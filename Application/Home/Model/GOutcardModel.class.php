<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	GOutcard	实体卡流转记录
// +----------------------------------------------------------------------
class GOutcardModel extends Model{
	
	function __construct(){		
		$this->outcard = M('outcard', DB_PREFIX_GLA, DB_DSN_GLA);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countOutcard($where) {
		return $this->outcard->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getOutcardlist($where, $field='*', $limit, $order='CARDOUT_ID desc') {
		return $this->outcard->where($where)
				->field($field.',DATE_FORMAT(OUT_DATE,"%Y-%m-%d") AS OUT_DATE')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 添加
	* @post:
	**/
	public function addOutcard($data) {
		$result = $this->outcard->data($data)->add();
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
	public function updateOutcard($where, $data) {
		$result = $this->outcard->where($where)->save($data);
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
	public function delOutcard($where) {
		$result = $this->outcard->where($where)->delete();
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
	public function findOutcard($where, $field='*') {
		return $this->outcard->where($where)
				->field($field.',DATE_FORMAT(OUT_DATE,"%Y-%m-%d") AS OUT_DATE')
				->find();
	}

	/*
	* 获取最新分配批次
	* @post:
	**/
	public function findBatchmax($where) {
		return $this->outcard->where($where)->max('CARDOUT_BATCH_ID');
	}
}
