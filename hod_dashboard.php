<?php
session_start();

// 1. SECURITY
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// 2. GET USER INFO
$my_dept_id = $_SESSION['dept_id'];
$my_name = $_SESSION['name'];
$msg = "";

// --- HANDLE RESET ACTIONS ---

// A. CLEAR GENERATED TIMETABLE
if (isset($_POST['clear_timetable'])) {
    $sql = "DELETE t FROM timetable t 
            JOIN subjects s ON t.subject_id = s.subject_id 
            WHERE s.dept_id = '$my_dept_id'";
    if($conn->query($sql)) {
        $msg = "Timetable data cleared successfully.";
    } else {
        $msg = "Error clearing timetable: " . $conn->error;
    }
}

// B. CLEAR SUBJECT ALLOTMENTS
if (isset($_POST['clear_allotment'])) {
    $sql = "DELETE FROM course_allotment WHERE dept_id = '$my_dept_id'";
    if($conn->query($sql)) {
        $msg = "All subject allotments cleared successfully.";
    } else {
        $msg = "Error clearing allotments: " . $conn->error;
    }
}

// C. CLEAR STAFF (Keep HOD)
if (isset($_POST['clear_staff'])) {
    $sql = "DELETE FROM faculty WHERE dept_id = '$my_dept_id' AND role = 'staff'";
    if($conn->query($sql)) {
        $msg = "All staff members deleted successfully.";
    } else {
        $msg = "Error clearing staff: " . $conn->error;
    }
}

// D. CLEAR SUBJECTS (NEW OPTION)
if (isset($_POST['clear_subjects'])) {
    // IMPORTANT: This also clears related timetable/allotment entries due to foreign key cascade.
    $sql = "DELETE FROM subjects WHERE dept_id = '$my_dept_id'";
    if($conn->query($sql)) {
        $msg = "All subject data cleared successfully.";
    } else {
        $msg = "Error clearing subjects: " . $conn->error;
    }
}

// ---------------------------

// 3. FETCH DATA (Same as before)
$dept_query = "SELECT dept_name, dept_code FROM departments WHERE dept_id = '$my_dept_id'";
$dept_result = $conn->query($dept_query);
$dept_row = $dept_result->fetch_assoc();
$dept_name = $dept_row['dept_name'];
$dept_code = $dept_row['dept_code'];

$sub_query = "SELECT COUNT(*) as total FROM subjects WHERE dept_id = '$my_dept_id'";
$subject_count = $conn->query($sub_query)->fetch_assoc()['total'];

$staff_query = "SELECT COUNT(*) as total FROM faculty WHERE dept_id = '$my_dept_id' AND role = 'staff'";
$staff_count = $conn->query($staff_query)->fetch_assoc()['total'];

$tt_check_sql = "SELECT COUNT(*) as total FROM timetable t JOIN subjects s ON t.subject_id = s.subject_id WHERE s.dept_id = '$my_dept_id'";
$tt_count = $conn->query($tt_check_sql)->fetch_assoc()['total'];

