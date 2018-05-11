<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	TTrace	交易流水
// +----------------------------------------------------------------------
class TTraceModel extends Model{
	
	function __construct(){		
		$this->trace 	= M('trace', DB_PREFIX_TRA, DB_DSN_TRA);
		$this->jfbls 	= "jfbls";
		$this->kfls 	= "kfls";
	}
	
	/*
	* 获取统计数量	新
	* @post:
	**/
	public function countNewsTrace($where) {
		return $this->trace->where($where)->count('TRACE_ID');
	}
	
	/*
	* 获取列表		新
	* @post:
	**/
	public function getNewsTracelist($where, $field='*', $limit, $order='SYSTEM_DATE desc, SYSTEM_TIME desc') {
		$res = $this->trace->where($where)
				->field($field.',DATE_FORMAT(SYSTEM_DATE,"%Y-%m-%d") AS SYSTEM_DATE, DATE_FORMAT(CONCAT(SYSTEM_DATE, LPAD(SYSTEM_TIME,6,"0")),"%H:%i:%s") AS SYSTEM_TIME, DATE_FORMAT(POS_DATE,"%Y-%m-%d") AS POS_DATE, DATE_FORMAT(CONCAT(POS_DATE, LPAD(POS_TIME,6,"0")),"%H:%i:%s") AS POS_TIME')

				->limit($limit)
				->order($order)
				->select();
		return $res;
	}
	
	//--------------------------------------------------------------------
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countTrace($where) {
		return $this->trace->alias('t')
				->join(DB_PREFIX_TRA.$this->jfbls.' j on t.SYSTEM_REF = j.SYSTEM_REF', 'LEFT')
				->join(DB_PREFIX_TRA.$this->kfls.' k on t.SYSTEM_REF = k.SYSTEM_REF', 'LEFT')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getTracelist($where, $field='t.*', $limit, $order='t.SYSTEM_DATE desc, t.SYSTEM_TIME desc') {
		 $this->trace->alias('t')
			->join(DB_PREFIX_TRA.$this->jfbls.' j on t.SYSTEM_REF = j.SYSTEM_REF', 'LEFT')
			->join(DB_PREFIX_TRA.$this->kfls.' k on t.SYSTEM_REF = k.SYSTEM_REF', 'LEFT')
			->where($where)
			->field($field.',DATE_FORMAT(t.SYSTEM_DATE,"%Y-%m-%d") AS SYSTEM_DATE, DATE_FORMAT(CONCAT(t.SYSTEM_DATE, LPAD(t.SYSTEM_TIME,6,"0")),"%H:%i:%s") AS SYSTEM_TIME, DATE_FORMAT(t.POS_DATE,"%Y-%m-%d") AS POS_DATE, DATE_FORMAT(CONCAT(t.POS_DATE, LPAD(t.POS_TIME,6,"0")),"%H:%i:%s") AS POS_TIME')
			->limit($limit)
			->order($order)
			->select();
		echo $this->trace->alias('t')->getLastSql();
	}
	
	/*
	* 获取列表 统计求和
	* @post:
	**/
	public function findmoreTrace($where, $field='t.*') {
		return $this->trace->alias('t')
				->join(DB_PREFIX_TRA.$this->jfbls.' j on t.SYSTEM_REF = j.SYSTEM_REF', 'LEFT')
				->join(DB_PREFIX_TRA.$this->kfls.' k on t.SYSTEM_REF = k.SYSTEM_REF', 'LEFT')
				->where($where)
				->field($field)
				->find();
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateTrace($where, $data) {
		$result = $this->trace->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '交易流水修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findTrace($where, $field='*') {
			$field = $field.',DATE_FORMAT(SYSTEM_DATE,"%Y-%m-%d") AS SYSTEM_DATE,
			DATE_FORMAT(CONCAT(SYSTEM_DATE, LPAD(SYSTEM_TIME,6,"0")),"%H:%i:%s") AS SYSTEM_TIME,
			DATE_FORMAT(SETTLE_SDATE,"%Y-%m-%d") AS SETTLE_SDATE,
			DATE_FORMAT(SETTLE_ADATE,"%Y-%m-%d") AS SETTLE_ADATE,
			DATE_FORMAT(SETTLE_CDATE,"%Y-%m-%d") AS SETTLE_CDATE,
			DATE_FORMAT(SETTLE_HDATE,"%Y-%m-%d") AS SETTLE_HDATE,
			DATE_FORMAT(SETTLE_PDATE,"%Y-%m-%d") AS SETTLE_PDATE';
		return $this->trace->where($where)
				->field($field)
				->find();
	}
	
	//==================== 统计 =============================
		
	/*
	* 获取列表	group
	* @post:	limit 0,20
	**/
	public function getTracegrouplist($where, $field='*', $limit, $group, $order='SYSTEM_DATE desc, SYSTEM_TIME desc') {
		return $this->trace->where($where)
				->field($field)
				->limit($limit)
				->group($group)
				->order($order)
				->select();
	}
	
	/*
	* 获取总和
	* @post:
	**/
	public function sumTrace($where, $field='TRANS_AMT') {
		return $this->trace->where($where)->sum($field);
	}
}
