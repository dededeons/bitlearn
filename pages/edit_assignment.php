<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$teacher_id = $_SESSION['user_id'];

// Get assignment and verify ownership
$assign_query = $conn->query("
    SELECT a.*, c.teacher_id 
    FROM assignments a 
    JOIN courses c ON a.course_id = c.id 
    WHERE a.id = $assignment_id AND c.teacher_id = $teacher_id
");

if (!$assign_query || $assign_query->num_rows === 0) {
    header("Location: manage_courses.php"); exit;
}
$assignment = $assign_query->fetch_assoc();
$course_id = $assignment['course_id'];

// Get all existing assignments in this course to populate prerequisite dropdown (excluding itself)
$all_course_assignments = [];
$c_query = $conn->query("SELECT id, title FROM assignments WHERE course_id = $course_id AND id != $assignment_id ORDER BY id ASC");
if($c_query) {
    while($a = $c_query->fetch_assoc()) $all_course_assignments[] = $a;
}

$page_title = 'Edit Penugasan';
require_once '../components/header.php';
?>

<div class="container main-content" style="padding-top:2rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
        <h2><i class="uil uil-pen"></i> Perbarui Agenda Tugas</h2>
        <a href="course_view.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">
            <i class="uil uil-arrow-left"></i> Batal / Kembali
        </a>
    </div>

    <div class="glass-card" style="max-width:800px; margin:0 auto; border-color:var(--warning);">
        <form action="../actions/update_assignment.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
            
            <div class="form-group">
                <label class="form-label">Judul Tugas / Ulangan</label>
                <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($assignment['title']); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Petunjuk & Deskripsi Tugas</label>
                <textarea name="description" class="form-control" rows="6" required><?php echo htmlspecialchars($assignment['description']); ?></textarea>
            </div>

            <div class="form-group" style="background:var(--surface); padding:1rem; border-radius:var(--radius-sm); border:1px solid rgba(255,255,255,0.1);">
                <label class="form-label" style="color:var(--primary);"><i class="uil uil-paperclip"></i> Ganti Lampiran Tersimpan (Opsional)</label>
                <?php if(!empty($assignment['file_path'])): ?>
                    <div style="margin-bottom:0.8rem; font-size:0.9rem; color:var(--secondary);"><i class="uil uil-file-check-alt"></i> File saat ini: <?php echo htmlspecialchars($assignment['file_path']); ?></div>
                <?php endif; ?>
                <input type="file" name="attachment_file" class="form-control" style="background:var(--background);">
                <small style="color:var(--text-muted);">Biarkan kosong jika tidak ingin mengubah lampiran lama.</small>
            </div>

            <div style="background:rgba(245, 158, 11, 0.1); padding:1.5rem; border-radius:var(--radius-sm); margin-bottom:1.5rem; border:1px solid rgba(245, 158, 11, 0.3);">
                <div class="form-group" style="margin:0; margin-bottom:1.5rem;">
                    <label class="form-label">Tenggat Waktu / Due Date</label>
                    <input type="datetime-local" name="due_date" class="form-control" style="background:var(--surface);" required value="<?php echo date('Y-m-d\TH:i', strtotime($assignment['due_date'])); ?>">
                </div>
                
                <div class="form-group" style="margin:0;">
                    <label class="form-label"><i class="uil uil-lock"></i> Kunci Prasyarat Tugas (Opsional)</label>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.8rem;">Pilih tugas terdahulu yang WAJIB diselesaikan/dikumpulkan sebelum siswa bisa mengerjakan tugas ini.</p>
                    <select name="is_prerequisite_of" class="form-control" style="background:var(--surface);">
                        <option value="">-- Bebas Akses (Tidak Ada Prasyarat) --</option>
                        <?php foreach($all_course_assignments as $p_asn): 
                            $sel = ($assignment['is_prerequisite_of'] == $p_asn['id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $p_asn['id']; ?>" <?php echo $sel; ?>>Wajib Mengumpulkan: <?php echo htmlspecialchars($p_asn['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top:2rem;">
                <label class="checkbox-container" style="display:flex; align-items:center; gap:10px; cursor:pointer; color:var(--text-main);">
                    <input type="checkbox" name="is_published" value="1" <?php echo ($assignment['is_published']) ? 'checked' : ''; ?> style="width:20px; height:20px;">
                    <strong>Publikasikan Tugas ke Siswa (Aktifkan)</strong>
                </label>
                <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.4rem;">Jika tidak dicentang, tugas akan disimpan sebagai Draf dan tidak terlihat oleh siswa.</p>
            </div>

            <div style="margin-top:2rem;">
                <button type="submit" class="btn btn-primary btn-block" style="padding:1rem; background:var(--warning); border:none;"><i class="uil uil-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../components/footer.php'; ?>
