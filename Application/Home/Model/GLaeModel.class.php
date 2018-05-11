<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	GLae	会员
// +----------------------------------------------------------------------
class GLaeModel extends Model{
	
	function __construct(){		
		$this->lae 		= M('lae', DB_PREFIX_GLA, DB_DSN_GLA);
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findLae($where, $field='*') {
		return $this->lae->where($where)
				->field($field)
				->find();
	}
}
