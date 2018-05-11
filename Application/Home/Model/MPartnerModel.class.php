<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MPartner	合作伙伴管理
// +----------------------------------------------------------------------
class MPartnerModel extends Model{
	
	function __construct(){
		$this->partner  = "partner";
		$this->branch 	= "branch";
		$this->city   	= "city";
		$this->plevel  	= "plevel";
	}
		
	/*
	* 获取统计数量
	* @post:
	**/
	public function countNewsPartner($where) {
		return M($this->partner)->where($where." and PARTNER_STATUS != '2'")->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getNewsPartnerlist($where, $field='*', $limit, $order='PARTNER_MAP_ID desc') {
		return M($this->partner)->where($where." and PARTNER_STATUS != '2'")->field($field)->limit($limit)->order($order)->select();
	}
	
	//---------------------------------------------
	
	/*
	* 获取统计数量
	* @post:
	**/
	public function countPartner($where) {
		return M($this->partner)->alias('a')
				->join(DB_PREFIX.$this->branch.' b on a.BRANCH_MAP_ID = b.BRANCH_MAP_ID')
				->where($where)
				->count();
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getPartnerlist($where, $field='*', $limit, $order='a.PARTNER_MAP_ID desc') {
		return M($this->partner)->alias('a')
				->join(DB_PREFIX.$this->branch.' b on a.BRANCH_MAP_ID = b.BRANCH_MAP_ID')
				->join('LEFT JOIN '.DB_PREFIX.$this->city.' c on a.CITY_NO = c.CITY_S_CODE')
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
	public function addPartner($data) {
		$result = M($this->partner)->data($data)->add();
		if($result === false) {
			return array('state'=>1, 'msg'=>"添加失败！");
		}
		//日志
		setLog(2, '合作伙伴添加成功！');
		return array('state'=>0, 'msg'=>"添加成功！",'PARTNER_MAP_ID'=>$result);
	}

	/*
	* 修改
	* @post:
	**/
	public function updatePartner($where, $data) {
		$result = M($this->partner)->where($where)->save($data);
		if($result === false) {
			return array('state'=>1, 'msg'=>"修改失败！");
		}
		//日志
		setLog(3, '合作伙伴修改成功！');
		return array('state'=>0, 'msg'=>"修改成功！");
	}
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findPartner($where, $field='a.*,b.*') {
		return M($this->partner)->alias('a')
				->join(DB_PREFIX.$this->branch.' b on a.BRANCH_MAP_ID = b.BRANCH_MAP_ID')
				->join(DB_PREFIX.$this->plevel.' l on a.PARTNER_LEVEL = l.PLEVEL_MAP_ID', 'LEFT')
				->where($where)
				->field($field.',DATE_FORMAT(a.END_TIME,"%Y-%m-%d") AS END_TIME')
				->find();
	}
	/*
	* 删除
	* @post:
	**/
	public function delPartner($where) {
		if (empty($where)) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		$m = M();
		$m->startTrans();	//启用事务
		//删除基本信息
		$r1 = M($this->partner)->where($where)->delete();
		if ($r1 === false) {
			$m->rollback();//不成功，则回滚
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//删除证件信息
		$r2 = M('pcert')->where($where)->delete();
		if ($r2 === false) {
			$m->rollback();//不成功，则回滚
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//删除权限信息
		$r3 = M('pauth')->where($where)->delete();
		if ($r3 === false) {
			$m->rollback();//不成功，则回滚
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//删除银行信息
		$r4 = M('pbact')->where($where)->delete();
		if ($r4 === false) {
			$m->rollback();//不成功，则回滚
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//删除结算信息
		$r5 = M('pcls')->where($where)->delete();
		if ($r5 === false) {
			$m->rollback();//不成功，则回滚
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		//删除其他信息
		$r6 = M('pcfg')->where($where)->delete();
		if($r6 === false) {
			return array('state'=>1, 'msg'=>"删除失败！");
		}
		$m->commit();//成功则提交
		M('pcert_tmp')->where($where)->delete();
		M('pauth_tmp')->where($where)->delete();
		M('pbact_tmp')->where($where)->delete();
		M('pcls_tmp')->where($where)->delete();
		M('pcfg_tmp')->where($where)->delete();
		//日志
		setLog(3, '合作伙伴删除成功！');
		return array('state'=>0, 'msg'=>"删除成功！");
	}

	/*
	* 获取单条信息 不关联
	* @post:
	**/
	public function findPartnerOne($where, $field='*') {
		return M($this->partner)->where($where)
				->field($field.',DATE_FORMAT(END_TIME,"%Y-%m-%d") AS END_TIME')
				->find();
	}

	/*
	* 获取最大编号
	* @post:
	**/
	public function findMaxPartner($where) {
		return M($this->partner)->where($where)->max('PARTNER_NO');
	}

	/*
	* 获取某分支机构下的所有合作伙伴数据
	* @post:
	**/
	public function getPartner_select($where) {
		$list = M($this->partner)->where($where)->field('PARTNER_MAP_ID,PARTNER_NAME')->select();
		foreach($list as $val){
			$res[] = array($val['PARTNER_MAP_ID'], $val['PARTNER_NAME']);
		}
		return $res;
	}

	/*
	*获取当前归属级别
	* @post:
	* $bid :	分支机构id
	* $flag:	selet级别
	* $lvname:	select名称
	* $pid:		合作伙伴id(当编辑时用到)
	* $maxlv:	最大级别
	**/
	public function getlevelsel($bid,$flag,$lvname,$pid,$maxlv='5',$is_root = 0){
		//级别
		$home = session('HOME');
		//测试假数据----------------------------
		/*	$home['BRANCH_MAP_ID'] = 3;
			$home['PARTNER_MAP_ID'] = 2;
			$home['USER_LEVEL'] = 3;*/
		//测试假数据----------------------------
		if (filter_auth($home['BRANCH_MAP_ID']) || $flag == 'all') {
			$lvdata = get_level_data($pid,$bid);
			$lvnum 	= count($lvdata);
			$bid 	= $lvdata[0]['id'];
			$flag 	= ($flag=='nobranch') ? 0 : -1;
		}else{
			if ($pid != '' && $bid !='') {
				$lvdata = get_level_data($pid,$bid);
				$lvnum 	= count($lvdata);
				$bid  	= $lvdata[0]['id'];
				$flag 	= $home['USER_LEVEL']-1;
				//$flag 	= $lvdata[$lvnum-1]['level']; 
			}else{
				$bid 	= $home['BRANCH_MAP_ID'];
				$pid 	= $home['PARTNER_MAP_ID'];
				$flag 	= $home['USER_LEVEL']-1;
				//$maxlv 	= $home['USER_LEVEL']+1;
				$lvdata = get_level_data($pid,$bid);
				$lvnum 	= count($lvdata);
				$bid 	= $lvdata[0]['id'];
			}
		}
		//分支机构
		if ($flag < 0 ) {
			$where = 'BRANCH_LEVEL = 1 and BRANCH_STATUS = 0'.$b_where;
			$b_sel = $this->getB1sel($where,$lvname,$bid,$maxlv,$is_root);
		}else{
			$b_sel = $this->getDefaultsel($lvname,$lvdata['0'],$maxlv,0);
		}
		if ($maxlv == 0) {	
			return $b_sel;
		}
		//合作方(地级市公司)
		if ($flag < 1) {
			$p1_sel = $this->getP1sel('PARTNER_LEVEL = 1 and PARTNER_MAP_ID_P = "0" and PARTNER_STATUS = 0 and BRANCH_MAP_ID = "'.$lvdata['0']['id'].'"',$lvname,$lvdata['1']['id'],$maxlv);
		}else{
			$p1_sel = $this->getDefaultsel($lvname,$lvdata['1'],$maxlv,1);
		}
		if ($maxlv == 1) {
			$res = $b_sel.$p1_sel;		
			return $res;
		}
		//合作方(区县公司)
		if ($flag < 2) {
			$where = 'PARTNER_LEVEL = 2 and PARTNER_STATUS = 0 and BRANCH_MAP_ID = '.$bid.' and PARTNER_MAP_ID_P = "'.$lvdata['1']['id'].'"';
			$p2_sel = $this->getP2sel($where,$lvname,$lvdata['2']['id'],$maxlv);
		}else{
			$p2_sel = $this->getDefaultsel($lvname,$lvdata['2'],$maxlv,2);
		}
		if ($maxlv == 2) {
			$res = $b_sel.$p1_sel.$p2_sel;
			return $res;
		}
		//合作方(推广中心)
		if ($flag < 3) {
			$where = 'PARTNER_LEVEL = 3 and PARTNER_STATUS = 0 and BRANCH_MAP_ID = '.$bid.' and PARTNER_MAP_ID_P = "'.$lvdata['2']['id'].'"';
			$p3_sel = $this->getP3sel($where,$lvname,$lvdata['3']['id'],$maxlv);
		}else{
			$p3_sel = $this->getDefaultsel($lvname,$lvdata['3'],$maxlv,3);
		}
		if ($maxlv == 3) {
			$res = $b_sel.$p1_sel.$p2_sel.$p3_sel;		
			return $res;
		}
		//合作方(发卡中心)
		if ($flag < 4) {
			$where = 'PARTNER_LEVEL = 4 and PARTNER_STATUS = 0 and BRANCH_MAP_ID = '.$bid.' and PARTNER_MAP_ID_P = "'.$lvdata['3']['id'].'"';
			$p4_sel = $this->getP4sel($where,$lvname,$lvdata['4']['id'],$maxlv);
		}else{
			$p4_sel = $this->getDefaultsel($lvname,$lvdata['4'],$maxlv,4);
		}
		//商户
		/*$slist = D('MShop')->getShop_select($post['SHOP_MAP_ID']);
		foreach($blist as $val){
			$selected  = $val['SHOP_MAP_ID']==$selected ? 'selected' : '';
			$s .= '<option value="'.$val['SHOP_MAP_ID'].'" '.$selected.'>'.$val['SHOP_NAME'].'</option>';
		}*/
		$res = $b_sel.$p1_sel.$p2_sel.$p3_sel.$p4_sel;		
		return $res;
	}

	/*
	* 分支机构
	* @post:
	**/
	public function getB1sel($where,$lvname,$selid,$maxlv,$is_root){
		$blist = D('MBranch')->getBranch_select($where);
		if (empty($blist)) {
			return '';
		}
		$unique_name = substr(md5($lvname),8,8); // 8位MD5加密 
		$b_sel = '<select class="combox '.$unique_name.'" name="'.$lvname.'" levelsel="0" flag="0" maxlv="'.$maxlv.'">';
		$b_sel .= '<option value="">请选择</option>';
		if ($is_root != 0) {
			$selected = $selid ==='100000'? 'selected' : '';
			$b_sel .= '<option value="100000" '.$selected.'>总部</option>';
		}
		
		foreach($blist as $val){
			$selected = $val['0']==$selid ? 'selected' : '';
			$b_sel .= '<option value="'.$val['0'].'" '.$selected.'>'.$val['1'].'</option>';
		}
		$b_sel.='</select>';
		return $b_sel;
	}

	/*
	* 合作方(地级子公司)
	* @post:
	**/
	public function getP1sel($where,$lvname,$selid,$maxlv){
		//合作方(地级子公司)
		$p1_list = $this->getPartner_select($where);
		if (empty($p1_list)) {
			return '';
		}
		$unique_name = substr(md5($lvname),8,8); // 8位MD5加密 
		$p1_sel = '<select class="combox '.$unique_name.'" name="'.$lvname.'" levelsel="1" flag="1" maxlv="'.$maxlv.'">';
		$p1_sel .= '<option value="">请选择</option>';
		foreach($p1_list as $val){
			$selected  = $val['0']==$selid ? 'selected' : '';
			$p1_sel .= '<option value="'.$val['0'].'" '.$selected.'>'.$val['1'].'</option>';
		}
		$p1_sel .= '<select>';
		return $p1_sel;
	}

	/*
	* 合作方(区县公司)
	* @post:
	**/
	public function getP2sel($where,$lvname,$selid,$maxlv){
		$p2_list = D('MPartner')->getPartner_select($where);
		if (empty($p2_list)) {
			return '';
		}
		$unique_name = substr(md5($lvname),8,8); // 8位MD5加密 
		$p2_sel = '<select class="combox '.$unique_name.'" name="'.$lvname.'" levelsel="2" flag="2" maxlv="'.$maxlv.'">';
		$p2_sel .= '<option value="">请选择</option>';
		foreach($p2_list as $val){
			$selected  = $val['0']==$selid ? 'selected' : '';
			$p2_sel .= '<option value="'.$val['0'].'" '.$selected.'>'.$val['1'].'</option>';
		}
		$p2_sel .= '</select>';
		return $p2_sel;
	}

	/*
	* 合作方(推广中心)
	* @post:
	**/
	public function getP3sel($where,$lvname,$selid,$maxlv){
		$p3_list = D('MPartner')->getPartner_select($where);
		if (empty($p3_list)) {
			return '';
		}
		$unique_name = substr(md5($lvname),8,8); // 8位MD5加密 
		$p3_sel = '<select class="combox '.$unique_name.'" name="'.$lvname.'" levelsel="3" flag="3" maxlv="'.$maxlv.'">';
		$p3_sel .= '<option value="">请选择</option>';
		foreach($p3_list as $val){
			$selected  = $val['0']==$selid ? 'selected' : '';
			$p3_sel .= '<option value="'.$val['0'].'" '.$selected.'>'.$val['1'].'</option>';
		}
		$p3_sel .= '</select>';
		
		return $p3_sel;
	}

	/*
	* 合作方(发卡中心)
	* @post:
	**/
	public function getP4sel($where,$lvname,$selid,$maxlv){
		$p4_list = D('MPartner')->getPartner_select($where);
		if (empty($p4_list)) {
			return '';
		}
		$unique_name = substr(md5($lvname),8,8); // 8位MD5加密 
		$p4_sel = '<select class="combox '.$unique_name.'" name="'.$lvname.'" levelsel="4" flag="4" maxlv="'.$maxlv.'">';
		$p4_sel .= '<option value="">请选择</option>';
		foreach($p4_list as $val){
			$selected  = $val['0']==$selid ? 'selected' : '';
			$p4_sel .= '<option value="'.$val['0'].'" '.$selected.'>'.$val['1'].'</option>';
		}
		$p4_sel .= '</select>';
		return $p4_sel;
	}

	/*
	* 默认级别下拉项
	* @post:
	**/
	public function getDefaultsel($lvname,$option_data,$maxlv,$lv_num){
		$unique_name = substr(md5($lvname),8,8); // 8位MD5加密 
		$sel_str = '<select class="combox '.$unique_name.'" name="'.$lvname.'" levelsel="'.$lv_num.'" flag="'.$lv_num.'" maxlv="'.$maxlv.'">';
		//$sel_str .= '<option value="">请选择</option>';
		$sel_str .= '<option selected value="'.$option_data['id'].'">'.$option_data['name'].'</option>';
		$sel_str .='</select>';
		return $sel_str;
	}
	/*
	* 默认级别下拉项
	* @post:
	**/
	public function getCompanypsel($pid,$post_name='PARTNER_MAP_ID_G'){
		$bid = '';$time = getmicrotime();
		if (empty($pid)) {
			$result['BRANCH_MAP_ID'] = $bid;
		}else{
			$where = "PARTNER_G_FLAG = 2 and PARTNER_MAP_ID = '".$pid."' and PARTNER_STATUS = 0"; 
			$result = $this->findPartnerOne($where, 'BRANCH_MAP_ID,PARTNER_MAP_ID,PARTNER_NAME');
		}
		//获取已选中的分公司, 下拉列表
		//if (!empty($code)) {
			$g_pid = $this->getPartner_select("PARTNER_G_FLAG = 2 and BRANCH_MAP_ID = '".$result['BRANCH_MAP_ID']."'");
			if ($g_pid) {
				foreach($g_pid as $val){
					//设置MCC码选中项
					if ($pid == $val['0']) {
						$g_pid .= '<option value="'.$val['0'].'" selected >'.$val['1'].'</option>';
					}else{
						$g_pid .= '<option value="'.$val['0'].'">'.$val['1'].'</option>';
					}
				}
			}else{
				$g_pid = '<option value="">暂无数据</option>';
			}
		//}
		//获取已选中的集团合作伙伴, 下拉列表
		$g_bid_sel = D('MBranch')->getBranchlist('BRANCH_STATUS = 0','BRANCH_MAP_ID,BRANCH_NAME');
		foreach($g_bid_sel as $key => $val){
			//设置MCC类选中项
			if ($result['BRANCH_MAP_ID'] == $val['BRANCH_MAP_ID']) {
				$mt .= '<option value="'.$val['BRANCH_MAP_ID'].'" selected >'.$val['BRANCH_NAME'].'</option>';
			}else{
				$mt .= '<option value="'.$val['BRANCH_MAP_ID'].'">'.$val['BRANCH_NAME'].'</option>';
			}
		}
		$res = '<select class="combox" name="G_P_BID" ref="combox_shop_p_'.$time.'" refUrl="'.__MODULE__.'/Public/ajaxgetcompany/pid/{value}">
					  <option value="">请选择</option>'.$mt.'
				</select>
				<select class="combox" name="'.$post_name.'" id="combox_shop_p_'.$time.'">
					  '.$g_pid.'
				</select>';		
		return $res;
	}
}
