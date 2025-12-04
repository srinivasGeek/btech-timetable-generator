<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';
$my_dept_id = $_SESSION['dept_id'];
$msg = ""; 
$sections = ["A", "B", "C"];
$semesters = ["I-I", "I-II", "II-I", "II-II", "III-I", "III-II", "IV-I", "IV-II"];

if (isset($_POST['generate']) || isset($_POST['reshuffle'])) {
    
    $target_sem = $_POST['sem_filter'];
    $target_sec = $_POST['sec_filter'];

    // 1. CLEANUP
    $sql_clean = "DELETE t FROM timetable t 
                  JOIN subjects s ON t.subject_id = s.subject_id 
                  WHERE s.dept_id = '$my_dept_id'";
    if ($target_sem != 'ALL') $sql_clean .= " AND s.semester = '$target_sem'";
    if ($target_sec != 'ALL') $sql_clean .= " AND t.section = '$target_sec'";
    $conn->query($sql_clean);
    
    // 2. FETCH DATA
    $sql_fetch = "SELECT * FROM subjects WHERE dept_id = '$my_dept_id'";
    if ($target_sem != 'ALL') $sql_fetch .= " AND semester = '$target_sem'";
    $sql_fetch .= " ORDER BY is_lab DESC, hours_per_week DESC";
    $result = $conn->query($sql_fetch);
    
    $fac_res = $conn->query("SELECT faculty_id, role, availability FROM faculty WHERE dept_id = '$my_dept_id'");
    $faculty_details = [];
    while($row = $fac_res->fetch_assoc()) { 
        $faculty_details[$row['faculty_id']] = ['role'=>$row['role'], 'avail'=>$row['availability']]; 
    }

    if($result->num_rows > 0) {
        $outcome = runScheduler($conn, $result, $faculty_details, $target_sec, $sections, $my_dept_id);
        $missed_subjects = $outcome['missed'];
        if (count($missed_subjects) == 0) $msg = "Timetable Generated Successfully!";
        else $msg = "Generated with conflicts.";
    }
}

// --- CORE ALGORITHM ---
function runScheduler($conn, $result, $faculty_details, $target_sec, $all_sections, $dept_id) {
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    $sections_to_run = ($target_sec == 'ALL') ? $all_sections : [$target_sec];
    $subjects = [];
    while($row = $result->fetch_assoc()) { $subjects[] = $row; }
    $missed_list = [];

    $faculty_ids = array_keys($faculty_details);

    foreach($sections_to_run as $current_sec) {
        foreach($subjects as $sub) {
            
            // GET ASSIGNMENT
            $sub_id = $sub['subject_id'];
            $allot_sql = "SELECT faculty_id, room_id FROM course_allotment WHERE subject_id = '$sub_id' AND section = '$current_sec'";
            $allot_res = $conn->query($allot_sql);
            
            if ($allot_res->num_rows == 0) {
                $missed_list[] = "{$sub['subject_name']} ($current_sec) - Not Assigned"; continue;
            }
            
            $row_allot = $allot_res->fetch_assoc();
            $fac_id = $row_allot['faculty_id'];
            $assigned_room_id = $row_allot['room_id']; 
            
            $hours_needed = $sub['hours_per_week'];
            $is_lab = $sub['is_lab'];
            $hours_assigned = 0;
            
            $teacher_role = $faculty_details[$fac_id]['role'];
            $teacher_avail = $faculty_details[$fac_id]['avail'];
            $used_slot_indices = []; // Anti-stacking tracker

            // --- LAB LOGIC ---
            if ($is_lab) {
                $valid_starts = ($teacher_role == 'hod') ? [4] : (($teacher_avail == 'MORNING') ? [0] : [4, 0]);

                foreach ($days as $day) {
                    if ($hours_assigned >= $hours_needed) break;
                    if (sectionHasLabOnDay($conn, $day, $current_sec, $sub['semester'], $dept_id)) continue; 

                    foreach ($valid_starts as $start) {
                        
                        // NOTE: Lab continuity (N, N+1, N+2) is assumed and allowed here.
                        $room_ok = false;
                        $final_room_id = null;

                        if ($assigned_room_id) {
                            if (checkSpecificRoom($conn, $day, $start, $assigned_room_id, 3)) {
                                $room_ok = true; $final_room_id = $assigned_room_id;
                            }
                        } else { // Auto Room Fallback
                            $found_room = findFreeRoom($conn, $day, $start, 'lab', 3);
                            if ($found_room) {
                                $room_ok = true; $final_room_id = $found_room;
                            }
                        }

                        if ($room_ok &&
                            isSlotFree($conn, $day, $start, $fac_id, $current_sec, $sub['semester']) &&
                            isSlotFree($conn, $day, $start+1, $fac_id, $current_sec, $sub['semester']) &&
                            isSlotFree($conn, $day, $start+2, $fac_id, $current_sec, $sub['semester'])) {
                            
                            bookSlot($conn, $day, $start, $sub['subject_id'], $fac_id, $current_sec, $final_room_id);
                            bookSlot($conn, $day, $start+1, $sub['subject_id'], $fac_id, $current_sec, $final_room_id);
                            bookSlot($conn, $day, $start+2, $sub['subject_id'], $fac_id, $current_sec, $final_room_id);
                            $hours_assigned += 3;
                            break; 
                        }
                    }
                }
            } 
            // --- THEORY LOGIC ---
            else {
                shuffle($days);
                $valid_slots = [0, 1, 2, 3, 4, 5, 6];
                if ($teacher_role == 'hod') $valid_slots = array_diff($valid_slots, [0]);
                if ($teacher_avail == 'MORNING') $valid_slots = array_diff($valid_slots, [4, 5, 6]);
                shuffle($valid_slots);

                foreach ($days as $day) {
                    if ($hours_assigned >= $hours_needed) break;
                    foreach ($valid_slots as $slot_idx) {
                        if ($hours_assigned >= $hours_needed) break;
                        
                        // CHECK 1: ANTI-STACKING (No same period index across days)
                        if (in_array($slot_idx, $used_slot_indices)) continue;

                        // CHECK 2: FATIGUE (No back-to-back classes of DIFFERENT subjects)
                        if (facultyIsFatigued($conn, $day, $slot_idx, $fac_id, $sub_id)) continue;
                        
                        // FINAL CHECK & BOOKING
                        $room_ok = false;
                        $final_room_id = null;
                        if ($assigned_room_id) {
                            if (checkSpecificRoom($conn, $day, $slot_idx, $assigned_room_id, 1)) {
                                $room_ok = true; $final_room_id = $assigned_room_id;
                            }
                        } else {
                            $found_room = findFreeRoom($conn, $day, $slot_idx, 'classroom', 1);
                            if ($found_room) {
                                $room_ok = true; $final_room_id = $found_room;
                            }
                        }

                        if ($room_ok && isSlotFree($conn, $day, $slot_idx, $fac_id, $current_sec, $sub['semester'])) {
                            bookSlot($conn, $day, $slot_idx, $sub['subject_id'], $fac_id, $current_sec, $final_room_id);
                            $hours_assigned++;
                            $used_slot_indices[] = $slot_idx; 
                            break; 
                        }
                    }
                }
            }
            if ($hours_assigned < $hours_needed) $missed_list[] = "{$sub['subject_name']} ($current_sec)";
        }
    }
    return ['missed' => $missed_list];
}

