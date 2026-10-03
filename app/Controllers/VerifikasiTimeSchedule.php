<?php

namespace App\Controllers;

use App\Models\RencanaMingguanModel;
use Config\Database;

class VerifikasiTimeSchedule extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Halaman verifikasi jadwal hanya dapat diakses admin.');
        }

        $db = Database::connect();
        $schedules = $db->table('rencana_mingguan r')
            ->select('s.id AS sekolah_id, s.nama_sekolah, s.npsn, s.total_minggu, p.perencana, p.pengawas, u.nama_lengkap AS nama_pengaju, COUNT(r.id) AS total_baris, SUM(r.target_rencana) AS target_total, MIN(r.tanggal_mulai) AS tanggal_mulai, MAX(r.tanggal_selesai) AS tanggal_selesai')
            ->join('sekolah s', 's.id = r.sekolah_id')
            ->join('personil_sekolah p', 'p.sekolah_id = s.id', 'left')
            ->join('users u', 'u.id = r.dibuat_oleh', 'left')
            ->where('r.status_verval', 'Diajukan')
            ->groupBy('s.id, s.nama_sekolah, s.npsn, s.total_minggu, p.perencana, p.pengawas, u.nama_lengkap')
            ->orderBy('r.updated_at', 'ASC')
            ->get()
            ->getResultArray();

        $scheduleModel = new RencanaMingguanModel();
        foreach ($schedules as &$schedule) {
            $schedule['minggu'] = $scheduleModel->getBySekolah((int) $schedule['sekolah_id']);
        }
        unset($schedule);

        return view('admin/verifikasi_time_schedule', [
            'title'      => 'Verifikasi Time Schedule',
            'activeMenu' => 'validasi-schedule',
            'schedules'  => $schedules,
        ]);
    }

    public function update(int $sekolahId)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat memverifikasi jadwal.');
        }

        $decision = (string) $this->request->getPost('keputusan');
        $note = trim((string) $this->request->getPost('catatan_verifikasi'));
        if (!in_array($decision, ['Diterima', 'Ditolak'], true)) {
            return redirect()->back()->with('error', 'Keputusan verifikasi tidak valid.');
        }
        if ($decision === 'Ditolak' && $note === '') {
            return redirect()->back()->with('error', 'Catatan wajib diisi jika jadwal ditolak.');
        }
        if (mb_strlen($note) > 2000) {
            return redirect()->back()->with('error', 'Catatan verifikasi maksimal 2.000 karakter.');
        }

        $db = Database::connect();
        $school = $db->table('sekolah')->where('id', $sekolahId)->get()->getRowArray();
        $plans = (new RencanaMingguanModel())->where('sekolah_id', $sekolahId)
            ->where('status_verval', 'Diajukan')
            ->orderBy('minggu_ke', 'ASC')
            ->findAll();

        if (!$school || count($plans) !== (int) $school['total_minggu']) {
            return redirect()->to('/admin/verifikasi-time-schedule')
                ->with('error', 'Jadwal tidak lengkap atau sudah diproses. Periksa jumlah minggu sebelum memverifikasi.');
        }

        $targetTotal = array_sum(array_column($plans, 'target_rencana'));
        if (round((float) $targetTotal, 2) !== 100.0) {
            return redirect()->to('/admin/verifikasi-time-schedule')
                ->with('error', 'Total target jadwal harus tepat 100% sebelum dapat diverifikasi.');
        }

        $db->transStart();
        $db->table('rencana_mingguan')
            ->where('sekolah_id', $sekolahId)
            ->where('status_verval', 'Diajukan')
            ->update([
                'status_verval'     => $decision,
                'diverifikasi_oleh' => (int) session()->get('id'),
                'diverifikasi_pada' => date('Y-m-d H:i:s'),
                'catatan_verifikasi'=> $note === '' ? null : $note,
            ]);
        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->to('/admin/verifikasi-time-schedule')
                ->with('error', 'Status verifikasi gagal disimpan. Silakan coba lagi.');
        }

        $message = $decision === 'Diterima'
            ? 'Time schedule sekolah berhasil diterima. Pengawas sekarang dapat menginput progres.'
            : 'Time schedule ditolak dan dikembalikan kepada perencana untuk revisi.';

        return redirect()->to('/admin/verifikasi-time-schedule')->with('success', $message);
    }
}
