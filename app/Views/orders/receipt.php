<?php
use App\Helpers\Format;
$latestPayment = $order['latest_payment'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk #<?= htmlspecialchars($order['order_number']) ?> - <?= htmlspecialchars($cafe['name']) ?></title>
    <link rel="stylesheet" href="<?= asset('css/receipt.css') ?>">
</head>
<body class="receipt-body">

<div class="receipt-container">
    <!-- Header -->
    <div class="receipt-header">
        <h1 class="receipt-logo-title"><?= htmlspecialchars($cafe['name']) ?></h1>
        <div class="receipt-meta"><?= htmlspecialchars($cafe['tagline']) ?></div>
        <div class="receipt-meta"><?= htmlspecialchars($cafe['address']) ?></div>
        <div class="receipt-meta">Telp: <?= htmlspecialchars($cafe['phone']) ?></div>
    </div>

    <div class="receipt-divider"></div>

    <!-- Info Row -->
    <div class="receipt-info-row">
        <span>No. Struk</span>
        <span style="font-weight: bold;"><?= htmlspecialchars($order['order_number']) ?></span>
    </div>
    <div class="receipt-info-row">
        <span>Tanggal</span>
        <span><?= Format::date($order['paid_at'] ?? $order['created_at'], 'd/m/Y H:i') ?></span>
    </div>
    <div class="receipt-info-row">
        <span>Kasir</span>
        <span><?= htmlspecialchars($order['cashier_name'] ?? 'Kasir') ?></span>
    </div>
    <div class="receipt-info-row">
        <span>Pelanggan</span>
        <span><?= htmlspecialchars($order['customer_name']) ?></span>
    </div>
    <div class="receipt-info-row">
        <span>Tipe Pesanan</span>
        <span style="font-weight: bold;">
            <?= ($order['order_type'] === 'dine_in') ? 'DINE-IN (' . ($order['table_number'] ? 'Meja ' . $order['table_number'] : '-') . ')' : 'TAKEAWAY' ?>
        </span>
    </div>

    <div class="receipt-divider"></div>

    <!-- Items Table -->
    <table class="receipt-table">
        <thead>
            <tr>
                <th class="col-qty">QTY</th>
                <th>MENU</th>
                <th class="col-price">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td class="col-qty"><?= $item['quantity'] ?>x</td>
                    <td>
                        <?= htmlspecialchars($item['product_name']) ?>
                        <div style="font-size: 10px; color: #555;">@ <?= Format::rupiah($item['unit_price']) ?></div>
                    </td>
                    <td class="col-price"><?= Format::rupiah($item['subtotal']) ?></td>
                </tr>
                <?php if (!empty($item['notes'])): ?>
                    <tr>
                        <td colspan="3" class="receipt-item-notes">Catatan: <?= htmlspecialchars($item['notes']) ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="receipt-divider"></div>

    <!-- Totals Summary -->
    <div class="receipt-summary-row">
        <span>Subtotal</span>
        <span><?= Format::rupiah($order['subtotal']) ?></span>
    </div>

    <?php if ((float)$order['discount_amount'] > 0): ?>
        <div class="receipt-summary-row">
            <span>Diskon <?= ($order['discount_percent'] > 0) ? "({$order['discount_percent']}%)" : '' ?></span>
            <span>- <?= Format::rupiah($order['discount_amount']) ?></span>
        </div>
    <?php endif; ?>

    <?php if ((float)$order['tax_amount'] > 0): ?>
        <div class="receipt-summary-row">
            <span>Pajak Restoran (PPN)</span>
            <span><?= Format::rupiah($order['tax_amount']) ?></span>
        </div>
    <?php endif; ?>

    <?php if ((float)$order['service_charge'] > 0): ?>
        <div class="receipt-summary-row">
            <span>Biaya Layanan</span>
            <span><?= Format::rupiah($order['service_charge']) ?></span>
        </div>
    <?php endif; ?>

    <div class="receipt-summary-row total-row">
        <span>TOTAL TAGIHAN</span>
        <span><?= Format::rupiah($order['total_amount']) ?></span>
    </div>

    <!-- Payment Method Details -->
    <div class="receipt-summary-row" style="margin-top: 6px;">
        <span>Metode Pembayaran</span>
        <span style="font-weight: bold; text-transform: uppercase;">
            <?= ($order['payment_method'] === 'cash') ? 'TUNAI (CASH)' : 'QRIS DINAMIS' ?>
        </span>
    </div>

    <?php if ($order['payment_method'] === 'cash' && $latestPayment): ?>
        <div class="receipt-summary-row">
            <span>Uang Diterima</span>
            <span><?= Format::rupiah($latestPayment['tendered_amount']) ?></span>
        </div>
        <div class="receipt-summary-row" style="font-weight: bold;">
            <span>Kembalian</span>
            <span><?= Format::rupiah($latestPayment['change_amount']) ?></span>
        </div>
    <?php elseif ($order['payment_method'] === 'qris' && $latestPayment): ?>
        <div class="receipt-summary-row" style="font-size: 10px; color: #555;">
            <span>ID Gateway</span>
            <span><?= htmlspecialchars($latestPayment['transaction_id'] ?? '-') ?></span>
        </div>
        <div class="receipt-summary-row">
            <span>Status</span>
            <span style="color: #059669; font-weight: bold;">VERIFIED LUNAS</span>
        </div>
    <?php endif; ?>

    <div class="receipt-divider"></div>

    <!-- Footer Message -->
    <div class="receipt-footer">
        <div><?= nl2br(htmlspecialchars($cafe['footer'])) ?></div>
        <div style="margin-top: 6px; font-size: 10px; color: #888;">Dicetak secara otomatis oleh CafeFlow POS</div>
    </div>
</div>

<div class="no-print" style="position: fixed; bottom: 15px; left: 50%; transform: translateX(-50%);">
    <button onclick="window.print()" style="padding: 8px 18px; background: #4A2E18; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
        🖨️ Cetak Struk
    </button>
</div>

</body>
</html>
