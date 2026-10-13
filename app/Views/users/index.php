<?php
use App\Helpers\Format;
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">Manajemen Staf & Hak Akses</h2>
        <p class="text-muted mb-0 small">Kelola akun kasir, barista/dapur, dan administrator pemilik kafe.</p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-cafe btn-sm d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus-fill"></i> Tambah Pengguna Baru
        </button>
    </div>
</div>

<div class="card-cafe">
    <div class="card-cafe-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nama Pengguna</th>
                        <th>Email Login</th>
                        <th>Peran / Hak Akses</th>
                        <th>No. Telepon</th>
                        <th>Status</th>
                        <th>Terdaftar</th>
                        <th class="pe-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="user-avatar-circle" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($u['name']) ?></div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger">Admin / Owner</span>
                                <?php elseif ($u['role'] === 'kasir'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success">Kasir POS</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning">Dapur / Barista</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= Format::date($u['created_at'], 'd M Y') ?></td>
                            <td class="pe-3 text-end">
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 me-1" onclick='openEditUserModal(<?= json_encode($u) ?>)' title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="<?= url('/users/' . $u['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna ini?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2.5" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/users') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Rian Barista" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Email Login <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="rian@cafeflow.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Peran Akses (Role) <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="kasir" selected>Kasir (Akses POS Kasir & Transaksi)</option>
                            <option value="dapur">Dapur / Barista (Akses KDS & Pengerjaan Pesanan)</option>
                            <option value="admin">Admin / Owner (Akses Seluruh Menu & Laporan)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-cafe">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pengguna -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Akun Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" id="editUserName" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Email (Readonly)</label>
                        <input type="email" id="editUserEmail" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Password Baru (Kosongkan jika tidak diganti)</label>
                        <input type="password" name="password" class="form-control" placeholder="Biarkan kosong untuk mempertahankan password lama">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Peran Akses (Role)</label>
                        <select id="editUserRole" name="role" class="form-select">
                            <option value="kasir">Kasir</option>
                            <option value="dapur">Dapur / Barista</option>
                            <option value="admin">Admin / Owner</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">No. Telepon</label>
                        <input type="text" id="editUserPhone" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Status Akun</label>
                        <select id="editUserActive" name="is_active" class="form-select">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-cafe">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditUserModal(u) {
        document.getElementById('editUserForm').action = '<?= url('/users/') ?>' + u.id + '/update';
        document.getElementById('editUserName').value = u.name;
        document.getElementById('editUserEmail').value = u.email;
        document.getElementById('editUserRole').value = u.role;
        document.getElementById('editUserPhone').value = u.phone || '';
        document.getElementById('editUserActive').value = u.is_active;
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }
</script>
