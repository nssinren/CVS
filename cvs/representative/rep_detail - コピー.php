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
	$CEL['CCSC'] = $Cstm[mb_convert_encoding("CCS確認状態","sjis","utf8")];
	$CEL['ZENG'] = $Cstm[mb_convert_encoding("前月存在フラグ","sjis","utf8")];
	$CEL['TOUG'] = $Cstm[mb_convert_encoding("当月存在フラグ","sjis","utf8")];

	//顧客番号
	$CEL['CNUM'] = sprintf("%08d", $CEL['CNUM']);

	//前月
	$TrueColor  = "<p class='text-success'>";
	$Color_end  = "</p>";
	$FalseColor = "<p class='text-danger'>";
	if($CEL['ZENG'] == 1){
		$ZENG_Msg = "{$TrueColor}前月顧客あり{$Color_end}";
	}else{
		$ZENG_Msg = "{$FalseColor}前月顧客なし{$Color_end}";
	}
	//当月
	if($CEL['TOUG'] == 1){
		$TOUG_Msg = "{$TrueColor}当月顧客あり{$Color_end}";
	}else{
		$TOUG_Msg = "{$FalseColor}当月顧客なし{$Color_end}";
	}
	//印鑑登録
	if($CEL['FINK'] == 1){
		$INKA_Msg = "{$TrueColor}印鑑データあり{$Color_end}";
	}else{
		$INKA_Msg = "{$FalseColor}印鑑データなし{$Color_end}";
	}

	//CCSボタン
	$RdoBtn1 = "";
	$RdoBtn2 = "";
	$RdoBtn3 = "";
	$tmpBtn  = "";
	switch(intval($CEL['CCSC'])){
		case 0:
			$RdoBtn1  = "<input type='radio' name='CCS1' id='RAD1' value='1' checked>";
			$RdoBtn1 .= "<label for='RAD1' class='radio-inline'>はい、既にCCSから照会を行っています</label>\n";

			$RdoBtn2  = "<input type='radio' name='CCS1' id='RAD2' value='2'>";
			$RdoBtn2 .= "<label for='RAD2' class='radio-inline'>はい、今回新たにCCSから照会を行いました</label>\n";

			$RdoBtn3  = "<input type='radio' name='CCS1' id='RAD3' value='3'>";
			$RdoBtn3 .= "<label for='RAD3' class='radio-inline'>いいえ、CCSから照会を行う必要はありません</label>\n";



			$CCSMsg  = "CCSから照会が必要です";
			$CCSMsg  = "<pre class='bg-danger'>{$CCSMsg}</pre>";
			break;
		case 1:
			$CCSMsg  = "過去にCCSから照会を取っています";
			$CCSMsg  = "<pre class='bg-success'>{$CCSMsg}</pre>";
			break;
		case 2:
			$CCSMsg  = "新たにCCSから照会を取りました";
			$CCSMsg  = "<pre class='bg-success'>{$CCSMsg}</pre>";
			break;
		default:
			$CCSMsg  = "CCSから照会を取る必要はありません";
			$CCSMsg  = "<pre class='bg-success'>{$CCSMsg}</pre>";
			break;
	}

	echo "<table class='table text-center'>\n";
	echo "<tr><td class='text-center bg-primary'>顧客番号</td><td>{$CEL['CNUM']}</td></tr>";
	echo "<tr><td class='text-center bg-primary'>CCS照会</td><td>{$CCSMsg}</td></tr>";
	echo "<tr><td class='text-center bg-primary'>登録状態</td><td class='text-left'>";
	echo "<lu><li>{$ZENG_Msg}</li>\n";
	echo "    <li>{$TOUG_Msg}</li>\n";
	echo "    <li>{$INKA_Msg}</li></lu>\n";
	echo "</td></tr>";
	echo "</table>\n";
	return $RdoBtn1 . $RdoBtn2 . $RdoBtn3;
