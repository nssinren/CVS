<?php //Connectiong Server
	require_once "./includes/php/reqfunctions.php";
	functions();
//	GoMaintenance();

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
	$FncSQL = "SELECT * FROM dbo.コメント WHERE 掲載期限 > '" . date("Y-m-d") . "' ORDER BY 更新日時 DESC";
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
	
//	echo "<div style='height:100px; overflow-x:scroll;'>";
//	echo "<div style='height:50px; overflow-x:scroll;'>";
//	echo "<div style='height:50px; overflow-x:hidden;'>";
	echo "<div style='height:100px; overflow-x:hidden; overflow-y:scroll;'>";
	echo "<table class='table table-condensed'>";
//	echo "<tr><th class='bg-info text-center' width='20%'>更新日時</th><th class='bg-info'>インフォメーション</th></tr>";
	
	for($i = 0;$i < $Max;$i++){
		echo "<tr>";
		echo "<td class='text-center' width='100px'><div class='datebox'>{$Msg[$i]['UPD']}</div></td>";
		echo "<td>{$Msg[$i]['MSG']}</td>";
		echo "</tr>";
//		$tmpMsg = $Msg[$i]['MSG'];
//		$LenFLG = False;
//		echo "{$Msg[$i]['UPD']}　／　";
//		echo "{$Msg[$i]['MSG']}<br>";
	}

	echo "</table>";
	echo "</div>";
//	echo "</div>";
}
?>
<?php
	$BTitle[0] = "CCS検証（代表者変更）";
	$BTitle[1] = "CCS検証（新規開設）";
	$BTitle[2] = "CCS検証（帳票ダウンロード）";
	$BText[0]  = "";
	$BText[1]  = "";
	$BText[2]  = "";
	
?>
<!DOCTYPE html>
<html>
<head>
	<title>トップ画面</title>
	<?php include('./includes/html/MyHeader.html'); ?>
	<link href="http://192.1.10.136/sys/cvs/includes/css/Design.css" rel="stylesheet">
</head>
<body>
	<h3 style="padding-left:30px;">顧客検証システム<span style='font-size: 12px;color:#666;'> - Customer Verifing System - </span></h3>		
	<?php include('./includes/html/MyNavigation.html'); ?>

	<div class='container-fulid'>
		<div class='row'>
			<h1>お知らせ</h1>
			<div class="col-md-2 text-right">
				<img src='./icons/char002_1.png' style="width:100px">
			</div>
			<div class="col-md-9 text-left">
				<div class='arrow_question'>
					<?php GetMsg($PDO); ?>
				</div>
			</div>
		</div>

		<div class='row'>
			<h1>メニュー</h1>
			<div class='col-md-4'>				<!-- 1 -->
				<div class='mybox'>
					<p class='p_mybox'>CCS検証（代表者変更）</p>
					<div class='myimage'><img src='./icons/index2.png'></div>
					<div class='mytext'>
						前月と当月のオンライン情報を比較し、代表者の変更があったものをチェックします。<br>
						CCSの取得状態を確認後、状況を更新してください。
					</div>
					<a href='./representative/rep_list.php'>link</a>
				</div>
			</div>
			<div class='col-md-4'>				<!-- 2 -->
				<div class='mybox'>
					<p class='p_mybox'>CCS検証（新規開設）</p>
					<div class='myimage'><img src='./icons/index1.png'></div>
					<div class='mytext'>
						・当座性口座を開設した顧客<br>
						・顧客を新規開設した顧客<br>
						をチェックします。<br>
						CCS照会の状態を確認後、状況を確認してください。
					</div>
					<a href='./new/new_list.php'>link</a>
				</div>
			</div>
			<div class='col-md-4'>				<!-- 3 -->
				<div class='mybox'>
					<p class='p_mybox'>印鑑登録状態確認</p>
					<div class='myimage'><img src='./icons/index4.png'></div>
					<div class='mytext'>新規に開設した顧客の印鑑取得状態を確認します。</div>
					<a href='./seal/seal_list.php'>link</a>
				</div>
			</div>
		</div><!-- Row(1) END -->
		<div class='row'>
			<div class='col-md-4'>				<!-- 4 -->
				<div class='mybox'>
					<p class='p_mybox'>帳票ダウンロード</p>
					<div class='myimage'><img src='./icons/index3.png'></div>
					<div class='mytext'>各種帳票のダウンロード・印刷が行えます。</div>
					<a href='./output.php'>link</a>
				</div>
			</div>
			<div class='col-md-4'>				<!-- 5 -->
				<div class='mybox'>
					<p class='p_mybox'>メッセージ</p>
					<div class='myimage'><img src='./icons/index5.png'></div>
					<div class='mytext'>各支店内・本支店間との簡単なメッセージのやり取りが行えます。</div>
					<a href='./message/disp_message.php'>link</a>
				</div>
			</div>
			<div class='col-md-4'>				<!-- 6 -->
			</div>
		</div><!-- Row(2) END -->
	</div><!-- Container(Menu) END -->
	<div style='height:60px;'></div>
	<?php
		require_once './includes/html/MyFooter.html';
		GetFileTime(getlastmod());
	?>
</body>
</html>