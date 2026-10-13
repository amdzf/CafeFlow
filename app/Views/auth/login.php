<div class="card border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
    <div class="p-4 p-sm-5 text-center" style="background: linear-gradient(135deg, #2D1B12 0%, #4A2E18 100%); color: #FBF8F3;">
        <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; border-radius: 18px; background: rgba(198, 124, 78, 0.25); border: 1px solid rgba(198, 124, 78, 0.5); font-size: 2rem; color: #C67C4E;">
            <i class="bi bi-cup-hot-fill"></i>
        </div>
        <h3 class="fw-bold mb-1" style="letter-spacing: -0.02em;">CafeFlow POS</h3>
        <p class="mb-2 text-white-50 small">Modern Cafe Point of Sale & Management System</p>
        
        <!-- Watermark Badge Header with Direct Instagram Link -->
        <div class="mt-2">
            <a href="https://www.instagram.com/achmdzf/" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                <span class="badge px-3 py-1.5 rounded-pill shadow-xs" style="background: rgba(198, 124, 78, 0.25); border: 1px solid rgba(198, 124, 78, 0.4); color: #F5D0A9; font-size: 0.76rem; letter-spacing: 0.2px;">
                    <i class="bi bi-instagram text-warning me-1"></i> Portofolio: <strong>Achmad Zulfikar</strong> (@achmdzf)
                </span>
            </a>
        </div>
    </div>

    <div class="card-body p-4 p-sm-5 bg-white">
        <?php if (has_flash('error') && !empty(get_flash('error'))): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: 10px;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div class="small"><?= htmlspecialchars(get_flash('error')) ?></div>
            </div>
        <?php endif; ?>

        <?php if (has_flash('success') && !empty(get_flash('success'))): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: 10px;">
                <i class="bi bi-check-circle-fill"></i>
                <div class="small"><?= htmlspecialchars(get_flash('success')) ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= url('/login') ?>" method="POST" id="loginForm">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold text-secondary small">Email Pengguna</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="<?= old('email', 'admin@cafeflow.com') ?>" placeholder="name@cafeflow.com" required style="border-radius: 0 10px 10px 0;">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold text-secondary small">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="password" name="password" value="admin123" placeholder="••••••••" required>
                    <button class="btn btn-light border border-start-0 text-muted" type="button" id="togglePasswordBtn" style="border-radius: 0 10px 10px 0;">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-cafe w-100 py-2.5 shadow-sm mb-4" style="font-size: 1rem;">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
            </button>
        </form>

        <!-- Demo Accounts Quick Fill Buttons -->
        <div class="border-top pt-3">
            <div class="text-center text-muted small fw-semibold mb-2">Demo 1-Click Quick Fill:</div>
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="fillDemo('admin@cafeflow.com', 'admin123')">
                    <i class="bi bi-shield-lock me-1"></i> Admin
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="fillDemo('kasir@cafeflow.com', 'kasir123')">
                    <i class="bi bi-calculator me-1"></i> Kasir
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning text-dark" onclick="fillDemo('dapur@cafeflow.com', 'dapur123')">
                    <i class="bi bi-fire me-1"></i> Dapur
                </button>
            </div>
        </div>

        <!-- Watermark Portfolio Attribution & Contact -->
        <div class="mt-4 pt-3 border-top text-center">
            <div class="d-inline-flex flex-wrap align-items-center justify-content-center gap-2 px-3 py-1.5 rounded-pill bg-light border text-muted" style="font-size: 0.78rem;">
                <span class="d-inline-flex align-items-center gap-1">
                    <i class="bi bi-briefcase-fill text-warning"></i>
                    Portofolio: <strong>Achmad Zulfikar</strong>
                </span>
                <span class="text-muted">•</span>
                <a href="https://www.instagram.com/achmdzf/" target="_blank" rel="noopener noreferrer" class="badge text-white d-inline-flex align-items-center gap-1 text-decoration-none shadow-xs px-2.5 py-1 rounded-pill" style="background: linear-gradient(45deg, #F58529, #DD2A7B, #8134AF); font-size: 0.74rem;">
                    <i class="bi bi-instagram"></i> @achmdzf
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Watermark Bottom Label & Instagram Contact Link -->
<div class="text-center mt-3">
    <p class="text-muted small mb-1.5" style="font-size: 0.82rem;">
        <i class="bi bi-patch-check-fill text-primary me-1"></i>
        Projek ini merupakan portofolio dari <strong>Achmad Zulfikar</strong>
    </p>
    <a href="https://www.instagram.com/achmdzf/" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary bg-white shadow-xs py-1 px-3 rounded-pill d-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
        <i class="bi bi-instagram" style="color: #DD2A7B;"></i>
        <span class="text-secondary fw-semibold">Kontak / Hubungi:</span>
        <span class="fw-bold" style="color: #DD2A7B;">@achmdzf</span>
        <i class="bi bi-arrow-up-right small text-muted"></i>
    </a>
</div>

<!-- Floating Corner Watermark & Direct Contact Link -->
<a href="https://www.instagram.com/achmdzf/" target="_blank" rel="noopener noreferrer" class="position-fixed bottom-0 end-0 m-3 d-none d-sm-flex align-items-center gap-2 px-3 py-2 rounded-pill shadow text-decoration-none" style="background: rgba(45, 27, 18, 0.92); backdrop-filter: blur(8px); border: 1px solid rgba(198, 124, 78, 0.4); color: #FBF8F3; font-size: 0.78rem; z-index: 1050; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
    <span class="badge px-1.5 py-0.5 fw-bold text-white rounded-pill" style="background: linear-gradient(45deg, #F58529, #DD2A7B, #8134AF); font-size: 0.65rem;">
        <i class="bi bi-instagram"></i> INSTAGRAM
    </span>
    <span>Achmad Zulfikar</span>
    <span class="text-warning font-monospace fw-bold">@achmdzf</span>
    <i class="bi bi-box-arrow-up-right small text-white-50 ms-0.5"></i>
</a>

<script>
    function fillDemo(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }

    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passInput = document.getElementById('password');
    if (toggleBtn && passInput) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = passInput.type === 'password';
            passInput.type = isPassword ? 'text' : 'password';
            toggleBtn.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
        });
    }
</script>
