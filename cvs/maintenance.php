<!DOCTYPE html>
<html>
<head>
<title>メンテナンス中</title>
	<?php include('./includes/html/MyHeader.html'); ?>
	<link href="http://192.1.10.136/sys/cvs/includes/css/Design.css" rel="stylesheet">
</head>
<body>
	<?php include('./includes/js/MyTimeOut.js'); ?>

	<h3>顧客検証システム</h3>
	<div class='container-fulid'>		
		<div class='row'>
			<div class='col-md-1'></div>
			<div class='col-md-10'>
				<h1>メンテナンス中</h1>
				<pre class='bg-primary' style='padding: 30px;'>ただいまメンテナンス中です。<br>終了までしばらくお待ち下さい。</pre>
			</div>
		</div><!-- Container(Menu) END -->
		<div class='row'>
			<div class='col-md-11'>
				<div class='text-right'>
					<input type='button' value='ログイン画面へ' onclick="location.href='login.php'" class='btn btn-danger btn-lg'>
				</div>
			</div>
		</div>
	</div><!-- Container(Body) END -->
	<div style='height:60px;'></div>
	<?php
		require_once './includes/html/MyFooter.html';
		GetFileTime(getlastmod());
	?>
</body>
</html>