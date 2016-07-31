<?php //Connectiong Server
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
//SQL条件文生成関数
function WhereSQL($FuncChk, $ChkVal)
{
	switch($FuncChk){
	case 'CHEC':
		switch($ChkVal){
		case 0:
			return "印鑑確認状態<=3";
			break;
		case 1:
			return "印鑑確認状態=0";
			break;
		case 2:
			return "印鑑確認状態=1 OR 印鑑確認状態=2";
			break;
		case 3:
			return "印鑑確認状態=3";
			break;
/*		case 3:
			return "CCS確認状態=1";
			break;
		case 4:
			return "CCS確認状態=3";
			break;*/
		}
		break;
	case 'DAIH':
		switch($ChkVal){
		case 0:
			return "";
			break;
		case 1:
			return "代表者存在フラグ4='True'";
			break;
		case 2:
			return "代表者存在フラグ4='False'";
			break;
		}
		break;
	case 'INKA':
		switch($ChkVal){
		case 0:
			return "";
			break;
		case 1:
			return "共通印鑑フラグ='True'";
			break;
		case 2:
			return "共通印鑑フラグ='False'";
			break;
		}
		break;
	case 'REGI':
		switch($ChkVal){
		case 0:
			return "";
			break;
		case 1:
			return "オンライン登録フラグ='True'";
			break;
		case 2:
			return "オンライン登録フラグ='False'";
			break;
		case 3:
			return "印鑑登録フラグ='True'";
			break;
		case 4:
			return "印鑑登録フラグ='False'";
			break;
		}
		break;
	}
}
//誕生日<->年齢変換関数
function birthToAge($ymd)
{
 
	$base  = new DateTime();
	$today = $base->format('Ymd');
 
	$birth    = new DateTime($ymd);
	$birthday = $birth->format('Ymd');
 
	$age = (int) (($today - $birthday) / 10000);
 
	return $age;
}
//テーブル生成関数
function SetList($FuncSQL, $FuncPDO, $tmpCNT)
{
	//+==============================================================
	//|　テーブルレコード取得　
	//+==============================================================
	//|
	$i = 0;
	$FuncSQL .= "ORDER BY 小規模店番";
	foreach ($FuncPDO->query($FuncSQL) as $Row){
		$Fct[$i]['NUMB'] = $tmpCNT + $i + 1;
		$Fct[$i]['CUST'] = sprintf("%08d",$Row[mb_convert_encoding("顧客番号","sjis","utf8")]);
		$Fct[$i]['STOR'] = sprintf("%03d", $Row[mb_convert_encoding("小規模店番","sjis","utf8")]);
//		$Fct[$i]['DAIH'] = $Row[mb_convert_encoding("代表者変更フラグ","sjis","utf8")];
		$Fct[$i]['CCSC'] = $Row[mb_convert_encoding("印鑑確認状態","sjis","utf8")];
//		$Fct[$i]['FTOU'] = $Row[mb_convert_encoding("当月存在フラグ","sjis","utf8")];
//		$Fct[$i]['FZEN'] = $Row[mb_convert_encoding("前月存在フラグ","sjis","utf8")];
		
		$MySQL ="SELECT * FROM dbo.店舗マスタ WHERE 小規模店番={$Fct[$i]['STOR']}";
		if($tmpCEL = $FuncPDO->query($MySQL)->fetch()){	
			$tmpStore = $tmpCEL[mb_convert_encoding("店舗名","sjis","utf8")];
		}else{
			$tmpStore = "不明";
		}
		
		$ZenCls[$i] = "";
		$TouCls[$i] = "";
//		$dmp = "";
/*		$MySQL = "SELECT * FROM dbo.前月CIF WHERE 顧客番号='{$Fct[$i]['CUST']}'";
		if($tmpCEL = $FuncPDO->query($MySQL)->fetch()){	
			$tmpZen[0] = $tmpCEL[mb_convert_encoding("漢字氏名","sjis","utf8")];
			$tmpZen[1] = $tmpCEL[mb_convert_encoding("氏名","sjis","utf8")];
			$tmpZen[2] = $tmpCEL[mb_convert_encoding("漢字代表者名","sjis","utf8")];
			$tmpZen[3] = $tmpCEL[mb_convert_encoding("漢字屋号","sjis","utf8")];
		}else{
			$tmpZen[0] = "顧客が存在しません";
			$tmpZen[1] = "存在しません";
			$tmpZen[2] = "存在しません";
			$tmpZen[3] = "存在しません";
			$ZenCls[$i] = " class='text-danger'";
//			$dmp = "Danger1";
		}
*/		
		$MySQL = "SELECT * FROM dbo.当月CIF WHERE 顧客番号='{$Fct[$i]['CUST']}'";
		if($tmpCEL = $FuncPDO->query($MySQL)->fetch()){
			$tmpTou[0] = $tmpCEL[mb_convert_encoding("漢字氏名","sjis","utf8")];
			$tmpTou[1] = $tmpCEL[mb_convert_encoding("氏名","sjis","utf8")];
			$tmpTou[2] = $tmpCEL[mb_convert_encoding("漢字代表者名","sjis","utf8")];
			$tmpTou[3] = $tmpCEL[mb_convert_encoding("漢字屋号","sjis","utf8")];
			$tmpTou[4] = $tmpCEL[mb_convert_encoding("総口座数","sjis","utf8")];
			$tmpTou[5] = $tmpCEL[mb_convert_encoding("顧客開設日","sjis","utf8")];
		}else{
			$tmpTou[0] = "顧客が存在しません";
			$tmpTou[1] = "存在しません";
			$tmpTou[2] = "存在しません";
			$tmpTou[3] = "存在しません";
			$tmpTou[4] = "－";
			$tmpTou[5] = "－";
			$TouCls[$i] = " class='text-danger'";
//			$dmp = "Danger2";
		}
		
//		for($j = 0;$j < 4;$j++){
//			if($tmpZen[$j] <> " "){
//				$tmpZen[0] = "前月：" . $tmpZen[$j];
//				$tmpZen[0] = "前月：" . $tmpZen[0];
//			}
//			if($tmpTou[$j] <> " "){
//				$tmpTou[0] = "当月：" . $tmpTou[$j];	
				$tmpTou[0] = "当月：" . $tmpTou[0];	
//			}
//		}
		$Fct[$i]['SNAM'] = $tmpStore;
//		$Fct[$i]['ZKNA'] = $tmpZen[0];
//		$Fct[$i]['ZSNA'] = $tmpZen[1];
//		$Fct[$i]['ZDNA'] = $tmpZen[2];
//		$Fct[$i]['ZYNA'] = $tmpZen[3];
		$Fct[$i]['TKNA'] = $tmpTou[0];
		$Fct[$i]['TSNA'] = $tmpTou[1];
		$Fct[$i]['TDNA'] = $tmpTou[2];
		$Fct[$i]['TYNA'] = $tmpTou[3];
		$Fct[$i]['TSKO'] = $tmpTou[4] . "件";
		$Fct[$i]['KAIS'] = $tmpTou[5];
		$i++;
	}
	//|
	//+==============================================================

	//+==============================================================
	//|　HTMLテーブル生成　
	//+==============================================================
	//|
	if($i == 0){
		echo "<pre class='bg-danger'>指定条件では該当データが検索できませんでした。</pre>\n";
	}else{
		echo "<table class='table'>";
		echo "<tr class='bg-info'><th class='text-center'>連番</th><th class='text-center'>小規模店番</th><th class='text-center'>顧客番号</th><th class='text-center'>漢字氏名</th><th class='text-center'>漢字代表者名</th><th class='text-center'>漢字屋号</th><th class='text-center'>CCS照会</th><th></th></tr>\n";
		for($j = 0; $j < $i; $j++){
			//接続先ページ生成
			$MyURL = "http://192.1.10.181/cvs/detail.php?Data1={$Fct[$j]['CUST']}&Data2={$Fct[$j]['STOR']}";
			$MyURL = "<a href='{$MyURL}' target='_blank'>{$Fct[$j]['CUST']}<a>";
			//代表者変更
/*			if($Fct[$j]['DAIH'] == True){
				$DAIH_Msg = "変更されています";
			}else{
				$DAIH_Msg = "";
			}*/
			//CCS確認状態・確認ボタン
			$URLColor = "";
			switch($Fct[$j]['CCSC']){
				case 0:
					$CCSC_Msg = "<pre class='bg-danger'>印影が登録されていません</pre>";
					break;
				case 1:
					$CCSC_Msg = "<pre class='bg-success'>登録しました</pre>";
					break;
				case 2:
					$CCSC_Msg = "<pre class='bg-warning'>登録不要です</pre>";
					break;
				case 3:
					$CCSC_Msg = "<pre class='bg-default'>照会不要です</pre>";
					break;
				default:
					$CCSC_Msg = "<pre class='bg-default'>その他（想定外）</pre>";
//					$URLColor = " class='bg-danger'";
					break;
			}
/*			if($Fct[$j]['CCSC'] == 0){
			}elseif($Fct[$j]['CCSC'] == 1){
				$CCSC_Msg = "照会されています";
			}else{
				$CCSC_Msg = "照会不要です";
			}*/
			//テーブル生成処理
			echo "<tr>\n";
			echo "<td class='text-center'>{$Fct[$j]['NUMB']}</td>\n";
			echo "<td class='text-center'>[{$Fct[$j]['STOR']}]<br>{$Fct[$j]['SNAM']}</td>\n";
			echo "<td class='text-center'><pre{$URLColor}>{$MyURL}</pre></td>\n";
			echo "<td><p{$TouCls[$j]}>{$Fct[$j]['TKNA']}</p></td>\n";
			echo "<td><p{$TouCls[$j]}>{$Fct[$j]['TSKO']}</p></td>\n";
			echo "<td><p{$TouCls[$j]}>{$Fct[$j]['KAIS']}</p></td>\n";
			echo "<td class='text-center'>{$CCSC_Msg}</td>\n";
			echo "</tr>\n";	
		}
		echo "</table>";
	}
	//|
	//+==============================================================
}
//セレクトボックス生成関数
function SetSelect($tmpVal, $tmpLbl, $tmpTag){
	$Max = count($tmpVal);
	for($i = 0;$i < $Max;$i++){
		if($tmpVal[$i] == $tmpTag){
			echo "<option value='{$tmpVal[$i]}' selected='selected'>{$tmpLbl[$i]}</option>\n";			
		}else{
			echo "<option value='{$tmpVal[$i]}'>{$tmpLbl[$i]}</option>\n";
		}
	}
}
?>
<?php //警告回避
	//FLG = 0 : 初回
	//FLG = 1 : 条件変更
	//FLG = 2 : ページ遷移

	if(!isset($_POST["PFLG"]) || $_POST["PFLG"] == "" ){
		$FLG = 0;
	}else{
		$FLG = $_POST['PFLG'];
	}
