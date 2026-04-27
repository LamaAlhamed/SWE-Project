<?php
session_start();
include("AtharDB.php");

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    // check empty
    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        echo "<script>alert('يرجى تعبئة جميع الحقول');</script>";
    }
    // check passwords match
    elseif ($password !== $confirm) {
        echo "<script>alert('كلمتا المرور غير متطابقتين');</script>";
    }
    else {
        // check email exists
        $check = $conn->prepare("SELECT * FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('البريد الإلكتروني مستخدم مسبقاً');</script>";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                $_SESSION['userID'] = $stmt->insert_id;
                $_SESSION['username'] = $username;

                header("Location: userPage.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>أثر - إنشاء حساب</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --maroon: #5C1F3A; --maroon-light: #7C2D4F; --salmon: #E8876A; --salmon-light: #F0A080; --cream: #FFF5F0; --text-dark: #2A0F1A; --text-mid: #6B3A4A; }
body { font-family: 'Tajawal', sans-serif; background: linear-gradient(160deg, #E8876A 0%, #C96070 40%, #7C2D4F 70%, #3A0F25 100%); min-height: 100vh; display: flex; flex-direction: column; }
nav { background: var(--maroon); padding: 16px 48px; display: flex; align-items: center; }
.logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.logo img { width: 44px; height: 44px; object-fit: contain; border-radius: 8px; }
.logo-text { font-size: 15px; font-weight: 600; color: white; }
.main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
.card { background: white; border-radius: 24px; padding: 48px 40px; width: 100%; max-width: 480px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
.card-header { text-align: center; margin-bottom: 36px; }
.card-header h1 { font-size: 28px; font-weight: 800; color: var(--maroon); margin-bottom: 8px; }
.card-header p { font-size: 14px; color: var(--text-mid); }
.form-group { margin-bottom: 20px; }
label { display: block; font-size: 14px; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
input { width: 100%; padding: 12px 16px; border: 1.5px solid #E8D6DC; border-radius: 12px; font-size: 15px; font-family: 'Tajawal', sans-serif; color: var(--text-dark); background: #FFF5F0; transition: border .2s; outline: none; }
input:focus { border-color: var(--maroon); background: white; }
.btn-main { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--maroon), var(--maroon-light)); color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 700; font-family: 'Tajawal', sans-serif; cursor: pointer; margin-top: 8px; transition: transform .2s, box-shadow .2s; }
.btn-main:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(92,31,58,0.3); }
.link-text { text-align: center; font-size: 14px; color: var(--text-mid); margin-top: 20px; }
.link-text a { color: var(--maroon); font-weight: 700; text-decoration: none; }
footer { background: #2A0F1A; padding: 20px; text-align: center; color: rgba(255,255,255,0.4); font-size: 12px; }
footer strong { color: var(--salmon); }
</style>
</head>
<body>
<nav>
  <a href="index.html" class="logo">
    <img src="images/logo.PNG" alt="أثر">
        <p class="logo-text">أترك أثرك..وساعد غيرك</p>
  </a>
</nav>
<div class="main">
  <div class="card">
    <div class="card-header">
      <h1>إنشاء حساب جديد</h1>
      <p>انضمي إلى منصة أثر وشاركي تجاربك الأكاديمية</p>
    </div>
      
    <form method="POST">
        <div class="form-group">
          <label>اسم المستخدم</label>
          <input type="text" placeholder="أدخل اسم المستخدم">
        </div>
        <div class="form-group">
          <label>البريد الإلكتروني</label>
          <input type="email" placeholder="أدخل بريدك الإلكتروني">
        </div>
        <div class="form-group">
          <label>كلمة المرور</label>
          <input type="password" placeholder="أدخل كلمة المرور">
        </div>
        <div class="form-group">
          <label>تأكيد كلمة المرور</label>
          <input type="password" placeholder="أعد إدخال كلمة المرور">
        </div>
        
        <button class="btn-main" name="register">إنشاء الحساب</button>

    </form>
      
    <div class="link-text">لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></div>
      </div>
      </div>
      <footer><p>جميع الحقوق محفوظة &copy; 2026 — <strong>منصة أثر</strong></p></footer>
      
      <script src="athar-ui.js"></script>
      
      </body>
      
      </html>
