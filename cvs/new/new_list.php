<?php //Connectiong Server
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
//SQL条件文生成関数
function WhereSQL($FuncChk, $ChkVal)
{
	switch($FuncChk){
	case 'CHEC':
		switch($ChkVal){
		case 0:
			return "新規確認状態<4";
			break;
		case 1:
			return "新規確認状態=0";
			break;
		case 2:
			return "新規確認状態=1 OR 新規確認状態=2";
			break;
		}
		break;
	case 'SHIN':
		switch($ChkVal){
		case 0:
			return "";
			break;
		case 1:
			return "顧客開設フラグ='True' AND 口座開設フラグ='False'";
			break;
		case 2:
			return "顧客開設フラグ='False' AND 口座開設フラグ='True'";
			break;
		case 3:
			return "顧客開設フラグ='True' AND 口座開設フラグ='True'";
			break;			
		}
	}
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
		$Fct[$i]['CUST'] = sprintf("%08d",	$Row[mb_convert_encoding("顧客番号","sjis","utf8")]);
		$Fct[$i]['STOR'] = sprintf("%03d", 	$Row[mb_convert_encoding("小規模店番","sjis","utf8")]);
		$Fct[$i]['NEWC'] = 					$Row[mb_convert_encoding("新規確認状態","sjis","utf8")];
		$Fct[$i]['FTOZ'] = 					$Row[mb_convert_encoding("口座開設フラグ","sjis","utf8")];
		$Fct[$i]['FZMN'] = 					$Row[mb_convert_encoding("前月存在フラグ","sjis","utf8")];
		$Fct[$i]['JNUM'] = 					$Row[mb_convert_encoding("人格コード","sjis","utf8")];
		
		//
		$MySQL ="SELECT * FROM dbo.店舗マスタ WHERE 小規模店番={$Fct[$i]['STOR']}";
		if($tmpCEL = $FuncPDO->query($MySQL)->fetch()){	
			$tmpStore = $tmpCEL[mb_convert_encoding("店舗名","sjis","utf8")];
		}else{
			$tmpStore = "不明";
		}
		$Fct[$i]['SNAM'] = $tmpStore;
		//
		$MySQL ="SELECT * FROM dbo.人格マスタ WHERE 人格コード={$Fct[$i]['JNUM']}";
		if($tmpCEL = $FuncPDO->query($MySQL)->fetch()){	
			$tmpJinkaku = $tmpCEL[mb_convert_encoding("名称","sjis","utf8")];
		}else{
			$tmpJinkaku = "不明";
		}
		$Fct[$i]['JINK'] = $tmpJinkaku;
		
		$TouCls[$i] = "";	
		$MySQL = "SELECT * FROM dbo.当月CIF WHERE 顧客番号='{$Fct[$i]['CUST']}'";
		if($tmpCEL = $FuncPDO->query($MySQL)->fetch()){
			$Fct[$i]['KNAM'] = $tmpCEL[mb_convert_encoding("漢字氏名","sjis","utf8")];
			$Fct[$i]['SIME'] = $tmpCEL[mb_convert_encoding("氏名","sjis","utf8")];
			$Fct[$i]['MDAT'] = $tmpCEL[mb_convert_encoding("顧客開設日","sjis","utf8")];
//			$Fct[$i]['MDAT'] = date("Y年m月d日", strtotime($Fct[$i]['MDAT']));
//			date("Y年m月d日", strtotime($Fct[$j]['TIME']))
		}else{
			$Fct[$i]['KNAM'] = "登録されていません";
			$Fct[$i]['SIME'] = "登録されていません";
			$Fct[$i]['MDAT'] = "－";
			$TouCls[$i] = " class='text-danger'";
		}

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
//		echo "<table class='table'>";
		echo "<table id='List' class='tablesorter' style='font-size:12px;'>";
		echo "<thead>
				  <tr class='bg-info'>
					  <th>連番</th>
					  <th>顧客番号</th>
					  <th>小規模店番（人格）</th>
					  <th>漢字氏名／カナ氏名</th>
					  <th>顧客開設日</th>
					  <th>開設区分</th>
					  <th>CCS照会</th>
				  </tr>
			  </thead>
			  <tbody>\n";
//			  <th class='text-center' colspan='2'>開設区分</th>
		for($j = 0; $j < $i; $j++){
			//接続先ページ生成
			$MyURL = "./new_detail.php?Data1={$Fct[$j]['CUST']}&Data2={$Fct[$j]['STOR']}";
//			$MyURL = "<a href='{$MyURL}' target='_blank'>{$Fct[$j]['CUST']}<a>";
			//当座性
			if($Fct[$j]['FTOZ'] == True){
//				$FTOZ_Msg = "<pre class='bg-success'>当座性新規</pre>";
				$FTOZ_Msg = "<p class='text-info'>当座性新規</p>";
			}else{
				$FTOZ_Msg = "<p> </p>";
			}
			//顧客
			if($Fct[$j]['FZMN'] == False){
//				$FZMN_Msg = "<pre class='bg-info'>顧客新規</pre>";
				$FZMN_Msg = "<p class='text-danger'>顧客新規</p>";
			}else{
				$FZMN_Msg = "<p> </p>";
			}
			//CCS確認状態・確認ボタン
			$URLColor = "";
			switch($Fct[$j]['NEWC']){
				case 0:
					$CCSC_Msg = "<pre class='bg-danger'>照会してください</pre>";
//					$CCSC_Msg = "<pre class='bg-danger'><a href='{$MyURL}' target='_blank'>照会してください</a></pre>";
					break;
				case 1:
					$CCSC_Msg = "<pre class='bg-warning'>照会していない口座があります</pre>";
					break;
				case 2:
					$CCSC_Msg = "<pre class='bg-success'>照会済みです</pre>";
					break;
				case 3:
					$CCSC_Msg = "<pre class='bg-default'>照会不要です</pre>";
					break;
				default:
					$CCSC_Msg = "<pre class='bg-default'>その他（想定外）</pre>";
					break;
			}

			echo "<tr>\n";
			echo "<td class='text-center'>{$Fct[$j]['NUMB']}</td>\n";

			echo "<td class='text-center'><a href='{$MyURL}' target='_blank'><pre>{$Fct[$j]['CUST']}</pre></a></td>\n";

			echo "<td class='text-left'>［{$Fct[$j]['STOR']}］{$Fct[$j]['SNAM']}<br>
				  ［{$Fct[$j]['JNUM']}］{$Fct[$j]['JINK']}</td>\n";
//			echo "<td class='text-center'>{$Fct[$j]['CUST']}</td>\n";
			echo "<td><p{$TouCls[$j]}>{$Fct[$j]['KNAM']}<br>
				  {$Fct[$j]['SIME']}</p></td>\n";
//			echo "<td class='text-center'>{$Fct[$j]['MDAT']}</td>\n";
//			echo "<td class='text-center'>{$FZMN_Msg}</td>\n";
//			echo "<td class='text-center'>{$FTOZ_Msg}</td>\n";
//			echo "<td>" . date("Ymd",strtotime(strval($Fct[$j]['MDAT']))) . "</td>";

//			echo "<td　class='{sortValue: " . $Fct[$j]['MDAT'] . "}'>" . date("Y年m月d日", strtotime(intval($Fct[$j]['MDAT']))) . "</td>";

			echo "<td>" . date("Y年m月d日", strtotime(intval($Fct[$j]['MDAT']))) . "</td>";


////			date("Y年m月d日", strtotime($Fct[$j]['TIME']))			
			echo "<td class='text-center'>{$FZMN_Msg}{$FTOZ_Msg}</td>\n";
			echo "<td class='text-center'><a href='{$MyURL}' target='_blank'>{$CCSC_Msg}</a></td>\n";		
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
		$SEL['SHIN'] =  0;
//		$SEL['INKA'] =  0;
//		$SEL['REGI'] =  0;
	}elseif($FLG == 1){
		$SEL['STOR'] =  $_POST['Store'];
		$SEL['CHEC'] =  $_POST['Check'];
		$SEL['SHIN'] =  $_POST['Shinki'];
//		$SEL['INKA'] =  $_POST['Inkan'];
//		$SEL['REGI'] =  $_POST['Regist'];
	}elseif($FLG == 2){
//		echo "SEL_Buf = {$_POST['PCHK']}<br>";
		$TenSQL  = $_POST['BefTen'];
		$WheSQL  = str_replace("*", "'", $_POST['BefWhe']);
//		echo $_POST['PCHK'];
		$SEL['STOR'] = substr($_POST['PCHK'], 0, 4);
		$SEL['CHEC'] = substr($_POST['PCHK'], 4, 1);
		$SEL['SHIN'] = substr($_POST['PCHK'], 5, 1);
//		$SEL['INKA'] = substr($_POST['PCHK'], 6, 1);
//		$SEL['REGI'] = substr($_POST['PCHK'], 7, 1);
	}
//	$PDFFile = "{$SVRURL}list/001" . $_SESSION['USERSTR'] . ".PDF";

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
			if(WhereSQL($Key, $Data) <> ""){
				if($FirstFLG == True){
					$WheSQL = WhereSQL($Key, $Data);
					$FirstFLG = False;
				}else{
					$WheSQL .= " AND " . WhereSQL($Key, $Data);
				}
			}
		}
		
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
	$TblSQL  = "SELECT * FROM (SELECT ROW_NUMBER() OVER(ORDER BY 小規模店番) as RowNum, * FROM dbo.フラグ管理マスタ ";
	if($TenSQL == ""){
		$CntSQL .= "WHERE (" . $WheSQL . ")";
		$TblSQL  .= "WHERE (" . $WheSQL . ")";
	}else{
		$CntSQL  .= "WHERE (" . $TenSQL . ") AND (" . $WheSQL . ")";
		$TblSQL  .= "WHERE (" . $TenSQL . ") AND (" . $WheSQL . ")";

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
	echo "[CNTSQL]<br>";
	echo $CntSQL . "<br>";
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
		$SelSQL = "SELECT * FROM dbo.店舗マスタ WHERE 管轄店舗=" . $_SESSION['USERSTR'] . "ORDER BY 小規模店番";
		$i = 0;
		$arrStrVal[$i] = 990 + $_SESSION['USERSTR'];
		$arrStrLbl[$i] = $StrTen . "地区全て";
/*		
		$SelSQL = "SELECT * FROM dbo.店舗マスタ WHERE 管轄店舗=" . $_SESSION['USERSTR'];
		$i = 0;
		$arrStrVal[$i] = 990 + $_SESSION['USERSTR'];
		$arrStrLbl[$i] = $StrTen . "全て";
*/
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
	for($i = 0;$i < 3;$i++){
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
		}
	}
	$CheckTarget = $SEL['CHEC'];
	
	for($i = 0;$i < 4;$i++){
		$arrShinkiVal[$i] = $i;
		switch($i){
		case 0:
			$arrShinkiLbl[$i] = "すべて";
			break;
		case 1:
			$arrShinkiLbl[$i] = "顧客開設のみ";
			break;
		case 2:
			$arrShinkiLbl[$i] = "口座開設のみ";
			break;
		case 3:
			$arrShinkiLbl[$i] = "どちらも含む";
			break;
		}
	}
	$ShinkiTarget = $SEL['SHIN'];