//	echo var_dump($FLG);
//	echo "<br>";
?>
<?php //ユーザー情報セット
	$UName = "";
	$MySQL = "SELECT * FROM dbo.ユーザー管理 WHERE ユーザーID = '" . $_SESSION["USERID"] . "'";
	foreach ($PDO->query($MySQL) as $Row){
		$UName   = $Row[mb_convert_encoding("ユーザー名","sjis","utf8")];
//		$UTenban = $Row[mb_convert_encoding("所属店舗","sjis","utf8")];
	}
	switch($_SESSION['USERSTR']){
		case 1:
			$StrTen = "本店";
			break;
		case 2:
			$StrTen = "五島";
			break;
		case 4:
			$StrTen = "県北";
			break;
		case 5:
			$StrTen = "壱岐";
			break;
		case 6:
			$StrTen = "対馬";
			break;
		case 9:
			$StrTen = "管理者";
			break;
	}
?>
<?php //初期値設定
//	echo "所属店舗=" . $_SESSION['USERSTR'] . "<br>";
	if($FLG == 0){
		$SEL['STOR'] =  990 + $_SESSION['USERSTR'];
		$SEL['CHEC'] =  0;
//		$SEL['DAIH'] =  1;
//		$SEL['INKA'] =  0;
//		$SEL['REGI'] =  0;
	}elseif($FLG == 1){
		$SEL['STOR'] =  $_POST['Store'];
		$SEL['CHEC'] =  $_POST['Check'];
//		$SEL['DAIH'] =  $_POST['Daihyo'];
//		$SEL['INKA'] =  $_POST['Inkan'];
//		$SEL['REGI'] =  $_POST['Regist'];
	}elseif($FLG == 2){
//		echo "SEL_Buf = {$_POST['PCHK']}<br>";
		$TenSQL  = $_POST['BefTen'];
		$WheSQL  = str_replace("*", "'", $_POST['BefWhe']);
//		echo $_POST['PCHK'];
		$SEL['STOR'] = substr($_POST['PCHK'], 0, 4);
		$SEL['CHEC'] = substr($_POST['PCHK'], 4, 1);
//		$SEL['DAIH'] = substr($_POST['PCHK'], 5, 1);
//		$SEL['INKA'] = substr($_POST['PCHK'], 6, 1);
//		$SEL['REGI'] = substr($_POST['PCHK'], 7, 1);
	}
	$PDFFile = "http://192.1.10.181/cvs/list/001" . $_SESSION['USERSTR'] . ".PDF";

