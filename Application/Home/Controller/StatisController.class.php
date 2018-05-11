<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  统计分析
// +----------------------------------------------------------------------
class StatisController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();		//权限及登录验证
		$this->TTrace  = 'TTrace';	//商家交易
		$this->GVip    = 'GVip';	//会员表
		$this->MDevice = 'MDevice';	//设备POS机
		$this->TJfbls  = 'TJfbls';	//会员收益
		$this->TTbbill = 'TTbbill';	//会员养老金
		$this->TTbls   = 'TTbls';	//保险统计
		$this->MPartner= 'MPartner';//合作伙伴
		$this->MBranch = 'MBranch';	//分公司
		$this->MExcel  = 'MExcel';
	}
	
	/*
	* 每日会员统计
	**/
	public function viptj() {
		$post = I('post');
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'CREATE_TIME_A'		=>	I('CREATE_TIME_A'),
				'CREATE_TIME_B'		=>	I('CREATE_TIME_B'),
				'bid'				=>	I('bid'),
				'pid'				=>	I('pid'),
			);
			$ajax_soplv = array(
				'bid'				=>	I('bid'),
				'pid'				=>	I('pid'),			
			);
		}
		//===结束===	
		$post['CREATE_TIME_A'] = $post['CREATE_TIME_A'] ? $post['CREATE_TIME_A'] : date('Y-m-d');
		$post['CREATE_TIME_B'] = $post['CREATE_TIME_B'] ? $post['CREATE_TIME_B'] : date('Y-m-d');
		if($post['submit'] == "viptj"){
			//流水归属
			//===优化统计===
			$getlevel = $ajax == 'loading' ? $ajax_soplv : filter_data('soplv');	//列表查询
			//===结束=======
			$post['bid'] = $getlevel['bid'];
			$post['pid'] = $getlevel['pid'];
			if($post['bid']){
				$where .= " and BRANCH_MAP_ID = '".$post['bid']."'";
			}
			if($post['pid']){
				$pids   = get_plv_childs($post['pid'], 1);
				$where .= " and PARTNER_MAP_ID in (".$pids.")";
			}
			//交易日期	开始
			if($post['CREATE_TIME_A']) {
				$where .= " and CREATE_TIME >= '".date('Ymd',strtotime($post['CREATE_TIME_A']))."000000'";
			}
			//交易日期	结束
			if($post['CREATE_TIME_B']) {
				$where .= " and CREATE_TIME <= '".date('Ymd',strtotime($post['CREATE_TIME_B']))."235959'";
			}
			$where = substr($where, 4);
			$Model = M('', DB_PREFIX_GLA, DB_DSN_GLA);
			//===优化统计===
			if($ajax == 'loading'){			
				$count_sql  = 'SELECT COUNT(VIP_ID) AS CNT,BRANCH_MAP_ID,PARTNER_MAP_ID FROM `k_vip` WHERE '.$where;
				$num 	   = $Model->query($count_sql);
				$count     = $num[0]['CNT'];
				$resdata = array(
					'count'		=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======
			$fiRow 	   = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow 	   = C('PAGE_COUNT');			
			$list_sql  = 'SELECT COUNT(VIP_ID) AS CNT,BRANCH_MAP_ID,PARTNER_MAP_ID FROM `k_vip` WHERE '.$where.' GROUP BY PARTNER_MAP_ID ORDER BY CNT DESC LIMIT '.$fiRow.','.$liRow;
			$list 	   = $Model->query($list_sql);
						
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
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	/*
	* 每日商家交易额统计
	**/
	public function shoptradetj() {
		$post = I('post');
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "shoptradetj"){
			$where = $newswhere = "TRACE_ID != ''";
			//流水归属
			$getlevel = filter_data('soplv');	//列表查询
			$post['bid'] = $getlevel['bid'];
			$post['pid'] = $getlevel['pid'];
			if($post['bid']){
				$where     .= " and SBRANCH_MAP_ID = '".$post['bid']."'";
				$newswhere .= " and SBRANCH_MAP_ID = '".$post['bid']."'";
			}
			if($post['pid']){
				$pids 	    = get_plv_childs($post['pid'], 1);
				$where 	   .= " and SPARTNER_MAP_ID in (".$pids.")";
				$newswhere .= " and SPARTNER_MAP_ID in (".$pids.")";
			}			
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where	   .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where	   .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}
			
			//正常
			$count  = D($this->TTrace)->sumTrace($where.' and TRANS_SUBID in ("31","32","33","39","43","44") and TRACE_RETCODE = "00" and TRACE_STATUS = 0 and TRACE_REVERFLAG = 0', 'TRANS_AMT');
			//正常
			$total1 = D($this->TTrace)->sumTrace($newswhere.' and TRACE_RETCODE = "00" and TRACE_STATUS = 0 and TRACE_REVERFLAG = 0', 'TRANS_AMT');
			//退货
			$total2 = D($this->TTrace)->sumTrace($newswhere.' and TRANS_SUBID in ("531","532","533","731","732","733","743","744") and TRACE_RETCODE = "00" and TRACE_STATUS = 0 and TRACE_REVERFLAG = 0', 'TRANS_AMT');
			$total	= $total1 - $total2;
			//组装数据
			$resdata  = array(
				'count'		=>	setMoney($count, 2, 2),
				'total'		=>	setMoney($total, 2, 2),
			);
			$this->assign ( 'resdata', 	$resdata );
		}
		$this->assign ( 'postdata', 	$post );
		
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
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	/*
	* 每日POS机统计
	**/
	public function postj() {
		$post = I('post');
		$post['INSTALL_DATE_A'] = $post['INSTALL_DATE_A'] ? $post['INSTALL_DATE_A'] : date('Y-m-d');
		$post['INSTALL_DATE_B'] = $post['INSTALL_DATE_B'] ? $post['INSTALL_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "postj"){
			$where = $newswhere = "DEVICE_ID != ''";
			//流水归属
			$getlevel = filter_data('soplv');	//列表查询
			$post['bid'] = $getlevel['bid'];
			$post['pid'] = $getlevel['pid'];
			if($post['bid']){
				$where     .= " and BRANCH_MAP_ID = '".$post['bid']."'";
				$newswhere .= " and BRANCH_MAP_ID = '".$post['bid']."'";
			}
			if($post['pid']){
				$pids 	    = get_plv_childs($post['pid'], 1);
				$where 	   .= " and PARTNER_MAP_ID in (".$pids.")";
				$newswhere .= " and PARTNER_MAP_ID in (".$pids.")";
			}			
			//交易日期	开始
			if($post['INSTALL_DATE_A']) {
				$where	   .= " and INSTALL_DATE >= '".date('Ymd',strtotime($post['INSTALL_DATE_A']))."000000'";
			}
			//交易日期	结束
			if($post['INSTALL_DATE_B']) {
				$where	   .= " and INSTALL_DATE <= '".date('Ymd',strtotime($post['INSTALL_DATE_B']))."235959'";
			}
			
			//新增POS
			$count = D($this->MDevice)->countNewsDevice($where);
			//累计POS
			$total = D($this->MDevice)->countNewsDevice($newswhere);
			//组装数据
			$resdata  = array(
				'count'		=>	$count,
				'total'		=>	$total,
			);
			$this->assign ( 'resdata', 	$resdata );
		}
		$this->assign ( 'postdata', 	$post );
		
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
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	/*
	* 每日会员收益统计
	**/
	public function vipprotj() {
		$post = I('post');
		$post['POS_DATE_A'] = $post['POS_DATE_A'] ? $post['POS_DATE_A'] : date('Y-m-d');
		$post['POS_DATE_B'] = $post['POS_DATE_B'] ? $post['POS_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "vipprotj"){
			$where = $newswhere = "SYSTEM_REF != ''";
			//流水归属
			$getlevel = filter_data('soplv');	//列表查询
			$post['bid'] = $getlevel['bid'];
			$post['pid'] = $getlevel['pid'];
			if($post['bid']){
				$where     .= " and VBRANCH_MAP_ID = '".$post['bid']."'";
				$newswhere .= " and VBRANCH_MAP_ID = '".$post['bid']."'";
			}
			if($post['pid']){
				$pids 	    = get_plv_childs($post['pid'], 1);
				$where 	   .= " and VPARTNER_MAP_ID in (".$pids.")";
				$newswhere .= " and VPARTNER_MAP_ID in (".$pids.")";
			}			
			//交易日期	开始
			if($post['POS_DATE_A']) {
				$where	   .= " and POS_DATE >= '".date('Ymd',strtotime($post['POS_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['POS_DATE_B']) {
				$where	   .= " and POS_DATE <= '".date('Ymd',strtotime($post['POS_DATE_B']))."'";
			}
			
			//新增会员收益
			$count  = D($this->TJfbls)->sumJfbls($where.' and TRANS_SUBID in ("31","32","33","39","43","44")', 'CON_FEE');
			//会员收益全部收益
			$total1 = D($this->TJfbls)->sumJfbls($newswhere, 'CON_FEE');
			//会员收益退货收益
			$total2 = D($this->TJfbls)->sumJfbls($newswhere.' and TRANS_SUBID in ("531","532","533","731","732","733","743","744")', 'CON_FEE');
			$total  = $total1 - $total2;
			//组装数据
			$resdata  = array(
				'count'		=>	setMoney($count, 2, 2),
				'total'		=>	setMoney($total, 2, 2),
			);
			$this->assign ( 'resdata', 	$resdata );
		}
		$this->assign ( 'postdata', 	$post );
		
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
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	/*
	* 每日会员养老金统计
	**/
	public function vippentj() {
		$post = I('post');
		$post['TB_TIME_A'] = $post['TB_TIME_A'] ? $post['TB_TIME_A'] : date('Y-m-d');
		$post['TB_TIME_B'] = $post['TB_TIME_B'] ? $post['TB_TIME_B'] : date('Y-m-d');
		if($post['submit'] == "vippentj"){
			$where = $newswhere = "SECURITY_TYPE = 2 and TB_FLAG = 0 and TB_DEL_FLAG = 0";
			//流水归属
			$getlevel = filter_data('soplv');	//列表查询
			$post['bid'] = $getlevel['bid'];
			$post['pid'] = $getlevel['pid'];
			if($post['bid']){
				$where     .= " and VBRANCH_MAP_ID = '".$post['bid']."'";
				$newswhere .= " and VBRANCH_MAP_ID = '".$post['bid']."'";
			}
			if($post['pid']){
				$pids 	    = get_plv_childs($post['pid'], 1);
				$where 	   .= " and VPARTNER_MAP_ID in (".$pids.")";
				$newswhere .= " and VPARTNER_MAP_ID in (".$pids.")";
			}			
			//交易日期	开始
			if($post['TB_TIME_A']) {
				$where	   .= " and TB_TIME >= '".date('Ymd',strtotime($post['TB_TIME_A']))."000000'";
			}
			//交易日期	结束
			if($post['TB_TIME_B']) {
				$where	   .= " and TB_TIME <= '".date('Ymd',strtotime($post['TB_TIME_B']))."235959'";
			}
			//新增会员养老金
			$count  = D($this->TTbls)->sumTbls($where, 'TB_AMT');
			//累计会员养老金
			$total = D($this->TTbls)->sumTbls($newswhere, 'TB_AMT');
			//组装数据
			$resdata  = array(
				'count'		=>	setMoney($count, 2, 2),
				'total'		=>	setMoney($total, 2, 2),
			);
			$this->assign ( 'resdata', 	$resdata );
		}
		$this->assign ( 'postdata', 	$post );
		
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
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	
	
	
		
	/*
	* 每日统计
	**/
	public function staday() {
		$post = I('post');
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "staday"){
			if ($post['bid'] != '' || $post['pid'] != '') {
				$soplv = array(
					'bid' => $post['bid'],
					'pid' => $post['pid']
				);
			}else{
				$soplv = filter_data('soplv');	//列表查询
			}
			$vip_where = "VIP_ID !='' ";
			//分支
			if($soplv['bid'] != '') {
				$vip_lv .= " and v.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$vipincome_lv .= " and VBRANCH_MAP_ID = '".$soplv['bid']."'";
				$pos_lv .= " and d.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$shoptrance_lv .= " and SBRANCH_MAP_ID = '".$soplv['bid']."'";
				$insure_lv .= " and BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$vip_lv .= " and v.PARTNER_MAP_ID in(".$pids.")";
				$vipincome_lv .= " and VPARTNER_MAP_ID in(".$pids.")";
				$pos_lv .= " and d.PARTNER_MAP_ID in(".$pids.")";
				$shoptrance_lv .= " and SPARTNER_MAP_ID in(".$pids.")";
				$insure_lv .= " and PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			//开始时间
			if ($post['SYSTEM_DATE_A']) {
				$vip_date .= " and v.CREATE_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
				$vipincome_date .= " and POS_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
				$shoptrance_date .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
				$pos_date .= " and d.INSTALL_DATE >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
				$insure_date .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			}
			//结束时间
			if ($post['SYSTEM_DATE_B']) {
				$vip_date .= " and v.CREATE_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
				$vipincome_date .= " and POS_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
				$shoptrance_date .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
				$pos_date .= " and d.INSTALL_DATE <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
				$insure_date .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			}
			//统计会员(新增会员)
			$resurlt['add_vip_count'] = D($this->GVip)->countVip($vip_where.$vip_lv.$vip_date);
			//统计会员(累计会员)
			$resurlt['vip_count'] = D($this->GVip)->countVip($vip_where.$vip_lv);

			//统计Device(新增POS)
			$resurlt['add_pos_count'] = D($this->MDevice)->countDevice('DEVICE_ID !=""'.$pos_lv.$pos_date);
			//统计Device(累计POS)
			$resurlt['pos_count'] = D($this->MDevice)->countDevice('DEVICE_ID !=""'.$pos_lv);

			//统计商户交易(新增商户交易)[正常]
			$shoptrance1_where = 'TRANS_SUBID in ("31","32","33","39","43","44") and TRACE_RETCODE = "00" and TRACE_STATUS = 0 and TRACE_REVERFLAG = 0';
			$resurlt['add_shoptrance_count'] = D($this->TTrace)->sumTrace($shoptrance1_where.$shoptrance_lv.$shoptrance_date,'TRANS_AMT');
			//统计商户交易(累计商户交易)[正常]
			$shoptrance1 = D($this->TTrace)->sumTrace('TRACE_RETCODE = "00" and TRACE_STATUS = 0 and TRACE_REVERFLAG = 0'.$shoptrance_lv,'TRANS_AMT');
			//统计商户交易(累计商户交易)[退货]
			$shoptrance2_where = 'TRANS_SUBID in ("531","532","533","731","732","733","743","744") and TRACE_RETCODE = "00" and TRACE_STATUS = 0 and TRACE_REVERFLAG = 0';
			$shoptrance2 = D($this->TTrace)->sumTrace($shoptrance2_where.$shoptrance_lv,'TRANS_AMT');
			$resurlt['shoptrance_count'] = $shoptrance1 - $shoptrance2;

			//统计会员收益(新增会员收益)
			$vipincome1_where = 'TRANS_SUBID in ("31","32","33","39","43","44")';
			$resurlt['add_vipincome_count'] = D($this->TJfbls)->sumJfbls($vipincome1_where.$vipincome_lv.$vipincome_date,'CON_FEE');
			//统计会员收益(累计会员收益)
			$vipincome1 = D($this->TJfbls)->sumJfbls($vip_where.$vipincome_lv,'CON_FEE');	//全部收益
			$vipincome2_where = 'TRANS_SUBID in ("531","532","533","731","732","733","743","744")';
			$vipincome2 = D($this->TJfbls)->sumJfbls($vipincome2_where.$vipincome_lv,'CON_FEE');	//退货收益
			$resurlt['vipincome_count'] = $vipincome1 - $vipincome2;

			//统计会员养老金(新增会员养老金)
			$resurlt['add_vippension_count'] = D($this->TTbls)->sumTbls('SECURITY_TYPE = 2 and TB_FLAG = 0 and TB_DEL_FLAG = 0'.$insure_lv.$insure_date,'TB_AMT');
			//统计会员养老金(累计会员养老金)
			$resurlt['vippension_count'] = D($this->TTbls)->sumTbls('SECURITY_TYPE = 2 and TB_FLAG = 0 and TB_DEL_FLAG = 0'.$insure_lv,'TB_AMT');			
		}
		$this->assign ( 'postdata', 	$post );
		$this->assign ( 'resurlt', 		$resurlt );
		//Excel导出参数
		unset($post['submit']);
		$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		$this->assign('statis_type',		C('PLEVEL_NAME'));		//查询类型
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display();
	}
	//每日统计 导出
	public function staday_export() {
		$post  = array(
			'bid'			=>	I('bid'),
			'pid'			=>	I('pid'),
			'SYSTEM_DATE_A'	=>	I('SYSTEM_DATE_A'),
			'SYSTEM_DATE_B'	=>	I('SYSTEM_DATE_B'),
		);
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		$vip_where = "VIP_ID !='' ";
		//分支
		if($post['bid'] != '') {
			$vip_lv .= " and v.BRANCH_MAP_ID = '".$post['bid']."'";
			$vipincome_lv .= " and VBRANCH_MAP_ID = '".$post['bid']."'";
			$pos_lv .= " and d.BRANCH_MAP_ID = '".$post['bid']."'";
			$shoptrance_lv .= " and SBRANCH_MAP_ID = '".$post['bid']."'";
			$insure_lv .= " and BRANCH_MAP_ID = '".$post['bid']."'";
			$post['bid'] = $post['bid'];
		}
		//合作伙伴
		if($post['pid'] != '') {
			$pids = get_plv_childs($post['pid'],1);
			$vip_lv .= " and v.PARTNER_MAP_ID in(".$pids.")";
			$vipincome_lv .= " and VPARTNER_MAP_ID in(".$pids.")";
			$pos_lv .= " and d.PARTNER_MAP_ID in(".$pids.")";
			$shoptrance_lv .= " and SPARTNER_MAP_ID in(".$pids.")";
			$insure_lv .= " and PARTNER_MAP_ID in(".$pids.")";
			$post['pid'] = $post['pid'];
		}

		//开始时间
		if ($post['SYSTEM_DATE_A']) {
			$vip_date .= " and v.CREATE_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			$vipincome_date .= " and POS_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			$shoptrance_date .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			$pos_date .= " and d.INSTALL_DATE >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			$insure_date .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
		}
		//结束时间
		if ($post['SYSTEM_DATE_B']) {
			$vip_date .= " and v.CREATE_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			$vipincome_date .= " and POS_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			$shoptrance_date .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			$pos_date .= " and d.INSTALL_DATE <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			$insure_date .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
		}
		$strPort = '';
		$urlPort = __ACTION__.'?submit=ajax&'.http_build_query($post);
		$strPort = '<p><a href="'.$urlPort.'&p=1"><button class="ch-btn-skin ch-btn-small ch-icon-copy">文件(1)</button></a></p>';
		$this->assign ( 'strPort', 	$strPort );
		//导出
		$submit = I('submit');
		if($submit == 'ajax'){
			//统计会员(新增会员)
			$resurlt['add_vip_count'] = D($this->GVip)->countVip($vip_where.$vip_lv.$vip_date);
			//统计会员(累计会员)
			$resurlt['vip_count'] = D($this->GVip)->countVip($vip_where.$vip_lv);

			//统计Device(新增POS)
			$resurlt['add_pos_count'] = D($this->MDevice)->countDevice('DEVICE_ID !=""'.$pos_lv.$pos_date);
			//统计Device(累计POS)
			$resurlt['pos_count'] = D($this->MDevice)->countDevice('DEVICE_ID !=""'.$pos_lv);

			//统计商户交易(新增商户交易)[正常]
			$shoptrance1_where = 'TRANS_SUBID in ("31","32","33","39","43","44") and TRACE_RETCODE = "00" and TRACE_STATUS = 0 and TRACE_REVERFLAG = 0';
			$resurlt['add_shoptrance_count'] = D($this->TTrace)->sumTrace($shoptrance1_where.$shoptrance_lv.$shoptrance_date,'TRANS_AMT');
			//统计商户交易(累计商户交易)[正常]
			$shoptrance1 = D($this->TTrace)->sumTrace('TRACE_RETCODE = "00" and TRACE_STATUS = 0 and TRACE_REVERFLAG = 0'.$shoptrance_lv,'TRANS_AMT');
			//统计商户交易(累计商户交易)[退货]
			$shoptrance2_where = 'TRANS_SUBID in ("531","532","533","731","732","733","743","744") and TRACE_RETCODE = "00" and TRACE_STATUS = 0 and TRACE_REVERFLAG = 0';
			$shoptrance2 = D($this->TTrace)->sumTrace($shoptrance2_where.$shoptrance_lv,'TRANS_AMT');
			$resurlt['shoptrance_count'] = $shoptrance1 - $shoptrance2;

			//统计会员收益(新增会员收益)
			$vipincome1_where = 'TRANS_SUBID in ("31","32","33","39","43","44")';
			$resurlt['add_vipincome_count'] = D($this->TJfbls)->sumJfbls($vipincome1_where.$vipincome_lv.$vipincome_date,'CON_FEE');
			//统计会员收益(累计会员收益)
			$vipincome1 = D($this->TJfbls)->sumJfbls($vip_where.$vipincome_lv,'CON_FEE');	//全部收益
			$vipincome2_where = 'TRANS_SUBID in ("531","532","533","731","732","733","743","744")';
			$vipincome2 = D($this->TJfbls)->sumJfbls($vipincome2_where.$vipincome_lv,'CON_FEE');	//退货收益
			$resurlt['vipincome_count'] = $vipincome1 - $vipincome2;

			//统计会员养老金(新增会员养老金)
			$resurlt['add_vippension_count'] = D($this->TTbls)->sumTbls('SECURITY_TYPE = 2 and TB_FLAG = 0 and TB_DEL_FLAG = 0'.$insure_lv.$insure_date,'TB_AMT');
			//统计会员养老金(累计会员养老金)
			$resurlt['vippension_count'] = D($this->TTbls)->sumTbls('SECURITY_TYPE = 2 and TB_FLAG = 0 and TB_DEL_FLAG = 0'.$insure_lv,'TB_AMT');
			$this->assign ('postdata', 	$post);
			//导出操作
			$xlsname = '每日统计';
			$xlscell = array(
				array('add_vip_count',			'新增会员'),
				array('vip_count',				'累计会员'),
				array('add_shoptrance_count',	'新增商家交易额'),
				array('shoptrance_count',		'累计商家交易额'),
				array('add_pos_count',			'新增POS机'),
				array('pos_count',				'累计POS机'),
				array('add_vipincome_count',	'新增会员收益'),
				array('vipincome_count',		'累计会员收益'),
				array('add_vippension_count',	'新增会员养老金'),
				array('vippension_count',		'累计会员养老金')
			);		
			$xlsarray = array();
			$xlsarray[] = array(
				'add_vip_count'			=>	$resurlt['add_vip_count'],
				'vip_count'				=>	$resurlt['vip_count'],
				'add_shoptrance_count'	=>	setMoney($resurlt['add_shoptrance_count'], '2', '2'),
				'shoptrance_count'		=>	setMoney($resurlt['shoptrance_count'], '2', '2'),
				'add_pos_count'			=>	$resurlt['add_pos_count'],
				'pos_count'				=>	$resurlt['pos_count'],
				'add_vipincome_count'	=>	setMoney($resurlt['add_vipincome_count'], '2', '2'),
				'vipincome_count'		=>	setMoney($resurlt['vipincome_count'], '2', '2'),
				'add_vippension_count'	=>	setMoney($resurlt['add_vippension_count'], '2', '2'),
				'vippension_count'		=>	setMoney($resurlt['vippension_count'], '2', '2')
			);	
			
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		$this->display('Public/export');
	}
	
	
	
	
	/*
	* 保险数据统计
	**/
	public function stasec() {
		$post = I('post');
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "stasec"){
			$where = "TB_ID !='' ";
			$soplv = filter_data('soplv');	//列表查询
			//分支
			if($soplv['bid'] != '') {
				$where .= " and BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$where_lv .= " and PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			$post['LEVEL'] = $post['LEVEL']!='' ? $post['LEVEL'] : 3;
			if ($post['LEVEL'] !='') {
				switch ($post['LEVEL']) {
					case '0':
						//查找当前所有对应的BID
						if ($soplv['bid'] == '') {
							$b_data = D($this->MBranch)->getBranchlist('','BRANCH_MAP_ID');
							$bid_arr = i_array_column($b_data,'BRANCH_MAP_ID');
							$bids = implode(',', $bid_arr);
							$where .=" and BRANCH_MAP_ID in (".$bids.")";
						}
						$group = 'SECURITY_TYPE,BRANCH_MAP_ID';
						break;
					default:
						//查找当前级别对应的PID
						$p_data = D($this->MPartner)->getPartnerlist('a.PARTNER_MAP_ID != ""'.$where_lv." and a.PARTNER_LEVEL = ".$post['LEVEL'],'b.BRANCH_MAP_ID,a.PARTNER_MAP_ID');
						$group = 'SECURITY_TYPE,BRANCH_MAP_ID,PARTNER_MAP_ID';
						break;
				}
			}
			//开始时间
			if ($post['SYSTEM_DATE_A']) {
				$where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
				$statis_where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			}
			//结束时间
			if ($post['SYSTEM_DATE_B']) {
				$where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
				$statis_where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			}
			//投保方式
			if ($post['ONLINE_FLAG']) {
				$where .= " and ONLINE_FLAG = '".$post['ONLINE_FLAG']."'";
				$statis_where .= " and ONLINE_FLAG = '".$post['ONLINE_FLAG']."'";
			}
			//投保险种
			if ($post['SECURITY_TYPE']) {
				$where .= " and SECURITY_TYPE = '".$post['SECURITY_TYPE']."'";
				$statis_where .= " and SECURITY_TYPE = '".$post['SECURITY_TYPE']."'";
			}
			//排序规则
			if ($post['choose_order']) {
				switch ($post['choose_order']) {
					case '1':
						$order = ", sum(TB_AMT) desc";
						break;
					case '2':
						$order = ", count('TB_ID') desc";
						break;
					case '3':
						$order = ", (SELECT TB_AMT FROM `t_tbls` WHERE ( TB_FLAG = 1 ) LIMIT 1 ) desc";
						break;
					default:
						$order = ", sum(TB_AMT) desc";
						break;
				}
			}
			//分页
			/*$count = D($this->TTbls)->findTbls($where,'COUNT(DISTINCT '.$group.') AS total');
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			//分页参数
			$this->assign ( 'totalCount', 	$count['total'] );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	*/
	       	switch ($post['LEVEL']) {
	       		case '0':
	       			$res_arr = D($this->TTbls)->getTblsgrouplist($where,'*,sum(TB_AMT) as tb_amt','SECURITY_TYPE desc',$group);
					if($res_arr){
						foreach ($res_arr as $key1 => $val) {
							if ($val['tb_amt']) {
								$where1 = "BRANCH_MAP_ID = ".$val['BRANCH_MAP_ID']." and SECURITY_TYPE = ".$val['SECURITY_TYPE'].$statis_where;
								$list[$val['BRANCH_MAP_ID'].$val['SECURITY_TYPE']]['BRANCH_MAP_ID'] = $val['BRANCH_MAP_ID'];															//分公司id
								$list[$val['BRANCH_MAP_ID'].$val['SECURITY_TYPE']]['SECURITY_TYPE'] = $val['SECURITY_TYPE'];															//险种
								$list[$val['BRANCH_MAP_ID'].$val['SECURITY_TYPE']]['tb_money'] = D($this->TTbls)->sumTbls($where1.' and TB_FLAG = 0', 'TB_AMT');						//投保金额
								$list[$val['BRANCH_MAP_ID'].$val['SECURITY_TYPE']]['tb_num'] = D($this->TTbls)->countTbls($where1.' and TB_FLAG = 0', 'TB_CNT');						//投保人数
								$list[$val['BRANCH_MAP_ID'].$val['SECURITY_TYPE']]['tb_money_no'] = D($this->TTbls)->sumTbls($where1.' and (TB_FLAG = 2 or TB_FLAG = 4)', 'TB_AMT');	//未投保积分
								$list[$val['BRANCH_MAP_ID'].$val['SECURITY_TYPE']]['tb_money_err'] = D($this->TTbls)->sumTbls($where1.' and TB_FLAG = 1', 'TB_AMT');	
							}
						}
					};
	       			break;
	       		default:
	       			$list = array();
					foreach ($p_data as $key => $value) {
						$pids = get_plv_childs($value['PARTNER_MAP_ID'],1);
						$findwhere = $where." and PARTNER_MAP_ID in (".$pids.")";
						$res_arr = D($this->TTbls)->getTblsgrouplist($findwhere,'*,sum(TB_AMT) as tb_amt','SECURITY_TYPE desc',$group);
						if($res_arr){
							foreach ($res_arr as $key1 => $val) {
								if ($val['tb_amt']) {
									$where1 = "BRANCH_MAP_ID = ".$value['BRANCH_MAP_ID']." and PARTNER_MAP_ID in (".$pids.") and SECURITY_TYPE = ".$val['SECURITY_TYPE'].$statis_where;
									$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['BRANCH_MAP_ID'] = $value['BRANCH_MAP_ID'];														//分公司id
									$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['PARTNER_MAP_ID'] = $value['PARTNER_MAP_ID'];														//合作伙伴id
									$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['SECURITY_TYPE'] = $val['SECURITY_TYPE'];															//险种
									$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['tb_money'] = D($this->TTbls)->sumTbls($where1.' and TB_FLAG = 0', 'TB_AMT');						//投保金额
									$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['tb_num'] = D($this->TTbls)->countTbls($where1.' and TB_FLAG = 0', 'TB_CNT');						//投保人数
									$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['tb_money_no'] = D($this->TTbls)->sumTbls($where1.' and (TB_FLAG = 2 or TB_FLAG = 4)', 'TB_AMT');	//未投保积分
									$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['tb_money_err'] = D($this->TTbls)->sumTbls($where1.' and TB_FLAG = 1', 'TB_AMT');	
								}
							}
						};
					}
	       			break;
	       	}
	       	$security = i_array_column($list,'SECURITY_TYPE');
			
			//排序规则
			if ($post['choose_order']) {
				switch ($post['choose_order']) {
					case '1':
						$order = i_array_column($list,'tb_money');
						break;
					case '2':
						$order = i_array_column($list,'tb_num');
						break;
					case '3':
						$order = i_array_column($list,'tb_money_no');
						break;
					default:
						$order = i_array_column($list,'tb_money');
						break;
				}
			}

			// 取得列的列表
			array_multisort($security, SORT_DESC, $order, SORT_DESC, $list); 
		/*
			$list = D($this->TTbls)->getTblsgrouplist($where,'*','SECURITY_TYPE desc'.$order,$group, $p->firstRow.','.$p->listRows);
			foreach ($list as $key => $value) {
				$where = "BRANCH_MAP_ID = ".$value['BRANCH_MAP_ID']." and PARTNER_MAP_ID = ".$value['PARTNER_MAP_ID']." and SECURITY_TYPE = ".$value['SECURITY_TYPE'];
				$list[$key]['tb_money'] = D($this->TTbls)->sumTbls($where.$statis_where.' and TB_FLAG = 0', 'TB_AMT');						//投保金额
				$list[$key]['tb_num'] = D($this->TTbls)->countTbls($where.$statis_where.' and TB_FLAG = 0', 'TB_CNT');						//投保人数
				$list[$key]['tb_money_no'] = D($this->TTbls)->sumTbls($where.$statis_where.' and (TB_FLAG = 2 or TB_FLAG = 4)', 'TB_AMT');	//未投保积分
				$list[$key]['tb_money_err'] = D($this->TTbls)->sumTbls($where.$statis_where.' and TB_FLAG = 1', 'TB_AMT');					//投保失败积分

				//根据级别调整归属显示名称
				if (empty($post['LEVEL'])) {
					unset($list[$key]['PARTNER_MAP_ID']);
				}
			}*/
			
			$this->assign ( 'list', 	$list );
		}
		$this->assign ( 'postdata', 	$post );
		//Excel导出参数
		unset($post['submit']);
		$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		$stasec_lv = array('省分公司', '地市子公司', '县区服务中心', '创业合伙人');
		$this->assign ( 'stasec_lv', 		$stasec_lv );
		//投保方式(线上,线下)
		$this->assign('ONLINE_FLAG',C('ONLINE_FLAG'));
		$this->assign('SECURITY_TYPE',C('SECURITY_TYPE'));
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	//保险数据统计 导出
	public function stasec_export() {
		$post  = array(
			'bid'			=>	I('bid'),
			'pid'			=>	I('pid'),
			'SYSTEM_DATE_A'	=>	I('SYSTEM_DATE_A'),
			'SYSTEM_DATE_B'	=>	I('SYSTEM_DATE_B'),
			'ONLINE_FLAG'	=>	I('ONLINE_FLAG'),
			'SECURITY_TYPE'	=>	I('SECURITY_TYPE'),
			'LEVEL'			=>	I('LEVEL'),
			'choose_order'	=>	I('choose_order'),
		);
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		$where = "TB_ID !='' ";
		//$soplv = filter_data('soplv');	//列表查询
		//分支
		if($post['bid'] != '') {
			$where .= " and BRANCH_MAP_ID = '".$post['bid']."'";
			$post['bid'] = $post['bid'];
		}
		//合作伙伴
		if($post['pid'] != '') {
			$pids = get_plv_childs($post['pid'],1);
			$where_lv .= " and PARTNER_MAP_ID in(".$pids.")";
			$post['pid'] = $post['pid'];
		}
		$post['LEVEL'] = $post['LEVEL']!='' ? $post['LEVEL'] : 3;
		if ($post['LEVEL'] !='') {
			switch ($post['LEVEL']) {
				case '0':
					//查找当前所有对应的BID
					if ($soplv['bid'] == '') {
						$b_data = D($this->MBranch)->getBranchlist('','BRANCH_MAP_ID');
						$bid_arr = i_array_column($b_data,'BRANCH_MAP_ID');
						$bids = implode(',', $bid_arr);
						$where .=" and BRANCH_MAP_ID in (".$bids.")";
					}
					$group = 'SECURITY_TYPE,BRANCH_MAP_ID';
					break;
				default:
					//查找当前级别对应的PID
					$p_data = D($this->MPartner)->getPartnerlist('a.PARTNER_MAP_ID != ""'.$where_lv." and a.PARTNER_LEVEL = ".$post['LEVEL'],'b.BRANCH_MAP_ID,a.PARTNER_MAP_ID');
					$group = 'SECURITY_TYPE,BRANCH_MAP_ID,PARTNER_MAP_ID';
					break;
			}
		}
		//开始时间
		if ($post['SYSTEM_DATE_A']) {
			$where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			$statis_where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
		}
		//结束时间
		if ($post['SYSTEM_DATE_B']) {
			$where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			$statis_where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
		}
		//投保方式
		if ($post['ONLINE_FLAG']) {
			$where .= " and ONLINE_FLAG = '".$post['ONLINE_FLAG']."'";
			$statis_where .= " and ONLINE_FLAG = '".$post['ONLINE_FLAG']."'";
		}
		//投保险种
		if ($post['SECURITY_TYPE']) {
			$where .= " and SECURITY_TYPE = '".$post['SECURITY_TYPE']."'";
			$statis_where .= " and SECURITY_TYPE = '".$post['SECURITY_TYPE']."'";
		}
		//排序规则
		if ($post['choose_order']) {
			switch ($post['choose_order']) {
				case '1':
					$order = ", sum(TB_AMT) desc";
					break;
				case '2':
					$order = ", count('TB_ID') desc";
					break;
				case '3':
					$order = ", (SELECT TB_AMT FROM `t_tbls` WHERE ( TB_FLAG = 1 ) LIMIT 1 ) desc";
					break;
				default:
					$order = ", sum(TB_AMT) desc";
					break;
			}
		}
		//计算
		/*$count = D($this->TTbls)->findTbls($where,'COUNT(DISTINCT '.$group.') AS total');
		$numPort = floor($count['total']/C('PAGE_COUNT_EXPORT'));
		$urlPort = __ACTION__.'?submit=ajax&'.http_build_query($post);
		$strPort = '';
		if($count > 0){
			for($i=0; $i<=$numPort; $i++){
				$strPort .= '<p><a href="'.$urlPort.'&p='.($i).'"><button class="ch-btn-skin ch-btn-small ch-icon-copy">文件('.($i+1).')</button></a></p>';
			}
		}else{
			$strPort .= '<p>暂无数据可下载~</p>';
		}
		$this->assign ( 'strPort', 	$strPort );*/
		$strPort = '';
		$urlPort = __ACTION__.'?submit=ajax&'.http_build_query($post);
		$strPort = '<p><a href="'.$urlPort.'&p=1"><button class="ch-btn-skin ch-btn-small ch-icon-copy">文件(1)</button></a></p>';
		$this->assign ( 'strPort', 	$strPort );
		//导出
		$submit = I('submit');
		if($submit == 'ajax'){
		 	switch ($post['LEVEL']) {
       		case '0':
       			$res_arr = D($this->TTbls)->getTblsgrouplist($where,'*,sum(TB_AMT) as tb_amt','SECURITY_TYPE desc',$group);
				if($res_arr){
					foreach ($res_arr as $key1 => $val) {
						if ($val['tb_amt']) {
							$where1 = "BRANCH_MAP_ID = ".$val['BRANCH_MAP_ID']." and SECURITY_TYPE = ".$val['SECURITY_TYPE'].$statis_where;
							$list[$val['BRANCH_MAP_ID'].$val['SECURITY_TYPE']]['BRANCH_MAP_ID'] = $val['BRANCH_MAP_ID'];															//分公司id
							$list[$val['BRANCH_MAP_ID'].$val['SECURITY_TYPE']]['SECURITY_TYPE'] = $val['SECURITY_TYPE'];															//险种
							$list[$val['BRANCH_MAP_ID'].$val['SECURITY_TYPE']]['tb_money'] = D($this->TTbls)->sumTbls($where1.' and TB_FLAG = 0', 'TB_AMT');						//投保金额
							$list[$val['BRANCH_MAP_ID'].$val['SECURITY_TYPE']]['tb_num'] = D($this->TTbls)->countTbls($where1.' and TB_FLAG = 0', 'TB_CNT');						//投保人数
							$list[$val['BRANCH_MAP_ID'].$val['SECURITY_TYPE']]['tb_money_no'] = D($this->TTbls)->sumTbls($where1.' and (TB_FLAG = 2 or TB_FLAG = 4)', 'TB_AMT');	//未投保积分
							$list[$val['BRANCH_MAP_ID'].$val['SECURITY_TYPE']]['tb_money_err'] = D($this->TTbls)->sumTbls($where1.' and TB_FLAG = 1', 'TB_AMT');	
						}
					}
				};
       			break;
       		default:
       			$list = array();
				foreach ($p_data as $key => $value) {
					$pids = get_plv_childs($value['PARTNER_MAP_ID'],1);
					$findwhere = $where." and PARTNER_MAP_ID in (".$pids.")";
					$res_arr = D($this->TTbls)->getTblsgrouplist($findwhere,'*,sum(TB_AMT) as tb_amt','SECURITY_TYPE desc',$group);
					if($res_arr){
						foreach ($res_arr as $key1 => $val) {
							if ($val['tb_amt']) {
								$where1 = "BRANCH_MAP_ID = ".$value['BRANCH_MAP_ID']." and PARTNER_MAP_ID in (".$pids.") and SECURITY_TYPE = ".$val['SECURITY_TYPE'].$statis_where;
								$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['BRANCH_MAP_ID'] = $value['BRANCH_MAP_ID'];														//分公司id
								$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['PARTNER_MAP_ID'] = $value['PARTNER_MAP_ID'];														//合作伙伴id
								$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['SECURITY_TYPE'] = $val['SECURITY_TYPE'];															//险种
								$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['tb_money'] = D($this->TTbls)->sumTbls($where1.' and TB_FLAG = 0', 'TB_AMT');						//投保金额
								$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['tb_num'] = D($this->TTbls)->countTbls($where1.' and TB_FLAG = 0', 'TB_CNT');						//投保人数
								$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['tb_money_no'] = D($this->TTbls)->sumTbls($where1.' and (TB_FLAG = 2 or TB_FLAG = 4)', 'TB_AMT');	//未投保积分
								$list[$value['PARTNER_MAP_ID'].$val['SECURITY_TYPE']]['tb_money_err'] = D($this->TTbls)->sumTbls($where1.' and TB_FLAG = 1', 'TB_AMT');	
							}
						}
					};
				}
       			break;
	       	}
	       	$security = i_array_column($list,'SECURITY_TYPE');
			
			//排序规则
			if ($post['choose_order']) {
				switch ($post['choose_order']) {
					case '1':
						$order = i_array_column($list,'tb_money');
						break;
					case '2':
						$order = i_array_column($list,'tb_num');
						break;
					case '3':
						$order = i_array_column($list,'tb_money_no');
						break;
					default:
						$order = i_array_column($list,'tb_money');
						break;
				}
			}
			// 取得列的列表
			array_multisort($security, SORT_DESC, $order, SORT_DESC, $list); 
			
			//导出操作
			$xlsname = '保险数据统计';
			$xlscell = array(
				array('company_name',	'组织名称'),
				array('security_type',	'保险类别'),
				array('tb_money',		'投保金额'),
				array('tb_num',			'投保人数'),
				array('tb_money_err',	'投保失败积分'),
				array('tb_money_no',	'未投保积分')
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'company_name'	=>	get_level_name($val['PARTNER_MAP_ID'],$val['BRANCH_MAP_ID']),
					'security_type'	=>	C('SECURITY_TYPE')[$val['SECURITY_TYPE']],
					'tb_money'		=>	setMoney($val['tb_money'], '2', '2'),
					'tb_num'		=>	$val['tb_num'] ? $val['tb_num'] : '0',
					'tb_money_err'	=>	setMoney($val['tb_money_err'], '2', '2'),
					'tb_money_no'	=>	setMoney($val['tb_money_no'], '2', '2')
				);	
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;		
		}
		$this->display('Public/export');
	}
	
	
	
	
	/*
	* 会员交易统计
	**/
	public function stavip() {
		$post = I('post');
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "stavip"){
			//$where = "VIP_ID > 0";
			$where = "VIP_ID > 0 and TRACE_RETCODE='00' and TRACE_REVERFLAG=0 and TRANS_AMT>0 and TRACE_VOIDFLAG = 0 and TRACE_REFUNDFLAG = 0";

			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}
			//归属
			$getlevel = filter_data('plv');	//列表查询
			$post['VBRANCH_MAP_ID']  = $getlevel['bid'];
			$post['VPARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['VBRANCH_MAP_ID']){
				$where .= " and VBRANCH_MAP_ID = '".$post['VBRANCH_MAP_ID']."'";
			}
			if($post['VPARTNER_MAP_ID']){
				$pids = get_plv_childs($post['VPARTNER_MAP_ID'],1);
				$where .= " and VPARTNER_MAP_ID in (".$pids.")";
			}
			
			$field = 'count(*) as CNT,sum(TRANS_AMT) as AMT,VBRANCH_MAP_ID,VPARTNER_MAP_ID,VIP_ID,VIP_CARDNO';
			$order = $post['ORDER_TYPE']==1 ? 'AMT desc' : 'CNT desc';
			$list  = D($this->TTrace)->getTracegrouplist($where, $field, '0,20', 'VIP_ID', $order);		
			$this->assign ( 'list', 		$list );
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
		$this->assign('user_level',			C('USER_LEVEL'));	//用户级别
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}	
	/*
	* 会员交易统计	Excel导出
	**/
	public function stavip_export() {
		$post  = array(
			'ORDER_TYPE'		=>	I('ORDER_TYPE'),
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
			'VBRANCH_MAP_ID'	=>	I('VBRANCH_MAP_ID'),
			'VPARTNER_MAP_ID'	=>	I('VPARTNER_MAP_ID'),
		);
		$where = "VIP_ID > 0";
		//交易日期	开始
		if($post['SYSTEM_DATE_A']) {
			$where .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
		}
		//交易日期	结束
		if($post['SYSTEM_DATE_B']) {
			$where .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
		}
		//归属
		if($post['VBRANCH_MAP_ID']){
			$where .= " and VBRANCH_MAP_ID = '".$post['VBRANCH_MAP_ID']."'";
		}
		if($post['VPARTNER_MAP_ID']){
			$pids = get_plv_childs($post['VPARTNER_MAP_ID'],1);
			$where .= " and VPARTNER_MAP_ID in (".$pids.")";
		}
		
		//计算
		$count   = 1;	//因为就1页
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
			$field = 'count(*) as CNT,sum(TRANS_AMT) as AMT,VBRANCH_MAP_ID,VPARTNER_MAP_ID,VIP_ID,VIP_CARDNO';
			$order = $post['ORDER_TYPE']==1 ? 'AMT desc' : 'CNT desc';
			$list  = D($this->TTrace)->getTracegrouplist($where, $field, '0,20', 'VIP_ID', $order);	
					
			//导出操作
			$xlsname = '会员交易排行文件('.($p+1).')';
			$xlscell = array(
				array('KEY',				'序号'),
				array('VBRANCH_MAP_ID',		'公司名称'),
				array('VPARTNER_MAP_ID',	'归属'),
				array('VIP_CARDNO',			'会员卡号'),
				array('VIP_ID',				'会员名称'),
				array('CNT',				'交易笔数'),
				array('AMT',				'交易金额'),
			);		
			$xlsarray = array();
			foreach($list as $key=>$val){
				$xlsarray[] = array(
					'KEY'				=>	$key+1,
					'VBRANCH_MAP_ID'	=>	get_branch_name($val['VBRANCH_MAP_ID']),
					'VPARTNER_MAP_ID'	=>	get_branch_name($val['VBRANCH_MAP_ID'], $val['VPARTNER_MAP_ID']),
					'VIP_CARDNO'		=>	setCard_no($val['VIP_CARDNO']),
					'VIP_ID'			=>	getvip_name($val['VIP_ID']),
					'CNT'				=>	$val['CNT'],
					'AMT'				=>	setMoney($val['AMT'], 2, 2),
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	
	
	
	/*
	* 商家交易统计
	**/
	public function stashop() {
		$post = I('post');
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
				'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
				'SEL_LEVEL'			=>	I('SEL_LEVEL'),
				'SHOP_NO'			=>	I('SHOP_NO'),
				'SBRANCH_MAP_ID'	=>	I('SBRANCH_MAP_ID'),
				'SPARTNER_MAP_ID'	=>	I('SPARTNER_MAP_ID'),
			);
			$ajax_soplv = array(
				'bid'				=>	I('SBRANCH_MAP_ID'),
				'pid'				=>	I('SPARTNER_MAP_ID'),			
			);
		}
		//===结束===
		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		$post['SEL_LEVEL'] 	   = $post['SEL_LEVEL']!=''	? $post['SEL_LEVEL'] 	 : 4;
		if($post['submit'] == "stashop"){
			//===优化统计===
			$getlevel = $ajax == 'loading' ? $ajax_soplv : filter_data('plv');	//列表查询
			//===结束=======
			$post['SBRANCH_MAP_ID']  = $getlevel['bid'];
			$post['SPARTNER_MAP_ID'] = $getlevel['pid'];			
			//非商户
			if($post['SEL_LEVEL'] != 4){
				switch($post['SEL_LEVEL']){
					case '0':
						//省分公司列表
						$where = "BRANCH_MAP_ID != ''";
						if($post['SBRANCH_MAP_ID']){
							$where .= " and BRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
						}
						$branchlist = D($this->MBranch)->getBranchlist($where, 'BRANCH_MAP_ID,"" as PARTNER_MAP_ID');		
					break;
					case '1':
						//地市子公司
						$where = "PARTNER_LEVEL = '1'";
						if($post['SBRANCH_MAP_ID']){
							$where .= " and BRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
						}
						if($post['SPARTNER_MAP_ID']){
							$where .= " and PARTNER_MAP_ID = '".$post['SPARTNER_MAP_ID']."'";
						}
						$branchlist = D($this->MPartner)->getNewsPartnerlist($where, 'BRANCH_MAP_ID,PARTNER_MAP_ID');		
					break;
					case '2':
						//区县服务中心
						$where = "PARTNER_LEVEL = '2'";
						if($post['SBRANCH_MAP_ID']){
							$where .= " and BRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
						}
						if($post['SPARTNER_MAP_ID']){
							$where .= " and PARTNER_MAP_ID_P = '".$post['SPARTNER_MAP_ID']."'";
						}
						$branchlist = D($this->MPartner)->getNewsPartnerlist($where, 'BRANCH_MAP_ID,PARTNER_MAP_ID');		
					break;
					case '3':
						//创业合伙人
						$where = "PARTNER_LEVEL = '3'";
						if($post['SBRANCH_MAP_ID']){
							$where .= " and BRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
						}
						if($post['SPARTNER_MAP_ID']){
							$where .= " and PARTNER_MAP_ID_P = '".$post['SPARTNER_MAP_ID']."'";
						}
						$branchlist = D($this->MPartner)->getNewsPartnerlist($where, 'BRANCH_MAP_ID,PARTNER_MAP_ID');		
					break;
				}
				if(!empty($branchlist)){
					for($i = 0; $i<count($branchlist); $i++){
						$where = "t.SYSTEM_REF != ''";
						//交易日期	开始
						if($post['SYSTEM_DATE_A']) {
							$where .= " and t.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
						}
						//交易日期	结束
						if($post['SYSTEM_DATE_B']) {
							$where .= " and t.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
						}
						//归属
						$BRANCH_MAP_ID  = $branchlist[$i]['BRANCH_MAP_ID'];
						$PARTNER_MAP_ID = $branchlist[$i]['PARTNER_MAP_ID'];
						if($BRANCH_MAP_ID){
							$where .= " and (j.ISS_BRANCH_MAP_ID2  = '".$BRANCH_MAP_ID."' OR j.ACQ_BRANCH_MAP_ID2  = '".$BRANCH_MAP_ID."')";
						}
						if($PARTNER_MAP_ID){
							$where .= " and (j.ISS_PARTNER_MAP_ID1 = '".$PARTNER_MAP_ID."' OR 
										j.ISS_PARTNER_MAP_ID2 = '".$PARTNER_MAP_ID."' OR 
										j.ISS_PARTNER_MAP_ID3 = '".$PARTNER_MAP_ID."' OR 
										j.VIP_PARTNER_MAP_ID1 = '".$PARTNER_MAP_ID."' OR 
										j.VIP_PARTNER_MAP_ID2 = '".$PARTNER_MAP_ID."' OR 
										j.ACQ_PARTNER_MAP_ID1 = '".$PARTNER_MAP_ID."' OR 
										j.ACQ_PARTNER_MAP_ID2 = '".$PARTNER_MAP_ID."' OR 
										j.PARTNER_MAP_ID3A    = '".$PARTNER_MAP_ID."' OR 
										j.PARTNER_MAP_ID3B    = '".$PARTNER_MAP_ID."' )";
						}
						//商户编号
						if($post['SHOP_NO']) {
							$where .= " and t.SHOP_NO = '".$post['SHOP_NO']."'";
						}
						$where .= " AND t.TRACE_RETCODE='00' AND t.TRACE_REVERFLAG=0 AND t.TRANS_AMT>0 AND j.JFB_FEE>0 AND t.TRACE_VOIDFLAG = 0 AND t.TRACE_REFUNDFLAG = 0";
						//交易类型（现金或银行卡）
						$where .= " AND t.TRANS_SUBID IN (43, 44, 39, 31, 32, 33, 38)";
						$Model = M('', DB_PREFIX_TRA, DB_DSN_TRA);
						//统计
						$total_sql = "select count(t.TRACE_ID) as CNT,sum(t.TRANS_AMT) as AMT FROM T_JFBLS j LEFT JOIN t_trace t ON(t.SYSTEM_REF = j.SYSTEM_REF) WHERE ".$where;
						$total     = $Model->query($total_sql);
						$list[] = array(
							'BRANCH_MAP_ID'		=>	$BRANCH_MAP_ID,
							'PARTNER_MAP_ID'	=>	$PARTNER_MAP_ID,
							'CNT'				=>	$total[0]['CNT'],
							'AMT'				=>	setMoney($total[0]['AMT'], 2, 2),
						);
					}
				}
				$this->assign ( 'list', 		$list );
			}
			//商户
			else{
				$where = "t.SYSTEM_REF != '' AND t.SHOP_NO > 0";
				//交易日期	开始
				if($post['SYSTEM_DATE_A']) {
					$where .= " and t.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
				}
				//交易日期	结束
				if($post['SYSTEM_DATE_B']) {
					$where .= " and t.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
				}
				//归属
				if($post['SBRANCH_MAP_ID']){
					$where .= " and (j.ISS_BRANCH_MAP_ID2  = '".$post['SBRANCH_MAP_ID']."' OR j.ACQ_BRANCH_MAP_ID2  = '".$post['SBRANCH_MAP_ID']."')";
				}
				if($post['SPARTNER_MAP_ID']){
					$where .= " and (j.ISS_PARTNER_MAP_ID1 = '".$post['SPARTNER_MAP_ID']."' OR 
								j.ISS_PARTNER_MAP_ID2 = '".$post['SPARTNER_MAP_ID']."' OR 
								j.ISS_PARTNER_MAP_ID3 = '".$post['SPARTNER_MAP_ID']."' OR 
								j.VIP_PARTNER_MAP_ID1 = '".$post['SPARTNER_MAP_ID']."' OR 
								j.VIP_PARTNER_MAP_ID2 = '".$post['SPARTNER_MAP_ID']."' OR 
								j.ACQ_PARTNER_MAP_ID1 = '".$post['SPARTNER_MAP_ID']."' OR 
								j.ACQ_PARTNER_MAP_ID2 = '".$post['SPARTNER_MAP_ID']."' OR 
								j.PARTNER_MAP_ID3A    = '".$post['SPARTNER_MAP_ID']."' OR 
								j.PARTNER_MAP_ID3B    = '".$post['SPARTNER_MAP_ID']."' )";
				}
				//商户编号
				if($post['SHOP_NO']) {
					$where .= " and t.SHOP_NO = '".$post['SHOP_NO']."'";
				}
				$where .= " AND t.TRACE_RETCODE='00' AND t.TRACE_REVERFLAG=0 AND t.TRANS_AMT>0 AND j.JFB_FEE>0 AND t.TRACE_VOIDFLAG = 0 AND t.TRACE_REFUNDFLAG = 0";
				//交易类型（现金或银行卡）
				$where .= " AND t.TRANS_SUBID IN (43, 44, 39, 31, 32, 33, 38)";
				$Model = M('', DB_PREFIX_TRA, DB_DSN_TRA);
				//分页
				$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
				$liRow = C('PAGE_COUNT');
				//===优化统计===
				if($ajax == 'loading'){
					//统计总条数
					$list_sql = "select count(t.TRACE_ID) as CNT,sum(t.TRANS_AMT) as AMT,t.SHOP_NO,t.SHOP_NAMEAB,t.SBRANCH_MAP_ID,t.SPARTNER_MAP_ID FROM T_JFBLS j LEFT JOIN t_trace t ON(t.SYSTEM_REF = j.SYSTEM_REF) WHERE ".$where;
					$total    = $Model->query($list_sql);		
					$resdata = array(
						'count'	=>	$count,
						'count'	=>	$total[0]['CNT'],
						'amt'	=>	setMoney($total[0]['AMT'], 2, 2),
					);
					$this->ajaxReturn($resdata);
				}
				//===结束=======				
				//统计				
				$list_sql = "select count(t.TRACE_ID) as CNT,sum(t.TRANS_AMT) as AMT,t.SHOP_NO,t.SHOP_NAMEAB,t.SBRANCH_MAP_ID,t.SPARTNER_MAP_ID FROM T_JFBLS j LEFT JOIN t_trace t ON(t.SYSTEM_REF = j.SYSTEM_REF) WHERE ".$where." GROUP BY t.SHOP_NO limit ".$fiRow.','.$liRow;
				$list     = $Model->query($list_sql);
								
				//分页参数
				$this->assign ( 'totalCount', 	C('PAGE_COUNT')==count($list) ? 1 : 0 );
				$this->assign ( 'numPerPage', 	'' );
				$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
				
				$this->assign ( 'list', 		$list );
							
				//Excel导出参数
				unset($post['submit']);
				$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
			}
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
		$sel_level = array('省分公司', '地市子公司', '县区服务中心', '创业合伙人', '商户');
		$this->assign('sel_level', 			$sel_level);
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 商家交易统计	Excel导出
	**/
	public function stashop_export() {
		$post  = array(
			'ORDER_TYPE'		=>	I('ORDER_TYPE'),
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A'),
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B'),
			'SEL_LEVEL'			=>	I('SEL_LEVEL'),
			'SHOP_NO'			=>	I('SHOP_NO'),
			'SBRANCH_MAP_ID'	=>	I('SBRANCH_MAP_ID'),
			'SPARTNER_MAP_ID'	=>	I('SPARTNER_MAP_ID'),
		);

		$post['SYSTEM_DATE_A'] = $post['SYSTEM_DATE_A'] ? $post['SYSTEM_DATE_A'] : date('Y-m-d');
		$post['SYSTEM_DATE_B'] = $post['SYSTEM_DATE_B'] ? $post['SYSTEM_DATE_B'] : date('Y-m-d');
		$post['SEL_LEVEL'] 	   = $post['SEL_LEVEL']!=''	? $post['SEL_LEVEL'] 	 : 4;
		$where = "VIP_ID > 0";
		
		//交易日期	开始
		if($post['SYSTEM_DATE_A']) {
			$where .= " and SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
		}
		//交易日期	结束
		if($post['SYSTEM_DATE_B']) {
			$where .= " and SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
		}
		//商户编号
		if($post['SHOP_NO']) {
			$where .= " and SHOP_NO = '".$post['SHOP_NO']."'";
		}
		//归属
		//$getlevel = filter_data('plv');	//列表查询
		$post['SBRANCH_MAP_ID']  = $post['SBRANCH_MAP_ID'];
		$post['SPARTNER_MAP_ID'] = $post['SPARTNER_MAP_ID'];
		
		//计算
		$count   = 1;	//因为就1页
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
			$field = 'count(*) as CNT,sum(TRANS_AMT) as AMT,SBRANCH_MAP_ID,SPARTNER_MAP_ID,SHOP_NO,SHOP_NAMEAB';
			$order = $post['ORDER_TYPE']==1 ? 'AMT desc' : 'CNT desc';
			
			switch($post['SEL_LEVEL']){
				case '0':
					$group = 'SBRANCH_MAP_ID';
					$order = $post['ORDER_TYPE']==1 ? 'AMT desc' : 'CNT desc';
					
					if($post['SBRANCH_MAP_ID']){
						$where .= " and SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
					}
					$list = D($this->TTrace)->getTracegrouplist($where, $field, '0,20', $group, $order);
					break;
				case '4':
					$group = 'SHOP_NO';
					$order = $post['ORDER_TYPE']==1 ? 'AMT desc' : 'CNT desc';
					
					if($post['SBRANCH_MAP_ID']){
						$where .= " and SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
					}
					if($post['SPARTNER_MAP_ID']){
						$pids = get_plv_childs($post['SPARTNER_MAP_ID'], 1);
						$where .= " and SPARTNER_MAP_ID in (".$pids.")";
					}
					$list = D($this->TTrace)->getTracegrouplist($where, $field, '0,20', $group, $order);
					break;
				default:
					$order = $post['ORDER_TYPE']==1 ? 'AMT' : 'CNT';
					
					$bw = "a.PARTNER_LEVEL = '".$post['SEL_LEVEL']."'";
					if($post['SBRANCH_MAP_ID']){
						$bw .= " and a.BRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
					}
					if($post['SPARTNER_MAP_ID']){
						$pids = get_plv_childs($post['SPARTNER_MAP_ID'], 1);
						$bw .= " and a.PARTNER_MAP_ID in (".$pids.")";
					}
					$blist = D($this->MPartner)->getPartnerlist($bw, 'a.PARTNER_MAP_ID');
					
					$list = array();
					foreach($blist as $val){
						$childs = get_plv_childs($val['PARTNER_MAP_ID'], 1);
						if($childs){
							$trdata = D($this->TTrace)->findTrace($where." and SPARTNER_MAP_ID in (".$childs.")", $field);
							if($trdata['CNT'] > 0){
								$list[] = $trdata;
							}
						}
					}
					$list = arr_sort($list, $order, ' desc');
			}		
			
			foreach($list as $key=>$val){
				$SBRANCH_NAME  = '';
				$SPARTNER_NAME = '';
				$branch = array();
				$branch = get_level_name($val['SPARTNER_MAP_ID'], $val['SBRANCH_MAP_ID']);
				$branch = explode(" - ",$branch);
				$paname = get_branch_name($val['SBRANCH_MAP_ID'], $val['SPARTNER_MAP_ID']);
				switch($post['SEL_LEVEL']){
					case '0':
						$SBRANCH_NAME  = $branch[0];
						$SPARTNER_NAME = $branch[0]==$paname ? $paname : '';
						break;
					case '1':
						$SBRANCH_NAME  = $branch[1];
						$SPARTNER_NAME = $branch[1]==$paname ? $paname : '';
						break;
					case '2':
						$SBRANCH_NAME  = $branch[2];
						$SPARTNER_NAME = $branch[2]==$paname ? $paname : '';
						break;
					case '3':
						$SBRANCH_NAME  = $branch[3];
						$SPARTNER_NAME = $branch[3]==$paname ? $paname : '';
						break;
					case '4':
						$SBRANCH_NAME  = $val['SHOP_NAMEAB'];
						$SPARTNER_NAME = $paname;
						break;
				}			
				$list[$key]['SBRANCH_NAME']  = $SBRANCH_NAME;
				$list[$key]['SPARTNER_NAME'] = $SPARTNER_NAME;
			}


			//导出操作
			$xlsname = '商家交易排行文件('.($p+1).')';
			$xlscell = array(
				array('KEY',				'序号'),
				array('SBRANCH_MAP_ID',		'公司名称'),
				array('SPARTNER_MAP_ID',	'归属'),
				array('SHOP_NO',			'商户号'),
				array('SHOP_NAMEAB',		'商户名称'),
				array('CNT',				'交易笔数'),
				array('AMT',				'交易金额'),
			);		
			$xlsarray = array();
			foreach($list as $key=>$val){
				$xlsarray[] = array(
					'KEY'				=>	$key+1,
					'SBRANCH_MAP_ID'	=>	get_branch_name($val['SBRANCH_MAP_ID']),
					'SPARTNER_MAP_ID'	=>	get_branch_name($val['SBRANCH_MAP_ID'], $val['SPARTNER_MAP_ID']),
					'SHOP_NO'			=>	$val['SHOP_NO']."\t",
					'SHOP_NAMEAB'		=>	$val['SHOP_NAMEAB'],
					'CNT'				=>	$val['CNT'],
					'AMT'				=>	setMoney($val['AMT'], 2, 2),
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
}