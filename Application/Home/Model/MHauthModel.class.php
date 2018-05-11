<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MHauth	通道权限
// +----------------------------------------------------------------------
class MHauthModel extends Model{
	
	function __construct(){
		$this->hauth		= "hauth";
		$this->host 		= "host";
		$this->hauth_tmp	= "hauth_tmp";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countHauth($where) {
		return M($this->hauth)->alias('ha')
				->join(DB_PREFIX.$this->host.' ho on ha.HOST_MAP_ID = ho.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hauth_tmp.' hap on ha.HOST_MAP_ID = hap.HOST_MAP_ID', 'LEFT')
				->where($where)
				->count();
	}	
	
	/*
	* 获取列表
	* @post:
	**/
	public function getHauthlist($where, $field='ha.*,ho.*', $limit, $order='ha.HOST_MAP_ID desc') {
		return M($this->hauth)->alias('ha')
				->join(DB_PREFIX.$this->host.' ho on ha.HOST_MAP_ID = ho.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hauth_tmp.' hap on ha.HOST_MAP_ID = hap.HOST_MAP_ID', 'LEFT')
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
	public function addHauth($data) {
		$result = M($this->hauth)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '通道权限添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateHauth($where, $data) {
		$result = M($this->hauth)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '通道权限修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 删除
	* @post:
	**/
	public function delHauth($where) {
		$result = M($this->hauth)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '通道权限删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findHauth($where, $field='*') {
		return M($this->hauth)->where($where)->field($field)->find();
	}
	
	
	
	/*----------------------------- tmp ----------------------------------*/
		
	/*
	* 添加
	* @post:
	**/
	public function addHauth_tmp($data) {
		$result = M($this->hauth_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '通道权限 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateHauth_tmp($where, $data) {
		$result = M($this->hauth_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '通道权限 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 删除
	* @post:
	**/
	public function delHauth_tmp($where) {
		$result = M($this->hauth_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '通道权限 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findHauth_tmp($where, $field='*') {
		return M($this->hauth_tmp)->where($where)->field($field)->find();
	}
}
