<?php

namespace App\Models;

use Core\Model;

class Table extends Model
{
    protected string $table = 'tables';

    public function allActive(): array
    {
        $stmt = self::db()->query("SELECT * FROM `{$this->table}` WHERE is_active = 1 ORDER BY table_number ASC");
        return $stmt->fetchAll();
    }
}
