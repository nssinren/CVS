<?php
session_start();

if(isset($_SESSION["USERID"])){
  $errorMessage = "ログアウトしました。";
}else{
  $errorMessage = "一定時間操作がなかったため、ログアウトしました。";
}
// セッション変数のクリア
$_SESSION = array();
// クッキーの破棄は不要
//if (ini_get("session.use_cookies")) {
//    $params = session_get_cookie_params();
//    setcookie(session_name(), '', time() - 42000,
//        $params["path"], $params["domain"],
//        $params["secure"], $params["httponly"]
//    );
//}
// セッションクリア
@session_destroy();
?>

<!doctype html>
<html>
<head>
	<title>ログアウト</title>
	<?php include('./includes/html/MyHeader.html'); ?>
</head>
<body>
	<h3>ログアウト</h3>
	<div class='container-fulid'>
		<div class='container'>
			<h1>ログアウトしました</h1>
			<div class='text-center'><img src="./icons/char002_1.png" style="width:150px;"></div>
			<div><pre class='text-center bg-info'><?php echo $errorMessage; ?></pre></div><br>
			<div class='text-right'>
				<input type='button' class='btn btn-lg btn-primary' value='ログイン画面に戻る' onclick="location.href='./login.php'"/>
			</div>
		</div>
	</div>
	<?php
		require_once './includes/html/MyFooter.html';
		GetFileTime(getlastmod());
	?>
</body>
</html>