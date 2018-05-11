<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	结算节假日
// +----------------------------------------------------------------------
class MWdateModel extends Model{
	
	function __construct(){
		$this->wdate = "wdate";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countWdate($where) {
		return M($this->wdate)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getWdatelist($where, $field='*', $limit, $order='WDATE_ID desc') {
		return M($this->wdate)->where($where)
				->field($field.',DATE_FORMAT(NOWORK_DATE,"%Y-%m-%d") AS NOWORK_DATE, DATE_FORMAT(WORK_DATE,"%Y-%m-%d") AS WORK_DATE')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 添加	all
	* @post:
	**/
	public function addAllWdate($data) {
		$result = M($this->wdate)->addAll($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '节假日添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}	

	/*
	* 添加
	* @post:
	**/
	public function addWdate($data) {
		$result = M($this->wdate)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '节假日添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateWdate($where, $data) {
		$result = M($this->wdate)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '节假日修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delWdate($where) {
		$result = M($this->wdate)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '节假日删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findWdate($where, $field='*') {
		return M($this->wdate)->where($where)
				->field($field.',DATE_FORMAT(NOWORK_DATE,"%Y-%m-%d") AS NOWORK_DATE, DATE_FORMAT(WORK_DATE,"%Y-%m-%d") AS WORK_DATE')
				->find();
	}
}
