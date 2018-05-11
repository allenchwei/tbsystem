<?php
namespace Home\Controller;
use Think\Controller;
// +----------------------------------------------------------------------
// | @gzy  公共类
// +----------------------------------------------------------------------
class HomeController extends Controller {
	    
	//空操作
	public function _empty(){
    	$this->display(C('ERROR_PAGE'));
    	exit;
    }
	
	//权限及登录验证
    public function _initialize() {
		$home  = session('HOME');
		$time  = session('_session_time');
		$times = time();
		if(!$home['USER_ID'] || !$time || ($times-$time > C('_SESSION_TIME'))){
			if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
				//ajax请求
				$this->ajaxReturn(array('statusCode'=>'301', 'message'=>'由于您长时间未做任何处理，请重新登录！'));
			}else{
				//正常请求
				redirect(__APP__."/Home/Public/logout.html");
			};
		}
		session('_session_time', $times);
		
		//检查权限
		//判断是否开启认证，并且当前模块是否需要验证
		if(C('USER_AUTH_ON') && !in_array(CONTROLLER_NAME, explode(',', C('NOT_AUTH_MODULE')))) {
			//导入RBAC类，开始验证
			import('Vendor.Common.RBAC');
			//通过accessDecision获取权限信息
			if(!\RBAC::AccessDecision()) {
				//检查认证识别号
				if(!$_SESSION[C('USER_AUTH_KEY')]) {
					//跳转到认证网关
					//redirect( C_URL.C('USER_AUTH_GATEWAY') );
					$this->ajaxReturn(array('statusCode'=>'301', 'message'=>'认证失败，请重新登录！'));
				}
				//没有权限 抛出错误
				if(C('RBAC_ERROR_PAGE')) {
					// 定义权限错误页面
					//redirect( C('RBAC_ERROR_PAGE') );
					$this->ajaxReturn(array('statusCode'=>'301', 'message'=>C('RBAC_ERROR_PAGE').'，请重新登录！'));
				}else{
					if(C('GUEST_AUTH_ON')) {
						$this->assign('jumpUrl', C_URL.C('USER_AUTH_GATEWAY'));
					}
					// 提示错误信息
					//$this->wrong(L('_VALID_ACCESS_').'，请退出重新登录！');
					$this->ajaxReturn(array('statusCode'=>'301', 'message'=>L('_VALID_ACCESS_').'，请重新登录！'));
				}
			}
		}
    }
	
    //ajax callback
	protected function ajaxResponse($status='', $msg='', $data='') {
    	$res = array(
    		'state'		=>	$status,
    		'msg'		=>	$msg,
			'result'	=>	!empty($data) ? $data : array()
    	);
    	$this->ajaxReturn($res);
    }
	
	/**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $callbackType 回调类型	closeCurrent为关闭当前页	forward为跳转到某页
     * @param string $forwardUrl 页面跳转地址
     * @param string $navTabId tab的ID
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function wrong($message, $callbackType, $forwardUrl='', $navTabId='', $ajax=true, $info) {
        $this->selfDispatchJump($message, 300, $forwardUrl, $ajax, $callbackType, $navTabId, $info);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $forwardUrl 页面跳转地址
     * @param string $navTabId tab的ID
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function right($message, $callbackType, $forwardUrl='', $navTabId='', $ajax=true, $info) {
        $this->selfDispatchJump($message, 200, $forwardUrl, $ajax, $callbackType, $navTabId, $info);
    }	
    
	/**
	 *	后台全局跳转方法
     * @access protected
     * @param string $message 错误信息
     * @param string $statusCode 信息编码 200为操作成功 300为操作错误  
     * @param string $callbackType 回调类型	closeCurrent为关闭当前页	forward为跳转到某页
     * @param string $forwardUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
	 */
	protected function selfDispatchJump($message, $statusCode=200, $forwardUrl='', $ajax=false, $callbackType, $navTabId, $info) {
		if($ajax === true || IS_AJAX){
			$data           		=	is_array($ajax) ? $ajax : array();
            $data['message']  		=   $message;
            $data['statusCode'] 	=   $statusCode;
            $data['callbackType']   =   $callbackType;
            $data['forwardUrl']    	=   $forwardUrl;
            $data['navTabId']    	=   $navTabId;
            $data['info']    		=   $info;
			header('Content-Type:application/json; charset=utf-8');
			exit(json_encode($data));
		}
    }
	
	//根据 cookie 登录	* 改动此处，也要改动 Public checklogin 方法
	public function cookieLogin() {
		$flag	= false;
		$jfb_mo = base64_decode(cookie('jfb_mo'));	//手机号
		$jfb_ch = base64_decode(cookie('jfb_ch'));	//秘钥
		if($jfb_mo && $jfb_ch) {
			$userdata = D('MUser')->findUser("USER_MOBILE = '".$jfb_mo."' and USER_STATUS=0");
			if(!empty($userdata)) {
				$kwd = md5(md5($userdata['USER_ID']."@#jf"));
				if($jfb_ch == $kwd){
					$response['userinfo'] = $userdata;
					
					//RBAC
					import('Vendor.Common.RBAC');
					session(C('USER_AUTH_KEY'), $response['userinfo']['USER_ID']);
					if(C('SPECIAL_USER') == $response['userinfo']['USER_ID']){
						session(C('ADMIN_AUTH_KEY'), true);
					}else{
						session(C('ADMIN_AUTH_KEY'), false);
					}
					
					//session
					$sedata = array(
						'BRANCH_MAP_ID'		=>	$response['userinfo']['BRANCH_MAP_ID'],
						'BRANCH_NAME'		=>	$response['userinfo']['BRANCH_NAME'],
						'PARTNER_MAP_ID'	=>	$response['userinfo']['PARTNER_MAP_ID'],
						'USER_ID'			=>	$response['userinfo']['USER_ID'],
						'USER_NAME'			=>	$response['userinfo']['USER_NAME'],
						'USER_MOBILE'		=>	$response['userinfo']['USER_MOBILE'],
						'USER_LEVEL'		=>	$response['userinfo']['USER_LEVEL'],
						'ROLE_ID'			=>	$response['userinfo']['ROLE_ID'],
						'ROLE_NAME'			=>	$response['userinfo']['ROLE_NAME'],
					);
					session('HOME', $sedata);
					
					//cookie
					cookie('jfb_mo', base64_encode($response['userinfo']['USER_MOBILE']), 30*24*3600);
					if($post['rememb'] == 1){
						cookie('jfb_ch', base64_encode(md5(md5($response['userinfo']['USER_ID']."@#jf"))), 7*24*3600);
					}
					
					//RBAC缓存访问权限
					\RBAC::saveAccessList();
					
					$flag = true;
				}
			}
		}		
		return $flag;
	}
}