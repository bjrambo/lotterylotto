<?php

/**********************************
 **          컨트롤러 클래스         **
 ***********************************/

class lotterylottoController extends lotterylotto
{

	//초기화
	function init()
	{
	}

	/**
	 * @brief 회원삭제시 로또복권 로그도 함께 삭제
	 * @param $obj
	 * @return object|void
	 */
	function triggerAfterDeleteMember(&$obj)
	{
		//기능사용할 경우 작동
		$oModuleModel = getModel('module');
		$module_config = $oModuleModel->getModuleConfig('lotterylotto');
		if ($module_config->del_user_log != 'yes')
		{
			return;
		}

		//회원번호 없을시 리턴
		if (!$obj->member_srl)
		{
			return;
		}
		$args = new stdClass();
		$args->member_srl = $obj->member_srl;
		//로그삭제
		$output = executeQuery('lotterylotto.delete_log_ByMemberSrl', $args);
		if (!$output->toBool())
		{
			return $output;
		}
	}

	/**
	 * @brief 복권구
	 * @return lotterylottoController|object
	 */
	function procLotterylottoBuyLottery()
	{
		//모듈정보구함
		$args = new stdClass();
		$args->module = 'lotterylotto'; //쿼리에 모듈명 변수전달
		$oModuleModel = getModel('module');
		$oLotterylottoModel = getModel('lotterylotto');
		$module_info = $oLotterylottoModel->getModuleInfo($args);
		$module_config = $oModuleModel->getModuleConfig('lotterylotto');

		//로또복권 모델클래스 호출
		$oLotterylottoModel = getModel('lotterylotto');

		//회원아닐시 리턴
		$logged_info = Context::get('logged_info');
		if (!Context::get('is_logged'))
		{
			return $this->setMessage('로그인후 이용가능합니다');
		}

		//오늘구매 횟수확인
		$args = new stdClass();
		$args->list_count = '500';
		$args->regdate_more = date('Ymd');
		$args->s_member_srl = $logged_info->member_srl;
		$today_count = $oLotterylottoModel->getBuyLotteryCnt($args);

		//일일구매횟수 초과시 리턴
		if ($module_config->buy_limit_use == 'yes' && $today_count > $module_config->buy_limit_count)
		{
			return $this->setMessage('일일 구매 횟수를 초과했습니다.');
		}

		//머니확인
		$user_money = $oLotterylottoModel->checkUserMoney($module_config->money_type, $logged_info);

		//머니부족시 리턴
		if ($module_config->buy_money > $user_money)
		{
			return $this->setMessage('구매 비용이 부족합니다');
		}

		//포인트히스토리 모듈에 복권구매 기록 (제거해도됨)
		/* Todo(Bjrambo): insert log by point history module 
		$PHC_member_srl = $logged_info->member_srl;
		$PHC_content = '"' . $module_info->browser_title . '" 에서 복권구매';
		eval('$__PHC' . $PHC_member_srl . '__[] = array($PHC_content,$PHC_point,$PHC_type);');
		eval('Context::set(\'__PHC\'.$PHC_member_srl.\'__\',$__PHC' . $PHC_member_srl . '__);');
		*/

		//복권구입으로 인한 머니차감
		$oLotterylottoModel->changeUserMoney($module_config->money_type, 'minus', $module_config->buy_money, $logged_info); //머니타입,변경타입,비용,회원정보

		//복권결과생성
		$result = $oLotterylottoModel->makeLotteryResult($module_config);

		//누적당첨금 확인
		$total_money = executeQuery('lotterylotto.get_totalMoney')->data->total_money;

		//포인트히스토리 모듈에 당첨금수령 기록 (제거해도됨)
		/* Todo(Bjrambo): insert log by point history module 
		$PHC_member_srl = $logged_info->member_srl;
		$PHC_content = '"' . $module_info->browser_title . '" 에서 ' . $result . '등 당첨금 수령';
		eval('$__PHC' . $PHC_member_srl . '__[] = array($PHC_content,$PHC_point,$PHC_type);');
		eval('Context::set(\'__PHC\'.$PHC_member_srl.\'__\',$__PHC' . $PHC_member_srl . '__);');
		*/

		//당첨금 처리
		$prize_result = $oLotterylottoModel->getPrizeMoney($result, $total_money, $logged_info, $module_config); //복권결과,누적당첨금,회원정보,모듈정보		

		$args = new stdClass();
		$args->member_srl = $logged_info->member_srl;    //회원번호
		$args->result = $result;                        //당첨결과
		$args->user_money = $user_money;                //보유머니
		$args->buy_money = $module_config->buy_money;    //복권가격
		$args->get_money = $prize_result->get_money;    //당첨금
		$args->total_money = $prize_result->total_money;//복권누적금
		$args->buy_tax = $module_config->buy_tax && $module_config->buy_tax_use == 'yes' ? $module_config->buy_money * ($module_config->buy_tax / 100) : 0; //복권구매 세금
		$args->get_tax = $prize_result->get_tax;        //당첨금세금
		$args->get_type = $prize_result->get_type;        //당첨금타입 (비율,고정)
		$args->regdate = date('YmdHis');                //등록일

		//로그등록
		$output = executeQuery('lotterylotto.insert_lottery_log', $args);
		if(!$output->toBool())
		{
			return $output;
		}
		
		//당첨메세지설정
		if ($result != 0)
		{
			$result_rank = $result . '등';
			$msg = str_replace(array('[rank]', '[point]', '[price]', '[money]', '[enter]'), array(
				$result_rank,
				$prize_result->get_money,
				$module_config->buy_money,
				$module_config->money_lang,
				'<br>'
			), $module_config->win_msg);
		}
		else
		{
			$result_rank = '꽝';
			$msg = str_replace(array('[rank]', '[point]', '[price]', '[money]', '[enter]'), array(
				$result_rank,
				$prize_result->get_money,
				$module_config->buy_money,
				$module_config->money_lang,
				'<br>'
			), $module_config->lose_msg);
		}

		//로그번호 가져옴 (당첨소감용)
		$args = new stdClass();
		$args->list_count = 1;
		$args->order_type = 'desc';
		$output = executeQuery('lotterylotto.get_lottery_log', $args);
		foreach ($output->data as $key => $val)
		{
			$log_srl = $val->log_srl;
			$member_srl = $val->member_srl;
		}

		//변수등록
		$this->add('msg', $msg);
		$this->add('rank', $result);
		$this->add('log_srl', $log_srl);                    //당첨소감
		$this->add('member_srl', $member_srl);                //당첨소감
		$this->add('get_money', $prize_result->get_money);    //당첨소감
	}

