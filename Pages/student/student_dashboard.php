<?php
session_start();
include '../../database/connectDb.php';

// Check if logged in and role is student
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'student') {
    header("Location: /Go local/Pages/AuthPage/login.php");
    exit();
}

$studentId = $_SESSION['id'];

// Fetch student’s requests
$query = "SELECT o.id, o.reason, o.date_from, o.date_to, 
          a.warden_status, a.parent_status 
          FROM outpass_requests o
          LEFT JOIN approvals a ON o.id = a.request_id
          WHERE o.student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student_dashboard.css">
</head>

<body>
    <div class="container">
        <h1>Welcome, <?php echo $_SESSION['name']; ?>!</h1>
        <a href="new_request.php">+ New Outpass Request</a>
        <h2>Your Outpass Requests</h2>
        <table>
            <tr>
                <th>Reason</th>
                <th>Date From</th>
                <th>Date To</th>
                <th>Parent Status</th>
                <th>Warden Status</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                    <td><?php echo $row['date_from']; ?></td>
                    <td><?php echo $row['date_to']; ?></td>
                    <td><?php echo ucfirst($row['parent_status']); ?></td>
                    <td><?php echo ucfirst($row['warden_status']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <a href="../AuthPage/logout.php">Logout</a>
    </div>
</body>

</html>
<?php
$stmt->close();
$conn->close();
?>