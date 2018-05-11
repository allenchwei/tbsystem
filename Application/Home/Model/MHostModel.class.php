<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MHost	通道基本信息
// +----------------------------------------------------------------------
class MHostModel extends Model{
	
	function __construct(){
		$this->host		= "host";
		$this->hauth	= "hauth";
		$this->hcls		= "hcls";
		$this->hmdr_tmp	= "hmdr_tmp";
		$this->hmdr 	= "hmdr";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countHost($where) {
		return M($this->host)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getHostlist($where, $field='*', $limit, $order='HOST_MAP_ID asc') {
		return M($this->host)->where($where)
				->field($field.',DATE_FORMAT(END_TIME,"%Y-%m-%d") AS END_TIME')
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addHost($data) {
		$result = M($this->host)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '通道添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！", 'HOST_MAP_ID'=>$result);
	}

	/*
	* 修改
	* @post:
	**/
	public function updateHost($where, $data) {
		$result = M($this->host)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '通道修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delHost($where) {
		$result = M($this->host)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '通道删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findHost($where, $field='*') {
		return M($this->host)->where($where)
				->field($field.',DATE_FORMAT(CREATE_TIME,"%Y-%m-%d") AS CREATE_TIME, DATE_FORMAT(END_TIME,"%Y-%m-%d") AS END_TIME')
				->find();
	}
	
	/*
	* 获取单条信息	more
	* @post:
	**/
	public function findmoreHost($where, $field='*') {
		return M($this->host)->alias('ho')
				->join(DB_PREFIX.$this->hauth.' ha on ho.HOST_MAP_ID=ha.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hcls.' hc on ho.HOST_MAP_ID=hc.HOST_MAP_ID')				
				->where($where)
				->field($field.', DATE_FORMAT(ho.END_TIME,"%Y-%m-%d") AS END_TIME')
				->find();
	}

	/*
	* 获取列表
	* @post:
	**/
	public function getHostHmdrlist($where, $field='a_hmdr.*', $order='a_hmdr.MCC_TYPE desc') {
		return M($this->host)
				->join(DB_PREFIX.$this->hmdr.' on a_host.HOST_MAP_ID = a_hmdr.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hmdr_tmp.' on a_host.HOST_MAP_ID = a_hmdr_tmp.HOST_MAP_ID', 'LEFT')
				->where($where)
				->field($field)
				->order($order)
				->select();
	}	
}