	/**
	 * @brief 당첨소감 작성
	 * @return BaseObject|Object|void
	 */
	function procLotterylottoUpdateMsg()
	{
		if(!Context::get('is_logged'))
		{
			return $this->createObject(-1, '로그인된 사용자만 이용 가능합니다.');
		}
		
		//모듈정보구함
		$args = new stdClass();
		$args->module = 'lotterylotto'; //쿼리에 모듈명 변수전달
		$oModuleModel = getModel('module');
		$module_config = $oModuleModel->getModuleConfig('lotterylotto');

		//입력값 받음 
		$obj = Context::getRequestVars();

		//본인이 아닐시 리턴
		$logged_info = Context::get('logged_info');
		if ($logged_info->member_srl != $obj->member_srl)
		{
			return;
		}

		//입력받은 메세지 없을시 기본메세지 세팅
		$obj->result = $obj->result != 0 ? $obj->result . '등' : 꽝;
		if ($obj->prize_msg && $obj->prize_msg != 'null')
		{
			$obj->prize_msg = strip_tags($obj->prize_msg);
		}
		else
		{
			$obj->prize_msg = str_replace(array(
				'[rank]',
				'[point]',
				'[price]',
				'[money]',
				'[enter]'
			), array(
				$obj->result,
				$obj->get_money,
				$module_config->buy_money,
				$module_config->money_lang,
				'<br>'
			), $module_config->prize_msg);
		}

		//당첨소감입력
		$output = executeQuery('lotterylotto.update_prizeMsg', $obj);
		if(!$output->toBool())
		{
			return $output;
		}
	}
}
