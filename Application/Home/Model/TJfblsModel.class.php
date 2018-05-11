<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	TJfbls	积分宝运营流水表
// +----------------------------------------------------------------------
class TJfblsModel extends Model{
	
	function __construct(){		
		$this->jfbls = M('jfbls', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	
	/*
	* 获取列表		新
	* @post:
	**/
	public function getNewsJfblslist($where, $field='*', $limit, $order='SYSTEM_REF desc') {
		return $this->jfbls->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
	
	//--------------------------------------------------------------------
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findJfbls($where, $field='*') {
		return $this->jfbls->where($where)
				->field($field.',DATE_FORMAT(JFB_CLEAR_DATE,"%Y-%m-%d") AS JFB_CLEAR_DATE')
				->find();
	}

	/*
	* 获取统计
	* @post:
	**/
	public function countJfbls($where, $field='*') {
		return $this->jfbls->where($where)
				->field($field.',DATE_FORMAT(JFB_CLEAR_DATE,"%Y-%m-%d") AS JFB_CLEAR_DATE')
				->count();
	}

	/*
	* 获取字段总和
	* @post:
	**/
	public function sumJfbls($where, $field='CON_FEE') {
		return $this->jfbls->where($where)->sum($field);
	}
}
