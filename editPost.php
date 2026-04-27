<?php
session_start();
include("AtharDB.php");

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// GET post
if (isset($_GET['id'])) {

    $postID = $_GET['id'];

    //only get user's post
    $stmt = $conn->prepare("SELECT * FROM experience WHERE experienceID=? AND studentID=?");
    $stmt->bind_param("ii", $postID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
}

// UPDATE post
if (isset($_POST['update'])) {

    $content = $_POST['content'];

    $stmt = $conn->prepare("UPDATE experience SET experienceContent=? WHERE experienceID=? AND studentID=?");
    $stmt->bind_param("sii", $content, $postID, $userID);

    if ($stmt->execute()) {
        header("Location: profile.php");
        exit();
    }
}
?>
    <form method="POST">
        <textarea name="content"><?php echo $post['experienceContent']; ?></textarea>
        <button type="submit" name="update">Update</button>
    </form>