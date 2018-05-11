<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	日志信息
// +----------------------------------------------------------------------
class MLogModel extends Model{
	
	function __construct(){
		$this->log	= "log";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countLog($where) {
		return M($this->log)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getLoglist($where, $field='*', $limit, $order='LOG_ID desc') {
		return M($this->log)->where($where)
				->field($field.',DATE_FORMAT(CONCAT(LOG_DATE, LPAD(LOG_TIME,6,"0")),"%Y-%m-%d %H:%i:%s") AS CREATR_TIME')
				->limit($limit)->order($order)->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addLog($data) {
		$result = M($this->log)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateLog($where, $data) {
		$result = M($this->log)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delLog($where) {
		$result = M($this->log)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findLog($where, $field='*') {
		return M($this->log)->where($where)->field($field)->find();
	}
}
