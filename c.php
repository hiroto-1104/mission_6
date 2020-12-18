<?php
    session_start();

    header("Content-type:text/html; charset=utf-8");
    
    //クロスサイトリクエストフォージェリ対策
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];
    
    //クリックジャッキング対策
    header('X-FRAME-OPTIONS:SAMEORIGIN');
    
    //DB接続設定
    $dsn = 'mysql:dbname=tb220944db;host:localhost';
    $user = 'tb-220944';
    $password = 'LtLhV383mH';
    $pdo = new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    //エラーメッセージの初期化
    $errors = array();
    
    if(empty($_GET))
    {
        header("location:b.php");
        exit();
    }
    else
    {
        //GETデータを変数に入れる
        $urltoken = isset($_GET['urltoken']) ? $_GET['urltoken'] : NULL;
        //メール入力判定
        if($urltoken == "")
        {
            $errors['urltoken'] = "もう一度登録をやり直してください。";
        }
        else
        {
            try
            {
                //例外処理を投げるようにする。
                $pdo -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                
                //flagが０の未登録者、仮登録日から24時間以内  URLのトークンが一致しているかはここで判断している
                $statement = $pdo -> prepare("SELECT mail FROM pre_member WHERE urltoken=(:urltoken) AND flag =0 AND date >now() - interval 24 hour");
                $statement -> bindValue(':urltoken',$urltoken,PDO::PARAM_STR);
                $statement -> execute();
                
                //レコード件数取得
                $row_count  = $statement -> rowCount();
                
                //24時間以内の仮登録され、本登録されていないトークンの場合
                if($row_count ==1)
                {
                    $mail_array = $statement -> fetch();
                    $mail = $mail_array['mail'];
                    $_SESSION['mail'] = $mail;
                }
                else
                {
                    $errors['urltoken_timeover'] = "このURLはご利用できません。有効期限が過ぎた等の問題があります。登録をやり直してください。";
                }
                $pdo = NULL;
            }
            catch(PDOException $e)
            {
                print('Error:'.$e->getMessage());
                die();
            }
            
        }
    }
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>会員登録画面</title>
    </head>
    <body>
        <h1>会員登録画面</h1>
        
        <?php if(count($errors) === 0): ?>
            
            <form action="d.php" method="post">
                <p>メールアドレス:<?=htmlspecialchars($mail,ENT_QUOTES,'UTF-8')?></p>
                <p>アカウント名:<input type="text" name="account"></p>
                <p>パスワード:<input type="text" name="password"></p>
                
                <input type="hidden" name="token" value="<?=$token ?>">
                <input type="submit" value="確認する">
            </form>
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