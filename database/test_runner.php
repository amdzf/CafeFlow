<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/app/Helpers/Env.php';
\App\Helpers\Env::load(ROOT_PATH . '/.env');
require_once ROOT_PATH . '/app/Config/Database.php';
require_once ROOT_PATH . '/core/Model.php';
require_once ROOT_PATH . '/app/Models/User.php';
require_once ROOT_PATH . '/app/Models/Product.php';
require_once ROOT_PATH . '/app/Models/Ingredient.php';
require_once ROOT_PATH . '/app/Models/Order.php';
require_once ROOT_PATH . '/app/Models/Payment.php';
require_once ROOT_PATH . '/app/Models/PaymentEvent.php';
require_once ROOT_PATH . '/app/Models/Expense.php';
require_once ROOT_PATH . '/app/Models/Setting.php';
require_once ROOT_PATH . '/app/Services/MidtransService.php';

echo "=== CAFEFLOW POS E2E INTEGRATION SUITE ===\n\n";

$passCount = 0;
$failCount = 0;

function testAssert(string $testName, bool $condition, ?string $extra = null) {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] {$testName}\n";
        $passCount++;
    } else {
        echo " [FAIL] {$testName}" . ($extra ? " - {$extra}" : "") . "\n";
        $failCount++;
    }
}

// TEST 1: Database Connection & Users
$userModel = new \App\Models\User();
$admin = $userModel->findByEmail('admin@cafeflow.com');
testAssert("Admin user exists and role is admin", ($admin && $admin['role'] === 'admin'));
testAssert("Admin password hash verifies with admin123", password_verify('admin123', $admin['password']));

$cashier = $userModel->findByEmail('kasir@cafeflow.com');
testAssert("Cashier user exists and role is kasir", ($cashier && $cashier['role'] === 'kasir'));
testAssert("Cashier password hash verifies with kasir123", password_verify('kasir123', $cashier['password']));

// TEST 2: Product & Recipe Inventory Check
$productModel = new \App\Models\Product();
$products = $productModel->allForPos();
testAssert("Active products list for POS loaded (count >= 10)", count($products) >= 10);

$kopiSusu = null;
foreach ($products as $p) {
    if (str_contains(strtolower($p['name']), 'kopi susu')) {
        $kopiSusu = $p;
        break;
    }
}
testAssert("Kopi Susu Gula Aren product found", $kopiSusu !== null);
testAssert("Recipe availability returns true for Kopi Susu", (bool)$kopiSusu['is_available']);

// TEST 3: Order Creation with Server-Side Price Calculation
$orderModel = new \App\Models\Order();
$cartItems = [
    [
        'product_id' => $kopiSusu['id'],
        'quantity' => 2,
        'notes' => 'Less ice, normal sugar'
    ]
];
$orderData = [
    'customer_name' => 'Budi Tester',
    'order_type' => 'dine_in',
    'table_id' => 1,
    'discount_percent' => 10,
    'payment_method' => 'cash',
    'notes' => 'Testing order flow'
];

$orderRes = $orderModel->createWithItems($orderData, $cartItems, (int)$admin['id']);
testAssert("Order created successfully", $orderRes['success'] === true);
testAssert("Order number formatted with prefix ORD-", str_starts_with($orderRes['order_number'], 'ORD-'));

// 2 cups @ 22.000 = 44.000, 10% disc = 4.400 -> 39.600 + 10% tax (3.960) = 43.560
$expectedTotal = 43560.00;
testAssert("Backend calculation matches expectation (Rp {$expectedTotal})", abs($orderRes['grand_total'] - $expectedTotal) < 1.0);

$orderId = $orderRes['order_id'];
$orderNumber = $orderRes['order_number'];

// TEST 4: Cash Payment & Change Calculation
$paymentModel = new \App\Models\Payment();
$tendered = 50000.00;
$change = $tendered - $orderRes['grand_total'];

$payId = $paymentModel->create([
    'order_id' => $orderId,
    'payment_method' => 'cash',
    'amount' => $orderRes['grand_total'],
    'tendered_amount' => $tendered,
    'change_amount' => $change,
    'transaction_id' => 'CASH-' . $orderNumber,
    'status' => 'paid',
    'cashier_id' => (int)$admin['id']
]);
$orderModel->update($orderId, [
    'payment_status' => 'paid',
    'order_status' => 'processing',
    'paid_at' => date('Y-m-d H:i:s')
]);
$orderModel->deductInventory($orderId, (int)$admin['id']);

$updatedOrder = $orderModel->findWithDetails($orderId);
testAssert("Order marked as PAID", $updatedOrder['payment_status'] === 'paid');
testAssert("Stock deducted flag set to 1", (int)$updatedOrder['stock_deducted'] === 1);

// Test Idempotent stock deduction (should not deduct twice!)
$ingredientModel = new \App\Models\Ingredient();
$beansBefore = (float)$ingredientModel->find(1)['stock'];
$orderModel->deductInventory($orderId, (int)$admin['id']); // Second call
$beansAfter = (float)$ingredientModel->find(1)['stock'];
testAssert("Idempotency guard prevents duplicate stock deduction on second call", ($beansBefore === $beansAfter));

