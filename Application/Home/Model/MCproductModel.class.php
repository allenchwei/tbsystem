<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MCproduct	卡套餐
// +----------------------------------------------------------------------
class MCproductModel extends Model{
	
	function __construct(){
		$this->cproduct 	= "cproduct";
		$this->cardlevel 	= "cardlevel";
		$this->cardtype 	= "cardtype";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countCproduct($where) {
		return M($this->cproduct)->alias('c')
				->join(DB_PREFIX.$this->cardlevel.' l on c.CARD_LEVEL = l.CLEVEL_ID', 'LEFT')
				->join(DB_PREFIX.$this->cardtype.' t on c.CARD_TYPE = t.CTYPE_ID', 'LEFT')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getCproductlist($where, $field='*', $limit, $order='c.CARD_P_MAP_ID desc') {
		return M($this->cproduct)->alias('c')
				->join(DB_PREFIX.$this->cardlevel.' l on c.CARD_LEVEL = l.CLEVEL_ID', 'LEFT')
				->join(DB_PREFIX.$this->cardtype.' t on c.CARD_TYPE = t.CTYPE_ID', 'LEFT')
				->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 获取列表	单表不关联
	* @post:
	**/
	public function getCproductlist_one($where, $field='*', $limit, $order='CARD_P_MAP_ID desc') {
		return M($this->cproduct)
				->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateCproduct($where, $data) {
		$result = M($this->cproduct)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '卡套餐修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findCproduct($where, $field='*') {
		return M($this->cproduct)->alias('c')
				->join(DB_PREFIX.$this->cardlevel.' l on c.CARD_LEVEL = l.CLEVEL_ID', 'LEFT')
				->join(DB_PREFIX.$this->cardtype.' t on c.CARD_TYPE = t.CTYPE_ID', 'LEFT')
				->where($where)
				->field($field)
				->find();
	}
	
	/*
	* 获取单条信息	单表不关联
	* @post:
	**/
	public function findCproduct_one($where, $field='*') {
		return M($this->cproduct)->where($where)
				->field($field)
				->find();
	}
}
