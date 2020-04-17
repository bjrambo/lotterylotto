<?php

class lotterylotto extends ModuleObject
{

	/*****************************************
	 * @brief 설치시 추가 작업이 필요할시 구현
	 ******************************************/
	function moduleInstall()
	{
		$oModuleModel = &getModel('module');
		$oModuleController = &getController('module');
		$module_info = $oModuleModel->getModuleInfoByMid('lotterylotto');
		if (!$module_info->module_srl)
		{
			$args = new stdClass();
			$args->module = 'lotterylotto';
			$args->mid = 'lotterylotto';
			$args->browser_title = '로또복권모듈';
			$oModuleController->insertModule($args);
		}

		$this->createObject();
	}

	/************************************************
	 * @brief 설치가 이상이 없는지 체크하는 method
	 ************************************************/
	function checkUpdate()
	{

		//트리거 확인
		$oModuleModel = getModel('module');
		if (!$oModuleModel->getTrigger('member.deleteMember', 'lotterylotto', 'controller', 'triggerAfterDeleteMember', 'after'))
		{
			return true;
		}

		return false;
	}

	/****************************************************
	 * @brief 업데이트 실행
	 ****************************************************/
	function moduleUpdate()
	{
		$oModuleModel = &getModel('module');
		$oModuleController = &getController('module');
		//트리거 설치 (회원탈퇴 혹은 삭제시 해당회원의 로또복권 로그삭제)
		if (!$oModuleModel->getTrigger('member.deleteMember', 'lotterylotto', 'controller', 'triggerAfterDeleteMember', 'after'))
		{
			$oModuleController->insertTrigger('member.deleteMember', 'lotterylotto', 'controller', 'triggerAfterDeleteMember', 'after');
		}

		$this->createObject(0, 'success_updated');
	}

	/*****************************************************
	 * @brief 캐시 파일 재생성
	 *****************************************************/
	function recompileCache()
	{

	}

	public function createObject($status = 0, $message = 'success' /* $arg1, $arg2 ... */)
	{
		$args = func_get_args();
		if (count($args) > 2)
		{
			global $lang;
			$message = vsprintf($lang->$message, array_slice($args, 2));
		}
		return class_exists('BaseObject') ? new BaseObject($status, $message) : new Object($status, $message);
	}
}

?>
