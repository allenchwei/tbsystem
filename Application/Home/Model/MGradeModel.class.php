<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MBank	银行
// +----------------------------------------------------------------------
class MGradeModel extends Model{
	
	function __construct(){
		$this->grade_rate	= "grade_rate";
		$this->host_online	= "host_online";
		$this->host			= "host";
	}
	/*
	* 获取列表
	* @post:	asc 周定
	**/
	public function getBanklist($where, $field='*', $limit, $order='ID desc') {
		return M($this->grade_rate)
				->join('LEFT JOIN '.DB_PREFIX.$this->host.' on a_host.HOST_MAP_ID=a_grade_rate.HOST_MAP_ID')
				->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 获取多条信息
	* @post:
	**/
	public function findHost_Bank($where, $field='*') {
		return M($this->grade_rate)
				->join('LEFT JOIN '.DB_PREFIX.$this->host.' on a_host.HOST_MAP_ID=a_grade_rate.HOST_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countBank($where) {
		return M($this->grade_rate)->where($where)->count();
	}

	/*
	* 获取单条信息
	* @post:
	**/
	public function findhost($where, $field='HOST_NAMEAB,HOST_MAP_ID') {
		return M($this->host)->where($where)->field($field)->select();
	}

	/*
	* 修改
	* @post:
	**/
	public function updateGrade($where, $data) {
		$result = M($this->grade_rate)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户等级扣率修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
}
?>