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
<?php //Functions
function SetTable1($FncSQL, $FncPDO)
{ //dbo.フラグ管理マスタ関数
	
	$i = 0;
//	$stmt = $FncPDO->query($FncSQL);
//	$Cstm = $stmt->fetch();
	$Cstm = $FncPDO->query($FncSQL)->fetch();
	
	//変数セット処理
	$CEL['CNUM'] = $Cstm[mb_convert_encoding("顧客番号","sjis","utf8")];
	$CEL['FINK'] = $Cstm[mb_convert_encoding("印鑑存在フラグ","sjis","utf8")];
	$CEL['INKC'] = $Cstm[mb_convert_encoding("印鑑確認状態","sjis","utf8")];
	$CEL['ZENG'] = $Cstm[mb_convert_encoding("前月存在フラグ","sjis","utf8")];
	$CEL['TOUG'] = $Cstm[mb_convert_encoding("当月存在フラグ","sjis","utf8")];

	//顧客番号
	$CEL['CNUM'] = sprintf("%08d", $CEL['CNUM']);

	//前月
	if($CEL['ZENG'] == 1){
		$ZENG_Msg = "<p class='text-success'>前月オンライン情報あり</p>";
	}else{
		$ZENG_Msg = "<p class='text-danger'>前月オンライン情報なし</p>";
	}
	//当月
	if($CEL['TOUG'] == 1){
		$TOUG_Msg = "<p class='text-success'>顧客新規</p>";
	}else{
		$TOUG_Msg = "<p class='text-danger'>顧客登録済み</p>";
	}
	//印鑑登録
	if($CEL['FINK'] == 1){
		$INKA_Msg = "<p class='text-success'>印鑑データあり</p>";
	}else{
		$INKA_Msg = "<p class='text-danger'>印鑑データなし</p>";
	}

	//CCSボタン
	$RdoBtn1 = "";
	$RdoBtn2 = "";
	$RdoBtn3 = "";
	$tmpBtn  = "";
	switch(intval($CEL['INKC'])){
		case 0:
			$CCSMsg  = "印鑑照合システムに登録されていません";
			$CCSMsg  = "<pre class='bg-danger'>{$CCSMsg}</pre>";
			break;
		default:
			$CCSMsg  = "印鑑照合システムで登録を行いました";
			$CCSMsg  = "<pre class='bg-success'>{$CCSMsg}</pre>";
			break;
	}

	echo "<table class='table text-center'>\n";
	echo "<tr><td class='text-center bg-primary'>顧客番号</td><td>{$CEL['CNUM']}</td></tr>";
	echo "<tr><td class='text-center bg-primary'>CCS照会</td><td>{$CCSMsg}</td></tr>";
	echo "<tr><td class='text-center bg-primary'>登録状態</td><td>";
//	echo "{$ZENG_Msg}\n";
	echo "{$TOUG_Msg}\n";
	echo "{$INKA_Msg}\n";
	echo "</td></tr>";
	echo "</table>\n";
	return $RdoBtn1 . $RdoBtn2 . $RdoBtn3;
//	echo $tmpBtn;

}
function CheckTable1($FncSQL, $FncPDO)
{ //フラグ管理処理
	$i = 0;
	$stmt = $FncPDO->query($FncSQL);
	$Cstm = $stmt->fetch();
	
	//変数セット処理
//	$CEL['FONL'] = $Cstm[mb_convert_encoding("オンライン登録フラグ","sjis","utf8")];
	$CEL['FINK'] = $Cstm[mb_convert_encoding("印鑑存在フラグ","sjis","utf8")];
	$CEL['FZEN'] = $Cstm[mb_convert_encoding("前月存在フラグ","sjis","utf8")];
	$CEL['FTOU'] = $Cstm[mb_convert_encoding("当月存在フラグ","sjis","utf8")];	
	$CEL['FCHE'] = $Cstm[mb_convert_encoding("印鑑確認状態","sjis","utf8")];
	
	//戻り値設定
	$RetFLG = 0;
	if($CEL['FZEN'] == 1){
		$RetFLG += 1000;
	}
	if($CEL['FTOU'] == 1){
		$RetFLG += 100;
	}
//	if($CEL['FONL'] == 1){
//		$RetFLG += 100;
//	}
	if($CEL['FINK'] == 1){
		$RetFLG += 10;
	}
	$RetFLG += $CEL['FCHE'];
	return sprintf("%04d", $RetFLG);
}
?>
<?php //警告回避処理
	if (!isset($_POST['PFLG']) || $_POST['PFLG'] == "" ){
		$FLG = 0;	//FLG=1で更新ボタン押下判定
	}else{
		$FLG = $_POST['PFLG'];
	}
