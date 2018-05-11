<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MDevice	设备基本信息
// +----------------------------------------------------------------------
class MDeviceModel extends Model{
	
	function __construct(){
		$this->device 	= "device";
		$this->model  	= "model";
		$this->factory  = "factory";
	}

	/*
	* 获取统计数量	新
	* @post:
	**/
	public function countNewsDevice($where) {
		return M($this->device)->where($where)->count();
	}
	
	/*
	* 获取列表		新
	* @post:
	**/
	public function getDeviceNewslist($where, $field='*', $limit, $order='DEVICE_SN desc') {
		return M($this->device)->where($where)->field($field)->limit($limit)->order($order)->select();
	}

	//--------------------------------------
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countDevice($where) {
		return M($this->device)->alias('d')
				->join(DB_PREFIX.$this->model.' m on m.MODEL_MAP_ID = d.MODEL_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getDevicelist($where, $field='*', $limit, $order='d.DEVICE_SN desc') {
		return M($this->device)->alias('d')
				->join(DB_PREFIX.$this->factory.' f on f.FACTORY_MAP_ID = d.FACTORY_MAP_ID')
				->join(DB_PREFIX.$this->model.' m on m.MODEL_MAP_ID = d.MODEL_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(d.INSTALL_DATE,"%Y-%m-%d") AS INSTALL_DATE, DATE_FORMAT(d.CREATE_TIME,"%Y-%m-%d") AS CREATE_TIME, DATE_FORMAT(d.UPDATE_TIME,"%Y-%m-%d") AS UPDATE_TIME')
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 获取列表
	* @post:
	**/
	public function countDevicelist($where, $field='d.*', $limit, $group='m.MODEL_MAP_ID', $order='d.DEVICE_SN desc') {
		$list = M($this->device)->alias('d')
				->join(DB_PREFIX.$this->factory.' f on f.FACTORY_MAP_ID = d.FACTORY_MAP_ID')
				->join(DB_PREFIX.$this->model.' m on m.MODEL_MAP_ID = d.MODEL_MAP_ID')
				->where($where)
				->field($field)
				->limit($limit)
				->group($group)
				->order($order)
				->select();
		//获取已经使用的设备数量
		foreach ($list as $key => $val) {
			$where1 = 'MODEL_MAP_ID = '.$val['MODEL_MAP_ID'].' and (POS_NO !="-" and  POS_NO !="")';
			$usednum = M($this->device)->where($where1)->count();
			$list[$key]['usednum'] = $usednum;
		}
		return $list;
	}

	/*
	* 获取型号统计列表
	* @post:
	**/
	public function countModellist($where, $field='m.*', $limit, $group='m.MODEL_MAP_ID') {
		return M($this->model)->alias('m')
				->join(DB_PREFIX.$this->factory.' f on f.FACTORY_MAP_ID = m.FACTORY_MAP_ID')
				->where($where)
				->field($field)
				->limit($limit)
				->group($group)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addDevice($data) {
		$result = M($this->device)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '设备添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateDevice($where, $data) {
		$result = M($this->device)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '设备修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delDevice($where) {
		$result = M($this->device)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '设备删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findDevice($where, $field='*') {
		return M($this->device)->alias('d')
				->join(DB_PREFIX.$this->factory.' f on f.FACTORY_MAP_ID = d.FACTORY_MAP_ID')
				->join(DB_PREFIX.$this->model.' m on m.MODEL_MAP_ID = d.MODEL_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(d.INSTALL_DATE,"%Y-%m-%d") AS INSTALL_DATE, DATE_FORMAT(d.CREATE_TIME,"%Y-%m-%d") AS CREATE_TIME, DATE_FORMAT(d.UPDATE_TIME,"%Y-%m-%d") AS UPDATE_TIME')
				->find();
	}

	/*
	* 获取设备分组
	* @post:
	**/
	public function groupDevice($where, $field='*',$group) {
		return M($this->device)->alias('d')
				->join(DB_PREFIX.$this->factory.' f on f.FACTORY_MAP_ID = d.FACTORY_MAP_ID')
				->join(DB_PREFIX.$this->model.' m on m.MODEL_MAP_ID = d.MODEL_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(d.INSTALL_DATE,"%Y-%m-%d") AS INSTALL_DATE, DATE_FORMAT(d.CREATE_TIME,"%Y-%m-%d") AS CREATE_TIME, DATE_FORMAT(d.UPDATE_TIME,"%Y-%m-%d") AS UPDATE_TIME')
				->group($group)
				->select();
	}
}
