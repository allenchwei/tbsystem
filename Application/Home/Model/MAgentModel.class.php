<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MAgent	合作伙伴管理
// +----------------------------------------------------------------------
class MAgentModel extends Model{
	
	function __construct(){
		$this->agent  = "agent";
		$this->branch = "branch";
		$this->city   = "city";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countAgent($where) {
		return M($this->agent)->alias('a')
				->join(DB_PREFIX.$this->branch.' b on a.BRANCH_MAP_ID = b.BRANCH_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getAgentlist($where, $field='*', $limit, $order='a.AGENT_MAP_ID desc') {
		return M($this->agent)->alias('a')
				->join(DB_PREFIX.$this->branch.' b on a.BRANCH_MAP_ID = b.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->city.' c on a.CITY_NO = c.CITY_S_CODE')
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
	public function addAgent($data) {
		$result = M($this->agent)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '合作伙伴添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！",'AGENT_MAP_ID'=>$result);
	}

	/*
	* 修改
	* @post:
	**/
	public function updateAgent($where, $data) {
		$result = M($this->agent)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '合作伙伴修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findAgent($where, $field='a.*,b.*') {
		return M($this->agent)->alias('a')
				->join(DB_PREFIX.$this->branch.' b on a.BRANCH_MAP_ID = b.BRANCH_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(a.END_TIME,"%Y-%m-%d") AS END_TIME')
				->find();
	}

	/*
	* 获取最大编号
	* @post:
	**/
	public function findMaxAgent($where) {
		return M($this->agent)->where($where)->max('AGENT_NO');
	}

	/*
	* 获取某分支机构下的所有合作伙伴数据
	* @post:
	**/
	public function getAgent_select($where) {
		$list = M($this->agent)->where($where)->field('AGENT_MAP_ID,AGENT_NAME');
		foreach($list as $val){
			$res[] = array($val['AGENT_MAP_ID'], $val['AGENT_NAME']);
		}
		return $res;
	}
}
