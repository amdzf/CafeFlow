<?php
use App\Helpers\Format;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">Manajemen Menu & Produk</h2>
        <p class="text-muted mb-0 small">Kelola katalog makanan, minuman, harga jual, HPP, serta konfigurasi resep bahan baku.</p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('/products/create') ?>" class="btn btn-cafe btn-sm d-flex align-items-center gap-1.5 shadow-sm">
            <i class="bi bi-plus-lg"></i> Tambah Menu Baru
        </a>
    </div>
</div>

<!-- Search & Category Filters -->
<div class="card-cafe mb-4">
    <div class="card-cafe-body p-3">
        <form action="<?= url('/products') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control form-control-sm" placeholder="Cari nama produk atau SKU...">
            </div>
            <div class="col-12 col-md-3">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($selectedCategory == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-dark bg-white">
                    <i class="bi bi-funnel"></i> Terapkan
                </button>
                <?php if ($search || $selectedCategory): ?>
                    <a href="<?= url('/products') ?>" class="btn btn-sm btn-link text-muted">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card-cafe">
    <div class="card-cafe-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 70px;">Foto</th>
                        <th>Kode / SKU</th>
                        <th>Nama Menu</th>
                        <th>Kategori</th>
                        <th>Harga Jual</th>
                        <th>HPP (Modal)</th>
                        <th>Tipe Stok</th>
                        <th>Status</th>
                        <th class="pe-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                Belum ada data produk atau tidak ditemukan hasil pencarian.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td class="ps-3">
                                    <?php if (has_product_image($p['image'] ?? null)): ?>
                                        <img src="<?= product_image_url($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="rounded shadow-sm" style="width: 48px; height: 48px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px; background: #F4EBE2;">
                                            <i class="bi bi-cup-hot text-secondary"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold font-monospace text-muted"><?= htmlspecialchars($p['sku']) ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($p['name']) ?></div>
                                    <?php if (!empty($p['description'])): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 250px; font-size: 0.75rem;">
                                            <?= htmlspecialchars($p['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category_name']) ?></span></td>
                                <td class="fw-bold text-secondary"><?= Format::rupiah($p['price']) ?></td>
                                <td class="text-muted"><?= Format::rupiah($p['cost_price']) ?></td>
                                <td>
                                    <?php if ($p['stock_type'] === 'recipe'): ?>
                                        <span class="badge bg-info-subtle text-info border border-info">
                                            <i class="bi bi-diagram-3 me-1"></i> Resep Bahan
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">
                                            <?= $p['stock'] ?> <?= htmlspecialchars($p['unit']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['status'] === 'tersedia'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger">Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3 text-end">
                                    <a href="<?= url('/products/' . $p['id'] . '/edit') ?>" class="btn btn-sm btn-light border py-1 px-2.5 me-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="<?= url('/products/' . $p['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan menu ini?')">
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
