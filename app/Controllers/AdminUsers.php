<?php

namespace App\Controllers;

use App\Models\UserModel;

class AdminUsers extends BaseController
{
    private array $roles = ['admin', 'pengawas', 'perencana', 'reviewer', 'fasilitator', 'ta_pusat'];

    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Halaman ini hanya dapat diakses admin.');
        }

        $model = new UserModel();
        $editId = (int) $this->request->getGet('edit');
        $editUser = $editId > 0 ? $model->find($editId) : null;

        return view('admin/users', [
            'title' => 'Manajemen User',
            'activeMenu' => 'admin-users',
            'users' => $model->orderBy('role', 'ASC')->orderBy('nama_lengkap', 'ASC')->findAll(),
            'editUser' => $editUser,
            'roles' => $this->roles,
        ]);
    }

    public function store()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat mengelola user.');
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[100]|alpha_numeric_punct',
            'email' => 'required|valid_email|max_length[150]',
            'nama_lengkap' => 'required|max_length[150]',
            'role' => 'required|in_list[' . implode(',', $this->roles) . ']',
            'password' => 'required|min_length[6]|max_length[72]',
            'nik' => 'permit_empty|max_length[20]',
            'nip' => 'permit_empty|max_length[30]',
            'no_hp' => 'permit_empty|max_length[20]',
            'npwp' => 'permit_empty|max_length[30]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $model = new UserModel();
        $username = trim((string) $this->request->getPost('username'));
        $email = trim((string) $this->request->getPost('email'));

        if ($model->where('username', $username)->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'Username sudah digunakan.');
        }
        if ($model->where('email', $email)->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'Email sudah digunakan.');
        }

        $ok = $model->insert([
            'username' => $username,
            'email' => $email,
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'nama_lengkap' => trim((string) $this->request->getPost('nama_lengkap')),
            'nik' => $this->nullablePost('nik'),
            'nip' => $this->nullablePost('nip'),
            'no_hp' => $this->nullablePost('no_hp'),
            'npwp' => $this->nullablePost('npwp'),
            'role' => (string) $this->request->getPost('role'),
        ]);

        if (!$ok) {
            return redirect()->back()->withInput()->with('error', 'User gagal ditambahkan.');
        }

        return redirect()->to('/admin/users')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat mengelola user.');
        }

        $model = new UserModel();
        $user = $model->find($id);
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[100]|alpha_numeric_punct',
            'email' => 'required|valid_email|max_length[150]',
            'nama_lengkap' => 'required|max_length[150]',
            'role' => 'required|in_list[' . implode(',', $this->roles) . ']',
            'password' => 'permit_empty|min_length[6]|max_length[72]',
            'nik' => 'permit_empty|max_length[20]',
            'nip' => 'permit_empty|max_length[30]',
            'no_hp' => 'permit_empty|max_length[20]',
            'npwp' => 'permit_empty|max_length[30]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $username = trim((string) $this->request->getPost('username'));
        $email = trim((string) $this->request->getPost('email'));

        $duplicateUsername = $model->where('username', $username)->where('id !=', $id)->first();
        $duplicateEmail = $model->where('email', $email)->where('id !=', $id)->first();
        if ($duplicateUsername) {
            return redirect()->back()->withInput()->with('error', 'Username sudah digunakan oleh user lain.');
        }
        if ($duplicateEmail) {
            return redirect()->back()->withInput()->with('error', 'Email sudah digunakan oleh user lain.');
        }

        $data = [
            'username' => $username,
            'email' => $email,
            'nama_lengkap' => trim((string) $this->request->getPost('nama_lengkap')),
            'nik' => $this->nullablePost('nik'),
            'nip' => $this->nullablePost('nip'),
            'no_hp' => $this->nullablePost('no_hp'),
            'npwp' => $this->nullablePost('npwp'),
            'role' => (string) $this->request->getPost('role'),
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Jangan mengubah role admin yang sedang login menjadi non-admin.
        if ($id === (int) session()->get('id') && $data['role'] !== 'admin') {
            return redirect()->back()->withInput()->with('error', 'Akun admin yang sedang digunakan tidak boleh diubah menjadi role lain.');
        }

        if (!$model->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', 'Data user gagal diperbarui.');
        }

        if ($id === (int) session()->get('id')) {
            session()->set([
                'username' => $data['username'],
                'email' => $data['email'],
                'nama_lengkap' => $data['nama_lengkap'],
                'role' => $data['role'],
            ]);
        }

        return redirect()->to('/admin/users')->with('success', 'Data user berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Hanya admin yang dapat mengelola user.');
        }

        if ($id === (int) session()->get('id')) {
            return redirect()->to('/admin/users')->with('error', 'Akun admin yang sedang login tidak dapat dihapus.');
        }

        $model = new UserModel();
        $user = $model->find($id);
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        if (!$model->delete($id)) {
            return redirect()->to('/admin/users')->with('error', 'User gagal dihapus.');
        }

        return redirect()->to('/admin/users')->with('success', 'User berhasil dihapus.');
    }

    private function isAdmin(): bool
    {
        return session()->get('role') === 'admin';
    }

    private function nullablePost(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));
        return $value === '' ? null : $value;
    }
}
