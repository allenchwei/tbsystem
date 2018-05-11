<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MPcfg	其他配置
// +----------------------------------------------------------------------
class MPcfgModel extends Model{
	
	function __construct(){
		$this->partner 	= "partner";
		$this->pcfg  	= "pcfg";
		$this->pcfg_tmp = "pcfg_tmp";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countPcfg($where) {
		return M($this->pcfg)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->pcfg_tmp.' tmp on tmp.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPcfglist($where, $field='ac.*', $limit, $order='ac.PARTNER_MAP_ID desc') {
		return M($this->pcfg)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->pcfg_tmp.' tmp on tmp.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
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
	public function addPcfg($data) {
		$result = M($this->pcfg)->data($data)->add();
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
	public function updatePcfg($where, $data) {
		$result = M($this->pcfg)->where($where)->save($data);
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
	public function findPcfg($where, $field='*') {
		return M($this->pcfg)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条信息
	* @post:
	**/
	public function findmorePcfg($where, $field='ac.*') {
		return M($this->pcfg)->alias('ac')
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
	public function addPcfg_tmp($data) {
		$result = M($this->pcfg_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '合作方其他配置信息 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 更新tmp数据
	* @post:
	**/
	public function updatePcfg_tmp($where,$data) {
		$result = M($this->pcfg_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '合作方其他配置信息 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除tmp数据
	* @post:
	**/
	public function delPcfg_tmp($where) {
		$result = M($this->pcfg_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '合作方其他配置信息 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findPcfg_tmp($where, $field='*') {
		return M($this->pcfg_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmorePcfg_tmp($where, $field='sd.*') {
		return M($this->pcfg_tmp)->alias('sd')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = sd.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}
}
