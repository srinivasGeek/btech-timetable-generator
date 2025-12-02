<?php
// Prevent any stray spaces/errors from corrupting the PDF
ob_start();

define('FPDF_FONTPATH', 'includes/font/');
require('includes/fpdf.php');
include 'includes/db_connect.php';

// INPUTS
$dept = $_GET['dept'] ?? 'CSE';
$sec = $_GET['sec'] ?? 'A';

// HELPER: Acronym Generator
function generateAcronym($string) {
    $string = trim(preg_replace('/\s+/', ' ', $string));
    if(strlen($string) <= 4) return strtoupper($string);
    $words = explode(" ", $string);
    $acronym = "";
    foreach ($words as $w) {
        if (!empty($w)) $acronym .= $w[0];
    }
    return strtoupper($acronym);
}

class PDF extends FPDF {
    // PAGE HEADER
    function Header() {
        // 1. Header Image
        if(file_exists('images/header.jpg')) {
            $this->Image('images/header.jpg', 10, 5, 277); // Fit to A4 Landscape
            $this->Ln(35); // Push content down
        } else {
            $this->SetFont('Arial', 'B', 20);
            $this->Cell(0, 10, 'COLLEGE TIMETABLE', 0, 1, 'C');
            $this->Ln(5);
        }

        // 2. Info Row
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, "Department: " . $_GET['dept'] . "  -  Section: " . $_GET['sec'], 0, 1, 'C');
        $this->Ln(5);

        // 3. Table Header
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(50, 50, 50); // Dark Grey
        $this->SetTextColor(255); // White text
        
        // Column Widths (Total ~270mm)
        $w_day = 30;
        $w_slot = 30;
        $w_gap = 10;
        
        $this->Cell($w_day, 8, 'Day', 1, 0, 'C', true);
        $this->Cell($w_slot, 8, '09:20-10:10', 1, 0, 'C', true);
        $this->Cell($w_slot, 8, '10:10-11:00', 1, 0, 'C', true);
        $this->Cell($w_gap, 8, 'Brk', 1, 0, 'C', true);
        $this->Cell($w_slot, 8, '11:20-12:10', 1, 0, 'C', true);
        $this->Cell($w_slot, 8, '12:10-01:00', 1, 0, 'C', true);
        $this->Cell($w_gap, 8, 'Lnh', 1, 0, 'C', true);
        $this->Cell($w_slot, 8, '01:50-02:40', 1, 0, 'C', true);
        $this->Cell($w_slot, 8, '02:40-03:30', 1, 0, 'C', true);
        $this->Cell($w_slot, 8, '03:30-04:20', 1, 1, 'C', true);
    }

    // PAGE FOOTER
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// GENERATE PDF
$pdf = new PDF('L', 'mm', 'A4'); // Landscape
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$w_day = 30;
$w_slot = 30;
$w_gap = 10;
$h_row = 12; // Standard Row Height

foreach ($days as $day) {
    // Row Start
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(0);
    $pdf->SetFillColor(240); // Light Grey for Day
    $pdf->Cell($w_day, $h_row, $day, 1, 0, 'C', true);
    
    $pdf->SetFont('Arial', '', 10); // Normal font for subjects

    for ($i = 1; $i <= 7; $i++) {
        // BREAK & LUNCH GAPS
        if ($i == 3 || $i == 5) {
            $pdf->SetFillColor(220);
            $pdf->Cell($w_gap, $h_row, '', 1, 0, 'C', true);
        }

        // FETCH DATA
        $sql = "SELECT s.subject_name, s.course_type 
                FROM generated_timetable t
                JOIN subjects s ON t.subject_code = s.subject_code
                WHERE t.day='$day' AND t.slot_id='$i' 
                AND s.department='$dept' AND t.section='$sec'";
        $res = $conn->query($sql);

        $text = "-";
        $fill = false;

        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $text = generateAcronym($row['subject_name']); // Only show CN, OS, etc.
            
            if ($row['course_type'] == 'LAB') {
                $pdf->SetFillColor(200, 230, 255); // Light Blue for Lab
                $fill = true;
            }
        } else {
            $pdf->SetFillColor(255); // White for empty
        }

        // DRAW CELL
        $pdf->Cell($w_slot, $h_row, $text, 1, 0, 'C', $fill);
    }
    $pdf->Ln(); // End of Row
}

// LEGEND TABLE (Subject + Faculty)
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, "Subject & Faculty Details", 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230);
$pdf->Cell(20, 8, 'Code', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Short', 1, 0, 'C', true);
$pdf->Cell(110, 8, 'Subject Name', 1, 0, 'L', true);
$pdf->Cell(80, 8, 'Faculty Name', 1, 0, 'L', true);
$pdf->Cell(20, 8, 'Type', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$sql_legend = "SELECT s.subject_code, s.subject_name, s.course_type, f.faculty_name 
               FROM course_allotment ca
               JOIN subjects s ON ca.subject_code = s.subject_code
               JOIN faculty f ON ca.faculty_id = f.faculty_id
               WHERE s.department = '$dept' AND ca.section = '$sec'
               ORDER BY s.course_type DESC, s.subject_name";

$res_legend = $conn->query($sql_legend);
while($row = $res_legend->fetch_assoc()) {
    $pdf->Cell(20, 7, $row['subject_code'], 1, 0, 'C');
    $pdf->Cell(20, 7, generateAcronym($row['subject_name']), 1, 0, 'C');
    $pdf->Cell(110, 7, $row['subject_name'], 1, 0, 'L');
    $pdf->Cell(80, 7, $row['faculty_name'], 1, 0, 'L');
    $pdf->Cell(20, 7, $row['course_type'], 1, 1, 'C');
}

ob_end_flush();
$pdf->Output('D', "Timetable_{$dept}_{$sec}.pdf");
?>