<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MRiskval	风险规则阀值
// +----------------------------------------------------------------------
class MRiskvalModel extends Model{
	
	function __construct(){
		$this->riskval 	= "riskval";
		$this->riskrule	= "riskrule";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countRiskval($where) {		
		return M($this->riskval)->alias('va')
				->join(DB_PREFIX.$this->riskrule.' ru on va.RULE_ID = ru.RULE_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getRiskvallist($where, $field='*', $limit, $order='va.VAL_ID desc') {
		return M($this->riskval)->alias('va')
				->join(DB_PREFIX.$this->riskrule.' ru on va.RULE_ID = ru.RULE_ID')
				->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
		
	/*
	* 添加
	* @post:
	**/
	public function addRiskval($data) {
		$result = M($this->riskval)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '风险规则阀值添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateRiskval($where, $data) {
		$result = M($this->riskval)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '风险规则阀值修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delRiskval($where) {
		$result = M($this->riskval)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '风险规则阀值删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findRiskval($where, $field='*') {
		return M($this->riskval)->where($where)->field($field)->find();
	}
}