if ($tt_count > 0) {
    $status_text = "Generated";
    $status_color = "#27ae60"; 
    $status_sub = "$tt_count Classes Scheduled";
} else {
    $status_text = "Not Generated";
    $status_color = "#e74c3c"; 
    $status_sub = "System Empty";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HOD Dashboard</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        
        .sidebar { width: 250px; background-color: #34495e; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #2c3e50; margin: 0; font-size: 1.2rem;}
        .sidebar a { padding: 15px 20px; color: #bdc3c7; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar a:hover { background-color: #2c3e50; color: white; }
        .sidebar a.active { background-color: #1abc9c; color: white; } 
        
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        
        .welcome-banner { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        
        .cards-container { display: flex; gap: 20px; margin-top: 20px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex: 1; text-align: center; border-top: 4px solid #1abc9c; }
        .card h3 { margin: 0; color: #7f8c8d; font-size: 0.9rem; text-transform: uppercase; }
        .card p { margin: 10px 0 0; font-size: 2.5rem; font-weight: bold; color: #2c3e50; }
        .card small { color: #888; font-size: 0.9rem; }

        /* Danger Zone Styles */
        .danger-zone { margin-top: 40px; background: white; padding: 20px; border-radius: 8px; border: 1px solid #fab1a0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .danger-title { color: #c0392b; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; font-weight: bold; }
        .action-row { display: flex; gap: 20px; flex-wrap: wrap; }
        
        .btn-danger { background-color: #fff; color: #c0392b; border: 1px solid #c0392b; padding: 10px 15px; border-radius: 4px; cursor: pointer; transition: 0.3s; font-weight: bold; }
        .btn-danger:hover { background-color: #c0392b; color: white; }

        .msg-box { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2><?php echo $dept_code; ?> HOD</h2>
        <a href="hod_dashboard.php" class="active">Dashboard</a>
		<a href="hod_syllabus.php">Syllabus Files</a>
        <a href="hod_subjects.php">Manage Subjects</a>
        <a href="hod_staff.php">Manage Staff</a>
        <a href="hod_allotment.php">Course Allotment</a>
        <a href="generate_timetable.php">Generate Timetable</a>
        <a href="view_timetable.php">View Timetable</a>
        <a href="workload.php">Workload Report</a>
		<a href="faculty_timetable.php">Individual Timetable</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        
        <div class="welcome-banner">
            <div>
                <h1 style="margin:0;">Hello, <?php echo $my_name; ?></h1>
                <p style="margin:5px 0 0; color: #7f8c8d;">Head of Department: <b><?php echo $dept_name; ?></b></p>
            </div>
            <div>
                <span style="background: #e67e22; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem;">HOD ACCESS</span>
            </div>
        </div>

        <?php if($msg != ""): ?>
            <div style="margin-top:20px;" class="msg-box"><?php echo $msg; ?></div>
        <?php endif; ?>

        <div class="cards-container">
            <div class="card">
                <h3>Total Subjects</h3>
                <p><?php echo $subject_count; ?></p>
                <small>In Curriculum</small>
            </div>
            <div class="card">
                <h3>My Staff</h3>
                <p><?php echo $staff_count; ?></p>
                <small>Faculty Members</small>
            </div>
            <div class="card" style="border-top-color: <?php echo $status_color; ?>;">
                <h3>Timetable Status</h3>
                <p style="color: <?php echo $status_color; ?>; font-size: 1.8rem; margin-top: 15px;">
                    <?php echo $status_text; ?>
                </p>
                <small><?php echo $status_sub; ?></small>
            </div>
        </div>

        <div class="danger-zone">
            <div class="danger-title">⚠️ Data Reset & Management</div>
            <p style="font-size:0.9em; color:#555; margin-bottom:15px;">
                Use these options to reset data for a new semester. <b>These actions are destructive and department-specific.</b>
            </p>
            
            <div class="action-row">
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete the Generated Timetable?');">
                    <button type="submit" name="clear_timetable" class="btn-danger">🗑️ Clear Generated Timetable</button>
                </form>

                <form method="POST" onsubmit="return confirm('Are you sure you want to remove ALL Subject Allotments? Faculty will be unassigned.');">
                    <button type="submit" name="clear_allotment" class="btn-danger">🗑️ Clear Course Allotments</button>
                </form>

                <form method="POST" onsubmit="return confirm('WARNING: Deleting Subjects will also clear ALL Timetable/Allotment entries that rely on them. Proceed?');">
                    <button type="submit" name="clear_subjects" class="btn-danger">🗑️ Clear Subject Data</button>
                </form>

                <form method="POST" onsubmit="return confirm('WARNING: This will delete ALL Staff accounts in your department. Are you sure?');">
                    <button type="submit" name="clear_staff" class="btn-danger">🗑️ Delete All Staff</button>
                </form>
            </div>
        </div>

    </div>

</body>
</html>