<?php
    session_start();
    
    include("AtharDB.php");
    
    if (!isset($_SESSION['studentID'])) {
        header("Location: login.php");
        exit();
    }
    $userID = $_SESSION['studentID'];

// get user info
$stmt = $conn->prepare("SELECT studentName, email FROM student WHERE studentID=?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
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
:root { --maroon: #5C1F3A; --maroon-light: #7C2D4F; --salmon: #E8876A; --cream: #FFF5F0; --text-dark: #2A0F1A; --text-mid: #6B3A4A; }
body { font-family: 'Tajawal', sans-serif; background: var(--cream); }
nav { background: var(--maroon); padding: 16px 48px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
.logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.logo img { width: 44px; height: 44px; object-fit: contain; border-radius: 8px; }
.logo-text { font-size: 15px; font-weight: 600; color: white; }
.nav-links { display: flex; align-items: center; gap: 16px; }
.nav-link { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; }
.btn-logout { background: rgba(255,255,255,0.12); color: white; text-decoration: none; padding: 8px 18px; border-radius: 8px; font-size: 14px; border: 1px solid rgba(255,255,255,0.2); cursor: pointer; font-family: 'Tajawal', sans-serif; }

.profile-header { background: linear-gradient(135deg, var(--maroon), #3A0F25); padding: 48px; color: white; }
.profile-info { display: flex; align-items: center; gap: 28px; }
.profile-avatar { width: 90px; height: 90px; background: linear-gradient(135deg, var(--salmon), var(--maroon-light)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 800; color: white; border: 3px solid rgba(255,255,255,0.3); flex-shrink: 0; }
.profile-name { font-size: 26px; font-weight: 800; margin-bottom: 6px; }
.profile-email { font-size: 14px; color: rgba(255,255,255,0.7); margin-bottom: 12px; }
.profile-stats { display: flex; gap: 24px; }
.stat { text-align: center; }
.stat-num { font-size: 22px; font-weight: 800; color: var(--salmon); display: block; }
.stat-label { font-size: 12px; color: rgba(255,255,255,0.6); }

.content { max-width: 900px; margin: 48px auto; padding: 0 24px; }
.section-title { font-size: 20px; font-weight: 800; color: var(--maroon); margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
.section-title::after { content: ''; flex: 1; height: 2px; background: linear-gradient(90deg, var(--maroon), transparent); }

.experience-card { background: white; border-radius: 16px; padding: 24px; margin-bottom: 16px; box-shadow: 0 2px 12px rgba(92,31,58,0.06); border: 1px solid rgba(92,31,58,0.06); }
.exp-course { font-size: 11px; font-weight: 700; letter-spacing: 1px; color: var(--salmon); margin-bottom: 8px; }
.exp-text { font-size: 14px; color: var(--text-mid); line-height: 1.7; margin-bottom: 16px; }
.exp-footer { display: flex; align-items: center; justify-content: space-between; }
.exp-date { font-size: 12px; color: var(--text-mid); }
.exp-actions { display: flex; gap: 10px; }
.btn-edit { background: rgba(92,31,58,0.08); color: var(--maroon); border: none; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-family: 'Tajawal', sans-serif; cursor: pointer; font-weight: 600; }
.btn-delete { background: rgba(192,57,43,0.08); color: #C0392B; border: none; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-family: 'Tajawal', sans-serif; cursor: pointer; font-weight: 600; }

.empty-state { text-align: center; padding: 60px 20px; color: var(--text-mid); }
.empty-state .icon { font-size: 48px; margin-bottom: 16px; }
.empty-state p { font-size: 16px; margin-bottom: 20px; }
.btn-add { display: inline-block; background: linear-gradient(135deg, var(--maroon), var(--maroon-light)); color: white; text-decoration: none; padding: 12px 28px; border-radius: 12px; font-size: 15px; font-weight: 700; }

footer { background: #2A0F1A; padding: 24px; text-align: center; color: rgba(255,255,255,0.4); font-size: 12px; margin-top: 20px; }
footer strong { color: var(--salmon); }

.tabs { display: flex; gap: 4px; background: white; border-radius: 14px; padding: 6px; box-shadow: 0 2px 12px rgba(92,31,58,0.06); margin-bottom: 28px; border: 1px solid rgba(92,31,58,0.08); }
.tab-btn { flex: 1; padding: 10px 16px; border: none; background: none; border-radius: 10px; font-family: 'Tajawal', sans-serif; font-size: 14px; font-weight: 600; color: var(--text-mid); cursor: pointer; transition: all .2s; }
.tab-btn.active { background: linear-gradient(135deg, var(--maroon), var(--maroon-light)); color: white; }
.tab-panel { display: none; }
.tab-panel.active { display: block; }
.liked-badge { background: rgba(232,135,106,0.12); color: var(--salmon); font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 50px; margin-right: 8px; }
.profile-actions {
  margin-bottom: 12px;
}

.profile-name-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 6px;
}

.icon-btn {
  background: transparent;
  border: none;
  padding: 0;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.icon-btn img {
  width: 30px;
  height: 30px;
  object-fit: contain;
  display: block;
}
</style>
</head>
<body>
<nav>
  <a href="index.html" class="logo">
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
    <div class="profile-avatar">ر</div>
<div>
  <div class="profile-name-row">
    <div class="profile-name" id="profileName"><?php echo $student['studenttName']; ?></div>

    <button class="icon-btn" id="editProfileBtn" onclick="toggleProfileEdit()" aria-label="تعديل البيانات" title="تعديل البيانات">
      <img id="editProfileIcon" src="images/edit.png" alt="تعديل">
    </button>
  </div>

  <div class="profile-email" id="profileEmail"><?php echo $student['email']; ?></div>

  <div class="profile-stats">
    <div class="stat"><span class="stat-num">٣</span><div class="stat-label">تجاربي</div></div>
    <div class="stat"><span class="stat-num">٢</span><div class="stat-label">إعجاب</div></div>
  </div>
</div>
  </div>
</div>

<div class="content">
  <div class="section-title">تجاربي الأكاديمية</div>


<div class="tabs">
    <button class="tab-btn active" onclick="switchTab('mine')"> تجاربي</button>
    <button class="tab-btn" onclick="switchTab('liked')"> إعجاباتي</button>
  </div>

  <!-- Reviews tab -->
  <div id="tab-mine" class="tab-panel active">
    <div id="tab-mine" class="tab-panel active">

<?php
$query = "SELECT * FROM posts";
$result = mysqli_query($conn, $query);

while ($post = mysqli_fetch_assoc($result)) {
?>

  <div class="experience-card">
    <div class="exp-course"><?php echo $experience['courseID']; ?></div>

    <div class="exp-text"><?php echo $experience['experienceContent']; ?></div>

    <div class="exp-footer">
      
      <?php if ($experience['studentID'] == $userID) { ?>
        <div class="exp-actions">
          <a href="editPost.php?id=<?php echo $row['experienceID']; ?>" class="btn-edit">تعديل</a>

          <a href="deletePost.php?id=<?php echo $row['experienceID']; ?>"
             class="btn-delete"
             onclick="return confirm('هل أنت متأكد من الحذف؟');">
             حذف
          </a>
          
          
        </div>
      <?php } ?>

    </div>
  </div>

<?php } ?>

    </div>  

 </div>

  

<footer>
  <p>جميع الحقوق محفوظة &copy; 2026 — <strong>منصة أثر</strong> | جامعة الملك سعود — قسم تقنية المعلومات</p>
</footer>
<script src="athar-ui.js"></script>
</body>
</html>
