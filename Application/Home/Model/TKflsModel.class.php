<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	TKfls	积分宝运营流水表
// +----------------------------------------------------------------------
class TKflsModel extends Model{
	
	function __construct(){		
		$this->kfls = M('kfls', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findKfls($where, $field='*') {
		return $this->kfls->where($where)
				->field($field.',DATE_FORMAT(JFB_CLEAR_DATE,"%Y-%m-%d") AS JFB_CLEAR_DATE')
				->find();
	}
}
