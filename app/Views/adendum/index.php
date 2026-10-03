<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$statusBadge = function ($s) {
    $map = [
        'Draft'     => 'bg-secondary-subtle text-secondary',
        'Diajukan'  => 'bg-info-subtle text-info',
        'Disetujui' => 'bg-success-subtle text-success',
        'Ditolak'   => 'bg-danger-subtle text-danger',
    ];
    $cls = $map[$s] ?? 'bg-light text-dark';
    return '<span class="badge ' . $cls . '">' . esc($s) . '</span>';
};
$fmt = fn($n) => number_format((float)$n, 0, ',', '.');
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h5 class="mb-0 fw-bold">Adendum</h5>
        <div class="small text-muted"><?= esc($sekolah['nama_sekolah'] ?? '-') ?> · NPSN: <?= esc($sekolah['npsn'] ?? '-') ?></div>
    </div>
    <a href="<?= base_url('adendum/form') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Adendum
    </a>
</div>

<!-- Filter Sekolah -->
<div class="card mb-4">
    <div class="card-body py-3">
        <div class="row align-items-end g-3">
            <div class="col-md-6">
                <label class="form-label small text-muted mb-1">Sekolah</label>
                <select class="form-select" onchange="window.location.href='<?= base_url('adendum') ?>/'+this.value">
                    <?php foreach ($sekolahList as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($sekolah['id'] ?? 0) == $s['id'] ? 'selected' : '' ?>>
                            <?= esc($s['nama_sekolah']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-diff text-primary"></i>
        <span>Daftar Adendum</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>NOMOR ADENDUM</th>
                        <th>TANGGAL</th>
                        <th>PERIHAL</th>
                        <th>NILAI PERUBAHAN</th>
                        <th>STATUS</th>
                        <th>FILE</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($adendum)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada data adendum
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($adendum as $i => $a): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-semibold"><?= esc($a['nomor_adendum']) ?></td>
                            <td><?= date('d/m/Y', strtotime($a['tanggal'])) ?></td>
                            <td>
                                <div class="fw-medium"><?= esc($a['perihal']) ?></div>
                                <?php if (!empty($a['uraian'])): ?>
                                    <div class="small text-muted text-truncate" style="max-width:220px;"><?= esc($a['uraian']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="<?= (float)$a['nilai_perubahan'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= ((float)$a['nilai_perubahan'] >= 0 ? '+' : '') . $fmt($a['nilai_perubahan']) ?>
                            </td>
                            <td><?= $statusBadge($a['status']) ?></td>
                            <td>
                                <?php if (!empty($a['file_adendum'])): ?>
                                    <a href="<?= base_url($a['file_adendum']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('adendum/form/' . $a['id']) ?>" class="btn btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= base_url('adendum/hapus/' . $a['id']) ?>" class="btn btn-outline-danger" title="Hapus"
                                       onclick="return confirm('Hapus adendum ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
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
