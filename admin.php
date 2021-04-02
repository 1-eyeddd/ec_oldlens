<?php
require_once('./config.php');
//登録時
$new_name = "";
$new_price = "";
$new_img = "./admin_imgs/";
$new_stock = "";
$new_status = "";
$scene = "";
$description = "";
//更新時
$item_id = "";
$update_stock="";
$change_status = "";
//エラーメッセージ
$success_msg = [];
$err_msg = [];
//データベース

$data = [];

try {
   // データベースに接続
        $dbh = new PDO(dsn, username, password, array(PDO::MYSQL_ATTR_INIT_COMMAND => charset));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
   
   //商品登録
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['sql_kind'] === 'insert') {
       
      if (isset($_POST['new_name']) === true) {
         $new_name = $_POST['new_name'];
      }
      if (isset($_POST['new_price']) === true) {
         $new_price = $_POST['new_price'];
      }
      if (isset($_POST['new_stock']) === true) {
         $new_stock = $_POST['new_stock'];
      }
      if (isset($_POST['new_status']) === true) {
         $new_status= $_POST['new_status'];
      }
      if (isset($_POST['scene']) === true) {
         $scene= $_POST['scene'];
      }
      if (isset($_POST['description']) === true) {
         $description= $_POST['description'];
      }
      
      //追加時入力エラー
      if ($new_name === '') {
        $err_msg[] = '商品名を入力してください。';
      } 
      if ($new_price === '') {
        $err_msg[] = '価格を入力してください。';
      } 
      if ($new_stock === '') {
        $err_msg[] = '個数を入力してください。';
      } 
      if ($description === '') {
        $err_msg[] = '説明文を入力してください。';
      } 
      if (ctype_digit($new_price) !== true) {
        $err_msg[] = '価格は半角数字かつ0以上の整数で入力してください。';
      }
      if (ctype_digit($new_stock) !== true) {
        $err_msg[] = '個数は半角数字かつ0以上の整数で入力してください。';
      }
      if($new_status !== '0' && $new_status !== '1'){
        $err_msg[] = 'ステータスを選択してください';
      }
      if($scene !== '0' && $scene !== '1'&& $scene !== '2'){
        $err_msg[] = '使用シーンを選択してください';
      }
      //画像登録時のエラー
      if (is_uploaded_file($_FILES['new_img']['tmp_name']) === TRUE) {
        // 画像の拡張子を取得
        $extension = pathinfo($_FILES['new_img']['name'], PATHINFO_EXTENSION);
        $extension = mb_strtolower($extension);
        // 指定の拡張子であるかどうかチェック
        if ($extension === 'png' || $extension === 'jpeg' || $extension === 'jpg') {
            // 保存する新しいファイル名の生成（ユニークな値を設定する）
            $new_img_filename = sha1(uniqid(mt_rand(), true)) . '.' . $extension;
            // 同名ファイルが存在するかどうかチェック
            if (is_file($new_img . $new_img_filename) !== TRUE) {
               // アップロードされたファイルを指定ディレクトリに移動して保存
               if (move_uploaded_file($_FILES['new_img']['tmp_name'], $new_img . $new_img_filename) !== TRUE) {
                    $err_msg[] = 'ファイルアップロードに失敗しました';
               }
            } else {
               $err_msg[] = 'ファイルアップロードに失敗しました。再度お試しください。';
            }
        } else {
            $err_msg[] = 'ファイル形式が異なります。';
        }
      } else {
        $err_msg[] = 'ファイルを選択してください';
      }
      
      //登録するとき
      if (count($err_msg) === 0){
        try{
            $dbh->beginTransaction();
            try {
               $sql = 'INSERT INTO items (name,price,img,status,description,scene,create_datetime) 
                       VALUES(?, ?, ?, ?, ? ,?, now())';
               $stmt = $dbh->prepare($sql);
               $stmt->bindValue(1, $new_name,    PDO::PARAM_STR);
               $stmt->bindValue(2, $new_price, PDO::PARAM_INT);
               $stmt->bindValue(3, $new_img_filename, PDO::PARAM_STR);
               $stmt->bindValue(4, $new_status, PDO::PARAM_INT);
               $stmt->bindValue(5, $description, PDO::PARAM_STR);
               $stmt->bindValue(6, $scene, PDO::PARAM_INT);
               $stmt->execute();
               $lastId = $dbh->lastInsertId();
               
               $sql = 'INSERT INTO items_stock (item_id,stock, create_datetime, update_datetime) VALUES(?,?, now(), now())';
               $stmt = $dbh->prepare($sql);
               $stmt->bindValue(1, $lastId,PDO::PARAM_INT);
               $stmt->bindValue(2, $new_stock,PDO::PARAM_INT);
               $stmt->execute();
               
               $dbh->commit();
               
               $success_msg[] = '商品が登録できました';
            } catch (PDOException $e) {
               $dbh->rollback();
              throw $e;
            }
        } catch (PDOException $e) {
            echo 'データベース処理でエラーが発生しました。理由：'.$e->getMessage();
        }
      }
   }
   
   
   //個数を更新するとき
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['sql_kind'] === 'update'){
     
      if (isset($_POST['update_stock']) === true) {
        $update_stock = $_POST['update_stock'];
      }
      if (isset($_POST['item_id']) === true) {
        $item_id = $_POST['item_id'];
      }
      //エラー
      if (ctype_digit($update_stock) !== true) {
        $err_msg[] = '個数は半角数字かつ0以上の整数で入力してください。';
      }
      if ($update_stock === '') {
        $err_msg[] = '個数を入力してください。';
      } 
      
      if (count($err_msg) === 0) {
        $sql = 'UPDATE items_stock SET stock = ?,update_datetime = now() WHERE item_id = ?;';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $update_stock,    PDO::PARAM_INT);
        $stmt->bindValue(2, $item_id,    PDO::PARAM_INT);
        $stmt->execute();
        $success_msg[] = '個数を更新できました';
      } else {
        $err_msg[] = '個数を更新できませんでした';
      }
   }
   
   //ステータスを更新するとき
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['sql_kind'] === 'change'){
     
      if (isset($_POST['change_status']) === true) {
        $change_status = $_POST['change_status'];
      }
      if (isset($_POST['item_id']) === true) {
        $item_id = $_POST['item_id'];
      }
      if($change_status !== '0' && $change_status !== '1'){
        $err_msg[] = 'ステータスを選択してください';
      }
      if (count($err_msg) === 0) {
        $sql = 'UPDATE items SET status = ?,update_datetime = now() WHERE item_id = ?;';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $change_status,    PDO::PARAM_INT);
        $stmt->bindValue(2, $item_id,    PDO::PARAM_INT);
        $stmt->execute();
        $success_msg[] = 'ステータスを更新できました';
      } else {
         $err_msg[] = 'ステータスを更新できませんでした。';
      }
   }
   //商品を削除するとき
   if($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['sql_kind'] === 'delete'){
       
       if (isset($_POST['item_id']) === true) {
        $item_id = $_POST['item_id'];
        }
        
       try{
            $dbh->beginTransaction();
            try {
               $sql = 'DELETE FROM items WHERE item_id = ?';
               $stmt = $dbh->prepare($sql);
               $stmt->bindValue(1, $item_id,PDO::PARAM_INT);
               $stmt->execute();
    
               $sql = 'DELETE FROM items_stock WHERE item_id = ?';
               $stmt = $dbh->prepare($sql);
               $stmt->bindValue(1, $item_id,PDO::PARAM_INT);
               $stmt->execute();
               
               $dbh->commit();
               
               $success_msg[] = '商品を削除しました';
            } catch (PDOException $e) {
               $dbh->rollback();
              throw $e;
            }
        } catch (PDOException $e) {
            echo 'データベース処理でエラーが発生しました。理由：'.$e->getMessage();
        }
   }
    //一覧表示
    $sql = 'SELECT 
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
              items.scene = scene_type.scene';
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
    <title>商品管理</title>
    <link rel="stylesheet" href="./css/admin.css">
    <link rel="shortcut icon" href="./images/logo.png" type="image/x-icon">
