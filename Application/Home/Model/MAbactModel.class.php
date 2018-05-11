<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MAbact	代理商证件管理
// +----------------------------------------------------------------------
class MAbactModel extends Model{
	
	function __construct(){
		$this->agent = "agent";
		$this->abact = "abact";
		$this->abact_tmp = "abact_tmp";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countAbact($where) {
		return M($this->abact)->alias('ab')
				->join(DB_PREFIX.$this->agent.' ag on ag.AGENT_MAP_ID = ab.AGENT_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getAbactlist($where, $field='*', $limit, $order='tmp.AGENT_MAP_ID desc,ab.AGENT_MAP_ID desc') {
		return M($this->abact)->alias('ab')
				->join(DB_PREFIX.$this->agent.' ag on ag.AGENT_MAP_ID = ab.AGENT_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->abact_tmp.' tmp on tmp.AGENT_MAP_ID = ab.AGENT_MAP_ID')
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
	public function addAbact($data) {
		$result = M($this->abact)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '代理商证件添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateAbact($where, $data) {
		$result = M($this->abact)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '代理商证件修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findAbact($where, $field='*') {
		return M($this->abact)->where($where)->field($field)->find();
	}
	/*
	* 获取单条信息
	* @post:
	**/
	public function findmoreAbact($where, $field='ab.*') {
		return M($this->abact)->alias('ab')
				->join(DB_PREFIX.$this->agent.' ag on ag.AGENT_MAP_ID = ab.AGENT_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

	//以下为tmp表MODEL用于数据变更====================================================================================

	/*
	* 添加tmp数据
	* @post:
	**/
	public function addAbact_tmp($data) {
		$result = M($this->abact_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '代理商证件 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 更新tmp数据
	* @post:
	**/
	public function updateAbact_tmp($where,$data) {
		$result = M($this->abact_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '代理商证件 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除tmp数据
	* @post:
	**/
	public function delAbact_tmp($where) {
		$result = M($this->abact_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '代理商证件 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findAbact_tmp($where, $field='*') {
		return M($this->abact_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmoreAbact_tmp($where, $field='ac.*') {
		return M($this->abact_tmp)->alias('ac')
				->join(DB_PREFIX.$this->agent.' ag on ag.AGENT_MAP_ID = ac.AGENT_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}
}
