<?php

namespace App\Controllers;

use App\Libraries\ProgressPhotoCompressor;
use App\Models\ProgresMingguanModel;
use Config\Database;

class ValidasiProgres extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Halaman validasi hanya dapat diakses admin.');
        }

        $model = new ProgresMingguanModel();
        $progres = $model->getPendingValidation();
        $riwayat = $model->getAllForAdmin();

        return view('validasi/progres', [
            'title'      => 'Validasi Progres',
            'activeMenu' => 'validasi-progres',
            'progres'    => $progres,
            'riwayat'    => $riwayat,
        ]);
    }

    public function delete(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat menghapus laporan progres.');
        }

        $model = new ProgresMingguanModel();
        $progres = $model->find($id);
        if (!$progres) {
            return redirect()->to('/validasi/progres')->with('error', 'Laporan progres tidak ditemukan.');
        }

        $fotoPekerjaan = Database::connect()->table('foto_progres_pekerjaan')
            ->where('progres_id', $id)
            ->get()
            ->getResultArray();
        if (!$model->delete($id)) {
            return redirect()->to('/validasi/progres')->with('error', 'Laporan progres gagal dihapus.');
        }

        $filesRemoved = $this->removeProgressAttachments(array_merge([$progres], $fotoPekerjaan));
        if (!$filesRemoved) {
            return redirect()->to('/validasi/progres')->with('error', 'Laporan terhapus, tetapi beberapa file lampiran gagal dihapus. Periksa izin folder uploads.');
        }

        return redirect()->to('/validasi/progres')->with('success', 'Laporan progres beserta seluruh file lampirannya berhasil dihapus.');
    }

    public function deleteMultiple()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat menghapus laporan progres.');
        }

        $submittedIds = $this->request->getPost('report_ids');
        $ids = [];
        if (is_array($submittedIds)) {
            foreach ($submittedIds as $id) {
                if ((is_string($id) || is_int($id)) && ctype_digit((string) $id) && (int) $id > 0) {
                    $ids[] = (int) $id;
                }
            }
        }
        $ids = array_values(array_unique($ids));
        if ($ids === []) {
            return redirect()->to('/validasi/progres')->with('error', 'Pilih minimal satu laporan untuk dihapus.');
        }

        $model = new ProgresMingguanModel();
        $reports = $model->whereIn('id', $ids)->findAll();
        if ($reports === []) {
            return redirect()->to('/validasi/progres')->with('error', 'Laporan yang dipilih tidak ditemukan.');
        }

        $existingIds = array_column($reports, 'id');
        $db = Database::connect();
        $fotoPekerjaan = $db->table('foto_progres_pekerjaan')
            ->whereIn('progres_id', $existingIds)
            ->get()
            ->getResultArray();
        $db->transBegin();
        $deleted = $db->table('progres_mingguan')->whereIn('id', $existingIds)->delete();
        if (!$deleted || !$db->transStatus()) {
            $db->transRollback();
            return redirect()->to('/validasi/progres')->with('error', 'Laporan gagal dihapus. Tidak ada perubahan yang disimpan.');
        }
        $db->transCommit();

        $filesRemoved = $this->removeProgressAttachments(array_merge($reports, $fotoPekerjaan));
        if (!$filesRemoved) {
            return redirect()->to('/validasi/progres')->with('error', count($reports) . ' laporan terhapus, tetapi beberapa file lampiran gagal dihapus. Periksa izin folder uploads.');
        }

        return redirect()->to('/validasi/progres')->with('success', count($reports) . ' laporan berhasil dihapus beserta seluruh file lampirannya.');
    }

    private function removeProgressAttachments(array $reports): bool
    {
        $allFilesRemoved = true;
        foreach ($reports as $report) {
            $attachments = [
                [$report['foto_depan'] ?? null, '#^uploads/progres/[a-f0-9]{32}\.jpg$#i'],
                [$report['foto_belakang'] ?? null, '#^uploads/progres/[a-f0-9]{32}\.jpg$#i'],
                [$report['foto_dalam'] ?? null, '#^uploads/progres/[a-f0-9]{32}\.jpg$#i'],
                [$report['pdf_laporan'] ?? null, '#^uploads/laporan_mingguan/[a-f0-9]{32}\.pdf$#i'],
            ];

            foreach ($attachments as [$attachment, $pathPattern]) {
                if (!is_string($attachment) || preg_match($pathPattern, $attachment) !== 1) {
                    continue;
                }

                $path = ROOTPATH . 'public/' . str_replace('/', DIRECTORY_SEPARATOR, $attachment);
                if (is_file($path) && !@unlink($path)) {
                    $allFilesRemoved = false;
                    log_message('error', 'Gagal menghapus file lampiran progres: ' . $path);
                }
            }
        }

        return $allFilesRemoved;
    }

    public function update(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat memvalidasi progres.');
        }

        $rules = [
            'keputusan' => 'required|in_list[Diterima,Ditolak]',
            'catatan'   => 'permit_empty|max_length[2000]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $model = new ProgresMingguanModel();
        $progres = $model->find($id);
        if (!$progres || $progres['status_verval'] !== 'Diajukan') {
            return redirect()->back()->with('error', 'Progres tidak ditemukan atau sudah divalidasi.');
        }

        $keputusan = $this->request->getPost('keputusan');
        $catatan = trim((string) $this->request->getPost('catatan'));
        if ($keputusan === 'Ditolak' && $catatan === '') {
            return redirect()->back()->with('error', 'Catatan wajib diisi jika progres ditolak.');
        }

        $keterangan = trim((string) ($progres['keterangan'] ?? ''));
        if ($catatan !== '') {
            $catatanValidasi = 'Catatan validasi admin: ' . $catatan;
            $keterangan = $keterangan === '' ? $catatanValidasi : $keterangan . "\n\n" . $catatanValidasi;
        }

        $db = Database::connect();
        $fotoRows = $db->table('foto_progres_pekerjaan')
            ->select('foto_progres_pekerjaan.bantuan_sekolah_id, bantuan_sekolah.foto_0_depan, bantuan_sekolah.foto_0_belakang, bantuan_sekolah.foto_0_dalam')
            ->join('bantuan_sekolah', 'bantuan_sekolah.id = foto_progres_pekerjaan.bantuan_sekolah_id')
            ->where('foto_progres_pekerjaan.progres_id', $id)
            ->get()
            ->getResultArray();
        $baselineFiles = [];
        $allowedPhotoMimes = ['image/jpeg', 'image/png', 'image/webp'];
        foreach ($fotoRows as $fotoRow) {
            $bantuanId = (int) $fotoRow['bantuan_sekolah_id'];
            foreach (['depan', 'belakang', 'dalam'] as $angle) {
                $file = $this->request->getFile('foto_0.' . $bantuanId . '.foto_' . $angle);
                if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if (!$file->isValid() || !in_array($file->getMimeType(), $allowedPhotoMimes, true) || $file->getSize() > 12 * 1024 * 1024) {
                    return redirect()->back()->withInput()->with('error', 'Foto kondisi 0% harus berupa JPG, PNG, atau WebP maksimal 12 MB.');
                }
                $baselineFiles[$bantuanId][$angle] = [
                    'file' => $file,
                    'old'  => $fotoRow['foto_0_' . $angle] ?? null,
                ];
            }
        }

        $uploadDirectory = ROOTPATH . 'public/uploads/progres';
        if ($baselineFiles !== [] && !is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
            return redirect()->back()->withInput()->with('error', 'Folder penyimpanan foto tidak dapat dibuat.');
        }
        $compressor = new ProgressPhotoCompressor();
        $newBaselinePhotos = [];
        foreach ($baselineFiles as $bantuanId => $angles) {
            foreach ($angles as $angle => $photo) {
                $compressedPhoto = $compressor->compress($photo['file']->getTempName());
                if ($compressedPhoto === null) {
                    $this->removeStoredPhotos($newBaselinePhotos);
                    return redirect()->back()->withInput()->with('error', 'Foto kondisi 0% tidak dapat dikompres hingga maksimal 5 MB.');
                }
                $newName = bin2hex(random_bytes(16)) . '.jpg';
                $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $newName;
                if (file_put_contents($destination, $compressedPhoto, LOCK_EX) !== strlen($compressedPhoto)) {
                    $this->removeStoredPhotos($newBaselinePhotos);
                    return redirect()->back()->withInput()->with('error', 'Foto kondisi 0% gagal disimpan.');
                }
                $newBaselinePhotos[] = $destination;
                $baselineFiles[$bantuanId][$angle]['new'] = 'uploads/progres/' . $newName;
            }
        }

        $model->update($id, [
            'status_verval' => $keputusan,
            'keterangan'    => $keterangan,
        ]);

        if ($baselineFiles !== []) {
            foreach ($baselineFiles as $bantuanId => $angles) {
                $updates = [];
                foreach ($angles as $angle => $photo) {
                    $updates['foto_0_' . $angle] = $photo['new'];
                }
                $db->table('bantuan_sekolah')->where('id', $bantuanId)->update($updates);
                foreach ($angles as $photo) {
                    $this->removeStoredPhotos([$photo['old'] ?? null]);
                }
            }
        }

        $message = $keputusan === 'Diterima' ? 'Progres berhasil diterima.' : 'Progres ditolak dan dikembalikan ke pengawas.';
        return redirect()->to('/validasi/progres')->with('success', $message);
    }

    private function removeStoredPhotos(array $photos): void
    {
        foreach ($photos as $photo) {
            if (!is_string($photo) || preg_match('#^uploads/progres/[a-f0-9]{32}\.jpg$#i', $photo) !== 1) {
                continue;
            }
            $path = ROOTPATH . 'public/' . str_replace('/', DIRECTORY_SEPARATOR, $photo);
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
