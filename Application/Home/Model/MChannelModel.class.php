<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	MChannel	渠道基本信息
// +----------------------------------------------------------------------
class MChannelModel extends Model{
	
	function __construct(){
		$this->channel	= "channel";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countChannel($where) {
		return M($this->channel)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getChannellist($where, $field='*', $limit, $order='CHANNEL_MAP_ID desc') {
		return M($this->channel)->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findChannel($where, $field='*') {
		return M($this->channel)->where($where)
				->field($field)
				->find();
	}

	/*
	* 添加
	* @post:
	**/
	public function addChannel($data) {
		$result = M($this->channel)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '渠道添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！",'CHANNEL_MAP_ID'=>$result);
	}

	/*
	* 修改
	* @post:
	**/
	public function updateChannel($where, $data) {
		$result = M($this->channel)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '渠道修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delChannel($where) {
		if (empty($where)) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		$m = M();
		$m->startTrans();	//启用事务
		//删除基本信息
		$res1 = M($this->channel)->where($where)->delete();
		if($res1 === false) {
			return array('state'=>1, 'msg'=>"删除基本信息失败！");
		}
		//删除结算方式
		$res2 = M('ccls')->where($where)->delete();
		if($res2 === false) {
			$m->rollback();//不成功，则回滚
			return array('state'=>1, 'msg'=>"删除结算方式失败！");
		}
		$m->commit();//成功则提交
		M('ccls_tmp')->where($where)->delete();
		//日志
		setLog(3, '渠道删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
}
