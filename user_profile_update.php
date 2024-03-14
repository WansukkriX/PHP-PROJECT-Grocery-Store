<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_POST['update_profile'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);

   $update_profile = $conn->prepare("UPDATE `users` SET name = ?, email = ? WHERE id = ?");
   $update_profile->execute([$name, $email, $user_id]);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;
   $old_image = $_POST['old_image'];

   if(!empty($image)){
      if($image_size > 2000000){
         $message[] = 'ขนาดรูปภาพใหญ่เกินไป!';
      }else{
         $update_image = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?");
         $update_image->execute([$image, $user_id]);
         if($update_image){
            move_uploaded_file($image_tmp_name, $image_folder);
            unlink('uploaded_img/'.$old_image);
            $message[] = 'อัพเดทรูปภาพเรียบร้อยแล้ว!';
         };
      };
   };

   $old_pass = $_POST['old_pass'];
   $update_pass = md5($_POST['update_pass']);
   $update_pass = filter_var($update_pass, FILTER_SANITIZE_STRING);
   $new_pass = md5($_POST['new_pass']);
   $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   $confirm_pass = md5($_POST['confirm_pass']);
   $confirm_pass = filter_var($confirm_pass, FILTER_SANITIZE_STRING);

   if(!empty($update_pass) AND !empty($new_pass) AND !empty($confirm_pass)){
      if($update_pass != $old_pass){
         $message[] = 'รหัสผ่านเก่าไม่ตรงกัน!';
      }elseif($new_pass != $confirm_pass){
         $message[] = 'ยืนยันรหัสผ่านไม่ตรงกัน!';
      }else{
         $update_pass_query = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
         $update_pass_query->execute([$confirm_pass, $user_id]);
         $message[] = 'อัพเดทรหัสผ่านเรียบร้อยแล้ว!';
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
   <title>update user profile</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/components.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="update-profile">

   <h1 class="title">แก้ไข้บัญชี</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
      <div class="flex">
         <div class="inputBox">
            <span>ชื่อผู้ใช้งาน:</span>
            <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" placeholder="แก้ไข้ชื่อผู้ใช้" required class="box">
            <span>อีเมล์ :</span>
            <input type="email" name="email" value="<?= $fetch_profile['email']; ?>" placeholder="แก้ไข้อีเมล์" required class="box">
            <span>รูปภาพ :</span>
            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
            <input type="hidden" name="old_image" value="<?= $fetch_profile['image']; ?>">
         </div>
         <div class="inputBox">
            <input type="hidden" name="old_pass" value="<?= $fetch_profile['password']; ?>">
            <span>รหัสผ่านเก่า :</span>
            <input type="password" name="update_pass" placeholder="ป้อนรหัสผ่านเก่า" class="box">
            <span>รหัสผ่านใหม่ :</span>
            <input type="password" name="new_pass" placeholder="ป้อนรหัสผ่านใหม่" class="box">
            <span>ยืนยันรหัสผ่าน:</span>
            <input type="password" name="confirm_pass" placeholder="ป้อนรหัสผ่านอีกครั้ง" class="box">
         </div>
      </div>
      <div class="flex-btn">
         <input type="submit" class="btn" value="แก้ไข้" name="update_profile">
         <a href="home.php" class="option-btn">กลับ</a>
      </div>
   </form>

</section>










<?php include 'footer.php'; ?>


<script src="js/script.js"></script>

</body>
</html>