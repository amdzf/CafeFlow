<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use App\Models\Order;
use App\Models\Setting;
use App\Services\ReceiptService;
use App\Helpers\Auth;
use App\Helpers\Logger;

class OrderController extends Controller
{
    public function index(Request $request): void
    {
        $orderModel = new Order();
        $db = Order::db();

        $status = $request->get('status');
        $paymentStatus = $request->get('payment_status');
        $date = $request->get('date', date('Y-m-d'));
        $search = $request->get('search');

        $sql = "
            SELECT o.*, t.table_number, u.name AS cashier_name,
                   (SELECT COUNT(id) FROM order_items WHERE order_id = o.id) AS total_items
            FROM orders o
            LEFT JOIN tables t ON t.id = o.table_id
            LEFT JOIN users u ON u.id = o.user_id
            WHERE 1=1
        ";
        $params = [];

        if ($date) {
            $sql .= " AND DATE(o.created_at) = :order_date";
            $params[':order_date'] = $date;
        }

        if ($status) {
            $sql .= " AND o.order_status = :status";
            $params[':status'] = $status;
        }

        if ($paymentStatus) {
            $sql .= " AND o.payment_status = :pay_status";
            $params[':pay_status'] = $paymentStatus;
        }

        if ($search) {
            $sql .= " AND (o.order_number LIKE :search OR o.customer_name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY o.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        $this->render('orders/index', [
            'title' => 'Daftar Pesanan - CafeFlow POS',
            'orders' => $orders,
            'currentDate' => $date,
            'currentStatus' => $status,
            'currentPayStatus' => $paymentStatus,
            'search' => $search
        ]);
    }

    public function show(Request $request, string $id): void
    {
        $orderModel = new Order();
        $order = $orderModel->findWithDetails($id);

        if (!$order) {
            $this->redirect('/orders', 'error', 'Pesanan tidak ditemukan.');
            return;
        }

        $this->render('orders/show', [
            'title' => "Pesanan #{$order['order_number']} - CafeFlow POS",
            'order' => $order
        ]);
    }

    /**
     * Store new order from POS cart
     */
    public function create(Request $request): void
    {
        $this->validateCsrf($request);

        $cart = $request->input('cart');
        if (empty($cart) || !is_array($cart)) {
            $this->json(['success' => false, 'message' => 'Keranjang pesanan masih kosong.'], 400);
            return;
        }

        $orderData = [
            'customer_name' => trim((string)$request->input('customer_name', 'Pelanggan Walk-in')),
            'order_type' => $request->input('order_type', 'dine_in'),
            'table_id' => $request->input('table_id'),
            'discount_percent' => (float)$request->input('discount_percent', 0),
            'discount_amount' => (float)$request->input('discount_amount', 0),
            'payment_method' => $request->input('payment_method', 'none'),
            'notes' => trim((string)$request->input('notes', ''))
        ];

        try {
            $orderModel = new Order();
            $result = $orderModel->createWithItems($orderData, $cart, (int)Auth::id());

            Logger::log(
                'Pesanan Dibuat',
                'orders',
                (string)$result['order_id'],
                "Pesanan {$result['order_number']} dibuat oleh " . Auth::user()['name']
            );

            $this->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat.',
                'order_id' => $result['order_id'],
                'order_number' => $result['order_number'],
                'grand_total' => $result['grand_total']
            ]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update order status (KDS / Cashier)
     */
    public function updateStatus(Request $request, string $id): void
    {
        $this->validateCsrf($request);

        $newStatus = strtolower((string)$request->input('order_status'));
        $allowed = ['pending', 'processing', 'ready', 'completed', 'cancelled'];

        if (!in_array($newStatus, $allowed, true)) {
            $this->json(['success' => false, 'message' => 'Status pesanan tidak valid.'], 400);
            return;
        }

        $orderModel = new Order();
        $order = $orderModel->findWithDetails($id);
        if (!$order) {
            $this->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
            return;
        }

        $updateData = ['order_status' => $newStatus];
        if ($newStatus === 'completed') {
            $updateData['completed_at'] = date('Y-m-d H:i:s');
            // If table occupied, free it
            if (!empty($order['table_id'])) {
                $db = Order::db();
                $db->prepare("UPDATE tables SET status = 'available' WHERE id = :id")->execute([':id' => $order['table_id']]);
            }
        }

        $orderModel->update($order['id'], $updateData);
        Logger::log('Update Status Pesanan', 'orders', (string)$order['id'], "Status diubah menjadi {$newStatus}");

        $this->json(['success' => true, 'message' => "Status pesanan berhasil diperbarui ke {$newStatus}."]);
    }

    /**
     * Thermal Receipt Preview / Print
     */
    public function receipt(Request $request, string $id): void
    {
        $orderModel = new Order();
        $order = $orderModel->findWithDetails($id);

        if (!$order) {
            die("Pesanan tidak ditemukan.");
        }

        $cafeInfo = ReceiptService::getCafeInfo();

        // Render receipt without the standard app layout
        $this->render('orders/receipt', [
            'order' => $order,
            'cafe' => $cafeInfo
        ], null);
    }
}
