<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MAcls	合作伙伴结算方式
// +----------------------------------------------------------------------
class MAclsModel extends Model{
	
	function __construct(){
		$this->agent = "agent";
		$this->acls  = "acls";
		$this->acls_tmp  = "acls_tmp";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countAcls($where) {
		return M($this->acls)->alias('ac')
				->join(DB_PREFIX.$this->agent.' ag on ag.AGENT_MAP_ID = ac.AGENT_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->acls_tmp.' tmp on tmp.AGENT_MAP_ID = ac.AGENT_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getAclslist($where, $field='ac.*', $limit, $order='tmp.AGENT_MAP_ID desc,ac.AGENT_MAP_ID desc') {
		return M($this->acls)->alias('ac')
				->join(DB_PREFIX.$this->agent.' ag on ag.AGENT_MAP_ID = ac.AGENT_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->acls_tmp.' tmp on tmp.AGENT_MAP_ID = ac.AGENT_MAP_ID')
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
	public function addAcls($data) {
		$result = M($this->acls)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '合作伙伴结算方式添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateAcls($where, $data) {
		$result = M($this->acls)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '合作伙伴结算方式修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findAcls($where, $field='*') {
		return M($this->acls)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条信息
	* @post:
	**/
	public function findmoreAcls($where, $field='ac.*') {
		return M($this->acls)->alias('ac')
				->join(DB_PREFIX.$this->agent.' ag on ag.AGENT_MAP_ID = ac.AGENT_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}


	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addAcls_tmp($data) {
		$result = M($this->acls_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '合作伙伴结算方式 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 更新tmp数据
	* @post:
	**/
	public function updateAcls_tmp($where,$data) {
		$result = M($this->acls_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '合作伙伴结算方式 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除tmp数据
	* @post:
	**/
	public function delAcls_tmp($where) {
		$result = M($this->acls_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '合作伙伴结算方式 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findAcls_tmp($where, $field='*') {
		return M($this->acls_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmoreAcls_tmp($where, $field='ac.*') {
		return M($this->acls_tmp)->alias('ac')
				->join(DB_PREFIX.$this->agent.' ag on ag.AGENT_MAP_ID = ac.AGENT_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}
}
