<?php
use App\Helpers\Format;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">Laporan Laba Rugi (Profit & Loss)</h2>
        <p class="text-muted mb-0 small">Kalkulasi performa finansial: Pendapatan Bersih dikurangi Harga Pokok Penjualan (HPP) dan Beban Operasional.</p>
    </div>

    <form action="<?= url('/reports/profit-loss') ?>" method="GET" class="d-flex align-items-center gap-2">
        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="form-control form-control-sm bg-white shadow-sm" style="max-width: 140px;">
        <span class="text-muted small">-</span>
        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="form-control form-control-sm bg-white shadow-sm" style="max-width: 140px;">
        <button type="submit" class="btn btn-sm btn-outline-dark bg-white shadow-sm">
            <i class="bi bi-funnel"></i> Filter
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary bg-white shadow-sm ms-2" onclick="window.print()">
            <i class="bi bi-printer"></i> Cetak
        </button>
    </form>
</div>

<!-- Financial Summary Statement Card -->
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card-cafe shadow-sm">
            <div class="card-cafe-header bg-light p-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Laporan Keuangan Laba Rugi CafeFlow</h4>
                    <div class="text-muted small">Periode: <?= Format::date($startDate, 'd M Y') ?> s/d <?= Format::date($endDate, 'd M Y') ?></div>
                </div>
                <div class="text-end">
                    <span class="badge bg-dark px-3 py-2 fs-6">
                        Laba Bersih: <?= Format::rupiah($netProfit) ?>
                    </span>
                </div>
            </div>

            <div class="card-cafe-body p-4">
                <!-- Section 1: Revenue -->
                <div class="mb-4">
                    <h6 class="fw-bold text-uppercase text-secondary border-bottom pb-2 mb-3">1. Pendapatan Penjualan (Revenue)</h6>
                    <div class="d-flex justify-content-between py-1.5 border-bottom border-light">
                        <span class="text-dark">Penjualan Kotor (Gross Sales)</span>
                        <span class="fw-semibold text-dark"><?= Format::rupiah($pnlSales['gross_sales']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom border-light">
                        <span class="text-danger">Potongan Diskon Penjualan (-)</span>
                        <span class="fw-semibold text-danger">(<?= Format::rupiah($pnlSales['total_discounts']) ?>)</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 fw-bold bg-light px-2 rounded-2 mt-2">
                        <span class="text-dark">Total Penjualan Bersih (Net Sales)</span>
                        <span class="text-success fs-6"><?= Format::rupiah($pnlSales['net_sales']) ?></span>
                    </div>
                </div>

                <!-- Section 2: Cost of Goods Sold (HPP) -->
                <div class="mb-4">
                    <h6 class="fw-bold text-uppercase text-secondary border-bottom pb-2 mb-3">2. Harga Pokok Penjualan (HPP / COGS)</h6>
                    <div class="d-flex justify-content-between py-1.5 border-bottom border-light">
                        <span class="text-dark">Beban Bahan Baku Terpakai (Resep & Porsi Terjual)</span>
                        <span class="fw-semibold text-danger">(<?= Format::rupiah($pnlSales['total_hpp']) ?>)</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 fw-bold bg-light px-2 rounded-2 mt-2">
                        <span class="text-dark">Laba Kotor (Gross Profit) [Penjualan Bersih - HPP]</span>
                        <span class="text-dark fs-6"><?= Format::rupiah($grossProfit) ?></span>
                    </div>
                </div>

                <!-- Section 3: Operational Expenses -->
                <div class="mb-4">
                    <h6 class="fw-bold text-uppercase text-secondary border-bottom pb-2 mb-3">3. Beban Operasional (Operating Expenses)</h6>
                    <?php if (empty($expensesByCategory)): ?>
                        <div class="text-muted small py-2">Tidak ada pengeluaran operasional tercatat pada periode ini.</div>
                    <?php else: ?>
                        <?php foreach ($expensesByCategory as $exp): ?>
                            <div class="d-flex justify-content-between py-1.5 border-bottom border-light">
                                <span class="text-dark"><?= htmlspecialchars($exp['name']) ?></span>
                                <span class="fw-semibold text-danger">(<?= Format::rupiah($exp['total_amount']) ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between py-2 fw-bold bg-danger-subtle text-danger px-2 rounded-2 mt-2">
                        <span>Total Beban Operasional (-)</span>
                        <span class="fs-6">(<?= Format::rupiah($totalExpenses) ?>)</span>
                    </div>
                </div>

                <!-- Section 4: Net Profit / Loss Result -->
                <div class="p-4 rounded-3 d-flex align-items-center justify-content-between <?= ($netProfit >= 0) ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger' ?>">
                    <div>
                        <h4 class="fw-bold mb-1">
                            <?= ($netProfit >= 0) ? 'LABA BERSIH (NET PROFIT)' : 'RUGI BERSIH (NET LOSS)' ?>
                        </h4>
                        <div class="small opacity-75">Hasil akhir laba kotor dikurangi seluruh beban operasional kafe.</div>
                    </div>
                    <div class="display-6 fw-bold">
                        <?= Format::rupiah($netProfit) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
