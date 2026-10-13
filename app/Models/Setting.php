<?php

namespace App\Models;

use Core\Model;

class Setting extends Model
{
    protected string $table = 'settings';
    private static array $cache = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $stmt = self::db()->prepare("SELECT setting_value FROM `{$this->table}` WHERE setting_key = :k LIMIT 1");
        $stmt->execute([':k' => $key]);
        $row = $stmt->fetch();

        if ($row && $row['setting_value'] !== null) {
            self::$cache[$key] = $row['setting_value'];
            return $row['setting_value'];
        }

        return $default;
    }

    public function set(string $key, mixed $value, ?string $description = null): bool
    {
        self::$cache[$key] = $value;
        $stmt = self::db()->prepare("
            INSERT INTO `{$this->table}` (setting_key, setting_value, description)
            VALUES (:k, :v, :d)
            ON DUPLICATE KEY UPDATE setting_value = :v, updated_at = NOW()
        ");
        return $stmt->execute([
            ':k' => $key,
            ':v' => $value,
            ':d' => $description
        ]);
    }

    public function getAll(): array
    {
        $stmt = self::db()->query("SELECT * FROM `{$this->table}` ORDER BY id ASC");
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
            self::$cache[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
}
