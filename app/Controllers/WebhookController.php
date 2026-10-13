<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Services\MidtransService;
use App\Helpers\Logger;

class WebhookController extends Controller
{
    /**
     * Official Midtrans Webhook Handler
     * URL: /api/webhook/midtrans
     */
    public function handle(Request $request): void
    {
        $rawPayload = $request->getRawBody();
        $data = json_decode($rawPayload, true);

        if (!$data || !is_array($data)) {
            $this->json(['status' => 'error', 'message' => 'Invalid JSON payload'], 400);
            return;
        }

        $orderId = $data['order_id'] ?? '';
        $statusCode = (string)($data['status_code'] ?? '');
        $grossAmount = (string)($data['gross_amount'] ?? '');
        $transactionStatus = $data['transaction_status'] ?? '';
        $transactionId = $data['transaction_id'] ?? '';
        $signatureReceived = $data['signature_key'] ?? '';

        $midtrans = new MidtransService();
        $paymentEventModel = new PaymentEvent();
        $orderModel = new Order();
        $paymentModel = new Payment();

        // 1. Verify Midtrans SHA512 Signature
        $isValidSignature = $midtrans->verifySignature($orderId, $statusCode, $grossAmount, $signatureReceived);

        // Find internal order
        $order = $orderModel->findByOrderNumber($orderId);

        // Find payment
        $payment = null;
        if ($order) {
            $payment = $paymentModel->findPendingByOrderId((int)$order['id']);
            if (!$payment && !empty($transactionId)) {
                $payment = $paymentModel->findByTransactionId($transactionId);
            }
        }

        // Log payment event audit trail
        $eventId = $paymentEventModel->logEvent([
            'payment_id' => $payment['id'] ?? null,
            'order_id' => $order['id'] ?? null,
            'gateway' => 'midtrans',
            'event_type' => $transactionStatus,
            'signature_received' => $signatureReceived,
            'signature_valid' => $isValidSignature ? 1 : 0,
            'payload' => $rawPayload,
            'processed_status' => $isValidSignature ? 'success' : 'failed'
        ]);

        if (!$isValidSignature) {
            Logger::log('Webhook Signature Invalid', 'payment_events', (string)$eventId, "Signature mismatch untuk Order {$orderId}");
            $this->json(['status' => 'error', 'message' => 'Invalid signature key'], 403);
            return;
        }

        if (!$order) {
            $this->json(['status' => 'error', 'message' => 'Order not found'], 404);
            return;
        }

        // 2. Validate Order Gross Amount Match
        $orderTotal = number_format((float)$order['total_amount'], 2, '.', '');
        $incomingGross = number_format((float)$grossAmount, 2, '.', '');
        if ($orderTotal !== $incomingGross) {
            Logger::log('Webhook Gross Amount Mismatch', 'orders', (string)$order['id'], "Order total {$orderTotal} vs incoming {$incomingGross}");
            $this->json(['status' => 'error', 'message' => 'Gross amount mismatch'], 422);
            return;
        }

        // 3. Process Transaction Status
        $now = date('Y-m-d H:i:s');

        if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
            // IDEMPOTENCY GUARD:
            // If already marked as paid, return 200 OK immediately without double deducting stock or revenue!
            if ($order['payment_status'] === 'paid') {
                $this->json([
                    'status' => 'success',
                    'message' => 'Notification acknowledged (Order already paid)'
                ], 200);
                return;
            }

            // Update Payment record
            if ($payment) {
                $paymentModel->update($payment['id'], [
                    'status' => 'paid',
                    'paid_at' => $now,
                    'transaction_id' => $transactionId ?: $payment['transaction_id']
                ]);
            }

            // Update Order record
            $orderModel->update($order['id'], [
                'payment_status' => 'paid',
                'order_status' => ($order['order_status'] === 'pending') ? 'processing' : $order['order_status'],
                'paid_at' => $now
            ]);

            // Deduct stock idempotently
            $orderModel->deductInventory((int)$order['id']);

            Logger::log(
                'QRIS Terverifikasi Berhasil',
                'orders',
                (string)$order['id'],
                "Pembayaran QRIS {$order['order_number']} diverifikasi sukses (TrxID: {$transactionId})"
            );

        } elseif ($transactionStatus === 'expire') {
            if ($order['payment_status'] !== 'paid') {
                if ($payment) {
                    $paymentModel->markAsExpired($payment['id']);
                }
                $orderModel->update($order['id'], [
                    'payment_status' => 'expired',
                    'order_status' => 'cancelled'
                ]);
                Logger::log('QRIS Kedaluwarsa', 'orders', (string)$order['id'], "Pembayaran QRIS {$order['order_number']} kedaluwarsa.");
            }
        } elseif ($transactionStatus === 'cancel' || $transactionStatus === 'deny') {
            if ($order['payment_status'] !== 'paid') {
                if ($payment) {
                    $paymentModel->markAsFailed($payment['id']);
                }
                $orderModel->update($order['id'], [
                    'payment_status' => 'failed',
                    'order_status' => 'cancelled'
                ]);
                Logger::log('QRIS Dibatalkan / Gagal', 'orders', (string)$order['id'], "Status gateway: {$transactionStatus}");
            }
        }

