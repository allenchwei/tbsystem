<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MAmdr	代理商证件管理
// +----------------------------------------------------------------------
class MAmdrModel extends Model{
	
	function __construct(){
		$this->partner 	= "partner";
		$this->amdr  	= "amdr";
		$this->amdr_tmp = "amdr_tmp";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countAmdr($where) {
		return M($this->amdr)->alias('am')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = am.PARTNER_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->amdr_tmp.' tmp on tmp.PARTNER_MAP_ID = am.PARTNER_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getAmdrlist($where, $field='am.*,ag.*', $limit, $order='am.PARTNER_MAP_ID desc') {
		return M($this->amdr)->alias('am')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = am.PARTNER_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->amdr_tmp.' tmp on tmp.AMDR_ID = am.AMDR_ID')
				->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 批量添加
	* @post:
	**/
	public function addAllAmdr($data) {
		$result = M($this->amdr)->addAll($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '代理商证件添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 添加
	* @post:
	**/
	public function addAmdr($data) {
		$result = M($this->amdr)->data($data)->add();
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
	public function updateAmdr($where, $data) {
		$result = M($this->amdr)->where($where)->save($data);
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
	public function findAmdr($where, $field='*') {
		return M($this->amdr)->where($where)->field($field)->find();
	}

	/*
	* 获取关联单条信息
	* @post:
	**/
	public function findmoreAmdr($where, $field='am.*') {
		return M($this->amdr)->alias('am')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = am.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addAmdr_tmp($data) {
		$result = M($this->amdr_tmp)->data($data)->add();
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
	public function updateAmdr_tmp($where,$data) {
		$result = M($this->amdr_tmp)->where($where)->save($data);
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
	public function delAmdr_tmp($where) {
		$result = M($this->amdr_tmp)->where($where)->delete();
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
	public function findAmdr_tmp($where, $field='*') {
		return M($this->amdr_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmoreAmdr_tmp($where, $field='ac.*') {
		return M($this->amdr_tmp)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}
}
