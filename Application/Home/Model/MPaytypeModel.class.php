<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MPaytype	支付方式
// +----------------------------------------------------------------------
class MPaytypeModel extends Model{
	
	function __construct(){
		$this->paytype = "paytype";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countPaytype($where) {
		return M($this->paytype)->where($where)->count();
	}
	 
	/*
	* 获取列表
	* @post:
	**/
	public function getPaytypelist($where='', $field='*', $order='PAY_MAP_ID asc') {
		return M($this->paytype)->where($where)->field($field)->order($order)->select();
	}
		
	/*
	* 添加
	* @post:
	**/
	public function addPaytype($data) {
		$result = M($this->paytype)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '支付方式添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updatePaytype($where, $data) {
		$result = M($this->paytype)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '支付方式修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delPaytype($where) {
		$result = M($this->paytype)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '支付方式删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findPaytype($where, $field='*') {
		return M($this->paytype)->where($where)->field($field)->find();
	}
}
