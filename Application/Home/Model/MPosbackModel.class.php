<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MPosback	本地商户终端详细信息
// +----------------------------------------------------------------------
class MPosbackModel extends Model{
	
	function __construct(){
		$this->pos		= "pos";
		$this->posback	= "posback";
		$this->partner	= "partner";
		$this->shop		= "shop";
		$this->device	= "device";
		$this->model	= "model";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countPosback($where) {
		return M($this->posback)->alias('pb')
				->join(DB_PREFIX.$this->shop.' s on pb.SHOP_NO1 = s.SHOP_NO')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPosbacklist($where, $field='*', $limit, $order='UPDATE_TIME desc') {
		return M($this->posback)->alias('pb')
				->join(DB_PREFIX.$this->shop.' s on pb.SHOP_NO1 = s.SHOP_NO')
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
	public function addPosback($data) {
		$result = M($this->posback)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户终端添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updatePosback($where, $data) {
		$result = M($this->posback)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户终端修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delPosback($where) {
		$result = M($this->posback)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '商户终端删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息(也可用于获取最新POS_NO)
	* @post:
	**/
	public function findPosback($where, $field='*',$order='POS_NO DESC') {
		return M($this->posback)->where($where)
				->field($field)
				->order($order)
				->find();
	}

	/*
	* 获取单条信息(也可用于获取最新POS_NO)
	* @post:
	**/
	public function findPosbackmore($where, $field='*') {
		return M($this->posback)->alias('pb')
				->join(DB_PREFIX.$this->shop.' s on pb.SHOP_NO1 = s.SHOP_NO')
				->where($where)
				->field($field)
				->find();
	}
}
