<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	分公司信息
// +----------------------------------------------------------------------
class MBranchModel extends Model{
	
	function __construct(){
		$this->branch	= "branch";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countBranch($where) {
		return M($this->branch)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getBranchlist($where, $field='*', $limit, $order='BRANCH_MAP_ID desc') {		
		return M($this->branch)->field($field)->where($where)->limit($limit)->order($order)->select();
	}
	
	/*
	* 添加
	* @post:
	**/
	public function addBranch($data) {
		$result = M($this->branch)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '分公司添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！", 'BRANCH_MAP_ID'=>$result);
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateBranch($where, $data) {
		$result = M($this->branch)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '分公司修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 删除
	* @post:
	**/
	public function delBranch($where) {
		$result = M($this->branch)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '分公司删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条
	* @post:
	**/
	public function findBranch($where, $field='*') {
		return M($this->branch)->where($where)->field($field)->find();
	}

	/*
	* 获取分支机构的下拉列表数据
	* @post:
	**/
	public function getBranch_select($where) {
		$list =  M($this->branch)->field('BRANCH_MAP_ID,BRANCH_NAME')->where($where)->select();
		foreach($list as $val){
			$res[] = array($val['BRANCH_MAP_ID'], $val['BRANCH_NAME']);
		}
		return $res;
	}
}
