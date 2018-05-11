<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	TAcqbill	平台分润
// +----------------------------------------------------------------------
class TAcqbillModel extends Model{
	
	function __construct(){		
		$this->acqbill 	= M('acqbill', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countAcqbill($where) {
		return $this->acqbill->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getAcqbilllist($where, $field='*', $limit, $order='ACQBILL_ID desc') {
		return $this->acqbill->where($where)
				->field($field.',DATE_FORMAT(SETTLE_DATE,"%Y-%m-%d") AS SETTLE_DATE,DATE_FORMAT(ACCT_TIME,"%Y-%m-%d %H:%i:%s") AS ACCT_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateAcqbill($where, $data) {
		$result = $this->acqbill->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '平台分润修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findAcqbill($where, $field='*') {
		return $this->acqbill->where($where)
				->field($field.',DATE_FORMAT(SETTLE_DATE,"%Y-%m-%d") AS SETTLE_DATE,DATE_FORMAT(ACCT_TIME,"%Y-%m-%d %H:%i:%s") AS ACCT_TIME')
				->find();
	}
}
