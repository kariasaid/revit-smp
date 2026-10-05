<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWeeklyAssistanceReports extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('laporan_template_bantuan')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'sekolah_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
                'bantuan_sekolah_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
                'nama_template' => ['type' => 'VARCHAR', 'constraint' => 255],
                'file_template' => ['type' => 'VARCHAR', 'constraint' => 255],
                'keterangan' => ['type' => 'TEXT', 'null' => true],
                'diunggah_oleh' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['sekolah_id', 'bantuan_sekolah_id'], 'uk_template_sekolah_bantuan');
            $this->forge->addKey('diunggah_oleh');
            $this->forge->addForeignKey('sekolah_id', 'sekolah', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('bantuan_sekolah_id', 'bantuan_sekolah', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('diunggah_oleh', 'users', 'id', 'RESTRICT', 'CASCADE');
            $this->forge->createTable('laporan_template_bantuan');
        }

        if (!$this->db->tableExists('laporan_mingguan_bantuan')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'sekolah_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
                'bantuan_sekolah_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
                'minggu_ke' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
                'target_rencana' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => true],
                'realisasi_fisik' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => true],
                'serapan_dana' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
                'uraian_kegiatan' => ['type' => 'TEXT', 'null' => true],
                'kendala' => ['type' => 'TEXT', 'null' => true],
                'tindak_lanjut' => ['type' => 'TEXT', 'null' => true],
                'status_verval' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'Draft'],
                'dibuat_oleh' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
                'diverifikasi_oleh' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
                'diverifikasi_pada' => ['type' => 'DATETIME', 'null' => true],
                'catatan_verifikasi' => ['type' => 'TEXT', 'null' => true],
                'file_pdf' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['sekolah_id', 'bantuan_sekolah_id', 'minggu_ke'], 'uk_laporan_mingguan_bantuan');
            $this->forge->addForeignKey('sekolah_id', 'sekolah', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('bantuan_sekolah_id', 'bantuan_sekolah', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('dibuat_oleh', 'users', 'id', 'RESTRICT', 'CASCADE');
            $this->forge->addForeignKey('diverifikasi_oleh', 'users', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('laporan_mingguan_bantuan');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('laporan_mingguan_bantuan', true);
        $this->forge->dropTable('laporan_template_bantuan', true);
    }
}
