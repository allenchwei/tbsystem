<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MSmodel	商户终端
// +----------------------------------------------------------------------
class MSmodelModel extends Model{
	
	function __construct(){
		$this->smodel	= "smodel";
		$this->partner	= "partner";
		$this->shop		= "shop";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countSmodel($where) {
		return M($this->smodel)->alias('sm')
				->join(DB_PREFIX.$this->partner.' a on p.PARTNER_MAP_ID = a.PARTNER_MAP_ID')
				->join(DB_PREFIX.$this->shop.' s on p.SHOP_MAP_ID = s.SHOP_MAP_ID')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getSmodellist($where, $field='*', $limit, $order='POS_INDEX desc') {
		return M($this->smodel)->alias('p')
				->join(DB_PREFIX.$this->partner.' a on p.PARTNER_MAP_ID = a.PARTNER_MAP_ID')
				->join(DB_PREFIX.$this->shop.' s on p.SHOP_MAP_ID = s.SHOP_MAP_ID')
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addSmodel($data) {
		$result = M($this->smodel)->data($data)->add();
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
	public function updateSmodel($where, $data) {
		$result = M($this->smodel)->where($where)->save($data);
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
	public function delSmodel($where) {
		$result = M($this->smodel)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '商户终端删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findSmodel($where, $field='*') {
		return M($this->smodel)->where($where)
				->field($field)
				->find();
	}
}
