<?php
    session_start();

    //クロスサイトリクエストフォージェリ対策
    if($_SESSION['token'] != $_POST['token'])
    {
        echo "不正アクセスの可能性あり";
        exit();
    }

    //クリックジャッキング対策
    header('X-FRAME-OPTIONS:SAMEORIGIN');

    //DB接続設定
    $dsn = 'mysql:dbname=tb220944db;host:localhost';
    $user = 'tb-220944';
    $password = 'LtLhV383mH';
    $pdo = new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    //エラーメッセージの初期化
    $errors = array();

    //ポスト送信されたものが空ではないか。
    if(empty($_POST))
    {
        header("location:d.php");
        exit();
    }
    //セッションを変数に代入
    $mail = $_SESSION['mail'];
    $account = $_SESSION['account'];

    //パスワードのハッシュ化
    $password_hash = password_hash($_SESSION['password'],PASSWORD_DEFAULT);

    //ここでデータベースに登録する。
    try
    {
        //例外処理を投げる
        $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        //トランザクション開始
        $pdo -> beginTransaction();

        //memberテーブルに本登録する。
        $statement = $pdo -> prepare("INSERT INTO member(account,mail,password) VALUE(:account,:mail,:password_hash)");
        $statement -> bindValue(':account',$account,PDO::PARAM_STR);
        $statement -> bindValue(':mail',$mail,PDO::PARAM_STR);
        $statement -> bindValue(':password_hash',$password_hash,PDO::PARAM_STR);
        $statement -> execute();

        $statement = $pdo -> prepare("UPDATE pre_member SET flag=1 WHERE mail=(:mail)");
        //プレースホルダーに実際の値を設定する
        $statement -> bindValue(':mail',$mail,PDO::PARAM_STR);
        $statement -> execute();

        //トランザクション完了
        $pdo -> commit();         

        //セッション変数をすべて解除
        $_SESSION = array();
        
        //セッションを破棄
        session_destroy();
    }
    catch(PDOException $e)
    {
        $pdo -> rollBack();
        $errors['error'] = "もう一度やり直してください。";
        print('error:'.$e ->getMessage());
    }
    $pdo = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>会員登録完了</title>
</head>
<body>
    <?php if(count($errors) === 0): ?>
        <h1>会員登録完了画面</h1>
        <p>会員登録完了しました。ログイン画面からどうぞ。</p>
        <p><a href ="https://tb-220944.tech-base.net/login.php">ログイン画面</a></p>

    <?php elseif(count($errors) > 0): ?>
        <?php
            foreach($errors as $value)
            {
                echo "<p>".$value."</p>";
            }
        ?>
    <?php endif; ?>

</body>
</html>