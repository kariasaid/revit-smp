<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CorrectScheduleCreatorForeignKey extends Migration
{
    public function up(): void
    {
        $foreignKey = $this->getCreatorForeignKey();

        if (!$foreignKey) {
            return;
        }

        if ($foreignKey['DELETE_RULE'] === 'SET NULL' && $foreignKey['UPDATE_RULE'] === 'CASCADE') {
            return;
        }

        $constraintName = str_replace('`', '``', $foreignKey['CONSTRAINT_NAME']);
        $this->db->query('ALTER TABLE `rencana_mingguan` DROP FOREIGN KEY `' . $constraintName . '`');
        $this->db->query(
            'ALTER TABLE `rencana_mingguan`
             ADD CONSTRAINT `fk_rencana_perencana_user`
             FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`)
             ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    public function down(): void
    {
        $foreignKey = $this->getCreatorForeignKey();
        if (!$foreignKey || ($foreignKey['CONSTRAINT_NAME'] === 'rencana_mingguan_dibuat_oleh_foreign'
            && $foreignKey['DELETE_RULE'] === 'SET NULL'
            && $foreignKey['UPDATE_RULE'] === 'CASCADE')) {
            return;
        }

        $constraintName = str_replace('`', '``', $foreignKey['CONSTRAINT_NAME']);
        $this->db->query('ALTER TABLE `rencana_mingguan` DROP FOREIGN KEY `' . $constraintName . '`');
        $this->db->query(
            'ALTER TABLE `rencana_mingguan`
             ADD CONSTRAINT `rencana_mingguan_dibuat_oleh_foreign`
             FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`)
             ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    private function getCreatorForeignKey(): ?array
    {
        return $this->db->query(
            'SELECT rc.CONSTRAINT_NAME, rc.DELETE_RULE, rc.UPDATE_RULE
             FROM information_schema.REFERENTIAL_CONSTRAINTS rc
             JOIN information_schema.KEY_COLUMN_USAGE kcu
               ON kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
              AND kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
              AND kcu.TABLE_NAME = rc.TABLE_NAME
             WHERE rc.CONSTRAINT_SCHEMA = DATABASE()
               AND rc.TABLE_NAME = ?
               AND kcu.COLUMN_NAME = ?
               AND kcu.REFERENCED_TABLE_NAME = ?',
            ['rencana_mingguan', 'dibuat_oleh', 'users']
        )->getRowArray();
    }
}
