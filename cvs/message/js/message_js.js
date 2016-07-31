function CheckDate(datestr){
	document.write(datestr);
	// 正規表現による書式チェック 
	var Msg = "入力された日付が誤っています。修正してください。";
	confirm("test");
	if(!datestr.match(/^\d{4}\/\d{2}\/\d{2}$/)){
		confirm(Msg);
		return false;
	}
	var vYear = datestr.substr(0, 4) - 0;
 	// Javascriptは、0-11で表現
	var vMonth = datestr.substr(5, 2) - 1;
	var vDay = datestr.substr(8, 2) - 0;
	// 月,日の妥当性チェック
	if(vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31){
		var vDt = new Date(vYear, vMonth, vDay);
		if(isNaN(vDt)){
			confirm(Msg);
			return false;
		}else if(vDt.getFullYear() == vYear
		 && vDt.getMonth() == vMonth
		 && vDt.getDate() == vDay){
			return true;
		}else{
			confirm(Msg);
			return false;
		}
	}else{
		alert(Msg);
		return false;
	}
}