<?php
namespace Home\Controller;
// +----------------------------------------------------------------------
// | @gzy  积分宝调用
// +----------------------------------------------------------------------
class ApiserverController extends HomeController {
	
	public function _initialize() {
		$this->checkToken();
		$this->MShop		= 'MShop';
		$this->MCity		= 'MCity';
		$this->MMcc			= 'MMcc';
		$this->GVip			= 'GVip';
		$this->GLap			= 'GLap';
		$this->GVipcard		= 'GVipcard';
		$this->MPartner		= 'MPartner';
		$this->MMcctype		= 'MMcctype';
		$this->MMcc			= 'MMcc';
		$this->TTrace		= 'TTrace';
		$this->MCproduct	= 'MCproduct';
		$this->MBranch		= 'MBranch';
		$this->MFeecfg		= 'MFeecfg';
		$this->TSmsls		= 'TSmsls';
		$this->MReg			= 'MReg';
		$this->MUser		= 'MUser';
		$this->MPos			= 'MPos';
		$this->MShopapply	= 'MShopapply';
		$this->GOutcard		= 'GOutcard';
		$this->MSmdr		= 'MSmdr';
		$this->MScfg		= 'MScfg';
	}
	
	//ajax callback
	protected function ajaxRets($status, $msg, $data) {
    	$res = array(
    		'status'		=>	$status.'',
    		'message'		=>	$msg,
			'requestcode'	=>	'',
			'result'		=>	!empty($data) ? $data : (object)array(),
    	);
		Add_LOG(CONTROLLER_NAME, $msg);
    	$this->ajaxReturn($res);
    }
	
	//验证token
	protected function checkToken() {
		$post = array(
			'user_id'		=> 	I('user_id'),
			'access_token'	=>	I('access_token')
		);
		if(empty($post['user_id']) || empty($post['access_token'])) {
			$this->ajaxRets(1, '缺少秘钥！');
		}
		$keyword = strtoupper(md5(strtoupper(md5($post['user_id'].'2016'))));
		if($keyword != $post['access_token']) {
			$this->ajaxRets(1, '秘钥匹配错误！');			
		}
	}
	
	/*
	* app登录【1】【通了】
	**/
	public function getlogindata() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'loginUserType'		=>	I('loginUserType'),		//请求类型，100商户登录 200会员登录 300合作伙伴登录
			'loginId'			=> 	I('loginId'),			//商户：商户id     会员：手机号、积分宝卡号、身份证号     合作方：公户工号、密码登录
			'cpwd'				=> 	I('cpwd'),				//密码
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(!in_array($post['loginUserType'], array('100','200','300')) || empty($post['loginId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		//商户
		if($post['loginUserType'] == '100'){
			$data = D($this->MShop)->findShop("SHOP_MAP_ID = '".$post['loginId']."' and SHOP_STATUS = 0", '*');
			if(empty($data)){
				$this->ajaxRets(1, '商户不存在！');
			}
			$citydata = D($this->MCity)->findCity("CITY_S_CODE = '".$data['CITY_NO']."'", 'PROVINCE_CODE,CITY_CODE,CITY_S_CODE');
			$mccdata  = D($this->MMcc)->findMcc("MCC_CODE = '".$data['MCC_CODE']."'", 'MCC_CODE,MCC_NAME');
			//获取商户pos
			$poslist  = D($this->MPos)->getPoslist("p.SHOP_MAP_ID ='".$post['loginId']."' and POS_STATUS = 0", 'p.POS_NO');		
			//获取商户归属
			$oParentRelation = jfbguishu($data['PARTNER_MAP_ID'],$data['SHOP_MAP_ID']);
			//组装数据
			$res[] = array(
				'loginUserType'		=>	$post['loginUserType'],
				'oId'				=>	$data['SHOP_MAP_ID'],					//组织ID
				'oParentId'			=>	$data['SHOP_MAP_ID_P'],					//上级组织ID
				'oParentName'		=>	'',										//上级组织名称
				'oName'				=>	$data['SHOP_NAME'],						//名称
				'oAddress'			=>	getcity_name($data['CITY_NO']).$data['ADDRESS'],	//地址
				'oStatus'			=>	$data['SHOP_STATUS'],					//状态
				'oAddTime'			=>	$data['CREATE_TIME'],          			//时间
				'oArea'				=>	$citydata['CITY_S_CODE'],				//地区ID
				'uId'				=>	$data['SHOP_MAP_ID'],					//用户ID
				'opPointRatio'		=>	set_jifenlv($data['SHOP_NO'],1),       	//积分率
				'dCodeName'			=>	$mccdata['MCC_NAME'],         			//所属行业
				'aParentIdCity'		=>	$citydata['CITY_CODE'].'00',         	//市ID
				'aParentIdProvince'	=>	$citydata['PROVINCE_CODE'].'0000',    	//省ID
				'ciMobile'			=>	$data['MOBILE'],          				//手机
				'ciTelephone'		=>	$data['TEL'],          					//电话
				'ciEmail'			=>	$data['EMAIL'],							//邮箱
				'mapX'				=>	'',										//经度
				'mapY'				=>	'',										//纬度;
				'dCode'				=>	$mccdata['MCC_CODE'],					//行业编码;
				//'oType'			=>	$data['SHOP_LEVEL'],					//组织类型01010101为区县级服务中心0101010101联盟店0101010102组织发卡点0101010103个人发卡点;
				'mercId'			=>	$data['SHOP_NO'],          				//商户号
				'termId'			=>	'99999999',//$poslist,					//终端号
				'oType'				=>	'0101010101',							//类型字符串
				'oParentRelation'	=>	$oParentRelation						//组织归属
			);
		}
		//会员
		else if($post['loginUserType'] == '200'){
			//$data = D($this->GVip)->findmoreVip("v.VIP_MOBILE = '".$post['loginId']."' or v.CARD_NO = '".$post['loginId']."' or (v.VIP_IDNO = '".$post['loginId']."' and v.VIP_IDNOTYPE=0) and v.VIP_STATUS = 0", 'v.*,l.ACCT_YLTBAL');
			
			if(strlen($post['loginId']) == 15 or strlen($post['loginId']) == 18){
				$data = D($this->GVip)->findVip("VIP_IDNOTYPE='0' and VIP_IDNO = '".$post['loginId']."' and VIP_STATUS = '0'");	//按身份证号
			}else{
				$data = D($this->GVip)->findVip("(VIP_MOBILE = '".$post['loginId']."' or CARD_NO = '".$post['loginId']."') and VIP_STATUS = '0'");//按手机或卡号
			}
			if(empty($data)){
				$this->ajaxRets(1, '会员不存在！');
			}
			//查询养老金
			$data2 = D($this->GLap)->findLap("ACCT_NO = '".setStrzero($data['VIP_ID'],9)."'", 'ACCT_YLTBAL,ACCT_YLVBAL,ACCT_YWTBAL,ACCT_YWVBAL');
			//组装数据
			$citydata = D($this->MCity)->findCity("CITY_S_CODE = '".$data['VIP_CITY']."'", 'PROVINCE_CODE,PROVINCE_NAME,CITY_CODE,CITY_NAME,CITY_S_CODE,CITY_S_NAME');
			if (substr($data['VIP_IDNO'], 0, 1) == '9') {
				$vip_auth_flag = 0;
			}else{
				$vip_auth_flag = 1;
			}

			//获取会员归属
			$mParentRelation = jfbguishu($data['PARTNER_MAP_ID'],$data['VIP_ID']);

			//组装数据
			$res[] = array(
				'loginUserType'		=>	$post['loginUserType'],
				'oId'				=>	$data['PARTNER_MAP_ID'],				//组织ID
				'mId'				=>	$data['VIP_ID'],						//会员编码
				'mName'				=>	$data['VIP_NAME'],						//会员姓名
				'mMobile'			=>	$data['VIP_MOBILE'],					//手机号
				'mBinding'			=>	$data['VIP_CARD_FLAG'],					//是否绑定0未绑定1绑定
				'mAuth_flag'		=>	$vip_auth_flag,							//实名认证标志0：否 1是
				'mEmail'			=>	$data['VIP_EMAIL'],						//电子邮件
				'mQQ'				=>	'',										//QQ
				'aId'				=>	$data['VIP_CITY'],
				'location'			=>	$citydata['PROVINCE_NAME'].' '.$citydata['CITY_NAME'].' '.$citydata['CITY_S_NAME'],
				'mNativeAddress'	=>	getcity_name($data['VIP_CITY']).$data['VIP_ADDRESS'],	//原籍地址mCurrentAddress			//现住地址
				'cFreePoint'		=>	($data2['ACCT_YLTBAL']+$data2['ACCT_YLVBAL']) ? ($data2['ACCT_YLTBAL']+$data2['ACCT_YLVBAL']) : 0,		//积分
				'cId'				=>	$data['CARD_NO'],                       //会员卡号（000000开头的是电子卡，不然是真实卡）
				'mIdentityId'		=>	$data['VIP_IDNO'],                 		//身份证号
				'mActivate'			=>	$data['VIP_STATUS'],					//状态: 0激活1没有激活(无审核)2自已修改自已信息(只能修改一次)10需要审核的卡3删除
				'mParentRelation'	=>	$mParentRelation						//会员归属
			);
		}
		//合作伙伴
		else{
			//验证密码
			if(empty($post['cpwd'])){
				$this->ajaxRets(1, '存在数据不规范，请检查！');
			}
			$checklog = array(
				'user_no'	=>	$post['loginId'],
				'password'	=>	strtoupper(md5($post['cpwd'])),
			);
			$response = D($this->MUser)->Login($checklog);
			if($response['state'] != 0){
				$this->ajaxRets(1, $response['msg']);
			}
			$response = $response['userinfo'];
			//总部或分公司
			if($response['PARTNER_MAP_ID'] == '0'){
				$branch  = D($this->MBranch)->findBranch("BRANCH_MAP_ID = '".$response['BRANCH_MAP_ID']."'");
				$bcity   = D($this->MCity)->findCity("CITY_S_CODE = '".$data['CITY_NO']."'", 'RPAD(PROVINCE_CODE,6,"0") as PROVINCE_CODE,RPAD(CITY_CODE,6,"0") as CITY_CODE,CITY_S_CODE');
				$oType = '01010101';
				//获取合作伙伴归属
				$oParentRelation = '['.$response['BRANCH_MAP_ID'].',0]';
			}else{
				$partner = D($this->MPartner)->findPartnerOne("PARTNER_MAP_ID = '".$response['PARTNER_MAP_ID']."'");
				$pcity   = D($this->MCity)->findCity("CITY_S_CODE = '".$data['CITY_NO']."'", 'RPAD(PROVINCE_CODE,6,"0") as PROVINCE_CODE,RPAD(CITY_CODE,6,"0") as CITY_CODE,CITY_S_CODE');
				if ($partner['PARTNER_LEVEL'] == 3) {
					$oType = '0101010102';
				}else{
					$oType = '01010101';
				}
				//获取合作伙伴归属
				$oParentRelation = jfbguishu($partner['PARTNER_MAP_ID']);
			}
			
			//组装数据
			$res[] = array(
				'loginUserType'		=>	$post['loginUserType'],
				'oId'				=>	$response['PARTNER_MAP_ID'] == '0' ? $branch['BRANCH_MAP_ID'] : $partner['PARTNER_MAP_ID'],		//组织ID
				'oParentId'			=>	$response['PARTNER_MAP_ID'] == '0' ? $branch['BRANCH_MAP_ID_P'] : $partner['PARTNER_MAP_ID'],	//上级组织ID
				'oParentName'		=>	'',																								//上级组织名称
				'oName'				=>	$response['PARTNER_MAP_ID'] == '0' ? $branch['BRANCH_NAME'] : $partner['PARTNER_NAME'],			//名称
				'oAddress'			=>	$response['PARTNER_MAP_ID'] == '0' ? getcity_name($branch['CITY_NO']).$branch['ADDRESS'] : getcity_name($partner['CITY_NO']).$partner['ADDRESS'],	//地址
				'oStatus'			=>	$response['PARTNER_MAP_ID'] == '0' ? $branch['BRANCH_STATUS'] : $partner['PARTNER_STATUS'],		//状态
				'oAddTime'			=>	$response['PARTNER_MAP_ID'] == '0' ? '' : $partner['CREATE_TIME'],					          	//时间
				'oArea'				=>	$response['PARTNER_MAP_ID'] == '0' ? $bcity['CITY_S_CODE'] : $pcity['CITY_S_CODE'],				//地区ID
				'uId'				=>	$post['loginId'],//$response['PARTNER_MAP_ID'] == '0' ? '' : $partner['CREATE_USERID'],							//用户ID
				'opPointRatio'		=>	'',								         														//积分率
				'dCodeName'			=>	'',         																					//所属行业
				'aParentIdCity'		=>	$response['PARTNER_MAP_ID'] == '0' ? $bcity['CITY_CODE'] : $pcity['CITY_CODE'],         		//市ID
				'aParentIdProvince'	=>	$response['PARTNER_MAP_ID'] == '0' ? $bcity['PROVINCE_CODE'] : $pcity['PROVINCE_CODE'],    		//省ID
				'ciMobile'			=>	$response['PARTNER_MAP_ID'] == '0' ? $branch['MOBILE'] : $partner['MOBILE'],          			//手机
				'ciTelephone'		=>	$response['PARTNER_MAP_ID'] == '0' ? $branch['TEL'] : $partner['TEL'],          				//电话
				'ciEmail'			=>	$response['PARTNER_MAP_ID'] == '0' ? '' : $partner['EMAIL'],									//邮箱
				'mapX'				=>	'',																								//经度
				'mapY'				=>	'',																								//纬度
				'dCode'				=>	'',																								//行业编码
				//'oType'			=>	$response['PARTNER_MAP_ID'] == '0' ? '' : $partner['PARTNER_LEVEL'],							//组织类型01010101为区县级服务中心0101010101联盟店0101010102组织发卡点0101010103个人发卡点;
				'mercId'			=>	'',				          																		//商户号
				'termId'			=>	'',																								//终端号
				'roleId'			=>	$response['ROLE_ID'],																			//权限
				'oType'				=>	$oType,																							//类型字符串
				'oParentRelation'	=>	$oParentRelation																				//组织归属
			);
		}
		$this->ajaxRets(0, '获取登录数据成功！', $res);
	}
	
	/*
	* 获取省级区域【2】
	**/
	public function getcityslist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$list = D($this->MCity)->getCityslist('RPAD(PROVINCE_CODE,6,"0") as aId,PROVINCE_NAME as aName');
		$this->ajaxRets(0, '获取省级列表成功！', $list);
	}
	
	/*
	* 获市级下属区域(县区)【3】
	**/
	public function getcityxlist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'sId'			=>	I('sId'),			//市id
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['sId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}		
		$list = D($this->MCity)->getCityxlist("CITY_CODE = '".substr($post['sId'],0,-2)."'", 'CITY_S_CODE as areaId,CITY_S_NAME as areaName, "" as current_code');
		$this->ajaxRets(0, '获取县级列表成功！', $list);
	}
			
	/*
	* 获取所有开通服务的市级【4】
	**/
	public function getopenpartlist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$plist = D($this->MPartner)->getPartnerlist("a.PARTNER_LEVEL = 1", 'a.CITY_NO');
		$str   = i_array_column($plist, 'CITY_NO');
		$str   = implode(',', $str);
		if(empty($str)){
			$this->ajaxRets(0, '暂无数据！');
		}
		$list = D($this->MCity)->getCityxlist("CITY_S_CODE in (".$str.")", 'RPAD(CITY_CODE,6,"0") as areaId,CITY_NAME as areaName,"" as pyName,"" as level,"" as isopen,RPAD(PROVINCE_CODE,6,"0") as parentID,"" as current_code');
		$this->ajaxRets(0, '获取开通服务的市级成功！', $list);
	}
			
