<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <div class="small text-muted mb-1">Administrasi</div>
    <h1 class="h4 fw-bold mb-1">Sekolah dan Penugasan</h1>
    <div class="text-muted">Tambahkan sekolah beserta akun perencana dan pengawas yang bertugas.</div>
</div>

<div class="card">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-list-ul text-primary me-2"></i>Sekolah terdaftar</strong>
        <span class="badge text-bg-light border"><?= count($schools) ?> sekolah</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Sekolah</th>
                        <th>Jenis bantuan</th>
                        <th>Perencana</th>
                        <th>Pengawas</th>
                        <th>Durasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($schools)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada sekolah terdaftar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($schools as $school): ?>
                            <tr>
                                <td class="ps-3"><a class="fw-semibold text-decoration-none" href="<?= base_url('admin/sekolah/' . $school['id']) ?>"><?= esc($school['nama_sekolah']) ?></a><div class="small text-muted">NPSN <?= esc($school['npsn']) ?> · <?= esc($school['kab_kota']) ?>, <?= esc($school['provinsi']) ?></div><a class="small" href="<?= base_url('admin/sekolah/' . $school['id']) ?>">Lihat detail</a></td>
                                <td>
                                    <?php foreach (($assistanceBySchool[$school['id']] ?? []) as $assistance): ?>
                                        <span class="badge text-bg-light border me-1 mb-1"><?= esc($assistance) ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td><?= esc($school['perencana'] ?? '-') ?><div class="small text-muted"><?= esc($school['hp_perencana'] ?? '') ?></div></td>
                                <td><?= esc($school['pengawas'] ?? '-') ?><div class="small text-muted"><?= esc($school['hp_pengawas'] ?? '') ?></div></td>
                                <td><?= (int) $school['total_minggu'] ?> minggu</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mb-4">
    <div class="small text-muted mb-1"></div>
    <h1 class="h4 fw-bold mb-1">TAMBAH DATA</h1>
    <div class="text-muted">Tambahkan sekolah beserta akun perencana dan pengawas yang bertugas.</div>
</div>

<div class="card mb-4">
    <div class="card-header py-3">
        <strong><i class="bi bi-building-add text-primary me-2"></i>Tambah sekolah</strong>
    </div>
    <div class="card-body">
        <form action="<?= base_url('admin/sekolah/simpan') ?>" method="post">
            <?= csrf_field() ?>
            <h2 class="h6 fw-bold mb-3">Informasi sekolah</h2>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label" for="nama_sekolah">Nama sekolah</label>
                    <input class="form-control" id="nama_sekolah" name="nama_sekolah" maxlength="200" value="<?= esc(old('nama_sekolah')) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="npsn">NPSN</label>
                    <input class="form-control" id="npsn" name="npsn" inputmode="numeric" maxlength="20" value="<?= esc(old('npsn')) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="total_minggu">Durasi (minggu)</label>
                    <input class="form-control" id="total_minggu" name="total_minggu" type="number" min="1" max="52" value="<?= esc(old('total_minggu', '16')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="provinsi">Provinsi</label>
                    <input class="form-control" id="provinsi" name="provinsi" maxlength="100" value="<?= esc(old('provinsi')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="kab_kota">Kabupaten/Kota</label>
                    <input class="form-control" id="kab_kota" name="kab_kota" maxlength="100" value="<?= esc(old('kab_kota')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="dana_diterima">Dana diterima (Rp)</label>
                    <input class="form-control" id="dana_diterima" name="dana_diterima" type="number" min="0" step="0.01" value="<?= esc(old('dana_diterima', '0')) ?>" required>
                </div>
            </div>

            <hr class="my-4">
            <div class="row g-4">
                <section class="col-lg-6" aria-labelledby="plannerHeading">
                    <h2 class="h6 fw-bold mb-3" id="plannerHeading"><i class="bi bi-calendar-week text-primary me-1"></i>Perencana</h2>
                    <div class="mb-3">
                        <label class="form-label" for="nama_perencana">Nama lengkap</label>
                        <input class="form-control" id="nama_perencana" name="nama_perencana" maxlength="150" value="<?= esc(old('nama_perencana')) ?>" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="nik_perencana">NIK</label>
                            <input class="form-control" id="nik_perencana" name="nik_perencana" inputmode="numeric" maxlength="16" minlength="16" value="<?= esc(old('nik_perencana')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="hp_perencana">Nomor HP</label>
                            <input class="form-control" id="hp_perencana" name="hp_perencana" maxlength="20" value="<?= esc(old('hp_perencana')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="email_perencana">Email <span class="text-muted fw-normal">(opsional)</span></label>
                            <input class="form-control" id="email_perencana" name="email_perencana" type="email" maxlength="150" value="<?= esc(old('email_perencana')) ?>">
                        </div>
                    </div>
                </section>
                <section class="col-lg-6" aria-labelledby="supervisorHeading">
                    <h2 class="h6 fw-bold mb-3" id="supervisorHeading"><i class="bi bi-person-check text-primary me-1"></i>Pengawas</h2>
                    <div class="mb-3">
                        <label class="form-label" for="nama_pengawas">Nama lengkap</label>
                        <input class="form-control" id="nama_pengawas" name="nama_pengawas" maxlength="150" value="<?= esc(old('nama_pengawas')) ?>" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="nik_pengawas">NIK</label>
                            <input class="form-control" id="nik_pengawas" name="nik_pengawas" inputmode="numeric" maxlength="16" minlength="16" value="<?= esc(old('nik_pengawas')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="hp_pengawas">Nomor HP</label>
                            <input class="form-control" id="hp_pengawas" name="hp_pengawas" maxlength="20" value="<?= esc(old('hp_pengawas')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="email_pengawas">Email <span class="text-muted fw-normal">(opsional)</span></label>
                            <input class="form-control" id="email_pengawas" name="email_pengawas" type="email" maxlength="150" value="<?= esc(old('email_pengawas')) ?>">
                        </div>
                    </div>
                </section>
                <section class="col-12" aria-labelledby="principalHeading">
                    <h2 class="h6 fw-bold mb-3" id="principalHeading">Kepala sekolah <span class="text-muted fw-normal">(opsional)</span></h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="kepala_sekolah">Nama lengkap</label>
                            <input class="form-control" id="kepala_sekolah" name="kepala_sekolah" maxlength="150" value="<?= esc(old('kepala_sekolah')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="hp_kepala_sekolah">Nomor HP</label>
                            <input class="form-control" id="hp_kepala_sekolah" name="hp_kepala_sekolah" maxlength="20" value="<?= esc(old('hp_kepala_sekolah')) ?>">
                        </div>
                    </div>
                </section>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-top mt-4 pt-3">
                <div class="small text-muted">Username dan password awal akun perencana/pengawas menggunakan NIK masing-masing.</div>
                <button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg me-1"></i> Buat sekolah dan akun</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
