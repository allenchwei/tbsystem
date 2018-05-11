<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MPbact	代理商证件管理
// +----------------------------------------------------------------------
class MPbactModel extends Model{
	
	function __construct(){
		$this->partner = "partner";
		$this->pbact = "pbact";
		$this->pbact_tmp = "pbact_tmp";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countPbact($where) {
		return M($this->pbact)->alias('ab')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ab.PARTNER_MAP_ID')
				->where($where.' and ag.PARTNER_STATUS = 0')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPbactlist($where, $field='*', $limit, $order='tmp.PARTNER_MAP_ID desc,ab.PARTNER_MAP_ID desc') {
		return M($this->pbact)->alias('ab')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ab.PARTNER_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->pbact_tmp.' tmp on tmp.PARTNER_MAP_ID = ab.PARTNER_MAP_ID')
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
	public function addPbact($data) {
		$result = M($this->pbact)->data($data)->add();
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
	public function updatePbact($where, $data) {
		$result = M($this->pbact)->where($where)->save($data);
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
	public function findPbact($where, $field='*') {
		return M($this->pbact)->where($where)->field($field)->find();
	}
	/*
	* 获取单条信息
	* @post:
	**/
	public function findmorePbact($where, $field='ab.*') {
		return M($this->pbact)->alias('ab')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ab.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

	//以下为tmp表MODEL用于数据变更====================================================================================

	/*
	* 添加tmp数据
	* @post:
	**/
	public function addPbact_tmp($data) {
		$result = M($this->pbact_tmp)->data($data)->add();
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
	public function updatePbact_tmp($where,$data) {
		$result = M($this->pbact_tmp)->where($where)->save($data);
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
	public function delPbact_tmp($where) {
		$result = M($this->pbact_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '通代理商证件 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findPbact_tmp($where, $field='*') {
		return M($this->pbact_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmorePbact_tmp($where, $field='ac.*') {
		return M($this->pbact_tmp)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}
}
