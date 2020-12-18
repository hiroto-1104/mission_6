<?php
session_start();
 
header("Content-type: text/html; charset=utf-8");
 
// ログイン状態のチェック
if (!isset($_SESSION["account"])) {
	header("Location: login_form.php");
	exit();
}
 
$account = $_SESSION['account'];
echo "<p>".htmlspecialchars($account,ENT_QUOTES)."さん、こんにちは！</p>";
 ?>
 <!DOCTYPE html>
 <html lang="ja">
 <head>
	 <meta charset="UTF-8">
	 <title>ホーム</title>
 </head>
 <body>
	 <h1>ホーム</h1>
 </body>
 </html>