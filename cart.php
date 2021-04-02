<?php
require_once('./config.php');
require_once('./common.php');
$img_file = "./admin_imgs/";
$scene = "";
//エラーメッセージ
$success_msg = [];
$err_msg = [];

$total = 0;

$data = [];

session_start();
check_user_login();
//セッションからユーザーID取得
$user_id = $_SESSION['user_id'];

try {
   // データベースに接続
    $dbh = new PDO(dsn, username, password, array(PDO::MYSQL_ATTR_INIT_COMMAND => charset));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    $sql_kind = "";
    if(isset($_POST['sql_kind'])){
        $sql_kind = $_POST['sql_kind'];
    }
    $item_id = "";
    if(isset($_POST['item_id'])){
        $item_id = $_POST['item_id'];
    }
    //カート内に商品を追加したとき
    if($sql_kind === 'insert_cart'){
        
        $sql = "SELECT * FROM cart WHERE user_id = ? AND item_id = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id,PDO::PARAM_INT);
        $stmt->bindValue(2, $item_id,PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        if($row === false){
            $sql = "INSERT INTO cart (user_id, item_id, amount, create_datetime, update_datetime) VALUES (?, ?, 1, now(),now())";
        } else {
            $sql = "UPDATE cart SET amount = amount+1, update_datetime = now() WHERE user_id = ? AND item_id = ?";
        }
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id,PDO::PARAM_INT);
        $stmt->bindValue(2, $item_id,PDO::PARAM_INT);
        $stmt->execute();
        
    //カート内の商品を削除したとき
    } else if ($sql_kind === 'delete_cart') {
        
        $sql = "DELETE FROM cart WHERE user_id = ? AND item_id = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id,PDO::PARAM_INT);
        $stmt->bindValue(2, $item_id,PDO::PARAM_INT);
        $stmt->execute();
        
    //カート内の商品の数を変更したとき
    } else if ($sql_kind === 'change_cart') {
        
        $amount = "";
        if(isset($_POST['amount'])){
            $amount = $_POST['amount'];
        }
        
        $sql = "UPDATE cart SET amount = ?, update_datetime = now() WHERE user_id = ? AND item_id = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $amount,    PDO::PARAM_INT);
        $stmt->bindValue(2, $user_id,PDO::PARAM_INT);
        $stmt->bindValue(3, $item_id,PDO::PARAM_INT);
        $stmt->execute();
        
    }
   
   $sql = 'SELECT 
              items.item_id,name,price,img,cart.amount
            FROM 
              items
            JOIN
              cart
            ON
              items.item_id = cart.item_id
            WHERE user_id = ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $user_id,PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll();
    
    foreach( $data as $item ){
        $total += $item['price']*$item['amount'];
    }
   

} catch (PDOException $e) {
   echo '接続できませんでした。理由：' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>カート内</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel="stylesheet" href="./css/cart.css">
    <link rel="shortcut icon" href="./images/logo.png" type="image/x-icon">
</head>
<body>
    <!------------------------------------ヘッダー---------------------------------->
    <div id="home" class="big-bg">
        <header class="header-wrapper">
            <div class="header-top">
                <a href="./top.php"><img src="./images/logo.png" class="logo" alt=""></a>
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
    <!-------------------カート内----------------------->
        <main>
            <div class="back-itemlist-btn">
                   <a href="./top.php">ショッピングを続ける</a>
            </div>
           <div class="content">
            <h1 class="title">ショッピングカート</h1>
                <?php if(count($data) > 0){ ?>
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
                                    <form class="cart-item-del" action="./cart.php" method="post">
                                        <input type="submit" value="削除">
                                        <input type="hidden" name="item_id" value="<?php print htmlspecialchars($read['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="sql_kind" value="delete_cart">
                                    </form>
                                    <span class="cart-item-price"><?php print htmlspecialchars(number_format($read['price']), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <form class="form_select_amount" action="./cart.php" method="post">
                                        <input type="text" class="cart-item-num2" min="0" name="amount" value="<?php print htmlspecialchars($read['amount'], ENT_QUOTES, 'UTF-8'); ?>" size="5">個&nbsp;<input type="submit" value="変更する">
                                        <input type="hidden" name="item_id" value="<?php print htmlspecialchars($read['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="sql_kind" value="change_cart">
                                    </form>
                                </div>
                            </li>
                        </ul>
                    <?php } ?>
                    <div class="buy-sum-box">
                        <span class="buy-sum-title">合計</span>
                        <span class="buy-sum-price"><?php echo number_format($total)  ?></span>
                    </div>
                    <div>
                        <form action="./finish.php" method="post">
                            <input class="buy-btn" type="submit" value="購入する">
                        </form>
                    </div>
                <?php } else {?>
                <p class="not-in-cart">現在カート内に商品はありません</p>
                <?php } ?>
           </div>
        </main>
    </div>
</body>
</html>