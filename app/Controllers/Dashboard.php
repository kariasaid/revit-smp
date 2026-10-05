<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\SekolahModel;
use App\Models\ProgresMingguanModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (session()->get('role') === 'admin') {
            return redirect()->to('/admin/sekolah');
        }

        if (session()->get('role') === 'perencana') {
            return redirect()->to('/perencana/time-schedule');
        }

        $userId       = (int) session()->get('id');
        $userModel    = new UserModel();
        $sekolahModel = new SekolahModel();
        $progresModel = new ProgresMingguanModel();

        $user        = $userModel->find($userId);
        $sekolahList = $sekolahModel->getByPengawas($userId);

        // Filter sekolah: ?sekolah=all | ?sekolah={id}
        $paramSekolah = $this->request->getGet('sekolah');
        $selectedId   = null; // null = semua sekolah dampingan
        $selectedSekolah = null;

        if ($paramSekolah !== null && $paramSekolah !== '' && $paramSekolah !== 'all') {
            $candidateId = (int) $paramSekolah;
            foreach ($sekolahList as $s) {
                if ((int) $s['id'] === $candidateId) {
                    $selectedId      = $candidateId;
                    $selectedSekolah = $s;
                    break;
                }
            }
        }

        // Scope sekolah yang dihitung
        $scopeList = $selectedId !== null
            ? array_values(array_filter($sekolahList, static fn ($s) => (int) $s['id'] === $selectedId))
            : $sekolahList;

        $totalSekolah    = count($sekolahList);
        $totalSekolahAll = max(8, $totalSekolah);
        $avgFisik        = 0.0;
        $avgKeuangan     = 0.0;
        $mingguBerjalan  = 0;
        $totalMingguMax  = 16;
        $pendingValidasi = 0;
        $totalLaporan    = 0;
        $statusProyek    = 'Normal';

        $chartTarget    = array_fill(1, 16, 0.0);
        $chartRealisasi = array_fill(1, 16, 0.0);
        $chartKeuangan  = array_fill(1, 16, 0.0);
        // Kumulatif untuk chart (lebih informatif)
        $chartAkumTarget    = array_fill(1, 16, 0.0);
        $chartAkumRealisasi = array_fill(1, 16, 0.0);
        $chartAkumKeuangan  = array_fill(1, 16, 0.0);

        $schoolRows = [];
        $activities = [];

        $sumFisik          = 0.0;
        $sumKeuangan       = 0.0;
        $countWithProgress = 0;

        // Pre-fetch progress per sekolah in scope
        $progresBySekolah = [];
        foreach ($scopeList as $s) {
            $progresBySekolah[(int) $s['id']] = $progresModel->getBySekolah((int) $s['id']);
        }

        // Build per-week data for charts (rata-rata antar sekolah di scope, atau 1 sekolah)
        $weekTargetSum    = array_fill(1, 16, 0.0);
        $weekRealisasiSum = array_fill(1, 16, 0.0);
        $weekKeuanganSum  = array_fill(1, 16, 0.0);
        $weekCount        = array_fill(1, 16, 0);

        foreach ($scopeList as $s) {
            $sid         = (int) $s['id'];
            $totalMinggu = (int) ($s['total_minggu'] ?? 16);
            $totalMingguMax = max($totalMingguMax, $totalMinggu);
            $danaDiterima = (float) ($s['dana_diterima'] ?? 0);

            $progresRows = $progresBySekolah[$sid] ?? [];
            $akumFisik   = 0.0;
            $akumTarget  = 0.0;
            $akumSerapan = 0.0;
            $lastMinggu  = 0;

            // Per-week values for this school (kumulatif)
            $schoolWeekTarget    = array_fill(1, 16, 0.0);
            $schoolWeekRealisasi = array_fill(1, 16, 0.0);
            $schoolWeekKeuangan  = array_fill(1, 16, 0.0);

            foreach ($progresRows as $p) {
                $totalLaporan++;
                $wk = (int) ($p['minggu_ke'] ?? 0);
                if ($wk < 1 || $wk > 16) {
                    continue;
                }
                $lastMinggu = max($lastMinggu, $wk);

                $status = $p['status_verval'] ?? '';

                if ($status === 'Diajukan') {
                    $pendingValidasi++;
                    $activities[] = [
                        'type'  => 'pending',
                        'title' => 'Laporan menunggu validasi',
                        'desc'  => ($s['nama_sekolah'] ?? '') . ' · Minggu ' . $wk,
                        'time'  => $p['updated_at'] ?? $p['created_at'] ?? '',
                        'icon'  => 'bi-hourglass-split',
                        'color' => 'warning',
                    ];
                }

                if ($status === 'Diterima') {
                    $t = (float) ($p['target_rencana'] ?? 0);
                    $r = (float) ($p['realisasi_fisik'] ?? 0);
                    $k = (float) ($p['serapan_dana'] ?? 0);

                    $akumFisik   += $r;
                    $akumTarget  += $t;
                    $akumSerapan += $k;

                    $schoolWeekTarget[$wk]    = $t;
                    $schoolWeekRealisasi[$wk] = $r;
                    $schoolWeekKeuangan[$wk]  = $k;

                    $weekTargetSum[$wk]    += $t;
                    $weekRealisasiSum[$wk] += $r;
                    $weekKeuanganSum[$wk]  += $k;
                    $weekCount[$wk]++;

                    $activities[] = [
                        'type'  => 'accepted',
                        'title' => 'Laporan mingguan telah diterima',
                        'desc'  => ($s['nama_sekolah'] ?? '') . ' · Minggu ' . $wk,
                        'time'  => $p['updated_at'] ?? $p['created_at'] ?? '',
                        'icon'  => 'bi-check-circle-fill',
                        'color' => 'success',
                    ];
                }

                if ($status === 'Ditolak') {
                    $activities[] = [
                        'type'  => 'rejected',
                        'title' => 'Laporan ditolak admin',
                        'desc'  => ($s['nama_sekolah'] ?? '') . ' · Minggu ' . $wk,
                        'time'  => $p['updated_at'] ?? $p['created_at'] ?? '',
                        'icon'  => 'bi-x-circle-fill',
                        'color' => 'danger',
                    ];
                }
            }

            // Build kumulatif series for this school then average later
            $runT = 0.0;
            $runR = 0.0;
            $runK = 0.0;
            for ($w = 1; $w <= 16; $w++) {
                $runT += $schoolWeekTarget[$w];
                $runR += $schoolWeekRealisasi[$w];
                $runK += $schoolWeekKeuangan[$w];
                // store for single-school chart
                if ($selectedId !== null) {
                    $chartAkumTarget[$w]    = round($runT, 2);
                    $chartAkumRealisasi[$w] = round($runR, 2);
                    $chartAkumKeuangan[$w]  = round($runK, 0);
                    $chartTarget[$w]        = round($schoolWeekTarget[$w], 2);
                    $chartRealisasi[$w]     = round($schoolWeekRealisasi[$w], 2);
                    $chartKeuangan[$w]      = round($schoolWeekKeuangan[$w], 0);
                }
            }

            $deviasi     = $akumFisik - $akumTarget;
            $pctKeuangan = $danaDiterima > 0 ? min(100.0, ($akumSerapan / $danaDiterima) * 100) : 0.0;

            if ($lastMinggu > 0 || $selectedId !== null) {
                $countWithProgress++;
                $sumFisik    += $akumFisik;
                $sumKeuangan += $pctKeuangan;
                $mingguBerjalan = max($mingguBerjalan, $lastMinggu);
            }

            if ($deviasi < -2) {
                $rowStatus = 'Terlambat';
            } elseif ($deviasi > 2) {
                $rowStatus = 'Maju';
            } else {
                $rowStatus = 'Normal';
            }

            $schoolRows[] = [
                'id'              => $sid,
                'nama_sekolah'    => $s['nama_sekolah'] ?? '-',
                'npsn'            => $s['npsn'] ?? '-',
                'target_fisik'    => round($akumTarget, 2),
                'realisasi_fisik' => round($akumFisik, 2),
                'deviasi'         => round($deviasi, 2),
                'keuangan'        => round($pctKeuangan, 2),
                'minggu'          => $lastMinggu,
                'total_minggu'    => $totalMinggu,
                'status'          => $rowStatus,
                'dana_diterima'   => $danaDiterima,
                'serapan'         => round($akumSerapan, 0),
            ];
        }

        // Multi-sekolah: rata-rata chart per minggu (kumulatif rata-rata)
        if ($selectedId === null && count($scopeList) > 0) {
            $runT = 0.0;
            $runR = 0.0;
            $runK = 0.0;
            $n    = max(1, count($scopeList));
            for ($w = 1; $w <= 16; $w++) {
                $avgT = $weekCount[$w] > 0 ? $weekTargetSum[$w] / $weekCount[$w] : 0;
                $avgR = $weekCount[$w] > 0 ? $weekRealisasiSum[$w] / $weekCount[$w] : 0;
                $avgK = $weekCount[$w] > 0 ? $weekKeuanganSum[$w] / $weekCount[$w] : 0;
                // Or distribute across all schools in scope:
                $avgT = $weekTargetSum[$w] / $n;
                $avgR = $weekRealisasiSum[$w] / $n;
                $avgK = $weekKeuanganSum[$w] / $n;

                $chartTarget[$w]    = round($avgT, 2);
                $chartRealisasi[$w] = round($avgR, 2);
                $chartKeuangan[$w]  = round($avgK, 0);

                $runT += $avgT;
                $runR += $avgR;
                $runK += $avgK;
                $chartAkumTarget[$w]    = round($runT, 2);
                $chartAkumRealisasi[$w] = round($runR, 2);
                $chartAkumKeuangan[$w]  = round($runK, 0);
            }
        }

        if ($countWithProgress > 0) {
            $avgFisik    = round($sumFisik / $countWithProgress, 2);
            $avgKeuangan = round($sumKeuangan / $countWithProgress, 2);
        }

        // Status proyek overall (berdasarkan rata-rata deviasi di scope)
        $avgDeviasi = 0.0;
        if (count($schoolRows) > 0) {
            $avgDeviasi = array_sum(array_column($schoolRows, 'deviasi')) / count($schoolRows);
        }
        if ($avgDeviasi < -2) {
            $statusProyek = 'Terlambat';
        } elseif ($avgDeviasi > 2) {
            $statusProyek = 'Maju';
        } else {
            $statusProyek = 'Normal';
        }

        // Sort activities newest first
        usort($activities, static function ($a, $b) {
            return strcmp($b['time'] ?? '', $a['time'] ?? '');
        });
        $activities = array_slice($activities, 0, 8);

        // Delta vs "previous week" (approx: last minggu realisasi vs previous)
        $deltaFisik    = 0.0;
        $deltaKeuangan = 0.0;
        if ($mingguBerjalan >= 2) {
            $prev = $mingguBerjalan - 1;
            $deltaFisik    = round(($chartAkumRealisasi[$mingguBerjalan] ?? 0) - ($chartAkumRealisasi[$prev] ?? 0), 2);
            // keuangan delta as absolute serapan difference (jt scale later in view)
            $deltaKeuangan = round((($chartAkumKeuangan[$mingguBerjalan] ?? 0) - ($chartAkumKeuangan[$prev] ?? 0)), 0);
        } elseif ($mingguBerjalan === 1) {
            $deltaFisik    = round($chartAkumRealisasi[1] ?? 0, 2);
            $deltaKeuangan = round($chartAkumKeuangan[1] ?? 0, 0);
        }

        $data = [
            'title'             => 'Dashboard Pengawas',
            'user'              => $user,
            'sekolahList'       => $sekolahList,
            'selectedId'        => $selectedId, // null = semua
            'selectedSekolah'   => $selectedSekolah,
            'activeMenu'        => 'dashboard',
            'stats'             => [
                'total_sekolah'     => $totalSekolah,
                'total_sekolah_all' => $totalSekolahAll,
                'scope_count'       => count($scopeList),
                'avg_fisik'         => $avgFisik,
                'avg_keuangan'      => $avgKeuangan,
                'minggu_berjalan'   => $mingguBerjalan,
                'total_minggu'      => $selectedSekolah
                    ? (int) ($selectedSekolah['total_minggu'] ?? 16)
                    : $totalMingguMax,
                'pending_validasi'  => $pendingValidasi,
                'total_laporan'     => $totalLaporan,
                'status_proyek'     => $statusProyek,
                'delta_fisik'       => $deltaFisik,
                'delta_keuangan'    => $deltaKeuangan,
                'filter_label'      => $selectedSekolah
                    ? ($selectedSekolah['nama_sekolah'] ?? 'Sekolah')
                    : 'Semua sekolah dampingan',
            ],
            'schoolRows'          => $schoolRows,
            'activities'          => $activities,
            'chartTarget'         => array_values($chartTarget),
            'chartRealisasi'      => array_values($chartRealisasi),
            'chartKeuangan'       => array_values($chartKeuangan),
            'chartAkumTarget'     => array_values($chartAkumTarget),
            'chartAkumRealisasi'  => array_values($chartAkumRealisasi),
            'chartAkumKeuangan'   => array_values($chartAkumKeuangan),
        ];

        return view('dashboard/index', $data);
    }
}
