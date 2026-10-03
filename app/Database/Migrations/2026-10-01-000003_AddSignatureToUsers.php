<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSignatureToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'ttd' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'foto',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'ttd');
    }
}