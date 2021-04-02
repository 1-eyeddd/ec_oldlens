<?php
require_once('./config.php');
require_once('./common.php');
$img_file = "./admin_imgs/";
//エラーメッセージ
$success_msg = [];
$err_msg = [];

$data = [];
$total  = 0;
session_start();
check_user_login();
//セッションからユーザーID取得
$user_id = $_SESSION['user_id'];

try {
   // データベースに接続
    $dbh = new PDO(dsn, username, password, array(PDO::MYSQL_ATTR_INIT_COMMAND => charset));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        
        $sql = 'SELECT 
              items.item_id,name,price,img,cart.amount,stock
            FROM 
              items
            JOIN
              cart
            ON
              items.item_id = cart.item_id
            JOIN
              items_stock
            ON
              items.item_id = items_stock.item_id 
            WHERE user_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id,PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        foreach( $data as $item ){
            if($item['amount'] > $item['stock']){
                $err_msg[] = $item['name'].'の在庫が不足しています。';
            }
            $total += $item['price']*$item['amount'];
        }
       if(count($data) === 0){
           $err_msg[] = 'カート内に商品がありません。';
       }
       if(count($err_msg) === 0){
          $dbh->beginTransaction();
            try {
                $sql = 'DELETE FROM cart WHERE user_id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $user_id,PDO::PARAM_INT);
                $stmt->execute();
                
               foreach($data as $item){
                   $sql = 'UPDATE items_stock SET stock = stock-? ,update_datetime = now() WHERE item_id = ?;';
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(1, $item['amount'],    PDO::PARAM_INT);
                    $stmt->bindValue(2, $item['item_id'],    PDO::PARAM_INT);
                    $stmt->execute();
                   
               }
                
               $dbh->commit();
               
            } catch (PDOException $e) {
               $dbh->rollback();
              throw $e;
            }
        }
    } else {
        $err_msg[] = '不正なアクセスです。';
    }
} catch (PDOException $e) {
   $err_msg[] = '接続できませんでした。理由：' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>購入完了</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel="stylesheet" href="./css/cart.css">
    <link rel="shortcut icon" href="./images/logo.png" type="image/x-icon">
</head>
<body>
    <div id="home" class="big-bg">
        <header class="header-wrapper">
            <div class="header-top">
                <a href=""><img src="./images/logo.png" class="logo" alt=""></a>
                <h1>Old Lens Shop</h1>
            </div>
            <nav>
                <ul class="main-nav">
                    <li><a href="./top.php">Home</a></li>
                    <li><a href="">Category</a></li>
                    <li><a href="">Blog</a></li>
                    <li><a href="">Contact</a></li>
                    <li><a href="logout.php">Log out</a></li>
                </ul>
                <a href="cart.php"><img src="./images/icon_123370_256.png" alt="カート"></a>
            </nav>
        </header>
        <main>
            <?php if(count($err_msg) === 0){ ?>
               <div class="content">
                   <h1 class="finishMsg">ご購入ありがとうございました。</h1>
                    <div class="cart-list-title">
                        <span class="cart-list-price">価格</span>
                        <span class="cart-list-num">数量</span>
                    </div>
                    <?php foreach( $data as $read ) { ?>
                        <ul class="cart-list">
                            <li>
                                <div class="cart-item">
                                    <img class="cart-item-img" src="<?php print $img_file . $read['img']; ?>">
                                    <span class="cart-item-name"><?php print htmlspecialchars($read['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="cart-item-price-finish"><?php print htmlspecialchars($read['price'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="cart-item-num2"><?php print htmlspecialchars($read['amount'], ENT_QUOTES, 'UTF-8'); ?>個&nbsp;</span>
                                </div>
                            </li>
                        </ul>
                    <?php } ?>
                    <div class="buy-sum-box">
                        <span class="buy-sum-title">合計</span>
                        <span class="buy-sum-price"><?php echo number_format($total)  ?></span>
                    </div>
               </div>
           <?php } ?>
           <?php foreach($err_msg as $err){ ?>
               <p><?php print htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></p>
           <?php } ?>
        </main>
    </div>
</body>
</html>