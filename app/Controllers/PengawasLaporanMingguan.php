<?php

namespace App\Controllers;

use App\Models\LaporanMingguanBantuanModel;
use App\Models\LaporanTemplateBantuanModel;
use App\Models\SekolahModel;

class PengawasLaporanMingguan extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'pengawas') return redirect()->to('/dashboard');
        $schools = (new SekolahModel())->where('pengawas_id', session()->get('id'))->orderBy('nama_sekolah', 'ASC')->findAll();
        if (!$schools) return redirect()->to('/dashboard')->with('error', 'Tidak ada sekolah dampingan.');
        $schoolId = (int) ($this->request->getGet('sekolah_id') ?: $schools[0]['id']);
        $school = null;
        foreach ($schools as $item) if ((int)$item['id'] === $schoolId) $school = $item;
        if (!$school) { $school = $schools[0]; $schoolId = (int)$school['id']; }
        $db = db_connect();
        $assistance = $db->table('bantuan_sekolah')->where('sekolah_id', $schoolId)->orderBy('nama_bantuan', 'ASC')->get()->getResultArray();
        $templates = (new LaporanTemplateBantuanModel())->where('sekolah_id', $schoolId)->findAll();
        $templateByAssistance = [];
        foreach ($templates as $t) $templateByAssistance[(int)$t['bantuan_sekolah_id']] = $t;
        $reports = (new LaporanMingguanBantuanModel())->getBySchool($schoolId);
        return view('pengawas/laporan_mingguan', [
            'title' => 'Laporan Mingguan per Jenis Bantuan', 'activeMenu' => 'laporan-mingguan',
            'schools' => $schools, 'school' => $school, 'assistance' => $assistance,
            'templateByAssistance' => $templateByAssistance, 'reports' => $reports,
        ]);
    }

    public function save()
    {
        if (session()->get('role') !== 'pengawas') return redirect()->to('/dashboard');
        $schoolId = (int)$this->request->getPost('sekolah_id');
        $assistanceId = (int)$this->request->getPost('bantuan_sekolah_id');
        $week = (int)$this->request->getPost('minggu_ke');
        $school = (new SekolahModel())->where('id', $schoolId)->where('pengawas_id', session()->get('id'))->first();
        $db = db_connect();
        $assistance = $db->table('bantuan_sekolah')->where(['id'=>$assistanceId,'sekolah_id'=>$schoolId])->get()->getRowArray();
        if (!$school || !$assistance || $week < 1 || $week > (int)$school['total_minggu']) return redirect()->back()->withInput()->with('error','Data sekolah, bantuan, atau minggu tidak valid.');
        $plan = $db->table('rencana_mingguan')->where(['sekolah_id'=>$schoolId,'minggu_ke'=>$week])->get()->getRowArray();
        if (!$plan || $plan['status_verval'] !== 'Diterima') return redirect()->back()->withInput()->with('error','Time schedule minggu ini belum diterima admin.');
        $real = (float)$this->request->getPost('realisasi_fisik');
        if ($real < 0 || $real > 100) return redirect()->back()->withInput()->with('error','Realisasi fisik harus 0 sampai 100%.');
        $model = new LaporanMingguanBantuanModel();
        $existing = $model->where(['sekolah_id'=>$schoolId,'bantuan_sekolah_id'=>$assistanceId,'minggu_ke'=>$week])->first();
        $data = [
            'sekolah_id'=>$schoolId,'bantuan_sekolah_id'=>$assistanceId,'minggu_ke'=>$week,
            'target_rencana'=>$plan['target_rencana'],'realisasi_fisik'=>$real,
            'serapan_dana'=>(float)$this->request->getPost('serapan_dana'),
            'uraian_kegiatan'=>trim((string)$this->request->getPost('uraian_kegiatan')),
            'kendala'=>trim((string)$this->request->getPost('kendala')),
            'tindak_lanjut'=>trim((string)$this->request->getPost('tindak_lanjut')),
            'status_verval'=>'Diajukan','dibuat_oleh'=>session()->get('id'),
        ];
        if ($existing && $existing['status_verval'] === 'Diterima') return redirect()->back()->with('error','Laporan yang sudah diterima tidak dapat diubah.');
        if ($existing) $model->update($existing['id'],$data); else $model->insert($data);
        return redirect()->to('/pengawas/laporan-mingguan?sekolah_id='.$schoolId)->with('success','Laporan mingguan berhasil diajukan.');
    }

    public function exportPdf(int $id)
    {
        if (session()->get('role') !== 'pengawas') return redirect()->to('/dashboard');
        $model = new LaporanMingguanBantuanModel();
        $report = $model->select('laporan_mingguan_bantuan.*, sekolah.nama_sekolah, sekolah.npsn, sekolah.provinsi, sekolah.kab_kota, bantuan_sekolah.nama_bantuan, users.nama_lengkap AS nama_pengawas')
            ->join('sekolah','sekolah.id=laporan_mingguan_bantuan.sekolah_id')
            ->join('bantuan_sekolah','bantuan_sekolah.id=laporan_mingguan_bantuan.bantuan_sekolah_id')
            ->join('users','users.id=laporan_mingguan_bantuan.dibuat_oleh','left')
            ->where('laporan_mingguan_bantuan.id',$id)->first();
        if (!$report || !(new SekolahModel())->where('id',$report['sekolah_id'])->where('pengawas_id',session()->get('id'))->first()) return redirect()->back()->with('error','Laporan tidak ditemukan.');
        $template = (new LaporanTemplateBantuanModel())->where(['sekolah_id'=>$report['sekolah_id'],'bantuan_sekolah_id'=>$report['bantuan_sekolah_id']])->first();
        $report['template_name'] = $template['nama_template'] ?? '-';
        $html = view('pengawas/laporan_mingguan_pdf',['report'=>$report]);
        if (class_exists(\Dompdf\Dompdf::class)) {
            $options = new \Dompdf\Options(); $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options); $dompdf->loadHtml($html,'UTF-8'); $dompdf->setPaper('A4','portrait'); $dompdf->render();
            $name = 'Laporan_Mingguan_'.preg_replace('/[^A-Za-z0-9_-]+/','_', $report['nama_sekolah'].'_'.$report['nama_bantuan'].'_M'.$report['minggu_ke']).'.pdf';
            return $this->response->setHeader('Content-Type','application/pdf')->setHeader('Content-Disposition','attachment; filename="'.$name.'"')->setBody($dompdf->output());
        }
        return $this->response->setHeader('Content-Type','text/html; charset=UTF-8')->setHeader('Content-Disposition','inline')->setBody($html . '<script>window.onload=function(){window.print();}</script>');
    }
}