	/*
	* 获取组织列表 - 服务中心【5】==
	**/
	public function getzuzhipartlist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'branch_map_id'		=>	I('branch_map_id'),		//分公司id
			'partner_map_id'	=>	I('partner_map_id'),	//合作方归属id
			'page'				=>	I('page'),				//第几页
			'num'				=>	I('num'),				//每页多少条
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['city_no'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$list = D($this->MCity)->getCitylist($post['city_no']);
		$this->ajaxRets(0, '成功！', $list);
	}
		
	/*
	* 获取组织列表 - 商户【5】==
	**/
	public function getzuzhishoplist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'branch_map_id'		=>	I('branch_map_id'),		//分公司id
			'partner_map_id'	=>	I('partner_map_id'),	//合作方归属id
			'page'				=>	I('page'),				//第几页
			'num'				=>	I('num'),				//每页多少条
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['city_no'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$list = D($this->MCity)->getCitylist($post['city_no']);
		$this->ajaxRets(0, '成功！', $list);
	}
	
	/*
	* 获取所有行业【6】
	**/
	public function getmcclist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$list = D($this->MMcctype)->getMcctypelist("MCC_TYPE != ''", 'MCC_TYPE as dCode,MCC_DESC as dCodeName');
		foreach($list as $key=>$val){
			$list[$key]['child'] = D($this->MMcc)->getMcclist("MCC_TYPE = '".$val['dCode']."'", 'MCC_CODE as cCode,MCC_NAME as cCodeName');
		}
		$this->ajaxRets(0, '获取行业列表成功！', $list);
	}
	
	/*
	* 查询会员养老金【7】【通了】
	**/
	public function findviply() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mId'		=>	I('mId'),		//会员id
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		//查找会员数据
		$vipdata = D($this->GVip)->findVip("VIP_ID = '".$post['mId']."'");
		if(empty($vipdata)){
			$this->ajaxRets(1, '会员不存在！');
		}
		//查询养老金
		$data = D($this->GLap)->findLap("ACCT_NO = '".setStrzero($post['mId'], 9)."'", 'ACCT_YLTBAL,ACCT_YLVBAL,ACCT_YWTBAL,ACCT_YWVBAL');
		//组装数据
		$res[] = array(
			'pension'	=>	setMoney(($data['ACCT_YLTBAL']+$data['ACCT_YLVBAL']),2,2),
		);
		$this->ajaxRets(0, '获取会员养老金成功！', $res);
	}
	

	/*
	* 绑定手机号 【8】
	**/
	public function upvipmob() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mId'		=>	I('mId'),		//会员id
			'mMobile'	=>	I('mMobile')	//需要绑定的手机号
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mId']) || empty($post['mMobile'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$data = D($this->GVip)->findVip("VIP_ID = '".$post['mId']."'");
		if(empty($data['VIP_ID'])){
			$this->ajaxRets(1, '会员不存在！');
		}
		$m_res = D($this->GVip)->findVip("VIP_MOBILE = '".$post['mMobile']."' and VIP_ID != '".$post['mId']."'", 'VIP_ID');
		if(!empty($m_res['VIP_ID'])){
			$this->ajaxRets(1, '该手机号已经存在！');
		}
		$updata = array(
			'VIP_MOBILE' => $post['mMobile']
		);
		//如果未绑卡，卡片数据也要同步修改
		/*$findvip3 = D($this->GVip)->findNewsVip("VIP_ID = '".$post['VIP_ID']."'");*/
		if (strlen($data['CARD_NO']) < 16) {
			$resdata['CARD_NO'] = '00'.$post['VIP_ID'];
			$data['CARD_NO']    = '00'.$post['VIP_ID'];
		}else{
			$post['CARD_NO']    = $data['CARD_NO'];
		}
		$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		$m->startTrans();	//启用事务
		$res = D($this->GVip)->updateVip("VIP_ID = '".$post['mId']."'", array('VIP_MOBILE' => $post['mMobile']));
		if($res['state'] != 0){
			$this->ajaxRets(1, '手机绑定失败');
		}

		//同步会员数据
		$url = VIP_PUSH_URL.'api/open/synchronize/member/modify';
		$vdata = array(
			'token' 	 	 => strtoupper(md5(strtoupper(md5($data['VIP_ID'].'2')))),	//(签名验证)
			'mId' 	 		 => $data['VIP_ID'],					//(会员ID)
			'mCId' 	 		 => $data['CARD_NO'],					//(卡号)
			'mName' 		 => $data['VIP_NAME'],					//(会员姓名)
			'mIdentityType'	 => $data['VIP_IDNOTYPE'],				//(证件类型)
			'mIdentityId' 	 => $data['VIP_IDNO'],					//(证件号)
			'mBirthday'		 => $data['VIP_BIRTHDAY'],				//(会员生日)
			'mMobile' 		 => $post['mMobile'],					//(手机号码)
			'gender' 		 => $data['VIP_SEX'],					//(会员性别)
			'mCurrentCity'	 => getcity_name($data['VIP_CITY']),	//(所在城市)
			'mNativeAddress' => $data['VIP_ADDRESS'],				//(户口地址)
			'mEmail' 		 => $data['VIP_EMAIL'],					//(会员邮箱)
			'operateType'	 => '2',								//(操作类型)
		);

		Add_LOG(CONTROLLER_NAME, json_encode($vdata));
