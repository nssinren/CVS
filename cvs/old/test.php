﻿<?php //Connectiong Server
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
<?php //Functions
//SQL条件文生成関数
function WhereSQL($FuncChk, $ChkVal)
{
	switch($FuncChk){
	case 'CHEC':
		switch($ChkVal){
		case 0:
			return "CCS確認状態<=3";
			break;
		case 1:
			return "CCS確認状態=0";
			break;
		case 2:
			return "CCS確認状態=1 OR CCS確認状態=2";
			break;
		case 3:
			return "CCS確認状態=3";
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
		$Fct[$i]['JNUM'] = sprintf("%03d", $Row[mb_convert_encoding("人格コード","sjis","utf8")]);
		$Fct[$i]['TIME'] = date("Y年n月j日", strtotime($Row[mb_convert_encoding("印鑑更新日時","sjis","utf8")]));
		$Fct[$i]['CCSC'] = $Row[mb_convert_encoding("CCS確認状態","sjis","utf8")];
		//小規模店番
		$MySQL ="SELECT * FROM dbo.店舗マスタ WHERE 小規模店番={$Fct[$i]['STOR']}";
		if($tmpCEL = $FuncPDO->query($MySQL)->fetch()){	
			$tmpStore = $tmpCEL[mb_convert_encoding("店舗名","sjis","utf8")];
		}else{
			$tmpStore = "不明";
		}
		$Fct[$i]['SNAM'] = "[{$Fct[$i]['STOR']}]{$tmpStore}";
		//人格コード
		$MySQL ="SELECT * FROM dbo.人格マスタ WHERE 人格コード={$Fct[$i]['JNUM']}";
		if($tmpCEL = $FuncPDO->query($MySQL)->fetch()){	
			$tmpJinkaku = $tmpCEL[mb_convert_encoding("名称","sjis","utf8")];
		}else{
			$tmpJinkaku = "不明";
		}
		$Fct[$i]['JNUM'] = "[{$Fct[$i]['JNUM']}]{$tmpJinkaku}";
		
		$ZenCls[$i] = "";
		$TouCls[$i] = "";

		$MySQL = "SELECT * FROM dbo.前月CIF WHERE 顧客番号='{$Fct[$i]['CUST']}'";
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
		}
		
		$ZenDaih = False;
		$ZenYago = False;
		if($tmpZen[2] === " "){
			$tmpZen[2] = "　";
			$ZenDaih = True;
		}
		if($tmpZen[3] === " "){
			$tmpZen[3] = "　";
			$ZenYago = True;
		}
		
		$MySQL = "SELECT * FROM dbo.当月CIF WHERE 顧客番号='{$Fct[$i]['CUST']}'";
		if($tmpCEL = $FuncPDO->query($MySQL)->fetch()){
			$tmpTou[0] = $tmpCEL[mb_convert_encoding("漢字氏名","sjis","utf8")];
			$tmpTou[1] = $tmpCEL[mb_convert_encoding("氏名","sjis","utf8")];
			$tmpTou[2] = $tmpCEL[mb_convert_encoding("漢字代表者名","sjis","utf8")];
			$tmpTou[3] = $tmpCEL[mb_convert_encoding("漢字屋号","sjis","utf8")];
		}else{
			$tmpTou[0] = "顧客が存在しません";
			$tmpTou[1] = "存在しません";
			$tmpTou[2] = "存在しません";
			$tmpTou[3] = "存在しません";
		}

		$DaihCls[$i] = "";
		$YagoCls[$i] = "";

		$TouDaih = False;
		$TouYago = False;
		if($tmpTou[2] === " "){
			$tmpTou[2] = "(※1)";
			$DaihCls[$i] = " class='text-danger'";
			$TouDaih = True;
		}
		if($tmpTou[3] === " "){
			$tmpTou[3] = "　";
		}else{
			$YagoCls[$i] = " class='text-danger'";
			$tmpTou[3] .= "(※2)";
			$TouYago = True;
		}
		
		if($tmpZen[2] === "－" AND $tmpTou[2] === "－" ){
			$tmpZen[2] = "";
			$tmpTou[2] = "";
		}
		if($tmpZen[3] === "－" AND $tmpTou[3] === "－" ){
			$tmpTou[3] = "";
			$tmpZen[3] = "";			
		}
		
		$Fct[$i]['ZKNA'] = $tmpZen[0];
		$Fct[$i]['ZSNA'] = $tmpZen[1];
		$Fct[$i]['ZDNA'] = $tmpZen[2];
		$Fct[$i]['ZYNA'] = $tmpZen[3];
		$Fct[$i]['TKNA'] = $tmpTou[0];
		$Fct[$i]['TSNA'] = $tmpTou[1];
		$Fct[$i]['TDNA'] = $tmpTou[2];
		$Fct[$i]['TYNA'] = $tmpTou[3];
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
		echo "<table id='test' class='tablesorter' style='font-size: 14px;'>";
		echo "<thead>
			  <tr class='bg-info'>
			  <th class='text-center'>連番</th>
			  <th class='text-center'>顧客番号</th>
			  <th>小規模店番／人格</th>
			  <th>漢字氏名</th>
			  <th>漢字代表者名</th>
			  <th>漢字屋号</th>
			  <th>最終更新日</th>
			  <th class='text-center'>CCS照会</th>
			  </tr></thead><tbody>\n";
		for($j = 0; $j < $i; $j++){
			//接続先ページ生成
//			var_dump($SVRURL);
			$MyURL = "./rep_detail.php?Data1={$Fct[$j]['CUST']}&Data2={$Fct[$j]['STOR']}";
			$MyURL = "<a href='{$MyURL}' target='_blank'>{$Fct[$j]['CUST']}<a>";
			//CCS確認状態・確認ボタン
			switch($Fct[$j]['CCSC']){
				case 0:
					$CCSC_Msg = "<pre class='bg-danger'>照会してください</pre>";
					break;
				case 1:
					$CCSC_Msg = "<pre class='bg-success'>既に照会しています</pre>";
					break;
				case 2:
					$CCSC_Msg = "<pre class='bg-warning'>照会しました</pre>";
					break;
				case 3:
					$CCSC_Msg = "<pre class='bg-default'>照会不要です</pre>";
					break;
				default:
					$CCSC_Msg = "<pre class='bg-default'>その他（想定外）</pre>";
					break;
			}
			//テーブル生成処理
			echo "<tr>\n";
			echo "<td class='text-center'>{$Fct[$j]['NUMB']}</td>\n";
			echo "<td class='text-center'><pre>{$MyURL}</pre></td>\n";
			echo "<td class='text-left'><p>{$Fct[$j]['SNAM']}</p><p>{$Fct[$j]['JNUM']}</p></td>\n";
			echo "<td><p>当月：{$Fct[$j]['TKNA']}</p><p>前月：{$Fct[$j]['ZKNA']}</p></td>\n";
			echo "<td><p{$DaihCls[$j]}>{$Fct[$j]['TDNA']}</p><p>{$Fct[$j]['ZDNA']}</p></td>\n";
			echo "<td><p{$YagoCls[$j]}>{$Fct[$j]['TYNA']}</p><p>{$Fct[$j]['ZYNA']}</p></td>\n";
			echo "<td class='text-center'>{$Fct[$j]['TIME']}</td>\n";
			echo "<td class='text-center'>{$CCSC_Msg}</td>\n";		
			echo "</tr>\n";	
		}
		echo "</tbody></table>";
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
//	var_dump($SVRURL);
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
//	$PDFFile = "http://192.1.10.181/cvs/list/001" . $_SESSION['USERSTR'] . ".PDF";

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
/*
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
*/
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
<title>リスト　代表者変更</title>
	<div class='bg-primary'><?php include('./includes/html/MyHeader.php'); ?></div>
</head>
<body>
<?php include('./includes/js/MyTimeOut.js'); ?>
<script type="text/javascript">
   $(document).ready(function() 
       { 
           $("#test").tablesorter({
			   headers:{
				   3:{sorter:false},
				   4:{sorter:false},
				   5:{sorter:false}
			   }
		   });
       } 
   ); 
</script>

<div class='container-fulid'>
	<div class='page-header bg-primary'>
		<br>
		<h3>代表者検証フォーム－代表者変更</h3>
		<br>
	</div>
	<?php include('./includes/html/MyNavigation.html'); ?>
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
					<label for='name' class='control-label col-md-4 text-right'>CCS確認状態</label>
					<div class='col-md-8'><select name='Check'><?php SetSelect($arrCheckVal, $arrCheckLbl, $CheckTarget); ?></select></div>
				</div>
				<div class='form-group'>
					<input type="hidden" name='PFLG' value='1'>
					<input type="hidden" name='BefTen' value='<?php echo $TenSQL; ?>'>
					<input type="hidden" name='BefWhe' value='<?php echo str_replace("'", "*", $WheSQL);?>'>
				</div>
				<div class='form-group'>
					<div class='col-md-12'><input type='submit' value='検索' class='btn-block btn-primary btn-lg'></div>
				</div>
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

<h3>代表者検証リスト</h3>
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
<p>※1：代表者の登録がされていません。</p>
<p>※2：漢字屋号が登録されています。</p>
	<?php
		require_once '../includes/html/MyFooter.html';
		GetFileTime(getlastmod());
	?>
</body>
</html>