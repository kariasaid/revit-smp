<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = !empty($adendum); ?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= base_url('adendum') ?>" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h5 class="mb-0 fw-bold"><?= $isEdit ? 'Edit Adendum' : 'Tambah Adendum' ?></h5>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= base_url('adendum/simpan') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int)$adendum['id'] ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Sekolah <span class="text-danger">*</span></label>
                    <select name="sekolah_id" class="form-select" required>
                        <option value="">— Pilih Sekolah —</option>
                        <?php foreach ($sekolahList as $s): ?>
                            <option value="<?= $s['id'] ?>"
                                <?= old('sekolah_id', $adendum['sekolah_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                <?= esc($s['nama_sekolah']) ?> (<?= esc($s['npsn']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Adendum <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_adendum" class="form-control"
                           value="<?= esc(old('nomor_adendum', $adendum['nomor_adendum'] ?? '')) ?>"
                           placeholder="Contoh: ADN/001/2026" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" class="form-control"
                           value="<?= esc(old('tanggal', $adendum['tanggal'] ?? date('Y-m-d'))) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Nilai Perubahan (Rp)</label>
                    <input type="number" name="nilai_perubahan" class="form-control" step="0.01"
                           value="<?= esc(old('nilai_perubahan', $adendum['nilai_perubahan'] ?? '0')) ?>"
                           placeholder="Bisa negatif untuk pengurangan">
                    <div class="form-text">Isi positif untuk penambahan, negatif untuk pengurangan anggaran.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <?php foreach (['Draft', 'Diajukan', 'Disetujui', 'Ditolak'] as $st): ?>
                            <option value="<?= $st ?>"
                                <?= old('status', $adendum['status'] ?? 'Draft') === $st ? 'selected' : '' ?>>
                                <?= $st ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Perihal <span class="text-danger">*</span></label>
                    <input type="text" name="perihal" class="form-control"
                           value="<?= esc(old('perihal', $adendum['perihal'] ?? '')) ?>"
                           placeholder="Ringkasan perubahan" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Uraian / Keterangan</label>
                    <textarea name="uraian" class="form-control" rows="4"
                              placeholder="Detail perubahan lingkup pekerjaan, volume, atau anggaran..."><?= esc(old('uraian', $adendum['uraian'] ?? '')) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">File Adendum (PDF)</label>
                    <input type="file" name="file_adendum" class="form-control" accept=".pdf">
                    <?php if (!empty($adendum['file_adendum'])): ?>
                        <div class="form-text">
                            File saat ini:
                            <a href="<?= base_url($adendum['file_adendum']) ?>" target="_blank">Lihat file</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i> <?= $isEdit ? 'Perbarui' : 'Simpan' ?>
                </button>
                <a href="<?= base_url('adendum') ?>" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
