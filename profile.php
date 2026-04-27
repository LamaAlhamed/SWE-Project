<?php
session_start();
include("AtharDB.php");

if (!isset($_SESSION['studentID'])) {
    header("Location: login.php");
    exit();
}

$studentID = $_SESSION['studentID'];

// ===== Handle profile update =====
$updateMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateProfile'])) {
    $newName  = trim($_POST['newName'] ?? '');
    $newEmail = trim($_POST['newEmail'] ?? '');

    if (empty($newName) || empty($newEmail)) {
        $updateMsg = 'error:يرجى تعبئة الاسم والبريد الإلكتروني';
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $updateMsg = 'error:البريد الإلكتروني غير صحيح';
    } else {
        $stmt = mysqli_prepare($connection,
            "SELECT studentID FROM student WHERE email = ? AND studentID != ?");
        mysqli_stmt_bind_param($stmt, "si", $newEmail, $studentID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $updateMsg = 'error:البريد الإلكتروني مستخدم من حساب آخر';
        } else {
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($connection,
                "UPDATE student SET studentName = ?, email = ? WHERE studentID = ?");
            mysqli_stmt_bind_param($stmt, "ssi", $newName, $newEmail, $studentID);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['studentName']  = $newName;
                $_SESSION['studentEmail'] = $newEmail;
                $updateMsg = 'success:تم تحديث بياناتك بنجاح';
            } else {
                $updateMsg = 'error:حدث خطأ، حاولي مرة أخرى';
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// ===== Handle experience delete =====
if (isset($_GET['deleteExp'])) {
    $expID = (int) $_GET['deleteExp'];
    $stmtF = mysqli_prepare($connection,
        "SELECT studyNote FROM experience WHERE experienceID = ? AND studentID = ?");
    mysqli_stmt_bind_param($stmtF, "ii", $expID, $studentID);
    mysqli_stmt_execute($stmtF);
    $rowF = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtF));
    mysqli_stmt_close($stmtF);

    if ($rowF !== false) {
        if (!empty($rowF['studyNote']) && file_exists($rowF['studyNote'])) {
            unlink($rowF['studyNote']);
        }
        $stmt = mysqli_prepare($connection,
            "DELETE FROM experience WHERE experienceID = ? AND studentID = ?");
        mysqli_stmt_bind_param($stmt, "ii", $expID, $studentID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: profile.php?tab=mine&deleted=1");
    exit();
}

// ===== Handle like/dislike AJAX =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_reaction'])) {
    header('Content-Type: application/json');
    $expID    = (int)($_POST['experienceID'] ?? 0);
    $reaction = $_POST['reactionType'] ?? '';

    if (!in_array($reaction, ['like', 'dislike']) || $expID <= 0) {
        echo json_encode(['error' => 'invalid']); exit();
    }

    $stmtC = mysqli_prepare($connection,
        "SELECT reactionID, reactionType FROM reaction WHERE studentID = ? AND experienceID = ?");
    mysqli_stmt_bind_param($stmtC, "ii", $studentID, $expID);
    mysqli_stmt_execute($stmtC);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtC));
    mysqli_stmt_close($stmtC);

    if ($existing) {
        if ($existing['reactionType'] === $reaction) {
            // toggle off
            $stmtD = mysqli_prepare($connection, "DELETE FROM reaction WHERE reactionID = ?");
            mysqli_stmt_bind_param($stmtD, "i", $existing['reactionID']);
            mysqli_stmt_execute($stmtD);
            mysqli_stmt_close($stmtD);
            $col = $reaction === 'like' ? 'likeCount' : 'dislikeCount';
            mysqli_query($connection,
                "UPDATE experience SET $col = GREATEST(0,$col-1) WHERE experienceID=$expID");
        } else {
            // switch reaction
            $stmtU = mysqli_prepare($connection,
                "UPDATE reaction SET reactionType = ? WHERE reactionID = ?");
            mysqli_stmt_bind_param($stmtU, "si", $reaction, $existing['reactionID']);
            mysqli_stmt_execute($stmtU);
            mysqli_stmt_close($stmtU);
            $add = $reaction === 'like' ? 'likeCount' : 'dislikeCount';
            $sub = $reaction === 'like' ? 'dislikeCount' : 'likeCount';
            mysqli_query($connection,
                "UPDATE experience SET $add=$add+1,$sub=GREATEST(0,$sub-1) WHERE experienceID=$expID");
        }
    } else {
        $stmtI = mysqli_prepare($connection,
            "INSERT INTO reaction (studentID, experienceID, reactionType) VALUES (?,?,?)");
        mysqli_stmt_bind_param($stmtI, "iis", $studentID, $expID, $reaction);
        mysqli_stmt_execute($stmtI);
        mysqli_stmt_close($stmtI);
        $col = $reaction === 'like' ? 'likeCount' : 'dislikeCount';
        mysqli_query($connection, "UPDATE experience SET $col=$col+1 WHERE experienceID=$expID");
    }

    $stmtN = mysqli_prepare($connection,
        "SELECT likeCount, dislikeCount FROM experience WHERE experienceID = ?");
    mysqli_stmt_bind_param($stmtN, "i", $expID);
    mysqli_stmt_execute($stmtN);
    $counts = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtN));
    mysqli_stmt_close($stmtN);

    $stmtMy = mysqli_prepare($connection,
        "SELECT reactionType FROM reaction WHERE studentID = ? AND experienceID = ?");
    mysqli_stmt_bind_param($stmtMy, "ii", $studentID, $expID);
    mysqli_stmt_execute($stmtMy);
    $myRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtMy));
    mysqli_stmt_close($stmtMy);

    echo json_encode([
        'likeCount'    => (int)$counts['likeCount'],
        'dislikeCount' => (int)$counts['dislikeCount'],
        'myReaction'   => $myRow ? $myRow['reactionType'] : null,
    ]);
    exit();
}

