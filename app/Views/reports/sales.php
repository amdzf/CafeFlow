<?php
use App\Helpers\Format;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">Laporan Penjualan</h2>
        <p class="text-muted mb-0 small">Analisis omzet bruto, diskon, penjualan bersih, pajak, dan performa per item produk.</p>
    </div>

    <!-- Date Range & Export Actions -->
    <div class="d-flex flex-wrap align-items-center gap-2">
        <form action="<?= url('/reports/sales') ?>" method="GET" class="d-flex align-items-center gap-2">
            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="form-control form-control-sm bg-white shadow-sm" style="max-width: 140px;">
            <span class="text-muted small">-</span>
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="form-control form-control-sm bg-white shadow-sm" style="max-width: 140px;">
            <button type="submit" class="btn btn-sm btn-outline-dark bg-white shadow-sm">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </form>

        <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary bg-white shadow-sm dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-file-earmark-word text-primary me-1"></i> Ekspor Word (.doc)
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 12px; font-size: 0.88rem;">
                <li><h6 class="dropdown-header text-uppercase small">Pilih Periode:</h6></li>
                <li>
                    <a class="dropdown-item py-2" href="<?= url('/reports/export-word?period=daily&date=' . date('Y-m-d')) ?>">
                        <i class="bi bi-calendar-event me-2 text-warning"></i> Harian (Hari Ini)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?= url('/reports/export-word?period=weekly') ?>">
                        <i class="bi bi-calendar-range me-2 text-primary"></i> Mingguan (7 Hari Terakhir)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?= url('/reports/export-word?period=monthly&month=' . date('Y-m')) ?>">
                        <i class="bi bi-calendar3 me-2 text-success"></i> Bulanan (Bulan Ini)
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

        <a href="<?= url('/reports/sales?start_date=' . urlencode($startDate) . '&end_date=' . urlencode($endDate) . '&export=csv') ?>" class="btn btn-sm btn-outline-success bg-white shadow-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Ekspor CSV
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary bg-white shadow-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak PDF
        </button>
    </div>
</div>

<!-- Sales Metrics Row -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon caramel">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <div class="stat-title">Omzet Bruto</div>
                <div class="stat-value text-secondary"><?= Format::rupiah($summary['gross_sales']) ?></div>
                <div class="small text-muted mt-1"><?= $summary['total_transactions'] ?> transaksi</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="bi bi-tag"></i>
            </div>
            <div>
                <div class="stat-title">Total Diskon</div>
                <div class="stat-value text-danger"><?= Format::rupiah($summary['total_discount']) ?></div>
                <div class="small text-muted mt-1">Potongan harga</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="stat-title">Penjualan Bersih (Net)</div>
                <div class="stat-value text-success"><?= Format::rupiah($summary['net_sales']) ?></div>
                <div class="small text-muted mt-1">Bruto dikurangi diskon</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon brown">
                <i class="bi bi-graph-up"></i>
            </div>
            <div>
                <div class="stat-title">Rata-rata Transaksi (AOV)</div>
                <div class="stat-value text-dark"><?= Format::rupiah($summary['avg_order_value']) ?></div>
                <div class="small text-muted mt-1">Per tiket transaksi</div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="card-cafe p-3 d-flex align-items-center justify-content-between">
            <div>
                <div class="small text-muted fw-semibold">Pajak Restoran Terkumpul (PPN)</div>
                <div class="fw-bold fs-5 text-dark"><?= Format::rupiah($summary['total_tax']) ?></div>
            </div>
            <i class="bi bi-bank fs-2 text-muted opacity-50"></i>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card-cafe p-3 d-flex align-items-center justify-content-between">
            <div>
                <div class="small text-muted fw-semibold">Total Penerimaan Kas & Bank (Grand Total)</div>
                <div class="fw-bold fs-5 text-secondary"><?= Format::rupiah($summary['grand_total']) ?></div>
            </div>
            <i class="bi bi-wallet-fill fs-2 text-secondary opacity-50"></i>
        </div>
    </div>
</div>

<!-- Sales by Product Table -->
<div class="card-cafe mb-4">
    <div class="card-cafe-header">
        <h5 class="fw-bold mb-0">Rincian Penjualan per Menu</h5>
        <span class="badge bg-light text-dark border"><?= count($productSales) ?> Menu Terjual</span>
    </div>
    <div class="card-cafe-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nama Menu</th>
                        <th>Kategori</th>
                        <th class="text-center">Qty Terjual</th>
                        <th class="text-end">Total Omzet (Rp)</th>
                        <th class="text-end">Total HPP (Modal)</th>
                        <th class="pe-3 text-end">Laba Kotor (Gross Profit)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productSales)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada penjualan pada periode ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($productSales as $p): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark"><?= htmlspecialchars($p['product_name']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category_name']) ?></span></td>
                                <td class="text-center fw-bold"><?= $p['qty_sold'] ?></td>
                                <td class="text-end fw-bold text-secondary"><?= Format::rupiah($p['total_sales']) ?></td>
                                <td class="text-end text-muted"><?= Format::rupiah($p['total_hpp']) ?></td>
                                <td class="pe-3 text-end fw-bold text-success"><?= Format::rupiah($p['gross_profit']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sales by Category Table -->
<div class="card-cafe">
    <div class="card-cafe-header">
        <h5 class="fw-bold mb-0">Kontribusi Penjualan per Kategori</h5>
    </div>
    <div class="card-cafe-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kategori</th>
                        <th class="text-center">Porsi / Cup Terjual</th>
                        <th class="pe-3 text-end">Total Omzet Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorySales as $c): ?>
                        <tr>
                            <td class="ps-3 fw-bold text-dark"><?= htmlspecialchars($c['category_name']) ?></td>
                            <td class="text-center fw-semibold"><?= $c['qty_sold'] ?></td>
                            <td class="pe-3 text-end fw-bold text-secondary"><?= Format::rupiah($c['total_sales']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
