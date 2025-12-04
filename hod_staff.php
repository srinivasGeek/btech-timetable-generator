<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';
$my_dept_id = $_SESSION['dept_id'];
$my_id = $_SESSION['user_id'];

// 1. ADD STAFF WITH AVAILABILITY
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_staff'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $designation = $_POST['designation'];
    $availability = $_POST['availability']; // NEW FIELD

    $sql = "INSERT INTO faculty (name, email, password, role, dept_id, designation, availability) 
            VALUES ('$name', '$email', '$password', 'staff', '$my_dept_id', '$designation', '$availability')";

    if ($conn->query($sql) === TRUE) {
        $success = "Staff member added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// 2. DELETE STAFF
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id != $my_id) {
        $conn->query("DELETE FROM faculty WHERE faculty_id=$id AND dept_id=$my_dept_id");
        header("Location: hod_staff.php"); 
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Staff</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        .sidebar { width: 250px; background-color: #34495e; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #2c3e50; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #bdc3c7; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar a:hover { background-color: #2c3e50; color: white; }
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        input, select { padding: 10px; margin: 5px; border: 1px solid #ddd; border-radius: 4px; width: 200px; }
        button { padding: 10px 20px; background: #1abc9c; color: white; border: none; cursor: pointer; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #16a085; color: white; }
        .avail-morning { color: #d35400; font-weight: bold; }
        .avail-full { color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>HOD Panel</h2>
        <a href="hod_dashboard.php">Dashboard</a>
        <a href="hod_subjects.php">Manage Subjects</a>
        <a href="hod_staff.php" class="active">Manage Staff</a>
        <a href="generate_timetable.php">Generate Timetable</a>
        <a href="view_timetable.php">View Timetable</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>My Faculty Team</h1>

        <div class="container">
            <h3>Add New Faculty Member</h3>
            <?php if(isset($success)) echo "<p style='color:green'>$success</p>"; ?>
            
            <form method="POST">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Login Email" required>
                <input type="text" name="password" placeholder="Login Password" required>
                <input type="text" name="designation" placeholder="Designation" required>
                
                <select name="availability" required>
                    <option value="FULL_DAY">Full Day</option>
                    <option value="MORNING">Morning Only</option>
                </select>

                <button type="submit" name="add_staff">Add Staff</button>
            </form>
        </div>

        <div class="container">
            <h3>Department Faculty List</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Availability</th> <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM faculty WHERE dept_id = '$my_dept_id' ORDER BY role ASC, name ASC";
                    $result = $conn->query($sql);

                    while($row = $result->fetch_assoc()) {
                        $avail_display = ($row['availability'] == 'MORNING') 
                                        ? "<span class='avail-morning'>MORNING</span>" 
                                        : "<span class='avail-full'>FULL DAY</span>";

                        echo "<tr>";
                        echo "<td>" . $row['name'] . " " . ($row['role']=='hod'?'(HOD)':'') . "</td>";
                        echo "<td>" . $row['designation'] . "</td>";
                        echo "<td>" . $avail_display . "</td>";
                        echo "<td>";
                        if ($row['role'] != 'hod') {
                            echo "<a href='hod_staff.php?delete=" . $row['faculty_id'] . "' style='color:red;'>Remove</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>