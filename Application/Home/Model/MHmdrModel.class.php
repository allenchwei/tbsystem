<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MHmdr	通道成本扣率
// +----------------------------------------------------------------------
class MHmdrModel extends Model{
	
	function __construct(){
		$this->hmdr 	= "hmdr";
		$this->host 	= "host";
		$this->hmdr_tmp	= "hmdr_tmp";
	}
	
	/*
	* 获取统计数量	group
	* @post:
	**/
	public function countgroupHmdr($where, $field='COUNT(DISTINCT hm.HOST_MAP_ID) as total') {
		return M($this->hmdr)->alias('hm')
				->join(DB_PREFIX.$this->host.' ho on hm.HOST_MAP_ID = ho.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hmdr_tmp.' hmp on hm.HMDR_ID = hmp.HMDR_ID', 'LEFT')
				->where($where)
				->field($field)
				->find();
	}
	
	/*
	* 获取列表	group
	* @post:
	**/
	public function getHmdrgrouplist($where, $field='hm.*,ho.*', $limit, $order='hm.HMDR_ID desc') {
		return M($this->hmdr)->alias('hm')
				->join(DB_PREFIX.$this->host.' ho on hm.HOST_MAP_ID = ho.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hmdr_tmp.' hmp on hm.HMDR_ID = hmp.HMDR_ID', 'LEFT')
				->where($where)
				->field($field)
				->limit($limit)
				->group('hm.HOST_MAP_ID')
				->order($order)
				->select();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getHmdrlist($where, $field='hm.*,ho.*', $order='hm.HMDR_ID desc') {
		return M($this->hmdr)->alias('hm')
				->join(DB_PREFIX.$this->host.' ho on hm.HOST_MAP_ID = ho.HOST_MAP_ID')
				->join(DB_PREFIX.$this->hmdr_tmp.' hmp on hm.HMDR_ID = hmp.HMDR_ID', 'LEFT')
				->where($where)
				->field($field)
				->order($order)
				->select();
	}	
	
	/*
	* 添加	all
	* @post:
	**/
	public function addAllHmdr($data) {
		$result = M($this->hmdr)->addAll($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '通道成本扣率添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}	
	
	/*
	* 添加
	* @post:
	**/
	public function addHmdr($data) {
		$result = M($this->hmdr)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '通道成本扣率添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateHmdr($where, $data) {
		$result = M($this->hmdr)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '通道成本扣率修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 删除
	* @post:
	**/
	public function delHmdr($where) {
		$result = M($this->hmdr)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '通道成本扣率删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findHmdr($where, $field='*') {
		return M($this->hmdr)->where($where)->field($field)->find();
	}
	
	
	
	/*----------------------------- tmp ----------------------------------*/
	

	/*
	* 添加	all
	* @post:
	**/
	public function addAllHmdr_tmp($data) {
		$result = M($this->hmdr_tmp)->addAll($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '通道成本扣率添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}	
	
	/*
	* 添加
	* @post:
	**/
	public function addHmdr_tmp($data) {
		$result = M($this->hmdr_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '通道成本扣率 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}
	
	/*
	* 修改
	* @post:
	**/
	public function updateHmdr_tmp($where, $data) {
		$result = M($this->hmdr_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '通道成本扣率 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 删除
	* @post:
	**/
	public function delHmdr_tmp($where) {
		$result = M($this->hmdr_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '通道成本扣率 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findHmdr_tmp($where, $field='*') {
		return M($this->hmdr_tmp)->where($where)->field($field)->find();
	}
}
