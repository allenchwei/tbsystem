<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MSecurity	保险公司信息
// +----------------------------------------------------------------------
class MSecurityModel extends Model{
	
	function __construct(){
		$this->security	= "security";
		$this->srba		= "srba";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countSecurity($where) {
		return M($this->security)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getSecuritylist($where, $field='*', $limit, $order='SECURITY_MAP_ID desc') {
		return M($this->security)->where($where)->field($field)->limit($limit)->order($order)->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addSecurity($data) {
		$result = M($this->security)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '保险公司添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！", 'SECURITY_MAP_ID'=>$result);
	}

	/*
	* 修改
	* @post:
	**/
	public function updateSecurity($where, $data) {
		$result = M($this->security)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '保险公司修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delSecurity($where) {
		$result = M($this->security)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '保险公司删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findSecurity($where, $field='*') {
		return M($this->security)->where($where)->field($field)->find();
	}

	/*
	* 获取列表(关联险种规则)
	* @post:
	**/
	public function getSecuritymore($where, $field='sec.*', $limit, $order='sec.SECURITY_MAP_ID desc') {
		return M($this->security)->alias('sec')
				->join(DB_PREFIX.$this->srba.' sr on sec.SECURITY_MAP_ID = sr.SECURITY_MAP_ID')
				->where($where)->field($field)->limit($limit)->order($order)->select();
	}
}
