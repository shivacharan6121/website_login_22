<?php
session_start();
require_once 'auth.php';
requireLogin();
require_once 'db_config.php';
require('fpdf/fpdf.php');

// Fetch data from database with optional search
$sql = "SELECT * FROM issue";
if (!empty($search_query)) {
    $sql .= " WHERE Nomenclature LIKE '%$search_query%'";
}
$sql .= " ORDER BY timestamp DESC";

$result = $conn->query($sql);

$data = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Create PDF class
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'ISSUED CONNECTOR RECORDS - EO-SAAW/RCI',0,1,'C');
        $this->Ln(5);
        $this->SetFont('Arial','B',10);
        $this->SetFillColor(255, 255, 255); // Light green background
        $this->SetTextColor(0); // Black text
        
        // Header row
        $this->Cell(10, 10, 'S.No', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Part No', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Make', 1, 0, 'C', true);
        $this->Cell(20, 10, 'Issued Qty', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Issued To', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Designation', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Purpose', 1, 0, 'C', true);
        $this->Cell(20, 10, 'Date', 1, 0, 'C', true);
        $this->Cell(20, 10, 'Time', 1, 1, 'C', true);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }
}

// Generate and output PDF
try {
    $pdf = new PDF('L'); // Landscape orientation for better fit
    $pdf->AddPage();
    $pdf->SetFont('Arial','',9);
    
    $serial = 1;
    foreach ($data as $row) {
        // Format timestamp
        $timestamp = strtotime($row['timestamp']);
        $date = date('d/m/Y', $timestamp);
        $time = date('h:i A', $timestamp);
        
        $pdf->Cell(10, 8, $serial++, 1);
        $pdf->Cell(50, 8, $row['Nomenclature'] ?? '', 1);
        $pdf->Cell(30, 8, $row['make'] ?? '', 1);
        $pdf->Cell(20, 8, $row['issuedqty'] ?? 0, 1, 0, 'C');
        $pdf->Cell(30, 8, $row['person_name'] ?? '', 1);
        $pdf->Cell(30, 8, $row['designation'] ?? 'N/A', 1);
        $pdf->Cell(50, 8, $row['purpose'] ?? '', 1);
        $pdf->Cell(20, 8, $date, 1, 0, 'C');
        $pdf->Cell(20, 8, $time, 1, 1, 'C');
    }

    $filename = "issued_connectors_" . date('Y-m-d') . ".pdf";
    $pdf->Output('D', $filename);
    exit;
    
} catch (Exception $e) {
    // If PDF generation fails, output error message
    header('Content-Type: text/plain');
    die("Error generating PDF: " . $e->getMessage());
}
?>