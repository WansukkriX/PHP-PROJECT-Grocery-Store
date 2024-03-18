<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
   <title>orders</title>
   
   <style>
      @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap');
      *{
         font-family: "Noto Sans Thai", sans-serif;
      }
   </style>
 
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

  
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="placed-orders">

   <h1 class="title">รายการสั่งซื้อ</h1>

   <div class="box-container">

   <?php
      $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
      $select_orders->execute([$user_id]);
      if($select_orders->rowCount() > 0){
         while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <div class="box">
      <p> วันที่วางสั่งซื้อ : <span><?= $fetch_orders['placed_on']; ?></span> </p>
      <p> ชื่อ : <span><?= $fetch_orders['name']; ?></span> </p>
      <p> เบอร์โทรศัพท์ : <span><?= $fetch_orders['number']; ?></span> </p>
      <p> อีเมล์: <span><?= $fetch_orders['email']; ?></span> </p>
      <p> ที่อยู่ : <span><?= $fetch_orders['address']; ?></span> </p>
      <p> ช่องทางชำระเงิน : <span><?= $fetch_orders['method']; ?></span> </p>
      <p> คำสั่งซื้อของคุณ : <span><?= $fetch_orders['total_products']; ?></span> </p>
      <p> ราคารวม : <span>฿<?= $fetch_orders['total_price']; ?> -</span> </p>
      <p> สถานะการชำระเงิน : <span style="color:<?php if($fetch_orders['payment_status'] == 'รอดำเนินการ'){ echo 'red'; }else{ echo 'green'; }; ?>"><?= $fetch_orders['payment_status']; ?></span> </p>
   </div>
   <?php
      }
   }else{
      echo '<p class="empty">ยังไม่มีการสั่งซื้อใดๆ!</p>';
   }
   ?>

   </div>

</section>









<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>