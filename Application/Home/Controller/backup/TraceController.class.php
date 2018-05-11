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
			);
			$ajax_soplv = array(
				'bid'				=>	I('BRANCH_MAP_ID'),
				'pid'				=>	I('PARTNER_MAP_ID'),			
			);
		}
		//===结束===
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "trace"){
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
					
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			
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
		//临时加大PHP占用内存
		ini_set('memory_limit', '256M'); //内存限制
		set_time_limit(0);
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
			'IS_SHARE_FEE'		=>	I('IS_SHARE_FEE'),
			'CHANNEL_MAP_ID'	=>	I('CHANNEL_MAP_ID'),
			'VIP_CARDNO'		=>	I('VIP_CARDNO'),
		);

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
		//是否参与分润
		if($post['IS_SHARE_FEE']) {
			$where .= " AND t.TRACE_RETCODE='00' AND t.TRACE_REVERFLAG=0 AND t.TRANS_AMT>0 AND j.JFB_FEE>0 AND t.TRACE_VOIDFLAG = 0 AND t.TRACE_REFUNDFLAG = 0";
			//交易类型（现金或银行卡）
			if(empty($post['TRANS_SUBID'])) {
				$where .= " AND t.TRANS_SUBID IN (43, 44, 39, 31, 32, 33, 38)";
			}
			$Model = M('', DB_PREFIX_TRA, DB_DSN_TRA);
			//统计总条数
			$count_sql = "select count(t.TRACE_ID) as TOTAL FROM T_JFBLS j LEFT JOIN t_trace t ON(t.SYSTEM_REF = j.SYSTEM_REF) WHERE ".$where;
			$num = $Model->query($count_sql);
			$count = $num[0]['TOTAL'];
		}else{
			//分页
			$count = D($this->TTrace)->countNewsTrace($where);
		}

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
			//是否参与分润
			if($post['IS_SHARE_FEE']) {
				$limit = ' limit '.$bRow.','.$eRow;
				//获取列表数据
				$list_sql = "select t.*, j.JFB_FEE, j.CON_FEE, j.PLAT_FEE FROM T_JFBLS j LEFT JOIN t_trace t ON(t.SYSTEM_REF = j.SYSTEM_REF) WHERE ".$where." ORDER BY t.SYSTEM_DATE desc, t.SYSTEM_TIME desc".$limit;
				$list = $Model->query($list_sql);
			}else{
				$list = D($this->TTrace)->getNewsTracelist($where, '*', $bRow.','.$eRow);
			}		
			//导出操作
			$xlsname = '交易流水文件('.($p+1).')';
			$xlscell = array(
				array('TRANS_NAME',		'交易类型'),
				array('BRANCH_MAP_ID',	'商户归属'),
				array('SHOP_NAME',		'商户名称'),
				array('SHOP_NAMEAB',	'商户简称'),
				array('CARD_NO',		'银行卡号'),
				array('TRANS_AMT',		'交易金额'),
				array('TRACE_STATUS',	'结果'),		
				array('VIP_CARDNO',		'会员卡号'),
				array('JIFENLV',		'积分率'),
				array('SYSTEM_DATE',	'系统时间'),
				array('POS_DATE',		'POS交易时间'),
				array('JFB_FEE',		'积分宝分润'),
				array('CON_FEE',		'平台分润'),
				array('PLAT_FEE',		'个人分润'),
				array('SHOP_NO',		'商户编码'),
				array('POS_NO',			'POS编号'),
				array('POS_TRACE',		'POS流水'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$shopdata = D('MShop')->findShop("SHOP_NO = '".$val['SHOP_NO']."'", 'SHOP_NAME');
				$xlsarray[] = array(
					'TRANS_NAME'	=>	$val['TRANS_NAME'],
					'BRANCH_MAP_ID'	=>	get_branch_name($val['SBRANCH_MAP_ID'], $val['SPARTNER_MAP_ID']),
					'SHOP_NAME'		=>	$shopdata['SHOP_NAME'],
					'SHOP_NAMEAB'	=>	$val['SHOP_NAMEAB'],
					'CARD_NO'		=>	setCard_no($val['CARD_NO']),
					'TRANS_AMT'		=>	setMoney($val['TRANS_AMT'], 2, 2),
					'TRACE_STATUS'	=>	C('TRACE_STATUS')[$val['TRACE_STATUS']],
					'VIP_CARDNO'	=>	$val['VIP_CARDNO']."\t",
					'JIFENLV'		=>	set_jifenlv($val['SHOP_NO']),
					'SYSTEM_DATE'	=>	$val['SYSTEM_DATE'].' '.$val['SYSTEM_TIME']."\t",
					'POS_DATE'		=>	$val['POS_DATE'].' '.$val['POS_TIME']."\t",
					'JFB_FEE'		=>	setMoney($val['JFB_FEE'], 2, 2),
					'CON_FEE'		=>	setMoney($val['CON_FEE'], 2, 2),
					'PLAT_FEE'		=>	setMoney($val['PLAT_FEE'], 2, 2),
					'SHOP_NO'		=> 	$val['SHOP_NO'],
					'POS_NO'		=> 	$val['POS_NO'],
					'POS_TRACE'		=> 	$val['POS_TRACE'],
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	
	
	
	
	
	/*
	* 现金消费交易流水查询
	**/
	public function xjtrace() {
		$post = I('post');		
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
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
			$ajax_soplv = array(
				'bid'				=>	I('SBRANCH_MAP_ID'),
				'pid'				=>	I('SPARTNER_MAP_ID'),			
			);
		}
		//===结束===
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
			//===优化统计===
			$getlevel = $ajax == 'loading' ? $ajax_soplv : filter_data('plv');	//列表查询
			//===结束=======
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
			if($post['VIP_CARDNO']) {
				$where .= " and VIP_CARDNO = '".$post['VIP_CARDNO']."'";
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
				$where .= " and POS_NO = '".$post['POS_NO']."'";
			}
			//流水号
			if($post['POS_TRACE']) {
				$where .= " and POS_TRACE = '".$post['POS_TRACE']."'";
			}
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->TTrace)->countNewsTrace($where);
				$resdata = array(
					'count'	=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======			
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->TTrace)->getNewsTracelist($where, '*', $fiRow.','.$liRow);
			
			//分页参数
			$this->assign ( 'totalCount', 	C('PAGE_COUNT')==count($list) ? 1 : 0 );
	       	$this->assign ( 'numPerPage', 	'' );
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
			$where .= " and VIP_CARDNO = '".$post['VIP_CARDNO']."'";
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
			$where .= " and POS_NO = '".$post['POS_NO']."'";
		}
		//流水号
		if($post['POS_TRACE']) {
			$where .= " and POS_TRACE = '".$post['POS_TRACE']."'";
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
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
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
			$ajax_soplv = array(
				'bid'				=>	I('SBRANCH_MAP_ID'),
				'pid'				=>	I('SPARTNER_MAP_ID'),			
			);
		}
		//===结束===		
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
			//===优化统计===
			$getlevel = $ajax == 'loading' ? $ajax_soplv : filter_data('plv');	//列表查询
			//===结束=======
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
				$where .= " and POS_NO = '".$post['POS_NO']."'";
			}
			//流水号
			if($post['POS_TRACE']) {
				$where .= " and POS_TRACE = '".$post['POS_TRACE']."'";
			}
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->TTrace)->countNewsTrace($where);
				$resdata = array(
					'count'	=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======			
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->TTrace)->getNewsTracelist($where, '*',  $fiRow.','.$liRow);
			
			//分页参数
			$this->assign ( 'totalCount', 	C('PAGE_COUNT')==count($list) ? 1 : 0 );
	       	$this->assign ( 'numPerPage', 	'' );
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
			$where .= " and POS_NO = '".$post['POS_NO']."'";
		}
		//流水号
		if($post['POS_TRACE']) {
			$where .= " and POS_TRACE = '".$post['POS_TRACE']."'";
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
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
				'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
				'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
				'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
				'SHOP_NO'			=>	I('SHOP_NO'),
				'SHOP_NAMEAB'		=>	I('SHOP_NAMEAB'),
				'TRANS_SUBID'		=>	I('TRANS_SUBID'),
				'SHOUYI_TYPE'		=>	I('SHOUYI_TYPE'),
			);
			$ajax_soplv = array(
				'bid'				=>	I('BRANCH_MAP_ID'),
				'pid'				=>	I('PARTNER_MAP_ID'),			
			);
		}
		//===结束===	
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "fdetail"){
			$where = "t.TRACE_RETCODE='00' and t.TRACE_REVERFLAG=0 and t.TRANS_AMT>0 and j.JFB_FEE>0 AND t.TRACE_VOIDFLAG = 0 AND t.TRACE_REFUNDFLAG = 0";
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and t.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and t.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}
			//流水归属
			//===优化统计===
			$getlevel = $ajax == 'loading' ? $ajax_soplv : filter_data('plv');	//列表查询
			//===结束=======
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
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
				$where .= " and t.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAMEAB']) {
				$where .= " and t.SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
			}
			//交易类型（现金或银行卡）
			if($post['TRANS_SUBID']) {
				$where .= " and t.TRANS_SUBID = '".$post['TRANS_SUBID']."'";
			}else{
				//消费条件
				$where_1 = " and t.TRANS_SUBID IN (43, 44, 39, 31, 32, 33, 38)";
			}
			//===优化统计===
			if($ajax == 'loading'){
				//去掉撤销，退货		
				$count = D($this->TTrace)->countTrace($where.$total_where1);	
				//消费收益统计				
				switch($user_level){
					case 0:
						$field = "(SUM(CASE WHEN k.BRANCH_FEE1 > 0 THEN k.BRANCH_FEE1 ELSE 0 END)+
								SUM(CASE WHEN j.BRANCH_FEE1 > 0 THEN j.BRANCH_FEE1 ELSE 0 END)+
								SUM(CASE WHEN j.CADF_FEE1 > 0 THEN j.CADF_FEE1 ELSE 0 END)+
								SUM(CASE WHEN j.NTAX_FEE1 > 0 THEN j.NTAX_FEE1 ELSE 0 END)+
								SUM(CASE WHEN j.EATF_FEE1 > 0 THEN j.EATF_FEE1 ELSE 0 END)) AS total";
					break;
					case 1:		//分公司
						$field = "(SUM(CASE WHEN k.BRANCH_MAP_ID2 = '".$branch_map_id."' THEN k.BRANCH_FEE2 ELSE 0 END)+
								SUM(CASE WHEN j.ACQ_BRANCH_MAP_ID2 = '".$branch_map_id."' THEN j.ACQ_BRANCH_FEE2 ELSE 0 END)+
								SUM(CASE WHEN j.ISS_BRANCH_MAP_ID2 = '".$branch_map_id."' THEN j.ISS_BRANCH_FEE2 ELSE 0 END)) AS total";
					break;
					case 2:		//地市子公司
						$field = "(SUM(CASE WHEN k.PARTNER_MAP_ID1 = '".$partner_map_id."' THEN k.PARTNER_FEE1 ELSE 0 END)+
								SUM(CASE WHEN j.ACQ_PARTNER_MAP_ID1 = '".$partner_map_id."' THEN j.ACQ_PARTNER_FEE1 ELSE 0 END)+
								SUM(CASE WHEN j.ISS_PARTNER_MAP_ID1 = '".$partner_map_id."' THEN j.ISS_PARTNER_FEE1+j.ISS_LCF_FEE1+j.ISS_LCA_FEE1 ELSE 0 END)) AS total";
					break;
					case 3:		//服务中心
						$field = "(SUM(CASE WHEN k.PARTNER_MAP_ID2 = '".$partner_map_id."' THEN k.PARTNER_FEE2 ELSE 0 END) +
								SUM(CASE WHEN k.PARTNER_MAP_ID3 = '".$partner_map_id."' THEN k.PARTNER_FEE3 ELSE 0 END) +
								SUM(CASE WHEN j.ACQ_PARTNER_MAP_ID2 = '".$partner_map_id."' THEN j.ACQ_PARTNER_FEE2 ELSE 0 END) +
								SUM(CASE WHEN j.ISS_PARTNER_MAP_ID2 = '".$partner_map_id."' THEN j.ISS_PARTNER_FEE2 ELSE 0 END) +
								SUM(CASE WHEN j.ISS_PARTNER_MAP_ID3 = '".$partner_map_id."' THEN j.ISS_PARTNER_FEE3 ELSE 0 END) +
								SUM(CASE WHEN j.PARTNER_MAP_ID3A = '".$partner_map_id."' THEN j.PARTNER_FEE3A ELSE 0 END) +
								SUM(CASE WHEN j.PARTNER_MAP_ID3B = '".$partner_map_id."' THEN j.PARTNER_FEE3B ELSE 0 END)) AS total";
					break;
					case 4:		//发卡点
						$field = "(SUM(CASE WHEN k.PARTNER_MAP_ID3 = '".$partner_map_id."' THEN k.PARTNER_FEE3 ELSE 0 END)+
								SUM(CASE WHEN j.PARTNER_MAP_ID3A = '".$partner_map_id."' THEN j.PARTNER_FEE3A ELSE 0 END) +
								SUM(CASE WHEN j.PARTNER_MAP_ID3B = '".$partner_map_id."' THEN j.PARTNER_FEE3B ELSE 0 END) +
								SUM(CASE WHEN j.ISS_PARTNER_MAP_ID3 = '".$partner_map_id."' THEN j.ISS_PARTNER_FEE3 ELSE 0 END)) AS total";
					break;
				}
				//收益统计
				$total_1 	  = $this->partner_statistics($field,$where.$where_1);
				$totalpingtai = $total_1[0]['total'];

				//总金额统计
				$total_2 	  = $this->partner_statistics2($where.$where_1);
				$totalall2	  = $total_2[0]['total_2'];

				$resdata 	  = array(
					'count'	  => $count,
					'amt'	  => setMoney($totalpingtai, 2, 2),
					'total'	  => setMoney($totalall2, 2, 2),
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======
			$fiRow  = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow  = C('PAGE_COUNT');
			$field  = 'k.BRANCH_FEE1 as K_BRANCH_FEE1,j.BRANCH_FEE1 as J_BRANCH_FEE1,j.CADF_FEE1,j.NTAX_FEE1,j.EATF_FEE1,';
			$field .= 'k.BRANCH_MAP_ID2,k.BRANCH_FEE2,j.ACQ_BRANCH_MAP_ID2,j.ACQ_BRANCH_FEE2,j.ISS_BRANCH_MAP_ID2,j.ISS_BRANCH_FEE2,';
			$field .= 'k.PARTNER_MAP_ID1,k.PARTNER_FEE1,j.ACQ_PARTNER_MAP_ID1,j.ACQ_PARTNER_FEE1,j.ISS_PARTNER_MAP_ID1,j.ISS_PARTNER_FEE1,j.ISS_LCF_FEE1,j.ISS_LCA_FEE1,';			
			$field .= 'k.PARTNER_MAP_ID2,k.PARTNER_FEE2,j.ACQ_PARTNER_MAP_ID2,j.ACQ_PARTNER_FEE2,j.ISS_PARTNER_MAP_ID2,j.ISS_PARTNER_FEE2,';
			$field .= 'k.PARTNER_MAP_ID3,k.PARTNER_FEE3,j.ISS_PARTNER_MAP_ID3,j.ISS_PARTNER_FEE3,j.PARTNER_MAP_ID3A,j.PARTNER_FEE3A,j.PARTNER_MAP_ID3B,j.PARTNER_FEE3B';				
			$list  = D($this->TTrace)->getTracelist($where.$total_where1, 't.*,k.TRAFICC_FEE,k.CON_RES_FEE,j.CON_FEE,'.$field, $fiRow.','.$liRow);
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
						if($partner_map_id == $val['PARTNER_MAP_ID3']){
							$pingtai += $val['PARTNER_FEE3'];
						}
						if($partner_map_id == $val['ISS_PARTNER_MAP_ID3']){
							$pingtai += $val['ISS_PARTNER_FEE3'];
						}	
						if($partner_map_id == $val['PARTNER_MAP_ID3A']){
							$pingtai += $val['PARTNER_FEE3A'];
						}
						if($partner_map_id == $val['PARTNER_MAP_ID3B']){
							$pingtai += $val['PARTNER_FEE3B'];
						}
						break;
					case 4:		//发卡点	
						if($partner_map_id == $val['ISS_PARTNER_MAP_ID3']){
							$pingtai += $val['ISS_PARTNER_FEE3'];
						}				
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

			//分页参数
			$this->assign ( 'totalCount', 	C('PAGE_COUNT')==count($list) ? 1 : 0 );
	       	$this->assign ( 'numPerPage', 	'' );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'list', 		$list );
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
	//收益统计【市公司】
	protected function partner_statistics($field,$where){
		$sql = "SELECT ".$field." FROM t_trace t LEFT JOIN t_jfbls j ON (t.SYSTEM_REF = j.SYSTEM_REF) LEFT JOIN t_kfls k ON (t.SYSTEM_REF = k.SYSTEM_REF) WHERE ".$where;
		//echo $sql;exit();
		$model = M('trace', DB_PREFIX_TRA, DB_DSN_TRA);
		return $model->query($sql);	 
	}
	//收益统计【市公司】
	protected function partner_statistics2($where){
		$field = "SUM(j.TRANS_AMT) AS total_2";
		$sql = "SELECT ".$field." FROM t_trace t LEFT JOIN t_jfbls j ON (t.SYSTEM_REF = j.SYSTEM_REF) WHERE ".$where;
		//echo $sql;exit();
		$model = M('trace', DB_PREFIX_TRA, DB_DSN_TRA);
		return $model->query($sql);	 
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
		$post = array(
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'SHOP_NO'			=>	I('SHOP_NO'),
			'SHOP_NAMEAB'		=>	I('SHOP_NAMEAB'),
			'TRANS_SUBID'		=>	I('TRANS_SUBID'),
			'SHOUYI_TYPE'		=>	I('SHOUYI_TYPE'),
		);
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
				
		$where = "t.TRACE_RETCODE='00' and t.TRACE_REVERFLAG=0 and t.TRANS_AMT>0 and j.JFB_FEE>0 AND t.TRACE_VOIDFLAG = 0 AND t.TRACE_REFUNDFLAG = 0";

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
		//商户号
		if($post['SHOP_NO']) {
			$where .= " and t.SHOP_NO = '".$post['SHOP_NO']."'";
		}
		//商户名称
		if($post['SHOP_NAMEAB']) {
			$where .= " and t.SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
		}
		//交易类型（现金或银行卡）
		if($post['TRANS_SUBID']) {
			$where .= " and t.TRANS_SUBID = '".$post['TRANS_SUBID']."'";
		}else{
			//消费条件
			$where_1 = " and t.TRANS_SUBID IN (43, 44, 39, 31, 32, 33, 38)";
		}
		//计算
		$count   = D($this->TTrace)->countTrace($where.$total_where1);
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
			$field .= 'k.PARTNER_MAP_ID3,k.PARTNER_FEE3,j.ISS_PARTNER_MAP_ID3,j.ISS_PARTNER_FEE3,j.PARTNER_MAP_ID3A,j.PARTNER_FEE3A,j.PARTNER_MAP_ID3B,j.PARTNER_FEE3B';				

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
						if($partner_map_id == $val['PARTNER_MAP_ID3']){
							$pingtai += $val['PARTNER_FEE3'];
						}
						if($partner_map_id == $val['ISS_PARTNER_MAP_ID3']){
							$pingtai += $val['ISS_PARTNER_FEE3'];
						}	
						if($partner_map_id == $val['PARTNER_MAP_ID3A']){
							$pingtai += $val['PARTNER_FEE3A'];
						}
						if($partner_map_id == $val['PARTNER_MAP_ID3B']){
							$pingtai += $val['PARTNER_FEE3B'];
						}
						break;
					case 4:		//发卡点	
						if($partner_map_id == $val['ISS_PARTNER_MAP_ID3']){
							$pingtai += $val['ISS_PARTNER_FEE3'];
						}				
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
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
				'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
				'VBRANCH_MAP_ID'	=>	I('VBRANCH_MAP_ID'),
				'VPARTNER_MAP_ID'	=>	I('VPARTNER_MAP_ID'),
				'SHOP_NO'			=>	I('SHOP_NO'),
				'SHOP_NAMEAB'		=>	I('SHOP_NAMEAB'),
				'VIP_ID'			=>	I('VIP_ID'),
				'VIP_CARDNO'		=>	I('VIP_CARDNO'),
			);
			$ajax_soplv = array(
				'bid'				=>	I('VBRANCH_MAP_ID'),
				'pid'				=>	I('VPARTNER_MAP_ID'),			
			);
		}
		//===结束===
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "vtrace"){		
			$where = "t.TRACE_RETCODE='00' and t.TRACE_REVERFLAG=0 and t.TRANS_AMT>0 and t.TRANS_SUBID!=41 and t.TRANS_SUBID !=42 and j.JFB_FEE>0 AND t.TRACE_VOIDFLAG = 0 AND t.TRACE_REFUNDFLAG = 0";
			$where_1 .= " and t.TRANS_SUBID IN (43, 44, 39, 31, 32, 33, 38)";
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and t.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and t.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}
			//会员归属
			//===优化统计===
			$getlevel = $ajax == 'loading' ? $ajax_soplv : filter_data('plv');	//列表查询
			//===结束=======
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
				$where .= " and t.VIP_CARDNO = '".$post['VIP_CARDNO']."'";
			}
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->TTrace)->countTrace($where);
				//个人总分润				
				$field = "sum(case when t.TRANS_SUBID in (43, 44, 39, 31, 32, 33, 38) then IF((k.TRAFICC_FEE>0 or k.CON_RES_FEE>0),(k.TRAFICC_FEE+k.CON_RES_FEE),j.CON_FEE) else 0 end) SUM1";
				$tlist = D($this->TTrace)->findmoreTrace($where, $field);
				$totalgeren = $tlist['SUM1'];

				//总金额统计
				$total_2 	  = $this->partner_statistics2($where.$where_1);
				$totalall2	  = $total_2[0]['total_2'];

				$resdata 	  = array(
					'count'	  => $count,
					'amt'	  => setMoney($totalgeren, 2, 2),
					'total'	  => setMoney($totalall2, 2, 2),
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$field  = 'k.BRANCH_FEE1 as K_BRANCH_FEE1,j.BRANCH_FEE1 as J_BRANCH_FEE1,j.CADF_FEE1,j.NTAX_FEE1,j.EATF_FEE1,';
			$field .= 'k.BRANCH_MAP_ID2,k.BRANCH_FEE2,j.ACQ_BRANCH_MAP_ID2,j.ACQ_BRANCH_FEE2,j.ISS_BRANCH_MAP_ID2,j.ISS_BRANCH_FEE2,';
			$field .= 'k.PARTNER_MAP_ID1,k.PARTNER_FEE1,j.ACQ_PARTNER_MAP_ID1,j.ACQ_PARTNER_FEE1,j.ISS_PARTNER_MAP_ID1,j.ISS_PARTNER_FEE1,j.ISS_LCF_FEE1,j.ISS_LCA_FEE1,';			
			$field .= 'k.PARTNER_MAP_ID2,k.PARTNER_FEE2,j.ACQ_PARTNER_MAP_ID2,j.ACQ_PARTNER_FEE2,j.ISS_PARTNER_MAP_ID2,j.ISS_PARTNER_FEE2,';
			$field .= 'k.PARTNER_MAP_ID3,k.PARTNER_FEE3,j.ISS_PARTNER_MAP_ID3,j.ISS_PARTNER_FEE3,j.PARTNER_MAP_ID3A,j.PARTNER_FEE3A,j.PARTNER_MAP_ID3B,j.PARTNER_FEE3B';				
			$list  = D($this->TTrace)->getTracelist($where, 't.*,k.TRAFICC_FEE,k.CON_RES_FEE,j.CON_FEE,'.$field, $fiRow.','.$liRow);
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
						if($partner_map_id == $val['ISS_PARTNER_MAP_ID3']){
							$pingtai += $val['ISS_PARTNER_FEE3'];
						}				
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
			
			//分页参数
			$this->assign ( 'totalCount', 	C('PAGE_COUNT')==count($list) ? 1 : 0 );
	       	$this->assign ( 'numPerPage', 	'' );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'list', 		$list );	
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
			'SHOP_NO'			=>	I('SHOP_NO'),
			'VIP_CARDNO'		=>	I('VIP_CARDNO')
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
		//商户号
		if($post['SHOP_NO']) {
			$where .= " and t.SHOP_NO = '".$post['SHOP_NO']."'";
		}	
		//会员
		if($post['VIP_ID'] != ''){
			$where .= $post['VIP_ID'] ? " and t.VIP_ID > 0" : " and t.VIP_ID = 0";
		}
		//会员卡号
		if($post['VIP_CARDNO']){
			$where .= " and t.VIP_CARDNO = '".$post['VIP_CARDNO']."'";
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