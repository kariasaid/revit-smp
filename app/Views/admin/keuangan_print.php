<?php
$formatMoney = static fn ($amount): string => number_format((float) $amount, 2, ',', '.');
$formatDate = static function (?string $date): string {
    if (!$date) return '-';
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $time = strtotime($date);
    return date('j', $time) . ' ' . $months[(int) date('n', $time)] . ' ' . date('Y', $time);
};
$closingDateObject = new DateTimeImmutable($closingDate);
$numberFormatter = new NumberFormatter('id', NumberFormatter::SPELLOUT);
$spellNumber = static fn (int $number): string => mb_convert_case($numberFormatter->format($number), MB_CASE_TITLE, 'UTF-8');
$weekdayNames = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$closingStatement = sprintf(
    'Pada hari ini %s tanggal %s Bulan %s Tahun %s Buku Kas Umum ditutup dengan posisi:',
    $weekdayNames[(int) $closingDateObject->format('N')],
    $spellNumber((int) $closingDateObject->format('j')),
    $monthNames[(int) $closingDateObject->format('n')],
    $spellNumber((int) $closingDateObject->format('Y'))
);
$isLedger = $category['type'] === 'ledger';
$isGeneral = $categorySlug === 'buku-kas-umum';
$isBank = $categorySlug === 'buku-bank';
$isCash = $categorySlug === 'buku-kas-tunai';
$printRows = array_reverse($rows);
$receipts = array_values(array_filter($printRows, static fn (array $row): bool => (float) ($row['penerimaan'] ?? 0) > 0));
$expenses = array_values(array_filter($printRows, static fn (array $row): bool => (float) ($row['pengeluaran'] ?? 0) > 0));
$total = array_sum(array_map(static fn (array $row): float => (float) ($row['total'] ?? 0), $printRows));
$principal = $p2sp['penanggung_jawab'] ?? [];
$chair = $p2sp['ketua'] ?? [];
$treasurer = $p2sp['bendahara'] ?? [];
$principalName = $principal['nama'] ?? ($schoolPersonnel['kepala_sekolah'] ?? '................................');
$chairName = $chair['nama'] ?? '................................';
$treasurerName = $treasurer['nama'] ?? '................................';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($category['title']) ?> - <?= esc($school['nama_sekolah']) ?></title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; font-family: Arial, Helvetica, sans-serif; font-size: 9pt; }
        .toolbar { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 12px; }
        .toolbar button { border: 0; background: #1d5296; color: #fff; padding: 8px 12px; cursor: pointer; }
        h1 { text-align: center; font: bold 14pt 'Times New Roman', serif; margin: 0 0 4px; text-transform: uppercase; }
        .subtitle { text-align: center; font-size: 10pt; margin-bottom: 10px; }
        .period { text-align: center; margin-bottom: 8px; font-weight: 600; }
        .school-meta { width: 100%; border-collapse: collapse; margin: 8px 0 12px; }
        .school-meta td { padding: 2px 4px; vertical-align: top; }
        table.report { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .report th, .report td { border: 1px solid #222; padding: 4px 5px; vertical-align: top; overflow-wrap: anywhere; }
        .report th { text-align: center; background: #e8edf3; font-weight: 700; }
        .report .number, .report .money { text-align: right; white-space: nowrap; }
        .report .center { text-align: center; }
        .report .section-head { background: #dbe7f3; text-align: center; }
        .totals { font-weight: 700; background: #f2f4f7; }
        .summary { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .summary td { padding: 3px 5px; }
        .summary .amount { text-align: right; width: 25%; }
        .signature-date { text-align: right; margin: 18px 0 8px; }
        .signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; text-align: center; margin-top: 10px; }
        .signature { min-height: 105px; position: relative; }
        .signature-role { min-height: 32px; }
        .signature-mark { position: absolute; z-index: 0; top: 30px; left: 50%; transform: translateX(-50%); max-width: 135px; width: 75%; height: 55px; object-fit: contain; opacity: .3; }
        .signature-name { position: relative; z-index: 1; margin-top: 44px; text-decoration: underline; font-weight: 600; }
        .footer-note { margin-top: 8px; font-size: 8pt; text-align: center; }
        .material-table th:nth-child(1) { width: 7%; }
        .material-table th:nth-child(2) { width: 33%; }
        .material-table th:nth-child(3) { width: 12%; }
        .material-table th:nth-child(4) { width: 12%; }
        .material-table th:nth-child(5) { width: 18%; }
        .material-table th:nth-child(6) { width: 18%; }
        .labor-table th:nth-child(1) { width: 7%; }
        .labor-table th:nth-child(2) { width: 15%; }
        .labor-table th:nth-child(3) { width: 22%; }
        .labor-table th:nth-child(4) { width: 22%; }
        .labor-table th:nth-child(5) { width: 12%; }
        .labor-table th:nth-child(6) { width: 10%; }
        .labor-table th:nth-child(7) { width: 12%; }
        @media print { .toolbar { display: none; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()"><i>PDF</i> Cetak / Simpan sebagai PDF</button>
    </div>

    <?php if ($isGeneral): ?>
        <h1>Buku Kas Umum</h1>
        <div class="subtitle">Program Revitalisasi · <?= esc($school['nama_sekolah']) ?></div>
        <div class="period">Periode: <?= esc($periodLabel) ?></div>
        <table class="report">
            <thead>
                <tr><th class="section-head" colspan="4">PENERIMAAN</th><th class="section-head" colspan="6">PENGELUARAN</th></tr>
                <tr>
                    <th>Tanggal</th><th>Uraian</th><th>No Bukti</th><th>Jumlah (Rp)</th>
                    <th>Tanggal</th><th>Uraian</th><th>Jml (Rp)</th><th>No Bukti</th><th>Jenis Biaya</th><th>Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td><td>Saldo Awal</td><td></td><td class="money"><?= $formatMoney($openingBalance) ?></td>
                    <td colspan="6"></td>
                </tr>
                <?php for ($index = 0, $rowCount = max(count($receipts), count($expenses)); $index < $rowCount; $index++): ?>
                    <?php $receipt = $receipts[$index] ?? null; $expense = $expenses[$index] ?? null; ?>
                    <tr>
                        <?php if ($receipt): ?>
                            <td class="center"><?= esc($formatDate($receipt['tanggal'])) ?></td>
                            <td><?= esc($receipt['uraian']) ?></td>
                            <td><?= esc($receipt['nomor_bukti'] ?? '-') ?></td>
                            <td class="money"><?= $formatMoney($receipt['penerimaan']) ?></td>
                        <?php else: ?>
                            <td colspan="4"></td>
                        <?php endif; ?>
                        <?php if ($expense): ?>
                            <td class="center"><?= esc($formatDate($expense['tanggal'])) ?></td>
                            <td><?= esc($expense['uraian']) ?></td>
                            <td class="money"><?= $formatMoney($expense['pengeluaran']) ?></td>
                            <td><?= esc($expense['nomor_bukti'] ?? '-') ?></td>
                            <td><?= esc($expense['jenis_biaya'] ?? '-') ?></td>
                            <td class="money"><?= $formatMoney($expense['pengeluaran']) ?></td>
                        <?php else: ?>
                            <td colspan="6"></td>
                        <?php endif; ?>
                    </tr>
                <?php endfor; ?>
                <tr class="totals">
                    <td colspan="3">Total Penerimaan</td><td class="money"><?= $formatMoney($totalReceipts) ?></td>
                    <td colspan="5">Total Pengeluaran</td><td class="money"><?= $formatMoney($totalExpenses) ?></td>
                </tr>
                <tr class="totals"><td colspan="9">Saldo Kas Umum</td><td class="money"><?= $formatMoney($closingBalance) ?></td></tr>
            </tbody>
        </table>
        <table class="summary">
            <tr><td colspan="2"><?= esc($closingStatement) ?></td></tr>
            <tr><td>Saldo Buku Kas Umum</td><td class="amount">Rp <?= $formatMoney($closingBalance) ?></td></tr>
            <tr><td>1. Saldo Bank</td><td class="amount">Rp <?= $formatMoney($bankBalance) ?></td></tr>
            <tr><td>2. Saldo Kas Tunai</td><td class="amount">Rp <?= $formatMoney($cashBalance) ?></td></tr>
            <tr><td>Perbedaan</td><td class="amount">Rp <?= $formatMoney($closingBalance - $bankBalance - $cashBalance) ?></td></tr>
        </table>
    <?php elseif ($isLedger): ?>
        <h1><?= $isBank ? 'Buku Bank (BB)' : 'Buku Pembantu Kas Tunai' ?></h1>
        <div class="period">Bulan: <?= esc($periodLabel) ?></div>
        <table class="school-meta">
            <tr><td style="width:22%">Nama Sekolah</td><td style="width:3%">:</td><td><?= esc($school['nama_sekolah']) ?></td></tr>
            <tr><td>NPSN</td><td>:</td><td><?= esc($school['npsn']) ?></td></tr>
            <tr><td>Kabupaten</td><td>:</td><td><?= esc($school['kab_kota'] ?? '-') ?></td></tr>
            <tr><td>Provinsi</td><td>:</td><td><?= esc($school['provinsi'] ?? '-') ?></td></tr>
        </table>
        <table class="report">
            <thead>
                <tr><th style="width:6%">No</th><th style="width:14%">Tanggal</th><th>Uraian</th><th style="width:15%">No Bukti</th><th style="width:17%">Debet/Penerimaan (Rp.)</th><th style="width:17%">Kredit/Pengeluaran (Rp.)</th><th style="width:17%">Saldo (Rp.)</th></tr>
            </thead>
            <tbody>
                <?php foreach ($printRows as $index => $row): ?>
                    <tr>
                        <td class="center"><?= $index + 1 ?></td>
                        <td class="center"><?= esc($formatDate($row['tanggal'])) ?></td>
                        <td><?= esc($row['uraian']) ?></td>
                        <td><?= esc($row['nomor_bukti'] ?? '-') ?></td>
                        <td class="money"><?= (float) $row['penerimaan'] > 0 ? $formatMoney($row['penerimaan']) : '-' ?></td>
                        <td class="money"><?= (float) $row['pengeluaran'] > 0 ? $formatMoney($row['pengeluaran']) : '-' ?></td>
                        <td class="money"><?= $formatMoney($row['saldo']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="totals">
                    <td colspan="4" class="center">Jumlah</td>
                    <td class="money"><?= $formatMoney($totalReceipts) ?></td>
                    <td class="money"><?= $formatMoney($totalExpenses) ?></td>
                    <td class="money"><?= $formatMoney($closingBalance) ?></td>
                </tr>
            </tbody>
        </table>
    <?php elseif ($isMaterials): ?>
        <h1>Buku Bahan Bangunan</h1>
        <div class="subtitle"><?= esc($school['nama_sekolah']) ?> · NPSN <?= esc($school['npsn']) ?></div>
        <div class="period">Periode: <?= esc($periodLabel) ?></div>
        <table class="report material-table">
            <thead><tr><th>No</th><th>Nama Bahan</th><th>Volume</th><th>Satuan</th><th>Harga Satuan (Rp)</th><th>Total (Rp)</th></tr></thead>
            <tbody>
                <?php foreach ($printRows as $index => $row): ?>
                    <tr><td class="center"><?= $index + 1 ?></td><td><?= esc($row['nama_bahan']) ?></td><td class="number"><?= $formatMoney($row['volume']) ?></td><td><?= esc($row['satuan']) ?></td><td class="money"><?= $formatMoney($row['harga_satuan']) ?></td><td class="money"><?= $formatMoney($row['total']) ?></td></tr>
                <?php endforeach; ?>
                <tr class="totals"><td colspan="5">Jumlah</td><td class="money">Rp <?= $formatMoney($total) ?></td></tr>
            </tbody>
        </table>
    <?php else: ?>
        <h1>Buku Ongkos Tukang</h1>
        <div class="subtitle"><?= esc($school['nama_sekolah']) ?> · NPSN <?= esc($school['npsn']) ?></div>
        <div class="period">Periode: <?= esc($periodLabel) ?></div>
        <table class="report labor-table">
            <thead><tr><th>No</th><th>Tanggal</th><th>Pekerjaan</th><th>Penerima</th><th>Volume</th><th>Tarif (Rp)</th><th>Total (Rp)</th></tr></thead>
            <tbody>
                <?php foreach ($printRows as $index => $row): ?>
                    <tr><td class="center"><?= $index + 1 ?></td><td class="center"><?= esc($formatDate($row['tanggal'])) ?></td><td><?= esc($row['pekerjaan']) ?></td><td><?= esc($row['penerima']) ?></td><td class="number"><?= $formatMoney($row['volume']) ?> <?= esc($row['satuan']) ?></td><td class="money"><?= $formatMoney($row['tarif']) ?></td><td class="money"><?= $formatMoney($row['total']) ?></td></tr>
                <?php endforeach; ?>
                <tr class="totals"><td colspan="6">Jumlah</td><td class="money">Rp <?= $formatMoney($total) ?></td></tr>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($isLedger || $isGeneral): ?>
        <div class="signature-date"><?= esc($school['kab_kota'] ?? 'Tempat') ?>, <?= esc($formatDate($closingDate)) ?></div>
        <div class="signatures">
            <?php foreach ([['Kepala Satuan Pendidikan', $principalName, $principal['ttd'] ?? null], ['Ketua P2SP', $chairName, $chair['ttd'] ?? null], ['Bendahara', $treasurerName, $treasurer['ttd'] ?? null]] as [$role, $name, $signature]): ?>
                <div class="signature">
                    <?php if ($signature): ?><img class="signature-mark" src="<?= base_url($signature) ?>" alt=""><?php endif; ?>
                    <div class="signature-role"><?= esc($role) ?></div>
                    <div class="signature-name"><?= esc($name) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="footer-note">Dicetak <?= esc($formatDate(date('Y-m-d'))) ?> · Sistem Pengarsipa Dokumen 1.1.0</div>
    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
