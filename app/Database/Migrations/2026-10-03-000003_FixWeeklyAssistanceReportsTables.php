<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixWeeklyAssistanceReportsTables extends Migration
{
    public function up(): void
    {
        // Dibuat dengan SQL IF NOT EXISTS agar aman jika migration sebelumnya
        // belum pernah dijalankan atau hanya sebagian yang sudah ada.
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `laporan_template_bantuan` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `sekolah_id` INT UNSIGNED NOT NULL,
                `bantuan_sekolah_id` INT UNSIGNED NOT NULL,
                `nama_template` VARCHAR(255) NOT NULL,
                `file_template` VARCHAR(255) NOT NULL,
                `keterangan` TEXT NULL,
                `diunggah_oleh` INT UNSIGNED NOT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_template_sekolah_bantuan` (`sekolah_id`, `bantuan_sekolah_id`),
                KEY `idx_template_pengunggah` (`diunggah_oleh`),
                KEY `idx_template_bantuan` (`bantuan_sekolah_id`),
                CONSTRAINT `fk_template_sekolah`
                    FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_template_bantuan`
                    FOREIGN KEY (`bantuan_sekolah_id`) REFERENCES `bantuan_sekolah` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_template_user`
                    FOREIGN KEY (`diunggah_oleh`) REFERENCES `users` (`id`)
                    ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `laporan_mingguan_bantuan` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `sekolah_id` INT UNSIGNED NOT NULL,
                `bantuan_sekolah_id` INT UNSIGNED NOT NULL,
                `minggu_ke` INT UNSIGNED NOT NULL,
                `target_rencana` DECIMAL(6,2) NULL,
                `realisasi_fisik` DECIMAL(6,2) NULL,
                `serapan_dana` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `uraian_kegiatan` TEXT NULL,
                `kendala` TEXT NULL,
                `tindak_lanjut` TEXT NULL,
                `status_verval` VARCHAR(30) NOT NULL DEFAULT 'Draft',
                `dibuat_oleh` INT UNSIGNED NOT NULL,
                `diverifikasi_oleh` INT UNSIGNED NULL,
                `diverifikasi_pada` DATETIME NULL,
                `catatan_verifikasi` TEXT NULL,
                `file_pdf` VARCHAR(255) NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_laporan_mingguan_bantuan` (`sekolah_id`, `bantuan_sekolah_id`, `minggu_ke`),
                KEY `idx_laporan_bantuan` (`bantuan_sekolah_id`),
                KEY `idx_laporan_pembuat` (`dibuat_oleh`),
                KEY `idx_laporan_verifikator` (`diverifikasi_oleh`),
                CONSTRAINT `fk_laporan_sekolah`
                    FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_laporan_bantuan`
                    FOREIGN KEY (`bantuan_sekolah_id`) REFERENCES `bantuan_sekolah` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_laporan_pembuat`
                    FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`)
                    ON DELETE RESTRICT ON UPDATE CASCADE,
                CONSTRAINT `fk_laporan_verifikator`
                    FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id`)
                    ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `laporan_mingguan_bantuan`");
        $this->db->query("DROP TABLE IF EXISTS `laporan_template_bantuan`");
    }
}
