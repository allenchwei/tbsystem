<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MTrans	交易参数
// +----------------------------------------------------------------------
class MTransModel extends Model{
	
	function __construct(){
		$this->trans = "trans";
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countTrans($where) {
		return M($this->trans)->where($where)->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getTranslist($where, $field='*', $limit, $order='TRANS_MAP_ID asc') {
		return M($this->trans)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
		
	/*
	* 添加
	* @post:
	**/
	public function addTrans($data) {
		$result = M($this->trans)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '交易添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateTrans($where, $data) {
		$result = M($this->trans)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '交易修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delTrans($where) {
		$result = M($this->trans)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '交易删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findTrans($where, $field='*') {
		return M($this->trans)->where($where)->field($field)->find();
	}
}
