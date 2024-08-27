<?php
require('../fpdf/fpdf.php');

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Lunch Pairs', 0, 1, 'C');
$pdf->Ln(10);

// Connect to the SQLite database
$db = new SQLite3('../database/lunch_roulette.db');

// Fetch pairs and user names
$query = "
    SELECT 
        u1.name AS user1_name, 
        u2.name AS user2_name, 
        u3.name AS user3_name 
    FROM 
        current_roulette cr
    LEFT JOIN 
        users u1 ON cr.user1 = u1.id
    LEFT JOIN 
        users u2 ON cr.user2 = u2.id
    LEFT JOIN 
        users u3 ON cr.user3 = u3.id
";

$result = $db->query($query);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 10, 'Person 1', 1);
$pdf->Cell(60, 10, 'Person 2', 1);
$pdf->Cell(60, 10, 'Person 3', 1);
$pdf->Ln();

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $pdf->Cell(60, 10, $row['user1_name'], 1);
    $pdf->Cell(60, 10, $row['user2_name'], 1);
    if (!empty($row['user3_name'])) {
        $pdf->Cell(60, 10, $row['user3_name'], 1);
    } else {
        $pdf->Cell(60, 10, '', 1);
    }
    $pdf->Ln();
}

$db->close();

// Output the PDF as a download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Lunch-Paare.pdf"');
$pdf->Output('D', 'Lunch-Paare.pdf'); // 'D' is for download
?>
