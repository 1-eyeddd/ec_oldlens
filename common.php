<?php

function check_user_login(){
    if (isset($_SESSION['user_id'])) {
      $user_id = $_SESSION['user_id'];
    } else {
      // 非ログインの場合、ログインページへリダイレクト
      header('Location: login.php');
      exit;
    }
}

?>