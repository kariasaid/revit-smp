<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <div class="small text-muted mb-1">Perencanaan / Monitoring</div>
    <h1 class="h4 fw-bold mb-1">Monitoring Progres Sekolah</h1>
    <div class="text-muted">Pantau jadwal dan status laporan mingguan yang diperiksa admin.</div>
</div>

<?php if (empty($schools)): ?>
    <div class="card"><div class="card-body text-center text-muted py-5">Belum ada sekolah yang ditugaskan kepada Anda.</div></div>
<?php else: ?>
    <?php foreach ($schools as $school): ?>
        <section class="card mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3 py-3">
                <div>
                    <h2 class="h6 fw-bold mb-1"><?= esc($school['nama_sekolah']) ?></h2>
                    <div class="small text-muted">NPSN <?= esc($school['npsn']) ?> · <?= esc($school['kab_kota'] ?? '-') ?>, <?= esc($school['provinsi'] ?? '-') ?></div>
                </div>
                <?php if ($school['schedule_status'] === 'Diterima'): ?>
                    <span class="badge text-bg-success">Time schedule diterima</span>
                <?php elseif ($school['schedule_status'] === 'Diajukan'): ?>
                    <span class="badge text-bg-warning">Time schedule menunggu admin</span>
                <?php elseif ($school['schedule_status'] === 'Ditolak'): ?>
                    <span class="badge text-bg-danger">Time schedule perlu revisi</span>
                <?php else: ?>
                    <span class="badge text-bg-secondary">Time schedule belum dibuat</span>
                <?php endif; ?>
            </div>
            <div class="card-body border-bottom py-3">
                <div class="d-flex flex-wrap gap-3 small">
                    <span><strong><?= (int) $school['progress_counts']['Diterima'] ?></strong> diterima</span>
                    <span><strong><?= (int) $school['progress_counts']['Diajukan'] ?></strong> menunggu admin</span>
                    <span><strong><?= (int) $school['progress_counts']['Ditolak'] ?></strong> ditolak</span>
                    <span><strong><?= (int) $school['progress_counts']['Draft'] ?></strong> draf</span>
                    <span><strong><?= (int) $school['not_reported'] ?></strong> belum dilaporkan</span>
                </div>
            </div>
            <div class="card-body border-bottom">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <strong class="small"><i class="bi bi-graph-up text-primary me-1"></i> Kurva S</strong>
                    <span class="small text-muted">Akumulasi rencana dan realisasi tervalidasi</span>
                </div>
                <div style="height: 260px; min-width: 0;">
                    <canvas id="kurva-perencana-<?= (int) $school['id'] ?>" aria-label="Kurva S <?= esc($school['nama_sekolah']) ?>" role="img"></canvas>
                </div>
            </div>
            <div class="card-body p-0 border-bottom">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 text-center text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">INDIKATOR</th>
                                <?php foreach ($school['curve_table'] as $curveRow): ?>
                                    <th>M-<?= (int) $curveRow['minggu_ke'] ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $indicators = [
                                'rencana' => 'RENCANA (%)',
                                'akumulasi_rencana' => 'AKUMULASI RENCANA (%)',
                                'realisasi' => 'REALISASI (%)',
                                'akumulasi_realisasi' => 'AKUMULASI REALISASI (%)',
                                'deviasi' => 'DEVIASI (%)',
                            ];
                            ?>
                            <?php foreach ($indicators as $field => $label): ?>
                                <tr>
                                    <th class="text-start"><?= esc($label) ?></th>
                                    <?php foreach ($school['curve_table'] as $curveRow): ?>
                                        <?php $value = (float) $curveRow[$field]; ?>
                                        <td class="<?= $field === 'deviasi' ? ($value >= 0 ? 'text-success' : 'text-danger') : '' ?>">
                                            <?= number_format($value, 2, ',', '.') ?>%
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Minggu</th>
                                <th>Time schedule</th>
                                <th>Target</th>
                                <th>Realisasi</th>
                                <th>Serapan dana</th>
                                <th>Status laporan</th>
                                <th>Dokumentasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($week = 1; $week <= (int) $school['total_minggu']; $week++): ?>
                                <?php
                                $plan = $school['plans_by_week'][$week] ?? null;
                                $report = $school['progress_by_week'][$week] ?? null;
                                $status = $report['status_verval'] ?? null;
                                $statusClasses = [
                                    'Diterima' => 'text-bg-success',
                                    'Diajukan' => 'text-bg-warning',
                                    'Ditolak' => 'text-bg-danger',
                                    'Draft' => 'text-bg-secondary',
                                ];
                                ?>
                                <tr>
                                    <td class="ps-3 fw-semibold">Minggu <?= $week ?></td>
                                    <td>
                                        <?php if ($plan): ?>
                                            <div><?= esc(date('d/m/Y', strtotime($plan['tanggal_mulai']))) ?> s.d. <?= esc(date('d/m/Y', strtotime($plan['tanggal_selesai']))) ?></div>
                                            <div class="small text-muted">
                                                <?= $plan['status_verval'] === 'Diterima' ? 'Disetujui admin' : esc($plan['status_verval']) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Belum dijadwalkan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $plan ? number_format((float) $plan['target_rencana'], 2, ',', '.') . '%' : '-' ?></td>
                                    <td><?= $report ? number_format((float) $report['realisasi_fisik'], 2, ',', '.') . '%' : '-' ?></td>
                                    <td><?= $report ? 'Rp ' . number_format((float) $report['serapan_dana'], 0, ',', '.') : '-' ?></td>
                                    <td>
                                        <?php if ($status): ?>
                                            <span class="badge <?= esc($statusClasses[$status] ?? 'text-bg-secondary') ?>"><?= esc($status) ?></span>
                                            <?php if (!empty($report['updated_at'])): ?><div class="small text-muted mt-1"><?= esc($report['updated_at']) ?></div><?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Belum dilaporkan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($report): ?>
                                            <?php foreach (['foto_depan' => 'Depan', 'foto_belakang' => 'Belakang', 'foto_dalam' => 'Dalam'] as $field => $label): ?>
                                                <?php if (!empty($report[$field])): ?>
                                                    <a href="<?= base_url($report[$field]) ?>" target="_blank" rel="noopener" title="Foto tampak <?= esc($label) ?>" class="d-inline-block me-1">
                                                        <img src="<?= base_url($report[$field]) ?>" alt="Foto tampak <?= esc($label) ?>" style="width: 44px; height: 36px; object-fit: cover; border-radius: 4px;">
                                                    </a>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($status === 'Diterima'): ?>
                                            <a href="<?= base_url('pelaksanaan/progres/lihat/' . $report['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i> Lihat
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const plannerCurves = <?= json_encode($curveCharts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const plannerChartFont = { family: "'Inter', system-ui, sans-serif", size: 11 };

    Object.entries(plannerCurves).forEach(([schoolId, curve]) => {
        const canvas = document.getElementById(`kurva-perencana-${schoolId}`);
        if (!canvas) return;

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: curve.labels,
                datasets: [
                    {
                        label: 'Akumulasi Rencana',
                        data: curve.planned,
                        borderColor: '#1d5296',
                        backgroundColor: 'rgba(29, 82, 150, 0.08)',
                        borderWidth: 2.5,
                        pointRadius: 3,
                        tension: 0.35,
                    },
                    {
                        label: 'Akumulasi Realisasi',
                        data: curve.actual,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.08)',
                        borderWidth: 2.5,
                        pointRadius: 3,
                        tension: 0.35,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 7, font: plannerChartFont } },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${Number(context.parsed.y).toFixed(2)}%`,
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { stepSize: 10, callback: (value) => `${value}%`, font: plannerChartFont },
                    },
                    x: { ticks: { autoSkip: true, maxTicksLimit: 9, font: plannerChartFont } },
                },
            },
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
