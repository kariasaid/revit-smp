<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <div class="small text-muted mb-1">Administrasi</div>
    <h1 class="h4 fw-bold mb-1">Manajemen User</h1>
    <div class="text-muted">Admin dapat menambahkan, mengubah, dan menghapus akun pengguna aplikasi.</div>
</div>

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header py-3">
                <strong><i class="bi bi-person-plus text-primary me-2"></i><?= $editUser ? 'Edit User' : 'Tambah User' ?></strong>
            </div>
            <div class="card-body">
                <form action="<?= $editUser ? base_url('admin/users/' . (int) $editUser['id'] . '/update') : base_url('admin/users/simpan') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="nama_lengkap">Nama Lengkap</label>
                        <input class="form-control" id="nama_lengkap" name="nama_lengkap" maxlength="150" value="<?= esc(old('nama_lengkap', $editUser['nama_lengkap'] ?? '')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="username">Username</label>
                        <input class="form-control" id="username" name="username" maxlength="100" value="<?= esc(old('username', $editUser['username'] ?? '')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email" maxlength="150" value="<?= esc(old('email', $editUser['email'] ?? '')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="role">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">-- Pilih Role --</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= esc($role) ?>" <?= old('role', $editUser['role'] ?? '') === $role ? 'selected' : '' ?>><?= esc(ucwords(str_replace('_', ' ', $role))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password <?= $editUser ? '<span class="text-muted fw-normal">(kosongkan jika tidak diubah)</span>' : '' ?></label>
                        <input class="form-control" id="password" name="password" type="password" minlength="6" maxlength="72" <?= $editUser ? '' : 'required' ?> autocomplete="new-password">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="nik">NIK</label>
                            <input class="form-control" id="nik" name="nik" maxlength="20" value="<?= esc(old('nik', $editUser['nik'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="nip">NIP</label>
                            <input class="form-control" id="nip" name="nip" maxlength="30" value="<?= esc(old('nip', $editUser['nip'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="no_hp">No. HP</label>
                            <input class="form-control" id="no_hp" name="no_hp" maxlength="20" value="<?= esc(old('no_hp', $editUser['no_hp'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="npwp">NPWP</label>
                            <input class="form-control" id="npwp" name="npwp" maxlength="30" value="<?= esc(old('npwp', $editUser['npwp'] ?? '')) ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $editUser ? 'Simpan Perubahan' : 'Tambah User' ?></button>
                        <?php if ($editUser): ?>
                            <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-people text-primary me-2"></i>User Terdaftar</strong>
                <span class="badge text-bg-light border"><?= count($users) ?> user</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Pengguna</th>
                                <th>Kontak</th>
                                <th>Role</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada user.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $item): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold"><?= esc($item['nama_lengkap']) ?></div>
                                        <div class="small text-muted">@<?= esc($item['username']) ?></div>
                                        <?php if (!empty($item['nip'])): ?><div class="small text-muted">NIP <?= esc($item['nip']) ?></div><?php endif; ?>
                                    </td>
                                    <td>
                                        <div><?= esc($item['email']) ?></div>
                                        <?php if (!empty($item['no_hp'])): ?><div class="small text-muted"><?= esc($item['no_hp']) ?></div><?php endif; ?>
                                    </td>
                                    <td><span class="badge text-bg-light border"><?= esc(ucwords(str_replace('_', ' ', $item['role']))) ?></span></td>
                                    <td class="text-end pe-3 text-nowrap">
                                        <a href="<?= base_url('admin/users?edit=' . (int) $item['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                        <?php if ((int) $item['id'] !== (int) session()->get('id')): ?>
                                            <form action="<?= base_url('admin/users/' . (int) $item['id'] . '/hapus') ?>" method="post" class="d-inline" onsubmit="return confirm(<?= json_encode('Hapus user ' . $item['nama_lengkap'] . '?', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>);">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Akun yang sedang login tidak dapat dihapus"><i class="bi bi-lock"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="alert alert-light border mt-3 small mb-0">
            <i class="bi bi-info-circle me-1"></i>
            User <strong>Perencana</strong> dan <strong>Pengawas</strong> yang dibuat di halaman ini otomatis tersedia pada pilihan penugasan sekolah.
        </div>
    </div>
</div>

<?= $this->endSection() ?>
