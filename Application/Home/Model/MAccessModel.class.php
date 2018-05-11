<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	权限表
// +----------------------------------------------------------------------
class MAccessModel extends Model{
	
	function __construct(){
		$this->access	= "access";
	}
	
	/*
	* 获取列表
	**/
	public function getAccesslist($field='*') {
		 return M($this->access)->field($field)->select();
	}
	
	/**
	 * 检查指定菜单是否有权限
	 * @param array $menu 	menu表中某记录数组
	 * @param int $role_id 	需要检查的角色ID
	 * @param int $access 	access表的所有数组记录
	 */
	public function is_checked($menu, $role_id, $access) {
		$menutemp = array(
			'ROLE_ID' 		=>	$role_id,
			'MENU_ID' 		=>	$menu['MENU_ID'],
			'MENU_PID' 		=>	$menu['MENU_PID'],
			'MENU_LEVEL'	=>	$menu['MENU_LEVEL'],
		);
		$info = in_array($menutemp, $access);
		if($info){
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 获取菜单深度
	 * @param $menu_id
	 * @param $array
	 * @param $i
	 */
	public function get_level($menu_id, $array=array(), $i=0) {
		foreach($array as $n=>$val){
			if($val['MENU_ID'] == $menu_id)
			{
				if($val['MENU_PID']== '0') return $i;
				$i++;
				return $this->get_level($val['MENU_PID'], $array, $i);
			}
		}
	}
	
	/*
	* 删除
	**/
	public function delAccess($where) {
		$result = M($this->access)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '权限删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	
	/**
	 * 获取菜单节点表信息
	 * @param int $menu_id 菜单节点ID
	 * @param int $menu 菜单节点数据
	 */
	public function get_menuinfo($menu_id, $menu) {
		$info = array(
			'MENU_ID' 		=> $menu[$menu_id]['MENU_ID'],
			'MENU_PID'		=> $menu[$menu_id]['MENU_PID'],
			'MENU_LEVEL'	=> $menu[$menu_id]['MENU_LEVEL']
		);
		return $info;
	}
	
	/*
	* 添加 批量
	**/
	public function addAccessAll($data) {
		$result = M($this->access)->addAll($data,array(),true);
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(2, '权限添加成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
}
