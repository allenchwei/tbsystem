<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @ljf  商户管理
// +----------------------------------------------------------------------
class HuiCaiController extends HomeController {
	
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
		$this->MHuiCai  = 'MHuiCai';
		$this->MTrans   = 'MTrans';
		$this->TTrace   = 'TTrace';
		$this->MHost 	= 'MHost';
		$this->TJfbls 	= 'TJfbls';
		$this->TKfls 	= 'TKfls';
	}

	/*
	* 商户管理 列表
	**/
	public function HuiCai(){
		$home = session('HOME');
		$post = I('post');

		if ($post['submit'] == 'HuiCai') {
			$where = "1=1 and CHANNEL_MAP_ID = 7";
			//状态
			if($post['SHOP_STATUS'] != '') {
				$where .= " and a_shop.SHOP_STATUS = '".$post['SHOP_STATUS']."'";
			}
			//商户ID
			if($post['SHOP_MAP_ID'] != '') {
				$where .= " and a_shop.SHOP_MAP_ID = '".$post['SHOP_MAP_ID']."'";
			}
			//商户身份证
			if($post['LP_ID'] != '') {
				$where .= " and a_shop.LP_ID = '".$post['LP_ID']."'";
			}
			if (strlen($post['MOBILE']) == 11) {
				$this->wrong('手机号码长度不正确');
			}
			//商户手机号
			if($post['MOBILE']) {
				$where .= " and a_shop.MOBILE = '".$post['MOBILE']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and a_shop.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}

			$user_id = $home["USER_ID"];
			$userModel = M('user');
			$userInfo = $userModel->where(array('USER_ID'=>$user_id))->find();
			if($userInfo['CHANNEL_MAP_ID']>0){
				$where .= " and a_shop.CHANNEL_MAP_ID=".$userInfo['CHANNEL_MAP_ID'];
			}

			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$shopModel = M('shop');
			$count = $shopModel->where($where)->count();
			$list  = D($this->MHuiCai)->getNewShoplist($where, '*', $fiRow.','.$liRow, 'SHOP_MAP_ID DESC');
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
		$this->assign('home',				$home);
		$this->assign('shop_status',		C('CHECK_POINT.all'));		//所有状态
		$this->assign('shop_status_all',	C('CHECK_POINT.all2'));		//所有状态																   
		$this->assign('shop_status_check',	C('CHECK_POINT.check'));	//部分状态
		//1:申请受理中
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display();
	}

	public function huicai_show($tpl='huicai_show'){
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
		$shopModel = M('shop');
		$shop = $shopModel->where(array('SHOP_MAP_ID'=>$id))->field('RECOMMEND_MAP_ID')->find();
		$recomd = $shop['RECOMMEND_MAP_ID'];
		//推荐人ID
		$shop_id = $shopModel->where(array('SHOP_MAP_ID'=>$recomd))->field('SHOP_MAP_ID,SHOP_NAME,MOBILE,R_ID')->find();
		//发展人
		$shop_faz = $shopModel->where(array('RECOMMEND_MAP_ID'=>$id))->field('SHOP_MAP_ID,SHOP_NAME')->select();
		$this->assign('R_ID',           C('R_ID'));
		$this->assign('shop',		    $shop_id);//推荐人信息
		$this->assign('shop_faz',		$shop_faz);//发展人
		$this->display($tpl);
	}

	public function huicai_trace(){
		$home = session('HOME');
		$post = I('post');
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
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
				'IS_SHARE_FEE'		=>	I('IS_SHARE_FEE'),
				'CHANNEL_MAP_ID'	=>	I('CHANNEL_MAP_ID'),
				'VIP_CARDNO'		=>	I('VIP_CARDNO'),
				'SOURCE'		=>	I('SOURCE'),
			);
			$ajax_soplv = array(
				'bid'				=>	I('BRANCH_MAP_ID'),
				'pid'				=>	I('PARTNER_MAP_ID'),			
			);
		}
		//===结束===
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "huicai_trace"){
			$mdata = array(
	           "serviceCode"=>C('SERVICECODE'),
	           "name"=>"常伟",
	           "idNumber"=>"321283199504040232",
	        );
	        $rdata = initBankParams($mdata);
	        // $rt = json_decode(reqApi($rdata),true);
	        $rt = reqApi($rdata);
	        $this->wrong($rt);

			if($post['IS_SHARE_FEE']) {
				$flag = 't.';
			}
			$where = "1=1";
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and ".$flag."SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and ".$flag."SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}
			//流水归属
			//===优化统计===
			$getlevel = $ajax == 'loading' ? $ajax_soplv : filter_data('plv');	//列表查询
			//===结束=======
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['IS_SHARE_FEE']) {
				if($post['BRANCH_MAP_ID']){
					$where .= " and (j.ISS_BRANCH_MAP_ID2  = '".$post['BRANCH_MAP_ID']."' OR j.ACQ_BRANCH_MAP_ID2  = '".$post['BRANCH_MAP_ID']."')";
				}
				if($post['PARTNER_MAP_ID']){
					$where .= " and (j.ISS_PARTNER_MAP_ID1 = '".$post['PARTNER_MAP_ID']."' OR 
								j.ISS_PARTNER_MAP_ID2 = '".$post['PARTNER_MAP_ID']."' OR 
								j.ISS_PARTNER_MAP_ID3 = '".$post['PARTNER_MAP_ID']."' OR 
								j.VIP_PARTNER_MAP_ID1 = '".$post['PARTNER_MAP_ID']."' OR 
								j.VIP_PARTNER_MAP_ID2 = '".$post['PARTNER_MAP_ID']."' OR 
								j.ACQ_PARTNER_MAP_ID1 = '".$post['PARTNER_MAP_ID']."' OR 
								j.ACQ_PARTNER_MAP_ID2 = '".$post['PARTNER_MAP_ID']."' OR 
								j.PARTNER_MAP_ID3A    = '".$post['PARTNER_MAP_ID']."' OR 
								j.PARTNER_MAP_ID3B    = '".$post['PARTNER_MAP_ID']."' )";
				}
			}else{
				if($post['BRANCH_MAP_ID']){
					$where .= " and (".$flag."SBRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."' or ".$flag."VBRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."')";
				}
				if($post['PARTNER_MAP_ID']){
					$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
					$where .= " and (".$flag."SPARTNER_MAP_ID in (".$pids.") or ".$flag."VPARTNER_MAP_ID in (".$pids."))";
				}
			}			
			//交易类型
			if($post['TRANS_SUBID']) {
				$where .= " and ".$flag."TRANS_SUBID = '".$post['TRANS_SUBID']."'";
			}
			//处理结果
			if($post['TRACE_STATUS'] != '') {
				$where .= " and ".$flag."TRACE_STATUS = '".$post['TRACE_STATUS']."'";
			}
			//交易金额	开始
			if($post['TRANS_AMT_A']) {
				$where .= " and ".$flag."TRANS_AMT >= '".setMoney($post['TRANS_AMT_A'], '2')."'";
			}
			//交易金额	结束
			if($post['TRANS_AMT_B']) {
				$where .= " and ".$flag."TRANS_AMT <= '".setMoney($post['TRANS_AMT_B'], '2')."'";
			}
			//支付通道
			if($post['HOST_MAP_ID']) {
				$where .= " and ".$flag."HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
			}
			//商户名称
			if($post['SHOP_NAMEAB']) {
				$where .= " and ".$flag."SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
			}
			//终端号
			if($post['POS_NO']) {
				$where .= " and ".$flag."POS_NO = '".$post['POS_NO']."'";
			}
			//流水号
			if($post['POS_TRACE']) {
				$where .= " and ".$flag."POS_TRACE = '".$post['POS_TRACE']."'";
			}
			//第三方渠道
			if($post['CHANNEL_MAP_ID'] != '') {
				$where .= " and ".$flag."CHANNEL_MAP_ID = '".$post['CHANNEL_MAP_ID']."'";
			}
			//会员卡号
			if($post['VIP_CARDNO']) {
				$where .= " and ".$flag."VIP_CARDNO = '".$post['VIP_CARDNO']."'";
			}
			//交易流水号
			if($post['ORDER_NO']) {
				$where .= " and ".$flag."ORDER_NO = '".$post['ORDER_NO']."'";
			}
			
			//来源
			// if($post['SOURCE']) {
			$where .= " and ".$flag."SOURCE = '".$post['SOURCE']."'";
			// }
					
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			
			if($home['HOST_MAP_ID']>0){
				$where .= " and ".$flag."SHOP_NO in (select SHOP_NO from pai_db_jfb.a_shopppp where HOST_MAP_ID=".$home['HOST_MAP_ID'].")";
			}
			
			if($home['CHANNEL_MAP_ID']>0){
				$where .= " and ".$flag."CHANNEL_MAP_ID=".$home['CHANNEL_MAP_ID'];
			}
			
			//是否参与分润
			if($post['IS_SHARE_FEE']) {
				$where .= " AND t.TRACE_RETCODE='00' AND t.TRACE_REVERFLAG=0 AND t.TRANS_AMT>0 AND j.JFB_FEE>0 AND t.TRACE_VOIDFLAG = 0 AND t.TRACE_REFUNDFLAG = 0";
				//交易类型（现金或银行卡）
				if(empty($post['TRANS_SUBID'])) {
					$where .= " AND t.TRANS_SUBID IN (43, 44, 39, 31, 32, 33, 38)";
				}
				$Model = M('', DB_PREFIX_TRA, DB_DSN_TRA);
				//===优化统计===
				if($ajax == 'loading'){
					//统计总条数
					$count_sql = "select count(t.TRACE_ID) as TOTAL FROM T_JFBLS j LEFT JOIN t_trace t ON(t.SYSTEM_REF = j.SYSTEM_REF) WHERE ".$where;
					$num 	   = $Model->query($count_sql);
					$count     = $num[0]['TOTAL'];
					//统计
					$total_sql = "select sum(t.TRANS_AMT) as AMT FROM T_JFBLS j LEFT JOIN t_trace t ON(t.SYSTEM_REF = j.SYSTEM_REF)	WHERE ".$where;
					$total     = $Model->query($total_sql);
					$amt       = $total[0]['AMT'];					
					$resdata = array(
						'count'	=>	$count,
						'amt'	=>	setMoney($amt, 2, 2),
					);
					$this->ajaxReturn($resdata);
				}
				//===结束=======
   				//获取列表数据
   				$list_sql = "select t.* FROM T_JFBLS j LEFT JOIN t_trace t ON(t.SYSTEM_REF = j.SYSTEM_REF) WHERE ".$where." ORDER BY t.SYSTEM_DATE desc, t.SYSTEM_TIME desc limit ".$fiRow.','.$liRow;
   				$list     = $Model->query($list_sql);  				
			}else{
				//===优化统计===
				if($ajax == 'loading'){
					$count = D($this->TTrace)->countNewsTrace($where);
					$data  = D($this->TTrace)->findTrace($where, 'sum(TRANS_AMT) as AMT');
					$resdata = array(
						'count'	=>	$count,
						'amt'	=>	setMoney($data['AMT'], 2, 2),
					);
					$this->ajaxReturn($resdata);
				}
				//===结束=======
				$list  = D($this->TTrace)->getNewsTracelist($where, '*', $fiRow.','.$liRow);
			var_dump($where);
			var_dump($list);
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
		$this->assign ( 'postdata', 	$post );
		
		//Excel导出参数
		unset($post['submit']);
		$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		
		//交易列表	筛选
		$trans_list   = D($this->MTrans)->getTranslist("TRANS_FLAG2 = 1", 'TRANS_SUBID,TRANS_NAME');
		$where = "HOST_STATUS = 0";
		if($home['HOST_MAP_ID']>0){
			$where .= " and HOST_MAP_ID=".$home['HOST_MAP_ID'];
		}
		$channel_where = "CHANNEL_STATUS = 0";
		if($home['CHANNEL_MAP_ID']>0){
			$channel_where .= " and CHANNEL_MAP_ID=".$home['CHANNEL_MAP_ID'];
		}
		//通道列表
		$host_list 	  = D($this->MHost)->getHostlist($where, 'HOST_MAP_ID,HOST_NAME');
		//渠道列表
		$channel_list = D($this->MChannel)->getChannellist($channel_where, 'CHANNEL_MAP_ID,CHANNEL_NAME');
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
		$this->assign('channel_list', 		$channel_list );
		$this->assign('timedata', 			$timedata);
		$this->assign('home', 				$home);
		$this->assign('trace_status', 		C('TRACE_STATUS') );	//流水标志
		$this->assign('fee_status', 		C('FEE_STATUS') );		//超扣标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
}