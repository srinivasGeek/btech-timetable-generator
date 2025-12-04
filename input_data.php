<?php
include 'includes/db_connect.php';
include 'includes/navbar.php';

$branches = ["CSE", "CSE(AIML)", "CSE(DS)", "CSE(CYBER)", "ECE", "MECH", "S&H"];
$semesters = ["I-I", "I-II", "II-I", "II-II", "III-I", "III-II", "IV-I", "IV-II"];
$available_sections = ["A", "B", "C", "D", "E"];
$msg = "";

// 1. ADD FACULTY
if (isset($_POST['add_faculty'])) {
    $sql = "INSERT INTO faculty (faculty_name, designation, department, availability) 
            VALUES ('$_POST[name]', '$_POST[desg]', '$_POST[dept]', '$_POST[avail]')";
    if($conn->query($sql)) $msg = "Faculty Added!";
}

// 2. ADD SUBJECT (With Custom Hours)
if (isset($_POST['add_subject'])) {
    $hours = $_POST['hours']; 
    $sql = "INSERT INTO subjects (subject_code, subject_name, course_type, semester, department, lecture_hours_per_week) 
            VALUES ('$_POST[code]', '$_POST[sub_name]', '$_POST[type]', '$_POST[sem]', '$_POST[dept]', '$hours')";
    if($conn->query($sql)) $msg = "Subject Added!";
}

// 3. ALLOTMENT (With Checkboxes)
if (isset($_POST['allot_subject'])) {
    if(!empty($_POST['sections'])) {
        foreach($_POST['sections'] as $sec) {
            // Check for duplicates before inserting
            $check = $conn->query("SELECT * FROM course_allotment WHERE subject_code='$_POST[sub_code]' AND section='$sec'");
            if($check->num_rows == 0) {
                $conn->query("INSERT INTO course_allotment (subject_code, faculty_id, section) VALUES ('$_POST[sub_code]', '$_POST[fac_id]', '$sec')");
            }
        }
        $msg = "Allocations Saved!";
    } else {
        $msg = "Please select at least one section.";
    }
}

// 4. DELETE ALLOTMENT
if (isset($_GET['del_id'])) {
    $conn->query("DELETE FROM course_allotment WHERE allotment_id='$_GET[del_id]'");
    header("Location: input_data.php");
}
?>

<!DOCTYPE html>
<html>
<head><title>Data Entry</title></head>
<body>
<div class="container">
    <?php if($msg): ?><div class="msg-success">✅ <?php echo $msg; ?></div><?php endif; ?>

    <div class="flex-row">
        <div class="flex-col box">
            <h3>1. Add Faculty</h3>
            <form method="post">
                <label>Name</label> <input type="text" name="name" required placeholder="Dr. Smith">
                <label>Designation</label> <input type="text" name="desg" placeholder="Professor">
                <label>Department</label> <select name="dept"><?php foreach($branches as $b) echo "<option value='$b'>$b</option>"; ?></select>
                <label>Availability</label> <select name="avail"><option value="ALL_DAY">All Day</option><option value="MORNING_ONLY">Morning Only</option></select>
                <button type="submit" name="add_faculty" class="btn btn-blue" style="width:100%">Save Faculty</button>
            </form>
        </div>

        <div class="flex-col box">
            <h3>2. Add Subject / Activity</h3>
            <form method="post">
                <label>Subject Code</label> 
                <input type="text" name="code" required placeholder="e.g. CS101">
                
                <label>Subject Name</label> 
                <input type="text" name="sub_name" required placeholder="e.g. Data Structures">
                
                <div style="display:flex; gap:10px;">
                    <div style="flex:1">
                        <label>Type</label> 
                        <select name="type">
                            <option value="THEORY">Theory</option>
                            <option value="LAB">Lab</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                    <div style="flex:1">
                        <label>Hours/Week</label> 
                        <input type="number" name="hours" min="1" max="10" value="4" required>
                    </div>
                </div>
                
                <label>Department</label> <select name="dept"><?php foreach($branches as $b) echo "<option value='$b'>$b</option>"; ?></select>
                <label>Semester</label> <select name="sem"><?php foreach($semesters as $sem) echo "<option value='$sem'>$sem</option>"; ?></select>

                <button type="submit" name="add_subject" class="btn btn-green" style="width:100%">Save Subject</button>
            </form>
        </div>
    </div>

    <div class="box" style="border-top-color: #fd7e14;">
        <h3>3. Link Faculty to Sections</h3>
        <form method="post">
            <div class="flex-row">
                <div class="flex-col">
                    <label>Subject / Activity</label>
                    <select name="sub_code">
                        <?php $res = $conn->query("SELECT * FROM subjects ORDER BY department, semester, subject_name");
                        while($row = $res->fetch_assoc()) echo "<option value='{$row['subject_code']}'>[{$row['department']} - {$row['semester']}] {$row['subject_name']} ({$row['lecture_hours_per_week']} hrs)</option>"; ?>
                    </select>
                </div>
                <div class="flex-col">
                    <label>Faculty</label>
                    <select name="fac_id">
                        <?php $res = $conn->query("SELECT * FROM faculty ORDER BY department, faculty_name");
                        while($row = $res->fetch_assoc()) echo "<option value='{$row['faculty_id']}'>{$row['faculty_name']}</option>"; ?>
                    </select>
                </div>
            </div>
            
            <label style="margin-top:15px;">Select Sections:</label>
            <div style="display:flex; gap:15px; margin-bottom:15px;">
                <?php foreach($available_sections as $s): ?>
                    <label style="font-weight:normal; background:#eee; padding:5px 15px; border-radius:15px; cursor:pointer;">
                        <input type="checkbox" name="sections[]" value="<?php echo $s; ?>"> Section <?php echo $s; ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" name="allot_subject" class="btn btn-orange">Link Faculty</button>
        </form>

        <h4 style="margin-top:20px; border-bottom:1px solid #ddd; padding-bottom:5px;">Current Allocations (Verify here):</h4>
        <table>
            <thead>
                <tr>
                    <th>Branch</th>
                    <th>Sem</th>
                    <th>Subject</th>
                    <th>Section</th>
                    <th>Faculty</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all allocations joined with subject/faculty details
                $sql = "SELECT ca.allotment_id, ca.section, s.subject_name, s.department, s.semester, f.faculty_name 
                        FROM course_allotment ca
                        JOIN subjects s ON ca.subject_code = s.subject_code
                        JOIN faculty f ON ca.faculty_id = f.faculty_id
                        ORDER BY s.department, s.semester, ca.section, s.subject_name";
                
                $res = $conn->query($sql);
                
                if($res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['department']}</td>";
                        echo "<td>{$row['semester']}</td>";
                        echo "<td>{$row['subject_name']}</td>";
                        echo "<td><strong style='color:#007bff;'>Section {$row['section']}</strong></td>";
                        echo "<td>{$row['faculty_name']}</td>";
                        echo "<td><a href='input_data.php?del_id={$row['allotment_id']}' style='color:red; font-weight:bold;'>Remove</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='color:#777; font-style:italic;'>No allocations found yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>