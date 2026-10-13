<?php

namespace Core;

use App\Config\Database;
use PDO;

abstract class Model
{
    protected static ?PDO $db = null;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public static function db(): PDO
    {
        if (self::$db === null) {
            self::$db = Database::getConnection();
        }
        return self::$db;
    }

    public static function beginTransaction(): bool
    {
        return self::db()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::db()->commit();
    }

    public static function rollBack(): bool
    {
        if (self::db()->inTransaction()) {
            return self::db()->rollBack();
        }
        return false;
    }

    public function all(string $orderBy = 'id DESC'): array
    {
        $stmt = self::db()->query("SELECT * FROM `{$this->table}` ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    public function find(int|string $id): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $columns = array_keys($data);
        $fields = implode('`, `', $columns);
        $placeholders = ':' . implode(', :', $columns);

        $sql = "INSERT INTO `{$this->table}` (`{$fields}`) VALUES ({$placeholders})";
        $stmt = self::db()->prepare($sql);

        $params = [];
        foreach ($data as $key => $value) {
            $params[':' . $key] = $value;
        }

        $stmt->execute($params);
        return (int)self::db()->lastInsertId();
    }

    public function update(int|string $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = :{$key}";
            $params[':' . $key] = $value;
        }

        $fieldStr = implode(', ', $fields);
        $sql = "UPDATE `{$this->table}` SET {$fieldStr} WHERE `{$this->primaryKey}` = :id";
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int|string $id): bool
    {
        $stmt = self::db()->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id");
        return $stmt->execute([':id' => $id]);
    }
}
