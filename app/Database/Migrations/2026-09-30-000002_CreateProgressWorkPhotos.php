<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProgressWorkPhotos extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('foto_progres_pekerjaan')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'progres_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'bantuan_sekolah_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'foto_depan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'foto_belakang' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'foto_dalam' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['progres_id', 'bantuan_sekolah_id'], 'uk_foto_progres_pekerjaan');
        $this->forge->addForeignKey('progres_id', 'progres_mingguan', 'id', 'CASCADE', 'CASCADE', 'fk_foto_progres_laporan');
        $this->forge->addForeignKey('bantuan_sekolah_id', 'bantuan_sekolah', 'id', 'CASCADE', 'CASCADE', 'fk_foto_progres_bantuan');
        $this->forge->createTable('foto_progres_pekerjaan');
    }

    public function down(): void
    {
        $this->forge->dropTable('foto_progres_pekerjaan', true);
    }
}