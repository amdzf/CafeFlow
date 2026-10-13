<?php

namespace App\Models;

use Core\Model;

class PaymentEvent extends Model
{
    protected string $table = 'payment_events';

    public function logEvent(array $data): int
    {
        return $this->create([
            'payment_id' => $data['payment_id'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'gateway' => $data['gateway'] ?? 'midtrans',
            'event_type' => $data['event_type'] ?? 'unknown',
            'signature_received' => $data['signature_received'] ?? null,
            'signature_valid' => $data['signature_valid'] ?? 0,
            'payload' => is_string($data['payload']) ? $data['payload'] : json_encode($data['payload'], JSON_UNESCAPED_UNICODE),
            'ip_address' => $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'),
            'processed_status' => $data['processed_status'] ?? 'success'
        ]);
    }
}
