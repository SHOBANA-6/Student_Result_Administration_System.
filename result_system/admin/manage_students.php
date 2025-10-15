<?php
require '../php/db_connect.php';

// Security check: Make sure an admin is logged in.
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../admin_login.php");
    exit();
}

$message = '';
$error = '';
$edit_student = null;

// Handle Edit Request (GET)
if (isset($_GET['edit_id'])) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE register_number = ?");
    $stmt->bind_param("i", $_GET['edit_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_student = $result->fetch_assoc();
    }
}

// Handle Form Submissions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $register_number = htmlspecialchars($_POST['register_number']);
    $student_name = htmlspecialchars($_POST['student_name']);
    $dob = $_POST['dob'];

    // Add or Update Action
    //if (isset($_POST['add'])) {
       // $stmt = $conn->prepare("INSERT INTO students (register_number, student_name, dob) VALUES (?, ?, ?)");
        //$stmt->bind_param("sss", $register_number, $student_name, $dob);
        //if ($stmt->execute()) {
          //  $message = "Student added successfully!";
        //} else {
          //  $error = "Error: Could not add student. Register number might already exist.";
        //}

        if (isset($_POST['add'])) {
    // Check if register number already exists
    $check = $conn->prepare("SELECT * FROM students WHERE register_number = ?");
    $check->bind_param("s", $register_number);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error = "Error: Register number already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO students (register_number, student_name, dob) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $register_number, $student_name, $dob);
        if ($stmt->execute()) {
            $message = "Student added successfully!";
        } else {
            $error = "Error: Could not add student.";
        }
    }
}

    } elseif (isset($_POST['update'])) {
        $student_id = $_POST['student_id'];
        $stmt = $conn->prepare("UPDATE students SET register_number = ?, student_name = ?, dob = ? WHERE id = ?");
        $stmt->bind_param("sssi", $register_number, $student_name, $dob, $student_id);
        if ($stmt->execute()) {
            $message = "Student updated successfully!";
        } else {
            $error = "Error: Could not update student.";
        }
    }
    // Delete Action
    elseif (isset($_POST['delete'])) {
        $student_id = $_POST['student_id'];
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        if ($stmt->execute()) {
            $message = "Student deleted successfully!";
        } else {
            $error = "Error: Could not delete student.";
        }
    }
    

// Fetch all students to display in the table
$students_result = $conn->query("SELECT * FROM students ORDER BY student_name ASC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="admin-header">
            <h1>Manage Students</h1>
            <div>
                <a href="admin_dashboard.php" class="btn">← Dashboard</a>
                <a href="logout.php" class="btn logout-btn">Logout</a>
            </div>
        </header>

        <div class="form-container manage-form">
            <h2><?php echo $edit_student ? 'Edit Student' : 'Add New Student'; ?></h2>
            <?php if ($message): ?><p class="success-message"><?php echo $message; ?></p><?php endif; ?>
            <?php if ($error): ?><p class="error-message"><?php echo $error; ?></p><?php endif; ?>

            <form action="manage_students.php" method="POST">
                <input type="hidden" name="student_id" value="<?php echo $edit_student['id'] ?? ''; ?>">
                <div class="input-group">
                    <label for="register_number">Register Number</label>
                    <input type="text" id="register_number" name="register_number" value="<?php echo htmlspecialchars($edit_student['register_number'] ?? ''); ?>" required>
                </div>
                <div class="input-group">
                    <label for="student_name">Student Name</label>
                    <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($edit_student['student_name'] ?? ''); ?>" required>
                </div>
                <div class="input-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" value="<?php echo $edit_student['dob'] ?? ''; ?>" required>
                </div>
                
                <?php if ($edit_student): ?>
                    <button type="submit" name="update" class="btn">Update Student</button>
                    <a href="manage_students.php" class="btn secondary-btn">Cancel Edit</a>
                <?php else: ?>
                    <button type="submit" name="add" class="btn">Add Student</button>
                <?php endif; ?>
            </form>
        </div>

        <div class="data-table">
            <h2>Existing Students</h2>
            <table>
                <thead>
                    <tr>
                        <th>Register Number</th>
                        <th>Student Name</th>
                        <th>Date of Birth</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $students_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['register_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td><?php echo $row['dob']; ?></td>
                        <td class="actions">
                            <a href="manage_students.php?edit_id=<?php echo $row['register_number']; ?>" class="btn-edit">Update</a>
                            <form action="manage_students.php" method="POST" style="display:inline;">
                                <input type="hidden" name="student_id" value="<?php echo $row['register_number']; ?>">
                                <button type="submit" name="delete" class="btn-delete" onclick="return confirm('Are you sure you want to delete this student? This will also delete all their results.');">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>