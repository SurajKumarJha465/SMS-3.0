<?php
session_start();
include("../db.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["role_id"] != 3 || !$_SESSION["is_approved"]) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Fetch student profile and metrics
$stmt = $conn->prepare("
    SELECT s.student_id, s.first_name, s.enrollment_date, u.email, q.attendance_percentage, q.current_gpa, q.pending_assignments, q.upcoming_events, q.total_credits, q.sgpa, q.cgpa
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    LEFT JOIN quick_view_metrics q ON s.student_id = q.student_id
    WHERE s.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch enrolled courses
$courses = $conn->query("
    SELECT c.course_id, c.course_code, c.course_name, c.credits, t.first_name AS instructor_name
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    JOIN teachers t ON c.instructor_id = t.teacher_id
    WHERE e.student_id = {$student['student_id']}
");

// Fetch upcoming assignments
$assignments = $conn->query("
    SELECT a.assignment_id, a.title, a.due_date, c.course_name, s.status
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    JOIN enrollments e ON c.course_id = e.course_id
    LEFT JOIN submissions s ON a.assignment_id = s.assignment_id AND s.student_id = {$student['student_id']}
    WHERE e.student_id = {$student['student_id']} AND a.due_date >= CURDATE()
");

// Fetch recent attendance (last 5 records)
$attendance = $conn->query("
    SELECT a.date, a.status, c.course_name
    FROM attendance a
    JOIN courses c ON a.course_id = c.course_id
    WHERE a.student_id = {$student['student_id']}
    ORDER BY a.date DESC
    LIMIT 5
");

// Fetch grades
$grades = $conn->query("
    SELECT c.course_name, r.internal_marks, r.external_marks, r.total_marks, r.letter_grade, r.grade_point, r.exam_type
    FROM results r
    JOIN courses c ON r.course_id = c.course_id
    WHERE r.student_id = {$student['student_id']}
");

// Fetch upcoming events
$events = $conn->query("
    SELECT title, event_date, venue
    FROM events
    WHERE event_date >= NOW()
    ORDER BY event_date
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h3><i class="fas fa-user-graduate"></i> Student Portal</h3>
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="profile_setup.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Section -->
            <div class="form-container">
                <h2><i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($student['first_name']); ?>!</h2>
                <p><strong>Student ID:</strong> <?php echo $student['student_id']; ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                <p><strong>Enrollment Date:</strong> <?php echo $student['enrollment_date'] ?: 'Not set'; ?></p>
            </div>

            <!-- Quick Metrics -->
            <div class="form-container">
                <h2><i class="fas fa-chart-line"></i> Quick Metrics</h2>
                <div class="metrics-grid">
                    <div class="metric-card">
                        <h4>Attendance</h4>
                        <p><?php echo $student['attendance_percentage'] ? number_format($student['attendance_percentage'], 2) . '%' : 'N/A'; ?></p>
                    </div>
                    <div class="metric-card">
                        <h4>Current GPA</h4>
                        <p><?php echo $student['current_gpa'] ? number_format($student['current_gpa'], 2) : 'N/A'; ?></p>
                    </div>
                    <div class="metric-card">
                        <h4>Pending Assignments</h4>
                        <p><?php echo $student['pending_assignments'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="metric-card">
                        <h4>Upcoming Events</h4>
                        <p><?php echo $student['upcoming_events'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="metric-card">
                        <h4>Total Credits</h4>
                        <p><?php echo $student['total_credits'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="metric-card">
                        <h4>SGPA</h4>
                        <p><?php echo $student['sgpa'] ? number_format($student['sgpa'], 2) : 'N/A'; ?></p>
                    </div>
                    <div class="metric-card">
                        <h4>CGPA</h4>
                        <p><?php echo $student['cgpa'] ? number_format($student['cgpa'], 2) : 'N/A'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Enrolled Courses -->
            <div class="form-container">
                <h2><i class="fas fa-book"></i> Enrolled Courses</h2>
                <?php if ($courses->num_rows > 0): ?>
                    <table>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Credits</th>
                            <th>Instructor</th>
                        </tr>
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['credits'] ?: 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($course['instructor_name']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p class="error">No courses enrolled.</p>
                <?php endif; ?>
            </div>

            <!-- Upcoming Assignments -->
            <div class="form-container">
                <h2><i class="fas fa-tasks"></i> Upcoming Assignments</h2>
                <?php if ($assignments->num_rows > 0): ?>
                    <table>
                        <tr>
                            <th>Title</th>
                            <th>Course</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                        <?php while ($assignment = $assignments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                                <td><?php echo $assignment['due_date']; ?></td>
                                <td>
                                    <?php echo $assignment['status'] ?: '<a href="submit_assignment.php?id=' . $assignment['assignment_id'] . '">Submit</a>'; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p class="error">No upcoming assignments.</p>
                <?php endif; ?>
            </div>

            <!-- Recent Attendance -->
            <div class="form-container">
                <h2><i class="fas fa-calendar-check"></i> Recent Attendance</h2>
                <?php if ($attendance->num_rows > 0): ?>
                    <table>
                        <tr>
                            <th>Course</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                        <?php while ($record = $attendance->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['course_name']); ?></td>
                                <td><?php echo $record['date']; ?></td>
                                <td><?php echo $record['status']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p class="error">No recent attendance records.</p>
                <?php endif; ?>
            </div>

            <!-- Grades Overview -->
            <div class="form-container">
                <h2><i class="fas fa-graduation-cap"></i> Grades</h2>
                <?php if ($grades->num_rows > 0): ?>
                    <table>
                        <tr>
                            <th>Course</th>
                            <th>Exam Type</th>
                            <th>Internal Marks</th>
                            <th>External Marks</th>
                            <th>Total Marks</th>
                            <th>Letter Grade</th>
                            <th>Grade Point</th>
                        </tr>
                        <?php while ($grade = $grades->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                <td><?php echo $grade['exam_type']; ?></td>
                                <td><?php echo $grade['internal_marks'] ? number_format($grade['internal_marks'], 2) : 'N/A'; ?></td>
                                <td><?php echo $grade['external_marks'] ? number_format($grade['external_marks'], 2) : 'N/A'; ?></td>
                                <td><?php echo $grade['total_marks'] ? number_format($grade['total_marks'], 2) : 'N/A'; ?></td>
                                <td><?php echo $grade['letter_grade'] ?: 'N/A'; ?></td>
                                <td><?php echo $grade['grade_point'] ? number_format($grade['grade_point'], 2) : 'N/A'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p class="error">No grades available.</p>
                <?php endif; ?>
            </div>

            <!-- Upcoming Events -->
            <div class="form-container">
                <h2><i class="fas fa-calendar-alt"></i> Upcoming Events</h2>
                <?php if ($events->num_rows > 0): ?>
                    <table>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Venue</th>
                        </tr>
                        <?php while ($event = $events->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo $event['event_date']; ?></td>
                                <td><?php echo htmlspecialchars($event['venue']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p class="error">No upcoming events.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>