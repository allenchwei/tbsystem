<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MShop	商户基本信息管理
// +----------------------------------------------------------------------
class MShopModel extends Model{
	
	function __construct(){
		$this->shop   = "shop";
		$this->partner= "partner";
		$this->branch = "branch";
		$this->sposreq= "sposreq";
	}
	/*
	* 获取统计数量[优化]
	* @post:
	**/
	public function countNewShop($where) {
		return M($this->shop)->where($where)->count();
	}
	
	/*
	* 获取列表[优化]
	* @post:
	**/
	public function getNewShoplist($where, $field='*', $limit, $order='SHOP_MAP_ID desc') {
		$list = M($this->shop)->where($where)
				->field($field.',DATE_FORMAT(CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
		if ($list) {
			foreach ($list as $kk => $val) {
				$blist = M($this->branch)->where('BRANCH_MAP_ID = '.$val['BRANCH_MAP_ID'])->field('BRANCH_NAME')->find();
				$plist = M($this->partner)->where('PARTNER_MAP_ID = '.$val['PARTNER_MAP_ID'])->field('PARTNER_NAME')->find();
				$list[$kk]['BRANCH_NAME']  = $blist['BRANCH_NAME'];
				$list[$kk]['PARTNER_NAME'] = $plist['PARTNER_NAME'];
				$splist = M('sposreq')->where('SHOP_MAP_ID = '.$val['SHOP_MAP_ID'])->field('INSTALL_FLAG')->select();
				foreach ($splist as $key => $value) {
					if ($value['INSTALL_FLAG'] == 0) {
						$list[$kk]['INSTALL_FLAG'] =  0 ;
						break;	//跳出循环
					}
				}
			}
		}
		return $list;
	}

	/*
	* 获取关联单条信息[优化]
	* @post:
	**/
	public function findmoreNewShop($where, $field='*') {
		$list = M($this->shop)->where($where)
				->field($field.',DATE_FORMAT(CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->find();
		//处理开关时间
		if ($list) {
			$blist = M($this->branch)->where('BRANCH_MAP_ID = '.$list['BRANCH_MAP_ID'])->field('BRANCH_NAME')->find();
			$plist = M($this->partner)->where('PARTNER_MAP_ID = '.$list['PARTNER_MAP_ID'])->field('PARTNER_NAME')->find();
			$list['BRANCH_NAME']  	= $blist['BRANCH_NAME'];
			$list['PARTNER_NAME'] 	= $blist['PARTNER_NAME'];
			$list['SHOP_OPENTIME'] 	= date('H:i:s',strtotime(date('Ymd').$list['SHOP_OPENTIME']));
			$list['SHOP_CLOSETIME'] = date('H:i:s',strtotime(date('Ymd').$list['SHOP_CLOSETIME']));
		}
		return $list;
	}
	//----------------结束----------------
	/*
	* 获取统计数量
	* @post:
	**/
	public function countShop($where) {
		return M($this->shop)->alias('s')
				->join(DB_PREFIX.$this->branch.' b on s.BRANCH_MAP_ID = b.BRANCH_MAP_ID','LEFT')
				->join(DB_PREFIX.$this->partner.' a on s.PARTNER_MAP_ID = a.PARTNER_MAP_ID','LEFT')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getShoplist($where, $field='s.*', $limit, $order='s.SHOP_MAP_ID desc') {
		$list = M($this->shop)->alias('s')
				->join(DB_PREFIX.$this->branch.' b on s.BRANCH_MAP_ID = b.BRANCH_MAP_ID','LEFT')
				->join(DB_PREFIX.$this->partner.' p on s.PARTNER_MAP_ID = p.PARTNER_MAP_ID','LEFT')
				->where($where)
				->field($field.',DATE_FORMAT(s.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->limit($limit)
				->order($order)
				->select();
		if ($list) {
			foreach ($list as $kk => $val) {
				$splist = M('sposreq')->where('SHOP_MAP_ID = '.$val['SHOP_MAP_ID'])->field('INSTALL_FLAG')->select();
				foreach ($splist as $key => $value) {
					if ($value['INSTALL_FLAG'] == 0) {
						$list[$kk]['INSTALL_FLAG'] =  0 ;
						break;	//跳出循环
					}
				}
			}
		}
		return $list;
	}

	/*
	* 添加
	* @post:
	**/
	public function addShop($data) {
		$result = M($this->shop)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '商户添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！",'SHOP_MAP_ID'=>$result);
	}

	/*
	* 修改
	* @post:
	**/
	public function updateShop($where, $data) {
		$result = M($this->shop)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '商户修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findShop($where, $field='*',$order='SHOP_NO DESC') {
		$res = M($this->shop)->where($where)->field($field.',DATE_FORMAT(CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')->find();
		//处理开关时间
		if ($res) {
			$res['SHOP_OPENTIME'] = date('H:i:s',strtotime(date('Ymd').$res['SHOP_OPENTIME']));
			$res['SHOP_CLOSETIME'] = date('H:i:s',strtotime(date('Ymd').$res['SHOP_CLOSETIME']));
		}
		return $res;
	}
	
	/*
	* 获取关联单条信息
	* @post:
	**/
	public function findmoreShop($where, $field='s.*') {
		$list = M($this->shop)->alias('s')
				->join('LEFT JOIN '.DB_PREFIX.$this->branch.' b on s.BRANCH_MAP_ID = b.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->partner.' a on s.PARTNER_MAP_ID = a.PARTNER_MAP_ID')
				->where($where)
				->field($field.',DATE_FORMAT(s.CREATE_TIME,"%Y-%m-%d %H:%i:%s") AS CREATE_TIME')
				->find();
		//处理开关时间
		if ($list) {
			$list['SHOP_OPENTIME'] = date('H:i:s',strtotime(date('Ymd').$list['SHOP_OPENTIME']));
			$list['SHOP_CLOSETIME'] = date('H:i:s',strtotime(date('Ymd').$list['SHOP_CLOSETIME']));
		}
		return $list;
	}

	/*
	* 获取某分支机构下的所有商户数据
	* @post:
	**/
	public function getShop_select($where) {
		$list = M($this->shop)->where($where)->field()->select();
		foreach($list as $val){
			$res[] = array($val['SHOP_MAP_ID'], $val['SHOP_NAME']);
		}
		return $res;
	}

	/*
	* 获取集团商户下拉联动
	* @post:
	**/
	public function getShoppsel($shop_p_id = '',$post_name='SHOP_MAP_ID_P') {
		$bid = '';$time = getmicrotime();
		if (empty($shop_p_id)) {
			$result['BRANCH_MAP_ID'] = $bid;
		}else{
			$where = "SHOP_LEVEL = 2 and SHOP_MAP_ID = '".$shop_p_id."'"; 
			$result = $this->findShop($where, 'BRANCH_MAP_ID,SHOP_MAP_ID,SHOP_NAME');
		}
		//获取已选中的MCC码, 下拉列表
		//if (!empty($code)) {
			$shop_pid = $this->getShop_select("SHOP_LEVEL = 2 and BRANCH_MAP_ID = '".$result['BRANCH_MAP_ID']."'");
			if ($shop_pid) {
				foreach($shop_pid as $val){
					//设置MCC码选中项
					if ($shop_p_id == $val['0']) {
						$shop_pid .= '<option value="'.$val['0'].'" selected >'.$val['1'].'</option>';
					}else{
						$shop_pid .= '<option value="'.$val['0'].'">'.$val['1'].'</option>';
					}
				}
			}else{
				$shop_pid = '<option value="">暂无数据</option>';
			}
		//}
		//获取已选中的MCC类, 下拉列表
		$shop_bid_sel = D('MBranch')->getBranchlist('BRANCH_STATUS = 0','BRANCH_MAP_ID,BRANCH_NAME');
		foreach($shop_bid_sel as $key => $val){
			//设置MCC类选中项
			if ($result['BRANCH_MAP_ID'] == $val['BRANCH_MAP_ID']) {
				$mt .= '<option value="'.$val['BRANCH_MAP_ID'].'" selected >'.$val['BRANCH_NAME'].'</option>';
			}else{
				$mt .= '<option value="'.$val['BRANCH_MAP_ID'].'">'.$val['BRANCH_NAME'].'</option>';
			}
		}
		$res = '<select class="combox" name="SHOP_P_BID" ref="combox_shop_p_'.$time.'" refUrl="'.__MODULE__.'/Public/ajaxgetshop_p/pid/{value}">
					  <option value="">请选择</option>'.$mt.'
				</select>
				<select class="combox" name="'.$post_name.'" id="combox_shop_p_'.$time.'">
					  '.$shop_pid.'
				</select>';		
		return $res;
	}

	/*
	* 获取关联单条信息
	* @post:
	**/
	public function findmoreShop2($where, $field='s.*') {
		$list = M($this->shop)->alias('s')
				->join('LEFT JOIN '.DB_PREFIX.'scert scert on s.SHOP_MAP_ID = scert.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.'smdr smdr on s.SHOP_MAP_ID = smdr.SHOP_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.'sbact sbact on s.SHOP_MAP_ID = sbact.SHOP_MAP_ID')
				->where($where)
				->field($field.',scert.*,smdr.*,sbact.*')
				->find();
		return $list;
	}
}
