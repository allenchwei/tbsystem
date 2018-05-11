<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MBid	银行联行号信息
// +----------------------------------------------------------------------
class MBidModel extends Model{
	
	function __construct(){
		$this->bid	= "bid";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countBid($where) {
		return M($this->bid)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getBidlist($where, $field='*', $limit, $order='BANK_BID desc') {
		return M($this->bid)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findBid($where, $field='*') {
		return M($this->bid)->where($where)->field($field)->find();
	}
}
