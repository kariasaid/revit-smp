<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$statusLabel = [
    'Draft' => 'Draf',
    'Diajukan' => 'Menunggu validasi',
    'Diterima' => 'Diterima',
    'Ditolak' => 'Perlu perbaikan',
];
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <div class="small text-muted mb-1">Pelaksanaan / Progres Mingguan</div>
        <h1 class="h4 fw-bold mb-1">Input Progres Pelaksanaan</h1>
        <div class="text-muted">Lengkapi laporan mingguan untuk <?= esc($sekolah['nama_sekolah']) ?>.</div>
    </div>
    <a href="<?= base_url('pelaksanaan/progres/' . $sekolah['id']) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke progres
    </a>
</div>

<div class="card mb-4">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="small text-muted">Sekolah</div>
            <div class="fw-semibold"><?= esc($sekolah['nama_sekolah']) ?></div>
            <div class="small text-muted">NPSN <?= esc($sekolah['npsn']) ?> · <?= esc($sekolah['kab_kota'] ?? '-') ?></div>
        </div>
        <div class="text-md-end">
            <div class="small text-muted">Periode pelaksanaan</div>
            <div class="fw-semibold"><?= (int) $sekolah['total_minggu'] ?> minggu</div>
        </div>
    </div>
</div>

<?php if ($existing && $existing['status_verval'] === 'Ditolak'): ?>
    <div class="alert alert-warning">
        <strong>Perlu perbaikan.</strong> Perbarui data minggu ini lalu ajukan kembali.
        <?php if (!empty($existing['keterangan'])): ?>
            <div class="mt-2 small"><?= nl2br(esc($existing['keterangan'])) ?></div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header py-3">
        <strong><i class="bi bi-clipboard2-check text-primary me-2"></i>Detail progres mingguan</strong>
    </div>
    <div class="card-body">
        <form id="weeklyProgressForm" action="<?= base_url('pelaksanaan/progres/simpan') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="sekolah_id" value="<?= (int) $sekolah['id'] ?>">

            <?php if (empty($bantuanPekerjaan)): ?>
                <div class="alert alert-warning">
                    Jenis bantuan untuk sekolah ini belum ditambahkan admin. Progres belum dapat diajukan.
                </div>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-12">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#weeklyPdfModal">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Lampirkan PDF Laporan Mingguan
                    </button>
                    <span class="small text-muted ms-2" id="weeklyPdfSelection">
                        <?php if (!empty($existing['pdf_laporan'])): ?>PDF tersimpan: <?= esc(basename($existing['pdf_laporan'])) ?><?php else: ?>PDF belum dipilih<?php endif; ?>
                    </span>
                    <?php if (!empty($existing['pdf_laporan'])): ?>
                        <a href="<?= base_url($existing['pdf_laporan']) ?>" class="small ms-2" target="_blank" rel="noopener">Lihat PDF saat ini</a>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="minggu_ke" class="form-label">Minggu ke</label>
                    <select class="form-select" id="minggu_ke" name="minggu_ke" required>
                        <option value="">Pilih minggu</option>
                        <?php for ($week = 1; $week <= (int) $sekolah['total_minggu']; $week++): ?>
                            <?php
                            $weekData = $progresByWeek[$week] ?? null;
                            $weekPlan = $planByWeek[$week] ?? null;
                            $locked = !$weekPlan || ($weekData && $weekData['status_verval'] === 'Diterima');
                            $selected = (int) old('minggu_ke', $selectedWeek) === $week;
                            ?>
                            <option value="<?= $week ?>" data-target="<?= esc($weekPlan['target_rencana'] ?? '') ?>" data-start="<?= esc($weekPlan['tanggal_mulai'] ?? '') ?>" data-end="<?= esc($weekPlan['tanggal_selesai'] ?? '') ?>" <?= $locked ? 'disabled' : '' ?> <?= $selected ? 'selected' : '' ?>>
                                Minggu <?= $week ?><?= $weekPlan ? '' : ' · Jadwal belum tersedia' ?><?= $weekData ? ' · ' . esc($statusLabel[$weekData['status_verval']] ?? $weekData['status_verval']) : '' ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <div class="form-text">Laporan yang menunggu validasi masih dapat diperbarui. Laporan yang sudah diterima terkunci.</div>
                </div>
                <div class="col-md-4">
                    <label for="target_rencana" class="form-label">Target rencana (%)</label>
                    <div class="input-group">
                        <input class="form-control" id="target_rencana" type="text" value="<?= esc(number_format((float) ($planByWeek[$selectedWeek]['target_rencana'] ?? 0), 2, '.', '')) ?>" readonly>
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text" id="schedulePeriod">
                        <?php if (!empty($planByWeek[$selectedWeek])): ?>
                            Periode: <?= esc($planByWeek[$selectedWeek]['tanggal_mulai']) ?> s.d. <?= esc($planByWeek[$selectedWeek]['tanggal_selesai']) ?>
                        <?php else: ?>
                            Target dan periode ditentukan oleh perencana.
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="realisasi_fisik" class="form-label">Total realisasi fisik (%)</label>
                    <div class="input-group">
                        <input class="form-control" id="realisasi_fisik" name="realisasi_fisik" type="number" min="0" max="100" step="0.01" value="<?= esc(old('realisasi_fisik', $existing['realisasi_fisik'] ?? '')) ?>" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="serapan_dana" class="form-label">Serapan dana minggu ini (Rp)</label>
                    <input class="form-control" id="serapan_dana" name="serapan_dana" type="number" min="0" step="0.01" value="<?= esc(old('serapan_dana', $existing['serapan_dana'] ?? '0')) ?>" required>
                </div>
                <div class="col-12">
                    <label for="keterangan" class="form-label">Kendala dan keterangan lapangan</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="4" maxlength="2000" placeholder="Catat kondisi pekerjaan, kendala, atau tindak lanjut minggu ini."><?= esc(old('keterangan', $existing['keterangan'] ?? '')) ?></textarea>
                </div>
                <?php foreach ($bantuanPekerjaan as $bantuan): ?>
                    <div class="col-12 mt-4">
                        <h3 class="h6 fw-bold border-top pt-3 mb-3"><?= esc($bantuan['nama_bantuan']) ?></h3>
                        <?php $realisasiPekerjaan = $fotoPekerjaanByBantuan[$bantuan['id']]['realisasi_fisik'] ?? ''; ?>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="realisasi_pekerjaan_<?= (int) $bantuan['id'] ?>" class="form-label">Realisasi minggu ini (%)</label>
                                <div class="input-group">
                                    <input class="form-control" id="realisasi_pekerjaan_<?= (int) $bantuan['id'] ?>" name="realisasi_pekerjaan[<?= (int) $bantuan['id'] ?>]" type="number" min="0" max="100" step="0.01" value="<?= esc(old('realisasi_pekerjaan.' . $bantuan['id'], $realisasiPekerjaan)) ?>" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <?php foreach (['foto_depan' => 'Tampak depan', 'foto_belakang' => 'Tampak belakang', 'foto_dalam' => 'Tampak dalam bangunan'] as $field => $label): ?>
                                <?php $fotoTersimpan = $fotoPekerjaanByBantuan[$bantuan['id']][$field] ?? null; ?>
                                <?php $inputId = 'foto_' . $bantuan['id'] . '_' . $field; ?>
                                <?php $previewId = 'preview_' . $bantuan['id'] . '_' . $field; ?>
                                <?php $helpId = 'help_' . $bantuan['id'] . '_' . $field; ?>
                                <div class="col-md-4">
                                    <label for="<?= esc($inputId) ?>" class="form-label">Foto <?= esc($label) ?></label>
                                    <a id="<?= esc($previewId) ?>" href="<?= !empty($fotoTersimpan) ? base_url($fotoTersimpan) : '#' ?>" data-saved-url="<?= esc(!empty($fotoTersimpan) ? base_url($fotoTersimpan) : '') ?>" target="_blank" rel="noopener" class="<?= !empty($fotoTersimpan) ? 'd-block' : 'd-none' ?> mb-2 progress-photo-preview">
                                        <img src="<?= !empty($fotoTersimpan) ? base_url($fotoTersimpan) : 'data:,' ?>" alt="Pratinjau <?= esc($bantuan['nama_bantuan']) ?> - <?= esc($label) ?>" class="img-thumbnail" style="height: 7cm; width: 100%; object-fit: cover;">
                                    </a>
                                    <input class="form-control progress-photo-input" id="<?= esc($inputId) ?>" data-preview-id="<?= esc($previewId) ?>" data-help-id="<?= esc($helpId) ?>" name="foto[<?= (int) $bantuan['id'] ?>][<?= esc($field) ?>]" type="file" accept="image/jpeg,image/png,image/webp">
                                    <div class="form-text" id="<?= esc($helpId) ?>">JPG, PNG, atau WebP. Sumber maksimal 12 MB, otomatis dikompres hingga 5 MB. Jika dikosongkan, foto terakhir yang diterima sebelum minggu ini akan digunakan; unggah foto baru jika belum tersedia.</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 pt-3 border-top">
                <div class="small text-muted">
                    <?php if ($existing && $existing['status_verval'] === 'Diajukan'): ?>
                        Perubahan akan disimpan pada laporan yang sedang menunggu validasi.
                    <?php else: ?>
                        Laporan akan berstatus <strong>Menunggu validasi</strong> setelah diajukan.
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary" <?= empty($bantuanPekerjaan) ? 'disabled' : '' ?>>
                    <i class="bi bi-send me-1"></i> <?= $existing && $existing['status_verval'] === 'Diajukan' ? 'Simpan perubahan' : 'Ajukan untuk validasi' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="weeklyPdfModal" tabindex="-1" aria-labelledby="weeklyPdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="weeklyPdfModalLabel">PDF Laporan Mingguan</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <label for="pdf_laporan" class="form-label">Pilih dokumen PDF</label>
                <input class="form-control" type="file" id="pdf_laporan" name="pdf_laporan" accept="application/pdf,.pdf" form="weeklyProgressForm">
                <div class="form-text">PDF maksimal 15 MB. Wajib untuk laporan baru; saat revisi, PDF lama tetap tersimpan jika tidak diganti.</div>
                <div class="small text-muted mt-2" id="weeklyPdfFileName">Belum ada file baru dipilih.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Gunakan PDF</button>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    const scheduleWeekSelect = document.getElementById('minggu_ke');
    const scheduleTargetInput = document.getElementById('target_rencana');
    const schedulePeriod = document.getElementById('schedulePeriod');
    scheduleWeekSelect.addEventListener('change', () => {
        const selectedWeek = scheduleWeekSelect.selectedOptions[0];
        scheduleTargetInput.value = selectedWeek.dataset.target || '0.00';
        schedulePeriod.textContent = selectedWeek.dataset.start
            ? `Periode: ${selectedWeek.dataset.start} s.d. ${selectedWeek.dataset.end}`
            : 'Target dan periode ditentukan oleh perencana.';
    });

    document.getElementById('pdf_laporan')?.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        document.getElementById('weeklyPdfFileName').textContent = file
            ? file.name
            : 'Belum ada file baru dipilih.';
        if (file) {
            document.getElementById('weeklyPdfSelection').textContent = `PDF dipilih: ${file.name}`;
        }
    });

    document.querySelectorAll('.progress-photo-input').forEach((input) => {
        const preview = document.getElementById(input.dataset.previewId);
        const image = preview.querySelector('img');
        const help = document.getElementById(input.dataset.helpId);

        input.addEventListener('change', () => {
            if (preview.dataset.objectUrl) {
                URL.revokeObjectURL(preview.dataset.objectUrl);
                delete preview.dataset.objectUrl;
            }

            const file = input.files?.[0];
            help.classList.toggle('d-none', Boolean(file));
            if (!file) {
                const savedUrl = preview.dataset.savedUrl;
                if (savedUrl) {
                    preview.href = savedUrl;
                    image.src = savedUrl;
                    preview.classList.remove('d-none');
                    preview.classList.add('d-block');
                } else {
                    preview.classList.add('d-none');
                    preview.classList.remove('d-block');
                    image.src = 'data:,';
                }
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            preview.dataset.objectUrl = objectUrl;
            preview.href = objectUrl;
            image.src = objectUrl;
            preview.classList.remove('d-none');
            preview.classList.add('d-block');
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
