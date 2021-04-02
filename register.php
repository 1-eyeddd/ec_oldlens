<?php
require_once('./config.php');
$user_name = "";
$password = "";
//エラーメッセージ
$success_msg = [];
$err_msg = [];

$data = [];
try {
   // データベースに接続
    $dbh = new PDO(dsn, username, password, array(PDO::MYSQL_ATTR_INIT_COMMAND => charset));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
   
   //ユーザー登録
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       
      if (isset($_POST['user_name']) === true) {
         $user_name = $_POST['user_name'];
      }
      if (isset($_POST['password']) === true) {
         $password = $_POST['password'];
      }
      
      //IDエラーチェック
      if ($user_name === '') {
        $err_msg[] = 'IDを入力してください。';
      }  else if (preg_match("/^[a-zA-Z0-9]+$/", $user_name) === false) {
        $err_msg[]= 'IDは半角英数字で入力してください';
      }  else if (mb_strlen($user_name) < 6){
        $err_msg[] = 'IDは６文字以上で入力してください';
      } 
      //pwエラーチェック
      if ($password === '') {
        $err_msg[] = 'パスワードを入力してください。';
      }  else if (preg_match("/^[a-zA-Z0-9]+$/", $password) === false) {
        $err_msg[]= 'パスワードは半角英数字で入力してください';
      }  elseif (mb_strlen($password) < 6){
        $err_msg[] = 'パスワードは６文字以上で入力してください';
      } 
      //重複チェック
        $sql = 'SELECT COUNT(*) AS cnt FROM ec_user WHERE user_name=?';
        $stmt = $dbh->prepare($sql);
        $stmt->execute(array($_POST['user_name']));
        $res = $stmt->fetch();
        if($res['cnt'] > 0){
            $err_msg[] = 'このIDは既に使用されています。';
        }
      //エラーがなければユーザー情報をsqlに入れる
        if (count($err_msg) === 0){
           $sql = 'INSERT INTO ec_user (user_name,password,create_datetime,update_datetime) 
                   VALUES(?, ?, now(), now())';
           $stmt = $dbh->prepare($sql);
           $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
           $stmt->bindValue(2, $password, PDO::PARAM_STR);
           $stmt->execute();
           $success_msg[] = '登録が完了しました。';
        }
    }  
} catch (PDOException $e) {
    echo 'データベース処理でエラーが発生しました。理由：'.$e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規登録</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel="stylesheet" href="./css/login.css">
    <link rel="shortcut icon" href="./images/logo.png" type="image/x-icon">
</head>
<body>
    <div id="home" class="big-bg">
        <header class="header-wrapper">
            <div class="header-top">
                <a href="./top.php"><img src="./images/logo.png" class="logo" alt=""></a>
                <h1>Old Lens Shop</h1>
            </div>
        </header>
        <main>
            <div class="form-wrapper">
                <?php foreach ($err_msg as $value) { ?>
                      <p class="err_msg"><?php print $value; ?></p>
                <?php } ?>
                <?php if(count($success_msg) === 0) { ?>
                <form method="POST" action="./register.php">
                    <div class="form-item">
                        <label for="id"></label>
                        <input type="text" name="user_name" placeholder="ID">  
                    </div>
                    <div class="form-item">
                        <label for="password"></label>
                        <input type="password" name="password" placeholder="Password"></input>
                    </div>
                    <div class="login-btn">
                        <div class="btn-new">
                            <input type="submit" class="regi" value="新規登録">
                        </div>
                    </div>
                </form>
                <?php } else { ?>
                <p class="success_msg">登録が完了しました！</p>
                    <div class="btn-to-top">
                        <a href="./login.php">ログイン画面に戻る</a>
                    </div>
                <?php  } ?>
            </div>
        </main>
    </div>
</body>
</html>