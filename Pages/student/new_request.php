<?php
session_start();
include '../../database/connectDb.php';

// Check if student is logged in
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../Pages/AuthPage/login.php");
    exit();
}

$message = "";
$color = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reason = $_POST['reason'];
    $date_from = $_POST['date_from'];
    $date_to = $_POST['date_to'];
    $student_id = $_SESSION['id'];

    $stmt = $conn->prepare("INSERT INTO outpass_requests (student_id, reason, date_from, date_to) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $student_id, $reason, $date_from, $date_to);

    if ($stmt->execute()) {
        // Also insert into approvals table
        $request_id = $stmt->insert_id;
        $conn->query("INSERT INTO approvals (request_id) VALUES ($request_id)");

        $message = "Outpass Request Submitted Successfully!";
        $color = "green";
    } else {
        $message = "Failed to submit request.";
        $color = "red";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>New Outpass Request</title>
    <link rel="stylesheet" href="new_request.css">
</head>

<body>
    <div class="form-container">
        <h2>Submit New Outpass Request</h2>

        <?php if ($message): ?>
            <p style="color: <?php echo $color; ?>;"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST">
            <textarea name="reason" placeholder="Enter reason" required></textarea>
            <input type="date" name="date_from" required>
            <input type="date" name="date_to" required>
            <button type="submit">Submit Request</button>
        </form>

        <a href="student_dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>

</html>