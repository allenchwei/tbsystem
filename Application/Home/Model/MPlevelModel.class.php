<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MPlevel	代理商级别
// +----------------------------------------------------------------------
class MPlevelModel extends Model{
	
	function __construct(){
		$this->plevel = "plevel";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countPlevel($where) {
		return M($this->plevel)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPlevellist($where, $field='*', $limit, $order='PLEVEL_MAP_ID asc') {
		return M($this->plevel)->where($where)->field($field)->limit($limit)->order($order)->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addPlevel($data) {
		$result = M($this->plevel)->data($data)->add();
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
	public function updatePlevel($where, $data) {
		$result = M($this->plevel)->where($where)->save($data);
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
	public function findPlevel($where, $field='*') {
		return M($this->plevel)->where($where)->field($field)->find();
	}
}
