<?php

namespace App\Models;

use CodeIgniter\Model;

class DokumenPelaporanModel extends Model
{
    protected $table            = 'dokumen_pelaporan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'sekolah_id', 'jenis_pelaporan', 'nama_dokumen', 'file_template',
        'file_unggah', 'status_unggah', 'status_validasi', 'urutan'
    ];
    protected $useTimestamps = true;

    public function getBySekolahJenis(int $sekolahId, string $jenis = '50%')
    {
        return $this->where('sekolah_id', $sekolahId)
                    ->where('jenis_pelaporan', $jenis)
                    ->orderBy('urutan', 'ASC')
                    ->findAll();
    }

    public function getAllForAdmin(): array
    {
        return $this->select('dokumen_pelaporan.*, sekolah.nama_sekolah, sekolah.npsn')
                    ->join('sekolah', 'sekolah.id = dokumen_pelaporan.sekolah_id')
                    ->orderBy('sekolah.nama_sekolah', 'ASC')
                    ->orderBy('dokumen_pelaporan.jenis_pelaporan', 'ASC')
                    ->orderBy('dokumen_pelaporan.urutan', 'ASC')
                    ->findAll();
    }
}
