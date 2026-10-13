<?php require __DIR__ . '/header.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar -->
    <?php require __DIR__ . '/sidebar.php'; ?>

    <!-- Main Container -->
    <div class="app-main">
        <!-- Navbar -->
        <?php require __DIR__ . '/navbar.php'; ?>

        <!-- Main Content -->
        <main class="app-content">
            <?php if (has_flash('success') && !empty(get_flash('success'))): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div><?= htmlspecialchars(get_flash('success')) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (has_flash('error') && !empty(get_flash('error'))): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div><?= htmlspecialchars(get_flash('error')) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
