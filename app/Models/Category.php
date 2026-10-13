<?php

namespace App\Models;

use Core\Model;

class Category extends Model
{
    protected string $table = 'categories';

    public function allActive(): array
    {
        $stmt = self::db()->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM `{$this->table}` c
            LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY c.sort_order ASC, c.name ASC
        ");
        return $stmt->fetchAll();
    }
}
