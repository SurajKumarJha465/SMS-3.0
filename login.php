<?php
include("db.php");
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if ($user['is_approved'] == 0) {
            $error = "Your account is not approved yet.";
        } elseif (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["role_id"] = $user["role_id"];

            switch ($user["role_id"]) {
                case 1: // Admin
                    header("Location: admin/dashboard.php");
                    break;
                case 2: // Teacher
                    $stmt = $conn->prepare("SELECT first_name, department FROM teachers WHERE user_id = ?");
                    $stmt->bind_param("i", $user["user_id"]);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $teacher = $res->fetch_assoc();

                    if (empty($teacher['first_name']) || empty($teacher['department'])) {
                        header("Location: teacher/profile_setup.php");
                    } else {
                        header("Location: teacher/dashboard.php");
                    }
                    break;

                case 3: // Student
                    // Check if profile is incomplete
                    $check = $conn->prepare("SELECT first_name, dob FROM students WHERE user_id = ?");
                    $check->bind_param("i", $user["user_id"]);
                    $check->execute();
                    $res = $check->get_result();
                    $student = $res->fetch_assoc();

                    if (empty($student['first_name']) || empty($student['dob'])) {
                        header("Location: student/profile_setup.php");
                    } else {
                        header("Location: student/dashboard.php");
                    }
                    break;
                default:
                    $error = "Unknown role.";
            }
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>
    <div class="form-container">
        <h2><i class="fas fa-sign-in-alt"></i> Login</h2>

        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

        <form method="POST">
            <div class="input-icon">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Username" required />
            </div>

            <div class="input-icon">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required />
            </div>

            <button type="submit">Login</button>
            <p>No account? <a href="registerr.php">Register</a></p>
        </form>
    </div>
</body>

</html>