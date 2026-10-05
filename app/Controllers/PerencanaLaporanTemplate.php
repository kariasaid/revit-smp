<?php

namespace App\Controllers;

use App\Models\LaporanTemplateBantuanModel;
use App\Models\SekolahModel;
use CodeIgniter\Database\BaseConnection;

class PerencanaLaporanTemplate extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'perencana') {
            return redirect()->to('/dashboard')->with('error', 'Halaman template hanya dapat diakses perencana.');
        }

        $schoolModel = new SekolahModel();
        $schools = $schoolModel->where('perencana_id', (int) session()->get('id'))->orderBy('nama_sekolah', 'ASC')->findAll();
        if (!$schools) {
            return view('perencana/laporan_template', [
                'title' => 'Template Laporan Mingguan', 'activeMenu' => 'laporan-template',
                'schools' => [], 'school' => null, 'assistance' => [], 'templates' => [],
            ]);
        }

        $schoolId = (int) ($this->request->getGet('sekolah_id') ?: $schools[0]['id']);
        $school = null;
        foreach ($schools as $item) {
            if ((int) $item['id'] === $schoolId) { $school = $item; break; }
        }
        if (!$school) {
            $school = $schools[0];
            $schoolId = (int) $school['id'];
        }

        $db = db_connect();
        $assistance = $db->table('bantuan_sekolah')->where('sekolah_id', $schoolId)->orderBy('nama_bantuan', 'ASC')->get()->getResultArray();
        $templates = (new LaporanTemplateBantuanModel())->where('sekolah_id', $schoolId)->findAll();
        $templateByAssistance = [];
        foreach ($templates as $template) { $templateByAssistance[(int) $template['bantuan_sekolah_id']] = $template; }

        return view('perencana/laporan_template', compact('schools', 'school', 'assistance', 'templateByAssistance') + [
            'title' => 'Template Laporan Mingguan', 'activeMenu' => 'laporan-template'
        ]);
    }

    public function upload()
    {
        if (session()->get('role') !== 'perencana') return redirect()->to('/dashboard');
        $schoolId = (int) $this->request->getPost('sekolah_id');
        $assistanceId = (int) $this->request->getPost('bantuan_sekolah_id');
        $school = (new SekolahModel())->where('id', $schoolId)->where('perencana_id', (int) session()->get('id'))->first();
        $assistance = db_connect()->table('bantuan_sekolah')->where(['id' => $assistanceId, 'sekolah_id' => $schoolId])->get()->getRowArray();
        $file = $this->request->getFile('file_template');
        if (!$school || !$assistance || !$file || !$file->isValid() || $file->getSize() > 15 * 1024 * 1024) {
            return redirect()->back()->with('error', 'Sekolah, jenis bantuan, atau file template tidak valid. Maksimal 15 MB.');
        }
        $allowed = ['pdf','doc','docx','xls','xlsx'];
        if (!in_array(strtolower($file->getClientExtension()), $allowed, true)) {
            return redirect()->back()->with('error', 'Template harus PDF, DOC, DOCX, XLS, atau XLSX.');
        }
        $dir = WRITEPATH . 'uploads/template_laporan';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $name = $file->getRandomName();
        $file->move($dir, $name);
        $model = new LaporanTemplateBantuanModel();
        $old = $model->where(['sekolah_id' => $schoolId, 'bantuan_sekolah_id' => $assistanceId])->first();
        if ($old) {
            $oldPath = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, (string) $old['file_template']);
            if (is_file($oldPath)) @unlink($oldPath);
            $model->update($old['id'], [
                'nama_template' => $file->getClientName(), 'file_template' => 'uploads/template_laporan/' . $name,
                'keterangan' => trim((string) $this->request->getPost('keterangan')), 'diunggah_oleh' => session()->get('id')
            ]);
        } else {
            $model->insert([
                'sekolah_id' => $schoolId, 'bantuan_sekolah_id' => $assistanceId,
                'nama_template' => $file->getClientName(), 'file_template' => 'uploads/template_laporan/' . $name,
                'keterangan' => trim((string) $this->request->getPost('keterangan')), 'diunggah_oleh' => session()->get('id')
            ]);
        }
        return redirect()->to('/perencana/laporan-template?sekolah_id=' . $schoolId)->with('success', 'Template berhasil disimpan.');
    }

    public function download(int $id)
    {
        $role = session()->get('role');
        if (!in_array($role, ['perencana','pengawas'], true)) return redirect()->to('/dashboard');
        $template = (new LaporanTemplateBantuanModel())->find($id);
        if (!$template) return redirect()->back()->with('error', 'Template tidak ditemukan.');
        $schoolModel = new SekolahModel();
        $allowed = $role === 'perencana'
            ? $schoolModel->where('id', $template['sekolah_id'])->where('perencana_id', session()->get('id'))->first()
            : $schoolModel->where('id', $template['sekolah_id'])->where('pengawas_id', session()->get('id'))->first();
        if (!$allowed) return redirect()->back()->with('error', 'Template bukan untuk sekolah dampingan Anda.');
        $path = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, (string) $template['file_template']);
        if (!is_file($path)) return redirect()->back()->with('error', 'File template tidak ditemukan.');
        return $this->response->download($path, null)->setFileName($template['nama_template']);
    }

    public function delete(int $id)
    {
        if (session()->get('role') !== 'perencana') return redirect()->to('/dashboard');
        $model = new LaporanTemplateBantuanModel();
        $template = $model->find($id);
        if (!$template || !(new SekolahModel())->where('id', $template['sekolah_id'])->where('perencana_id', session()->get('id'))->first()) {
            return redirect()->back()->with('error', 'Template tidak ditemukan.');
        }
        $path = WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, (string) $template['file_template']);
        $model->delete($id);
        if (is_file($path)) @unlink($path);
        return redirect()->back()->with('success', 'Template dihapus.');
    }
}
