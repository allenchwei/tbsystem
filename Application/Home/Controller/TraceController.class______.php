<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  流水
// +----------------------------------------------------------------------
class TraceController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->TTrace 		= 'TTrace';
		$this->MTrans 		= 'MTrans';
		$this->MHost 		= 'MHost';
		$this->TJfbls 		= 'TJfbls';
		$this->TKfls 		= 'TKfls';
		$this->MExcel 		= 'MExcel';
		$this->MReg 		= 'MReg';
		$this->MPartner 	= 'MPartner';
		$this->MChannel	 	= 'MChannel';
	}
	
	/*
	* 交易流水查询
	**/
	public function trace() {
		$post = I('post');
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "trace"){
			$where = "SYSTEM_REF != ''";	//FEE_STATUS 0正常1超扣
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}
			//流水归属
			$getlevel = filter_data('plv');	//列表查询
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and (SBRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."' or VBRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."')";
			}
			if($post['PARTNER_MAP_ID']){
				$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
				$where .= " and (SPARTNER_MAP_ID in (".$pids.") or VPARTNER_MAP_ID in (".$pids."))";
			}
			
			//交易类型
			if($post['TRANS_SUBID']) {
				$where .= " and TRANS_SUBID = '".$post['TRANS_SUBID']."'";
			}
			//处理结果
			if($post['TRACE_STATUS'] != '') {
				$where .= " and TRACE_STATUS = '".$post['TRACE_STATUS']."'";
			}
			//交易金额	开始
			if($post['TRANS_AMT_A']) {
				$where .= " and TRANS_AMT >= '".setMoney($post['TRANS_AMT_A'], '2')."'";
			}
			//交易金额	结束
			if($post['TRANS_AMT_B']) {
				$where .= " and TRANS_AMT <= '".setMoney($post['TRANS_AMT_B'], '2')."'";
			}
			//支付通道
			if($post['HOST_MAP_ID']) {
				$where .= " and HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
			}
			//商户名称
			if($post['SHOP_NAMEAB']) {
				$where .= " and SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
			}
			//终端号
			if($post['POS_NO']) {
				$where .= " and POS_NO like '".$post['POS_NO']."%'";
			}
			//流水号
			if($post['POS_TRACE']) {
				$where .= " and POS_TRACE like '".$post['POS_TRACE']."%'";
			}
			//第三方渠道
			if($post['CHANNEL_MAP_ID'] != '') {
				$where .= " and CHANNEL_MAP_ID = '".$post['CHANNEL_MAP_ID']."'";
			}
			//分页
			$count = D($this->TTrace)->countNewsTrace($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TTrace)->getNewsTracelist($where, '*', $p->firstRow.','.$p->listRows);
			$data  = D($this->TTrace)->findTrace($where, 'sum(TRANS_AMT) as AMT');
			
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'list', 		$list );
			$this->assign ( 'amt', 			$data['AMT'] );
		}
		$this->assign ( 'postdata', 	$post );
		
		//Excel导出参数
		unset($post['submit']);
		$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		
		//交易列表	筛选
		$trans_list   = D($this->MTrans)->getTranslist("TRANS_FLAG2 = 1", 'TRANS_SUBID,TRANS_NAME');
		//通道列表
		$host_list 	  = D($this->MHost)->getHostlist("HOST_STATUS = 0", 'HOST_MAP_ID,HOST_NAME');
		//渠道列表
		$channel_list = D($this->MChannel)->getChannellist("CHANNEL_STATUS = 0", 'CHANNEL_MAP_ID,CHANNEL_NAME');
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
		$this->assign('trace_status', 		C('TRACE_STATUS') );	//流水标志
		$this->assign('fee_status', 		C('FEE_STATUS') );		//超扣标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 交易流水查询	详情
	**/
	public function trace_show() {
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
		$this->display();
	}
	/*
	* 交易流水查询	导出
	**/
	public function trace_export() {
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
		$where = "SYSTEM_REF != ''";	//FEE_STATUS 0正常1超扣
		//交易日期	开始
		if($post['SYSTEM_DATE_A']) {
			$where .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
		}
		//交易日期	结束
		if($post['SYSTEM_DATE_B']) {
			$where .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
		}
		//流水归属
		if($post['BRANCH_MAP_ID']){
			$where .= " and (SBRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."' or VBRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."')";
		}
		if($post['PARTNER_MAP_ID']){
			$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
			$where .= " and (SPARTNER_MAP_ID in (".$pids.") or VPARTNER_MAP_ID in (".$pids."))";
		}			
		//交易类型
		if($post['TRANS_SUBID']) {
			$where .= " and TRANS_SUBID = '".$post['TRANS_SUBID']."'";
		}
		//处理结果
		if($post['TRACE_STATUS'] != '') {
			$where .= " and TRACE_STATUS = '".$post['TRACE_STATUS']."'";
		}
		//交易金额	开始
		if($post['TRANS_AMT_A']) {
			$where .= " and TRANS_AMT >= '".setMoney($post['TRANS_AMT_A'], '2')."'";
		}
		//交易金额	结束
		if($post['TRANS_AMT_B']) {
			$where .= " and TRANS_AMT <= '".setMoney($post['TRANS_AMT_B'], '2')."'";
		}
		//支付通道
		if($post['HOST_MAP_ID']) {
			$where .= " and HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
		}
		//商户名称
		if($post['SHOP_NAMEAB']) {
			$where .= " and SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
		}
		//终端号
		if($post['POS_NO']) {
			$where .= " and POS_NO like '".$post['POS_NO']."%'";
		}
		//流水号
		if($post['POS_TRACE']) {
			$where .= " and POS_TRACE like '".$post['POS_TRACE']."%'";
		}
		
		//计算
		$count   = D($this->TTrace)->countNewsTrace($where);
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
			$list = D($this->TTrace)->getNewsTracelist($where, '*', $bRow.','.$eRow);
					
			//导出操作
			$xlsname = '交易流水文件('.($p+1).')';
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
			
			
			/* $twhere  = "t.TRANS_SUBID != '33' and t.TRANS_SUBID != '533' and t.TRANS_SUBID != '733'";
			$twhere .= " and t.SYSTEM_DATE >= '".date('Ymd',strtotime('2016-04-01'))."'";
			$twhere .= " and t.SYSTEM_DATE <= '".date('Ymd',strtotime('2016-05-31'))."'";
			$tfield  = 't.SHOP_NO,t.SHOP_NAMEAB,t.ORDER_NO,t.SYSTEM_DATE,t.TRANS_AMT,t.OP_FEE,j.PLAT_FEE,j.CON_FEE,t.VIP_FLAG,t.TRANS_NAME';
			$tlist   = D($this->TTrace)->getTracelist($twhere, $tfield, '');
			
			//导出操作
			$xlsname = '4、5月交易流水';
			$xlscell = array(
				array('SHOP_NO',		'商户编号'),
				array('SHOP_NAMEAB',	'商户简称'),
				array('ORDER_NO',		'订单号'),
				array('SYSTEM_DATE',	'日期'),
				array('TRANS_AMT',		'订单消费金额'),		
				array('OP_FEE',			'平台收单收益'),
				array('PLAT_FEE',		'积分宝平台收益'),
				array('CON_FEE',		'积分宝个人收益'),
				array('VIP_FLAG',		'会员类型'),
				array('TRANS_NAME',		'交易类型'),
			);		
			$xlsarray = array();
			foreach($tlist as $val){
				$xlsarray[] = array(				
					'SHOP_NO'		=>	$val['SHOP_NO']."\t",
					'SHOP_NAMEAB'	=>	$val['SHOP_NAMEAB'],
					'ORDER_NO'		=>	$val['ORDER_NO']."\t",
					'SYSTEM_DATE'	=>	$val['SYSTEM_DATE']."\t",
					'TRANS_AMT'		=>	setMoney($val['TRANS_AMT'], 2, 2),
					'OP_FEE'		=>	setMoney($val['OP_FEE'], 2, 2),
					'PLAT_FEE'		=>	setMoney($val['PLAT_FEE'], 2, 2),
					'CON_FEE'		=>	setMoney($val['CON_FEE'], 2, 2),
					'VIP_FLAG'		=>	C('VIP_FLAG')[$val['VIP_FLAG']],
					'TRANS_NAME'	=>	$val['TRANS_NAME'],
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit; */
		}
		
		$this->display('Public/export');
	}
	
	
	
	
	
	
	/*
	* 现金消费交易流水查询
	**/
	public function xjtrace() {
		$post = I('post');
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "xjtrace"){
			$where = "FEE_STATUS=0 and TRACE_STATUS=0 and TRANS_SUBID=33 and TRACE_VOIDFLAG=0 and TRACE_REVERFLAG=0 and TRACE_REFUNDFLAG!=1";	//FEE_STATUS 0正常1超扣
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}
			//流水归属
			$getlevel = filter_data('plv');	//列表查询
			$post['SBRANCH_MAP_ID']  = $getlevel['bid'];
			$post['SPARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['SBRANCH_MAP_ID']){
				$where .= " and SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
			}
			if($post['SPARTNER_MAP_ID']){
				$pids = get_plv_childs($post['SPARTNER_MAP_ID'], 1);
				$where .= " and SPARTNER_MAP_ID in (".$pids.")";
			}
			
			//交易金额	开始
			if($post['TRANS_AMT_A']) {
				$where .= " and TRANS_AMT >= '".setMoney($post['TRANS_AMT_A'], '2')."'";
			}
			//交易金额	结束
			if($post['TRANS_AMT_B']) {
				$where .= " and TRANS_AMT <= '".setMoney($post['TRANS_AMT_B'], '2')."'";
			}
			//会员卡号
			if($post['VIP_CARDNO']){
				$where .= " and VIP_CARDNO like '".$post['VIP_CARDNO']."%'";
			}
			//支付通道
			if($post['HOST_MAP_ID']) {
				$where .= " and HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
			}
			//商户名称
			if($post['SHOP_NAMEAB']) {
				$where .= " and SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
			}
			//终端号
			if($post['POS_NO']) {
				$where .= " and POS_NO like '".$post['POS_NO']."%'";
			}
			//流水号
			if($post['POS_TRACE']) {
				$where .= " and POS_TRACE like '".$post['POS_TRACE']."%'";
			}
			//分页
			$count = D($this->TTrace)->countNewsTrace($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TTrace)->getNewsTracelist($where, '*', $p->firstRow.','.$p->listRows);
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
		$this->assign('host_list', 			$host_list );
		$this->assign('timedata', 			$timedata);
		$this->assign('trace_status', 		C('TRACE_STATUS') );	//流水标志
		$this->assign('fee_status', 		C('FEE_STATUS') );		//超扣标志
		$this->assign('trace_refundflag',	C('TRACE_REFUNDFLAG') );//撤销确认
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 现金消费交易流水查询	撤销申请
	**/
	public function xjtrace_revoca() {
		$post = I('post');
		if($post['submit'] == "xjtrace_revoca"){
			//验证
			if(empty($post['SYSTEM_REF'])){
				$this->wrong("参数数据出错！");
			}
			$res = D($this->TTrace)->updateTrace("SYSTEM_REF='".$post['SYSTEM_REF']."'", array('TRACE_REFUNDFLAG'=> 2));
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('撤销申请成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TTrace)->findTrace("SYSTEM_REF='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['TRACE_RETCODE']!='00' || $info['TRACE_STATUS']!='0' || $info['TRACE_VOIDFLAG']!='0' || $info['TRACE_REVERFLAG']!='0' || $info['TRACE_REFUNDFLAG']!='0'){
			$this->wrong("不满足撤销申请的条件！");
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
		$this->display();
	}
	/*
	* 现金消费交易流水查询	撤销确认
	**/
	public function xjtrace_affirm() {
		$post = I('post');
		if($post['submit'] == "xjtrace_affirm"){
			//验证
			if(empty($post['SYSTEM_REF']) || empty($post['FLAG'])){
				$this->wrong("参数数据出错！");
			}
			$resdata = array(
				'TRACE_REFUNDFLAG'	=>	$post['FLAG']==1 ? 0 : 1
			);			
			$res = D($this->TTrace)->updateTrace("SYSTEM_REF='".$post['SYSTEM_REF']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//reg
			if($post['FLAG'] == 2){
				//reg
				$regdata = array(
					'REG_TYPE'		=>	'401',
					'REG_INDEX'		=>	$post['TRACE_INDEX1'],
					'REG_DESC'		=>	'手工撤销',
					'REG_AMT'		=>	$post['TRANS_AMT'],
					'MARK_FLAG'		=>	1,
					'MARK_DATE'		=>	date('Ymd'),
				);
				D($this->MReg)->addReg($regdata);		
			}		
			
			$msg = $post['FLAG']==1 ? '手工撤销拒绝成功！' : '手工撤销确认成功！';
			$this->right($msg, 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TTrace)->findTrace("SYSTEM_REF='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['TRACE_REFUNDFLAG'] != '2'){
			$this->wrong("不满足撤销确认的条件！");
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
		$this->display();
	}
	/*
	* 现金消费交易流水查询	导出
	**/
	public function xjtrace_export() {
		$post  = array(
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
			'SBRANCH_MAP_ID'	=>	I('SBRANCH_MAP_ID'),
			'SPARTNER_MAP_ID'	=>	I('SPARTNER_MAP_ID'),
			'TRANS_AMT_A'		=>	I('TRANS_AMT_A'),
			'TRANS_AMT_B'		=>	I('TRANS_AMT_B'),
			'VIP_CARDNO'		=>	I('VIP_CARDNO'),
			'HOST_MAP_ID'		=>	I('HOST_MAP_ID'),
			'SHOP_NAMEAB'		=>	I('SHOP_NAMEAB'),
			'POS_NO'			=>	I('POS_NO'),
			'POS_TRACE'			=>	I('POS_TRACE'),
		);
		$where = "FEE_STATUS=0 and TRACE_STATUS=0 and TRANS_SUBID=33 and TRACE_VOIDFLAG=0 and TRACE_REVERFLAG=0 and TRACE_REFUNDFLAG!=1";	//FEE_STATUS 0正常1超扣
		//交易日期	开始
		if($post['SYSTEM_DATE_A']) {
			$where .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
		}
		//交易日期	结束
		if($post['SYSTEM_DATE_B']) {
			$where .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
		}
		//流水归属
		if($post['SBRANCH_MAP_ID']){
			$where .= " and SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
		}
		if($post['SPARTNER_MAP_ID']){
			$pids = get_plv_childs($post['SPARTNER_MAP_ID'], 1);
			$where .= " and SPARTNER_MAP_ID in (".$pids.")";
		}			
		//交易金额	开始
		if($post['TRANS_AMT_A']) {
			$where .= " and TRANS_AMT >= '".setMoney($post['TRANS_AMT_A'], '2')."'";
		}
		//交易金额	结束
		if($post['TRANS_AMT_B']) {
			$where .= " and TRANS_AMT <= '".setMoney($post['TRANS_AMT_B'], '2')."'";
		}
		//会员卡号
		if($post['VIP_CARDNO']){
			$where .= " and VIP_CARDNO like '".$post['VIP_CARDNO']."%'";
		}
		//支付通道
		if($post['HOST_MAP_ID']) {
			$where .= " and HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
		}
		//商户名称
		if($post['SHOP_NAMEAB']) {
			$where .= " and SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
		}
		//终端号
		if($post['POS_NO']) {
			$where .= " and POS_NO like '".$post['POS_NO']."%'";
		}
		//流水号
		if($post['POS_TRACE']) {
			$where .= " and POS_TRACE like '".$post['POS_TRACE']."%'";
		}
		
		//计算
		$count   = D($this->TTrace)->countNewsTrace($where);
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
			$list = D($this->TTrace)->getNewsTracelist($where, 't.*', $bRow.','.$eRow);
			
			//导出操作
			$xlsname = '现金消费交易流水文件('.($p+1).')';
			$xlscell = array(			
				array('TRANS_NAME',			'交易类型'),			
				array('SHOP_NAMEAB',		'商户名称'),
				array('CARD_NO',			'银行卡号'),
				array('TRANS_AMT',			'交易金额'),
				array('TRACE_STATUS',		'结果'),		
				array('VIP_CARDNO',			'会员卡号'),
				array('JIFENLV',			'积分率'),
				array('TRACE_REFUNDFLAG',	'撤销状态'),
				array('SYSTEM_DATE',		'交易时间'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'TRANS_NAME'		=>	$val['TRANS_NAME'],
					'SHOP_NAMEAB'		=>	$val['SHOP_NAMEAB'],
					'CARD_NO'			=>	setCard_no($val['CARD_NO']),
					'TRANS_AMT'			=>	setMoney($val['TRANS_AMT'], 2, 2),
					'TRACE_STATUS'		=>	C('TRACE_STATUS')[$val['TRACE_STATUS']],
					'VIP_CARDNO'		=>	$val['VIP_CARDNO']."\t",
					'JIFENLV'			=>	set_jifenlv($val['SHOP_NO']),
					'TRACE_REFUNDFLAG'	=>	C('TRACE_REFUNDFLAG')[$val['TRACE_REFUNDFLAG']],
					'SYSTEM_DATE'		=>	$val['SYSTEM_DATE'].' '.$val['SYSTEM_TIME']."\t",
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	
	
	
	
	/*
	* 超扣流水查询
	**/
	public function cktrace() {
		$post = I('post');
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "cktrace"){
			$where = "FEE_STATUS = 1 and TRACE_STATUS = 0 and TRACE_REVERFLAG = 0";	//FEE_STATUS 0正常1超扣
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}
			//商户归属
			$getlevel = filter_data('plv');	//列表查询
			$post['SBRANCH_MAP_ID']  = $getlevel['bid'];
			$post['SPARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['SBRANCH_MAP_ID']){
				$where .= " and SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
			}
			if($post['SPARTNER_MAP_ID']){
				$pids = get_plv_childs($post['SPARTNER_MAP_ID']);
				$where .= " and SPARTNER_MAP_ID in (".$pids.")";
			}
			
			//交易金额	开始
			if($post['TRANS_AMT_A']) {
				$where .= " and TRANS_AMT >= '".setMoney($post['TRANS_AMT_A'], '2')."'";
			}
			//交易金额	结束
			if($post['TRANS_AMT_B']) {
				$where .= " and TRANS_AMT <= '".setMoney($post['TRANS_AMT_B'], '2')."'";
			}
			//支付通道
			if($post['HOST_MAP_ID']) {
				$where .= " and HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
			}
			//商户名称
			if($post['SHOP_NAMEAB']) {
				$where .= " and SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
			}
			//终端号
			if($post['POS_NO']) {
				$where .= " and POS_NO like '".$post['POS_NO']."%'";
			}
			//流水号
			if($post['POS_TRACE']) {
				$where .= " and POS_TRACE like '".$post['POS_TRACE']."%'";
			}
			//分页
			$count = D($this->TTrace)->countNewsTrace($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TTrace)->getNewsTracelist($where, '*', $p->firstRow.','.$p->listRows);
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
		$this->assign('host_list', 			$host_list );
		$this->assign('timedata', 			$timedata);
		$this->assign('trace_status', 		C('TRACE_STATUS') );	//流水标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}	
	/*
	* 超扣流水查询	详情
	**/
	public function cktrace_show() {
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
		$this->display('trace_show');
	}
	/*
	* 超扣流水查询	导出
	**/
	public function cktrace_export() {
		$post  = array(
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
			'SBRANCH_MAP_ID'	=>	I('SBRANCH_MAP_ID'),
			'SPARTNER_MAP_ID'	=>	I('SPARTNER_MAP_ID'),
			'TRANS_AMT_A'		=>	I('TRANS_AMT_A'),
			'TRANS_AMT_B'		=>	I('TRANS_AMT_B'),
			'HOST_MAP_ID'		=>	I('HOST_MAP_ID'),
			'SHOP_NAMEAB'		=>	I('SHOP_NAMEAB'),
			'POS_NO'			=>	I('POS_NO'),
			'POS_TRACE'			=>	I('POS_TRACE'),
		);
		$where = "FEE_STATUS = 1 and TRACE_STATUS = 0 and TRACE_REVERFLAG = 0";	//FEE_STATUS 0正常1超扣
		//交易日期	开始
		if($post['SYSTEM_DATE_A']) {
			$where .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
		}
		//交易日期	结束
		if($post['SYSTEM_DATE_B']) {
			$where .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
		}
		//商户归属
		$getlevel = filter_data('plv');	//列表查询
		$post['SBRANCH_MAP_ID']  = $getlevel['bid'];
		$post['SPARTNER_MAP_ID'] = $getlevel['pid'];
		if($post['SBRANCH_MAP_ID']){
			$where .= " and SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
		}
		if($post['SPARTNER_MAP_ID']){
			$pids = get_plv_childs($post['SPARTNER_MAP_ID']);
			$where .= " and SPARTNER_MAP_ID in (".$pids.")";
		}
		
		//交易金额	开始
		if($post['TRANS_AMT_A']) {
			$where .= " and TRANS_AMT >= '".setMoney($post['TRANS_AMT_A'], '2')."'";
		}
		//交易金额	结束
		if($post['TRANS_AMT_B']) {
			$where .= " and TRANS_AMT <= '".setMoney($post['TRANS_AMT_B'], '2')."'";
		}
		//支付通道
		if($post['HOST_MAP_ID']) {
			$where .= " and HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
		}
		//商户名称
		if($post['SHOP_NAMEAB']) {
			$where .= " and SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
		}
		//终端号
		if($post['POS_NO']) {
			$where .= " and POS_NO like '".$post['POS_NO']."%'";
		}
		//流水号
		if($post['POS_TRACE']) {
			$where .= " and POS_TRACE like '".$post['POS_TRACE']."%'";
		}
		
		//计算
		$count   = D($this->TTrace)->countNewsTrace($where);
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
			$list = D($this->TTrace)->getNewsTracelist($where, '*', $bRow.','.$eRow);
				
			//导出操作
			$xlsname = '超扣流水文件('.($p+1).')';
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
	
	
	
	
	
	/*
	* 平台收益明细
	**/
	public function fdetail() {
		$home = session('HOME');
		$user_level 	= $home['USER_LEVEL'];
		$branch_map_id 	= $home['BRANCH_MAP_ID'];
		$partner_map_id = $home['PARTNER_MAP_ID'];		
		$post = I('post');
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "fdetail"){
			$jlist = D($this->TJfbls)->getNewsJfblslist("JFB_FEE>0", 'SYSTEM_REF');
			$jids  = i_array_column($jlist, 'SYSTEM_REF');
			$jids  = implode(',', $jids);
						
			$where = "TRACE_RETCODE='00' and TRACE_REVERFLAG=0 and TRANS_AMT>0 and TRANS_SUBID!=41 and TRANS_SUBID!=42 and SYSTEM_REF in (".$jids.")";			
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}
			//流水归属
			$getlevel = filter_data('plv');	//列表查询
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and (SBRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."' or VBRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."')";
			}
			if($post['PARTNER_MAP_ID']){
				$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
				$where .= " and (SPARTNER_MAP_ID in (".$pids.") or VPARTNER_MAP_ID in (".$pids."))";
			}
			//收益归属	当前流水归属
			if($post['SHOUYI_TYPE'] == 2){
				$branch_map_id 	= $post['BRANCH_MAP_ID']  ?  $post['BRANCH_MAP_ID'] : '';	
				$partner_map_id = $post['PARTNER_MAP_ID'] ? $post['PARTNER_MAP_ID'] : '';
				$level = 0;
				if($partner_map_id){
					$partnerdata = D($this->MPartner)->findPartnerOne("PARTNER_MAP_ID = '".$partner_map_id."'", 'PARTNER_LEVEL');
					$level 		 = $partnerdata['PARTNER_LEVEL'];
				}
				$user_level  	 = $level + 1;
			}
			//商户号
			if($post['SHOP_NO']) {
				$where .= " and SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAMEAB']) {
				$where .= " and SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
			}
			//分页
			$count = D($this->TTrace)->countNewsTrace($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TTrace)->getNewsTracelist($where, '*', $p->firstRow.','.$p->listRows);
			foreach($list as $key=>$val){
				$kfls  = D($this->TKfls)->findKfls("SYSTEM_REF = '".$val['SYSTEM_REF']."'");	
				$jfbls = D($this->TJfbls)->findJfbls("SYSTEM_REF = '".$val['SYSTEM_REF']."'");		
				
				//计算个人分润
				if($kfls['TRAFICC_FEE'] || $kfls['CON_RES_FEE']){
					$fee = $kfls['TRAFICC_FEE'] + $kfls['CON_RES_FEE'];
				}else{
					$fee = $jfbls['CON_FEE'];
				}
				$list[$key]['geren'] = $fee;
				
				//计算平台分润
				$pingtai = 0;
				switch($user_level){
					case 0:		//总部
						$pingtai = $kfls['BRANCH_FEE1'] + $jfbls['BRANCH_FEE1'] + $jfbls['CADF_FEE1'] + $jfbls['NTAX_FEE1'] + $jfbls['EATF_FEE1'];
						break;
					case 1:		//分公司
						if($branch_map_id == $kfls['BRANCH_MAP_ID2']){
							$pingtai += $kfls['BRANCH_FEE2'];
						}
						if($branch_map_id == $jfbls['ACQ_BRANCH_MAP_ID2']){
							$pingtai += $jfbls['ACQ_BRANCH_FEE2'];
						}
						if($branch_map_id == $jfbls['ISS_BRANCH_MAP_ID2']){
							$pingtai += $jfbls['ISS_BRANCH_FEE2'];
						}
						break;
					case 2:		//地市子公司
						if($partner_map_id == $kfls['PARTNER_MAP_ID1']){
							$pingtai += $kfls['PARTNER_FEE1'];
						}
						if($partner_map_id == $jfbls['ACQ_PARTNER_MAP_ID1']){
							$pingtai += $jfbls['ACQ_PARTNER_FEE1'];
						}	
						if($partner_map_id == $jfbls['ISS_PARTNER_MAP_ID1']){
							$pingtai += $jfbls['ISS_PARTNER_FEE1'] + $jfbls['ISS_LCF_FEE1'] + $jfbls['ISS_LCA_FEE1'];
						}					
						break;
					case 3:		//服务中心				
						if($partner_map_id == $kfls['PARTNER_MAP_ID2']){
							$pingtai += $kfls['PARTNER_FEE2'];
						}
						if($partner_map_id == $jfbls['ACQ_PARTNER_MAP_ID2']){
							$pingtai += $jfbls['ACQ_PARTNER_FEE2'];
						}	
						if($partner_map_id == $jfbls['ISS_PARTNER_MAP_ID2']){
							$pingtai += $jfbls['ISS_PARTNER_FEE2'];
						}
						break;
					case 4:		//发卡点			
						if($partner_map_id == $kfls['PARTNER_MAP_ID3']){
							$pingtai += $kfls['PARTNER_FEE3'];
						}
						if($partner_map_id == $jfbls['ISS_PARTNER_MAP_ID3']){
							$pingtai += $jfbls['ISS_PARTNER_FEE3'];
						}		
						if($partner_map_id == $jfbls['PARTNER_MAP_ID3A']){
							$pingtai += $jfbls['PARTNER_FEE3A'];
						}	
						if($partner_map_id == $jfbls['PARTNER_MAP_ID3B']){
							$pingtai += $jfbls['PARTNER_FEE3B'];
						}
						break;
				}
				$list[$key]['pingtai'] = $pingtai;
			}
			
			//计算平台总分润
			$totalbegin = '0';
			$totalend   = '0';
			$tlist  	= D($this->TTrace)->getNewsTracelist($where, '*', $p->firstRow.','.$p->listRows);
			foreach($tlist as $key=>$val){
				$kfls  = D($this->TKfls)->findKfls("SYSTEM_REF = '".$val['SYSTEM_REF']."'");	
				$jfbls = D($this->TJfbls)->findJfbls("SYSTEM_REF = '".$val['SYSTEM_REF']."'");		
								
				//计算平台分润
				$pingtai = 0;
				switch($user_level){
					case 0:		//总部
						$pingtai = $kfls['BRANCH_FEE1'] + $jfbls['BRANCH_FEE1'] + $jfbls['CADF_FEE1'] + $jfbls['NTAX_FEE1'] + $jfbls['EATF_FEE1'];
						break;
					case 1:		//分公司
						if($branch_map_id == $kfls['BRANCH_MAP_ID2']){
							$pingtai += $kfls['BRANCH_FEE2'];
						}
						if($branch_map_id == $jfbls['ACQ_BRANCH_MAP_ID2']){
							$pingtai += $jfbls['ACQ_BRANCH_FEE2'];
						}
						if($branch_map_id == $jfbls['ISS_BRANCH_MAP_ID2']){
							$pingtai += $jfbls['ISS_BRANCH_FEE2'];
						}
						break;
					case 2:		//地市子公司
						if($partner_map_id == $kfls['PARTNER_MAP_ID1']){
							$pingtai += $kfls['PARTNER_FEE1'];
						}
						if($partner_map_id == $jfbls['ACQ_PARTNER_MAP_ID1']){
							$pingtai += $jfbls['ACQ_PARTNER_FEE1'];
						}	
						if($partner_map_id == $jfbls['ISS_PARTNER_MAP_ID1']){
							$pingtai += $jfbls['ISS_PARTNER_FEE1'] + $jfbls['ISS_LCF_FEE1'] + $jfbls['ISS_LCA_FEE1'];
						}					
						break;
					case 3:		//服务中心				
						if($partner_map_id == $kfls['PARTNER_MAP_ID2']){
							$pingtai += $kfls['PARTNER_FEE2'];
						}
						if($partner_map_id == $jfbls['ACQ_PARTNER_MAP_ID2']){
							$pingtai += $jfbls['ACQ_PARTNER_FEE2'];
						}	
						if($partner_map_id == $jfbls['ISS_PARTNER_MAP_ID2']){
							$pingtai += $jfbls['ISS_PARTNER_FEE2'];
						}
						break;
					case 4:		//发卡点			
						if($partner_map_id == $kfls['PARTNER_MAP_ID3']){
							$pingtai += $kfls['PARTNER_FEE3'];
						}
						if($partner_map_id == $jfbls['ISS_PARTNER_MAP_ID3']){
							$pingtai += $jfbls['ISS_PARTNER_FEE3'];
						}		
						if($partner_map_id == $jfbls['PARTNER_MAP_ID3A']){
							$pingtai += $jfbls['PARTNER_FEE3A'];
						}	
						if($partner_map_id == $jfbls['PARTNER_MAP_ID3B']){
							$pingtai += $jfbls['PARTNER_FEE3B'];
						}
						break;
				}
				//处理平台总分润
				//现金消费+银行卡消费+预授权确认完成
				if($val['TRANS_SUBID']=='33' || $val['TRANS_SUBID']=='31' || $val['TRANS_SUBID']=='43'){					
					$totalbegin += $pingtai;
				}
				//现金消费(撤销、退货)+银行卡消费（撤销、退货）+预授权撤销完成
				if($val['TRANS_SUBID']=='733' || $val['TRANS_SUBID']=='533' || $val['TRANS_SUBID']=='731' || $val['TRANS_SUBID']=='531' || $val['TRANS_SUBID']=='743'){					
					$totalend   += $pingtai;
				}
			}			
			$totalpingtai = $totalbegin - $totalend;
			
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'list', 		$list );
			$this->assign ( 'totalpingtai', $totalpingtai );	
		}
		$this->assign ( 'postdata', 		$post );
		
		//Excel导出参数
		unset($post['submit']);
		$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		
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
		$this->assign('timedata', 			$timedata);		
		$this->assign('trace_status', 		C('TRACE_STATUS') );	//流水标志
		$this->assign('clear_sflag', 		C('CLEAR_SFLAG') );		//划转标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 平台收益明细	详情
	**/
	public function fdetail_show() {
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
		$this->display('trace_show');
	}	
	/*
	* 平台收益明细	导出
	**/
	public function fdetail_export() {
		$home = session('HOME');
		$user_level 	= $home['USER_LEVEL'];
		$branch_map_id 	= $home['BRANCH_MAP_ID'];
		$partner_map_id = $home['PARTNER_MAP_ID'];		
		$post  = array(
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'SHOP_NAMEAB'		=>	I('SHOP_NAMEAB'),
		);
		$where = "t.TRACE_RETCODE='00' and t.TRACE_REVERFLAG=0 and t.TRANS_AMT>0 and t.TRANS_SUBID!=41 and t.TRANS_SUBID !=42 and j.JFB_FEE>0";			
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
		//商户名称
		if($post['SHOP_NAMEAB']) {
			$where .= " and t.SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
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
			$field  = 'k.BRANCH_FEE1 as K_BRANCH_FEE1,j.BRANCH_FEE1 as J_BRANCH_FEE1,j.CADF_FEE1,j.NTAX_FEE1,j.EATF_FEE1,';
			$field .= 'k.BRANCH_MAP_ID2,k.BRANCH_FEE2,j.ACQ_BRANCH_MAP_ID2,j.ACQ_BRANCH_FEE2,j.ISS_BRANCH_MAP_ID2,j.ISS_BRANCH_FEE2,';
			$field .= 'k.PARTNER_MAP_ID1,k.PARTNER_FEE1,j.ACQ_PARTNER_MAP_ID1,j.ACQ_PARTNER_FEE1,j.ISS_PARTNER_MAP_ID1,j.ISS_PARTNER_FEE1,j.ISS_LCF_FEE1,j.ISS_LCA_FEE1,';			
			$field .= 'k.PARTNER_MAP_ID2,k.PARTNER_FEE2,j.ACQ_PARTNER_MAP_ID2,j.ACQ_PARTNER_FEE2,j.ISS_PARTNER_MAP_ID2,j.ISS_PARTNER_FEE2,';
			$field .= 'k.PARTNER_MAP_ID3,k.PARTNER_FEE3,j.PARTNER_MAP_ID3A,j.PARTNER_FEE3A,j.PARTNER_MAP_ID3B,j.PARTNER_FEE3B';					
			$list  = D($this->TTrace)->getTracelist($where, 't.*,k.TRAFICC_FEE,k.CON_RES_FEE,j.CON_FEE,'.$field, $bRow.','.$eRow);
			foreach($list as $key=>$val){
				//计算个人分润
				if($val['TRAFICC_FEE'] || $val['CON_RES_FEE']){
					$fee = $val['TRAFICC_FEE'] + $val['CON_RES_FEE'];
				}else{
					$fee = $val['CON_FEE'];
				}
				$list[$key]['geren'] = $fee;
				//计算平台分润
				$pingtai = 0;
				switch($user_level){
					case 0:		//总部
						$pingtai = $val['K_BRANCH_FEE1'] + $val['J_BRANCH_FEE1'] + $val['CADF_FEE1'] + $val['NTAX_FEE1'] + $val['EATF_FEE1'];
						break;
					case 1:		//分公司
						if($branch_map_id == $val['BRANCH_MAP_ID2']){
							$pingtai += $val['BRANCH_FEE2'];
						}
						if($branch_map_id == $val['ACQ_BRANCH_MAP_ID2']){
							$pingtai += $val['ACQ_BRANCH_FEE2'];
						}
						if($branch_map_id == $val['ISS_BRANCH_MAP_ID2']){
							$pingtai += $val['ISS_BRANCH_FEE2'];
						}
						break;
					case 2:		//地市子公司
						if($partner_map_id == $val['PARTNER_MAP_ID1']){
							$pingtai += $val['PARTNER_FEE1'];
						}
						if($partner_map_id == $val['ACQ_PARTNER_MAP_ID1']){
							$pingtai += $val['ACQ_PARTNER_FEE1'];
						}	
						if($partner_map_id == $val['ISS_PARTNER_MAP_ID1']){
							$pingtai += $val['ISS_PARTNER_FEE1'] + $val['ISS_LCF_FEE1'] + $val['ISS_LCA_FEE1'];
						}					
						break;
					case 3:		//服务中心				
						if($partner_map_id == $val['PARTNER_MAP_ID2']){
							$pingtai += $val['PARTNER_FEE2'];
						}
						if($partner_map_id == $val['ACQ_PARTNER_MAP_ID2']){
							$pingtai += $val['ACQ_PARTNER_FEE2'];
						}	
						if($partner_map_id == $val['ISS_PARTNER_MAP_ID2']){
							$pingtai += $val['ISS_PARTNER_FEE2'];
						}
						break;
					case 4:		//发卡点					
						if($partner_map_id == $val['PARTNER_MAP_ID3']){
							$pingtai += $val['PARTNER_FEE3'];
						}
						if($partner_map_id == $val['PARTNER_MAP_ID3A']){
							$pingtai += $val['PARTNER_FEE3A'];
						}	
						if($partner_map_id == $val['PARTNER_MAP_ID3B']){
							$pingtai += $val['PARTNER_FEE3B'];
						}
						break;
				}
				$list[$key]['pingtai'] = $pingtai;
			}		
		
			//导出操作
			$xlsname = '平台收益文件('.($p+1).')';
			$xlscell = array(
				array('TRANS_NAME',		'交易类型'),
				array('SHOP_NAMEAB',	'商户名称'),
				array('CARD_NO',		'银行卡号'),
				array('TRANS_AMT',		'交易金额'),
				array('JIFENLV',		'积分率'),
				array('TRACE_STATUS',	'结果'),		
				array('VIP_CARDNO',		'会员卡号'),
				array('pingtai',		'平台分润'),
				array('geren',			'个人分润'),
				array('MARK_FLAG',		'结算标志'),
				array('SYSTEM_DATE',	'交易日期'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'TRANS_NAME'	=>	$val['TRANS_NAME'],
					'SHOP_NAMEAB'	=>	$val['SHOP_NAMEAB'],
					'CARD_NO'		=>	setCard_no($val['CARD_NO']),
					'TRANS_AMT'		=>	setMoney($val['TRANS_AMT'], 2, 2),
					'JIFENLV'		=>	set_jifenlv($val['SHOP_NO']),
					'TRACE_STATUS'	=>	C('TRACE_STATUS')[$val['TRACE_STATUS']],
					'VIP_CARDNO'	=>	$val['VIP_CARDNO']."\t",
					'pingtai'		=>	setMoney($val['pingtai'], 2, 2),
					'geren'			=>	setMoney($val['geren'], 2, 2),
					'MARK_FLAG'		=>	$val['MARK_FLAG']==0 ? '已清' : '未清',
					'SYSTEM_DATE'	=>	$val['SYSTEM_DATE'].' '.$val['SYSTEM_TIME']."\t",	
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	
	
	
	/*
	* 会员收益明细
	**/
	public function vtrace() {
		$home = session('HOME');
		$user_level 	= $home['USER_LEVEL'];
		$branch_map_id 	= $home['BRANCH_MAP_ID'];
		$partner_map_id = $home['PARTNER_MAP_ID'];		
		$post = I('post');
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "vtrace"){
			$where = "t.TRACE_RETCODE='00' and t.TRACE_REVERFLAG=0 and t.TRANS_AMT>0 and t.TRANS_SUBID!=41 and t.TRANS_SUBID !=42";			
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and t.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and t.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}
			//会员归属
			$getlevel = filter_data('plv');	//列表查询
			$post['VBRANCH_MAP_ID']  = $getlevel['bid'];
			$post['VPARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['VBRANCH_MAP_ID']){
				$where .= " and t.VBRANCH_MAP_ID = '".$post['VBRANCH_MAP_ID']."'";
			}
			if($post['VPARTNER_MAP_ID']){
				$pids = get_plv_childs($post['VPARTNER_MAP_ID'], 1);
				$where .= " and t.VPARTNER_MAP_ID in (".$pids.")";
			}
			//商户号
			if($post['SHOP_NO']) {
				$where .= " and t.SHOP_NO = '".$post['SHOP_NO']."'";
			}			
			//商户名称
			if($post['SHOP_NAMEAB']) {
				$where .= " and t.SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
			}
			//会员
			if($post['VIP_ID'] != ''){
				$where .= $post['VIP_ID'] ? " and t.VIP_ID > 0" : " and t.VIP_ID = 0";
			}
			//会员卡号
			if($post['VIP_CARDNO']){
				$where .= " and t.VIP_CARDNO like '".$post['VIP_CARDNO']."%'";
			}
			//分页
			$count = D($this->TTrace)->countTrace($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$field  = 'k.BRANCH_FEE1 as K_BRANCH_FEE1,j.BRANCH_FEE1 as J_BRANCH_FEE1,j.CADF_FEE1,j.NTAX_FEE1,j.EATF_FEE1,';
			$field .= 'k.BRANCH_MAP_ID2,k.BRANCH_FEE2,j.ACQ_BRANCH_MAP_ID2,j.ACQ_BRANCH_FEE2,j.ISS_BRANCH_MAP_ID2,j.ISS_BRANCH_FEE2,';
			$field .= 'k.PARTNER_MAP_ID1,k.PARTNER_FEE1,j.ACQ_PARTNER_MAP_ID1,j.ACQ_PARTNER_FEE1,j.ISS_PARTNER_MAP_ID1,j.ISS_PARTNER_FEE1,j.ISS_LCF_FEE1,j.ISS_LCA_FEE1,';			
			$field .= 'k.PARTNER_MAP_ID2,k.PARTNER_FEE2,j.ACQ_PARTNER_MAP_ID2,j.ACQ_PARTNER_FEE2,j.ISS_PARTNER_MAP_ID2,j.ISS_PARTNER_FEE2,';
			$field .= 'k.PARTNER_MAP_ID3,k.PARTNER_FEE3,j.PARTNER_MAP_ID3A,j.PARTNER_FEE3A,j.PARTNER_MAP_ID3B,j.PARTNER_FEE3B';					
			$list  = D($this->TTrace)->getTracelist($where, 't.*,k.TRAFICC_FEE,k.CON_RES_FEE,j.CON_FEE,'.$field, $p->firstRow.','.$p->listRows);
			foreach($list as $key=>$val){
				//计算个人分润
				if($val['TRAFICC_FEE'] || $val['CON_RES_FEE']){
					$fee = $val['TRAFICC_FEE'] + $val['CON_RES_FEE'];
				}else{
					$fee = $val['CON_FEE'];
				}
				$list[$key]['geren'] = $fee;
				//计算平台分润
				$pingtai = '0';
				switch($user_level){
					case 0:		//总部
						$pingtai = $val['K_BRANCH_FEE1'] + $val['J_BRANCH_FEE1'] + $val['CADF_FEE1'] + $val['NTAX_FEE1'] + $val['EATF_FEE1'];
						break;
					case 1:		//分公司
						if($branch_map_id = $val['BRANCH_MAP_ID2']){
							$pingtai += $val['BRANCH_FEE2'];
						}
						if($branch_map_id = $val['ACQ_BRANCH_MAP_ID2']){
							$pingtai += $val['ACQ_BRANCH_FEE2'];
						}
						if($branch_map_id = $val['ISS_BRANCH_MAP_ID2']){
							$pingtai += $val['ISS_BRANCH_FEE2'];
						}			
						break;
					case 2:		//地市子公司
						if($partner_map_id = $val['PARTNER_MAP_ID1']){
							$pingtai += $val['PARTNER_FEE1'];
						}
						if($partner_map_id = $val['ACQ_PARTNER_MAP_ID1']){
							$pingtai += $val['ACQ_PARTNER_FEE1'];
						}	
						if($partner_map_id = $val['ISS_PARTNER_MAP_ID1']){
							$pingtai += $val['ISS_PARTNER_FEE1'] + $val['ISS_LCF_FEE1'] + $val['ISS_LCA_FEE1'];
						}					
						break;
					case 3:		//服务中心				
						if($partner_map_id = $val['PARTNER_MAP_ID2']){
							$pingtai += $val['PARTNER_FEE2'];
						}
						if($partner_map_id = $val['ACQ_PARTNER_MAP_ID2']){
							$pingtai += $val['ACQ_PARTNER_FEE2'];
						}	
						if($partner_map_id = $val['ISS_PARTNER_MAP_ID2']){
							$pingtai += $val['ISS_PARTNER_FEE2'];
						}
						break;
					case 4:		//发卡点					
						if($partner_map_id = $val['PARTNER_MAP_ID3']){
							$pingtai += $val['PARTNER_FEE3'];
						}
						if($partner_map_id = $val['PARTNER_MAP_ID3A']){
							$pingtai += $val['PARTNER_FEE3A'];
						}	
						if($partner_map_id = $val['PARTNER_MAP_ID3B']){
							$pingtai += $val['PARTNER_FEE3B'];
						}
						break;
				}
				$list[$key]['pingtai'] = $pingtai;
			}
			
			$tlist  = D($this->TTrace)->getTracelist($where, 't.*,k.TRAFICC_FEE,k.CON_RES_FEE,j.CON_FEE,'.$field, '');
			$totalbegin = '0';
			$totalend   = '0';
			foreach($tlist as $key=>$val){
				//计算个人分润
				if($val['TRAFICC_FEE'] || $val['CON_RES_FEE']){
					$fee = $val['TRAFICC_FEE'] + $val['CON_RES_FEE'];
				}else{
					$fee = $val['CON_FEE'];
				}				
				//处理个人总分润
				//现金消费+银行卡消费+预授权确认完成
				if($val['TRANS_SUBID']=='33' || $val['TRANS_SUBID']=='31' || $val['TRANS_SUBID']=='43'){					
					$totalbegin += $fee;
				}
				//现金消费(撤销、退货)+银行卡消费（撤销、退货）+预授权撤销完成
				if($val['TRANS_SUBID']=='733' || $val['TRANS_SUBID']=='533' || $val['TRANS_SUBID']=='731' || $val['TRANS_SUBID']=='531' || $val['TRANS_SUBID']=='743'){					
					$totalend   += $fee;
				}
			}
			$totalgeren = $totalbegin - $totalend;
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'list', 		$list );
			$this->assign ( 'totalgeren', 	$totalgeren );		
		}
		$this->assign ( 'postdata', 		$post );
		
		//Excel导出参数
		unset($post['submit']);
		$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		
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
		$this->assign('timedata', 			$timedata);		
		$this->assign('trace_status', 		C('TRACE_STATUS') );	//流水标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 会员收益明细	导出
	**/
	public function vtrace_export() {
		$home = session('HOME');
		$user_level 	= $home['USER_LEVEL'];
		$branch_map_id 	= $home['BRANCH_MAP_ID'];
		$partner_map_id = $home['PARTNER_MAP_ID'];		
		$post  = array(
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
			'VBRANCH_MAP_ID'	=>	I('VBRANCH_MAP_ID'),
			'VPARTNER_MAP_ID'	=>	I('VPARTNER_MAP_ID'),
			'SHOP_NAMEAB'		=>	I('SHOP_NAMEAB'),
			'VIP_ID'			=>	I('VIP_ID'),
			'SHOP_NAMEAB'		=>	I('SHOP_NAMEAB'),
		);
		$where = "t.TRACE_RETCODE='00' and t.TRACE_REVERFLAG=0 and t.TRANS_AMT>0 and t.TRANS_SUBID!=41 and t.TRANS_SUBID !=42";			
		//交易日期	开始
		if($post['SYSTEM_DATE_A']) {
			$where .= " and t.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
		}
		//交易日期	结束
		if($post['SYSTEM_DATE_B']) {
			$where .= " and t.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
		}
		//会员归属
		if($post['VBRANCH_MAP_ID']){
			$where .= " and t.VBRANCH_MAP_ID = '".$post['VBRANCH_MAP_ID']."'";
		}
		if($post['VPARTNER_MAP_ID']){
			$pids = get_plv_childs($post['VPARTNER_MAP_ID'], 1);
			$where .= " and t.VPARTNER_MAP_ID in (".$pids.")";
		}						
		//商户名称
		if($post['SHOP_NAMEAB']) {
			$where .= " and t.SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
		}
		//会员
		if($post['VIP_ID'] != ''){
			$where .= $post['VIP_ID'] ? " and t.VIP_ID > 0" : " and t.VIP_ID = 0";
		}
		//会员卡号
		if($post['VIP_CARDNO']){
			$where .= " and t.VIP_CARDNO like '".$post['VIP_CARDNO']."%'";
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
			$field  = 'k.BRANCH_FEE1 as K_BRANCH_FEE1,j.BRANCH_FEE1 as J_BRANCH_FEE1,j.CADF_FEE1,j.NTAX_FEE1,j.EATF_FEE1,';
			$field .= 'k.BRANCH_MAP_ID2,k.BRANCH_FEE2,j.ACQ_BRANCH_MAP_ID2,j.ACQ_BRANCH_FEE2,j.ISS_BRANCH_MAP_ID2,j.ISS_BRANCH_FEE2,';
			$field .= 'k.PARTNER_MAP_ID1,k.PARTNER_FEE1,j.ACQ_PARTNER_MAP_ID1,j.ACQ_PARTNER_FEE1,j.ISS_PARTNER_MAP_ID1,j.ISS_PARTNER_FEE1,j.ISS_LCF_FEE1,j.ISS_LCA_FEE1,';			
			$field .= 'k.PARTNER_MAP_ID2,k.PARTNER_FEE2,j.ACQ_PARTNER_MAP_ID2,j.ACQ_PARTNER_FEE2,j.ISS_PARTNER_MAP_ID2,j.ISS_PARTNER_FEE2,';
			$field .= 'k.PARTNER_MAP_ID3,k.PARTNER_FEE3,j.PARTNER_MAP_ID3A,j.PARTNER_FEE3A,j.PARTNER_MAP_ID3B,j.PARTNER_FEE3B';					
			$list  = D($this->TTrace)->getTracelist($where, 't.*,k.TRAFICC_FEE,k.CON_RES_FEE,j.CON_FEE,'.$field, $bRow.','.$eRow);
			foreach($list as $key=>$val){
				//计算个人分润
				if($val['TRAFICC_FEE'] || $val['CON_RES_FEE']){
					$fee = $val['TRAFICC_FEE'] + $val['CON_RES_FEE'];
				}else{
					$fee = $val['CON_FEE'];
				}
				$list[$key]['geren'] = $fee;
				//计算平台分润
				$pingtai = '0';
				switch($user_level){
					case 0:		//总部
						$pingtai = $val['K_BRANCH_FEE1'] + $val['J_BRANCH_FEE1'] + $val['CADF_FEE1'] + $val['NTAX_FEE1'] + $val['EATF_FEE1'];
						break;
					case 1:		//分公司
						if($branch_map_id = $val['BRANCH_MAP_ID2']){
							$pingtai += $val['BRANCH_FEE2'];
						}
						if($branch_map_id = $val['ACQ_BRANCH_MAP_ID2']){
							$pingtai += $val['ACQ_BRANCH_FEE2'];
						}
						if($branch_map_id = $val['ISS_BRANCH_MAP_ID2']){
							$pingtai += $val['ISS_BRANCH_FEE2'];
						}			
						break;
					case 2:		//地市子公司
						if($partner_map_id = $val['PARTNER_MAP_ID1']){
							$pingtai += $val['PARTNER_FEE1'];
						}
						if($partner_map_id = $val['ACQ_PARTNER_MAP_ID1']){
							$pingtai += $val['ACQ_PARTNER_FEE1'];
						}	
						if($partner_map_id = $val['ISS_PARTNER_MAP_ID1']){
							$pingtai += $val['ISS_PARTNER_FEE1'] + $val['ISS_LCF_FEE1'] + $val['ISS_LCA_FEE1'];
						}					
						break;
					case 3:		//服务中心				
						if($partner_map_id = $val['PARTNER_MAP_ID2']){
							$pingtai += $val['PARTNER_FEE2'];
						}
						if($partner_map_id = $val['ACQ_PARTNER_MAP_ID2']){
							$pingtai += $val['ACQ_PARTNER_FEE2'];
						}	
						if($partner_map_id = $val['ISS_PARTNER_MAP_ID2']){
							$pingtai += $val['ISS_PARTNER_FEE2'];
						}
						break;
					case 4:		//发卡点					
						if($partner_map_id = $val['PARTNER_MAP_ID3']){
							$pingtai += $val['PARTNER_FEE3'];
						}
						if($partner_map_id = $val['PARTNER_MAP_ID3A']){
							$pingtai += $val['PARTNER_FEE3A'];
						}	
						if($partner_map_id = $val['PARTNER_MAP_ID3B']){
							$pingtai += $val['PARTNER_FEE3B'];
						}
						break;
				}
				$list[$key]['pingtai'] = $pingtai;
			}
				
			//导出操作
			$xlsname = '会员收益文件('.($p+1).')';
			$xlscell = array(	
				array('TRANS_NAME',		'交易类型'),
				array('VIP_ID',			'会员名称'),
				array('VIP_CARDNO',		'会员卡号'),
				array('SHOP_NAMEAB',	'商户名称'),
				array('TRANS_AMT',		'交易金额'),
				array('JIFENLV',		'积分率'),
				array('TRACE_STATUS',	'结果'),	
				array('pingtai',		'平台分润'),
				array('geren',			'个人分润'),
				array('SYSTEM_DATE',	'交易日期'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'TRANS_NAME'	=>	$val['TRANS_NAME'],
					'VIP_ID'		=>	$val['VIP_ID'] ? getvip_name($val['VIP_ID']) : '',
					'VIP_CARDNO'	=>	$val['VIP_CARDNO']."\t",
					'SHOP_NAMEAB'	=>	$val['SHOP_NAMEAB'],
					'TRANS_AMT'		=>	setMoney($val['TRANS_AMT'], 2, 2),
					'JIFENLV'		=>	set_jifenlv($val['SHOP_NO']),
					'TRACE_STATUS'	=>	C('TRACE_STATUS')[$val['TRACE_STATUS']],
					'pingtai'		=>	setMoney($val['pingtai'], 2, 2),
					'geren'			=>	setMoney($val['geren'], 2, 2),
					'SYSTEM_DATE'	=>	$val['SYSTEM_DATE'].' '.$val['SYSTEM_TIME']."\t",
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
}