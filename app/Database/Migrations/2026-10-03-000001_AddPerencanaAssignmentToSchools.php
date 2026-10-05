<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerencanaAssignmentToSchools extends Migration
{
    public function up(): void
    {
        if (!$this->db->fieldExists('perencana_id', 'sekolah')) {
            $this->forge->addColumn('sekolah', [
                'perencana_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'pengawas_id',
                ],
            ]);
        }

        $indexExists = false;
        foreach ($this->db->query('SHOW INDEX FROM `sekolah`')->getResultArray() as $index) {
            if (($index['Key_name'] ?? '') === 'fk_sekolah_perencana') {
                $indexExists = true;
                break;
            }
        }
        if (!$indexExists) {
            $this->db->query('ALTER TABLE `sekolah` ADD KEY `fk_sekolah_perencana` (`perencana_id`)');
        }

        $foreignKeyExists = false;
        $fkCheck = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sekolah' AND CONSTRAINT_NAME = 'fk_sekolah_perencana'")->getRowArray();
        $foreignKeyExists = !empty($fkCheck);
        if (!$foreignKeyExists) {
            $this->db->query('ALTER TABLE `sekolah` ADD CONSTRAINT `fk_sekolah_perencana` FOREIGN KEY (`perencana_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
        }
        $sql = <<<SQL
UPDATE `sekolah` s
INNER JOIN `personil_sekolah` ps ON ps.`sekolah_id` = s.`id`
INNER JOIN `users` u ON u.`nama_lengkap` = ps.`perencana` AND u.`role` = 'perencana'
SET s.`perencana_id` = u.`id`
WHERE s.`perencana_id` IS NULL
SQL;
        $this->db->query($sql);
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `sekolah` DROP FOREIGN KEY `fk_sekolah_perencana`');
        $this->db->query('ALTER TABLE `sekolah` DROP KEY `fk_sekolah_perencana`');
        $this->forge->dropColumn('sekolah', 'perencana_id');
    }
}
