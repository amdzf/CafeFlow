<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Model;
use App\Models\Expense;
use App\Models\InventoryMovement;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Setting;
use PDO;

class ReportController extends Controller
{
    /**
     * 1. Laporan Penjualan (Sales Report)
     */
    public function sales(Request $request): void
    {
        $db = Model::db();
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        // Overall Sales Summary
        $stmtSummary = $db->prepare("
            SELECT 
                COALESCE(SUM(subtotal), 0) AS gross_sales,
                COALESCE(SUM(discount_amount), 0) AS total_discount,
                COALESCE(SUM(subtotal - discount_amount), 0) AS net_sales,
                COALESCE(SUM(tax_amount), 0) AS total_tax,
                COALESCE(SUM(service_charge), 0) AS total_service,
                COALESCE(SUM(total_amount), 0) AS grand_total,
                COUNT(id) AS total_transactions,
                COALESCE(AVG(total_amount), 0) AS avg_order_value
            FROM orders
            WHERE payment_status = 'paid' AND (DATE(paid_at) BETWEEN :s_date AND :e_date)
        ");
        $stmtSummary->execute([':s_date' => $startDate, ':e_date' => $endDate]);
        $summary = $stmtSummary->fetch();

        // Product Breakdown
        $stmtProducts = $db->prepare("
            SELECT oi.product_id, oi.product_name, c.name AS category_name,
                   SUM(oi.quantity) AS qty_sold,
                   SUM(oi.subtotal) AS total_sales,
                   SUM(oi.cost_price * oi.quantity) AS total_hpp,
                   SUM(oi.subtotal - (oi.cost_price * oi.quantity)) AS gross_profit
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            JOIN products p ON p.id = oi.product_id
            JOIN categories c ON c.id = p.category_id
            WHERE o.payment_status = 'paid' AND (DATE(o.paid_at) BETWEEN :s_date AND :e_date)
            GROUP BY oi.product_id, oi.product_name, c.name
            ORDER BY total_sales DESC
        ");
        $stmtProducts->execute([':s_date' => $startDate, ':e_date' => $endDate]);
        $productSales = $stmtProducts->fetchAll();

        // Category Breakdown
        $stmtCategories = $db->prepare("
            SELECT c.name AS category_name,
                   SUM(oi.quantity) AS qty_sold,
                   SUM(oi.subtotal) AS total_sales
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            JOIN products p ON p.id = oi.product_id
            JOIN categories c ON c.id = p.category_id
            WHERE o.payment_status = 'paid' AND (DATE(o.paid_at) BETWEEN :s_date AND :e_date)
            GROUP BY c.id, c.name
            ORDER BY total_sales DESC
        ");
        $stmtCategories->execute([':s_date' => $startDate, ':e_date' => $endDate]);
        $categorySales = $stmtCategories->fetchAll();

        // Check if export CSV requested
        if ($request->get('export') === 'csv') {
            $this->exportSalesCsv($summary, $productSales, $startDate, $endDate);
            return;
        }

        $this->render('reports/sales', [
            'title' => 'Laporan Penjualan - CafeFlow POS',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
            'productSales' => $productSales,
            'categorySales' => $categorySales
        ]);
    }

    /**
     * 2. Laporan Metode Pembayaran (Payment Methods)
     */
    public function payments(Request $request): void
    {
        $db = Model::db();
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        // Payment status counts & totals
        $stmtMethods = $db->prepare("
            SELECT 
                payment_method,
                status,
                COUNT(id) AS total_count,
                COALESCE(SUM(amount), 0) AS total_amount
            FROM payments
            WHERE (DATE(created_at) BETWEEN :s_date AND :e_date)
            GROUP BY payment_method, status
        ");
        $stmtMethods->execute([':s_date' => $startDate, ':e_date' => $endDate]);
        $breakdowns = $stmtMethods->fetchAll();

        // Daily Recap
        $stmtDaily = $db->prepare("
            SELECT 
                DATE(created_at) AS trx_date,
                SUM(CASE WHEN payment_method = 'cash' AND status = 'paid' THEN amount ELSE 0 END) AS cash_paid,
                SUM(CASE WHEN payment_method = 'qris' AND status = 'paid' THEN amount ELSE 0 END) AS qris_paid,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) AS pending_amount,
                SUM(CASE WHEN status = 'failed' OR status = 'expired' THEN amount ELSE 0 END) AS failed_amount,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS total_paid
            FROM payments
            WHERE (DATE(created_at) BETWEEN :s_date AND :e_date)
            GROUP BY DATE(created_at)
            ORDER BY trx_date DESC
        ");
        $stmtDaily->execute([':s_date' => $startDate, ':e_date' => $endDate]);
        $dailyRecap = $stmtDaily->fetchAll();

        $this->render('reports/payments', [
            'title' => 'Laporan Metode Pembayaran - CafeFlow POS',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'breakdowns' => $breakdowns,
            'dailyRecap' => $dailyRecap
        ]);
    }

    /**
     * 3. Laporan Laba Rugi (Profit and Loss / P&L)
     */
    public function profitLoss(Request $request): void
    {
        $db = Model::db();
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        // Sales Revenue & Cost of Goods Sold (HPP)
        $stmtSales = $db->prepare("
            SELECT 
                COALESCE(SUM(subtotal), 0) AS gross_sales,
                COALESCE(SUM(discount_amount), 0) AS total_discounts,
                COALESCE(SUM(subtotal - discount_amount), 0) AS net_sales,
                COALESCE(SUM(total_cost), 0) AS total_hpp,
                COALESCE(SUM(subtotal - discount_amount - total_cost), 0) AS gross_profit
            FROM orders
            WHERE payment_status = 'paid' AND (DATE(paid_at) BETWEEN :s_date AND :e_date)
        ");
        $stmtSales->execute([':s_date' => $startDate, ':e_date' => $endDate]);
        $pnlSales = $stmtSales->fetch();

        // Operational Expenses Breakdown
        $expenseModel = new Expense();
        $expensesByCategory = $expenseModel->getExpensesByCategory($startDate, $endDate);
        $totalExpenses = $expenseModel->getTotalExpenses($startDate, $endDate);

        $grossProfit = (float)$pnlSales['gross_profit'];
        $netProfit = $grossProfit - $totalExpenses;

        $this->render('reports/profit_loss', [
            'title' => 'Laporan Laba Rugi (P&L) - CafeFlow POS',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'pnlSales' => $pnlSales,
            'expensesByCategory' => $expensesByCategory,
            'totalExpenses' => $totalExpenses,
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit
        ]);
    }

    /**
     * 4. Laporan Stok & Mutasi
     */
    public function inventory(Request $request): void
    {
        $invModel = new InventoryMovement();
        $ingModel = new Ingredient();
        $prodModel = new Product();

        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $itemType = $request->get('item_type'); // 'ingredient', 'product', or null

        $movements = $invModel->allWithDetails($startDate, $endDate, $itemType);
        $ingredients = $ingModel->allWithAlerts();
        $products = $prodModel->allWithCategory();

        $this->render('reports/inventory', [
            'title' => 'Laporan Stok & Mutasi Inventaris - CafeFlow POS',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'itemType' => $itemType,
            'movements' => $movements,
            'ingredients' => $ingredients,
            'products' => $products
        ]);
    }

    private function exportSalesCsv(array $summary, array $productSales, string $startDate, string $endDate): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=Laporan_Penjualan_{$startDate}_{$endDate}.csv");

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['LAPORAN PENJUALAN CAFEFLOW POS']);
        fputcsv($output, ["Periode: {$startDate} s/d {$endDate}"]);
        fputcsv($output, []);

        fputcsv($output, ['RINGKASAN PENJUALAN']);
        fputcsv($output, ['Omzet Bruto', number_format((float)$summary['gross_sales'], 2, '.', '')]);
        fputcsv($output, ['Total Diskon', number_format((float)$summary['total_discount'], 2, '.', '')]);
        fputcsv($output, ['Penjualan Bersih', number_format((float)$summary['net_sales'], 2, '.', '')]);
        fputcsv($output, ['Total Pajak (PPN)', number_format((float)$summary['total_tax'], 2, '.', '')]);
        fputcsv($output, ['Biaya Layanan', number_format((float)$summary['total_service'], 2, '.', '')]);
        fputcsv($output, ['Total Penerimaan', number_format((float)$summary['grand_total'], 2, '.', '')]);
        fputcsv($output, ['Jumlah Transaksi', $summary['total_transactions']]);
        fputcsv($output, ['Rata-rata Transaksi (AOV)', number_format((float)$summary['avg_order_value'], 2, '.', '')]);
        fputcsv($output, []);

        fputcsv($output, ['DETAIL PENJUALAN PER PRODUK']);
        fputcsv($output, ['ID', 'Nama Produk', 'Kategori', 'Qty Terjual', 'Total Omzet (Rp)', 'Total HPP (Rp)', 'Laba Kotor (Rp)']);

        foreach ($productSales as $p) {
            fputcsv($output, [
                $p['product_id'],
                $p['product_name'],
                $p['category_name'],
                $p['qty_sold'],
                number_format((float)$p['total_sales'], 2, '.', ''),
                number_format((float)$p['total_hpp'], 2, '.', ''),
                number_format((float)$p['gross_profit'], 2, '.', ''),
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Ekspor Laporan Transaksi ke Format Microsoft Word (.doc)
     * Mendukung periode: Harian, Mingguan, Bulanan, dan Custom
     */
    public function exportWord(Request $request): void
    {
        $db = Model::db();
        $settingModel = new Setting();
        $cafeName = $settingModel->get('cafe_name', 'CafeFlow Coffee & Eatery');
        $cafeTagline = $settingModel->get('cafe_tagline', 'Artisan Brews & Cozy Vibes');
        $cafeAddress = $settingModel->get('cafe_address', 'Jl. Senopati No. 45, Jakarta Selatan');
        $cafePhone = $settingModel->get('cafe_phone', '0812-3456-7890');

        $period = strtolower($request->get('period', 'daily')); // daily, weekly, monthly, custom

        $today = date('Y-m-d');
        if ($period === 'daily') {
            $date = $request->get('date', $today);
            $startDate = $date;
            $endDate = $date;
            $periodLabel = "Harian (" . date('d F Y', strtotime($date)) . ")";
            $filePeriod = "Harian_" . date('Ymd', strtotime($date));
        } elseif ($period === 'weekly') {
            $startDate = $request->get('start_date', date('Y-m-d', strtotime('-6 days')));
            $endDate = $request->get('end_date', $today);
            $periodLabel = "Mingguan (" . date('d M Y', strtotime($startDate)) . " s/d " . date('d M Y', strtotime($endDate)) . ")";
            $filePeriod = "Mingguan_" . date('Ymd', strtotime($startDate)) . "_" . date('Ymd', strtotime($endDate));
        } elseif ($period === 'monthly') {
            $month = $request->get('month', date('Y-m'));
            $startDate = date('Y-m-01', strtotime($month . '-01'));
            $endDate = date('Y-m-t', strtotime($month . '-01'));
            $periodLabel = "Bulanan (" . date('F Y', strtotime($month . '-01')) . ")";
            $filePeriod = "Bulanan_" . date('Ym', strtotime($month . '-01'));
        } else {
            $startDate = $request->get('start_date', $today);
            $endDate = $request->get('end_date', $today);
            $periodLabel = "Periode " . date('d M Y', strtotime($startDate)) . " s/d " . date('d M Y', strtotime($endDate));
            $filePeriod = "Custom_" . date('Ymd', strtotime($startDate)) . "_" . date('Ymd', strtotime($endDate));
        }

        // 1. Ringkasan Finansial
        $stmtSummary = $db->prepare("
            SELECT 
                COALESCE(SUM(subtotal), 0) AS gross_sales,
                COALESCE(SUM(discount_amount), 0) AS total_discount,
                COALESCE(SUM(subtotal - discount_amount), 0) AS net_sales,
                COALESCE(SUM(tax_amount), 0) AS total_tax,
                COALESCE(SUM(service_charge), 0) AS total_service,
                COALESCE(SUM(total_amount), 0) AS grand_total,
                COUNT(id) AS total_transactions,
                COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN payment_method = 'qris' THEN total_amount ELSE 0 END), 0) AS qris_total,
                COALESCE(SUM(total_cost), 0) AS total_hpp,
                COALESCE(AVG(total_amount), 0) AS avg_order_value
            FROM orders
            WHERE payment_status = 'paid' AND (DATE(paid_at) BETWEEN :s_date AND :e_date)
        ");
        $stmtSummary->execute([':s_date' => $startDate, ':e_date' => $endDate]);
        $summary = $stmtSummary->fetch();

        // 2. Daftar Transaksi
        $stmtOrders = $db->prepare("
            SELECT o.*, t.table_number, u.name AS cashier_name,
                   (SELECT GROUP_CONCAT(CONCAT(quantity, 'x ', product_name) SEPARATOR ', ') FROM order_items WHERE order_id = o.id) AS items_summary
            FROM orders o
            LEFT JOIN tables t ON t.id = o.table_id
            LEFT JOIN users u ON u.id = o.user_id
            WHERE o.payment_status = 'paid' AND (DATE(o.paid_at) BETWEEN :s_date AND :e_date)
            ORDER BY o.paid_at ASC, o.id ASC
        ");
        $stmtOrders->execute([':s_date' => $startDate, ':e_date' => $endDate]);
        $orders = $stmtOrders->fetchAll();

        // 3. Ringkasan Menu Terjual
        $stmtProducts = $db->prepare("
            SELECT oi.product_name, c.name AS category_name, 
                   SUM(oi.quantity) AS qty_sold, 
                   SUM(oi.subtotal) AS total_sales
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            JOIN products p ON p.id = oi.product_id
            JOIN categories c ON c.id = p.category_id
            WHERE o.payment_status = 'paid' AND (DATE(o.paid_at) BETWEEN :s_date AND :e_date)
            GROUP BY oi.product_id, oi.product_name, c.name
            ORDER BY qty_sold DESC
        ");
        $stmtProducts->execute([':s_date' => $startDate, ':e_date' => $endDate]);
        $topProducts = $stmtProducts->fetchAll();

        // Download Header untuk Microsoft Word
        $filename = "Laporan_Transaksi_CafeFlow_" . $filePeriod . ".doc";
        header("Content-Type: application/vnd.ms-word; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Format Rupiah helper
        $rp = fn($num) => 'Rp ' . number_format((float)$num, 0, ',', '.');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="utf-8">';
        echo '<!--[if gte mso 9]><xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom><w:DoNotOptimizeForBrowser/></w:WordDocument></xml><![endif]-->';
        echo '<style>';
        echo 'body { font-family: "Segoe UI", Arial, sans-serif; font-size: 10pt; color: #2D1B12; line-height: 1.4; }';
        echo '.header-title { font-size: 18pt; font-weight: bold; color: #4A2E18; margin: 0; text-transform: uppercase; }';
        echo '.header-subtitle { font-size: 10pt; color: #78716C; margin: 2px 0 15px 0; }';
        echo '.doc-title { font-size: 14pt; font-weight: bold; color: #2D1B12; border-bottom: 2px solid #4A2E18; padding-bottom: 4px; margin-top: 15px; margin-bottom: 15px; }';
        echo 'table { width: 100%; border-collapse: collapse; margin-bottom: 18px; font-size: 9.5pt; }';
        echo 'th { background-color: #4A2E18; color: #FFFFFF; font-weight: bold; padding: 7px 8px; border: 1px solid #4A2E18; text-align: left; }';
        echo 'td { padding: 6px 8px; border: 1px solid #E5E7EB; vertical-align: top; }';
        echo '.text-right { text-align: right; }';
        echo '.text-center { text-align: center; }';
        echo '.fw-bold { font-weight: bold; }';
        echo '.bg-summary { background-color: #FBF8F3; border: 1px solid #EAE3D9; }';
        echo '.badge-cash { color: #047857; font-weight: bold; }';
        echo '.badge-qris { color: #0369A1; font-weight: bold; }';
        echo '.footer-box { margin-top: 30px; font-size: 9pt; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';

        // Header Dokumen
        echo '<div style="text-align: center; margin-bottom: 20px;">';
        echo '<div class="header-title">' . htmlspecialchars($cafeName) . '</div>';
        echo '<div class="header-subtitle">' . htmlspecialchars($cafeTagline) . ' | ' . htmlspecialchars($cafeAddress) . ' | Telp: ' . htmlspecialchars($cafePhone) . '</div>';
        echo '<hr style="border: none; border-top: 2px solid #4A2E18; margin: 10px 0;">';
        echo '<div class="doc-title">LAPORAN TRANSAKSI PENJUALAN ' . strtoupper($periodLabel) . '</div>';
        echo '<div style="font-size: 9pt; color: #666;">Tanggal Cetak: ' . date('d F Y, H:i') . ' WIB | Dicetak oleh: ' . htmlspecialchars(\App\Helpers\Auth::user()['name'] ?? 'Administrator') . '</div>';
        echo '</div>';

        // 1. Ringkasan Eksekutif Finansial
        echo '<h3 style="color: #4A2E18; margin-bottom: 8px; font-size: 11pt;">I. RINGKASAN EKSEKUTIF FINANSIAL</h3>';
        echo '<table class="bg-summary">';
        echo '<tr>';
        echo '<td style="width: 25%; font-weight: bold;">Total Transaksi:</td><td>' . $summary['total_transactions'] . ' Transaksi Lunas</td>';
        echo '<td style="width: 25%; font-weight: bold;">Rata-rata Transaksi (AOV):</td><td>' . $rp($summary['avg_order_value']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td style="font-weight: bold;">Omzet Bruto:</td><td class="fw-bold">' . $rp($summary['gross_sales']) . '</td>';
        echo '<td style="font-weight: bold;">Total Diskon:</td><td style="color: #DC2626;">- ' . $rp($summary['total_discount']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td style="font-weight: bold;">Penjualan Bersih:</td><td class="fw-bold" style="color: #047857;">' . $rp($summary['net_sales']) . '</td>';
        echo '<td style="font-weight: bold;">Pajak Restoran (PPN):</td><td>' . $rp($summary['total_tax']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td style="font-weight: bold;">Penjualan Tunai (Cash):</td><td class="badge-cash">' . $rp($summary['cash_total']) . '</td>';
        echo '<td style="font-weight: bold;">Penjualan QRIS Dinamis:</td><td class="badge-qris">' . $rp($summary['qris_total']) . '</td>';
        echo '</tr>';
        echo '<tr style="background-color: #F4EBE2;">';
        echo '<td colspan="2" style="font-size: 11pt; font-weight: bold; color: #4A2E18;">TOTAL PENERIMAAN KAS & BANK (GRAND TOTAL):</td>';
        echo '<td colspan="2" class="text-right" style="font-size: 12pt; font-weight: bold; color: #4A2E18;">' . $rp($summary['grand_total']) . '</td>';
        echo '</tr>';
        echo '</table>';

        // 2. Rincian Seluruh Transaksi
        echo '<h3 style="color: #4A2E18; margin-top: 25px; margin-bottom: 8px; font-size: 11pt;">II. DAFTAR RINCIAN TRANSAKSI PESANAN</h3>';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th style="width: 4%;">No</th>';
        echo '<th style="width: 16%;">No. Order</th>';
        echo '<th style="width: 13%;">Waktu</th>';
        echo '<th style="width: 15%;">Pelanggan</th>';
        echo '<th style="width: 10%;">Tipe / Meja</th>';
        echo '<th style="width: 25%;">Item Menu Pesanan</th>';
        echo '<th style="width: 8%;" class="text-center">Metode</th>';
        echo '<th style="width: 9%;" class="text-right">Total (Rp)</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        if (empty($orders)) {
            echo '<tr><td colspan="8" class="text-center" style="padding: 15px; color: #666;">Tidak ada data transaksi lunas pada periode ini.</td></tr>';
        } else {
            $no = 1;
            foreach ($orders as $o) {
                $orderTypeStr = ($o['order_type'] === 'dine_in') ? 'Dine-In (' . ($o['table_number'] ? 'Meja ' . $o['table_number'] : '-') . ')' : 'Takeaway';
                $methodStr = ($o['payment_method'] === 'cash') ? '<span class="badge-cash">TUNAI</span>' : '<span class="badge-qris">QRIS</span>';
                echo '<tr>';
                echo '<td class="text-center">' . $no++ . '</td>';
                echo '<td class="fw-bold">' . htmlspecialchars($o['order_number']) . '</td>';
                echo '<td>' . date('d/m/Y H:i', strtotime($o['paid_at'] ?? $o['created_at'])) . '</td>';
                echo '<td>' . htmlspecialchars($o['customer_name']) . '</td>';
                echo '<td>' . htmlspecialchars($orderTypeStr) . '</td>';
                echo '<td><small>' . htmlspecialchars($o['items_summary'] ?? '-') . '</small></td>';
                echo '<td class="text-center">' . $methodStr . '</td>';
                echo '<td class="text-right fw-bold">' . number_format((float)$o['total_amount'], 0, ',', '.') . '</td>';
                echo '</tr>';
            }
            // Baris Total
            echo '<tr style="background-color: #F3F4F6; font-weight: bold;">';
            echo '<td colspan="7" class="text-right">TOTAL KESELURUHAN:</td>';
            echo '<td class="text-right" style="color: #4A2E18;">' . number_format((float)$summary['grand_total'], 0, ',', '.') . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';

        // 3. Rekapitulasi Menu Terlaris pada Periode
        echo '<h3 style="color: #4A2E18; margin-top: 25px; margin-bottom: 8px; font-size: 11pt;">III. REKAPITULASI PENJUALAN PER ITEM MENU</h3>';
        echo '<table style="width: 70%;">';
        echo '<thead>';
        echo '<tr>';
        echo '<th style="width: 8%;">No</th>';
        echo '<th style="width: 45%;">Nama Menu</th>';
        echo '<th style="width: 25%;">Kategori</th>';
        echo '<th style="width: 10%;" class="text-center">Qty Terjual</th>';
        echo '<th style="width: 12%;" class="text-right">Total Omzet</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        if (empty($topProducts)) {
            echo '<tr><td colspan="5" class="text-center" style="padding: 10px; color: #666;">Belum ada menu terjual pada periode ini.</td></tr>';
        } else {
            $num = 1;
            foreach ($topProducts as $tp) {
                echo '<tr>';
                echo '<td class="text-center">' . $num++ . '</td>';
                echo '<td class="fw-bold">' . htmlspecialchars($tp['product_name']) . '</td>';
                echo '<td>' . htmlspecialchars($tp['category_name']) . '</td>';
                echo '<td class="text-center fw-bold">' . $tp['qty_sold'] . '</td>';
                echo '<td class="text-right">' . $rp($tp['total_sales']) . '</td>';
                echo '</tr>';
            }
        }
        echo '</tbody>';
        echo '</table>';

        // Tanda Tangan & Pengesahan
        echo '<div class="footer-box" style="margin-top: 40px;">';
        echo '<table style="border: none; width: 100%;">';
        echo '<tr>';
        echo '<td style="border: none; width: 50%; text-align: center;">';
        echo 'Dibuat Oleh,<br><br><br><br>';
        echo '<b>( ' . htmlspecialchars(\App\Helpers\Auth::user()['name'] ?? 'Petugas Kasir / Admin') . ' )</b><br>';
        echo 'Staf Administrasi CafeFlow';
        echo '</td>';
        echo '<td style="border: none; width: 50%; text-align: center;">';
        echo 'Mengetahui,<br><br><br><br>';
        echo '<b>( .................................................. )</b><br>';
        echo 'Owner / General Manager';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';

        echo '</body>';
        echo '</html>';
        exit;
    }
}

