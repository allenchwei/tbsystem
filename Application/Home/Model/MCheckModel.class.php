<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MCheck	进件与变更审批流程
// +----------------------------------------------------------------------
class MCheckModel extends Model{
	
	function __construct(){
		$this->check = "check";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countCheck($where) {
		return M($this->check)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getChecklist($where, $field='*', $limit, $order='CHECK_ID desc') {
		return M($this->check)
				->where($where)
				->field($field.',DATE_FORMAT(CHECK_TIME,"%Y-%m-%d %H:%i:%s") AS CHECK_TIME')
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addCheck($data) {
		$result = M($this->check)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '进件与变更审批添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！",'checkId'=>$result);
	}

	/*
	* 修改
	* @post:
	**/
	public function updateCheck($where, $data) {
		$result = M($this->check)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '进件与变更审批修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delCheck($where) {
		$result = M($this->check)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '进件与变更审批删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findCheck($where, $field='*', $order='CHECK_TIME desc') {
		return M($this->check)
				->where($where)
				->field($field.',DATE_FORMAT(CHECK_TIME,"%Y-%m-%d %H:%i:%s") AS CHECK_TIME')
				->order($order)
				->find();
	}
}