</head>
<?php foreach ($success_msg as $value) { ?>
      <p><?php print $value; ?></p>
<?php } ?>
<?php foreach ($err_msg as $value_err) { ?>
      <p><?php print $value_err; ?></p>
<?php } ?>
<body>
    <h1>Old Lens Shop管理ページ</h1>
    <a href="./admin_user.php">ユーザー管理ページ</a>
    <section>
        <h2>商品の登録</h2>
        <form method="post" enctype="multipart/form-data">
            <div><label>名前: <input type="text" name="new_name" ></label></div>
            <div><label>値段: <input type="text" name="new_price"></label></div>
            <div><label>個数: <input type="text" name="new_stock"></label></div>
            <div><label>商品画像：<input type="file" name="new_img"></label></div>
            <div>
              <label>ステータス：
                <select name="new_status">
                    <option value="0">非公開</option>
                    <option value="1">公開</option>
                </select>
              </label>
            </div>
            <div>
                <label>使用シーン：
                    <select name="scene">
                    <option value="0">ポートレート</option>
                    <option value="1">風景</option>
                    <option value="2">マクロ</option>
                    </select>
                </label>
            </div>
            <div>
                <label>商品説明：<br>
                    <textarea name="description" cols="40" rows="5"></textarea>
                </label>
            </div>
            <input type="hidden" name="sql_kind" value="insert">
            <div><input type="submit" value="商品を登録する"></div>
        </form>
    </section>
    <section>
        <h2>商品情報一覧・変更</h2>
        <table>
            <caption>商品一覧</caption>
            <tr>
                <th>商品画像</th>
                <th>商品名</th>
                <th>価格</th>
                <th>在庫数</th>
                <th>ステータス</th>
                <th>使用シーン</th>
                <th>商品説明</th>
                <th>操作</th>
            </tr>
        <?php foreach ($data as $read) { ?>
            <?php if($read['status'] === 0) { ?>
                <tr class="status_false">
                    <td><img src="<?php print $new_img . $read['img']; ?>"  class="img-size"></td>
                    <td><?php print htmlspecialchars($read['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php print htmlspecialchars($read['price'], ENT_QUOTES, 'UTF-8'); ?>円</td>
                    <td>
                       <form method="post">
                          <input type="hidden" name="sql_kind" value="update">
                          <input type="hidden" name="item_id" value="<?php print htmlspecialchars($read['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                          <input type="text" name="update_stock" size="10" value="<?php print htmlspecialchars($read['stock'], ENT_QUOTES, 'UTF-8'); ?>">個<input type="submit" value="変更">
                       </form>
                    </td>
                    <td>
                        <form method="post">
                        <input type="hidden" name="item_id" value="<?php print htmlspecialchars($read['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="change_status" value="1">
                        <input type="hidden" name="sql_kind" value="change">
                        <input type="submit" value="非公開→公開">
                        </form>
                    </td>
                    <td><?php print htmlspecialchars($read['type_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php print htmlspecialchars($read['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                      <form method="post">
                        <input type="hidden" name="item_id" value="<?php print htmlspecialchars($read['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="sql_kind" value="delete">
                        <input type="submit" value="削除する">
                      </form>
                    </td>
                </tr>
            <?php } else { ?>
                <tr>
                    <td><img src="<?php print $new_img . $read['img']; ?>"></td>
                    <td><?php print htmlspecialchars($read['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php print htmlspecialchars($read['price'], ENT_QUOTES, 'UTF-8'); ?>円</td>
                    <td>
                       <form method="post">
                          <input type="hidden" name="sql_kind" value="update">
                          <input type="hidden" name="item_id" value="<?php print htmlspecialchars($read['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                          <input type="text" name="update_stock" size="10" value="<?php print htmlspecialchars($read['stock'], ENT_QUOTES, 'UTF-8'); ?>">個<input type="submit" value="変更">
                       </form>
                    </td>
                    <td>
                        <form method="post">
                        <input type="hidden" name="item_id" value="<?php print htmlspecialchars($read['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="change_status" value="0">
                        <input type="hidden" name="sql_kind" value="change">
                        <input type="submit" value="公開→非公開">
                        </form>
                    </td>
                    <td><?php print htmlspecialchars($read['type_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php print htmlspecialchars($read['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                      <form method="post">
                        <input type="hidden" name="item_id" value="<?php print htmlspecialchars($read['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="sql_kind" value="delete">
                        <input type="submit" value="削除する">
                      </form>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
        </table>
    </section>
</body>
    
</html>