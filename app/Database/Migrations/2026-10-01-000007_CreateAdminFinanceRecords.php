<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminFinanceRecords extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'sekolah_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'jenis_buku' => ['type' => 'VARCHAR', 'constraint' => 20],
            'tanggal' => ['type' => 'DATE'],
            'nomor_bukti' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'uraian' => ['type' => 'VARCHAR', 'constraint' => 250],
            'penerimaan' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'pengeluaran' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['sekolah_id', 'jenis_buku', 'tanggal'], false, false, 'idx_buku_keuangan_sekolah_tanggal');
        $this->forge->addForeignKey('sekolah_id', 'sekolah', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('buku_keuangan');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'sekolah_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'tanggal' => ['type' => 'DATE'],
            'nama_bahan' => ['type' => 'VARCHAR', 'constraint' => 150],
            'volume' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'satuan' => ['type' => 'VARCHAR', 'constraint' => 30],
            'harga_satuan' => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'total' => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['sekolah_id', 'tanggal']);
        $this->forge->addForeignKey('sekolah_id', 'sekolah', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('bahan_bangunan');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'sekolah_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'tanggal' => ['type' => 'DATE'],
            'pekerjaan' => ['type' => 'VARCHAR', 'constraint' => 200],
            'penerima' => ['type' => 'VARCHAR', 'constraint' => 150],
            'volume' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'satuan' => ['type' => 'VARCHAR', 'constraint' => 30],
            'tarif' => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'total' => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['sekolah_id', 'tanggal']);
        $this->forge->addForeignKey('sekolah_id', 'sekolah', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ongkos_tukang');
    }

    public function down(): void
    {
        $this->forge->dropTable('ongkos_tukang', true);
        $this->forge->dropTable('bahan_bangunan', true);
        $this->forge->dropTable('buku_keuangan', true);
    }
}
