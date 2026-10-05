<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class User extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data['users'] = $this->userModel->findAll();
        return view('user/index', $data);
    }

    public function input()
    {
        return view('user/input');
    }

    public function simpan()
    {
        $rules = [
            'nama'     => 'required|min_length[3]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'username' => 'required|min_length[3]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->userModel->save([
            'nama'     => $this->request->getPost('nama'),
            'email'    => $this->request->getPost('email'),
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => $this->request->getPost('role'),
            'no_hp'    => $this->request->getPost('no_hp'),
        ]);

        return redirect()->to('user')->with('success', 'Data user berhasil disimpan.');
    }

    public function edit($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('user')->with('error', 'User tidak ditemukan.');
        }
        return view('user/edit', ['user' => $user]);
    }

    public function update($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('user')->with('error', 'User tidak ditemukan.');
        }

        $rules = [
            'nama'     => 'required|min_length[3]',
            'email'    => "required|valid_email|is_unique[users.email,id,{$id}]",
            'username' => "required|min_length[3]|is_unique[users.username,id,{$id}]",
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama'     => $this->request->getPost('nama'),
            'email'    => $this->request->getPost('email'),
            'username' => $this->request->getPost('username'),
            'role'     => $this->request->getPost('role'),
            'no_hp'    => $this->request->getPost('no_hp'),
        ];

        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);
        return redirect()->to('user')->with('success', 'Data user berhasil diperbarui.');
    }

    public function hapus($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('user')->with('error', 'User tidak ditemukan.');
        }
        $this->userModel->delete($id);
        return redirect()->to('user')->with('success', 'User berhasil dihapus.');
    }
}
