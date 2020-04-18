<?php

/**********************************
 **            뷰 클래스             **
 ***********************************/

class lotterylottoView extends lotterylotto
{

	//초기화
	function init()
	{
		// 사용자 템플릿 파일의 경로 설정 (skins)
		$template_path = sprintf("%sskins/%s/", $this->module_path, $this->module_info->skin);
		if (!is_dir($template_path) || !$this->module_info->skin)
		{
			$this->module_info->skin = 'default';
			$template_path = sprintf("%sskins/%s/", $this->module_path, $this->module_info->skin);
		}
		$this->setTemplatePath($template_path);

		$oLotterylottoModel = getModel('lotterylotto');
		//TODO(BJRambo) : 모듈 가져오는 방식이 마음에 안듬. 새로운 방법을 찾아보자. 디비 쿼리가 계속 생기지 않도록하자
		$args = new stdClass();
		$args->module = 'lotterylotto'; //쿼리에 모듈명 변수전달
		$this->module_info = $oLotterylottoModel->getModuleInfo($args);
		
		$this->module_config = $this->getConfig();
		
		Context::set('module_config', $this->module_config);
		Context::set('module_info', $this->module_info);
	}


	//복권화면
	function dispLotterylottoIndex()
	{
		//변수세팅
		$oLotterylottoModel = getModel('lotterylotto');
		$logged_info = Context::get('logged_info');
		//보유머니
		$this->module_config->user_money = $oLotterylottoModel->checkUserMoney($this->module_config->money_type, $logged_info);
		//누적당첨금 없을시 비상누적금사용
		$total_money = executeQuery('lotterylotto.get_totalMoney')->data->total_money;
		if (!$total_money)
		{
			$this->module_config->total_money = $this->module_config->emergency_money;
		}
		else
		{
			$this->module_config->total_money = $total_money;
		}
		//설정한 포인트보다 작을시 비상누적금사용 (비상누적금에 기존적립된 누적금을 합침)
		if ($total_money && $this->module_config->emergency_money_min_point > $total_money)
		{
			$this->module_config->total_money = $this->module_config->emergency_money + $total_money;
		}
		//$this->module_config->total_money = executeQuery('lotterylotto.get_totalMoney')->data->total_money ? executeQuery('lotterylotto.get_totalMoney')->data->total_money : $this->module_config->emergency_money;

		//오늘구매횟수
		$args = new stdClass();
		$args->list_count = '500';
		$args->regdate_more = date('Ymd');
		$args->s_member_srl = $logged_info->member_srl;
		$this->module_config->today_count = $oLotterylottoModel->getBuyLotteryCnt($args);
		//예상당첨금(1~5위)
		$this->module_config->get_money1 = $this->module_config->money_type1 == 'fix' ? $this->module_config->get_fix_money1 : intval($this->module_config->get_per_money1 / 100 * $this->module_config->total_money);
		$this->module_config->get_money2 = $this->module_config->money_type2 == 'fix' ? $this->module_config->get_fix_money2 : intval($this->module_config->get_per_money2 / 100 * $this->module_config->total_money);
		$this->module_config->get_money3 = $this->module_config->money_type3 == 'fix' ? $this->module_config->get_fix_money3 : intval($this->module_config->get_per_money3 / 100 * $this->module_config->total_money);
		$this->module_config->get_money4 = $this->module_config->money_type4 == 'fix' ? $this->module_config->get_fix_money4 : intval($this->module_config->get_per_money4 / 100 * $this->module_config->total_money);
		$this->module_config->get_money5 = $this->module_config->money_type5 == 'fix' ? $this->module_config->get_fix_money5 : intval($this->module_config->get_per_money5 / 100 * $this->module_config->total_money);
		//오늘누적 당첨금
		$args = new stdClass();
		$args->regdate_more = date('Ymd');
		$today = executeQuery('lotterylotto.get_totalMoney', $args);
		$args = new stdClass();
		$args->regdate_less = date('Ymd');
		$yesterday = executeQuery('lotterylotto.get_totalMoney', $args);
		$this->module_config->today_total_money = $today->data->total_money && $yesterday->data->total_money ? $today->data->total_money - $yesterday->data->total_money : $today->data->total_money;
		//당첨소감 적용대상 (기존변수유지하고 새로운변수로 추가해서 넘김)
		$this->module_config->prize_msg_target2 = in_array("x", $this->module_config->prize_msg_target) ? 'x' : implode(',', $this->module_config->prize_msg_target);

		//모듈정보세팅
		Context::set('module_config', $this->module_config);

		//복권로그 구함
		$args = new stdClass();
		$args->page = Context::get('page');
		$args->order_type = 'desc';
		$args->page_count = '5';
		$args->list_count = $this->module_config->user_list_count;
		$args->regdate_more = date('Ymd', strtotime(sprintf('-%s day', $this->module_config->user_list_date - 1)));
		$output = $oLotterylottoModel->getLotteryLog($args);
		Context::set('log_info', $output->data);

		//페이지 세팅
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page_list', $output->data);
		Context::set('page', $output->page);
		Context::set('page_navigation', $output->page_navigation);

		//템플릿 파일 설정
		$this->setTemplateFile('index');
	}

	//나의구매내역
	function dispLotterylottoMylottery()
	{
		//변수세팅
		$oLotterylottoModel = getModel('lotterylotto');
		$logged_info = Context::get('logged_info');
		//구매금액 && 당첨금액 && 구매횟수
		$args = new stdClass();
		$args->s_member_srl = $logged_info->member_srl;
		$args->list_count = '99999';
		$user_lottery = $oLotterylottoModel->getLotteryLog($args);
		$user_money = new stdClass();
		foreach ($user_lottery->data as $key => $val)
		{
			$user_money->get_money = $val->get_money + $user_money->get_money;
			$user_money->buy_money = $val->buy_money + $user_money->buy_money;
			$user_money->buy_cnt = $user_money->buy_cnt + 1;
		}
		$this->module_config->user_money = $user_money;

		//모듈정보세팅
		Context::set('module_config', $this->module_config);

		//복권로그 구함
		$args = new stdClass();
		$args->s_member_srl = $logged_info->member_srl;
		$args->page = Context::get('page');
		$args->order_type = 'desc';
		$args->list_count = '13';
		$args->page_count = '5';
		$output = $oLotterylottoModel->getLotteryLog($args);
		Context::set('log_info', $output->data);

		//페이지 세팅
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page_list', $output->data);
		Context::set('page', $output->page);
		Context::set('page_navigation', $output->page_navigation);

		//템플릿 파일 설정
		$this->setTemplateFile('my_lottery');
	}
}
