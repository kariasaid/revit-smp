<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <div class="small text-muted mb-1">Perencanaan / Jadwal Awal</div>
        <h1 class="h4 fw-bold mb-1">Time Schedule Awal</h1>
        <div class="text-muted">Susun rencana mingguan sebelum pengawas mengirim progres.</div>
    </div>
    <?php if ($scheduleStatus === 'Diterima'): ?>
        <span class="badge text-bg-success align-self-center"><i class="bi bi-check2-circle me-1"></i> Disetujui admin</span>
    <?php elseif ($scheduleStatus === 'Diajukan'): ?>
        <span class="badge text-bg-warning align-self-center"><i class="bi bi-hourglass-split me-1"></i> Menunggu verifikasi admin</span>
    <?php elseif ($scheduleStatus === 'Ditolak'): ?>
        <span class="badge text-bg-danger align-self-center"><i class="bi bi-arrow-return-left me-1"></i> Perlu revisi</span>
    <?php else: ?>
        <span class="badge text-bg-warning align-self-center"><i class="bi bi-exclamation-circle me-1"></i> Belum dibuat</span>
    <?php endif; ?>
</div>

<?php if (count($schools) > 1): ?>
    <form method="get" action="<?= base_url('perencana/time-schedule') ?>" class="mb-3">
        <label for="sekolah_id" class="form-label">Pilih sekolah</label>
        <select class="form-select" id="sekolah_id" name="sekolah_id" onchange="this.form.submit()">
            <?php foreach ($schools as $assignedSchool): ?>
                <option value="<?= (int) $assignedSchool['id'] ?>" <?= (int) $assignedSchool['id'] === (int) $school['id'] ? 'selected' : '' ?>>
                    <?= esc($assignedSchool['nama_sekolah']) ?> · NPSN <?= esc($assignedSchool['npsn']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between gap-3">
        <div>
            <div class="small text-muted">Sekolah</div>
            <div class="fw-semibold"><?= esc($school['nama_sekolah']) ?></div>
            <div class="small text-muted">NPSN <?= esc($school['npsn']) ?> · <?= esc($school['kab_kota'] ?? '-') ?></div>
        </div>
        <div class="text-md-end">
            <div class="small text-muted">Durasi jadwal</div>
            <div class="fw-semibold"><?= (int) $school['total_minggu'] ?> minggu</div>
        </div>
    </div>
</div>

<?php if ($scheduleStatus === 'Diajukan'): ?>
    <div class="alert alert-info">Jadwal sudah dikirim dan menunggu verifikasi admin. Pengawas belum dapat menginput progres.</div>
<?php elseif ($scheduleStatus === 'Ditolak'): ?>
    <div class="alert alert-warning">
        <strong>Jadwal perlu diperbaiki.</strong>
        <?php if (!empty($verificationNote)): ?><div class="mt-1"><?= nl2br(esc($verificationNote)) ?></div><?php endif; ?>
        Perbarui jadwal lalu ajukan kembali kepada admin.
    </div>
<?php elseif ($scheduleStatus === 'Diterima'): ?>
    <div class="alert alert-success">Jadwal telah diverifikasi admin dan menjadi acuan pengawas.</div>
<?php endif; ?>

<?php if (!$scheduleExists && $progressWeeks > 0): ?>
    <div class="alert alert-info">
        <strong>Baseline proyek berjalan.</strong>
        Target <?= (int) $progressWeeks ?> minggu yang sudah dilaporkan diambil dari progres sekolah ini (<?= number_format($progressTargetTotal, 2, ',', '.') ?>%).
        Sisa target dibagi sebagai saran untuk minggu berikutnya. Periksa target dan lengkapi tanggal proyek sebelum menyimpan.
    </div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <strong><i class="bi bi-graph-up text-primary me-2"></i>Kurva S Time Schedule Awal</strong>
        <span class="small text-muted">Rencana mengikuti target mingguan; realisasi memakai progres yang diterima admin.</span>
    </div>
    <div class="card-body">
        <div style="height: 280px; min-width: 0;">
            <canvas id="timeScheduleCurve" aria-label="Kurva S time schedule awal" role="img"></canvas>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header py-3">
        <strong><i class="bi bi-table text-primary me-2"></i>Indikator Kurva S</strong>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 text-center text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th class="text-start">INDIKATOR</th>
                        <?php for ($week = 1; $week <= (int) $school['total_minggu']; $week++): ?>
                            <th>M-<?= $week ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ([
                        'rencana' => 'RENCANA (%)',
                        'akumulasi-rencana' => 'AKUMULASI RENCANA (%)',
                    ] as $indicator => $label): ?>
                        <tr>
                            <th class="text-start"><?= esc($label) ?></th>
                            <?php for ($week = 1; $week <= (int) $school['total_minggu']; $week++): ?>
                                <td data-indicator="<?= esc($indicator) ?>" data-week="<?= $week ?>"></td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<form action="<?= base_url('perencana/time-schedule/simpan') ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="sekolah_id" value="<?= (int) $school['id'] ?>">
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <div>
                <strong><i class="bi bi-calendar-week text-primary me-2"></i>Rencana kerja mingguan</strong>
                <div class="small text-muted fw-normal">Atur rentang tanggal dan target fisik per minggu. Total target harus 100%.</div>
            </div>
            <div class="small fw-semibold">Total target: <span id="totalTarget">0,00%</span></div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Minggu</th>
                            <th>Periode jadwal</th>
                            <th>Target fisik (%)</th>
                            <th>Rencana pekerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($week = 1; $week <= (int) $school['total_minggu']; $week++): ?>
                            <?php $plan = $plansByWeek[$week] ?? []; ?>
                            <tr>
                                <td class="ps-3 fw-semibold">Minggu <?= $week ?></td>
                                <td style="min-width: 230px;">
                                    <?php if ($week === 1): ?>
                                        <input class="form-control form-control-sm" id="tanggal_mulai_1" type="date" name="tanggal_mulai_1" value="<?= esc(old('tanggal_mulai_1', $plan['tanggal_mulai'] ?? '')) ?>" required <?= $scheduleStatus === 'Diterima' ? 'disabled' : '' ?>>
                                        <div class="form-text">Minggu berikutnya otomatis berjarak 7 hari.</div>
                                    <?php endif; ?>
                                    <span class="small text-muted week-period" data-week="<?= $week ?>">
                                        <?php if ($week === 1): ?>Masukkan tanggal mulai Minggu 1.<?php else: ?>Otomatis setelah tanggal Minggu 1 diisi.<?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input class="form-control target-input" type="number" min="0" max="100" step="0.01" name="target_rencana_<?= $week ?>" value="<?= esc(old('target_rencana_' . $week, $plan['target_rencana'] ?? $suggestedTargets[$week] ?? '')) ?>" required <?= $scheduleStatus === 'Diterima' ? 'disabled' : '' ?>>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text">
                                        <?= ($targetSources[$week] ?? '') === 'progres' ? 'Dari progres berjalan' : ((($targetSources[$week] ?? '') === 'sisa') ? 'Saran sisa target' : '') ?>
                                    </div>
                                </td>
                                <td><input class="form-control form-control-sm" type="text" maxlength="1000" name="keterangan_<?= $week ?>" value="<?= esc(old('keterangan_' . $week, $plan['keterangan'] ?? '')) ?>" placeholder="Contoh: pekerjaan struktur lantai 1" <?= $scheduleStatus === 'Diterima' ? 'disabled' : '' ?>></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <span class="small text-muted">Jadwal harus diverifikasi admin sebelum menjadi acuan progres.</span>
            <?php if ($scheduleStatus !== 'Diterima'): ?>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Ajukan ke admin</button>
            <?php endif; ?>
        </div>
    </div>
</form>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const targetInputs = document.querySelectorAll('.target-input');
    const totalTarget = document.getElementById('totalTarget');
    const firstWeekStart = document.getElementById('tanggal_mulai_1');
    const weekPeriods = document.querySelectorAll('.week-period');
    const updateTargetTotal = () => {
        const total = Array.from(targetInputs).reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
        totalTarget.textContent = `${total.toFixed(2).replace('.', ',')}%`;
        totalTarget.classList.toggle('text-success', Math.abs(total - 100) < 0.005);
        totalTarget.classList.toggle('text-danger', Math.abs(total - 100) >= 0.005);
    };
    targetInputs.forEach((input) => input.addEventListener('input', updateTargetTotal));
    updateTargetTotal();

    const scheduleWeekLabels = ['Mulai', ...Array.from(targetInputs, (_, index) => `Minggu ${index + 1}`)];
    const actualCumulative = <?= json_encode($actualCumulative) ?>;
    const plannedCurve = new Chart(document.getElementById('timeScheduleCurve'), {
        type: 'line',
        data: {
            labels: scheduleWeekLabels,
            datasets: [
                {
                    label: 'Akumulasi Rencana',
                    data: <?= json_encode($plannedCumulative) ?>,
                    borderColor: '#1d5296',
                    backgroundColor: 'rgba(29, 82, 150, 0.08)',
                    borderWidth: 2.5,
                    pointRadius: 3,
                    tension: 0.35,
                },
                {
                    label: 'Akumulasi Realisasi Diterima',
                    data: actualCumulative,
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
                legend: { position: 'top' },
                tooltip: { callbacks: { label: (context) => `${context.dataset.label}: ${Number(context.parsed.y).toFixed(2)}%` } },
            },
            scales: {
                y: { beginAtZero: true, max: 100, ticks: { stepSize: 10, callback: (value) => `${value}%` } },
                x: { ticks: { autoSkip: true, maxTicksLimit: 9 } },
            },
        },
    });

    const updateScheduleCurve = () => {
        let cumulative = 0;
        const planned = [0];
        targetInputs.forEach((input, index) => {
            const week = index + 1;
            const target = parseFloat(input.value) || 0;
            cumulative += target;
            planned.push(Number(cumulative.toFixed(2)));

            const weeklyPlanCell = document.querySelector(`[data-indicator="rencana"][data-week="${week}"]`);
            const cumulativePlanCell = document.querySelector(`[data-indicator="akumulasi-rencana"][data-week="${week}"]`);
            if (weeklyPlanCell) weeklyPlanCell.textContent = `${target.toFixed(2).replace('.', ',')}%`;
            if (cumulativePlanCell) cumulativePlanCell.textContent = `${cumulative.toFixed(2).replace('.', ',')}%`;
        });
        plannedCurve.data.datasets[0].data = planned;
        plannedCurve.update('none');
    };
    targetInputs.forEach((input) => input.addEventListener('input', updateScheduleCurve));
    updateScheduleCurve();

    const updateWeekPeriods = () => {
        if (!firstWeekStart.value) {
            weekPeriods.forEach((period) => {
                period.textContent = Number(period.dataset.week) === 1
                    ? 'Masukkan tanggal mulai Minggu 1.'
                    : 'Otomatis setelah tanggal Minggu 1 diisi.';
            });
            return;
        }

        const [year, month, day] = firstWeekStart.value.split('-').map(Number);
        const firstDate = new Date(year, month - 1, day);
        const formatter = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        weekPeriods.forEach((period) => {
            const weekStart = new Date(firstDate);
            weekStart.setDate(weekStart.getDate() + ((Number(period.dataset.week) - 1) * 7));
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekEnd.getDate() + 6);
            period.textContent = `${formatter.format(weekStart)} s.d. ${formatter.format(weekEnd)}`;
        });
    };
    firstWeekStart.addEventListener('input', updateWeekPeriods);
    updateWeekPeriods();
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
