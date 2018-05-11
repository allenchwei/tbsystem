<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  流水
// +----------------------------------------------------------------------
class WithdrawController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
	}
	
	/*
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
				$where .= " and CREATE_TIME >= '".date('Ymd',strtotime($post['CREATE_TIME_A']))."000000'";
			}
			//提现日期	结束
			if($post['CREATE_TIME_B']) {
				$where .= " and CREATE_TIME <= '".date('Ymd',strtotime($post['CREATE_TIME_B']))."235959'";
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
		// echo $lapModel->getLastSql();
		$withdraw_status = array(
			0 => '成功',
			1 => '处理中',
			2 => '失败'
		);
		$this->assign('status', 		$withdraw_status );	//提现标志
		$this->assign('info', 				$info);
		$this->assign('lap', 				$lap);
		$this->display();
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
			//组装数据
			$data = array(
				'STATUS' => $post['CHECK_POINT'],
				'NOTE' => $post['NOTE'],
				'UPDATE_TIME' => date('YmdHis'),
				'OPERATE_USERID' => $home['USER_ID'],
				'OPERATE_USERNAME' => $home['USER_NAME']
			);
			$info = $withdrawModel->where(array('ID'=>$post['ID']))->find();
			$lapModel = M('lap');
			$lap = $lapModel->where(array('SHOP_MAP_ID'=>$info['SHOP_MAP_ID']))->find();
			//验证MAC押码
			$appendStr = getLD($lap['SHOP_MAP_ID']).getLD($lap['ACCT_TBAL']).getLD($lap['ACCT_VBAL']);
			$mac = strtoupper(hash_hmac('md5', $appendStr, MACKEY));
			if($mac != $lap['MAC']){
				$this->wrong("商户账上金额有误！");
			}
			
			$res = $withdrawModel->where(array('ID'=>$post['ID']))->save($data);
			
			if($post['CHECK_POINT'] == 2){
				$ACCT_TBAL = $lap['ACCT_TBAL'];
				$ACCT_VBAL = $lap['ACCT_VBAL']+$info['AMOUNT'];
				$appendStr = getLD($lap['SHOP_MAP_ID']).getLD($ACCT_TBAL).getLD($ACCT_VBAL);
			}else{
				$ACCT_TBAL = $lap['ACCT_TBAL']-$info['AMOUNT'];
				$ACCT_VBAL = $lap['ACCT_VBAL'];
				$appendStr = getLD($lap['SHOP_MAP_ID']).getLD($ACCT_TBAL).getLD($ACCT_VBAL);
			}
			$mac = strtoupper(hash_hmac('md5', $appendStr, MACKEY));
			//组装数据
			$lapData = array(
				'ACCT_TBAL' => $ACCT_TBAL,
				'ACCT_VBAL' => $ACCT_VBAL,
				'MAC' => $mac
			);
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
}
