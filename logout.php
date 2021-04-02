<?php

session_start();
$session_name = session_name();
$_SESSION = array();

if (isset($_COOKIE[$session_name])) {

  setcookie($session_name, '', time() - 42000);
  
}
session_destroy();
// ログアウトの処理が完了したらログインページへリダイレクト
header('Location: login.php');
exit;
?>