//	echo $tmpBtn;

}
/*
function SetTable2($FncSQL, $FncPDO, $Exist)
{ //dbo.前月・当月CIF
	$i = 0;
	$stmt = $FncPDO->query($FncSQL);
	$Cstm = $stmt->fetch();
	//変数セット処理
	$CEL['TUKI'] = "月";
	if(strpos($FncSQL,'当月') !== False){
		$CEL['TUKI'] = "当月";
	}elseif(strpos($FncSQL,'前月') !== False){
		$CEL['TUKI'] = "前月";		
	}
	if($Exist == 1){
		$CEL['NAME'] = $Cstm[mb_convert_encoding("漢字氏名","sjis","utf8")];
		$CEL['KANA'] = $Cstm[mb_convert_encoding("氏名","sjis","utf8")];
		$CEL['DAIH'] = $Cstm[mb_convert_encoding("漢字代表者名","sjis","utf8")];
		$CEL['YAGO'] = $Cstm[mb_convert_encoding("漢字屋号","sjis","utf8")];
		$CEL['SYOK'] = $Cstm[mb_convert_encoding("小規模店番","sjis","utf8")];
		$CEL['JNUM'] = $Cstm[mb_convert_encoding("人格コード","sjis","utf8")];
		
		//小規模店番
		$CEL['SYOK'] = sprintf("%3d", $CEL['SYOK']);
		$tmpSQL = "SELECT * FROM dbo.店舗マスタ WHERE 小規模店番={$CEL['SYOK']}";
		if($tmpCEL = $FncPDO->query($tmpSQL)->fetch()){	
			$CEL['SYNA'] = $tmpCEL[mb_convert_encoding("店舗名","sjis","utf8")];
		}else{
			$CEL['SYNA'] = "不明";
		}
		//人格コード
		$tmpSQL = "SELECT * FROM dbo.人格マスタ WHERE 人格コード={$CEL['JNUM']}";
		if($tmpCEL = $FncPDO->query($tmpSQL)->fetch()){	
			$CEL['JINK'] = $tmpCEL[mb_convert_encoding("名称","sjis","utf8")];
		}else{
			$CEL['JINK'] = "不明";
		}
		
//		echo "<td class='text-center bg-warning'><strong>{$CEL['TUKI']}</strong></td>";
		echo "<td class='text-center bg-primary'><strong>{$CEL['TUKI']}</strong></td>";
		echo "<td>［{$CEL['SYOK']}］{$CEL['SYNA']}<br>
				  ［{$CEL['JNUM']}］{$CEL['JINK']}</td>";
		echo "<td>{$CEL['NAME']}<br>{$CEL['KANA']}</td>";
		echo "<td>{$CEL['DAIH']}</td>";
		echo "<td>{$CEL['YAGO']}</td>";
	}else{
		echo "<tr><td class='text-center bg-warning'><strong>{$CEL['TUKI']}</strong></td><td colspan='5'><p class='text-danger'>顧客が存在しません</p></td></tr>";
	}
}
*/
function CheckTable1($FncSQL, $FncPDO)
{ //フラグ管理処理
	$i = 0;
	$stmt = $FncPDO->query($FncSQL);
	$Cstm = $stmt->fetch();
	
	//変数セット処理
	$CEL['FINK'] = $Cstm[mb_convert_encoding("印鑑存在フラグ","sjis","utf8")];
	$CEL['FZEN'] = $Cstm[mb_convert_encoding("前月存在フラグ","sjis","utf8")];
	$CEL['FTOU'] = $Cstm[mb_convert_encoding("当月存在フラグ","sjis","utf8")];	
	$CEL['FCHE'] = $Cstm[mb_convert_encoding("CCS確認状態","sjis","utf8")];
	
	//戻り値設定
	$RetFLG = 0;
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
	return sprintf("%04d", $RetFLG);
}
?>
<?php //警告回避処理
	if (!isset($_POST['PFLG']) || $_POST['PFLG'] == "" ){
		$FLG = 0;	//FLG=1で更新ボタン押下判定
	}else{
		$FLG = $_POST['PFLG'];
	}
//	GoMaintenance();
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
	
	$TblSQL3   = "SELECT * FROM dbo.印鑑マスタ WHERE 顧客番号={$Key1} AND 最新世代フラグ='True'";
