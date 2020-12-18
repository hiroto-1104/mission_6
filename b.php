<!--メール確認、送信-->
<?php
    session_start();
    
    header("Content-type: text/html; charset=utf-8");
    
    //クロスサイトリクエストフォージェリ対策のトークン判定
    if($_POST["token"] != $_SESSION["token"])
    {
        echo "不正アクセスの可能性あり";
        exit();
    }
    
    //クリックジャッキング対策
    header('X-FRAME-OPTIONS: SAMEORIGIN');
    
    //データベース接続
    //DB接続設定
    $dsn = 'mysql:dbname=tb220944db;host:localhost';
    $user = 'tb-220944';
    $password = 'LtLhV383mH';
    $pdo = new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    //エラーメッセージの初期化
    $errors = array();
    
    //$_POSTの中が空の場合、a.phpに移動
    if(empty($_POST["mail"]))
    {
        header("location: a.php");
        exit();
    }
    //それ以外の場合。
    else
    {
        //POSTされたデータを変数に入れる(三項演算子)
        $mail_address = isset($_POST["mail"]) ? $_POST["mail"] : NULL;
        
        //メール入力判定
        if($mail_address == "")
        {
            $errors['mail'] = "メールが入力されていません。";
        }
        else
        {   ///^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/は正しいメールアドレスかという意味
            if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$mail_address))
            {
                $errors['mail_check'] = "メールアドレスの形式が正しくありません。";
            }
            /*
            ここで本登録用のmemberテーブルに既に登録されているmailかどうかチェックする。
            $errors['member_check'] = "このメールアドレスはすでに利用されております。";
            */
            $sql = "SELECT id FROM member WHERE mail = :mail";
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindValue(':mail',$mail_address,PDO::PARAM_STR);
            
            $stmt -> execute();
            $result = $stmt -> fetch(PDO::FETCH_ASSOC);
            if(isset($result['id']))
            {
                $errors['user_check'] = "このメールアドレスはすでに利用されています。";
            }
        }
    }
    
    if(count($errors) === 0)
    {
        $urltoken = hash('sha256',uniqid(rand(),1));
        $url = "https://tb-220944.tech-base.net/c.php"."?urltoken=".$urltoken;
        //ここでデータベースに登録する。
        try
        {
            $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $statement = $pdo->prepare("INSERT INTO pre_member (urltoken,mail,date) VALUES (:urltoken,:mail,now() )");
		
    		//プレースホルダへ実際の値を設定する
    		$statement->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
    		$statement->bindValue(':mail', $mail_address, PDO::PARAM_STR);
    		$statement->execute();
    		
    		//データベース接続切断
    		$sql = null;
        }
        catch(PDOException $e)
        {
            print('ERROR:'.$e -> getMessage());
            die();
        }
        require 'src/Exception.php';
        require 'src/PHPMailer.php';
        require 'src/SMTP.php';
        require 'setting.php';

        // PHPMailerのインスタンス生成
        $mail = new PHPMailer\PHPMailer\PHPMailer();
    
        $mail->isSMTP(); // SMTPを使うようにメーラーを設定する
        $mail->SMTPAuth = true;
        $mail->Host = MAIL_HOST; // メインのSMTPサーバー（メールホスト名）を指定
        $mail->Username = MAIL_USERNAME; // SMTPユーザー名（メールユーザー名）
        $mail->Password = MAIL_PASSWORD; // SMTPパスワード（メールパスワード）
        $mail->SMTPSecure = MAIL_ENCRPT; // TLS暗号化を有効にし、「SSL」も受け入れます
        $mail->Port = SMTP_PORT; // 接続するTCPポート
    
        // メール内容設定
        $mail->CharSet = "UTF-8";
        $mail->Encoding = "base64";
        $mail->setFrom(MAIL_FROM,MAIL_FROM_NAME);
        $mail->addAddress($mail_address, '受信者さん'); //受信者（送信先）を追加する
        //$mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
        //    $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
        //    $mail->addBcc('xxxxxxxxxx@xxxxxxxxxx'); // BCCで追加
        $mail->Subject = MAIL_SUBJECT; // メールタイトル
        $mail->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
        $body = "24時間以内に下記のURLからご登録ください。<br>"."<a href=".$url.">".$url."</a>";
    
        $mail->Body  = $body; // メール本文
        // メール送信の実行
        if(!$mail->send())
        {
        	echo 'メッセージは送られませんでした！';
        	echo 'Mailer Error: ' . $mail->ErrorInfo;
        } 
        else
        {
        	echo 'メールをお送りしました。24時間以内にメールに記載されたURLからご登録下さい。';
        }
    }
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>メール確認画面</title>
    </head>
    <body>
        <p>↓このURL が記載されたメールが届きます。</p>
        <a href="<?=$url?>"><?=$url?></a>
    </body>
</html>