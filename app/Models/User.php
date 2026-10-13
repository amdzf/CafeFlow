<?php

namespace App\Models;

use Core\Model;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM `{$this->table}` WHERE `email` = :email AND `is_active` = 1 LIMIT 1");
        $stmt->execute([':email' => $email]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function getAllUsers(): array
    {
        $stmt = self::db()->query("SELECT id, name, email, role, phone, is_active, created_at FROM `{$this->table}` ORDER BY id ASC");
        return $stmt->fetchAll();
    }
}
