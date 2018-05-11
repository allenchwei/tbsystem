<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	TQbls	钱宝流水
// +----------------------------------------------------------------------
class TQblsModel extends Model{
	
	function __construct(){		
		$this->qbls = M('qbls', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countQbls($where) {
		return $this->qbls->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getQblslist($where, $field='*', $limit, $order='QB_ID desc') {
		return $this->qbls->where($where)
				->field($field.',DATE_FORMAT(TRADE_TIME,"%Y-%m-%d") AS TRADE_TIME,DATE_FORMAT(ACCOUNT_TIME,"%Y-%m-%d") AS ACCOUNT_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
}
