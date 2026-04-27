
<?php
session_start();
include("AtharDB.php");

if (!isset($_SESSION['studentID'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {

    $experienceID = $_GET['experienceID'];
    $studentID = $_SESSION['studentID'];

    $stmt = mysqli_prepare($connection,
        "DELETE FROM experience WHERE experienceID=? AND studentID=?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $experienceID, $studentID);
    mysqli_stmt_execute($stmt);

    header("Location: profile.php");
    exit();
}
?>
