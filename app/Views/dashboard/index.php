<?php
use App\Helpers\Format;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--cafe-dark); letter-spacing: -0.02em;">Dashboard Utama</h2>
        <p class="text-muted mb-0 small">Ringkasan performa penjualan kafe harian, mingguan, bulanan, dan estimasi laba bersih real-time.</p>
    </div>

    <!-- Date Range Filter Form -->
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="btn-group shadow-sm" role="group">
            <a href="<?= url('/dashboard?range=today') ?>" class="btn btn-sm <?= ($range === 'today') ? 'btn-cafe' : 'btn-outline-secondary bg-white' ?>">Hari Ini</a>
            <a href="<?= url('/dashboard?range=week') ?>" class="btn btn-sm <?= ($range === 'week') ? 'btn-cafe' : 'btn-outline-secondary bg-white' ?>">7 Hari</a>
            <a href="<?= url('/dashboard?range=month') ?>" class="btn btn-sm <?= ($range === 'month') ? 'btn-cafe' : 'btn-outline-secondary bg-white' ?>">Bulan Ini</a>
        </div>

        <form action="<?= url('/dashboard') ?>" method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="range" value="custom">
            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="form-control form-control-sm bg-white shadow-sm" style="max-width: 140px;">
            <span class="text-muted small">-</span>
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="form-control form-control-sm bg-white shadow-sm" style="max-width: 140px;">
            <button type="submit" class="btn btn-sm btn-outline-dark bg-white shadow-sm">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </form>

        <!-- Tombol Ekspor Laporan Word -->
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary bg-white shadow-sm dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-file-earmark-word text-primary me-1"></i> Unduh Word (.doc)
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 12px; font-size: 0.88rem;">
                <li><h6 class="dropdown-header text-uppercase small">Pilih Periode Laporan:</h6></li>
                <li>
                    <a class="dropdown-item py-2" href="<?= url('/reports/export-word?period=daily&date=' . date('Y-m-d')) ?>">
                        <i class="bi bi-calendar-event me-2 text-warning"></i> Laporan Harian (Hari Ini)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?= url('/reports/export-word?period=weekly') ?>">
                        <i class="bi bi-calendar-range me-2 text-primary"></i> Laporan Mingguan (7 Hari Terakhir)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?= url('/reports/export-word?period=monthly&month=' . date('Y-m')) ?>">
                        <i class="bi bi-calendar3 me-2 text-success"></i> Laporan Bulanan (Bulan Ini)
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item py-2" href="<?= url('/reports/export-word?period=custom&start_date=' . urlencode($startDate) . '&end_date=' . urlencode($endDate)) ?>">
                        <i class="bi bi-funnel me-2 text-secondary"></i> Sesuai Filter Terpilih (<?= htmlspecialchars($startDate) ?> s/d <?= htmlspecialchars($endDate) ?>)
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- SECTION: PERBANDINGAN STATISTIK HARIAN, MINGGUAN & BULANAN     -->
<!-- ============================================================== -->
<div class="row g-3 mb-4">
    <!-- KARTU 1: STATISTIK HARIAN -->
    <div class="col-12 col-lg-4">
        <div class="card-cafe h-100 p-3.5 border-start border-4 border-warning shadow-sm position-relative overflow-hidden">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon caramel" style="width: 44px; height: 44px; font-size: 1.3rem;">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Statistik Harian</h6>
                        <span class="badge bg-warning-subtle text-dark border border-warning" style="font-size: 0.72rem;">Hari Ini (<?= date('d M Y') ?>)</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <a href="<?= url('/reports/export-word?period=daily&date=' . date('Y-m-d')) ?>" class="btn btn-sm btn-outline-warning text-dark py-0.5 px-2 small shadow-sm" title="Unduh Laporan Harian Word (.doc)">
                        <i class="bi bi-file-earmark-word"></i> Word
                    </a>
                    <span class="badge bg-light text-muted border">Daily</span>
                </div>
            </div>

            <div class="mb-3">
                <div class="small text-muted fw-semibold uppercase" style="font-size: 0.75rem;">Omzet Harian:</div>
                <div class="fs-4 fw-bold text-secondary"><?= Format::rupiah($statDaily['omzet']) ?></div>
                <div class="small text-muted"><?= $statDaily['trx_count'] ?> transaksi selesai</div>
            </div>

            <div class="row g-2 pt-2 border-top border-light" style="font-size: 0.82rem;">
                <div class="col-6">
                    <span class="text-muted d-block">Tunai (Cash):</span>
                    <span class="fw-bold text-success"><?= Format::rupiah($statDaily['cash_total']) ?></span>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block">QRIS Lunas:</span>
                    <span class="fw-bold text-primary"><?= Format::rupiah($statDaily['qris_total']) ?></span>
                </div>
                <div class="col-6 mt-2">
                    <span class="text-muted d-block">Beban Operasional:</span>
                    <span class="fw-bold text-danger"><?= Format::rupiah($statDaily['expenses']) ?></span>
                </div>
                <div class="col-6 mt-2">
                    <span class="text-muted d-block">Est. Laba Bersih:</span>
                    <span class="fw-bold <?= ($statDaily['net_profit'] >= 0) ? 'text-dark' : 'text-danger' ?>">
                        <?= Format::rupiah($statDaily['net_profit']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- KARTU 2: STATISTIK MINGGUAN -->
    <div class="col-12 col-lg-4">
        <div class="card-cafe h-100 p-3.5 border-start border-4 border-primary shadow-sm position-relative overflow-hidden">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon blue" style="width: 44px; height: 44px; font-size: 1.3rem;">
                        <i class="bi bi-calendar-range"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Statistik Mingguan</h6>
                        <span class="badge bg-primary-subtle text-primary border border-primary" style="font-size: 0.72rem;">7 Hari Terakhir</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <a href="<?= url('/reports/export-word?period=weekly') ?>" class="btn btn-sm btn-outline-primary py-0.5 px-2 small shadow-sm" title="Unduh Laporan Mingguan Word (.doc)">
                        <i class="bi bi-file-earmark-word"></i> Word
                    </a>
                    <span class="badge bg-light text-muted border">Weekly</span>
                </div>
            </div>

            <div class="mb-3">
                <div class="small text-muted fw-semibold uppercase" style="font-size: 0.75rem;">Omzet Mingguan:</div>
                <div class="fs-4 fw-bold text-primary"><?= Format::rupiah($statWeekly['omzet']) ?></div>
                <div class="small text-muted"><?= $statWeekly['trx_count'] ?> transaksi selesai</div>
            </div>

            <div class="row g-2 pt-2 border-top border-light" style="font-size: 0.82rem;">
                <div class="col-6">
                    <span class="text-muted d-block">Tunai (Cash):</span>
                    <span class="fw-bold text-success"><?= Format::rupiah($statWeekly['cash_total']) ?></span>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block">QRIS Lunas:</span>
                    <span class="fw-bold text-primary"><?= Format::rupiah($statWeekly['qris_total']) ?></span>
                </div>
                <div class="col-6 mt-2">
                    <span class="text-muted d-block">Beban Operasional:</span>
                    <span class="fw-bold text-danger"><?= Format::rupiah($statWeekly['expenses']) ?></span>
                </div>
                <div class="col-6 mt-2">
                    <span class="text-muted d-block">Est. Laba Bersih:</span>
                    <span class="fw-bold <?= ($statWeekly['net_profit'] >= 0) ? 'text-dark' : 'text-danger' ?>">
                        <?= Format::rupiah($statWeekly['net_profit']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- KARTU 3: STATISTIK BULANAN -->
    <div class="col-12 col-lg-4">
        <div class="card-cafe h-100 p-3.5 border-start border-4 border-success shadow-sm position-relative overflow-hidden">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon green" style="width: 44px; height: 44px; font-size: 1.3rem;">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Statistik Bulanan</h6>
                        <span class="badge bg-success-subtle text-success border border-success" style="font-size: 0.72rem;">Bulan Ini (<?= date('F Y') ?>)</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <a href="<?= url('/reports/export-word?period=monthly&month=' . date('Y-m')) ?>" class="btn btn-sm btn-outline-success py-0.5 px-2 small shadow-sm" title="Unduh Laporan Bulanan Word (.doc)">
                        <i class="bi bi-file-earmark-word"></i> Word
                    </a>
                    <span class="badge bg-light text-muted border">Monthly</span>
                </div>
            </div>

            <div class="mb-3">
                <div class="small text-muted fw-semibold uppercase" style="font-size: 0.75rem;">Omzet Bulanan:</div>
                <div class="fs-4 fw-bold text-success"><?= Format::rupiah($statMonthly['omzet']) ?></div>
                <div class="small text-muted"><?= $statMonthly['trx_count'] ?> transaksi selesai</div>
            </div>

            <div class="row g-2 pt-2 border-top border-light" style="font-size: 0.82rem;">
                <div class="col-6">
                    <span class="text-muted d-block">Tunai (Cash):</span>
                    <span class="fw-bold text-success"><?= Format::rupiah($statMonthly['cash_total']) ?></span>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block">QRIS Lunas:</span>
                    <span class="fw-bold text-primary"><?= Format::rupiah($statMonthly['qris_total']) ?></span>
                </div>
                <div class="col-6 mt-2">
                    <span class="text-muted d-block">Beban Operasional:</span>
                    <span class="fw-bold text-danger"><?= Format::rupiah($statMonthly['expenses']) ?></span>
                </div>
                <div class="col-6 mt-2">
                    <span class="text-muted d-block">Est. Laba Bersih:</span>
                    <span class="fw-bold <?= ($statMonthly['net_profit'] >= 0) ? 'text-dark' : 'text-danger' ?>">
                        <?= Format::rupiah($statMonthly['net_profit']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- SECTION: RINGKASAN FILTER TERPILIH (DETAIL EXPENSE & HPP)       -->
<!-- ============================================================== -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
        <div class="stat-card">
            <div class="stat-icon caramel">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="stat-title">Omzet (Filter Terpilih)</div>
                <div class="stat-value text-secondary"><?= Format::rupiah($kpiFiltered['omzet']) ?></div>
                <div class="small text-muted mt-1"><?= $kpiFiltered['trx_count'] ?> transaksi lunas</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-3">
        <div class="stat-card">
            <div class="stat-icon brown">
                <i class="bi bi-basket3"></i>
            </div>
            <div>
                <div class="stat-title">Total HPP (Modal)</div>
                <div class="stat-value text-dark"><?= Format::rupiah($kpiFiltered['hpp_total']) ?></div>
                <div class="small text-muted mt-1">Modal bahan baku</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-3">
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <div class="stat-title">Pengeluaran Operasional</div>
                <div class="stat-value text-danger"><?= Format::rupiah($expenseFiltered) ?></div>
                <div class="small text-muted mt-1">Beban terbayar</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-3">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <div class="stat-title">Estimasi Laba Bersih</div>
                <div class="stat-value <?= ($netProfitFiltered >= 0) ? 'text-success' : 'text-danger' ?>">
                    <?= Format::rupiah($netProfitFiltered) ?>
                </div>
                <div class="small text-muted mt-1">Penjualan - HPP - Beban</div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- SECTION: GRAFIK TREN MULTI-PERIODE DENGAN SWITCHER            -->
<!-- ============================================================== -->
<div class="row g-4 mb-4">
    <!-- Chart Column -->
    <div class="col-12 col-xl-8">
        <div class="card-cafe h-100">
            <div class="card-cafe-header flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-0" id="chartTitleText">Tren Penjualan Harian (7 Hari Terakhir)</h5>
                    <div class="small text-muted" id="chartSubtitleText">Grafik omzet harian kafe dalam Rupiah</div>
                </div>

                <!-- Interactive Period Switcher Buttons -->
                <div class="btn-group btn-group-sm shadow-sm" role="group">
                    <button type="button" class="btn btn-cafe" id="btnChartDaily" onclick="switchChartPeriod('daily')">
                        <i class="bi bi-calendar-event me-1"></i> Harian
                    </button>
                    <button type="button" class="btn btn-outline-secondary bg-white" id="btnChartWeekly" onclick="switchChartPeriod('weekly')">
                        <i class="bi bi-calendar-range me-1"></i> Mingguan
                    </button>
                    <button type="button" class="btn btn-outline-secondary bg-white" id="btnChartMonthly" onclick="switchChartPeriod('monthly')">
                        <i class="bi bi-calendar3 me-1"></i> Bulanan
                    </button>
                </div>
            </div>
            <div class="card-cafe-body">
                <canvas id="salesTrendChart" style="max-height: 320px; width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Products Card -->
    <div class="col-12 col-xl-4">
        <div class="card-cafe h-100">
            <div class="card-cafe-header">
                <div>
                    <h5 class="fw-bold mb-0">Produk Terlaris</h5>
                    <div class="small text-muted">Menu paling diminati pelanggan</div>
                </div>
                <i class="bi bi-trophy text-warning fs-5"></i>
            </div>
            <div class="card-cafe-body p-0">
                <?php if (empty($topProducts)): ?>
                    <div class="p-4 text-center text-muted small">Belum ada data penjualan pada periode ini.</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($topProducts as $index => $prod): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between p-3 border-0 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge rounded-pill <?= ($index === 0) ? 'bg-warning text-dark' : 'bg-light text-dark border' ?>" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                        <?= $index + 1 ?>
                                    </span>
                                    <div>
                                        <div class="fw-semibold text-dark" style="font-size: 0.92rem;"><?= htmlspecialchars($prod['product_name']) ?></div>
                                        <div class="small text-muted"><?= $prod['total_sold'] ?> terjual</div>
                                    </div>
                                </div>
                                <div class="fw-bold text-secondary" style="font-size: 0.92rem;">
                                    <?= Format::rupiah($prod['total_revenue']) ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock & Recent Orders Row -->
<div class="row g-4">
    <!-- Low Stock Alerts -->
    <div class="col-12 col-lg-5">
        <div class="card-cafe h-100">
            <div class="card-cafe-header bg-danger-subtle text-danger border-bottom-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                    <h5 class="fw-bold mb-0">Peringatan Stok Menipis</h5>
                </div>
                <a href="<?= url('/ingredients') ?>" class="btn btn-sm btn-danger py-1 px-2.5">Restock</a>
            </div>
            <div class="card-cafe-body p-0">
                <?php if (empty($lowStockProducts) && empty($lowStockIngredients)): ?>
                    <div class="p-4 text-center text-success small">
                        <i class="bi bi-check-circle-fill fs-4 d-block mb-1"></i>
                        Semua stok produk dan bahan baku dalam batas aman.
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($lowStockIngredients as $ing): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between p-3 border-bottom">
                                <div>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($ing['name']) ?></div>
                                    <div class="small text-danger">Bahan Baku (Min: <?= $ing['min_stock'] ?> <?= $ing['unit'] ?>)</div>
                                </div>
                                <span class="badge bg-danger-subtle text-danger border border-danger px-2.5 py-1.5">
                                    Sisa <?= $ing['stock'] ?> <?= $ing['unit'] ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                        <?php foreach ($lowStockProducts as $lp): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between p-3 border-bottom">
                                <div>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($lp['name']) ?></div>
                                    <div class="small text-danger">Produk Langsung (Min: <?= $lp['min_stock'] ?> <?= $lp['unit'] ?>)</div>
                                </div>
                                <span class="badge bg-danger-subtle text-danger border border-danger px-2.5 py-1.5">
                                    Sisa <?= $lp['stock'] ?> <?= $lp['unit'] ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="col-12 col-lg-7">
        <div class="card-cafe h-100">
            <div class="card-cafe-header">
                <div>
                    <h5 class="fw-bold mb-0">Transaksi Terbaru</h5>
                    <div class="small text-muted">Aktivitas penjualan kasir dan QRIS terkini</div>
                </div>
                <a href="<?= url('/orders') ?>" class="btn btn-sm btn-outline-secondary">Semua Pesanan</a>
            </div>
            <div class="card-cafe-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">No. Pesanan</th>
                                <th>Pelanggan / Meja</th>
                                <th>Metode</th>
                                <th>Total</th>
                                <th>Status Bayar</th>
                                <th class="pe-3 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center p-4 text-muted">Belum ada transaksi tercatat.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark">
                                            <?= htmlspecialchars($order['order_number']) ?>
                                            <div class="small text-muted fw-normal"><?= Format::date($order['created_at'], 'H:i') ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($order['customer_name']) ?></div>
                                            <div class="small text-muted">
                                                <?= ($order['order_type'] === 'dine_in') ? ($order['table_number'] ? 'Meja ' . $order['table_number'] : 'Dine-In') : 'Takeaway' ?>
                                            </div>
                                        </td>
                                        <td><?= Format::paymentMethodBadge($order['payment_method']) ?></td>
                                        <td class="fw-bold text-secondary"><?= Format::rupiah($order['total_amount']) ?></td>
                                        <td><?= Format::paymentStatusBadge($order['payment_status']) ?></td>
                                        <td class="pe-3 text-end">
                                            <a href="<?= url('/orders/' . $order['id']) ?>" class="btn btn-sm btn-light border py-1 px-2" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= url('/orders/' . $order['id'] . '/receipt') ?>" target="_blank" class="btn btn-sm btn-light border py-1 px-2" title="Struk">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- CHART.JS MULTI-PERIODE CONTROLLER SCRIPT                      -->
<!-- ============================================================== -->
<script>
    const chartDataStore = {
        daily: {
            title: 'Tren Penjualan Harian (7 Hari Terakhir)',
            subtitle: 'Grafik omzet harian kafe dalam Rupiah',
            labels: <?= json_encode($chartDailyLabels) ?>,
            sales: <?= json_encode($chartDailySales) ?>,
            orders: <?= json_encode($chartDailyOrders) ?>,
            color: '#C67C4E',
            pointColor: '#4A2E18'
        },
        weekly: {
            title: 'Tren Penjualan Mingguan (4 Minggu Terakhir)',
            subtitle: 'Grafik omzet per minggu dalam Rupiah',
            labels: <?= json_encode($chartWeeklyLabels) ?>,
            sales: <?= json_encode($chartWeeklySales) ?>,
            orders: <?= json_encode($chartWeeklyOrders) ?>,
            color: '#0284C7',
            pointColor: '#0369A1'
        },
        monthly: {
            title: 'Tren Penjualan Bulanan (Tahun <?= date('Y') ?>)',
            subtitle: 'Grafik omzet kumulatif 12 bulan dalam Rupiah',
            labels: <?= json_encode($chartMonthlyLabels) ?>,
            sales: <?= json_encode($chartMonthlySales) ?>,
            orders: <?= json_encode($chartMonthlyOrders) ?>,
            color: '#10B981',
            pointColor: '#059669'
        }
    };

    let salesChartInstance = null;

    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesTrendChart').getContext('2d');
        salesChartInstance = initSalesChart(ctx, chartDataStore.daily);
    });

    function initSalesChart(ctx, dataObj) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(198, 124, 78, 0.4)');
        gradient.addColorStop(1, 'rgba(198, 124, 78, 0.0)');

        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: dataObj.labels,
                datasets: [{
                    label: 'Omzet Penjualan (Rp)',
                    data: dataObj.sales,
                    borderColor: dataObj.color,
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: dataObj.pointColor,
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Omzet: ' + formatRupiah(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'k';
                            }
                        },
                        grid: { color: '#F2ECE4' }
                    }
                }
            }
        });
    }

    function switchChartPeriod(period) {
        if (!salesChartInstance || !chartDataStore[period]) return;

        const dataObj = chartDataStore[period];

        // Update titles
        document.getElementById('chartTitleText').textContent = dataObj.title;
        document.getElementById('chartSubtitleText').textContent = dataObj.subtitle;

        // Update button states
        const btnDaily = document.getElementById('btnChartDaily');
        const btnWeekly = document.getElementById('btnChartWeekly');
        const btnMonthly = document.getElementById('btnChartMonthly');

        [btnDaily, btnWeekly, btnMonthly].forEach(b => {
            b.className = 'btn btn-outline-secondary bg-white';
        });

        if (period === 'daily') btnDaily.className = 'btn btn-cafe';
        if (period === 'weekly') btnWeekly.className = 'btn btn-primary';
        if (period === 'monthly') btnMonthly.className = 'btn btn-success';

        // Update Chart data and styling
        const ctx = document.getElementById('salesTrendChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);

        if (period === 'weekly') {
            gradient.addColorStop(0, 'rgba(2, 132, 199, 0.4)');
            gradient.addColorStop(1, 'rgba(2, 132, 199, 0.0)');
        } else if (period === 'monthly') {
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
        } else {
            gradient.addColorStop(0, 'rgba(198, 124, 78, 0.4)');
            gradient.addColorStop(1, 'rgba(198, 124, 78, 0.0)');
        }

        salesChartInstance.data.labels = dataObj.labels;
        salesChartInstance.data.datasets[0].data = dataObj.sales;
        salesChartInstance.data.datasets[0].borderColor = dataObj.color;
        salesChartInstance.data.datasets[0].backgroundColor = gradient;
        salesChartInstance.data.datasets[0].pointBackgroundColor = dataObj.pointColor;
        salesChartInstance.update();
    }
</script>
