<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MHcls	通道结算方式
// +----------------------------------------------------------------------
class MHclsModel extends Model{
	
	function __construct(){
		$this->hcls 	= "hcls";
		$this->host	 	= "host";
		$this->hcls_tmp	= "hcls_tmp";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countHcls($where) {
		return M($this->hcls)->alias('hc')
				->join(DB_PREFIX.$this->host.' ho on hc.HOST_MAP_ID = ho.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hcls_tmp.' hcp on hc.HOST_MAP_ID = hcp.HOST_MAP_ID', 'LEFT')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getHclslist($where, $field='hc.*,ho.*', $limit, $order='hc.HOST_MAP_ID desc') {
		return M($this->hcls)->alias('hc')
				->join(DB_PREFIX.$this->host.' ho on hc.HOST_MAP_ID = ho.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hcls_tmp.' hcp on hc.HOST_MAP_ID = hcp.HOST_MAP_ID', 'LEFT')
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
	public function addHcls($data) {
		$result = M($this->hcls)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '通道结算方式添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}	
	
	/*
	* 修改
	* @post:
	**/
	public function updateHcls($where, $data) {
		$result = M($this->hcls)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '通道结算方式修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 删除
	* @post:
	**/
	public function delHcls($where) {
		$result = M($this->hcls)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '通道结算方式删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}

	/*
	* 获取单条信息
	* @post:
	**/
	public function findHcls($where, $field='*') {
		return M($this->hcls)->where($where)->field($field)->find();
	}
	
	
	
	/*----------------------------- tmp ----------------------------------*/
		
	/*
	* 添加
	* @post:
	**/
	public function addHcls_tmp($data) {
		$result = M($this->hcls_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '通道结算方式 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateHcls_tmp($where, $data) {
		$result = M($this->hcls_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '通道结算方式 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
		
	/*
	* 删除
	* @post:
	**/
	public function delHcls_tmp($where) {
		$result = M($this->hcls_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '通道结算方式 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findHcls_tmp($where, $field='*') {
		return M($this->hcls_tmp)->where($where)->field($field)->find();
	}
}
