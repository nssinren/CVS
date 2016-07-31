<?php 
function SetCIFTable($FncSQL, $FncPDO, $Exist, $FNEW){ //dbo.前月・当月CIF
//+===============================================
//|	$FncSQL 	...	SQL文
//|	$FncPDO 	...	メイン側PDO（関数側で定義でもOK？）
//|	$Exist 		...	存在の有無
//|	$FNEW 		...	新規フラグ
//+===============================================

	$i = 0;
	$stmt = $FncPDO->query($FncSQL);
	$Cstm = $stmt->fetch();

	if($Exist == 1){	//顧客が存在した場合
		//+===============================================
		//|	変数セット処理
		//+===============================================
		//|
		$CEL['NUMB'] = sprintf("%08d",	$Cstm[mb_convert_encoding("顧客番号","sjis","utf8")]);
		$CEL['NAME'] = 				  	$Cstm[mb_convert_encoding("漢字氏名","sjis","utf8")];
		$CEL['KANA'] = 					$Cstm[mb_convert_encoding("氏名","sjis","utf8")];
		$CEL['DAIH'] = 					$Cstm[mb_convert_encoding("漢字代表者名","sjis","utf8")];
		$CEL['YAGO'] = 					$Cstm[mb_convert_encoding("漢字屋号","sjis","utf8")];
		$CEL['SYOK'] = 					$Cstm[mb_convert_encoding("小規模店番","sjis","utf8")];
		$CEL['JINK'] = 					$Cstm[mb_convert_encoding("人格コード","sjis","utf8")];
		$CEL['CKOZ'] = 					$Cstm[mb_convert_encoding("総口座数","sjis","utf8")];
		$tmpDate     = 					$Cstm[mb_convert_encoding("顧客開設日","sjis","utf8")];
		$tmpBirth    = 					$Cstm[mb_convert_encoding("生年月日","sjis","utf8")];

		//顧客開設日
		if($tmpDate <> 0){
			$CEL['MDAT'] = date("Y年n月j日",strtotime(intval($tmpDate)));
		}else{
			$CEL['MDAT'] = "登録されていません";			
		}
		//生年月日
		if($tmpBirth <> 0){
			$CEL['BIRT'] = date("Y年n月j日",strtotime(intval($tmpBirth)));
			$CEL['AGE '] = birthToAge(intval($tmpBirth));
		}else{
			$CEL['BIRT'] = "登録されていません";			
			$CEL['AGE '] = "不詳";
		}		
		//小規模店番
		$CEL['SYOK'] = sprintf("%03d", $CEL['SYOK']);
		$tmpSQL = "SELECT * FROM dbo.店舗マスタ WHERE 小規模店番={$CEL['SYOK']}";
		if($tmpCEL = $FncPDO->query($tmpSQL)->fetch()){	
			$CEL['SYNA'] = $tmpCEL[mb_convert_encoding("店舗名","sjis","utf8")];
		}else{
			$CEL['SYNA'] = "不明";
		}
		//人格コード
		$CEL['JINK'] = sprintf("%03d", $CEL['JINK']);
		$tmpSQL = "SELECT * FROM dbo.人格マスタ WHERE 人格コード={$CEL['JINK']}";
		if($tmpCEL = $FncPDO->query($tmpSQL)->fetch()){	
			$CEL['JINA'] = $tmpCEL[mb_convert_encoding("名称","sjis","utf8")];
		}else{
			$CEL['JINA'] = "不明";
		}

		$tmpCLS    = " class='bg-primary text-center' style='font-weight:bold;'";
		$tmpCenter = " style='text-align:center;'";
		
		if($FNEW == 1){
			$CEL['MDAT'] = "<span class='text-danger'>{$CEL['MDAT']}</span>";
		}else{
			$CEL['MDAT'] = "<span>{$CEL['MDAT']}</span>";			
		}
		//|
		//+===============================================

		//+===============================================
		//|	テーブル生成処理
		//+===============================================
		//|
		echo "<table class='table text-right'>\n";
		echo "<tr><td{$tmpCLS}}>顧客番号</td>				<td>{$CEL['NUMB']}</td></tr>";
		echo "<tr><td{$tmpCLS}}>小規模店番</td>			<td>［{$CEL['SYOK']}］{$CEL['SYNA']}</td></tr>";
		echo "<tr><td{$tmpCLS}}>人格</td>				<td>［{$CEL['JINK']}］{$CEL['JINA']}</td></tr>";
		echo "<tr><td{$tmpCLS}}>漢字氏名<br>カナ氏名</td>	<td>{$CEL['NAME']}<br>{$CEL['KANA']}</td></tr>";
		echo "<tr><td{$tmpCLS}}>代表者名</td>				<td>{$CEL['DAIH']}</td></tr>";
		echo "<tr><td{$tmpCLS}}>漢字屋号</td>				<td>{$CEL['YAGO']}</td></tr>";
		echo "<tr><td{$tmpCLS}}>保有口座数</td>			<td>{$CEL['CKOZ']}口座</td></tr>";
		echo "<tr><td{$tmpCLS}}>顧客開設日</td>			<td>{$CEL['MDAT']}</td></tr>";
		echo "<tr><td{$tmpCLS}}>生年月日</td>				<td>{$CEL['BIRT']}（{$CEL['AGE ']}歳）</td></tr>";
		echo "</tr></table>\n";
		//|
		//+===============================================
	}else{		//顧客が存在しなかった場合
		echo "<pre class='bg-danger'>オンラインシステム側で登録されていません</pre>";
	}
}
?>