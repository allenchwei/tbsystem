<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	银行卡Bin信息
// +----------------------------------------------------------------------
class MCardbinModel extends Model{
	
	function __construct(){
		$this->cardbin	= "cardbin";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countCardbin($where) {
		return M($this->cardbin)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getCardbinlist($where, $field='*', $limit, $order='BIN_ID desc') {
		return M($this->cardbin)->where($where)->field($field)->limit($limit)->order($order)->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addCardbin($data) {
		$result = M($this->cardbin)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '银行卡Bin添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateCardbin($where, $data) {
		$result = M($this->cardbin)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '银行卡Bin修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delCardbin($where) {
		$result = M($this->cardbin)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '银行卡Bin删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findCardbin($where, $field='*') {
		return M($this->cardbin)->where($where)->field($field)->find();
	}
}
