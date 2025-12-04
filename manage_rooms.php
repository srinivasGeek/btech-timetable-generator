<?php
session_start();

// 1. SECURITY: Only Admin Allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// 2. HANDLE ADD ROOM
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $room_no = $conn->real_escape_string($_POST['room_no']);
    $capacity = intval($_POST['capacity']);
    $type = $_POST['type']; // 'classroom' or 'lab'

    $sql = "INSERT INTO rooms (room_no, capacity, room_type) VALUES ('$room_no', '$capacity', '$type')";

    if ($conn->query($sql) === TRUE) {
        $success = "Room added successfully!";
    } else {
        $error = "Error: " . $conn->error; // Likely duplicate room number
    }
}

// 3. HANDLE DELETE ROOM
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM rooms WHERE room_id=$id");
    header("Location: manage_rooms.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rooms</title>
    <style>
        /* Shared Admin CSS */
        body { margin: 0; font-family: sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #1a252f; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #adb5bd; text-decoration: none; border-bottom: 1px solid #34495e; }
        .sidebar a:hover { background-color: #34495e; color: white; }
        
        .main-content { flex: 1; padding: 20px; overflow-y: auto; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        
        input, select { padding: 10px; margin: 5px; border: 1px solid #ddd; border-radius: 4px; width: 200px; }
        button { padding: 10px 20px; background: #27ae60; color: white; border: none; cursor: pointer; border-radius: 4px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #2c3e50; color: white; }
        
        .badge-lab { background: #e74c3c; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; }
        .badge-class { background: #3498db; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Timetable Admin</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_departments.php">Manage Departments</a>
        <a href="manage_faculty.php">Manage Faculty (HODs)</a>
        <a href="manage_rooms.php" style="background-color: #2980b9; color: white;">Manage Rooms</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Infrastructure Management</h1>

        <div class="container">
            <h3>Add New Room / Lab</h3>
            <?php 
            if(isset($success)) echo "<p style='color:green'>$success</p>";
            if(isset($error)) echo "<p style='color:red'>$error</p>";
            ?>
            
            <form method="POST">
                <input type="text" name="room_no" placeholder="Room Number (e.g. 101, Lab-A)" required>
                <input type="number" name="capacity" placeholder="Capacity (e.g. 60)" required>
                <select name="type">
                    <option value="classroom">Theory Classroom</option>
                    <option value="lab">Laboratory</option>
                </select>
                <button type="submit" name="add_room">Add Room</button>
            </form>
        </div>

        <div class="container">
            <h3>Existing Rooms</h3>
            <table>
                <thead>
                    <tr>
                        <th>Room No</th>
                        <th>Type</th>
                        <th>Capacity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM rooms ORDER BY room_no ASC");
                    while ($row = $result->fetch_assoc()) {
                        $badge = ($row['room_type'] == 'lab') 
                            ? "<span class='badge-lab'>LAB</span>" 
                            : "<span class='badge-class'>THEORY</span>";
                        
                        echo "<tr>";
                        echo "<td><b>" . $row['room_no'] . "</b></td>";
                        echo "<td>" . $badge . "</td>";
                        echo "<td>" . $row['capacity'] . " Students</td>";
                        echo "<td><a href='manage_rooms.php?delete=" . $row['room_id'] . "' style='color:red; text-decoration:none;' onclick='return confirm(\"Delete this room?\")'>Delete</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>