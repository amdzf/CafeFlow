<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Model;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\Expense;
use PDO;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $db = Model::db();
        $expenseModel = new Expense();

        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('-6 days'));
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        // ==============================================================
        // 1. STATISTIK HARIAN (HARI INI)
        // ==============================================================
        $stmtDaily = $db->query("
            SELECT 
                COALESCE(SUM(total_amount), 0) AS omzet,
                COUNT(id) AS trx_count,
                COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN payment_method = 'qris' THEN total_amount ELSE 0 END), 0) AS qris_total,
                COALESCE(SUM(total_cost), 0) AS hpp_total,
                COALESCE(SUM(subtotal - discount_amount), 0) AS net_sales
            FROM orders
            WHERE payment_status = 'paid' AND DATE(paid_at) = CURDATE()
        ");
        $statDaily = $stmtDaily->fetch();
        $expenseDaily = $expenseModel->getTotalExpenses($today, $today);
        $statDaily['expenses'] = $expenseDaily;
        $statDaily['net_profit'] = (float)$statDaily['net_sales'] - (float)$statDaily['hpp_total'] - $expenseDaily;

        // ==============================================================
        // 2. STATISTIK MINGGUAN (7 HARI TERAKHIR)
        // ==============================================================
        $stmtWeekly = $db->prepare("
            SELECT 
                COALESCE(SUM(total_amount), 0) AS omzet,
                COUNT(id) AS trx_count,
                COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN payment_method = 'qris' THEN total_amount ELSE 0 END), 0) AS qris_total,
                COALESCE(SUM(total_cost), 0) AS hpp_total,
                COALESCE(SUM(subtotal - discount_amount), 0) AS net_sales
            FROM orders
            WHERE payment_status = 'paid' AND (DATE(paid_at) BETWEEN :s_date AND :e_date)
        ");
        $stmtWeekly->execute([':s_date' => $weekStart, ':e_date' => $today]);
        $statWeekly = $stmtWeekly->fetch();
        $expenseWeekly = $expenseModel->getTotalExpenses($weekStart, $today);
        $statWeekly['expenses'] = $expenseWeekly;
        $statWeekly['net_profit'] = (float)$statWeekly['net_sales'] - (float)$statWeekly['hpp_total'] - $expenseWeekly;

        // ==============================================================
        // 3. STATISTIK BULANAN (BULAN INI)
        // ==============================================================
        $stmtMonthly = $db->prepare("
            SELECT 
                COALESCE(SUM(total_amount), 0) AS omzet,
                COUNT(id) AS trx_count,
                COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN payment_method = 'qris' THEN total_amount ELSE 0 END), 0) AS qris_total,
                COALESCE(SUM(total_cost), 0) AS hpp_total,
                COALESCE(SUM(subtotal - discount_amount), 0) AS net_sales
            FROM orders
            WHERE payment_status = 'paid' AND (DATE(paid_at) BETWEEN :s_date AND :e_date)
        ");
        $stmtMonthly->execute([':s_date' => $monthStart, ':e_date' => $monthEnd]);
        $statMonthly = $stmtMonthly->fetch();
        $expenseMonthly = $expenseModel->getTotalExpenses($monthStart, $monthEnd);
        $statMonthly['expenses'] = $expenseMonthly;
        $statMonthly['net_profit'] = (float)$statMonthly['net_sales'] - (float)$statMonthly['hpp_total'] - $expenseMonthly;

        // ==============================================================
        // 4. FILTER RENTANG TANGGAL (CUSTOM / SELECTED)
        // ==============================================================
        $range = $request->get('range', 'today'); // 'today', 'week', 'month', 'custom'
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($range === 'today') {
            $startDate = $today;
            $endDate = $today;
        } elseif ($range === 'week') {
            $startDate = $weekStart;
            $endDate = $today;
        } elseif ($range === 'month') {
            $startDate = $monthStart;
            $endDate = $monthEnd;
        } elseif (!$startDate || !$endDate) {
            $startDate = $today;
            $endDate = $today;
        }

        $stmtFiltered = $db->prepare("
            SELECT 
                COALESCE(SUM(total_amount), 0) AS omzet,
                COUNT(id) AS trx_count,
                COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN payment_method = 'qris' THEN total_amount ELSE 0 END), 0) AS qris_total,
                COALESCE(SUM(total_cost), 0) AS hpp_total,
                COALESCE(SUM(subtotal - discount_amount), 0) AS net_sales
            FROM orders
            WHERE payment_status = 'paid' AND (DATE(paid_at) BETWEEN :s_date AND :e_date)
        ");
        $stmtFiltered->execute([':s_date' => $startDate, ':e_date' => $endDate]);
        $kpiFiltered = $stmtFiltered->fetch();

        $expenseFiltered = $expenseModel->getTotalExpenses($startDate, $endDate);
        $netProfitFiltered = (float)$kpiFiltered['net_sales'] - (float)$kpiFiltered['hpp_total'] - $expenseFiltered;

        // ==============================================================
        // 5. TOP 5 PRODUK TERLARIS (PERIODE FILTER)
        // ==============================================================
        $stmtTop = $db->prepare("
            SELECT oi.product_name, SUM(oi.quantity) AS total_sold, SUM(oi.subtotal) AS total_revenue
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            WHERE o.payment_status = 'paid' AND (DATE(o.paid_at) BETWEEN :s_date AND :e_date)
            GROUP BY oi.product_id, oi.product_name
            ORDER BY total_sold DESC
            LIMIT 5
        ");
        $stmtTop->execute([':s_date' => $startDate, ':e_date' => $endDate]);
        $topProducts = $stmtTop->fetchAll();

        // 6. LOW STOCK ALERTS
        $productModel = new Product();
        $ingredientModel = new Ingredient();
        $lowStockProducts = $productModel->getLowStockDirect();
        $lowStockIngredients = $ingredientModel->getLowStock();

        // 7. TRANSAKSI TERBARU
        $stmtRecent = $db->query("
            SELECT o.*, t.table_number, u.name AS cashier_name
            FROM orders o
            LEFT JOIN tables t ON t.id = o.table_id
            LEFT JOIN users u ON u.id = o.user_id
            ORDER BY o.id DESC
            LIMIT 8
        ");
        $recentOrders = $stmtRecent->fetchAll();

        // ==============================================================
        // 8. DATA GRAFIK CHART.JS (HARIAN, MINGGUAN, BULANAN)
        // ==============================================================
        
        // A. Harian: 7 Hari Terakhir
        $chartDailyLabels = [];
        $chartDailySales = [];
        $chartDailyOrders = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $chartDailyLabels[] = date('d M', strtotime($d));

            $stmtDay = $db->prepare("
                SELECT COALESCE(SUM(total_amount), 0) AS total, COUNT(id) AS cnt
                FROM orders 
                WHERE payment_status = 'paid' AND DATE(paid_at) = :d
            ");
            $stmtDay->execute([':d' => $d]);
            $dayRes = $stmtDay->fetch();
            $chartDailySales[] = (float)($dayRes['total'] ?? 0);
            $chartDailyOrders[] = (int)($dayRes['cnt'] ?? 0);
        }

        // B. Mingguan: 4 Minggu Terakhir
        $chartWeeklyLabels = ['3 Minggu Lalu', '2 Minggu Lalu', 'Minggu Lalu', 'Minggu Ini'];
        $chartWeeklySales = [];
        $chartWeeklyOrders = [];
        for ($w = 3; $w >= 0; $w--) {
            $wStart = date('Y-m-d', strtotime("-" . (($w + 1) * 7 - 1) . " days"));
            $wEnd = date('Y-m-d', strtotime("-" . ($w * 7) . " days"));

            $stmtWk = $db->prepare("
                SELECT COALESCE(SUM(total_amount), 0) AS total, COUNT(id) AS cnt
                FROM orders 
                WHERE payment_status = 'paid' AND (DATE(paid_at) BETWEEN :ws AND :we)
            ");
            $stmtWk->execute([':ws' => $wStart, ':we' => $wEnd]);
            $wkRes = $stmtWk->fetch();
            $chartWeeklySales[] = (float)($wkRes['total'] ?? 0);
            $chartWeeklyOrders[] = (int)($wkRes['cnt'] ?? 0);
        }

        // C. Bulanan: 12 Bulan Tahun Ini
        $chartMonthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $chartMonthlySales = array_fill(0, 12, 0);
        $chartMonthlyOrders = array_fill(0, 12, 0);

        $stmtMonth = $db->query("
            SELECT MONTH(paid_at) AS m, SUM(total_amount) AS total, COUNT(id) AS cnt
            FROM orders
            WHERE payment_status = 'paid' AND YEAR(paid_at) = YEAR(CURDATE())
            GROUP BY MONTH(paid_at)
        ");
        while ($row = $stmtMonth->fetch()) {
            $mIndex = (int)$row['m'] - 1;
            if (isset($chartMonthlySales[$mIndex])) {
                $chartMonthlySales[$mIndex] = (float)$row['total'];
                $chartMonthlyOrders[$mIndex] = (int)$row['cnt'];
            }
        }

        $this->render('dashboard/index', [
            'title' => 'Dashboard Utama - CafeFlow POS',
            'range' => $range,
            'startDate' => $startDate,
            'endDate' => $endDate,
            // Statistik Tiga Periode
            'statDaily' => $statDaily,
            'statWeekly' => $statWeekly,
            'statMonthly' => $statMonthly,
            // Filtered values
            'kpiFiltered' => $kpiFiltered,
            'expenseFiltered' => $expenseFiltered,
            'netProfitFiltered' => $netProfitFiltered,
            // Data Tabel & List
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'lowStockIngredients' => $lowStockIngredients,
            'recentOrders' => $recentOrders,
            // Data Grafik Lengkap
            'chartDailyLabels' => $chartDailyLabels,
            'chartDailySales' => $chartDailySales,
            'chartDailyOrders' => $chartDailyOrders,
            'chartWeeklyLabels' => $chartWeeklyLabels,
            'chartWeeklySales' => $chartWeeklySales,
            'chartWeeklyOrders' => $chartWeeklyOrders,
            'chartMonthlyLabels' => $chartMonthlyLabels,
            'chartMonthlySales' => $chartMonthlySales,
            'chartMonthlyOrders' => $chartMonthlyOrders
        ]);
    }
}
