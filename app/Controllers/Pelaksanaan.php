<?php

namespace App\Controllers;

use App\Libraries\ProgressPhotoCompressor;
use App\Models\RencanaMingguanModel;
use App\Models\SekolahModel;
use App\Models\ProgresMingguanModel;
use App\Models\PersonilSekolahModel;
use App\Models\TimP2SPModel;
use App\Models\UserModel;
use Config\Database;

class Pelaksanaan extends BaseController
{
    public function progres($sekolahId = null)
    {
        if (session()->get('role') === 'admin') {
            return redirect()->to('/validasi/progres');
        }

        if (session()->get('role') !== 'pengawas') {
            return redirect()->to('/dashboard')->with('error', 'Halaman ini khusus untuk pengawas.');
        }

        $userId = session()->get('id');
        $sekolahModel = new SekolahModel();
        $progresModel = new ProgresMingguanModel();
        $personilModel = new PersonilSekolahModel();

        $sekolahList = $sekolahModel->getByPengawas($userId);
        if (empty($sekolahList)) {
            return redirect()->to('/dashboard')->with('error', 'Tidak ada sekolah kelolaan.');
        }

        $sekolahId = $sekolahId ?? $sekolahList[0]['id'];
        $sekolah = $sekolahModel->find($sekolahId);
        $personil = $personilModel->getBySekolah($sekolahId);
        $progres = $progresModel->getBySekolah($sekolahId);
        $summary = $progresModel->getSummary($sekolahId);
        $realisasiPekerjaanByProgress = [];
        if ($progres !== []) {
            $workProgressRows = Database::connect()->table('foto_progres_pekerjaan')
                ->select('foto_progres_pekerjaan.progres_id, foto_progres_pekerjaan.realisasi_fisik, bantuan_sekolah.nama_bantuan')
                ->join('bantuan_sekolah', 'bantuan_sekolah.id = foto_progres_pekerjaan.bantuan_sekolah_id')
                ->whereIn('foto_progres_pekerjaan.progres_id', array_column($progres, 'id'))
                ->orderBy('bantuan_sekolah.nama_bantuan', 'ASC')
                ->get()
                ->getResultArray();
            foreach ($workProgressRows as $workProgress) {
                $realisasiPekerjaanByProgress[$workProgress['progres_id']][] = $workProgress;
            }
        }

        $data = [
            'title'       => 'Progres Pelaksanaan',
            'activeMenu'  => 'progres',
            'sekolah'     => $sekolah,
            'personil'    => $personil,
            'progres'     => $progres,
            'summary'     => $summary,
            'realisasiPekerjaanByProgress' => $realisasiPekerjaanByProgress,
            'sekolahList' => $sekolahList,
        ];

        return view('pelaksanaan/progres', $data);
    }

    public function lihatProgres(int $id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'pengawas', 'perencana'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Halaman ini hanya dapat diakses admin, pengawas, dan perencana.');
        }

        $progres = (new ProgresMingguanModel())->find($id);
        if (!$progres || ($role !== 'admin' && $progres['status_verval'] !== 'Diterima')) {
            $redirect = match ($role) {
                'admin' => '/validasi/progres',
                'pengawas' => '/pelaksanaan/progres',
                default => '/perencana/monitoring-progres',
            };
            return redirect()->to($redirect)->with('error', 'Detail hanya tersedia untuk laporan yang sudah diterima admin.');
        }

        $sekolahModel = new SekolahModel();
        $sekolah = $sekolahModel->find((int) $progres['sekolah_id']);
        if (!$sekolah) {
            return redirect()->to('/dashboard')->with('error', 'Sekolah laporan tidak ditemukan.');
        }

        if ($role === 'pengawas' && (int) $sekolah['pengawas_id'] !== (int) session()->get('id')) {
            return redirect()->to('/dashboard')->with('error', 'Laporan bukan berasal dari sekolah kelolaan Anda.');
        }
        if ($role === 'perencana') {
            $assignedSchoolIds = array_map(
                static fn (array $assignedSchool): int => (int) $assignedSchool['id'],
                $sekolahModel->getByPerencana((string) session()->get('nama_lengkap'))
            );
            if (!in_array((int) $sekolah['id'], $assignedSchoolIds, true)) {
                return redirect()->to('/dashboard')->with('error', 'Laporan bukan berasal dari sekolah penugasan Anda.');
            }
        }

        $fotoPekerjaan = Database::connect()->table('foto_progres_pekerjaan')
            ->select('foto_progres_pekerjaan.*, bantuan_sekolah.nama_bantuan, bantuan_sekolah.foto_0_depan, bantuan_sekolah.foto_0_belakang, bantuan_sekolah.foto_0_dalam')
            ->join('bantuan_sekolah', 'bantuan_sekolah.id = foto_progres_pekerjaan.bantuan_sekolah_id')
            ->where('foto_progres_pekerjaan.progres_id', $id)
            ->orderBy('bantuan_sekolah.nama_bantuan', 'ASC')
            ->get()
            ->getResultArray();

