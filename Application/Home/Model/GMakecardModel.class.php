<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	GMakecard	实体卡制卡记录
// +----------------------------------------------------------------------
class GMakecardModel extends Model{
	
	function __construct(){		
		$this->makecard = M('makecard', DB_PREFIX_GLA, DB_DSN_GLA);
	}
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countMakecard($where) {
		return $this->makecard->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getMakecardlist($where, $field='*', $limit, $order='CARD_BATCH desc') {
		return $this->makecard->where($where)
				->field($field.',DATE_FORMAT(OUT_DATE,"%Y-%m-%d") AS OUT_DATE')
				->limit($limit)
				->order($order)
				->select();
	}
	
	/*
	* 添加
	* @post:
	**/
	public function addMakecard($data) {
		$result = $this->makecard->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '实体卡添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！", 'CARD_BATCH'=>$result);
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateMakecard($where, $data) {
		$result = $this->makecard->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '实体卡修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delMakecard($where) {
		$result = $this->makecard->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '实体卡删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findMakecard($where, $field='*',$order="CARD_BATCH DESC") {
		return $this->makecard->where($where)
				->field($field.',DATE_FORMAT(OUT_DATE,"%Y-%m-%d") AS OUT_DATE')
				->order($order)
				->find();
	}
}
