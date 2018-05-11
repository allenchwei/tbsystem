<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	角色权限
// +----------------------------------------------------------------------
class MRoleModel extends Model{
	
	function __construct(){
		$this->role	= "role";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countRole($where) {
		return M($this->role)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getRolelist($where, $field='*', $limit, $order='ROLE_ID desc') {		
		return M($this->role)->field($field)->where($where)->limit($limit)->order($order)->select();
	}
	
	/*
	* 添加
	**/
	public function addRole($data) {
		$result = M($this->role)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '角色添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	**/
	public function updateRole($where, $data) {
		$result = M($this->role)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '角色修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	**/
	public function delRole($where) {
		$result = M($this->role)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '角色删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条
	**/
	public function findRole($where, $field='*') {
		return M($this->role)->where($where)->field($field)->find();
	}
}
