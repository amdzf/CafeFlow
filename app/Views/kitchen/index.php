<?php
use App\Helpers\Format;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h2 class="fw-bold mb-0 text-dark">Kitchen & Bar Display System (KDS)</h2>
            <span class="badge bg-warning text-dark px-2.5 py-1.5"><i class="bi bi-broadcast me-1"></i> Live Auto-Sync</span>
        </div>
        <p class="text-muted mb-0 small">Antarmuka khusus Barista & Tim Dapur untuk memproses dan menyajikan pesanan pelanggan.</p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm bg-white shadow-sm" onclick="fetchKitchenOrders()">
            <i class="bi bi-arrow-clockwise"></i> Segarkan
        </button>
        <span class="badge bg-light text-muted border px-2.5 py-2 small" id="kdsLastSync">Sinkronisasi: Baru saja</span>
    </div>
</div>

<!-- KDS Order Cards Grid -->
<div class="kds-board" id="kdsBoardContainer">
    <!-- Populated dynamically via kitchen.js -->
    <?php if (empty($orders)): ?>
        <div class="col-12 text-center py-5 text-muted" id="kdsEmptyState">
            <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
            <h5 class="fw-bold text-dark">Semua Pesanan Selesai Disajikan!</h5>
            <div class="small">Pesanan baru yang telah dibayar akan muncul secara otomatis di sini.</div>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="kds-card status-<?= htmlspecialchars($order['order_status']) ?>" id="kds-order-<?= $order['id'] ?>">
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light bg-opacity-50">
                    <div>
                        <div class="fw-bold text-dark fs-5">
                            <?= ($order['order_type'] === 'dine_in') ? ($order['table_number'] ? 'Meja ' . $order['table_number'] : 'Dine-In') : 'TAKEAWAY' ?>
                        </div>
                        <div class="small text-muted">#<?= htmlspecialchars($order['order_number']) ?> &bull; <?= htmlspecialchars($order['customer_name']) ?></div>
                    </div>
                    <div>
                        <?= Format::orderStatusBadge($order['order_status']) ?>
                        <div class="small text-muted text-end mt-1"><?= Format::date($order['created_at'], 'H:i') ?></div>
                    </div>
                </div>

                <div class="p-3">
                    <ul class="list-unstyled mb-3">
                        <?php foreach ($order['items'] as $item): ?>
                            <li class="py-1.5 border-bottom border-light d-flex align-items-start justify-content-between">
                                <div>
                                    <span class="fw-bold text-dark me-2"><?= $item['quantity'] ?>x</span>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($item['product_name']) ?></span>
                                    <?php if (!empty($item['notes'])): ?>
                                        <div class="small text-danger fw-semibold fst-italic ps-4">
                                            &bull; <?= htmlspecialchars($item['notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Action Status Transition Buttons -->
                    <div class="d-flex gap-2">
                        <?php if ($order['order_status'] === 'pending'): ?>
                            <button type="button" class="btn btn-warning btn-sm w-100 fw-bold" onclick="updateKitchenStatus(<?= $order['id'] ?>, 'processing')">
                                <i class="bi bi-play-circle me-1"></i> Mulai Buat
                            </button>
                        <?php elseif ($order['order_status'] === 'processing'): ?>
                            <button type="button" class="btn btn-primary btn-sm w-100 fw-bold" onclick="updateKitchenStatus(<?= $order['id'] ?>, 'ready')">
                                <i class="bi bi-bell me-1"></i> Siap Disajikan
                            </button>
                        <?php elseif ($order['order_status'] === 'ready'): ?>
                            <button type="button" class="btn btn-success btn-sm w-100 fw-bold" onclick="updateKitchenStatus(<?= $order['id'] ?>, 'completed')">
                                <i class="bi bi-check-circle me-1"></i> Selesaikan
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    window.KDS_CONFIG = {
        ordersApiUrl: '<?= url('/api/kitchen/orders') ?>',
        updateStatusUrl: '<?= url('/api/orders/') ?>',
        csrfToken: '<?= csrf_token() ?>'
    };
</script>
<script src="<?= asset('js/kitchen.js') ?>"></script>
