<?php //Connectiong Server
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
function GetMsg($FncPDO){
	$FncSQL = "SELECT * FROM dbo.操作ログ WHERE 掲載期限 > '" . date("Y-m-d") . "' ORDER BY 更新日時 DESC";
//	$FncSQL = "SELECT * FROM dbo.操作ログ ORDER BY 更新日時 DESC";
//	echo $FncSQL;
	$i = 0;
	foreach ($FncPDO->query($FncSQL) as $Row){
//		echo $Row[mb_convert_encoding("更新日時","sjis","utf8")] . "<br>";
		$Msg[$i]['MSG'] = $Row[mb_convert_encoding("コメント","sjis","utf8")];
		$Msg[$i]['UPD'] = date("n月j日", strtotime($Row[mb_convert_encoding("更新日時","sjis","utf8")]));
		$i++;
	}
	$Max = $i;
	
	echo "<table class='table'>";
	echo "<tr><th class='bg-info text-center' width='20%'>更新日時</th><th class='bg-info'>インフォメーション</th></tr>";
	
	for($i = 0;$i < $Max;$i++){
		echo "<tr>";
		echo "<td class='text-center'>{$Msg[$i]['UPD']}</td>";
		echo "<td>{$Msg[$i]['MSG']}</td>";
		echo "</tr>";
		$tmpMsg = $Msg[$i]['MSG'];
		$LenFLG = False;
	}

	echo "</table>";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>トップ画面</title>
	<div class='bg-primary'><?php include('./includes/html/MyHeader.html'); ?></div>
</head>
<body>
<?php include('./includes/js/MyTimeOut.js'); ?>
<div class='container-fulid'>
	<div class='page-header bg-primary'>
		<br>
		<h3>顧客検証システム</h3>
		<p color='#FFF'>　Customer Verifing System</p>
		<br>
	</div>
	<?php include('./includes/html/MyNavigation.html'); ?>
	<div class='container-fluid'>
		<div class='row'>
			<div class='col-md-4'>
				<h3>更新情報</h3>
					<div style='height:300px; overflow-x:scroll;'>
					<?php GetMsg($PDO); ?>
					</div>
			</div>
			<div class='col-md-4 col-md-offset-1'>
				<h3>メニュー</h3>
					<input type='button' class='btn-lg btn-block btn-primary' value='代表者検証' onclick="location.href='./representative/rep_list.php'">
					<p class='help-block'>前月と当月のオンラインシステム上の情報を比較し、代表者変更が行われているものをリストにしています。</p>
					<input type='button' class='btn-lg btn-block btn-primary' value='新規開設' onclick="location.href='./new/new_list.php'">
					<p class='help-block'>当月中に新規顧客開設・口座開設があったものをリストにしています。</p>
					<input type='button' class='btn-lg btn-block btn-primary' value='帳票出力' onclick="location.href='./output.php'">
					<p class='help-block'>各帳票の出力を行います。</p>
			</div>
		</div>
	</div>
</div>
<?php
	require_once './includes/html/MyFooter.html';
	GetFileTime(getlastmod());
?>
</body>
</html>