<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonilSekolahModel extends Model
{
    protected $table            = 'personil_sekolah';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'sekolah_id', 'kepala_sekolah', 'hp_kepala_sekolah',
        'perencana', 'hp_perencana', 'pengawas', 'hp_pengawas',
        'reviewer', 'hp_reviewer', 'fasilitator', 'hp_fasilitator',
        'ta_pusat', 'hp_ta_pusat'
    ];
    protected $useTimestamps = true;

    public function getBySekolah(int $sekolahId)
    {
        return $this->where('sekolah_id', $sekolahId)->first();
    }
}
