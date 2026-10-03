<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFinanceDocuments extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'sekolah_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'kategori' => ['type' => 'VARCHAR', 'constraint' => 30],
            'tanggal_kuitansi' => ['type' => 'DATE'],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 250],
            'file_pdf' => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['sekolah_id', 'kategori', 'tanggal_kuitansi'], false, false, 'idx_dokumen_keuangan_sekolah_tanggal');
        $this->forge->addForeignKey('sekolah_id', 'sekolah', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('dokumen_keuangan');
    }

    public function down(): void
    {
        $this->forge->dropTable('dokumen_keuangan', true);
    }
}
