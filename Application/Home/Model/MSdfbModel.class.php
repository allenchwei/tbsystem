<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MSdfb	商户代付银行帐户管理
// +----------------------------------------------------------------------
class MSdfbModel extends Model{
	
	function __construct(){
		$this->shop  	 = "shop";
		$this->partner 	 = "partner";
		$this->branch	 = "branch";
		$this->sdfb 	 = "sdfb";
		$this->sdfb_tmp  = "sdfb_tmp";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countSdfb($where) {
		return M($this->sdfb)->alias('sd')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->sdfb_tmp.' tmp on tmp.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getSdfblist($where, $field='sd.*', $limit, $order='tmp.PARTNER_MAP_ID desc') {
		return M($this->sdfb)->alias('sd')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->sdfb_tmp.' tmp on tmp.SHOP_MAP_ID = sd.SHOP_MAP_ID')
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
	public function addSdfb($data) {
		$result = M($this->sdfb)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户代付银行帐户管理添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateSdfb($where, $data) {
		$result = M($this->sdfb)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户代付银行帐户管理修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findSdfb($where, $field='*') {
		return M($this->sdfb)->where($where)->field($field)->find();
	}

	/*
	* 获取关联表单条信息
	* @post:
	**/
	public function findmoreSdfb($where, $field='sd.*') {
		return M($this->sdfb)->alias('sd')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = sd.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addSdfb_tmp($data) {
		$result = M($this->sdfb_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户代付银行帐户管理 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 更新tmp数据
	* @post:
	**/
	public function updateSdfb_tmp($where,$data) {
		$result = M($this->sdfb_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户代付银行帐户管理 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除tmp数据
	* @post:
	**/
	public function delSdfb_tmp($where) {
		$result = M($this->sdfb_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '商户代付银行帐户管理 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findSdfb_tmp($where, $field='*') {
		return M($this->sdfb_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmoreSdfb_tmp($where, $field='sd.*') {
		return M($this->sdfb_tmp)->alias('sd')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = sd.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countNotmpSdfb($where) {
		return M($this->sdfb)->alias('sd')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getNotmpSdfblist($where, $field='sd.*', $limit, $order='sd.PARTNER_MAP_ID desc') {
		return M($this->sdfb)->alias('sd')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sd.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->field($field.',DATE_FORMAT(sh.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
}
