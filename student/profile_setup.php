<?php
session_start();
include("../db.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role_id"] != 3) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $dob = $_POST["dob"];
    $gender = $_POST["gender"];
    $address = $_POST["address"];
    $phone = $_POST["phone"];

    $stmt = $conn->prepare("UPDATE students SET first_name = ?, last_name = ?, dob = ?, gender = ?, address = ?, phone = ? WHERE user_id = ?");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $dob, $gender, $address, $phone, $user_id);
    $stmt->execute();

    echo "<script>alert('Profile updated successfully!'); window.location='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Your Profile</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="form-container">
    <h2>👤 Complete Your Profile</h2>
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required />
        <input type="text" name="last_name" placeholder="Last Name" required />
        <input type="date" name="dob" required />

        <div>
            <label><input type="radio" name="gender" value="Male" required> Male</label>
            <label><input type="radio" name="gender" value="Female" required> Female</label>
            <label><input type="radio" name="gender" value="Other" required> Other</label>
        </div>

        <textarea name="address" placeholder="Address" required></textarea>
        <input type="text" name="phone" placeholder="Phone Number" required />
        <button type="submit">Save Profile</button>
    </form>
</div>
</body>
</html>
