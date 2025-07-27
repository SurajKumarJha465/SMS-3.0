<?php include("db.php"); ?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role_id = $_POST["role_id"];

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, is_approved) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("sssi", $username, $email, $password, $role_id);
    $stmt->execute();

    echo "<script>alert('Registration successful. You can now login.'); window.location='login.php';</script>";
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
