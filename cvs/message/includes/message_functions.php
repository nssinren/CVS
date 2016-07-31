<?php
	function SetMsgList($FncPDO, $UsrStr){
		

		$MySQL =	"SELECT * FROM dbo.メッセージ 
					 WHERE (小規模店番 = {$UsrStr} OR 小規模店番 = 0) 
					 AND 掲載期限 > '" . date("Y/m/d") . "' ORDER BY 登録日 DESC, 連番 DESC, 枝番 DESC";
		if($UsrStr == 9){
		$MySQL =	"SELECT * FROM dbo.メッセージ 
					 WHERE 掲載期限 > '" . date("Y/m/d") . "' ORDER BY 登録日 DESC, 連番 DESC, 枝番 DESC";
		}
//		echo $MySQL;

		//+================================================================
		//| コメント生成処理
		//+================================================================
		//|
		$i = 0;
		foreach($FncPDO->query($MySQL) as $CELL){
			$TD[$i]['DAT'] 	= $CELL[mb_convert_encoding("登録日",	"sjis","utf8")];
			$TD[$i]['MSG']	= $CELL[mb_convert_encoding("メッセージ",	"sjis","utf8")];
			$TD[$i]['ID1']	= $CELL[mb_convert_encoding("連番",	"sjis","utf8")];
			$TD[$i]['ID2']	= $CELL[mb_convert_encoding("枝番",	"sjis","utf8")];
			$tmpStr[$i]		= $CELL[mb_convert_encoding("小規模店番",	"sjis","utf8")];
			$tmpUsr[$i]		= $CELL[mb_convert_encoding("ユーザーID",	"sjis","utf8")];
			$i++;
		}

		$Max = $i;
		for($i=0; $i<$Max; $i++){
			//小規模店番
			$MySQL ="SELECT * FROM dbo.店舗マスタ WHERE 小規模店番=" . (990 + $tmpStr[$i]);
//			echo $MySQL;
			if($tmpCEL = $FncPDO->query($MySQL)->fetch()){	
				$TD[$i]['STR'] = $tmpCEL[mb_convert_encoding("店舗名","sjis","utf8")] . "向け";
			}else{
				$TD[$i]['STR'] = "不明";
			}
			//更新者
			$MySQL ="SELECT * FROM dbo.ユーザー管理 WHERE ユーザーID='{$tmpUsr[$i]}'";
			if($tmpCEL = $FncPDO->query($MySQL)->fetch()){	
				$TD[$i]['USR'] = $tmpCEL[mb_convert_encoding("ユーザー名","sjis","utf8")] . "さん";
			}else{
				$TD[$i]['USR'] = "不明";
			}
			//更新日
			$TD[$i]['DAT'] = date("n/j", strtotime($TD[$i]['DAT']));
			//リンク先
			$TD[$i]['BTN'] = "./edit_message.php?data1={$TD[$i]['ID1']}&data2={$TD[$i]['ID2']}";
			//メッセージ
			$TD[$i]['MSG'] = str_replace("\n", "<br>", $TD[$i]['MSG']);
		}
		//|
		//+================================================================

		//+================================================================
		//| テーブル生成処理
		//+================================================================
		//|
		$RetTbl  = "<table class='table text-center'>";
		$RetTbl .= "<tr class='bg-primary'>
					<th class='text-center' style='width:10%;'>番号</th>
					<th class='text-center' style='width:10%;'>発言日</th>
					<th class='text-center' style='width:14%;'>宛先</th>"
					//<th class='text-center' style='width:12%;'>発言者</th>
					."<th 									  >メッセージ</th>
					<th class='text-center' style='width:10%;'></th>
					</tr>";
		for($i=0; $i<$Max; $i++){
			$RetTbl .= "<tr>";

			$RetTbl .= "<td>". 
							$TD[$i]['ID1']
						."</td>";

			$RetTbl .= "<td><div class='date'>". 
							$TD[$i]['DAT']
						."</div></td>";

			$RetTbl .= "<td style='font-size:12px;'><div class='id" . (990 + $tmpStr[$i]) . "'>".
							$TD[$i]['STR'] . "<br>" . $TD[$i]['USR']
						."</div></td>";

			$RetTbl .= "<td class='text-left'>
						<div class='textbox'>
							<div class='textstart'></div>".
								$TD[$i]['MSG'] 
							."<div class='textend'></div>
						</div></td>";

			$RetTbl .= "<td>".
							"<a href=". $TD[$i]['BTN'] . " target='_blank' class='btn btn-lg btn-block btn-default' style='font-size: 12px;'>編集</a>" 
						."</td>";			$RetTbl .= "</tr>";
		}
		$RetTbl .= "</table>";
		//|
		//+================================================================
		
		return $RetTbl;
	}

