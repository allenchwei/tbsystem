<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
import('Vendor.Common.Tree');
// +----------------------------------------------------------------------
// | @gzy  公共管理
// +----------------------------------------------------------------------
class CommonController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->MSecurity 	= 'MSecurity';
		$this->MBranch		= 'MBranch';
		$this->MHost 		= 'MHost';
		$this->MBid			= 'MBid';
		$this->MBank 		= 'MBank';
		$this->MCity		= 'MCity';
		$this->MBbact 		= 'MBbact';
		$this->MNotice	 	= 'MNotice';
		$this->MSmsmodel	= 'MSmsmodel';
		$this->MDkco		= 'MDkco';
		$this->TSmsls 		= 'TSmsls';
		$this->MSrba 		= 'MSrba';
		$this->GVip 		= 'GVip';
		$this->MPartner 	= 'MPartner';
		$this->MUser 		= 'MUser';
		$this->MShop 		= 'MShop';
		$this->MChannel 	= 'MChannel';
		$this->MCcls 		= 'MCcls';
	}
	
	/*
	* 分公司管理
	**/
	public function branch() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "branch"){
			//如果是超级管理员，查看所有
			/*if(C('SPECIAL_USER') == $home['USER_ID']){
				$where = "BRANCH_MAP_ID !=''";
			}else{
				$where = "BRANCH_MAP_ID = '".$home['BRANCH_MAP_ID']."'";
			}		*/	
			
			$where = "1=1";
			//分支级别
			if($post['BRANCH_LEVEL'] !='') {
				$where .= " and BRANCH_LEVEL = '".$post['BRANCH_LEVEL']."'";
			}
			//分支名称
			if($post['BRANCH_NAME']) {
				$where .= " and BRANCH_NAME like '%".$post['BRANCH_NAME']."%'";
			}
			
			//分页
			$count = D($this->MBranch)->countBranch($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MBranch)->getBranchlist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('branch_level',		C('BRANCH_LEVEL'));	//分支机构级别
		$this->assign('branch_status',		C('BRANCH_STATUS'));//分支机构状态
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 分公司管理 添加
	**/
	public function branch_add() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "branch_add") {
			$post['BANK_NAME']    = $_REQUEST['org_BANK_NAME'];
			$post['BANKACCT_BID'] = $_REQUEST['org_BANKACCT_BID'];
			//验证
			if(empty($post['BRANCH_NAME']) || empty($post['BRANCH_NAMEAB']) || empty($post['CITY_NO']) || empty($post['ADDRESS']) || empty($post['ZIP']) || empty($post['MANAGER']) || empty($post['MOBILE'])){
				$this->wrong("缺少必填项数据！");
			}
			//总部没有账户，无需验资
			if($post['BRANCH_LEVEL'] != 0 ){
				if(empty($post['BANKACCT_NAME']) || empty($post['BANKACCT_NO']) || empty($post['BANK_NAME']) || empty($post['BANKACCT_BID'])){
					$this->wrong("缺少必填项数据！");
				}
			}
			//组装数据
			$resdata = array(
				'BRANCH_LEVEL'		=>	1,
				'BRANCH_MAP_ID_P'	=>	100000,
				'BRANCH_NAME'		=>	$post['BRANCH_NAME'],
				'BRANCH_NAMEAB'		=>	$post['BRANCH_NAMEAB'],
				'BRANCH_STATUS'		=>	$post['BRANCH_STATUS'],
				'HOST_MAP_ID'		=>	$post['HOST_MAP_ID'],
				'HOST_MAP_ID_NEW'	=>	0,
				'CITY_NO'			=>	$post['CITY_NO'],
				'ADDRESS'			=>	$post['ADDRESS'],
				'ZIP'				=>	$post['ZIP'],
				'MANAGER'			=>	$post['MANAGER'],
				'MOBILE'			=>	$post['MOBILE'],
				'TEL'				=>	$post['TEL'],
				'FAX'				=>	$post['FAX'],
				'SECURITY_MAP_ID'	=>	''
			);
			$res = D($this->MBranch)->addBranch($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//账户数据
			$bbactdata = array(
				'BRANCH_MAP_ID'		=>	$res['BRANCH_MAP_ID'],
				'BANKACCT_NAME'		=>	$post['BANKACCT_NAME'],
				'BANKACCT_NO'		=>	$post['BANKACCT_NO'],
				'BANKACCT_BID'		=>	$post['BANKACCT_BID'],
				'BANK_NAME'			=>	$post['BANK_NAME'],
				'BANK_FLAG'			=>	0,
			);			
			$resb = D($this->MBbact)->addBbact($bbactdata);
			if($resb['state'] != 0){
				$this->wrong($resb['msg']);
			}
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//通道列表
		$host_list 	   = D($this->MHost)->getHostlist("HOST_STATUS = 0", 'HOST_MAP_ID,HOST_NAME');
		$this->assign('host_list',			$host_list);
		//保险公司
		//$security_list = D($this->MSecurity)->getSecuritylist('SECURITY_STATUS != 1','SECURITY_MAP_ID,SECURITY_NAME');
		//$this->assign('security_list',		$security_list);
		$this->assign('branch_level',		C('BRANCH_LEVEL'));	//分支机构级别
		$this->assign('branch_status',		C('BRANCH_STATUS'));//分支机构状态
		$this->display();
	}
	/*
	* 分公司管理 查看
	**/
	public function branch_show() {		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MBranch)->findBranch("BRANCH_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$bbactdata = D($this->MBbact)->findBbact("BRANCH_MAP_ID='".$id."'");
		$info['BANKACCT_NAME'] = $bbactdata['BANKACCT_NAME'];
		$info['BANKACCT_NO']   = $bbactdata['BANKACCT_NO'];
		$info['BANKACCT_BID']  = $bbactdata['BANKACCT_BID'];
		$info['BANK_NAME'] 	   = $bbactdata['BANK_NAME'];		
		
		//通道列表
		$host_data = D($this->MHost)->findHost("HOST_MAP_ID = '".$info['HOST_MAP_ID']."'", 'HOST_NAME');
		$info['HOST_NAME'] = $host_data['HOST_NAME'];
		//保险公司
		//$security_data = D($this->MSecurity)->findSecurity("SECURITY_MAP_ID = '".$info['SECURITY_MAP_ID']."'", 'SECURITY_NAME');
		//$info['SECURITY_NAME'] = $security_data['SECURITY_NAME'];
		$this->assign('branch_level',		C('BRANCH_LEVEL'));	//分支机构级别
		$this->assign('branch_status',		C('BRANCH_STATUS'));//分支机构状态
		$this->assign ('info', 				$info);
		$this->display();
	}
	/*
	* 分公司管理 修改
	**/
	public function branch_edit() {
		$post = I('post');
		if($post['submit'] == "branch_edit") {
			$post['BANK_NAME']    = $_REQUEST['org_BANK_NAME'];
			$post['BANKACCT_BID'] = $_REQUEST['org_BANKACCT_BID'];
			//验证
			if(empty($post['BRANCH_NAME']) || empty($post['BRANCH_NAMEAB']) || empty($post['CITY_NO']) || empty($post['ADDRESS']) || empty($post['ZIP']) || empty($post['MANAGER']) || empty($post['MOBILE'])){
				$this->wrong("缺少必填项数据！");
			}
			//总部没有账户，无需验资
			if($post['BRANCH_LEVEL'] != 0 ){
				if(empty($post['BANKACCT_NAME']) || empty($post['BANKACCT_NO']) || empty($post['BANK_NAME']) || empty($post['BANKACCT_BID'])){
					$this->wrong("缺少必填项数据！");
				}
			}
			//组装数据
			$resdata = array(
				'BRANCH_NAME'		=>	$post['BRANCH_NAME'],
				'BRANCH_NAMEAB'		=>	$post['BRANCH_NAMEAB'],
				'BRANCH_STATUS'		=>	$post['BRANCH_STATUS'],
				'HOST_MAP_ID'		=>	$post['HOST_MAP_ID'],
				'CITY_NO'			=>	$post['CITY_NO'],
				'ADDRESS'			=>	$post['ADDRESS'],
				'ZIP'				=>	$post['ZIP'],
				'MANAGER'			=>	$post['MANAGER'],
				'MOBILE'			=>	$post['MOBILE'],
				'TEL'				=>	$post['TEL'],
				'FAX'				=>	$post['FAX']
			);
			$res = D($this->MBranch)->updateBranch("BRANCH_MAP_ID='".$post['BRANCH_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//账户数据
			$bbactdata = array(
				'BANKACCT_NAME'		=>	$post['BANKACCT_NAME'],
				'BANKACCT_NO'		=>	$post['BANKACCT_NO'],
				'BANKACCT_BID'		=>	$post['BANKACCT_BID'],
				'BANK_NAME'			=>	$post['BANK_NAME']
			);			
			$resb = D($this->MBbact)->updateBbact("BRANCH_MAP_ID='".$post['BRANCH_MAP_ID']."'", $bbactdata);
			if($resb['state'] != 0){
				$this->wrong($resb['msg']);
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MBranch)->findBranch("BRANCH_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$bbactdata = D($this->MBbact)->findBbact("BRANCH_MAP_ID='".$id."'");
		$info['BANKACCT_NAME'] = $bbactdata['BANKACCT_NAME'];
		$info['BANKACCT_NO']   = $bbactdata['BANKACCT_NO'];
		$info['BANKACCT_BID']  = $bbactdata['BANKACCT_BID'];
		$info['BANK_NAME'] 	   = $bbactdata['BANK_NAME'];		
		
		//通道列表
		$host_list 	   = D($this->MHost)->getHostlist("HOST_STATUS = 0", 'HOST_MAP_ID,HOST_NAME');
		$this->assign('host_list',			$host_list);
		//保险公司
		//$security_list = D($this->MSecurity)->getSecuritylist('SECURITY_STATUS != 1','SECURITY_MAP_ID,SECURITY_NAME');
		//$this->assign('security_list',		$security_list);
		$this->assign('branch_level',		C('BRANCH_LEVEL'));	//分支机构级别
		$this->assign('branch_status',		C('BRANCH_STATUS'));//分支机构状态
		$this->assign ('info', 				$info);
		$this->display('branch_add');
	}
	/*
	* 分公司管理 关闭
	**/
	public function branch_close() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$info = D($this->MBranch)->findBranch("BRANCH_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['BRANCH_STATUS'] == 1){
			$this->wrong("当前状态下不允许暂停业务操作！");
		}
		$res = D($this->MBranch)->updateBranch("BRANCH_MAP_ID='".$id."'", array('BRANCH_STATUS'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg']);
	}
	/*
	* 分公司管理 开启
	**/
	public function branch_open() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$info = D($this->MBranch)->findBranch("BRANCH_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['BRANCH_STATUS'] == 0){
			$this->wrong("当前状态下不允许恢复开通操作！");
		}
		$res = D($this->MBranch)->updateBranch("BRANCH_MAP_ID='".$id."'", array('BRANCH_STATUS'=> 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg']);
	}
	
	
	
	/*
	* 代扣公司管理
	**/
	public function dkco() {
		$post = I('post');
		if($post['submit'] == "dkco"){
			$where = "DKCO_STATUS = 0";
			//代扣公司名称
			if($post['DKCO_NAME']) {
				$where .= " and DKCO_NAME like '%".$post['DKCO_NAME']."%'";
			}
			//状态
			if($post['DKCO_STATUS'] != '') {
				$where .= " and DKCO_STATUS = '".$post['DKCO_STATUS']."'";
			}
			//分页
			$count = D($this->MDkco)->countDkco($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MDkco)->getDkcolist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('dkco_fee_flag',		C('DKCO_FEE_FLAG'));	//代扣公司收费标准
		$this->assign('dkco_status',		C('DKCO_STATUS'));		//代扣公司状态
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 代扣公司管理 添加
	**/
	public function dkco_add() {
		$post = I('post');
		if($post['submit'] == "dkco_add") {
			//验证
			if(empty($post['DKCO_NAME']) || empty($post['CITY_NO']) || empty($post['ADDRESS']) || empty($post['ZIP']) || empty($post['MANAGER']) || empty($post['MOBILE']) || empty($post['DKCO_DK_T']) || empty($post['DKCO_DK_LOWAMT'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'DKCO_NAME'			=>	$post['DKCO_NAME'],
				'DKCO_STATUS'		=>	$post['DKCO_STATUS'],
				'DKCO_FEE_FLAG'		=>	$post['DKCO_FEE_FLAG'],
				'DKCO_FEE_FIX'		=>	$post['DKCO_FEE_FLAG'] == 0 ? setMoney($post['DKCO_FEE_FIX'], '2') : '',
				'DKCO_FEE_PER'		=>	$post['DKCO_FEE_PER'],
				'DKCO_DK_T'			=>	$post['DKCO_DK_T'],
				'DKCO_DK_WD'		=>	$post['DKCO_DK_WD'],
				'DKCO_DK_LOWAMT'	=>	setMoney($post['DKCO_DK_LOWAMT'], '2'),
				'CITY_NO'			=>	$post['CITY_NO'],
				'ADDRESS'			=>	$post['ADDRESS'],
				'ZIP'				=>	$post['ZIP'],
				'MANAGER'			=>	$post['MANAGER'],
				'MOBILE'			=>	$post['MOBILE'],
				'TEL'				=>	$post['TEL'],
				'FAX'				=>	$post['FAX']
			);
			$res = D($this->MDkco)->addDkco($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign('dkco_fee_flag',		C('DKCO_FEE_FLAG'));	//代扣公司收费标准
		$this->assign('dkco_status',		C('DKCO_STATUS'));		//代扣公司状态
		$this->assign('dkco_dk_wd',			C('DKCO_DK_WD'));		//代扣公司工作日
		$this->display();
	}
	/*
	* 代扣公司管理 详情
	**/
	public function dkco_show() {		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MDkco)->findDkco("DKCO_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('dkco_fee_flag',		C('DKCO_FEE_FLAG'));	//代扣公司收费标准
		$this->assign('dkco_status',		C('DKCO_STATUS'));		//代扣公司状态
		$this->assign('dkco_dk_wd',			C('DKCO_DK_WD'));		//代扣公司工作日
		$this->assign('info',				$info);					//代扣公司工作日
		$this->display();
	}
	/*
	* 代扣公司管理 修改
	**/
	public function dkco_edit() {		
		$post = I('post');
		if($post['submit'] == "dkco_edit") {
			//验证
			if(empty($post['DKCO_NAME']) || empty($post['CITY_NO']) || empty($post['ADDRESS']) || empty($post['ZIP']) || empty($post['MANAGER']) || empty($post['MOBILE']) || empty($post['DKCO_DK_T']) || empty($post['DKCO_DK_LOWAMT'])){
				$this->wrong("缺少必填项数据！");
			}
			if(($post['DKCO_FEE_FLAG']==0 && empty($post['DKCO_FEE_FIX'])) || ($post['DKCO_FEE_FLAG']==1 && empty($post['DKCO_FEE_PER']))) {
				$this->wrong("请输入收费标准！");
			}
			//组装数据
			$resdata = array(
				'DKCO_NAME'			=>	$post['DKCO_NAME'],
				'DKCO_STATUS'		=>	$post['DKCO_STATUS'],
				'DKCO_FEE_FLAG'		=>	$post['DKCO_FEE_FLAG'],
				'DKCO_FEE_FIX'		=>	$post['DKCO_FEE_FLAG'] == 0 ? setMoney($post['DKCO_FEE_FIX'], '2') : '',
				'DKCO_FEE_PER'		=>	$post['DKCO_FEE_PER'],
				'DKCO_DK_T'			=>	$post['DKCO_DK_T'],
				'DKCO_DK_WD'		=>	$post['DKCO_DK_WD'],
				'DKCO_DK_LOWAMT'	=>	setMoney($post['DKCO_DK_LOWAMT'], '2'),
				'CITY_NO'			=>	$post['CITY_NO'],
				'ADDRESS'			=>	$post['ADDRESS'],
				'ZIP'				=>	$post['ZIP'],
				'MANAGER'			=>	$post['MANAGER'],
				'MOBILE'			=>	$post['MOBILE'],
				'TEL'				=>	$post['TEL'],
				'FAX'				=>	$post['FAX']
			);
			$res = D($this->MDkco)->updateDkco("DKCO_MAP_ID='".$post['DKCO_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MDkco)->findDkco("DKCO_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('dkco_fee_flag',		C('DKCO_FEE_FLAG'));	//代扣公司收费标准
		$this->assign('dkco_status',		C('DKCO_STATUS'));		//代扣公司状态
		$this->assign('dkco_dk_wd',			C('DKCO_DK_WD'));		//代扣公司工作日
		$this->assign('info',				$info);					//代扣公司工作日
		$this->display('dkco_add');
	}	
	
	
	
	/*
	* 保险公司
	**/
	public function security() {
		$post = I('post');
		if($post['submit'] == "security"){
			$where = "1=1";			
			//在线标志
			if($post['ONLINE_FLAG']) {
				$where .= " and ONLINE_FLAG = '".$post['ONLINE_FLAG']."'";
			}
			//状态
			if($post['SECURITY_STATUS']) {
				$where .= " and SECURITY_STATUS = '".$post['SECURITY_STATUS']."'";
			}
			//公司名称
			if($post['SECURITY_NAME']) {
				$where .= " and SECURITY_NAME like '%".$post['SECURITY_NAME']."%'";
			}
			//承保险种	需处理
			$type_count = 0;
			$twhere		= "SRBA_ID != ''";
			if($post['SECURITY_TYPE']) {
				$type_list  = str_split($post['SECURITY_TYPE']);
				$type_count = count($type_list);
				foreach($type_list as $key=>$val){
					if($key == 0){
						$str  = " SECURITY_TYPE = '".$val."'";
					}else{
						$str .= " or SECURITY_TYPE = '".$val."'";
					}
				}
				$twhere .= " and (".$str.")";
			}

			$slist = D($this->MSecurity)->getSecuritylist($where);
			foreach($slist as $val){
				$rlist = D($this->MSrba)->getSrbalist($twhere." and SECURITY_MAP_ID='".$val['SECURITY_MAP_ID']."'", 'SECURITY_TYPENAME');
				foreach($rlist as $key2=>$val2){
					if($key2 == 0){
						$title  = $val2['SECURITY_TYPENAME'];
					}else{
						$title .= '，'.$val2['SECURITY_TYPENAME'];
					}
				}
				$val['SECURITY_TYPE_STR'] = $title;					
				if($type_count > 0 && ($type_count == count($rlist))){		
					$list1[] = $val;
				}else{
					$list2[] = $val;
				}
			}
			$list = $type_count>0 ? $list1 : $list2;		
			
			//分页参数
			$this->assign ( 'totalCount', 	count($list) );
	       	$this->assign ( 'numPerPage', 	50 );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('security_type',		C('SECURITY_TYPE'));	//险种类型
		$this->assign('online_flag',		C('ONLINE_FLAG'));		//在线标志
		$this->assign('security_status',	C('SECURITY_STATUS'));	//在线状态
		\Cookie::set ('_currentUrl_', 		__SELF__);		
		$this->display();
	}
	/*
	* 保险公司 添加
	**/
	public function security_add() {
		$post = I('post');
		if($post['submit'] == "security_add") {
			//验证
			if(empty($post['SECURITY_NAME']) || empty($post['CITY_NO']) || empty($post['ADDRESS']) || empty($post['ZIP']) || empty($post['MANAGER']) || empty($post['MOBILE'])){
				$this->wrong("缺少必填项数据！");
			}			
			if(($post['AGE_BEGIN'] > $post['AGE_END']) || ($post['AGE_BEGIN2'] > $post['AGE_END2'])){
				$this->wrong("最小年龄必须小于最大年龄！");
			}
			//组装数据
			$resdata = array(
				'SECURITY_LEVEL'		=>	0,
				'SECURITY_MAP_ID_P'		=>	0,
				'SECURITY_NAME'			=>	$post['SECURITY_NAME'],
				'SECURITY_STATUS'		=>	$post['SECURITY_STATUS'],
				'CITY_NO'				=>	$post['CITY_NO'],
				'ADDRESS'				=>	$post['ADDRESS'],
				'ZIP'					=>	$post['ZIP'],
				'MANAGER'				=>	$post['MANAGER'],
				'MOBILE'				=>	$post['MOBILE'],
				'TEL'					=>	$post['TEL'],
				'FAX'					=>	$post['FAX'],
			);
			$res = D($this->MSecurity)->addSecurity($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}			
			
			//入险种库
			if($post['SECURITY_TYPE'] == 12){
				//组装数据
				$seldata = array(					
					array(
						'SECURITY_MAP_ID'		=>	$res['SECURITY_MAP_ID'],
						'SECURITY_TYPE'			=>	2,
						'SECURITY_TYPENAME'		=>	C('SECURITY_TYPE')[2],
						'AGE_BEGIN'				=>	$post['AGE_BEGIN2'],
						'AGE_END'				=>	$post['AGE_END2'],
						'SECURITY_AMT'			=>	setMoney($post['SECURITY_AMT2'], '2'),
						'SECURITY_LOWAMT'		=>	setMoney($post['SECURITY_LOWAMT2'], '2'),
						'SECURITY_MAXAMT'		=>	setMoney($post['SECURITY_MAXAMT2'], '2'),
						'ONLINE_FLAG'			=>	$post['ONLINE_FLAG2']
					),
					array(
						'SECURITY_MAP_ID'		=>	$res['SECURITY_MAP_ID'],
						'SECURITY_TYPE'			=>	1,
						'SECURITY_TYPENAME'		=>	C('SECURITY_TYPE')[1],
						'AGE_BEGIN'				=>	$post['AGE_BEGIN'],
						'AGE_END'				=>	$post['AGE_END'],
						'SECURITY_AMT'			=>	setMoney($post['SECURITY_AMT'], '2'),
						'SECURITY_LOWAMT'		=>	setMoney($post['SECURITY_LOWAMT'], '2'),
						'SECURITY_MAXAMT'		=>	setMoney($post['SECURITY_MAXAMT'], '2'),
						'ONLINE_FLAG'			=>	$post['ONLINE_FLAG']
					)
				);
				$res = D($this->MSrba)->addAllSrba($seldata);
				if($res['state'] != 0){
					$this->wrong($res['msg']);
				}
			}else{
				//组装数据
				$seldata = array(
					'SECURITY_MAP_ID'		=>	$res['SECURITY_MAP_ID'],
					'SECURITY_TYPE'			=>	$post['SECURITY_TYPE'],
					'SECURITY_TYPENAME'		=>	C('SECURITY_TYPE')[$post['SECURITY_TYPE']],
					'AGE_BEGIN'				=>	$post['SECURITY_TYPE'] == 2 ? $post['AGE_BEGIN2'] : $post['AGE_BEGIN'],
					'AGE_END'				=>	$post['SECURITY_TYPE'] == 2 ? $post['AGE_END2'] : $post['AGE_END'],
					'SECURITY_AMT'			=>	$post['SECURITY_TYPE'] == 2 ? setMoney($post['SECURITY_AMT2'], '2') : setMoney($post['SECURITY_AMT'], '2'),
					'SECURITY_LOWAMT'		=>	$post['SECURITY_TYPE'] == 2 ? setMoney($post['SECURITY_LOWAMT2'], '2') : setMoney($post['SECURITY_LOWAMT'], '2'),
					'SECURITY_MAXAMT'		=>	$post['SECURITY_TYPE'] == 2 ? setMoney($post['SECURITY_MAXAMT2'], '2') : setMoney($post['SECURITY_MAXAMT'], '2'),
					'ONLINE_FLAG'			=>	$post['SECURITY_TYPE'] == 2 ? $post['ONLINE_FLAG2'] : $post['ONLINE_FLAG'],
				);
				$res = D($this->MSrba)->addSrba($seldata);
				if($res['state'] != 0){
					$this->wrong($res['msg']);
				}
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign('security_type',		C('SECURITY_TYPE'));	//险种类型
		$this->assign('online_flag',		C('ONLINE_FLAG'));		//在线标志
		$this->assign('security_status',	C('SECURITY_STATUS'));	//在线状态
		$this->display();
	}
	/*
	* 保险公司 查看
	**/
	public function security_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MSecurity)->findSecurity("SECURITY_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$srba_info  = D($this->MSrba)->findSrba("SECURITY_MAP_ID='".$info['SECURITY_MAP_ID']."' and SECURITY_TYPE=1");
		$srba_info2 = D($this->MSrba)->findSrba("SECURITY_MAP_ID='".$info['SECURITY_MAP_ID']."' and SECURITY_TYPE=2");
		//险种selected
		if($srba_info['SECURITY_TYPE'] && $srba_info2['SECURITY_TYPE']){
			$type_num = 12;
		}else{
			$type_num = $srba_info['SECURITY_TYPE'] ? 1 : 2;
		}	
		
		$this->assign('security_type',		C('SECURITY_TYPE'));	//险种类型
		$this->assign('online_flag',		C('ONLINE_FLAG'));		//在线标志
		$this->assign('security_status',	C('SECURITY_STATUS'));	//在线状态
		$this->assign ('info', 				$info);
		$this->assign ('srba_info', 		$srba_info);
		$this->assign ('srba_info2', 		$srba_info2);
		$this->assign ('type_num', 			$type_num);
		$this->display();
	}
	/*
	* 保险公司 修改
	**/
	public function security_edit() {
		$post = I('post');
		if($post['submit'] == "security_edit") {
			//验证
			if(empty($post['SECURITY_NAME']) || empty($post['CITY_NO']) || empty($post['ADDRESS']) || empty($post['ZIP']) || empty($post['MANAGER']) || empty($post['MOBILE'])){
				$this->wrong("缺少必填项数据！");
			}
			if(($post['AGE_BEGIN'] > $post['AGE_END']) || ($post['AGE_BEGIN2'] > $post['AGE_END2'])){
				$this->wrong("最小年龄必须小于最大年龄！");
			}
			//组装数据
			$resdata = array(
				'SECURITY_LEVEL'		=>	0,
				'SECURITY_MAP_ID_P'		=>	0,
				'SECURITY_NAME'			=>	$post['SECURITY_NAME'],
				'SECURITY_STATUS'		=>	$post['SECURITY_STATUS'],
				'CITY_NO'				=>	$post['CITY_NO'],
				'ADDRESS'				=>	$post['ADDRESS'],
				'ZIP'					=>	$post['ZIP'],
				'MANAGER'				=>	$post['MANAGER'],
				'MOBILE'				=>	$post['MOBILE'],
				'TEL'					=>	$post['TEL'],
				'FAX'					=>	$post['FAX'],
			);
			$res = D($this->MSecurity)->updateSecurity("SECURITY_MAP_ID = '".$post['SECURITY_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			
			//入险种库
			if($post['SECURITY_TYPE'] == 12){
				if($post['SRBA_ID2']){
					$seldata2 = array(
						'AGE_BEGIN'				=>	$post['AGE_BEGIN2'],
						'AGE_END'				=>	$post['AGE_END2'],
						'SECURITY_AMT'			=>	setMoney($post['SECURITY_AMT2'], '2'),
						'SECURITY_LOWAMT'		=>	setMoney($post['SECURITY_LOWAMT2'], '2'),
						'SECURITY_MAXAMT'		=>	setMoney($post['SECURITY_MAXAMT2'], '2'),
						'ONLINE_FLAG'			=>	$post['ONLINE_FLAG2']
					);
					$res2 = D($this->MSrba)->updateSrba("SRBA_ID='".$post['SRBA_ID2']."'", $seldata2);
					if($res2['state'] != 0){
						$this->wrong($res2['msg']);
					}
				}else{
					$seldata2 = array(
						'SECURITY_MAP_ID'		=>	$post['SECURITY_MAP_ID'],
						'SECURITY_TYPE'			=>	2,
						'SECURITY_TYPENAME'		=>	C('SECURITY_TYPE')[2],
						'AGE_BEGIN'				=>	$post['AGE_BEGIN2'],
						'AGE_END'				=>	$post['AGE_END2'],
						'SECURITY_AMT'			=>	setMoney($post['SECURITY_AMT2'], '2'),
						'SECURITY_LOWAMT'		=>	setMoney($post['SECURITY_LOWAMT2'], '2'),
						'SECURITY_MAXAMT'		=>	setMoney($post['SECURITY_MAXAMT2'], '2'),
						'ONLINE_FLAG'			=>	$post['ONLINE_FLAG2']
					);
					$res2 = D($this->MSrba)->addSrba($seldata2);
					if($res2['state'] != 0){
						$this->wrong($res2['msg']);
					}
				}
				
				if($post['SRBA_ID']){
					$seldata1 = array(
						'AGE_BEGIN'				=>	$post['AGE_BEGIN'],
						'AGE_END'				=>	$post['AGE_END'],
						'SECURITY_AMT'			=>	setMoney($post['SECURITY_AMT'], '2'),
						'SECURITY_LOWAMT'		=>	setMoney($post['SECURITY_LOWAMT'], '2'),
						'SECURITY_MAXAMT'		=>	setMoney($post['SECURITY_MAXAMT'], '2'),
						'ONLINE_FLAG'			=>	$post['ONLINE_FLAG'],
					);
					$res1 = D($this->MSrba)->updateSrba("SRBA_ID='".$post['SRBA_ID']."'", $seldata1);
					if($res1['state'] != 0){
						$this->wrong($res1['msg']);
					}
				}else{
					$seldata1 = array(
						'SECURITY_MAP_ID'		=>	$post['SECURITY_MAP_ID'],
						'SECURITY_TYPE'			=>	1,
						'SECURITY_TYPENAME'		=>	C('SECURITY_TYPE')[1],
						'AGE_BEGIN'				=>	$post['AGE_BEGIN'],
						'AGE_END'				=>	$post['AGE_END'],
						'SECURITY_AMT'			=>	setMoney($post['SECURITY_AMT'], '2'),
						'SECURITY_LOWAMT'		=>	setMoney($post['SECURITY_LOWAMT'], '2'),
						'SECURITY_MAXAMT'		=>	setMoney($post['SECURITY_MAXAMT'], '2'),
						'ONLINE_FLAG'			=>	$post['ONLINE_FLAG'],
					);
					$res1 = D($this->MSrba)->addSrba($seldata1);
					if($res1['state'] != 0){
						$this->wrong($res1['msg']);
					}
				}
			}else{
				D($this->MSrba)->delSrba("SECURITY_MAP_ID='".$post['SECURITY_MAP_ID']."'");
				//组装数据
				$seldata = array(
					'SECURITY_MAP_ID'		=>	$post['SECURITY_MAP_ID'],
					'SECURITY_TYPE'			=>	$post['SECURITY_TYPE'],
					'SECURITY_TYPENAME'		=>	C('SECURITY_TYPE')[$post['SECURITY_TYPE']],
					'AGE_BEGIN'				=>	$post['SECURITY_TYPE'] == 2 ? $post['AGE_BEGIN2'] : $post['AGE_BEGIN'],
					'AGE_END'				=>	$post['SECURITY_TYPE'] == 2 ? $post['AGE_END2'] : $post['AGE_END'],
					'SECURITY_AMT'			=>	$post['SECURITY_TYPE'] == 2 ? setMoney($post['SECURITY_AMT2'], '2') : setMoney($post['SECURITY_AMT'], '2'),
					'SECURITY_LOWAMT'		=>	$post['SECURITY_TYPE'] == 2 ? setMoney($post['SECURITY_LOWAMT2'], '2') : setMoney($post['SECURITY_LOWAMT'], '2'),
					'SECURITY_MAXAMT'		=>	$post['SECURITY_TYPE'] == 2 ? setMoney($post['SECURITY_MAXAMT2'], '2') : setMoney($post['SECURITY_MAXAMT'], '2'),
					'ONLINE_FLAG'			=>	$post['SECURITY_TYPE'] == 2 ? $post['ONLINE_FLAG2'] : $post['ONLINE_FLAG'],
				);
				$res = D($this->MSrba)->addSrba($seldata);
				if($res['state'] != 0){
					$this->wrong($res['msg']);
				}
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MSecurity)->findSecurity("SECURITY_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$srba_info  = D($this->MSrba)->findSrba("SECURITY_MAP_ID='".$info['SECURITY_MAP_ID']."' and SECURITY_TYPE=1", '*,BANK_NAME as BANK_NAME1,BANKACCT_BID as BANKACCT_BID1');
		$srba_info2 = D($this->MSrba)->findSrba("SECURITY_MAP_ID='".$info['SECURITY_MAP_ID']."' and SECURITY_TYPE=2", '*,BANK_NAME as BANK_NAME2,BANKACCT_BID as BANKACCT_BID2');
				
		//险种selected
		if($srba_info['SECURITY_TYPE'] && $srba_info2['SECURITY_TYPE']){
			$type_num = 12;
		}else{
			$type_num = $srba_info['SECURITY_TYPE'] ? 1 : 2;
		}	
		
		$this->assign('security_type',		C('SECURITY_TYPE'));	//险种类型
		$this->assign('online_flag',		C('ONLINE_FLAG'));		//在线标志
		$this->assign('security_status',	C('SECURITY_STATUS'));	//在线状态
		$this->assign ('info', 				$info);
		$this->assign ('srba_info', 		$srba_info);
		$this->assign ('srba_info2', 		$srba_info2);
		$this->assign ('type_num', 			$type_num);
		$this->display('security_add');
	}
	
	
	
	/*
	* 系统公告管理
	**/
	public function notice() {
		$home = session('HOME');
		$post = I('post');
		$post['NOTICE_TIME_A'] = $post['NOTICE_TIME_A'] ? $post['NOTICE_TIME_A'] : date('Y-m-d',strtotime('-1 week'));
		$post['NOTICE_TIME_B'] = $post['NOTICE_TIME_B'] ? $post['NOTICE_TIME_B'] : date('Y-m-d');
		if($post['submit'] == "notice"){
			$where = "1=1";
			//归属
			$getlevel = filter_data('plv');	//列表查询
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
			}
			if($post['PARTNER_MAP_ID']){
				$pids = get_plv_childs($post['PARTNER_MAP_ID']);
				$where .= " and PARTNER_MAP_ID in (".$pids.")";
			}
				
			//发布日期	开始
			if($post['NOTICE_TIME_A']) {
				$where .= " and NOTICE_TIME >= '".date('Ymd',strtotime($post['NOTICE_TIME_A']))."'";
			}
			//发布日期	结束
			if($post['NOTICE_TIME_B']) {
				$where .= " and NOTICE_TIME <= '".date('Ymd',strtotime($post['NOTICE_TIME_B']))."'";
			}		
			//分页
			$count = D($this->MNotice)->countNotice($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MNotice)->getNoticelist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'list', 		$list );
		}
		$this->assign ( 'postdata', 	$post );
		
		\Cookie::set (	'_currentUrl_', 	__SELF__);
		$this->display();
	}
	/*
	* 系统公告管理 添加
	**/
	public function notice_add() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "notice_add") {
			//验证
			if(empty($post['NOTICE_TIME']) || empty($post['NOTICE_EXP']) || empty($post['NOTICE_TITLE']) || empty($post['NOTICE_DESC'])){
				$this->wrong("缺少必填项数据！");
			}
			$post['NOTICE_TIME'] = date("Ymd",strtotime($post['NOTICE_TIME']));
			$post['NOTICE_EXP']  = date("Ymd",strtotime($post['NOTICE_EXP']));
			if($post['NOTICE_TIME'] > $post['NOTICE_EXP']){
				$this->wrong("请规范选择公告有效日期！");
			}
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'	=>	$home['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'=>	$home['PARTNER_MAP_ID'],
				'PARTNER_LEVEL'	=>	$home['USER_LEVEL'] + 1,
				'NOTICE_TIME'	=>	$post['NOTICE_TIME'],
				'NOTICE_EXP'	=>	$post['NOTICE_EXP'],
				'NOTICE_TITLE'	=>	$post['NOTICE_TITLE'],
				'NOTICE_DESC'	=>	$post['NOTICE_DESC']
			);
			$res = D($this->MNotice)->addNotice($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign('PARTNER_MAP_ID', 	$home['PARTNER_MAP_ID']);
		$this->assign('BRANCH_MAP_ID', 		$home['BRANCH_MAP_ID']);
		$this->display();
	}
	/*
	* 系统公告管理 详情
	**/
	public function notice_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MNotice)->findNotice("NOTICE_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('info', 		$info);
		$this->display('notice_show');
	}
	/*
	* 系统公告管理 修改
	**/
	public function notice_edit() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "notice_edit") {
			//验证
			if(empty($post['NOTICE_TIME']) || empty($post['NOTICE_EXP']) || empty($post['NOTICE_TITLE']) || empty($post['NOTICE_DESC'])){
				$this->wrong("缺少必填项数据！");
			}
			$post['NOTICE_TIME'] = date("Ymd",strtotime($post['NOTICE_TIME']));
			$post['NOTICE_EXP']  = date("Ymd",strtotime($post['NOTICE_EXP']));
			if($post['NOTICE_TIME'] > $post['NOTICE_EXP']){
				$this->wrong("请规范选择公告有效日期！");
			}
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'	=>	$home['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'=>	$home['PARTNER_MAP_ID'],
				'PARTNER_LEVEL'	=>	$home['USER_LEVEL'] + 1,
				'NOTICE_TIME'	=>	$post['NOTICE_TIME'],
				'NOTICE_EXP'	=>	$post['NOTICE_EXP'],
				'NOTICE_TITLE'	=>	$post['NOTICE_TITLE'],
				'NOTICE_DESC'	=>	$post['NOTICE_DESC']
			);
			$res = D($this->MNotice)->updateNotice("NOTICE_ID='".$post['NOTICE_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MNotice)->findNotice("NOTICE_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('info', 			$info);
		$this->assign('PARTNER_MAP_ID', 	$home['PARTNER_MAP_ID']);
		$this->assign('BRANCH_MAP_ID', 		$home['BRANCH_MAP_ID']);
		$this->display('notice_add');
	}
	/*
	* 系统公告管理 删除
	**/
	public function notice_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$res = D($this->MNotice)->delNotice("NOTICE_ID='".$id."'");
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg']);
	}
	
	
	
	/*
	* 短信模板维护
	**/
	public function smstem() {
		$post = I('post');
		if($post['submit'] == "smstem"){
			$where = "1=1";
			//类型
			if($post['SMS_MODEL_TYPE']) {
				$where .= " and SMS_MODEL_TYPE = '".$post['SMS_MODEL_TYPE']."'";
			}
			//分页
			$count = D($this->MSmsmodel)->countSmsmodel($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MSmsmodel)->getSmsmodellist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('sms_model_type',		C('SMS_MODEL_TYPE'));	//短信类型
		$this->assign('sms_model_status',	C('SMS_MODLE_STATUS'));	//短信状态
		\Cookie::set ('_currentUrl_', 		__SELF__);		
		$this->display();
	}
	/*
	* 短信模板维护 添加
	**/
	public function smstem_add() {
		$post = I('post');
		if($post['submit'] == "smstem_add") {
			//验证
			if(empty($post['SMS_MODEL'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'SMS_MODEL_TYPE'	=>	$post['SMS_MODEL_TYPE'],
				'SMS_MODEL_CITY'	=>	$post['FLAG']==1 ? '000000' : $post['SMS_MODEL_CITY'],
				'SMS_MODLE_STATUS'	=>	$post['SMS_MODLE_STATUS'],
				'SMS_MODEL'			=>	$post['SMS_MODEL']
			);
			$res = D($this->MSmsmodel)->addSmsmodel($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign('sms_model_type',		C('SMS_MODEL_TYPE'));	//短信类型
		$this->assign('sms_model_status',	C('SMS_MODLE_STATUS'));	//短信状态
		$this->display();
	}
	/*
	* 短信模板维护 详情
	**/
	public function smstem_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MSmsmodel)->findSmsmodel("SMS_MODLE_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('sms_model_type',		C('SMS_MODEL_TYPE'));	//短信类型
		$this->assign('sms_model_status',	C('SMS_MODLE_STATUS'));	//短信状态
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 短信模板维护 修改
	**/
	public function smstem_edit() {
		$post = I('post');
		if($post['submit'] == "smstem_edit") {
			//验证
			if(empty($post['SMS_MODEL'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'SMS_MODEL_TYPE'	=>	$post['SMS_MODEL_TYPE'],
				'SMS_MODEL_CITY'	=>	$post['FLAG']==1 ? '000000' : $post['SMS_MODEL_CITY'],
				'SMS_MODLE_STATUS'	=>	$post['SMS_MODLE_STATUS'],
				'SMS_MODEL'			=>	$post['SMS_MODEL']
			);
			$res = D($this->MSmsmodel)->updateSmsmodel("SMS_MODLE_ID='".$post['SMS_MODLE_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MSmsmodel)->findSmsmodel("SMS_MODLE_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('sms_model_type',		C('SMS_MODEL_TYPE'));	//短信类型
		$this->assign('sms_model_status',	C('SMS_MODLE_STATUS'));	//短信状态
		$this->assign('info', 				$info);
		$this->display('smstem_add');
	}
	/*
	* 短信模板维护 删除
	**/
	public function smstem_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$res = D($this->MSmsmodel)->delSmsmodel("SMS_MODLE_ID='".$id."'");
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg']);
	}
	/*
	* 短信模板维护 群发短信
	**/
	public function smstem_sends() {
		$post = I('post');
		if($post['submit'] == "smstem_sends") {
			//1会员 2合作伙伴 3登录用户 4分公司 5商户    4分公司对应 一级
			$getlevel = get_level_val('plv');
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'] ? $getlevel['pid'] : 0;
			if(empty($post['BRANCH_MAP_ID'])) {
				$this->wrong("请选择归属！");
			}
			if($post['flag'] != 4){
				if(empty($post['PARTNER_MAP_ID'])) {
					$this->wrong("请选择归属! ");
				}
				$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);	//包含本身
			}
			switch($post['flag']){
				case 1:
					$where = "v.BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."' and v.PARTNER_MAP_ID in (".$pids.")";
					$list  = D($this->GVip)->getViplist($where, 'VIP_MOBILE as MOBILE,VIP_NAME as NAME');					
					break;
				case 2:
					$where = "a.BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."' and a.PARTNER_MAP_ID in (".$pids.")";
					$list  = D($this->MPartner)->getPartnerlist($where, 'a.MOBILE,a.MANAGER as NAME');					
					break;
				case 3:
					$where = "u.ROLE_ID != 1 and u.BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."' and u.PARTNER_MAP_ID in (".$pids.")";
					$list = D($this->MUser)->getUserlist($where, 'u.USER_MOBILE as MOBILE,u.USER_NAME as NAME');					
					break;
				case 4:
					$where = "BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
					$list = D($this->MBranch)->getBranchlist($where, 'MOBILE,MANAGER as NAME');					
					break;
				case 5:
					$where = "s.BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."' and s.PARTNER_MAP_ID in (".$pids.")";
					$list = D($this->MShop)->getShoplist($where, 's.MOBILE,s.SHOP_NAME as NAME');					
					break;
			}
			if(empty($list)){
				$this->wrong("该条件下无数据，无法发送! ");
			}
					
			//短信模板	为了安全，再查下吧
			$finddata = D($this->MSmsmodel)->findSmsmodel("SMS_MODLE_ID='".$post['SMS_MODLE_ID']."'");			
			if(empty($finddata)){
				$this->wrong("参数数据出错！");
			}
			
			$ls = array();
			foreach($list as $val){
				$ls[] = array(
					'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],
					'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],
					'SMS_MODEL_TYPE'	=>	$finddata['SMS_MODEL_TYPE'],
					'VIP_FLAG'			=>	'0',
					'VIP_ID'			=>	'0',
					'VIP_CARDNO'		=>	'-',
					'SMS_RECV_MOB'		=>	$val['MOBILE'],
					'SMS_RECV_NAME'		=>	$val['NAME'],
					'SMS_TEXT'			=>	$finddata['SMS_MODEL'],
					'SMS_STATUS'		=>	'2',
					'SMS_DATE'			=>	date('Ymd'),
					'SMS_TIME'			=>	date('His'),
					'SMS_MODEL_ID'		=>	$finddata['SMS_MODLE_ID'],
					'SMS_MUL_BATCH'		=>	'0',
					'SMS_RESP_ID'		=>	'0',
				);		
			}
			$res = D($this->TSmsls)->addAllSmsls($ls);
			if($res['state'] != 0){
				$this->wrong('发送失败！');
			}
			$this->right('发送成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MSmsmodel)->findSmsmodel("SMS_MODLE_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['SMS_MODEL_TYPE'] != 8){
			$this->wrong("只能发送营销类短信！");
		}
		$this->assign('info', 				$info);
		$this->display();
	}
	
	
	
	/*
	* 短信流水管理
	**/
	public function smstrace() {
		$post = I('post');
		$post['SMS_DATE_A'] = $post['SMS_DATE_A'] ? $post['SMS_DATE_A'] : date('Y-m-d');
		$post['SMS_DATE_B'] = $post['SMS_DATE_B'] ? $post['SMS_DATE_B'] : date('Y-m-d');
		$post['SMS_TIME_A'] = $post['SMS_TIME_A'] ? $post['SMS_TIME_A'] : '000000';
		$post['SMS_TIME_B'] = $post['SMS_TIME_B'] ? $post['SMS_TIME_B'] : '235959';	
		if($post['submit'] == "smstrace"){
			$where = "1=1";
			//归属
			$getlevel = filter_data('plv');	//列表查询
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
			}
			if($post['PARTNER_MAP_ID']){
				$pids = get_plv_childs($post['PARTNER_MAP_ID'],1);
				$where .= " and PARTNER_MAP_ID in (".$pids.")";
			}
				
			//短信类型
			if($post['SMS_MODEL_TYPE']) {
				$where .= " and SMS_MODEL_TYPE = '".$post['SMS_MODEL_TYPE']."'";
			}
			//状态
			if($post['SMS_STATUS'] != '') {
				$where .= " and SMS_STATUS = '".$post['SMS_STATUS']."'";
			}
			//会员卡号
			if($post['VIP_CARDNO'] != '') {
				$where .= " and VIP_CARDNO = '".$post['VIP_CARDNO']."'";
			}
			//手机号
			if($post['SMS_RECV_MOB'] != '') {
				$where .= " and SMS_RECV_MOB = '".$post['SMS_RECV_MOB']."'";
			}
			//起始日期
			if($post['SMS_DATE_A']) {
				$where .= " and SMS_DATE >= '".date("Ymd",strtotime($post['SMS_DATE_A']))."'";
			}
			//终止日期
			if($post['SMS_DATE_B']) {
				$where .= " and SMS_DATE <= '".date("Ymd",strtotime($post['SMS_DATE_B']))."'";
			}
			//起始时间
			if($post['SMS_TIME_A']) {
				$where .= " and SMS_TIME >= '".date("His",strtotime($post['SMS_TIME_A']))."'";
			}
			//终止时间
			if($post['SMS_TIME_B']) {
				$where .= " and SMS_TIME <= '".date("His",strtotime($post['SMS_TIME_B']))."'";
			}
			//分页
			$count = D($this->TSmsls)->countSmsls($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TSmsls)->getSmslslist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
						
			$this->assign ( 'list', 		$list );
		}
		$this->assign ( 'postdata', 	$post );
		
		$this->assign('sms_model_type',		C('SMS_MODEL_TYPE'));	//短信类型
		$this->assign('sms_status',			C('SMS_STATUS'));		//短信状态
		\Cookie::set ('_currentUrl_', 		__SELF__);		
		$this->display();
	}
	/*
	* 短信流水管理 详情
	**/
	public function smstrace_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TSmsls)->findSmsls("SMS_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('sms_model_type',		C('SMS_MODEL_TYPE'));	//短信类型
		$this->assign('sms_status',			C('SMS_STATUS'));		//短信状态
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 短信流水管理 重发
	**/
	public function smstrace_sends() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TSmsls)->findSmsls("SMS_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		unset($info['SMS_ID']);
		$info['SMS_STATUS'] = 2;
		$info['SMS_DATE'] 	= date('Ymd');
		$info['SMS_TIME'] 	= date('Ymd');		
		$res = D($this->TSmsls)->addSmsls($info);
		if($res['state'] != 0){
			$this->wrong('重发失败！');
		}
		$this->right ('重发成功！', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	
	
	
	
	/*
	* 渠道管理
	**/
	public function channel() {
		$post = I('post');
		if($post['submit'] == "channel"){
			$where = "1=1";
			//类型
			if($post['CHANNEL_NAME']) {
				$where .= " and CHANNEL_NAME like '%".$post['CHANNEL_NAME']."%'";
			}
			//分页
			$count = D($this->MChannel)->countChannel($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MChannel)->getChannellist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign ('channel_status', C('CHANNEL_STATUS'));		//渠道状态
		\Cookie::set ('_currentUrl_', 		__SELF__);		
		$this->display();
	}
	/*
	* 渠道管理 添加
	**/
	public function channel_add() {
		$post = I('post');
		if($post['submit'] == "channel_add") {
			//验证
			if(empty($post['CHANNEL_NAME']) || empty($post['CHANNEL_NAMEAB']) || empty($post['CITY_NO']) || empty($post['SETTLE_T'])){
				$this->wrong("缺少必填项数据！");
			}
			$home = session('HOME');
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'		=>	0,
				'CHANNEL_NAME'		=>	$post['CHANNEL_NAME'],
				'CHANNEL_NAMEAB'	=>	$post['CHANNEL_NAMEAB'],
				'CHANNEL_STATUS'	=>	$post['CHANNEL_STATUS'],
				'CHANNEL_ACQ_CODE'	=>	0,
				'CITY_NO'			=>	$post['CITY_NO'],
				'ADDRESS'			=>	$post['ADDRESS'],
				'ZIP'				=>	$post['ZIP'],
				'TEL'				=>	$post['TEL'],
				'MANAGER'			=>	$post['MANAGER'],
				'MOBILE'			=>	$post['MOBILE'],
				'EMAIL'				=>	$post['EMAIL'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'CREATE_TIME'		=>	date('YmdHis')
			);
			$m = M();
			$m->startTrans();	//启用事务
			$res = D($this->MChannel)->addChannel($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//添加结算方式
			//组装数据
			$cclsdata = array(
				'CHANNEL_MAP_ID' 		=> $res['CHANNEL_MAP_ID'],
				'CHANNEL_STATUS'		=> 0,
				'CHANNEL_SETTLE_FLAG'	=> 0,
				'SETTLE_T'				=> $post['SETTLE_T'],
				'SETTLE_T_UNIT'			=> 1,
				'SETTLE_FLAG'			=> 0,
				'SETTLE_TOP_AMT'		=> 0,
				'SETTLE_FREE_AMT'		=> 0,
				'SETTLE_OFF_AMT'		=> 0,
				'SETTLE_OFF_FEE'		=> 0,
				'SETTLE_FEE'			=> 0
			);
			$res = D($this->MCcls)->addCcls($cclsdata);
			if ($res['state'] != 0) {
				$m->rollback();		//回滚
				$this->wrong('结算方式添加失败');
			}
			$m->commit();			//成功则提交
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign ('channel_status', C('CHANNEL_STATUS'));		//渠道状态
		$this->display();
	}
	/*
	* 渠道管理 查看
	**/
	public function channel_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MChannel)->findChannel("CHANNEL_MAP_ID='".$id."'");
		$ccls_info = D($this->MCcls)->findCcls("CHANNEL_MAP_ID='".$id."'");
		if(empty($info) || empty($ccls_info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('channel_status', C('CHANNEL_STATUS'));		//渠道状态
		$this->assign ('info', $info);								//数据信息
		$this->assign ('ccls_info', $ccls_info);					//结算方式信息
		$this->display();
	}
	/*
	* 渠道管理 修改
	**/
	public function channel_edit() {
		$post = I('post');
		if($post['submit'] == "channel_edit") {
			//验证
			if(empty($post['CHANNEL_MAP_ID']) || empty($post['CHANNEL_NAME']) || empty($post['CHANNEL_NAMEAB']) || empty($post['CITY_NO']) || empty($post['SETTLE_T'])){
				$this->wrong("缺少必填项数据！");
			}
			$home = session('HOME');
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'		=>	0,
				'CHANNEL_NAME'		=>	$post['CHANNEL_NAME'],
				'CHANNEL_NAMEAB'	=>	$post['CHANNEL_NAMEAB'],
				'CHANNEL_STATUS'	=>	$post['CHANNEL_STATUS'],
				'CHANNEL_ACQ_CODE'	=>	0,
				'CITY_NO'			=>	$post['CITY_NO'],
				'ADDRESS'			=>	$post['ADDRESS'],
				'ZIP'				=>	$post['ZIP'],
				'TEL'				=>	$post['TEL'],
				'MANAGER'			=>	$post['MANAGER'],
				'MOBILE'			=>	$post['MOBILE'],
				'EMAIL'				=>	$post['EMAIL'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'CREATE_TIME'		=>	date('YmdHis')
			);
			$m = M();
			$m->startTrans();	//启用事务

			$where = 'CHANNEL_MAP_ID ='.$post['CHANNEL_MAP_ID'];
			$res = D($this->MChannel)->updateChannel($where,$resdata);
			if($res['state'] != 0){
				$this->wrong('渠道基本信息修改失败');
			}
			//添加结算方式
			//组装数据
			$cclsdata = array(
				'SETTLE_T' => $post['SETTLE_T']
			);
			$res = D($this->MCcls)->updateCcls($where, $cclsdata);
			if ($res['state'] != 0) {
				$m->rollback();		//回滚
				$this->wrong('结算方式修改失败');
			}
			$m->commit();			//成功则提交
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MChannel)->findChannel("CHANNEL_MAP_ID='".$id."'");
		$ccls_info = D($this->MCcls)->findCcls("CHANNEL_MAP_ID='".$id."'");
		if(empty($info) || empty($ccls_info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('channel_status', C('CHANNEL_STATUS'));		//渠道状态
		$this->assign ('info', $info);								//数据信息
		$this->assign ('ccls_info', $ccls_info);					//结算方式信息
		$this->display('channel_add');
	}
	/*
	* 渠道管理 查看
	**/
	public function channel_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$res = D($this->MChannel)->delChannel("CHANNEL_MAP_ID='".$id."'");
		if($res['state']!=0){
			$this->wrong("参数数据出错！");
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}

	/*
	* 银行数据管理
	**/
	public function bankbid() {
		$post = I('post');
		if($post['submit'] == "bankbid"){
			$where = "1=1";
			//所在省
			if($post['CITY_S_CODE']) {
				$where .= " and CITY_S_CODE = '".$post['CITY_S_CODE']."'";
			}
			//开户银行
			if($post['ISSUE_CODE']) {
				$where .= " and ISSUE_CODE = '".$post['ISSUE_CODE']."'";
			}
			//银行名称
			if($post['BANK_BNAME']) {
				$where .= " and BANK_BNAME like '%".$post['BANK_BNAME']."%'";
			}
			//银行行号
			if($post['BANK_BID']) {
				$where .= " and BANK_BID = '".$post['BANK_BID']."'";
			}
			//分页
			$count = D($this->MBid)->countBid($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MBid)->getBidlist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		//省列表
		$citylist = D($this->MCity)->getCityslist('RPAD(PROVINCE_CODE,6,"0") as PROVINCE_CODE,PROVINCE_NAME');
		//银行列表
		$banklist = D($this->MBank)->getBanklist('','ISSUE_CODE,BANK_NAME');
		$this->assign('citylist',			$citylist);
		$this->assign('banklist',			$banklist);
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}

	/*
	* 银行数据管理
	**/
	public function bankbid_add() {
		$post = I('post');
		if($post['submit'] == "bankbid_add") {
			//验证
			if(empty($post['ISSUE_CODE']) || empty($post['BANK_BID']) || empty($post['CITY_S_CODE']) || empty($post['BANK_BNAME'])){
				$this->wrong("缺少必填项数据！");
			}
			$issue_code = $post['ISSUE_CODE'];
			$city_code = substr($post['CITY_S_CODE'],0,2);
			//银行数据
			$bankdata = D('MBank')->findBank('ISSUE_CODE = "'.$issue_code.'"','ISSUE_CODE,BANK_NAME');
			$citydata = D('MCity')->findCity("PROVINCE_CODE='".$city_code."'",'PROVINCE_NAME');
			//组装数据
			$resdata = array(
				'BANK_TYPE'		=>	'9',
				'ISSUE_CODE'	=>	$issue_code,
				'BANK_NAME'		=>	$bankdata['BANK_NAME'] ? $bankdata['BANK_NAME'] : '',
				'CITY_S_CODE'	=>	$post['CITY_S_CODE'],
				'CITY_S_NAME'	=>	$citydata['PROVINCE_NAME'] ? $citydata['PROVINCE_NAME'] : '',
				'BANK_BID'		=>	$$post['BANK_BID'],
				'BANK_BNAME'	=>	$post['BANK_BNAME']
			);
			$res = D($this->Bid)->add($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//省列表
		$citylist = D($this->MCity)->getCityslist('RPAD(PROVINCE_CODE,6,"0") as PROVINCE_CODE,PROVINCE_NAME');
		//银行列表
		$banklist = D($this->MBank)->getBanklist('','ISSUE_CODE,BANK_NAME');
		$this->assign('citylist',			$citylist);
		$this->assign('banklist',			$banklist);
		$this->display();
	}

	/*
	* 银行数据管理
	**/
	public function bankbid_edit() {
		$post = I('post');
		if($post['submit'] == "bankbid_edit") {
			//验证
			if(empty($post['ISSUE_CODE']) || empty($post['BANK_BID']) || empty($post['CITY_S_CODE']) || empty($post['BANK_BNAME'])){
				$this->wrong("缺少必填项数据！");
			}
			$issue_code = $post['ISSUE_CODE'];
			$city_code = substr($post['CITY_S_CODE'],0,2);
			//银行数据
			$bankdata = D('MBank')->findBank('ISSUE_CODE = "'.$issue_code.'"','ISSUE_CODE,BANK_NAME');
			$citydata = D('MCity')->findCity("PROVINCE_CODE='".$city_code."'",'PROVINCE_NAME');
			//组装数据
			$resdata = array(
				'BANK_TYPE'		=>	'9',
				'ISSUE_CODE'	=>	'0'.substr($BANK_BID,0,3).'0000',
				'BANK_NAME'		=>	$bankdata['BANK_NAME'] ? $bankdata['BANK_NAME'] : '',
				'CITY_S_CODE'	=>	$CITY_S_CODE,
				'CITY_S_NAME'	=>	$citydata['PROVINCE_NAME'],
				'BANK_BID'		=>	$BANK_BID,
				'BANK_BNAME'	=>	$post['BANK_BNAME']
			);
			$res = D($this->Bid)->updateBid($where,$resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MBranch)->findBid('BANK_BID = "'.$id.'"', $field='*');
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}

		//省列表
		$citylist = D($this->MCity)->getCityslist('RPAD(PROVINCE_CODE,6,"0") as PROVINCE_CODE,PROVINCE_NAME');
		//银行列表
		$banklist = D($this->MBank)->getBanklist('','ISSUE_CODE,BANK_NAME');
		$this->assign('info',			$info);
		$this->assign('citylist',		$citylist);
		$this->assign('banklist',		$banklist);
		$this->display();
	}
	/*if($BANK_BID){
				$count = $Model->where("BANK_BID='".$BANK_BID."'")->count();
				if($count == 0){
					$issue_code = '0'.substr($BANK_BID,0,3).'0000';
					$city_code = substr($CITY_S_CODE,0,2);
					//银行数据
					$bankdata = D('MBank')->findBank('ISSUE_CODE = "'.$issue_code.'"','ISSUE_CODE,BANK_NAME');
					$citydata = D('MCity')->findCity("PROVINCE_CODE='".$city_code."'",'PROVINCE_NAME');
					//组装数据
					$resdata = array(
						'BANK_TYPE'		=>	'9',
						'ISSUE_CODE'	=>	'0'.substr($BANK_BID,0,3).'0000',
						'BANK_NAME'		=>	$bankdata['BANK_NAME'] ? $bankdata['BANK_NAME'] : '',
						'CITY_S_CODE'	=>	$CITY_S_CODE ? $CITY_S_CODE : '',
						'CITY_S_NAME'	=>	$citydata['PROVINCE_NAME'] ? $citydata['PROVINCE_NAME'] : '',
						'BANK_BID'		=>	$BANK_BID,
						'BANK_BNAME'	=>	$BANK_BNAME ? $BANK_BNAME : ''
					);
					//dump($resdata);exit;
					$Model->data($resdata)->add();
				}
			}*/
}