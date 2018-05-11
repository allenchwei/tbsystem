<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	登记表 	MReg
// +----------------------------------------------------------------------
class MRegModel extends Model{
	
	function __construct(){
		$this->reg	= "reg";
	}
	
	/*
	* 添加
	* @post:
	**/
	public function addReg($data) {
		$result = M($this->reg)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '登记表添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
}
