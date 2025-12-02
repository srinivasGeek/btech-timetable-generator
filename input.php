<?php
include 'includes/db_connect.php';
$msg = "";

// 1. Add Faculty
if (isset($_POST['add_faculty'])) {
    $sql = "INSERT INTO faculty (faculty_name, designation, department, availability) 
            VALUES ('$_POST[name]', '$_POST[desg]', '$_POST[dept]', '$_POST[avail]')";
    $conn->query($sql); $msg = "Faculty Added!";
}

// 2. Add Subject
if (isset($_POST['add_subject'])) {
    $hours = ($_POST['type'] == 'LAB') ? 3 : 4;
    $sql = "INSERT INTO subjects (subject_code, subject_name, course_type, semester, department, lecture_hours_per_week) 
            VALUES ('$_POST[code]', '$_POST[sub_name]', '$_POST[type]', '$_POST[sem]', '$_POST[dept]', '$hours')";
    $conn->query($sql); $msg = "Subject Added!";
}

// 3. Allotment (Link Faculty to Subject)
if (isset($_POST['allot_subject'])) {
    $sql = "INSERT INTO course_allotment (subject_code, faculty_id) VALUES ('$_POST[sub_code]', '$_POST[fac_id]')";
    $conn->query($sql); $msg = "Subject Allotted to Faculty!";
}
?>

<!DOCTYPE html>
<html>
<head><title>Input Data</title></head>
<body style="font-family: sans-serif; padding: 20px;">
    <h2>Data Entry (Current Status: <?php echo $msg; ?>)</h2>
    
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <h3>1. Add Faculty</h3>
        <form method="post">
            <input type="text" name="name" placeholder="Name" required>
            <select name="dept"><option>CSE</option><option>IT</option></select>
            <input type="text" name="desg" placeholder="Designation">
            <select name="avail">
                <option value="ALL_DAY">All Day</option>
                <option value="MORNING_ONLY">Morning Only (till 1:00 PM)</option>
            </select>
            <button type="submit" name="add_faculty">Add Faculty</button>
        </form>
    </div>

    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <h3>2. Add Subject</h3>
        <form method="post">
            <input type="text" name="code" placeholder="Code (e.g. CS101)" required>
            <input type="text" name="sub_name" placeholder="Subject Name" required>
            <select name="type"><option value="THEORY">Theory</option><option value="LAB">Lab (3 Hrs)</option></select>
            <select name="dept"><option>CSE</option><option>IT</option></select>
            <input type="number" name="sem" placeholder="Semester" value="1">
            <button type="submit" name="add_subject">Add Subject</button>
        </form>
    </div>

    <div style="border:1px solid #ccc; padding:10px; background:#eef;">
        <h3>3. Assign Subject to Faculty</h3>
        <form method="post">
            Subject: <select name="sub_code">
                <?php 
                $res = $conn->query("SELECT * FROM subjects");
                while($row = $res->fetch_assoc()) echo "<option value='{$row['subject_code']}'>{$row['subject_name']}</option>"; 
                ?>
            </select>
            Faculty: <select name="fac_id">
                <?php 
                $res = $conn->query("SELECT * FROM faculty");
                while($row = $res->fetch_assoc()) echo "<option value='{$row['faculty_id']}'>{$row['faculty_name']}</option>"; 
                ?>
            </select>
            <button type="submit" name="allot_subject">Link Them</button>
        </form>
    </div>
</body>
</html>