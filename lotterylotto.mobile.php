<?php

require_once(_XE_PATH_ . 'modules/lotterylotto/lotterylotto.view.php');

/**********************************
 **          모바일 뷰 클래스         **
 ***********************************/
class lotterylottoMobile extends lotterylottoView
{

	function init()
	{

		// 사용자 템플릿 파일의 경로 설정 (m.skins)
		$template_path = sprintf("%sm.skins/%s/", $this->module_path, $this->module_info->mskin);
		if (!is_dir($template_path) || !$this->module_info->mskin)
		{
			$this->module_info->mskin = 'default';
			$template_path = sprintf("%sm.skins/%s/", $this->module_path, $this->module_info->mskin);
		}
		$this->setTemplatePath($template_path);

		//모듈정보구함
		$args = new stdClass();
		$args->module = 'lotterylotto'; //쿼리에 모듈명 변수전달
		$oModuleModel = getModel('module');
		$oLotterylottoModel = getModel('lotterylotto');
		$this->module_info = $oLotterylottoModel->getModuleInfo($args);
		$this->module_config = $oModuleModel->getModuleConfig('lotterylotto');

		//모듈정보세팅
		Context::set('module_config', $this->module_config);
		Context::set('module_info', $this->module_info);

		//javascript, JS 필터 추가
		Context::loadJavascriptPlugin('ui.datepicker');
		Context::addJsFile("./common/js/jquery.js", true, '', -100000);
		Context::addJsFile("./common/js/js_app.js", true, '', -100000);
		Context::addJsFile("./common/js/x.js", true, '', -100000);
		Context::addJsFile("./common/js/common.js", true, '', -100000);
		Context::addJsFile("./common/js/xml_handler.js", true, '', -100000);
		Context::addJsFile("./common/js/xml_js_filter.js", true, '', -100000);
	}

} 
