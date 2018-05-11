<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MBank	银行
// +----------------------------------------------------------------------
class TTransferlsModel extends Model{
	
	function __construct(){
		$this->transferls   =   M('transferls', DB_PREFIX_TRA, DB_DSN_TRA);
	}
	/*
	* 获取列表
	* @post:	asc 周定
	**/
	public function getNewsTransferlslist($where, $field='*', $limit, $order='TRANSFER_ID desc') {
		return $this->transferls->where($where)->field($field)->limit($limit)->order($order)->select();
	}

	public function selectNewsTrace($where) {
		return $this->transferls->where($where)->field('*')->select();
	}

	/*
	* 获取统计数量	新
	* @post:
	**/
	public function countNewstransferls($where) {
		return $this->transferls->where($where)->count('TRANSFER_ID');
	}
}
?>