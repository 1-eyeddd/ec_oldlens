<?php
require_once('./config.php');
$err_msg[] = '';

$user_name = '';
$password = ''; 
session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    if (isset($_POST['user_name'])) {
        $user_name = htmlspecialchars($_POST["user_name"], ENT_QUOTES, 'UTF-8');
    }
    if (isset($_POST['password'])) {
        $password = htmlspecialchars($_POST["password"], ENT_QUOTES, 'UTF-8');
    }
    
    try {
        $dbh = new PDO(dsn, username, password, array(PDO::MYSQL_ATTR_INIT_COMMAND => charset));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        $sql= "SELECT * FROM ec_user WHERE user_name = ? AND password = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
        $stmt->bindValue(2, $password, PDO::PARAM_STR);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        if($rows){
            $_SESSION['user_id'] = $rows[0]['user_id'];
            $_SESSION['user_name'] = $rows[0]['user_name'];
            //topへリダイレクト
            header('Location: ./top.php');
            exit;
        } else {
          $err_msg[] = 'メールアドレス又はパスワードが間違っています。';
        }
      
    } catch (PDOException $e) {
       $err_msg[] =  '接続できませんでした。理由：' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel="stylesheet" href="./css/login.css">
    <link rel="shortcut icon" href="./images/logo.png" type="image/x-icon">
</head>
<body>
    <div id="home" class="big-bg">
        <header class="header-wrapper">
            <div class="header-top">
                <a href=""><img src="./images/logo.png" class="logo" alt=""></a>
                <h1>Old Lens Shop</h1>
            </div>
        </header>
        <main>
            <div class="form-wrapper">
                <?php foreach ($err_msg as $value) { ?>
                      <p class="err_msg"><?php print $value; ?></p>
                <?php } ?>
                <form action="./login.php" method="post">
                    <div class="form-item">
                        <label for="id"></label>
                        <input type="text" name="user_name" placeholder="ID">  
                    </div>
                    <div class="form-item">
                        <label for="password"></label>
                        <input type="password" name="password" placeholder="Password"></input>
                    </div>
                    <div class="login-btn">
                        <input type="submit" class="btn" value="Login"><br>
                        <div class="btn-new">
                            <a href="./register.php" >新規登録</a>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>