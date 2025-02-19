<?php
require('fpdf/fpdf.php');  // รวม FPDF

@include 'config.php';  // เชื่อมต่อฐานข้อมูล

session_start();

$user_id = $_SESSION['user_id'];

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if(!isset($user_id)){
   header('location:login.php');
   exit();
}

// ตรวจสอบว่าได้รับ order_id จาก URL หรือไม่
if(isset($_GET['order_id'])){
    $order_id = $_GET['order_id'];

    // ดึงข้อมูลคำสั่งซื้อจากฐานข้อมูล
    $select_order = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? AND id = ?");
    $select_order->execute([$user_id, $order_id]);
    $order = $select_order->fetch(PDO::FETCH_ASSOC);

    if($order){
        // สร้าง PDF ใบเสร็จ
        $pdf = new FPDF();
        $pdf->AddPage();

        // เพิ่มฟอนต์ที่แปลงแล้วสำหรับภาษาไทย (เช่น THSarabunNew)
        $pdf->AddFont('sarabun', '', 'THSarabunNew.php');  // ฟอนต์ภาษาไทย
        $pdf->SetFont('sarabun', '', 16);  // ขนาดฟอนต์

        // เพิ่มข้อมูลใบเสร็จ (ใช้ iconv แปลงเป็น cp874)
        $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'ใบเสร็จรับเงิน'), 0, 1, 'C');
        $pdf->Ln(10);  // เพิ่มระยะห่าง

        $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'วันที่วางสั่งซื้อ: ' . $order['placed_on']), 0, 1);
        $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'ชื่อ: ' . $order['name']), 0, 1);
        $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'เบอร์โทรศัพท์: ' . $order['number']), 0, 1);
        $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'อีเมล์: ' . $order['email']), 0, 1);
        $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'ที่อยู่: ' . $order['address']), 0, 1);
        $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'ช่องทางชำระเงิน: ' . $order['method']), 0, 1);
        $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'คำสั่งซื้อของคุณ: ' . $order['total_products']), 0, 1);
        $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'ราคารวม: ฿ ' . $order['total_price']), 0, 1);
        $pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'สถานะการชำระเงิน: ' . $order['payment_status']), 0, 1);

        // สร้างไฟล์ PDF และส่งให้ดาวน์โหลด
        $pdf->Output('D', 'order_' . $order_id . '.pdf');  // 'D' คือการดาวน์โหลด
    } else {
        echo "ไม่พบคำสั่งซื้อนี้";
    }
} else {
    echo "ไม่พบคำสั่งซื้อ";
}
?>
