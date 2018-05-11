<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MAauth	代理商权限管理
// +----------------------------------------------------------------------
class MAauthModel extends Model{
	
	function __construct(){
		$this->agent = "agent";
		$this->aauth = "aauth";
		$this->aauth_tmp = "aauth_tmp";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countAauth($where) {
		return M($this->aauth)->alias('aa')
				->join(DB_PREFIX.$this->agent.' ag on ag.AGENT_MAP_ID = aa.AGENT_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getAauthlist($where, $field='*', $limit, $order='tmp.AGENT_MAP_ID desc') {
		return M($this->aauth)->alias('aa')
				->join(DB_PREFIX.$this->agent.' ag on ag.AGENT_MAP_ID = aa.AGENT_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->aauth_tmp.' tmp on tmp.AGENT_MAP_ID = aa.AGENT_MAP_ID')
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
	public function addAauth($data) {
		$result = M($this->aauth)->data($data)->add();
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
	public function updateAauth($where, $data) {
		$result = M($this->aauth)->where($where)->save($data);
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
	public function findAauth($where, $field='*') {
		return M($this->aauth)->where($where)->field($field)->find();
	}

	/*
	* 获取关联表单条信息
	* @post:
	**/
	public function findmoreAauth($where, $field='aa.*') {
		return M($this->aauth)->alias('aa')
				->join(DB_PREFIX.$this->agent.' ag on ag.AGENT_MAP_ID = aa.AGENT_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addAauth_tmp($data) {
		$result = M($this->aauth_tmp)->data($data)->add();
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
	public function updateAauth_tmp($where,$data) {
		$result = M($this->aauth_tmp)->where($where)->save($data);
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
	public function delAauth_tmp($where) {
		$result = M($this->aauth_tmp)->where($where)->delete();
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
	public function findAauth_tmp($where, $field='*') {
		return M($this->aauth_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmoreAauth_tmp($where, $field='ac.*') {
		return M($this->aauth_tmp)->alias('ac')
				->join(DB_PREFIX.$this->agent.' ag on ag.AGENT_MAP_ID = ac.AGENT_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}
}
