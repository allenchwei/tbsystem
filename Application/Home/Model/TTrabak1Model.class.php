<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @gzy	TTrace	交易流水
// +----------------------------------------------------------------------
class TTrabak1Model extends Model{
	
	function __construct(){		
		$this->trace 	= M('trabak1', DB_PREFIX_TRA, DB_DSN_TRA);
		$this->jfbls 	= "jfbls";
		$this->kfls 	= "kfls";
	}
	
	
	
	/*
	* 批量修改
	* @post:
	**/
	public function addAllTrace($data) {
		$result = $this->trace->addAll($data);
		//$result = $this->trace->add($data[0]);
		//echo $this->trace->getLastSql();
		//var_dump($result);
		if($result === false) {
			return array('state'=>1, 'msg'=>"批量插入失败！");
		}
		//日志
		setLog(3, '交易流水修批量插入成功！');
		return array('state'=>0, 'msg'=>"批量插入成功！");
	}
	
	
}
