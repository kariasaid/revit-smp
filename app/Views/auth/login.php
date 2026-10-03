<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Pengarsipan Dokumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f0fe 0%, #f8fafc 50%, #e0e7ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        /* Decorative buildings */
        .deco-left, .deco-right {
            position: absolute;
            bottom: 0;
            opacity: .35;
            pointer-events: none;
        }
        .deco-left { left: 5%; width: 220px; }
        .deco-right { right: 5%; width: 200px; }
        .login-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(29, 82, 150, .12);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            z-index: 10;
        }
        .logo-area { text-align: center; margin-bottom: 1.75rem; }
        .logo-area .logo-icon {
            width: 56px; height: 56px;
            background: #1d5296;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: .75rem;
        }
        .logo-area h1 { font-size: 1.15rem; font-weight: 700; color: #1d5296; margin: 0; }
        .logo-area p { font-size: .85rem; color: #6b7280; margin: .25rem 0 0; }
        .form-control {
            border-radius: .5rem;
            padding: .7rem 1rem;
            border: 1px solid #e5e7eb;
        }
        .form-control:focus {
            border-color: #1d5296;
            box-shadow: 0 0 0 .2rem rgba(29, 82, 150, .15);
        }
        .btn-login {
            background: #1d5296;
            border: none;
            border-radius: .5rem;
            padding: .75rem;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover { background: #153d70; }
        .form-check-label { font-size: .875rem; color: #6b7280; }
        .forgot-link { font-size: .875rem; color: #1d5296; text-decoration: none; }
        .forgot-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <!-- Decorative SVG buildings -->
    <svg class="deco-left" viewBox="0 0 200 180" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="30" y="40" width="100" height="120" rx="4" stroke="#1d5296" stroke-width="2" fill="#e8f0fe"/>
        <rect x="45" y="55" width="25" height="20" rx="2" stroke="#1d5296" stroke-width="1.5"/>
        <rect x="90" y="55" width="25" height="20" rx="2" stroke="#1d5296" stroke-width="1.5"/>
        <rect x="45" y="90" width="25" height="20" rx="2" stroke="#1d5296" stroke-width="1.5"/>
        <rect x="90" y="90" width="25" height="20" rx="2" stroke="#1d5296" stroke-width="1.5"/>
        <path d="M70 160 L70 130 L90 130 L90 160" stroke="#1d5296" stroke-width="2"/>
        <path d="M20 40 L80 10 L140 40" stroke="#f59e0b" stroke-width="2"/>
        <line x1="10" y1="160" x2="160" y2="160" stroke="#f59e0b" stroke-width="2"/>
    </svg>
    <svg class="deco-right" viewBox="0 0 180 160" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="40" y="50" width="90" height="90" rx="4" stroke="#1d5296" stroke-width="2" fill="#e8f0fe"/>
        <rect x="55" y="65" width="20" height="18" rx="2" stroke="#1d5296" stroke-width="1.5"/>
        <rect x="95" y="65" width="20" height="18" rx="2" stroke="#1d5296" stroke-width="1.5"/>
        <path d="M70 140 L70 115 L100 115 L100 140" stroke="#1d5296" stroke-width="2"/>
        <path d="M30 50 L85 15 L140 50" stroke="#f59e0b" stroke-width="2"/>
        <rect x="10" y="100" width="30" height="40" rx="2" stroke="#f59e0b" stroke-width="1.5" fill="none"/>
    </svg>

    <div class="login-card">
        <div class="logo-area">
            <div class="logo-icon">
                <i class="bi bi-building text-white fs-4"></i>
            </div>
            <h1>CV TAKSU DEWATA</h1>
            <p class="fw-semibold text-dark mt-2 mb-0" style="font-size:1rem;">Aplikasi Pengarsipan Dokumen</p>
            <p class="text-primary fw-bold" style="font-size:1.05rem;">Revitalisasi Sekolah</p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('login') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label small fw-medium text-secondary">Email/Username</label>
                <input type="text" name="login" class="form-control" placeholder="5103061007760005" required value="<?= old('login') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-medium text-secondary">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePass()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="#" class="forgot-link">Lupa Password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-login">
                Masuk Portal Aplikasi <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>
    </div>

    <script>
        function togglePass() {
            const p = document.getElementById('password');
            const i = document.getElementById('eyeIcon');
            if (p.type === 'password') { p.type = 'text'; i.classList.replace('bi-eye', 'bi-eye-slash'); }
            else { p.type = 'password'; i.classList.replace('bi-eye-slash', 'bi-eye'); }
        }
    </script>
</body>
</html>
