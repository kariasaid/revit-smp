<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function attemptLogin()
    {
        $login    = $this->request->getPost('login');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        $userModel = new UserModel();
        $user = $userModel->findByEmailOrUsername($login);

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->with('error', 'Email/Username atau Password salah.');
        }

        $sessionData = [
            'id'           => $user['id'],
            'username'     => $user['username'],
            'email'        => $user['email'],
            'nama_lengkap' => $user['nama_lengkap'],
            'role'         => $user['role'],
            'foto'         => $user['foto'],
            'isLoggedIn'   => true,
        ];
        session()->set($sessionData);

        if ($remember) {
            // Optional: set cookie remember token
        }

        return redirect()->to('/dashboard')->with('success', 'Selamat datang, ' . $user['nama_lengkap']);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
