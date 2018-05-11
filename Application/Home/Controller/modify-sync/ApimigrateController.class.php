<?php
namespace Home\Controller;
// +----------------------------------------------------------------------
// | @gzy  积分宝迁移数据调用
// +----------------------------------------------------------------------
class ApimigrateController extends HomeController {
	
	public function _initialize() {
		$this->MBranch		= 'MBranch';
		$this->MBbact		= 'MBbact';
		$this->MPartner	 	= 'MPartner';
		$this->MPcert		= 'MPcert';
		$this->MPbact		= 'MPbact';
		$this->MPauth		= 'MPauth';
		$this->MPcls		= 'MPcls';
		$this->MPcfg		= 'MPcfg';
		$this->GVipcard	 	= 'GVipcard';
		$this->GVip			= 'GVip';
		$this->MFeecfg		= 'MFeecfg';
		$this->GLap			= 'GLap';
		$this->MReg			= 'MReg';
		$this->MCity		= 'MCity';
		$this->MFactory		= 'MFactory';
		$this->MModel		= 'MModel';
		$this->MDevice		= 'MDevice';
		$this->MShop		= 'MShop';
		$this->MScert		= 'MScert';
		$this->MSauth		= 'MSauth';
		$this->MSmdr		= 'MSmdr';
		$this->MSbact		= 'MSbact';
		$this->MSdkb		= 'MSdkb';
		$this->MScfg		= 'MScfg';
		$this->MUser		= 'MUser';
		$this->MRole		= 'MRole';
	}
	
	//ajax callback
	protected function ajaxRets($status,$msg,$data) {
    	$res = array(
    		'state'		=>	$status,
    		'msg'		=>	$msg,
			'result'	=>	!empty($data) ? $data : array(),
    	);
		Add_LOG(CONTROLLER_NAME, $msg);
    	$this->ajaxReturn($res);
    }
	//整合保险单号
	protected function updateTBNO($yw_no, $yl_no){
		//都不存在
		if (empty($yw_no) && empty($yl_no)) {
			return '';
		}
		//意外险单号存在
		if (empty($yw_no)) {
			$YW = setStrzero('', 40, ' ','r');
		}else{
			$YW = setStrzero($yw_no, 40, ' ','r');
		}
		//养老险单号存在
		if (empty($yl_no)) {
			$YL = setStrzero('', 40, ' ','r');
		}else{
			$YL = setStrzero($yl_no, 40, ' ','r');
		}
		return $YW.$YL;
	}

	
	
