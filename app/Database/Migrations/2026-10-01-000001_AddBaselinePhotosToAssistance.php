<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBaselinePhotosToAssistance extends Migration
{
    public function up(): void
    {
        $fields = [];
        foreach (['depan', 'belakang', 'dalam'] as $angle) {
            $fields['foto_0_' . $angle] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
        }

        $this->forge->addColumn('bantuan_sekolah', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('bantuan_sekolah', ['foto_0_depan', 'foto_0_belakang', 'foto_0_dalam']);
    }
}