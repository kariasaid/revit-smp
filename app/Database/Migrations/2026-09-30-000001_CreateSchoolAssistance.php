<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSchoolAssistance extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('bantuan_sekolah')) {
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
            'nama_bantuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['sekolah_id', 'nama_bantuan'], 'uk_bantuan_sekolah_nama');
        $this->forge->addForeignKey('sekolah_id', 'sekolah', 'id', 'CASCADE', 'CASCADE', 'fk_bantuan_sekolah');
        $this->forge->createTable('bantuan_sekolah');
    }

    public function down(): void
    {
        $this->forge->dropTable('bantuan_sekolah', true);
    }
}