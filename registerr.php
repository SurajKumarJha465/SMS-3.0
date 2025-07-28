<?php
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role_id = $_POST["role_id"];

    // Validate role_id
    if (!in_array($role_id, [2, 3])) {
        echo "<script>alert('Error: Invalid role selected.'); window.location='registerr.php';</script>";
        exit;
    }

    // 1. Insert into users
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, is_approved) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("sssi", $username, $email, $password, $role_id);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // 2. Insert into role-specific table
        try {
            if ($role_id == 2) { // Teacher
                $insertTeacher = $conn->prepare("INSERT INTO teachers (user_id) VALUES (?)");
                $insertTeacher->bind_param("i", $user_id);
                if (!$insertTeacher->execute()) {
                    throw new Exception("Failed to insert into teachers table: " . $insertTeacher->error);
                }
                $insertTeacher->close();
            } elseif ($role_id == 3) { // Student
                $insertStudent = $conn->prepare("INSERT INTO students (user_id) VALUES (?)");
                $insertStudent->bind_param("i", $user_id);
                if (!$insertStudent->execute()) {
                    throw new Exception("Failed to insert into students table: " . $insertStudent->error);
                }
                $insertStudent->close();
            }
            echo "<script>alert('Registration successful. You can now login.'); window.location='login.php';</script>";
        } catch (Exception $e) {
            // Rollback user insertion if role-specific insertion fails
            $conn->query("DELETE FROM users WHERE user_id = $user_id");
            echo "<script>alert('Error: " . $e->getMessage() . "'); window.location='registerr.php';</script>";
        }
    } else {
        echo "<script>alert('Error: Registration failed. " . $stmt->error . "'); window.location='registerr.php';</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>
    <div class="form-container">
        <h2><i class="fas fa-user-plus"></i> Sign Up</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required />
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password" required />
            <div>
                <label><input type="radio" name="role_id" value="2" required> Teacher</label>
                <label><input type="radio" name="role_id" value="3" required> Student</label>
            </div>
            <button type="submit">Register</button>
            <p>Already registered? <a href="login.php">Login</a></p>
        </form>
    </div>
</body>

</html>