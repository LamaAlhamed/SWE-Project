<?php
session_start();
include 'AtharDB.php';

// ===== Handle like/dislike AJAX =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_reaction'])) {
    header('Content-Type: application/json');

    if (!isset($_SESSION['studentID'])) {
        echo json_encode(['error' => 'not_logged_in']); exit();
    }

    $studentID = (int)$_SESSION['studentID'];
    $expID     = (int)($_POST['experienceID'] ?? 0);
    $reaction  = $_POST['reactionType'] ?? '';

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
            mysqli_stmt_execute($stmtD); mysqli_stmt_close($stmtD);
            $col = $reaction === 'like' ? 'likeCount' : 'dislikeCount';
            mysqli_query($connection,
                "UPDATE experience SET $col = GREATEST(0,$col-1) WHERE experienceID=$expID");
        } else {
            // switch reaction
            $stmtU = mysqli_prepare($connection,
                "UPDATE reaction SET reactionType = ? WHERE reactionID = ?");
            mysqli_stmt_bind_param($stmtU, "si", $reaction, $existing['reactionID']);
            mysqli_stmt_execute($stmtU); mysqli_stmt_close($stmtU);
            $add = $reaction === 'like' ? 'likeCount' : 'dislikeCount';
            $sub = $reaction === 'like' ? 'dislikeCount' : 'likeCount';
            mysqli_query($connection,
                "UPDATE experience SET $add=$add+1,$sub=GREATEST(0,$sub-1) WHERE experienceID=$expID");
        }
    } else {
        $stmtI = mysqli_prepare($connection,
            "INSERT INTO reaction (studentID, experienceID, reactionType) VALUES (?,?,?)");
        mysqli_stmt_bind_param($stmtI, "iis", $studentID, $expID, $reaction);
        mysqli_stmt_execute($stmtI); mysqli_stmt_close($stmtI);
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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("المقرر غير موجود.");
}

$courseID = (int) $_GET['id'];

$courseQuery = "SELECT * FROM course WHERE courseID = $courseID";
$courseResult = mysqli_query($connection, $courseQuery);

if (!$courseResult || mysqli_num_rows($courseResult) == 0) {
    die("لم يتم العثور على المقرر.");
}

$course = mysqli_fetch_assoc($courseResult);

$resourcesQuery = "SELECT * FROM resource WHERE courseID = $courseID ORDER BY resourceID DESC";
$resourcesResult = mysqli_query($connection, $resourcesQuery);

$loggedStudentID = isset($_SESSION['studentID']) ? (int)$_SESSION['studentID'] : 0;

$experiencesQuery = "
    SELECT e.*, s.studentName,
           COALESCE(r.reactionType, '') AS myReaction
    FROM experience e
    INNER JOIN student s ON e.studentID = s.studentID
    LEFT JOIN reaction r ON r.experienceID = e.experienceID AND r.studentID = $loggedStudentID
    WHERE e.courseID = $courseID
    ORDER BY e.experienceID DESC
";
$experiencesResult = mysqli_query($connection, $experiencesQuery);

