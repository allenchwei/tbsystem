<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MScfg	商户其他配置信息
// +----------------------------------------------------------------------
class MScfgModel extends Model{
	
	function __construct(){
		$this->shop  	 = "shop";
		$this->partner 	 = "partner";
		$this->branch	 = "branch";
		$this->scfg 	 = "scfg";
		$this->scfg_tmp  = "scfg_tmp";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countNotmpScfg($where) {
		return M($this->scfg)->alias('sd')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getNotmpScfglist($where, $field='sd.*', $limit, $order='sh.PARTNER_MAP_ID desc') {
		return M($this->scfg)->alias('sd')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(sh.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	//------------------------------------------

	/*
	* 获取统计数量
	* @post:
	**/
	public function countScfg($where) {
		return M($this->scfg)->alias('sd')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->scfg_tmp.' tmp on tmp.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getScfglist($where, $field='sd.*', $limit, $order='sh.PARTNER_MAP_ID desc') {
		return M($this->scfg)->alias('sd')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->scfg_tmp.' tmp on tmp.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(sh.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addScfg($data) {
		$result = M($this->scfg)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户其他配置信息添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateScfg($where, $data) {
		$result = M($this->scfg)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户其他配置信息修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findScfg($where, $field='*') {
		return M($this->scfg)->where($where)->field($field)->find();
	}

	/*
	* 获取关联表单条信息
	* @post:
	**/
	public function findmoreScfg($where, $field='sd.*') {
		return M($this->scfg)->alias('sd')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = sd.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}


	/*
	* 获取商户积分列表
	* @post:
	**/
	public function getScfglist_2($where, $field='sd.*', $limit, $order='sh.SHOP_MAP_ID desc') {
		return M($this->scfg)->alias('sd')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(sh.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	/*
	* 获取统计数量
	* @post:
	**/
	public function countScfg_2($where) {
		return M($this->scfg)->alias('sd')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where)
				->count();
	}
	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addScfg_tmp($data) {
		$result = M($this->scfg_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户其他配置信息 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 更新tmp数据
	* @post:
	**/
	public function updateScfg_tmp($where,$data) {
		$result = M($this->scfg_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户其他配置信息 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除tmp数据
	* @post:
	**/
	public function delScfg_tmp($where) {
		$result = M($this->scfg_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '商户其他配置信息 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findScfg_tmp($where, $field='*') {
		return M($this->scfg_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmoreScfg_tmp($where, $field='sd.*') {
		return M($this->scfg_tmp)->alias('sd')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = sd.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

}
