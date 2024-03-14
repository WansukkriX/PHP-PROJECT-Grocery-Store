<?php
// เริ่ม session หากยังไม่เริ่มต้น
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบว่ามีค่า $_SESSION['user_id'] หรือไม่
if (!isset($_SESSION['user_id'])) {
    // หากไม่มีให้เรียกใช้ฟังก์ชันหรือโค้ดที่ทำให้ผู้ใช้เข้าสู่ระบบ
    // เช่น การเปลี่ยนเส้นทางไปหน้าล็อกอินหรือสร้างเซสชันใหม่
    header('Location: login.php');
    exit(); // ออกจากสคริปต์ทันทีหลังจาก redirect
}

// กำหนดค่า $user_id จาก $_SESSION
$user_id = $_SESSION['user_id'];

// เชื่อมต่อฐานข้อมูล
include 'config.php';

// ตรวจสอบการส่งคำขอเพิ่มสินค้าลงในตะกร้า
if(isset($_POST['add_to_cart'])){
   // รับค่าสินค้าจากฟอร์ม
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

   // ตรวจสอบว่าสินค้าอยู่ในตะกร้าแล้วหรือไม่
   $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_cart_numbers->rowCount() > 0){
      $message[] = 'already added to cart!';
   }else{
      // ตรวจสอบว่าสินค้าอยู่ในรายการสินค้าที่ชอบหรือไม่
      $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$p_name, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         // หากอยู่ในรายการสินค้าที่ชอบ ลบออกจากรายการสินค้าที่ชอบ
         $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
         $delete_wishlist->execute([$p_name, $user_id]);
      }

      // เพิ่มสินค้าลงในตะกร้า
      $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'added to cart!';
   }
}

// ตรวจสอบการส่งคำขอลบรายการสินค้าที่ชอบออกจากรายการ
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
   $delete_wishlist_item->execute([$delete_id]);
   header('location:wishlist.php');
}

// ตรวจสอบการส่งคำขอลบรายการสินค้าที่ชอบทั้งหมดออกจากรายการ
if(isset($_GET['delete_all'])){
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist_item->execute([$user_id]);
   header('location:wishlist.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>wishlist</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="wishlist">

   <h1 class="title">สินค้าที่ถูกเพิ่มลงในรายการ</h1>

   <div class="box-container">

   <?php
      $grand_total = 0;
      $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
      $select_wishlist->execute([$user_id]);
      if($select_wishlist->rowCount() > 0){
         while($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" method="POST" class="box">
      <a href="wishlist.php?delete=<?= $fetch_wishlist['id']; ?>" class="fas fa-times" onclick="return confirm('delete this from wishlist?');"></a>
      <a href="view_page.php?pid=<?= $fetch_wishlist['pid']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_wishlist['image']; ?>" alt="">
      <div class="name"><?= $fetch_wishlist['name']; ?></div>
      <div class="price">$<?= $fetch_wishlist['price']; ?>/-</div>
      <input type="number" min="1" value="1" class="qty" name="p_qty">
      <input type="hidden" name="pid" value="<?= $fetch_wishlist['pid']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_wishlist['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_wishlist['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_wishlist['image']; ?>">
      <input type="submit" value="add to cart" name="add_to_cart" class="btn">
   </form>
   <?php
      $grand_total += $fetch_wishlist['price'];
      }
   }else{
      echo '<p class="empty">รายการสิ่งที่คุณต้องการว่างเปล่า</p>';
   }
   ?>
   </div>

   <div class="wishlist-total">
      <p>รวมทั้งสิ้น : <span>฿<?= $grand_total; ?> -</span></p>
      <a href="shop.php" class="option-btn">เลือกสินค้า</a>
      <a href="wishlist.php?delete_all" class="delete-btn <?= ($grand_total > 1)?'':'disabled'; ?>">ลบทั้งหมด</a>
   </div>

</section>








<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>