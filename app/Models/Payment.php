<?php

namespace App\Models;

use Core\Model;

class Payment extends Model
{
    protected string $table = 'payments';

    public function findByTransactionId(string $trxId): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM `{$this->table}` WHERE transaction_id = :tx LIMIT 1");
        $stmt->execute([':tx' => $trxId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findPendingByOrderId(int $orderId): ?array
    {
        $stmt = self::db()->prepare("
            SELECT * FROM `{$this->table}` 
            WHERE order_id = :oid AND status = 'pending' 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([':oid' => $orderId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function markAsPaid(int $id, ?string $paidAt = null): bool
    {
        $time = $paidAt ?: date('Y-m-d H:i:s');
        $stmt = self::db()->prepare("
            UPDATE `{$this->table}` 
            SET status = 'paid', paid_at = :paid_at 
            WHERE id = :id
        ");
        return $stmt->execute([':paid_at' => $time, ':id' => $id]);
    }

    public function markAsExpired(int $id): bool
    {
        $stmt = self::db()->prepare("UPDATE `{$this->table}` SET status = 'expired' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function markAsFailed(int $id): bool
    {
        $stmt = self::db()->prepare("UPDATE `{$this->table}` SET status = 'failed' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
