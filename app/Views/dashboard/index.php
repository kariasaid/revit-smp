<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .dash-welcome {
        background: linear-gradient(120deg, #0b3d91 0%, #1d6fd8 55%, #3b9cff 100%);
        border-radius: 1rem;
        overflow: hidden;
        position: relative;
        min-height: 140px;
    }
    .dash-welcome::after {
        content: '';
        position: absolute;
        inset: 0;
        background: url('https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=800&q=60') center/cover no-repeat;
        opacity: .28;
        mix-blend-mode: overlay;
    }
    .dash-welcome .welcome-inner {
        position: relative;
        z-index: 1;
        color: #fff;
        padding: 1.5rem 1.75rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }
    .dash-welcome .welcome-icon {
        width: 56px; height: 56px;
        background: rgba(255,255,255,.18);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
        backdrop-filter: blur(6px);
    }
    .dash-welcome h4 { font-weight: 700; margin: 0 0 .25rem; font-size: 1.35rem; }
    .dash-welcome p { margin: 0; opacity: .88; font-size: .9rem; }

    .kpi-card {
        background: #fff;
        border-radius: .9rem;
        padding: 1.15rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        height: 100%;
        border: 1px solid #f0f2f5;
        transition: box-shadow .2s, transform .15s;
    }
    .kpi-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); transform: translateY(-1px); }
    .kpi-card .kpi-icon {
        width: 42px; height: 42px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem;
        margin-bottom: .75rem;
    }
    .kpi-card .kpi-label { font-size: .78rem; color: #6b7280; font-weight: 500; margin-bottom: .2rem; }
    .kpi-card .kpi-value { font-size: 1.45rem; font-weight: 700; color: #111827; line-height: 1.2; }
    .kpi-card .kpi-sub { font-size: .75rem; color: #9ca3af; margin-top: .15rem; }
    .kpi-card .kpi-delta { font-size: .75rem; font-weight: 600; }
    .kpi-card .kpi-delta.up { color: #059669; }
    .kpi-card .kpi-delta.down { color: #dc2626; }
    .kpi-progress {
        height: 5px;
        border-radius: 99px;
        background: #eef2f7;
        margin-top: .65rem;
        overflow: hidden;
    }
    .kpi-progress > span { display: block; height: 100%; border-radius: 99px; transition: width .4s ease; }

    .panel-card {
        background: #fff;
        border-radius: .9rem;
        border: 1px solid #f0f2f5;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
        height: 100%;
    }
    .panel-card .panel-header {
        padding: 1rem 1.25rem .75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #f3f4f6;
        gap: .5rem;
        flex-wrap: wrap;
    }
    .panel-card .panel-header h6 {
        margin: 0; font-weight: 600; font-size: .95rem; color: #1f2937;
        display: flex; align-items: center; gap: .5rem;
    }
    .panel-card .panel-body { padding: 1rem 1.25rem 1.25rem; }
    .panel-card .panel-sub { font-size: .78rem; color: #9ca3af; margin: .15rem 0 0; }

    .status-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .3rem .7rem; border-radius: 99px;
        font-size: .75rem; font-weight: 600;
    }
    .status-pill.normal { background: #d1fae5; color: #065f46; }
    .status-pill.terlambat { background: #fee2e2; color: #991b1b; }
    .status-pill.maju { background: #dbeafe; color: #1e40af; }

    .activity-item {
        display: flex; gap: .75rem; padding: .7rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .activity-item:last-child { border-bottom: 0; }
    .activity-item .act-icon {
        width: 34px; height: 34px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-size: .95rem;
    }
    .activity-item .act-title { font-size: .82rem; font-weight: 600; color: #1f2937; margin: 0; }
    .activity-item .act-desc { font-size: .75rem; color: #6b7280; margin: 0; }
    .activity-item .act-time { font-size: .7rem; color: #9ca3af; white-space: nowrap; }

    .quick-btn {
        display: flex; align-items: center; gap: .65rem;
        padding: .85rem 1rem;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: .75rem;
        text-decoration: none;
        color: #1f2937;
        font-size: .85rem;
        font-weight: 500;
        transition: all .15s;
    }
    .quick-btn:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d5296;
    }
    .quick-btn i.main-icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: #e8f0fe; color: #1d5296; font-size: 1.05rem;
    }

    .mini-bar {
        height: 6px; border-radius: 99px; background: #e5e7eb; overflow: hidden; min-width: 70px;
    }
    .mini-bar > span { display: block; height: 100%; border-radius: 99px; }

    .school-select-card, .status-project-card {
        background: #fff;
        border-radius: .9rem;
        border: 1px solid #f0f2f5;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
        padding: 1rem 1.15rem;
        height: 100%;
    }
    .school-select-card label, .status-project-card label {
        font-size: .75rem; color: #6b7280; font-weight: 500; margin-bottom: .35rem; display: block;
    }

    .filter-chip {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #eff6ff; color: #1d4ed8;
        font-size: .78rem; font-weight: 600;
        padding: .35rem .75rem; border-radius: 99px;
        border: 1px solid #bfdbfe;
    }
    .filter-chip .clear-filter {
        color: #1d4ed8; text-decoration: none; font-size: .9rem; line-height: 1;
        opacity: .7;
    }
    .filter-chip .clear-filter:hover { opacity: 1; }

    .table-dash thead th {
        background: #f9fafb !important;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
        padding: .65rem .75rem;
    }
    .table-dash td {
        padding: .7rem .75rem;
        font-size: .85rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    .table-dash tbody tr:hover { background: #f8fafc; }
    .table-dash tbody tr.is-selected { background: #eff6ff; }
    .table-dash tbody tr.is-selected:hover { background: #dbeafe; }

    .deviasi-pos { color: #059669; font-weight: 600; }
    .deviasi-neg { color: #dc2626; font-weight: 600; }

    .chart-wrap { position: relative; height: 240px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$stats = $stats ?? [];
$totalSekolah     = (int) ($stats['total_sekolah'] ?? count($sekolahList ?? []));
$totalSekolahAll  = (int) ($stats['total_sekolah_all'] ?? 8);
$scopeCount       = (int) ($stats['scope_count'] ?? $totalSekolah);
$avgFisik         = (float) ($stats['avg_fisik'] ?? 0);
$avgKeuangan      = (float) ($stats['avg_keuangan'] ?? 0);
$mingguBerjalan   = (int) ($stats['minggu_berjalan'] ?? 0);
$totalMinggu      = (int) ($stats['total_minggu'] ?? 16);
$pendingValidasi  = (int) ($stats['pending_validasi'] ?? 0);
$totalLaporan     = (int) ($stats['total_laporan'] ?? 0);
$statusProyek     = $stats['status_proyek'] ?? 'Normal';
$deltaFisik       = (float) ($stats['delta_fisik'] ?? 0);
$deltaKeuangan    = (float) ($stats['delta_keuangan'] ?? 0);
$filterLabel      = $stats['filter_label'] ?? 'Semua sekolah dampingan';
$selectedId       = $selectedId ?? null;
$isFiltered       = $selectedId !== null;

$pctSekolah = $totalSekolahAll > 0 ? round(($totalSekolah / $totalSekolahAll) * 100) : 0;
$pctMinggu  = $totalMinggu > 0 ? round(($mingguBerjalan / $totalMinggu) * 100) : 0;
$namaPengawas = esc($user['nama_lengkap'] ?? session()->get('nama_lengkap') ?? 'Pengawas');

$dashBase = base_url('dashboard');
$statusClass = match ($statusProyek) {
    'Terlambat' => 'terlambat',
    'Maju'      => 'maju',
    default     => 'normal',
};
$statusIcon = match ($statusProyek) {
    'Terlambat' => 'bi-exclamation-circle',
    'Maju'      => 'bi-arrow-up-circle',
    default     => 'bi-check-circle',
};
?>
<div class="row g-3 mb-3">
    <div class="col-lg-9">
        <div class="dash-welcome h-100">
            <div class="welcome-inner">
                <div class="welcome-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <h4>Selamat Datang, <span class="fw-bold"><?= $namaPengawas ?></span></h4>
                    <p>
                        <?php if ($isFiltered): ?>
                            Memantau: <strong><?= esc($filterLabel) ?></strong>
                        <?php else: ?>
                            Pantau progres revitalisasi sekolah secara berkala dan tepat sasaran.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="school-select-card">
            <label><i class="bi bi-building me-1"></i> Pilih Sekolah</label>
            <select class="form-select form-select-sm" id="dashSchoolSelect" aria-label="Pilih sekolah">
                <option value="all" <?= !$isFiltered ? 'selected' : '' ?>>— Semua sekolah dampingan —</option>
                <?php foreach ($sekolahList as $s): ?>
                    <option value="<?= (int) $s['id'] ?>" <?= ($isFiltered && (int) $selectedId === (int) $s['id']) ? 'selected' : '' ?>>
                        <?= esc($s['nama_sekolah']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text mt-1" style="font-size:.7rem">Data dashboard berubah sesuai pilihan</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-12">
    <div class="col-6 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#ecfdf5;color:#059669"><i class="bi bi-bullseye"></i></div>
            <div class="kpi-label">Progres Fisik <?= $isFiltered ? '' : 'Rata-rata' ?></div>
            <div class="kpi-value"><?= number_format($avgFisik, 2, ',', '.') ?>%</div>
            <div class="kpi-sub">
                <?php if ($deltaFisik > 0): ?>
                    <span class="kpi-delta up"><i class="bi bi-arrow-up-short"></i> +<?= number_format($deltaFisik, 2, ',', '.') ?>% minggu ini</span>
                <?php elseif ($deltaFisik < 0): ?>
                    <span class="kpi-delta down"><i class="bi bi-arrow-down-short"></i> <?= number_format($deltaFisik, 2, ',', '.') ?>% minggu ini</span>
                <?php else: ?>
                    <span class="text-muted">vs minggu sebelumnya</span>
                <?php endif; ?>
            </div>
            <div class="kpi-progress"><span style="width:<?= min(100, $avgFisik) ?>%;background:#10b981"></span></div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#eff6ff;color:#2563eb"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-label">Progres Keuangan <?= $isFiltered ? '' : 'Rata-rata' ?></div>
            <div class="kpi-value"><?= number_format($avgKeuangan, 2, ',', '.') ?>%</div>
            <div class="kpi-sub">
                <?php if ($deltaKeuangan > 0): ?>
                    <span class="kpi-delta up"><i class="bi bi-arrow-up-short"></i> serapan naik minggu ini</span>
                <?php elseif ($deltaKeuangan < 0): ?>
                    <span class="kpi-delta down"><i class="bi bi-arrow-down-short"></i> serapan turun minggu ini</span>
                <?php else: ?>
                    <span class="text-muted">vs minggu sebelumnya</span>
                <?php endif; ?>
            </div>
            <div class="kpi-progress"><span style="width:<?= min(100, $avgKeuangan) ?>%;background:#3b82f6"></span></div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#f5f3ff;color:#7c3aed"><i class="bi bi-calendar-week"></i></div>
            <div class="kpi-label">Minggu Berjalan</div>
            <div class="kpi-value"><?= $mingguBerjalan ?> <span class="fs-6 fw-normal text-muted">/ <?= $totalMinggu ?></span></div>
            <div class="kpi-sub"><?= $pctMinggu ?>% waktu proyek</div>
            <div class="kpi-progress"><span style="width:<?= $pctMinggu ?>%;background:#8b5cf6"></span></div>
        </div>
    </div>
    <div class="col-12 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#fff7ed;color:#ea580c"><i class="bi bi-file-earmark-text"></i></div>
            <div class="kpi-label">Laporan Menunggu Validasi</div>
            <div class="kpi-value"><?= $pendingValidasi ?></div>
            <div class="kpi-sub">dari <?= $totalLaporan ?> laporan <?= $isFiltered ? 'sekolah ini' : 'terbaru' ?></div>
            <div class="kpi-progress">
                <span style="width:<?= $totalLaporan > 0 ? min(100, ($pendingValidasi / max(1, $totalLaporan)) * 100) : 0 ?>%;background:#f59e0b"></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-12">
    <div class="col-lg-9">
        <div class="panel-card">
            <div class="panel-header">
                <div>
                    <h6><i class="bi bi-graph-up text-primary"></i> Target vs Realisasi Progres Fisik</h6>
                    <p class="panel-sub">
                        <?= $isFiltered ? 'Kumulatif · ' . esc($filterLabel) : 'Rata-rata kumulatif seluruh sekolah dampingan' ?>
                    </p>
                </div>
            </div>
            <div class="panel-body">
                <div class="chart-wrap">
                    <canvas id="chartFisik"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="panel-card">
            <div class="panel-header">
                <h6><i class="bi bi-bell text-primary"></i> Aktivitas Terbaru</h6>
                <a href="<?= base_url('pelaksanaan/progres' . ($isFiltered ? '/' . $selectedId : '')) ?>" class="small text-primary text-decoration-none">Lihat semua →</a>
            </div>
            <div class="panel-body pt-1" style="max-height: 300px; overflow-y: auto;">
                <?php if (empty($activities)): ?>
                    <p class="text-muted small text-center py-4 mb-0">Belum ada aktivitas<?= $isFiltered ? ' untuk sekolah ini' : '' ?></p>
                <?php else: ?>
                    <?php foreach ($activities as $act): ?>
                        <?php
                        $bg = match ($act['color'] ?? '') {
                            'success' => 'background:#ecfdf5;color:#059669',
                            'warning' => 'background:#fff7ed;color:#ea580c',
                            'danger'  => 'background:#fef2f2;color:#dc2626',
                            'info'    => 'background:#eff6ff;color:#2563eb',
                            default   => 'background:#f3f4f6;color:#6b7280',
                        };
                        $timeLabel = '';
                        if (!empty($act['time'])) {
                            try {
                                $dt = new \DateTime($act['time']);
                                $diff = (new \DateTime())->diff($dt);
                                if ($diff->days === 0) {
                                    $timeLabel = $diff->h > 0 ? $diff->h . ' jam lalu' : 'Baru saja';
                                } elseif ($diff->days === 1) {
                                    $timeLabel = '1 hari lalu';
                                } else {
                                    $timeLabel = $diff->days . ' hari lalu';
                                }
                            } catch (\Exception $e) {
                                $timeLabel = $act['time'];
                            }
                        }
                        ?>
                        <div class="activity-item">
                            <div class="act-icon" style="<?= $bg ?>">
                                <i class="bi <?= esc($act['icon'] ?? 'bi-info-circle') ?>"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <p class="act-title text-truncate"><?= esc($act['title']) ?></p>
                                <p class="act-desc text-truncate"><?= esc($act['desc']) ?></p>
                            </div>
                            <span class="act-time"><?= esc($timeLabel) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-12">
        <div class="panel-card">
            <div class="panel-header">
                <h6>
                    <i class="bi bi-building text-primary"></i>
                    <?= $isFiltered ? 'Detail Sekolah Dipilih' : 'Status Sekolah Dampingan' ?>
                </h6>
                <?php if (!$isFiltered): ?>
                    <span class="small text-muted">Klik baris atau pilih di dropdown untuk filter</span>
                <?php endif; ?>
            </div>
            <div class="panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-dash mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Sekolah</th>
                                <th>NPSN</th>
                                <th>Target Fisik</th>
                                <th>Realisasi Fisik</th>
                                <th>Deviasi</th>
                                <th>Keuangan</th>
                                <th>Minggu</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($schoolRows)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Belum ada sekolah kelolaan</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($schoolRows as $i => $row): ?>
                                    <tr class="<?= ($isFiltered && (int)$selectedId === (int)$row['id']) ? 'is-selected' : '' ?>"
                                        style="cursor:pointer"
                                        onclick="location.href='<?= $dashBase ?>?sekolah=<?= (int)$row['id'] ?>'"
                                        title="Filter dashboard ke sekolah ini">
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <span class="fw-semibold text-primary"><?= esc($row['nama_sekolah']) ?></span>
                                        </td>
                                        <td class="text-muted"><?= esc($row['npsn']) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="mini-bar flex-grow-1">
                                                    <span style="width:<?= min(100, $row['target_fisik']) ?>%;background:#93c5fd"></span>
                                                </div>
                                                <span class="small"><?= number_format($row['target_fisik'], 2, ',', '.') ?>%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="mini-bar flex-grow-1">
                                                    <span style="width:<?= min(100, $row['realisasi_fisik']) ?>%;background:#34d399"></span>
                                                </div>
                                                <span class="small"><?= number_format($row['realisasi_fisik'], 2, ',', '.') ?>%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($row['deviasi'] >= 0): ?>
                                                <span class="deviasi-pos">+<?= number_format($row['deviasi'], 2, ',', '.') ?>%</span>
                                            <?php else: ?>
                                                <span class="deviasi-neg"><?= number_format($row['deviasi'], 2, ',', '.') ?>%</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="mini-bar flex-grow-1">
                                                    <span style="width:<?= min(100, $row['keuangan']) ?>%;background:#60a5fa"></span>
                                                </div>
                                                <span class="small"><?= number_format($row['keuangan'], 2, ',', '.') ?>%</span>
                                            </div>
                                        </td>
                                        <td class="text-nowrap"><?= (int)$row['minggu'] ?> / <?= (int)$row['total_minggu'] ?></td>
                                        <td>
                                            <?php if ($row['status'] === 'Terlambat'): ?>
                                                <span class="status-pill terlambat"><i class="bi bi-exclamation-circle"></i> Terlambat</span>
                                            <?php elseif ($row['status'] === 'Maju'): ?>
                                                <span class="status-pill maju"><i class="bi bi-arrow-up-circle"></i> Maju</span>
                                            <?php else: ?>
                                                <span class="status-pill normal"><i class="bi bi-check-circle"></i> Normal</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">
                        Menampilkan <?= count($schoolRows) ?> sekolah
                        <?php if ($isFiltered): ?> · <a href="<?= $dashBase ?>?sekolah=all" class="text-primary text-decoration-none">Tampilkan semua</a><?php endif; ?>
                    </span>
                    <a href="<?= base_url('pelaksanaan/progres' . ($isFiltered ? '/' . $selectedId : '')) ?>" class="small text-primary text-decoration-none">
                        Buka progres pelaksanaan →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const select = document.getElementById('dashSchoolSelect');
    if (select) {
        select.addEventListener('change', function () {
            const v = this.value || 'all';
            const url = new URL('<?= $dashBase ?>', window.location.origin);
            url.searchParams.set('sekolah', v);
            window.location.href = url.toString();
        });
    }

    const labels = ['M1','M2','M3','M4','M5','M6','M7','M8','M9','M10','M11','M12','M13','M14','M15','M16'];
    let tData = <?= json_encode($chartAkumTarget ?? $chartTarget ?? array_fill(0, 16, 0)) ?>;
    let rData = <?= json_encode($chartAkumRealisasi ?? $chartRealisasi ?? array_fill(0, 16, 0)) ?>;
    let kData = <?= json_encode($chartAkumKeuangan ?? $chartKeuangan ?? array_fill(0, 16, 0)) ?>;

    const allZero = rData.every(v => !v) && tData.every(v => !v);
    if (allZero) {
        tData = [5,10,16,22,28,35,42,50,57,64,70,76,82,88,94,100];
        rData = [4,8,13,18,24,30,37,44,50,56,62,68,74,80,86,92];
        kData = [20,45,80,120,170,230,300,380,470,570,680,800,930,1070,1220,1380].map(v => v * 1e6);
    }

    const ctxF = document.getElementById('chartFisik');
    if (ctxF) {
        new Chart(ctxF, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Target',
                        data: tData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'transparent',
                        borderWidth: 2.5,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#3b82f6',
                    },
                    {
                        label: 'Realisasi',
                        data: rData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,.1)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#10b981',
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { boxWidth: 12, usePointStyle: true, pointStyle: 'line', font: { size: 11 } }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: (ctx) => ctx.dataset.label + ': ' + Number(ctx.raw).toFixed(2) + '%'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: v => v + '%', font: { size: 10 } },
                        grid: { color: '#f3f4f6' }
                    },
                    x: { ticks: { font: { size: 10 } }, grid: { display: false } }
                },
                interaction: { mode: 'nearest', axis: 'x', intersect: false }
            }
        });
    }

    const ctxK = document.getElementById('chartKeuangan');
    if (ctxK) {
        new Chart(ctxK, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Realisasi',
                    data: kData,
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                    barPercentage: 0.65,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const v = ctx.raw || 0;
                                if (Math.abs(v) >= 1e6) return 'Rp ' + (v / 1e6).toFixed(1) + ' jt';
                                if (Math.abs(v) >= 1e3) return 'Rp ' + (v / 1e3).toFixed(0) + ' rb';
                                return 'Rp ' + Number(v).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 10 },
                            callback: (v) => {
                                if (Math.abs(v) >= 1e6) return 'Rp ' + (v / 1e6) + ' jt';
                                if (Math.abs(v) >= 1e3) return 'Rp ' + (v / 1e3) + ' rb';
                                return v;
                            }
                        },
                        grid: { color: '#f3f4f6' }
                    },
                    x: { ticks: { font: { size: 10 } }, grid: { display: false } }
                }
            }
        });
    }
})();
</script>
<?= $this->endSection() ?>
