<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isLedger = $category['type'] === 'ledger';
$isMaterials = $category['type'] === 'materials';
$isUpload = $isMaterials || $categorySlug === 'ongkos-tukang';
$isGeneralCashBook = $categorySlug === 'buku-kas-umum';
$isBankBook = $categorySlug === 'buku-bank';
$receiptLabel = $isBankBook ? 'Debit' : 'Penerimaan';
$expenseLabel = $isBankBook ? 'Kredit' : 'Pengeluaran';
$monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$formatMoney = static fn ($amount): string => number_format((float) $amount, 2, ',', '.');
$formatDecimal = static fn ($amount): string => number_format((float) $amount, 2, '.', '');
$basePath = 'admin/keuangan/' . $categorySlug;
$exportQuery = ['sekolah_id' => (int) $school['id']];
if ($selectedYear !== '') $exportQuery['tahun'] = $selectedYear;
if ($selectedMonth !== '') $exportQuery['bulan'] = $selectedMonth;
$exportUrl = base_url($basePath . '/cetak') . '?' . http_build_query($exportQuery);
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <div class="small text-muted mb-1">Administrasi / Keuangan</div>
        <h1 class="h4 fw-bold mb-1"><?= esc($category['title']) ?></h1>
        <div class="text-muted"><?= esc($school['nama_sekolah']) ?> · NPSN <?= esc($school['npsn']) ?></div>
    </div>
    <a href="<?= esc($exportUrl) ?>" class="btn btn-outline-primary" target="_blank" rel="noopener">
        <i class="bi bi-file-earmark-pdf me-1"></i>Ekspor PDF
    </a>
</div>