//	echo "*********************************<br>";
//	foreach($SEL as $key=>$value){
//		echo "{$key}...{$value}<br>";
//	}
//	echo "*********************************<br>";

?>
<?php //SQL文セット	
	if($FLG <> 2){
		$WheSQL   = "";
		$TenSQL   = "";
		$MySQL    = "";
		$FirstFLG = True;
		$i = 0;
		foreach($SEL as $Key=>$Data){
			$WheSQL  .= WhereSQL($Key, $Data);	
		}
		
		//小規模店番選択
/*		if($SEL['STOR'] == 999){
			$TenSQL = "";	
		}elseif($SEL['STOR'] >= 990){
			$TenSQL = "管轄店舗={$_SESSION['USERSTR']}";	
		}else{
			$TenSQL .= "小規模店番=" . $SEL['STOR'];
		}
*/
		if($_SESSION['USERSTR'] == 9){
			if($SEL['STOR'] == 999){
				$TenSQL = "";
			}else{
				$TenSQL = "管轄店舗={$SEL['STOR']}";				
			}
		}else{
			if($SEL['STOR'] >= 990){
				$TenSQL = "管轄店舗={$_SESSION['USERSTR']}";	
			}else{
				$TenSQL .= "小規模店番=" . $SEL['STOR'];
			}
		}
	}
