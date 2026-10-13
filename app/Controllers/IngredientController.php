<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Helpers\Logger;
use App\Helpers\Auth;

class IngredientController extends Controller
{
    public function index(Request $request): void
    {
        $ingredientModel = new Ingredient();
        $ingredients = $ingredientModel->allWithAlerts();

        $this->render('ingredients/index', [
            'title' => 'Manajemen Bahan Baku & Stok - CafeFlow POS',
            'ingredients' => $ingredients
        ]);
    }

    public function store(Request $request): void
    {
        $this->validateCsrf($request);

        $code = trim((string)$request->post('code'));
        $name = trim((string)$request->post('name'));
        $unit = trim((string)$request->post('unit', 'gram'));
        $stock = (float)$request->post('stock', 0);
        $costPerUnit = (float)$request->post('cost_per_unit', 0);
        $minStock = (float)$request->post('min_stock', 100);

        $ingredientModel = new Ingredient();
        $id = $ingredientModel->create([
            'code' => $code,
            'name' => $name,
            'unit' => $unit,
            'stock' => $stock,
            'cost_per_unit' => $costPerUnit,
            'min_stock' => $minStock
        ]);

        if ($stock > 0) {
            $ingredientModel->adjustStock($id, $stock, 'in', 'initial', (string)$id, 'Stok awal bahan baku', (int)Auth::id());
        }

        Logger::log('Tambah Bahan Baku', 'ingredients', (string)$id, "Bahan baku {$name} ({$code}) ditambahkan.");

        $this->redirect('/ingredients', 'success', "Bahan baku {$name} berhasil ditambahkan!");
    }

    public function update(Request $request, string $id): void
    {
        $this->validateCsrf($request);

        $ingredientModel = new Ingredient();
        $item = $ingredientModel->find((int)$id);
        if (!$item) {
            $this->redirect('/ingredients', 'error', 'Bahan baku tidak ditemukan.');
            return;
        }

        $code = trim((string)$request->post('code'));
        $name = trim((string)$request->post('name'));
        $unit = trim((string)$request->post('unit', 'gram'));
        $costPerUnit = (float)$request->post('cost_per_unit', 0);
        $minStock = (float)$request->post('min_stock', 100);

        $ingredientModel->update($item['id'], [
            'code' => $code,
            'name' => $name,
            'unit' => $unit,
            'cost_per_unit' => $costPerUnit,
            'min_stock' => $minStock
        ]);

        Logger::log('Update Bahan Baku', 'ingredients', (string)$item['id'], "Data bahan baku {$name} diperbarui.");

        $this->redirect('/ingredients', 'success', "Data bahan baku {$name} berhasil diperbarui!");
    }

    public function adjust(Request $request, string $id): void
    {
        $this->validateCsrf($request);

        $ingredientModel = new Ingredient();
        $item = $ingredientModel->find((int)$id);
        if (!$item) {
            $this->redirect('/ingredients', 'error', 'Bahan baku tidak ditemukan.');
            return;
        }

        $qtyChange = (float)$request->post('quantity_change', 0);
        $movementType = $request->post('movement_type', 'in'); // 'in', 'out', 'adjustment'
        $notes = trim((string)$request->post('notes', ''));

        if ($movementType === 'out' && $qtyChange > 0) {
            $qtyChange = -$qtyChange;
        }

        $ingredientModel->adjustStock(
            $item['id'],
            $qtyChange,
            $movementType,
            'manual',
            null,
            $notes,
            (int)Auth::id()
        );

        Logger::log(
            'Penyesuaian Stok Bahan',
            'ingredients',
            (string)$item['id'],
            "Penyesuaian stok {$item['name']}: {$qtyChange} {$item['unit']} ({$movementType})"
        );

        $this->redirect('/ingredients', 'success', "Stok {$item['name']} berhasil diperbarui!");
    }

    public function delete(Request $request, string $id): void
    {
        $this->validateCsrf($request);

        $ingredientModel = new Ingredient();
        $item = $ingredientModel->find((int)$id);
        if ($item) {
            $ingredientModel->delete($item['id']);
            Logger::log('Hapus Bahan Baku', 'ingredients', (string)$item['id'], "Bahan {$item['name']} dihapus.");
        }

        $this->redirect('/ingredients', 'success', 'Bahan baku berhasil dihapus.');
    }
}
