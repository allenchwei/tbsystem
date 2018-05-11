<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	TPbill	分支机构结算单
// +----------------------------------------------------------------------
class TPbillModel extends Model{
	
	function __construct(){		
		$this->pbill 	= M('pbill', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countPbill($where) {
		return $this->pbill->where($where.' and CHAR_LENGTH(SETTLE_DATE) = 6')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPbilllist($where, $field='*', $limit, $order='SETTLE_DATE desc') {
		return $this->pbill->where($where.' and CHAR_LENGTH(SETTLE_DATE) = 6')
				->field($field.', DATE_FORMAT(ACCT_TIME,"%Y-%m-%d %H:%i:%s") AS ACCT_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updatePbill($where, $data) {
		$result = $this->pbill->where($where)->save($data);
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
	public function findPbill($where, $field='*') {
		return $this->pbill->where($where.' and CHAR_LENGTH(SETTLE_DATE) = 6')
				->field($field.',DATE_FORMAT(SETTLE_DATE,"%Y-%m-%d") AS SETTLE_DATE, DATE_FORMAT(ACCT_TIME,"%Y-%m-%d %H:%i:%s") AS ACCT_TIME')
				->find();
	}
}
