<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <div class="small text-muted mb-1">Administrasi / Verifikasi</div>
    <h1 class="h4 fw-bold mb-1">Verifikasi Time Schedule</h1>
    <div class="text-muted">Periksa rencana mingguan sebelum menjadi acuan laporan progres.</div>
</div>

<?php if (empty($schedules)): ?>
    <div class="card"><div class="card-body text-center text-muted py-5">Tidak ada time schedule yang menunggu verifikasi.</div></div>
<?php else: ?>
    <?php foreach ($schedules as $schedule): ?>
        <section class="card mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3 py-3">
                <div>
                    <h2 class="h6 fw-bold mb-1"><?= esc($schedule['nama_sekolah']) ?></h2>
                    <div class="small text-muted">NPSN <?= esc($schedule['npsn']) ?> · Perencana: <?= esc($schedule['perencana'] ?? '-') ?> · Pengawas: <?= esc($schedule['pengawas'] ?? '-') ?></div>
                    <div class="small text-muted">Diajukan oleh <?= esc($schedule['nama_pengaju'] ?? $schedule['perencana'] ?? '-') ?></div>
                </div>
                <div class="text-end">
                    <span class="badge text-bg-warning">Menunggu verifikasi</span>
                    <div class="small text-muted mt-1"><?= (int) $schedule['total_baris'] ?>/<?= (int) $schedule['total_minggu'] ?> minggu · Total <?= number_format((float) $schedule['target_total'], 2, ',', '.') ?>%</div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr><th class="ps-3">Minggu</th><th>Periode</th><th>Target</th><th>Rencana pekerjaan</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedule['minggu'] as $week): ?>
                                <tr>
                                    <td class="ps-3">Minggu <?= (int) $week['minggu_ke'] ?></td>
                                    <td><?= esc(date('d/m/Y', strtotime($week['tanggal_mulai']))) ?> s.d. <?= esc(date('d/m/Y', strtotime($week['tanggal_selesai']))) ?></td>
                                    <td><?= number_format((float) $week['target_rencana'], 2, ',', '.') ?>%</td>
                                    <td><?= esc($week['keterangan'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <form action="<?= base_url('admin/verifikasi-time-schedule/' . $schedule['sekolah_id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <label for="catatan_<?= (int) $schedule['sekolah_id'] ?>" class="form-label">Catatan verifikasi <span class="text-muted fw-normal">(wajib jika ditolak)</span></label>
                    <textarea class="form-control mb-3" id="catatan_<?= (int) $schedule['sekolah_id'] ?>" name="catatan_verifikasi" rows="2" maxlength="2000" placeholder="Catatan untuk perencana"></textarea>
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-outline-danger" type="submit" name="keputusan" value="Ditolak"><i class="bi bi-arrow-return-left me-1"></i> Tolak untuk revisi</button>
                        <button class="btn btn-success" type="submit" name="keputusan" value="Diterima"><i class="bi bi-check2 me-1"></i> Terima jadwal</button>
                    </div>
                </form>
            </div>
        </section>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
