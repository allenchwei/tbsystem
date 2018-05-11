<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MSbact	商户银行帐户管理
// +----------------------------------------------------------------------
class MSbactModel extends Model{
	
	function __construct(){
		$this->shop  	 = "shop";
		$this->partner 	 = "partner";
		$this->branch	 = "branch";
		$this->sbact 	 = "sbact";
		$this->sbact_tmp = "sbact_tmp";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countNotmpSbact($where) {
		return M($this->sbact)->alias('sb')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sb.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getNotmpSbactlist($where, $field='sb.*', $limit, $order='sb.PARTNER_MAP_ID desc') {
		return M($this->sbact)->alias('sb')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sb.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->field($field.',DATE_FORMAT(sh.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	//-------------------------------------------
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countSbact($where) {
		return M($this->sbact)->alias('sb')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sb.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->sbact_tmp.' tmp on tmp.SHOP_MAP_ID = sb.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on b.BRANCH_MAP_ID = sh.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on a.PARTNER_MAP_ID = sh.PARTNER_MAP_ID')
				->where($where.' and sh.SHOP_STATUS = 0')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getSbactlist($where, $field='sb.*', $limit, $order='tmp.PARTNER_MAP_ID desc') {
		return M($this->sbact)->alias('sb')
				->join(DB_PREFIX.$this->shop.' sh on sh.SHOP_MAP_ID = sb.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->sbact_tmp.' tmp on tmp.SHOP_MAP_ID = sb.SHOP_MAP_ID')
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
	public function addSbact($data) {
		$result = M($this->sbact)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户银行帐户添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateSbact($where, $data) {
		$result = M($this->sbact)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户银行帐户修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findSbact($where, $field='*') {
		return M($this->sbact)->where($where)->field($field)->find();
	}

	/*
	* 获取关联表单条信息
	* @post:
	**/
	public function findmoreSbact($where, $field='sb.*') {
		return M($this->sbact)->alias('sb')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = sb.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addSbact_tmp($data) {
		$result = M($this->sbact_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户银行帐户 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 更新tmp数据
	* @post:
	**/
	public function updateSbact_tmp($where,$data) {
		$result = M($this->sbact_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户银行帐户 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除tmp数据
	* @post:
	**/
	public function delSbact_tmp($where) {
		$result = M($this->sbact_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '商户银行帐户 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findSbact_tmp($where, $field='*') {
		return M($this->sbact_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmoreSbact_tmp($where, $field='sb.*') {
		return M($this->sbact_tmp)->alias('sb')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = sb.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

}
