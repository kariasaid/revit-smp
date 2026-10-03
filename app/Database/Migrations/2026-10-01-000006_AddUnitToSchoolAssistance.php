<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnitToSchoolAssistance extends Migration
{
    public function up(): void
    {
        if (!$this->db->fieldExists('satuan_volume', 'bantuan_sekolah')) {
            $this->forge->addColumn('bantuan_sekolah', [
                'satuan_volume' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                    'after'      => 'volume',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('satuan_volume', 'bantuan_sekolah')) {
            $this->forge->dropColumn('bantuan_sekolah', 'satuan_volume');
        }
    }
}