<?php
use App\Helpers\Format;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">Pengaturan Aplikasi & Log Audit</h2>
        <p class="text-muted mb-0 small">Konfigurasi profil kafe, struk, persentase pajak PPN, payment gateway Midtrans QRIS, dan riwayat aktivitas sistem.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Settings Tabs -->
    <div class="col-12 col-lg-8">
        <div class="card-cafe">
            <div class="card-cafe-header p-0 border-bottom-0">
                <ul class="nav nav-tabs border-0 px-3 pt-2" id="settingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold" id="cafe-tab" data-bs-toggle="tab" data-bs-target="#cafeTabPane" type="button" role="tab">
                            <i class="bi bi-shop me-1"></i> Profil Kafe & Struk
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="tax-tab" data-bs-toggle="tab" data-bs-target="#taxTabPane" type="button" role="tab">
                            <i class="bi bi-percent me-1"></i> Pajak & Biaya
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold" id="midtrans-tab" data-bs-toggle="tab" data-bs-target="#midtransTabPane" type="button" role="tab">
                            <i class="bi bi-qr-code me-1"></i> Gateway Midtrans (QRIS)
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-cafe-body p-4">
                <form action="<?= url('/settings') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="tab-content" id="settingsTabContent">
                        <!-- TAB 1: Profil Kafe & Struk -->
                        <div class="tab-pane fade show active" id="cafeTabPane" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-secondary">Nama Kafe</label>
                                    <input type="text" name="cafe_name" class="form-control" value="<?= htmlspecialchars($settings['cafe_name'] ?? 'CafeFlow Coffee & Eatery') ?>" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-secondary">Slogan / Tagline</label>
                                    <input type="text" name="cafe_tagline" class="form-control" value="<?= htmlspecialchars($settings['cafe_tagline'] ?? 'Artisan Brews & Cozy Vibes') ?>">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-secondary">No. Telepon / WhatsApp</label>
                                    <input type="text" name="cafe_phone" class="form-control" value="<?= htmlspecialchars($settings['cafe_phone'] ?? '0812-3456-7890') ?>">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-secondary">Mata Uang</label>
                                    <input type="text" name="currency_symbol" class="form-control" value="<?= htmlspecialchars($settings['currency_symbol'] ?? 'Rp') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-secondary">Alamat Lengkap</label>
                                    <input type="text" name="cafe_address" class="form-control" value="<?= htmlspecialchars($settings['cafe_address'] ?? 'Jl. Senopati No. 45, Jakarta Selatan') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-secondary">Catatan Kaki Struk (Receipt Footer)</label>
                                    <textarea name="receipt_footer" class="form-control" rows="3"><?= htmlspecialchars($settings['receipt_footer'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: Pajak & Biaya Layanan -->
                        <div class="tab-pane fade" id="taxTabPane" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-secondary">Pajak Restoran PPN (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" name="tax_percent" class="form-control" value="<?= htmlspecialchars($settings['tax_percent'] ?? '10') ?>" min="0" max="100">
                                        <span class="input-group-text bg-light">%</span>
                                    </div>
                                    <div class="form-text small">Dihitung otomatis pada subtotal pesanan kasir setelah diskon.</div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-secondary">Biaya Layanan / Service Charge (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" name="service_charge_percent" class="form-control" value="<?= htmlspecialchars($settings['service_charge_percent'] ?? '0') ?>" min="0" max="100">
                                        <span class="input-group-text bg-light">%</span>
                                    </div>
                                    <div class="form-text small">Dapat diatur 0% jika kafe tidak mengenakan biaya layanan.</div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: Midtrans Payment Gateway -->
                        <div class="tab-pane fade" id="midtransTabPane" role="tabpanel">
                            <div class="alert alert-info d-flex align-items-start gap-2 mb-3" style="border-radius: 12px;">
                                <i class="bi bi-info-circle-fill fs-5 mt-0.5"></i>
                                <div class="small">
                                    <b>Integrasi Resmi Midtrans QRIS:</b> Dapatkan kredensial dari 
                                    <a href="https://dashboard.midtrans.com" target="_blank" class="fw-bold text-decoration-none">dashboard.midtrans.com</a> 
                                    (Menu: <i>Settings &gt; Access Keys</i>). Masukkan Server Key & Client Key. Endpoint webhook resmi adalah <code>/api/webhook/midtrans</code>.
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-secondary">Environment Mode</label>
                                    <select name="midtrans_is_production" class="form-select">
                                        <option value="0" <?= (($settings['midtrans_is_production'] ?? '0') === '0') ? 'selected' : '' ?>>Sandbox (Pengujian & Demo)</option>
                                        <option value="1" <?= (($settings['midtrans_is_production'] ?? '0') === '1') ? 'selected' : '' ?>>Production (Live Transaksi Nyata)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-secondary">Merchant ID</label>
                                    <input type="text" name="midtrans_merchant_id" class="form-control font-monospace" value="<?= htmlspecialchars($settings['midtrans_merchant_id'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-secondary">Midtrans Server Key</label>
                                    <input type="text" name="midtrans_server_key" class="form-control font-monospace" value="<?= htmlspecialchars($settings['midtrans_server_key'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-secondary">Midtrans Client Key</label>
                                    <input type="text" name="midtrans_client_key" class="form-control font-monospace" value="<?= htmlspecialchars($settings['midtrans_client_key'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-4 text-end">
                        <button type="submit" class="btn btn-cafe px-4 py-2">
                            <i class="bi bi-save me-1"></i> Simpan Konfigurasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Security Information -->
    <div class="col-12 col-lg-4">
        <div class="card-cafe mb-4">
            <div class="card-cafe-header">
                <h6 class="fw-bold mb-0">Informasi Keamanan Transaksi</h6>
            </div>
            <div class="card-cafe-body small text-muted">
                <div class="mb-2 d-flex align-items-center gap-2 text-success fw-semibold">
                    <i class="bi bi-shield-check fs-5"></i> PDO Prepared Statements
                </div>
                <div class="mb-2 d-flex align-items-center gap-2 text-success fw-semibold">
                    <i class="bi bi-shield-check fs-5"></i> CSRF Token Active
                </div>
                <div class="mb-2 d-flex align-items-center gap-2 text-success fw-semibold">
                    <i class="bi bi-shield-check fs-5"></i> Midtrans SHA512 Verification
                </div>
                <div class="mb-2 d-flex align-items-center gap-2 text-success fw-semibold">
                    <i class="bi bi-shield-check fs-5"></i> Idempotent Webhook Guard
                </div>
                <div class="d-flex align-items-center gap-2 text-success fw-semibold">
                    <i class="bi bi-shield-check fs-5"></i> Server-side Order Price Recalculation
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Section: Audit Activity Logs Table -->
<div class="card-cafe mt-4">
    <div class="card-cafe-header">
        <div>
            <h5 class="fw-bold mb-0">Audit Log Aktivitas Terkini</h5>
            <div class="small text-muted">Catatan login, transaksi, modifikasi stok, dan status webhook</div>
        </div>
        <span class="badge bg-light text-dark border">50 Log Terakhir</span>
    </div>
    <div class="card-cafe-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Waktu</th>
                        <th>Aksi</th>
                        <th>Tipe / ID</th>
                        <th>Keterangan Aktivitas</th>
                        <th>Pengguna</th>
                        <th class="pe-3">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada catatan log.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= Format::date($l['created_at'], 'd/m/Y H:i:s') ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($l['action']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        <?= htmlspecialchars($l['entity_type'] ?? '-') ?> <?= $l['entity_id'] ? '#' . htmlspecialchars($l['entity_id']) : '' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($l['details'] ?? '') ?></td>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($l['user_name'] ?? 'Sistem / Tamu') ?></span>
                                    <?php if (!empty($l['user_role'])): ?>
                                        <span class="badge bg-light text-secondary border ms-1"><?= htmlspecialchars($l['user_role']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3 text-muted font-monospace"><?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
