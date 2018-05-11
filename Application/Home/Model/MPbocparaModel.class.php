<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	IC卡参数
// +----------------------------------------------------------------------
class MPbocparaModel extends Model{
	
	function __construct(){
		$this->pbocpara	= "pbocpara";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countPbocpara($where) {
		return M($this->pbocpara)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPbocparalist($where, $field='*', $limit, $order='PBOCPARA_ID desc') {
		return M($this->pbocpara)->where($where)->field($field)->limit($limit)->order($order)->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addPbocpara($data) {
		$result = M($this->pbocpara)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, 'IC卡参数添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updatePbocpara($where, $data) {
		$result = M($this->pbocpara)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, 'IC卡参数修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delPbocpara($where, $data) {
		$result = M($this->pbocpara)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, 'IC卡参数删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findPbocpara($where, $field='*') {
		return M($this->pbocpara)->where($where)->field($field)->find();
	}
}