?>
<?php //カウントページSQL文セット
	$CntSQL = "SELECT COUNT(*) FROM dbo.フラグ管理マスタ ";
	$TblSQL  = "SELECT * FROM (SELECT ROW_NUMBER() OVER(ORDER BY 顧客番号) as RowNum, * FROM dbo.フラグ管理マスタ ";
	if($TenSQL == ""){
//		$CntSQL .= "WHERE (" . $TenSQL . ")";
//		$TblSQL  .= "WHERE (" . $TenSQL . ")";
		$CntSQL .= "WHERE (" . $WheSQL . ")";
		$TblSQL  .= "WHERE (" . $WheSQL . ")";
	}else{
		$CntSQL  .= "WHERE (" . $TenSQL . ") AND (" . $WheSQL . ")";
		$TblSQL  .= "WHERE (" . $TenSQL . ") AND (" . $WheSQL . ")";
//		$CntSQL  .= "WHERE (" . $WheSQL . ")";
//		$TblSQL  .= "WHERE (" . $WheSQL . ")";
	}

	echo "=========================================================<br>";
	echo "[CntSQL]<br>";
	echo $CntSQL . "<br>";
	echo "<br>";
	echo "[TenSQL]<br>";
	echo $TenSQL . "<br>";
	echo "<br>";
	echo "[WheSQL]<br>";
	echo $WheSQL . "<br>";
	echo "=========================================================<br>";

	$stmt = $PDO->query($CntSQL);
	$DataCNT = $stmt->fetchColumn();
	$SplPage = $_SESSION['USERPAG'];
	$MaxPage = ceil($DataCNT / $SplPage);
	if($FLG == 2){
		$NowPage = $_POST['PageList'];
	}else{
		$NowPage = 1;
	}
	$PgNum   = ($NowPage - 1) * $SplPage;
	$TblSQL .= ") AS RowNumberd_Result WHERE RowNum Between " . ($PgNum + 1) . " AND " . ($PgNum + $SplPage);
