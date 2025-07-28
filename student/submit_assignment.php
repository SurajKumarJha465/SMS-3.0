<?php
session_start();
include("../db.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role_id"] != 3 || !$_SESSION["is_approved"]) {
    header("Location: ../login.php");
    exit;
}

$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student_id = $_SESSION["student_id"] ?? 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, status) VALUES (?, ?, 'Submitted')");
    $stmt->bind_param("ii", $assignment_id, $student_id);
    if ($stmt->execute()) {
        echo "<script>alert('Assignment submitted successfully!'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error submitting assignment: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Assignment</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
<div class="form-container">
    <h2><i class="fas fa-upload"></i> Submit Assignment</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="input-icon">
            <i class="fas fa-file-upload"></i>
            <input type="file" name="assignment_file" required />
        </div>
        <button type="submit">Submit</button>
    </form>
</div>
</body>
</html>