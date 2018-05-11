<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	分公司银行账户
// +----------------------------------------------------------------------
class MBbactModel extends Model{
	
	function __construct(){
		$this->bbact	= "bbact";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countBbact($where) {
		return M($this->bbact)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getBbactlist($where, $field='*', $limit, $order='BRANCH_MAP_ID desc') {		
		return M($this->bbact)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
	
	/*
	* 添加
	* @post:
	**/
	public function addBbact($data) {
		$result = M($this->bbact)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '分公司银行账户添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateBbact($where, $data) {
		$result = M($this->bbact)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '分公司银行账户修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 删除
	* @post:
	**/
	public function delBbact($where) {
		$result = M($this->bbact)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '分公司银行账户删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条
	* @post:
	**/
	public function findBbact($where, $field='*') {
		return M($this->bbact)->where($where)->field($field)->find();
	}
}