?>
<?php //テーブル更新処理
	GoMaintenance();
	if($FLG == 1){
		$USRNAME = "不明";
		$TmpSQL = "SELECT * FROM dbo.ユーザー管理 WHERE ユーザーID='{$_SESSION['USERID']}'";
		if($tmpCEL = $PDO->query($TmpSQL)->fetch()){
			$USRNAME = $tmpCEL[mb_convert_encoding("ユーザー名","sjis","utf8")];
		}else{
			$USRNAME = "不明";
		}
		$UpdSQL = "UPDATE dbo.フラグ管理マスタ 
				   SET CCS確認状態={$_POST['CCS1']}, 
				   CCS更新日='" . date("Y/m/d H:i:s", time()) . "', 
				   CCS更新者='{$USRNAME}' 
				   WHERE 顧客番号={$_GET['Data1']}";
//		echo $UpdSQL;	//DEBUG
		$res = $PDO->query($UpdSQL);
		echo 	"<script language='javascript' type='text/javascript'>
					alert('データを更新しました。［画面更新］ボタンを押した上で状況を確認してください。');
					self.close();
				</script>";
	}

?>
<!DOCTYPE html>
<html>
<head>
<title>明細(代表者変更)</title>
<?php include('../includes/html/MyHeader.html');?>
</head>
<body>
<?php include('../includes/js/MyTimeOut.js'); ?>
<div class='container-fulid'>
	<h3>代表者検証フォーム</h3>
</div>
<?php include('../includes/html/MyNavigation.html'); ?>

<div class='container-fulid'>
	<div class='row'>
		<div class='col-md-11'>
			<div class='text-right'>
				<input type='button' value='画面を閉じる' onclick='window.close()' class='btn btn-danger btn-lg'>
			</div>
		</div>
	</div>
	<div class='row'>
		<div class='col-md-1'>
		</div>
		<div class='col-md-10'>
			<div class='row'>
				<h1>基本情報</h1>
				<div class='col-md-6'>
						<?php $MyBOX = SetTable1($TblSQL1, $PDO); ?>
				</div>
			</div>
			<div class='row'>
				<div class='col-md-6'>
					<h1>前月オンライン情報</h1>
					<?php SetCIFTable($TblSQL2[0], $PDO, $TblFLG['ZEN'], 0); ?>
				</div>
				<div class='col-md-6'>
					<h1>当月オンライン情報</h1>
					<?php SetCIFTable($TblSQL2[1], $PDO, $TblFLG['ZEN'], 0); ?>
				</div>
			</div>
			<div class='row'>
				<h1>CCS確認状態</h1>
				<form name='update' method='post'>
				<input type='hidden' name='PFLG' value='1'>					
				<?php 
					$BtnMsg = "hidden";							
					if($TblFLG['CHE'] == 0 ){
						$BtnMsg = "submit";
	//					echo "<pre class='bg-primary'>CCS確認状態</pre>";
						echo "<pre class='bg-warning' style='padding:30px;'>";
						echo $MyBOX;
						echo "</pre>";
						/*Confirmボタンをphp内部に記述すると処理をスルーされるため外部に記述*/
					}
				?>
				<input type='<?php echo $BtnMsg; ?>' class='btn-lg btn-block btn-warning' value='状況を更新します' onclick="return confirm('更新してもよろしいですか？');">
				</form>
			</div>
		<h1>印鑑照合システム側情報</h1>
		<?php //Table3作成処理
			require_once '../includes/php/SetInkanTbl.php';
			SetTable3($TblSQL3, $PDO, $TblFLG['INK']);
		?>
		</div>
	</div>
	<div class='row'>
		<div class='col-md-11'>
			<div class='text-right'>
				<input type='button' value='画面を閉じる' onclick='window.close()' class='btn btn-danger btn-lg'>
			</div>
		</div>
	</div>
</div>
<div class='container-fulid'>
	<div style='height:60px;'></div>
		<?php
			require_once '../includes/html/MyFooter.html';
			GetFileTime(getlastmod());
		?>
</div>
</body>
</html>