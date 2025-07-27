<?php
$host = "localhost";
$db = "sms";
$user = "root";
$pass = "Root113890";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
