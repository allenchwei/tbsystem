<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MStockop	设备基本信息
// +----------------------------------------------------------------------
class MStockopModel extends Model{
	
	function __construct(){
		$this->stockop 	= "stockop";
		$this->model  	= "model";
		$this->factory  = "factory";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countStockop($where) {
		return M($this->stockop)->alias('s')
				->join(DB_PREFIX.$this->model.' m on m.MODEL_MAP_ID = s.MODEL_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getStockoplist($where, $field='*', $limit, $order='d.stockop_SN desc') {
		return M($this->stockop)->alias('s')
				->join(DB_PREFIX.$this->factory.' f on f.FACTORY_MAP_ID = d.FACTORY_MAP_ID')
				->join(DB_PREFIX.$this->model.' m on m.MODEL_MAP_ID = d.MODEL_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(d.INSTALL_DATE,"%Y-%m-%d") AS INSTALL_DATE, DATE_FORMAT(d.CREATE_TIME,"%Y-%m-%d") AS CREATE_TIME, DATE_FORMAT(d.UPDATE_TIME,"%Y-%m-%d") AS UPDATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addStockop($data) {
		$result = M($this->stockop)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '设备添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateStockop($where, $data) {
		$result = M($this->stockop)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '设备修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delStockop($where) {
		$result = M($this->stockop)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '设备删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findStockop($where, $field='*') {
		return M($this->stockop)->alias('s')
				->join(DB_PREFIX.$this->factory.' f on f.FACTORY_MAP_ID = d.FACTORY_MAP_ID')
				->join(DB_PREFIX.$this->model.' m on m.MODEL_MAP_ID = d.MODEL_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(d.INSTALL_DATE,"%Y-%m-%d") AS INSTALL_DATE, DATE_FORMAT(d.CREATE_TIME,"%Y-%m-%d") AS CREATE_TIME, DATE_FORMAT(d.UPDATE_TIME,"%Y-%m-%d") AS UPDATE_TIME')
				->find();
	}

	/*
	* 获取设备分组
	* @post:
	**/
	public function groupStockop($where, $field='*',$group) {
		return M($this->stockop)->alias('s')
				->join(DB_PREFIX.$this->factory.' f on f.FACTORY_MAP_ID = d.FACTORY_MAP_ID')
				->join(DB_PREFIX.$this->model.' m on m.MODEL_MAP_ID = d.MODEL_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(d.INSTALL_DATE,"%Y-%m-%d") AS INSTALL_DATE, DATE_FORMAT(d.CREATE_TIME,"%Y-%m-%d") AS CREATE_TIME, DATE_FORMAT(d.UPDATE_TIME,"%Y-%m-%d") AS UPDATE_TIME')
				->group($group)
				->select();
	}
}
