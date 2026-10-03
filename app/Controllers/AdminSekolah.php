<?php

namespace App\Controllers;

use App\Libraries\ProgressPhotoCompressor;
use Config\Database;
use Throwable;

class AdminSekolah extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Halaman ini hanya dapat diakses admin.');
        }

        $schools = Database::connect()->table('sekolah')
            ->select('sekolah.*, personil_sekolah.perencana, personil_sekolah.hp_perencana, personil_sekolah.pengawas, personil_sekolah.hp_pengawas')
            ->join('personil_sekolah', 'personil_sekolah.sekolah_id = sekolah.id', 'left')
            ->orderBy('sekolah.nama_sekolah', 'ASC')
            ->get()
            ->getResultArray();
        $assistanceRows = Database::connect()->table('bantuan_sekolah')
            ->orderBy('nama_bantuan', 'ASC')
            ->get()
            ->getResultArray();
        $assistanceBySchool = [];
        foreach ($assistanceRows as $assistance) {
            $assistanceBySchool[$assistance['sekolah_id']][] = $assistance['nama_bantuan'];
        }

        return view('admin/sekolah', [
            'title'      => 'Kelola Sekolah',
            'activeMenu' => 'admin-sekolah',
            'schools'    => $schools,
            'assistanceBySchool' => $assistanceBySchool,
        ]);
    }

    public function storeBantuan(int $schoolId)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat mengelola jenis bantuan.');
        }

        $name = trim((string) $this->request->getPost('nama_bantuan'));
        if ($name === '' || mb_strlen($name) > 150) {
            return redirect()->back()->withInput()->with('error', 'Jenis bantuan wajib diisi dan maksimal 150 karakter.');
        }
        $volume = trim((string) $this->request->getPost('volume'));
        if (preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', $volume) !== 1 || (float) $volume <= 0) {
            return redirect()->back()->withInput()->with('error', 'Volume harus lebih besar dari 0 dan maksimal dua angka desimal.');
        }
        $volumeUnit = trim((string) $this->request->getPost('satuan_volume'));
        if ($volumeUnit === '' || mb_strlen($volumeUnit) > 30) {
            return redirect()->back()->withInput()->with('error', 'Satuan volume wajib diisi dan maksimal 30 karakter.');
        }

        $db = Database::connect();
        if ($db->table('sekolah')->where('id', $schoolId)->countAllResults() === 0) {
            return redirect()->to('/admin/sekolah')->with('error', 'Sekolah tidak ditemukan.');
        }

        $duplicate = $db->table('bantuan_sekolah')
            ->where('sekolah_id', $schoolId)
            ->where('nama_bantuan', $name)
            ->countAllResults();
        if ($duplicate > 0) {
            return redirect()->to("/admin/sekolah/$schoolId")->with('error', 'Jenis bantuan tersebut sudah terdaftar untuk sekolah ini.');
        }

        $db->table('bantuan_sekolah')->insert([
            'sekolah_id'  => $schoolId,
            'nama_bantuan' => $name,
            'volume' => number_format((float) $volume, 2, '.', ''),
            'satuan_volume' => $volumeUnit,
        ]);

        return redirect()->to("/admin/sekolah/$schoolId")->with('success', 'Jenis bantuan berhasil ditambahkan.');
    }

    public function detail(int $schoolId)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Halaman ini hanya dapat diakses admin.');
        }

        $db = Database::connect();
        $school = $db->table('sekolah')
            ->select('sekolah.*, personil_sekolah.perencana, personil_sekolah.pengawas, personil_sekolah.kepala_sekolah')
            ->join('personil_sekolah', 'personil_sekolah.sekolah_id = sekolah.id', 'left')
            ->where('sekolah.id', $schoolId)
            ->get()
            ->getRowArray();
        if (!$school) {
            return redirect()->to('/admin/sekolah')->with('error', 'Sekolah tidak ditemukan.');
        }

        $assistance = $db->table('bantuan_sekolah')
            ->where('sekolah_id', $schoolId)
            ->orderBy('nama_bantuan', 'ASC')
            ->get()
            ->getResultArray();

        return view('admin/detail_sekolah', [
            'title'      => 'Detail Sekolah',
            'activeMenu' => 'admin-sekolah',
            'school'     => $school,
            'assistance' => $assistance,
        ]);
    }

    public function updateBantuan(int $assistanceId)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat mengubah jenis bantuan.');
        }

        $db = Database::connect();
        $assistance = $db->table('bantuan_sekolah')->where('id', $assistanceId)->get()->getRowArray();
        if (!$assistance) {
            return redirect()->to('/admin/sekolah')->with('error', 'Jenis bantuan tidak ditemukan.');
        }

        $name = trim((string) $this->request->getPost('nama_bantuan'));
        if ($name === '' || mb_strlen($name) > 150) {
            return redirect()->back()->withInput()->with('error', 'Jenis bantuan wajib diisi dan maksimal 150 karakter.');
        }
        $volume = trim((string) $this->request->getPost('volume'));
        if (preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', $volume) !== 1 || (float) $volume <= 0) {
            return redirect()->back()->withInput()->with('error', 'Volume harus lebih besar dari 0 dan maksimal dua angka desimal.');
        }
        $volumeUnit = trim((string) $this->request->getPost('satuan_volume'));
        if ($volumeUnit === '' || mb_strlen($volumeUnit) > 30) {
            return redirect()->back()->withInput()->with('error', 'Satuan volume wajib diisi dan maksimal 30 karakter.');
        }
        $duplicate = $db->table('bantuan_sekolah')
            ->where('sekolah_id', (int) $assistance['sekolah_id'])
            ->where('nama_bantuan', $name)
            ->where('id !=', $assistanceId)
            ->countAllResults();
        if ($duplicate > 0) {
            return redirect()->back()->withInput()->with('error', 'Jenis bantuan tersebut sudah terdaftar untuk sekolah ini.');
        }

        $updates = [
            'nama_bantuan' => $name,
            'volume' => number_format((float) $volume, 2, '.', ''),
            'satuan_volume' => $volumeUnit,
        ];
        $newPhotos = [];
        foreach (['depan', 'belakang', 'dalam'] as $angle) {
            $file = $this->request->getFile('foto_0_' . $angle);
            if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $stored = $this->storeBaselinePhoto($file);
            if ($stored === null) {
                $this->removeStoredPhotos($newPhotos);
                return redirect()->back()->withInput()->with('error', 'Foto awal harus berupa JPG, PNG, atau WebP maksimal 12 MB dan 16 megapiksel.');
            }
            $updates['foto_0_' . $angle] = $stored['relative'];
            $newPhotos[] = $stored['absolute'];
        }

        if (!$db->table('bantuan_sekolah')->where('id', $assistanceId)->update($updates)) {
            $this->removeStoredPhotos($newPhotos);
            return redirect()->back()->withInput()->with('error', 'Jenis bantuan gagal diperbarui.');
        }
        foreach (['depan', 'belakang', 'dalam'] as $angle) {
            if (isset($updates['foto_0_' . $angle])) {
                $this->removeStoredPhotos([$assistance['foto_0_' . $angle] ?? null]);
            }
        }

        return redirect()->to('/admin/sekolah/' . $assistance['sekolah_id'])->with('success', 'Jenis bantuan dan foto awal berhasil diperbarui.');
    }

    public function deleteBantuan(int $assistanceId)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat menghapus jenis bantuan.');
        }

        $db = Database::connect();
        $assistance = $db->table('bantuan_sekolah')->where('id', $assistanceId)->get()->getRowArray();
        if (!$assistance) {
            return redirect()->to('/admin/sekolah')->with('error', 'Jenis bantuan tidak ditemukan.');
        }
        $progressPhotos = $db->table('foto_progres_pekerjaan')
            ->where('bantuan_sekolah_id', $assistanceId)
            ->get()
            ->getResultArray();
        if (!$db->table('bantuan_sekolah')->where('id', $assistanceId)->delete()) {
            return redirect()->back()->with('error', 'Jenis bantuan gagal dihapus.');
        }

        foreach ($progressPhotos as $progressPhoto) {
            $this->removeStoredPhotos([
                $progressPhoto['foto_depan'] ?? null,
                $progressPhoto['foto_belakang'] ?? null,
                $progressPhoto['foto_dalam'] ?? null,
            ]);
        }
        $this->removeStoredPhotos([
            $assistance['foto_0_depan'] ?? null,
            $assistance['foto_0_belakang'] ?? null,
            $assistance['foto_0_dalam'] ?? null,
        ]);
        return redirect()->to('/admin/sekolah/' . $assistance['sekolah_id'])->with('success', 'Jenis bantuan berhasil dihapus.');
    }

    private function storeBaselinePhoto($file): ?array
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!$file->isValid() || !in_array($file->getMimeType(), $allowedMimes, true) || $file->getSize() > 12 * 1024 * 1024) {
            return null;
        }
        $compressed = (new ProgressPhotoCompressor())->compress($file->getTempName());
        if ($compressed === null) {
            return null;
        }

        $directory = ROOTPATH . 'public/uploads/progres';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return null;
        }
        $name = bin2hex(random_bytes(16)) . '.jpg';
        $absolute = $directory . DIRECTORY_SEPARATOR . $name;
        if (file_put_contents($absolute, $compressed, LOCK_EX) !== strlen($compressed)) {
            return null;
        }
        return ['absolute' => $absolute, 'relative' => 'uploads/progres/' . $name];
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

    public function store()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat menambahkan sekolah.');
        }

        $rules = [
            'nama_sekolah'       => 'required|max_length[200]',
            'npsn'               => 'required|numeric|max_length[20]',
            'provinsi'           => 'required|max_length[100]',
            'kab_kota'           => 'required|max_length[100]',
            'dana_diterima'      => 'required|decimal|greater_than_equal_to[0]',
            'total_minggu'       => 'required|integer|greater_than[0]|less_than_equal_to[52]',
            'nama_perencana'     => 'required|max_length[150]',
            'nik_perencana'      => 'required|numeric|exact_length[16]',
            'email_perencana'    => 'permit_empty|valid_email|max_length[150]',
            'hp_perencana'       => 'permit_empty|max_length[20]',
            'nama_pengawas'      => 'required|max_length[150]',
            'nik_pengawas'       => 'required|numeric|exact_length[16]',
            'email_pengawas'     => 'permit_empty|valid_email|max_length[150]',
            'hp_pengawas'        => 'permit_empty|max_length[20]',
            'kepala_sekolah'     => 'permit_empty|max_length[150]',
            'hp_kepala_sekolah'  => 'permit_empty|max_length[20]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $plannerNik = (string) $this->request->getPost('nik_perencana');
        $supervisorNik = (string) $this->request->getPost('nik_pengawas');
        if ($plannerNik === $supervisorNik) {
            return redirect()->back()->withInput()->with('error', 'NIK perencana dan pengawas harus berbeda.');
        }

        $db = Database::connect();
        if ($db->table('sekolah')->where('npsn', $this->request->getPost('npsn'))->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'NPSN tersebut sudah terdaftar.');
        }

        $plannerEmail = trim((string) $this->request->getPost('email_perencana'))
            ?: $plannerNik . '.perencana@revit-smp.local';
        $supervisorEmail = trim((string) $this->request->getPost('email_pengawas'))
            ?: $supervisorNik . '.pengawas@revit-smp.local';

        foreach ([[$plannerNik, $plannerEmail], [$supervisorNik, $supervisorEmail]] as [$nik, $email]) {
            $duplicate = $db->table('users')
                ->groupStart()
                ->where('username', $nik)
                ->orWhere('email', $email)
                ->orWhere('nik', $nik)
                ->groupEnd()
                ->countAllResults();
            if ($duplicate > 0) {
                return redirect()->back()->withInput()->with('error', 'NIK perencana atau pengawas sudah memiliki akun. Gunakan NIK yang belum terdaftar.');
            }
        }

        $db->transBegin();
        try {
            $db->table('users')->insert([
                'username'     => $plannerNik,
                'email'        => $plannerEmail,
                'password'     => password_hash($plannerNik, PASSWORD_DEFAULT),
                'nama_lengkap' => trim((string) $this->request->getPost('nama_perencana')),
                'nik'          => $plannerNik,
                'no_hp'        => trim((string) $this->request->getPost('hp_perencana')) ?: null,
                'role'         => 'perencana',
            ]);
            $plannerId = (int) $db->insertID();

            $db->table('users')->insert([
                'username'     => $supervisorNik,
                'email'        => $supervisorEmail,
                'password'     => password_hash($supervisorNik, PASSWORD_DEFAULT),
                'nama_lengkap' => trim((string) $this->request->getPost('nama_pengawas')),
                'nik'          => $supervisorNik,
                'no_hp'        => trim((string) $this->request->getPost('hp_pengawas')) ?: null,
                'role'         => 'pengawas',
            ]);
            $supervisorId = (int) $db->insertID();

            $db->table('sekolah')->insert([
                'nama_sekolah' => trim((string) $this->request->getPost('nama_sekolah')),
                'npsn'         => trim((string) $this->request->getPost('npsn')),
                'provinsi'     => trim((string) $this->request->getPost('provinsi')),
                'kab_kota'     => trim((string) $this->request->getPost('kab_kota')),
                'dana_diterima'=> (float) $this->request->getPost('dana_diterima'),
                'total_minggu' => (int) $this->request->getPost('total_minggu'),
                'pengawas_id'  => $supervisorId,
            ]);
            $schoolId = (int) $db->insertID();

            $db->table('personil_sekolah')->insert([
                'sekolah_id'       => $schoolId,
                'kepala_sekolah'   => trim((string) $this->request->getPost('kepala_sekolah')) ?: null,
                'hp_kepala_sekolah'=> trim((string) $this->request->getPost('hp_kepala_sekolah')) ?: null,
                'perencana'        => trim((string) $this->request->getPost('nama_perencana')),
                'hp_perencana'     => trim((string) $this->request->getPost('hp_perencana')) ?: null,
                'pengawas'         => trim((string) $this->request->getPost('nama_pengawas')),
                'hp_pengawas'      => trim((string) $this->request->getPost('hp_pengawas')) ?: null,
            ]);

            if (!$db->transStatus()) {
                throw new \RuntimeException('Database menolak salah satu data sekolah atau akun.');
            }
            $db->transCommit();
        } catch (Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Gagal menambahkan sekolah beserta akun: ' . $exception->getMessage());
            return redirect()->back()->withInput()->with('error', 'Sekolah dan akun gagal dibuat. Periksa NIK, email, serta NPSN.');
        }

        return redirect()->to('/admin/sekolah')->with(
            'success',
            'Sekolah berhasil dibuat. Username dan password awal perencana serta pengawas menggunakan NIK masing-masing.'
        );
    }
}
