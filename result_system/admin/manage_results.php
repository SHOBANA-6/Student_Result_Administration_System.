<?php
session_start();
require '../php/db_connect.php';

// Security check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../admin_login.php");
    exit();
}

$message = '';
$error = '';
$edit_result = null;

// Fetch students and subjects for dropdowns
$students = $conn->query("SELECT register_number, student_name FROM students ORDER BY student_name");
$subjects = $conn->query("SELECT id, subject_name, subject_code FROM subjects ORDER BY subject_name");

// Handle Edit Request (GET)
//if (isset($_GET['edit_id'])) {
  //  $stmt = $conn->prepare("SELECT * FROM results WHERE id = ?");
    //$stmt->bind_param("i", $_GET['edit_id']);
    //$stmt->execute();
    //$result = $stmt->get_result();
    //if ($result->num_rows > 0) {
      //  $edit_result = $result->fetch_assoc();
    //}
//}
if (isset($_GET['edit_id'])) {
    $stmt = $conn->prepare("
        SELECT r.*, st.student_name, st.register_number, su.subject_name, su.subject_code
        FROM results r
        JOIN students st ON r.student_id = st.id
        JOIN subjects su ON r.subject_id = su.id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $_GET['edit_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_result = $result->fetch_assoc();
    }
}


// Handle Form Submissions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $result_id = $_POST['result_id'];
        $stmt = $conn->prepare("DELETE FROM results WHERE id = ?");
        $stmt->bind_param("i", $result_id);
        if ($stmt->execute()) {
            $message = "Result deleted successfully!";
        } else {
            $error = "Error deleting result.";
        }
    } else {
        $student_register = $_POST['student_register'];
        $subject_id = $_POST['subject_id'];
        $cia1 = $_POST['cia1'];
        $cia2 = $_POST['cia2'];
        $semester_exam = $_POST['semester_exam'];

        // ✅ Convert register_number → student_id
        $student_stmt = $conn->prepare("SELECT id FROM students WHERE register_number = ?");
        $student_stmt->bind_param("s", $student_register);
        $student_stmt->execute();
        $student_res = $student_stmt->get_result();
        if ($student_res->num_rows > 0) {
            $student_id = $student_res->fetch_assoc()['id'];
        } else {
            $error = "Invalid student selected.";
            $student_id = null;
        }

        if ($student_id) {
            if (isset($_POST['add'])) {
                // Check if result already exists
                $check_stmt = $conn->prepare("SELECT id FROM results WHERE student_id = ? AND subject_id = ?");
                $check_stmt->bind_param("ii", $student_id, $subject_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $error = "Result for this student and subject already exists. Please edit it instead.";
                } else {
                    $stmt = $conn->prepare("INSERT INTO results (student_id, subject_id, cia1, cia2, semester_exam) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("iiiii", $student_id, $subject_id, $cia1, $cia2, $semester_exam);
                    if ($stmt->execute()) {
                        $message = "Result added successfully!";
                        //header("Location: manage_results.php?success=1");
                        //exit();
                    } else {
                        $error = "Error adding result.";
                    }
                }
            } elseif (isset($_POST['update'])) {
                $result_id = $_POST['result_id'];
                $stmt = $conn->prepare("UPDATE results SET student_id = ?, subject_id = ?, cia1 = ?, cia2 = ?, semester_exam = ? WHERE id = ?");
                $stmt->bind_param("iiiiii", $student_id, $subject_id, $cia1, $cia2, $semester_exam, $result_id);
                if ($stmt->execute()) {
                    $message = "Result updated successfully!";
                } else {
                    $error = "Error updating result.";
                }
            }
        }
    }
}

// Fetch all results with student and subject names using JOIN
$results_query = "
    SELECT r.id, st.student_name, st.register_number, su.subject_name, 
           r.cia1, r.cia2, r.internal, r.semester_exam, r.total 
    FROM results r
    JOIN students st ON r.student_id = st.id
    JOIN subjects su ON r.subject_id = su.id
    ORDER BY st.student_name, su.subject_name
