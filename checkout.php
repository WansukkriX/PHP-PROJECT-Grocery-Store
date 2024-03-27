<?php

@include 'config.php';

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_POST['order'])){
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $number = $_POST['number'];
    $number = filter_var($number, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $method = $_POST['method'];
    $method = filter_var($method, FILTER_SANITIZE_STRING);
    // Separate address components and format them correctly
    $flat = $_POST['flat'];
    $street = ($_POST['street'] ?? '');
    $city = $_POST['city'];
    $state = $_POST['state'];
    $country = $_POST['country'];
    $pin_code = $_POST['pin_code'];
    // Combine address components into a single string
    $address = "$flat $street $city $state $country  $pin_code";
    $address = filter_var($address, FILTER_SANITIZE_STRING);
    $placed_on = date('d/m/Y');

    $cart_total = 0;
    $cart_products[] = '';

    $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $cart_query->execute([$user_id]);
    
    if($cart_query->rowCount() > 0){
        while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
            $cart_products[] = $cart_item['name'].' ( '.$cart_item['quantity'].' )';
            $sub_total = ($cart_item['price'] * $cart_item['quantity']);
            $cart_total += $sub_total;
        };
    };

    $total_products = implode(', ', $cart_products);

    if($cart_total == 0){
        $message[] = 'ตะกร้าของคุณว่างเปล่า';
    } else {
        $order_query = $conn->prepare("SELECT * FROM `orders` WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
        $order_query->execute([$name, $number, $email, $method, $address, $total_products, $cart_total]);

        if($order_query->rowCount() > 0) {
            $message[] = '<span style="color:green;" >คำสั่งซื้อได้ทำการส่งแล้ว!</span>';
        } elseif($cart_total < 0) {
            $message[] = 'จำนวนสินค้าไม่ถูกต้อง';
        } elseif($cart_total == 0) {
            $message[] = 'สินค้าหมด!';
        } else {
         
            $out_of_stock = false;
            $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $cart_query->execute([$user_id]);
            if($cart_query->rowCount() > 0){
                while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
                    $product_id = $cart_item['pid'];
                    $quantity_ordered = $cart_item['quantity'];

                 
                    $product_query = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
                    $product_query->execute([$product_id]);
                    if($product_query->rowCount() > 0){
                        $product_data = $product_query->fetch(PDO::FETCH_ASSOC);
                        $available_quantity = $product_data['stock'];

                        if($quantity_ordered > $available_quantity){
                            $out_of_stock = true;
                            break;
                        }
                    }
                }
            }

            if($out_of_stock){
                $message[] = 'สินค้าบางรายการหมด!';
            } else {
                $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status) VALUES(?,?,?,?,?,?,?,?,?,?)");
                $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on, 'รอดำเนินการ']);

                // Update stock quantities after placing order
                $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $cart_query->execute([$user_id]);
                if($cart_query->rowCount() > 0){
                    while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
                        $product_id = $cart_item['pid'];
                        $quantity_ordered = $cart_item['quantity'];

                        // Update stock quantities
                        $product_query = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
                        $product_query->execute([$product_id]);
                        if($product_query->rowCount() > 0){
                            $product_data = $product_query->fetch(PDO::FETCH_ASSOC);
                            $available_quantity = $product_data['stock'];

                            // Calculate remaining stock after order
                            $remaining_quantity = $available_quantity - $quantity_ordered;

                            // Update stock quantities
                            $update_stock = $conn->prepare("UPDATE `products` SET stock = ? WHERE id = ?");
                            $update_stock->execute([$remaining_quantity, $product_id]);
                        }
                    }
                }

                // Delete items from cart after placing order
                $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
                $delete_cart->execute([$user_id]);
                $message[] = '<span style="color:green;"> สั่งซื้อเรียบร้อยแล้ว! </span> ';
            }                 
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
   <title>checkout</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="display-orders">

   <?php
      $cart_grand_total = 0;
      $select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart_items->execute([$user_id]);
      if($select_cart_items->rowCount() > 0){
         while($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)){
            $cart_total_price = ($fetch_cart_items['price'] * $fetch_cart_items['quantity']);
            $cart_grand_total += $cart_total_price;
   ?>
   <p> <?= $fetch_cart_items['name']; ?> <span>(<?= '฿ '.$fetch_cart_items['price'].'  x'. $fetch_cart_items['quantity']; ?>)</span> ชิ้น </p>
   <?php
    }
   }else{
      echo '<p class="empty">ตะกร้าของคุณว่างเปล่า!</p>';
   }
   ?>
   <div class="grand-total">รวมทั้งสิ้น : <span>฿ <?= $cart_grand_total; ?></span></div>
</section>

<section class="checkout-orders">

   <form action="" method="POST">

      <h3>สั่งสินค้าของคุณ</h3>

      <div class="flex">
         <div class="inputBox">
            <span>ชื่อของคุณ:</span>
            <input type="text" name="name" placeholder="ป้อนชื่อของคุณ" class="box" required>
         </div>
         <div class="inputBox">
            <span>เบอร์โทรศัพท์:</span>
            <input type="number" name="number" placeholder="ป้อนหมายเลขโทรศัพท์" class="box" required>
         </div>
         <div class="inputBox">
            <span>อีเมล์:</span>
            <input type="email" name="email" placeholder="ป้อนอีเมล์ของคุณ" class="box" required>
         </div>
         <div class="inputBox">
            <span>วิธีการชำระเงิน :</span>
            <select name="method" class="box" required>
               <option value="เก็บเงินปลายทาง">เก็บเงินปลายทาง</option>
               <option value="บัตรเครดิต card">บัตรเครดิต</option>
               <option value="ชำระเงินผ่านมือถือ">ชำระเงินผ่านมือถือ</option>
               <option value="paypal">paypal</option>
            </select>
         </div>
         <div class="inputBox">
            <span>ที่อยู่ :</span>
            <input type="text" name="flat" placeholder="ป้อนที่อยู่ของคุณ" class="box" required>
         </div>
         <!-- <div class="inputBox">
            <span>address line 02 :</span>
            <input type="text" name="street" placeholder="e.g. street name" class="box" required>
         </div> -->
         <div class="inputBox">
            <span>เมือง:</span>
            <input type="text" name="city" placeholder="ป้อนเมืองของคุณ" class="box" required>
         </div>
         <div class="inputBox">
            <span>จังหวัด :</span>
            <input type="text" name="state" placeholder="ป้อนจังหวัดของคุณ" class="box" required>
         </div>
         <div class="inputBox">
            <span>ประเทศ :</span>
            <input type="text" name="country" placeholder="ป้อนประเทศของคุณ" class="box" required>
         </div>
         <div class="inputBox">
            <span>รหัสไปรษณีย์:</span>
            <input type="number" min="0" name="pin_code" placeholder="รหัสไปรษณีย์" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($cart_grand_total > 1)?'':'disabled'; ?>" value="ส่งคำสั่งซื้อ">

   </form>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
