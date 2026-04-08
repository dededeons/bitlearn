<?php
require_once 'core/config.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    $role = trim(strtolower((string) $_SESSION['user_role']));
    if ($role === 'teacher') {
        header("Location: pages/teacher_dashboard.php");
    } else {
        header("Location: pages/student_dashboard.php");
    }
    exit;
}

$page_title = 'Masuk ke Portal';
$hide_navbar = true; // hide navbar for the auth page to look cleaner
require_once 'components/header.php';
?>

<div class="auth-wrapper">
    <div class="glass-card auth-card">
        <div class="auth-header">
            <div style="display:flex; justify-content:center; margin-bottom:1.5rem; padding: 1rem;">
                <img src="<?php echo BASE_URL; ?>/assets/logo.png" alt="BitLearn Logo"
                    style="height:auto; width:220px; max-width:100%; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.5)); transform: scale(1.1);">
            </div>
            <h1>Selamat Datang</h1>
            <p>Silakan masuk menggunakan identitas Anda</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="uil uil-exclamation-circle"></i>
                <?php
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="uil uil-check-circle"></i>
                <?php
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/actions/login.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="username">Username (NIP / NISN)</label>
                <div style="position:relative;">
                    <i class="uil uil-user"
                        style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:1.2rem;"></i>
                    <input type="text" id="username" name="email" class="form-control" style="padding-left:3rem;"
                        placeholder="Masukkan NIP atau NISN Anda" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi</label>
                <div style="position:relative;">
                    <i class="uil uil-lock"
                        style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:1.2rem;"></i>
                    <input type="password" id="password" name="password" class="form-control" style="padding-left:3rem;"
                        placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top:1rem;">
                Masuk <i class="uil uil-arrow-right"></i>
            </button>
        </form>

        <div style="margin-top: 2rem; text-align:center; color:var(--text-muted); font-size:0.9rem;">
            <p>Khusus akses Siswa:<br>Silakan tanyakan detail kredensial Anda kepada Guru Mata Pelajaran.</p>
        </div>
    </div>
</div>

<?php require_once 'components/footer.php'; ?>