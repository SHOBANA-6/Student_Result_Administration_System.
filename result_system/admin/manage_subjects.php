<?php
require '../php/db_connect.php';

// Security check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../admin_login.php");
    exit();
}

$message = '';
$error = '';
$edit_subject = null;

// Handle Edit Request (GET)
if (isset($_GET['edit_id'])) {
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $_GET['edit_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_subject = $result->fetch_assoc();
    }
}

// Handle Form Submissions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = htmlspecialchars($_POST['subject_name']);
    $subject_code = htmlspecialchars($_POST['subject_code']);

    //if (isset($_POST['add'])) {
      //  $stmt = $conn->prepare("INSERT INTO subjects (subject_name, subject_code) VALUES (?, ?)");
        //$stmt->bind_param("ss", $subject_name, $subject_code);
        //if ($stmt->execute()) {
          //  $message = "Subject added successfully!";
        //} else {
         //   $error = "Error: Could not add subject. Code might already exist.";
        //}
   // }
   
   if (isset($_POST['add'])) {
    // Check if subject_code already exists
    $check = $conn->prepare("SELECT * FROM subjects WHERE subject_code = ?");
    $check->bind_param("s", $subject_code);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error = "Error: Subject code already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name, subject_code) VALUES (?, ?)");
        $stmt->bind_param("ss", $subject_name, $subject_code);
        if ($stmt->execute()) {
            $message = "Subject added successfully!";
        } else {
            $error = "Error: Could not add subject.";
        }
    }
}

    elseif (isset($_POST['update'])) {
        $subject_id = $_POST['subject_id'];
        $stmt = $conn->prepare("UPDATE subjects SET subject_name = ?, subject_code = ? WHERE id = ?");
        $stmt->bind_param("ssi", $subject_name, $subject_code, $subject_id);
        if ($stmt->execute()) {
            $message = "Subject updated successfully!";
        } else {
            $error = "Error: Could not update subject.";
        }
    } elseif (isset($_POST['delete'])) {
        $subject_id = $_POST['subject_id'];
        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->bind_param("i", $subject_id);
        if ($stmt->execute()) {
            $message = "Subject deleted successfully!";
        } else {
            $error = "Error: Could not delete subject.";
        }
    }
}

// Fetch all subjects
$subjects_result = $conn->query("SELECT * FROM subjects ORDER BY subject_name ASC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="admin-header">
            <h1>Manage Subjects</h1>
            <div>
                <a href="admin_dashboard.php" class="btn">← Dashboard</a>
                <a href="logout.php" class="btn logout-btn">Logout</a>
            </div>
        </header>

        <div class="form-container manage-form">
            <h2><?php echo $edit_subject ? 'Edit Subject' : 'Add New Subject'; ?></h2>
            <?php if ($message): ?><p class="success-message"><?php echo $message; ?></p><?php endif; ?>
            <?php if ($error): ?><p class="error-message"><?php echo $error; ?></p><?php endif; ?>

            <form action="manage_subjects.php" method="POST">
                <input type="hidden" name="subject_id" value="<?php echo $edit_subject['id'] ?? ''; ?>">
                <div class="input-group">
                    <label for="subject_name">Subject Name</label>
                    <input type="text" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($edit_subject['subject_name'] ?? ''); ?>" required>
                </div>
                <div class="input-group">
                    <label for="subject_code">Subject Code</label>
                    <input type="text" id="subject_code" name="subject_code" value="<?php echo htmlspecialchars($edit_subject['subject_code'] ?? ''); ?>" required>
                </div>
                
                <?php if ($edit_subject): ?>
                    <button type="submit" name="update" class="btn">Update Subject</button>
                    <a href="manage_subjects.php" class="btn secondary-btn">Cancel Edit</a>
                <?php else: ?>
                    <button type="submit" name="add" class="btn">Add Subject</button>
                <?php endif; ?>
            </form>
        </div>

        <div class="data-table">
            <h2>Existing Subjects</h2>
            <table>
                <thead>
                    <tr>
                        <th>Subject Name</th>
                        <th>Subject Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $subjects_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                        <td class="actions">
                            <a href="manage_subjects.php?edit_id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                            <form action="manage_subjects.php" method="POST" style="display:inline;">
                                <input type="hidden" name="subject_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete" class="btn-delete" onclick="return confirm('Are you sure you want to delete this subject?');">Delete</button>
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