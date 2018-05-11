<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MCardlevel	卡级别
// +----------------------------------------------------------------------
class MCardlevelModel extends Model{
	
	function __construct(){
		$this->cardlevel = "cardlevel";
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getCardlevellist($where, $field='*', $limit, $order='CLEVEL_ID asc') {
		return M($this->cardlevel)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
}
