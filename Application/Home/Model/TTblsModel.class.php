<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	TTbls	投保明细
// +----------------------------------------------------------------------
class TTblsModel extends Model{
	
	function __construct(){		
		$this->tbls = M('tbls', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countTbls($where) {
		return $this->tbls->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getTblslist($where, $field='*', $limit, $order='TB_ID desc') {
		return $this->tbls->where($where)
				->field($field.',DATE_FORMAT(TB_TIME,"%Y-%m-%d") as TB_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 添加
	* @post:
	**/
	public function addTbls($data) {
		$result = $this->tbls->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '会员添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！", 'VIP_ID'=>$result);
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateTbls($where, $data) {
		$result = $this->tbls->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '会员修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delTbls($where) {
		$result = $this->tbls->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '会员删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findTbls($where, $field='*') {
		return $this->tbls->where($where)
				->field($field.',DATE_FORMAT(TB_TIME,"%Y-%m-%d") as TB_TIME')
				->find();
	}
	/*
	* 获取字段总和
	* @post:
	**/
	public function sumTbls($where, $field='TB_AMT') {
		return $this->tbls->where($where)->sum($field);
	}

	/*
	* 提交投保数据
	* @post:
	**/
	public function getTblsgroup($where, $field='*',$order='TB_TIME asc',$group="JFB_SECU_REF") {
		return $this->tbls->where($where)
				->field($field.' ,COUNT(VIP_ID) as num, SUM(TB_AMT) as summoney,DATE_FORMAT(TB_TIME,"%Y-%m-%d") as TB_TIME')
				->order($order)
				->group($group)
				->find();
	}
	/*
	* 分组统计投保数据
	* @post:
	**/
	public function getTblsgroupcount($where, $group="JFB_SECU_REF") {
		return $this->tbls->where($where)->count('DISTINCT '.$group);
	}
	/*
	* 分组统计投保数据列表
	* @post:
	**/
	public function getTblsgrouplist($where, $field='*',$order='TB_TIME desc',$group="JFB_SECU_REF",$limit="") {
		return $this->tbls->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->group($group)
				->select();
	}
}
