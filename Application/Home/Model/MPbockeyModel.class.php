<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	IC卡公钥维护
// +----------------------------------------------------------------------
class MPbockeyModel extends Model{
	
	function __construct(){
		$this->pbockey	= "pbockey";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countPbockey($where) {
		return M($this->pbockey)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPbockeylist($where, $field='*', $limit, $order='PBOCKEY_ID desc') {
		return M($this->pbockey)->where($where)
				->field($field.',DATE_FORMAT(IC_PUBKEY_EXP,"%Y-%m-%d") AS IC_PUBKEY_EXP')
				->limit($limit)->order($order)->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addPbockey($data) {
		$result = M($this->pbockey)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, 'IC卡公钥添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updatePbockey($where, $data) {
		$result = M($this->pbockey)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, 'IC卡公钥修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delPbockey($where, $data) {
		$result = M($this->pbockey)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, 'IC卡公钥删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findPbockey($where, $field='*') {
		return M($this->pbockey)->where($where)
				->field($field)
				->find();
	}
}