?>
<?php //キーチェック処理
	$GFLG = True;
	if(isset($_GET['Data1'])){
		$Key1 = intval($_GET['Data1']);	
	}else{
		$GFLG = false;
	}
	if(isset($_GET['Data2'])){
		$Key2 = intval($_GET['Data2']);	
	}else{
		$GFLG = false;
	}
	
	if($GFLG <> False){
		$MySQL = "SELECT * FROM dbo.店舗マスタ WHERE 管轄店舗=" . $_SESSION['USERSTR'];
		$CNT   = 0;
		$GFLG = False;
		foreach($PDO->query($MySQL) as $Row){
			$tmpStore[$CNT] = $Row[mb_convert_encoding("小規模店番","sjis","utf8")];
			$CNT++;
		}
		for($i = 0;$i < $CNT;$i++){
			if($Key2 == $tmpStore[$i]){
				$GFLG = True;
			}
		}
	}
	
	if($_SESSION['USERSTR'] == 9){
		$GFLG = True;
	}
	
	if($GFLG == False){
		echo "不正なアクセスです。システムを再起動してください。";
		exit;
	}
?>
<?php //TblSQL生成処理
	$TblSQL1  = "SELECT * FROM dbo.フラグ管理マスタ WHERE 顧客番号={$Key1}";
	$tmpFLG   = CheckTable1($TblSQL1, $PDO);
	$TblFLG['ZEN'] = intval(substr($tmpFLG, 0, 1));
	$TblFLG['TOU'] = intval(substr($tmpFLG, 1, 1));
	$TblFLG['INK'] = intval(substr($tmpFLG, 2, 1));
	$TblFLG['CHE'] = intval(substr($tmpFLG, 3, 1));
	$TblSQL2[0] = "SELECT * FROM dbo.前月CIF WHERE 顧客番号={$Key1}";
	$TblSQL2[1] = "SELECT * FROM dbo.当月CIF WHERE 顧客番号={$Key1}";
	$TblSQL3    = "SELECT * FROM dbo.印鑑マスタ WHERE 顧客番号={$Key1} AND 最新世代フラグ='True'";

?>
<?php //テーブル更新処理
	if($FLG == 1){
		$USRNAME = "不明";
		$TmpSQL = "SELECT * FROM dbo.ユーザー管理 WHERE ユーザーID='{$_SESSION['USERID']}'";
		if($tmpCEL = $PDO->query($TmpSQL)->fetch()){
			$USRNAME = $tmpCEL[mb_convert_encoding("ユーザー名","sjis","utf8")];
		}else{
			$USRNAME = "不明";
		}
		$UpdSQL = "UPDATE dbo.フラグ管理マスタ 
				   SET 印鑑確認状態=1, 
				   印鑑更新日='" . date("Y/m/d H:i:s", time()) . "', 
				   印鑑更新者='{$USRNAME}' 
				   WHERE 顧客番号={$_GET['Data1']}";
//		echo $UpdSQL;
		$res = $PDO->query($UpdSQL);
		echo 	"<script language='javascript' type='text/javascript'>
					alert('データを更新しました。［画面更新］ボタンを押した上で状況を確認してください。');
					self.close();
				</script>";
		$BtnType = "hidden";
	}

?>
<!DOCTYPE html>
<html>
<head>
	<title>明細(印鑑)</title>
	<?php include('../includes/html/MyHeader.html'); ?>
</head>
<body>
	<?php include('../includes/js/MyTimeOut.js'); ?>
	<h3>新規印鑑検証フォーム</h3>
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
			<div class="col-md-6">
				<h1>オンラインシステム側情報</h1>
				<?php SETCIFTable($TblSQL2[1], $PDO, $TblFLG['TOU'],true); ?>
				<form name='update' method='post'>
				<input type='hidden' name='PFLG' value='1'>
				<div style="text-align:right;margin-left: 50px;">
				<?php 
				if($TblFLG['CHE'] == 0){
				$BtnType = "submit";
				}else{
				$BtnType = "hidden";							
				}
				?>
				<input type='<?php echo $BtnType; ?>' class='btn btn-lg btn-warning' value='印鑑登録を行いました' onclick='return confirm("データを更新してもよろしいですか？");'>
				</div>
				</form>
			</div>
		</div>

		<div class="row">
			<div class='text-right'>
				<input type='button' value='画面を閉じる' onclick='window.close()' class='btn btn-danger btn-lg'>
			</div>
		</div>
	</div>

	<div style='height:100px;'></div>
	</div>
		<?php
			require_once '../includes/html/MyFooter.html';
			GetFileTime(getlastmod());
		?>
</body>
</html>