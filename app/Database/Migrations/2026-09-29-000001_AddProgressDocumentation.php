<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProgressDocumentation extends Migration
{
    private array $photoColumns = ['foto_depan', 'foto_belakang', 'foto_dalam'];

    public function up(): void
    {
        foreach ($this->photoColumns as $column) {
            if (!$this->db->fieldExists($column, 'progres_mingguan')) {
                $this->forge->addColumn('progres_mingguan', [
                    $column => [
                        'type'       => 'VARCHAR',
                        'constraint' => 255,
                        'null'       => true,
                    ],
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->photoColumns as $column) {
            if ($this->db->fieldExists($column, 'progres_mingguan')) {
                $this->forge->dropColumn('progres_mingguan', $column);
            }
        }
    }
}
