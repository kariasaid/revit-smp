<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateP2spTeam extends Migration
{
    public function up(): void
    {
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
            'posisi' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'nip_nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'ttd' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['sekolah_id', 'posisi'], 'uk_tim_p2sp_sekolah_posisi');
        $this->forge->addForeignKey('sekolah_id', 'sekolah', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tim_p2sp');
    }

    public function down(): void
    {
        $this->forge->dropTable('tim_p2sp', true);
    }
}