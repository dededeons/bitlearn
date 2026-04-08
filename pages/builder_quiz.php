<?php
require_once '../core/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') { header("Location: ../index.php"); exit; }

$lesson_id = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : 0;
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Verify teacher owns course
$teacher_id = $_SESSION['user_id'];
$chk = $conn->query("SELECT courses.id FROM lessons JOIN modules ON lessons.module_id = modules.id JOIN courses ON modules.course_id = courses.id WHERE lessons.id = $lesson_id AND courses.teacher_id = $teacher_id");
if (!$chk || $chk->num_rows === 0) {
    header("Location: teacher_dashboard.php"); exit;
}

$lesson = $conn->query("SELECT title FROM lessons WHERE id = $lesson_id")->fetch_assoc();
$questions = $conn->query("SELECT * FROM quiz_questions WHERE lesson_id = $lesson_id ORDER BY id ASC");

$page_title = 'Perakit Bank Soal';
require_once '../components/header.php';
?>

<div class="container main-content" style="padding-top:2rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
        <div>
            <h2><i class="uil uil-processor"></i> Perakit Evaluasi Ujian</h2>
            <p style="color:var(--text-muted);">Materi Induk: <b style="color:var(--text-main);"><?php echo htmlspecialchars($lesson['title']); ?></b></p>
        </div>
        <a href="course_view.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">
            <i class="uil uil-arrow-left"></i> Selesai & Kembali ke Materi
        </a>
    </div>

    <?php if(isset($_SESSION['success'])): ?><div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>

    <div class="grid grid-cols-2">
        <!-- Render Existing DB Questions -->
        <div style="padding-right:1rem;">
            <h3 style="margin-bottom:1.5rem; color:var(--text-main);">Daftar Soal Tes</h3>
            
            <?php if ($questions && $questions->num_rows > 0): ?>
                <?php 
                $num = 1;
                while($q = $questions->fetch_assoc()): 
                    $qid = $q['id'];
                    $opts = $conn->query("SELECT * FROM quiz_options WHERE question_id = $qid ORDER BY id ASC");
                ?>
                    <div class="glass-card" style="margin-bottom:1.5rem; padding:1.5rem;">
                        <h4 style="margin-bottom:1rem; line-height:1.5;"><b><?php echo $num; ?>.</b> <?php echo nl2br(htmlspecialchars($q['question_text'])); ?></h4>
                        <?php if($opts && $opts->num_rows > 0): ?>
                            <ul style="list-style:none; margin:0; padding:0;">
                                <?php 
                                $letter = 'A';
                                while($opt = $opts->fetch_assoc()): 
                                ?>
                                    <li style="padding:0.6rem; margin-bottom:0.5rem; background:<?php echo $opt['is_correct'] ? 'rgba(16, 185, 129, 0.2)' : 'rgba(0,0,0,0.2)'; ?>; border-radius:var(--radius-sm); border:1px solid <?php echo $opt['is_correct'] ? 'var(--secondary)' : 'var(--border)'; ?>; color:<?php echo $opt['is_correct'] ? 'var(--secondary)' : 'var(--text-muted)'; ?>;">
                                        <b><?php echo $letter; ?>.</b> <?php echo htmlspecialchars($opt['option_text']); ?>
                                        <?php if($opt['is_correct']) echo '<i class="uil uil-check-circle" style="float:right;"></i>'; ?>
                                    </li>
                                <?php $letter++; endwhile; ?>
                            </ul>
                        <?php endif; ?>
                        
                        <!-- Simple Delete Button -->
                        <form action="../actions/delete_question.php" method="POST" style="margin-top:1rem; text-align:right;">
                            <input type="hidden" name="question_id" value="<?php echo $qid; ?>">
                            <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" style="font-size:0.8rem; padding:0.4rem 0.8rem;"><i class="uil uil-trash"></i> Hapus Soal</button>
                        </form>
                    </div>
                <?php $num++; endwhile; ?>
            <?php else: ?>
                <div style="background:rgba(245, 158, 11, 0.1); border:1px dashed rgba(245, 158, 11, 0.3); padding:2rem; border-radius:var(--radius-sm); text-align:center;">
                    <i class="uil uil-file-question" style="font-size:3rem; color:var(--warning);"></i>
                    <p style="color:var(--warning); margin-top:1rem;">Belum ada butir soal Ujian. Buat sekarang pada panel di sebelah kanan.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add Question Form -->
        <div>
            <div class="glass-card" style="position:sticky; top:100px;">
                <h3 style="border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:1rem; margin-bottom:1rem;"><i class="uil uil-plus-circle"></i> Tambah Butir Soal Baru</h3>
                <form action="../actions/save_quiz_question.php" method="POST">
                    <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Teks Pertanyaan Baru</label>
                        <textarea name="question_text" class="form-control" rows="3" placeholder="Sebutkan unsur-unsur pembentuk senyawa air?" required></textarea>
                    </div>

                    <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1rem;"><i class="uil uil-lightbulb-alt"></i> Ketik 4 Pilihan Jawaban, dan <b>pilih tombol bundar</b> untuk menandai mana yang benar.</p>

                    <div style="background:var(--background); padding:1rem; border-radius:var(--radius-sm); border:1px solid var(--border);">
                        <!-- Opt A -->
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.8rem;">
                            <input type="radio" name="correct_option" value="0" required style="width:20px; height:20px; cursor:pointer;" checked>
                            <input type="text" name="options[]" class="form-control" placeholder="Pilihan A" required>
                        </div>
                        <!-- Opt B -->
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.8rem;">
                            <input type="radio" name="correct_option" value="1" style="width:20px; height:20px; cursor:pointer;">
                            <input type="text" name="options[]" class="form-control" placeholder="Pilihan B" required>
                        </div>
                        <!-- Opt C -->
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.8rem;">
                            <input type="radio" name="correct_option" value="2" style="width:20px; height:20px; cursor:pointer;">
                            <input type="text" name="options[]" class="form-control" placeholder="Pilihan C" required>
                        </div>
                        <!-- Opt D -->
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.8rem;">
                            <input type="radio" name="correct_option" value="3" style="width:20px; height:20px; cursor:pointer;">
                            <input type="text" name="options[]" class="form-control" placeholder="Pilihan D" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="margin-top:1.5rem;"><i class="uil uil-save"></i> Simpan Butir Soal ke Kuis</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/footer.php'; ?>