<form action="<?= base_url($basePath) ?>" method="get" class="row g-3 mb-3">
    <div class="col-12 col-md-5">
        <label class="form-label" for="school_id">Pilih sekolah</label>
        <select class="form-select" id="school_id" name="sekolah_id" onchange="this.form.submit()">
            <?php foreach ($schools as $listedSchool): ?>
                <option value="<?= (int) $listedSchool['id'] ?>" <?= (int) $listedSchool['id'] === (int) $school['id'] ? 'selected' : '' ?>>
                    <?= esc($listedSchool['nama_sekolah']) ?> · NPSN <?= esc($listedSchool['npsn']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label" for="filter-year">Tahun</label>
        <select class="form-select" id="filter-year" name="tahun" onchange="this.form.submit()">
            <option value="">Semua tahun</option>
            <?php foreach ($yearOptions as $year): ?>
                <option value="<?= (int) $year ?>" <?= (string) $year === $selectedYear ? 'selected' : '' ?>><?= (int) $year ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label" for="filter-month">Bulan</label>
        <select class="form-select" id="filter-month" name="bulan" onchange="this.form.submit()">
            <option value="">Semua bulan</option>
            <?php foreach ($monthNames as $monthNumber => $monthName): ?>
                <option value="<?= $monthNumber ?>" <?= (string) $monthNumber === $selectedMonth ? 'selected' : '' ?>><?= esc($monthName) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<div class="card mb-4">
    <div class="card-header py-3"><strong><i class="bi bi-plus-circle text-primary me-2"></i>Tambah catatan</strong></div>
    <div class="card-body">
            <form action="<?= base_url($basePath . ($isUpload || $categorySlug === 'ongkos-tukang' ? '/upload' : '/simpan')) ?>" 
                method="post" <?= ($isUpload || $categorySlug === 'ongkos-tukang') ? 'enctype="multipart/form-data"' : '' ?> 
                class="row g-3 align-items-end">
            <?= csrf_field() ?>
            <input type="hidden" name="sekolah_id" value="<?= (int) $school['id'] ?>">
            <input type="hidden" name="tahun" value="<?= esc($selectedYear) ?>">
            <input type="hidden" name="bulan" value="<?= esc($selectedMonth) ?>">
            <?php if ($isGeneralCashBook): ?>
                <div class="col-12 col-lg-6">
                    <label class="form-label" for="source_transaction_id">Pilih transaksi dari Buku Bank atau Buku Kas Tunai</label>
                    <select class="form-select" id="source_transaction_id" name="source_transaction_id" <?= $sourceTransactions === [] ? 'disabled' : 'required' ?>>
                        <option value=""><?= $sourceTransactions === [] ? 'Belum ada transaksi sumber yang tersedia' : 'Pilih transaksi' ?></option>
                        <?php foreach ($sourceTransactions as $source): ?>
                            <?php $sourceLabel = $source['jenis_buku'] === 'bank' ? 'Buku Bank' : 'Buku Kas Tunai'; ?>
                            <?php
                            if ($source['jenis_buku'] === 'bank') {
                                $sourceAmountLabel = (float) $source['penerimaan'] > 0
                                    ? 'Debit Rp ' . $formatMoney($source['penerimaan'])
                                    : 'Kredit Rp ' . $formatMoney($source['pengeluaran']);
                            } else {
                                $sourceAmountLabel = (float) $source['penerimaan'] > 0
                                    ? 'Penerimaan Rp ' . $formatMoney($source['penerimaan'])
                                    : 'Pengeluaran Rp ' . $formatMoney($source['pengeluaran']);
                            }
                            ?>
                            <option value="<?= (int) $source['id'] ?>">
                                <?= esc($source['tanggal']) ?> · <?= esc($sourceLabel) ?> · <?= esc($source['nomor_bukti'] ?: 'Tanpa nomor bukti') ?> · <?= esc($source['uraian']) ?> · <?= esc($sourceAmountLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Tanggal, nomor bukti, uraian, dan nominal akan disalin dari transaksi sumber.</div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label" for="jenis_biaya">Jenis biaya</label>
                    <select class="form-select" id="jenis_biaya" name="jenis_biaya">
                        <option value="">Pilih jenis biaya</option>
                        <?php foreach ($generalExpenseTypes as $expenseType): ?>
                            <option value="<?= esc($expenseType) ?>"><?= esc($expenseType) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Wajib dipilih untuk transaksi pengeluaran.</div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit" <?= $sourceTransactions === [] ? 'disabled' : '' ?>><i class="bi bi-arrow-down-square me-1"></i>Input ke Buku Kas Umum</button>
                </div>
            <?php else: ?>
                <?php if (!$isUpload): ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label" for="new-tanggal">Tanggal</label>
                        <input class="form-control" type="date" id="new-tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>
                <?php endif; ?>
            <?php if ($isLedger): ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label" for="new-nomor-bukti">Nomor bukti</label>
                    <input class="form-control" id="new-nomor-bukti" name="nomor_bukti" maxlength="80">
                </div>
                <div class="col-12 col-lg-6">
                    <label class="form-label" for="new-uraian">Uraian</label>
                    <input class="form-control" id="new-uraian" name="uraian" maxlength="250" required>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label" for="new-penerimaan"><?= esc($receiptLabel) ?> (Rp)</label>
                    <input class="form-control" type="number" id="new-penerimaan" name="penerimaan" min="0" step="0.01" value="0" required>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label" for="new-pengeluaran"><?= esc($expenseLabel) ?> (Rp)</label>
                    <input class="form-control" type="number" id="new-pengeluaran" name="pengeluaran" min="0" step="0.01" value="0" required>
                </div>
                <?php if ($isBankBook): ?>
                    <div class="col-12 small text-muted">Debit menambah saldo bank; kredit mengurangi saldo bank.</div>
                <?php endif; ?>
            <?php elseif ($isUpload): ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label" for="new-tanggal-kuitansi">Tanggal Kuitansi</label>
                    <input type="date" class="form-control" id="new-tanggal-kuitansi" name="tanggal_kuitansi" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label" for="new-file">File SPJ Bahan Bangunan</label>
                    <input type="file" class="form-control" id="new-file" name="file_pdf" accept=".pdf" required>
                </div>

                <div class="col-12 col-sm-6 col-lg-5">
                    <label class="form-label" for="new-keterangan">Keterangan</label>
                    <input type="text" class="form-control" id="new-keterangan" name="keterangan" placeholder="Contoh: SPJ Pesanan Bahan Bangunan ke-1" required>
                </div>
            <?php else: ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label" for="new-pekerjaan">Pekerjaan</label>
                    <input class="form-control" id="new-pekerjaan" name="pekerjaan" maxlength="200" required>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label" for="new-penerima">Penerima</label>
                    <input class="form-control" id="new-penerima" name="penerima" maxlength="150" required>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="new-volume">Volume</label>
                    <input class="form-control" type="number" id="new-volume" name="volume" min="0.01" step="0.01" required>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label" for="new-satuan">Satuan</label>
                    <input class="form-control" id="new-satuan" name="satuan" maxlength="30" placeholder="hari, orang" required>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label" for="new-tarif">Tarif (Rp)</label>
                    <input class="form-control" type="number" id="new-tarif" name="tarif" min="0" step="0.01" required>
                </div>
            <?php endif; ?>
            <div class="col-12">
                <button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg me-1"></i>Simpan catatan</button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <strong><?= esc($category['title']) ?> · <?= esc($school['nama_sekolah']) ?></strong>
        <span class="badge text-bg-light border"><?= count($rows) ?> catatan</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <?php if ($isLedger): ?>
                            <?php if ($isGeneralCashBook): ?><th>Sumber</th><?php endif; ?>
                            <th class="ps-3">Tanggal</th>
                            <th>No. bukti</th>
                            <th>Uraian</th>
                            <?php if ($isGeneralCashBook): ?><th>Jenis Biaya</th><?php endif; ?>
                            <th><?= esc($receiptLabel) ?></th>
                            <th><?= esc($expenseLabel) ?></th>
                            <th>Saldo</th>
                            <th class="text-end pe-3">Aksi</th>
                        <?php elseif ($isUpload): ?>
                            <th>No</th>
                            <th>Tanggal Kuitansi</th>
                            <th>Keterangan</th>
                            <th>File SPJ</th>
                            <th class="text-end pe-3">Aksi</th>
                        <?php else: ?>
                            <th>Pekerjaan</th>
                            <th>Penerima</th>
                            <th>Volume</th>
                            <th>Tarif</th>
                            <th>Total</th>
                            <th class="text-end pe-3">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []): ?>
                        <tr>
                            <td colspan="<?= $isGeneralCashBook ? 9 : ($isLedger ? 8 : ($isUpload ? 5 : 6)) ?>" class="text-center text-muted py-5">
                                Belum ada catatan untuk sekolah ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 0; ?>
                        <?php foreach ($rows as $row): ?>
                            <?php $updateFormId = 'update-record-' . (int) $row['id']; ?>
                            <tr>
                                <?php if ($isGeneralCashBook): ?>
                                    <td class="ps-3"><?= esc($row['tanggal']) ?></td>
                                    <td><?= ($row['sumber_jenis'] ?? '') === 'bank' ? 'Buku Bank' : 'Buku Kas Tunai' ?></td>
                                    <td><?= esc($row['nomor_bukti'] ?? '-') ?></td>
                                    <td><?= esc($row['uraian']) ?></td>
                                    <td><?= esc($row['jenis_biaya'] ?? '-') ?></td>
                                    <td class="text-nowrap">Rp <?= $formatMoney($row['penerimaan']) ?></td>
                                    <td class="text-nowrap">Rp <?= $formatMoney($row['pengeluaran']) ?></td>
                                    <td class="text-nowrap">Rp <?= $formatMoney($row['saldo']) ?></td>
                                <?php elseif ($isLedger): ?>
                                    <td class="ps-3">
                                        <input class="form-control form-control-sm" type="date" name="tanggal" value="<?= esc($row['tanggal']) ?>" form="<?= esc($updateFormId) ?>" required>
                                    </td>
                                    <td>
                                        <input class="form-control form-control-sm" name="nomor_bukti" maxlength="80" value="<?= esc($row['nomor_bukti'] ?? '') ?>" form="<?= esc($updateFormId) ?>">
                                    </td>
                                    <td>
                                        <input class="form-control form-control-sm" name="uraian" maxlength="250" value="<?= esc($row['uraian']) ?>" form="<?= esc($updateFormId) ?>" required>
                                    </td>
                                    <td>
                                        <input class="form-control form-control-sm" type="number" name="penerimaan" min="0" step="0.01" value="<?= esc($formatDecimal($row['penerimaan'])) ?>" form="<?= esc($updateFormId) ?>" required>
                                    </td>
                                    <td>
                                        <input class="form-control form-control-sm" type="number" name="pengeluaran" min="0" step="0.01" value="<?= esc($formatDecimal($row['pengeluaran'])) ?>" form="<?= esc($updateFormId) ?>" required>
                                    </td>
                                    <td class="text-nowrap">Rp <?= $formatMoney($row['saldo']) ?></td>
                                <?php elseif ($isUpload): ?>
                                    <td><?= ++$no ?></td>
                                    <td><?= esc(date('d/m/Y', strtotime($row['tanggal'] ?? $row['tanggal_kuitansi'] ?? ''))) ?></td>
                                    <td><?= esc($row['keterangan'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($row['file_pdf'])): ?>
                                            <a href="<?= base_url($basePath . '/dokumen/' . (int) $row['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                Lihat PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php else: ?>
                                    <td>
                                        <input class="form-control form-control-sm" name="pekerjaan" maxlength="200" value="<?= esc($row['pekerjaan']) ?>" form="<?= esc($updateFormId) ?>" required>
                                    </td>
                                    <td>
                                        <input class="form-control form-control-sm" name="penerima" maxlength="150" value="<?= esc($row['penerima']) ?>" form="<?= esc($updateFormId) ?>" required>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <input class="form-control form-control-sm" type="number" name="volume" min="0.01" step="0.01" value="<?= esc($formatDecimal($row['volume'])) ?>" form="<?= esc($updateFormId) ?>" required>
                                            <input class="form-control form-control-sm" name="satuan" maxlength="30" value="<?= esc($row['satuan']) ?>" form="<?= esc($updateFormId) ?>" required>
                                        </div>
                                    </td>
                                    <td>
                                        <input class="form-control form-control-sm" type="number" name="tarif" min="0" step="0.01" value="<?= esc($formatDecimal($row['tarif'])) ?>" form="<?= esc($updateFormId) ?>" required>
                                    </td>
                                    <td class="text-nowrap">Rp <?= $formatMoney($row['total']) ?></td>
                                <?php endif; ?>

                                <td class="text-end pe-3 text-nowrap">
                                    <?php if ($isUpload): ?>
                                        <!-- Tombol hapus khusus dokumen -->
                                        <form action="<?= base_url($basePath . '/dokumen/' . (int) $row['id'] . '/hapus') ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus catatan ini?');">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <?php if (!$isGeneralCashBook): ?>
                                            <form id="<?= esc($updateFormId) ?>" action="<?= base_url($basePath . '/' . (int) $row['id'] . '/update') ?>" method="post">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="tahun" value="<?= esc($selectedYear) ?>">
                                                <input type="hidden" name="bulan" value="<?= esc($selectedMonth) ?>">
                                            </form>
                                            <button class="btn btn-sm btn-outline-primary" type="submit" form="<?= esc($updateFormId) ?>" title="Simpan perubahan">
                                                <i class="bi bi-save"></i>
                                            </button>
                                        <?php endif; ?>
                                        <form action="<?= base_url($basePath . '/' . (int) $row['id'] . '/hapus') ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus catatan ini?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="tahun" value="<?= esc($selectedYear) ?>">
                                            <input type="hidden" name="bulan" value="<?= esc($selectedMonth) ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