        $this->json([
            'status' => 'success',
            'message' => 'Notification processed successfully',
            'order_id' => $orderId,
            'transaction_status' => $transactionStatus
        ], 200);
    }

    /**
     * Interactive Sandbox Simulation Endpoint
     * Simulates Midtrans Webhook with mathematically valid SHA512 signature!
     * URL: /api/webhook/simulate
     */
    public function simulate(Request $request): void
    {
        $orderNumber = trim((string)$request->input('order_number'));
        $targetStatus = $request->input('transaction_status', 'settlement'); // 'settlement', 'expire', 'cancel'

        $orderModel = new Order();
        $order = $orderModel->findByOrderNumber($orderNumber);

        if (!$order) {
            $this->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
            return;
        }

        $midtrans = new MidtransService();
        $statusCode = ($targetStatus === 'settlement') ? '200' : '202';
        $grossAmount = number_format((float)$order['total_amount'], 2, '.', '');
        $simulatedTrxId = 'SIM-MID-' . date('YmdHis') . '-' . mt_rand(100, 999);

        // Generate authentic SHA512 signature
        $validSignature = $midtrans->calculateSignature($orderNumber, $statusCode, $grossAmount);

        // Build authentic Midtrans notification payload
        $mockPayload = [
            'transaction_time' => date('Y-m-d H:i:s'),
            'transaction_status' => $targetStatus,
            'transaction_id' => $simulatedTrxId,
            'status_message' => 'midtrans payment notification simulation',
            'status_code' => $statusCode,
            'signature_key' => $validSignature,
            'payment_type' => 'qris',
            'order_id' => $orderNumber,
            'gross_amount' => $grossAmount,
            'fraud_status' => 'accept',
            'currency' => 'IDR'
        ];

        // Process through the actual webhook logic using curl or direct dispatch
        $req = new Request();
        // Execute internal webhook logic
        $ch = curl_init(url('/api/webhook/midtrans'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mockPayload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200) {
            $this->json([
                'success' => true,
                'message' => "Simulasi notifikasi payment gateway [{$targetStatus}] berhasil dikirim dan diverifikasi!",
                'order_number' => $orderNumber,
                'signature_used' => $validSignature,
                'payload' => $mockPayload
            ]);
            return;
        }

        // If curl to self had issues with port/path, invoke logic directly:
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $directRequest = new class($mockPayload) extends Request {
            private array $mock;
            public function __construct(array $mock) {
                parent::__construct();
                $this->mock = $mock;
            }
            public function getRawBody(): string {
                return json_encode($this->mock);
            }
        };

        // Call handle method directly
        $this->handle($directRequest);
    }
}