?>
<?php //ポスト状態取得
//	echo "<br>==[Select Box]==================================<br>\n";
	$SEL_Buf  = sprintf("%04d", $SEL['STOR']);
	$SEL_Buf .= $SEL['CHEC'];
	$SEL_Buf .= $SEL['SHIN'];
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
	<title>リスト(新規口座)</title>
	<?php include('../includes/html/MyHeader.html'); ?>
</head>
<body>
	<?php include('../includes/js/MyTimeOut.js'); ?>
	<script type="text/javascript">
	   $(document).ready(function() 
	       { 
	           $("#List").tablesorter({
				   headers:{
					   3:{sorter:false},
	//				   4:{sorter:false},
					   5:{sorter:false},
					   6:{sorter:false}
				   }
			   });
	       } 
	   ); 
	</script>
	<h3>新規口座検証フォーム</h3>
	<?php include('../includes/html/MyNavigation.html'); ?>

	<div class='container-fulid'>
		<div class='row'>
			<div class='col-md-6'>
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
						<label for='name' class='control-label col-md-4 text-right'>開設状態</label>
						<div class='col-md-8'><select name='Shinki'><?php SetSelect($arrShinkiVal, $arrShinkiLbl, $ShinkiTarget); ?></select></div>
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
			</div><div class="col-md-6">
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
		</div><!--Row1 END-->
		<div class="row">
			<h1>代表者検証リスト（新規口座開設）</h1>
			<div style='font-size:12px;'>※▼ボタンの付いている項目はページ内でソートすることができます。</div>
			<?php
				SetList($TblSQL, $PDO, $PgNum);
			?>
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

	<div style='height:60px;'></div>
	<?php
		require_once '../includes/html/MyFooter.html';
		GetFileTime(getlastmod());
	?>
</body>
</html>