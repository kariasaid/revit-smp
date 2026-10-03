<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\SekolahModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (session()->get('role') === 'admin') {
            return redirect()->to('/admin/sekolah');
        }

        if (session()->get('role') === 'perencana') {
            return redirect()->to('/perencana/time-schedule');
        }

        $userId = session()->get('id');
        $userModel = new UserModel();
        $sekolahModel = new SekolahModel();

        $user = $userModel->find($userId);
        $sekolahList = $sekolahModel->getByPengawas($userId);

        // Dummy pendamping untuk tampilan (bisa diganti join jika ada tabel)
        $data = [
            'title'       => 'Dashboard',
            'user'        => $user,
            'sekolahList' => $sekolahList,
            'activeMenu'  => 'dashboard',
        ];

        return view('dashboard/index', $data);
    }
}
