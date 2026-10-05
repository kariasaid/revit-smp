<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'REVIT SMP') ?> | Sistem Pengarsipan Dokumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1d5296;
            --primary-dark: #153d70;
            --primary-light: #e8f0fe;
            --sidebar-width: 260px;
            --bg-body: #f4f6f9;
            --card-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        * { font-family: 'Inter', sans-serif; }
        body { background: var(--bg-body); margin: 0; min-height: 100vh; }

        /* Sidebar – dark blue SaaS style */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #0a2540 0%, #0d2f52 100%);
            border-right: none;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 1000;
            overflow-y: auto;
            transition: transform .3s;
            display: flex;
            flex-direction: column;
        }
        .sidebar-brand {
            padding: 1.35rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-brand img { height: 32px; }
        .sidebar-brand span { font-weight: 700; color: #fff; font-size: 1.05rem; letter-spacing: .01em; }
        .sidebar-brand .brand-sub { font-size: .68rem; color: rgba(255,255,255,.55); font-weight: 400; margin-top: 1px; }
        .nav-sidebar { padding: 1rem .75rem; flex: 1; }
        .nav-sidebar .nav-link {
            color: rgba(255,255,255,.7);
            border-radius: .55rem;
            padding: .65rem 1rem;
            margin-bottom: .2rem;
            font-size: .88rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .nav-sidebar .nav-link:hover { background: rgba(255,255,255,.08); color: #fff; }
        .nav-sidebar .nav-link.active {
            background: #1d6fd8;
            color: #fff;
            box-shadow: 0 4px 12px rgba(29,111,216,.35);
        }
        .nav-sidebar .nav-link i { font-size: 1.1rem; width: 22px; text-align: center; }
        .nav-sidebar .submenu { padding-left: 2.25rem; }
        .nav-sidebar .submenu .nav-link { font-size: .82rem; padding: .4rem 1rem; color: rgba(255,255,255,.55); }
        .nav-sidebar .submenu .nav-link:hover,
        .nav-sidebar .submenu .nav-link.active { color: #fff; background: rgba(255,255,255,.08); }
        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,.08);
            color: rgba(255,255,255,.45);
            font-size: .72rem;
        }
        .sidebar-footer .sf-brand { color: rgba(255,255,255,.75); font-weight: 600; font-size: .8rem; }

        /* Main content */
        .main-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; min-width: 0; display: flex; flex-direction: column; }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: .85rem 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .topbar-title { font-weight: 600; color: #1f2937; font-size: 1.05rem; }
        .content-area { padding: 1.5rem 1.75rem; flex: 1; min-width: 0; background: #f0f4f8; }
        .nav-sidebar .nav-link .bi-chevron-down { color: rgba(255,255,255,.4); }

        /* Cards */
        .card { border: none; border-radius: .75rem; box-shadow: var(--card-shadow); }
        .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; font-weight: 600; }
        .stat-card {
            background: #fff;
            border-radius: .75rem;
            padding: 1.25rem;
            box-shadow: var(--card-shadow);
            height: 100%;
        }
        .stat-card .stat-label { font-size: .8rem; color: #6b7280; margin-bottom: .35rem; }
        .stat-card .stat-value { font-size: 1.35rem; font-weight: 700; color: #111827; }
        .stat-card .stat-value.text-primary { color: var(--primary) !important; }
        .stat-card .stat-value.text-success { color: #059669 !important; }
        .stat-card .stat-value.text-warning { color: #d97706 !important; }

        /* Buttons */
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .btn-success { background: #059669; border-color: #059669; }
        .btn-outline-primary { color: var(--primary); border-color: var(--primary); }
        .btn-outline-primary:hover { background: var(--primary); color: #fff; }

        /* Badge */
        .badge-diterima { background: #d1fae5; color: #065f46; font-weight: 500; }
        .badge-deviasi-pos { background: #d1fae5; color: #065f46; }
        .badge-deviasi-neg { background: #fee2e2; color: #991b1b; }

        /* Table */
        .table thead th {
            background: #f9fafb;
            font-size: .78rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .03em;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }
        .table td { vertical-align: middle; font-size: .875rem; }

        /* Footer */
        .app-footer {
            text-align: center;
            padding: 1rem;
            font-size: .8rem;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            background: #fff;
        }

        /* Alert warning soft */
        .alert-soft-warning {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            border-radius: .75rem;
        }
        .progress-photo-input { color: transparent; }
        .progress-photo-input::file-selector-button { color: var(--bs-body-color); }
        .progress-photo-input::-webkit-file-upload-button { color: var(--bs-body-color); }
        .table-responsive { width: 100%; -webkit-overflow-scrolling: touch; }
        .sidebar-backdrop { display: none; }

        @media (max-width: 991.98px) {
            .sidebar { width: min(var(--sidebar-width), 88vw); z-index: 1050; transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-backdrop { position: fixed; inset: 0; z-index: 1040; border: 0; background: rgba(15, 23, 42, .38); }
            .sidebar-backdrop.show { display: block; }
            body.sidebar-open { overflow: hidden; }
            .main-wrapper { margin-left: 0; }
            .topbar { padding: .75rem 1.25rem; }
            .content-area { padding: 1.25rem; }
            .content-area .row > * { min-width: 0; }
            .content-area .card-header.d-flex { flex-wrap: wrap; gap: .5rem; }
        }

        @media (max-width: 575.98px) {
            .topbar { padding: .65rem .75rem; }
            .topbar-title { font-size: .95rem; }
            .content-area { padding: .75rem; }
            .content-area > .d-flex { gap: .75rem !important; }
            .card { border-radius: .625rem; }
            .card-header { padding: .75rem; }
            .card-body:not(.p-0) { padding: .875rem; }
            .stat-card { padding: .875rem; }
            .stat-card .stat-value { font-size: 1.1rem; overflow-wrap: anywhere; }
            .table td { font-size: .8125rem; }
            .table-responsive > .table { margin-bottom: 0; }
            .content-area .btn { max-width: 100%; }
            .content-area .input-group > .form-control,
            .content-area .input-group > .form-select { min-width: 0; }
            .app-footer { padding: .75rem .5rem; font-size: .7rem; line-height: 1.45; }
            .modal-dialog { margin: .5rem; }
            .modal-content { max-width: 100%; }
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div style="width:38px;height:38px;background:linear-gradient(135deg,#1d6fd8,#3b9cff);border-radius:10px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(29,111,216,.4);">
                <i class="bi bi-building text-white" style="font-size:1.1rem"></i>
            </div>
            <div>
                <span>TAKSU DEWATA</span>
                <div class="brand-sub">Revitalisasi Sekolah</div>
            </div>
        </div>
        <nav class="nav-sidebar">
            <?php if (session()->get('role') !== 'admin'): ?>
            <a href="<?= base_url('dashboard') ?>" class="nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
            <?php endif; ?>
            <?php if (session()->get('role') === 'perencana'): ?>
                <a href="<?= base_url('tim-p2sp') ?>" class="nav-link <?= ($activeMenu ?? '') === 'tim-p2sp' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Tim P2SP
                </a>
                <a href="<?= base_url('perencana/time-schedule') ?>" class="nav-link <?= ($activeMenu ?? '') === 'time-schedule' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-week"></i> Time Schedule Awal
                </a>
                <a href="<?= base_url('perencana/monitoring-progres') ?>" class="nav-link <?= ($activeMenu ?? '') === 'monitoring-progres' ? 'active' : '' ?>">
                    <i class="bi bi-clipboard2-data"></i> Monitoring Progres
                </a>
            <?php endif; ?>
            <?php if (session()->get('role') === 'admin'): ?>
                <a href="<?= base_url('tim-p2sp') ?>" class="nav-link <?= ($activeMenu ?? '') === 'tim-p2sp' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Tim P2SP
                </a>
                <a href="<?= base_url('pelaksanaan/kurva-s') ?>" class="nav-link <?= ($activeMenu ?? '') === 'kurva-s' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up"></i> Kurva S Pelaksanaan
                </a>
                <a href="<?= base_url('validasi/progres') ?>" class="nav-link <?= ($activeMenu ?? '') === 'validasi-progres' ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i> Validasi Progres
                </a>
                <div class="nav-item">
                    <a href="#adminPelaporanSub" class="nav-link <?= str_starts_with($activeMenu ?? '', 'admin-pelaporan') ? 'active' : '' ?>" data-bs-toggle="collapse">
                        <i class="bi bi-file-earmark-check"></i> Validasi Pelaporan
                        <i class="bi bi-chevron-down ms-auto small"></i>
                    </a>
                    <div class="collapse <?= str_starts_with($activeMenu ?? '', 'admin-pelaporan') ? 'show' : '' ?>" id="adminPelaporanSub">
                        <div class="submenu">
                            <a href="<?= base_url('admin/pelaporan/50') ?>" class="nav-link <?= ($activeMenu ?? '') === 'admin-pelaporan-50' ? 'active' : '' ?>">Pelaporan 50%</a>
                            <a href="<?= base_url('admin/pelaporan/100') ?>" class="nav-link <?= ($activeMenu ?? '') === 'admin-pelaporan-100' ? 'active' : '' ?>">Pelaporan 100%</a>
                        </div>
                    </div>
                </div>
                <div class="nav-item">
                    <a href="#adminKeuanganSub" class="nav-link <?= str_starts_with($activeMenu ?? '', 'keuangan-') ? 'active' : '' ?>" data-bs-toggle="collapse">
                        <i class="bi bi-cash-stack"></i> Keuangan
                        <i class="bi bi-chevron-down ms-auto small"></i>
                    </a>
                    <div class="collapse <?= str_starts_with($activeMenu ?? '', 'keuangan-') ? 'show' : '' ?>" id="adminKeuanganSub">
                        <div class="submenu">
                            <div class="nav-item">
                                <a href="#adminBukuSub" class="nav-link <?= in_array($activeMenu ?? '', ['keuangan-buku-bank', 'keuangan-buku-kas-tunai', 'keuangan-buku-kas-umum'], true) ? 'active' : '' ?>" data-bs-toggle="collapse">
                                    Keuangan
                                    <i class="bi bi-chevron-down ms-auto small"></i>
                                </a>
                                <div class="collapse <?= in_array($activeMenu ?? '', ['keuangan-buku-bank', 'keuangan-buku-kas-tunai', 'keuangan-buku-kas-umum'], true) ? 'show' : '' ?>" id="adminBukuSub">
                                    <div class="submenu">
                                        <a href="<?= base_url('admin/keuangan/buku-bank') ?>" class="nav-link <?= ($activeMenu ?? '') === 'keuangan-buku-bank' ? 'active' : '' ?>">Buku Bank</a>
                                        <a href="<?= base_url('admin/keuangan/buku-kas-tunai') ?>" class="nav-link <?= ($activeMenu ?? '') === 'keuangan-buku-kas-tunai' ? 'active' : '' ?>">Buku Kas Tunai</a>
                                        <a href="<?= base_url('admin/keuangan/buku-kas-umum') ?>" class="nav-link <?= ($activeMenu ?? '') === 'keuangan-buku-kas-umum' ? 'active' : '' ?>">Buku Kas Umum</a>
                                    </div>
                                </div>
                            </div>
                            <a href="<?= base_url('admin/keuangan/bahan-bangunan') ?>" class="nav-link <?= ($activeMenu ?? '') === 'keuangan-bahan-bangunan' ? 'active' : '' ?>">Bahan Bangunan</a>
                            <a href="<?= base_url('admin/keuangan/ongkos-tukang') ?>" class="nav-link <?= ($activeMenu ?? '') === 'keuangan-ongkos-tukang' ? 'active' : '' ?>">Ongkos Tukang</a>
                        </div>
                    </div>
                </div>
                <a href="<?= base_url('admin/verifikasi-time-schedule') ?>" class="nav-link <?= ($activeMenu ?? '') === 'validasi-schedule' ? 'active' : '' ?>">
                    <i class="bi bi-calendar2-check"></i> Verifikasi Jadwal
                </a>
                <a href="<?= base_url('admin/sekolah') ?>" class="nav-link <?= ($activeMenu ?? '') === 'admin-sekolah' ? 'active' : '' ?>">
                    <i class="bi bi-buildings"></i> Sekolah
                </a>
            <?php endif; ?>
            <?php if (session()->get('role') === 'pengawas'): ?>
            <div class="nav-item">
                <a href="#pelaksanaanSub" class="nav-link <?= in_array($activeMenu ?? '', ['progres','kurva-s','adendum']) ? 'active' : '' ?>" data-bs-toggle="collapse">
                    <i class="bi bi-clipboard-data"></i> Pelaksanaan
                    <i class="bi bi-chevron-down ms-auto small"></i>
                </a>
                <div class="collapse <?= in_array($activeMenu ?? '', ['progres','kurva-s','adendum']) ? 'show' : '' ?>" id="pelaksanaanSub">
                    <div class="submenu">
                        <a href="<?= base_url('pelaksanaan/progres') ?>" class="nav-link <?= ($activeMenu ?? '') === 'progres' ? 'active' : '' ?>">Progres Pelaksanaan</a>
                        <a href="<?= base_url('pelaksanaan/kurva-s') ?>" class="nav-link <?= ($activeMenu ?? '') === 'kurva-s' ? 'active' : '' ?>">Kurva S</a>
                        <a href="<?= base_url('adendum') ?>" class="nav-link <?= ($activeMenu ?? '') === 'adendum' ? 'active' : '' ?>">Adendum</a>
                    </div>
                </div>
            </div>
            <div class="nav-item">
                <a href="#pelaporanSub" class="nav-link <?= str_starts_with($activeMenu ?? '', 'pelaporan') ? 'active' : '' ?>" data-bs-toggle="collapse">
                    <i class="bi bi-file-earmark-text"></i> Pelaporan
                    <i class="bi bi-chevron-down ms-auto small"></i>
                </a>
                <div class="collapse <?= str_starts_with($activeMenu ?? '', 'pelaporan') ? 'show' : '' ?>" id="pelaporanSub">
                    <div class="submenu">
                        <a href="<?= base_url('pelaporan/50') ?>" class="nav-link <?= ($activeMenu ?? '') === 'pelaporan-50' ? 'active' : '' ?>">Pelaporan 50%</a>
                        <a href="<?= base_url('pelaporan/100') ?>" class="nav-link <?= ($activeMenu ?? '') === 'pelaporan-100' ? 'active' : '' ?>">Pelaporan 100%</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <a href="<?= base_url('profil') ?>" class="nav-link <?= ($activeMenu ?? '') === 'profil' ? 'active' : '' ?>">
                <i class="bi bi-person"></i> Profil Pengguna
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div style="width:28px;height:28px;background:rgba(255,255,255,.1);border-radius:7px;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-building" style="font-size:.85rem;color:rgba(255,255,255,.7)"></i>
                </div>
                <div>
                    <div class="sf-brand">REVIT SMP</div>
                    <div>Sistem Informasi Bantuan<br>Revitalisasi Sekolah</div>
                </div>
            </div>
            <div class="mt-2 opacity-75">v1.0.0</div>
        </div>
    </aside>
    <button class="sidebar-backdrop" id="sidebarBackdrop" type="button" aria-label="Tutup menu"></button>

    <!-- Main -->
    <div class="main-wrapper">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light d-lg-none" id="sidebarToggle"><i class="bi bi-list"></i></button>
                <span class="topbar-title">
                    <?php
                    $roleLabel = match (session()->get('role')) {
                        'pengawas'  => 'Dashboard Pengawas',
                        'admin'     => 'Dashboard Admin',
                        'perencana' => 'Dashboard Perencana',
                        default     => esc($title ?? 'Dashboard'),
                    };
                    echo $roleLabel;
                    ?>
                </span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center gap-2 text-decoration-none text-dark" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-semibold" style="width:36px;height:36px;font-size:.8rem;">
                            <?php
                            $nama = session()->get('nama_lengkap') ?? 'User';
                            $parts = preg_split('/\s+/', trim($nama));
                            $initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? ($parts[0] ?? 'U'), 0, 1));
                            echo esc($initials);
                            ?>
                        </div>
                        <div class="d-none d-md-block text-start lh-sm">
                            <div class="fw-semibold" style="font-size:.85rem"><?= esc(session()->get('nama_lengkap') ?? 'Pengguna') ?></div>
                            <div class="text-muted" style="font-size:.72rem"><?= esc(ucfirst((string) (session()->get('role') ?? ''))) ?></div>
                        </div>
                        <i class="bi bi-chevron-down text-muted small d-none d-md-inline"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= base_url('profil') ?>"><i class="bi bi-person me-2"></i>Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content-area">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>

        <footer class="app-footer">
            Hak Cipta © 2026 I Kadek Kariasa / Made Gapur &nbsp;|&nbsp; Versi 1.1.0
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        const setSidebarOpen = (isOpen) => {
            sidebar.classList.toggle('show', isOpen);
            sidebarBackdrop.classList.toggle('show', isOpen);
            document.body.classList.toggle('sidebar-open', isOpen);
        };

        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            setSidebarOpen(!sidebar.classList.contains('show'));
        });
        sidebarBackdrop.addEventListener('click', () => setSidebarOpen(false));
        sidebar.querySelectorAll('.nav-link:not([data-bs-toggle="collapse"])').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.matchMedia('(max-width: 991.98px)').matches) setSidebarOpen(false);
            });
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') setSidebarOpen(false);
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
