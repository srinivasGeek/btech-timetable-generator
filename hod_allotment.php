<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';
$my_dept_id = $_SESSION['dept_id'];
$sections = ["A", "B", "C"];

// 1. HANDLE ALLOTMENT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_faculty'])) {
    $sub_id = $_POST['subject_id'];
    $fac_id = $_POST['faculty_id'];
    $sec = $_POST['section'];
    $room_id = $_POST['room_id']; // NEW INPUT

    $check = $conn->query("SELECT * FROM course_allotment WHERE subject_id='$sub_id' AND section='$sec'");
    
    if ($check->num_rows > 0) {
        $error = "Subject already assigned for Section $sec.";
    } else {
        // Insert with Room ID
        $sql = "INSERT INTO course_allotment (subject_id, faculty_id, section, dept_id, room_id) 
                VALUES ('$sub_id', '$fac_id', '$sec', '$my_dept_id', '$room_id')";
        if ($conn->query($sql)) {
            $success = "Allotment saved successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// 2. DELETE ALLOTMENT
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM course_allotment WHERE allotment_id=$id AND dept_id=$my_dept_id");
    header("Location: hod_allotment.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Allotment</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        .sidebar { width: 250px; background-color: #34495e; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #2c3e50; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #bdc3c7; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar a:hover { background-color: #2c3e50; color: white; }
        .active { background-color: #1abc9c; color: white; }
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        select, button { padding: 10px; margin: 5px; border-radius: 4px; border: 1px solid #ddd; width: 48%;}
        button { background: #27ae60; color: white; border: none; cursor: pointer; width: 100%; margin-top: 10px;}
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #8e44ad; color: white; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>HOD Panel</h2>
        <a href="hod_dashboard.php">Dashboard</a>
        <a href="hod_subjects.php">Manage Subjects</a>
        <a href="hod_staff.php">Manage Staff</a>
        <a href="hod_allotment.php" class="active">Course Allotment</a>
        <a href="generate_timetable.php">Generate Timetable</a>
        <a href="view_timetable.php">View Timetable</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Course Allotment</h1>
        <div class="container">
            <h3>Assign Faculty & Room</h3>
            <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
            <?php if(isset($success)) echo "<p style='color:green'>$success</p>"; ?>
            
            <form method="POST">
                <select name="subject_id" required>
                    <option value="">Select Subject</option>
                    <?php
                    $sub_res = $conn->query("SELECT * FROM subjects WHERE dept_id='$my_dept_id' ORDER BY semester ASC");
                    while($s = $sub_res->fetch_assoc()) {
                        $type = $s['is_lab'] ? "(LAB)" : "";
                        echo "<option value='".$s['subject_id']."'>".$s['subject_name']." $type</option>";
                    }
                    ?>
                </select>

                <select name="faculty_id" required>
                    <option value="">Select Faculty</option>
                    <?php
                    $fac_res = $conn->query("SELECT * FROM faculty WHERE dept_id='$my_dept_id'");
                    while($f = $fac_res->fetch_assoc()) {
                        echo "<option value='".$f['faculty_id']."'>".$f['name']."</option>";
                    }
                    ?>
                </select>

                <select name="section" required>
                    <option value="">Select Section</option>
                    <?php foreach($sections as $sec) echo "<option value='$sec'>Section $sec</option>"; ?>
                </select>

                <select name="room_id" required>
                    <option value="">Select Room / Lab</option>
                    <?php
                    // Display Rooms (Show Type to help HOD pick correct one)
                    $room_res = $conn->query("SELECT * FROM rooms ORDER BY room_type, room_no");
                    while($r = $room_res->fetch_assoc()) {
                        $rtype = ($r['room_type'] == 'lab') ? " [LAB]" : "";
                        echo "<option value='".$r['room_id']."'>".$r['room_no']."$rtype</option>";
                    }
                    ?>
                </select>

                <button type="submit" name="assign_faculty">Save Assignment</button>
            </form>
        </div>

        <div class="container">
            <h3>Current Assignments</h3>
            <table>
                <thead>
                    <tr>
                        <th>Sec</th>
                        <th>Subject</th>
                        <th>Faculty</th>
                        <th>Assigned Room</th> <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT ca.*, s.subject_name, f.name, r.room_no 
                            FROM course_allotment ca 
                            JOIN subjects s ON ca.subject_id = s.subject_id 
                            JOIN faculty f ON ca.faculty_id = f.faculty_id 
                            JOIN rooms r ON ca.room_id = r.room_id
                            WHERE ca.dept_id = '$my_dept_id' 
                            ORDER BY ca.section, s.subject_name";
                    $result = $conn->query($sql);

                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><b>" . $row['section'] . "</b></td>";
                        echo "<td>" . $row['subject_name'] . "</td>";
                        echo "<td>" . $row['name'] . "</td>";
                        echo "<td>" . $row['room_no'] . "</td>";
                        echo "<td><a href='hod_allotment.php?delete=" . $row['allotment_id'] . "' style='color:red;'>Remove</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>