<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= url('/products') ?>" class="btn btn-light border btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <div>
            <h3 class="fw-bold mb-0 text-dark">Tambah Menu Baru</h3>
            <span class="text-muted small">Lengkapi informasi produk, harga jual, HPP, dan komposisi resep bahan.</span>
        </div>
    </div>
</div>

<form action="<?= url('/products') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- Main Form Column -->
        <div class="col-12 col-lg-8">
            <div class="card-cafe mb-4">
                <div class="card-cafe-header">
                    <h5 class="fw-bold mb-0">Informasi Dasar Menu</h5>
                </div>
                <div class="card-cafe-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small text-secondary">Kode SKU <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control" placeholder="Contoh: CF-LATTE" required value="<?= old('sku') ?>">
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold small text-secondary">Nama Menu / Produk <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Caffe Latte Single Origin" required value="<?= old('name') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Kategori Menu <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Satuan Penjualan</label>
                            <input type="text" name="unit" class="form-control" placeholder="cup, porsi, pcs, botol" value="cup">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" name="price" class="form-control fw-bold" placeholder="0" min="0" required value="<?= old('price') ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-secondary">HPP / Harga Modal Bahan (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" name="cost_price" class="form-control" placeholder="0" min="0" value="<?= old('cost_price', 0) ?>">
                            </div>
                            <div class="form-text small">Dihitung otomatis atau diisi perkiraan modal per sajian.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-secondary">Deskripsi Menu</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan cita rasa dan keunikan menu..."><?= old('description') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Configuration & Recipe Builder -->
            <div class="card-cafe">
                <div class="card-cafe-header">
                    <h5 class="fw-bold mb-0">Manajemen Stok & Resep Bahan</h5>
                </div>
                <div class="card-cafe-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Metode Pengurangan Stok:</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stock_type" id="stockTypeRecipe" value="recipe" checked onchange="toggleStockType()">
                                <label class="form-check-label fw-semibold" for="stockTypeRecipe">
                                    Resep Bahan Baku (Otomatis potong stok biji kopi, susu, sirup, dll)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stock_type" id="stockTypeDirect" value="direct" onchange="toggleStockType()">
                                <label class="form-check-label fw-semibold" for="stockTypeDirect">
                                    Stok Langsung (Pastry, botolan, atau stok siap saji)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Stock Section -->
                    <div id="directStockSection" class="row g-3 d-none mb-3 p-3 bg-light rounded-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Jumlah Stok Saat Ini</label>
                            <input type="number" name="stock" class="form-control" value="10" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Batas Minimum Peringatan Stok</label>
                            <input type="number" name="min_stock" class="form-control" value="5" min="0">
                        </div>
                    </div>

                    <!-- Recipe Ingredients Builder Section -->
                    <div id="recipeSection">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="small text-muted fw-semibold">Komposisi Bahan per 1 Porsi:</div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addRecipeRow()">
                                <i class="bi bi-plus"></i> Tambah Bahan
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0" id="recipeTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bahan Baku</th>
                                        <th style="width: 150px;">Jumlah Takaran</th>
                                        <th style="width: 60px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="recipeTableBody">
                                    <tr>
                                        <td>
                                            <select name="recipes[0][ingredient_id]" class="form-select form-select-sm">
                                                <option value="">Pilih Bahan Baku</option>
                                                <?php foreach ($ingredients as $ing): ?>
                                                    <option value="<?= $ing['id'] ?>"><?= htmlspecialchars($ing['name']) ?> (<?= $ing['unit'] ?>)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="recipes[0][quantity]" class="form-control form-control-sm" placeholder="Contoh: 18">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0 border-0" onclick="removeRecipeRow(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Status & Image Upload -->
        <div class="col-12 col-lg-4">
            <div class="card-cafe mb-4">
                <div class="card-cafe-header">
                    <h6 class="fw-bold mb-0">Status & Visibilitas</h6>
                </div>
                <div class="card-cafe-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Status Ketersediaan</label>
                        <select name="status" class="form-select">
                            <option value="tersedia" selected>Tersedia (Ready to Order)</option>
                            <option value="habis">Habis (Out of Stock)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Foto Menu</label>
                        <input type="file" name="image_file" class="form-control form-control-sm" accept="image/*">
                        <div class="form-text small">Format JPG, PNG, atau WEBP. Maksimal 2MB.</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-cafe w-100 py-2.5 shadow-sm fw-bold">
                <i class="bi bi-save me-1"></i> Simpan Menu Baru
            </button>
        </div>
    </div>
</form>

<script>
    let recipeRowCount = 1;

    function toggleStockType() {
        const isRecipe = document.getElementById('stockTypeRecipe').checked;
        const recipeSec = document.getElementById('recipeSection');
        const directSec = document.getElementById('directStockSection');

        if (isRecipe) {
            recipeSec.classList.remove('d-none');
            directSec.classList.add('d-none');
        } else {
            recipeSec.classList.add('d-none');
            directSec.classList.remove('d-none');
        }
    }

    function addRecipeRow() {
        const tbody = document.getElementById('recipeTableBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="recipes[${recipeRowCount}][ingredient_id]" class="form-select form-select-sm">
                    <option value="">Pilih Bahan Baku</option>
                    <?php foreach ($ingredients as $ing): ?>
                        <option value="<?= $ing['id'] ?>"><?= htmlspecialchars($ing['name']) ?> (<?= $ing['unit'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="recipes[${recipeRowCount}][quantity]" class="form-control form-control-sm" placeholder="Takaran">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-link text-danger p-0 border-0" onclick="removeRecipeRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        recipeRowCount++;
    }

    function removeRecipeRow(btn) {
        const row = btn.closest('tr');
        if (row) row.remove();
    }
</script>
