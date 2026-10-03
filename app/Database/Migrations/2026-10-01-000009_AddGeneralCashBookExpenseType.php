<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGeneralCashBookExpenseType extends Migration
{
    public function up(): void
    {
        if (!$this->db->fieldExists('jenis_biaya', 'buku_keuangan')) {
            $this->forge->addColumn('buku_keuangan', [
                'jenis_biaya' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                    'after'      => 'sumber_id',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('jenis_biaya', 'buku_keuangan')) {
            $this->forge->dropColumn('buku_keuangan', 'jenis_biaya');
        }
    }
}
