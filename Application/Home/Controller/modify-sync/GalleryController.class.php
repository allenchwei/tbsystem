<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @ljf  银行通道管理
// +----------------------------------------------------------------------
class GalleryController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->MHost 	= 'MHost';
		$this->MHauth 	= 'MHauth';
		$this->MHmdr 	= 'MHmdr';
		$this->MHcls 	= 'MHcls';
		$this->MCheck 	= 'MCheck';
		$this->MHshop 	= "MHshop";
		$this->MCity 	= "MCity";
		$this->MHpos 	= "MHpos";
		$this->MKey 	= "MKey";
	}
	
	/*
	* 通道管理 列表
	**/
	public function host() {
		$post = I('post');
		if($post['submit'] == "host"){
			$where = "1=1";
			//通道名称
			if($post['HOST_NAME']) {
				$where .= " and HOST_NAME like '%".$post['HOST_NAME']."%'";
			}
			//状态
			if($post['HOST_STATUS'] != '') {
				$where .= " and HOST_STATUS = '".$post['HOST_STATUS']."'";
			}
			//分页
			$count = D($this->MHost)->countHost($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MHost)->getHostlist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_status_check',	C('CHECK_POINT.check'));	//通道状态
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display();
	}
	/*
	* 通道管理 添加
	**/
	public function host_add() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "host_add") {
			//验证
			if(empty($post['HOST_NAME']) || empty($post['END_TIME']) || empty($post['CITY_NO']) || empty($post['ADDRESS']) || empty($post['ZIP']) || empty($post['MANAGER']) || empty($post['MOBILE']) || empty($post['SETTLE_OFF_AMT']) || empty($post['SETTLE_FREE_AMT']) || empty($post['SETTLE_TOP_AMT'])){
				$this->wrong("缺少必填项数据！");
			}
			//Host 通道
			$hostdata = array(
				'BRANCH_MAP_ID'		=>	$home['BRANCH_MAP_ID'],
				'HOST_NAME'			=>	$post['HOST_NAME'],
				'HOST_NAMEAB'		=>	$post['HOST_NAMEAB'],
				'HOST_STATUS'		=>	6,
				'HOST_ACQ_CODE'		=>	$post['HOST_ACQ_CODE'] ? $post['HOST_ACQ_CODE'] : '00000000000',
				'CITY_NO'			=>	$post['CITY_NO'],
				'ADDRESS'			=>	$post['ADDRESS'],
				'ZIP'				=>	$post['ZIP'],
				'TEL'				=>	$post['TEL'],
				'MANAGER'			=>	$post['MANAGER'],
				'MOBILE'			=>	$post['MOBILE'],
				'EMAIL'				=>	$post['EMAIL'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'CREATE_TIME'		=>	date('YmdHis'),
				'END_TIME'			=>	date('Ymd', strtotime($post['END_TIME'])).'235959',
				'KEY_TYPE'			=>	$post['KEY_TYPE'],
			);
			$res_host = D($this->MHost)->addHost($hostdata);
			//Hauth 权限
			$hauthdata = array(
				'HOST_MAP_ID'		=>	$res_host['HOST_MAP_ID'],
				'HOST_STATUS'		=>	0,
				'HOST_FLAG'			=>	0,
				'HOST_PPP_FLAG'		=>	$post['HOST_PPP_FLAG'],
				'AUTH_TRANS_MAP'	=>	get_authstr($post['AUTH_TRANS_MAP']),	//特殊处理
				'AUTH_PAYS_MAP'		=>	'',
				'AUTH_ISSUE_MAP'	=>	0
			);
			$res_hauth = D($this->MHauth)->addHauth($hauthdata);
			//Hmdr 扣率
			$hmdr_list = json_decode(htmlspecialchars_decode($post['HMDR_DATA']), true);
			$hmdrdata = array();
			foreach($hmdr_list as $val){
				$hmdrdata[] = array(
					'HOST_MAP_ID'	=>	$res_host['HOST_MAP_ID'],
					'PAY_TYPE'		=>	0,
					'BDB_TYPE'		=>	0,
					'MCC_TYPE'		=>	$val['MCC_TYPE'],
					'HOST_STATUS'	=>	0,
					'PER_FEE'		=>	setMoney($val['PER_FEE'], '2'),
					'FIX_FEE'		=>	setMoney($val['FIX_FEE'], '2'),
					'PER_RAKE'		=>	''
				);
			}
			$res_hmdr = D($this->MHmdr)->addAllHmdr($hmdrdata);
			//Hcls 结算
			$hclsdata = array(
				'HOST_MAP_ID'		=>	$res_host['HOST_MAP_ID'],
				'HOST_STATUS'		=>	0,
				'HOST_SETTLE_FLAG'	=>	$post['HOST_SETTLE_FLAG'],
				'SETTLE_T'			=>	$post['SETTLE_T'],
				'SETTLE_T_UNIT'		=>	1,
				'SETTLE_FLAG'		=>	$post['SETTLE_OFF_FEE'] ? 1 : 0,
				'SETTLE_TOP_AMT'	=>	setMoney($post['SETTLE_TOP_AMT'], '2'),
				'SETTLE_FREE_AMT'	=>	setMoney($post['SETTLE_FREE_AMT'], '2'),
				'SETTLE_OFF_AMT'	=>	setMoney($post['SETTLE_OFF_AMT'], '2'),
				'SETTLE_OFF_FEE'	=>	setMoney($post['SETTLE_OFF_FEE'], '2'),
				'SETTLE_FEE'		=>	setMoney($post['SETTLE_FEE'], '2')
			);
			$res_hcls = D($this->MHcls)->addHcls($hclsdata);
			
			//判断
			if($res_host['state']!=0 || $res_hauth['state']!=0 || $res_hmdr['state']!=0 || $res_hcls['state']!=0){
				D($this->MHost)->delHost("HOST_MAP_ID='".$res_host['HOST_MAP_ID']."'");
				D($this->MHauth)->delHauth("HOST_MAP_ID='".$res_host['HOST_MAP_ID']."'");
				D($this->MHmdr)->delHmdr("HOST_MAP_ID='".$res_host['HOST_MAP_ID']."'");
				D($this->MHcls)->delHcls("HOST_MAP_ID='".$res_host['HOST_MAP_ID']."'");
				$this->wrong('添加失败！');
			}
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//交易开通
		$trans_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');	
		$this->assign('trans_list',			$trans_list);				//交易开通
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_ppp_flag',		C('HOST_PPP_FLAG'));		//映射规则
		$this->assign('mcc_type',			C('MCC_TYPE'));				//MCC分类
		$this->assign('host_settle_flag',	C('HOST_SETTLE_FLAG'));		//清算模式
		$this->display();
	}
	/*
	* 通道管理 查看
	**/
	public function host_show() {
		$home = session('HOME');
		$id   = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$field = 'ho.*,ha.HOST_PPP_FLAG,ha.AUTH_TRANS_MAP,hc.HOST_SETTLE_FLAG,hc.SETTLE_OFF_AMT,hc.SETTLE_FREE_AMT,hc.SETTLE_TOP_AMT,hc.SETTLE_T,hc.SETTLE_T_UNIT,hc.SETTLE_FLAG,hc.SETTLE_OFF_FEE,hc.SETTLE_FEE';
		$info = D($this->MHost)->findmoreHost("ho.HOST_MAP_ID='".$id."'", $field);
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//hmdr
		$hmdr_list = D($this->MHmdr)->getHmdrlist("hm.HOST_MAP_ID='".$info['HOST_MAP_ID']."'", 'hm.*', '', 'hm.MCC_TYPE asc');
		//交易开通
		$trans_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');	
		$this->assign('trans_list',			$trans_list);				//交易开通
		$this->assign ('auth_trans_checked', str_split($info['AUTH_TRANS_MAP']));	//交易开通	
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_ppp_flag',		C('HOST_PPP_FLAG'));		//映射规则
		$this->assign('hmdr_list',			$hmdr_list);				//成本扣率
		$this->assign('mcc_type',			C('MCC_TYPE'));				//MCC分类
		$this->assign('host_settle_flag',	C('HOST_SETTLE_FLAG'));		//清算模式
		$this->assign('info', 				$info);
		$this->display('host_show');
	}
	/*
	* 通道管理 修改
	**/
	public function host_edit() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "host_edit") {
			//验证
			if(empty($post['HOST_NAME']) || empty($post['END_TIME']) || empty($post['CITY_NO']) || empty($post['ADDRESS']) || empty($post['ZIP']) || empty($post['MANAGER']) || empty($post['MOBILE'])){
				$this->wrong("缺少必填项数据！");
			}
			//Host 通道
			$hostdata = array(
				'HOST_NAME'			=>	$post['HOST_NAME'],
				'HOST_NAMEAB'		=>	$post['HOST_NAMEAB'],
				'HOST_STATUS'		=>	$post['NEWS_FLAG'] == 0 ? 0 : 6,	//NEWS_FLAG是状态
				'HOST_ACQ_CODE'		=>	$post['HOST_ACQ_CODE'] ? $post['HOST_ACQ_CODE'] : '00000000000',
				'CITY_NO'			=>	$post['CITY_NO'],
				'ADDRESS'			=>	$post['ADDRESS'],
				'ZIP'				=>	$post['ZIP'],
				'TEL'				=>	$post['TEL'],
				'MANAGER'			=>	$post['MANAGER'],
				'MOBILE'			=>	$post['MOBILE'],
				'EMAIL'				=>	$post['EMAIL'],
				'END_TIME'			=>	date('Ymd', strtotime($post['END_TIME'])).'235959',
				'KEY_TYPE'			=>	$post['KEY_TYPE'],
			);
			$res_host = D($this->MHost)->updateHost("HOST_MAP_ID = '".$post['HOST_MAP_ID']."'", $hostdata);
			if($res_host['state'] != 0){
				$this->wrong("修改失败！");
			}																//NEWS_FLAG是状态
			if($post['NEWS_FLAG'] != 0 && $post['NEWS_FLAG'] != 4){
				//Hauth 权限
				$hauthdata = array(
					'HOST_PPP_FLAG'		=>	$post['HOST_PPP_FLAG'],
					'AUTH_TRANS_MAP'	=>	get_authstr($post['AUTH_TRANS_MAP']),	//特殊处理
				);
				$res_hauth = D($this->MHauth)->updateHauth("HOST_MAP_ID = '".$post['HOST_MAP_ID']."'", $hauthdata);
				if($res_hauth['state'] != 0){
					$this->wrong("修改失败！");
				}
				//Hmdr 扣率
				$hmdr_list = json_decode(htmlspecialchars_decode($post['HMDR_DATA']), true);
				$hmdrdata = array();
				foreach($hmdr_list as $val){
					$hmdrdata = array(
						'PER_FEE'		=>	setMoney($val['PER_FEE'], '2'),
						'FIX_FEE'		=>	setMoney($val['FIX_FEE'], '2')
					);
					D($this->MHmdr)->updateHmdr("HMDR_ID = '".$val['HMDR_ID']."'", $hmdrdata);
				}
				//Hcls 结算
				$hclsdata = array(
					'HOST_SETTLE_FLAG'	=>	$post['HOST_SETTLE_FLAG'],
					'SETTLE_T'			=>	$post['SETTLE_T'],
					'SETTLE_FLAG'		=>	$post['SETTLE_OFF_FEE'] ? 1 : 0,
					'SETTLE_TOP_AMT'	=>	setMoney($post['SETTLE_TOP_AMT'], '2'),
					'SETTLE_FREE_AMT'	=>	setMoney($post['SETTLE_FREE_AMT'], '2'),
					'SETTLE_OFF_AMT'	=>	setMoney($post['SETTLE_OFF_AMT'], '2'),
					'SETTLE_OFF_FEE'	=>	setMoney($post['SETTLE_OFF_FEE'], '2'),
					'SETTLE_FEE'		=>	setMoney($post['SETTLE_FEE'], '2')
				);
				$res_hcls = D($this->MHcls)->updateHcls("HOST_MAP_ID = '".$post['HOST_MAP_ID']."'", $hclsdata);
				if($res_hcls['state'] != 0){
					$this->wrong("修改失败！");
				}
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$field = 'ho.*,ha.HOST_PPP_FLAG,ha.AUTH_TRANS_MAP,hc.HOST_SETTLE_FLAG,hc.SETTLE_OFF_AMT,hc.SETTLE_FREE_AMT,hc.SETTLE_TOP_AMT,hc.SETTLE_T,hc.SETTLE_T_UNIT,hc.SETTLE_FLAG,hc.SETTLE_OFF_FEE,hc.SETTLE_FEE';
		$info  = D($this->MHost)->findmoreHost("ho.HOST_MAP_ID='".$id."'", $field);
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}		
		//hmdr
		$hmdr_list = D($this->MHmdr)->getHmdrlist("hm.HOST_MAP_ID='".$info['HOST_MAP_ID']."'", 'hm.*', '', 'hm.MCC_TYPE asc');
		//交易开通
		$trans_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');	
		$this->assign('trans_list',			$trans_list);				//交易开通
		$this->assign ('auth_trans_checked', str_split($info['AUTH_TRANS_MAP']));	//交易开通	
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_ppp_flag',		C('HOST_PPP_FLAG'));		//映射规则
		$this->assign('hmdr_list',			$hmdr_list);				//成本扣率
		$this->assign('mcc_type',			C('MCC_TYPE'));				//MCC分类
		$this->assign('host_settle_flag',	C('HOST_SETTLE_FLAG'));		//清算模式
		$this->assign('info', 				$info);
		$this->display('host_add');
	}
	/*
	* 通道管理 审核
	**/
	public function host_check() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "host_check") {
			//验证
			if(empty($post['HOST_MAP_ID']) || $post['CHECK_F']==''){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据	4待复核5初审不通过
			$resdata = array(
				'HOST_STATUS'	=>	$post['CHECK_F']==0 ? 4 : 5
			);
			$res = D($this->MHost)->updateHost("HOST_MAP_ID='".$post['HOST_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//审核记录
			$checkdata = array(
				'CHECK_NO'		=>	'1'.setStrzero($post['HOST_MAP_ID'], 15),
				'CHECK_FLAG'	=>	2,
				'CHECK_POINT'	=>	$resdata['HOST_STATUS'],
				'CHECK_DESC'	=>	$post['CHECK_DESC'],
				'USER_ID'		=>	$home['USER_ID'],
				'USER_NAME'		=>	$home['USER_NAME'],
				'CHECK_TIME'	=>	date('YmdHis')
			);
			$res_check = D($this->MCheck)->addCheck($checkdata);
			if($res_check['state'] != 0){
				$this->wrong($res_check['msg']);
			}
			$msg = $resdata['HOST_STATUS']==4 ? '待复核中...' : '初审不通过！';
			$this->right($msg, 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$field = 'ho.*,ha.HOST_PPP_FLAG,ha.AUTH_TRANS_MAP,hc.HOST_SETTLE_FLAG,hc.SETTLE_OFF_AMT,hc.SETTLE_FREE_AMT,hc.SETTLE_TOP_AMT,hc.SETTLE_T,hc.SETTLE_T_UNIT,hc.SETTLE_FLAG,hc.SETTLE_OFF_FEE,hc.SETTLE_FEE';
		$info = D($this->MHost)->findmoreHost("ho.HOST_MAP_ID='".$id."'", $field);
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['HOST_STATUS'] != 6){
			$this->wrong("当前操作不允许初审申请！");
		}
		
		//hmdr
		$hmdr_list = D($this->MHmdr)->getHmdrlist("hm.HOST_MAP_ID='".$info['HOST_MAP_ID']."'", 'hm.*', '', 'hm.MCC_TYPE asc');
		//交易开通
		$trans_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');	
		$this->assign('trans_list',			$trans_list);				//交易开通
		$this->assign ('auth_trans_checked', str_split($info['AUTH_TRANS_MAP']));	//交易开通	
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_ppp_flag',		C('HOST_PPP_FLAG'));		//映射规则
		$this->assign('hmdr_list',			$hmdr_list);				//成本扣率
		$this->assign('mcc_type',			C('MCC_TYPE'));				//MCC分类
		$this->assign('host_settle_flag',	C('HOST_SETTLE_FLAG'));		//清算模式
		$this->assign('check_title', 		'初审');
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 通道管理 复审
	**/
	public function host_recheck() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "host_recheck") {
			//验证
			if(empty($post['HOST_MAP_ID']) || $post['CHECK_F']==''){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据	0通过3复核不通过
			$resdata = array(
				'HOST_STATUS'	=>	$post['CHECK_F']==0 ? 0 : 3
			);
			$res = D($this->MHost)->updateHost("HOST_MAP_ID='".$post['HOST_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//审核记录
			$checkdata = array(
				'CHECK_NO'		=>	'1'.setStrzero($post['HOST_MAP_ID'], 15),
				'CHECK_FLAG'	=>	2,
				'CHECK_POINT'	=>	$resdata['HOST_STATUS'],
				'CHECK_DESC'	=>	$post['CHECK_DESC'],
				'USER_ID'		=>	$home['USER_ID'],
				'USER_NAME'		=>	$home['USER_NAME'],
				'CHECK_TIME'	=>	date('YmdHis')
			);
			$res_check = D($this->MCheck)->addCheck($checkdata);
			if($res_check['state'] != 0){
				$this->wrong($res_check['msg']);
			}
			$msg = $resdata['HOST_STATUS']==0 ? '已成功通过复核！' : '复核不通过！';
			$this->right($msg, 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$field = 'ho.*,ha.HOST_PPP_FLAG,ha.AUTH_TRANS_MAP,hc.HOST_SETTLE_FLAG,hc.SETTLE_OFF_AMT,hc.SETTLE_FREE_AMT,hc.SETTLE_TOP_AMT,hc.SETTLE_T,hc.SETTLE_T_UNIT,hc.SETTLE_FLAG,hc.SETTLE_OFF_FEE,hc.SETTLE_FEE';
		$info = D($this->MHost)->findmoreHost("ho.HOST_MAP_ID='".$id."'", $field);
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['HOST_STATUS'] != 4){
			$this->wrong("当前操作不允许复核申请！");
		}
		
		//hmdr
		$hmdr_list = D($this->MHmdr)->getHmdrlist("hm.HOST_MAP_ID='".$info['HOST_MAP_ID']."'", 'hm.*', '', 'hm.MCC_TYPE asc');
		//交易开通
		$trans_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');	
		$this->assign('trans_list',			$trans_list);				//交易开通
		$this->assign ('auth_trans_checked', str_split($info['AUTH_TRANS_MAP']));	//交易开通		
		
		//获取审核记录
		$CHECK_NO   = '1'.setStrzero($info['HOST_MAP_ID'], 15);
		$check_data = D($this->MCheck)->findCheck("CHECK_NO='".$CHECK_NO."' and CHECK_POINT='".$info['HOST_STATUS']."'");
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_ppp_flag',		C('HOST_PPP_FLAG'));		//映射规则
		$this->assign('hmdr_list',			$hmdr_list);				//成本扣率
		$this->assign('mcc_type',			C('MCC_TYPE'));				//MCC分类
		$this->assign('host_settle_flag',	C('HOST_SETTLE_FLAG'));		//清算模式
		$this->assign('check_title', 		'复核');
		$this->assign('info', 				$info);
		$this->assign('check_data', 		$check_data);
		$this->display('host_check');
	}
	/*
	* 通道管理 关闭
	**/
	public function host_close() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MHost)->findHost("HOST_MAP_ID = '".$id."'");		
		//过滤
		if($info['HOST_STATUS'] != 0){
			$this->wrong("当前操作不允许暂停开通！");
		}		
		$res = D($this->MHost)->updateHost("HOST_MAP_ID = '".$id."'", array('HOST_STATUS'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('暂停成功！');
	}
	/*
	* 通道管理 开启
	**/
	public function host_open() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MHost)->findHost("HOST_MAP_ID = '".$id."'");		
		//过滤
		if($info['HOST_STATUS'] != 1){
			$this->wrong("当前操作不允许恢复开通！");
		}
		$res = D($this->MHost)->updateHost("HOST_MAP_ID = '".$id."'", array('HOST_STATUS'=> 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('恢复成功！');
	}
	
	
	
	/*
	* 通道权限变更
	**/
	public function hauth() {
		$post = I('post');
		if($post['submit'] == "hauth"){
			$where = "ho.HOST_STATUS = 0"; //host表正常才显示
			//通道名称
			if($post['HOST_NAME']) {
				$where .= " and ho.HOST_NAME like '%".$post['HOST_NAME']."%'";
			}
			//状态
			if($post['HOST_STATUS'] != '') {
				if($post['HOST_STATUS'] == 0){
					$where .= " and ha.HOST_STATUS = '".$post['HOST_STATUS']."'";
				}else{
					$where .= " and hap.HOST_STATUS = '".$post['HOST_STATUS']."'";
				}
			}
			//分页
			$count = D($this->MHauth)->countHauth($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MHauth)->getHauthlist($where, 'ha.*,ho.HOST_NAME,hap.HOST_MAP_ID as TMP_ID,hap.HOST_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,ha.HOST_MAP_ID desc');
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_status_check',	C('CHECK_POINT.check'));	//通道状态
		\Cookie::set ('_currentUrl_', 		__SELF__);		
		$this->display();
	}
	/*
	* 通道权限变更	审核过程
	**/
	public function hauth_edit() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "hauth_edit") {
			//验证
			if(empty($post['HOST_MAP_ID']) || $post['HOST_PPP_FLAG']=='' || empty($post['AUTH_TRANS_MAP'])){
				$this->wrong("缺少必填项数据！");
			}
			if($post['flag'] == 0){
				$hauth_copy = D($this->MHauth)->findHauth("HOST_MAP_ID='".$post['HOST_MAP_ID']."'");
				$hauth_copy['HOST_STATUS']    = 6;
				$hauth_copy['HOST_PPP_FLAG']  = $post['HOST_PPP_FLAG'];
				$hauth_copy['AUTH_TRANS_MAP'] = get_authstr($post['AUTH_TRANS_MAP']);
				$res = D($this->MHauth)->addHauth_tmp($hauth_copy);
			}else{
				$hauth_copy = array(
					'HOST_STATUS'		=>	6,
					'HOST_PPP_FLAG'		=>	$post['HOST_PPP_FLAG'],
					'AUTH_TRANS_MAP'	=>	get_authstr($post['AUTH_TRANS_MAP'])
				);
				$res = D($this->MHauth)->updateHauth_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'", $hauth_copy);
			}
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}		
		
		$arr  = explode("_", $_REQUEST['id']);
		$id   = $arr[0];
		$flag = $arr[1]; //0正常1有修改
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		if($flag == 0){
			$hauth_data = D($this->MHauth)->findHauth("HOST_MAP_ID='".$id."'");
		}else{
			$hauth_data = D($this->MHauth)->findHauth_tmp("HOST_MAP_ID='".$id."'");			
		}
		//过滤
		if($hauth_data['HOST_STATUS'] == 4){
			$this->wrong("当前操作不允许修改！");
		}
		
		$info = D($this->MHost)->findHost("HOST_MAP_ID='".$hauth_data['HOST_MAP_ID']."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['flag']			= $flag;
		$info['HOST_PPP_FLAG']  = $hauth_data['HOST_PPP_FLAG'];
		//交易开通
		$trans_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');	
		$this->assign('trans_list',			$trans_list);				//交易开通
		$this->assign ('auth_trans_checked', str_split($hauth_data['AUTH_TRANS_MAP']));	//交易开通	
		//tmp表状态
		if($flag == 1){
			$info['HOST_STATUS'] = $hauth_data['HOST_STATUS'];
		}
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_ppp_flag',		C('HOST_PPP_FLAG'));		//映射规则
		$this->assign('info', 				$info);
		$this->display('hauth_add');
	}
	/*
	* 通道权限变更 审核
	**/
	public function hauth_check() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "hauth_check") {
			//验证
			if(empty($post['HOST_MAP_ID']) || $post['CHECK_F']==''){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据	4待复核5初审不通过
			$resdata = array(
				'HOST_STATUS'	=>	$post['CHECK_F']==0 ? 4 : 5
			);
			$res = D($this->MHauth)->updateHauth_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//审核记录
			$checkdata = array(
				'CHECK_NO'		=>	'1'.setStrzero($post['HOST_MAP_ID'], 15),
				'CHECK_FLAG'	=>	2,
				'CHECK_POINT'	=>	$resdata['HOST_STATUS'],
				'CHECK_DESC'	=>	$post['CHECK_DESC'],
				'USER_ID'		=>	$home['USER_ID'],
				'USER_NAME'		=>	$home['USER_NAME'],
				'CHECK_TIME'	=>	date('YmdHis')
			);
			$res_check = D($this->MCheck)->addCheck($checkdata);
			if($res_check['state'] != 0){
				$this->wrong($res_check['msg']);
			}
			$msg = $resdata['HOST_STATUS']==4 ? '待复核中...' : '初审不通过！';
			$this->right($msg, 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$arr  = explode("_", $_REQUEST['id']);
		$id   = $arr[0];
		$flag = $arr[1]; //0正常1有修改
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		if($flag == 0){
			$hauth_data = D($this->MHauth)->findHauth("HOST_MAP_ID='".$id."'");
		}else{
			$hauth_data = D($this->MHauth)->findHauth_tmp("HOST_MAP_ID='".$id."'");			
		}
		if($hauth_data['HOST_STATUS'] != 6){
			$this->wrong("当前操作不允许初审申请！");
		}
		
		$info = D($this->MHost)->findHost("HOST_MAP_ID='".$hauth_data['HOST_MAP_ID']."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['flag']			= $flag;
		$info['HOST_PPP_FLAG']  = $hauth_data['HOST_PPP_FLAG'];
		//交易开通
		$trans_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');	
		$this->assign('trans_list',			$trans_list);				//交易开通
		$this->assign ('auth_trans_checked', str_split($hauth_data['AUTH_TRANS_MAP']));	//交易开通	
		//tmp表状态
		if($flag == 1){
			$info['HOST_STATUS'] = $hauth_data['HOST_STATUS'];
		}
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_ppp_flag',		C('HOST_PPP_FLAG'));		//映射规则
		$this->assign('check_title', 		'初审');
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 通道权限变更 复审
	**/
	public function hauth_recheck() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "hauth_recheck") {
			//验证
			if(empty($post['HOST_MAP_ID']) || $post['CHECK_F']==''){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据	0通过3复核不通过
			$resdata = array(
				'HOST_STATUS'	=>	$post['CHECK_F']==0 ? 0 : 3
			);
			if($resdata['HOST_STATUS'] == 0){
				$hauth_copy = D($this->MHauth)->findHauth_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'");
				$hauth_copy['HOST_STATUS'] = 0;
				$res = D($this->MHauth)->updateHauth("HOST_MAP_ID='".$post['HOST_MAP_ID']."'", $hauth_copy);
				if($res['state'] != 0){
					$this->wrong($res['msg']);
				}
				D($this->MHauth)->delHauth_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'");
			}else{
				$res = D($this->MHauth)->updateHauth_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'", $resdata);
				if($res['state'] != 0){
					$this->wrong($res['msg']);
				}
			}			
			//审核记录
			$checkdata = array(
				'CHECK_NO'		=>	'1'.setStrzero($post['HOST_MAP_ID'], 15),
				'CHECK_FLAG'	=>	2,
				'CHECK_POINT'	=>	$resdata['HOST_STATUS'],
				'CHECK_DESC'	=>	$post['CHECK_DESC'],
				'USER_ID'		=>	$home['USER_ID'],
				'USER_NAME'		=>	$home['USER_NAME'],
				'CHECK_TIME'	=>	date('YmdHis')
			);
			$res_check = D($this->MCheck)->addCheck($checkdata);
			if($res_check['state'] != 0){
				$this->wrong($res_check['msg']);
			}
			$msg = $resdata['HOST_STATUS']==0 ? '已成功通过复核！' : '复核不通过！';
			$this->right($msg, 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$arr  = explode("_", $_REQUEST['id']);
		$id   = $arr[0];
		$flag = $arr[1]; //0正常1有修改
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		if($flag == 0){
			$hauth_data = D($this->MHauth)->findHauth("HOST_MAP_ID='".$id."'");
		}else{
			$hauth_data = D($this->MHauth)->findHauth_tmp("HOST_MAP_ID='".$id."'");			
		}
		if($hauth_data['HOST_STATUS'] != 4){
			$this->wrong("当前操作不允许复核申请！");
		}
		
		$info = D($this->MHost)->findHost("HOST_MAP_ID='".$hauth_data['HOST_MAP_ID']."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['flag']			= $flag;
		$info['HOST_PPP_FLAG']  = $hauth_data['HOST_PPP_FLAG'];
		//交易开通
		$trans_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');	
		$this->assign('trans_list',			$trans_list);				//交易开通
		$this->assign ('auth_trans_checked', str_split($hauth_data['AUTH_TRANS_MAP']));	//交易开通	
		//tmp表状态
		if($flag == 1){
			$info['HOST_STATUS'] = $hauth_data['HOST_STATUS'];
		}
		//获取审核记录
		$CHECK_NO   = '1'.setStrzero($info['HOST_MAP_ID'], 15);
		$check_data = D($this->MCheck)->findCheck("CHECK_NO='".$CHECK_NO."' and CHECK_POINT='".$hauth_data['HOST_STATUS']."'");		
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_ppp_flag',		C('HOST_PPP_FLAG'));		//映射规则
		$this->assign('check_title', 		'复核');
		$this->assign('info', 				$info);
		$this->assign('check_data', 		$check_data);
		$this->display('hauth_check');
	}
	
	
	
	/*
	* 通道扣率变更
	**/
	public function hmdr() {
		$post = I('post');
		if($post['submit'] == "hmdr"){
			$where = "ho.HOST_STATUS = 0";
			//通道名称
			if($post['HOST_NAME']) {
				$where .= " and ho.HOST_NAME like '%".$post['HOST_NAME']."%'";
			}
			//状态
			if($post['HOST_STATUS'] != '') {
				if($post['HOST_STATUS'] == 0){
					$where .= " and hm.HOST_STATUS = '".$post['HOST_STATUS']."'";
				}else{
					$where .= " and hmp.HOST_STATUS = '".$post['HOST_STATUS']."'";
				}
			}
			//分页
			$count = D($this->MHmdr)->countgroupHmdr($where);
			$count = $count['total'];
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MHmdr)->getHmdrgrouplist($where, 'hm.*,ho.HOST_NAME,hmp.HOST_MAP_ID as TMP_ID,hmp.HOST_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,hm.HMDR_ID desc');
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_status_check',	C('CHECK_POINT.check'));	//通道状态
		\Cookie::set ('_currentUrl_', 		__SELF__);		
		$this->display();
	}	
	/*
	* 通道扣率变更	审核过程
	**/
	public function hmdr_edit() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "hmdr_edit") {
			//验证
			if(empty($post['HMDR_DATA'])){
				$this->wrong("缺少必填项数据！");
			}
			$hmdr_list = json_decode(htmlspecialchars_decode($post['HMDR_DATA']), true);
			if($post['flag'] == 0){
				$hmdrtmp = array();
				foreach($hmdr_list as $val){
					$hmdrdata = D($this->MHmdr)->findHmdr("HMDR_ID = '".$val['HMDR_ID']."'");
					$hmdrdata['HOST_STATUS'] = 6;
					$hmdrdata['PER_FEE']  	 = setMoney($val['PER_FEE'], '2');
					$hmdrdata['FIX_FEE']  	 = setMoney($val['FIX_FEE'], '2');
					$hmdrtmp[] = $hmdrdata;
				}
				$res = D($this->MHmdr)->addAllHmdr_tmp($hmdrtmp);
			}else{
				foreach($hmdr_list as $val){
					$hmdr_copy = array(
						'HOST_STATUS'	=>	6,
						'PER_FEE'		=>	setMoney($val['PER_FEE'], '2'),
						'FIX_FEE'		=>	setMoney($val['FIX_FEE'], '2')
					);
					$res = D($this->MHmdr)->updateHmdr_tmp("HMDR_ID='".$val['HMDR_ID']."'", $hmdr_copy);
				}
			}
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}		
		
		$arr  = explode("_", $_REQUEST['id']);
		$id   = $arr[0];
		$flag = $arr[1];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		if($flag == 0){
			$hmdr_list = D($this->MHmdr)->getHmdrlist("hm.HOST_MAP_ID='".$id."'", 'hm.*', '', 'hm.MCC_TYPE asc');
		}else{
			$hmdr_list = D($this->MHmdr)->getHmdrlist("hm.HOST_MAP_ID='".$id."'", 'hmp.*', '', 'hmp.MCC_TYPE asc');
		}
		//过滤
		if($hmdr_list[0]['HOST_STATUS'] == 4){
			$this->wrong("当前操作不允许修改！");
		}
				
		$info = D($this->MHost)->findHost("HOST_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['flag']			= $flag;
		//tmp表状态
		if($flag == 1){
			$info['HOST_STATUS'] = $hmdr_list[0]['HOST_STATUS'];
		}
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('mcc_type',			C('MCC_TYPE'));				//映射规则
		$this->assign('info', 				$info);
		$this->assign('hmdr_list', 			$hmdr_list);
		$this->display('hmdr_add');
	}
	/*
	* 通道扣率变更 审核
	**/
	public function hmdr_check() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "hmdr_check") {
			//验证
			if(empty($post['HOST_MAP_ID']) || $post['CHECK_F']==''){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据	4待复核5初审不通过
			$resdata = array(
				'HOST_STATUS'	=>	$post['CHECK_F']==0 ? 4 : 5
			);
			$res = D($this->MHmdr)->updateHmdr_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//审核记录
			$checkdata = array(
				'CHECK_NO'		=>	'1'.setStrzero($post['HOST_MAP_ID'], 15),
				'CHECK_FLAG'	=>	2,
				'CHECK_POINT'	=>	$resdata['HOST_STATUS'],
				'CHECK_DESC'	=>	$post['CHECK_DESC'],
				'USER_ID'		=>	$home['USER_ID'],
				'USER_NAME'		=>	$home['USER_NAME'],
				'CHECK_TIME'	=>	date('YmdHis')
			);
			$res_check = D($this->MCheck)->addCheck($checkdata);
			if($res_check['state'] != 0){
				$this->wrong($res_check['msg']);
			}
			$msg = $resdata['HOST_STATUS']==4 ? '待复核中...' : '初审不通过！';
			$this->right($msg, 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}		
		
		$arr  = explode("_", $_REQUEST['id']);
		$id   = $arr[0];
		$flag = $arr[1];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		if($flag == 0){
			$hmdr_list = D($this->MHmdr)->getHmdrlist("hm.HOST_MAP_ID='".$id."'", 'hm.*', '', 'hm.MCC_TYPE asc');
		}else{
			$hmdr_list = D($this->MHmdr)->getHmdrlist("hm.HOST_MAP_ID='".$id."'", 'hmp.*', '', 'hmp.MCC_TYPE asc');
		}
		if($hmdr_list[0]['HOST_STATUS'] != 6){
			$this->wrong("当前操作不允许初审申请！");
		}
				
		$info = D($this->MHost)->findHost("HOST_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//tmp表状态
		if($flag == 1){
			$info['HOST_STATUS'] = $hmdr_list[0]['HOST_STATUS'];
		}
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('mcc_type',			C('MCC_TYPE'));				//映射规则
		$this->assign('title', 				'初审');
		$this->assign('info', 				$info);
		$this->assign('hmdr_list', 			$hmdr_list);
		$this->display();
	}
	/*
	* 通道扣率变更 复审
	**/
	public function hmdr_recheck() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "hmdr_recheck") {
			//验证
			if(empty($post['HOST_MAP_ID']) || $post['CHECK_F']==''){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据	0通过3复核不通过
			$resdata = array(
				'HOST_STATUS'	=>	$post['CHECK_F']==0 ? 0 : 3
			);
			if($resdata['HOST_STATUS'] == 0){
				$hmdr_list = D($this->MHmdr)->getHmdrlist("hm.HOST_MAP_ID='".$post['HOST_MAP_ID']."'", 'hmp.*', '', 'hmp.MCC_TYPE asc');
				foreach($hmdr_list as $val){
					$hmdr_copy = array(
						'HOST_STATUS'	=>	0,
						'PER_FEE'		=>	$val['PER_FEE'],
						'FIX_FEE'		=>	$val['FIX_FEE']
					);
					$res = D($this->MHmdr)->updateHmdr("HMDR_ID='".$val['HMDR_ID']."'", $hmdr_copy);
				}
				if($res['state'] != 0){
					$this->wrong($res['msg']);
				}
				D($this->MHmdr)->delHmdr_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'");
			}else{
				$res = D($this->MHmdr)->updateHmdr_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'", $resdata);
				if($res['state'] != 0){
					$this->wrong($res['msg']);
				}
			}			
			//审核记录
			$checkdata = array(
				'CHECK_NO'		=>	'1'.setStrzero($post['HOST_MAP_ID'], 15),
				'CHECK_FLAG'	=>	2,
				'CHECK_POINT'	=>	$resdata['HOST_STATUS'],
				'CHECK_DESC'	=>	$post['CHECK_DESC'],
				'USER_ID'		=>	$home['USER_ID'],
				'USER_NAME'		=>	$home['USER_NAME'],
				'CHECK_TIME'	=>	date('YmdHis')
			);
			$res_check = D($this->MCheck)->addCheck($checkdata);
			if($res_check['state'] != 0){
				$this->wrong($res_check['msg']);
			}
			$msg = $resdata['HOST_STATUS']==0 ? '已成功通过复核！' : '复核不通过！';
			$this->right($msg, 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
				
		$arr  = explode("_", $_REQUEST['id']);
		$id   = $arr[0];
		$flag = $arr[1];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		if($flag == 0){
			$hmdr_list = D($this->MHmdr)->getHmdrlist("hm.HOST_MAP_ID='".$id."'", 'hm.*', '', 'hm.MCC_TYPE asc');
		}else{
			$hmdr_list = D($this->MHmdr)->getHmdrlist("hm.HOST_MAP_ID='".$id."'", 'hmp.*', '', 'hmp.MCC_TYPE asc');
		}
		if($hmdr_list[0]['HOST_STATUS'] != 4){
			$this->wrong("当前操作不允许复核申请！");
		}
				
		$info = D($this->MHost)->findHost("HOST_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//tmp表状态
		if($flag == 1){
			$info['HOST_STATUS'] = $hmdr_list[0]['HOST_STATUS'];
		}
		//获取审核记录
		$CHECK_NO   = '1'.setStrzero($hmdr_list[0]['HOST_MAP_ID'], 15);
		$check_data = D($this->MCheck)->findCheck("CHECK_NO='".$CHECK_NO."' and CHECK_POINT='".$hmdr_list[0]['HOST_STATUS']."'");
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('mcc_type',			C('MCC_TYPE'));				//映射规则
		$this->assign('title', 				'复核');
		$this->assign('info', 				$info);
		$this->assign('hmdr_list', 			$hmdr_list);
		$this->assign('check_data', 		$check_data);
		$this->display('hmdr_check');
	}
	
	
	
	/*
	* 通道手续费变更
	**/
	public function hcls() {
		$post = I('post');
		if($post['submit'] == "hcls"){
			$where = "ho.HOST_STATUS = 0";
			//通道名称
			if($post['HOST_NAME']) {
				$where .= " and ho.HOST_NAME like '%".$post['HOST_NAME']."%'";
			}
			//状态
			if($post['HOST_STATUS'] != '') {
				if($post['HOST_STATUS'] == 0){
					$where .= " and hc.HOST_STATUS = '".$post['HOST_STATUS']."'";
				}else{
					$where .= " and hcp.HOST_STATUS = '".$post['HOST_STATUS']."'";
				}
			}
			//分页
			$count = D($this->MHcls)->countHcls($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MHcls)->getHclslist($where, 'hc.*,ho.HOST_NAME,hcp.HOST_MAP_ID as TMP_ID,hcp.HOST_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,hc.HOST_MAP_ID desc');
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_status_check',	C('CHECK_POINT.check'));	//通道状态
		\Cookie::set ('_currentUrl_', 		__SELF__);		
		$this->display();
	}
	/*
	* 通道手续费变更	审核过程
	**/
	public function hcls_edit() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "hcls_edit") {
			//验证
			if(empty($post['HOST_MAP_ID']) || $post['HOST_SETTLE_FLAG']=='' || empty($post['SETTLE_OFF_AMT']) || empty($post['SETTLE_FREE_AMT']) || 
			empty($post['SETTLE_TOP_AMT']) || $post['SETTLE_T']==''){
				$this->wrong("缺少必填项数据！");
			}
			if($post['flag'] == 0){
				$hcls_copy = D($this->MHcls)->findHcls("HOST_MAP_ID='".$post['HOST_MAP_ID']."'");
				$hcls_copy['HOST_STATUS']   = 6;
				$hcls_copy['HOST_SETTLE_FLAG']	= $post['HOST_SETTLE_FLAG'];
				$hcls_copy['SETTLE_T']			= $post['SETTLE_T'];
				$hcls_copy['SETTLE_FLAG']		= $post['SETTLE_OFF_FEE'] ? 1 : 0;
				$hcls_copy['SETTLE_TOP_AMT']	= setMoney($post['SETTLE_TOP_AMT'], '2');
				$hcls_copy['SETTLE_FREE_AMT']	= setMoney($post['SETTLE_FREE_AMT'], '2');
				$hcls_copy['SETTLE_OFF_AMT']	= setMoney($post['SETTLE_OFF_AMT'], '2');
				$hcls_copy['SETTLE_OFF_FEE']	= setMoney($post['SETTLE_OFF_FEE'], '2');
				$hcls_copy['SETTLE_FEE']		= setMoney($post['SETTLE_FEE'], '2');
				$res = D($this->MHcls)->addHcls_tmp($hcls_copy);
			}else{
				$hcls_copy = array(
					'HOST_STATUS'		=> 	6,
					'HOST_SETTLE_FLAG'	=> 	$post['HOST_SETTLE_FLAG'],
					'SETTLE_T'			=> 	$post['SETTLE_T'],
					'SETTLE_FLAG'		=> 	$post['SETTLE_OFF_FEE'] ? 1 : 0,
					'SETTLE_TOP_AMT'	=> 	setMoney($post['SETTLE_TOP_AMT'], '2'),
					'SETTLE_FREE_AMT'	=> 	setMoney($post['SETTLE_FREE_AMT'], '2'),
					'SETTLE_OFF_AMT'	=> 	setMoney($post['SETTLE_OFF_AMT'], '2'),
					'SETTLE_OFF_FEE'	=> 	setMoney($post['SETTLE_OFF_FEE'], '2'),
					'SETTLE_FEE'		=> 	setMoney($post['SETTLE_FEE'], '2')
				);
				$res = D($this->MHcls)->updateHcls_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'", $hcls_copy);
			}
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}		
		
		$arr  = explode("_", $_REQUEST['id']);
		$id   = $arr[0];
		$flag = $arr[1];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		if($flag == 0){
			$hcls_data = D($this->MHcls)->findHcls("HOST_MAP_ID='".$id."'");
		}else{
			$hcls_data = D($this->MHcls)->findHcls_tmp("HOST_MAP_ID='".$id."'");			
		}
		//过滤
		if($hcls_data['HOST_STATUS'] == 4){
			$this->wrong("当前操作不允许修改！");
		}
		
		$info = D($this->MHost)->findHost("HOST_MAP_ID='".$hcls_data['HOST_MAP_ID']."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//显示tmp表状态
		if($flag == 1){
			$info['HOST_STATUS'] = $hcls_data['HOST_STATUS'];
		}
		$info['flag']				= $flag;
		$info['HOST_SETTLE_FLAG']  	= $hcls_data['HOST_SETTLE_FLAG'];
		$info['SETTLE_OFF_AMT']  	= $hcls_data['SETTLE_OFF_AMT'];
		$info['SETTLE_FREE_AMT']  	= $hcls_data['SETTLE_FREE_AMT'];
		$info['SETTLE_TOP_AMT']  	= $hcls_data['SETTLE_TOP_AMT'];
		$info['SETTLE_T']  			= $hcls_data['SETTLE_T'];
		$info['SETTLE_T_UNIT']  	= $hcls_data['SETTLE_T_UNIT'];
		$info['SETTLE_FLAG']  		= $hcls_data['SETTLE_FLAG'];
		$info['SETTLE_OFF_FEE']  	= $hcls_data['SETTLE_OFF_FEE'];
		$info['SETTLE_FEE']  		= $hcls_data['SETTLE_FEE'];
				
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_settle_flag',	C('HOST_SETTLE_FLAG'));		//清算模式
		$this->assign('info', 				$info);
		$this->display('hcls_add');
	}
	/*
	* 通道手续费变更 审核
	**/
	public function hcls_check() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "hcls_check") {
			//验证
			if(empty($post['HOST_MAP_ID']) || $post['CHECK_F']==''){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据	4待复核5初审不通过
			$resdata = array(
				'HOST_STATUS'	=>	$post['CHECK_F']==0 ? 4 : 5
			);
			$res = D($this->MHcls)->updateHcls_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//审核记录
			$checkdata = array(
				'CHECK_NO'		=>	'1'.setStrzero($post['HOST_MAP_ID'], 15),
				'CHECK_FLAG'	=>	2,
				'CHECK_POINT'	=>	$resdata['HOST_STATUS'],
				'CHECK_DESC'	=>	$post['CHECK_DESC'],
				'USER_ID'		=>	$home['USER_ID'],
				'USER_NAME'		=>	$home['USER_NAME'],
				'CHECK_TIME'	=>	date('YmdHis')
			);
			$res_check = D($this->MCheck)->addCheck($checkdata);
			if($res_check['state'] != 0){
				$this->wrong($res_check['msg']);
			}
			$msg = $resdata['HOST_STATUS']==4 ? '待复核中...' : '初审不通过！';
			$this->right($msg, 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$arr  = explode("_", $_REQUEST['id']);
		$id   = $arr[0];
		$flag = $arr[1];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		if($flag == 0){
			$hcls_data = D($this->MHcls)->findHcls("HOST_MAP_ID='".$id."'");
		}else{
			$hcls_data = D($this->MHcls)->findHcls_tmp("HOST_MAP_ID='".$id."'");			
		}
		if($hcls_data['HOST_STATUS'] != 6){
			$this->wrong("当前操作不允许初审申请！");
		}
		
		$info = D($this->MHost)->findHost("HOST_MAP_ID='".$hcls_data['HOST_MAP_ID']."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['HOST_SETTLE_FLAG']  	= $hcls_data['HOST_SETTLE_FLAG'];
		$info['SETTLE_OFF_AMT']  	= $hcls_data['SETTLE_OFF_AMT'];
		$info['SETTLE_FREE_AMT']  	= $hcls_data['SETTLE_FREE_AMT'];
		$info['SETTLE_TOP_AMT']  	= $hcls_data['SETTLE_TOP_AMT'];
		$info['SETTLE_T']  			= $hcls_data['SETTLE_T'];
		$info['SETTLE_T_UNIT']  	= $hcls_data['SETTLE_T_UNIT'];
		$info['SETTLE_FLAG']  		= $hcls_data['SETTLE_FLAG'];
		$info['SETTLE_OFF_FEE']  	= $hcls_data['SETTLE_OFF_FEE'];
		$info['SETTLE_FEE']  		= $hcls_data['SETTLE_FEE'];
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_settle_flag',	C('HOST_SETTLE_FLAG'));		//清算模式
		$this->assign('check_title', 		'初审');
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 通道手续费变更 复审
	**/
	public function hcls_recheck() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "hcls_recheck") {
			//验证
			if(empty($post['HOST_MAP_ID']) || $post['CHECK_F']==''){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据	0通过3复核不通过
			$resdata = array(
				'HOST_STATUS'	=>	$post['CHECK_F']==0 ? 0 : 3
			);
			if($resdata['HOST_STATUS'] == 0){
				$hcls_copy = D($this->MHcls)->findHcls_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'");
				$hcls_copy['HOST_STATUS'] = 0;
				$res = D($this->MHcls)->updateHcls("HOST_MAP_ID='".$post['HOST_MAP_ID']."'", $hcls_copy);
				if($res['state'] != 0){
					$this->wrong($res['msg']);
				}
				D($this->MHcls)->delHcls_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'");
			}else{
				$res = D($this->MHcls)->updateHcls_tmp("HOST_MAP_ID='".$post['HOST_MAP_ID']."'", $resdata);
				if($res['state'] != 0){
					$this->wrong($res['msg']);
				}
			}			
			//审核记录
			$checkdata = array(
				'CHECK_NO'		=>	'1'.setStrzero($post['HOST_MAP_ID'], 15),
				'CHECK_FLAG'	=>	2,
				'CHECK_POINT'	=>	$resdata['HOST_STATUS'],
				'CHECK_DESC'	=>	$post['CHECK_DESC'],
				'USER_ID'		=>	$home['USER_ID'],
				'USER_NAME'		=>	$home['USER_NAME'],
				'CHECK_TIME'	=>	date('YmdHis')
			);
			$res_check = D($this->MCheck)->addCheck($checkdata);
			if($res_check['state'] != 0){
				$this->wrong($res_check['msg']);
			}
			$msg = $resdata['HOST_STATUS']==0 ? '已成功通过复核！' : '复核不通过！';
			$this->right($msg, 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$arr  = explode("_", $_REQUEST['id']);
		$id   = $arr[0];
		$flag = $arr[1];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		if($flag == 0){
			$hcls_data = D($this->MHcls)->findHcls("HOST_MAP_ID='".$id."'");
		}else{
			$hcls_data = D($this->MHcls)->findHcls_tmp("HOST_MAP_ID='".$id."'");			
		}
		if($hcls_data['HOST_STATUS'] != 4){
			$this->wrong("当前操作不允许复核申请！");
		}
		
		$info = D($this->MHost)->findHost("HOST_MAP_ID='".$hcls_data['HOST_MAP_ID']."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//获取审核记录
		$CHECK_NO   = '1'.setStrzero($info['HOST_MAP_ID'], 15);
		$check_data = D($this->MCheck)->findCheck("CHECK_NO='".$CHECK_NO."' and CHECK_POINT='".$hcls_data['HOST_STATUS']."'");
		
		$info['HOST_SETTLE_FLAG']  	= $hcls_data['HOST_SETTLE_FLAG'];
		$info['SETTLE_OFF_AMT']  	= $hcls_data['SETTLE_OFF_AMT'];
		$info['SETTLE_FREE_AMT']  	= $hcls_data['SETTLE_FREE_AMT'];
		$info['SETTLE_TOP_AMT']  	= $hcls_data['SETTLE_TOP_AMT'];
		$info['SETTLE_T']  			= $hcls_data['SETTLE_T'];
		$info['SETTLE_T_UNIT']  	= $hcls_data['SETTLE_T_UNIT'];
		$info['SETTLE_FLAG']  		= $hcls_data['SETTLE_FLAG'];
		$info['SETTLE_OFF_FEE']  	= $hcls_data['SETTLE_OFF_FEE'];
		$info['SETTLE_FEE']  		= $hcls_data['SETTLE_FEE'];
		
		$this->assign('key_type',			C('KEY_TYPE'));				//秘钥体系
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_settle_flag',	C('HOST_SETTLE_FLAG'));		//清算模式
		$this->assign('title', 				'复核');
		$this->assign('info', 				$info);
		$this->assign('check_data', 		$check_data);
		$this->display('hcls_check');
	}
	
	
	
	/*
	* 通道报备商户
	**/
	public function hshop() {
		$post = I('post');
		if($post['submit'] == "hshop"){
			$where = "1=1";
			//通道名称
			if($post['HOST_MAP_ID']) {
				$where .= " and HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
			}
			//MCC类
			if($post['MCC_CODE']) {
				$where .= " and MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//状态
			if($post['HSHOP_STATUS'] != '') {
				$where .= " and HSHOP_STATUS = '".$post['HSHOP_STATUS']."'";
			}
			//商户号
			if($post['HSHOP_NO']) {
				$where .= " and HSHOP_NO = '".$post['HSHOP_NO']."'";
			}
			//分页
			$count = D($this->MHshop)->countHshop($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MHshop)->getHshoplist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		//通道列表
		$host = D($this->MHost)->getHostlist("HOST_STATUS = 0", 'HOST_MAP_ID,HOST_NAME');
		foreach($host as $val){
			$host_list[$val['HOST_MAP_ID']] = $val['HOST_NAME'];
		}
		$this->assign('host_list',			$host_list);
		$this->assign('hshop_status',		C('HSHOP_STATUS'));	//通道商户状态
		$this->assign('mcc_type',			C('MCC_TYPE'));		//mcc大类
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 通道报备商户 添加
	**/
	public function hshop_add() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "hshop_add") {
			//验证
			if(empty($post['HSHOP_NO']) || empty($post['HSHOP_NAME']) || empty($post['HSHOP_NAMEABCN']) || empty($post['HSHOP_NAMEABEN'])){
				$this->wrong("缺少必填项数据！");
			}
			//检查
			$finddata = D($this->MHshop)->findHshop("HOST_MAP_ID='".$post['HOST_MAP_ID']."' and HSHOP_NO='".$post['HSHOP_NO']."'");
			if(!empty($finddata)){
				$this->wrong("该通道下的商户编号已经存在！");
			}
			$citydata = D('MCity')->findCity("CITY_S_CODE='".$post['CITY_NO']."'", 'PROVINCE_NAME');
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'		=>	$home['BRANCH_MAP_ID'],
				'HOST_MAP_ID'		=>	$post['HOST_MAP_ID'],
				'HSHOP_NO'			=>	$post['HSHOP_NO'],
				'HSHOP_NAMEABCN'	=>	$post['HSHOP_NAMEABCN'],
				'HSHOP_NAMEABEN'	=>	$post['HSHOP_NAMEABEN'],
				'HSHOP_NAME'		=>	$post['HSHOP_NAME'],
				'HSHOP_STATUS'		=>	0,
				'HSHOPWORK_CNT'		=>	0,
				'MCC_TYPE'			=>	$post['MCC_TYPE'],
				'MCC_CODE'			=>	$post['MCC_CODE'],
				'CITY_NO'			=>	$post['CITY_NO'],
				'CITY_NAME'			=>	$citydata['PROVINCE_NAME'],	//城市名称
				'HSHOP_ADDRESS'		=>	$post['HSHOP_ADDRESS'],
				'CREATE_TIME'		=>	date("YmdHis")
			);
			$res = D($this->MHshop)->addHshop($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//通道列表
		$host_list = D($this->MHost)->getHostlist("HOST_STATUS = 0", 'HOST_MAP_ID,HOST_NAME');
		$this->assign('host_list',			$host_list);
		$this->display();
	}
	/*
	* 通道报备商户 查看
	**/
	public function hshop_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MHshop)->findHshop("HSHOP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//通道数据
		$hostdata = D($this->MHost)->findHost("HOST_MAP_ID='".$info['HOST_MAP_ID']."'", 'HOST_NAME');
		$info['HOST_NAME'] = $hostdata['HOST_NAME'];
		$this->assign('mcc_type',			C('MCC_TYPE'));
		$this->assign ('info', 				$info);
		$this->display('hshop_show');
	}
	/*
	* 通道报备商户 修改
	**/
	public function hshop_edit() {
		$post = I('post');
		if($post['submit'] == "hshop_edit") {
			//验证
			if(empty($post['HSHOP_NO']) || empty($post['HSHOP_NAME']) || empty($post['HSHOP_NAMEABCN']) || empty($post['HSHOP_NAMEABEN'])){
				$this->wrong("缺少必填项数据！");
			}
			//检查
			$finddata = D($this->MHshop)->findHshop("HSHOP_ID!='".$post['HSHOP_ID']."' and HOST_MAP_ID='".$post['HOST_MAP_ID']."' and HSHOP_NO='".$post['HSHOP_NO']."'");
			if(!empty($finddata)){
				$this->wrong("该通道下的商户编号已经存在！");
			}
			$citydata = D('MCity')->findCity("CITY_S_CODE='".$post['CITY_NO']."'", 'PROVINCE_NAME');
			//组装数据
			$resdata = array(
				'HOST_MAP_ID'		=>	$post['HOST_MAP_ID'],
				'HSHOP_NO'			=>	$post['HSHOP_NO'],
				'HSHOP_NAMEABCN'	=>	$post['HSHOP_NAMEABCN'],
				'HSHOP_NAMEABEN'	=>	$post['HSHOP_NAMEABEN'],
				'HSHOP_NAME'		=>	$post['HSHOP_NAME'],
				'MCC_TYPE'			=>	$post['MCC_TYPE'],
				'MCC_CODE'			=>	$post['MCC_CODE'],
				'CITY_NO'			=>	$post['CITY_NO'],
				'CITY_NAME'			=>	$citydata['PROVINCE_NAME'],	//城市名称
				'HSHOP_ADDRESS'		=>	$post['HSHOP_ADDRESS']
			);
			$res = D($this->MHshop)->updateHshop("HSHOP_ID='".$post['HSHOP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MHshop)->findHshop("HSHOP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//通道列表
		$host_list = D($this->MHost)->getHostlist("HOST_STATUS = 0", 'HOST_MAP_ID,HOST_NAME');
		$this->assign('host_list',			$host_list);
		$this->assign('info', 				$info);
		$this->display('hshop_add');
	}
	/*
	* 通道报备商户 关闭
	**/
	public function hshop_close() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MHshop)->findHshop("HSHOP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//过滤
		if($info['HSHOP_STATUS'] != 0){
			$this->wrong("当前操作不允许暂停操作！");
		}
		$res = D($this->MHshop)->updateHshop("HSHOP_ID='".$id."'", array('HSHOP_STATUS'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		//该通道商户下的所有pos也暂停		
		$res = D($this->MHpos)->updateHpos("HOST_MAP_ID='".$info['HOST_MAP_ID']."' and HSHOP_NO='".$info['HSHOP_NO']."'", array('HPOS_STATUS'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('暂停成功！');
	}
	/*
	* 通道报备商户 开启
	**/
	public function hshop_open() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MHshop)->findHshop("HSHOP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//过滤
		if($info['HSHOP_STATUS'] != 1){
			$this->wrong("当前操作不允许恢复操作！");
		}
		$res = D($this->MHshop)->updateHshop("HSHOP_ID='".$id."'", array('HSHOP_STATUS'=> 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		//该通道商户下的所有pos也暂停		
		$res = D($this->MHpos)->updateHpos("HOST_MAP_ID='".$info['HOST_MAP_ID']."' and HSHOP_NO='".$info['HSHOP_NO']."'", array('HPOS_STATUS'=> 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('恢复成功！');
	}
	
	
	
	/*
	* 通道报备终端
	**/
	public function hpos() {
		$post = I('post');
		if($post['submit'] == "hpos"){
			$where = "1=1";			
			//通道
			if($post['HOST_MAP_ID']) {
				$where .= " and ho.HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
			}
			//MCC类
			if($post['MCC_CODE']) {
				$where .= " and hs.MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//状态
			if($post['HPOS_STATUS'] != '') {
				$where .= " and hp.HPOS_STATUS = '".$post['HPOS_STATUS']."'";
			}
			//商户号
			if($post['HSHOP_NO']) {
				$where .= " and hs.HSHOP_NO = '".$post['HSHOP_NO']."'";
			}
			//终端号
			if($post['HPOS_NO']) {
				$where .= " and hp.HPOS_NO = '".$post['HPOS_NO']."'";
			}
			
			//分页
			$count = D($this->MHpos)->countHpos($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MHpos)->getHposlist($where, 'hp.*,ho.HOST_MAP_ID,ho.HOST_NAME,hs.HSHOP_NO,hs.HSHOP_NAME', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}		
		//通道列表
		$host = D($this->MHost)->getHostlist("HOST_STATUS = 0", 'HOST_MAP_ID,HOST_NAME');
		foreach($host as $val){
			$host_list[$val['HOST_MAP_ID']] = $val['HOST_NAME'];
		}
		$this->assign('host_list',			$host_list);
		$this->assign('login_flag',			C('LOGIN_FLAG'));	//签到标志
		$this->assign('hpos_status',		C('HPOS_STATUS'));	//终端状态
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 通道报备终端 添加
	**/
	public function hpos_add() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "hpos_add") {
			//验证
			if(empty($post['HSHOP_NO']) || empty($post['HPOS_NO']) || empty($post['HPOS_BATCH']) || empty($post['HPOS_TRACE'])){
				$this->wrong("缺少必填项数据！");
			}
			//检查商户是否存在
			$hshopdata = D($this->MHshop)->findHshop("HOST_MAP_ID='".$post['HOST_MAP_ID']."' and HSHOP_NO='".$post['HSHOP_NO']."'");
			if(empty($hshopdata)){
				$this->wrong("该通道下的商户编号不存在！");
			}
			//检查
			$finddata = D($this->MHpos)->findHpos("HOST_MAP_ID='".$post['HOST_MAP_ID']."' and HSHOP_NO='".$post['HSHOP_NO']."' and HPOS_NO='".$post['HPOS_NO']."'");
			if(!empty($finddata)){
				$this->wrong("该通道下的商户编号和终端号已经存在！");
			}
			
			//获取秘钥体系
			$host_data = D($this->MHost)->findHost("HOST_MAP_ID = '".$post['HOST_MAP_ID']."'", 'KEY_TYPE');
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'		=>	0,
				'HOST_MAP_ID'		=>	$post['HOST_MAP_ID'],
				'HSHOP_NO'			=>	$post['HSHOP_NO'],
				'HPOS_NO'			=>	$post['HPOS_NO'],
				'HPOS_STATUS'		=>	0,
				'POSWORK_CNT'		=>	0,
				'LOGIN_FLAG'		=>	1,
				'HPOS_BATCH'		=>	$post['HPOS_BATCH'],
				'HPOS_TRACE'		=>	$post['HPOS_TRACE'],
				'KEY_INDEX'			=>	$host_data['KEY_TYPE']==1 ? setStrzero($post['HSHOP_NO'], 15).setStrzero($post['HPOS_NO'], 8) : '00000000000000000000000',
				'CREATE_TIME'		=>	date("YmdHis")
			);
			$res = D($this->MHpos)->addHpos($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			
			//添加key数据
			$key_data = array(
				'HOST_MAP_ID'		=>	$resdata['HOST_MAP_ID'],						//主机编号
				'HOST_NAME'			=>	'平台',										//中文名称
				'KEY_INDEX'			=>	$resdata['KEY_INDEX'],						//对称密钥索引
				'KEY_MKINDEX'		=>	0,											//保护密钥在加密机索引
				'KEY_KEKINDEX'		=>	0,											//主密钥在加密机索引
				'KEY_KEK'			=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',			
				'KEY_PINKEY'		=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',			
				'KEY_PINVALUE'		=>	'FFFFFFFF',									
				'KEY_MACKEY'		=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',			
				'KEY_MACVALUE'		=>	'FFFFFFFF',									
				'KEY_TRACKKEY'		=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',			
				'KEY_TRACKVALUE'	=>	'FFFFFFFF'									
			);
			if($host_data['KEY_TYPE'] == 1){
				$key_res = D($this->MKey)->addKey($key_data);
				if($key_res['state'] != 0){
					$this->wrong('商户KEY数据添加失败');
				}
			}else{
				$count = D($this->MKey)->countKey("HOST_MAP_ID = '".$post['HOST_MAP_ID']."' and KEY_INDEX='".$resdata['KEY_INDEX']."'");
				if($count == 0){
					$key_res = D($this->MKey)->addKey($key_data);
					if($key_res['state'] != 0){
						$this->wrong('商户KEY数据添加失败');
					}
				}
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}		
		$host_list = D($this->MHost)->getHostlist("HOST_STATUS = 0", 'HOST_MAP_ID,HOST_NAME');
		$this->assign('host_list',			$host_list);		//通道列表
		$this->display();
	}
	/*
	* 通道报备终端 查看
	**/
	public function hpos_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MHpos)->findHpos("HPOS_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//通道数据
		$hostdata = D($this->MHost)->findHost("HOST_MAP_ID='".$info['HOST_MAP_ID']."'", 'HOST_NAME');
		$info['HOST_NAME'] = $hostdata['HOST_NAME'];
		//通道商户
		$hshopdata = D($this->MHshop)->findHshop("HOST_MAP_ID='".$info['HOST_MAP_ID']."' and HSHOP_NO='".$info['HSHOP_NO']."'", 'HSHOP_NAME');
		$info['HSHOP_NAME'] = $hshopdata['HSHOP_NAME'];
		$this->assign('info', 			$info);
		$this->assign('login_flag',		C('LOGIN_FLAG'));	//签到标志
		$this->display('hpos_show');
	}
	/*
	* 通道报备终端 修改
	**/
	public function hpos_edit() {
		$post = I('post');
		if($post['submit'] == "hpos_edit") {
			//验证
			if(empty($post['HSHOP_NO']) || empty($post['HPOS_NO']) || empty($post['HPOS_BATCH']) || empty($post['HPOS_TRACE'])){
				$this->wrong("缺少必填项数据！");
			}
			//检查商户是否存在
			$hshopdata = D($this->MHshop)->findHshop("HOST_MAP_ID='".$post['HOST_MAP_ID']."' and HSHOP_NO='".$post['HSHOP_NO']."'");
			if(empty($hshopdata)){
				$this->wrong("该通道下的商户编号不存在！");
			}
			//检查
			$finddata = D($this->MHpos)->findHpos("HPOS_ID!='".$post['HPOS_ID']."' and HOST_MAP_ID='".$post['HOST_MAP_ID']."' and HSHOP_NO='".$post['HSHOP_NO']."' and HPOS_NO='".$post['HPOS_NO']."'");
			if(!empty($finddata)){
				$this->wrong("该通道下的商户编号和终端号已经存在！");
			}
			
			//获取秘钥体系
			$host_data = D($this->MHost)->findHost("HOST_MAP_ID = '".$post['HOST_MAP_ID']."'", 'KEY_TYPE');
			//组装数据
			$resdata = array(
				'HOST_MAP_ID'		=>	$post['HOST_MAP_ID'],
				'HSHOP_NO'			=>	$post['HSHOP_NO'],
				'HPOS_NO'			=>	$post['HPOS_NO'],
				'HPOS_BATCH'		=>	$post['HPOS_BATCH'],
				'HPOS_TRACE'		=>	$post['HPOS_TRACE'],
				'KEY_INDEX'			=>	$host_data['KEY_TYPE']==1 ? setStrzero($post['HSHOP_NO'], 15).setStrzero($post['HPOS_NO'], 8) : '00000000000000000000000',
			);
			$res = D($this->MHpos)->updateHpos("HPOS_ID='".$post['HPOS_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			if($host_data['KEY_TYPE'] == 1){
				//检查是否修改 key
				if($post['KEY_INDEX'] != $resdata['KEY_INDEX']){
					$keydata = D($this->MKey)->findKey("HOST_MAP_ID = '".$post['HOST_MAP_ID']."' and KEY_INDEX='".$post['KEY_INDEX']."'");
					if(!empty($keydata)) {
						$key_res = D($this->MKey)->updateKey("KEY_ID = '".$keydata['KEY_ID']."'", array('KEY_INDEX'=>$resdata['KEY_INDEX']));	
						if($key_res['state'] != 0){
							$this->wrong('商户KEY数据添加失败');
						}
					}
				}
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MHpos)->findHpos("HPOS_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$hshopdata = D($this->MHshop)->findHshop("HOST_MAP_ID='".$info['HOST_MAP_ID']."' and HSHOP_NO='".$info['HSHOP_NO']."'", 'HSHOP_NAME');
		$info['HSHOP_NAME'] = $hshopdata['HSHOP_NAME'];
		
		$host_list = D($this->MHost)->getHostlist("HOST_STATUS = 0", 'HOST_MAP_ID,HOST_NAME');
		$this->assign('host_list',			$host_list);		//通道列表
		$this->assign('login_flag',			C('LOGIN_FLAG'));	//签到标志
		$this->assign('info', 				$info);				//详情
		$this->display('hpos_add');
	}
	/*
	* 通道报备终端 手动签到
	**/
	public function hpos_login() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MHpos)->findHpos("HPOS_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['LOGIN_FLAG'] == 1){
			$this->wrong("该状态已经签退！");
		}
		$res = D($this->MHpos)->updateHpos("HPOS_ID='".$id."'", array('LOGIN_FLAG'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('签到成功！', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 通道报备终端 导入
	**/
	public function hpos_inport() {
		$this->wrong('别催，这个功能先放放！');
	}
}