<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$fmt = fn($n) => number_format((float)$n, 0, ',', '.');
$pct = fn($n) => number_format((float)$n, 2, '.', '') . '%';
$is100 = ($jenis_pelaporan ?? '50%') === '100%';
$akumFisik = (float)($summary['akumulasi_fisik'] ?? 0);
?>

<div class="card mb-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-cloud-upload text-primary"></i>
        <span>Unggah Dokumen Pelaporan <?= esc($jenis_pelaporan) ?></span>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label small text-muted">Sekolah</label>
            <select class="form-select" disabled>
                <option><?= esc($sekolah['nama_sekolah'] ?? '-') ?></option>
            </select>
        </div>

        <!-- Summary Metrics -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-label">TOTAL BANTUAN</div>
                    <div class="stat-value"><?= $fmt($sekolah['dana_diterima'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-label">SERAPAN DANA (VALID)</div>
                    <div class="stat-value text-success"><?= $fmt($summary['akumulasi_serapan'] ?? 0) ?></div>
                    <div class="small text-muted">Diinput: <?= $fmt($summary['akumulasi_serapan'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-label">PROGRES FISIK DIINPUT</div>
                    <div class="stat-value text-primary"><?= $pct($akumFisik) ?></div>
                    <div class="progress mt-2" style="height:6px;">
                        <div class="progress-bar bg-primary" style="width:<?= min(100, $akumFisik) ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-label">PROGRES FISIK DIVALIDASI</div>
                    <div class="stat-value text-success"><?= $pct($akumFisik) ?></div>
                    <div class="progress mt-2" style="height:6px;">
                        <div class="progress-bar bg-success" style="width:<?= min(100, $akumFisik) ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($is100 && $akumFisik < 100): ?>
        <div class="alert alert-soft-warning d-flex align-items-start gap-2 mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                <strong>Akses Unggah Ditutup: Progres Fisik Belum 100%</strong>
                <ul class="mb-0 mt-1 small">
                    <li>Progres Fisik Diinput: <?= $pct($akumFisik) ?></li>
                    <li>Progres Fisik Valid: <?= $pct($akumFisik) ?></li>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabel Dokumen -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>NAMA DOKUMEN PELAPORAN <?= esc($jenis_pelaporan) ?></th>
                        <th>TEMPLATE DOKUMEN</th>
                        <th>STATUS UNGGAH</th>
                        <th>VERIFIKASI & VALIDASI</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dokumen)): ?>
                        <tr><td colspan="6" class="text-center text-muted">Belum ada dokumen</td></tr>
                    <?php else: ?>
                        <?php foreach ($dokumen as $i => $d): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div class="fw-medium"><?= esc($d['nama_dokumen']) ?></div>
                                <div class="small text-muted">Format: application/pdf</div>
                            </td>
                            <td>
                                <?php if (!empty($d['file_template'])): ?>
                                    <a href="#" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i> Unduh Template
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($d['status_unggah'] === 'Sudah Unggah'): ?>
                                    <span class="badge bg-success-subtle text-success">Sudah Unggah</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Belum Unggah</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($d['status_validasi'] === '-'): ?>
                                    <span class="text-muted">-</span>
                                <?php else: ?>
                                    <span class="badge bg-info-subtle text-info"><?= esc($d['status_validasi']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap align-items-center gap-1">
                                <?php if (!empty($d['file_unggah'])): ?>
                                    <a href="<?= base_url('pelaporan/lihat/' . (int) $d['id']) ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                                        <i class="bi bi-eye me-1"></i> Lihat
                                    </a>
                                <?php endif; ?>
                                <?php if (!$is100 || $akumFisik >= 100): ?>
                                <form action="<?= base_url('pelaporan/unggah') ?>" method="post" enctype="multipart/form-data" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="dokumen_id" value="<?= $d['id'] ?>">
                                    <label class="btn btn-sm btn-primary mb-0">
                                        <i class="bi bi-upload me-1"></i> <?= !empty($d['file_unggah']) ? 'Upload Ulang' : 'Upload' ?>
                                        <input type="file" name="file_unggah" accept=".pdf" class="d-none" onchange="this.form.submit()">
                                    </label>
                                </form>
                                <?php elseif (empty($d['file_unggah'])): ?>
                                    <button class="btn btn-sm btn-secondary" disabled>Upload</button>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
