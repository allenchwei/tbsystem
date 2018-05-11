<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	TSckbill	商户超扣结算
// +----------------------------------------------------------------------
class TSckbillModel extends Model{
	
	function __construct(){		
		$this->sckbill 	= M('sckbill', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countSckbill($where) {
		return $this->sckbill->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getSckbilllist($where, $field='*', $limit, $order='SCKBILL_ID desc') {
		return $this->sckbill->where($where)
				->field($field.',DATE_FORMAT(SETTLE_DATE,"%Y-%m-%d") AS SETTLE_DATE,DATE_FORMAT(ACCT_TIME,"%Y-%m-%d %H:%i:%s") AS ACCT_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateSckbill($where, $data) {
		$result = $this->sckbill->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户超扣结算修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findSckbill($where, $field='*') {
		return $this->sckbill->where($where)
				->field($field.',DATE_FORMAT(SETTLE_DATE,"%Y-%m-%d") AS SETTLE_DATE,DATE_FORMAT(ACCT_TIME,"%Y-%m-%d %H:%i:%s") AS ACCT_TIME')
				->find();
	}
}
