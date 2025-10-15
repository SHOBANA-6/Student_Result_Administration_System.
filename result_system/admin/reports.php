<?php
require '../php/db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../admin_login.php");
    exit();
}

// Fetch subjects for dropdown
$subjects = $conn->query("SELECT id, subject_name, subject_code FROM subjects ORDER BY subject_name");

// Base query
$query = "
    SELECT 
        st.register_number, st.student_name, 
        su.subject_code, su.subject_name, 
        r.cia1, r.cia2, r.internal, r.semester_exam, r.total
    FROM results r
    JOIN students st ON r.student_id = st.id
    JOIN subjects su ON r.subject_id = su.id
";

// Filtering logic
$where_clauses = [];
$params = [];
$types = '';

// Subject filter
if (!empty($_GET['subject_id'])) {
    $where_clauses[] = "r.subject_id = ?";
    $params[] = $_GET['subject_id'];
    $types .= 'i';
}

// Register Number filter
if (!empty($_GET['register_number'])) {
    $where_clauses[] = "st.register_number LIKE ?";
    $params[] = '%' . $_GET['register_number'] . '%';
    $types .= 's';
}

// Pass/Fail filter
$pass_fail = $_GET['pass_fail'] ?? '';
if ($pass_fail === 'pass') {
    $where_clauses[] = "r.total >= 50";
} elseif ($pass_fail === 'fail') {
    $where_clauses[] = "r.total < 50";
}

// Append WHERE clauses
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

$query .= " ORDER BY st.register_number, su.subject_name";

// Prepare and execute
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$results_data = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Results Report</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container" style="max-width: 1200px;">
    <header class="admin-header">
        <h1>Results Report</h1>
        <div>
            <a href="admin_dashboard.php" class="btn">← Dashboard</a>
            <a href="logout.php" class="btn logout-btn">Logout</a>
        </div>
    </header>

    <div class="filter-container">
        <h3>Filter Results</h3>
        <form action="reports.php" method="GET" class="filter-form">
            <div class="filter-group">
                <label for="subject_id">Subject:</label>
                <select name="subject_id" id="subject_id">
                    <option value="">All Subjects</option>
                    <?php while($subject = $subjects->fetch_assoc()): ?>
                        <option value="<?php echo $subject['id']; ?>" <?php echo (isset($_GET['subject_id']) && $_GET['subject_id'] == $subject['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['subject_name'] . ' (' . $subject['subject_code'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="register_number">Register Number:</label>
                <input type="text" name="register_number" id="register_number" value="<?php echo htmlspecialchars($_GET['register_number'] ?? ''); ?>" placeholder="Enter Reg No.">
            </div>

            <div class="filter-group">
                <label for="pass_fail">Pass/Fail:</label>
                <select name="pass_fail" id="pass_fail">
                    <option value="">All</option>
                    <option value="pass" <?php echo (isset($_GET['pass_fail']) && $_GET['pass_fail']=='pass')?'selected':''; ?>>Pass</option>
                    <option value="fail" <?php echo (isset($_GET['pass_fail']) && $_GET['pass_fail']=='fail')?'selected':''; ?>>Fail</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn">Apply Filters</button>
                <a href="reports.php" class="btn secondary-btn">Reset</a>
            </div>
        </form>
    </div>

    <div class="data-table">
        <h2>Report Data</h2>
        <table>
            <thead>
                <tr>
                    <th>Register No.</th>
                    <th>Student Name</th>
                    <th>Subject</th>
                    <th>CIA 1</th>
                    <th>CIA 2</th>
                    <th>Internal</th>
                    <th>Semester</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results_data->num_rows > 0): ?>
                    <?php while($row = $results_data->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['register_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                            <td><?php echo $row['cia1']; ?></td>
                            <td><?php echo $row['cia2']; ?></td>
                            <td><?php echo $row['internal']; ?></td>
                            <td><?php echo $row['semester_exam']; ?></td>
                            <td><strong><?php echo $row['total']; ?></strong></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">No results found for the selected filters.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
