-- ============================================================
-- REVIT SMP - Sistem Informasi Bantuan Revitalisasi Sekolah
-- Schema Database MySQL (XAMPP)
-- Versi: 1.0 | Tahun: 2026
-- ============================================================

CREATE DATABASE IF NOT EXISTS `revit_smp` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `revit_smp`;

-- ------------------------------------------------------------
-- Tabel: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(150) NOT NULL,
  `nik` VARCHAR(20) DEFAULT NULL,
  `nip` VARCHAR(30) DEFAULT NULL,
  `no_hp` VARCHAR(20) DEFAULT NULL,
  `npwp` VARCHAR(30) DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `ttd` VARCHAR(255) DEFAULT NULL,
  `role` ENUM('admin','pengawas','perencana','reviewer','fasilitator','ta_pusat') NOT NULL DEFAULT 'pengawas',
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: sekolah
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sekolah` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_sekolah` VARCHAR(200) NOT NULL,
  `npsn` VARCHAR(20) NOT NULL,
  `provinsi` VARCHAR(100) DEFAULT NULL,
  `kab_kota` VARCHAR(100) DEFAULT NULL,
  `dana_diterima` DECIMAL(15,2) DEFAULT 0.00,
  `total_minggu` TINYINT UNSIGNED DEFAULT 16,
  `pengawas_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_npsn` (`npsn`),
  KEY `fk_sekolah_pengawas` (`pengawas_id`),
  CONSTRAINT `fk_sekolah_pengawas` FOREIGN KEY (`pengawas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: bantuan_sekolah
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bantuan_sekolah` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sekolah_id` INT UNSIGNED NOT NULL,
  `nama_bantuan` VARCHAR(150) NOT NULL,
  `volume` DECIMAL(12,2) DEFAULT NULL,
  `satuan_volume` VARCHAR(30) DEFAULT NULL,
  `foto_0_depan` VARCHAR(255) DEFAULT NULL,
  `foto_0_belakang` VARCHAR(255) DEFAULT NULL,
  `foto_0_dalam` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bantuan_sekolah_nama` (`sekolah_id`, `nama_bantuan`),
  CONSTRAINT `fk_bantuan_sekolah` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: personil_sekolah
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `personil_sekolah` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sekolah_id` INT UNSIGNED NOT NULL,
  `kepala_sekolah` VARCHAR(150) DEFAULT NULL,
  `hp_kepala_sekolah` VARCHAR(20) DEFAULT NULL,
  `perencana` VARCHAR(150) DEFAULT NULL,
  `hp_perencana` VARCHAR(20) DEFAULT NULL,
  `pengawas` VARCHAR(150) DEFAULT NULL,
  `hp_pengawas` VARCHAR(20) DEFAULT NULL,
  `reviewer` VARCHAR(150) DEFAULT NULL,
  `hp_reviewer` VARCHAR(20) DEFAULT NULL,
  `fasilitator` VARCHAR(150) DEFAULT NULL,
  `hp_fasilitator` VARCHAR(20) DEFAULT NULL,
  `ta_pusat` VARCHAR(150) DEFAULT NULL,
  `hp_ta_pusat` VARCHAR(20) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sekolah` (`sekolah_id`),
  CONSTRAINT `fk_personil_sekolah` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: tim_p2sp
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tim_p2sp` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sekolah_id` INT UNSIGNED NOT NULL,
  `posisi` VARCHAR(40) NOT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `nip_nik` VARCHAR(30) NOT NULL,
  `jabatan` VARCHAR(150) NOT NULL,
  `ttd` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tim_p2sp_sekolah_posisi` (`sekolah_id`, `posisi`),
  CONSTRAINT `fk_tim_p2sp_sekolah` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: rencana_mingguan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rencana_mingguan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sekolah_id` INT UNSIGNED NOT NULL,
  `minggu_ke` TINYINT UNSIGNED NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `target_rencana` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `keterangan` TEXT DEFAULT NULL,
  `dibuat_oleh` INT UNSIGNED DEFAULT NULL,
  `status_verval` VARCHAR(20) NOT NULL DEFAULT 'Draft',
  `diverifikasi_oleh` INT UNSIGNED DEFAULT NULL,
  `diverifikasi_pada` DATETIME DEFAULT NULL,
  `catatan_verifikasi` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rencana_sekolah_minggu` (`sekolah_id`, `minggu_ke`),
  CONSTRAINT `fk_rencana_sekolah` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rencana_perencana` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_rencana_verifikasi` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: rencana_mingguan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rencana_mingguan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sekolah_id` INT UNSIGNED NOT NULL,
  `minggu_ke` TINYINT UNSIGNED NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `target_rencana` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `keterangan` TEXT DEFAULT NULL,
  `dibuat_oleh` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rencana_sekolah_minggu` (`sekolah_id`, `minggu_ke`),
  CONSTRAINT `fk_rencana_sekolah` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rencana_perencana` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: progres_mingguan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `progres_mingguan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sekolah_id` INT UNSIGNED NOT NULL,
  `minggu_ke` TINYINT UNSIGNED NOT NULL COMMENT '1 s/d 16',
  `serapan_dana` DECIMAL(15,2) DEFAULT 0.00,
  `target_rencana` DECIMAL(6,2) DEFAULT 0.00 COMMENT 'Persentase target rencana minggu ini',
  `realisasi_fisik` DECIMAL(6,2) DEFAULT 0.00 COMMENT 'Persentase realisasi fisik minggu ini',
  `deviasi` DECIMAL(6,2) DEFAULT 0.00 COMMENT 'realisasi_fisik - target_rencana',
  `status_verval` ENUM('Draft','Diajukan','Diterima','Ditolak') NOT NULL DEFAULT 'Draft',
  `keterangan` TEXT DEFAULT NULL,
  `pdf_laporan` VARCHAR(255) DEFAULT NULL COMMENT 'PDF laporan mingguan dari pengawas',
  `foto_depan` VARCHAR(255) DEFAULT NULL COMMENT 'Foto dokumentasi tampak depan',
  `foto_belakang` VARCHAR(255) DEFAULT NULL COMMENT 'Foto dokumentasi tampak belakang',
  `foto_dalam` VARCHAR(255) DEFAULT NULL COMMENT 'Foto dokumentasi bagian dalam bangunan',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sekolah_minggu` (`sekolah_id`, `minggu_ke`),
  CONSTRAINT `fk_progres_sekolah` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: foto_progres_pekerjaan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `foto_progres_pekerjaan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `progres_id` INT UNSIGNED NOT NULL,
  `bantuan_sekolah_id` INT UNSIGNED NOT NULL,
  `realisasi_fisik` DECIMAL(6,2) DEFAULT NULL,
  `foto_depan` VARCHAR(255) DEFAULT NULL,
  `foto_belakang` VARCHAR(255) DEFAULT NULL,
  `foto_dalam` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_foto_progres_pekerjaan` (`progres_id`, `bantuan_sekolah_id`),
  CONSTRAINT `fk_foto_progres_laporan` FOREIGN KEY (`progres_id`) REFERENCES `progres_mingguan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_foto_progres_bantuan` FOREIGN KEY (`bantuan_sekolah_id`) REFERENCES `bantuan_sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: dokumen_pelaporan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dokumen_pelaporan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sekolah_id` INT UNSIGNED NOT NULL,
  `jenis_pelaporan` ENUM('50%','100%') NOT NULL,
  `nama_dokumen` VARCHAR(200) NOT NULL,
  `file_template` VARCHAR(255) DEFAULT NULL COMMENT 'Path template PDF',
  `file_unggah` VARCHAR(255) DEFAULT NULL COMMENT 'Path file yang diunggah',
  `status_unggah` ENUM('Belum Unggah','Sudah Unggah') NOT NULL DEFAULT 'Belum Unggah',
  `status_validasi` ENUM('-','Menunggu','Diterima','Ditolak') NOT NULL DEFAULT '-',
  `urutan` TINYINT UNSIGNED DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sekolah_jenis` (`sekolah_id`, `jenis_pelaporan`),
  CONSTRAINT `fk_dokumen_sekolah` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: buku_keuangan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `buku_keuangan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sekolah_id` INT UNSIGNED NOT NULL,
  `jenis_buku` VARCHAR(20) NOT NULL,
  `tanggal` DATE NOT NULL,
  `nomor_bukti` VARCHAR(80) DEFAULT NULL,
  `uraian` VARCHAR(250) NOT NULL,
  `penerimaan` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `pengeluaran` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `sumber_jenis` VARCHAR(20) DEFAULT NULL,
  `sumber_id` INT UNSIGNED DEFAULT NULL,
  `jenis_biaya` VARCHAR(30) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_buku_keuangan_sekolah_tanggal` (`sekolah_id`, `jenis_buku`, `tanggal`),
  UNIQUE KEY `uq_buku_keuangan_sumber` (`sekolah_id`, `sumber_jenis`, `sumber_id`),
  CONSTRAINT `fk_buku_keuangan_sekolah` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_buku_keuangan_sumber` FOREIGN KEY (`sumber_id`) REFERENCES `buku_keuangan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: bahan_bangunan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bahan_bangunan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sekolah_id` INT UNSIGNED NOT NULL,
  `tanggal` DATE NOT NULL,
  `nama_bahan` VARCHAR(150) NOT NULL,
  `volume` DECIMAL(12,2) NOT NULL,
  `satuan` VARCHAR(30) NOT NULL,
  `harga_satuan` DECIMAL(15,2) NOT NULL,
  `total` DECIMAL(15,2) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bahan_bangunan_sekolah_tanggal` (`sekolah_id`, `tanggal`),
  CONSTRAINT `fk_bahan_bangunan_sekolah` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: ongkos_tukang
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ongkos_tukang` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sekolah_id` INT UNSIGNED NOT NULL,
  `tanggal` DATE NOT NULL,
  `pekerjaan` VARCHAR(200) NOT NULL,
  `penerima` VARCHAR(150) NOT NULL,
  `volume` DECIMAL(12,2) NOT NULL,
  `satuan` VARCHAR(30) NOT NULL,
  `tarif` DECIMAL(15,2) NOT NULL,
  `total` DECIMAL(15,2) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ongkos_tukang_sekolah_tanggal` (`sekolah_id`, `tanggal`),
  CONSTRAINT `fk_ongkos_tukang_sekolah` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: dokumen_keuangan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dokumen_keuangan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sekolah_id` INT UNSIGNED NOT NULL,
  `kategori` VARCHAR(30) NOT NULL,
  `tanggal_kuitansi` DATE NOT NULL,
  `keterangan` VARCHAR(250) NOT NULL,
  `file_pdf` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dokumen_keuangan_sekolah_tanggal` (`sekolah_id`, `kategori`, `tanggal_kuitansi`),
  CONSTRAINT `fk_dokumen_keuangan_sekolah` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- DATA DUMMY (sesuai screenshot)
-- ------------------------------------------------------------

-- User Pengawas
INSERT INTO `users` (`username`, `email`, `password`, `nama_lengkap`, `nik`, `nip`, `no_hp`, `npwp`, `role`) VALUES
('meindrawan', '5108060105820014@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'I Gusti Putu Meindrawan, ST', '5108060105820014', NULL, '081916124068', '448896894902000', 'pengawas'),
('admin', 'admin@kemendikdasmen.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Sistem', NULL, NULL, NULL, NULL, 'admin'),
('perencana', 'perencana@revit-smp.id', '$2y$10$yeiRp/8I/JUZ7tUr3Z5JtuxWEpyw8o.tTL1GJHMcpmSfJl3gHBXSG', 'Gede Andrea Kusumawardhana, S.S.T.Spl', NULL, NULL, NULL, NULL, 'perencana');
-- Password demo pengawas/admin: password
-- Akun perencana: username perencana, password RencanaSMP#2026

-- Sekolah
INSERT INTO `sekolah` (`nama_sekolah`, `npsn`, `provinsi`, `kab_kota`, `dana_diterima`, `total_minggu`, `pengawas_id`) VALUES
('SMP NEGERI 1 SERIRIT', '50100321', 'Prov. Bali', 'Kab. Buleleng', 786779000.00, 16, 1);

-- Personil Sekolah
INSERT INTO `personil_sekolah` (`sekolah_id`, `kepala_sekolah`, `hp_kepala_sekolah`, `perencana`, `hp_perencana`, `pengawas`, `hp_pengawas`, `reviewer`, `hp_reviewer`, `fasilitator`, `hp_fasilitator`, `ta_pusat`, `hp_ta_pusat`) VALUES
(1, 'Desak Putu Widiani, S.Pd', '087762209498', 'Gede Andrea Kusumawardhana, S.S.T.Spl', '081239691500', 'I Gusti Putu Meindrawan, ST', '081916124068', 'Ferdinan Agrifa', '082216529667', 'I Kadek Badradnyana Wirapraja Mahatama, ST.,MT.', '082237093330', 'Asep M Hidayat', '081234540897');

-- Progres Mingguan (Minggu 1-8 sesuai screenshot)
INSERT INTO `progres_mingguan` (`sekolah_id`, `minggu_ke`, `serapan_dana`, `target_rencana`, `realisasi_fisik`, `deviasi`, `status_verval`) VALUES
(1, 1, 0.00, 1.33, 0.00, -1.33, 'Diterima'),
(1, 2, 12355000.00, 2.12, 1.57, -0.55, 'Diterima'),
(1, 3, 19446991.00, 4.39, 2.47, -1.92, 'Diterima'),
(1, 4, 67091942.00, 5.63, 8.53, 2.90, 'Diterima'),
(1, 5, 73643416.00, 6.55, 9.36, 2.81, 'Diterima'),
(1, 6, 33542905.00, 8.22, 4.26, -3.96, 'Diterima'),
(1, 7, 110572733.00, 8.95, 14.05, 5.10, 'Diterima'),
(1, 8, 80130038.00, 8.95, 10.18, 1.23, 'Diterima');

-- Dokumen Pelaporan 50%
INSERT INTO `dokumen_pelaporan` (`sekolah_id`, `jenis_pelaporan`, `nama_dokumen`, `file_template`, `status_unggah`, `status_validasi`, `urutan`) VALUES
(1, '50%', 'Laporan Kemajuan Progres', 'templates/laporan_kemajuan_progres.pdf', 'Belum Unggah', '-', 1),
(1, '50%', 'Rekap Progres 50%', 'templates/rekap_progres_50.pdf', 'Belum Unggah', '-', 2),
(1, '50%', 'Berita Acara Review tahap 2', 'templates/ba_review_tahap2.pdf', 'Belum Unggah', '-', 3),
(1, '50%', 'Dokumentasi 50%', 'templates/dokumentasi_50.pdf', 'Belum Unggah', '-', 4),
(1, '50%', 'RPD 30%', NULL, 'Belum Unggah', '-', 5),
(1, '50%', 'Rekening Koran', NULL, 'Belum Unggah', '-', 6);

-- Dokumen Pelaporan 100%
INSERT INTO `dokumen_pelaporan` (`sekolah_id`, `jenis_pelaporan`, `nama_dokumen`, `file_template`, `status_unggah`, `status_validasi`, `urutan`) VALUES
(1, '100%', 'Laporan Akhir Pelaksanaan', 'templates/laporan_akhir.pdf', 'Belum Unggah', '-', 1),
(1, '100%', 'Rekap Progres 100%', 'templates/rekap_progres_100.pdf', 'Belum Unggah', '-', 2),
(1, '100%', 'Berita Acara Serah Terima', 'templates/ba_serah_terima.pdf', 'Belum Unggah', '-', 3),
(1, '100%', 'Dokumentasi 100%', 'templates/dokumentasi_100.pdf', 'Belum Unggah', '-', 4),
(1, '100%', 'RPD 100%', NULL, 'Belum Unggah', '-', 5),
(1, '100%', 'Rekening Koran Final', NULL, 'Belum Unggah', '-', 6);

-- ------------------------------------------------------------
-- VIEW helper (opsional) untuk akumulasi Kurva S
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW `v_kurva_s` AS
SELECT 
  p.sekolah_id,
  p.minggu_ke,
  p.target_rencana AS rencana,
  SUM(p.target_rencana) OVER (PARTITION BY p.sekolah_id ORDER BY p.minggu_ke) AS akumulasi_rencana,
  p.realisasi_fisik AS realisasi,
  SUM(p.realisasi_fisik) OVER (PARTITION BY p.sekolah_id ORDER BY p.minggu_ke) AS akumulasi_realisasi,
  p.deviasi AS deviasi_mingguan,
  SUM(p.deviasi) OVER (PARTITION BY p.sekolah_id ORDER BY p.minggu_ke) AS akumulasi_deviasi
FROM progres_mingguan p
ORDER BY p.sekolah_id, p.minggu_ke;

-- ------------------------------------------------------------
-- Tabel: adendum
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `adendum` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sekolah_id` INT UNSIGNED NOT NULL,
  `nomor_adendum` VARCHAR(50) NOT NULL,
  `tanggal` DATE NOT NULL,
  `perihal` VARCHAR(255) NOT NULL,
  `uraian` TEXT DEFAULT NULL,
  `nilai_perubahan` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Positif=tambah, Negatif=kurang',
  `file_adendum` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Draft','Diajukan','Disetujui','Ditolak') NOT NULL DEFAULT 'Draft',
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_adendum_sekolah` (`sekolah_id`),
  CONSTRAINT `fk_adendum_sekolah` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dummy adendum
INSERT INTO `adendum` (`sekolah_id`, `nomor_adendum`, `tanggal`, `perihal`, `uraian`, `nilai_perubahan`, `status`, `created_by`) VALUES
(1, 'ADN/001/REVIT/2026', '2026-03-15', 'Penyesuaian volume pekerjaan atap', 'Perubahan volume penutup atap dari 450 m2 menjadi 480 m2 karena penyesuaian pengukuran ulang di lapangan.', 12500000.00, 'Disetujui', 1),
(1, 'ADN/002/REVIT/2026', '2026-05-20', 'Pengurangan item pekerjaan pagar', 'Item pagar depan dihapus dari lingkup karena sudah dikerjakan pihak ketiga.', -8500000.00, 'Diajukan', 1);
