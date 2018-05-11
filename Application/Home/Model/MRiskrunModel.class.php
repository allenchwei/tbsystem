<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MRiskrun	风险调整评级
// +----------------------------------------------------------------------
class MRiskrunModel extends Model{
	
	function __construct(){
		$this->riskrun = "riskrun";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countRiskrun($where) {
		return M($this->riskrun)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getRiskrunlist($where, $field='*', $limit, $order='RISKRUN_ID asc') {
		return M($this->riskrun)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
		
	/*
	* 添加
	* @post:
	**/
	public function addRiskrun($data) {
		$result = M($this->riskrun)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '风险调整评级添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateRiskrun($where, $data) {
		$result = M($this->riskrun)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '风险调整评级修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delRiskrun($where) {
		$result = M($this->riskrun)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '风险调整评级删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findRiskrun($where, $field='*') {
		return M($this->riskrun)->where($where)->field($field)->find();
	}
}