// --- NEW HELPER: FATIGUE CHECK ---
function facultyIsFatigued($conn, $day, $current_slot_idx, $fac_id, $current_sub_id) {
    if ($current_slot_idx == 0) return false;
    
    // Slot Indices that follow a scheduled BREAK or LUNCH must be ignored for the check.
    // Slot 2 follows 11:00-11:20 BREAK.
    // Slot 4 follows 1:00-1:50 LUNCH.
    if ($current_slot_idx == 2 || $current_slot_idx == 4) return false; 
    
    $prev_slot_idx = $current_slot_idx - 1;
    $times = [0=>'09:20-10:10', 1=>'10:10-11:00', 2=>'11:20-12:10', 3=>'12:10-01:00', 4=>'01:50-02:40', 5=>'02:40-03:30', 6=>'03:30-04:20'];
    $prev_time_str = $times[$prev_slot_idx];

    // 1. Check if faculty is busy in the previous slot
    $sql = "SELECT subject_id FROM timetable WHERE day='$day' AND time_slot='$prev_time_str' AND faculty_id='$fac_id'";
    $res = $conn->query($sql);

    if ($res->num_rows > 0) {
        $prev_sub_id = $res->fetch_assoc()['subject_id'];
        
        // 2. If the subjects are DIFFERENT, it's a conflict (Fatigue)
        if ($prev_sub_id != $current_sub_id) {
            return true; // Conflict: Different subject back-to-back
        }
        // If same subject, it's allowed (double period/continuation)
    }
    
    return false; // No conflict
}

// --- REMAINDER HELPER FUNCTIONS (No change in logic) ---
function bookSlot($conn, $day, $slot_idx, $sub_id, $fac_id, $sec, $room_id) {
    $times = [0=>'09:20-10:10', 1=>'10:10-11:00', 2=>'11:20-12:10', 3=>'12:10-01:00', 4=>'01:50-02:40', 5=>'02:40-03:30', 6=>'03:30-04:20'];
    $time_str = $times[$slot_idx];
    $sql = "INSERT INTO timetable (day, time_slot, subject_id, faculty_id, room_id, section) 
            VALUES ('$day', '$time_str', '$sub_id', '$fac_id', '$room_id', '$sec')";
    $conn->query($sql);
}

