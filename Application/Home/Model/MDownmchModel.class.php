<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	MChannel	渠道基本信息
// +----------------------------------------------------------------------
class MDownmchModel extends Model{
	
	function __construct(){
		$this->downmch	= "downmch";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countDownmch($where) {
		return M($this->downmch)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getDownmchlist($where, $field='*', $limit, $order='ID desc') {
		return M($this->downmch)->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addDownmch($data) {
		$result = M($this->downmch)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '渠道添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	/*
	* 修改
	* @post:
	**/
	public function saveDownmch($where,$data) {
		$result = M($this->downmch)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '渠道添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
}
?>