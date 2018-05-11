<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MSmdr	商户扣率管理
// +----------------------------------------------------------------------
class MSmdrModel extends Model{
	
	function __construct(){
		$this->shop  	 = "shop";
		$this->partner 	 = "partner";
		$this->branch	 = "branch";
		$this->smdr 	 = "smdr";
		$this->smdr_tmp  = "smdr_tmp";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countNotmpgroupSmdr($where,$group="COUNT(DISTINCT sm.SHOP_MAP_ID) as total") {
		return M($this->smdr)->alias('sm')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sm.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->field($group)
				->find();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getNotmpSmdrlist($where, $field='sm.*', $limit, $order='sm.SHOP_MAP_ID desc', $group="") {
		return M($this->smdr)->alias('sm')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sm.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(sh.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->group($group)
				->select();
	}
	
	//---------------------------------------------------------
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countSmdr($where) {
		return M($this->smdr)->alias('sm')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sm.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->smdr_tmp.' tmp on tmp.SHOP_MAP_ID = sm.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->count();
	}
	/*
	* 获取统计数量
	* @post:
	**/
	public function countgroupSmdr($where,$group="COUNT(DISTINCT sm.SHOP_MAP_ID) as total") {
		return M($this->smdr)->alias('sm')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sm.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->smdr_tmp.' tmp on tmp.SHOP_MAP_ID = sm.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				// ->join('LEFT JOIN '.db2_prepare(connection, statement)IX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->field($group)
				->find();
	}

	/*
	* 获取单个商户扣率组
	* @post:
	**/
	public function getSmdrfind($where, $field='sm.*', $limit, $order='sm.SHOP_MAP_ID desc', $group="") {
		return M($this->smdr)->alias('sm')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sm.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(sh.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->group($group)
				->select();
	}
	/*
	* 获取列表
	* @post:
	**/
	public function getSmdrlist($where, $field='sm.*', $limit, $order='tmp.SHOP_MAP_ID desc', $group="") {
		return M($this->smdr)->alias('sm')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sm.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->smdr_tmp.' tmp on tmp.SHOP_MAP_ID = sm.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(sh.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->group($group)
				->select();
	}

	/*
	* 获取列表
	* @post:
	**/
	public function getSmdrtmplist($where, $field='tmp.*', $limit, $order='tmp.SHOP_MAP_ID desc') {
		return M($this->smdr_tmp)->alias('tmp')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = tmp.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
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
	public function addSmdr($data) {
		$result = M($this->smdr)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户扣率添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateSmdr($where, $data) {
		$result = M($this->smdr)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户扣率修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findSmdr($where, $field='*') {
		return M($this->smdr)->where($where)->field($field)->find();
	}

	/*
	* 获取关联表单条信息
	* @post:
	**/
	public function findmoreSmdr($where, $field='sm.*') {
		return M($this->smdr)->alias('sm')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sm.SHOP_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addSmdr_tmp($data) {
		$result = M($this->smdr_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户扣率 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 更新tmp数据
	* @post:
	**/
	public function updateSmdr_tmp($where,$data) {
		$result = M($this->smdr_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户扣率 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除tmp数据
	* @post:
	**/
	public function delSmdr_tmp($where) {
		$result = M($this->smdr_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '商户扣率 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findSmdr_tmp($where, $field='*') {
		return M($this->smdr_tmp)->where($where)->field($field)->find();
	}
}
