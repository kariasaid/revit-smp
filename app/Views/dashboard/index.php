<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Banner Welcome -->
<div class="card mb-4 overflow-hidden" style="background: linear-gradient(135deg, #1d5296 0%, #2563eb 100%); border: none;">
    <div class="card-body text-white p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 opacity-75 small">
                <i class="bi bi-person-badge"></i> Dashboard Tim Teknis
            </div>
            <h4 class="mb-1 fw-bold">Selamat Datang, <?= esc($user['nama_lengkap'] ?? session()->get('nama_lengkap')) ?></h4>
            <p class="mb-0 small opacity-75">Kelola profil biodata teknis dan pantau progres revitalisasi sekolah sasaran secara terpusat.</p>
        </div>
        <a href="<?= base_url('profil') ?>" class="btn btn-warning text-dark fw-semibold">
            <i class="bi bi-pencil-square me-1"></i> Perbaharui Biodata
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Profil Tim Teknis -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-person-vcard text-primary"></i>
                <span>Profil Tim Teknis</span>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted small" style="width:40%">NAMA LENGKAP</td>
                        <td class="fw-semibold"><?= esc($user['nama_lengkap'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">NIK</td>
                        <td><?= esc($user['nik'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">NIP</td>
                        <td><?= esc($user['nip'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">EMAIL</td>
                        <td><i class="bi bi-envelope me-1 text-muted"></i><?= esc($user['email'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">TELEPON / WHATSAPP</td>
                        <td><i class="bi bi-telephone me-1 text-muted"></i><?= esc($user['no_hp'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">NPWP</td>
                        <td><i class="bi bi-card-text me-1 text-muted"></i><?= esc($user['npwp'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Daftar Sekolah Kelolaan -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-building text-primary"></i>
                    <span>Daftar Sekolah Kelolaan</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Total <?= count($sekolahList) ?> sekolah berada dalam pendampingan Anda</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>SATUAN PENDIDIKAN</th>
                                <th>MENU REVITALISASI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sekolahList)): ?>
                                <tr><td colspan="4" class="text-center text-muted">Belum ada sekolah kelolaan</td></tr>
                            <?php else: ?>
                                <?php foreach ($sekolahList as $i => $s): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <a href="<?= base_url('pelaksanaan/progres/' . $s['id']) ?>" class="text-primary fw-semibold text-decoration-none">
                                            <?= esc($s['nama_sekolah']) ?>
                                        </a>
                                        <div class="small text-muted">NPSN: <?= esc($s['npsn']) ?></div>
                                    </td>
                                    <td><span class="badge bg-primary-subtle text-primary">3 Menu</span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
