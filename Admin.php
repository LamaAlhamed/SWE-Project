<?php
session_start();
require_once 'AtharDB.php';



// Admin auth check
if (!isset($_SESSION['adminID'])) {
    header('Location: login.php');
    exit;
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Delete course
    if (isset($_POST['action']) && $_POST['action'] === 'delete_course') {
        $id = (int)$_POST['courseID'];
        $stmt = mysqli_prepare($connection, "DELETE FROM course WHERE courseID = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: Admin.php?deleted=1');
        exit;
    }

    // Delete experience
    if (isset($_POST['action']) && $_POST['action'] === 'delete_experience') {
        $id = (int)$_POST['experienceID'];
        $stmt = mysqli_prepare($connection, "DELETE FROM experience WHERE experienceID = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: Admin.php?exp_deleted=1');
        exit;
    }

    // Logout
    if (isset($_POST['action']) && $_POST['action'] === 'logout') {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}


$totalCourses     = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS c FROM course"))['c'];
$totalExperiences = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS c FROM experience"))['c'];
$totalStudents    = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS c FROM student"))['c'];
$totalLevels      = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(DISTINCT level) AS c FROM course"))['c'];


$coursesResult = mysqli_query($connection, "
   SELECT c.courseID, c.courseCode, c.courseName, c.level, c.track,
           COUNT(e.experienceID) AS expCount
    FROM course c
    LEFT JOIN experience e ON c.courseID = e.courseID
    GROUP BY c.courseID
    ORDER BY c.level ASC, c.courseName ASC
");

$experiencesResult = mysqli_query($connection, "
    SELECT e.experienceID, e.experienceContent, e.studyNote,
           s.studentName, c.courseName, c.courseID
    FROM experience e
    JOIN student s ON e.studentID = s.studentID
    JOIN course c ON e.courseID = c.courseID
    ORDER BY c.courseID ASC
");
$expByCourse = [];
while ($row = mysqli_fetch_assoc($experiencesResult)) {
    $expByCourse[$row['courseID']][] = $row;
}

// Arabic numbers
function toArabicNum($n) {
    return str_replace(
        ['0','1','2','3','4','5','6','7','8','9'],
        ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
        (string)$n
    );
}

function levelName($lvl) {
    $map = [3=>'الثالث',4=>'الرابع',5=>'الخامس',6=>'السادس',7=>'السابع',8=>'الثامن'];
    return $map[$lvl] ?? $lvl;
}

$adminName = htmlspecialchars($_SESSION['adminName'] ?? 'المدير');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>أثر - لوحة الإدارة</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
  --maroon: #5C1F3A; --maroon-light: #7C2D4F;
  --salmon: #E8876A; --cream: #FFF5F0;
  --text-dark: #2A0F1A; --text-mid: #6B3A4A;
  --gold: #C9963A; --navy: #1E2A45;
}
body { font-family: 'Tajawal', sans-serif; background: var(--cream); }

nav {
  background: var(--navy); padding: 16px 48px;
  display: flex; align-items: center; justify-content: space-between;
  position: sticky; top: 0; z-index: 100;
}
.logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.logo img { width: 44px; height: 44px; object-fit: contain; border-radius: 8px; }
.logo-text { font-size: 15px; font-weight: 600; color: white; }
.admin-badge {
  background: var(--salmon); color: white; font-size: 11px;
  font-weight: 700; padding: 3px 10px; border-radius: 50px;
}
.btn-logout {
  background: rgba(255,255,255,0.12); color: white;
  border: 1px solid rgba(255,255,255,0.2); padding: 8px 18px;
  border-radius: 8px; font-size: 14px; font-family: 'Tajawal', sans-serif; cursor: pointer;
}

.page-header { background: linear-gradient(135deg, var(--navy), #0F1A2E); padding: 40px 48px; color: white; }
.page-header h1 { font-size: 28px; font-weight: 800; margin-bottom: 6px; }
.page-header p { color: rgba(255,255,255,0.6); font-size: 14px; }

.stats-bar {
  display: flex; gap: 20px; padding: 24px 48px;
  background: white; border-bottom: 1px solid #E8D6DC;
}
.stat-card {
  background: var(--cream); border-radius: 12px; padding: 16px 24px;
  flex: 1; text-align: center; border: 1px solid rgba(92,31,58,0.08);
}
.stat-card .num { font-size: 28px; font-weight: 900; color: var(--maroon); }
.stat-card .label { font-size: 12px; color: var(--text-mid); margin-top: 4px; }

.content { padding: 40px 48px; }
.section-header {
  display: flex; align-items: center;
  justify-content: space-between; margin-bottom: 20px;
}
.section-title { font-size: 20px; font-weight: 800; color: var(--maroon); }
.btn-add {
  background: linear-gradient(135deg, var(--maroon), var(--maroon-light));
  color: white; border: none; padding: 10px 24px; border-radius: 10px;
  font-size: 14px; font-weight: 700; font-family: 'Tajawal', sans-serif;
  cursor: pointer; text-decoration: none; display: inline-block;
}

table {
  width: 100%; background: white; border-radius: 16px;
  overflow: hidden; box-shadow: 0 2px 12px rgba(92,31,58,0.06);
  border-collapse: collapse; margin-bottom: 16px;
}
thead { background: var(--maroon); color: white; }
th { padding: 14px 20px; text-align: right; font-size: 14px; font-weight: 600; }
td { padding: 14px 20px; font-size: 14px; color: var(--text-dark); border-bottom: 1px solid var(--cream); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: #FFF5F0; }

.badge { display: inline-block; padding: 3px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; }
.badge-active { background: rgba(39,174,96,0.12); color: #27AE60; }

.btn-sm {
  padding: 5px 12px; border-radius: 7px; font-size: 12px;
  font-family: 'Tajawal', sans-serif; cursor: pointer;
  border: none; font-weight: 600; margin-left: 4px;
}
.btn-edit-sm { background: rgba(92,31,58,0.08); color: var(--maroon); text-decoration: none; display: inline-block; }
.btn-delete-sm { background: rgba(192,57,43,0.08); color: #C0392B; }
.btn-toggle-sm {
  background: rgba(30,42,69,0.08); color: var(--navy);
  padding: 5px 12px; border-radius: 7px; font-size: 12px;
  font-family: 'Tajawal', sans-serif; cursor: pointer;
  border: none; font-weight: 600; margin-left: 4px;
}

/* Experiences accordion */
.exp-accordion { display: none; background: #FFF9F7; }
.exp-accordion.open { display: table-row-group; }
.exp-accordion td { padding: 0 !important; border: none !important; }
.exp-inner { padding: 0 20px 20px; }
.exp-card {
  background: white; border-radius: 12px; padding: 16px 20px;
  margin-top: 12px; border: 1px solid #F0E4E8;
  display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
}
.exp-card-body { flex: 1; }
.exp-card-body .exp-student { font-size: 12px; color: var(--salmon); font-weight: 700; margin-bottom: 6px; }
.exp-card-body .exp-text { font-size: 14px; color: var(--text-dark); line-height: 1.7; }
.exp-card-body .exp-note { font-size: 12px; color: var(--text-mid); margin-top: 6px; font-style: italic; }
.exp-empty { font-size: 13px; color: var(--text-mid); padding: 16px 0; text-align: center; }

/* Toast */
.toast {
  position: fixed; bottom: 32px; left: 50%; transform: translateX(-50%);
  background: #27AE60; color: white; padding: 14px 28px; border-radius: 12px;
  font-size: 15px; font-weight: 700; font-family: 'Tajawal', sans-serif;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 9999;
  animation: fadeInOut 3s ease forwards;
}
@keyframes fadeInOut {
  0%   { opacity: 0; transform: translateX(-50%) translateY(10px); }
  15%  { opacity: 1; transform: translateX(-50%) translateY(0); }
  80%  { opacity: 1; }
  100% { opacity: 0; }
}

/* Confirm modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(42,15,26,0.65);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999; backdrop-filter: blur(4px); display: none;
}
.modal-overlay.show { display: flex; }
.modal-box {
  background: white; border-radius: 20px; padding: 36px 32px;
  max-width: 380px; width: 90%; text-align: center;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25);
}
.modal-box .modal-icon { font-size: 40px; margin-bottom: 14px; }
.modal-box .modal-title { font-size: 16px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
.modal-box .modal-sub { font-size: 13px; color: var(--text-mid); margin-bottom: 28px; }
.modal-btns { display: flex; gap: 10px; }
.modal-btns .btn-confirm {
  flex: 1; padding: 12px;
  background: linear-gradient(135deg,#C0392B,#E74C3C);
  color: white; border: none; border-radius: 12px;
  font-size: 15px; font-weight: 700; font-family: 'Tajawal', sans-serif; cursor: pointer;
}
.modal-btns .btn-cancel-modal {
  flex: 1; padding: 12px; background: #F5EEF0; color: var(--text-mid);
  border: none; border-radius: 12px; font-size: 15px;
  font-weight: 600; font-family: 'Tajawal', sans-serif; cursor: pointer;
}

footer {
  background: #0F1A2E; padding: 24px; text-align: center;
  color: rgba(255,255,255,0.4); font-size: 12px;
  position: sticky; bottom: 0; margin-top: 60px;
}
footer strong { color: var(--salmon); }
</style>
</head>
<body>

<?php if (isset($_GET['deleted'])): ?>
<div class="toast"> تم حذف المقرر بنجاح</div>
<?php elseif (isset($_GET['exp_deleted'])): ?>
<div class="toast"> تم حذف التجربة بنجاح</div>
<?php elseif (isset($_GET['added'])): ?>
<div class="toast"> تمت إضافة المقرر بنجاح</div>
<?php elseif (isset($_GET['edited'])): ?>
<div class="toast"> تم تعديل المقرر بنجاح</div>
<?php endif; ?>

<!-- Confirm Modal -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal-box">
    <div class="modal-icon">⚠️</div>
    <div class="modal-title" id="modalTitle">هل تريدين المتابعة؟</div>
    <div class="modal-sub">لا يمكن التراجع عن هذا الإجراء</div>
    <div class="modal-btns">
      <button class="btn-confirm" id="modalConfirm">نعم، احذف</button>
      <button class="btn-cancel-modal" onclick="closeModal()">إلغاء</button>
    </div>
  </div>
</div>

<!-- Hidden forms for POST actions -->
<form id="deleteCourseForm" method="POST" action="Admin.php" style="display:none;">
  <input type="hidden" name="action" value="delete_course">
  <input type="hidden" name="courseID" id="deleteCourseID">
</form>
<form id="deleteExpForm" method="POST" action="Admin.php" style="display:none;">
  <input type="hidden" name="action" value="delete_experience">
  <input type="hidden" name="experienceID" id="deleteExpID">
</form>
<form id="logoutForm" method="POST" action="Admin.php" style="display:none;">
  <input type="hidden" name="action" value="logout">
</form>

<nav>
  <a href="index.php" class="logo">
    <img src="images/logo.PNG" alt="أثر">
    <p class="logo-text">أترك أثرك..وساعد غيرك</p>
  </a>
  <div style="display:flex;align-items:center;gap:12px;">
    <span class="admin-badge">مدير النظام — <?= $adminName ?></span>
    <button class="btn-logout" onclick="document.getElementById('logoutForm').submit()">تسجيل الخروج</button>
  </div>
</nav>

<div class="page-header">
  <h1>لوحة الإدارة</h1>
  <p>إدارة المقررات وتجارب الطالبات</p>
</div>

<div class="stats-bar">
  <div class="stat-card">
    <div class="num"><?= toArabicNum($totalCourses) ?></div>
    <div class="label">مقرر</div>
  </div>
  <div class="stat-card">
    <div class="num"><?= toArabicNum($totalExperiences) ?></div>
    <div class="label">تجربة منشورة</div>
  </div>
  <div class="stat-card">
    <div class="num"><?= toArabicNum($totalStudents) ?></div>
    <div class="label">طالبة مسجلة</div>
  </div>
  <div class="stat-card">
    <div class="num">٦</div>
    <div class="label">مستويات</div>
  </div>
</div>

<div class="content">
  <div class="section-header">
    <div class="section-title">المقررات الدراسية</div>
    <a href="AE-Course.php" class="btn-add">+ إضافة مقرر</a>
  </div>

  <table>
    <thead>
      <tr>
        <th>رمز المقرر</th>
        <th>اسم المقرر</th>
        <th>المستوى</th>
        <th>المسار</th>
        <th>عدد التجارب</th>
        <th>إجراءات</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($course = mysqli_fetch_assoc($coursesResult)): ?>
      <?php $cid = $course['courseID']; ?>
      <tr>
        <td><?= htmlspecialchars($course['courseCode'] ?? '') ?></td>
        <td><?= htmlspecialchars($course['courseName']) ?></td>
        <td><?= levelName($course['level']) ?></td>
        <td><?= htmlspecialchars($course['track']) ?></td>
        <td><?= toArabicNum($course['expCount']) ?></td>
        <td>
          <?php if (!empty($expByCourse[$cid])): ?>
          <button class="btn-toggle-sm" onclick="toggleExp(<?= $cid ?>)">التجارب ▾</button>
          <?php endif; ?>
          <a href="AE-Course.php?edit=1&id=<?= $cid ?>" class="btn-sm btn-edit-sm">تعديل</a>
          <button class="btn-sm btn-delete-sm"
            onclick="confirmDeleteCourse(<?= $cid ?>, '<?= addslashes(htmlspecialchars($course['courseName'])) ?>')">
            حذف
          </button>
        </td>
      </tr>
      <?php if (!empty($expByCourse[$cid])): ?>
      <tr>
        <td colspan="6" style="padding:0;border:none;">
          <div id="exp-<?= $cid ?>" style="display:none;padding:0 20px 20px;">
            <?php foreach ($expByCourse[$cid] as $exp): ?>
            <div class="exp-card">
              <div class="exp-card-body">
                <div class="exp-student">👤 <?= htmlspecialchars($exp['studentName']) ?></div>
                <div class="exp-text"><?= nl2br(htmlspecialchars($exp['experienceContent'])) ?></div>
                <?php if ($exp['studyNote']): ?>
                <div class="exp-note">📝 <?= htmlspecialchars($exp['studyNote']) ?></div>
                <?php endif; ?>
              </div>
              <button class="btn-sm btn-delete-sm"
                onclick="confirmDeleteExp(<?= $exp['experienceID'] ?>)">
                حذف
              </button>
            </div>
            <?php endforeach; ?>
          </div>
        </td>
      </tr>
      <?php endif; ?>
    <?php endwhile; ?>
    <?php if ($totalCourses == 0): ?>
      <tr><td colspan="6" style="text-align:center;color:var(--text-mid);padding:32px;">لا توجد مقررات بعد</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<footer><p>جميع الحقوق محفوظة &copy; 2026 — <strong>منصة أثر</strong> | لوحة الإدارة</p></footer>

<script>
// Toggle experiences
function toggleExp(id) {
  const el = document.getElementById('exp-' + id);
  if (!el) return;
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

// Modal logic
let pendingAction = null;
function openModal(title, action) {
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('confirmModal').classList.add('show');
  pendingAction = action;
}
function closeModal() {
  document.getElementById('confirmModal').classList.remove('show');
  pendingAction = null;
}
document.getElementById('modalConfirm').addEventListener('click', function () {
  if (pendingAction) pendingAction();
  closeModal();
});
document.getElementById('confirmModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

function confirmDeleteCourse(id, name) {
  openModal('هل تريد حذف "' + name + '"؟', function() {
    document.getElementById('deleteCourseID').value = id;
    document.getElementById('deleteCourseForm').submit();
  });
}
function confirmDeleteExp(id) {
  openModal('هل تريد حذف هذه التجربة؟', function() {
    document.getElementById('deleteExpID').value = id;
    document.getElementById('deleteExpForm').submit();
  });
}

// Auto-hide toast
const toast = document.querySelector('.toast');
if (toast) setTimeout(() => toast.remove(), 3200);
</script>
</body>
</html>