//	echo $TblSQL;
/*	
	echo "データ件数　＝" . $DataCNT . "<br>";
	echo "最大ページ数＝" . $MaxPage . "<br>";
	echo "現在ページ数＝" . $NowPage . "<br>";
*/
?>
<?php //リストボックスセット
	//店舗リスト
	if($_SESSION['USERSTR'] == 9){
		$arrStrVal[0] = 999;
		$arrStrLbl[0] = "全店舗分";
		$arrStrVal[1] = 1;
		$arrStrLbl[1] = "本店";
		$arrStrVal[2] = 2;
		$arrStrLbl[2] = "五島支店";
		$arrStrVal[3] = 4;
		$arrStrLbl[3] = "県北支店";
		$arrStrVal[4] = 5;
		$arrStrLbl[4] = "壱岐支店";
		$arrStrVal[5] = 6;
		$arrStrLbl[5] = "対馬支店";
		$StrTarget    = $SEL['STOR'];
	}else{
		$SelSQL = "SELECT * FROM dbo.店舗マスタ WHERE 管轄店舗=" . $_SESSION['USERSTR'];
		$i = 0;
		$arrStrVal[$i] = 990 + $_SESSION['USERSTR'];
		$arrStrLbl[$i] = "県南全て";
		$i = 1;
		foreach ($PDO->query($SelSQL) as $Row){
			if($Row[mb_convert_encoding("小規模店番","sjis","utf8")] < 990){	
				$arrStrVal[$i] = $Row[mb_convert_encoding("小規模店番","sjis","utf8")];
				$arrStrLbl[$i] = "[" . sprintf("%03d", $arrStrVal[$i]) . "] " . $Row[mb_convert_encoding("店舗名","sjis","utf8")];
				$StrTarget     = $SEL['STOR'];
				//echo "{$arrStrVal[$i]}  {$arrStrLbl[$i]}<br>";
			}
			$i++;
		}
	}
	//ページリスト
	for($i = 0;$i < $MaxPage;$i++){
		$arrPageVal[$i] = ($i + 1);
		$arrPageLbl[$i] = ($i + 1) . " / {$MaxPage} ページ";
		$PageTarget     = $NowPage;
	}
	for($i = 0;$i < 4;$i++){
		$arrCheckVal[$i] = $i;
		switch($i){
		case 0:
			$arrCheckLbl[$i] = "すべて";
			break;
		case 1:
			$arrCheckLbl[$i] = "未確認";
			break;
		case 2:
			$arrCheckLbl[$i] = "照会済み";
			break;
		case 3:
			$arrCheckLbl[$i] = "照会不要";
			break;
//		case 3:
//			$arrCheckLbl[$i] = "照会が済んでいる";
//			break;
//		case 4:
//			$arrCheckLbl[$i] = "照会不要（顧客抹消済み）";
//			break;
		}
	}
	$CheckTarget = $SEL['CHEC'];
