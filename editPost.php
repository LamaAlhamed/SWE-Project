<?php
    session_start();
    include("AtharDB.php");

    if (!isset($_SESSION['userID'])) {
        header("Location: login.php");
        exit();
    }

    $id = $_GET['id'];
    $userID = $_SESSION['userID'];

    // get post (only if owner)
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "Not allowed";
        exit();
    }

    $post = $result->fetch_assoc();

    // update
    if (isset($_POST['save'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];

        $stmt = $conn->prepare("UPDATE posts SET title=?, content=? WHERE id=?");
        $stmt->bind_param("ssi", $title, $content, $id);
        $stmt->execute();

        header("Location: userPage.php");
        exit();
    }
?>

    <form method="POST">
        <input type="text" name="title" value="<?php echo $post['title']; ?>">
        <textarea name="content"><?php echo $post['content']; ?></textarea>
        <button name="save">Save Changes</button>
    </form>