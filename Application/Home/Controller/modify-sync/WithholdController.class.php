<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  代扣管理
// +----------------------------------------------------------------------
class WithholdController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->TDkls 	= 'TDkls';
		$this->TTrace 	= 'TTrace';
	}
	
	/*
	* 代扣流水
	**/
	public function dkls() {
		$post = I('post');
		if($post['submit'] == "dkls"){
			$where = "DK_ID != ''";
			//代扣批次
			if($post['JFB_DK_REF']) {
				$where .= " and JFB_DK_REF = '".$post['JFB_DK_REF']."'";
			}
			//代扣公司
			if($post['DKCO_MAP_ID']) {
				$where .= " and DKCO_MAP_ID = '".$post['DKCO_MAP_ID']."'";
			}
			//发起日期
			if($post['DK_DATE']) {
				$where .= " and DK_DATE = '".date('Ymd',strtotime($post['DK_DATE']))."'";
			}
			//代扣结果
			if($post['DK_FLAG']) {
				$where .= " and DK_FLAG = '".$post['DK_FLAG']."'";
			}
			//总笔数 开始
			if($post['TRANS_CNT_A']) {
				$where .= " and TRANS_CNT_A >= '".$post['TRANS_CNT_A']."'";
			}
			//总笔数 结束
			if($post['TRANS_CNT_B']) {
				$where .= " and TRANS_CNT_B <= '".$post['TRANS_CNT_B']."'";
			}
			//总金额 开始
			if($post['TRANS_AMT_A']) {
				$where .= " and TRANS_AMT_A >= '".setMoney($post['TRANS_AMT_A'],2,2)."'";
			}
			//总金额 结束
			if($post['TRANS_AMT_B']) {
				$where .= " and TRANS_AMT_B <= '".setMoney($post['TRANS_AMT_B'],2,2)."'";
			}
			//分页
			$count = D($this->TDkls)->countDkls($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->TDkls)->getDklslist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('dk_flag',			C('DK_FLAG'));	//代扣到帐标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	/*
	* 代扣流水
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
		//获取代扣明细
		$dklist  = D($this->TTrace)->getTracelist("SYSTEM_NEWREF >= '".$info['SYSTEM_REF_BEGIN']."' and SYSTEM_NEWREF <= '".$info['SYSTEM_REF_END']."'", 't.TRANS_NAME,t.VIP_ID,t.VIP_CARDNO,t.TRANS_AMT,t.TRACE_STATUS,j.PLAT_FEE,j.CON_FEE');		
		
		$this->assign('dk_flag',			C('DK_FLAG'));			//代扣到帐标志
		$this->assign('trace_status', 		C('TRACE_STATUS') );	//流水标志
		$this->assign ('info', 				$info);
		$this->assign ('dklist', 			$dklist);
		$this->display();
	}
}