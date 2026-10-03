<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWeeklyReportPdf extends Migration
{
    public function up(): void
    {
        if (!$this->db->fieldExists('pdf_laporan', 'progres_mingguan')) {
            $this->forge->addColumn('progres_mingguan', [
                'pdf_laporan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'keterangan',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('pdf_laporan', 'progres_mingguan')) {
            $this->forge->dropColumn('progres_mingguan', 'pdf_laporan');
        }
    }
}