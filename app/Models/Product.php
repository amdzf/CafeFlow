<?php

namespace App\Models;

use Core\Model;

class Product extends Model
{
    protected string $table = 'products';

    public function allWithCategory(?int $categoryId = null, ?string $search = null): array
    {
        $sql = "
            SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                   (CASE 
                        WHEN p.stock_type = 'direct' AND p.stock <= p.min_stock THEN 1 
                        ELSE 0 
                    END) AS is_low_stock
            FROM `{$this->table}` p
            JOIN categories c ON c.id = p.category_id
            WHERE p.is_active = 1
        ";
        $params = [];

        if ($categoryId) {
            $sql .= " AND p.category_id = :cat_id";
            $params[':cat_id'] = $categoryId;
        }

        if ($search) {
            $sql .= " AND (p.name LIKE :search OR p.sku LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY c.sort_order ASC, p.name ASC";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function allForPos(): array
    {
        $stmt = self::db()->query("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM `{$this->table}` p
            JOIN categories c ON c.id = p.category_id
            WHERE p.is_active = 1 AND p.status = 'tersedia'
            ORDER BY c.sort_order ASC, p.name ASC
        ");
        $products = $stmt->fetchAll();

        // Check recipe ingredients availability
        foreach ($products as &$p) {
            if ($p['stock_type'] === 'recipe') {
                $p['is_available'] = $this->checkRecipeAvailability($p['id']);
                $p['display_stock'] = $p['is_available'] ? 'Tersedia (Resep)' : 'Bahan Habis';
            } else {
                $p['is_available'] = ($p['stock'] > 0);
                $p['display_stock'] = $p['stock'] . ' ' . $p['unit'];
            }
        }

        return $products;
    }

    public function checkRecipeAvailability(int $productId): bool
    {
        $stmt = self::db()->prepare("
            SELECT pr.quantity, i.stock
            FROM product_recipes pr
            JOIN ingredients i ON i.id = pr.ingredient_id
            WHERE pr.product_id = :pid
        ");
        $stmt->execute([':pid' => $productId]);
        $recipes = $stmt->fetchAll();

        if (empty($recipes)) {
            return true; // No recipe required, available
        }

        foreach ($recipes as $r) {
            if ((float)$r['stock'] < (float)$r['quantity']) {
                return false; // Insufficient ingredient
            }
        }
        return true;
    }

    public function findWithRecipes(int $id): ?array
    {
        $product = $this->find($id);
        if (!$product) return null;

        $stmt = self::db()->prepare("
            SELECT pr.*, i.name AS ingredient_name, i.unit AS ingredient_unit, i.stock AS ingredient_stock, i.cost_per_unit
            FROM product_recipes pr
            JOIN ingredients i ON i.id = pr.ingredient_id
            WHERE pr.product_id = :id
            ORDER BY i.name ASC
        ");
        $stmt->execute([':id' => $id]);
        $product['recipes'] = $stmt->fetchAll();

        return $product;
    }

    public function getLowStockDirect(): array
    {
        $stmt = self::db()->query("
            SELECT p.*, c.name as category_name 
            FROM `{$this->table}` p
            JOIN categories c ON c.id = p.category_id
            WHERE p.is_active = 1 AND p.stock_type = 'direct' AND p.stock <= p.min_stock
            ORDER BY p.stock ASC
        ");
        return $stmt->fetchAll();
    }

    public function adjustDirectStock(int $id, int $qtyChange, string $movementType, ?string $refType = null, ?string $refId = null, ?string $notes = null, ?int $userId = null): bool
    {
        $item = $this->find($id);
        if (!$item || $item['stock_type'] !== 'direct') return false;

        $previous = (int)$item['stock'];
        $newStock = max(0, $previous + $qtyChange);

        $stmt = self::db()->prepare("UPDATE `{$this->table}` SET stock = :new_stock WHERE id = :id");
        $stmt->execute([':new_stock' => $newStock, ':id' => $id]);

        $stmtMove = self::db()->prepare("
            INSERT INTO inventory_movements 
            (item_type, item_id, movement_type, quantity, previous_stock, new_stock, reference_type, reference_id, notes, created_by)
            VALUES ('product', :item_id, :m_type, :qty, :prev, :new, :ref_type, :ref_id, :notes, :user_id)
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
