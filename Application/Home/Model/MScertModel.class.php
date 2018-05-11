<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MScert	商户证件管理
// +----------------------------------------------------------------------
class MScertModel extends Model{
	
	function __construct(){
		$this->shop  	 = "shop";
		$this->partner 	 = "partner";
		$this->branch	 = "branch";
		$this->scert 	 = "scert";
		$this->scert_tmp = "scert_tmp";
	}
	
	/*
	* 获取统计数量	新
	* @post:
	**/
	public function countNewsScert($where) {
		return M($this->scert)->where($where)->count();
	}
	
	/*
	* 获取列表		新
	* @post:
	**/
	public function getNewsScertlist($where, $field='*', $limit, $order='SHOP_MAP_ID desc') {
		return M($this->scert)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
	
	/*
	* 获取统计数量	gzy
	* @post:
	**/
	public function countNotmpScert($where) {
		return M($this->scert)->alias('sc')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sc.SHOP_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->count();
	}
	
	/*
	* 获取列表		gzy
	* @post:
	**/
	public function getNotmpScertlist($where, $field='sc.*', $limit, $order='sc.SHOP_MAP_ID desc') {
		return M($this->scert)->alias('sc')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sc.SHOP_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->field($field.',DATE_FORMAT(sh.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	
	//----------------------------------------------
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countScert($where) {
		return M($this->scert)->alias('sc')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sc.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->scert_tmp.' tmp on tmp.SHOP_MAP_ID = sc.SHOP_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getScertlist($where, $field='sc.*', $limit, $order='tmp.SHOP_MAP_ID desc') {
		return M($this->scert)->alias('sc')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sc.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->scert_tmp.' tmp on tmp.SHOP_MAP_ID = sc.SHOP_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->field($field.',DATE_FORMAT(sh.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addScert($data) {
		$result = M($this->scert)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户证件添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateScert($where, $data) {
		$result = M($this->scert)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户证件修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findScert($where, $field='*') {
		return M($this->scert)
				->where($where)
				->field($field)
				->find();
	}

	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addScert_tmp($data) {
		$result = M($this->scert_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户证件 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 更新tmp数据
	* @post:
	**/
	public function updateScert_tmp($where,$data) {
		$result = M($this->scert_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户证件 tmp 修过成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除tmp数据
	* @post:
	**/
	public function delScert_tmp($where) {
		$result = M($this->scert_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '商户证件 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findScert_tmp($where, $field='*') {
		return M($this->scert_tmp)
				->where($where)
				->field($field)
				->find();
	}
}
