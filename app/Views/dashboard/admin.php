<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.admin-dashboard{--dash-primary:#1d5296;--dash-success:#059669;--dash-danger:#dc2626;--dash-warning:#d97706}
.dashboard-header{background:linear-gradient(135deg,#153d70,#2563eb);color:#fff;border-radius:18px;padding:25px;margin-bottom:20px}
.dashboard-header h3{font-weight:700;margin-bottom:5px}.dashboard-header small{opacity:.75}
.stat-box{background:#fff;border-radius:15px;padding:18px;height:100%;box-shadow:0 2px 8px rgba(0,0,0,.06);position:relative;overflow:hidden}
.stat-box .icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:#e8f0fe;color:var(--dash-primary);font-size:20px;margin-bottom:12px}
.stat-title{color:#6b7280;font-size:12px;text-transform:uppercase;font-weight:600}
.stat-number{font-size:25px;font-weight:750;color:#111827}.stat-sub{color:#9ca3af;font-size:12px}
.dashboard-card{border:none;border-radius:15px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.dashboard-card .card-header{padding:16px 20px;font-weight:700}
.status-pill{padding:5px 10px;border-radius:30px;font-size:11px;font-weight:600}
.status-success{background:#dcfce7;color:#166534}.status-danger{background:#fee2e2;color:#991b1b}.status-warning{background:#fef3c7;color:#92400e}
.progress-thin{height:7px}.school-selector{min-width:320px}
@media(max-width:768px){.school-selector{width:100%;min-width:100%}}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="admin-dashboard">

<div class="dashboard-header">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
<div>
<div class="small mb-1"><i class="bi bi-speedometer2 me-1"></i>SISTEM MONITORING REVITALISASI SEKOLAH</div>
<h3>Dashboard Admin</h3>
<small>Monitoring progres fisik, keuangan, laporan mingguan dan status pelaksanaan</small>
</div>
<div class="text-end"><div class="small"><?= date('d F Y') ?></div><div class="small"><?= date('H:i') ?> WITA</div></div>
</div>
</div>

<div class="row g-3 mb-4">
<?php
$cards = [
 ['bi-buildings','Total Sekolah',$dashboard['jumlah_sekolah'],'sekolah terdaftar',''],
 ['bi-check-circle','Selesai',$dashboard['selesai'],'proyek selesai','text-success'],
 ['bi-activity','Normal',$dashboard['normal'],'sesuai target','text-success'],
 ['bi-exclamation-triangle','Terlambat',$dashboard['terlambat'],'perlu perhatian','text-danger'],
 ['bi-file-earmark-x','Belum Lapor',$dashboard['belum_lapor'],'belum ada progres','text-warning'],
 ['bi-clock-history','Menunggu',$dashboard['pending_reports'],'validasi laporan','text-warning'],
];
foreach($cards as $c): ?>
<div class="col-xl-2 col-md-4 col-6"><div class="stat-box">
<div class="icon"><i class="bi <?= $c[0] ?>"></i></div>
<div class="stat-title"><?= $c[1] ?></div>
<div class="stat-number <?= $c[4] ?>"><?= number_format($c[2]) ?></div>
<div class="stat-sub"><?= $c[3] ?></div>
</div></div>
<?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
<?php
$money = [
 ['Total Dana',$dashboard['total_dana'],''],
 ['Total Pengeluaran',$dashboard['total_pengeluaran'],'text-danger'],
 ['Total Saldo',$dashboard['total_saldo'],'text-success'],
];
foreach($money as $m): ?>
<div class="col-lg-3 col-md-6"><div class="stat-box">
<div class="stat-title"><?= $m[0] ?></div>
<div class="stat-number <?= $m[2] ?>">Rp <?= number_format($m[1],0,',','.') ?></div>
</div></div>
<?php endforeach; ?>
<div class="col-lg-3 col-md-6"><div class="stat-box">
<div class="stat-title">Rata-rata Progres Fisik</div>
<div class="stat-number text-primary"><?= number_format($dashboard['rata_fisik'],2) ?>%</div>
<div class="stat-sub">Rata-rata serapan: <?= number_format($dashboard['rata_keuangan'],2) ?>%</div>
</div></div>
</div>

<div class="row g-4 mb-4">
<div class="col-lg-6"><div class="card dashboard-card">
<div class="card-header"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Sekolah Perlu Perhatian</div>
<div class="card-body p-0"><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Sekolah</th><th class="text-end">Fisik</th><th class="text-end">Deviasi</th><th></th></tr></thead>
<tbody>
<?php if(empty($sekolahTerlambat)): ?>
<tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-check-circle text-success fs-3"></i><br>Tidak ada sekolah terlambat</td></tr>
<?php else: foreach(array_slice($sekolahTerlambat,0,7) as $row): ?>
<tr><td><div class="fw-semibold"><?= esc($row['nama_sekolah']) ?></div><small class="text-muted">NPSN: <?= esc($row['npsn']) ?></small></td>
<td class="text-end"><?= number_format($row['fisik'],2) ?>%</td>
<td class="text-end text-danger"><?= number_format($row['deviasi'],2) ?>%</td>
<td><a href="<?= base_url('dashboard?sekolah_id='.$row['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div></div></div>

<div class="col-lg-6"><div class="card dashboard-card">
<div class="card-header"><i class="bi bi-clock-history text-warning me-2"></i>Laporan Menunggu Validasi</div>
<div class="card-body p-0"><div class="table-responsive"><table class="table mb-0">
<thead><tr><th>Sekolah</th><th>Minggu</th><th>Tanggal</th></tr></thead><tbody>
<?php if(empty($pendingReports)): ?>
<tr><td colspan="3" class="text-center text-muted py-4">Tidak ada laporan menunggu validasi.</td></tr>
<?php else: foreach($pendingReports as $row): ?>
<tr><td><?= esc($row['nama_sekolah']) ?></td><td><span class="badge bg-warning text-dark">Minggu <?= $row['minggu_ke'] ?></span></td><td><?= date('d-m-Y',strtotime($row['updated_at'])) ?></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div></div></div>
</div>

<div class="card dashboard-card mb-4">
<div class="card-header"><i class="bi bi-buildings text-primary me-2"></i>Monitoring Seluruh Sekolah <span class="float-end small text-muted"><?= count($summary) ?> sekolah</span></div>
<div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
<thead><tr><th>Sekolah</th><th class="text-center">Minggu</th><th class="text-end">Target</th><th class="text-end">Fisik</th><th class="text-end">Deviasi</th><th class="text-end">Keuangan</th><th class="text-end">Saldo</th><th class="text-center">Status</th><th></th></tr></thead>
<tbody>
<?php foreach($summary as $row): ?>
<tr>
<td><div class="fw-semibold"><?= esc($row['nama_sekolah']) ?></div><small class="text-muted">NPSN: <?= esc($row['npsn']) ?></small></td>
<td class="text-center"><strong><?= $row['minggu_terakhir'] ?></strong>/<?= $row['total_minggu'] ?><?php if($row['gap_minggu']>0): ?><br><small class="text-danger">tertinggal <?= $row['gap_minggu'] ?> minggu</small><?php endif; ?></td>
<td class="text-end"><?= number_format($row['target'],2) ?>%</td>
<td class="text-end"><strong><?= number_format($row['fisik'],2) ?>%</strong><div class="progress progress-thin mt-1"><div class="progress-bar bg-primary" style="width:<?= min(100,max(0,$row['fisik'])) ?>%"></div></div></td>
<td class="text-end <?= $row['deviasi']<0?'text-danger':'text-success' ?>"><?= $row['deviasi']>=0?'+':'' ?><?= number_format($row['deviasi'],2) ?>%</td>
<td class="text-end"><?= number_format($row['keuangan'],2) ?>%<div class="progress progress-thin mt-1"><div class="progress-bar bg-success" style="width:<?= min(100,max(0,$row['keuangan'])) ?>%"></div></div></td>
<td class="text-end">Rp <?= number_format($row['saldo'],0,',','.') ?></td>
<td class="text-center"><span class="status-pill status-<?= $row['status_class'] ?>"><?= esc($row['status']) ?></span></td>
<td><a href="<?= base_url('dashboard?sekolah_id='.$row['id']) ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
</tr>
<?php endforeach; ?>
</tbody></table></div></div></div>

<div class="card dashboard-card mb-4">
<div class="card-header"><div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
<span><i class="bi bi-search me-2"></i>Detail Monitoring Sekolah</span>
<form method="get" action="<?= base_url('dashboard') ?>">
<select name="sekolah_id" class="form-select form-select-sm school-selector" onchange="this.form.submit()">
<?php foreach($sekolahList as $school): ?>
<option value="<?= $school['id'] ?>" <?= (int)$school['id']===(int)($selectedSekolah['id']??0)?'selected':'' ?>><?= esc($school['nama_sekolah']) ?></option>
<?php endforeach; ?>
</select></form></div></div>

<?php if($detail): ?>
<div class="card-body">
<h5 class="fw-bold mb-1"><?= esc($detail['nama_sekolah']) ?></h5>
<div class="text-muted small mb-4">NPSN: <?= esc($detail['npsn']) ?></div>
<div class="row g-3">
<?php
$detailCards = [
 ['Progres Fisik',number_format($detail['fisik'],2).'%', 'text-primary','Target: '.number_format($detail['target'],2).'%'],
 ['Serapan Keuangan',number_format($detail['keuangan'],2).'%', 'text-success','Pengeluaran: Rp '.number_format($detail['pengeluaran'],0,',','.')],
 ['Minggu Berjalan',$detail['minggu_berjalan'].' / '.$detail['total_minggu'],'text-warning','Laporan terakhir: Minggu '.$detail['minggu_terakhir']],
 ['Saldo Dana','Rp '.number_format($detail['saldo'],0,',','.'),'text-success','Dana: Rp '.number_format($detail['dana'],0,',','.')],
];
foreach($detailCards as $dc): ?>
<div class="col-lg-3 col-md-6"><div class="stat-box"><div class="stat-title"><?= $dc[0] ?></div><div class="stat-number <?= $dc[2] ?>"><?= $dc[1] ?></div><div class="stat-sub"><?= $dc[3] ?></div></div></div>
<?php endforeach; ?>
</div>
<div class="row g-4 mt-2">
<div class="col-lg-7"><div class="card dashboard-card"><div class="card-header">Kurva Progres Fisik</div><div class="card-body"><div style="height:320px"><canvas id="chartFisik"></canvas></div></div></div></div>
<div class="col-lg-5"><div class="card dashboard-card"><div class="card-header">Serapan Keuangan</div><div class="card-body"><div style="height:320px"><canvas id="chartKeuangan"></canvas></div></div></div></div>
</div>
</div>
<?php else: ?>
<div class="card-body text-center py-5"><i class="bi bi-building fs-1 text-muted"></i><p class="text-muted mt-3">Belum ada sekolah yang dapat ditampilkan.</p></div>
<?php endif; ?>
</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const chartLabels=<?= json_encode($chartLabels??[]) ?>;
const chartTarget=<?= json_encode($chartTarget??[]) ?>;
const chartFisik=<?= json_encode($chartFisik??[]) ?>;
const chartKeuangan=<?= json_encode($chartKeuangan??[]) ?>;

const canvasFisik=document.getElementById('chartFisik');
if(canvasFisik){
new Chart(canvasFisik,{type:'line',data:{labels:chartLabels,datasets:[
{label:'Target Kumulatif',data:chartTarget,borderWidth:2,tension:.35,fill:false},
{label:'Realisasi Fisik',data:chartFisik,borderWidth:3,tension:.35,fill:false}
]},options:{responsive:true,maintainAspectRatio:false,interaction:{intersect:false,mode:'index'},plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,suggestedMax:100,ticks:{callback:value=>value+'%'}}}}});
}
const canvasKeuangan=document.getElementById('chartKeuangan');
if(canvasKeuangan){
new Chart(canvasKeuangan,{type:'bar',data:{labels:chartLabels,datasets:[{label:'Pengeluaran Kumulatif',data:chartKeuangan,borderWidth:1}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{callback:value=>'Rp '+new Intl.NumberFormat('id-ID',{notation:'compact'}).format(value)}}}}});
}
</script>
<?= $this->endSection() ?>
