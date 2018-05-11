<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	PUser	监控用户
// +----------------------------------------------------------------------
class PUserModel extends Model{
	
	function __construct(){
		$this->user 	= M('user', DB_PREFIX_PAM, DB_DSN_PAM);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countUser($where) {
		return $this->user->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getUserlist($where, $field='*', $limit, $order='BRANCH_MAP_ID desc') {
		return $this->user->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
		
	/*
	* 添加
	* @post:
	**/
	public function addUser($data) {
		$result = $this->user->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '监控用户添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateUser($where, $data) {
		$result = $this->user->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '监控用户修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 删除
	* @post:
	**/
	public function delUser($where) {
		$result = $this->user->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '监控用户删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findUser($where, $field='*') {
		return $this->user->where($where)				
				->field($field)
				->find();
	}
}