        return view('pelaksanaan/detail_progres', [
            'title'          => 'Detail Progres Mingguan',
            'activeMenu'     => match ($role) {
                'admin' => 'validasi-progres',
                'pengawas' => 'progres',
                default => 'monitoring-progres',
            },
            'sekolah'        => $sekolah,
            'progres'        => $progres,
            'fotoPekerjaan'  => $fotoPekerjaan,
            'backUrl'        => match ($role) {
                'admin' => '/validasi/progres',
                'pengawas' => '/pelaksanaan/progres/' . $sekolah['id'],
                default => '/perencana/monitoring-progres',
            },
            'canPrint'       => true,
        ]);
    }

    public function printProgres(int $id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'pengawas', 'perencana'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Halaman ini hanya dapat diakses admin, pengawas, dan perencana.');
        }

        $progres = (new ProgresMingguanModel())->find($id);
        if (!$progres || ($role !== 'admin' && $progres['status_verval'] !== 'Diterima')) {
            return redirect()->to('/dashboard')->with('error', 'Laporan belum diterima admin.');
        }

        $sekolahModel = new SekolahModel();
        $sekolah = $sekolahModel->find((int) $progres['sekolah_id']);
        if (!$sekolah) {
            return redirect()->to('/dashboard')->with('error', 'Sekolah laporan tidak ditemukan.');
        }

        if ($role === 'pengawas' && (int) $sekolah['pengawas_id'] !== (int) session()->get('id')) {
            return redirect()->to('/dashboard')->with('error', 'Laporan bukan berasal dari sekolah kelolaan Anda.');
        }
        if ($role === 'perencana') {
            $assignedSchoolIds = array_map(
                static fn (array $assignedSchool): int => (int) $assignedSchool['id'],
                $sekolahModel->getByPerencana((string) session()->get('nama_lengkap'))
            );
            if (!in_array((int) $sekolah['id'], $assignedSchoolIds, true)) {
                return redirect()->to('/dashboard')->with('error', 'Laporan bukan berasal dari sekolah penugasan Anda.');
            }
        }

        $db = Database::connect();
        $fotoPekerjaan = $db->table('foto_progres_pekerjaan')
            ->select('foto_progres_pekerjaan.*, bantuan_sekolah.nama_bantuan, bantuan_sekolah.volume, bantuan_sekolah.satuan_volume, bantuan_sekolah.foto_0_depan, bantuan_sekolah.foto_0_belakang, bantuan_sekolah.foto_0_dalam')
            ->join('bantuan_sekolah', 'bantuan_sekolah.id = foto_progres_pekerjaan.bantuan_sekolah_id')
            ->where('foto_progres_pekerjaan.progres_id', $id)
            ->orderBy('bantuan_sekolah.nama_bantuan', 'ASC')
            ->get()
            ->getResultArray();
        $rencana = (new RencanaMingguanModel())
            ->where('sekolah_id', (int) $progres['sekolah_id'])
            ->where('minggu_ke', (int) $progres['minggu_ke'])
            ->first();
        $realisasiKumulatif = 0.0;
        foreach ((new ProgresMingguanModel())->getBySekolah((int) $progres['sekolah_id']) as $progressRow) {
            if ($progressRow['status_verval'] === 'Diterima' && (int) $progressRow['minggu_ke'] <= (int) $progres['minggu_ke']) {
                $realisasiKumulatif += (float) $progressRow['realisasi_fisik'];
            }
        }
        $p2spByPosition = [];
        foreach ((new TimP2SPModel())->getBySekolah((int) $sekolah['id']) as $member) {
            $p2spByPosition[$member['posisi']] = $member;
        }
        $personil = (new PersonilSekolahModel())->getBySekolah((int) $sekolah['id']);
        $userModel = new UserModel();
        $pengawasUser = $userModel->find((int) ($sekolah['pengawas_id'] ?? 0));
        $perencanaUser = null;
        if (!empty($personil['perencana'])) {
            $perencanaUser = $userModel->where('role', 'perencana')
                ->where('nama_lengkap', $personil['perencana'])
                ->first();
        }
        foreach ([
            'ketua' => 'kepala_sekolah',
            'kepala_pelaksana' => 'perencana',
        ] as $position => $legacyField) {
            if (empty($p2spByPosition[$position]['nama'])) {
                $p2spByPosition[$position] = [
                    'nama' => $personil[$legacyField] ?? '-',
                    'ttd' => null,
                ];
            }
        }
        $p2spKepalaPelaksanaName = trim((string) ($p2spByPosition['kepala_pelaksana']['nama'] ?? ''));
        $perencanaName = trim((string) ($perencanaUser['nama_lengkap'] ?? ''));
        $kepalaPelaksanaTtd = $p2spByPosition['kepala_pelaksana']['ttd'] ?? null;
        if ($perencanaName !== '' && mb_strtolower($perencanaName) === mb_strtolower($p2spKepalaPelaksanaName) && !empty($perencanaUser['ttd'])) {
            $kepalaPelaksanaTtd = $perencanaUser['ttd'];
        }

        return view('pelaksanaan/print_progres', [
            'sekolah'       => $sekolah,
            'personil'      => $personil,
            'p2spByPosition' => $p2spByPosition,
            'pengawasName'  => $pengawasUser['nama_lengkap'] ?? ($personil['pengawas'] ?? '-'),
            'pengawasTtd'   => $pengawasUser['ttd'] ?? null,
            'kepalaPelaksanaTtd' => $kepalaPelaksanaTtd,
            'progres'       => $progres,
            'realisasiKumulatif' => $realisasiKumulatif,
            'rencana'       => $rencana,
            'fotoPekerjaan' => $fotoPekerjaan,
        ]);
    }

    public function formProgres(int $sekolahId)
    {
        if (session()->get('role') !== 'pengawas') {
            return redirect()->to('/dashboard')->with('error', 'Hanya pengawas yang dapat menginput progres.');
        }

        $sekolahModel = new SekolahModel();
        $sekolah = $sekolahModel->where('id', $sekolahId)
                                ->where('pengawas_id', session()->get('id'))
                                ->first();
        if (!$sekolah) {
            return redirect()->to('/dashboard')->with('error', 'Sekolah tidak ditemukan atau bukan sekolah kelolaan Anda.');
        }

        $plans = (new RencanaMingguanModel())->getBySekolah($sekolahId);
        if (empty($plans)) {
            return redirect()->to('/pelaksanaan/progres/' . $sekolahId)
                             ->with('error', 'Time schedule awal belum dibuat oleh perencana.');
        }
        if (!$this->isScheduleApproved($plans, (int) $sekolah['total_minggu'])) {
            return redirect()->to('/pelaksanaan/progres/' . $sekolahId)
                             ->with('error', 'Time schedule masih menunggu verifikasi admin. Progres belum dapat diinput.');
        }
        $planByWeek = [];
        foreach ($plans as $plan) {
            $planByWeek[(int) $plan['minggu_ke']] = $plan;
        }

        $progresModel = new ProgresMingguanModel();
        $progres = $progresModel->getBySekolah($sekolahId);
        $progresByWeek = [];
        foreach ($progres as $row) {
            $progresByWeek[(int) $row['minggu_ke']] = $row;
        }

        $selectedWeek = (int) old('minggu_ke', $this->request->getGet('minggu') ?? 0);
        $existing = $progresByWeek[$selectedWeek] ?? null;
        if ($existing && !in_array($existing['status_verval'], ['Draft', 'Diajukan', 'Ditolak'], true)) {
            $existing = null;
        }

        $db = Database::connect();
        $bantuanPekerjaan = $db->table('bantuan_sekolah')
            ->where('sekolah_id', $sekolahId)
            ->orderBy('nama_bantuan', 'ASC')
            ->get()
            ->getResultArray();
        $fotoPekerjaanByBantuan = [];
        $fallbackPhotoByBantuan = $this->getLatestAcceptedPhotos($sekolahId, $selectedWeek > 0 ? $selectedWeek : PHP_INT_MAX);
        if ($existing) {
            $fotoPekerjaan = $db->table('foto_progres_pekerjaan')
                ->where('progres_id', $existing['id'])
                ->get()
                ->getResultArray();
            foreach ($fotoPekerjaan as $foto) {
                $fotoPekerjaanByBantuan[$foto['bantuan_sekolah_id']] = $foto;
            }
        }

        return view('pelaksanaan/input_progres', [
            'title'         => 'Input Progres Mingguan',
            'activeMenu'    => 'progres',
            'sekolah'       => $sekolah,
            'progresByWeek' => $progresByWeek,
            'planByWeek'    => $planByWeek,
            'selectedWeek'  => $selectedWeek,
            'existing'      => $existing,
            'bantuanPekerjaan' => $bantuanPekerjaan,
            'fotoPekerjaanByBantuan' => $fotoPekerjaanByBantuan,
        ]);
    }

    public function simpanProgres()
    {
        if (session()->get('role') !== 'pengawas') {
            return redirect()->to('/dashboard')->with('error', 'Hanya pengawas yang dapat menginput progres.');
        }

        $progresModel = new ProgresMingguanModel();
        $sekolahId = (int) $this->request->getPost('sekolah_id');
        $mingguKe  = (int) $this->request->getPost('minggu_ke');

        $sekolah = (new SekolahModel())->where('id', $sekolahId)
                                      ->where('pengawas_id', session()->get('id'))
                                      ->first();
        if (!$sekolah) {
            return redirect()->to('/dashboard')->with('error', 'Sekolah tidak ditemukan atau bukan sekolah kelolaan Anda.');
        }

        $weekPlan = (new RencanaMingguanModel())->where('sekolah_id', $sekolahId)
                                                 ->where('minggu_ke', $mingguKe)
                                                 ->first();
        if (!$weekPlan) {
            return redirect()->to('/pelaksanaan/progres/' . $sekolahId)
                             ->with('error', 'Time schedule untuk minggu ini belum dibuat oleh perencana.');
        }
        if ($weekPlan['status_verval'] !== 'Diterima') {
            return redirect()->to('/pelaksanaan/progres/' . $sekolahId)
                             ->with('error', 'Time schedule harus diterima admin sebelum progres dapat diajukan.');
        }

        $existing = $progresModel->where('sekolah_id', $sekolahId)
                                 ->where('minggu_ke', $mingguKe)
                                 ->first();
        if ($existing && !in_array($existing['status_verval'], ['Draft', 'Diajukan', 'Ditolak'], true)) {
            return redirect()->to('/pelaksanaan/progres/' . $sekolahId)
                             ->with('error', 'Progres minggu ini sudah diterima dan tidak dapat diubah.');
        }

        $db = Database::connect();
        $bantuanPekerjaan = $db->table('bantuan_sekolah')
            ->where('sekolah_id', $sekolahId)
            ->orderBy('nama_bantuan', 'ASC')
            ->get()
            ->getResultArray();
        if ($bantuanPekerjaan === []) {
            return redirect()->to('/pelaksanaan/progres/' . $sekolahId)
                             ->with('error', 'Jenis bantuan sekolah belum diatur oleh admin.');
        }

        $fotoPekerjaanByBantuan = [];
        if ($existing) {
            foreach ($db->table('foto_progres_pekerjaan')->where('progres_id', $existing['id'])->get()->getResultArray() as $foto) {
                $fotoPekerjaanByBantuan[$foto['bantuan_sekolah_id']] = $foto;
            }
        }
        $fallbackPhotoByBantuan = $this->getLatestAcceptedPhotos($sekolahId, $mingguKe);

        $rules = [
            'minggu_ke'       => 'required|is_natural_no_zero|less_than_equal_to[' . (int) $sekolah['total_minggu'] . ']',
            'serapan_dana'    => 'required|decimal|greater_than_equal_to[0]',
            'realisasi_fisik' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'keterangan'      => 'permit_empty|max_length[2000]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $pdfFile = $this->request->getFile('pdf_laporan');
        $hasNewPdf = $pdfFile && $pdfFile->getError() !== UPLOAD_ERR_NO_FILE;
        if ($hasNewPdf && (!$pdfFile->isValid() || $pdfFile->getMimeType() !== 'application/pdf' || $pdfFile->getSize() > 15 * 1024 * 1024)) {
            return redirect()->back()->withInput()->with('error', 'Laporan mingguan harus berupa PDF maksimal 15 MB.');
        }
        if (!$hasNewPdf && empty($existing['pdf_laporan'])) {
            return redirect()->back()->withInput()->with('error', 'PDF laporan mingguan wajib dilampirkan.');
        }

        $photoFields = ['foto_depan', 'foto_belakang', 'foto_dalam'];
        $allowedPhotoMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $postedWorkRealizations = $this->request->getPost('realisasi_pekerjaan');
        $photoRows = [];
        $uploadFiles = [];
        $fallbackPhotoFiles = [];
        foreach ($bantuanPekerjaan as $bantuan) {
            $bantuanId = (int) $bantuan['id'];
            $workRealization = is_array($postedWorkRealizations) ? ($postedWorkRealizations[$bantuanId] ?? null) : null;
            if (!is_scalar($workRealization)) {
                return redirect()->back()->withInput()->with('error', 'Realisasi minggu ini wajib diisi untuk pekerjaan ' . $bantuan['nama_bantuan'] . '.');
            }
            $workRealization = trim((string) $workRealization);
            if (preg_match('/^\d{1,3}(?:\.\d{1,2})?$/D', $workRealization) !== 1 || (float) $workRealization > 100) {
                return redirect()->back()->withInput()->with('error', 'Realisasi pekerjaan ' . $bantuan['nama_bantuan'] . ' harus berupa angka 0 sampai 100 dengan maksimal dua angka desimal.');
            }
            $photoRows[$bantuanId]['realisasi_fisik'] = number_format((float) $workRealization, 2, '.', '');

            foreach ($photoFields as $field) {
                $file = $this->request->getFile('foto.' . $bantuanId . '.' . $field);
                $storedPhoto = $fotoPekerjaanByBantuan[$bantuanId][$field] ?? null;
                if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                    if (empty($storedPhoto)) {
                        $fallbackPhoto = $fallbackPhotoByBantuan[$bantuanId][$field] ?? null;
                        if (empty($fallbackPhoto)) {
                            return redirect()->back()->withInput()->with('error', 'Unggah foto ' . str_replace('foto_', '', $field) . ' untuk pekerjaan ' . $bantuan['nama_bantuan'] . ' karena belum ada foto progres sebelumnya yang diterima.');
                        }
                        $fallbackPhotoFiles[$bantuanId][$field] = $fallbackPhoto;
                        $photoRows[$bantuanId][$field] = $fallbackPhoto;
                        continue;
                    }
                    $photoRows[$bantuanId][$field] = $storedPhoto;
                    continue;
                }

                if (!$file->isValid() || !in_array($file->getMimeType(), $allowedPhotoMimes, true) || $file->getSize() > 12 * 1024 * 1024) {
                    return redirect()->back()->withInput()->with('error', 'Foto pekerjaan ' . $bantuan['nama_bantuan'] . ' harus berupa JPG, PNG, atau WebP maksimal 12 MB.');
                }

                $photoRows[$bantuanId][$field] = $storedPhoto;
                $uploadFiles[$bantuanId][$field] = $file;
            }
        }

        $target    = (float) $weekPlan['target_rencana'];
        $realisasi = (float) $this->request->getPost('realisasi_fisik');
        $serapan   = (float) $this->request->getPost('serapan_dana');
        $deviasi   = $realisasi - $target;

        $data = [
            'sekolah_id'     => $sekolahId,
            'minggu_ke'      => $mingguKe,
            'serapan_dana'   => $serapan,
            'target_rencana' => $target,
            'realisasi_fisik'=> $realisasi,
            'deviasi'        => $deviasi,
            'status_verval'  => 'Diajukan',
            'keterangan'     => trim((string) $this->request->getPost('keterangan')),
            'pdf_laporan'    => $existing['pdf_laporan'] ?? null,
        ];

        $uploadDirectory = ROOTPATH . 'public/uploads/progres';
        if ($uploadFiles !== [] && !is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
            return redirect()->back()->withInput()->with('error', 'Folder penyimpanan foto tidak dapat dibuat.');
        }

        $movedPhotos = [];
        $movedPdf = null;
        $compressor = new ProgressPhotoCompressor();
        $cleanupMovedPhotos = static function () use (&$movedPhotos, &$movedPdf): void {
            foreach ($movedPhotos as $movedPhoto) {
                if (is_file($movedPhoto)) {
                    unlink($movedPhoto);
                }
            }
            if ($movedPdf !== null && is_file($movedPdf)) {
                unlink($movedPdf);
            }
        };

        foreach ($uploadFiles as $bantuanId => $files) {
            foreach ($files as $field => $file) {
                $compressedPhoto = $compressor->compress($file->getTempName());
                if ($compressedPhoto === null) {
                    $cleanupMovedPhotos();
                    return redirect()->back()->withInput()->with('error', 'Foto tidak dapat dikompres hingga maksimal 5 MB. Pastikan dimensi foto tidak melebihi 16 megapiksel.');
                }

                $newName = bin2hex(random_bytes(16)) . '.jpg';
                $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $newName;
                if (file_put_contents($destination, $compressedPhoto, LOCK_EX) !== strlen($compressedPhoto)) {
                    $cleanupMovedPhotos();
                    return redirect()->back()->withInput()->with('error', 'Foto dokumentasi gagal disimpan. Silakan coba lagi.');
                }

                $photoRows[$bantuanId][$field] = 'uploads/progres/' . $newName;
                $movedPhotos[] = $destination;
            }
        }

        foreach ($fallbackPhotoFiles as $bantuanId => $fields) {
            foreach ($fields as $field => $fallbackPhoto) {
                if (preg_match('#^uploads/progres/[a-f0-9]{32}\.jpg$#i', $fallbackPhoto) !== 1) {
                    $cleanupMovedPhotos();
                    return redirect()->back()->withInput()->with('error', 'Foto progres sebelumnya tidak valid. Unggah foto baru untuk pekerjaan ini.');
                }

                $source = ROOTPATH . 'public/' . str_replace('/', DIRECTORY_SEPARATOR, $fallbackPhoto);
                if (!is_file($source)) {
                    $cleanupMovedPhotos();
                    return redirect()->back()->withInput()->with('error', 'Foto progres sebelumnya tidak ditemukan. Unggah foto baru untuk pekerjaan ini.');
                }

                $newName = bin2hex(random_bytes(16)) . '.jpg';
                $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $newName;
                if (!copy($source, $destination)) {
                    $cleanupMovedPhotos();
                    return redirect()->back()->withInput()->with('error', 'Foto progres sebelumnya gagal disalin. Silakan unggah foto baru.');
                }

                $photoRows[$bantuanId][$field] = 'uploads/progres/' . $newName;
                $movedPhotos[] = $destination;
            }
        }

        if ($hasNewPdf) {
            $pdfDirectory = ROOTPATH . 'public/uploads/laporan_mingguan';
            if (!is_dir($pdfDirectory) && !mkdir($pdfDirectory, 0755, true) && !is_dir($pdfDirectory)) {
                $cleanupMovedPhotos();
                return redirect()->back()->withInput()->with('error', 'Folder penyimpanan PDF tidak dapat dibuat.');
            }

            $pdfName = bin2hex(random_bytes(16)) . '.pdf';
            try {
                $pdfFile->move($pdfDirectory, $pdfName);
            } catch (\Throwable $exception) {
                $cleanupMovedPhotos();
                return redirect()->back()->withInput()->with('error', 'PDF laporan gagal disimpan.');
            }
            $movedPdf = $pdfDirectory . DIRECTORY_SEPARATOR . $pdfName;
            $data['pdf_laporan'] = 'uploads/laporan_mingguan/' . $pdfName;
        }

        $db->transBegin();
        if ($existing) {
            $saved = $progresModel->update($existing['id'], $data);
            $progresId = (int) $existing['id'];
        } else {
            $saved = $progresModel->insert($data);
            $progresId = (int) $progresModel->getInsertID();
        }

        if ($saved === false || !$db->transStatus()) {
            $db->transRollback();
            $cleanupMovedPhotos();
            return redirect()->back()->withInput()->with('error', 'Data progres gagal disimpan. Silakan coba lagi.');
        }

        foreach ($bantuanPekerjaan as $bantuan) {
            $bantuanId = (int) $bantuan['id'];
            $photoData = [
                'progres_id'        => $progresId,
                'bantuan_sekolah_id'=> $bantuanId,
                'realisasi_fisik'   => $photoRows[$bantuanId]['realisasi_fisik'],
            ];
            foreach ($photoFields as $field) {
                $photoData[$field] = $photoRows[$bantuanId][$field] ?? null;
            }

            if (isset($fotoPekerjaanByBantuan[$bantuanId])) {
                $savedPhoto = $db->table('foto_progres_pekerjaan')
                    ->where('progres_id', $progresId)
                    ->where('bantuan_sekolah_id', $bantuanId)
                    ->update($photoData);
            } else {
                $savedPhoto = $db->table('foto_progres_pekerjaan')->insert($photoData);
            }

            if (!$savedPhoto || !$db->transStatus()) {
                $db->transRollback();
                $cleanupMovedPhotos();
                return redirect()->back()->withInput()->with('error', 'Dokumentasi pekerjaan gagal disimpan. Tidak ada perubahan yang disimpan.');
            }
        }

        $db->transCommit();

        foreach ($fotoPekerjaanByBantuan as $bantuanId => $oldPhotoRow) {
            foreach ($photoFields as $field) {
                $oldPhoto = (string) ($oldPhotoRow[$field] ?? '');
                $newPhoto = (string) ($photoRows[$bantuanId][$field] ?? '');
                if ($oldPhoto !== '' && $oldPhoto !== $newPhoto && preg_match('#^uploads/progres/[a-f0-9]{32}\.jpg$#i', $oldPhoto) === 1) {
                    $oldPhotoPath = ROOTPATH . 'public/' . str_replace('/', DIRECTORY_SEPARATOR, $oldPhoto);
                    if (is_file($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }
            }
        }

        $oldPdf = (string) ($existing['pdf_laporan'] ?? '');
        if ($oldPdf !== '' && $oldPdf !== $data['pdf_laporan'] && preg_match('#^uploads/laporan_mingguan/[a-f0-9]{32}\.pdf$#i', $oldPdf) === 1) {
            $oldPdfPath = ROOTPATH . 'public/' . str_replace('/', DIRECTORY_SEPARATOR, $oldPdf);
            if (is_file($oldPdfPath)) {
                unlink($oldPdfPath);
            }
        }

        return redirect()->to('/pelaksanaan/progres/' . $sekolahId)
                 ->with('success', 'Progres minggu ke-' . $mingguKe . ' berhasil diajukan untuk validasi admin.');
    }

    public function hapusProgres($id)
    {
        $progresModel = new ProgresMingguanModel();
        $row = $progresModel->find($id);
        if ($row) {
            $progresModel->delete($id);
            return redirect()->to('/pelaksanaan/progres/' . $row['sekolah_id'])
                             ->with('success', 'Data progres dihapus.');
        }
        return redirect()->back();
    }

    public function kurvaS($sekolahId = null)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'pengawas'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Kurva S hanya dapat diakses admin atau pengawas.');
        }

        $userId = session()->get('id');
        $sekolahModel = new SekolahModel();
        $progresModel = new ProgresMingguanModel();
        $personilModel = new PersonilSekolahModel();

        $sekolahList = $role === 'admin'
            ? $sekolahModel->orderBy('nama_sekolah', 'ASC')->findAll()
            : $sekolahModel->getByPengawas((int) $userId);
        if (empty($sekolahList)) {
            return redirect()->to('/dashboard')->with('error', 'Tidak ada sekolah kelolaan.');
        }

        $sekolahId = (int) ($sekolahId ?? $sekolahList[0]['id']);
        $sekolah = null;
        foreach ($sekolahList as $listedSchool) {
            if ((int) $listedSchool['id'] === $sekolahId) {
                $sekolah = $listedSchool;
                break;
            }
        }
        if (!$sekolah) {
            return redirect()->to('/pelaksanaan/kurva-s')->with('error', 'Sekolah tidak termasuk dalam daftar akses Anda.');
        }
        $personil = $personilModel->getBySekolah($sekolahId);
        $kurva = $progresModel->getKurvaS($sekolahId);

        // Lengkapi sampai minggu 16 jika perlu (rencana sisa bisa 0)
        $totalMinggu = (int) ($sekolah['total_minggu'] ?? 16);
        $existingMinggu = array_column($kurva, 'minggu_ke');
        for ($i = 1; $i <= $totalMinggu; $i++) {
            if (!in_array($i, $existingMinggu)) {
                $lastAkumR = !empty($kurva) ? end($kurva)['akumulasi_rencana'] : 0;
                $lastAkumRe= !empty($kurva) ? end($kurva)['akumulasi_realisasi'] : 0;
                $lastAkumD = !empty($kurva) ? end($kurva)['akumulasi_deviasi'] : 0;
                // Untuk demo: rencana sisa dibagi rata (bisa diganti data rencana lengkap)
                $kurva[] = [
                    'minggu_ke'           => $i,
                    'rencana'             => 0,
                    'akumulasi_rencana'   => $lastAkumR,
                    'realisasi'           => 0,
                    'akumulasi_realisasi' => $lastAkumRe,
                    'deviasi_mingguan'    => 0,
                    'akumulasi_deviasi'   => $lastAkumD,
                ];
            }
        }
        usort($kurva, fn($a, $b) => $a['minggu_ke'] <=> $b['minggu_ke']);

        $data = [
            'title'       => 'Kurva S Pelaksanaan',
            'activeMenu'  => 'kurva-s',
            'sekolah'     => $sekolah,
            'personil'    => $personil,
            'kurva'       => $kurva,
            'sekolahList' => $sekolahList,
        ];

        return view('pelaksanaan/kurva_s', $data);
    }

    public function kalkulasiUlang()
    {
        if (session()->get('role') !== 'pengawas') {
            return redirect()->to('/dashboard')->with('error', 'Hanya pengawas yang dapat menghitung ulang Kurva S.');
        }

        $sekolahId = (int) $this->request->getPost('sekolah_id');
        $assignedSchools = (new SekolahModel())->getByPengawas((int) session()->get('id'));
        $allowedSchoolIds = array_map(static fn (array $school): int => (int) $school['id'], $assignedSchools);
        if (!in_array($sekolahId, $allowedSchoolIds, true)) {
            return redirect()->to('/pelaksanaan/kurva-s')->with('error', 'Sekolah tidak termasuk dalam daftar akses Anda.');
        }

        $progresModel = new ProgresMingguanModel();
        $rows = $progresModel->getBySekolah($sekolahId);
        foreach ($rows as $r) {
            $deviasi = (float)$r['realisasi_fisik'] - (float)$r['target_rencana'];
            $progresModel->update($r['id'], ['deviasi' => $deviasi]);
        }
        return redirect()->to('/pelaksanaan/kurva-s/' . $sekolahId)
                         ->with('success', 'Kalkulasi ulang berhasil.');
    }

    /**
     * Export PDF Kurva S (dengan chart).
     * - POST chart_image (base64 PNG dari canvas Chart.js) → gambar chart asli
     * - Jika tidak ada → generate SVG server-side dari data kurva
     * Memerlukan: composer require dompdf/dompdf (opsional; fallback print HTML)
     */
    public function unduhPdfKurvaS($sekolahId = null)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'pengawas'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Kurva S hanya dapat diakses admin atau pengawas.');
        }

        $userId = session()->get('id');
        $sekolahModel  = new SekolahModel();
        $progresModel  = new ProgresMingguanModel();
        $personilModel = new PersonilSekolahModel();

        $sekolahList = $role === 'admin'
            ? $sekolahModel->orderBy('nama_sekolah', 'ASC')->findAll()
            : $sekolahModel->getByPengawas((int) $userId);
        if (empty($sekolahList)) {
            return redirect()->to('/dashboard')->with('error', 'Tidak ada sekolah kelolaan.');
        }

        // Terima dari POST (form dengan chart) atau GET
        $sekolahId = (int) ($this->request->getPost('sekolah_id')
            ?: $sekolahId
            ?: $sekolahList[0]['id']);

        $sekolah = null;
        foreach ($sekolahList as $listedSchool) {
            if ((int) $listedSchool['id'] === $sekolahId) {
                $sekolah = $listedSchool;
                break;
            }
        }
        if (!$sekolah) {
            return redirect()->to('/pelaksanaan/kurva-s')->with('error', 'Sekolah tidak termasuk dalam daftar akses Anda.');
        }
        $personil = $personilModel->getBySekolah($sekolahId);
        $kurva    = $progresModel->getKurvaS($sekolahId);

        // Chart image dari canvas (data:image/png;base64,...)
        $chartImage = $this->request->getPost('chart_image');
        if ($chartImage && preg_match('/^data:image\/(png|jpeg);base64,/', $chartImage)) {
            // batasi ukuran ~3MB base64
            if (strlen($chartImage) > 4_000_000) {
                $chartImage = null;
            }
        } else {
            $chartImage = null;
        }

        // Fallback chart: PNG (GD) > SVG sederhana (Dompdf-safe)
        $chartSvg = null;
        if (!$chartImage) {
            $chartImage = $this->buildKurvaSChartPng($kurva);
            if (!$chartImage) {
                $chartSvg = $this->buildKurvaSSvg($kurva);
            }
        }

        $data = [
            'sekolah'    => $sekolah,
            'personil'   => $personil,
            'kurva'      => $kurva,
            'chartImage' => $chartImage,
            'chartSvg'   => $chartSvg,
        ];

        $html = view('pelaksanaan/kurva_s_pdf', $data);

        if (class_exists(\Dompdf\Dompdf::class)) {
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('isPhpEnabled', false);
            $options->set('defaultFont', 'DejaVu Sans');
            // DPI lebih tinggi = chart lebih tajam
            $options->set('dpi', 120);

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = 'Kurva_S_' . preg_replace('/\s+/', '_', $sekolah['nama_sekolah'] ?? 'Sekolah') . '.pdf';
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($dompdf->output());
        }

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody($html . '<script>window.onload=function(){window.print();}</script>');
    }

    private function getLatestAcceptedPhotos(int $schoolId, int $beforeWeek): array
    {
        $rows = Database::connect()->table('foto_progres_pekerjaan')
            ->select('foto_progres_pekerjaan.*')
            ->join('progres_mingguan', 'progres_mingguan.id = foto_progres_pekerjaan.progres_id')
            ->where('progres_mingguan.sekolah_id', $schoolId)
            ->where('progres_mingguan.status_verval', 'Diterima')
            ->where('progres_mingguan.minggu_ke <', $beforeWeek)
            ->orderBy('progres_mingguan.minggu_ke', 'DESC')
            ->get()
            ->getResultArray();

        $latestPhotos = [];
        foreach ($rows as $row) {
            $assistanceId = (int) $row['bantuan_sekolah_id'];
            foreach (['foto_depan', 'foto_belakang', 'foto_dalam'] as $field) {
                if (empty($latestPhotos[$assistanceId][$field]) && !empty($row[$field])) {
                    $latestPhotos[$assistanceId][$field] = $row[$field];
                }
            }
        }

        return $latestPhotos;
    }

    private function isScheduleApproved(array $plans, int $totalWeeks): bool
    {
        if (count($plans) !== $totalWeeks) {
            return false;
        }

        foreach ($plans as $plan) {
            if ($plan['status_verval'] !== 'Diterima') {
                return false;
            }
        }

        return true;
    }

    /**
     * Render Kurva S ke PNG base64 via GD (paling andal di Dompdf).
     * Return null jika ekstensi GD tidak tersedia.
     */
    protected function buildKurvaSChartPng(array $kurva): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        $w = 1100;
        $h = 380;
        $padL = 58;
        $padR = 24;
        $padT = 40;
        $padB = 50;
        $plotW = $w - $padL - $padR;
        $plotH = $h - $padT - $padB;

        $img = imagecreatetruecolor($w, $h);
        if (!$img) {
            return null;
        }

        // Anti-alias lines via imagesetthickness + smooth background
        $white  = imagecolorallocate($img, 255, 255, 255);
        $gridC  = imagecolorallocate($img, 229, 231, 235);
        $axisC  = imagecolorallocate($img, 156, 163, 175);
        $textC  = imagecolorallocate($img, 107, 114, 128);
        $blue   = imagecolorallocate($img, 29, 82, 150);
        $green  = imagecolorallocate($img, 5, 150, 105);
        $dark   = imagecolorallocate($img, 55, 65, 81);

        imagefilledrectangle($img, 0, 0, $w, $h, $white);

        $pointsR  = [[0, 0.0]];
        $pointsRe = [[0, 0.0]];
        foreach ($kurva as $i => $k) {
            $pointsR[]  = [$i + 1, (float) $k['akumulasi_rencana']];
            $pointsRe[] = [$i + 1, (float) $k['akumulasi_realisasi']];
        }
        $maxX = max(count($kurva), 1);

        $toXY = static function (float $x, float $y) use ($padL, $padT, $plotW, $plotH, $maxX): array {
            $px = (int) round($padL + ($x / $maxX) * $plotW);
            $py = (int) round($padT + $plotH - (min(100.0, max(0.0, $y)) / 100.0) * $plotH);
            return [$px, $py];
        };

        // Grid horizontal + label Y
        for ($p = 0; $p <= 100; $p += 20) {
            [, $gy] = $toXY(0, (float) $p);
            imageline($img, $padL, $gy, $padL + $plotW, $gy, $gridC);
            $label = $p . '%';
            imagestring($img, 2, $padL - 8 - strlen($label) * 6, $gy - 6, $label, $textC);
        }

        // Grid vertical + label X
        for ($i = 0; $i <= $maxX; $i++) {
            [$gx] = $toXY((float) $i, 0);
            imageline($img, $gx, $padT, $gx, $padT + $plotH, $gridC);
            $label = $i === 0 ? 'Mulai' : 'M' . $i;
            $lx = $gx - (int) (strlen($label) * 3);
            imagestring($img, 1, max(0, $lx), $h - 28, $label, $textC);
        }

        // Axis border
        imagerectangle($img, $padL, $padT, $padL + $plotW, $padT + $plotH, $axisC);

        // Draw polyline helper
        $drawLine = static function (array $pts, $color) use ($img, $toXY) {
            imagesetthickness($img, 3);
            for ($i = 1, $n = count($pts); $i < $n; $i++) {
                [$x1, $y1] = $toXY((float) $pts[$i - 1][0], (float) $pts[$i - 1][1]);
                [$x2, $y2] = $toXY((float) $pts[$i][0], (float) $pts[$i][1]);
                imageline($img, $x1, $y1, $x2, $y2, $color);
            }
            imagesetthickness($img, 1);
            foreach ($pts as [$x, $y]) {
                [$px, $py] = $toXY((float) $x, (float) $y);
                imagefilledellipse($img, $px, $py, 8, 8, $color);
            }
        };

        $drawLine($pointsR, $blue);
        $drawLine($pointsRe, $green);

        // Legend
        imagefilledellipse($img, $padL, 16, 10, 10, $blue);
        imagestring($img, 3, $padL + 12, 8, 'Akumulasi Rencana', $dark);
        imagefilledellipse($img, $padL + 180, 16, 10, 10, $green);
        imagestring($img, 3, $padL + 192, 8, 'Akumulasi Realisasi', $dark);

        // Y-axis title (horizontal approximation — rotated text tidak native di GD built-in font)
        imagestring($img, 2, 4, (int) ($padT + $plotH / 2 - 40), '% Kumulatif', $textC);

        ob_start();
        imagepng($img, null, 6);
        $png = ob_get_clean();
        imagedestroy($img);

        if ($png === false || $png === '') {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode($png);
    }

    /**
     * SVG minimal & Dompdf-safe (fallback jika GD tidak ada).
     * - Hanya: rect, line, polyline, circle, text
     * - Tanpa transform, dominant-baseline, percentage size, gradient
     * - Koordinat absolut integer, font-size numerik
     * - stroke-linecap/linejoin untuk garis lebih rapi
     */
    protected function buildKurvaSSvg(array $kurva): string
    {
        $w = 1000;
        $h = 360;
        $padL = 55;
        $padR = 20;
        $padT = 36;
        $padB = 48;
        $plotW = $w - $padL - $padR;
        $plotH = $h - $padT - $padB;

        $pointsR  = [[0, 0.0]];
        $pointsRe = [[0, 0.0]];
        foreach ($kurva as $i => $k) {
            $pointsR[]  = [$i + 1, (float) $k['akumulasi_rencana']];
            $pointsRe[] = [$i + 1, (float) $k['akumulasi_realisasi']];
        }
        $maxX = max(count($kurva), 1);

        $toXY = static function ($x, $y) use ($padL, $padT, $plotW, $plotH, $maxX) {
            $px = (int) round($padL + ($x / $maxX) * $plotW);
            $py = (int) round($padT + $plotH - (min(100, max(0, $y)) / 100) * $plotH);
            return [$px, $py];
        };

        $polyPoints = static function (array $pts) use ($toXY) {
            $out = [];
            foreach ($pts as [$x, $y]) {
                [$px, $py] = $toXY($x, $y);
                $out[] = $px . ',' . $py;
            }
            return implode(' ', $out);
        };

        $parts = [];
        $parts[] = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">',
            $w, $h, $w, $h
        );
        // Background solid (bukan 100%)
        $parts[] = sprintf('<rect x="0" y="0" width="%d" height="%d" fill="#ffffff"/>', $w, $h);

        // Legend (tanpa transform)
        $parts[] = sprintf('<circle cx="%d" cy="14" r="4" fill="#1d5296"/>', $padL);
        $parts[] = sprintf('<text x="%d" y="18" font-size="11" font-family="DejaVu Sans, Arial, sans-serif" fill="#374151">Akumulasi Rencana</text>', $padL + 10);
        $parts[] = sprintf('<circle cx="%d" cy="14" r="4" fill="#059669"/>', $padL + 170);
        $parts[] = sprintf('<text x="%d" y="18" font-size="11" font-family="DejaVu Sans, Arial, sans-serif" fill="#374151">Akumulasi Realisasi</text>', $padL + 180);

        // Horizontal grid + Y labels (dy manual, tanpa dominant-baseline)
        for ($p = 0; $p <= 100; $p += 20) {
            [, $gy] = $toXY(0, $p);
            $parts[] = sprintf(
                '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#e5e7eb" stroke-width="1"/>',
                $padL, $gy, $padL + $plotW, $gy
            );
            $parts[] = sprintf(
                '<text x="%d" y="%d" font-size="10" font-family="DejaVu Sans, Arial, sans-serif" fill="#6b7280" text-anchor="end">%d%%</text>',
                $padL - 6, $gy + 4, $p
            );
        }

        // Vertical grid + X labels
        for ($i = 0; $i <= $maxX; $i++) {
            [$gx] = $toXY($i, 0);
            $parts[] = sprintf(
                '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#e5e7eb" stroke-width="1"/>',
                $gx, $padT, $gx, $padT + $plotH
            );
            $label = $i === 0 ? 'Mulai' : ('M' . $i);
            $parts[] = sprintf(
                '<text x="%d" y="%d" font-size="9" font-family="DejaVu Sans, Arial, sans-serif" fill="#6b7280" text-anchor="middle">%s</text>',
                $gx, $h - 14, $label
            );
        }

        // Plot border
        $parts[] = sprintf(
            '<rect x="%d" y="%d" width="%d" height="%d" fill="none" stroke="#9ca3af" stroke-width="1"/>',
            $padL, $padT, $plotW, $plotH
        );

        // Polylines — stroke-linecap/join didukung Dompdf modern
        $parts[] = sprintf(
            '<polyline fill="none" stroke="#1d5296" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="%s"/>',
            $polyPoints($pointsR)
        );
        $parts[] = sprintf(
            '<polyline fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="%s"/>',
            $polyPoints($pointsRe)
        );

        // Dots
        foreach ($pointsR as [$x, $y]) {
            [$px, $py] = $toXY($x, $y);
            $parts[] = sprintf('<circle cx="%d" cy="%d" r="3" fill="#1d5296"/>', $px, $py);
        }
        foreach ($pointsRe as [$x, $y]) {
            [$px, $py] = $toXY($x, $y);
            $parts[] = sprintf('<circle cx="%d" cy="%d" r="3" fill="#059669"/>', $px, $py);
        }

        // Y title sebagai teks horizontal di kiri (hindari rotate — Dompdf sering gagal)
        $parts[] = sprintf(
            '<text x="8" y="%d" font-size="9" font-family="DejaVu Sans, Arial, sans-serif" fill="#6b7280">%%</text>',
            (int) ($padT + $plotH / 2)
        );

        $parts[] = '</svg>';

        return implode("\n", $parts);
    }
}
