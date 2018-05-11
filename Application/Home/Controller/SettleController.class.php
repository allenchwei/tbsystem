<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  清算管理
// +----------------------------------------------------------------------
class SettleController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->THbill 		= 'THbill';
		$this->MHost 		= 'MHost';
		$this->TAcqbill		= 'TAcqbill';
		$this->TSckbill		= 'TSckbill';
		$this->TBbill		= 'TBbill';
		$this->TPbill		= 'TPbill';
		$this->TPibill		= 'TPibill';
		$this->MDkco		= 'MDkco';
		$this->TDkls		= 'TDkls';
		$this->MExcel		= 'MExcel';
		$this->TTrace		= 'TTrace';
		$this->TSmsls		= 'TSmsls';
		$this->MPartner		= 'MPartner';
		$this->TQbls		= 'TQbls';
		$this->MBranch		= 'MBranch';
		$this->MPartner		= 'MPartner';
	}
	
	/*
	* 通道清算
	**/
	public function hbill() {
		$post = I('post');
		if($post['submit'] == "hbill"){
			$where = "1=1";
			//支付通道
			if($post['HOST_MAP_ID']) {
				$where .= " and HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
			}
			//交易日期	开始
			if($post['SETTLE_DATE_A']) {
				$where .= " and SETTLE_DATE >= '".date('Ymd',strtotime($post['SETTLE_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SETTLE_DATE_B']) {
				$where .= " and SETTLE_DATE <= '".date('Ymd',strtotime($post['SETTLE_DATE_B']))."'";
			}
			//审核状态
			if($post['CHECK_FLAG'] != '') {
				$where .= " and CHECK_FLAG = '".$post['CHECK_FLAG']."'";
			}
			//划转
			if($post['ACCT_FLAG'] != '') {
				$where .= " and ACCT_FLAG = '".$post['ACCT_FLAG']."'";
			}
			//分页
			$count = D($this->THbill)->countHbill($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->THbill)->getHbilllist($where, '*', $p->firstRow.','.$p->listRows);
			$data  = D($this->THbill)->findHbill($where, 'sum(PT_CON_AMT) as PT_CON_AMT,sum(PT_REF_AMT) as PT_REF_AMT');
			
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );	
			$this->assign ( 'xfamt', 		$data['PT_CON_AMT'] );
			$this->assign ( 'thamt', 		$data['PT_REF_AMT'] );
			
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		//通道列表
		$host = D($this->MHost)->getHostlist('HOST_STATUS = 0', 'HOST_MAP_ID,HOST_NAME');
		foreach($host as $val){
			$host_list[$val['HOST_MAP_ID']] = $val['HOST_NAME'];
		}
		$this->assign('host_list', 			$host_list );
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 通道清算	结算单
	**/
	public function hbill_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->THbill)->findHbill("HBILL_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//通道信息
		$host_data = D($this->MHost)->findHost("HOST_MAP_ID = '".$info['HOST_MAP_ID']."'", 'HOST_NAME');
		$info['HOST_NAME'] = $host_data['HOST_NAME'];
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('bankacct_flag', 		C('BANKACCT_FLAG'));	//结算标志
		$this->assign ('info', 				$info);
		$this->display();
	}
	/*
	* 通道清算 初审
	**/
	public function hbill_check() {
		$post = I('post');
		if($post['submit'] == "hbill_check"){
			//验证
			if(empty($post['HBILL_ID'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'CHECK_FLAG'	=> 	1,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$res = D($this->THbill)->updateHbill("HBILL_ID = '".$post['HBILL_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('初审通过！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->THbill)->findHbill("HBILL_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['CHECK_FLAG'] != 0){
			$this->wrong("当前状态下不允许初审操作！");
		}
		$info['CHECK_TITLE'] = '初审';
		//通道信息
		$host_data = D($this->MHost)->findHost("HOST_MAP_ID = '".$info['HOST_MAP_ID']."'", 'HOST_NAME');
		$info['HOST_NAME'] = $host_data['HOST_NAME'];
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('bankacct_flag', 		C('BANKACCT_FLAG'));	//结算标志
		$this->assign ('info', 				$info);
		$this->display();
	}
	/*
	* 通道清算 复核
	**/
	public function hbill_recheck() {
		$post = I('post');
		if($post['submit'] == "hbill_recheck"){
			//验证
			if(empty($post['HBILL_ID'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'CHECK_FLAG'	=> 	2,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$res = D($this->THbill)->updateHbill("HBILL_ID = '".$post['HBILL_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('复核通过！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->THbill)->findHbill("HBILL_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['CHECK_FLAG'] != 1){
			$this->wrong("当前状态下不允许复核操作！");
		}
		$info['CHECK_TITLE'] = '复核';
		//通道信息
		$host_data = D($this->MHost)->findHost("HOST_MAP_ID = '".$info['HOST_MAP_ID']."'", 'HOST_NAME');
		$info['HOST_NAME'] = $host_data['HOST_NAME'];
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('bankacct_flag', 		C('BANKACCT_FLAG'));	//结算标志
		$this->assign ('info', 				$info);
		$this->display('hbill_check');
	}
	/*
	* 通道清算 收款确认
	**/
	public function hbill_affirm() {
		$post = I('post');
		if($post['submit'] == "hbill_affirm"){
			//验证
			if(empty($post['HBILL_ID'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'ACCT_FLAG'		=> 0,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$res = D($this->THbill)->updateHbill("HBILL_ID = '".$post['HBILL_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('到账成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->THbill)->findHbill("HBILL_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['CHECK_FLAG'] != 2){
			$this->wrong("请等待审核！");
		}
		if($info['ACCT_FLAG'] != 1){
			$this->wrong("当前状态下不允许到账确认操作！");
		}
		$info['CHECK_TITLE'] = '收款确认';
		//通道信息
		$host_data = D($this->MHost)->findHost("HOST_MAP_ID = '".$info['HOST_MAP_ID']."'", 'HOST_NAME');
		$info['HOST_NAME'] = $host_data['HOST_NAME'];
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('bankacct_flag', 		C('BANKACCT_FLAG'));	//结算标志
		$this->assign ('info', 				$info);
		$this->display('hbill_check');
	}
	/*
	* 通道清算	导出
	**/
	public function hbill_export() {
		$post  = array(
			'HOST_MAP_ID'		=>	I('HOST_MAP_ID'),
			'SETTLE_DATE_A'		=>	I('SETTLE_DATE_A'),
			'SETTLE_DATE_B'		=>	I('SETTLE_DATE_B'),
			'CHECK_FLAG'		=>	I('CHECK_FLAG'),
			'ACCT_FLAG'			=>	I('ACCT_FLAG'),
		);
		$where = "1=1";
		//支付通道
		if($post['HOST_MAP_ID']) {
			$where .= " and HOST_MAP_ID = '".$post['HOST_MAP_ID']."'";
		}
		//交易日期	开始
		if($post['SETTLE_DATE_A']) {
			$where .= " and SETTLE_DATE >= '".date('Ymd',strtotime($post['SETTLE_DATE_A']))."'";
		}
		//交易日期	结束
		if($post['SETTLE_DATE_B']) {
			$where .= " and SETTLE_DATE <= '".date('Ymd',strtotime($post['SETTLE_DATE_B']))."'";
		}
		//审核状态
		if($post['CHECK_FLAG'] != '') {
			$where .= " and CHECK_FLAG = '".$post['CHECK_FLAG']."'";
		}
		//划转
		if($post['ACCT_FLAG'] != '') {
			$where .= " and ACCT_FLAG = '".$post['ACCT_FLAG']."'";
		}
		
		//计算
		$count   = D($this->THbill)->countHbill($where);
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
			$list = D($this->THbill)->getHbilllist($where, '*', $bRow.','.$eRow);
			
			//通道列表
			$host = D($this->MHost)->getHostlist('HOST_STATUS = 0', 'HOST_MAP_ID,HOST_NAME');
			foreach($host as $val){
				$host_list[$val['HOST_MAP_ID']] = $val['HOST_NAME'];
			}		
			//导出操作
			$xlsname = '通道清算文件('.($p+1).')';
			$xlscell = array(
				array('SETTLE_DATE',	'日期'),
				array('HOST_MAP_ID',	'通道名称'),
				array('PT_CON_CNT',		'消费笔数'),
				array('PT_CON_AMT',		'消费金额'),
				array('PT_REF_CNT',		'退货笔数'),
				array('PT_REF_AMT',		'退货金额'),
				array('PT_SETTLE_AMT',	'应结'),		
				array('HT_SETTLE_AMT',	'实结'),		
				array('CHECK_FLAG',		'审核状态'),
				array('ACCT_FLAG',		'划转状态'),
				array('ACCT_DESC',		'备注'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'SETTLE_DATE'	=>	$val['SETTLE_DATE']."\t",
					'HOST_MAP_ID'	=>	$host_list[$val['HOST_MAP_ID']],
					'PT_CON_CNT'	=>	$val['PT_CON_CNT'],
					'PT_CON_AMT'	=>	setMoney($val['PT_CON_AMT'], 2, 2),
					'PT_REF_CNT'	=>	$val['PT_REF_CNT'],
					'PT_REF_AMT'	=>	setMoney($val['PT_REF_AMT'], 2, 2),
					'PT_SETTLE_AMT'=>	setMoney($val['PT_SETTLE_AMT'], 2, 2),
					'HT_SETTLE_AMT'=>	setMoney($val['HT_SETTLE_AMT']+$val['HL_SETTLE_AMT'], 2, 2),
					'CHECK_FLAG'	=>	C('CHECK_FLAG_HBILL')[$val['CHECK_FLAG']],
					'ACCT_FLAG'		=>	C('ACCT_FLAG')[$val['ACCT_FLAG']],
					'ACCT_DESC'		=>	$val['ACCT_DESC'],
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	
	
	
	
	
	/*
	* 平台收单分润结算
	**/
	public function acqbill() {
		$post = I('post');
		if($post['submit'] == "acqbill"){
			$where = "1=1";
			//账号
			if($post['ACCT_NO']) {
				$where .= " and ACCT_NO = '".$post['ACCT_NO']."'";
			}
			//交易日期	开始
			if($post['SETTLE_DATE_A']) {
				$where .= " and SETTLE_DATE >= '".date('Ymd',strtotime($post['SETTLE_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SETTLE_DATE_B']) {
				$where .= " and SETTLE_DATE <= '".date('Ymd',strtotime($post['SETTLE_DATE_B']))."'";
			}
			//审核状态
			if($post['CHECK_FLAG'] != '') {
				$where .= " and CHECK_FLAG = '".$post['CHECK_FLAG']."'";
			}
			//划转
			if($post['ACCT_FLAG'] != '') {
				$where .= " and ACCT_FLAG = '".$post['ACCT_FLAG']."'";
			}
			//分页
			$count = D($this->TAcqbill)->countAcqbill($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TAcqbill)->getAcqbilllist($where, '*', $p->firstRow.','.$p->listRows);
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
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 平台收单分润结算	结算单
	**/
	public function acqbill_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TAcqbill)->findAcqbill("ACQBILL_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('bankacct_flag', 		C('BANKACCT_FLAG'));	//结算标志
		$this->assign ('info', 				$info);
		$this->display();
	}
	/*
	* 平台收单分润结算 初审
	**/
	public function acqbill_check() {
		$post = I('post');
		if($post['submit'] == "acqbill_check"){
			//验证
			if(empty($post['ACQBILL_ID'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'CHECK_FLAG'	=> 	1,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$res = D($this->TAcqbill)->updateAcqbill("ACQBILL_ID = '".$post['ACQBILL_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('初审通过！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TAcqbill)->findAcqbill("ACQBILL_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['CHECK_FLAG'] != 0){
			$this->wrong("当前状态下不允许初审操作！");
		}
		$info['CHECK_TITLE'] = '初审';
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('bankacct_flag', 		C('BANKACCT_FLAG'));	//结算标志
		$this->assign ('info', 				$info);
		$this->display();
	}
	/*
	* 平台收单分润结算 复核
	**/
	public function acqbill_recheck() {
		$post = I('post');
		if($post['submit'] == "acqbill_recheck"){
			//验证
			if(empty($post['ACQBILL_ID'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'CHECK_FLAG'	=> 	2,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$res = D($this->TAcqbill)->updateAcqbill("ACQBILL_ID = '".$post['ACQBILL_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('复核通过！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TAcqbill)->findAcqbill("ACQBILL_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['CHECK_FLAG'] != 1){
			$this->wrong("当前状态下不允许复核操作！");
		}
		$info['CHECK_TITLE'] = '复核';
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('bankacct_flag', 		C('BANKACCT_FLAG'));	//结算标志
		$this->assign ('info', 				$info);
		$this->display('acqbill_check');
	}
	/*
	* 平台收单分润结算 结算确认
	**/
	public function acqbill_affirm() {
		$post = I('post');
		if($post['submit'] == "acqbill_affirm"){
			//验证
			if(empty($post['ACQBILL_ID'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'ACCT_FLAG'		=> 0,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$res = D($this->TAcqbill)->updateAcqbill("ACQBILL_ID = '".$post['ACQBILL_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('复核通过！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TAcqbill)->findAcqbill("ACQBILL_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if($info['CHECK_FLAG'] != 2){
			$this->wrong("请等待审核！");
		}
		if($info['ACCT_FLAG'] != 1){
			$this->wrong("当前状态下不允许结算确认操作！");
		}
		$info['CHECK_TITLE'] = '结算确认';
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('bankacct_flag', 		C('BANKACCT_FLAG'));	//结算标志
		$this->assign ('info', 				$info);
		$this->display('acqbill_check');
	}
	/*
	* 平台收单分润结算	导出
	**/
	public function acqbill_export() {
		$post  = array(
			'ACCT_NO'			=>	I('ACCT_NO'),
			'SETTLE_DATE_A'		=>	I('SETTLE_DATE_A'),
			'SETTLE_DATE_B'		=>	I('SETTLE_DATE_B'),
			'CHECK_FLAG'		=>	I('CHECK_FLAG'),
			'ACCT_FLAG'			=>	I('ACCT_FLAG'),
		);
		$where = "1=1";
		//账号
		if($post['ACCT_NO']) {
			$where .= " and ACCT_NO = '".$post['ACCT_NO']."'";
		}
		//交易日期	开始
		if($post['SETTLE_DATE_A']) {
			$where .= " and SETTLE_DATE >= '".date('Ymd',strtotime($post['SETTLE_DATE_A']))."'";
		}
		//交易日期	结束
		if($post['SETTLE_DATE_B']) {
			$where .= " and SETTLE_DATE <= '".date('Ymd',strtotime($post['SETTLE_DATE_B']))."'";
		}
		//审核状态
		if($post['CHECK_FLAG'] != '') {
			$where .= " and CHECK_FLAG = '".$post['CHECK_FLAG']."'";
		}
		//划转
		if($post['ACCT_FLAG'] != '') {
			$where .= " and ACCT_FLAG = '".$post['ACCT_FLAG']."'";
		}
		
		//计算
		$count   = D($this->TAcqbill)->countAcqbill($where);
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
			$list = D($this->TAcqbill)->getAcqbilllist($where, '*', $bRow.','.$eRow);
				
			//导出操作
			$xlsname = '平台收单分润结算文件('.($p+1).')';
			$xlscell = array(
				array('SETTLE_DATE',	'日期'),
				array('ACCT_NO',		'户名'),
				array('PT_CON_CNT',		'消费笔数'),
				array('PT_CON_AMT',		'消费金额'),
				array('PT_REF_CNT',		'退货笔数'),
				array('PT_REF_AMT',		'退货金额'),
				array('PT_SETTLE_AMT',	'应结'),		
				array('HT_SETTLE_AMT',	'实结'),		
				array('CHECK_FLAG',		'审核'),
				array('ACCT_FLAG',		'划转'),
				array('ACCT_DESC',		'备注'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'SETTLE_DATE'	=>	$val['SETTLE_DATE']."\t",
					'ACCT_NO'		=>	getacct_name($val['ACCT_NO'], '20201'),
					'PT_CON_CNT'	=>	$val['PT_CON_CNT'],
					'PT_CON_AMT'	=>	setMoney($val['PT_CON_AMT'], 2, 2),
					'PT_REF_CNT'	=>	$val['PT_REF_CNT'],
					'PT_REF_AMT'	=>	setMoney($val['PT_REF_AMT'], 2, 2),
					'PT_SETTLE_AMT'=>	setMoney($val['PT_SETTLE_AMT'], 2, 2),
					'HT_SETTLE_AMT'=>	setMoney($val['HT_SETTLE_AMT']+$val['HL_SETTLE_AMT'], 2, 2),
					'CHECK_FLAG'	=>	C('CHECK_FLAG_HBILL')[$val['CHECK_FLAG']],
					'ACCT_FLAG'		=>	C('ACCT_FLAG')[$val['ACCT_FLAG']],
					'ACCT_DESC'		=>	$val['ACCT_DESC'],
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	
	
	
	
	
	/*
	* 分公司平台收益结算
	**/
	public function bbill() {
		$post = I('post');
		if($post['submit'] == "bbill"){
			$where = "1=1";
			//过滤
			$getlevel = filter_data('plv');
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID'];
			}
			
			//账号类别
			if($post['BANKACCT_FLAG'] != '') {
				$where .= " and BANKACCT_FLAG = '".$post['BANKACCT_FLAG']."'";
			}
			//审核状态
			if($post['CHECK_FLAG'] != '') {
				$where .= " and CHECK_FLAG = '".$post['CHECK_FLAG']."'";
			}
			//划转
			if($post['ACCT_FLAG'] != '') {
				$where .= " and ACCT_FLAG = '".$post['ACCT_FLAG']."'";
			}
			//交易金额	开始
			if($post['SETTLE_AMT_A']) {
				$where .= " and SETTLE_AMT >= '".setMoney($post['SETTLE_AMT_A'], '2')."'";
			}
			//交易金额	结束
			if($post['SETTLE_AMT_B']) {
				$where .= " and SETTLE_AMT <= '".setMoney($post['SETTLE_AMT_B'], '2')."'";
			}
			//发票
			if($post['TAX_TICKET_FLAG'] != '') {
				$where .= " and TAX_TICKET_FLAG = '".$post['TAX_TICKET_FLAG']."'";
			}
			//公司名称
			if($post['BRANCH_NAME']){
				$pids = get_branchname_childs($post['BRANCH_NAME']);
				if($pids){
					$where .= " and BRANCH_MAP_ID in (".$pids.")";
				}				
			}
			//来源
			$where .= " and SOURCE = '".$post['SOURCE']."'";
			$twhere = $where;
			
			//交易月份
			if($post['SETTLE_DATE']) {
				$twhere .= " and SETTLE_DATE = '".$post['SETTLE_DATE']."'";
				$twhere2 = " and SETTLE_DATE = '".$post['SETTLE_DATE']."'";
			}			
			//分页
			$count = D($this->TBbill)->countBbill($twhere." and BRANCH_MAP_ID != 100000");
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TBbill)->getBbilllist($twhere." and BRANCH_MAP_ID != 100000", '*', $p->firstRow.','.$p->listRows, 'SETTLE_DATE desc,ACCT_TIME desc,SETTLE_AMT desc');
			//$total = D($this->TBbill)->findBbill($twhere." and BRANCH_MAP_ID != 100000",'sum(CON_AMT) as CON_AMT,sum(REF_AMT) as REF_AMT,sum(SETTLE_AMT) as SETTLE_AMT,sum(SHARE_FEE) as SHARE_FEE');
			$total = D($this->TBbill)->findBbill($twhere." and BRANCH_MAP_ID != 100000",'sum(SETTLE_AMT) as SETTLE_AMT,sum(SHARE_FEE) as SHARE_FEE');
			$zongbu_dk = D($this->TBbill)->findBbill("BRANCH_MAP_ID = 100000".$twhere2,'sum(SHARE_FEE) as SHARE_FEE');
			$tlist = array();
			if(!empty($list)){
				foreach($list as $val){
					$acct = D($this->TBbill)->findBbill($where." and BRANCH_MAP_ID='".$val['BRANCH_MAP_ID']."' and ACCT_FLAG=1 and SETTLE_DATE<=".$val['SETTLE_DATE'], 'sum(SETTLE_AMT) as AMT');		
					$val['fukuan_AMT']   = $acct['AMT'];		//累计未付款
					$tick = D($this->TBbill)->findBbill($where." and BRANCH_MAP_ID='".$val['BRANCH_MAP_ID']."' and TAX_TICKET_FLAG=1 and SETTLE_DATE<=".$val['SETTLE_DATE'], 'sum(SETTLE_AMT) as AMT');	
					$val['kaipiao_AMT']  = $tick['AMT'];		//累计未开票
					$tlist[] = $val;
				}
			}
			
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 		$post );
			$this->assign ( 'list', 			$tlist );
			$this->assign ( 'total', 			$total );
			
			//根据时间显示代扣统计内容
			if($post['SETTLE_DATE']) {
				$dk_text = '分公司手续费分摊总额 '.setMoney($total['SHARE_FEE'],2,2).' 元 (总部分摊：'.setMoney($zongbu_dk['SHARE_FEE'],2,2).' 元)';
				$this->assign ( 'dk_text', 	$dk_text);
			}
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		//组装12个月数据
		$date_arr = array();
		$year = date('Y');
		for ($i=1; $i <= 12; $i++) {
			$month = strlen($i) == 1 ? '0'.$i : $i;
			$date_arr[$year.$month] = $year.'-'.$month;
		}
		$this->assign('date_arr', 			$date_arr);				//月份
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('tax_ticket_flag',	C('TAX_TICKET_FLAG'));	//发票标志
		$this->assign('bankacct_flag',		C('BANKACCT_FLAG'));	//结算标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 分公司平台收益结算	结算单
	**/
	public function bbill_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TBbill)->findBbill("BBILL_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('bankacct_flag', 		C('BANKACCT_FLAG'));	//结算标志
		$this->assign ('info', 				$info);
		$this->display();
	}
	/*
	* 分公司平台收益结算 初审
	**/
	public function bbill_check() {
		$post = I('post');
		if($post['submit'] == "bbill_check"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'CHECK_FLAG'	=> 	1,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$where['BBILL_ID'] = array('in', $ids);
			$res = D($this->TBbill)->updateBbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('初审通过！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TBbill)->getBbilllist("BBILL_ID in (".$ids.") and CHECK_FLAG = '0'", 'BBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'BBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display();
	}
	/*
	* 分公司平台收益结算 复核
	**/
	public function bbill_recheck() {
		$post = I('post');
		if($post['submit'] == "bbill_recheck"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'CHECK_FLAG'	=> 	2,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$where['BBILL_ID'] = array('in', $ids);
			$res = D($this->TBbill)->updateBbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('复核通过！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TBbill)->getBbilllist("BBILL_ID in (".$ids.") and CHECK_FLAG = '1'", 'BBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'BBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display('bbill_check');
	}
	/*
	* 分公司平台收益结算 一键初审
	**/
	public function yjbbill_check() {
		$list = D($this->TBbill)->getBbilllist("CHECK_FLAG = '0'", 'BBILL_ID');
		if(empty($list)){
			$this->wrong("无数据可一键初审！");
		}
		$ids = i_array_column($list, 'BBILL_ID');
		
		$where['BBILL_ID'] = array('in', $ids);
		$res = D($this->TBbill)->updateBbill($where, array('CHECK_FLAG'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('一键初审成功！');
	}
	/*
	* 分公司平台收益结算 一键复核
	**/
	public function yjbbill_recheck() {
		$list = D($this->TBbill)->getBbilllist("CHECK_FLAG = '1'", 'BBILL_ID');
		if(empty($list)){
			$this->wrong("无数据可一键复核！");
		}
		$ids = i_array_column($list, 'BBILL_ID');
		
		$where['BBILL_ID'] = array('in', $ids);
		$res = D($this->TBbill)->updateBbill($where, array('CHECK_FLAG'=> 2));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('一键复核成功！');
	}
	/*
	* 分公司平台收益结算 付款确认
	**/
	public function bbill_affirm() {
		$post = I('post');
		if($post['submit'] == "bbill_affirm"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'ACCT_FLAG'		=> 0,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$where['BBILL_ID'] = array('in', $ids);
			$res = D($this->TBbill)->updateBbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('付款确认成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TBbill)->getBbilllist("BBILL_ID in (".$ids.") and CHECK_FLAG='2' and ACCT_FLAG='1'", 'BBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'BBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display('bbill_check');
	}
	/*
	* 分公司平台收益结算 发票确认
	**/
	public function bbill_invo() {
		$post = I('post');
		if($post['submit'] == "bbill_invo"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'TAX_TICKET_FLAG'	=> 0,
				'ACCT_DESC'			=>	$post['ACCT_DESC']
			);
			$where['BBILL_ID'] = array('in', $ids);
			$res = D($this->TBbill)->updateBbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('发票确认成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TBbill)->getBbilllist("BBILL_ID in (".$ids.") and CHECK_FLAG='2' and TAX_TICKET_FLAG='1'", 'BBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'BBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display('bbill_check');
	}
	/*
	* 分公司平台收益结算 发票撤销
	**/
	public function bbill_noinvo() {
		$post = I('post');
		if($post['submit'] == "bbill_noinvo"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'TAX_TICKET_FLAG'	=> 1,
				'ACCT_DESC'			=>	$post['ACCT_DESC']
			);
			$where['BBILL_ID'] = array('in', $ids);
			$res = D($this->TBbill)->updateBbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('发票撤销成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TBbill)->getBbilllist("BBILL_ID in (".$ids.") and CHECK_FLAG='2' and TAX_TICKET_FLAG='0'", 'BBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'BBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display('bbill_check');
	}
	/*
	* 分公司平台收益结算	导出
	**/
	public function bbill_export() {
		$post  = array(
			'BANKACCT_FLAG'		=>	I('BANKACCT_FLAG'),
			'SETTLE_DATE'		=>	I('SETTLE_DATE'),
			'CHECK_FLAG'		=>	I('CHECK_FLAG'),
			'ACCT_FLAG'			=>	I('ACCT_FLAG'),
			'SETTLE_AMT_A'		=>	I('SETTLE_AMT_A'),
			'SETTLE_AMT_B'		=>	I('SETTLE_AMT_B'),
			'TAX_TICKET_FLAG'	=>	I('TAX_TICKET_FLAG'),
		);
		$where = "1=1";
		//过滤
		$getlevel = filter_data('plv');
		$post['BRANCH_MAP_ID']  = $getlevel['bid'];
		$post['PARTNER_MAP_ID'] = $getlevel['pid'];
		if($post['BRANCH_MAP_ID']){
			$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
		}
		
		//账号类别
		if($post['BANKACCT_FLAG'] != '') {
			$where .= " and BANKACCT_FLAG = '".$post['BANKACCT_FLAG']."'";
		}
		//审核状态
		if($post['CHECK_FLAG'] != '') {
			$where .= " and CHECK_FLAG = '".$post['CHECK_FLAG']."'";
		}
		//划转
		if($post['ACCT_FLAG'] != '') {
			$where .= " and ACCT_FLAG = '".$post['ACCT_FLAG']."'";
		}
		//交易金额	开始
		if($post['SETTLE_AMT_A']) {
			$where .= " and SETTLE_AMT >= '".setMoney($post['SETTLE_AMT_A'], '2')."'";
		}
		//交易金额	结束
		if($post['SETTLE_AMT_B']) {
			$where .= " and SETTLE_AMT <= '".setMoney($post['SETTLE_AMT_B'], '2')."'";
		}
		//发票
		if($post['TAX_TICKET_FLAG'] != '') {
			$where .= " and TAX_TICKET_FLAG = '".$post['TAX_TICKET_FLAG']."'";
		}
		
		$twhere = $where;
		
		//交易月份
		if($post['SETTLE_DATE']) {
			$twhere .= " and SETTLE_DATE = '".$post['SETTLE_DATE']."'";
		}			
		//计算
		$count   = D($this->TBbill)->countBbill($twhere." and BRANCH_MAP_ID != 100000");
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
			$list = D($this->TBbill)->getBbilllist($twhere." and BRANCH_MAP_ID != 100000", '*', $bRow.','.$eRow, 'SETTLE_DATE desc,ACCT_TIME desc,SETTLE_AMT desc');
			$tlist = array();
			if(!empty($list)){
				foreach($list as $val){
					$acct = D($this->TBbill)->findBbill($where." and BRANCH_MAP_ID='".$val['BRANCH_MAP_ID']."' and ACCT_FLAG=1 and SETTLE_DATE<=".$val['SETTLE_DATE'], 'sum(SETTLE_AMT) as AMT');		
					$val['fukuan_AMT']   = $acct['AMT'];		//累计未付款
					$tick = D($this->TBbill)->findBbill($where." and BRANCH_MAP_ID='".$val['BRANCH_MAP_ID']."' and TAX_TICKET_FLAG=1 and SETTLE_DATE<=".$val['SETTLE_DATE'], 'sum(SETTLE_AMT) as AMT');	
					$val['kaipiao_AMT']  = $tick['AMT'];		//累计未开票
					$b_data = D($this->MBranch)->findBranch("BRANCH_MAP_ID='".$val['BRANCH_MAP_ID']."'",'CITY_NO,BRANCH_NAME');
					$val['CITY_NAME']  = $b_data['CITY_NO'];		//累计未开票
					$val['BRANCH_NAME']  = $b_data['BRANCH_NAME'];		//累计未开票
					$tlist[] = $val;
				}
			}
			$zong = D($this->TBbill)->findBbill($where, 'sum(SETTLE_AMT) as AMT,count(*) as CNT');	
				
			//导出操作
			$xlsname = '分公司平台收益结算文件('.($p+1).')';
			$xlscel1 = array(
				array('PINGTAI11',		'审核方式'),
				array('PINGTAI12',		'1'),
			);
			$xlscel2 = array(
				array('PINGTAI21',		'总金额'),
				array('PINGTAI22',		setMoney($zong['AMT'], 2, 2)),
			);
			$xlscel3 = array(
				array('PINGTAI31',		'总笔数'),
				array('PINGTAI32',		$zong['CNT']),
			);
			$xlscel4 = array(
				array('PINGTAI41',		'制单类型'),
				array('PINGTAI42',		'企业自制凭证号'),
				array('PINGTAI43',		'客户号'),
				array('PINGTAI44',		'预约标志'),
				array('PINGTAI45',		'付款账号'),
				array('PINGTAI46',		'累计未付款'),
				array('PINGTAI47',		'收款账号'),
				array('PINGTAI48',		'收款人姓名'),		
				array('PINGTAI49',		'收款账户类型'),
				array('PINGTAI50',		'子客户号'),
				array('PINGTAI51',		'子付款账号'),
				array('PINGTAI52',		'子付款账户名'),
				array('PINGTAI53',		'子付款账户开户行名'),
				array('PINGTAI54',		'用途'),
				array('PINGTAI55',		'汇路'),
				array('PINGTAI56',		'是否通知收款人'),
				array('PINGTAI57',		'手机号码'),
				array('PINGTAI58',		'邮箱'),
				array('PINGTAI59',		'支付行号&支付行名称'),
				array('PINGTAI60',		'结算金额'),
				array('PINGTAI61',		'累计未开票'),
				array('PINGTAI62',		'代扣手续费'),
				array('PINGTAI63',		'所在城市'),
			);
			$xlsarray = array();
			foreach($tlist as $val){
				$xlsarray[] = array(
					'PINGTAI41'		=>	'2',
					'PINGTAI42'		=>	'',
					'PINGTAI43'		=>	'',
					'PINGTAI44'		=>	'0',
					'PINGTAI45'		=>	'',
					'PINGTAI46'		=>	setMoney($val['fukuan_AMT'],2,2),
					'PINGTAI47'		=>	$val['BANKACCT_NO']."\t",
					'PINGTAI48'		=>	$val['BANKACCT_NAME'],
					'PINGTAI49'		=>	'0',
					'PINGTAI50'		=>	'',
					'PINGTAI51'		=>	'',
					'PINGTAI52'		=>	'',
					'PINGTAI53'		=>	'',
					'PINGTAI54'		=>	'积分宝消费养老收益款',
					'PINGTAI55'		=>	'6',
					'PINGTAI56'		=>	'0',
					'PINGTAI57'		=>	'',
					'PINGTAI58'		=>	'',
					'PINGTAI59'		=>	$val['BANKACCT_BID'].'&'.$val['BANK_NAME'],
					'PINGTAI60'		=>	setMoney($val['SETTLE_AMT'], 2, 2),
					'PINGTAI61'		=>	setMoney($val['kaipiao_AMT'],2,2),
					'PINGTAI62'		=>	setMoney($val['SHARE_FEE'],2,2),
					'PINGTAI63'		=>	getcity_name($val['CITY_NAME'])
				);
			}
			D($this->MExcel)->BexportExcel($xlsname, $xlscel1, $xlscel2, $xlscel3, $xlscel4, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	
	
	
	
	/*
	* 合作伙伴平台收益结算
	**/
	public function pbill() {
		$post = I('post');				
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
				'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
				'BANKACCT_FLAG'		=>	I('BANKACCT_FLAG'),
				'SETTLE_DATE'		=>	I('SETTLE_DATE'),
				'CHECK_FLAG'		=>	I('CHECK_FLAG'),
				'ACCT_FLAG'			=>	I('ACCT_FLAG'),
				'SETTLE_AMT_A'		=>	I('SETTLE_AMT_A'),
				'SETTLE_AMT_B'		=>	I('SETTLE_AMT_B'),
				'TAX_TICKET_FLAG'	=>	I('TAX_TICKET_FLAG'),
				'PARTNER_NAME'		=>	I('PARTNER_NAME'),
				'tax_FLAG'			=>	I('tax_FLAG'),
				'SOURCE'			=>	I('SOURCE'),
			);
			$ajax_soplv = array(
				'bid'				=>	I('BRANCH_MAP_ID'),
				'pid'				=>	I('PARTNER_MAP_ID'),			
			);
		}
		//===结束===
		if($post['submit'] == "pbill"){
			$where = "1=1";
			//账号类别
			if($post['BANKACCT_FLAG'] != '') {
				$where .= " and BANKACCT_FLAG = '".$post['BANKACCT_FLAG']."'";
			}
			//归属
			//===优化统计===
			$getlevel = $ajax == 'loading' ? $ajax_soplv : filter_data('plv');	//列表查询
			//===结束=======
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			//特许处理
			if($post['BRANCH_MAP_ID'] && !$post['PARTNER_MAP_ID']){
				$pids = get_partbran_childs($post['BRANCH_MAP_ID']);
				$where .= " and PARTNER_MAP_ID in (".$pids.")";				
			}
			if($post['BRANCH_MAP_ID'] && $post['PARTNER_MAP_ID']){
				$pids = get_plv_childs($post['PARTNER_MAP_ID'],1);
				$where .= " and PARTNER_MAP_ID in (".$pids.")";
			}
			//审核状态
			if($post['CHECK_FLAG'] != '') {
				$where .= " and CHECK_FLAG = '".$post['CHECK_FLAG']."'";
			}
			//划转
			if($post['ACCT_FLAG'] != '') {
				$where .= " and ACCT_FLAG = '".$post['ACCT_FLAG']."'";
			}
			//交易金额	开始
			if($post['SETTLE_AMT_A']) {
				$where .= " and SETTLE_AMT >= '".setMoney($post['SETTLE_AMT_A'], '2')."'";
			}
			//交易金额	结束
			if($post['SETTLE_AMT_B']) {
				$where .= " and SETTLE_AMT <= '".setMoney($post['SETTLE_AMT_B'], '2')."'";
			}
			//发票
			if($post['TAX_TICKET_FLAG'] != '') {
				$where .= " and TAX_TICKET_FLAG = '".$post['TAX_TICKET_FLAG']."'";
			}
			//合作伙伴名称
			if($post['PARTNER_NAME']){
				$pids = get_partname_childs($post['PARTNER_NAME']);
				$where .= " and PARTNER_MAP_ID in (".$pids.")";
			}			
			//来源
			// $where .= " and SOURCE = '".$post['SOURCE']."'";
			$twhere = $where;		
			//交易月份
			if($post['SETTLE_DATE']) {
				$twhere .= " and SETTLE_DATE = '".$post['SETTLE_DATE']."'";
				$twhere2 = " and SETTLE_DATE = '".$post['SETTLE_DATE']."'";
			}
			//===优化统计===
			if($ajax == 'loading'){
				$total = D($this->TPbill)->findPbill($twhere,'count(*) as CNT,sum(SETTLE_AMT) as SETTLE_AMT,sum(SHARE_FEE) as SHARE_FEE');
				$resdata = array(
					'count'	=>	$total['CNT'],
					'amt'	=>	setMoney($total['SETTLE_AMT'], 2, 2),
					'fee'	=>	setMoney($total['SHARE_FEE'], 2, 2),
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->TPbill)->getPbilllist($twhere, '*', $fiRow.','.$liRow, 'SETTLE_DATE desc,ACCT_TIME desc,SETTLE_AMT desc');
			$tlist = array();
			if(!empty($list)){
				foreach($list as $val){
					$acct = D($this->TPbill)->findPbill($where." and PARTNER_MAP_ID='".$val['PARTNER_MAP_ID']."' and ACCT_FLAG=1 and SETTLE_DATE<=".$val['SETTLE_DATE'], 'sum(SETTLE_AMT) as AMT');		
					$val['fukuan_AMT']   = $acct['AMT'];		//累计未付款
					$tick = D($this->TPbill)->findPbill($where." and PARTNER_MAP_ID='".$val['PARTNER_MAP_ID']."' and TAX_TICKET_FLAG=1 and SETTLE_DATE<=".$val['SETTLE_DATE'], 'sum(SETTLE_AMT) as AMT');	
					$val['kaipiao_AMT']  = $tick['AMT'];		//累计未开票
					$leveldata = D($this->MPartner)->findPartner("PARTNER_MAP_ID = '".$val['PARTNER_MAP_ID']."'", 'l.REMINDER_AMT');
					$val['REMINDER_AMT'] = $leveldata['REMINDER_AMT'];							//没什么用处，暂放页面节点显示
					$val['tax_FLAG']	 = $tick['AMT']>=$leveldata['REMINDER_AMT'] ? '0' : '1';	//发票提醒
					if($post['tax_FLAG']=='0' && $val['tax_FLAG']=='0'){
						$tlist[] = $val;
					}else if($post['tax_FLAG']=='1' && $val['tax_FLAG']=='1'){
						$tlist[] = $val;
					}else if($post['tax_FLAG']==''){
						$tlist[] = $val;
					}
				}
			}
			
			//分页参数
			$this->assign ( 'totalCount', 	C('PAGE_COUNT')==count($list) ? 1 : 0 );
	       	$this->assign ( 'numPerPage', 	'' );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
						
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$tlist );

			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		//组装12个月数据
		$date_arr = array();
		$year = date('Y');
		for ($i=1; $i <= 12; $i++) {
			$month = strlen($i) == 1 ? '0'.$i : $i;
			$date_arr[$year.$month] = $year.'-'.$month;
		}
		$this->assign('date_arr', 			$date_arr);				//月份
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('tax_ticket_flag',	C('TAX_TICKET_FLAG'));	//发票标志
		$this->assign('bankacct_flag',		C('BANKACCT_FLAG'));	//结算标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 合作伙伴平台收益结算	结算单
	**/
	public function pbill_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TPbill)->findPbill("PBILL_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('bankacct_flag', 		C('BANKACCT_FLAG'));	//结算标志
		$this->assign ('info', 				$info);
		$this->display();
	}
	/*
	* 合作伙伴平台收益结算 初审
	**/
	public function pbill_check() {
		$post = I('post');
		if($post['submit'] == "pbill_check"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'CHECK_FLAG'	=> 	1,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$where['PBILL_ID'] = array('in', $ids);
			$res = D($this->TPbill)->updatePbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('初审通过！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TPbill)->getPbilllist("PBILL_ID in (".$ids.") and CHECK_FLAG = '0'", 'PBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'PBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display();
	}
	/*
	* 合作伙伴平台收益结算 复核
	**/
	public function pbill_recheck() {
		$post = I('post');
		if($post['submit'] == "pbill_recheck"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'CHECK_FLAG'	=> 	2,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$where['PBILL_ID'] = array('in', $ids);
			$res = D($this->TPbill)->updatePbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('复核通过！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TPbill)->getPbilllist("PBILL_ID in (".$ids.") and CHECK_FLAG = '1'", 'PBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'PBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display('pbill_check');
	}
	/*
	* 分公司平台收益结算 一键初审
	**/
	public function yjpbill_check() {
		$list = D($this->TPbill)->getPbilllist("CHECK_FLAG = '0'", 'PBILL_ID');
		if(empty($list)){
			$this->wrong("无数据可一键初审！");
		}
		$ids = i_array_column($list, 'PBILL_ID');
		
		$where['PBILL_ID'] = array('in', $ids);
		$res = D($this->TPbill)->updatePbill($where, array('CHECK_FLAG'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('一键初审成功！');
	}
	/*
	* 分公司平台收益结算 一键复核
	**/
	public function yjpbill_recheck() {
		$list = D($this->TPbill)->getPbilllist("CHECK_FLAG = '1'", 'PBILL_ID');
		if(empty($list)){
			$this->wrong("无数据可一键复核！");
		}
		$ids = i_array_column($list, 'PBILL_ID');
		
		$where['PBILL_ID'] = array('in', $ids);
		$res = D($this->TPbill)->updatePbill($where, array('CHECK_FLAG'=> 2));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('一键复核成功！');
	}
	/*
	* 合作伙伴平台收益结算 付款确认
	**/
	public function pbill_affirm() {
		$post = I('post');
		if($post['submit'] == "pbill_affirm"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'ACCT_FLAG'		=> 0,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$where['PBILL_ID'] = array('in', $ids);
			$res = D($this->TPbill)->updatePbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('付款确认成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TPbill)->getPbilllist("PBILL_ID in (".$ids.") and CHECK_FLAG = '2' and ACCT_FLAG='1'", 'PBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'PBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display('pbill_check');
	}
	/*
	* 合作伙伴平台收益结算 发票确认
	**/
	public function pbill_invo() {
		$post = I('post');
		if($post['submit'] == "pbill_invo"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'TAX_TICKET_FLAG'	=> 0,
				'ACCT_DESC'			=>	$post['ACCT_DESC']
			);
			$where['PBILL_ID'] = array('in', $ids);
			$res = D($this->TPbill)->updatePbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('发票确认成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TPbill)->getPbilllist("PBILL_ID in (".$ids.") and CHECK_FLAG = '2' and TAX_TICKET_FLAG='1'", 'PBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'PBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display('pbill_check');
	}
	/*
	* 合作伙伴平台收益结算 发票撤销
	**/
	public function pbill_noinvo() {
		$post = I('post');
		if($post['submit'] == "pbill_noinvo"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'TAX_TICKET_FLAG'	=> 1,
				'ACCT_DESC'			=>	$post['ACCT_DESC']
			);
			$where['PBILL_ID'] = array('in', $ids);
			$res = D($this->TPbill)->updatePbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('发票撤销成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TPbill)->getPbilllist("PBILL_ID in (".$ids.") and CHECK_FLAG = '2' and TAX_TICKET_FLAG='0'", 'PBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'PBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display('pbill_check');
	}
	/*
	* 合作伙伴平台收益结算	导出
	**/
	public function pbill_export() {
		$post  = array(
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'BANKACCT_FLAG'		=>	I('BANKACCT_FLAG'),
			'SETTLE_DATE'		=>	I('SETTLE_DATE'),
			'CHECK_FLAG'		=>	I('CHECK_FLAG'),
			'ACCT_FLAG'			=>	I('ACCT_FLAG'),
			'SETTLE_AMT_A'		=>	I('SETTLE_AMT_A'),
			'SETTLE_AMT_B'		=>	I('SETTLE_AMT_B'),
			'TAX_TICKET_FLAG'	=>	I('TAX_TICKET_FLAG'),
			'PARTNER_NAME'		=>	I('PARTNER_NAME'),
			'tax_FLAG'			=>	I('tax_FLAG'),
		);
		$where = "1=1";
		//账号类别
		if($post['BANKACCT_FLAG'] != '') {
			$where .= " and BANKACCT_FLAG = '".$post['BANKACCT_FLAG']."'";
		}
		//归属
		//特许处理
		if($post['BRANCH_MAP_ID'] && !$post['PARTNER_MAP_ID']){
			$pids = get_partbran_childs($post['BRANCH_MAP_ID']);
			$where .= " and PARTNER_MAP_ID in (".$pids.")";				
		}
		if($post['BRANCH_MAP_ID'] && $post['PARTNER_MAP_ID']){
			$pids = get_plv_childs($post['PARTNER_MAP_ID']);
			$where .= " and PARTNER_MAP_ID in (".$pids.")";
		}
		//审核状态
		if($post['CHECK_FLAG'] != '') {
			$where .= " and CHECK_FLAG = '".$post['CHECK_FLAG']."'";
		}
		//划转
		if($post['ACCT_FLAG'] != '') {
			$where .= " and ACCT_FLAG = '".$post['ACCT_FLAG']."'";
		}
		//交易金额	开始
		if($post['SETTLE_AMT_A']) {
			$where .= " and SETTLE_AMT >= '".setMoney($post['SETTLE_AMT_A'], '2')."'";
		}
		//交易金额	结束
		if($post['SETTLE_AMT_B']) {
			$where .= " and SETTLE_AMT <= '".setMoney($post['SETTLE_AMT_B'], '2')."'";
		}
		//发票
		if($post['TAX_TICKET_FLAG'] != '') {
			$where .= " and TAX_TICKET_FLAG = '".$post['TAX_TICKET_FLAG']."'";
		}
		//合作伙伴名称
		if($post['PARTNER_NAME']){
			$pids = get_partname_childs($post['PARTNER_NAME']);
			$where .= " and PARTNER_MAP_ID in (".$pids.")";
		}		
			
		$twhere = $where;
	
		//交易月份
		if($post['SETTLE_DATE']) {
			$twhere .= " and SETTLE_DATE = '".$post['SETTLE_DATE']."'";
		}
		//计算
		$count   = D($this->TPbill)->countPbill($twhere);
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
			$list = D($this->TPbill)->getPbilllist($twhere, '*', $bRow.','.$eRow, 'SETTLE_DATE desc,ACCT_TIME desc,SETTLE_AMT desc');
			$tlist = array();
			if(!empty($list)){
				foreach($list as $val){
					$acct = D($this->TPbill)->findPbill($where." and PARTNER_MAP_ID='".$val['PARTNER_MAP_ID']."' and ACCT_FLAG=1 and SETTLE_DATE<=".$val['SETTLE_DATE'], 'sum(SETTLE_AMT) as AMT');		
					$val['fukuan_AMT']   = $acct['AMT'];		//累计未付款
					$tick = D($this->TPbill)->findPbill($where." and PARTNER_MAP_ID='".$val['PARTNER_MAP_ID']."' and TAX_TICKET_FLAG=1 and SETTLE_DATE<=".$val['SETTLE_DATE'], 'sum(SETTLE_AMT) as AMT');	
					$val['kaipiao_AMT']  = $tick['AMT'];		//累计未开票
					$leveldata = D($this->MPartner)->findPartner("PARTNER_MAP_ID = '".$val['PARTNER_MAP_ID']."'", 'l.REMINDER_AMT');
					$val['REMINDER_AMT'] = $leveldata['REMINDER_AMT'];							//没什么用处，暂放页面节点显示
					$val['tax_FLAG']	 = $tick['AMT']>=$leveldata['REMINDER_AMT'] ? '0' : '1';	//发票提醒
					
					$b_data = D($this->MPartner)->findPartnerOne("PARTNER_MAP_ID='".$val['PARTNER_MAP_ID']."'",'CITY_NO');
					$val['CITY_NAME']  = $b_data['CITY_NO'];
					if($post['tax_FLAG']=='0' && $val['tax_FLAG']=='0'){
						$tlist[] = $val;
					}else if($post['tax_FLAG']=='1' && $val['tax_FLAG']=='1'){
						$tlist[] = $val;
					}else if($post['tax_FLAG']==''){
						$tlist[] = $val;
					}else{
						$tlist[] = $val;
					}
				}
			}
			$zong = D($this->TPbill)->findPbill($where, 'sum(SETTLE_AMT) as AMT,count(*) as CNT');		
		
			//导出操作
			$xlsname = '合作伙伴平台收益结算文件('.($p+1).')';
			$xlscel1 = array(
				array('PINGTAI11',		'审核方式'),
				array('PINGTAI12',		'1'),
			);
			$xlscel2 = array(
				array('PINGTAI21',		'总金额'),
				array('PINGTAI22',		setMoney($zong['AMT'], 2, 2)),
			);
			$xlscel3 = array(
				array('PINGTAI31',		'总笔数'),
				array('PINGTAI32',		$zong['CNT']),
			);
			$xlscel4 = array(
				array('PINGTAI41',		'制单类型'),
				array('PINGTAI42',		'企业自制凭证号'),
				array('PINGTAI43',		'客户号'),
				array('PINGTAI44',		'预约标志'),
				array('PINGTAI45',		'付款账号'),
				array('PINGTAI46',		'累计未付款'),
				array('PINGTAI47',		'收款账号'),
				array('PINGTAI48',		'收款人姓名'),		
				array('PINGTAI49',		'收款账户类型'),
				array('PINGTAI50',		'子客户号'),
				array('PINGTAI51',		'子付款账号'),
				array('PINGTAI52',		'子付款账户名'),
				array('PINGTAI53',		'子付款账户开户行名'),
				array('PINGTAI54',		'用途'),
				array('PINGTAI55',		'汇路'),
				array('PINGTAI56',		'是否通知收款人'),
				array('PINGTAI57',		'手机号码'),
				array('PINGTAI58',		'邮箱'),
				array('PINGTAI59',		'支付行号&支付行名称'),
				array('PINGTAI60',		'结算金额'),
				array('PINGTAI61',		'累计未开票'),
				array('PINGTAI62',		'代扣手续费'),
				array('PINGTAI63',		'所在城市'),
			);
			$xlsarray = array();
			foreach($tlist as $val){
				$xlsarray[] = array(
					'PINGTAI41'		=>	'2',
					'PINGTAI42'		=>	'',
					'PINGTAI43'		=>	'',
					'PINGTAI44'		=>	'0',
					'PINGTAI45'		=>	'',
					'PINGTAI46'		=>	setMoney($val['fukuan_AMT'],2,2),
					'PINGTAI47'		=>	$val['BANKACCT_NO']."\t",
					'PINGTAI48'		=>	$val['BANKACCT_NAME'],
					'PINGTAI49'		=>	'0',
					'PINGTAI50'		=>	'',
					'PINGTAI51'		=>	'',
					'PINGTAI52'		=>	'',
					'PINGTAI53'		=>	'',
					'PINGTAI54'		=>	'积分宝消费养老收益款',
					'PINGTAI55'		=>	'6',
					'PINGTAI56'		=>	'0',
					'PINGTAI57'		=>	'',
					'PINGTAI58'		=>	'',
					'PINGTAI59'		=>	$val['BANKACCT_BID'].'&'.$val['BANK_NAME'],
					'PINGTAI60'		=>	setMoney($val['SETTLE_AMT'], 2, 2),
					'PINGTAI61'		=>	setMoney($val['kaipiao_AMT'],2,2),
					'PINGTAI62'		=>	setMoney($val['SHARE_FEE'],2,2),
					'PINGTAI63'		=>	getcity_name($val['CITY_NAME'])
				);
			}
			D($this->MExcel)->BexportExcel($xlsname, $xlscel1, $xlscel2, $xlscel3, $xlscel4, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	
	
	
	
	/*
	* 商户超扣结算
	**/
	public function csbill() {
		$post = I('post');
		if($post['submit'] == "csbill"){
			$where = "1=1";
			//归属
			$getlevel = filter_data('plv');	//列表查询
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
			}
			if($post['PARTNER_MAP_ID']){
				$pids = get_plv_childs($post['PARTNER_MAP_ID']);
				$where .= " and PARTNER_MAP_ID in (".$pids.")";
			}
			
			//交易日期	开始
			if($post['SETTLE_DATE_A']) {
				$where .= " and SETTLE_DATE >= '".date('Ymd',strtotime($post['SETTLE_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SETTLE_DATE_B']) {
				$where .= " and SETTLE_DATE <= '".date('Ymd',strtotime($post['SETTLE_DATE_B']))."'";
			}
			//审核状态
			if($post['CHECK_FLAG'] != '') {
				$where .= " and CHECK_FLAG = '".$post['CHECK_FLAG']."'";
			}
			//划转
			if($post['ACCT_FLAG'] != '') {
				$where .= " and ACCT_FLAG = '".$post['ACCT_FLAG']."'";
			}
			//交易金额	开始
			if($post['SETTLE_AMT_A']) {
				$where .= " and SETTLE_AMT >= '".setMoney($post['SETTLE_AMT_A'], '2')."'";
			}
			//交易金额	结束
			if($post['SETTLE_AMT_B']) {
				$where .= " and SETTLE_AMT <= '".setMoney($post['SETTLE_AMT_B'], '2')."'";
			}
			//分页
			$count = D($this->TSckbill)->countSckbill($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TSckbill)->getSckbilllist($where, '*', $p->firstRow.','.$p->listRows);
			$total = D($this->TSckbill)->findSckbill($where, 'sum(CON_AMT) as CON_AMT,sum(REF_AMT) as REF_AMT,sum(SETTLE_AMT) as SETTLE_AMT');
			
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
			$this->assign ( 'total', 		$total );
			
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 商户超扣结算	结算单
	**/
	public function csbill_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TSckbill)->findSckbill("SCKBILL_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('check_flag', 		C('CHECK_FLAG_HBILL'));	//审核过程
		$this->assign('acct_flag', 			C('ACCT_FLAG'));		//划转标志
		$this->assign('bankacct_flag', 		C('BANKACCT_FLAG'));	//结算标志
		$this->assign ('info', 				$info);
		$this->display();
	}
	/*
	* 商户超扣结算 初审
	**/
	public function csbill_check() {
		$post = I('post');
		if($post['submit'] == "csbill_check"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'CHECK_FLAG'	=> 	1,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$where['SCKBILL_ID'] = array('in', $ids);
			$res = D($this->TSckbill)->updateSckbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('初审通过！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TSckbill)->getSckbilllist("SCKBILL_ID in (".$ids.") and CHECK_FLAG = '0'", 'SCKBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'SCKBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display();
	}
	/*
	* 商户超扣结算 复核
	**/
	public function csbill_recheck() {
		$post = I('post');
		if($post['submit'] == "csbill_recheck"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'CHECK_FLAG'	=> 	2,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$where['SCKBILL_ID'] = array('in', $ids);
			$res = D($this->TSckbill)->updateSckbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('复核通过！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
				
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TSckbill)->getSckbilllist("SCKBILL_ID in (".$ids.") and CHECK_FLAG = '1'", 'SCKBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'SCKBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display('csbill_check');
	}
	/*
	* 商户超扣结算 付款确认
	**/
	public function csbill_affirm() {
		$post = I('post');
		if($post['submit'] == "csbill_affirm"){
			$ids = explode(',',$post['ids']);
			//验证
			if(empty($ids)){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'ACCT_FLAG'		=> 0,
				'ACCT_DESC'		=>	$post['ACCT_DESC']
			);
			$where['SCKBILL_ID'] = array('in', $ids);
			$res = D($this->TSckbill)->updateSckbill($where, $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('付款确认成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$ids = $_REQUEST['ids'];
		if(empty($ids)){
			$this->wrong('缺少参数！');
		}
		$list = D($this->TSckbill)->getSckbilllist("SCKBILL_ID in (".$ids.") and CHECK_FLAG='2' and ACCT_FLAG='1'", 'SCKBILL_ID');
		$arr  = explode(',', $ids);
		if(count($arr) != count($list)){
			$this->wrong('存在 '.(count($arr)-count($list)).' 项勾选有问题！');
		}
		$str = i_array_column($list, 'SCKBILL_ID');
		$str = implode(',', $str);
		$this->assign ('ids', 	$str);
		$this->display('csbill_check');
	}
	/*
	* 商户超扣结算	导出
	**/
	public function csbill_export() {
		$post  = array(
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'SETTLE_DATE_A'		=>	I('SETTLE_DATE_A'),
			'SETTLE_DATE_B'		=>	I('SETTLE_DATE_B'),
			'CHECK_FLAG'		=>	I('CHECK_FLAG'),
			'ACCT_FLAG'			=>	I('ACCT_FLAG'),
			'SETTLE_AMT_A'		=>	I('SETTLE_AMT_A'),
			'SETTLE_AMT_B'		=>	I('SETTLE_AMT_B'),
		);
		$where = "1=1";
		//归属
		if($post['BRANCH_MAP_ID']){
			$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
		}
		if($post['PARTNER_MAP_ID']){
			$pids = get_plv_childs($post['PARTNER_MAP_ID']);
			$where .= " and PARTNER_MAP_ID in (".$pids.")";
		}			
		//交易日期	开始
		if($post['SETTLE_DATE_A']) {
			$where .= " and SETTLE_DATE >= '".date('Ymd',strtotime($post['SETTLE_DATE_A']))."'";
		}
		//交易日期	结束
		if($post['SETTLE_DATE_B']) {
			$where .= " and SETTLE_DATE <= '".date('Ymd',strtotime($post['SETTLE_DATE_B']))."'";
		}
		//审核状态
		if($post['CHECK_FLAG'] != '') {
			$where .= " and CHECK_FLAG = '".$post['CHECK_FLAG']."'";
		}
		//划转
		if($post['ACCT_FLAG'] != '') {
			$where .= " and ACCT_FLAG = '".$post['ACCT_FLAG']."'";
		}
		//交易金额	开始
		if($post['SETTLE_AMT_A']) {
			$where .= " and SETTLE_AMT >= '".setMoney($post['SETTLE_AMT_A'], '2')."'";
		}
		//交易金额	结束
		if($post['SETTLE_AMT_B']) {
			$where .= " and SETTLE_AMT <= '".setMoney($post['SETTLE_AMT_B'], '2')."'";
		}
		
		//计算
		$count   = D($this->TSckbill)->countSckbill($where);
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
			$list = D($this->TSckbill)->getSckbilllist($where, '*', $bRow.','.$eRow);
				
			//导出操作
			$xlsname = '商户超扣文件('.($p+1).')';
			$xlscell = array(
				array('SETTLE_DATE',	'日期'),
				array('BRANCH_MAP_ID',	'归属'),
				array('SHOP_MAP_ID',	'商户'),
				array('CON_CNT',		'消费笔数'),
				array('CON_AMT',		'消费金额'),
				array('REF_CNT',		'退货笔数'),
				array('REF_AMT',		'退货金额'),
				array('SETTLE_AMT',		'结算金额'),		
				array('CHECK_FLAG',		'审核'),
				array('ACCT_FLAG',		'划转'),
				array('ACCT_DESC',		'备注'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'SETTLE_DATE'		=>	$val['SETTLE_DATE']."\t",
					'BRANCH_MAP_ID'		=>	get_level_name($val['PARTNER_MAP_ID'],$val['BRANCH_MAP_ID']),
					'SHOP_MAP_ID'		=>	get_shop_name($val['SHOP_MAP_ID']),
					'CON_CNT'			=>	$val['CON_CNT'],
					'CON_AMT'			=>	setMoney($val['CON_AMT'], 2, 2),
					'REF_CNT'			=>	$val['REF_CNT'],
					'REF_AMT'			=>	setMoney($val['REF_AMT'], 2, 2),
					'SETTLE_AMT'		=>	setMoney($val['SETTLE_AMT'], 2, 2),
					'CHECK_FLAG'		=>	C('CHECK_FLAG_HBILL')[$val['CHECK_FLAG']],
					'ACCT_FLAG'			=>	C('ACCT_FLAG')[$val['ACCT_FLAG']],
					'ACCT_DESC'			=>	$val['ACCT_DESC'],
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}
	
	
	
	
		
	/*
	* 商户代扣结算
	**/
	public function dkls() {
		$post = I('post');				
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
				'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
				'SETTLE_DATE_A'		=>	I('SETTLE_DATE_A'),
				'SETTLE_DATE_B'		=>	I('SETTLE_DATE_B'),
				'DKCO_MAP_ID'		=>	I('DKCO_MAP_ID'),
				'DK_FLAG'			=>	I('DK_FLAG'),
				'SHOP_NAME'			=>	I('SHOP_NAME'),
				'SEA_TITLE'			=>	I('SEA_TITLE'),
				'DK_ORDER_ID'		=>	I('DK_ORDER_ID'),
				'BANKACCT_NO'		=>	I('BANKACCT_NO'),
			);
			$ajax_soplv = array(
				'bid'				=>	I('BRANCH_MAP_ID'),
				'pid'				=>	I('PARTNER_MAP_ID'),			
			);
		}
		//===结束===
		$post['SETTLE_DATE_A'] = $post['SETTLE_DATE_A'] ? $post['SETTLE_DATE_A'] : date('Y-m-d');
		$post['SETTLE_DATE_B'] = $post['SETTLE_DATE_B'] ? $post['SETTLE_DATE_B'] : date('Y-m-d');
		if($post['submit'] == "dkls"){
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
				$pids = get_plv_childs($post['PARTNER_MAP_ID'],1);
				$where .= " and PARTNER_MAP_ID in (".$pids.")";
			}			
			//代扣日期	开始
			if($post['SETTLE_DATE_A']) {
				$where .= " and DK_DATE >= '".date('Ymd',strtotime($post['SETTLE_DATE_A']))."'";
			}
			//代扣日期	结束
			if($post['SETTLE_DATE_B']) {
				$where .= " and DK_DATE <= '".date('Ymd',strtotime($post['SETTLE_DATE_B']))."'";
			}
			//代扣公司
			if($post['DKCO_MAP_ID']) {
				$where .= " and DKCO_MAP_ID = '".$post['DKCO_MAP_ID']."'";
			}
			//代扣结果
			if($post['DK_FLAG'] != '') {
				$where .= " and DK_FLAG = '".$post['DK_FLAG']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//关键字
			if($post['SEA_TITLE']) {
				$where .= " and (BANK_NAME like '%".$post['SEA_TITLE']."%' or RES like '%".$post['SEA_TITLE']."%')";
			}
			//银行账号
			if($post['BANKACCT_NO']) {
				$where .= " and BANKACCT_NO = '".$post['BANKACCT_NO']."'";
			}
			//代扣订单ID
			if($post['DK_ORDER_ID']) {
				$where .= " and DK_ORDER_ID = '".$post['DK_ORDER_ID']."'";
			}
			//===优化统计===
			if($ajax == 'loading'){
				$count   = D($this->TDkls)->countDkls($where);
				$total   = D($this->TDkls)->findDkls($where,'sum(DK_AMT) as DK_AMT,sum(DK_FEE) as DK_FEE');
				$resdata = array(
					'count'	=>	$count,
					'amt'	=>	setMoney($total['DK_AMT'], 2, 2),
					'fee'	=>	setMoney($total['DK_FEE'], 2, 2),
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->TDkls)->getDklslist($where, '*',  $fiRow.','.$liRow, 'DK_DATE desc,DK_TIME desc');
			
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
		
		//代扣公司列表
		$dlist  = D($this->MDkco)->getDkcolist('DKCO_STATUS = 0', 'DKCO_MAP_ID,DKCO_NAME');
		foreach($dlist as $val){
			$dk_list[$val['DKCO_MAP_ID']] = $val['DKCO_NAME'];
		}
		$this->assign('dk_list', 			$dk_list );
		$this->assign('dk_flag', 			C('DK_FLAG'));		//代扣到账标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 商户代扣结算	结算单
	**/
	public function dkls_show() {	
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TDkls)->findDkls("DK_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//详情导去
		if($_REQUEST['submit'] == "show_export"){
			$field = 't.TRANS_NAME,t.VIP_ID,t.VIP_CARDNO,t.TRANS_AMT,t.TRACE_STATUS,t.PLAT_FEE,t.CON_FEE,t.SYSTEM_DATE,t.SYSTEM_TIME';
			$where = "t.SHOP_NO='".$info['SHOP_NO']."' and t.SETTLE_SDATE='".date('Ymd',strtotime($info['SETTLE_DATE']))."' and t.PAY_TYPE=5 and t.TRACE_STATUS=0 and t.TRACE_RETCODE='00' and t.TRACE_REVERFLAG=0";
			$list  = D($this->TTrace)->getTracelist($where, 't.*,j.PLAT_FEE,j.CON_FEE');
			//导出操作
			$xlsname = '导出代扣明细';
			$xlscell = array(
				array('TRANS_NAME',		'交易类型'),
				array('VIP_ID',			'会员名称'),
				array('VIP_CARDNO',		'会员卡号'),
				array('TRANS_AMT',		'交易金额'),
				array('PLAT_FEE',		'平台分润'),
				array('CON_FEE',		'个人分润'),
				array('SYSTEM_DATE',	'交易时间'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'TRANS_NAME'		=>	$val['TRANS_NAME'],
					'VIP_ID'			=>	$val['VIP_ID'] ? getvip_name($val['VIP_ID']) : '',
					'VIP_CARDNO'		=>	$val['VIP_CARDNO'],
					'TRANS_AMT'			=>	setMoney($val['TRANS_AMT'], 2, 2),
					'PLAT_FEE'			=>	setMoney($val['PLAT_FEE'], 2, 2),
					'CON_FEE'			=>	setMoney($val['CON_FEE'], 2, 2),
					'SYSTEM_DATE'		=>	$val['SYSTEM_DATE'].' '.$val['SYSTEM_TIME'],
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;			
		}
		
		
		$where = "t.SHOP_NO='".$info['SHOP_NO']."' and t.SETTLE_SDATE='".date('Ymd',strtotime($info['SETTLE_DATE']))."' and t.PAY_TYPE=5 and t.TRACE_STATUS=0 and t.TRACE_RETCODE='00' and t.TRACE_REVERFLAG=0";
		//分页
		$count = D($this->TTrace)->countTrace($where);
		$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
		$list  = D($this->TTrace)->getTracelist($where, 't.*,j.PLAT_FEE,j.CON_FEE', $p->firstRow.','.$p->listRows);
		//分页参数
		$this->assign ( 'totalCount', 	$count );
		$this->assign ( 'numPerPage', 	$p->listRows );
		$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);		
		$this->assign ( 'list', 		$list );
			
		$this->assign('dk_flag', 			C('DK_FLAG'));			//代扣到账标志
		$this->assign('info', 				$info);
		$this->display();
	}
	/**
	 * [dkls_pay 手工代扣 银盛&ChinaPay]
	 * @return [type] [description]
	 */
	public function dkls_pay(){
		if(CON_ENVIRONMENT != 'online'){
			$this->wrong('非生产环境不能提交代扣！');
		}
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode(file_get_contents("php://input")));
		
		$home = session('HOME');
		set_time_limit(0);
		$id = $_REQUEST['id'];
		if(empty($id)) {
			$this->wrong('缺少参数！');
		}
		$info = D($this->TDkls)->findDkls("DK_ID = '".$id."'");
		if(empty($info)) {
			$this->wrong("参数数据出错！");
		}
		if($info['DK_FLAG'] == 0 || $info['DK_FLAG'] == 3) {
			$this->wrong("当前状态不允许手工单笔代扣！");
		}
		if(empty($info['DK_AMT']) || empty($info['BANKACCT_NO']) || empty($info['BANKACCT_NAME']) || !in_array($info['DKCO_MAP_ID'], array('1','2','3'))){
			$updata = array(
				'DK_DATE'		=>	date('Ymd'),
				'DK_TIME'		=>	date('His'),
				'RES'			=>	'代扣数据不完整，不能发送代扣！操作员：'.$home['USER_NO']
			);
			D($this->TDkls)->updateDkls("DK_ID = '".$info['DK_ID']."'", $updata);
			$this->wrong($updata['RES']);
		}
		if(empty($info['BANK_BID'])){
			$updata = array(
				'DK_DATE'		=>	date('Ymd'),
				'DK_TIME'		=>	date('His'),
				'RES'			=>	'缺少银行BID，不能发送代扣！操作员：'.$home['USER_NO']
			);
			D($this->TDkls)->updateDkls("DK_ID = '".$info['DK_ID']."'", $updata);
			$this->wrong($updata['RES']);
		}
		
		$order_id = substr(getmicrotime(), 2);	//16位
		//获取手续费
		$dkco	  = D($this->MDkco)->findDkco("DKCO_MAP_ID = '".$info['DKCO_MAP_ID']."'", 'DKCO_FEE_FLAG,DKCO_FEE_FIX,DKCO_FEE_PER');
		switch($dkco['DKCO_FEE_FLAG']){
			case '0':	//每笔固定金额
				$dk_sxf = $dkco['DKCO_FEE_FIX'];
				break;
			case '1':	//每笔百分比
				$dk_sxf = $info['DK_AMT']*$dkco['DKCO_FEE_PER']/100;
				break;
		}
				
		//ChinaPay
		if($info['DKCO_MAP_ID'] == 2){
			include_once(C('ChinaPay')['url_netpay']);
			$merid = buildKey(C('ChinaPay')['pri_key']);
			if(!$merid) {
				$this->wrong("导入私钥文件出错！");
			}
	 		//处理证件类型
			switch($info['DK_IDNO_TYPE']){
				case 0:
					$idno_type = '01';
					break;
				case 1:
					$idno_type = '03';
					break;
				case 2:
					$idno_type = '02';
					break;
				case 3:
					$idno_type = '05';
					break;
				default:
					$idno_type = '06';
			}
			$bankacct_name = json_encode($info['BANKACCT_NAME']);
			$bankacct_name = substr($bankacct_name, 1);
			$bankacct_name = substr($bankacct_name, 0, -1); 
			//组装数据
			$resdata  = array(
				'merId'			=>	$merid,										//商户号
				'transDate'		=>	date('Ymd'),								//商户日期
				'orderNo'		=>	$order_id,									//订单号 	16位
				'transType'		=>	'0003',										//交易类型
				'openBankId'	=>	'0'.substr($info['BANK_BID'],0,3),			//开户行号
				'cardType'		=>	$info['SHOP_ACCT_FLAG'] ? $info['SHOP_ACCT_FLAG'] : 0,	//卡折标志
				'cardNo'		=>	$info['BANKACCT_NO'],						//卡号/折号
				'usrName'		=>	$bankacct_name,								//持卡人姓名
				'certType'		=>	$idno_type,									//证件类型
				'certId'		=>	$info['DK_IDNO'],							//证件号
				'curyId'		=>	'156',										//币种
				'transAmt'		=>	$info['DK_AMT'],							//金额
				'purpose'		=>	'',											//用途
				'priv1'			=>	'\u5929',									//私有域
				'version'		=>	'20151207',									//版本号
				'gateId'		=>	'7008',										//网关号
				'termType'		=>	'07',										//渠道类型
				'payMode'		=>	'1',										//交易模式
			);
			//计算签名值
			$plain 	  = $resdata['merId'].$resdata['transDate'].$resdata['orderNo'].$resdata['transType'].$resdata['openBankId'].$resdata['cardType'].$resdata['cardNo'].$resdata['usrName'].$resdata['certType'].$resdata['certId'].$resdata['curyId'].$resdata['transAmt'].$resdata['priv1'].$resdata['version'].$resdata['gateId'].$resdata['termType'].$resdata['payMode'];
			$chkvalue = sign(base64_encode($plain));
			if(!$chkvalue) {
				$this->wrong("签名失败！");
			}
			$resdata['chkValue'] = $chkvalue; 
			Add_LOG(CONTROLLER_NAME, json_encode($resdata));			
			$res = httpPostForm(C('ChinaPay')['url_pay'], $resdata);			
			Add_LOG(CONTROLLER_NAME, json_encode($res));
			$res = str_replace('=', '":"', $res);
			$res = str_replace('&', '","', $res);
			$res = '{"'.$res.'"}';
			$res = json_decode($res, true);			
			if($res['responseCode'] != '00'){
				$updata = array(
					'DK_DATE'		=>	date('Ymd'),
					'DK_TIME'		=>	date('His'),
					'RES'			=>	$res['message'].' 操作员：'.$home['USER_NO'],
					'DK_FLAG'		=> 	2,
					'DK_ORDER_ID'	=>	$order_id
				);
				D($this->TDkls)->updateDkls("DK_ID = '".$info['DK_ID']."'", $updata);
				$this->wrong($updata['RES']);
			}
			$dk_right = $res['transStat'];
		}
		//银盛
		elseif($info['DKCO_MAP_ID'] == 1 || $info['DKCO_MAP_ID'] == 3){
			vendor('Alipay.YinSheng');
			//组装数据			
			$resdata  = array(
				'Ver'				=>	'1.0',											//版本号
				'MsgCode'			=>	'S1031',										//报文编号
				'Time'				=>	date('YmdHis'),									//发送时间
				'OrderId'			=>	$order_id,										//订单号
				'BusiCode'			=>	'1010004',										//业务代码	 max(10)	1010004
				'ShopDate'			=>	date('Ymd'),									//商户日期
				'Cur'				=>	'CNY',											//币种
				'Amount'			=>	$info['DK_AMT'],								//金额
				'Note'				=>	'积分宝佣金代扣',								//订单说明
				'BankAccountType'	=>	'11',											//账户类型	11借记卡
				'BankName'			=>	$info['BANK_NAME'],								//银行
				'AccountNo'			=>	$info['BANKACCT_NO'],							//账户号	6217993300031483990	
				'AccountName'		=>	$info['BANKACCT_NAME'],							//账户名
				'Province'			=>	'',												//开户行所在省
				'City'				=>	'',												//开户行所在市
				'BankCode'			=>	setStrzero($info['BANK_BID'], 12, '0', 'r'),	//银行行号
				'ExtraData'			=>	'',												//银行卡附加数据
			);
			Add_LOG(CONTROLLER_NAME, json_encode($resdata));
			$ysConf = 'YinshengPay';
			if($info['DKCO_MAP_ID'] == 3){
				$ysConf = 'YinshengBXHPay';
			}
	 		$Pay  = new \YinSheng(C($ysConf));
			$xml  = $Pay->yx_xmlS1031($resdata);	//组报文
			$sign = $Pay->yx_encrypt($xml);			//加密
			$res  = $Pay->yx_pay($resdata['MsgCode'], $resdata['OrderId'], $sign);
			Add_LOG(CONTROLLER_NAME, json_encode($res));
			if($res['state'] != 0){
				$updata = array(
					'DK_DATE'		=>	date('Ymd'),
					'DK_TIME'		=>	date('His'),
					'RES'			=>	$res['msg'].' 操作员：'.$home['USER_NO'],
					'DK_FLAG'		=> 	$res['state']==2 ? 3 : 2,	//3等待中
					'DK_ORDER_ID'	=>	$order_id
				);
				D($this->TDkls)->updateDkls("DK_ID = '".$info['DK_ID']."'", $updata);			
				$this->wrong($updata['RES']);
			}
			$dk_right = $res['data']['Result']['TradeSN'];
		}

		$updata = array(
			'DK_FEE'		=>	$dk_sxf,	//手续费
			'DK_DATE'		=>	date('Ymd'),
			'DK_TIME'		=>	date('His'),
			'RES'			=>	'手工单笔代扣成功！操作员：'.$home['USER_NO'],
			'DK_FLAG'		=>	0,
			'DK_ORDER_ID'	=>	$order_id,
			'DK_RET_TRACE'	=>	$dk_right,
		);
		D($this->TDkls)->updateDkls("DK_ID = '".$info['DK_ID']."'", $updata);
		//发短信
		if($info['MOBILE']){
			$smsdata = array(
				'dk_bankacct_no_tail'	=>	substr($info['BANKACCT_NO'], -4, 4),
				'datetime'				=>	date('Y-m-d H:i:s', strtotime($updata['DK_DATE'].$updata['DK_TIME'])),
				'dk_yl_amt'				=>	setMoney($info['DK_AMT'], 2, 2),
			);			
			$arr = setSmsmodel(6, $smsdata);
			$sls = array(
				'BRANCH_MAP_ID'		=>	$info['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=>	$info['PARTNER_MAP_ID'],
				'SMS_MODEL_TYPE'	=>	6,
				'VIP_FLAG'			=>	0,
				'VIP_ID'			=>	0,
				'VIP_CARDNO'		=>	'-',
				'SMS_RECV_MOB'		=>	$info['MOBILE'],
				'SMS_RECV_NAME'		=>	$info['SHOP_NAME'],
				'SMS_TEXT'			=>	$arr['str'],
				'SMS_STATUS'		=>	2,
				'SMS_DATE'			=>	date('Ymd'),
				'SMS_TIME'			=>	date('His'),
				'SMS_MODEL_ID'		=>	$arr['mid'],
				'SMS_MUL_BATCH'		=>	0,
				'SMS_RESP_ID'		=>	0,
			);
			D($this->TSmsls)->addSmsls($sls);
		}
		$this->right('手工单笔代扣成功！');
	}
	/*
	* 商户代扣结算 手工单笔代扣（银盛）
	**/
	public function dkls_yspay() {
		set_time_limit(0);
		$id = $_REQUEST['id'];
		if(empty($id)) {
			$this->wrong('缺少参数！');
		}
		$info = D($this->TDkls)->findDkls("DK_ID = '".$id."'");
		if(empty($info)) {
			$this->wrong("参数数据出错！");
		}
		if($info['DK_FLAG'] == 0) {
			$this->wrong("当前状态不允许手工单笔代扣！");
		}
		if(empty($info['DK_AMT']) || empty($info['BANK_NAME']) || empty($info['BANKACCT_NO']) || empty($info['BANKACCT_NAME']) || empty($info['BANK_BID'])){
			D($this->TDkls)->updateDkls("DK_ID = '".$info['DK_ID']."'", array('RES'=>'代扣数据不完整，不能发送代扣！'));
			$this->wrong("代扣数据不完整，不能发送代扣！");
		}
		
		vendor('Alipay.YinSheng');
		$pconfig  = C('YinshengPay');
		//组装数据
		$order_id = getmicrotime();
		$resdata  = array(
			'Ver'				=>	'1.0',											//版本号
			'MsgCode'			=>	'S1031',										//报文编号
			'Time'				=>	date('YmdHis'),									//发送时间
			'OrderId'			=>	$order_id,										//订单号
			'BusiCode'			=>	'1010004',										//业务代码	 max(10)	1010004
			'ShopDate'			=>	date('Ymd'),									//商户日期
			'Cur'				=>	'CNY',											//币种
			'Amount'			=>	$info['DK_AMT'],								//金额
			'Note'				=>	'积分宝佣金代扣',								//订单说明
			'BankAccountType'	=>	'11',											//账户类型	11借记卡
			'BankName'			=>	$info['BANK_NAME'],								//银行
			'AccountNo'			=>	$info['BANKACCT_NO'],							//账户号	6217993300031483990	
			'AccountName'		=>	$info['BANKACCT_NAME'],							//账户名
			'Province'			=>	'',												//开户行所在省
			'City'				=>	'',												//开户行所在市
			'BankCode'			=>	setStrzero($info['BANK_BID'], 12, '0', 'r'),	//银行行号
			'ExtraData'			=>	'',												//银行卡附加数据
		);
 		$Pay  = new \YinSheng($pconfig);
		$xml  = $Pay->yx_xmlS1031($resdata);	//组报文
		$sign = $Pay->yx_encrypt($xml);			//加密
		$res  = $Pay->yx_pay($resdata['MsgCode'], $resdata['OrderId'], $sign);
		if($res['state'] != 0){
			D($this->TDkls)->updateDkls("DK_ID = '".$info['DK_ID']."'", array('DK_FLAG'=> 2,'RES'=>$res['msg'],'DK_ORDER_ID'=>$order_id));			
			$this->wrong($res['msg']);
		}
		
		//代扣成功，入库
		//$dk_id  = $res['data']['Order']['OrderId'];
		$updata = array(
			'DK_DATE'		=>	date('Ymd'),
			'DK_TIME'		=>	date('His'),
			'DK_FLAG'		=>	0,
			'RES'			=>	'手工单笔代扣成功！',
			'DK_RET_TRACE'	=>	$res['data']['Result']['TradeSN'],
			'DK_ORDER_ID'	=>	$order_id,
		);
		D($this->TDkls)->updateDkls("DK_ID = '".$info['DK_ID']."'", $updata);
		
		//发短信
		if($info['MOBILE']){
			$smsdata = array(
				'dk_bankacct_no_tail'	=>	substr($resdata['AccountNo'], -4, 4),
				'datetime'				=>	date('Y-m-d H:i:s', strtotime($updata['DK_DATE'].$updata['DK_TIME'])),
				'dk_yl_amt'				=>	setMoney($resdata['Amount'], 2, 2),
			);
			$arr = setSmsmodel(6, $smsdata);
			$sls = array(
				'BRANCH_MAP_ID'		=>	$info['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=>	$info['PARTNER_MAP_ID'],
				'SMS_MODEL_TYPE'	=>	6,
				'VIP_FLAG'			=>	0,
				'VIP_ID'			=>	0,
				'VIP_CARDNO'		=>	'-',
				'SMS_RECV_MOB'		=>	$info['MOBILE'],
				'SMS_RECV_NAME'		=>	$info['SHOP_NAME'],
				'SMS_TEXT'			=>	$arr['str'],
				'SMS_STATUS'		=>	2,
				'SMS_DATE'			=>	date('Ymd'),
				'SMS_TIME'			=>	date('His'),
				'SMS_MODEL_ID'		=>	$arr['mid'],
				'SMS_MUL_BATCH'		=>	0,
				'SMS_RESP_ID'		=>	0,
			);
			D($this->TSmsls)->addSmsls($sls);
		}
		$this->right('手工单笔代扣成功！');
	}	
	/*
	* 商户代扣结算 手工单笔代扣（ChinaPay）
	**/
	public function dkls_chpay() {
		set_time_limit(0);
		$id = $_REQUEST['id'];
		if(empty($id)) {
			$this->wrong('缺少参数！');
		}
		$info = D($this->TDkls)->findDkls("DK_ID = '".$id."'");
		if(empty($info)) {
			$this->wrong("参数数据出错！");
		}
		if($info['DK_FLAG'] == 0) {
			$this->wrong("当前状态不允许手工单笔代扣！");
		}
		if(empty($info['DK_AMT']) || empty($info['BANKACCT_NO']) || empty($info['BANKACCT_NAME']) || empty($info['BANK_BID'])){
			D($this->TDkls)->updateDkls("DK_ID = '".$info['DK_ID']."'", array('RES'=>'代扣数据不完整，不能发送代扣！【ChinaPay】'));
			$this->wrong("代扣数据不完整，不能发送代扣！");
		}
		//处理证件类型
		switch($info['DK_IDNO_TYPE']){
			case 0:
				$idno_type = '01';
				break;
			case 1:
				$idno_type = '03';
				break;
			case 2:
				$idno_type = '02';
				break;
			case 3:
				$idno_type = '05';
				break;
			default:
				$idno_type = '06';				
		}		
		
		include_once(C('ChinaPay')['url_netpay']);
		$merid = buildKey(C('ChinaPay')['pri_key']);
		if(!$merid) {
			$this->wrong("导入私钥文件出错！");
		}
		//组装数据
		$order_id = substr(getmicrotime(),2);
		$resdata  = array(
			'merId'			=>	$merid,										//商户号
			'transDate'		=>	date('Ymd'),								//商户日期
			'orderNo'		=>	$order_id,									//订单号
			'transType'		=>	'0003',										//交易类型
			'openBankId'	=>	'0'.substr($info['BANK_BID'],0,3),			//开户行号
			'cardType'		=>	$info['SHOP_ACCT_FLAG'] ? $info['SHOP_ACCT_FLAG'] : 0,	//卡折标志
			'cardNo'		=>	$info['BANKACCT_NO'],						//卡号/折号
			'usrName'		=>	unicode_encode($info['BANKACCT_NAME']),		//持卡人姓名
			'certType'		=>	$idno_type,									//证件类型
			'certId'		=>	$info['DK_IDNO'],							//证件号
			'curyId'		=>	'156',										//币种
			'transAmt'		=>	$info['DK_AMT'],							//金额
			'purpose'		=>	'',											//用途
			'priv1'			=>	unicode_encode('私有域'),					//私有域
			'version'		=>	'20151207',									//版本号
			'gateId'		=>	'7008',										//网关号
			'termType'		=>	'07',										//渠道类型
			'payMode'		=>	'1',										//交易模式
		);
		//计算签名值
		$plain 	  = $resdata['merId'].$resdata['transDate'].$resdata['orderNo'].$resdata['transType'].$resdata['openBankId'].$resdata['cardType'].$resdata['cardNo'].$resdata['usrName'].$resdata['certType'].$resdata['certId'].$resdata['curyId'].$resdata['transAmt'].$resdata['priv1'].$resdata['version'].$resdata['gateId'].$resdata['termType'].$resdata['payMode'];
		$chkvalue = sign(base64_encode($plain));
		if(!$chkvalue) {
			$this->wrong("签名失败！");
		}
		$resdata['chkValue'] = $chkvalue;
		$res = httpPostForm(C('ChinaPay')['url_pay'], $resdata);
		$res = str_replace('=', '":"', $res);
		$res = str_replace('&', '","', $res);
		$res = '{"'.$res.'"}';
		$res = json_decode($res, true);
		
		if($res['responseCode'] != '00'){
			D($this->TDkls)->updateDkls("DK_ID = '".$info['DK_ID']."'", array('DK_FLAG'=> 2,'RES'=>$res['message'].'【ChinaPay】','DK_ORDER_ID'=>$order_id));			
			$this->wrong($res['message']);
		}
		
		//代扣成功，入库
		$updata = array(
			'DK_DATE'		=>	date('Ymd'),
			'DK_TIME'		=>	date('His'),
			'DK_FLAG'		=>	0,
			'RES'			=>	'手工单笔代扣成功！【ChinaPay】',
			'DK_RET_TRACE'	=>	$res['transStat'],
			'DK_ORDER_ID'	=>	$order_id,
		);
		D($this->TDkls)->updateDkls("DK_ID = '".$info['DK_ID']."'", $updata);
		
		//发短信
		if($info['MOBILE']){
			$smsdata = array(
				'dk_bankacct_no_tail'	=>	substr($resdata['cardNo'], -4, 4),
				'datetime'				=>	date('Y-m-d H:i:s', strtotime($updata['DK_DATE'].$updata['DK_TIME'])),
				'dk_yl_amt'				=>	setMoney($resdata['transAmt'], 2, 2),
			);
			$arr = setSmsmodel(6, $smsdata);
			$sls = array(
				'BRANCH_MAP_ID'		=>	$info['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=>	$info['PARTNER_MAP_ID'],
				'SMS_MODEL_TYPE'	=>	6,
				'VIP_FLAG'			=>	0,
				'VIP_ID'			=>	0,
				'VIP_CARDNO'		=>	'-',
				'SMS_RECV_MOB'		=>	$info['MOBILE'],
				'SMS_RECV_NAME'		=>	$info['SHOP_NAME'],
				'SMS_TEXT'			=>	$arr['str'],
				'SMS_STATUS'		=>	2,
				'SMS_DATE'			=>	date('Ymd'),
				'SMS_TIME'			=>	date('His'),
				'SMS_MODEL_ID'		=>	$arr['mid'],
				'SMS_MUL_BATCH'		=>	0,
				'SMS_RESP_ID'		=>	0,
			);
			D($this->TSmsls)->addSmsls($sls);
		}
		$this->right('手工单笔代扣成功！【ChinaPay】');
	}
	/*
	* 商户代扣结算 手工线下代扣
	**/
	public function dkls_offline() {	
		$id = $_REQUEST['id'];
		$home = session('HOME');
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->TDkls)->findDkls("DK_ID = '".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//过滤
		if($info['DK_FLAG'] == 0){
			$this->wrong('已经代扣，不能重复处理该操作！');
		}
		$res = D($this->TDkls)->updateDkls("DK_ID = '".$id."'", array('DK_FLAG'=> 0,'RES'=>'手工线下代扣成功！操作员：'.$home['USER_NO']));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}		
		
		//修改 trace
		//$where = "SHOP_NO='".$info['SHOP_NO']."' and SETTLE_SDATE='".date('Ymd',strtotime($info['SETTLE_DATE']))."' and PAY_TYPE='5' and TRACE_RETCODE='00' and TRACE_STATUS='0' and TRACE_REVERFLAG='0' and MARK_FLAG='3' and TRANS_AMT>0";
		//D($this->TTrace)->updateTrace($where, array('MARK_FLAG'=> 1));
		
		$this->right('代扣成功！');
	}
	/*
	* 商户代扣结算	导出
	**/
	public function dkls_export() {
		$post = array(
			'BRANCH_MAP_ID'		=>	I('BRANCH_MAP_ID'),
			'PARTNER_MAP_ID'	=>	I('PARTNER_MAP_ID'),
			'SETTLE_DATE_A'		=>	I('SETTLE_DATE_A'),
			'SETTLE_DATE_B'		=>	I('SETTLE_DATE_B'),
			'DKCO_MAP_ID'		=>	I('DKCO_MAP_ID'),
			'DK_FLAG'			=>	I('DK_FLAG'),
			'SHOP_NAME'			=>	I('SHOP_NAME'),
			'SEA_TITLE'			=>	I('SEA_TITLE'),
			'DK_ORDER_ID'		=>	I('DK_ORDER_ID'),
			'BANKACCT_NO'		=>	I('BANKACCT_NO'),
		);
		$where = "1=1";
		//归属
		if($post['BRANCH_MAP_ID']){
			$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
		}
		if($post['PARTNER_MAP_ID']){
			$pids = get_plv_childs($post['PARTNER_MAP_ID']);
			$where .= " and PARTNER_MAP_ID in (".$pids.")";
		}		
		
		//代扣日期	开始
		if($post['SETTLE_DATE_A']) {
			$where .= " and DK_DATE >= '".date('Ymd',strtotime($post['SETTLE_DATE_A']))."'";
		}
		//代扣日期	结束
		if($post['SETTLE_DATE_B']) {
			$where .= " and DK_DATE <= '".date('Ymd',strtotime($post['SETTLE_DATE_B']))."'";
		}
		//代扣公司
		if($post['DKCO_MAP_ID']) {
			$where .= " and DKCO_MAP_ID = '".$post['DKCO_MAP_ID']."'";
		}
		//代扣结果
		if($post['DK_FLAG'] != '') {
			$where .= " and DK_FLAG = '".$post['DK_FLAG']."'";
		}
		//商户名称
		if($post['SHOP_NAME']) {
			$where .= " and SHOP_NAME like '%".$post['SHOP_NAME']."%'";
		}
		//关键字
		if($post['SEA_TITLE']) {
			$where .= " and (BANK_NAME like '%".$post['SEA_TITLE']."%' or RES like '%".$post['SEA_TITLE']."%')";
		}
		//银行账号
		if($post['BANKACCT_NO']) {
			$where .= " and BANKACCT_NO = '".$post['BANKACCT_NO']."'";
		}
		//代扣订单ID
		if($post['DK_ORDER_ID']) {
			$where .= " and DK_ORDER_ID = '".$post['DK_ORDER_ID']."'";
		}
		
		
		
		//计算
		$count   = D($this->TDkls)->countDkls($where);
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
			$list = D($this->TDkls)->getDklslist($where, '*', $bRow.','.$eRow, 'DK_DATE desc,DK_TIME desc');
					
			//导出操作
			$xlsname = '商户代扣结算';
			$xlscell = array(
				array('SETTLE_DATE',	'日期'),
				array('JFB_DK_REF',		'批次'),
				array('SHOP_NAME',		'商户'),
				array('BRANCH_MAP_ID',	'归属'),
				array('BANKACCT_NO',	'银行账号'),
				array('BANK_NAME',		'开户行'),
				array('TRANS_CNT',		'交易总笔数'),
				array('DK_AMT',			'代扣总金额'),
				array('DK_FEE',			'手续费'),		
				array('DKCO_MAP_ID',	'代扣公司'),
				array('DK_FLAG',		'代扣结果'),
				array('DK_DATE',		'代扣时间'),
				array('RES',			'备注'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'SETTLE_DATE'		=>	$val['SETTLE_DATE']."\t",
					'JFB_DK_REF'		=>	$val['JFB_DK_REF'],
					'SHOP_NAME'			=>	$val['SHOP_NAME'],
					'BRANCH_MAP_ID'		=>	get_branch_name($val['BRANCH_MAP_ID'], $val['PARTNER_MAP_ID']),
					'BANKACCT_NO'		=>	$val['BANKACCT_NO'],
					'BANK_NAME'			=>	$val['BANK_NAME'],
					'TRANS_CNT'			=>	$val['TRANS_CNT'],
					'DK_AMT'			=>	setMoney($val['DK_AMT'], 2, 2),
					'DK_FEE'			=>	setMoney($val['DK_FEE'], 2, 2),
					'DKCO_MAP_ID'		=>	get_dkco_name($val['DKCO_MAP_ID']),
					'DK_FLAG'			=>	C('DK_FLAG')[$val['DK_FLAG']],
					'DK_DATE'			=>	$val['DK_DATE'].' '.$val['DK_TIME']."\t",
					'RES'				=>	$val['RES'],
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
			
			/*
			$model = M('dkls', DB_PREFIX_TRA, DB_DSN_TRA);
			$list  = $model->where("BANK_BID = 0")->group('shop_no')->select();
			//导出操作
			$xlsname = '商户代扣结算';
			$xlscell = array(
				array('SHOP_NAME',		'商户'),
				array('SHOP_NO',		'商户号'),
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'SHOP_NAME'			=>	$val['SHOP_NAME'],
					'SHOP_NO'			=>	$val['SHOP_NO'],
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;*/
		}
		
		$this->display('Public/export');
	}
	/*
	* 商户代扣结算	钱宝数据
	**/
	public function dkls_qblist() {
		$where = "1=1";
		//分页
		$count = D($this->TQbls)->countQbls($where);
		$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
		$list  = D($this->TQbls)->getQblslist($where, '*', $p->firstRow.','.$p->listRows);
		
		//分页参数
		$this->assign ( 'totalCount', 	$count );
		$this->assign ( 'numPerPage', 	$p->listRows );
		$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);		
		$this->assign ( 'list', 		$list );
		$this->display();
	}
	
	/*
	* 通道平台收益结算
	**/
	public function pibill() {
		$post = I('post');
		if($post['submit'] == "pibill"){
			$where = "1=1";
			//类别
			if($post['SOURCE_TYPE'] != '') {
				$where .= " and SOURCE_TYPE = '".$post['SOURCE_TYPE']."'";
			}
			//通道名称
			if($post['PI_MAP_NAME']){
				$where .= " and PI_MAP_NAME like '%".$post['PI_MAP_NAME']."%'";				
			}
			//交易月份
			if($post['SETTLE_DATE']) {
				$where .= " and SETTLE_DATE = '".$post['SETTLE_DATE']."'";
			}
			//分页
			$count = D($this->TPibill)->countPibill($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TPibill)->getPibilllist($where, '*', $p->firstRow.','.$p->listRows);
			
			//统计数据
			$field = 'sum(JFB_FEE_AMT) as JFB_FEE_AMT_SUM,sum(PLAT_FEE_AMT) as PLAT_FEE_AMT_SUM,sum(CON_DIV_FEE_AMT) as CON_DIV_FEE_AMT_SUM,sum(PI_AMT) as PI_AMT_SUM';
			$total = D($this->TPibill)->findPibill($where, $field);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
			//$this->assign ( 'total', 		$total );
			//根据时间显示代扣统计内容
			if($post['SETTLE_DATE']) {
				$dk_text = "<span>　入账金额汇总 ".setMoney($total['JFB_FEE_AMT_SUM'],2,2)." 元</span><span>　平台分润金额汇总 ".setMoney($total['PLAT_FEE_AMT_SUM'],2,2)." 元</span><span>　消费者分期金额汇总 ".setMoney($total['CON_DIV_FEE_AMT_SUM'],2,2)." 元</span><span>　养老金金额汇总 ".setMoney($total['PI_AMT_SUM'],2,2)." 元</span>";
				$this->assign ('dk_text', $dk_text);
			}
			
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}

		$this->assign('p_flag', C('SOURCE_TYPE'));		//通道类型
		//组装12个月数据
		$date_arr = array();
		$year = date('Y');
		for ($i=1; $i <= 12; $i++) {
			$month = strlen($i) == 1 ? '0'.$i : $i;
			$date_arr[$year.$month] = $year.'-'.$month;
		}
		$this->assign('date_arr', 		$date_arr);		//月份
		//组装12个月数据
		\Cookie::set ('_currentUrl_', 	__SELF__);
		$this->display();
	}



	/**
	 * [dzupload 对账文件上传]
	 * @return [type] [description]
	 */
	public function dzupload(){
		// $id = $_REQUEST['id'];
		$post = I('post');

		if (IS_POST){
            if($post['channel']==2) $channelno = '3101080001x';
            $savePath = C('Uploads').'ttrabak1/'.date('Y').'/';//上传目录 
            
            Mk_Folder($savePath);//设置目录可写

            $upload = new \Think\Upload();
            $upload->saveName = 'DZ'.$channelno.date(Ymd);
            $upload->maxSize = 2000000;//上传附件大小,1M=1000000
            $upload->allowExts = array('xls');//上传附件格式
            $upload->rootPath = $savePath;//上传目录,相对于单入口文件
            $upload->autoSub =   false; 
            $upload->replace = true;       
            
            if($info  =  $upload->upload()){
                
                $file_name=$info[0]['savename'];//获取上传后的新文件名
                echo json_encode(array(
                    'statusCode'=>'200',
                    'message'=>'文件上传成功！',
                    'navTabId'=>'Settle',
                    'callbackType'=>'',
                    'forwardUrl'=>''
                ));exit;
            }else{
                echo json_encode(array(
                    'statusCode'=>'300',
                    'message'=>'文件上传失败: '.$upload->getError(),
                    'navTabId'=>'Settle',
                    'callbackType'=>'',
                    'forwardUrl'=>''
                ));exit;
            }
        }

		$this->display();
	}
}?>