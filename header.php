<?php

@include 'config.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// แสดงข้อความหากมีการส่งข้อความมาจากต้นทางอื่น
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

   <style>
   @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap');
   *{
      font-family: "Noto Sans Thai", sans-serif;
   }
   .icons{
      position: relative;
   }

   .icons span{
      font-weight:500;
      position:absolute;
      top: -5px;
      color:var(--light-color);
      
   }

  
   </style>

<header class="header">

   <div class="flex">

      <a href="admin_page.php" class="logo">ร้าน<span>โชคชัย</span></a>

    <nav class="navbar">
    <a href="home.php">หน้าแรก</a>
    <a href="shop.php">สินค้า</a>
    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="orders.php">คำสั่งซื้อ</a>
    <?php endif; ?>
    <!-- <a href="about.php">เกี่ยวกับ</a> -->
    <a href="contact.php">ติดต่อ</a>
</nav>

      <div class="icons">
   
         <div id="menu-btn" class="fas fa-bars"></div>
         
         <div id="user-btn" class="fas fa-user"></div>
        

         <?php
            // ตรวจสอบการเข้าสู่ระบบและนับจำนวนรายการในตะกร้าและรายการที่ชอบ
            if($user_id !== null) {
               $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
               $count_cart_items->execute([$user_id]);
               $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
               $count_wishlist_items->execute([$user_id]);
         ?>
            <!-- แสดงปุ่มรายการที่ชอบและรายการในตะกร้า -->
            <!-- <a href="wishlist.php"><i class="fas fa-heart"></i><span>(<?= $count_wishlist_items->rowCount(); ?>)</span></a> -->
            <a href="cart.php"><i class="fas fa-shopping-cart"></i><span style="color:red"><?= $count_cart_items->rowCount(); ?></span></a>
         <?php
            } // ปิดเงื่อนไขการเข้าสู่ระบบ
         ?>
      </div>

      <div class="profile">
         <?php
            if($user_id !== null) {
               // เข้าสู่ระบบแล้ว แสดงโปรไฟล์ผู้ใช้
               $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
               $select_profile->execute([$user_id]);
               $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
            <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
            <p><?= $fetch_profile['name']; ?></p>
            <a href="user_profile_update.php" class="btn">จัดการโปรไฟล์</a>
            <a href="logout.php" class="delete-btn">ออกจากระบบ</a>
         <?php
            } else {
               // ยังไม่เข้าสู่ระบบ
         ?>
            <!-- แสดงปุ่มเข้าสู่ระบบและสมัคร -->
            <div class="flex-btn">
               <a href="login.php" class="option-btn">เข้าสู่ระบบ</a>
               <a href="register.php" class="option-btn">สมัคร</a>
            </div>
         <?php
            } // ปิดเงื่อนไขการเข้าสู่ระบบ
         ?>
      </div>

   </div>

</header>
