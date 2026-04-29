<?php
session_start();
require_once 'AtharDB.php';

// Admin auth check
if (!isset($_SESSION['adminID'])) {
    header('Location: login.php');
    exit;
}

$isEdit   = isset($_GET['edit']) && isset($_GET['id']);
$courseID = $isEdit ? (int)$_GET['id'] : 0;
$course   = null;
$errors   = [];

// Load existing course if editing
if ($isEdit) {
    $stmt = mysqli_prepare($connection, "SELECT * FROM course WHERE courseID = ?");
    mysqli_stmt_bind_param($stmt, 'i', $courseID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $course = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if (!$course) {
        header('Location: Admin.php');
        exit;
    }


    $rRes = mysqli_query($connection,
        "SELECT * FROM resource WHERE courseID = $courseID LIMIT 1");
    $existingResource = $rRes ? mysqli_fetch_assoc($rRes) : null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $courseCode = trim($_POST['courseCode'] ?? '');
    $courseName = trim($_POST['courseName'] ?? '');
    $desc       = trim($_POST['courseDescription'] ?? '');
    $level      = (int)($_POST['level'] ?? 0);
    $track      = trim($_POST['track'] ?? '');
    $resTitle   = trim($_POST['resourceTitle'] ?? '');
    $resLink    = trim($_POST['resourceLink'] ?? '');


    if (!$courseName) $errors[] = 'اسم المقرر مطلوب';
    if (!$desc)       $errors[] = 'وصف المقرر مطلوب';
    if (!$level)      $errors[] = 'المستوى الدراسي مطلوب';
    if (!$track)      $errors[] = 'المسار مطلوب';


    if (!empty($courseCode)) {
        if ($isEdit) {
            $dupStmt = mysqli_prepare($connection,
                "SELECT courseID FROM course WHERE courseCode = ? AND courseID != ?");
            mysqli_stmt_bind_param($dupStmt, 'si', $courseCode, $courseID);
        } else {
            $dupStmt = mysqli_prepare($connection,
                "SELECT courseID FROM course WHERE courseCode = ?");
            mysqli_stmt_bind_param($dupStmt, 's', $courseCode);
        }
        mysqli_stmt_execute($dupStmt);
        mysqli_stmt_store_result($dupStmt);
        if (mysqli_stmt_num_rows($dupStmt) > 0) {
            $errors[] = 'رمز المقرر "' . htmlspecialchars($courseCode) . '" مستخدم مسبقاً، اختار رمزً آخر';
        }
        mysqli_stmt_close($dupStmt);
    }
if (!$isEdit && !$resTitle) {
    $errors[] = 'عنوان المصدر التعليمي مطلوب';
}

    $hasFile = !empty($_FILES['file']['name']);
    $hasLink = !empty($resLink);

    if (!$isEdit) {

        if (!$hasFile && !$hasLink) {
            $errors[] = 'المصدر التعليمي مطلوب — أضيفي رابطاً أو ارفعي ملفاً';
        }
    }


    $uploadedFile = $existingResource['resourceLink'] ?? '';
    if ($hasFile) {
        $allowed = ['pdf','doc','docx','ppt','pptx'];
        $ext     = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'نوع الملف غير مدعوم (.pdf, .doc, .docx, .ppt, .pptx)';
        } else {
            $uploadDir = 'uploads/resources/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $newName   = uniqid('res_') . '.' . $ext;
            $targetPath = $uploadDir . $newName;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                $uploadedFile = $targetPath;
            } else {
                $errors[] = 'فشل رفع الملف، حاولي مرة أخرى';
            }
        }
    } elseif ($hasLink) {
        $uploadedFile = $resLink;
    }

    if (empty($errors)) {
        if ($isEdit) {

            $stmt = mysqli_prepare($connection,
                "UPDATE course SET courseCode=?, courseName=?, courseDescription=?, level=?, track=? WHERE courseID=?");
            mysqli_stmt_bind_param($stmt, 'sssisi', $courseCode, $courseName, $desc, $level, $track, $courseID);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);


            if ($hasFile || $hasLink) {
                $rCheck = mysqli_query($connection,
                    "SELECT resourceID FROM resource WHERE courseID = $courseID LIMIT 1");
                if (mysqli_num_rows($rCheck) > 0) {
                    $rRow  = mysqli_fetch_assoc($rCheck);
                    $stmt2 = mysqli_prepare($connection,
                        "UPDATE resource SET resourceTitle=?, resourceLink=? WHERE resourceID=?");
                    mysqli_stmt_bind_param($stmt2, 'ssi', $resTitle, $uploadedFile, $rRow['resourceID']);
                    mysqli_stmt_execute($stmt2);
                    mysqli_stmt_close($stmt2);
                } else {
                    $stmt2 = mysqli_prepare($connection,
                        "INSERT INTO resource (resourceTitle, resourceLink, courseID) VALUES (?,?,?)");
                    mysqli_stmt_bind_param($stmt2, 'ssi', $resTitle, $uploadedFile, $courseID);
                    mysqli_stmt_execute($stmt2);
                    mysqli_stmt_close($stmt2);
                }
            }

            $_SESSION['newCourseID']   = $courseID;
            $_SESSION['newCourseName'] = $courseName;
            $_SESSION['isEdit']        = true;
            $showPrereqModal = true;

        } else {

            $stmt = mysqli_prepare($connection,
                "INSERT INTO course (courseCode, courseName, courseDescription, level, track) VALUES (?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'sssis', $courseCode, $courseName, $desc, $level, $track);
            mysqli_stmt_execute($stmt);
            $newCourseID = mysqli_insert_id($connection);
            mysqli_stmt_close($stmt);


            if ($newCourseID && $uploadedFile) {
                $stmt2 = mysqli_prepare($connection,
                    "INSERT INTO resource (resourceTitle, resourceLink, courseID) VALUES (?,?,?)");
                mysqli_stmt_bind_param($stmt2, 'ssi', $resTitle, $uploadedFile, $newCourseID);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
            }

            $_SESSION['newCourseID']   = $newCourseID;
            $_SESSION['newCourseName'] = $courseName;
            $showPrereqModal = true;
        }
    }
}

