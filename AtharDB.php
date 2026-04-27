<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "localhost:8889";
$user = "root";
$pass = "root";
$dbname = "athar";

$connection = mysqli_connect($host, $user, $pass, $dbname);

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>