/*
		$resjson = httpPostForm($url,$vdata);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->code != '0') {
			$m->rollback();	//回滚
			$this->ajaxRets(1,'会员数据同步失败');
		}
*/
		$m->commit();		//提交
		$this->ajaxRets(0, '手机绑定成功');
	}
	/*
	* 查询养老金明细【11】【通了】
	**/
	public function getviplylist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mId'			=>	I('mId'),				//会员id
			'startTime'		=>	I('startTime'),			//开始时间（2015-07-31）此格式,无须时分秒
			'endTime'		=>	I('endTime'),			//截止时间
			'pageSize'		=>	I('pageSize'),			//单页记录数
			'pCountIndex'	=>	I('pCountIndex'),		//页码（比如第一页为1）
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$where = "t.VIP_ID = '".$post['mId']."'";
		//交易日期	开始
		if($post['startTime']) {
			$where .= " and t.SYSTEM_DATE >= '".date('Ymd',strtotime($post['startTime']))."'";
		}
		//交易日期	结束
		if($post['endTime']) {
			$where .= " and t.SYSTEM_DATE <= '".date('Ymd',strtotime($post['endTime']))."'";
		}
		$limit = ($post['pCountIndex'] - 1)*$post['pageSize'].','.$post['pageSize'];
		$count = D($this->TTrace)->countTrace($where);
		$tlist = D($this->TTrace)->getTracelist($where, 't.TRANS_AMT,t.SYSTEM_DATE,t.SYSTEM_TIME,t.SHOP_NO,t.SHOP_NAMEAB,j.CON_FEE', $limit);
		foreach($tlist as $val){
			//组装数据
			$list[] = array(
				'dealMny'		=>	setMoney($val['TRANS_AMT'],2,2),				//消费金额
				'dealtime'		=>	$val['SYSTEM_DATE'].' '.$val['SYSTEM_TIME'],   	//消费日期
				'ratio'			=>	set_jifenlv($val['SHOP_NO'],1),			 		//积分率
				'oName'			=>	$val['SHOP_NAMEAB'], 							//商户店铺名称
				'point'			=>	$val['CON_FEE'] ? setMoney($val['CON_FEE'],2,2) : '0',	//养老金			
			);
		}
		$res = array(
			'total'		=>	ceil($count/$post['pageSize']),
			'data'		=>	!empty($list) ? $list : array(),
		);	
		$this->ajaxRets(0, '获取会员养老金列表成功！', $res);
	}
	
	/*
	* 查询养老金7天走势【12】【通了】
	**/
	public function getviplysevlist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mId'		=>	I('mId'),		//会员id
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$time0 = date('Ymd', strtotime("-1 day"));
		$time1 = date('Ymd', strtotime("-2 day"));
		$time2 = date('Ymd', strtotime("-3 day"));
		$time3 = date('Ymd', strtotime("-4 day"));
		$time4 = date('Ymd', strtotime("-5 day"));
		$time5 = date('Ymd', strtotime("-6 day"));
		$time6 = date('Ymd', strtotime("-7 day"));	
		$data0 = D($this->TTrace)->findmoreTrace("t.VIP_ID = '".$post['mId']."' and t.SYSTEM_DATE = '".$time0."'", 'sum(j.CON_FEE) as CON_FEE');
		$data1 = D($this->TTrace)->findmoreTrace("t.VIP_ID = '".$post['mId']."' and t.SYSTEM_DATE = '".$time1."'", 'sum(j.CON_FEE) as CON_FEE');
		$data2 = D($this->TTrace)->findmoreTrace("t.VIP_ID = '".$post['mId']."' and t.SYSTEM_DATE = '".$time2."'", 'sum(j.CON_FEE) as CON_FEE');
		$data3 = D($this->TTrace)->findmoreTrace("t.VIP_ID = '".$post['mId']."' and t.SYSTEM_DATE = '".$time3."'", 'sum(j.CON_FEE) as CON_FEE');
		$data4 = D($this->TTrace)->findmoreTrace("t.VIP_ID = '".$post['mId']."' and t.SYSTEM_DATE = '".$time4."'", 'sum(j.CON_FEE) as CON_FEE');
		$data5 = D($this->TTrace)->findmoreTrace("t.VIP_ID = '".$post['mId']."' and t.SYSTEM_DATE = '".$time5."'", 'sum(j.CON_FEE) as CON_FEE');
		$data6 = D($this->TTrace)->findmoreTrace("t.VIP_ID = '".$post['mId']."' and t.SYSTEM_DATE = '".$time6."'", 'sum(j.CON_FEE) as CON_FEE');
		//$zong  = D($this->TTrace)->findmoreTrace("t.VIP_ID = '".$post['mId']."'", 'sum(j.CON_FEE) as CON_FEE');
		$data = D($this->GVip)->findmoreVip("VIP_ID = '".$post['mId']."'", 'l.ACCT_YLTBAL,l.ACCT_YLVBAL,l.ACCT_YWTBAL,l.ACCT_YWVBAL');
		
		//组装数据
		$res = array(
			'total'	=>	setMoney(($data['ACCT_YLTBAL']+$data['ACCT_YLVBAL']),2,2),
			//'total'	=>	$zong['CON_FEE'] ? $zong['CON_FEE']/100 : '0',
			'data'	=>	array(
				array('date'=>	date('m-d',strtotime($time6)),'value'=>	$data6['CON_FEE'] ? $data6['CON_FEE'] : '0'),
				array('date'=>	date('m-d',strtotime($time5)),'value'=>	$data5['CON_FEE'] ? $data5['CON_FEE'] : '0'),
				array('date'=>	date('m-d',strtotime($time4)),'value'=>	$data4['CON_FEE'] ? $data4['CON_FEE'] : '0'),
				array('date'=>	date('m-d',strtotime($time3)),'value'=>	$data3['CON_FEE'] ? $data3['CON_FEE'] : '0'),
				array('date'=>	date('m-d',strtotime($time2)),'value'=>	$data2['CON_FEE'] ? $data2['CON_FEE'] : '0'),
				array('date'=>	date('m-d',strtotime($time1)),'value'=>	$data1['CON_FEE'] ? $data1['CON_FEE'] : '0'),
				array('date'=>	date('m-d',strtotime($time0)),'value'=>	$data0['CON_FEE'] ? $data0['CON_FEE'] : '0'),
			)
		);
		$this->ajaxRets(0, '获取养老金7天走势成功！', $res);
	}
	
	/*
	* 会员注册【13】【通了】
	**/
	public function addvip() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mMobile'		=>	I('mMobile'),		//手机号
			'uId'			=>	I('uId'),			//发卡点
			'mPassword'		=>	I('mPassword'),		//密码
			'mRemark'		=>	I('remark')
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mMobile']) || empty($post['uId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		//检测手机号
		$mobile = D($this->GVip)->findVip("VIP_MOBILE = '".$post['mMobile']."'");
		if(!empty($mobile)){
			$this->ajaxRets(1, '该手机号已经存在！');
		}
		if ($post['uId'] == 4) {
			$u_data['PARTNER_MAP_ID'] = 4;
		}else{
			$u_data = D($this->MUser)->findUser('USER_NO = "'.$post['uId'].'"','PARTNER_MAP_ID');
		}
		
		$partdata = D($this->MPartner)->findPartnerOne("PARTNER_MAP_ID = '".$u_data['PARTNER_MAP_ID']."'", 'BRANCH_MAP_ID,PARTNER_MAP_ID');
		if(empty($partdata)) {
			$this->ajaxRets(1, '合作方不存在！');
		}
		if ($partdata['PARTNER_MAP_ID'] == 4) {
			$vflag = '-';
		}else{
			$vflag = '1';
		}
		//组装数据
		$resdata = array(
			'BRANCH_MAP_ID'		=>	$partdata['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'	=>	$partdata['PARTNER_MAP_ID'],
			'VIP_SOURCE'		=>	'3',
			'CARD_NO'			=>	'00'.$post['mMobile'],
			'VIP_NAME'			=>	$post['mMobile'],
			'VIP_STATUS'		=>	'0',
			'VIP_CARD_FLAG'		=>	$vflag,
			'VIP_AUTH_FLAG'		=>	'0',	//认证
			'VIP_PARTNER_FLAG'	=>	'0',
			'VIP_IDNOTYPE'		=>	'9',
			'VIP_IDNO'			=>	'9'.$post['mMobile'],
			'VIP_MOBILE'		=>	$post['mMobile'],
			'VIP_CITY'			=>	'',
			'VIP_ADDRESS'		=>	'',
			'VIP_SEX'			=>	'',
			'VIP_EMAIL'			=>	'piccbill@jfb315.net',
			'VIP_BIRTHDAY'		=>	'',
			'VIP_DONATE'		=>	'0',
			'VIP_DONATE_PER'	=>	'',
			'VIP_ID_M'			=>	'',
			'VIP_PIN'			=>	strtoupper(md5(strtoupper(md5('888888')))),
			'VIP_PINTIME'		=>	'0',
			'VIP_PINLIMIT'		=>	'5',
			'CREATE_TIME'		=>	date('YmdHis'),
			'ACTIVE_TIME'		=>	date('YmdHis'),
			'UPDATE_TIME'		=>	date('YmdHis'),
			'RES'				=>	'',
			'ID_PHOTO'			=>	'',
		);
		$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		$m->startTrans();	//启用事务
			
		$res_vip = D($this->GVip)->addVip($resdata);
		if($res_vip['state'] != 0){
			$this->ajaxRets(1, '会员注册失败！');
		}
		$up_cardno = D($this->GVip)->updateVip("VIP_ID = '".$res_vip['VIP_ID']."'", array('CARD_NO' => '00'.$res_vip['VIP_ID'],'VIP_IDNO' => '99'.$res_vip['VIP_ID']));
		if ($up_cardno['state'] != 0) {
			$m->rollback();	//回滚
			$this->ajaxRets(1, '会员卡号生成失败！');
		}
		//因为有归属并且为虚拟卡类型，只插入lap表
		$fee = D($this->MCproduct)->findCproduct_one('CARD_P_MAP_ID = 1', 'USER_OPENFEE');
		//$fee = D($this->MFeecfg)->findFeecfg('CFG_FLAG = 1', 'CARD_OPENFEE');
		$lap = array(
			'SUBJECT_CODE'	=>	'20101',
			'ACCT_NO'		=>	setStrzero($res_vip['VIP_ID'], 9),
			'ACCT_NAME'		=>	$resdata['VIP_NAME'],
			'ACCT_TYPE'		=>	'V',
			'ACCT_VALBAL'	=>	'0',
			'ACCT_YLTBAL'	=>	'0',
			'ACCT_YLVBAL'	=>	'0',
			'ACCT_YWTBAL'	=>	'0',
			'ACCT_YWVBAL'	=>	'0',
			'ACCT_DIVBAL'	=>	$fee['USER_OPENFEE'],
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
			$m->rollback();	//回滚
			$this->ajaxRets(1, '会员分账户表导入失败！');
		}		
		$m->commit();		//成功

		//同步新增会员数据
		$url = VIP_PUSH_URL.'api/open/synchronize/member/register';
		$data = array(
			'token' 	 	 => strtoupper(md5(strtoupper(md5($res_vip['VIP_ID'].'1')))),	//(签名验证)
			'mId' 	 		 => $res_vip['VIP_ID'],					//(会员ID)
			'mCId' 	 		 => '00'.$res_vip['VIP_ID'],			//(卡号)
			'mName' 		 => $resdata['VIP_NAME'],				//(会员姓名)
			'mIdentityType'	 => $resdata['VIP_IDNOTYPE'],			//(证件类型)
			'mIdentityId' 	 => '99'.$res_vip['VIP_ID'],			//(证件号)
			'mBirthday'		 => $resdata['VIP_BIRTHDAY'],			//(会员生日)
			'mMobile' 		 => $resdata['VIP_MOBILE'],				//(手机号码)
			'gender' 		 => $resdata['VIP_SEX'],				//(会员性别)
			'mCurrentCity'	 => getcity_name($resdata['VIP_CITY']),	//(所在城市)
			'mNativeAddress' => $resdata['VIP_ADDRESS'],			//(户口地址)
			'mEmail' 		 => $resdata['VIP_EMAIL'],				//(会员邮箱)
			'operateType'	 => '1',								//(操作类型)
			'migrateType'	 => '2',								//(迁移区分)
			'mRemark'	 	 => $post['mRemark'],
			'mPassword'	 	 => $post['mPassword']
		);

		Add_LOG(CONTROLLER_NAME, json_encode($data));
/*
		$resjson = httpPostForm($url,$data);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->code != '0') {
			Add_LOG(CONTROLLER_NAME, '会员数据同步失败');
		}
*/
		
		$res[] = array(
			'mId'	=>	$res_vip['VIP_ID'].'',		
		);
		$this->ajaxRets(0, '注册成功！', $res);
	}
	
	/*
	* 根据会员mId查询会员【14】【通了】
	**/
	public function findvip() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mId'		=>	I('mId'),		//会员id
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		//$data = D($this->GVip)->findmoreVip("VIP_ID = '".$post['mId']."'", 'v.*,l.ACCT_YLTBAL');

		$data = D($this->GVip)->findVip("VIP_STATUS = 0 and VIP_ID = '".$post['mId']."'");//按手机或卡号
		if(empty($data)){
			$this->ajaxRets(1, '会员不存在！');
		}
		//查询养老金
		$data2 = D($this->GLap)->findLap("ACCT_NO = '".setStrzero($data['VIP_ID'],9)."'", 'ACCT_YLTBAL,ACCT_YLVBAL,ACCT_YWTBAL,ACCT_YWVBAL');

		$citydata = D($this->MCity)->findCity("CITY_S_CODE = '".$data['CITY_NO']."'", 'RPAD(PROVINCE_CODE,6,"0") as PROVINCE_CODE,RPAD(CITY_CODE,6,"0") as CITY_CODE,CITY_S_CODE');
		//组装数据
		$res[] = array(
			'mId'				=>	$data['VIP_ID'],						//会员编码
			'mName'				=>	$data['VIP_NAME'],						//会员姓名
			'mMobile'			=>	$data['VIP_MOBILE'],					//手机号
			'mBinding'			=>	$data['VIP_CARD_FLAG'],					//是否绑定0未绑定1绑定
			'mEmail'			=>	$data['VIP_EMAIL'],						//电子邮件
			'mQQ'				=>	'',										//QQ
			'mNativeAddress'	=>	getcity_name($data['VIP_CITY']).$data['VIP_ADDRESS'],	//原籍地址mCurrentAddress			//现住地址
			'cFreePoint'		=>	$data2['ACCT_YLTBAL'] ? $data2['ACCT_YLTBAL'] : '0',		//积分
			'mActivate'			=>	$data['VIP_STATUS'],					//状态: 0激活1没有激活(无审核)2自已修改自已信息(只能修改一次)10需要审核的卡3删除
			'mParentRelation'	=>	'',										//组织结构关系
			'mIdentityId'		=>	$data['VIP_IDNO'],						//证件号码
			'mAreaOid'			=>	$data['PARTNER_MAP_ID'],				//服务中心ID
			'oId'				=>	'0',									//0
			'mCurrentAddress'	=>	getcity_name($data['VIP_CITY']).$data['VIP_ADDRESS'],		//地址
			'mProvOid'			=>	$citydata['PROVINCE_CODE'],				//省ID
			'mCityOid'			=>	$citydata['CITY_CODE'],					//市id
			'cId'				=>	$data['CARD_NO'],						//卡号
		);
		$this->ajaxRets(0, '获取会员信息成功！', $res);
	}
	
	/*
	* 会员实名认证【15】【通了】
	**/
	public function authvip() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'VIP_IDNOTYPE'	=>	I('cardType'),	//证件类型
			'VIP_IDNO'		=>	I('idNo'),		//身份证
			'VIP_MOBILE'	=>	I('mMobile'),	//手机号
			'VIP_EMAIL'		=>	I('email'),		//邮箱(选填)
			'VIP_CITY'		=>	I('location'),	//地区(选填)
			'VIP_NAME'		=>	I('name'),		//用户姓名
			'SEX'			=>	I('gender'),	//性别
			'BIRTHDAY'		=>	I('birth'),		//生日
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if($post['VIP_IDNOTYPE'] =='' || empty($post['VIP_IDNO']) || empty($post['VIP_MOBILE']) || empty($post['VIP_NAME'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}		
		$data = D($this->GVip)->findVip("VIP_MOBILE = '".$post['VIP_MOBILE']."'");
		if(empty($data)){
			$this->ajaxRets(1, '会员不存在！');
		}
		if($data['VIP_AUTH_FLAG'] != '0'){
			$this->ajaxRets(1, '该会员已经实名认证！');
		}		
		//检测证件号
		$card = D($this->GVip)->findVip("VIP_MOBILE != '".$post['VIP_MOBILE']."' and VIP_IDNOTYPE = '".$post['VIP_IDNOTYPE']."' and VIP_IDNO = '".$post['VIP_IDNO']."'");
		if(!empty($card)){
			$this->ajaxRets(1, '该证件号已注册！');
		}
		
		$city_no = $card['VIP_CITY'] ? $card['VIP_CITY'] : substr($post['VIP_IDNO'], 0, 6);
		/*$Y		 = substr($post['VIP_IDNO'], 6, 4);
		$m		 = substr($post['VIP_IDNO'], 10, 2);
		$d		 = substr($post['VIP_IDNO'], 12, 2);
		$sex	 = substr($post['VIP_IDNO'], 16, 1)%2 == 0 ? '0' : '1';*/

		//如果性别存在，那使用，否则根据身份证生成
		if ($post['SEX'] != '') {
			$sex = $post['SEX'];
		}else{
			$sex = substr($post['VIP_IDNO'], 16, 1)%2 == 0 ? '0' : '1';
		}
		//如果生日存在，那使用，否则根据身份证生成
		if (!empty($post['BIRTHDAY'])) {
			$birthday = $post['BIRTHDAY'];
		}else{
			$Y		  = substr($post['VIP_IDNO'], 6, 4);
			$m		  = substr($post['VIP_IDNO'], 10, 2);
			$d		  = substr($post['VIP_IDNO'], 12, 2);
			$birthday = $Y.$m.$d;
		}

		//组装数据
		$resdata = array(
			'VIP_NAME'			=>	$post['VIP_NAME'],
			'VIP_AUTH_FLAG'		=>	'1',	//认证
			'VIP_IDNOTYPE'		=>	$post['VIP_IDNOTYPE'],
			'VIP_IDNO'			=>	$post['VIP_IDNO'],
			'VIP_CITY'			=>	$post['VIP_CITY'] ? $post['VIP_CITY'] : $city_no,
			'VIP_SEX'			=>	$sex,
			'VIP_EMAIL'			=>	$post['VIP_EMAIL'] ? $post['VIP_EMAIL'] : 'piccbill@jfb315.net',
			'VIP_BIRTHDAY'		=>	$birthday,
			'UPDATE_TIME'		=>	date('YmdHis'),
		);
		$M = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		$M->startTrans();	//启用事务
		$res = D($this->GVip)->updateVip("VIP_ID = '".$data['VIP_ID']."'", $resdata);
		if($res['state'] != 0){
			$this->ajaxRets(1, '会员实名认证失败！');
		}
		//同步修改会员数据
		$url = VIP_PUSH_URL.'api/open/synchronize/member/modify';
		$vdata = array(
			'token' 	 	 => strtoupper(md5(strtoupper(md5($data['VIP_ID'].'2')))),	//(签名验证)
			'mId' 	 		 => $data['VIP_ID'].'',							//(会员ID)
			'mCId' 	 		 => $data['CARD_NO'].'',						//(卡号)
			'mName' 		 => $resdata['VIP_NAME'].'',					//(会员姓名)
			'mIdentityType'	 => $resdata['VIP_IDNOTYPE'].'',				//(证件类型)
			'mIdentityId' 	 => $resdata['VIP_IDNO'].'',					//(证件号)
			'mBirthday'		 => $resdata['VIP_BIRTHDAY'],					//(会员生日)
			'mMobile' 		 => $post['VIP_MOBILE'].'',						//(手机号码)
			'gender' 		 => $resdata['VIP_SEX'].'',						//(会员性别)
			'mCurrentCity'	 => getcity_name($resdata['VIP_CITY']).'',		//(所在城市)
			'mNativeAddress' => $data['VIP_ADDRESS'].'',					//(户口地址)
			'mEmail' 		 => $resdata['VIP_EMAIL'].'',					//(会员邮箱)
			'operateType'	 => '2'											//(操作类型)
		);
		Add_LOG(CONTROLLER_NAME, json_encode($vdata));
/*
		$resjson = httpPostForm($url,$vdata);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->code != '0') {
			$M->rollback();	//回滚
			$this->ajaxRets(1,'会员数据同步失败');
		}
*/
		$M->commit();		//回滚
		$this->ajaxRets(0, '实名认证成功！');
	}
	
	/*
	* 根据区县查询发卡点【16】
	**/
	public function findvippartner() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mId'		=>	I('mId'),		//会员id
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$data = D($this->GVip)->findVip("VIP_ID = '".$post['mId']."'", 'BRANCH_MAP_ID,PARTNER_MAP_ID');
		if(empty($data)){
			$this->ajaxRets(1, '会员不存在！');
		}
		$res[] = array(
			'oId'		=>	$data['PARTNER_MAP_ID'],											//发卡点id
			'oName'		=> 	get_branch_name($data['BRANCH_MAP_ID'], $data['PARTNER_MAP_ID']),	//发卡点名称
		);
		$this->ajaxRets(0, '查询发卡点成功！', $res);
	}
	
	/*
	* 校验手机号是否已存在【17】【通了】
	**/
	public function checkvip() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mMobile'	=>	I('mMobile'),	//手机号
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mMobile'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}		
		$data = D($this->GVip)->findVip("VIP_MOBILE = '".$post['mMobile']."'");
		//组装数据
		$res[] = array(
			'isExist'	=> !empty($data) ? true : false,
		);
		$msg = !empty($data) ? '存在' : '不存在';
		$this->ajaxRets(0, '手机号'.$msg.'！', $res);
	}
	
	/*
	* 校验所属组织是否存在【19】
	**/
	public function findshopbranch() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'uId'		=>	I('uId'),	//商户id
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['uId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$shopdata = D($this->MShop)->findShop("SHOP_MAP_ID = '".$post['uId']."'", 'BRANCH_MAP_ID');
		if(empty($shopdata)){
			$this->ajaxRets(1, '商户不存在！');
		}
		$brandata = D($this->MBranch)->findBranch("BRANCH_MAP_ID = '".$shopdata['BRANCH_MAP_ID']."'", 'BRANCH_NAME,BRANCH_MAP_ID_P');
		$res[] = array(
			'oName'			=>	$brandata['BRANCH_NAME'],															//本级组织名称
			'parentOName'	=>	$brandata['BRANCH_MAP_ID_P'] ? get_branch_name($brandata['BRANCH_MAP_ID_P']) : '',	//父级组织名称		
		);
		$this->ajaxRets(0, '组织名称获取成功！', $res);
	}
	
	/*
	* 换卡【20】【通了】
	**/
	public function exchvip() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mId'		=>	I('mId'),		//会员ID
			'newCId'	=>	I('newCId'),	//新卡号
			'aCode'		=>	I('aCode'),		//新卡号校验码
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mId']) || empty($post['newCId']) || empty($post['aCode'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$data = D($this->GVip)->findVip("VIP_ID = '".$post['mId']."'");
		if(empty($data)){
			$this->ajaxRets(1, '会员不存在！');
		}
		//检测卡号、验证码	新卡
		$newvipcard = D($this->GVipcard)->findVipcard("CARD_NO = '".$post['newCId']."' and CARD_STATUS=1 and CARD_CHECK = '".$post['aCode']."'");
		if(empty($newvipcard)){
			$this->ajaxRets(1, "卡号、校验码不通过！");
		}
		
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$post['mId']."'");
		//判断卡套餐是否一样
		if ($info['VIP_CARD_FLAG'] != 2) {
			$vip_card_flag = 3;
		}else{
			$vip_card_flag = 2;
		}
		if ($vip_card_flag != $newvipcard['CARD_P_MAP_ID']) {
			$this->ajaxRets(1,'当前卡片类型与会员现有的卡片类型不同，不能换卡');
		}
		//判断会员是否属于杭州总部（如果是，那做为虚拟卡,换卡后以会员以卡归属为准）
		if ($info['PARTNER_MAP_ID'] == 4) {
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'		=>	$newvipcard['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=>	$newvipcard['PARTNER_MAP_ID'],
				'CARD_NO'			=>	$post['newCId'],
				'VIP_CARD_FLAG'		=>	$newvipcard['VIP_CARD_FLAG'],
				'UPDATE_TIME'		=>	date('YmdHis'),
			);
			$data['BRANCH_MAP_ID']  = $newvipcard['BRANCH_MAP_ID'];
			$data['PARTNER_MAP_ID'] = $newvipcard['PARTNER_MAP_ID'];
		}else{
			//组装数据
			$resdata = array(
				'CARD_NO'			=>	$post['newCId'],
				'VIP_CARD_FLAG'		=>	$newvipcard['VIP_CARD_FLAG'],
				'UPDATE_TIME'		=>	date('YmdHis'),
			);
		}

		$M = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		$M->startTrans();	//启用事务
		
		$res_vip = D($this->GVip)->updateVip("VIP_ID = '".$post['mId']."'", $resdata);
		if($res_vip['state'] != 0){
			$this->ajaxRets(1, $res_vip['msg']);
		}
		//绑定成功，改 vipcard
		$oldvipcard = D($this->GVipcard)->findVipcard("VIP_ID = '".$post['mId']."'");
		if(empty($oldvipcard)){
			$this->ajaxRets(1, '该会员还未绑卡！');
		}
		$oldvipcard['CARD_STATUS'] = 3;
		$res_tmp  = D($this->GVipcard)->addVipcard_tmp($oldvipcard);
		if($res_tmp['state'] != 0){
			$M->rollback();	//回滚
			$this->ajaxRets(1, '换卡失败！');
		}
		$res_del  = D($this->GVipcard)->delVipcard("VIP_ID = '".$post['mId']."'");
		if($res_del['state'] != 0){
			$M->rollback();	//回滚
			$this->ajaxRets(1, '换卡失败！');
		}
		
		$new_card = array(
			'BRANCH_MAP_ID'		=> $data['BRANCH_MAP_ID'],
			'BRANCH_MAP_ID1'	=> $newvipcard['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'	=> $data['PARTNER_MAP_ID'],
			'PARTNER_MAP_ID1'	=> $newvipcard['PARTNER_MAP_ID'],
			'VIP_ID'			=> $post['mId'],
			'ACTIVE_TIME'		=> date('YmdHis'),	//激活时间
			'UPDATE_TIME'		=> date('YmdHis'),	//变更时间
			'CARD_STATUS'		=> 0			
		);
		$res_new = D($this->GVipcard)->updateVipcard("CARD_NO = '".$post['newCId']."'", $new_card);
		if($res_new['state'] != 0){
			$M->rollback();	//回滚
			$this->ajaxRets(1, '换卡失败！');
		}

		//同步修改会员数据
		$url = VIP_PUSH_URL.'api/open/synchronize/member/modify';
		$vdata = array(
			'token' 	 	 => strtoupper(md5(strtoupper(md5($data['VIP_ID'].'2')))),	//(签名验证)
			'mId' 	 		 => $data['VIP_ID'],					//(会员ID)
			'mCId' 	 		 => $post['newCId'],					//(卡号)
			'mName' 		 => $data['VIP_NAME'],					//(会员姓名)
			'mIdentityType'	 => $data['VIP_IDNOTYPE'],				//(证件类型)
			'mIdentityId' 	 => $data['VIP_IDNO'],					//(证件号)
			'mBirthday'		 => date('Ymd',$data['VIP_BIRTHDAY']),	//(会员生日)
			'mMobile' 		 => $data['VIP_MOBILE'],				//(手机号码)
			'gender' 		 => $data['VIP_SEX'],					//(会员性别)
			'mCurrentCity'	 => getcity_name($data['VIP_CITY']),	//(所在城市)
			'mNativeAddress' => $data['VIP_ADDRESS'],				//(户口地址)
			'mEmail' 		 => $data['VIP_EMAIL'],					//(会员邮箱)
			'operateType'	 => '2'									//(操作类型)
		);
		Add_LOG(CONTROLLER_NAME, json_encode($vdata));
/*
		$resjson = httpPostForm($url,$vdata);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->code != '0') {
			$M->rollback();	//回滚
			$this->ajaxRets(1,'会员数据同步失败');
		}
*/
		$M->commit();		//成功
		
		$res[] = array(
			'oId'	=>	'',
		);		
		$this->ajaxRets(0, '换卡成功！', $res);
	}
	
	/*
	* 根据商户登录ID查询商户号和终端号【21】===
	**/
	public function findshopid() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mMobile'	=>	I('mMobile'),	//手机号
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mMobile'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$this->ajaxRets(0, '换卡成功！', $res);
	}
	
	/*
	* 通过mID更新会员积分【22】
	**/
	public function updatevipjf() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mId'			=>	I('mId'),			//卡号
			'cFreePoint'	=>	I('cFreePoint'),	//积分
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mId']) || empty($post['cFreePoint']) || $post['cFreePoint']<0){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$vipdata = D($this->GVip)->findVip("CARD_NO = '".$post['mId']."'", 'VIP_ID');
		if(empty($vipdata)){
			$this->ajaxRets(1, '卡号不存在！');
		}
		//组装数据
		$resdata = array(
			'ACCT_YLVBAL'	=>	$post['cFreePoint'],
			'MAC'			=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',		
		);
		$res_lap = D($this->GLap)->updateLap("ACCT_NO = '".setStrzero($vipdata['VIP_ID'], 9)."'", $resdata);
		if($res_lap['state'] != 0){
			$this->ajaxRets(1, '会员积分更新失败！');
		}
		$this->ajaxRets(0, '积分更新成功！');
	}
	
	/*
	* 通过区县服务中心oID查询商户【23】===
	**/
	public function getshoplist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mMobile'	=>	I('mMobile'),	//手机号
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mMobile'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$this->ajaxRets(0, '换卡成功！', $res);
	}
	
	/*
	* 通过登录名判断会员是否存在【25】===
	**/
	public function checknamevip() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mMobile'	=>	I('mMobile'),	//手机号
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mMobile'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$this->ajaxRets(0, '换卡成功！', $res);
	}
	
	/*
	* 更新会员信息【26】【通了】
	**/
	public function updatevip() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mId'		=>	I('mId'),		//会员id
			'email'		=>	I('email'),		//会员邮箱
			'location'	=>	I('location'),	//会员区域
			'birthday'	=>	I('birth'),		//会员生日
			'sex'		=>	I('gender'),	//会员性别
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$data = D($this->GVip)->findVip("VIP_ID = '".$post['mId']."'");
		if(empty($data)){
			$this->ajaxRets(1, '会员不存在！');
		}
		//组装数据
		$resdata = array(
			'VIP_CITY'			=>	$post['location'] ? $post['location'] : $data['VIP_CITY'],
			'VIP_EMAIL'			=>	$post['email'] ? $post['email'] : 'piccbill@jfb315.net',
			'UPDATE_TIME'		=>	date('YmdHis'),
		);

		//如果性别存在，那使用，否则根据身份证生成
		if ($sex != '') {
			$resdata['SEX'] = $sex;
		}
		//如果生日存在，那使用，否则根据身份证生成
		if (!empty($birthday)) {
			$resdata['BIRTHDAY'] = $birthday;
		}

		$M = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		$M->startTrans();	//启用事务

		$res_vip = D($this->GVip)->updateVip("VIP_ID = '".$post['mId']."'", $resdata);
		if($res_vip['state'] != 0){
			$this->ajaxRets(1, '会员信息更新失败！');
		}

		//同步新增会员数据
		$url = VIP_PUSH_URL.'api/open/synchronize/member/modify';
		$vdata = array(
			'token' 	 	 => strtoupper(md5(strtoupper(md5($data['VIP_ID'].'2')))),	//(签名验证)
			'mId' 	 		 => $data['VIP_ID'],					//(会员ID)
			'mCId' 	 		 => $data['CARD_NO'],					//(卡号)
			'mName' 		 => $data['VIP_NAME'],					//(会员姓名)
			'mIdentityType'	 => $data['VIP_IDNOTYPE'],				//(证件类型)
			'mIdentityId' 	 => $data['VIP_IDNO'],					//(证件号)
			'mBirthday'		 => $resdata['BIRTHDAY'] ? $resdata['BIRTHDAY'] : ($data['VIP_BIRTHDAY'] ? $data['VIP_BIRTHDAY'] : ''),	//(会员生日)
			'mMobile' 		 => $data['VIP_MOBILE'],				//(手机号码)
			'gender' 		 => ($resdata['VIP_SEX'] != '') ? $resdata['VIP_SEX'] : $data['VIP_SEX'],					//(会员性别)
			'mCurrentCity'	 => getcity_name($resdata['VIP_CITY']),	//(所在城市)
			'mNativeAddress' => $data['VIP_ADDRESS'],				//(户口地址)
			'mEmail' 		 => $resdata['VIP_EMAIL'],				//(会员邮箱)
			'operateType'	 => '2'									//(操作类型)
		);

		Add_LOG(CONTROLLER_NAME, json_encode($vdata));
