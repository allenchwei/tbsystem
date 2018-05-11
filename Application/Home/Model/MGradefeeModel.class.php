<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MBank	银行
// +----------------------------------------------------------------------
class MGradefeeModel extends Model{
	
	function __construct(){
		$this->grade_fee	= "grade_fee";
	}
	/*
	* 获取列表
	* @post:	asc 周定
	**/
	public function getMGradefeelist($where, $field='*', $limit, $order='SHOP_MAP_ID desc') {
		return M($this->grade_fee)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findBank($where, $field='*') {
		return M($this->grade_fee)->where($where)->field($field)->find();
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countMGradefee($where) {
		return M($this->grade_fee)->where($where)->count();
	}

	/*
	* 修改
	* @post:
	**/
	public function updateGrade($where, $data) {
		$result = M($this->grade_fee)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户等级扣率修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
}
?>