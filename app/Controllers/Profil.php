<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profil extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $user = $userModel->find(session()->get('id'));

        $data = [
            'title'      => 'Profil Pengguna',
            'activeMenu' => 'profil',
            'user'       => $user,
        ];

        return view('profil/index', $data);
    }

    public function update()
    {
        $userModel = new UserModel();
        $id = session()->get('id');

        $data = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'email'        => $this->request->getPost('email'),
        ];

        $password = $this->request->getPost('password');
        $passwordConfirm = $this->request->getPost('password_confirm');

        if (!empty($password)) {
            if ($password !== $passwordConfirm) {
                return redirect()->back()->with('error', 'Konfirmasi kata sandi tidak cocok.');
            }
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $userModel->update($id, $data);
        session()->set('nama_lengkap', $data['nama_lengkap']);
        session()->set('email', $data['email']);

        return redirect()->to('/profil')->with('success', 'Profil berhasil diperbarui.');
    }

    public function uploadFoto()
    {
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/foto', $newName);

            $userModel = new UserModel();
            $userModel->update(session()->get('id'), ['foto' => 'uploads/foto/' . $newName]);
            session()->set('foto', 'uploads/foto/' . $newName);

            return redirect()->to('/profil')->with('success', 'Foto profil berhasil diunggah.');
        }
        return redirect()->back()->with('error', 'Gagal mengunggah foto.');
    }

    public function uploadTtd()
    {
        if (!in_array(session()->get('role'), ['perencana', 'pengawas'], true)) {
            return redirect()->to('/profil')->with('error', 'Unggah tanda tangan hanya tersedia untuk perencana dan pengawas.');
        }

        $file = $this->request->getFile('ttd');
        $mimeExtensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$file || !$file->isValid() || $file->hasMoved() || $file->getSizeByUnit('kb') > 2048 || !isset($mimeExtensions[$file->getMimeType()])) {
            return redirect()->to('/profil')->with('error', 'Tanda tangan harus berupa JPG, PNG, atau WebP maksimal 2 MB.');
        }

        $directory = ROOTPATH . 'public/uploads/ttd-pengguna';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return redirect()->to('/profil')->with('error', 'Folder penyimpanan tanda tangan tidak dapat dibuat.');
        }

        $newName = bin2hex(random_bytes(16)) . '.' . $mimeExtensions[$file->getMimeType()];
        try {
            $file->move($directory, $newName);
        } catch (\Throwable $exception) {
            return redirect()->to('/profil')->with('error', 'Tanda tangan gagal diunggah.');
        }

        $userModel = new UserModel();
        $user = $userModel->find(session()->get('id'));
        $newPath = 'uploads/ttd-pengguna/' . $newName;
        if (!$user || !$userModel->update(session()->get('id'), ['ttd' => $newPath])) {
            unlink($directory . DIRECTORY_SEPARATOR . $newName);
            return redirect()->to('/profil')->with('error', 'Tanda tangan gagal disimpan.');
        }

        if (!empty($user['ttd']) && preg_match('#^uploads/ttd-pengguna/[a-f0-9]{32}\.(jpg|png|webp)$#i', $user['ttd'])) {
            $oldPath = ROOTPATH . 'public/' . $user['ttd'];
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        return redirect()->to('/profil')->with('success', 'Tanda tangan berhasil diunggah.');
    }
}
