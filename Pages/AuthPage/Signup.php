<?php
include '../../database/connectDb.php';
$message = "";
$color = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentName = $_POST['student_name'];
    $studentEmail = $_POST['student_email'];
    $studentPass = $_POST['student_pass'];

    $parentName = $_POST['parent_name'];
    $parentEmail = $_POST['parent_email'];
    $parentPass = $_POST['parent_pass'];

    // Check if student or parent email already exists
    $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ? OR email = ?");
    $checkEmail->bind_param("ss", $studentEmail, $parentEmail);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        $message = "Email already exists!";
        $color = "#FFA500";
    } else {
        // First insert parent
        $insertParent = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'parent')");
        $insertParent->bind_param("sss", $parentName, $parentEmail, $parentPass);

        if ($insertParent->execute()) {
            $parentId = $insertParent->insert_id;

            // Then insert student with parent_id
            $insertStudent = $conn->prepare("INSERT INTO users (name, email, password, role, parent_id) VALUES (?, ?, ?, 'student', ?)");
            $insertStudent->bind_param("sssi", $studentName, $studentEmail, $studentPass, $parentId);
            $insertStudent->execute();

            $message = "Signup Successful!";
            $color = "#008000";
        } else {
            $message = "Error: " . $insertParent->error;
            $color = "#FF0000";
        }


        $insertStudent->close();
        $insertParent->close();
    }

    $checkEmail->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Signup Page</title>
    <link rel="stylesheet" href="signup.css">
</head>

<body class="body-signup">
    <h1>JUIT Outpass Management System</h1>
    <div class="signup-container">
        <form class="signup-form" method="post">
            <h1 class="signup-title">Student & Parent Signup</h1>

            <?php if ($message): ?>
                <p style="color: <?php echo $color; ?>;"><?php echo $message; ?></p>
            <?php endif; ?>

            <input type="text" name="student_name" placeholder="Student Name" required class="signup-input">
            <input type="email" name="student_email" placeholder="Student Email" required class="signup-input">
            <input type="password" name="student_pass" placeholder="Student Password" required class="signup-input">

            <h3>Parent Details</h3>
            <input type="text" name="parent_name" placeholder="Parent Name" required class="signup-input">
            <input type="email" name="parent_email" placeholder="Parent Email" required class="signup-input">
            <input type="password" name="parent_pass" placeholder="Parent Password" required class="signup-input">

            <button type="submit" class="signup-button">Sign Up</button>
            <p>Already registered? <a href="login.php">Login</a></p>
        </form>
    </div>
</body>

</html>