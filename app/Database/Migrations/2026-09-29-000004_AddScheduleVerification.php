<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddScheduleVerification extends Migration
{
    public function up(): void
    {
        $fields = [
            'status_verval' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'Draft',
            ],
            'diverifikasi_oleh' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'diverifikasi_pada' => ['type' => 'DATETIME', 'null' => true],
            'catatan_verifikasi' => ['type' => 'TEXT', 'null' => true],
        ];
        foreach (array_keys($fields) as $field) {
            if ($this->db->fieldExists($field, 'rencana_mingguan')) {
                unset($fields[$field]);
            }
        }
        if ($fields !== []) {
            $this->forge->addColumn('rencana_mingguan', $fields);
        }

        $foreignKeyCount = $this->db->query(
            'SELECT COUNT(*) AS total
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME = ?',
            ['rencana_mingguan', 'diverifikasi_oleh', 'users']
        )->getRowArray()['total'];
        if ((int) $foreignKeyCount === 0) {
            $this->forge->addForeignKey(
                'diverifikasi_oleh',
                'users',
                'id',
                'CASCADE',
                'SET NULL',
                'fk_rencana_diverifikasi_oleh'
            );
        }

        $this->db->table('rencana_mingguan')
            ->set('status_verval', 'Diajukan')
            ->update();
    }

    public function down(): void
    {
        $foreignKeys = $this->db->query(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME = ?',
            ['rencana_mingguan', 'diverifikasi_oleh', 'users']
        )->getResultArray();
        foreach ($foreignKeys as $foreignKey) {
            $this->forge->dropForeignKey('rencana_mingguan', $foreignKey['CONSTRAINT_NAME']);
        }

        foreach (['status_verval', 'diverifikasi_oleh', 'diverifikasi_pada', 'catatan_verifikasi'] as $field) {
            if ($this->db->fieldExists($field, 'rencana_mingguan')) {
                $this->forge->dropColumn('rencana_mingguan', $field);
            }
        }
    }
}
