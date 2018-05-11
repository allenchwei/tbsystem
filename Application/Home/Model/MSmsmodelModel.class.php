<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	短信模板
// +----------------------------------------------------------------------
class MSmsmodelModel extends Model{
	
	function __construct(){
		$this->smsmodel = "smsmodel";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countSmsmodel($where) {
		return M($this->smsmodel)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getSmsmodellist($where, $field='*', $limit, $order='SMS_MODLE_ID desc') {
		return M($this->smsmodel)->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addSmsmodel($data) {
		$result = M($this->smsmodel)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '短信模板添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateSmsmodel($where, $data) {
		$result = M($this->smsmodel)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '短信模板修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delSmsmodel($where) {
		$result = M($this->smsmodel)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '短信模板删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findSmsmodel($where, $field='*') {
		return M($this->smsmodel)->where($where)
				->field($field)
				->find();
	}
}
