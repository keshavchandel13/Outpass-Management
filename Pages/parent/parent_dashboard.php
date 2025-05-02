<?php
session_start();
include '../../database/connectDb.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'parent') {
    header("Location: ../../Pages/AuthPage/login.php");
    exit();
}

$parent_id = $_SESSION['id'];


// Get linked student
$studentQuery = $conn->prepare("SELECT id, name FROM users WHERE parent_id = ?");
$studentQuery->bind_param("i", $parent_id);
$studentQuery->execute();
$studentResult = $studentQuery->get_result();

$student = $studentResult->fetch_assoc();

$requests = [];

if ($student) {
    $student_id = $student['id'];

    $query = $conn->prepare("
        SELECT r.*, a.parent_status, a.warden_status 
        FROM outpass_requests r
        JOIN approvals a ON r.id = a.request_id
        WHERE r.student_id = ?
        ORDER BY r.created_at DESC
    ");
    $query->bind_param("i", $student_id);
    $query->execute();
    $requests = $query->get_result();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if (in_array($action, ['approved', 'declined'])) {
        $stmt = $conn->prepare("UPDATE approvals SET parent_status = ? WHERE request_id = ?");
        $stmt->bind_param("si", $action, $request_id);
        $stmt->execute();
        header("Location: parent_dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parent Dashboard</title>
    <link rel="stylesheet" href="parent_dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        
     <h2>Welcome, <?php echo $_SESSION['name']; ?>!</h2>
        
        <?php if ($student): ?>
            <h3>Requests from: <?php echo htmlspecialchars($student['name']); ?></h3>

            <?php if ($requests->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Reason</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Warden Status</th>
                            <th>Your Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $requests->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                <td><?php echo $row['date_from']; ?></td>
                                <td><?php echo $row['date_to']; ?></td>
                                <td><?php echo ucfirst($row['warden_status']); ?></td>
                                <td><?php echo ucfirst($row['parent_status']); ?></td>
                                <td>
                                    <?php if ($row['parent_status'] === 'pending'): ?>
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
                <p>No requests yet.</p>
            <?php endif; ?>

        <?php else: ?>
            <p>No student linked to your account.</p>
        <?php endif; ?>
    </div>
</body>
</html>
