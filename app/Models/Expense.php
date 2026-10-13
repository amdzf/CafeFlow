<?php

namespace App\Models;

use Core\Model;

class Expense extends Model
{
    protected string $table = 'expenses';

    public function allWithCategory(?string $startDate = null, ?string $endDate = null, ?int $categoryId = null): array
    {
        $sql = "
            SELECT e.*, ec.name AS category_name, u.name AS user_name
            FROM `{$this->table}` e
            JOIN expense_categories ec ON ec.id = e.category_id
            JOIN users u ON u.id = e.user_id
            WHERE 1=1
        ";
        $params = [];

        if ($startDate) {
            $sql .= " AND e.expense_date >= :s_date";
            $params[':s_date'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND e.expense_date <= :e_date";
            $params[':e_date'] = $endDate;
        }

        if ($categoryId) {
            $sql .= " AND e.category_id = :cat_id";
            $params[':cat_id'] = $categoryId;
        }

        $sql .= " ORDER BY e.expense_date DESC, e.id DESC";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTotalExpenses(?string $startDate = null, ?string $endDate = null): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) AS total FROM `{$this->table}` WHERE 1=1";
        $params = [];

        if ($startDate) {
            $sql .= " AND expense_date >= :s_date";
            $params[':s_date'] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND expense_date <= :e_date";
            $params[':e_date'] = $endDate;
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return (float)($res['total'] ?? 0);
    }

    public function getExpensesByCategory(?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "
            SELECT ec.id, ec.name, COALESCE(SUM(e.amount), 0) AS total_amount, COUNT(e.id) AS total_count
            FROM expense_categories ec
            LEFT JOIN `{$this->table}` e ON e.category_id = ec.id
        ";
        $params = [];

        if ($startDate && $endDate) {
            $sql .= " AND (e.expense_date BETWEEN :s_date AND :e_date)";
            $params[':s_date'] = $startDate;
            $params[':e_date'] = $endDate;
        }

        $sql .= " GROUP BY ec.id, ec.name ORDER BY total_amount DESC";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
