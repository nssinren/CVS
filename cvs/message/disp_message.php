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


<!DOCTYPE html>
<html>
<head>
<title>メッセージ画面</title>
	<?php
		include('../includes/html/MyHeader.html');
		echo "<LINK href='./includes/message_css.css' rel='stylesheet' type='text/css'>";
	?>
</head>
<body>
	<h3>メッセージ</h3>
	<?php include('../includes/js/MyTimeOut.js'); ?>
	<?php include('../includes/html/MyNavigation.html'); ?>
		
	<div class='container-fulid'>
		<div class='row'>
			<div class='col-md-11'>
				<div class='text-right'><a href="./disp_message.php" class="btn btn-lg btn-success">画面を更新します</a></div>
			</div>
		</div>
		<div class='row'>
			<div class='col-md-1'></div><!--dummy-->
			<div class='col-md-10'>
				<h1>内容</h1>
				<!--<?php echo $_SESSION['USERSTR']; ?>-->
				<?php echo SetMsgList($PDO, $_SESSION['USERSTR']); ?>
				<div class='text-right' style='margin-bottom: 5px;'><a href='./edit_message.php' target='_blank' class='btn btn-info'>新規メッセージを追加します</a></div>
			</div>
		</div>
	</div>

<div style='height:60px;'></div>
<?php
	require_once '../includes/html/MyFooter.html';
	GetFileTime(getlastmod());
?>
</body>
</html>