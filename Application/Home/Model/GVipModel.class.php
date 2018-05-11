<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	GVip	会员
// +----------------------------------------------------------------------
class GVipModel extends Model{
	
	function __construct(){		
		$this->vip 	=	M('vip', DB_PREFIX_GLA, DB_DSN_GLA);
		$this->lap	=	'lap';
	}
	
	/*
	* 获取统计数量	新
	* @post:
	**/
	public function countNewsVip($where) {
		return $this->vip->where($where)->count('VIP_ID');
	}
	
	/*
	* 获取列表		新
	* @post:
	**/
	public function getNewsViplist($where, $field='*', $limit, $order='VIP_ID desc') {
		return $this->vip->where($where)->field($field)->limit($limit)->order($order)->select();	
	}
	
	//----------------结束----------------
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countVip($where) {
		return $this->vip->alias('v')
				->join(DB_PREFIX_GLA.$this->lap.' l on v.VIP_ID = LPAD(l.ACCT_NO,9,"0")', 'LEFT')
				->field('VIP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getViplist($where, $field='v.*', $limit, $order='VIP_ID desc') {
		return $this->vip->alias('v')
				->join(DB_PREFIX_GLA.$this->lap.' l on v.VIP_ID = LPAD(l.ACCT_NO,9,"0")', 'LEFT')
				->where($where)
				->field($field.',DATE_FORMAT(v.VIP_BIRTHDAY,"%Y-%m-%d") as VIP_BIRTHDAY,DATE_FORMAT(v.CREATE_TIME,"%Y-%m-%d %H:%i:%s") as CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 添加
	* @post:
	**/
	public function addVip($data) {
		$result = $this->vip->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '会员添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！", 'VIP_ID'=>$result);
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateVip($where, $data) {
		$result = $this->vip->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '会员修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delVip($where) {
		$result = $this->vip->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '会员删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息	新
	* @post:
	**/
	public function findNewsVip($where, $field='*') {
		return $this->vip->where($where)->field($field)->find();
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findVip($where, $field='*') {
		return $this->vip->where($where)
				->field($field.',DATE_FORMAT(VIP_BIRTHDAY,"%Y-%m-%d") as VIP_BIRTHDAY')
				->find();
	}
	
	/*
	* 获取单条信息
	* @post:	more
	**/
	public function findmoreVip($where, $field='v.*') {
		return $this->vip->alias('v')
				->join(DB_PREFIX_GLA.$this->lap.' l on v.VIP_ID = LPAD(l.ACCT_NO,9,"0")', 'LEFT')
				->where($where)
				->field($field.',DATE_FORMAT(v.VIP_BIRTHDAY,"%Y-%m-%d") as VIP_BIRTHDAY,DATE_FORMAT(v.CREATE_TIME,"%Y-%m-%d %H:%i:%s") as CREATE_TIME')
				->find();
	}
}
