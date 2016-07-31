<?php
	function SetTable3($FncSQL, $FncPDO, $Exist){ //印鑑マスタ
		require_once "myfunctions.php";
		$i = 0;
		if($Exist == 1){
			foreach($FncPDO->query($FncSQL) as $INKN){
		//		$CEL[$i]['RENB'] = sprintf("%02d", $i + 1);
				$CEL[$i]['RENB'] = $i + 1;
				$CEL[$i]['STOR'] = $INKN[mb_convert_encoding("小規模店番","sjis","utf8")];
				$CEL[$i]['KAIY'] = $INKN[mb_convert_encoding("解約フラグ","sjis","utf8")];
				$AccNum			 = $INKN[mb_convert_encoding("口座番号","sjis","utf8")];
				$AccSub			 = $INKN[mb_convert_encoding("口座科目","sjis","utf8")];

//				$CEL[$i]['KAMO'] = $INKN[mb_convert_encoding("口座科目","sjis","utf8")];
//				$CEL[$i]['KOZA'] = $INKN[mb_convert_encoding("口座番号","sjis","utf8")];
//				$CEL[$i]['SEDA'] = $INKN[mb_convert_encoding("世代情報","sjis","utf8")];
				$CEL[$i]['TORI'] = $INKN[mb_convert_encoding("取引担当者フラグ","sjis","utf8")];
				$CEL[$i]['DATE'] = $INKN[mb_convert_encoding("コメント更新日時","sjis","utf8")];
		//		$CEL[$i]['SEDA'] = sprintf("%02d", $INKN[mb_convert_encoding("世代情報","sjis","utf8")]);
		//		コメント情報は別テーブルから取得...キーはコメントファイル名
				$CommentKey 	 = $INKN[mb_convert_encoding("口座コメントキー","sjis","utf8")];

		//		echo "*******{$CommentKey}*******";
		//		コメントファイル取得
				$tmpSQL = "SELECT * FROM dbo.印鑑コメントマスタ WHERE キー='{$CommentKey}'";
				if($tmpCEL = $FncPDO->query($tmpSQL)->fetch()){	
					$CEL[$i]['COMM'] = $tmpCEL[mb_convert_encoding("コメント","sjis","utf8")];
				}else{
					$CEL[$i]['COMM'] = "コメントなし";
				}

				//小規模店番
				$CEL[$i]['STOR'] = sprintf("%03d", $CEL[$i]['STOR']);
				//店番名
				$tmpSQL = "SELECT * FROM dbo.店舗マスタ WHERE 小規模店番={$CEL[$i]['STOR']}";
				if($tmpCEL = $FncPDO->query($tmpSQL)->fetch()){	
					$Tenpo[$i] = "［{$CEL[$i]['STOR']}］" . $tmpCEL[mb_convert_encoding("店舗名","sjis","utf8")];
				}else{
					$Tenpo[$i] = "不明";
				}


				//口座
				$Kouza[$i]		=	SetAccnum(0, $AccNum, $AccSub);
				//口座科目
/*				$tmpSQL = "SELECT * FROM dbo.科目マスタ WHERE 印鑑番号={$CEL[$i]['KAMO']}";
				if($tmpCEL = $FncPDO->query($tmpSQL)->fetch()){	
					$Kamoku[$i] = $tmpCEL[mb_convert_encoding("科目名","sjis","utf8")];
				}else{
					$Kamoku[$i] = "不明";
				}
				//口座番号
				$Kouza[$i] = SetAccnum($);
				if($CEL[$i]['KOZA'] == 0){
					$Kouza[$i] = "";
					$Kamoku[$i] = "共通印鑑";
				}else{
					$Kouza[$i] = sprintf("%010d", $CEL[$i]['KOZA']);
				}*/
				//コメント	
				if($CEL[$i]['COMM'] === ""){
					$CEL[$i]['COMM'] = "コメント情報なし";
				}else{
					$CEL[$i]['COMM'] = str_replace("\n", "<br>", $CEL[$i]['COMM']);			
				}
				//解約済み
				if($CEL[$i]['KAIY'] == 1){
					$CEL[$i]['KAIY'] = "<pre class='bg-danger'>解約済み</pre>";
				}else{
					$CEL[$i]['KAIY'] = "";
					
				}
				//取引担当者
				if($CEL[$i]['TORI'] == 1){
					$CEL[$i]['TORI'] = "<pre class='bg-info'>取引担当者あり</pre>";
				}else{
					$CEL[$i]['TORI'] = "";			
				}
				//コメント更新日時
				if(mb_strlen($CEL[$i]['DATE']) > 8){
					$CEL[$i]['DATE'] = date("Y年n月j日", strtotime($CEL[$i]['DATE']));
				}else{
					$CEL[$i]['DATE'] = "なし";
				}
				$i++;
			}
			$Max = $i;

			echo "<table class='table text-center'>\n";
			//タイトル
//			$bgColor = "bg-primary";
			$bgColor = "";
			echo "<tr class='bg-primary'>
					<th class='text-center'>連番</th>
					<th class='text-center'>小規模店番</th>
					<th class='text-left  '>口座番号</th>
					<th class='text-left  '>コメント</th>
					<th class='text-center'>コメント更新日時</th>
					<th class='text-center'>その他備考</th>
				</tr>\n";
			for($i = 0;$i < $Max;$i++){
				echo "<tr>";
				echo "<td>{$CEL[$i]['RENB']}</td>";
				echo "<td>{$Tenpo[$i]}</td>";
		//		echo "<td></td>";
				echo "<td class='text-left'>{$Kouza[$i]}</td>";
		//		echo "<td>{$CEL[$i]['SEDA']}</td>";
				echo "<td class='text-left'>{$CEL[$i]['COMM']}</td>";
				echo "<td>{$CEL[$i]['DATE']}</td>";
				echo "<td>{$CEL[$i]['KAIY']}{$CEL[$i]['TORI']}</td>";
				echo "</tr>";
			}
			echo "</table>";
		}else{
			echo "<pre class='bg-danger text-center'>印鑑情報が登録されていません。</pre>";
		}
	}
?>
