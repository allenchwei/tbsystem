<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	TDap	交易流水
// +----------------------------------------------------------------------
class TDapModel extends Model{
	
	function __construct(){		
		$this->dap 		= M('dap', DB_PREFIX_TRA, DB_DSN_TRA);
		$this->trace	= "trace";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countDap($where) {
		return $this->dap->alias('d')
				->join(DB_PREFIX_TRA.$this->trace.' t on d.SYSTEM_REF = t.SYSTEM_REF', 'LEFT')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getDaplist($where, $field='d.*', $limit, $order='d.ACCT_TRACE desc') {
		return $this->dap->alias('d')
				->join(DB_PREFIX_TRA.$this->trace.' t on d.SYSTEM_REF = t.SYSTEM_REF', 'LEFT')
				->where($where)
				->field($field.',DATE_FORMAT(d.SYSTEM_DATE,"%Y-%m-%d") AS SYSTEM_DATE, DATE_FORMAT(CONCAT(d.SYSTEM_DATE, LPAD(d.SYSTEM_TIME,6,"0")),"%H:%i:%s") AS SYSTEM_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findDap($where, $field='*') {
		return $this->dap->where($where)
				->field($field)
				->find();
	}
}
