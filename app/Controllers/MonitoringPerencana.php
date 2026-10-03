<?php

namespace App\Controllers;

use App\Models\ProgresMingguanModel;
use App\Models\RencanaMingguanModel;
use App\Models\SekolahModel;

class MonitoringPerencana extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'perencana') {
            return redirect()->to('/dashboard')->with('error', 'Halaman monitoring hanya dapat diakses perencana.');
        }

        $schools = (new SekolahModel())->getByPerencana((string) session()->get('nama_lengkap'));
        $scheduleModel = new RencanaMingguanModel();
        $progressModel = new ProgresMingguanModel();
        $curveCharts = [];

        foreach ($schools as &$school) {
            $plans = $scheduleModel->getBySekolah((int) $school['id']);
            $progress = $progressModel->getBySekolah((int) $school['id']);
            $school['plans_by_week'] = [];
            $school['progress_by_week'] = [];
            $school['progress_counts'] = [
                'Diterima' => 0,
                'Diajukan' => 0,
                'Ditolak' => 0,
                'Draft' => 0,
            ];
            $school['schedule_status'] = $plans[0]['status_verval'] ?? null;

            foreach ($plans as $plan) {
                $school['plans_by_week'][(int) $plan['minggu_ke']] = $plan;
            }
            foreach ($progress as $report) {
                $week = (int) $report['minggu_ke'];
                $school['progress_by_week'][$week] = $report;
                if (isset($school['progress_counts'][$report['status_verval']])) {
                    $school['progress_counts'][$report['status_verval']]++;
                }
            }
            $school['not_reported'] = max(0, (int) $school['total_minggu'] - count($progress));

            $curveByWeek = [];
            foreach ($progressModel->getKurvaS((int) $school['id']) as $curveRow) {
                $curveByWeek[(int) $curveRow['minggu_ke']] = $curveRow;
            }

            $planned = [0];
            $actual = [0];
            $labels = ['Mulai'];
            $school['curve_table'] = [];
            $lastPlanned = 0.0;
            $lastActual = 0.0;
            for ($week = 1; $week <= (int) $school['total_minggu']; $week++) {
                $curveRow = $curveByWeek[$week] ?? null;
                if (isset($curveByWeek[$week])) {
                    $lastPlanned = (float) $curveRow['akumulasi_rencana'];
                    $lastActual = (float) $curveRow['akumulasi_realisasi'];
                }
                $school['curve_table'][] = [
                    'minggu_ke' => $week,
                    'rencana' => (float) ($curveRow['rencana'] ?? 0),
                    'akumulasi_rencana' => $lastPlanned,
                    'realisasi' => (float) ($curveRow['realisasi'] ?? 0),
                    'akumulasi_realisasi' => $lastActual,
                    'deviasi' => (float) ($curveRow['deviasi_mingguan'] ?? 0),
                ];
                $planned[] = $lastPlanned;
                $actual[] = $lastActual;
                $labels[] = 'Minggu ' . $week;
            }
            $curveCharts[(int) $school['id']] = [
                'labels'  => $labels,
                'planned' => $planned,
                'actual'  => $actual,
            ];
        }
        unset($school);

        return view('perencana/monitoring_progres', [
            'title'      => 'Monitoring Progres Sekolah',
            'activeMenu' => 'monitoring-progres',
            'schools'    => $schools,
            'curveCharts'=> $curveCharts,
        ]);
    }
}
