<?php //Connectiong Server
	require_once "../includes/php/reqfunctions.php";
	require_once "./includes/message_functions.php";
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
<?php 		//更新／新規の判定
	$GFLG = 1;
	if(isset($_GET['data1'])){
		$Key1 = intval($_GET['data1']);	
	}else{
		$Key1 = 0;
		$GFLG = 0;
	}
	if(isset($_GET['data2'])){
		$Key2 = intval($_GET['data2']);	
	}else{
		$Key2 = 0;
		$GFLG = 0;
	}
//	echo "****GFLG = {$GFLG}****";


	$Pop1	= " onclick=\"return confirm('メッセージを追加してもよろしいですか？')\"";
	$Pop2	= " onclick=\"return confirm('メッセージを更新してもよろしいですか？')\"";
	$Pop3	= " onclick=\"return confirm('メッセージを削除してもよろしいですか？')\"";
	if($GFLG == 0){
		$Title	= "メッセージ追加";
		$Btn1	= "";
		$Btn2 	= " disabled";
		$Btn3 	= " disabled";
//		$Btn2 	= "";
//		$Btn3 	= "";
		$Cls1	= "btn-lg btn-block  btn-info";
		$Cls2 	= "btn-lg btn-block  btn-default";
		$Cls3 	= "btn-lg btn-block  btn-default";
/*
		$Cls1	= " btn-info";
		$Cls2 	= " btn-success";
		$Cls3 	= " btn-danger";
*/
	}else{
		$Title = "メッセージ編集・削除";
		$Btn1 	= " disabled";
		$Btn2 	= "";
		$Btn3 	= "";
		$Cls1	= "btn-lg btn-block  btn-default";
		$Cls2 	= "btn-lg btn-block  btn-success";
		$Cls3 	= "btn-lg btn-block  btn-warning";

	}
?>
<?php 
	list($Limit, $To, $Message) = GetDefault($PDO, $GFLG, $Key1, $Key2);
?>

<?php 		//更新前処理
	if(!isset($_POST["PFLG"]) || $_POST["PFLG"] == "" ){
		$FLG = 0;
	}else{
//		echo $_POST['Sub'];
		$FLG = $_POST['PFLG'];
		switch ($_POST['Sub']) {
			case '追加':
				$FLG = 1;
				break;
			case '更新':
				$FLG = 2;
				break;			
			case '削除':
				$FLG = 3;
				break;
		}
	}
	if($FLG <> 0){
		$Usr 	= $_SESSION['USERID'];
		$Str 	= $_POST['To'];		
		$Dat 	= date("Y-m-d");
		$Eda 	= 1;

		if($FLG == 1){
			$CntSQL = "SELECT COUNT(*) from dbo.メッセージ";
			$stmt 	= $PDO->query($CntSQL);
			$Ren 	= $stmt->fetchColumn() + 1;

			$Lim 	= $_POST['Limit'];
			$Msg 	= $_POST['Msg'];
		}elseif($FLG == 2){
			$Ren 	= $Key1;
			$Eda 	= $Key2;
			$Lim 	= $_POST['Limit'];
			$Msg 	= $_POST['Msg'];
		}else{
			$Ren 	= $Key1;
			$Eda 	= $Key2;
			$Lim 	= date("Y-m-d", strtotime('-1 month'));
			$Msg 	= $_POST['Msg'];
		}
		//call function
		ExecuteSQL($PDO, $FLG, $Ren, $Eda, $Usr, $Str, $Dat, $Lim, $Msg);
		$Limit 		= $_POST['Limit'];
		$To 		= $_POST['To'];
		$Message 	= $_POST['Msg'];
//		list($Limit, $To, $Message) = GetDefault($PDO, $GFLG, $Key1, $Key2);

	}
?>

<!DOCTYPE html>
<html>
<head>
<title>詳細画面</title>
	<?php
		include('../includes/html/MyHeader.html');
		echo "<LINK href='./includes/message_css.css' rel='stylesheet' type='text/css'>";
	?>
</head>
<body>
	<?php include('../includes/js/MyTimeOut.js'); ?>
	<h3><?php echo $Title; ?></h3>
	<?php include('../includes/html/MyNavigation.html'); ?>

	<div class='container-fulid'>
		<div class='container-fulid'>
			<div class='row'>
				<div class='col-md-1'></div><!--dummy-->
				<div class='col-md-10'>
<form name="list" method="post" onsubmit="return CheckDate()">
					<h1>掲載期限</h1>
					<div style="margin-left:40px">
						<input type="date" name="Limit" value='<?php echo $Limit; ?>' required  style="width: 20%;">
					</div>
					<h1>公開先</h1>
					<div style="margin-left:40px">
						<select name="To" style="width: 20%;"><?php echo SetStoreList($To); ?></select>
					</div>
					<h1>内容</h1>
						<textarea name='Msg' cols='200' rows='10' style='width:100%;'><?php echo $Message; ?></textarea>
				</div>
			</div>

			<div class='row'><!--BtnGroup-->
				<div class='col-md-1'></div><!--dummy-->
				<div class='col-md-2'>
					<input type='submit' name='Sub' class='<?php echo $Cls1; ?>' value='追加'<?php echo $Btn1; echo $Pop1; ?>">
				</div>
				<div class='col-md-2'>
					<input type='submit' name='Sub' class='<?php echo $Cls2; ?>' value='更新'<?php echo $Btn2; echo $Pop2; ?>>
				</div>
				<div class='col-md-2'>
					<input type='submit' name='Sub' class='<?php echo $Cls3; ?>' value='削除'<?php echo $Btn3; echo $Pop3; ?>>
				</div>
				<div class='col-md-2'></div><!--dummy-->
				<div class='col-md-2'>
					<div class='text-right'>
						<input type='hidden' name='PFLG' value='1'><!--Button Push Flug-->
						<input type='button' value='画面を閉じる' onclick='window.close()' class='btn btn-danger btn-lg'>
					</div>
				</div>
			</div><!--BtnGroup End-->
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

<script type="text/javascript">
	function CheckDate(){
		datestr = document.list.Limit.value;
		datestr = datestr.replace(/-/g, "/");

		Msgstr	= document.list.Msg.value;

		var DFLG = 1;
		var MFLG = 1;
		var MyEcho = "データを更新しました。";

		// 正規表現による書式チェック 
		if(!datestr.match(/^\d{4}\/\d{2}\/\d{2}$/)){
			DFLG = 0;
		}
		var vYear 	= datestr.substr(0, 4) - 0;
	 	// Javascriptは、0-11で表現
		var vMonth 	= datestr.substr(5, 2) - 1;
		var vDay 	= datestr.substr(8, 2) - 0;
		// 月,日の妥当性チェック
		if(vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31){
			var vDt = new Date(vYear, vMonth, vDay);
			if(isNaN(vDt)){
				DFLG = 0;
			}else if(vDt.getFullYear() == vYear && vDt.getMonth() == vMonth && vDt.getDate() == vDay){
				DFLG = 1;
			}else{
				DFLG = 0;
			}
		}else{
			DFLG = 0;
		}

		if(Msgstr == ""){
			MFLG = 0;
		}

		var FLG = true;
		if(DFLG == 0){
			MyEcho = "正しい掲載期限を入力してください。\n";
			FLG = false;
		}
		if(MFLG == 0){
			MyEcho += "内容を入力してください。\n";
			FLG = false;
		}
		window.alert(MyEcho);
		return FLG;
	}
</script>