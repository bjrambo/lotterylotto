<?php

/*************************************
 **        관리자 컨트롤러 클래스        **
 **************************************/

class lotterylottoAdminController extends lotterylotto
{

	//초기화
	function init()
	{
	}

	//관리자 모듈설정저장
	function procLotterylottoAdminModuleInfo()
	{
		//입력값을 모두 받음
		$args = Context::getRequestVars();
		$args->module = 'lotterylotto';
		//모듈등록 유무에 따라 insert/update
		$oModuleController = &getController('module');
		if (!$args->module_srl)
		{
			$output = $oModuleController->insertModule($args); //모듈insert
			$this->setMessage('success_registed');
		}
		else
		{
			$output = $oModuleController->updateModule($args); //모듈update
			$this->setMessage('success_updated');
		}
		if (!$output->toBool())
		{
			return $output;
		}
		//모듈시작 화면으로 돌아감
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispLotterylottoAdminModuleInfo'));
	}

	//로또복권 설정
	function procLotterylottoAdminConfig()
	{

		// 변수정리
		$args = Context::getRequestVars();
		$module_config->module = 'lotterylotto';    //모듈명
		$module_config->money_type = $args->money_type;         //화폐단위
		$module_config->respond_skin = $args->respond_skin;     //반응형스킨
		$module_config->rank_list_use = $args->rank_list_use;//당첨금순위
		$module_config->del_user_log = $args->del_user_log;     //회원탈퇴시 로그삭제
		$module_config->money_lang = $module_config->money_type == 'point' ? '포인트' : '캐시'; //화폐단위 언어(자동설정)
		$module_config->money_lang_en = $module_config->money_type == 'point' ? 'P' : 'C';        //화폐단위 언어(자동설정)
		$p_imgfile = 'point.gif';
		$c_imgfile = 'cash.png';    //화폐이미지 설정
		$module_config->money_type == 'point' ? $module_config->money_img = '<img src="' . $this->module_path . 'img/' . $p_imgfile . '" class="lottery_money_img" />' : $module_config->money_img = '<img src="' . $this->module_path . 'img/' . $c_imgfile . '" class="lottery_money_img" />';
		$module_config->buy_limit_use = $args->buy_limit_use;                                                                    //일일구매제한기능
		$module_config->buy_limit_count = $args->buy_limit_count ? intval($args->buy_limit_count) : $args->buy_limit_count = 1;    //일일구매제한횟수
		$module_config->buy_money = $args->buy_money ? intval($args->buy_money) : $args->buy_money = 100;                        //복권가격
		$module_config->buy_tax_use = $args->buy_tax_use;                                                                        //구매수수료기능
		$module_config->buy_tax = $args->buy_tax ? intval($args->buy_tax) : $args->buy_tax = 20;                                //구매수수료 (%)
		///////////////////////////////		당첨확률		///////////////////////////////////////// 
		$module_config->first_percent = $args->first_percent ? round($args->first_percent, 2) : $args->first_percent = 0.5;        //1등 당첨확률
		$module_config->second_percent = $args->second_percent ? round($args->second_percent, 2) : $args->second_percent = 5;    //2등 당첨확률
		$module_config->third_percent = $args->third_percent ? round($args->third_percent, 2) : $args->third_percent = 10;        //3등 당첨확률
		$module_config->fourth_percent = $args->fourth_percent ? round($args->fourth_percent, 2) : $args->fourth_percent = 15;    //4등 당첨확률
		$module_config->fifth_percent = $args->fifth_percent ? round($args->fifth_percent, 2) : $args->fifth_percent = 40;        //5등 당첨확률
		$module_config->lose_percent = round(100 - $args->first_percent - $args->second_percent - $args->third_percent - $args->fourth_percent - $args->fifth_percent, 2);        //꽝 확률
		///////////////////////////////		당첨금		//////////////////////////////////////////////
		$module_config->emergency_money = $args->emergency_money ? $args->emergency_money : $args->emergency_money = 1000;    //비상누적금
		$module_config->emergency_money_min_point = $args->emergency_money_min_point ? $args->emergency_money_min_point : $args->emergency_money_min_point = 500;    //비상누적금 이용시점
		$module_config->fix_money_minus = $args->fix_money_minus;            //고정당첨금 차감기능
		$module_config->money_type1 = $args->money_type1;                    //1등 당첨금형태 (비율,고정)
		$module_config->get_fix_money1 = $args->get_fix_money1 ? intval($args->get_fix_money1) : $args->get_fix_money1 = 100;    //당첨금(고정)
		$module_config->get_per_money1 = $args->get_per_money1 ? round($args->get_per_money1, 2) : $args->get_per_money1 = 50;    //당첨금(비율)
		$module_config->get_tax1 = $args->get_tax1 ? round($args->get_tax1, 2) : $args->get_tax1 = 20;                            //수수료(비율)
		$module_config->last_money1 = round(100 - $args->get_per_money1 - $args->get_tax1, 2);                                        //누적금(비율)
		$module_config->money_type2 = $args->money_type2;                    //2등 당첨금형태 (비율,고정)
		$module_config->get_fix_money2 = $args->get_fix_money2 ? intval($args->get_fix_money2) : $args->get_fix_money2 = 100;    //당첨금(고정)
		$module_config->get_per_money2 = $args->get_per_money2 ? round($args->get_per_money2, 2) : $args->get_per_money2 = 50;    //당첨금(비율)
		$module_config->get_tax2 = $args->get_tax2 ? round($args->get_tax2, 2) : $args->get_tax2 = 20;                            //수수료(비율)
		$module_config->last_money2 = round(100 - $args->get_per_money2 - $args->get_tax2, 2);                                        //누적금(비율)
		$module_config->money_type3 = $args->money_type3;                    //3등 당첨금형태 (비율,고정)
		$module_config->get_fix_money3 = $args->get_fix_money3 ? intval($args->get_fix_money3) : $args->get_fix_money3 = 100;    //당첨금(고정)
		$module_config->get_per_money3 = $args->get_per_money3 ? round($args->get_per_money3, 2) : $args->get_per_money3 = 50;    //당첨금(비율)
		$module_config->get_tax3 = $args->get_tax3 ? round($args->get_tax3, 2) : $args->get_tax3 = 20;                            //수수료(비율)
		$module_config->last_money3 = round(100 - $args->get_per_money3 - $args->get_tax3, 2);                                        //누적금(비율)
		$module_config->money_type4 = $args->money_type4;                    //4등 당첨금형태 (비율,고정)
		$module_config->get_fix_money4 = $args->get_fix_money4 ? intval($args->get_fix_money4) : $args->get_fix_money4 = 100;    //당첨금(고정)
		$module_config->get_per_money4 = $args->get_per_money4 ? round($args->get_per_money4, 2) : $args->get_per_money4 = 50;    //당첨금(비율)
		$module_config->get_tax4 = $args->get_tax4 ? round($args->get_tax4, 2) : $args->get_tax4 = 20;                            //수수료(비율)
		$module_config->last_money4 = round(100 - $args->get_per_money4 - $args->get_tax4, 2);                                        //누적금(비율)
		$module_config->money_type5 = $args->money_type5;                    //5등 당첨금형태 (비율,고정)
		$module_config->get_fix_money5 = $args->get_fix_money5 ? intval($args->get_fix_money5) : $args->get_fix_money5 = 100;    //당첨금(고정)
		$module_config->get_per_money5 = $args->get_per_money5 ? round($args->get_per_money5, 2) : $args->get_per_money5 = 50;    //당첨금(비율)
		$module_config->get_tax5 = $args->get_tax5 ? round($args->get_tax5, 2) : $args->get_tax5 = 20;                            //수수료(비율)
		$module_config->last_money5 = round(100 - $args->get_per_money5 - $args->get_tax5, 2);                                        //누적금(비율)
		///////////////////////////////		당첨메세지		//////////////////////////////////////////////
		$module_config->win_msg = $args->win_msg ? $args->win_msg : '축하합니다! [rank]에 당첨되셔서[enter][point][money]를 획득하였습니다.';            //당첨메세지
		$module_config->lose_msg = $args->lose_msg ? $args->lose_msg : '이런.. [rank]이네요. 다음기회를 노려보세요.';        //꽝메세지
		///////////////////////////////		당첨소감설정		//////////////////////////////////////////////
		$module_config->prize_msg_target = $args->prize_msg_target ? $args->prize_msg_target : array(
			1,
			2,
			3,
			4,
			5
		);        //당첨후기대상
		$module_config->prize_msg = $args->prize_msg ? $args->prize_msg : '[rank]이예요!!';            //당첨소감 기본메세지
		///////////////////////////////		복권화면		//////////////////////////////////////////////
		$module_config->user_list_use = $args->user_list_use;                                            //복권 구매목록 표시
		$module_config->user_list_date = intval($args->user_list_date) ? $args->user_list_date : 7;        //조회기간(n일)
		$module_config->user_list_count = intval($args->user_list_count) ? $args->user_list_count : 7;    //목록수


		// 설정저장
		$oModuleController = &getController('module');
		$oModuleController->insertModuleConfig('lotterylotto', $module_config);

		// 성공메세지
		$this->setMessage('success_updated');

		//설정 화면으로 돌아감
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispLotterylottoAdminConfig'));
	}



