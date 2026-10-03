<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVolumeToSchoolAssistance extends Migration
{
    public function up(): void
    {
        if (!$this->db->fieldExists('volume', 'bantuan_sekolah')) {
            $this->forge->addColumn('bantuan_sekolah', [
                'volume' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'null'       => true,
                    'after'      => 'nama_bantuan',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('volume', 'bantuan_sekolah')) {
            $this->forge->dropColumn('bantuan_sekolah', 'volume');
        }
    }
}