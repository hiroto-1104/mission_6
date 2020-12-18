<!--フォーム作成-->
<?php
    session_start();
    
    header("Content-type: text/html; charset=utf-8");
    
    //クロスサイトリクエストフォージェリ対策
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];
    
    //クリックジャッキング対策
    header('X-FRAME-OPTIONS: SAMEORIGIN');
?>
<!DOCTYPE html>
<html lang="ja">
    <head> 
        <meta charset="UTF-8">
        <title>mission_6-2</title>
    </head>
    <body>
        <h1>メール登録画面</h1>
        
        <form action="b.php" method="post">
            <input type="text" name="mail" placeholder="メールアドレス">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            <input type="submit" name="submit_1" value="登録">
        </form>
    </body>
</html>