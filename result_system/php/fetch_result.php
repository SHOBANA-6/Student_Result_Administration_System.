<?php
require 'db_connect.php';

if (isset($_POST['register_number']) && isset($_POST['dob'])) {
    $register_number = $_POST['register_number'];
    $dob = $_POST['dob'];

    // Step 1: Verify student and get ID
    $stmt = $conn->prepare("SELECT id, student_name, register_number FROM students WHERE register_number = ? AND dob = ?");
    $stmt->bind_param("ss", $register_number, $dob);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $student_id = $student['id']; // ✅ use id (not register number)

        // Step 2: Fetch results using student_id
        $result_stmt = $conn->prepare("
            SELECT s.subject_name, s.subject_code, r.cia1, r.cia2, r.internal, r.semester_exam, r.total
            FROM results r
            JOIN subjects s ON r.subject_id = s.id
            WHERE r.student_id = ?
        ");
        $result_stmt->bind_param("i", $student_id);
        $result_stmt->execute();
        $results_data = $result_stmt->get_result();

        if ($results_data->num_rows > 0) {
            echo "<h2>Results for {$student['student_name']} ({$register_number})</h2>";
            echo "<table border='1' cellpadding='8' cellspacing='0'>
                    <tr style='background:#f2f2f2;'>
                        <th>Subject Code</th>
                        <th>Subject</th>
                        <th>CIA1</th>
                        <th>CIA2</th>
                        <th>Internal</th>
                        <th>Semester Exam</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>";
            
            while ($row = $results_data->fetch_assoc()) {
                // ✅ Round off marks
                $cia1 = round($row['cia1']);
                $cia2 = round($row['cia2']);
                $internal = round($row['internal']);
                $semester_exam = round($row['semester_exam']);
                $total = round($row['total']);

                // ✅ Determine Pass/Fail
                $status = ($total >= 35) ? "<span style='color:green;font-weight:bold;'>Pass</span>" 
                                         : "<span style='color:red;font-weight:bold;'>Fail</span>";

                echo "<tr>
                        <td>{$row['subject_code']}</td>
                        <td>{$row['subject_name']}</td>
                        <td>{$cia1}</td>
                        <td>{$cia2}</td>
                        <td>{$internal}</td>
                        <td>{$semester_exam}</td>
                        <td>{$total}</td>
                        <td>{$status}</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<script>alert('No results found for this student.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Invalid Register Number or Date of Birth.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Please enter Register Number and Date of Birth.'); window.history.back();</script>";
}
?>
