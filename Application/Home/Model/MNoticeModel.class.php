<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	系统通知
// +----------------------------------------------------------------------
class MNoticeModel extends Model{
	
	function __construct(){
		$this->notice = "notice";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countNotice($where) {
		return M($this->notice)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getNoticelist($where, $field='*', $limit, $order='NOTICE_TIME desc') {
		return M($this->notice)->where($where)
				->field($field.',DATE_FORMAT(NOTICE_TIME,"%Y-%m-%d") AS NOTICE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addNotice($data) {
		$result = M($this->notice)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '系统通知添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateNotice($where, $data) {
		$result = M($this->notice)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '系统通知修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delNotice($where) {
		$result = M($this->notice)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '系统通知删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findNotice($where, $field='*') {
		return M($this->notice)->where($where)
				->field($field.',DATE_FORMAT(NOTICE_TIME,"%Y-%m-%d") AS NOTICE_TIME,DATE_FORMAT(NOTICE_EXP,"%Y-%m-%d") AS NOTICE_EXP')
				->find();
	}
}
