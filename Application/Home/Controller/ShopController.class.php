<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @ljf  商户管理
// +----------------------------------------------------------------------
class ShopController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->MShop	= 'MShop';
		$this->MScert	= 'MScert';
		$this->MSauth	= 'MSauth';
		$this->MSmdr	= 'MSmdr';
		$this->MSbact	= 'MSbact';
		$this->MSrisk	= 'MSrisk';
		$this->MSdkb	= 'MSdkb';
		$this->MScfg	= 'MScfg';
		$this->MSposreq	= 'MSposreq';
		$this->MPos		= 'MPos';
		$this->MBranch	= 'MBranch';
		$this->MPartner	= 'MPartner';
		$this->MCity	= 'MCity';
		$this->MCheck	= 'MCheck';
		$this->MHost 	= 'MHost';
		$this->MShopppp	= 'MShopppp';
		$this->MPosppp	= 'MPosppp';
		$this->MChannel	= 'MChannel';
		$this->GLae		= 'GLae';
		$this->TDkls	= 'TDkls';
		$this->MExcel 	= 'MExcel';
		$this->MSdfb 	= 'MSdfb';
		$this->MBank	= 'MBank';
		$this->MSbank	= 'MSbank';
		$this->MGrade	= 'MGrade';
		$this->MGradefee= 'MGradefee';
	}
	
	/*
	* 商户管理 列表
	**/
	public function shop() {
		$home = session('HOME');
		$post = I('post');
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'		=>	I('submit'),
				'bid'			=>	I('bid'),
				'pid'			=>	I('pid'),
				'SHOP_STATUS'	=>	I('SHOP_STATUS'),
				'MCC_TYPE'		=>	I('MCC_TYPE'),
				'MCC_CODE'		=>	I('MCC_CODE'),
				'POS_STATUS'	=>	I('POS_STATUS'),
				'SHOP_NO'		=>	I('SHOP_NO'),
				'SHOP_NAME'		=>	I('SHOP_NAME'),
				'SHOP_NAMEABCN'	=>	I('SHOP_NAMEABCN'),
				'SHOP_MAP_ID'	=>	I('SHOP_MAP_ID'),
			);
			$ajax_soplv = array(
				'bid'			=>	$post['bid'],
				'pid'			=>	$post['pid'],			
			);
		}
		//===结束=======
		if($post['submit'] == "shop"){
			$where = "1=1";
			//归属推荐个创
			if ($home['USER_LEVEL']==4) {
				$where .= " and PARTNER_MAP_ID_R = '".$home['PARTNER_MAP_ID']."'";
				$post['bid'] = $home['BRANCH_MAP_ID'];
				$post['pid'] = $home['PARTNER_MAP_ID'];
			}else{
				//分支
				//===优化统计===
				$soplv = $ajax == 'loading' ? $ajax_soplv : filter_data('soplv');	//列表查询
				//===结束=======
				if($soplv['bid'] != '') {
					$where .= " and BRANCH_MAP_ID = '".$soplv['bid']."'";
					$post['bid'] = $soplv['bid'];
				}
				//合作伙伴
				if($soplv['pid'] != '') {
					$pids = get_plv_childs($soplv['pid'],1);
					$where .= " and PARTNER_MAP_ID in(".$pids.")";
					$post['pid'] = $soplv['pid'];
				}
			}
			if($home['HOST_MAP_ID']>0){
				$where .= " and a_shop.SHOP_NO in (select SHOP_NO from pai_db_jfb.a_shopppp where HOST_MAP_ID=".$home['HOST_MAP_ID'].")";
			}
			//状态
			if($post['SHOP_STATUS'] != '') {
				$where .= " and a_shop.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
			}
			//MCC类
			if($post['MCC_TYPE'] != '') {
				$where .= " and a_shop.MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//商户ID
			if($post['SHOP_MAP_ID'] != '') {
				$where .= " and a_shop.SHOP_MAP_ID = '".$post['SHOP_MAP_ID']."'";
			}
			//商家渠道
			if($post['CHANNEL_MAP_ID'] != '') {
				$where .= " and a_shop.CHANNEL_MAP_ID = '".$post['CHANNEL_MAP_ID']."'";
			}
			//商户类型码
			if($post['MCC_CODE']) {
				$where .= " and a_shop.MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//商户号
			if($post['SHOP_NO']) {
				$where .= " and a_shop.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户手机号
			if($post['MOBILE']) {
				$where .= " and a_shop.MOBILE = '".$post['MOBILE']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and a_shop.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//商户简称
			if($post['SHOP_NAMEABCN']) {
				$where .= " and a_shop.SHOP_NAMEABCN like '%".$post['SHOP_NAMEABCN']."%'";
			}
			//门店码
			if($post['SHOP_MAP_ID_CODE']) {
				$result = $this->select_code();
				$where .= " and a_shop.SHOP_MAP_ID IN ($result)";
			}
			
			$user_id = $home["USER_ID"];
			$userModel = M('user');
			$userInfo = $userModel->where(array('USER_ID'=>$user_id))->find();
			if($userInfo['CHANNEL_MAP_ID']>0){
				$where .= " and a_shop.CHANNEL_MAP_ID=".$userInfo['CHANNEL_MAP_ID'];
			}
			
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->MShop)->countNewShop($where);
				$resdata = array(
					'count'	=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======			
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$shopModel = M('shop');
			//商户身份证号
			if($post['LP_ID']) {
				$where .= " and a_scert.LP_ID='".$post['LP_ID']."'";
				$count = $shopModel->join('a_scert on a_scert.SHOP_MAP_ID=a_shop.SHOP_MAP_ID')->where($where)->count();
				$list  = $shopModel->join('a_scert on a_scert.SHOP_MAP_ID=a_shop.SHOP_MAP_ID')->where($where)->limit($fiRow.','.$liRow)->order('a_shop.SHOP_MAP_ID DESC')->select();
			}else{
				$count = $shopModel->where($where)->count();
				$list  = D($this->MShop)->getNewShoplist($where, '*', $fiRow.','.$liRow, 'SHOP_MAP_ID DESC');
			}
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
						
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		$host_result = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID');
		$this->assign('host_result',		$host_result);
		// echo $where;
		$this->assign('home',				$home);
		$this->assign('shop_status',		C('CHECK_POINT.all'));		//所有状态
		$this->assign('shop_status_all',		C('CHECK_POINT.all2'));		//所有状态																   
		$this->assign('shop_status_check',	C('CHECK_POINT.check'));	//部分状态
		$this->assign('install_status', 	array('0' => '已装机','1' => '申请受理中','2' => '已拒绝'));	//装机状态 0:完成  1:申请受理中
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display();
	}

	//获取门店码商户
	public function select_code(){
		$shopModel = M('shop');
		$shop = $shopModel->field('SHOP_MAP_ID_CODE')->where('SHOP_MAP_ID_CODE IS NOT NULL')->select();
		foreach ($shop as $key => $value) {
			foreach ($value as $k => $v) {
				$code[] = $v;
			}
		}
		for ($i=0; $i < count($code) ; $i++) { 
			$total .= $code[$i].',';
		}
		$total = substr($total,0,-1);
		return $total;
	}
	/*
	* 商户管理 详情
	**/
	public function shop_show($tpl='shop_show') {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//获取商户基本信息
		$shop_info  = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");
		//获取商户证照信息
		$scert_info = D($this->MScert)->findScert("SHOP_MAP_ID='".$id."'");
		//获取商户权限信息
		$sauth_info = D($this->MSauth)->findSauth("SHOP_MAP_ID='".$id."'");
		//获取商户结算信息
		$smdr_info  = D($this->MSmdr)->getSmdrfind("sm.SHOP_MAP_ID='".$id."' and sm.SHOP_STATUS = 0", 'sm.SETTLE_T,sm.PAY_TYPE, sm.JFB_PER_FEE, sm.JFB_FIX_FEE, sm.PER_FEE, sm.FIX_FEE','',"locate(sm.PAY_TYPE,'5,0,4')");
		//获取商户银行帐户信息
		$sbact_info = D($this->MSbact)->findSbact("SHOP_MAP_ID='".$id."' and IS_DEFAULT=0");
		//获取商户代扣银行帐户信息
		$sdkb_info  = D($this->MSdkb)->findSdkb("SHOP_MAP_ID='".$id."'");
		//获取商户风险级别帐户信息
		//$srisk_info = D($this->MSrisk)->findSrisk("SHOP_MAP_ID='".$id."'");
		//获取商户其他配置信息
		$scfg_info = D($this->MScfg)->findScfg("SHOP_MAP_ID='".$id."'");
		if(empty($shop_info) || empty($scert_info) || empty($sauth_info) || empty($smdr_info)){
			$this->wrong("参数数据出错！");
		}
		$scert_info['OTHER'] = explode(',', $scert_info['OTHER_PHOTOS']);
		$t_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');
		/*===========查看商户下级信息  开始 ==================*/
		$shopModel = M('shop');
		$shop = $shopModel->where(array('SHOP_MAP_ID'=>$id))->field('RECOMMEND_MAP_ID,CHANNEL_MAP_ID')->find();
		$recomd = $shop['RECOMMEND_MAP_ID'];
		$channel = $shop['CHANNEL_MAP_ID'];
		//推荐人ID
		$shop_id = $shopModel->where(array('SHOP_MAP_ID'=>$recomd,'CHANNEL_MAP_ID'=>$channel))->field('SHOP_MAP_ID,SHOP_NAME,MOBILE,R_ID')->find();
		//发展人
		$shop_faz = $shopModel->where(array('RECOMMEND_MAP_ID'=>$id))->field('SHOP_MAP_ID,SHOP_NAME')->select();
		$this->assign('R_ID',           C('R_ID'));
		$this->assign('shop',		    $shop_id);//推荐人信息
		$this->assign('shop_faz',		$shop_faz);//发展人
		/*===========查看商户下级信息  结束 ==================*/
		$this->assign ('t_list', 	$t_list);							//交易开通
		$this->assign ('check_status',	 	 C('CHECK_POINT.check'));	//审核状态
		$this->assign ('bank_flag_redio',	 C('PARTNER_BANK_FLAG'));	//结算标志
		$this->assign ('settle_t_unit',		 C('SETTLE_T_UNIT'));		//结算周期
		$this->assign ('auth_trans_checked', str_split($sauth_info['AUTH_TRANS_MAP']));	//交易开通
		$this->assign ('shop_info', 	$shop_info);	//基础信息
		$this->assign ('scert_info', 	$scert_info);	//证照信息
		$this->assign ('sauth_info', 	$sauth_info);	//权限信息
		$this->assign ('smdr_info', 	$smdr_info);	//结算方式
		$this->assign ('sbact_info', 	$sbact_info);	//银行帐户
		$this->assign ('sdkb_info', 	$sdkb_info);	//代扣银行帐户
		//$this->assign ('srisk_info', 	$srisk_info);	//风险级别
		$this->assign ('scfg_info', 	$scfg_info);	//其他配置
		$home = session('HOME');
		$this->assign ('home', 	$home);	//home
		$this->display($tpl);
	}
	/*
	* 商户管理 添加
	**/
	public function shop_add() {
		$shop  = I('shop');		//商户基本数据
		$sauth = I('sauth');	//商户权限数据
		$scert = I('scert');	//商户证照数据
		$smdr  = I('smdr');		//商户扣率数据
		$sbact = I('sbact');	//商户银行帐户数据
		//$srisk = I('srisk');	//商户风险级别数据
		$sdkb  = I('sdkb');		//商户代扣帐户数据
		$scfg  = I('scfg');		//商户其他配置数据
		$plv   = get_level_val('plv',2);	//归属推广中心
		$glv   = I('glv');					//归属集团商户
		$plv_r = I('plv_r');				//推荐合作方
		$plv_b = I('plv_b');				//大商家编号
		$home = session('HOME');			//当前登录用户数据
		if($shop['submit'] == "shop_add") {
			//基本数据验证
			if(empty($shop['SHOP_NAME']) || empty($shop['SHOP_NAMEABCN']) || empty($shop['MOBILE']) || empty($shop['ZIP']) ||
				empty($shop['ADDRESS']) || empty($shop['CITY_NO']) || empty($shop['MCC_TYPE']) || empty($shop['MCC_CODE']))
			{
				$this->wrong("请填写基本信息必填项！");
			}
			//判断商户归属
			// if ($plv['bid'] =='' || $plv['pid'] == '' ) {
				// $this->wrong("请选择商户归属,且商户只能归属到区县服务中心！");
			// }
			//判断商户归属集团商户
			if ($shop['SHOP_LEVEL'] == 3) {
				if ($glv['pid'] == '' ) {
					$this->wrong("请选择归属集团商户,且集团商户只能归属到合作方！");
				}
			}
			//判断推荐合作方归属
			if ($shop['is_pr']) {
				if ($plv_r['2'] == '') {
					$this->wrong("请选择推荐个创归属,且只能归属到区县服务中心或创业合伙人！");
				}
				$plv_r_id = $plv_r['3'] ? $plv_r['3'] :$plv_r['2'];
			}
			//判断推荐大商家
			/*if ($shop['is_pb']) {
				if ($plv_b['2'] == '') {
					$this->wrong("请选择推荐大商家归属,且只能归属到区县服务中心或创业合伙人！");
				}
				$plv_b_id = $plv_b['3'] ? $plv_b['3'] :$plv_b['2'];
			}*/
			//合同编号验证
			$check_agreement = D($this->MScert)->findScert('AGREEMENT_NO = "'.$scert['AGREEMENT_NO'].'"','AGREEMENT_NO');
			if(!empty($check_agreement['AGREEMENT_NO']))
			{
				$this->wrong("当前证件中的合同编号已经存在, 请重新填写！");
			}
			//权限数据验证
			if(empty($sauth['AUTH_TRANS_MAP']) || empty($sauth['DAY_MAXAMT']))
			{
				$this->wrong("请填写权限信息必填项！");
			}
			//银行帐户验证
			switch ($sbact['SHOP_BANK_FLAG']) {
			 	case '0':
			 		$sbact['BANK_NAME1']    = $_REQUEST['org1_BANK_NAME'];
					$sbact['BANKACCT_BID1'] = $_REQUEST['org1_BANKACCT_BID'];
			 		if (empty($sbact['BANKACCT_NAME1']) || empty($sbact['BANKACCT_NO1']) || empty($sbact['BANKACCT_BID1']) || empty($sbact['BANK_NAME1']) ) {
			 			$this->wrong("请填写银行帐户必填项! ");
			 		}
			 		break;
			 	case '1':
			 		$sbact['BANK_NAME2']    = $_REQUEST['org2_BANK_NAME'];
					$sbact['BANKACCT_BID2'] = $_REQUEST['org2_BANKACCT_BID'];
			 		if (empty($sbact['BANKACCT_NAME2']) || empty($sbact['BANKACCT_NO2']) || empty($sbact['BANKACCT_BID2']) || empty($sbact['BANK_NAME2']) ) {
			 			$this->wrong("请填写银行帐户必填项! ");
			 		}
			 		break;
			}

			//代扣银行帐户验证
			switch ($sdkb['SHOP_BANK_FLAG']) {
			 	case '0':
			 		$sdkb['BANK_NAME1']    = $_REQUEST['org3_BANK_NAME'];
					$sdkb['BANKACCT_BID1'] = $_REQUEST['org3_BANKACCT_BID'];
			 		if (empty($sdkb['BANKACCT_NAME1']) || empty($sdkb['BANKACCT_NO1']) || empty($sdkb['BANKACCT_BID1']) || empty($sdkb['BANK_NAME1']) ) {
			 			// $this->wrong("请填写代扣银行帐户必填项! ");
			 		}
			 		break;
			 	case '1':
			 		$sdkb['BANK_NAME2']    = $_REQUEST['org4_BANK_NAME'];
					$sdkb['BANKACCT_BID2'] = $_REQUEST['org4_BANKACCT_BID'];
			 		if (empty($sdkb['BANKACCT_NAME2']) || empty($sdkb['BANKACCT_NO2']) || empty($sdkb['BANKACCT_BID2']) || empty($sdkb['BANK_NAME2']) ) {
			 			// $this->wrong("请填写代扣银行帐户必填项! ");
			 		}
			 		break;
			}
			//扣率数据验证
			/*if(empty($smdr['SETTLE_OFF_AMT']) || empty($smdr['SETTLE_FREE_AMT']) || empty($smdr['SETTLE_TOP_AMT']))
			{
				$this->wrong("请填写扣率信息必填项！");
			}*/
			//其他设置验证
			if ($scfg['RAKE_FLAG'] == 1) {
				if($scfg['CON_PER_RAKE']>=0 && $scfg['PLAT_PER_RAKE']>=0 && ($scfg['CON_PER_RAKE'] + $scfg['PLAT_PER_RAKE'] != 100)){
					$this->wrong("运营分润的会员和平台比例必须是大于0的数字并且它们相加必须是100！");
				}
			}
			//地区名称
			$post = I('post');
			$cityres = D($this->MCity)->findCity('CITY_S_CODE = "'.$shop['CITY_NO'].'"','CITY_S_NAME');
			//MCC
			//$mccres = M('mcc')->where('MCC_TYPE = "'.$shop['MCC_TYPE'].'"')->getField('MCC_CODE');
			
			if ($shop['SHOP_LEVEL'] == 3) {
				$gid = $glv['pid'];
			}
			$thr_name = I('thr_name');
			if($thr_name){
				$fir_name = I('fir_name');
				$sec_name = I('sec_name');
				$cateModel = M('cate_sf');
				$cateInfo = $cateModel->where(array('FIR_NAME'=>$fir_name,'SEC_NAME'=>$sec_name,'THR_NAME'=>$thr_name))->find();
				$shop_data['THR_NAME'] = $cateInfo['THR_NAME'];
				$shop_data['CATEID'] = $cateInfo['CATEID'];
			}else{
				$this->wrong('行业类目必填！');
			}
			$userModel = M('user');
			$userInfo = $userModel->where(array('USER_ID'=>$home['USER_ID']))->find();
			//组装基本数据
			$shop_data = array(
				'BRANCH_MAP_ID'		=>	$plv['bid'],
				'PARTNER_MAP_ID'	=>	$plv['pid'] ? $plv['pid'] : 0,
				'CHANNEL_MAP_ID'	=>	$userInfo['CHANNEL_MAP_ID'] ? $userInfo['CHANNEL_MAP_ID'] : 0,			//渠道编号
				'PARTNER_MAP_ID_R'	=>	$plv_r_id ? $plv_r_id : 0,			//推荐合作方
				'PARTNER_MAP_ID_B'	=>	0,									//大商家编号
				//'SHOP_NO'			=>	$shop['SHOP_NO'] ? $shop['SHOP_NO'] : '',
				'SHOP_LEVEL'		=>	$shop['SHOP_LEVEL'],
				'SHOP_MAP_ID_P'		=>	$glv['pid'] ? $glv['pid'] : 0,		//归属集团商户
				'SHOP_NAMEABCN'		=>	$shop['SHOP_NAMEABCN'],
				'SHOP_NAME'			=>	$shop['SHOP_NAME'],
				'SHOP_OPENTIME'		=>	$shop['SHOP_OPENTIME'] ? date('His',strtotime($shop['SHOP_OPENTIME'])) : '',
				'SHOP_CLOSETIME'	=>	$shop['SHOP_CLOSETIME'] ? date('His',strtotime($shop['SHOP_CLOSETIME'])) : '',
				'SHOP_STATUS'		=>	6,
				'MCC_TYPE'			=>	$shop['MCC_TYPE'],
				'MCC_CODE'			=>	$shop['MCC_CODE'],
				'SHOP_KIND'			=>	$shop['SHOP_KIND'],
				'THR_NAME'			=>	$cateInfo['THR_NAME'],
				'CATEID'			=>	$cateInfo['CATEID'],
				'CUR_TCASHAMT'		=>	0,							//当天现金累计额
				'CUR_TOTALAMT'		=>	0,							//当天累计额
				'CUR_DATE'			=>	date('Ymd'),				//当日日期
				'CITY_NO'			=>	$shop['CITY_NO'],			
				'CITY_NAME'			=>	$cityres['CITY_S_NAME'],
				'ADDRESS'			=>	$shop['ADDRESS'],
				'ZIP'				=>	$shop['ZIP'],
				'MOBILE'			=>	$shop['MOBILE'],
				'TEL'				=>	$shop['TEL'],
				'EMAIL'				=>	$shop['EMAIL'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'CREATE_TIME'		=>	date('YmdHis')
			);
			$m = M();
			$m->startTrans();	//启用事务
			//如果有商户号，判断商户是否重复
			/*if (!empty($shop['SHOP_NO'])) {
				$sdata = D($this->MShop)->findmoreNewShop('SHOP_NO = "'.$shop['SHOP_NO'].'"');
				if (!empty($sdata['SHOP_NO'])) {
					$this->wrong('商户号与系统其他商户重复，请重新填写！');
				}
			}else{
				unset($shop['SHOP_NO']);
			}*/
			
			//基础数据入库
			$shop_res = D($this->MShop)->addShop($shop_data);
			if($shop_res['state']!=0){
				$this->wrong('商户基础信息添加失败！');
			}
			//更新商户的SHOP_NO
			D($this->MShop)->updateShop('SHOP_MAP_ID = '.$shop_res['SHOP_MAP_ID'],array('SHOP_NO'=>setStrzero($shop_res['SHOP_MAP_ID'],15)));
			
			
			//$res = D($this->MShop)->updateShop('SHOP_MAP_ID = '.$shop_res['SHOP_MAP_ID'],array('SHOP_NO'=>setStrzero($shop_res['SHOP_MAP_ID'],15)));
			//组装证照数据
			$scert_data = array(
				'SHOP_MAP_ID'	=>	$shop_res['SHOP_MAP_ID'],
				'SHOP_STATUS'	=>	0,
				'AGREEMENT_NO'	=>	$scert['AGREEMENT_NO'],
				'REG_EXP'		=>	$scert['REG_EXP_FLAG']==1 ? '99999999' : date('Ymd',strtotime($scert['REG_EXP'])),	//REG_EXP_FLAG临时增加
				'LP_EXP'		=>	$scert['LP_EXP_FLAG']==1 ? '99999999' : date('Ymd',strtotime($scert['LP_EXP'])),	//LP_EXP_FLAG临时增加
				'REG_ADDR'		=>	getcity_name($scert['CITY_NO']).$scert['REG_ADDR'],
				'REG_ID'		=>	$scert['REG_ID'],
				'TAX_ID'		=>	$scert['TAX_ID'],
				'ORG_ID'		=>	$scert['ORG_ID'],
				'LP_NAME'		=>	$scert['LP_NAME'],
				'LP_ID'			=>	$scert['LP_ID'],
				'REGID_PHOTO'	=>	$scert['REGID_PHOTO'],
				'TAXID_PHOTO'	=>	$scert['TAXID_PHOTO'],
				'ORGID_PHOTO'	=>	$scert['ORGID_PHOTO'],
				'LP_D_PHOTO'	=>	$scert['LP_D_PHOTO'],
				'LP_R_PHOTO'	=>	$scert['LP_R_PHOTO'],
				'BANK_PHOTO'	=>	$scert['BANK_PHOTO'],
				'REGADDR_PHOTO1'=>	$scert['REGADDR_PHOTO1'],
				'REGADDR_PHOTO2'=>	$scert['REGADDR_PHOTO2'],
				'OFFICE_PHOTO1'	=>	$scert['OFFICE_PHOTO1'],
				'OFFICE_PHOTO2'	=>	$scert['OFFICE_PHOTO2'],
				'OFFICE_PHOTO3'	=>	$scert['OFFICE_PHOTO3'],
				'OFFICE_PHOTO4'	=>	$scert['OFFICE_PHOTO4'],
				'OTHER_PHOTOS'	=>	$scert['OTHER_PHOTOS'],
				'AGREEMENTID_PHOTO1'	=>	$scert['AGREEMENTID_PHOTO1'],
				'AGREEMENTID_PHOTO2'	=>	$scert['AGREEMENTID_PHOTO2'],
				'LICENSEID_PHOTO'	=>	$scert['LICENSEID_PHOTO'],
				'GRANTID_PHOTO1'	=>	$scert['GRANTID_PHOTO1'],
				'GRANTID_PHOTO2'	=>	$scert['GRANTID_PHOTO2'],
				'RES'			=>	$scert['RES']
			);

			//证照数据入库
			$scert_res = D($this->MScert)->addScert($scert_data);
			if($scert_res['state']!=0){
				$m->rollback();//不成功，则回滚
				$this->wrong('商户证照信息添加失败！');
			}

			//组装权限数据
			$sauth_data = array(
				'SHOP_MAP_ID'	 =>	$shop_res['SHOP_MAP_ID'],
				'SHOP_STATUS'	 =>	0,
				//开通交易位图
				'AUTH_TRANS_MAP' =>	get_authstr($sauth['AUTH_TRANS_MAP']),	
				//日均限额
				'DAY_MAXAMT'	 =>	$sauth['DAY_MAXAMT'] ? setMoney($sauth['DAY_MAXAMT'], '6') : 10000000,	
				//日均现金限额
				'DAY_CASH_MAXAMT'=>	$sauth['DAY_CASH_MAXAMT'] ? setMoney($sauth['DAY_CASH_MAXAMT'], '6') : 100000000,
				//单笔现金限额
				'CASH_MAXAMT'	 =>	$sauth['CASH_MAXAMT'] ? setMoney($sauth['CASH_MAXAMT'], '6') : 1000000
			);
			//权限数据入库
			$sauth_res = D($this->MSauth)->addSauth($sauth_data);
			if($sauth_res['state']!=0){
				$m->rollback();	//不成功，则回滚
				$this->wrong('商户权限数据添加失败！');
			}
			//现金的积分宝扣率, 等于银行卡商户扣率+积分宝扣率
			//$smdr['mdr'][0]['JFB_PER_FEE'] = $smdr['mdr'][1]['JFB_PER_FEE'] + $smdr['mdr'][1]['PER_FEE'];
			//积分宝的积分宝扣率, 等于银行卡商户扣率+积分宝扣率
			//$smdr['mdr'][2]['JFB_PER_FEE'] = $smdr['mdr'][1]['JFB_PER_FEE'] + $smdr['mdr'][1]['PER_FEE'];
			//扣率数据
			foreach ($smdr['mdr'] as $value) {
				//组装扣率数据
				$smdr_data = array(
					'SHOP_MAP_ID'		=>	$shop_res['SHOP_MAP_ID'],
					'PAY_TYPE'			=>	$value['PAY_TYPE'] ? $value['PAY_TYPE'] : 0,
					'SHOP_STATUS'		=>	0,
					'SETTLE_T'			=>	$smdr['SETTLE_T'],
					'SETTLE_T_UNIT'		=>	1,
					/*'SETTLE_FLAG'		=>	$smdr['SETTLE_FLAG'] ? $smdr['SETTLE_FLAG'] : 0,
					'SETTLE_TOP_AMT'	=>	setMoney($value['SETTLE_TOP_AMT'], '2'),
					'SETTLE_FREE_AMT'	=>	setMoney($value['SETTLE_FREE_AMT'], '2'),
					'SETTLE_OFF_AMT'	=>	setMoney($value['SETTLE_OFF_AMT'], '2'),
					'SETTLE_OFF_FEE'	=>	$value['SETTLE_OFF_FEE'] ? setMoney($value['SETTLE_OFF_FEE'], '2') : '0',
					'SETTLE_FEE'		=>	$value['SETTLE_FEE'] ? setMoney($value['SETTLE_FEE'],'2') : '0',*/
					'JFB_PER_FEE'		=>	$value['JFB_PER_FEE'] ? setMoney($value['JFB_PER_FEE'],'2') : '0',	//积分宝比例扣率线(万分比)
					'JFB_FIX_FEE'		=>	$value['JFB_FIX_FEE'] ? setMoney($value['JFB_FIX_FEE'], '2') : '0',	//积分宝封顶扣率线(单位分)
					'PER_FEE'			=>	$value['PER_FEE'] ? setMoney($value['PER_FEE'],'2') : '0',			//商户比例扣线(万分比)
					'FIX_FEE'			=>	$value['FIX_FEE'] ? setMoney($value['FIX_FEE'],'2') : '0',			//商户封顶扣率线(单位分)
					'DYN_PER_FEE'		=>	'0'
				);
				//扣率数据入库
				$smdr_res = D($this->MSmdr)->addSmdr($smdr_data);
				if($smdr_res['state']!=0){
					$m->rollback();//不成功，则回滚
					$this->wrong('商户扣率数据添加失败！');
				}
			}
			
			//组装银行账户数据
			switch ($sbact['SHOP_BANK_FLAG']) {
				case '0':			//对公账户变更
					$sbact_data = array(
						'SHOP_MAP_ID'		=>	$shop_res['SHOP_MAP_ID'],
						'SHOP_STATUS'		=>	0,
						'SHOP_BANK_FLAG'	=>	$sbact['SHOP_BANK_FLAG'],
						'BANKACCT_NAME1'	=>	$sbact['BANKACCT_NAME1'] ? $sbact['BANKACCT_NAME1'] : '',
						'BANKACCT_NO1'		=>	$sbact['BANKACCT_NO1'] ? $sbact['BANKACCT_NO1'] : '',
						'BANKACCT_BID1'		=>	$sbact['BANKACCT_BID1'] ? $sbact['BANKACCT_BID1'] : '',
						'BANK_NAME1'		=>	$sbact['BANK_NAME1'] ? $sbact['BANK_NAME1'] : '',
						'BANK_MOBILE1'		=>	$sbact['BANK_MOBILE1'] ? $sbact['BANK_MOBILE1'] : ''
					);
					break;
				case '1':			//对私账户变更
					$sbact_data = array(
						'SHOP_MAP_ID'		=>	$shop_res['SHOP_MAP_ID'],
						'SHOP_STATUS'		=>	0,
						'SHOP_BANK_FLAG'	=>	$sbact['SHOP_BANK_FLAG'],
						'BANKACCT_NAME2'	=>	$sbact['BANKACCT_NAME2'] ? $sbact['BANKACCT_NAME2'] : '',
						'BANKACCT_NO2'		=>	$sbact['BANKACCT_NO2'] ? $sbact['BANKACCT_NO2'] : '',
						'BANKACCT_BID2'		=>	$sbact['BANKACCT_BID2'] ? $sbact['BANKACCT_BID2'] : '',
						'BANK_NAME2'		=>	$sbact['BANK_NAME2'] ? $sbact['BANK_NAME2'] : '',
						'BANK_MOBILE2'		=>	$sbact['BANK_MOBILE2'] ? $sbact['BANK_MOBILE2'] : ''
					);
					break;
				default:
					$m->rollback();//不成功，则回滚
					$this->wrong('商户银行账户信息不正确,变更失败！');
					break;
			}
			//银行帐户入库
			$sbact_res = D($this->MSbact)->addSbact($sbact_data);
			if($sbact_res['state']!=0){
				$m->rollback();//不成功，则回滚
				$this->wrong('商户银行信息添加失败！');
			}

			//组装风险级别数据
			$srisk_data = array(
				'SHOP_MAP_ID'	=>	$shop_res['SHOP_MAP_ID'],
				'SHOP_GRADE'	=>	$srisk['SHOP_GRADE'] ? $srisk['SHOP_GRADE'] : 3,
				'SHOP_RISKBOUND'=>	$srisk['SHOP_RISKBOUND'] ? $srisk['SHOP_RISKBOUND'] : 100,
				'POSMODE_PER'	=>	$srisk['POSMODE_PER'] ? $srisk['POSMODE_PER'] : '',
				'REFUND_PER'	=>	$srisk['REFUND_PER'] ? $srisk['REFUND_PER'] : '',
				'CBREQ_PER'		=>	$srisk['CBREQ_PER'] ? $srisk['CBREQ_PER'] : '',
				'CBBACK_PER'	=>	$srisk['CBBACK_PER'] ? $srisk['CBBACK_PER'] : '',
				'BUSITIME_ABS'	=>	$srisk['BUSITIME_ABS'] ? $srisk['BUSITIME_ABS'] : '',
				'BALANCE_PER'	=>	$srisk['BALANCE_PER'] ? $srisk['BALANCE_PER'] : '',
				'RES'			=>	$srisk['RES'] ? $srisk['RES'] : '',
			);
			//风险级别入库
			$srisk_res = D($this->MSrisk)->addSrisk($srisk_data);
			if($srisk_res['state']!=0){
				$m->rollback();//不成功，则回滚
				$this->wrong('商户风险评级添加失败！');
			}

			//组装代扣帐户数据
			switch ($sdkb['SHOP_BANK_FLAG']) {
				case '0':			//对公账户变更
					$sdkb_data = array(
						'SHOP_MAP_ID'		=>	$shop_res['SHOP_MAP_ID'],
						'SHOP_BANK_FLAG'	=>	$sdkb['SHOP_BANK_FLAG'],
						'DKCO_MAP_ID'		=>	$sdkb['DKCO_MAP_ID'],
						'SHOP_STATUS'		=>	0,
						'BANKACCT_NAME1'	=>	$sdkb['BANKACCT_NAME1'] ? $sdkb['BANKACCT_NAME1'] : '',
						'BANKACCT_NO1'		=>	$sdkb['BANKACCT_NO1'] ? $sdkb['BANKACCT_NO1'] : '',
						'BANKACCT_BID1'		=>	$sdkb['BANKACCT_BID1'] ? $sdkb['BANKACCT_BID1'] : '',
						'BANK_NAME1'		=>	$sdkb['BANK_NAME1'] ? $sdkb['BANK_NAME1'] : '',
						'SHOP_ACCT_FLAG'	=>	$sdkb['SHOP_ACCT_FLAG'] ? $sdkb['SHOP_ACCT_FLAG'] :'0',
						'DK_IDNO_TYPE'		=>	$sdkb['DK_IDNO_TYPE'] ? $sdkb['DK_IDNO_TYPE'] :'0',
						'DK_IDNO'			=>	$sdkb['DK_IDNO'] ? $sdkb['DK_IDNO'] :''
					);
					break;
				case '1':			//对私账户变更
					$sdkb_data = array(
						'SHOP_MAP_ID'		=>	$shop_res['SHOP_MAP_ID'],
						'SHOP_BANK_FLAG'	=>	$sdkb['SHOP_BANK_FLAG'],
						'DKCO_MAP_ID'		=>	$sdkb['DKCO_MAP_ID'],
						'SHOP_STATUS'		=>	0,
						'BANKACCT_NAME2'	=>	$sdkb['BANKACCT_NAME2'] ? $sdkb['BANKACCT_NAME2'] : '',
						'BANKACCT_NO2'		=>	$sdkb['BANKACCT_NO2'] ? $sdkb['BANKACCT_NO2'] : '',
						'BANKACCT_BID2'		=>	$sdkb['BANKACCT_BID2'] ? $sdkb['BANKACCT_BID2'] : '',
						'BANK_NAME2'		=>	$sdkb['BANK_NAME2'] ? $sdkb['BANK_NAME2'] : '',
						'SHOP_ACCT_FLAG'	=>	$sdkb['SHOP_ACCT_FLAG'] ? $sdkb['SHOP_ACCT_FLAG'] :'0',
						'DK_IDNO_TYPE'		=>	$sdkb['DK_IDNO_TYPE'] ? $sdkb['DK_IDNO_TYPE'] :'0',
						'DK_IDNO'			=>	$sdkb['DK_IDNO'] ? $sdkb['DK_IDNO'] :''
					);
					break;
				default:
					// $m->rollback();		//不成功，则回滚
					// $this->wrong('代扣银行账户信息不正确,添加失败！');
					break;
			}
			//代扣银行帐户入库
			/*$sdkb_res = D($this->MSdkb)->addSdkb($sdkb_data);
			if($sdkb_res['state']!=0){
				$m->rollback();		//不成功，则回滚
				$this->wrong('商户代扣银行信息添加失败！');
			}*/

			//组装其他配置数据
			$scfg_plv = get_level_val('scfg_plv');
			$scfg_data = array(
				'SHOP_MAP_ID'	=>	$shop_res['SHOP_MAP_ID'],
				'SHOP_STATUS'	=>	0,
				'DIV_FLAG'		=>	$scfg['DIV_FLAG'] ? $scfg['DIV_FLAG'] : 0,				//分期特殊分配比例
				'CARD_OPENFEE'	=>	$scfg['CARD_OPENFEE'] ? $scfg['CARD_OPENFEE'] : 3000,	//卡收费总额
				'DIV_PER'		=>	$scfg['DIV_PER'] ? setMoney($scfg['DIV_PER']) : 0,		//预免卡分期比例
				'BOUND_RATE'	=>	$scfg['BOUND_RATE'] ? $scfg['BOUND_RATE'] : 50,			//积分兑换比例
				'RAKE_FLAG'		=>	$scfg['RAKE_FLAG'] ? $scfg['RAKE_FLAG'] : 0,			//特殊分配比例
				'CON_PER_RAKE'	=>	$scfg['CON_PER_RAKE'] ? setMoney($scfg['CON_PER_RAKE']) : 0,	//消费者比例
				'PLAT_PER_RAKE'	=>	$scfg['PLAT_PER_RAKE'] ? setMoney($scfg['PLAT_PER_RAKE']) : (10000-setMoney($scfg['CON_PER_RAKE'])),	//平台比例
				'DONATE_FLAG'	=>	$scfg['DONATE_FLAG'] ? $scfg['DONATE_FLAG'] : 0,		//转赠标志
				'DONATE_TYPE'	=>	$scfg['DONATE_TYPE'],							 		//转赠产品
				'DONATE_RATE'	=>	$scfg['DONATE_RATE'] ? $scfg['DONATE_RATE'] : 0,		//转赠率
				'PARTNER_MAP_ID'=>	$scfg_plv['pid'] ? $scfg_plv['pid'] : $home['PARTNER_MAP_ID'],	//转赠对象
				'DONATE_RES'	=>	$scfg['DONATE_RES']										//备注
			);
		
			//其他配置数据入库
			/*$scfg_res = D($this->MScfg)->addScfg($scfg_data);
			if($scfg_res['state']!=0){
				$m->rollback();		//不成功，则回滚
				$this->wrong('商户其他配置添加失败！');
			}*/
			$m->commit();	//全部成功则提交
			$this->right($shop_res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//渠道列表
		$channel_list = D($this->MChannel)->getChannellist("CHANNEL_STATUS = 0", 'CHANNEL_MAP_ID,CHANNEL_NAME');
		$t_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');
		$this->assign ('t_list', 	$t_list);							//交易开通
		$this->assign ('channel_list', 		$channel_list );			//渠道列表
		$this->assign ('bank_flag_redio', 	C('PARTNER_BANK_FLAG'));	//结算标志
		$this->assign ('home', 				$home);						//当前登录用户数据
		$this->display();
	}
	/*
	* 商户管理 修改
	**/
	public function shop_edit() {
		$shop  = I('shop');
		$sauth = I('sauth');
		$scert = I('scert');
		$smdr  = I('smdr');
		$sbact = I('sbact');
		$srisk = I('srisk');
		$sdkb  = I('sdkb');
		$scfg  = I('scfg');					//商户其他配置数据
		$home = session('HOME');			//当前登录用户数据
		if($shop['submit'] == "shop_edit") {
			//基本数据验证
			if ($home['BRANCH_MAP_ID'] == C('SPECIAL_USER') || $shop['SHOP_STATUS'] != 0) {
				$plv   = get_level_val('plv',2);	//归属推广中心
				//$glv   = get_level_val('glv');	//归属集团商户
				$plv_r = I('plv_r');				//推荐合作方
				if(empty($shop['SHOP_MAP_ID']) || empty($shop['SHOP_NAME']) || empty($shop['SHOP_NAMEABCN']) || empty($shop['MOBILE']) || empty($shop['ZIP']) ||
					empty($shop['ADDRESS']) || empty($shop['CITY_NO'])){
					$this->wrong("请填写基本信息必填项！");
				}
			
				//判断商户归属
				// if ($plv['bid'] =='' || $plv['pid'] == '' ) {
					// $this->wrong("请选择商户归属,且商户只能归属到区县服务中心！");
				// }
				//判断商户归属集团商户
				if ($shop['SHOP_LEVEL'] == 3) {
					if ($shop['SHOP_MAP_ID_P'] == '' ) {
						$this->wrong("请选择归属集团主商户！");
					}
				}
				//判断推荐合作方归属
				if ($shop['is_pr']) {
					if ($plv_r['2'] == '') {
						$this->wrong("请选择推荐个创归属,且只能归属到区县服务中心或创业合伙人！");
					}
					$plv_r_id = $plv_r['3'] ? $plv_r['3'] :$plv_r['2'];
				}
				//判断推荐大商家
				/*if ($shop['is_pb']) {
					if ($plv_b['2'] == '') {
						$this->wrong("请选择推荐大商家归属,且只能归属到区县服务中心或创业合伙人！");
					}
					$plv_b_id = $plv_b['3'] ? $plv_b['3'] :$plv_b['2'];
				}*/
			}else{
				if(empty($shop['SHOP_MAP_ID']) || empty($shop['MOBILE']) || empty($shop['ZIP']) || empty($shop['ADDRESS']) || empty($shop['CITY_NO'])){
					$this->wrong("请填写基本信息必填项！");
				}
			}
			if ($shop['SHOP_STATUS'] != 0 && $shop['SHOP_STATUS'] != 4) {
				//合同编号验证
				$check_agreement = D($this->MScert)->findScert('AGREEMENT_NO = "'.$scert['AGREEMENT_NO'].'" and SHOP_MAP_ID != "'.$shop['SHOP_MAP_ID'].'"','AGREEMENT_NO');
				if(!empty($check_agreement['AGREEMENT_NO']))
				{
					$this->wrong("当前证件中的合同编号已经存在, 请重新填写！");
				}
				//权限数据验证
				if(empty($sauth['AUTH_TRANS_MAP']) || empty($sauth['DAY_MAXAMT']))
				{
					$this->wrong("请填写权限信息必填项！");
				}
				//银行帐户验证
				switch ($sbact['SHOP_BANK_FLAG']) {
				 	case '0':
				 		$sbact['BANK_NAME1']    = $_REQUEST['org1_BANK_NAME'];
						$sbact['BANKACCT_BID1'] = $_REQUEST['org1_BANKACCT_BID'];
				 		if (empty($sbact['BANKACCT_NAME1']) || empty($sbact['BANKACCT_NO1']) || empty($sbact['BANKACCT_BID1']) || empty($sbact['BANK_NAME1']) ) {
				 			$this->wrong("请填写银行帐户必填项! ");
				 		}
				 		break;
				 	case '1':
				 		$sbact['BANK_NAME2']    = $_REQUEST['org2_BANK_NAME'];
						$sbact['BANKACCT_BID2'] = $_REQUEST['org2_BANKACCT_BID'];
				 		if (empty($sbact['BANKACCT_NAME2']) || empty($sbact['BANKACCT_NO2']) || empty($sbact['BANKACCT_BID2']) || empty($sbact['BANK_NAME2']) ) {
				 			$this->wrong("请填写银行帐户必填项! ");
				 		}
				 		break;
				}

				//代扣银行帐户验证
				switch ($sdkb['SHOP_BANK_FLAG']) {
				 	case '0':
				 		$sdkb['BANK_NAME1']    = $_REQUEST['org3_BANK_NAME'];
						$sdkb['BANKACCT_BID1'] = $_REQUEST['org3_BANKACCT_BID'];
				 		// if (empty($sdkb['BANKACCT_NAME1']) || empty($sdkb['BANKACCT_NO1']) || empty($sdkb['BANKACCT_BID1']) || empty($sdkb['BANK_NAME1']) ) {
				 			// $this->wrong("请填写代扣银行帐户必填项! ");
				 		// }
				 		break;
				 	case '1':
				 		$sdkb['BANK_NAME2']    = $_REQUEST['org4_BANK_NAME'];
						$sdkb['BANKACCT_BID2'] = $_REQUEST['org4_BANKACCT_BID'];
				 		// if (empty($sdkb['BANKACCT_NAME2']) || empty($sdkb['BANKACCT_NO2']) || empty($sdkb['BANKACCT_BID2']) || empty($sdkb['BANK_NAME2']) ) {
				 			// $this->wrong("请填写代扣银行帐户必填项! ");
				 		// }
				 		break;
				}
				/*//扣率数据验证
				if(empty($smdr['SETTLE_OFF_AMT']) || empty($smdr['SETTLE_FREE_AMT']) || empty($smdr['SETTLE_TOP_AMT']))
				{
					$this->wrong("请填写扣率信息必填项！");
				}*/
				$shop['SHOP_STATUS'] = 6;
			}
			//其他设置验证
			if ($scfg['RAKE_FLAG'] == 1) {
				if($scfg['CON_PER_RAKE']>=0 && $scfg['PLAT_PER_RAKE']>=0 && ($scfg['CON_PER_RAKE'] + $scfg['PLAT_PER_RAKE'] != 100)){
					$this->wrong("运营分润的会员和平台比例必须是大于0的数字并且它们相加必须是100！");
				}
			}
				//地区名称
				$post = I('post');
				$cityres = D($this->MCity)->findCity('CITY_S_CODE = "'.$shop['CITY_NO'].'"','CITY_S_NAME');
				//组装基本数据
				if ($home['BRANCH_MAP_ID'] == C('SPECIAL_USER') || $shop['SHOP_STATUS'] != 0) {
					$shop_data = array(
						'BRANCH_MAP_ID'		=>	$plv['bid'],
						'PARTNER_MAP_ID'	=>	$plv['pid'] ? $plv['pid'] : 0,
						'CHANNEL_MAP_ID'	=>	$shop['CHANNEL_MAP_ID'] ? $shop['CHANNEL_MAP_ID'] : 0,
						'PARTNER_MAP_ID_R'	=>	$plv_r_id ? $plv_r_id : 0,	//推荐合作方
						'PARTNER_MAP_ID_B'	=>	0,							//大商家编号
						'SHOP_LEVEL'		=>	$shop['SHOP_LEVEL'],
						'SHOP_STATUS'		=>	$shop['SHOP_STATUS'],
						'SHOP_MAP_ID_P'		=>	$shop['SHOP_MAP_ID_P'] ? $shop['SHOP_MAP_ID_P'] : 0,
						'SHOP_NAMEABCN'		=>	$shop['SHOP_NAMEABCN'],
						'SHOP_NAME'			=>	$shop['SHOP_NAME'],
						'SHOP_OPENTIME'		=>	$shop['SHOP_OPENTIME'] ? date('His',strtotime($shop['SHOP_OPENTIME'])) : '',
						'SHOP_CLOSETIME'	=>	$shop['SHOP_CLOSETIME'] ? date('His',strtotime($shop['SHOP_CLOSETIME'])) : '',
						'ADDRESS'			=>	$shop['ADDRESS'],
						'ZIP'				=>	$shop['ZIP'],
						'MOBILE'			=>	$shop['MOBILE'],
						'TEL'				=>	$shop['TEL'],
						'EMAIL'				=>	$shop['EMAIL'],
						'CITY_NO'			=>	$shop['CITY_NO'],
						'CITY_NAME'			=>	$cityres['CITY_S_NAME'],
						'CREATE_USERID'		=>	$home['USER_ID'],
						'CREATE_USERNAME'	=>	$home['USER_NAME']
					);
				}else{
					$shop_data = array(
						'SHOP_OPENTIME'		=>	$shop['SHOP_OPENTIME'] ? date('His',strtotime($shop['SHOP_OPENTIME'])) : '',
						'SHOP_CLOSETIME'	=>	$shop['SHOP_CLOSETIME'] ? date('His',strtotime($shop['SHOP_CLOSETIME'])) : '',
						'ADDRESS'			=>	$shop['ADDRESS'],
						'ZIP'				=>	$shop['ZIP'],
						'MOBILE'			=>	$shop['MOBILE'],
						'TEL'				=>	$shop['TEL'],
						'EMAIL'				=>	$shop['EMAIL'],
						'CITY_NO'			=>	$shop['CITY_NO'],
						'CITY_NAME'			=>	$cityres['CITY_S_NAME'],
						'CREATE_USERID'		=>	$home['USER_ID'],
						'CREATE_USERNAME'	=>	$home['USER_NAME']
					);
				}
				
				if ($shop['SHOP_STATUS'] != '0') {
					$shop_data['MCC_TYPE'] = $shop['MCC_TYPE'];
					$shop_data['MCC_CODE'] = $shop['MCC_CODE'];
					/*if ($shop['CHANNEL_MAP_ID'] != '100002' and $shop['CHANNEL_MAP_ID'] != '0') {
						//如果有商户号，判断商户是否重复
						if ($shop['SHOP_NO']) {
							$sdata = D($this->MShop)->findmoreNewShop('SHOP_NO = "'.$shop['SHOP_NO'].'" and SHOP_MAP_ID !="'.$shop['SHOP_MAP_ID'].'"');
							if (!empty($sdata['SHOP_NO'])) {
								$this->wrong('商户号与系统其他商户重复，请重新填写！');
							}
						}
						$shop_data['SHOP_NO'] = $shop['SHOP_NO'];
					}*/
				}
				$thr_name = I('thr_name');
				if($thr_name){
					$fir_name = I('fir_name');
					$sec_name = I('sec_name');
					$cateModel = M('cate_sf');
					$cateInfo = $cateModel->where(array('FIR_NAME'=>$fir_name,'SEC_NAME'=>$sec_name,'THR_NAME'=>$thr_name))->find();
					$shop_data['THR_NAME'] = $cateInfo['THR_NAME'];
					$shop_data['CATEID'] = $cateInfo['CATEID'];
				}else{
					$this->wrong('行业类目必填！');
				}
				$shop_data['SHOP_KIND'] = $shop['SHOP_KIND'];
				$where = "SHOP_MAP_ID = ".$shop['SHOP_MAP_ID'];
				$m = M();
				$m->startTrans();	//启用事务
				//基础数据变更
				$shop_res = D($this->MShop)->updateShop($where,$shop_data);
				if($shop_res['state']!=0){
					$this->wrong('商户基础信息修改失败！');
				}
				if ($shop['SHOP_STATUS'] != 0 && $shop['SHOP_STATUS'] != 4) {	
					//组装证照数据
					$scert_data = array(
						'AGREEMENT_NO'	=>	$scert['AGREEMENT_NO'],
						'REG_EXP'		=>	$scert['REG_EXP_FLAG']==1 ? '99999999' : date('Ymd',strtotime($scert['REG_EXP'])),	//REG_EXP_FLAG临时增加
						'LP_EXP'		=>	$scert['LP_EXP_FLAG']==1 ? '99999999' : date('Ymd',strtotime($scert['LP_EXP'])),	//LP_EXP_FLAG临时增加
						'REG_ADDR'		=>	$scert['REG_ADDR'],
						'REG_ID'		=>	$scert['REG_ID'],
						'TAX_ID'		=>	$scert['TAX_ID'],
						'ORG_ID'		=>	$scert['ORG_ID'],
						'LP_NAME'		=>	$scert['LP_NAME'],
						'LP_ID'			=>	$scert['LP_ID'],
						'REGID_PHOTO'	=>	$scert['REGID_PHOTO'],
						'TAXID_PHOTO'	=>	$scert['TAXID_PHOTO'],
						'ORGID_PHOTO'	=>	$scert['ORGID_PHOTO'],
						'LP_D_PHOTO'	=>	$scert['LP_D_PHOTO'],
						'LP_R_PHOTO'	=>	$scert['LP_R_PHOTO'],
						'BANK_PHOTO'	=>	$scert['BANK_PHOTO'],
						'REGADDR_PHOTO1'=>	$scert['REGADDR_PHOTO1'],
						'REGADDR_PHOTO2'=>	$scert['REGADDR_PHOTO2'],
						'OFFICE_PHOTO1'	=>	$scert['OFFICE_PHOTO1'],
						'OFFICE_PHOTO2'	=>	$scert['OFFICE_PHOTO2'],
						'OFFICE_PHOTO3'	=>	$scert['OFFICE_PHOTO3'],
						'OFFICE_PHOTO4'	=>	$scert['OFFICE_PHOTO4'],
						'OFFICE_PHOTO5'	=>	$scert['OFFICE_PHOTO5'],
						'AGREEMENTID_PHOTO1'	=>	$scert['AGREEMENTID_PHOTO1'],
						'AGREEMENTID_PHOTO2'	=>	$scert['AGREEMENTID_PHOTO2'],
						'LICENSEID_PHOTO'	=>	$scert['LICENSEID_PHOTO'],
						'GRANTID_PHOTO1'	=>	$scert['GRANTID_PHOTO1'],
						'GRANTID_PHOTO2'	=>	$scert['GRANTID_PHOTO2'],
						'BANK_D_PHOTO'	=>	$scert['BANK_D_PHOTO'],
						'BANK_R_PHOTO'	=>	$scert['BANK_R_PHOTO'],
						'LP_GRANTID_PHOTO'	=>	$scert['LP_GRANTID_PHOTO'],
						'BANK_LP_D_PHOTO'	=>	$scert['BANK_LP_D_PHOTO'],
						'BANK_LP_R_PHOTO'	=>	$scert['BANK_LP_R_PHOTO'],
						'OTHER_PHOTOS'	=>	$scert['OTHER_PHOTOS'],
						'RES'			=>	$scert['RES']
					);
					//证照数据变更
					$scert_res = D($this->MScert)->updateScert($where,$scert_data);
					if($scert_res['state']!=0){
						$m->rollback();		//不成功，则回滚
						$this->wrong('商户证照信息修改失败！');
					}
					//组装权限数据
					$sauth_data = array(
						'AUTH_TRANS_MAP' =>	get_authstr($sauth['AUTH_TRANS_MAP']),
						'DAY_MAXAMT'	 =>	$sauth['DAY_MAXAMT'] ? setMoney($sauth['DAY_MAXAMT'], '6') : 10000000,
						'DAY_CASH_MAXAMT'=>	$sauth['DAY_CASH_MAXAMT'] ? setMoney($sauth['DAY_CASH_MAXAMT'], '6') : 100000000,
						'CASH_MAXAMT'	 =>	$sauth['CASH_MAXAMT'] ? setMoney($sauth['CASH_MAXAMT'], '6') : 1000000
					);
					//权限数据变更
					$sauth_res = D($this->MSauth)->updateSauth($where,$sauth_data);
					if($sauth_res['state']!=0){
						$this->wrong('商户权限数据修改失败！');
					}

					//现金的积分宝扣率, 等于银行卡商户扣率+积分宝扣率
					//$smdr['mdr'][0]['JFB_PER_FEE'] = $smdr['mdr'][1]['JFB_PER_FEE'] + $smdr['mdr'][1]['PER_FEE'];
					//积分宝的积分宝扣率, 等于银行卡商户扣率+积分宝扣率
					//$smdr['mdr'][2]['JFB_PER_FEE'] = $smdr['mdr'][1]['JFB_PER_FEE'] + $smdr['mdr'][1]['PER_FEE'];
					//扣率数据
					foreach ($smdr['mdr'] as $value) {
						//组装扣率数据
						$smdr_data = array(
							'PAY_TYPE'			=>	$value['PAY_TYPE'] ? $value['PAY_TYPE'] : 0,
							'SETTLE_T'			=>	$smdr['SETTLE_T'],
							'SETTLE_T_UNIT'		=>	1,
							/*'SETTLE_FLAG'		=>	$smdr['SETTLE_FLAG'] ? $smdr['SETTLE_FLAG'] : 0,
							'SETTLE_TOP_AMT'	=>	setMoney($value['SETTLE_TOP_AMT'], '2'),
							'SETTLE_FREE_AMT'	=>	setMoney($value['SETTLE_FREE_AMT'], '2'),
							'SETTLE_OFF_AMT'	=>	setMoney($value['SETTLE_OFF_AMT'], '2'),
							'SETTLE_OFF_FEE'	=>	$value['SETTLE_OFF_FEE'] ? setMoney($value['SETTLE_OFF_FEE'], '2') : '0',
							'SETTLE_FEE'		=>	$value['SETTLE_FEE'] ? setMoney($value['SETTLE_FEE'],'2') : '0',*/
							'JFB_PER_FEE'		=>	$value['JFB_PER_FEE'] ? setMoney($value['JFB_PER_FEE'],'2') : '0',	//积分宝比例扣率线(万分比)
							'JFB_FIX_FEE'		=>	$value['JFB_FIX_FEE'] ? setMoney($value['JFB_FIX_FEE'], '2') : '0',	//积分宝封顶扣率线(单位分)
							'PER_FEE'			=>	$value['PER_FEE'] ? setMoney($value['PER_FEE'],'2') : '0',			//商户比例扣线(万分比)
							'FIX_FEE'			=>	$value['FIX_FEE'] ? setMoney($value['FIX_FEE'],'2') : '0',			//商户封顶扣率线(单位分)
							'DYN_PER_FEE'		=>	'0'
						);
						//扣率数据入库
						$smdr_res = D($this->MSmdr)->updateSmdr($where." and PAY_TYPE = ".$value['PAY_TYPE'], $smdr_data);
						if($smdr_res['state']!=0){
							$m->rollback();		//不成功，则回滚
							$this->wrong('商户扣率数据修改失败！');
						}
					}

					//组装银行账户数据
					switch ($sbact['SHOP_BANK_FLAG']) {
						case '0':			//对公账户变更
							$sbact_data = array(
								'SHOP_BANK_FLAG'	=>	$sbact['SHOP_BANK_FLAG'],
								'BANKACCT_NAME1'	=>	$sbact['BANKACCT_NAME1'] ? $sbact['BANKACCT_NAME1'] : '',
								'BANKACCT_NO1'		=>	$sbact['BANKACCT_NO1'] ? $sbact['BANKACCT_NO1'] : '',
								'BANKACCT_BID1'		=>	$sbact['BANKACCT_BID1'] ? $sbact['BANKACCT_BID1'] : '',
								'BANK_NAME1'		=>	$sbact['BANK_NAME1'] ? $sbact['BANK_NAME1'] : '',
								'BANK_MOBILE1'		=>	$sbact['BANK_MOBILE1'] ? $sbact['BANK_MOBILE1'] : ''
							);
							break;
						case '1':			//对私账户变更
							$sbact_data = array(
								'SHOP_BANK_FLAG'	=>	$sbact['SHOP_BANK_FLAG'],
								'BANKACCT_NAME2'	=>	$sbact['BANKACCT_NAME2'] ? $sbact['BANKACCT_NAME2'] : '',
								'BANKACCT_NO2'		=>	$sbact['BANKACCT_NO2'] ? $sbact['BANKACCT_NO2'] : '',
								'BANKACCT_BID2'		=>	$sbact['BANKACCT_BID2'] ? $sbact['BANKACCT_BID2'] : '',
								'BANK_NAME2'		=>	$sbact['BANK_NAME2'] ? $sbact['BANK_NAME2'] : '',
								'BANK_MOBILE2'		=>	$sbact['BANK_MOBILE2'] ? $sbact['BANK_MOBILE2'] : ''
							);
							break;
					}
					//银行帐户变更
					$sbact_res = D($this->MSbact)->updateSbact($where, $sbact_data);
					if($sbact_res['state']!=0){
						$m->rollback();		//不成功，则回滚
						$this->wrong('商户银行信息修改失败！');
					}
					
					//组装代扣帐户数据
					switch ($sdkb['SHOP_BANK_FLAG']) {
						case '0':			//对公账户变更
							$sdkb_data = array(
								'SHOP_BANK_FLAG'	=>	$sdkb['SHOP_BANK_FLAG'],
								'DKCO_MAP_ID'		=>	$sdkb['DKCO_MAP_ID'],
								'BANKACCT_NAME1'	=>	$sdkb['BANKACCT_NAME1'] ? $sdkb['BANKACCT_NAME1'] : '',
								'BANKACCT_NO1'		=>	$sdkb['BANKACCT_NO1'] ? $sdkb['BANKACCT_NO1'] : '',
								'BANKACCT_BID1'		=>	$sdkb['BANKACCT_BID1'] ? $sdkb['BANKACCT_BID1'] : '',
								'BANK_NAME1'		=>	$sdkb['BANK_NAME1'] ? $sdkb['BANK_NAME1'] : '',
								'SHOP_ACCT_FLAG'	=>	$sdkb['SHOP_ACCT_FLAG'] ? $sdkb['SHOP_ACCT_FLAG'] :'0',
								'DK_IDNO_TYPE'		=>	$sdkb['DK_IDNO_TYPE'] ? $sdkb['DK_IDNO_TYPE'] :'0',
								'DK_IDNO'			=>	$sdkb['DK_IDNO'] ? $sdkb['DK_IDNO'] :''
							);
							break;
						case '1':			//对私账户变更
							$sdkb_data = array(
								'SHOP_BANK_FLAG'	=>	$sdkb['SHOP_BANK_FLAG'],
								'DKCO_MAP_ID'		=>	$sdkb['DKCO_MAP_ID'],
								'BANKACCT_NAME2'	=>	$sdkb['BANKACCT_NAME2'] ? $sdkb['BANKACCT_NAME2'] : '',
								'BANKACCT_NO2'		=>	$sdkb['BANKACCT_NO2'] ? $sdkb['BANKACCT_NO2'] : '',
								'BANKACCT_BID2'		=>	$sdkb['BANKACCT_BID2'] ? $sdkb['BANKACCT_BID2'] : '',
								'BANK_NAME2'		=>	$sdkb['BANK_NAME2'] ? $sdkb['BANK_NAME2'] : '',
								'SHOP_ACCT_FLAG'	=>	$sdkb['SHOP_ACCT_FLAG'] ? $sdkb['SHOP_ACCT_FLAG'] :'0',
								'DK_IDNO_TYPE'		=>	$sdkb['DK_IDNO_TYPE'] ? $sdkb['DK_IDNO_TYPE'] :'0',
								'DK_IDNO'			=>	$sdkb['DK_IDNO'] ? $sdkb['DK_IDNO'] :''
							);
							break;
						default:
							// $m->rollback();		//不成功，则回滚
							// $this->wrong('代扣银行账户信息不正确,添加失败！');
							break;
					}
					//代扣银行帐户变更
					// $sdkb_res = D($this->MSdkb)->updateSdkb($where, $sdkb_data);
					// if($sdkb_res['state']!=0){
						// $m->rollback();		//不成功，则回滚
						// $this->wrong('商户银行信息修改失败！');
					// }
				}

			//组装其他配置数据
			$scfg_plv = get_level_val('scfg_plv');
			$scfg_data = array(
				'DIV_FLAG'		=>	$scfg['DIV_FLAG'] ? $scfg['DIV_FLAG'] : 0,				//分期特殊分配比例
				'CARD_OPENFEE'	=>	$scfg['CARD_OPENFEE'] ? $scfg['CARD_OPENFEE'] : 3000,	//卡收费总额
				'DIV_PER'		=>	$scfg['DIV_PER'] ? setMoney($scfg['DIV_PER']) : 0,		//预免卡分期比例
				'BOUND_RATE'	=>	$scfg['BOUND_RATE'] ? $scfg['BOUND_RATE'] : 50,			//积分兑换比例
				'RAKE_FLAG'		=>	$scfg['RAKE_FLAG'] ? $scfg['RAKE_FLAG'] : 0,			//特殊分配比例
				'CON_PER_RAKE'	=>	$scfg['CON_PER_RAKE'] ? setMoney($scfg['CON_PER_RAKE'],2,1) : 0,	//消费者比例
				'PLAT_PER_RAKE'	=>	$scfg['PLAT_PER_RAKE'] ? setMoney($scfg['PLAT_PER_RAKE']) : (10000-setMoney($scfg['CON_PER_RAKE'])),	//平台比例
				'DONATE_FLAG'	=>	$scfg['DONATE_FLAG'] ? $scfg['DONATE_FLAG'] : 0,		//转赠标志
				'DONATE_TYPE'	=>	$scfg['DONATE_TYPE'],									//转赠产品
				'DONATE_RATE'	=>	$scfg['DONATE_RATE'] ? $scfg['DONATE_RATE'] : 0,		//转赠率
				'PARTNER_MAP_ID'=>	$scfg_plv['pid'] ? $scfg_plv['pid'] : $home['PARTNER_MAP_ID'],		//转赠对象
				'DONATE_RES'	=>	$scfg['DONATE_RES']										//备注
			);
			//其他配置数据变更
			$scfg_res = D($this->MScfg)->updateScfg($where, $scfg_data);
			if($scfg_res['state']!=0){
				// $m->rollback();		//不成功，则回滚
				// $this->wrong('商户其他配置添加失败！');
			}

			//同步商户数据
			/* @author sea start */
			// if ($shop['SHOP_STATUS'] == 0) {
			// 	$sync_res = shop_sync_data($shop['SHOP_MAP_ID'],2);
			// 	if (!$sync_res) {
			// 		$m->rollback();		//不成功，则回滚
			// 		$this->wrong('商户修改数据同步失败');
			// 	}
			// }
			/* @author sea end */
			$m->commit();	//全部成功则提交
			$this->right($shop_res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//获取商户基本信息
		$shop_info  = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");
		//获取商户证照信息
		$scert_info = D($this->MScert)->findScert("SHOP_MAP_ID='".$id."'");
		//获取商户权限信息
		$sauth_info = D($this->MSauth)->findSauth("SHOP_MAP_ID='".$id."'");
		//获取商户结算信息
		$smdr_info  = D($this->MSmdr)->getSmdrfind("sm.SHOP_MAP_ID='".$id."' and sm.SHOP_STATUS = 0", 'sm.SETTLE_T,sm.PAY_TYPE, sm.JFB_PER_FEE, sm.JFB_FIX_FEE, sm.PER_FEE, sm.FIX_FEE','',"locate(sm.PAY_TYPE,'5,0,4')");
		//获取商户银行帐户信息
		$sbact_info = D($this->MSbact)->findSbact("SHOP_MAP_ID='".$id."'");
		//获取商户代扣银行帐户信息
		$sdkb_info  = D($this->MSdkb)->findSdkb("SHOP_MAP_ID='".$id."'");
		//获取商户其他配置信息
		$scfg_info = D($this->MScfg)->findScfg("SHOP_MAP_ID='".$id."'");
		if(empty($shop_info) || empty($scert_info) || empty($smdr_info)){
			$this->wrong("参数数据出错！");
		}
		$scert_info['OTHER'] = explode(',', $scert_info['OTHER_PHOTOS']);
		//渠道列表
		$channel_list = D($this->MChannel)->getChannellist("CHANNEL_STATUS = 0", 'CHANNEL_MAP_ID,CHANNEL_NAME');
		//商户权限数据
		$t_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');
		$this->assign ('channel_list', 		$channel_list );			//渠道列表
		$this->assign ('t_list', 	$t_list);							//交易开通
		$this->assign ('check_status',	 	 C('CHECK_POINT.check'));	//审核状态
		$this->assign ('bank_flag_redio',	 C('PARTNER_BANK_FLAG'));	//结算标志
		$this->assign ('settle_t_unit',		 C('SETTLE_T_UNIT'));		//结算周期
		$this->assign ('auth_trans_checked', str_split($sauth_info['AUTH_TRANS_MAP']));	//交易开通数据
		$this->assign ('shop_info', 	$shop_info);					//基础信息
		$this->assign ('scert_info', 	$scert_info);					//证照信息
		$this->assign ('sauth_info', 	$sauth_info);					//权限信息
		$this->assign ('smdr_info', 	$smdr_info);					//结算方式
		$this->assign ('sbact_info', 	$sbact_info);					//银行帐户
		$this->assign ('sdkb_info', 	$sdkb_info);					//代扣银行帐户
		$this->assign ('scfg_info', 	$scfg_info);					//其他配置
		$this->assign ('home', 			$home);							//当前登录用户数据
		//获取当前信息状态
		$where = "PARTNER_MAP_ID = '".$partner['PARTNER_MAP_ID']."'";
		if ($shop_info['SHOP_STATUS'] == 0 || $shop_info['SHOP_STATUS'] == 4) {
			$this->display('shop_edit2');
		}else{
			$this->display('shop_edit');
		}
	}	
	/*
	* 商户管理 冻结
	**/
	public function shop_close() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		if(is_array($id)){
			$where = array('SHOP_MAP_ID'=> array('in', implode(',', $id)));
		}else{
			$where = array('SHOP_MAP_ID'=> array('eq', $id));
		}
		$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		$m->startTrans();	//启用事务
		//判断操作项状态
		$shopstatus = M('shop')->where($where)->field('SHOP_STATUS')->select();
		foreach ($shopstatus as $key => $value) {
			if ($value['SHOP_STATUS']!='0') {
				$this->wrong('当前状态无法执行此操作');
			}
		}
		$res = D($this->MShop)->updateShop($where, array('SHOP_STATUS'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}

		//同步数据
		$syc_arr = array(
			'operateType' 	=>	'4',				//(操作类型)
			'oId'		 	=>	$id,				//(商户ID)
			'token' 		=>	strtoupper(md5(strtoupper(md5($id.'4'))))	//(签名）oId+ operateType 双次MD5
		);

/* @author sea start */
		//同步
		$url = SHOP_SYN_URL.'PointRepositoryOpenLibrary/ShopInfroSynchService';
		Add_LOG(CONTROLLER_NAME, json_encode($syc_arr));
/* @author sea end */
/*
		$resjson = httpPostForm($url, $syc_arr);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->status != '0') {
			$m->rollback();	//回滚
			$this->wrong('商户第三方POS数据同步修改失败');
		}
*/
		$m->commit();		//提交
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 商户管理 恢复
	**/
	public function shop_open() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		if(is_array($id)){
			$where = array('SHOP_MAP_ID'=> array('in', implode(',', $id)));
		}else{
			$where = array('SHOP_MAP_ID'=> array('eq', $id));
		}
		//判断操作项状态
		$shopstatus = M('shop')->where($where)->field('SHOP_STATUS')->select();
		foreach ($shopstatus as $key => $value) {
			if ($value['SHOP_STATUS']!='1' && $value['SHOP_STATUS']!='2') {
				$this->wrong('当前状态无法执行此操作');
			}
		}
		$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		$m->startTrans();	//启用事务
		$res = D($this->MShop)->updateShop($where, array('SHOP_STATUS'=> 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		//同步数据
		$syc_arr = array(
			'operateType' 	=>	'5',				//(操作类型)
			'oId'		 	=>	$id,				//(商户ID)
			'token' 		=>	strtoupper(md5(strtoupper(md5($id.'5'))))	//(签名）oId+ operateType 双次MD5
		);
		/* @author sea start */
		//同步
		$url = SHOP_SYN_URL.'PointRepositoryOpenLibrary/ShopInfroSynchService';
		Add_LOG(CONTROLLER_NAME, json_encode($syc_arr));
		/* @author sea end */
/*
		$resjson = httpPostForm($url, $syc_arr);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->status != '0') {
			$m->rollback();	//回滚
			$this->wrong('商户第三方POS数据同步修改失败');
		}
*/
		$m->commit();		//提交
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 商户管理 注销
	**/
	public function shop_cancel() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$where = array('SHOP_MAP_ID'=> array('eq', $id));
		
		//判断操作项状态
		$shopstatus = M('shop')->where($where)->field('SHOP_STATUS')->find();
		if ($shopstatus['SHOP_STATUS']!='0') {
			$this->wrong('当前状态无法执行此操作');
		}
		$res_str = '';
		$res_no = '0';
		//判断该商户下是否还有POS
		$pos_res = D($this->MPos)->countPos('p.SHOP_MAP_ID = "'.$id.'" and p.POS_STATUS = 0');
		if ($pos_res['state']!=0) {
			$res_no +=1;
			$res_str .= $res_no.'. 该商户还存在正常使用的POS, 请注销.<br />';
		}
		//判断该商户下是否还有未完成的代扣款
		$where1 = 'ACCT_NO = "S'.setStrzero($id, 8, $var='0', $type='l').'"';
		$lae_res= D($this->GLae)->findLae($where1, 'ACCT_VALBAL');
		if ($lae_res['ACCT_VALBAL']<0) {
			$res_no +=1;
			$res_str .= $res_no.'. 该商户还有'.abs($lae_res['ACCT_VALBAL']).'元没结算, 请结算交易.';
		}
		//报错提示信息
		if (!empty($res_str)) {
			$this->wrong($res_str);
		}
		$m = M();
		$m->startTrans();	//启用事务
		$res = D($this->MShop)->updateShop($where, array('SHOP_STATUS'=> 2));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		/* @author sea start */
		//同步商户数据（AppAerver）
		$sync_res = shop_sync_data($id,3);
		if (!$sync_res) {
			$m->rollback();		//不成功，则回滚
			$this->wrong('商户修改数据同步失败');
		}
		/* @author sea end */
		//同步数据
		$syc_arr = array(
			'operateType' 	=>	'3',								//(操作类型)
			'oId'		 	=>	$id,								//(商户ID)
			'token' 		=>	strtoupper(md5(strtoupper(md5($id.'3'))))	//(签名）oId+ operateType 双次MD5
		);

		//同步
		/* @author sea start */
		$url = SHOP_SYN_URL.'PointRepositoryOpenLibrary/ShopInfroSynchService';
		Add_LOG(CONTROLLER_NAME, json_encode($syc_arr));
		/* @author sea end */
/*
		$resjson = httpPostForm($url, $syc_arr);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->status != '0') {
			$m->rollback();	//回滚
			$this->wrong('商户第三方POS数据同步修改失败');
		}
*/
		$m->commit();		//不成功，则回滚
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 商户管理 审核
	**/
	public function shop_check() {
		$post = I('post');
		if ($post['submit'] == "shop_check") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			if($post['CHECK_POINT']==4){
				if ($post['CHECK_POINT'] == 5) {
					if (empty($post['CHECK_DESC'])) {
						$this->wrong('请填写初审不通过原因');
					}
				}
				/**==============================实名认证开始==============================*/
				if ($post['key'] != '0000' && $home['ROLE_NAME'] == 'OEM') {
					$this->wrong('未实名认证或认证不通过');
				}
				/**==============================实名认证结束==============================*/

				//========================   2017.03.30 CRYSTAL  BEGIN  ==================================
				
				//上福进件
				$shopSZModel = M('shop_intopieces');
				$shopSZInfo = $shopSZModel->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID'],'STATUS'=>0))->find();
				// if($shopSZInfo){
				// 	$this->wrong('商户已进件');
				// }
				$isNew = I('isNew');
				if($isNew == 'old1'){
					$shopInfo = D($this->MShop)->findmoreShop2(array('s.SHOP_MAP_ID'=>$post['SHOP_MAP_ID'],'smdr.PAY_TYPE'=>5));
					$url = PHP_API_URL.'index.php/Home/Shop/upShopinfo_sea';
					$data = array(
						'shop_map_id'=>$post['SHOP_MAP_ID'],
						'user_login_mobile'=>$shopInfo['MOBILE'],
						'mcht_name'=>$shopInfo['SHOP_NAME'],
						'mcht_short_name'=>$shopInfo['SHOP_NAMEABCN'],
						'address'=>$shopInfo['ADDRESS'],
						'leg_phone'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANK_MOBILE1']:$shopInfo['BANK_MOBILE2'],
						'county_code'=>$shopInfo['CITY_NO'],
						'leg_name'=>$shopInfo['LP_NAME'],
						'leg_mail'=>$shopInfo['EMAIL'],
						'per_fee'=>$shopInfo['JFB_PER_FEE'],
						'acc_name'=>$shopInfo['LP_NAME'],
						'acc_bank_name'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANK_NAME1']:$shopInfo['BANK_NAME2'],
						'acc_no'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANKACCT_NO1']:$shopInfo['BANKACCT_NO2'],
						'service_tel'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANK_MOBILE1']:$shopInfo['BANK_MOBILE2'],
						'id_no'=>$shopInfo['LP_ID'],
						'settle_cycle'=>$shopInfo['SETTLE_T'],
						'acc_bank_no'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANKACCT_BID1']:$shopInfo['BANKACCT_BID2']
					);
					$data['funcName'] = 'upShopinfo_sea';
					$data['signTime'] = time();
					$data["sign"] = rySignStr($data);
					$jsonStr = json_encode($data);
					// $this->wrong($jsonStr);
					$result = getCurlDataByjson($url, $jsonStr);
					Add_LOG('SHOP',$result);
					//dump($result);exit;
					$this->testlog(__LINE__. 'LINE ajax_piece_review' .json_encode($result));
					if ($result->status != "200") {
						$datas['NOTE3'] = $result->message;
						D($this->MShop)->updateShop($where,$datas);
						// $post['CHECK_POINT'] = 5;
						// $post['CHECK_DESC'] = $datas['NOTE3'];
						// if($result->message != '鉴权未通过，请填写正确信息或更新其它银行卡后重试~'){
							// $this->wrong($isNew." SHANGFU:".$result->message);
						// }
					}else{
						$datas['HOST_MAP_ID'] = 1;
						D($this->MShop)->updateShop($where,$datas);
					}
				}else{
					// $url = PHP_API_URL.'index.php/Home/Reg/upShopinfo';
					// $data = array(
					// 	'shop_map_id'=>$post['SHOP_MAP_ID']
					// );
					// $data['funcName'] = 'upShopinfo';
				}
				// $data['signTime'] = time();
				// $data["sign"] = rySignStr($data);
				// $jsonStr = json_encode($data);
				// // Add_LOG('SHOP',$url.' '.$jsonStr);
				// $result = getCurlDataByjson($url, $jsonStr);
				// Add_LOG('SHOP',$result);
				// //dump($result);exit;
				// //$this->testlog(__LINE__. 'LINE ajax_piece_review' .json_encode($result));
				// if ($result->status != "200") {
				// 	$datas['NOTE3'] = $result->message;
				// 	D($this->MShop)->updateShop($where,$datas);
				// 	// $post['CHECK_POINT'] = 5;
				// 	// $post['CHECK_DESC'] = $datas['NOTE3'];
				// 	// if($result->message != '鉴权未通过，请填写正确信息或更新其它银行卡后重试~'){
				// 		$this->wrong($isNew." SHANGFU:".$result->message);
				// 	// }
				// }else{
				// 	$datas['HOST_MAP_ID'] = 1;
				// 	D($this->MShop)->updateShop($where,$datas);
				// }
				//========================   2017.03.30 CRYSTAL  END  ====================================

			}


			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '1',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户进件】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			//修改状态
			$shop_res = D($this->MShop)->updateShop($where,array('SHOP_STATUS' => $post['CHECK_POINT']));	//基本信息状态修改
			if (!$shop_res) {
				$this->wrong("审核操作失败！");
			}
			//添加审核记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('添加记录失败');
			}
			// if($result && $result->status != "200"){
				// $this->wrong("SHANGFU:".$result->message);
			// }

			/**==============================极光推送开始==============================*/
			if ($post['CHECK_POINT'] == 5) {
				$shopModel = M('shop');
				$shop = $shopModel->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->field('CHANNEL_MAP_ID')->find();
				$appPackage = D($this->MChannel)->findChannel("CHANNEL_MAP_ID = '".$shop['CHANNEL_MAP_ID']."'", 'CHANNEL_NAMEAB');
				$url = 'http://172.19.220.31:90/kakaloan/api/jpush/send';
				//type类型：1:发送广播，2:发送通知。
				//flag类型：0:全局发送广播，1:根据SHOP_MAP_ID单个发送通知，针对审核，2：SHOP_MAP_ID商户，普通推送
				$para = array(
						'content'=>$post['CHECK_DESC'],
						'shopMapId'=>$post['SHOP_MAP_ID'],
						'type'=>$post['CHECK_POINT'],
						'title'=>'审核不通过',
						'messageId'=>$res['checkId']?$res['checkId']:'00',
						'appPackage'=>$appPackage['CHANNEL_NAMEAB'],
						'alert'=>'【启禀小主】您提交的审核资料审核不通过，原因：'.$post['CHECK_DESC']
					);
				$result = json_decode(doPostArr($url,$para),true);
				if ($result['resCode'] != 0) {
					$this->wrong($result['resMsg']);
				}
			}
			/**==============================极光推送结束==============================*/

			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		/**==============================实名认证开始==============================*/
		$scertModel = M('scert');
		$scert = $scertModel->field('LP_NAME,LP_ID')->where(array('SHOP_MAP_ID'=>$id,'SHOP_STATUS'=>0))->find();
		if (empty($scert['LP_NAME']) || empty($scert['LP_ID'])) {
			$this->wrong('请填写该商户姓名和身份证号');
		}
		$this->assign('LP_NAME',$scert['LP_NAME']);
		$this->assign('LP_ID',$scert['LP_ID']);
		/**==============================实名认证结束==============================*/
		//判断当前状态是否符合复审操作
		$shoptatus = D($this->MShop)->findShop('SHOP_MAP_ID = "'.$id.'"','SHOP_STATUS');
		if ($shoptatus['SHOP_STATUS'] != 6) {
			$this->wrong('当前状态不允许初审操作');
		}
		//获取最新一条审核记录
		$check_no = '3'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'"');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$this->assign('home',$home);
		$this->shop_show('shop_check');
	}

	public function getauth(){
		$LP_ID = getPara("LP_ID");
		$LP_NAME = getPara("LP_NAME");
		$mdata = array(
           "serviceCode"=>C('SERVICECODE'),
           "name"=>$LP_NAME,//姓名
           "idNumber"=>$LP_ID,//身份证号
        );
        $rdata = initBankParams($mdata);
        $rt = json_decode(reqApi($rdata),true);
		$ext = $rt['ext'];
        $image = base64_decode($ext['photo']);
        $returnUrl = DownLoadPic($image);
        $para = array('returnUrl'=>$returnUrl,'msg'=>$rt['msg'],'key'=>$rt['key']);
		echo json_encode($para);
	}


	/*
	* 商户管理 复核
	**/
	public function shop_recheck() {
		$post = I('post');
		if ($post['submit'] == "shop_recheck") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			if ($post['CHECK_POINT'] == 3 && empty($post['CHECK_DESC'])) {
				$this->wrong('请填写复核不通过原因');
			}
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '1',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户进件】复核操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			//修改状态
			$m = M();
			$m->startTrans();	//启用事务
			if($post['CHECK_POINT'] != '0'){
				$shop_res = D($this->MShop)->updateShop($where,array('SHOP_STATUS' => $post['CHECK_POINT']));		//基本信息状态修改
			}else{
				//判断渠道是否自主（如果是自主的，则不用处动生成商户编号）
				//$shop_d = D($this->MShop)->findShop('SHOP_MAP_ID = "'.$post['SHOP_MAP_ID'].'"','CHANNEL_MAP_ID');
				//$upshop = array();
				/*if ($shop_d['CHANNEL_MAP_ID'] == '100002') {
					//生成并更新商户编号
					$shop_no = create_shopno($post['SHOP_MAP_ID']);
					if($shop_no){
						$upshop['SHOP_NO'] = $shop_no;
					}else{
						$m->rollback();
						$this->wrong('生成商户编号失败');
					}
					
				}*/


				//生成并更新商户编号
				$upshop = array();
				// $shop_no = create_shopno($post['SHOP_MAP_ID']);
				// if($shop_no){
				// 	$upshop['SHOP_NO'] = $shop_no;
				// }else{
				// 	//$m->rollback();
				// 	$this->wrong('生成商户编号失败');
				// }
				$upshop['SHOP_STATUS'] = $post['CHECK_POINT'];
				/*//========================   2017.03.30 CRYSTAL  BEGIN  ==================================
				$shopInfo = D($this->MShop)->findmoreShop2(array('s.SHOP_MAP_ID'=>$post['SHOP_MAP_ID'],'smdr.PAY_TYPE'=>5));
				
				//上福进件
				$url = 'http://172.16.0.25/index.php/Home/Shop/upShopinfo_sea';
                $data = array(
                    'user_login_mobile'=>$shopInfo['MOBILE'],
                    'mcht_name'=>$shopInfo['SHOP_NAME'],
                    'mcht_short_name'=>$shopInfo['SHOP_NAMEABCN'],
                    'address'=>$shopInfo['ADDRESS'],
                    'leg_phone'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANK_MOBILE1']:$shopInfo['BANK_MOBILE2'],
                    'county_code'=>$shopInfo['CITY_NO'],
                    'leg_name'=>$shopInfo['LP_NAME'],
                    'leg_mail'=>$shopInfo['EMAIL'],
                    'per_fee'=>$shopInfo['JFB_PER_FEE'],
                    'acc_name'=>$shopInfo['LP_NAME'],
                    'acc_bank_name'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANK_NAME1']:$shopInfo['BANK_NAME2'],
                    'acc_no'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANKACCT_NO1']:$shopInfo['BANKACCT_NO2'],
                    'service_tel'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANK_MOBILE1']:$shopInfo['BANK_MOBILE2'],
                    'id_no'=>$shopInfo['LP_ID'],
                    'settle_cycle'=>$shopInfo['SETTLE_T'],
                    'acc_bank_no'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANKACCT_BID1']:$shopInfo['BANKACCT_BID2']
                );

                $data['signTime'] = time();
                $data['funcName'] = 'upShopinfo_sea';
                $data["sign"] = rySignStr($data);
                $jsonStr = json_encode($data);
				// Add_LOG('SHOP',$url.' '.$jsonStr);
                $result = getCurlDataByjson($url, $jsonStr);
				Add_LOG('SHOP',$result);
                //dump($result);exit;
                //$this->testlog(__LINE__. 'LINE ajax_piece_review' .json_encode($result));
                if ($result->status != "200") {
                    $datas['NOTE3'] = $result->message;
                    D($this->MShop)->updateShop($where,$datas);
                    $this->wrong("SHANGFU:".$result->message);
                }
                //========================   2017.03.30 CRYSTAL  END  ====================================
				*/
				$shopModel = M('shop');
				$shopInfo = $shopModel->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
				if(empty($shopInfo['SHOP_PIN'])){
					$upshop['SHOP_PIN'] = strtoupper(md5(strtoupper(md5('888888'))));
				}
				
				//如果为某个机构的渠道商户则为其生成下游商户号
				/*if($shopInfo['CHANNEL_MAP_ID']>0){
					$channelModel = M('channel');
					$channel = $channelModel->alias("ch")->join('pai_db_jfb.a_user u on u.CHANNEL_MAP_ID=ch.CHANNEL_MAP_ID')
								->where(array('ch.CHANNEL_STATUS'=>0, 'u.USER_STATUS'=>0, 'ch.CHANNEL_MAP_ID'=>$shopInfo['CHANNEL_MAP_ID']))->find();
					if($channel){
						$len = strlen($channel['CHANNEL_NAMEAB']);
						$n = 15- $len;
						$jgh = $channel['CHANNEL_NAMEAB'];
						$downmchModel = M("downmch");
						$downmch = $downmchModel->where(array('MCH_ID'=>array('like',$jgh.'%')))->find();
						if($downmch){
							$mch_id = $downmch['MCH_ID'] + 1;
						}else{
							$mch_id = $jgh.str_pad(1,$n,'0',STR_PAD_LEFT);
						}
						$key = strtoupper(hash_hmac('md5', $mch_id, '1842803'.time()));
						$data = array(
							'SHOP_MAP_ID' => $shopInfo['SHOP_MAP_ID'],
							'MCH_ID' => $mch_id,
							'KEY' => $key,
							'FLAG' => 0,
							'STATUS' => 1,
							'CREATE_TIME' => date('Y-m-d H:i:s'),
						);
						$res = $downmchModel->add($data);
						// $this->wrong("审核操作失败！".$res.' '.$downmchModel->getLastSql());
					}
				}*/
				
				$shop_res = D($this->MShop)->updateShop($where,$upshop);		//基本信息状态修改
				if (!$shop_res) {
					$m->rollback();
					$this->wrong("审核操作失败！");
				}
				/* @author sea start */
				//同步商户数据
				//查找小二后台是否已有数据
				// $jfbModel = M('shop_', DB_PREFIX_JFB, DB_DSN_JFB);
				// $jfbShop = $jfbModel->query("select * from jfb_shop_ where ID_=".$post['SHOP_MAP_ID']);
				// $sync_res = shop_sync_data($post['SHOP_MAP_ID'],$jfbShop?2:1);
				// if (!$sync_res) {
				// 	$m->rollback();
				// 	$this->wrong('商户进件数据同步失败');
				// }
				/* @author sea end */
			}
			
			//添加审核记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('添加记录失败');
			}
			$m->commit();
			
			
			/*=======================极光推送开始 ============================================ */
			$shopModel = M('shop');
			$shop = $shopModel->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->field('CHANNEL_MAP_ID')->find();
			$appPackage = D($this->MChannel)->findChannel("CHANNEL_MAP_ID = '".$shop['CHANNEL_MAP_ID']."'", 'CHANNEL_NAMEAB');
			$url = 'http://172.19.220.31:90/kakaloan/api/jpush/send';
			//type类型：1:发送广播，2:发送通知。
			//flag类型：0:全局发送广播，1:根据SHOP_MAP_ID单个发送通知，针对审核，2：SHOP_MAP_ID商户，普通推送
			if ($post['CHECK_POINT'] == 0) {
				$para = array(
						'content'=>'恭喜您，您提交的审核资料已经审核通过，现全部功能已向您开放，重新登录APP就可以正常使用。祝您生活愉快 ！',
						'shopMapId'=>$post['SHOP_MAP_ID'],
						'type'=>$post['CHECK_POINT'],
						"title"=>'审核通过',
						'messageId'=>$res['checkId']?$res['checkId']:'00',
						'appPackage'=>$appPackage['CHANNEL_NAMEAB'],
						'alert'=>'【启禀小主】您提交的审核资料已经通过！',
					);
			}else{
				$para = array(
					'content'=>$post['CHECK_DESC'],
					'shopMapId'=>$post['SHOP_MAP_ID'],
					'type'=>$post['CHECK_POINT'],
					'title'=>'审核不通过',
					'messageId'=>$res['checkId']?$res['checkId']:'00',
					'appPackage'=>$appPackage['CHANNEL_NAMEAB'],
					'alert'=>'【启禀小主】您提交的审核资料审核不通过，原因：'.$post['CHECK_DESC']
				);
			}
			
			$result = json_decode(doPostArr($url,$para),true);
			if ($result['resCode'] != 0) {
				$this->wrong($result['resMsg']);
			}
			/*=======================极光推送结束 ============================================== */

			//+++++++++++ START 2017.10.18 CRYSTAL +++++++++++++
			$channelModel = M('channel');
			$data = array(
				'CHANNEL_MAP_ID' => $shopInfo['CHANNEL_MAP_ID'],
				'CHANNEL_NAMEAB' => array('exp', " REGEXP '[a-z]'"),
			);
			$channelInfo = $channelModel->field('CHANNEL_NAME,CHANNEL_NAMEAB')->where($data)->find();
			if($channelInfo){
				//发送审核短信
				$data = array(
					"mobile" => $shopInfo['MOBILE'],
					"cname" => $channelInfo['CHANNEL_NAME'],
					"app_nor" => $channelInfo['CHANNEL_NAMEAB'],
					"model_id" => 7,
					"nonce_str" => 'ns'.time()
				);
				$key = 'C55ED90531F97512252224039DD3CEB9';
				$data['sign'] = szSign($data, $key);
				$url = PHP_API_URL.'index.php/Home/Api/func_succ_msg';
				doPost($url,$data);
			}
			//+++++++++++ END 2017.10.18 CRYSTAL +++++++++++++
			
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}

		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//判断当前状态是否符合复审操作
		$shopstatus = D($this->MShop)->findShop('SHOP_MAP_ID = "'.$id.'"','SHOP_STATUS');
		if ($shopstatus['SHOP_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '3'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$this->shop_show('shop_check');
	}

/*
	* 商户管理 批量复核
	**/
	public function shop_recheck_more() {
		Add_LOG(CONTROLLER_NAME);
		//查询商户资料
		$shop_d = D($this->MShop)->findShop('SHOP_MAP_ID = "'.$shop_id.'"','CHANNEL_MAP_ID,MCC_CODE,CITY_NO');
		//判断MCC和地区码是否存在，
		if (empty($shop_d['MCC_CODE']) ||empty($shop_d['CITY_NO'])) {
			Add_LOG(CONTROLLER_NAME, '商户ID：'.$shop_id.' MCC码或地区码不存在，');
			echo '商户ID：'.$shop_id.' MCC码或地区码不存在，';
		}
		//判断生成的商户号是不是15位
		$shop_no = create_shopno($shop_id);
		if(strlen($shop_no) != 15){
			Add_LOG(CONTROLLER_NAME, '生成商户号不复合规范，复核失败');
			echo '生成商户号不复合规范，复核失败';
		}
		//更新为正常状态
		$shop_res = D($this->MShop)->updateShop('SHOP_MAP_ID = "'.$shop_id.'"', array('SHOP_STATUS' => 0));		//基本信息状态修改
		if (!$shop_res) {
			Add_LOG(CONTROLLER_NAME, '商户ID：'.$shop_id.' 修改复核失败');
			echo '商户ID：'.$shop_id.' 修改复核失败';
		}
		//记录日志
		$post = I('post');
		if ($post['submit'] == "shop_recheck") {
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '1',//$post['CHECK_FLAG'],
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'],
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			if(empty($post['SHOP_MAP_ID']) || $check_data['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			//修改状态
			$m = M();
			$m->startTrans();	//启用事务
			if($post['CHECK_POINT'] != '0'){
				$shop_res = D($this->MShop)->updateShop($where,array('SHOP_STATUS' => $post['CHECK_POINT']));		//基本信息状态修改
			}else{
				//判断渠道是否自主（如果是自主的，则不用处动生成商户编号）
				$shop_d = D($this->MShop)->findShop('SHOP_MAP_ID = "'.$post['SHOP_MAP_ID'].'"','CHANNEL_MAP_ID');
				$upshop = array();
				if ($shop_d['CHANNEL_MAP_ID'] == '100002') {
					//生成并更新商户编号
					$shop_no = create_shopno($post['SHOP_MAP_ID']);
					if($shop_no){
						$upshop['SHOP_NO'] = $shop_no;
					}else{
						$m->rollback();
						$this->wrong('生成商户编号失败');
					}
					
				}
				$upshop['SHOP_STATUS'] = $post['CHECK_POINT'];
				$shop_res = D($this->MShop)->updateShop($where,$upshop);		//基本信息状态修改
				if (!$shop_res) {
					$m->rollback();
					$this->wrong("审核操作失败！");
				}
				//同步商户数据
				/* @author sea start */
				$sync_res = shop_sync_data($post['SHOP_MAP_ID'],1);
				if (!$sync_res) {
					$m->rollback();
					$this->wrong('商户进件数据同步失败');
				}
				/* @author sea end */
			}
			
			//添加审核记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('添加记录失败');
			}
			$m->commit();
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}

		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//判断当前状态是否符合复审操作
		$shopstatus = D($this->MShop)->findShop('SHOP_MAP_ID = "'.$id.'"','SHOP_STATUS');
		if ($shopstatus['SHOP_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '3'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$this->shop_show('shop_check');
	}

	/*
	* 安装申请
	**/
	public function install_add() {
		$post = I('post');
		if ($post['submit'] == "install_add") {
			foreach ($post['FACTORY_MAP_ID'] as $key => $value) {
				if (empty($value) || empty($post['MODEL_MAP_ID'][$key])) {
					$this->wrong('请选择厂商和型号');
				}
				if (empty($post['NUM'][$key])) {
					$this->wrong('请添写申请数量');
				}
			}
			$home = session('HOME');
			$install_data = array(
				'SHOP_MAP_ID'		=> $post['SHOP_MAP_ID'],
				'BRANCH_MAP_ID'		=> $post['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=> $post['PARTNER_MAP_ID'],
				'APPLY_DATE'		=> date('Ymd'),
				'INSTALL_FLAG'		=> 1,	//装机状态(0:完成  1:申请受理中)
				'CREATE_USERID'		=> $home['USER_ID'],
				'CREATE_USERNAME'	=> $home['USER_NAME'],
				'CREATE_TIME'		=> date('YmdHis')
			);
			$m = M();
			$m->startTrans();//开启事务
			foreach ($post['FACTORY_MAP_ID'] as $key => $val) {
				if(!empty($val) && !empty($post['MODEL_MAP_ID'][$key]) && !empty($post['NUM'][$key])){
					$install_data['FACTORY_MAP_ID'] = $val;
					$install_data['MODEL_MAP_ID']	= $post['MODEL_MAP_ID'][$key];
					$install_data['NUM'] 			= $post['NUM'][$key];
					$result = D($this->MSposreq)->addSposreq($install_data);
					if($result['state'] != 0){
						$m->rollback();		//不成功，则回滚
						$this->wrong('操作失败');
					}
					/*//添加POS数据
					for ($i=0; $i < $install_data['NUM']; $i++) { 
						//获取当前最新的POS_NO
						$data = D($this->MPos)->findPos('','POS_NO');
						$pos_no = setStrzero($data['POS_NO']+1,8);
						//插入POS表
						$pos_data = array(
							'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],				//分支编号
							'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],			//代理编号
							'SHOP_MAP_ID'		=>	$post['SHOP_MAP_ID'],				//商户ID
							'SHOP_NO'			=>	$post['SHOP_NO'],					//商户编号
							'DEVICE_SN'			=>	$pos_no,							//设备序列号
							'POS_NO'			=>	$pos_no,							//终端号
							'POS_INDEX'			=>	$post['SHOP_NO'].$pos_no,			//备用索引
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
							'COM_INDEX'			=>	'' ,								//通讯参数索引
							'KEY_INDEX'			=>	$post['SHOP_NO'].$pos_no,			//对称密钥索引
						);
						$pos_res = D($this->MPos)->addPos($pos_data);
						if ($pos_res['state'] != 0){
							$m->rollback();//不成功，则回滚
							$this->wrong('商户POS数据添加失败');
						}
					}
					$m->commit();//成功则提交*/
				}else{
					$this->wrong('操作失败');
				}
			}
			//添加安装协议照片
			$scert_data = array(
				'INSTALL_PHOTO' => $post['INSTALL_PHOTO']
			);
			//证照数据变更
			$scert_res = D($this->MScert)->updateScert('SHOP_MAP_ID = '.$post['SHOP_MAP_ID'],$scert_data);
			if($scert_res['state']!=0){
				$m->rollback();		//不成功，则回滚
				$this->wrong('安装协议修改失败！');
			}
			$m->commit();//成功则提交*/
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//获取商户基本信息
		$shop_info = D($this->MShop)->findmoreNewShop('SHOP_MAP_ID="'.$id.'"');
		if($shop_info['SHOP_STATUS'] != 0){
			$this->wrong('当前状态无法执行此操作');
		}
		$this->assign('shop_info',$shop_info);
		$this->display('install_add');
	}


	/*
	* 安装申请【批量】
	**/
	public function install_add_more() {
		$home = session('HOME');
		if (empty($home['USER_ID'])) {
			echo "笨蛋，先登录";exit;
		}
		$open = I('submit');
		$bid = I('bid');
		$pid = I('pid');
		$model_name = I('model_name');
		if (empty($bid)) {
			echo "请添写组织id";exit;
		}
		if (empty($model_name)) {
			echo "请添写型号名称";exit;
		}
		if (!($model_name=='H80' or $model_name=='S910')) {
			echo "型号名称填写错误";exit;
		}
		//查询机型
		$info = D('MModel')->findModel("MODEL_NAME='".$model_name."' and MODEL_STATUS = 0", 'm.*, f.FACTORY_NAME');
		if (empty($info)) {
			echo "未找到相关型号数据";exit;
		}
		if (!empty($pid)) {
			$pids = get_plv_childs($pid,1);
			$where1 .= " and PARTNER_MAP_ID in(".$pids.")";
		}
		$where = "BRANCH_MAP_ID = ".$bid." and SHOP_STATUS = 0 and SHOP_MAP_ID < 100000".$where1;
		//$where = "BRANCH_MAP_ID = 836 and SHOP_STATUS = 0 and PARTNER_MAP_ID = '".$pid."'";
		$list = M('shop')->where($where)->select();
		if ($open == "install_add_more") {
			$n = 0;
			$home = session('HOME');
			foreach ($list as $key => $val) {
				$install_data = array(
					'SHOP_MAP_ID'		=> $val['SHOP_MAP_ID'],
					'BRANCH_MAP_ID'		=> $val['BRANCH_MAP_ID'],
					'PARTNER_MAP_ID'	=> $val['PARTNER_MAP_ID'],
					'APPLY_DATE'		=> date('Ymd'),
					'INSTALL_FLAG'		=> 1,	//装机状态(0:完成  1:申请受理中)
					'CREATE_USERID'		=> $home['USER_ID'],
					'CREATE_USERNAME'	=> 'myself0728',
					'CREATE_TIME'		=> date('YmdHis'),
					'FACTORY_MAP_ID' 	=> $info['FACTORY_MAP_ID'],
					'MODEL_MAP_ID'		=> $info['MODEL_MAP_ID'],
					'NUM'				=> '1',
				);
				$result = D($this->MSposreq)->addSposreq($install_data);
				if($result['state'] == 0){
					$n++;
				}
			}
			echo "有".$n."商户申请成功";
		}
	}
	
	/*
	* 商户权限变更管理 列表
	**/
	public function sauth() {
		$post = I('post');
		if($post['submit'] == "sauth"){
			$where = "1=1";			
			$soplv = filter_data('soplv');	//列表查询
			//状态
			if($post['SHOP_STATUS'] != '') {
				if($post['SHOP_STATUS'] == 0){
					$where .= " and sa.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}else{
					$where .= " and tmp.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}
			}
			//分支
			if($soplv['bid'] != '') {
				$where .= " and sh.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$where .= " and sh.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			//MCC
			if($post['MCC_TYPE'] != '') {
				$where .= " and sh.MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//商户类型码
			if ($post['MCC_CODE']) {
				$where .= " and sh.MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//商户号
			if ($post['SHOP_NO']) {
				$where .= " and sh.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and sh.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//分页
			if($post['SHOP_STATUS'] == '0'){
				$count = D($this->MSauth)->countNotmpSauth($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->MSauth)->getNotmpSauthlist($where, 'sa.SHOP_MAP_ID,sh.SHOP_STATUS,sh.SHOP_NAME,sh.SHOP_NO,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME', $p->firstRow.','.$p->listRows, 'sa.SHOP_MAP_ID desc');
			}else{
				$count = D($this->MSauth)->countSauth($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->MSauth)->getSauthlist($where, 'sa.SHOP_MAP_ID,sa.SHOP_STATUS,sh.SHOP_NAME,sh.SHOP_NO,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME,tmp.SHOP_MAP_ID as TMP_ID,tmp.SHOP_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,sa.SHOP_MAP_ID desc');
			}			
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set (	'_currentUrl_', 	__SELF__);
		$this->assign ('mcc_type',C('MCC_TYPE'));					//MCC类型
		//审核状态数组
		$this->assign ('shop_status',C('CHECK_POINT.all'));			//通道全部状态
		$this->assign ('shop_status_all',C('CHECK_POINT.all2'));			//通道全部状态																		 
		$this->assign ('shop_status_check',C('CHECK_POINT.check'));		//部分状态
		$this->display();
	}
	/*
	* 商户权限变更管理 修改
	**/
	public function sauth_edit() {
		$sauth = I('sauth');
		if($sauth['submit'] == "sauth_edit") {
			$home = session('HOME');
			//组装权限数据
			$sauth_data = array(
				'SHOP_MAP_ID'	 =>	$sauth['SHOP_MAP_ID'],
				'SHOP_STATUS'	 =>	6,
				'AUTH_TRANS_MAP' =>	get_authstr($sauth['AUTH_TRANS_MAP']),
				'DAY_MAXAMT'	 =>	$sauth['DAY_MAXAMT'] ? setMoney($sauth['DAY_MAXAMT'], '6') : 10000000,
				'DAY_CASH_MAXAMT'=>	$sauth['DAY_CASH_MAXAMT'] ? setMoney($sauth['DAY_CASH_MAXAMT'], '6') : 100000000,
				'CASH_MAXAMT'	 =>	$sauth['CASH_MAXAMT'] ? setMoney($sauth['CASH_MAXAMT'], '6') : 1000000
			);
			//判断当前数据是否正在发生变更
			$where = 'SHOP_MAP_ID = "'.$sauth['SHOP_MAP_ID'].'"';
			$sauth_tmp = D($this->MSauth)->findSauth_tmp($where);
			if ($sauth_tmp['SHOP_STATUS'] == 4) {
				$this->wrong('当前数据已进入待复审状态,如需变更请等待复审结束后,再次提交!');
			}
			//证件数据入tmp库
			if($sauth['flag'] == 1){
				$res = D($this->MSauth)->updateSauth_tmp("SHOP_MAP_ID='".$sauth['SHOP_MAP_ID']."'", $sauth_data);
			}else{
				$res = D($this->MSauth)->addSauth_tmp($sauth_data);
			}
			if($res['state']!=0){
				$this->wrong('商户权限信息变更失败！');
			}

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($sauth['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '【商户权限信息变更】',
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
			$this->wrong('缺少参数2！');
		}
		$idarr = explode('_', $ids);
		$id    = $idarr[0];
		$flag  = $idarr[1];
		$shop_info = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$sauth_info = D($this->MSauth)->findSauth_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$sauth_info = D($this->MSauth)->findSauth("SHOP_MAP_ID='".$id."'");
		}
		if(empty($shop_info) || empty($sauth_info)){
			$this->wrong("参数数据出错！");
		}
		if ($sauth_info['SHOP_STATUS'] == 4) {
			$this->wrong('当前状态不允许修改操作');
		}

		$sauth_info['flag'] = $flag;
		$t_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');
		$this->assign ('t_list', 	$t_list);											//交易开通
		$this->assign ('shop_info', $shop_info);
		$this->assign ('sauth_info', $sauth_info);
		$this->assign ('auth_trans_checked', str_split($sauth_info['AUTH_TRANS_MAP']));	//交易开通数据
		$this->display();
	}	
	/*
	* 商户权限变更管理 初审
	**/
	public function sauth_check() {
		$post = I('post');
		if($post['submit'] == "sauth_check") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT'] == ''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户权限变更】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			//更新表审核状态
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			$sauth_tmp = D($this->MSauth)->updateSauth_tmp($where,array('SHOP_STATUS' => $post['CHECK_POINT']));
			if ($sauth_tmp['state']!=0) {
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

		$shop_info = D($this->MShop)->findmoreNewShop('SHOP_MAP_ID = "'.$id.'"');
		if($flag == 1){
			$sauth_info = D($this->MSauth)->findSauth_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$sauth_info = D($this->MSauth)->findSauth("SHOP_MAP_ID='".$id."'");
		}
		if(empty($shop_info) || empty($sauth_info)){
			$this->wrong("参数数据出错！");
		}
		if($sauth_info['SHOP_STATUS'] != 6){
			$this->wrong('当前状态不允许此操作');
		}
		$shop_info['CHECK_FLAG'] = $flag;
		$t_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');
		$this->assign ('t_list', 	$t_list);											//交易开通
		$this->assign ('shop_info', $shop_info);
		$this->assign ('sauth_info', $sauth_info);
		$this->assign ('auth_trans_checked', str_split($sauth_info['AUTH_TRANS_MAP']));	//交易开通数据
		$this->display();
	}
	
	/*
	* 商户权限变更管理 复审
	**/
	public function sauth_recheck() {
		$post = I('post');
		if ($post['submit'] == "sauth_recheck") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户权限变更】复核操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$sauth = D($this->MSauth)->updateSauth_tmp($where,array('SHOP_STATUS' => $post['CHECK_POINT']));
				if($sauth['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$sauth_change = D($this->MSauth)->findSauth_tmp($where);
				$sauth_change['SHOP_STATUS'] = 0;
				$m = M();
				$m->startTrans();	//启用事务
				//更新主表数据
				$sauth = D($this->MSauth)->updateSauth($where,$sauth_change);
				if($sauth['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MSauth)->delSauth_tmp('SHOP_MAP_ID = '.$post['SHOP_MAP_ID']);
				if ($delres['state'] != 0){
					$m->rollback();
					$this->wrong("操作失败!");
				}
/* @author sea start */
				//同步商户数据到小二后台
				// $sync_res = shop_sync_data($post['SHOP_MAP_ID'],2);
				// if (!$sync_res) {
					// $m->rollback();		//不成功，则回滚
					// $this->wrong('商户修改第三方权限数据同步失败');
				// }
/* @author sea end */
				//查询商户权限
				$sauth_data = D('MSauth')->findSauth("SHOP_MAP_ID='".$post['SHOP_MAP_ID']."'",'CASH_MAXAMT,AUTH_TRANS_MAP');
				$auth_trans = str_split($sauth_data['AUTH_TRANS_MAP']);
				//同步数据
				$syc_arr = array(
					'operateType' 	=>	'22',									//(操作类型)
					'oId'		 	=>	$post['SHOP_MAP_ID'],					//(商户ID)
					'cashPermit' 	=>	$auth_trans[3] ? 0 : 1,					//(权限-现金消费)【权限开通】现金消费。（积分宝规则，0：开放；1：关闭）
					'cashLimit' 	=>	$sauth_data['CASH_MAXAMT']/100,			//(单笔现金限额，单位元)
					'token' 		=>	strtoupper(md5(strtoupper(md5($post['SHOP_MAP_ID'].'22'))))		//(签名）oId+ operateType 双次MD5
				);
				/* @author sea start */
				//同步
				// $url = SHOP_SYN_URL.'PointRepositoryOpenLibrary/ShopInfroSynchService';
				// Add_LOG(CONTROLLER_NAME, json_encode($syc_arr));
				// $resjson = httpPostForm($url, $syc_arr);
				// Add_LOG(CONTROLLER_NAME, $resjson);
				// $result = json_decode($resjson);
				// if ($result->status != '0') {
					// $m->rollback();	//回滚2
					// $this->wrong('商户第三方权限数据同步修改失败');
				// }
				/* @author sea end */
				$m->commit();
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
		$shop_info = D($this->MShop)->findmoreNewShop('SHOP_MAP_ID = "'.$id.'"');
		if ($flag == '1') {
			$sauth_info = D($this->MSauth)->findSauth_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$sauth_info = D($this->MSauth)->findSauth("SHOP_MAP_ID='".$id."'");
		}
		if ($sauth_info['SHOP_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '3'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$shop_info['CHECK_FLAG'] = $flag;
		$t_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');
		$this->assign ('t_list', 	$t_list);											//交易开通
		$this->assign('shop_info',$shop_info);
		$this->assign('sauth_info',$sauth_info);
		$this->assign('auth_trans_checked', str_split($sauth_info['AUTH_TRANS_MAP']));	//交易开通数据
		$this->display('sauth_check');
	}
	
	
	
	
	/*
	* 商户证照变更管理 列表
	**/
	public function scert() {
		$post = I('post');
		if($post['submit'] == "scert"){
			$where = "1=1";			
			$soplv = filter_data('soplv');	//列表查询
			//状态
			if($post['SHOP_STATUS'] != '') {
				if($post['SHOP_STATUS'] == 0){
					$where .= " and sc.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}else{
					$where .= " and tmp.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}
			}
			//分支
			if($soplv['bid'] != '') {
				$where .= " and sh.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$where .= " and sh.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			//MCC
			if($post['MCC_TYPE'] != '') {
				$where .= " and sh.MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//商户类型码
			if ($post['MCC_CODE']) {
				$where .= " and sh.MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//商户号
			if ($post['SHOP_NO']) {
				$where .= " and sh.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and sh.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//分页
			if($post['SHOP_STATUS'] == '0'){
				$count = D($this->MScert)->countNotmpScert($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->MScert)->getNotmpScertlist($where, 'sc.SHOP_MAP_ID,sh.SHOP_NO,sh.SHOP_STATUS,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME', $p->firstRow.','.$p->listRows, 'sc.SHOP_MAP_ID desc');
			}else{
				$count = D($this->MScert)->countScert($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->MScert)->getScertlist($where, 'sc.SHOP_MAP_ID,sh.SHOP_NO,sc.SHOP_STATUS,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME,tmp.SHOP_MAP_ID as TMP_ID,tmp.SHOP_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,sc.SHOP_MAP_ID desc');
			}
			foreach ($list as $key => $value) {
				$check_no = '3'.setStrzero($value['SHOP_MAP_ID'],15);
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
		//审核状态数组
		$this->assign ('shop_status',C('CHECK_POINT.all'));			//全部状态
		$this->assign ('shop_status_check',C('CHECK_POINT.check'));		//部分状态
		$this->display();
	}
	/*
	* 商户证照变更管理 修改
	**/
	public function scert_edit() {
		$scert = I('scert');
		if($scert['submit'] == "scert_edit") {
			$home = session('HOME');
			//组装证照数据
			$scert_data = array(
				'SHOP_MAP_ID'	=>	$scert['SHOP_MAP_ID'],
				'SHOP_STATUS'	=>	6,
				'AGREEMENT_NO'	=>	$scert['AGREEMENT_NO'],
				'REG_EXP'		=>	$scert['REG_EXP_FLAG']==1 ? '99999999' : date('Ymd',strtotime($scert['REG_EXP'])),	//REG_EXP_FLAG临时增加
				'LP_EXP'		=>	$scert['LP_EXP_FLAG']==1 ? '99999999' : date('Ymd',strtotime($scert['LP_EXP'])),	//LP_EXP_FLAG临时增加
				'REG_ADDR'		=>	$scert['REG_ADDR'],
				'REG_ID'		=>	$scert['REG_ID'],
				'TAX_ID'		=>	$scert['TAX_ID'],
				'ORG_ID'		=>	$scert['ORG_ID'],
				'LP_NAME'		=>	$scert['LP_NAME'],
				'LP_ID'			=>	$scert['LP_ID'],
				'REGID_PHOTO'	=>	$scert['REGID_PHOTO'],
				'TAXID_PHOTO'	=>	$scert['TAXID_PHOTO'],
				'ORGID_PHOTO'	=>	$scert['ORGID_PHOTO'],
				'LP_D_PHOTO'	=>	$scert['LP_D_PHOTO'],
				'LP_R_PHOTO'	=>	$scert['LP_R_PHOTO'],
				'BANK_PHOTO'	=>	$scert['BANK_PHOTO'],
				'REGADDR_PHOTO1'=>	$scert['REGADDR_PHOTO1'],
				'REGADDR_PHOTO2'=>	$scert['REGADDR_PHOTO2'],
				'OFFICE_PHOTO1'	=>	$scert['OFFICE_PHOTO1'],
				'OFFICE_PHOTO2'	=>	$scert['OFFICE_PHOTO2'],
				'OFFICE_PHOTO3'	=>	$scert['OFFICE_PHOTO3'],
				'OTHER_PHOTOS'	=>	$scert['OTHER_PHOTOS'],
				'OFFICE_PHOTO4'	=>	$scert['OFFICE_PHOTO4'],
				'AGREEMENTID_PHOTO1'	=>	$scert['AGREEMENTID_PHOTO1'],
				'AGREEMENTID_PHOTO2'	=>	$scert['AGREEMENTID_PHOTO2'],
				'LICENSEID_PHOTO'	=>	$scert['LICENSEID_PHOTO'],
				'GRANTID_PHOTO1'	=>	$scert['GRANTID_PHOTO1'],
				'GRANTID_PHOTO2'	=>	$scert['GRANTID_PHOTO2'],
				'BANK_D_PHOTO'	=>	$scert['BANK_D_PHOTO'],
				'BANK_R_PHOTO'	=>	$scert['BANK_R_PHOTO'],
				'LP_GRANTID_PHOTO'	=>	$scert['LP_GRANTID_PHOTO'],
				'BANK_LP_D_PHOTO'	=>	$scert['BANK_LP_D_PHOTO'],
				'BANK_LP_R_PHOTO'	=>	$scert['BANK_LP_R_PHOTO'],
				//'INSTALL_PHOTO'	=>	$scert['INSTALL_PHOTO'],
				'RES'			=>	$scert['RES']
			);
			//判断当前数据是否正在发生变更
			$where = 'SHOP_MAP_ID = "'.$scert['SHOP_MAP_ID'].'"';
			//证件数据入tmp库
			if($scert['flag'] == 1){
				$res = D($this->MScert)->updateScert_tmp($where, $scert_data);
			}else{
				$res = D($this->MScert)->addScert_tmp($scert_data);
			}
			if($res['state']!=0){
				$this->wrong('商户证照信息变更失败！');
			}

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($scert['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '【商户证照信息变更】',
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
		$shop_info = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$scert_info = D($this->MScert)->findScert_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$scert_info = D($this->MScert)->findScert("SHOP_MAP_ID='".$id."'");
		}
		
		if(empty($shop_info) || empty($scert_info)){
			$this->wrong("参数数据出错！");
		}
		if ($scert_info['SHOP_STATUS'] == 4) {
			$this->wrong('当前状态不允许修改操作');
		}
		$scert_info['OTHER'] = explode(',', $scert_info['OTHER_PHOTOS']);
		$scert_info['flag'] = $flag;
		$this->assign ('shop_info', $shop_info);
		$this->assign ('scert_info', $scert_info);
		$this->display();
	}	
	/*
	* 商户证照变更管理 初审
	**/
	public function scert_check() {
		$post = I('post');
		if($post['submit'] == "scert_check") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT'] == ''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户证照变更】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			//更新tmp表审核状态
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			$scert_tmp = D($this->MScert)->updateScert_tmp($where,array('SHOP_STATUS' => $post['CHECK_POINT']));
			if ($scert_tmp['state']==1) {
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

		$shop_info = D($this->MShop)->findmoreNewShop('SHOP_MAP_ID = "'.$id.'"');
		if($flag == 1){
			$scert_info = D($this->MScert)->findScert_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$scert_info = D($this->MScert)->findScert("SHOP_MAP_ID='".$id."'");
		}
		if(empty($shop_info) || empty($scert_info)){
			$this->wrong("参数数据出错！");
		}
		if ($scert_info['SHOP_STATUS'] != 6) {
			$this->wrong('当前状态不允许此操作');
		}
		$scert_info['OTHER'] = explode(',', $scert_info['OTHER_PHOTOS']);
		$shop_info['CHECK_FLAG'] = $flag;
		$this->assign ('shop_info', $shop_info);
		$this->assign ('scert_info', $scert_info);
		$this->display();
	}
	
	/*
	* 商户证照变更管理 复审
	**/
	public function scert_recheck() {
		$post = I('post');
		if ($post['submit'] == "scert_recheck") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户证照变更】复核操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$sauth = D($this->MScert)->updateScert_tmp($where,array('SHOP_STATUS' => $post['CHECK_POINT']));
				if($scert['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$scert_change = D($this->MScert)->findScert_tmp($where);
				$scert_change['SHOP_STATUS'] = 0;
				//更新证照主表数据
				$scert = D($this->MScert)->updateScert($where,$scert_change);
				if($scert['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MScert)->delScert_tmp('SHOP_MAP_ID = '.$post['SHOP_MAP_ID']);
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
		$shop_info = D($this->MShop)->findmoreNewShop('SHOP_MAP_ID = "'.$id.'"');
		if ($flag == 1) {
			$scert_info = D($this->MScert)->findScert_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$scert_info = D($this->MScert)->findScert("SHOP_MAP_ID='".$id."'");
		}
		if ($scert_info['SHOP_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '3'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$scert_info['OTHER'] = explode(',', $scert_info['OTHER_PHOTOS']);
		$shop_info['CHECK_FLAG'] = $flag;
		$this->assign('shop_info',$shop_info);
		$this->assign('scert_info',$scert_info);
		$this->display('scert_check');
	}
	
	
	
	
	
	/*
	* 商户银行账户变更管理 列表
	**/
	public function sbact() {
		$post = I('post');
		if($post['submit'] == "sbact"){
			$where = "1=1";	
			$soplv = filter_data('soplv');	//列表查询
			//状态
			if($post['SHOP_STATUS'] != '') {
				if($post['SHOP_STATUS'] == 0){
					$where .= " and sb.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}else{
					$where .= " and tmp.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}
			}
			//分支
			if($soplv['bid'] != '') {
				$where .= " and sh.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$where .= " and sh.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			//MCC大类
			if($post['MCC_TYPE'] != '') {
				$where .= " and sh.MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//商户类型码
			if ($post['MCC_CODE'] != '') {
				$where .= " and sh.MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//商户号
			if ($post['SHOP_NO']) {
				$where .= " and sh.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and sh.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//分页
			if($post['SHOP_STATUS'] == '0'){
				$count = D($this->MSbact)->countNotmpSbact($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->MSbact)->getNotmpSbactlist($where, 'sb.SHOP_MAP_ID,sh.SHOP_NO,sh.SHOP_STATUS,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME', $p->firstRow.','.$p->listRows, 'sb.SHOP_MAP_ID desc');
			}else{
				$count = D($this->MSbact)->countSbact($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->MSbact)->getSbactlist($where, 'sb.SHOP_MAP_ID,sh.SHOP_NO,sb.SHOP_STATUS,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME,tmp.SHOP_MAP_ID as TMP_ID,tmp.SHOP_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,sb.SHOP_MAP_ID desc');
			}
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set (	'_currentUrl_', 	__SELF__);
		$this->assign ('mcc_type',C('MCC_TYPE'));					//MCC类型
		//审核状态数组
		$this->assign ('shop_status',C('CHECK_POINT.all'));			//通道全部状态
		$this->assign ('shop_status_check',C('CHECK_POINT.check'));		//部分状态
		
		$this->display();
	}
	/*
	* 商户银行账户变更管理 修改
	**/
	public function sbact_edit() {
		$sbact = I('sbact');
		if($sbact['submit'] == "sbact_edit") {
			$home = session('HOME');
			//获取银行账户数据
			$sbact['BANK_NAME1']    = $_REQUEST['org1_BANK_NAME'];
			$sbact['BANKACCT_BID1'] = $_REQUEST['org1_BANKACCT_BID'];
			$sbact['BANK_NAME2']    = $_REQUEST['org2_BANK_NAME'];
			$sbact['BANKACCT_BID2'] = $_REQUEST['org2_BANKACCT_BID'];
			//组装银行帐户数据
			$sbact_data = array(
				'SHOP_MAP_ID'		=>	$sbact['SHOP_MAP_ID'],
				'SHOP_STATUS'		=>	6,
				'SHOP_BANK_FLAG'	=>	$sbact['SHOP_BANK_FLAG'],
				'BANKACCT_NAME1'	=>	$sbact['BANKACCT_NAME1'],
				'BANKACCT_NO1'		=>	$sbact['BANKACCT_NO1'],
				'BANKACCT_BID1'		=>	$sbact['BANKACCT_BID1'],
				'BANK_NAME1'		=>	$sbact['BANK_NAME1'],
				'BANK_MOBILE1'		=>	$sbact['BANK_MOBILE1'],							  
				'BANKACCT_NAME2'	=>	$sbact['BANKACCT_NAME2'],
				'BANKACCT_NO2'		=>	$sbact['BANKACCT_NO2'],
				'BANKACCT_BID2'		=>	$sbact['BANKACCT_BID2'],
				'BANK_NAME2'		=>	$sbact['BANK_NAME2'],
				'BANK_MOBILE2'		=>	$sbact['BANK_MOBILE2']							 
			);
			//判断当前数据是否正在发生变更
			$where = 'SHOP_MAP_ID = "'.$sbact['SHOP_MAP_ID'].'"';
			//银行帐户数据入tmp库
			if($sbact['flag'] == 1){
				$res = D($this->MSbact)->updateSbact_tmp($where, $sbact_data);
			}else{
				$res = D($this->MSbact)->addSbact_tmp($sbact_data);
			}
			if($res['state']!=0){
				$this->wrong('商户银行帐户信息变更失败！');
			}

			/*if(empty($pauth['PARTNER_MAP_ID']) || empty($check_data['CHECK_FLAG']) || empty($check_data['CHECK_POINT']) || empty($check_data['CHECK_DESC'])){
				$this->wrong("参数数据出错！");
			}*/

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($sbact['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '【商户银行卡信息变更】',
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
		$shop_info = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$sbact_info = D($this->MSbact)->findSbact_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$sbact_info = D($this->MSbact)->findSbact("SHOP_MAP_ID='".$id."'");
		}
		if(empty($shop_info) || empty($sbact_info)){
			$this->wrong("参数数据出错！");
		}
		if ($sbact_info['SHOP_STATUS'] == 4) {
			$this->wrong('当前状态不允许此操作');
		}
		$sbact_info['flag'] = $flag;
		$this->assign ('shop_info', $shop_info);
		$this->assign ('sbact_info', $sbact_info);
		$this->assign ('bank_flag_redio', 	C('PARTNER_BANK_FLAG'));	//结算标志
		$this->display();
	}	
	/*
	* 商户银行账户变更管理 初审
	**/
	public function sbact_check() {
		$post = I('post');
		if($post['submit'] == "sbact_check") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT'] == ''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户银行账户变更】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			//更新表审核状态
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			$update_sbact = D($this->MSbact)->updateSbact_tmp($where,array('SHOP_STATUS' => $post['CHECK_POINT']));
			if ($update_sbact['state'] != 0) {
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

		$shop_info = D($this->MShop)->findmoreNewShop('SHOP_MAP_ID = "'.$id.'"');
		if($flag == 1){
			$sbact_info = D($this->MSbact)->findSbact_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$sbact_info = D($this->MSbact)->findSbact("SHOP_MAP_ID='".$id."'");
		}
		if(empty($shop_info) || empty($sbact_info)){
			$this->wrong("参数数据出错！");
		}
		if ($sbact_info['SHOP_STATUS'] != 6) {
			$this->wrong('当前状态不允许此操作');
		}
		$shop_info['CHECK_FLAG'] = $flag;
		$this->assign ('shop_info', $shop_info);
		$this->assign ('sbact_info', $sbact_info);
		$this->assign ('bank_flag_redio', C('PARTNER_BANK_FLAG'));	//结算标志
		$this->display();
	}
	
	/*
	* 商户银行账户变更管理 复审
	**/
	public function sbact_recheck() {
		$post = I('post');
		if ($post['submit'] == "sbact_recheck") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT'] == ''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户银行帐户变更】复核操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$sbact = D($this->MSbact)->updateSbact_tmp($where,array('SHOP_STATUS' => $post['CHECK_POINT']));
				if($sbact['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$sbact_change = D($this->MSbact)->findSbact_tmp($where);
				$sbact_change['SHOP_STATUS'] = 0;
				//更新证照主表数据
				$sbact = D($this->MSbact)->updateSbact($where,$sbact_change);
				if($sbact['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MSbact)->delSbact_tmp('SHOP_MAP_ID = '.$post['SHOP_MAP_ID']);
				if ($delres['state'] != 0){
					$this->wrong("操作失败!");
				}
				//2017.04.11 CRYSTAL
				$shopPiecesModel  =   M("shop_intopieces");
				// $shopModel           =   M("shop");
				// $shopInfo  =   $shopModel->where(array("SHOP_MAP_ID"=>$post['SHOP_MAP_ID']))->find();
				$shopPieces  =   $shopPiecesModel->where(array('STATUS'=>0,"SHOP_MAP_ID"=>$post['SHOP_MAP_ID']))->select();
				if($shopPieces){
					$m = M();
					$m->startTrans();
					$bankAcctNo = $sbact_change['SHOP_BANK_FLAG']==0?$sbact_change['BANKACCT_NO1']:$sbact_change['BANKACCT_NO2'];
					$bankName = $sbact_change['SHOP_BANK_FLAG']==0?$sbact_change['BANK_NAME1']:$sbact_change['BANK_NAME2'];
					$bankAcctbid = $sbact_change['SHOP_BANK_FLAG']==0?$sbact_change['BANKACCT_BID1']:$sbact_change['BANKACCT_BID2'];
					$bankPhone = $sbact_change['SHOP_BANK_FLAG']==0?$sbact_change['BANK_MOBILE1']:$sbact_change['BANK_MOBILE2'];
					//访问后台接口修改信息
					$url = PHP_API_URL."index.php/Home/Ipay/updateSfShopAccount";
					$data           =   array(
						"acc_no"        =>  $bankAcctNo,//结算账号
						"acc_bank_name" =>  $bankName,//结算银行名称
						"acc_bank_no"   =>  $bankAcctbid,//结算银行联行号
						"service_tel"   =>  $bankPhone,//银行预留手机号码
						"nonce_str"     =>  md5(time()),//签名随机数字
					);
					foreach($shopPieces as $k=>$v){
						$data["mcht_no"]    =   $v["SHOP_ID1"];
						$data["signTime"]   =   time();
						$data["funcName"]   =   "updateSfShopAccount";//访问的后台接口方法名称
						$data["sign"]   =    rySignStr($data);
						$jsonStr = json_encode($data);
						$result  =   getCurlDataByjson($url, $jsonStr);
						//两个中有一个失败则都失败
						if($result->status == "200"){
							
						}else{
							$this->wrong('上福商户账户变更失败');
							$m->rollback();
						}
					}
					$m->commit(); 
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
		$shop_info = D($this->MShop)->findmoreNewShop('SHOP_MAP_ID = "'.$id.'"');
		if ($flag == '1') {
			$sbact_info = D($this->MSbact)->findSbact_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$sbact_info = D($this->MSbact)->findSbact("SHOP_MAP_ID='".$id."'");
		}
		if ($sbact_info['SHOP_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '3'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$shop_info['CHECK_FLAG'] = $flag;
		$this->assign('shop_info',$shop_info);
		$this->assign('sbact_info',$sbact_info);
		$this->assign ('bank_flag_redio', C('PARTNER_BANK_FLAG'));	//结算标志
		$this->display('sbact_check');
	}
	
	
	
	/*
	* 商户扣率变更管理 列表
	**/
	public function smdr() {
		$post = I('post');
		if($post['submit'] == "smdr"){
			$where = "sh.SHOP_STATUS = '0'";
			$soplv = filter_data('soplv');	//列表查询
			//状态
			if($post['SHOP_STATUS'] != '') {
				if($post['SHOP_STATUS'] == 0){
					$where .= " and sm.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}else{
					$where .= " and tmp.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}
			}
			//分支
			if($soplv['bid'] != '') {
				$where .= " and sh.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$where .= " and sh.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			//MCC
			if($post['MCC_TYPE'] != '') {
				$where .= " and sh.MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//商户类型码
			if ($post['MCC_CODE']) {
				$where .= " and sh.MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//商户号
			if ($post['SHOP_NO']) {
				$where .= " and sh.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and sh.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//分页
			if($post['SHOP_STATUS'] == '0'){
				$count = D($this->MSmdr)->countNotmpgroupSmdr($where,'COUNT(DISTINCT sm.SHOP_MAP_ID) as total');
				$p 	   = new \Think\Page($count['total'], C('PAGE_COUNT'));
				$list  = D($this->MSmdr)->getNotmpSmdrlist($where, 'sm.SHOP_MAP_ID,sh.SHOP_NO,sh.SHOP_STATUS,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME', $p->firstRow.','.$p->listRows, 'sm.SHOP_MAP_ID desc', 'sm.SHOP_MAP_ID');
			}else{
				$count = D($this->MSmdr)->countgroupSmdr($where,'COUNT(DISTINCT sm.SHOP_MAP_ID) as total');
				$p 	   = new \Think\Page($count['total'], C('PAGE_COUNT'));
				$list  = D($this->MSmdr)->getSmdrlist($where, 'sm.SHOP_MAP_ID,sh.SHOP_NO,sm.SHOP_STATUS,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME,tmp.SHOP_MAP_ID as TMP_ID,tmp.SHOP_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,sm.SHOP_MAP_ID desc', 'sm.SHOP_MAP_ID');
			}
			//分页参数
			$this->assign ( 'totalCount', 	$count['total'] );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set (	'_currentUrl_', 	__SELF__);	
		$this->assign ('mcc_type',C('MCC_TYPE'));					//MCC类型
		//审核状态数组
		$this->assign ('shop_status',C('CHECK_POINT.all'));			//通道全部状态
		$this->assign ('shop_status_check',C('CHECK_POINT.check'));		//部分状态
		$this->display();
	}
	/*
	* 商户扣率变更管理 修改
	**/
	public function smdr_edit() {
		$smdr = I('smdr');
		if($smdr['submit'] == "smdr_edit") {
			$home = session('HOME');
			//组装扣率数据
			//现金的积分宝扣率, 等于银行卡商户扣率+积分宝扣率
			//$smdr['mdr'][0]['JFB_PER_FEE'] = $smdr['mdr'][1]['JFB_PER_FEE'] + $smdr['mdr'][1]['PER_FEE'];
			//积分宝的积分宝扣率, 等于银行卡商户扣率+积分宝扣率
			//$smdr['mdr'][2]['JFB_PER_FEE'] = $smdr['mdr'][1]['JFB_PER_FEE'] + $smdr['mdr'][1]['PER_FEE'];
			foreach ($smdr['mdr'] as $value) {
				//组装扣率数据
				$smdr_data = array(
					'SHOP_MAP_ID'		=>	$smdr['SHOP_MAP_ID'],
					'PAY_TYPE'			=>	$value['PAY_TYPE'] ? $value['PAY_TYPE'] : 0,
					'SHOP_STATUS'		=>	6,
					'SETTLE_T'			=>	$smdr['SETTLE_T'],
					'SETTLE_T_UNIT'		=>	1,
					'JFB_PER_FEE'		=>	$value['JFB_PER_FEE'] ? setMoney($value['JFB_PER_FEE'],'2') : '0',	//积分宝比例扣率线(万分比)
					'JFB_FIX_FEE'		=>	$value['JFB_FIX_FEE'] ? setMoney($value['JFB_FIX_FEE'], '2') : '0',	//积分宝封顶扣率线(单位分)
					'PER_FEE'			=>	$value['PER_FEE'] ? setMoney($value['PER_FEE'],'2') : '0',			//商户比例扣线(万分比)
					'FIX_FEE'			=>	$value['FIX_FEE'] ? setMoney($value['FIX_FEE'],'2') : '0',			//商户封顶扣率线(单位分)
					'DYN_PER_FEE'		=>	'0'
				);
				/*//扣率数据入库
				$smdr_res = D($this->MSmdr)->addSmdr($smdr_data);
				if($smdr_res['state']!=0){
					$m->rollback();		//不成功，则回滚
					$this->wrong('商户扣率数据添加失败！');
				}*/
				//判断当前数据是否正在发生变更
				$where = 'SHOP_MAP_ID = "'.$smdr['SHOP_MAP_ID'].'"';
				//银行帐户数据入tmp库
				if($smdr['flag'] == 1){
					$res = D($this->MSmdr)->updateSmdr_tmp($where." and PAY_TYPE = ".$value['PAY_TYPE'], $smdr_data);
				}else{
					$res = D($this->MSmdr)->addSmdr_tmp($smdr_data);
				}
				if($res['state']!=0){
					$this->wrong('商户扣率信息变更失败！');
				}
			}
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($smdr['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '【商户扣率信息变更】',
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
		$shop_info = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$smdr_info = D($this->MSmdr)->getSmdrtmplist("tmp.SHOP_MAP_ID='".$id."'", 'tmp.SETTLE_T,tmp.PAY_TYPE, tmp.JFB_PER_FEE, tmp.JFB_FIX_FEE, tmp.PER_FEE, tmp.FIX_FEE','',"locate(tmp.PAY_TYPE,'5,0,4')");
		}else{
			//获取商户结算信息
			$smdr_info  = D($this->MSmdr)->getSmdrfind("sm.SHOP_MAP_ID='".$id."' and sm.SHOP_STATUS = 0", 'sm.SETTLE_T,sm.PAY_TYPE, sm.JFB_PER_FEE, sm.JFB_FIX_FEE, sm.PER_FEE, sm.FIX_FEE','',"locate(sm.PAY_TYPE,'5,0,4')");
		}
		if(empty($shop_info) || empty($smdr_info)){
			$this->wrong("参数数据出错！");
		}
		if ($smdr_info['SHOP_STATUS']==4) {
			$this->wrong('当前状态不允许此操作');
		}
		$smdr_info['flag'] = $flag;
		$this->assign ('shop_info', $shop_info);
		$this->assign ('smdr_info', $smdr_info);
		$this->display();
	}	
	/*
	* 商户扣率变更管理 审核
	**/
	public function smdr_check() {
		$post = I('post');
		if ($post['submit'] == "smdr_check") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT'] == ''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户扣率变更】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			//修改状态
			$smdr_res = D($this->MSmdr)->updateSmdr_tmp($where,array('SHOP_STATUS' => $post['CHECK_POINT']));
			if ($smdr_res['state']!=0) {
				$this->wrong("审核操作失败！");
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
		$shop_info = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$smdr_info = D($this->MSmdr)->getSmdrtmplist("tmp.SHOP_MAP_ID='".$id."'", 'tmp.SETTLE_T,tmp.PAY_TYPE, tmp.JFB_PER_FEE, tmp.JFB_FIX_FEE, tmp.PER_FEE, tmp.FIX_FEE, tmp.SHOP_STATUS','',"locate(tmp.PAY_TYPE,'5,0,4')");
		}else{
			//获取商户结算信息
			$smdr_info  = D($this->MSmdr)->getSmdrfind("sm.SHOP_MAP_ID='".$id."' and sm.SHOP_STATUS = 0", 'sm.SETTLE_T,sm.PAY_TYPE, sm.JFB_PER_FEE, sm.JFB_FIX_FEE, sm.PER_FEE, sm.FIX_FEE, sm.SHOP_STATUS','',"locate(sm.PAY_TYPE,'5,0,4')");
		}
		
		if(empty($shop_info) || empty($smdr_info)){
			$this->wrong("参数数据出错！");
		}
		if ($smdr_info[0]['SHOP_STATUS'] != 6) {
			$this->wrong('当前状态不允许此操作');
		}
		//发卡行列表
		$shop_info['SHOP_FLAG'] = $flag;
		$this->assign ('shop_info', $shop_info);
		$this->assign ('smdr_info', $smdr_info);
		$this->display('smdr_check');
	}
	/*
	* 商户扣率变更管理 复审
	**/
	public function smdr_recheck() {
		$post = I('post');
		if ($post['submit'] == "smdr_recheck") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户扣率变更】复核操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			$sfFeeUpdate = false;
			$msFeeUpdate = false;
			$sfFee = 0;
			$m = M();
			$m->startTrans();	//启用事务
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$smdr = D($this->MSmdr)->updateSmdr_tmp($where,array('SHOP_STATUS' => $post['CHECK_POINT']));
				if($smdr['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$smdr_change = D($this->MSmdr)->getSmdrtmplist('tmp.SHOP_MAP_ID = '.$post['SHOP_MAP_ID']);
				foreach ($smdr_change as $key => $value) {
					$where1 = 'SHOP_MAP_ID = '.$value['SHOP_MAP_ID'].' and PAY_TYPE = '.$value['PAY_TYPE'];
					//更新证照主表数据
					$value['SHOP_STATUS'] = 0;
					unset($value['SMDR_ID']);
					$smdr = D($this->MSmdr)->updateSmdr($where1,$value);
					if($smdr['state'] != 0){
						$m->rollback();
						$this->wrong("操作失败!");
					}
					if($value['PAY_TYPE']=='5' && $smdr['state']==0){
						$sfFeeUpdate = true;
						$msFeeUpdate = true;
						$sfFee = $value['JFB_PER_FEE'];
						$SHOP_MAP_ID = $value['SHOP_MAP_ID'];
						$SETTLE_T = $value['SETTLE_T'];
					}
					//更新成功后删除TMP表中的该数据
					$delres = D($this->MSmdr)->delSmdr_tmp('SHOP_MAP_ID = '.$value['SHOP_MAP_ID'].' and PAY_TYPE = '.$value['PAY_TYPE']);
					if ($delres['state'] != 0){
						$m->rollback();
						$this->wrong("操作失败!");
					}
				}

				$smdr_data = M('smdr')
					->field('SHOP_MAP_ID, PAY_TYPE, JFB_PER_FEE, JFB_FIX_FEE, PER_FEE, FIX_FEE')
					->where("SHOP_MAP_ID = '".$post['SHOP_MAP_ID']."' and PAY_TYPE in(5,0)")
					->order("locate(PAY_TYPE,'5,0')")
					->select();

				$jfb_smdr = $smdr_data[0];
				$yl_smdr  = $smdr_data[1];
				//如果银行卡扣率存在，那么按银行卡的扣率，否则走现金的积分宝扣率
				if ($yl_smdr['PER_FEE']>0 || $yl_smdr['JFB_PER_FEE']>0) {
					$per_fee 	  = $yl_smdr['PER_FEE']/10000;		//商户扣率【银行卡】
					$fix_fee  	  = $yl_smdr['FIX_FEE']/100;		//商户封顶【银行卡】
					$jfb_per_fee  = $yl_smdr['JFB_PER_FEE']/10000;	//积分宝扣率【银行卡】
					$jfb_fix_per  = $yl_smdr['JFB_FIX_FEE']/100;	//积分宝封顶【银行卡】
				}else{
					$per_fee 	  = $jfb_smdr['PER_FEE']/10000;		//商户扣率【积分宝】
					$fix_fee  	  = $jfb_smdr['FIX_FEE']/100;		//商户封顶【积分宝】
					$jfb_per_fee  = $jfb_smdr['JFB_PER_FEE']/10000;	//积分宝扣率【积分宝】
					$jfb_fix_per  = $jfb_smdr['JFB_FIX_FEE']/100;	//积分宝封顶【积分宝】
				}
/* @author sea start */
				//同步商户数据到小二后台
				$sync_res = shop_sync_data($post['SHOP_MAP_ID'],2);
				if (!$sync_res) {
					$m->rollback();		//不成功，则回滚
					$this->wrong('商户修改数据同步失败');
				}
/* @author sea end */				
				//同步数据
				$syc_arr = array(
					'operateType' 	=>	'23',									//(操作类型)
					'oId'		 	=>	$post['SHOP_MAP_ID'],					//(商户ID)
					'pointRatio'  	=>	$jfb_per_fee,							//(积分比率)
					'pointHighest'  =>	$jfb_fix_per,							//(积分封顶金额，单位元)
					'umsRation' 	=>	$per_fee,								//(银联手续费率)
					'umsHighest' 	=>	$fix_fee,								//(银联封顶手续费，单位元)
					'umsLowest' 	=>	0,										//(银联保底手续费，单位元)
					'token' 		=>	strtoupper(md5(strtoupper(md5($post['SHOP_MAP_ID'].'23'))))	//(签名）oId+ operateType 双次MD5
				);
/* @author sea start */
				//同步
				$url = SHOP_SYN_URL.'PointRepositoryOpenLibrary/ShopInfroSynchService';
				Add_LOG(CONTROLLER_NAME, json_encode($syc_arr));
/* @author sea end */
				//同步数据至上福 2017.04.11 CRYSTAL
				if($sfFeeUpdate){
					$shopPiecesModel = M('shop_intopieces');
					$smdrModel = M('smdr');
					$shopPieces = $shopPiecesModel->where(array('SHOP_MAP_ID'=>$SHOP_MAP_ID,'STATUS'=>0))->find();
					if($shopPieces){
						$smdrInfo = $smdrModel->where(array('SHOP_MAP_ID'=>$SHOP_MAP_ID,'PAY_TYPE'=>5))->find();
						if($smdrInfo['JFB_PER_FEE']!=$sfFee){
							$url = PHP_API_URL."index.php/Home/Ipay/updateSfShopPerFee";
							$data = array(
								"shopNo"        =>  $SHOP_MAP_ID,
								"settle_rate"   =>  $sfFee/10000,//转为小数
								"settle_cycle"  =>  $SETTLE_T,//T0到账
								"nonce_str"     =>  md5(time()),//随机字符串
								"signTime"      =>  time(),
								"funcName"      =>  'updateSfShopPerFee',
							);
							// $this->wrong('商户扣率信息变更失败！'.$data['settle_rate']);
							$data["sign"]   =   rySignStr($data);
							$jsonStr = json_encode($data);
							$result  =   getCurlDataByjson($url, $jsonStr);
							if($result->status=="200"){
								$m->commit();
							}else{
								$m->rollback();
								$this->wrong('上福信息变更失败:'.$result->message);
							}
						}else{
							$m->commit();
						}
					}
					$m->commit();
				}else{
					$m->commit();
				}							  
/*
				$resjson = httpPostForm($url, $syc_arr);
				Add_LOG(CONTROLLER_NAME, $resjson);
				$result = json_decode($resjson);
				if ($result->status != '0') {
					$m->rollback();	//回滚
					$this->wrong('商户第三方POS数据同步修改失败');
				}
*/

			}
			//添加审核记录
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$m->rollback();
				$this->wrong('添加记录失败');
			}
			$m->commit();
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
		$shop_info = D($this->MShop)->findmoreNewShop('SHOP_MAP_ID = "'.$id.'"');
		/*if ($post['CHECK_FLAG']) {
			$smdr_info = D($this->MSmdr)->findSmdr_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$smdr_info = D($this->MSmdr)->findSmdr("SHOP_MAP_ID='".$id."'");
		}*/

		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$smdr_info = D($this->MSmdr)->getSmdrtmplist("tmp.SHOP_MAP_ID='".$id."'", 'tmp.SETTLE_T,tmp.PAY_TYPE, tmp.JFB_PER_FEE, tmp.JFB_FIX_FEE, tmp.PER_FEE, tmp.FIX_FEE, tmp.SHOP_STATUS','',"locate(tmp.PAY_TYPE,'5,0,4')");
		}else{
			//获取商户结算信息
			$smdr_info  = D($this->MSmdr)->getSmdrfind("sm.SHOP_MAP_ID='".$id."' and sm.SHOP_STATUS = 0", 'sm.SETTLE_T,sm.PAY_TYPE, sm.JFB_PER_FEE, sm.JFB_FIX_FEE, sm.PER_FEE, sm.FIX_FEE, sm.SHOP_STATUS','',"locate(sm.PAY_TYPE,'5,0,4')");
		}
		if ($smdr_info[0]['SHOP_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '3'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$shop_info['CHECK_FLAG'] = $flag;
		$this->assign('shop_info',$shop_info);
		$this->assign('smdr_info',$smdr_info);
		$this->display('smdr_check');
	}
		
	
	
	/*
	* 商户风险调整管理 列表
	**/
	public function srisk() {
		$post = I('post');
		if($post['submit'] == "srisk"){
			$where = "sh.SHOP_STATUS = 0";
			$soplv = get_level_val('soplv');
			//状态
			if($post['SHOP_STATUS'] != '') {
				if($post['SHOP_STATUS'] == 0){
					$where .= " and sr.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}else{
					$where .= " and tmp.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}
			}
			//分支
			if($soplv['bid'] != '') {
				$where .= " and sh.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$where .= " and sh.PARTNER_MAP_ID = '".$soplv['pid']."'";
				$post['pid'] = $soplv['pid'];
			}
			//MCC
			if($post['MCC_TYPE'] != '') {
				$where .= " and sh.MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//商户类型码
			if ($post['MCC_CODE']) {
				$where .= " and sh.MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//商户号
			if ($post['SHOP_NO']) {
				$where .= " and sh.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//所在地区
			if ($post['CITY_NO']) {
				$where .= " and sh.CITY_NO = '".$post['CITY_NO']."'";
			}
			//分页
			$count = D($this->MSrisk)->countSrisk($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MSrisk)->getSrisklist($where, 'sr.SHOP_MAP_ID,sh.SHOP_NO,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME', $p->firstRow.','.$p->listRows, 'sr.SHOP_MAP_ID desc');
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set (	'_currentUrl_', 	__SELF__);	
		//所属分支
		$this->assign ('mcc_type',C('MCC_TYPE'));					//MCC类型
		//审核状态数组
		$this->assign ('shop_status',C('CHECK_POINT.all'));			//通道全部状态
		$this->assign ('shop_status_check',C('CHECK_POINT.check'));		//部分状态
		$this->display();
	}
	/*
	* 商户风险调整管理 编辑
	**/
	public function srisk_edit() {
		$srisk = I('srisk');
		if($srisk['submit'] == "srisk_edit") {
			$home = session('HOME');
			//组装风险级别数据
			$srisk_data = array(
				'SHOP_GRADE'	=>	$srisk['SHOP_GRADE'],
				'SHOP_RISKBOUND'=>	($srisk['SHOP_RISKBOUND']!='') ? $srisk['SHOP_RISKBOUND'] : 100,
				'POSMODE_PER'	=>	$srisk['POSMODE_PER'] ? $srisk['POSMODE_PER'] : '',
				'REFUND_PER'	=>	$srisk['REFUND_PER'] ? $srisk['REFUND_PER'] : '',
				'CBREQ_PER'		=>	$srisk['CBREQ_PER'] ? $srisk['CBREQ_PER'] : '',
				'CBBACK_PER'	=>	$srisk['CBBACK_PER'] ? $srisk['CBBACK_PER'] : '',
				'BUSITIME_ABS'	=>	$srisk['BUSITIME_ABS'] ? $srisk['BUSITIME_ABS'] : '',
				'BALANCE_PER'	=>	$srisk['BALANCE_PER'] ? $srisk['BALANCE_PER'] : '',
				'RES'			=>	$srisk['RES'] ? $srisk['RES'] : '',
			);
			//风险级别变更
			$where = 'SHOP_MAP_ID = '.$srisk['SHOP_MAP_ID'];
			$srisk_res = D($this->MSrisk)->updateSrisk($where, $srisk_data);
			if($srisk_res['state']!=0){
				$this->wrong('商户风险评级修改失败！');
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
		$shop_info = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");
		//获取商户风险级别帐户信息
		$srisk_info = D($this->MSrisk)->findSrisk("SHOP_MAP_ID='".$id."'");
		if(empty($shop_info) || empty($srisk_info)){
			$this->wrong("参数数据出错！");
		}

		$this->assign ('shop_info', $shop_info);
		$this->assign ('srisk_info', $srisk_info);
		$this->assign ('shop_grade', C('SHOP_GRADE'));		//风险级别
		$this->display();
	}
	
	
	/*
	* 商户代扣账户变更管理 列表
	**/
	public function sdkb() {
		$post = I('post');
		if($post['submit'] == "sdkb"){
			$where = "sh.SHOP_STATUS = 0";
			$soplv = filter_data('soplv');	//列表查询
			//状态
			if($post['SHOP_STATUS'] != '') {
				if($post['SHOP_STATUS'] == 0){
					$where .= " and sd.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}else{
					$where .= " and tmp.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}
			}
			//分支
			if($soplv['bid'] != '') {
				$where .= " and sh.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$where .= " and sh.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			//MCC
			if($post['MCC_TYPE'] != '') {
				$where .= " and sh.MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//商户类型码
			if ($post['MCC_CODE']) {
				$where .= " and sh.MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//商户号
			if ($post['SHOP_NO']) {
				$where .= " and sh.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and sh.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//分页
			if($post['SHOP_STATUS'] == '0'){
				$count = D($this->MSdkb)->countNotmpSdkb($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->MSdkb)->getNotmpSdkblist($where, 'sd.SHOP_MAP_ID,sh.SHOP_NO,sh.SHOP_STATUS,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME', $p->firstRow.','.$p->listRows, 'sd.SHOP_MAP_ID desc');
			}else{
				$count = D($this->MSdkb)->countSdkb($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->MSdkb)->getSdkblist($where, 'sd.SHOP_MAP_ID,sh.SHOP_NO,sd.SHOP_STATUS,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME,tmp.SHOP_MAP_ID as TMP_ID,tmp.SHOP_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,sd.SHOP_MAP_ID desc');
			}
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set ('_currentUrl_', 	__SELF__);
		$this->assign ('mcc_type',C('MCC_TYPE'));					//MCC类型
		//审核状态数组
		$this->assign ('shop_status',C('CHECK_POINT.all'));			//通道全部状态
		$this->assign ('shop_status_check',C('CHECK_POINT.check'));		//部分状态
		
		$this->display();
	}
	/*
	* 商户代扣账户变更管理 修改
	**/
	public function sdkb_edit() {
		$sdkb = I('sdkb');
		if($sdkb['submit'] == "sdkb_edit") {
			$home = session('HOME');
			//获取代扣账户银行账号
			$sdkb['BANK_NAME1']    = $_REQUEST['org3_BANK_NAME'];
			$sdkb['BANKACCT_BID1'] = $_REQUEST['org3_BANKACCT_BID'];
			$sdkb['BANK_NAME2']    = $_REQUEST['org4_BANK_NAME'];
			$sdkb['BANKACCT_BID2'] = $_REQUEST['org4_BANKACCT_BID'];
			//组装代扣帐户数据
			$sdkb_data = array(
				'SHOP_MAP_ID'	=>	$sdkb['SHOP_MAP_ID'],
				'DKCO_MAP_ID'	=>	$sdkb['DKCO_MAP_ID'],
				'SHOP_STATUS'	=>	6,
				'SHOP_BANK_FLAG'=>	$sdkb['SHOP_BANK_FLAG'],
				'BANKACCT_NAME1'=>	$sdkb['BANKACCT_NAME1'],
				'BANKACCT_NO1'	=>	$sdkb['BANKACCT_NO1'],
				'BANKACCT_BID1'	=>	$sdkb['BANKACCT_BID1'],
				'BANK_NAME1'	=>	$sdkb['BANK_NAME1'],
				'BANKACCT_NAME2'=>	$sdkb['BANKACCT_NAME2'],
				'BANKACCT_NO2'	=>	$sdkb['BANKACCT_NO2'],
				'BANKACCT_BID2'	=>	$sdkb['BANKACCT_BID2'],
				'BANK_NAME2'	=>	$sdkb['BANK_NAME2'],
				'SHOP_ACCT_FLAG'=>	$sdkb['SHOP_ACCT_FLAG'],
				'DK_IDNO_TYPE'	=>	$sdkb['DK_IDNO_TYPE'],
				'DK_IDNO'		=>	$sdkb['DK_IDNO']
			);

			//判断当前数据是否正在发生变更
			$where = 'SHOP_MAP_ID = "'.$sdkb['SHOP_MAP_ID'].'"';
			//银行帐户数据入tmp库
			if($sdkb['flag'] == 1){
				$res = D($this->MSdkb)->updateSdkb_tmp($where , $sdkb_data);
			}else{
				$res = D($this->MSdkb)->addSdkb_tmp($sdkb_data);
			}
			if($res['state']!=0){
				$this->wrong('商户代扣信息变更失败！');
			}

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($sdkb['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '【商户代扣信息变更】',
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
		$shop_info = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$sdkb_info = D($this->MSdkb)->findSdkb_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$sdkb_info = D($this->MSdkb)->findSdkb("SHOP_MAP_ID='".$id."'");
		}
		if(empty($shop_info) || empty($sdkb_info)){
			$this->wrong("参数数据出错！");
		}
		if ($sdkb_info['SHOP_STATUS']==4) {
			$this->wrong('当前状态不允许此操作');
		}
		$sdkb_info['flag'] = $flag;
		$this->assign ('shop_info', $shop_info);
		$this->assign ('sdkb_info', $sdkb_info);
		$this->assign ('bank_flag_redio', 	C('PARTNER_BANK_FLAG'));	//结算标志
		$this->display();
	}	
	/*
	* 商户代扣账户变更管理 初审
	**/
	public function sdkb_check() {
		$post = I('post');
		if($post['submit'] == "sdkb_check") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT'] == ''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户代扣账户变更】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			//更新表审核状态
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			$update_sdkb = D($this->MSdkb)->updateSdkb_tmp($where,array('SHOP_STATUS' => $post['CHECK_POINT']));
			if ($update_sdkb['state'] != 0) {
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

		$shop_info = D($this->MShop)->findmoreNewShop('SHOP_MAP_ID = "'.$id.'"');
		if($flag == 1){
			$sdkb_info = D($this->MSdkb)->findSdkb_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$sdkb_info = D($this->MSdkb)->findSdkb("SHOP_MAP_ID='".$id."'");
		}
		if(empty($shop_info) || empty($sdkb_info)){
			$this->wrong("参数数据出错！");
		}
		if($sdkb_info['SHOP_STATUS'] != 6){
			$this->wrong("当前状态不允许此操作");
		}
		$shop_info['CHECK_FLAG'] = $flag;
		$this->assign ('shop_info', $shop_info);
		$this->assign ('sdkb_info', $sdkb_info);
		$this->assign ('bank_flag_redio', C('PARTNER_BANK_FLAG'));	//结算标志
		$this->display();
	}
	
	/*
	* 商户代扣账户变更管理 复审
	**/
	public function sdkb_recheck() {
		$post = I('post');
		if ($post['submit'] == "sdkb_recheck") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户代扣账户变更】复核操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$sdkb = D($this->MSdkb)->updateSdkb_tmp($where,array('SHOP_STATUS' => $post['CHECK_POINT']));
				if($sdkb['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$m = M();
				$m->startTrans();	//启用事务
				$sdkb_change = D($this->MSdkb)->findSdkb_tmp($where);
				$sdkb_change['SHOP_STATUS'] = 0;
				//更新证照主表数据
				$sdkb = D($this->MSdkb)->updatesdkb($where,$sdkb_change);
				if($sdkb['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MSdkb)->delSdkb_tmp('SHOP_MAP_ID = '.$post['SHOP_MAP_ID']);
				if ($delres['state'] != 0){
					$m->rollback();	//回滚
					$this->wrong("操作失败!");
				}
		
				$m->commit();
				//更改相应的商户代扣失败的流水中的代扣资料
				$dk_res = D($this->MSdkb)->findSdkb("SHOP_MAP_ID='".$post['SHOP_MAP_ID']."'");
				if ($dk_res['SHOP_BANK_FLAG'] == 0 ) {
					$dk_data = array(
						'DKCO_MAP_ID'	=>	$dk_res['DKCO_MAP_ID'],		//代扣公司id
						'SHOP_BANK_FLAG'=>	$dk_res['SHOP_BANK_FLAG'],	//对公对私（0：从对公户代扣 1从对私代扣）
						'SHOP_ACCT_FLAG'=>	$dk_res['SHOP_ACCT_FLAG'],	//卡折标志（0：卡  1：折）
						'BANKACCT_NAME'	=>	$dk_res['BANKACCT_NAME1'],	//户名
						'BANKACCT_NO'	=>	$dk_res['BANKACCT_NO1'],		//账号
						'BANK_NAME'		=>	$dk_res['BANK_NAME1'],		//开户行
						'BANK_BID'		=>	$dk_res['BANKACCT_BID1'],		//结算户行联行号
						'DK_IDNO_TYPE'	=>	$dk_res['DK_IDNO_TYPE'],
						'DK_IDNO'		=>	$dk_res['DK_IDNO']
					);
				}else{
					$dk_data = array(
						'DKCO_MAP_ID'	=>	$dk_res['DKCO_MAP_ID'],		//代扣公司id
						'SHOP_BANK_FLAG'=>	$dk_res['SHOP_BANK_FLAG'],	//对公对私（0：从对公户代扣 1从对私代扣）
						'SHOP_ACCT_FLAG'=>	$dk_res['SHOP_ACCT_FLAG'],	//卡折标志（0：卡  1：折）
						'BANKACCT_NAME'	=>	$dk_res['BANKACCT_NAME2'],	//户名
						'BANKACCT_NO'	=>	$dk_res['BANKACCT_NO2'],		//账号
						'BANK_NAME'		=>	$dk_res['BANK_NAME2'],		//开户行
						'BANK_BID'		=>	$dk_res['BANKACCT_BID2'],		//结算户行联行号
						'DK_IDNO_TYPE'	=>	$dk_res['DK_IDNO_TYPE'],
						'DK_IDNO'		=>	$dk_res['DK_IDNO']
					);
				}
				D($this->TDkls)->updateDkls("SHOP_NO = '".$post['SHOP_NO']."' and DK_FLAG != 0", $dk_data);
			}

			//添加审核记录
			D($this->MCheck)->addCheck($check_data);
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
		$shop_info = D($this->MShop)->findmoreNewShop('SHOP_MAP_ID = "'.$id.'"');
		if ($flag == '1') {
			$sdkb_info = D($this->MSdkb)->findSdkb_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$sdkb_info = D($this->MSdkb)->findSdkb("SHOP_MAP_ID='".$id."'");
		}
		if ($sdkb_info['SHOP_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '3'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$shop_info['CHECK_FLAG'] = $flag;
		$this->assign('shop_info',$shop_info);
		$this->assign('sdkb_info',$sdkb_info);
		$this->assign ('bank_flag_redio', 			C('PARTNER_BANK_FLAG'));	//结算标志
		$this->display('sdkb_check');
	}

	/*
	* 商户其他配置变更管理 列表
	**/
	public function ssett() {
		$post = I('post');
		if($post['submit'] == "ssett"){
			$where = "sh.SHOP_STATUS = 0";
			$soplv = filter_data('soplv');	//列表查询
			//状态
			if($post['SHOP_STATUS'] != '') {
				if($post['SHOP_STATUS'] == 0){
					$where .= " and sd.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}else{
					$where .= " and tmp.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}
			}
			//分支
			if($soplv['bid'] != '') {
				$where .= " and sh.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$where .= " and sh.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			//MCC
			if($post['MCC_TYPE'] != '') {
				$where .= " and sh.MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//商户类型码
			if ($post['MCC_CODE']) {
				$where .= " and sh.MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//商户号
			if ($post['SHOP_NO']) {
				$where .= " and sh.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and sh.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//分页
			if($post['SHOP_STATUS'] == '0'){
				$count = D($this->MScfg)->countNotmpScfg($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->MScfg)->getNotmpScfglist($where, 'sd.SHOP_MAP_ID,sh.SHOP_NO,sh.SHOP_STATUS,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME', $p->firstRow.','.$p->listRows, 'sd.SHOP_MAP_ID desc');
			}else{
				$count = D($this->MScfg)->countScfg($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->MScfg)->getScfglist($where, 'sd.SHOP_MAP_ID,sh.SHOP_NO,sd.SHOP_STATUS,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME,tmp.SHOP_MAP_ID as TMP_ID,tmp.SHOP_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,sd.SHOP_MAP_ID desc');
			}
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set ('_currentUrl_', 	__SELF__);
		$this->assign ('mcc_type',C('MCC_TYPE'));					//MCC类型
		//审核状态数组
		$this->assign ('shop_status',C('CHECK_POINT.all'));			//通道全部状态
		$this->assign ('shop_status_check',C('CHECK_POINT.check'));		//部分状态
		
		$this->display();
	}
	/*
	* 商户其他配置变更管理 修改
	**/
	public function ssett_edit() {
		$scfg = I('scfg');
		$home = session('HOME');
		if($scfg['submit'] == "ssett_edit") {
			//组装其他配置数据
			$scfg_plv = get_level_val('scfg_plv');
			$scfg_data = array(
				'SHOP_MAP_ID'	=>	$scfg['SHOP_MAP_ID'],
				'SHOP_STATUS'	=>	6,														//分期特殊分配比例
				'DIV_FLAG'		=>	$scfg['DIV_FLAG'] ? $scfg['DIV_FLAG'] : 0,				//分期特殊分配比例
				'CARD_OPENFEE'	=>	$scfg['CARD_OPENFEE'] ? $scfg['CARD_OPENFEE'] : 3000,	//卡收费总额
				'DIV_PER'		=>	$scfg['DIV_PER'] ? setMoney($scfg['DIV_PER']) : 0,		//预免卡分期比例
				'BOUND_RATE'	=>	$scfg['BOUND_RATE'] ? $scfg['BOUND_RATE'] : 50,			//积分兑换比例
				'RAKE_FLAG'		=>	$scfg['RAKE_FLAG'] ? $scfg['RAKE_FLAG'] : 0,			//特殊分配比例
				'CON_PER_RAKE'	=>	$scfg['CON_PER_RAKE'] ? setMoney($scfg['CON_PER_RAKE'],2,1) : 0,	//消费者比例
				'PLAT_PER_RAKE'	=>	$scfg['PLAT_PER_RAKE'] ? setMoney($scfg['PLAT_PER_RAKE']) : (10000-setMoney($scfg['CON_PER_RAKE'])),	//平台比例
				'DONATE_FLAG'	=>	$scfg['DONATE_FLAG'] ? $scfg['DONATE_FLAG'] : 0,		//转赠标志
				'DONATE_TYPE'	=>	$scfg['DONATE_TYPE'],									//转赠产品
				'DONATE_RATE'	=>	$scfg['DONATE_RATE'] ? $scfg['DONATE_RATE'] : 0,		//转赠率
				'PARTNER_MAP_ID'=>	$scfg_plv['pid'] ? $scfg_plv['pid'] : $home['PARTNER_MAP_ID'],	//转赠对象
				'DONATE_RES'	=>	$scfg['DONATE_RES']										//备注
			);
			//其他配置数据变更
			$where = 'SHOP_MAP_ID = "'.$scfg['SHOP_MAP_ID'].'"';
			//其他配置数据入tmp表
			if($scfg['flag'] == 1){
				$res = D($this->MScfg)->updateScfg_tmp($where , $scfg_data);
			}else{
				$res = D($this->MScfg)->addScfg_tmp($scfg_data);
			}
			if($res['state']!=0){
				$this->wrong('商户其他配置变更失败！');
			}

			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($scfg['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '6',
				'CHECK_DESC'	=> '【商户其他配置信息变更】',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('信息变更记录失败');
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
		$shop_info = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");
		if ($flag == '1') {
			//判断当前数据是否符合变更条件
			$scfg_info = D($this->MScfg)->findScfg_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$scfg_info = D($this->MScfg)->findScfg("SHOP_MAP_ID='".$id."'");
		}
		if(empty($shop_info) || empty($scfg_info)){
			$this->wrong("参数数据出错！");
		}
		if ($scfg_info['SHOP_STATUS']==4) {
			$this->wrong('当前状态不允许此操作');
		}
		$scfg_info['flag'] = $flag;
		$this->assign ('shop_info', $shop_info);
		$this->assign ('scfg_info', $scfg_info);
		$this->assign ('home', 		$home);			//当前登录用户数据
		$this->display();
	}	
	/*
	* 商户其他配置变更管理 初审
	**/
	public function ssett_check() {
		$post = I('post');
		if($post['submit'] == "ssett_check") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT'] == ''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户其他配置变更】初审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			//更新表审核状态
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			$update_scfg = D($this->MScfg)->updateScfg_tmp($where,array('SHOP_STATUS' => $post['CHECK_POINT']));
			if ($update_scfg['state'] != 0) {
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

		$shop_info = D($this->MShop)->findmoreNewShop('SHOP_MAP_ID = "'.$id.'"');
		if($flag == 1){
			$scfg_info = D($this->MScfg)->findScfg_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$scfg_info = D($this->MScfg)->findScfg("SHOP_MAP_ID='".$id."'");
		}
		if(empty($shop_info) || empty($scfg_info)){
			$this->wrong("参数数据出错！");
		}
		if($scfg_info['SHOP_STATUS'] != 6){
			$this->wrong('当前状态不允许初审操作');
		}
		$shop_info['CHECK_FLAG'] = $flag;
		$this->assign ('shop_info', $shop_info);
		$this->assign ('scfg_info', $scfg_info);
		$this->display();
	}
	
	/*
	* 商户其他配置变更管理 复审
	**/
	public function ssett_recheck() {
		$post = I('post');
		if ($post['submit'] == "ssett_recheck") {
			if(empty($post['SHOP_MAP_ID']) || $post['CHECK_POINT'] == ''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($post['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> $post['CHECK_POINT'],
				'CHECK_DESC'	=> $post['CHECK_DESC'] ? $post['CHECK_DESC'] : '【商户其他配置变更】复审操作',
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			if ($post['CHECK_POINT'] != '0') {
				//更新副表数据
				$sauth = D($this->MScfg)->updateScfg_tmp($where,array('SHOP_STATUS' => $post['CHECK_POINT']));
				if($scfg['state'] != 0){
					$this->wrong("操作失败!");
				}
			}else{
				$scfg_change = D($this->MScfg)->findScfg_tmp($where);
				$scfg_change['SHOP_STATUS'] = 0;
				//更新证照主表数据
				$scfg = D($this->MScfg)->updateScfg($where,$scfg_change);
				if($scfg['state'] != 0){
					$this->wrong("操作失败!");
				}
				//更新成功后删除TMP表中的该数据
				$delres = D($this->MScfg)->delScfg_tmp('SHOP_MAP_ID = '.$post['SHOP_MAP_ID']);
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
		$shop_info = D($this->MShop)->findmoreNewShop('SHOP_MAP_ID = "'.$id.'"');
		if ($flag == '1') {
			$scfg_info = D($this->MScfg)->findScfg_tmp("SHOP_MAP_ID='".$id."'");
		}else{
			$scfg_info = D($this->MScfg)->findScfg("SHOP_MAP_ID='".$id."'");
		}
		if ($scfg_info['SHOP_STATUS'] != 4) {
			$this->wrong('当前状态不允许复核申请');
		}
		//获取初审记录
		$check_no = '3'.setStrzero($id,15);
		$check_info = D($this->MCheck)->findCheck('CHECK_NO = "'.$check_no.'" and CHECK_POINT = 4');
		if($check_info){
			$this->assign('check_info',$check_info);
		}
		$shop_info['CHECK_FLAG'] = $flag;
		$this->assign('shop_info',$shop_info);
		$this->assign('scfg_info',$scfg_info);
		$this->display('ssett_check');
	}


	
	/*
	* POS管理 列表
	**/
	public function pos() {
		$post = I('post');
		if($post['submit'] == "pos"){
			$soplv = filter_data('soplv');	//列表查询
			$where = "POS_STATUS = 0";
			//分支
			if($soplv['bid'] != '') {
				$where .= " and p.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$where .= " and p.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			//商户号
			if($post['SHOP_NO']) {
				$where .= " and p.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and s.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//终端号
			if($post['POS_NO']) {
				$where .= " and p.POS_NO = '".$post['POS_NO']."'";
			}
			
			//分页
			$count = D($this->MPos)->countPos($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MPos)->getPoslist($where, 'p.*,s.SHOP_NAME,a.PARTNER_NAME', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
		$this->assign('pos_status',			C('POS_STATUS'));	//POS状态
		\Cookie::set ('_currentUrl_', 		__SELF__);			
		$this->display();
	}
	/*
	* POS 查看
	**/
	public function pos_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$pos_info = D($this->MPos)->findPosmore("p.POS_ID='".$id."'",'p.*,m.MODEL_NAME');
		$shop_info = D($this->MShop)->findShop("SHOP_MAP_ID='".$pos_info['SHOP_MAP_ID']."'");
		if(empty($pos_info) || empty($shop_info)){
			$this->wrong("参数数据出错！");
		}
		//获取COMM名称
		$comm_data = D('MComm')->findComm('COM_INDEX = "'.$pos_info['COM_INDEX'].'"');
		$this->assign('comm_data', $comm_data);
		$this->assign('pos_info', $pos_info);
		$this->assign('shop_info', $shop_info);
		$this->assign('pos_status',	C('POS_STATUS'));	//终端状态
		$this->display();
	}
	/*
	* POS 修改
	**/
	public function pos_edit() {
		$pos = I('pos');
		if($pos['submit'] == "pos_edit") {
			if ($pos['POS_ID'] == '') {
				$this->wrong('缺少参数');
			}
			$home = session('HOME');
			//组装代扣帐户数据
			$pos_data = array(
				'POS_BATCH'			=>	$pos['POS_BATCH'],
				'POS_TRACE'			=>	$pos['POS_TRACE'],
				'POS_TIMEOUT'		=>	$pos['POS_TIMEOUT'],
				'COM_INDEX'			=>	$pos['COM_INDEX'],
				'POS_MAN_MODE'		=>	$pos['POS_MAN_MODE'],
				'POS_PBOCKEYFLAG'	=>	$pos['POS_PBOCKEYFLAG'],
				'POS_PBOCPARAFLAG'	=>	$pos['POS_PBOCPARAFLAG'],
				'POS_PARAFLAG'		=>	$pos['POS_PARAFLAG'],
				'POS_PROGFLAG'		=>	$pos['POS_PROGFLAG'],
				'POS_HMDFLAG'		=>	$pos['POS_HMDFLAG'],
				'POS_NEWPROGVER'	=>	$pos['POS_NEWPROGVER']
			);
			$pos_res = D($this->MPos)->updatePos("POS_ID='".$pos['POS_ID']."'",$pos_data);
			if($pos_res['state'] != 0){
				$this->wrong('信息变更失败');
			}
			$this->right('信息变更成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = I('id');
		$pos_info = D($this->MPos)->findPosmore("p.POS_ID='".$id."'",'p.*,m.MODEL_NAME');
		$shop_info = D($this->MShop)->findShop("SHOP_MAP_ID='".$pos_info['SHOP_MAP_ID']."'");
		if(empty($pos_info) || empty($shop_info)){
			$this->wrong("参数数据出错！");
		}

		$comm_data = D('MComm')->getCommlist('','COM_INDEX,COM_NAME');
		//获取
		$this->assign('pos_info', $pos_info);
		$this->assign('shop_info', $shop_info);
		$this->assign('comm_data', $comm_data);
		$this->display('pos_add');
	}
	/*
	* POS 关闭
	**/
	public function pos_close() {
		$ids = $_REQUEST['POS_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('POS_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('POS_ID'=> array('eq', $ids));
		}
		$res = D($this->MPos)->updatePos($where, array('POS_STATUS'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* POS 开启
	**/
	public function pos_open() {
		$ids = $_REQUEST['POS_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('POS_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('POS_ID'=> array('eq', $ids));
		}
		$res = D($this->MPos)->updatePos($where, array('POS_STATUS'=> 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	
	
	
	/*
	* 商户映射
	**/
	public function smapped() {
		 $post = I('post');
		if($post['submit'] == "smapped"){
			$where = "1=1";
			//通道名称
			if($post['HOST_MAP_ID']) {
				$where .= " and h.HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
			}
			//平台商户号
			if($post['SHOP_NO']) {
				$where .= " and sh.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//通道商户号
			if($post['HSHOP_NO']) {
				$where .= " and hs.HSHOP_NO = '".$post['HSHOP_NO']."'";
			}
			//分页
			$count = D($this->MShopppp)->countShopppp($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MShopppp)->getShopppplist($where, 'sh.SHOPPPP_ID,sh.SHOP_NO,sh.HOST_MAP_ID,sh.HSHOP_NO,s.SHOP_NAME,hs.HSHOP_NAME,h.HOST_MAP_ID,h.HOST_NAME', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		//通道列表
		$hostsel = D($this->MHost)->getHostlist("HOST_STATUS != 2", 'HOST_MAP_ID,HOST_NAME');
		$this->assign ('hostsel', 	$hostsel);		//通道列表
		\Cookie::set ('_currentUrl_', 	__SELF__);	
		$this->display();
	}
	/*
	* 商户映射 添加
	**/
	public function smapped_add() {
		$post = I('post');
		if($post['submit'] == "smapped_add"){
			if (empty($post['SHOP_NO']) || empty($post['HOST_MAP_ID']) || empty($post['HSHOP_NO'])) {
				$this->wrong('请填写必填项！');
			}
			if (empty($post['SHOP_NAME']) || empty($post['HSHOP_NAME'])) {
				$this->wrong('平台商户或通道商户不存在, 请填写正确的平台商户号和通道商户号！');
			}
			//判断是否重复
			$where = 'sh.SHOP_NO = '.$post['SHOP_NO'].' and sh.HOST_MAP_ID = '.$post['HOST_MAP_ID'];
			$res  = D($this->MShopppp)->findShopppp($where);
			if ($res) {
				$this->wrong('当前平台商户号和通道号已存在, 不能重复添加！');
			}
			$shopppp_data = array(
				'BRANCH_MAP_ID'	=> 0,
				'PPP_FLAG'		=> 0,
				'SHOP_NO'		=> $post['SHOP_NO'],
				'HOST_MAP_ID'	=> $post['HOST_MAP_ID'],
				'HSHOP_NO'		=> $post['HSHOP_NO']
			);
			$res = D($this->MShopppp)->addShopppp($shopppp_data);
			if ($res['state']!=0) {
				$this->wrong('映射失败！');
			}
			$this->right('映射成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//通道列表
		$hostsel = D($this->MHost)->getHostlist("HOST_STATUS = 0 or HOST_STATUS = 3", 'HOST_MAP_ID,HOST_NAME');
		$this->assign ('hostsel', 	$hostsel);		//通道列表
		$this->display();
	}	
	/*
	* 商户映射 修改
	**/
	public function smapped_edit() {
		$post = I('post');
		if($post['submit'] == "smapped_edit"){
			if (empty($post['SHOP_NO']) || empty($post['HOST_MAP_ID']) || empty($post['HSHOP_NO'])) {
				$this->wrong('请填写必填项！');
			}
			if (empty($post['SHOP_NAME']) || empty($post['HSHOP_NAME'])) {
				$this->wrong('平台商户或通道商户不存在, 请填写正确的平台商户号和通道商户号！');
			}
			//判断是否重复
			$where = 'sh.SHOP_NO = '.$post['SHOP_NO'].' and sh.HOST_MAP_ID = '.$post['HOST_MAP_ID'];
			$res  = D($this->MShopppp)->findShopppp($where);
			if ($res && $res['SHOPPPP_ID'] != $post['SHOPPPP_ID']) {
				$this->wrong('当前平台商户号和通道号已存在, 不能重复添加！');
			}
			$shopppp_data = array(
				'SHOP_NO'		=> $post['SHOP_NO'],
				'HOST_MAP_ID'	=> $post['HOST_MAP_ID'],
				'HSHOP_NO'		=> $post['HSHOP_NO']
			);
			$where = 'SHOPPPP_ID = '.$post['SHOPPPP_ID'];
			$res = D($this->MShopppp)->updateShopppp($where,$shopppp_data);
			if ($res['state']!=0) {
				$this->wrong('映射失败！');
			}
			$this->right('映射成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//获取通道POS映射信息
		$where = "sh.SHOPPPP_ID = ".$id;
		$info  = D($this->MShopppp)->findShopppp($where,'sh.*, s.SHOP_NAME, hs.HSHOP_NAME');
		//通道列表
		$hostsel = D($this->MHost)->getHostlist("HOST_STATUS = 0 or HOST_STATUS = 3", 'HOST_MAP_ID,HOST_NAME');
		$this->assign ('hostsel', 	$hostsel);		//通道列表
		$this->assign ('info', 		$info);			//POS映射信息
		$this->display('smapped_add');
	}	
	/*
	* 商户映射 删除
	**/
	public function smapped_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$where = 'SHOPPPP_ID = '.$id;
		$res = D($this->MShopppp)->delShopppp($where);
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}

	/*
	* 通道POS映射
	**/
	public function sposmapped() {
		 $post = I('post');
		if($post['submit'] == "sposmapped"){
			$where = "1=1";
			//通道名称
			if($post['HOST_MAP_ID']) {
				$where .= " and h.HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
			}
			//平台商户号
			if($post['SHOP_NO']) {
				$where .= " and po.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//平台商户终端号
			if($post['POS_NO']) {
				$where .= " and po.POS_NO = '".$post['POS_NO']."'";
			}
			//通道商户号
			if($post['HSHOP_NO']) {
				$where .= " and hs.HSHOP_NO = '".$post['HSHOP_NO']."'";
			}
			//通道商户终端号
			if($post['HPOS_NO']) {
				$where .= " and po.HPOS_NO = '".$post['HPOS_NO']."'";
			}
			//分页
			$count = D($this->MPosppp)->countPosppp($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MPosppp)->getPosppplist($where, 'po.POSPPP_ID,po.POS_NO,po.HPOS_NO,s.SHOP_NO,s.SHOP_NAME,hs.HSHOP_NO,hs.HSHOP_NAME,h.HOST_MAP_ID,h.HOST_NAME', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		//通道列表
		$hostsel = D($this->MHost)->getHostlist("HOST_STATUS = 0 or HOST_STATUS = 3", 'HOST_MAP_ID,HOST_NAME');
		$this->assign ('hostsel', 	$hostsel);		//通道列表
		\Cookie::set ('_currentUrl_', 	__SELF__);	
		$this->display();
	}

	/*
	* 通道POS映射 添加
	**/
	public function sposmapped_add() {
		$post = I('post');
		if($post['submit'] == "sposmapped_add"){
			if (empty($post['SHOP_NO']) || empty($post['POS_NO']) || empty($post['HOST_MAP_ID']) || empty($post['HSHOP_NO']) || empty($post['HPOS_NO'])) {
				$this->wrong('请填写必填项！');
			}
			if (empty($post['SHOP_NAME']) || empty($post['HSHOP_NAME'])) {
				$this->wrong('平台商户或通道商户不存在, 请填写正确的平台商户号和通道商户号！');
			}
			//判断是否重复
			$where = 'po.SHOP_NO = '.$post['SHOP_NO'].' and po.POS_NO = '.$post['POS_NO'].' and po.HOST_MAP_ID = '.$post['HOST_MAP_ID'];
			$res  = D($this->MPosppp)->findPosppp($where);
			if ($res) {
				$this->wrong('当前平台商户号,终端号和通道已存在, 不能重复添加！');
			}
			$posppp_data = array(
				'BRANCH_MAP_ID'	=> 0,
				'PPP_FLAG'		=> 0,
				'SHOP_NO'		=> $post['SHOP_NO'],
				'POS_NO'		=> $post['POS_NO'],
				'HOST_MAP_ID'	=> $post['HOST_MAP_ID'],
				'HSHOP_NO'		=> $post['HSHOP_NO'],
				'HPOS_NO'		=> $post['HPOS_NO']
			);
			$res = D($this->MPosppp)->addPosppp($posppp_data);
			if ($res['state']!=0) {
				$this->wrong('映射失败！');
			}
			$this->right('映射成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//通道列表
		$hostsel = D($this->MHost)->getHostlist("HOST_STATUS = 0 or HOST_STATUS = 3", 'HOST_MAP_ID,HOST_NAME');
		$this->assign ('hostsel', 	$hostsel);		//通道列表
		$this->display();
	}

	/*
	* 通道POS映射 编辑
	**/
	public function sposmapped_edit() {
		$post = I('post');
		if($post['submit'] == "sposmapped_edit"){
			if (empty($post['SHOP_NO']) || empty($post['POS_NO']) || empty($post['HOST_MAP_ID']) || empty($post['HSHOP_NO']) || empty($post['HPOS_NO'])) {
				$this->wrong('请填写必填项！');
			}
			if (empty($post['SHOP_NAME']) || empty($post['HSHOP_NAME'])) {
				$this->wrong('平台商户或通道商户不存在, 请填写正确的平台商户号和通道商户号！');
			}
			//判断是否重复
			$where = 'po.SHOP_NO = '.$post['SHOP_NO'].' and po.POS_NO = '.$post['POS_NO'].' and po.HOST_MAP_ID = '.$post['HOST_MAP_ID'];
			$res  = D($this->MPosppp)->findPosppp($where);
			if ($res && $res['POSPPP_ID'] != $post['POSPPP_ID']) {
				$this->wrong('当前平台商户号,终端号和通道已存在, 不能重复添加！');
			}
			$Posppp_data = array(
				'BRANCH_MAP_ID'	=> 0,
				'PPP_FLAG'		=> 0,
				'SHOP_NO'		=> $post['SHOP_NO'],
				'POS_NO'		=> $post['POS_NO'],
				'HOST_MAP_ID'	=> $post['HOST_MAP_ID'],
				'HSHOP_NO'		=> $post['HSHOP_NO'],
				'HPOS_NO'		=> $post['HPOS_NO']
			);
			$where = 'POSPPP_ID = '.$post['POSPPP_ID'];
			$res = D($this->MPosppp)->updatePosppp($where,$Posppp_data);
			if ($res['state']!=0) {
				$this->wrong('映射失败！');
			}
			$this->right('映射成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//获取通道POS映射信息
		$info  = D($this->MPosppp)->findPosppp("po.POSPPP_ID='".$id."'",'po.*,s.SHOP_NAME,hs.HSHOP_NAME');
		//通道列表
		$hostsel = D($this->MHost)->getHostlist("HOST_STATUS = 0 or HOST_STATUS = 3", 'HOST_MAP_ID,HOST_NAME');
		$this->assign ('hostsel', 	$hostsel);		//通道列表
		$this->assign ('info', 		$info);			//POS映射信息
		$this->display('sposmapped_add');
	}
	
	/*
	* 通道POS映射 删除
	**/
	public function sposmapped_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$where = 'POSPPP_ID = '.$id;
		$res = D($this->MPosppp)->delPosppp($where);
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	//统一调整商户积分扣率
	public function update_smdr(){
		$mdr = array();
		$smdr_info  = D($this->MSmdr)->getSmdrfind("sm.SHOP_STATUS = 0", 'sm.SHOP_MAP_ID,sm.PAY_TYPE, sm.JFB_PER_FEE, sm.JFB_FIX_FEE, sm.PER_FEE','',"locate(sm.PAY_TYPE,'5,0,4')");
		foreach ($smdr_info as $key => $value) {
			$mdr[$value['SHOP_MAP_ID']][] = $value;
		}
		//扣率数据
		foreach ($mdr as $key => $value) {
			foreach ($value as $key1 => $val) {
				$where = 'SHOP_MAP_ID = '.$val['SHOP_MAP_ID']." and PAY_TYPE = ".$val['PAY_TYPE'];
				if ($val['PAY_TYPE'] == 5 || $val['PAY_TYPE'] == 4) {
					$smdr_data = array(
						'JFB_PER_FEE' => ($value[1]['JFB_PER_FEE'] + $value[1]['PER_FEE']),
						'PER_FEE'	  => 0,
					);
					//扣率数据修改
					D($this->MSmdr)->updateSmdr($where, $smdr_data);
				}
			}
		}
		echo '<H1>操作成功</H1>';
	}

	//审核记录
	public function shop_check_note(){
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$idarr = explode('_', $id);
		$id    = $idarr[0];
		//判断当前状态是否符合复审操作
		$shoptatus = D($this->MShop)->findShop('SHOP_MAP_ID = "'.$id.'"','SHOP_STATUS');
		/*if ($shoptatus['SHOP_STATUS'] != 6) {
			$this->wrong('当前状态不允许初审操作');
		}*/
		//获取审核记录
		$check_no = '3'.setStrzero($id,15);
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

	//商户积分设置
	public function spoints(){
		$post = I('post');
		if($post['submit'] == "spoints"){
			$where = "1=1";
			$soplv = filter_data('soplv');	//列表查询
			//状态
			if($post['SHOP_STATUS'] != '') {
				$where .= " and sd.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
			}
			//分支
			if($soplv['bid'] != '') {
				$where .= " and sh.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$where .= " and sh.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			//MCC
			if($post['MCC_TYPE'] != '') {
				$where .= " and sh.MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//商户类型码
			if ($post['MCC_CODE']) {
				$where .= " and sh.MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//商户号
			if ($post['SHOP_NO']) {
				$where .= " and sh.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and sh.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//分页
			$count = D($this->MScfg)->countScfg_2($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MScfg)->getScfglist_2($where, 'sd.SHOP_MAP_ID,sh.SHOP_NO,sd.SHOP_STATUS,sd.BOUND_RATE,sh.SHOP_NAME,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME', $p->firstRow.','.$p->listRows, 'sd.SHOP_MAP_ID desc');
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set ('_currentUrl_', 	__SELF__);
		$this->assign ('mcc_type',C('MCC_TYPE'));					//MCC类型
		//审核状态数组
		$this->assign ('shop_status',C('CHECK_POINT.all'));			//通道全部状态
		$this->assign ('shop_status_check',C('CHECK_POINT.check'));		//部分状态
		
		$this->display();
	}
	//审核记录
	public function spoints_edit(){
		$post = I('post');
		if($post['submit'] == "spoints_edit"){
			$where = 'SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
			$scfg_data = array('BOUND_RATE' => $post['BOUND_RATE']);
			$res = D($this->MScfg)->updateScfg($where , $scfg_data);
			if ($res['state']!=0) {
				$this->wrong('修改失败！');
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//判断当前状态是否符合复审操作
		$scfg_info = D($this->MScfg)->findScfg('SHOP_MAP_ID = "'.$id.'"','SHOP_MAP_ID,BOUND_RATE');
		/*if ($shoptatus['SHOP_STATUS'] != 6) {
			$this->wrong('当前状态不允许初审操作');
		}*/
		$this->assign ( 'scfg_info', 	$scfg_info );
		$this->display();
	}
	//同步商户数据
	public function sync_shop(){
		$shop_id = I('SHOP_MAP_ID');
		/* @author sea start */
		$sync_res = shop_sync_data($shop_id,1);
		if (!$sync_res) {
			echo '<h1>商户进件数据同步失败</h1>';
		}else{
			// echo '<h1>商户进件数据同步成功</h1>';
		}
		/* @author sea end */
	}
	//批量商户同步
	public function sync_shop_more(){
		$where = "SHOP_STATUS = 0 and CREATE_TIME > 20160728000000";
		$list  = D($this->MShop)->getNewShoplist($where, 'SHOP_MAP_ID', '','CREATE_TIME DESC');
		$n_ok = 0;
		$n_no = 0;
		echo '<h1>共计 '.count($list).' 个商户！</h1>';
		foreach ($list as $key => $value) {
			/* @author sea start */
			$sync_res = shop_sync_data($value['SHOP_MAP_ID'],1);
			if (!$sync_res) {
				echo '<h1>商户进件数据同步失败</h1>';
				$n_no++;
			}else{
				echo '<h1>商户进件数据同步成功</h1>';
				$n_ok++;
			}
				/* @author sea end */
		}
		echo '<h1>共计 '.count($list).' 个商户！成功：'.$n_ok.'个，失败：'.$n_no.'个</h1>';
	}

	//批量商户同步
	public function sync_shop_more2(){
		$where = "SHOP_MAP_ID > 100000 and left(SHOP_NO,9) = '000000000' and SHOP_STATUS = 0";
		$list  = D($this->MShop)->getNewShoplist($where, 'SHOP_MAP_ID', '','CREATE_TIME DESC');
		$mtrace= M('trace', DB_PREFIX_TRA, DB_DSN_TRA);
		$mshop = M('shop');
		$n_ok = 0;
		foreach ($list as $key => $value) {
			$trace_res = $mtrace->where('SHOP_NO = '.$value['SHOP_NO'])->find();
			if (!empty($trace_res['SHOP_NO'])) {
				continue;
			}
			$n_ok++;
			$mshop->where('SHOP_MAP_ID = "'.$value['SHOP_MAP_ID'].'"')->save(array('SHOP_STATUS' => 6 ));
		}
		echo '<h1>共计 '.count($list).' 个商户！成功：'.$n_ok.'个，失败：'.count($list) - $n_ok.'个</h1>';
	}

	//删除商户数据
	public function shop_del(){
		$pidstr = '9,18,47';
		$where1 = 'PARTNER_MAP_ID in ('.$pidstr.')';
		$field = 'PARTNER_MAP_ID,SHOP_NAME,SHOP_MAP_ID';
		$list = M('shop')->where($where1)->field($field)->select();
		//删除商户关联表
		foreach ($list as $key => $value) {
			M('shop')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
			M('sauth')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
			M('sauth_tmp')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
			M('scert')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
			M('scert_tmp')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
			M('smdr')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
			M('smdr_tmp')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
			M('sbact')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
			M('sbact_tmp')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
			M('sdkb')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
			M('sdkb_tmp')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
			M('scfg')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
			M('scfg_tmp')->where('SHOP_MAP_ID="'.$value['SHOP_MAP_ID'].'"')->delete();
		}
		//删除合作伙伴关联表
		$pids = get_plv_childs('8','1');
		$where = "PARTNER_MAP_ID in(".$pids.")";
		M('partner')->where($where)->delete();
		M('pcert')->where($where)->delete();
		M('pcert_tmp')->where($where)->delete();
		M('pauth')->where($where)->delete();
		M('pauth_tmp')->where($where)->delete();
		M('pbact')->where($where)->delete();
		M('pbact_tmp')->where($where)->delete();
		M('pcls')->where($where)->delete();
		M('pcls_tmp')->where($where)->delete();
		M('pcfg')->where($where)->delete();
		M('pcfg_tmp')->where($where)->delete();
		echo '成功';
	}

	//批量通过正常商户
	public function shop_pass_more(){
		$bid = I('bid');
		if (empty($bid)) {
			echo "<h1>缺少参数！</h1>";exit;
		}
		$where = 'BRANCH_MAP_ID="'.$bid.'" and SHOP_MAP_ID < 100000 and SHOP_STATUS = 6';
		$upshop = array('SHOP_STATUS' => 0 );
		$result = M('shop')->where($where)->save($upshop);
		if($result === false) {
			echo "<h1>修改失败！</h1>";exit;
		}
		echo "<h1>成功修改".$result."个商户！</h1>";
	}

	public function add_shop(){
		$home = session('HOME');	//当前登录用户数据
		$id = I('id');
		$shop_info  = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");
		$shop_no = $shop_info["SHOP_NO"];
    	$uname = $home["USER_NAME"];

    	$file = dirname(THINK_PATH);
		//命令格式："/add_shop 888430507630000 XXX";
		$cmd = sprintf("/add_shop %s %s",$shop_no,$uname);
		$dir = $file.$cmd;
		exec($dir,$output);
		$rs = $output;
		$this -> assign('cmd',$cmd);
		$this -> assign('rs',$rs);
		$this -> display();
	}

	// public function do_add_shop(){
	// 	$shop_no = I("shop_no");
 //    	$bank_name = I("bank_name");
 //    	$uname = I("uname");
 //    	if (empty($shop_no)||empty($bank_name)||empty($uname)) {
 //    		echo "缺少参数";exit;
 //    	}
	// 	$file = dirname(THINK_PATH);
	// 	//命令格式："/add_shop 888430507630000 中国工商银行 XXX";
	// 	$cmd = sprintf("/add_shop %s %s %s",$shop_no,$uname);
	// 	$dir = $file.$cmd;
	// 	exec($dir,$output);
	// 	//dump($output);
	// 	$rs = $output;
	// 	$this -> assign('cmd',$cmd);
	// 	$this -> assign('rs',$rs);
	// 	$this -> display();
	// }
	
	//正常老商户 单进件
	public function online_add(){
		$post = I('post');
		if ($post['submit'] == "online_add") {
			$shopModel = M('shop');
			$shopSZModel = M('shop_intopieces');
			$shopInfo = $shopModel->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			if(!$shopInfo){
				$this->wrong('商户不存在');
			}
			if($shopInfo['SHOP_STATUS'] !=0 ){
				$this->wrong('当前状态不可操作');
			}
			$shopSZCon['SHOP_MAP_ID'] = $shopInfo['SHOP_MAP_ID'];
			$shopSZCon['CHANNEL'] = 0;				 
			$shopSZCon['STATUS'] = 0;
			$shopSZInfo = $shopSZModel->where($shopSZCon)->find();
			if($shopSZInfo){
				$this->wrong('商户已进件');
			}
			//========================   2017.03.30 CRYSTAL  BEGIN  ==================================
			// $shopInfo = D($this->MShop)->findmoreShop2(array('s.SHOP_MAP_ID'=>$post['SHOP_MAP_ID'],'smdr.PAY_TYPE'=>5));
			
			//上福进件
			// $url = 'http://114.215.243.204:8081/index.php/Home/Shop/upShopinfo_sea';
			$url = PHP_API_URL.'index.php/Home/Reg/upShopinfo';
			$data = array(
				'shop_map_id'=>$post['SHOP_MAP_ID']/*,
				'user_login_mobile'=>$shopInfo['MOBILE'],
				'mcht_name'=>$shopInfo['SHOP_NAME'],
				'mcht_short_name'=>$shopInfo['SHOP_NAMEABCN'],
				'address'=>$shopInfo['ADDRESS'],
				'leg_phone'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANK_MOBILE1']:$shopInfo['BANK_MOBILE2'],
				'county_code'=>$shopInfo['CITY_NO'],
				'leg_name'=>$shopInfo['LP_NAME'],
				'leg_mail'=>$shopInfo['EMAIL'],
				'per_fee'=>$shopInfo['JFB_PER_FEE'],
				'acc_name'=>$shopInfo['LP_NAME'],
				'acc_bank_name'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANK_NAME1']:$shopInfo['BANK_NAME2'],
				'acc_no'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANKACCT_NO1']:$shopInfo['BANKACCT_NO2'],
				'service_tel'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANK_MOBILE1']:$shopInfo['BANK_MOBILE2'],
				'id_no'=>$shopInfo['LP_ID'],
				'settle_cycle'=>$shopInfo['SETTLE_T'],
				'acc_bank_no'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANKACCT_BID1']:$shopInfo['BANKACCT_BID2']*/
			);

			$data['signTime'] = time();
			$data['funcName'] = 'upShopinfo';//'upShopinfo_sea';
			$data["sign"] = rySignStr($data);
			$jsonStr = json_encode($data);
			// Add_LOG('SHOP',$url.' '.$jsonStr);
			$result = getCurlDataByjson($url, $jsonStr);
			Add_LOG('SHOP',$result);
			//dump($result);exit;
			//$this->testlog(__LINE__. 'LINE ajax_piece_review' .json_encode($result));
			if ($result->status != "200") {
				$datas['NOTE3'] = $result->message;
				D($this->MShop)->updateShop($where,$datas);
				$this->wrong("SHANGFU_WRONG:".$result->message);
			}else{
				$datas['HOST_MAP_ID'] = 1;
				D($this->MShop)->updateShop($where,$datas);
			}
			//========================   2017.03.30 CRYSTAL  END  ====================================
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$shopModel = M('shop');
		$shopInfo = $shopModel->where(array('SHOP_MAP_ID'=>$id))->find();
		if($shopInfo['SHOP_STATUS'] !=0 ){
			$this->wrong('当前状态不可操作');
		}
		$this->shop_show('online_add');
	}
	
	//鉴权 2017.05.27
	public function authority(){
		$post = I('post');
		if ($post['submit'] == "authority") {
			$shopModel = M('shop');
			$sbactModel = M('sbact');
			$scertModel = M('scert');
			$shopInfo = $shopModel->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			$sbactInfo = $sbactModel->where(array('SHOP_MAP_ID'=>$shopInfo['SHOP_MAP_ID']))->find();
			$scertInfo = $scertModel->where(array('SHOP_MAP_ID'=>$shopInfo['SHOP_MAP_ID']))->find();
			if(empty($shopInfo) && empty($sbactInfo) && empty($scertInfo)){
				$this->wrong('参数异常');
			}
			// if($shopInfo['SHOP_STATUS'] !=0 ){
				// $this->wrong('当前状态不可操作');
			// }
			if(($sbactInfo['SHOP_BANK_FLAG'] == 0 && $sbactInfo['IS_AUTH1'] == 0) || ($sbactInfo['SHOP_BANK_FLAG'] == 1 && $sbactInfo['IS_AUTH2'] == 0)){
				$this->wrong('已鉴权');
			}
			$LP_ID = $scertInfo['LP_ID'];//法人身份证号
			if($sbactInfo['SHOP_BANK_FLAG'] == 0){
				$BANKACCT_NAME = $sbactInfo['BANKACCT_NAME1'];//结算户名(对公)
				$BANKACCT_NO = $sbactInfo['BANKACCT_NO1'];//结算户账号(对公)
				$BANK_MOBILE = $sbactInfo['BANK_MOBILE1'];//银行预留手机号(对公)
			}else{
				$BANKACCT_NAME = $sbactInfo['BANKACCT_NAME2'];//结算户名(对私)
				$BANKACCT_NO = $sbactInfo['BANKACCT_NO2'];//结算户账号(对私)
				$BANK_MOBILE = $sbactInfo['BANK_MOBILE2'];//银行预留手机号(对私)
			}
			//亿美鉴权
			$url = PHP_API_URL.'index.php/Home/YmFactors/authority';
			$data = array(
				'BANKACCT_NAME'=>$BANKACCT_NAME,
				'LP_ID'=>$LP_ID,
				'BANKACCT_NO'=>$BANKACCT_NO,
				'BANK_MOBILE'=>$BANK_MOBILE
			);

			$data['signTime'] = time();
			$data['funcName'] = 'authority';
			$data["sign"] = rySignStr($data);
			$jsonStr = json_encode($data);
			
			$result = getCurlDataByjson($url, $jsonStr);
			Add_LOG('SHOP',$result);
			if ($result->status != "200") {
				$this->wrong("亿美Error:".$result->message);
			}
			if($sbactInfo['SHOP_BANK_FLAG'] == 0){
				$IS_AUTH1 = 0;//是否鉴权(对公)
				$sbactData = array(
					'IS_AUTH1' => $IS_AUTH1
				);
			}else{
				$IS_AUTH2 = 0;//是否鉴权(对私)
				$sbactData = array(
					'IS_AUTH2' => $IS_AUTH2
				);
			}
			$res = $sbactModel->where(array('SHOP_MAP_ID'=>$shopInfo['SHOP_MAP_ID']))->save($sbactData);
			$this->right('操作成功.信息'.$result->message, 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$shopModel = M('shop');
		$shopInfo = $shopModel->where(array('SHOP_MAP_ID'=>$id))->find();
		// if($shopInfo['SHOP_STATUS'] !=6 ){
			// $this->wrong('当前状态不可操作');
		// }
		$this->shop_show('authority');
	}
	
	//支付路由 2017.06.06
	/*
	* 通道管理 列表
	**/
	public function host() {
		$post = I('post');
		if($post['submit'] == "host"){
			$where = "HOST_MAP_ID != ''";
			//通道名称
			if($post['HOST_NAME']) {
				$where .= " and HOST_NAME like '%".$post['HOST_NAME']."%'";
			}
			//状态
			if($post['HOST_STATUS'] != '') {
				$where .= " and HOST_STATUS = '".$post['HOST_STATUS']."'";
			}
			//分页
			$hostModel = M('host');
			$count = $hostModel->where($where)->count();
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = $hostModel->field('*,DATE_FORMAT(END_TIME,"%Y-%m-%d") AS END_TIME')->where($where)->limit($p->firstRow.','.$p->listRows)->select();
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
			if(empty($post['HOST_NAME']) || empty($post['END_TIME']) || empty($post['CITY_NO']) || empty($post['ADDRESS']) || empty($post['ZIP']) || empty($post['MANAGER']) || empty($post['MOBILE'])){
				$this->wrong("缺少必填项数据！");
			}
			//Host 通道
			$hostdata = array(
				'HOST_NAME'			=>	$post['HOST_NAME'],
				'HOST_NAMEAB'		=>	$post['HOST_NAMEAB'],
				'HOST_STATUS'		=>	$post['HOST_STATUS'],
				'CONTROLLER_NAME'	=>	$post['CONTROLLER_NAME'],
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
				'CHANNEL'			=>	$post['CHANNEL'],
				'END_TIME'			=>	date('Ymd', strtotime($post['END_TIME'])).'235959',
			);
			$hostModel = M('host');
			$res_host = $hostModel->add($hostdata);
			
			//判断
			if(!$res_host){
				$this->wrong('添加失败！');
			}
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//支付渠道 0:上福 1:浙星 2:JAVA 3:民生 4:华宝 5:威富通 6:易联众 7:通莞聚合
		$channel_list = C('CHANNEL_LIST');
		$this->assign('channel_list', $channel_list);
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
		$hostModel = M('host');
		$info = $hostModel->field('*,DATE_FORMAT(END_TIME,"%Y-%m-%d") AS END_TIME')->where(array('HOST_MAP_ID'=>$id))->find();
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//成本扣率
		$hmdr_list = D($this->MHost)->getHostHmdrlist("a_host.HOST_MAP_ID='".$id."'", 'a_hmdr.*', 'a_hmdr.MCC_TYPE asc');
		//结算方式
		$field = 'ho.*,ha.HOST_PPP_FLAG,ha.AUTH_TRANS_MAP,hc.HOST_SETTLE_FLAG,hc.SETTLE_OFF_AMT,hc.SETTLE_FREE_AMT,hc.SETTLE_TOP_AMT,hc.SETTLE_T,hc.SETTLE_T_UNIT,hc.SETTLE_FLAG,hc.SETTLE_OFF_FEE,hc.SETTLE_FEE';
		$info_host = D($this->MHost)->findmoreHost("ho.HOST_MAP_ID='".$id."'", $field);
		// var_dump($info_host);
		$this->assign('info', 				$info);
		$this->assign('hmdr_list',			$hmdr_list);				//成本扣率
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('info_host', 			$info_host);
		$this->assign('mcc_type',			C('MCC_TYPE'));				//MCC分类
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
				'HOST_STATUS'		=>	$post['HOST_STATUS'],
				'CONTROLLER_NAME'   =>	$post['CONTROLLER_NAME'],
				'CITY_NO'			=>	$post['CITY_NO'],
				'ADDRESS'			=>	$post['ADDRESS'],
				'ZIP'				=>	$post['ZIP'],
				'TEL'				=>	$post['TEL'],
				'MANAGER'			=>	$post['MANAGER'],
				'MOBILE'			=>	$post['MOBILE'],
				'EMAIL'				=>	$post['EMAIL'],
				'CHANNEL'			=>	$post['CHANNEL'],
				'END_TIME'			=>	date('Ymd', strtotime($post['END_TIME'])).'235959',
			);
			$m = M();
			$hostModel = M('host');
			$hostConfigModel = M('host_config');
			$m->startTrans();
			$res_host = $hostModel->where(array('HOST_MAP_ID'=>$post['HOST_MAP_ID']))->save($hostdata);
			$res_host_config = 1;
			if($post['HOST_STATUS'] == 1){
				$res_host_config = $hostConfigModel->where(array('HOST_MAP_ID'=>$post['HOST_MAP_ID']))->save(array('STATUS'=>$post['HOST_STATUS']));
			}
			if(!$res_host && !$res_host_config){
				$m->rollback();
				$this->wrong("修改失败！");
			}
			$m->commit();
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$hostModel = M('host');
		$info  = $hostModel->field('*,DATE_FORMAT(END_TIME,"%Y-%m-%d") AS END_TIME')->where(array('HOST_MAP_ID'=>$id))->find();
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//支付渠道 0:上福 1:浙星 2:JAVA 3:民生 4:华宝 5:威富通 6:易联众 7:通莞聚合
		$channel_list = C('CHANNEL_LIST');
		//成本扣率
		$hmdr_list = D($this->MHost)->getHostHmdrlist("a_host.HOST_MAP_ID='".$id."'", 'a_hmdr.*', 'a_hmdr.MCC_TYPE asc');
		//结算方式
		$field = 'ho.*,ha.HOST_PPP_FLAG,ha.AUTH_TRANS_MAP,hc.HOST_SETTLE_FLAG,hc.SETTLE_OFF_AMT,hc.SETTLE_FREE_AMT,hc.SETTLE_TOP_AMT,hc.SETTLE_T,hc.SETTLE_T_UNIT,hc.SETTLE_FLAG,hc.SETTLE_OFF_FEE,hc.SETTLE_FEE';
		$info_host = D($this->MHost)->findmoreHost("ho.HOST_MAP_ID='".$id."'", $field);

		$this->assign('channel_list', 		$channel_list);
		$this->assign('info', 				$info);
		$this->assign('hmdr_list',			$hmdr_list);				//成本扣率
		$this->assign('info_host', 			$info_host);				//结算方式
		$this->assign('mcc_type',			C('MCC_TYPE'));				//MCC分类
		$this->display('host_add');
	}
	
	//路由配置管理
	public function host_config(){
		$post = I('post');
		if($post['submit'] == "host_config"){
			$where = "h.HOST_MAP_ID != ''";
			//通道名称
			if($post['HOST_NAME']) {
				$where .= " and ho.HOST_NAME like '%".$post['HOST_NAME']."%'";
			}
			//状态
			if($post['STATUS'] != '') {
				$where .= " and h.STATUS = '".$post['STATUS']."'";
			}
			//分页
			$hostModel = M('host_config');
			$count = $hostModel->where($where)->count();
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = $hostModel->alias('h')
						->join('a_host ho on ho.HOST_MAP_ID=h.HOST_MAP_ID','LEFT')
						->where($where)->limit($p->firstRow.','.$p->listRows)->select();
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
		$this->display('host_config');
	}
	
	/*
	* 路由配置管理 添加
	**/
	public function host_config_add() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "host_config_add") {
			//验证
			if(empty($post['TYPE']) || empty($post['TYPE'])!=0 || empty($post['STYPE']) || empty($post['HOST_MAP_ID'])){
				$this->wrong("缺少必填项数据！");
			}
			//Host 通道
			$typeArr = array('交易量','交易额','时间段','扣率','分类','结算方式');
			$TYPE_NAME = $typeArr[$post['TYPE']-1];
			
			$STYPE_NAME = '';
			switch($post['TYPE']){
				case 1:
				case 2:
				case 3:
					$stypeArr = array('每日','每周','每月','每笔');
					$STYPE_NAME = $stypeArr[$post['STYPE']-1];
				break;
				case 4:
					$STYPE_NAME = ($post['STYPE']/100).'%';
				break;
				case 5:
					$stypeArr = array('医疗','餐饮','生活服务','购物','旅游出行','休闲娱乐','汽车服务','其他');
					$STYPE_NAME = $stypeArr[$post['STYPE']-1];
				break;
				case 6:
					$STYPE_NAME = 'T+';
				break;
			}
			$hostdata = array(
				'TYPE'				=>	$post['TYPE'],
				'TYPE_NAME'			=>	$TYPE_NAME,
				'STYPE'				=>	$post['STYPE'],
				'STYPE_NAME'		=>	$STYPE_NAME,
				'VAL'				=>	$post['VAL'],
				'HOST_MAP_ID'		=>	$post['HOST_MAP_ID'],
				'STATUS'			=>	$post['STATUS'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'CREATE_TIME'		=>	date('YmdHis'),
			);
			$hostModel = M('host_config');
			$res_host = $hostModel->add($hostdata);
			
			//判断
			if(!$res_host){
				$this->wrong('添加失败！'.$hostModel->getLastSql());
			}
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//支付渠道 0:上福 1:浙星 2:JAVA 3:民生 4:华宝 5:威富通 6:易联众 7:通莞聚合
		$channel_list = C('CHANNEL_LIST');
		$config_list = array(
			array('id'=>1,'name'=>'交易量'),
			array('id'=>2,'name'=>'交易额'),
			array('id'=>3,'name'=>'时间段'),
			array('id'=>4,'name'=>'扣率'),
			array('id'=>5,'name'=>'分类'),
			array('id'=>6,'name'=>'结算方式'),
		);
		$stype_list = array(
			array('id'=>1,'name'=>'每日'),
			array('id'=>2,'name'=>'每周'),
			array('id'=>3,'name'=>'每月'),
		);
		$hostModel = M('host');
		$host_list = $hostModel->where(array('HOST_STATUS'=>0))->select();
		//扣率
		$rateModel = M('rate_sf');
		$rate_list = $rateModel->select();
		$this->assign('rate_list', $rate_list);
		$this->assign('host_list', $host_list);
		$this->assign('channel_list', $channel_list);
		$this->assign('config_list', $config_list);
		$this->assign('stype_list', $stype_list);
		$this->display();
	}
	
	/*
	* 通道管理 修改
	**/
	public function host_config_edit() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "host_config_edit") {
			//验证
			if(empty($post['ID']) || empty($post['TYPE']) || empty($post['TYPE'])!=0 || empty($post['STYPE']) || empty($post['HOST_MAP_ID'])){
				$this->wrong("缺少必填项数据！");
			}
			//Host 通道
			$typeArr = array('交易量','交易额','时间段','扣率','分类','结算方式');
			$TYPE_NAME = $typeArr[$post['TYPE']-1];
			
			$STYPE_NAME = '';
			switch($post['TYPE']){
				case 1:
				case 2:
				case 3:
					$stypeArr = array('每日','每周','每月','每笔');
					$STYPE_NAME = $stypeArr[$post['STYPE']-1];
				break;
				case 4:
					$STYPE_NAME = ($post['STYPE']/100).'%';
				break;
				case 5:
					$stypeArr = array('医疗','餐饮','生活服务','购物','旅游出行','休闲娱乐','汽车服务','其他');
					$STYPE_NAME = $stypeArr[$post['STYPE']-1];
				break;
				case 6:
					$STYPE_NAME = 'T+';
				break;
			}
			$hostdata = array(
				'TYPE'				=>	$post['TYPE'],
				'TYPE_NAME'			=>	$TYPE_NAME,
				'STYPE'				=>	$post['STYPE'],
				'STYPE_NAME'		=>	$STYPE_NAME,
				'VAL'				=>	$post['VAL'],
				'HOST_MAP_ID'		=>	$post['HOST_MAP_ID'],
				'STATUS'			=>	$post['STATUS'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'CREATE_TIME'		=>	date('YmdHis'),
			);
			$hostModel = M('host_config');
			$res_host = $hostModel->where(array('ID'=>$post['ID']))->save($hostdata);
			if(!$res_host){
				$this->wrong("修改失败！");
			}																//NEWS_FLAG是状态
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$hostModel = M('host_config');
		$info  = $hostModel->where(array('ID'=>$id))->find();
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}		
		//支付渠道 0:上福 1:浙星 2:JAVA 3:民生 4:华宝 5:威富通 6:易联众 7:通莞聚合
		$channel_list = C('CHANNEL_LIST');
		$config_list = array(
			array('id'=>1,'name'=>'交易量'),
			array('id'=>2,'name'=>'交易额'),
			array('id'=>3,'name'=>'时间段'),
			array('id'=>4,'name'=>'扣率'),
			array('id'=>5,'name'=>'分类'),
			array('id'=>6,'name'=>'结算方式'),
		);
		$stype_list = array(
			array('id'=>1,'name'=>'每日'),
			array('id'=>2,'name'=>'每周'),
			array('id'=>3,'name'=>'每月'),
		);
		$hostModel = M('host');
		$host_list = $hostModel->where(array('HOST_STATUS'=>0))->select();
		//扣率
		$rateModel = M('rate_sf');
		$rate_list = $rateModel->select();
		$this->assign('rate_list', $rate_list);
		$this->assign('host_list', $host_list);
		$this->assign('channel_list', $channel_list);
		$this->assign('config_list', $config_list);
		$this->assign('stype_list', $stype_list);
		$this->assign('info', 				$info);
		$this->display('host_config_add');
	}
	
	/*
	* 商户路由变更管理 列表
	**/
	public function shost() {
		$post = I('post');
		if($post['submit'] == "shost"){
			$where = "sh.SHOP_STATUS = 0";
			$soplv = filter_data('soplv');	//列表查询
			//状态
			if($post['SHOP_STATUS'] != '') {
				$where .= " and sh.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
			}
			//分支
			if($soplv['bid'] != '') {
				$where .= " and sh.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$where .= " and sh.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			//MCC
			if($post['MCC_TYPE'] != '') {
				$where .= " and sh.MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//商户类型码
			if ($post['MCC_CODE']) {
				$where .= " and sh.MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//商户号
			if ($post['SHOP_MAP_ID']) {
				$where .= " and sh.SHOP_MAP_ID = '".$post['SHOP_MAP_ID']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and sh.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//分页
			$count = M('shop')->alias('sh')->where($where)->count();
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list = M('shop')->alias('sh')
				->join('LEFT JOIN a_branch b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN a_partner a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where)
				->field('*,DATE_FORMAT(sh.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($p->firstRow.','.$p->listRows)
				->order('sh.SHOP_MAP_ID desc')
				->select();
			
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set ('_currentUrl_', 	__SELF__);
		$this->assign ('mcc_type',C('MCC_TYPE'));					//MCC类型
	
		$this->display();
	}
	/*
	* 商户路由变更管理 修改
	**/
	public function shost_edit() {
		$shost = I('shost');
		$shop = I('shop');
		$home = session('HOME');
		if($shost['submit'] == "shost_edit") {
			//组装路由通道数据
			$data = array(
				'SHOP_MAP_ID'	=>	$shost['SHOP_MAP_ID'],
				'HOST_MAP_ID'	=>	$shop['HOST_MAP_ID'],										
				'ISAUTO'		=>	$shop['ISAUTO']										
			);

			$shopModel = M('shop');
			$shop = $shopModel->where(array('SHOP_MAP_ID'=>$data['SHOP_MAP_ID'],'SHOP_STATUS'=>0))->find();
			$res = $shopModel->where(array('SHOP_MAP_ID'=>$data['SHOP_MAP_ID'],'SHOP_STATUS'=>0))->save($data);
			if(!$res){
				$this->wrong('商户路由通道变更失败！');
			}
			$autoArr = array('关','开');
			$hostModel = M('host');
			$oldHost = $hostModel->where(array('HOST_MAP_ID'=>$shop['HOST_MAP_ID']))->find();
			$nowHost = $hostModel->where(array('HOST_MAP_ID'=>$data['HOST_MAP_ID']))->find();
			if($shop['HOST_MAP_ID']!=$data['HOST_MAP_ID']){
				$msg = ($oldHost['HOST_NAME']?$oldHost['HOST_NAME']:'无').'->'.$nowHost['HOST_NAME'];
			}else{
				$msg = '自动:'.$autoArr[$data['ISAUTO']];
			}
			//组装数据
			$check_data = array(
				'CHECK_NO' 		=> '3'.setStrzero($shost['SHOP_MAP_ID'],15),
				'CHECK_FLAG'	=> '2',
				'CHECK_POINT'	=> '0',
				'CHECK_DESC'	=> '【商户路由通道信息变更】'.$msg,
				'USER_ID'		=> $home['USER_ID'],
				'USER_NAME'		=> $home['USER_NAME'],
				'CHECK_TIME'	=> date('YmdHis')
			);
			$res = D($this->MCheck)->addCheck($check_data);
			if($res['state'] != 0){
				$this->wrong('信息变更记录失败');
			}
			$this->right('信息变更成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$shop_info = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");

		if(empty($shop_info)){
			$this->wrong("参数数据出错！");
		}
		if ($scfg_info['SHOP_STATUS']==4) {
			$this->wrong('当前状态不允许此操作');
		}
		//路由通道
		$hostModel = M('host');
		$host_list = $hostModel->where(array('HOST_STATUS'=>0))->select();
		
		$this->assign ('shop_info', $shop_info);
		$this->assign ('host_list', $host_list);
		$this->display();
	}
	
	//商户路由通道映射 20170607
	/*
	* 商户映射
	**/
	public function shmapped() {
		 $post = I('post');
		if($post['submit'] == "shmapped"){
			$where = "1=1 and p.STATUS=0";
			//通道名称
			if($post['HOST_MAP_ID']!='') {
				$where .= " and p.HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
			}
			//平台商户ID
			if($post['SHOP_MAP_ID']) {
				$where .= " and sh.SHOP_MAP_ID = '".$post['SHOP_MAP_ID']."'";
			}
			//通道商户号
			if($post['SHOP_ID1']) {
				$where .= " and p.SHOP_ID1 = '".$post['SHOP_ID1']."'";
			}
			$where .= " and h.CHANNEL!=''";
			//分页
			$piecesModel = M('shop_intopieces');
			$count = $piecesModel->alias('p')
						->join('a_host h on h.HOST_MAP_ID=p.HOST_MAP_ID','LEFT')
						->join('a_shop sh on sh.SHOP_MAP_ID=p.SHOP_MAP_ID','LEFT')
						->where($where)->count();
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = $piecesModel->alias('p')
						->join('a_host h on h.HOST_MAP_ID=p.HOST_MAP_ID','LEFT')
						->join('a_shop sh on sh.SHOP_MAP_ID=p.SHOP_MAP_ID','LEFT')
						->field("*,h.HOST_MAP_ID HOST_MAP_ID")->where($where)->limit($p->firstRow.','.$p->listRows)->order('p.SHOP_MAP_ID desc')->select();
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		//通道列表
		$hostModel = M('host');
		$hostsel = $hostModel->where(array("HOST_STATUS"=>array('neq',2), "CHANNEL"=>array('neq','')))->select();
		$this->assign ('hostsel', 	$hostsel);		//通道列表
		\Cookie::set ('_currentUrl_', 	__SELF__);	
		$this->display();
	}
	/*
	* 商户映射 添加
	**/
	public function shmapped_add() {
		$post = I('post');
		if($post['submit'] == "shmapped_add"){
			if (empty($post['SHOP_MAP_ID']) || $post['HOST_MAP_ID']=='' || empty($post['SHOP_ID1']) || $post['FLAG']=='') {
				$this->wrong('请填写必填项！');
			}
			$shopModel = M('shop');
			$shopInfo = $shopModel->alias('sh')
						->join('a_smdr smdr on smdr.SHOP_MAP_ID=sh.SHOP_MAP_ID','LEFT')->where(array('sh.SHOP_MAP_ID'=>$post['SHOP_MAP_ID'],'smdr.PAY_TYPE'=>5))->find();
			if (empty($shopInfo)) {
				$this->wrong('平台商户不存在, 请填写正确的平台商户号！');
			}
			//判断是否重复
			$piecesModel = M('shop_intopieces');
			$condition = array(
				'SHOP_MAP_ID'=>$post['SHOP_MAP_ID'],
				'HOST_MAP_ID'=>$post['HOST_MAP_ID'],
				'FLAG'=>$post['FLAG'],
				'STATUS'=>0,
			);
			$res = $piecesModel->where($condition)->find();
			if ($res) {
				$this->wrong('当前商户的扣率在此通道已存在, 不能再添加！');
			}
			// $res = $piecesModel->where(array('SHOP_ID1'=>$post['SHOP_ID1']))->find();
			// if ($res) {
				// $this->wrong('当前商户的通道号已存在, 不能重复添加！');
			// }
			$pieces_data = array(
				'SHOP_MAP_ID'	=> $post['SHOP_MAP_ID'],
				'FLAG'			=> $post['FLAG'],
				'HOST_MAP_ID'		=> $post['HOST_MAP_ID'],
				'SHOP_ID1'		=> $post['SHOP_ID1'],
				'SHOP_ID'		=> $shopInfo['MOBILE'],
				'afee'			=> $post['afee'],
				'KEYT'			=> $post['KEYT']
			);
			$res = $piecesModel->add($pieces_data);
			if (!$res) {
				$this->wrong('映射失败！');
			}
			$this->right('映射成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//通道列表
		$hostModel = M('host');
		$hostsel = $hostModel->where(array("HOST_STATUS"=>array('neq',2), "CHANNEL"=>array('neq','')))->select();
		$this->assign ('hostsel', 	$hostsel);		//通道列表
		$this->display();
	}	
	/*
	* 商户映射 修改
	**/
	public function shmapped_edit() {
		$post = I('post');
		if($post['submit'] == "shmapped_edit"){
			if (empty($post['SHOP_MAP_ID']) || $post['HOST_MAP_ID']=='' || empty($post['SHOP_ID1']) || empty($post['ID']) || $post['FLAG']=='' || $post['afee']=='') {
				$this->wrong('请填写必填项！');
			}
			$shopModel = M('shop');
			$shopInfo = $shopModel->alias('sh')
						->join('a_smdr smdr on smdr.SHOP_MAP_ID=sh.SHOP_MAP_ID','LEFT')->where(array('sh.SHOP_MAP_ID'=>$post['SHOP_MAP_ID'],'smdr.PAY_TYPE'=>5))->find();
			if (empty($shopInfo)) {
				$this->wrong('平台商户不存在, 请填写正确的平台商户号！');
			}
			//判断是否重复
			$piecesModel = M('shop_intopieces');
			$res = $piecesModel->where(array('ID'=>$post['ID']))->find();
			if (empty($res)) {
				$this->wrong('路由不存在');
			}
			// $res = $piecesModel->where(array('SHOP_ID1'=>array('neq',$res['SHOP_ID1']),'SHOP_MAP_ID'=>$post['SHOP_MAP_ID'],'CHANNEL'=>$post['CHANNEL'],'FLAG'=>$post['FLAG']))->find();
			// if ($res) {
				// $this->wrong('当前商户的扣率在此通道已存在, 不能再添加！');
			// }
			$pieces_data = array(
				'SHOP_MAP_ID'	=> $post['SHOP_MAP_ID'],
				'FLAG'			=> $post['FLAG'],
				'HOST_MAP_ID'		=> $post['HOST_MAP_ID'],
				'SHOP_ID1'		=> $post['SHOP_ID1'],
				'SHOP_ID'		=> $shopInfo['MOBILE'],
				'afee'			=> $post['afee'],
				'KEYT'			=> $post['KEYT']
			);
			$res = $piecesModel->where(array('ID'=>$post['ID']))->save($pieces_data);
			if (!$res) {
				$this->wrong('映射失败！');
			}
			$this->right('映射成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//获取通道POS映射信息
		$where = "p.ID = ".$id;
		$piecesModel = M('shop_intopieces');
		$info  = $piecesModel->alias('p')
					->field("h.*,p.*")
					->join('a_host h on h.HOST_MAP_ID=p.HOST_MAP_ID','LEFT')
					->join('a_shop sh on sh.SHOP_MAP_ID=p.SHOP_MAP_ID','LEFT')
					->where($where)->find();
		//通道列表
		$hostModel = M('host');
		$hostsel = $hostModel->where(array("HOST_STATUS"=>array('neq',2), "CHANNEL"=>array('neq','')))->select();
		$this->assign ('hostsel', 	$hostsel);		//通道列表
		$this->assign ('info', 		$info);			//POS映射信息
		$this->display('shmapped_add');
	}	
	/*
	* 商户映射 删除
	**/
	public function shmapped_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$piecesModel = M('shop_intopieces');
		$res = $piecesModel->where(array('ID'=>$id))->save(array('STATUS'=>1));
		if(!$res){
			$this->wrong('删除失败');
		}
		$this->right('删除成功', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	
	// 试点商户导入至小二后台 2017.06.09
	public function injfbshop(){
		$condition['ID_BSSCODE'] = array('eq', '');
		$condition['SHOP_STATUS'] = array('neq', 0);
		$condition['PARTNER_MAP_ID'] = array('in', '101545,101534');
		$shopModel = M('shop');
		$list = $shopModel->field("SHOP_MAP_ID")->where($condition)->select();
		echo $shopModel->getLastSql().'<br/>';
		echo count($list);
		$jfbModel = M('shop_', DB_PREFIX_JFB, DB_DSN_JFB);
		foreach($list as $val){
			$jfb = $jfbModel->query("select * from jfb_shop_ where ID_=".$val['SHOP_MAP_ID']);
			if(!$jfb){
				$sync_res = shop_sync_data($val['SHOP_MAP_ID'],1);
				if ($sync_res) {
					echo json_encode($val).'<br/>';
					Add_LOG('INJFBSHOP',__LINE__ .' | '.json_encode($val));
				}
			}
		}
	}
	
	//正常老商户 单进件 上福老接口
	public function oldsf_add(){
		$post = I('post');
		if ($post['submit'] == "oldsf_add") {
			$shopModel = M('shop');
			$shopSZModel = M('shop_intopieces');
			$shopInfo = $shopModel->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			if(!$shopInfo){
				$this->wrong('商户不存在');
			}
			if($shopInfo['SHOP_STATUS'] !=0 ){
				$this->wrong('当前状态不可操作');
			}
			$shopSZCon['SHOP_MAP_ID'] = $shopInfo['SHOP_MAP_ID'];
			$shopSZCon['STATUS'] = 0;
			$shopSZInfo = $shopSZModel->where($shopSZCon)->find();
			if($shopSZInfo){
				$this->wrong('商户已进件');
			}
			//========================   2017.03.30 CRYSTAL  BEGIN  ==================================
			$shopInfo = D($this->MShop)->findmoreShop2(array('s.SHOP_MAP_ID'=>$post['SHOP_MAP_ID'],'smdr.PAY_TYPE'=>5));
			
			//上福进件
			$url = PHP_API_URL.'index.php/Home/Shop/upShopinfo_sea';
			$data = array(
				'shop_map_id'=>$post['SHOP_MAP_ID'],
				'user_login_mobile'=>$shopInfo['MOBILE'],
				'mcht_name'=>$shopInfo['SHOP_NAME'],
				'mcht_short_name'=>$shopInfo['SHOP_NAMEABCN'],
				'address'=>$shopInfo['ADDRESS'],
				'leg_phone'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANK_MOBILE1']:$shopInfo['BANK_MOBILE2'],
				'county_code'=>$shopInfo['CITY_NO'],
				'leg_name'=>$shopInfo['LP_NAME'],
				'leg_mail'=>$shopInfo['EMAIL'],
				'per_fee'=>$shopInfo['JFB_PER_FEE'],
				'acc_name'=>$shopInfo['LP_NAME'],
				'acc_bank_name'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANK_NAME1']:$shopInfo['BANK_NAME2'],
				'acc_no'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANKACCT_NO1']:$shopInfo['BANKACCT_NO2'],
				'service_tel'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANK_MOBILE1']:$shopInfo['BANK_MOBILE2'],
				'id_no'=>$shopInfo['LP_ID'],
				'settle_cycle'=>$shopInfo['SETTLE_T'],
				'acc_bank_no'=>$shopInfo['SHOP_BANK_FLAG']==0?$shopInfo['BANKACCT_BID1']:$shopInfo['BANKACCT_BID2']
			);

			$data['signTime'] = time();
			$data['funcName'] = 'upShopinfo_sea';
			$data["sign"] = rySignStr($data);
			$jsonStr = json_encode($data);
			// Add_LOG('SHOP',$url.' '.$jsonStr);
			$result = getCurlDataByjson($url, $jsonStr);
			Add_LOG('SHOP',$result);
			//dump($result);exit;
			//$this->testlog(__LINE__. 'LINE ajax_piece_review' .json_encode($result));
			if ($result->status != "200") {
				$datas['NOTE3'] = $result->message;
				D($this->MShop)->updateShop($where,$datas);
				$this->wrong("OLD SHANGFU:".$result->message);
			}else{
				$datas['HOST_MAP_ID'] = 1;
				D($this->MShop)->updateShop($where,$datas);
			}
			//========================   2017.03.30 CRYSTAL  END  ====================================
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$shopModel = M('shop');
		$shopInfo = $shopModel->where(array('SHOP_MAP_ID'=>$id))->find();
		if($shopInfo['SHOP_STATUS'] !=0 ){
			$this->wrong('当前状态不可操作');
		}
		$this->shop_show('online_add');
	}
	
	/*
	* 路由商户映射查询	导出
	**/
	public function shmapped_export() {
		//临时加大PHP占用内存
		ini_set('memory_limit', '256M'); //内存限制
		set_time_limit(0);
		$post  = array(
			'CHANNEL'		=>	I('CHANNEL'),
			'SHOP_MAP_ID'		=>	I('SHOP_MAP_ID'),
			'SHOP_ID1'		=>	I('SHOP_ID1'),
		);
		$where = "1=1";
		//通道名称
		if($post['CHANNEL']) {
			$condition['p.CHANNEL'] = $post['CHANNEL'];
		}
		//平台商户ID
		if($post['SHOP_MAP_ID']) {
			$condition['p.SHOP_MAP_ID'] = $post['SHOP_MAP_ID'];
		}
		//通道商户号
		if($post['SHOP_ID1']) {
			$condition['p.SHOP_ID1'] = $post['SHOP_ID1'];
		}
		$condition['s.SHOP_STATUS'] = 0;
		$condition['p.STATUS'] = 0;
		
		$piecesModel = M('shop_intopieces');
		$count = $piecesModel->alias('p')
			->join('a_shop s on s.SHOP_MAP_ID=p.SHOP_MAP_ID','LEFT')
			->where($condition)->count();

		//计算
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
			
			$list = $piecesModel->alias('p')
				->join('a_shop s on s.SHOP_MAP_ID=p.SHOP_MAP_ID','LEFT')
				->where($condition)->limit($bRow.','.$eRow)->select();
			
			//导出操作
			$xlsname = '路由商户映射文件('.($p+1).')';
			$xlscell = array(
				array('SHOP_NAME',		'商户名称'),
				array('SHOP_ID1',		'商户号'),
				array('FLAG',	'标志'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'SHOP_NAME'	=>	$val['SHOP_NAME'],
					'SHOP_ID1'	=>	$val['SHOP_ID1'],
					'FLAG'	=>	$val['FLAG']==1?'高扣':'低扣',
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	/**
		@param 民生银行单进件
		@time  2017-07-07
	*/
	public function Ms_intopiece(){
		$post = I('post');
		if ($post['submit'] == "Ms_intopiece") {
			$shopModel = M('shop');
			$shopSZModel = M('shop_intopieces');
			$shopInfo = $shopModel->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			if(!$shopInfo){
				$this->wrong('商户不存在');
			}
			if($shopInfo['SHOP_STATUS'] !=0 ){
				$this->wrong('当前状态不可操作');
			}
			$shopSZCon['SHOP_MAP_ID'] = $shopInfo['SHOP_MAP_ID'];
			$shopSZCon['CHANNEL'] = 3;
			$shopSZCon['STATUS'] = 0;
			$shopSZInfo = $shopSZModel->where($shopSZCon)->find();
			// if($shopSZInfo){
			// 	$this->wrong('商户已进件');
			// }
		    //========================   2017.07.04 CRYSTAL  BEGIN  ==================================
			$shopInfo = D($this->MShop)->findmoreShop2(array('s.SHOP_MAP_ID'=>$post['SHOP_MAP_ID'],'smdr.PAY_TYPE'=>5));
			$where = array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']);
            $scertInfo = D($this->MScert)->findScert($where);
            //民生注册通道微信进件
            $url_ms = PHP_API_URL.'index.php/Home/MchntAddReg/MchntAddRegister';
            $data_ms = array(
                'mchntFullName'=>$shopInfo['SHOP_NAME'],
                'mchntName'=>$shopInfo['SHOP_NAMEABCN'],
                'address'=>$shopInfo['ADDRESS'],
                'province'=>$shopInfo['CITY_NAME'],
                'city'=>$shopInfo['CITY_NAME'],
                'contactName'=>$shopInfo['BANKACCT_NAME2'],
                'telephone'=>$shopInfo['TEL'],
                'acdCode'=>$shopInfo['CITY_NO'],
                'corpName'=>$scertInfo['LP_NAME'],
                'idtCard'=>$scertInfo['LP_ID'],
                'per_fee'=>$shopInfo['JFB_PER_FEE'],
                'licId'=>$scertInfo['REG_ID'],
                'licValidity'=>$scertInfo['REG_EXP'],
                'servTel'=>$shopInfo['TEL'],
                'SHOP_MAP_ID'=>$post['SHOP_MAP_ID']
            );
            $data_ms = json_encode($data_ms);
			// $this->wrong("MS_WRONG1:".$data_ms);
            $result_ms = getCurlDataByjson($url_ms, $data_ms);
            Add_LOG('MS_REG_WX',$result_ms);
            // $this->wrong(json_encode($result_ms));
            if ($result_ms->status != "200") {
                $datas_ms['NOTE3'] = $result_ms->message;
                D($this->MShop)->updateShop($where,$datas_ms);
                $this->wrong("MS_WRONG1_WX1:".$result_ms->status.$result_ms->message);
            }else{
				$datas['HOST_MAP_ID'] = 1;
				D($this->MShop)->updateShop($where,$datas);
				$cmbcMchntId = $result_ms->cmbcMchntId;
				$outMchntId = $result_ms->outMchntId;
				$txnSeq = $result_ms->txnSeq;
			}


            // 支付通道微信进件
            $wheres = 'PAY_TYPE = 5 and SHOP_MAP_ID = '.$post['SHOP_MAP_ID'];
	        $shopInfo_sauth = D($this->MSauth)->findSauth($where);
	        $shopInfo_smdr = D($this->MSmdr)->findSmdr($wheres);
	        $shopInfo_sbact = D($this->MSbact)->findSbact($where);
	        $shopInfo_sdkb = D($this->MSdkb)->findSdkb($where);
	        $url_zhifu = PHP_API_URL.'index.php/Home/MchntAddPay/MchntPay';
	        $type = array("0"=>"01","1"=>"03","2"=>"02","3"=>"04","9"=>"99");
	        $sdkb_type = $shopInfo_sdkb['DK_IDNO_TYPE'];
	        $data_zhifu = array(
	        	'txnSeq'=>$txnSeq,
	        	'cmbcMchntId'=>$cmbcMchntId,
	        	'outMchntId'=>$outMchntId,
	            'apiCode'=>"微信",
	            'industryId'=>"102",
	            'operateType'=>"间联",
	            'dayLimit'=>$shopInfo_sauth['DAY_MAXAMT'],    //日限额
	            'monthLimit'=>$shopInfo_sauth['DAY_MAXAMT'],    //月限额
	            'fixFeeRate'=>sprintf( "%.2f" , $shopInfo_smdr['JFB_PER_FEE']),    //固定扣率
	            'account'=>$shopInfo_sbact['SHOP_BANK_FLAG']==0?$shopInfo_sbact['BANKACCT_NO1']:$shopInfo_sbact['BANKACCT_NO2'],//结算账号
	            'pbcBankId'=>$shopInfo_sbact['SHOP_BANK_FLAG']==0?$shopInfo_sbact['BANKACCT_BID1']:$shopInfo_sbact['BANKACCT_BID2'],    //开户行号，人民银行大小额支付行号
	            'acctName'=>$shopInfo_sbact['SHOP_BANK_FLAG']==0?$shopInfo_sbact['BANK_NAME1']:$shopInfo_sbact['BANK_NAME2'],    //开户人
	            'acctType'=>"对私",
	            'message'=>"",
	            'idType'=>$type[$sdkb_type]?$type[$sdkb_type]:"",   //开户人证件类型
	            'idCode'=>$shopInfo_sdkb['DK_IDNO'],     //证件号码
	            'acctTelephone'=>$shopInfo_sbact['SHOP_BANK_FLAG']==0?$shopInfo_sbact['BANK_MOBILE1']:$shopInfo_sbact['BANK_MOBILE2']
	        );
	        $jsonStr_zhifu = json_encode($data_zhifu);
	        $result_zhifu = getCurlDataByjson($url_zhifu, $jsonStr_zhifu);
	        Add_LOG('MS_WX',$result_zhifu);
	        // $this->testlog(__LINE__. 'LINE ajax_piece_review' .json_encode($result));
	        if ($result_zhifu->status != "200") {
	            $datas_zhifu['NOTE3'] = $result_zhifu->message;
	            D($this->MShop)->updateShop($where,$datas_zhifu);
	            $this->wrong("MS_WRONG_WX2:".$result_zhifu->message);
	        }else{
				$datas['HOST_MAP_ID'] = 1;
				D($this->MShop)->updateShop($where,$datas);

	            //民生注册通道支付宝进件
				$scertInfo = D($this->MScert)->findScert($where);
	            $url_ms = PHP_API_URL.'index.php/Home/MchntAddReg/MchntAddRegister';
	            $data_ms = array(
	                'mchntFullName'=>$shopInfo['SHOP_NAME'],
	                'mchntName'=>$shopInfo['SHOP_NAMEABCN'],
	                'address'=>$shopInfo['ADDRESS'],
	                'province'=>$shopInfo['CITY_NAME'],
	                'city'=>$shopInfo['CITY_NAME'],
	                'contactName'=>$shopInfo['BANKACCT_NAME2'],
	                'telephone'=>$shopInfo['TEL'],
	                'acdCode'=>$shopInfo['CITY_NO'],
	                'corpName'=>$scertInfo['LP_NAME'],
	                'idtCard'=>$scertInfo['LP_ID'],
	                'per_fee'=>$shopInfo['JFB_PER_FEE'],
	                'licId'=>$scertInfo['REG_ID'],
	                'licValidity'=>$scertInfo['REG_EXP'],
	                'servTel'=>$shopInfo['TEL'],
	                'SHOP_MAP_ID'=>$post['SHOP_MAP_ID']
	            );
	            $data_ms = json_encode($data_ms);
				// $this->wrong("MS_WRONG1:".$data_ms);
	            $result_ms = getCurlDataByjson($url_ms, $data_ms);
	            Add_LOG('MS_REG_ZFB',$result_ms);
	            // $this->wrong($result_ms);
	            if ($result_ms->status != "200") {
	                $datas_ms['NOTE3'] = $result_ms->message;
	                D($this->MShop)->updateShop($where,$datas_ms);
	                $this->wrong("MS_WRONG_ZFB1:".$result_ms->status.$result_ms->message);
	            }else{
					$datas['HOST_MAP_ID'] = 1;
					D($this->MShop)->updateShop($where,$datas);
					$cmbcMchntId = $result_ms->cmbcMchntId;
					$outMchntId = $result_ms->outMchntId;
					$txnSeq = $result_ms->txnSeq;
					// 支付通道支付宝进件
					$data_zhifu = array(
	        		'txnSeq'=>$txnSeq,
		        	'cmbcMchntId'=>$cmbcMchntId,
		        	'outMchntId'=>$outMchntId,
		            'apiCode'=>"支付宝",
		            'industryId'=>"2016063000191709",
		            'operateType'=>"间联",
		            'dayLimit'=>$shopInfo_sauth['DAY_MAXAMT'],    //日限额
		            'monthLimit'=>$shopInfo_sauth['DAY_MAXAMT'],    //月限额
		            'fixFeeRate'=>sprintf( "%.2f" , $shopInfo_smdr['JFB_PER_FEE']),    //固定扣率
		            'account'=>$shopInfo_sbact['SHOP_BANK_FLAG']==0?$shopInfo_sbact['BANKACCT_NO1']:$shopInfo_sbact['BANKACCT_NO2'],//结算账号
		            'pbcBankId'=>$shopInfo_sbact['SHOP_BANK_FLAG']==0?$shopInfo_sbact['BANKACCT_BID1']:$shopInfo_sbact['BANKACCT_BID2'],    //开户行号，人民银行大小额支付行号
		            'acctName'=>$shopInfo_sbact['SHOP_BANK_FLAG']==0?$shopInfo_sbact['BANK_NAME1']:$shopInfo_sbact['BANK_NAME2'],    //开户人
		            'acctType'=>"对私",
		            'message'=>"",
		            'idType'=>$type[$sdkb_type]?$type[$sdkb_type]:"",   //开户人证件类型
		            'idCode'=>$shopInfo_sdkb['DK_IDNO'],     //证件号码
		            'acctTelephone'=>$shopInfo_sbact['SHOP_BANK_FLAG']==0?$shopInfo_sbact['BANK_MOBILE1']:$shopInfo_sbact['BANK_MOBILE2']
			        );
			        $jsonStr_zhifu = json_encode($data_zhifu);
			        $result_zhifu = getCurlDataByjson($url_zhifu, $jsonStr_zhifu);
			        Add_LOG('MS_ZFB',$result_zhifu);
			        if ($result_zhifu->status != "200") {
			            $datas_zhifu['NOTE3'] = $result_zhifu->message;
			            D($this->MShop)->updateShop($where,$datas_zhifu);
			            $this->wrong("MS_WRONG_ZFB2:".$result_zhifu->message);
		        	}else{
		        		$datas['HOST_MAP_ID'] = 1;
						D($this->MShop)->updateShop($where,$datas);
		        	}
				}
			}
            //========================   2017.07.04 CRYSTAL  END  ====================================
			//========================   2017.07.04 CRYSTAL  START  ==================================
			$MS_url = PHP_API_URL.'index.php/Home/MsInforUpload/MShopReg';
			$MS_data = array(
				"shopMapId"=>$post['SHOP_MAP_ID'],
				"cmbcMchntId"=>$cmbcMchntId,
				"outMchntId"=>$outMchntId
				);
			$MS_jsonStr = json_encode($MS_data);
			$MS_result = getCurlDataByjson($MS_url, $MS_jsonStr);
			Add_LOG('MS_PHOTO',$MS_result);
			if ($MS_result->status != "200") {
				$datas['NOTE3'] = $MS_result->message;
				D($this->MShop)->updateShop($where,$datas);
				$this->wrong("MS_WRONG_PICTURE:".$MS_result->status.$MS_result->message);
			}else{
				$datas['HOST_MAP_ID'] = 1;
				D($this->MShop)->updateShop($where,$datas);
				// $this->wrong("MS_SUCCESS:".$MS_result->message);
			}
			//========================   2017.07.04 CRYSTAL  END  ====================================
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$shopModel = M('shop');
		$shopInfo = $shopModel->where(array('SHOP_MAP_ID'=>$id))->find();
		if($shopInfo['SHOP_STATUS'] !=0 ){
			$this->wrong('当前状态不可操作');
		}
		$this->shop_show('Ms_intopiece');
	}
	
	/*
	 * 立减配额 列表 
	 **/
	public function sminus() {
		 $post = I('post');
		if($post['submit'] == "sminus"){
			$where = "1=1";
			//平台商户ID
			if($post['SHOP_MAP_ID']) {
				$where .= " and sh.SHOP_MAP_ID = '".$post['SHOP_MAP_ID']."'";
			}
			//平台商户名称
			if($post['SHOP_NAME']) {
				$where .= " and sh.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//分页
			$sminusModel = M('sminus');
			$count = $sminusModel->alias('m')
						->join('a_shop sh on sh.SHOP_MAP_ID=m.SHOP_MAP_ID','LEFT')
						->where($where)->count();
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = $sminusModel->alias('m')
						->join('a_shop sh on sh.SHOP_MAP_ID=m.SHOP_MAP_ID','LEFT')
						->where($where)->limit($p->firstRow.','.$p->listRows)->select();
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 	__SELF__);	
		$this->display();
	}
	/*
	* 立减配额 添加
	**/
	public function sminus_add() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "sminus_add"){
			if (empty($post['SHOP_MAP_ID']) || empty($post['NORMAL_AMT']) || empty($post['VIP_AMT'])) {
				$this->wrong('请填写必填项！');
			}
			//判断是否重复
			$res  = M('sminus')->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			$shop  = M('shop')->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			if(empty($shop)){
				$this->wrong('平台商户不存在！');
			}
			if ($res) {
				$this->wrong('当前平台商户号已存在配额, 不能重复添加！');
			}
			//转换为分
			$normal_amt = $post['NORMAL_AMT'] * 100;
			$total_normal_amt = $normal_amt;
			$vip_amt = $post['VIP_AMT'] * 100;
			$total_vip_amt = $vip_amt;
			$key = C('MACKEY');//key
			$text = getLD($post['SHOP_MAP_ID']).getLD($normal_amt).
				getLD($total_normal_amt).getLD($vip_amt).
				getLD($total_vip_amt);//拼接
			$mac = strtoupper(hash_hmac('md5',$text,$key));
			//组装数据
			$data = array(
				'SHOP_MAP_ID'		=> $post['SHOP_MAP_ID'],
				'STATUS'			=> $post['STATUS'],
				'TOTAL_NORMAL_AMT'	=> $total_normal_amt,
				'NORMAL_AMT'		=> $normal_amt,
				'TOTAL_VIP_AMT'		=> $total_vip_amt,
				'VIP_AMT'			=> $vip_amt,
				'MAC'				=> $mac,
				'SYSTEM_TIME'		=> date('YmdHis'),
				'CREATE_USERID'		=> $home['USER_ID'],
				'CREATE_USERNAME'	=> $home['USER_NAME'],
			);
			$res = M('sminus')->add($data);
			if (!$res) {
				$this->wrong('添加失败！');
			}
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->display();
	}	
	/*
	* 立减配额 修改
	**/
	public function sminus_edit() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "sminus_edit"){
			if (empty($post['SHOP_MAP_ID']) || empty($post['NORMAL_AMT']) || empty($post['VIP_AMT'])) {
				$this->wrong('请填写必填项！');
			}
			//判断是否重复
			$minus  = M('sminus')->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			$shop  = M('shop')->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			if(empty($shop)){
				$this->wrong('平台商户不存在！');
			}
			if (empty($minus)) {
				$this->wrong('当前平台商户未参与配额！');
			}
			$normal = ($post['NORMAL_AMT'] * 100) - $minus['NORMAL_AMT'];
			$vip = ($post['VIP_AMT'] * 100) - $minus['VIP_AMT'];
			//转换为分
			$normal_amt = ($minus['NORMAL_AMT'] + $normal);
			$total_normal_amt = ($minus['TOTAL_NORMAL_AMT'] + $normal);
			$vip_amt = ($minus['VIP_AMT'] + $vip);
			$total_vip_amt = ($minus['TOTAL_VIP_AMT'] + $vip);
			$key = C('MACKEY');//key
			$text = getLD($post['SHOP_MAP_ID']).getLD($normal_amt).
				getLD($total_normal_amt).getLD($vip_amt).
				getLD($total_vip_amt);//拼接
			$mac = strtoupper(hash_hmac('md5',$text,$key));
			//组装数据
			$data = array(
				'SHOP_MAP_ID'		=> $post['SHOP_MAP_ID'],
				'STATUS'			=> $post['STATUS'],
				'TOTAL_NORMAL_AMT'	=> $total_normal_amt,
				'NORMAL_AMT'		=> $normal_amt,
				'TOTAL_VIP_AMT'		=> $total_vip_amt,
				'VIP_AMT'			=> $vip_amt,
				'MAC'				=> $mac,
				'SYSTEM_TIME'		=> date('YmdHis'),
				'CREATE_USERID'		=> $home['USER_ID'],
				'CREATE_USERNAME'	=> $home['USER_NAME'],
			);
			$res = M('sminus')->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->save($data);
			if (!$res) {
				$this->wrong('修改失败！');
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$sminusModel = M('sminus');
		$info  = $sminusModel->where(array('SHOP_MAP_ID'=>$id))->find();
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}		
		$this->assign('info', $info);
		$this->display('sminus_add');
	}
	
	//CRYSTAL 20170711
	/*
	* 商户代付账户变更管理 列表
	**/
	public function sdfb() {
		$post = I('post');
		if($post['submit'] == "sdfb"){
			$where = "sh.SHOP_STATUS = 0";
			$soplv = filter_data('soplv');	//列表查询
			//状态
			if($post['SHOP_STATUS'] != '') {
				if($post['SHOP_STATUS'] == 0){
					$where .= " and sd.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}else{
					$where .= " and tmp.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
				}
			}
			//分支
			if($soplv['bid'] != '') {
				$where .= " and sh.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$where .= " and sh.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			//MCC
			if($post['MCC_TYPE'] != '') {
				$where .= " and sh.MCC_TYPE = '".$post['MCC_TYPE']."'";
			}
			//商户类型码
			if ($post['MCC_CODE']) {
				$where .= " and sh.MCC_CODE = '".$post['MCC_CODE']."'";
			}
			//商户号
			if ($post['SHOP_NO']) {
				$where .= " and sh.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and sh.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//分页
			if($post['SHOP_STATUS'] == '0'){
				$count = D($this->MSdfb)->countNotmpSdfb($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->MSdfb)->getNotmpSdfblist($where, 'sd.SHOP_MAP_ID,sh.SHOP_NO,sh.SHOP_STATUS,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME', $p->firstRow.','.$p->listRows, 'sd.SHOP_MAP_ID desc');
			}else{
				$count = D($this->MSdfb)->countSdfb($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->MSdfb)->getSdfblist($where, 'sd.SHOP_MAP_ID,sh.SHOP_NO,sd.SHOP_STATUS,sh.SHOP_NAME,sh.CITY_NO,sh.CREATE_TIME,a.PARTNER_NAME,b.BRANCH_NAME,tmp.SHOP_MAP_ID as TMP_ID,tmp.SHOP_STATUS as TMP_STATUS', $p->firstRow.','.$p->listRows, 'TMP_ID desc,sd.SHOP_MAP_ID desc');
			}
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set ('_currentUrl_', 	__SELF__);
		$this->assign ('mcc_type',C('MCC_TYPE'));					//MCC类型
		//审核状态数组
		$this->assign ('shop_status',C('CHECK_POINT.all'));			//通道全部状态
		$this->assign ('shop_status_check',C('CHECK_POINT.check'));		//部分状态
		
		$this->display();
	}

	//汇米 商户银行信息管理 查询
	public function bank(){
		$post = I('post');
		if($post['submit'] == "bank"){
			$soplv = filter_data('soplv');	//列表查询
			$where = "SHOP_STATUS = 0";
			//商户号
			if($post['SHOP_MAP_ID']) {
				$where .= " and SHOP_MAP_ID = '".$post['SHOP_MAP_ID']."'";
			}
			if($post['SHOP_BANK_FLAG'] != '') {
				$where .= " and SHOP_BANK_FLAG = '".$post['SHOP_BANK_FLAG']."'";
			}
			//商户名称
			if($post['BANKACCT_NAME']) {
				$where .= " and ( BANKACCT_NAME1 LIKE '%".$post['BANKACCT_NAME']."%'";
				$where .= " or BANKACCT_NAME2 LIKE '%".$post['BANKACCT_NAME']."%')";
			}

			//分页
			$count = D($this->MBank)->countBank($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MBank)->getBanklist($where, '*', $p->firstRow.','.$p->listRows);

			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);			
		$this->display();
	}
	//汇米 商户支付信息管理 查询
	public function sbank(){
		$post = I('post');
		if($post['submit'] == "sbank"){
			$soplv = filter_data('soplv');	//列表查询
			$where = "ID_TYPE = 01";
			//商户号
			if($post['SHOP_MAP_ID']) {
				$where .= " and SHOP_MAP_ID = '".$post['SHOP_MAP_ID']."'";
			}
			//预留手机号
			if($post['MOBILE'] != '') {
				$where .= " and MOBILE = '".$post['MOBILE']."'";
			}
			//卡类型
			if($post['ACCT_TYPE'] != '') {
				$where .= " and ACCT_TYPE = '".$post['ACCT_TYPE']."'";
			}
			//持卡人姓名
			if($post['ACC_NAME']) {
				$where .= " and ACC_NAME LIKE '%".$post['ACC_NAME']."%'";
			}
			// var_dump($where);
			//分页
			$count = D($this->MSbank)->countBank($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MSbank)->getBanklist($where, '*', $p->firstRow.','.$p->listRows);

			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);			
		$this->display();
	}

	//汇米 商户等级扣率管理 查询
	public function grade(){
		$post = I('post');
		if($post['submit'] == "grade"){
			$where = "0 = 0";
			//控制器信息
			if($post['HOST_MAP_ID'] != '') {
				$where .= " and a_host.HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
			}
			//商户等级
			if($post['SHOP_GRADE'] != '') {
				$where .= " and SHOP_GRADE = '".$post['SHOP_GRADE']."'";
			}
			//状态
			if($post['STATUS'] != '') {
				$where .= " and STATUS = '".$post['STATUS']."'";
			}
			//分页
			$count = D($this->MGrade)->countBank($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MGrade)->getBanklist($where, '*', $p->firstRow.','.$p->listRows,'ID desc');
			//查询控制器信息
			$find_host = D($this->MGrade)->findhost('1=1','HOST_NAMEAB');
			$host_result = array();
			foreach ($find_host as $key => $value) {
				foreach ($value as $k => $val) {
					$Grade = M('host');
					$find_host = $Grade->where('HOST_NAMEAB = '."'$val'")->field('HOST_MAP_ID')->find();
					$host_id = $find_host['HOST_MAP_ID'];
					$host_result[$host_id] = $val;
				}
			}
			$this->assign ( 'host_result',  $host_result);
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			$this->assign ( 'GRADE_LEVEL',  C('GRADE_LEVEL'));
			$this->assign ( 'GRADE_STATUS', C('GRADE_STATUS'));
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);			
		$this->display();
	}

	//汇米 商户等级扣率通道 修改
	public function grade_edit(){
		$post = I('post');
		if($post['submit'] == "grade_edit"){
			$where = 'ID = '.$post['ID'];
			//验证
			if(empty($post['SHOP_GRADE']) || empty($post['HOST_MAP_ID']) || $post['STATUS'] == ''){
				$this->wrong("缺少必填项数据！");
			}
			$scfg_data = array(
							   'SHOP_GRADE' 	 => $post['SHOP_GRADE'],
							   'HOST_MAP_ID' 	 => $post['HOST_MAP_ID'],
							   'STATUS' 		 => $post['STATUS'],
							   'UPDATE_TIME' 	 => date('Y-m-d H:i:s',time())
							);
			$res = D($this->MGrade)->updateGrade($where , $scfg_data);
			if ($res['state']!=0) {
				$this->wrong('修改失败！');
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//判断当前状态是否符合复审操作
		$scfg_info = D($this->MGrade)->findHost_Bank('ID = "'.$id.'"');
		$find_host = D($this->MGrade)->findhost('1=1','HOST_NAMEAB');
		$host_result = array();
		foreach ($find_host as $key => $value) {
			foreach ($value as $k => $val) {
				$Grade = M('host');
				$find_host = $Grade->where('HOST_NAMEAB = '."'$val'")->field('HOST_MAP_ID')->find();
				$host_id = $find_host['HOST_MAP_ID'];
				$host_result[$host_id] = $val;
			}
		}
		$this->assign ('GRADE_LEVEL',C('GRADE_LEVEL'));
		$this->assign ('GRADE_STATUS',C('GRADE_STATUS'));
		$this->assign ('host_result',$host_result);
		$this->assign ('scfg_info',$scfg_info);
		$this->display();
	}

	//汇米 商户等级扣率通道 添加
	public function grade_add(){
		$post = I('post');
		if($post['submit'] == "grade_add"){
			$home = session('HOME');
			//验证
			if(empty($post['SHOP_GRADE']) || empty($post['HOST_MAP_ID']) || $post['STATUS'] == '' || empty($home['USER_ID']) || empty($home['USER_NAME'])){
				$this->wrong("缺少必填项数据！");
			}
			$scfg_data = array(
			    'SHOP_GRADE' 	  => $post['SHOP_GRADE'],
			    'HOST_MAP_ID' 	  => $post['HOST_MAP_ID'],
			    'STATUS' 		  => $post['STATUS'],
			    'CREATE_USERID'   => $home['USER_ID'],
			    'CREATE_USERNAME' => $home['USER_NAME'],
			    'CREATE_TIME'     => date('Y-m-d H:i:s',time())
			);
			$res = M('grade_rate');
			$res_add = $res->add($scfg_data);
			if (!$res_add) {
				$this->wrong('添加失败！');
			}
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$find_host = D($this->MGrade)->findhost('1=1','HOST_NAMEAB');
		$host_result = array();
		foreach ($find_host as $key => $value) {
			foreach ($value as $k => $val) {
				$Grade = M('host');
				$find_host = $Grade->where('HOST_NAMEAB = '."'$val'")->field('HOST_MAP_ID')->find();
				$host_id = $find_host['HOST_MAP_ID'];
				$host_result[$host_id] = $val;
			}
		}
		//判断当前状态是否符合复审操作
		$this->assign ('host_result',$host_result);
		$this->assign ('GRADE_LEVEL',C('GRADE_LEVEL'));
		$this->assign ('GRADE_STATUS',C('GRADE_STATUS'));
		$this->display();
	}

	//汇米 商户比例扣率管理 查询
	public function grade_fee(){
		$post = I('post');
		if($post['submit'] == "grade_fee"){
			$where = "0 = 0";
			//商户等级
			if($post['SHOP_GRADE'] != '') {
				$where .= " and SHOP_GRADE = '".$post['SHOP_GRADE']."'";
			}
			
			//分页
			$count = D($this->MGradefee)->countMGradefee($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MGradefee)->getMGradefeelist($where, '*', $p->firstRow.','.$p->listRows,'ID desc');
			
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
		$this->assign ( 'GRADE_LEVEL',  C('GRADE_LEVEL'));
		\Cookie::set ('_currentUrl_', 		__SELF__);			
		$this->display();
	}

	//汇米 商户比例扣率管理 添加
	public function grade_fee_add(){
		$post = I('post');
		if($post['submit'] == "grade_fee_add"){
			//验证
			if(empty($post['SHOP_GRADE']) || $post['FIR_FEE'] == '' || $post['SEC_FEE'] == '' || $post['THR_FEE'] == ''){
				$this->wrong("缺少必填项数据！");
			}
			$scfg_data = array(
			    'SHOP_GRADE'   => $post['SHOP_GRADE'],
			    'FIR_FEE' 	   => $post['FIR_FEE'],
			    'SEC_FEE' 	   => $post['SEC_FEE'],
			    'THR_FEE'      => $post['THR_FEE']
			);
			$res = M('grade_fee');
			$res_add = $res->add($scfg_data);
			if (!$res_add) {
				$this->wrong('添加失败！');
			}
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//判断当前状态是否符合复审操作	
		$this->assign ('GRADE_LEVEL',C('GRADE_LEVEL'));
		$this->display();
	}

	//汇米 商户比例扣率管理 修改
	public function grade_fee_edit(){
		$post = I('post');
		if($post['submit'] == "grade_fee_edit"){
			$where = 'ID = '.$post['ID'];
			//验证
			if(empty($post['SHOP_GRADE']) || $post['FIR_FEE'] == '' || $post['SEC_FEE'] == '' || $post['THR_FEE'] == ''){
				$this->wrong("缺少必填项数据！");
			}
			$scfg_data = array(
			    'SHOP_GRADE'   => $post['SHOP_GRADE'],
			    'FIR_FEE' 	   => $post['FIR_FEE'],
			    'SEC_FEE' 	   => $post['SEC_FEE'],
			    'THR_FEE'      => $post['THR_FEE']
			);
			$res = D($this->MGradefee)->updateGrade($where , $scfg_data);
			if ($res['state']!=0) {
				$this->wrong('修改失败！');
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		//判断当前状态是否符合复审操作
		$scfg_info = D($this->MGradefee)->findBank('ID = "'.$id.'"');
		$this->assign ('GRADE_LEVEL',C('GRADE_LEVEL'));
		$this->assign ( 'scfg_info', $scfg_info );
		$this->display();
	}

	//汇米 文本 查询
	public function text_info(){
		$post = I('post');
		if($post['submit'] == "text_info"){
			$where = "APP_CATEGORY != 5";
			//商户等级
			if($post['category'] != '') {
				$where .= " and APP_CATEGORY = '".$post['category']."'";
			}
			
			$res = M('version_info');
			//分页
			$count = $res->where($where)->count();
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = $res->where($where)->field('*')->limit($p->firstRow.','.$p->listRows)->order('ID desc')->select();
			
			$host_result = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID');
			$this->assign ('host_result',$host_result);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
			$this->assign ( 'list', 		$list );
		}
		$this->assign ( 'TEXT_CATEGORY',  C('TEXT_CATEGORY'));
		$this->assign ( 'ENABLE',  C('ENABLE'));
		$this->assign ( 'FORCE',  C('FORCE'));
		\Cookie::set ('_currentUrl_', 		__SELF__);			
		$this->display();
	}

	//汇米 文本 添加
	public function text_info_add(){
		$post = I('post');
		if($post['submit'] == "text_info_add"){
			//验证
			if(empty($post['APP_CATEGORY']) || empty($post['CONTENT']) || empty($post['CHANNEL_MAP_ID'])){
				$this->wrong("缺少必填项数据！");
			}
			$res = M('version_info');
			$check = $res->where(array('APP_CATEGORY'=>$post['APP_CATEGORY'],'CHANNEL_MAP_ID'=>$post['CHANNEL_MAP_ID']))->find();
			if ($check) {
				// $this->wrong('相关文本类别已经添加，请勿重复添加！');
			}
			$text_data = array(
			    'APP_CATEGORY'    => $post['APP_CATEGORY'],//类别
			    'CONTENT' 	   	  => $post['CONTENT'],//文本内容
			    'BEGIN_TIME'      => date('Y-m-d H:i:s',time()),//编辑时间
			    'CHANNEL_MAP_ID'  => $post['CHANNEL_MAP_ID']//渠道
			);
			$res_add = $res->add($text_data);
			if (!$res_add) {
				$this->wrong('添加失败！');
			}
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$host_result = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID');
		$this->assign ('host_result',$host_result);
		$this->assign ('TEXT_CATEGORY',C('TEXT_CATEGORY'));
		$this->display();
	}

	/*
		@param 	$mysqldb    数据表名称
				$fieldname  已知字段
				$selectname 查询字段
		@return $host_result 
 	*/
	private function get_array($mysqldb,$fieldname,$selectname){
		$adModel = M($mysqldb);
		$find_host = $adModel->where('1=1')->field($fieldname)->select();
		$host_result = array();
		foreach ($find_host as $key => $value) {
			foreach ($value as $k => $val) {
				$find_host = $adModel->where($fieldname."='$val'")->field($selectname)->find();
				$host_id = $find_host[$selectname];
				$host_result[$host_id] = $val;
			}
		}
		return $host_result;
	}

	//汇米 文本 修改
	public function text_info_edit(){
		$post = I('post');
		if($post['submit'] == "text_info_edit"){
			//验证
			if(empty($post['APP_CATEGORY']) || empty($post['CONTENT']) || empty($post['CHANNEL_MAP_ID'])){
				$this->wrong("缺少必填项数据！");
			}
			$where = 'ID = '.$post['ID'];
			$res = M('version_info');
			$select_app = $res->where($where)->find();
			if ($select_app['APP_CATEGORY'] == 3 || $select_app['APP_CATEGORY'] == 6 || $select_app['APP_CATEGORY'] == 7) {
				//APP版本传值验证
				// if(empty($post['APP_ENABLE']) || empty($post['APP_FORCE']) || empty($post['VERSION']) || empty($post['APP_URL'])){
				// 	$this->wrong("缺少必填项数据！");
				// }
				$data = array(
				    'APP_CATEGORY'     => $post['APP_CATEGORY'],
				    'CONTENT' 	   	   => $post['CONTENT'],
				    'APP_ENABLE' 	   => $post['APP_ENABLE'],
				    'APP_FORCE' 	   => $post['APP_FORCE'],
				    'CHANNEL_MAP_ID'   => $post['CHANNEL_MAP_ID'],//渠道
				    'UPDATE_TIME'      => date('Y-m-d H:i:s',time()),
				    "APP_URL"		   => $post['APP_URL'],
				    "VERSION"		   => $post['VERSION']
				);
				$res = $res->where($where)->save($data);
			}else{
				$data = array(
				    'APP_CATEGORY'     => $post['APP_CATEGORY'],
				    'CONTENT' 	   	   => $post['CONTENT'],
				    'CHANNEL_MAP_ID'   => $post['CHANNEL_MAP_ID'],//渠道
				    'UPDATE_TIME'      => date('Y-m-d H:i:s',time())
				);
				$res = $res->where($where)->save($data);
			}
			if (!$res) {
				$this->wrong('修改失败！');
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$res = M('version_info');
		//判断当前状态是否符合复审操作
		$scfg_info = $res->where('ID = "'.$id.'"')->find();
		$host_result = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID');
		$this->assign ('host_result',$host_result);
		$this->assign ('TEXT_CATEGORY',C('TEXT_CATEGORY'));
		$this->assign ( 'scfg_info', $scfg_info );
		$this->assign ( 'ENABLE',  C('ENABLE'));
		$this->assign ( 'FORCE',  C('FORCE'));
		$this->assign ( 'CONTENT', htmlspecialchars_decode($scfg_info['CONTENT']) );
		$this->display();
	}

	//CRYSTAL 2017.08.07 开通支付
	public function open_pay(){
		$post = I('post');
		if ($post['submit'] == "open_pay") {
			$shopModel = M('shop');
			$shopSZModel = M('shop_intopieces');
			$shopInfo = $shopModel->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			if(!$shopInfo){
				$this->wrong('商户不存在');
			}
			if($shopInfo['SHOP_STATUS'] !=0 ){
				$this->wrong('当前状态不可操作');
			}
			$condition = array(
				'hshop.HSHOP_STATUS' => 0, 
				'gr.STATUS' => 0, 
				'hshop.MCC_CODE' => '',
				'gr.SHOP_GRADE' => $shopInfo['SHOP_GRADE'],
				'hshop.HOST_MAP_ID' => array('exp','not in (SELECT HOST_MAP_ID FROM pai_db_jfb.a_shop_intopieces WHERE SHOP_MAP_ID = '.$post['SHOP_MAP_ID'].' AND `STATUS` = 0)')
			);
			$hshopModel = M('hshop');
			$list = $hshopModel->alias('hshop')
					->field('hshop.HSHOP_NO,hshop.KEYT,hshop.HOST_MAP_ID,h.CHANNEL')
					->join("pai_db_jfb.a_grade_rate gr on gr.HOST_MAP_ID=hshop.HOST_MAP_ID", "LEFT")
					->join("pai_db_jfb.a_host h on h.HOST_MAP_ID=hshop.HOST_MAP_ID", "LEFT")
					->where($condition)
					->select();
			foreach($list as $val){
				$si_data = array(
					'SHOP_MAP_ID' => $post['SHOP_MAP_ID'],
					'SHOP_ID1' => $val['HSHOP_NO'],
					'FLAG' => 0,
					'KEYT' => $val['KEYT'],
					'CHANNEL' => $val['CHANNEL'],
					'HOST_MAP_ID' => $val['HOST_MAP_ID']
				);
				$shopSZModel->add($si_data);
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$shopModel = M('shop');
		$shopInfo = $shopModel->where(array('SHOP_MAP_ID'=>$id))->find();
		if($shopInfo['SHOP_STATUS'] !=0 ){
			$this->wrong('当前状态不可操作');
		}
		$this->shop_show('open_pay');
	}

	//轮播图信息展示
	public function broad_info(){
		$post = I('post');
		if($post['submit'] == "broad_info"){
			$where = "APP_CATEGORY = 5";
			//商户等级
			if($post['category'] != '') {
				$where .= " and APP_CATEGORY = '".$post['category']."'";
			}
			
			$res = M('version_info');
			//分页
			$count = $res->where($where)->count();
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = $res->where($where)->field('*')->limit($p->firstRow.','.$p->listRows)->order('ID desc')->select();
			
			$host_result = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID');
			$this->assign ('host_result',$host_result);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
			$this->assign ( 'list', 		$list );
		}
		$this->assign ( 'TEXT_CATEGORY',  array('5'=>'轮播图'));
		$this->assign ( 'ENABLE',  C('ENABLE'));
		$this->assign ( 'FORCE',  C('FORCE'));
		\Cookie::set ('_currentUrl_', 		__SELF__);			
		$this->display();
	}

	//轮播图 添加
	public function broad_info_add(){
		$post = I('post');
		if($post['submit'] == "broad_info_add"){
			//验证
			if(empty($post['APP_CATEGORY']) || empty($post['content']) || empty($post['CHANNEL_MAP_ID']) || empty($post['broad_no'])){
				$this->wrong("缺少必填项数据！");
			}
			$post['content'] = str_replace("Public/","http://api.yuemanbank.com/Public/",$post['content']);
			$res = M('version_info');
			$text_data = array(
			    'APP_CATEGORY'    => $post['APP_CATEGORY'],//类别
			    'CONTENT' 	   	  => $post['content'],//文本内容
			    'BEGIN_TIME'      => date('Y-m-d H:i:s',time()),//编辑时间
			    'CHANNEL_MAP_ID'  => $post['CHANNEL_MAP_ID']//渠道
			);

			$this->wrong($pic_url);
			$res_add = $res->add($text_data);
			if (!$res_add) {
				$this->wrong('文本表添加失败！');
			}
			$ad = M('ad');
			$pic_url = 'http://api.yuemanbank.com:89/Home/TextShow/broad?channel_map_id='.$post['CHANNEL_MAP_ID'].'&id='.$res_add;
			$save_data = array('AD_URL'=>$pic_url);
			$ad_Model = $ad->where(array('AD_ID'=>$post['broad_no']))->save($save_data);
			if (!$ad_Model) {
				$this->wrong('轮播图表添加失败！');
			}
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$host_result = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID');
		$this->assign ('host_result',$host_result);
		$this->assign ('TEXT_CATEGORY',array('5'=>'轮播图'));
		$this->display();
	}

	//轮播图 修改
	public function broad_info_edit(){
		$post = I('post');
		if($post['submit'] == "broad_info_edit"){
			//验证
			if(empty($post['APP_CATEGORY']) || empty($post['CONTENT']) || empty($post['CHANNEL_MAP_ID'])){
				$this->wrong("缺少必填项数据！");
			}
			$where = 'ID = '.$post['ID'];
			$res = M('version_info');
			$data = array(
			    'APP_CATEGORY'     => $post['APP_CATEGORY'],
			    'CONTENT' 	   	   => $post['CONTENT'],
			    'CHANNEL_MAP_ID'   => $post['CHANNEL_MAP_ID'],//渠道
			    'UPDATE_TIME'      => date('Y-m-d H:i:s',time())
			);
			$res = $res->where($where)->save($data);
			if (!$res) {
				$this->wrong('修改失败！');
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$res = M('version_info');
		//判断当前状态是否符合复审操作
		$scfg_info = $res->where('ID = "'.$id.'"')->find();
		$host_result = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID');
		$this->assign ('host_result',$host_result);
		$this->assign ('TEXT_CATEGORY',array('5'=>'轮播图'));
		$this->assign ( 'scfg_info', $scfg_info );
		$this->assign ( 'ENABLE',  C('ENABLE'));
		$this->assign ( 'FORCE',  C('FORCE'));
		$this->assign ( 'CONTENT', htmlspecialchars_decode($scfg_info['CONTENT']) );
		$this->display();
	}

	//ajax获取轮播图信息
	public function ajaxgetcode(){
		$broad = I('broad_no');
		$ad_info = $this->get_ad($broad);
		echo json_encode($ad_info);
	}
    //查找包名和id号
    public function get_ad($broad){
		$adModel = M('ad');
		$find_host = $adModel->where(array('CHANNEL_MAP_ID'=>$broad))->field('AD_NAME,AD_ID')->select();
		$host = array();
		foreach ($find_host as $key => $value) {
			$id = $value['AD_ID'];
			$find_host = $adModel->where(array('CHANNEL_MAP_ID'=>$broad,'AD_ID = '.$id))->field('AD_NAME')->find();
			$find = $find_host['AD_NAME'];
			$host[$id] = $find;
		}
		return $host;
    }
	
	
	
	
	
	
	
	
	
	
	//商户角色配置管理
	public function role_config(){
		$post = I('post');
		if($post['submit'] == "role_config"){
			$where = "1=1";
			//渠道
			if($post['CHANNEL_MAP_ID']) {
				$where .= " and ch.CHANNEL_MAP_ID=".$post['CHANNEL_MAP_ID'];
			}
			//状态
			if($post['STATUS'] != '') {
				$where .= " and ro.STATUS = '".$post['STATUS']."'";
			}
			//分页
			$roleModel = M('shop_role');
			$count = $roleModel->alias('ro')
					->join('a_channel ch on ch.CHANNEL_MAP_ID=ro.CHANNEL_MAP_ID','LEFT')
					->where($where)->count();
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = $roleModel->alias('ro')
						->join('a_channel ch on ch.CHANNEL_MAP_ID=ro.CHANNEL_MAP_ID','LEFT')
						->where($where)->limit($p->firstRow.','.$p->listRows)->select();
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$channelModel = M('channel');
		$channel_list = $channelModel->where(array('CHANNEL_STATUS'=>0))->select();
		$this->assign('channel_list', $channel_list);
		$this->assign('host_status',		C('CHECK_POINT.all'));		//通道状态
		$this->assign('host_status_check',	C('CHECK_POINT.check'));	//通道状态
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display('role_config');
	}
	
	/*
	* 商户角色配置管理 添加
	**/
	public function role_config_add() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "role_config_add") {
			//验证
			if(empty($post['R_ID']) || empty($post['CHANNEL_MAP_ID']) || empty($post['R_NAME']) || empty($post['R_APPLYAMT'])){
				$this->wrong("缺少必填项数据！");
			}
			$roledata = array(
				'R_ID'				=>	$post['R_ID'],
				'CHANNEL_MAP_ID'	=>	$post['CHANNEL_MAP_ID'],
				'R_NAME'			=>	$post['R_NAME'],
				'R_APPLYAMT'		=>	$post['R_APPLYAMT']*100,
				'R_FEE'				=>	$post['R_FEE'],
				'R_AMT'				=>	$post['R_AMT']*100,
				'R_UPSAMT'			=>	$post['R_UPSAMT']*100,
				'R_UPSAMT_SEC'		=>	$post['R_UPSAMT_SEC']*100,
				'R_UPSAMT_THR'		=>	$post['R_UPSAMT_THR']*100,
				'STATUS'			=>	$post['STATUS'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'CREATE_TIME'		=>	date('YmdHis'),
				'RES'				=>	$post['RES'],
				'R_RAMT'			=>	$post['R_RAMT']*100,
			);
			$roleModel = M('shop_role');
			$role = $roleModel->where(array('CHANNEL_MAP_ID'=>$post['CHANNEL_MAP_ID'], 'R_ID'=>$post['R_ID']))->find();
			if($role){
				$this->wrong("该渠道已存在该等级");
			}
			$res_role = $roleModel->add($roledata);
			
			//判断
			if(!$res_role){
				$this->wrong('添加失败！');
			}
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$channelModel = M('channel');
		$channel_list = $channelModel->where(array('CHANNEL_STATUS'=>0))->select();
		$this->assign('channel_list', $channel_list);
		$this->display();
	}
	
	/*
	* 角色配置管理 修改
	**/
	public function role_config_edit() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "role_config_edit") {
			//验证
			if(empty($post['R_ID']) || empty($post['CHANNEL_MAP_ID']) || empty($post['R_NAME']) || empty($post['R_APPLYAMT'])){
				$this->wrong("缺少必填项数据！");
			}
			$roledata = array(
				'R_ID'				=>	$post['R_ID'],
				'CHANNEL_MAP_ID'	=>	$post['CHANNEL_MAP_ID'],
				'R_NAME'			=>	$post['R_NAME'],
				'R_APPLYAMT'		=>	$post['R_APPLYAMT']*100,
				'R_FEE'				=>	$post['R_FEE'],
				'R_AMT'				=>	$post['R_AMT']*100,
				'R_UPSAMT'			=>	$post['R_UPSAMT']*100,
				'R_UPSAMT_SEC'		=>	$post['R_UPSAMT_SEC']*100,
				'R_UPSAMT_THR'		=>	$post['R_UPSAMT_THR']*100,
				'STATUS'			=>	$post['STATUS'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'CREATE_TIME'		=>	date('YmdHis'),
				'RES'				=>	$post['RES'],
				'R_RAMT'			=>	$post['R_RAMT']*100,
			);
			$roleModel = M('shop_role');
			$role = $roleModel->where(array('CHANNEL_MAP_ID'=>$post['CHANNEL_MAP_ID'], 'R_ID'=>$post['R_ID']))->find();
			if($role['ID']!=$post['ID']){
				$this->wrong("该渠道已存在该等级");
			}
			$res_role = $roleModel->where(array('ID'=>$post['ID']))->save($roledata);
			if(!$res_role){
				$this->wrong("修改失败！");
			}																//NEWS_FLAG是状态
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$roleModel = M('shop_role');
		$info  = $roleModel->alias('ro')
				->join('a_channel ch on ch.CHANNEL_MAP_ID=ro.CHANNEL_MAP_ID','LEFT')
				->where(array('ID'=>$id))->find();
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}		
		
		$channelModel = M('channel');
		$channel_list = $channelModel->where(array('CHANNEL_STATUS'=>0))->select();
		$this->assign('channel_list', $channel_list);
		$this->assign('info', 				$info);
		$this->display('role_config_add');
	}

	//商户进件
	public function merch_add(){
		$post = I('post');
		if ($post['submit'] == "merch_add") {
			$shopModel = M('shop');
			$shopSZModel = M('shop_intopieces');
			//获取商户基本信息
			$shopInfo = $shopModel
						->alias('shop')
						->join('LEFT JOIN a_channel channel on shop.CHANNEL_MAP_ID = channel.CHANNEL_MAP_ID')
						->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))
						->field('channel.CHANNEL_NAMEAB,shop.SHOP_NAMEABCN,shop.ADDRESS,shop.MOBILE,shop.EMAIL,shop.SHOP_OPENTIME,shop.SHOP_CLOSETIME,shop.SHOP_KIND,shop.TEL,shop.CITY_NAME,channel.KEYT,channel.HOST_MAP_ID')
						->find();
			$key = $shopInfo['KEYT'];
			if(!$shopInfo){
				$this->wrong('商户不存在');
			}
			if($shopInfo['SHOP_STATUS'] !=0 ){
				$this->wrong('当前状态不可操作');
			}
			$shopSZCon['SHOP_MAP_ID'] = $post['SHOP_MAP_ID'];
			$shopSZCon['CHANNEL'] = 0;
			$shopSZCon['STATUS'] = 0;
			$shopSZInfo = $shopSZModel->where($shopSZCon)->find();
			if($shopSZInfo){
				$this->wrong('商户已进件');
			}
			$host_map_id = $shopInfo['HOST_MAP_ID'];
			$host_map_id = 0;
			switch ($host_map_id) {
				case 10:
					//ISV进件接口
					//========================   2017.09.12 CRYSTAL  START  ==================================
					//获取商户证照信息
					$scert_info = D($this->MScert)->findScert("SHOP_MAP_ID='".$post['SHOP_MAP_ID']."'");
					//获取商户权限信息
					$sauth_info = D($this->MSauth)->findSauth("SHOP_MAP_ID='".$post['SHOP_MAP_ID']."'");
					//获取商户结算信息
					$smdr_info  = M('smdr')->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID'],'PAY_TYPE'=>5))->find();
					//获取商户银行帐户信息
					$sbact_info = D($this->MSbact)->findSbact("SHOP_MAP_ID='".$post['SHOP_MAP_ID']."'");
					//获取商户代扣银行帐户信息
					$sdkb_info  = D($this->MSdkb)->findSdkb("SHOP_MAP_ID='".$post['SHOP_MAP_ID']."'");
					$data = array(
						"sp_id"=>$shopInfo['CHANNEL_NAMEAB'],//机构号
						"per_fee"=>$smdr_info['JFB_PER_FEE'],
						"arena_name"=>$shopInfo['SHOP_NAMEABCN'],
						"arena_address"=>$shopInfo['ADDRESS'],
						"arena_contacts"=>$scert_info['LP_NAME'],
						"arena_phone"=>$shopInfo['MOBILE'],
						"arena_email"=>$shopInfo['EMAIL'],
						"corporate_name"=>$shopInfo['CHANNEL_NAMEAB'],
						"corporate_address"=>$scert_info['REG_ADDR'],
						"corporate_certno"=>$scert_info['REG_ID'],
						"corporate_business_scope"=>$shopInfo['MCC_CODE'],
						"corporate_term"=>$scert_info['REG_EXP'] == '99999999'?'长期':'',
		                "corporate_term_start"=>$scert_info['CORPORATE_TERM_START'],
		                "corporate_term_end"=>$scert_info['CORPORATE_TERM_END'],
						"business_scope_type"=>$shopInfo['SHOP_KIND'],
						"office_telephone"=>$shopInfo['TEL'],
						"sign_ali_account"=>$shopInfo['CHANNEL_NAMEAB'],
						"cert_people_name"=>$scert_info['LP_NAME'],
						"bank_account_certno"=>$scert_info['LP_ID'],
						"cert_term_validity_start"=>$scert_info['CERT_TERM_VALIDITY_START'],
						"cert_term_validity_end"=>$scert_info['CERT_TERM_VALIDITY_END'],
						"bank_account_type"=>$sbact_info['SHOP_BANK_FLAG'] == '0' ? '对公':'对私',
						"bank_account_name"=>$sbact_info['SHOP_BANK_FLAG'] == '0' ?$sbact_info['BANKACCT_NAME1']:$sbact_info['BANKACCT_NAME2'],
						"bank_account_no"=>$sbact_info['SHOP_BANK_FLAG'] == '0' ?$sbact_info['BANKACCT_NO1']:$sbact_info['BANKACCT_NO2'],
						"bank_name"=>$sbact_info['SHOP_BANK_FLAG'] == '0' ? $sbact_info['BANK_NAME1']:$sbact_info['BANKBRA_NAME1'],
						"bank_name_sub"=>$sbact_info['SHOP_BANK_FLAG'] == '0' ? $sbact_info['BANK_NAME1']:$sbact_info['BANKBRA_NAME1'],
						"bank_city"=>$shopInfo['CITY_NAME'],
						"img_idcard_a"=>$scert_info['LP_D_PHOTO'],
						"img_idcard_b"=>$scert_info['LP_R_PHOTO'],
						"img_bankcard_a"=>$scert_info['BANK_D_PHOTO'],
						"img_license"=>$scert_info['REGID_PHOTO'],
						"img_logo"=>$scert_info['OFFICE_PHOTO1'],
						"img_indoor_a"=>$scert_info['OFFICE_PHOTO2'],
						"img_indoor_b"=>$scert_info['OFFICE_PHOTO3'],
						"shop_map_id"=>$post['SHOP_MAP_ID']
					);
					// $this->wrong(json_encode($data));
					$checksign = szSign($data,$key);
					$data['sign'] = $checksign;
					$url = 'http://172.19.220.31:89/index.php/Home/Open/save_info';
					$data = json_encode($data);
					$return = doPostArr($url,$data);
		            $result = json_decode($return,true);
		            if ($result['status'] != 200) {
		            	$this->wrong($result['message']?json_encode($result['message']):'进件失败');
		            }
					//========================   2017.09.12 CRYSTAL  END  ====================================
					$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
					break;
				
				case 15:
					//付临门进件接口
					$shop_map_id = $post['SHOP_MAP_ID'];
					$nonce_str = time();
					$para = array(
							"shop_map_id"=>$shop_map_id,
							"nonce_str"=>$nonce_str
						);
					$key = 'C55ED90531F97512252224039DD3CEB9';
					$para['sign'] = szSign($para,$key);
					$para = json_encode($para);
					$url = 'http://172.19.220.31:89/Home/FreePay/shop_reg';
					$result = json_decode(doPostArr($url,$para),true);
					if ($result['status'] != 200) {
						$this->wrong($result['message']?json_encode($result['message']):'进件失败');
					}
					$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
					break;

				case 0:
					//上福进件接口
					$shop_map_id = $post['SHOP_MAP_ID'];
					$nonce_str = '88'.time();
					$sbactModel = M('sbact');
					$sbact = $sbactModel->field('SHOP_BANK_FLAG')->where(array('SHOP_MAP_ID'=>$shop_map_id))->find();
					if ($sbact['SHOP_BANK_FLAG'] == 0) {
						$type = '2';//进件类型 type 1 个人 2 商户
					}elseif ($sbact['SHOP_BANK_FLAG'] == 1) {
						$type = '1';
					}else{
						$this->wrong('商户信息不完整');
					}
					$para = array(
							"shop_map_id"=>$shop_map_id,
							"nonce_str"=>$nonce_str,
							"type"=>$type,
						);
					$key = 'C55ED90531F97512252224039DD3CEB9';
					$para['sign'] = szSign($para,$key);
					$para = json_encode($para);
					// Add_LOG('ShangfuPay', __LINE__ . ' shop|后台进件 para ：'.$para);
					$url = 'http://172.19.220.31:89/Home/ShangfuPay/shop_reg';
					$result = json_decode(doPostArr($url,$para),true);
					// Add_LOG('ShangfuPay', __LINE__ . ' shop|后台进件 result ：'.json_encode($result));
					if ($result['status'] != 200) {
						$this->wrong($result['message']?$result['message']:'进件失败');
					}
					$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
					break;

				default:
					$this->wrong('HOST_MAP_ID异常');
					break;
			}
			
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$shopModel = M('shop');
		$shopInfo = $shopModel->where(array('SHOP_MAP_ID'=>$id))->find();
		if($shopInfo['SHOP_STATUS'] !=0 ){
			$this->wrong('当前状态不可操作');
		}
		//获取商户基本信息
		$shop_info  = D($this->MShop)->findmoreNewShop("SHOP_MAP_ID='".$id."'");
		//获取商户证照信息
		$scert_info = D($this->MScert)->findScert("SHOP_MAP_ID='".$id."'");
		//获取商户权限信息
		$sauth_info = D($this->MSauth)->findSauth("SHOP_MAP_ID='".$id."'");
		//获取商户结算信息
		$smdr_info  = D($this->MSmdr)->getSmdrfind("sm.SHOP_MAP_ID='".$id."' and sm.SHOP_STATUS = 0", 'sm.SETTLE_T,sm.PAY_TYPE, sm.JFB_PER_FEE, sm.JFB_FIX_FEE, sm.PER_FEE, sm.FIX_FEE','',"locate(sm.PAY_TYPE,'5,0,4')");
		//获取商户银行帐户信息
		$sbact_info = D($this->MSbact)->findSbact("SHOP_MAP_ID='".$id."'");
		//获取商户代扣银行帐户信息
		$sdkb_info  = D($this->MSdkb)->findSdkb("SHOP_MAP_ID='".$id."'");
		//获取商户风险级别帐户信息
		//$srisk_info = D($this->MSrisk)->findSrisk("SHOP_MAP_ID='".$id."'");
		//获取商户其他配置信息
		$scfg_info = D($this->MScfg)->findScfg("SHOP_MAP_ID='".$id."'");
		if(empty($shop_info) || empty($scert_info) || empty($smdr_info)){
			$this->wrong("参数数据出错！");
		}
		$scert_info['OTHER'] = explode(',', $scert_info['OTHER_PHOTOS']);
		$t_list = D('MTrans')->getTranslist('TRANS_FLAG1 = 1 and TRANS_STATUS = 0','TRANS_MAP_ID,TRANS_NAME');
		$this->assign ('t_list', 	$t_list);							//交易开通
		$this->assign ('check_status',	 	 C('CHECK_POINT.check'));	//审核状态
		$this->assign ('bank_flag_redio',	 C('PARTNER_BANK_FLAG'));	//结算标志
		$this->assign ('settle_t_unit',		 C('SETTLE_T_UNIT'));		//结算周期
		$this->assign ('auth_trans_checked', str_split($sauth_info['AUTH_TRANS_MAP']));	//交易开通
		$this->assign ('shop_info', 	$shop_info);	//基础信息
		$this->assign ('scert_info', 	$scert_info);	//证照信息
		$this->assign ('sauth_info', 	$sauth_info);	//权限信息
		$this->assign ('smdr_info', 	$smdr_info);	//结算方式
		$this->assign ('sbact_info', 	$sbact_info);	//银行帐户
		$this->assign ('sdkb_info', 	$sdkb_info);	//代扣银行帐户
		//$this->assign ('srisk_info', 	$srisk_info);	//风险级别
		$this->assign ('scfg_info', 	$scfg_info);	//其他配置
		$this->display('merch_add');
	}

	/*消息推送功能*/
	public function push(){
		$post = I('post');
		if ($post['submit'] == 'push') {
			if (empty($post['type']) || empty($post['appPackage']) || empty($post['content']) || empty($post['title']) || empty($post['alert'])) {
				$this->wrong('缺少参数！');
			}
			$url = 'http://172.19.220.31:90/kakaloan/api/jpush/send';
			//type类型：1:发送广播，2:发送通知。
			//flag类型：0:全局发送广播，1:根据SHOP_MAP_ID单个发送通知，针对审核，2：SHOP_MAP_ID商户，普通推送
			$data = array(
				'content'=>$post['content'],//推送内容
				'type'=>$post['type'],//推送类型
				'title'=>$post['title'],//标题
				'messageId'=>'',//checkID
				'appPackage'=>$post['appPackage'],//包名
				'alert'=>$post['alert']//推送栏内容
			);
			$result = json_decode(doPostArr($url,$data),true);
			if ($result['resCode'] != 0) {
				$this->wrong($result['resMsg']);
			}
			$this->right($result['resMsg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//推送类型
		$type = array("6"=>"平台通知");
		$home = session('HOME');
		//包名（区分汇才和艾刷）
		$appPackage = array("aishua"=>"艾刷","huicai"=>"汇财");
		$this->assign('appPackage',$appPackage);
		$this->assign('type',$type);
		$this->assign('home',$home);
		$this->display();
	}
}
