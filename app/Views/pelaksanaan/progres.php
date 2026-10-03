<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$fmt = fn($n) => number_format((float)$n, 0, ',', '.');
$pct = fn($n) => number_format((float)$n, 2, '.', '') . '%';
$devBadge = function($d) {
    $d = (float)$d;
    if ($d >= 0) return '<span class="badge badge-deviasi-pos">↗ ' . number_format($d, 2) . '%</span>';
    return '<span class="badge badge-deviasi-neg">↘ ' . number_format($d, 2) . '%</span>';
};
$statusBadge = [
    'Draft' => 'text-bg-secondary',
    'Diajukan' => 'text-bg-warning',
    'Diterima' => 'badge-diterima',
    'Ditolak' => 'text-bg-danger',
];
?>

<!-- Header Sekolah -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-building fs-4 text-primary"></i>
        <div>
            <h5 class="mb-0 fw-bold"><?= esc($sekolah['nama_sekolah'] ?? '-') ?></h5>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Identitas Sekolah -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-info-circle text-primary"></i> Identitas Sekolah
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted" style="width:40%">Nama Sekolah</td><td class="fw-semibold">: <?= esc($sekolah['nama_sekolah'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">NPSN</td><td>: <?= esc($sekolah['npsn'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">Provinsi</td><td>: <?= esc($sekolah['provinsi'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">Kab/Kota</td><td>: <?= esc($sekolah['kab_kota'] ?? '-') ?></td></tr>
                </table>
                <hr>
                <div class="card-header px-0 pt-0 d-flex align-items-center gap-2">
                    <i class="bi bi-cash-stack text-primary"></i> Rencana, Realisasi & Serapan Dana
                </div>
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted" style="width:45%">Jadwal Perencanaan</td><td>: <?= (int)($sekolah['total_minggu'] ?? 16) ?> Minggu</td></tr>
                    <tr><td class="text-muted">Pelaksanaan Telah Diinput</td><td>: <?= $summary['total_laporan'] ?? 0 ?> Minggu</td></tr>
                    <tr><td class="text-muted">Pelaksanaan Telah Divalidasi</td><td>: <?= $summary['minggu_divalidasi'] ?? 0 ?> Minggu</td></tr>
                    <tr><td class="text-muted">Akumulasi Fisik</td><td>: <span class="text-success fw-bold"><?= $pct($summary['akumulasi_fisik'] ?? 0) ?></span></td></tr>
                    <tr><td class="text-muted">Dana Diterima</td><td>: <?= $fmt($sekolah['dana_diterima'] ?? 0) ?></td></tr>
                    <tr><td class="text-muted">Akumulasi Serapan Anggaran</td><td>: <span class="text-warning fw-bold"><?= $fmt($summary['akumulasi_serapan'] ?? 0) ?></span></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Kontak Personil -->
    <div class="col-lg-6">
        <div class="card h-70">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-people text-primary"></i> Kontak Personil
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted" style="width:40%">Nama Kepala Sekolah</td>
                        <td>: <?= esc($personil['kepala_sekolah'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. Telp Sekolah</td>
                        <td>: <?= esc($personil['hp_kepala_sekolah'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama Perencana</td>
                        <td>: <?= esc($personil['perencana'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. HP Perencana</td>
                        <td>: <?= esc($personil['hp_perencana'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama Pengawas</td>
                        <td>: <?= esc($personil['pengawas'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. HP Pengawas</td>
                        <td>: <?= esc($personil['hp_pengawas'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Laporan</div>
            <div class="stat-value"><?= $summary['total_laporan'] ?? 0 ?> Minggu</div>
            <i class="bi bi-calendar3 text-muted"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Total Realisasi Fisik</div>
            <div class="stat-value text-success"><?= $pct($summary['akumulasi_fisik'] ?? 0) ?></div>
            <i class="bi bi-bar-chart text-success"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Status Deviasi Terakhir</div>
            <div class="stat-value <?= ($summary['status_deviasi'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                <?= number_format($summary['status_deviasi'] ?? 0, 2) ?>%
            </div>
            <i class="bi bi-graph-up-arrow text-muted"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Serapan Dana Terinput</div>
            <div class="stat-value text-warning"><?= $fmt($summary['akumulasi_serapan'] ?? 0) ?></div>
            <i class="bi bi-wallet2 text-warning"></i>
        </div>
    </div>
</div>

<!-- Tabel Progres Mingguan -->
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <i class="bi bi-table text-primary me-1"></i>
            <strong>Progres Pelaksanaan Mingguan</strong>
            <div class="small text-muted fw-normal">Pantau perkembangan realisasi fisik, deviasi, dan status validasi setiap minggunya</div>
        </div>
        <a href="<?= base_url('pelaksanaan/progres/input/' . ($sekolah['id'] ?? '')) ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Input Progres Mingguan
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>MINGGU</th>
                        <th>SERAPAN DANA</th>
                        <th>TARGET RENCANA</th>
                        <th>REALISASI FISIK</th>
                        <th>DEVIASI</th>
                        <th>STATUS VERVAL</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($progres)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data progres</td></tr>
                    <?php else: ?>
                        <?php foreach ($progres as $p): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border">Minggu ke-<?= (int)$p['minggu_ke'] ?></span></td>
                            <td><?= $fmt($p['serapan_dana']) ?></td>
                            <td>
                                <div class="small text-muted">Target</div>
                                <strong><?= $pct($p['target_rencana']) ?></strong>
                            </td>
                            <td>
                                <div class="small text-muted">Realisasi</div>
                                <strong class="text-success"><?= $pct($p['realisasi_fisik']) ?></strong>
                            </td>
                            <td><?= $devBadge($p['deviasi']) ?></td>
                            <td><span class="badge <?= esc($statusBadge[$p['status_verval']] ?? 'text-bg-secondary') ?>"><?= esc($p['status_verval']) ?></span></td>
                            <td>
                                <?php if (in_array($p['status_verval'], ['Draft', 'Diajukan', 'Ditolak'], true)): ?>
                                    <a href="<?= base_url('pelaksanaan/progres/input/' . $sekolah['id'] . '?minggu=' . $p['minggu_ke']) ?>" class="btn btn-sm btn-outline-primary" title="Perbaiki progres">
                                        <i class="bi bi-pencil me-1"></i> Perbaiki
                                    </a>
                                <?php elseif ($p['status_verval'] === 'Diterima'): ?>
                                    <a href="<?= base_url('pelaksanaan/progres/lihat/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
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

<?= $this->endSection() ?>