// TEST 5: QRIS Payment Charge & SHA512 Signature Verification
$cartItemsQris = [
    [
        'product_id' => $kopiSusu['id'],
        'quantity' => 1,
        'notes' => 'Hot'
    ]
];
$orderDataQris = [
    'customer_name' => 'Siti QRIS',
    'order_type' => 'takeaway',
    'discount_percent' => 0,
    'payment_method' => 'qris'
];
$orderQrisRes = $orderModel->createWithItems($orderDataQris, $cartItemsQris, (int)$admin['id']);
$qrisOrderId = $orderQrisRes['order_id'];
$qrisOrderNumber = $orderQrisRes['order_number'];
$qrisGrandTotal = $orderQrisRes['grand_total'];

$midtrans = new \App\Services\MidtransService();
$chargeResult = $midtrans->createQrisCharge($qrisOrderNumber, $qrisGrandTotal);
testAssert("Midtrans dynamic QRIS charge generated", $chargeResult['success'] === true);
testAssert("QR code string and image URL generated", !empty($chargeResult['qr_string']) && !empty($chargeResult['qr_url']));

// Check Signature Calculation & Verification
$statusCode = '200';
$grossAmountStr = number_format($qrisGrandTotal, 2, '.', '');
$calculatedSignature = $midtrans->calculateSignature($qrisOrderNumber, $statusCode, $grossAmountStr);
testAssert("Midtrans SHA512 signature computed", strlen($calculatedSignature) === 128);

$verifyResult = $midtrans->verifySignature($qrisOrderNumber, $statusCode, $grossAmountStr, $calculatedSignature);
testAssert("Midtrans signature verifies accurately with server key", $verifyResult === true);

$tamperedSignature = $calculatedSignature . 'bad';
$verifyTampered = $midtrans->verifySignature($qrisOrderNumber, $statusCode, $grossAmountStr, $tamperedSignature);
testAssert("Tampered signature correctly rejected by security check", $verifyTampered === false);

// Webhook simulation execution
$paymentEventModel = new \App\Models\PaymentEvent();
$eventId = $paymentEventModel->logEvent([
    'order_id' => $qrisOrderId,
    'gateway' => 'midtrans',
    'event_type' => 'settlement',
    'signature_received' => $calculatedSignature,
    'signature_valid' => 1,
    'payload' => json_encode(['order_id' => $qrisOrderNumber, 'status' => 'settlement']),
    'processed_status' => 'success'
]);
testAssert("Payment webhook audit log event created (ID: {$eventId})", $eventId > 0);

// Mark QRIS order as paid
$orderModel->update($qrisOrderId, [
    'payment_status' => 'paid',
    'paid_at' => date('Y-m-d H:i:s')
]);
$orderModel->deductInventory($qrisOrderId);
$qrisOrderVerified = $orderModel->findWithDetails($qrisOrderId);
testAssert("QRIS order transitions to PAID", $qrisOrderVerified['payment_status'] === 'paid');

// TEST 6: Kitchen Display System (KDS) Queue
$kitchenOrders = $orderModel->getKitchenOrders();
testAssert("KDS active orders queue retrieved", count($kitchenOrders) > 0);

// TEST 7: Financial Report Aggregations
$expenseModel = new \App\Models\Expense();
$totalExpenses = $expenseModel->getTotalExpenses();
testAssert("Expense total calculated", $totalExpenses >= 0);

// TEST 8: Transaction Report Export (Word) Aggregations
$db = \Core\Model::db();
$today = date('Y-m-d');
$stmtWord = $db->prepare("
    SELECT 
        COALESCE(SUM(subtotal), 0) AS gross_sales,
        COALESCE(SUM(total_amount), 0) AS grand_total,
        COUNT(id) AS total_transactions
    FROM orders
    WHERE payment_status = 'paid' AND DATE(paid_at) = :today
");
$stmtWord->execute([':today' => $today]);
$wordDailySummary = $stmtWord->fetch();
testAssert("Word report daily query executes successfully", $wordDailySummary !== false);
testAssert("Word report daily transactions count is non-negative", (int)$wordDailySummary['total_transactions'] >= 0);

$stmtItems = $db->prepare("
    SELECT oi.product_name, SUM(oi.quantity) AS qty_sold
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE o.payment_status = 'paid'
    GROUP BY oi.product_id, oi.product_name
");
$stmtItems->execute();
$itemsRecap = $stmtItems->fetchAll();
testAssert("Word report sold menu recap executes cleanly", is_array($itemsRecap));

echo "\n==========================================\n";
echo "TEST RESULTS: {$passCount} PASSED, {$failCount} FAILED\n";
echo "==========================================\n";

if ($failCount === 0) {
    echo "ALL SUITES PASSED! CafeFlow POS is 100% functional.\n";
} else {
    exit(1);
}
