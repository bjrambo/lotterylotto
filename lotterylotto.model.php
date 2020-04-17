<?php
    
	/**********************************
	**			모델 클래스			 **
	***********************************/
	
    class lotterylottoModel extends lotterylotto {

        //초기화
        function init() {
		
		}

		//모듈정보구함
		function getModuleInfo($args){
			$output = executeQuery('lotterylotto.get_moduleInfo',$args);
            if(!$output->data->module_srl) return;
			$oModuleModel = &getModel('module');
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($output->data->module_srl);
			return $module_info;
        }
		
		//복권구매횟수
		function getBuyLotteryCnt($args){
			$output = executeQuery('lotterylotto.get_lottery_log',$args);
			$cnt = count($output->data) + 1;
			return $cnt;
		}
		
		//복권로그
		function getLotteryLog($args){
			$output = executeQueryArray('lotterylotto.get_lottery_log',$args);
			return $output;
		}
		
		//순위통계
		function getLotteryRank($args){
			$output = executeQueryArray('lotterylotto.get_lottery_rank',$args);
			foreach($output->data as $key => $val){
				$val->rank = $key + 1;
			}
			return $output;
		}
		
		//머니확인
		function checkUserMoney($money_type,$logged_info){
			if(!$money_type || !$logged_info) return;
			if($money_type == 'point'){ //포인트
				$oPointModel = &getModel('point'); 
				$point = $oPointModel->getPoint($logged_info->member_srl);
				return $point;
			}else{ //캐시
				$oCashModel = &getModel('cash');
				$cash = $oCashModel->getCash($logged_info->member_srl);
				return $cash;
			}
		}
		
		//머니변경(add,minus,update)
		function changeUserMoney($money_type,$change_type,$money,$logged_info){
			if(!$money_type || !$change_type || !$money || !$logged_info) return;
			if($money_type == 'point'){ //포인트
				$oPointController = &getController('point');
				$oPointController->setPoint($logged_info->member_srl,$money, $change_type);
				return;
			}else{ //캐시
				$oCashController = &getController('cash');
				$oCashController->setCash($logged_info->member_srl, $money, $change_type);
				return;
			}
		}
		
		//당첨금수령
		function getPrizeMoney($result,$total_money,$logged_info,$module_config){
			//누적당첨금 없을시 비상누적금사용
			if(!$total_money) $total_money = $module_config->emergency_money;
			//설정한 포인트보다 작을시 비상누적금사용 (비상누적금에 기존적립된 누적금을 합침)
			if($module_config->emergency_money_min_point > $total_money) $total_money = $module_config->emergency_money + $total_money;
			//1등당첨금 수령
			if($result == '1'){
				if($module_config->money_type1 == 'fix'){ //고정 당첨금
					$this->changeUserMoney($module_config->money_type,'add',$module_config->get_fix_money1,$logged_info);
					$output->get_money = $module_config->get_fix_money1;
					$output->get_tax = $module_config->fix_money_minus == 'yes' ?  $module_config->get_fix_money1 : 0;
					$output->get_type = 'fix';
					//누적당첨금 계산식 (복잡함)
					if($module_config->fix_money_minus == 'yes'){ 
						if($total_money > $module_config->get_fix_money1){ 
							$output->total_money = $module_config->buy_tax && $module_config->buy_tax_use == 'yes' ? $module_config->buy_money * (1 - ($module_config->buy_tax/100)) + $total_money - $module_config->get_fix_money1 : $total_money + $module_config->buy_money - $module_config->get_fix_money1; 
						}else{
							$output->total_money = $module_config->emergency_money;
						}
					}else{
						$output->total_money = $module_config->buy_tax && $module_config->buy_tax_use == 'yes' ? $module_config->buy_money * (1 - ($module_config->buy_tax/100)) + $total_money : $total_money + $module_config->buy_money;   
					}
					return $output;
				}else{	//비율 당첨금
					$prize_money = intval($total_money * ($module_config->get_per_money1/100));
					$this->changeUserMoney($module_config->money_type,'add',$prize_money,$logged_info);
					$output->get_money = $prize_money;
					$output->get_tax = intval($total_money * ($module_config->get_tax1/100));
					$output->get_type = 'per';
					$output->total_money = intval($total_money * ($module_config->last_money1/100));
					return $output;
				}
			}elseif($result == '2'){
				if($module_config->money_type2 == 'fix'){ //고정 당첨금
					$this->changeUserMoney($module_config->money_type,'add',$module_config->get_fix_money2,$logged_info);
					$output->get_money = $module_config->get_fix_money2;
					$output->get_tax = $module_config->fix_money_minus == 'yes' ?  $module_config->get_fix_money2 : 0;
					$output->get_type = 'fix';
					//누적당첨금 계산식 (복잡함)
					if($module_config->fix_money_minus == 'yes'){ 
						if($total_money > $module_config->get_fix_money2){ 
							$output->total_money = $module_config->buy_tax && $module_config->buy_tax_use == 'yes' ? $module_config->buy_money * (1 - ($module_config->buy_tax/100)) + $total_money - $module_config->get_fix_money2 : $total_money + $module_config->buy_money - $module_config->get_fix_money2; 
						}else{
							$output->total_money = $module_config->emergency_money;
						}
					}else{
						$output->total_money = $module_config->buy_tax && $module_config->buy_tax_use == 'yes' ? $module_config->buy_money * (1 - ($module_config->buy_tax/100)) + $total_money : $total_money + $module_config->buy_money;   
					}
					return $output;
				}else{	//비율 당첨금
					$prize_money = intval($total_money * ($module_config->get_per_money2/100));
					$this->changeUserMoney($module_config->money_type,'add',$prize_money,$logged_info);
					$output->get_money = $prize_money;
					$output->get_tax = intval($total_money * ($module_config->get_tax2/100));
					$output->get_type = 'per';
					$output->total_money = intval($total_money * ($module_config->last_money2/100));
					return $output;
				}
			}elseif($result == '3'){
				if($module_config->money_type3 == 'fix'){ //고정 당첨금
					$this->changeUserMoney($module_config->money_type,'add',$module_config->get_fix_money3,$logged_info);
					$output->get_money = $module_config->get_fix_money3;
					$output->get_tax = $module_config->fix_money_minus == 'yes' ?  $module_config->get_fix_money3 : 0;
					$output->get_type = 'fix';
					//누적당첨금 계산식 (복잡함)
					if($module_config->fix_money_minus == 'yes'){ 
						if($total_money > $module_config->get_fix_money3){ 
							$output->total_money = $module_config->buy_tax && $module_config->buy_tax_use == 'yes' ? $module_config->buy_money * (1 - ($module_config->buy_tax/100)) + $total_money - $module_config->get_fix_money3 : $total_money + $module_config->buy_money - $module_config->get_fix_money3; 
						}else{
							$output->total_money = $module_config->emergency_money;
						}
					}else{
						$output->total_money = $module_config->buy_tax && $module_config->buy_tax_use == 'yes' ? $module_config->buy_money * (1 - ($module_config->buy_tax/100)) + $total_money : $total_money + $module_config->buy_money;   
					}
					return $output;
				}else{	//비율 당첨금
					$prize_money = intval($total_money * ($module_config->get_per_money3/100));
					$this->changeUserMoney($module_config->money_type,'add',$prize_money,$logged_info);
					$output->get_money = $prize_money;
					$output->get_tax = intval($total_money * ($module_config->get_tax3/100));
					$output->get_type = 'per';
					$output->total_money = intval($total_money * ($module_config->last_money3/100));
					return $output;
				}
			}elseif($result == '4'){	
				if($module_config->money_type4 == 'fix'){ //고정 당첨금
					$this->changeUserMoney($module_config->money_type,'add',$module_config->get_fix_money4,$logged_info);
					$output->get_money = $module_config->get_fix_money4;
					$output->get_tax = $module_config->fix_money_minus == 'yes' ?  $module_config->get_fix_money4 : 0;
					$output->get_type = 'fix';
					//누적당첨금 계산식 (복잡함)
					if($module_config->fix_money_minus == 'yes'){ 
						if($total_money > $module_config->get_fix_money4){ 
							$output->total_money = $module_config->buy_tax && $module_config->buy_tax_use == 'yes' ? $module_config->buy_money * (1 - ($module_config->buy_tax/100)) + $total_money - $module_config->get_fix_money4 : $total_money + $module_config->buy_money - $module_config->get_fix_money4; 
						}else{
							$output->total_money = $module_config->emergency_money;
						}
					}else{
						$output->total_money = $module_config->buy_tax && $module_config->buy_tax_use == 'yes' ? $module_config->buy_money * (1 - ($module_config->buy_tax/100)) + $total_money : $total_money + $module_config->buy_money;   
					}
					return $output;
				}else{	//비율 당첨금
					$prize_money = intval($total_money * ($module_config->get_per_money4/100));
					$this->changeUserMoney($module_config->money_type,'add',$prize_money,$logged_info);
					$output->get_money = $prize_money;
					$output->get_tax = intval($total_money * ($module_config->get_tax4/100));
					$output->get_type = 'per';
					$output->total_money = intval($total_money * ($module_config->last_money4/100));
					return $output;
				}
			}elseif($result == '5'){
				if($module_config->money_type5 == 'fix'){ //고정 당첨금
					$this->changeUserMoney($module_config->money_type,'add',$module_config->get_fix_money5,$logged_info);
					$output->get_money = $module_config->get_fix_money5;
					$output->get_tax = $module_config->fix_money_minus == 'yes' ?  $module_config->get_fix_money5 : 0;
					$output->get_type = 'fix';
					//누적당첨금 계산식 (복잡함)
					if($module_config->fix_money_minus == 'yes'){ 
						if($total_money > $module_config->get_fix_money5){ 
							$output->total_money = $module_config->buy_tax && $module_config->buy_tax_use == 'yes' ? $module_config->buy_money * (1 - ($module_config->buy_tax/100)) + $total_money - $module_config->get_fix_money5 : $total_money + $module_config->buy_money - $module_config->get_fix_money5; 
						}else{
							$output->total_money = $module_config->emergency_money;
						}
					}else{
						$output->total_money = $module_config->buy_tax && $module_config->buy_tax_use == 'yes' ? $module_config->buy_money * (1 - ($module_config->buy_tax/100)) + $total_money : $total_money + $module_config->buy_money;   
					}
					return $output;
				}else{	//비율 당첨금
					$prize_money = intval($total_money * ($module_config->get_per_money5/100));
					$this->changeUserMoney($module_config->money_type,'add',$prize_money,$logged_info);
					$output->get_money = $prize_money;
					$output->get_tax = intval($total_money * ($module_config->get_tax5/100));
					$output->get_type = 'per';
					$output->total_money = intval($total_money * ($module_config->last_money5/100));
					return $output;
				}
			}elseif($result == '0'){
				$output->get_money = '0';
				$output->get_tax = '0';
				$output->get_type = 'not';
				$output->total_money = $module_config->buy_tax && $module_config->buy_tax_use == 'yes' ? $module_config->buy_money * (1 - ($module_config->buy_tax/100)) + $total_money : $total_money + $module_config->buy_money;
				return $output;
			}
		}
		
		//복권추첨
		function makeLotteryResult($module_config){
			$first_per = $module_config->first_percent * 100;	//1위 당첨확률
			$second_per = $module_config->second_percent * 100; //2위 당첨확률
			$third_per = $module_config->third_percent * 100;	//3위 당첨확률
			$fourth_per = $module_config->fourth_percent * 100; //4위 당첨확률
			$fifth_per = $module_config->fifth_percent * 100;	//5위 당첨확률
			$lose_per = 10000 - $first_per - $second_per - $third_per - $fourth_per - $fifth_per; //꽝 확률
			
			//꽝확률 오류 발생시 리턴
			if($lose_per < 0 && $lose_per > 10000) return $this->setMessage('꽝 확률 오류발생');

			//복권 주머니 생성
			$pocket1 = array_fill(0, $first_per, "1");
			$pocket2 = array_fill($first_per, $second_per, "2");
			$pocket3 = array_fill($first_per+$second_per, $third_per, "3");
			$pocket4 = array_fill($first_per+$second_per+$third_per, $fourth_per, "4");
			$pocket5 = array_fill($first_per+$second_per+$third_per+$fourth_per, $fifth_per, "5");
			$pocket6 = array_fill($first_per+$second_per+$third_per+$fourth_per+$fifth_per, $lose_per, "0");
			$pocket = array_merge($pocket1,$pocket2,$pocket3,$pocket4,$pocket5,$pocket6);
			
			//주머니 랜덤 섞기
			$x = mt_rand(1,3);
			for($i=0; $i<$x; $i++){
				shuffle($pocket);
			}
			   
			//결과 랜덤뽑기
			$y = mt_rand(0,9999);
			if($pocket[$y] == '1'){
				$result = "1";
			}elseif($pocket[$y] == '2'){
				$result = "2";
			}elseif($pocket[$y] == '3'){
				$result = "3";
			}elseif($pocket[$y] == '4'){
				$result = "4";
			}elseif($pocket[$y] == '5'){
				$result = "5";
			}else{
				$result = "0";
			}
			
			//결과값 반환
			return $result;
		}
	}
?>