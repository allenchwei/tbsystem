<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  风险控制
// +----------------------------------------------------------------------
class RiskController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->MRiskrun 	= 'MRiskrun';
		$this->MRiskrule 	= 'MRiskrule';
		$this->MRiskval 	= 'MRiskval';
		$this->MCrisk 		= 'MCrisk';
		$this->MBank 		= 'MBank';
		$this->MSrisk 		= 'MSrisk';
		$this->TTrace 		= 'TTrace';
		$this->MTrans 		= 'MTrans';
		$this->MHost 		= 'MHost';
		$this->TJfbls 		= 'TJfbls';
		$this->TKfls 		= 'TKfls';
		$this->MExcel 		= 'MExcel';
	}
	
	/*
	* 商户风险自动评调设置
	**/
	public function riskrun() {
		$post = I('post');
		if($post['submit'] == "riskrun"){
			$where = "1=1";
			//商户风险等级
			if($post['SHOP_GRADE']) {
				$where .= " and SHOP_GRADE = '".$post['SHOP_GRADE']."'";
			}
			//MCC大类
			if($post['MCC_TYPE']) {
				$where .= " and MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//MCC类别
			if($post['MCC_CODE']) {
				$where .= " and MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//分页
			$count = D($this->MRiskrun)->countRiskrun($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MRiskrun)->getRiskrunlist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('mcc_type',			C('MCC_TYPE'));			//MCC
		$this->assign('shop_grade',			C('SHOP_GRADE'));		//风险级别
		$this->assign('riskrun_status',		C('RISKRUN_STATUS'));	//风险规则状态
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 商户风险自动评调设置	详情
	**/
	public function riskrun_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MRiskrun)->findRiskrun("RISKRUN_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('mcc_type',			C('MCC_TYPE'));			//MCC
		$this->assign('shop_grade',			C('SHOP_GRADE'));		//风险级别
		$this->assign('riskrun_status',		C('RISKRUN_STATUS'));	//风险规则状态
		$this->assign ('info', 				$info);
		$this->display();
	}
	/*
	* 商户风险自动评调设置	修改
	**/
	public function riskrun_edit() {
		$post = I('post');
		if($post['submit'] == "riskrun_edit") {
			//验证
			if(empty($post['RISKRUN_BOUND'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'RISKRUN_BOUND'		=>	$post['RISKRUN_BOUND'],
				'RISKRUN_STATUS'	=>	$post['RISKRUN_STATUS'],
				'RISKRUN_REMARK'	=>	$post['RISKRUN_REMARK']
			);
			$res = D($this->MRiskrun)->updateRiskrun("RISKRUN_ID='".$post['RISKRUN_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MRiskrun)->findRiskrun("RISKRUN_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('mcc_type',			C('MCC_TYPE'));			//MCC
		$this->assign('shop_grade',			C('SHOP_GRADE'));		//风险级别
		$this->assign('riskrun_status',		C('RISKRUN_STATUS'));	//风险规则状态
		$this->assign ('info', 				$info);
		$this->display('riskrun_add');
	}
	
	
	
	/*
	* 风险规则设置
	**/
	public function riskrule() {
		$post = I('post');
		if($post['submit'] == "riskrule"){
			$where = "1=1";
			//风险规则类型
			if($post['RULE_TYPE'] != '') {
				$where .= " and RULE_TYPE = '".$post['RULE_TYPE']."'";
			}
			//风控规则状态
			if($post['RULE_STATUS'] !='') {
				$where .= " and RULE_STATUS = '".$post['RULE_STATUS']."'";
			}
			//分页
			$count = D($this->MRiskrule)->countRiskrule($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MRiskrule)->getRiskrulelist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('rule_type',			C('RULE_TYPE'));	//风险规则类型
		$this->assign('rule_mode',			C('RULE_MODE'));	//风险规则模式
		$this->assign('rule_status',		C('RULE_STATUS'));	//风险规则模式
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 风险规则设置	添加
	**/
	public function riskrule_add() {
		$post = I('post');
		if($post['submit'] == "riskrule_add") {
			//验证
			if(empty($post['RULE_NAME'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'RULE_TYPE'		=>	$post['RULE_TYPE'],
				'RULE_NAME'		=>	$post['RULE_NAME'],
				'RULE_MODE'		=>	$post['RULE_MODE'],
				'RULE_STATUS'	=>	$post['RULE_STATUS'],
				'RULE_REMARK'	=>	$post['RULE_REMARK']
			);
			$res = D($this->MRiskrule)->addRiskrule($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign('rule_type',			C('RULE_TYPE'));	//风险规则类型
		$this->assign('rule_mode',			C('RULE_MODE'));	//风险规则模式
		$this->assign('rule_status',		C('RULE_STATUS'));	//风险规则模式
		$this->display();
	}
	/*
	* 风险规则设置	详情
	**/
	public function riskrule_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MRiskrule)->findRiskrule("RULE_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('rule_type',			C('RULE_TYPE'));	//风险规则类型
		$this->assign('rule_mode',			C('RULE_MODE'));	//风险规则模式
		$this->assign('rule_status',		C('RULE_STATUS'));	//风险规则模式
		$this->assign ('info', 				$info);
		$this->display();
	}
	/*
	* 风险规则设置	修改
	**/
	public function riskrule_edit() {
		$post = I('post');
		if($post['submit'] == "riskrule_edit") {
			//验证
			if(empty($post['RULE_NAME'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'RULE_TYPE'		=>	$post['RULE_TYPE'],
				'RULE_NAME'		=>	$post['RULE_NAME'],
				'RULE_MODE'		=>	$post['RULE_MODE'],
				'RULE_STATUS'	=>	$post['RULE_STATUS'],
				'RULE_REMARK'	=>	$post['RULE_REMARK']
			);
			$res = D($this->MRiskrule)->updateRiskrule("RULE_ID='".$post['RULE_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MRiskrule)->findRiskrule("RULE_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('rule_type',			C('RULE_TYPE'));	//风险规则类型
		$this->assign('rule_mode',			C('RULE_MODE'));	//风险规则模式
		$this->assign('rule_status',		C('RULE_STATUS'));	//风险规则模式
		$this->assign ('info', 				$info);
		$this->display('riskrule_add');
	}
	/*
	* 风险规则设置	删除
	**/
	public function riskrule_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$res = D($this->MRiskrule)->delRiskrule("RULE_ID = '".$id."'");
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg']);
	}
	
	

	/*
	* 风控阀值管理
	**/
	public function riskval() {
		$post = I('post');
		if($post['submit'] == "riskval"){
			$where = "1=1";
			//适用商户等级
			if($post['SHOP_GRADE']) {
				$where .= " and va.SHOP_GRADE = '".$post['SHOP_GRADE']."'";
			}
			//适用商户分类
			if($post['MCC_TYPE']) {
				$where .= " and va.MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//风控规则类型
			if($post['RULE_TYPE'] != '') {
				$where .= " and ru.RULE_TYPE = '".$post['RULE_TYPE']."'";
			}
			//风控规则状态
			if($post['RISK_STATUS'] != '') {
				$where .= " and va.RISK_STATUS = '".$post['RISK_STATUS']."'";
			}
			//分页
			$count = D($this->MRiskval)->countRiskval($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MRiskval)->getRiskvallist($where, 'va.*,ru.RULE_NAME', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('shop_grade',			C('SHOP_GRADE'));	//商户级别
		$this->assign('mcc_type',			C('MCC_TYPE'));		//商户分类
		$this->assign('rule_type',			C('RULE_TYPE'));	//风险规则类型
		$this->assign('risk_status',		C('RISK_STATUS'));	//风险规则阀值状态
		$this->assign('risk_t',				C('RISK_T'));		//风险周期
		$this->assign('risk_active',		C('RISK_ACTIVE'));	//风险动作
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 风控阀值管理	添加
	**/
	public function riskval_add() {
		$post = I('post');
		if($post['submit'] == "riskval_add") {
			//验证
			if(empty($post['RISK_TARGET']) || empty($post['ACTIVE_BOUND'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'RULE_ID'		=>	$post['RULE_ID'],
				'RISK_STATUS'	=>	$post['RISK_STATUS'],
				'RISK_T'		=>	$post['RISK_T'],
				'SHOP_GRADE'	=>	$post['SHOP_GRADE'],
				'MCC_TYPE'		=>	$post['MCC_TYPE']?$post['MCC_TYPE']:'',
				'MCC_CODE'		=>	$post['MCC_CODE']?$post['MCC_CODE']:'',
				'RISK_PARA'		=>	0,
				'RISK_TARGET'	=>	$post['RISK_TARGET'],
				'ACTIVE_BOUND'	=>	$post['ACTIVE_BOUND'],
				'RISK_ACTIVE'	=>	$post['RISK_ACTIVE'],
				'RISK_REMARK'	=>	$post['RISK_REMARK']
			);
			$res = D($this->MRiskval)->addRiskval($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//规则列表
		$riskrule_list = D($this->MRiskrule)->getRiskrulelist("RULE_ID!=''", 'RULE_ID,RULE_NAME');
		$this->assign('shop_grade',			C('SHOP_GRADE'));	//商户级别
		$this->assign('mcc_type',			C('MCC_TYPE'));		//商户分类
		$this->assign('risk_t',				C('RISK_T'));		//风险周期
		$this->assign('risk_active',		C('RISK_ACTIVE'));	//风险动作
		$this->assign('risk_status',		C('RISK_STATUS'));	//风险规则阀值状态
		$this->assign('riskrule_list',		$riskrule_list);
		$this->display();
	}
	/*
	* 风控阀值管理	详情
	**/
	public function riskval_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MRiskval)->findRiskval("VAL_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//规则详情
		$riskrule_data = D($this->MRiskrule)->findRiskrule("RULE_ID ='".$info['RULE_ID']."'", 'RULE_NAME');
		$info['RULE_NAME'] = $riskrule_data['RULE_NAME'];
		$this->assign('shop_grade',			C('SHOP_GRADE'));	//商户级别
		$this->assign('mcc_type',			C('MCC_TYPE'));		//商户分类
		$this->assign('risk_t',				C('RISK_T'));		//风险周期
		$this->assign('risk_active',		C('RISK_ACTIVE'));	//风险动作
		$this->assign('risk_status',		C('RISK_STATUS'));	//风险规则阀值状态
		$this->assign('riskrule_list',		$riskrule_list);
		$this->assign('info',				$info);
		$this->display();
	}
	/*
	* 风控阀值管理	修改
	**/
	public function riskval_edit() {
		$post = I('post');
		if($post['submit'] == "riskval_edit") {
			//验证
			if(empty($post['RISK_TARGET']) || empty($post['ACTIVE_BOUND'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'RULE_ID'		=>	$post['RULE_ID'],
				'RISK_STATUS'	=>	$post['RISK_STATUS'],
				'RISK_T'		=>	$post['RISK_T'],
				'SHOP_GRADE'	=>	$post['SHOP_GRADE'],
				'MCC_TYPE'		=>	$post['MCC_TYPE'],
				'MCC_CODE'		=>	$post['MCC_CODE'],
				'RISK_TARGET'	=>	$post['RISK_TARGET'],
				'ACTIVE_BOUND'	=>	$post['ACTIVE_BOUND'],
				'RISK_ACTIVE'	=>	$post['RISK_ACTIVE'],
				'RISK_REMARK'	=>	$post['RISK_REMARK']
			);
			$res = D($this->MRiskval)->updateRiskval("VAL_ID='".$post['VAL_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MRiskval)->findRiskval("VAL_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//规则列表
		$riskrule_list = D($this->MRiskrule)->getRiskrulelist("RULE_ID!=''", 'RULE_ID,RULE_NAME');
		$this->assign('shop_grade',			C('SHOP_GRADE'));	//商户级别
		$this->assign('mcc_type',			C('MCC_TYPE'));		//商户分类
		$this->assign('risk_t',				C('RISK_T'));		//风险周期
		$this->assign('risk_active',		C('RISK_ACTIVE'));	//风险动作
		$this->assign('risk_status',		C('RISK_STATUS'));	//风险规则阀值状态
		$this->assign('riskrule_list',		$riskrule_list);
		$this->assign('info',				$info);
		$this->display('riskval_add');
	}
	/*
	* 风控阀值管理	停用
	**/
	public function riskval_close() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MRiskval)->findRiskval("VAL_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['RISK_STATUS'] == 1){
			$this->wrong("当前状态下不允许暂停操作！");			
		}
		$res = D($this->MRiskval)->updateRiskval("VAL_ID = '".$id."'", array('RISK_STATUS'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('停用成功！');
	}
	/*
	* 风控阀值管理	恢复开通
	**/
	public function riskval_open() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MRiskval)->findRiskval("VAL_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['RISK_STATUS'] == 0){
			$this->wrong("当前状态下不允许恢复操作！");			
		}
		$res = D($this->MRiskval)->updateRiskval("VAL_ID = '".$id."'", array('RISK_STATUS'=> 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('恢复成功！');
	}
	
	
	
	/*
	* 黑名单卡管理
	**/
	public function crisk() {
		$post = I('post');
		if($post['submit'] == "crisk"){
			$where = "1=1";
			//发卡银行
			if($post['ISSUE_CODE']) {
				$where .= " and ISSUE_CODE = '".$post['ISSUE_CODE']."'";
			}
			//卡号
			if($post['CARD_NO']) {
				$where .= " and CARD_NO = '".$post['CARD_NO']."'";
			}
			//分页
			$count = D($this->MCrisk)->countCrisk($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MCrisk)->getCrisklist($where, '*', $p->firstRow.','.$p->listRows);
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
		$this->assign('cardblack_flag',		C('CARDBLACK_FLAG'));	//卡标识
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 黑名单卡管理	导入
	**/
	public function crisk_export() {
		$this->wrong('先不做！');
	}
	
	
	
	/*
	* 商户风险积数
	**/
	public function srisk() {
		$post = I('post');
		if($post['submit'] == "srisk"){
			$where = "1=1";
			//归属
			$getlevel = filter_data('plv');	//列表查询
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and sh.BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
			}
			if($post['PARTNER_MAP_ID']){
				$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
				$where .= " and sh.PARTNER_MAP_ID in (".$pids.")";
			}
			
			//商户风险等级
			if($post['SHOP_GRADE']) {
				$where .= " and sr.SHOP_GRADE = '".$post['SHOP_GRADE']."'";
			}
			//商户编号
			if($post['SHOP_NO']) {
				$where .= " and sh.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//分页
			$count = D($this->MSrisk)->countSrisk($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MSrisk)->getSrisklist($where, 'sr.*,sh.SHOP_NAME,sh.SHOP_NO', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('shop_grade',			C('SHOP_GRADE'));	//商户级别
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 商户风险积数	详情
	**/
	public function srisk_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MSrisk)->findmoreSrisk("sr.SHOP_MAP_ID='".$id."'", 'sr.*,sh.SHOP_NAME,sh.SHOP_NO');
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('shop_grade',			C('SHOP_GRADE'));	//商户级别
		$this->assign('info',				$info);
		$this->display();
	}
	/*
	* 商户风险积数	修改
	**/
	public function srisk_edit() {
		$post = I('post');
		if($post['submit'] == "srisk_edit") {
			//验证
			if(empty($post['SHOP_RISKBOUND']) || empty($post['POSMODE_PER']) || empty($post['REFUND_PER']) || empty($post['CBREQ_PER']) || empty($post['CBBACK_PER']) || empty($post['BUSITIME_ABS']) || empty($post['BALANCE_PER'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'SHOP_GRADE'		=>	$post['SHOP_GRADE'],
				'SHOP_RISKBOUND'	=>	$post['SHOP_RISKBOUND'],
				'POSMODE_PER'		=>	$post['POSMODE_PER'],
				'REFUND_PER'		=>	$post['REFUND_PER'],
				'CBREQ_PER'			=>	$post['CBREQ_PER'],
				'CBBACK_PER'		=>	$post['CBBACK_PER'],
				'BUSITIME_ABS'		=>	$post['BUSITIME_ABS'],
				'BALANCE_PER'		=>	$post['BALANCE_PER'],
				'RES'				=>	$post['RES']
			);
			$res = D($this->MSrisk)->updateSrisk("SHOP_MAP_ID='".$post['SHOP_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MSrisk)->findmoreSrisk("sr.SHOP_MAP_ID='".$id."'", 'sr.*,sh.SHOP_NAME,sh.SHOP_NO');
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('shop_grade',			C('SHOP_GRADE'));	//商户级别
		$this->assign('info',				$info);
		$this->display('srisk_add');
	}
	
	
	
	/*
	* 风险案例管理
	**/
	public function riskworks() {
		$post = I('post');
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "riskworks"){
			$where = "t.FEE_STATUS = 0 and t.ERROR_POINT > 0";	//FEE_STATUS 0正常1超扣
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and t.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and t.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}
			//流水归属
			$getlevel = filter_data('plv');	//列表查询
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and (t.SBRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."' or t.VBRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."')";
			}
			if($post['PARTNER_MAP_ID']){
				$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
				$where .= " and (t.SPARTNER_MAP_ID in (".$pids.") or t.VPARTNER_MAP_ID in (".$pids."))";
			}
			
			//交易类型
			if($post['TRANS_SUBID']) {
				$where .= " and t.TRANS_SUBID = '".$post['TRANS_SUBID']."'";
			}
			//处理结果
			if($post['TRACE_STATUS'] != '') {
				$where .= " and t.TRACE_STATUS = '".$post['TRACE_STATUS']."'";
			}
			//交易金额	开始
			if($post['TRANS_AMT_A']) {
				$where .= " and t.TRANS_AMT >= '".setMoney($post['TRANS_AMT_A'], '2')."'";
			}
			//交易金额	结束
			if($post['TRANS_AMT_B']) {
				$where .= " and t.TRANS_AMT <= '".setMoney($post['TRANS_AMT_B'], '2')."'";
			}
			//支付通道
			if($post['HOST_MAP_ID']) {
				$where .= " and t.HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
			}
			//商户名称
			if($post['SHOP_NAMEAB']) {
				$where .= " and t.SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
			}
			//终端号
			if($post['POS_NO']) {
				$where .= " and t.POS_NO = '".$post['POS_NO']."'";
			}
			//流水号
			if($post['POS_TRACE']) {
				$where .= " and t.POS_TRACE = '".$post['POS_TRACE']."'";
			}
			//分页
			$count = D($this->TTrace)->countTrace($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TTrace)->getTracelist($where, 't.*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'list', 		$list );
		}
		$this->assign ( 'postdata', 	$post );
		
		//Excel导出参数
		unset($post['submit']);
		$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		
		//交易列表	筛选
		$trans_list = D($this->MTrans)->getTranslist("TRANS_FLAG2 = 1", 'TRANS_SUBID,TRANS_NAME');
		//通道列表
		$host_list = D($this->MHost)->getHostlist("HOST_STATUS = 0", 'HOST_MAP_ID,HOST_NAME');
		//时间选择
		$timedata = array(
			'jintian_b'		=>	date('Y-m-d'),
			'jintian_n'		=>	date('Y-m-d'),
			'zuotian_b'		=>	date('Y-m-d', strtotime('-1 day')),
			'zuotian_n'		=>	date('Y-m-d', strtotime('-1 day')),
			'benyue_b'		=>	date('Y-m-d', getmonthtime(1)[0]),
			'benyue_n'		=>	date('Y-m-d'),
			'shangyue_b'	=>	date('Y-m-d', getmonthtime(4)[0]),
			'shangyue_n'	=>	date('Y-m-d', getmonthtime(4)[1]),
		);
		$this->assign('trans_list', 		$trans_list );
		$this->assign('host_list', 			$host_list );
		$this->assign('timedata', 			$timedata );
		$this->assign('trace_status', 		C('TRACE_STATUS') );	//流水标志
		$this->assign('fee_status', 		C('FEE_STATUS') );		//超扣标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display('Trace:trace');
	}
	/*
	* 风险案例管理
	**/
	public function riskworks_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TTrace)->findTrace("SYSTEM_REF='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//通道信息
		$host_data	= D($this->MHost)->findHost("HOST_MAP_ID = '".$info['HOST_MAP_ID']."'", 'HOST_NAME');
		$info['HOST_NAME'] = $host_data['HOST_NAME'];		
		//积分宝运营流水
		$jfbls_info = D($this->TJfbls)->findJfbls("SYSTEM_REF='".$id."'");
		//积分宝卡费流水
		$kfls_info 	= D($this->TKfls)->findKfls("SYSTEM_REF='".$id."'");		
		
		$this->assign('trace_status', 		C('TRACE_STATUS') );	//流水标志
		$this->assign('fee_status', 		C('FEE_STATUS') );		//超扣标志
		$this->assign('error_point', 		C('ERROR_POINT') );		//差错标志
		$this->assign('vip_flag', 			C('VIP_FLAG') );		//会员卡类型
		$this->assign('clear_sflag', 		C('CLEAR_SFLAG') );		//商户划转标志
		$this->assign('settle_stype', 		C('SETTLE_STYPE') );	//商户结算方式
		$this->assign('clear_aflag', 		C('CLEAR_AFLAG') );		//代理划转标志
		$this->assign('settle_atype', 		C('SETTLE_ATYPE') );	//代理结算方式
		$this->assign('clear_cflag', 		C('CLEAR_CFLAG') );		//渠道划转标志
		$this->assign('settle_cflag', 		C('SETTLE_CFLAG') );	//渠道结算方式
		$this->assign('clear_hflag', 		C('CLEAR_HFLAG') );		//通道对账标志
		$this->assign('settle_hflag', 		C('SETTLE_HFLAG') );	//通道结算方式
		$this->assign('clear_pflag', 		C('CLEAR_PFLAG') );		//分润对账标志
		$this->assign('settle_ptype', 		C('SETTLE_PTYPE') );	//分润结算方式
		$this->assign('jfb_clear_flag', 	C('JFB_CLEAR_FLAG') );	//分润结算方式
		$this->assign('info', 				$info);
		$this->assign('jfbls_info', 		$jfbls_info);
		$this->assign('kfls_info', 			$kfls_info);
		$this->display('Trace:trace_show');
	}	
	/*
	* 风险案例管理	导出
	**/
	public function riskworks_export() {
		$post  = array(
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'TRANS_SUBID'		=>	I('TRANS_SUBID'),
			'TRACE_STATUS'		=>	I('TRACE_STATUS'),
			'TRANS_AMT_A'		=>	I('TRANS_AMT_A'),
			'TRANS_AMT_B'		=>	I('TRANS_AMT_B'),
			'HOST_MAP_ID'		=>	I('HOST_MAP_ID'),
			'SHOP_NAMEAB'		=>	I('SHOP_NAMEAB'),
			'POS_NO'			=>	I('POS_NO'),
			'POS_TRACE'			=>	I('POS_TRACE'),
		);		
		$where = "t.FEE_STATUS = 0 and t.ERROR_POINT > 0";	//FEE_STATUS 0正常1超扣
		//交易日期	开始
		if($post['SYSTEM_DATE_A']) {
			$where .= " and t.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
		}
		//交易日期	结束
		if($post['SYSTEM_DATE_B']) {
			$where .= " and t.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
		}
		//流水归属
		if($post['BRANCH_MAP_ID']){
			$where .= " and (t.SBRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."' or t.VBRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."')";
		}
		if($post['PARTNER_MAP_ID']){
			$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
			$where .= " and (t.SPARTNER_MAP_ID in (".$pids.") or t.VPARTNER_MAP_ID in (".$pids."))";
		}		
		//交易类型
		if($post['TRANS_SUBID']) {
			$where .= " and t.TRANS_SUBID = '".$post['TRANS_SUBID']."'";
		}
		//处理结果
		if($post['TRACE_STATUS'] != '') {
			$where .= " and t.TRACE_STATUS = '".$post['TRACE_STATUS']."'";
		}
		//交易金额	开始
		if($post['TRANS_AMT_A']) {
			$where .= " and t.TRANS_AMT >= '".setMoney($post['TRANS_AMT_A'], '2')."'";
		}
		//交易金额	结束
		if($post['TRANS_AMT_B']) {
			$where .= " and t.TRANS_AMT <= '".setMoney($post['TRANS_AMT_B'], '2')."'";
		}
		//支付通道
		if($post['HOST_MAP_ID']) {
			$where .= " and t.HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
		}
		//商户名称
		if($post['SHOP_NAMEAB']) {
			$where .= " and t.SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
		}
		//终端号
		if($post['POS_NO']) {
			$where .= " and t.POS_NO = '".$post['POS_NO']."'";
		}
		//流水号
		if($post['POS_TRACE']) {
			$where .= " and t.POS_TRACE = '".$post['POS_TRACE']."'";
		}
		
		//计算
		$count   = D($this->TTrace)->countTrace($where);
		$numPort = floor($count/C('PAGE_COUNT_EXPORT'));
		$urlPort = __ACTION__.'?submit=ajax&'.http_build_query($post);
		$strPort = '';
		if($count > 0){
			for($i=0; $i<=$numPort; $i++){
				$strPort .= '<p><a href="'.$urlPort.'&p='.($i).'"><button class="ch-btn-skin ch-btn-small ch-icon-copy">文件('.($i+1).')</button></a></p>';
			}
		}else{
			$strPort .= '<p>暂无数据可下载~</p>';
		}
		$this->assign ( 'strPort', 	$strPort );
		
		//导出
		$submit = I('submit');
		$p 		= I('p');
		if($submit == 'ajax'){
			$bRow = $p * C('PAGE_COUNT_EXPORT');
			$eRow = C('PAGE_COUNT_EXPORT');
			$list = D($this->TTrace)->getTracelist($where, '*', $bRow.','.$eRow);
				
			//导出操作
			$xlsname = '风险案例文件('.($p+1).')';
			$xlscell = array(
				array('TRANS_NAME',		'交易类型'),
				array('SHOP_NAMEAB',	'商户名称'),
				array('CARD_NO',		'银行卡号'),
				array('TRANS_AMT',		'交易金额'),
				array('TRACE_STATUS',	'结果'),		
				array('VIP_CARDNO',		'会员卡号'),
				array('JIFENLV',		'积分率'),
				array('SYSTEM_DATE',	'交易时间'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'TRANS_NAME'	=>	$val['TRANS_NAME'],
					'SHOP_NAMEAB'	=>	$val['SHOP_NAMEAB'],
					'CARD_NO'		=>	setCard_no($val['CARD_NO']),
					'TRANS_AMT'		=>	setMoney($val['TRANS_AMT'], 2, 2),
					'TRACE_STATUS'	=>	C('TRACE_STATUS')[$val['TRACE_STATUS']],
					'VIP_CARDNO'	=>	$val['VIP_CARDNO']."\t",
					'JIFENLV'		=>	set_jifenlv($val['SHOP_NO']),
					'SYSTEM_DATE'	=>	$val['SYSTEM_DATE'].' '.$val['SYSTEM_TIME']."\t",
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
}