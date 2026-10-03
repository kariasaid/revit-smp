<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <div class="small text-muted mb-1">Administrasi / Sekolah</div>
        <h1 class="h4 fw-bold mb-1"><?= esc($school['nama_sekolah']) ?></h1>
        <div class="text-muted">NPSN <?= esc($school['npsn']) ?> · <?= esc($school['kab_kota']) ?>, <?= esc($school['provinsi']) ?></div>
    </div>
    <a href="<?= base_url('admin/sekolah') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card h-100"><div class="card-body"><div class="small text-muted">Durasi pekerjaan</div><div class="h5 mb-0"><?= (int) $school['total_minggu'] ?> minggu</div></div></div></div>
    <div class="col-md-4"><div class="card h-100"><div class="card-body"><div class="small text-muted">Perencana</div><div class="fw-semibold"><?= esc($school['perencana'] ?? '-') ?></div></div></div></div>
    <div class="col-md-4"><div class="card h-100"><div class="card-body"><div class="small text-muted">Pengawas</div><div class="fw-semibold"><?= esc($school['pengawas'] ?? '-') ?></div></div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <div><strong><i class="bi bi-images text-primary me-2"></i>Jenis bantuan, volume, dan foto awal bangunan</strong><div class="small text-muted">Volume ditampilkan di dokumentasi progres mingguan. Foto awal disimpan sebagai kondisi 0%.</div></div>
        <span class="badge text-bg-light border"><?= count($assistance) ?> jenis bantuan</span>
    </div>
    <div class="card-body">
        <form action="<?= base_url('admin/sekolah/' . $school['id'] . '/bantuan') ?>" method="post" class="row g-2 align-items-end mb-4">
            <?= csrf_field() ?>
            <div class="col-md-5"><label class="form-label" for="nama_bantuan">Tambah jenis bantuan</label><input class="form-control" id="nama_bantuan" name="nama_bantuan" maxlength="150" required></div>
            <div class="col-md-3"><label class="form-label" for="volume_bantuan">Volume</label><input class="form-control" id="volume_bantuan" name="volume" type="number" min="0.01" step="0.01" placeholder="Contoh: 1" required></div>
            <div class="col-md-2"><label class="form-label" for="satuan_volume_bantuan">Satuan</label><input class="form-control" id="satuan_volume_bantuan" name="satuan_volume" maxlength="30" placeholder="m², unit" required></div>
            <div class="col-md-2"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-plus-lg me-1"></i>Tambah</button></div>
        </form>

        <?php if ($assistance === []): ?>
            <div class="text-center text-muted py-4">Belum ada jenis bantuan untuk sekolah ini.</div>
        <?php else: ?>
            <div class="vstack gap-4">
                <?php foreach ($assistance as $item): ?>
                    <form action="<?= base_url('admin/bantuan/' . $item['id'] . '/update') ?>" method="post" enctype="multipart/form-data" class="border rounded p-3">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <label class="form-label" for="bantuan-<?= (int) $item['id'] ?>">Nama jenis bantuan</label>
                                <input class="form-control" id="bantuan-<?= (int) $item['id'] ?>" name="nama_bantuan" maxlength="150" value="<?= esc($item['nama_bantuan']) ?>" required>
                                <label class="form-label mt-2" for="volume-<?= (int) $item['id'] ?>">Volume</label>
                                <input class="form-control" id="volume-<?= (int) $item['id'] ?>" name="volume" type="number" min="0.01" step="0.01" value="<?= esc($item['volume'] ?? '') ?>" required>
                                <label class="form-label mt-2" for="satuan-volume-<?= (int) $item['id'] ?>">Satuan volume</label>
                                <input class="form-control" id="satuan-volume-<?= (int) $item['id'] ?>" name="satuan_volume" maxlength="30" value="<?= esc($item['satuan_volume'] ?? '') ?>" placeholder="m², m³, unit, paket" required>
                                <div class="d-flex gap-2 mt-3">
                                    <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-save me-1"></i>Simpan perubahan</button>
                                    <button class="btn btn-outline-danger btn-sm" type="submit" formaction="<?= base_url('admin/bantuan/' . $item['id'] . '/hapus') ?>" formmethod="post" onclick="return confirm('Hapus jenis bantuan ini beserta foto awalnya?')"><i class="bi bi-trash me-1"></i>Hapus</button>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="row g-3">
                                    <?php foreach (['depan' => 'Tampak depan', 'belakang' => 'Tampak belakang', 'dalam' => 'Tampak dalam'] as $angle => $label): ?>
                                        <?php $field = 'foto_0_' . $angle; ?>
                                        <div class="col-md-4">
                                            <div class="small fw-semibold mb-2"><?= esc($label) ?></div>
                                            <?php if (!empty($item[$field])): ?><a href="<?= base_url($item[$field]) ?>" target="_blank" rel="noopener"><img src="<?= base_url($item[$field]) ?>" alt="<?= esc($label) ?>" class="baseline-preview img-thumbnail w-100 mb-2" style="height: 130px; object-fit: cover;"></a><?php else: ?><div class="baseline-preview border rounded text-muted small d-flex align-items-center justify-content-center mb-2" style="height: 130px;">Belum ada foto</div><?php endif; ?>
                                            <label class="form-label small text-muted" for="<?= $field . '-' . (int) $item['id'] ?>">Ganti foto</label>
                                            <input class="form-control form-control-sm" id="<?= $field . '-' . (int) $item['id'] ?>" type="file" name="<?= $field ?>" accept="image/jpeg,image/png,image/webp">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="small text-muted mt-2">Format JPG, PNG, atau WebP. Maksimal 12 MB per file.</div>
                            </div>
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    document.querySelectorAll('input[type="file"][name^="foto_0_"]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;

            const column = input.closest('.col-md-4');
            const currentPreview = column.querySelector('.baseline-preview');
            const preview = document.createElement('img');
            preview.className = 'baseline-preview img-thumbnail w-100 mb-2';
            preview.style.height = '130px';
            preview.style.objectFit = 'cover';
            preview.alt = 'Pratinjau foto yang dipilih';
            preview.src = URL.createObjectURL(file);

            if (currentPreview.tagName === 'IMG') {
                currentPreview.src = preview.src;
            } else {
                currentPreview.replaceWith(preview);
            }
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>