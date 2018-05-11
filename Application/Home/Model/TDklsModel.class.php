<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	TDkls	代扣流水
// +----------------------------------------------------------------------
class TDklsModel extends Model{
	
	function __construct(){		
		$this->dkls = M('dkls', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	
	/*
	* 获取列表		新
	* @post:
	**/
	public function getNewsDklslist($where, $field='*', $limit, $order='DK_ID desc') {
		return $this->dkls->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
	
	//----------------结束----------------
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countDkls($where) {
		return $this->dkls->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getDklslist($where, $field='*', $limit, $order='DK_ID desc') {
		return $this->dkls->where($where)
				->field($field.',DATE_FORMAT(SETTLE_DATE,"%Y-%m-%d") AS SETTLE_DATE, DATE_FORMAT(DK_DATE,"%Y-%m-%d") AS DK_DATE, DATE_FORMAT(CONCAT(DK_DATE, LPAD(DK_TIME,6,"0")),"%H:%i") AS DK_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 获取列表	group
	* @post:	limit 0,20
	**/
	public function getDklsgrouplist($where, $field='*', $limit,  $group, $order='DK_ID desc') {
		return $this->dkls->where($where)
				->field($field)
				->limit($limit)
				->group($group)
				->order($order)
				->select();
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateDkls($where, $data) {
		$result = $this->dkls->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '代扣流水修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
		
	/*
	* 获取单条信息
	* @post:
	**/
	public function findDkls($where, $field='*') {
		return $this->dkls->where($where)
				->field($field.',DATE_FORMAT(SETTLE_DATE,"%Y-%m-%d") AS SETTLE_DATE, DATE_FORMAT(DK_DATE,"%Y-%m-%d") AS DK_DATE, DATE_FORMAT(CONCAT(DK_DATE, LPAD(DK_TIME,6,"0")),"%H:%i") AS DK_TIME')
				->find();
	}
		
	/*
	* 获取单条信息
	* @post:
	**/
	public function findNewsDkls($where, $field='*') {
		return $this->dkls->where($where)->field($field)->find();
	}
	
	
	/*
	* 获取单条信息
	* lock
	* @post:
	**/
	public function findLockDkls($where, $field='*') {
		return $this->dkls->lock(true)->where($where)->field($field)->find();
	}
}
