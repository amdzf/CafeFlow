<?php
use App\Helpers\Format;
$payment = $order['latest_payment'] ?? null;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= url('/orders') ?>" class="btn btn-light border btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <div>
            <h3 class="fw-bold mb-0 text-dark">Pesanan #<?= htmlspecialchars($order['order_number']) ?></h3>
            <span class="text-muted small">Dibuat pada <?= Format::date($order['created_at'], 'd M Y, H:i:s') ?> oleh <?= htmlspecialchars($order['cashier_name'] ?? 'Kasir') ?></span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('/orders/' . $order['id'] . '/receipt') ?>" target="_blank" class="btn btn-cafe btn-sm d-flex align-items-center gap-1.5 shadow-sm">
            <i class="bi bi-printer"></i> Cetak Struk Belanja
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left: Order Items & Calculation Details -->
    <div class="col-12 col-lg-8">
        <div class="card-cafe mb-4">
            <div class="card-cafe-header">
                <h5 class="fw-bold mb-0">Rincian Item Menu</h5>
                <span class="badge bg-light text-dark border"><?= count($order['items']) ?> Jenis Menu</span>
            </div>
            <div class="card-cafe-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Menu</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">HPP (Modal)</th>
                                <th class="pe-3 text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($item['product_name']) ?></div>
                                        <?php if (!empty($item['notes'])): ?>
                                            <div class="small text-danger fst-italic">Catatan: <?= htmlspecialchars($item['notes']) ?></div>
                                        <?php endif; ?>
                                        <div class="small text-muted" style="font-size: 0.75rem;">SKU: <?= htmlspecialchars($item['sku'] ?? '-') ?></div>
                                    </td>
                                    <td class="text-center fw-bold"><?= $item['quantity'] ?></td>
                                    <td class="text-end text-muted"><?= Format::rupiah($item['unit_price']) ?></td>
                                    <td class="text-end text-muted"><?= Format::rupiah($item['cost_price']) ?></td>
                                    <td class="pe-3 text-end fw-bold text-dark"><?= Format::rupiah($item['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Financial Breakdown Table Footer -->
            <div class="p-3 border-top bg-light bg-opacity-50">
                <div class="row justify-content-end">
                    <div class="col-12 col-md-6">
                        <div class="d-flex justify-content-between py-1 text-muted small">
                            <span>Subtotal Menu:</span>
                            <span class="fw-semibold text-dark"><?= Format::rupiah($order['subtotal']) ?></span>
                        </div>
                        <?php if ((float)$order['discount_amount'] > 0): ?>
                            <div class="d-flex justify-content-between py-1 text-muted small">
                                <span>Potongan Diskon <?= ($order['discount_percent'] > 0) ? "({$order['discount_percent']}%)" : '' ?>:</span>
                                <span class="fw-semibold text-danger">- <?= Format::rupiah($order['discount_amount']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ((float)$order['tax_amount'] > 0): ?>
                            <div class="d-flex justify-content-between py-1 text-muted small">
                                <span>Pajak Restoran (PPN):</span>
                                <span class="fw-semibold text-dark"><?= Format::rupiah($order['tax_amount']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ((float)$order['service_charge'] > 0): ?>
                            <div class="d-flex justify-content-between py-1 text-muted small">
                                <span>Biaya Layanan:</span>
                                <span class="fw-semibold text-dark"><?= Format::rupiah($order['service_charge']) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between py-2 border-top mt-2">
                            <span class="fw-bold fs-5 text-dark">Total Tagihan:</span>
                            <span class="fw-bold fs-4 text-secondary"><?= Format::rupiah($order['total_amount']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Status, Payment & Customer Card -->
    <div class="col-12 col-lg-4">
        <!-- Order Meta Card -->
        <div class="card-cafe mb-4">
            <div class="card-cafe-header">
                <h6 class="fw-bold mb-0">Informasi Pelanggan</h6>
            </div>
            <div class="card-cafe-body">
                <div class="mb-3">
                    <div class="small text-muted">Nama Pelanggan</div>
                    <div class="fw-semibold text-dark fs-6"><?= htmlspecialchars($order['customer_name']) ?></div>
                </div>
                <div class="mb-3">
                    <div class="small text-muted">Tipe Pesanan</div>
                    <div>
                        <?php if ($order['order_type'] === 'dine_in'): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary px-2.5 py-1.5">
                                <i class="bi bi-shop me-1"></i> Dine-In (<?= $order['table_number'] ? 'Meja ' . $order['table_number'] : '-' ?>)
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2.5 py-1.5">
                                <i class="bi bi-bag me-1"></i> Takeaway
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="small text-muted">Status Pengerjaan Dapur</div>
                    <div class="mt-1"><?= Format::orderStatusBadge($order['order_status']) ?></div>
                </div>
                <div>
                    <div class="small text-muted">Status Pembayaran</div>
                    <div class="mt-1"><?= Format::paymentStatusBadge($order['payment_status']) ?></div>
                </div>
            </div>
        </div>

        <!-- Payment Records Card -->
        <div class="card-cafe">
            <div class="card-cafe-header">
                <h6 class="fw-bold mb-0">Data Pembayaran</h6>
            </div>
            <div class="card-cafe-body">
                <?php if ($payment): ?>
                    <div class="mb-2">
                        <div class="small text-muted">Metode</div>
                        <div class="mt-1"><?= Format::paymentMethodBadge($payment['payment_method']) ?></div>
                    </div>
                    <div class="mb-2">
                        <div class="small text-muted">Nominal Dibayar</div>
                        <div class="fw-bold text-dark"><?= Format::rupiah($payment['amount']) ?></div>
                    </div>
                    <?php if ($payment['payment_method'] === 'cash'): ?>
                        <div class="mb-2">
                            <div class="small text-muted">Uang Diterima / Kembalian</div>
                            <div class="small text-dark"><?= Format::rupiah($payment['tendered_amount']) ?> / <span class="text-success fw-bold"><?= Format::rupiah($payment['change_amount']) ?></span></div>
                        </div>
                    <?php else: ?>
                        <div class="mb-2">
                            <div class="small text-muted">Transaction ID Gateway</div>
                            <div class="small font-monospace text-break"><?= htmlspecialchars($payment['transaction_id'] ?? '-') ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="mb-0">
                        <div class="small text-muted">Waktu Lunas</div>
                        <div class="small text-dark"><?= Format::date($payment['paid_at'] ?? $payment['created_at']) ?></div>
                    </div>
                <?php else: ?>
                    <div class="text-muted small">Belum ada catatan pembayaran.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
