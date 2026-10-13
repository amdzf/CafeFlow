<?php
use App\Helpers\Format;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">Bahan Baku & Manajemen Stok</h2>
        <p class="text-muted mb-0 small">Pantau stok bahan mentah (biji kopi, susu, sirup, dll) dan lakukan restock harian.</p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-cafe btn-sm d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
            <i class="bi bi-plus-lg"></i> Tambah Bahan Baku
        </button>
    </div>
</div>

<div class="card-cafe">
    <div class="card-cafe-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode Bahan</th>
                        <th>Nama Bahan Baku</th>
                        <th>Stok Saat Ini</th>
                        <th>Batas Min. Stok</th>
                        <th>Biaya per Satuan</th>
                        <th>Status Stok</th>
                        <th class="pe-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ingredients)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Belum ada data bahan baku.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ingredients as $ing): ?>
                            <tr>
                                <td class="ps-3 font-monospace fw-bold text-muted"><?= htmlspecialchars($ing['code']) ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($ing['name']) ?></div>
                                </td>
                                <td>
                                    <span class="fw-bold fs-6 <?= $ing['is_low_stock'] ? 'text-danger' : 'text-dark' ?>">
                                        <?= number_format($ing['stock'], 1, ',', '.') ?> <?= htmlspecialchars($ing['unit']) ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?= number_format($ing['min_stock'], 1, ',', '.') ?> <?= htmlspecialchars($ing['unit']) ?></td>
                                <td class="text-muted"><?= Format::rupiah($ing['cost_per_unit']) ?> / <?= htmlspecialchars($ing['unit']) ?></td>
                                <td>
                                    <?php if ($ing['is_low_stock']): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Stok Menipis
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success">
                                            <i class="bi bi-check-circle me-1"></i> Aman
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 me-1" 
                                            onclick='openAdjustModal(<?= json_encode($ing) ?>)' title="Restock / Sesuaikan Stok">
                                        <i class="bi bi-arrow-down-up"></i> Restock
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 me-1" 
                                            onclick='openEditModal(<?= json_encode($ing) ?>)' title="Edit Bahan">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="<?= url('/ingredients/' . $ing['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus bahan baku ini?')">
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

<!-- Modal 1: Tambah Bahan Baku -->
<div class="modal fade" id="addIngredientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Tambah Bahan Baku Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/ingredients') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Kode Bahan <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="Contoh: ING-SYRUP-VANILLA" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Nama Bahan Baku <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Sirup Vanilla Gourmet" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Satuan Ukur <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control" placeholder="gram, ml, pcs, lembar" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Stok Awal</label>
                            <input type="number" step="0.01" name="stock" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Biaya Modal per Satuan (Rp)</label>
                            <input type="number" step="0.01" name="cost_per_unit" class="form-control" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Batas Min. Peringatan</label>
                            <input type="number" step="0.01" name="min_stock" class="form-control" value="100">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-cafe">Simpan Bahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Edit Bahan Baku -->
<div class="modal fade" id="editIngredientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Bahan Baku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editIngredientForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Kode Bahan <span class="text-danger">*</span></label>
                        <input type="text" id="editCode" name="code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Nama Bahan Baku <span class="text-danger">*</span></label>
                        <input type="text" id="editName" name="name" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Satuan</label>
                            <input type="text" id="editUnit" name="unit" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Batas Min. Peringatan</label>
                            <input type="number" step="0.01" id="editMinStock" name="min_stock" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Biaya per Satuan (Rp)</label>
                        <input type="number" step="0.01" id="editCost" name="cost_per_unit" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-cafe">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Restock / Penyesuaian Stok -->
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Restock / Penyesuaian Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjustStockForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="fw-bold text-dark fs-6" id="adjustItemName">-</div>
                        <div class="small text-muted">Stok saat ini: <b class="text-dark" id="adjustItemCurrentStock">-</b></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Jenis Penyesuaian</label>
                        <select name="movement_type" class="form-select">
                            <option value="in" selected>Masuk / Restock Pembelian (+)</option>
                            <option value="out">Keluar / Kerusakan / Basi (-)</option>
                            <option value="adjustment">Penyesuaian Opname Fisik</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Jumlah Perubahan (<span id="adjustUnitLabel">unit</span>)</label>
                        <input type="number" step="0.01" name="quantity_change" class="form-control fw-bold" placeholder="0" required min="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Catatan / Keterangan</label>
                        <input type="text" name="notes" class="form-control" placeholder="Contoh: Belanja mingguan Roastery, supplier faktur #123">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4">Proses Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(ing) {
        document.getElementById('editIngredientForm').action = '<?= url('/ingredients/') ?>' + ing.id + '/update';
        document.getElementById('editCode').value = ing.code;
        document.getElementById('editName').value = ing.name;
        document.getElementById('editUnit').value = ing.unit;
        document.getElementById('editMinStock').value = ing.min_stock;
        document.getElementById('editCost').value = ing.cost_per_unit;
        new bootstrap.Modal(document.getElementById('editIngredientModal')).show();
    }

    function openAdjustModal(ing) {
        document.getElementById('adjustStockForm').action = '<?= url('/ingredients/') ?>' + ing.id + '/adjust';
        document.getElementById('adjustItemName').textContent = ing.name + ' (' + ing.code + ')';
        document.getElementById('adjustItemCurrentStock').textContent = ing.stock + ' ' + ing.unit;
        document.getElementById('adjustUnitLabel').textContent = ing.unit;
        new bootstrap.Modal(document.getElementById('adjustStockModal')).show();
    }
</script>
