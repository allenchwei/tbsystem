<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MSposreq	商户POS需求信息需求信息
// +----------------------------------------------------------------------
class MSposreqModel extends Model{
	
	function __construct(){
		$this->sposreq	= "sposreq";
		$this->partner	= "partner";
		$this->shop		= "shop";
		$this->model	= "model";
		$this->branch	= "branch";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countSposreq($where) {
		return M($this->sposreq)->alias('sp')
				->join(DB_PREFIX.$this->shop.' s on sp.SHOP_MAP_ID = s.SHOP_MAP_ID')
				->join(DB_PREFIX.$this->model.' m on sp.MODEL_MAP_ID = m.MODEL_MAP_ID')
				->join(DB_PREFIX.$this->partner.' a on s.PARTNER_MAP_ID = a.PARTNER_MAP_ID', 'LEFT')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getSposreqlist($where, $field='sp.*', $limit, $order='sp.SMODEL_ID desc') {
		return M($this->sposreq)->alias('sp')
				->join(DB_PREFIX.$this->shop.' s on sp.SHOP_MAP_ID = s.SHOP_MAP_ID')
				->join(DB_PREFIX.$this->model.' m on sp.MODEL_MAP_ID = m.MODEL_MAP_ID')
				->join(DB_PREFIX.$this->partner.' a on s.PARTNER_MAP_ID = a.PARTNER_MAP_ID', 'LEFT')
				->where($where)
				->field($field.',DATE_FORMAT(sp.APPLY_DATE,"%Y-%m-%d") AS APPLY_DATE')
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addSposreq($data) {
		$result = M($this->sposreq)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户POS需求信息添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateSposreq($where, $data) {
		$result = M($this->sposreq)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户POS需求信息修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delSposreq($where) {
		$result = M($this->sposreq)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '商户POS需求信息删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findSposreq($where, $field='*') {
		return M($this->sposreq)->where($where)
				->field($field)
				->find();
	}
}
