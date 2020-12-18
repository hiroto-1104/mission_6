<?php    
    session_start();
    
    header("Content-type: text/html; charset=utf-8");
    
    //クロスサイトリクエストフォージェリ対策
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];

    //クリックジャッキング対策
    header('X-FRAME-OPTIONS:SAMEORIGIN');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン画面</title>
    <link rel="stylesheet" href="https://tb-220944.tech-base.net/login.css">
</head>
<body>
    <div class="main">
    <h1>ログイン</h1>
    <div class="form">
        <form action="login_check.php" method="post">
            <input type="text" name="account" placeholder="アカウント名" >
            <input type="text" name="pass" placeholder="パスワード"><br>
            <input type="hidden" name="token" value="<?=$token?>">
            <input type="submit" value="ログイン">
        </form>
    </div>
    <a class="link" href="https://tb-220944.tech-base.net/a.php">新規登録</a>
</div>
</body>
</html>