// ===== Load student info =====
$stmt = $connection->prepare("SELECT studentName, email FROM student WHERE studentID = ?");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ===== Load my experiences (with my reaction) =====
$myExpResult = mysqli_query($connection, "
    SELECT e.experienceID, e.experienceContent, e.studyNote,
           e.likeCount, e.dislikeCount, e.courseID,
           c.courseCode, c.courseName,
           COALESCE(r.reactionType, '') AS myReaction
    FROM experience e
    JOIN course c ON e.courseID = c.courseID
    LEFT JOIN reaction r ON r.experienceID = e.experienceID AND r.studentID = $studentID
    WHERE e.studentID = $studentID
    ORDER BY e.experienceID DESC
");

// ===== Load liked experiences =====
$likedResult = mysqli_query($connection, "
    SELECT e.experienceID, e.experienceContent, e.likeCount, e.dislikeCount,
           c.courseCode, c.courseName, c.courseID,
           s.studentName AS authorName
    FROM reaction r
    JOIN experience e ON r.experienceID = e.experienceID
    JOIN course c     ON e.courseID = c.courseID
    JOIN student s    ON e.studentID = s.studentID
    WHERE r.studentID = $studentID AND r.reactionType = 'like'
    ORDER BY r.reactionID DESC
");

$myCount    = mysqli_num_rows($myExpResult);
$likedCount = mysqli_num_rows($likedResult);
$avatarLetter = mb_substr(trim($user['studentName']), 0, 1, "UTF-8");
$activeTab    = $_GET['tab'] ?? 'mine';
$msgType = ''; $msgText = '';
if ($updateMsg) { [$msgType, $msgText] = explode(':', $updateMsg, 2); }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>أثر - الملف الشخصي</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
  --maroon: #5C1F3A; --maroon-light: #7C2D4F;
  --salmon: #E8876A; --cream: #FFF5F0;
  --text-dark: #2A0F1A; --text-mid: #6B3A4A;
}
body { font-family: 'Tajawal', sans-serif; background: var(--cream); }
nav {
  background: var(--maroon); padding: 16px 48px;
  display: flex; align-items: center; justify-content: space-between;
  position: sticky; top: 0; z-index: 100;
}
.logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.logo img { width: 44px; height: 44px; object-fit: contain; border-radius: 8px; }
.logo-text { font-size: 15px; font-weight: 600; color: white; }
.nav-links { display: flex; align-items: center; gap: 16px; }
.nav-link { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; }
.btn-logout {
  background: rgba(255,255,255,0.12); color: white; text-decoration: none;
  padding: 8px 18px; border-radius: 8px; font-size: 14px;
  border: 1px solid rgba(255,255,255,0.2);
}
.profile-header { background: linear-gradient(135deg, var(--maroon), #3A0F25); padding: 48px; color: white; }
.profile-info { display: flex; align-items: flex-start; gap: 28px; }
.profile-avatar {
  width: 90px; height: 90px;
  background: linear-gradient(135deg, var(--salmon), var(--maroon-light));
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  font-size: 36px; font-weight: 800; color: white;
  border: 3px solid rgba(255,255,255,0.3); flex-shrink: 0;
}
.profile-right { flex: 1; }
.profile-name { font-size: 26px; font-weight: 800; margin-bottom: 4px; }
.profile-email { font-size: 14px; color: rgba(255,255,255,0.7); margin-bottom: 16px; }
.profile-stats { display: flex; gap: 24px; margin-bottom: 20px; }
.stat { text-align: center; }
.stat-num { font-size: 22px; font-weight: 800; color: var(--salmon); display: block; }
.stat-label { font-size: 12px; color: rgba(255,255,255,0.6); }
.btn-edit-profile {
  background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
  color: white; padding: 8px 20px; border-radius: 10px;
  font-family: 'Tajawal', sans-serif; font-size: 14px; font-weight: 600; cursor: pointer;
}
.edit-profile-form {
  background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
  border-radius: 16px; padding: 24px; margin-top: 20px; display: none;
}
.edit-profile-form.open { display: block; }
.edit-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.edit-profile-form label { display: block; font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.8); margin-bottom: 6px; }
.edit-profile-form input {
  width: 100%; padding: 10px 14px; border: 1.5px solid rgba(255,255,255,0.3);
  border-radius: 10px; font-family: 'Tajawal', sans-serif; font-size: 14px;
  background: rgba(255,255,255,0.1); color: white; outline: none;
}
.edit-profile-form input::placeholder { color: rgba(255,255,255,0.5); }
.edit-profile-form input:focus { border-color: var(--salmon); }
.edit-form-actions { display: flex; gap: 10px; }
.btn-save-profile {
  background: var(--salmon); color: white; border: none; padding: 10px 24px;
  border-radius: 10px; font-family: 'Tajawal', sans-serif; font-size: 14px; font-weight: 700; cursor: pointer;
}
.btn-cancel-profile {
  background: transparent; color: rgba(255,255,255,0.7);
  border: 1px solid rgba(255,255,255,0.3); padding: 10px 20px;
  border-radius: 10px; font-family: 'Tajawal', sans-serif; font-size: 14px; cursor: pointer;
}
.content { max-width: 900px; margin: 48px auto; padding: 0 24px; }
.alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }
.alert-success { background: #E8F5E8; color: #27AE60; border: 1px solid #BEE0BE; }
.alert-error   { background: #FDE8E8; color: #C0392B; border: 1px solid #F5BCBC; }

/* ── DELETE SUCCESS NOTIFICATION ── */
/* Toast — matches Admin.php style */
.toast {
  position: fixed; bottom: 32px; left: 50%; transform: translateX(-50%);
  background: #27AE60; color: white; padding: 14px 28px; border-radius: 12px;
  font-size: 15px; font-weight: 700; font-family: 'Tajawal', sans-serif;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 9999;
  animation: fadeInOut 3.5s ease forwards;
  white-space: nowrap;
}
@keyframes fadeInOut {
  0%   { opacity: 0; transform: translateX(-50%) translateY(10px); }
  12%  { opacity: 1; transform: translateX(-50%) translateY(0); }
  80%  { opacity: 1; }
  100% { opacity: 0; }
}

.tabs {
  display: flex; gap: 4px; background: white; border-radius: 14px; padding: 6px;
  box-shadow: 0 2px 12px rgba(92,31,58,0.06); margin-bottom: 28px;
  border: 1px solid rgba(92,31,58,0.08);
}
.tab-btn {
  flex: 1; padding: 10px 16px; border: none; background: none;
  border-radius: 10px; font-family: 'Tajawal', sans-serif;
  font-size: 14px; font-weight: 600; color: var(--text-mid); cursor: pointer;
}
.tab-btn.active { background: linear-gradient(135deg, var(--maroon), var(--maroon-light)); color: white; }
.tab-panel { display: none; }
.tab-panel.active { display: block; }

.experience-card {
  background: white; border-radius: 16px; padding: 24px; margin-bottom: 16px;
  box-shadow: 0 2px 12px rgba(92,31,58,0.06); border: 1px solid rgba(92,31,58,0.06);
}
.exp-course { font-size: 11px; font-weight: 700; letter-spacing: 1px; color: var(--salmon); margin-bottom: 8px; }
.exp-text { font-size: 14px; color: var(--text-mid); line-height: 1.7; margin-bottom: 16px; }
.exp-footer { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.exp-stats { display: flex; gap: 8px; }
.exp-actions { display: flex; gap: 10px; }

/* Reaction buttons */
.btn-react {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 16px; border-radius: 10px; font-size: 13px;
  font-family: 'Tajawal', sans-serif; font-weight: 600;
  cursor: pointer; border: 1.5px solid #E8D6DC;
  background: white; color: var(--text-mid); transition: all .2s;
}
.btn-react:hover { border-color: var(--maroon); color: var(--maroon); }
.btn-react.active-like    { background: rgba(39,174,96,0.1);  border-color: #27AE60; color: #27AE60; }
.btn-react.active-dislike { background: rgba(192,57,43,0.1); border-color: #C0392B; color: #C0392B; }
.btn-react .count { font-weight: 800; }

.btn-edit-exp {
  background: rgba(92,31,58,0.08); color: var(--maroon); border: none;
  padding: 7px 16px; border-radius: 8px; font-size: 13px;
  font-family: 'Tajawal', sans-serif; cursor: pointer; font-weight: 600;
  text-decoration: none; display: inline-block;
}
.btn-delete-exp {
  background: rgba(192,57,43,0.08); color: #C0392B; border: none;
  padding: 7px 16px; border-radius: 8px; font-size: 13px;
  font-family: 'Tajawal', sans-serif; cursor: pointer; font-weight: 600;
}
.study-note-tag {
  display: inline-block; font-size: 12px; color: var(--maroon);
  background: rgba(92,31,58,0.07); padding: 4px 10px;
  border-radius: 6px; margin-bottom: 12px; font-weight: 600;
}
.exp-author { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.exp-avatar-sm {
  width: 30px; height: 30px;
  background: linear-gradient(135deg, var(--salmon), var(--maroon));
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  color: white; font-size: 13px; font-weight: 700; flex-shrink: 0;
}
.exp-author-name { font-size: 13px; font-weight: 700; color: var(--text-dark); }
.empty-state { text-align: center; padding: 60px 20px; color: var(--text-mid); }
.empty-state .icon { font-size: 48px; margin-bottom: 16px; }
.empty-state p { font-size: 15px; margin-bottom: 20px; line-height: 1.7; }
.btn-add {
  display: inline-block;
  background: linear-gradient(135deg, var(--maroon), var(--maroon-light));
  color: white; text-decoration: none; padding: 12px 28px; border-radius: 12px;
  font-size: 15px; font-weight: 700;
}

/* ── DELETE CONFIRM MODAL ── */
/* Modal — exact Admin.php style */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(42,15,26,0.65);
  display: none; align-items: center; justify-content: center;
  z-index: 9999; backdrop-filter: blur(4px);
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
.modal-btns .btn-confirm-delete {
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
.modal-btns .btn-cancel-modal:hover { background: #EDE0E4; }

footer { background: #2A0F1A; padding: 24px; text-align: center; color: rgba(255,255,255,0.4); font-size: 12px; margin-top: 20px; }
footer strong { color: var(--salmon); }
</style>
</head>
<body>

<!-- ── DELETE CONFIRM MODAL ── -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal-box">
    <div class="modal-icon">⚠️</div>
    <div class="modal-title" id="modalTitle">هل تريدين حذف هذه التجربة؟</div>
    <div class="modal-sub">لا يمكن التراجع عن هذا الإجراء</div>
    <div class="modal-btns">
      <button class="btn-confirm-delete" id="confirmDeleteBtn">نعم، احذف</button>
      <button class="btn-cancel-modal" onclick="closeDeleteModal()">إلغاء</button>
    </div>
  </div>
</div>

<nav>
  <a href="index.php" class="logo">
    <img src="images/logo.PNG" alt="أثر">
    <p class="logo-text">أترك أثرك..وساعد غيرك</p>
  </a>
  <div class="nav-links">
    <a href="index.php" class="nav-link">الرئيسية</a>
    <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
  </div>
</nav>

<div class="profile-header">
  <div class="profile-info">
    <div class="profile-avatar"><?= htmlspecialchars($avatarLetter) ?></div>
    <div class="profile-right">
      <div class="profile-name"><?= htmlspecialchars($user['studentName']) ?></div>
      <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
      <div class="profile-stats">
        <div class="stat">
          <span class="stat-num"><?= $myCount ?></span>
          <div class="stat-label">تجاربي</div>
        </div>
        <div class="stat">
          <span class="stat-num"><?= $likedCount ?></span>
          <div class="stat-label">إعجاباتي</div>
        </div>
      </div>
      <button class="btn-edit-profile" onclick="toggleEditForm()">✏️ تعديل البيانات</button>
      <div class="edit-profile-form" id="editForm">
        <form method="POST">
          <div class="edit-row">
            <div>
              <label>الاسم الجديد</label>
              <input type="text" name="newName" value="<?= htmlspecialchars($user['studentName']) ?>" placeholder="اسم المستخدم">
            </div>
            <div>
              <label>البريد الإلكتروني الجديد</label>
              <input type="email" name="newEmail" value="<?= htmlspecialchars($user['email']) ?>" placeholder="بريدك الإلكتروني">
            </div>
          </div>
          <div class="edit-form-actions">
            <button type="submit" name="updateProfile" class="btn-save-profile">حفظ التغييرات</button>
            <button type="button" class="btn-cancel-profile" onclick="toggleEditForm()">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="content">

  <?php if (isset($_GET['deleted'])): ?>
    <div class="toast" id="deleteToast"> تم حذف التجربة بنجاح</div>
  <?php endif; ?>

  <?php if ($msgText): ?>
    <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>">
      <?= $msgType === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($msgText) ?>
    </div>
  <?php endif; ?>

  <div class="tabs">
    <button class="tab-btn <?= $activeTab === 'mine'  ? 'active' : '' ?>"
            onclick="switchTab('mine', this)">📝 تجاربي (<?= $myCount ?>)</button>
    <button class="tab-btn <?= $activeTab === 'liked' ? 'active' : '' ?>"
            onclick="switchTab('liked', this)">❤️ إعجاباتي (<?= $likedCount ?>)</button>
  </div>

  <!-- MY EXPERIENCES -->
  <div id="tab-mine" class="tab-panel <?= $activeTab === 'mine' ? 'active' : '' ?>">
    <?php if ($myCount > 0): ?>
      <?php while ($exp = mysqli_fetch_assoc($myExpResult)): ?>
        <div class="experience-card">
          <div class="exp-course">
            <?= htmlspecialchars($exp['courseCode']) ?> — <?= htmlspecialchars($exp['courseName']) ?>
          </div>
          <?php if (!empty($exp['studyNote'])): ?>
            <a href="<?= htmlspecialchars($exp['studyNote']) ?>" target="_blank" class="study-note-tag">📎 ملف مرفق</a>
          <?php endif; ?>
          <div class="exp-text">
            <?= nl2br(htmlspecialchars(mb_strimwidth($exp['experienceContent'], 0, 300, '...'))) ?>
          </div>
          <div class="exp-footer">
            <div class="exp-stats">
              <button class="btn-react <?= $exp['myReaction']==='like' ? 'active-like' : '' ?>"
                      onclick="sendReaction(<?= $exp['experienceID'] ?>, 'like', this)">
                👍 <span class="count"><?= (int)$exp['likeCount'] ?></span>
              </button>
              <button class="btn-react <?= $exp['myReaction']==='dislike' ? 'active-dislike' : '' ?>"
                      onclick="sendReaction(<?= $exp['experienceID'] ?>, 'dislike', this)">
                👎 <span class="count"><?= (int)$exp['dislikeCount'] ?></span>
              </button>
            </div>
            <div class="exp-actions">
              <a href="edit-experience.php?experienceID=<?= $exp['experienceID'] ?>" class="btn-edit-exp">تعديل ✏️</a>
              <button class="btn-delete-exp" onclick="openDeleteModal(<?= $exp['experienceID'] ?>)">حذف 🗑️</button>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-state">
        <div class="icon">📖</div>
        <p>لم تضيفي أي تجربة حتى الآن.<br>شاركي تجربتك وساعدي زميلاتك!</p>
        <a href="courses.php?level=3" class="btn-add">استعرضي المقررات</a>
      </div>
    <?php endif; ?>
  </div>

  <!-- LIKED EXPERIENCES -->
  <div id="tab-liked" class="tab-panel <?= $activeTab === 'liked' ? 'active' : '' ?>">
    <?php if ($likedCount > 0): ?>
      <?php while ($exp = mysqli_fetch_assoc($likedResult)): ?>
        <div class="experience-card">
          <div class="exp-course">
            <?= htmlspecialchars($exp['courseCode']) ?> — <?= htmlspecialchars($exp['courseName']) ?>
          </div>
          <div class="exp-author">
            <div class="exp-avatar-sm"><?= htmlspecialchars(mb_substr(trim($exp['authorName']), 0, 1, "UTF-8")) ?></div>
            <div class="exp-author-name"><?= htmlspecialchars($exp['authorName']) ?></div>
          </div>
          <div class="exp-text">
            <?= nl2br(htmlspecialchars(mb_strimwidth($exp['experienceContent'], 0, 300, '...'))) ?>
          </div>
          <div class="exp-footer">
            <div class="exp-stats">
              <span class="btn-react active-like" style="cursor:default;">👍 <span class="count"><?= (int)$exp['likeCount'] ?></span></span>
            </div>
            <a href="course-details.php?id=<?= $exp['courseID'] ?>" class="btn-edit-exp">عرض المقرر ←</a>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-state">
        <div class="icon">❤️</div>
        <p>لم تضغطي إعجاب على أي تجربة حتى الآن.<br>استعرضي المقررات واستفيدي من تجارب زميلاتك.</p>
        <a href="courses.php?level=3" class="btn-add">استعرضي المقررات</a>
      </div>
    <?php endif; ?>
  </div>

</div>

<footer>
  <p>جميع الحقوق محفوظة &copy; 2026 — <strong>منصة أثر</strong> | جامعة الملك سعود</p>
</footer>

<script>
// Tab switching
function switchTab(tab, btn) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  btn.classList.add('active');
}

// Edit profile toggle
function toggleEditForm() {
  document.getElementById('editForm').classList.toggle('open');
}
<?php if ($msgType === 'error'): ?>
  document.getElementById('editForm').classList.add('open');
<?php endif; ?>

// Delete modal
let pendingDeleteID = null;
function openDeleteModal(expID) {
  pendingDeleteID = expID;
  document.getElementById('deleteModal').classList.add('show');
}
function closeDeleteModal() {
  document.getElementById('deleteModal').classList.remove('show');
  pendingDeleteID = null;
}
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
  if (pendingDeleteID) {
    window.location.href = 'profile.php?deleteExp=' + pendingDeleteID + '&tab=mine';
  }
});
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) closeDeleteModal();
});

// Like / Dislike AJAX
function sendReaction(expID, type, clickedBtn) {
  const statsEl    = clickedBtn.closest('.exp-stats');
  const btns       = statsEl.querySelectorAll('.btn-react');
  const likeBtn    = btns[0];
  const dislikeBtn = btns[1];

  fetch('profile.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ajax_reaction=1&experienceID=' + expID + '&reactionType=' + type
  })
  .then(r => r.json())
  .then(data => {
    likeBtn.querySelector('.count').textContent    = data.likeCount;
    dislikeBtn.querySelector('.count').textContent = data.dislikeCount;
    likeBtn.classList.remove('active-like');
    dislikeBtn.classList.remove('active-dislike');
    if (data.myReaction === 'like')    likeBtn.classList.add('active-like');
    if (data.myReaction === 'dislike') dislikeBtn.classList.add('active-dislike');
  })
  .catch(console.error);
}

// Auto-hide toast
const toast = document.querySelector('.toast');
if (toast) setTimeout(() => toast.remove(), 3500);
</script>
</body>
</html>