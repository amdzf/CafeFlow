    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>

    <!-- Chart.js (v4) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <!-- Global App Helpers & Clock -->
    <script>
        // Update live clock in navbar
        function updateClock() {
            const el = document.getElementById('liveDateTime');
            if (!el) return;
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            el.textContent = now.toLocaleDateString('id-ID', options);
        }
        setInterval(updateClock, 1000);

        // Sidebar mobile toggle
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebar = document.getElementById('appSidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }

        // Global flash message handler with SweetAlert2
        <?php if (has_flash('success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= addslashes(get_flash('success')) ?>',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        <?php endif; ?>

        <?php if (has_flash('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Perhatian',
                text: '<?= addslashes(get_flash('error')) ?>',
                confirmButtonColor: '#4A2E18'
            });
        <?php endif; ?>

        // Helper currency formatter in JS
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
        }
    </script>
</body>
</html>
