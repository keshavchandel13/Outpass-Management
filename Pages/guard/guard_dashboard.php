<?php
session_start();
include '../../database/connectDb.php';

// Redirect if not guard
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'guard') {
    header("Location: ../../Pages/AuthPage/login.php");
    exit();
}

$searchedStudent = null;

// Handle student search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_student'])) {
    $name = $_POST['student_name'];
    $stmt = $conn->prepare("
        SELECT u.name, u.email, o.reason, o.date_from, o.date_to, a.warden_status, a.parent_status
        FROM users u
        JOIN outpass_requests o ON u.id = o.student_id
        JOIN approvals a ON o.id = a.request_id
        WHERE u.name = ? AND u.role = 'student'
        ORDER BY o.created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $searchedStudent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get all students with approved or pending requests
$stmt = $conn->prepare("
    SELECT u.name, o.reason, o.date_from, o.date_to, a.warden_status, a.parent_status
    FROM users u
    JOIN outpass_requests o ON u.id = o.student_id
    JOIN approvals a ON o.id = a.request_id
    WHERE u.role = 'student'
    ORDER BY o.created_at DESC
");
$stmt->execute();
$allRequests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guard Dashboard</title>
    <link rel="stylesheet" href="guard.css">
</head>
<body>
    <h1>Guard Dashboard</h1>

    <form method="POST">
        <input type="text" name="student_name" placeholder="Enter Student Name" required>
        <button type="submit" name="search_student">Check Approval</button>
    </form>

    <?php if ($searchedStudent): ?>
        <h2>Approval Details for <?= htmlspecialchars($searchedStudent['name']) ?></h2>
        <table>
            <tr>
                <th>Email</th>
                <th>Reason</th>
                <th>Date From</th>
                <th>Date To</th>
                <th>Warden Status</th>
                <th>Parent Status</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars($searchedStudent['email']) ?></td>
                <td><?= htmlspecialchars($searchedStudent['reason']) ?></td>
                <td><?= htmlspecialchars($searchedStudent['date_from']) ?></td>
                <td><?= htmlspecialchars($searchedStudent['date_to']) ?></td>
                <td><?= htmlspecialchars($searchedStudent['warden_status']) ?></td>
                <td><?= htmlspecialchars($searchedStudent['parent_status']) ?></td>
            </tr>
        </table>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <p>No approval found for that student name.</p>
    <?php endif; ?>

    <h2>All Outpass Requests</h2>
    <table>
        <tr>
            <th>Student Name</th>
            <th>Reason</th>
            <th>Date From</th>
            <th>Date To</th>
            <th>Warden Status</th>
            <th>Parent Status</th>
        </tr>
        <?php foreach ($allRequests as $req): ?>
            <tr>
                <td><?= htmlspecialchars($req['name']) ?></td>
                <td><?= htmlspecialchars($req['reason']) ?></td>
                <td><?= htmlspecialchars($req['date_from']) ?></td>
                <td><?= htmlspecialchars($req['date_to']) ?></td>
                <td><?= htmlspecialchars($req['warden_status']) ?></td>
                <td><?= htmlspecialchars($req['parent_status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
