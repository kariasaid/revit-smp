<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWeeklySchedule extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('rencana_mingguan')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sekolah_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'minggu_ke' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
            ],
            'tanggal_mulai' => ['type' => 'DATE'],
            'tanggal_selesai' => ['type' => 'DATE'],
            'target_rencana' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,2',
                'default'    => 0,
            ],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'dibuat_oleh' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['sekolah_id', 'minggu_ke'], 'uk_rencana_sekolah_minggu');
        $this->forge->addForeignKey('sekolah_id', 'sekolah', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('dibuat_oleh', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('rencana_mingguan', true, [
            'ENGINE'        => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE'       => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('rencana_mingguan', true);
    }
}
