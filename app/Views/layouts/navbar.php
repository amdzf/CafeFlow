<?php
use App\Helpers\Auth;
$currentUser = Auth::user();
$userRole = Auth::role();
$roleBadge = match($userRole) {
    'admin' => '<span class="badge bg-danger-subtle text-danger border border-danger">Admin / Owner</span>',
    'kasir' => '<span class="badge bg-success-subtle text-success border border-success">Kasir POS</span>',
    'dapur' => '<span class="badge bg-warning-subtle text-warning border border-warning">Barista & Dapur</span>',
    default => '<span class="badge bg-secondary">Staf</span>'
};
?>

<header class="app-navbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light d-lg-none" type="button" id="sidebarToggleBtn">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div class="d-none d-md-flex align-items-center gap-2 text-muted" style="font-size: 0.88rem;">
            <i class="bi bi-calendar3 text-secondary"></i>
            <span id="liveDateTime"><?= date('l, d F Y - H:i') ?></span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Sandbox Mode Alert Badge -->
        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2.5 py-1.5 d-none d-sm-inline-flex align-items-center gap-1">
            <i class="bi bi-shield-check"></i> Midtrans Sandbox Active
        </span>

        <?php if ($userRole === 'admin' || $userRole === 'kasir'): ?>
            <a href="<?= url('/pos') ?>" class="btn btn-cafe btn-sm d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-cart-plus"></i> <span class="d-none d-sm-inline">Buka POS</span>
            </a>
        <?php endif; ?>

        <!-- User Profile Dropdown -->
        <div class="dropdown">
            <button class="navbar-user-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar-circle">
                    <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="text-start d-none d-md-block">
                    <div class="fw-semibold text-dark" style="font-size: 0.88rem; line-height: 1.2;">
                        <?= htmlspecialchars($currentUser['name'] ?? 'User') ?>
                    </div>
                    <div style="font-size: 0.72rem;">
                        <?= $roleBadge ?>
                    </div>
                </div>
                <i class="bi bi-chevron-down text-muted ms-1" style="font-size: 0.75rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 12px; min-width: 200px;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold text-dark"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
                </li>
                <?php if ($userRole === 'admin'): ?>
                    <li><a class="dropdown-item py-2" href="<?= url('/dashboard') ?>"><i class="bi bi-speedometer2 me-2 text-muted"></i>Dashboard</a></li>
                    <li><a class="dropdown-item py-2" href="<?= url('/settings') ?>"><i class="bi bi-gear me-2 text-muted"></i>Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                <?php endif; ?>
                <li>
                    <form action="<?= url('/logout') ?>" method="POST" class="m-0">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item py-2 text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Keluar
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
