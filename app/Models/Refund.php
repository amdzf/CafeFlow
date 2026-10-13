<?php

namespace App\Models;

use Core\Model;

class Refund extends Model
{
    protected string $table = 'refunds';

    public function allWithDetails(): array
    {
        $stmt = self::db()->query("
            SELECT r.*, o.order_number, u.name AS refunded_by_name
            FROM `{$this->table}` r
            JOIN orders o ON o.id = r.order_id
            JOIN users u ON u.id = r.refunded_by
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll();
    }
}