";
$results_data = $conn->query($results_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Results</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container" style="max-width: 1200px;">
    <header class="admin-header">
        <h1>Manage Results</h1>
        <div>
            <a href="admin_dashboard.php" class="btn">← Dashboard</a>
            <a href="logout.php" class="btn logout-btn">Logout</a>
        </div>
    </header>

    <div class="form-container manage-form">
        <h2><?php echo $edit_result ? 'Edit Result' : 'Add New Result'; ?></h2>
        <?php if ($message): ?><p class="success-message"><?php echo $message; ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error-message"><?php echo $error; ?></p><?php endif; ?>

        <form action="manage_results.php" method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="result_id" value="<?php echo $edit_result['id'] ?? ''; ?>">
           <!-- Student -->
<div class="input-group">
    <label for="student_register">Student</label>
    <?php if ($edit_result): ?>
        <input type="text" value="<?php echo htmlspecialchars($edit_result['student_name'] . ' (' . $edit_result['register_number'] . ')'); ?>" readonly>
        <input type="hidden" name="student_register" value="<?php echo $edit_result['register_number']; ?>">
    <?php else: ?>
        <select id="student_register" name="student_register" required>
            <option value="">-- Select Student --</option>
            <?php 
            $students->data_seek(0);
            while($student = $students->fetch_assoc()): ?>
                <option value="<?php echo $student['register_number']; ?>">
                    <?php echo htmlspecialchars($student['student_name'] . ' (' . $student['register_number'] . ')'); ?>
                </option>
            <?php endwhile; ?>
        </select>
    <?php endif; ?>
</div>

<!-- Subject -->
<div class="input-group">
    <label for="subject_id">Subject</label>
    <?php if ($edit_result): ?>
        <input type="text" value="<?php echo htmlspecialchars($edit_result['subject_name'] . ' (' . $edit_result['subject_code'] . ')'); ?>" readonly>
        <input type="hidden" name="subject_id" value="<?php echo $edit_result['subject_id']; ?>">
    <?php else: ?>
        <select id="subject_id" name="subject_id" required>
            <option value="">-- Select Subject --</option>
            <?php 
            $subjects->data_seek(0);
            while($subject = $subjects->fetch_assoc()): ?>
                <option value="<?php echo $subject['id']; ?>">
                    <?php echo htmlspecialchars($subject['subject_name'] . ' (' . $subject['subject_code'] . ')'); ?>
                </option>
            <?php endwhile; ?>
        </select>
    <?php endif; ?>
</div>



            <div class="input-group">
                <label for="cia1">CIA 1 (out of 25)</label>
                <input type="number" step="1" id="cia1" name="cia1" value="<?php echo $edit_result['cia1'] ?? ''; ?>" required>
            </div>
            <div class="input-group">
                <label for="cia2">CIA 2 (out of 25)</label>
                <input type="number" step="1" id="cia2" name="cia2" value="<?php echo $edit_result['cia2'] ?? ''; ?>" required>
            </div>
            <div class="input-group">
                <label for="semester_exam">Semester Exam (out of 75)</label>
                <input type="number" step="1" id="semester_exam" name="semester_exam" value="<?php echo $edit_result['semester_exam'] ?? ''; ?>" required>
            </div>

            <?php if ($edit_result): ?>
                <button type="submit" name="update" class="btn">Update Result</button>
                <a href="manage_results.php" class="btn secondary-btn">Cancel Edit</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn">Add Result</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="data-table">
        <h2>All Results</h2>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Subject</th>
                    <th>CIA 1</th>
                    <th>CIA 2</th>
                    <th>Internal</th>
                    <th>Semester</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $results_data->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_name'] . ' (' . $row['register_number'] . ')'); ?></td>
                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                    <td><?php echo round($row['cia1']); ?></td>
                    <td><?php echo round($row['cia2']); ?></td>
                    <td><?php echo round($row['internal']); ?></td>
                    <td><?php echo round($row['semester_exam']); ?></td>
                    <td><strong><?php echo round($row['total']); ?></strong></td>
                    <td class="actions">
                         <a href="manage_results.php?edit_id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                         <form action="manage_results.php" method="POST" style="display:inline;">
                             <input type="hidden" name="result_id" value="<?php echo $row['id']; ?>">
                             <button type="submit" name="delete" class="btn-delete" onclick="return confirm('Are you sure you want to delete this result?');">Delete</button>
                         </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function validateForm() {
    let cia1 = document.getElementById("cia1").value;
    let cia2 = document.getElementById("cia2").value;
    let sem  = document.getElementById("semester_exam").value;

    cia1 = parseInt(cia1);
    cia2 = parseInt(cia2);
    sem  = parseInt(sem);

    if (cia1 < 0 || cia1 > 25) {
        alert("CIA 1 marks must be between 0 and 25!");
        return false;
    }
    if (cia2 < 0 || cia2 > 25) {
        alert("CIA 2 marks must be between 0 and 25!");
        return false;
    }
    if (sem < 0 || sem > 75) {
        alert("Semester Exam marks must be between 0 and 75!");
        return false;
    }
    return true;
}
</script>

</body>
</html>
