<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  争议差错
// +----------------------------------------------------------------------
class DisputeController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
	}
	
	/*
	* 调单申请
	**/
	public function apply() {
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
}