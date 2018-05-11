<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @CRYSTAL 商户立减配额设置
// +----------------------------------------------------------------------
class SminusController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
	}
	/*
	 * 列表 
	 **/
	public function slist() {
		 $post = I('post');
		if($post['submit'] == "slist"){
			$where = "1=1";
			//平台商户ID
			if($post['SHOP_MAP_ID']) {
				$where .= " and sh.SHOP_MAP_ID = '".$post['SHOP_MAP_ID']."'";
			}
			//平台商户名称
			if($post['SHOP_NAME']) {
				$where .= " and sh.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//分页
			$sminusModel = M('sminus');
			$count = $sminusModel->alias('m')
						->join('a_shop sh on sh.SHOP_MAP_ID=m.SHOP_MAP_ID','LEFT')
						->where($where)->count();
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = $sminusModel->alias('m')
						->join('a_shop sh on sh.SHOP_MAP_ID=m.SHOP_MAP_ID','LEFT')
						->where($where)->limit($p->firstRow.','.$p->listRows)->select();
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 	__SELF__);	
		$this->display();
	}
	/*
	* 添加
	**/
	public function add() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "add"){
			if (empty($post['SHOP_MAP_ID']) || empty($post['NORMAL_AMT']) || empty($post['VIP_AMT'])) {
				$this->wrong('请填写必填项！');
			}
			//判断是否重复
			$res  = M('sminus')->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			$shop  = M('shop')->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			if(empty($shop)){
				$this->wrong('平台商户不存在！');
			}
			if ($res) {
				$this->wrong('当前平台商户号已存在配额, 不能重复添加！');
			}
			//转换为分
			$normal_amt = $post['NORMAL_AMT'] * 100;
			$total_normal_amt = $normal_amt;
			$vip_amt = $post['VIP_AMT'] * 100;
			$total_vip_amt = $vip_amt;
			$key = C('MACKEY');//key
			$text = getLD($post['SHOP_MAP_ID']).getLD($normal_amt).
				getLD($total_normal_amt).getLD($vip_amt).
				getLD($total_vip_amt);//拼接
			$mac = strtoupper(hash_hmac('md5',$text,$key));
			//组装数据
			$data = array(
				'SHOP_MAP_ID'		=> $post['SHOP_MAP_ID'],
				'STATUS'			=> $post['STATUS'],
				'TOTAL_NORMAL_AMT'	=> $total_normal_amt,
				'NORMAL_AMT'		=> $normal_amt,
				'TOTAL_VIP_AMT'		=> $total_vip_amt,
				'VIP_AMT'			=> $vip_amt,
				'MAC'				=> $mac,
				'SYSTEM_TIME'		=> date('YmdHis'),
				'CREATE_USERID'		=> $home['USER_ID'],
				'CREATE_USERNAME'	=> $home['USER_NAME'],
			);
			$res = M('sminus')->add($data);
			if (!$res) {
				$this->wrong('添加失败！');
			}
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->display();
	}	
	/*
	* 修改
	**/
	public function edit() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "edit"){
			if (empty($post['SHOP_MAP_ID']) || empty($post['NORMAL_AMT']) || empty($post['VIP_AMT'])) {
				$this->wrong('请填写必填项！');
			}
			//判断是否重复
			$minus  = M('sminus')->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			$shop  = M('shop')->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->find();
			if(empty($shop)){
				$this->wrong('平台商户不存在！');
			}
			if (empty($minus)) {
				$this->wrong('当前平台商户未参与配额！');
			}
			$normal = $post['NORMAL_AMT'] - $minus['NORMAL_AMT'];
			$vip = $post['VIP_AMT'] - $minus['VIP_AMT'];
			//转换为分
			$normal_amt = ($minus['NORMAL_AMT'] + $normal) * 100;
			$total_normal_amt = ($minus['TOTAL_NORMAL_AMT'] + $normal) * 100;
			$vip_amt = ($minus['VIP_AMT'] + $vip) * 100;
			$total_vip_amt = ($minus['TOTAL_VIP_AMT'] + $vip) * 100;
			$key = C('MACKEY');//key
			$text = getLD($post['SHOP_MAP_ID']).getLD($normal_amt).
				getLD($total_normal_amt).getLD($vip_amt).
				getLD($total_vip_amt);//拼接
			$mac = strtoupper(hash_hmac('md5',$text,$key));
			//组装数据
			$data = array(
				'SHOP_MAP_ID'		=> $post['SHOP_MAP_ID'],
				'STATUS'			=> $post['STATUS'],
				'TOTAL_NORMAL_AMT'	=> $total_normal_amt,
				'NORMAL_AMT'		=> $normal_amt,
				'TOTAL_VIP_AMT'		=> $total_vip_amt,
				'VIP_AMT'			=> $vip_amt,
				'MAC'				=> $mac,
				'SYSTEM_TIME'		=> date('YmdHis'),
				'CREATE_USERID'		=> $home['USER_ID'],
				'CREATE_USERNAME'	=> $home['USER_NAME'],
			);
			$res = M('sminus')->where(array('SHOP_MAP_ID'=>$post['SHOP_MAP_ID']))->save($data);
			if (!$res) {
				$this->wrong('修改失败！');
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$sminusModel = M('sminus');
		$info  = $sminusModel->where(array('SHOP_MAP_ID'=>$id))->find();
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}		
		$this->assign('info', $info);
		$this->display('add');
	}	
}
