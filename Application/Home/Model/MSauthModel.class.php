<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MSauth	商户权限管理
// +----------------------------------------------------------------------
class MSauthModel extends Model{
	
	function __construct(){
		$this->sauth = "sauth";
		$this->shop  = "shop";
		$this->sauth_tmp = "sauth_tmp";
		$this->partner = "partner";
		$this->branch = "branch";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countNotmpSauth($where) {
		return M($this->sauth)->alias('sa')->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sa.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getNotmpSauthlist($where, $field='sa.*', $limit, $order='sa.SHOP_MAP_ID desc') {
		 return M($this->sauth)->alias('sa')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sa.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->field($field.',DATE_FORMAT(sh.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	//-------------------------------------

	/*
	* 获取统计数量
	* @post:
	**/
	public function countSauth($where) {
		return M($this->sauth)->alias('sa')->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sa.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->sauth_tmp.' tmp on tmp.SHOP_MAP_ID = sa.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getSauthlist($where, $field='sa.*', $limit, $order='tmp.SHOP_MAP_ID desc') {
		 return M($this->sauth)->alias('sa')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sa.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->sauth_tmp.' tmp on tmp.SHOP_MAP_ID = sa.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
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
	public function addSauth($data) {
		$result = M($this->sauth)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户权限添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateSauth($where, $data) {
		$result = M($this->sauth)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户权限修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findSauth($where, $field='*') {
		return M($this->sauth)->where($where)->field($field)->find();
	}

	/*
	* 获取关联表单条信息
	* @post:
	**/
	public function findmoreSauth($where, $field='sa.*') {
		return M($this->sauth)->alias('sa')
				->join(DB_PREFIX.$this->shop.' ag on ag.SHOP_MAP_ID = sa.SHOP_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addSauth_tmp($data) {
		$result = M($this->sauth_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户权限 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 更新tmp数据
	* @post:
	**/
	public function updateSauth_tmp($where,$data) {
		$result = M($this->sauth_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户权限 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除tmp数据
	* @post:
	**/
	public function delSauth_tmp($where) {
		$result = M($this->sauth_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '商户权限 tmo 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findSauth_tmp($where, $field='*') {
		return M($this->sauth_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmoreSauth_tmp($where, $field='sa.*') {
		return M($this->sauth_tmp)->alias('sa')
				->join(DB_PREFIX.$this->shop.' ag on ag.SHOP_MAP_ID = sa.SHOP_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

}
