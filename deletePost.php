
<?php
session_start();
include("AtharDB.php");

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {

    $postID = $_GET['id'];
    $userID = $_SESSION['userID'];

    $stmt = mysqli_prepare($connection,
        "DELETE FROM experience WHERE experienceID=? AND studentID=?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $postID, $userID);
    mysqli_stmt_execute($stmt);

    header("Location: profile.php");
    exit();
}
?>
