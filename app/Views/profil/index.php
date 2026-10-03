<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Informasi Profil Pengguna</span>
        <span class="small text-muted">Perbaharui informasi profil dan alamat email akun Anda jika diperlukan.</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="<?= base_url('profil/update') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= esc($user['nama_lengkap'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= esc($user['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kata Sandi Baru</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti kata sandi">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Konfirmasi Kata Sandi</label>
                        <input type="password" name="password_confirm" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti kata sandi">
                    </div>
                    <button type="submit" class="btn btn-primary px-4">Submit</button>
                </form>
            </div>
            <div class="col-lg-4 text-center">
                <div class="mb-3">
                    <?php if (!empty($user['foto'])): ?>
                        <img src="<?= base_url($user['foto']) ?>" alt="Foto Profil" class="rounded-circle border" style="width:140px;height:140px;object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center" style="width:140px;height:140px;">
                            <i class="bi bi-person fs-1 text-secondary"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <form action="<?= base_url('profil/upload-foto') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <label class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-paperclip me-1"></i> Unggah Foto Profil
                        <input type="file" name="foto" accept="image/*" class="d-none" onchange="this.form.submit()">
                    </label>
                </form>
                <?php if (in_array(session()->get('role'), ['perencana', 'pengawas'], true)): ?>
                    <div class="border-top mt-4 pt-4 text-start">
                        <h2 class="h6 fw-semibold">Tanda Tangan</h2>
                        <p class="small text-muted">Unggah gambar JPG, PNG, atau WebP maksimal 2 MB untuk digunakan pada dokumen cetak.</p>
                        <?php if (!empty($user['ttd'])): ?>
                            <div class="mb-3 text-center">
                                <img src="<?= base_url($user['ttd']) ?>" alt="Tanda tangan pengguna" class="border rounded p-2" style="width:100%;max-width:240px;height:90px;object-fit:contain">
                            </div>
                        <?php endif; ?>
                        <form action="<?= base_url('profil/upload-ttd') ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <label class="form-label" for="ttd">File tanda tangan</label>
                            <input class="form-control mb-2" type="file" id="ttd" name="ttd" accept="image/jpeg,image/png,image/webp" required>
                            <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-upload me-1"></i>Unggah Tanda Tangan</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
