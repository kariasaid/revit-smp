<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWorkRealization extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('foto_progres_pekerjaan') && !$this->db->fieldExists('realisasi_fisik', 'foto_progres_pekerjaan')) {
            $this->forge->addColumn('foto_progres_pekerjaan', [
                'realisasi_fisik' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '6,2',
                    'null'       => true,
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('realisasi_fisik', 'foto_progres_pekerjaan')) {
            $this->forge->dropColumn('foto_progres_pekerjaan', 'realisasi_fisik');
        }
    }
}