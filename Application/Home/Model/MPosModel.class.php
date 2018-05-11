<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MPos	商户终端
// +----------------------------------------------------------------------
class MPosModel extends Model{
	
	function __construct(){
		$this->pos		= "pos";
		$this->partner	= "partner";
		$this->shop		= "shop";
		$this->device	= "device";
		$this->model	= "model";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countPos($where) {
		return M($this->pos)->alias('p')
				->join(DB_PREFIX.$this->partner.' a on p.PARTNER_MAP_ID = a.PARTNER_MAP_ID')
				->join(DB_PREFIX.$this->shop.' s on p.SHOP_MAP_ID = s.SHOP_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPoslist($where, $field='*', $limit, $order='POS_INDEX desc') {
		return M($this->pos)->alias('p')
				->join(DB_PREFIX.$this->partner.' a on p.PARTNER_MAP_ID = a.PARTNER_MAP_ID')
				->join(DB_PREFIX.$this->shop.' s on p.SHOP_MAP_ID = s.SHOP_MAP_ID')
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
	public function addPos($data) {
		$result = M($this->pos)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户终端添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！",'POS_ID'=>$result);
	}

	/*
	* 修改
	* @post:
	**/
	public function updatePos($where, $data) {
		$result = M($this->pos)->where($where)->save($data);
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
	public function delPos($where) {
		$result = M($this->pos)->where($where)->delete();
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
	public function findPos($where, $field='*',$order='POS_NO DESC') {
		return M($this->pos)->where($where)
				->field($field)
				->order($order)
				->find();
	}

	/*
	* 获取单条信息(也可用于获取最新POS_NO)
	* @post:
	**/
	public function findPosmore($where, $field='*') {
		return M($this->pos)->alias('p')
				->join(DB_PREFIX.$this->device.' d on d.DEVICE_SN = p.DEVICE_SN')
				->join(DB_PREFIX.$this->model.' m on m.MODEL_MAP_ID = d.MODEL_MAP_ID','LEFT')
				->where($where)
				->field($field)
				->find();
	}
}
