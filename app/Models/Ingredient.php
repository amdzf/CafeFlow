<?php

namespace App\Models;

use Core\Model;

class Ingredient extends Model
{
    protected string $table = 'ingredients';

    public function allWithAlerts(): array
    {
        $stmt = self::db()->query("
            SELECT *, 
                   (CASE WHEN stock <= min_stock THEN 1 ELSE 0 END) AS is_low_stock
            FROM `{$this->table}`
            ORDER BY is_low_stock DESC, name ASC
        ");
        return $stmt->fetchAll();
    }

    public function getLowStock(): array
    {
        $stmt = self::db()->query("
            SELECT * FROM `{$this->table}`
            WHERE stock <= min_stock
            ORDER BY stock ASC
        ");
        return $stmt->fetchAll();
    }

    public function adjustStock(int $id, float $qtyChange, string $movementType, ?string $refType = null, ?string $refId = null, ?string $notes = null, ?int $userId = null): bool
    {
        $item = $this->find($id);
        if (!$item) return false;

        $previous = (float)$item['stock'];
        $newStock = max(0, $previous + $qtyChange);

        $stmt = self::db()->prepare("UPDATE `{$this->table}` SET stock = :new_stock WHERE id = :id");
        $stmt->execute([':new_stock' => $newStock, ':id' => $id]);

        // Insert inventory movement
        $stmtMove = self::db()->prepare("
            INSERT INTO inventory_movements 
            (item_type, item_id, movement_type, quantity, previous_stock, new_stock, reference_type, reference_id, notes, created_by)
            VALUES ('ingredient', :item_id, :m_type, :qty, :prev, :new, :ref_type, :ref_id, :notes, :user_id)
        ");
        $stmtMove->execute([
            ':item_id' => $id,
            ':m_type' => $movementType,
            ':qty' => abs($qtyChange),
            ':prev' => $previous,
            ':new' => $newStock,
            ':ref_type' => $refType,
            ':ref_id' => $refId,
            ':notes' => $notes,
            ':user_id' => $userId
        ]);

        return true;
    }
}
