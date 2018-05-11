<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	商户申请
// +----------------------------------------------------------------------
class MShopapplyModel extends Model{
	
	function __construct(){
		$this->shopapply	= "shopapply";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countShopapply($where) {
		return M($this->shopapply)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getShopapplylist($where, $field='*', $limit, $order='APPLY_MAP_ID desc') {
		return M($this->shopapply)->where($where)
				->field($field.',DATE_FORMAT(CREATR_TIME,"%Y-%m-%d %H:%i:%s") AS CREATR_TIME')
				->limit($limit)->order($order)->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addShopapply($data) {
		$result = M($this->shopapply)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户申请添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateShopapply($where, $data) {
		$result = M($this->shopapply)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户申请修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delShopapply($where) {
		$result = M($this->shopapply)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '商户申请删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findShopapply($where, $field='*') {
		return M($this->shopapply)->where($where)->field($field)->find();
	}
}
