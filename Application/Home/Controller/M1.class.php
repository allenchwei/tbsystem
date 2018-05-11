<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  会员
// +----------------------------------------------------------------------
class MemberController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->GVip 		= 'GVip';
		$this->MCproduct 	= 'MCproduct';
		$this->GVipcard 	= 'GVipcard';
		$this->GLap 		= 'GLap';
		$this->MExcel 		= 'MExcel';
		$this->MFeecfg 		= 'MFeecfg';
		$this->MReg 		= 'MReg';
		$this->MRole 		= 'MRole';
		$this->TSmsls 		= 'TSmsls';
		$this->MShopapply	= 'MShopapply';
	}
	
	/*
	* 会员业务管理
	**/
	public function vbus() {
		$post = I('post');
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
				'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
				'VIP_CARD_FLAG'		=>	I('VIP_CARD_FLAG'),
				'VIP_IDNO'			=>	I('VIP_IDNO'),
				'VIP_MOBILE'		=>	I('VIP_MOBILE'),
				'VIP_NAME'			=>	I('VIP_NAME'),
				'VIP_AUTH_FLAG'		=>	I('VIP_AUTH_FLAG'),
				'VIP_DONATE'		=>	I('VIP_DONATE'),
				'CARD_NO'			=>	I('CARD_NO'),
				'ACCT_DIVBAL'		=>	I('ACCT_DIVBAL'),
				'CREATE_TIME_A'		=>	I('CREATE_TIME_A'),
				'CREATE_TIME_B'		=>	I('CREATE_TIME_B'),
			);
			$ajax_soplv = array(
				'bid'				=>	$post['BRANCH_MAP_ID'],
				'pid'				=>	$post['PARTNER_MAP_ID'],			
			);
		}
		//===结束=======
		if($post['submit'] == "vbus"){
			$where = "1=1";
			//归属
			//===优化统计===
			$getlevel = $ajax == 'loading' ? $ajax_soplv : filter_data('plv');	//列表查询
			//===结束=======
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
			}
			if($post['PARTNER_MAP_ID']){
				$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
				$where .= " and PARTNER_MAP_ID in (".$pids.")";
			}			
			//卡套餐
			if($post['VIP_CARD_FLAG'] != '') {
				if($post['VIP_CARD_FLAG'] == 1){
					$where .= " and (VIP_CARD_FLAG = '-' or VIP_CARD_FLAG = '1')";
				}else{
					$where .= " and VIP_CARD_FLAG = '".$post['VIP_CARD_FLAG']."'";
				}
			}
			//身份证号
			if($post['VIP_IDNO']) {
				if (strlen($post['VIP_IDNO']) == 15 || strlen($post['VIP_IDNO']) == 18) {
					$id_type = " and VIP_IDNOTYPE = '0'";
				}
				$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'".$id_type;
			}
			//手机号
			if($post['VIP_MOBILE']) {
				$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
			}
			//实名认证
			if($post['VIP_AUTH_FLAG'] != '') {
				$where .= " and VIP_AUTH_FLAG = '".$post['VIP_AUTH_FLAG']."'";
			}
			//子母卡
			if($post['VIP_DONATE'] != '') {
				$where .= " and VIP_DONATE = '".$post['VIP_DONATE']."'";
			}
			//会员卡号
			if($post['CARD_NO']) {
				$where .= " and CARD_NO = '".$post['CARD_NO']."'";
			}
			//会员名称
			if($post['VIP_NAME']) {
				$where .= " and VIP_NAME like '%".$post['VIP_NAME']."%'";
			}
			//卡费回收
			if($post['ACCT_DIVBAL'] != '') {
				$lw 	 = $post['ACCT_DIVBAL']==0 ? "ACCT_DIVBAL = 0" : "ACCT_DIVBAL > 0";
				$laplist = D($this->GLap)->getNewsLaplist($lw, 'ACCT_NO', '');
				if(!empty($laplist)){
					foreach($laplist as $val){
						$str .= (int)($val['ACCT_NO']).',';
					}
				}
				if($str){
					$where .= " and VIP_ID in (".substr($str,0,-1).")";
				}
			}
			//创建日期	开始
			if($post['CREATE_TIME_A']) {
				$where .= " and CREATE_TIME >= '".date('Ymd',strtotime($post['CREATE_TIME_A']))."000000'";
			}
			//创建日期	结束
			if($post['CREATE_TIME_B']) {
				$where .= " and CREATE_TIME <= '".date('Ymd',strtotime($post['CREATE_TIME_B']))."235959'";
			}
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->GVip)->countNewsVip($where);
				$resdata = array(
					'count'	=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======			
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->GVip)->getNewsViplist($where, '*', $fiRow.','.$liRow);
			foreach($list as $key=>$val){
				$lapdata = D($this->GLap)->findLap("ACCT_NO = '".setStrzero($val['VIP_ID'],9)."'", 'ACCT_DIVBAL');
				$list[$key]['ACCT_DIVBAL'] = $lapdata['ACCT_DIVBAL'];
				$list[$key]['CREATE_TIME'] = date('Y-m-d H:i:s', strtotime($val['CREATE_TIME']));
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
		//卡套餐
		$product = D($this->MCproduct)->getCproductlist_one('', 'CARD_P_MAP_ID,CARD_NAME');
		foreach($product as $val){
			$vip_card_flag[$val['CARD_P_MAP_ID']] = $val['CARD_NAME'];
		}
		$this->assign('vip_card_flag', 		$vip_card_flag);		//卡套餐
		$this->assign('vip_auth_flag', 		C('VIP_AUTH_FLAG'));	//实名认证
		$this->assign('vip_donate', 		C('VIP_DONATE'));		//子母卡
		$this->assign('vip_status', 		C('VIP_STATUS'));		//会员状态
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 会员业务管理 开户
	**/
	public function vbus_add() {


		$post = I('post');
		if($post['submit'] == "vbus_add") {
			//归属
			$getlevel = I('plv');	//归属推广中心
			$post['BRANCH_MAP_ID']  = $getlevel['0'];
			$post['PARTNER_MAP_ID'] = $getlevel['3'] ? $getlevel['3'] : $getlevel['2'];
			
			//判断商户归属
			if (empty($post['BRANCH_MAP_ID']) || empty($post['PARTNER_MAP_ID'])) {
				$this->wrong("请选择归属机构,且会员只能归属到区县服务中心！");
			}
			//验证
			if(empty($post['VIP_NAME']) || empty($post['VIP_IDNO']) || empty($post['VIP_BIRTHDAY']) || empty($post['VIP_MOBILE']) || empty($post['VIP_CITY'])){
				$this->wrong("请完善基础信息！");
			}
			if(empty($post['CARD_NO']) || empty($post['VIP_CARD_FLAG']) || empty($post['CARD_CHECK'])){
				$this->wrong("请完善绑卡设置！");
			}
			//检测证件号
			$findvip1 = D($this->GVip)->findNewsVip("VIP_IDNOTYPE = '".$post['VIP_IDNOTYPE']."' and VIP_IDNO = '".$post['VIP_IDNO']."'");
			if(!empty($findvip1)){
				$this->wrong("该证件号已注册！");
			}
			//检测手机号
			$findvip2 = D($this->GVip)->findNewsVip("VIP_MOBILE = '".$post['VIP_MOBILE']."'");
			if(!empty($findvip2)){
				$this->wrong("该手机号已注册！");
			}
			//检查性别是否合法
			if($post['VIP_IDNOTYPE']==0 && $post['VIP_IDNOTYPE']!=''){
				$sex = substr($post['VIP_IDNO'], -2, 1);
				if($sex%2 != $post['VIP_SEX']){
					$this->wrong("请规范选择会员性别！");
				}
			}
			//检查卡号是否重复
			$findvip3 = D($this->GVip)->findNewsVip("CARD_NO = '".$post['CARD_NO']."'");
			if(!empty($findvip3)){
				$this->wrong("该卡号已注册！");
			}
			//卡号信息，检测卡号、验证码，
			$findvipcard = D($this->GVipcard)->findVipcard("CARD_NO = '".$post['CARD_NO']."' and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."' and PARTNER_MAP_ID = '".$post['PARTNER_MAP_ID']."' and CARD_STATUS=1 and CARD_CHECK = '".$post['CARD_CHECK']."'");
			if(empty($findvipcard)){
				$this->wrong("1.所绑卡片, 必须是当前所选归属的卡片.<br />
							  2.卡号要与验证码匹配.<br />
							  3.所绑卡片应该是库存状态.");
			}
			//证件
			$ID_PHOTO = '';
			if($post['ID_PHOTO_A'] || $post['ID_PHOTO_B']){
				if($post['ID_PHOTO_A'] && $post['ID_PHOTO_B']){
					$ID_PHOTO = $post['ID_PHOTO_A'].','.$post['ID_PHOTO_B'];
				}
				if($post['ID_PHOTO_A'] && !$post['ID_PHOTO_B']){
					$ID_PHOTO = $post['ID_PHOTO_A'];
				}
				if(!$post['ID_PHOTO_A'] && $post['ID_PHOTO_B']){
					$ID_PHOTO = $post['ID_PHOTO_B'];
				}
			}
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],
				'VIP_SOURCE'		=>	1,
				'CARD_NO'			=>	$post['CARD_NO'],
				'VIP_NAME'			=>	$post['VIP_NAME'],
				'VIP_STATUS'		=>	0,
				'VIP_CARD_FLAG'		=>	$post['VIP_CARD_FLAG'],
				'VIP_AUTH_FLAG'		=>	1,	//认证
				'VIP_PARTNER_FLAG'	=>	0,
				'VIP_IDNOTYPE'		=>	$post['VIP_IDNOTYPE'],
				'VIP_IDNO'			=>	$post['VIP_IDNO'],
				'VIP_MOBILE'		=>	$post['VIP_MOBILE'],
				'VIP_CITY'			=>	$post['VIP_CITY'],
				'VIP_ADDRESS'		=>	$post['VIP_ADDRESS'] ? $post['VIP_ADDRESS'] : '',
				'VIP_SEX'			=>	$post['VIP_SEX'],
				'VIP_EMAIL'			=>	$post['VIP_EMAIL'] ? $post['VIP_EMAIL'] : 'piccbill@jfb315.net',
				'VIP_BIRTHDAY'		=>	date('Ymd',strtotime($post['VIP_BIRTHDAY'])),
				'VIP_DONATE'		=>	0,
				'VIP_DONATE_PER'	=>	'',
				'VIP_ID_M'			=>	'',
				'VIP_PIN'			=>	strtoupper(md5(strtoupper(md5('888888')))),
				'VIP_PINTIME'		=>	0,
				'VIP_PINLIMIT'		=>	5,
				'CREATE_TIME'		=>	date('YmdHis'),
				'ACTIVE_TIME'		=>	date('YmdHis'),
				'UPDATE_TIME'		=>	date('YmdHis'),
				'RES'				=>	'',
				'ID_PHOTO'			=>	$ID_PHOTO,
			);
			$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
			$m->startTrans();	//启用事务
			
			$res = D($this->GVip)->addVip($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//将会员归属覆盖卡归属
			$vcarddata   = array(
				'BRANCH_MAP_ID1'	=>	$findvipcard['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID1'	=>	$findvipcard['PARTNER_MAP_ID'],
				'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],				
				'VIP_ID'			=>	$res['VIP_ID'],		//vip表vip_id 覆盖vipcard表 vip_id
				'ACTIVE_TIME'		=>	date('YmdHis'),		//激活时间
				'UPDATE_TIME'		=>	date('YmdHis'),		//变更时间
				'CARD_STATUS'		=>	0
			);
			$res_vip = D($this->GVipcard)->updateVipcard("CARD_NO = '".$post['CARD_NO']."'", $vcarddata);
			if($res_vip['state'] != 0){
				$m->rollback();	//回滚
				$this->wrong('卡产品修改失败！');
			}
			
			//如果是预免卡3，只插入lap表，如果是收费卡2，插入lap表,还要插入reg表
			$feeclap = D($this->MFeecfg)->findFeecfg('CFG_FLAG = 3', 'CARD_OPENFEE');
			$lapdata = array(
				'SUBJECT_CODE'	=>	'20101',
				'ACCT_NO'		=>	setStrzero($res['VIP_ID'], 9),
				'ACCT_NAME'		=>	$resdata['VIP_NAME'],
				'ACCT_TYPE'		=>	'V',
				'ACCT_VALBAL'	=>	'0',
				'ACCT_YLTBAL'	=>	'0',
				'ACCT_YLVBAL'	=>	'0',
				'ACCT_YWTBAL'	=>	'0',
				'ACCT_YWVBAL'	=>	'0',
				'ACCT_DIVBAL'	=>	$resdata['VIP_CARD_FLAG']==3 ? $feeclap['CARD_OPENFEE'] : '0',
				'ACCT_CAMT'		=>	'0',
				'ACCT_DAMT'		=>	'0',
				'ACCT_DATE'		=>	date('Ymd'),
				'SYSTEM_TIME'	=>	date('YmdHis'),
				'MAC'			=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',
				'YLT_AMT'		=>	'0',
				'YWT_AMT'		=>	'0',
			);
			D($this->GLap)->addLap($lapdata);	
			if($resdata['VIP_CARD_FLAG'] == 2){
				//reg
				$feecreg = D($this->MFeecfg)->findFeecfg('CFG_FLAG = 2', 'TRAFICC_FEE');
				$regdata = array(
					'REG_TYPE'	=>	'101',
					'REG_INDEX'	=>	setStrzero($res['VIP_ID'], 9),
					'REG_DESC'	=>	$resdata['VIP_NAME'],
					'REG_AMT'	=>	$feecreg['TRAFICC_FEE'],
					'MARK_FLAG'	=>	1,
					'MARK_DATE'	=>	date('Ymd'),
				);
				D($this->MReg)->addReg($regdata);			
			}
			$m->commit();	//成功
			/* @author sea start */
			/*//同步新增会员数据
			$url = VIP_PUSH_URL.'api/open/synchronize/member/register';
			$data = array(
				'token' 	 	 => strtoupper(md5(strtoupper(md5($res['VIP_ID'].'1')))),	//(签名验证)
				'mId' 	 		 => $res['VIP_ID'],						//(会员ID)
				'mCId' 	 		 => $resdata['CARD_NO'] ? $resdata['CARD_NO'] : '00'.$res['VIP_ID'],	//(卡号)
				'mName' 		 => $resdata['VIP_NAME'],				//(会员姓名)
				'mIdentityType'	 => $resdata['VIP_IDNOTYPE'],			//(证件类型)
				'mIdentityId' 	 => $resdata['VIP_IDNO'],				//(证件号)
				'mBirthday'		 => $resdata['VIP_BIRTHDAY'],			//(会员生日)
				'mMobile' 		 => $resdata['VIP_MOBILE'],				//(手机号码)
				'gender' 		 => $resdata['VIP_SEX'],				//(会员性别)
				'mCurrentCity'	 => getcity_name($resdata['VIP_CITY']),	//(所在城市)
				'mNativeAddress' => $resdata['VIP_ADDRESS'],			//(户口地址)
				'mEmail' 		 => $resdata['VIP_EMAIL'],				//(会员邮箱)
				'operateType'	 => '1',								//(操作类型)
				'migrateType'	 => '2',								//(迁移区分)
				'mRemark'	 	 => 'glzxNew'
			);
			Add_LOG(CONTROLLER_NAME);
			Add_LOG(CONTROLLER_NAME, json_encode($data));
			$resjson = httpPostForm($url,$data);
			Add_LOG(CONTROLLER_NAME, $resjson);
			$result = json_decode($resjson);
			if ($result->code != '0') {
				$this->wrong('会员数据同步失败,请联系管理员手动同步');
			}
			*/
			/* @author sea end */
			//发短信
			if($resdata['VIP_MOBILE'] && ($resdata['VIP_CARD_FLAG']==3 or $resdata['VIP_CARD_FLAG']==2)){
				//短信模板
				$model_arr = setSmsmodel(2);
				//短信流水
				$smsls = array(
					'BRANCH_MAP_ID'		=>	$resdata['BRANCH_MAP_ID'],
					'PARTNER_MAP_ID'	=>	$resdata['PARTNER_MAP_ID'],
					'SMS_MODEL_TYPE'	=>	'2',
					'VIP_FLAG'			=>	$resdata['VIP_CARD_FLAG'],
					'VIP_ID'			=>	$res['VIP_ID'],
					'VIP_CARDNO'		=>	$resdata['CARD_NO'],
					'SMS_RECV_MOB'		=>	$resdata['VIP_MOBILE'],
					'SMS_RECV_NAME'		=>	$resdata['VIP_NAME'],
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
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign('vip_idnotype', 		C('VIP_IDNOTYPE'));		//证件类型
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别

		//如果是超级管理员，查看所有
		$home = session('HOME');
		if(C('SPECIAL_USER') == $home['USER_ID']){
			$where = "ROLE_ID != 1 and ROLE_STATUS = 1";
		}else{
			$where = "ROLE_ID != 1 and ROLE_STATUS = 1 and ROLE_LEVEL>='".$home['USER_LEVEL']."'";
		}		
		$role_list = D($this->MRole)->getRolelist($where, 'ROLE_ID,ROLE_NAME');	//除超管外		
		$this->assign('role_list', 			$role_list);
		
		$this->assign('user_level',			C('USER_LEVEL'));	//用户级别
		$this->assign('user_status',		C('USER_STATUS'));	//用户状态
		$this->display();


	}
	/*
	* 会员业务管理 详情	【跟下面的 vinfo_show 一样】
	**/
	public function vbus_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['VIP_BIRTHDAY'] = $info['VIP_BIRTHDAY'] ? date('Y-m-d', strtotime($info['VIP_BIRTHDAY'])) : '';
		$info['ID_PHOTO'] = explode(',', $info['ID_PHOTO']);
		//获取当前卡的金额
		$lapdata = D($this->GLap)->findLap("ACCT_NO = '".setStrzero($info['VIP_ID'], 9)."'");
		$info['ACCT_VALBAL'] = $lapdata['ACCT_VALBAL'];	//可用
		$info['ACCT_DIVBAL'] = $lapdata['ACCT_DIVBAL'];	//剩余
		$info['ACCT_YLTBAL'] = $lapdata['ACCT_YLTBAL'];	//养老已投总额
		$info['ACCT_YLVBAL'] = $lapdata['ACCT_YLVBAL'];	//养老可投金额
		$info['ACCT_YWTBAL'] = $lapdata['ACCT_YWTBAL'];	//意外已投总额
		$info['ACCT_YWVBAL'] = $lapdata['ACCT_YWVBAL'];	//意外可投金额				
		//绑定卡的卡费
		$info['CARD_OPENFEE'] = 0;
		$info['IS_BANG'] 	  = 0;	//0未绑定，1绑定	
		$vipcard  = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID']."' and CARD_STATUS = 0", 'CARD_P_MAP_ID');
		if(!empty($vipcard)){
			$feecfg = D($this->MFeecfg)->findFeecfg("CFG_FLAG = '".$vipcard['CARD_P_MAP_ID']."'", 'CARD_OPENFEE');
			$info['CARD_OPENFEE'] = setMoney($feecfg['CARD_OPENFEE'], '2', '2');
			$info['IS_BANG']	  = 1;
		}
		//子母卡
		if($info['VIP_DONATE'] == 1){
			$info_m = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID_M']."' and CARD_STATUS = 0", 'CARD_NO');
			$info['CARD_NO_M'] = $info_m['CARD_NO'];
		}
		$this->assign('vip_idnotype', 		C('VIP_IDNOTYPE'));		//证件类型
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别
		$this->assign('info', 				$info);
		$this->display('vinfo_show');
	}
	/*
	* 会员业务管理 归属变更
	**/
	public function vbus_partner() {
		$post = I('post');
		if($post['submit'] == "vbus_partner") {
			//归属
			$getlevel = get_level_val('plv');
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'] ? $getlevel['pid'] : 0;
			//验证
			if(empty($post['BRANCH_MAP_ID'])){
				$this->wrong("请选择归属！");
			}
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],
				'UPDATE_TIME'		=>	date('YmdHis'),
			);
			$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
			$m->startTrans();	//启用事务
			
			$res = D($this->GVip)->updateVip("VIP_ID = '".$post['VIP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//改卡片归属
			$vcarddata   = array(
				'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],
				'UPDATE_TIME'		=>	date('YmdHis'),
			);
			$res_vip = D($this->GVipcard)->updateVipcard("VIP_ID = '".$post['VIP_ID']."'", $vcarddata);
			if($res_vip['state'] != 0){
				$m->rollback();	//回滚
				$this->wrong('卡产品修改失败！');
			}
			$m->commit();	//成功
			$this->right('归属变更成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['VIP_BIRTHDAY'] = $info['VIP_BIRTHDAY'] ? date('Y-m-d', strtotime($info['VIP_BIRTHDAY'])) : '';
		$info['ID_PHOTO'] = explode(',', $info['ID_PHOTO']);
		//获取当前卡的金额
		$lapdata = D($this->GLap)->findLap("ACCT_NO = '".setStrzero($info['VIP_ID'], 9)."'");
		$info['ACCT_VALBAL'] = $lapdata['ACCT_VALBAL'];	//可用
		$info['ACCT_DIVBAL'] = $lapdata['ACCT_DIVBAL'];	//剩余
		$info['ACCT_YLTBAL'] = $lapdata['ACCT_YLTBAL'];	//养老已投总额
		$info['ACCT_YLVBAL'] = $lapdata['ACCT_YLVBAL'];	//养老可投金额
		$info['ACCT_YWTBAL'] = $lapdata['ACCT_YWTBAL'];	//意外已投总额
		$info['ACCT_YWVBAL'] = $lapdata['ACCT_YWVBAL'];	//意外可投金额				
		//绑定卡的卡费
		$info['CARD_OPENFEE'] = 0;
		$info['IS_BANG'] 	  = 0;	//0未绑定，1绑定	
		$vipcard  = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID']."' and CARD_STATUS = 0", 'CARD_P_MAP_ID');
		if(!empty($vipcard)){
			$feecfg = D($this->MFeecfg)->findFeecfg("CFG_FLAG = '".$vipcard['CARD_P_MAP_ID']."'", 'CARD_OPENFEE');
			$info['CARD_OPENFEE'] = setMoney($feecfg['CARD_OPENFEE'], '2', '2');
			$info['IS_BANG']	  = 1;
		}
		//子母卡
		if($info['VIP_DONATE'] == 1){
			$info_m = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID_M']."' and CARD_STATUS = 0", 'CARD_NO');
			$info['CARD_NO_M'] = $info_m['CARD_NO'];
		}
		$this->assign('vip_idnotype', 		C('VIP_IDNOTYPE'));		//证件类型
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 会员业务管理 冻结
	**/
	public function vbus_close() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//过滤
		if($info['VIP_STATUS'] == 2){
			$this->wrong('当前操作不允许冻结！');
		}
		$res = D($this->GVip)->updateVip("VIP_ID = '".$id."'", array('VIP_STATUS'=> 2));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('冻结成功！');
	}
	/*
	* 会员业务管理 解冻
	**/
	public function vbus_open() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//过滤
		if($info['VIP_STATUS'] == 0){
			$this->wrong('当前操作不允许解冻！');
		}
		$res = D($this->GVip)->updateVip("VIP_ID = '".$id."'", array('VIP_STATUS'=> 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('解冻成功！');
	}
	/*
	* 会员业务管理 重置密码
	**/
	public function vbus_resetpwd() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$resdata = array(
			'VIP_PIN'		=> strtoupper(md5(strtoupper(md5('888888')))),
			'VIP_PINTIME'	=>	0
		);
		$res = D($this->GVip)->updateVip("VIP_ID = '".$id."'", $resdata);
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('重置密码成功！');
	}
	
	
	
	
	/*
	* 会员资料管理
	**/
	public function vinfo() {
		$post = I('post');		
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
				'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
				'VIP_CARD_FLAG'		=>	I('VIP_CARD_FLAG'),
				'VIP_IDNO'			=>	I('VIP_IDNO'),
				'VIP_MOBILE'		=>	I('VIP_MOBILE'),
				'VIP_AUTH_FLAG'		=>	I('VIP_AUTH_FLAG'),
				'CARD_NO'			=>	I('CARD_NO'),
				'CREATE_TIME_A'		=>	I('CREATE_TIME_A'),
				'CREATE_TIME_B'		=>	I('CREATE_TIME_B'),
			);
			$ajax_soplv = array(
				'bid'				=>	$post['BRANCH_MAP_ID'],
				'pid'				=>	$post['PARTNER_MAP_ID'],			
			);
		}
		//===结束=======		
		if($post['submit'] == "vinfo"){
			$where = "1=1";
			//归属
			//===优化统计===
			$getlevel = $ajax == 'loading' ? $ajax_soplv : filter_data('plv');	//列表查询
			//===结束=======
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
			}
			if($post['PARTNER_MAP_ID']){
				$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
				$where .= " and PARTNER_MAP_ID in (".$pids.")";
			}
			//卡套餐
			if($post['VIP_CARD_FLAG'] !='') {
				if($post['VIP_CARD_FLAG'] == 1){
					$where .= " and (VIP_CARD_FLAG = '-' or VIP_CARD_FLAG = '1')";
				}else{
					$where .= " and VIP_CARD_FLAG = '".$post['VIP_CARD_FLAG']."'";
				}
			}
			//身份证号
			if($post['VIP_IDNO']) {
				if (strlen($post['VIP_IDNO']) == 15 || strlen($post['VIP_IDNO']) == 18) {
					$id_type = " and VIP_IDNOTYPE = '0'";
				}
				$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'".$id_type;
			}
			//手机号
			if($post['VIP_MOBILE']) {
				$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
			}
			//实名认证
			if($post['VIP_AUTH_FLAG']) {
				$where .= " and VIP_AUTH_FLAG = '".$post['VIP_AUTH_FLAG']."'";
			}
			//卡号
			if($post['CARD_NO']) {
				$where .= " and CARD_NO = '".$post['CARD_NO']."'";
			}
			//创建时间	开始
			if($post['CREATE_TIME_A']) {
				$where .= " and CREATE_TIME >= '".date('YmdHis',strtotime($post['CREATE_TIME_A'].'000000'))."'";
			}
			//创建时间	结束
			if($post['CREATE_TIME_B']) {
				$where .= " and CREATE_TIME <= '".date('YmdHis',strtotime($post['CREATE_TIME_B'].'235959'))."'";
			}
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->GVip)->countNewsVip($where);
				$resdata = array(
					'count'	=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======			
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->GVip)->getNewsViplist($where, '*', $fiRow.','.$liRow);
			foreach($list as $key=>$val){
				$list[$key]['CREATE_TIME'] = date('Y-m-d H:i:s', strtotime($val['CREATE_TIME']));
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
		//卡套餐
		$product = D($this->MCproduct)->getCproductlist_one('', 'CARD_P_MAP_ID,CARD_NAME');
		foreach($product as $val){
			$vip_card_flag[$val['CARD_P_MAP_ID']] = $val['CARD_NAME'];
		}		
		$this->assign('vip_card_flag', 		$vip_card_flag);		//卡套餐
		$this->assign('vip_auth_flag', 		C('VIP_AUTH_FLAG'));	//实名认证
		$this->assign('vip_status', 		C('VIP_STATUS'));		//会员状态
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 会员资料管理 详情
	**/
	public function vinfo_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['VIP_BIRTHDAY'] = $info['VIP_BIRTHDAY'] ? date('Y-m-d', strtotime($info['VIP_BIRTHDAY'])) : '';
		$info['ID_PHOTO'] = explode(',', $info['ID_PHOTO']);
		//获取当前卡的金额
		$lapdata = D($this->GLap)->findLap("ACCT_NO = '".setStrzero($info['VIP_ID'], 9)."'");
		$info['ACCT_VALBAL'] = $lapdata['ACCT_VALBAL'];	//可用
		$info['ACCT_DIVBAL'] = $lapdata['ACCT_DIVBAL'];	//剩余
		$info['ACCT_YLTBAL'] = $lapdata['ACCT_YLTBAL'];	//养老已投总额
		$info['ACCT_YLVBAL'] = $lapdata['ACCT_YLVBAL'];	//养老可投金额
		$info['ACCT_YWTBAL'] = $lapdata['ACCT_YWTBAL'];	//意外已投总额
		$info['ACCT_YWVBAL'] = $lapdata['ACCT_YWVBAL'];	//意外可投金额				
		//绑定卡的卡费
		$info['CARD_OPENFEE'] = 0;
		$info['IS_BANG'] 	  = 0;	//0未绑定，1绑定	
		$vipcard  = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID']."' and CARD_STATUS = 0", 'CARD_P_MAP_ID');
		if(!empty($vipcard)){
			$feecfg = D($this->MFeecfg)->findFeecfg("CFG_FLAG = '".$vipcard['CARD_P_MAP_ID']."'", 'CARD_OPENFEE');
			$info['CARD_OPENFEE'] = setMoney($feecfg['CARD_OPENFEE'], '2', '2');
			$info['IS_BANG']	  = 1;
		}
		//子母卡
		if($info['VIP_DONATE'] == 1){
			$info_m = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID_M']."' and CARD_STATUS = 0", 'CARD_NO');
			$info['CARD_NO_M'] = $info_m['CARD_NO'];
		}
		$this->assign('vip_idnotype', 		C('VIP_IDNOTYPE'));		//证件类型
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 会员资料管理 修改
	**/
	public function vinfo_edit() {
		$post = I('post');
		if($post['submit'] == "vinfo_edit") {
			//验证
			if(empty($post['VIP_NAME']) || empty($post['VIP_IDNO']) || empty($post['VIP_BIRTHDAY']) || empty($post['VIP_MOBILE']) || empty($post['VIP_CITY'])){
				$this->wrong("请完善基础信息！");
			}
			//检测证件号
			$findvip1 = D($this->GVip)->findNewsVip("VIP_ID != '".$post['VIP_ID']."' and VIP_IDNOTYPE = '".$post['VIP_IDNOTYPE']."' and VIP_IDNO = '".$post['VIP_IDNO']."'");
			if(!empty($findvip1)){
				$this->wrong("该证件号已注册！");
			}
			//检测手机号
			$findvip2 = D($this->GVip)->findNewsVip("VIP_ID != '".$post['VIP_ID']."' and VIP_MOBILE = '".$post['VIP_MOBILE']."'");
			if(!empty($findvip2)){
				$this->wrong("该手机号已注册！");
			}
			//检查性别是否合法
			if($post['VIP_IDNOTYPE']==0 && $post['VIP_IDNOTYPE']!=''){
				$sex = substr($post['VIP_IDNO'], -2, 1);
				if($sex%2 != $post['VIP_SEX']){
					$this->wrong("请规范选择会员性别！");
				}
			}
			//证件
			$ID_PHOTO = '';
			if($post['ID_PHOTO_A'] || $post['ID_PHOTO_B']){
				if($post['ID_PHOTO_A'] && $post['ID_PHOTO_B']){
					$ID_PHOTO = $post['ID_PHOTO_A'].','.$post['ID_PHOTO_B'];
				}
				if($post['ID_PHOTO_A'] && !$post['ID_PHOTO_B']){
					$ID_PHOTO = $post['ID_PHOTO_A'];
				}
				if(!$post['ID_PHOTO_A'] && $post['ID_PHOTO_B']){
					$ID_PHOTO = $post['ID_PHOTO_B'];
				}
			}
			if (substr($post['VIP_IDNO'], 0, 1) == '9') {
				$vip_auth_flag = 0;
			}else{
				$vip_auth_flag = 1;
			}
			//组装数据
			$resdata = array(
				'VIP_NAME'			=>	$post['VIP_NAME'],
				'VIP_IDNOTYPE'		=>	$post['VIP_IDNOTYPE'],
				'VIP_IDNO'			=>	$post['VIP_IDNO'],
				'VIP_AUTH_FLAG'		=>	$vip_auth_flag,
				'VIP_MOBILE'		=>	$post['VIP_MOBILE'],
				'VIP_CITY'			=>	$post['VIP_CITY'],
				'VIP_ADDRESS'		=>	$post['VIP_ADDRESS'] ? $post['VIP_ADDRESS'] : '',
				'VIP_SEX'			=>	$post['VIP_SEX'],
				'VIP_EMAIL'			=>	$post['VIP_EMAIL'] ? $post['VIP_EMAIL'] : 'piccbill@jfb315.net',
				'VIP_BIRTHDAY'		=>	date('Ymd',strtotime($post['VIP_BIRTHDAY'])),
				'UPDATE_TIME'		=>	date('YmdHis'),
				'ID_PHOTO'			=>	$ID_PHOTO,
			);

			$findvip3 = D($this->GVip)->findNewsVip("VIP_ID = '".$post['VIP_ID']."'");
			if (strlen($findvip3['CARD_NO']) < 16) {
				$resdata['CARD_NO'] = '00'.$post['VIP_ID'];
				$post['CARD_NO']    = '00'.$post['VIP_ID'];
			}else{
				$post['CARD_NO']    = $findvip3['CARD_NO'];
			}
			$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
			$m->startTrans();	//启用事务
			$res = D($this->GVip)->updateVip("VIP_ID = '".$post['VIP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			/* @author sea start */
			/*//同步修改会员数据
			$url = VIP_PUSH_URL.'api/open/synchronize/member/modify';
			$data = array(
				'token' 	 	 => strtoupper(md5(strtoupper(md5($post['VIP_ID'].'2')))),			//(签名验证)
				'mId' 	 		 => $post['VIP_ID'],					//(会员ID)
				'mCId' 	 		 => $post['CARD_NO'],					//(卡号)
				'mName' 		 => $resdata['VIP_NAME'],				//(会员姓名)
				'mIdentityType'	 => $resdata['VIP_IDNOTYPE'],			//(证件类型)
				'mIdentityId' 	 => $resdata['VIP_IDNO'],				//(证件号)
				'mBirthday'		 => $resdata['VIP_BIRTHDAY'],			//(会员生日)
				'mMobile' 		 => $resdata['VIP_MOBILE'],				//(手机号码)
				'gender' 		 => $resdata['VIP_SEX'],				//(会员性别)
				'mCurrentCity'	 => getcity_name($resdata['VIP_CITY']),	//(所在城市)
				'mNativeAddress' => $resdata['VIP_ADDRESS'],			//(户口地址)
				'mEmail' 		 => $resdata['VIP_EMAIL'],				//(会员邮箱)
				'operateType'	 => '2'									//(操作类型)
			);
			Add_LOG(CONTROLLER_NAME, json_encode($data));
			$resjson = httpPostForm($url,$data);
			Add_LOG(CONTROLLER_NAME, $resjson);
			$result = json_decode($resjson);
			if ($result->code != '0') {
				$m->rollback();	//回滚
				$this->wrong('会员数据同步修改失败');
			}*/
			/* @author sea end */
			//同步修改保险记录
			$updateTB = array(
				'VIP_NAME' 		 => $resdata['VIP_NAME'],				//(会员姓名)
				'VIP_IDNO' 	 	 => $resdata['VIP_IDNO'],				//(证件号)
				'VIP_MOBILE' 	 => $resdata['VIP_MOBILE'],				//(手机号码)
				'VIP_EMAIL' 	 => $resdata['VIP_EMAIL'],				//(会员邮箱)
			);
			D('TTbls')->updateTbls('VIP_ID = "'.$post['VIP_ID'].'"', $updateTB);
			$m->commit();	//成功
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['VIP_BIRTHDAY'] = $info['VIP_BIRTHDAY'] ? date('Y-m-d', strtotime($info['VIP_BIRTHDAY'])) : '';
		$info['ID_PHOTO'] = explode(',', $info['ID_PHOTO']);
		$this->assign('vip_idnotype', 		C('VIP_IDNOTYPE'));		//证件类型
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 会员资料管理 下载
	**/
	public function vinfo_down() {
		$submit = $_REQUEST['submit'];	
		$url 	= $_REQUEST['url'];
		if($submit == "vinfo_down") {
			if(!$url){
				$this->wrong('证件不存在！');			
			}			
			$filename = $url;
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=".basename($filename));
			readfile($filename);
			exit;
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if(empty($info['ID_PHOTO'])){
			$this->wrong("该用户未上传证件！");
		}
		$info['ID_PHOTO'] = explode(',', $info['ID_PHOTO']);
		$this->assign('info', 				$info);
		$this->display();
	}	
	/*
	* 会员资料管理 导出
	**/
	public function vinfo_export() {
		$post  = array(
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'VIP_CARD_FLAG'		=>	I('VIP_CARD_FLAG'),
			'VIP_IDNO'			=>	I('VIP_IDNO'),
			'VIP_MOBILE'		=>	I('VIP_MOBILE'),
			'VIP_AUTH_FLAG'		=>	I('VIP_AUTH_FLAG'),
			'CARD_NO'			=>	I('CARD_NO'),
			'CREATE_TIME_A'		=>	I('CREATE_TIME_A'),
			'CREATE_TIME_B'		=>	I('CREATE_TIME_B'),
		);
		$where = "1=1";
		//归属
		if($post['BRANCH_MAP_ID']){
			$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
		}
		if($post['PARTNER_MAP_ID']){
			$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
			$where .= " and PARTNER_MAP_ID in (".$pids.")";
		}		
		//卡套餐
		if($post['VIP_CARD_FLAG'] !='') {
			if($post['VIP_CARD_FLAG'] == 1){
				$where .= " and (VIP_CARD_FLAG = '-' or VIP_CARD_FLAG = '1')";
			}else{
				$where .= " and VIP_CARD_FLAG = '".$post['VIP_CARD_FLAG']."'";
			}
		}
		//身份证号
		if($post['VIP_IDNO']) {
			if (strlen($post['VIP_IDNO']) == 15 || strlen($post['VIP_IDNO']) == 18) {
				$id_type = " and VIP_IDNOTYPE = '0'";
			}
			$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'".$id_type;
		}
		//手机号
		if($post['VIP_MOBILE']) {
			$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
		}
		//实名认证
		if($post['VIP_AUTH_FLAG']) {
			$where .= " and VIP_AUTH_FLAG = '".$post['VIP_AUTH_FLAG']."'";
		}
		//卡号
		if($post['CARD_NO']) {
			$where .= " and CARD_NO = '".$post['CARD_NO']."'";
		}

		//创建时间	开始
		if($post['CREATE_TIME_A']) {
			$where .= " and CREATE_TIME >= '".date('YmdHis',strtotime($post['CREATE_TIME_A'].'000000'))."'";
		}
		//创建时间	结束
		if($post['CREATE_TIME_B']) {
			$where .= " and CREATE_TIME <= '".date('YmdHis',strtotime($post['CREATE_TIME_B'].'235959'))."'";
		}
		//计算
		$count   = D($this->GVip)->countNewsVip($where);
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
			$list = D($this->GVip)->getNewsViplist($where, '*', $bRow.','.$eRow);
			
			//卡套餐
			$product1 = array(array('CARD_P_MAP_ID'=>'-', 'CARD_NAME'=>'虚拟卡'));   //贡
			$product2 = D($this->MCproduct)->getCproductlist_one('', 'CARD_P_MAP_ID,CARD_NAME');
			$product  = array_merge($product1, $product2);
			foreach($product as $val){
				$vip_card_flag[$val['CARD_P_MAP_ID']] = $val['CARD_NAME'];
			}
			//导出操作
			$xlsname = '会员资料文件('.($p+1).')';
			$xlscell = array(
				array('BRANCH_MAP_ID',	'归属'),
				array('PARTNER_MAP_ID',	'所属组织'),
				array('VIP_CARD_FLAG',	'卡套餐'),
				array('CARD_NO',		'卡号'),
				array('VIP_NAME',		'会员姓名'),
				array('VIP_MOBILE',		'会员手机'),
				array('VIP_IDNO',		'身份证号码'),
				array('VIP_CITY',		'所在城市'),
				array('VIP_SEX',		'性别'),
				array('VIP_BIRTHDAY',	'生日'),
				array('VIP_EMAIL',		'邮箱'),
				array('VIP_AUTH_FLAG',	'实名认证'),
				array('VIP_STATUS',		'状态'),
				array('CREATE_TIME',	'创建日期'),		
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'BRANCH_MAP_ID'	=>	get_branch_name($val['BRANCH_MAP_ID'], $val['PARTNER_MAP_ID']),
					'PARTNER_MAP_ID'=>	get_level_name($val['PARTNER_MAP_ID'], $val['BRANCH_MAP_ID']),
					'VIP_CARD_FLAG'	=>	$vip_card_flag[$val['VIP_CARD_FLAG']],
					'CARD_NO'		=>	$val['CARD_NO']."\t",
					'VIP_NAME'		=>	$val['VIP_NAME']."\t",
					'VIP_MOBILE'	=>	$val['VIP_MOBILE']."\t",
					'VIP_IDNO'		=>	$val['VIP_IDNO']."\t",
					'VIP_CITY'		=>	getcity_name($val['VIP_CITY']),
					'VIP_SEX'		=>	C('VIP_SEX')[$val['VIP_SEX']],
					'VIP_BIRTHDAY'	=>	$val['VIP_BIRTHDAY']."\t",
					'VIP_EMAIL'		=>	$val['VIP_EMAIL'],
					'VIP_AUTH_FLAG'	=>	C('VIP_AUTH_FLAG')[$val['VIP_AUTH_FLAG']],
					'VIP_STATUS'	=>	C('VIP_STATUS')[$val['VIP_STATUS']],
					'CREATE_TIME'	=>	$val['CREATE_TIME']."\t",
				);	
			}
			//dump($xlsarray);exit;
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			//会员姓名  性别  身份证号码  电话  邮箱  所属组织
			/*$xlscell = array(
				array('VIP_NAME',		'会员姓名'),
				array('VIP_MOBILE',		'会员手机'),
				array('VIP_SEX',		'性别'),
				array('VIP_IDNO',		'身份证号码'),
				array('VIP_EMAIL',		'邮箱'),
				array('PARTNER_MAP_ID',	'所属组织'),
				array('CREATE_TIME',	'创建日期'),		
			);
			$xlsarray = array();
			foreach($list as $val){
				$p_data = D('MPartner')->findPartner('PARTNER_MAP_ID = '.$val['PARTNER_MAP_ID'],'PARTNER_MAP_ID_P');
				$xlsarray[] = array(
					'VIP_NAME'		=>	$val['VIP_NAME'],
					'VIP_MOBILE'	=>	$val['VIP_MOBILE']."\t",
					'VIP_SEX'		=>	C('VIP_SEX')[$val['VIP_SEX']],
					'VIP_IDNO'		=>	$val['VIP_IDNO']."\t",
					'VIP_EMAIL'		=>	$val['VIP_EMAIL'],
					'BRANCH_MAP_ID'	=>	get_partner_name($p_data['PARTNER_MAP_ID_P']),
					'CREATE_TIME'	=>	$val['CREATE_TIME']."\t",
				);	
			}*/

			//D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	
	
	
	/*
	* 会员虚拟卡管理
	**/
	public function vfcard() {
		$post = I('post');		
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
				'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
				'CARD_NO'			=>	I('CARD_NO'),
				'VIP_IDNO'			=>	I('VIP_IDNO'),
				'VIP_MOBILE'		=>	I('VIP_MOBILE'),
			);
			$ajax_soplv = array(
				'bid'				=>	I('BRANCH_MAP_ID'),
				'pid'				=>	I('PARTNER_MAP_ID'),			
			);
		}
		//===结束=======	
		if($post['submit'] == "vfcard"){
			$where = "VIP_CARD_FLAG in ('1','-')";
			//归属
			//===优化统计===
			$getlevel = $ajax == 'loading' ? $ajax_soplv : filter_data('plv');	//列表查询
			//===结束=======
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
			}
			if($post['PARTNER_MAP_ID']){
				$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
				$where .= " and PARTNER_MAP_ID in (".$pids.")";
			}					
			//虚拟卡号
			if($post['CARD_NO'] !='') {
				$where .= " and CARD_NO = '".$post['CARD_NO']."'";
			}
			//身份证号
			if($post['VIP_IDNO']) {
				if (strlen($post['VIP_IDNO']) == 15 || strlen($post['VIP_IDNO']) == 18) {
					$id_type = " and VIP_IDNOTYPE = '0'";
				}
				$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'".$id_type;
			}
			//手机号
			if($post['VIP_MOBILE']) {
				$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
			}
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->GVip)->countNewsVip($where);
				$resdata = array(
					'count'	=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======			
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->GVip)->getNewsViplist($where, '*', $fiRow.','.$liRow);
			foreach($list as $key=>$val){
				$list[$key]['CREATE_TIME'] = date('Y-m-d H:i:s', strtotime($val['CREATE_TIME']));
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
		$this->assign('vip_status', 		C('VIP_STATUS'));		//会员状态
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 会员虚拟卡管理 详情	【跟 vinfo_show 一样】
	**/
	public function vfcard_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['VIP_BIRTHDAY'] = $info['VIP_BIRTHDAY'] ? date('Y-m-d', strtotime($info['VIP_BIRTHDAY'])) : '';
		$info['ID_PHOTO'] = explode(',', $info['ID_PHOTO']);
		//获取当前卡的金额
		$lapdata = D($this->GLap)->findLap("ACCT_NO = '".setStrzero($info['VIP_ID'], 9)."'");
		$info['ACCT_VALBAL'] = $lapdata['ACCT_VALBAL'];	//可用
		$info['ACCT_DIVBAL'] = $lapdata['ACCT_DIVBAL'];	//剩余
		$info['ACCT_YLTBAL'] = $lapdata['ACCT_YLTBAL'];	//养老已投总额
		$info['ACCT_YLVBAL'] = $lapdata['ACCT_YLVBAL'];	//养老可投金额
		$info['ACCT_YWTBAL'] = $lapdata['ACCT_YWTBAL'];	//意外已投总额
		$info['ACCT_YWVBAL'] = $lapdata['ACCT_YWVBAL'];	//意外可投金额				
		//绑定卡的卡费
		$info['CARD_OPENFEE'] = 0;
		$info['IS_BANG'] 	  = 0;	//0未绑定，1绑定	
		$vipcard  = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID']."' and CARD_STATUS = 0", 'CARD_P_MAP_ID');
		if(!empty($vipcard)){
			$feecfg = D($this->MFeecfg)->findFeecfg("CFG_FLAG = '".$vipcard['CARD_P_MAP_ID']."'", 'CARD_OPENFEE');
			$info['CARD_OPENFEE'] = setMoney($feecfg['CARD_OPENFEE'], '2', '2');
			$info['IS_BANG']	  = 1;
		}
		//子母卡
		if($info['VIP_DONATE'] == 1){
			$info_m = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID_M']."' and CARD_STATUS = 0", 'CARD_NO');
			$info['CARD_NO_M'] = $info_m['CARD_NO'];
		}
		$this->assign('vip_idnotype', 		C('VIP_IDNOTYPE'));		//证件类型
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别
		$this->assign('info', 				$info);
		$this->display('vinfo_show');
	}
	/*
	* 会员虚拟卡管理 绑定实体卡
	**/
	public function vfcard_bind() {
		$post = I('post');
		if($post['submit'] == "vfcard_bind") {
			//验证
			if(empty($post['CARD_NO']) || empty($post['VIP_CARD_FLAG']) || empty($post['V_VIP_CARD_FLAG']) || empty($post['CARD_CHECK'])){
				$this->wrong("缺少必填项数据！");
			}
			//检测卡号、验证码
			$findvipcard = D($this->GVipcard)->findVipcard("CARD_NO = '".$post['CARD_NO']."' and CARD_STATUS=1 and CARD_CHECK = '".$post['CARD_CHECK']."'");
			if(empty($findvipcard)){
				$this->wrong("卡号、校验码不通过！");
			}


			//卡号信息，检测卡号、验证码，
			/*$findvipcard = D($this->GVipcard)->findVipcard("BRANCH_MAP_ID = '".$post['V_BRANCH_MAP_ID']."' and PARTNER_MAP_ID = '".$post['V_PARTNER_MAP_ID']."' and CARD_NO = '".$post['CARD_NO']."' and CARD_STATUS=1 and CARD_CHECK = '".$post['CARD_CHECK']."'");
			if(empty($findvipcard)){
				$this->wrong("1.所绑卡片, 必须是当前发卡商所归属的卡片.<br />
							  2.卡号要与验码匹配.<br />
							  3.所绑卡片应该是库存状态.");
				//$this->wrong("卡号、校验码不通过！");
			}*/
			//组装数据
			$resdata = array(
				'CARD_NO'			=>	$post['CARD_NO'],
				'VIP_CARD_FLAG'		=>	$post['VIP_CARD_FLAG'],
				'UPDATE_TIME'		=>	date('YmdHis'),
			);
			//判断会员是否属于杭州总部（如果是，那做为虚拟卡）
			$vip_res = D($this->GVip)->findNewsVip("VIP_ID = '".$post['VIP_ID']."'", 'PARTNER_MAP_ID,VIP_CARD_FLAG');
			//判断卡套餐是否一样
			if ($vip_res['VIP_CARD_FLAG'] != 2) {
				$vip_card_flag = 3;
			}else{
				$vip_card_flag = 2;
			}
			if ($vip_card_flag != $findvipcard['CARD_P_MAP_ID']) {
				$this->wrong('当前卡片类型与会员现有的卡片类型不同，不能换卡');
			}
			if ($vip_res['PARTNER_MAP_ID'] == 4) {
				$post['V_VIP_CARD_FLAG'] = '-';
			}
			$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
			$m->startTrans();	//启用事务
			
			//等于-，vip的归属=卡的归属
			if($post['V_VIP_CARD_FLAG'] == '-'){
				$resdata['BRANCH_MAP_ID']  = $post['BRANCH_MAP_ID'];
				$resdata['PARTNER_MAP_ID'] = $post['PARTNER_MAP_ID'];
			}
			$res = D($this->GVip)->updateVip("VIP_ID = '".$post['VIP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			
			//绑定成功，改 vipcard 表状态
			$vcarddata = array(
				'VIP_ID'		=>	$post['VIP_ID'],	//vip表vip_id 覆盖vipcard表 vip_id
				'CARD_STATUS'	=>	0
			);			
			//等于1，卡的归属=vip的归属
			if($post['V_VIP_CARD_FLAG'] == 1){
				$vcarddata['BRANCH_MAP_ID']  = $post['V_BRANCH_MAP_ID'];
				$vcarddata['PARTNER_MAP_ID'] = $post['V_PARTNER_MAP_ID'];
			}
			$vcarddata['ACTIVE_TIME'] = date('YmdHis');		//激活时间
			$vcarddata['UPDATE_TIME'] = date('YmdHis');		//变更时间
			$res_vip = D($this->GVipcard)->updateVipcard("CARD_NO = '".$post['CARD_NO']."'", $vcarddata);
			if($res_vip['state'] != 0){
				$m->rollback();	//回滚
				$this->wrong('卡产品修改失败！');
			}
			
			//reg 同开户逻辑
			//如果是收费卡2，插入reg表
			if($resdata['VIP_CARD_FLAG'] == 2){
				//reg
				$feecreg = D($this->MFeecfg)->findFeecfg('CFG_FLAG = 2', 'TRAFICC_FEE');
				$regdata = array(
					'REG_TYPE'	=>	'101',
					'REG_INDEX'	=>	setStrzero($post['VIP_ID'], 9),
					'REG_DESC'	=>	$post['VIP_NAME'],
					'REG_AMT'	=>	$feecreg['TRAFICC_FEE'],
					'MARK_FLAG'	=>	1,
					'MARK_DATE'	=>	date('Ymd'),
				);
				D($this->MReg)->addReg($regdata);			
			}
			
			//同步修改会员数据
			$info = D($this->GVip)->findNewsVip("VIP_ID = '".$post['VIP_ID']."'");
			/* @author sea start */
			/*$url = VIP_PUSH_URL.'api/open/synchronize/member/modify';
			$data = array(
				'token' 	 	 => strtoupper(md5(strtoupper(md5($post['VIP_ID'].'2')))),		//(签名验证)
				'mId' 	 		 => $post['VIP_ID'],					//(会员ID)
				'mCId' 	 		 => $post['CARD_NO'],					//(卡号)
				'mName' 		 => $info['VIP_NAME'],					//(会员姓名)
				'mIdentityType'	 => $info['VIP_IDNOTYPE'],				//(证件类型)
				'mIdentityId' 	 => $info['VIP_IDNO'],					//(证件号)
				'mBirthday'		 => date('Ymd',strtotime($info['VIP_BIRTHDAY'])),				//(会员生日)
				'mMobile' 		 => $info['VIP_MOBILE'],				//(手机号码)
				'gender' 		 => $info['VIP_SEX'],					//(会员性别)
				'mCurrentCity'	 => getcity_name($info['VIP_CITY']),	//(所在城市)
				'mNativeAddress' => $info['VIP_ADDRESS'],				//(户口地址)
				'mEmail' 		 => $info['VIP_EMAIL'],					//(会员邮箱)
				'operateType'	 => '2'									//(操作类型)
			);
			Add_LOG(CONTROLLER_NAME, json_encode($data));
			$resjson = httpPostForm($url,$data);
			Add_LOG(CONTROLLER_NAME, $resjson);
			$result = json_decode($resjson);
			if ($result->code != '0') {
				$m->rollback();	//回滚
				$this->wrong('会员数据同步修改失败');
			}*/
			/* @author sea end */
			$m->commit();	//成功	
			//发短信
			if($info['VIP_MOBILE'] && ($info['VIP_CARD_FLAG']==3 or $info['VIP_CARD_FLAG']==2)){
				//短信模板
				$model_arr = setSmsmodel(2);
				//短信流水
				$smsls = array(
					'BRANCH_MAP_ID'		=>	$info['BRANCH_MAP_ID'],
					'PARTNER_MAP_ID'	=>	$info['PARTNER_MAP_ID'],
					'SMS_MODEL_TYPE'	=>	'2',
					'VIP_FLAG'			=>	$info['VIP_CARD_FLAG'],
					'VIP_ID'			=>	$info['VIP_ID'],
					'VIP_CARDNO'		=>	$info['CARD_NO'],
					'SMS_RECV_MOB'		=>	$info['VIP_MOBILE'],
					'SMS_RECV_NAME'		=>	$info['VIP_NAME'],
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
			$this->right('绑定成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['VIP_BIRTHDAY'] = $info['VIP_BIRTHDAY'] ? date('Y-m-d', strtotime($info['VIP_BIRTHDAY'])) : '';
		$info['ID_PHOTO'] = explode(',', $info['ID_PHOTO']);
		//获取当前卡的金额
		$lapdata = D($this->GLap)->findLap("ACCT_NO = '".setStrzero($info['VIP_ID'], 9)."'");
		$info['ACCT_VALBAL'] = $lapdata['ACCT_VALBAL'];	//可用
		$info['ACCT_DIVBAL'] = $lapdata['ACCT_DIVBAL'];	//剩余
		$info['ACCT_YLTBAL'] = $lapdata['ACCT_YLTBAL'];	//养老已投总额
		$info['ACCT_YLVBAL'] = $lapdata['ACCT_YLVBAL'];	//养老可投金额
		$info['ACCT_YWTBAL'] = $lapdata['ACCT_YWTBAL'];	//意外已投总额
		$info['ACCT_YWVBAL'] = $lapdata['ACCT_YWVBAL'];	//意外可投金额				
		//绑定卡的卡费
		$info['CARD_OPENFEE'] = 0;
		$info['IS_BANG'] 	  = 0;	//0未绑定，1绑定	
		$vipcard  = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID']."' and CARD_STATUS = 0", 'CARD_P_MAP_ID');
		if(!empty($vipcard)){
			$feecfg = D($this->MFeecfg)->findFeecfg("CFG_FLAG = '".$vipcard['CARD_P_MAP_ID']."'", 'CARD_OPENFEE');
			$info['CARD_OPENFEE'] = setMoney($feecfg['CARD_OPENFEE'], '2', '2');
			$info['IS_BANG']	  = 1;
		}
		//子母卡
		if($info['VIP_DONATE'] == 1){
			$info_m = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID_M']."' and CARD_STATUS = 0", 'CARD_NO');
			$info['CARD_NO_M'] = $info_m['CARD_NO'];
		}
		$this->assign('vip_idnotype', 		C('VIP_IDNOTYPE'));		//证件类型
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 会员虚拟卡管理 导出
	**/
	public function vfcard_export() {
		$post  = array(
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'CARD_NO'			=>	I('CARD_NO'),
			'VIP_IDNO'			=>	I('VIP_IDNO'),
			'VIP_MOBILE'		=>	I('VIP_MOBILE'),
		);
		$where = "VIP_CARD_FLAG in ('1','-')";
		//归属
		if($post['BRANCH_MAP_ID']){
			$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
		}
		if($post['PARTNER_MAP_ID']){
			$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
			$where .= " and PARTNER_MAP_ID in (".$pids.")";
		}		
		//虚拟卡号
		if($post['CARD_NO'] !='') {
			$where .= " and CARD_NO = '".$post['CARD_NO']."'";
		}
		//身份证号
		if($post['VIP_IDNO']) {
			if (strlen($post['VIP_IDNO']) == 15 || strlen($post['VIP_IDNO']) == 18) {
				$id_type = " and VIP_IDNOTYPE = '0'";
			}
			$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'".$id_type;
		}
		//手机号
		if($post['VIP_MOBILE']) {
			$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
		}
		
		//计算
		$count   = D($this->GVip)->countNewsVip($where);
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
			$list = D($this->GVip)->getNewsViplist($where, '*', $bRow.','.$eRow);
		
			//导出操作
			$xlsname = '会员虚拟卡文件('.($p+1).')';
			$xlscell = array(
				array('BRANCH_MAP_ID',	'归属'),
				array('VIP_NAME',		'会员姓名'),
				array('CARD_NO',		'虚拟卡号'),
				array('VIP_CITY',		'所在城市'),
				array('VIP_SEX',		'性别'),
				array('VIP_BIRTHDAY',	'生日'),
				array('VIP_STATUS',		'状态'),
				array('CREATE_TIME',	'创建日期'),		
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'BRANCH_MAP_ID'	=>	get_branch_name($val['BRANCH_MAP_ID'], $val['PARTNER_MAP_ID']),
					'VIP_NAME'		=>	$val['VIP_NAME'],
					'CARD_NO'		=>	$val['CARD_NO']."\t",
					'VIP_CITY'		=>	getcity_name($val['VIP_CITY'],'1'),
					'VIP_SEX'		=>	C('VIP_SEX')[$val['VIP_SEX']],
					'VIP_BIRTHDAY'	=>	$val['VIP_BIRTHDAY']."\t",
					'VIP_STATUS'	=>	C('VIP_STATUS')[$val['VIP_STATUS']],
					'CREATE_TIME'	=>	$val['CREATE_TIME']."\t",
				);	
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	
	
	
	/*
	* 实体卡管理
	**/
	public function vtcard() {
		$post = I('post');				
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
				'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
				'VIP_CARD_FLAG'		=>	I('VIP_CARD_FLAG'),
				'CARD_NO'			=>	I('CARD_NO'),
				'VIP_IDNO'			=>	I('VIP_IDNO'),
				'VIP_MOBILE'		=>	I('VIP_MOBILE'),
				'VIP_DONATE'		=>	I('VIP_DONATE'),
			);
			$ajax_soplv = array(
				'bid'				=>	$post['BRANCH_MAP_ID'],
				'pid'				=>	$post['PARTNER_MAP_ID'],			
			);
		}
		//===结束=======	
		if($post['submit'] == "vtcard"){
			$where = "VIP_CARD_FLAG in ('2','3')";
			//归属
			//===优化统计===
			$getlevel = $ajax == 'loading' ? $ajax_soplv : filter_data('plv');	//列表查询
			//===结束=======
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
			}
			if($post['PARTNER_MAP_ID']){
				$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
				$where .= " and PARTNER_MAP_ID in (".$pids.")";
			}			
			//卡套餐
			if($post['VIP_CARD_FLAG']) {
				if($post['VIP_CARD_FLAG'] == 1){
					$where .= " and (VIP_CARD_FLAG = '-' or VIP_CARD_FLAG = '1')";
				}else{
					$where .= " and VIP_CARD_FLAG = '".$post['VIP_CARD_FLAG']."'";
				}
			}
			//实体卡卡号
			if($post['CARD_NO'] !='') {
				$where .= " and CARD_NO = '".$post['CARD_NO']."'";
			}
			//身份证号
			if($post['VIP_IDNO']) {
				if (strlen($post['VIP_IDNO']) == 15 || strlen($post['VIP_IDNO']) == 18) {
					$id_type = " and VIP_IDNOTYPE = '0'";
				}
				$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'".$id_type;
			}
			//手机号
			if($post['VIP_MOBILE']) {
				$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
			}
			//子母卡
			if($post['VIP_DONATE']) {
				$where .= " and VIP_DONATE = '".$post['VIP_DONATE']."'";
			}			
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->GVip)->countNewsVip($where);
				$resdata = array(
					'count'	=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======			
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->GVip)->getNewsViplist($where, '*', $fiRow.','.$liRow);
			foreach($list as $key=>$val){
				$list[$key]['CREATE_TIME'] = date('Y-m-d H:i:s', strtotime($val['CREATE_TIME']));
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
		//卡套餐
		$product = D($this->MCproduct)->getCproductlist_one('', 'CARD_P_MAP_ID,CARD_NAME');
		foreach($product as $val){
			$vip_card_flag[$val['CARD_P_MAP_ID']] = $val['CARD_NAME'];
		}		
		$this->assign('vip_card_flag', 		$vip_card_flag);		//卡套餐
		$this->assign('vip_donate', 		C('VIP_DONATE'));		//子母卡		
		$this->assign('vip_status', 		C('VIP_STATUS'));		//会员状态
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 实体卡管理 详情	【跟 vinfo_show 一样】
	**/
	public function vtcard_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['VIP_BIRTHDAY'] = $info['VIP_BIRTHDAY'] ? date('Y-m-d', strtotime($info['VIP_BIRTHDAY'])) : '';
		$info['ID_PHOTO'] = explode(',', $info['ID_PHOTO']);
		//获取当前卡的金额
		$lapdata = D($this->GLap)->findLap("ACCT_NO = '".setStrzero($info['VIP_ID'], 9)."'");
		$info['ACCT_VALBAL'] = $lapdata['ACCT_VALBAL'];	//可用
		$info['ACCT_DIVBAL'] = $lapdata['ACCT_DIVBAL'];	//剩余
		$info['ACCT_YLTBAL'] = $lapdata['ACCT_YLTBAL'];	//养老已投总额
		$info['ACCT_YLVBAL'] = $lapdata['ACCT_YLVBAL'];	//养老可投金额
		$info['ACCT_YWTBAL'] = $lapdata['ACCT_YWTBAL'];	//意外已投总额
		$info['ACCT_YWVBAL'] = $lapdata['ACCT_YWVBAL'];	//意外可投金额				
		//绑定卡的卡费
		$info['CARD_OPENFEE'] = 0;
		$info['IS_BANG'] 	  = 0;	//0未绑定，1绑定	
		$vipcard  = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID']."' and CARD_STATUS = 0", 'CARD_P_MAP_ID');
		if(!empty($vipcard)){
			$feecfg = D($this->MFeecfg)->findFeecfg("CFG_FLAG = '".$vipcard['CARD_P_MAP_ID']."'", 'CARD_OPENFEE');
			$info['CARD_OPENFEE'] = setMoney($feecfg['CARD_OPENFEE'], '2', '2');
			$info['IS_BANG']	  = 1;
		}
		//子母卡
		if($info['VIP_DONATE'] == 1){
			$info_m = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID_M']."' and CARD_STATUS = 0", 'CARD_NO');
			$info['CARD_NO_M'] = $info_m['CARD_NO'];
		}
		$this->assign('vip_idnotype', 		C('VIP_IDNOTYPE'));		//证件类型
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别
		$this->assign('info', 				$info);
		$this->display('vinfo_show');
	}
	/*
	* 实体卡管理 换卡	改卡归属
	**/
	public function vtcard_exch() {
		$post = I('post');
		if($post['submit'] == "vtcard_exch") {
			//验证
			if(empty($post['VIP_ID']) || empty($post['CARD_NO']) || empty($post['VIP_CARD_FLAG']) || empty($post['CARD_CHECK'])){
				$this->wrong("缺少必填项数据！");
			}
			//检测卡号、验证码	新卡(换卡：卡归属与当前登录用户归属匹配，不需要与会员归属匹配，换卡后，卡归属同会员归属)
			$findvipcard = D($this->GVipcard)->findVipcard("CARD_NO = '".$post['CARD_NO']."' and CARD_STATUS=1 and CARD_CHECK = '".$post['CARD_CHECK']."'");
			if(empty($findvipcard)){
				$this->wrong("卡号、校验码不通过！");
			}
			/*$home = session('HOME');
			if ($home['PARTNER_MAP_ID'] != $findvipcard['PARTNER_MAP_ID']) {
				$this->wrong("此卡片归属与当前登录账户归属不匹配！");
			}*/

			//判断会员是否属于杭州总部（如果是，那做为虚拟卡,换卡后以会员以卡归属为准）
			$info = D($this->GVip)->findNewsVip("VIP_ID = '".$post['VIP_ID']."'");

			//判断卡套餐是否一样
			if ($info['VIP_CARD_FLAG'] != 2) {
				$vip_card_flag = 3;
			}else{
				$vip_card_flag = 2;
			}
			if ($vip_card_flag != $findvipcard['CARD_P_MAP_ID']) {
				$this->wrong('当前卡片类型与会员现有的卡片类型不同，不能换卡');
			}
			if ($info['PARTNER_MAP_ID'] == 4) {
				//组装数据
				$resdata = array(
					'BRANCH_MAP_ID'		=>	$findvipcard['BRANCH_MAP_ID'],
					'PARTNER_MAP_ID'	=>	$findvipcard['PARTNER_MAP_ID'],
					'CARD_NO'			=>	$post['CARD_NO'],
					'VIP_CARD_FLAG'		=>	$post['VIP_CARD_FLAG'],
					'UPDATE_TIME'		=>	date('YmdHis'),
				);
				$post['V_BRANCH_MAP_ID'] = $findvipcard['BRANCH_MAP_ID'];
				$post['V_PARTNER_MAP_ID'] = $findvipcard['PARTNER_MAP_ID'];
			}else{
				//组装数据
				$resdata = array(
					'CARD_NO'			=>	$post['CARD_NO'],
					'VIP_CARD_FLAG'		=>	$post['VIP_CARD_FLAG'],
					'UPDATE_TIME'		=>	date('YmdHis'),
				);
			}
			
			$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
			$m->startTrans();	//启用事务			
			
			$res = D($this->GVip)->updateVip("VIP_ID = '".$post['VIP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//绑定成功，改 vipcard
			$old_card = D($this->GVipcard)->findVipcard("VIP_ID = '".$post['VIP_ID']."'");
			$old_card['CARD_STATUS'] = 3;
			$res_tmp  = D($this->GVipcard)->addVipcard_tmp($old_card);
			if($res_tmp['state'] != 0){
				$m->rollback();	//回滚
				$this->wrong('换卡失败！');
			}
			$res_del  = D($this->GVipcard)->delVipcard("CARD_NO = '".$old_card['CARD_NO']."'");
			if($res_del['state'] != 0){
				$m->rollback();	//回滚
				$this->wrong('换卡失败！');
			}

			$new_card = array(
				'BRANCH_MAP_ID'		=> $post['V_BRANCH_MAP_ID'],
				'BRANCH_MAP_ID1'	=> $findvipcard['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=> $post['V_PARTNER_MAP_ID'],
				'PARTNER_MAP_ID1'	=> $findvipcard['PARTNER_MAP_ID'],
				'VIP_ID'			=> $post['VIP_ID'],
				'CARD_STATUS'		=> 0,
				'UPDATE_TIME'		=> date('YmdHis')		//更新时间
			);
			$res_new = D($this->GVipcard)->updateVipcard("CARD_NO = '".$post['CARD_NO']."'", $new_card);
			if($res_new['state'] != 0){
				$m->rollback();	//回滚
				$this->wrong('修改卡产品失败！');
			}

			//同步修改会员数据
			//$info = D($this->GVip)->findNewsVip("VIP_ID = '".$post['VIP_ID']."'");
			/* @author sea start */
			/*$url = VIP_PUSH_URL.'api/open/synchronize/member/modify';
			$data = array(
				'token' 	 	 => strtoupper(md5(strtoupper(md5($post['VIP_ID'].'2')))),		//(签名验证)
				'mId' 	 		 => $post['VIP_ID'],					//(会员ID)
				'mCId' 	 		 => $post['CARD_NO'],					//(卡号)
				'mName' 		 => $info['VIP_NAME'],					//(会员姓名)
				'mIdentityType'	 => $info['VIP_IDNOTYPE'],				//(证件类型)
				'mIdentityId' 	 => $info['VIP_IDNO'],					//(证件号)
				'mBirthday'		 => date('Ymd',strtotime($info['VIP_BIRTHDAY'])),				//(会员生日)
				'mMobile' 		 => $info['VIP_MOBILE'],				//(手机号码)
				'gender' 		 => $info['VIP_SEX'],					//(会员性别)
				'mCurrentCity'	 => getcity_name($info['VIP_CITY']),	//(所在城市)
				'mNativeAddress' => $info['VIP_ADDRESS'],				//(户口地址)
				'mEmail' 		 => $info['VIP_EMAIL'],					//(会员邮箱)
				'operateType'	 => '2'									//(操作类型)
			);
			Add_LOG(CONTROLLER_NAME, json_encode($data));
			$resjson = httpPostForm($url,$data);
			Add_LOG(CONTROLLER_NAME, $resjson);
			$result = json_decode($resjson);
			if ($result->code != '0') {
				$m->rollback();	//回滚
				$this->wrong('会员数据同步修改失败');
			}*/
			/* @author sea end */
			$m->commit();	//成功
			
			$this->right('换卡成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//过滤
		if(empty($info['CARD_NO'])){
			$this->wrong("该卡还未绑定，请先绑定！");
		}
		$info['VIP_BIRTHDAY'] = $info['VIP_BIRTHDAY'] ? date('Y-m-d', strtotime($info['VIP_BIRTHDAY'])) : '';
		$info['ID_PHOTO'] = explode(',', $info['ID_PHOTO']);
		//获取当前卡的金额
		$lapdata = D($this->GLap)->findLap("ACCT_NO = '".setStrzero($info['VIP_ID'], 9)."'");
		$info['ACCT_VALBAL'] = $lapdata['ACCT_VALBAL'];	//可用
		$info['ACCT_DIVBAL'] = $lapdata['ACCT_DIVBAL'];	//剩余
		$info['ACCT_YLTBAL'] = $lapdata['ACCT_YLTBAL'];	//养老已投总额
		$info['ACCT_YLVBAL'] = $lapdata['ACCT_YLVBAL'];	//养老可投金额
		$info['ACCT_YWTBAL'] = $lapdata['ACCT_YWTBAL'];	//意外已投总额
		$info['ACCT_YWVBAL'] = $lapdata['ACCT_YWVBAL'];	//意外可投金额				
		//绑定卡的卡费
		$info['CARD_OPENFEE'] = 0;
		$info['IS_BANG'] 	  = 0;	//0未绑定，1绑定	
		$vipcard  = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID']."' and CARD_STATUS = 0", 'CARD_P_MAP_ID');
		if(!empty($vipcard)){
			$feecfg = D($this->MFeecfg)->findFeecfg("CFG_FLAG = '".$vipcard['CARD_P_MAP_ID']."'", 'CARD_OPENFEE');
			$info['CARD_OPENFEE'] = setMoney($feecfg['CARD_OPENFEE'], '2', '2');
			$info['IS_BANG']	  = 1;
		}
		//子母卡
		if($info['VIP_DONATE'] == 1){
			$info_m = D($this->GVipcard)->findVipcard("VIP_ID = '".$info['VIP_ID_M']."' and CARD_STATUS = 0", 'CARD_NO');
			$info['CARD_NO_M'] = $info_m['CARD_NO'];
		}
		$this->assign('vip_idnotype', 		C('VIP_IDNOTYPE'));		//证件类型
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 实体卡管理 修改卡套餐
	**/
	public function vtcard_edit() {
		$post = I('post');
		if($post['submit'] == "vtcard_edit") {
			//验证
			if(empty($post['VIP_ID']) || empty($post['VIP_CARD_FLAG'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'VIP_CARD_FLAG'		=>	$post['VIP_CARD_FLAG'],
				'UPDATE_TIME'		=>	date('YmdHis')
			);
			$res = D($this->GVip)->updateVip("VIP_ID = '".$post['VIP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['VIP_BIRTHDAY'] = $info['VIP_BIRTHDAY'] ? date('Y-m-d', strtotime($info['VIP_BIRTHDAY'])) : '';
		//卡套餐
		$product = D($this->MCproduct)->getCproductlist_one('', 'CARD_P_MAP_ID,CARD_NAME');
		foreach($product as $val){
			$vip_card_flag[$val['CARD_P_MAP_ID']] = $val['CARD_NAME'];
		}		
		$this->assign('vip_card_flag', 		$vip_card_flag);		//卡套餐
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 实体卡管理 注销
	**/
	public function vtcard_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//改 vip 表
		$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		$m->startTrans();	//启用事务	
		$res = D($this->GVip)->updateVip("VIP_ID = '".$info['VIP_ID']."'", array('VIP_STATUS'=>	9));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		//检查绑卡没有
		if($info['CARD_NO']){
			//改 vipcard 表
			$resv = D($this->GVipcard)->updateVipcard("CARD_NO = '".$info['CARD_NO']."'", array('CARD_STATUS'=>	9,'UPDATE_TIME' => date('YmdHis')));
			if($resv['state'] != 0){
				$m->rollback();	//回滚
				$this->wrong($resv['msg']);
			}			
		}
		//同步修改会员数据
		/* @author sea start */
		/*$url = VIP_PUSH_URL.'api/open/synchronize/member/logout';
		$data = array(
			'token' 	 	 => strtoupper(md5(strtoupper(md5($id.'3')))),	//(签名验证)
			'mId' 	 		 => $id,							//(会员ID)
			'operateType'	 => '3'								//(操作类型)
		);
		Add_LOG(CONTROLLER_NAME, json_encode($data));
		$resjson = httpPostForm($url,$data);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->code != '0') {
			$m->rollback();	//回滚
			$this->wrong('会员注销失败');
		}*/
		/* @author sea end */
		$m->commit();	//成功
		$this->right('注销成功！');
	}

	/*
	* 实体卡管理 删除
	**/
	public function vtcard_clear() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//改 vip 表
		$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		$m->startTrans();	//启用事务
		$upvipdata = array(
			'VIP_STATUS' => '9', 
			'VIP_MOBILE' => '9'.$info['VIP_ID'], 
			'CARD_NO' 	 => '9'.$info['VIP_ID'], 
			'VIP_IDNO' 	 => '9'.$info['VIP_ID'],
			'RES' 	 	 => $info['VIP_MOBILE'].','.$info['CARD_NO']
		);
		$res = D($this->GVip)->updateVip("VIP_ID = '".$info['VIP_ID']."'", $upvipdata);
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		//检查绑卡没有
		if($info['CARD_NO']){
			//改 vipcard 表
			$resv = D($this->GVipcard)->updateVipcard("CARD_NO = '".$info['CARD_NO']."'", array('CARD_STATUS'=>	9,'UPDATE_TIME' => date('YmdHis')));
			if($resv['state'] != 0){
				$m->rollback();	//回滚
				$this->wrong($resv['msg']);
			}			
		}
		//同步修改会员数据
		/* @author sea start */
		/*$url = VIP_PUSH_URL.'api/open/synchronize/member/logout';
		$data = array(
			'token' 	 	 => strtoupper(md5(strtoupper(md5($id.'3')))),	//(签名验证)
			'mId' 	 		 => $id,							//(会员ID)
			'operateType'	 => '3'								//(操作类型)
		);
		Add_LOG(CONTROLLER_NAME, json_encode($data));
		$resjson = httpPostForm($url,$data);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->code != '0') {
			$m->rollback();	//回滚
			$this->wrong('会员注销失败');
		}*/
		/* @author sea end */
		$m->commit();	//成功
		$this->right('注销成功！');
	}
	/*
	* 实体卡管理 导出
	**/
	public function vtcard_export() {
		$post  = array(
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'VIP_CARD_FLAG'		=>	I('VIP_CARD_FLAG'),
			'CARD_NO'			=>	I('CARD_NO'),
			'VIP_IDNO'			=>	I('VIP_IDNO'),
			'VIP_MOBILE'		=>	I('VIP_MOBILE'),
			'VIP_DONATE'		=>	I('VIP_DONATE'),
		);
		$where = "VIP_CARD_FLAG in ('2','3')";
		//归属
		if($post['BRANCH_MAP_ID']){
			$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
		}
		if($post['PARTNER_MAP_ID']){
			$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
			$where .= " and PARTNER_MAP_ID in (".$pids.")";
		}
		//卡套餐
		if($post['VIP_CARD_FLAG']) {
			if($post['VIP_CARD_FLAG'] == 1){
				$where .= " and (VIP_CARD_FLAG = '-' or VIP_CARD_FLAG = '1')";
			}else{
				$where .= " and VIP_CARD_FLAG = '".$post['VIP_CARD_FLAG']."'";
			}
		}
		//实体卡卡号
		if($post['CARD_NO'] !='') {
			$where .= " and CARD_NO = '".$post['CARD_NO']."'";
		}
		//身份证号
		if($post['VIP_IDNO']) {
			if (strlen($post['VIP_IDNO']) == 15 || strlen($post['VIP_IDNO']) == 18) {
				$id_type = " and VIP_IDNOTYPE = '0'";
			}
			$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'".$id_type;
		}
		//手机号
		if($post['VIP_MOBILE']) {
			$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
		}
		//子母卡
		if($post['VIP_DONATE']) {
			$where .= " and VIP_DONATE = '".$post['VIP_DONATE']."'";
		}
		
		//计算
		$count   = D($this->GVip)->countNewsVip($where);
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
			$list = D($this->GVip)->getNewsViplist($where, '*', $bRow.','.$eRow);
		
			//卡套餐
			$product1 = array(array('CARD_P_MAP_ID'=>'-', 'CARD_NAME'=>'虚拟卡'));		//贡
			$product2 = D($this->MCproduct)->getCproductlist_one('', 'CARD_P_MAP_ID,CARD_NAME');
			$product  = array_merge($product1, $product2);
			foreach($product as $val){
				$vip_card_flag[$val['CARD_P_MAP_ID']] = $val['CARD_NAME'];
			}
			//导出操作
			$xlsname = '会员实体卡文件('.($p+1).')';
			$xlscell = array(
				array('BRANCH_MAP_ID',	'归属'),
				array('VIP_NAME',		'会员姓名'),
				array('CARD_NO',		'卡号'),
				array('VIP_CARD_FLAG',	'卡套餐'),
				array('VIP_CITY',		'所在城市'),
				array('VIP_SEX',		'性别'),
				array('VIP_BIRTHDAY',	'生日'),
				array('VIP_DONATE',		'子母卡'),
				array('VIP_STATUS',		'状态'),
				array('CREATE_TIME',	'创建日期'),		
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'BRANCH_MAP_ID'	=>	get_branch_name($val['BRANCH_MAP_ID'], $val['PARTNER_MAP_ID']),
					'VIP_NAME'		=>	$val['VIP_NAME'],
					'CARD_NO'		=>	$val['CARD_NO']."\t",
					'VIP_CARD_FLAG'	=>	$vip_card_flag[$val['VIP_CARD_FLAG']],
					'VIP_CITY'		=>	getcity_name($val['VIP_CITY'],'1'),
					'VIP_SEX'		=>	C('VIP_SEX')[$val['VIP_SEX']],
					'VIP_BIRTHDAY'	=>	$val['VIP_BIRTHDAY']."\t",
					'VIP_DONATE'	=>	C('VIP_DONATE')[$val['VIP_DONATE']],
					'VIP_STATUS'	=>	C('VIP_STATUS')[$val['VIP_STATUS']],
					'CREATE_TIME'	=>	$val['CREATE_TIME']."\t",
				);	
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}

	/*
	* 会员申请商户列表
	**/
	public function shopapply() {
		$post = I('post');
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'APPLY_SHOPNAME'	=>	I('APPLY_SHOPNAME'),
				'APPLY_NAME'		=>	I('APPLY_NAME'),
				'APPLY_MOBILE'		=>	I('APPLY_MOBILE'),
			);
		}
		//===结束=======	
		if($post['submit'] == "shopapply"){
			$where = "1=1";
			//手机号
			if($post['APPLY_MOBILE'] != '') {
				$where .= " and APPLY_MOBILE = '".$post['APPLY_MOBILE']."'";
			}
			//商户名称
			if($post['APPLY_SHOPNAME']) {
				$where .= " and APPLY_SHOPNAME like '%".$post['APPLY_SHOPNAME']."%'";
			}
			//会员名称
			if($post['APPLY_NAME']) {
				$where .= " and APPLY_NAME like '%".$post['APPLY_NAME']."%'";
			}	
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->MShopapply)->countShopapply($where);
				$resdata = array(
					'count'	=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======			
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->MShopapply)->getShopapplylist($where, '*', $fiRow.','.$liRow);	
						
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
		$this->assign('apply_status', 		C('APPLY_STATUS'));		//联系状态
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}

	/*
	* 会员申请商户 详情
	**/
	public function shopapply_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MShopapply)->findShopapply("APPLY_MAP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('apply_status', 		C('APPLY_STATUS'));		//联系状态
		$this->assign('info', 				$info);
		$this->display();
	}

	/*
	* 会员申请商户 修改
	**/
	public function shopapply_edit() {
		$post = I('post');
		if($post['submit'] == "shopapply_edit") {
			//验证
			if(empty($post['APPLY_MAP_ID'])){
				$this->wrong("参数数据出错");
			}
			//组装数据
			$resdata = array(
				'APPLY_STATUS'		=>	$post['APPLY_STATUS'],
				'RES'				=>	$post['RES']
			);
			$res = D($this->MShopapply)->updateShopapply("APPLY_MAP_ID = '".$post['APPLY_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MShopapply)->findShopapply("APPLY_MAP_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('info', 	$info);
		$this->display();
	}
	//手动同步会员信息
	public function custom_sync_member(){
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//检测会员是否存在
		$findvip = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($findvip)){
			$this->wrong("会员信息不存在！");
		}
		//同步新增会员数据
		/* @author sea start */
		/*$url = VIP_PUSH_URL.'api/open/synchronize/member/register';
		$data = array(
			'token' 	 	 => strtoupper(md5(strtoupper(md5($findvip['VIP_ID'].'1')))),	//(签名验证)
			'mId' 	 		 => $findvip['VIP_ID'],					//(会员ID)
			'mCId' 	 		 => $findvip['CARD_NO'],				//(卡号)
			'mName' 		 => $findvip['VIP_NAME'],				//(会员姓名)
			'mIdentityType'	 => $findvip['VIP_IDNOTYPE'],			//(证件类型)
			'mIdentityId' 	 => $findvip['VIP_IDNO'],				//(证件号)
			'mBirthday'		 => $findvip['VIP_BIRTHDAY'],			//(会员生日)
			'mMobile' 		 => $findvip['VIP_MOBILE'],				//(手机号码)
			'gender' 		 => $findvip['VIP_SEX'],				//(会员性别)
			'mCurrentCity'	 => getcity_name($findvip['VIP_CITY']),	//(所在城市)
			'mNativeAddress' => $findvip['VIP_ADDRESS'],			//(户口地址)
			'mEmail' 		 => $findvip['VIP_EMAIL'],				//(会员邮箱)
			'operateType'	 => '1',								//(操作类型)
			'migrateType'	 => '2',								//(迁移区分)
			'mRemark'	 	 => 'glzxNew'
		);
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode($data));
		$resjson = httpPostForm($url,$data);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->code != '0') {
			$this->wrong('会员数据同步失败');
		}*/
		/* @author sea end */
		$this->right('同步成功！');
	}

	//初始化卡片信息（用于当登录次数超出上限，恢复）
	public function init_card_pintime(){
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//检测手机号
		$findvip = D($this->GVip)->findNewsVip("VIP_ID = '".$id."'");
		if(empty($findvip)){
			$this->wrong("会员信息不存在！");
		}
		//更新错误次数字段
		$up_pintime = D($this->GVip)->updateVip("VIP_ID = '".$id."'", array('VIP_PINTIME' => 0 ));
		if ($up_pintime['state'] != '0') {
			$this->wrong('初始化失败');
		}
		$this->right('初始化成功！');
	}
}
