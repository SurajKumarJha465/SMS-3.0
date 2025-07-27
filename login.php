<?php 
include("db.php");

session_start(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $role_id = $_POST["role_id"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role_id = ?");
    $stmt->bind_param("si", $username, $role_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["role_id"] = $user["role_id"];

            // Redirect based on role
            switch ($user["role_id"]) {
                case 1:
                    header("Location: admin/dashboard.php");
                    break;
                case 2:
                    header("Location: teacher/dashboard.php");
                    break;
                case 3:
                    header("Location: student/dashboard.php");
                    break;
                default:
                    echo "Unknown role.";
            }
            exit;
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
    <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="form-container">
        <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required />
            <input type="password" name="password" placeholder="Password" required />
            <select name="role_id" required>
                <option value="">Select Role</option>
                <option value="1">Admin</option>
                <option value="2">Teacher</option>
                <option value="3">Student</option>
            </select>
            <button type="submit">Login</button>
            <p>No account? <a href="register.php">Register</a></p>
        </form>
    </div>
</body>

</html>