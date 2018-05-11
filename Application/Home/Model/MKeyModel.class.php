<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	POS应用密钥 	MKey
// +----------------------------------------------------------------------
class MKeyModel extends Model{
	
	function __construct(){
		$this->key	= "key";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countKey($where) {
		return M($this->key)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getKeylist($where, $field='*', $limit, $order='HOST_MAP_ID desc') {
		$list = M($this->key)->where($where)->field($field)->limit($limit)->order($order)->select();
		if (!$list) {
			$list = array();
		}
		return $list;
	}


	/*
	* 添加
	* @post:
	**/
	public function addkey($data) {
		$result = M($this->key)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, 'POS应用密钥添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateKey($where, $data) {
		$result = M($this->key)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, 'POS应用密钥修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delKey($where, $data) {
		$result = M($this->key)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, 'POS应用密钥删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findKey($where, $field='*') {
		return M($this->key)->where($where)->field($field)->find();
	}
}
