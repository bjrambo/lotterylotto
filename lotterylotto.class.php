<?php

class lotterylotto extends ModuleObject
{
	/**
	 * 등록할 트리거를 여기에 선언하면 자동으로 등록된다.
	 * checkUpdate(), moduleUpdate() 등에서 체크 및 생성 루틴을 중복으로 작성하지 않아도 된다.
	 */
	protected static $_insert_triggers = array(
		// array('document.insertDocument', 'after', 'controller', 'triggerAfterInsertDocument'),
		// array('document.updateDocument', 'after', 'controller', 'triggerAfterUpdateDocument'),
		// array('document.deleteDocument', 'after', 'controller', 'triggerAfterDeleteDocument'),
	);

	/**
	 * 이전 버전에서 등록했던 트리거를 삭제하려면 위와 동일한 문법으로 여기에 선언하면 된다.
	 * 사용하지 않는 트리거는 삭제해 주는 것이 성능에 도움이 된다.
	 */
	protected static $_delete_triggers = array(
		// array('comment.insertComment', 'after', 'controller', 'triggerAfterInsertComment'),
		// array('comment.updateComment', 'after', 'controller', 'triggerAfterUpdateComment'),
		// array('comment.deleteComment', 'after', 'controller', 'triggerAfterDeleteComment'),
	);

	// =========================== 이 부분 아래는 수정하지 않아도 된다 ============================

	/**
	 * 모듈 설정 캐시를 위한 변수.
	 */
	protected static $_config_cache = null;

	public function getConfig()
	{
		if (self::$_config_cache === null)
		{
			$oModuleModel = getModel('module');
			self::$_config_cache = $oModuleModel->getModuleConfig($this->module) ?: new stdClass;
		}
		return self::$_config_cache;
	}

	/**
	 * 모듈 설정을 저장하는 함수.
	 *
	 * 설정을 변경할 필요가 있을 때 ModuleController를 직접 호출하지 말고 이 함수를 사용한다.
	 * getConfig()으로 가져온 설정을 적절히 변경하여 setConfig()으로 다시 저장하는 것이 정석.
	 *
	 * @param object $config
	 * @return object
	 */
	public function setConfig($config)
	{
		$oModuleController = getController('module');
		$result = $oModuleController->insertModuleConfig($this->module, $config);
		if ($result->toBool())
		{
			self::$_config_cache = $config;
		}
		return $result;
	}
	
	function moduleInstall()
	{
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');
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
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');
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
