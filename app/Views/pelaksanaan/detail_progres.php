<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <div class="small text-muted mb-1"><?= session()->get('role') === 'admin' ? 'Administrasi / Validasi' : 'Pelaksanaan / Progres Mingguan' ?></div>
        <h1 class="h4 fw-bold mb-1">Detail Laporan Minggu <?= (int) $progres['minggu_ke'] ?></h1>
        <div class="text-muted"><?= esc($sekolah['nama_sekolah']) ?> · NPSN <?= esc($sekolah['npsn']) ?></div>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canPrint): ?>
            <a href="<?= base_url('pelaksanaan/progres/print/' . $progres['id']) ?>" target="_blank" rel="noopener" class="btn btn-primary">
                <i class="bi bi-printer me-1"></i>
            </a>
        <?php endif; ?>
        <a href="<?= base_url($backUrl) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header py-3">
        <strong><i class="bi bi-clipboard2-check text-primary me-2"></i>Ringkasan laporan</strong>
        <?php
        $statusClasses = [
            'Draft' => 'text-bg-secondary',
            'Diajukan' => 'text-bg-warning',
            'Diterima' => 'text-bg-success',
            'Ditolak' => 'text-bg-danger',
        ];
        ?>
        <span class="badge <?= esc($statusClasses[$progres['status_verval']] ?? 'text-bg-light') ?> ms-2"><?= esc($progres['status_verval']) ?></span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="small text-muted">Target rencana</div>
                <div class="fw-semibold"><?= number_format((float) $progres['target_rencana'], 2, ',', '.') ?>%</div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="small text-muted">Total realisasi fisik</div>
                <div class="fw-semibold text-success"><?= number_format((float) $progres['realisasi_fisik'], 2, ',', '.') ?>%</div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="small text-muted">Serapan dana</div>
                <div class="fw-semibold">Rp <?= number_format((float) $progres['serapan_dana'], 0, ',', '.') ?></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="small text-muted">Terakhir diperbarui</div>
                <div class="fw-semibold"><?= esc($progres['updated_at'] ?? '-') ?></div>
            </div>
        </div>
        <?php if (!empty($progres['keterangan'])): ?>
            <hr>
            <div class="small text-muted mb-1">Keterangan lapangan</div>
            <div><?= nl2br(esc($progres['keterangan'])) ?></div>
        <?php endif; ?>
        <?php if (!empty($progres['pdf_laporan'])): ?>
            <hr>
            <a href="<?= base_url($progres['pdf_laporan']) ?>" class="btn btn-outline-danger btn-sm" target="_blank" rel="noopener">
                <i class="bi bi-file-earmark-pdf me-1"></i> Buka PDF Laporan Mingguan
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header py-3">
        <strong><i class="bi bi-images text-primary me-2"></i>Realisasi dan dokumentasi per pekerjaan</strong>
    </div>
    <div class="card-body">
        <?php if ($fotoPekerjaan !== []): ?>
            <?php foreach ($fotoPekerjaan as $foto): ?>
                <section>
                    <h2 class="h6 fw-bold"><?= esc($foto['nama_bantuan']) ?></h2>
                    <div class="mb-3">
                        <span class="small text-muted">Realisasi minggu ini: </span>
                        <?php if ($foto['realisasi_fisik'] === null): ?>
                            <span class="text-muted">Belum dicatat</span>
                        <?php else: ?>
                            <strong><?= number_format((float) $foto['realisasi_fisik'], 2, ',', '.') ?>%</strong>
                        <?php endif; ?>
                    </div>
                    <div class="row g-3">
                        <?php foreach (['foto_depan' => 'Tampak depan', 'foto_belakang' => 'Tampak belakang', 'foto_dalam' => 'Tampak dalam bangunan'] as $field => $label): ?>
                            <div class="col-md-4">
                                <div class="small fw-semibold mb-2"><?= esc($label) ?></div>
                                <?php if (!empty($foto[$field])): ?>
                                    <a href="<?= base_url($foto[$field]) ?>" target="_blank" rel="noopener">
                                        <img src="<?= base_url($foto[$field]) ?>" alt="<?= esc($foto['nama_bantuan']) ?> - <?= esc($label) ?>" class="img-thumbnail" style="height: 220px; width: 100%; object-fit: cover;">
                                    </a>
                                <?php else: ?>
                                    <div class="text-muted small">Foto belum tersedia.</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach (['foto_depan' => 'Tampak depan', 'foto_belakang' => 'Tampak belakang', 'foto_dalam' => 'Tampak dalam bangunan'] as $field => $label): ?>
                    <div class="col-md-4">
                        <div class="small fw-semibold mb-2"><?= esc($label) ?></div>
                        <?php if (!empty($progres[$field])): ?>
                            <a href="<?= base_url($progres[$field]) ?>" target="_blank" rel="noopener">
                                <img src="<?= base_url($progres[$field]) ?>" alt="<?= esc($label) ?>" class="img-thumbnail" style="height: 220px; width: 100%; object-fit: cover;">
                            </a>
                        <?php else: ?>
                            <div class="text-muted small">Foto belum tersedia.</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>