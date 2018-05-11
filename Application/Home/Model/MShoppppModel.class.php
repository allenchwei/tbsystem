<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MShopppp	商户映射
// +----------------------------------------------------------------------
class MShoppppModel extends Model{
	function __construct(){
		$this->shopppp = "shopppp";
		$this->hshop  = "hshop";
		$this->host   = "host";
		$this->shop   = "shop";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countShopppp($where) {
		return M($this->shopppp)->alias('sh')
				->join(DB_PREFIX.$this->hshop.' hs on hs.HSHOP_NO=sh.HSHOP_NO')
				->join(DB_PREFIX.$this->shop.' s on s.SHOP_NO=sh.SHOP_NO')
				->join(DB_PREFIX.$this->host.' h on h.HOST_MAP_ID=sh.HOST_MAP_ID')
				->field($field)
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getShopppplist($where, $field='sh.*', $limit, $order='sh.SHOPPPP_ID desc') {
		return M($this->shopppp)->alias('sh')
				->join(DB_PREFIX.$this->hshop.' hs on hs.HSHOP_NO=sh.HSHOP_NO')
				->join(DB_PREFIX.$this->shop.' s on s.SHOP_NO=sh.SHOP_NO')
				->join(DB_PREFIX.$this->host.' h on h.HOST_MAP_ID=sh.HOST_MAP_ID')
				->field($field)
				->where($where)
				->limit($limit)
				->order($order)
				->select();
	}	
	
	/*
	* 添加
	* @post:
	**/
	public function addShopppp($data) {
		$result = M($this->shopppp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateShopppp($where, $data) {
		$result = M($this->shopppp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 删除
	* @post:
	**/
	public function delShopppp($where) {
		$result = M($this->shopppp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findShopppp($where, $field='sh.*') {
		return M($this->shopppp)->alias('sh')
				->join(DB_PREFIX.$this->hshop.' hs on hs.HSHOP_NO=sh.HSHOP_NO')
				->join(DB_PREFIX.$this->shop.' s on s.SHOP_NO=sh.SHOP_NO')
				->field($field)
				->where($where)
				->find();
	}
}
