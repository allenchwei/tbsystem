<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MShop	商户基本信息管理
// +----------------------------------------------------------------------
class MHuiCaiModel extends Model{
	
	function __construct(){
		$this->shop   = "shop";
		$this->partner= "partner";
		$this->branch = "branch";
		$this->sposreq= "sposreq";
	}

	/*
	* 获取统计数量[优化]
	* @post:
	**/
	public function countNewShop($where) {
		return M($this->shop)->where($where)->count();
	}

	/*
	* 获取列表[优化]
	* @post:
	**/
	public function getNewShoplist($where, $field='*', $limit, $order='SHOP_MAP_ID desc') {
		$list = M($this->shop)->where($where)
				->field($field.',DATE_FORMAT(CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
		if ($list) {
			foreach ($list as $kk => $val) {
				$blist = M($this->branch)->where('BRANCH_MAP_ID = '.$val['BRANCH_MAP_ID'])->field('BRANCH_NAME')->find();
				$plist = M($this->partner)->where('PARTNER_MAP_ID = '.$val['PARTNER_MAP_ID'])->field('PARTNER_NAME')->find();
				$list[$kk]['BRANCH_NAME']  = $blist['BRANCH_NAME'];
				$list[$kk]['PARTNER_NAME'] = $plist['PARTNER_NAME'];
				$splist = M('sposreq')->where('SHOP_MAP_ID = '.$val['SHOP_MAP_ID'])->field('INSTALL_FLAG')->select();
				foreach ($splist as $key => $value) {
					if ($value['INSTALL_FLAG'] == 0) {
						$list[$kk]['INSTALL_FLAG'] =  0 ;
						break;	//跳出循环
					}
				}
			}
		}
		return $list;
	}
}