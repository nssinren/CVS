<?php //Connection
	require_once "../includes/php/reqfunctions.php";
	functions();
	GoMaintenance();

session_start();
if (!isset($_SESSION["USERID"])) {
	//logout
	header("Location: ../logout.php");
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
<?php //キーチェック処理
function CheckDigit($FUsrStore, $PDO){
	$Key[0] = 0;
	$Key[1] = 0;
	$GFLG = True;
	if(isset($_GET['Data1'])){
		$Key[0] = intval($_GET['Data1']);	
	}else{
		$GFLG = false;
	}
	if(isset($_GET['Data2'])){
		$Key[1] = intval($_GET['Data2']);	
	}else{
		$GFLG = false;
	}
	
	if($GFLG <> False){
		$MySQL = "SELECT * FROM dbo.店舗マスタ WHERE 管轄店舗=" . $FUsrStore;
		$CNT   = 0;
		$GFLG = False;
		foreach($PDO->query($MySQL) as $Row){
			$tmpStore[$CNT] = $Row[mb_convert_encoding("小規模店番","sjis","utf8")];
			$CNT++;
		}
		for($i = 0;$i < $CNT;$i++){
			if($Key[1] == $tmpStore[$i]){
				$GFLG = True;
			}
		}
	}
	
	if($FUsrStore == 9){
		$GFLG = True;
	}
	
	if($GFLG == False){
//		echo "不正なアクセスです。システムを再起動してください。";
//		exit;
		echo "<script type='text/javascript'>
				alert('不正なアクセスであるため画面を終了します。システムを再起動してください。');
				location.href = '../index.php';
			 </script>
			 ";
	}
	return $Key;
}
?>

<?php //Functions
function SetTable1($FncSQL, $FncPDO)
{ //dbo.フラグ管理マスタ関数
	
	$i = 0;
	$Cstm = $FncPDO->query($FncSQL)->fetch();
	
	//変数セット処理
	$CEL['CNUM'] = $Cstm[mb_convert_encoding("顧客番号","sjis","utf8")];
	$CEL['FINK'] = $Cstm[mb_convert_encoding("印鑑存在フラグ","sjis","utf8")];
	$CEL['NEWC'] = $Cstm[mb_convert_encoding("新規確認状態","sjis","utf8")];
	$CEL['TOZA'] = $Cstm[mb_convert_encoding("口座開設フラグ","sjis","utf8")];
	$CEL['KOKY'] = $Cstm[mb_convert_encoding("顧客開設フラグ","sjis","utf8")];
	$CEL['TOUG'] = $Cstm[mb_convert_encoding("当月存在フラグ","sjis","utf8")];
//	$CEL['KKOK'] = $Cstm[mb_convert_encoding("顧客開設日","sjis","utf8")];

	//顧客番号
	$CEL['CNUM'] = sprintf("%08d", $CEL['CNUM']);
	
	//
	$KOKY_Msg = "";
	$TOZA_Msg = "";
	if($CEL['KOKY'] == true){
		$KOKY_Msg = "<span class='text-danger'>顧客新規</span>";
	}else{
		$KOKY_Msg = "<span class='text-success'>顧客開設済み</span>";		
	}
/*	if($CEL['TOZA'] == true){
		$TOZA_Msg = "<p class='text-danger'>当座性口座新規開設</p>";
	}
//	$KOKY_Msg = "<p class='text-success'></p>";
*/	
	//印鑑登録
	if($CEL['FINK'] == 1){
		$INKA_Msg = "<span class='text-success'>印鑑データあり</span>";
	}else{
		$INKA_Msg = "<span class='text-danger'>印鑑データなし</span>";
	}


	//CCSボタン
//	$tmpBtn1 = "";
//	$tmpBtn2 = "";
	$RdoBtn1 = "";
	$RdoBtn2 = "";
	$RdoBtn3 = "";
	$tmpBtn  = "";
	switch(intval($CEL['NEWC'])){
		case 0:
			$RdoBtn1 = "<div class='checkbox'><input type='radio' name='CCS1' value='1' checked>はい、既にCCSから照会を行っています\n";
			$RdoBtn2 = "<input type='radio' name='CCS1' value='2'>はい、今回新たにCCSから照会を行いました\n";
			$RdoBtn3 = "<input type='radio' name='CCS1' value='3'>いいえ、CCSから照会を行う必要はありません</div>\n";
			$CCSMsg  = "CCSから照会が必要です";
			$CCSMsg  = "<pre class='bg-danger'>{$CCSMsg}</pre>";
			break;
		case 1:
			$CCSMsg  = "照会していない口座があります";
			$CCSMsg  = "<pre class='bg-success'>{$CCSMsg}</pre>";
			break;
		case 2:
			$CCSMsg  = "CCSから照会を取りました";
			$CCSMsg  = "<pre class='bg-success'>{$CCSMsg}</pre>";
			break;
		default:
			$CCSMsg  = "CCSから照会を取る必要はありません";
			$CCSMsg  = "<pre class='bg-success'>{$CCSMsg}</pre>";
			break;
	}

	echo "<table class='table text-center'>\n";
	echo "<tr><td class='bg-primary' style='font-weight:bold;'>CCS照会</td><td>{$CCSMsg}</td></tr>";
	echo "<tr><td class='bg-primary' style='font-weight:bold;'>登録状態</td>";
	echo "<td class='text-left'>
			<lu>
				<li>{$KOKY_Msg}</li>
				<li>{$INKA_Msg}</li>
			</lu>
		 </td></tr>";
	echo "</table>\n";
	return $RdoBtn1 . $RdoBtn2 . $RdoBtn3;
}
function SetTable4($FncSQL, $FncPDO)
{//当座マスタ
	$i = 0;
	foreach($FncPDO->query($FncSQL) as $TOZA){
		//一時ファイル読み込み
		$tmpKAISHI       = $TOZA[mb_convert_encoding("口座開設日","sjis","utf8")];
		$tmpAccNum       = $TOZA[mb_convert_encoding("口座番号","sjis","utf8")];
		$tmpAccNum		 = strval(sprintf("%010.0f",$tmpAccNum));
		$AccType		 = $TOZA[mb_convert_encoding("貯金区分","sjis","utf8")];
		$tmpKamoku		 = $TOZA[mb_convert_encoding("科目コード","sjis","utf8")];
		$tmpKeiyaku		 = $TOZA[mb_convert_encoding("契約番号","sjis","utf8")];
		//表示データセット
		$CEL[$i]['KAMO'] = $TOZA[mb_convert_encoding("科目コード","sjis","utf8")];
		$CEL[$i]['CHID'] = strval($tmpAccNum . sprintf("%03d", $tmpKeiyaku));
		$CEL[$i]['KAIS'] = date("Y年n月j日", strtotime(intval($tmpKAISHI)));
		$CEL[$i]['SIKO'] = $TOZA[mb_convert_encoding("死口座","sjis","utf8")];
		$CEL[$i]['SFLG'] = $TOZA[mb_convert_encoding("新規フラグ","sjis","utf8")];
		$CEL[$i]['SCHE'] = $TOZA[mb_convert_encoding("新規確認状態","sjis","utf8")];
		$CEL[$i]['KOZA'] = SetAccnum(1, $tmpAccNum, $tmpKamoku);
//		$tmpAccNum		 = $TOZA[mb_convert_encoding("新規確認状態","sjis","utf8")];
//		$MyLen           = mb_strlen($tmpKOZA);
//		$CEL[$i]['KOZA'] = sprintf("%07d", mb_substr($tmpKOZA, 0, $MyLen - 3));
//		echo $tmpAccNum . " - {$CEL[$i]['CHID']}<br>";	//DEBUG
		$i++;
	}
	$Max = $i;
	
	for($i = 0;$i < $Max;$i++){
//		$tmpSQL = "SELECT * FROM dbo.科目マスタ WHERE 科目番号={$CEL[$i]['KAMO']}";
/*		if($tmpCEL = $FncPDO->query($tmpSQL)->fetch()){	
			$CEL[$i]['KAMO'] = $tmpCEL[mb_convert_encoding("科目名","sjis","utf8")];
		}else{
			$CEL[$i]['KAMO'] = "不明";
		}*/
		if($CEL[$i]['SIKO']){
			$CEL[$i]['SIKO'] = "<pre class='bg-danger'>解約済み</pre>"; 
		}else{
			$CEL[$i]['SIKO'] = "";
		}
		if($CEL[$i]['SCHE'] == 0){
			$MyRadio[$i]  = "<input type='hidden' name='check{$i}' value='0'>\n"; //チェックボックスが空の時の回避処理
			$MyRadio[$i] .= "<input type='checkbox' id='check{$i}' name='check{$i}' value='{$CEL[$i]['CHID']}'>\n";
			$MyRadio[$i] .= "<label for='check{$i}' class='checkbox-inline'>CCSから照会を行いました</label>\n";
		}else{
			$MyRadio[$i] = "<pre class='bg-success'>CCS照会済みです</pre>";
		}
/*		if($CEL[$i]['SFLG'] == 1){
			$CEL[$i]['CLAS'] = " class='bg-danger'";
		}else{
			$CEL[$i]['CLAS'] = "";			
		}*/
	}
	
//	+=========================================================
//	|　テーブル生成処理
//	+=========================================================
//	|　
	echo "<table class='table text-center'>";
	echo "<tr class='bg-primary'>
		  <th class='text-center'>口座番号</th>
		  <th class='text-center'>開設日</th>
		  <th class='text-center'>備考</th>
		  <th class='text-center'>状態</th>
		  </tr>";
	for($i = 0;$i < $Max;$i++){
		echo "<tr>";
		echo "<td>{$CEL[$i]['KOZA']}</td>";
		echo "<td>{$CEL[$i]['KAIS']}</td>";
		echo "<td>{$CEL[$i]['SIKO']}</td>";
		echo "<td>{$MyRadio[$i]}</td>";
		echo "</tr>";
	}
	echo "</table>";
	/*最大ループ回数をセット*/
	echo "<input type='hidden' value='{$Max}' name='Max_i'>";
//	|　
//	+=========================================================
}
function CheckTable1($FncSQL, $FncPDO)
{ //フラグ管理処理
	$i = 0;
	$stmt = $FncPDO->query($FncSQL);
	$Cstm = $stmt->fetch();
	
	//変数セット処理
	$CEL['FNEW'] = $Cstm[mb_convert_encoding("顧客開設フラグ","sjis","utf8")];
	$CEL['FTOZ'] = $Cstm[mb_convert_encoding("口座開設フラグ","sjis","utf8")];
	$CEL['FINK'] = $Cstm[mb_convert_encoding("印鑑存在フラグ","sjis","utf8")];
	$CEL['FZEN'] = $Cstm[mb_convert_encoding("前月存在フラグ","sjis","utf8")];
	$CEL['FTOU'] = $Cstm[mb_convert_encoding("当月存在フラグ","sjis","utf8")];	
	$CEL['FCHE'] = $Cstm[mb_convert_encoding("CCS確認状態","sjis","utf8")];
	
	//戻り値設定
	$RetFLG = 0;
	if($CEL['FNEW'] == 1){
		$RetFLG += 100000;
	}
	if($CEL['FTOZ'] == 1){
		$RetFLG += 10000;
	}
	if($CEL['FZEN'] == 1){
		$RetFLG += 1000;
	}
	if($CEL['FTOU'] == 1){
		$RetFLG += 100;
	}
	if($CEL['FINK'] == 1){
		$RetFLG += 10;
	}
	$RetFLG += $CEL['FCHE'];
	return sprintf("%06d", $RetFLG);
}
?>
<?php //警告回避処理
	if (!isset($_POST['PFLG']) || $_POST['PFLG'] == "" ){
		$FLG   = 0;	//FLG=1で更新ボタン押下判定
		$Max_i = 0;
	}else{
		$FLG = $_POST['PFLG'];
	}
	$Key = CheckDigit($_SESSION['USERSTR'], $PDO);
	$MySQL = "SELECT COUNT(*) FROM dbo.口座管理マスタ WHERE (顧客番号=" . $Key[0] . " AND 新規確認状態=0)";
//	echo "{$MySQL}<br>";	//DEBUG
	$stmt = $PDO->query($MySQL);
	$NowCNT = $stmt->fetchColumn();
/*
	$MySQL = "SELECT COUNT(*) FROM dbo.口座管理マスタ WHERE (顧客番号=". $Key[0] . " AND 新規フラグ='True')";
	echo "{$MySQL}<br>";
	$stmt = $PDO->query($MySQL);
	$MaxCNT = $stmt->fetchColumn();

	$ENDFLG = 0;
	if($NowCNT == $MaxCNT){
		$ENDFLG = 1;
	}*/
//	echo "top:now/max|end = {$NowCNT} / {$MaxCNT} | {$ENDFLG}<br>";
//	echo "top:now/max|end = {$NowCNT}<br>";
?>
<?php //TblSQL生成処理
	$TblSQL1  = "SELECT * FROM dbo.フラグ管理マスタ WHERE 顧客番号={$Key[0]}";
	$tmpFLG   = CheckTable1($TblSQL1, $PDO);
	$TblFLG['NEW'] = intval(substr($tmpFLG, 0, 1));
	$TblFLG['TOZ'] = intval(substr($tmpFLG, 1, 1));
	$TblFLG['ZEN'] = intval(substr($tmpFLG, 2, 1));
	$TblFLG['TOU'] = intval(substr($tmpFLG, 3, 1));
	$TblFLG['INK'] = intval(substr($tmpFLG, 4, 1));
	$TblFLG['CHE'] = intval(substr($tmpFLG, 5, 1));
	//
	/*
	echo "FLG = {$tmpFLG}<br>";
	echo "NEW = {$TblFLG['NEW']}<br>";
	echo "TOZ = {$TblFLG['TOZ']}<br>";
	echo "ZEN = {$TblFLG['ZEN']}<br>";
	echo "TOU = {$TblFLG['TOU']}<br>";
	echo "INK = {$TblFLG['INK']}<br>";
	echo "CHE = {$TblFLG['CHE']}<br>";
	*/
	//
	$TblSQL2[0] = "SELECT * FROM dbo.前月CIF WHERE 顧客番号={$Key[0]}";
	$TblSQL2[1] = "SELECT * FROM dbo.当月CIF WHERE 顧客番号={$Key[0]}";
	$TblSQL3   = "SELECT * FROM dbo.印鑑マスタ WHERE 顧客番号={$Key[0]} AND 最新世代フラグ='True'";
	$TblSQL4   = "SELECT * FROM dbo.口座管理マスタ WHERE 顧客番号={$Key[0]} AND 新規フラグ='True'";
?>

<?php //テーブル更新処理
//	echo $FLG;	//DEBUG
	GoMaintenance();
	if($FLG == 1){
//		echo "*****************************************<br>";
		//+=================================================
		//| ユーザー名取得
		//+=================================================
		//|
		$TmpSQL = "SELECT * FROM dbo.ユーザー管理 WHERE ユーザーID='{$_SESSION['USERID']}'";
		if($tmpCEL = $PDO->query($TmpSQL)->fetch()){
			$USRNAME = $tmpCEL[mb_convert_encoding("ユーザー名","sjis","utf8")];
		}else{
			$USRNAME = "不明";
		}
		//| 
		//+=================================================
		for($i=0; $i<$_POST['Max_i']; $i++){
//			+=================================================
//			| Debug
//			+=================================================
//			|
			$check = "check" . $i;
//			echo $_POST["{$check}"] . "<br>";	//DEBUG
//			echo $tmpstr . "<br>";				//DEBUG
			$tmpstr = strval($_POST["{$check}"]);
//			| 
//			+=================================================

//			+=================================================
//			| 初期値
//			+=================================================
//			|
			$AccNum = mb_substr($_POST["{$check}"], 0, 10);
			$AgrNum = intval(mb_substr($_POST["{$check}"], 11, 3));

			//処理実行
			if(intval($AccNum) > 0 ){
				$UpdSQL = "UPDATE dbo.口座管理マスタ SET 新規確認状態=1, 更新日時='" . date("Y/m/d H:i:s", time()) . "', 更新者='{$USRNAME}' WHERE 口座番号={$AccNum} AND 契約番号={$AgrNum}";
//				echo $UpdSQL . "<br>";	//DEBUG
			}
			$res = $PDO->query($UpdSQL);
//			| 
//			+=================================================
		}
		//+=================================================
		//| フラグ管理マスタ更新
		//+=================================================
		//|
		$MySQL = "SELECT COUNT(*) FROM dbo.口座管理マスタ WHERE (顧客番号=" . $Key[0] . " AND 新規確認状態=0)";
		$stmt = $PDO->query($MySQL);
		$NowCNT = $stmt->fetchColumn();

//		echo "update:now/max|end = {$NowCNT}<br>"; //DEBUG

		switch($NowCNT){
			case 0:
				$UpdSQL = "UPDATE dbo.フラグ管理マスタ SET 新規確認状態=2 WHERE 顧客番号=" . $Key[0];
				break;
			default:
				$UpdSQL = "UPDATE dbo.フラグ管理マスタ SET 新規確認状態=1 WHERE 顧客番号=" . $Key[0];
				break;
		}
//		echo $UpdSQL;	DEBUG
		$res = $PDO->query($UpdSQL);
		//| 
		//+=================================================
		echo	"<script type='text/javascript'>
					alert('データを更新しました。');
					self.close();
				</script>";
		
//		echo "*****************************************<br>";	//DEBUG
	}
/*	if($FLG == 1){
		$USRNAME = "不明";
		$TmpSQL = "SELECT * FROM dbo.ユーザー管理 WHERE ユーザーID='{$_SESSION['USERID']}'";
		if($tmpCEL = $PDO->query($TmpSQL)->fetch()){
			$USRNAME = $tmpCEL[mb_convert_encoding("ユーザー名","sjis","utf8")];
		}else{
			$USRNAME = "不明";
		}
		$UpdSQL = "UPDATE dbo.フラグ管理マスタ SET CCS確認状態={$_POST['CCS1']}, 更新日='" . date("Y/m/d H:i:s", time()) . "', 更新者='{$USRNAME}' WHERE 顧客番号={$_GET['Data1']}";
//		echo $UpdSQL;
		echo	"<script type='text/javascript'>
					alert('データを更新しました。');
				</script>";
		echo "Exec";
		$res = $PDO->query($UpdSQL);
	}*/
?>

<!DOCTYPE html>
<html>
<head>
	<title>明細(新規口座)</title>
	<?php include('../includes/html/MyHeader.html'); ?>
</head>
<body>
	<?php include('../includes/js/MyTimeOut.js'); ?>
	<h3>新規口座検証フォーム</h3>
	<?php include('../includes/html/MyNavigation.html'); ?>

	<div class='container-fulid'>
		<div class="row">
			<div class='text-right'>
				<input type='button' value='画面を閉じる' onclick='window.close();' class='btn btn-danger btn-lg'>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<h1>基本情報</h1>
				<?php $MyBOX = SetTable1($TblSQL1, $PDO); ?>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<h1>オンライン側情報</h1>
				<?php //Table2作成処理
					SetCIFTable($TblSQL2[1], $PDO, $TblFLG['TOU'], $TblFLG['NEW']); 
				?>
			</div>
		</div>

		<div class="row">
			<h1>口座情報</h1>
			<form name='update' method='post'>
			<input type='hidden' name='PFLG' value='1'>
			<?php
			SetTable4($TblSQL4, $PDO);
			$BtnType = "submit";
			if($NowCNT == 0){
			$BtnType = "hidden";
			}
			?>
			<div class='text-right'>
				<input type='<?php echo $BtnType; ?>' class='btn btn-lg btn-warning' value='状況を更新します' onclick='return confirm("データを更新してもよろしいですか？");'>
			</div>
			</form>
		</div>

		<div class="row">
			<h1>印鑑照合システム側情報</h1>
			<?php //Table3作成処理
			SetTable3($TblSQL3, $PDO, $TblFLG['INK']);
			?>
		</div>

		<div class="row">
			<div class='text-right'>
				<input type='button' value='画面を閉じる' onclick='window.close()' class='btn btn-danger btn-lg'>
			</div>
		</div>
	</div>

	<div style='height:100px;'></div>
	<?php
		require_once '../includes/html/MyFooter.html';
		GetFileTime(getlastmod());
	?>
</body>
</html>