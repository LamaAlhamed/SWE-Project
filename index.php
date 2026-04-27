<?php
session_start();
require_once 'AtharDB.php';

// Search functionality
$searchResults = [];
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $keyword = trim($_GET['search']);
    $stmt = mysqli_prepare($connection, 
        "SELECT courseID, courseCode, courseName, level, track 
         FROM course 
         WHERE courseName LIKE ? OR courseCode LIKE ? 
         ORDER BY level ASC LIMIT 10");
    $like = "%" . $keyword . "%";
    mysqli_stmt_bind_param($stmt, "ss", $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $searchResults[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Get course count per level for stats
$levelCounts = [];
$result = mysqli_query($connection, "SELECT level, COUNT(*) as cnt FROM course GROUP BY level");
while ($row = mysqli_fetch_assoc($result)) {
    $levelCounts[$row['level']] = $row['cnt'];
}

// Total courses count
$totalResult = mysqli_query($connection, "SELECT COUNT(*) as total FROM course");
$totalRow = mysqli_fetch_assoc($totalResult);
$totalCourses = $totalRow['total'];

$isLoggedIn = isset($_SESSION['studentID']);
$studentName = $isLoggedIn ? $_SESSION['studentName'] : '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>أثر - الصفحة الرئيسية</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
  --maroon: #5C1F3A; --maroon-light: #7C2D4F;
  --salmon: #E8876A; --salmon-light: #F0A080;
  --gold: #7C2D4F; --cream: #FFF5F0;
  --text-dark: #2A0F1A; --text-mid: #6B3A4A;
}
body { font-family: 'Tajawal', sans-serif; background: var(--cream); color: var(--text-dark); }
nav { background: var(--maroon); padding: 16px 48px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 20px rgba(92,31,58,0.3); }
.logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.logo img { width: 48px; height: 48px; object-fit: contain; border-radius: 8px; }
.logo-text { font-size: 15px; font-weight: 600; color: white; }
.nav-links { display: flex; align-items: center; gap: 16px; }
.btn-signin { color: white; text-decoration: none; font-size: 15px; font-weight: 500; padding: 8px 20px; border-radius: 8px; }
.btn-signin:hover { background: rgba(255,255,255,0.1); }
.btn-signup { background: linear-gradient(135deg, var(--salmon), var(--salmon-light)); color: white; text-decoration: none; font-size: 15px; font-weight: 600; padding: 10px 24px; border-radius: 50px; box-shadow: 0 4px 15px rgba(232,135,106,0.4); }
.btn-profile { background: rgba(255,255,255,0.15); color: white; text-decoration: none; padding: 8px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; }
.btn-logout { background: transparent; color: rgba(255,255,255,0.8); border: 1px solid rgba(255,255,255,0.3); text-decoration: none; padding: 8px 18px; border-radius: 8px; font-size: 14px; cursor: pointer; font-family: 'Tajawal', sans-serif; }

.hero { background: linear-gradient(160deg, #E8876A 0%, #C96070 40%, #7C2D4F 70%, #3A0F25 100%); padding: 100px 48px 80px; text-align: center; min-height: 480px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden; }
.hero::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse 60% 50% at 50% 100%, rgba(255,255,255,0.08) 0%, transparent 70%); }
.hero-badge { display: inline-block; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); color: white; font-size: 13px; padding: 6px 20px; border-radius: 50px; margin-bottom: 24px; position: relative; }
.hero h1 { font-size: clamp(36px,6vw,64px); font-weight: 900; color: white; line-height: 1.2; margin-bottom: 20px; position: relative; }
.hero h1 span { color: #FFD4C2; }
.hero p { font-size: 18px; color: rgba(255,255,255,0.8); max-width: 560px; line-height: 1.7; margin-bottom: 40px; position: relative; }
.hero-buttons { display: flex; gap: 16px; justify-content: center; position: relative; }
.hero-btn-primary { background: white; color: var(--maroon); text-decoration: none; font-size: 16px; font-weight: 700; padding: 14px 36px; border-radius: 50px; box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
.hero-btn-secondary { background: rgba(255,255,255,0.15); border: 1.5px solid rgba(255,255,255,0.4); color: white; text-decoration: none; font-size: 16px; font-weight: 600; padding: 14px 36px; border-radius: 50px; }

.course-search-wrapper { width: 100%; max-width: 620px; margin: 0 auto 28px; position: relative; z-index: 5; }
.course-search { width: 100%; display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); border-radius: 18px; padding: 8px; }
.course-search input { flex: 1; border: none; outline: none; background: transparent; color: white; font-family: 'Tajawal', sans-serif; font-size: 15px; padding: 14px 16px; }
.course-search input::placeholder { color: rgba(255,255,255,0.72); }
.course-search button { border: none; background: white; color: var(--maroon); font-family: 'Tajawal', sans-serif; font-size: 15px; font-weight: 800; padding: 12px 24px; border-radius: 14px; cursor: pointer; }

.search-results { background: white; border-radius: 16px; margin-top: 12px; overflow: hidden; box-shadow: 0 12px 30px rgba(42,15,26,0.16); text-align: right; }
.search-result-item { padding: 14px 18px; border-bottom: 1px solid rgba(92,31,58,0.08); color: var(--text-dark); font-size: 14px; text-decoration: none; display: block; transition: background .2s; }
.search-result-item:hover { background: #FFF5F0; }
.search-result-item:last-child { border-bottom: none; }
.result-code { color: var(--maroon); font-weight: 800; display: block; margin-bottom: 3px; }
.result-name { color: var(--text-mid); font-size: 13px; }
.no-results { padding: 20px; color: var(--text-mid); font-size: 14px; text-align: center; }

.levels-section { padding: 80px 48px; background: var(--cream); }
.section-header { text-align: center; margin-bottom: 48px; }
.section-header h2 { font-size: 32px; font-weight: 800; color: var(--maroon); margin-bottom: 12px; }
.section-header p { font-size: 16px; color: var(--text-mid); }
.gold-line { width: 60px; height: 3px; background: linear-gradient(90deg, var(--maroon), var(--gold)); border-radius: 2px; margin: 16px auto 0; }

.cards-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 24px; max-width: 1100px; margin: 0 auto; }
.level-card { border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(92,31,58,0.08); text-decoration: none; color: inherit; transition: transform .3s, box-shadow .3s; background: white; border: 1px solid rgba(92,31,58,0.08); }
.level-card:hover { transform: translateY(-6px); box-shadow: 0 12px 40px rgba(92,31,58,0.18); }
.card-visual { width: 100%; height: 180px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
.card-shine { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 50%); z-index: 1; }
.card-num { font-size: 90px; font-weight: 900; color: white; position: relative; z-index: 2; line-height: 1; }
.dot1, .dot2 { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.3); z-index: 1; }
.lv3 { background: linear-gradient(135deg, #E8876A, #B85C6E); }
.lv4 { background: linear-gradient(135deg, #7C2D4F, #5C1F3A); }
.lv5 { background: linear-gradient(135deg, #C96070, #9C3D62); }
.lv6 { background: linear-gradient(135deg, #5C1F3A, #3A0F25); }
.lv7 { background: linear-gradient(135deg, #7C2D4F, #A07020); }
.lv8 { background: linear-gradient(135deg, #E8B860, #7C2D4F); }
.card-body { padding: 20px 24px; }
.card-title { font-size: 18px; font-weight: 700; color: var(--maroon); margin-bottom: 8px; }
.card-desc { font-size: 13px; color: var(--text-mid); line-height: 1.6; margin-bottom: 16px; }
.card-footer { display: flex; align-items: center; justify-content: space-between; }
.card-courses { font-size: 12px; color: var(--text-mid); }
.card-arrow { width: 32px; height: 32px; background: linear-gradient(135deg, var(--salmon), var(--maroon)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; }
.level-card:hover .card-arrow { transform: translateX(-4px); transition: transform .2s; }

.about-section { background: linear-gradient(135deg, var(--maroon) 0%, #3A0F25 100%); padding: 80px 48px; text-align: center; }
.about-section h2 { font-size: 28px; font-weight: 800; color: white; margin-bottom: 16px; }
.about-section p { font-size: 16px; color: rgba(255,255,255,0.75); max-width: 600px; margin: 0 auto 40px; line-height: 1.8; }
.stats-grid { display: flex; justify-content: center; gap: 60px; flex-wrap: wrap; }
.stat-item { text-align: center; }
.stat-num { font-size: 42px; font-weight: 900; color: var(--salmon-light); display: block; }
.stat-label { font-size: 14px; color: rgba(255,255,255,0.6); margin-top: 4px; }

footer { background: #2A0F1A; padding: 32px 48px; text-align: center; color: rgba(255,255,255,0.4); font-size: 13px; }
footer strong { color: var(--salmon); }

@keyframes fadeUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
.hero-badge { animation: fadeUp .5s ease both; }
.hero h1 { animation: fadeUp .6s .1s ease both; }
.hero p { animation: fadeUp .6s .2s ease both; }
.hero-buttons { animation: fadeUp .6s .3s ease both; }
</style>
</head>
<body>

<nav>
  <a href="index.php" class="logo">
    <img src="images/logo.PNG" alt="أثر">
    <p class="logo-text">أترك أثرك..وساعد غيرك</p>
  </a>
  <div class="nav-links">
    <?php if ($isLoggedIn): ?>
      <span style="color:rgba(255,255,255,0.8);font-size:14px;">أهلاً، <?= htmlspecialchars($studentName) ?></span>
      <a href="profile.php" class="btn-profile">حسابي</a>
      <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
    <?php else: ?>
      <a href="login.php" class="btn-signin">تسجيل الدخول</a>
      <a href="register.php" class="btn-signup">إنشاء حساب</a>
    <?php endif; ?>
  </div>
</nav>

<section class="hero">
  <div class="hero-badge">منصة أثر الأكاديمية</div>
  <h1>اكتشف تجارب <span>زملائك</span><br>في مقرراتك</h1>
  <p>منصة تجمع تجارب الطالبات الأكاديمية في مكان واحد، لتساعدك على اتخاذ قرارات أفضل قبل التسجيل في مقرراتك</p>

  <div class="course-search-wrapper">
    <form method="GET" action="index.php">
      <div class="course-search">
        <input type="text" name="search" id="courseSearchInput"
               placeholder="ابحث عن مقرر... مثال: قواعد البيانات أو IT 222"
               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
               autocomplete="off">
        <button type="submit">بحث</button>
      </div>
    </form>

    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
      <div class="search-results">
        <?php if (!empty($searchResults)): ?>
          <?php foreach ($searchResults as $course): ?>
            <a href="course-details.php?id=<?= $course['courseID'] ?>" class="search-result-item">
              <span class="result-code"><?= htmlspecialchars($course['courseCode']) ?> — المستوى <?= $course['level'] ?></span>
              <span class="result-name"><?= htmlspecialchars($course['courseName']) ?></span>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no-results">لا توجد نتائج للبحث عن "<?= htmlspecialchars($_GET['search']) ?>"</div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="hero-buttons">
    <a href="<?= $isLoggedIn ? 'courses.php' : 'register.php' ?>" class="hero-btn-primary">ابدأ الآن</a>
    <a href="#levels" class="hero-btn-secondary">استعرض المستويات</a>
  </div>
</section>

<section class="levels-section" id="levels">
  <div class="section-header">
    <h2>استعرض المستويات الدراسية</h2>
    <p>اختر مستواك الدراسي لاستعراض المقررات والتجارب</p>
    <div class="gold-line"></div>
  </div>

  <div class="cards-grid">
    <?php
    $levels = [
      3 => ['label'=>'الثالث',  'class'=>'lv3', 'num'=>'٣'],
      4 => ['label'=>'الرابع',  'class'=>'lv4', 'num'=>'٤'],
      5 => ['label'=>'الخامس', 'class'=>'lv5', 'num'=>'٥'],
      6 => ['label'=>'السادس', 'class'=>'lv6', 'num'=>'٦'],
      7 => ['label'=>'السابع', 'class'=>'lv7', 'num'=>'٧'],
      8 => ['label'=>'الثامن', 'class'=>'lv8', 'num'=>'٨'],
    ];
    foreach ($levels as $num => $info):
      $count = isset($levelCounts[$num]) ? $levelCounts[$num] : 0;
    ?>
    <a href="courses.php?level=<?= $num ?>" class="level-card">
      <div class="card-visual <?= $info['class'] ?>">
        <div class="card-shine"></div>
        <div class="dot1" style="width:100px;height:100px;top:-20px;right:-20px;"></div>
        <div class="dot2" style="width:60px;height:60px;bottom:10px;left:10px;"></div>
        <span class="card-num"><?= $info['num'] ?></span>
      </div>
      <div class="card-body">
        <div class="card-title">المستوى <?= $info['label'] ?></div>
        <div class="card-desc">استعرض مقررات المستوى <?= $info['label'] ?> وتجارب الطلاب</div>
        <div class="card-footer">
          <span class="card-courses"><?= $count > 0 ? $count . ' مقررات متاحة' : 'لا توجد مقررات بعد' ?></span>
          <div class="card-arrow">←</div>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="about-section">
  <h2>لماذا أثر؟</h2>
  <p>أثر منصة تعليمية مخصصة لطالبات تقنية المعلومات في جامعة الملك سعود، تجمع تفاصيل المقررات وتجارب الطالبات في مكان واحد منظم ومجاني</p>
  <div class="stats-grid">
    <div class="stat-item">
      <span class="stat-num">+<?= $totalCourses ?></span>
      <div class="stat-label">مقرر دراسي</div>
    </div>
    <div class="stat-item"><span class="stat-num">6</span><div class="stat-label">مستويات دراسية</div></div>
    <div class="stat-item"><span class="stat-num">3</span><div class="stat-label">مسارات تخصصية</div></div>
  </div>
</section>

<footer>
  <p>جميع الحقوق محفوظة &copy; 2026 — <strong>منصة أثر</strong> | جامعة الملك سعود — قسم تقنية المعلومات</p>
</footer>

</body>
</html>
