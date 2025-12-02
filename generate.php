<?php
include 'includes/db_connect.php';
include 'includes/navbar.php';

$branches = ["CSE", "CSE(AIML)", "CSE(DS)", "CSE(CYBER)", "ECE", "MECH", "S&H"];
$sections = ["A", "B", "C", "D", "E"];
$msg = "";

if (isset($_POST['generate'])) {
    
    $target_dept = $_POST['dept_filter'];
    $target_sec  = $_POST['sec_filter'];

    // 1. CLEANUP
    $sql_clean = "DELETE g FROM generated_timetable g 
                  JOIN subjects s ON g.subject_code = s.subject_code 
                  WHERE 1=1";
    if ($target_dept != 'ALL') $sql_clean .= " AND s.department = '$target_dept'";
    if ($target_sec != 'ALL') $sql_clean .= " AND g.section = '$target_sec'";
    
    if ($target_dept == 'ALL' && $target_sec == 'ALL') {
        $conn->query("TRUNCATE TABLE generated_timetable");
    } else {
        $conn->query($sql_clean);
    }
    $msg = "Generated with Load Balancing for $target_dept ($target_sec).";

    // 2. FETCH
    $sql_fetch = "SELECT ca.allotment_id, ca.subject_code, ca.faculty_id, ca.section, 
                         s.course_type, s.lecture_hours_per_week, s.department,
                         f.faculty_name, f.availability 
                  FROM course_allotment ca
                  JOIN subjects s ON ca.subject_code = s.subject_code
                  JOIN faculty f ON ca.faculty_id = f.faculty_id
                  WHERE 1=1";

    if ($target_dept != 'ALL') $sql_fetch .= " AND s.department = '$target_dept'";
    if ($target_sec != 'ALL')  $sql_fetch .= " AND ca.section = '$target_sec'";

    $sql_fetch .= " ORDER BY s.course_type ASC, s.lecture_hours_per_week DESC";
    $result = $conn->query($sql_fetch);
    runScheduler($conn, $result);
}

function runScheduler($conn, $result) {
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    
    while ($row = $result->fetch_assoc()) {
        $hours_needed = $row['lecture_hours_per_week']; 
        if($hours_needed == 0) $hours_needed = ($row['course_type'] == 'LAB') ? 3 : 4;
        
        $hours_assigned = 0;
        $dept = $row['department'];
        $sec = $row['section'];
        $fac_id = $row['faculty_id'];

        // --- LAB LOGIC (Unchanged - Labs are hard constraints) ---
        if ($row['course_type'] == 'LAB') {
            foreach ($days as $day) {
                if ($hours_assigned >= $hours_needed) break;
                // Labs still prefer morning/afternoon blocks, so we don't shuffle slots here
                foreach ([1, 2, 5] as $start) {
                    if ($hours_assigned >= $hours_needed) break;
                    if ($start == 5 && $row['availability'] == 'MORNING_ONLY') continue;

                    // Lab Capacity Check (Max 2)
                    if (!checkLabCapacity($conn, $day, $start, $dept) || 
                        !checkLabCapacity($conn, $day, $start+1, $dept) || 
                        !checkLabCapacity($conn, $day, $start+2, $dept)) continue;

                    if (isSlotFree($conn, $day, $start, $fac_id, $dept, $sec) &&
                        isSlotFree($conn, $day, $start+1, $fac_id, $dept, $sec) &&
                        isSlotFree($conn, $day, $start+2, $fac_id, $dept, $sec)) {
                        
                        bookSlot($conn, $day, $start, $row, "Lab");
                        bookSlot($conn, $day, $start+1, $row, "Lab");
                        bookSlot($conn, $day, $start+2, $row, "Lab");
                        $hours_assigned += 3;
                        break;
                    }
                }
            }
        } 
        // --- THEORY LOGIC (UPDATED: SPREAD STRATEGY) ---
        else {
            shuffle($days); // Randomize Days
            
            // Define Slots 1 to 7
            $theory_slots = [1, 2, 3, 4, 5, 6, 7];

            // PASS 1: The "Fair" Pass
            // We SHUFFLE slots so we don't always pick Slot 1 first
            shuffle($theory_slots); 

            foreach ($days as $day) {
                if ($hours_assigned >= $hours_needed) break;
                
                foreach ($theory_slots as $slot) {
                    // FATIGUE CHECK:
                    // If Slot is 1 (9:20 AM), check if Prof already has 2 or more Slot 1s this week.
                    // If yes, skip this slot and look for a later one.
                    if ($slot == 1) {
                        $fatigue = getSlot1Count($conn, $fac_id);
                        if ($fatigue >= 2) continue; // "Too many mornings! Skip."
                    }

                    if (isValidTheorySlot($conn, $day, $slot, $row, $dept, $sec)) {
                        bookSlot($conn, $day, $slot, $row, "Classroom");
                        $hours_assigned++;
                        break; // Move to next day (Spread 1 per day)
                    }
                }
            }
            
            // PASS 2: The "Force" Pass
            // If we failed to book because we were being too nice, just book anywhere available
            if ($hours_assigned < $hours_needed) {
                foreach ($days as $day) {
                    if ($hours_assigned >= $hours_needed) break;
                    // Reset slot order for second pass (ordered is fine here)
                    for ($slot=1; $slot<=7; $slot++) {
                        if ($hours_assigned >= $hours_needed) break;
                        if (isValidTheorySlot($conn, $day, $slot, $row, $dept, $sec)) {
                            bookSlot($conn, $day, $slot, $row, "Classroom");
                            $hours_assigned++;
                        }
                    }
                }
            }
        }
    }
}

