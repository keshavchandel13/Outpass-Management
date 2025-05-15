<?php
session_start();
include '../../database/connectDb.php';

$message = "";
$color = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check email and password
    $stmt = $conn->prepare("SELECT id, name, role, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $role, $db_pass);
        $stmt->fetch();

        if ($password === $db_pass) {
            $_SESSION['id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role == 'student') {
                header("Location: ../student/student_dashboard.php");
            } elseif ($role == 'parent') {
                header("Location: ../parent/parent_dashboard.php");
            } elseif ($role == 'warden') {
                header("Location: ../warden/warden_dashboard.php");
            }
            elseif ($role == 'guard'){
                header("Location: ../guard/guard_dashboard.php");
            }
            elseif ($role == 'admin'){
                header("Location: ../admin/admin_dashboard.php");
            }
            exit;
        } else {
            $message = "Incorrect password!";
            $color = "#FF0000";
        }
    } else {
        $message = "Email not found!";
        $color = "#FF0000";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="typeWriter.css">
</head>

<body class="body-login">
    <div class="typewriter-container ">
        <h1 class="typewriter ">JUIT Outpass Management System</h1>
    </div>
    <div class="login-container">
        <form class="login-form" method="post">
            <h1 class="login-title">Login</h1>

            <?php if ($message): ?>
                <p style="color: <?php echo $color; ?>;"><?php echo $message; ?></p>
            <?php endif; ?>

            <input type="email" name="email" placeholder="Email" required class="login-input">
            <input type="password" name="password" placeholder="Password" required class="login-input">
            <button type="submit" class="login-button">Login</button>
            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
        </form>
    </div>
</body>

</html>