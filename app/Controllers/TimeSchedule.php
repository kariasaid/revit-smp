<?php

namespace App\Controllers;

use App\Models\RencanaMingguanModel;
use App\Models\ProgresMingguanModel;
use App\Models\SekolahModel;
use Config\Database;
use DateTimeImmutable;

class TimeSchedule extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'perencana') {
            return redirect()->to('/dashboard')->with('error', 'Halaman jadwal awal hanya dapat diakses perencana.');
        }

        $schools = (new SekolahModel())->getByPerencana((int) session()->get('id'));
        if (empty($schools)) {
            return redirect()->to('/dashboard')->with('error', 'Belum ada sekolah yang ditugaskan kepada akun perencana ini.');
        }

        $selectedSchoolId = (int) ($this->request->getGet('sekolah_id') ?? $schools[0]['id']);
        $school = null;
        foreach ($schools as $assignedSchool) {
            if ((int) $assignedSchool['id'] === $selectedSchoolId) {
                $school = $assignedSchool;
                break;
            }
        }
        if (!$school) {
            return redirect()->to('/perencana/time-schedule')->with('error', 'Sekolah tidak termasuk penugasan Anda.');
        }

        $plans = (new RencanaMingguanModel())->getBySekolah($selectedSchoolId);
        $plansByWeek = [];
        foreach ($plans as $plan) {
            $plansByWeek[(int) $plan['minggu_ke']] = $plan;
        }
        $scheduleStatus = $plans === [] ? null : $plans[0]['status_verval'];

        $progressTargetsByWeek = [];
        $actualByWeek = [];
        foreach ((new ProgresMingguanModel())->getBySekolah($selectedSchoolId) as $progress) {
            $progressTargetsByWeek[(int) $progress['minggu_ke']] = (float) $progress['target_rencana'];
            if ($progress['status_verval'] === 'Diterima') {
                $actualByWeek[(int) $progress['minggu_ke']] = (float) $progress['realisasi_fisik'];
            }
        }

        $suggestedTargets = [];
        $targetSources = [];
        $scheduledTotal = 0.0;
        $futureWeeks = [];
        for ($week = 1; $week <= (int) $school['total_minggu']; $week++) {
            if (isset($plansByWeek[$week])) {
                $suggestedTargets[$week] = (float) $plansByWeek[$week]['target_rencana'];
                $targetSources[$week] = 'tersimpan';
            } elseif (isset($progressTargetsByWeek[$week])) {
                $suggestedTargets[$week] = $progressTargetsByWeek[$week];
                $targetSources[$week] = 'progres';
            } else {
                $futureWeeks[] = $week;
                continue;
            }
            $scheduledTotal += $suggestedTargets[$week];
        }

        $remainingTarget = max(0, round(100 - $scheduledTotal, 2));
        $distributedTarget = $futureWeeks === [] ? 0 : floor(($remainingTarget / count($futureWeeks)) * 100) / 100;
        foreach ($futureWeeks as $index => $week) {
            $suggestedTargets[$week] = $index === array_key_last($futureWeeks)
                ? round($remainingTarget - ($distributedTarget * (count($futureWeeks) - 1)), 2)
                : $distributedTarget;
            $targetSources[$week] = 'sisa';
        }

        $plannedCumulative = [0];
        $plannedTotal = 0.0;
        for ($week = 1; $week <= (int) $school['total_minggu']; $week++) {
            $plannedTotal += (float) ($suggestedTargets[$week] ?? 0);
            $plannedCumulative[] = round($plannedTotal, 2);
        }

        $actualCumulative = [0];
        $actualTotal = 0.0;
        for ($week = 1; $week <= (int) $school['total_minggu']; $week++) {
            $weekActual = $actualByWeek[$week] ?? 0;
            $actualTotal += $weekActual;
            $actualCumulative[] = round($actualTotal, 2);
        }

        return view('perencana/time_schedule', [
            'title'          => 'Time Schedule Awal',
            'activeMenu'     => 'time-schedule',
            'schools'        => $schools,
            'school'         => $school,
            'plansByWeek'    => $plansByWeek,
            'scheduleStatus' => $scheduleStatus,
            'verificationNote' => $plans[0]['catatan_verifikasi'] ?? null,
            'suggestedTargets' => $suggestedTargets,
            'targetSources'  => $targetSources,
            'plannedCumulative' => $plannedCumulative,
            'actualCumulative' => $actualCumulative,
            'progressTargetTotal' => round(array_sum($progressTargetsByWeek), 2),
            'progressWeeks'  => count($progressTargetsByWeek),
            'scheduleExists' => count($plansByWeek) === (int) $school['total_minggu'],
        ]);
    }

    public function save()
    {
        if (session()->get('role') !== 'perencana') {
            return redirect()->to('/dashboard')->with('error', 'Hanya perencana yang dapat membuat time schedule awal.');
        }

        $schoolId = (int) $this->request->getPost('sekolah_id');
        $schools = (new SekolahModel())->getByPerencana((int) session()->get('id'));
        $school = null;
        foreach ($schools as $assignedSchool) {
            if ((int) $assignedSchool['id'] === $schoolId) {
                $school = $assignedSchool;
                break;
            }
        }
        if (!$school) {
            return redirect()->to('/dashboard')->with('error', 'Sekolah tidak termasuk penugasan Anda.');
        }

        $scheduleModel = new RencanaMingguanModel();
        $existingPlans = $scheduleModel->getBySekolah($schoolId);
        if (!empty($existingPlans) && $existingPlans[0]['status_verval'] === 'Diterima') {
            return redirect()->back()->with('error', 'Time schedule yang sudah diterima admin tidak dapat diubah.');
        }

        $plansByWeek = [];
        foreach ($existingPlans as $plan) {
            $plansByWeek[(int) $plan['minggu_ke']] = $plan;
        }

        $rows = [];
        $totalTarget = 0.0;
        $weeks = (int) $school['total_minggu'];
        $initialStart = trim((string) $this->request->getPost('tanggal_mulai_1'));
        if (!$this->isValidDate($initialStart)) {
            return redirect()->back()->withInput()->with('error', 'Tanggal mulai Minggu 1 wajib diisi dengan tanggal yang valid.');
        }
        $firstWeekStart = DateTimeImmutable::createFromFormat('!Y-m-d', $initialStart);

        for ($week = 1; $week <= $weeks; $week++) {
            $weekStart = $firstWeekStart->modify('+' . (($week - 1) * 7) . ' days');
            $weekEnd = $weekStart->modify('+6 days');
            $targetInput = trim((string) $this->request->getPost('target_rencana_' . $week));
            $note = trim((string) $this->request->getPost('keterangan_' . $week));

            if (!is_numeric($targetInput) || (float) $targetInput < 0 || (float) $targetInput > 100) {
                return redirect()->back()->withInput()->with('error', 'Target minggu ke-' . $week . ' harus di antara 0 dan 100 persen.');
            }
            if (mb_strlen($note) > 1000) {
                return redirect()->back()->withInput()->with('error', 'Catatan minggu ke-' . $week . ' maksimal 1.000 karakter.');
            }

            $row = [
                'sekolah_id'     => $schoolId,
                'minggu_ke'      => $week,
                'tanggal_mulai'  => $weekStart->format('Y-m-d'),
                'tanggal_selesai'=> $weekEnd->format('Y-m-d'),
                'target_rencana' => round((float) $targetInput, 2),
                'keterangan'     => $note === '' ? null : $note,
                'dibuat_oleh'    => (int) session()->get('id'),
                'status_verval'  => 'Diajukan',
                'diverifikasi_oleh' => null,
                'diverifikasi_pada' => null,
                'catatan_verifikasi' => null,
            ];
            if (isset($plansByWeek[$week])) {
                $row['id'] = $plansByWeek[$week]['id'];
            }

            $rows[] = $row;
            $totalTarget += (float) $targetInput;
        }

        if (round($totalTarget, 2) !== 100.0) {
            return redirect()->back()->withInput()->with('error', 'Total target rencana seluruh minggu harus tepat 100%. Total saat ini ' . number_format($totalTarget, 2, ',', '.') . '%.');
        }

        $database = Database::connect();
        $database->transStart();
        foreach ($rows as $row) {
            if (!$scheduleModel->save($row)) {
                $database->transRollback();
                return redirect()->back()->withInput()->with('error', 'Jadwal gagal disimpan. Silakan periksa kembali data.');
            }
        }
        $database->transComplete();

        if (!$database->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Jadwal gagal disimpan karena terjadi kesalahan database.');
        }

        return redirect()->to('/perencana/time-schedule?sekolah_id=' . $schoolId)
                 ->with('success', 'Time schedule awal berhasil diajukan kepada admin untuk verifikasi.');
    }

    private function isValidDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
