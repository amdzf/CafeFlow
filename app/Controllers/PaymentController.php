<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use App\Helpers\Auth;
use App\Helpers\Logger;

class PaymentController extends Controller
{
    /**
     * Process Cash Payment
     */
    public function payCash(Request $request): void
    {
        $this->validateCsrf($request);

        $orderId = (int)$request->input('order_id');
        $tendered = (float)$request->input('tendered_amount');

        $orderModel = new Order();
        $order = $orderModel->findWithDetails($orderId);

        if (!$order) {
            $this->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
            return;
        }

        if ($order['payment_status'] === 'paid') {
            $this->json(['success' => false, 'message' => 'Pesanan ini sudah lunas sebelumnya.'], 400);
            return;
        }

        $totalAmount = (float)$order['total_amount'];
        if ($tendered < $totalAmount) {
            $this->json([
                'success' => false, 
                'message' => 'Nominal uang tunai yang diterima kurang dari total pembayaran (' . number_format($totalAmount, 0, ',', '.') . ').'
            ], 422);
            return;
        }

        $changeAmount = $tendered - $totalAmount;
        $cashierId = (int)Auth::id();
        $paidTime = date('Y-m-d H:i:s');

        $paymentModel = new Payment();
        $trxId = 'CASH-' . $order['order_number'];

        // Save payment
        $paymentId = $paymentModel->create([
            'order_id' => $orderId,
            'payment_method' => 'cash',
            'amount' => $totalAmount,
            'tendered_amount' => $tendered,
            'change_amount' => $changeAmount,
            'transaction_id' => $trxId,
            'status' => 'paid',
            'paid_at' => $paidTime,
            'cashier_id' => $cashierId
        ]);

        // Update order status
        $orderModel->update($orderId, [
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'order_status' => ($order['order_status'] === 'pending') ? 'processing' : $order['order_status'],
            'paid_at' => $paidTime
        ]);

        // Idempotent inventory deduction
        $orderModel->deductInventory($orderId, $cashierId);

        Logger::log(
            'Pembayaran Tunai Diterima', 
            'payments', 
            (string)$paymentId, 
            "Pesanan {$order['order_number']} dibayar tunai Rp " . number_format($totalAmount, 0, ',', '.') . " (Diterima: Rp " . number_format($tendered, 0, ',', '.') . ", Kembalian: Rp " . number_format($changeAmount, 0, ',', '.') . ")"
        );

        $this->json([
            'success' => true,
            'message' => 'Pembayaran tunai berhasil diproses.',
            'order_id' => $orderId,
            'order_number' => $order['order_number'],
            'tendered_amount' => $tendered,
            'change_amount' => $changeAmount,
            'receipt_url' => url("/orders/{$orderId}/receipt")
        ]);
    }

    /**
     * Generate Dynamic QRIS Charge
     */
    public function createQris(Request $request): void
    {
        $this->validateCsrf($request);

        $orderId = (int)$request->input('order_id');
        $orderModel = new Order();
        $order = $orderModel->findWithDetails($orderId);

        if (!$order) {
            $this->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
            return;
        }

        if ($order['payment_status'] === 'paid') {
            $this->json(['success' => false, 'message' => 'Pesanan ini sudah lunas sebelumnya.'], 400);
            return;
        }

        // Call Midtrans QRIS Service
        $midtrans = new MidtransService();
        $chargeResult = $midtrans->createQrisCharge(
            $order['order_number'],
            (float)$order['total_amount'],
            ['name' => $order['customer_name']],
            $order['items']
        );

        if (!$chargeResult['success']) {
            $this->json(['success' => false, 'message' => 'Gagal membuat tagihan QRIS ke payment gateway.'], 500);
            return;
        }

        $paymentModel = new Payment();
        // Check if pending payment exists for this order
        $existing = $paymentModel->findPendingByOrderId($orderId);
        if ($existing) {
            $paymentModel->update($existing['id'], [
                'transaction_id' => $chargeResult['transaction_id'],
                'qr_string' => $chargeResult['qr_string'],
                'qr_url' => $chargeResult['qr_url'],
                'expired_at' => $chargeResult['expired_at']
            ]);
            $paymentId = $existing['id'];
        } else {
            $paymentId = $paymentModel->create([
                'order_id' => $orderId,
                'payment_method' => 'qris',
                'amount' => (float)$order['total_amount'],
                'tendered_amount' => (float)$order['total_amount'],
                'change_amount' => 0.00,
                'transaction_id' => $chargeResult['transaction_id'],
                'qr_string' => $chargeResult['qr_string'],
                'qr_url' => $chargeResult['qr_url'],
                'status' => 'pending',
                'expired_at' => $chargeResult['expired_at'],
                'cashier_id' => (int)Auth::id()
            ]);
        }

        $orderModel->update($orderId, [
            'payment_method' => 'qris'
        ]);

        Logger::log(
            'QRIS Dibuat',
            'payments',
            (string)$paymentId,
            "QRIS dibuat untuk pesanan {$order['order_number']} sebesar Rp " . number_format($order['total_amount'], 0, ',', '.')
        );

        $this->json([
            'success' => true,
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'order_number' => $order['order_number'],
            'gross_amount' => (float)$order['total_amount'],
            'transaction_id' => $chargeResult['transaction_id'],
            'qr_string' => $chargeResult['qr_string'],
            'qr_url' => $chargeResult['qr_url'],
            'expired_at' => $chargeResult['expired_at'],
            'is_sandbox_simulation' => $chargeResult['is_sandbox_simulation']
        ]);
    }

    /**
     * Poll Payment Status (Realtime frontend check)
     */
    public function checkStatus(Request $request, string $orderId): void
    {
        $orderModel = new Order();
        $order = $orderModel->findWithDetails($orderId);

        if (!$order) {
            $this->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
            return;
        }

        $latestPayment = $order['latest_payment'] ?? null;

        $this->json([
            'success' => true,
            'order_id' => $order['id'],
            'order_number' => $order['order_number'],
            'payment_status' => $order['payment_status'],
            'order_status' => $order['order_status'],
            'paid_at' => $order['paid_at'],
            'payment' => $latestPayment,
            'is_paid' => ($order['payment_status'] === 'paid'),
            'receipt_url' => url("/orders/{$order['id']}/receipt")
        ]);
    }
}
