<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ROOT_PATH', dirname(__DIR__));

// PSR-4 style Class Autoloader
spl_autoload_register(function ($class) {
    $prefixApp = 'App\\';
    $prefixCore = 'Core\\';

    if (str_starts_with($class, $prefixApp)) {
        $relative = substr($class, strlen($prefixApp));
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    if (str_starts_with($class, $prefixCore)) {
        $relative = substr($class, strlen($prefixCore));
        $file = ROOT_PATH . '/core/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load Environment variables from .env
App\Helpers\Env::load(ROOT_PATH . '/.env');

// Load Global functions (url, asset, flash, old, csrf)
require_once ROOT_PATH . '/app/Helpers/functions.php';

// Set Timezone
date_default_timezone_set(App\Helpers\Env::get('APP_TIMEZONE', 'Asia/Jakarta'));

use Core\Request;
use Core\Router;
use App\Helpers\Auth;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PosController;
use App\Controllers\OrderController;
use App\Controllers\PaymentController;
use App\Controllers\KitchenController;
use App\Controllers\ProductController;
use App\Controllers\IngredientController;
use App\Controllers\ExpenseController;
use App\Controllers\ReportController;
use App\Controllers\UserController;
use App\Controllers\SettingController;
use App\Controllers\WebhookController;

$request = new Request();
$router = new Router();

// ==========================================
// ROUTES CONFIGURATION
// ==========================================

// Root Redirect
$router->get('/', function (Request $req) {
    if (!Auth::check()) {
        header('Location: ' . url('/login'));
        exit;
    }
    $role = Auth::role();
    $target = match ($role) {
        'admin' => '/dashboard',
        'kasir' => '/pos',
        'dapur' => '/kitchen',
        default => '/login'
    };
    header('Location: ' . url($target));
    exit;
});

// Authentication
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'loginProcess']);
$router->post('/logout', [AuthController::class, 'logout']);

// Dashboard (Admin only)
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth', 'role:admin']);

// Point of Sale / POS (Admin, Kasir)
$router->get('/pos', [PosController::class, 'index'], ['auth', 'role:admin,kasir']);

// Orders
$router->get('/orders', [OrderController::class, 'index'], ['auth']);
$router->get('/orders/{id}', [OrderController::class, 'show'], ['auth']);
$router->get('/orders/{id}/receipt', [OrderController::class, 'receipt'], ['auth']);
$router->post('/api/orders/create', [OrderController::class, 'create'], ['auth', 'role:admin,kasir', 'csrf']);
$router->post('/api/orders/{id}/status', [OrderController::class, 'updateStatus'], ['auth', 'csrf']);

// Payments API
$router->post('/api/payments/cash', [PaymentController::class, 'payCash'], ['auth', 'role:admin,kasir', 'csrf']);
$router->post('/api/payments/qris', [PaymentController::class, 'createQris'], ['auth', 'role:admin,kasir', 'csrf']);
$router->get('/api/payments/status/{id}', [PaymentController::class, 'checkStatus'], ['auth']);

// Webhook for Midtrans Payment Gateway
$router->post('/api/webhook/midtrans', [WebhookController::class, 'handle']);
$router->post('/payment/webhook', [WebhookController::class, 'handle']);
$router->post('/api/webhook/simulate', [WebhookController::class, 'simulate'], ['auth', 'csrf']);

// Kitchen Display System (KDS)
$router->get('/kitchen', [KitchenController::class, 'index'], ['auth', 'role:admin,dapur']);
$router->get('/api/kitchen/orders', [KitchenController::class, 'orders'], ['auth', 'role:admin,dapur']);

// Products (Admin only)
$router->get('/products', [ProductController::class, 'index'], ['auth', 'role:admin']);
$router->get('/products/create', [ProductController::class, 'create'], ['auth', 'role:admin']);
$router->post('/products', [ProductController::class, 'store'], ['auth', 'role:admin', 'csrf']);
$router->get('/products/{id}/edit', [ProductController::class, 'edit'], ['auth', 'role:admin']);
$router->post('/products/{id}/update', [ProductController::class, 'update'], ['auth', 'role:admin', 'csrf']);
$router->post('/products/{id}/delete', [ProductController::class, 'delete'], ['auth', 'role:admin', 'csrf']);

// Ingredients & Inventory (Admin only)
$router->get('/ingredients', [IngredientController::class, 'index'], ['auth', 'role:admin']);
$router->post('/ingredients', [IngredientController::class, 'store'], ['auth', 'role:admin', 'csrf']);
$router->post('/ingredients/{id}/update', [IngredientController::class, 'update'], ['auth', 'role:admin', 'csrf']);
$router->post('/ingredients/{id}/adjust', [IngredientController::class, 'adjust'], ['auth', 'role:admin', 'csrf']);
$router->post('/ingredients/{id}/delete', [IngredientController::class, 'delete'], ['auth', 'role:admin', 'csrf']);

// Expenses (Admin only)
$router->get('/expenses', [ExpenseController::class, 'index'], ['auth', 'role:admin']);
$router->post('/expenses', [ExpenseController::class, 'store'], ['auth', 'role:admin', 'csrf']);
$router->post('/expenses/{id}/delete', [ExpenseController::class, 'delete'], ['auth', 'role:admin', 'csrf']);

// Financial Reports (Admin only)
$router->get('/reports/sales', [ReportController::class, 'sales'], ['auth', 'role:admin']);
$router->get('/reports/payments', [ReportController::class, 'payments'], ['auth', 'role:admin']);
$router->get('/reports/profit-loss', [ReportController::class, 'profitLoss'], ['auth', 'role:admin']);
$router->get('/reports/inventory', [ReportController::class, 'inventory'], ['auth', 'role:admin']);
$router->get('/reports/export-word', [ReportController::class, 'exportWord'], ['auth', 'role:admin']);

// Staff / Users (Admin only)
$router->get('/users', [UserController::class, 'index'], ['auth', 'role:admin']);
$router->post('/users', [UserController::class, 'store'], ['auth', 'role:admin', 'csrf']);
$router->post('/users/{id}/update', [UserController::class, 'update'], ['auth', 'role:admin', 'csrf']);
$router->post('/users/{id}/delete', [UserController::class, 'delete'], ['auth', 'role:admin', 'csrf']);

// Settings & Logs (Admin only)
$router->get('/settings', [SettingController::class, 'index'], ['auth', 'role:admin']);
$router->post('/settings', [SettingController::class, 'update'], ['auth', 'role:admin', 'csrf']);

// Dispatch Request
$router->dispatch($request);
