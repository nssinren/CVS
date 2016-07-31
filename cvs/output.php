<?php //Connectiong Server
	require_once "./includes/php/reqfunctions.php";
	functions();
	GoMaintenance();
	
session_start();
if (!isset($_SESSION["USERID"])) {
	//logout
	header("Location: ./logout.php");
	exit();
}
$SRV = '192.1.10.111';
$DB  = 'CVSDB';
$USR = 'sa';
$PSW = 'nssinren';
$MyDNS = "sqlsrv:server=$SRV;database=$DB";
//echo $MyDNS;
try{
	$PDO = new PDO($MyDNS,$USR,$PSW);
	$e="OK</br>";
}catch(PDOException $e){
	$e->Getmessage();
}
?>

<?php
	$PDF1 		= "./list/001" . $_SESSION['USERSTR'] . ".pdf";
	$PDF2 		= "./list/002" . $_SESSION['USERSTR'] . ".pdf";
	$OLDPDF1 	= "./list/101" . $_SESSION['USERSTR'] . ".pdf";
	$OLDPDF2 	= "./list/102" . $_SESSION['USERSTR'] . ".pdf";
?>

<!DOCTYPE html>
<html>
<head>
<title>帳票出力画面</title>
	<?php include('./includes/html/MyHeader.html'); ?>
</head>
<body>
	<?php include('./includes/js/MyTimeOut.js'); ?>
	<h3>帳票ダウンロード</h3>
	<?php include('./includes/html/MyNavigation.html'); ?>

<div class='container-fulid'>
	<div class='container-fulid'>
		<div class='row'>
			<div class='col-md-1'></div><!--Dummy-->
			<div class='col-md-6'>
				<h1>メニュー</h1>
				<input type='button' class='btn-lg btn-block btn-primary' value='CCS点検表（代表者変更）' onclick="window.open('<?php echo $PDF1; ?>')"/>
				<p class='help-block'>代表者変更が行われている顧客のチェックリストを出力します。</p>
				<input type='button' class='btn-lg btn-block btn-primary' value='CCS点検表（新規口座開設）' onclick="window.open('<?php echo $PDF2; ?>')"/>
				<p class='help-block'>先月開設された顧客及び口座のチェックリストを出力します。</p>
			</div>
			<div class='col-md-4'>
				<h1>過去分</h1>
				<input type='button' class='btn-lg btn-block btn-warning' value='代表者変更' onclick="window.open('<?php echo $OLDPDF1; ?>')"/>
				<p class='help-block'>前月末　最終チェック後の代表者変更リストです。</p>
				<input type='button' class='btn-lg btn-block btn-warning' value='新規口座' onclick="window.open('<?php echo $OLDPDF2; ?>')"/>
				<p class='help-block'>前月末　最終チェック後の新規口座開設リストです。</p>
			</div>
		</div>
	</div>
</div>

<div style='height:60px;'></div>
<?php
	require_once './includes/html/MyFooter.html';
	GetFileTime(getlastmod());
?>
</body>
</html>