<?php
    session_start();

    header("Content-type: text/html; charset=utf-8");

    //クロスサイトリクエストフォージェリ対策
    if($_SESSION['token'] != $_POST["token"])
    {
        echo "不正アクセスの可能性あり";
        exit();
    }

    //クリックジャッキング対策
    header("X-FRAME-OPTIONS:SAMEORIGIN");

    //データベース接続
    //DB接続設定
    $dsn = 'mysql:dbname=tb220944db;host:localhost';
    $user = 'tb-220944';
    $password = 'LtLhV383mH';
    $pdo = new PDO($dsn,$user,$password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    //前後にある半角全角スペースを削除する関数
    //前後にある半角全角スペースを削除する関数
    function spaceTrim ($str)
    {
        // 行頭
        $str = preg_replace('/^[ 　]+/u', '', $str);
        // 末尾
        $str = preg_replace('/[ 　]+$/u', '', $str);
        return $str;
    }
    
    //エラーメッセージの初期化
    $errors = array();

    if(empty($_POST))
    {
        echo "入力欄に空欄があります。";
        exit();
    }
  
    //POSTされたデータを変数に入れる
    $account = isset($_POST['account'])? $_POST['account'] : NULL;
    $password = isset($_POST['pass'])? $_POST['pass'] : NULL;

    //前後にある半角全角スペースを削除
    $account = spaceTrim($account);
    $password = spaceTrim($password);

    //アカウント入力判定
    if($account == "")
    {
        $errors['account'] = "アカウントが入力されていません。";
    }
    elseif($account > 10)
    {
        $errors['account_length'] = "アカウントは10文字以内で入力してください。";
    }

    //パスワード入力判定
    if($password == '')
    {
        $errors['password'] = "パスワードが入力されていません。";
    }
    elseif(!preg_match('/^[0-9a-zA-Z]{5,30}$/', $_POST["pass"]))
    {
        $errors['password_length'] = "パスワードは半角英数字の5文字以上30字以下で入力してください。";
    }
    else
    {
        $password_hide = str_repeat('*',strlen($password));
    }
    //エラーがなければ実行する
    if($errors == array())
    {
        try
        {
            //例外処理を投げるようにする。
            $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            //アカウントで検索
            $statement = $pdo -> prepare("SELECT * FROM member WHERE account=:account");
            $statement -> bindValue('account',$account,PDO::PARAM_STR);
            $statement -> execute();
            
            //アカウントが一致
            //if文の条件式に代入の式。代入し終わった後のもので真偽を判断する。
            //select文で該当箇所がなかった場合はfalseを返す。
            if($row = $statement -> fetch())
            {echo "成功";
                $password_hash = $row["password"];
                if (password_verify($password, $password_hash)) {
                    //セッションハイジャック対策
                    session_regenerate_id(true);
                    
                    $_SESSION['account'] = $account;
                    header("Location: home.php");
                    exit();
                }
                else
                {
                    $errors['password'] = "アカウントおよびパスワードが一致しません。";
                }
            }
            else
            {
                $errors['account'] = "アカウントおよびパスワードが一致しません。";
            }
        }
        
        catch(PDOException $e)
        {
            print('Error:'.$e->getMessage());
		    die();
        }
    }
    $pdo = null;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン確認画面</title>
</head>
<body>
<h1>ログイン確認画面</h1>
 
<?php if(count($errors) > 0): ?>
 
<?php
foreach($errors as $value){
	echo "<p>".$value."</p>";
}
?>
 
<input type="button" value="戻る" onClick="history.back()">
 
<?php endif; ?>
</body>
</html>