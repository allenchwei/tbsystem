<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MPauth	代理商权限管理
// +----------------------------------------------------------------------
class MPauthModel extends Model{
	
	function __construct(){
		$this->partner 	 = "partner";
		$this->pauth 	 = "pauth";
		$this->pauth_tmp = "pauth_tmp";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countPauth($where) {
		return M($this->pauth)->alias('aa')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = aa.PARTNER_MAP_ID')
				->where($where.' and ag.PARTNER_STATUS = 0')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPauthlist($where, $field='*', $limit, $order='tmp.PARTNER_MAP_ID desc') {
		return M($this->pauth)->alias('aa')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = aa.PARTNER_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->pauth_tmp.' tmp on tmp.PARTNER_MAP_ID = aa.PARTNER_MAP_ID')
				->where($where.' and ag.PARTNER_STATUS = 0')
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addPauth($data) {
		$result = M($this->pauth)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '代理商权限添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updatePauth($where, $data) {
		$result = M($this->pauth)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '代理商权限修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findPauth($where, $field='*') {
		return M($this->pauth)->where($where)->field($field)->find();
	}

	/*
	* 获取关联表单条信息
	* @post:
	**/
	public function findmorePauth($where, $field='aa.*') {
		return M($this->pauth)->alias('aa')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = aa.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addPauth_tmp($data) {
		$result = M($this->pauth_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '代理商权限 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 更新tmp数据
	* @post:
	**/
	public function updatePauth_tmp($where,$data) {
		$result = M($this->pauth_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '代理商权限 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除tmp数据
	* @post:
	**/
	public function delPauth_tmp($where) {
		$result = M($this->pauth_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '代理商权限 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findPauth_tmp($where, $field='*') {
		return M($this->pauth_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmorePauth_tmp($where, $field='ac.*') {
		return M($this->pauth_tmp)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}
}
