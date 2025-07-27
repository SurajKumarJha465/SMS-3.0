<?php include("db.php"); ?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role_id = $_POST["role_id"];

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, is_approved) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("sssi", $username, $email, $password, $role_id);
    $stmt->execute();

    $user_id = $conn->insert_id;

    if ($role_id == 2) {
        // Teacher registration
        $first_name = $_POST["t_first_name"];
        $last_name = $_POST["t_last_name"];
        $department = $_POST["department"];
        $phone = $_POST["t_phone"];

        $stmt = $conn->prepare("INSERT INTO teachers (user_id, first_name, last_name, department, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $first_name, $last_name, $department, $phone);
        $stmt->execute();
    } elseif ($role_id == 3) {
        // Student registration
        $first_name = $_POST["s_first_name"];
        $last_name = $_POST["s_last_name"];
        $dob = $_POST["dob"];
        $gender = $_POST["gender"];
        $address = $_POST["address"];
        $phone = $_POST["s_phone"];

        $stmt = $conn->prepare("INSERT INTO students (user_id, first_name, last_name, dob, gender, address, phone, enrollment_date) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())");
        $stmt->bind_param("issssss", $user_id, $first_name, $last_name, $dob, $gender, $address, $phone);
        $stmt->execute();
    }

    echo "<script>alert('Registration successful. You can now login.'); window.location='login.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
    <script>
        function showFields() {
            var role = document.getElementById("role_id").value;
            document.getElementById("studentFields").style.display = role == 3 ? "block" : "none";
            document.getElementById("teacherFields").style.display = role == 2 ? "block" : "none";
        }
    </script>
</head>
<body>
<div class="form-container">
    <h2><i class="fas fa-user-plus"></i> Sign Up</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />

        <select name="role_id" id="role_id" onchange="showFields()" required>
            <option value="">Select Role</option>
            <option value="1">Admin</option>
            <option value="2">Teacher</option>
            <option value="3">Student</option>
        </select>

        <!-- Teacher Fields -->
        <div id="teacherFields" style="display: none;">
            <input type="text" name="t_first_name" placeholder="First Name" />
            <input type="text" name="t_last_name" placeholder="Last Name" />
            <input type="text" name="department" placeholder="Department" />
            <input type="text" name="t_phone" placeholder="Phone" />
        </div>

        <!-- Student Fields -->
        <div id="studentFields" style="display: none;">
            <input type="text" name="s_first_name" placeholder="First Name" />
            <input type="text" name="s_last_name" placeholder="Last Name" />
            <input type="date" name="dob" placeholder="Date of Birth" />
            <select name="gender">
                <option value="">Select Gender</option>
                <option value="M">Male</option>
                <option value="F">Female</option>
                <option value="O">Other</option>
            </select>
            <textarea name="address" placeholder="Address"></textarea>
            <input type="text" name="s_phone" placeholder="Phone" />
        </div>

        <button type="submit">Register</button>
        <p>Already registered? <a href="login.php">Login</a></p>
    </form>
</div>
</body>
</html>
