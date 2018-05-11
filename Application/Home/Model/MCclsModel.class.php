<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	MCcls	渠道结算信息
// +----------------------------------------------------------------------
class MCclsModel extends Model{
	
	function __construct(){
		$this->ccls	= "ccls";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countCcls($where) {
		return M($this->ccls)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getCclslist($where, $field='*', $limit, $order='CHANNEL_MAP_ID desc') {
		return M($this->ccls)->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findCcls($where, $field='*') {
		return M($this->ccls)->where($where)
				->field($field)
				->find();
	}

	/*
	* 添加
	* @post:
	**/
	public function addCcls($data) {
		$result = M($this->ccls)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '渠道结算添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！",'CHANNEL_MAP_ID'=>$result);
	}

	/*
	* 修改
	* @post:
	**/
	public function updateCcls($where, $data) {
		$result = M($this->ccls)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '渠道结算修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除
	* @post:
	**/
	public function delCcls($where) {
		$result = M($this->ccls)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(3, '渠道结算删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
}
