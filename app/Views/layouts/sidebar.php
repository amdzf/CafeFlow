<?php
use App\Helpers\Auth;
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$role = Auth::role();

function isActive(string $path, string $currentUri): string {
    $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $cleanUri = $currentUri;
    if ($scriptName !== '/' && $scriptName !== '\\' && !empty($scriptName)) {
        if (str_starts_with($cleanUri, $scriptName)) {
            $cleanUri = substr($cleanUri, strlen($scriptName));
        }
    }
    $cleanUri = '/' . ltrim($cleanUri, '/');
    return ($cleanUri === $path || str_starts_with($cleanUri, $path . '/')) ? 'active' : '';
}
?>

<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-header">
        <a href="<?= url('/') ?>" class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-cup-hot-fill"></i>
            </div>
            <div>
                <div>CafeFlow</div>
                <div style="font-size: 0.68rem; font-weight: 500; color: rgba(255,255,255,0.6); margin-top: -2px;">POS & Management</div>
            </div>
        </a>
    </div>

    <ul class="sidebar-menu">
        <?php if ($role === 'admin'): ?>
            <li class="sidebar-category">Ringkasan</li>
            <li class="sidebar-item">
                <a href="<?= url('/dashboard') ?>" class="sidebar-link <?= isActive('/dashboard', $currentUri) ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard Utama</span>
                </a>
            </li>
        <?php endif; ?>

        <li class="sidebar-category">Operasional Penjualan</li>
        <?php if ($role === 'admin' || $role === 'kasir'): ?>
            <li class="sidebar-item">
                <a href="<?= url('/pos') ?>" class="sidebar-link <?= isActive('/pos', $currentUri) ?>">
                    <i class="bi bi-calculator-fill"></i>
                    <span>Kasir (POS)</span>
                    <span class="badge bg-success-subtle text-success">Aktif</span>
                </a>
            </li>
        <?php endif; ?>

        <li class="sidebar-item">
            <a href="<?= url('/orders') ?>" class="sidebar-link <?= isActive('/orders', $currentUri) ?>">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Daftar Pesanan</span>
            </a>
        </li>

        <?php if ($role === 'admin' || $role === 'dapur'): ?>
            <li class="sidebar-item">
                <a href="<?= url('/kitchen') ?>" class="sidebar-link <?= isActive('/kitchen', $currentUri) ?>">
                    <i class="bi bi-fire"></i>
                    <span>Dapur & Bar (KDS)</span>
                    <span class="badge bg-warning-subtle text-warning">Live</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
            <li class="sidebar-category">Katalog & Inventaris</li>
            <li class="sidebar-item">
                <a href="<?= url('/products') ?>" class="sidebar-link <?= isActive('/products', $currentUri) ?>">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>Manajemen Produk</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('/ingredients') ?>" class="sidebar-link <?= isActive('/ingredients', $currentUri) ?>">
                    <i class="bi bi-egg-fried"></i>
                    <span>Bahan Baku & Resep</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('/expenses') ?>" class="sidebar-link <?= isActive('/expenses', $currentUri) ?>">
                    <i class="bi bi-wallet2"></i>
                    <span>Pengeluaran Cafe</span>
                </a>
            </li>

            <li class="sidebar-category">Laporan Keuangan</li>
            <li class="sidebar-item">
                <a href="<?= url('/reports/sales') ?>" class="sidebar-link <?= isActive('/reports/sales', $currentUri) ?>">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Laporan Penjualan</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('/reports/payments') ?>" class="sidebar-link <?= isActive('/reports/payments', $currentUri) ?>">
                    <i class="bi bi-qr-code-scan"></i>
                    <span>Metode Bayar (QRIS/Cash)</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('/reports/profit-loss') ?>" class="sidebar-link <?= isActive('/reports/profit-loss', $currentUri) ?>">
                    <i class="bi bi-pie-chart-fill"></i>
                    <span>Laporan Laba Rugi (P&L)</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('/reports/inventory') ?>" class="sidebar-link <?= isActive('/reports/inventory', $currentUri) ?>">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Mutasi & Stok</span>
                </a>
            </li>

            <li class="sidebar-category">Pengaturan Sistem</li>
            <li class="sidebar-item">
                <a href="<?= url('/users') ?>" class="sidebar-link <?= isActive('/users', $currentUri) ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Staf & Akses</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('/settings') ?>" class="sidebar-link <?= isActive('/settings', $currentUri) ?>">
                    <i class="bi bi-sliders"></i>
                    <span>Pengaturan & Log</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-footer">
        <form action="<?= url('/logout') ?>" method="POST" id="logoutForm">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-light w-100 btn-sm d-flex align-items-center justify-content-center gap-2" style="border-radius: 8px;">
                <i class="bi bi-box-arrow-right"></i> Keluar Sistem
            </button>
        </form>
    </div>
</aside>
