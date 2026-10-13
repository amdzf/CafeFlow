<?php
use App\Helpers\Format;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">Pengeluaran Operasional Cafe</h2>
        <p class="text-muted mb-0 small">Catat dan pantau seluruh beban operasional seperti bahan baku, gaji karyawan, listrik, sewa, dan perawatan.</p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-cafe btn-sm d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="bi bi-plus-lg"></i> Catat Pengeluaran
        </button>
    </div>
</div>

<!-- Filter Bar -->
<div class="card-cafe mb-4">
    <div class="card-cafe-body p-3">
        <form action="<?= url('/expenses') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-12 col-md-3">
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-12 col-md-3">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Semua Kategori Beban</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($selectedCategory == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-dark bg-white">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            <div class="col-auto ms-auto">
                <span class="text-muted small me-2">Total Periode:</span>
                <span class="fw-bold text-danger fs-5"><?= Format::rupiah($totalAmount) ?></span>
            </div>
        </form>
    </div>
</div>

<!-- Expenses Table -->
<div class="card-cafe">
    <div class="card-cafe-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Tanggal</th>
                        <th>Kategori Beban</th>
                        <th>Keterangan / Judul Pengeluaran</th>
                        <th>Nominal Biaya</th>
                        <th>Dicatat Oleh</th>
                        <th class="pe-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada data pengeluaran pada periode ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $e): ?>
                            <tr>
                                <td class="ps-3"><?= Format::date($e['expense_date'], 'd M Y') ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($e['category_name']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($e['title']) ?></div>
                                    <?php if (!empty($e['description'])): ?>
                                        <div class="small text-muted" style="font-size: 0.78rem;"><?= htmlspecialchars($e['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-danger"><?= Format::rupiah($e['amount']) ?></td>
                                <td><?= htmlspecialchars($e['user_name']) ?></td>
                                <td class="pe-3 text-end">
                                    <form action="<?= url('/expenses/' . $e['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus data pengeluaran ini?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2.5" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Catat Pengeluaran -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Catat Pengeluaran Operasional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/expenses') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Kategori Beban <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Judul Pengeluaran <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Pembelian Fresh Milk 2 Dus" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Nominal Biaya (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="amount" class="form-control fw-bold fs-5" placeholder="0" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Catatan / Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Keterangan tambahan (nomor nota, toko supplier, dll)"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-cafe">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
