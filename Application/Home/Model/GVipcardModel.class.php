<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	GVipcard	实体卡号信息
// +----------------------------------------------------------------------
class GVipcardModel extends Model{
	
	function __construct(){		
		$this->vipcard 		= M('vipcard', DB_PREFIX_GLA, DB_DSN_GLA);
		$this->vip     		= 'vip';
		$this->vipcard_his 	= M('vipcard_his', DB_PREFIX_GLA, DB_DSN_GLA);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countVipcard($where) {
		return $this->vipcard->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getVipcardlist($where, $field='*', $limit, $order='VIP_ID desc') {
		return $this->vipcard->where($where)->field($field)->limit($limit)->order($order)->select();
	}

	/*
	* 获取统计(关联会员信息)
	* @post:
	**/
	public function countVipcardmore($where, $field='*', $limit, $order='vc.CARD_NO desc') {
		return $this->vipcard->alias('vc')
				->join(DB_PREFIX_GLA.$this->vip.' vp on vp.CARD_NO = vc.CARD_NO','LEFT')
				->where($where)
				->count();
	}

	/*
	* 获取列表(关联会员信息)
	* @post:
	**/
	public function getVipcardmore($where, $field='*', $limit, $order='vp.VIP_ID desc, vc.CARD_NO desc,vc.UPDATE_TIME desc') {
		return $this->vipcard->alias('vc')
				->join(DB_PREFIX_GLA.$this->vip.' vp on vp.CARD_NO = vc.CARD_NO','LEFT')
				->where($where)
				->field($field.',DATE_FORMAT(vp.VIP_BIRTHDAY,"%Y-%m-%d") as VIP_BIRTHDAY,DATE_FORMAT(vp.CREATE_TIME,"%Y-%m-%d %H:%i:%s") as CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}


	/*
	* 获取列表(关联会员信息) 带group  适用apiserver 45接口
	* @post:
	**/
	public function getVipcardmoregroup($where, $field='vc.*', $limit, $group, $order='vc.VIP_ID desc') {
		return $this->vipcard->alias('vc')
				->join(DB_PREFIX_GLA.$this->vip.' vp on vp.VIP_ID = vc.VIP_ID','LEFT')
				->where($where)
				->field($field)
				->limit($limit)
				->group($group)
				->order($order)
				->select();
	}

	/*
	* 获取每个分支机构分组统计列表
	* @post:
	**/
	public function groupVipcardlist($where, $field='*', $limit, $group, $order) {
		return $this->vipcard->where($where)
				->field($field)
				->limit($limit)
				->group($group)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addVipcard($data) {
		$result = $this->vipcard->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '实体卡添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 添加
	* @post:
	**/
	public function addAllvipcard($data) {
		$result = $this->vipcard->addAll($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '实体卡添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateVipcard($where, $data) {
		$result = $this->vipcard->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '实体卡修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delVipcard($where) {
		$result = $this->vipcard->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '实体卡删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findVipcard($where, $field='*') {
		return $this->vipcard->where($where)
				->field($field)
				->find();
	}
	/*
	* 获取单条关联信息
	* @post:
	**/
	public function findVipcardmore($where, $field='*') {
		return $this->vipcard->alias('vc')
				->join(DB_PREFIX_GLA.$this->vip.' vp on vp.CARD_NO = vc.CARD_NO','LEFT')
				->where($where)
				->field($field.',DATE_FORMAT(VIP_BIRTHDAY,"%Y-%m-%d") AS VIP_BIRTHDAY')
				->find();
	}
		
	//------------------------------------- tmp -------------------------------------------
	
	/*
	* 添加
	* @post:
	**/
	public function addVipcard_tmp($data) {
		$result = $this->vipcard_his->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '实体卡 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 换卡查询
	* @post:
	**/
	public function findVipcard_tmp($where,$field = '*') {
		return $this->vipcard_his->where($where)->field($field)->find();
	}
	/*
	* 获取单条关联信息
	* @post:
	**/
	public function findVipcardmore_tmp($where, $field='*') {
		return $this->vipcard_his->alias('vc')
				->join(DB_PREFIX_GLA.$this->vip.' vp on vp.VIP_ID = vc.VIP_ID','LEFT')
				->where($where)
				->field($field.',DATE_FORMAT(VIP_BIRTHDAY,"%Y-%m-%d") AS VIP_BIRTHDAY')
				->find();
	}
	/*
	* 获取统计(关联会员信息)
	* @post:
	**/
	public function countVipcardmore_tmp($where, $field='*', $limit, $order='vc.CARD_NO desc') {
		return $this->vipcard_his->alias('vc')
				->join(DB_PREFIX_GLA.$this->vip.' vp on vp.VIP_ID = vc.VIP_ID','LEFT')
				->where($where)
				->count();
	}

	/*
	* 获取列表(关联会员信息)
	* @post:
	**/
	public function getVipcardmore_tmp($where, $field='*', $limit, $order='vp.VIP_ID desc, vc.CARD_NO desc,UPDATE_TIME desc') {
		return $this->vipcard_his->alias('vc')
				->join(DB_PREFIX_GLA.$this->vip.' vp on vp.VIP_ID = vc.VIP_ID','LEFT')
				->where($where)
				->field($field.',DATE_FORMAT(vp.VIP_BIRTHDAY,"%Y-%m-%d") as VIP_BIRTHDAY,DATE_FORMAT(vp.CREATE_TIME,"%Y-%m-%d %H:%i:%s") as CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
}
