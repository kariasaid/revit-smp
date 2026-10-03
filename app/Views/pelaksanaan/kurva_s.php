<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
/* ===== Kurva S Chart container ===== */
.kurva-chart-card {
    border: none;
    border-radius: .75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
    overflow: hidden;
}
.kurva-chart-card .card-body {
    padding: 1.25rem 1.5rem 1.5rem;
}
.kurva-legend {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 1.25rem;
    margin-bottom: .75rem;
    font-size: .8125rem;
    color: #4b5563;
    font-weight: 500;
}
.kurva-legend-item {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
}
.kurva-legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.kurva-legend-dot.rencana { background: #1d5296; box-shadow: 0 0 0 3px rgba(29,82,150,.15); }
.kurva-legend-dot.realisasi { background: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.15); }

.kurva-chart-wrap {
    position: relative;
    width: 100%;
    height: 380px;
    max-height: 50vh;
}
.kurva-chart-wrap canvas {
    width: 100% !important;
    height: 100% !important;
}

/* Tooltip native Chart.js digaya lewat options; fallback soft */
@media (max-width: 768px) {
    .kurva-chart-wrap { height: 280px; max-height: 40vh; }
    .kurva-legend { justify-content: center; font-size: .75rem; gap: .75rem; }
}

/* Tabel indikator di bawah chart */
.kurva-indikator-table {
    font-size: .78rem;
}
.kurva-indikator-table thead th {
    background: #1d5296 !important;
    color: #fff !important;
    font-weight: 600;
    white-space: nowrap;
    border-color: #17447a !important;
    padding: .5rem .35rem;
}
.kurva-indikator-table td.label-col,
.kurva-indikator-table tbody tr td:first-child {
    text-align: left !important;
    font-weight: 600;
    background: #f8fafc;
    white-space: nowrap;
    position: sticky;
    left: 0;
    z-index: 1;
}
.kurva-indikator-table tbody tr:hover td {
    background: #f1f5f9;
}
.kurva-indikator-table tbody tr:hover td:first-child {
    background: #e8f0fe;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h5 class="mb-0 fw-bold">Kurva S Pelaksanaan</h5>
</div>

<!-- Filter & Actions -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted">Sekolah</label>
                <form method="get" action="<?= base_url('pelaksanaan/kurva-s') ?>" id="formSekolah">
                    <select name="sekolah_id" class="form-select" onchange="window.location.href='<?= base_url('pelaksanaan/kurva-s') ?>/'+this.value">
                        <?php foreach ($sekolahList as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($sekolah['id'] ?? 0) == $s['id'] ? 'selected' : '' ?>>
                                <?= esc($s['nama_sekolah']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="col-md-7 text-md-end">
                <?php if (session()->get('role') === 'pengawas'): ?>
                <form action="<?= base_url('pelaksanaan/kurva-s/kalkulasi') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="sekolah_id" value="<?= (int)($sekolah['id'] ?? 0) ?>">
                    <button type="submit" class="btn btn-warning text-dark fw-semibold me-2">
                        <i class="bi bi-arrow-repeat me-1"></i> Kalkulasi Ulang
                    </button>
                </form>
                <?php endif; ?>
                <form id="formPdfKurvaS" action="<?= base_url('pelaksanaan/kurva-s/pdf/' . ($sekolah['id'] ?? '')) ?>" method="post" target="_blank" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="sekolah_id" value="<?= (int)($sekolah['id'] ?? 0) ?>">
                    <input type="hidden" name="chart_image" id="chartImageInput" value="">
                    <button type="button" class="btn btn-success fw-semibold" id="btnUnduhPdf">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF Kurva S
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Info Sekolah + Personil -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="bi bi-building text-primary fs-5"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold"><?= esc($sekolah['nama_sekolah'] ?? '-') ?></h6>
                    <div class="small text-muted">NPSN: <?= esc($sekolah['npsn'] ?? '-') ?></div>
                </div>
            </div>
            <span class="badge bg-primary-subtle text-primary">Kurva S Rencana vs Realisasi</span>
        </div>
        <hr>
        <div class="row small">
            <div class="col-md-6">
                <i class="bi bi-person me-1 text-muted"></i>
                <strong>Perencana:</strong> <?= esc($personil['perencana'] ?? '-') ?>
                &nbsp;·&nbsp; <i class="bi bi-telephone me-1"></i><?= esc($personil['hp_perencana'] ?? '-') ?>
            </div>
            <div class="col-md-6">
                <i class="bi bi-person-check me-1 text-muted"></i>
                <strong>Pengawas:</strong> <?= esc($personil['pengawas'] ?? '-') ?>
                &nbsp;·&nbsp; <i class="bi bi-telephone me-1"></i><?= esc($personil['hp_pengawas'] ?? '-') ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="card kurva-chart-card mb-4">
    <div class="card-body">
        <div class="kurva-legend">
            <span class="kurva-legend-item">
                <span class="kurva-legend-dot rencana"></span> Akumulasi Rencana
            </span>
            <span class="kurva-legend-item">
                <span class="kurva-legend-dot realisasi"></span> Akumulasi Realisasi
            </span>
        </div>
        <div class="kurva-chart-wrap">
            <canvas id="kurvaSChart" aria-label="Kurva S Rencana vs Realisasi" role="img"></canvas>
        </div>
    </div>
</div>

<!-- Tabel Indikator -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 text-center kurva-indikator-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-start">INDIKATOR</th>
                        <?php foreach ($kurva as $k): ?>
                            <th>M-<?= $k['minggu_ke'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-start fw-medium">Rencana (%)</td>
                        <?php foreach ($kurva as $k): ?>
                            <td><?= number_format($k['rencana'], 2) ?>%</td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td class="text-start fw-medium">Akumulasi Rencana (%)</td>
                        <?php foreach ($kurva as $k): ?>
                            <td><?= number_format($k['akumulasi_rencana'], 2) ?>%</td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td class="text-start fw-medium">Realisasi (%)</td>
                        <?php foreach ($kurva as $k): ?>
                            <td><?= number_format($k['realisasi'], 2) ?>%</td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td class="text-start fw-medium">Akumulasi Realisasi (%)</td>
                        <?php foreach ($kurva as $k): ?>
                            <td><?= number_format($k['akumulasi_realisasi'], 2) ?>%</td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td class="text-start fw-medium">Deviasi Mingguan (%)</td>
                        <?php foreach ($kurva as $k): ?>
                            <?php $d = $k['deviasi_mingguan']; ?>
                            <td class="<?= $d >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= ($d >= 0 ? '' : '') . number_format($d, 2) ?>%
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td class="text-start fw-medium">Akumulasi Deviasi (%)</td>
                        <?php foreach ($kurva as $k): ?>
                            <?php $d = $k['akumulasi_deviasi']; ?>
                            <td class="<?= $d >= 0 ? 'text-success bg-success-subtle' : 'text-danger bg-danger-subtle' ?>">
                                <?= number_format($d, 2) ?>%
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const labels = <?= json_encode(array_map(fn($k) => 'Minggu ' . $k['minggu_ke'], $kurva)) ?>;
const akumRencana = <?= json_encode(array_column($kurva, 'akumulasi_rencana')) ?>;
const akumRealisasi = <?= json_encode(array_column($kurva, 'akumulasi_realisasi')) ?>;

const chartFont = {
    family: "'Inter', system-ui, -apple-system, sans-serif",
    size: 12,
};

window.kurvaSChartInstance = new Chart(document.getElementById('kurvaSChart'), {
    type: 'line',
    data: {
        labels: ['Mulai', ...labels],
        datasets: [
            {
                label: 'Akumulasi Rencana',
                data: [0, ...akumRencana],
                borderColor: '#1d5296',
                backgroundColor: 'rgba(29, 82, 150, 0.08)',
                borderWidth: 2.5,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#1d5296',
                pointBorderWidth: 2.5,
                pointHoverBackgroundColor: '#1d5296',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2,
                tension: 0.35,
                fill: false,
                order: 2,
            },
            {
                label: 'Akumulasi Realisasi',
                data: [0, ...akumRealisasi],
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.08)',
                borderWidth: 2.5,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#059669',
                pointBorderWidth: 2.5,
                pointHoverBackgroundColor: '#059669',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2,
                tension: 0.35,
                fill: false,
                order: 1,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false, // tinggi dikontrol .kurva-chart-wrap
        devicePixelRatio: Math.max(window.devicePixelRatio || 1, 2), // tajam saat capture PDF
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.92)',
                titleFont: { ...chartFont, size: 12, weight: '600' },
                bodyFont: { ...chartFont, size: 12 },
                padding: 12,
                cornerRadius: 8,
                displayColors: true,
                boxPadding: 6,
                callbacks: {
                    title: (items) => items[0]?.label ?? '',
                    label: (ctx) => ' ' + ctx.dataset.label + ': ' + Number(ctx.parsed.y).toFixed(2) + '%',
                },
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                border: { display: false },
                grid: {
                    color: 'rgba(229, 231, 235, 0.9)',
                    drawTicks: false,
                },
                ticks: {
                    color: '#6b7280',
                    font: chartFont,
                    padding: 8,
                    stepSize: 10,
                    callback: (v) => v + '%',
                },
                title: {
                    display: true,
                    text: 'Persentase Kumulatif (%)',
                    color: '#6b7280',
                    font: { ...chartFont, size: 11, weight: '500' },
                    padding: { bottom: 4 },
                },
            },
            x: {
                border: { display: false },
                grid: {
                    color: 'rgba(229, 231, 235, 0.6)',
                    drawTicks: false,
                },
                ticks: {
                    color: '#6b7280',
                    font: { ...chartFont, size: 11 },
                    maxRotation: 45,
                    minRotation: 0,
                    autoSkip: true,
                    maxTicksLimit: 18,
                    padding: 6,
                },
                title: {
                    display: true,
                    text: 'Periode Pelaksanaan (Minggu)',
                    color: '#6b7280',
                    font: { ...chartFont, size: 11, weight: '500' },
                    padding: { top: 8 },
                },
            },
        },
        animation: {
            duration: 600,
            easing: 'easeOutQuart',
        },
    },
});

// Unduh PDF dengan capture canvas Chart.js
document.getElementById('btnUnduhPdf')?.addEventListener('click', function () {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyiapkan PDF...';

    const form = document.getElementById('formPdfKurvaS');
    const input = document.getElementById('chartImageInput');
    let dataUrl = '';

    try {
        const chart = window.kurvaSChartInstance;
        const canvas = document.getElementById('kurvaSChart');
        if (canvas && chart) {
            // render ulang dengan DPR tinggi agar PDF tajam
            const prevDpr = chart.options.devicePixelRatio;
            chart.options.devicePixelRatio = Math.max(window.devicePixelRatio || 1, 2);
            chart.resize();
            chart.update('none');
            dataUrl = canvas.toDataURL('image/png', 1.0);
            chart.options.devicePixelRatio = prevDpr;
        }
    } catch (e) {
        console.warn('Gagal capture chart:', e);
    }

    input.value = dataUrl;
    form.submit();

    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF Kurva S';
    }, 2000);
});
</script>
<?= $this->endSection() ?>
