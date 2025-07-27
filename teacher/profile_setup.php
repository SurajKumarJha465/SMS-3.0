<?php
session_start();
include("../db.php");

// Protect the page
if (!isset($_SESSION["user_id"]) || $_SESSION["role_id"] != 2) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $department = $_POST["department"];
    $phone = $_POST["phone"];

    $stmt = $conn->prepare("UPDATE teachers SET first_name = ?, last_name = ?, department = ?, phone = ? WHERE user_id = ?");
    $stmt->bind_param("ssssi", $first_name, $last_name, $department, $phone, $user_id);
    $stmt->execute();

    echo "<script>alert('Profile updated successfully!'); window.location='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Teacher Profile</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="form-container">
    <h2>👨‍🏫 Complete Your Profile</h2>
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required />
        <input type="text" name="last_name" placeholder="Last Name" required />
        <input type="text" name="department" placeholder="Department" required />
        <input type="text" name="phone" placeholder="Phone Number" required />
        <button type="submit">Save Profile</button>
    </form>
</div>
</body>
</html>
