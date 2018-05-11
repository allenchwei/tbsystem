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
		$this->TTransferls	= 'TTransferls';
	}
	
	/*
	* 交易流水查询
	**/
	public function trace() {
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
			if($post['MOBILE']){
				$shopModel = M('shop');
				$info = $shopModel->where(array('MOBILE'=>$post['MOBILE']))->find();
				if($info){
					$where .= " and ".$flag."SHOP_NO='".$info['SHOP_NO']."'";
				}else{
					$this->wrong('手机号码输入错误或不存在');
				}
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
				$where .= " and (".$flag."ORDER_NO like '%".$post['ORDER_NO']."%' or ".$flag."TRACE_INDEX1 like '%".$post['ORDER_NO']."%' or ".$flag."DOWN_TRADE_NO like '%".$post['ORDER_NO']."%')";
			}
			
			//来源
			if($post['SOURCE']!='') {
			$where .= " and ".$flag."SOURCE = '".$post['SOURCE']."'";
			}
					
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			
			if($home['HOST_MAP_ID']>0){
				$where .= " and ".$flag."SHOP_NO in (select SHOP_NO from pai_db_jfb.a_shopppp where HOST_MAP_ID=".$home['HOST_MAP_ID'].")";
			}
			//OEM下放后台权限处理
			if($home['CHANNEL_MAP_ID']>0){
				$where .= " and ".$flag."CHANNEL_MAP_ID=".$home['CHANNEL_MAP_ID'];
				$roleModel = M('role');
				$role = $roleModel->field('ROLE_PID')->where(array('ROLE_ID'=>$home['ROLE_ID'],'ROLE_STATUS'=>1))->find();
				if ($role['ROLE_PID'] != 1) {
					if (empty($post['MOBILE'])) {
						$this->wrong('请选择条件查找');
					}
					// if($post['ORDER_NO']) {
					// 	$where .= " and (".$flag."ORDER_NO = ".$post['ORDER_NO']." or ".$flag."TRACE_INDEX1 = ".$post['ORDER_NO']." or ".$flag."DOWN_TRADE_NO = ".$post['ORDER_NO'].")";
					// }
				}
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
		$where = "1=1";
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
		$this->assign('home', 			$home);
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
		$home = session('HOME');
		$this->assign('home', 		$home );
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
			'SOURCE'		=>	I('SOURCE'),
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
		//来源
		if($post['SOURCE']!='') {
			$where .= " and ".$flag."SOURCE = '".$post['SOURCE']."'";
		}
		$home = session('HOME');
		//查看权限是否为总部
		if ($home['CHANNEL_MAP_ID'] != 0) {
			$where .= " and ".$flag."CHANNEL_MAP_ID = '".$home['CHANNEL_MAP_ID']."'";
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
			// if($post['IS_SHARE_FEE']) {
				// $limit = ' limit '.$bRow.','.$eRow;
				//获取列表数据
				// $list_sql = "select t.*, j.JFB_FEE, j.CON_FEE, j.PLAT_FEE FROM T_JFBLS j LEFT JOIN t_trace t ON(t.SYSTEM_REF = j.SYSTEM_REF) WHERE ".$where." ORDER BY t.SYSTEM_DATE desc, t.SYSTEM_TIME desc".$limit;
				// $list = $Model->query($list_sql);
			// }else{
				$list = D($this->TTrace)->getNewsTracelist($where, '*', $bRow.','.$eRow);
			// }		
			//导出操作
			$xlsname = '交易流水文件('.($p+1).')';
			$xlscell = array(
				// array('SOURCE',		'来源'),
				array('TRANS_NAME',		'交易类型'),
				// array('BRANCH_MAP_ID',	'商户归属'),
				array('SHOP_NAME',		'商户名称'),
				// array('SHOP_NAMEAB',	'商户简称'),
				// array('CARD_NO',		'银行卡号'),
				array('ORDER_NO',		'订单号'),
				array('TRANS_AMT',		'交易金额'),
				array('TRACE_STATUS',	'结果'),		
				// array('VIP_CARDNO',		'会员卡号'),
				// array('JIFENLV',		'积分率'),
				array('SYSTEM_DATE',	'系统时间'),
				array('POS_DATE',		'交易时间'),
				array('DOWN_TRADE_NO',	'下游流水'),
				// array('JFB_FEE',		'越满分润'),
				// array('CON_FEE',		'平台分润'),
				// array('PLAT_FEE',		'个人分润'),
				// array('SHOP_NO',		'商户编码'),
				// array('POS_NO',			'POS编号'),
				// array('POS_TRACE',		'POS流水'),
			);
			if ($home['CHANNEL_MAP_ID'] == 0) {
				$JIFENLV = array(
					array('JIFENLV','积分率'),
					array('SHOP_MDR','交易手续率'),
					array('SHOP_AMT','结算金额'),
					array('ACQ_BRANCH_FEE2','一级分润'),
					array('ACQ_PARTNER_FEE1','二级分润'),
					array('THR_FEE','三级分润'),
				);
				$xlscell = array_merge($xlscell,$JIFENLV);
			}
			$xlsarray = array();
			$jfblsModel = M('jfbls', DB_PREFIX_TRA, DB_DSN_TRA);
			foreach($list as $val){
				$jfbls = $jfblsModel->field('ACQ_BRANCH_FEE2,ACQ_PARTNER_FEE1,ACQ_PARTNER_FEE2')->where(array('SYSTEM_REF'=>$val['SYSTEM_REF']))->find();
				$shopdata = D('MShop')->findShop("SHOP_NO = '".$val['SHOP_NO']."'", 'SHOP_NAME');
				$xlsarray[] = array(
					// 'SOURCE'	=>	$val['SOURCE']?'线上':'线下',
					'TRANS_NAME'	=>	$val['TRANS_NAME'],
					// 'BRANCH_MAP_ID'	=>	get_branch_name($val['SBRANCH_MAP_ID'], $val['SPARTNER_MAP_ID']),
					'SHOP_NAME'		=>	$shopdata['SHOP_NAME'],
					// 'SHOP_NAMEAB'	=>	$val['SHOP_NAMEAB'],
					// 'CARD_NO'		=>	setCard_no($val['CARD_NO']),
					'ORDER_NO'		=>	$val['ORDER_NO'],
					'TRANS_AMT'		=>	setMoney($val['TRANS_AMT'], 2, 2),
					'TRACE_STATUS'	=>	C('TRACE_STATUS')[$val['TRACE_STATUS']],
					// 'VIP_CARDNO'	=>	$val['VIP_CARDNO']."\t",
					'JIFENLV'		=>	setMoney($val['PER_FEE'], 2, 2),
					'SYSTEM_DATE'	=>	$val['SYSTEM_DATE'].' '.$val['SYSTEM_TIME']."\t",
					'POS_DATE'		=>	$val['POS_DATE'].' '.$val['POS_TIME']."\t",
					'DOWN_TRADE_NO'	=>	$val['DOWN_TRADE_NO'],
					'ACQ_BRANCH_FEE2'	=>	setMoney($jfbls['ACQ_BRANCH_FEE2'], 2, 2),
					'ACQ_PARTNER_FEE1'	=>	setMoney($jfbls['ACQ_PARTNER_FEE1'], 2, 2),
					'ACQ_PARTNER_FEE2'	=>	setMoney($jfbls['ACQ_PARTNER_FEE2'], 2, 2),
					'SHOP_MDR'	=>	setMoney($val['SHOP_MDR'], 2, 2),
					'SHOP_AMT'	=>	setMoney($val['SHOP_AMT'], 2, 2),
					// 'JFB_FEE'		=>	setMoney($val['JFB_FEE'], 2, 2),
					// 'CON_FEE'		=>	setMoney($val['CON_FEE'], 2, 2),
					// 'PLAT_FEE'		=>	setMoney($val['PLAT_FEE'], 2, 2),
					// 'SHOP_NO'		=> 	$val['SHOP_NO'],
					// 'POS_NO'		=> 	$val['POS_NO'],
					// 'POS_TRACE'		=> 	$val['POS_TRACE'],
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
				'SOURCE'			=>	I('SOURCE'),
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
			//来源
			// if($post['SOURCE']) {
			$where .= " and t.SOURCE = '".$post['SOURCE']."'";
			// }
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
				'SOURCE'		=>	I('SOURCE'),
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
			// $where = "t.TRACE_RETCODE='00' and t.TRACE_REVERFLAG=0 and t.TRANS_AMT>0 and t.TRANS_SUBID!=41 and t.TRANS_SUBID !=42 and j.JFB_FEE>0 AND t.TRACE_VOIDFLAG = 0 AND t.TRACE_REFUNDFLAG = 0";
			$where = "t.SOURCE=".$post['SOURCE']." and t.TRACE_RETCODE='00' and t.TRACE_REVERFLAG=0 and t.TRANS_AMT>0 and t.TRANS_SUBID!=41 and t.TRANS_SUBID !=42 AND t.TRACE_VOIDFLAG = 0 AND t.TRACE_REFUNDFLAG = 0";
			if($post['SOURCE'] == 0){
				$where .= " and j.JFB_FEE>0";
			}else{
				$where .= " and t.VIP_MOBILE<>0 and j.CON_FEE>0";
			}
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

	/*
	* 交易流水查询
	**/
	public function transferls() {
		$home = session('HOME');
		$post = I('post');
		//===优化统计===
		$ajax = I('ajax');
		// var_dump($ajax);exit;
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
				'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
				'SHOP_NAME'			=>	I('SHOP_NAME'),
				'MOBILE'			=>	I('MOBILE'),
				'RECEIVE_NAME'		=>	I('RECEIVE_NAME'),
				'RECEIVE_MOBILE'	=>	I('RECEIVE_MOBILE'),
				'STATUS'			=>	I('STATUS'),
				'TRANS_AMT_A'		=>	I('TRANS_AMT_A'),
				'TRANS_AMT_B'		=>	I('TRANS_AMT_B'),
				'SOURCE'			=>	I('SOURCE'),
			);
		}
		//===结束===
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "transferls"){
			$where = "1=1";
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and ".$flag."CREATE_TIME >= '".date('Y-m-d',strtotime($post['SYSTEM_DATE_A'])).' 00:00:00'."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and ".$flag."CREATE_TIME <= '".date('Y-m-d',strtotime($post['SYSTEM_DATE_B'])).' 23:59:59'."'";
			}
			//处理结果
			if($post['STATUS'] != '') {
				$where .= " and ".$flag."STATUS = '".$post['STATUS']."'";
			}
			//交易金额	开始
			if($post['TRANS_AMT_A']) {
				$where .= " and ".$flag."TRANS_AMT >= '".setMoney($post['TRANS_AMT_A'], '2')."'";
			}
			//交易金额	结束
			if($post['TRANS_AMT_B']) {
				$where .= " and ".$flag."TRANS_AMT <= '".setMoney($post['TRANS_AMT_B'], '2')."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and ".$flag."SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//商户手机号
			if($post['MOBILE']) {
				$where .= " and ".$flag."MOBILE = '".$post['MOBILE']."'";
			}
			//收款商户名称
			if($post['RECEIVE_NAME']) {
				$where .= " and ".$flag."RECEIVE_NAME like '%".$post['RECEIVE_NAME']."%'";
			}
			//收款商户手机号
			if($post['RECEIVE_MOBILE']) {
				$where .= " and ".$flag."RECEIVE_MOBILE = '".$post['RECEIVE_MOBILE']."'";
			}
			
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');

			//===优化统计===
			// if($ajax == 'loading'){
			// 	$count = D($this->TTrace)->countNewsTrace($where);
			// 	$data  = D($this->TTrace)->findTrace($where, 'sum(TRANS_AMT) as AMT');
			// 	$resdata = array(
			// 		'count'	=>	$count,
			// 		'amt'	=>	setMoney($data['AMT'], 2, 2),
			// 	);
			// 	$this->ajaxReturn($resdata);
			// }
			//===结束=======
			$list  = D($this->TTransferls)->getNewsTransferlslist($where, '*', $fiRow.','.$liRow);
			
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
		$this->assign('trace_status', 		C('TRANSFERLS_STATUS') );	//流水标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}

	/*
	* 转账流水查询	导出
	**/
	public function transferls_export() {
		//临时加大PHP占用内存
		ini_set('memory_limit', '256M'); //内存限制
		set_time_limit(0);
		$post  = array(
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
			'SHOP_NAME'			=>	I('SHOP_NAME'),
			'MOBILE'			=>	I('MOBILE'),
			'RECEIVE_NAME'		=>	I('RECEIVE_NAME'),
			'RECEIVE_MOBILE'	=>	I('RECEIVE_MOBILE'),
			'STATUS'			=>	I('STATUS'),
			'TRANS_AMT_A'		=>	I('TRANS_AMT_A'),
			'TRANS_AMT_B'		=>	I('TRANS_AMT_B'),
			'SOURCE'			=>	I('SOURCE'),
		);
		$where = "1=1";
		//交易日期	开始
		if($post['SYSTEM_DATE_A']) {
			$where .= " and ".$flag."CREATE_TIME >= '".date('Y-m-d',strtotime($post['SYSTEM_DATE_A'])).' 00:00:00'."'";
		}
		//交易日期	结束
		if($post['SYSTEM_DATE_B']) {
			$where .= " and ".$flag."CREATE_TIME <= '".date('Y-m-d',strtotime($post['SYSTEM_DATE_B'])).' 23:59:59'."'";
		}
		//处理结果
		if($post['STATUS'] != '') {
			$where .= " and ".$flag."STATUS = '".$post['STATUS']."'";
		}
		//交易金额	开始
		if($post['TRANS_AMT_A']) {
			$where .= " and ".$flag."TRANS_AMT >= '".setMoney($post['TRANS_AMT_A'], '2')."'";
		}
		//交易金额	结束
		if($post['TRANS_AMT_B']) {
			$where .= " and ".$flag."TRANS_AMT <= '".setMoney($post['TRANS_AMT_B'], '2')."'";
		}
		//商户名称
		if($post['SHOP_NAME']) {
			$where .= " and ".$flag."SHOP_NAME like '%".$post['SHOP_NAME']."%'";
		}
		//商户手机号
		if($post['MOBILE']) {
			$where .= " and ".$flag."MOBILE = '".$post['MOBILE']."'";
		}
		//收款商户名称
		if($post['RECEIVE_NAME']) {
			$where .= " and ".$flag."RECEIVE_NAME like '%".$post['RECEIVE_NAME']."%'";
		}
		//收款商户手机号
		if($post['RECEIVE_MOBILE']) {
			$where .= " and ".$flag."RECEIVE_MOBILE = '".$post['RECEIVE_MOBILE']."'";
		}
		$count = D($this->TTransferls)->countNewstransferls($where);
		
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
			$list = D($this->TTransferls)->getNewsTransferlslist($where, '*', $bRow.','.$eRow);
			//导出操作
			$xlsname = '转账流水文件('.($p+1).')';
			$xlscell = array(
				array('SHOP_NAME',			'商户名称'),
				array('MOBILE',				'商户手机号'),
				array('RECEIVE_NAME',		'收款商户名称'),
				array('RECEIVE_MOBILE',		'收款商户手机号'),
				array('TRANS_AMT',			'交易金额'),
				array('STATUS',				'状态'),
				array('CREATE_TIME',		'转账时间'),
				array('SUCC_TIME',			'成功时间'),
				array('RES',				'描述')
			);		
			$xlsarray = array();
			foreach($list as $val){
				// $shopdata = D('MShop')->findShop("SHOP_NO = '".$val['SHOP_NO']."'", 'SHOP_NAME');
				$xlsarray[] = array(
					'SHOP_NAME'		=>	$val['SHOP_NAME'],
					'MOBILE'		=>	$val['MOBILE'],
					'RECEIVE_NAME'	=>	$val['RECEIVE_NAME'],
					'RECEIVE_MOBILE'=>	$val['RECEIVE_MOBILE'],
					'TRANS_AMT'		=>	setMoney($val['TRANS_AMT'], 2, 2),
					'STATUS'		=> 	C('TRANSFERLS_STATUS')[$val['STATUS']],
					'CREATE_TIME'	=> 	$val['CREATE_TIME'],
					'SUCC_TIME'		=> 	$val['SUCC_TIME'],
					'RES'			=> 	$val['RES']
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	/*
	 * CRYSTAL 2017.08.02
	 * 提现记录查询
	 **/
	public function withdraw() {
		$post = I('post');  
		$post['CREATE_TIME_A'] = $post['CREATE_TIME_A'] ? $post['CREATE_TIME_A'] : date('Y-m-d');
		$post['CREATE_TIME_B'] = $post['CREATE_TIME_B'] ? $post['CREATE_TIME_B'] : date('Y-m-d');
		
		if($post['submit'] == "withdraw"){
			$where = "1=1";
			//提现日期	开始
			if($post['CREATE_TIME_A']) {
				$where .= " and UPDATE_TIME >= '".$post['CREATE_TIME_A']."'";
			}
			//提现日期	结束
			if($post['CREATE_TIME_B']) {
				$where .= " and UPDATE_TIME <= '".$post['CREATE_TIME_B']." 23:59:59'";
			}
			//处理结果
			if($post['STATUS'] != '') {
				$where .= " and STATUS = '".$post['STATUS']."'";
			}
			//提现金额	开始
			if($post['AMOUNT_A']) {
				$where .= " and AMOUNT >= '".setMoney($post['AMOUNT_A'], '2')."'";
			}
			//提现金额	结束
			if($post['TRANS_AMT_B']) {
				$where .= " and AMOUNT <= '".setMoney($post['AMOUNT_B'], '2')."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//银行卡
			if($post['BANKACCT_NO']) {
				$where .= " and BANKACCT_NO like '%".$post['BANKACCT_NO']."%'";
			}
			//订单号
			if($post['ORDER_NO']) {
				$where .= " and ORDER_NO like '%".$post['ORDER_NO']."%'";
			}
			
			//渠道
			if($post['CHANNEL_MAP_ID']) {
				$where .= " and CHANNEL_MAP_ID = ".$post['CHANNEL_MAP_ID'];
			}
			
			//代付公司
			if($post['DFCO_MAP_ID']) {
				$where .= " and DFCO_MAP_ID = ".$post['DFCO_MAP_ID'];
			}
			
			//分页
			$withdrawModel = M('withdraw', DB_PREFIX_TRA, DB_DSN_TRA);
			$count = $withdrawModel->where($where)->count();
			// echo $withdrawModel->getLastSql();
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = $withdrawModel->where($where)->limit($p->firstRow.','.$p->listRows)->order('CREATE_TIME DESC')->select();
			$data  = $withdrawModel->field('sum(AMOUNT) as AMT')->where($where)->limit($p->firstRow.','.$p->listRows)->find();
			
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'list', 		$list );
			$this->assign ( 'amt', 			$data['AMT'] );
		}
		$home = session('HOME');
		$channelModel = M('channel');
		$channel_list = $channelModel->select();
		
		$dfcoModel = M('dfco');
		$dfco_list = $dfcoModel->select();
		
		$this->assign ( 'postdata', 	$post );
		
		//Excel导出参数
		unset($post['submit']);
		$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		$this->assign ( 'channel_list', 	$channel_list );
		$this->assign ( 'dfco_list', 	$dfco_list );
		$this->assign ( 'home', 	$home );
		
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
		$withdraw_status = array(
			0 => '成功',
			1 => '处理中',
			2 => '失败'
		);
		$this->assign('timedata', 			$timedata);
		$this->assign('status', 		$withdraw_status );	//提现标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	/*
	* 提现记录查询	详情
	**/
	public function withdraw_show($tpl='withdraw_show') {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$withdrawModel = M('withdraw', DB_PREFIX_TRA, DB_DSN_TRA);
		$lapModel = M('lap');
		$info = $withdrawModel->where(array('ID'=>$id))->find();
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$lap = $lapModel->where(array('SHOP_MAP_ID'=>$info['SHOP_MAP_ID']))->find();
		$withdraw_status = array(
			0 => '成功',
			1 => '处理中',
			2 => '失败'
		);
		$this->assign('status', 		$withdraw_status );	//提现标志
		$this->assign('info', 				$info);
		$this->assign('lap', 				$lap);
		$this->display($tpl);
	}
	
	/*
	* 提现管理 审核
	**/
	public function withdraw_check() {
		$post = I('post');
		$withdrawModel = M('withdraw', DB_PREFIX_TRA, DB_DSN_TRA);
		if ($post['submit'] == "withdraw_check") {
			if(empty($post['ID']) || $post['CHECK_POINT']==''){
				$this->wrong("参数数据出错！");
			}
			$home = session('HOME');
			$m = M();
			
			$info = $withdrawModel->where(array('ID'=>$post['ID']))->find();
			
			if($info['STATUS'] != 1){
				$this->wrong("当前状态不能进行审核！");
			}
			
			$lapModel = M('lap');
			$lap = $lapModel->where(array('SHOP_MAP_ID'=>$info['SHOP_MAP_ID']))->find();
			//验证MAC押码
			$appendStr = getLD($lap['SHOP_MAP_ID']).getLD($lap['ACCT_TBAL']).getLD($lap['ACCT_VBAL']).
						getLD($lap['ACCT_CBAL']).getLD($lap['ACCT_WBAL']).getLD($lap['ACCT_DBAL']);
			$mac = strtoupper(hash_hmac('md5', $appendStr, MACKEY));
			if($mac != $lap['MAC'] && $lap['MAC']!= 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF'){
				$this->wrong("商户账上金额有误！");
			}
			
			$totalAmt = $lap['ACCT_VBAL']+$lap['ACCT_DBAL'];
			//实际金额不能小于可用余额 || 冻结余额不能小于提现金额 || 可用余额加冻结金额不能大于实际余额
			if($lap['ACCT_TBAL']<$lap['ACCT_VBAL'] || $lap['ACCT_DBAL']<$info['AMOUNT'] 
			|| $totalAmt>$lap['ACCT_TBAL']){
				$this->wrong("提现金额异常！");
			}
			
			//组装数据
			$wd_data = array(
				'NOTE' => $info['NOTE'].'<br/>'.date('Y-m-d H:i:s').' '.$post['NOTE'],
				'UPDATE_TIME' => date('Y-m-d H:i:s'),
				'OPERATE_USERID' => $home['USER_ID'],
				'OPERATE_USERNAME' => $home['USER_NAME'],
			);
			$res = $withdrawModel->where(array('ID'=>$post['ID'], 'STATUS'=>1))->save($wd_data);
			$total = $info['AMOUNT']+$info['WD_FEE'];
			if($post['CHECK_POINT'] == 2){
				if($post['NOTE'] == '代付失败009'){
					$ACCT_TBAL = $lap['ACCT_TBAL']+$total;
					$ACCT_VBAL = $lap['ACCT_VBAL']+$total;
					$ACCT_CBAL = $lap['ACCT_CBAL'];
					$ACCT_WBAL = $lap['ACCT_WBAL'];
					$ACCT_DBAL = $lap['ACCT_DBAL'];
				}else{
					$ACCT_TBAL = $lap['ACCT_TBAL'];
					$ACCT_VBAL = $lap['ACCT_VBAL']+$total;
					$ACCT_CBAL = $lap['ACCT_CBAL'];
					$ACCT_WBAL = $lap['ACCT_WBAL'];
					$ACCT_DBAL = $lap['ACCT_DBAL']-$total;
				}
				$appendStr = getLD($lap['SHOP_MAP_ID']).getLD($ACCT_TBAL).getLD($ACCT_VBAL).
							getLD($ACCT_CBAL).getLD($ACCT_WBAL).getLD($ACCT_DBAL);
			}else{
				$ACCT_TBAL = $lap['ACCT_TBAL']-$total;
				$ACCT_VBAL = $lap['ACCT_VBAL'];
				$ACCT_CBAL = $lap['ACCT_CBAL'];
				$ACCT_WBAL = $lap['ACCT_WBAL'];
				$ACCT_DBAL = $lap['ACCT_DBAL']-$total;
				$appendStr = getLD($lap['SHOP_MAP_ID']).getLD($ACCT_TBAL).getLD($ACCT_VBAL).
							getLD($ACCT_CBAL).getLD($ACCT_WBAL).getLD($ACCT_DBAL);
			}
			$mac = strtoupper(hash_hmac('md5', $appendStr, MACKEY));
			//组装数据
			$lapData = array(
				'ACCT_TBAL' => $ACCT_TBAL,
				'ACCT_VBAL' => $ACCT_VBAL,
				'ACCT_CBAL' => $ACCT_CBAL,
				'ACCT_WBAL' => $ACCT_WBAL,
				'ACCT_DBAL' => $ACCT_DBAL,
				'MAC' => $mac
			);
			
			if($post['CHECK_POINT'] != 2){
				if($info['AMOUNT'] == 0){
					$res = $withdrawModel->where(array('ID'=>$post['ID'], 'STATUS'=>1))->save(array('STATUS'=>0));
				}else{
					# code begin
					# 调用代付
					$url = PHP_API_URL.'index.php/Home/Account/signDistill';
					$data = array(
						'shopMapId' => $lap['SHOP_MAP_ID'],
						'withdrawId' => $post['ID'],
						'nonce_str' => time(),
					);
					$data["sign"] = szSign($data, C('HMKEY'));
					$jsonStr = json_encode($data);
					$result = getCurlDataByjson($url, $jsonStr);
					Add_LOG('Trace',__LINE__ . ' | ' . __FUNCTION__ . ' ' . json_encode($result));
					if ($result->status != 200) {
						$this->wrong($result->message);
					}
					# code end
				}
			}else{
				$res = $withdrawModel->where(array('ID'=>$post['ID'], 'STATUS'=>1))->save(array('STATUS'=>2));
			}
			
			$res2 = $lapModel->where(array('SHOP_MAP_ID'=>$info['SHOP_MAP_ID']))->save($lapData);
			if(!$res || !$res2){
				$m->rollback();
				$this->wrong("审核操作失败！");
			}
			$m->commit();
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		//判断当前状态是否符合审核操作
		$info = $withdrawModel->where(array('ID'=>$id))->find();
		if ($info['STATUS'] != 1) {
			$this->wrong('当前状态不允许审核操作');
		}
		$this->withdraw_show('withdraw_check');
	}
	/*
	* 2017.12.04
	* 提现流水	导出
	**/
	public function withdraw_export() {
		//临时加大PHP占用内存
		ini_set('memory_limit', '256M'); //内存限制
		set_time_limit(0);
		$post  = array(
			'CREATE_TIME_A'		=>	I('CREATE_TIME_A'),
			'CREATE_TIME_B'		=>	I('CREATE_TIME_B'),
			'STATUS'		=>	I('STATUS'),
			'CHANNEL_MAP_ID'		=>	I('CHANNEL_MAP_ID'),
			'DFCO_MAP_ID'		=>	I('DFCO_MAP_ID'),
		);
		//交易日期	开始
		if($post['CREATE_TIME_A']) {
			$where['w.UPDATE_TIME'] = array('egt', $post['CREATE_TIME_A']);
		}
		if($post['CREATE_TIME_B']) {
			$where['w.UPDATE_TIME'] = array('elt', $post['CREATE_TIME_B']);
		}
		if($post['CREATE_TIME_A'] && $post['CREATE_TIME_B']){
			$where['w.UPDATE_TIME'] = array(array('egt',$post['CREATE_TIME_A'].' 00:00:00'), array('elt',$post['CREATE_TIME_B'].' 23:59:59'));
		}
		//状态
		if($post['STATUS']!='') {
			$where['w.STATUS'] = $post['STATUS'];
		}
		//渠道
		if($post['CHANNEL_MAP_ID']) {
			$where['w.CHANNEL_MAP_ID'] = $post['CHANNEL_MAP_ID'];
		}
		//代付公司
		if($post['DFCO_MAP_ID']) {
			$where['w.DFCO_MAP_ID'] = $post['DFCO_MAP_ID'];
		}
		$withModel = M("withdraw", DB_PREFIX_TRA, DB_DSN_TRA);
		$count = $withModel->alias('w')->where($where)->count();

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
			$list = $withModel->alias('w')
					->field('dfco.DFCO_NAME,w.SHOP_NAME,w.AMOUNT,w.BANK_NAME,w.BANKACCT_NAME,w.BANKACCT_NO,w.UPDATE_TIME,w.WD_FEE')
					->join('pai_db_jfb.a_dfco dfco on dfco.DFCO_MAP_ID=w.DFCO_MAP_ID', 'LEFT')
					->where($where)->limit($bRow.','.$eRow)->select();
			//导出操作
			$xlsname = '提现流水文件('.($p+1).')';
			$xlscell = array(
				array('UPDATE_TIME',			'日期'),
				array('SHOP_NAME',		'商户名称'),
				array('AMOUNT',		'到账金额'),
				array('WD_FEE',		'手续费'),
				array('BANK_NAME',			'银行名称'),
				array('BANKACCT_NAME',				'银行姓名'),
				array('BANKACCT_NO',		'银行卡号'),
				array('DFCO_NAME',			'代付公司'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'UPDATE_TIME'	       =>	$val['UPDATE_TIME'],
					'SHOP_NAME'		       =>	$val['SHOP_NAME'],
					'AMOUNT'	           =>	setMoney($val['AMOUNT'], 2, 2),
					'WD_FEE'               =>	setMoney($val['WD_FEE'], 2, 2),
					'BANK_NAME'            => 	$val['BANK_NAME'],
					'BANKACCT_NAME'	       => 	$val['BANKACCT_NAME'],
					'BANKACCT_NO'          => 	$val['BANKACCT_NO'],
					'DFCO_NAME'            => 	$val['DFCO_NAME'],
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	//20170814 CRYSTAL 运营商 创业合伙人 三级分润 月结算费用列表
	function month_settle(){
		$post = I('post');  
		$post['SETTLE_DATE'] = $post['SETTLE_DATE'] ? $post['SETTLE_DATE'] : date('Y-m');
		
		if($post['submit'] == "month_settle"){
			//清算日期
			if($post['SETTLE_DATE']) {
				$wh['_string'] = "left(m.CREATE_TIME,7)='".$post['SETTLE_DATE']."'";
			}
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			
			$settleModel = M("trace_settle", DB_PREFIX_TRA, DB_DSN_TRA);
			$count = $settleModel->alias('m')->where($wh)->count();
			$list = $settleModel->alias('m')
					->field('m.ORDER_NO,left(m.CREATE_TIME,7) CREATE_TIME,t.TRANS_AMT,t.SHOP_AMT,t.PER_FEE
					,ls.ACQ_BRANCH_NAMEAB2,ls.ACQ_BRANCH_MAP_ID2,ls.ACQ_BRANCH_FEE2
					,ls.ACQ_PARTNER_NAMEAB1,ls.ACQ_PARTNER_MAP_ID1,ls.ACQ_PARTNER_FEE1
					,ls.ACQ_PARTNER_NAMEAB2,ls.ACQ_PARTNER_MAP_ID2,ls.ACQ_PARTNER_FEE2
					,m.PARTNER_NAMEAB1,m.PARTNER_MAP_ID1,m.PARTNER_FEE1,m.PARTNER_AMOUNT1
					,m.PARTNER_NAMEAB2,m.PARTNER_MAP_ID2,m.PARTNER_FEE2,m.PARTNER_AMOUNT2
					,ls.ACQ_BRANCH_FEE2+ls.ACQ_PARTNER_FEE1+ls.ACQ_PARTNER_FEE2+m.PARTNER_AMOUNT1+m.PARTNER_AMOUNT2 TOTAL_AMT
					')
					->join('tra_db_jfb.t_trace t on t.ORDER_NO=m.ORDER_NO', 'LEFT')
					->join('tra_db_jfb.t_jfbls ls on ls.SYSTEM_REF=t.SYSTEM_REF', 'LEFT')
					->where($wh)->limit($fiRow.','.$liRow)->select();
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign('list', $list);
		}
		
		$this->assign ( 'postdata', 	$post );
		
		//Excel导出参数
		unset($post['submit']);
		$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		
		//分润列表
		$settleModel = M("trace_settle", DB_PREFIX_TRA, DB_DSN_TRA);
		$timedata = $settleModel->field("left(CREATE_TIME,7) SETTLE_DATE")->where('1=1')->group("left(CREATE_TIME,7)")->select();
		
		//渠道
		$channelModel = M("channel");
		$channel_list = $channelModel->field("CHANNEL_MAP_ID,CHANNEL_NAME")->where(array('CHANNEL_STATUS'=>0))->select();
		
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('tax_ticket_flag',	C('TAX_TICKET_FLAG'));	//发票标志
		// dump($timedata);
		$this->assign('timedata', 			$timedata);
		$this->assign('channel_list', 			$channel_list);
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	/*
	* 分润流水	导出
	**/
	public function month_settle_export() {
		//临时加大PHP占用内存
		ini_set('memory_limit', '256M'); //内存限制
		set_time_limit(0);
		$post  = array(
			'SETTLE_DATE'		=>	I('SETTLE_DATE'),
		);
		//交易日期	开始
		if($post['SETTLE_DATE']) {
			$where['_string'] = "left(m.CREATE_TIME, 7)='".$post['SETTLE_DATE']."'";
		}
		$settleModel = M("trace_settle", DB_PREFIX_TRA, DB_DSN_TRA);
		$count = $settleModel->alias('m')->where($where)->count();
		
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
			$list = $settleModel->alias('m')
					->field('m.ORDER_NO,left(m.CREATE_TIME,7) CREATE_TIME,t.TRANS_AMT,t.SHOP_AMT,t.PER_FEE
					,ls.ACQ_BRANCH_NAMEAB2,ls.ACQ_BRANCH_MAP_ID2,ls.ACQ_BRANCH_FEE2
					,ls.ACQ_PARTNER_NAMEAB1,ls.ACQ_PARTNER_MAP_ID1,ls.ACQ_PARTNER_FEE1
					,ls.ACQ_PARTNER_NAMEAB2,ls.ACQ_PARTNER_MAP_ID2,ls.ACQ_PARTNER_FEE2
					,m.PARTNER_NAMEAB1,m.PARTNER_MAP_ID1,m.PARTNER_FEE1,m.PARTNER_AMOUNT1
					,m.PARTNER_NAMEAB2,m.PARTNER_MAP_ID2,m.PARTNER_FEE2,m.PARTNER_AMOUNT2
					,ls.ACQ_BRANCH_FEE2+ls.ACQ_PARTNER_FEE1+ls.ACQ_PARTNER_FEE2+m.PARTNER_AMOUNT1+m.PARTNER_AMOUNT2 TOTAL_AMT
					')
					->join('tra_db_jfb.t_trace t on t.ORDER_NO=m.ORDER_NO', 'LEFT')
					->join('tra_db_jfb.t_jfbls ls on ls.SYSTEM_REF=t.SYSTEM_REF', 'LEFT')
					->where($where)->limit($bRow.','.$eRow)->select();
			//导出操作
			$xlsname = '分润流水文件('.($p+1).')';
			$xlscell = array(
				array('CREATE_TIME',			'日期'),
				array('ORDER_NO',				'订单号'),
				array('TRANS_AMT',		'消费金额'),
				array('SHOP_AMT',		'净金额'),
				array('PER_FEE',			'扣率'),
				array('ACQ_BRANCH_NAMEAB2',				'姓名(一)'),
				array('ACQ_BRANCH_FEE2',		'返佣(一)'),
				array('ACQ_PARTNER_NAMEAB1',			'姓名(二)'),
				array('ACQ_PARTNER_FEE1',				'返佣(二)'),
				array('ACQ_PARTNER_NAMEAB2',			'姓名(三)'),
				array('ACQ_PARTNER_FEE2',				'返佣(三)'),
				array('PARTNER_NAMEAB1',			'姓名(运)'),
				array('PARTNER_AMOUNT1',				'返佣(运)'),
				array('PARTNER_FEE1',			'万几(运)'),
				array('PARTNER_NAMEAB2',			'姓名(创)'),
				array('PARTNER_AMOUNT2',				'返佣(创)'),
				array('PARTNER_FEE2',			'万几(创)'),
				array('TOTAL_AMT',			'统计'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'CREATE_TIME'	       =>	$val['CREATE_TIME'],
					'ORDER_NO'		       =>	$val['ORDER_NO'],
					'TRANS_AMT'	           =>	setMoney($val['TRANS_AMT'], 2, 2),
					'SHOP_AMT'             =>	setMoney($val['SHOP_AMT'], 2, 2),
					'PER_FEE'		       =>	setMoney($val['PER_FEE'], 2, 2),
					'ACQ_BRANCH_NAMEAB2'   => 	$val['ACQ_BRANCH_NAMEAB2'],
					'ACQ_BRANCH_FEE2'	   => 	setMoney($val['ACQ_BRANCH_FEE2'], 2, 2),
					'ACQ_PARTNER_NAMEAB1'  => 	$val['ACQ_PARTNER_NAMEAB1'],
					'ACQ_PARTNER_FEE1'	   => 	setMoney($val['ACQ_PARTNER_FEE1'], 2, 2),
					'ACQ_PARTNER_NAMEAB2'  => 	$val['ACQ_PARTNER_NAMEAB2'],
					'ACQ_PARTNER_FEE2'	   => 	setMoney($val['ACQ_PARTNER_FEE2'], 2, 2),
					'PARTNER_NAMEAB1'	   => 	$val['PARTNER_NAMEAB1'],
					'PARTNER_AMOUNT1'	   => 	setMoney($val['PARTNER_AMOUNT1'], 2, 2),
					'PARTNER_FEE1'	       => 	$val['PARTNER_FEE1'],
					'PARTNER_NAMEAB2'	   => 	$val['PARTNER_NAMEAB2'],
					'PARTNER_AMOUNT2'	   => 	setMoney($val['PARTNER_AMOUNT2'], 2, 2),
					'PARTNER_FEE2'	       => 	$val['PARTNER_FEE2'],
					'TOTAL_AMT'	           => 	setMoney($val['TOTAL_AMT'], 2, 2),
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	/*
	* 上福对账交易流水	导出
	**/
	public function sf_trace_export() {
		//临时加大PHP占用内存
		ini_set('memory_limit', '256M'); //内存限制
		set_time_limit(0);
		$post  = array(
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
		);

		// 交易日期	开始
		if(empty($post['SYSTEM_DATE_A'])) {
			$this->wrong('交易开始日期不能为空');
		}
		$bill_date = $post['SYSTEM_DATE_A'];
		//查找文件是否已存在
		$name = 'download_'.substr(date('Y', strtotime($bill_date)), 2).'_'.date('m', strtotime($bill_date)).'_'.date('d', strtotime($bill_date)).'.log';
		// echo $name;
		$path = './Public/file/apilog/';
		$file = $path.$name;
		if(!file_exists($file)){
			$bill_date = date('Ymd',strtotime($bill_date));
			$data = array(
				'bill_date' => $bill_date,
				'nonce_str' => 'ns'.time(),
				'sp_id' => '1076'
			);
			$url = 'http://api.shangfudata.com/gate/spsvr/trade/down';
			$data['sign'] = szSign($data, '964E61C99CF243B783471D7EED8A0AEF');
			// dump($data);
			$charge = getDownData($url, $data);
			$charge = str_replace('sp_id', '服务商编号', $charge);
			$charge = str_replace('mcht_no', '商户编号', $charge);
			$charge = str_replace('sp_trade_no', '服务商订单号', $charge);
			$charge = str_replace('trade_no', '系统订单号', $charge);
			$charge = str_replace('trade_type', '订单类型', $charge);
			$charge = str_replace('total_fee', '订单金额', $charge);
			$charge = str_replace('trade_time', '订单时间', $charge);
			$charge = str_replace('trade_state', '订单状态', $charge);
			$charge = str_replace('hand_fee', '手续费', $charge);
			$charge = str_replace('settle_fee', '结算金额', $charge);
			$charge = str_replace('settle_rate_type', '收费类型', $charge);
			$charge = str_replace('settle_rate', '收费标准', $charge);
			$charge = str_replace('settle_min_fee', '最低收费', $charge);
			$charge = str_replace('extra_rate_type', '服务服收费类型', $charge);
			$charge = str_replace('extra_rate', '服务费收费标准', $charge);
			$charge = str_replace('extra_min_fee', '服务费最低收费', $charge);
			Add_DWON_LOG($name,$charge);
		}
		
		$strPort = '';
		$strPort .= '<p><a href="index.php/Home/Trace/download_file?file='.$file.'"><button class="ch-btn-skin ch-btn-small ch-icon-copy">文件(1)</button></a></p>';

		$this->assign ( 'strPort', 	$strPort );
		
		$this->display('Public/export');exit;
		sleep(3);
		header( "Content-Disposition:  attachment;  filename=".$file); //告诉浏览器通过附件形式来处理文件
		header('Content-Length: ' . filesize($file)); //下载文件大小
		readfile($file);  //读取文件内容
	}
	
	function download_file(){
		$file = I('file');
		header( "Content-Disposition:  attachment;  filename=".$file); //告诉浏览器通过附件形式来处理文件
		header('Content-Length: ' . filesize($file)); //下载文件大小
		readfile($file);  //读取文件内容
	}

	//返佣管理
	public function rebate(){
		$post = I('post');
		$home = session('HOME');
		if($post['submit'] == "rebate"){
			$home = session('HOME');
			$where = "transferls.STATUS = 0 and transferls.FLAG = 2";
			if ($home['ROLE_NAME'] == OEM) {
				$where .= " and channel.CHANNEL_MAP_ID = " . $home['CHANNEL_MAP_ID'];
			}
			//商户ID
			if($post['SHOP_MAP_ID']) {
				$where .= " and lap.SHOP_MAP_ID = '".$post['SHOP_MAP_ID']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and transferls.SHOP_NAME = '".$post['SHOP_NAME']."'";
			}
			//手机号码
			if($post['MOBILE']) {
				$where .= " and transferls.MOBILE = '".$post['MOBILE']."'";
			}
			// $lapModel = M('lap');
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$transferlsModel = M('transferls', DB_PREFIX_TRA, DB_DSN_TRA);
			$lap = $transferlsModel->alias('transferls')
							->field('(sum(lap.ACCT_CBAL)-sum(lap.ACCT_CVBAL)) as ACCT_DJ,lap.ACCT_CBAL,lap.ACCT_CVBAL,transferls.SHOP_NAME,sum(transferls.TRANS_AMT) as ACCT_SUM,(sum(lap.ACCT_CVBAL)+sum(lap.ACCT_CBAL-lap.ACCT_CVBAL)+sum(transferls.TRANS_AMT)) as AMT_SUM,lap.SHOP_MAP_ID,transferls.MOBILE')
							->join('LEFT JOIN pai_db_jfb.a_lap lap on lap.SHOP_MAP_ID = transferls.SHOP_MAP_ID')
							->join('pai_db_jfb.a_shop shop on shop.SHOP_MAP_ID = transferls.SHOP_MAP_ID')
							->join('pai_db_jfb.a_channel channel on channel.CHANNEL_MAP_ID = shop.CHANNEL_MAP_ID')
							->where($where)
							->group('transferls.SHOP_NAME')
							->select();
			// echo $transferlsModel->getLastSql();
			//计算查询出的个数
			$count = $transferlsModel->query("SELECT COUNT(*) FROM (
				SELECT COUNT(*) FROM t_transferls AS transferls 
				LEFT JOIN pai_db_jfb.a_lap lap on lap.SHOP_MAP_ID = transferls.SHOP_MAP_ID 
				INNER JOIN pai_db_jfb.a_shop shop on shop.SHOP_MAP_ID = transferls.SHOP_MAP_ID 
				INNER JOIN pai_db_jfb.a_channel channel on channel.CHANNEL_MAP_ID = shop.CHANNEL_MAP_ID 
				WHERE $where 
				GROUP BY transferls.SHOP_NAME) a");
			// $count = 0;
			$count = $count[0]['COUNT(*)'];
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$this->assign ( 'list', 		  $lap );
			$this->assign ( 'totalCount', 	$count );
			$this->assign ( 'numPerPage', 	$p->listRows );
			$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		$this->display();
	}

	//返佣管理 -> 返佣记录
	public function rebate_show(){
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$traceModel = M('trace', DB_PREFIX_TRA, DB_DSN_TRA);
		$trace = $traceModel->alias('trace')
						  ->field("shop.SHOP_MAP_ID,shop.MOBILE as mobile,jfbls.SHOP_NAMEAB as userName,trace.TRANS_AMT as amount,trace.system_date as date1,trace.system_time as date2,
						  	ifnull(((CASE when jfbls.ACQ_PARTNER_MAP_ID2=$id then jfbls.ACQ_PARTNER_FEE2 else 0 end ) + (CASE when jfbls.ACQ_PARTNER_MAP_ID1 then jfbls.ACQ_PARTNER_FEE1 else 0 end ) + (CASE when jfbls.ACQ_BRANCH_MAP_ID2=$id then jfbls.ACQ_BRANCH_FEE2 else 0 end )),0) as commissionAmount")
						  ->join('pai_db_jfb.a_shop shop on shop.SHOP_NO = trace.SHOP_NO')
						  ->join('tra_db_jfb.t_jfbls jfbls on jfbls.SYSTEM_REF = trace.SYSTEM_REF')
						  ->where(array('shop.SHOP_STATUS'=>0,'trace.TRACE_STATUS'=>0,'jfbls.ACQ_BRANCH_MAP_ID2|jfbls.ACQ_PARTNER_MAP_ID1|jfbls.ACQ_PARTNER_MAP_ID2'=>$id))
						  ->select();
		// echo $traceModel->getLastSql();
		// var_dump($trace);
		$this->assign('info',$trace);
		$this->display();
	}
}
?>