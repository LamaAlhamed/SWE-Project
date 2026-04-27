
<?php
session_start();
include("AtharDB.php");

// must be logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {

    $postID = $_GET['id'];
    $userID = $_SESSION['userID'];

    //only delete YOUR post
    $stmt = $conn->prepare("DELETE FROM experience WHERE experienceID=? AND studentID=?");
    $stmt->bind_param("ii", $postID, $userID);

    if ($stmt->execute()) {
        header("Location: profile.php");
        exit();
    } else {
        echo "Error deleting post";
    }
}
?>