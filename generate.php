<?php
include 'includes/db_connect.php';
include 'includes/navbar.php';

$branches = ["CSE", "CSE(AIML)", "CSE(DS)", "CSE(CYBER)", "ECE", "MECH", "S&H"];
$semesters = ["I-I", "I-II", "II-I", "II-II", "III-I", "III-II", "IV-I", "IV-II"];
$sections = ["A", "B", "C", "D", "E"];
$msg = "";

if (isset($_POST['generate'])) {
    
    $target_dept = $_POST['dept_filter'];
    $target_sec  = $_POST['sec_filter'];
    $target_sem  = $_POST['sem_filter'];

    // 1. CLEANUP PREVIOUS DATA
    // We only wipe the specific batch we are generating for.
    // We DO NOT wipe other departments, so their bookings remain as "Blockers" for conflicts.
    $sql_clean = "DELETE g FROM generated_timetable g 
                  JOIN subjects s ON g.subject_code = s.subject_code 
                  WHERE 1=1";
    
    if ($target_dept != 'ALL') $sql_clean .= " AND s.department = '$target_dept'";
    if ($target_sec != 'ALL')  $sql_clean .= " AND g.section = '$target_sec'";
    if ($target_sem != 'ALL')  $sql_clean .= " AND s.semester = '$target_sem'";

    // If "ALL" is selected, we wipe the slate clean to start fresh
    if ($target_dept == 'ALL' && $target_sec == 'ALL' && $target_sem == 'ALL') {
        $conn->query("TRUNCATE TABLE generated_timetable");
    } else {
        $conn->query($sql_clean);
    }
    
    $msg = "Generated for $target_dept ($target_sem) - Sec $target_sec.";

    // 2. FETCH ALLOTMENTS
    $sql_fetch = "SELECT ca.allotment_id, ca.subject_code, ca.faculty_id, ca.section, 
                         s.course_type, s.lecture_hours_per_week, s.department, s.semester,
                         f.faculty_name, f.availability 
                  FROM course_allotment ca
                  JOIN subjects s ON ca.subject_code = s.subject_code
                  JOIN faculty f ON ca.faculty_id = f.faculty_id
                  WHERE 1=1";

    if ($target_dept != 'ALL') $sql_fetch .= " AND s.department = '$target_dept'";
    if ($target_sec != 'ALL')  $sql_fetch .= " AND ca.section = '$target_sec'";
    if ($target_sem != 'ALL')  $sql_fetch .= " AND s.semester = '$target_sem'";

    // IMPORTANT: Labs first, then Theory
    $sql_fetch .= " ORDER BY s.course_type ASC, s.lecture_hours_per_week DESC";
    
    $result = $conn->query($sql_fetch);
    runScheduler($conn, $result, $target_sem); // Pass Semester for batch checking
}

// --- CORE ALGORITHM ---
function runScheduler($conn, $result, $target_sem) {
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    
    while ($row = $result->fetch_assoc()) {
        $hours_needed = $row['lecture_hours_per_week']; 
        if($hours_needed == 0) $hours_needed = ($row['course_type'] == 'LAB') ? 3 : 4;
        
        $hours_assigned = 0;
        $dept = $row['department'];
        $sem  = $row['semester']; 
        $sec  = $row['section'];
        $fac_id = $row['faculty_id'];

        // --- LAB LOGIC ---
        if ($row['course_type'] == 'LAB') {
            foreach ($days as $day) {
                if ($hours_assigned >= $hours_needed) break;
                // Labs prefer blocks starting at 1, 2, or 5
                foreach ([1, 2, 5] as $start) {
                    if ($hours_assigned >= $hours_needed) break;
                    
                    // Morning Constraint
                    if ($start == 5 && $row['availability'] == 'MORNING_ONLY') continue;
                    
                    // Lab Room Capacity (Max 2 Labs per Dept)
                    if (!checkLabCapacity($conn, $day, $start, $dept) || 
                        !checkLabCapacity($conn, $day, $start+1, $dept) || 
                        !checkLabCapacity($conn, $day, $start+2, $dept)) continue;

                    // STRICT CONFLICT CHECK (3 Slots must be free)
                    if (isSlotFree($conn, $day, $start, $fac_id, $dept, $sec, $sem) &&
                        isSlotFree($conn, $day, $start+1, $fac_id, $dept, $sec, $sem) &&
                        isSlotFree($conn, $day, $start+2, $fac_id, $dept, $sec, $sem)) {
                        
                        bookSlot($conn, $day, $start, $row, "Lab");
                        bookSlot($conn, $day, $start+1, $row, "Lab");
                        bookSlot($conn, $day, $start+2, $row, "Lab");
                        $hours_assigned += 3;
                        break;
                    }
                }
            }
        } 
        
        // --- THEORY LOGIC ---
        else {
            shuffle($days); 
            $theory_slots = [1, 2, 3, 4, 5, 6, 7];
            $slots_used_by_subject = []; 

            // Pass 1: Spread across days
            foreach ($days as $day) {
                if ($hours_assigned >= $hours_needed) break;
                shuffle($theory_slots); // Randomize slot order per day

                foreach ($theory_slots as $slot) {
                    // Morning Fatigue Check
                    if ($slot == 1 && getSlot1Count($conn, $fac_id) >= 2) continue;
                    
                    // Avoid same slot multiple days
                    if (in_array($slot, $slots_used_by_subject)) continue;

                    // STRICT CONFLICT CHECK
                    if (isValidTheorySlot($conn, $day, $slot, $row, $dept, $sec, $sem)) {
                        bookSlot($conn, $day, $slot, $row, "Classroom");
                        $hours_assigned++;
                        $slots_used_by_subject[] = $slot;
                        break; // Next day
                    }
                }
            }
            
            // Pass 2: Force fill if needed
            if ($hours_assigned < $hours_needed) {
                foreach ($days as $day) {
                    if ($hours_assigned >= $hours_needed) break;
                    for ($slot=1; $slot<=7; $slot++) {
                        if ($hours_assigned >= $hours_needed) break;
                        if (isValidTheorySlot($conn, $day, $slot, $row, $dept, $sec, $sem)) {
                            bookSlot($conn, $day, $slot, $row, "Classroom");
                            $hours_assigned++;
                        }
                    }
                }
            }
        }
    }
}

