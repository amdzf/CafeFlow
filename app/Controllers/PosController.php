<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Table;
use App\Models\Order;
use App\Models\Setting;
use App\Helpers\Auth;

class PosController extends Controller
{
    public function index(Request $request): void
    {
        $productModel = new Product();
        $categoryModel = new Category();
        $tableModel = new Table();
        $orderModel = new Order();
        $settingModel = new Setting();

        $products = $productModel->allForPos();
        $categories = $categoryModel->allActive();
        $tables = $tableModel->allActive();
        $nextOrderNumber = $orderModel->generateOrderNumber();

        $taxPercent = (float)$settingModel->get('tax_percent', 10);
        $servicePercent = (float)$settingModel->get('service_charge_percent', 0);
        $currency = $settingModel->get('currency_symbol', 'Rp');

        $this->render('pos/index', [
            'title' => 'Point of Sale (Kasir) - CafeFlow POS',
            'products' => $products,
            'categories' => $categories,
            'tables' => $tables,
            'nextOrderNumber' => $nextOrderNumber,
            'taxPercent' => $taxPercent,
            'servicePercent' => $servicePercent,
            'currency' => $currency
        ]);
    }
}
