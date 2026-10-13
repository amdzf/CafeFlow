<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use App\Models\Order;
use App\Helpers\Logger;

class KitchenController extends Controller
{
    public function index(Request $request): void
    {
        $orderModel = new Order();
        $orders = $orderModel->getKitchenOrders();

        $this->render('kitchen/index', [
            'title' => 'Kitchen & Bar Display System (KDS) - CafeFlow POS',
            'orders' => $orders
        ]);
    }

    /**
     * Real-time polling API for KDS
     */
    public function orders(Request $request): void
    {
        $orderModel = new Order();
        $orders = $orderModel->getKitchenOrders();

        $this->json([
            'success' => true,
            'count' => count($orders),
            'orders' => $orders
        ]);
    }
}
