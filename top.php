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

try {
   // データベースに接続
    $dbh = new PDO(dsn, username, password, array(PDO::MYSQL_ATTR_INIT_COMMAND => charset));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    if(isset($_POST['scene_type'])){
        $scene = $_POST['scene_type'];
    }
    //一覧表示
    $sql = 'SELECT items.item_id,name,price,img,status,items.scene,description,stock,type_name
            FROM items
            JOIN items_stock ON items.item_id = items_stock.item_id
            JOIN scene_type ON items.scene = scene_type.scene
            WHERE status = 1';
            //検索表示
            if ($scene !== '') {
                $sql .= ' AND items.scene = ' . $scene;
            }
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
    <title>Old Lens Shop</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel="stylesheet" href="./css/itemlist.css">
    <link rel="shortcut icon" href="./images/logo.png" type="image/x-icon">
</head>
<body>
    <div id="home" class="big-bg">
        <header class="header-wrapper">
            <div class="header-top">
                <a href="./top.php"><img src="./images/logo.png" class="logo" alt="ロゴ"></a>
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
            <div class="home-top">
                <h2>Old Lens Shop</h2>
            </div>
            <div class="about">
                <h3>about</h3>
                <p>オールドレンズはデジタルカメラのレンズに比べ、彩度やコントラストが弱く柔らかで幻想的な描写になります。そんなオールドレンズを厳選してご紹介しています。</p>
            </div>
                <div class="content">
                    <h3>items</h3>
                    <?php foreach ($data as $read) { ?>
                    <ul class="item-list">
                        <li>
                          <div class="item">
                            <a href="<?php echo "./itemDetail.php?item_id=".htmlspecialchars($read['item_id'], ENT_QUOTES, "UTF-8")?>" class="detail-link">
                              <img class="item-img" src="<?php print $img_file.$read['img']; ?>" >
                              <div class="item-info">
                                <span class="item-name"><?php print htmlspecialchars($read['name'], ENT_QUOTES, 'UTF-8'); ?></span><br>
                                <span class="item-price">&yen;<?php print htmlspecialchars($read['price'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php if((int)$read['stock'] === 0){ ?>
                                    <span class="red">売り切れ</span>
                                <?php } ?>
                              </div>
                            </a>
                          </div>
                        </li>
                      </ul>
                      <?php } ?>
                    </div>
                </div>
            <div class="scene">
                <h3>scene</h3>
                <ul class="scene-list">
                    <form method="POST">
                        <li>
                            <input type="hidden" name="scene_type" value="0">
                            <input type="image" src="./images/scene1.jpg">
                        </li>
                    </form>
                    <form method="POST">
                        <li>
                            <input type="hidden" name="scene_type" value="1">
                            <input type="image" src="./images/scene2.jpg">
                        </li>
                    </form>
                    <form method="POST">
                        <li>
                            <input type="hidden" name="scene_type" value="2">
                            <input type="image" src="./images/scene3.jpg">
                        </li>
                    </form>
                </ul>
            </div>
        </main>
        <footer>
            <div class="footer">
                <h4>Old Lens Shop</h4>
            </div>
        </footer>
    </div>
</body>
</html>