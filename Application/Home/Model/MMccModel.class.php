<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MMcc	商户分类代码管理
// +----------------------------------------------------------------------
class MMccModel extends Model{
	
	function __construct(){
		$this->mcc   = "mcc";
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getMcclist($where, $field='*', $limit, $order='MCC_TYPE asc') {
		return M($this->mcc)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getMcc_codelist($mcctype) {
		$mccsel = M($this->mcc)->where('MCC_TYPE = '.$mcctype)->field('MCC_CODE,MCC_NAME')->limit($limit)->order($order)->select();
		$res[] = array('', '请选择');
		foreach($mccsel as $val){
			$res[] = array($val['MCC_CODE'], $val['MCC_NAME']);
		}
		return $res;
	}

	/*
	* 获取单条数据
	* @post:
	**/
	public function findMcc($where,$field="*") {
		return M($this->mcc)->where($where)->field($field)->find();
	}
}
