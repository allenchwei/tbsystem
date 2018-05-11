<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	POS终端通讯参数信息
// +----------------------------------------------------------------------
class MCommModel extends Model{
	
	function __construct(){
		$this->comm	= "comm";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countComm($where) {
		return M($this->comm)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getCommlist($where, $field='*', $limit, $order='COM_INDEX desc') {
		return M($this->comm)->where($where)->field($field)->limit($limit)->order($order)->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addComm($data) {
		$result = M($this->comm)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, 'POS终端通讯添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateComm($where, $data) {
		$result = M($this->comm)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, 'POS终端通讯修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delComm($where, $data) {
		$result = M($this->comm)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, 'POS终端通讯删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findComm($where, $field='*') {
		return M($this->comm)->where($where)->field($field)->find();
	}
}
