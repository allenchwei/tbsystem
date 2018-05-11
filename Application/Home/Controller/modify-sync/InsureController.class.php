<?php
namespace Home\Controller;
use Think\Controller;
import('Vendor.Cookie.Cookie');
import('Vendor.Phpdes.phpdes');
// +----------------------------------------------------------------------
// | @gzy  投保管理
// +----------------------------------------------------------------------
class InsureController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->TTbls 	 = 'TTbls';
		$this->GVip  	 = 'GVip';
		$this->TTbbill   = 'TTbbill';
		$this->MSecurity = 'MSecurity';
		$this->GVip 	 = 'GVip';
		$this->MExcel 	 = 'MExcel';
		$this->MCproduct = 'MCproduct';
	}
	
	
	/*
	* 预免卡意外险明细管理
	**/
	public function fcarddetail() {
		$post = I('post');
		if($post['submit'] == "fcarddetail"){
			$where = "SECURITY_TYPE=1 and VIP_FLAG=3 and TB_FLAG!=9";
			$soplv = filter_data('soplv');	//列表查询
			//分公司
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
			//开始时间
			if ($post['SYSTEM_DATE_A']) {
				$where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			}
			//结束时间
			if ($post['SYSTEM_DATE_B']) {
				$where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			}
			//保险公司
			if ($post['SECURITY_MAP_ID']) {
				$where .= " and SECURITY_MAP_ID = '".$post['SECURITY_MAP_ID']."'";
			}
			//移除标志
			if ($post['TB_DEL_FLAG']!='') {
				$where .= " and TB_DEL_FLAG = '".$post['TB_DEL_FLAG']."'";
			}
			//投保方式
			if ($post['ONLINE_FLAG'] != '') {
				$where .= " and ONLINE_FLAG = '".$post['ONLINE_FLAG']."'";
			}
			//会员手机
			if ($post['VIP_MOBILE']) {
				$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
			}
			//会员卡号
			if ($post['VIP_CARDNO']) {
				$where .= " and VIP_CARDNO = '".$post['VIP_CARDNO']."'";
			}
			//会员身份证号
			if ($post['VIP_IDNO']) {
				$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'";
			}
			//投保单号
			if ($post['TB_NO']) {
				$where .= " and TB_NO = '".$post['TB_NO']."'";
			}
			//状态
			if($post['TB_FLAG'] != '' && $post['TB_FLAG1'] != '') {
				$where .= " and (TB_FLAG = '".$post['TB_FLAG']."' or TB_FLAG = '".$post['TB_FLAG1']."')";
			}else if($post['TB_FLAG'] != ''){
				$where .= " and TB_FLAG = '".$post['TB_FLAG']."'";
			}else if($post['TB_FLAG1'] != ''){
				$where .= " and TB_FLAG = '".$post['TB_FLAG1']."'";
			}
			//批次号
			if($post['JFB_SECU_REF']) {
				$where .= " and JFB_SECU_REF = '".$post['JFB_SECU_REF']."'";
			}

			//过滤失败的重复数据
			/*if($post['TB_FLAG1'] == '1'){
				$Model = M('tbls', DB_PREFIX_TRA, DB_DSN_TRA); // 实例化一个model对象 没有对应任何数据表
				//分页
				$count_sql = "select count(a.TB_ID) as total from (select * from  t_tbls where ".$where." group by VIP_ID) a";
				$res = $Model->query($count_sql);
				$count = $res[0]['total'];
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$limit = ' limit '.$p->firstRow.",".$p->listRows;
				$sql = "select * from (select * from  t_tbls order by TB_ID desc) a where ".$where." group by VIP_ID order by TB_ID desc".$limit;
				$list = $Model->query($sql);
			}else{
				//分页
				$count = D($this->TTbls)->countTbls($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->TTbls)->getTblslist($where, '*', $p->firstRow.','.$p->listRows);
			}*/

			//分页
			$count = D($this->TTbls)->countTbls($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TTbls)->getTblslist($where, '*', $p->firstRow.','.$p->listRows);

			//统计投保参数(仅成功和失败统计)
			if($post['TB_FLAG1'] == '0' or $post['TB_FLAG1'] == '1' or $post['TB_FLAG'] == '2'){
				$total = D($this->TTbls)->findTbls($where, 'sum(TB_AMT) as money');
				$total_str = '<span>　共计 '.setMoney($total['money'], '2', '2').' 元</span>';
				$this->assign ( 'total_str', $total_str );
			}
			
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		//移除标志
		$this->assign('TB_DEL_FLAG',C('TB_DEL_FLAG'));
		//投保方式(线上,线下)
		$this->assign('ONLINE_FLAG',C('ONLINE_FLAG'));
		//投保状态
		$this->assign('TB_FLAG',C('TB_FLAG'));
		//保险公司
		$res = D($this->MSecurity)->getSecuritylist('','SECURITY_MAP_ID,SECURITY_NAME');
		$this->assign('sec_sel',$res);
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display();
	}	
	/*
	* 预免卡意外险明细管理	详情
	**/
	public function fcarddetail_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$where = 'TB_ID = "'.$id.'"';
		$info  = D($this->TTbls)->findTbls($where, '*', $p->firstRow.','.$p->listRows);
		//会员信息
		$vip_info = D($this->GVip)->findVip('VIP_ID = '.$info['VIP_ID']);
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}	
		$this->assign ('info', $info);
		$this->assign ('vip_info', $vip_info);
		$this->display();
	}
	/*
	* 预免卡意外险明细管理	移除
	**/
	public function fcarddetail_del() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('TB_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('TB_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$tblsstatus = D($this->TTbls)->getTblslist($where,'TB_FLAG,TB_DEL_FLAG');
		foreach ($tblsstatus as $key => $value) {
			if (($value['TB_FLAG']!='2') || ($value['TB_DEL_FLAG']!='0')) {
				$this->wrong('此操作仅限投保状态为待提交并且移除标志为参保时执行');
			}
		}
		$res = D($this->TTbls)->updateTbls($where, array('TB_DEL_FLAG' => 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	
	/*
	* 预免卡意外险明细管理	添加
	**/
	public function fcarddetail_add() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('TB_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('TB_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$tblsstatus = D($this->TTbls)->getTblslist($where,'TB_FLAG,TB_DEL_FLAG');
		foreach ($tblsstatus as $key => $value) {
			if ($value['TB_DEL_FLAG']!='1') {
				$this->wrong('此操作仅限移除标志为移除时执行');
			}
		}
		$res = D($this->TTbls)->updateTbls($where, array('TB_DEL_FLAG' => 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 预免卡险明细管理	初始化
	**/
	public function fcarddetail_init() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('TB_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('TB_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$tblsstatus = D($this->TTbls)->getTblslist($where,'TB_FLAG,TB_DEL_FLAG');
		foreach ($tblsstatus as $key => $value) {
			if (($value['TB_FLAG']!='4') || ($value['TB_DEL_FLAG']!='0')) {
				$this->wrong('此操作仅限投保状态为待确认并且移除标志为参保时执行');
			}
		}
		$res = D($this->TTbls)->updateTbls($where, array('TB_FLAG' => 2));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}

	/*
	* 预免卡险明细管理	修改会员资料并同步
	**/
	public function vipdata_edit() {
		$post = I('post');
		if($post['submit'] == "vipdata_edit") {
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
				'VIP_EMAIL'			=>	$post['VIP_EMAIL'] ? $post['VIP_EMAIL'] : 'sshmjfb@163.com',
				'VIP_BIRTHDAY'		=>	date('Ymd',strtotime($post['VIP_BIRTHDAY'])),
				'UPDATE_TIME'		=>	date('YmdHis'),
				'ID_PHOTO'			=>	$ID_PHOTO,
			);

			$findvip3 = D($this->GVip)->findNewsVip("VIP_ID = '".$post['VIP_ID']."'");
			if (strlen($findvip3['CARD_NO']) < 16) {
				$resdata['CARD_NO'] = '0'.$post['VIP_ID'];
				$post['CARD_NO']    = '0'.$post['VIP_ID'];
			}else{
				$post['CARD_NO']    = $findvip3['CARD_NO'];
			}
			$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
			$m->startTrans();	//启用事务
			$res = D($this->GVip)->updateVip("VIP_ID = '".$post['VIP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			
			//同步修改会员数据
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
/*
			$resjson = httpPostForm($url,$data);
			Add_LOG(CONTROLLER_NAME, $resjson);
			$result = json_decode($resjson);
			if ($result->code != '0') {
				$m->rollback();	//回滚
				$this->wrong('会员数据同步修改失败');
			}
*/
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

		$tb_data = D($this->TTbls)->findTbls('TB_ID = '.$id, 'VIP_ID');
		$info = D($this->GVip)->findNewsVip("VIP_ID = '".$tb_data['VIP_ID']."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['VIP_BIRTHDAY'] = $info['VIP_BIRTHDAY'] ? date('Y-m-d', strtotime($info['VIP_BIRTHDAY'])) : '';
		$info['ID_PHOTO'] = explode(',', $info['ID_PHOTO']);
		$this->assign('vip_idnotype', 		C('VIP_IDNOTYPE'));		//证件类型
		$this->assign('vip_sex', 			C('VIP_SEX'));			//会员性别
		$this->assign('info', 				$info);
		$this->display('Member/vinfo_edit');
	}
	/*
	* 预免卡意外险明细管理	提交投保
	**/
	public function fcarddetail_submit() {
		$home = session('HOME');
		//统计同一批次号的
		$where = 'VIP_FLAG = 3 and SECURITY_TYPE = 1 and TB_FLAG = 2 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
		//数据隔离
		if(!filter_auth($home['BRANCH_MAP_ID'])){
			$pids = get_plv_childs($home['PARTNER_MAP_ID'],1);
			$where.= ' and PARTNER_MAP_ID in ('.$pids.')';
		}
		$res = D($this->TTbls)->getTblsgroup($where);
		if (empty($res)) {
			$this->wrong('当前没有可提交投保数据');//(提交投保数据必须是预免卡,意外险,待提交,参保状态)
		}
		$tbbill_data = array(
			'BRANCH_MAP_ID'		=> $home['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'	=> $home['PARTNER_MAP_ID'],
			'SECURITY_MAP_ID'	=> $res['SECURITY_MAP_ID'],
			'SECURITY_TYPE'		=> $res['SECURITY_TYPE'],
			'ONLINE_FLAG'		=> $res['ONLINE_FLAG'],
			'JFB_SECU_REF'		=> $res['JFB_SECU_REF'],
			'TB_TIME'			=> date('YmdHis'),
			'TB_FLAG'			=> 1,	//投保结果
			'VIP_FLAG'			=> 3,	//意外卡
			'TB_STATUS'			=> 4,	//状态标识(0：已投保 1：见审批表)
			'TB_AMT'			=> $res['summoney'],
			'TB_CNT'			=> $res['num'],
			'TB_AMT_SUCC'		=> 0,
			'TB_CNT_SUCC'		=> 0
		);

		//入库到汇总表
		$tbbill_res = D($this->TTbbill)->addTbbill($tbbill_data);
		//更新所有批次号对应该的状态值
		if ($tbbill_res['state'] != 0) {
			$this->wrong('提交失败');
		}
		//更新所有批次号对应该的状态值及批次
		$ref = '10';
		D($this->TTbls)->updateTbls($where.' and JFB_SECU_REF = "'.$res['JFB_SECU_REF'].'"',array('TB_FLAG'=>4,'JFB_SECU_REF' =>$ref.$tbbill_res['TBBILL_ID']));
		D($this->TTbbill)->updateTbbill('TBBILL_ID = "'.$tbbill_res['TBBILL_ID'].'"',array('JFB_SECU_REF' =>$ref.$tbbill_res['TBBILL_ID']));
		
		$this->right($ref.$tbbill_res['TBBILL_ID'].'批次，共计成功提交：'.$res['num'].'个会员，'.setMoney($res['summoney'],2,2).'元', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	
	/*
	* 预免卡意外险明细管理	撤销投保
	**/
	public function fcarddetail_cancel() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('TB_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('TB_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$tblsstatus = D($this->TTbls)->getTblslist($where,'TB_FLAG,TB_DEL_FLAG');
		foreach ($tblsstatus as $key => $value) {
			if (($value['TB_FLAG']!='0') || ($value['TB_DEL_FLAG']!='0')) {
				$this->wrong('此操作仅限投保状态为成功的时候才允许撤销操作');
			}
		}
		$res = D($this->TTbls)->updateTbls($where, array('TB_FLAG' => 5));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}

	/*
	* 预免卡意外险明细管理 导出
	**/
	public function fcarddetail_export() {
		$post  = array(
			'bid'				=>	I('bid'),
			'pid'				=>	I('pid'),
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A') ? I('SYSTEM_DATE_A') : '',
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B') ? I('SYSTEM_DATE_B') : '',
			'SECURITY_MAP_ID'	=>	I('SECURITY_MAP_ID'),
			'TB_DEL_FLAG'		=>	I('TB_DEL_FLAG'),
			'ONLINE_FLAG'		=>	I('ONLINE_FLAG'),
			'VIP_MOBILE'		=>	I('VIP_MOBILE'),
			'VIP_CARDNO'		=>	I('VIP_CARDNO'),
			'VIP_IDNO'			=>	I('VIP_IDNO'),
			'TB_NO'				=>	I('TB_NO'),
			'TB_FLAG'			=>	I('TB_FLAG'),
			'TB_FLAG1'			=>	I('TB_FLAG1'),
			'JFB_SECU_REF'		=>	I('JFB_SECU_REF')
		);
		$where = "SECURITY_TYPE=1 and VIP_FLAG=3 and TB_FLAG!=9";
		//分公司
		if($post['bid'] != '') {
			$where .= " and BRANCH_MAP_ID = '".$post['bid']."'";
		}
		//合作伙伴
		if($post['pid'] != '') {
			$pids = get_plv_childs($post['pid'],1);
			$where .= " and PARTNER_MAP_ID in(".$pids.")";
		}
		//开始时间
		if ($post['SYSTEM_DATE_A']) {
			$where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
		}
		//结束时间
		if ($post['SYSTEM_DATE_B']) {
			$where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
		}
		//保险公司
		if ($post['SECURITY_MAP_ID']) {
			$where .= " and SECURITY_MAP_ID = '".$post['SECURITY_MAP_ID']."'";
		}
		//移除标志
		if ($post['TB_DEL_FLAG']!='') {
			$where .= " and TB_DEL_FLAG = '".$post['TB_DEL_FLAG']."'";
		}
		//投保方式
		if ($post['ONLINE_FLAG'] != '') {
			$where .= " and ONLINE_FLAG = '".$post['ONLINE_FLAG']."'";
		}
		//会员手机
		if ($post['VIP_MOBILE']) {
			$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
		}
		//会员卡号
		if ($post['VIP_CARDNO']) {
			$where .= " and VIP_CARDNO = '".$post['VIP_CARDNO']."'";
		}
		//会员身份证号
		if ($post['VIP_IDNO']) {
			$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'";
		}
		//投保单号
		if ($post['TB_NO']) {
			$where .= " and TB_NO = '".$post['TB_NO']."'";
		}
		//状态
		if($post['TB_FLAG'] != '' && $post['TB_FLAG1'] != '') {
			$where .= " and (TB_FLAG = '".$post['TB_FLAG']."' or TB_FLAG = '".$post['TB_FLAG1']."')";
		}else if($post['TB_FLAG'] != ''){
			$where .= " and TB_FLAG = '".$post['TB_FLAG']."'";
		}else if($post['TB_FLAG1'] != ''){
			$where .= " and TB_FLAG = '".$post['TB_FLAG1']."'";
		}
		//批次号
		if($post['JFB_SECU_REF']) {
			$where .= " and JFB_SECU_REF = '".$post['JFB_SECU_REF']."'";
		}

		//过滤失败的重复数据[计算]
		/*if($post['TB_FLAG1'] == '1'){
			$Model = M('tbls', DB_PREFIX_TRA, DB_DSN_TRA); // 实例化一个model对象 没有对应任何数据表
			//分页
			$count_sql = "select count(a.TB_ID) as total from (select * from  t_tbls where ".$where." group by VIP_ID) a";
			$res = $Model->query($count_sql);
			$count = $res[0]['total'];
		}else{
			//分页
			$count = D($this->TTbls)->countTbls($where);
		}*/
		//分页
		$count = D($this->TTbls)->countTbls($where);
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
			//过滤失败的重复数据[列表]
			/*if($post['TB_FLAG1'] == '1'){
				$limit = ' limit '.$bRow.','.$eRow;
				$sql = "select * from (select * from  t_tbls order by TB_ID desc) a where ".$where." group by VIP_ID order by TB_ID desc".$limit;
				$list = $Model->query($sql);
			}else{
				$list  = D($this->TTbls)->getTblslist($where, '*', $bRow.','.$eRow);
			}*/
			$list  = D($this->TTbls)->getTblslist($where, '*', $bRow.','.$eRow);
			//导出操作
			$xlsname = '预免卡意外险明细管理';
			$xlscell = array(
				array('TB_ID',				'投保流水号'),
				array('VIP_NAME',			'会员名称'),
				array('VIP_MOBILE',			'手机号'),
				array('VIP_IDNO',			'身份证号'),
				array('VIP_CARDNO',			'卡号'),
				array('TB_AMT',				'投保金额'),
				array('VIP_EMAIL',			'邮箱'),
				array('TB_FLAG',			'投保结果'),
				array('SECURITY_MAP_ID',	'保险公司'),
				array('TB_NO',				'保单号'),
				array('ONLINE_FLAG',		'投保方式'),		
				array('TB_TIME',			'投保日期'),		
				array('GUISHU_3',			'归属服务中心'),
				array('GUISHU',				'归属'),
				array('TB_DESC',			'备注')
			);		
			$xlsarray = array();
			foreach($list as $val){
				$guishu = get_level_name($val['PARTNER_MAP_ID'], $val['BRANCH_MAP_ID']);
				$gejizuzhi = explode('-', $guishu);
				$xlsarray[] = array(
					'TB_ID'			=>	$val['TB_ID'],
					'VIP_NAME'		=>	$val['VIP_NAME'],
					'VIP_MOBILE'	=>	$val['VIP_MOBILE']."\t",
					'VIP_IDNO'		=>	$val['VIP_IDNO']."\t",
					'VIP_CARDNO'	=>	$val['VIP_CARDNO']."\t",
					'TB_AMT'		=>	setMoney($val['TB_AMT'], '2', '2'),
					'VIP_EMAIL'		=>	$val['VIP_EMAIL'],
					'TB_FLAG'		=>	C('TB_FLAG')[$val['TB_FLAG']],
					'SECURITY_NAME'	=>	get_security_name($val['SECURITY_MAP_ID']),
					'TB_NO'			=>	$val['TB_NO']."\t",
					'ONLINE_FLAG'	=>	C('ONLINE_FLAG')[$val['ONLINE_FLAG']],
					'TB_TIME'		=>	$val['TB_TIME']."\t",
					'GUISHU_3'		=>	$gejizuzhi[2],
					'GUISHU'		=>	$guishu,
					'TB_DESC'		=>	$val['TB_DESC']
				);	
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		$this->display('Public/export');
	}

	
	
	

	/*
	* 预免卡意外险投保管理
	**/
	public function fcardinsure() {
		$post = I('post');
		if($post['submit'] == "fcardinsure"){
			$where = "SECURITY_TYPE=1 and VIP_FLAG = 3";
			$soplv = filter_data('soplv');	//列表查询
			//分公司
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
			//保险公司
			if ($post['SECURITY_MAP_ID']) {
				$where .= " and SECURITY_MAP_ID = '".$post['SECURITY_MAP_ID']."'";
			}
			//状态
			if($post['TB_STATUS'] != '') {
				$where .= " and TB_STATUS = '".$post['TB_STATUS']."'";
			}
			//批次号
			if($post['JFB_SECU_REF']) {
				$where .= " and JFB_SECU_REF = '".$post['JFB_SECU_REF']."'";
			}
			//开始时间
			if ($post['SYSTEM_DATE_A']) {
				$where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			}
			//结束时间
			if ($post['SYSTEM_DATE_B']) {
				$where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			}
			//分页
			$count = D($this->TTbbill)->countTbbill($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TTbbill)->getTbbilllist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
		//投保方式(线上,线下)
		$this->assign('ONLINE_FLAG',C('ONLINE_FLAG'));
		//投保状态
		$this->assign('TB_STATUS',C('TB_STATUS_TBBILL'));
		//保险公司
		$res = D($this->MSecurity)->getSecuritylist('','SECURITY_MAP_ID,SECURITY_NAME');
		$this->assign('sec_sel',$res);
		\Cookie::set ('_currentUrl_', 	__SELF__);	
		$this->display();
	}
	
	/*
	* 预免卡意外险投保管理	详情
	**/
	public function fcardinsure_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$where = 'TBBILL_ID = "'.$id.'"';
		$info  = D($this->TTbbill)->findTbbill($where);
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}	
		$this->assign ('info', $info);
		$this->display();
	}

	/*
	* 预免卡意外险投保管理	投保确认
	**/
	public function fcardinsure_submit() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$home = session("HOME");
		$where = 'TBBILL_ID = "'.$id.'"';
		$info  = D($this->TTbbill)->findTbbill($where, 'PARTNER_MAP_ID,JFB_SECU_REF,TB_STATUS' );
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if ($info['TB_STATUS'] !=4) {
			$this->wrong("当前状态无法完成此操作！");
		}

		//得到所有当前批次号对应的数据 
		$where1 = 'JFB_SECU_REF = "'.$info['JFB_SECU_REF'].'" and VIP_FLAG = 3 and SECURITY_TYPE = 1 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
		//数据隔离
		if(!filter_auth($home['BRANCH_MAP_ID'])){
			$pids = get_plv_childs($info['PARTNER_MAP_ID'],1);
			$where1.= ' and PARTNER_MAP_ID in ('.$pids.')';
		}
		$res = D($this->TTbls)->getTblslist($where1.' and TB_FLAG = 4');
		foreach ($res as $key => $value) {
			//获得会员数据
			$vipdata = D($this->GVip)->findVip('VIP_ID = "'.$value['VIP_ID'].'"', $field='*');
			$vipdata['SEX_NAME'] = $vipdata['VIP_SEX'] ? '男' : '女';
			$vipdata['VIP_SEX'] = $vipdata['VIP_SEX'] ? 1 : 2;
			//查看该保险号是否已经投保
			$is_insure = D($this->TTbls)->findTbls("VIP_ID = ".$value['VIP_ID']." and SECURITY_TYPE = 1 and TB_FLAG = 0 and ONLINE_FLAG = 0", $field='*');
			if($is_insure['VIP_ID']){
				$TB_FLAG = array('TB_FLAG'=>6,'TB_DESC'=>'已经投过');		//如果不成功更新为失败
				//更新保单号对应该的状态值(投保确认的数据必须是预免卡,意外险,待确认,参保, 在线投保状态)
				$where = 'TB_ID = '.$value['TB_ID'].' and VIP_FLAG = 3 and SECURITY_TYPE = 1 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
				$t_res = D($this->TTbls)->updateTbls($where, $TB_FLAG);
			}else{
				$this->assign('vipdata',$vipdata);
				$this->assign('data',$value);
				//发送XML数据(加密发送XML报文)
				$xmldata = $this->fetch('accident_insurance');		//意外险
				//发送并接收返回值
				$url = C('INSURE_URL1');	//意外险投保链接
				$xml_arr = $this->post_xml($xmldata,$url);
				if ($xml_arr) {
					$backdata = array(
						'TransRefGUID' 		=> $xml_arr['TXLifeResponse']['TransRefGUID'],
						'ResultCode' 		=> $xml_arr['TXLifeResponse']['TransResult']['ResultCode'],
						'ResultInfoDesc'	=> $xml_arr['TXLifeResponse']['TransResult']['ResultInfo']['ResultInfoDesc'],
						'PolNumber' 		=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['PolNumber'],
						'HOAppFormNumber' 	=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['ApplicationInfo']['HOAppFormNumber'],
						'BankCode' 			=> $xml_arr['TXLifeResponse']['OLifEExtension']['BankCode']
					);
					if ($backdata['ResultCode'] == 'Success') {
						//如果成功更新数据状态
						$TB_FLAG = array(
							'TB_NO'		=>	$backdata['PolNumber'] ? $backdata['PolNumber'] : $backdata['HOAppFormNumber'],
							'TB_FLAG'	=>	0,
							'TB_TIME'	=>	date('YmdHis'),
							'TB_DESC'	=>	$backdata['ResultInfoDesc']
						);
						//修改vip表
						if(strlen($vipdata['RES']) == '80'){
							$yw_no = setStrzero($TB_FLAG['TB_NO'], 40, ' ','r');	//意外险单号
							$yl_no = substr($vipdata['RES'],40,40);	//养老险单号
							$vip_res = $yw_no.$yl_no;
						}else{
							$vip_res = setStrzero($TB_FLAG['TB_NO'], 80, ' ','r');
						}
						D($this->GVip)->updateVip('VIP_ID = '.$value['VIP_ID'], array('RES' => $vip_res));
					}else{
						//如果不成功更新为失败
						$TB_FLAG = array(
							'TB_FLAG'	=>	1,
							'TB_DESC'	=>	$backdata['ResultInfoDesc']
						);
					}
					//更新保单号对应该的状态值(投保确认的数据必须是预免卡,意外险,待确认,参保, 在线投保状态)
					$where = 'TB_ID = '.$value['TB_ID'].' and VIP_FLAG = 3 and SECURITY_TYPE = 1 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
					$t_res = D($this->TTbls)->updateTbls($where, $TB_FLAG);
				}	
			}
			//更新现有的，投保数据（将所有意外险投保失败的数据，改为过期状态）
			$upwhere =  'VIP_ID = "'.$value['VIP_ID'].'" and TB_ID < '.$value['TB_ID'].' and VIP_FLAG = 3 and SECURITY_TYPE = 1 and TB_FLAG = 1 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
			D($this->TTbls)->updateTbls($upwhere, array('TB_FLAG' => 6));
		}
		$tb_money_succ = D($this->TTbls)->sumTbls($where1.' and TB_FLAG = 0', 'TB_AMT');			//投保金额
		$tb_num_succ = D($this->TTbls)->countTbls($where1.' and TB_FLAG = 0', 'TB_CNT');			//投保人数

		//更新所有批次号对应该的状态值
		$up_tbbill = array(
			'TB_FLAG'		=> 0, 				//投保结果(0：正常 1：未确认 2：失败)
			'TB_STATUS'		=> 0, 				//状态标识(0：已投保 1：见审批表)
			'TB_AMT_SUCC'	=> $tb_money_succ, 	//成功投保金额
			'TB_CNT_SUCC'	=> $tb_num_succ		//成功投保人数
		);
		$g_res = D($this->TTbbill)->updateTbbill('TBBILL_ID = "'.$id.'"',$up_tbbill);
		if (!$g_res) {
			$this->wrong('提交失败');
		}
		$this->right('提交成功', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		//记得要加事物
	}

	/*
	* 投保测试数据
	**/
	/*public function test_backdata() {
		//解析返回参数
		return '854e34074ba42831b4f58300cd43c20e5098a987a04aa6439a3b658e7239d2775db76205b9e8b8a63f1211d974049c09c40ccfbeb4ffe00f64bd1b61e014c8930ef5e294d2cb289c9cce963da85039625ae15c65521fb74c2f3afffc8a46c0f4d6cbc02318dcc4fb83d4ecc1b331ab93c7cd882046a07e0a6503dc0aeff3ebc62e7839e0728162689cce963da85039621f682e4f2bd36999b8f9f922066a3dab9cce963da8503962da58c2c195a9f15b2c13269fdf77196db6687019ef571758a5575d5698941989e5542c7abbef7155dbd1041b859f5b2ea295cb869762425e372d060cc69778978f6cc3fc328580fa0839784dc66d72c4e5542c7abbef7155dbd1041b859f5b2ec26ba730355b640552053f87df582713067338aa884a9061a3bce1dae14e52ce0e2e4f25d66f4fd1a1fbb9e5bda28e9d0e2e4f25d66f4fd194c4b510064df169de8865abf337ec39f74a9f2df72bb7280b65314860d544c3a77693e79a27dda2a3ac4c669d01ae631afd40398a11195fa77693e79a27dda2a77693e79a27dda21c9210dc9dbca2fb9c1c526b6e7131f38c03a7d9991311c258b28d83d8754ed3e1b49d43a640a61ea77693e79a27dda2a77693e79a27dda22aeec1d1fbbebbc4686bf759be2459d97b97101adb6092b3a77693e79a27dda2a77693e79a27dda2185dc6b8a8e6440036c4a4143572c2b6e990c8d471f6852446d2b71fd2dbd69a35405ad0f818986ed6dca709d65869f3fade9975cc0460c3a77693e79a27dda23dc6b2ee2c6bc6b15ef9e3711ba78d477560ebb6217738af0e2e4f25d66f4fd1a77693e79a27dda2964e305111db5c5edb9955d74279ee2fe389cff9eb009aee2519f27954b1ff86d60978009099ec23a77693e79a27dda2c16ff786130c101f1afd40398a11195fa77693e79a27dda23e63c1b89f28c8c58765406b5b6b633c3dc6b2ee2c6bc6b17d169bc51223ff432d8a3c268e9131ef46815e038a876232b210725bac886f48a77693e79a27dda29818d00509744169934325b208f17bd47aef3cea44630991822f57ff0c457a2de925bb43437faaf2916fc99adde00dcb2d8a3c268e9131ef38b4bfade4dfb7157b97101adb6092b3a77693e79a27dda28eef5a4cbc6bc3518eb5c0518f35825a33910b62652e8f9b6973f8112555930845b66d022a9f69a3c30d485384412c9afa060a8e11e6fabd7ab0e21e1383a2fcfade9975cc0460c33dc6b2ee2c6bc6b138b4bfade4dfb7157b97101adb6092b3bf8ff1c63fb3cf154e4da6bf2c334e9483841184a0cef7800cede97d9b333d4c70951acd0b4ca8dc7f64d3da790eb6c44c047476af33d289787e0e8c334f42922d8a3c268e9131ef012c7f457a00c6797c6106c48bc59ccf012c7f457a00c679fade9975cc0460c3c7dc2ffb04c2c83770951acd0b4ca8dc9e64321622af4e6a8cd9a0ac17a5f502e917420359ac175d35cfd0850401f72f70fcd0c1e99dafa2';
	}*/
	
	
	
	/*
	* 付费卡意外险明细管理
	**/
	public function pcarddetail() {
		$post = I('post');
		if($post['submit'] == "pcarddetail"){
			$where = "SECURITY_TYPE=1 and VIP_FLAG=2 and TB_FLAG!=9";
			$soplv = filter_data('soplv');	//列表查询
			//分公司
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
			//开始时间
			if ($post['SYSTEM_DATE_A']) {
				$where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 000000'))."'";
			}
			//结束时间
			if ($post['SYSTEM_DATE_B']) {
				$where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 235959'))."'";
			}
			//保险公司
			if ($post['SECURITY_MAP_ID']) {
				$where .= " and SECURITY_MAP_ID = '".$post['SECURITY_MAP_ID']."'";
			}
			//移除标志
			if ($post['TB_DEL_FLAG'] != '') {
				$where .= " and TB_DEL_FLAG = '".$post['TB_DEL_FLAG']."'";
			}
			//投保方式
			if ($post['ONLINE_FLAG'] != '') {
				$where .= " and ONLINE_FLAG = '".$post['ONLINE_FLAG']."'";
			}
			//会员手机
			if ($post['VIP_MOBILE']) {
				$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
			}
			//会员卡号
			if ($post['VIP_CARDNO']) {
				$where .= " and VIP_CARDNO = '".$post['VIP_CARDNO']."'";
			}
			//会员身份证号
			if ($post['VIP_IDNO']) {
				$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'";
			}
			//投保单号
			if ($post['TB_NO']) {
				$where .= " and TB_NO = '".$post['TB_NO']."'";
			}
			//状态
			if($post['TB_FLAG'] != '' && $post['TB_FLAG1'] != '') {
				$where .= " and (TB_FLAG = '".$post['TB_FLAG']."' or TB_FLAG = '".$post['TB_FLAG1']."')";
			}else if($post['TB_FLAG'] != ''){
				$where .= " and TB_FLAG = '".$post['TB_FLAG']."'";
			}else if($post['TB_FLAG1'] != ''){
				$where .= " and TB_FLAG = '".$post['TB_FLAG1']."'";
			}
			//批次号
			if($post['JFB_SECU_REF']) {
				$where .= " and JFB_SECU_REF = '".$post['JFB_SECU_REF']."'";
			}
			//过滤失败的重复数据
			/*if($post['TB_FLAG1'] == '1'){
				$Model = M('tbls', DB_PREFIX_TRA, DB_DSN_TRA); // 实例化一个model对象 没有对应任何数据表
				//分页
				$count_sql = "select count(a.TB_ID) as total from (select * from  t_tbls where ".$where." group by VIP_ID) a";
				$res = $Model->query($count_sql);
				$count = $res[0]['total'];
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$limit = ' limit '.$p->firstRow.",".$p->listRows;
				$sql = "select * from (select * from t_tbls order by TB_ID desc) a where ".$where." group by VIP_ID order by TB_ID desc".$limit;
				$list = $Model->query($sql);
			}else{
				//分页
				$count = D($this->TTbls)->countTbls($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->TTbls)->getTblslist($where, '*', $p->firstRow.','.$p->listRows);
			}*/

			$count = D($this->TTbls)->countTbls($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TTbls)->getTblslist($where, '*', $p->firstRow.','.$p->listRows);
			//统计投保参数(仅成功和失败统计)
			if($post['TB_FLAG1'] == '0' or $post['TB_FLAG1'] == '1' or $post['TB_FLAG'] == '2'){
				$total = D($this->TTbls)->findTbls($where, 'sum(TB_AMT) as money');
				$total_str = '<span>　共计 '.setMoney($total['money'], '2', '2').' 元</span>';
				$this->assign ( 'total_str', $total_str );
			}

			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		//移除标志
		$this->assign('TB_DEL_FLAG',C('TB_DEL_FLAG'));
		//投保方式(线上,线下)
		$this->assign('ONLINE_FLAG',C('ONLINE_FLAG'));
		//投保状态
		$this->assign('TB_FLAG',C('TB_FLAG'));
		//保险公司
		$res = D($this->MSecurity)->getSecuritylist('','SECURITY_MAP_ID,SECURITY_NAME');
		$this->assign('sec_sel',$res);
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display();
	}
	
	/*
	* 付费卡意外险明细管理	详情
	**/
	public function pcarddetail_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$where = 'TB_ID = "'.$id.'"';
		$info  = D($this->TTbls)->findTbls($where, '*', $p->firstRow.','.$p->listRows);
		//会员信息
		$vip_info = D($this->GVip)->findVip('VIP_ID = '.$info['VIP_ID']);
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}	
		$this->assign ('info', $info);
		$this->assign ('vip_info', $vip_info);
		$this->display();
	}
	
	/*
	* 付费卡意外险明细管理	移除
	**/
	public function pcarddetail_del() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('TB_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('TB_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$tblsstatus = D($this->TTbls)->getTblslist($where,'TB_FLAG,TB_DEL_FLAG');
		foreach ($tblsstatus as $key => $value) {
			if (($value['TB_FLAG']!='2') || ($value['TB_DEL_FLAG']!='0')) {
				$this->wrong('此操作仅限投保状态为待提交并且移除标志为参保时执行');
			}
		}
		$res = D($this->TTbls)->updateTbls($where, array('TB_DEL_FLAG' => 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	
	/*
	* 付费卡意外险明细管理	添加
	**/
	public function pcarddetail_add() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('TB_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('TB_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$tblsstatus = D($this->TTbls)->getTblslist($where,'TB_FLAG,TB_DEL_FLAG');
		foreach ($tblsstatus as $key => $value) {
			if ($value['TB_DEL_FLAG']!='1') {
				$this->wrong('此操作仅限移除标志为移除时执行');
			}
		}
		$res = D($this->TTbls)->updateTbls($where, array('TB_DEL_FLAG' => 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}

	/*
	* 付费卡意外险明细管理	初始化
	**/
	public function pcarddetail_init() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('TB_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('TB_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$tblsstatus = D($this->TTbls)->getTblslist($where,'TB_FLAG,TB_DEL_FLAG');
		foreach ($tblsstatus as $key => $value) {
			if (($value['TB_FLAG']!='4') || ($value['TB_DEL_FLAG']!='0')) {
				$this->wrong('此操作仅限投保状态为待确认并且移除标志为参保时执行');
			}
		}
		$res = D($this->TTbls)->updateTbls($where, array('TB_FLAG' => 2));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}

	/*
	* 付费卡意外险明细管理	提交投保
	**/
	public function pcarddetail_submit() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('请勾选需要投保的数据（提交投保数据必须是付费卡,意外险,待提交,参保状态）');
		}
		
		$home = session('HOME');
		//统计条件（会员卡类型：付费卡；保险类型：意外险；投保结果：待提交；移除标识：参保；在线状态：线上）
		$where = 'VIP_FLAG = 2 and SECURITY_TYPE = 1 and TB_FLAG = 2 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
		//数据隔离
		if(!filter_auth($home['BRANCH_MAP_ID'])){
			$pids = get_plv_childs($home['PARTNER_MAP_ID'],1);
			$where .= ' and PARTNER_MAP_ID in ('.$pids.')';
		}
		//统计勾选投保数据
		if(is_array($ids)){
			$where .= ' and TB_ID in ('.implode(',', $ids).')';
		}else{
			$where .= ' and TB_ID = "'.$ids.'"';
		}
		$res = D($this->TTbls)->findTbls($where,'SECURITY_MAP_ID,SECURITY_TYPE,ONLINE_FLAG,JFB_SECU_REF,COUNT(VIP_ID) as num, SUM(TB_AMT) as summoney');

		//生成汇总单
		$tbbill_data = array(
			'BRANCH_MAP_ID'		=> $home['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'	=> $home['PARTNER_MAP_ID'],
			'SECURITY_MAP_ID'	=> $res['SECURITY_MAP_ID'],
			'SECURITY_TYPE'		=> $res['SECURITY_TYPE'],
			'ONLINE_FLAG'		=> $res['ONLINE_FLAG'],
			'JFB_SECU_REF'		=> $res['JFB_SECU_REF'],
			'TB_TIME'			=> date('YmdHis'),
			'TB_FLAG'			=> 1,
			'VIP_FLAG'			=> 2,
			'TB_STATUS'			=> 4,
			'TB_AMT'			=> $res['summoney'],
			'TB_CNT'			=> $res['num'],
			'TB_AMT_SUCC'		=> 0,
			'TB_CNT_SUCC'		=> 0
		);
		//入库到汇总表
		$tbbill_res = D($this->TTbbill)->addTbbill($tbbill_data);
		if ($tbbill_res['state'] != 0) {
			$this->wrong('提交失败');
		}

		//获取投保数据，遍历更改状态
		$tblsstatus = D($this->TTbls)->getTblslist($where);
		$ref = '20';
		foreach ($tblsstatus as $key => $value) {
			//更新所有批次号对应该的状态值及批次
			D($this->TTbls)->updateTbls('TB_ID = "'.$value['TB_ID'].'"', array('TB_FLAG'=>4,'JFB_SECU_REF' =>$ref.$tbbill_res['TBBILL_ID']));
			D($this->TTbbill)->updateTbbill('TBBILL_ID = "'.$tbbill_res['TBBILL_ID'].'"',array('JFB_SECU_REF' =>$ref.$tbbill_res['TBBILL_ID']));
		}
		$this->right($ref.$tbbill_res['TBBILL_ID'].'批次，共计成功提交：'.$res['num'].'个会员，'.setMoney($res['summoney'],2,2).'元', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	//意外险手动投保【预免卡意外险】
	public function yw_custom_post2(){
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('请勾选需要投保的数据（提交投保数据必须是预免卡,意外险,待确认,参保状态）');
		}
		$this->custom_post($ids,3);
	}

	//意外险手动投保【付费卡意外险】
	public function yw_custom_post(){
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('请勾选需要投保的数据（提交投保数据必须是付费卡,意外险,待确认,参保状态）');
		}
		$this->custom_post($ids,2);
	}

	//意外险手动投保【公共】
	public function custom_post($ids,$vipflag){
		//实例化投保模型对象
		$tblsModel = D($this->TTbls);
		$vipModel  = D($this->GVip);
		$num = 0;
		if(is_array($ids)){
			foreach ($ids as $key => $value) {
				//更新保单号对应该的状态值(投保确认的数据必须是付费卡,意外险,待确认,参保, 在线投保状态)
				$where = 'TB_ID = "'.$value.'" and VIP_FLAG = '.$vipflag.' and SECURITY_TYPE = 1 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
				$res = $tblsModel->findTbls($where, '*');
				if (!$res['TB_ID']) {
					continue;
				}
				//查看该保险号是否已经投保
				$is_insure = $tblsModel->findTbls("VIP_ID = ".$res['VIP_ID']." and SECURITY_TYPE = 1 and TB_FLAG = 0 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0", '*');
				if($is_insure['VIP_ID']){
					$TB_UPDATA = array('TB_FLAG'=>6,'TB_DESC'=>'已经投过');		//如果不成功更新为失败
					//更新保单号对应该的状态值(投保确认的数据必须是预免卡,意外险,待确认,参保, 在线投保状态)
					$t_res = $tblsModel->updateTbls("TB_ID = '".$value."'", $TB_UPDATA);
				}else{
					//获得会员数据
					$vipdata = $vipModel->findVip('VIP_ID = '.$res['VIP_ID'], '*');
					$vipdata['SEX_NAME'] = $vipdata['VIP_SEX'] ? '男' : '女';
					$vipdata['VIP_SEX'] = $vipdata['VIP_SEX'] ? 1 : 2;
					$this->assign('vipdata',$vipdata);
					$this->assign('data',$res);
					//发送XML数据(加密发送XML报文)
					$xmldata = $this->fetch('accident_insurance');		//意外险
					//发送并接收返回值
					$url = C('INSURE_URL1');	//意外险投保链接
					$xml_arr = $this->post_xml($xmldata,$url,0);
					if ($xml_arr) {
						$backdata = array(
							'TransRefGUID' 		=> $xml_arr['TXLifeResponse']['TransRefGUID'],
							'ResultCode' 		=> $xml_arr['TXLifeResponse']['TransResult']['ResultCode'],
							'ResultInfoDesc'	=> $xml_arr['TXLifeResponse']['TransResult']['ResultInfo']['ResultInfoDesc'],
							'PolNumber' 		=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['PolNumber'],
							'HOAppFormNumber' 	=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['ApplicationInfo']['HOAppFormNumber'],
							'BankCode' 			=> $xml_arr['TXLifeResponse']['OLifEExtension']['BankCode']
						);
						if ($backdata['ResultCode'] == 'Success') {
							//如果成功更新数据状态
							$TB_UPDATA = array(
								'TB_NO'		=>	$backdata['PolNumber'] ? $backdata['PolNumber'] : $backdata['HOAppFormNumber'],
								'TB_FLAG'	=>	0,
								'TB_TIME'	=>	date('YmdHis'),
								'TB_DESC'	=>	$backdata['ResultInfoDesc']
							);
							//修改vip表
							if(strlen($vipdata['RES']) == '80'){
								$yw_no = setStrzero($TB_UPDATA['TB_NO'], 40, ' ','r');	//意外险单号
								$yl_no = substr($vipdata['RES'],40,40);	//养老险单号
								$vip_res = $yw_no.$yl_no;
							}else{
								$vip_res = setStrzero($TB_UPDATA['TB_NO'], 80, ' ','r');
							}
							$vipModel->updateVip('VIP_ID = '.$res['VIP_ID'], array('RES' => $vip_res));
							$num++;
						}else{
							//如果不成功更新为失败
							$TB_UPDATA = array(
								'TB_FLAG'	=>	1,
								'TB_DESC'	=>	$backdata['ResultInfoDesc']
							);
						}
						//更新保单号对应该的状态值(投保确认的数据必须是预免卡,意外险,待确认,参保, 在线投保状态)
						$where = 'TB_ID = "'.$res['TB_ID'].'" and VIP_FLAG = '.$vipflag.' and SECURITY_TYPE = 1 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
						$t_res = $tblsModel->updateTbls($where, $TB_UPDATA);
					}
				}
				//更新现有的，投保数据（将所有意外险投保失败的数据，改为过期状态）
				$upwhere = 'VIP_ID = "'.$res['VIP_ID'].'" and TB_ID < '.$res['TB_ID'].' and VIP_FLAG = '.$vipflag.' and SECURITY_TYPE = 1 and TB_FLAG = 1 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
				D($this->TTbls)->updateTbls($upwhere, array('TB_FLAG' => 6));
			}
		}else{
			//更新保单号对应该的状态值(投保确认的数据必须是付费卡,意外险,待确认,参保, 在线投保状态)
			$where = 'TB_ID = "'.$ids.'" and VIP_FLAG = '.$vipflag.' and SECURITY_TYPE = 1 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
			$res = $tblsModel->findTbls($where, '*');
			if (!$res['TB_ID']) {
				continue;
			}
			
			//查看该保险号是否已经投保
			$is_insure = $tblsModel->findTbls("VIP_ID = ".$res['VIP_ID']." and SECURITY_TYPE = 1 and TB_FLAG = 0 and and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0", '*');
			if($is_insure['VIP_ID']){
				$TB_UPDATA = array('TB_FLAG'=>1,'TB_DESC'=>'已经投过');		//如果不成功更新为失败
				//更新保单号对应该的状态值(投保确认的数据必须是预免卡,意外险,待确认,参保, 在线投保状态)
				$t_res = $tblsModel->updateTbls("TB_ID = '".$is_insure['TB_ID']."'", $TB_UPDATA);
			}else{
				//获得会员数据
				$vipdata = $vipModel->findVip('VIP_ID = '.$res['VIP_ID'], '*');
				$vipdata['SEX_NAME'] = $vipdata['VIP_SEX'] ? '男' : '女';
				$vipdata['VIP_SEX'] = $vipdata['VIP_SEX'] ? 1 : 2;
				$this->assign('vipdata',$vipdata);
				$this->assign('data',$res);
				//发送XML数据(加密发送XML报文)
				$xmldata = $this->fetch('accident_insurance');		//意外险
				//发送并接收返回值
				$url = C('INSURE_URL1');	//意外险投保链接
				$xml_arr = $this->post_xml($xmldata,$url,0);
				if ($xml_arr) {
					$backdata = array(
						'TransRefGUID' 		=> $xml_arr['TXLifeResponse']['TransRefGUID'],
						'ResultCode' 		=> $xml_arr['TXLifeResponse']['TransResult']['ResultCode'],
						'ResultInfoDesc'	=> $xml_arr['TXLifeResponse']['TransResult']['ResultInfo']['ResultInfoDesc'],
						'PolNumber' 		=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['PolNumber'],
						'HOAppFormNumber' 	=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['ApplicationInfo']['HOAppFormNumber'],
						'BankCode' 			=> $xml_arr['TXLifeResponse']['OLifEExtension']['BankCode']
					);
					if ($backdata['ResultCode'] == 'Success') {
						//如果成功更新数据状态
						$TB_UPDATA = array(
							'TB_NO'		=>	$backdata['PolNumber'] ? $backdata['PolNumber'] : $backdata['HOAppFormNumber'],
							'TB_FLAG'	=>	0,
							'TB_TIME'	=>	date('YmdHis'),
							'TB_DESC'	=>	$backdata['ResultInfoDesc']
						);
						//修改vip表
						if(strlen($vipdata['RES']) == '80'){
							$yw_no = setStrzero($TB_UPDATA['TB_NO'], 40, ' ','r');	//意外险单号
							$yl_no = substr($vipdata['RES'],40,40);	//养老险单号
							$vip_res = $yw_no.$yl_no;
						}else{
							$vip_res = setStrzero($TB_UPDATA['TB_NO'], 80, ' ','r');
						}
						$vipModel->updateVip('VIP_ID = '.$res['VIP_ID'], array('RES' => $vip_res));
						$num++;
						//更新现有的，投保数据（将所有意外险投保失败的数据，改为过期状态）
						$upwhere = 'VIP_FLAG = '.$vipflag.' and SECURITY_TYPE = 1 and TB_FLAG = 1 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0 and VIP_ID = "'.$res['VIP_ID'].'" and TB_ID < '.$res['TB_ID'];
						D($this->TTbls)->updateTbls($upwhere, array('TB_FLAG' => 6));
					}else{
						//如果不成功更新为失败
						$TB_UPDATA = array(
							'TB_FLAG'	=>	1,
							'TB_DESC'	=>	$backdata['ResultInfoDesc']
						);
					}
					//更新保单号对应该的状态值(投保确认的数据必须是预免卡,意外险,待确认,参保, 在线投保状态)
					$where = 'TB_ID = "'.$res['TB_ID'].'" and VIP_FLAG = '.$vipflag.' and SECURITY_TYPE = 1 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
					$t_res = $tblsModel->updateTbls($where, $TB_UPDATA);
				}
				//更新现有的，投保数据（将所有意外险投保失败的数据，改为过期状态）
				$upwhere = 'VIP_ID = "'.$res['VIP_ID'].'" and TB_ID < '.$res['TB_ID'].' and VIP_FLAG = '.$vipflag.' and SECURITY_TYPE = 1 and TB_FLAG = 1 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
				D($this->TTbls)->updateTbls($upwhere, array('TB_FLAG' => 6));
			}
		}
		$this->right('共计成功提交：'.$num.'个会员，'.($num*8).'元', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}


	/*
	* 预免卡意外险明细管理	撤销投保
	**/
	public function pcarddetail_cancel() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('TB_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('TB_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$tblsstatus = D($this->TTbls)->getTblslist($where,'TB_FLAG,TB_DEL_FLAG');
		foreach ($tblsstatus as $key => $value) {
			if (($value['TB_FLAG']!='0') || ($value['TB_DEL_FLAG']!='0')) {
				$this->wrong('此操作仅限投保状态为成功的时候才允许撤销操作');
			}
		}
		$res = D($this->TTbls)->updateTbls($where, array('TB_FLAG' => 5));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}

	/*
	* 付费卡意外险明细管理 导出
	**/
	public function pcarddetail_export() {
		$post  = array(
			'bid'				=>	I('bid'),
			'pid'				=>	I('pid'),
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A') ? I('SYSTEM_DATE_A') : '',
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B') ? I('SYSTEM_DATE_B') : '',
			'SECURITY_MAP_ID'	=>	I('SECURITY_MAP_ID'),
			'TB_DEL_FLAG'		=>	I('TB_DEL_FLAG'),
			'ONLINE_FLAG'		=>	I('ONLINE_FLAG'),
			'VIP_MOBILE'		=>	I('VIP_MOBILE'),
			'VIP_CARDNO'		=>	I('VIP_CARDNO'),
			'VIP_IDNO'			=>	I('VIP_IDNO'),
			'TB_NO'				=>	I('TB_NO'),
			'TB_FLAG'			=>	I('TB_FLAG'),
			'TB_FLAG1'			=>	I('TB_FLAG1'),
			'JFB_SECU_REF'		=>	I('JFB_SECU_REF')
		);
		$where = "SECURITY_TYPE=1 and VIP_FLAG=2 and TB_FLAG!=9";
		//分公司
		if($post['bid'] != '') {
			$where .= " and BRANCH_MAP_ID = '".$post['bid']."'";
		}
		//合作伙伴
		if($post['pid'] != '') {
			$pids = get_plv_childs($post['pid'],1);
			$where .= " and PARTNER_MAP_ID in(".$pids.")";
		}
		//开始时间
		if ($post['SYSTEM_DATE_A']) {
			$where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
		}
		//结束时间
		if ($post['SYSTEM_DATE_B']) {
			$where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
		}
		//保险公司
		if ($post['SECURITY_MAP_ID']) {
			$where .= " and SECURITY_MAP_ID = '".$post['SECURITY_MAP_ID']."'";
		}
		//移除标志
		if ($post['TB_DEL_FLAG']!='') {
			$where .= " and TB_DEL_FLAG = '".$post['TB_DEL_FLAG']."'";
		}
		//投保方式
		if ($post['ONLINE_FLAG'] != '') {
			$where .= " and ONLINE_FLAG = '".$post['ONLINE_FLAG']."'";
		}
		//会员手机
		if ($post['VIP_MOBILE']) {
			$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
		}
		//会员卡号
		if ($post['VIP_CARDNO']) {
			$where .= " and VIP_CARDNO = '".$post['VIP_CARDNO']."'";
		}
		//会员身份证号
		if ($post['VIP_IDNO']) {
			$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'";
		}
		//投保单号
		if ($post['TB_NO']) {
			$where .= " and TB_NO = '".$post['TB_NO']."'";
		}
		//状态
		if($post['TB_FLAG'] != '' && $post['TB_FLAG1'] != '') {
			$where .= " and (TB_FLAG = '".$post['TB_FLAG']."' or TB_FLAG = '".$post['TB_FLAG1']."')";
		}else if($post['TB_FLAG'] != ''){
			$where .= " and TB_FLAG = '".$post['TB_FLAG']."'";
		}else if($post['TB_FLAG1'] != ''){
			$where .= " and TB_FLAG = '".$post['TB_FLAG1']."'";
		}
		//批次号
		if($post['JFB_SECU_REF']) {
			$where .= " and JFB_SECU_REF = '".$post['JFB_SECU_REF']."'";
		}

		//计算
		$count = D($this->TTbls)->countTbls($where);
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
			$list  = D($this->TTbls)->getTblslist($where, '*', $bRow.','.$eRow);
			//导出操作
			$xlsname = '付费卡意外险明细管理';
			$xlscell = array(
				array('TB_ID',				'投保流水号'),
				array('VIP_NAME',			'会员名称'),
				array('VIP_MOBILE',			'手机号'),
				array('VIP_IDNO',			'身份证号'),
				array('VIP_CARDNO',			'卡号'),
				array('TB_AMT',				'投保金额'),
				array('VIP_EMAIL',			'邮箱'),
				array('TB_FLAG',			'投保结果'),
				array('SECURITY_MAP_ID',	'保险公司'),
				array('TB_NO',				'保单号'),
				array('ONLINE_FLAG',		'投保方式'),		
				array('TB_TIME',			'投保日期'),		
				array('GUISHU_3',			'归属服务中心'),
				array('GUISHU',				'归属'),
				array('TB_DESC',			'备注')
			);		
			$xlsarray = array();
			foreach($list as $val){
				$guishu = get_level_name($val['PARTNER_MAP_ID'], $val['BRANCH_MAP_ID']);
				$gejizuzhi = explode('-', $guishu);
				$xlsarray[] = array(
					'TB_ID'			=>	$val['TB_ID'],
					'VIP_NAME'		=>	$val['VIP_NAME'],
					'VIP_MOBILE'	=>	$val['VIP_MOBILE']."\t",
					'VIP_IDNO'		=>	$val['VIP_IDNO']."\t",
					'VIP_CARDNO'	=>	$val['VIP_CARDNO']."\t",
					'TB_AMT'		=>	setMoney($val['TB_AMT'], '2', '2'),
					'VIP_EMAIL'		=>	$val['VIP_EMAIL'],
					'TB_FLAG'		=>	C('TB_FLAG')[$val['TB_FLAG']],
					'SECURITY_NAME'	=>	get_security_name($val['SECURITY_MAP_ID']),
					'TB_NO'			=>	$val['TB_NO']."\t",
					'ONLINE_FLAG'	=>	C('ONLINE_FLAG')[$val['ONLINE_FLAG']],
					'TB_TIME'		=>	$val['TB_TIME']."\t",
					'GUISHU_3'		=>	$gejizuzhi[2],
					'GUISHU'		=>	$guishu,
					'TB_DESC'		=>	$val['TB_DESC']
				);	
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		$this->display('Public/export');
	}

	
	
	
	
	/*
	* 付费卡意外险投保管理
	**/
	public function pcardinsure() {
		$post = I('post');
		if($post['submit'] == "pcardinsure"){
			$where = "SECURITY_TYPE = 1 and VIP_FLAG = 2";
			$soplv = filter_data('soplv');	//列表查询
			//分公司
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
			//保险公司
			if ($post['SECURITY_MAP_ID']) {
				$where .= " and SECURITY_MAP_ID = '".$post['SECURITY_MAP_ID']."'";
			}
			//状态
			if($post['TB_STATUS'] != '') {
				$where .= " and TB_STATUS = '".$post['TB_STATUS']."'";
			}
			//批次号
			if($post['JFB_SECU_REF']) {
				$where .= " and JFB_SECU_REF = '".$post['JFB_SECU_REF']."'";
			}
			//开始时间
			if ($post['SYSTEM_DATE_A']) {
				$where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			}
			//结束时间
			if ($post['SYSTEM_DATE_B']) {
				$where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			}
			//分页
			$count = D($this->TTbbill)->countTbbill($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TTbbill)->getTbbilllist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
		//投保方式(线上,线下)
		$this->assign('ONLINE_FLAG',C('ONLINE_FLAG'));
		//投保状态
		$this->assign('TB_STATUS',C('TB_STATUS_TBBILL'));
		//保险公司
		$res = D($this->MSecurity)->getSecuritylist('','SECURITY_MAP_ID,SECURITY_NAME');
		$this->assign('sec_sel',$res);
		\Cookie::set ('_currentUrl_', 	__SELF__);	
		$this->display();
	}

	/*
	* 付费卡意外险投保管理	投保确认
	**/
	public function pcardinsure_submit() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$home = session("HOME");
		$where = 'TBBILL_ID = "'.$id.'"';
		$info  = D($this->TTbbill)->findTbbill($where, 'PARTNER_MAP_ID,JFB_SECU_REF,TB_STATUS' );
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if ($info['TB_STATUS'] !=4) {
			$this->wrong("当前状态无法完成此操作！");
		}

		//得到所有当前批次号对应的数据 
		$where1 = 'JFB_SECU_REF = '.$info['JFB_SECU_REF'].' and VIP_FLAG = 2 and SECURITY_TYPE = 1 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
		//数据隔离
		if(!filter_auth($home['BRANCH_MAP_ID'])){
			$pids = get_plv_childs($info['PARTNER_MAP_ID'],1);
			$where1.= ' and PARTNER_MAP_ID in ('.$pids.')';
		}
		$res = D($this->TTbls)->getTblslist($where1.' and TB_FLAG = 4');
		foreach ($res as $key => $value) {
			//获得会员数据
			$vipdata = D($this->GVip)->findVip('VIP_ID = '.$value['VIP_ID'], $field='*');
			$vipdata['SEX_NAME'] = $vipdata['VIP_SEX'] ? '男' : '女';
			$vipdata['VIP_SEX'] = $vipdata['VIP_SEX'] ? 1 : 2;
			//查看该保险号是否已经投保
			$is_insure = D($this->TTbls)->findTbls("VIP_ID = ".$value['VIP_ID']." and SECURITY_TYPE = 1 and TB_FLAG = 0 and ONLINE_FLAG = 0", $field='*');
			if($is_insure['VIP_ID']){
				$TB_FLAG = array('TB_FLAG'=>6,'TB_DESC'=>'已经投过');		//如果不成功更新为失败
				//更新保单号对应该的状态值(投保确认的数据必须是预免卡,意外险,待确认,参保, 在线投保状态)
				$where = 'TB_ID = '.$value['TB_ID'].' and VIP_FLAG = 2 and SECURITY_TYPE = 1 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
				$t_res = D($this->TTbls)->updateTbls($where, $TB_FLAG);
			}else{
				$this->assign('vipdata',$vipdata);
				$this->assign('data',$value);
				//发送XML数据(加密发送XML报文)
				$xmldata = $this->fetch('accident_insurance');		//意外险
				//发送并接收返回值
				$url = C('INSURE_URL1');	//意外险投保链接
				$xml_arr = $this->post_xml($xmldata,$url);
				if ($xml_arr) {
					$backdata = array(
						'TransRefGUID' 		=> $xml_arr['TXLifeResponse']['TransRefGUID'],
						'ResultCode' 		=> $xml_arr['TXLifeResponse']['TransResult']['ResultCode'],
						'ResultInfoDesc'	=> $xml_arr['TXLifeResponse']['TransResult']['ResultInfo']['ResultInfoDesc'],
						'PolNumber' 		=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['PolNumber'],
						'HOAppFormNumber' 	=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['ApplicationInfo']['HOAppFormNumber'],
						'BankCode' 			=> $xml_arr['TXLifeResponse']['OLifEExtension']['BankCode']
					);
					if ($backdata['ResultCode'] == 'Success') {
						//如果成功更新数据状态
						$TB_FLAG = array(
							'TB_NO'		=>	$backdata['PolNumber'] ? $backdata['PolNumber'] : $backdata['HOAppFormNumber'],
							'TB_FLAG'	=>	0,
							'TB_TIME'	=>	date('YmdHis'),
							'TB_DESC'	=>	$backdata['ResultInfoDesc']
						);
						//修改vip表
						if(strlen($vipdata['RES']) == '80'){
							$yw_no = setStrzero($TB_FLAG['TB_NO'], 40, ' ','r');	//意外险单号
							$yl_no = substr($vipdata['RES'],40,40);	//养老险单号
							$vip_res = $yw_no.$yl_no;
						}else{
							$vip_res = setStrzero($TB_FLAG['TB_NO'], 80, ' ','r');
						}
						D($this->GVip)->updateVip('VIP_ID = '.$value['VIP_ID'], array('RES' => $vip_res));
					}else{
						//如果不成功更新为失败
						$TB_FLAG = array(
							'TB_FLAG'	=>	1,
							'TB_DESC'	=>	$backdata['ResultInfoDesc']
						);
					}
					//更新保单号对应该的状态值(投保确认的数据必须是预免卡,意外险,待确认,参保, 在线投保状态)
					$where = 'TB_ID = '.$value['TB_ID'].' and VIP_FLAG = 2 and SECURITY_TYPE = 1 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
					$t_res = D($this->TTbls)->updateTbls($where, $TB_FLAG);
				}	
			}
			//更新现有的，投保数据（将所有意外险投保失败的数据，改为过期状态）
			$upwhere = "VIP_ID = '".$value['VIP_ID']."' and TB_ID < ".$value['TB_ID']." and VIP_FLAG = 2 and SECURITY_TYPE = 1 and TB_FLAG = 1 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0";
			D($this->TTbls)->updateTbls($upwhere, array('TB_FLAG' => 6));
		}
		$tb_money_succ = D($this->TTbls)->sumTbls($where1.' and TB_FLAG = 0', 'TB_AMT');			//投保金额
		$tb_num_succ = D($this->TTbls)->countTbls($where1.' and TB_FLAG = 0', 'TB_CNT');			//投保人数

		//更新所有批次号对应该的状态值
		$up_tbbill = array(
			'TB_FLAG'		=> 0, 				//投保结果(0：正常 1：未确认 2：失败)
			'TB_STATUS'		=> 0, 				//状态标识(0：已投保 1：见审批表)
			'TB_AMT_SUCC'	=> $tb_money_succ, 	//成功投保金额
			'TB_CNT_SUCC'	=> $tb_num_succ		//成功投保人数
		);
		$g_res = D($this->TTbbill)->updateTbbill('TBBILL_ID = "'.$id.'"',$up_tbbill);
		if (!$g_res) {
			$this->wrong('提交失败');
		}
		$this->right('提交成功', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}


	/*
	* 付费卡意外险明细管理	详情
	**/
	public function pcardinsure_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$where = 'TBBILL_ID = "'.$id.'"';
		$info  = D($this->TTbbill)->findTbbill($where);
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}	
		$this->assign ('info', $info);
		$this->display();
	}
	
	
	
	/*
	* 养老险明细管理
	**/
	public function pendetail() {
		$post = I('post');
		if($post['submit'] == "pendetail"){
			$where = "SECURITY_TYPE=2 and TB_FLAG!=9";
			$soplv = filter_data('soplv');	//列表查询
			//分公司
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
			//开始时间
			if ($post['SYSTEM_DATE_A']) {
				$where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			}
			//结束时间
			if ($post['SYSTEM_DATE_B']) {
				$where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			}
			//保险公司
			if ($post['SECURITY_MAP_ID']) {
				$where .= " and SECURITY_MAP_ID = '".$post['SECURITY_MAP_ID']."'";
			}
			//移除标志
			if ($post['TB_DEL_FLAG']!='') {
				$where .= " and TB_DEL_FLAG = '".$post['TB_DEL_FLAG']."'";
			}
			//投保方式
			if ($post['ONLINE_FLAG'] != '') {
				$where .= " and ONLINE_FLAG = '".$post['ONLINE_FLAG']."'";
			}
			//会员手机
			if ($post['VIP_MOBILE']) {
				$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
			}
			//会员卡号
			if ($post['VIP_CARDNO']) {
				$where .= " and VIP_CARDNO = '".$post['VIP_CARDNO']."'";
			}
			//会员身份证号
			if ($post['VIP_IDNO']) {
				$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'";
			}
			//投保单号
			if ($post['TB_NO']) {
				$where .= " and TB_NO = '".$post['TB_NO']."'";
			}
			//卡类型
			if ($post['VIP_FLAG']) {
				$where .= " and VIP_FLAG = '".$post['VIP_FLAG']."'";
			}
			
			//状态
			if($post['TB_FLAG'] != '' && $post['TB_FLAG1'] != '') {
				$where .= " and (TB_FLAG = '".$post['TB_FLAG']."' or TB_FLAG = '".$post['TB_FLAG1']."')";
			}else if($post['TB_FLAG'] != ''){
				$where .= " and TB_FLAG = '".$post['TB_FLAG']."'";
			}else if($post['TB_FLAG1'] != ''){
				$where .= " and TB_FLAG = '".$post['TB_FLAG1']."'";
			}
			//批次号
			if($post['JFB_SECU_REF']) {
				$where .= " and JFB_SECU_REF = '".$post['JFB_SECU_REF']."'";
			}
			//过滤失败的重复数据
			/*if($post['TB_FLAG1'] == '1'){
				$Model = M('tbls', DB_PREFIX_TRA, DB_DSN_TRA); // 实例化一个model对象 没有对应任何数据表
				//分页
				$count_sql = "select count(a.TB_ID) as total from (select * from  t_tbls where ".$where." group by VIP_ID) a";
				$res = $Model->query($count_sql);
				$count = $res[0]['total'];
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$limit = ' limit '.$p->firstRow.",".$p->listRows;
				$sql = "select * from (select * from  t_tbls order by TB_ID desc) a where ".$where." group by VIP_ID order by TB_ID desc".$limit;
				$list = $Model->query($sql);
			}else{
				//分页
				$count = D($this->TTbls)->countTbls($where);
				$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
				$list  = D($this->TTbls)->getTblslist($where, '*', $p->firstRow.','.$p->listRows);
			}*/

			$count = D($this->TTbls)->countTbls($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TTbls)->getTblslist($where, '*', $p->firstRow.','.$p->listRows);
			//统计投保参数(仅成功和失败统计)
			if($post['TB_FLAG1'] == '0' or $post['TB_FLAG1'] == '1' or $post['TB_FLAG'] == '2'){
				$total = D($this->TTbls)->findTbls($where, 'sum(TB_AMT) as money');
				$total_str = '<span>　共计 '.setMoney($total['money'], '2', '2').' 元</span>';
				$this->assign ( 'total_str', $total_str );
			}
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );

			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		//移除标志
		$this->assign('TB_DEL_FLAG',C('TB_DEL_FLAG'));
		//投保方式(线上,线下)
		$this->assign('ONLINE_FLAG',C('ONLINE_FLAG'));
		//投保状态
		$this->assign('TB_FLAG',C('TB_FLAG'));
		//保险公司
		$res = D($this->MSecurity)->getSecuritylist('','SECURITY_MAP_ID,SECURITY_NAME');
		//获取卡套餐列表
		$cardsel = D($this->MCproduct)->getCproductlist_one('CARD_P_MAP_ID = 2 or CARD_P_MAP_ID = 3','CARD_P_MAP_ID,CARD_NAME');

		$this->assign('cardsel',$cardsel);
		$this->assign('sec_sel',$res);
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display();
	}
	
	/*
	* 养老险明细管理	详情
	**/
	public function pendetail_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$where = 'TB_ID = "'.$id.'"';
		$info  = D($this->TTbls)->findTbls($where, '*', $p->firstRow.','.$p->listRows);
		//会员信息
		$vip_info = D($this->GVip)->findVip('VIP_ID = '.$info['VIP_ID']);
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}	
		$this->assign ('info', $info);
		$this->assign ('vip_info', $vip_info);
		$this->display();
	}
	/*
	* 养老险明细管理	移除
	**/
	public function pendetail_del() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('TB_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('TB_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$tblsstatus = D($this->TTbls)->getTblslist($where,'TB_FLAG,TB_DEL_FLAG');
		foreach ($tblsstatus as $key => $value) {
			if (($value['TB_FLAG']!='2') || ($value['TB_DEL_FLAG']!='0')) {
				$this->wrong('此操作仅限投保状态为待提交并且移除标志为参保时执行');
			}
		}
		$res = D($this->TTbls)->updateTbls($where, array('TB_DEL_FLAG' => 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	
	/*
	* 养老险明细管理	添加
	**/
	public function pendetail_add() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('TB_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('TB_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$tblsstatus = D($this->TTbls)->getTblslist($where,'TB_FLAG,TB_DEL_FLAG');
		foreach ($tblsstatus as $key => $value) {
			if ($value['TB_DEL_FLAG']!='1') {
				$this->wrong('此操作仅限移除标志为移除时执行');
			}
		}
		$res = D($this->TTbls)->updateTbls($where, array('TB_DEL_FLAG' => 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	
	/*
	* 养老险明细管理	初始化
	**/
	public function pendetail_init() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('TB_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('TB_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$tblsstatus = D($this->TTbls)->getTblslist($where,'TB_FLAG,TB_DEL_FLAG');
		foreach ($tblsstatus as $key => $value) {
			if (($value['TB_FLAG']!='4') || ($value['TB_DEL_FLAG']!='0')) {
				$this->wrong('此操作仅限养老险投保状态为待确认的并且移除标志为参保时执行');
			}
		}
		$res = D($this->TTbls)->updateTbls($where, array('TB_FLAG' => 2));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}

	/*
	* 养老险明细管理	提交投保
	**/
	public function pendetail_submit() {
		$home = session('HOME');
		//查询养老险，待提交，参保，在线状态的
		$where = 'SECURITY_TYPE = 2 and TB_FLAG = 2 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
		//数据隔离
		if(!filter_auth($home['BRANCH_MAP_ID'])){
			$pids = get_plv_childs($home['PARTNER_MAP_ID'],1);
			$where.= ' and PARTNER_MAP_ID in ('.$pids.')';
		}
		$res = D($this->TTbls)->getTblsgroup($where);
		if (empty($res)) {
			$this->wrong('当前没有可提交投保数据');//提交投保数据必须是养老险,待提交,参保状态
		}
		$tbbill_data = array(
			'BRANCH_MAP_ID'		=> $home['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'	=> $home['PARTNER_MAP_ID'],
			'SECURITY_MAP_ID'	=> $res['SECURITY_MAP_ID'],
			'SECURITY_TYPE'		=> $res['SECURITY_TYPE'],
			'ONLINE_FLAG'		=> $res['ONLINE_FLAG'],
			'JFB_SECU_REF'		=> $res['JFB_SECU_REF'],
			'TB_TIME'			=> date('YmdHis'),
			'TB_FLAG'			=> 1,
			'TB_STATUS'			=> 4,
			'TB_AMT'			=> $res['summoney'],
			'TB_CNT'			=> $res['num'],
			'TB_AMT_SUCC'		=> 0,
			'TB_CNT_SUCC'		=> 0
		);
		//入库到汇总表
		$tbbill_res = D($this->TTbbill)->addTbbill($tbbill_data);
		if ($tbbill_res['state'] != 0) {
			$this->wrong('提交失败');
		}
		//更新所有批次号对应该的状态值及批次
		$ref = '30';
		D($this->TTbls)->updateTbls($where.' and JFB_SECU_REF = "'.$res['JFB_SECU_REF'].'"',array('TB_FLAG'=>4,'JFB_SECU_REF' =>$ref.$tbbill_res['TBBILL_ID']));
		D($this->TTbbill)->updateTbbill('TBBILL_ID = "'.$tbbill_res['TBBILL_ID'].'"',array('JFB_SECU_REF' =>$ref.$tbbill_res['TBBILL_ID']));
		
		$this->right($ref.$tbbill_res['TBBILL_ID'].'批次，共计成功提交：'.$res['num'].'个会员，'.setMoney($res['summoney'],2,2).'元', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}





	//养老险手动投保【公共】
	public function yl_custom_post(){
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('请勾选需要投保的数据（提交投保数据必须是养老险,待确认,参保状态）');
		}
		//实例化投保模型对象
		$tblsModel = D($this->TTbls);
		$vipModel  = D($this->GVip);
		$num = 0;		//成功数量
		$yl_money = 0;	//成功金额
		if(is_array($ids)){
			foreach ($ids as $key => $value) {
				//查看该保险信息
				$where = 'TB_ID = "'.$value.'" and SECURITY_TYPE = 2 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
				$res = $tblsModel->findTbls($where, '*');
				//获得会员数据
				$vipdata = $vipModel->findVip('VIP_ID = '.$res['VIP_ID'], $field='*');
				$vipdata['SEX_NAME'] = $vipdata['VIP_SEX'] ? '男' : '女';
				$vipdata['VIP_SEX'] = $vipdata['VIP_SEX'] ? 1 : 2;
				$vipdata['VIP_OLD'] = getAgeByID($res['VIP_IDNO']) >= 45 ? getAgeByID($res['VIP_IDNO'])+1 : 45;
				$this->assign('vipdata',$vipdata);
				$this->assign('data',$res);
				//查看该保险号是否已经投保
				if($res['TB_NO']!= '' && substr($res['TB_NO'], 0, 1) != 'Y'){
					//发送XML数据(加密发送XML报文)
					$xmldata = $this->fetch('endowment_insurance_2');		//养老保险(续保)
					$url = C('INSURE_URL2');								//养老险续保链接
				}else{
					$xmldata = $this->fetch('endowment_insurance_1');		//养老保险(投保)
					$url = C('INSURE_URL1');								//养老险续保链接
				}
				//发送并接收返回值
				$xml_arr = $this->post_xml($xmldata,$url,0);
				if ($xml_arr) {
					$backdata = array(
						'TransRefGUID' 		=> $xml_arr['TXLifeResponse']['TransRefGUID'],
						'ResultCode' 		=> $xml_arr['TXLifeResponse']['TransResult']['ResultCode'],
						'ResultInfoDesc'	=> $xml_arr['TXLifeResponse']['TransResult']['ResultInfo']['ResultInfoDesc'],
						'PolNumber' 		=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['PolNumber'],
						'HOAppFormNumber' 	=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['ApplicationInfo']['HOAppFormNumber'],
						'BankCode' 			=> $xml_arr['TXLifeResponse']['OLifEExtension']['BankCode']
					);
					if ($backdata['ResultCode'] == 'Success') {
						//如果成功更新数据状态
						$TB_UPDATA = array(
							'TB_NO'		=>	$backdata['PolNumber'] ? $backdata['PolNumber'] : $backdata['HOAppFormNumber'],
							'TB_FLAG'	=>	0,
							'TB_TIME'	=>	date('YmdHis'),
							'TB_DESC'	=>	$backdata['ResultInfoDesc']
						);
						//修改vip表
						if(strlen($vipdata['RES']) == '80'){
							$yw_no = substr($vipdata['RES'],0,40);					//养老险单号
							$yl_no = setStrzero($TB_UPDATA['TB_NO'], 40, ' ','r');	//意外险单号
							$vip_res = $yw_no.$yl_no;
						}else{
							$yw_no = setStrzero('', 40, ' ','r');
							$yl_no = setStrzero($TB_UPDATA['TB_NO'], 40, ' ','r');
							$vip_res = $yw_no.$yl_no;
						}
						$vipModel->updateVip('VIP_ID = '.$res['VIP_ID'], array('RES' => $vip_res));
						$yl_money += $res['TB_AMT'];
						$num++;
					}else{
						//如果不成功更新为失败
						$TB_UPDATA = array(
							'TB_FLAG'	=>	1,
							'TB_DESC'	=>	$backdata['ResultInfoDesc']
						);
					}
					//更新保单号对应该的状态值(投保确认的数据必须是养老险, 待确认, 参保, 在线投保状态)
					$where = 'TB_ID = '.$res['TB_ID'].' and SECURITY_TYPE = 2 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
					$t_res = $tblsModel->updateTbls($where, $TB_UPDATA);
				}

				//更新现有的，投保数据（将所有意外险投保失败的数据，改为过期状态）
				$upwhere = "VIP_ID = '".$res['VIP_ID']."' and TB_ID < ".$res['TB_ID']." and SECURITY_TYPE = 2 and TB_FLAG = 1 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0";
				D($this->TTbls)->updateTbls($upwhere, array('TB_FLAG' => 6));
			}
		}else{
			//查看该保险信息
			$where = 'TB_ID = '.$ids.' and SECURITY_TYPE = 2 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
			$res = $tblsModel->findTbls($where, '*');
			//获得会员数据
			$vipdata = $vipModel->findVip('VIP_ID = '.$res['VIP_ID'], $field='*');
			$vipdata['SEX_NAME'] = $vipdata['VIP_SEX'] ? '男' : '女';
			$vipdata['VIP_SEX'] = $vipdata['VIP_SEX'] ? 1 : 2;
			$vipdata['VIP_OLD'] = getAgeByID($res['VIP_IDNO']) >= 45 ? getAgeByID($res['VIP_IDNO'])+1 : 45;
			$this->assign('vipdata',$vipdata);
			$this->assign('data',$res);
			//查看该保险号是否已经投保
			if($res['TB_NO']!= '' && substr($res['TB_NO'], 0, 1) != 'Y'){
				//发送XML数据(加密发送XML报文)
				$xmldata = $this->fetch('endowment_insurance_2');		//养老保险(续保)
				$url = C('INSURE_URL2');								//养老险续保链接
			}else{
				$xmldata = $this->fetch('endowment_insurance_1');		//养老保险(投保)
				$url = C('INSURE_URL1');								//养老险续保链接
			}
			//发送并接收返回值
			$xml_arr = $this->post_xml($xmldata,$url);
			if ($xml_arr) {
				$backdata = array(
					'TransRefGUID' 		=> $xml_arr['TXLifeResponse']['TransRefGUID'],
					'ResultCode' 		=> $xml_arr['TXLifeResponse']['TransResult']['ResultCode'],
					'ResultInfoDesc'	=> $xml_arr['TXLifeResponse']['TransResult']['ResultInfo']['ResultInfoDesc'],
					'PolNumber' 		=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['PolNumber'],
					'HOAppFormNumber' 	=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['ApplicationInfo']['HOAppFormNumber'],
					'BankCode' 			=> $xml_arr['TXLifeResponse']['OLifEExtension']['BankCode']
				);
				if ($backdata['ResultCode'] == 'Success') {
					//如果成功更新数据状态
					$TB_UPDATA = array(
						'TB_NO'		=>	$backdata['PolNumber'] ? $backdata['PolNumber'] : $backdata['HOAppFormNumber'],
						'TB_FLAG'	=>	0,
						'TB_TIME'	=>	date('YmdHis'),
						'TB_DESC'	=>	$backdata['ResultInfoDesc']
					);
					//修改vip表
					if(strlen($vipdata['RES']) == '80'){
						$yw_no = substr($vipdata['RES'],0,40);					//养老险单号
						$yl_no = setStrzero($TB_UPDATA['TB_NO'], 40, ' ','r');	//意外险单号
						$vip_res = $yw_no.$yl_no;
					}else{
						$yw_no = setStrzero('', 40, ' ','r');
						$yl_no = setStrzero($TB_UPDATA['TB_NO'], 40, ' ','r');
						$vip_res = $yw_no.$yl_no;
					}
					$vipModel->updateVip('VIP_ID = '.$res['VIP_ID'], array('RES' => $vip_res));
					$yl_money += $res['TB_AMT'];
					$num++;
				}else{
					//如果不成功更新为失败
					$TB_UPDATA = array(
						'TB_FLAG'	=>	1,
						'TB_DESC'	=>	$backdata['ResultInfoDesc']
					);
				}
				//更新保单号对应该的状态值(投保确认的数据必须是养老险, 待确认, 参保, 在线投保状态)
				$where = 'TB_ID = '.$res['TB_ID'].' and SECURITY_TYPE = 2 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
				$t_res = $tblsModel->updateTbls($where, $TB_UPDATA);
			}

			//更新现有的，投保数据（将所有意外险投保失败的数据，改为过期状态）
			$upwhere = "VIP_ID = '".$res['VIP_ID']."' and TB_ID < ".$res['TB_ID']." and SECURITY_TYPE = 2 and TB_FLAG = 1 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0";
			D($this->TTbls)->updateTbls($upwhere, array('TB_FLAG' => 6));
		}
		$this->right('共计成功提交：'.$num.'个会员，'.($yl_money/100).'元', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}







	/*
	* 养老险明细管理	撤销投保
	**/
	public function pendetail_cancel() {
		$ids = $_REQUEST['TB_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('TB_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('TB_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$tblsstatus = D($this->TTbls)->getTblslist($where,'TB_FLAG,TB_DEL_FLAG');
		foreach ($tblsstatus as $key => $value) {
			if (($value['TB_FLAG']!='0') || ($value['TB_DEL_FLAG']!='0')) {
				$this->wrong('此操作仅限投保状态为成功的时候才允许撤销操作');
			}
		}
		$res = D($this->TTbls)->updateTbls($where, array('TB_FLAG' => 5));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}

	//养老险明细管理 导出
	public function pendetail_export() {
		$post  = array(
			'bid'				=>	I('bid'),
			'pid'				=>	I('pid'),
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A') ? I('SYSTEM_DATE_A') : '',
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B') ? I('SYSTEM_DATE_B') : '',
			'SECURITY_MAP_ID'	=>	I('SECURITY_MAP_ID'),
			'TB_DEL_FLAG'		=>	I('TB_DEL_FLAG'),
			'ONLINE_FLAG'		=>	I('ONLINE_FLAG'),
			'VIP_MOBILE'		=>	I('VIP_MOBILE'),
			'VIP_CARDNO'		=>	I('VIP_CARDNO'),
			'VIP_IDNO'			=>	I('VIP_IDNO'),
			'TB_NO'				=>	I('TB_NO'),
			'TB_FLAG'			=>	I('TB_FLAG'),
			'TB_FLAG1'			=>	I('TB_FLAG1'),
			'JFB_SECU_REF'		=>	I('JFB_SECU_REF'),
			'VIP_FLAG'			=>	I('VIP_FLAG')
		);
		$where = "SECURITY_TYPE=2 and TB_FLAG!=9";
		//分公司
		if($post['bid'] != '') {
			$where .= " and BRANCH_MAP_ID = '".$post['bid']."'";
		}
		//合作伙伴
		if($post['pid'] != '') {
			$pids = get_plv_childs($post['pid'],1);
			$where .= " and PARTNER_MAP_ID in(".$pids.")";
		}
		//开始时间
		if ($post['SYSTEM_DATE_A']) {
			$where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
		}
		//结束时间
		if ($post['SYSTEM_DATE_B']) {
			$where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
		}
		//保险公司
		if ($post['SECURITY_MAP_ID']) {
			$where .= " and SECURITY_MAP_ID = '".$post['SECURITY_MAP_ID']."'";
		}
		//移除标志
		if ($post['TB_DEL_FLAG']!='') {
			$where .= " and TB_DEL_FLAG = '".$post['TB_DEL_FLAG']."'";
		}
		//投保方式
		if ($post['ONLINE_FLAG'] != '') {
			$where .= " and ONLINE_FLAG = '".$post['ONLINE_FLAG']."'";
		}
		//会员手机
		if ($post['VIP_MOBILE']) {
			$where .= " and VIP_MOBILE = '".$post['VIP_MOBILE']."'";
		}
		//会员卡号
		if ($post['VIP_CARDNO']) {
			$where .= " and VIP_CARDNO = '".$post['VIP_CARDNO']."'";
		}
		//会员身份证号
		if ($post['VIP_IDNO']) {
			$where .= " and VIP_IDNO = '".$post['VIP_IDNO']."'";
		}
		//投保单号
		if ($post['TB_NO']) {
			$where .= " and TB_NO = '".$post['TB_NO']."'";
		}
		//状态
		if($post['TB_FLAG'] != '' && $post['TB_FLAG1'] != '') {
			$where .= " and (TB_FLAG = '".$post['TB_FLAG']."' or TB_FLAG = '".$post['TB_FLAG1']."')";
		}else if($post['TB_FLAG'] != ''){
			$where .= " and TB_FLAG = '".$post['TB_FLAG']."'";
		}else if($post['TB_FLAG1'] != ''){
			$where .= " and TB_FLAG = '".$post['TB_FLAG1']."'";
		}
		//批次号
		if($post['JFB_SECU_REF']) {
			$where .= " and JFB_SECU_REF = '".$post['JFB_SECU_REF']."'";
		}
		//卡类型
		if ($post['VIP_FLAG']) {
			$where .= " and VIP_FLAG = '".$post['VIP_FLAG']."'";
		}

		//计算
		$count = D($this->TTbls)->countTbls($where);
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
			//导出操作
			$list  = D($this->TTbls)->getTblslist($where, '*', $bRow.','.$eRow);
			$xlsname = '养老险明细管理';
			$xlscell = array(
				array('TB_ID',				'投保流水号'),
				array('VIP_NAME',			'会员名称'),
				array('VIP_MOBILE',			'手机号'),
				array('VIP_IDNO',			'身份证号'),
				array('VIP_CARDNO',			'卡号'),
				array('TB_AMT',				'投保金额'),
				array('VIP_EMAIL',			'邮箱'),
				array('TB_FLAG',			'投保结果'),
				array('SECURITY_MAP_ID',	'保险公司'),
				array('TB_NO',				'保单号'),
				array('ONLINE_FLAG',		'投保方式'),		
				array('TB_TIME',			'投保日期'),		
				array('GUISHU_3',			'归属服务中心'),
				array('GUISHU',				'归属'),
				array('TB_DESC',			'备注')
			);		
			$xlsarray = array();
			foreach($list as $val){
				$guishu = get_level_name($val['PARTNER_MAP_ID'], $val['BRANCH_MAP_ID']);
				$gejizuzhi = explode('-', $guishu);
				$xlsarray[] = array(
					'TB_ID'			=>	$val['TB_ID'],
					'VIP_NAME'		=>	$val['VIP_NAME'],
					'VIP_MOBILE'	=>	$val['VIP_MOBILE']."\t",
					'VIP_IDNO'		=>	$val['VIP_IDNO']."\t",
					'VIP_CARDNO'	=>	$val['VIP_CARDNO']."\t",
					'TB_AMT'		=>	setMoney($val['TB_AMT'], '2', '2'),
					'VIP_EMAIL'		=>	$val['VIP_EMAIL'],
					'TB_FLAG'		=>	C('TB_FLAG')[$val['TB_FLAG']],
					'SECURITY_NAME'	=>	get_security_name($val['SECURITY_MAP_ID']),
					'TB_NO'			=>	$val['TB_NO']."\t",
					'ONLINE_FLAG'	=>	C('ONLINE_FLAG')[$val['ONLINE_FLAG']],
					'TB_TIME'		=>	$val['TB_TIME']."\t",
					'GUISHU_3'		=>	$gejizuzhi[2],
					'GUISHU'		=>	$guishu,
					'TB_DESC'		=>	$val['TB_DESC']
				);	
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		$this->display('Public/export');
	}
	
	
	
	/*
	* 养老险投保管理
	**/
	public function persioninsure() {
		$post = I('post');
		if($post['submit'] == "persioninsure"){
			$where = "SECURITY_TYPE = 2";
			$soplv = filter_data('soplv');	//列表查询
			//分公司
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
			//保险公司
			if ($post['SECURITY_MAP_ID']) {
				$where .= " and SECURITY_MAP_ID = '".$post['SECURITY_MAP_ID']."'";
			}
			//状态
			if($post['TB_STATUS'] != '') {
				$where .= " and TB_STATUS = '".$post['TB_STATUS']."'";
			}
			//批次号
			if($post['JFB_SECU_REF']) {
				$where .= " and JFB_SECU_REF = '".$post['JFB_SECU_REF']."'";
			}
			//开始时间
			if ($post['SYSTEM_DATE_A']) {
				$where .= " and TB_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			}
			//结束时间
			if ($post['SYSTEM_DATE_B']) {
				$where .= " and TB_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			}
			//分页
			$count = D($this->TTbbill)->countTbbill($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TTbbill)->getTbbilllist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
		//投保方式(线上,线下)
		$this->assign('ONLINE_FLAG',C('ONLINE_FLAG'));
		//投保状态
		$this->assign('TB_STATUS',C('TB_STATUS_TBBILL'));
		//保险公司
		$res = D($this->MSecurity)->getSecuritylist('','SECURITY_MAP_ID,SECURITY_NAME');
		$this->assign('sec_sel',$res);
		\Cookie::set ('_currentUrl_', 	__SELF__);	
		$this->display();
	}
	
	/*
	* 养老险投保管理	投保确认
	**/
	public function persioninsure_submit() {

		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$home = session("HOME");
		$where = 'TBBILL_ID = "'.$id.'"';
		$info  = D($this->TTbbill)->findTbbill($where, 'PARTNER_MAP_ID,JFB_SECU_REF,TB_STATUS' );
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if ($info['TB_STATUS'] !=4) {
			$this->wrong("当前状态无法完成此操作！");
		}

		//得到所有当前批次号对应的数据 
		$where1 = 'JFB_SECU_REF = '.$info['JFB_SECU_REF'].' and SECURITY_TYPE = 2 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
		//数据隔离
		if(!filter_auth($home['BRANCH_MAP_ID'])){
			$pids = get_plv_childs($info['PARTNER_MAP_ID'],1);
			$where1.= ' and PARTNER_MAP_ID in ('.$pids.')';
		}
		$res = D($this->TTbls)->getTblslist($where1.' and TB_FLAG = 4');
		foreach ($res as $key => $value) {
			//获得会员数据
			$vipdata = D($this->GVip)->findVip('VIP_ID = '.$value['VIP_ID'], $field='*');
			$vipdata['SEX_NAME'] = $vipdata['VIP_SEX'] ? '男' : '女';
			$vipdata['VIP_SEX'] = $vipdata['VIP_SEX'] ? 1 : 2;
			$vipdata['VIP_OLD'] = getAgeByID($value['VIP_IDNO']) >= 45 ? getAgeByID($value['VIP_IDNO'])+1 : 45;
			$this->assign('vipdata',$vipdata);
			$this->assign('data',$value);
			//查看该保险号是否已经投保
			//$is_insure = D($this->TTbls)->findTbls("VIP_ID = ".$value['VIP_ID']." and SECURITY_TYPE = 2 and TB_FLAG = 0 and ONLINE_FLAG = 0", $field='*');

			if($value['TB_NO']!= '' && substr($value['TB_NO'], 0, 1) != 'Y'){
				//发送XML数据(加密发送XML报文)
				$xmldata = $this->fetch('endowment_insurance_2');		//养老保险(续保)
				$url = C('INSURE_URL2');								//养老险续保链接
			}else{
				$xmldata = $this->fetch('endowment_insurance_1');		//养老保险(投保)
				$url = C('INSURE_URL1');								//养老险续保链接
			}
			//发送并接收返回值
			$xml_arr = $this->post_xml($xmldata,$url);
			if ($xml_arr) {
				$backdata = array(
					'TransRefGUID' 		=> $xml_arr['TXLifeResponse']['TransRefGUID'],
					'ResultCode' 		=> $xml_arr['TXLifeResponse']['TransResult']['ResultCode'],
					'ResultInfoDesc'	=> $xml_arr['TXLifeResponse']['TransResult']['ResultInfo']['ResultInfoDesc'],
					'PolNumber' 		=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['PolNumber'],
					'HOAppFormNumber' 	=> $xml_arr['TXLifeResponse']['OLifE']['Holding']['Policy']['ApplicationInfo']['HOAppFormNumber'],
					'BankCode' 			=> $xml_arr['TXLifeResponse']['OLifEExtension']['BankCode']
				);
				if ($backdata['ResultCode'] == 'Success') {
					//如果成功更新数据状态
					$TB_FLAG = array(
						'TB_NO'		=>	$backdata['PolNumber'] ? $backdata['PolNumber'] : $backdata['HOAppFormNumber'],
						'TB_FLAG'	=>	0,
						'TB_TIME'	=>	date('YmdHis'),
						'TB_DESC'	=>	$backdata['ResultInfoDesc']
					);
					//修改vip表
					if(strlen($vipdata['RES']) == '80'){
						$yw_no = substr($vipdata['RES'],0,40);					//养老险单号
						$yl_no = setStrzero($TB_FLAG['TB_NO'], 40, ' ','r');	//意外险单号
						$vip_res = $yw_no.$yl_no;
					}else{
						$yw_no = setStrzero('', 40, ' ','r');
						$yl_no = setStrzero($TB_FLAG['TB_NO'], 40, ' ','r');
						$vip_res = $yw_no.$yl_no;
					}
					D($this->GVip)->updateVip('VIP_ID = '.$value['VIP_ID'], array('RES' => $vip_res));
				}else{
					//如果不成功更新为失败
					$TB_FLAG = array(
						'TB_FLAG'	=>	1,
						'TB_DESC'	=>	$backdata['ResultInfoDesc']
					);
				}
				//更新保单号对应该的状态值(投保确认的数据必须是预免卡,意外险,待确认,参保, 在线投保状态)
				$where = 'TB_ID = '.$value['TB_ID'].' and SECURITY_TYPE = 2 and TB_FLAG = 4 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
				$t_res = D($this->TTbls)->updateTbls($where, $TB_FLAG);
			}
			//更新现有的，投保数据（将所有意外险投保失败的数据，改为过期状态）
			$upwhere = "VIP_ID = '".$value['VIP_ID']."' and TB_ID < ".$value['TB_ID']." and SECURITY_TYPE = 2 and TB_FLAG = 1 and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0";
			D($this->TTbls)->updateTbls($upwhere, array('TB_FLAG' => 6));
		}
		$tb_money_succ = D($this->TTbls)->sumTbls($where1.' and TB_FLAG = 0', 'TB_AMT');			//投保金额
		$tb_num_succ = D($this->TTbls)->countTbls($where1.' and TB_FLAG = 0', 'TB_CNT');			//投保人数

		//更新所有批次号对应该的状态值
		$up_tbbill = array(
			'TB_FLAG'		=> 0, 				//投保结果(0：正常 1：未确认 2：失败)
			'TB_STATUS'		=> 0, 				//状态标识(0：已投保 1：见审批表)
			'TB_AMT_SUCC'	=> $tb_money_succ, 	//成功投保金额
			'TB_CNT_SUCC'	=> $tb_num_succ		//成功投保人数
		);
		$g_res = D($this->TTbbill)->updateTbbill('TBBILL_ID = "'.$id.'"' ,$up_tbbill);
		if (!$g_res) {
			$this->wrong('提交失败');
		}
		$this->right('提交成功', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}

	/*
	* 养老险投保管理	详情
	**/
	public function persioninsure_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$where = 'TBBILL_ID = "'.$id.'"';
		$info  = D($this->TTbbill)->findTbbill($where);
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}	
		$this->assign ('info', $info);
		$this->display();
	}
	
	/*
	* 投保接口	详情
	**/
	public function post_xml($xmldata,$url,$flag=1) {
		//发送XML数据(加密发送XML报文)
		$key = C('INSURE_KEY');
		$outstr = iconv("UTF-8","gbk",$xmldata);
		$str = bin2hex(encrypt($outstr, $key));
		$xml_res = send_xml($str,$url,'gbk');
		if ($flag) {
			$log_filename = "./Public/file/apilog/insure_".date('Y_m_d').".log";
		}else{
			$log_filename = "./Public/file/apilog/insure_custom_".date('Y_m_d').".log";
		}
		
        file_put_contents($log_filename, date('Y-m-d H:i:s')."\r\n".$outstr."\r\n", FILE_APPEND);

		//接受返回数据(解密返回报文)
		$str4=decrypt(pack("H*",$xml_res), $key);
       	file_put_contents($log_filename, date('Y-m-d H:i:s')."\r\n".$str4."\r\n", FILE_APPEND);
		$xml_arr = get_xml($str4);
		if ($xml_arr) {
			return $xml_arr;
		}
		return false;

		
		//转换为simplexml对象(测试方法)
		/*$str4=decrypt(pack("H*",$this->test_backdata()), 'ZPJ#I&JC');
		$xml_arr = get_xml($str4);
		if ($xml_arr) {
			return $xml_arr;
		}
		return false;*/
	}


	//意外险手动投保
	public function custom_post1(){
		//发送XML数据(加密发送XML报文)
		$xmldata = '<?xml version="1.0" encoding="gbk"?>
<TXLife>
    <TXLifeRequest>
        <TransRefGUID>347785</TransRefGUID>
        <TransType tc="104">Policy Change</TransType>
        <TransMode tc="51">Additional Premium</TransMode>
        <OLifE>
            <Holding>
                <Policy>
                    <PolNumber>005602280593158</PolNumber>
                </Policy>
                <Change>
                    <ChangeType tc="436">万能险追加保费</ChangeType>
                    <AdditionalPremium>34.00</AdditionalPremium>
                    <ProposerType tc="1">投保人</ProposerType>
                    <Proposer>
                        <FullName>郭振宇</FullName>
                        <GovtIDTC tc="1">中华人民共和国身份证</GovtIDTC>
                        <GovtID>232331197211263212</GovtID>
                        <Phone>
                            <PhoneTypeCode tc="12">12</PhoneTypeCode>
                            <DialNumber>13614602789</DialNumber>
                        </Phone>
                        <BankName>00030000000000</BankName>
                        <BankAccountNumber>33001617027053017852</BankAccountNumber>
                        <BankAcctType tc="7">普通卡</BankAcctType>
                        <AcctHolderName>浙江积分宝控股有限公司消费养老保险专户</AcctHolderName>
                        <AcctGovtIDTC tc="1">中华人民共和国身份证</AcctGovtIDTC>
                        <AcctGovtID>330725196710076114</AcctGovtID>
                        <AcctPubPri tc="2">对公账户</AcctPubPri>
                    </Proposer>
                    <ApplyMethod tc="9">在线自助</ApplyMethod>
                    <PaymentMethod tc="3">客户银行转账</PaymentMethod>
                    <ApplyDate />
                    <EffDate>2016-09-05 00:00:00</EffDate>
                </Change>
            </Holding>
        </OLifE>
        <OLifEExtension VendorCode="1">
            <CarrierCode>PICC</CarrierCode>
            <Branch>1940303</Branch>
            <AgencyCode>19410215</AgencyCode>
            <Teller></Teller>
            <BankCode>ZJJFB</BankCode>
        </OLifEExtension>
    </TXLifeRequest>
</TXLife>';

		//发送并接收返回值
	//	$url = C('INSURE_URL1');	//意外险投保链接
		$url = C('INSURE_URL2');	
		$xml_arr = $this->post_xml($xmldata,$url);
		dump($xml_arr);
	}
}
