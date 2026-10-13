<?php
use App\Helpers\Format;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">Laporan Metode Pembayaran</h2>
        <p class="text-muted mb-0 small">Rekapitulasi transaksi Tunai (Cash), QRIS Payment Gateway terverifikasi, serta status pending & kedaluwarsa.</p>
    </div>

    <form action="<?= url('/reports/payments') ?>" method="GET" class="d-flex align-items-center gap-2">
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

<!-- Payment Status Breakdown Cards -->
<div class="row g-3 mb-4">
    <?php
    $totalCashPaid = 0;
    $totalQrisPaid = 0;
    $totalPending = 0;
    $totalFailed = 0;

    foreach ($breakdowns as $b) {
        if ($b['payment_method'] === 'cash' && $b['status'] === 'paid') $totalCashPaid += (float)$b['total_amount'];
        if ($b['payment_method'] === 'qris' && $b['status'] === 'paid') $totalQrisPaid += (float)$b['total_amount'];
        if ($b['status'] === 'pending') $totalPending += (float)$b['total_amount'];
        if ($b['status'] === 'failed' || $b['status'] === 'expired') $totalFailed += (float)$b['total_amount'];
    }
    ?>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-cash"></i>
            </div>
            <div>
                <div class="stat-title">Tunai (Cash) Lunas</div>
                <div class="stat-value text-success"><?= Format::rupiah($totalCashPaid) ?></div>
                <div class="small text-muted mt-1">Diterima langsung kasir</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-qr-code-scan"></i>
            </div>
            <div>
                <div class="stat-title">QRIS Dinamis Lunas</div>
                <div class="stat-value text-primary"><?= Format::rupiah($totalQrisPaid) ?></div>
                <div class="small text-muted mt-1">Verifikasi payment gateway</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon brown">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="stat-title">Transaksi Pending</div>
                <div class="stat-value text-warning"><?= Format::rupiah($totalPending) ?></div>
                <div class="small text-muted mt-1">Menunggu pembayaran</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="bi bi-x-circle"></i>
            </div>
            <div>
                <div class="stat-title">Gagal / Kedaluwarsa</div>
                <div class="stat-value text-danger"><?= Format::rupiah($totalFailed) ?></div>
                <div class="small text-muted mt-1">Tidak masuk pendapatan</div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Recap Table -->
<div class="card-cafe">
    <div class="card-cafe-header">
        <h5 class="fw-bold mb-0">Rekapitulasi Pembayaran Harian</h5>
    </div>
    <div class="card-cafe-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Tanggal Transaksi</th>
                        <th class="text-end">Tunai (Cash)</th>
                        <th class="text-end">QRIS Terverifikasi</th>
                        <th class="text-end">Pending</th>
                        <th class="text-end">Gagal/Expired</th>
                        <th class="pe-3 text-end">Total Diterima</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dailyRecap)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada riwayat pembayaran pada rentang tanggal ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dailyRecap as $row): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark"><?= Format::date($row['trx_date'], 'd M Y') ?></td>
                                <td class="text-end text-success fw-semibold"><?= Format::rupiah($row['cash_paid']) ?></td>
                                <td class="text-end text-primary fw-semibold"><?= Format::rupiah($row['qris_paid']) ?></td>
                                <td class="text-end text-warning"><?= Format::rupiah($row['pending_amount']) ?></td>
                                <td class="text-end text-danger"><?= Format::rupiah($row['failed_amount']) ?></td>
                                <td class="pe-3 text-end fw-bold text-dark fs-6"><?= Format::rupiah($row['total_paid']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
