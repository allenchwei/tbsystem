<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	THbill	通道结算
// +----------------------------------------------------------------------
class THbillModel extends Model{
	
	function __construct(){		
		$this->hbill 	= M('hbill', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countHbill($where) {
		return $this->hbill->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getHbilllist($where, $field='*', $limit, $order='SETTLE_DATE desc') {
		return $this->hbill->where($where)
				->field($field.',DATE_FORMAT(SETTLE_DATE,"%Y-%m-%d") AS SETTLE_DATE, DATE_FORMAT(ACCT_TIME,"%Y-%m-%d %H:%i:%s") AS ACCT_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateHbill($where, $data) {
		$result = $this->hbill->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '通道结算修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findHbill($where, $field='*') {
		return $this->hbill->where($where)
				->field($field.',DATE_FORMAT(SETTLE_DATE,"%Y-%m-%d") AS SETTLE_DATE, DATE_FORMAT(ACCT_TIME,"%Y-%m-%d %H:%i:%s") AS ACCT_TIME')
				->find();
	}
}
