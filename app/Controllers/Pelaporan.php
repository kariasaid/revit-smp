<?php

namespace App\Controllers;

use App\Models\SekolahModel;
use App\Models\DokumenPelaporanModel;
use App\Models\ProgresMingguanModel;

class Pelaporan extends BaseController
{
    public function adminIndex(?string $jenis = null)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Halaman validasi pelaporan hanya dapat diakses admin.');
        }

        if ($jenis !== null && !in_array($jenis, ['50', '100'], true)) {
            return redirect()->to('/admin/pelaporan/50');
        }

        $jenisPelaporan = $jenis === null ? null : $jenis . '%';
        $dokumen = (new DokumenPelaporanModel())->getAllForAdmin();
        if ($jenisPelaporan !== null) {
            $dokumen = array_values(array_filter(
                $dokumen,
                static fn (array $item): bool => $item['jenis_pelaporan'] === $jenisPelaporan
            ));
        }

        return view('admin/pelaporan', [
            'title' => 'Validasi Pelaporan ' . ($jenisPelaporan ?? ''),
            'activeMenu' => 'admin-pelaporan-' . ($jenis ?? 'all'),
            'dokumen' => $dokumen,
            'jenisPelaporan' => $jenisPelaporan,
        ]);
    }

    public function index($jenis = '50')
    {
        $userId = session()->get('id');
        $sekolahModel = new SekolahModel();
        $dokumenModel = new DokumenPelaporanModel();
        $progresModel = new ProgresMingguanModel();

        $sekolahList = $sekolahModel->getByPengawas($userId);
        if (empty($sekolahList)) {
            return redirect()->to('/dashboard')->with('error', 'Tidak ada sekolah kelolaan.');
        }

        $sekolahId = $sekolahList[0]['id'];
        $sekolah = $sekolahModel->find($sekolahId);
        $jenisPelaporan = $jenis === '100' ? '100%' : '50%';
        $dokumen = $dokumenModel->getBySekolahJenis($sekolahId, $jenisPelaporan);
        $summary = $progresModel->getSummary($sekolahId);

        $data = [
            'title'           => 'Pelaporan ' . $jenisPelaporan,
            'activeMenu'      => 'pelaporan-' . $jenis,
            'sekolah'         => $sekolah,
            'dokumen'         => $dokumen,
            'summary'         => $summary,
            'jenis_pelaporan' => $jenisPelaporan,
            'sekolahList'     => $sekolahList,
        ];

        return view('pelaporan/index', $data);
    }

    public function unggah()
    {
        if (session()->get('role') !== 'pengawas') {
            return redirect()->to('/dashboard')->with('error', 'Hanya pengawas yang dapat mengunggah dokumen pelaporan.');
        }

        $dokumenId = (int) $this->request->getPost('dokumen_id');
        $file = $this->request->getFile('file_unggah');
        $dokumenModel = new DokumenPelaporanModel();
        $dokumen = $dokumenModel->find($dokumenId);
        if (!$dokumen || !(new SekolahModel())->where('id', $dokumen['sekolah_id'])
            ->where('pengawas_id', session()->get('id'))
            ->first()) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan atau bukan sekolah kelolaan Anda.');
        }

        if ($file && $file->isValid() && !$file->hasMoved()
            && strtolower($file->getClientExtension()) === 'pdf'
            && $file->getMimeType() === 'application/pdf'
            && $file->getSize() <= 15 * 1024 * 1024) {
            $newName = $file->getRandomName();
            $uploadDirectory = WRITEPATH . 'uploads/dokumen';
            if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                return redirect()->back()->with('error', 'Folder penyimpanan dokumen tidak dapat dibuat.');
            }

            try {
                $file->move($uploadDirectory, $newName);
            } catch (\Throwable $exception) {
                return redirect()->back()->with('error', 'Dokumen gagal diunggah.');
            }

            $newPath = 'uploads/dokumen/' . $newName;
            $updated = $dokumenModel->update($dokumenId, [
                'file_unggah'   => $newPath,
                'status_unggah' => 'Sudah Unggah',
                'status_validasi' => 'Menunggu',
            ]);
            if (!$updated) {
                @unlink($uploadDirectory . DIRECTORY_SEPARATOR . $newName);
                return redirect()->back()->with('error', 'Dokumen gagal disimpan.');
            }

            $oldPath = $this->resolveUploadedPdf($dokumen['file_unggah'] ?? null);
            if ($oldPath && is_file($oldPath)) {
                @unlink($oldPath);
            }

            return redirect()->back()->with('success', 'Dokumen berhasil diunggah.');
        }

        return redirect()->back()->with('error', 'Dokumen harus berupa PDF maksimal 15 MB.');
    }

    public function lihat(int $dokumenId)
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'pengawas'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Dokumen pelaporan hanya dapat dilihat admin dan pengawas.');
        }

        $dokumen = (new DokumenPelaporanModel())->find($dokumenId);
        if (!$dokumen || ($role === 'pengawas' && !(new SekolahModel())->where('id', $dokumen['sekolah_id'])
            ->where('pengawas_id', session()->get('id'))
            ->first())) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan atau bukan sekolah kelolaan Anda.');
        }

        $path = $this->resolveUploadedPdf($dokumen['file_unggah'] ?? null);
        if (!$path || !is_file($path) || mime_content_type($path) !== 'application/pdf') {
            return redirect()->back()->with('error', 'File PDF tidak ditemukan atau tidak valid.');
        }

        $downloadName = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) $dokumen['nama_dokumen']) . '.pdf';
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $downloadName . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody((string) file_get_contents($path));
    }

    public function validasiAdmin(int $dokumenId)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat memvalidasi dokumen pelaporan.');
        }

        $decision = (string) $this->request->getPost('keputusan');
        if (!in_array($decision, ['Diterima', 'Ditolak'], true)) {
            return redirect()->back()->with('error', 'Keputusan validasi dokumen tidak valid.');
        }

        $model = new DokumenPelaporanModel();
        $dokumen = $model->find($dokumenId);
        if (!$dokumen || empty($dokumen['file_unggah']) || $dokumen['status_validasi'] !== 'Menunggu') {
            return redirect()->back()->with('error', 'Dokumen tidak tersedia atau sudah divalidasi.');
        }

        if (!$model->update($dokumenId, ['status_validasi' => $decision])) {
            return redirect()->back()->with('error', 'Status validasi dokumen gagal disimpan.');
        }

        $message = $decision === 'Diterima' ? 'Dokumen berhasil diterima.' : 'Dokumen ditolak.';
        return redirect()->to('/admin/pelaporan')->with('success', $message);
    }

    public function hapusAdmin(int $dokumenId)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat menghapus dokumen pelaporan.');
        }

        $model = new DokumenPelaporanModel();
        $dokumen = $model->find($dokumenId);
        if (!$dokumen || empty($dokumen['file_unggah'])) {
            return redirect()->to('/admin/pelaporan')->with('error', 'Dokumen unggahan tidak ditemukan.');
        }

        $filePath = $this->resolveUploadedPdf($dokumen['file_unggah']);
        if (!$filePath) {
            return redirect()->to('/admin/pelaporan')->with('error', 'Lokasi file dokumen tidak valid.');
        }

        if (!$model->update($dokumenId, [
            'file_unggah' => null,
            'status_unggah' => 'Belum Unggah',
            'status_validasi' => '-',
        ])) {
            return redirect()->to('/admin/pelaporan')->with('error', 'Data dokumen gagal diperbarui.');
        }

        if (is_file($filePath) && !@unlink($filePath)) {
            log_message('error', 'Admin gagal menghapus file pelaporan: ' . $filePath);
            return redirect()->to('/admin/pelaporan')->with('error', 'Status dokumen direset, tetapi file fisik gagal dihapus. Periksa izin folder writable/uploads/dokumen.');
        }

        return redirect()->to('/admin/pelaporan')->with('success', 'File dokumen dihapus dan status direset agar dapat diunggah kembali.');
    }

    private function resolveUploadedPdf(?string $relativePath): ?string
    {
        if (!$relativePath || preg_match('#^uploads/dokumen/[A-Za-z0-9_-]+\.pdf$#i', $relativePath) !== 1) {
            return null;
        }

        $uploadRoot = realpath(WRITEPATH . 'uploads/dokumen');
        $filePath = realpath(WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
        if (!$uploadRoot || !$filePath || !str_starts_with($filePath, $uploadRoot . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $filePath;
    }
}
