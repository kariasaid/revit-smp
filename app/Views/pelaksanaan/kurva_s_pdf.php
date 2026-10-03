<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kurva S - <?= esc($sekolah['nama_sekolah'] ?? '') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 11px; color: #1f2937; padding: 20px 24px; }
        .header { border-bottom: 2px solid #1d5296; padding-bottom: 10px; margin-bottom: 14px; }
        .header h1 { font-size: 15px; color: #1d5296; margin-bottom: 3px; }
        .header .sub { font-size: 10px; color: #6b7280; }
        .info-box { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px 12px; margin-bottom: 12px; }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box td { padding: 2px 6px; vertical-align: top; font-size: 10px; }
        .info-box .label { color: #6b7280; width: 120px; }
        .chart-wrap { text-align: center; margin: 8px 0 14px; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px; background: #fff; }
        .chart-wrap img { width: 1000px; height: auto; display: block; margin: 0 auto; }
        .chart-wrap svg { display: block; margin: 0 auto; }
        table.data { width: 100%; border-collapse: collapse; font-size: 8px; margin-top: 6px; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 3px 2px; text-align: center; }
        table.data th { background: #1d5296; color: #fff; font-weight: 600; }
        table.data td.label-col { text-align: left; font-weight: 600; background: #f1f5f9; white-space: nowrap; padding-left: 4px; }
        .pos { color: #059669; }
        .neg { color: #dc2626; }
        .footer { margin-top: 16px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; }
        .section-title { font-weight: 700; font-size: 11px; margin: 4px 0 6px; color: #1d5296; }
        .summary { font-size: 10px; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KURVA S PELAKSANAAN</h1>
        <!--<div class="sub">Sistem Informasi Bantuan Revitalisasi Sekolah (REVIT SMP) · Dit. SMP Kemendikdasmen</div>-->
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td class="label">Nama Sekolah</td>
                <td><strong><?= esc($sekolah['nama_sekolah'] ?? '-') ?></strong></td>
                <td class="label">NPSN</td>
                <td><?= esc($sekolah['npsn'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Provinsi / Kab-Kota</td>
                <td><?= esc(($sekolah['provinsi'] ?? '') . ' / ' . ($sekolah['kab_kota'] ?? '')) ?></td>
                <td class="label">Total Minggu</td>
                <td><?= (int)($sekolah['total_minggu'] ?? 16) ?> Minggu</td>
            </tr>
            <tr>
                <td class="label">Perencana</td>
                <td><?= esc($personil['perencana'] ?? '-') ?></td>
                <td class="label">HP Perencana</td>
                <td><?= esc($personil['hp_perencana'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Pengawas</td>
                <td><?= esc($personil['pengawas'] ?? '-') ?></td>
                <td class="label">HP Pengawas</td>
                <td><?= esc($personil['hp_pengawas'] ?? '-') ?></td>
            </tr>
        </table>
    </div>

    <?php
    $last = !empty($kurva) ? end($kurva) : null;
    if ($last):
    ?>
    <div class="summary">
        <strong>Ringkasan Akhir:</strong>
        Akumulasi Rencana <?= number_format($last['akumulasi_rencana'], 2) ?>%
        &nbsp;|&nbsp; Akumulasi Realisasi <?= number_format($last['akumulasi_realisasi'], 2) ?>%
        &nbsp;|&nbsp; Akumulasi Deviasi
        <span class="<?= $last['akumulasi_deviasi'] >= 0 ? 'pos' : 'neg' ?>">
            <?= number_format($last['akumulasi_deviasi'], 2) ?>%
        </span>
    </div>
    <?php endif; ?>

    <div class="section-title">Grafik Kurva S — Rencana vs Realisasi</div>
    <div class="chart-wrap">
        <?php if (!empty($chartImage)): ?>
            <img src="<?= $chartImage ?>" alt="Kurva S Chart" width="1000" height="340" style="width:1000px; height:auto;">
        <?php elseif (!empty($chartSvg)): ?>
            <?= $chartSvg ?>
        <?php else: ?>
            <p style="color:#9ca3af; padding:40px;">Grafik tidak tersedia</p>
        <?php endif; ?>
    </div>

    <div class="section-title">Tabel Indikator Kurva S</div>
    <table class="data">
        <thead>
            <tr>
                <th style="text-align:left;">INDIKATOR</th>
                <?php foreach ($kurva as $k): ?>
                    <th>M-<?= $k['minggu_ke'] ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label-col">Rencana (%)</td>
                <?php foreach ($kurva as $k): ?>
                    <td><?= number_format($k['rencana'], 2) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="label-col">Akumulasi Rencana (%)</td>
                <?php foreach ($kurva as $k): ?>
                    <td><?= number_format($k['akumulasi_rencana'], 2) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="label-col">Realisasi (%)</td>
                <?php foreach ($kurva as $k): ?>
                    <td><?= number_format($k['realisasi'], 2) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="label-col">Akumulasi Realisasi (%)</td>
                <?php foreach ($kurva as $k): ?>
                    <td><?= number_format($k['akumulasi_realisasi'], 2) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="label-col">Deviasi Mingguan (%)</td>
                <?php foreach ($kurva as $k): ?>
                    <?php $d = $k['deviasi_mingguan']; ?>
                    <td class="<?= $d >= 0 ? 'pos' : 'neg' ?>"><?= number_format($d, 2) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class="label-col">Akumulasi Deviasi (%)</td>
                <?php foreach ($kurva as $k): ?>
                    <?php $d = $k['akumulasi_deviasi']; ?>
                    <td class="<?= $d >= 0 ? 'pos' : 'neg' ?>"><?= number_format($d, 2) ?></td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada <?= date('d/m/Y H:i') ?> WITA · Aplikasi Pengarsipan Dokumen · I Kadek Kariasa / Made Gapur &nbsp;|&nbsp; Versi 1.1.0
        &nbsp;|&nbsp; Dokumen digenerate otomatis dari sistem.
    </div>
</body>
</html>
