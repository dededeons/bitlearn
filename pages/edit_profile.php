<?php
require_once '../core/config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$res = $conn->query($query);
$user = $res->fetch_assoc();

$page_title = "Pengaturan Profil Saya";
require_once '../components/header.php';
?>

<div class="container main-content">
    <div style="max-width:600px; margin:0 auto;">
        <h2 style="margin-bottom:2rem;"><i class="uil uil-user-circle"></i> Pengaturan Profil Saya</h2>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="uil uil-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="uil uil-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="glass-card">
            <form action="../actions/update_profile.php" method="POST" enctype="multipart/form-data">
                
                <div style="text-align:center; margin-bottom:2rem;">
                    <?php if(!empty($user['profile_pic'])): ?>
                        <img src="<?php echo htmlspecialchars(BASE_URL . '/uploads/' . $user['profile_pic']); ?>" alt="Profile" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid var(--primary); margin-bottom:1rem; box-shadow:0 10px 25px rgba(0,0,0,0.5);">
                    <?php else: ?>
                        <div style="width:120px; height:120px; border-radius:50%; background:var(--surface); display:inline-flex; align-items:center; justify-content:center; border:3px solid var(--primary); font-size:4rem; color:var(--text-muted); margin-bottom:1rem; box-shadow:0 10px 25px rgba(0,0,0,0.5);">
                            <i class="uil uil-user"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group" style="text-align:left; margin-top:1rem;">
                        <label class="form-label" for="profile_pic">Unggah Pasfoto Baru (Opsional)</label>
                        <input type="file" id="profile_pic" name="profile_pic" class="form-control" accept="image/png, image/jpeg, image/jpg" style="padding:0.75rem;">
                        <small style="color:var(--text-muted); display:block; margin-top:0.5rem;"><i class="uil uil-info-circle"></i> Maksimal 2MB. Format: JPG atau PNG persegi (disarankan 1:1).</small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="username">Username (NIP / NISN) <span style="color:var(--danger);">*Gunakan untuk Login</span></label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <hr style="border:0; border-top:1px solid rgba(255,255,255,0.1); margin:2.5rem 0 1.5rem 0;">
                <h4 style="margin-bottom:1.5rem; color:var(--text-main);"><i class="uil uil-lock"></i> Pengaturan Keamanan</h4>

                <div class="form-group">
                    <label class="form-label" for="new_password">Sandi Baru</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="••••••••">
                    <small style="color:var(--text-muted);">Biarkan kosong sepenuhnya bila tidak ingin mengganti kata sandi saat ini.</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Konfirmasi Sandi Baru</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••">
                </div>

                <div style="margin-top:2.5rem; display:flex; gap:1rem;">
                    <a href="javascript:history.back()" class="btn btn-secondary" style="flex:1;">Tutup Batal</a>
                    <button type="submit" class="btn btn-primary" style="flex:1;"><i class="uil uil-save"></i> Simpan Profil</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../components/footer.php'; ?>