?>
<?php //ポスト状態取得
//	echo "<br>==[Select Box]==================================<br>\n";
	$SEL_Buf  = sprintf("%04d", $SEL['STOR']);
	$SEL_Buf .= $SEL['CHEC'];
//	$SEL_Buf .= $SEL['DAIH'];
//	$SEL_Buf .= $SEL['INKA'];
//	$SEL_Buf .= $SEL['REGI'];
//	echo " PSTORE={$SEL['STOR']}<br>";
//	echo " PCHECK={$SEL['CHEC']}<br>";
//	echo "PDAIHYO={$SEL['DAIH']}<br>";
//	echo "{$SEL['INKA']}<br>";
//	echo "{$SEL['REGI']}<br>";
//	echo "===========================================<br>\n";
?>
<!DOCTYPE html>
<html>
<head>
<title>リスト画面</title>
<meta charset="utf-8">
<!-- Bootstrap -->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- BootstrapのCSS読み込み -->
<link href="http://192.1.10.181/cvs/css/bootstrap.min.css" rel="stylesheet">
<link href="http://192.1.10.181/cvs/css/sticky-footer.css" rel="stylesheet">
<!-- jQuery読み込み -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<!-- BootstrapのJS読み込み -->
<script src="http://192.1.10.181/cvsjs/bootstrap.min.js"></script>
<!-- Bootstrap -->
<link rel="shortcut icon" href="http://192.1.10.181/cvs/RNoC.ico" type="image/x-icon">
<link rel="icon" href="http://192.1.10.181/cvs/RNoC.ico" type="image/x-icon">
</head>
<body>
<script type="text/javascript">
<!--
var Sample = {
wait : 600, //待機時間（秒)
url : "http://192.1.10.181/cvs/logout.php" //ジャンプ先URL
};
Sample.record = function() {
this.timeout = +new Date() + this.wait * 1000;
};
Sample.check = function() {
if (this.timeout == undefined) this.record();
if (this.timeout - new Date() < 0) location.href = this.url;
}
//@cc_on
document./*@if(1)attachEvent('on' + @else @*/addEventListener(/*@end @*/
'mousemove', function(){ Sample.record() }, false);
setInterval(function(){ Sample.check() }, 500);
//-->
</script>
<div class='container-fulid'>
	<div class='page-header bg-primary'>
		<br>
		<h1>印鑑登録検証フォーム</h1>
		<br>
	</div>
		<nav class="navbar navbar-default">
		  <div class="container-fluid">
		  <div class="collapse navbar-collapse">
		  <!-- 右寄せになる部分 ================ -->
			  <ul class="nav navbar-nav navbar-right">
				<!-- リンクのみ -->
				  <li><a href="http://192.1.10.181/cvs/index.php">トップ画面</a></li>
				  <li><a href="http://192.1.10.181/cvs/logout.php">ログアウト</a></li>
				  <li><a href="javascript:;" onclick="window.close();">システム終了</a></li>
				<!-- Nav6 ドロップダウン -->
<!--				  <li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <span class="caret"></span></a>
					<ul class="dropdown-menu">
					  <li><a href="#">link 1</a></li>
					</ul>
				  </li>-->
			  </ul>
		  </div>
		  </div>
		</nav>
	</div>
	<div class='container-fulid'>
		<div class='row'>
			<div class='col-md-1'>
			</div>
			<div class='col-md-4'>
				<pre class='bg-info'>検索条件</pre>
				<form name='form1' method='post' class='form-horizontal'>
					<div class='form-group'>
						<label for='name' class='control-label col-md-4 text-right'>小規模店番</label>
						<div class='col-md-8'><select name='Store'><?php SetSelect($arrStrVal, $arrStrLbl, $StrTarget); ?></select></div>
					</div>
					<div class='form-group'>
						<label for='name' class='control-label col-md-4 text-right'>印鑑登録状態</label>
						<div class='col-md-8'><select name='Check'><?php SetSelect($arrCheckVal, $arrCheckLbl, $CheckTarget); ?></select></div>
					</div>
