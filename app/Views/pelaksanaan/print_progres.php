<?php
$formatDate = static function (?string $date): string {
    if (!$date) {
        return '-';
    }
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $time = strtotime($date);
    return date('j', $time) . ' ' . $months[(int) date('n', $time)] . ' ' . date('Y', $time);
};
$period = $rencana
    ? $formatDate($rencana['tanggal_mulai']) . ' s/d ' . $formatDate($rencana['tanggal_selesai'])
    : '-';
$imageFields = [
    'foto_depan' => 'Tampak depan',
    'foto_belakang' => 'Tampak belakang',
    'foto_dalam' => 'Tampak dalam bangunan',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dokumentasi Mingguan - <?= esc($sekolah['nama_sekolah'] ?? '') ?></title>
    <style>
        @page { size: A4 landscape; margin: 12mm 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; font-family: Arial, Helvetica, sans-serif; font-size: 10pt; }
        .print-toolbar { padding: 10px; text-align: right; }
        .print-toolbar button { border: 0; background: #1d4ed8; color: #fff; padding: 8px 14px; cursor: pointer; }
        .title { text-align: center; font: bold 15pt Georgia, 'Times New Roman', serif; margin: 2px 0 12px; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .meta td { padding: 2px 4px; vertical-align: top; }
        .meta .label { width: 20%; }
        .meta .value { width: 30%; }
        .meta .right-label { width: 17%; }
        .meta .right-value { width: 33%; }
        .report { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .report th, .report td { border: 1.4px solid #111; }
        .report th { background: #cfe2f3; text-align: center; font-size: 10pt; padding: 6px 4px; }
        .report td { vertical-align: top; }
        .report .menu { width: 16%; padding: 7px 5px; font-size: 10pt; }
        .report .volume { width: 7%; text-align: center; padding-top: 7px; }
        .report .ket { width: 9%; padding: 7px 5px; text-align: center; }
        .report .photos { width: 34%; padding: 5px; }
        .photo-grid { display: grid; grid-template-columns: 1fr; gap: 5px; }
        .photo { text-align: center; page-break-inside: avoid; }
        .photo img { display: block; width: 5cm; height: 5cm; margin: 0 auto; object-fit: cover; border: 0; }
        .photo .caption { font-size: 7pt; color: #333; padding-top: 2px; }
        .empty { width: 5cm; height: 5cm; margin: 0 auto; display: grid; place-items: center; color: #777; font-size: 8pt; }
        .work-row { page-break-inside: avoid; }
        .signature-page { page-break-before: always; break-before: page; }
        .sign-date { text-align: right; margin-top: 14px; }
        .signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 12px; text-align: center; }
        .signature { min-height: 100px; position: relative; }
        .signature .role { min-height: 32px; }
        .signature .signature-mark { position: absolute; z-index: 0; top: 35px; left: 50%; transform: translateX(-50%); width: 150px; height: 58px; object-fit: contain; opacity: .32; }
        .signature .role, .signature .name { position: relative; z-index: 1; }
        .signature .name { margin-top: 35px; text-decoration: underline; }
        .note { margin-top: 8px; font-size: 8pt; color: #555; }
        @media print {
            .print-toolbar { display: none; }
            .title { margin-top: 0; }
        }
    </style>
</head>
<body>
    <div class="print-toolbar"><button type="button" onclick="window.print()">Cetak / Simpan sebagai PDF</button></div>
    <h1 class="title">DOKUMENTASI PROGRESS KEMAJUAN PEKERJAAN MINGGUAN</h1>

    <table class="meta">
        <tr>
            <td class="label">Nama Satuan Pendidikan</td><td class="value">: <?= esc($sekolah['nama_sekolah'] ?? '-') ?></td>
            <td class="right-label">Minggu/Bulan Ke</td><td class="right-value">: <?= (int) $progres['minggu_ke'] ?>/<?= (int) ceil((int) $progres['minggu_ke'] / 4) ?></td>
        </tr>
        <tr>
            <td class="label">NPSN</td><td class="value">: <?= esc($sekolah['npsn'] ?? '-') ?></td>
            <td class="right-label">Periode Tanggal</td><td class="right-value">: <?= esc($period) ?></td>
        </tr>
        <tr>
            <td class="label">Kab/Kota</td><td class="value">: <?= esc($sekolah['kab_kota'] ?? '-') ?></td>
            <td class="right-label">Realisasi Kumulatif</td><td class="right-value">: <?= number_format((float) $realisasiKumulatif, 2, ',', '.') ?>%</td>
        </tr>
        <tr>
            <td class="label">Provinsi</td><td class="value">: <?= esc($sekolah['provinsi'] ?? '-') ?></td>
            <td class="right-label"></td><td class="right-value"></td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th class="menu">Menu</th>
                <th class="volume">Volume</th>
                <th class="ket">Ket</th>
                <th class="photos">Foto 0 %</th>
                <th class="photos">Foto Progres <?= number_format((float) $realisasiKumulatif, 2, ',', '.') ?>%</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($fotoPekerjaan !== []): ?>
            <?php foreach ($fotoPekerjaan as $foto): ?>
                <?php $baselineFields = ['foto_0_depan' => 'Tampak depan', 'foto_0_belakang' => 'Tampak belakang', 'foto_0_dalam' => 'Tampak dalam bangunan']; ?>
                <?php foreach (array_keys($imageFields) as $photoIndex => $field): ?>
                    <?php $baselineField = array_keys($baselineFields)[$photoIndex]; ?>
                    <tr class="work-row">
                        <?php if ($photoIndex === 0): ?>
                            <td class="menu" rowspan="3"><?= esc($foto['nama_bantuan']) ?></td>
                            <td class="volume" rowspan="3">
                                <?php if (isset($foto['volume']) && $foto['volume'] !== null): ?>
                                    <?= number_format((float) $foto['volume'], 2, ',', '.') ?> <?= esc($foto['satuan_volume'] ?? '') ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="ket" rowspan="3">
                                <?= $foto['realisasi_fisik'] === null ? '-' : number_format((float) $foto['realisasi_fisik'], 2, ',', '.') . '%' ?>
                            </td>
                        <?php endif; ?>
                        <td class="photos">
                            <div class="photo">
                                <?php if (!empty($foto[$baselineField])): ?>
                                    <img src="<?= base_url($foto[$baselineField]) ?>" alt="<?= esc($baselineFields[$baselineField]) ?> kondisi 0%">
                                <?php else: ?>
                                    <div class="empty">Foto belum tersedia</div>
                                <?php endif; ?>
                                <div class="caption"><?= esc($baselineFields[$baselineField]) ?></div>
                            </div>
                        </td>
                        <td class="photos">
                            <div class="photo">
                                <?php if (!empty($foto[$field])): ?>
                                    <img src="<?= base_url($foto[$field]) ?>" alt="<?= esc($imageFields[$field]) ?>">
                                <?php else: ?>
                                    <div class="empty">Foto belum tersedia</div>
                                <?php endif; ?>
                                <div class="caption"><?= esc($imageFields[$field]) ?></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr class="work-row">
                <td class="menu">Dokumentasi pekerjaan</td><td class="volume">-</td><td class="ket">-</td>
                <td class="photos"><div class="empty">Foto kondisi 0% belum tersedia</div></td>
                <td class="photos"><div class="empty">Foto progres belum tersedia</div></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="signature-page">
        <div class="sign-date"><?= esc($sekolah['kab_kota'] ?? 'Tempat') ?>, <?= esc($formatDate($rencana['tanggal_selesai'] ?? date('Y-m-d'))) ?></div>
        <div class="signatures">
            <div class="signature">
                <?php if (!empty($p2spByPosition['ketua']['ttd'])): ?><img class="signature-mark" src="<?= base_url($p2spByPosition['ketua']['ttd']) ?>" alt=""><?php endif; ?>
                <div class="role">Mengetahui,<br>Ketua P2SP</div>
                <div class="name"><?= esc($p2spByPosition['ketua']['nama'] ?? '-') ?></div>
            </div>
            <div class="signature">
                <?php if (!empty($pengawasTtd)): ?><img class="signature-mark" src="<?= base_url($pengawasTtd) ?>" alt=""><?php endif; ?>
                <div class="role">Tim Teknis (Pengawas)</div>
                <div class="name"><?= esc($pengawasName) ?></div>
            </div>
            <div class="signature">
                <?php if (!empty($kepalaPelaksanaTtd)): ?><img class="signature-mark" src="<?= base_url($kepalaPelaksanaTtd) ?>" alt=""><?php endif; ?>
                <div class="role">Kepala Pelaksana</div>
                <div class="name"><?= esc($p2spByPosition['kepala_pelaksana']['nama'] ?? '-') ?></div>
            </div>
        </div>
    </div>
    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
