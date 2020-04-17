//복권구입
function buy_lottery() {
	if( confirm("복권 구매시 {$module_config->buy_money} {$module_config->money_lang} 차감됩니다") ){
		exec_xml('lotterylotto','procLotterylottoBuyLottery',{}, completeReceive, ['message', 'reload']); //모듈이름//액션이름//보내줄값//콜백함수//콜백함수에서 받을변수(미입력시 message 기본내장)
	}
}

//결과메세지 (공통)
function completeReceive(ret_obj) {
    var message = ret_obj['message'];
    var reload = ret_obj['reload'];
    alert(message);
    if(reload) location.reload();
}
