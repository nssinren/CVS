<?php //セッションを張る
session_start();
/*
$SRV = '192.1.10.154';
$DB  = 'RNoCDB';
$USR = 'sa';
$PSW = 'nssinren';
$MyDNS = "sqlsrv:server=$SRV;database=$DB";
$EMsg = "";
$FLG = True;
*/
$SRV = '192.1.10.136';
$DB  = 'myweb10po';
$USR = 'sa';
$PSW = 'P@ssw0rd';
$MyDNS = "sqlsrv:server=$SRV;database=$DB";
$EMsg = "";
$FLG = True;?>
<?php //ログイン
if (isset($_POST["login"])){
	if (empty($_POST["userid"])){
		$EMsg = "ユーザー名が未入力です。";
		$FLG = False;
	} else if (empty($_POST["password"])) {
		$EMsg = "パスワードが未入力です。";
		$FLG = False;
	}
}
//警告回避処理
if (!isset($_POST["userid"]) || $_POST["userid"] == "" ){
	$FLG = False;
}
if ($FLG === True) {
	try{
		$PDO = new PDO($MyDNS,$USR,$PSW);
		$e="OK</br>";
	}catch(PDOException $e){
		$e->Getmessage();
		exit();
	}
//	echo $e;
	$FLG = False;
//	echo var_dump($_POST["userid"]);
	if (is_null($_POST["userid"]) == False ) {
//		$MySQL = "SELECT * FROM dbo.ユーザー管理 WHERE ユーザーID = '" . $_POST["userid"] . "'";
		$MySQL = "SELECT * FROM dbo.T_PER_USER WHERE PER_usrid='" . $_POST["userid"] . "'";
//		echo $MySQL;
		foreach ($PDO->query($MySQL) as $Row){
//			echo var_dump($Row[1]);
			$SvrID  = $Row[mb_convert_encoding("PER_usrid","sjis","utf8")];
			$SvrPsw = $Row[mb_convert_encoding("PER_pass","sjis","utf8")];
			$FLG = True;
		}
	}
	if ($FLG === False) {
		$EMsg = "ユーザーが存在しません。";
	} else {
//		echo "cmp result = [" . strcmp($SvrID, $_POST["userid"]) . "]";
		if (md5($_POST["password"]) === $SvrPsw){
			$_SESSION["USERID"] = $_POST["userid"];
//			$_SESSION["USERSTR"] = $SvrStr;
//			echo $_SESSION["USERID"];
			
			$PDO = null;
			//===========================================================================
			$SRV = '192.1.10.111';
			$DB  = 'CVSDB';
			$USR = 'sa';
			$PSW = 'nssinren';
			$MyDNS = "sqlsrv:server=$SRV;database=$DB";
			try{
				$PDO = new PDO($MyDNS,$USR,$PSW);
				$e="OK</br>";
			}catch(PDOException $e){
				$e->Getmessage();
				exit();
			}
			$MySQL = "SELECT * FROM dbo.ユーザー管理 WHERE ユーザーID='{$_SESSION['USERID']}'";
//			$MySQL = "SELECT * FROM dbo.ユーザー管理";
			foreach ($PDO->query($MySQL) as $Row){
//				echo var_dump($Row[3]);
				$SvrStr = $Row[mb_convert_encoding("所属店舗","sjis","utf8")];
				$SvrPag = $Row[mb_convert_encoding("ページ枚数","sjis","utf8")];
				$Access = $Row[mb_convert_encoding("アクセス回数","sjis","utf8")] + 1;
			}
//			echo "Page = {$SvrPag}, Store = {$SvrStr}";
			$_SESSION['USERSTR'] = $SvrStr;
			$_SESSION['USERPAG'] = $SvrPag;

			$UpdSQL = "UPDATE dbo.ユーザー管理 SET アクセス回数={$Access} WHERE ユーザーID='{$_SESSION['USERID']}'";
			$res = $PDO->query($UpdSQL);
			//===========================================================================
			
			

			header("Location: ./index.php");
			exit();
		} else {
			$EMsg = "パスワードに誤りがあります。";
		}
	}
	if($EMsg <> ""){
		$EMsg = "<pre class='bg-warning'>{$EMsg}</pre>";
	}
}

?>
<?php //URL生成
	$tmpHTML = " value='";
	$FLG = True;
	if (!isset($_POST["userid"]) || $_POST["userid"] == "" ){
		$FLG = False;
	}
	if ($FLG == True) {
		$tmpHTML = $tmpHTML . htmlspecialchars($_POST["userid"], ENT_QUOTES) . "'"; 
	} else {
		$tmpHTML = "";
	}
?>
<!DOCTYPE html>
<html>
<head>
<title>ログインフォーム</title>
	<?php include('./includes/html/MyHeader.html'); ?>
</head>
<body>
	<h3>顧客検証システム　ログイン</h3>
	<div class='container-fulid'>
		<div class=row><div style='height: 50px;'></div></div>
		<div class="row">
			<div class="col-md-3"></div>
			<div class="col-md-6">
				<form method='post' class='form-horizontal' name='loginForm' action=''>
					<div class='form-group'>
						<label for='name' class='control-rabel col-xs-3'><p class='text-right text-success'>ユーザーID</p></label>
						<div class='col-xs-9'><input type='text' name='userid' class='form-control'<?php echo $tmpHTML; ?> placeholder='ユーザーIDを入力してください'></div>
					</div>
					<div class='form-group'>
						<label for='name' class='control-rabel col-xs-3'><p class='text-right text-success'>パスワード</p></label>
						<div class='col-xs-9'><input type='password' name='password' class='form-control' placeholder='パスワードを入力してください'></div>					
					</div>
					<div class='form-group'>
						<div class='col-xs-3'></div>
						<div class='col-xs-9'><input type='submit' name='login' class='btn-primary btn-lg btn-block' value='ログイン'>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3"></div>
			<div class="col-md-6">
				<?php echo $EMsg; ?>
			</div>
		</div>
	</div>
	<?php
		require_once './includes/html/MyFooter.html';
		GetFileTime(getlastmod());
	?>
</body>
</html>