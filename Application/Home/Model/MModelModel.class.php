<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MModel	设备型号管理
// +----------------------------------------------------------------------
class MModelModel extends Model{
	
	function __construct(){
		$this->model   	= "model";
		$this->factory 	= "factory";
		$this->MFactory = "MFactory";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countModel($where) {
		return M($this->model)->alias('m')
				->join(DB_PREFIX.$this->factory.' f on f.FACTORY_MAP_ID = m.FACTORY_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getModellist($where, $field='m.*', $limit, $order='m.MODEL_MAP_ID desc') {
		return M($this->model)->alias('m')
				->join(DB_PREFIX.$this->factory.' f on f.FACTORY_MAP_ID = m.FACTORY_MAP_ID')
				->where($where)
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addModel($data) {
		$result = M($this->model)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '设备型号添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updateModel($where, $data) {
		$result = M($this->model)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '设备型号添修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}

	/*
	* 删除
	* @post:
	**/
	public function delModel($where) {
		$result = M($this->model)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '设备型号删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findModel($where, $field='m.*') {
		return  M($this->model)->alias('m')
				->join(DB_PREFIX.$this->factory.' f on f.FACTORY_MAP_ID = m.FACTORY_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

	/*
	* 获取厂商设备下拉联动
	* @post:
	**/
	public function getModelsel($code='',$model_id='MODEL_MAP_ID', $factory_id='FACTORY_MAP_ID') {
		$msel = '';$fsel = '';$time = getmicrotime();
		//获取已选中的设备型号, 下拉列表
		if ($code !='') {
			$result = M($this->model)->where('MODEL_MAP_ID = '.$code)->field('FACTORY_MAP_ID')->find();
			$mlist = M($this->model)->where('FACTORY_MAP_ID = '.$code)->field('MODEL_MAP_ID,MODEL_NAME')->select();
			foreach($mlist as $val){
				//设置设备型号选中项
				if ($code == $val['MODEL_MAP_ID']) {
					$msel .= '<option value="'.$val['MODEL_MAP_ID'].'" selected >'.$val['MODEL_NAME'].'</option>';
				}else{
					$msel .= '<option value="'.$val['MODEL_MAP_ID'].'">'.$val['MODEL_NAME'].'</option>';
				}
			}
		}
		//获取已选中的厂商, 下拉列表
		$flist = D($this->MFactory)->getFactorylist('','FACTORY_MAP_ID,FACTORY_NAME');
		foreach($flist as $val){
			//设置厂商选中项
			if ($result['FACTORY_MAP_ID'] == $val['FACTORY_MAP_ID']) {
				$fsel .= '<option value="'.$val['FACTORY_MAP_ID'].'" selected >'.$val['FACTORY_NAME'].'</option>';
			}else{
				$fsel .= '<option value="'.$val['FACTORY_MAP_ID'].'">'.$val['FACTORY_NAME'].'</option>';
			}
		}
		$res = '<select class="combox" name="'.$factory_id.'" ref="combox_model_'.$time.'" refUrl="'.__MODULE__.'/Public/ajaxgetmodel/f_id/{value}">
					  <option value="">请选择</option>'.$fsel.'
				</select>
				<select class="combox" name="'.$model_id.'" id="combox_model_'.$time.'">
					  <option value="">请选择</option>'.$msel.'
				</select>';		
		return $res;
	}
}
