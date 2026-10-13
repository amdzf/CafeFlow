<?php

namespace App\Models;

use Core\Model;

class ProductRecipe extends Model
{
    protected string $table = 'product_recipes';

    public function syncRecipes(int $productId, array $ingredients): void
    {
        // Delete existing recipes for this product
        $stmtDel = self::db()->prepare("DELETE FROM `{$this->table}` WHERE product_id = :pid");
        $stmtDel->execute([':pid' => $productId]);

        // Insert new recipes
        $stmtIns = self::db()->prepare("
            INSERT INTO `{$this->table}` (product_id, ingredient_id, quantity)
            VALUES (:pid, :iid, :qty)
        ");

        foreach ($ingredients as $item) {
            $iid = (int)($item['ingredient_id'] ?? 0);
            $qty = (float)($item['quantity'] ?? 0);
            if ($iid > 0 && $qty > 0) {
                $stmtIns->execute([
                    ':pid' => $productId,
                    ':iid' => $iid,
                    ':qty' => $qty
                ]);
            }
        }
    }
}
