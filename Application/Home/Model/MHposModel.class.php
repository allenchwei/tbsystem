<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MHpos	通道终端
// +----------------------------------------------------------------------
class MHposModel extends Model{
	
	function __construct(){
		$this->hpos 	= "hpos";
		$this->host 	= "host";
		$this->hshop 	= "hshop";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countHpos($where) {
		return M($this->hpos)->alias('hp')
				->join(DB_PREFIX.$this->host.' ho on hp.HOST_MAP_ID = ho.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hshop.' hs on (hp.HOST_MAP_ID = hs.HOST_MAP_ID and hp.HSHOP_NO = hs.HSHOP_NO)', 'LEFT')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getHposlist($where, $field='hp.*,ho.*,hs.*', $limit, $order='hp.HPOS_ID desc') {
		return M($this->hpos)->alias('hp')
				->join(DB_PREFIX.$this->host.' ho on hp.HOST_MAP_ID = ho.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hshop.' hs on (hp.HOST_MAP_ID = hs.HOST_MAP_ID and hp.HSHOP_NO = hs.HSHOP_NO)', 'LEFT')
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
	public function addHpos($data) {
		$result = M($this->hpos)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '通道添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateHpos($where, $data) {
		$result = M($this->hpos)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '通道修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findHpos($where, $field='*') {
		return M($this->hpos)->where($where)->field($field)->find();
	}
}
