<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MBank	银行
// +----------------------------------------------------------------------
class MBankModel extends Model{
	
	function __construct(){
		$this->sbact	= "sbact";
		$this->partner	= "partner";
		$this->shop		= "shop";
	}
	
	/*
	* 获取列表
	* @post:	asc 周定
	**/
	public function getBanklist($where, $field='*', $limit, $order='SHOP_MAP_ID desc') {
		return M($this->sbact)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findBank($where, $field='*') {
		return M($this->sbact)->where($where)->field($field)->limit(0,50)->find();
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countBank($where) {
		return M($this->sbact)
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	// public function getBanklist($where, $field='*', $limit, $order='SHOP_MAP_ID desc') {
	// 	return M($this->sbact)
	// 			->join(DB_PREFIX.$this->shop.' on sbact.SHOP_MAP_ID = shop.SHOP_MAP_ID')
	// 			->where($where)
	// 			->field($field)
	// 			->limit($limit)
	// 			->order($order)
	// 			->select();
	// }
}
