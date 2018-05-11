<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	TSmsls	短信流水
// +----------------------------------------------------------------------
class TSmslsModel extends Model{
	
	function __construct(){		
		$this->smsls = M('smsls', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countSmsls($where) {
		return $this->smsls->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getSmslslist($where, $field='*', $limit, $order='SMS_ID desc') {
		return $this->smsls->where($where)
				->field($field.',DATE_FORMAT(SMS_DATE,"%Y-%m-%d") AS SMS_DATE, DATE_FORMAT(CONCAT(SMS_DATE, LPAD(SMS_TIME,6,"0")),"%H:%i:%s") AS SMS_TIME')
				->limit($limit)
				->order($order)
				->select();
	}
		
	/*
	* 添加	all
	* @post:
	**/
	public function addAllSmsls($data) {
		$result = $this->smsls->addAll($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '短信流水添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 添加
	* @post:
	**/
	public function addSmsls($data) {
		$result = $this->smsls->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '短信流水添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！", 'result'=>$result);
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateSmsls($where, $data) {
		$result = $this->smsls->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '短信流水修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delSmsls($where) {
		$result = $this->smsls->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '短信流水删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findSmsls($where, $field='*', $order='SMS_ID desc') {
		return $this->smsls->where($where)
				->field($field.',DATE_FORMAT(SMS_DATE,"%Y-%m-%d") AS SMS_DATE, DATE_FORMAT(CONCAT(SMS_DATE, LPAD(SMS_TIME,6,"0")),"%H:%i:%s") AS SMS_TIME')
				->order($order)
				->find();
	}
}
