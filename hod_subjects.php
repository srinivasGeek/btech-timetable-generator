<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';
$my_dept_id = $_SESSION['dept_id'];

// DEFINITIONS
$semesters = ["I-I", "I-II", "II-I", "II-II", "III-I", "III-II", "IV-I", "IV-II"];

// HANDLE ADD SUBJECT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subject'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $code = $conn->real_escape_string($_POST['code']);
    $semester = $_POST['semester']; // Now a string like "II-I"
    $hours = intval($_POST['hours']);
    $type = $_POST['type']; 
    $is_lab = ($type == 'lab') ? 1 : 0;

    $sql = "INSERT INTO subjects (subject_name, subject_code, semester, hours_per_week, is_lab, dept_id) 
            VALUES ('$name', '$code', '$semester', '$hours', '$is_lab', '$my_dept_id')";

    if ($conn->query($sql) === TRUE) {
        $success = "Subject added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM subjects WHERE subject_id=$id AND dept_id=$my_dept_id");
    header("Location: hod_subjects.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Subjects</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        .sidebar { width: 250px; background-color: #34495e; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #2c3e50; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #bdc3c7; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar a:hover { background-color: #2c3e50; color: white; }
        .sidebar a.active { background-color: #1abc9c; color: white; }
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        input, select { padding: 10px; margin: 5px; border: 1px solid #ddd; border-radius: 4px; width: 45%; }
        button { padding: 10px 20px; background: #1abc9c; color: white; border: none; cursor: pointer; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #1abc9c; color: white; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>HOD Panel</h2>
        <a href="hod_dashboard.php">Dashboard</a>
        <a href="hod_subjects.php" class="active">Manage Subjects</a>
        <a href="hod_staff.php">Manage Staff</a>
        <a href="generate_timetable.php">Generate Timetable</a>
        <a href="view_timetable.php">View Timetable</a> 
		<a href="faculty_timetable.php">Individual Timetable</a>
		<a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Curriculum Management</h1>

        <div class="container">
            <h3>Add New Subject</h3>
            <?php if(isset($success)) echo "<p style='color:green'>$success</p>"; ?>
            <form method="POST">
                <input type="text" name="name" placeholder="Subject Name" required>
                <input type="text" name="code" placeholder="Subject Code" required>
                <br>
                <select name="semester" required>
                    <option value="">Select Semester</option>
                    <?php foreach($semesters as $sem) echo "<option value='$sem'>$sem</option>"; ?>
                </select>

                <input type="number" name="hours" placeholder="Hours/Week (e.g. 4)" required>
                <br>
                <select name="type">
                    <option value="theory">Theory Class</option>
                    <option value="lab">Laboratory</option>
                </select>
                <button type="submit" name="add_subject">Add Subject</button>
            </form>
        </div>

        <div class="container">
            <h3>Subjects List</h3>
            <table>
                <thead>
                    <tr>
                        <th>Sem</th>
                        <th>Code</th>
                        <th>Subject Name</th>
                        <th>Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM subjects WHERE dept_id = '$my_dept_id' ORDER BY semester ASC");
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['semester'] . "</td>";
                        echo "<td>" . $row['subject_code'] . "</td>";
                        echo "<td>" . $row['subject_name'] . "</td>";
                        echo "<td>" . ($row['is_lab'] ? "LAB" : "THEORY") . "</td>";
                        echo "<td><a href='hod_subjects.php?delete=" . $row['subject_id'] . "' style='color:red;'>Delete</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>