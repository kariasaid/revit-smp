<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanTemplateBantuanModel extends Model
{
    protected $table = 'laporan_template_bantuan';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'sekolah_id', 'bantuan_sekolah_id', 'nama_template', 'file_template',
        'keterangan', 'diunggah_oleh'
    ];
    protected $useTimestamps = true;
}
