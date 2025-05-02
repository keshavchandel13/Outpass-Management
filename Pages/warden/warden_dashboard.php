<?php
session_start();
include '../../database/connectDb.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'warden') {
    header("Location: ../../Pages/AuthPage/login.php");
    exit();
}

// Fetch all requests that are approved by parent but pending by warden
$query = $conn->prepare("
    SELECT r.*, u.name AS student_name, a.parent_status, a.warden_status 
    FROM outpass_requests r
    JOIN users u ON r.student_id = u.id
    JOIN approvals a ON r.id = a.request_id
    WHERE a.parent_status = 'approved'
    ORDER BY r.created_at DESC
");
$query->execute();
$requests = $query->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if (in_array($action, ['approved', 'declined'])) {
        $stmt = $conn->prepare("UPDATE approvals SET warden_status = ? WHERE request_id = ?");
        $stmt->bind_param("si", $action, $request_id);
        $stmt->execute();
        header("Location: warden_dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warden Dashboard</title>
    <link rel="stylesheet" href="warden_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome, <?php echo $_SESSION['name']; ?> (Warden)</h2>
        <h3>Pending Requests</h3>

        <?php if ($requests->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Reason</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Parent Status</th>
                        <th>Your Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $requests->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td><?php echo $row['date_from']; ?></td>
                            <td><?php echo $row['date_to']; ?></td>
                            <td><?php echo ucfirst($row['parent_status']); ?></td>
                            <td><?php echo ucfirst($row['warden_status']); ?></td>
                            <td>
                                <?php if ($row['warden_status'] === 'pending'): ?>
                                    <form method="post">
                                        <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="action" value="approved" class="approve">Approve</button>
                                        <button type="submit" name="action" value="declined" class="decline">Decline</button>
                                    </form>
                                <?php else: ?>
                                    <em>Responded</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending requests.</p>
        <?php endif; ?>
    </div>
</body>
</html>
