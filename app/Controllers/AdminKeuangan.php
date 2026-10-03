<?php

namespace App\Controllers;

use App\Models\SekolahModel;
use App\Models\TimP2SPModel;
use App\Models\PersonilSekolahModel;
use Config\Database;
use DateTimeImmutable;

class AdminKeuangan extends BaseController
{
    private const GENERAL_EXPENSE_TYPES = [
        'Persiapan',
        'Perencanaan',
        'Pengawasan',
        'Pengelolaan',
        'Fisik',
        'Mebelair',
    ];

    private const CATEGORIES = [
        'buku-bank' => ['title' => 'Buku Bank', 'type' => 'ledger', 'jenis_buku' => 'bank'],
        'buku-kas-tunai' => ['title' => 'Buku Kas Tunai', 'type' => 'ledger', 'jenis_buku' => 'kas_tunai'],
        'buku-kas-umum' => ['title' => 'Buku Kas Umum', 'type' => 'ledger', 'jenis_buku' => 'kas_umum'],
        'bahan-bangunan' => ['title' => 'Bahan Bangunan', 'type' => 'materials', 'jenis_buku' => null],
        'ongkos-tukang' => ['title' => 'Ongkos Tukang', 'type' => 'labor', 'jenis_buku' => null],
    ];

    public function index(string $categorySlug)
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }

        $category = self::CATEGORIES[$categorySlug] ?? null;
        if ($category === null) {
            return redirect()->to('/admin/sekolah')->with('error', 'Kategori keuangan tidak ditemukan.');
        }

        $schoolModel = new SekolahModel();
        $schools = $schoolModel->orderBy('nama_sekolah', 'ASC')->findAll();
        if ($schools === []) {
            return redirect()->to('/admin/sekolah')->with('error', 'Tambahkan sekolah sebelum mencatat transaksi.');
        }

        $selectedSchoolId = (int) ($this->request->getGet('sekolah_id') ?? $schools[0]['id']);
        $school = null;
        foreach ($schools as $listedSchool) {
            if ((int) $listedSchool['id'] === $selectedSchoolId) {
                $school = $listedSchool;
                break;
            }
        }
        if (!$school) {
            return redirect()->to('/admin/keuangan/' . $categorySlug)->with('error', 'Sekolah tidak ditemukan.');
        }

        $isUploadCategory = in_array($categorySlug, ['bahan-bangunan', 'ongkos-tukang'], true);
        if ($isUploadCategory) {
            $allRows = Database::connect()->table('dokumen_keuangan')
                ->select('id, sekolah_id, tanggal_kuitansi AS tanggal, keterangan, file_pdf, created_at')
                ->where('sekolah_id', $selectedSchoolId)
                //->where('kategori', $categorySlug)
                ->orderBy('tanggal_kuitansi', 'DESC')
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
        } else {
            $builder = Database::connect()->table($this->tableFor($category));
            $builder->where('sekolah_id', $selectedSchoolId);
            if ($category['type'] === 'ledger') {
                $builder->where('jenis_buku', $category['jenis_buku']);
            }
            $allRows = $builder->orderBy('tanggal', 'ASC')->orderBy('id', 'ASC')->get()->getResultArray();
        }

        if ($category['type'] === 'ledger') {
            $balance = 0.0;
            foreach ($allRows as &$row) {
                $balance += (float) $row['penerimaan'] - (float) $row['pengeluaran'];
                $row['saldo'] = $balance;
            }
            unset($row);
        }
        $allRows = array_reverse($allRows);

        $selectedYear = (string) $this->request->getGet('tahun');
        if (preg_match('/^\d{4}$/D', $selectedYear) !== 1) {
            $selectedYear = '';
        }
        $selectedMonth = (string) $this->request->getGet('bulan');
        if (preg_match('/^(?:[1-9]|1[0-2])$/D', $selectedMonth) !== 1) {
            $selectedMonth = '';
        }

        $yearOptions = [date('Y') => (int) date('Y')];
        foreach ($allRows as $row) {
            $rowYear = substr((string) $row['tanggal'], 0, 4);
            $yearOptions[$rowYear] = (int) $rowYear;
        }
        rsort($yearOptions, SORT_NUMERIC);
        $rows = array_values(array_filter($allRows, static function (array $row) use ($selectedYear, $selectedMonth): bool {
            $rowDate = (string) $row['tanggal'];
            return ($selectedYear === '' || substr($rowDate, 0, 4) === $selectedYear)
                && ($selectedMonth === '' || (int) substr($rowDate, 5, 2) === (int) $selectedMonth);
        }));

        $sourceTransactions = [];
        if ($categorySlug === 'buku-kas-umum') {
            $db = Database::connect();
            $usedSources = $db->table('buku_keuangan')
                ->select('sumber_jenis, sumber_id')
                ->where('sekolah_id', $selectedSchoolId)
                ->where('jenis_buku', 'kas_umum')
                ->where('sumber_id IS NOT NULL', null, false)
                ->get()
                ->getResultArray();
            $usedSourceKeys = [];
            foreach ($usedSources as $usedSource) {
                $usedSourceKeys[$usedSource['sumber_jenis'] . ':' . $usedSource['sumber_id']] = true;
            }

            $sourceRows = $db->table('buku_keuangan')
                ->where('sekolah_id', $selectedSchoolId)
                ->whereIn('jenis_buku', ['bank', 'kas_tunai'])
                ->orderBy('tanggal', 'DESC')
                ->orderBy('id', 'DESC')
                ->get()
                ->getResultArray();
            foreach ($sourceRows as $sourceRow) {
                $sourceDate = (string) $sourceRow['tanggal'];
                if ($selectedYear !== '' && substr($sourceDate, 0, 4) !== $selectedYear) {
                    continue;
                }
                if ($selectedMonth !== '' && (int) substr($sourceDate, 5, 2) !== (int) $selectedMonth) {
                    continue;
                }

                $sourceKey = $sourceRow['jenis_buku'] . ':' . $sourceRow['id'];
                if (!isset($usedSourceKeys[$sourceKey])) {
                    $sourceTransactions[] = $sourceRow;
                }
            }
        }

        return view('admin/keuangan', [
            'title' => $category['title'],
            'activeMenu' => 'keuangan-' . $categorySlug,
            'categorySlug' => $categorySlug,
            'category' => $category,
            'isUploadCategory' => $isUploadCategory,
            'isUploadCategory' => $isUploadCategory,
            'schools' => $schools,
            'school' => $school,
            'rows' => $rows,
            'yearOptions' => $yearOptions,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'sourceTransactions' => $sourceTransactions,
            'generalExpenseTypes' => self::GENERAL_EXPENSE_TYPES,
        ]);
    }

    public function print(string $categorySlug)
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }

        $category = self::CATEGORIES[$categorySlug] ?? null;
        if ($category === null) {
            return redirect()->to('/admin/sekolah')->with('error', 'Kategori keuangan tidak ditemukan.');
        }

        $schoolId = (int) $this->request->getGet('sekolah_id');
        $school = (new SekolahModel())->find($schoolId);
        if (!$school) {
            return redirect()->to($this->categoryUrl($categorySlug, $schoolId))->with('error', 'Sekolah tidak ditemukan.');
        }

        $year = (string) $this->request->getGet('tahun');
        if (preg_match('/^\d{4}$/D', $year) !== 1) {
            $year = '';
        }
        $month = (string) $this->request->getGet('bulan');
        if (preg_match('/^(?:[1-9]|1[0-2])$/D', $month) !== 1) {
            $month = '';
        }

        $db = Database::connect();
        $builder = $db->table($this->tableFor($category))->where('sekolah_id', $schoolId);
        if ($category['type'] === 'ledger') {
            $builder->where('jenis_buku', $category['jenis_buku']);
        }
        $allRows = $builder->orderBy('tanggal', 'ASC')->orderBy('id', 'ASC')->get()->getResultArray();

        if ($year === '' && $month !== '') {
            $yearsForMonth = [];
            foreach ($allRows as $row) {
                if ((int) substr((string) $row['tanggal'], 5, 2) === (int) $month) {
                    $yearsForMonth[] = (int) substr((string) $row['tanggal'], 0, 4);
                }
            }
            $year = $yearsForMonth === [] ? date('Y') : (string) max($yearsForMonth);
        }

        $openingBalance = 0.0;
        if ($category['type'] === 'ledger' && ($year !== '' || $month !== '')) {
            if ($year !== '' && $month !== '') {
                $periodStart = new DateTimeImmutable(sprintf('%s-%02d-01', $year, (int) $month));
            } elseif ($year !== '') {
                $periodStart = new DateTimeImmutable($year . '-01-01');
            } else {
                $latestYear = null;
                foreach ($allRows as $row) {
                    if ((int) substr((string) $row['tanggal'], 5, 2) === (int) $month) {
                        $rowYear = (int) substr((string) $row['tanggal'], 0, 4);
                        $latestYear = $latestYear === null ? $rowYear : max($latestYear, $rowYear);
                    }
                }
                $periodStart = $latestYear === null
                    ? null
                    : new DateTimeImmutable(sprintf('%04d-%02d-01', $latestYear, (int) $month));
            }

            if ($periodStart !== null) {
                $openingBalance = $this->getLedgerBalance(
                    Database::connect(),
                    $schoolId,
                    $category['jenis_buku'],
                    $periodStart->modify('-1 day')->format('Y-m-d')
                );
            }
        }

        $runningBalance = 0.0;
        $rows = [];
        foreach ($allRows as $row) {
            $rowDate = (string) $row['tanggal'];
            $matchesPeriod = $this->matchesSelectedPeriod($rowDate, $year, $month);
            if ($category['type'] === 'ledger') {
                $runningBalance += (float) $row['penerimaan'] - (float) $row['pengeluaran'];
                $row['saldo'] = $runningBalance;
            }
            if ($matchesPeriod) {
                $rows[] = $row;
            }
        }
        $rows = array_reverse($rows);

        $totalReceipts = 0.0;
        $totalExpenses = 0.0;
        foreach ($rows as $row) {
            if ($category['type'] === 'ledger') {
                $totalReceipts += (float) $row['penerimaan'];
                $totalExpenses += (float) $row['pengeluaran'];
            }
        }

        $closingDate = $this->closingDate($year, $month, $allRows);
        $bankBalance = $this->getLedgerBalance($db, $schoolId, 'bank', $closingDate);
        $cashBalance = $this->getLedgerBalance($db, $schoolId, 'kas_tunai', $closingDate);
        $p2sp = [];
        foreach ((new TimP2SPModel())->getBySekolah($schoolId) as $member) {
            $p2sp[$member['posisi']] = $member;
        }
        $schoolPersonnel = (new PersonilSekolahModel())->getBySekolah($schoolId) ?? [];

        return view('admin/keuangan_print', [
            'category' => $category,
            'categorySlug' => $categorySlug,
            'school' => $school,
            'rows' => $rows,
            'periodLabel' => $this->periodLabel($year, $month),
            'year' => $year !== '' ? $year : date('Y', strtotime($closingDate)),
            'month' => $month !== '' ? (int) $month : null,
            'openingBalance' => $openingBalance,
            'closingBalance' => $openingBalance + $totalReceipts - $totalExpenses,
            'totalReceipts' => $totalReceipts,
            'totalExpenses' => $totalExpenses,
            'bankBalance' => $bankBalance,
            'cashBalance' => $cashBalance,
            'p2sp' => $p2sp,
            'schoolPersonnel' => $schoolPersonnel,
            'closingDate' => $closingDate,
        ]);
    }

    public function store(string $categorySlug)
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }

        $category = self::CATEGORIES[$categorySlug] ?? null;
        if ($category === null) {
            return redirect()->to('/admin/sekolah')->with('error', 'Kategori keuangan tidak ditemukan.');
        }

        $schoolId = (int) $this->request->getPost('sekolah_id');
        if (!(new SekolahModel())->find($schoolId)) {
            return redirect()->back()->withInput()->with('error', 'Pilih sekolah yang valid.');
        }
        if (in_array($categorySlug, ['bahan-bangunan', 'ongkos-tukang'], true)) {
            return redirect()->back()->with('error', 'Kategori ini menggunakan unggah PDF.');
        }

        if ($categorySlug === 'buku-kas-umum') {
            $sourceId = (int) $this->request->getPost('source_transaction_id');
            $db = Database::connect();
            $source = $db->table('buku_keuangan')
                ->where('id', $sourceId)
                ->where('sekolah_id', $schoolId)
                ->whereIn('jenis_buku', ['bank', 'kas_tunai'])
                ->get()
                ->getRowArray();
            if (!$source) {
                return redirect()->back()->withInput()->with('error', 'Pilih transaksi bank atau kas tunai yang valid.');
            }

            $expenseType = trim((string) $this->request->getPost('jenis_biaya'));
            $isExpense = (float) $source['pengeluaran'] > 0;
            if ($isExpense && !in_array($expenseType, self::GENERAL_EXPENSE_TYPES, true)) {
                return redirect()->back()->withInput()->with('error', 'Pilih jenis biaya untuk transaksi pengeluaran.');
            }

            $duplicate = $db->table('buku_keuangan')
                ->where('sekolah_id', $schoolId)
                ->where('jenis_buku', 'kas_umum')
                ->where('sumber_jenis', $source['jenis_buku'])
                ->where('sumber_id', $sourceId)
                ->countAllResults();
            if ($duplicate > 0) {
                return redirect()->back()->withInput()->with('error', 'Transaksi tersebut sudah ada di Buku Kas Umum.');
            }

            $saved = $db->table('buku_keuangan')->insert([
                'sekolah_id' => $schoolId,
                'jenis_buku' => 'kas_umum',
                'tanggal' => $source['tanggal'],
                'nomor_bukti' => $source['nomor_bukti'],
                'uraian' => $source['uraian'],
                'penerimaan' => $source['penerimaan'],
                'pengeluaran' => $source['pengeluaran'],
                'sumber_jenis' => $source['jenis_buku'],
                'sumber_id' => $sourceId,
                'jenis_biaya' => $isExpense ? $expenseType : null,
            ]);
            if (!$saved) {
                return redirect()->back()->withInput()->with('error', 'Transaksi gagal dimasukkan ke Buku Kas Umum.');
            }

            return redirect()->to($this->categoryUrl($categorySlug, $schoolId))->with('success', 'Transaksi berhasil disinkronkan ke Buku Kas Umum.');
        }

        $data = $this->recordData($category);
        if (is_string($data)) {
            return redirect()->back()->withInput()->with('error', $data);
        }

        $data['sekolah_id'] = $schoolId;
        if ($category['type'] === 'ledger') {
            $data['jenis_buku'] = $category['jenis_buku'];
        }

        $saved = Database::connect()->table($this->tableFor($category))->insert($data);
        if (!$saved) {
            return redirect()->back()->withInput()->with('error', 'Transaksi gagal disimpan.');
        }

        return redirect()->to($this->categoryUrl($categorySlug, $schoolId, (string) $this->request->getPost('tahun'), (string) $this->request->getPost('bulan')))->with('success', 'Transaksi berhasil disimpan.');
    }

    public function uploadDocument(string $categorySlug)
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }
        if (!in_array($categorySlug, ['bahan-bangunan', 'ongkos-tukang'], true)) {
            return redirect()->to('/admin/sekolah')->with('error', 'Kategori dokumen tidak valid.');
        }

        $schoolId = (int) $this->request->getPost('sekolah_id');
        if (!(new SekolahModel())->find($schoolId)) {
            return redirect()->back()->withInput()->with('error', 'Pilih sekolah yang valid.');
        }

        $date = trim((string) $this->request->getPost('tanggal_kuitansi'));
        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $description = trim((string) $this->request->getPost('keterangan'));
        $file = $this->request->getFile('file_pdf');
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date || $description === '' || mb_strlen($description) > 250) {
            return redirect()->back()->withInput()->with('error', 'Tanggal kuitansi dan keterangan wajib diisi; keterangan maksimal 250 karakter.');
        }
        if (!$file || !$file->isValid() || $file->hasMoved() || strtolower($file->getClientExtension()) !== 'pdf' || $file->getMimeType() !== 'application/pdf' || $file->getSize() > 15 * 1024 * 1024) {
            return redirect()->back()->withInput()->with('error', 'File harus berupa PDF maksimal 15 MB.');
        }

        $directory = WRITEPATH . 'uploads/keuangan';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return redirect()->back()->withInput()->with('error', 'Folder penyimpanan PDF tidak dapat dibuat.');
        }
        $fileName = bin2hex(random_bytes(16)) . '.pdf';
        try {
            $file->move($directory, $fileName);
        } catch (\Throwable $exception) {
            return redirect()->back()->withInput()->with('error', 'File PDF gagal disimpan.');
        }

        $inserted = Database::connect()->table('dokumen_keuangan')->insert([
            'sekolah_id'       => $schoolId,
            'tanggal_kuitansi' => $date,
            'keterangan'       => $description,
            'file_pdf'         => 'uploads/keuangan/' . $fileName,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        if (!$inserted) {
            @unlink($directory . DIRECTORY_SEPARATOR . $fileName);
            return redirect()->back()->withInput()->with('error', 'Data kuitansi gagal disimpan.');
        }

        return redirect()->to($this->categoryUrl($categorySlug, $schoolId, (string) $this->request->getPost('tahun'), (string) $this->request->getPost('bulan')))->with('success', 'PDF kuitansi berhasil diunggah.');
    }

    public function viewDocument(string $categorySlug, int $documentId)
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }
        if (!in_array($categorySlug, ['bahan-bangunan', 'ongkos-tukang'], true)) {
            return redirect()->to('/admin/sekolah')->with('error', 'Kategori dokumen tidak valid.');
        }

        $document = Database::connect()->table('dokumen_keuangan')
            ->where('id', $documentId)
            ->get()
            ->getRowArray();
        $path = $document ? $this->resolveFinancePdf($document['file_pdf']) : null;
        if (!$path || !is_file($path) || mime_content_type($path) !== 'application/pdf') {
            return redirect()->back()->with('error', 'PDF kuitansi tidak ditemukan atau tidak valid.');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="kuitansi-' . $documentId . '.pdf"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody((string) file_get_contents($path));
    }

    public function deleteDocument(string $categorySlug, int $documentId)
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }
        if (!in_array($categorySlug, ['bahan-bangunan', 'ongkos-tukang'], true)) {
            return redirect()->to('/admin/sekolah')->with('error', 'Kategori dokumen tidak valid.');
        }

        $db = Database::connect();
        $document = $db->table('dokumen_keuangan')
            ->where('id', $documentId)
            ->get()
            ->getRowArray();
        if (!$document) {
            return redirect()->to('/admin/keuangan/' . $categorySlug)->with('error', 'Dokumen kuitansi tidak ditemukan.');
        }

        $path = $this->resolveFinancePdf($document['file_pdf']);
        if ($path && is_file($path) && !@unlink($path)) {
            log_message('error', 'Gagal menghapus file dokumen keuangan: ' . $path);
            return redirect()->to($this->categoryUrl($categorySlug, (int) $document['sekolah_id']))->with('error', 'File PDF gagal dihapus. Periksa izin folder writable/uploads/keuangan.');
        }
        $db->table('dokumen_keuangan')->where('id', $documentId)->delete();

        return redirect()->to($this->categoryUrl($categorySlug, (int) $document['sekolah_id']))->with('success', 'Data kuitansi beserta file PDF berhasil dihapus.');
    }

    public function update(string $categorySlug, int $recordId)
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }

        $category = self::CATEGORIES[$categorySlug] ?? null;
        if ($category === null) {
            return redirect()->to('/admin/sekolah')->with('error', 'Kategori keuangan tidak ditemukan.');
        }
        if ($categorySlug === 'buku-kas-umum') {
            return redirect()->to('/admin/keuangan/buku-kas-umum')->with('error', 'Ubah transaksi melalui Buku Bank atau Buku Kas Tunai, lalu pilih ulang ke Buku Kas Umum.');
        }

        $db = Database::connect();
        $builder = $db->table($this->tableFor($category))->where('id', $recordId);
        if ($category['type'] === 'ledger') {
            $builder->where('jenis_buku', $category['jenis_buku']);
        }
        $record = $builder->get()->getRowArray();
        if (!$record) {
            return redirect()->to('/admin/keuangan/' . $categorySlug)->with('error', 'Transaksi tidak ditemukan.');
        }

        $data = $this->recordData($category);
        if (is_string($data)) {
            return redirect()->back()->withInput()->with('error', $data);
        }

        $updated = $db->table($this->tableFor($category))
            ->where('id', $recordId)
            ->where('sekolah_id', (int) $record['sekolah_id'])
            ->update($data);
        if (!$updated) {
            return redirect()->back()->withInput()->with('error', 'Transaksi gagal diperbarui.');
        }

        return redirect()->to($this->categoryUrl($categorySlug, (int) $record['sekolah_id'], (string) $this->request->getPost('tahun'), (string) $this->request->getPost('bulan')))->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function delete(string $categorySlug, int $recordId)
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }

        $category = self::CATEGORIES[$categorySlug] ?? null;
        if ($category === null) {
            return redirect()->to('/admin/sekolah')->with('error', 'Kategori keuangan tidak ditemukan.');
        }

        $db = Database::connect();
        $builder = $db->table($this->tableFor($category))->where('id', $recordId);
        if ($category['type'] === 'ledger') {
            $builder->where('jenis_buku', $category['jenis_buku']);
        }
        $record = $builder->get()->getRowArray();
        if (!$record) {
            return redirect()->to('/admin/keuangan/' . $categorySlug)->with('error', 'Transaksi tidak ditemukan.');
        }

        $db->table($this->tableFor($category))
            ->where('id', $recordId)
            ->where('sekolah_id', (int) $record['sekolah_id'])
            ->delete();

        return redirect()->to($this->categoryUrl($categorySlug, (int) $record['sekolah_id'], (string) $this->request->getPost('tahun'), (string) $this->request->getPost('bulan')))->with('success', 'Transaksi berhasil dihapus.');
    }

    private function recordData(array $category): array|string
    {
        $date = trim((string) $this->request->getPost('tanggal'));
        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
            return 'Tanggal transaksi tidak valid.';
        }

        if ($category['type'] === 'ledger') {
            $voucherNumber = trim((string) $this->request->getPost('nomor_bukti'));
            $description = trim((string) $this->request->getPost('uraian'));
            $receipt = $this->decimalAmount('penerimaan', false, 13);
            $expense = $this->decimalAmount('pengeluaran', false, 13);
            if ($description === '' || mb_strlen($description) > 250 || mb_strlen($voucherNumber) > 80) {
                return 'Uraian wajib diisi; uraian maksimal 250 dan nomor bukti maksimal 80 karakter.';
            }
            if (is_string($receipt) || is_string($expense)) {
                return 'Penerimaan dan pengeluaran harus bernilai nol atau lebih, maksimal dua desimal.';
            }
            if ($receipt === 0.0 && $expense === 0.0) {
                return 'Isi nilai penerimaan atau pengeluaran.';
            }
            if ($receipt > 0 && $expense > 0) {
                return 'Isi penerimaan atau pengeluaran pada satu transaksi, bukan keduanya.';
            }

            return [
                'tanggal' => $date,
                'nomor_bukti' => $voucherNumber !== '' ? $voucherNumber : null,
                'uraian' => $description,
                'penerimaan' => number_format($receipt, 2, '.', ''),
                'pengeluaran' => number_format($expense, 2, '.', ''),
            ];
        }

        $volume = $this->decimalAmount('volume', true, 10);
        $unit = trim((string) $this->request->getPost('satuan'));
        if (is_string($volume) || $unit === '' || mb_strlen($unit) > 30) {
            return 'Volume harus lebih besar dari nol dan satuan wajib diisi (maksimal 30 karakter).';
        }

        if ($category['type'] === 'materials') {
            $name = trim((string) $this->request->getPost('nama_bahan'));
            $unitPrice = $this->decimalAmount('harga_satuan', false, 13);
            if ($name === '' || mb_strlen($name) > 150 || is_string($unitPrice)) {
                return 'Nama bahan wajib diisi (maksimal 150 karakter) dan harga satuan harus nol atau lebih.';
            }

            return [
                'tanggal' => $date,
                'nama_bahan' => $name,
                'volume' => number_format($volume, 2, '.', ''),
                'satuan' => $unit,
                'harga_satuan' => number_format($unitPrice, 2, '.', ''),
                'total' => number_format(round($volume * $unitPrice, 2), 2, '.', ''),
            ];
        }

        $work = trim((string) $this->request->getPost('pekerjaan'));
        $recipient = trim((string) $this->request->getPost('penerima'));
        $rate = $this->decimalAmount('tarif', false, 13);
        if ($work === '' || mb_strlen($work) > 200 || $recipient === '' || mb_strlen($recipient) > 150 || is_string($rate)) {
            return 'Pekerjaan dan penerima wajib diisi; tarif harus nol atau lebih.';
        }

        return [
            'tanggal' => $date,
            'pekerjaan' => $work,
            'penerima' => $recipient,
            'volume' => number_format($volume, 2, '.', ''),
            'satuan' => $unit,
            'tarif' => number_format($rate, 2, '.', ''),
            'total' => number_format(round($volume * $rate, 2), 2, '.', ''),
        ];
    }

    private function decimalAmount(string $field, bool $mustBePositive, int $integerDigits): float|string
    {
        $value = trim((string) $this->request->getPost($field));
        $pattern = '/^\d{1,' . $integerDigits . '}(?:\.\d{1,2})?$/D';
        if (preg_match($pattern, $value) !== 1) {
            return 'invalid';
        }

        $amount = (float) $value;
        if ($mustBePositive && $amount <= 0) {
            return 'invalid';
        }

        return $amount;
    }

    private function resolveFinancePdf(?string $relativePath): ?string
    {
        if (!$relativePath || preg_match('#^uploads/keuangan/[a-f0-9]{32}\.pdf$#i', $relativePath) !== 1) {
            return null;
        }

        $uploadRoot = realpath(WRITEPATH . 'uploads/keuangan');
        $filePath = realpath(WRITEPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
        if (!$uploadRoot || !$filePath || !str_starts_with($filePath, $uploadRoot . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $filePath;
    }

    private function tableFor(array $category): string
    {
        return match ($category['type']) {
            'ledger' => 'buku_keuangan',
            'materials' => 'bahan_bangunan',
            default => 'ongkos_tukang',
        };
    }

    private function categoryUrl(string $categorySlug, int $schoolId, string $year = '', string $month = ''): string
    {
        $query = ['sekolah_id' => $schoolId];
        if (preg_match('/^\d{4}$/D', $year) === 1) {
            $query['tahun'] = $year;
        }
        if (preg_match('/^(?:[1-9]|1[0-2])$/D', $month) === 1) {
            $query['bulan'] = $month;
        }

        return '/admin/keuangan/' . $categorySlug . '?' . http_build_query($query);
    }

    private function matchesSelectedPeriod(string $date, string $year, string $month): bool
    {
        return ($year === '' || substr($date, 0, 4) === $year)
            && ($month === '' || (int) substr($date, 5, 2) === (int) $month);
    }

    private function closingDate(string $year, string $month, array $rows): string
    {
        if ($month !== '') {
            $closingYear = $year !== '' ? $year : date('Y');
            return (new DateTimeImmutable(sprintf('%s-%02d-01', $closingYear, (int) $month)))
                ->modify('last day of this month')
                ->format('Y-m-d');
        }
        if ($year !== '') {
            return $year . '-12-31';
        }

        $latestDate = $rows === [] ? date('Y-m-d') : (string) $rows[array_key_last($rows)]['tanggal'];
        return (new DateTimeImmutable($latestDate))->modify('last day of this month')->format('Y-m-d');
    }

    private function getLedgerBalance($db, int $schoolId, string $bookType, string $throughDate): float
    {
        $transactions = $db->table('buku_keuangan')
            ->where('sekolah_id', $schoolId)
            ->where('jenis_buku', $bookType)
            ->where('tanggal <=', $throughDate)
            ->get()
            ->getResultArray();
        $balance = 0.0;
        foreach ($transactions as $transaction) {
            $balance += (float) $transaction['penerimaan'] - (float) $transaction['pengeluaran'];
        }

        return $balance;
    }

    private function periodLabel(string $year, string $month): string
    {
        $monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        if ($year !== '' && $month !== '') {
            return $monthNames[(int) $month] . ' ' . $year;
        }
        if ($year !== '') {
            return 'Tahun ' . $year;
        }
        if ($month !== '') {
            return $monthNames[(int) $month] . ' (semua tahun)';
        }

        return 'Semua Periode';
    }

    private function adminOnly()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Modul keuangan hanya dapat diakses admin.');
        }

        return null;
    }
}