<?php

namespace App\Controllers;

use App\Models\AdendumModel;
use App\Models\SekolahModel;

class Adendum extends BaseController
{
    public function index($sekolahId = null)
    {
        $userId = session()->get('id');
        $sekolahModel = new SekolahModel();
        $adendumModel = new AdendumModel();

        $sekolahList = $sekolahModel->getByPengawas($userId);
        if (empty($sekolahList)) {
            return redirect()->to('/dashboard')->with('error', 'Tidak ada sekolah kelolaan.');
        }

        $sekolahId = $sekolahId ?? $sekolahList[0]['id'];
        $sekolah   = $sekolahModel->find($sekolahId);
        $adendum   = $adendumModel->getBySekolah($sekolahId);

        $data = [
            'title'       => 'Adendum',
            'activeMenu'  => 'adendum',
            'sekolah'     => $sekolah,
            'adendum'     => $adendum,
            'sekolahList' => $sekolahList,
        ];

        return view('adendum/index', $data);
    }

    public function form($id = null)
    {
        $userId = session()->get('id');
        $sekolahModel = new SekolahModel();
        $adendumModel = new AdendumModel();

        $sekolahList = $sekolahModel->getByPengawas($userId);
        $adendum = null;

        if ($id) {
            $adendum = $adendumModel->find($id);
            if (!$adendum) {
                return redirect()->to('/adendum')->with('error', 'Data adendum tidak ditemukan.');
            }
        }

        $data = [
            'title'       => $id ? 'Edit Adendum' : 'Tambah Adendum',
            'activeMenu'  => 'adendum',
            'sekolahList' => $sekolahList,
            'adendum'     => $adendum,
        ];

        return view('adendum/form', $data);
    }

    public function simpan()
    {
        $adendumModel = new AdendumModel();
        $id = $this->request->getPost('id');

        $rules = [
            'sekolah_id'     => 'required|integer',
            'nomor_adendum'  => 'required|max_length[50]',
            'tanggal'        => 'required|valid_date',
            'perihal'        => 'required|max_length[255]',
            'uraian'         => 'permit_empty',
            'nilai_perubahan'=> 'permit_empty|decimal',
            'status'         => 'required|in_list[Draft,Diajukan,Disetujui,Ditolak]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $data = [
            'sekolah_id'      => (int) $this->request->getPost('sekolah_id'),
            'nomor_adendum'   => $this->request->getPost('nomor_adendum'),
            'tanggal'         => $this->request->getPost('tanggal'),
            'perihal'         => $this->request->getPost('perihal'),
            'uraian'          => $this->request->getPost('uraian'),
            'nilai_perubahan' => (float) ($this->request->getPost('nilai_perubahan') ?: 0),
            'status'          => $this->request->getPost('status'),
            'created_by'      => session()->get('id'),
        ];

        // Upload file adendum (opsional)
        $file = $this->request->getFile('file_adendum');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/adendum', $newName);
            $data['file_adendum'] = 'uploads/adendum/' . $newName;
        }

        if ($id) {
            $adendumModel->update($id, $data);
            $msg = 'Adendum berhasil diperbarui.';
        } else {
            $adendumModel->insert($data);
            $msg = 'Adendum berhasil ditambahkan.';
        }

        return redirect()->to('/adendum/' . $data['sekolah_id'])->with('success', $msg);
    }

    public function hapus($id)
    {
        $adendumModel = new AdendumModel();
        $row = $adendumModel->find($id);
        if ($row) {
            // Hapus file jika ada
            if (!empty($row['file_adendum']) && is_file(WRITEPATH . $row['file_adendum'])) {
                @unlink(WRITEPATH . $row['file_adendum']);
            }
            $adendumModel->delete($id);
            return redirect()->to('/adendum/' . $row['sekolah_id'])->with('success', 'Adendum dihapus.');
        }
        return redirect()->back()->with('error', 'Data tidak ditemukan.');
    }
}
