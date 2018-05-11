<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @ljf  合作伙伴管理
// +----------------------------------------------------------------------
class AgentController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->MAlevel	= 'MAlevel';
		$this->MAgent	= 'MAgent';
		$this->MAcert	= 'MAcert';
		$this->MAbact	= 'MAbact';
		$this->MAmdr	= 'MAmdr';
		$this->MBranch	= 'MBranch';
		$this->MHost	= 'MHost';
		$this->MAauth	= 'MAauth';
		$this->MAcls	= 'MAcls';
		$this->MCheck	= 'MCheck';
		$this->MShop	= 'MShop';
		$this->MBank	= 'MBank';
		$this->MPos		= 'MPos';
	}	
	
	/*
	* 合作伙伴等级维护 列表
	**/
	public function alevel() {
		$post = I('post');
		if($post['submit'] == "alevel"){
			$where = "PLEVEL_MAP_ID !='' ";
			//等级
			if($post['PLEVEL_NAME']) {
				$where .= " and PLEVEL_NAME = '".$post['PLEVEL_NAME']."'";
			}
			//分页
			$count = D($this->MAlevel)->countAlevel($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MAlevel)->getAlevellist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('plevel_name',		C('PLEVEL_NAME'));	//合作伙伴等级
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 合作伙伴等级维护 详情
	**/
	public function plevel_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MAlevel)->findAlevel("PLEVEL_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('plevel_name',		C('PLEVEL_NAME'));	//合作伙伴等级
		$this->assign ('info', $info);
		$this->display();
	}
	/*
	* 合作伙伴等级维护 修改
	**/
	public function plevel_edit() {
		$post = I('post');
		if($post['submit'] == "plevel_edit") {
			//验证
			if(empty($post['JOIN_FEE']) || empty($post['JOINFEE_BEGIN']) || empty($post['JOINFEE_END']) || empty($post['FUND_AMT']) || empty($post['FUND_BEGIN']) || empty($post['FUND_END'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'JOIN_FEE'		=>	setMoney($post['JOIN_FEE'], '6'),
				'JOINFEE_BEGIN'	=>	setMoney($post['JOINFEE_BEGIN'], '6'),
				'JOINFEE_END'	=>	setMoney($post['JOINFEE_END'], '6'),
				'FUND_AMT'		=>	setMoney($post['FUND_AMT'], '6'),
				'FUND_BEGIN'	=>	setMoney($post['FUND_BEGIN'], '6'),
				'FUND_END'		=>	setMoney($post['FUND_END'], '6')
			);
			$res = D($this->MAlevel)->updateAlevel("PLEVEL_MAP_ID='".$post['PLEVEL_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MAlevel)->findAlevel("PLEVEL_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('plevel_name',		C('PLEVEL_NAME'));	//合作伙伴等级
		$this->assign('info', 				$info);	
		$this->display('plevel_add');
	}

	
	
	/*
	* 合作伙伴管理 列表
	**/
	public function agent() {
		$post = I('post');
		//if($post['submit'] == "agent"){
			$where = "a.AGENT_MAP_ID != ''";
			//所属分支
			if($post['BRANCH_MAP_ID']) {
				$where .= " and a.BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
			}
			//合作伙伴级别
			if($post['PLEVEL_NAME']) {
				$where .= " and a.AGENT_LEVEL = '".$post['PLEVEL_NAME']."'";
			}
			//合作伙伴状态
			if($post['AGENT_STATUS']!='') {
				$where .= " and a.AGENT_STATUS = '".$post['AGENT_STATUS']."'";
			}
			//合作伙伴名称
			if($post['AGENT_NAME']) {
				$where .= " and a.AGENT_NAME like '%".$post['AGENT_NAME']."%'";
			}
			//分页
			$count = D($this->MAgent)->countAgent($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MAgent)->getAgentlist($where, 'a.*, c.PROVINCE_NAME, c.CITY_NAME, c.CITY_S_NAME', $p->firstRow.','.$p->listRows);
			//统计商户数量
			foreach ($list as $key => $val) {
				$poswhere = "p.AGENT_MAP_ID = ".$val['AGENT_MAP_ID'];
				$shopwhere = "s.AGENT_MAP_ID = ".$val['AGENT_MAP_ID'];
				$list[$key]['POS_COUNT'] = D($this->MPos)->countPos($poswhere);
				$list[$key]['SHOP_COUNT'] = D($this->MShop)->countShop($shopwhere);
			}	

			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		//}
		//所属分支
		$branchsel = D($this->MBranch)->getBranchlist('','BRANCH_MAP_ID,BRANCH_NAME');
		$this->assign ('branchsel', $branchsel);
		
		$this->assign('agent_status_arr',C('CHECK_POINT.all'));	//代理商审核状态数组
		$this->assign('plevel_arr',C('PLEVEL_NAME'));			//级别名称
		\Cookie::set ('_currentUrl_', 	__SELF__);	
		$this->display();
	}
	/*
	* 合作伙伴管理 详情
	**/
	public function agent_show($tpl = 'agent_show') {		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}

		//获取合作伙伴基本信息
		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		//获取合作伙伴证照信息
		$acert_info = D($this->MAcert)->findAcert("AGENT_MAP_ID='".$id."'");
		//获取合作伙伴权限信息
		$aauth_info = D($this->MAauth)->findAauth("AGENT_MAP_ID='".$id."'");
		//获取合作伙伴结算方式信息
		$acls_info  = D($this->MAcls)->findAcls("AGENT_MAP_ID='".$id."'");
		//获取合作伙伴银行帐户信息
		$abact_info = D($this->MAbact)->findAbact("AGENT_MAP_ID='".$id."'");
		if(empty($agent_info) || empty($acert_info) || empty($aauth_info) ||  empty($acls_info) ||  empty($abact_info)){
			$this->wrong("参数数据出错！");
		}
		
		$this->assign ('bank_flag_redio',	 C('AGENT_BANK_FLAG'));		//结算标志
		$this->assign ('settle_t_unit',		 C('SETTLE_T_UNIT'));		//结算周期
		$this->assign ('auth_trans_checked', str_split($aauth_info['AUTH_TRANS_MAP']));	//交易开通

		$this->assign ('agent_info', 	$agent_info);	//基础信息
		$this->assign ('acert_info', 	$acert_info);	//证照信息
		$this->assign ('aauth_info', 	$aauth_info);	//权限信息
		$this->assign ('acls_info', 	$acls_info);	//结算方式
		$this->assign ('abact_info', 	$abact_info);	//银行帐户
		$this->display($tpl);
	}
	/*
	* 合作伙伴管理 添加
	**/
	public function agent_add() {
		$agent = I('agent');
		$acert = I('acert');
		$aauth = I('aauth');
		$acls  = I('acls');
		$abact = I('abact');
		if($agent['submit'] == "agent_add") {
			//基本数据验证
			if(empty($agent['AGENT_NAME']) || empty($agent['END_TIME']) || empty($agent['ZIP']) || 
				empty($agent['JOIN_FEE']) || empty($agent['FUND_AMT']) || empty($agent['MANAGER']) || 
				empty($agent['MOBILE']) || empty($agent['SALER_ID']) || empty($agent['CITY_NO'])){
				$this->wrong("请填写基本信息必填项! ");
			}
			//结算方式验证
			if(empty($acls['SETTLE_TOP_AMT']) || empty($acls['SETTLE_FREE_AMT']) || empty($acls['SETTLE_OFF_AMT']) || 
				empty($acls['SETTLE_OFF_FEE']) || $acls['SETTLE_FEE']=''){
				$this->wrong("请填写结算方式必填项! ");
			}
			//代理商编号
			$home = session('HOME');
			//获得当前用户的AGENT_NO
			if ($home['AGENT_MAP_ID']!=0) {
				$where = 'AGENT_MAP_ID = '.$home['AGENT_MAP_ID'];
				$agent_info = D($this->MAgent)->findAgent($where, $field='a.AGENT_NO, a.AGENT_LEVEL');
				if ($agent_info['AGENT_NO']) {
					$agent_no = $agent_info['AGENT_NO'];
				}
			}
			switch ($agent['AGENT_LEVEL']) {
				case '1':
					$a_no = $agent_info['AGENT_NO'] ? $agent_info['AGENT_NO'] : "100000000000";
					$where = 'AGENT_NO like "%000000000" and AGENT_NO >= "'.$a_no.'" and AGENT_NO <="999000000000"';
					$maxag_no = D($this->MAgent)->findMaxAgent($where);
					if($maxag_no >= 999000000000){
						$this->wrong("该级别分公司编号超出最大范围,数据添加失败！");
					}
					$maxag_no = $maxag_no ? $maxag_no : '100000000000';
					$AGENT_NO = $maxag_no+1000000000;

					break;
				case '2':
					$a_no = $agent_info['AGENT_NO'] ? $agent_info['AGENT_NO'] : "100001000000";
					$where = 'AGENT_NO like "%000000" and AGENT_NO >= "'.$a_no.'" and AGENT_NO <="100999000000"';
					$maxag_no = D($this->MAgent)->findMaxAgent($where);
					if($maxag_no >= 100999000000){
						$this->wrong("该级别分公司编号超出最大范围,数据添加失败！");
					}
					$maxag_no = $maxag_no ? $maxag_no : '100001000000';
					$AGENT_NO = $maxag_no+1000000;
					break;
				case '3':
					$a_no = $agent_info['AGENT_NO'] ? $agent_info['AGENT_NO'] : "100000001000";
					$where = 'AGENT_NO like "%000" and AGENT_NO >= "'.$a_no.'" and AGENT_NO <="100000999000"';
					$maxag_no = D($this->MAgent)->findMaxAgent($where);
					if($maxag_no >= 100000999000){
						$this->wrong("该级别分公司编号超出最大范围,数据添加失败！");
					}
					$maxag_no = $maxag_no ? $maxag_no : '100000001000';
					$AGENT_NO = $maxag_no+1000;
					break;
				default:
					$this->wrong("参数数据出错！");
					break;
			}

			//组装基本数据
			$agent_data = array(
				'BRANCH_MAP_ID'	=>	$agent['BRANCH_MAP_ID'],
				'AGENT_NO'		=>	$AGENT_NO,
				'AGENT_LEVEL'	=>	$agent['AGENT_LEVEL'],
				'AGENT_MAP_ID_P'=>	$agent['AGENT_MAP_ID_P'] ? $agent['AGENT_MAP_ID_P'] : 0,
				'AGENT_NAME'	=>	$agent['AGENT_NAME'],
				'AGENT_STATUS'	=>	6,
				'AGENT_LEVEL'	=>	$agent['AGENT_LEVEL'],
				'AUTH_ZONE'		=>	$agent['AUTH_ZONE'] ? $agent['AUTH_ZONE'] : 0,
				'JOIN_FEE'		=>	setMoney($agent['JOIN_FEE'], 6),
				'FUND_AMT'		=>	setMoney($agent['FUND_AMT'], 6),
				'CITY_NO'		=>	$agent['CITY_NO'],				
				'ADDRESS'		=>	$agent['ADDRESS'],
				'ZIP'			=>	$agent['ZIP'],
				'TEL'			=>	$agent['TEL'],
				'MANAGER'		=>	$agent['MANAGER'],
				'MOBILE'		=>	$agent['MOBILE'],
				'EMAIL'			=>	$agent['EMAIL'],
				'SALER_ID'		=>	$agent['SALER_ID'],
				'SALER_NAME'	=>	$agent['SALER_NAME'],
				'CREATE_USERID'	=>	$home['USER_ID'],
				'CREATE_USERNAME'=>	$home['USER_NAME'],
				'CREATE_TIME'	=>	date("Ymdhis"),
				'END_TIME'		=>	date("Ymdhis",strtotime($agent['END_TIME']." 23:59:59"))
			);
			//基础数据入库
			$agent_res = D($this->MAgent)->addAgent($agent_data);
			if($agent_res['state']!=0){
				$this->wrong('合作伙伴基础信息添加失败！');
			}

			//组装证件数据
			$acert_data = array(
				'AGENT_MAP_ID'	=>	$agent_res['AGENT_MAP_ID'],
				'AGENT_STATUS'	=>	0,
				'AGREEMENT_NO'	=>	$acert['AGREEMENT_NO'],
				'REG_ADDR'		=>	$acert['REG_ADDR'],
				'REG_ID'		=>	$acert['REG_ID'],
				'TAX_ID'		=>	$acert['TAX_ID'],
				'ORG_ID'		=>	$acert['ORG_ID'],
				'LP_NAME'		=>	$acert['LP_NAME'],
				'LP_ID'			=>	$acert['LP_ID'],
				'REGID_PHOTO'	=>	$acert['REGID_PHOTO'],
				'TAXID_PHOTO'	=>	$acert['TAXID_PHOTO'],
				'ORGID_PHOTO'	=>	$acert['ORGID_PHOTO'],
				'LP_D_PHOTO'	=>	$acert['LP_D_PHOTO'],
				'LP_R_PHOTO'	=>	$acert['LP_R_PHOTO'],
				'BANK_PHOTO'	=>	$acert['BANK_PHOTO'],
				'RES'			=>	$acert['RES']
			);
			//证件数据入库
			$acert_res = D($this->MAcert)->addAcert($acert_data);
			if($acert_res['state']!=0){
				$this->wrong('合作伙伴证件信息添加失败！');
			}

			//组装权限数据
			$aauth_data = array(
				'AGENT_MAP_ID'	=>	$agent_res['AGENT_MAP_ID'],
				'AGENT_STATUS'	=>	0,
				'HOST_MAP_ID'	=>	0,
				'AUTH_TRANS_MAP'=>	$aauth['AUTH_TRANS_MAP'],
				'AUTH_PAYS_MAP'	=>	''
			);
			//权限数据入库
			$aauth_res = D($this->MAauth)->addAauth($aauth_data);
			if($aauth_res['state']!=0){
				$this->wrong('合作伙伴权限信息添加失败！');
			}

			//组装结算方式数据
			$acls_data = array(
				'AGENT_MAP_ID'		=>	$agent_res['AGENT_MAP_ID'],
				'AGENT_STATUS'		=>	0,
				'SETTLE_T'			=>	$acls['SETTLE_T'],
				'SETTLE_T_UNIT'		=>	$acls['SETTLE_T_UNIT'],
				'SETTLE_TYPE'		=>	2,			//0：结算到代理商 1：结算到商户 默认2保留
				'SETTLE_FLAG'		=>	$acls['SETTLE_FLAG'] ? $acls['SETTLE_FLAG'] : 1,
				'SETTLE_TOP_AMT'	=>	setMoney($acls['SETTLE_TOP_AMT']),
				'SETTLE_FREE_AMT'	=>	setMoney($acls['SETTLE_FREE_AMT']),
				'SETTLE_OFF_AMT'	=>	setMoney($acls['SETTLE_OFF_AMT']),
				'SETTLE_OFF_FEE'	=>	$acls['SETTLE_OFF_FEE'] ? setMoney($acls['SETTLE_OFF_FEE']) : 0,
				'SETTLE_FEE'		=>	setMoney($acls['SETTLE_FEE'])
			);
			//结算方式数据入库
			$acls_res = D($this->MAcls)->addAcls($acls_data);
			if($acls_res['state']!=0){
				$this->wrong('合作伙伴结算方式信息添加失败！');
			}
			
			//组装银行账户数据
			$abact_data = array(
				'AGENT_MAP_ID'		=>	$agent_res['AGENT_MAP_ID'],
				'AGENT_STATUS'		=>	0,
				'AGENT_BANK_FLAG'	=>	$abact['AGENT_BANK_FLAG'],
				'BANKACCT_NAME1'	=>	$abact['BANKACCT_NAME1'] ? $abact['BANKACCT_NAME1'] : '',
				'BANKACCT_NO1'		=>	$abact['BANKACCT_NO1'] ? $abact['BANKACCT_NO1'] : '',
				'BANKACCT_BID1'		=>	$abact['BANKACCT_BID1'] ? $abact['BANKACCT_BID1'] : '',
				'BANK_NAME1'		=>	$abact['BANK_NAME1'] ? $abact['BANK_NAME1'] : '',
				'BANKACCT_NAME2'	=>	$abact['BANKACCT_NAME2'] ? $abact['BANKACCT_NAME2'] : '',
				'BANKACCT_NO2'		=>	$abact['BANKACCT_NO2'] ? $abact['BANKACCT_NO2'] : '',
				'BANKACCT_BID2'		=>	$abact['BANKACCT_BID2'] ? $abact['BANKACCT_BID2'] : '',
				'BANK_NAME2'		=>	$abact['BANK_NAME2'] ? $abact['BANK_NAME2'] : '',
			);
			//银行账户数据入库
			$abact_res = D($this->MAbact)->addAbact($abact_data);
			if($abact_res['state']!=0){
				$this->wrong('合作伙伴银行账户信息添加失败！');
			}
			$this->right($abact_res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//所属分支
		$branchsel = D($this->MBranch)->getBranchlist('','BRANCH_MAP_ID,BRANCH_NAME');
		//发卡行列表
		$banklist = D($this->MBank)->getBanklist('','ISSUE_CODE,BANK_NAME');
		$this->assign ('branchsel', $branchsel);	//所属分支
		$this->assign ('banklist', $banklist);		//发卡行列表
		$this->assign ('auth_trans_map_checkbox', 	C('AUTH_TRANS_MAP'));	//交易开通
		$this->assign ('bank_flag_redio', 			C('AGENT_BANK_FLAG'));	//结算标志
		$this->display();
	}	
	/*
	* 合作伙伴管理 修改
	**/
	public function agent_edit() {
		$agent = I('agent');
		$acert = I('acert');
		$aauth = I('aauth');
		$acls  = I('acls');
		$abact = I('abact');
		$home = session('HOME');
		if($agent['submit'] == "agent_edit") {			//基本数据验证
			if(empty($agent['AGENT_NAME']) || empty($agent['END_TIME']) || empty($agent['ZIP']) || 
				empty($agent['JOIN_FEE']) || empty($agent['FUND_AMT']) || empty($agent['MANAGER']) || 
				empty($agent['MOBILE']) || empty($agent['SALER_ID']) || empty($agent['CITY_NO'])){
				$this->wrong("请填写基本信息必填项! ");
			}
			
			//组装基本数据
			$agent_data = array(
				'BRANCH_MAP_ID'	=>	$agent['BRANCH_MAP_ID'],
				'AGENT_MAP_ID_P'=>	$agent['AGENT_MAP_ID_P'] ? $agent['AGENT_MAP_ID_P'] : 0,
				'AGENT_NAME'	=>	$agent['AGENT_NAME'],
				'AUTH_ZONE'		=>	$agent['AUTH_ZONE'] ? $agent['AUTH_ZONE'] : 0,
				'JOIN_FEE'		=>	setMoney($agent['JOIN_FEE'], 6),
				'FUND_AMT'		=>	setMoney($agent['FUND_AMT'], 6),
				'CITY_NO'		=>	$agent['CITY_NO'],				
				'ADDRESS'		=>	$agent['ADDRESS'],
				'ZIP'			=>	$agent['ZIP'],
				'TEL'			=>	$agent['TEL'],
				'MANAGER'		=>	$agent['MANAGER'],
				'MOBILE'		=>	$agent['MOBILE'],
				'EMAIL'			=>	$agent['EMAIL'],
				'SALER_ID'		=>	$agent['SALER_ID'],
				'SALER_NAME'	=>	$agent['SALER_NAME'],
				'CREATE_USERID'	=>	$home['USER_ID'],
				'CREATE_USERNAME'=>	$home['USER_NAME'],
				'CREATE_TIME'	=>	date("YmdHis"),
				'END_TIME'		=>	date("YmdHis",strtotime($agent['END_TIME']." 23:59:59"))
			);
			//基础数据更改
			$where = "AGENT_MAP_ID = '".$agent['AGENT_MAP_ID']."'";
			$agent_res = D($this->MAgent)->updateAgent($where, $agent_data);
			if($agent_res['state']!=0){
				$this->wrong('合作伙伴基础信息编辑失败');
			}

			if (!empty($acert)) {
				//组装证件数据
				$acert_data = array(
					'AGREEMENT_NO'	=>	$acert['AGREEMENT_NO'],
					'REG_ADDR'		=>	$acert['REG_ADDR'],
					'REG_ID'		=>	$acert['REG_ID'],
					'TAX_ID'		=>	$acert['TAX_ID'],
					'ORG_ID'		=>	$acert['ORG_ID'],
					'LP_NAME'		=>	$acert['LP_NAME'],
					'LP_ID'			=>	$acert['LP_ID'],
					'REGID_PHOTO'	=>	$acert['REGID_PHOTO'],
					'TAXID_PHOTO'	=>	$acert['TAXID_PHOTO'],
					'ORGID_PHOTO'	=>	$acert['ORGID_PHOTO'],
					'LP_D_PHOTO'	=>	$acert['LP_D_PHOTO'],
					'LP_R_PHOTO'	=>	$acert['LP_R_PHOTO'],
					'BANK_PHOTO'	=>	$acert['BANK_PHOTO']
				);
				//证件数据变更
				$acert_res = D($this->MAcert)->updateAcert($where, $acert_data);
				if($acert_res['state']!=0){
					$this->wrong('合作伙伴证件信息变更失败！');
				}
			}
			//组装权限数据
			if (!empty($aauth)) {
				$aauth_data = array(
					'AGENT_STATUS'	=>	6,
					'AUTH_TRANS_MAP'=>	$aauth['AUTH_TRANS_MAP']
				);
				//权限数据入库
				$aauth_res = D($this->MAauth)->updateAauth($where,$aauth_data);
				if($aauth_res['state']!=0){
					$this->wrong('合作伙伴权限信息变更失败！');
				}
			}
			//组装结算方式数据
			if (!empty($acls)) {
				$acls_data = array(
					'SETTLE_T'			=>	$acls['SETTLE_T'],
					'SETTLE_T_UNIT'		=>	$acls['SETTLE_T_UNIT'],
					'SETTLE_TYPE'		=>	2,			//0：结算到代理商 1：结算到商户 默认2保留
					'SETTLE_FLAG'		=>	$acls['SETTLE_FLAG'] ? $acls['SETTLE_FLAG'] : 0,	//0：不足顺延下个周期， 1：本周期结算
					'SETTLE_TOP_AMT'	=>	setMoney($acls['SETTLE_TOP_AMT']),
					'SETTLE_FREE_AMT'	=>	setMoney($acls['SETTLE_FREE_AMT']),
					'SETTLE_OFF_AMT'	=>	setMoney($acls['SETTLE_OFF_AMT']),
					'SETTLE_OFF_FEE'	=>	$acls['SETTLE_OFF_FEE'] ? setMoney($acls['SETTLE_OFF_FEE']) : 0,
					'SETTLE_FEE'		=>	setMoney($acls['SETTLE_FEE'])
				);
				//结算方式数据入库
				$acls_res = D($this->MAcls)->updateAcls($where,$acls_data);
				if($acls_res['state']!=0){
					$this->wrong('合作伙伴结算方式信息变更失败！');
				}
			}
			//组装银行账户数据
			if (!empty($acls)) {
				switch ($abact['AGENT_BANK_FLAG']) {
					case '0':			//对公账户变更
						$abact_data = array(
							'AGENT_BANK_FLAG'	=>	$abact['AGENT_BANK_FLAG'],
							'BANKACCT_NAME1'	=>	$abact['BANKACCT_NAME1'] ? $abact['BANKACCT_NAME1'] : '',
							'BANKACCT_NO1'		=>	$abact['BANKACCT_NO1'] ? $abact['BANKACCT_NO1'] : '',
							'BANKACCT_BID1'		=>	$abact['BANKACCT_BID1'] ? $abact['BANKACCT_BID1'] : '',
							'BANK_NAME1'		=>	$abact['BANK_NAME1'] ? $abact['BANK_NAME1'] : ''
						);
						break;
					case '1':			//对私账户变更
						$abact_data = array(
							'AGENT_BANK_FLAG'	=>	$abact['AGENT_BANK_FLAG'],
							'BANKACCT_NAME2'	=>	$abact['BANKACCT_NAME2'] ? $abact['BANKACCT_NAME2'] : '',
							'BANKACCT_NO2'		=>	$abact['BANKACCT_NO2'] ? $abact['BANKACCT_NO2'] : '',
							'BANKACCT_BID2'		=>	$abact['BANKACCT_BID2'] ? $abact['BANKACCT_BID2'] : '',
							'BANK_NAME2'		=>	$abact['BANK_NAME2'] ? $abact['BANK_NAME2'] : ''
						);
						break;
					default:
						$this->wrong('合作伙伴银行账户信息不正确,变更失败！');
						break;
				}
				//银行账户数据入库
				$abact_res = D($this->MAbact)->updateAbact($where,$abact_data);
				if($abact_res['state']!=0){
					$this->wrong('合作伙伴银行账户信息变更失败！');
				}
			}
			$this->right('信息变更成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		
		//获取合作伙伴基本信息
		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$id."'","a.*");
		//获取合作伙伴证照信息
		$acert_info = D($this->MAcert)->findAcert("AGENT_MAP_ID='".$id."'");
		//获取合作伙伴权限信息
		$aauth_info = D($this->MAauth)->findAauth("AGENT_MAP_ID='".$id."'");
		//获取合作伙伴结算方式信息
		$acls_info = D($this->MAcls)->findAcls("AGENT_MAP_ID='".$id."'");
		//获取合作伙伴银行帐户信息
		$abact_info = D($this->MAbact)->findAbact("AGENT_MAP_ID='".$id."'");
		//所属分支
		$branchsel = D($this->MBranch)->getBranchlist('','BRANCH_MAP_ID,BRANCH_NAME');
		//发卡行列表
		$banklist = D($this->MBank)->getBanklist('','ISSUE_CODE,BANK_NAME');
		if(empty($agent_info) || empty($acert_info) || empty($aauth_info) || empty($acls_info) || empty($abact_info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('auth_trans_checked', str_split($aauth_info['AUTH_TRANS_MAP']));	//交易已开通数据
		$this->assign ('agent_info', $agent_info);	
		$this->assign ('acert_info', $acert_info);	
		$this->assign ('aauth_info', $aauth_info);
		$this->assign ('acls_info',  $acls_info);	
		$this->assign ('abact_info', $abact_info);
		$this->assign ('branchsel', $branchsel);								//所属分支
		$this->assign ('banklist', $banklist);									//发卡行列表
		$this->assign ('auth_trans_map_checkbox', 	$home['AUTH_TRANS_MAP']);	//交易开通
		$this->assign ('bank_flag_redio', 			C('AGENT_BANK_FLAG'));		//结算标志

		//获取当前信息状态
		$where = "AGENT_MAP_ID = '".$agent['AGENT_MAP_ID']."'";
		if ($agent_info['AGENT_STATUS'] == 0 || $agent_info['AGENT_STATUS'] == 4) {
			$this->display('agent_edit2');
		}else{
			$this->display('agent_edit');
		}
		
	}
	/*
	* 合作伙伴管理 审核
	**/
	public function agent_check() {
		$post = I('post');
		if ($post['submit'] == "agent_check") {
			$home = session('HOME');
			//验证
			if($post['CHECK_POINT']=='' || empty($post['CHECK_DESC'])){
				$this->wrong("缺少审核状态, 审核信息！");
			}
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '1',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['AGENT_MAP_ID']) || $check_data['CHECK_POINT']=='' || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}
			$where = 'AGENT_MAP_ID = '.$post['AGENT_MAP_ID'];
			//修改状态
			$agent_res = D($this->MAgent)->updateAgent($where,array('AGENT_STATUS' => $post['CHECK_POINT']));	//基本信息状态修改
			if (!$agent_res) {
				$this->wrong("审核操作失败！");
			}
			//添加审核记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('添加记录失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->agent_show('agent_check');
	}
	/*
	* 合作伙伴管理 复审
	**/
	public function agent_recheck() {
		$post = I('post');
		if ($post['submit'] == "agent_recheck") {
			$home = session('HOME');
			//验证
			if($post['CHECK_POINT']=='' || empty($post['CHECK_DESC'])){
				$this->wrong("缺少审核状态, 审核信息！");
			}
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '1',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['AGENT_MAP_ID']) || $check_data['CHECK_POINT']=='' || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}
			$where = 'AGENT_MAP_ID = '.$post['AGENT_MAP_ID'];
			//修改状态
			$agent_res = D($this->MAgent)->updateAgent($where,array('AGENT_STATUS' => $post['CHECK_POINT']));	//基本信息状态修改
			if (!$agent_res) {
				$this->wrong("审核操作失败！");
			}
			//添加审核记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('添加记录失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}

		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//判断当前状态是否符合复审操作
		$agentstatus = D($this->MAgent)->findAgent('a.AGENT_MAP_ID = "'.$id.'"','a.AGENT_STATUS');
		if ($agentstatus['AGENT_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '2'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$this->agent_show('agent_check');
	}
	/*
	* 合作伙伴管理 冻结
	**/
	public function agent_close() {
		$ids = $_REQUEST['AGENT_MAP_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('AGENT_MAP_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('AGENT_MAP_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		/*$agentstatus = M('agent')->where($where)->field('AGENT_STATUS')->select();
		foreach ($devstatus as $key => $value) {
			if (!in_array($value['AGENT_STATUS'], array('0'))) {
				$this->wrong('当前状态无法执行此操作');
			}
		}*/
		$res = D($this->MAgent)->updateAgent($where, array('AGENT_STATUS'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 合作伙伴管理 恢复开通
	**/
	public function agent_open() {
		$ids = $_REQUEST['AGENT_MAP_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('AGENT_MAP_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('AGENT_MAP_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		/*$agentstatus = M('agent')->where($where)->field('AGENT_STATUS')->select();
		foreach ($devstatus as $key => $value) {
			if (!in_array($value['AGENT_STATUS'], array('0'))) {
				$this->wrong('当前状态无法执行此操作');
			}
		}*/
		$res = D($this->MAgent)->updateAgent($where, array('AGENT_STATUS'=> 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	
	
	
	/*
	* 合作伙伴证照变更管理 列表
	**/
	public function acert() {
		$acert_status_check = C('CHECK_POINT.check');
		$post = I('post');
		//if($post['submit'] == "agent"){
			$where = "ac.AGENT_MAP_ID !=''";
			//状态
			if($post['AGENT_STATUS'] != '') {
				if($post['AGENT_STATUS'] == 0) {
					$where .= " and ac.AGENT_STATUS = '".$post['AGENT_STATUS']."'";
				}else{
					$status = implode(",", array_keys( $acert_status_check ));
					$where .= " and tmp.AGENT_STATUS in (".$status.")";
				}			
			}else{
				$status = implode(",", array_keys( $acert_status_check ));
				$where .= " and ac.AGENT_STATUS in (".$status.")";
			}
			//分页
			$count = D($this->MAcert)->countAcert($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MAcert)->getAcertlist($where, 'ac.AGENT_MAP_ID,ac.AGENT_STATUS,ac.RES,ag.AGENT_NO,ag.AGENT_LEVEL,ag.AGENT_NAME,tmp.AGENT_MAP_ID as TMP_ID,tmp.AGENT_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,ac.AGENT_MAP_ID desc');
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		//}
		
		\Cookie::set ('_currentUrl_', 	__SELF__);
		//代理商审核状态数组
		$this->assign('agent_status',C('CHECK_POINT.all'));			//通道全部状态
		$this->assign('agent_status_check',C('CHECK_POINT.check'));	//通道部分状态
		$this->assign('plevel_arr',C('PLEVEL_NAME'));				//级别名称
		$this->display();
	}
	/*
	* 合作伙伴证照变更管理 修改
	**/
	public function acert_edit() {
		$acert = I('acert');
		if($acert['submit'] == "acert_edit") {
			$home = session('HOME');
			//组装证件数据
			$acert_data = array(
				'AGENT_MAP_ID'	=>	$acert['AGENT_MAP_ID'],
				'AGENT_STATUS'	=>	6,
				'AGREEMENT_NO'	=>	$acert['AGREEMENT_NO'],
				'REG_ADDR'		=>	$acert['REG_ADDR'],
				'REG_ID'		=>	$acert['REG_ID'],
				'TAX_ID'		=>	$acert['TAX_ID'],
				'ORG_ID'		=>	$acert['ORG_ID'],
				'LP_NAME'		=>	$acert['LP_NAME'],
				'LP_ID'			=>	$acert['LP_ID'],
				'REGID_PHOTO'	=>	$acert['REGID_PHOTO'],
				'TAXID_PHOTO'	=>	$acert['TAXID_PHOTO'],
				'ORGID_PHOTO'	=>	$acert['ORGID_PHOTO'],
				'LP_D_PHOTO'	=>	$acert['LP_D_PHOTO'],
				'LP_R_PHOTO'	=>	$acert['LP_R_PHOTO'],
				'BANK_PHOTO'	=>	$acert['BANK_PHOTO'],
				'RES'			=>	$acert['RES']
			);
			//判断当前数据是否正在发生变更
			$where = 'AGENT_MAP_ID = "'.$acert['AGENT_MAP_ID'].'"';
			$acert_tmp = D($this->MAcert)->findAcert_tmp($where);
			if ($acert_tmp['AGENT_STATUS'] == 4) {
				$this->wrong('当前数据已进入待复审状态,如需变更请等待复审结束后,再次提交!');
			}
			//证件数据入tmp库
			if($acert['flag'] == 1){
				$res = D($this->MAcert)->updateAcert_tmp("AGENT_MAP_ID='".$acert['AGENT_MAP_ID']."'", $acert_data);
			}else{
				$res = D($this->MAcert)->addAcert_tmp($acert_data);
			}
			if($res['state']!=0){
				$this->wrong('合作伙伴证件信息变更失败！');
			}

			/*if(empty($acert['AGENT_MAP_ID']) || empty($check_data['CHECK_FLAG']) || empty($check_data['CHECK_POINT']) || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}*/

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($acert['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '证照资质变更',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $id);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$acert_info = D($this->MAcert)->findAcert_tmp("AGENT_MAP_ID='".$id."'");
/*			if ($acert_info) {
				$this->wrong('当前数据已经提交变更,如需变更请通过审核后,再次提交!');
			}*/
		}else{
			$acert_info = D($this->MAcert)->findAcert("AGENT_MAP_ID='".$id."'");
		}
		
		if(empty($agent_info) || empty($agent_info)){
			$this->wrong("参数数据出错！");
		}
		$agent_info['flag'] = $flag;
		$this->assign ('agent_info', $agent_info);
		$this->assign ('acert_info', $acert_info);
		$this->display();
	}
	/*
	* 合作伙伴证照变更管理 审核
	**/
	public function acert_check() {
		$post = I('post');
		if($post['submit'] == "acert_check") {
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['AGENT_MAP_ID']) || empty($check_data['CHECK_POINT']) || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}
			//更新tmp表审核状态
			$where = 'AGENT_MAP_ID = '.$post['AGENT_MAP_ID'];
			$acert_tmp = D($this->MAcert)->updateAcert_tmp($where,array('AGENT_STATUS' => $post['CHECK_POINT']));
			if ($acert_tmp['state']==1) {
				$this->wrong("审核操作失败！");
			}
			//初审记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('操作失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];

		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if($flag == 1){
			$acert_info = D($this->MAcert)->findAcert_tmp("AGENT_MAP_ID='".$id."'");
		}else{
			$acert_info = D($this->MAcert)->findAcert("AGENT_MAP_ID='".$id."'");
		}
		if(empty($agent_info) || empty($acert_info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('agent_info', $agent_info);
		$this->assign ('acert_info', $acert_info);
		$this->display();
	}
	/*
	* 合作伙伴证照变更管理 复审
	**/
	public function acert_recheck() {
		$post = I('post');
		if ($post['submit'] == "acert_recheck") {
			$home = session('HOME');
			//验证
			if($post['CHECK_POINT']=='' || empty($post['CHECK_DESC'])){
				$this->wrong("缺少审核状态, 审核信息！");
			}
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			//验证
			if(empty($post['AGENT_MAP_ID']) || $post['CHECK_POINT']=='' || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}
			//判断是否通过并更改数据
			$where = 'AGENT_MAP_ID = '.$post['AGENT_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$acert = D($this->MAcert)->updateAcert_tmp($where,array('AGENT_STATUS' => $post['CHECK_POINT']));
				if($acert['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$acert_change = D($this->MAcert)->findAcert_tmp($where);
				$acert_change['AGENT_STATUS'] = 0;
				//更新证照主表数据
				$acert = D($this->MAcert)->updateAcert($where,$acert_change);
				if($acert['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MAcert)->delAcert_tmp('AGENT_MAP_ID = '.$post['AGENT_MAP_ID']);
				if ($delres['state'] != 0){
					$this->wrong("操作失败!");
				}
			}
			//添加审核记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('添加记录失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//查看待审信息
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		//判断当前状态是否符合复审操作
		$agent_info = D($this->MAgent)->findAgent('a.AGENT_MAP_ID = "'.$id.'"','a.*, b.BRANCH_NAME');
		$acert_info = D($this->MAcert)->findAcert_tmp("AGENT_MAP_ID='".$id."'");
		if ($acert_info['AGENT_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '2'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$this->assign('agent_info',$agent_info);
		$this->assign('acert_info',$acert_info);
		$this->display('acert_check');
	}
	
	/*
	* 合作伙伴权限变更管理 列表
	**/
	public function aauth() {
		$post = I('post');
		//if($post['submit'] == "aauth"){
			$where = "aa.AGENT_MAP_ID != ''";
			//状态
			if($post['AGENT_STATUS'] != '') {
				if($post['AGENT_STATUS'] == 0){
					$where .= " and aa.AGENT_STATUS = '".$post['AGENT_STATUS']."'";
				}else{
					$where .= " and tmp.AGENT_STATUS = '".$post['AGENT_STATUS']."'";
				}
			}
			//分页
			$count = D($this->MAauth)->countAauth($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MAauth)->getAauthlist($where, 'aa.AGENT_MAP_ID,ag.AGENT_NO,ag.AGENT_LEVEL,ag.AGENT_NAME,aa.AGENT_STATUS,tmp.AGENT_MAP_ID as TMP_ID,tmp.AGENT_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,aa.AGENT_MAP_ID desc');
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		//}
	
		\Cookie::set (	'_currentUrl_', 	__SELF__);
		//代理商审核状态数组
		$this->assign('agent_status',C('CHECK_POINT.all'));			//通道全部状态
		$this->assign('agent_status_check',C('CHECK_POINT.check'));	//通道部分状态
		$this->assign('plevel_arr',C('PLEVEL_NAME'));				//级别名称
		$this->display();
	}
	/*
	* 代理商权限 审核
	**/
	public function aauth_edit() {
		$aauth = I('aauth');
		if($aauth['submit'] == "aauth_edit") {
			$home = session('HOME');
			//组装权限数据
			$aauth_data = array(
				'AGENT_MAP_ID'	=>	$aauth['AGENT_MAP_ID'],
				'AGENT_STATUS'	=>	6,
				'HOST_MAP_ID'	=>	$aauth['HOST_MAP_ID'] ? $aauth['HOST_MAP_ID'] : 0,
				'AUTH_TRANS_MAP'=>	$aauth['AUTH_TRANS_MAP'],
				'AUTH_PAYS_MAP'	=>	''
			);

			//判断当前数据是否正在发生变更
			$where = 'AGENT_MAP_ID = "'.$aauth['AGENT_MAP_ID'].'"';
			$aauth_tmp = D($this->MAauth)->findAauth_tmp($where);
			if ($aauth_tmp['AGENT_STATUS'] == 4) {
				$this->wrong('当前数据已进入待复审状态,如需变更请等待复审结束后,再次提交!');
			}
			//证件数据入tmp库
			if($aauth['flag'] == 1){
				$res = D($this->MAauth)->updateAauth_tmp("AGENT_MAP_ID='".$aauth['AGENT_MAP_ID']."'", $aauth_data);
			}else{
				$res = D($this->MAauth)->addAauth_tmp($aauth_data);
			}
			if($res['state']!=0){
				$this->wrong('合作伙伴权限信息变更失败！');
			}

			/*if(empty($aauth['AGENT_MAP_ID']) || empty($check_data['CHECK_FLAG']) || empty($check_data['CHECK_POINT']) || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}*/

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($aauth['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '权限信息变更',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('信息变更失败');
			}
			$this->right('信息变更成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$aauth_info = D($this->MAauth)->findAauth_tmp("AGENT_MAP_ID='".$id."'");
/*			if ($aauth_info) {
				$this->wrong('当前数据已经提交变更,如需变更请通过审核后,再次提交!');
			}*/
		}else{
			$aauth_info = D($this->MAauth)->findAauth("AGENT_MAP_ID='".$id."'");
		}
		
		if(empty($agent_info) || empty($aauth_info)){
			$this->wrong("参数数据出错！");
		}
		$agent_info['flag'] = $flag;
		//路由通道
		$hostsel = D($this->MHost)->getHostlist('HOST_STATUS = 0', $field='HOST_MAP_ID,HOST_NAME');
		$this->assign ('hostsel', $hostsel);
		$this->assign ('agent_info', $agent_info);
		$this->assign ('aauth_info', $aauth_info);
		$this->assign ('auth_trans_checked', str_split($aauth_info['AUTH_TRANS_MAP']));	//交易开通数据
		$this->assign ('auth_pays_checked',  str_split($aauth_info['AUTH_PAYS_MAP']));	//支付开通数据
		$this->display();
	}
	/*
	* 合作伙伴权限变更管理 审核
	**/
	public function aauth_check() {
		$post = I('post');
		if($post['submit'] == "aauth_check") {
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['AGENT_MAP_ID']) || empty($check_data['CHECK_FLAG']) || empty($check_data['CHECK_POINT']) || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}
			//更新tmp表审核状态
			$where = 'AGENT_MAP_ID = '.$post['AGENT_MAP_ID'];
			$acert_tmp = D($this->MAauth)->updateAauth_tmp($where,array('AGENT_STATUS' => $post['CHECK_POINT']));
			if ($aauth_tmp['state']==1) {
				$this->wrong("审核操作失败！");
			}
			//初审记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('操作失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];

		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if($flag == 1){
			$aauth_info = D($this->MAauth)->findAauth_tmp("AGENT_MAP_ID='".$id."'");
		}else{
			$aauth_info = D($this->MAauth)->findAauth("AGENT_MAP_ID='".$id."'");
		}
		if(empty($agent_info) || empty($agent_info)){
			$this->wrong("参数数据出错！");
		}
		//路由通道
		$hostsel = D($this->MHost)->getHostlist('HOST_STATUS = 0', $field='HOST_MAP_ID,HOST_NAME');
		$this->assign ('hostsel', $hostsel);
		$this->assign ('agent_info', $agent_info);
		$this->assign ('aauth_info', $aauth_info);
		$this->assign ('auth_trans_checked', str_split($aauth_info['AUTH_TRANS_MAP']));	//交易开通数据
		$this->assign ('auth_pays_checked',  str_split($aauth_info['AUTH_PAYS_MAP']));	//支付开通数据
		$this->display();
	}
	/*
	* 合作伙伴权限变更管理 复核
	**/
	public function aauth_recheck() {
		$post = I('post');
		if ($post['submit'] == "aauth_recheck") {
			$home = session('HOME');
			//验证字段
			if($post['CHECK_POINT']=='' || empty($post['CHECK_DESC'])){
				$this->wrong("缺少审核状态, 审核信息！");
			}
			//组装审核数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['AGENT_MAP_ID']) || empty($check_data['CHECK_FLAG']) || $post['CHECK_POINT']=='' || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}
			$where = 'AGENT_MAP_ID = '.$post['AGENT_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$aauth = D($this->MAauth)->updateAauth_tmp($where,array('AGENT_STATUS' => $post['CHECK_POINT']));
				if($aauth['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$aauth_change = D($this->MAauth)->findAauth_tmp($where);
				$aauth_change['AGENT_STATUS'] = 0;
				//更新主表数据
				$aauth = D($this->MAauth)->updateAauth($where,$aauth_change);
				if($aauth['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MAauth)->delAauth_tmp('AGENT_MAP_ID = '.$post['AGENT_MAP_ID']);
				if ($delres['state'] != 0){
					$this->wrong("操作失败!");
				}
			}
			//添加审核记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('添加记录失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}

		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		//判断当前状态是否符合复审操作
		$agent_info = D($this->MAgent)->findAgent('a.AGENT_MAP_ID = "'.$id.'"','a.*, b.BRANCH_NAME');
		$aauth_info = D($this->MAauth)->findAauth_tmp("AGENT_MAP_ID='".$id."'");
		if ($aauth_info['AGENT_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '2'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
			//$this->assign('aauth_info',$aauth_info);
		}
		//路由通道
		$hostsel = D($this->MHost)->getHostlist('HOST_STATUS = 0', $field='HOST_MAP_ID,HOST_NAME');
		$this->assign ('hostsel', $hostsel);
		$this->assign ('agent_info', $agent_info);
		$this->assign ('aauth_info', $aauth_info);
		$this->assign ('auth_trans_checked', str_split($aauth_info['AUTH_TRANS_MAP']));	//交易开通数据
		$this->assign ('auth_pays_checked',  str_split($aauth_info['AUTH_PAYS_MAP']));	//支付开通数据
		$this->display('aauth_check');
	}
	
	
	/*
	* 合作伙伴结算方式变更管理 列表
	**/
	public function amdr() {
		$post = I('post');
		//if($post['submit'] == "amdr"){
			$where = "am.AGENT_MAP_ID != ''";
			//状态
			if($post['AGENT_STATUS'] != '') {
				if($post['AGENT_STATUS'] == 0){
					$where .= " and aa.AGENT_STATUS = '".$post['AGENT_STATUS']."'";
				}else{
					$where .= " and tmp.AGENT_STATUS = '".$post['AGENT_STATUS']."'";
				}
			}
			//合作伙伴名称
			if($post['AGENT_NAME']) {
				$where .= " and ag.AGENT_NAME like '%".$post['AGENT_NAME']."%'";
			}
			//分页
			$count = D($this->MAmdr)->countAmdr($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MAmdr)->getAmdrlist($where, 'am.*, tmp.AGENT_MAP_ID as TMP_ID, tmp.AGENT_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,am.AMDR_ID desc');
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		//}
		$home = session('HOME');
		$pay_arr = array();
		foreach ($home['AUTH_PAYS_MAP'] as $key => $val) {
			$pay_arr[$val['PAY_TYPE']] = $val['PAY_NAME'];
		}
		$this->assign('pay_type',			$pay_arr);					//支付类型
		$this->assign('mcc_type',			C('MCC_TYPE'));				//MCC分类
		$this->assign('agent_status',		C('CHECK_POINT.all'));		//合作伙伴所有状态
		$this->assign('agent_status_check',	C('CHECK_POINT.check'));	//合作伙伴部分状态
		\Cookie::set ('_currentUrl_', 	__SELF__);			
		$this->display();
	}
	/*
	* 合作伙伴结算方式变更管理 修改
	**/
	public function amdr_edit() {
		$amdr = I('amdr');
		if($amdr['submit'] == "amdr_edit") {
			$home = session('HOME');
			//组装扣率数据
			$amdr_data = array(
				'AGENT_MAP_ID'	=>	$amdr['AGENT_MAP_ID'],
				'PAY_TYPE'		=>	0,
				'MCC_TYPE'		=>	$amdr['MCC_TYPE'],
				'AGENT_STATUS'	=>	6,
				'PER_FEE'		=>	$amdr['PER_FEE'],
				'FIX_FEE'		=>	$amdr['FIX_FEE'],
				'PER_RAKE'		=>	$amdr['PER_RAKE']
			);
			//判断当前数据是否正在发生变更
			$where = 'AMDR_ID = "'.$amdr['AMDR_ID'].'"';
			$amdr_tmp = D($this->MAmdr)->findAmdr_tmp($where);
			if ($amdr_tmp['AGENT_STATUS'] == 4) {
				$this->wrong('当前数据已进入待复审状态,如需变更请等待复审结束后,再次提交!');
			}
			//扣率数据入tmp库
			if($amdr['flag'] == 1){
				$res = D($this->MAmdr)->updateAmdr_tmp($where, $amdr_data);
			}else{
				$amdr_data['AMDR_ID'] = $amdr['AMDR_ID'];
				$res = D($this->MAmdr)->addAmdr_tmp($amdr_data);
			}
			if($res['state'] != 0){
				$this->wrong('合作伙伴扣率信息变更失败！');
			}

			/*if(empty($amdr['AGENT_MAP_ID']) || empty($check_data['CHECK_FLAG']) || empty($check_data['CHECK_POINT']) || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}*/

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($amdr['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '扣率信息变更',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('信息变更失败');
			}
			$this->right('信息变更成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$amdr_info = D($this->MAmdr)->findAmdr_tmp("AMDR_ID='".$id."'");
/*			if ($amdr_info) {
				$this->wrong('当前数据已经提交变更,如需变更请通过审核后,再次提交!');
			}*/
		}else{
			$amdr_info = D($this->MAmdr)->findAmdr("AMDR_ID='".$id."'");
		}
		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$amdr_info['AGENT_MAP_ID']."'",'a.*, b.BRANCH_NAME');
		if(empty($amdr_info) || empty($agent_info)){
			$this->wrong("参数数据出错！");
		}
		$agent_info['flag'] = $flag;
		$this->assign ('mcc_type',	C('MCC_TYPE'));	//MCC分类
		$this->assign ('agent_info', $agent_info);
		$this->assign ('amdr_info', $amdr_info);
		$this->display();
	}	
	/*
	* 合作伙伴结算方式变更管理 审核
	**/
	public function amdr_check() {
		$post = I('post');
		if($post['submit'] == "amdr_check") {
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['AGENT_MAP_ID']) || empty($post['AMDR_ID']) || empty($check_data['CHECK_FLAG']) || empty($check_data['CHECK_POINT']) || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}
			//更新tmp表审核状态
			$where = 'AMDR_ID = '.$post['AMDR_ID'];
			$amdr_tmp = D($this->MAmdr)->updateAmdr_tmp($where,array('AGENT_STATUS' => $post['CHECK_POINT']));
			if ($amdr_tmp['state']==1) {
				$this->wrong("审核操作失败！");
			}
			//初审记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('操作失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		if($flag == 1){
			$amdr_info = D($this->MAmdr)->findAmdr_tmp("AMDR_ID='".$id."'");
		}else{
			$amdr_info = D($this->MAmdr)->findAmdr("AMDR_ID='".$id."'");
		}
		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$amdr_info['AGENT_MAP_ID']."'",'a.*, b.BRANCH_NAME');
		if(empty($agent_info) || empty($amdr_info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('mcc_type',	C('MCC_TYPE'));	//MCC分类
		$this->assign ('agent_info', $agent_info);
		$this->assign ('amdr_info', $amdr_info);
		$this->display();
	}
	/*
	* 合作伙伴结算方式变更管理 复核
	**/
	public function amdr_recheck() {
		$post = I('post');
		if ($post['submit'] == "amdr_recheck") {
			$home = session('HOME');
			//验证字段
			if($post['CHECK_POINT']=='' || empty($post['CHECK_DESC'])){
				$this->wrong("缺少审核状态, 审核信息！");
			}
			//组装审核数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['AGENT_MAP_ID']) || empty($post['AMDR_ID']) || empty($check_data['CHECK_FLAG']) || $check_data['CHECK_POINT']=='' || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}
			$where = 'AMDR_ID = '.$post['AMDR_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$amdr = D($this->MAmdr)->updateAmdr_tmp($where,array('AGENT_STATUS' => $post['CHECK_POINT']));
				if($amdr['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$amdr_change = D($this->MAmdr)->findAmdr_tmp($where);
				$amdr_change['AGENT_STATUS'] = 0;
				//更新主表数据
				$amdr = D($this->MAmdr)->updateAmdr($where,$amdr_change);
				if($amdr['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MAmdr)->delAmdr_tmp('AMDR_ID = '.$post['AMDR_ID']);
				if ($delres['state'] != 0){
					$this->wrong("操作失败!");
				}
			}
			//添加审核记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('添加记录失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}

		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		//判断当前状态是否符合复审操作
		
		$amdr_info = D($this->MAmdr)->findAmdr_tmp("AMDR_ID='".$id."'");
		if ($amdr_info['AGENT_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$amdr_info['AGENT_MAP_ID']."'",'a.*, b.BRANCH_NAME');
		//获取初审记录
		$check_no = '2'.setStrzero($amdr_info['AGENT_MAP_ID'],15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$this->assign ('mcc_type',	C('MCC_TYPE'));	//MCC分类
		$this->assign ('agent_info', $agent_info);
		$this->assign ('amdr_info', $amdr_info);
		$this->display('amdr_check');
	}
	
	
	/*
	* 合作伙伴结算方式变更管理 列表
	**/
	public function acls() {
		$post = I('post');
		//if($post['submit'] == "acls"){
			$where = "ac.AGENT_MAP_ID != ''";
			//审核状态
			if($post['AGENT_STATUS']) {
				$where .= " and ac.AGENT_STATUS = '".$post['AGENT_STATUS']."'";
			}
			//分页
			$count = D($this->MAcls)->countAcls($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MAcls)->getAclslist($where, 'ac.AGENT_MAP_ID,ag.AGENT_NO,ag.AGENT_LEVEL,ag.AGENT_NAME,ac.AGENT_STATUS,tmp.AGENT_MAP_ID as TMP_ID,tmp.AGENT_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		//}
	
		\Cookie::set ('_currentUrl_', 	__SELF__);			
		$this->assign('agent_status',C('CHECK_POINT.all'));			//代理商所有审核状态数组
		$this->assign('agent_status_check',C('CHECK_POINT.check'));	//代理商部分审核状态数组
		$this->assign('plevel_arr',C('PLEVEL_NAME'));				//级别名称
		$this->display();
	}
	/*
	* 合作伙伴结算方式变更管理 修改
	**/
	public function acls_edit() {
		$acls = I('acls');
		if($acls['submit'] == "acls_edit") {
			$home = session('HOME');
			//组装结算方式数据
			$acls_data = array(
				'AGENT_MAP_ID'		=>	$acls['AGENT_MAP_ID'],
				'AGENT_STATUS'		=>	0,
				'SETTLE_T'			=>	$acls['SETTLE_T'],
				'SETTLE_T_UNIT'		=>	$acls['SETTLE_T_UNIT'],
				'SETTLE_TYPE'		=>	2,		//0：结算到代理商 1：结算到商户 默认2保留
				'SETTLE_FLAG'		=>	$acls['SETTLE_FLAG'] ? $acls['SETTLE_FLAG'] : 1,
				'SETTLE_TOP_AMT'	=>	setMoney($acls['SETTLE_TOP_AMT']),
				'SETTLE_FREE_AMT'	=>	setMoney($acls['SETTLE_FREE_AMT']),
				'SETTLE_OFF_AMT'	=>	setMoney($acls['SETTLE_OFF_AMT']),
				'SETTLE_OFF_FEE'	=>	$acls['SETTLE_OFF_FEE'] ? setMoney($acls['SETTLE_OFF_FEE']) : 0,
				'SETTLE_FEE'		=>	setMoney($acls['SETTLE_FEE'])
			);
			//判断当前数据是否正在发生变更
			$where = 'AGENT_MAP_ID = "'.$acls['AGENT_MAP_ID'].'"';
			$acls_tmp = D($this->MAcls)->findAcls_tmp($where);
			if ($acls_tmp['AGENT_STATUS'] == 4) {
				$this->wrong('当前数据已进入待复审状态,如需变更请等待复审结束后,再次提交!');
			}
			//证件数据入tmp库
			if($acls['flag'] == 1){
				$res = D($this->MAcls)->updateAcls_tmp("AGENT_MAP_ID='".$acls['AGENT_MAP_ID']."'", $acls_data);
			}else{
				$res = D($this->MAcls)->addAcls_tmp($acls_data);
			}
			if($res['state']!=0){
				$this->wrong('合作伙伴银行账户信息变更失败！');
			}

			/*if(empty($acls['AGENT_MAP_ID']) || empty($check_data['CHECK_FLAG']) || empty($check_data['CHECK_POINT']) || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}*/

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($acls['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '银行账户变更',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $id);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$acls_info = D($this->MAcls)->findAcls_tmp("AGENT_MAP_ID='".$id."'");
			/* if ($acls_info) {
				$this->wrong('当前数据已经提交变更,如需变更请通过审核后,再次提交!');
			}*/
		}else{
			$acls_info = D($this->MAcls)->findAcls("AGENT_MAP_ID='".$id."'");
		}
		
		if(empty($agent_info) || empty($acls_info)){
			$this->wrong("参数数据出错！");
		}
		//发卡行列表
		$issuelist = M('bank')->field('ISSUE_CODE,BANK_NAME')->select();
		$acls_info['flag'] = $flag;
		$this->assign ('issuelist', $issuelist);	//发卡行列表
		$this->assign ('bank_flag_redio', C('AGENT_BANK_FLAG'));	//结算标志
		$this->assign ('agent_info', $agent_info);
		$this->assign ('acls_info', $acls_info);
		$this->display();
	}
	/*
	* 合作伙伴结算方式变更管理 审核
	**/
	public function acls_check() {
		$post = I('post');
		if($post['submit'] == "acls_check") {
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['AGENT_MAP_ID']) || empty($check_data['CHECK_POINT']) || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}
			//更新tmp表审核状态
			$where = 'AGENT_MAP_ID = '.$post['AGENT_MAP_ID'];
			$acls_tmp = D($this->MAcls)->updateAcls_tmp($where,array('AGENT_STATUS' => $post['CHECK_POINT']));
			if ($acls_tmp['state']==1) {
				$this->wrong("审核操作失败！");
			}
			//初审记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('操作失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];

		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if($flag == 1){
			$acls_info = D($this->MAcls)->findAcls_tmp("AGENT_MAP_ID='".$id."'");
		}else{
			$acls_info = D($this->MAcls)->findAcls("AGENT_MAP_ID='".$id."'");
		}
		if(empty($agent_info) || empty($acls_info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('agent_info', $agent_info);
		$this->assign ('acls_info', $acls_info);
		$this->display();
	}	
	/*
	* 合作伙伴结算方式变更管理 复核
	**/
	public function acls_recheck() {
		$post = I('post');
		if ($post['submit'] == "acls_recheck") {
			$home = session('HOME');
			//验证字段
			if($post['CHECK_POINT']=='' || empty($post['CHECK_DESC'])){
				$this->wrong("缺少审核状态, 审核信息！");
			}
			//组装审核数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['AGENT_MAP_ID']) || $post['CHECK_POINT']=='' || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}
			$where = 'AGENT_MAP_ID = '.$post['AGENT_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$acls = D($this->MAcls)->updateAcls_tmp($where,array('AGENT_STATUS' => $post['CHECK_POINT']));
				if($acls['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$acls_change = D($this->MAcls)->findAcls_tmp($where);
				$acls_change['AGENT_STATUS'] = 0;
				//更新主表数据
				$acls = D($this->MAcls)->updateAcls($where,$acls_change);
				if($acls['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MAcls)->delAcls_tmp('AGENT_MAP_ID = '.$post['AGENT_MAP_ID']);
				if ($delres['state'] != 0){
					$this->wrong("操作失败!");
				}
			}
			//添加审核记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('添加记录失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}

		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		//判断当前状态是否符合复审操作
		$agent_info = D($this->MAgent)->findAgent('a.AGENT_MAP_ID = "'.$id.'"','a.*, b.BRANCH_NAME');
		$acls_info = D($this->MAcls)->findAcls_tmp("AGENT_MAP_ID='".$id."'");
		if ($acls_info['AGENT_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '2'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
			//$this->assign('acls_info',$acls_info);
		}
		$this->assign('agent_info',$agent_info);
		$this->assign('acls_info',$acls_info);
		//$this->agent_show('agent_check');
		$this->display('acls_check');
	}
	
	
	
	/*
	* 合作伙伴银行账户变更管理 列表
	**/
	public function abact() {
		$post = I('post');
		//if($post['submit'] == "abact"){
			$where = "ab.AGENT_MAP_ID != ''";
			//审核状态
			if($post['AGENT_STATUS']) {
				$where .= " and ab.AGENT_STATUS = '".$post['AGENT_STATUS']."'";
			}

			//分页
			$count = D($this->MAbact)->countAbact($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MAbact)->getAbactlist($where, 'ab.AGENT_MAP_ID,ag.AGENT_NO,ag.AGENT_LEVEL,ag.AGENT_NAME,ab.AGENT_STATUS,tmp.AGENT_MAP_ID as TMP_ID,tmp.AGENT_STATUS as TMP_STATUS ', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		//}
	
		\Cookie::set (	'_currentUrl_', 	__SELF__);
		$this->assign('agent_status',C('CHECK_POINT.all'));
		$this->assign('agent_status_check',C('CHECK_POINT.check'));
		$this->assign('plevel_arr',C('PLEVEL_NAME'));			//级别名称
		$this->display();
	}
	/*
	* 合作伙伴银行账户变更管理 修改
	**/
	public function abact_edit() {
		$abact = I('abact');
		if($abact['submit'] == "abact_edit") {
			$home = session('HOME');
			//组装银行账户数据
			$abact_data = array(
				'AGENT_MAP_ID'		=>	$abact['AGENT_MAP_ID'],
				'AGENT_STATUS'		=>	6,
				'AGENT_BANK_FLAG'	=>	$abact['AGENT_BANK_FLAG'],
				'BANKACCT_NAME1'	=>	$abact['BANKACCT_NAME1'],
				'BANKACCT_NO1'		=>	$abact['BANKACCT_NO1'],
				'BANKACCT_BID1'		=>	$abact['BANKACCT_BID1'],
				'BANK_NAME1'		=>	$abact['BANK_NAME1'],
				'BANKACCT_NAME2'	=>	$abact['BANKACCT_NAME2'],
				'BANKACCT_NO2'		=>	$abact['BANKACCT_NO2'],
				'BANKACCT_BID2'		=>	$abact['BANKACCT_BID2'],
				'BANK_NAME2'		=>	$abact['BANK_NAME2']
			);
			//判断当前数据是否正在发生变更
			$where = 'AGENT_MAP_ID = "'.$abact['AGENT_MAP_ID'].'"';
			$abact_tmp = D($this->MAbact)->findAbact_tmp($where);
			if ($abact_tmp['AGENT_STATUS'] == 4) {
				$this->wrong('当前数据已进入待复审状态,如需变更请等待复审结束后,再次提交!');
			}
			//证件数据入tmp库
			if($abact['flag'] == 1){
				$res = D($this->MAbact)->updateAbact_tmp("AGENT_MAP_ID='".$abact['AGENT_MAP_ID']."'", $abact_data);
			}else{
				$res = D($this->MAbact)->addAbact_tmp($abact_data);
			}
			if($res['state']!=0){
				$this->wrong('合作伙伴银行账户信息变更失败！');
			}

			/*if(empty($abact['AGENT_MAP_ID']) || empty($check_data['CHECK_FLAG']) || empty($check_data['CHECK_POINT']) || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}*/

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($abact['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '银行账户变更',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $id);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$abact_info = D($this->MAbact)->findAbact_tmp("AGENT_MAP_ID='".$id."'");
/*			if ($abact_info) {
				$this->wrong('当前数据已经提交变更,如需变更请通过审核后,再次提交!');
			}*/
		}else{
			$abact_info = D($this->MAbact)->findAbact("AGENT_MAP_ID='".$id."'");
		}
		
		if(empty($agent_info) || empty($abact_info)){
			$this->wrong("参数数据出错！");
		}
		//发卡行列表
		$banklist = M('bank')->field('ISSUE_CODE,BANK_NAME')->select();
		$agent_info['flag'] = $flag;
		$this->assign ('banklist', $banklist);	//发卡行列表
		$this->assign ('bank_flag_redio', 			C('AGENT_BANK_FLAG'));	//结算标志
		$this->assign ('agent_info', $agent_info);
		$this->assign ('abact_info', $abact_info);
		$this->display();
	}
	/*
	* 合作伙伴银行账户变更管理 审核
	**/
	public function abact_check() {
		$post = I('post');
		if($post['submit'] == "abact_check") {
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['AGENT_MAP_ID']) || empty($check_data['CHECK_FLAG']) || empty($check_data['CHECK_POINT']) || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}
			//更新tmp表审核状态
			$where = 'AGENT_MAP_ID = '.$post['AGENT_MAP_ID'];
			$abact_tmp = D($this->MAbact)->updateAbact_tmp($where,array('AGENT_STATUS' => $post['CHECK_POINT']));
			if ($abact_tmp['state']==1) {
				$this->wrong("审核操作失败！");
			}
			//初审记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('操作失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];

		$agent_info = D($this->MAgent)->findAgent("a.AGENT_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if($flag == 1){
			$abact_info = D($this->MAbact)->findAbact_tmp("AGENT_MAP_ID='".$id."'");
		}else{
			$abact_info = D($this->MAbact)->findAbact("AGENT_MAP_ID='".$id."'");
		}
		if(empty($agent_info) || empty($agent_info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('bank_flag_redio', C('AGENT_BANK_FLAG'));	//结算标志
		$this->assign ('agent_info', $agent_info);
		$this->assign ('abact_info', $abact_info);
		$this->display();
	}	
	/*
	* 合作伙伴银行账户变更管理 复核
	**/
	public function abact_recheck() {
		$post = I('post');
		if ($post['submit'] == "abact_recheck") {
			$home = session('HOME');
			//验证
			if($post['CHECK_POINT']=='' || empty($post['CHECK_DESC'])){
				$this->wrong("缺少审核状态, 审核信息！");
			}
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['AGENT_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['AGENT_MAP_ID']) || empty($check_data['CHECK_FLAG']) || $post['CHECK_POINT']=='' || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}
			$where = 'AGENT_MAP_ID = '.$post['AGENT_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$abact = D($this->MAbact)->updateAbact_tmp($where,array('AGENT_STATUS' => $post['CHECK_POINT']));
				if($abact['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$abact_change = D($this->MAbact)->findAbact_tmp($where);
				$abact_change['AGENT_STATUS'] = 0;
				//更新证照主表数据
				$abact = D($this->MAbact)->updateAbact($where,$abact_change);
				if($abact['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MAbact)->delAbact_tmp('AGENT_MAP_ID = '.$post['AGENT_MAP_ID']);
				if ($delres['state'] != 0){
					$this->wrong("操作失败!");
				}
			}
			//添加审核记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('添加记录失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//查看待审核信息
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		//判断当前状态是否符合复审操作
		$agent_info = D($this->MAgent)->findAgent('a.AGENT_MAP_ID = "'.$id.'"','a.*, b.BRANCH_NAME');
		$abact_info = D($this->MAbact)->findAbact_tmp("AGENT_MAP_ID='".$id."'");
		if ($abact_info['AGENT_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '2'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$this->assign('bank_flag_redio', C('AGENT_BANK_FLAG'));	//结算标志
		$this->assign('agent_info',$agent_info);
		$this->assign('abact_info',$abact_info);
		$this->display('abact_check');
	}
}