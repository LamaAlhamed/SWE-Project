<?php
session_start();
require_once 'AtharDB.php';

// يجب أن تكون مسجلة دخول
if (!isset($_SESSION['studentID'])) {
    header("Location: login.php");
    exit();
}

$studentID = $_SESSION['studentID'];

// جلب experienceID من URL
$experienceID = isset($_GET['experienceID']) ? intval($_GET['experienceID']) : 0;

if ($experienceID <= 0) {
    header("Location: profile.php");
    exit();
}

// جلب التجربة مع التأكد أنها تخص الطالبة الحالية
$stmt = mysqli_prepare($connection,
    "SELECT e.experienceID, e.experienceContent, e.studyNote, e.courseID,
            c.courseCode, c.courseName, c.level
     FROM experience e
     JOIN course c ON e.courseID = c.courseID
     WHERE e.experienceID = ? AND e.studentID = ?");
mysqli_stmt_bind_param($stmt, "ii", $experienceID, $studentID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$experience = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// إذا التجربة مو موجودة أو مو تبع الطالبة
if (!$experience) {
    header("Location: profile.php");
    exit();
}

$errors = [];
$success = false;

// معالجة الفورم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $experienceContent = trim($_POST['experienceContent'] ?? '');
    $studyNote = $experience['studyNote']; // نحتفظ بالملف القديم افتراضياً
    $removeFile = isset($_POST['removeFile']) && $_POST['removeFile'] === '1';

    // التحقق من الوصف
    if (empty($experienceContent)) {
        $errors[] = 'وصف التجربة مطلوب';
    } elseif (mb_strlen($experienceContent) < 20) {
        $errors[] = 'وصف التجربة يجب أن يكون 20 حرف على الأقل';
    }

    // حذف الملف القديم إذا طلبت
    if ($removeFile) {
        if (!empty($experience['studyNote']) && file_exists($experience['studyNote'])) {
            unlink($experience['studyNote']);
        }
        $studyNote = '';
    }

    // رفع ملف جديد إذا موجود
    if (isset($_FILES['studyNote']) && $_FILES['studyNote']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['application/pdf', 'application/msword',
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                         'application/vnd.ms-powerpoint',
                         'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
        $fileType = $_FILES['studyNote']['type'];
        $fileSize = $_FILES['studyNote']['size'];
        $maxSize  = 10 * 1024 * 1024;

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'نوع الملف غير مدعوم. الأنواع المقبولة: PDF, DOC, DOCX, PPT, PPTX';
        } elseif ($fileSize > $maxSize) {
            $errors[] = 'حجم الملف يتجاوز 10MB';
        } else {
            $uploadDir = 'uploads/study_notes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['studyNote']['name'], PATHINFO_EXTENSION);
            $fileName = 'note_' . $studentID . '_' . time() . '.' . $ext;
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['studyNote']['tmp_name'], $uploadPath)) {
                // حذف الملف القديم
                if (!empty($experience['studyNote']) && file_exists($experience['studyNote'])) {
                    unlink($experience['studyNote']);
                }
                $studyNote = $uploadPath;
            } else {
                $errors[] = 'فشل رفع الملف، حاولي مرة أخرى';
            }
        }
    }

    // تحديث في قاعدة البيانات
    if (empty($errors)) {
        $stmt = mysqli_prepare($connection,
            "UPDATE experience SET experienceContent = ?, studyNote = ? 
             WHERE experienceID = ? AND studentID = ?");
        mysqli_stmt_bind_param($stmt, "ssii", $experienceContent, $studyNote, $experienceID, $studentID);

        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            // تحديث البيانات المحلية لعرضها بعد النجاح
            $experience['experienceContent'] = $experienceContent;
            $experience['studyNote'] = $studyNote;
        } else {
            $errors[] = 'حدث خطأ أثناء التحديث، حاولي مرة أخرى';
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>أثر - تعديل تجربة</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --maroon: #5C1F3A; --maroon-light: #7C2D4F; --salmon: #E8876A; --cream: #FFF5F0; --text-dark: #2A0F1A; --text-mid: #6B3A4A; }
body { font-family: 'Tajawal', sans-serif; background: var(--cream); }
nav { background: var(--maroon); padding: 16px 48px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
.logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.logo img { width: 44px; height: 44px; object-fit: contain; border-radius: 8px; }
.nav-link { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; }
.page-header { background: linear-gradient(135deg, var(--maroon), #3A0F25); padding: 40px 48px; color: white; }
.breadcrumb { font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 12px; }
.breadcrumb a { color: rgba(255,255,255,0.6); text-decoration: none; }
.page-header h1 { font-size: 28px; font-weight: 800; }
.content { max-width: 700px; margin: 48px auto; padding: 0 24px; }
.form-card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 4px 20px rgba(92,31,58,0.08); }
.form-group { margin-bottom: 24px; }
label { display: block; font-size: 15px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
.required { color: var(--salmon); margin-right: 4px; }
.optional { font-size: 12px; color: var(--text-mid); font-weight: 400; margin-right: 8px; }
textarea { width: 100%; padding: 14px 16px; border: 1.5px solid #E8D6DC; border-radius: 12px; font-size: 15px; font-family: 'Tajawal', sans-serif; color: var(--text-dark); background: #FFF5F0; resize: vertical; min-height: 160px; outline: none; transition: border .2s; line-height: 1.7; }
textarea:focus { border-color: var(--maroon); background: white; }
.char-count { font-size: 12px; color: var(--text-mid); text-align: left; margin-top: 6px; }
.file-upload { border: 2px dashed #E8D6DC; border-radius: 12px; padding: 32px; text-align: center; background: #FFF5F0; cursor: pointer; transition: border .2s; }
.file-upload:hover { border-color: var(--maroon); }
.file-upload-icon { font-size: 36px; margin-bottom: 12px; }
.file-upload p { font-size: 14px; color: var(--text-mid); margin-bottom: 8px; }
.file-upload span { font-size: 12px; color: var(--text-mid); opacity: 0.7; }
input[type="file"] { display: none; }
.file-name { font-size: 13px; color: var(--maroon); margin-top: 10px; font-weight: 600; display: none; }
.existing-file { background: #F0F7F0; border: 1px solid #BEE0BE; border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; }
.existing-file span { font-size: 13px; color: #27AE60; font-weight: 600; }
.btn-remove-file { background: none; border: none; color: #C0392B; font-size: 13px; cursor: pointer; font-family: 'Tajawal', sans-serif; font-weight: 600; }
.form-actions { display: flex; gap: 12px; margin-top: 8px; }
.btn-submit { flex: 1; padding: 14px; background: linear-gradient(135deg, var(--maroon), var(--maroon-light)); color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 700; font-family: 'Tajawal', sans-serif; cursor: pointer; transition: transform .2s; }
.btn-submit:hover { transform: translateY(-2px); }
.btn-cancel { padding: 14px 24px; background: var(--cream); color: var(--text-mid); border: 1.5px solid #E8D6DC; border-radius: 12px; font-size: 15px; font-weight: 600; font-family: 'Tajawal', sans-serif; cursor: pointer; text-decoration: none; display: flex; align-items: center; }
.alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; }
.alert-error { background: #FDE8E8; color: #C0392B; border: 1px solid #F5BCBC; }
.alert-success { background: #E8F5E8; color: #27AE60; border: 1px solid #BEE0BE; }
.alert ul { margin-right: 20px; }
.alert ul li { margin-bottom: 4px; }
.edit-badge { background: rgba(255,255,255,0.15); display: inline-block; padding: 4px 14px; border-radius: 50px; font-size: 12px; margin-bottom: 10px; }
footer { background: #2A0F1A; padding: 24px; text-align: center; color: rgba(255,255,255,0.4); font-size: 12px; margin-top: 60px; }
footer strong { color: var(--salmon); }
</style>
</head>
<body>
<nav>
  <a href="index.php" class="logo">
    <img src="images/logo.PNG" alt="أثر">
  </a>
  <a href="profile.php" class="nav-link">← العودة للملف الشخصي</a>
</nav>

<div class="page-header">
  <div class="breadcrumb">
    <a href="index.php">الرئيسية</a> ← 
    <a href="profile.php">ملفي الشخصي</a> ← 
    <span>تعديل التجربة</span>
  </div>
  <div class="edit-badge">✏️ وضع التعديل</div>
  <h1>تعديل التجربة الأكاديمية</h1>
  <p style="color:rgba(255,255,255,0.7);margin-top:6px;font-size:14px;">
    <?= htmlspecialchars($experience['courseCode']) ?> — <?= htmlspecialchars($experience['courseName']) ?>
  </p>
</div>

<div class="content">

  <?php if ($success): ?>
    <div class="alert alert-success">
      ✅ تم تحديث تجربتك بنجاح!
      <div style="margin-top:10px;display:flex;gap:16px;">
        <a href="profile.php" style="color:#27AE60;font-weight:700;">العودة للملف الشخصي ←</a>
        <a href="course-details.php?id=<?= $experience['courseID'] ?>" style="color:#27AE60;font-weight:700;">عرض المقرر ←</a>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (!$success): ?>
  <div class="form-card">
    <form method="POST" enctype="multipart/form-data">

      <div class="form-group">
        <label>وصف تجربتك <span class="required">*</span></label>
        <textarea name="experienceContent" id="expContent"
                  placeholder="شاركي تجربتك مع هذا المقرر..."
                  maxlength="2000"><?= htmlspecialchars($experience['experienceContent']) ?></textarea>
        <div class="char-count"><span id="charCount">0</span> / 2000 حرف</div>
      </div>

      <div class="form-group">
        <label>الملف المرفق <span class="optional">(اختياري)</span></label>

        <?php if (!empty($experience['studyNote'])): ?>
          <div class="existing-file" id="existingFile">
            <span>📄 يوجد ملف مرفق حالياً</span>
            <button type="button" class="btn-remove-file" onclick="removeExistingFile()">✕ حذف الملف</button>
          </div>
          <input type="hidden" name="removeFile" id="removeFile" value="0">
        <?php endif; ?>

        <label class="file-upload" for="file-input">
          <div class="file-upload-icon">📎</div>
          <p><?= !empty($experience['studyNote']) ? 'أو ارفعي ملفاً جديداً ليحل محل القديم' : 'اضغطي لرفع ملف أو اسحبيه هنا' ?></p>
          <span>PDF, DOC, PPT — حجم أقصى 10MB</span>
        </label>
        <input type="file" id="file-input" name="studyNote" accept=".pdf,.doc,.docx,.ppt,.pptx">
        <div class="file-name" id="fileName"></div>
      </div>

      <div class="form-actions">
        <a href="profile.php" class="btn-cancel">إلغاء</a>
        <button type="submit" class="btn-submit">حفظ التعديلات</button>
      </div>

    </form>
  </div>

  <?php else: ?>
  <!-- عرض التجربة المحدثة بعد النجاح -->
  <div class="form-card">
    <div style="margin-bottom:16px;">
      <span style="font-size:12px;font-weight:700;color:var(--salmon);letter-spacing:1px;">
        <?= htmlspecialchars($experience['courseCode']) ?> — <?= htmlspecialchars($experience['courseName']) ?>
      </span>
    </div>
    <p style="font-size:15px;color:var(--text-mid);line-height:1.8;">
      <?= nl2br(htmlspecialchars($experience['experienceContent'])) ?>
    </p>
    <?php if (!empty($experience['studyNote'])): ?>
      <div style="margin-top:16px;padding:10px 14px;background:#F0F7F0;border-radius:8px;font-size:13px;color:#27AE60;font-weight:600;">
        📄 يوجد ملف مرفق
      </div>
    <?php endif; ?>
    <div style="margin-top:24px;display:flex;gap:12px;">
      <a href="profile.php" class="btn-cancel" style="text-decoration:none;">العودة للملف الشخصي</a>
      <a href="course-details.php?id=<?= $experience['courseID'] ?>" class="btn-submit" style="text-decoration:none;text-align:center;display:flex;align-items:center;justify-content:center;">عرض المقرر</a>
    </div>
  </div>
  <?php endif; ?>

</div>

<footer><p>جميع الحقوق محفوظة &copy; 2026 — <strong>منصة أثر</strong></p></footer>

<script>
// عداد الأحرف
const textarea = document.getElementById('expContent');
const charCount = document.getElementById('charCount');
if (textarea) {
    charCount.textContent = textarea.value.length;
    textarea.addEventListener('input', () => {
        charCount.textContent = textarea.value.length;
    });
}

// اسم الملف الجديد
const fileInput = document.getElementById('file-input');
const fileNameDiv = document.getElementById('fileName');
if (fileInput) {
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            fileNameDiv.textContent = '📄 ' + fileInput.files[0].name;
            fileNameDiv.style.display = 'block';
        }
    });
}

// حذف الملف الموجود
function removeExistingFile() {
    const existingFile = document.getElementById('existingFile');
    const removeInput = document.getElementById('removeFile');
    if (existingFile) existingFile.style.display = 'none';
    if (removeInput) removeInput.value = '1';
}
</script>
</body>
</html>