<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgresMingguanModel extends Model
{
    protected $table            = 'progres_mingguan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'sekolah_id', 'minggu_ke', 'serapan_dana', 'target_rencana',
        'realisasi_fisik', 'deviasi', 'status_verval', 'keterangan',
        'foto_depan', 'foto_belakang', 'foto_dalam', 'pdf_laporan'
    ];
    protected $useTimestamps = true;

    public function getBySekolah(int $sekolahId)
    {
        return $this->where('sekolah_id', $sekolahId)
                    ->orderBy('minggu_ke', 'ASC')
                    ->findAll();
    }

    public function getPendingValidation(): array
    {
        return $this->select('progres_mingguan.*, sekolah.nama_sekolah, sekolah.npsn, users.nama_lengkap AS nama_pengawas, (SELECT YEAR(MIN(rencana_mingguan.tanggal_mulai)) FROM rencana_mingguan WHERE rencana_mingguan.sekolah_id = progres_mingguan.sekolah_id) AS tahun_proyek')
                    ->join('sekolah', 'sekolah.id = progres_mingguan.sekolah_id')
                    ->join('users', 'users.id = sekolah.pengawas_id', 'left')
                    ->where('progres_mingguan.status_verval', 'Diajukan')
                    ->orderBy('progres_mingguan.updated_at', 'ASC')
                    ->findAll();
    }

    public function getAllForAdmin(): array
    {
        return $this->select('progres_mingguan.*, sekolah.nama_sekolah, sekolah.npsn, users.nama_lengkap AS nama_pengawas, (SELECT YEAR(MIN(rencana_mingguan.tanggal_mulai)) FROM rencana_mingguan WHERE rencana_mingguan.sekolah_id = progres_mingguan.sekolah_id) AS tahun_proyek')
                    ->join('sekolah', 'sekolah.id = progres_mingguan.sekolah_id')
                    ->join('users', 'users.id = sekolah.pengawas_id', 'left')
                    ->orderBy('sekolah.nama_sekolah', 'ASC')
                    ->orderBy('progres_mingguan.minggu_ke', 'DESC')
                    ->findAll();
    }

    public function getKurvaS(int $sekolahId)
    {
        $plansByWeek = [];
        foreach ((new RencanaMingguanModel())->getBySekolah($sekolahId) as $plan) {
            if ($plan['status_verval'] === 'Diterima') {
                $plansByWeek[(int) $plan['minggu_ke']] = $plan;
            }
        }

        $progressByWeek = [];
        foreach ($this->getBySekolah($sekolahId) as $progress) {
            if ($progress['status_verval'] === 'Diterima') {
                $progressByWeek[(int) $progress['minggu_ke']] = $progress;
            }
        }

        $weeks = array_unique(array_merge(array_keys($plansByWeek), array_keys($progressByWeek)));
        sort($weeks, SORT_NUMERIC);
        $akumRencana = 0;
        $akumRealisasi = 0;
        $akumDeviasi = 0;
        $result = [];

        foreach ($weeks as $week) {
            $progress = $progressByWeek[$week] ?? null;
            $rencana = isset($plansByWeek[$week])
                ? (float) $plansByWeek[$week]['target_rencana']
                : (float) ($progress['target_rencana'] ?? 0);
            $realisasi = (float) ($progress['realisasi_fisik'] ?? 0);
            $deviasi = $realisasi - $rencana;

            $akumRencana   += $rencana;
            $akumRealisasi += $realisasi;
            $akumDeviasi   += $deviasi;

            $result[] = [
                'minggu_ke'          => (int) $week,
                'rencana'            => $rencana,
                'akumulasi_rencana'  => round($akumRencana, 2),
                'realisasi'          => $realisasi,
                'akumulasi_realisasi'=> round($akumRealisasi, 2),
                'deviasi_mingguan'   => $deviasi,
                'akumulasi_deviasi'  => round($akumDeviasi, 2),
            ];
        }
        return $result;
    }

    public function getSummary(int $sekolahId)
    {
        $rows = $this->getBySekolah($sekolahId);
        $totalMingguInput = count($rows);
        $validatedRows = array_filter($rows, static fn (array $row): bool => $row['status_verval'] === 'Diterima');
        $pendingRows = array_filter($rows, static fn (array $row): bool => $row['status_verval'] === 'Diajukan');
        $akumFisik = 0;
        $akumSerapan = 0;
        $lastDeviasi = 0;

        foreach ($validatedRows as $r) {
            $akumFisik   += (float) $r['realisasi_fisik'];
            $akumSerapan += (float) $r['serapan_dana'];
            $lastDeviasi  = (float) $r['deviasi'];
        }

        return [
            'total_laporan'     => $totalMingguInput,
            'akumulasi_fisik'   => round($akumFisik, 2),
            'akumulasi_serapan' => $akumSerapan,
            'status_deviasi'    => $lastDeviasi,
            'minggu_divalidasi' => count($validatedRows),
            'minggu_menunggu'   => count($pendingRows),
        ];
    }
}
