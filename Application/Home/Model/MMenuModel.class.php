<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	菜单节点
// +----------------------------------------------------------------------
class MMenuModel extends Model{
	
	function __construct(){
		$this->menu	= "menu";
		$this->access	= "access";
	}
	
	/*
	* 获取列表
	**/
	public function getMenulist($where, $field='*', $limit, $order='MENU_SORT desc') {
		return M($this->menu)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
	
	/*
	* 获取列表	关联
	**/
	public function getMenulinelist($where, $field='*', $limit, $order='m.MENU_SORT desc') {
		return M($this->menu)->alias('m')
				->join(DB_PREFIX.$this->access.' a on m.MENU_ID = a.MENU_ID')
				->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 添加
	**/
	public function addMenu($data) {
		$result = M($this->menu)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '菜单添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	**/
	public function updateMenu($where, $data) {
		$result = M($this->menu)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '菜单修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	**/
	public function delMenu($where) {
		$result = M($this->menu)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '菜单删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条
	**/
	public function findMenu($where, $field='*') {
		return M($this->menu)->where($where)->field($field)->find();
	}
}
