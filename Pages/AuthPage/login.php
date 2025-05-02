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
    <style>
        .body-login {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 80px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .login-form {
            display: flex;
            flex-direction: column;
        }

        .login-title {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .login-input {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }

        .login-button {
            padding: 10px;
            margin-top: 15px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .login-button:hover {
            background-color: #45a049;
        }

        .login-form p {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .login-form a {
            color: #007BFF;
            text-decoration: none;
        }

        .login-form a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body class="body-login">
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