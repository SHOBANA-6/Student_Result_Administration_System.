<?php
require '../php/db_connect.php';
// Security check: Make sure an admin is logged in.
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container admin-dashboard">
        <header>
            <h1>Admin Dashboard</h1>
            <a href="logout.php" class="btn logout-btn">Logout</a>
        </header>

        <div class="dashboard-menu">
            <div class="menu-item">
                <h3>Manage Students</h3>
                <p>Add, edit, or delete student records.</p>
                <a href="manage_students.php" class="btn">Go to Students</a>
            </div>
        
            <div class="menu-item">
                <h3>Manage Subjects</h3>
                <p>Add, edit, or delete subjects.</p><br>
                <a href="manage_subjects.php" class="btn">Go to Subjects</a>
            </div>
            <div class="menu-item">
                <h3>Manage Results</h3>
                <p>Add or update student results.</p>
                <a href="manage_results.php" class="btn">Go to Results</a>
            </div>
            <div class="menu-item">
                <h3>View Reports</h3>
                <p>Filter and view a complete list of all student results.</p>
                <a href="reports.php" class="btn">Go to Reports</a>
            </div>
        </div>
    </div>
</body>
</html>