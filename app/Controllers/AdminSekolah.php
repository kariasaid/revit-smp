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
            ->select('sekolah.*, personil_sekolah.perencana, personil_sekolah.hp_perencana, personil_sekolah.pengawas, personil_sekolah.hp_pengawas, perencana.nama_lengkap AS nama_perencana_user, perencana.nik AS nik_perencana_user, perencana.nip AS nip_perencana_user, pengawas.nama_lengkap AS nama_pengawas_user, pengawas.nik AS nik_pengawas_user, pengawas.nip AS nip_pengawas_user')
            ->join('users perencana', 'perencana.id = sekolah.perencana_id', 'left')
            ->join('users pengawas', 'pengawas.id = sekolah.pengawas_id', 'left')
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

        $perencana = Database::connect()->table('users')->select('id, nama_lengkap, nik, nip, no_hp, email')->where('role', 'perencana')->orderBy('nama_lengkap', 'ASC')->get()->getResultArray();
        $pengawas = Database::connect()->table('users')->select('id, nama_lengkap, nik, nip, no_hp, email')->where('role', 'pengawas')->orderBy('nama_lengkap', 'ASC')->get()->getResultArray();

        return view('admin/sekolah', [
            'title'      => 'Kelola Sekolah',
            'activeMenu' => 'admin-sekolah',
            'schools'    => $schools,
            'assistanceBySchool' => $assistanceBySchool,
            'perencana'  => $perencana,
            'pengawas'   => $pengawas,
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
            ->select('sekolah.*, personil_sekolah.perencana, personil_sekolah.pengawas, personil_sekolah.kepala_sekolah, perencana.nama_lengkap AS nama_perencana_user, perencana.nik AS nik_perencana_user, perencana.nip AS nip_perencana_user, pengawas.nama_lengkap AS nama_pengawas_user, pengawas.nik AS nik_pengawas_user, pengawas.nip AS nip_pengawas_user')
            ->join('users perencana', 'perencana.id = sekolah.perencana_id', 'left')
            ->join('users pengawas', 'pengawas.id = sekolah.pengawas_id', 'left')
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

        $perencana = $db->table('users')->select('id, nama_lengkap, nik, nip, no_hp, email')->where('role', 'perencana')->orderBy('nama_lengkap', 'ASC')->get()->getResultArray();
        $pengawas = $db->table('users')->select('id, nama_lengkap, nik, nip, no_hp, email')->where('role', 'pengawas')->orderBy('nama_lengkap', 'ASC')->get()->getResultArray();

        return view('admin/detail_sekolah', [
            'title'      => 'Detail Sekolah',
            'activeMenu' => 'admin-sekolah',
            'school'     => $school,
            'assistance' => $assistance,
            'perencana'  => $perencana,
            'pengawas'   => $pengawas,
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
            'nama_sekolah' => 'required|max_length[200]', 'npsn' => 'required|numeric|max_length[20]',
            'provinsi' => 'required|max_length[100]', 'kab_kota' => 'required|max_length[100]',
            'dana_diterima' => 'required|decimal|greater_than_equal_to[0]',
            'total_minggu' => 'required|integer|greater_than[0]|less_than_equal_to[52]',
            'perencana_id' => 'required|integer', 'pengawas_id' => 'required|integer',
            'kepala_sekolah' => 'permit_empty|max_length[150]', 'hp_kepala_sekolah' => 'permit_empty|max_length[20]',
        ];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        $db = Database::connect();
        $perencanaId = (int) $this->request->getPost('perencana_id'); $pengawasId = (int) $this->request->getPost('pengawas_id');
        $perencana = $db->table('users')->where('id', $perencanaId)->where('role', 'perencana')->get()->getRowArray();
        $pengawas = $db->table('users')->where('id', $pengawasId)->where('role', 'pengawas')->get()->getRowArray();
        if (!$perencana || !$pengawas) return redirect()->back()->withInput()->with('error', 'Perencana atau pengawas yang dipilih tidak valid.');
        if ($perencanaId === $pengawasId) return redirect()->back()->withInput()->with('error', 'Perencana dan pengawas harus merupakan akun yang berbeda.');
        $npsn = trim((string) $this->request->getPost('npsn'));
        if ($db->table('sekolah')->where('npsn', $npsn)->countAllResults() > 0) return redirect()->back()->withInput()->with('error', 'NPSN tersebut sudah terdaftar.');
        $db->transBegin();
        try {
            $db->table('sekolah')->insert([
                'nama_sekolah' => trim((string) $this->request->getPost('nama_sekolah')), 'npsn' => $npsn,
                'provinsi' => trim((string) $this->request->getPost('provinsi')), 'kab_kota' => trim((string) $this->request->getPost('kab_kota')),
                'dana_diterima' => (float) $this->request->getPost('dana_diterima'), 'total_minggu' => (int) $this->request->getPost('total_minggu'),
                'perencana_id' => $perencanaId, 'pengawas_id' => $pengawasId,
            ]);
            $schoolId = (int) $db->insertID();
            $db->table('personil_sekolah')->insert([
                'sekolah_id' => $schoolId, 'kepala_sekolah' => trim((string) $this->request->getPost('kepala_sekolah')) ?: null,
                'hp_kepala_sekolah' => trim((string) $this->request->getPost('hp_kepala_sekolah')) ?: null,
                'perencana' => $perencana['nama_lengkap'], 'hp_perencana' => $perencana['no_hp'] ?? null,
                'pengawas' => $pengawas['nama_lengkap'], 'hp_pengawas' => $pengawas['no_hp'] ?? null,
            ]);
            if (!$db->transStatus()) throw new \RuntimeException('Database menolak data sekolah.');
            $db->transCommit();
        } catch (Throwable $exception) {
            $db->transRollback(); log_message('error', 'Gagal menambahkan sekolah: ' . $exception->getMessage());
            return redirect()->back()->withInput()->with('error', 'Sekolah gagal dibuat. Periksa data yang dipilih.');
        }
        return redirect()->to('/admin/sekolah')->with('success', 'Sekolah berhasil dibuat dengan penugasan perencana dan pengawas dari database.');
    }

    public function updatePenugasan(int $schoolId)
    {
        if (session()->get('role') !== 'admin') return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat mengubah penugasan.');
        $db = Database::connect(); $perencanaId = (int) $this->request->getPost('perencana_id'); $pengawasId = (int) $this->request->getPost('pengawas_id');
        if (!$db->table('sekolah')->where('id', $schoolId)->countAllResults()) return redirect()->to('/admin/sekolah')->with('error', 'Sekolah tidak ditemukan.');
        $perencana = $db->table('users')->where('id', $perencanaId)->where('role', 'perencana')->get()->getRowArray();
        $pengawas = $db->table('users')->where('id', $pengawasId)->where('role', 'pengawas')->get()->getRowArray();
        if (!$perencana || !$pengawas) return redirect()->back()->with('error', 'Perencana atau pengawas yang dipilih tidak valid.');
        if ($perencanaId === $pengawasId) return redirect()->back()->with('error', 'Perencana dan pengawas harus berbeda.');
        $db->transBegin();
        try {
            $db->table('sekolah')->where('id', $schoolId)->update(['perencana_id' => $perencanaId, 'pengawas_id' => $pengawasId]);
            $db->table('personil_sekolah')->where('sekolah_id', $schoolId)->update([
                'perencana' => $perencana['nama_lengkap'], 'hp_perencana' => $perencana['no_hp'] ?? null,
                'pengawas' => $pengawas['nama_lengkap'], 'hp_pengawas' => $pengawas['no_hp'] ?? null,
            ]);
            if (!$db->transStatus()) throw new \RuntimeException('Gagal memperbarui penugasan.');
            $db->transCommit();
        } catch (Throwable $exception) {
            $db->transRollback(); log_message('error', 'Gagal memperbarui penugasan sekolah: ' . $exception->getMessage());
            return redirect()->back()->with('error', 'Penugasan gagal diperbarui.');
        }
        return redirect()->to('/admin/sekolah/' . $schoolId)->with('success', 'Perencana dan pengawas berhasil diperbarui.');
    }

}
