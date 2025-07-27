<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role_id"] != 2) {
    header("Location: ../login.php");
    exit;
}

include("../db.php");

// Fetch teacher name using user_id
$user_id = $_SESSION["user_id"];
$query = $conn->prepare("SELECT first_name, last_name FROM teachers WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$teacher = $result->fetch_assoc();
$full_name = $teacher['first_name'] . ' ' . $teacher['last_name'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h2>
    <a href="../logout.php">Logout</a>
</body>
</html>
