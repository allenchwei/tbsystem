<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MCardtype	卡种类
// +----------------------------------------------------------------------
class MCardtypeModel extends Model{
	
	function __construct(){
		$this->cardtype = "cardtype";
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getCardtypelist($where, $field='*', $limit, $order='CTYPE_ID asc') {
		return M($this->cardtype)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
}
