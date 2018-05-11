<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @ljf  合作伙伴管理
// +----------------------------------------------------------------------
class PartnerController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->MPartner	= 'MPartner';
		$this->MPcert	= 'MPcert';
		$this->MPbact	= 'MPbact';
		$this->MBranch	= 'MBranch';
		$this->MHost	= 'MHost';
		$this->MPauth	= 'MPauth';
		$this->MPcls	= 'MPcls';
		$this->MPcfg	= 'MPcfg';
		$this->MCheck	= 'MCheck';
		$this->MShop	= 'MShop';
		$this->MBank	= 'MBank';
		$this->MBid		= 'MBid';
		$this->MDevice	= 'MDevice';
		$this->MSecurity= 'MSecurity';
		$this->GVipcard = 'GVipcard';
		$this->GVip 	= 'GVip';
		$this->MUser	= 'MUser';
		$this->MRole	= 'MRole';
		$this->MCity	= 'MCity';
	}

	/*
	* 合作伙伴管理 列表
	**/
	public function partner() {
		$post = I('post');
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'PARTNER_STATUS'	=>	I('PARTNER_STATUS'),
				'bid'				=>	I('bid'),
				'pid'				=>	I('pid'),
				'PLEVEL_NAME'		=>	I('PLEVEL_NAME'),
				'PARTNER_G_FLAG'	=>	I('PARTNER_G_FLAG'),
				'PARTNER_NAME'		=>	I('PARTNER_NAME'),
				'PLEVEL_MAP_ID'		=>	I('PLEVEL_MAP_ID'),
			);
			$ajax_soplv = array(
				'bid'				=>	I('bid'),
				'pid'				=>	I('pid'),		
			);
		}
		//===结束=======
		if($post['submit'] == "partner"){
			$home = session('HOME');
			$where = "1=1";
			//===优化统计===
			$sellv = $ajax == 'loading' ? $ajax_soplv : filter_data('soplv');	//列表查询
			//===结束=======
			//归属分公司
			if($sellv['bid']) {
				$where .= " and BRANCH_MAP_ID = '".$sellv['bid']."'";
				$post['bid'] = $sellv['bid'];
			}
			//归属合作伙伴
			if ($sellv['pid']) {
				$pids = get_plv_childs($sellv['pid'],1);
				$where .= " and PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $sellv['pid'];
			}
			//合作伙伴级别
			if($post['PLEVEL_NAME']) {
				$where .= " and PARTNER_LEVEL = '".$post['PLEVEL_NAME']."'";
			}
			//合作伙伴状态
			if($post['PARTNER_STATUS'] != '') {
				$where .= " and PARTNER_STATUS = '".$post['PARTNER_STATUS']."'";
			}
			//合作伙伴类别
			if($post['PARTNER_G_FLAG'] != '') {
				$where .= " and PARTNER_G_FLAG = '".$post['PARTNER_G_FLAG']."'";
			}
			//合作伙伴名称
			if($post['PARTNER_NAME']) {
				$where .= " and PARTNER_NAME like '%".$post['PARTNER_NAME']."%'";
			}
			//合作伙伴ID
			if($post['PARTNER_MAP_ID']) {
				$where .= " and PARTNER_MAP_ID = '".$post['PARTNER_MAP_ID']."'";
			}
			//合作伙伴角色
			if($post['PLEVEL_MAP_ID'] != '') {
				$where .= " and PLEVEL_MAP_ID = '".$post['PLEVEL_MAP_ID']."'";
			}
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->MPartner)->countNewsPartner($where);
				$resdata = array(
					'count'	=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======			
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->MPartner)->getNewsPartnerlist($where, '*', $fiRow.','.$liRow);
			foreach($list as $key=>$val){
				$list[$key]['POS_COUNT']  = D($this->MDevice)->countNewsDevice("PARTNER_MAP_ID = '".$val['PARTNER_MAP_ID']."'");
				$list[$key]['SHOP_COUNT'] = D($this->MShop)->countNewShop("PARTNER_MAP_ID = '".$val['PARTNER_MAP_ID']."'");
			}			
			
			//分页参数
			$this->assign ( 'totalCount', 	C('PAGE_COUNT')==count($list) ? 1 : 0 );
	       	$this->assign ( 'numPerPage', 	'' );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
						
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		$this->assign ('partner_status_arr',C('CHECK_POINT.all'));	//合作伙伴审核状态数组
		$this->assign ('plevel_arr',		C('PLEVEL_NAME'));		//级别名称
		$this->assign ('partner_g_f', 		C('PARTNER_G_FLAG'));	//类型
		\Cookie::set  ('_currentUrl_', 	__SELF__);	
		$this->display();
	}
	/*
	* 合作伙伴管理 详情
	**/
	public function partner_show($tpl = 'partner_show') {		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//获取合作伙伴基本信息
		$partner_info = D($this->MPartner)->findPartner("a.PARTNER_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		//获取合作伙伴证照信息
		$pcert_info = D($this->MPcert)->findPcert("PARTNER_MAP_ID='".$id."'");
		//获取合作伙伴权限信息
		$pauth_info = D($this->MPauth)->findPauth("PARTNER_MAP_ID='".$id."'");
		//获取合作伙伴结算方式信息
		$pcls_info  = D($this->MPcls)->findPcls("PARTNER_MAP_ID='".$id."'");
		//获取合作伙伴银行帐户信息
		$pbact_info = D($this->MPbact)->findPbact("PARTNER_MAP_ID='".$id."'");
		//获取合作伙伴其他配置
		$pcfg_info = D($this->MPcfg)->findPcfg("PARTNER_MAP_ID='".$id."'");
		if(empty($partner_info) || empty($pcert_info) || empty($pauth_info) ||  empty($pcls_info) ||  empty($pbact_info)){
			$this->wrong("参数数据出错！");
		}
		$pcert_info['OTHER'] = explode(',', $pcert_info['OTHER_PHOTOS']);
		$this->assign ('bank_flag_redio',	 C('PARTNER_BANK_FLAG'));					//结算标志
		$this->assign ('settle_t_unit',		 C('SETTLE_T_UNIT'));						//结算周期
		$this->assign ('auth_trans_checked', str_split($pauth_info['AUTH_TRANS_MAP']));	//交易开通
		$this->assign ('partner_info', 	$partner_info);	//基础信息
		$this->assign ('pcert_info', 	$pcert_info);	//证照信息
		$this->assign ('pauth_info', 	$pauth_info);	//权限信息
		$this->assign ('pcls_info', 	$pcls_info);	//结算方式
		$this->assign ('pbact_info', 	$pbact_info);	//银行帐户
		$this->assign ('pcfg_info', 	$pcfg_info);	//其他配置
		$this->display($tpl);
	}
	/*
	* 合作伙伴管理 添加
	**/
	public function partner_add() {
		$partner = I('partner');
		$pcert   = I('pcert');
		//$pauth   = I('pauth');
		$pcls  	 = I('pcls');
		$pbact   = I('pbact');
		$pcfg    = I('pcfg');
		$plv     = I('plv');
		$glv     = I('glv');
		$home 	 = session('HOME');
		if($partner['submit'] == "partner_add") {
			//基本数据验证
			if(empty($partner['PARTNER_NAME']) || empty($partner['PARTNER_NAMEAB']) || empty($partner['ADDRESS']) || empty($partner['MANAGER']) || 
				empty($partner['MOBILE']) || empty($partner['CITY_NO'])){
				$this->wrong("请填写基本信息必填项! ");
			}
			//权限开通验证
			/*if($pauth['AUTH_TRANS_MAP'] ==''){
				$this->wrong("请填写权限信息必填项! ");
			}*/
			//结算方式验证
			/*if(empty($pcls['SETTLE_TOP_AMT']) || empty($pcls['SETTLE_FREE_AMT']) || empty($pcls['SETTLE_OFF_AMT'])){
				$this->wrong("请填写结算方式必填项! ");
			}*/
			//检测用户表操作员手机号
			$findmobile = D($this->MUser)->findUser("USER_MOBILE='".$partner['MOBILE']."'");
			if(!empty($findmobile)){
				$this->wrong("该合作方手机号已注册操作员，请更换！");
			}
			//银行帐户验证
			switch ($pbact['PARTNER_BANK_FLAG']) {
			 	case '0':
			 		$pbact['BANK_NAME1']    = $_REQUEST['org1_BANK_NAME'];
					$pbact['BANKACCT_BID1'] = $_REQUEST['org1_BANKACCT_BID'];
			 		if (empty($pbact['BANKACCT_NAME1']) || empty($pbact['BANKACCT_NO1']) || empty($pbact['BANKACCT_BID1']) || empty($pbact['BANK_NAME1']) ) {
			 			$this->wrong("请填写银行帐户必填项! ");
			 		}
			 		break;
			 	case '1':
			 		$pbact['BANK_NAME2']    = $_REQUEST['org2_BANK_NAME'];
					$pbact['BANKACCT_BID2'] = $_REQUEST['org2_BANKACCT_BID'];
			 		if (empty($pbact['BANKACCT_NAME2']) || empty($pbact['BANKACCT_NO2']) || empty($pbact['BANKACCT_BID2']) || empty($pbact['BANK_NAME2']) ) {
			 			$this->wrong("请填写银行帐户必填项! ");
			 		}
			 		break;
			}
			//其他配置项验证
			if($pcfg['CARD_OPENFEE']=='' ||$pcfg['DIV_PER']==''){
				$this->wrong("请填写其他配置必填项! ");
			}
			//组装基本数据
			if($plv['0']==''){
				$this->wrong("请选择合作伙伴归属! ");
			}
			//$home = session('HOME');
			switch ($partner['PARTNER_LEVEL']) {
				case '1':
					$partner['BRANCH_MAP_ID'] = $plv['0'];
					$partner['PARTNER_MAP_ID_P'] = 0;
					break;
				case '2':
					$partner['BRANCH_MAP_ID'] = $plv['0'];
					$partner['PARTNER_MAP_ID_P'] = $plv['1'];
					if(empty($plv['1'])){
						$this->wrong("请选择地市合作伙伴归属! ");
					}
					break;
				case '3':
					$partner['BRANCH_MAP_ID'] = $plv['0'];
					$partner['PARTNER_MAP_ID_P'] = $plv['2'];
					if(empty($plv['2'])){
						$this->wrong("请选择区县级合作伙伴归属! ");
					}
					break;
				case '4':
					$partner['BRANCH_MAP_ID'] = $plv['0'];
					$partner['PARTNER_MAP_ID_P'] = $plv['3'];
					if(empty($plv['3'])){
						$this->wrong("请选择创业合伙人合作伙伴归属! ");
					}
					break;
				default:
					if(empty($plv['3'])){
						$this->wrong("请选择合作伙伴级别! ");
					}
					break;
			}
			$partner_data = array(
				'BRANCH_MAP_ID'		=>	$partner['BRANCH_MAP_ID'],
				'PARTNER_LEVEL'		=>	$partner['PARTNER_LEVEL'],
				'PARTNER_MAP_ID_P'	=>	$partner['PARTNER_MAP_ID_P'] ? $partner['PARTNER_MAP_ID_P'] : 0,
				'PARTNER_P_FLAG'  	=>	0,
				'PARTNER_G_FLAG'  	=>	$partner['PARTNER_G_FLAG'],
				'PARTNER_MAP_ID_G'	=>	$glv['1'] ? $glv['1'] : 0,
				'PARTNER_NAME'		=>	$partner['PARTNER_NAME'],
				'PARTNER_NAMEAB'	=>	$partner['PARTNER_NAMEAB'],
				'PARTNER_STATUS'	=>	6,
				'PLEVEL_MAP_ID'		=>	($partner['PARTNER_LEVEL'] == 3) ? $partner['PARTNER_LEVEL1'] : $partner['PARTNER_LEVEL'],
				'SECURITY_MAP_ID1'	=>	$partner['SECURITY_MAP_ID1'] ? $partner['SECURITY_MAP_ID1'] : 0,
				'SECURITY_MAP_ID2'	=>	$partner['SECURITY_MAP_ID2'] ? $partner['SECURITY_MAP_ID2'] : 0,
				'AUTH_ZONE'			=>	$partner['AUTH_ZONE'] ? $partner['AUTH_ZONE'] : 0,
				//'JOIN_FEE'			=>	setMoney($partner['JOIN_FEE'], 6),
				//'FUND_AMT'			=>	setMoney($partner['FUND_AMT'], 6),
				'CITY_NO'			=>	$partner['CITY_NO'],				
				'ADDRESS'			=>	$partner['ADDRESS'],
				'ZIP'				=>	$partner['ZIP'],
				'TEL'				=>	$partner['TEL'],
				'MANAGER'			=>	$partner['MANAGER'],
				'MOBILE'			=>	$partner['MOBILE'],
				'EMAIL'				=>	$partner['EMAIL'],
				//'SALER_ID'			=>	$partner['SALER_ID'],
				//'SALER_NAME'		=>	$partner['SALER_NAME'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'CREATE_TIME'		=>	date("YmdHis"),
				'END_TIME'			=>	date("YmdHis",strtotime($partner['END_TIME']." 23:59:59"))
			);
			$m = M();
			$m->startTrans();	//启用事务
			//基础数据入库
			$partner_res = D($this->MPartner)->addPartner($partner_data);
			if($partner_res['state']!=0){
				$this->wrong('合作伙伴基础信息添加失败！');
			}

			//组装证件数据
			$pcert_data = array(
				'PARTNER_MAP_ID'=>	$partner_res['PARTNER_MAP_ID'],
				'PARTNER_STATUS'=>	0,
				'AGREEMENT_NO'	=>	$pcert['AGREEMENT_NO'],
				'REG_ADDR'		=>	getcity_name($pcert['CITY_NO']).$pcert['REG_ADDR'],
				'REG_ID'		=>	$pcert['REG_ID'],
				'TAX_ID'		=>	$pcert['TAX_ID'],
				'ORG_ID'		=>	$pcert['ORG_ID'],
				'LP_NAME'		=>	$pcert['LP_NAME'],
				'LP_ID'			=>	$pcert['LP_ID'],
				'REGID_PHOTO'	=>	$pcert['REGID_PHOTO'],
				'TAXID_PHOTO'	=>	$pcert['TAXID_PHOTO'],
				'ORGID_PHOTO'	=>	$pcert['ORGID_PHOTO'],
				'LP_D_PHOTO'	=>	$pcert['LP_D_PHOTO'],
				'LP_R_PHOTO'	=>	$pcert['LP_R_PHOTO'],
				'BANK_PHOTO'	=>	$pcert['BANK_PHOTO'],
				'RES'			=>	$pcert['RES']
			);
			//证件数据入库
			$pcert_res = D($this->MPcert)->addPcert($pcert_data);
			if($pcert_res['state']!=0){
				$m->rollback();//回滚
				$this->wrong('合作伙伴证件信息添加失败！');
			}

			//组装权限数据
			$pauth_data = array(
				'PARTNER_MAP_ID'	=>	$partner_res['PARTNER_MAP_ID'],
				'PARTNER_STATUS'	=>	0,
				'HOST_MAP_ID'		=>	0,
				'AUTH_TRANS_MAP'	=>	setStrzero('',128,'1'),	
				'AUTH_PAYS_MAP'		=>	setStrzero('',128,'1'),
			);
			//权限数据入库
			$pauth_res = D($this->MPauth)->addPauth($pauth_data);
			if($pauth_res['state']!=0){
				$m->rollback();//不成功，则回滚
				$this->wrong('合作伙伴权限信息添加失败！');
			}

			//组装结算方式数据
			$pcls_data = array(
				'PARTNER_MAP_ID'	=>	$partner_res['PARTNER_MAP_ID'],
				'PARTNER_STATUS'	=>	0,
				'SETTLE_T'			=>	$pcls['SETTLE_T'],
				'SETTLE_T_UNIT'		=>	$pcls['SETTLE_T_UNIT'],
				/*'SETTLE_TYPE'		=>	2,			//0：结算到合作伙伴 1：结算到商户 默认2保留
				'SETTLE_FLAG'		=>	$pcls['SETTLE_FLAG'] ? $pcls['SETTLE_FLAG'] : 1,
				'SETTLE_TOP_AMT'	=>	setMoney($pcls['SETTLE_TOP_AMT']),
				'SETTLE_FREE_AMT'	=>	setMoney($pcls['SETTLE_FREE_AMT']),
				'SETTLE_OFF_AMT'	=>	setMoney($pcls['SETTLE_OFF_AMT']),
				'SETTLE_OFF_FEE'	=>	$pcls['SETTLE_OFF_FEE'] ? setMoney($pcls['SETTLE_OFF_FEE']) : 0,
				'SETTLE_FEE'		=>	setMoney($pcls['SETTLE_FEE'])*/
			);
			//结算方式数据入库
			$pcls_res = D($this->MPcls)->addPcls($pcls_data);
			if($pcls_res['state']!=0){
				$m->rollback();		//不成功，则回滚
				$this->wrong('合作伙伴结算方式信息添加失败！');
			}
			//组装银行账户数据
			switch ($pbact['PARTNER_BANK_FLAG']) {
				case '0':			//对公账户变更
					$pbact_data = array(
						'PARTNER_MAP_ID'	=>	$partner_res['PARTNER_MAP_ID'],
						'PARTNER_STATUS'	=>	0,
						'PARTNER_BANK_FLAG'	=>	$pbact['PARTNER_BANK_FLAG'],
						'BANKACCT_NAME1'	=>	$pbact['BANKACCT_NAME1'] ? $pbact['BANKACCT_NAME1'] : '',
						'BANKACCT_NO1'		=>	$pbact['BANKACCT_NO1'] ? $pbact['BANKACCT_NO1'] : '',
						'BANKACCT_BID1'		=>	$pbact['BANKACCT_BID1'] ? $pbact['BANKACCT_BID1'] : '',
						'BANK_NAME1'		=>	$pbact['BANK_NAME1'] ? $pbact['BANK_NAME1'] : ''
					);
					break;
				case '1':			//对私账户变更
					$pbact_data = array(
						'PARTNER_MAP_ID'	=>	$partner_res['PARTNER_MAP_ID'],
						'PARTNER_STATUS'	=>	0,
						'PARTNER_BANK_FLAG'	=>	$pbact['PARTNER_BANK_FLAG'],
						'BANKACCT_NAME2'	=>	$pbact['BANKACCT_NAME2'] ? $pbact['BANKACCT_NAME2'] : '',
						'BANKACCT_NO2'		=>	$pbact['BANKACCT_NO2'] ? $pbact['BANKACCT_NO2'] : '',
						'BANKACCT_BID2'		=>	$pbact['BANKACCT_BID2'] ? $pbact['BANKACCT_BID2'] : '',
						'BANK_NAME2'		=>	$pbact['BANK_NAME2'] ? $pbact['BANK_NAME2'] : ''
					);
					break;
				default:
					$m->rollback();//不成功，则回滚
					$this->wrong('合作伙伴银行账户信息不正确,变更失败！');
					break;
			}

			//银行账户数据入库
			$pbact_res = D($this->MPbact)->addPbact($pbact_data);
			if($pbact_res['state']!=0){
				$m->rollback();//不成功，则回滚
				$this->wrong('合作伙伴银行账户信息添加失败！');
			}

			//组装其他配置数据
			$pcfg_data = array(
				'PARTNER_MAP_ID'=>	$partner_res['PARTNER_MAP_ID'],
				'DIV_FLAG'		=>	$pcfg['DIV_FLAG'],
				'CARD_OPENFEE'	=>	$pcfg['CARD_OPENFEE'] ? setMoney($pcfg['CARD_OPENFEE']) : 3000,
				'DIV_PER'		=>	$pcfg['DIV_PER'] ? $pcfg['DIV_PER'] : 20,
				'RAKE_FLAG'		=>	0,
				'CON_PER_RAKE'	=>	5000,
				'PLAT_PER_RAKE'	=>	5000
			);
			//其他配置数据入库
			$pcfg_res = D($this->MPcfg)->addPcfg($pcfg_data);
			if($pcfg_res['state']!=0){
				$m->rollback();//不成功，则回滚
				$this->wrong('合作伙伴其他配置信息添加失败！');
			}

			/*//创建操作员
			//用户编号
			$findno  = D($this->MUser)->findUser("USER_NO > '".($partner_res['PARTNER_MAP_ID'].'000')."' and USER_NO < '".($partner_res['PARTNER_MAP_ID'].'999')."'", 'max(USER_NO) as USER_NO');
			$user_no = $findno['USER_NO'] ? ($findno['USER_NO'] + 1) : ($partner_res['PARTNER_MAP_ID'].'001');
			//处理角色
			switch($partner_data['PARTNER_LEVEL']){
				case '1':	//市
					$role_id = C('JFB_SHI');
					break;
				case '2':	//县
					$role_id = C('JFB_XIAN');
					break;
				case '3':	//发卡点
					$role_id = C('JFB_CHUANG');
					break;
				default:
					$m->rollback();//不成功，则回滚
					$this->wrong('合作伙伴操作员添加失败！');
					break;
			}
			$role_data = D($this->MRole)->findRole("ROLE_ID='".$role_id."'", 'ROLE_NAME');
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'	=>	$partner_data['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'=>	$partner_res['PARTNER_MAP_ID'],
				'USER_NO'		=>	$user_no,
				'USER_MOBILE'	=>	$partner_data['MOBILE'],
				'USER_NAME'		=>	$partner_data['PARTNER_NAME'],
				'USER_FLAG'		=>	0,
				'USER_PASSWD'	=>	strtoupper(md5(strtoupper(md5('000000')))),
				'USER_LEVEL'	=>	$partner_data['PARTNER_LEVEL']+1,
				'USER_STATUS'	=>	0,
				'ROLE_ID'		=>	$role_id,
				'ROLE_NAME'		=>	$role_data['ROLE_NAME'],
				'EMAIL'			=>	$partner_data['EMAIL'],
				'PINERR_NUM'	=>	0,
				'LOGIN_IP'		=>	get_client_ip(),
				'CREATE_TIME'	=>	date("YmdHis"),
				'ACTIVE_TIME'	=>	date("YmdHis"),
				'UPDATE_TIME'	=>	date("YmdHis")
			);
			$user_res = D($this->MUser)->addUser($resdata);
			if ($user_res['state'] != '0') {
				$m->rollback();//不成功，则回滚
				$this->wrong('合作伙伴操作员添加失败！');
			}*/
			$m->commit();	//全部成功则提交
			$this->right($pcfg_res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//保险公司[意外险]
		$security_data[0] = D($this->MSecurity)->getSecuritymore('sec.SECURITY_STATUS != 1 and sr.SECURITY_TYPE = 1','sec.SECURITY_MAP_ID,sec.SECURITY_NAME','','','sec.SECURITY_MAP_ID asc');
		//保险公司[养老险]
		$security_data[1] = D($this->MSecurity)->getSecuritymore('sec.SECURITY_STATUS != 1 and sr.SECURITY_TYPE = 2','sec.SECURITY_MAP_ID,sec.SECURITY_NAME','','','sec.SECURITY_MAP_ID asc');
		$this->assign ('security_data',				$security_data);
		$this->assign ('partner_g_f', 				C('PARTNER_G_FLAG'));		//类型
		$this->assign ('auth_trans_map_checkbox', 	C('AUTH_TRANS_MAP'));		//交易开通
		$this->assign ('bank_flag_redio', 			C('PARTNER_BANK_FLAG'));	//结算标志
		if ($home['USER_LEVEL'] == 0) {
			$home['USER_LEVEL'] = 1;
			//$home['P_LEVEL'] = 2;
		}
		$this->assign ('home', 						$home);						//当前用户SESSION
		$this->display();
	}	
	/*
	* 合作伙伴管理 修改
	**/
	public function partner_edit() {
		$partner = I('partner');
		$pcert   = I('pcert');
		$pauth 	 = I('pauth');
		$pcls    = I('pcls');
		$pbact   = I('pbact');
		$pcfg    = I('pcfg');
		$plv     = I('plv');
		//$glv     = I('glv');
		$home 	 = session('HOME');
		if($partner['submit'] == "partner_edit") {
			//基本数据验证
			if(empty($partner['PARTNER_NAME']) || empty($partner['PARTNER_NAMEAB']) || empty($partner['ADDRESS']) || empty($partner['MANAGER']) || 
				empty($partner['MOBILE']) || empty($partner['CITY_NO'])){
				$this->wrong("请填写基本信息必填项! ");
			}
			if ($partner['PARTNER_STATUS'] != 0 && $partner['PARTNER_STATUS'] != 4) {
				$partner['PARTNER_STATUS'] = 6;
			}
			//权限开通验证
			/*if($pauth['AUTH_TRANS_MAP'] ==''){
				$this->wrong("请填写权限信息必填项! ");
			}*/
			//结算方式验证
			/*if(empty($pcls['SETTLE_TOP_AMT']) || empty($pcls['SETTLE_FREE_AMT']) || empty($pcls['SETTLE_OFF_AMT'])){
				$this->wrong("请填写结算方式必填项! ");
			}*/
			//银行帐户验证
			switch ($pbact['PARTNER_BANK_FLAG']) {
			 	case '0':
			 		$pbact['BANK_NAME1']    = $_REQUEST['org1_BANK_NAME'];
					$pbact['BANKACCT_BID1'] = $_REQUEST['org1_BANKACCT_BID'];
			 		if (empty($pbact['BANKACCT_NAME1']) || empty($pbact['BANKACCT_NO1']) || empty($pbact['BANKACCT_BID1']) || empty($pbact['BANK_NAME1']) ) {
			 			$this->wrong("请填写银行帐户必填项! ");
			 		}
			 		break;
			 	case '1':
				 	$pbact['BANK_NAME2']    = $_REQUEST['org2_BANK_NAME'];
					$pbact['BANKACCT_BID2'] = $_REQUEST['org2_BANKACCT_BID'];
			 		if (empty($pbact['BANKACCT_NAME2']) || empty($pbact['BANKACCT_NO2']) || empty($pbact['BANKACCT_BID2']) || empty($pbact['BANK_NAME2']) ) {
			 			$this->wrong("请填写银行帐户必填项! ");
			 		}
			 		break;
			}
			//其他配置项验证
			if ($pcfg) {
				if($pcfg['CARD_OPENFEE']=='' ||$pcfg['DIV_PER']==''){
					$this->wrong("请填写其他配置必填项! ");
				}
			}
			
			//组装基本数据
			if($plv['0']==''){
				$this->wrong("请选择合作伙伴归属! ");
			}
			//$home = session('HOME');
			switch ($partner['PARTNER_LEVEL']) {
				case '1':
					$partner['BRANCH_MAP_ID'] = $plv['0'];
					$partner['PARTNER_MAP_ID_P'] = 0;
					break;
				case '2':
					$partner['BRANCH_MAP_ID'] = $plv['0'];
					$partner['PARTNER_MAP_ID_P'] = $plv['1'];
					if(empty($plv['1'])){
						$this->wrong("请选择地市合作伙伴归属! ");
					}
					break;
				case '3':
					$partner['BRANCH_MAP_ID'] = $plv['0'];
					$partner['PARTNER_MAP_ID_P'] = $plv['2'];
					if(empty($plv['2'])){
						$this->wrong("请选择区县级合作伙伴归属! ");
					}
					break;
				case '4':
					$partner['BRANCH_MAP_ID'] = $plv['0'];
					$partner['PARTNER_MAP_ID_P'] = $plv['3'];
					if(empty($plv['3'])){
						$this->wrong("请选择创业合伙人合作伙伴归属! ");
					}
					break;
				default:
					if(empty($plv['3'])){
						$this->wrong("请选择合作伙伴级别! ");
					}
					break;
			}
			$partner_data = array(
				'BRANCH_MAP_ID'		=>	$partner['BRANCH_MAP_ID'],
				//'PARTNER_LEVEL'		=>	$partner['PARTNER_LEVEL'],
				'PARTNER_MAP_ID_P'	=>	$partner['PARTNER_MAP_ID_P'] ? $partner['PARTNER_MAP_ID_P'] : 0,
				'PARTNER_P_FLAG'  	=>	0,
				'PARTNER_G_FLAG'  	=>	$partner['PARTNER_G_FLAG'],
				'PARTNER_MAP_ID_G'	=>	$partner['PARTNER_MAP_ID_G'] ? $partner['PARTNER_MAP_ID_G'] : 0,
				'PARTNER_NAME'		=>	$partner['PARTNER_NAME'],
				'PARTNER_NAMEAB'	=>	$partner['PARTNER_NAMEAB'],
				'PARTNER_STATUS'	=>	$partner['PARTNER_STATUS'],
				'PLEVEL_MAP_ID'		=>	($partner['PARTNER_LEVEL'] == 3) ? $partner['PARTNER_LEVEL1'] : $partner['PARTNER_LEVEL'],
				'SECURITY_MAP_ID1'	=>	$partner['SECURITY_MAP_ID1'] ? $partner['SECURITY_MAP_ID1'] : 0,
				'SECURITY_MAP_ID2'	=>	$partner['SECURITY_MAP_ID2'] ? $partner['SECURITY_MAP_ID2'] : 0,
				'AUTH_ZONE'			=>	$partner['AUTH_ZONE'] ? $partner['AUTH_ZONE'] : 0,
				//'JOIN_FEE'			=>	setMoney($partner['JOIN_FEE'], 6),
				//'FUND_AMT'			=>	setMoney($partner['FUND_AMT'], 6),
				'CITY_NO'			=>	$partner['CITY_NO'],				
				'ADDRESS'			=>	$partner['ADDRESS'],
				'ZIP'				=>	$partner['ZIP'],
				'TEL'				=>	$partner['TEL'],
				'MANAGER'			=>	$partner['MANAGER'],
				'MOBILE'			=>	$partner['MOBILE'],
				'EMAIL'				=>	$partner['EMAIL'],
				//'SALER_ID'			=>	$partner['SALER_ID'],
				//'SALER_NAME'		=>	$partner['SALER_NAME'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'CREATE_TIME'		=>	date("YmdHis"),
				'END_TIME'			=>	date("YmdHis",strtotime($partner['END_TIME']." 23:59:59"))
			);
			if ($home['BRANCH_MAP_ID'] == C('SPECIAL_USER')) {
				$partner_data['PARTNER_LEVEL'] = $partner['PARTNER_LEVEL'];
				$partner_data['PLEVEL_MAP_ID'] = ($partner['PARTNER_LEVEL'] == 3) ? $partner['PARTNER_LEVEL1'] : $partner['PARTNER_LEVEL'];
			}else{
				if ($partner['PARTNER_STATUS'] != '0') {
					$partner_data['PARTNER_LEVEL'] = $partner['PARTNER_LEVEL'];
					$partner_data['PLEVEL_MAP_ID'] = ($partner['PARTNER_LEVEL'] == 3) ? $partner['PARTNER_LEVEL1'] : $partner['PARTNER_LEVEL'];
				}
			}
			
			//判断修改的基本信息和全部信息的状态
			/*if ($partner['PARTNER_STATUS'] != 0 && $partner['PARTNER_STATUS'] != 4) {
				$partner_data['PARTNER_STATUS'] = 6;
			}*/

			$m = M();
			$m->startTrans();	//启用事务
			//基础数据更改
			$where = "PARTNER_MAP_ID = '".$partner['PARTNER_MAP_ID']."'";
			$partner_res = D($this->MPartner)->updatePartner($where, $partner_data);
			if($partner_res['state']!=0){
				$this->wrong('合作伙伴基础信息编辑失败');
			}

			if (!empty($pcert)) {
				//组装证件数据
				$pcert_data = array(
					'AGREEMENT_NO'	=>	$pcert['AGREEMENT_NO'],
					'REG_ADDR'		=>	$pcert['REG_ADDR'],
					'REG_ID'		=>	$pcert['REG_ID'],
					'TAX_ID'		=>	$pcert['TAX_ID'],
					'ORG_ID'		=>	$pcert['ORG_ID'],
					'LP_NAME'		=>	$pcert['LP_NAME'],
					'LP_ID'			=>	$pcert['LP_ID'],
					'REGID_PHOTO'	=>	$pcert['REGID_PHOTO'],
					'TAXID_PHOTO'	=>	$pcert['TAXID_PHOTO'],
					'ORGID_PHOTO'	=>	$pcert['ORGID_PHOTO'],
					'LP_D_PHOTO'	=>	$pcert['LP_D_PHOTO'],
					'LP_R_PHOTO'	=>	$pcert['LP_R_PHOTO'],
					'BANK_PHOTO'	=>	$pcert['BANK_PHOTO'],
					'OTHER_PHOTOS'	=>	$pcert['OTHER_PHOTOS']
				);
				//证件数据变更
				$pcert_res = D($this->MPcert)->updatePcert($where, $pcert_data);
				if($pcert_res['state']!=0){
					$m->rollback();//不成功，则回滚
					$this->wrong('合作伙伴证件信息变更失败！');
				}
			}
			//组装权限数据
			/*if (!empty($pauth)) {
				$pauth_data = array(
					'AUTH_TRANS_MAP'=>	$pauth['AUTH_TRANS_MAP']
				);
				//权限数据入库
				$pauth_res = D($this->MPauth)->updatePauth($where,$pauth_data);
				if($pauth_res['state']!=0){
					$m->rollback();//不成功，则回滚
					$this->wrong('合作伙伴权限信息变更失败！');
				}
			}*/
			//组装结算方式数据
			if (!empty($pcls)) {
				$pcls_data = array(
					'SETTLE_T'			=>	$pcls['SETTLE_T'],
					'SETTLE_T_UNIT'		=>	$pcls['SETTLE_T_UNIT'],
					/*'SETTLE_TYPE'		=>	2,			//0：结算到合作伙伴 1：结算到商户 默认2保留
					'SETTLE_FLAG'		=>	$pcls['SETTLE_FLAG'] ? $pcls['SETTLE_FLAG'] : 0,	//0：不足顺延下个周期， 1：本周期结算
					'SETTLE_TOP_AMT'	=>	setMoney($pcls['SETTLE_TOP_AMT']),
					'SETTLE_FREE_AMT'	=>	setMoney($pcls['SETTLE_FREE_AMT']),
					'SETTLE_OFF_AMT'	=>	setMoney($pcls['SETTLE_OFF_AMT']),
					'SETTLE_OFF_FEE'	=>	$pcls['SETTLE_OFF_FEE'] ? setMoney($pcls['SETTLE_OFF_FEE']) : 0,
					'SETTLE_FEE'		=>	setMoney($pcls['SETTLE_FEE'])*/
				);
				//结算方式数据入库
				$pcls_res = D($this->MPcls)->updatePcls($where,$pcls_data);
				if($pcls_res['state']!=0){
					$m->rollback();//不成功，则回滚
					$this->wrong('合作伙伴结算方式信息变更失败！');
				}
			}
			//组装银行账户数据
			if (!empty($pbact)) {
				switch ($pbact['PARTNER_BANK_FLAG']) {
					case '0':			//对公账户变更
						$pbact_data = array(
							'PARTNER_BANK_FLAG'	=>	$pbact['PARTNER_BANK_FLAG'],
							'BANKACCT_NAME1'	=>	$pbact['BANKACCT_NAME1'] ? $pbact['BANKACCT_NAME1'] : '',
							'BANKACCT_NO1'		=>	$pbact['BANKACCT_NO1'] ? $pbact['BANKACCT_NO1'] : '',
							'BANKACCT_BID1'		=>	$pbact['BANKACCT_BID1'] ? $pbact['BANKACCT_BID1'] : '',
							'BANK_NAME1'		=>	$pbact['BANK_NAME1'] ? $pbact['BANK_NAME1'] : ''
						);
						break;
					case '1':			//对私账户变更
						$pbact_data = array(
							'PARTNER_BANK_FLAG'	=>	$pbact['PARTNER_BANK_FLAG'],
							'BANKACCT_NAME2'	=>	$pbact['BANKACCT_NAME2'] ? $pbact['BANKACCT_NAME2'] : '',
							'BANKACCT_NO2'		=>	$pbact['BANKACCT_NO2'] ? $pbact['BANKACCT_NO2'] : '',
							'BANKACCT_BID2'		=>	$pbact['BANKACCT_BID2'] ? $pbact['BANKACCT_BID2'] : '',
							'BANK_NAME2'		=>	$pbact['BANK_NAME2'] ? $pbact['BANK_NAME2'] : ''
						);
						break;
					default:
						$m->rollback();//不成功，则回滚
						$this->wrong('合作伙伴银行账户信息不正确,变更失败！');
						break;
				}
				//银行账户数据入库
				$pbact_res = D($this->MPbact)->updatePbact($where,$pbact_data);
				if($pbact_res['state']!=0){
					$m->rollback();//不成功，则回滚
					$this->wrong('合作伙伴银行账户信息变更失败！');
				}
			}
			//组装其他配置数据
			if ($pcfg['DIV_FLAG']!='') {
				$pcfg_data = array(
					'DIV_FLAG'		=>	$pcfg['DIV_FLAG'],
					'CARD_OPENFEE'	=>	$pcfg['CARD_OPENFEE'] ? setMoney($pcfg['CARD_OPENFEE']) : 3000,
					'DIV_PER'		=>	$pcfg['DIV_PER'] ? $pcfg['DIV_PER'] : 20
				);
				//其他配置数据入库
				$pcfg_res = D($this->MPcfg)->updatePcfg($where,$pcfg_data);
				if($pcfg_res['state']!=0){
					$m->rollback();//不成功，则回滚
					$this->wrong('合作伙伴结算方式信息变更失败！');
				}
			}
			
			$m->commit();//成功则提交
			$this->right('信息变更成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//获取合作伙伴基本信息
		$partner_info = D($this->MPartner)->findPartner("a.PARTNER_MAP_ID='".$id."'","a.*");
		//获取合作伙伴证照信息
		$pcert_info = D($this->MPcert)->findPcert("PARTNER_MAP_ID='".$id."'");
		$pcert_info['OTHER'] = explode(',', $pcert_info['OTHER_PHOTOS']);
		//获取合作伙伴权限信息
	//	$pauth_info = D($this->MPauth)->findPauth("PARTNER_MAP_ID='".$id."'");
		//获取合作伙伴结算方式信息
		$pcls_info = D($this->MPcls)->findPcls("PARTNER_MAP_ID='".$id."'");
		//获取合作伙伴银行帐户信息
		$pbact_info = D($this->MPbact)->findPbact("PARTNER_MAP_ID='".$id."'");
		//获取合作伙伴其他配置
		$pcfg_info = D($this->MPcfg)->findPcfg("PARTNER_MAP_ID='".$id."'");
		if(empty($partner_info) || empty($pcert_info) || empty($pcls_info) || empty($pbact_info)){
			$this->wrong("参数数据出错！");
		}
		//保险公司[意外险]
		$security_data[0] = D($this->MSecurity)->getSecuritymore('sec.SECURITY_STATUS != 1 and sr.SECURITY_TYPE = 1','sec.SECURITY_MAP_ID,sec.SECURITY_NAME','','','sec.SECURITY_MAP_ID asc');
		//保险公司[养老险]
		$security_data[1] = D($this->MSecurity)->getSecuritymore('sec.SECURITY_STATUS != 1 and sr.SECURITY_TYPE = 2','sec.SECURITY_MAP_ID,sec.SECURITY_NAME','','','sec.SECURITY_MAP_ID asc');

		$this->assign('security_data',	$security_data);
		$this->assign ('auth_trans_checked', str_split($pauth_info['AUTH_TRANS_MAP']));	//交易已开通数据
		$this->assign ('partner_info', 	$partner_info);	
		$this->assign ('pcert_info', 	$pcert_info);
		$this->assign ('pcls_info',  	$pcls_info);	
		$this->assign ('pbact_info', 	$pbact_info);
		$this->assign ('pcfg_info', 	$pcfg_info);
		$this->assign ('auth_trans_map_checkbox', 	$home['AUTH_TRANS_MAP']);	//交易开通
		$this->assign ('bank_flag_redio', 			C('PARTNER_BANK_FLAG'));	//结算标志
		$this->assign ('partner_g_f', 				C('PARTNER_G_FLAG'));		//类型
		if ($home['USER_LEVEL'] == 0) {
			$home['USER_LEVEL'] = 1;
		}
		$this->assign ('home', $home);											//当前用户SESSION
		//获取当前信息状态
		$where = "PARTNER_MAP_ID = '".$partner['PARTNER_MAP_ID']."'";
		if ($partner_info['PARTNER_STATUS'] == 0 || $partner_info['PARTNER_STATUS'] == 4) {
			$this->display('partner_edit2');
		}else{
			$this->display('partner_edit');
		}
	}
	/*
	* 合作伙伴管理 审核
	**/
	public function partner_check() {
		$post = I('post');
		if ($post['submit'] == "partner_check") {
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '1',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【合作伙伴进件】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['PARTNER_MAP_ID']) || $check_data['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$where = 'PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID'];
			//修改状态
			$partner_res = D($this->MPartner)->updatePartner($where,array('PARTNER_STATUS' => $post['CHECK_POINT']));	//基本信息状态修改
			if (!$partner_res) {
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
		$partnerstatus = D($this->MPartner)->findPartner('a.PARTNER_MAP_ID = "'.$id.'"','a.PARTNER_STATUS');
		if ($partnerstatus['PARTNER_STATUS'] != 6) {
			$this->wrong('当前状态不允许初审操作');
		}
		$this->partner_show('partner_check');
	}
	/*
	* 合作伙伴管理 复审
	**/
	public function partner_recheck() {
		$post = I('post');
		if ($post['submit'] == "partner_recheck") {
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '1',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【合作伙伴进件】复核操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['PARTNER_MAP_ID']) || $check_data['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$where = 'PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID'];
			//修改状态
			$partner_res = D($this->MPartner)->updatePartner($where,array('PARTNER_STATUS' => $post['CHECK_POINT']));	//基本信息状态修改
			if (!$partner_res) {
				$this->wrong("审核操作失败！");
			}


			//创建操作员
			//判断当前状态是否符合复审操作
			$partner_data = D($this->MPartner)->findPartner('a.PARTNER_MAP_ID = "'.$post['PARTNER_MAP_ID'].'"','a.*,b.*');
			//用户编号
			$findno  = D($this->MUser)->findUser("USER_NO > '".($post['PARTNER_MAP_ID'].'000')."' and USER_NO < '".($post['PARTNER_MAP_ID'].'999')."'", 'max(USER_NO) as USER_NO');
			$user_no = $findno['USER_NO'] ? ($findno['USER_NO'] + 1) : ($post['PARTNER_MAP_ID'].'001');
			//处理角色
			switch($partner_data['PARTNER_LEVEL']){
				case '1':	//市
					$role_id = C('JFB_SHI');
					break;
				case '2':	//县
					$role_id = C('JFB_XIAN');
					break;
				case '3':	//发卡点
					$role_id = C('JFB_CHUANG');
					break;
				default:
					$m->rollback();//不成功，则回滚
					$this->wrong('合作伙伴操作员添加失败！');
					break;
			}
			$role_data = D($this->MRole)->findRole("ROLE_ID='".$role_id."'", 'ROLE_NAME');
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'	=>	$partner_data['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'=>	$partner_data['PARTNER_MAP_ID'],
				'USER_NO'		=>	$user_no,
				'USER_MOBILE'	=>	$partner_data['MOBILE'],
				'USER_NAME'		=>	$partner_data['PARTNER_NAME'],
				'USER_FLAG'		=>	0,
				'USER_PASSWD'	=>	strtoupper(md5(strtoupper(md5('000000')))),
				'USER_LEVEL'	=>	$partner_data['PARTNER_LEVEL']+1,
				'USER_STATUS'	=>	0,
				'ROLE_ID'		=>	$role_id,
				'ROLE_NAME'		=>	$role_data['ROLE_NAME'],
				'EMAIL'			=>	$partner_data['EMAIL'],
				'PINERR_NUM'	=>	0,
				'LOGIN_IP'		=>	get_client_ip(),
				'CREATE_TIME'	=>	date("YmdHis"),
				'ACTIVE_TIME'	=>	date("YmdHis"),
				'UPDATE_TIME'	=>	date("YmdHis")
			);
			$user_res = D($this->MUser)->addUser($resdata);
			if ($user_res['state'] != '0') {
				$m->rollback();//不成功，则回滚
				$this->wrong('合作伙伴操作员添加失败！');
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
		$partnerstatus = D($this->MPartner)->findPartner('a.PARTNER_MAP_ID = "'.$id.'"','a.PARTNER_STATUS');
		if ($partnerstatus['PARTNER_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '2'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$this->partner_show('partner_check');
	}

	public function partner_adduser()
	{
		$home = session('HOME');
		if (empty($home['USER_ID'])) {
			echo "笨蛋，先登录";exit;
		}
		//批量修改状态
		/*$bid = I('bid');
		if (empty($bid)) {
			echo "<h1>缺少参数！</h1>";exit;
		}*/
		$where = "a.BRANCH_MAP_ID = 7121 and a.PARTNER_STATUS = 6";
		//$where1 = "BRANCH_MAP_ID = 7121 and PARTNER_STATUS = 6";
		$upartner = array('PARTNER_STATUS' => 0 );
		/*$result = M('partner')->where($where1)->save($upartner);
		if($result === false) {
			echo "<h1>修改失败！</h1>";exit;
		}
		echo "<h1>成功修改".$result."个合作伙伴！</h1>";*/

		//批量添加操作员
		$list  = D($this->MPartner)->getPartnerlist($where, 'a.*', '', 'a.PARTNER_MAP_ID DESC');
		//统计商户数量
		$n = 0;
		foreach ($list as $key => $val) {
			M('partner')->where('PARTNER_MAP_ID = "'.$val['PARTNER_MAP_ID'].'"')->save($upartner);
			//用户编号
			$findno  = D($this->MUser)->findUser("USER_NO = '".($val['PARTNER_MAP_ID'].'001')."'", 'USER_NO');
			if (!empty($findno['USER_NO'])) {
				continue;
			}
			$user_no = $val['PARTNER_MAP_ID'].'001';
			//处理角色
			switch($val['PARTNER_LEVEL']){
				case '1':	//市
					$role_id = C('JFB_SHI');
					break;
				case '2':	//县
					$role_id = C('JFB_XIAN');
					break;
				case '3':	//发卡点
					$role_id = C('JFB_CHUANG');
					break;
				default:
					echo $val['PARTNER_MAP_ID'].' 合作伙伴操作员添加失败！<br />';
					break;
			}
			$role_data = D($this->MRole)->findRole("ROLE_ID='".$role_id."'", 'ROLE_NAME');
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'	=>	$val['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'=>	$val['PARTNER_MAP_ID'],
				'USER_NO'		=>	$user_no,
				'USER_MOBILE'	=>	$val['MOBILE'],
				'USER_NAME'		=>	$val['PARTNER_NAME'],
				'USER_FLAG'		=>	0,
				'USER_PASSWD'	=>	strtoupper(md5(strtoupper(md5('000000')))),
				'USER_LEVEL'	=>	$val['PARTNER_LEVEL']+1,
				'USER_STATUS'	=>	0,
				'ROLE_ID'		=>	$role_id,
				'ROLE_NAME'		=>	$role_data['ROLE_NAME'],
				'EMAIL'			=>	$val['EMAIL'],
				'PINERR_NUM'	=>	0,
				'LOGIN_IP'		=>	get_client_ip(),
				'CREATE_TIME'	=>	date("YmdHis"),
				'ACTIVE_TIME'	=>	date("YmdHis"),
				'UPDATE_TIME'	=>	date("YmdHis")
			);
			$user_res = D($this->MUser)->addUser($resdata);
			if ($user_res['state'] != '0') {
				echo $val['PARTNER_MAP_ID'].' 合作伙伴操作员添加失败！<br />';
			}
			$n++;
		}
		echo '已经添加'.$n."个操作员";
	}
	/*
	* 合作伙伴管理 暂停
	**/
	public function partner_close() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		if(is_array($id)){
			$where = array('PARTNER_MAP_ID'=> array('in', implode(',', $id)));
		}else{
			$where = array('PARTNER_MAP_ID'=> array('eq', $id));
		}
		//判断操作项状态
		$partnerstatus = M('partner')->where($where)->field('PARTNER_STATUS')->select();
		foreach ($partnerstatus as $key => $value) {
			if ($value['PARTNER_STATUS']!='0') {
				$this->wrong('当前状态下不允许暂停业务操作！');
			}
		}
		$res = D($this->MPartner)->updatePartner($where, array('PARTNER_STATUS'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 合作伙伴管理 恢复开通
	**/
	public function partner_open() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		if(is_array($id)){
			$where = array('PARTNER_MAP_ID'=> array('in', implode(',', $id)));
		}else{
			$where = array('PARTNER_MAP_ID'=> array('eq', $id));
		}
		//判断操作项状态
		$partnerstatus = M('partner')->where($where)->field('PARTNER_STATUS')->select();
		foreach ($partnerstatus as $key => $value) {
			if ($value['PARTNER_STATUS']!='1') {
				$this->wrong('当前状态下不允许恢复业务操作！');
			}
		}
		$res = D($this->MPartner)->updatePartner($where, array('PARTNER_STATUS'=> 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	
	/*
	* 合作伙伴管理 注销
	**/
	public function partner_cancel() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$where = "PARTNER_MAP_ID = ".$id;
		//判断操作项状态
		$partner_res = D($this->MPartner)->findPartnerOne($where,'PARTNER_MAP_ID_P,PARTNER_STATUS,PARTNER_LEVEL');
		if ($partner_res['PARTNER_STATUS']!='0' || $partner_res['PARTNER_LEVEL']!='3') {
			$this->wrong('合作伙伴注销条件必须当前状态为正常, 并且级别为创业合伙人的才允许注销操作！');
		}
		/*$p_res = D($this->MPartner)->findPartnerOne('PARTNER_MAP_ID_P = "'.$id.'"','PARTNER_MAP_ID,PARTNER_STATUS,PARTNER_LEVEL');
		if (!empty($p_res['PARTNER_MAP_ID'])) {
			$res_no +=1;
			$res_str .= $res_no.'. 该合作伙伴还有下级组织, 不允许注销操作！<br />';
		}*/
		$res_str = '';
		//判断当前合作伙伴下是否还有未使用的卡
		$card_res = D($this->GVipcard)->countVipcard($where.' and CARD_STATUS = 1');
		if ($card_res != '0') {
			$this->wrong('该合作伙伴当有空卡未回收, 不允许注销操作！');
		}
		
		//报错提示信息
		if (!empty($res_str)) {
			$this->wrong($res_str);
		}
		$res = D($this->MPartner)->updatePartner($where, array('PARTNER_STATUS'=> '2'));
		//注销操作员
		$user_data = array('USER_STATUS' => 2);
		$user_res = D($this->MUser)->updateUser($where, $user_data);
		if($user_res['state'] != 0){
			$this->wrong('操作失败');
		}

		//如果当前合作伙伴有推荐商户，则变更到当前合作伙伴的上一级
		$mshop = D($this->MShop);
		$shop_res = $mshop->getShop_select('PARTNER_MAP_ID_R = "'.$id.'"');
		if (!empty($shop_res)) {
			foreach ($shop_res as $key => $value) {
				$mshop->updateShop('SHOP_MAP_ID = "'.$value[0].'"', array('PARTNER_MAP_ID_R' => $partner_res['PARTNER_MAP_ID_P']));
			}
		}

		//会员归属为上一级
		$mvip = D($this->GVip);
		$vip_res = $mvip->countNewsVip($where);
		if ($vip_res) {
			$mvip->updateVip($where, array('PARTNER_MAP_ID' => $partner_res['PARTNER_MAP_ID_P']));
		}
		
		//卡归属为上一级
		$mvipcard = D($this->GVipcard);
		$vipcard_res = $mvipcard->countVipcard($where,$up_data);
		if ($vipcard_res) {
			$up_data = array(
				'PARTNER_MAP_ID' => $partner_res['PARTNER_MAP_ID_P'],
				'PARTNER_MAP_ID1' => $partner_res['PARTNER_MAP_ID_P'],
			);
			$mvipcard->updateVipcard($where, $up_data);
		}
		$this->right('操作成功', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 合作伙伴管理 删除
	**/
	public function partner_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$where = 'PARTNER_MAP_ID = "'.$id.'"';
		//判断操作项状态
		$partner_res = D($this->MPartner)->findPartnerOne($where,'PARTNER_STATUS,PARTNER_LEVEL');
		if ($partner_res['PARTNER_STATUS']=='0') {
			$this->wrong('合作伙伴状态为正常, 不允许删除操作！');
		}
		$p_res = D($this->MPartner)->findPartnerOne('PARTNER_MAP_ID_P = "'.$id.'"','PARTNER_MAP_ID,PARTNER_STATUS,PARTNER_LEVEL');
		if (!empty($p_res['PARTNER_MAP_ID'])) {
			$this->wrong('该合作伙伴还有下级组织, 不允许删除操作！');
		}
		$res_str = '';
		//判断当前合作伙伴下是否还有未使用的卡
		$card_res = D($this->GVipcard)->countVipcard('PARTNER_MAP_ID = "'.$id.'" and CARD_STATUS = 1');
		if ($card_res != '0') {
			$res_str .= '该合作伙伴当前下面还有未使用的卡片, 不能执行删除操作！<br />';
		}
		//判断当前合作伙伴下不能有正常状态商户
		$shop_res = D($this->MShop)->countShop('s.PARTNER_MAP_ID = "'.$id.'"');
		if ($shop_res != '0') {
			$res_str .= '该合作伙伴当前下面还有商户, 不能执行删除操作！';
		}
		//报错提示信息
		if (!empty($res_str)) {
			$this->wrong($res_str);
		}
		//删除基本信息
		$partner_res = D($this->MPartner)->delPartner($where);
		if ($partner_res['state']!='0') {
			$this->wrong($partner_res['msg']);
		}

		$this->right($partner_res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	
	
	/*
	* 合作伙伴证照变更管理 列表
	**/
	public function pcert() {
		$pcert_status_check = C('CHECK_POINT.check');
		$post = I('post');		
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'PARTNER_STATUS'	=>	I('PARTNER_STATUS'),
				'bid'				=>	I('bid'),
				'pid'				=>	I('pid'),
				'PLEVEL_NAME'		=>	I('PLEVEL_NAME'),
				'PARTNER_G_FLAG'	=>	I('PARTNER_G_FLAG'),
				'PARTNER_NAME'		=>	I('PARTNER_NAME'),
				'PLEVEL_MAP_ID'		=>	I('PLEVEL_MAP_ID'),
			);
			$ajax_soplv = array(
				'bid'				=>	I('bid'),
				'pid'				=>	I('pid'),		
			);
		}
		//===结束=======
		if($post['submit'] == "pcert"){
			$where = "1=1";
			//===优化统计===
			$sellv = $ajax == 'loading' ? $ajax_soplv : filter_data('soplv');	//列表查询
			//===结束=======
			//所属归属
			if($sellv['bid']) {
				$where .= " and ag.BRANCH_MAP_ID = '".$sellv['bid']."'";
			}
			//合作伙伴id
			if($sellv['pid']) {
				$pids = get_plv_childs($sellv['pid'],1);
				$where .= " and ac.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $sellv['pid'];
			}
			//合作伙伴级别
			if($post['PLEVEL_NAME']) {
				$where .= " and ag.PARTNER_LEVEL = '".$post['PLEVEL_NAME']."'";
			}
			//合作伙伴状态
			if($post['PARTNER_STATUS'] != '') {
				if($post['PARTNER_STATUS'] == 0){
					$where .= " and ac.PARTNER_STATUS = '".$post['PARTNER_STATUS']."'";
				}else{
					$where .= " and tmp.PARTNER_STATUS = '".$post['PARTNER_STATUS']."'";
				}
			}
			//合作伙伴类别
			if($post['PARTNER_G_FLAG'] != '') {
				$where .= " and ag.PARTNER_G_FLAG = '".$post['PARTNER_G_FLAG']."'";
			}
			//合作伙伴名称
			if($post['PARTNER_NAME']) {
				$where .= " and ag.PARTNER_NAME like '%".$post['PARTNER_NAME']."%'";
			}
			//合作伙伴角色
			if($post['PLEVEL_MAP_ID'] != '') {
				$where .= " and ag.PLEVEL_MAP_ID = '".$post['PLEVEL_MAP_ID']."'";
			}
			$where .= " and ac.PARTNER_STATUS != '2'";
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->MPcert)->countPcert($where);
				$resdata = array(
					'count'	=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->MPcert)->getPcertlist($where, 'ac.PARTNER_MAP_ID,ac.PARTNER_STATUS,ac.RES,ag.PARTNER_LEVEL,ag.PARTNER_NAME,tmp.PARTNER_MAP_ID as TMP_ID,tmp.PARTNER_STATUS as TMP_STATUS', $fiRow.','.$liRow, 'TMP_ID desc,ac.PARTNER_MAP_ID desc');
			foreach ($list as $key => $value) {
				$check_no = '2'.setStrzero($value['PARTNER_MAP_ID'],15);
				$list[$key]['CHECK_DESC'] = get_check_note($check_no)['CHECK_DESC'];
			}			
			
			//分页参数
			$this->assign ( 'totalCount', 	C('MAX_PAGE') );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
						
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		
		\Cookie::set ('_currentUrl_', 	__SELF__);
		//合作伙伴审核状态数组
		$this->assign ('plevel_arr',		C('PLEVEL_NAME'));		//级别名称
		$this->assign ('partner_status_arr',C('CHECK_POINT.check'));//合作伙伴审核状态数组
		$this->assign ('plevel_arr',		C('PLEVEL_NAME'));		//合作伙伴级别名称
		$this->assign ('partner_g_f', 		C('PARTNER_G_FLAG'));	//合作伙伴类型
		$this->display();
	}
	/*
	* 合作伙伴证照变更管理 修改
	**/
	public function pcert_edit() {
		$pcert = I('pcert');
		if($pcert['submit'] == "pcert_edit") {
			$home = session('HOME');
			//组装证件数据
			$pcert_data = array(
				'PARTNER_MAP_ID'=>	$pcert['PARTNER_MAP_ID'],
				'PARTNER_STATUS'=>	6,
				'AGREEMENT_NO'	=>	$pcert['AGREEMENT_NO'],
				'REG_ADDR'		=>	$pcert['REG_ADDR'],
				'REG_ID'		=>	$pcert['REG_ID'],
				'TAX_ID'		=>	$pcert['TAX_ID'],
				'ORG_ID'		=>	$pcert['ORG_ID'],
				'LP_NAME'		=>	$pcert['LP_NAME'],
				'LP_ID'			=>	$pcert['LP_ID'],
				'REGID_PHOTO'	=>	$pcert['REGID_PHOTO'],
				'TAXID_PHOTO'	=>	$pcert['TAXID_PHOTO'],
				'ORGID_PHOTO'	=>	$pcert['ORGID_PHOTO'],
				'LP_D_PHOTO'	=>	$pcert['LP_D_PHOTO'],
				'LP_R_PHOTO'	=>	$pcert['LP_R_PHOTO'],
				'BANK_PHOTO'	=>	$pcert['BANK_PHOTO'],
				'OTHER_PHOTOS'	=>	$pcert['OTHER_PHOTOS'],
				'RES'			=>	$pcert['RES']
			);
			
			//证件数据入tmp库
			if($pcert['flag'] == 1){
				//判断当前数据是否正在发生变更
				$where = 'PARTNER_MAP_ID = "'.$pcert['PARTNER_MAP_ID'].'"';
				$pcert_tmp = D($this->MPcert)->findPcert_tmp($where);
				if ($pcert_tmp['PARTNER_STATUS'] == 4) {
					$this->wrong('当前状态不允许进行些此操作!');
				}
				$res = D($this->MPcert)->updatePcert_tmp("PARTNER_MAP_ID='".$pcert['PARTNER_MAP_ID']."'", $pcert_data);
			}else{
				$res = D($this->MPcert)->addPcert_tmp($pcert_data);
			}
			if($res['state']!=0){
				$this->wrong('合作伙伴证件信息变更失败！');
			}
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($pcert['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '证照资质变更',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('操作失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $id);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		$partner_info = D($this->MPartner)->findPartner("a.PARTNER_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$pcert_info = D($this->MPcert)->findPcert_tmp("PARTNER_MAP_ID='".$id."'");
			if ($pcert_info['PARTNER_STATUS'] == 4) {
				$this->wrong('当前状态不允许修改操作');
			}
		}else{
			$pcert_info = D($this->MPcert)->findPcert("PARTNER_MAP_ID='".$id."'");
		}
		
		if(empty($partner_info) || empty($partner_info)){
			$this->wrong("参数数据出错！");
		}
		$pcert_info['OTHER'] = explode(',', $pcert_info['OTHER_PHOTOS']);
		$pcert_info['flag'] = $flag;
		$this->assign ('partner_info', 		$partner_info);			//合作伙伴基础信息
		$this->assign ('pcert_info', 		$pcert_info);			//合作伙伴证照信息
		$this->display();
	}
	/*
	* 合作伙伴证照变更管理 审核
	**/
	public function pcert_check() {
		$post = I('post');
		if($post['submit'] == "pcert_check") {
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【合作伙伴证照变更】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['PARTNER_MAP_ID']) || $check_data['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			//更新tmp表审核状态
			$where = 'PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID'];
			$pcert_tmp = D($this->MPcert)->updatePcert_tmp($where,array('PARTNER_STATUS' => $post['CHECK_POINT']));
			if ($pcert_tmp['state']==1) {
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

		$partner_info = D($this->MPartner)->findPartner("a.PARTNER_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if($flag == 1){
			$pcert_info = D($this->MPcert)->findPcert_tmp("PARTNER_MAP_ID='".$id."'");
		}else{
			$pcert_info = D($this->MPcert)->findPcert("PARTNER_MAP_ID='".$id."'");
		}
		if(empty($partner_info) || empty($pcert_info)){
			$this->wrong("参数数据出错！");
		}
		if ($pcert_info['PARTNER_STATUS'] != 6) {
			$this->wrong('当前状态不允许此操作');
		}
		$pcert_info['OTHER'] = explode(',', $pcert_info['OTHER_PHOTOS']);
		$this->assign ('partner_info', $partner_info);
		$this->assign ('pcert_info', $pcert_info);
		$this->display();
	}
	/*
	* 合作伙伴证照变更管理 复审
	**/
	public function pcert_recheck() {
		$post = I('post');
		if ($post['submit'] == "pcert_recheck") {
			$home = session('HOME');
			//验证
			if($post['CHECK_POINT']==''){
				$this->wrong("缺少审核状态, 审核信息！");
			}
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【合作伙伴证照变更】复核操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			//验证
			if(empty($post['PARTNER_MAP_ID']) || $post['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			//判断是否通过并更改数据
			$where = 'PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$pcert = D($this->MPcert)->updatePcert_tmp($where,array('PARTNER_STATUS' => $post['CHECK_POINT']));
				if($pcert['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$pcert_change = D($this->MPcert)->findPcert_tmp($where);
				$pcert_change['PARTNER_STATUS'] = 0;
				//更新证照主表数据
				$pcert = D($this->MPcert)->updatePcert($where,$pcert_change);
				if($pcert['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MPcert)->delPcert_tmp('PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID']);
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
		$partner_info = D($this->MPartner)->findPartner('a.PARTNER_MAP_ID = "'.$id.'"','a.*, b.BRANCH_NAME');
		$pcert_info = D($this->MPcert)->findPcert_tmp("PARTNER_MAP_ID='".$id."'");
		if ($pcert_info['PARTNER_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核操作');
		}
		//获取初审记录
		$check_no = '2'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$pcert_info['OTHER'] = explode(',', $pcert_info['OTHER_PHOTOS']);
		$this->assign('partner_info',$partner_info);
		$this->assign('pcert_info',$pcert_info);
		$this->display('pcert_check');
	}
	
	
	
	
	/*
	* 合作伙伴权限变更管理 列表
	**/
	public function pauth() {
		$post = I('post');
		if($post['submit'] == "pauth"){
			$where = "1=1";
			$sellv = filter_data('soplv');	//列表查询
			//所属归属
			if($sellv['bid']) {
				$where .= " and ag.BRANCH_MAP_ID = '".$sellv['bid']."'";
			}
			//合作伙伴id
			if ($sellv['pid']) {
				$pids = get_plv_childs($sellv['pid'],1);
				$where .= " and ag.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $sellv['pid'];
			}
			//合作伙伴级别
			if($post['PLEVEL_NAME']) {
				$where .= " and ag.PARTNER_LEVEL = '".$post['PLEVEL_NAME']."'";
			}
			//合作伙伴状态
			if($post['PARTNER_STATUS'] != '') {
				if($post['PARTNER_STATUS'] == 0){
					$where .= " and aa.PARTNER_STATUS = '".$post['PARTNER_STATUS']."'";
				}else{
					$where .= " and tmp.PARTNER_STATUS = '".$post['PARTNER_STATUS']."'";
				}
			}
			//合作伙伴类别
			if($post['PARTNER_G_FLAG']!='') {
				$where .= " and ag.PARTNER_G_FLAG = '".$post['PARTNER_G_FLAG']."'";
			}
			//合作伙伴名称
			if($post['PARTNER_NAME']!='') {
				$where .= " and ag.PARTNER_NAME like '%".$post['PARTNER_NAME']."%'";
			}
			//合作伙伴角色
			if($post['PLEVEL_MAP_ID']!='') {
				$where .= " and ag.PLEVEL_MAP_ID = '".$post['PLEVEL_MAP_ID']."'";
			}
			$where .= " and ag.PARTNER_STATUS != '2'";
			//分页
			$count = D($this->MPauth)->countPauth($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MPauth)->getPauthlist($where, 'aa.PARTNER_MAP_ID,ag.PARTNER_LEVEL,ag.PARTNER_NAME,aa.PARTNER_STATUS,tmp.PARTNER_MAP_ID as TMP_ID,tmp.PARTNER_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,aa.PARTNER_MAP_ID desc');
			foreach ($list as $key => $value) {
				$check_no = '2'.setStrzero($value['PARTNER_MAP_ID'],15);
				$list[$key]['CHECK_DESC'] = get_check_note($check_no)['CHECK_DESC'];
			}
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set ('_currentUrl_', 	__SELF__);
		//合作伙伴审核状态数组
		$this->assign ('plevel_arr',		C('PLEVEL_NAME'));		//级别名称
		$this->assign ('partner_status_arr',C('CHECK_POINT.check'));//合作伙伴审核状态数组
		$this->assign ('plevel_arr',		C('PLEVEL_NAME'));		//合作伙伴级别名称
		$this->assign ('partner_g_f', 		C('PARTNER_G_FLAG'));	//合作伙伴类型
		$this->display();
	}
	/*
	* 合作伙伴权限 修改
	**/
	public function pauth_edit() {
		$pauth = I('pauth');
		if($pauth['submit'] == "pauth_edit") {
			$home = session('HOME');
			//组装权限数据
			$pauth_data = array(
				'PARTNER_MAP_ID'=>	$pauth['PARTNER_MAP_ID'],
				'PARTNER_STATUS'=>	6,
				'HOST_MAP_ID'	=>	$pauth['HOST_MAP_ID'] ? $pauth['HOST_MAP_ID'] : 0,
				'AUTH_TRANS_MAP'=>	$pauth['AUTH_TRANS_MAP'],
				'AUTH_PAYS_MAP'	=>	''
			);
			//证件数据入tmp库
			if($pauth['flag'] == 1){
				$res = D($this->MPauth)->updatePauth_tmp("PARTNER_MAP_ID='".$pauth['PARTNER_MAP_ID']."'", $pauth_data);
			}else{
				$res = D($this->MPauth)->addPauth_tmp($pauth_data);
			}
			if($res['state']!=0){
				$this->wrong('合作伙伴权限信息变更失败！');
			}

			/*if(empty($pauth['PARTNER_MAP_ID']) || empty($check_data['CHECK_FLAG']) || $check_data['CHECK_POINT']='' || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}*/

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($pauth['PARTNER_MAP_ID'],15),
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
		$partner_info = D($this->MPartner)->findPartner("a.PARTNER_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$pauth_info = D($this->MPauth)->findPauth_tmp("PARTNER_MAP_ID='".$id."'");
			if ($pauth_info['PARTNER_STATUS'] == 4) {
				$this->wrong('当前状态不允许修改操作');
			}
		}else{
			$pauth_info = D($this->MPauth)->findPauth("PARTNER_MAP_ID='".$id."'");
		}
		
		if(empty($partner_info) || empty($pauth_info)){
			$this->wrong("参数数据出错！");
		}
		$pauth_info['flag'] = $flag;
		//路由通道
		$hostsel = D($this->MHost)->getHostlist('HOST_STATUS = 0', $field='HOST_MAP_ID,HOST_NAME');
		$this->assign ('hostsel', $hostsel);
		$this->assign ('partner_info', $partner_info);
		$this->assign ('pauth_info', $pauth_info);
		$this->assign ('auth_trans_checked', str_split($pauth_info['AUTH_TRANS_MAP']));	//交易开通数据
		$this->assign ('auth_pays_checked',  str_split($pauth_info['AUTH_PAYS_MAP']));	//支付开通数据
		$this->display();
	}
	/*
	* 合作伙伴权限变更管理 审核
	**/
	public function pauth_check() {
		$post = I('post');
		if($post['submit'] == "pauth_check") {
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【合作伙伴权限变更】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['PARTNER_MAP_ID']) || $check_data['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			//更新tmp表审核状态
			$where = 'PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID'];
			$pcert_tmp = D($this->MPauth)->updatePauth_tmp($where,array('PARTNER_STATUS' => $post['CHECK_POINT']));
			if ($pauth_tmp['state']==1) {
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

		$partner_info = D($this->MPartner)->findPartner("a.PARTNER_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if($flag == 1){
			//判断当前数据是否符合变更条件
			$pauth_info = D($this->MPauth)->findPauth_tmp("PARTNER_MAP_ID='".$id."'");
			
		}else{
			$pauth_info = D($this->MPauth)->findPauth("PARTNER_MAP_ID='".$id."'");
		}
		if ($pauth_info['PARTNER_STATUS'] != 6) {
			$this->wrong('当前状态不允许初审操作');
		}
		if(empty($partner_info) || empty($partner_info)){
			$this->wrong("参数数据出错！");
		}
		//路由通道
		$hostsel = D($this->MHost)->getHostlist('HOST_STATUS = 0', $field='HOST_MAP_ID,HOST_NAME');
		$this->assign ('hostsel', $hostsel);
		$this->assign ('partner_info', $partner_info);
		$this->assign ('pauth_info', $pauth_info);
		$this->assign ('auth_trans_checked', str_split($pauth_info['AUTH_TRANS_MAP']));	//交易开通数据
		$this->assign ('auth_pays_checked',  str_split($pauth_info['AUTH_PAYS_MAP']));	//支付开通数据
		$this->display();
	}
	/*
	* 合作伙伴权限变更管理 复核
	**/
	public function pauth_recheck() {
		$post = I('post');
		if ($post['submit'] == "pauth_recheck") {
			$home = session('HOME');
			//组装审核数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【合作伙伴权限变更】复核操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['PARTNER_MAP_ID']) || $check_data['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$where = 'PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$pauth = D($this->MPauth)->updatePauth_tmp($where,array('PARTNER_STATUS' => $post['CHECK_POINT']));
				if($pauth['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$pauth_change = D($this->MPauth)->findPauth_tmp($where);
				$pauth_change['PARTNER_STATUS'] = 0;
				//更新主表数据
				$pauth = D($this->MPauth)->updatePauth($where,$pauth_change);
				if($pauth['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MPauth)->delPauth_tmp('PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID']);
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
		$partner_info = D($this->MPartner)->findPartner('a.PARTNER_MAP_ID = "'.$id.'"','a.*, b.BRANCH_NAME');
		$pauth_info = D($this->MPauth)->findPauth_tmp("PARTNER_MAP_ID='".$id."'");
		if ($pauth_info['PARTNER_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核操作');
		}
		//获取初审记录
		$check_no = '2'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
			//$this->assign('pauth_info',$pauth_info);
		}
		//路由通道
		$hostsel = D($this->MHost)->getHostlist('HOST_STATUS = 0', $field='HOST_MAP_ID,HOST_NAME');
		$this->assign ('hostsel', $hostsel);
		$this->assign ('partner_info', $partner_info);
		$this->assign ('pauth_info', $pauth_info);
		$this->assign ('auth_trans_checked', str_split($pauth_info['AUTH_TRANS_MAP']));	//交易开通数据
		$this->assign ('auth_pays_checked',  str_split($pauth_info['AUTH_PAYS_MAP']));	//支付开通数据
		$this->display('pauth_check');
	}
	
	
	
	
	/*
	* 合作伙伴结算方式变更管理 列表
	**/
	public function pcls() {
		$post = I('post');		
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'PARTNER_STATUS'	=>	I('PARTNER_STATUS'),
				'bid'				=>	I('bid'),
				'pid'				=>	I('pid'),
				'PLEVEL_NAME'		=>	I('PLEVEL_NAME'),
				'PARTNER_G_FLAG'	=>	I('PARTNER_G_FLAG'),
				'PARTNER_NAME'		=>	I('PARTNER_NAME'),
				'PLEVEL_MAP_ID'		=>	I('PLEVEL_MAP_ID'),
			);
			$ajax_soplv = array(
				'bid'				=>	I('bid'),
				'pid'				=>	I('pid'),			
			);
		}
		//===结束=======
		if($post['submit'] == "pcls"){
			$where = "1=1";
			//===优化统计===
			$sellv = $ajax == 'loading' ? $ajax_soplv : filter_data('soplv');	//列表查询
			//===结束=======
			//所属归属
			if($sellv['bid']) {
				$where .= " and ag.BRANCH_MAP_ID = '".$sellv['bid']."'";
			}
			//合作伙伴id
			if ($sellv['pid']) {
				$pids = get_plv_childs($sellv['pid'],1);
				$where .= " and ac.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $sellv['pid'];
			}
			//合作伙伴级别
			if($post['PLEVEL_NAME']) {
				$where .= " and ag.PARTNER_LEVEL = '".$post['PLEVEL_NAME']."'";
			}
			//合作伙伴状态
			if($post['PARTNER_STATUS'] != '') {
				if($post['PARTNER_STATUS'] == 0){
					$where .= " and ac.PARTNER_STATUS = '".$post['PARTNER_STATUS']."'";
				}else{
					$where .= " and tmp.PARTNER_STATUS = '".$post['PARTNER_STATUS']."'";
				}
			}
			//合作伙伴类别
			if($post['PARTNER_G_FLAG']!='') {
				$where .= " and ag.PARTNER_G_FLAG = '".$post['PARTNER_G_FLAG']."'";
			}
			//合作伙伴名称
			if($post['PARTNER_NAME']!='') {
				$where .= " and ag.PARTNER_NAME like '%".$post['PARTNER_NAME']."%'";
			}
			//合作伙伴角色
			if($post['PLEVEL_MAP_ID']!='') {
				$where .= " and ag.PLEVEL_MAP_ID = '".$post['PLEVEL_MAP_ID']."'";
			}	
			$where .= " and ag.PARTNER_STATUS != '2'";
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->MPcls)->countPcls($where);
				$resdata = array(
					'count'	=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======			
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->MPcls)->getPclslist($where, 'ac.PARTNER_MAP_ID,ag.PARTNER_LEVEL,ag.PARTNER_NAME,ac.PARTNER_STATUS,tmp.PARTNER_MAP_ID as TMP_ID,tmp.PARTNER_STATUS as TMP_STATUS', $fiRow.','.$liRow);
			foreach ($list as $key => $value) {
				$check_no = '2'.setStrzero($value['PARTNER_MAP_ID'],15);
				$list[$key]['CHECK_DESC'] = get_check_note($check_no)['CHECK_DESC'];
			}
			
			//分页参数
			$this->assign ( 'totalCount', 	C('MAX_PAGE') );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
						
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
	
		\Cookie::set ('_currentUrl_', 	__SELF__);
		//合作伙伴审核状态数组
		$this->assign ('plevel_arr',		C('PLEVEL_NAME'));		//级别名称
		$this->assign ('partner_status_arr',C('CHECK_POINT.check'));//合作伙伴审核状态数组
		$this->assign ('plevel_arr',		C('PLEVEL_NAME'));		//合作伙伴级别名称
		$this->assign ('partner_g_f', 		C('PARTNER_G_FLAG'));	//合作伙伴类型
		$this->display();
	}
	/*
	* 合作伙伴结算方式变更管理 修改
	**/
	public function pcls_edit() {
		$pcls = I('pcls');
		if($pcls['submit'] == "pcls_edit") {
			$home = session('HOME');
			//组装结算方式数据
			$pcls_data = array(
				'PARTNER_MAP_ID'	=>	$pcls['PARTNER_MAP_ID'],
				'PARTNER_STATUS'	=>	6,
				'SETTLE_T'			=>	$pcls['SETTLE_T'],
				'SETTLE_T_UNIT'		=>	$pcls['SETTLE_T_UNIT'],
				'SETTLE_TYPE'		=>	2,		//0：结算到合作伙伴 1：结算到商户 默认2保留
				'SETTLE_FLAG'		=>	$pcls['SETTLE_FLAG'] ? $pcls['SETTLE_FLAG'] : 1,
				'SETTLE_TOP_AMT'	=>	setMoney($pcls['SETTLE_TOP_AMT']),
				'SETTLE_FREE_AMT'	=>	setMoney($pcls['SETTLE_FREE_AMT']),
				'SETTLE_OFF_AMT'	=>	setMoney($pcls['SETTLE_OFF_AMT']),
				'SETTLE_OFF_FEE'	=>	$pcls['SETTLE_OFF_FEE'] ? setMoney($pcls['SETTLE_OFF_FEE']) : 0,
				'SETTLE_FEE'		=>	setMoney($pcls['SETTLE_FEE'])
			);
			//判断当前数据是否正在发生变更
			$where = 'PARTNER_MAP_ID = "'.$pcls['PARTNER_MAP_ID'].'"';
			$pcls_tmp = D($this->MPcls)->findPcls_tmp($where);
			if ($pcls_tmp['PARTNER_STATUS'] == 4) {
				$this->wrong('当前数据已进入待复审状态,如需变更请等待复审结束后,再次提交!');
			}
			//证件数据入tmp库
			if($pcls['flag'] == 1){
				$res = D($this->MPcls)->updatePcls_tmp("PARTNER_MAP_ID='".$pcls['PARTNER_MAP_ID']."'", $pcls_data);
			}else{
				$res = D($this->MPcls)->addPcls_tmp($pcls_data);
			}
			if($res['state']!=0){
				$this->wrong('合作伙伴银行账户信息变更失败！');
			}

			/*if(empty($pcls['PARTNER_MAP_ID']) || empty($check_data['CHECK_FLAG']) || $check_data['CHECK_POINT']='' || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}*/

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($PCLS['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '银行账户变更',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('操作失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $id);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		$partner_info = D($this->MPartner)->findPartner("a.PARTNER_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$pcls_info = D($this->MPcls)->findPcls_tmp("PARTNER_MAP_ID='".$id."'");
		}else{
			$pcls_info = D($this->MPcls)->findPcls("PARTNER_MAP_ID='".$id."'");
		}
		
		if(empty($partner_info) || empty($pcls_info)){
			$this->wrong("参数数据出错！");
		}
		if ($pcls_info['PARTNER_STATUS'] == 4) {
			$this->wrong('当前状态不允许修改操作');
		}
		//发卡行列表
		$issuelist = M('bank')->field('ISSUE_CODE,BANK_NAME')->select();
		$pcls_info['flag'] = $flag;
		$this->assign ('issuelist', $issuelist);	//发卡行列表
		$this->assign ('bank_flag_redio', C('PARTNER_BANK_FLAG'));	//结算标志
		$this->assign ('partner_info', $partner_info);
		$this->assign ('pcls_info', $pcls_info);
		$this->display();
	}
	/*
	* 合作伙伴结算方式变更管理 审核
	**/
	public function pcls_check() {
		$post = I('post');
		if($post['submit'] == "pcls_check") {
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【合作伙伴结算方式变更】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['PARTNER_MAP_ID']) || $check_data['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			//更新tmp表审核状态
			$where = 'PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID'];
			$pcls_tmp = D($this->MPcls)->updatePcls_tmp($where,array('PARTNER_STATUS' => $post['CHECK_POINT']));
			if ($pcls_tmp['state']==1) {
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

		$partner_info = D($this->MPartner)->findPartner("a.PARTNER_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if($flag == 1){
			$pcls_info = D($this->MPcls)->findPcls_tmp("PARTNER_MAP_ID='".$id."'");
			
		}else{
			$pcls_info = D($this->MPcls)->findPcls("PARTNER_MAP_ID='".$id."'");
		}
		if ($pcls_info['PARTNER_STATUS'] != 6) {
			$this->wrong('当前状态不允许初审操作');
		}
		if(empty($partner_info) || empty($pcls_info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('partner_info', $partner_info);
		$this->assign ('pcls_info', $pcls_info);
		$this->display();
	}	
	/*
	* 合作伙伴结算方式变更管理 复核
	**/
	public function pcls_recheck() {
		$post = I('post');
		if ($post['submit'] == "pcls_recheck") {
			$home = session('HOME');
			//组装审核数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【合作伙伴结算方式变更】复核操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['PARTNER_MAP_ID']) || $check_data['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$where = 'PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$pcls = D($this->MPcls)->updatePcls_tmp($where,array('PARTNER_STATUS' => $post['CHECK_POINT']));
				if($pcls['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$pcls_change = D($this->MPcls)->findPcls_tmp($where);
				$pcls_change['PARTNER_STATUS'] = 0;
				//更新主表数据
				$pcls = D($this->MPcls)->updatePcls($where,$pcls_change);
				if($pcls['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MPcls)->delPcls_tmp('PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID']);
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
		$partner_info = D($this->MPartner)->findPartner('a.PARTNER_MAP_ID = "'.$id.'"','a.*, b.BRANCH_NAME');
		$pcls_info = D($this->MPcls)->findPcls_tmp("PARTNER_MAP_ID='".$id."'");
		if ($pcls_info['PARTNER_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '2'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
			//$this->assign('pcls_info',$pcls_info);
		}
		$this->assign('partner_info',$partner_info);
		$this->assign('pcls_info',$pcls_info);
		//$this->partner_show('partner_check');
		$this->display('pcls_check');
	}
	
	
	
	/*
	* 合作伙伴银行账户变更管理 列表
	**/
	public function pbact() {
		$post = I('post');
		if($post['submit'] == "pbact"){
			$where = "1=1";
			$sellv = filter_data('soplv');	//列表查询
			//所属归属
			if($sellv['bid']) {
				$where .= " and ag.BRANCH_MAP_ID = '".$sellv['bid']."'";
			}
			//合作伙伴id
			if ($sellv['pid']) {
				$pids = get_plv_childs($sellv['pid'],1);
				$where .= " and ab.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $sellv['pid'];
			}
			//合作伙伴级别
			if($post['PLEVEL_NAME']) {
				$where .= " and ag.PARTNER_LEVEL = '".$post['PLEVEL_NAME']."'";
			}
			//合作伙伴状态
			if($post['PARTNER_STATUS'] != '') {
				if($post['PARTNER_STATUS'] == 0){
					$where .= " and ab.PARTNER_STATUS = '".$post['PARTNER_STATUS']."'";
				}else{
					$where .= " and tmp.PARTNER_STATUS = '".$post['PARTNER_STATUS']."'";
				}
			}
			//合作伙伴类别
			if($post['PARTNER_G_FLAG']!='') {
				$where .= " and ag.PARTNER_G_FLAG = '".$post['PARTNER_G_FLAG']."'";
			}
			//合作伙伴名称
			if($post['PARTNER_NAME']!='') {
				$where .= " and ag.PARTNER_NAME like '%".$post['PARTNER_NAME']."%'";
			}
			//合作伙伴角色
			if($post['PLEVEL_MAP_ID']!='') {
				$where .= " and ag.PLEVEL_MAP_ID = '".$post['PLEVEL_MAP_ID']."'";
			}
			$where .= " and ag.PARTNER_STATUS != '2'";
			//分页
			$count = D($this->MPbact)->countPbact($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MPbact)->getPbactlist($where, 'ab.PARTNER_MAP_ID,ag.PARTNER_LEVEL,ag.PARTNER_NAME,ab.PARTNER_STATUS,tmp.PARTNER_MAP_ID as TMP_ID,tmp.PARTNER_STATUS as TMP_STATUS ', $p->firstRow.','.$p->listRows);
			foreach ($list as $key => $value) {
				$check_no = '2'.setStrzero($value['PARTNER_MAP_ID'],15);
				$list[$key]['CHECK_DESC'] = get_check_note($check_no)['CHECK_DESC'];
			}
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set (	'_currentUrl_', 	__SELF__);
		//合作伙伴审核状态数组
		$this->assign ('plevel_arr',		C('PLEVEL_NAME'));		//级别名称
		$this->assign ('partner_status_arr',C('CHECK_POINT.check'));//合作伙伴审核状态数组
		$this->assign ('plevel_arr',		C('PLEVEL_NAME'));		//合作伙伴级别名称
		$this->assign ('partner_g_f', 		C('PARTNER_G_FLAG'));	//合作伙伴类型
		$this->display();
	}
	/*
	* 合作伙伴银行账户变更管理 修改
	**/
	public function pbact_edit() {
		$pbact = I('pbact');
		if($pbact['submit'] == "pbact_edit") {
			$home = session('HOME');
			//组装银行账户数据
			$pbact['BANK_NAME1']    = $_REQUEST['org1_BANK_NAME'];
			$pbact['BANKACCT_BID1'] = $_REQUEST['org1_BANKACCT_BID'];
			$pbact['BANK_NAME2']    = $_REQUEST['org2_BANK_NAME'];
			$pbact['BANKACCT_BID2'] = $_REQUEST['org2_BANKACCT_BID'];
			$pbact_data = array(
				'PARTNER_MAP_ID'	=>	$pbact['PARTNER_MAP_ID'],
				'PARTNER_STATUS'	=>	6,
				'PARTNER_BANK_FLAG'	=>	$pbact['PARTNER_BANK_FLAG'],
				'BANKACCT_NAME1'	=>	$pbact['BANKACCT_NAME1'],
				'BANKACCT_NO1'		=>	$pbact['BANKACCT_NO1'],
				'BANKACCT_BID1'		=>	$pbact['BANKACCT_BID1'],
				'BANK_NAME1'		=>	$pbact['BANK_NAME1'],
				'BANKACCT_NAME2'	=>	$pbact['BANKACCT_NAME2'],
				'BANKACCT_NO2'		=>	$pbact['BANKACCT_NO2'],
				'BANKACCT_BID2'		=>	$pbact['BANKACCT_BID2'],
				'BANK_NAME2'		=>	$pbact['BANK_NAME2']
			);
			//判断当前数据是否正在发生变更
			$where = 'PARTNER_MAP_ID = "'.$pbact['PARTNER_MAP_ID'].'"';
			/*$pbact_tmp = D($this->MPbact)->findPbact_tmp($where);
			if ($pbact_tmp['PARTNER_STATUS'] == 4) {
				$this->wrong('当前状态不能进行此操作!');
			}*/
			//证件数据入tmp库
			if($pbact['flag'] == 1){
				$res = D($this->MPbact)->updatePbact_tmp("PARTNER_MAP_ID='".$pbact['PARTNER_MAP_ID']."'", $pbact_data);
			}else{
				$res = D($this->MPbact)->addPbact_tmp($pbact_data);
			}
			if($res['state']!=0){
				$this->wrong('合作伙伴银行账户信息变更失败！');
			}

			/*if(empty($pbact['PARTNER_MAP_ID']) || empty($check_data['CHECK_FLAG']) || $check_data['CHECK_POINT']=='' || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}*/

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($pbact['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '银行账户变更',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('操作失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $id);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		$partner_info = D($this->MPartner)->findPartner("a.PARTNER_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$pbact_info = D($this->MPbact)->findPbact_tmp("PARTNER_MAP_ID='".$id."'");
		}else{
			$pbact_info = D($this->MPbact)->findPbact("PARTNER_MAP_ID='".$id."'");
		}
		if ($pbact_info['PARTNER_STATUS'] == 4) {
			$this->wrong('当前状态不允许此操作');
		}
		if(empty($partner_info) || empty($pbact_info)){
			$this->wrong("参数数据出错！");
		}

		$pbact_info['flag'] = $flag;
		$this->assign ('partner_info',	$partner_info);
		$this->assign ('pbact_info', 	$pbact_info);
		$this->assign ('bank_flag_redio', C('PARTNER_BANK_FLAG'));	//结算标志
		$this->display();
	}
	/*
	* 合作伙伴银行账户变更管理 审核
	**/
	public function pbact_check() {
		$post = I('post');
		if($post['submit'] == "pbact_check") {
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【合作伙伴银行账户变更】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['PARTNER_MAP_ID']) || $check_data['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			//更新tmp表审核状态
			$where = 'PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID'];
			$pbact_tmp = D($this->MPbact)->updatePbact_tmp($where,array('PARTNER_STATUS' => $post['CHECK_POINT']));
			if ($pbact_tmp['state']==1) {
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

		$partner_info = D($this->MPartner)->findPartner("a.PARTNER_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if($flag == 1){
			$pbact_info = D($this->MPbact)->findPbact_tmp("PARTNER_MAP_ID='".$id."'");
		}else{
			$pbact_info = D($this->MPbact)->findPbact("PARTNER_MAP_ID='".$id."'");
		}
		if ($pbact_info['PARTNER_STATUS'] != 6) {
			$this->wrong('当前状态不允许此操作');
		}
		if(empty($partner_info) || empty($partner_info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('bank_flag_redio', C('PARTNER_BANK_FLAG'));	//结算标志
		$this->assign ('partner_info', $partner_info);
		$this->assign ('pbact_info', $pbact_info);
		$this->display();
	}	
	/*
	* 合作伙伴银行账户变更管理 复核
	**/
	public function pbact_recheck() {
		$post = I('post');
		if ($post['submit'] == "pbact_recheck") {
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【合作伙伴银行账户变更】复核操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['PARTNER_MAP_ID']) || $post['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$where = 'PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$pbact = D($this->MPbact)->updatePbact_tmp($where,array('PARTNER_STATUS' => $post['CHECK_POINT']));
				if($pbact['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$pbact_change = D($this->MPbact)->findPbact_tmp($where);
				$pbact_change['PARTNER_STATUS'] = 0;
				//更新证照主表数据
				$pbact = D($this->MPbact)->updatePbact($where,$pbact_change);
				if($pbact['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MPbact)->delPbact_tmp('PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID']);
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
		$partner_info = D($this->MPartner)->findPartner('a.PARTNER_MAP_ID = "'.$id.'"','a.*, b.BRANCH_NAME');
		$pbact_info = D($this->MPbact)->findPbact_tmp("PARTNER_MAP_ID='".$id."'");
		if ($pbact_info['PARTNER_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '2'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$this->assign('bank_flag_redio', C('PARTNER_BANK_FLAG'));	//结算标志
		$this->assign('partner_info',$partner_info);
		$this->assign('pbact_info',$pbact_info);
		$this->display('pbact_check');
	}

	/*
	* 合作伙伴其他配置变更管理 列表
	**/
	public function psett() {
		$post = I('post');
		if($post['submit'] == "psett"){
			$where = "ag.PARTNER_STATUS = 0";
			$sellv = filter_data('soplv');	//列表查询
			//所属归属
			if($sellv['bid']) {
				$where .= " and ag.BRANCH_MAP_ID = '".$sellv['bid']."'";
			}
			//合作伙伴id
			if ($sellv['pid']) {
				$pids = get_plv_childs($sellv['pid'],1);
				$where .= " and ag.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $sellv['pid'];
			}
			//合作伙伴级别
			if($post['PLEVEL_NAME']) {
				$where .= " and ag.PARTNER_LEVEL = '".$post['PLEVEL_NAME']."'";
			}
			//合作伙伴状态
			if($post['PARTNER_STATUS'] != '') {
				if($post['PARTNER_STATUS'] == 0){
					$where .= " and ac.PARTNER_STATUS = '".$post['PARTNER_STATUS']."'";
				}else{
					$where .= " and tmp.PARTNER_STATUS = '".$post['PARTNER_STATUS']."'";
				}
			}
			//合作伙伴类别
			if($post['PARTNER_G_FLAG']!='') {
				$where .= " and ag.PARTNER_G_FLAG = '".$post['PARTNER_G_FLAG']."'";
			}
			//合作伙伴名称
			if($post['PARTNER_NAME']!='') {
				$where .= " and ag.PARTNER_NAME like '%".$post['PARTNER_NAME']."%'";
			}
			//合作伙伴角色
			if($post['PLEVEL_MAP_ID']!='') {
				$where .= " and ag.PLEVEL_MAP_ID = '".$post['PLEVEL_MAP_ID']."'";
			}
			$where .= " and ag.PARTNER_STATUS != '2'";
			//分页
			$count = D($this->MPcfg)->countPcfg($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MPcfg)->getPcfglist($where, 'ac.PARTNER_MAP_ID,ag.PARTNER_LEVEL,ag.PARTNER_NAME,ag.PARTNER_STATUS,tmp.PARTNER_MAP_ID as TMP_ID,tmp.PARTNER_STATUS as TMP_STATUS ', $p->firstRow.','.$p->listRows);
			foreach ($list as $key => $value) {
				$check_no = '2'.setStrzero($value['PARTNER_MAP_ID'],15);
				$list[$key]['CHECK_DESC'] = get_check_note($check_no)['CHECK_DESC'];
			}
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set ('_currentUrl_', 	__SELF__);
		//合作伙伴审核状态数组
		$this->assign ('plevel_arr',		C('PLEVEL_NAME'));		//级别名称
		$this->assign ('partner_status_arr',C('CHECK_POINT.check'));//合作伙伴审核状态数组
		$this->assign ('plevel_arr',		C('PLEVEL_NAME'));		//合作伙伴级别名称
		$this->assign ('partner_g_f', 		C('PARTNER_G_FLAG'));	//合作伙伴类型
		$this->display();
	}
	/*
	* 合作伙伴其他配置变更管理 修改
	**/
	public function psett_edit() {
		$pcfg = I('pcfg');
		if($pcfg['submit'] == "psett_edit") {
			$home = session('HOME');
			//组装其他配置数据
			if (!empty($pcfg)) {
				$pcfg_data = array(
					'PARTNER_MAP_ID'	=>	$pcfg['PARTNER_MAP_ID'],
					'PARTNER_STATUS'	=>	6,
					'CARD_OPENFEE'		=>	setMoney($pcfg['CARD_OPENFEE']),
					'DIV_PER'			=>	$pcfg['DIV_PER'] ? $pcfg['DIV_PER'] : 0,
					'DIV_FLAG'			=>	$pcfg['DIV_FLAG']
				);
				//其他配置数据入库
				$pcfg_res = D($this->MPcfg)->updatePcfg($where,$pcfg_data);
				if($pcfg_res['state']!=0){
					$m->rollback();//不成功，则回滚
					$this->wrong('合作伙伴结算方式信息变更失败！');
				}
			}

			//判断当前数据是否正在发生变更
			$where = 'PARTNER_MAP_ID = "'.$pcfg['PARTNER_MAP_ID'].'"';
			/*$pcfg_tmp = D($this->MPcfg)->findPcfg_tmp($where);
			if ($pcfg_tmp['PARTNER_STATUS'] == 4) {
				$this->wrong('当前状态不能进行此操作!');
			}*/
			//证件数据入tmp库
			if($pcfg['flag'] == 1){
				$res = D($this->MPcfg)->updatePcfg_tmp("PARTNER_MAP_ID='".$pcfg['PARTNER_MAP_ID']."'", $pcfg_data);
			}else{
				$res = D($this->MPcfg)->addPcfg_tmp($pcfg_data);
			}
			if($res['state']!=0){
				$this->wrong('合作伙伴其他配置信息变更失败！');
			}

			/*if(empty($pcfg['PARTNER_MAP_ID']) || empty($check_data['CHECK_FLAG']) || $check_data['CHECK_POINT']=='' || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}*/

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($pcfg['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '其他配置变更',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('操作失败');
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $id);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		$partner_info = D($this->MPartner)->findPartner("a.PARTNER_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$pcfg_info = D($this->MPcfg)->findPcfg_tmp("PARTNER_MAP_ID='".$id."'");
		}else{
			$pcfg_info = D($this->MPcfg)->findPcfg("PARTNER_MAP_ID='".$id."'");
		}
		if ($pcfg_info['PARTNER_STATUS'] == 4) {
			$this->wrong('当前状态不允许此操作');
		}
		if(empty($partner_info) || empty($pcfg_info)){
			$this->wrong("参数数据出错！");
		}

		$pcfg_info['flag'] = $flag;
		$this->assign ('partner_info',	$partner_info);
		$this->assign ('pcfg_info', 	$pcfg_info);
		$this->display();
	}
	/*
	* 合作伙伴其他配置变更管理 审核
	**/
	public function psett_check() {
		$post = I('post');
		if($post['submit'] == "psett_check") {
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【合作伙伴其他配置变更】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['PARTNER_MAP_ID']) || $check_data['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			//更新tmp表审核状态
			$where = 'PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID'];
			$pcfg_tmp = D($this->MPcfg)->updatePcfg_tmp($where,array('PARTNER_STATUS' => $post['CHECK_POINT']));
			if ($pcfg_tmp['state']==1) {
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

		$partner_info = D($this->MPartner)->findPartner("a.PARTNER_MAP_ID='".$id."'",'a.*, b.BRANCH_NAME');
		if($flag == 1){
			$pcfg_info = D($this->MPcfg)->findPcfg_tmp("PARTNER_MAP_ID='".$id."'");
		}else{
			$pcfg_info = D($this->MPcfg)->findPcfg("PARTNER_MAP_ID='".$id."'");
		}
		if ($pcfg_info['PARTNER_STATUS'] != 6) {
			$this->wrong('当前状态不允许此操作');
		}
		if(empty($partner_info) || empty($partner_info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign ('partner_info', $partner_info);
		$this->assign ('pcfg_info', $pcfg_info);
		$this->display();
	}	
	/*
	* 合作伙伴其他配置变更管理 复核
	**/
	public function psett_recheck() {
		$post = I('post');
		if ($post['submit'] == "psett_recheck") {
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '2'.setStrzero($post['PARTNER_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【合作伙伴其他配置变更】复核操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['PARTNER_MAP_ID']) || $post['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$where = 'PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$pcfg = D($this->MPcfg)->updatePcfg_tmp($where,array('PARTNER_STATUS' => $post['CHECK_POINT']));
				if($pcfg['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$pcfg_change = D($this->MPcfg)->findPcfg_tmp($where);
				$pcfg_change['PARTNER_STATUS'] = 0;
				//更新证照主表数据
				$pcfg = D($this->MPcfg)->updatePcfg($where,$pcfg_change);
				if($pcfg['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MPcfg)->delPcfg_tmp('PARTNER_MAP_ID = '.$post['PARTNER_MAP_ID']);
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
		$partner_info = D($this->MPartner)->findPartner('a.PARTNER_MAP_ID = "'.$id.'"','a.*, b.BRANCH_NAME');
		$pcfg_info = D($this->MPcfg)->findPcfg_tmp("PARTNER_MAP_ID='".$id."'");
		if ($pcfg_info['PARTNER_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '2'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$this->assign('partner_info',$partner_info);
		$this->assign('pcfg_info',$pcfg_info);
		$this->display('psett_check');
	}

	//审核记录
	public function partner_check_note(){
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//判断当前状态是否符合复审操作
		$partnertatus = D($this->MPartner)->findPartner('PARTNER_MAP_ID = "'.$id.'"','PARTNER_STATUS');
		/*if ($partnertatus['SHOP_STATUS'] != 6) {
			$this->wrong('当前状态不允许初审操作');
		}*/
		//获取审核记录
		$check_no = '2'.setStrzero($id,15);
		$where = 'CHECK_NO = "'.$check_no.'"';
		//分页
		$count = D($this->MCheck)->countCheck($where);
		if($count){
			//分页
			//$count = D($this->MCheck)->countCheck($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MCheck)->getChecklist($where, $field='*', $limit, $order='CHECK_ID desc');
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->display();
	}
}