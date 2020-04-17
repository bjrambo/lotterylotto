<?php
    
	/**********************************
	**		관리자 뷰 클래스		 **
	***********************************/

    class lotterylottoAdminView extends lotterylotto {

        //초기화
        function init() {
			//모듈정보구함
			$args->module = 'lotterylotto'; //쿼리에 모듈명 변수전달
			$oModuleModel = &getModel('module');
			$oLotterylottoModel = &getModel('lotterylotto');
            $this->module_info = $oLotterylottoModel->getModuleInfo($args);
            $this->module_config = $oModuleModel->getModuleConfig('lotterylotto');		
			//모듈정보세팅
			Context::set('module_config', $this->module_config);
            Context::set('module_info', $this->module_info);
			// 관리자 템플릿 파일의 경로 설정 (tpl)
            $template_path = sprintf("%stpl/",$this->module_path);
            $this->setTemplatePath($template_path);
        }

		//스킨관리 
		function dispLotterylottoAdminSkinInfo() {
			$oModuleAdminModel = &getAdminModel('module');
			$skin_content = $oModuleAdminModel->getModuleSkinHTML($this->module_info->module_srl);
			Context::set('skin_content', $skin_content);
			// 템플릿 파일 지정			
			$this->setTemplateFile('skin_info');
        }	
		
		//권한관리
		function dispLotterylottoAdminGrantInfo() {
			$oModuleAdminModel = &getAdminModel('module');
			$grant_content = $oModuleAdminModel->getModuleGrantHTML($this->module_info->module_srl, $this->xml_info->grant);
			Context::set('grant_content', $grant_content);
			//템플릿 파일 지정
			$this->setTemplateFile('grant_list');
		}
		
        //관리자 모듈설정
        function dispLotterylottoAdminModuleInfo() {
			// 모듈 카테고리 목록 구함
			$oModuleModel = &getModel('module');
            $module_category = $oModuleModel->getModuleCategories();
            Context::set('module_category', $module_category);
			// 스킨 목록 구함
            $skin_list = $oModuleModel->getSkins($this->module_path);
			$mskin_list = $oModuleModel->getSkins($this->module_path, "m.skins");
            Context::set('skin_list',$skin_list);
			Context::set('mskin_list',$mskin_list);
			// 레이아웃 목록 구함
            $oLayoutModel = &getModel('layout');
            $layout_list = $oLayoutModel->getLayoutList();
			$mobile_layout_list = $oLayoutModel->getLayoutList(0,"M");
            Context::set('layout_list', $layout_list);
			Context::set('mlayout_list', $mobile_layout_list);
			//템플릿 파일 지정
            $this->setTemplateFile('index');
		}
		
		
		//로또복권 설정
		function dispLotterylottoAdminConfig() {
			//템플릿 파일 지정
            $this->setTemplateFile('config');
		}
		
		
		//로또복권 로그
		function dispLotterylottoAdminLog() {
			//검색기간설정
			$search_day = Context::get('search_day'); 
			if(!$search_day) $search_day = 'all';
			if($search_day == 'last'){
				$day_last = Context::get('day_last');  
                if(!$day_last) $day_last = 7;
				$args->regdate_more = date('Ymd',strtotime(sprintf('-%s day', $day_last)));
			}else if($search_day == 'moreless'){
				$day_more = Context::get('day_more');  
				$day_less = Context::get('day_less');  
				if(	$day_more != '') $args->regdate_more = date('Ymd',strtotime($day_more));
				if(	$day_less != '') $args->regdate_less = date('Ymd',strtotime($day_less)). '235959';
			}
			//검색대상설정
			$args->search_target = Context::get('search_target');
			$args->search_keyword = Context::get('search_keyword');
			$search_target = trim($args->search_target);
			$search_keyword = trim($args->search_keyword);
			//검색결과 변수에 넣음
			if($search_target && $search_keyword) {
				switch($search_target) {
					case 'log_srl' :
						$args->s_log_srl = $search_keyword;
						break; 
					case 'member_srl' :
						$args->s_member_srl = $search_keyword;
						break;
					case 'result' :
						if($search_keyword == '꽝'){
							$args->s_result = '0';
						}else{
							$args->s_result = str_replace('등','',$search_keyword);
						}
						break;	
					case 'get_type' :
						if($search_keyword == '비율'){
							$args->s_get_type = 'per';
						}elseif($search_keyword == '고정'){
							$args->s_get_type = 'fix';
						}else{
							$args->s_get_type = 'not';
						}
						break;
					case 'ipaddress' :
						$args->s_ipaddress = $search_keyword;
						break;
				}
			}
			
			//목록수
			$args->list_count = Context::get('list_count');
			
			//페이지
			$args->page = Context::get('page');
			$args->order_type = Context::get('order_type');
			if(!$args->order_type) $args->order_type = 'desc';
			
			//로그구함
            $oLotterylottoModel = &getModel('lotterylotto');
            $output = $oLotterylottoModel->getLotteryLog($args);
			
			//결과값 세팅
			Context::set('log_info',$output->data);
			
			//페이지 세팅
			Context::set('total_count', $output->total_count);
			Context::set('total_page', $output->total_page);
			Context::set('page_list', $output->data);
			Context::set('page', $output->page);
			Context::set('page_navigation', $output->page_navigation);
			
			//템플릿 파일 지정
            $this->setTemplateFile('log');
		}
		
		//로또복권 통계
		function dispLotterylottoAdminStats() {
			//검색기간설정
			$search_day = Context::get('search_day'); 
			if(!$search_day) $search_day = 'today';
			//오늘통계
			if($search_day == 'today'){
				$args->regdate_more = date('Ymd');
				$args->regdate_less = date('Ymd').'235959';
			//전체통계
			}else if($search_day == 'all'){
			//최근N일통계
			}else if($search_day == 'last'){
				$day_last = Context::get('day_last');  
                if(!$day_last) $day_last = 7;
				$args->regdate_more = date('Ymd',strtotime(sprintf('-%s day', $day_last)));
				$args->regdate_less = date('Ymd').'235959';
			//기간통계
			}else if($search_day == 'moreless'){
				$day_more = Context::get('day_more');  
				$day_less = Context::get('day_less');  
				if(	$day_more != '' && $day_less != ''){
					$args->regdate_more = date('Ymd',strtotime($day_more));
					$args->regdate_less = date('Ymd',strtotime($day_less)).'235959';
				}
			}
			
			//전체목록 가져오기위해 높은값 대입.
			$args->list_count = '9999999999';
						
			//로그구함
            $oLotterylottoModel = &getModel('lotterylotto');
            $output = $oLotterylottoModel->getLotteryLog($args);
			
			//통계구함
			foreach($output->data as $key => $val){
				//손익통계
				$stats->sum_tax = $stats->sum_tax + $val->get_tax + $val->buy_tax;
				$stats->sum_getmoney = $stats->sum_getmoney + $val->get_money;
				$stats->sum_total = $stats->sum_tax + $stats->sum_getmoney;
				//순위통계
				if($val->result == 1) $stats->first_cnt = $stats->first_cnt + 1;
				if($val->result == 2) $stats->second_cnt = $stats->second_cnt + 1;
				if($val->result == 3) $stats->third_cnt = $stats->third_cnt + 1;
				if($val->result == 4) $stats->fourth_cnt = $stats->fourth_cnt + 1;
				if($val->result == 5) $stats->fifth_cnt = $stats->fifth_cnt + 1;
				if($val->result == 0) $stats->lose_cnt = $stats->lose_cnt + 1;
				$stats->total_cnt = $stats->total_cnt+1;
			}
			//순위통계 값없을시 0 대입
			$stats->first_cnt = $stats->first_cnt ? $stats->first_cnt : 0;
			$stats->second_cnt = $stats->second_cnt ? $stats->second_cnt : 0;
			$stats->third_cnt = $stats->third_cnt ? $stats->third_cnt : 0;
			$stats->fourth_cnt = $stats->fourth_cnt ? $stats->fourth_cnt : 0;
			$stats->fifth_cnt = $stats->fifth_cnt ? $stats->fifth_cnt : 0;
			$stats->lose_cnt = $stats->lose_cnt ? $stats->lose_cnt : 0;
			
			//결과값세팅
			Context::set('stats_info', $stats);
			
			//회원순위통계
			$args->order_type = 'desc';
			$args->list_count = '10';
			$args->sort_index = 'buy_money';
			$rank_buy_money = $oLotterylottoModel->getLotteryRank($args); 	
			Context::set('rank_info_buy', $rank_buy_money->data); 
			$args->sort_index = 'get_money';
			$rank_get_money = $oLotterylottoModel->getLotteryRank($args); 
			Context::set('rank_info_get', $rank_get_money->data); 
			$args->sort_index = 'total_tax';
			$rank_total_tax = $oLotterylottoModel->getLotteryRank($args); 
			Context::set('rank_info_tax', $rank_total_tax->data); 			
			
			//템플릿 파일 지정
            $this->setTemplateFile('stats');
		}
	
		
	}
?>