// --- STRICT HELPER FUNCTIONS ---

function getSlot1Count($conn, $fac_id) {
    // Count how many Morning slots this faculty has ACROSS ALL DEPARTMENTS
    $sql = "SELECT COUNT(*) as c FROM generated_timetable WHERE faculty_id = '$fac_id' AND slot_id = 1";
    return $conn->query($sql)->fetch_assoc()['c'];
}

function checkLabCapacity($conn, $day, $slot, $dept) {
    // Max 2 Labs running simultaneously per Department
    $sql = "SELECT COUNT(*) as c FROM generated_timetable g 
            JOIN subjects s ON g.subject_code = s.subject_code 
            WHERE g.day = '$day' AND g.slot_id = '$slot' 
            AND s.department = '$dept' AND s.course_type = 'LAB'";
    return ($conn->query($sql)->fetch_assoc()['c'] < 2);
}

function isValidTheorySlot($conn, $day, $slot, $row, $dept, $sec, $sem) {
    if ($slot > 4 && $row['availability'] == 'MORNING_ONLY') return false;
    return isSlotFree($conn, $day, $slot, $row['faculty_id'], $dept, $sec, $sem);
}

// *** THIS IS THE CRITICAL FUNCTION ***
function isSlotFree($conn, $day, $slot, $fac_id, $dept, $sec, $sem) {
    
    // CHECK 1: GLOBAL FACULTY CONFLICT
    // Search the ENTIRE timetable. If this Faculty ID appears anywhere for this Day/Slot, return FALSE.
    // It doesn't matter if they are in CSE, ECE, or MECH. They are busy.
    $check_fac = $conn->query("SELECT * FROM generated_timetable 
                               WHERE day='$day' AND slot_id='$slot' AND faculty_id='$fac_id'");
    
    if ($check_fac->num_rows > 0) {
        return false; // Faculty is busy elsewhere!
    }

    // CHECK 2: STUDENT BATCH CONFLICT
    // Is this specific Class (Dept + Sem + Sec) already busy in this slot?
    // We join 'subjects' table to filter by Department and Semester.
    $check_batch = $conn->query("SELECT * FROM generated_timetable g 
                                 JOIN subjects s ON g.subject_code = s.subject_code
                                 WHERE g.day='$day' AND g.slot_id='$slot' 
                                 AND s.department='$dept' AND s.semester='$sem' AND g.section='$sec'");

    if ($check_batch->num_rows > 0) {
        return false; // Students are busy!
    }

    return true; // Slot is free
}

function bookSlot($conn, $day, $slot, $data, $room) {
    $sub = $data['subject_code'];
    $fac = $data['faculty_id'];
    $sec = $data['section'];
    
    // We insert into the global table.
    // Once inserted, the 'isSlotFree' check above will see this row and block the faculty 
    // from being used by any other department for this slot.
    $conn->query("INSERT INTO generated_timetable (day, slot_id, subject_code, faculty_id, room_no, section) 
                  VALUES ('$day', '$slot', '$sub', '$fac', '$room', '$sec')");
}
?>

<!DOCTYPE html>
<html>
<head><title>Generator</title></head>
<body>
<div class="container">
    <div class="control-panel">
        <h2>⚙️ Generator (Conflict Proof)</h2>
        <?php if($msg) echo "<div class='msg-success'>✅ $msg</div>"; ?>
        
        <form method="post">
            <div style="display:flex; gap:10px; justify-content:center; align-items:center;">
                <div style="flex:1">
                    <label>Department:</label>
                    <select name="dept_filter">
                        <option value="ALL">-- ALL --</option>
                        <?php foreach($branches as $b) echo "<option value='$b'>$b</option>"; ?>
                    </select>
                </div>
                
                <div style="flex:1">
                    <label>Semester:</label>
                    <select name="sem_filter">
                        <option value="ALL">-- ALL --</option>
                        <?php foreach($semesters as $sem) echo "<option value='$sem'>$sem</option>"; ?>
                    </select>
                </div>

                <div style="flex:1">
                    <label>Section:</label>
                    <select name="sec_filter">
                        <option value="ALL">-- ALL --</option>
                        <?php foreach($sections as $s) echo "<option value='$s'>$s</option>"; ?>
                    </select>
                </div>
            </div>

            <button type="submit" name="generate" class="btn btn-blue" style="margin-top:20px;">Run Generator</button>
        </form>
    </div>
</div>
</body>
</html>