function getAvatarLetter($name) {
    return mb_substr(trim($name), 0, 1, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>أثر - تفاصيل المقرر</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --maroon: #5C1F3A; --maroon-light: #7C2D4F; --salmon: #E8876A; --cream: #FFF5F0; --text-dark: #2A0F1A; --text-mid: #6B3A4A; --gold: #C9963A; }
body { font-family: 'Tajawal', sans-serif; background: var(--cream); }
nav { background: var(--maroon); padding: 16px 48px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
.logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.logo img { width: 44px; height: 44px; object-fit: contain; border-radius: 8px; }
.logo-text { font-size: 15px; font-weight: 600; color: white; }
.nav-links { display: flex; align-items: center; gap: 16px; }
.nav-link { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; }
.btn-profile { background: rgba(255,255,255,0.15); color: white; text-decoration: none; padding: 8px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; }

.page-header { background: linear-gradient(135deg, var(--maroon), #3A0F25); padding: 48px; color: white; }
.breadcrumb { font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 16px; }
.breadcrumb a { color: rgba(255,255,255,0.6); text-decoration: none; }
.course-code-badge { display: inline-block; background: rgba(255,255,255,0.15); padding: 4px 14px; border-radius: 50px; font-size: 13px; font-weight: 700; margin-bottom: 12px; }
.page-header h1 { font-size: 32px; font-weight: 800; margin-bottom: 12px; }
.course-tags { display: flex; gap: 10px; flex-wrap: wrap; }
.tag { background: rgba(255,255,255,0.12); padding: 5px 14px; border-radius: 50px; font-size: 12px; }

.content { max-width: 1000px; margin: 0 auto; padding: 48px; }
.section { background: white; border-radius: 16px; padding: 28px; margin-bottom: 24px; box-shadow: 0 2px 12px rgba(92,31,58,0.06); }
.section-title { font-size: 18px; font-weight: 800; color: var(--maroon); margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid var(--cream); }
.section p { font-size: 15px; color: var(--text-mid); line-height: 1.8; }
.refs-list { list-style: none; }
.refs-list li { padding: 10px 0; border-bottom: 1px solid var(--cream); font-size: 14px; color: var(--text-mid); display: flex; align-items: center; gap: 10px; }
.refs-list li:last-child { border-bottom: none; }
.refs-list a { color: inherit; text-decoration: none; }
.ref-icon { width: 32px; height: 32px; background: var(--cream); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }

.exp-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.exp-header h2 { font-size: 18px; font-weight: 800; color: var(--maroon); }
.btn-add-exp { background: linear-gradient(135deg, var(--maroon), var(--maroon-light)); color: white; text-decoration: none; padding: 10px 24px; border-radius: 10px; font-size: 14px; font-weight: 700; font-family: 'Tajawal', sans-serif; border: none; cursor: pointer; transition: transform .2s; }
.btn-add-exp:hover { transform: translateY(-2px); }

.experience-card { background: var(--cream); border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 1px solid rgba(92,31,58,0.08); }
.exp-user { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.exp-avatar { width: 38px; height: 38px; background: linear-gradient(135deg, var(--salmon), var(--maroon)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 15px; font-weight: 700; }
.exp-name { font-size: 14px; font-weight: 700; color: var(--text-dark); }
.exp-date { font-size: 12px; color: var(--text-mid); }
.exp-text { font-size: 14px; color: var(--text-mid); line-height: 1.7; margin-bottom: 14px; }
.exp-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.btn-like { background: white; border: 1.5px solid #E8D6DC; color: var(--text-mid); padding: 6px 14px; border-radius: 8px; font-size: 13px; font-family: 'Tajawal', sans-serif; cursor: pointer; transition: all .2s; }
.btn-like:hover { border-color: var(--maroon); color: var(--maroon); }
.btn-like.active-like { background: #FFF0F5; border-color: var(--maroon); color: var(--maroon); font-weight: 700; }
.btn-like.active-dislike { background: #FFF0F0; border-color: #C0392B; color: #C0392B; font-weight: 700; }
.study-note-link {
  display: inline-block;
  color: var(--maroon);
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  margin-top: 8px;
}
.empty-message {
  color: var(--text-mid);
  font-size: 14px;
}
footer { background: #2A0F1A; padding: 24px 48px; text-align: center; color: rgba(255,255,255,0.4); font-size: 12px; margin-top: 20px; }
footer strong { color: var(--salmon); }
</style>
</head>
<body>
<nav>
  <a href="index.php" class="logo">
    <img src="images/logo.PNG" alt="أثر">
    <p class="logo-text">أترك أثرك..وساعد غيرك</p>
  </a>
  <div class="nav-links">
    <a href="index.php" class="nav-link">الرئيسية</a>
    <a href="profile.php" class="btn-profile">حسابي</a>
  </div>
</nav>

<div class="page-header">
  <div class="breadcrumb">
    <a href="index.php">الرئيسية</a> ←
    <a href="courses.php?level=<?php echo $course['level']; ?>">المستوى <?php echo $course['level']; ?></a> ←
    <span><?php echo htmlspecialchars($course['courseCode']); ?></span>
  </div>
  <div class="course-code-badge"><?php echo htmlspecialchars($course['courseCode']); ?></div>
  <h1><?php echo htmlspecialchars($course['courseName']); ?></h1>
  <div class="course-tags">
    <span class="tag">المستوى <?php echo htmlspecialchars($course['level']); ?></span>
    <span class="tag"><?php echo htmlspecialchars($course['track']); ?></span>
  </div>
</div>

<div class="content">

  <div class="section">
    <div class="section-title">وصف المقرر</div>
    <p><?php echo nl2br(htmlspecialchars($course['courseDescription'])); ?></p>
  </div>

  <div class="section">
    <div class="section-title">المراجع والمصادر</div>
    <ul class="refs-list">
      <?php if ($resourcesResult && mysqli_num_rows($resourcesResult) > 0): ?>
        <?php while ($resource = mysqli_fetch_assoc($resourcesResult)): ?>
          <li>
            <div class="ref-icon">🔗</div>
            <a href="<?php echo htmlspecialchars($resource['resourceLink']); ?>" target="_blank">
              <?php echo htmlspecialchars($resource['resourceTitle']); ?>
            </a>
          </li>
        <?php endwhile; ?>
      <?php else: ?>
        <li>لا توجد مصادر مضافة لهذا المقرر حالياً.</li>
      <?php endif; ?>
    </ul>
  </div>

  <div class="section">
    <div class="exp-header">
      <h2>تجارب الطلاب</h2>
      <a href="add-experience.php?courseID=<?php echo $course['courseID']; ?>" class="btn-add-exp">+ إضافة تجربة</a>
    </div>

    <?php if ($experiencesResult && mysqli_num_rows($experiencesResult) > 0): ?>
      <?php while ($exp = mysqli_fetch_assoc($experiencesResult)): ?>
        <div class="experience-card">
          <div class="exp-user">
            <div class="exp-avatar"><?php echo htmlspecialchars(getAvatarLetter($exp['studentName'])); ?></div>
            <div>
              <div class="exp-name"><?php echo htmlspecialchars($exp['studentName']); ?></div>
              <div class="exp-date">تجربة طالب/ـة</div>
            </div>
          </div>

          <div class="exp-text">
            <?php echo nl2br(htmlspecialchars($exp['experienceContent'])); ?>
          </div>

          <?php if (!empty($exp['studyNote'])): ?>
            <a class="study-note-link" href="uploads/<?php echo htmlspecialchars($exp['studyNote']); ?>" target="_blank">
              📎 عرض المذكرة المرفقة
            </a>
          <?php endif; ?>

          <div class="exp-actions">
            <?php if (isset($_SESSION['studentID'])): ?>
              <button class="btn-like <?= $exp['myReaction']==='like' ? 'active-like' : '' ?>"
                      onclick="sendReaction(<?= $exp['experienceID'] ?>, 'like', this)">
                👍 مفيدة <span class="count">(<?= (int)$exp['likeCount'] ?>)</span>
              </button>
              <button class="btn-like <?= $exp['myReaction']==='dislike' ? 'active-dislike' : '' ?>"
                      onclick="sendReaction(<?= $exp['experienceID'] ?>, 'dislike', this)">
                👎 غير مفيدة <span class="count">(<?= (int)$exp['dislikeCount'] ?>)</span>
              </button>
            <?php else: ?>
              <span class="btn-like" style="cursor:default;" title="سجّل دخولك للتفاعل">👍 مفيدة (<?= (int)$exp['likeCount'] ?>)</span>
              <span class="btn-like" style="cursor:default;" title="سجّل دخولك للتفاعل">👎 غير مفيدة (<?= (int)$exp['dislikeCount'] ?>)</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="empty-message">لا توجد تجارب مضافة لهذا المقرر حالياً.</p>
    <?php endif; ?>
  </div>
</div>

<footer><p>جميع الحقوق محفوظة &copy; 2026 — <strong>منصة أثر</strong> | جامعة الملك سعود</p></footer>

<script>
function sendReaction(expID, type, clickedBtn) {
  const actionsEl  = clickedBtn.closest('.exp-actions');
  const btns       = actionsEl.querySelectorAll('.btn-like');
  const likeBtn    = btns[0];
  const dislikeBtn = btns[1];

  fetch('course-details.php?id=<?php echo $courseID; ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ajax_reaction=1&experienceID=' + expID + '&reactionType=' + type
  })
  .then(r => r.json())
  .then(data => {
    if (data.error === 'not_logged_in') {
      window.location.href = 'login.php';
      return;
    }
    likeBtn.querySelector('.count').textContent    = '(' + data.likeCount + ')';
    dislikeBtn.querySelector('.count').textContent = '(' + data.dislikeCount + ')';
    likeBtn.classList.remove('active-like');
    dislikeBtn.classList.remove('active-dislike');
    if (data.myReaction === 'like')    likeBtn.classList.add('active-like');
    if (data.myReaction === 'dislike') dislikeBtn.classList.add('active-dislike');
  })
  .catch(console.error);
}
</script>
</body>
</html>