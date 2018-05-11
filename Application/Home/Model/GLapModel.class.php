<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	GLap	会员分户账
// +----------------------------------------------------------------------
class GLapModel extends Model{
	
	function __construct(){		
		$this->lap = M('lap', DB_PREFIX_GLA, DB_DSN_GLA);
	}
	
	/*
	* 获取统计数量	新
	* @post:
	**/
	public function countNewsLap($where) {
		return $this->lap->where($where)->count();
	}
	
	/*
	* 获取列表		新
	* @post:
	**/
	public function getNewsLaplist($where, $field='*', $limit, $order='ACCT_NO desc') {
		return $this->lap->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
	
	//----------------结束----------------
	
	/*
	* 添加
	* @post:
	**/
	public function addLap($data) {
		$result = $this->lap->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '会员分户账添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateLap($where, $data) {
		$result = $this->lap->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '会员分户账修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findLap($where, $field='*') {
		return $this->lap->where($where)
				->field($field)
				->find();
	}
}
