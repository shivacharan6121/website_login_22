<?php
session_start();
require_once 'auth.php';
requireLogin();
require_once 'db_config.php';
require('fpdf/fpdf.php');

// Fetch data from database
$sql = "SELECT * FROM part ORDER BY make, Nomenclature";
$result = $conn->query($sql);

$data = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
$conn->close();

// Create PDF class
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'Connector Records - EO-SAAW/RCI. ' . date('d-m-Y') . '',0,1,'C');
        $this->Ln(5);
        $this->SetFont('Arial','B',10);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(10, 10, 'S.No', 1, 0, 'C', true);
        $this->Cell(60, 10, 'Nomenclature', 1, 0, 'C', true);
        $this->Cell(40, 10, 'Make', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Total Qty', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Issued Qty', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Available Qty', 1, 1, 'C', true);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }
}

// Generate and output PDF
try {
    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('times','',10);

    $serial = 1;
    foreach ($data as $row) {
        $pdf->Cell(10, 8, $serial++, 1);
        $pdf->Cell(60, 8, $row['Nomenclature'] ?? '', 1);
        $pdf->Cell(40, 8, $row['make'] ?? '', 1);
        $pdf->Cell(25, 8, $row['quantity'] ?? 0, 1, 0, 'C');
        $pdf->Cell(25, 8, $row['usedqty'] ?? 0, 1, 0, 'C');
        $pdf->Cell(30, 8, $row['availableqty'] ?? 0, 1, 1, 'C');
    }

    $filename = "connector_records_" . date('d-m-Y') . ".pdf";
    $pdf->Output('D', $filename);
    exit;
    
} catch (Exception $e) {
    // If PDF generation fails, output error message
    header('Content-Type: text/plain');
    die("Error generating PDF: " . $e->getMessage());
}
?>