	/*
	* 分支机构
	**/
	public function branch() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),		//分支公司ID
			'BRANCH_NAME'		=> 	I('BRANCH_NAME'),		//中文名称			80
			'BRANCH_NAMEAB'		=>	I('BRANCH_NAMEAB'),		//中文名称简称		40
			'CITY_NO'			=>	I('CITY_NO'),			//所在城市数字代码
			'ADDRESS'			=>	I('ADDRESS'),			//地址				80
			'ZIP'				=>	I('ZIP'),				//邮编
			'MANAGER'			=>	I('MANAGER'),			//联系经理			20
			'MOBILE'			=>	I('MOBILE'),			//经理手机
			'TEL'				=>	I('TEL'),				//联系电话
			'FAX'				=>	I('FAX'),				//联系传真
			'SECURITY_MAP_ID'	=>	I('SECURITY_MAP_ID'),	//承保公司
			'BANKACCT_NAME'		=>	I('BANKACCT_NAME'),		//结算户名			80
			'BANKACCT_NO'		=>	I('BANKACCT_NO'),		//结算户账号		
			'BANKACCT_BID'		=>	I('BANKACCT_BID'),		//结算户行联行号
			'BANK_NAME'			=>	I('BANK_NAME'),			//结算户开户行		80
			'BANK_FLAG'			=>	I('BANK_FLAG'),			//结算标志
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['BRANCH_MAP_ID']) || strlen($post['BRANCH_NAME'])>80 || strlen($post['BRANCH_NAMEAB'])>40 || strlen($post['ADDRESS'])>80 || 
		strlen($post['MANAGER'])>20 || strlen($post['BANKACCT_NAME'])>80 || strlen($post['BANK_NAME'])>80){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$branchdata = D($this->MBranch)->findBranch("BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'");
		if(!empty($branchdata)){
			$this->ajaxRets(1, '该分支机构已经存在！');
		}
		//branch 表
		$branch = array(
			'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],
			'BRANCH_LEVEL'		=>	1,
			'BRANCH_MAP_ID_P'	=>	100000,
			'BRANCH_NAME'		=>	$post['BRANCH_NAME'],
			'BRANCH_NAMEAB'		=>	$post['BRANCH_NAMEAB'],
			'BRANCH_STATUS'		=>	0,
			'HOST_MAP_ID'		=>	1,
			'HOST_MAP_ID_NEW'	=>	1,
			'CITY_NO'			=>	$post['CITY_NO'],
			'ADDRESS'			=>	$post['ADDRESS'],
			'ZIP'				=>	$post['ZIP'],
			'MANAGER'			=>	$post['MANAGER'],
			'MOBILE'			=>	$post['MOBILE'],
			'TEL'				=>	$post['TEL'],
			'FAX'				=>	$post['FAX'],
			'SECURITY_MAP_ID'	=>	$post['SECURITY_MAP_ID'],
		);
		$M = M();
		$M->startTrans();	//启用事务
		
		$res = D($this->MBranch)->addBranch($branch);
		if($res['state'] != 0){
			$this->ajaxRets(1, '分支机构导入失败！');
		}
		//bbact 表
		$bbact = array(
			'BRANCH_MAP_ID'		=>	$res['BRANCH_MAP_ID'],
			'BANKACCT_NAME'		=>	$post['BANKACCT_NAME'],
			'BANKACCT_NO'		=>	$post['BANKACCT_NO'],
			'BANKACCT_BID'		=>	$post['BANKACCT_BID'],
			'BANK_NAME'			=>	$post['BANK_NAME'],
			'BANK_FLAG'			=>	$post['BANK_FLAG'],
		);
		$res_bb = D($this->MBbact)->addBbact($bbact);
		if($res_bb['state'] != 0){
			$M->rollback();	//回滚
			$this->ajaxRets(1, '分支机构账户导入失败！');
		}
		
		$M->commit();	//成功
		$this->ajaxRets(0, '分支机构导入成功！');
	}
	
	/*
	* 会员
	**/
	public function vip() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),							//隶属分支
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),						//会员归属
			'VIP_SOURCE'		=>	I('VIP_SOURCE'),							//会员来源
			'VIP_ID'			=>	I('VIP_ID'),								//会员ID
			'CARD_NO'			=>	I('CARD_NO'),								//卡号号码
			'VIP_NAME'			=>	I('VIP_NAME'),								//隶属分支			30			*
			'VIP_STATUS'		=>	I('VIP_STATUS'),							//会员状态
			'VIP_CARD_FLAG'		=>	I('VIP_CARD_FLAG'),							//绑定状态
			'VIP_AUTH_FLAG'		=>	I('VIP_AUTH_FLAG'),							//实名认证标志
			'VIP_IDNOTYPE'		=>	I('VIP_IDNOTYPE'),							//会员证件类型
			'VIP_IDNO'			=>	I('VIP_IDNO'),								//证件号码						*
			'VIP_MOBILE'		=>	I('VIP_MOBILE'),							//手机号码						*
			'VIP_CITY'			=>	I('VIP_CITY'),								//所在城市
			'VIP_ADDRESS'		=>	I('VIP_ADDRESS'),							//户口地址			100			*
			'VIP_SEX'			=>	I('VIP_SEX'),								//性别							*
			'VIP_EMAIL'			=>	I('VIP_EMAIL') ,							//持卡人电子邮件				*
			'VIP_BIRTHDAY'		=>	I('VIP_BIRTHDAY'),							//持卡人生日					*
			'VIP_DONATE'		=>	I('VIP_DONATE'),							//子母卡标志
			'VIP_DONATE_PER'	=>	I('VIP_DONATE_PER'),						//收益转让百比
			'VIP_ID_M'			=>	I('VIP_ID_M'),								//母卡会员ID
			'CREATE_TIME'		=>	I('CREATE_TIME'),							//创建时间
			'ACTIVE_TIME'		=>	I('ACTIVE_TIME'),							//激活时间
			'UPDATE_TIME'		=>	I('UPDATE_TIME'),							//修改时间
			'YWX_NO'			=>	I('YWX_NO'),								//备注信息
			'YLX_NO'			=>	I('YLX_NO'),								//备注信息
			'ID_PHOTO'			=>	I('ID_PHOTO'),								//身份证图片
			'YW_VAL_AMT'		=>	I('YW_VAL_AMT'),							//未结算意外险金额
			'YL_VAL_AMT'		=>	I('YL_VAL_AMT'),							//未结算养老金金额
			'DIV_AMT'			=>	I('DIV_AMT'),								//预免回收卡费余额
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['BRANCH_MAP_ID']) || empty($post['VIP_ID']) || empty($post['CARD_NO']) || strlen($post['VIP_NAME'])>30 || $post['VIP_IDNOTYPE']=='' || 
		empty($post['VIP_IDNO']) || empty($post['VIP_MOBILE']) || strlen($post['VIP_ADDRESS'])>100){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		//处理迁移会员重复
		$is_more = D($this->GVip)->findVip("VIP_ID = '".$post['VIP_ID']."' and VIP_MOBILE = '".$post['VIP_MOBILE']."'");
		if(!empty($is_more['VIP_ID'])){
			$this->ajaxRets(0, '会员导入成功！');
		}
		//证件号
		$indo = D($this->GVip)->findVip("VIP_IDNOTYPE = '".$post['VIP_IDNOTYPE']."' and VIP_IDNO = '".$post['VIP_IDNO']."'");
		if(!empty($indo)){
			$this->ajaxRets(1, '该证件号已经存在！');
		}
		//手机号
		$mobile = D($this->GVip)->findVip("VIP_MOBILE = '".$post['VIP_MOBILE']."'");
		if(!empty($mobile)){
			$this->ajaxRets(1, '该手机号已经存在！');
		}
		//性别
		if($post['VIP_IDNOTYPE']==0 && $post['VIP_IDNOTYPE']!=''){
			$idlen = strlen($post['VIP_IDNO']);
			if($idlen == 15){
				$sex = substr($post['VIP_IDNO'], -1, 1);
			}elseif ($idlen == 18) {
				$sex = substr($post['VIP_IDNO'], -2, 1);
			}
			
			if($sex%2 != $post['VIP_SEX']){
				$this->ajaxRets(1, '会员性别不合法！');
			}
		}
		//vip表
		$vip = array(
			'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],
			'VIP_SOURCE'		=>	$post['VIP_SOURCE'],
			'VIP_ID'			=>	$post['VIP_ID'],
			'CARD_NO'			=>	$post['CARD_NO'],
			'VIP_NAME'			=>	$post['VIP_NAME'],
			'VIP_STATUS'		=>	$post['VIP_STATUS'],
			'VIP_CARD_FLAG'		=>	$post['VIP_CARD_FLAG'],
			'VIP_AUTH_FLAG'		=>	$post['VIP_AUTH_FLAG'],
			'VIP_PARTNER_FLAG'	=>	0,
			'VIP_IDNOTYPE'		=>	$post['VIP_IDNOTYPE'],
			'VIP_IDNO'			=>	$post['VIP_IDNO'],
			'VIP_MOBILE'		=>	$post['VIP_MOBILE'],
			'VIP_CITY'			=>	$post['VIP_CITY'],
			'VIP_ADDRESS'		=>	$post['VIP_ADDRESS'],
			'VIP_SEX'			=>	$post['VIP_SEX'],
			'VIP_EMAIL'			=>	$post['VIP_EMAIL'],
			'VIP_BIRTHDAY'		=>	$post['VIP_BIRTHDAY'],
			'VIP_DONATE'		=>	$post['VIP_DONATE'],
			'VIP_DONATE_PER'	=>	$post['VIP_DONATE_PER'],
			'VIP_ID_M'			=>	$post['VIP_ID_M'],
			'VIP_PIN'			=>	strtoupper(md5(strtoupper(md5('888888')))),
			'VIP_PINTIME'		=>	0,
			'VIP_PINLIMIT'		=>	5,
			'CREATE_TIME'		=>	$post['CREATE_TIME'],
			'ACTIVE_TIME'		=>	$post['ACTIVE_TIME'],
			'UPDATE_TIME'		=>	$post['UPDATE_TIME'],
			'RES'				=>	$this->updateTBNO($post['YWX_NO'], $post['YLX_NO']),
			'ID_PHOTO'			=>	$post['ID_PHOTO'],
		);
		$M = M();
		$M->startTrans();	//启用事务
		
		$res = D($this->GVip)->addVip($vip);
		if($res['state'] != 0){
			$this->ajaxRets(1, '会员导入失败！');
		}
		
		//lap 表，	如果是预免卡3，只插入lap表，如果是收费卡2，插入lap表,还要插入reg表
		$lap = array(
			'SUBJECT_CODE'	=>	'20101',
			'ACCT_NO'		=>	setStrzero($res['VIP_ID'], 9),
			'ACCT_NAME'		=>	$post['VIP_NAME'],
			'ACCT_TYPE'		=>	'V',
			'ACCT_VALBAL'	=>	'0',
			'ACCT_YLTBAL'	=>	'0',
			'ACCT_YLVBAL'	=>	'0',
			'ACCT_YWTBAL'	=>	'0',
			'ACCT_YWVBAL'	=>	'0',
			'ACCT_DIVBAL'	=>	$post['DIV_AMT'],
			'ACCT_CAMT'		=>	'0',
			'ACCT_DAMT'		=>	'0',
			'ACCT_DATE'		=>	date('Ymd'),
			'SYSTEM_TIME'	=>	date('YmdHis'),
			'MAC'			=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',
			'YLT_AMT'		=>	'0',
			'YWT_AMT'		=>	'0',
		);
		$res_lap = D($this->GLap)->addLap($lap);
		if($res_lap['state'] != 0){
			$M->rollback();	//回滚
			$this->ajaxRets(1, '会员分账户表导入失败！');
		}
		
		//reg 表
		if($post['YL_VAL_AMT']>0 || $post['YW_VAL_AMT']>0){
			$reg    = array(
				'REG_TYPE'	=>	'201',
				'REG_INDEX'	=>	setStrzero($res['VIP_ID'], 9),
				'REG_DESC'	=>	$post['VIP_NAME'],
				'REG_AMT'	=>	'0',
				'MARK_FLAG'	=>	'1',
				'MARK_DATE'	=>	date('Ymd'),
				'YL_BAL'	=>	$post['YL_VAL_AMT'],
				'YW_BAL'	=>	$post['YW_VAL_AMT'],
				'DIV_BAL'	=>	$post['DIV_AMT'],
				'AMT1'		=>	'0',
				'AMT2'		=>	'0',
			);
			D($this->MReg)->addReg($reg);			
		}
		
		$M->commit();	//成功
		$this->ajaxRets(0, '会员导入成功！');
	}
		
	/*
	* 设备厂商
	**/
	public function facturer() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'FACTORY_NAME'		=>	I('FACTORY_NAME'),		//厂商名称			48
			'FACTORY_STATUS'	=>	I('FACTORY_STATUS'),	//公司状态
			'CITY_NO'			=>	I('CITY_NO'),			//所在城市数字代码
			'CITY_NAME'			=>	I('CITY_NAME'),			//所在城市名称		20
			'ADDRESS'			=>	I('ADDRESS'),			//厂商地址			80
			'ZIP'				=>	I('ZIP'),				//邮政编码
			'MANAGER'			=>	I('MANAGER'),			//联系人
			'TEL'				=>	I('TEL'),				//联系电话
			'WEB'				=>	I('WEB'),				//公司网址			40
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(strlen($post['FACTORY_NAME'])>48 || $post['FACTORY_STATUS']=='' || empty($post['CITY_NO']) || strlen($post['CITY_NAME'])>20 || 
		strlen($post['ADDRESS'])>80 || empty($post['ZIP']) || empty($post['MANAGER']) || empty($post['TEL']) || strlen($post['WEB'])>40){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$citydata = D($this->MCity)->findCity("CITY_S_CODE='".$post['CITY_NO']."'");
		//factory 表
		$factory = array(
			'FACTORY_NAME'		=>	$post['FACTORY_NAME'],
			'FACTORY_STATUS'	=>	$post['FACTORY_STATUS'],
			'CITY_NO'			=>	$post['CITY_NO'],
			'CITY_NAME'			=>	$citydata['CITY_NAME'] ? $citydata['CITY_NAME'] : '',
			'ADDRESS'			=>	$post['ADDRESS'],
			'ZIP'				=>	$post['ZIP'],
			'MANAGER'			=>	$post['MANAGER'],
			'TEL'				=>	$post['TEL'],
			'WEB'				=>	$post['WEB']
		);
		$res = D($this->MFactory)->addFactory($factory);
		if($res['state'] != 0){
			$this->ajaxRets(1, '设备厂商导入失败！');
		}
		$this->ajaxRets(0, '设备厂商导入成功！');
	}
		
	/*
	* 设备型号
	**/
	public function model() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'FACTORY_MAP_ID'	=>	I('FACTORY_MAP_ID'),	//厂商ID
			'MODEL_NAME'		=>	I('MODEL_NAME'),		//型号名称	10
			'MODEL_STATUS'		=>	I('MODEL_STATUS'),		//型号状态
			'MODEL_TYPE'		=>	I('MODEL_TYPE'),		//型号类型
			'MODEL_COMM'		=>	I('MODEL_COMM'),		//通讯方式
			'MODEL_PINPAD'		=>	I('MODEL_PINPAD'),		//密码键盘
			'MODEL_PRINTER'		=>	I('MODEL_PRINTER'),		//打印机
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['FACTORY_MAP_ID']) || strlen($post['MODEL_NAME'])>10 || $post['MODEL_STATUS']=='' || $post['MODEL_TYPE']=='' || 
		$post['MODEL_COMM']=='' || $post['MODEL_PINPAD']=='' || $post['MODEL_PRINTER']==''){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		//model 表
		$model = array(
			'FACTORY_MAP_ID'	=>	$post['FACTORY_MAP_ID'],
			'MODEL_NAME'		=>	$post['MODEL_NAME'],
			'MODEL_STATUS'		=>	$post['MODEL_STATUS'],
			'MODEL_TYPE'		=>	$post['MODEL_TYPE'],
			'MODEL_COMM'		=>	$post['MODEL_COMM'],
			'MODEL_PINPAD'		=>	$post['MODEL_PINPAD'],
			'MODEL_PRINTER'		=>	$post['MODEL_PRINTER'],
			'MODEL_PRICE'		=>	'0',
			'MODEL_REMARK'		=>	''
		);
		$res = D($this->MModel)->addModel($model);
		if($res['state'] != 0){
			$this->ajaxRets(1, '设备型号导入失败！');
		}
		$this->ajaxRets(0, '设备型号导入成功！');
	}
		
	/*
	* 设备基础信息
	**/
	public function device() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'FACTORY_MAP_ID'	=>	I('FACTORY_MAP_ID'),	//厂商ID
			'MODEL_MAP_ID'		=>	I('MODEL_MAP_ID'),		//型号ID
			'DEVICE_SN'			=>	I('DEVICE_SN'),			//设备序列号
			'DEVICE_STATUS'		=>	I('DEVICE_STATUS'),		//设备状态
			'DEVICE_ATTACH'		=>	I('DEVICE_ATTACH'),		//隶属标识
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),		//公司编号
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),	//代理编号
			'SHOP_NO'			=>	I('SHOP_NO'),			//安装商户
			'POS_NO'			=>	I('POS_NO'),			//安装POS编号
			'DEVICE_ADDRESS'	=>	I('DEVICE_ADDRESS'),	//安装地址		80
			'INSTALL_DATE'		=>	I('INSTALL_DATE'),		//安装日期
			'CREATE_TIME'		=>	I('CREATE_TIME'),		//入库时间
			'UPDATE_TIME'		=>	I('UPDATE_TIME'),		//安装时间
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['FACTORY_MAP_ID']) || empty($post['MODEL_MAP_ID']) || empty($post['DEVICE_SN']) || $post['DEVICE_STATUS']=='' || $post['DEVICE_ATTACH']=='' || 
		empty($post['BRANCH_MAP_ID']) || $post['PARTNER_MAP_ID']=='' || empty($post['SHOP_NO']) || empty($post['POS_NO']) || strlen($post['DEVICE_ADDRESS'])>80 || 
		empty($post['INSTALL_DATE']) || empty($post['CREATE_TIME']) || empty($post['UPDATE_TIME'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$finddata = D($this->MDevice)->findDevice("DEVICE_SN='".$post['DEVICE_SN']."'");
		if(!empty($finddata)){
			$this->ajaxRets("设备序列号 ".$post['DEVICE_SN']." 已经存在！");
		}
		$device = array(
			'FACTORY_MAP_ID'	=>	$post['FACTORY_MAP_ID'],
			'MODEL_MAP_ID'		=>	$post['MODEL_MAP_ID'],
			'DEVICE_SN' 		=>	$post['DEVICE_SN'],
			'DEVICE_STATUS'		=>	$post['DEVICE_STATUS'],
			'DEVICE_ATTACH'		=>	$post['DEVICE_ATTACH'],
			'BRANCH_MAP_ID'		=>  $post['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],
			'SHOP_NO'			=>	$post['SHOP_NO'],
			'POS_NO'			=>	$post['POS_NO'],
			'POS_INDEX'			=>	$post['DEVICE_SN'],
			'DEVICE_TOKEN'		=>	setStrzero('', 32, 'F'),
			'DEVICE_ADDRESS'	=>	$post['DEVICE_ADDRESS'],
			'INSTALL_DATE'		=>	$post['INSTALL_DATE'],
			'CREATE_USERID'		=>	'100000',
			'CREATE_USERNAME'	=>	'root',
			'CREATE_TIME'		=>	$post['CREATE_TIME'],
			'UPDATE_TIME'		=>	$post['UPDATE_TIME'],
		);
		$res = D($this->MDevice)->addDevice($device);
		if($res['state'] != 0){
			$this->ajaxRets(1, '设备基础信息导入失败！');
		}
		$this->ajaxRets(0, '设备基础信息导入成功！');
	}
	
	
	
	
	
	
	/*
	* 合作伙伴
	**/
	public function partner() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		//基础信息
		$partner_data = array(
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),									//隶属分支编号
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),								//组织编号
			'PARTNER_LEVEL'		=>	I('PARTNER_LEVEL'),									//级别
			'PARTNER_MAP_ID_P'	=>	I('PARTNER_MAP_ID_P') ? I('PARTNER_MAP_ID_P') : 0,	//上级合作方
			'PARTNER_P_FLAG'	=>	I('PARTNER_P_FLAG')	  ? I('PARTNER_P_FLAG')   : 0,	//与上级的关系
			'PARTNER_G_FLAG'	=>	I('PARTNER_G_FLAG')	  ? I('PARTNER_G_FLAG')   : 0,	//类型
			'PARTNER_MAP_ID_G'	=>	I('PARTNER_MAP_ID_G') ? I('PARTNER_MAP_ID_G') : 0,	//归属集团
			'PARTNER_NAME'		=>	I('PARTNER_NAME'),									//中文名称
			'PARTNER_NAMEAB'	=>	I('PARTNER_NAMEAB'),								//中文简称
			'PARTNER_STATUS'	=>	6,													//状态
			'PLEVEL_MAP_ID'		=>	I('PLEVEL_MAP_ID'),									//合作方架构
			'SECURITY_MAP_ID1'	=>	I('SECURITY_MAP_ID1') ? I('SECURITY_MAP_ID1') : 0,	//养老承保公司
			'SECURITY_MAP_ID2'	=>	I('SECURITY_MAP_ID2') ? I('SECURITY_MAP_ID2') : 0,	//意外承保公司
			'AUTH_ZONE'			=>	0,													//授权区域
			'JOIN_FEE'			=>	I('JOIN_FEE') ? I('JOIN_FEE') : 0,					//实收加盟费
			'FUND_AMT'			=>	I('FAFUND_AMTX') ? I('FAFUND_AMTX') : 0,			//实收保证金
			'CITY_NO'			=>	I('CITY_NO'),										//所在城市数字代码
			'ADDRESS'			=>	I('ADDRESS'),										//收货地址
			'ZIP'				=>	I('ZIP'),											//邮编
			'TEL'				=>	I('TEL') ? I('TEL') : '',							//联系电话
			'MANAGER'			=>	I('MANAGER'),										//联系人姓名
			'MOBILE'			=>	I('MOBILE'),										//联系人手机号
			'EMAIL'				=>	I('EMAIL') ? I('EMAIL') : '',						//邮件地址
			'SALER_ID'			=>	I('SALER_ID') ? I('SALER_ID') : '',					//销售编号
			'SALER_NAME'		=>	I('SALER_NAME') ? I('SALER_NAME') : '',				//销售人员
			'CREATE_USERID'		=>	I('CREATE_USERID') ? I('CREATE_USERID') : '',		//创建用户
			'CREATE_USERNAME'	=>	I('CREATE_USERNAME') ? I('CREATE_USERNAME') : '',	//创建用户
			'CREATE_TIME'		=>	I('CREATE_TIME') ? I('CREATE_TIME') : date("YmdHis"),//创建时间
			'END_TIME'			=>	I('END_TIME') ? I('END_TIME') : '',					//协议到期
		);
		Add_LOG(CONTROLLER_NAME, json_encode($partner_data));
		//基础数据验证
		if($partner_data['BRANCH_MAP_ID']=='' || empty($partner_data['PARTNER_MAP_ID']) || $partner_data['PARTNER_LEVEL']=='' || strlen($partner_data['PARTNER_NAME'])>80 || strlen($partner_data['PARTNER_NAMEAB'])>40 || $partner_data['CITY_NO']=='' || 
			strlen($partner_data['ADDRESS'])>100 || $partner_data['ZIP']=='' || $partner_data['PARTNER_STATUS']=='' || strlen($partner_data['MANAGER'])>20 || $partner_data['MOBILE']==''){
			$this->ajaxRets(1, '基础数据存在数据不规范，请检查！');
		}
		//组装证件数据
		$pcert_data = array(
			'PARTNER_MAP_ID'=>	I('PARTNER_MAP_ID'),
			'PARTNER_STATUS'=>	0,
			'AGREEMENT_NO'	=>	I('AGREEMENT_NO'),
			'REG_ADDR'		=>	I('REG_ADDR'),
			'REG_ID'		=>	I('REG_ID'),
			'TAX_ID'		=>	I('TAX_ID'),
			'ORG_ID'		=>	I('ORG_ID'),
			'LP_NAME'		=>	I('LP_NAME'),
			'LP_ID'			=>	I('LP_ID'),
			'REGID_PHOTO'	=>	I('REGID_PHOTO'),
			'TAXID_PHOTO'	=>	I('TAXID_PHOTO'),
			'ORGID_PHOTO'	=>	I('ORGID_PHOTO'),
			'LP_D_PHOTO'	=>	I('LP_D_PHOTO'),
			'LP_R_PHOTO'	=>	I('LP_R_PHOTO'),
			'BANK_PHOTO'	=>	I('BANK_PHOTO')
		);
		Add_LOG(CONTROLLER_NAME, json_encode($pcert_data));
		//证件数据验证
		if($pcert_data['PARTNER_MAP_ID']=='' || $pcert_data['AGREEMENT_NO']==''){
			$this->ajaxRets(1, '证件数据存在数据不规范，请检查！');
		}
		//组装权限数据
		$pauth_data = array(
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'PARTNER_STATUS'	=>	0,
			'HOST_MAP_ID'		=>	0,
			'AUTH_TRANS_MAP'	=>	setStrzero('',128,'1'),
			'AUTH_PAYS_MAP'		=>	setStrzero('',128,'1'),
		);
		//组装结算方式数据
		$pcls_data = array(
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'PARTNER_STATUS'	=>	0,
			'SETTLE_T'			=>	1,
			'SETTLE_T_UNIT'		=>	2,
			'SETTLE_TYPE'		=>	2,			//0：结算到合作伙伴 1：结算到商户 默认2保留
			'SETTLE_FLAG'		=>	0,
			'SETTLE_TOP_AMT'	=>	100000000,
			'SETTLE_FREE_AMT'	=>	100000000,
			'SETTLE_OFF_AMT'	=>	100000,
			'SETTLE_OFF_FEE'	=>	0,
			'SETTLE_FEE'		=>	0
		);
		//组装银行账户数据
		$pbact_data = array(
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'PARTNER_STATUS'	=>	0,
			'PARTNER_BANK_FLAG'	=>	I('PARTNER_BANK_FLAG'),
			'BANKACCT_NAME1'	=>	I('BANKACCT_NAME1'),
			'BANKACCT_NO1'		=>	I('BANKACCT_NO1'),
			'BANKACCT_BID1'		=>	I('BANKACCT_BID1'),
			'BANK_NAME1'		=>	I('BANK_NAME1'),
			'BANKACCT_NAME2'	=>	I('BANKACCT_NAME2'),
			'BANKACCT_NO2'		=>	I('BANKACCT_NO2'),
			'BANKACCT_BID2'		=>	I('BANKACCT_BID2'),
			'BANK_NAME2'		=>	I('BANK_NAME2')
		);
		Add_LOG(CONTROLLER_NAME, json_encode($pbact_data));
		//银行账户数据验证
		/*switch ($pbact_data['PARTNER_BANK_FLAG']) {
		 	case '0':
		 		if ($pbact_data['BANKACCT_NAME1']=='' || $pbact_data['BANKACCT_NO1']=='' || $pbact_data['BANKACCT_BID1']=='' || strlen($pbact_data['BANKACCT_NAME1'])>80 || $pbact_data['BANK_NAME1']=='' || strlen($pbact_data['BANK_NAME1'])>80 ) {
		 			$this->ajaxRets(1, '对公银行帐户数据存在数据不规范，请检查！');
		 		}
		 		break;
		 	case '1':
		 		if ($pbact_data['BANKACCT_NAME2']=='' || $pbact_data['BANKACCT_NO2']=='' || $pbact_data['BANKACCT_BID2']=='' || strlen($pbact_data['BANKACCT_NAME2'])>80 || $pbact_data['BANK_NAME2']=='' || strlen($pbact_data['BANK_NAME2'])>80 ) {
		 			$this->ajaxRets(1, '对私银行帐户数据存在数据不规范，请检查！');
		 		}
		 		break;
		 	default:
		 		$this->ajaxRets(1, '银行帐户数据导入失败！');
		 		break;
		}*/
		//组装其他配置数据
		$pcfg_data = array(
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'PARTNER_STATUS'	=>	0,
			'DIV_FLAG'			=>	I('DIV_FLAG') ? I('DIV_FLAG') : 0,
			'CARD_OPENFEE' 		=>	I('CARD_OPENFEE') ? I('CARD_OPENFEE') : 3000,
			'DIV_PER' 			=>	I('DIV_PER') ? I('DIV_PER') : 20,
			'RAKE_FLAG' 		=>	I('RAKE_FLAG') ? I('RAKE_FLAG') : 0,
			'CON_PER_RAKE' 		=>	I('CON_PER_RAKE') ? I('CON_PER_RAKE') : 5000,
			'PLAT_PER_RAKE' 	=>	I('PLAT_PER_RAKE') ? I('PLAT_PER_RAKE') : 5000
		);
		Add_LOG(CONTROLLER_NAME, json_encode($pcfg_data));
		$m = M();
		$m->startTrans();	//启用事务
		//基础数据入库
		$partner_res = D($this->MPartner)->addPartner($partner_data);
		if($partner_res['state']!=0){
			$this->ajaxRets(1, '合作伙伴基础信息导入失败！');
		}
		//证件数据入库
		$pcert_res = D($this->MPcert)->addPcert($pcert_data);
		if($pcert_res['state']!=0){
			$m->rollback();//回滚
			$this->ajaxRets(1, '合作伙伴证件信息导入失败！');
		}
		//权限数据入库
		$pauth_res = D($this->MPauth)->addPauth($pauth_data);
		if($pauth_res['state']!=0){
			$m->rollback();			//不成功，则回滚
			$this->ajaxRets(1, '合作伙伴权限信息导入失败！');
		}
		//结算方式数据入库
		$pcls_res = D($this->MPcls)->addPcls($pcls_data);
		if($pcls_res['state']!=0){
			$m->rollback();			//不成功，则回滚
			$this->ajaxRets(1, '合作伙伴结算方式信息导入失败！');
		}
		//银行账户数据入库
		$pbact_res = D($this->MPbact)->addPbact($pbact_data);
		if($pbact_res['state']!=0){
			$m->rollback();			//不成功，则回滚
			$this->ajaxRets(1, '合作伙伴银行账户信息导入失败！');
		}
		//其他配置数据入库
		$pcfg_res = D($this->MPcfg)->addPcfg($pcfg_data);
		if($pcfg_res['state']!=0){
			$m->rollback();			//不成功，则回滚
			$this->ajaxRets(1, '合作伙伴其他配置信息导入失败！');
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
				$this->ajaxRets(1, '合作伙伴操作员添加失败！');
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
			$this->ajaxRets(1, '合作伙伴操作员添加失败！');
		}*/
		$m->commit();	//全部成功则提交
		$this->ajaxRets(0, '导入成功！');
	}
	
	/*
	* 卡片
	**/
	public function cardno() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		//基础信息
		$card_no = I('CARD_NO');
		$card_data = array(
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),									//隶属分支编号
			'BRANCH_MAP_ID1'	=>	I('BRANCH_MAP_ID1'),								//隶属分支编号[原始]
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),								//分配发卡点
			'PARTNER_MAP_ID1'	=>	I('PARTNER_MAP_ID1'),								//分配发卡点[原始]
			'VIP_ID'			=>	I('VIP_ID'),										//会员代码
			'CARD_NO'			=>	$card_no,											//卡片卡号
			'CARD_BIN'			=>	'956688',											//卡产品类
			'CARD_P_MAP_ID'		=>	I('CARD_P_MAP_ID') ? I('CARD_P_MAP_ID') : '',		//卡产品ID
			'CARD_STATUS'		=>	I('CARD_STATUS'),									//卡号状态
			'CARD_EXP'			=>	'9912',												//卡有效期
			'CARD_TRACK2'		=>	$card_no.'='.getCardCheck($card_no),				//卡二磁道
			'CARD_CHECK'		=>	I('CARD_CHECK'),									//卡校验值
			'CARD_BIRTHDAY'		=>	I('CARD_BIRTHDAY'),									//开户日期
			'CARD_BATCH'		=>	0,													//发卡批次
			'ACTIVE_TIME'		=>	I('ACTIVE_TIME'),									//激活时间
			'UPDATE_TIME'		=>	I('ACTIVE_TIME')									//修改时间
		);
		Add_LOG(CONTROLLER_NAME, json_encode($card_data));
		//验证
		if($card_data['BRANCH_MAP_ID']=='' || $card_data['BRANCH_MAP_ID1']=='' || $card_data['PARTNER_MAP_ID']=='' || $card_data['PARTNER_MAP_ID1']=='' || 
			$card_data['VIP_ID']=='' || $card_data['CARD_NO'] =='' || $card_data['CARD_STATUS']=='' || 
			$card_data['CARD_CHECK']=='' || $card_data['CARD_BIRTHDAY']=='' || $card_data['ACTIVE_TIME']==''){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		//验证会员或卡号唯一
		$where = 'VIP_ID = "'.$card_data['VIP_ID'].'" or CARD_NO = "'.$card_data['CARD_NO'].'"';
		$is_check = D($this->GVipcard)->findVipcard($where,'VIP_ID,CARD_NO');
		if ($is_check) {
			$this->ajaxRets(1, '会员ID('.$is_check['VIP_ID'].')或卡号('.$is_check['CARD_NO'].')不能重复');
			exit;
		}
		//基础数据入库
		$vip_res = D($this->GVipcard)->addVipcard($card_data);
		if ($vip_res['state']!=0){
			$this->ajaxRets(1, '导入失败');
		}
		$this->ajaxRets(0, '导入成功');
	}
	
	/*
	* 更新VIP, RES字段
	**/
	public function update_vip_insure() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		//基础信息
		$post = array(
			'VIP_ID'		=>	I('VIP_ID'),			//会员ID
			'SECURITY_TYPE'	=>	I('SECURITY_TYPE'),		//险种
			'YWX_TB_NO'		=>	I('YWX_TB_NO'),			//意外险单号
			'YLX_TB_NO'		=>	I('YLX_TB_NO')			//养老险单号
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if($post['VIP_ID']=='' || $post['SECURITY_TYPE']=='' || ($post['YWX_TB_NO']=='' && $post['YLX_TB_NO']=='')){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}

		//获得会员数据
		$vipdata = D($this->GVip)->findVip('VIP_ID = '.$post['VIP_ID'], $field='*');
		if (empty($vipdata)) {
			$this->ajaxRets(1, '会员ID为'.$post['VIP_ID'].'的会员不存在，请检查！');
		}
		//修改vip表
		if ($post['SECURITY_TYPE']=='1' && !empty($post['YWX_TB_NO'])) {
			if(strlen($vipdata['RES']) == '80'){
				$yw_no = setStrzero($post['YWX_TB_NO'], 40, ' ','r');	//意外险单号
				$yl_no = substr($vipdata['RES'],40,40);					//养老险单号
				$vip_res = $yw_no.$yl_no;
			}else{
				$vip_res = setStrzero($post['YWX_TB_NO'], 80, ' ','r');
			}
		}else if($post['SECURITY_TYPE']=='2' && !empty($post['YLX_TB_NO'])){
			if(strlen($vipdata['RES']) == '80'){
				$yw_no = substr($vipdata['RES'],0,40);					//意外险单号
				$yl_no = setStrzero($post['YLX_TB_NO'], 40, ' ','r');	//养老险单号
				$vip_res = $yw_no.$yl_no;
			}else{
				$yw_no = setStrzero('', 40, ' ','r');					//意外险单号
				$yl_no = setStrzero($post['YWX_TB_NO'], 40, ' ','r');	//养老险单号
				$vip_res = $yw_no.$yl_no;
			}
		}else{
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$res = D($this->GVip)->updateVip('VIP_ID = '.$post['VIP_ID'], array('RES' => $vip_res));
		if ($vip_res['state']!=0){
			$this->ajaxRets(1, '导入失败');
		}
		$this->ajaxRets(0, '导入成功');
	}
	/*
	* 商户
	**/
	public function shop() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		//组装基本数据
		$shop_data = array(
			'SHOP_MAP_ID'		=>	I('SHOP_MAP_ID'),								//商户ID
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),								//隶属分支编号
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),							//组织编号
			'CHANNEL_MAP_ID'	=>	'100002',										//渠道编号
			'PARTNER_MAP_ID_R'	=>	I('PARTNER_MAP_ID_R'),							//推荐合作方
			'PARTNER_MAP_ID_B'	=>	I('PARTNER_MAP_ID_B'),							//大商家编号
			'AGENT_MAP_ID'		=>	0,												//归属代理商
			'SHOP_NO'			=>	setStrzero(I('SHOP_MAP_ID'),15),
			'SHOP_LEVEL'		=>	I('SHOP_LEVEL'),
			'SHOP_MAP_ID_P'		=>	I('SHOP_MAP_ID_P'),								//归属集团商户
			'SHOP_NAMEABCN'		=>	I('SHOP_NAMEABCN'),
			'SHOP_NAMEABEN'		=>	I('SHOP_NAMEABEN'),
			'SHOP_NAME'			=>	I('SHOP_NAME'),
			'SHOP_OPENTIME'		=>	I('SHOP_OPENTIME'),				 				//营业日期date('His'),
			'SHOP_CLOSETIME'	=>	I('SHOP_CLOSETIME'),							//打烊日期
			'SHOP_STATUS'		=>	6,
			'MCC_TYPE'			=>	I('MCC_TYPE'),
			'MCC_CODE'			=>	I('MCC_CODE'),
			'CUR_TCASHAMT'		=>	0,												//当天现金累计额
			'CUR_TOTALAMT'		=>	0,												//当天累计额
			'CUR_DATE'			=>	date('Ymd'),									//当日日期
			'CITY_NO'			=>	I('CITY_NO'),			
			'CITY_NAME'			=>	I('CITY_NAME'),
			'ADDRESS'			=>	I('ADDRESS'),
			'ZIP'				=>	I('ZIP'),
			'MOBILE'			=>	I('MOBILE'),
			'TEL'				=>	I('TEL'),
			'EMAIL'				=>	I('EMAIL'),
			'SALER_ID'			=>	0,
			'SALER_NAME'		=>	'-',
			'CREATE_USERID'		=>	0,
			'CREATE_USERNAME'	=>	'-',
			'CREATE_TIME'		=>	date('YmdHis')
		);
		Add_LOG(CONTROLLER_NAME, json_encode($shop_data));
		//基础数据验证
		if($shop_data['BRANCH_MAP_ID']=='' || $shop_data['PARTNER_MAP_ID']=='' || empty($shop_data['SHOP_LEVEL']) || strlen($shop_data['SHOP_NAME'])>80 || strlen($shop_data['SHOP_NAMEAB'])>40 || $shop_data['CITY_NO']=='' || 
			strlen($shop_data['ADDRESS'])>100 || $shop_data['ZIP']=='' || strlen($shop_data['MANAGER'])>20 || $shop_data['MOBILE']==''){
			$this->ajaxRets(1, '基础数据存在数据不规范，请检查！');
		}
		//判断商户归属集团商户
		if ($shop_data['SHOP_LEVEL'] == 3) {
			if ($glv['pid'] == '') {
				$this->ajaxRets(1, '缺少归属集团商户,且集团商户只能归属到合作方！');
			}
		}

		
		//判断推荐合作方归属
		/*if (!empty($shop_data['PARTNER_MAP_ID_R'])) {
			//基础数据入库
			$partner_res = D($this->MPartner)->findPartner('PARTNER_MAP_ID = '.$shop_data['PARTNER_MAP_ID_R'],'PARTNER_LEVEL');
			if($partner_res['PARTNER_LEVEL']!=2 || $partner_res['PARTNER_LEVEL']!=3){
				$this->ajaxRets(1,"推荐个创归属只能归属到区县服务中心或创业合伙人！");
			}
		}*/
		//组装证照数据
		$scert_data = array(
			'SHOP_MAP_ID'	=>	I('SHOP_MAP_ID'),
			'SHOP_STATUS'	=>	0,
			'AGREEMENT_NO'	=>	I('AGREEMENT_NO'),
			'REG_EXP'		=>	'20991231',
			'REG_ADDR'		=>	getcity_name(I('CITY_NO')).I('REG_ADDR'),
			'REG_ID'		=>	I('REG_ID'),
			'TAX_ID'		=>	I('TAX_ID'),
			'ORG_ID'		=>	I('ORG_ID'),
			'LP_NAME'		=>	I('LP_NAME'),
			'LP_ID'			=>	I('LP_ID'),
			'REGID_PHOTO'	=>	I('REGID_PHOTO'),
			'TAXID_PHOTO'	=>	I('TAXID_PHOTO'),
			'ORGID_PHOTO'	=>	I('ORGID_PHOTO'),
			'LP_D_PHOTO'	=>	I('LP_D_PHOTO'),
			'LP_R_PHOTO'	=>	I('LP_R_PHOTO'),
			'BANK_PHOTO'	=>	I('BANK_PHOTO'),
			'REGADDR_PHOTO1'=>	I('REGADDR_PHOTO1'),
			'REGADDR_PHOTO2'=>	I('REGADDR_PHOTO2'),
			'OFFICE_PHOTO1'	=>	I('OFFICE_PHOTO1'),
			'OFFICE_PHOTO2'	=>	I('OFFICE_PHOTO2'),
			'OFFICE_PHOTO3'	=>	I('OFFICE_PHOTO3'),
			'OFFICE_PHOTO4'	=>	I('OFFICE_PHOTO4'),
			'OTHER_PHOTOS'	=>	I('OTHER_PHOTOS'),
			'RES'			=>	I('RES')
		);
		//合同编号验证
		if (empty($scert_data['AGREEMENT_NO'])) {
			$this->ajaxRets(1,"证件中的合同编号不能为空, 请重新填写！");
		}else{
			$check_agreement = D($this->MScert)->findScert('AGREEMENT_NO = "'.$scert_data['AGREEMENT_NO'].'"','AGREEMENT_NO');
			if(!empty($check_agreement['AGREEMENT_NO']))
			{
				$this->ajaxRets(1,"当前证件中的合同编号已经存在, 请重新填写！");
			}
		}

		//组装权限数据
		$sauth_data = array(
			'SHOP_MAP_ID'		=>	I('SHOP_MAP_ID'),
			'SHOP_STATUS'		=>	0,
			'AUTH_TRANS_MAP'	=>	'11110111010101101111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',	//开通交易位图
			'AUTH_PAYS_MAP'		=>	setStrzero('',128,'1'),	//开通支付方式
			'DAY_MAXAMT'		=>	3000000,				//日均限额
			'DAY_CASH_MAXAMT'	=>	2000000,				//日均现金限额
			'CASH_MAXAMT'		=>	500000					//单笔现金限额
		);
		Add_LOG(CONTROLLER_NAME, json_encode($sauth_data));
		//银行账户
		$sbact_data = array(
			'SHOP_MAP_ID'		=>	I('SHOP_MAP_ID'),
			'SHOP_STATUS'		=>	0,
			'SHOP_BANK_FLAG'	=>	I('SHOP_BANK_FLAG'),
			'BANKACCT_NAME1'	=>	I('BANKACCT_NAME1'),
			'BANKACCT_NO1'		=>	I('BANKACCT_NO1'),
			'BANKACCT_BID1'		=>	I('BANKACCT_BID1'),
			'BANK_NAME1'		=>	I('BANK_NAME1'),
			'BANKACCT_NAME2'	=>	I('BANKACCT_NAME2'),
			'BANKACCT_NO2'		=>	I('BANKACCT_NO2'),
			'BANKACCT_BID2'		=>	I('BANKACCT_BID2'),
			'BANK_NAME2'		=>	I('BANK_NAME2')
		);
		Add_LOG(CONTROLLER_NAME, json_encode($sbact_data));
		//组装银行账户数据
		/*switch ($sbact_data['SHOP_BANK_FLAG']) {
		 	case '0':
		 		if ($sbact_data['BANKACCT_NAME1']=='' || $sbact_data['BANKACCT_NO1']=='' || $sbact_data['BANKACCT_BID1']=='' || strlen($sbact_data['BANKACCT_NAME1'])>80 || $sbact_data['BANK_NAME1']=='' || strlen($sbact_data['BANK_NAME1'])>80 ) {
		 			$this->ajaxRets(1, '对公银行帐户数据存在数据不规范，请检查！');
		 		}
		 		break;
		 	case '1':
		 		if ($sbact_data['BANKACCT_NAME2']=='' || $sbact_data['BANKACCT_NO2']=='' || $sbact_data['BANKACCT_BID2']=='' || strlen($sbact_data['BANKACCT_NAME2'])>80 || $sbact_data['BANK_NAME2']=='' || strlen($sbact_data['BANK_NAME2'])>80 ) {
		 			$this->ajaxRets(1, '对私银行帐户数据存在数据不规范，请检查！');
		 		}
		 		break;
		 	default:
		 		$this->ajaxRets(1, '银行帐户数据不规范，请检查！！');
		 		break;
		}*/

		//代扣账户
		$sdkb_data = array(
			'SHOP_MAP_ID'	=>	I('SHOP_MAP_ID'),
			'DKCO_MAP_ID'	=>	I('DKCO_MAP_ID'),		//代扣公司ID
			'SHOP_STATUS'	=>	0,
			'SHOP_BANK_FLAG'=>	I('SHOP_BANK_FLAG'),	//结算标志(0：结算到对公 1结算到对私)
			'BANKACCT_NAME1'=>	I('DK_BANKACCT_NAME1'),	//结算户名(对公)
			'BANKACCT_NO1'	=>	I('DK_BANKACCT_NO1'),	//结算户账号(对公)
			'BANKACCT_BID1'	=>	I('DK_BANKACCT_BID1'),	//结算户行联行号(对公)
			'BANK_NAME1'	=>	I('DK_BANK_NAME1'),		//结算户开户行(对公)
			'BANKACCT_NAME2'=>	I('DK_BANKACCT_NAME2'),	//结算户名(对私)
			'BANKACCT_NO2'	=>	I('DK_BANKACCT_NO2'),	//结算户账号(对私)
			'BANKACCT_BID2'	=>	I('DK_BANKACCT_BID2'),	//结算户行联行号(对私)
			'BANK_NAME2'	=>	I('DK_BANK_NAME2'),		//结算户开户行(对私)
			'SHOP_ACCT_FLAG'=>	I('SHOP_ACCT_FLAG'),	//账户卡折标志
			'DK_IDNO_TYPE'	=>	I('DK_IDNO_TYPE'),		//代扣证件类型(0身份证、1护照、2军人证3、回乡证   9为未知)
			'DK_IDNO'		=>	I('DK_IDNO')			//代扣证件号码
		);
		Add_LOG(CONTROLLER_NAME, json_encode($sdkb_data));
	/*	switch ($sdkb_data['SHOP_BANK_FLAG']) {
		 	case '0':
		 		if ($sdkb_data['BANKACCT_NAME1']=='' || $sdkb_data['BANKACCT_NO1']=='' || $sdkb_data['BANKACCT_BID1']=='' || strlen($sdkb_data['BANKACCT_NAME1'])>80 || $sdkb_data['BANK_NAME1']=='' || strlen($sdkb_data['BANK_NAME1'])>80 ) {
		 			$this->ajaxRets(1, '对公银行帐户数据存在数据不规范，请检查！');
		 		}
		 		break;
		 	case '1':
		 		if ($sdkb_data['BANKACCT_NAME2']=='' || $sdkb_data['BANKACCT_NO2']=='' || $sdkb_data['BANKACCT_BID2']=='' || strlen($sdkb_data['BANKACCT_NAME2'])>80 || $sdkb_data['BANK_NAME2']=='' || strlen($sdkb_data['BANK_NAME2'])>80 ) {
		 			$this->ajaxRets(1, '对私银行帐户数据存在数据不规范，请检查！');
		 		}
		 		break;
		 	default:
		 		$this->ajaxRets(1, '代扣数据不规范，请检查！！');
		 		break;
		}*/
		if($sdkb_data['DK_IDNO']=='' || $sdkb_data['DK_IDNO_TYPE']=='' || $sdkb_data['DKCO_MAP_ID']=='') {
			$this->ajaxRets(1, '代扣数据不规范，请检查！！');
		}

		//其他配置
		$scfg_data = array(
			'SHOP_MAP_ID'	=>	I('SHOP_MAP_ID'),
			'SHOP_STATUS'	=>	0,															//分期特殊分配比例
			'DIV_FLAG'		=>	I('DIV_FLAG') ? I('DIV_FLAG') : 0,							//分期特殊分配比例
			'CARD_OPENFEE'	=>	I('CARD_OPENFEE') ? I('CARD_OPENFEE') : 3000,				//卡收费总额
			'DIV_PER'		=>	I('DIV_PER') ? setMoney(I('DIV_PER')) : 0,					//预免卡分期比例
			'BOUND_RATE'	=>	I('BOUND_RATE') ? I('BOUND_RATE') : 0,						//积分兑换比例
			'RAKE_FLAG'		=>	I('RAKE_FLAG') ? I('RAKE_FLAG') : 0,						//特殊分配比例
			'CON_PER_RAKE'	=>	I('CON_PER_RAKE') ? I('CON_PER_RAKE') : 0,	//消费者比例
			'PLAT_PER_RAKE'	=>	I('PLAT_PER_RAKE') ? I('PLAT_PER_RAKE') : (10000-I('CON_PER_RAKE')),	//平台比例
			'DONATE_FLAG'	=>	I('DONATE_FLAG') ? I('DONATE_FLAG') : 0,					//转赠标志
			'DONATE_TYPE'	=>	I('DONATE_TYPE'),											//转赠产品
			'DONATE_RATE'	=>	I('DONATE_RATE') ? I('DONATE_RATE') : 0,					//转赠率
			'PARTNER_MAP_ID'=>	I('PARTNER_MAP_ID'),										//转赠对象
			'DONATE_RES'	=>	I('DONATE_RES')												//备注
		);
		Add_LOG(CONTROLLER_NAME, json_encode($scfg_data));
		if ($scfg['RAKE_FLAG'] == 1) {
			if($scfg['CON_PER_RAKE']>=0 && $scfg['PLAT_PER_RAKE']>=0 && ($scfg['CON_PER_RAKE'] + $scfg['PLAT_PER_RAKE'] != 100)){
				$this->ajaxRets(1,"运营分润的会员和平台比例必须是大于0的数字并且它们相加必须是100！");
			}
		}

		//入库操作
		$m = M();
		$m->startTrans();	//启用事务
		//基础数据入库
		$shop_res = D($this->MShop)->addShop($shop_data);
		if($shop_res['state']!=0){
			$this->ajaxRets(1, '商户基础信息导入失败！');
		}
		//证件数据入库
		$scert_res = D($this->MScert)->addScert($scert_data);
		if($scert_res['state']!=0){
			$m->rollback();//回滚
			$this->ajaxRets(1, '商户证件信息导入失败！');
		}

		//权限数据入库
		$sauth_res = D($this->MSauth)->addSauth($sauth_data);
		if($sauth_res['state']!=0){
			$m->rollback();			//不成功，则回滚
			$this->ajaxRets(1, '商户权限信息导入失败！');
		}
		//扣率数据入库
		$smdr_data = array(
			'SHOP_MAP_ID'	=>	I('SHOP_MAP_ID'),
			'SHOP_STATUS'	=>	0,
			'PER_FEE_1'		=>	I('PER_FEE') ? I('PER_FEE') : 0,				//商户扣率[银行卡]
			'JFB_PER_FEE_1'	=>	I('JFB_PER_FEE') ? I('JFB_PER_FEE') : 0,		//积分宝扣率[银行卡]
			'SETTLE_T'		=>	I('SETTLE_T') ? I('SETTLE_T') : 1,				//结算周期天数
			'SETTLE_T_UNIT'	=>	I('SETTLE_T_UNIT') ? I('SETTLE_T_UNIT') : 1,	//结算周期单位
			'FIX_FEE'		=>	I('FIX_FEE'),									//商户封顶扣率线
			'JFB_FIX_FEE'	=>	I('JFB_FIX_FEE')								//积分宝封顶扣率线 
		);

		//现金扣率
		$smdr['mdr'][0]['PER_FEE'] 	   = 0;
		$smdr['mdr'][0]['JFB_PER_FEE'] = $smdr_data['JFB_PER_FEE_1'] + $smdr_data['PER_FEE_1'];
		$smdr['mdr'][0]['PAY_TYPE']	   = 5;
		
		//银行卡扣率
		$smdr['mdr'][1]['JFB_PER_FEE'] = $smdr_data['JFB_PER_FEE_1'];
		$smdr['mdr'][1]['PER_FEE']	   = $smdr_data['PER_FEE_1'];
		$smdr['mdr'][1]['PAY_TYPE']	   = 0;
		$smdr['mdr'][1]['FIX_FEE']	   = $smdr_data['FIX_FEE'];
		$smdr['mdr'][1]['JFB_FIX_FEE'] = $smdr_data['JFB_FIX_FEE'];

		//积分宝的积分宝扣率, 等于银行卡商户扣率+积分宝扣率
		$smdr['mdr'][2]['PER_FEE']	   = 0;
		$smdr['mdr'][2]['JFB_PER_FEE'] = 0;
		$smdr['mdr'][2]['PAY_TYPE']	   = 4;
		//扣率数据 
		foreach ($smdr['mdr'] as $value) {
			//组装扣率数据
			$smdr_data1 = array(
				'SHOP_MAP_ID'		=>	$smdr_data['SHOP_MAP_ID'],
				'PAY_TYPE'			=>	$value['PAY_TYPE'] ? $value['PAY_TYPE'] : 0,
				'SHOP_STATUS'		=>	0,
				'SETTLE_T'			=>	$smdr_data['SETTLE_T'] ? $smdr_data['SETTLE_T'] : '1',
				'SETTLE_T_UNIT'		=>	$smdr_data['SETTLE_T_UNIT'] ? $smdr_data['SETTLE_T_UNIT'] : '1',
				'JFB_PER_FEE'		=>	$value['JFB_PER_FEE'] ? $value['JFB_PER_FEE'] : '0',	//积分宝比例扣率线(万分比)
				'JFB_FIX_FEE'		=>	$value['JFB_FIX_FEE'] ? $value['JFB_FIX_FEE'] : '0',	//积分宝封顶扣率线(单位分)
				'PER_FEE'			=>	$value['PER_FEE'] ? $value['PER_FEE'] : '0',			//商户比例扣线(万分比)
				'FIX_FEE'			=>	$value['FIX_FEE'] ? $value['FIX_FEE'] : '0',			//商户封顶扣率线(单位分)
				'DYN_PER_FEE'		=>	'0'
			);
			//扣率数据入库
			$smdr_res = D($this->MSmdr)->addSmdr($smdr_data1);
			if($smdr_res['state']!=0){
				$m->rollback();//不成功，则回滚
				$this->ajaxRets(1, '商户扣率数据添加失败！');
			}
		}
		//银行账户数据入库
		$sbact_res = D($this->MSbact)->addSbact($sbact_data);
		if($sbact_res['state']!=0){
			$m->rollback();			//不成功，则回滚
			$this->ajaxRets(1, '商户银行账户信息导入失败！');
		}

		//代扣账户数据入库
		$sdkb_res = D($this->MSdkb)->addSdkb($sdkb_data);
		if($sdkb_res['state']!=0){
			$m->rollback();		//不成功，则回滚
			$this->ajaxRets(1,'商户代扣银行信息添加失败！');
		}
		//其他配置数据入库
		$scfg_res = D($this->MScfg)->addScfg($scfg_data);
		if($scfg_res['state']!=0){
			$m->rollback();			//不成功，则回滚
			$this->ajaxRets(1, '商户其他配置信息导入失败！');
		}
		$m->commit();	//全部成功则提交
		$this->ajaxRets(0, '导入成功！');
	}
	/*
	* 商户终端
	**/
	public function shop_pos() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		//组装基本数据
		$pos_data = array(
			'POS_ID'			=>	I('POS_ID'),
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),					//隶属分支编号
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),				//组织编号
			'SHOP_MAP_ID'		=>	I('SHOP_MAP_ID'),					//商户ID
			'SHOP_NO'			=>	I('SHOP_NO'),
			'POS_NO'			=>	I('POS_NO'),
			'POS_INDEX'			=>	I('SHOP_NO').I('POS_NO'),			//备用索引(默认为商户编号+POS_NO)
			'DEVICE_SN'			=>	I('DEVICE_SN'),						//设备序列号
			'POS_STATUS'		=>	1,									//状态
			'POS_PBOCKEYFLAG'	=>	0,									//IC公钥更新标志
			'POS_PBOCPARAFLAG'	=>	0,									//IC参数更新标志
			'POS_PARAFLAG'		=>	0,									//参数更新标志
			'POS_PROGFLAG'		=>	0,									//程序更新标志
			'POS_CURPROGVER'	=>	0,									//终端程序当前版本号
			'POS_NEWPROGVER'	=>	0,									//终端程序最新版本号
			'POS_HMDFLAG'		=>	0,									//IC黑名单更新标志
			'POS_BATCH'			=>	00001,								//批次号
			'POS_TRACE'			=>	00001,								//流水号
			'POS_TIMEOUT'		=>	60,									//交易超时时间
			'POS_MAXAMT'		=>	10000000,							//单笔交易金额上限
			'POS_MAXCNT'		=>	500,								//累计笔数
			'POS_CARDFLAG'		=>	11,									//刷卡标志
			'POS_PINFLAG'		=>	000,								//密码标志
			'POS_COMM_RETRY'	=>	3,									//通讯重试次数
			'POS_CONFIRM_MODE'	=>	11,									//预授权完成方式
			'POS_TRANS_DEFAULT'	=>	1,									//默认交易支持
			'POS_TIP'			=>	0,									//小费支持
			'POS_TIP_PER'		=>	0,									//小费百分比
			'POS_MAN_MODE'		=>	0,									//手工输入卡号
			'POS_TRANS_RETRY'	=>	3,									//交易重发次数
			'POS_MAXREFUNDAMT'	=>	99999999,							//退货交易金额上限
			'POS_ECHOTIME'		=>	3600,								//回响周期
			'POS_LOGOUT'		=>	1,									//允许自动签退
			'POS_TICKETNUMS'	=>	2,									//打印票据单数
			'COM_INDEX'			=>	'',									//通讯参数索引
			'KEY_INDEX'			=>	I('SHOP_NO').I('POS_NO'),			//对称密钥索引
		);
		Add_LOG(CONTROLLER_NAME, json_encode($pos_data));
		if (empty($pos_data['POS_ID']) || empty($pos_data['BRANCH_MAP_ID']) || empty($pos_data['PARTNER_MAP_ID']) || empty($pos_data['SHOP_MAP_ID']) || 
			empty($pos_data['POS_NO']) || empty($pos_data['POS_INDEX']) || empty($pos_data['DEVICE_SN']) ) {
			$this->ajaxRets(1, '基础数据存在数据不规范，请检查！');
		}
		//判断设备是否在库中且未被占用
		$finddata = D($this->MDevice)->findDevice("DEVICE_SN='".$pos_data['DEVICE_SN']."' and POS_NO = '-' and DEVICE_STATUS = 2");
		if(empty($finddata)){
			$this->ajaxRets("设备序列号为".$pos_data['DEVICE_SN']."的设备在库存中不存在或该设备已经被使用, 受理失败！");
		}
		//判断是否POS_NO是否重复
		$data = D($this->MPos)->findPos('POS_NO="'.$pos_data['POS_NO'].'"','POS_NO');
		if (!empty($data['POS_NO'])) {
			$this->ajaxRets(1, 'POS_NO为:'.$pos_data['POS_NO'].' 的商户POS号已经存在，信息导入失败！');
		}
		$pos_res = D($this->MPos)->addPos($pos_data);
		if ($pos_res['state'] != 0){
			$this->ajaxRets('商户POS数据添加失败');
		}
		$this->ajaxRets(0, '商户POS导入成功！');
	}
}