<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MPosppp	通道终端映射
// +----------------------------------------------------------------------
class MPospppModel extends Model{
	function __construct(){
		$this->posppp = "posppp";
		$this->hshop  = "hshop";
		$this->host   = "host";
		$this->shop   = "shop";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countPosppp($where) {
		return M($this->posppp)->alias('po')
				->join(DB_PREFIX.$this->host.' h on h.HOST_MAP_ID=po.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hshop.' hs on hs.HSHOP_NO=po.HSHOP_NO')
				->join(DB_PREFIX.$this->shop.' s on s.SHOP_NO=po.SHOP_NO')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPosppplist($where, $field='po.*', $limit, $order='po.POSPPP_ID desc') {
		return M($this->posppp)->alias('po')
				->join(DB_PREFIX.$this->host.' h on h.HOST_MAP_ID=po.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hshop.' hs on hs.HSHOP_NO=po.HSHOP_NO')
				->join(DB_PREFIX.$this->shop.' s on s.SHOP_NO=po.SHOP_NO')
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
	public function addPosppp($data) {
		$result = M($this->posppp)->data($data)->add();
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
	public function updatePosppp($where, $data) {
		$result = M($this->posppp)->where($where)->save($data);
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
	public function delPosppp($where) {
		$result = M($this->posppp)->where($where)->delete();
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
	public function findPosppp($where, $field='po.*') {
		return M($this->posppp)->alias('po')
			->join(DB_PREFIX.$this->host.' h on h.HOST_MAP_ID=po.HOST_MAP_ID')
			->join(DB_PREFIX.$this->hshop.' hs on hs.HSHOP_NO=po.HSHOP_NO')
			->join(DB_PREFIX.$this->shop.' s on s.SHOP_NO=po.SHOP_NO')
			->field($field)
			->where($where)
			->find();
	}
}
