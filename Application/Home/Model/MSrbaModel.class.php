<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MSrba	险种规则
// +----------------------------------------------------------------------
class MSrbaModel extends Model{
	
	function __construct(){
		$this->srba	= "srba";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countSrba($where) {
		return M($this->srba)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getSrbalist($where, $field='*', $limit, $order='SRBA_ID desc') {
		return M($this->srba)->where($where)->field($field)->limit($limit)->order($order)->select();
	}

	/*
	* 添加	all
	* @post:
	**/
	public function addAllSrba($data) {
		$result = M($this->srba)->addAll($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '险种规则添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 添加
	* @post:
	**/
	public function addSrba($data) {
		$result = M($this->srba)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '险种规则添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateSrba($where, $data) {
		$result = M($this->srba)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '险种规则修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delSrba($where) {
		$result = M($this->srba)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '险种规则删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findSrba($where, $field='*') {
		return M($this->srba)->where($where)->field($field)->find();
	}
}
