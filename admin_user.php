<?php
require_once('./config.php');
$data = [];

try {
       // データベースに接続
        $dbh = new PDO(dsn, username, password, array(PDO::MYSQL_ATTR_INIT_COMMAND => charset));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
       //一覧表示
        $sql = 'SELECT user_name,create_datetime FROM ec_user';
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo 'データベース処理でエラーが発生しました。理由：'.$e->getMessage();
    }
    
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザー管理</title>
    <link rel="stylesheet" href="./css/admin.css">
    <link rel="shortcut icon" href="./images/logo.png" type="image/x-icon">
</head>
<body>
    <h1>Old Lens Shop管理ページ</h1>
    <a href="./admin.php">商品管理ページ</a>
    <h2>ユーザー情報一覧</h2>
    <table>
        <tr>
            <th>ユーザーID</th>
            <th>登録日</th>    
        </tr>
        <?php foreach ($data as $read) { ?>
            <tr>
                <td><?php echo htmlspecialchars($read['user_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($read['create_datetime'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>