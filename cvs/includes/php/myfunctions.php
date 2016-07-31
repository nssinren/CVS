<?php
	function SetAccnum($AccType, $AccNum, $AccSub){

		$tmpAccNum = strval($AccNum);
/*
		echo "SetAccnum-AccNum  : " . $tmpAccNum . "<br>";
		echo "SetAccnum-AccType : " . $AccType . "<br>";
		echo "SetAccnum-AccSubj : " . $AccSub . "<br>";
*/
		//口座科目が0(=共通)なら処理を抜ける
		if($AccSub == 0){
			return "［共通］";
		}
		$FLG = false;
		if($AccType == 0){
			$FLG = True;
			$MySQL = "SELECT * from dbo.科目マスタ WHERE 印鑑番号={$AccSub}";
		}else{
			$MySQL = "SELECT * from dbo.科目マスタ WHERE 科目番号={$AccSub}";
		}

/*		switch($AccType){
			case 0;		//印鑑側情報
				$MySQL = "SELECT * from dbo.科目マスタ WHERE 印鑑番号={$AccSub}";
				break;
			case 1;		//当座情報
				$retAccNum = mb_substr($tmpAccNum, 0, 7);
				break;
			case 2;		//定期情報
				$retAccNum = mb_substr($tmpAccNum, 0, 7) . "-" . mb_substr($tmpAccNum, 8, 10);
				break;
			default;
				return "貯金区分エラーです";
				break;
		}*/

		//+=================================================
		//|　科目名取得処理
		//+=================================================
		//|
		$SRV = '192.1.10.111';
		$DB  = 'CVSDB';
		$USR = 'sa';
		$PSW = 'nssinren';
		$MyDNS = "sqlsrv:server=$SRV;database=$DB";
		//echo $MyDNS;
		try{
			$FncPDO = new PDO($MyDNS,$USR,$PSW);
			$e="OK</br>";
		}catch(PDOException $e){
			$e->Getmessage();
		}

		if($tmpCEL = $FncPDO->query($MySQL)->fetch()){	
			$retAccSub	= $tmpCEL[mb_convert_encoding("科目名",	"sjis","utf8")];
			$AccLength	= $tmpCEL[mb_convert_encoding("口座桁数",	"sjis","utf8")];
		}else{
			$retAccSub = "不明";
		}

		switch($AccLength){
			case 7;		//当座情報
				if($FLG == True){
					$tmpAccNum = strval(sprintf("%07.0f", $tmpAccNum));
				}				
				$retAccNum = mb_substr($tmpAccNum, 0, 7);
				break;
			case 10;		//定期情報
				if($FLG == True){
					$tmpAccNum = strval(sprintf("%010.0f", $tmpAccNum));
				}
				$retAccNum = mb_substr($tmpAccNum, 0, 7) . "-" . sprintf("%03.0f", mb_substr($tmpAccNum, 8, 10));
				break;
			default;
				return "エラー";
				break;
		}

		//|
		//+=================================================
		$fncPDO = null;

		return "［{$retAccSub}］{$retAccNum}";
	}

	function birthToAge($ymd){
	 
		$base  = new DateTime();
		$today = $base->format('Ymd');
	 
		$birth    = new DateTime($ymd);
		$birthday = $birth->format('Ymd');
	 
		$age = (int) (($today - $birthday) / 10000);
	 
		return $age;
	}

	function GoMaintenance(){
		$URL = "http://192.1.10.136/sys/cvs/maintenance.php";
//		$URL = $_SERVER['DOCUMENT_ROOT'] . "/cvs/";
//		echo $URL;
		$SRV = '192.1.10.111';
		$DB  = 'CVSDB';
		$USR = 'sa';
		$PSW = 'nssinren';
		$MyDNS = "sqlsrv:server=$SRV;database=$DB";
		//echo $MyDNS;
		try{
			$MntPDO = new PDO($MyDNS,$USR,$PSW);
			$e="OK</br>";
		}catch(PDOException $e){
			$e->Getmessage();
		}

		$MySQL 	= "SELECT メンテナンスフラグ FROM dbo.処理管理マスタ";	 
		$stmt = $MntPDO->query($MySQL);
		$Cstm = $stmt->fetch();
//		$Cstm = mb_convert_encoding($Cstm,"sjis","utf8");
//		var_dump($Cstm);
//		echo "FLG=" . $Cstm[0];
		if($Cstm[0] == True){
			header("Location: {$URL}");
			exit();
		}

	}

?>
