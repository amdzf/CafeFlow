<?php
use App\Helpers\Format;
?>

<div class="pos-wrapper">
    <!-- Left Column: Product Catalog -->
    <div class="pos-catalog">
        <!-- Top Toolbar: Search & Order Number -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark px-3 py-2 fs-6 fw-bold" style="border-radius: 10px;">
                    <i class="bi bi-receipt me-1"></i> <span id="posOrderNumber"><?= htmlspecialchars($nextOrderNumber) ?></span>
                </span>
                <span class="badge bg-secondary-subtle text-secondary px-2.5 py-2 small">Kasir Aktif</span>
            </div>

            <!-- Instant Search Input -->
            <div class="flex-grow-1" style="max-width: 380px;">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="productSearchInput" class="form-control border-start-0 ps-0" placeholder="Cari nama produk atau SKU..." style="border-radius: 0 10px 10px 0;">
                </div>
            </div>
        </div>

        <!-- Category Filter Pills -->
        <div class="d-flex align-items-center gap-2 mb-3 pb-1" style="overflow-x: auto; white-space: nowrap;" id="categoryFilterContainer">
            <button type="button" class="category-pill active" data-category="all">
                <i class="bi bi-grid-fill"></i> Semua Menu
            </button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="category-pill" data-category="<?= htmlspecialchars($cat['slug']) ?>">
                    <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i> <?= htmlspecialchars($cat['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Product Cards Grid -->
        <div class="flex-grow-1" style="overflow-y: auto; padding-right: 4px;">
            <div class="row g-3" id="productGrid">
                <?php foreach ($products as $p): ?>
                    <div class="col-6 col-md-4 col-xl-3 product-item" 
                         data-id="<?= $p['id'] ?>"
                         data-sku="<?= htmlspecialchars($p['sku']) ?>"
                         data-name="<?= htmlspecialchars($p['name']) ?>"
                         data-price="<?= $p['price'] ?>"
                         data-category="<?= htmlspecialchars($p['category_slug']) ?>"
                         data-available="<?= $p['is_available'] ? '1' : '0' ?>">
                        <div class="pos-product-card <?= !$p['is_available'] ? 'opacity-50' : '' ?>" onclick="addToCart(<?= htmlspecialchars(json_encode($p)) ?>)">
                            <div class="position-relative">
                                <?php if (has_product_image($p['image'] ?? null)): ?>
                                    <img src="<?= product_image_url($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="pos-product-img" loading="lazy">
                                <?php else: ?>
                                    <div class="pos-product-img d-flex align-items-center justify-content-center text-muted" style="background: linear-gradient(135deg, #F4EBE2 0%, #EAE3D9 100%);">
                                        <i class="bi bi-cup-hot fs-1 text-secondary" style="opacity: 0.7;"></i>
                                    </div>
                                <?php endif; ?>

                                <span class="badge position-absolute top-0 end-0 m-2 <?= $p['is_available'] ? 'bg-success' : 'bg-danger' ?>" style="font-size: 0.7rem;">
                                    <?= htmlspecialchars($p['display_stock']) ?>
                                </span>
                            </div>

                            <div class="pos-product-body">
                                <div class="pos-product-title text-truncate" title="<?= htmlspecialchars($p['name']) ?>">
                                    <?= htmlspecialchars($p['name']) ?>
                                </div>
                                <div class="small text-muted mb-2" style="font-size: 0.75rem;">
                                    <?= htmlspecialchars($p['category_name']) ?>
                                </div>
                                <div class="mt-auto d-flex align-items-center justify-content-between">
                                    <div class="pos-product-price">
                                        <?= Format::rupiah($p['price']) ?>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" <?= !$p['is_available'] ? 'disabled' : '' ?>>
                                        <i class="bi bi-plus-lg text-secondary"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="noProductAlert" class="text-center py-5 text-muted d-none">
                <i class="bi bi-search fs-1 d-block mb-2"></i>
                <div>Tidak ada menu yang sesuai dengan pencarian Anda.</div>
            </div>
        </div>
    </div>

    <!-- Right Column: Cart & Checkout Panel -->
    <div class="pos-cart-panel">
        <!-- Cart Header -->
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-white" style="border-radius: var(--card-radius) var(--card-radius) 0 0;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-bag-check-fill text-secondary fs-5"></i>
                <h5 class="fw-bold mb-0 text-dark">Keranjang Pesanan</h5>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="clearCart()" title="Kosongkan Keranjang">
                <i class="bi bi-trash3"></i>
            </button>
        </div>

        <!-- Order Options: Type, Customer, Table -->
        <div class="p-3 border-bottom bg-light bg-opacity-50">
            <!-- Order Type: Dine-in / Takeaway -->
            <div class="btn-group w-100 mb-2.5" role="group">
                <input type="radio" class="btn-check" name="posOrderType" id="typeDineIn" value="dine_in" checked onchange="handleOrderTypeChange()">
                <label class="btn btn-sm btn-outline-secondary" for="typeDineIn"><i class="bi bi-shop me-1"></i> Dine-In (Meja)</label>

                <input type="radio" class="btn-check" name="posOrderType" id="typeTakeaway" value="takeaway" onchange="handleOrderTypeChange()">
                <label class="btn btn-sm btn-outline-secondary" for="typeTakeaway"><i class="bi bi-bag me-1"></i> Takeaway (Bungkus)</label>
            </div>

            <div class="row g-2">
                <div class="col-7">
                    <input type="text" id="customerNameInput" class="form-control form-control-sm" placeholder="Nama Pelanggan (opsional)" value="Pelanggan Walk-in">
                </div>
                <div class="col-5" id="tableSelectContainer">
                    <select id="tableSelect" class="form-select form-select-sm">
                        <option value="">Pilih Meja</option>
                        <?php foreach ($tables as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($t['status'] === 'occupied') ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($t['table_number']) ?> (<?= htmlspecialchars($t['name']) ?>) <?= ($t['status'] === 'occupied') ? '[Terisi]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Cart Items Scroll List -->
        <div class="cart-items-list" id="cartItemsContainer">
            <!-- Dynamically populated via pos.js -->
            <div id="cartEmptyState" class="text-center py-5 text-muted">
                <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <div class="fw-semibold">Keranjang Masih Kosong</div>
                <div class="small text-muted">Pilih menu di samping untuk menambahkan pesanan.</div>
            </div>
        </div>

        <!-- Cart Summary Calculations -->
        <div class="p-3 border-top bg-white" style="border-radius: 0 0 var(--card-radius) var(--card-radius);">
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Subtotal</span>
                <span class="fw-semibold text-dark" id="displaySubtotal">Rp 0</span>
            </div>

            <!-- Discount Input -->
            <div class="d-flex align-items-center justify-content-between small text-muted mb-1">
                <span class="d-flex align-items-center gap-1">
                    Diskon (%)
                    <input type="number" id="discountPercentInput" class="form-control form-control-sm py-0 px-1 text-center" style="width: 50px; height: 24px;" min="0" max="100" value="0" oninput="calculateTotals()">
                </span>
                <span class="fw-semibold text-danger" id="displayDiscount">- Rp 0</span>
            </div>

            <!-- Tax (PPN) -->
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Pajak PPN (<?= $taxPercent ?>%)</span>
                <span class="fw-semibold text-dark" id="displayTax">Rp 0</span>
            </div>

            <!-- Grand Total -->
            <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top">
                <span class="fw-bold text-dark fs-6">Total Tagihan:</span>
                <span class="fw-bold text-secondary fs-4" id="displayGrandTotal">Rp 0</span>
            </div>

            <!-- Action Buttons: Cash & QRIS -->
            <div class="row g-2 mt-2">
                <div class="col-6">
                    <button type="button" class="btn btn-cash w-100 d-flex align-items-center justify-content-center gap-2" onclick="openCashPaymentModal()">
                        <i class="bi bi-cash-stack"></i> TUNAI
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="btn btn-qris w-100 d-flex align-items-center justify-content-center gap-2" onclick="openQrisPaymentModal()">
                        <i class="bi bi-qr-code-scan"></i> QRIS
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: PEMBAYARAN TUNAI (CASH)           -->
<!-- ========================================== -->
<div class="modal fade" id="cashModal" tabindex="-1" aria-labelledby="cashModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon green" style="width: 42px; height: 42px; font-size: 1.3rem;">
                        <i class="bi bi-cash"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" id="cashModalLabel">Pembayaran Tunai</h5>
                        <div class="small text-muted">Input uang yang diterima & hitung kembalian</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded-4 text-center mb-3">
                    <div class="small text-muted fw-semibold uppercase mb-1">Total yang Harus Dibayar:</div>
                    <div class="display-6 fw-bold text-dark" id="cashModalGrandTotal">Rp 0</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-secondary">Nominal Uang Diterima (Rp):</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white fw-bold">Rp</span>
                        <input type="number" id="cashTenderedInput" class="form-control fw-bold fs-4" placeholder="0" min="0" oninput="calculateCashChange()">
                    </div>
                </div>

                <!-- Quick Cash Shortcut Buttons -->
                <div class="mb-4">
                    <label class="form-label fw-semibold small text-muted">Pilihan Cepat:</label>
                    <div class="d-flex flex-wrap gap-2" id="quickCashButtons">
                        <button type="button" class="btn btn-quick-cash" onclick="setQuickCash('exact')">Uang Pas</button>
                        <button type="button" class="btn btn-quick-cash" onclick="setQuickCash(20000)">20.000</button>
                        <button type="button" class="btn btn-quick-cash" onclick="setQuickCash(50000)">50.000</button>
                        <button type="button" class="btn btn-quick-cash" onclick="setQuickCash(100000)">100.000</button>
                        <button type="button" class="btn btn-quick-cash" onclick="setQuickCash(200000)">200.000</button>
                        <button type="button" class="btn btn-quick-cash" onclick="setQuickCash(500000)">500.000</button>
                    </div>
                </div>

                <!-- Kembalian Display -->
                <div class="p-3 rounded-4 border d-flex align-items-center justify-content-between" id="changeContainer" style="background-color: var(--cafe-accent-light);">
                    <div>
                        <div class="small text-muted fw-semibold">Uang Kembalian:</div>
                        <div class="fs-3 fw-bold text-success" id="cashChangeDisplay">Rp 0</div>
                    </div>
                    <i class="bi bi-wallet-fill text-success fs-1 opacity-50"></i>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-cash px-4 py-2.5" id="btnConfirmCash" onclick="submitCashPayment()">
                    <i class="bi bi-check-lg me-1"></i> Proses & Konfirmasi Bayar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: PEMBAYARAN QRIS DINAMIS           -->
<!-- ========================================== -->
<div class="modal fade" id="qrisModal" tabindex="-1" aria-labelledby="qrisModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-center" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopQrisPolling()"></button>
            </div>

            <div class="modal-body p-4 pt-0">
                <div class="d-inline-flex align-items-center justify-content-center mb-2" style="width: 52px; height: 52px; border-radius: 14px; background: #E0F2FE; color: #0284C7; font-size: 1.5rem;">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
                <h4 class="fw-bold mb-1 text-dark">QRIS Pembayaran Dinamis</h4>
                <div class="text-muted small mb-3">Scan QR code melalui BCA, Mandiri, GoPay, OVO, Dana, ShopeePay atau LinkAja</div>

                <!-- Grand Total Box -->
                <div class="p-3 bg-light rounded-4 mb-3">
                    <div class="small text-muted mb-1">Total Tagihan:</div>
                    <div class="fs-2 fw-bold text-primary" id="qrisModalTotal">Rp 0</div>
                    <div class="small text-muted">No. Pesanan: <span class="fw-semibold text-dark" id="qrisOrderNumDisplay">-</span></div>
                </div>

                <!-- QR Code Display Box -->
                <div class="position-relative d-inline-block p-3 bg-white border rounded-4 shadow-sm mb-3" style="min-width: 260px; min-height: 260px;">
                    <div id="qrisLoadingSpinner" class="d-flex flex-column align-items-center justify-content-center" style="height: 230px;">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <span class="small text-muted">Menghubungkan ke Payment Gateway...</span>
                    </div>

                    <img id="qrisCodeImage" src="" alt="QRIS Code" class="img-fluid d-none" style="width: 240px; height: 240px; border-radius: 8px;">

                    <!-- Success Overlay inside QR box once verified -->
                    <div id="qrisSuccessOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column align-items-center justify-content-center bg-white rounded-4" style="background: rgba(255,255,255,0.95) !important;">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        <h5 class="fw-bold text-success mt-2">Pembayaran Berhasil!</h5>
                        <p class="small text-muted mb-0">Terverifikasi otomatis oleh gateway</p>
                    </div>
                </div>

                <!-- Countdown Timer & Gateway Status Badge -->
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    <span class="badge bg-danger-subtle text-danger border border-danger px-2.5 py-1.5" id="qrisTimerBadge">
                        <i class="bi bi-clock me-1"></i> Berlaku: <span id="qrisCountdown">15:00</span>
                    </span>
                    <span class="badge bg-warning-subtle text-warning border border-warning px-2.5 py-1.5" id="qrisStatusBadge">
                        <i class="bi bi-hourglass-split me-1"></i> Menunggu Scan Pelanggan
                    </span>
                </div>

                <!-- Sandbox Simulator Box (For Instant Testing Without External App) -->
                <div class="p-3 border rounded-3 bg-light text-start mt-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge bg-info-subtle text-info border border-info" style="font-size: 0.72rem;">Sandbox Developer Tool</span>
                        <span class="small text-muted" style="font-size: 0.72rem;">Uji Validasi Webhook</span>
                    </div>
                    <div class="small text-muted mb-2" style="font-size: 0.8rem;">
                        Klik tombol simulasi di bawah untuk menguji webhook payment gateway dengan signature SHA512 valid secara otomatis:
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success w-100" id="btnSimulatePay" onclick="simulatePaymentWebhook('settlement')">
                            <i class="bi bi-check2-circle me-1"></i> Simulasi Bayar Sukses
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnSimulateExpire" onclick="simulatePaymentWebhook('expire')" title="Simulasi Kedaluwarsa">
                            <i class="bi bi-x-circle"></i> Expire
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="stopQrisPolling()">Tutup</button>
                <button type="button" class="btn btn-cafe d-none" id="btnQrisPrintReceipt" onclick="printCurrentReceipt()">
                    <i class="bi bi-printer me-1"></i> Cetak Struk Belanja
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 3: PREVIEW STRUK THERMAL             -->
<!-- ========================================== -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="receiptModalLabel">Struk Pembayaran</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="receiptIframeContainer" style="height: 480px;">
                <iframe id="receiptIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
            <div class="modal-footer border-0 pt-2 d-flex gap-2">
                <button type="button" class="btn btn-light btn-sm flex-grow-1" data-bs-dismiss="modal" onclick="startNewPosOrder()">
                    <i class="bi bi-plus-circle me-1"></i> Pesanan Baru
                </button>
                <button type="button" class="btn btn-cafe btn-sm flex-grow-1" onclick="printReceiptFrame()">
                    <i class="bi bi-printer me-1"></i> Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Pass backend configuration parameters into Javascript -->
<script>
    window.POS_CONFIG = {
        taxPercent: <?= (float)$taxPercent ?>,
        servicePercent: <?= (float)$servicePercent ?>,
        currency: '<?= addslashes($currency) ?>',
        createOrderUrl: '<?= url('/api/orders/create') ?>',
        payCashUrl: '<?= url('/api/payments/cash') ?>',
        createQrisUrl: '<?= url('/api/payments/qris') ?>',
        checkStatusUrl: '<?= url('/api/payments/status/') ?>',
        simulateWebhookUrl: '<?= url('/api/webhook/simulate') ?>',
        receiptBaseUrl: '<?= url('/orders/') ?>',
        csrfToken: '<?= csrf_token() ?>'
    };
</script>
<script src="<?= asset('js/pos.js') ?>"></script>
