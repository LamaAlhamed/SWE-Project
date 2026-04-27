
<?php
    session_start();
    include("AtharDB.php");

    if (!isset($_SESSION['userID'])) {
        header("Location: login.php");
        exit();
    }

    $id = $_GET['id'];
    $userID = $_SESSION['userID'];

    // only delete if user owns the post
    $stmt = $conn->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $userID);
    $stmt->execute();

    header("Location: userPage.php");
    exit();
?>