$allCourses    = [];
$allCoursesRes = mysqli_query($connection,
    "SELECT courseID, courseCode, courseName FROM course ORDER BY level, courseCode");
while ($row = mysqli_fetch_assoc($allCoursesRes)) {
    $allCourses[] = $row;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_prereq'])) {
    $targetID  = (int)($_SESSION['newCourseID'] ?? 0);
    $prereqIDs = $_POST['prereqID'] ?? [];
    $wasEdit   = $_SESSION['isEdit'] ?? false;

    if ($targetID > 0 && !empty($prereqIDs)) {
        $stmtDel = mysqli_prepare($connection,
            "DELETE FROM courseprerequisite WHERE courseID = ?");
        mysqli_stmt_bind_param($stmtDel, 'i', $targetID);
        mysqli_stmt_execute($stmtDel);
        mysqli_stmt_close($stmtDel);

        $stmtIns = mysqli_prepare($connection,
            "INSERT INTO courseprerequisite (courseID, prerequisiteCourseID) VALUES (?, ?)");
        foreach ($prereqIDs as $pid) {
            $pid = (int)$pid;
            if ($pid > 0 && $pid !== $targetID) {
                mysqli_stmt_bind_param($stmtIns, 'ii', $targetID, $pid);
                mysqli_stmt_execute($stmtIns);
            }
        }
        mysqli_stmt_close($stmtIns);
    }
    unset($_SESSION['newCourseID'], $_SESSION['newCourseName'], $_SESSION['isEdit']);
    header('Location: Admin.php?' . ($wasEdit ? 'edited=1' : 'added=1'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['skip_prereq'])) {
    $wasEdit = $_SESSION['isEdit'] ?? false;
    unset($_SESSION['newCourseID'], $_SESSION['newCourseName'], $_SESSION['isEdit']);
    header('Location: Admin.php?' . ($wasEdit ? 'edited=1' : 'added=1'));
    exit;
}

$val = [
    'courseName'        => $_POST['courseName']        ?? ($course['courseName']        ?? ''),
    'courseCode'        => $_POST['courseCode']        ?? ($course['courseCode']        ?? ''),
    'courseDescription' => $_POST['courseDescription'] ?? ($course['courseDescription'] ?? ''),
    'level'             => $_POST['level']             ?? ($course['level']             ?? ''),
    'track'             => $_POST['track']             ?? ($course['track']             ?? ''),
    'resourceTitle'     => $_POST['resourceTitle']     ?? ($existingResource['resourceTitle'] ?? ''),
    'resourceLink'      => $_POST['resourceLink']      ?? ($existingResource['resourceLink'] ?? ''),
];

$tracks = ['عام', 'الأمن السيبراني', 'علم البيانات والذكاء الاصطناعي', 'الشبكات وهندسة إنترنت الأشياء'];
$levels = [3=>'المستوى الثالث',4=>'المستوى الرابع',5=>'المستوى الخامس',
           6=>'المستوى السادس',7=>'المستوى السابع',8=>'المستوى الثامن'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>أثر - <?= $isEdit ? 'تعديل' : 'إضافة' ?> مقرر</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
  --maroon: #5C1F3A; --maroon-light: #7C2D4F; --salmon: #E8876A;
  --gold: #C9963A; --cream: #FFF5F0; --text-dark: #2A0F1A; --text-mid: #6B3A4A;
}
body { font-family: 'Tajawal', sans-serif; background: var(--cream); min-height: 100vh; }
nav { background: var(--maroon); padding: 16px 48px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 20px rgba(92,31,58,0.3); }
.logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.logo img { width: 48px; height: 48px; object-fit: contain; border-radius: 8px; }
.logo-text { font-size: 15px; font-weight: 600; color: white; }
.admin-badge { background: var(--gold); color: white; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 50px; }
.btn-back { background: rgba(255,255,255,0.15); color: white; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 20px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.3); }
.page-header { background: linear-gradient(135deg, var(--maroon), #3A0F25); padding: 40px 48px; }
.page-header h1 { font-size: 28px; font-weight: 800; color: white; margin-bottom: 6px; }
.page-header p { color: rgba(255,255,255,0.7); font-size: 14px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; margin-top: 12px; font-size: 13px; color: rgba(255,255,255,0.6); }
.breadcrumb a { color: var(--salmon); text-decoration: none; }
.content { padding: 48px; max-width: 700px; margin: 0 auto; }
.card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 4px 20px rgba(92,31,58,0.08); border: 1px solid #F0E4E8; }
.gold-line { width: 50px; height: 3px; background: linear-gradient(90deg, var(--maroon), var(--gold)); border-radius: 2px; margin-bottom: 32px; }
.error-box { background: rgba(192,57,43,0.08); border: 1px solid rgba(192,57,43,0.2); border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; }
.error-box p { color: #C0392B; font-size: 14px; font-weight: 600; margin-bottom: 4px; }
.error-box ul { padding-right: 20px; }
.error-box ul li { color: #C0392B; font-size: 13px; margin-top: 4px; }
.form-group { margin-bottom: 24px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
label { display: block; font-size: 14px; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
.required { color: var(--salmon); margin-right: 4px; }
.optional-label { font-size: 12px; color: var(--text-mid); font-weight: 400; margin-right: 6px; }
input[type="text"], input[type="url"], select, textarea { width: 100%; padding: 14px 16px; border: 1.5px solid #E8D6DC; border-radius: 12px; font-family: 'Tajawal', sans-serif; font-size: 15px; color: var(--text-dark); background: #FFF9F7; transition: border-color .2s, box-shadow .2s; outline: none; }
input:focus, select:focus, textarea:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(92,31,58,0.1); }
select { cursor: pointer; }
textarea { resize: vertical; min-height: 120px; line-height: 1.7; }

/* Resource section */
.resource-section { background: #FFF5F0; border: 1.5px solid #E8D6DC; border-radius: 14px; padding: 24px; margin-bottom: 24px; }
.resource-section-title { font-size: 15px; font-weight: 700; color: var(--maroon); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.resource-tabs { display: flex; gap: 8px; margin-bottom: 16px; }
.res-tab { flex: 1; padding: 10px; border: 1.5px solid #E8D6DC; border-radius: 10px; background: white; font-family: 'Tajawal', sans-serif; font-size: 13px; font-weight: 600; color: var(--text-mid); cursor: pointer; text-align: center; transition: all .2s; }
.res-tab.active { background: var(--maroon); color: white; border-color: var(--maroon); }
.res-panel { display: none; }
.res-panel.active { display: block; }
.file-upload { border: 2px dashed #E8D6DC; border-radius: 12px; padding: 24px; text-align: center; background: white; cursor: pointer; transition: border .2s; display: block; }
.file-upload:hover { border-color: var(--maroon); }
.file-upload-icon { font-size: 32px; margin-bottom: 8px; }
.file-upload p { font-size: 13px; color: var(--text-mid); margin-bottom: 6px; }
.file-upload span { font-size: 11px; color: var(--text-mid); opacity: 0.7; }
input[type="file"] { display: none; }
.file-name { margin-top: 8px; font-size: 13px; color: var(--maroon); font-weight: 600; display: none; }
.existing-res { background: white; border: 1px solid #BEE0BE; border-radius: 10px; padding: 10px 14px; font-size: 13px; color: #27AE60; font-weight: 600; margin-bottom: 12px; }

.btn-group { display: flex; gap: 12px; margin-top: 8px; }
.btn-submit { flex: 1; padding: 15px; background: linear-gradient(135deg, var(--maroon), var(--maroon-light)); color: white; border: none; border-radius: 12px; font-family: 'Tajawal', sans-serif; font-size: 16px; font-weight: 700; cursor: pointer; transition: transform .2s, box-shadow .2s; }
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(92,31,58,0.3); }
.btn-cancel-link { padding: 15px 24px; background: #F5EEF0; color: var(--text-mid); border: none; border-radius: 12px; font-family: 'Tajawal', sans-serif; font-size: 16px; font-weight: 600; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; }
.btn-cancel-link:hover { background: #EDE0E4; }
footer { background: #0F1A2E; padding: 24px; text-align: center; color: rgba(255,255,255,0.4); font-size: 12px; margin-top: 60px; }
footer strong { color: var(--salmon); }

/* Modal prereq */
.prereq-overlay { position: fixed; inset: 0; background: rgba(42,15,26,0.65); display: flex; align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(4px); }
.prereq-box { background: white; border-radius: 20px; padding: 36px 32px; max-width: 440px; width: 92%; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
.prereq-box .prereq-icon { font-size: 40px; margin-bottom: 14px; }
.prereq-box .prereq-title { font-size: 17px; font-weight: 700; color: var(--text-dark); margin-bottom: 6px; }
.prereq-box .prereq-sub { font-size: 13px; color: var(--text-mid); margin-bottom: 22px; }
.prereq-select { width: 100%; padding: 8px 12px; border: 1.5px solid #E8D6DC; border-radius: 12px; background: #FFF9F7; margin-bottom: 20px; display: none; max-height: 200px; overflow-y: auto; text-align: right; }
.prereq-select.show { display: block; }
.prereq-btns { display: flex; gap: 10px; }
.prereq-btns .btn-yes { flex: 1; padding: 12px; background: linear-gradient(135deg, var(--maroon), var(--maroon-light)); color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; font-family: 'Tajawal', sans-serif; cursor: pointer; }
.prereq-btns .btn-no { flex: 1; padding: 12px; background: #F5EEF0; color: var(--text-mid); border: none; border-radius: 12px; font-size: 15px; font-weight: 600; font-family: 'Tajawal', sans-serif; cursor: pointer; }
</style>
</head>
<body>
<nav>
  <a href="index.php" class="logo">
    <img src="images/logo.PNG" alt="أثر">
    <p class="logo-text">أترك أثرك..وساعد غيرك</p>
  </a>
  <div style="display:flex;align-items:center;gap:12px;">
    <span class="admin-badge">مدير النظام</span>
    <a href="Admin.php" class="btn-back">← لوحة الإدارة</a>
  </div>
</nav>

<div class="page-header">
  <h1><?= $isEdit ? 'تعديل المقرر' : 'إضافة مقرر جديد' ?></h1>
  <p><?= $isEdit ? 'عدّل بيانات المقرر' : 'أضف مقرراً جديداً لمنصة أثر' ?></p>
  <div class="breadcrumb">
    <a href="Admin.php">لوحة الإدارة</a> ←
    <span><?= $isEdit ? 'تعديل مقرر' : 'إضافة مقرر' ?></span>
  </div>
</div>

<div class="content">
  <div class="card">
    <div class="gold-line"></div>

    <?php if (!empty($errors)): ?>
    <div class="error-box">
      <p>يرجى تصحيح الأخطاء التالية:</p>
      <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="courseForm">

      <div class="form-row">
        <div>
          <label>رمز المقرر</label>
          <input type="text" name="courseCode" placeholder="مثال: IT 210"
                 value="<?= htmlspecialchars($val['courseCode']) ?>">
        </div>
        <div>
          <label>المستوى الدراسي <span class="required">*</span></label>
          <select name="level" required>
            <option value="" disabled <?= !$val['level'] ? 'selected' : '' ?>>اختر المستوى</option>
            <?php foreach ($levels as $num => $name): ?>
            <option value="<?= $num ?>" <?= $val['level'] == $num ? 'selected' : '' ?>><?= $name ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>المسار <span class="required">*</span></label>
        <select name="track" required>
          <option value="" disabled <?= !$val['track'] ? 'selected' : '' ?>>اختر المسار</option>
          <?php foreach ($tracks as $t): ?>
          <option value="<?= htmlspecialchars($t) ?>" <?= $val['track'] === $t ? 'selected' : '' ?>>
            <?= htmlspecialchars($t) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>اسم المقرر <span class="required">*</span></label>
        <input type="text" name="courseName" placeholder="أدخل اسم المقرر"
               value="<?= htmlspecialchars($val['courseName']) ?>" required>
      </div>

      <div class="form-group">
        <label>وصف المقرر <span class="required">*</span></label>
        <textarea name="courseDescription" placeholder="أدخل وصفاً تفصيلياً للمقرر..." required><?= htmlspecialchars($val['courseDescription']) ?></textarea>
      </div>


      <div class="resource-section">
        <div class="resource-section-title">
          📚 المصدر التعليمي
          <?php if (!$isEdit): ?>
            <span class="required">* مطلوب</span>
          <?php else: ?>
            <span class="optional-label">(اتركه فارغاً للإبقاء على الحالي)</span>
          <?php endif; ?>
        </div>

        <?php if ($isEdit && !empty($existingResource)): ?>
        <div class="existing-res">
          📎 المصدر الحالي: <?= htmlspecialchars($existingResource['resourceTitle']) ?>
        </div>
        <?php endif; ?>

        <div class="form-group">
          <label>عنوان المصدر <span class="required">*</span></label>
          <input type="text" name="resourceTitle"
                 placeholder="مثال: كتاب قواعد البيانات — Silberschatz"
                 value="<?= htmlspecialchars($val['resourceTitle']) ?>">
        </div>


        <div class="resource-tabs">
          <button type="button" class="res-tab active" onclick="switchResTab('link', this)">🔗 رابط إلكتروني</button>
          <button type="button" class="res-tab" onclick="switchResTab('file', this)">📎 رفع ملف</button>
        </div>


        <div class="res-panel active" id="panel-link">
          <input type="url" name="resourceLink"
                 placeholder="https://..."
                 value="<?= htmlspecialchars($val['resourceLink']) ?>">
        </div>


        <div class="res-panel" id="panel-file">
          <label class="file-upload" for="res-file-input">
            <div class="file-upload-icon">📎</div>
            <p>اضغطي لرفع ملف أو اسحبيه هنا</p>
            <span>PDF, DOC, PPT — حجم أقصى 10MB</span>
          </label>
          <input type="file" id="res-file-input" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx">
          <div class="file-name" id="resFileName"></div>
        </div>
      </div>


      <div class="btn-group">
        <button type="submit" class="btn-submit">
          <?= $isEdit ? 'حفظ التعديل ✓' : 'إضافة المقرر' ?>
        </button>
        <a href="Admin.php" class="btn-cancel-link">إلغاء</a>
      </div>

    </form>
  </div>
</div>

<footer><p>جميع الحقوق محفوظة &copy; 2026 — <strong>منصة أثر</strong> | لوحة الإدارة</p></footer>

<script>

function switchResTab(type, btn) {
  document.querySelectorAll('.res-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.res-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('panel-' + type).classList.add('active');
}

document.getElementById('res-file-input').addEventListener('change', function() {
  const nameEl = document.getElementById('resFileName');
  if (this.files.length > 0) {
    nameEl.textContent = '📄 ' + this.files[0].name;
    nameEl.style.display = 'block';
  } else {
    nameEl.style.display = 'none';
  }
});


function showFormError(msg) {
  let box = document.getElementById('jsErrorBox');
  if (!box) {
    box = document.createElement('div');
    box.id = 'jsErrorBox';
    box.className = 'error-box';
    document.getElementById('courseForm').prepend(box);
  }
  box.innerHTML = '<p>يرجى تصحيح الأخطاء التالية:</p><ul><li>' + msg + '</li></ul>';
  box.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function clearError(el) {
  el.style.borderColor = '';
  el.style.boxShadow = '';
}

function highlightError(el) {
  el.style.borderColor = '#C0392B';
  el.style.boxShadow = '0 0 0 3px rgba(192,57,43,0.15)';
  el.addEventListener('input', () => clearError(el), { once: true });
  el.addEventListener('change', () => clearError(el), { once: true });
}


document.getElementById('courseForm').addEventListener('submit', function(e) {
  const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

  
  const oldBox = document.getElementById('jsErrorBox');
  if (oldBox) oldBox.remove();

  const errors = [];


  const courseName = document.querySelector('[name="courseName"]');
  const level      = document.querySelector('[name="level"]');
  const track      = document.querySelector('[name="track"]');
  const desc       = document.querySelector('[name="courseDescription"]');

  if (!courseName.value.trim()) { errors.push('اسم المقرر مطلوب'); highlightError(courseName); }
  if (!level.value)             { errors.push('المستوى الدراسي مطلوب'); highlightError(level); }
  if (!track.value)             { errors.push('المسار مطلوب'); highlightError(track); }
  if (!desc.value.trim())       { errors.push('وصف المقرر مطلوب'); highlightError(desc); }


  if (!isEdit) {
    const resTitle = document.querySelector('[name="resourceTitle"]');
    const resLink  = document.querySelector('[name="resourceLink"]');
    const resFile  = document.getElementById('res-file-input').files.length > 0;

    if (!resTitle.value.trim()) {
      errors.push('عنوان المصدر التعليمي مطلوب');
      highlightError(resTitle);
    }
    if (!resLink.value.trim() && !resFile) {
      errors.push('المصدر التعليمي مطلوب — أضيفي رابطاً أو ارفعي ملفاً');
      highlightError(resLink);
    }
  }

  if (errors.length > 0) {
    e.preventDefault();
    showFormError(errors.join('</li><li>'));
  }
});
</script>

<?php if (!empty($showPrereqModal)): ?>
<div class="prereq-overlay" id="prereqOverlay">
  <div class="prereq-box">
    <div class="prereq-icon">📚</div>
    <div class="prereq-title">هل لهذا المقرر متطلب سابق؟</div>
    <div style="font-size:12px;color:var(--text-mid);margin-bottom:4px;">يمكنك اختيار أكثر من متطلب</div>
    <div class="prereq-sub">«<?= htmlspecialchars($_SESSION['newCourseName'] ?? '') ?>»</div>

    <?php
    $currentPrereqs = [];
    if (!empty($_SESSION['newCourseID'])) {
        $cpRes = mysqli_query($connection,
            "SELECT prerequisiteCourseID FROM courseprerequisite WHERE courseID=" . (int)$_SESSION['newCourseID']);
        while ($cpRow = mysqli_fetch_assoc($cpRes))
            $currentPrereqs[] = $cpRow['prerequisiteCourseID'];
    }
    ?>

    <form method="POST" id="prereqForm">
      <div class="prereq-select" id="prereqSelect">
        <?php foreach ($allCourses as $c): ?>
          <?php if ($c['courseID'] != ($_SESSION['newCourseID'] ?? 0)): ?>
          <label style="display:flex;align-items:center;gap:8px;padding:7px 4px;border-bottom:1px solid #F0E4E8;cursor:pointer;font-size:13px;">
            <input type="checkbox" name="prereqID[]" value="<?= $c['courseID'] ?>"
              <?= in_array($c['courseID'], $currentPrereqs) ? 'checked' : '' ?>
              style="accent-color:var(--maroon);width:16px;height:16px;flex-shrink:0;">
            <?= htmlspecialchars($c['courseCode'] . ' — ' . $c['courseName']) ?>
          </label>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <div class="prereq-btns" id="prereqBtnsInitial">
        <button type="button" class="btn-yes" onclick="showCourseList()">نعم</button>
        <button type="button" class="btn-no" onclick="skipPrereq()">لا، ليس له متطلب</button>
      </div>
      <div class="prereq-btns" id="prereqBtnsConfirm" style="display:none;">
        <button type="submit" name="save_prereq" class="btn-yes">حفظ المتطلب ✓</button>
        <button type="button" class="btn-no" onclick="skipPrereq()">تخطي</button>
      </div>
    </form>
  </div>
</div>

<form method="POST" id="skipForm" style="display:none;">
  <input type="hidden" name="skip_prereq" value="1">
</form>

<script>
function showCourseList() {
  document.getElementById('prereqSelect').classList.add('show');
  document.getElementById('prereqBtnsInitial').style.display = 'none';
  document.getElementById('prereqBtnsConfirm').style.display = 'flex';
}
function skipPrereq() {
  document.getElementById('skipForm').submit();
}
</script>
<?php endif; ?>

</body>
</html>
