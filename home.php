<?php

@include 'config.php';
header('Content-Type: text/html; charset=utf-8');
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];

   if(isset($_POST['add_to_wishlist'])){
      $pid = $_POST['pid'];
      $pid = filter_var($pid, FILTER_SANITIZE_STRING);
      $p_name = $_POST['p_name'];
      $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
      $p_price = $_POST['p_price'];
      $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
      $p_image = $_POST['p_image'];
      $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);

      $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$p_name, $user_id]);

      $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
      $check_cart_numbers->execute([$p_name, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         $message[] = 'เพิ่มลงในรายการสิ่งที่ต้องการแล้ว!';
      }elseif($check_cart_numbers->rowCount() > 0){
         $message[] = 'เพิ่มลงในตะกร้าแล้ว!';
      }else{
         $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
         $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
         $message[] = 'เพิ่มลงในรายการสิ่งที่ต้องการ';
      }
   }

   if(isset($_POST['add_to_cart'])){
      $pid = $_POST['pid'];
      $pid = filter_var($pid, FILTER_SANITIZE_STRING);
      $p_name = $_POST['p_name'];
      $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
      $p_price = $_POST['p_price'];
      $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
      $p_image = $_POST['p_image'];
      $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);
      $p_qty = $_POST['p_qty'];
      $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);

      $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
      $check_cart_numbers->execute([$p_name, $user_id]);

      if($check_cart_numbers->rowCount() > 0){
         $message[] = 'เพิ่มลงในตะกร้าแล้ว!';
      }else{

         $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
         $check_wishlist_numbers->execute([$p_name, $user_id]);

         if($check_wishlist_numbers->rowCount() > 0){
            $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
            $delete_wishlist->execute([$p_name, $user_id]);
         }

         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
         $message[] = 'เพิ่มลงในตะกร้าแล้ว!';
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>home page</title>

     <!-- font awesome cdn link  -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

     <!-- custom css file link  -->
     <link rel="stylesheet" href="css/style.css">


</head>

<body>

     <?php include 'header.php'; ?>

     <div class="home-bg">

          <section class="home">

               <div class="content">
                    <span>ยินดีต้อนรับสู่ร้านโชคชัย</span>
                    <h3>ช้อปของชำออนไลน์ สะดวก ส่งเร็ว ครบจบในร้านเดียว</h3>
                    <a href="shop.php" class="btn">ดูสิค้า</a>
               </div>

          </section>

     </div>

     <section class="home-category">

          <h1 class="title">หมวดหมู่ สินค้า</h1>

          <div class="box-container">
<!-- หมวดหมู่ -->
               <div class="box">
                    <img src="images/s1.jpg"  alt="">
                    
                    <a href="category.php?category=1" class="btn">ของใช้ในบ้านและสุขภาพ</a>
               </div>

               <div class="box">
                    <img src="images/as.png"  alt="">
                   
                    <a href="category.php?category=2" class="btn">ผลิตภัณฑ์สำหรับสัตว์เลี้ยง</a>
               </div>

               <div class="box">
                    <img src="images/as1.png"  alt="">
                   
                    <a href="category.php?category=3" class="btn">ผลิตภัณฑ์นมและเครื่องดื่ม</a>
               </div>

             

               <div class="box">
                    <img src="images/sd.png"  alt="">
                   
                    <a href="category.php?category=4" class="btn">ข้าวสารและแป้ง</a>
               </div>
          

          </div>
<!-- หมวดหมู่/ -->
     </section>

     <section class="products">

          <h1 class="title">สินค้าแนะนำ</h1>

          <div class="box-container">

               <?php
      $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 6");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
      ?>
               <form action="" class="box" method="POST">
                    <a href="">
                         <div class="price"> <span>฿ <?= $fetch_products['price']; ?></span></div>
                         <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                         
                         <div class="name"><?= $fetch_products['name']; ?></div>
                         <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">

                         <div class="product-title">
                              <input class="title" type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
                         </div>

                         <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
                         <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">
                         <input type="number" min="1" value="1" name="p_qty" class="qty" style="display:none" >
                         <div class="add">
                              <a href="view_page.php?pid=<?= $fetch_products['id']; ?>"
                                   class="option-btn">รายละเอียด</a>                        
                         </div>
                         <!-- <div class="add">
                               <input type="submit" value="🔍เพิ่มในตะกร้า" class="btn" name="add_to_cart">                     
                         </div> -->

                    </a>

               </form>
               <?php
      }
   }else{
      echo '<p class="empty">no products added yet!</p>';
   }
   ?>

          </div>

     </section>

     <?php include 'footer.php'; ?>

     <script src="js/script.js"></script>

</body>

</html>