<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	Mhshop	通道商户
// +----------------------------------------------------------------------
class MHshopModel extends Model{
	
	function __construct(){
		$this->hshop = "hshop";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countHshop($where) {
		return M($this->hshop)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getHshoplist($where, $field='*', $limit, $order='HSHOP_ID desc') {
		return M($this->hshop)->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}	
	
	/*
	* 添加
	* @post:
	**/
	public function addHshop($data) {
		$result = M($this->hshop)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '通道商户添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateHshop($where, $data) {
		$result = M($this->hshop)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '通道商户修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 删除
	* @post:
	**/
	public function delHshop($where) {
		$result = M($this->hshop)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '通道商户删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findHshop($where, $field='*') {
		return M($this->hshop)->where($where)->field($field)->find();
	}
}
