<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MVipmarket	产品推广
// +----------------------------------------------------------------------
class MVipmarketModel extends Model{
	
	function __construct(){
		$this->vipmarket 	= "vipmarket";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countVipmarket($where) {
		return M($this->vipmarket)->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getVipmarketlist($where, $field='*', $limit, $order='MARKET_ID desc') {
		return M($this->vipmarket)->where($where)
				->field($field.',DATE_FORMAT(BEGIN_DATE,"%Y-%m-%d") AS BEGIN_DATE, DATE_FORMAT(BEGIN_END,"%Y-%m-%d") AS BEGIN_END')
				->limit($limit)
				->order($order)
				->select();
	}
		
	/*
	* 添加
	* @post:
	**/
	public function addVipmarket($data) {
		$result = M($this->vipmarket)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '产品推广添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateVipmarket($where, $data) {
		$result = M($this->vipmarket)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '产品推广修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 删除
	* @post:
	**/
	public function delVipmarket($where) {
		$result = M($this->vipmarket)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '产品推广删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findVipmarket($where, $field='*') {
		return M($this->vipmarket)->where($where)				
				->field($field.',DATE_FORMAT(BEGIN_DATE,"%Y-%m-%d") AS BEGIN_DATE, DATE_FORMAT(BEGIN_END,"%Y-%m-%d") AS BEGIN_END')
				->find();
	}
}
