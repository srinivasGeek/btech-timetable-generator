<?php
// Define the path to the font directory explicitly
define('FPDF_FONTPATH', 'includes/font/');

require('includes/fpdf.php'); 

class PDF extends FPDF {
    // Page Header
    function Header() {
        // 1. Logo/Header Image
        // Adjust the path 'images/header.jpg' to where your image is.
        // 10, 10 is X,Y position. 190 is width (A4 width is 210, so 190 leaves margins).
        if(file_exists('images/header.jpg')) {
            $this->Image('images/header.jpg', 10, 10, 190); 
        } else {
            // Fallback if image missing
            $this->SetFont('Arial', 'B', 15);
            $this->Cell(0, 10, 'DEPARTMENT OF CSE (DS)', 0, 1, 'C');
            $this->Ln(5);
        }
        
        // Move cursor down to start the content below the header image
        // Increase this number if your header image is tall
        $this->Ln(35); 
        
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'II BTECH I SEM EXTERNAL LAB EXAMINATION NOV 2025', 0, 1, 'C');
        $this->Ln(5);
    }

    // Page Footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Custom Table Function
    function ExamTable($header, $data) {
        // Colors, line width and bold font
        $this->SetFillColor(200, 220, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(50, 50, 100);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        
        // Header
        $w = array(30, 25, 25, 70, 40); // Column widths
        for($i=0; $i<count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        $this->Ln();
        
        // Data
        $this->SetFont('Arial', '', 10);
        foreach($data as $row) {
            $this->Cell($w[0], 6, $row[0], 1, 0, 'C'); // Date
            $this->Cell($w[1], 6, $row[1], 1, 0, 'C'); // Session
            $this->Cell($w[2], 6, $row[2], 1, 0, 'C'); // Lab No
            
            // Handle long text in Lab Name using MultiCell logic or shortening
            // For simplicity here, we assume it fits or we reduce font
            $this->SetFont('Arial', '', 9); 
            $this->Cell($w[3], 6, $row[3], 1, 0, 'L'); // Lab Name
            
            $this->SetFont('Arial', '', 10);
            $this->Cell($w[4], 6, $row[4], 1, 0, 'L'); // Examiner
            $this->Ln();
        }
        // Closing line
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

// ==========================================
// DATA PREPARATION (From your uploaded file)
// ==========================================

// Define Timings
$timing_fn = "9:20 AM-12:50 PM";
$timing_an = "1:50 PM -4:20 PM";

// We group data by Section so we can print one page per section
$sections_data = [
    "CSD-A" => [
        ["25-11-2025", "FN", "LAB 006", "OOPS THROUGH JAVA", "Mr RAJINI"],
        ["26-11-2025", "FN", "LAB 006", "DATA VISUALIZATION (R)", "Mr P HEMANTH"],
        ["27-11-2025", "FN", "LAB 301&302", "DATA STRUCTURES LAB", "Mr G JACOB"],
    ],
    // Note: CSD-B data inferred from source rows 2, 5, 8 
    "CSD-B" => [
        ["25-11-2025", "AN", "LAB 006", "OOPS THROUGH JAVA", "Mr RAJINI"],
        ["26-11-2025", "AN", "LAB 006", "DATA VISUALIZATION (R)", "Mr P HEMANTH"],
        ["27-11-2025", "AN", "LAB 301&302", "DATA STRUCTURES LAB", "Mr G JACOB"],
    ],
    "CSD-C" => [
        ["25-11-2025", "AN", "LAB 301&302", "DATA STRUCTURES LAB", "Mr G JACOB"],
        ["26-11-2025", "AN", "LAB 301&302", "OOPS THROUGH JAVA", "Mr RAJINI"],
        ["27-11-2025", "AN", "LAB 006", "DATA VISUALIZATION (R)", "Mr P HEMANTH"],
    ]
];

// ==========================================
// GENERATE PDF
// ==========================================

$pdf = new PDF();
$header = array('Date', 'Session', 'Lab No', 'Lab Name', 'Examiner');

foreach ($sections_data as $section_name => $rows) {
    $pdf->AddPage();
    
    // Section Title
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, "Time Table for Branch: $section_name", 0, 1, 'L');
    $pdf->Ln(5);

    // Print the Table
    $pdf->ExamTable($header, $rows);
    
    // Timings Legend
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, "Exam Timings:", 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, "FN: $timing_fn", 0, 1);
    $pdf->Cell(0, 5, "AN: $timing_an", 0, 1);
    
    // Guidelines
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, "Guidelines to Students:", 0, 1);
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(0, 5, "1) Lab Record Certification: Your Lab Observation/Record must be fully signed by your instructor before the day of the exam. An unsigned or incomplete record will prevent you from sitting for the exam.");
    $pdf->MultiCell(0, 5, "2) Dress Code: Formal attire is required; otherwise, you will be denied permission.");
    $pdf->MultiCell(0, 5, "3) Identification: Students must wear their ID cards throughout the examination period.");

    // Signatures
    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(90, 10, "HOD", 0, 0, 'L');
    $pdf->Cell(90, 10, "PRINCIPAL", 0, 1, 'R');
}

$pdf->Output();
?>