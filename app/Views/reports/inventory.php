<?php
use App\Helpers\Format;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">Laporan Mutasi & Inventaris Stok</h2>
        <p class="text-muted mb-0 small">Audit jejak pergerakan stok: barang masuk, pengurangan otomatis akibat penjualan kasir, dan penyesuaian fisik.</p>
    </div>

    <form action="<?= url('/reports/inventory') ?>" method="GET" class="d-flex align-items-center gap-2">
        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="form-control form-control-sm bg-white shadow-sm" style="max-width: 140px;">
        <span class="text-muted small">-</span>
        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="form-control form-control-sm bg-white shadow-sm" style="max-width: 140px;">
        
        <select name="item_type" class="form-select form-select-sm bg-white shadow-sm" style="max-width: 150px;">
            <option value="">Semua Item</option>
            <option value="ingredient" <?= ($itemType === 'ingredient') ? 'selected' : '' ?>>Bahan Baku</option>
            <option value="product" <?= ($itemType === 'product') ? 'selected' : '' ?>>Produk Langsung</option>
        </select>

        <button type="submit" class="btn btn-sm btn-outline-dark bg-white shadow-sm">
            <i class="bi bi-funnel"></i> Filter
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary bg-white shadow-sm ms-2" onclick="window.print()">
            <i class="bi bi-printer"></i> Cetak
        </button>
    </form>
</div>

<!-- Inventory Movement Log Table -->
<div class="card-cafe">
    <div class="card-cafe-header">
        <h5 class="fw-bold mb-0">Riwayat Mutasi Stok (Inventory Movements)</h5>
        <span class="badge bg-light text-dark border"><?= count($movements) ?> Catatan</span>
    </div>
    <div class="card-cafe-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Waktu</th>
                        <th>Tipe Item</th>
                        <th>Nama Item</th>
                        <th>Jenis Mutasi</th>
                        <th class="text-end">Stok Awal</th>
                        <th class="text-center">Perubahan</th>
                        <th class="text-end">Stok Akhir</th>
                        <th>Referensi & Catatan</th>
                        <th class="pe-3">Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">Belum ada riwayat mutasi pada rentang tanggal ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movements as $m): ?>
                            <tr>
                                <td class="ps-3"><?= Format::date($m['created_at'], 'd/m/Y H:i') ?></td>
                                <td>
                                    <span class="badge <?= ($m['item_type'] === 'ingredient') ? 'bg-primary-subtle text-primary border border-primary' : 'bg-secondary-subtle text-secondary border' ?>">
                                        <?= ($m['item_type'] === 'ingredient') ? 'Bahan Baku' : 'Produk' ?>
                                    </span>
                                </td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($m['item_name'] ?? 'Item Terhapus') ?></td>
                                <td>
                                    <?php if ($m['movement_type'] === 'sale'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger">Penjualan</span>
                                    <?php elseif ($m['movement_type'] === 'in'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success">Masuk / Restock</span>
                                    <?php elseif ($m['movement_type'] === 'out'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning">Keluar / Rusak</span>
                                    <?php else: ?>
                                        <span class="badge bg-info-subtle text-info border border-info">Penyesuaian</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-muted"><?= number_format($m['previous_stock'], 1, ',', '.') ?></td>
                                <td class="text-center fw-bold <?= in_array($m['movement_type'], ['sale', 'out']) ? 'text-danger' : 'text-success' ?>">
                                    <?= in_array($m['movement_type'], ['sale', 'out']) ? '-' : '+' ?><?= number_format($m['quantity'], 1, ',', '.') ?> <?= htmlspecialchars($m['item_unit'] ?? '') ?>
                                </td>
                                <td class="text-end fw-bold text-dark"><?= number_format($m['new_stock'], 1, ',', '.') ?></td>
                                <td>
                                    <div class="small fw-semibold text-dark"><?= htmlspecialchars($m['reference_id'] ?? '-') ?></div>
                                    <div class="small text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($m['notes'] ?? '') ?></div>
                                </td>
                                <td class="pe-3 small text-muted"><?= htmlspecialchars($m['user_name'] ?? 'Sistem') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
