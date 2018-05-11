<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MDkco	代扣银行管理
// +----------------------------------------------------------------------
class MDkcoModel extends Model{
	
	function __construct(){
		$this->dkco  = "dkco";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countDkco($where) {
		return M($this->dkco)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getDkcolist($where, $field='*', $limit, $order='DKCO_MAP_ID desc') {
		return M($this->dkco)->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addDkco($data) {
		$result = M($this->dkco)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '代扣银行添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateDkco($where, $data) {
		$result = M($this->dkco)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '代扣银行修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findDkco($where, $field='*') {
		return M($this->dkco)->where($where)->field($field)->find();
	}
}
