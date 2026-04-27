<?php
session_start();
include("AtharDB.php");

$errors = [];
$success = false;

if (isset($_POST['register'])) {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (empty($username)) $errors[] = 'اسم المستخدم مطلوب';
    if (empty($email))    $errors[] = 'البريد الإلكتروني مطلوب';
    if (empty($password)) $errors[] = 'كلمة المرور مطلوبة';
    if ($password !== $confirm) $errors[] = 'كلمتا المرور غير متطابقتين';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'البريد الإلكتروني غير صحيح';

    if (empty($errors)) {
        // check if email already used
        $stmt = mysqli_prepare($connection, "SELECT studentID FROM student WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'البريد الإلكتروني مستخدم مسبقاً';
        }
        mysqli_stmt_close($stmt);
    }

    if (empty($errors)) {
        // check if username already used
        $stmt = mysqli_prepare($connection, "SELECT studentID FROM student WHERE studentName = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'اسم المستخدم مستخدم مسبقاً';
        }
        mysqli_stmt_close($stmt);
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($connection,
            "INSERT INTO student (studentName, email, password) VALUES (?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashedPassword);

        if (mysqli_stmt_execute($stmt)) {
            $newID = mysqli_insert_id($connection);
            mysqli_stmt_close($stmt);

            // Set correct session variables matching what the rest of the site expects
            $_SESSION['studentID']    = $newID;
            $_SESSION['studentName']  = $username;
            $_SESSION['studentEmail'] = $email;

            header("Location: profile.php");
            exit();
        } else {
            $errors[] = 'حدث خطأ أثناء إنشاء الحساب، حاولي مرة أخرى';
            mysqli_stmt_close($stmt);
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
:root {
  --maroon: #5C1F3A; --maroon-light: #7C2D4F;
  --salmon: #E8876A; --cream: #FFF5F0;
  --text-dark: #2A0F1A; --text-mid: #6B3A4A;
}
body {
  font-family: 'Tajawal', sans-serif;
  background: linear-gradient(160deg, #E8876A 0%, #C96070 40%, #7C2D4F 70%, #3A0F25 100%);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
nav {
  background: var(--maroon);
  padding: 16px 48px;
  display: flex;
  align-items: center;
}
.logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.logo img { width: 44px; height: 44px; object-fit: contain; border-radius: 8px; }
.logo-text { font-size: 15px; font-weight: 600; color: white; }
.main {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}
.card {
  background: white;
  border-radius: 24px;
  padding: 48px 40px;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.card-header { text-align: center; margin-bottom: 36px; }
.card-header h1 { font-size: 28px; font-weight: 800; color: var(--maroon); margin-bottom: 8px; }
.card-header p { font-size: 14px; color: var(--text-mid); }
.form-group { margin-bottom: 20px; }
label { display: block; font-size: 14px; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
input {
  width: 100%;
  padding: 12px 16px;
  border: 1.5px solid #E8D6DC;
  border-radius: 12px;
  font-size: 15px;
  font-family: 'Tajawal', sans-serif;
  color: var(--text-dark);
  background: #FFF5F0;
  transition: border .2s;
  outline: none;
}
input:focus { border-color: var(--maroon); background: white; }
.alert-error {
  background: #FDE8E8;
  color: #C0392B;
  border: 1px solid #F5BCBC;
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 14px;
  margin-bottom: 20px;
}
.alert-error ul { margin-right: 20px; }
.alert-error ul li { margin-bottom: 4px; }
.btn-main {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, var(--maroon), var(--maroon-light));
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 700;
  font-family: 'Tajawal', sans-serif;
  cursor: pointer;
  margin-top: 8px;
  transition: transform .2s, box-shadow .2s;
}
.btn-main:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(92,31,58,0.3); }
.link-text { text-align: center; font-size: 14px; color: var(--text-mid); margin-top: 20px; }
.link-text a { color: var(--maroon); font-weight: 700; text-decoration: none; }
.link-text a:hover { text-decoration: underline; }
footer {
  background: #2A0F1A;
  padding: 20px;
  text-align: center;
  color: rgba(255,255,255,0.4);
  font-size: 12px;
}
footer strong { color: var(--salmon); }
</style>
</head>
<body>
<nav>
  <a href="index.php" class="logo">
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

    <?php if (!empty($errors)): ?>
      <div class="alert-error">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>اسم المستخدم</label>
        <input type="text" name="username" placeholder="أدخل اسم المستخدم"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>البريد الإلكتروني</label>
        <input type="email" name="email" placeholder="أدخل بريدك الإلكتروني"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>كلمة المرور</label>
        <input type="password" name="password" placeholder="أدخل كلمة المرور">
      </div>
      <div class="form-group">
        <label>تأكيد كلمة المرور</label>
        <input type="password" name="confirm" placeholder="أعد إدخال كلمة المرور">
      </div>
      <button type="submit" class="btn-main" name="register">إنشاء الحساب</button>
    </form>

    <div class="link-text">لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></div>
  </div>
</div>

<footer><p>جميع الحقوق محفوظة &copy; 2026 — <strong>منصة أثر</strong></p></footer>
</body>
</html>