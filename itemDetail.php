<?php
require_once('./config.php');
require_once('./common.php');

$img_file = "./admin_imgs/";
$scene = "";
//エラーメッセージ
$success_msg = [];
$err_msg = [];

$data = [];

session_start();
check_user_login();

$item_no = '';
if (isset($_GET['item_id'])) {
    $item_no = htmlspecialchars($_GET["item_id"], ENT_QUOTES, 'UTF-8');
}
try {
   // データベースに接続
    $dbh = new PDO(dsn, username, password, array(PDO::MYSQL_ATTR_INIT_COMMAND => charset));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

   //詳細ページを表示する
    $sql = "SELECT 
              items.item_id,items.name,items.price,items.img,items.status,items.scene,items.description,items_stock.stock,scene_type.type_name
            FROM 
              items
            JOIN
              items_stock
            ON
              items.item_id = items_stock.item_id
            JOIN
              scene_type
            ON
              items.scene = scene_type.scene
            WHERE
              items.item_id = {$item_no}";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll();
    
} catch (PDOException $e) {
   echo '接続できませんでした。理由：' . $e->getMessage();
   
}

 
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品詳細</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel="stylesheet" href="./css/detail.css">
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
            <form action="./cart.php" method="post">
                <div class="itemWrap">
                    <?php foreach( $data as $read ) { ?>
                        <div class="purchaseWrap">
                            <div class="item-img">
                                <img class="item-main-img" src="<?php print $img_file . $read['img']; ?>" alt="">
                            </div>
                            <div class="purchaseDiscription">
                                <div class="itemTitle">
                                    <h2><?php print htmlspecialchars($read['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                    <h3>&yen;<?php print htmlspecialchars($read['price'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                </div>
                                <p><?php print htmlspecialchars($read['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p>scene：<?php print htmlspecialchars($read['type_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <div class="purchaseBtn">
                                    <?php if((int)$read['stock'] === 0){ ?>
                                    <span class="red">売り切れ</span>
                                    <?php } else {?>
                                        <input type="hidden" name="item_id" value="<?php print htmlspecialchars($read['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="submit" class="addToCart" value="カートに入れる">
                                        <input type="hidden" name="sql_kind" value="insert_cart">
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </form>
        </main>
    </div>
</body>
</html>