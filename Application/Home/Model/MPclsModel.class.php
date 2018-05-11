<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MPcls	合作伙伴结算方式
// +----------------------------------------------------------------------
class MPclsModel extends Model{
	
	function __construct(){
		$this->partner = "partner";
		$this->pcls  = "pcls";
		$this->pcls_tmp  = "pcls_tmp";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countPcls($where) {
		return M($this->pcls)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->pcls_tmp.' tmp on tmp.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->where($where.' and ag.PARTNER_STATUS = 0')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPclslist($where, $field='ac.*', $limit, $order='tmp.PARTNER_MAP_ID desc,ac.PARTNER_MAP_ID desc') {
		return M($this->pcls)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->pcls_tmp.' tmp on tmp.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
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
	public function addPcls($data) {
		$result = M($this->pcls)->data($data)->add();
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
	public function updatePcls($where, $data) {
		$result = M($this->pcls)->where($where)->save($data);
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
	public function findPcls($where, $field='*') {
		return M($this->pcls)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条信息
	* @post:
	**/
	public function findmorePcls($where, $field='ac.*') {
		return M($this->pcls)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}


	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addPcls_tmp($data) {
		$result = M($this->pcls_tmp)->data($data)->add();
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
	public function updatePcls_tmp($where,$data) {
		$result = M($this->pcls_tmp)->where($where)->save($data);
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
	public function delPcls_tmp($where) {
		$result = M($this->pcls_tmp)->where($where)->delete();
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
	public function findPcls_tmp($where, $field='*') {
		return M($this->pcls_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmorePcls_tmp($where, $field='ac.*') {
		return M($this->pcls_tmp)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}
}
