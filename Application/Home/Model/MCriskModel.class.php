<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MCrisk	黑名单卡管理
// +----------------------------------------------------------------------
class MCriskModel extends Model{
	
	function __construct(){
		$this->crisk = "crisk";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countCrisk($where) {
		return M($this->crisk)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getCrisklist($where, $field='*', $limit, $order='CREATE_DATE desc') {
		return M($this->crisk)->where($where)				
				->field($field.',DATE_FORMAT(CREATE_DATE,"%Y-%m-%d") AS CREATE_DATE')
				->limit($limit)
				->order($order)
				->select();
	}
		
	/*
	* 添加
	* @post:
	**/
	public function addCrisk($data) {
		$result = M($this->crisk)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '黑名单卡添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateCrisk($where, $data) {
		$result = M($this->crisk)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '黑名单卡修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delCrisk($where) {
		$result = M($this->crisk)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '黑名单卡删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findCrisk($where, $field='*') {
		return M($this->crisk)->where($where)				
				->field($field.',DATE_FORMAT(CREATE_DATE,"%Y-%m-%d") AS CREATE_DATE')
				->find();
	}
}
