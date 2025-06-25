<?php
require_once __DIR__ . '/../config/db.php';
require_role('client');

// ✅ Directly include FPDF from the vendor folder
require_once __DIR__ . '/../vendor/setasign/fpdf/fpdf.php';  // Make sure this file exists

// ✅ Get case details
$id = (int)($_GET['case'] ?? 0);
$case = db()->prepare("SELECT * FROM cases WHERE id=? AND client_id=?");
$case->execute([$id, current_user()['id']]);
$case = $case->fetch(PDO::FETCH_ASSOC);

if (!$case) {
    exit('Case not found');
}

// ✅ Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Case Summary', 0, 1, 'C');
$pdf->Ln(4);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 8, "Case Number: " . $case['case_number']);
$pdf->MultiCell(0, 8, "Title: " . $case['title']);
$pdf->MultiCell(0, 8, "Status: " . $case['status']);
$pdf->Ln(4);
$pdf->MultiCell(0, 7, "Description:\n" . $case['description']);

$pdf->Output('D', 'case_' . $case['case_number'] . '_summary.pdf');

// ✅ Log the action
audit('case_pdf_download', 'case', $id);
