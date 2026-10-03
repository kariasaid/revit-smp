<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <div class="small text-muted mb-1">Data Sekolah / Tim P2SP</div>
    <h1 class="h4 fw-bold mb-1">Tim P2SP</h1>
    <div class="text-muted">Kelola identitas dan tanda tangan enam anggota tim sekolah.</div>
</div>

<?php if (count($schools) > 1 || session()->get('role') === 'admin'): ?>
    <form method="get" action="<?= base_url('tim-p2sp') ?>" class="mb-3">
        <label for="sekolah_id" class="form-label">Pilih sekolah</label>
        <select class="form-select" id="sekolah_id" name="sekolah_id" onchange="this.form.submit()">
            <?php foreach ($schools as $listedSchool): ?>
                <option value="<?= (int) $listedSchool['id'] ?>" <?= (int) $listedSchool['id'] === (int) $school['id'] ? 'selected' : '' ?>>
                    <?= esc($listedSchool['nama_sekolah']) ?> · NPSN <?= esc($listedSchool['npsn']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <div class="small text-muted">Sekolah</div>
        <div class="fw-semibold"><?= esc($school['nama_sekolah']) ?></div>
        <div class="small text-muted">NPSN <?= esc($school['npsn']) ?> · <?= esc($school['kab_kota'] ?? '-') ?></div>
    </div>
</div>

<form action="<?= base_url('tim-p2sp/simpan') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="sekolah_id" value="<?= (int) $school['id'] ?>">
    <?php foreach ($positions as $position => $label): ?>
        <?php $member = $members[$position]; ?>
        <section class="card mb-3">
            <div class="card-header py-3"><strong><?= esc($label) ?></strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="nama_<?= esc($position) ?>">Nama</label>
                        <input class="form-control" id="nama_<?= esc($position) ?>" name="anggota[<?= esc($position) ?>][nama]" maxlength="150" value="<?= esc($member['nama'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="nip_nik_<?= esc($position) ?>">NIP/NIK</label>
                        <input class="form-control" id="nip_nik_<?= esc($position) ?>" name="anggota[<?= esc($position) ?>][nip_nik]" maxlength="30" value="<?= esc($member['nip_nik'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="jabatan_<?= esc($position) ?>">Jabatan</label>
                        <input class="form-control" id="jabatan_<?= esc($position) ?>" name="anggota[<?= esc($position) ?>][jabatan]" maxlength="150" value="<?= esc($member['jabatan'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="ttd_<?= esc($position) ?>">Tanda tangan (JPG, PNG, WebP; maks. 2 MB)</label>
                        <input class="form-control" type="file" id="ttd_<?= esc($position) ?>" name="ttd_<?= esc($position) ?>" accept="image/jpeg,image/png,image/webp" <?= empty($member['ttd']) ? 'required' : '' ?>>
                    </div>
                    <?php if (!empty($member['ttd'])): ?>
                        <div class="col-md-6">
                            <div class="form-label">Tanda tangan tersimpan</div>
                            <img src="<?= base_url($member['ttd']) ?>" alt="Tanda tangan <?= esc($label) ?>" class="border rounded p-2" style="max-width: 240px; max-height: 100px; object-fit: contain">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
    <div class="d-flex justify-content-end mb-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Tim P2SP</button>
    </div>
</form>

<?= $this->endSection() ?>