<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
};

if(isset($_POST['add_product'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $category = $_POST['category'];
   $category = filter_var($category, FILTER_SANITIZE_STRING);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);
   $stock = $_POST['stock'];
   $stock = filter_var($stock, FILTER_SANITIZE_NUMBER_INT);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'ชื่อสินค้ามีอยู่แล้ว!';
   }else{

      $insert_products = $conn->prepare("INSERT INTO `products`(name, category, details, price, stock, image) VALUES(?,?,?,?,?,?)");
      $insert_products->execute([$name, $category, $details, $price, $stock, $image]);

      if($insert_products){
         if($image_size > 2000000){
            $message[] = 'image size is too large!';
         }else{
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'เพิ่มสินค้าใหม่แล้ว!';
         }

      }

   }

};

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $select_delete_image = $conn->prepare("SELECT image FROM `products` WHERE id = ?");
   $select_delete_image->execute([$delete_id]);
   $fetch_delete_image = $select_delete_image->fetch(PDO::FETCH_ASSOC);
   unlink('uploaded_img/'.$fetch_delete_image['image']);
   $delete_products = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_products->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   header('location:admin_products.php');


}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="add-products">

   <h1 class="title">เพิ่มสินค้าใหม่</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <input type="text" name="name" class="box" required placeholder="ชื่อสินค้า">
            <select name="category" class="box" required>
               <option value="" selected disabled>เลือกหมวดหมู่</option>
               <option value="1">ของใช้ในบ้านและสุขภาพ</option>
               <option value="2">ผลิตภัณฑ์สำหรับสัตว์เลี้ยง</option>
               <option value="3">ผลิตภัณฑ์นมและเครื่องดื่ม</option>
               <option value="4">ข้าวสารและแป้ง</option>
            </select>
         </div>
         <div class="inputBox">
            <input type="number" min="0" name="price" class="box" required placeholder="ราคา">
            <input type="number" min="0" name="stock" class="box" required placeholder="จำนวนสินค้า">
         </div>
      </div>
      <input type="file" name="image" required class=" box" style="width" accept="image/jpg, image/jpeg, image/png">

      <textarea name="details" class="box" required placeholder="รายละเอียดสินค้า" cols="30" rows="10"></textarea>
      <input type="submit" class="btn" value="เพิ่มสินค้า" name="add_product">
   </form>

</section>

<section class="show-products">

   <h1 class="title">สินค่าที่ถูกเพิ่ม</h1>

   <div class="box-container">

      <?php
         $show_products = $conn->prepare("SELECT * FROM `products`");
         $show_products->execute();
         if($show_products->rowCount() > 0){
            while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){  
      ?>
      <div class="box">
         <div class="price">฿ <?= $fetch_products['price']; ?> </div>
         <div class="stock">มีสินค้าทั้งหม <span><?= $fetch_products['stock']; ?> </span>ชิ้น </div>
         
         <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
         <div class="name"><?= $fetch_products['name']; ?></div>
         <div class="cat">
            <span>หมวดหมู่</span>
            <br>
   <?php
      $category_names = [
         1 => 'ของใช้ในบ้านและสุขภาพ',
         2 => 'ผลิตภัณฑ์สำหรับสัตว์เลี้ยง',
         3 => 'ผลิตภัณฑ์นมและเครื่องดื่ม',
         4 => 'ข้าวสารและแป้ง'
      ];
    
      // ใช้ค่าของ category ID ที่ดึงมาแล้วแมปไปยังชื่อหมวดหมู่
      $category_name = $category_names[$fetch_products['category']] ?? 'หมวดหมู่ไม่ระบุ';
      echo $category_name;
   ?>
</div>

         <!-- <div class="details"><?= $fetch_products['details']; ?></div> -->
         <div class="flex-btn">
            <a href="admin_update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">แก้ไข้</a>
            <a href="admin_products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('คุณต้องการลบสินค้านี้หรือไม่?');">ลบ</a>
         </div>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">ไม่มีสินค้าที่เพิ่มเข้ามาใหม่!</p>';
      }
      ?>

   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>











