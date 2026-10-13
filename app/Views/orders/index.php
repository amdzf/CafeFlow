<?php
use App\Helpers\Format;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">Daftar Transaksi & Pesanan</h2>
        <p class="text-muted mb-0 small">Riwayat seluruh pesanan masuk, status pembayaran, dan pengerjaan dapur.</p>
    </div>

    <!-- Filter Form -->
    <form action="<?= url('/orders') ?>" method="GET" class="d-flex flex-wrap align-items-center gap-2">
        <input type="date" name="date" value="<?= htmlspecialchars($currentDate) ?>" class="form-control form-control-sm bg-white shadow-sm" style="max-width: 140px;">

        <select name="payment_status" class="form-select form-select-sm bg-white shadow-sm" style="max-width: 130px;">
            <option value="">Semua Bayar</option>
            <option value="paid" <?= ($currentPayStatus === 'paid') ? 'selected' : '' ?>>Lunas (Paid)</option>
            <option value="unpaid" <?= ($currentPayStatus === 'unpaid') ? 'selected' : '' ?>>Pending</option>
            <option value="expired" <?= ($currentPayStatus === 'expired') ? 'selected' : '' ?>>Expired</option>
            <option value="failed" <?= ($currentPayStatus === 'failed') ? 'selected' : '' ?>>Failed</option>
        </select>

        <select name="status" class="form-select form-select-sm bg-white shadow-sm" style="max-width: 140px;">
            <option value="">Semua Status</option>
            <option value="pending" <?= ($currentStatus === 'pending') ? 'selected' : '' ?>>Menunggu</option>
            <option value="processing" <?= ($currentStatus === 'processing') ? 'selected' : '' ?>>Diproses</option>
            <option value="ready" <?= ($currentStatus === 'ready') ? 'selected' : '' ?>>Siap Saji</option>
            <option value="completed" <?= ($currentStatus === 'completed') ? 'selected' : '' ?>>Selesai</option>
            <option value="cancelled" <?= ($currentStatus === 'cancelled') ? 'selected' : '' ?>>Dibatalkan</option>
        </select>

        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Cari No. Order..." class="form-control form-control-sm bg-white shadow-sm" style="max-width: 160px;">

        <button type="submit" class="btn btn-sm btn-outline-dark bg-white shadow-sm">
            <i class="bi bi-funnel"></i> Filter
        </button>
    </form>
</div>

<div class="card-cafe">
    <div class="card-cafe-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No. Pesanan</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Tipe / Meja</th>
                        <th>Total Tagihan</th>
                        <th>Metode</th>
                        <th>Status Bayar</th>
                        <th>Status Dapur</th>
                        <th class="pe-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt-cutoff fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                Tidak ditemukan data pesanan untuk filter yang dipilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark">
                                    <a href="<?= url('/orders/' . $o['id']) ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($o['order_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div><?= Format::date($o['created_at'], 'H:i') ?></div>
                                    <div class="small text-muted" style="font-size: 0.75rem;"><?= Format::date($o['created_at'], 'd M Y') ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($o['customer_name']) ?></div>
                                    <div class="small text-muted" style="font-size: 0.75rem;"><?= $o['total_items'] ?> item menu</div>
                                </td>
                                <td>
                                    <?php if ($o['order_type'] === 'dine_in'): ?>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-shop me-1"></i> <?= $o['table_number'] ? 'Meja ' . $o['table_number'] : 'Dine-In' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-bag me-1"></i> Takeaway
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-secondary">
                                    <?= Format::rupiah($o['total_amount']) ?>
                                </td>
                                <td><?= Format::paymentMethodBadge($o['payment_method']) ?></td>
                                <td><?= Format::paymentStatusBadge($o['payment_status']) ?></td>
                                <td><?= Format::orderStatusBadge($o['order_status']) ?></td>
                                <td class="pe-3 text-end">
                                    <a href="<?= url('/orders/' . $o['id']) ?>" class="btn btn-sm btn-light border py-1 px-2.5 me-1" title="Lihat Rincian">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    <a href="<?= url('/orders/' . $o['id'] . '/receipt') ?>" target="_blank" class="btn btn-sm btn-outline-dark py-1 px-2.5" title="Cetak Struk">
                                        <i class="bi bi-printer"></i> Struk
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