// --- NEW HELPER: Count how many 9:20 AM classes a prof has ---
function getSlot1Count($conn, $fac_id) {
    $sql = "SELECT COUNT(*) as c FROM generated_timetable 
            WHERE faculty_id = '$fac_id' AND slot_id = 1";
    return $conn->query($sql)->fetch_assoc()['c'];
}

function checkLabCapacity($conn, $day, $slot, $dept) {
    $sql = "SELECT COUNT(*) as c FROM generated_timetable g
            JOIN subjects s ON g.subject_code = s.subject_code
            WHERE g.day = '$day' AND g.slot_id = '$slot' AND s.department = '$dept' AND s.course_type = 'LAB'";
    $count = $conn->query($sql)->fetch_assoc()['c'];
    return ($count < 2);
}

function isValidTheorySlot($conn, $day, $slot, $row, $dept, $sec) {
    if ($slot > 4 && $row['availability'] == 'MORNING_ONLY') return false;
    return isSlotFree($conn, $day, $slot, $row['faculty_id'], $dept, $sec);
}

function isSlotFree($conn, $day, $slot, $fac_id, $dept, $sec) {
    $check_fac = $conn->query("SELECT * FROM generated_timetable WHERE day='$day' AND slot_id='$slot' AND faculty_id='$fac_id'");
    $check_batch = $conn->query("SELECT * FROM generated_timetable g JOIN subjects s ON g.subject_code = s.subject_code WHERE g.day='$day' AND g.slot_id='$slot' AND s.department='$dept' AND g.section='$sec'");
    return ($check_fac->num_rows == 0 && $check_batch->num_rows == 0);
}

function bookSlot($conn, $day, $slot, $data, $room) {
    $sub = $data['subject_code'];
    $fac = $data['faculty_id'];
    $sec = $data['section'];
    $conn->query("INSERT INTO generated_timetable (day, slot_id, subject_code, faculty_id, room_no, section) VALUES ('$day', '$slot', '$sub', '$fac', '$room', '$sec')");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generator Control</title>
    <style>
        .control-panel { max-width: 600px; margin: 30px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; border-top: 5px solid #007bff; }
        select { padding: 10px; font-size: 16px; border-radius: 4px; margin: 5px; }
        .btn-gen { padding: 12px 25px; font-size: 18px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin-top: 20px; }
        .success-msg { color: green; font-weight: bold; margin-bottom: 20px; }
    </style>
</head>
<body style="background-color: #f4f4f9; font-family: sans-serif;">
    <div class="control-panel">
        <h2>⚙️ Generator (Smart Spread)</h2>
        <?php if($msg) echo "<div class='success-msg'>✅ $msg</div>"; ?>
        <form method="post">
            <label>Department:</label><br>
            <select name="dept_filter"><option value="ALL">-- ALL DEPARTMENTS --</option><?php foreach($branches as $b) echo "<option value='$b'>$b</option>"; ?></select><br><br>
            <label>Section:</label><br>
            <select name="sec_filter"><option value="ALL">-- ALL SECTIONS --</option><?php foreach($sections as $s) echo "<option value='$s'>Section $s</option>"; ?></select><br>
            <button type="submit" name="generate" class="btn-gen">Run Generator</button>
        </form>
        <br><hr><a href="view_timetable.php" style="text-decoration:none; color:#555;">View Results &rarr;</a>
    </div>
</body>
</html>