/*
		$resjson = httpPostForm($url,$vdata);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->code != '0') {
			$M->rollback();	//回滚
			$this->ajaxRets(1,'会员数据同步失败');
		}
*/
		$M->commit();		//提交
		$this->ajaxRets(0, '更新成功！');
	}
	
	/*
	* 查询养老金排行【27】===
	**/
	public function getacctylorderlist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		
		$list = D($this->GVip)->getViplist('', 'v.VIP_NAME, l.ACCT_YLTBAL,l.ACCT_YLVBAL,l.ACCT_YWTBAL,l.ACCT_YWVBAL', '0,10', '(l.ACCT_YLTBAL + l.ACCT_YLVBAL + l.ACCT_YWTBAL + l.ACCT_YWVBAL) desc');
		if(empty($list)){
			$this->ajaxRets(1, '会员不存在！');
		}
		//组装数据
		foreach ($list as $key => $value) {
			$res[] = array(
				'name'		=>	$value['VIP_NAME'],
				'pension'	=>	setMoney(($value['ACCT_YLTBAL'] + $value['ACCT_YLVBAL']),2,2)
			);
		}
		$this->ajaxRets(0, '获取养老金排行成功！', $res);
	}
	
	/*
	* 商户申请【29】
	**/
	public function shopapply() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'APPLY_NAME'		=>	I('name'),			//申请者姓名
			'APPLY_SHOPNAME'	=>	I('shopName'),		//商户名称
			'APPLY_MOBILE'		=>	I('mMobile'),		//手机号
			'CITY_NO'			=>	I('location'),		//区域id(管理中心类型)
			'PARTNER_MAP_ID'	=>	I('hqId'),			//如果区域id查不到，则启用总部合作方id
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['APPLY_NAME']) || empty($post['APPLY_SHOPNAME']) || empty($post['APPLY_MOBILE']) || empty($post['CITY_NO']) || empty($post['PARTNER_MAP_ID'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$partlist = D($this->MPartner)->getPartnerlist("a.CITY_NO = '".$post['CITY_NO']."'",'a.BRANCH_MAP_ID,a.PARTNER_MAP_ID','');
		$partdata = $partlist[0];
		if(empty($partdata)){
			$partdata = D($this->MPartner)->findPartnerOne("PARTNER_MAP_ID = '".$post['PARTNER_MAP_ID']."'", 'BRANCH_MAP_ID,PARTNER_MAP_ID');
			if(empty($partdata)){
				$this->ajaxRets(1, '合作方不存在！');
			}
		}
		//组装数据
		$resdata = array(
			'BRANCH_MAP_ID'		=>	$partdata['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'	=>	$partdata['PARTNER_MAP_ID'],
			'APPLY_NAME'		=>	$post['APPLY_NAME'],			//用户名称
			'APPLY_SHOPNAME'	=>	$post['APPLY_SHOPNAME'],		//商户名称
			'APPLY_MOBILE'		=>	$post['APPLY_MOBILE'],			//手机号
			'CITY_NO'			=>	$post['CITY_NO'],				//区域ID
			'APPLY_STATUS'		=>	'0',							//状态 0未联系 1已联系
			'APPLY_PARTNERNAME'	=>	'',								//当前修改用户，所属组织名称
			'CREATR_TIME'		=>	date('YmdHis'),					//申请时间
			'RES'				=>	'',						//描述
		);
		$res_shopapply = D($this->MShopapply)->addShopapply($resdata);
		if($res_shopapply['state'] != 0){
			$this->ajaxRets(1, '商户申请添加失败！');
		}	
		$this->ajaxRets(0, '商户申请成功！');
	}
	
	/*
	* 根据区域名称查区域信息（只用于城市）【30】===
	**/
	public function getcitynamelist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'name'	=>	I('name'),	//地区名称
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['name'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$list = D($this->MCity)->getCityxlist("CITY_NAME like '%".$post['name']."%'", 'CITY_S_CODE as areaId,CITY_S_NAME as areaName, "CITY_S_CODE" as current_code');
		$this->ajaxRets(0, '获取区域列表成功！', $list);
	}

	/*
	* 会员交易查询  【32】【通了】
	**/
	public function getviptracelist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'startTime'	  =>	I('startTime'),		//开始时间
			'endTime'	  =>	I('endTime'),		//结束时间
			'mId'		  =>	I('mId'),			//会员ID
			'pageSize'	  =>	I('pageSize'),		//单页记录号
			'pCountIndex' =>	I('pCountIndex')	//页码
		);
		//验证
		if(empty($post['mId']) || empty($post['pageSize']) || empty($post['pCountIndex'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$where = "t.VIP_ID = '".$post['mId']."'";
		//交易日期	开始
		if($post['startTime']) {
			$where .= " and t.SYSTEM_DATE >= '".date('Ymd',strtotime($post['startTime']))."'";
		}
		//交易日期	结束
		if($post['endTime']) {
			$where .= " and t.SYSTEM_DATE <= '".date('Ymd',strtotime($post['endTime']))."'";
		}
		$limit = ($post['pCountIndex'] - 1)*$post['pageSize'].','.$post['pageSize'];
		$count = D($this->TTrace)->countTrace($where);
		$tlist = D($this->TTrace)->getTracelist($where, 't.TRACE_ID,t.CARD_NO,t.SHOP_NAMEAB,t.TRANS_NAME,t.SYSTEM_DATE,t.SYSTEM_TIME,t.TRANS_AMT', $limit);
		foreach($tlist as $val){
			//组装数据
			$list[] = array(
				'trace_id'		=>	$val['TRACE_ID'],								//主键
				'cId'			=>	$val['CARD_NO'],								//卡号
				'shopName'		=>	$val['SHOP_NAMEAB'],   							//商户名称
				'dealType'		=>	$val['TRANS_NAME'],						 		//交易类型
				'time'			=>	$val['SYSTEM_DATE'].' '.$val['SYSTEM_TIME'], 	//交易时间
				'mny'			=>	$val['TRANS_AMT'],								//金额			
			);
		}
		$res = array(
			'total'		=>	$count,
			'data'		=>	!empty($list) ? $list : array(),
		);	
		$this->ajaxRets(0, '获取会员交易数据成功！', $res);
	}

	/*
	* 商户交易查询  【33】
	**/
	public function getshoptracelist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'startTime'	  	=>	I('startTime'),		//开始时间
			'endTime'	  	=>	I('endTime'),		//结束时间
			'oId'		  	=>	I('oId'),			//商户编号
			'pageSize'	  	=>	I('pageSize'),		//单页记录号
			'pCountIndex' 	=>	I('pCountIndex'),	//页码
			'dealType' 		=>	I('dealType'),		//交易方式
		);
		//验证
		if(empty($post['oId']) || empty($post['pageSize']) || empty($post['pCountIndex'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$where = "t.SHOP_NO = '".$post['oId']."'";
		//交易日期	开始
		if($post['startTime']) {
			$where .= " and t.POS_DATE >= '".date('Ymd',strtotime($post['startTime']))."'";
		}
		//交易日期	结束
		if($post['endTime']) {
			$where .= " and t.POS_DATE <= '".date('Ymd',strtotime($post['endTime']))."'";
		}
		//交易方式
		if($post['dealType']) {
			$where .= " and t.TRANS_SUBID = '".$post['dealType']."'";
		}
		
		$Model = M('', DB_PREFIX_TRA, DB_DSN_TRA);
		//统计总条数
		$count_sql = "select count(t.TRACE_ID) as TOTAL FROM T_JFBLS j LEFT JOIN t_trace t ON(t.SYSTEM_REF = j.SYSTEM_REF) WHERE ".$where." AND t.TRACE_VOIDFLAG = 0 AND t.TRACE_REFUNDFLAG = 0 AND t.TRACE_REVERFLAG = 0";
		$num = $Model->query($count_sql);
		$count = $num[0]['TOTAL'];
		$limit = ' limit '.($post['pCountIndex'] - 1)*$post['pageSize'].','.$post['pageSize'];
		//获取列表数据
		$list_sql = "select t.*,DATE_FORMAT(t.POS_DATE,'%Y-%m-%d') AS POS_DATE_FORMAT, DATE_FORMAT(CONCAT(t.POS_DATE, LPAD(t.POS_TIME,6,'0')),'%H:%i:%s') AS POS_TIME_FORMAT FROM T_JFBLS j LEFT JOIN t_trace t ON(t.SYSTEM_REF = j.SYSTEM_REF) WHERE ".$where." AND t.TRACE_VOIDFLAG = 0 AND t.TRACE_REFUNDFLAG = 0 AND t.TRACE_REVERFLAG = 0 ORDER BY t.SYSTEM_DATE desc, t.SYSTEM_TIME desc".$limit;
		$tlist = $Model->query($list_sql);
		//统计
		$total_sql = "select sum(t.TRANS_AMT) as AMT FROM T_JFBLS j LEFT JOIN t_trace t ON(t.SYSTEM_REF = j.SYSTEM_REF) 
		WHERE ".$where." AND t.TRACE_VOIDFLAG = 0 AND t.TRACE_REFUNDFLAG = 0 AND t.TRACE_REVERFLAG = 0";

		$total  = $Model->query($total_sql);
		$data['AMT'] = $total[0]['AMT'];

		foreach($tlist as $val){
			//组装数据
			$list[] = array(
				'trace_id'		=>	$val['TRACE_ID'],								//主键
				'cId'			=>	$val['VIP_CARDNO'],								//卡号
				'shopName'		=>	$val['SHOP_NAMEAB'],   							//商户名称
				'dealType'		=>	$val['TRANS_SUBID'],							//交易类型
				'time'			=>	$val['POS_DATE_FORMAT'].' '.$val['POS_TIME_FORMAT'], 			//交易时间
				'mny'			=>	$val['TRANS_AMT'],								//金额			
			);
		}
		//$data  = D($this->TTrace)->findmoreTrace($where, 'sum(t.TRANS_AMT) as amt');
		$res = array(
			'total'		=>	ceil($count/$post['pageSize']),		//总页数
			'totalCount'=>	$count,								//总记录数
			'sum'		=>	$data['AMT'],						//总金额
			'data'		=>	!empty($list) ? $list : array()		//记录内容
		);	
		$this->ajaxRets(0, '获取商户交易数据成功！', $res);
	}
	
	/*
	* 卡套餐查询【34】【通了】
	**/
	public function findvipcard() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'cId' => I('cId')			//卡号
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['cId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$vipcard_res = D($this->GVipcard)->findVipcard('CARD_NO = "'.$post['cId'].'"','CARD_P_MAP_ID');
		if(empty($vipcard_res)) {
			$this->ajaxRets(1, '未查到相关卡片数据！');
		}
		//获取卡类型
		$card_res = D($this->MCproduct)->findCproduct_one('CARD_P_MAP_ID = '.$vipcard_res['CARD_P_MAP_ID'],'CARD_NAME');
		if(empty($card_res)) {
			$this->ajaxRets(1, '未查到相关卡片类型数据！');
		}
		$res = array(
			'cTypeName' 	=> $card_res['CARD_NAME'],
		);
		$this->ajaxRets(0, '获取卡套餐成功！', $res);
	}

	/*
	* 通过UID查询组织名称【36】
	**/
	public function findvipbranch() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'uId' => I('uId') 		//地区编号
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['uId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$vip_res = D($this->GVip)->findVip('VIP_ID = "'.$post['uId'].'"','BRANCH_MAP_ID');
		if(empty($vip_res)) {
			$this->ajaxRets(1, '未查到相关会员数据！');
		}
		//组装数据
		$res[] = array(
			'orgName' => get_branch_name($vip_res['BRANCH_MAP_ID'])
		);
		$this->ajaxRets(0, '获取组织名称成功！', $res);
	}

	/*
	* 根据OID获取交易详情  【37】
	**/
	public function gettracedetaillist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'trace_id'	  =>	I('trace_id'),		//流水id
		);
		//验证
		if(empty($post['trace_id'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$where = "TRACE_ID = '".$post['trace_id']."'";
		$data  = D($this->TTrace)->findTrace($where, 'SHOP_NO,SHOP_NAMEAB,VIP_CARDNO,TRANS_SUBID,SYSTEM_DATE,SYSTEM_TIME,TRANS_AMT');
		if(empty($data)) {
			$this->ajaxRets(1, '流水不存在');
		}
		//组装数据
		$res = array(
			'merchant_id'		=>	$data['SHOP_NO'],									//商户编号
			'merchant_name'		=>	$data['SHOP_NAMEAB'],								//商户名
			'member_id'			=>	$data['VIP_CARDNO'],								//会员卡号
			'payment_method'	=>	$data['TRANS_SUBID'],								//支付方式（交易类型）
			'payment_amount'	=>	$data['TRANS_AMT'],									//收款金额
			'payment_time'		=>	$data['SYSTEM_DATE'].' '.$data['SYSTEM_TIME'],		//收款日期
		);
		$this->ajaxRets(0, '获取交易详情成功！', $res);
	}

	/*
	* 根据OID查询7日收益  【38】===
	**/
	public function getshopsevlist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'oId'	  =>	I('oId'),		//组织id
		);
		//验证
		if(empty($post['oId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}

		$p_where = 'a.PARTNER_MAP_ID = "'.$post['oId'].'"';
		$p_res = D('MPartner')->findPartner($p_where, 'a.BRANCH_MAP_ID,a.PARTNER_MAP_ID,a.PARTNER_LEVEL');
		if (empty($p_res)) {
			$this->ajaxRets(1, '组织id错误，未查到相关数据！');
		}
		$user_level 	= $p_res['PARTNER_LEVEL']+1;
		$branch_map_id 	= $p_res['BRANCH_MAP_ID'];
		$partner_map_id = $p_res['PARTNER_MAP_ID'];
			
		$where = "t.TRACE_RETCODE='00' and t.TRACE_REVERFLAG = 0 and t.TRANS_AMT>0 and j.JFB_FEE>0 and t.TRACE_VOIDFLAG = 0 and t.TRACE_REFUNDFLAG = 0";

		//消费条件
		$where_1 = " and t.TRANS_SUBID IN (43, 44, 39, 31, 32, 33, 38)";
		//退货条件
		$where_2 = " and t.TRANS_SUBID IN (731, 733, 738, 743, 533, 531)";

		//统计日期条件
		//昨日条件
		$yesterday_where = " and t.SYSTEM_DATE = '".date('Ymd',strtotime('-1 day'))."'";
		//7天
		$seven_where = " and t.SYSTEM_DATE >= '".date('Ymd',strtotime('-6 day'))."' and t.SYSTEM_DATE <= '".date('Ymd')."'";
		//按日期 分组
		$groupdate = " group by t.SYSTEM_DATE";
		if($branch_map_id){
			$where .= " and (j.ISS_BRANCH_MAP_ID2  = '".$branch_map_id."' OR j.ACQ_BRANCH_MAP_ID2  = '".$branch_map_id."')";
		}
		if($partner_map_id){
			$where .= " and (j.ISS_PARTNER_MAP_ID1 = '".$partner_map_id."' OR 
							j.ISS_PARTNER_MAP_ID2 = '".$partner_map_id."' OR 
							j.ISS_PARTNER_MAP_ID3 = '".$partner_map_id."' OR 
							j.VIP_PARTNER_MAP_ID1 = '".$partner_map_id."' OR 
							j.VIP_PARTNER_MAP_ID2 = '".$partner_map_id."' OR 
							j.ACQ_PARTNER_MAP_ID1 = '".$partner_map_id."' OR 
							j.ACQ_PARTNER_MAP_ID2 = '".$partner_map_id."' OR 
							j.PARTNER_MAP_ID3A    = '".$partner_map_id."' OR 
							j.PARTNER_MAP_ID3B    = '".$partner_map_id."' )";
		}
		//计算平台分润
		$result = array();
		switch($user_level){
			case 0:
				$field = "SUM(CASE WHEN k.BRANCH_FEE1 > 0 THEN k.BRANCH_FEE1 ELSE 0 END) AS K_BRANCH_FEE1,
						SUM(CASE WHEN j.BRANCH_FEE1 > 0 THEN j.BRANCH_FEE1 ELSE 0 END) AS J_BRANCH_FEE1,
						SUM(CASE WHEN j.CADF_FEE1 > 0 THEN j.CADF_FEE1 ELSE 0 END) AS CADF_FEE1,
						SUM(CASE WHEN j.NTAX_FEE1 > 0 THEN j.NTAX_FEE1 ELSE 0 END) AS NTAX_FEE1,
						SUM(CASE WHEN j.EATF_FEE1 > 0 THEN j.EATF_FEE1 ELSE 0 END) AS EATF_FEE1";
				//【总部】总消费收益统计
				$total_1 = $this->root_statistics($field,$where.$where_1);
				//【总部】总退货收益统计
			//	$total_2 = $this->root_statistics($field,$where.$where_2);
				$result['profitTotal'] = setMoney($total_1['total'],2,2);
				$result['profitCardTotal'] = setMoney($total_1['cafay'],2,2);

				//【总部】昨日收益统计
				$y_total_1 = $this->root_statistics($field,$where.$where_1.$yesterday_where);
				//$y_total_2 = $this->root_statistics($field,$where.$where_2.$yesterday_where);
				$result['ondProfitTotal']   = setMoney($y_total_1['total'],2,2);
				$result['ondProfitCard']   = setMoney($y_total_1['cafay'],2,2);

				//【总部】7天收益统计
				$seven_total_1 = $this->root_statistics($field,$where.$where_1.$seven_where);
				//$seven_total_2 = $this->root_statistics($field,$where.$where_2.$seven_where);
				$result['sedProfitTotalSum'] = setMoney($seven_total_1['total'],2,2);
				$result['sedProfitCardSum'] = setMoney($seven_total_1['cafay'],2,2);

				//【总部】7天收益每日统计
				$eday_total_1 = $this->root_statistics($field.',t.SYSTEM_DATE',$where.$where_1.$seven_where, $groupdate);
				//$eday_total_2 = $this->root_statistics($field.',t.SYSTEM_DATE',$where.$where_2.$seven_where, $groupdate);
				$dates = array();
				for ($i=0; $i < 7; $i++) { 
					$dates[$i]['value'] = setMoney($eday_total_1[$i]['value'],2,2);
					$dates[$i]['date']  = $eday_total_1[$i]['date'];
				}
				$result['dates'] = $dates;
				break;
			case 1:		//分公司
				$field = "SUM(CASE WHEN k.BRANCH_MAP_ID2 = '".$branch_map_id."' THEN k.BRANCH_FEE2 ELSE 0 END) AS BRANCH_FEE2,
						SUM(CASE WHEN j.ACQ_BRANCH_MAP_ID2 = '".$branch_map_id."' THEN j.ACQ_BRANCH_FEE2 ELSE 0 END) AS ACQ_BRANCH_FEE2,
						SUM(CASE WHEN j.ISS_BRANCH_MAP_ID2 = '".$branch_map_id."' THEN j.ISS_BRANCH_FEE2 ELSE 0 END) AS ISS_BRANCH_FEE2";
				
				//【分公司】总消费收益统计
				$total_1 = $this->branch_statistics($field,$where.$where_1);
				//【分公司】总退货收益统计
				//$total_2 = $this->branch_statistics($field,$where.$where_2);
				$result['profitTotal'] = setMoney($total_1['total'],2,2);
				$result['profitCardTotal'] = setMoney($total_1['cafay'],2,2);

				//【分公司】昨日收益统计
				$y_total_1 = $this->branch_statistics($field,$where.$where_1.$yesterday_where);
				//$y_total_2 = $this->branch_statistics($field,$where.$where_2.$yesterday_where);
				$result['ondProfitTotal']   = setMoney($y_total_1['total'],2,2);
				$result['ondProfitCard']   = setMoney($y_total_1['cafay'],2,2);

				//【分公司】7天收益统计
				$seven_total_1 = $this->branch_statistics($field,$where.$where_1.$seven_where);
			//	$seven_total_2 = $this->branch_statistics($field,$where.$where_2.$seven_where);
				$result['sedProfitTotalSum'] = setMoney($seven_total_1['total'],2,2);
				$result['sedProfitCardSum'] = setMoney($seven_total_1['cafay'],2,2);

				//【分公司】7天收益每日统计
				$eday_total_1 = $this->branch_statistics($field.',t.SYSTEM_DATE',$where.$where_1.$seven_where, $groupdate);
			//	$eday_total_2 = $this->branch_statistics($field.',t.SYSTEM_DATE',$where.$where_2.$seven_where, $groupdate);
				$dates = array();
				for ($i=0; $i < 7; $i++) { 
					$dates[$i]['value'] = setMoney($eday_total_1[$i]['value'],2,2);
					$dates[$i]['date']  = $eday_total_1[$i]['date'];
				}
				$result['dates'] = $dates;
				break;
			case 2:		//地市子公司
				$field = "SUM(CASE WHEN k.PARTNER_MAP_ID1 = '".$partner_map_id."' THEN k.PARTNER_FEE1 ELSE 0 END) AS PARTNER_FEE1,
						SUM(CASE WHEN j.ACQ_PARTNER_MAP_ID1 = '".$partner_map_id."' THEN j.ACQ_PARTNER_FEE1 ELSE 0 END) AS ACQ_PARTNER_FEE1,
						SUM(CASE WHEN j.ISS_PARTNER_MAP_ID1 = '".$partner_map_id."' THEN j.ISS_PARTNER_FEE1+j.ISS_LCF_FEE1+j.ISS_LCA_FEE1 ELSE 0 END) AS ISS_PARTNER_FEE1";
				//【地市子公司】总消费收益统计
				$total_1 = $this->partner_statistics_1($field,$where.$where_1);
				//【地市子公司】总退货收益统计
			//	$total_2 = $this->partner_statistics_1($field,$where.$where_2);
				$result['profitTotal'] = setMoney($total_1['total'],2,2);
				$result['profitCardTotal'] = setMoney($total_1['cafay'],2,2);

				//【地市子公司】昨日收益统计
				$y_total_1 = $this->partner_statistics_1($field,$where.$where_1.$yesterday_where);
			//	$y_total_2 = $this->partner_statistics_1($field,$where.$where_2.$yesterday_where);
				$result['ondProfitTotal']   = setMoney($y_total_1['total'],2,2);
				$result['ondProfitCard']   = setMoney($y_total_1['cafay'],2,2);

				//【地市子公司】7天收益统计
				$seven_total_1 = $this->partner_statistics_1($field,$where.$where_1.$seven_where);
			//	$seven_total_2 = $this->partner_statistics_1($field,$where.$where_2.$seven_where);
				$result['sedProfitTotalSum'] = setMoney($seven_total_1['total'],2,2);
				$result['sedProfitCardSum'] = setMoney($seven_total_1['cafay'],2,2);

				//【地市子公司】7天收益每日统计
				$eday_total_1 = $this->partner_statistics_1($field.',t.SYSTEM_DATE',$where.$where_1.$seven_where, $groupdate);
			//	$eday_total_2 = $this->partner_statistics_1($field.',t.SYSTEM_DATE',$where.$where_2.$seven_where, $groupdate);
				$dates = array();
				for ($i=0; $i < 7; $i++) { 
					$dates[$i]['value'] = setMoney($eday_total_1[$i]['value'],2,2);
					$dates[$i]['date']  = $eday_total_1[$i]['date'];
				}
				$result['dates'] = $dates;
				break;
			case 3:		//服务中心
				$field = "SUM(CASE WHEN k.PARTNER_MAP_ID2 = '".$partner_map_id."' THEN k.PARTNER_FEE2 ELSE 0 END) AS K_PARTNER_FEE2,
						SUM(CASE WHEN k.PARTNER_MAP_ID3 = '".$partner_map_id."' THEN k.PARTNER_FEE3 ELSE 0 END) AS K_PARTNER_FEE3,
						SUM(CASE WHEN j.ACQ_PARTNER_MAP_ID2 = '".$partner_map_id."' THEN j.ACQ_PARTNER_FEE2 ELSE 0 END) AS J_ACQ_PARTNER_FEE2,
						SUM(CASE WHEN j.ISS_PARTNER_MAP_ID2 = '".$partner_map_id."' THEN j.ISS_PARTNER_FEE2 ELSE 0 END) AS J_ISS_PARTNER_FEE2,
						SUM(CASE WHEN j.ISS_PARTNER_MAP_ID3 = '".$partner_map_id."' THEN j.ISS_PARTNER_FEE3 ELSE 0 END) AS J_ISS_PARTNER_FEE3,
						SUM(CASE WHEN j.PARTNER_MAP_ID3A = '".$partner_map_id."' THEN j.PARTNER_FEE3A ELSE 0 END) AS J_PARTNER_FEE3A,
						SUM(CASE WHEN j.PARTNER_MAP_ID3B = '".$partner_map_id."' THEN j.PARTNER_FEE3B ELSE 0 END) AS J_PARTNER_FEE3B";
				
				//【服务中心】总消费收益统计
				$total_1 = $this->partner_statistics_2($field,$where.$where_1);
				//【服务中心】总退货收益统计
			//	$total_2 = $this->partner_statistics_2($field,$where.$where_2);
				$result['profitTotal'] = setMoney($total_1['total'],2,2);
				$result['profitCardTotal'] = setMoney($total_1['cafay'],2,2);

				//【服务中心】昨日收益统计
				$y_total_1 = $this->partner_statistics_2($field,$where.$where_1.$yesterday_where);
			//	$y_total_2 = $this->partner_statistics_2($field,$where.$where_2.$yesterday_where);
				$result['ondProfitTotal']   = setMoney($y_total_1['total'],2,2);
				$result['ondProfitCard']   = setMoney($y_total_1['cafay'],2,2);

				//【服务中心】7天收益统计
				$seven_total_1 = $this->partner_statistics_2($field,$where.$where_1.$seven_where);
			//	$seven_total_2 = $this->partner_statistics_2($field,$where.$where_2.$seven_where);
				$result['sedProfitTotalSum'] = setMoney($seven_total_1['total'],2,2);
				$result['sedProfitCardSum'] = setMoney($seven_total_1['cafay'],2,2);

				//【服务中心】7天收益每日统计
				$eday_total_1 = $this->partner_statistics_2($field.',t.SYSTEM_DATE',$where.$where_1.$seven_where, $groupdate);
			//	$eday_total_2 = $this->partner_statistics_2($field.',t.SYSTEM_DATE',$where.$where_2.$seven_where, $groupdate);
				$dates = array();
				for ($i=0; $i < 7; $i++) { 
					$dates[$i]['value'] = setMoney($eday_total_1[$i]['value'],2,2);
					$dates[$i]['date']  = $eday_total_1[$i]['date'];
				}
				$result['dates'] = $dates;
				break;
			case 4:		//发卡点
				$field = "SUM(CASE WHEN k.PARTNER_MAP_ID3 = '".$partner_map_id."' THEN k.PARTNER_FEE3 ELSE 0 END) AS PARTNER_FEE3,
						SUM(CASE WHEN j.PARTNER_MAP_ID3A = '".$partner_map_id."' THEN j.PARTNER_FEE3A ELSE 0 END) AS PARTNER_FEE3A,
						SUM(CASE WHEN j.PARTNER_MAP_ID3B = '".$partner_map_id."' THEN j.PARTNER_FEE3B ELSE 0 END) AS PARTNER_FEE3B,
						SUM(CASE WHEN j.ISS_PARTNER_MAP_ID3 = '".$partner_map_id."' THEN j.ISS_PARTNER_FEE3 ELSE 0 END) AS ISS_PARTNER_FEE3";
				
				//【发卡点】总消费收益统计
				$total_1 = $this->partner_statistics_3($field,$where.$where_1);
				//【发卡点】总退货收益统计
			//	$total_2 = $this->partner_statistics_3($field,$where.$where_2);
				$result['profitTotal'] = setMoney($total_1['total'],2,2);
				$result['profitCardTotal'] = setMoney($total_1['cafay'],2,2);

				//【发卡点】昨日收益统计
				$y_total_1 = $this->partner_statistics_3($field,$where.$where_1.$yesterday_where);
			//	$y_total_2 = $this->partner_statistics_3($field,$where.$where_2.$yesterday_where);
				$result['ondProfitTotal']   = setMoney($y_total_1['total'],2,2);
				$result['ondProfitCard']   = setMoney($y_total_1['cafay'],2,2);

				//【发卡点】7天收益统计
				$seven_total_1 = $this->partner_statistics_3($field,$where.$where_1.$seven_where);
			//	$seven_total_2 = $this->partner_statistics_3($field,$where.$where_2.$seven_where);
				$result['sedProfitTotalSum'] = setMoney($seven_total_1['total'],2,2);
				$result['sedProfitCardSum'] = setMoney($seven_total_1['cafay'],2,2);

				//【发卡点】7天收益每日统计
				$eday_total_1 = $this->partner_statistics_3($field.',t.SYSTEM_DATE',$where.$where_1.$seven_where, $groupdate);
			//	$eday_total_2 = $this->partner_statistics_3($field.',t.SYSTEM_DATE',$where.$where_2.$seven_where, $groupdate);
				$dates = array();
				for ($i=0; $i < 7; $i++) { 
					$dates[$i]['value'] = setMoney($eday_total_1[$i]['value'],2,2);
					$dates[$i]['date']  = $eday_total_1[$i]['date'];
				}
				$result['dates'] = $dates;
				break;
		}
		$this->ajaxRets(0, '获取统计信息成功！', $result);
	}

	/*
	* 根据OID和交易金额查询养老金  【40】===
	**/
	public function seatotally() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'oId'	  =>	I('oId'),		//商户id
			'amount'  =>	I('amount')		//交易金额
		);
		//验证
		if(empty($post['oId']) || empty($post['amount'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}

		//获取商户结算信息
		$smdr_info  = D($this->MSmdr)->findSmdr("SHOP_MAP_ID='".$post['oId']."' and SHOP_STATUS = 0 and PAY_TYPE = 5", 'PAY_TYPE, JFB_PER_FEE, JFB_FIX_FEE, PER_FEE, FIX_FEE');
		
		//手续费
		$poundage = 0;
		if($smdr_info['JFB_FIX_FEE'] > 0) {
			$poundage = $smdr_info['JFB_FIX_FEE'];
		}else{
			$poundage = $post['amount'] * ($smdr_info['JFB_PER_FEE']/10000);
		}
		//计算消费者的所得金额
		$scfg_info = D($this->MScfg)->findScfg("SHOP_MAP_ID='".$post['oId']."'");
		if ($scfg_info['RAKE_FLAG'] ==1 ) {
			$per_rake = $scfg_info['CON_PER_RAKE']/10000;
		}else{
			$feecfg_res = D($this->MFeecfg)->findFeecfg('CFG_FLAG = 1','CON_PER');
			$per_rake = $feecfg_res['CON_PER']/10000;
		}
		$res = $poundage * $per_rake;
		$res = array(
			'pointFee' => ($poundage * $per_rake)
		);
		$this->ajaxRets(0, '获取成功！', $res);
	}

	/*
	* 根据OID查询现金POS机  【41】
	**/
	public function parttotally() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'oId'	  =>	I('oId'),		//商户id
		);
		//验证
		if(empty($post['oId'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		//获取商户pos
		$poslist  = D($this->MPos)->getPoslist("p.SHOP_MAP_ID ='".$post['oId']."' and POS_STATUS = 0", 'p.SHOP_NO as termId,p.POS_NO as shopId');	
		$res = array(
			'data' =>	$poslist,
		);
		$this->ajaxRets(0, '获取POS机成功！', $res);
	}

	/*
	* 组织信息查询  【43】
	**/
	public function getpartrelalist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$partlist = D($this->MPartner)->getPartnerlist('','a.PARTNER_MAP_ID as oId,a.PARTNER_MAP_ID_P as pId,a.PARTNER_NAME as oName','');
		foreach($partlist as $key=>$val){
			$partlist[$key]['oParentRelation'] = getParentRelation($val['oId']);
		}
		$res = array(
			'list'	=>		$partlist,
		);
		$this->ajaxRets(0, '获取交易详情成功！', $res);
	}

	/*
	* 商家版注册接口  【44】
	**/
	public function addauthvip() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'CARD_NO'	  	=>	I('cardId'),		//卡号
			'CARD_CHECK'	=>	I('code'),			//6位校验码
			'VIP_NAME'		=>	I('memberName'),	//会员名
			'VIP_MOBILE'  	=>	I('phoneNo'),		//手机号
			'VIP_IDNOTYPE'	=>	I('cardType'),		//证件类型
			'VIP_IDNO'		=>	I('identityId'),	//身份证
			'SEX'			=>	I('gender'),		//性别
			'BIRTHDAY'		=>	I('birth'),			//生日
			'VIP_EMAIL'	  	=>	I('email'),			//邮箱
			'PARTNER_MAP_ID'=>	I('oId'),			//合作伙伴ID
			'mRemark'		=>	I('remark'),		//注册通道
			'mPassword'		=>	I('mPassword')		//注册通道
		);
		//验证
		if(empty($post['CARD_NO']) || empty($post['CARD_CHECK']) || empty($post['VIP_NAME']) || empty($post['VIP_MOBILE']) || $post['VIP_IDNOTYPE']=='' || empty($post['VIP_IDNO'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		//检测证件号
		$carddata = D($this->GVip)->findVip("VIP_IDNOTYPE = '".$post['VIP_IDNOTYPE']."' and VIP_IDNO = '".$post['VIP_IDNO']."'");
		if(!empty($carddata)){
			$this->ajaxRets(1, '该证件号已注册！');
		}
		$city_no = substr($post['VIP_IDNO'], 0, 6);
		//如果性别存在，那使用，否则根据身份证生成
		if ($post['SEX'] != '') {
			$sex = $post['SEX'];
		}else{
			$sex = substr($post['VIP_IDNO'], 16, 1)%2 == 0 ? '0' : '1';
		}
		//如果生日存在，那使用，否则根据身份证生成
		if (!empty($post['BIRTHDAY'])) {
			$birthday = $post['BIRTHDAY'];
		}else{
			$Y		  = substr($post['VIP_IDNO'], 6, 4);
			$m		  = substr($post['VIP_IDNO'], 10, 2);
			$d		  = substr($post['VIP_IDNO'], 12, 2);
			$birthday = $Y.$m.$d;
		}
		//检测手机号
		$mobiledata = D($this->GVip)->findVip("VIP_MOBILE = '".$post['VIP_MOBILE']."'");
		if(!empty($mobiledata)){
			$this->ajaxRets(1, "该手机号已注册！");
		}
		//卡号信息，检测卡号、验证码，
		if (!empty($post['PARTNER_MAP_ID'])) {
			$vip_where = ' and PARTNER_MAP_ID = "'.$post['PARTNER_MAP_ID'].'"';
		}
		$vipcarddata = D($this->GVipcard)->findVipcard("CARD_NO = '".$post['CARD_NO']."' and CARD_STATUS=1 and CARD_CHECK = '".$post['CARD_CHECK']."'".$vip_where, 'BRANCH_MAP_ID,PARTNER_MAP_ID,CARD_P_MAP_ID');
		if(empty($vipcarddata)){
			$this->ajaxRets(1, "卡号、校验码不匹配，或卡已经被绑定过，或当前合作方库存中没有找到对应的卡号！");
		}
		if(empty($vipcarddata['CARD_P_MAP_ID'])){
			$this->ajaxRets(1, "卡号套餐不能为空！");
		}

		//判断会员是否属于杭州总部（如果是，那做为虚拟卡类型）
		if (($post['PARTNER_MAP_ID'] == 4) && ($vipcarddata['CARD_P_MAP_ID'] != 3)) {
			$this->ajaxRets(1,'杭州创业合伙只能绑定预免卡');
		}
		
		//组装数据
		$vip = array(
			'BRANCH_MAP_ID'		=>	$vipcarddata['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'	=>	$vipcarddata['PARTNER_MAP_ID'],
			'VIP_SOURCE'		=>	'3',			//app
			'CARD_NO'			=>	$post['CARD_NO'],
			'VIP_NAME'			=>	$post['VIP_NAME'],
			'VIP_STATUS'		=>	'0',
			'VIP_CARD_FLAG'		=>	$vipcarddata['CARD_P_MAP_ID'],
			'VIP_AUTH_FLAG'		=>	'1',			//认证
			'VIP_PARTNER_FLAG'	=>	'0',
			'VIP_IDNOTYPE'		=>	$post['VIP_IDNOTYPE'],
			'VIP_IDNO'			=>	$post['VIP_IDNO'],
			'VIP_MOBILE'		=>	$post['VIP_MOBILE'],
			'VIP_CITY'			=>	$city_no ? $city_no : '',
			'VIP_ADDRESS'		=>	'',
			'VIP_SEX'			=>	$sex,
			'VIP_EMAIL'			=>	$post['VIP_EMAIL'] ? $post['VIP_EMAIL'] : 'piccbill@jfb315.net',
			'VIP_BIRTHDAY'		=>	$birthday,
			'VIP_DONATE'		=>	'0',
			'VIP_DONATE_PER'	=>	'',
			'VIP_ID_M'			=>	'',
			'VIP_PIN'			=>	strtoupper(md5(strtoupper(md5('888888')))),
			'VIP_PINTIME'		=>	'0',
			'VIP_PINLIMIT'		=>	'5',
			'CREATE_TIME'		=>	date('YmdHis'),
			'ACTIVE_TIME'		=>	date('YmdHis'),
			'UPDATE_TIME'		=>	date('YmdHis'),
			'RES'				=>	'',
			'ID_PHOTO'			=>	'',
		);
		$M = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		$M->startTrans();	//启用事务
		
		$res = D($this->GVip)->addVip($vip);
		if($res['state'] != 0){
			$this->ajaxRets(1, '会员添加失败！');
		}
		
		//将会员归属覆盖卡归属
		$vcarddata = array(
			'BRANCH_MAP_ID1'	=>	$vipcarddata['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID1'	=>	$vipcarddata['PARTNER_MAP_ID'],
			'BRANCH_MAP_ID'		=>	$vipcarddata['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'	=>	$vipcarddata['PARTNER_MAP_ID'],				
			'VIP_ID'			=>	$res['VIP_ID'],	//vip表vip_id 覆盖vipcard表 vip_id
			'ACTIVE_TIME'		=> date('YmdHis'),	//激活时间
			'UPDATE_TIME'		=> date('YmdHis'),	//变更时间
			'CARD_STATUS'		=>	0
		);
		$res_vip = D($this->GVipcard)->updateVipcard("CARD_NO = '".$post['CARD_NO']."'", $vcarddata);
		if($res_vip['state'] != 0){
			$M->rollback();	//回滚
			$this->ajaxRets(1, '卡产品修改失败！');
		}
		
		//如果是预免卡3，只插入lap表，如果是收费卡2，插入lap表,还要插入reg表
		$fee = D($this->MFeecfg)->findFeecfg('CFG_FLAG = 3', 'CARD_OPENFEE');
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
			'ACCT_DIVBAL'	=>	$vipcarddata['CARD_P_MAP_ID']==3 ? $fee['CARD_OPENFEE'] : '0',
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
		if($vipcarddata['CARD_P_MAP_ID'] == 2){
			//reg
			$fee = D($this->MFeecfg)->findFeecfg('CFG_FLAG = 2', 'TRAFICC_FEE');
			$reg = array(
				'REG_TYPE'	=>	'101',
				'REG_INDEX'	=>	setStrzero($res['VIP_ID'], 9),
				'REG_DESC'	=>	$post['VIP_NAME'],
				'REG_AMT'	=>	$fee['TRAFICC_FEE'],
				'MARK_FLAG'	=>	1,
				'MARK_DATE'	=>	date('Ymd'),
				'YL_BAL'	=>	'0',
				'YW_BAL'	=>	'0',
				'DIV_BAL'	=>	'0',
				'AMT1'		=>	'0',
				'AMT2'		=>	'0',
			);
			D($this->MReg)->addReg($reg);			
		}
		$M->commit();		//成功
		//同步新增会员数据
		$url = VIP_PUSH_URL.'api/open/synchronize/member/register';
		$data = array(
			'token' 	 	 => strtoupper(md5(strtoupper(md5($res['VIP_ID'].'1')))),	//(签名验证)
			'mId' 	 		 => $res['VIP_ID'],					//(会员ID)
			'mCId' 	 		 => $vip['CARD_NO'],				//(卡号)
			'mName' 		 => $vip['VIP_NAME'],				//(会员姓名)
			'mIdentityType'	 => $vip['VIP_IDNOTYPE'],			//(证件类型)
			'mIdentityId' 	 => $vip['VIP_IDNO'],				//(证件号)
			'mBirthday'		 => $vip['VIP_BIRTHDAY'],			//(会员生日)
			'mMobile' 		 => $vip['VIP_MOBILE'],				//(手机号码)
			'gender' 		 => $vip['VIP_SEX'],				//(会员性别)
			'mCurrentCity'	 => getcity_name($vip['VIP_CITY']),	//(所在城市)
			'mNativeAddress' => $vip['VIP_ADDRESS'],			//(户口地址)
			'mEmail' 		 => $vip['VIP_EMAIL'],				//(会员邮箱)
			'operateType'	 => '1',							//(操作类型)
			'migrateType'	 => '2',							//(迁移区分)
			'mRemark'	 	 => $post['mRemark'],
			'mPassword'	 	 => $post['mPassword']
		);
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode($data));
/*
		$resjson = httpPostForm($url,$data);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->code != '0') {
			Add_LOG(CONTROLLER_NAME, '【商家版】会员数据同步失败');
		}
*/

		//发短信
		if($vip['VIP_MOBILE']){
			//短信模板
			$model_arr = setSmsmodel(2);
			//短信流水
			$smsls = array(
				'BRANCH_MAP_ID'		=>	$vip['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=>	$vip['PARTNER_MAP_ID'],
				'SMS_MODEL_TYPE'	=>	'2',
				'VIP_FLAG'			=>	$vip['VIP_CARD_FLAG'],
				'VIP_ID'			=>	$res['VIP_ID'],
				'VIP_CARDNO'		=>	$vip['CARD_NO'],
				'SMS_RECV_MOB'		=>	$vip['VIP_MOBILE'],
				'SMS_RECV_NAME'		=>	$vip['VIP_NAME'],
				'SMS_TEXT'			=>	$model_arr['str'],
				'SMS_STATUS'		=>	'2',
				'SMS_DATE'			=>	date('Ymd'),
				'SMS_TIME'			=>	date('His'),
				'SMS_MODEL_ID'		=>	$model_arr['mid'],
				'SMS_MUL_BATCH'		=>	'0',
				'SMS_RESP_ID'		=>	'0',
			);
			D($this->TSmsls)->addSmsls($smsls);
		}		
		$this->ajaxRets(0, '激活成功！');
	}
	
	/*
	* 获取发卡记录【45】
	**/
	public function getoutcardlist() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'oId'			=>	I('oId'),				//合作方id
			'dateFormat'	=>	I('dateFormat'),		//default(全部)month(最近的一个月）week（最近一星期）
			'pageSize'		=>	I('pageSize'),			//单页记录数
			'pCountIndex'	=>	I('pCountIndex'),		//页码（比如第一页为1）
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['oId']) || !in_array($post['dateFormat'], array('default','month','week')) || empty($post['pageSize']) || empty($post['pCountIndex'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$partdata = D($this->MPartner)->findPartnerOne("PARTNER_MAP_ID = '".$post['oId']."'", 'BRANCH_MAP_ID,PARTNER_MAP_ID');
		if(empty($partdata)) {
			$this->ajaxRets(1, '合作方不存在！');
		}	
		$where = "vc.BRANCH_MAP_ID = '".$partdata['BRANCH_MAP_ID']."' and vc.PARTNER_MAP_ID = '".$partdata['PARTNER_MAP_ID']."' and vc.CARD_STATUS = 0";
		//全部
		if($post['dateFormat'] == 'default'){
			$limit = ($post['pCountIndex'] - 1)*$post['pageSize'].','.$post['pageSize'];
			$list  = D($this->GVipcard)->getVipcardmoregroup($where, 'count(vc.VIP_ID) as count,DATE_FORMAT(vc.ACTIVE_TIME,"%Y-%m-%d") as date', $limit, 'LEFT(vc.ACTIVE_TIME,8)','vc.ACTIVE_TIME desc');
			$cnt = 0;
			$cnt = D($this->GVipcard)->countVipcardmore($where);
			$res = array(
				'total' =>	$cnt.'',
				'data'	=>	!empty($list) ? $list : array(),		
			);		
		}
		//最近一个月
		else if($post['dateFormat'] == 'month'){
			$where .= " and OUT_DATE >= '".date('Ymd',strtotime('-1 month'))."'";
			$total = D($this->GOutcard)->findOutcard($where, 'sum(CARD_NUM) as CARD_NUM');
			$res = array(
				'total' =>	$total['CARD_NUM'] ? $total['CARD_NUM'] : '0',
			);	
		}
		//最近一星期
		else{
			$where .= " and OUT_DATE >= '".date('Ymd',strtotime('-1 week'))."'";
			$total = D($this->GOutcard)->findOutcard($where, 'sum(CARD_NUM) as CARD_NUM');
			$res = array(
				'total' =>	$total['CARD_NUM'] ? $total['CARD_NUM'] : '0',
			);
		}
		$this->ajaxRets(0, '获取发卡记录成功！', $res);
	}
	
	/*
	* 发卡明细【46】
	**/
	public function getoutcarddetail() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mType'			=>	I('mType'),				//0是预免卡 1是付费卡
			'oId'			=>	I('oId'),				//合作方id
			'dateFormat'	=>	I('dateFormat'),		//日期，等于某一天
			'pageSize'		=>	I('pageSize'),			//单页记录数
			'pCountIndex'	=>	I('pCountIndex'),		//页码（比如第一页为1）
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['oId']) || empty($post['dateFormat']) || empty($post['pageSize']) || empty($post['pCountIndex'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$partdata = D($this->MPartner)->findPartnerOne("PARTNER_MAP_ID = '".$post['oId']."'", 'BRANCH_MAP_ID,PARTNER_MAP_ID');
		if(empty($partdata)) {
			$this->ajaxRets(1, '合作方不存在！');
		}
		$where = "vc.BRANCH_MAP_ID = '".$partdata['BRANCH_MAP_ID']."' and vc.PARTNER_MAP_ID = '".$partdata['PARTNER_MAP_ID']."' and LEFT(vc.ACTIVE_TIME,8) = '".date('Ymd',strtotime($post['dateFormat']))."' and vc.CARD_STATUS = 0";
		if($post['mType'] != '') {
			switch($post['mType']){
				case '0':	//预免卡
					$where .= " and vc.CARD_P_MAP_ID = '3'";
					break;
				case '1':	//收费卡
					$where .= " and vc.CARD_P_MAP_ID = '2'";
					break;
			}	
		}
		$limit = ($post['pCountIndex'] - 1)*$post['pageSize'].','.$post['pageSize'];
		$count = D($this->GVipcard)->countVipcardmore($where);
		$tlist = D($this->GVipcard)->getVipcardmore($where, 'vc.CARD_NO,vp.VIP_IDNO,vp.VIP_MOBILE,vp.VIP_EMAIL,vp.CREATE_TIME,vp.VIP_NAME', $limit,'vc.ACTIVE_TIME desc');
		
		foreach($tlist as $val){
			//组装数据
			$list[] = array(
				'mCId'			=>	$val['CARD_NO'] ? $val['CARD_NO'] : '',
				'identityId'	=>	$val['VIP_IDNO'] ? $val['VIP_IDNO'] : '',
				'mobile'		=>	$val['VIP_MOBILE'] ? $val['VIP_MOBILE'] : '',
				'memaile'		=>	$val['VIP_EMAIL'] ? $val['VIP_EMAIL'] : '',
				'cTime'			=>	$val['CREATE_TIME'] ? $val['CREATE_TIME'] : '',
				'mName'			=>	$val['VIP_NAME'] ? $val['VIP_NAME'] : '',
			);
		}
		$res = array(
			'total'		=>	$count,
			'data'		=>	!empty($list) ? $list : array(),
		);
		$this->ajaxRets(0, '获取发卡明细成功！', $res);
	}

	/*
	* 绑绑定实体卡【47】
	**/
	public function vfcard_bind() {
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		$post = array(
			'mId'		=>	I('mId'),		//会员ID
			'newCId'	=>	I('newCId'),	//新卡号
			'aCode'		=>	I('aCode'),		//新卡号校验码
		);
		Add_LOG(CONTROLLER_NAME, json_encode($post));
		//验证
		if(empty($post['mId']) || empty($post['newCId']) || empty($post['aCode'])){
			$this->ajaxRets(1, '存在数据不规范，请检查！');
		}
		$vipdata = D($this->GVip)->findVip("VIP_ID = '".$post['mId']."'");
		if(empty($vipdata)){
			$this->ajaxRets(1, '会员不存在！');
		}
		//检测卡号、验证码	新卡
		$vipcard = D($this->GVipcard)->findVipcard("CARD_NO = '".$post['newCId']."' and CARD_STATUS=1 and CARD_CHECK = '".$post['aCode']."'");
		if(empty($vipcard)){
			$this->ajaxRets(1, "卡号、校验码不通过！");
		}
		//组装数据
		$resdata = array(
			'CARD_NO'			=>	$post['newCId'],
			'VIP_CARD_FLAG'		=>	$vipcard['CARD_P_MAP_ID'],
			'UPDATE_TIME'		=>	date('YmdHis'),
		);
		$M = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		$M->startTrans();	//启用事务
		//判断会员是否属于杭州总部（如果是，那做为虚拟卡）
		$vip_res = D($this->GVip)->findNewsVip("VIP_ID = '".$post['newCId']."'", 'PARTNER_MAP_ID');
		//判断卡套餐是否一样
		if ($vip_res['VIP_CARD_FLAG'] != 2) {
			$vip_card_flag = 3;
		}else{
			$vip_card_flag = 2;
		}
		if ($vip_card_flag != $vipcard['CARD_P_MAP_ID']) {
			$this->ajaxRets(1,'当前卡片类型与会员现有的卡片类型不同，不能换卡');
		}
		//判断会员是否属于杭州总部（如果是，那做为虚拟卡）
		if ($vip_res['PARTNER_MAP_ID'] == 4) {
			$vipdata['VIP_CARD_FLAG'] = '-';
		}
		//等于-，vip的归属=卡的归属
		if($vipdata['VIP_CARD_FLAG'] == '-'){
			$resdata['BRANCH_MAP_ID']  = $vipcard['BRANCH_MAP_ID'];
			$resdata['PARTNER_MAP_ID'] = $vipcard['PARTNER_MAP_ID'];
		}
		$res1 = D($this->GVip)->updateVip("VIP_ID = '".$post['mId']."'", $resdata);
		if($res1['state'] != 0){
			$this->ajaxRets(1, $res1['msg']);
		}
		
		//绑定成功，改 vipcard 表状态
		$vcarddata = array(
			'VIP_ID'		=>	$post['mId'],	//vip表vip_id 覆盖vipcard表 vip_id
			'ACTIVE_TIME'	=> date('YmdHis'),	//激活时间
			'UPDATE_TIME'	=> date('YmdHis'),	//变更时间
			'CARD_STATUS'	=>	0
		);			
		//等于1，卡的归属=vip的归属
		if($vipdata['VIP_CARD_FLAG'] == 1){
			$vcarddata['BRANCH_MAP_ID']  = $vipdata['BRANCH_MAP_ID'];
			$vcarddata['PARTNER_MAP_ID'] = $vipdata['PARTNER_MAP_ID'];
		}
		$res_vip = D($this->GVipcard)->updateVipcard("CARD_NO = '".$post['newCId']."'", $vcarddata);
		if($res_vip['state'] != 0){
			$M->rollback();	//回滚
			$this->ajaxRets(1, '卡产品修改失败！');
		}
		
		//reg 同开户逻辑
		//如果是收费卡2，插入reg表
		if($resdata['VIP_CARD_FLAG'] == 2){
			//reg
			$feecreg = D($this->MFeecfg)->findFeecfg('CFG_FLAG = 2', 'TRAFICC_FEE');
			$regdata = array(
				'REG_TYPE'	=>	'101',
				'REG_INDEX'	=>	setStrzero($post['mId'], 9),
				'REG_DESC'	=>	$vipdata['VIP_NAME'],
				'REG_AMT'	=>	$feecreg['TRAFICC_FEE'],
				'MARK_FLAG'	=>	1,
				'MARK_DATE'	=>	date('Ymd'),
			);
			D($this->MReg)->addReg($regdata);			
		}

		//同步修改会员数据
		$url = VIP_PUSH_URL.'api/open/synchronize/member/modify';
		$data = array(
			'token' 	 	 => strtoupper(md5(strtoupper(md5($vipdata['VIP_ID'].'2')))),	//(签名验证)
			'mId' 	 		 => $vipdata['VIP_ID'],						//(会员ID)
			'mCId' 	 		 => $post['newCId'].'',						//(卡号)
			'mName' 		 => $vipdata['VIP_NAME'].'',				//(会员姓名)
			'mIdentityType'	 => $vipdata['VIP_IDNOTYPE'].'',			//(证件类型)
			'mIdentityId' 	 => $vipdata['VIP_IDNO'].'',				//(证件号)
			'mBirthday'		 => date('Ymd',$vipdata['VIP_BIRTHDAY']).'',//(会员生日)
			'mMobile' 		 => $vipdata['VIP_MOBILE'].'',				//(手机号码)
			'gender' 		 => $vipdata['VIP_SEX'].'',					//(会员性别)
			'mCurrentCity'	 => getcity_name($vipdata['VIP_CITY']).'',	//(所在城市)
			'mNativeAddress' => $vipdata['VIP_ADDRESS'].'',				//(户口地址)
			'mEmail' 		 => $vipdata['VIP_EMAIL'].'',				//(会员邮箱)
			'operateType'	 => '2'										//(操作类型)
		);

		Add_LOG(CONTROLLER_NAME, json_encode($data));
/*
		$resjson = httpPostForm($url,$data);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->code != '0') {
			$M->rollback();	//回滚
			$this->ajaxRets(1,'会员数据同步失败');
		}
*/
		$M->commit();		//成功
		
		$res[] = array(
			'oId'	=>	$vipdata['PARTNER_MAP_ID'],
		);
		$this->ajaxRets(0, '绑卡成功！', $res);
	}

	//收益统计【总部】
	protected function root_statistics($field,$where,$groupdate){
		if ($groupdate) {
			$where .= $groupdate;
		}
		$sql = "SELECT ".$field." FROM t_trace t LEFT JOIN t_jfbls j ON (t.SYSTEM_REF = j.SYSTEM_REF) LEFT JOIN t_kfls k ON (t.SYSTEM_REF = k.SYSTEM_REF) WHERE ".$where;
		$model = M('trace', DB_PREFIX_TRA, DB_DSN_TRA);
		$res = $model->query($sql);
		$total = array();$n = 0;
		if ($groupdate) {
			//获得单日销售额、日期名称
			$res_num = count($res);
			if ($res_num == 0) {
				for ($i=$n; $i < 7; $i++) { 
					$date = date("Ymd",strtotime("-".(6-$i)." day"));
					$total[$i]['total'] = 0;
					$total[$i]['date']  = date('m-d',strtotime($date));
				}
			}else{
				foreach ($res as $key => $value) {
					for ($i=$n; $i < 7; $i++) { 
						$n++;
						$date = date("Ymd",strtotime("-".(6-$i)." day"));
						if ($date == $value["SYSTEM_DATE"]) {
							$total[$i]['value'] = ($value["K_BRANCH_FEE1"] + $value["J_BRANCH_FEE1"] + $value["CADF_FEE1"] + $value["NTAX_FEE1"] + $value["EATF_FEE1"]);
							$total[$i]['date']  = date('m-d',strtotime($date));
							if ($key+1 < $res_num) {
								break;
							}
						}else{
							$total[$i]['value'] = 0;
							$total[$i]['date']  = date('m-d',strtotime($date));
						}
					}
				}
			}
		}else{
			$total['total'] = $res[0]["K_BRANCH_FEE1"] + $res[0]["J_BRANCH_FEE1"] + $res[0]["CADF_FEE1"] + $res[0]["NTAX_FEE1"] + $res[0]["EATF_FEE1"];
			$total['cafay'] = $res[0]["K_BRANCH_FEE1"] + $res[0]["J_BRANCH_FEE1"];
		}
		return $total;		 
	}

	//收益统计【省公司】
	protected function branch_statistics($field,$where,$groupdate){
		if ($groupdate) {
			$where .= $groupdate;
		}
		$sql = "SELECT ".$field." FROM t_trace t LEFT JOIN t_jfbls j ON (t.SYSTEM_REF = j.SYSTEM_REF) LEFT JOIN t_kfls k ON (t.SYSTEM_REF = k.SYSTEM_REF) WHERE ".$where;
		$model = M('trace', DB_PREFIX_TRA, DB_DSN_TRA);
		$res = $model->query($sql);
		$total = array();$n = 0;
		if ($groupdate) {
			//获得单日销售额、日期名称
			$res_num = count($res);
			if ($res_num == 0) {
				for ($i=$n; $i < 7; $i++) { 
					$date = date("Ymd",strtotime("-".(6-$i)." day"));
					$total[$i]['total'] = 0;
					$total[$i]['date']  = date('m-d',strtotime($date));
				}
			}else{
				foreach ($res as $key => $value) {
					for ($i=$n; $i < 7; $i++) { 
						$n++;
						$date = date("Ymd",strtotime("-".(6-$i)." day"));
						if ($date == $value["SYSTEM_DATE"]) {
							$total[$i]['value'] = ($value["BRANCH_FEE2"] + $value["ACQ_BRANCH_FEE2"] + $value["ISS_BRANCH_FEE2"]);
							$total[$i]['date']  = date('m-d',strtotime($date));
							if ($key+1 < $res_num) {
								break;
							}
						}else{
							$total[$i]['value'] = 0;
							$total[$i]['date']  = date('m-d',strtotime($date));
						}
					}
				}
			}
		}else{
			$total['total'] = $res[0]["BRANCH_FEE2"] + $res[0]["ACQ_BRANCH_FEE2"] + $res[0]["ISS_BRANCH_FEE2"];
			$total['cafay'] = $res[0]["ISS_BRANCH_FEE2"];
		}
		return $total;		 
	}

	//收益统计【市公司】
	protected function partner_statistics_1($field,$where,$groupdate){
		if ($groupdate) {
			$where .= $groupdate;
		}
		$sql = "SELECT ".$field." FROM t_trace t LEFT JOIN t_jfbls j ON (t.SYSTEM_REF = j.SYSTEM_REF) LEFT JOIN t_kfls k ON (t.SYSTEM_REF = k.SYSTEM_REF) WHERE ".$where;
		$model = M('trace', DB_PREFIX_TRA, DB_DSN_TRA);
		$res = $model->query($sql);
		$total = array();$n = 0;
		if ($groupdate) {
			//获得单日销售额、日期名称
			$res_num = count($res);
			if ($res_num == 0) {
				for ($i=$n; $i < 7; $i++) { 
					$date = date("Ymd",strtotime("-".(6-$i)." day"));
					$total[$i]['total'] = 0;
					$total[$i]['date']  = date('m-d',strtotime($date));
				}
			}else{
				foreach ($res as $key => $value) {
					for ($i=$n; $i < 7; $i++) { 
						$n++;
						$date = date("Ymd",strtotime("-".(6-$i)." day"));
						if ($date == $value["SYSTEM_DATE"]) {
							$total[$i]['value'] = ($value["PARTNER_FEE1"] + $value["ACQ_PARTNER_FEE1"] + $value["ISS_PARTNER_FEE1"]);
							$total[$i]['date']  = date('m-d',strtotime($date));
							if ($key+1 < $res_num) {
								break;
							}
						}else{
							$total[$i]['value'] = 0;
							$total[$i]['date']  = date('m-d',strtotime($date));
						}
					}
				}
			}
		}else{
			$total['total'] = $res[0]["PARTNER_FEE1"] + $res[0]["ACQ_PARTNER_FEE1"] + $res[0]["ISS_PARTNER_FEE1"];
			$total['cafay'] = $res[0]["ISS_PARTNER_FEE1"];
		}
		return $total;		 
	}

	//收益统计【区县服务中心】
	protected function partner_statistics_2($field,$where,$groupdate){
		if ($groupdate) {
			$where .= $groupdate;
		}
		$sql = "SELECT ".$field." FROM t_trace t LEFT JOIN t_jfbls j ON (t.SYSTEM_REF = j.SYSTEM_REF) LEFT JOIN t_kfls k ON (t.SYSTEM_REF = k.SYSTEM_REF) WHERE ".$where;
		$model = M('trace', DB_PREFIX_TRA, DB_DSN_TRA);
		$res = $model->query($sql);
		$total = array();$n = 0;
		if ($groupdate) {
			//获得单日销售额、日期名称
			$res_num = count($res);
			if ($res_num == 0) {
				for ($i=$n; $i < 7; $i++) { 
					$date = date("Ymd",strtotime("-".(6-$i)." day"));
					$total[$i]['total'] = 0;
					$total[$i]['date']  = date('m-d',strtotime($date));
				}
			}else{
				foreach ($res as $key => $value) {
					for ($i=$n; $i < 7; $i++) { 
						$n++;
						$date = date("Ymd",strtotime("-".(6-$i)." day"));
						if ($date == $value["SYSTEM_DATE"]) {
							$total[$i]['value'] = ($value["K_PARTNER_FEE2"] + $value["K_PARTNER_FEE3"] + $value["J_ACQ_PARTNER_FEE2"] + $value["J_ISS_PARTNER_FEE2"] + $value["J_ISS_PARTNER_FEE3"] + $value["J_PARTNER_FEE3A"] + $value["J_PARTNER_FEE3B"]);
							$total[$i]['date']  = date('m-d',strtotime($date));
							if ($key+1 < $res_num) {
								break;
							}
						}else{
							$total[$i]['value'] = 0;
							$total[$i]['date']  = date('m-d',strtotime($date));
						}
					}
				}
			}
		}else{
			$total['total'] = $res[0]["K_PARTNER_FEE2"] + $res[0]["K_PARTNER_FEE3"] + $res[0]["J_ACQ_PARTNER_FEE2"] + $res[0]["J_ISS_PARTNER_FEE2"] + $res[0]["J_ISS_PARTNER_FEE3"] + $res[0]["J_PARTNER_FEE3A"] + $res[0]["J_PARTNER_FEE3B"];
			$total['cafay'] = $res[0]["J_ISS_PARTNER_FEE2"];
		}
		return $total;		 
	}
	//收益统计【发卡点】
	protected function partner_statistics_3($field,$where,$groupdate){
		if ($groupdate) {
			$where .= $groupdate;
		}
		$sql = "SELECT ".$field." FROM t_trace t LEFT JOIN t_jfbls j ON (t.SYSTEM_REF = j.SYSTEM_REF) LEFT JOIN t_kfls k ON (t.SYSTEM_REF = k.SYSTEM_REF) WHERE ".$where;
		$model = M('trace', DB_PREFIX_TRA, DB_DSN_TRA);
		$res = $model->query($sql);
		$total = array();$n = 0;
		if ($groupdate) {
			//获得单日销售额、日期名称
			$res_num = count($res);
			if ($res_num == 0) {
				for ($i=$n; $i < 7; $i++) { 
					$date = date("Ymd",strtotime("-".(6-$i)." day"));
					$total[$i]['total'] = 0;
					$total[$i]['date']  = date('m-d',strtotime($date));
				}
			}else{
				foreach ($res as $key => $value) {
					for ($i=$n; $i < 7; $i++) { 
						$n++;
						$date = date("Ymd",strtotime("-".(6-$i)." day"));
						if ($date == $value["SYSTEM_DATE"]) {
							$total[$i]['value'] = ($value["PARTNER_FEE3"] + $value["PARTNER_FEE3A"] + $value["PARTNER_FEE3B"] + $value["ISS_PARTNER_FEE3"]);
							$total[$i]['date']  = date('m-d',strtotime($date));
							if ($key+1 < $res_num) {
								break;
							}
						}else{
							$total[$i]['value'] = 0;
							$total[$i]['date']  = date('m-d',strtotime($date));
						}
					}
				}
			}
		}else{
			$total['total'] = $res[0]["PARTNER_FEE3"] + $res[0]["PARTNER_FEE3A"] + $res[0]["PARTNER_FEE3B"] + $res[0]["ISS_PARTNER_FEE3"];
			$total['cafay'] = $res[0]["ISS_PARTNER_FEE3"];
		}
		return $total;		 
	}
}
