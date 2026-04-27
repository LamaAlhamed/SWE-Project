<?php
include 'AtharDB.php';

$level = isset($_GET['level']) ? (int) $_GET['level'] : 3;

$sql = "
    SELECT c.courseID, c.courseCode, c.courseName, c.courseDescription, c.level, c.track,
           COUNT(e.experienceID) AS experienceCount
    FROM course c
    LEFT JOIN experience e ON c.courseID = e.courseID
    WHERE c.level = $level
    GROUP BY c.courseID
    ORDER BY c.courseCode ASC
";

$result = mysqli_query($connection, $sql);

function getTrackClass($track) {
    $track = trim($track);

    if ($track === 'الأمن السيبراني' || strtolower($track) === 'cybersecurity' || strtolower($track) === 'cyber') {
        return 'cyber';
    } elseif ($track === 'الذكاء الاصطناعي' || strtolower($track) === 'ai') {
        return 'ai';
    } elseif ($track === 'الشبكات' || strtolower($track) === 'networks') {
        return 'networks';
    } else {
        return 'general';
    }
}

function getTrackLabel($track) {
    $track = trim($track);

    if ($track === 'Cybersecurity') return 'الأمن السيبراني';
    if ($track === 'AI') return 'الذكاء الاصطناعي';
    if ($track === 'Networks') return 'الشبكات';
    if ($track === '' || strtolower($track) === 'general') return 'عام';

    return $track;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>أثر - المقررات</title>
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
.breadcrumb span { color: white; }
.page-header h1 { font-size: 32px; font-weight: 800; margin-bottom: 8px; }
.page-header p { color: rgba(255,255,255,0.7); font-size: 15px; }

.content { padding: 48px; max-width: 1200px; margin: 0 auto; }
.section-title { font-size: 20px; font-weight: 800; color: var(--maroon); margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
.section-title::after { content: ''; flex: 1; height: 2px; background: linear-gradient(90deg, var(--maroon), transparent); border-radius: 2px; }

.courses-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.course-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 12px rgba(92,31,58,0.08); text-decoration: none; color: inherit; transition: transform .2s, box-shadow .2s; border: 1px solid rgba(92,31,58,0.06); display: block; }
.course-card:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(92,31,58,0.14); }
.course-code { font-size: 11px; font-weight: 700; letter-spacing: 1.5px; color: var(--salmon); margin-bottom: 8px; }
.course-name { font-size: 17px; font-weight: 700; color: var(--maroon); margin-bottom: 10px; line-height: 1.4; }
.course-desc { font-size: 13px; color: var(--text-mid); line-height: 1.6; margin-bottom: 16px; }
.course-meta { display: flex; align-items: center; justify-content: space-between; }
.course-exp { font-size: 12px; color: var(--text-mid); }
.course-arrow { width: 28px; height: 28px; background: linear-gradient(135deg, var(--salmon), var(--maroon)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 13px; }

footer { background: #2A0F1A; padding: 24px 48px; text-align: center; color: rgba(255,255,255,0.4); font-size: 12px; margin-top: 60px; }
footer strong { color: var(--salmon); }

.course-meta-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-direction: row-reverse;
  gap: 12px;
  margin-bottom: 10px;
}

.course-track {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
  border: 1px solid rgba(92,31,58,0.14);
  box-shadow: 0 2px 8px rgba(92,31,58,0.06);
}

.course-track.general { background: #FFF5F0; color: #5C1F3A; }
.course-track.cyber { background: rgba(124,45,79,0.10); color: #7C2D4F; }
.course-track.networks { background: rgba(232,135,106,0.14); color: #A24E3B; }
.course-track.ai { background: rgba(201,150,58,0.12); color: #8A6518; }

.empty-message {
  background: white;
  border-radius: 16px;
  padding: 40px 60px;
  text-align: center;
  color: var(--text-mid);
  box-shadow: 0 2px 12px rgba(92,31,58,0.08);
  max-width: 850px;
  width: 100%;
  margin: 40px auto;
  grid-column: 1 / -1;
  font-size: 22px;
  line-height: 1.8;
}
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
  <div class="breadcrumb"><a href="index.php">الرئيسية</a> ← <span>المستوى <?php echo $level; ?></span></div>
  <h1>مقررات المستوى <?php echo $level; ?></h1>
  <p>استعرض جميع المقررات المتاحة في هذا المستوى</p>
</div>

<div class="content">
  <div class="section-title">المقررات الدراسية</div>
  <div class="courses-grid">

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <a href="course-details.php?id=<?php echo $row['courseID']; ?>" class="course-card">
              <div class="course-meta-top">
                <div class="course-track <?php echo getTrackClass($row['track']); ?>">
                  <?php echo htmlspecialchars(getTrackLabel($row['track'])); ?>
                </div>
                <div class="course-code"><?php echo htmlspecialchars($row['courseCode']); ?></div>
              </div>

              <div class="course-name"><?php echo htmlspecialchars($row['courseName']); ?></div>

              <div class="course-desc">
                <?php echo htmlspecialchars(mb_strimwidth($row['courseDescription'], 0, 100, '...')); ?>
              </div>

              <div class="course-meta">
                <span class="course-exp"><?php echo $row['experienceCount']; ?> تجربة</span>
                <div class="course-arrow">←</div>
              </div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-message">
          لا توجد مقررات مضافة لهذا المستوى حالياً.
        </div>
    <?php endif; ?>

  </div>
</div>

<footer><p>جميع الحقوق محفوظة &copy; 2026 — <strong>منصة أثر</strong> | جامعة الملك سعود</p></footer>
</body>
</html>