<?php
session_start();
include("AtharDB.php");

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// load post
if (isset($_GET['id'])) {

    $postID = $_GET['id'];

    $stmt = mysqli_prepare($connection,
        "SELECT * FROM experience WHERE experienceID=? AND studentID=?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $postID, $userID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($result);
}

// update
if (isset($_POST['update'])) {

    $content = $_POST['content'];

    $stmt = mysqli_prepare($connection,
        "UPDATE experience SET experienceContent=? WHERE experienceID=? AND studentID=?"
    );
    mysqli_stmt_bind_param($stmt, "sii", $content, $postID, $userID);
    mysqli_stmt_execute($stmt);

    header("Location: profile.php");
    exit();
}
?>
