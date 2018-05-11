<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MPcert	合作伙伴证件管理
// +----------------------------------------------------------------------
class MPcertModel extends Model{
	
	function __construct(){
		$this->pcert = "pcert";
		$this->partner = "partner";
		$this->pcert_tmp = "pcert_tmp";
	}

	/*
	* 获取统计数量
	* @post:
	**/
	public function countPcert($where) {
		return M($this->pcert)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->pcert_tmp.' tmp on tmp.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->where($where.' and ag.PARTNER_STATUS = 0')
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPcertlist($where, $field='ac.*', $limit, $order='tmp.PARTNER_MAP_ID desc') {
		return M($this->pcert)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->pcert_tmp.' tmp on tmp.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->where($where.' and ag.PARTNER_STATUS = 0')
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}

	/*
	* 添加
	* @post:
	**/
	public function addPcert($data) {
		$result = M($this->pcert)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '合作伙伴证件添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！");
	}

	/*
	* 修改
	* @post:
	**/
	public function updatePcert($where, $data) {
		$result = M($this->pcert)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '合作伙伴证件修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findPcert($where, $field='*') {
		return M($this->pcert)->where($where)->field($field)->find();
	}

	/*
	* 获取关联表单条信息
	* @post:
	**/
	public function findmorePcert($where, $field='ac.*') {
		return M($this->pcert)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

	//以下为tmp表MODEL用于数据变更====================================================================================
	/*
	* 添加tmp数据
	* @post:
	**/
	public function addPcert_tmp($data) {
		$result = M($this->pcert_tmp)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"操作失败！");
		}
		//日志
		setLog(2, '合作伙伴证件 tmp 添加成功！');
		return array('state'=>0, 'msg'=>"操作成功！");
	}
	/*
	* 更新tmp数据
	* @post:
	**/
	public function updatePcert_tmp($where,$data) {
		$result = M($this->pcert_tmp)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '合作伙伴证件 tmp 修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	/*
	* 删除tmp数据
	* @post:
	**/
	public function delPcert_tmp($where) {
		$result = M($this->pcert_tmp)->where($where)->delete();
		if($result === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//日志
		setLog(4, '合作伙伴证件 tmp 删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}
	/*
	* 获取单条tmp数据
	* @post:
	**/
	public function findPcert_tmp($where, $field='*') {
		return M($this->pcert_tmp)->where($where)->field($field)->find();
	}
	/*
	* 获取关联表单条tmp数据
	* @post:
	**/
	public function findmorePcert_tmp($where, $field='ac.*') {
		return M($this->pcert_tmp)->alias('ac')
				->join(DB_PREFIX.$this->partner.' ag on ag.PARTNER_MAP_ID = ac.PARTNER_MAP_ID')
				->where($where)
				->field($field)
				->find();
	}

}
