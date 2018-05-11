<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  银行设置
// +----------------------------------------------------------------------
class BanksetController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->MWdate		= 'MWdate';
		$this->MCardbin 	= 'MCardbin';
		$this->MBank 		= 'MBank';
		$this->MPbocpara 	= 'MPbocpara';
		$this->MPbockey 	= 'MPbockey';
		$this->MBranch 		= 'MBranch';
		$this->MHost 		= 'MHost';
		$this->MComm 		= 'MComm';
	}
	
	/*
	* 节假日管理
	**/
	public function wdate() {
		$post = I('post');
		if($post['submit'] == "wdate"){
			$where = "1=1";
			//所属分支
			if($post['NOWORK_DATE']) {
				$where .= " and NOWORK_DATE = '".date('Ymd',strtotime($post['NOWORK_DATE']))."'";
			}
			//分页
			$count = D($this->MWdate)->countWdate($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MWdate)->getWdatelist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('user_status',		C('USER_STATUS'));	//分支机构级别
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 节假日管理 添加
	**/
	public function wdate_add() {
		$post = I('post');
		if($post['submit'] == "wdate_add") {
			//验证
			if(empty($post['NOWORK_DATE_A']) || empty($post['NOWORK_DATE_B']) || empty($post['WORK_DATE']) || empty($post['RES'])){
				$this->wrong("缺少必填项数据！");
			}
			$post['NOWORK_DATE_A'] = date("Ymd", strtotime($post['NOWORK_DATE_A']));
			$post['NOWORK_DATE_B'] = date("Ymd", strtotime($post['NOWORK_DATE_B']));
			$post['WORK_DATE'] 	   = date("Ymd", strtotime($post['WORK_DATE']));			
			if($post['NOWORK_DATE_A'] > $post['NOWORK_DATE_B']){
				$this->wrong("请规范选择法定节假日期！");
			}
			if($post['NOWORK_DATE_B'] >= $post['WORK_DATE']){
				$this->wrong("请规范选择顺延清算日！");
			}
			$count = D($this->MWdate)->countWdate("NOWORK_DATE >= '".$post['NOWORK_DATE_A']."' and NOWORK_DATE <= '".$post['NOWORK_DATE_B']."'");
			if($count > 0){
				$this->wrong("部分节假日期已经存在！");
			}
			$num = $post['NOWORK_DATE_B'] - $post['NOWORK_DATE_A'] + 1;
			if($num > 15){
				$this->wrong("节假日期天数太大，超出限制！");
			}
			
			//组装数据
			$resdata = array();
			for($i=0; $i<$num; $i++){
				$resdata[] = array(
					'NOWORK_DATE'	=>	$post['NOWORK_DATE_A'] + $i,
					'WORK_DATE'		=>	$post['WORK_DATE'],
					'RES'			=>	$post['RES']
				);
			}
			$res = D($this->MWdate)->addAllWdate($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->display();
	}
	/*
	* 节假日管理 修改
	**/
	public function wdate_edit() {
		$post = I('post');
		if($post['submit'] == "wdate_edit") {
			//验证
			if(empty($post['NOWORK_DATE']) || empty($post['WORK_DATE']) || empty($post['RES'])){
				$this->wrong("缺少必填项数据！");
			}
			$post['NOWORK_DATE'] = date("Ymd", strtotime($post['NOWORK_DATE']));
			$post['WORK_DATE'] 	 = date("Ymd", strtotime($post['WORK_DATE']));
			if($post['NOWORK_DATE'] >= $post['WORK_DATE']){
				$this->wrong("请规范选择顺延清算日！");
			}		
			$count = D($this->MWdate)->countWdate("WDATE_ID != '".$post['WDATE_ID']."' and NOWORK_DATE = '".$post['NOWORK_DATE']."'");
			if($count > 0){
				$this->wrong("该节假日期已经存在！");
			}
			//组装数据
			$resdata = array(
				'NOWORK_DATE'	=> 	$post['NOWORK_DATE'],
				'WORK_DATE'		=> 	$post['WORK_DATE'],
				'RES'			=>	$post['RES']
			);
			$res = D($this->MWdate)->updateWdate("WDATE_ID='".$post['WDATE_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MWdate)->findWdate("WDATE_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('info', 				$info);
		$this->display('wdate_add');
	}
	/*
	* 节假日管理 删除
	**/
	public function wdate_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$res = D($this->MWdate)->delWdate("WDATE_ID='".$id."'");
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg']);
	}
	
	
	
	/*
	* 银行卡BIN维护
	**/
	public function cardbin() {
		$post = I('post');
		if($post['submit'] == "cardbin"){
			$where = "1=1";
			//银行卡名
			if($post['ISSUE_CODE']) {
				if($post['ISSUE_CODE'] == '-1') {
					$where .= " and BIN_FLAG = '1'";				
				}else{
					$where .= " and ISSUE_CODE = '".$post['ISSUE_CODE']."'";
				}					
			}
			//银行卡类型
			if($post['CARD_TYPE']) {
				$where .= " and CARD_TYPE = '".$post['CARD_TYPE']."'";
			}
			//银行卡bin
			if($post['CARD_BIN']) {
				$where .= " and CARD_BIN = '".$post['CARD_BIN']."'";	//左边开始匹配
			}
			//分页
			$count = D($this->MCardbin)->countCardbin($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MCardbin)->getCardbinlist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		//发卡行列表
		$bank_list = D($this->MBank)->getBanklist("BANK_MAP_ID!=''", 'ISSUE_CODE,BANK_NAME');
		$this->assign('bank_list', 			$bank_list);		
		$this->assign('card_type',			C('CARD_TYPE'));	//卡类型
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 银行卡BIN维护 添加
	**/
	public function cardbin_add() {
		$post = I('post');
		if($post['submit'] == "cardbin_add") {
			//验证
			if(empty($post['CARD_BIN']) || empty($post['CARD_NAME']) || empty($post['PAN_OFF']) || empty($post['PAN_LEN'])){
				$this->wrong("缺少必填项数据！");
			}
			$finddata = D($this->MCardbin)->findCardbin("CARD_BIN='".$post['CARD_BIN']."'");
			if(!empty($finddata)){
				$this->wrong("该卡BIN号已经存在！");
			}
			//如是是银行卡
			if($post['BIN_FLAG'] == 0){
				$brankdata = D($this->MBank)->findBank("ISSUE_CODE = '".$post['ISSUE_CODE']."'", 'BANK_NAME');
				$post['ISSUE_NAME'] = $brankdata['BANK_NAME'];
			}else{
				$post['ISSUE_CODE'] = '';
				$post['ISSUE_NAME'] = '';			
			}
			//组装数据
			$resdata = array(
				'ISSUE_CODE'	=>	$post['ISSUE_CODE'],
				'ISSUE_NAME'	=>	$post['ISSUE_NAME'],
				'CARD_BIN'		=>	$post['CARD_BIN'],
				'CARD_NAME'		=>	$post['CARD_NAME'],
				'CARD_TYPE'		=>	$post['CARD_TYPE'],
				'CARD_TRACK'	=>	$post['CARD_TRACK'],
				'PAN_OFF'		=>	$post['PAN_OFF'],
				'PAN_LEN'		=>	$post['PAN_LEN'],
				'BIN_FLAG'		=>	$post['BIN_FLAG']
			);
			$res = D($this->MCardbin)->addCardbin($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$bank_list = D($this->MBank)->getBanklist("BANK_MAP_ID!=''", 'ISSUE_CODE,BANK_NAME');
		$this->assign('bank_list', 			$bank_list);		//发卡行列表
		$this->assign('card_type',			C('CARD_TYPE'));	//卡类型
		$this->assign('card_track',			C('CARD_TRACK'));	//磁道类型
		$this->display();
	}
	/*
	* 银行卡BIN维护 详情
	**/
	public function cardbin_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MCardbin)->findCardbin("BIN_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//检查银行卡 卡bin是否合法
		if($info['BIN_FLAG'] == 0){
			$brankdata = D($this->MBank)->findBank("ISSUE_CODE = '".$info['ISSUE_CODE']."'", 'BANK_NAME');
			if(empty($brankdata)) {
				$this->wrong("该银行卡参数不全，无法查看！");
			}
			$info['ISSUE_NAME'] = $brankdata['BANK_NAME'];
		}
		
		$this->assign('card_type',			C('CARD_TYPE'));	//卡类型
		$this->assign('card_track',			C('CARD_TRACK'));	//磁道类型
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 银行卡BIN维护 修改
	**/
	public function cardbin_edit() {
		$post = I('post');
		if($post['submit'] == "cardbin_edit") {
			//验证
			if(empty($post['CARD_BIN']) || empty($post['CARD_NAME']) || empty($post['PAN_OFF']) || empty($post['PAN_LEN'])){
				$this->wrong("缺少必填项数据！");
			}
			$finddata = D($this->MCardbin)->findCardbin("BIN_ID!='".$post['BIN_ID']."' and CARD_BIN='".$post['CARD_BIN']."'");
			if(!empty($finddata)){
				$this->wrong("该卡BIN号已经存在！");
			}
			//如是是银行卡
			if($post['BIN_FLAG'] == 0){
				$brankdata = D($this->MBank)->findBank("ISSUE_CODE = '".$post['ISSUE_CODE']."'", 'BANK_NAME');
				$post['ISSUE_NAME'] = $brankdata['BANK_NAME'];
			}else{
				$post['ISSUE_CODE'] = '';
				$post['ISSUE_NAME'] = '';			
			}
			//组装数据
			$resdata = array(
				'ISSUE_CODE'	=>	$post['ISSUE_CODE'],
				'ISSUE_NAME'	=>	$post['ISSUE_NAME'],
				'CARD_BIN'		=>	$post['CARD_BIN'],
				'CARD_NAME'		=>	$post['CARD_NAME'],
				'CARD_TYPE'		=>	$post['CARD_TYPE'],
				'CARD_TRACK'	=>	$post['CARD_TRACK'],
				'PAN_OFF'		=>	$post['PAN_OFF'],
				'PAN_LEN'		=>	$post['PAN_LEN'],
				'BIN_FLAG'		=>	$post['BIN_FLAG']
			);
			$res = D($this->MCardbin)->updateCardbin("BIN_ID='".$post['BIN_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MCardbin)->findCardbin("BIN_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//检查银行卡 卡bin是否合法
		if($info['BIN_FLAG'] == 0){
			$brankdata = D($this->MBank)->findBank("ISSUE_CODE = '".$info['ISSUE_CODE']."'", 'BANK_NAME');
			if(empty($brankdata)) {
				$this->wrong("该银行卡参数不全，无法修改！");
			}
		}
		
		$bank_list = D($this->MBank)->getBanklist("BANK_MAP_ID!=''", 'ISSUE_CODE,BANK_NAME');
		$this->assign('bank_list', 			$bank_list);		//发卡行列表
		$this->assign('card_type',			C('CARD_TYPE'));	//卡类型
		$this->assign('card_track',			C('CARD_TRACK'));	//磁道类型
		$this->assign ('info', 				$info);
		$this->display('cardbin_add');
	}
	
	
	
	/*
	* 银行IC卡参数维护
	**/
	public function iccardparam() {
		$post = I('post');
		if($post['submit'] == 'iccardparam') {
			$where = '1=1';
			//应用版本号
			if($post['IC_APP_VER']) {
				$where .= ' and IC_APP_VER = "'.$post['IC_APP_VER'].'"';
			}
			//IC卡AID值
			if($post['IC_AID']) {
				$where .= ' and IC_AID = "'.$post['IC_AID'].'"';
			}
			//分页
			$count = D($this->MPbocpara)->countPbocpara($where);
			$p 	   = new \Think\Page($count,C('PAGE_COUNT'));
			$list  = D($this->MPbocpara)->getPbocparalist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
	       	$this->assign ( 'numPerPage', $p->listRows );
	       	$this->assign ( 'currentPage', !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);			
			$this->assign ( 'totalCount', $count );
			
			$this->assign ( 'list', 	$list );
			$this->assign ( 'postdata',	$post );	//分页参数
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);//当前URL
		$this->display();
	}
	/*
	* 银行IC卡参数维护 添加
	**/
	public function iccardparam_add() {
		$post = I('post');
		if($post['submit'] == "iccardparam_add") {
			//验证
			if(empty($post['IC_AID']) || empty($post['IC_TAC_DEFAULT']) || empty($post['IC_ASI']) || empty($post['IC_TAC_ONLINE']) || empty($post['IC_APP_VER']) || empty($post['IC_TAC_REFUSE']) || $post['IC_LEAST_AMT']=='' || $post['IC_CASH_LIMIT_AMT']=='' || empty($post['IC_RAND_OFFSET']) || $post['IC_NFC_LEAST_AMT']=='' || $post['IC_RAND_MAX_PER']=='' || $post['IC_NFC_LIMIT_AMT']=='' || $post['IC_RAND_PER']=='' || $post['IC_NFC_CVM']=='' || $post['IC_ONLINE_PIN']=='' || empty($post['IC_DDOL_DEFAULT'])){
				$this->wrong("缺少必填项数据！");
			}
			$finddata = D($this->MPbocpara)->findPbocpara("IC_AID='".$post['IC_AID']."' and IC_ASI='".$post['IC_ASI']."'");
			if(!empty($finddata)){
				$this->wrong("该IC卡参数已经存在！");
			}
			//组装数据
			$pbocparadata = array(
				'IC_AID'			=>	$post['IC_AID'],
				'IC_ASI'			=>	$post['IC_ASI'],
				'IC_APP_VER'		=>	$post['IC_APP_VER'],
				'IC_TAC_DEFAULT'	=>	$post['IC_TAC_DEFAULT'],
				'IC_TAC_ONLINE'		=>	$post['IC_TAC_ONLINE'],
				'IC_TAC_REFUSE'		=>	$post['IC_TAC_REFUSE'],
				'IC_LEAST_AMT'		=>	setMoney($post['IC_LEAST_AMT'], '2'),
				'IC_RAND_OFFSET'	=>	$post['IC_RAND_OFFSET'],
				'IC_RAND_MAX_PER'	=>	$post['IC_RAND_MAX_PER'],
				'IC_RAND_PER'		=>	$post['IC_RAND_PER'],
				'IC_DDOL_DEFAULT'	=>	$post['IC_DDOL_DEFAULT'],
				'IC_ONLINE_PIN'		=>	$post['IC_ONLINE_PIN'],
				'IC_CASH_LIMIT_AMT'	=>	setMoney($post['IC_CASH_LIMIT_AMT'], '2'),
				'IC_NFC_LEAST_AMT'	=>	setMoney($post['IC_NFC_LEAST_AMT'], '2'),
				'IC_NFC_LIMIT_AMT'	=>	setMoney($post['IC_NFC_LIMIT_AMT'], '2'),
				'IC_NFC_CVM'		=>	setMoney($post['IC_NFC_CVM'], '2'),
			);
			$res = D($this->MPbocpara)->addPbocpara($pbocparadata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign('ic_online_pin',		C('IC_ONLINE_PIN'));	//磁道类型
		$this->display();
	}
	/*
	* 银行IC卡参数维护 详情
	**/
	public function iccardparam_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MPbocpara)->findPbocpara("PBOCPARA_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('ic_online_pin',		C('IC_ONLINE_PIN'));	//磁道类型
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 银行IC卡参数维护 修改
	**/
	public function iccardparam_edit() {
		$post = I('post');
		if($post['submit'] == "iccardparam_edit") {
			//验证
			if(empty($post['IC_AID']) || empty($post['IC_TAC_DEFAULT']) || empty($post['IC_ASI']) || empty($post['IC_TAC_ONLINE']) || empty($post['IC_APP_VER']) || empty($post['IC_TAC_REFUSE']) || $post['IC_LEAST_AMT']=='' || $post['IC_CASH_LIMIT_AMT']=='' || empty($post['IC_RAND_OFFSET']) || $post['IC_NFC_LEAST_AMT']=='' || $post['IC_RAND_MAX_PER']=='' || $post['IC_NFC_LIMIT_AMT']=='' || $post['IC_RAND_PER']=='' || $post['IC_NFC_CVM']=='' || $post['IC_ONLINE_PIN']=='' || empty($post['IC_DDOL_DEFAULT'])){
				$this->wrong("缺少必填项数据！");
			}
			$finddata = D($this->MPbocpara)->findPbocpara("PBOCPARA_ID!='".$post['PBOCPARA_ID']."' and IC_AID='".$post['IC_AID']."' and IC_ASI='".$post['IC_ASI']."'");
			if(!empty($finddata)){
				$this->wrong("该IC卡参数已经存在！");
			}
			//组装数据
			$pbocparadata = array(
				'IC_AID'			=>	$post['IC_AID'],
				'IC_ASI'			=>	$post['IC_ASI'],
				'IC_APP_VER'		=>	$post['IC_APP_VER'],
				'IC_TAC_DEFAULT'	=>	$post['IC_TAC_DEFAULT'],
				'IC_TAC_ONLINE'		=>	$post['IC_TAC_ONLINE'],
				'IC_TAC_REFUSE'		=>	$post['IC_TAC_REFUSE'],
				'IC_LEAST_AMT'		=>	setMoney($post['IC_LEAST_AMT'], '2'),
				'IC_RAND_OFFSET'	=>	$post['IC_RAND_OFFSET'],
				'IC_RAND_MAX_PER'	=>	$post['IC_RAND_MAX_PER'],
				'IC_RAND_PER'		=>	$post['IC_RAND_PER'],
				'IC_DDOL_DEFAULT'	=>	$post['IC_DDOL_DEFAULT'],
				'IC_ONLINE_PIN'		=>	$post['IC_ONLINE_PIN'],
				'IC_CASH_LIMIT_AMT'	=>	setMoney($post['IC_CASH_LIMIT_AMT'], '2'),
				'IC_NFC_LEAST_AMT'	=>	setMoney($post['IC_NFC_LEAST_AMT'], '2'),
				'IC_NFC_LIMIT_AMT'	=>	setMoney($post['IC_NFC_LIMIT_AMT'], '2'),
				'IC_NFC_CVM'		=>	setMoney($post['IC_NFC_CVM'], '2'),
			);
			$res = D($this->MPbocpara)->updatePbocpara("PBOCPARA_ID='".$post['PBOCPARA_ID']."'", $pbocparadata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MPbocpara)->findPbocpara("PBOCPARA_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('ic_online_pin',		C('IC_ONLINE_PIN'));	//磁道类型
		$this->assign('info', 				$info);
		$this->display('iccardparam_add');
	}
	
	
	
	/*
	* 银行IC卡公钥维护
	**/
	public function iccardkey() {
		$post = I('post');
		if($post['submit'] == 'iccardkey') {
			$where = '1=1';
			//公钥索引
			if($post['IC_PUBKEY_INDEX']) {
				$where .= ' and IC_PUBKEY_INDEX = "'.$post['IC_PUBKEY_INDEX'].'"';
			}
			//公钥RID
			if($post['IC_PUBKEY_RID']) {
				$where .= ' and IC_PUBKEY_RID = "'.$post['IC_PUBKEY_RID'].'"';
			}
			//分页
			$count = D($this->MPbockey)->countPbockey($where);
			$p 	   = new \Think\Page($count,C('PAGE_COUNT'));
			$list  = D($this->MPbockey)->getPbockeylist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
	       	$this->assign ( 'numPerPage', $p->listRows );
	       	$this->assign ( 'currentPage', !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);			
			$this->assign ( 'totalCount', $count );
			
			$this->assign ( 'list', 		$list );
			$this->assign ( 'postdata',		$post );
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);//当前URL
		$this->display();
	}
	/*
	* 银行IC卡公钥维护 添加
	**/
	public function iccardkey_add() {
		$post = I('post');
		if($post['submit'] == "iccardkey_add") {
			//验证
			if(empty($post['IC_PUBKEY_INDEX']) || empty($post['IC_PUBKEY_RID']) || empty($post['IC_PUBKEY_EXP']) || empty($post['IC_PUBKEY_HASH']) || empty($post['IC_PUBKEY_ALG']) || empty($post['IC_PUBKEY_POINT']) || empty($post['IC_PUBKEY_CHECK']) || empty($post['IC_PUBKEY_MODE'])){
				$this->wrong("缺少必填项数据！");
			}
			$finddata = D($this->MPbockey)->findPbockey("IC_PUBKEY_RID='".$post['IC_PUBKEY_RID']."' and IC_PUBKEY_INDEX='".$post['IC_PUBKEY_INDEX']."'");
			if(!empty($finddata)){
				$this->wrong("该IC卡公钥已经存在！");
			}
			//组装数据
			$resdata = array(
				'IC_PUBKEY_RID'		=>	$post['IC_PUBKEY_RID'],
				'IC_PUBKEY_INDEX'	=>	$post['IC_PUBKEY_INDEX'],
				'IC_PUBKEY_EXP'		=>	date("Ymd", strtotime($post['IC_PUBKEY_EXP'])),
				'IC_PUBKEY_HASH'	=>	$post['IC_PUBKEY_HASH'],
				'IC_PUBKEY_ALG'		=>	$post['IC_PUBKEY_ALG'],
				'IC_PUBKEY_MODE'	=>	$post['IC_PUBKEY_MODE'],
				'IC_PUBKEY_POINT'	=>	$post['IC_PUBKEY_POINT'],
				'IC_PUBKEY_CHECK'	=>	$post['IC_PUBKEY_CHECK']
			);
			$res = D($this->MPbockey)->addPbockey($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->display();
	}
	/*
	* 银行IC卡公钥维护 详情
	**/
	public function iccardkey_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MPbockey)->findPbockey("PBOCKEY_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('info', 				$info);
		$this->display();
	}
	/*
	* 银行IC卡公钥维护 修改
	**/
	public function iccardkey_edit() {
		$post = I('post');
		if($post['submit'] == "iccardkey_edit") {
			//验证
			if(empty($post['IC_PUBKEY_INDEX']) || empty($post['IC_PUBKEY_RID']) || empty($post['IC_PUBKEY_EXP']) || empty($post['IC_PUBKEY_HASH']) || empty($post['IC_PUBKEY_ALG']) || empty($post['IC_PUBKEY_POINT']) || empty($post['IC_PUBKEY_CHECK']) || empty($post['IC_PUBKEY_MODE'])){
				$this->wrong("缺少必填项数据！");
			}
			$finddata = D($this->MPbockey)->findPbockey("PBOCKEY_ID!='".$post['PBOCKEY_ID']."' and IC_PUBKEY_RID='".$post['IC_PUBKEY_RID']."' and IC_PUBKEY_INDEX='".$post['IC_PUBKEY_INDEX']."'");
			if(!empty($finddata)){
				$this->wrong("该IC卡公钥已经存在！");
			}
			//组装数据
			$resdata = array(
				'IC_PUBKEY_RID'		=>	$post['IC_PUBKEY_RID'],
				'IC_PUBKEY_INDEX'	=>	$post['IC_PUBKEY_INDEX'],
				'IC_PUBKEY_EXP'		=>	date("Ymd", strtotime($post['IC_PUBKEY_EXP'])),
				'IC_PUBKEY_HASH'	=>	$post['IC_PUBKEY_HASH'],
				'IC_PUBKEY_ALG'		=>	$post['IC_PUBKEY_ALG'],
				'IC_PUBKEY_MODE'	=>	$post['IC_PUBKEY_MODE'],
				'IC_PUBKEY_POINT'	=>	$post['IC_PUBKEY_POINT'],
				'IC_PUBKEY_CHECK'	=>	$post['IC_PUBKEY_CHECK']
			);
			$res = D($this->MPbockey)->updatePbockey("PBOCKEY_ID='".$post['PBOCKEY_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MPbockey)->findPbockey("PBOCKEY_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('info', 				$info);
		$this->display('iccardkey_add');
	}	
	
	
	
	/*
	* 银行路由设置
	**/
	public function route() {
		$post = I('post');
		if($post['submit'] == "route"){
			$where = "1=1";
			//代理商名称
			if($post['BRANCH_MAP_ID']) {
				$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
			}
			//分支机构
			if($post['HOST_MAP_ID']) {
				$where .= " and HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
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
		//分支机构
		$branch_list = D($this->MBranch)->getBranchlist("BRANCH_LEVEL=1", 'BRANCH_MAP_ID,BRANCH_NAME');
		//通道列表
		$host = D($this->MHost)->getHostlist("HOST_STATUS = 0", 'HOST_MAP_ID,HOST_NAME');
		foreach($host as $val){
			$host_list[$val['HOST_MAP_ID']] = $val['HOST_NAME'];
		}
		$this->assign('branch_list',		$branch_list);
		$this->assign('host_list',			$host_list);
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 银行路由设置 修改
	**/
	public function route_edit() {
		$post = I('post');
		if($post['submit'] == "route_edit") {
			//验证
			if(empty($post['BRANCH_MAP_ID']) || empty($post['HOST_MAP_ID'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'HOST_MAP_ID'		=>	$post['HOST_MAP_ID'],
				'HOST_MAP_ID_NEW'	=>	$post['HOST_MAP_ID']
			);
			$res = D($this->MBranch)->updateBranch("BRANCH_MAP_ID='".$post['BRANCH_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MBranch)->findBranch("BRANCH_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}		
		//通道列表
		$host = D($this->MHost)->getHostlist("HOST_STATUS = 0", 'HOST_MAP_ID,HOST_NAME');
		foreach($host as $val){
			$host_list[$val['HOST_MAP_ID']] = $val['HOST_NAME'];
		}
		$this->assign('host_list',			$host_list);
		$this->assign('info', 				$info);
		$this->display('route_add');
	}
	/*
	* 银行路由设置 详情
	**/
	public function route_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MBranch)->findBranch("BRANCH_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//通道信息
		$host_data1 = D($this->MHost)->findHost("HOST_MAP_ID= '".$info['HOST_MAP_ID']."'", 'HOST_NAME');
		$host_data2 = D($this->MHost)->findHost("HOST_MAP_ID= '".$info['HOST_MAP_ID_NEW']."'", 'HOST_NAME');
		$info['HOST_NAME']	   = $host_data1['HOST_NAME'];
		$info['HOST_NAME_NEW'] = $host_data2['HOST_NAME'];
		$this->assign ('info', 				$info);
		$this->display();
	}
	
	
	
	/*
	* POS通讯参数设置
	**/
	public function poscom() {
		$post = I('post');
		if($post['submit'] == 'poscom') {
			$where = '1=1';
			//通讯索引
			if($post['COM_INDEX']) {
				$where .= ' and COM_INDEX = "'.$post['COM_INDEX'].'"';
			}
			//通讯名称
			if ($post['COM_NAME']) {
				$where .= ' and COM_NAME like "%'.$post['COM_NAME'].'%"';
			}			
			//分页
			$count = D($this->MComm)->countComm($where);
			$p     = new \Think\Page($count,C('PAGE_COUNT'));
			$list  = D($this->MComm)->getCommlist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
	       	$this->assign ( 'numPerPage', $p->listRows );
	       	$this->assign ( 'currentPage', !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);			
			$this->assign ( 'totalCount', $count );
			
			$this->assign ( 'list', 		$list);
			$this->assign ( 'postdata',		$post);	//分页参数
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);//当前URL
		$this->display();
	}
	/*
	* POS通讯参数设置 添加
	**/
	public function poscom_add() {
		$post = I('post');
		if($post['submit'] == "poscom_add") {
			//验证
			if(empty($post['COM_INDEX']) || empty($post['COM_NAME'])){
				$this->wrong("缺少通讯ID,通讯名称");
			}
			$finddata = D($this->MComm)->findComm("COM_INDEX='".$post['COM_INDEX']."'");
			if(!empty($finddata)){
				$this->wrong("该通讯设备已经存在！");
			}
			//组装数据
			$resdata = array(
				'COM_INDEX'			=>	$post['COM_INDEX'],
				'COM_NAME'			=>	$post['COM_NAME'],
				'COM_TRANSGATEWAY'	=>	'',
				'COM_TRANSAPN'		=>	$post['COM_TRANSAPN'],
				'COM_TRANSAPNUSR'	=>	$post['COM_TRANSAPNUSR'],
				'COM_TRANSAPNPWD'	=>	strtoupper(md5(strtoupper(md5($post['COM_TRANSAPNPWD'])))),
				'COM_TRANSIP'		=>	$post['COM_TRANSIP'],
				'COM_TRANSPORT'		=>	$post['COM_TRANSPORT'],
				'COM_TRANSTEL1'		=>	$post['COM_TRANSTEL1'],
				'COM_TRANSTEL2'		=>	$post['COM_TRANSTEL2'],
				'COM_TRANSTEL3'		=>	$post['COM_TRANSTEL3'],
				'COM_MANGATEWAY'	=>	'',
				'COM_MANAPN'		=>	$post['COM_MANAPN'],
				'COM_MANAPNUSR'		=>	$post['COM_MANAPNUSR'],
				'COM_MANAPNPWD'		=>	strtoupper(md5(strtoupper(md5($post['COM_MANAPNPWD'])))),
				'COM_MANIP'			=>	$post['COM_MANIP'],
				'COM_MANPORT'		=>	$post['COM_MANPORT'],
				'COM_MANTEL1'		=>	$post['COM_MANTEL1']
			);
			$res = D($this->MComm)->addComm($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->display();		
	}
	/*
	* POS通讯参数设置 修改
	**/
	public function poscom_edit() {
		$post = I('post');
		if($post['submit'] == "poscom_edit") {
			//验证
			if(empty($post['COM_INDEX']) || empty($post['COM_NAME'])){
				$this->wrong("缺少通讯ID,通讯名称");
			}
			//组装数据
			$resdata = array(
				'COM_NAME'			=>	$post['COM_NAME'],
				'COM_TRANSGATEWAY'	=>	'',
				'COM_TRANSAPN'		=>	$post['COM_TRANSAPN'],
				'COM_TRANSAPNUSR'	=>	$post['COM_TRANSAPNUSR'],
				'COM_TRANSIP'		=>	$post['COM_TRANSIP'],
				'COM_TRANSPORT'		=>	$post['COM_TRANSPORT'],
				'COM_TRANSTEL1'		=>	$post['COM_TRANSTEL1'],
				'COM_TRANSTEL2'		=>	$post['COM_TRANSTEL2'],
				'COM_TRANSTEL3'		=>	$post['COM_TRANSTEL3'],
				'COM_MANGATEWAY'	=>	'',
				'COM_MANAPN'		=>	$post['COM_MANAPN'],
				'COM_MANAPNUSR'		=>	$post['COM_MANAPNUSR'],
				'COM_MANIP'			=>	$post['COM_MANIP'],
				'COM_MANPORT'		=>	$post['COM_MANPORT'],
				'COM_MANTEL1'		=>	$post['COM_MANTEL1']
			);
			if($post['COM_TRANSAPNPWD']){
				$resdata['COM_TRANSAPNPWD'] = strtoupper(md5(strtoupper(md5($post['COM_TRANSAPNPWD']))));
			}
			if($post['COM_MANAPNPWD']){
				$resdata['COM_MANAPNPWD'] 	 = strtoupper(md5(strtoupper(md5($post['COM_MANAPNPWD']))));
			}
			$res = D($this->MComm)->updateComm("COM_INDEX='".$post['COM_INDEX']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MComm)->findComm("COM_INDEX='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('info', $info);
		$this->display('poscom_add');
	}
	/*
	* POS通讯参数设置 详情
	**/
	public function poscom_show() {		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MComm)->findComm("COM_INDEX='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}	
		$this->assign ('info', 			$info);
		$this->display();
	}	
}