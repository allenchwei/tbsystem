<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	TPibill	通道养老金统计
// +----------------------------------------------------------------------
class TPibillModel extends Model{
	
	function __construct(){		
		$this->pibill 	= M('pibill', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countPibill($where) {
		return $this->pibill->where($where.' and CHAR_LENGTH(SETTLE_DATE) = 6')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPibilllist($where, $field='*', $limit, $order='SETTLE_DATE desc') {
		return $this->pibill->where($where.' and CHAR_LENGTH(SETTLE_DATE) = 6')
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updatePibill($where, $data) {
		$result = $this->pibill->where($where)->save($data);
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
	public function findPibill($where, $field='*') {
		return $this->pibill->where($where.' and CHAR_LENGTH(SETTLE_DATE) = 6')
				->field($field)
				->find();
	}
}
