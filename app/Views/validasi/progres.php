<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$schoolOptions = [];
$yearOptions = [];
foreach (array_merge($progres, $riwayat) as $report) {
    $schoolOptions[(int) $report['sekolah_id']] = [
        'id' => (int) $report['sekolah_id'],
        'nama_sekolah' => $report['nama_sekolah'],
        'npsn' => $report['npsn'],
    ];
    if (!empty($report['tahun_proyek'])) {
        $yearOptions[(int) $report['tahun_proyek']] = (int) $report['tahun_proyek'];
    }
}
usort($schoolOptions, static fn (array $left, array $right): int => strnatcasecmp($left['nama_sekolah'], $right['nama_sekolah']));
rsort($yearOptions, SORT_NUMERIC);
?>

<div class="mb-4">
    <div class="small text-muted mb-1">Administrasi / Validasi</div>
    <h1 class="h4 fw-bold mb-1">Validasi Progres Pelaksanaan</h1>
    <div class="text-muted">Tinjau laporan yang diajukan pengawas sebelum masuk ke rekap progres.</div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-5">
        <label class="form-label" for="schoolFilter">Filter sekolah</label>
        <select class="form-select" id="schoolFilter">
            <option value="">Semua sekolah</option>
            <?php foreach ($schoolOptions as $schoolOption): ?>
                <option value="<?= (int) $schoolOption['id'] ?>"><?= esc($schoolOption['nama_sekolah']) ?> · NPSN <?= esc($schoolOption['npsn']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label" for="projectYearFilter">Tahun proyek berjalan</label>
        <select class="form-select" id="projectYearFilter">
            <option value="">Semua tahun</option>
            <?php foreach ($yearOptions as $year): ?>
                <option value="<?= (int) $year ?>"><?= (int) $year ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <strong><i class="bi bi-inbox text-primary me-2"></i>Menunggu validasi</strong>
        <span class="badge text-bg-primary" id="pendingReportCount"><?= count($progres) ?> laporan</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Sekolah / Pengawas</th>
                        <th>Minggu</th>
                        <th>Target</th>
                        <th>Realisasi</th>
                        <th>Serapan dana</th>
                        <th class="text-end pe-3">Keputusan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($progres)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">Tidak ada laporan yang menunggu validasi.</td></tr>
                    <?php else: ?>
                        <?php foreach ($progres as $row): ?>
                            <tr data-school-id="<?= (int) $row['sekolah_id'] ?>" data-project-year="<?= esc($row['tahun_proyek'] ?? '') ?>">
                                <td class="ps-3">
                                    <div class="fw-semibold"><?= esc($row['nama_sekolah']) ?></div>
                                    <div class="small text-muted">NPSN <?= esc($row['npsn']) ?> · <?= esc($row['nama_pengawas'] ?? 'Pengawas') ?></div>
                                    <div class="small text-muted">Diajukan <?= esc($row['updated_at']) ?></div>
                                    <?php if (!empty($row['pdf_laporan'])): ?>
                                        <a href="<?= base_url($row['pdf_laporan']) ?>" class="btn btn-sm btn-outline-danger mt-2" target="_blank" rel="noopener">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF Laporan
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark border">Minggu <?= (int) $row['minggu_ke'] ?></span></td>
                                <td><?= number_format((float) $row['target_rencana'], 2) ?>%</td>
                                <td><div class="fw-semibold"><?= number_format((float) $row['realisasi_fisik'], 2) ?>% total</div></td>
                                <td>Rp <?= number_format((float) $row['serapan_dana'], 0, ',', '.') ?></td>
                                <td class="text-end pe-3" style="min-width: 250px;">
                                    <form action="<?= base_url('validasi/progres/' . $row['id']) ?>" method="post" enctype="multipart/form-data" class="validation-form">
                                        <?= csrf_field() ?>
                                        <textarea name="catatan" class="form-control form-control-sm mb-2" rows="2" maxlength="2000" placeholder="Catatan admin (wajib jika ditolak)"></textarea>
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-sm btn-outline-danger" type="submit" name="keputusan" value="Ditolak">
                                                <i class="bi bi-arrow-return-left me-1"></i> Tolak
                                            </button>
                                            <button class="btn btn-sm btn-success" type="submit" name="keputusan" value="Diterima">
                                                <i class="bi bi-check2 me-1"></i> Terima
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr class="d-none filter-empty"><td colspan="6" class="text-center text-muted py-4">Tidak ada laporan yang cocok dengan pencarian.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <strong><i class="bi bi-archive text-primary me-2"></i>Semua laporan mingguan</strong>
        <div class="d-flex align-items-center gap-2">
            <span class="badge text-bg-light border" id="historyReportCount"><?= count($riwayat) ?> laporan</span>
            <form id="bulkDeleteForm" action="<?= base_url('validasi/progres/hapus-massal') ?>" method="post" onsubmit="return confirm('Hapus semua laporan yang dipilih beserta foto dokumentasinya?');">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-danger" id="bulkDeleteButton" type="submit" disabled>
                    <i class="bi bi-trash me-1"></i><span id="selectedReportLabel">Hapus dipilih</span>
                </button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3"><input class="form-check-input" id="selectVisibleReports" type="checkbox" aria-label="Pilih semua laporan yang terlihat"></th>
                        <th class="ps-3">Sekolah / Pengawas</th>
                        <th>Minggu</th>
                        <th>Status</th>
                        <th>Realisasi</th>
                        <th>Serapan dana</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($riwayat)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada laporan mingguan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($riwayat as $row): ?>
                            <tr data-school-id="<?= (int) $row['sekolah_id'] ?>" data-project-year="<?= esc($row['tahun_proyek'] ?? '') ?>">
                                <td class="ps-3"><input class="form-check-input report-select" type="checkbox" name="report_ids[]" value="<?= (int) $row['id'] ?>" form="bulkDeleteForm" aria-label="Pilih laporan minggu ke-<?= (int) $row['minggu_ke'] ?> untuk <?= esc($row['nama_sekolah']) ?>"></td>
                                <td class="ps-3">
                                    <div class="fw-semibold"><?= esc($row['nama_sekolah']) ?></div>
                                    <div class="small text-muted">NPSN <?= esc($row['npsn']) ?> · <?= esc($row['nama_pengawas'] ?? 'Pengawas') ?></div>
                                </td>
                                <td>Minggu <?= (int) $row['minggu_ke'] ?></td>
                                <td><span class="badge text-bg-light border"><?= esc($row['status_verval']) ?></span></td>
                                <td><?= number_format((float) $row['realisasi_fisik'], 2) ?>%</td>
                                <td>Rp <?= number_format((float) $row['serapan_dana'], 0, ',', '.') ?></td>
                                <td class="text-end pe-3">
                                    <div class="d-flex justify-content-end align-items-center gap-1">
                                    <a href="<?= base_url('pelaksanaan/progres/lihat/' . $row['id']) ?>" class="btn btn-sm btn-outline-primary" aria-label="Lihat detail laporan minggu ke-<?= (int) $row['minggu_ke'] ?> untuk <?= esc($row['nama_sekolah']) ?>" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (!empty($row['pdf_laporan'])): ?>
                                        <a href="<?= base_url($row['pdf_laporan']) ?>" class="btn btn-sm btn-outline-danger" target="_blank" rel="noopener" aria-label="Buka PDF laporan minggu ke-<?= (int) $row['minggu_ke'] ?> untuk <?= esc($row['nama_sekolah']) ?>">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    <?php endif; ?>
                                    <form action="<?= base_url('validasi/progres/' . $row['id'] . '/hapus') ?>" method="post" class="d-inline-flex m-0" onsubmit="return confirm('Hapus laporan mingguan ini secara permanen? Foto dokumentasi juga akan dihapus.');">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Hapus laporan minggu ke-<?= (int) $row['minggu_ke'] ?> untuk <?= esc($row['nama_sekolah']) ?>">
                                            <i class="bi bi-trash me-1"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr class="d-none filter-empty"><td colspan="7" class="text-center text-muted py-4">Tidak ada laporan yang cocok dengan pencarian.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    const schoolFilter = document.getElementById('schoolFilter');
    const projectYearFilter = document.getElementById('projectYearFilter');
    const reportTables = [
        { body: document.querySelector('.card table tbody'), count: document.getElementById('pendingReportCount') },
        { body: document.querySelectorAll('.card table tbody')[1], count: document.getElementById('historyReportCount') },
    ];
    const selectVisibleReports = document.getElementById('selectVisibleReports');
    const reportSelections = [...document.querySelectorAll('.report-select')];
    const bulkDeleteButton = document.getElementById('bulkDeleteButton');
    const selectedReportLabel = document.getElementById('selectedReportLabel');

    const updateReportSelection = () => {
        const visibleSelections = reportSelections.filter((checkbox) => !checkbox.closest('tr').classList.contains('d-none'));
        const selectedCount = reportSelections.filter((checkbox) => checkbox.checked).length;
        const visibleSelectedCount = visibleSelections.filter((checkbox) => checkbox.checked).length;

        selectVisibleReports.checked = visibleSelections.length > 0 && visibleSelectedCount === visibleSelections.length;
        selectVisibleReports.indeterminate = visibleSelectedCount > 0 && visibleSelectedCount < visibleSelections.length;
        bulkDeleteButton.disabled = selectedCount === 0;
        selectedReportLabel.textContent = selectedCount > 0 ? `Hapus dipilih (${selectedCount})` : 'Hapus dipilih';
    };

    selectVisibleReports.addEventListener('change', () => {
        reportSelections
            .filter((checkbox) => !checkbox.closest('tr').classList.contains('d-none'))
            .forEach((checkbox) => { checkbox.checked = selectVisibleReports.checked; });
        updateReportSelection();
    });

    reportSelections.forEach((checkbox) => checkbox.addEventListener('change', updateReportSelection));

    const filterReports = () => {
        const selectedSchoolId = schoolFilter.value;
        const selectedProjectYear = projectYearFilter.value;

        reportTables.forEach(({ body, count }) => {
            const rows = [...body.querySelectorAll('[data-school-id]')];
            let visible = 0;

            rows.forEach((row) => {
                const matchesSchool = !selectedSchoolId || row.dataset.schoolId === selectedSchoolId;
                const matchesYear = !selectedProjectYear || row.dataset.projectYear === selectedProjectYear;
                const matches = matchesSchool && matchesYear;
                row.classList.toggle('d-none', !matches);
                visible += matches ? 1 : 0;
            });

            body.querySelector('.filter-empty').classList.toggle('d-none', rows.length === 0 || visible > 0);
            count.textContent = selectedSchoolId || selectedProjectYear ? `${visible} dari ${rows.length} laporan` : `${rows.length} laporan`;
        });
        updateReportSelection();
    };

    schoolFilter.addEventListener('change', filterReports);
    projectYearFilter.addEventListener('change', filterReports);

    document.querySelectorAll('.validation-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (event.submitter?.value === 'Ditolak' && !form.querySelector('[name="catatan"]').value.trim()) {
                event.preventDefault();
                window.alert('Catatan wajib diisi untuk menolak progres.');
            }
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
