<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$schoolOptions = [];
foreach ($dokumen as $item) {
    $schoolOptions[(int) $item['sekolah_id']] = [
        'id' => (int) $item['sekolah_id'],
        'nama_sekolah' => $item['nama_sekolah'],
        'npsn' => $item['npsn'],
    ];
}
usort($schoolOptions, static fn (array $left, array $right): int => strnatcasecmp($left['nama_sekolah'], $right['nama_sekolah']));
?>

<div class="mb-4">
    <div class="small text-muted mb-1">Administrasi / Pelaporan</div>
    <h1 class="h4 fw-bold mb-1">Validasi Dokumen Pelaporan <?= esc($jenisPelaporan ?? '') ?></h1>
    <div class="text-muted">Tinjau, setujui, tolak, atau hapus dokumen yang diunggah pengawas.</div>
</div>

<div class="mb-3" style="max-width: 480px;">
    <label class="form-label" for="schoolFilter">Filter sekolah</label>
    <select class="form-select" id="schoolFilter">
        <option value="">Semua sekolah</option>
        <?php foreach ($schoolOptions as $schoolOption): ?>
            <option value="<?= (int) $schoolOption['id'] ?>"><?= esc($schoolOption['nama_sekolah']) ?> · NPSN <?= esc($schoolOption['npsn']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <strong><i class="bi bi-file-earmark-check text-primary me-2"></i>Semua dokumen pelaporan</strong>
        <span class="badge text-bg-primary" id="documentCount"><?= count($dokumen) ?> dokumen</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Sekolah</th>
                        <th>Jenis</th>
                        <th>Nama dokumen</th>
                        <th>Status unggah</th>
                        <th>Validasi</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dokumen === []): ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">Belum ada dokumen pelaporan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($dokumen as $item): ?>
                            <tr data-school-id="<?= (int) $item['sekolah_id'] ?>">
                                <td class="ps-3">
                                    <div class="fw-semibold"><?= esc($item['nama_sekolah']) ?></div>
                                    <div class="small text-muted">NPSN <?= esc($item['npsn']) ?></div>
                                </td>
                                <td><?= esc($item['jenis_pelaporan']) ?></td>
                                <td><?= esc($item['nama_dokumen']) ?></td>
                                <td>
                                    <?php if (!empty($item['file_unggah'])): ?>
                                        <span class="badge text-bg-success">Sudah Unggah</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Belum Unggah</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['status_validasi'] === 'Diterima'): ?>
                                        <span class="badge text-bg-success">Diterima</span>
                                    <?php elseif ($item['status_validasi'] === 'Ditolak'): ?>
                                        <span class="badge text-bg-danger">Ditolak</span>
                                    <?php elseif ($item['status_validasi'] === 'Menunggu'): ?>
                                        <span class="badge text-bg-info">Menunggu</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex flex-wrap justify-content-end align-items-center gap-1">
                                        <?php if (!empty($item['file_unggah'])): ?>
                                            <a href="<?= base_url('pelaporan/lihat/' . (int) $item['id']) ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" title="Lihat dokumen" aria-label="Lihat <?= esc($item['nama_dokumen']) ?>">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($item['status_validasi'] === 'Menunggu'): ?>
                                                <form action="<?= base_url('admin/pelaporan/' . (int) $item['id'] . '/validasi') ?>" method="post" class="d-inline-flex m-0">
                                                    <?= csrf_field() ?>
                                                    <button class="btn btn-sm btn-success" type="submit" name="keputusan" value="Diterima" aria-label="Terima <?= esc($item['nama_dokumen']) ?>" title="Terima">
                                                        <i class="bi bi-check2"></i>
                                                    </button>
                                                </form>
                                                <form action="<?= base_url('admin/pelaporan/' . (int) $item['id'] . '/validasi') ?>" method="post" class="d-inline-flex m-0">
                                                    <?= csrf_field() ?>
                                                    <button class="btn btn-sm btn-outline-danger" type="submit" name="keputusan" value="Ditolak" aria-label="Tolak <?= esc($item['nama_dokumen']) ?>" title="Tolak">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form action="<?= base_url('admin/pelaporan/' . (int) $item['id'] . '/hapus') ?>" method="post" class="d-inline-flex m-0" onsubmit="return confirm('Hapus file dokumen ini? Checklist tetap tersedia dan pengawas dapat mengunggah ulang.');">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Hapus <?= esc($item['nama_dokumen']) ?>" title="Hapus file">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small">Belum ada file</span>
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

<?= $this->section('scripts') ?>
<script>
    const schoolFilter = document.getElementById('schoolFilter');
    const documentRows = [...document.querySelectorAll('[data-school-id]')];
    const documentCount = document.getElementById('documentCount');

    schoolFilter.addEventListener('change', () => {
        const selectedSchoolId = schoolFilter.value;
        let visibleCount = 0;

        documentRows.forEach((row) => {
            const matches = !selectedSchoolId || row.dataset.schoolId === selectedSchoolId;
            row.classList.toggle('d-none', !matches);
            visibleCount += matches ? 1 : 0;
        });

        documentCount.textContent = selectedSchoolId
            ? `${visibleCount} dari ${documentRows.length} dokumen`
            : `${documentRows.length} dokumen`;
    });
</script>
<?= $this->endSection() ?>
