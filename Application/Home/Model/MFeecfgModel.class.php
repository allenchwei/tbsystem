<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MFeecfg	集分宝分润办法
// +----------------------------------------------------------------------
class MFeecfgModel extends Model{
	
	function __construct(){
		$this->feecfg = "feecfg";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countFeecfg($where) {
		return M($this->feecfg)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getFeecfglist($where, $field='*', $limit, $order='CFG_ID asc') {
		return M($this->feecfg)->where($where)->field($field)->limit($limit)->order($order)->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addFeecfg($data) {
		$result = M($this->feecfg)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '集分宝分润添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateFeecfg($where, $data) {
		$result = M($this->feecfg)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '集分宝分润修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findFeecfg($where, $field='*') {
		return M($this->feecfg)->where($where)->field($field)->find();
	}
}