?>

<?php 
	function ExecuteSQL($FncPDO, $FLG, $Renban, $Edaban, $UserID, $StoreNum, $CreateDate, $LimitDate, $Message){
		switch ($FLG) {
			case 1: 	//追加
				$MySQL 	=	"INSERT dbo.メッセージ VALUES(
							  {$Renban},
							  {$Edaban},
							  {$StoreNum},
							 '{$Message}',
							 '{$UserID}',
							 '{$CreateDate}',
							 '{$LimitDate}')";
				break;
			case 2: 	//更新
				$MySQL 	=	"UPDATE dbo.メッセージ SET
							 小規模店番	={$StoreNum},
							 メッセージ		='{$Message}',
							 ユーザーID	='{$UserID}',
							 掲載期限		='{$LimitDate}'
							WHERE 連番={$Renban} AND 枝番={$Edaban}";
				break;
			case 3: 	//削除
				$MySQL 	= 	"UPDATE dbo.メッセージ SET 掲載期限='{$LimitDate}'
							 WHERE 連番={$Renban} AND 枝番={$Edaban}";
				echo 	"<script language='javascript' type='text/javascript'>
							self.close();
						 </script>";
				break;
		}
//		echo $MySQL;
		$res = $FncPDO->query($MySQL);
	}
?>
<?php 		//宛先リスト作成
	function SetStoreList($To){
		for($i=0; $i<=9; $i++){
			$Select[$i] = "";
			if($To == $i){
				$Select[$i] = " selected";
			}
		}
		$Tolist  = "<option value='0'{$Select[0]}>全体</option>";
		$Tolist .= "<option value='1'{$Select[1]}>県南</option>";
		$Tolist .= "<option value='2'{$Select[2]}>五島</option>";
		$Tolist .= "<option value='4'{$Select[4]}>県北</option>";
		$Tolist .= "<option value='5'{$Select[5]}>壱岐</option>";
		$Tolist .= "<option value='6'{$Select[6]}>対馬</option>";
		$Tolist .= "<option value='9'{$Select[9]}>管理者</option>";
		return $Tolist;
	}
?>
<?php 		//初期値設定
	function GetDefault($PDO, $GFLG, $Key1, $Key2){
		$Limit	= date("Y-m-d", strtotime('+1 month'));
		$To		= 0;
		$Message= "";
		if($GFLG == 1){
			$MySQL = "SELECT * FROM dbo.メッセージ WHERE 連番={$Key1} AND 枝番={$Key2}";
			if($tmpCEL = $PDO->query($MySQL)->fetch()){	
				$Limit	= $tmpCEL[mb_convert_encoding("掲載期限",		"sjis","utf8")];
				$To		= $tmpCEL[mb_convert_encoding("小規模店番",	"sjis","utf8")];
				$Message= $tmpCEL[mb_convert_encoding("メッセージ",		"sjis","utf8")];
			}
		}
		$Ret[0] 	= $Limit;
		$Ret[1] 	= $To;
		$Ret[2] 	= $Message;
		return $Ret;
	}
?>
