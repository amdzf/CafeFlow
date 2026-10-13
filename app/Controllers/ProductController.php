<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\ProductRecipe;
use App\Helpers\Logger;
use App\Helpers\Auth;

class ProductController extends Controller
{
    public function index(Request $request): void
    {
        $productModel = new Product();
        $categoryModel = new Category();

        $catId = $request->get('category_id') ? (int)$request->get('category_id') : null;
        $search = $request->get('search') ? trim((string)$request->get('search')) : null;

        $products = $productModel->allWithCategory($catId, $search);
        $categories = $categoryModel->allActive();

        $this->render('products/index', [
            'title' => 'Manajemen Produk - CafeFlow POS',
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $catId,
            'search' => $search
        ]);
    }

    public function create(Request $request): void
    {
        $categoryModel = new Category();
        $ingredientModel = new Ingredient();

        $this->render('products/create', [
            'title' => 'Tambah Produk Baru - CafeFlow POS',
            'categories' => $categoryModel->allActive(),
            'ingredients' => $ingredientModel->all('name ASC')
        ]);
    }

    public function store(Request $request): void
    {
        $this->validateCsrf($request);

        $sku = trim((string)$request->post('sku'));
        $name = trim((string)$request->post('name'));
        $categoryId = (int)$request->post('category_id');
        $price = (float)$request->post('price');
        $costPrice = (float)$request->post('cost_price');
        $stockType = $request->post('stock_type') === 'recipe' ? 'recipe' : 'direct';
        $stock = (int)$request->post('stock', 0);
        $minStock = (int)$request->post('min_stock', 5);
        $unit = trim((string)$request->post('unit', 'porsi'));
        $status = $request->post('status') === 'habis' ? 'habis' : 'tersedia';
        $description = trim((string)$request->post('description', ''));

        $errors = $this->validate([
            'sku' => $sku,
            'name' => $name,
            'price' => $price
        ], [
            'sku' => 'required',
            'name' => 'required',
            'price' => 'required|numeric'
        ]);

        if (!empty($errors)) {
            $this->back('error', 'Silakan lengkapi data produk dengan benar.');
            return;
        }

        // Handle Image
        $imageName = $this->handleImageUpload($request);

        $productModel = new Product();
        $productId = $productModel->create([
            'category_id' => $categoryId,
            'sku' => $sku,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'cost_price' => $costPrice,
            'stock_type' => $stockType,
            'stock' => $stock,
            'min_stock' => $minStock,
            'unit' => $unit,
            'image' => $imageName,
            'status' => $status,
            'is_active' => 1
        ]);

        // If recipe stock type, save recipes
        if ($stockType === 'recipe') {
            $recipeIngredients = $request->post('recipes', []);
            if (is_array($recipeIngredients)) {
                $recipeModel = new ProductRecipe();
                $recipeModel->syncRecipes($productId, $recipeIngredients);
            }
        }

        Logger::log('Tambah Produk', 'products', (string)$productId, "Produk {$name} ({$sku}) berhasil ditambahkan.");

        $this->redirect('/products', 'success', "Produk {$name} berhasil ditambahkan!");
    }

    public function edit(Request $request, string $id): void
    {
        $productModel = new Product();
        $product = $productModel->findWithRecipes((int)$id);

        if (!$product) {
            $this->redirect('/products', 'error', 'Produk tidak ditemukan.');
            return;
        }

        $categoryModel = new Category();
        $ingredientModel = new Ingredient();

        $this->render('products/edit', [
            'title' => "Edit Produk: {$product['name']} - CafeFlow POS",
            'product' => $product,
            'categories' => $categoryModel->allActive(),
            'ingredients' => $ingredientModel->all('name ASC')
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $this->validateCsrf($request);

        $productModel = new Product();
        $product = $productModel->find((int)$id);
        if (!$product) {
            $this->redirect('/products', 'error', 'Produk tidak ditemukan.');
            return;
        }

        $sku = trim((string)$request->post('sku'));
        $name = trim((string)$request->post('name'));
        $categoryId = (int)$request->post('category_id');
        $price = (float)$request->post('price');
        $costPrice = (float)$request->post('cost_price');
        $stockType = $request->post('stock_type') === 'recipe' ? 'recipe' : 'direct';
        $stock = (int)$request->post('stock', 0);
        $minStock = (int)$request->post('min_stock', 5);
        $unit = trim((string)$request->post('unit', 'porsi'));
        $status = $request->post('status') === 'habis' ? 'habis' : 'tersedia';
        $description = trim((string)$request->post('description', ''));

        $imageName = $this->handleImageUpload($request) ?? $product['image'];

        $productModel->update($product['id'], [
            'category_id' => $categoryId,
            'sku' => $sku,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'cost_price' => $costPrice,
            'stock_type' => $stockType,
            'stock' => $stock,
            'min_stock' => $minStock,
            'unit' => $unit,
            'image' => $imageName,
            'status' => $status
        ]);

        if ($stockType === 'recipe') {
            $recipeIngredients = $request->post('recipes', []);
            if (is_array($recipeIngredients)) {
                $recipeModel = new ProductRecipe();
                $recipeModel->syncRecipes($product['id'], $recipeIngredients);
            }
        }

        Logger::log('Update Produk', 'products', (string)$product['id'], "Produk {$name} berhasil diperbarui.");

        $this->redirect('/products', 'success', "Data produk {$name} berhasil diperbarui!");
    }

    public function delete(Request $request, string $id): void
    {
        $this->validateCsrf($request);

        $productModel = new Product();
        $product = $productModel->find((int)$id);
        if (!$product) {
            $this->redirect('/products', 'error', 'Produk tidak ditemukan.');
            return;
        }

        // Soft deactivate so historical orders remain valid
        $productModel->update($product['id'], ['is_active' => 0]);
        Logger::log('Hapus Produk', 'products', (string)$product['id'], "Produk {$product['name']} dinonaktifkan.");

        $this->redirect('/products', 'success', "Produk {$product['name']} berhasil dihapus.");
    }

    private function handleImageUpload(Request $request): ?string
    {
        $file = $request->file('image_file');
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed, true)) {
                $newName = 'prod_' . uniqid() . '.' . $ext;
                $targetDir = dirname(__DIR__, 2) . '/public/images/products/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                if (move_uploaded_file($file['tmp_name'], $targetDir . $newName)) {
                    return $newName;
                }
            }
        }

        $preset = $request->post('image_preset');
        if (!empty($preset)) {
            return trim($preset);
        }

        return null;
    }
}
