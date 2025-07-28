<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role_id"] != 3) {
    header("Location: ../login.php");
    exit;
}

include("../db.php");

// Fetch student name using user_id
$user_id = $_SESSION["user_id"];
$query = $conn->prepare("SELECT first_name, last_name FROM students WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$student = $result->fetch_assoc();

$full_name = ($student) ? $student['first_name'] . ' ' . $student['last_name'] : 'Student';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h2>
    <a href="../logout.php">Logout</a>
</body>
</html>
