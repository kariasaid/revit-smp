<?php

namespace App\Controllers;

use App\Models\SekolahModel;
use App\Models\TimP2SPModel;
use Config\Database;
use Throwable;

class TimP2SP extends BaseController
{
    private const POSITIONS = [
        'penanggung_jawab' => 'Penanggung Jawab',
        'ketua'            => 'Ketua P2SP',
        'bendahara'        => 'Bendahara',
        'sekretaris'       => 'Sekretaris',
        'kepala_pelaksana' => 'Kepala Pelaksana',
        'keamanan'         => 'Keamanan',
    ];

    public function index()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'perencana'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Tim P2SP hanya dapat dikelola admin atau perencana.');
        }

        $schools = $role === 'admin'
            ? (new SekolahModel())->orderBy('nama_sekolah', 'ASC')->findAll()
            : (new SekolahModel())->getByPerencana((string) session()->get('nama_lengkap'));
        if ($schools === []) {
            return redirect()->to('/dashboard')->with('error', 'Belum ada sekolah yang ditugaskan kepada akun ini.');
        }

        $selectedSchoolId = (int) ($this->request->getGet('sekolah_id') ?? $schools[0]['id']);
        $school = null;
        foreach ($schools as $assignedSchool) {
            if ((int) $assignedSchool['id'] === $selectedSchoolId) {
                $school = $assignedSchool;
                break;
            }
        }
        if (!$school) {
            return redirect()->to('/tim-p2sp')->with('error', 'Sekolah tidak termasuk penugasan Anda.');
        }

        $members = [];
        foreach ((new TimP2SPModel())->getBySekolah($selectedSchoolId) as $member) {
            $members[$member['posisi']] = $member;
        }
        if (!isset($members['penanggung_jawab'])) {
            $schoolPersonnel = Database::connect()->table('personil_sekolah')
                ->select('kepala_sekolah')
                ->where('sekolah_id', $selectedSchoolId)
                ->get()
                ->getRowArray();
            $members['penanggung_jawab'] = [
                'nama' => $schoolPersonnel['kepala_sekolah'] ?? '',
                'nip_nik' => '',
                'jabatan' => 'Kepala Sekolah',
                'ttd' => null,
            ];
        }

        foreach (self::POSITIONS as $key => $label) {
            $members[$key] ??= ['nama' => '', 'nip_nik' => '', 'jabatan' => '', 'ttd' => null];
        }
        $oldMembers = old('anggota');
        if (is_array($oldMembers)) {
            foreach (self::POSITIONS as $key => $label) {
                if (isset($oldMembers[$key]) && is_array($oldMembers[$key])) {
                    foreach (['nama', 'nip_nik', 'jabatan'] as $field) {
                        if (isset($oldMembers[$key][$field]) && is_string($oldMembers[$key][$field])) {
                            $members[$key][$field] = $oldMembers[$key][$field];
                        }
                    }
                }
            }
        }

        return view('tim_p2sp/index', [
            'title' => 'Tim P2SP',
            'activeMenu' => 'tim-p2sp',
            'schools' => $schools,
            'school' => $school,
            'members' => $members,
            'positions' => self::POSITIONS,
        ]);
    }

    public function save()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'perencana'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Tim P2SP hanya dapat dikelola admin atau perencana.');
        }

        $schoolId = (int) $this->request->getPost('sekolah_id');
        $schools = $role === 'admin'
            ? (new SekolahModel())->findAll()
            : (new SekolahModel())->getByPerencana((string) session()->get('nama_lengkap'));
        $school = null;
        foreach ($schools as $assignedSchool) {
            if ((int) $assignedSchool['id'] === $schoolId) {
                $school = $assignedSchool;
                break;
            }
        }
        if (!$school) {
            return redirect()->to('/tim-p2sp')->with('error', 'Sekolah tidak termasuk penugasan Anda.');
        }

        $postedMembers = $this->request->getPost('anggota');
        if (!is_array($postedMembers)) {
            return redirect()->back()->withInput()->with('error', 'Data anggota Tim P2SP tidak valid.');
        }

        $model = new TimP2SPModel();
        $existingMembers = [];
        foreach ($model->getBySekolah($schoolId) as $member) {
            $existingMembers[$member['posisi']] = $member;
        }
        $rows = [];
        $newFiles = [];
        $oldFiles = [];
        $uploadDirectory = ROOTPATH . 'public/uploads/ttd-p2sp';

        foreach (self::POSITIONS as $position => $label) {
            $input = $postedMembers[$position] ?? [];
            if (!is_array($input)) {
                return redirect()->back()->withInput()->with('error', 'Data anggota Tim P2SP tidak valid.');
            }

            $name = trim((string) ($input['nama'] ?? ''));
            $identity = trim((string) ($input['nip_nik'] ?? ''));
            $jobTitle = trim((string) ($input['jabatan'] ?? ''));
            if ($name === '' || mb_strlen($name) > 150 || $identity === '' || mb_strlen($identity) > 30 || $jobTitle === '' || mb_strlen($jobTitle) > 150) {
                return redirect()->back()->withInput()->with('error', 'Nama, NIP/NIK, dan jabatan wajib diisi untuk ' . $label . '.');
            }

            $signature = $this->request->getFile('ttd_' . $position);
            if ($signature && $signature->getError() !== UPLOAD_ERR_NO_FILE) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!$signature->isValid() || $signature->getSizeByUnit('kb') > 2048 || !in_array($signature->getMimeType(), $allowedMimeTypes, true)) {
                    return redirect()->back()->withInput()->with('error', 'Tanda tangan ' . $label . ' harus berupa JPG, PNG, atau WebP maksimal 2 MB.');
                }
            } elseif (empty($existingMembers[$position]['ttd'])) {
                return redirect()->back()->withInput()->with('error', 'Gambar tanda tangan wajib diunggah untuk ' . $label . '.');
            }
        }

        foreach (self::POSITIONS as $position => $label) {
            $input = $postedMembers[$position] ?? [];
            $name = trim((string) ($input['nama'] ?? ''));
            $identity = trim((string) ($input['nip_nik'] ?? ''));
            $jobTitle = trim((string) ($input['jabatan'] ?? ''));

            $signaturePath = $existingMembers[$position]['ttd'] ?? null;
            $signature = $this->request->getFile('ttd_' . $position);
            if ($signature && $signature->getError() !== UPLOAD_ERR_NO_FILE) {
                $allowedMimeTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                $mimeType = $signature->getMimeType();
                if (!$signature->isValid() || $signature->getSizeByUnit('kb') > 2048 || !isset($allowedMimeTypes[$mimeType])) {
                    return redirect()->back()->withInput()->with('error', 'Tanda tangan ' . $label . ' harus berupa JPG, PNG, atau WebP maksimal 2 MB.');
                }
                if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                    return redirect()->back()->withInput()->with('error', 'Folder penyimpanan tanda tangan tidak dapat dibuat.');
                }

                $newName = bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mimeType];
                try {
                    $signature->move($uploadDirectory, $newName);
                } catch (Throwable $exception) {
                    foreach ($newFiles as $filePath) {
                        if (is_file($filePath)) {
                            unlink($filePath);
                        }
                    }
                    return redirect()->back()->withInput()->with('error', 'Tanda tangan ' . $label . ' gagal disimpan.');
                }
                $newFiles[] = $uploadDirectory . DIRECTORY_SEPARATOR . $newName;
                if (!empty($signaturePath) && preg_match('#^uploads/ttd-p2sp/[a-f0-9]{32}\.(jpg|png|webp)$#i', $signaturePath)) {
                    $oldFiles[] = ROOTPATH . 'public/' . $signaturePath;
                }
                $signaturePath = 'uploads/ttd-p2sp/' . $newName;
            }

            $rows[$position] = [
                'sekolah_id' => $schoolId,
                'posisi' => $position,
                'nama' => $name,
                'nip_nik' => $identity,
                'jabatan' => $jobTitle,
                'ttd' => $signaturePath,
            ];
        }

        $db = Database::connect();
        $db->transStart();
        foreach ($rows as $position => $row) {
            if (isset($existingMembers[$position])) {
                $model->update($existingMembers[$position]['id'], $row);
            } else {
                $model->insert($row);
            }
        }
        $db->transComplete();

        if ($db->transStatus() === false) {
            foreach ($newFiles as $filePath) {
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }
            return redirect()->back()->withInput()->with('error', 'Data Tim P2SP gagal disimpan.');
        }

        foreach ($oldFiles as $filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        return redirect()->to('/tim-p2sp?sekolah_id=' . $schoolId)->with('success', 'Data Tim P2SP berhasil disimpan.');
    }
}