function checkSpecificRoom($conn, $day, $start_slot, $room_id, $duration) {
    $times = [0=>'09:20-10:10', 1=>'10:10-11:00', 2=>'11:20-12:10', 3=>'12:10-01:00', 4=>'01:50-02:40', 5=>'02:40-03:30', 6=>'03:30-04:20'];
    for($i=0; $i<$duration; $i++) {
        $idx = $start_slot + $i;
        if(!isset($times[$idx])) return false;
        $t_str = $times[$idx];
        $sql = "SELECT * FROM timetable WHERE day='$day' AND time_slot='$t_str' AND room_id='$room_id'";
        if($conn->query($sql)->num_rows > 0) return false;
    }
    return true;
}

function findFreeRoom($conn, $day, $start_slot, $type, $duration) {
    $rooms_res = $conn->query("SELECT room_id FROM rooms WHERE room_type='$type'");
    $all_rooms = [];
    while($r = $rooms_res->fetch_assoc()) { $all_rooms[] = $r['room_id']; }
    shuffle($all_rooms); 

    foreach($all_rooms as $rid) {
        if(checkSpecificRoom($conn, $day, $start_slot, $rid, $duration)) {
            return $rid;
        }
    }
    return false;
}
function sectionHasLabOnDay($conn, $day, $sec, $sem, $dept_id) {
    $sql = "SELECT COUNT(*) as c FROM timetable t JOIN subjects s ON t.subject_id = s.subject_id 
            WHERE t.day = '$day' AND t.section = '$sec' AND s.semester = '$sem' AND s.dept_id = '$dept_id' AND s.is_lab = 1";
    return ($conn->query($sql)->fetch_assoc()['c'] > 0);
}

function isSlotFree($conn, $day, $slot_idx, $fac_id, $sec, $sem) {
    $times = [0=>'09:20-10:10', 1=>'10:10-11:00', 2=>'11:20-12:10', 3=>'12:10-01:00', 4=>'01:50-02:40', 5=>'02:40-03:30', 6=>'03:30-04:20'];
    if(!isset($times[$slot_idx])) return false;
    $time_str = $times[$slot_idx];

    $q1 = "SELECT * FROM timetable WHERE day='$day' AND time_slot='$time_str' AND faculty_id='$fac_id'";
    if ($conn->query($q1)->num_rows > 0) return false;

    $q2 = "SELECT * FROM timetable t JOIN subjects s ON t.subject_id = s.subject_id 
           WHERE t.day='$day' AND t.time_slot='$time_str' AND s.semester='$sem' AND t.section='$sec'";
    if ($conn->query($q2)->num_rows > 0) return false;

    return true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generator</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; height: 100vh; background-color: #f4f4f9; }
        .sidebar { width: 250px; background-color: #34495e; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background-color: #2c3e50; margin: 0; }
        .sidebar a { padding: 15px 20px; color: #bdc3c7; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar a:hover { background-color: #2c3e50; color: white; }
        .main-content { flex: 1; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .card { background: white; padding: 30px; border-radius: 8px; width: 100%; max-width: 600px; text-align:center;}
        select { width: 100%; padding: 10px; border: 1px solid #ddd; }
        .btn-gen { width: 100%; padding: 15px; background: #2980b9; color: white; border: none; cursor: pointer; border-radius: 5px; margin-top:10px;}
        .btn-reshuffle { width: 100%; padding: 15px; background: #c0392b; color: white; border: none; cursor: pointer; border-radius: 5px; margin-top:10px;}
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom:10px;}
        .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom:10px; text-align:left;}
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>HOD Panel</h2>
        <a href="hod_dashboard.php">Dashboard</a>
        <a href="hod_subjects.php">Manage Subjects</a>
        <a href="hod_staff.php">Manage Staff</a>
        <a href="hod_allotment.php">Course Allotment</a>
        <a href="generate_timetable.php" class="active" style="background-color: #1abc9c; color: white;">Generate Timetable</a>
        <a href="view_timetable.php">View Timetable</a>
        <a href="workload.php">Workload Report</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <div class="card">
            <h2>⚙️ Final Generator (Robust)</h2>
            <?php if (isset($msg) && strpos($msg, 'Successfully') !== false): ?>
                <div class="alert-success">✅ <?php echo $msg; ?> <br> <a href="view_timetable.php">View Timetable</a></div>
            <?php elseif (isset($msg) && $msg != ""): ?>
                <div class="alert-danger">
                    ⚠️ <b>Missed Subjects:</b>
                    <ul><?php foreach($missed_subjects as $ms) echo "<li>$ms</li>"; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div style="display:flex; gap:10px;">
                    <select name="sem_filter"><option value="ALL">ALL Semesters</option><?php foreach($semesters as $s) echo "<option value='$s'>$s</option>"; ?></select>
                    <select name="sec_filter"><option value="ALL">ALL Sections</option><?php foreach($sections as $s) echo "<option value='$s'>Section $s</option>"; ?></select>
                </div>
                <button type="submit" name="generate" class="btn-gen">⚡ Generate</button>
            </form>
        </div>
    </div>
</body>
</html>