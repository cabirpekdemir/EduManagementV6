<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/fpdf.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT a.id, s.name AS student_name, c.name AS course_name, a.title, a.grade, a.created_at
    FROM assignments a
    JOIN students s ON s.id = a.student_id
    JOIN courses c ON c.id = a.course_id
    ORDER BY a.created_at DESC
");
$stmt->execute();
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Odev Listesi',0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(10, 7, 'ID', 1);
$pdf->Cell(40, 7, 'Ogrenci', 1);
$pdf->Cell(40, 7, 'Kurs', 1);
$pdf->Cell(50, 7, 'Baslik', 1);
$pdf->Cell(20, 7, 'Not', 1);
$pdf->Cell(30, 7, 'Tarih', 1);
$pdf->Ln();

$pdf->SetFont('Arial','',9);
foreach ($assignments as $a) {
    $pdf->Cell(10, 6, $a['id'], 1);
    $pdf->Cell(40, 6, mb_convert_encoding($a['student_name'], 'ISO-8859-9', 'UTF-8'), 1);
    $pdf->Cell(40, 6, mb_convert_encoding($a['course_name'], 'ISO-8859-9', 'UTF-8'), 1);
    $pdf->Cell(50, 6, mb_convert_encoding($a['title'], 'ISO-8859-9', 'UTF-8'), 1);
    $pdf->Cell(20, 6, $a['grade'] ?? '-', 1);
    $pdf->Cell(30, 6, date('d.m.Y', strtotime($a['created_at'])), 1);
    $pdf->Ln();
}

$pdf->Output();