	///////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////  로 그 삭 제 ////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////

	//선택로그 삭제
	function procLotterylottoAdminLogDelete()
	{
		$log_srls = Context::get('log_srls');
		if (!$log_srls)
		{
			$this->createObject(-1, '선택 대상이 없습니다');
		}

		//로그분리
		$log_srl_list = explode("@", $log_srls);
		foreach ($log_srl_list as $key => $val)
		{
			// 루프돌면서 선택된 로그 삭제
			$args = null;
			$args->log_srl = $val;
			$this->DeleteLog($args);
		}
		$this->setMessage('success_deleted');
	}

	//전체로그 삭제
	function procLotterylottoAdminLogDeleteAll()
	{
		$this->DeleteLogAll();
		//auto_increment 초기화
		$oDB = &DB::getInstance();
		$query = sprintf("alter table %slotterylotto_log auto_increment=1", $oDB->prefix);
		$query = $oDB->_query($query);
		$oDB->_fetch($query);
		$this->setMessage('success_deleted');
	}

	////////////// 로그삭제를 위한 메서드, module.xml에 등록하지 않음 ★시작★ ////////////////////

	function DeleteLog($args)
	{ //log_srl
		$output = executeQuery('lotterylotto.delete_log', $args);
		if (!$output->toBool())
		{
			return $output;
		}
	}

	function DeleteLogAll()
	{
		$output = executeQuery('lotterylotto.delete_log');
		if (!$output->toBool())
		{
			return $output;
		}
	}

	////////////// 로그삭제를 위한 메서드, module.xml에 등록하지 않음 ★끝★  ////////////////////


}

?>
