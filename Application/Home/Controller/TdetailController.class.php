<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  流水
// +----------------------------------------------------------------------
class TdetailController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->TTrace 		= 'TTrace';
		$this->MTrans 		= 'MTrans';
		$this->MHost 		= 'MHost';
		$this->TJfbls 		= 'TJfbls';
		$this->TKfls 		= 'TKfls';
		$this->TDap 		= 'TDap';
	}
		
	/*
	* 商户明细账查询
	**/
	public function shopacc() {
		$post = I('post');
		if($post['submit'] == "shopacc"){
			$where = "d.SUBJECT_CODE = '20201' and 'G' != (SELECT LEFT(d.ACCT_NO, 1))";
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and d.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and d.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}
			//分录金额	开始
			if($post['ACCT_AMT_A']) {
				$where .= " and d.ACCT_AMT >= '".setMoney($post['ACCT_AMT_A'], '2')."'";
			}
			//分录金额	结束
			if($post['ACCT_AMT_B']) {
				$where .= " and d.ACCT_AMT <= '".setMoney($post['ACCT_AMT_B'], '2')."'";
			}
			//商户名称
			if($post['SHOP_NAMEAB']) {
				$where .= " and t.SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
			}
			//归属
			$getlevel = get_level_val('plv');
			$post['SBRANCH_MAP_ID']  = $getlevel['bid'];
			$post['SPARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['SBRANCH_MAP_ID']){
				$where .= " and t.SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
			}
			if($post['SPARTNER_MAP_ID']){
				$pids = get_plv_childs($post['SPARTNER_MAP_ID']);
				$where .= " and t.SPARTNER_MAP_ID in (".$pids.")";
			}
			
			//分页
			$count = D($this->TDap)->countDap($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TDap)->getDaplist($where, 'd.*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	
	
	/*
	* 会员明细账查询
	**/
	public function vipacc() {		
		$post = I('post');
		if($post['submit'] == "vipacc"){
			$where = "d.SUBJECT_CODE = '20101' and 'G' != (SELECT LEFT(d.ACCT_NO, 1))";
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and d.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and d.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}			
			//分录金额	开始
			if($post['ACCT_AMT_A']) {
				$where .= " and d.ACCT_AMT >= '".setMoney($post['ACCT_AMT_A'], '2')."'";
			}
			//分录金额	结束
			if($post['ACCT_AMT_B']) {
				$where .= " and d.ACCT_AMT <= '".setMoney($post['ACCT_AMT_B'], '2')."'";
			}
			//会员手机号
			if($post['VIP_CARDNO']) {
				$where .= " and t.VIP_CARDNO = '".$post['VIP_CARDNO']."'";
			}
			//归属
			$getlevel = get_level_val('plv');
			$post['SBRANCH_MAP_ID']  = $getlevel['bid'];
			$post['SPARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['SBRANCH_MAP_ID']){
				$where .= " and t.SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
			}
			if($post['SPARTNER_MAP_ID']){
				$pids = get_plv_childs($post['SPARTNER_MAP_ID']);
				$where .= " and t.SPARTNER_MAP_ID in (".$pids.")";
			}
			
			//分页
			$count = D($this->TDap)->countDap($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TDap)->getDaplist($where, 'd.*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	
	
	/*
	* 分公司明细账查询
	**/
	public function branchacc() {
		$post = I('post');
		if($post['submit'] == "branchacc"){
			$where = "d.SUBJECT_CODE = '20204' and 'G' != (SELECT LEFT(d.ACCT_NO, 1))";
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and d.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and d.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}			
			//分录金额	开始
			if($post['ACCT_AMT_A']) {
				$where .= " and d.ACCT_AMT >= '".setMoney($post['ACCT_AMT_A'], '2')."'";
			}
			//分录金额	结束
			if($post['ACCT_AMT_B']) {
				$where .= " and d.ACCT_AMT <= '".setMoney($post['ACCT_AMT_B'], '2')."'";
			}
			//归属
			$getlevel = get_level_val('plv');
			$post['SBRANCH_MAP_ID']  = $getlevel['bid'];
			$post['SPARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['SBRANCH_MAP_ID']){
				$where .= " and t.SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
			}
			if($post['SPARTNER_MAP_ID']){
				$pids = get_plv_childs($post['SPARTNER_MAP_ID']);
				$where .= " and t.SPARTNER_MAP_ID in (".$pids.")";
			}
			
			//分页
			$count = D($this->TDap)->countDap($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TDap)->getDaplist($where, 'd.*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	
	
	/*
	* 合作伙伴明细账查询
	**/
	public function partneracc() {
		$post = I('post');
		if($post['submit'] == "partneracc"){
			$where = "d.SUBJECT_CODE = '20203' and 'G' != (SELECT LEFT(d.ACCT_NO, 1))";
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and d.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and d.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}			
			//分录金额	开始
			if($post['ACCT_AMT_A']) {
				$where .= " and d.ACCT_AMT >= '".setMoney($post['ACCT_AMT_A'], '2')."'";
			}
			//分录金额	结束
			if($post['ACCT_AMT_B']) {
				$where .= " and d.ACCT_AMT <= '".setMoney($post['ACCT_AMT_B'], '2')."'";
			}
			//归属
			$getlevel = get_level_val('plv');
			$post['SBRANCH_MAP_ID']  = $getlevel['bid'];
			$post['SPARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['SBRANCH_MAP_ID']){
				$where .= " and t.SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
			}
			if($post['SPARTNER_MAP_ID']){
				$pids = get_plv_childs($post['SPARTNER_MAP_ID']);
				$where .= " and t.SPARTNER_MAP_ID in (".$pids.")";
			}
			
			//分页
			$count = D($this->TDap)->countDap($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TDap)->getDaplist($where, 'd.*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	
	
	/*
	* 超扣明细账查询
	**/
	public function exceedacc() {
		$post = I('post');
		if($post['submit'] == "exceedacc"){
			$where = "d.SUBJECT_CODE = '60102' and 'G' != (SELECT LEFT(d.ACCT_NO, 1))";
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and d.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and d.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}			
			//分录金额	开始
			if($post['ACCT_AMT_A']) {
				$where .= " and d.ACCT_AMT >= '".setMoney($post['ACCT_AMT_A'], '2')."'";
			}
			//分录金额	结束
			if($post['ACCT_AMT_B']) {
				$where .= " and d.ACCT_AMT <= '".setMoney($post['ACCT_AMT_B'], '2')."'";
			}
			//商户名称
			if($post['SHOP_NAMEAB']) {
				$where .= " and t.SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
			}
			//归属
			$getlevel = get_level_val('plv');
			$post['SBRANCH_MAP_ID']  = $getlevel['bid'];
			$post['SPARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['SBRANCH_MAP_ID']){
				$where .= " and t.SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
			}
			if($post['SPARTNER_MAP_ID']){
				$pids = get_plv_childs($post['SPARTNER_MAP_ID']);
				$where .= " and t.SPARTNER_MAP_ID in (".$pids.")";
			}
			
			//分页
			$count = D($this->TDap)->countDap($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TDap)->getDaplist($where, 'd.*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	
	
	/*
	* 收单分润明细账查询
	**/
	public function beyondacc() {
		$post = I('post');
		if($post['submit'] == "beyondacc"){
			$where = "d.SUBJECT_CODE = '60101' and 'G' != (SELECT LEFT(d.ACCT_NO, 1))";
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and d.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and d.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}			
			//分录金额	开始
			if($post['ACCT_AMT_A']) {
				$where .= " and d.ACCT_AMT >= '".setMoney($post['ACCT_AMT_A'], '2')."'";
			}
			//分录金额	结束
			if($post['ACCT_AMT_B']) {
				$where .= " and d.ACCT_AMT <= '".setMoney($post['ACCT_AMT_B'], '2')."'";
			}
			//归属
			$getlevel = get_level_val('plv');
			$post['SBRANCH_MAP_ID']  = $getlevel['bid'];
			$post['SPARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['SBRANCH_MAP_ID']){
				$where .= " and t.SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
			}
			if($post['SPARTNER_MAP_ID']){
				$pids = get_plv_childs($post['SPARTNER_MAP_ID']);
				$where .= " and t.SPARTNER_MAP_ID in (".$pids.")";
			}
			
			//分页
			$count = D($this->TDap)->countDap($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TDap)->getDaplist($where, 'd.*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	
	
	/*
	* 通道明细账查询
	**/
	public function hostacc() {
		$post = I('post');
		if($post['submit'] == "hostacc"){
			$where = "d.SUBJECT_CODE = '10202' and 'G' != (SELECT LEFT(d.ACCT_NO, 1))";
			//交易日期	开始
			if($post['SYSTEM_DATE_A']) {
				$where .= " and d.SYSTEM_DATE >= '".date('Ymd',strtotime($post['SYSTEM_DATE_A']))."'";
			}
			//交易日期	结束
			if($post['SYSTEM_DATE_B']) {
				$where .= " and d.SYSTEM_DATE <= '".date('Ymd',strtotime($post['SYSTEM_DATE_B']))."'";
			}			
			//分录金额	开始
			if($post['ACCT_AMT_A']) {
				$where .= " and d.ACCT_AMT >= '".setMoney($post['ACCT_AMT_A'], '2')."'";
			}
			//分录金额	结束
			if($post['ACCT_AMT_B']) {
				$where .= " and d.ACCT_AMT <= '".setMoney($post['ACCT_AMT_B'], '2')."'";
			}
			//商户名称
			if($post['SHOP_NAMEAB']) {
				$where .= " and t.SHOP_NAMEAB like '%".$post['SHOP_NAMEAB']."%'";
			}
			//归属
			$getlevel = get_level_val('plv');
			$post['SBRANCH_MAP_ID']  = $getlevel['bid'];
			$post['SPARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['SBRANCH_MAP_ID']){
				$where .= " and t.SBRANCH_MAP_ID = '".$post['SBRANCH_MAP_ID']."'";
			}
			if($post['SPARTNER_MAP_ID']){
				$pids = get_plv_childs($post['SPARTNER_MAP_ID']);
				$where .= " and t.SPARTNER_MAP_ID in (".$pids.")";
			}
			
			//分页
			$count = D($this->TDap)->countDap($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TDap)->getDaplist($where, 'd.*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
}