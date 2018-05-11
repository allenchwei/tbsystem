<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MBank	银行
// +----------------------------------------------------------------------
class MSbankModel extends Model{
	
	function __construct(){
		$this->sbank	= "sbank";
	}
	/*
	* 获取列表
	* @post:	asc 周定
	**/
	public function getBanklist($where, $field='*', $limit, $order='SHOP_MAP_ID desc') {
		return M($this->sbank)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findBank($where, $field='*') {
		return M($this->sbank)->where($where)->field($field)->limit(0,50)->find();
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countBank($where) {
		return M($this->sbank)->where($where)->count();
	}
}
?>