<?php
session_start();
include '../../database/connectDb.php';

// Redirect if not admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../Pages/AuthPage/login.php");
    exit();
}

$roles = ['student', 'parent', 'warden', 'guard'];
$users = [];

foreach ($roles as $role) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $users[$role] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Handle adding users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_type'])) {
    if ($_POST['add_type'] === 'single') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();
        $stmt->close();

    } elseif ($_POST['add_type'] === 'student_parent') {
        $studentName = $_POST['student_name'];
        $studentEmail = $_POST['student_email'];
        $studentPass = $_POST['student_pass'];
        $parentName = $_POST['parent_name'];
        $parentEmail = $_POST['parent_email'];
        $parentPass = $_POST['parent_pass'];

        // Check if emails already exist
        $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ? OR email = ?");
        $checkEmail->bind_param("ss", $studentEmail, $parentEmail);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows == 0) {
            // Insert parent
            $insertParent = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'parent')");
            $insertParent->bind_param("sss", $parentName, $parentEmail, $parentPass);
            if ($insertParent->execute()) {
                $parentId = $insertParent->insert_id;
                // Insert student with parent_id
                $insertStudent = $conn->prepare("INSERT INTO users (name, email, password, role, parent_id) VALUES (?, ?, ?, 'student', ?)");
                $insertStudent->bind_param("sssi", $studentName, $studentEmail, $studentPass, $parentId);
                $insertStudent->execute();
                $insertStudent->close();
            }
            $insertParent->close();
        }
        $checkEmail->close();
    }
    header("Location: admin_dashboard.php");
    exit();
}

// Handle delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .sidebar button {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            color: #fff;
            border: none;
            cursor: pointer;
        } 
        .content {
            flex-grow: 1;
            padding: 20px;
        }
        .tab-content {
            display: none;
        }

        .add-form input, .add-form select {
            display: block;
            margin: 10px 0;
            padding: 8px;
            width: 100%;
        } 
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <button onclick="showTab('student')">Students</button>
            <button onclick="showTab('parent')">Parents</button>
            <button onclick="showTab('warden')">Wardens</button>
            <button onclick="showTab('guard')">Guards</button>
            <button onclick="showTab('add')">Add User</button>
        </aside>

        <main class="content">
            <?php foreach ($roles as $role): ?>
                <section id="<?php echo $role; ?>" class="tab-content">
                    <h2><?php echo ucfirst($role); ?>s</h2>
                    <table>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($users[$role] as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </section>
            <?php endforeach; ?>

            <section id="add" class="tab-content">
                <h2>Add User</h2>
                <form method="post" class="add-form">
                    <select name="add_type" id="add_type" onchange="toggleForm()" required>
                        <option value="">Select Add Type</option>
                        <option value="single">Single User</option>
                        <option value="student_parent">Student with Parent</option>
                    </select>

                    <div id="singleUserForm" style="display:none;">
                        <input type="text" name="name" placeholder="Name">
                        <input type="email" name="email" placeholder="Email">
                        <input type="password" name="password" placeholder="Password">
                        <select name="role">
                            <option value="student">Student</option>
                            <option value="parent">Parent</option>
                            <option value="warden">Warden</option>
                            <option value="guard">Guard</option>
                        </select>
                    </div>

                    <div id="studentParentForm" style="display:none;">
                        <h3>Student Info</h3>
                        <input type="text" name="student_name" placeholder="Student Name">
                        <input type="email" name="student_email" placeholder="Student Email">
                        <input type="password" name="student_pass" placeholder="Student Password">

                        <h3>Parent Info</h3>
                        <input type="text" name="parent_name" placeholder="Parent Name">
                        <input type="email" name="parent_email" placeholder="Parent Email">
                        <input type="password" name="parent_pass" placeholder="Parent Password">
                    </div>

                    <button type="submit">Add User</button>
                </form>
            </section>
        </main>
    </div>

    <script>
        function showTab(id) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
            document.getElementById(id).style.display = 'block';
        }
        function toggleForm() {
            const type = document.getElementById('add_type').value;
            document.getElementById('singleUserForm').style.display = (type === 'single') ? 'block' : 'none';
            document.getElementById('studentParentForm').style.display = (type === 'student_parent') ? 'block' : 'none';
        }
        showTab('student');
    </script>
</body>
</html>