<!--					<div class='form-group'>
						<label for='name' class='control-label col-md-4 text-right'>代表者</label>						
						<div class='col-md-8'><select name='Daihyo'><?php SetSelect($arrDaihyoVal, $arrDaihyoLbl, $DaihyoTarget); ?></select></div>
					</div>-->
<!--				<div class='form-group'>
						<label for='name' class='control-label col-md-3 text-right'>顧客番号</label>						
						<div class='col-md-9'><input type='text' name='CusNum' placeholder='任意入力項目'></div>
					</div>-->
					<div class='form-group'>
						<input type="hidden" name='PFLG' value='1'>
						<input type="hidden" name='BefTen' value='<?php echo $TenSQL; ?>'>
						<input type="hidden" name='BefWhe' value='<?php echo str_replace("'", "*", $WheSQL);?>'>
					</div>
					<div class='form-group'>
						<!--<label for='name' class='control-label col-md-3'></label>-->
						<div class='col-md-12'><input type='submit' value='検索' class='btn-block btn-primary btn-lg'></div>
					</div>
<!--				<div class='form-group'>
						<div class='col-md-12'><input type="button" value="帳票表示" class='btn-lg btn-warning btn-block' onclick="window.open('<?php echo $PDFFile; ?>')"/></div>
					</div>-->
				</form>
			</div>
			<div class='col-md-4 col-md-offset-2'>
				<pre class='bg-success'>情報</pre>
				<table class='table'>
				<tr><td class='text-right'>ユーザー名：</td><td class='text-left'><?php echo $UName . "さん"; ?></td></tr>
				<tr><td class='text-right'>所属店舗：</td><td class='text-left'><?php echo $StrTen; ?></td></tr>
				<tr><td class='text-right'>該当件数：</td><td class='text-left'><?php echo "{$DataCNT}件"; ?></td></tr>
				</table>
				<form method='post'>
					<input type="hidden" name='PFLG' value='2'>
					<input type="hidden" name='PCHK' value='<?php echo $SEL_Buf; ?>'>
					<input type="hidden" name='MYST' value='<?php echo $MyStore; ?>'>
					<input type="hidden" name='PageNum' value='<?php echo $NowPage; ?>'>
					<input type="hidden" name='PageList' value='<?php echo $NowPage; ?>'>
					<input type="hidden" name='BefTen' value='<?php echo $TenSQL; ?>'>
					<input type="hidden" name='BefWhe' value='<?php echo str_replace("'", "*", $WheSQL);?>'>
					<input type='submit' value='画面更新' class='btn-lg btn-success btn-block'>
				</form>
			</div>
			<div class='col-md-1'>
			</div>
		</div>
	</div>
	<h1>代表者検証リスト</h1>
	<div class='container-fulid'>
		<?php
			SetList($TblSQL, $PDO, $PgNum);
		?>
	</div>
	<form name='form2' method='post' class='text-right'>
		<div class='form-group'>
			<label for='name'>ページ数</label>
			<select name='PageList'><?php SetSelect($arrPageVal, $arrPageLbl, $PageTarget); ?></select>
			<input type="hidden" name='PFLG' value='2'>
			<input type="hidden" name='PCHK' value='<?php echo $SEL_Buf; ?>'>
			<input type="hidden" name='MYST' value='<?php echo $MyStore; ?>'>
			<input type="hidden" name='PageNum' value='<?php echo $_POST['PageList']; ?>'>
			<input type="hidden" name='BefTen' value='<?php echo $TenSQL; ?>'>
			<input type="hidden" name='BefWhe' value='<?php echo str_replace("'", "*", $WheSQL);?>'>
			<input type='submit' value='移動' class='btn-xs btn-primary'>
		</div>
	</form>
	</div>
</div>
<footer class="footer">
</footer>
</body>
</html>