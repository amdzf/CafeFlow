<?php

namespace App\Models;

use Core\Model;

class InventoryMovement extends Model
{
    protected string $table = 'inventory_movements';

    public function allWithDetails(?string $startDate = null, ?string $endDate = null, ?string $itemType = null): array
    {
        $sql = "
            SELECT im.*, u.name AS user_name,
                   CASE 
                       WHEN im.item_type = 'ingredient' THEN i.name
                       ELSE p.name
                   END AS item_name,
                   CASE 
                       WHEN im.item_type = 'ingredient' THEN i.unit
                       ELSE p.unit
                   END AS item_unit
            FROM `{$this->table}` im
            LEFT JOIN ingredients i ON i.id = im.item_id AND im.item_type = 'ingredient'
            LEFT JOIN products p ON p.id = im.item_id AND im.item_type = 'product'
            LEFT JOIN users u ON u.id = im.created_by
            WHERE 1=1
        ";
        $params = [];

        if ($startDate) {
            $sql .= " AND DATE(im.created_at) >= :s_date";
            $params[':s_date'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND DATE(im.created_at) <= :e_date";
            $params[':e_date'] = $endDate;
        }

        if ($itemType) {
            $sql .= " AND im.item_type = :item_type";
            $params[':item_type'] = $itemType;
        }

        $sql .= " ORDER BY im.created_at DESC, im.id DESC";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
