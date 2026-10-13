<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= url('/products') ?>" class="btn btn-light border btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <div>
            <h3 class="fw-bold mb-0 text-dark">Edit Menu: <?= htmlspecialchars($product['name']) ?></h3>
            <span class="text-muted small">Perbarui detail produk, harga, HPP, atau racikan resep bahan baku.</span>
        </div>
    </div>
</div>

<form action="<?= url('/products/' . $product['id'] . '/update') ?>" method="POST" enctype="multipart/form-data">
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
                            <input type="text" name="sku" class="form-control" required value="<?= htmlspecialchars($product['sku']) ?>">
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold small text-secondary">Nama Menu / Produk <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Kategori Menu <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Satuan Penjualan</label>
                            <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($product['unit']) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-secondary">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" name="price" class="form-control fw-bold" min="0" required value="<?= (float)$product['price'] ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-secondary">HPP / Harga Modal Bahan (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" name="cost_price" class="form-control" min="0" value="<?= (float)$product['cost_price'] ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-secondary">Deskripsi Menu</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
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
                                <input class="form-check-input" type="radio" name="stock_type" id="stockTypeRecipe" value="recipe" <?= ($product['stock_type'] === 'recipe') ? 'checked' : '' ?> onchange="toggleStockType()">
                                <label class="form-check-label fw-semibold" for="stockTypeRecipe">
                                    Resep Bahan Baku
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stock_type" id="stockTypeDirect" value="direct" <?= ($product['stock_type'] === 'direct') ? 'checked' : '' ?> onchange="toggleStockType()">
                                <label class="form-check-label fw-semibold" for="stockTypeDirect">
                                    Stok Langsung
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Stock Section -->
                    <div id="directStockSection" class="row g-3 <?= ($product['stock_type'] === 'recipe') ? 'd-none' : '' ?> mb-3 p-3 bg-light rounded-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Jumlah Stok Saat Ini</label>
                            <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-secondary">Batas Minimum Peringatan</label>
                            <input type="number" name="min_stock" class="form-control" value="<?= $product['min_stock'] ?>" min="0">
                        </div>
                    </div>

                    <!-- Recipe Ingredients Builder Section -->
                    <div id="recipeSection" class="<?= ($product['stock_type'] === 'direct') ? 'd-none' : '' ?>">
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
                                    <?php if (!empty($product['recipes'])): ?>
                                        <?php foreach ($product['recipes'] as $idx => $rec): ?>
                                            <tr>
                                                <td>
                                                    <select name="recipes[<?= $idx ?>][ingredient_id]" class="form-select form-select-sm">
                                                        <option value="">Pilih Bahan Baku</option>
                                                        <?php foreach ($ingredients as $ing): ?>
                                                            <option value="<?= $ing['id'] ?>" <?= ($rec['ingredient_id'] == $ing['id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($ing['name']) ?> (<?= $ing['unit'] ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="recipes[<?= $idx ?>][quantity]" class="form-control form-control-sm" value="<?= (float)$rec['quantity'] ?>">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 border-0" onclick="removeRecipeRow(this)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
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
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Status & Photo -->
        <div class="col-12 col-lg-4">
            <div class="card-cafe mb-4">
                <div class="card-cafe-header">
                    <h6 class="fw-bold mb-0">Status & Foto</h6>
                </div>
                <div class="card-cafe-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Status Ketersediaan</label>
                        <select name="status" class="form-select">
                            <option value="tersedia" <?= ($product['status'] === 'tersedia') ? 'selected' : '' ?>>Tersedia (Ready to Order)</option>
                            <option value="habis" <?= ($product['status'] === 'habis') ? 'selected' : '' ?>>Habis (Out of Stock)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Foto Menu</label>
                        <?php if (has_product_image($product['image'] ?? null)): ?>
                            <div class="mb-2">
                                <img src="<?= product_image_url($product['image']) ?>" alt="Foto" class="rounded img-fluid shadow-sm border" style="max-height: 140px; object-fit: cover;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image_file" class="form-control form-control-sm" accept="image/*">
                        <div class="form-text small">Pilih file baru jika ingin mengganti foto.</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-cafe w-100 py-2.5 shadow-sm fw-bold">
                <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
            </button>
        </div>
    </div>
</form>

<script>
    let recipeRowCount = <?= max(1, count($product['recipes'] ?? [])) ?>;

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
