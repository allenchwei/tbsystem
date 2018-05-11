<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MAlevel	代理商级别
// +----------------------------------------------------------------------
class MAlevelModel extends Model{
	
	function __construct(){
		$this->alevel = "alevel";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countAlevel($where) {
		return M($this->alevel)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getAlevellist($where, $field='*', $limit, $order='PLEVEL_MAP_ID asc') {
		return M($this->alevel)->where($where)->field($field)->limit($limit)->order($order)->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addAlevel($data) {
		$result = M($this->alevel)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '代理商级别添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateAlevel($where, $data) {
		$result = M($this->alevel)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '代理商级别修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findAlevel($where, $field='*') {
		return M($this->alevel)->where($where)->field($field)->find();
	}
}
