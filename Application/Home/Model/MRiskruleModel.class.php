<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MRiskrule	风险规则
// +----------------------------------------------------------------------
class MRiskruleModel extends Model{
	
	function __construct(){
		$this->riskrule = "riskrule";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countRiskrule($where) {
		return M($this->riskrule)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getRiskrulelist($where, $field='*', $limit, $order='RULE_ID desc') {
		return M($this->riskrule)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
		
	/*
	* 添加
	* @post:
	**/
	public function addRiskrule($data) {
		$result = M($this->riskrule)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '风险规则添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateRiskrule($where, $data) {
		$result = M($this->riskrule)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '风险规则修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delRiskrule($where) {
		$result = M($this->riskrule)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '风险规则删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findRiskrule($where, $field='*') {
		return M($this->riskrule)->where($where)->field($field)->find();
	}
}
