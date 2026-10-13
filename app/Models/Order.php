<?php

namespace App\Models;

use Core\Model;
use PDO;

class Order extends Model
{
    protected string $table = 'orders';

    public function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . date('Ymd') . '-';
        $stmt = self::db()->prepare("
            SELECT order_number FROM `{$this->table}` 
            WHERE order_number LIKE :prefix 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([':prefix' => $prefix . '%']);
        $lastOrder = $stmt->fetch();

        if ($lastOrder) {
            $lastNum = (int)substr($lastOrder['order_number'], -4);
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        return $prefix . $newNum;
    }

    public function findWithDetails(int|string $id): ?array
    {
        $stmt = self::db()->prepare("
            SELECT o.*, t.table_number, t.name AS table_name, u.name AS cashier_name
            FROM `{$this->table}` o
            LEFT JOIN tables t ON t.id = o.table_id
            LEFT JOIN users u ON u.id = o.user_id
            WHERE o.id = :id OR o.order_number = :order_num
            LIMIT 1
        ");
        $stmt->execute([':id' => $id, ':order_num' => $id]);
        $order = $stmt->fetch();

        if (!$order) return null;

        // Fetch items
        $stmtItems = self::db()->prepare("
            SELECT oi.*, p.sku, p.stock_type, p.image
            FROM order_items oi
            LEFT JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = :order_id
            ORDER BY oi.id ASC
        ");
        $stmtItems->execute([':order_id' => $order['id']]);
        $order['items'] = $stmtItems->fetchAll();

        // Fetch payments
        $stmtPayments = self::db()->prepare("
            SELECT p.*, u.name AS cashier_name
            FROM payments p
            LEFT JOIN users u ON u.id = cashier_id
            WHERE p.order_id = :order_id
            ORDER BY p.id DESC
        ");
        $stmtPayments->execute([':order_id' => $order['id']]);
        $order['payments'] = $stmtPayments->fetchAll();
        $order['latest_payment'] = $order['payments'][0] ?? null;

        return $order;
    }

    public function findByOrderNumber(string $orderNumber): ?array
    {
        return $this->findWithDetails($orderNumber);
    }

    /**
     * Creates order and items in a single transaction with backend-validated calculation
     */
    public function createWithItems(array $orderData, array $cartItems, int $userId): array
    {
        $db = self::db();
        $db->beginTransaction();

        try {
            // 1. Fetch live product data from database to enforce server-side prices
            $productModel = new Product();
            $subtotal = 0.00;
            $totalCost = 0.00;
            $preparedItems = [];

            foreach ($cartItems as $cartItem) {
                $pid = (int)($cartItem['product_id'] ?? 0);
                $qty = max(1, (int)($cartItem['quantity'] ?? 1));
                $notes = trim((string)($cartItem['notes'] ?? ''));

                $product = $productModel->find($pid);
                if (!$product || $product['is_active'] != 1 || $product['status'] !== 'tersedia') {
                    throw new \Exception("Produk [ID: {$pid}] tidak tersedia atau tidak aktif.");
                }

                $unitPrice = (float)$product['price'];
                $costPrice = (float)$product['cost_price'];
                $itemSubtotal = $unitPrice * $qty;
                $itemCost = $costPrice * $qty;

                $subtotal += $itemSubtotal;
                $totalCost += $itemCost;

                $preparedItems[] = [
                    'product_id' => $pid,
                    'product_name' => $product['name'],
                    'unit_price' => $unitPrice,
                    'cost_price' => $costPrice,
                    'quantity' => $qty,
                    'subtotal' => $itemSubtotal,
                    'notes' => $notes
                ];
            }

            if (empty($preparedItems)) {
                throw new \Exception("Keranjang belanja kosong.");
            }

            // 2. Calculate discounts, taxes, and service charges on backend
            $discountPercent = max(0, min(100, (float)($orderData['discount_percent'] ?? 0)));
            $discountAmount = ($discountPercent > 0) ? round(($subtotal * $discountPercent) / 100, 2) : 0.00;

            if (isset($orderData['discount_amount']) && (float)$orderData['discount_amount'] > 0 && $discountPercent == 0) {
                $discountAmount = min($subtotal, (float)$orderData['discount_amount']);
            }

            $afterDiscount = max(0, $subtotal - $discountAmount);

            // Settings for tax and service charge
            $settingModel = new Setting();
            $taxRate = (float)$settingModel->get('tax_percent', 10);
            $serviceRate = (float)$settingModel->get('service_charge_percent', 0);

            $taxAmount = round(($afterDiscount * $taxRate) / 100, 2);
            $serviceCharge = round(($afterDiscount * $serviceRate) / 100, 2);
            $grandTotal = $afterDiscount + $taxAmount + $serviceCharge;

            $orderNumber = $this->generateOrderNumber();
            $orderType = in_array($orderData['order_type'] ?? '', ['dine_in', 'takeaway']) ? $orderData['order_type'] : 'dine_in';
            $tableId = ($orderType === 'dine_in' && !empty($orderData['table_id'])) ? (int)$orderData['table_id'] : null;

            // 3. Insert into orders
            $stmtOrder = $db->prepare("
                INSERT INTO `{$this->table}` 
                (order_number, customer_name, order_type, table_id, subtotal, discount_percent, discount_amount, tax_amount, service_charge, total_cost, total_amount, payment_status, order_status, payment_method, notes, user_id)
                VALUES (:num, :c_name, :o_type, :table_id, :subtotal, :disc_pct, :disc_amt, :tax_amt, :svc_amt, :cost_amt, :total_amt, 'unpaid', 'pending', :method, :notes, :user_id)
            ");
            $stmtOrder->execute([
                ':num' => $orderNumber,
                ':c_name' => !empty($orderData['customer_name']) ? trim($orderData['customer_name']) : 'Pelanggan Walk-in',
                ':o_type' => $orderType,
                ':table_id' => $tableId,
                ':subtotal' => $subtotal,
                ':disc_pct' => $discountPercent,
                ':disc_amt' => $discountAmount,
                ':tax_amt' => $taxAmount,
                ':svc_amt' => $serviceCharge,
                ':cost_amt' => $totalCost,
                ':total_amt' => $grandTotal,
                ':method' => $orderData['payment_method'] ?? 'none',
                ':notes' => $orderData['notes'] ?? null,
                ':user_id' => $userId
            ]);

            $orderId = (int)$db->lastInsertId();

            // 4. Insert into order_items
            $stmtItem = $db->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, unit_price, cost_price, quantity, subtotal, notes)
                VALUES (:order_id, :product_id, :name, :price, :cost, :qty, :subtotal, :notes)
            ");

            foreach ($preparedItems as $item) {
                $stmtItem->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product_id'],
                    ':name' => $item['product_name'],
                    ':price' => $item['unit_price'],
                    ':cost' => $item['cost_price'],
                    ':qty' => $item['quantity'],
                    ':subtotal' => $item['subtotal'],
                    ':notes' => $item['notes']
                ]);
            }

            $db->commit();

            return [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'grand_total' => $grandTotal
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Deducts inventory once order payment is confirmed. IDEMPOTENT.
     */
    public function deductInventory(int $orderId, ?int $userId = null): bool
    {
        $db = self::db();
        $order = $this->findWithDetails($orderId);
        if (!$order) return false;

        // IDEMPOTENCY CHECK: if stock has already been deducted, DO NOT deduct again!
        if ((int)$order['stock_deducted'] === 1) {
            return true;
        }

        $ingredientModel = new Ingredient();
        $productModel = new Product();

        foreach ($order['items'] as $item) {
            $productId = (int)$item['product_id'];
            $qtySold = (int)$item['quantity'];
            $stockType = $item['stock_type'] ?? 'direct';

            if ($stockType === 'direct') {
                $productModel->adjustDirectStock(
                    $productId,
                    -$qtySold,
                    'sale',
                    'order',
                    $order['order_number'],
                    "Penjualan transaksi {$order['order_number']}",
                    $userId
                );
            } else {
                // Recipe based: deduct ingredients
                $stmtRecipe = $db->prepare("
                    SELECT ingredient_id, quantity 
                    FROM product_recipes 
                    WHERE product_id = :pid
                ");
                $stmtRecipe->execute([':pid' => $productId]);
                $recipes = $stmtRecipe->fetchAll();

                foreach ($recipes as $r) {
                    $ingId = (int)$r['ingredient_id'];
                    $usedPerItem = (float)$r['quantity'];
                    $totalDeduction = $usedPerItem * $qtySold;

                    $ingredientModel->adjustStock(
                        $ingId,
                        -$totalDeduction,
                        'sale',
                        'order',
                        $order['order_number'],
                        "Resep pemakaian {$qtySold}x {$item['product_name']} (Order {$order['order_number']})",
                        $userId
                    );
                }
            }
        }

        // Mark stock as deducted
        $stmtUpdate = $db->prepare("UPDATE `{$this->table}` SET stock_deducted = 1 WHERE id = :id");
        $stmtUpdate->execute([':id' => $orderId]);

        // If dine in and table assigned, mark table as occupied
        if ($order['order_type'] === 'dine_in' && !empty($order['table_id'])) {
            $stmtTable = $db->prepare("UPDATE tables SET status = 'occupied' WHERE id = :id");
            $stmtTable->execute([':id' => $order['table_id']]);
        }

        return true;
    }

    public function getKitchenOrders(): array
    {
        $stmt = self::db()->query("
            SELECT o.*, t.table_number, t.name as table_name
            FROM `{$this->table}` o
            LEFT JOIN tables t ON t.id = o.table_id
            WHERE o.payment_status = 'paid' 
              AND o.order_status IN ('pending', 'processing', 'ready')
            ORDER BY 
                CASE o.order_status
                    WHEN 'pending' THEN 1
                    WHEN 'processing' THEN 2
                    WHEN 'ready' THEN 3
                    ELSE 4
                END,
                o.created_at ASC
        ");
        $orders = $stmt->fetchAll();

        foreach ($orders as &$order) {
            $stmtItems = self::db()->prepare("
                SELECT oi.* 
                FROM order_items oi
                WHERE oi.order_id = :oid
            ");
            $stmtItems->execute([':oid' => $order['id']]);
            $order['items'] = $stmtItems->fetchAll();
        }

        return $orders;
    }
}
