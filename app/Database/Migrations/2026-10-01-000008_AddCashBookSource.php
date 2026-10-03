<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCashBookSource extends Migration
{
    public function up(): void
    {
        $fields = [];
        if (!$this->db->fieldExists('sumber_jenis', 'buku_keuangan')) {
            $fields['sumber_jenis'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ];
        }
        if (!$this->db->fieldExists('sumber_id', 'buku_keuangan')) {
            $fields['sumber_id'] = [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('buku_keuangan', $fields);
        }
        $this->forge->addUniqueKey(['sekolah_id', 'sumber_jenis', 'sumber_id'], 'uq_buku_keuangan_sumber');
        $this->forge->addForeignKey('sumber_id', 'buku_keuangan', 'id', 'CASCADE', 'CASCADE', 'fk_buku_keuangan_sumber');
        $this->forge->processIndexes('buku_keuangan');
    }

    public function down(): void
    {
        foreach (['sumber_jenis', 'sumber_id'] as $field) {
            if ($this->db->fieldExists($field, 'buku_keuangan')) {
                $this->forge->dropColumn('buku_keuangan', $field);
            }
        }
    }
}
