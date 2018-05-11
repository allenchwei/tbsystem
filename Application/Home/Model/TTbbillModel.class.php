<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	TTbbill	投保汇总单
// +----------------------------------------------------------------------
class TTbbillModel extends Model{
	
	function __construct(){		
		$this->tbbill = M('tbbill', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countTbbill($where) {
		return $this->tbbill->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getTbbilllist($where, $field='*', $limit, $order='TBBILL_ID desc') {
		return $this->tbbill->where($where)
				->field($field.',DATE_FORMAT(TB_TIME,"%Y-%m-%d") as TB_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 添加
	* @post:
	**/
	public function addTbbill($data) {
		$result = $this->tbbill->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！", 'TBBILL_ID'=>$result);
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateTbbill($where, $data) {
		$result = $this->tbbill->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delTbbill($where) {
		$result = $this->tbbill->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findTbbill($where, $field='*') {
		return $this->tbbill->where($where)
				->field($field.',DATE_FORMAT(TB_TIME,"%Y-%m-%d") as TB_TIME')
				->find();
	}
	
	/*
	* 获取字段总和
	* @post:
	**/
	public function sumTbbill($where, $field='TB_AMT_SUCC') {
		return $this->tbbill->where($where)->sum($field);
	}

	/*
	* 按归属分组 获取字段总和
	* @post:
	**/
	public function groupTbbill($where, $field='TB_AMT_SUCC',$group="BRANCH_MAP_ID,PARTNER_MAP_ID,SECURITY_TYPE") {
		return $this->tbbill->where($where)->field($field)->group($group)->select();
	}
}
