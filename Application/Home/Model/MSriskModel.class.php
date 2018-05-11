<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MSrisk	商户风险评级
// +----------------------------------------------------------------------
class MSriskModel extends Model{
	
	function __construct(){
		$this->shop  	 = "shop";
		$this->partner 	 = "partner";
		$this->branch	 = "branch";
		$this->srisk 	 = "srisk";
		$this->srisk_tmp = "srisk_tmp";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countSrisk($where) {
		return M($this->srisk)->alias('sr')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sr.SHOP_MAP_ID')
				->join(DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID', 'LEFT')
				->join(DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID', 'LEFT')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getSrisklist($where, $field='sr.*', $limit, $order='sr.SHOP_MAP_ID desc') {
		return M($this->srisk)->alias('sr')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sr.SHOP_MAP_ID')
				->join(DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID', 'LEFT')
				->join(DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID', 'LEFT')
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
	public function addSrisk($data) {
		$result = M($this->srisk)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户风险评级添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateSrisk($where, $data) {
		$result = M($this->srisk)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户风险评级修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findSrisk($where, $field='*') {
		return M($this->srisk)->where($where)->field($field)->find();
	}

	/*
	* 获取关联表单条信息
	* @post:
	**/
	public function findmoreSrisk($where, $field='sr.*') {
		return M($this->srisk)->alias('sr')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sr.SHOP_MAP_ID')
				->join(DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID', 'LEFT')
				->join(DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID', 'LEFT')
				->where($where)
				->field($field)
				->find();
	}

	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addSrisk_tmp($data) {
		$result = M($this->srisk_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户风险评级 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 更新tmp数据
	* @post:
	**/
	public function updateSrisk_tmp($where,$data) {
		$result = M($this->srisk_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户风险评级 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除tmp数据
	* @post:
	**/
	public function delSrisk_tmp($where) {
		$result = M($this->srisk_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '商户风险评级 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findSrisk_tmp($where, $field='*') {
		return M($this->srisk_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmoreSrisk_tmp($where, $field='sr.*') {
		return M($this->srisk_tmp)->alias('sr')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = sr.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

}
