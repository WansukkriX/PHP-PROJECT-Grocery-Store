<?php
require('fpdf/fpdf.php');  // รวม FPDF

$pdf = new FPDF();
$pdf->AddPage();

// เพิ่มฟอนต์ที่แปลงแล้ว
$pdf->AddFont('sarabun', '', 'THSarabunNew.php'); // ใช้ฟอนต์ที่แปลงแล้ว
$pdf->SetFont('sarabun', '', 16);  // ตั้งฟอนต์และขนาดฟอนต์

// เพิ่มข้อความภาษาไทย
$pdf->Cell(0, 10,  iconv('utf-8', 'cp874', 'วิธีการสร้างไฟล์ PDF ด้วย PHP'), 0, 1, 'C');

// สร้างไฟล์ PDF และแสดงในเบราว์เซอร์
$pdf->Output('I', 'test_pdf.pdf');
?>
