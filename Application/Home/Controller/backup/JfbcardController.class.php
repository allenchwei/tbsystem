<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  积分宝卡片
// +----------------------------------------------------------------------
class JfbcardController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->GMakecard = 'GMakecard';
		$this->GVipcard	 = 'GVipcard';
		$this->GVipModel = 'GVipModel';
		$this->GOutcard	 = 'GOutcard';
		$this->GLap	 	 = 'GLap';
		$this->MBranch	 = 'MBranch';
		$this->MPartner	 = 'MPartner';
		$this->MUser	 = 'MUser';
		$this->MCproduct = 'MCproduct';
		$this->MExcel 	 = 'MExcel';
		$this->GVip 	 = 'GVip';
	}
		
	/*
	* 积分宝制卡
	**/
	public function makecard() {
		$post = I('post');
		if($post['submit'] == "makecard"){
			$where = "1=1";
			$mkplv = filter_data('mkplv');	//列表查询
			//分公司
			if($mkplv['bid'] != '') {
				$where .= " and BRANCH_MAP_ID = '".$mkplv['bid']."'";
				$post['bid'] = $mkplv['bid'];
			}
			//状态
			if($post['OUT_STATUS'] != '') {
				$where .= " and OUT_STATUS = '".$post['OUT_STATUS']."'";
			}
			//批次号
			if($post['CARD_BATCH']) {
				$where .= " and CARD_BATCH = '".$post['CARD_BATCH']."'";
			}
			
			//分页
			$count = D($this->GMakecard)->countMakecard($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->GMakecard)->getMakecardlist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'&'.http_build_query($post) );
		}
		//获取分公司下拉列表
		$branchsel = D($this->MBranch)->getBranch_select();
		$this->assign ('branchsel', $branchsel);		//分公司
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display();
	}
	/*
	* 积分宝制卡 制卡申请
	**/
	public function makecard_add() {
		ini_set('memory_limit', '250M'); //内存限制
		set_time_limit(0); //
		$makecard  = I('makecard');	
		//获取上次制卡序号
		$last_card_sn = M('sys')->field('CARD_SN')->find();
		$home = session('HOME');
		if($makecard['submit'] == "makecard_add") {
			//制卡公司bid
			$makepid = get_level_val('makepid');		
			$makecard['BRANCH_MAP_ID'] = $makepid['bid'];
			//数据验证
			if(empty($makecard['CARD_NUM']) || empty($makecard['BRANCH_MAP_ID']) || empty($makecard['CARD_BEGIN']) || empty($makecard['CARD_END']))
			{
				$this->wrong("请填写必填项！");
			}
			if (($makecard['CARD_END'] - $makecard['CARD_BEGIN']+1) != $makecard['CARD_NUM']) {
				$this->wrong("制卡数量与卡片序号不符！");
			}
			if ($makecard['CARD_NUM'] > 10000) {
				$this->wrong("一批最多制卡数量由10000张!");
			}
			//组装数据
			$makecard_data = array(
				'BRANCH_MAP_ID'		=>	$makecard['BRANCH_MAP_ID'],
				'ZONE_CODE'			=>	0,
				'CARD_BIN'			=>	0,
			  //'CARD_P_MAP_ID'		=>	0,			//卡套餐ID 0虚拟卡1收费卡2预免卡
				'CARD_NAME'			=>	0,
				'CARD_NUM'			=>	$makecard['CARD_NUM'],
				'CARD_BEGIN'		=>	$makecard['CARD_BEGIN'],
				'CARD_END'			=>	$makecard['CARD_END'],
				'OUT_STATUS'		=>	1, 			//发卡过程(0已制卡,1在途中)
				'OUT_DATE'			=>	date('Ymd'),
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'RES'				=>	$makecard['RES']
			);

			$M = M('', DB_PREFIX_GLA, DB_DSN_GLA);
			$M->startTrans();	//启用事务
		
			//添加制卡记录
			$res = D($this->GMakecard)->addMakecard($makecard_data);
			if($res['state']!=0){
				$this->wrong('制卡记录添加失败！');
			}
			//向VIPCARD表中添加数据
			$n = 0;				//制卡次数
			$n1 = 0;			//制卡起始数量
			$onetime = 5000;	//每次制卡数量
			$m_vipcard = D($this->GVipcard);
			$pp = ceil($makecard_data['CARD_NUM']/$onetime);
			for ($n=0; $n < $pp; $n++) {
				$n2 = ($n+1) * $onetime;
				if ($n2 > $makecard_data['CARD_NUM']) {
					$n2 = $makecard_data['CARD_NUM'];
				}
				for ($i=$n1; $i < $n2; $i++) {
					//组装数据
					$card_no = $makecard_data['CARD_BEGIN']+$i;
					$vipcard_data[] = array(
						'BRANCH_MAP_ID'		=>	$makecard_data['BRANCH_MAP_ID'],	//隶属分支
						'BRANCH_MAP_ID1'	=>	$makecard_data['BRANCH_MAP_ID'],	//隶属分支
						'PARTNER_MAP_ID'	=>	0,									//分配创业合伙人
						'PARTNER_MAP_ID1'	=>	0,									//隶属创业合伙人
						'VIP_ID'			=>	'9'.substr($card_no, 8),			//会员代码
						'CARD_NO'			=>	$card_no,							//卡号号码
						'CARD_BIN'			=>	0,									//卡套餐类
					  //'CARD_P_MAP_ID'		=>	2,									//卡套餐ID
						'CARD_STATUS'		=>	8,									//卡号状态
						'CARD_EXP'			=>	date('ym'),							//卡有效期
						'CARD_TRACK2'		=>	$card_no.'='.getCardCheck($card_no),//卡二磁道
						'CARD_CHECK'		=>	getrand_code(),						//卡校验值
						'CARD_BIRTHDAY'		=>	0,									//开户日期
						'CARD_BATCH'		=>	$res['CARD_BATCH'],					//发卡批次
						'ACTIVE_TIME'		=>	date('YmdHis'),						//激活时间
						'UPDATE_TIME'		=>	date('YmdHis')						//修改时间
					);
				}
				$vip_res = $m_vipcard->addAllvipcard($vipcard_data);
				if ($vip_res['state']!=0){
					$M->rollback();	//回滚
					$this->wrong('批量制卡失败！');
				}
				$vipcard_data = array();
				$n1 += $onetime;
			}
			
			//更新sys
			$card_num = $last_card_sn['CARD_SN']+$makecard_data['CARD_NUM'];
			$updata_sys = M('sys')->where('SYSTEM_STATUS != ""')->save(array('CARD_SN' => $card_num));
			if ($updata_sys === false) {
				$M->rollback();	//回滚
				$this->wrong('制卡更新失败！');
			}
			$M->commit();	//回滚
			//更新制卡数量
			$upres = D($this->GMakecard)->updateMakecard('CARD_BATCH = "'.$res['CARD_BATCH'].'"', array('CARD_NUM' => $makecard_data['CARD_NUM']));
			$this->right('成功制卡'.$makecard['CARD_NUM'].'张', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		//获取上次制卡批次
		$last_card_batch = D($this->GMakecard)->findMakecard('','CARD_BATCH');
		if ($last_card_sn) {
			$num = setStrzero($last_card_sn['CARD_SN'],8);				//默认开始
			$num1 = setStrzero(($last_card_sn['CARD_SN']),8);			//默认结束
			$last_card_sn = substr($num, 0,4).'　'.substr($num, 4,8);	//默认开始(格式)
			$last_card_sn1 = substr($num1, 0,4).'　'.substr($num1, 4,8);//默认结束(格式)
		}else{
			$last_card_sn = '0000　0000';
		}
	
		//获取分公司下拉列表
		//$branchsel = D($this->MBranch)->getBranch_select();
		//$this->assign ('branchsel', $branchsel);				//分公司
		$this->assign ('last_card_sn', $last_card_sn);			//起始卡号
		$this->assign ('last_card_sn1', $last_card_sn1);		//结束卡号
		$this->assign ('beginnum', $num);						//起始卡号的后8位
		$this->assign ('endnum', $num1);						//结束卡号的后8位
		$this->assign ('last_card_batch', $last_card_batch['CARD_BATCH']);	//上次制卡批次
		$this->assign ('homedata', $home);						//当前用户SESSION
		$this->display();
	}
	/*
	* 积分宝制卡 详情
	**/
	public function makecard_show() {
		$card_batch = I('id');
		if (!$card_batch) {
			$this->wrong('缺少参数！');
		}
		$where = 'CARD_BATCH = '.$card_batch;
		//获取制卡记录信息
		$res = D($this->GMakecard)->findMakecard($where);
		$this->assign('makecard_data',$res);
		$this->display();		
	}
	/*
	* 积分宝制卡 Excel导出
	**/
	public function makecard_export() {
		$post  = array(
			'id' =>	I('id')
		);
		if (empty($post['id'])) {
			$this->wrong('导出数据失败, 请选择导出的记录!');
		}
		//获取制卡信息
		$makecard_data = D($this->GMakecard)->findMakecard("CARD_BATCH = '".$post['id']."'",'BRANCH_MAP_ID,CARD_BATCH,CARD_BEGIN,CARD_END');
		if ($makecard_data['CARD_BEGIN'] and $makecard_data['CARD_END']) {
			$where = "vc.CARD_BATCH = '".$post['id']."'";
			//计算
			$count = D($this->GVipcard)->countVipcardmore($where);
			$numPort = floor($count/C('PAGE_COUNT_EXPORT'));
			$urlPort = __ACTION__.'?submit=ajax&'.http_build_query($post);
		}
		$strPort = '';
		if($count > 0){
			for($i=0; $i<=$numPort; $i++){
				$strPort .= '<p><a href="'.$urlPort.'&p='.($i).'"><button class="ch-btn-skin ch-btn-small ch-icon-copy">文件('.($i+1).')</button></a></p>';
			}
		}else{
			$strPort .= '<p>暂无数据可下载~</p>';
		}
		$this->assign ( 'strPort', 	$strPort );
		
		//导出
		$submit = I('submit');
		$p 		= I('p');
		if($submit == 'ajax'){
			$bRow = $p * C('PAGE_COUNT_EXPORT');
			$eRow = C('PAGE_COUNT_EXPORT');
			$list  = D($this->GVipcard)->getVipcardmore($where, 'vc.*,vp.VIP_NAME,vp.VIP_MOBILE,vp.VIP_CITY,vp.VIP_SEX,vp.VIP_BIRTHDAY,vp.CREATE_TIME', $bRow.','.$eRow);
			//导出操作
			$xlsname = '积分宝制卡信息数据';
			$xlscell = array(
				array('CARD_NO',		'卡号'),
				array('GUISHU',			'归属'),
				array('CARD_STATUS',	'卡片状态'),
				array('CARD_CHECK',		'卡片校验码'),
				array('CARD_P_MAP_ID',	'卡套餐'),
				array('VIP_NAME',		'会员'),
				array('VIP_MOBILE',		'会员手机号'),
				array('VIP_CITY',		'所在城市'),
				array('VIP_SEX',		'性别'),
				array('VIP_BIRTHDAY',	'生日'),
				array('CREATE_TIME',	'创建日期')
			);
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'CARD_NO'		=>	$val['CARD_NO']."\t",
					'GUISHU'		=>	get_level_name($val['PARTNER_MAP_ID'], $val['BRANCH_MAP_ID']),
					'CARD_STATUS'	=>	C('CARD_STATUS')[$val['CARD_STATUS']],
					'CARD_CHECK'	=>	$val['CARD_CHECK']."\t",
					'CARD_P_MAP_ID'	=>	C('CARD_P_MAP_ID')[$val['CARD_P_MAP_ID']],
					'VIP_NAME'		=>	$val['VIP_NAME'],
					'VIP_MOBILE'	=>	$val['VIP_MOBILE']."\t",
					'VIP_CITY'		=>	getcity_name($val['VIP_CITY']),
					'VIP_SEX'		=>	C('VIP_SEX')[$val['VIP_SEX']],
					'VIP_BIRTHDAY'	=>	$val['VIP_BIRTHDAY']."\t",
					'CREATE_TIME'	=>	$val['CREATE_TIME']."\t"
				);	
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		$this->display('Public/export');
	}
	/*
	* 积分宝制卡 完成
	**/
	public function makecard_finish() {
		//获取制卡id
		$card_batch = $_REQUEST['id'];
		if (!$card_batch) {
			$this->wrong('缺少参数！');
		}
		$where = 'CARD_BATCH = '.$card_batch;
		//获取制卡记录信息
		$res = D($this->GMakecard)->findMakecard($where);
		if (empty($res)) {
			$this->wrong('获取制卡记录信息失败!');
		}
		//判断是否已经制卡
		if ($res['OUT_STATUS']==0) {
			$this->wrong('当前已制卡,不能重复制卡!');
		}
		
		$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		$m->startTrans();//开启事务
		//修改卡片状态为库存
		$vip_res = D($this->GVipcard)->updateVipcard($where, array('CARD_STATUS' => 1));
		if ($vip_res['state'] != 0) {
			$m->rollback();		//不成功，则回滚
			$this->wrong('制卡失败!');
		}
		//修改制卡记录状态
		$upmakeres = D($this->GMakecard)->updateMakecard($where, array('OUT_STATUS' => 0));
		if ($upmakeres['state'] != 0) {
			$m->rollback();		//不成功，则回滚
			$this->wrong('制卡记录更新失败!');
		}
		$m->commit();			//全部成功则提交
		$this->right('制卡成功');
	}
	
	
	
	/*
	* 卡片流转
	**/
	public function outcard() {
		$post = I('post');
		if($post['submit'] == "outcard"){
			$where  = "OUT_FLAG = '0'";
			$falv   = filter_data('falv');		//列表查询
			$shoulv = filter_data('shoulv');	//列表查询
			//发货方
			if($falv['bid'] != '') {
				$where .= " and BRANCH_MAP_ID_OUT = '".$falv['bid']."'";
				$post['fabid'] = $falv['bid'];
			}
			if($falv['pid'] != '') {
				$where .= " and PARTNER_MAP_ID_OUT = '".$falv['pid']."'";
				$post['fapid'] = $falv['pid'];
			}
			//收货方
			if($shoulv['bid'] != '') {
				$where .= " and BRANCH_MAP_ID_IN = '".$shoulv['bid']."'";
				$post['shoubid'] = $shoulv['bid'];
			}
			if($shoulv['pid'] != '' && $shoulv['pid'] != $falv['pid']) {
				$where .= " and PARTNER_MAP_ID_IN = '".$shoulv['pid']."'";
				$post['shoupid'] = $shoulv['pid'];
			}
			//日期
			if($post['START_DATE'] != '') {
				$where .= " and OUT_DATE >= '".date('Ymd',strtotime($post['START_DATE']))."'";
			}
			if($post['END_DATE'] != '') {
				$where .= " and OUT_DATE <= '".date('Ymd',strtotime($post['END_DATE']))."'";
			}
			//批次号
			if($post['CARDOUT_BATCH_ID']) {
				$where .= " and CARDOUT_BATCH_ID = '".$post['CARDOUT_BATCH_ID']."'";
			}
			//分页
			$count = D($this->GOutcard)->countOutcard($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->GOutcard)->getOutcardlist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}else{
			$pdata = array(
				'START_DATE' => date('Y-m-d',strtotime('-1 month')),
				'END_DATE'   => date('Y-m-d')
			);
			$this->assign ( 'postdata', 	$pdata);
		}
		$home = session('HOME');
		$this->assign ( 'home', $home);
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 卡片流转 分配
	**/
	public function outcard_allot() {
		$outcard = I('outcard');
		$home = session('HOME');
		if (empty($home['PARTNER_MAP_ID'])) {
			//获取当分公司数据
			$where1 = "BRANCH_MAP_ID = '".$home['BRANCH_MAP_ID']."'";
			//$b_data = D($this->MBranch)->findBranch($where1, 'BRANCH_NAME,ADDRESS,MANAGER,MOBILE');
			//$b_data['DELIVER_NAME'] = $b_data['BRANCH_NAME'];
			$maxnum = D($this->GOutcard)->findBatchmax($where);	//查找最大批次号
		}else{
			//获取当前合作伙伴公司数据
			$where1 = "BRANCH_MAP_ID = '".$home['BRANCH_MAP_ID']."' and PARTNER_MAP_ID = '".$home['PARTNER_MAP_ID']."'";
			//$b_data = D($this->MPartner)->findPartner($where1, 'a.PARTNER_NAME,a.ADDRESS,a.MANAGER,a.MOBILE');
			//$b_data['DELIVER_NAME'] = $b_data['PARTNER_NAME'];
			$maxnum = D($this->GOutcard)->findBatchmax($where);	//查找最大批次号
		}
		
		if($outcard['submit'] == "outcard_allot") {
			//验证数据
			$lvdata = get_level_val('plv');
			if($home['BRANCH_MAP_ID'] != 100000){	//如果是总部操作, 那不做限制
				if (empty($lvdata['pid'])) {
					$this->wrong('请选择到合作伙伴');
				}
				//查找对应的操作员数据
				$user_data = D($this->MUser)->findUser('PARTNER_MAP_ID = "'.$lvdata['pid'].'"','USER_ID,USER_NAME');
				if (!$user_data) {
					$this->wrong('未查到收货方的操作员数据, 请给收货方分配一个操作员');
				}
			}else{
				//查找对应的操作员数据
				$user_data = D($this->MUser)->findUser('BRANCH_MAP_ID = "'.$lvdata['bid'].'" and PARTNER_MAP_ID = 0','USER_ID,USER_NAME');
				if (!$user_data) {
					$this->wrong('未查到收货方的操作员数据, 请给收货方分配一个操作员');
				}
			}
			if (empty($outcard['CARD_NUM'])) {
				$this->wrong('请添写必填项');
			}
			//组装记录数据
			$outcard_data = array(
				'CARD_BATCH' 		=> 0, 
				'BRANCH_MAP_ID_OUT' => $home['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID_OUT'=> $home['PARTNER_MAP_ID'], 
				'BRANCH_MAP_ID_IN'	=> $lvdata['bid'], 
				'PARTNER_MAP_ID_IN'	=> $lvdata['pid'] ? $lvdata['pid'] : 0, 
				'OUT_FLAG'			=> 0, 
				'CARDOUT_BATCH_ID' 	=> $maxnum+1,
				'CARD_BIN' 			=> 0, 
				'CARD_NAME' 		=> 0, 
				'CARD_NUM' 			=> $outcard['CARD_NUM'], 
				'CARD_BEGIN' 		=> $outcard['CARD_BEGIN'], 
				'CARD_END' 			=> $outcard['CARD_END'], 
				'OUT_DATE' 			=> date('Ymd'), 
				'CREATE_USERID' 	=> $home['USER_ID'], 
				'CREATE_USERNAME'	=> $home['USER_NAME'], 
				'RECV_USERID' 		=> $user_data['USER_ID']
			);
			
			//组装修改数据
			$vipcard_data = array(
				'BRANCH_MAP_ID'  => $lvdata['bid'],
				'PARTNER_MAP_ID' => $lvdata['pid'] ? $lvdata['pid'] : 0,
				'UPDATE_TIME' 	 => date('YmdHis'),
			);
			//判断用户选择的是连续卡号还是非连续的
			$cardno_type = I('cardno_type');
			if($cardno_type == 0) {
				if (($outcard['CARD_END'] - $outcard['CARD_BEGIN']) < 0 || ($outcard['CARD_END'] - $outcard['CARD_BEGIN']) > 10000) {
					$this->wrong('开始卡号不能大于结束卡号并且要小于10000张');
				}
				//判断卡号和分配数量是否合法
				$cardnum = D($this->GVipcard)->countVipcard("(CARD_NO between '".$outcard['CARD_BEGIN']."' and '".$outcard['CARD_END']."') and CARD_STATUS=1 and ".$where1);
				//判断数量
				if ($outcard['CARD_NUM'] > $cardnum) {
					$this->wrong('当前发货方库存数量不足分配或没有对应卡号的卡片!');
				}
				$where = "CARD_NO between '".$outcard['CARD_BEGIN']."' and '".$outcard['CARD_END']."' and CARD_STATUS='1' and ".$where1;
				//分配给创业合伙人, 更新VIPCARD
				$vip_res = D($this->GVipcard)->updateVipcard($where, $vipcard_data);

				/*for ($i=$outcard['CARD_BEGIN']; $i<=$outcard['CARD_END']; $i++) { 
					$where = "CARD_NO = '".$i."' and CARD_STATUS=1 and ".$where1;
					//分配给创业合伙人, 更新VIPCARD
					$vip_res = D($this->GVipcard)->updateVipcard($where, $vipcard_data);
				}*/
				//添加流转记录入库
				$res = D($this->GOutcard)->addOutcard($outcard_data);
				if ($res['state']!=0) {
					$this->wrong('流转失败!');
				}
			}else{
				//判断卡号和分配数量是否合法
				$cardno_str = "'".implode("','", $outcard['CARD_NO'])."'";
				$cardnum = D($this->GVipcard)->countVipcard("CARD_NO in (".$cardno_str.") and CARD_STATUS=1 and ".$where1);
				//判断数量
				if ($outcard['CARD_NUM'] > $cardnum) {
					$this->wrong('当前发货方库存数量不足分配或没有对应卡号的卡片!');
				}
				foreach ($outcard['CARD_NO'] as $val) {
					$where = "CARD_NO = '".$val."' and CARD_STATUS=1 and ".$where1;
					//分配给创业合伙人, 更新VIPCARD
					$vip_res = D($this->GVipcard)->updateVipcard($where, $vipcard_data);
					//添加流转记录入库
					$outcard_data['CARD_BEGIN'] = $val;
					$outcard_data['CARD_END'] 	= $val;
					$res = D($this->GOutcard)->addOutcard($outcard_data);
					if ($res['state']!=0) {
						$this->wrong('流转失败!');
					}
				}
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign('home',$home);
		$this->assign('max_batch',$maxnum+1);
		//$this->assign('b_data',$b_data);
		$this->assign('cardnum',$cardnum);
		$this->display();		
	}
	/*
	* 卡片分配 详情
	**/
	public function outcard_show() {
		$cardout_id = I('id');
		if (!$cardout_id) {
			$this->wrong('缺少参数！');
		}
		$where = 'CARDOUT_ID = '.$cardout_id;
		//获取制卡记录信息
		$res = D($this->GOutcard)->getOutcardlist($where);
		if (count($res) == 1) {
			$this->assign('cardno_type',0);
		}else{
			$this->assign('cardno_type',1);
		}

		//获取发卡公司数据
		$where = 'BRANCH_MAP_ID = '.$res[0]['BRANCH_MAP_ID'];
		$b_data = D($this->MBranch)->findBranch($where, $field='BRANCH_NAME,ADDRESS,MANAGER,MOBILE');
		//获取制卡记录信息
		$cardnum = D($this->GVipcard)->countVipcard($where." and CARD_STATUS=1");
		$this->assign('outcard_data',$res);
		$this->assign('b_data',$b_data);
		$this->display();		
	}
	/*
	* 卡片分配 Excel导出
	**/
	public function outcard_export() {
		$post  = array(
			'BRANCH_MAP_ID_OUT'		=>	I('fabid'),
			'PARTNER_MAP_ID_OUT'	=>	I('fapid'),
			'BRANCH_MAP_ID_IN'		=>	I('shoubid'),
			'PARTNER_MAP_ID_IN'		=>	I('shoupid'),
			'START_DATE'			=>	I('START_DATE'),
			'END_DATE'				=>	I('END_DATE'),
			'CARDOUT_BATCH_ID'		=>	I('CARDOUT_BATCH_ID')
		);
		$where  = "OUT_FLAG = '0'";
		//发货方
		if($post['BRANCH_MAP_ID_OUT'] != '') {
			$where .= " and BRANCH_MAP_ID_OUT = '".$post['BRANCH_MAP_ID_OUT']."'";
			$post['fabid'] = $post['BRANCH_MAP_ID_OUT'];
		}
		if($post['PARTNER_MAP_ID_OUT'] != '') {
			$where .= " and PARTNER_MAP_ID_OUT = '".$post['PARTNER_MAP_ID_OUT']."'";
			$post['fapid'] = $post['PARTNER_MAP_ID_OUT'];
		}
		//收货方
		if($post['BRANCH_MAP_ID_IN'] != '' && $post['BRANCH_MAP_ID_IN'] != $post['PARTNER_MAP_ID_OUT']) {
			$where .= " and BRANCH_MAP_ID_IN = '".$post['BRANCH_MAP_ID_IN']."'";
			$post['shoubid'] = $post['BRANCH_MAP_ID_IN'];
		}
		if($post['PARTNER_MAP_ID_IN'] != '') {
			$where .= " and PARTNER_MAP_ID_IN = '".$post['PARTNER_MAP_ID_IN']."'";
			$post['shoupid'] = $post['PARTNER_MAP_ID_IN'];
		}
		//日期
		if($post['START_DATE'] != '') {
			$where .= " and OUT_DATE >= '".date('Ymd',strtotime($post['START_DATE']))."'";
		}
		if($post['END_DATE'] != '') {
			$where .= " and OUT_DATE <= '".date('Ymd',strtotime($post['END_DATE']))."'";
		}
		//批次号
		if($post['CARDOUT_BATCH_ID']) {
			$where .= " and CARDOUT_BATCH_ID = '".$post['CARDOUT_BATCH_ID']."'";
		}
		//计算
		$count = D($this->GOutcard)->countOutcard($where);
		$numPort = floor($count/C('PAGE_COUNT_EXPORT'));
		$urlPort = __ACTION__.'?submit=ajax&'.http_build_query($post);
		$strPort = '';
		if($count > 0){
			for($i=0; $i<=$numPort; $i++){
				$strPort .= '<p><a href="'.$urlPort.'&p='.($i).'"><button class="ch-btn-skin ch-btn-small ch-icon-copy">文件('.($i+1).')</button></a></p>';
			}
		}else{
			$strPort .= '<p>暂无数据可下载~</p>';
		}
		$this->assign ( 'strPort', 	$strPort );
		
		//导出
		$submit = I('submit');
		$p 		= I('p');
		if($submit == 'ajax'){
			$bRow = $p * C('PAGE_COUNT_EXPORT');
			$eRow = C('PAGE_COUNT_EXPORT');
			$list  = D($this->GOutcard)->getOutcardlist($where, '*', $bRow.','.$eRow);
			//导出操作
			$xlsname = '积分宝制卡数据';
			$xlscell = array(
				array('CARDOUT_BATCH_ID',	'分配批次'),
				array('OUT_NAME',			'发货方'),
				array('IN_NAME',			'收货方'),
				array('CARD_NUM',			'数量'),
				array('CARD_BEGIN',			'起始卡号'),
				array('CARD_END',			'结束卡号'),
				array('CREATE_USERNAME',	'分配人'),
				array('OUT_DATE',			'分配日期')
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'CARDOUT_BATCH_ID'	=>	$val['CARDOUT_BATCH_ID'],
					'OUT_NAME'			=>	get_level_name($val['PARTNER_MAP_ID_OUT'], $val['BRANCH_MAP_ID_OUT']),
					'IN_NAME'			=>	get_level_name($val['PARTNER_MAP_ID_IN'], $val['BRANCH_MAP_ID_IN']),
					'CARD_NUM'			=>	$val['CARD_NUM'],
					'CARD_BEGIN'		=>	$val['CARD_BEGIN']."\t",
					'CARD_END'			=>	$val['CARD_END']."\t",
					'CREATE_USERNAME'	=>	$val['CREATE_USERNAME'],
					'OUT_DATE'			=>	$val['OUT_DATE']."\t"
				);	
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		$this->display('Public/export');
	}
	
	
	
	/*
	* 卡片回收
	**/
	public function reccard() {
		$post = I('post');
		if($post['submit'] == "reccard"){
			$where  = "OUT_FLAG = '1'";
			$falv   = filter_data('falv');		//列表查询
			$shoulv = filter_data('shoulv');	//列表查询
			//发货方
			if($falv['bid'] != '') {
				$where .= " and BRANCH_MAP_ID_OUT = '".$falv['bid']."'";
				$post['fabid'] = $falv['bid'];
			}
			if($falv['pid'] != '' && $falv['pid'] != $shoulv['pid']) {
				$where .= " and PARTNER_MAP_ID_OUT = '".$falv['pid']."'";
				$post['fapid'] = $falv['pid'];
			}
			//收货方
			if($shoulv['bid'] != '') {
				$where .= " and BRANCH_MAP_ID_IN = '".$shoulv['bid']."'";
				$post['shoubid'] = $shoulv['bid'];
			}
			if($shoulv['pid'] != '') {
				$where .= " and PARTNER_MAP_ID_IN = '".$shoulv['pid']."'";
				$post['shoupid'] = $shoulv['pid'];
			}
			//日期
			if($post['START_DATE'] != '') {
				$where .= " and OUT_DATE >= '".date('Ymd',strtotime($post['START_DATE']))."'";
			}
			if($post['END_DATE'] != '') {
				$where .= " and OUT_DATE <= '".date('Ymd',strtotime($post['END_DATE']))."'";
			}
			//批次号
			if($post['CARD_BATCH']) {
				$where .= " and CARD_BATCH = '".$post['CARD_BATCH']."'";
			}
			//分页
			$count = D($this->GOutcard)->countOutcard($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->GOutcard)->getOutcardlist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}else{
			$pdata = array(
				'START_DATE' => date('Y-m-d',strtotime('-1 month')),
				'END_DATE'   => date('Y-m-d')
			);
			$this->assign ( 'postdata', 	$pdata);
		}
		$home = session('HOME');
		$this->assign ( 'home', $home);
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display();
	}

	/*
	* 卡片回收 回收
	**/
	public function reccard_add() {
		$outcard = I('outcard');
		$home = session('HOME');
		if (empty($home['PARTNER_MAP_ID'])) {
			//获取当分公司数据
			$where1 = "BRANCH_MAP_ID = '".$home['BRANCH_MAP_ID']."'";
			$b_data = D($this->MBranch)->findBranch($where1, 'BRANCH_NAME,ADDRESS,MANAGER,MOBILE');
			$b_data['DELIVER_NAME'] = $b_data['BRANCH_NAME'];
			$maxnum = D($this->GOutcard)->findBatchmax($where);
		}else{
			//获取当前合作伙伴公司数据
			$where1 = "a.BRANCH_MAP_ID = '".$home['BRANCH_MAP_ID']."' and a.PARTNER_MAP_ID = '".$home['PARTNER_MAP_ID']."'";
			$b_data = D($this->MPartner)->findPartner($where1, 'a.PARTNER_NAME,a.ADDRESS,a.MANAGER,a.MOBILE');
			$b_data['DELIVER_NAME'] = $b_data['PARTNER_NAME'];
			$maxnum = D($this->GOutcard)->findBatchmax($where);
		}
		
		if($outcard['submit'] == "reccard_add") {
			//验证数据
			$lvdata = get_level_val('plv');
			if($home['BRANCH_MAP_ID'] != 100000){	//如果是总部操作, 那不做限制
				if (empty($lvdata['pid'])) {
					$this->wrong('请选择到合作伙伴');
				}
				//查找对应的用户数据
				$where2 = "PARTNER_MAP_ID = '".$lvdata['pid']."'";
				$user_data = D($this->MUser)->findUser($where2,'USER_ID,USER_NAME');
				if (!$user_data) {
					$this->wrong('未查到发货方的用户数据, 请给发货方分配一个操作员');
				}
			}else{
				//查找对应的用户数据
				$where2 = "BRANCH_MAP_ID = '".$lvdata['bid']."' and PARTNER_MAP_ID = 0";
				$user_data = D($this->MUser)->findUser($where2,'USER_ID,USER_NAME');
				if (!$user_data) {
					$this->wrong('未查到发货方的用户数据, 请给发货方分配一个操作员');
				}
			}

			if (empty($outcard['CARD_NUM'])) {
				$this->wrong('请添写必填项');
			}
			//组装记录数据
			$outcard_data = array(
				'CARD_BATCH' 		=> 0, 
				'BRANCH_MAP_ID_IN' 	=> $home['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID_IN'	=> $home['PARTNER_MAP_ID'], 
				'BRANCH_MAP_ID_OUT'	=> $lvdata['bid'], 
				'PARTNER_MAP_ID_OUT'=> $lvdata['pid'] ? $lvdata['pid'] : 0, 
				'OUT_FLAG'			=> 1, 
				'CARDOUT_BATCH_ID' 	=> $maxnum+1,
				'CARD_BIN' 			=> 0, 
				'CARD_NAME' 		=> 0, 
				'CARD_NUM' 			=> $outcard['CARD_NUM'], 
				'CARD_BEGIN' 		=> $outcard['CARD_BEGIN'], 
				'CARD_END' 			=> $outcard['CARD_END'], 
				'OUT_DATE' 			=> date('Ymd'), 
				'CREATE_USERID' 	=> $home['USER_ID'], 
				'CREATE_USERNAME'	=> $home['USER_NAME'], 
				'RECV_USERID' 		=> $user_data['USER_ID']
			);
			
			//组装修改数据
			$vipcard_data = array(
				'BRANCH_MAP_ID'  => $home['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID' => $home['PARTNER_MAP_ID'],
				'UPDATE_TIME' 	 => date('YmdHis'),
			);
			//判断用户选择的是连续卡号还是非连续的
			$cardno_type = I('cardno_type');
			if($cardno_type == 0) {
				if (($outcard['CARD_END'] - $outcard['CARD_BEGIN']) < 0 || ($outcard['CARD_END'] - $outcard['CARD_BEGIN']) > 10000) {
					$this->wrong('开始卡号不能大于结束卡号并且要小于10000张');
				}
				//判断卡号和分配数量是否合法
				$cardnum = D($this->GVipcard)->countVipcard("(CARD_NO between '".$outcard['CARD_BEGIN']."' and '".$outcard['CARD_END']."') and CARD_STATUS=1 and ".$where2);
				//判断数量
				if ($outcard['CARD_NUM'] > $cardnum) {
					$this->wrong('当前发货方库存数量不足分配或没有对应卡号的卡片!');
				}

				$where = "CARD_NO between '".$outcard['CARD_BEGIN']."' and '".$outcard['CARD_END']."' and CARD_STATUS='1' and ".$where2;
				//分配给创业合伙人, 更新VIPCARD
				$vip_res = D($this->GVipcard)->updateVipcard($where, $vipcard_data);

				/*for ($i=$outcard['CARD_BEGIN']; $i<=$outcard['CARD_END']; $i++) { 
					$where = "CARD_NO = '".$i."' and CARD_STATUS=1 and ".$where2;
					//分配给创业合伙人, 更新VIPCARD
					$vip_res = D($this->GVipcard)->updateVipcard($where, $vipcard_data);
				}*/
				//添加流转记录入库
				$res = D($this->GOutcard)->addOutcard($outcard_data);
				if ($res['state']!=0) {
					$this->wrong('流转失败!');
				}
			}else{
				//判断卡号和分配数量是否合法
				$cardno_str = "'".implode("','", $outcard['CARD_NO'])."'";
				$cardnum = D($this->GVipcard)->countVipcard("CARD_NO in (".$cardno_str.") and CARD_STATUS=1 and ".$where2);
				//判断数量
				if ($outcard['CARD_NUM'] > $cardnum) {
					$this->wrong('当前发货方库存数量不足分配或没有对应卡号的卡片!');
				}
				foreach ($outcard['CARD_NO'] as $val) {
					$where = "CARD_NO = '".$val."' and CARD_STATUS=1 and ".$where2;
					//分配给创业合伙人, 更新VIPCARD
					$vip_res = D($this->GVipcard)->updateVipcard($where, $vipcard_data);
					//添加流转记录入库
					$outcard_data['CARD_BEGIN'] = $val;
					$outcard_data['CARD_END'] 	= $val;
					$res = D($this->GOutcard)->addOutcard($outcard_data);
					if ($res['state']!=0) {
						$this->wrong('流转失败!');
					}
				}
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign('home',$home);
		$this->assign('max_batch',$maxnum+1);
		$this->assign('b_data',$b_data);
		$this->assign('cardnum',$cardnum);
		$this->display();
	}
	/*
	* 卡片回收 详情
	**/
	public function reccard_show() {
		$cardout_id = I('id');
		if (!$cardout_id) {
			$this->wrong('缺少参数！');
		}
		$where = 'CARDOUT_ID = '.$cardout_id;
		//获取制卡记录信息
		$res = D($this->GOutcard)->getOutcardlist($where);
		if (count($res) == 1) {
			$this->assign('cardno_type',0);
		}else{
			$this->assign('cardno_type',1);
		}

		//获取发卡公司数据
		//$where = 'b.BRANCH_MAP_ID_OUT = '.$res[0]['BRANCH_MAP_ID_OUT'];
		//$b_data = D($this->MPartner)->findPartner($where, $field='BRANCH_NAME,ADDRESS,MANAGER,MOBILE');
		//获取制卡记录信息
		$cardnum = D($this->GVipcard)->countVipcard($where." and CARD_STATUS=1");
		$this->assign('outcard_data',$res);
		$this->assign('b_data',$b_data);
		$this->display();	
	}
	/*
	* 卡片回收 Excel导出
	**/
	public function reccard_export() {
		$post  = array(
			'BRANCH_MAP_ID_OUT'		=>	I('fabid'),
			'PARTNER_MAP_ID_OUT'	=>	I('fapid'),
			'BRANCH_MAP_ID_IN'		=>	I('shoubid'),
			'PARTNER_MAP_ID_IN'		=>	I('shoupid'),
			'START_DATE'			=>	I('START_DATE'),
			'END_DATE'				=>	I('END_DATE'),
			'CARDOUT_BATCH_ID'		=>	I('CARDOUT_BATCH_ID')
		);
		$where  = "OUT_FLAG = '1'";
		//发货方
		if($post['BRANCH_MAP_ID_OUT'] != '') {
			$where .= " and BRANCH_MAP_ID_OUT = '".$post['BRANCH_MAP_ID_OUT']."'";
			$post['fabid'] = $post['BRANCH_MAP_ID_OUT'];
		}
		if($post['PARTNER_MAP_ID_OUT'] != '' && $post['PARTNER_MAP_ID_OUT'] != $post['PARTNER_MAP_ID_IN']) {
			$where .= " and PARTNER_MAP_ID_OUT = '".$post['PARTNER_MAP_ID_OUT']."'";
			$post['fapid'] = $post['PARTNER_MAP_ID_OUT'];
		}
		//收货方
		if($post['BRANCH_MAP_ID_IN'] != '') {
			$where .= " and BRANCH_MAP_ID_IN = '".$post['BRANCH_MAP_ID_IN']."'";
			$post['shoubid'] = $post['BRANCH_MAP_ID_IN'];
		}
		if($post['PARTNER_MAP_ID_IN'] != '') {
			$where .= " and PARTNER_MAP_ID_IN = '".$post['PARTNER_MAP_ID_IN']."'";
			$post['shoupid'] = $post['PARTNER_MAP_ID_IN'];
		}
		//日期
		if($post['START_DATE'] != '') {
			$where .= " and OUT_DATE >= '".date('Ymd',strtotime($post['START_DATE']))."'";
		}
		if($post['END_DATE'] != '') {
			$where .= " and OUT_DATE <= '".date('Ymd',strtotime($post['END_DATE']))."'";
		}
		//批次号
		if($post['CARDOUT_BATCH_ID']) {
			$where .= " and CARDOUT_BATCH_ID = '".$post['CARDOUT_BATCH_ID']."'";
		}
		//计算
		$count = D($this->GOutcard)->countOutcard($where);
		$numPort = floor($count/C('PAGE_COUNT_EXPORT'));
		$urlPort = __ACTION__.'?submit=ajax&'.http_build_query($post);
		$strPort = '';
		if($count > 0){
			for($i=0; $i<=$numPort; $i++){
				$strPort .= '<p><a href="'.$urlPort.'&p='.($i).'"><button class="ch-btn-skin ch-btn-small ch-icon-copy">文件('.($i+1).')</button></a></p>';
			}
		}else{
			$strPort .= '<p>暂无数据可下载~</p>';
		}
		$this->assign ( 'strPort', 	$strPort );
		
		//导出
		$submit = I('submit');
		$p 		= I('p');
		if($submit == 'ajax'){
			$bRow = $p * C('PAGE_COUNT_EXPORT');
			$eRow = C('PAGE_COUNT_EXPORT');
			$list  = D($this->GOutcard)->getOutcardlist($where, '*', $bRow.','.$eRow);
			$this->assign ( 'postdata', 	$post);
			//导出操作
			$xlsname = '积分宝制卡数据';
			$xlscell = array(
				array('CARD_BATCH',		'制卡批次'),
				array('OUT_NAME',		'发货方'),
				array('IN_NAME',		'收货方'),
				array('CARD_NUM',		'数量'),
				array('CARD_BEGIN',		'起始卡号'),
				array('CARD_END',		'结束卡号'),
				array('CREATE_USERNAME','分配人'),
				array('OUT_DATE',		'分配日期')
			);		
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'CARD_BATCH'	=>	$val['CARD_BATCH'],
					'OUT_NAME'		=>	get_level_name($val['PARTNER_MAP_ID_OUT'], $val['BRANCH_MAP_ID_OUT']),
					'IN_NAME'		=>	get_level_name($val['PARTNER_MAP_ID_IN'], $val['BRANCH_MAP_ID_IN']),
					'CARD_NUM'		=>	$val['CARD_NUM'],
					'CARD_BEGIN'	=>	$val['CARD_BEGIN']."\t",
					'CARD_END'		=>	$val['CARD_END']."\t",
					'OUT_STATUS'	=>	C('OUT_STATUS')[$val['OUT_STATUS']],
					'OUT_DATE'		=>	$val['OUT_DATE']."\t"
				);	
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		$this->display('Public/export');
	}
	
	
	
	/*
	* 卡片信息
	**/
	public function carddata() {
		$post = I('post');		
		//===优化统计===
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'bid'				=>	I('bid'),
				'pid'				=>	I('pid'),
				'CARD_STATUS'		=>	I('CARD_STATUS'),
				'CARD_P_MAP_ID'		=>	I('CARD_P_MAP_ID'),
				'VIP_IDNO'			=>	I('VIP_IDNO'),
				'VIP_MOBILE'		=>	I('VIP_MOBILE'),
				'CARD_NO'			=>	I('CARD_NO'),
			);
			$ajax_soplv = array(
				'bid'				=>	I('bid'),
				'pid'				=>	I('pid'),			
			);
		}
		//===结束===
		if($post['submit'] == "carddata"){
			$where = "1=1";
			//===优化统计===
			$faplv = $ajax == 'loading' ? $ajax_soplv : filter_data('faplv');	//列表查询
			//===结束=======
			//分公司
			if($faplv['bid'] != '') {
				$where .= " and BRANCH_MAP_ID = '".$faplv['bid']."'";
				$post['bid'] = $faplv['bid'];
			}
			//合作伙伴公司
			if($faplv['pid'] != '') {
				$pids = get_plv_childs($faplv['pid'],1);
				$where .= " and PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $faplv['pid'];
			}
			//卡片状态
			if($post['CARD_P_MAP_ID'] != '') {
				$where .= " and CARD_P_MAP_ID = '".$post['CARD_P_MAP_ID']."'";
			}
			//卡套餐
			if($post['CARD_STATUS'] != '') {
				$where .= " and CARD_STATUS = '".$post['CARD_STATUS']."'";
			}
			//会员卡号
			if($post['CARD_NO']) {
				$where .= " and CARD_NO = '".$post['CARD_NO']."'";
			}
			//===优化统计===
			if($ajax == 'loading'){
				$count	 = D($this->GVipcard)->countVipcard($where);
				$resdata = array(
					'count'	=>	$count,
				);
				$this->ajaxReturn($resdata);
			}
			//===结束=======		
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->GVipcard)->getVipcardlist($where, 'CARD_NO,BRANCH_MAP_ID,PARTNER_MAP_ID,CARD_STATUS,CARD_CHECK,CARD_P_MAP_ID', $fiRow.','.$liRow);
			foreach($list as $key=>$val){
				$vipdata = array();
				if($val['CARD_NO']){
					$vipdata = D($this->GVip)->findNewsVip("CARD_NO = '".$val['CARD_NO']."'", 'VIP_NAME,VIP_MOBILE,VIP_CITY,VIP_SEX,VIP_BIRTHDAY,CREATE_TIME');
				}
				$list[$key]['VIP_NAME'] 	= $vipdata['VIP_NAME']		? $vipdata['VIP_NAME']	: '';
				$list[$key]['VIP_MOBILE'] 	= $vipdata['VIP_MOBILE']	? $vipdata['VIP_MOBILE']: '';
				$list[$key]['VIP_CITY'] 	= $vipdata['VIP_CITY']		? $vipdata['VIP_CITY']	: '';
				$list[$key]['VIP_SEX'] 		= $vipdata['VIP_SEX']		? $vipdata['VIP_SEX']	: '';
				$list[$key]['VIP_BIRTHDAY'] = $vipdata['VIP_BIRTHDAY']	? date('Y-m-d', strtotime($vipdata['VIP_BIRTHDAY']))	: '';
				$list[$key]['CREATE_TIME'] 	= $vipdata['CREATE_TIME']	? date('Y-m-d', strtotime($vipdata['CREATE_TIME']))	: '';
			}
			
			//分页参数
			$this->assign ( 'totalCount', 	C('PAGE_COUNT')==count($list) ? 1 : 0 );
	       	$this->assign ( 'numPerPage', 	'' );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
						
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
			
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		$this->assign ('cardstatus',C('CARD_STATUS'));	//卡状态
		$this->assign ('cproduct',C('CARD_P_MAP_ID'));	//卡套餐
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display();
	}

	/*
	* 换卡卡片
	**/
	public function chanagecard() {
		$post = I('post');
		if($post['submit'] == "chanagecard"){
			$where = "1=1";
			$faplv = filter_data('faplv');//列表查询
			//分公司
			if($faplv['bid'] != '') {
				$where .= " and vc.BRANCH_MAP_ID = '".$faplv['bid']."'";
				$post['bid'] = $faplv['bid'];
			}
			//合作伙伴公司
			if($faplv['pid'] != '') {
				$pids = get_plv_childs($faplv['pid'],1);
				//$where .= " and vc.PARTNER_MAP_ID in(".$pids.")";
				$where .= " and vc.PARTNER_MAP_ID = '".$faplv['pid']."'";
				$post['pid'] = $faplv['pid'];
			}
			//卡片状态
			if($post['CARD_P_MAP_ID'] != '') {
				$where .= " and vc.CARD_P_MAP_ID = '".$post['CARD_P_MAP_ID']."'";
			}
			//卡套餐
			if($post['CARD_STATUS']!='') {
				$where .= " and vc.CARD_STATUS = '".$post['CARD_STATUS']."'";
			}
			//会员身份证
			if($post['VIP_IDNO']) {
				$where .= " and vp.VIP_IDNO = '".$post['VIP_IDNO']."'";
			}
			//会员手机号
			if($post['VIP_MOBILE']) {
				$where .= " and vp.VIP_MOBILE = '".$post['VIP_MOBILE']."'";
			}
			//会员手机号
			if($post['CARD_NO']) {
				$where .= " and vc.CARD_NO = '".$post['CARD_NO']."'";
			}
			//分页
			$count = D($this->GVipcard)->countVipcardmore_tmp($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->GVipcard)->getVipcardmore_tmp($where, 'vc.*,vp.VIP_NAME,vp.VIP_MOBILE,vp.VIP_CITY,vp.VIP_SEX,vp.VIP_BIRTHDAY,vp.CREATE_TIME', $p->firstRow.','.$p->listRows);

			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		//获取分公司下拉列表
		$branchsel = D($this->MBranch)->getBranch_select();
		$this->assign ('branchsel', $branchsel);		//分公司
		$this->assign ('cardstatus',C('CARD_STATUS'));	//卡状态
		$this->assign ('cproduct',C('CARD_P_MAP_ID'));	//卡套餐
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display();
	}
	
	/*
	* 换卡卡片 详情
	**/
	public function changecard_show() {
		$card_no = I('id');
		if (!$card_no) {
			$this->wrong('缺少参数！');
		}
		$where = 'vc.CARD_NO = "'.$card_no.'"';
		$vip_res = D($this->GVipcard)->findVipcardmore_tmp($where,'vp.*,vc.CARD_P_MAP_ID,vc.CARD_TRACK2');
		//获得会员卡数据
		if ($vip_res['VIP_ID']) {
			//获取卡费
			$cardmoney = D($this->MCproduct)->findCproduct_one('CARD_P_MAP_ID = '.$vip_res['CARD_P_MAP_ID']);
			//根据会员id获取账户信息
			$acct_no = setStrzero($vip_res['VIP_ID'],'9');
			$lap_res = D($this->GLap)->findLap('ACCT_NO = '.$acct_no);
			$this->assign ('cardmoney', $cardmoney['USER_OPENFEE']);	//会员卡费
			$this->assign ('vip_data', $vip_res);						//会员卡信息
			$this->assign ('lap_data', $lap_res);						//会员账户信息
			$this->display('changecard_show');
		}else{
			$card_res = D($this->GVipcard)->findVipcard('CARD_NO = "'.$card_no.'"');
			//获取卡费
			$cardmoney = D($this->MCproduct)->findCproduct_one('CARD_P_MAP_ID = '.$card_res['CARD_P_MAP_ID']);
			$this->assign ('cardmoney', $cardmoney['USER_OPENFEE']);	//会员卡费
			$this->assign ('card_res', $card_res);						//卡信息
			$this->display('changecard_show1');
		}
	}

	/*
	* 卡片回收 详情
	**/
	public function carddata_show() {
		$card_no = I('id');
		if (!$card_no) {
			$this->wrong('缺少参数！');
		}
		$where = 'vc.CARD_NO = "'.$card_no.'"';
		$vip_res = D($this->GVipcard)->findVipcardmore($where,'vp.*,vc.CARD_P_MAP_ID,vc.CARD_TRACK2');
		//获得会员卡数据
		if ($vip_res['VIP_ID']) {
			//获取卡费
			$cardmoney = D($this->MCproduct)->findCproduct_one('CARD_P_MAP_ID = '.$vip_res['CARD_P_MAP_ID']);
			//根据会员id获取账户信息
			$acct_no = setStrzero($vip_res['VIP_ID'],'9');
			$lap_res = D($this->GLap)->findLap('ACCT_NO = '.$acct_no);
			$this->assign ('cardmoney', $cardmoney['USER_OPENFEE']);	//会员卡费
			$this->assign ('vip_data', $vip_res);						//会员卡信息
			$this->assign ('lap_data', $lap_res);						//会员账户信息
			$this->display('carddata_show');
		}else{
			$card_res = D($this->GVipcard)->findVipcard('CARD_NO = "'.$card_no.'"');
			//获取卡费
			$cardmoney = D($this->MCproduct)->findCproduct_one('CARD_P_MAP_ID = '.$card_res['CARD_P_MAP_ID']);
			$this->assign ('cardmoney', $cardmoney['USER_OPENFEE']);	//会员卡费
			$this->assign ('card_res', $card_res);						//卡信息
			$this->display('carddata_show1');
		}
	}
	/*
	* 修改卡片信息
	**/
	public function carddata_edit() {
		$makecard  = I('makecard');	
		$home = session('HOME');
		if($makecard['submit'] == "carddata_edit") {
			$cardno_type = I('cardno_type');
			//数据验证
			if(empty($makecard['CARD_NUM']))
			{
				$this->wrong("请填写必填项！");
			}
			if ($cardno_type != 0) {
				if (count($makecard['CARD_NO']) != $makecard['CARD_NUM']) {
					$this->wrong("卡数量与卡片序号不符！");
				}
			}else{
				if (($makecard['CARD_END'] - $makecard['CARD_BEGIN']+1) != $makecard['CARD_NUM']) {
					$this->wrong("卡数量与卡片序号不符！");
				}
			}
			if ($makecard['CARD_NUM'] > 10000) {
				$this->wrong("一批最多修改卡数量为10000张!");
			}

			//判断卡号是否连续
			if ($cardno_type !=0) {
				//非连续卡号
				for ($i=0; $i < count($makecard['CARD_NO']); $i++) {
					$card_no = $makecard['CARD_NO'][$i];
					$res = D($this->GVipcard)->findVipcard('CARD_NO = "'.$card_no.'"','CARD_P_MAP_ID,CARD_STATUS');
					if ($res['CARD_STATUS'] == 1) {
						D($this->GVipcard)->updateVipcard('CARD_NO = "'.$card_no.'"', array('CARD_P_MAP_ID' => $makecard['CARD_P_MAP_ID']));
					}
				}
			}else{
				$where = "CARD_NO between '".$makecard['CARD_BEGIN']."' and '".$makecard['CARD_END']."' and CARD_STATUS='1'";
				//分配给创业合伙人, 更新VIPCARD
				$vip_res = D($this->GVipcard)->updateVipcard($where, array('CARD_P_MAP_ID' => $makecard['CARD_P_MAP_ID']));

				//向VIPCARD表中添加数据
				/*for ($i=0; $i < $makecard['CARD_NUM']; $i++) {
					//组装数据
					$card_no = $makecard['CARD_BEGIN']+$i;
					$res = D($this->GVipcard)->findVipcard('CARD_NO = "'.$card_no.'"','CARD_P_MAP_ID,CARD_STATUS');
					if ($res['CARD_STATUS'] == 1) {
						D($this->GVipcard)->updateVipcard('CARD_NO = "'.$card_no.'"', array('CARD_P_MAP_ID' => $makecard['CARD_P_MAP_ID']));
					}
				}*/
			}
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//获取卡套餐列表
		$cardsel = D($this->MCproduct)->getCproductlist_one('CARD_P_MAP_ID = 2 or CARD_P_MAP_ID = 3','CARD_P_MAP_ID,CARD_NAME');
		$this->assign('cardsel',$cardsel);
		$this->display('carddata_custom2');
	}
	/*
	* 卡片信息 删除
	**/
	public function carddata_del() {
		$ids = I('CARD_NO_STR');
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('CARD_NO'=> array('in', implode(',', $ids)));
		}else{
			$where = array('CARD_NO'=> array('eq', $ids));
		}
		//判断操作项状态
		$cardstatus = D($this->GVipcard)->getVipcardlist($where,'CARD_STATUS');
		foreach ($cardstatus as $key => $value) {
			if ($value['CARD_STATUS'] != 1) {
				$this->wrong('此操作仅限卡片为库存状态执行');
			}
		}
		$res = D($this->GVipcard)->delVipcard($where);
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 卡片信息 Excel导出
	**/
	public function carddata_export() {
		$post  = array(
			'bid'					=>	I('bid'),
			'pid'					=>	I('pid'),
			'CARD_P_MAP_ID'			=>	I('CARD_P_MAP_ID'),
			'CARD_STATUS'			=>	I('CARD_STATUS'),
			'VIP_IDNO'				=>	I('VIP_IDNO'),
			'VIP_MOBILE'			=>	I('VIP_MOBILE'),
			'CARD_NO'				=>	I('CARD_NO'),
		);
		$where = "1=1";
		//分公司
		if($post['bid'] != '') {
			$where .= " and BRANCH_MAP_ID = '".$post['bid']."'";
		}
		//合作伙伴公司
		if($post['pid'] != '') {
			$pids = get_plv_childs($post['pid'],1);
			$where .= " and PARTNER_MAP_ID in(".$pids.")";
		}
		//卡片状态
		if($post['CARD_P_MAP_ID'] != '') {
			$where .= " and CARD_P_MAP_ID = '".$post['CARD_P_MAP_ID']."'";
		}
		//卡套餐
		if($post['CARD_STATUS'] != '') {
			$where .= " and CARD_STATUS = '".$post['CARD_STATUS']."'";
		}
		//会员卡号
		if($post['CARD_NO']) {
			$where .= " and CARD_NO = '".$post['CARD_NO']."'";
		}
		//计算		
		$count = D($this->GVipcard)->countVipcard($where);
		
		//导出
		$submit = I('submit');
		$p 		= I('p');
		if($submit == 'ajax'){
			//====新导出====
			set_time_limit(0);
			error_reporting(0);
			header("Content-type:application/vnd.ms-excel");
			header("Content-Disposition:filename=".iconv("UTF-8", "GBK", "卡片信息_".($p+1)).".csv");
						
			$fput 	 = fopen('php://output', 'a');
			$headstr = array('卡号','归属','卡片状态','卡片校验码','卡套餐','会员','会员手机号','所在城市','性别','生日','创建日期');	//表头
			foreach($headstr as $key=>$val) {
				$headstr[$key] = iconv('utf-8', 'gbk', $val); 		//将中文标题转换编码，否则乱码  
			}
			fputcsv($fput, $headstr); 								//将标题名称通过fputcsv写到文件句柄   
			
			$limit = $p*C('NEWS_COUNT_EXPORT').','.C('NEWS_COUNT_EXPORT');
			$list  = D($this->GVipcard)->getVipcardlist($where, '*', $limit);
			foreach($list as $key=>$val){
				$vipdata = array();
				if($val['CARD_NO']){
					$vipdata = D($this->GVip)->findNewsVip("CARD_NO = '".$val['CARD_NO']."'", 'VIP_NAME,VIP_MOBILE,VIP_CITY,VIP_SEX,VIP_BIRTHDAY,CREATE_TIME');
				}
				//组装导出数组
				$resdata = array(
					$val['CARD_NO']."\t",
					get_level_name($val['PARTNER_MAP_ID'], $val['BRANCH_MAP_ID']),
					C('CARD_STATUS')[$val['CARD_STATUS']],
					$val['CARD_CHECK']."\t",
					C('CARD_P_MAP_ID')[$val['CARD_P_MAP_ID']],
					($vipdata['VIP_NAME']		? $vipdata['VIP_NAME']				: ''),
					($vipdata['VIP_MOBILE']		? $vipdata['VIP_MOBILE']."\t"		: ''),
					($vipdata['VIP_CITY']		? getcity_name($vipdata['VIP_CITY']): ''),
					($vipdata['VIP_SEX']		? C('VIP_SEX')[$vipdata['VIP_SEX']]	: ''),
					($vipdata['VIP_BIRTHDAY']	? date('Y-m-d', strtotime($vipdata['VIP_BIRTHDAY']))."\t"		: ''),
					($vipdata['CREATE_TIME']	? date('Y-m-d H:i:s', strtotime($vipdata['CREATE_TIME']))."\t"	: ''),
				);
				$rows = array();  
				foreach($resdata as $item){  
					$rows[] = iconv('utf-8', 'GBK', $item);  
				}  
				fputcsv($fput, $rows);
			}          
			unset($list);  										//将已经写到csv中的数据存储变量销毁，释放内存占用				 
			ob_flush();  										//刷新缓冲区 
			flush(); 
			exit;
		}	
		if($count > 0){
			$numPort = floor($count/C('NEWS_COUNT_EXPORT'));
			$urlPort = __ACTION__.'?submit=ajax&'.http_build_query($post);
			$strPort = '';
			for($i=0; $i<=$numPort; $i++){
				$strPort .= '<p><a href="'.$urlPort.'&p='.($i).'"><button class="ch-btn-skin ch-btn-small ch-icon-copy">文件_'.($i+1).'</button></a></p>';
			}
		}else{
			$strPort = '<p>暂无数据可下载 ~</p>';
		}
		$this->assign ( 'strPort', 	$strPort );		
		$this->display('Public/export');
	}
	
	/*
	* 手动录入
	**/
	public function carddata_custom() {
		$makecard  = I('makecard');	
		$home = session('HOME');
		if($makecard['submit'] == "carddata_custom") {
			//制卡公司bid
			$makepid = get_level_val('makepid');		
			$makecard['BRANCH_MAP_ID'] = $makepid['bid'];
			$makecard['PARTNER_MAP_ID'] = $makepid['pid'];
			$cardno_type = I('cardno_type');

			//数据验证
			if(empty($makecard['CARD_NUM']) || empty($makecard['BRANCH_MAP_ID']))
			{
				$this->wrong("请填写必填项！");
			}
			if ($cardno_type != 0) {
				if (count($makecard['CARD_NO']) != $makecard['CARD_NUM']) {
					$this->wrong("制卡数量与卡片序号不符！");
				}
			}else{
				if (($makecard['CARD_END'] - $makecard['CARD_BEGIN']+1) != $makecard['CARD_NUM']) {
					$this->wrong("制卡数量与卡片序号不符！");
				}
			}
			if ($makecard['CARD_NUM'] > 10000) {
				$this->wrong("一批最多制卡数量由10000张!");
			}
			

			//判断卡号是否连续
			if ($cardno_type !=0) {
				$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
				$m->startTrans();//开启事务
				//非连续卡号
				for ($i=0; $i < count($makecard['CARD_NO']); $i++) {
					$card_no = $makecard['CARD_NO'][$i];
					//组装数据
					$makecard_data = array(
						'BRANCH_MAP_ID'		=>	$makecard['BRANCH_MAP_ID'],
						'ZONE_CODE'			=>	0,
						'CARD_BIN'			=>	0,
					  	'CARD_P_MAP_ID'		=>	$makecard['CARD_P_MAP_ID'],			//卡套餐ID 0虚拟卡1收费卡2预免卡
						'CARD_NAME'			=>	0,
						'CARD_NUM'			=>	$card_no,
						'CARD_BEGIN'		=>	$card_no,
						'CARD_END'			=>	$makecard['CARD_END'],
						'OUT_STATUS'		=>	0, 			//发卡过程(0已制卡,1在途中)
						'OUT_DATE'			=>	date('Ymd'),
						'CREATE_USERID'		=>	$home['USER_ID'],
						'CREATE_USERNAME'	=>	$home['USER_NAME']
					);
					$res = D($this->GMakecard)->addMakecard($makecard_data);
					if($res['state']!=0){
						$m->rollback();		//不成功，则回滚
						$this->wrong('添加失败！');
					}

					//组装数据
					$vipcard_data = array(
						'BRANCH_MAP_ID'		=>	$makecard_data['BRANCH_MAP_ID'],	//隶属分支
						'BRANCH_MAP_ID1'	=>	$makecard_data['BRANCH_MAP_ID'],	//隶属分支
						'PARTNER_MAP_ID'	=>	$makecard['PARTNER_MAP_ID'] ? $makecard['PARTNER_MAP_ID'] : 0,		//分配创业合伙人
						'PARTNER_MAP_ID1'	=>	$makecard['PARTNER_MAP_ID'] ? $makecard['PARTNER_MAP_ID'] : 0,		//隶属创业合伙人
						'VIP_ID'			=>	'2'.substr($card_no, 8),			//会员代码
						'CARD_NO'			=>	$card_no,							//卡号号码
						'CARD_BIN'			=>	0,									//卡套餐类
						'CARD_P_MAP_ID'		=>	$makecard['CARD_P_MAP_ID'],			//卡套餐ID
						'CARD_STATUS'		=>	1,									//卡片状态
						'CARD_EXP'			=>	'9912',								//卡有效期
						'CARD_TRACK2'		=>	$card_no.'='.getCardCheck($card_no),//卡二磁道
						'CARD_CHECK'		=>	$makecard['CARD_CHECK'] ? $makecard['CARD_CHECK'] : '000000',//卡校验值
						'CARD_BIRTHDAY'		=>	0,									//开户日期
						'CARD_BATCH'		=>	0,									//发卡批次
						'ACTIVE_TIME'		=>	date('YmdHis'),						//激活时间
						'UPDATE_TIME'		=>	date('YmdHis')						//修改时间
					);
					$vip_res = D($this->GVipcard)->addVipcard($vipcard_data);
					if ($vip_res['state']!=0){
						$m->rollback();		//不成功，则回滚
						$this->wrong('制卡失败!');
					}
				}
			}else{
				//组装数据
				$makecard_data = array(
					'BRANCH_MAP_ID'		=>	$makecard['BRANCH_MAP_ID'],
					'ZONE_CODE'			=>	0,
					'CARD_BIN'			=>	0,
				  	'CARD_P_MAP_ID'		=>	$makecard['CARD_P_MAP_ID'],			//卡套餐ID 0虚拟卡1收费卡2预免卡
					'CARD_NAME'			=>	0,
					'CARD_NUM'			=>	$makecard['CARD_NUM'],
					'CARD_BEGIN'		=>	$makecard['CARD_BEGIN'],
					'CARD_END'			=>	$makecard['CARD_END'],
					'OUT_STATUS'		=>	0, 			//发卡过程(0已制卡,1在途中)
					'OUT_DATE'			=>	date('Ymd'),
					'CREATE_USERID'		=>	$home['USER_ID'],
					'CREATE_USERNAME'	=>	$home['USER_NAME']
				);
				$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
				$m->startTrans();//开启事务
				$res = D($this->GMakecard)->addMakecard($makecard_data);
				if($res['state']!=0){
					$m->rollback();		//不成功，则回滚
					$this->wrong('添加失败！');
				}
				//向VIPCARD表中添加数据
				for ($i=0; $i < $makecard_data['CARD_NUM']; $i++) {
					//组装数据
					$card_no = $makecard_data['CARD_BEGIN']+$i;
					$vipcard_data = array(
						'BRANCH_MAP_ID'		=>	$makecard_data['BRANCH_MAP_ID'],	//隶属分支
						'BRANCH_MAP_ID1'	=>	$makecard_data['BRANCH_MAP_ID'],	//隶属分支
						'PARTNER_MAP_ID'	=>	$makecard['PARTNER_MAP_ID'] ? $makecard['PARTNER_MAP_ID'] : 0,		//分配创业合伙人
						'PARTNER_MAP_ID1'	=>	$makecard['PARTNER_MAP_ID'] ? $makecard['PARTNER_MAP_ID'] : 0,		//隶属创业合伙人
						'VIP_ID'			=>	'2'.substr($card_no, 8),			//会员代码
						'CARD_NO'			=>	$card_no,							//卡号号码
						'CARD_BIN'			=>	0,									//卡套餐类
						'CARD_P_MAP_ID'		=>	$makecard['CARD_P_MAP_ID'],			//卡套餐ID
						'CARD_STATUS'		=>	1,									//卡片状态
						'CARD_EXP'			=>	'9912',							//卡有效期
						'CARD_TRACK2'		=>	$card_no.'='.getCardCheck($card_no),//卡二磁道
						'CARD_CHECK'		=>	$makecard['CARD_CHECK'] ? $makecard['CARD_CHECK'] : '000000',//卡校验值
						'CARD_BIRTHDAY'		=>	0,									//开户日期
						'CARD_BATCH'		=>	0,									//发卡批次
						'ACTIVE_TIME'		=>	date('YmdHis'),						//激活时间
						'UPDATE_TIME'		=>	date('YmdHis')						//修改时间
					);
					$vip_res = D($this->GVipcard)->addVipcard($vipcard_data);
					if ($vip_res['state']!=0){
						$m->rollback();		//不成功，则回滚
						$this->wrong('制卡失败!');
					}
				}
			}
			$m->commit();	//全部成功则提交
			$this->right('操作成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//获取卡套餐列表
		$cardsel = D($this->MCproduct)->getCproductlist_one('CARD_P_MAP_ID = 2 or CARD_P_MAP_ID = 3','CARD_P_MAP_ID,CARD_NAME');
		$this->assign('cardsel',$cardsel);
		$this->display();
	}

	
	/*
	* 卡片统计
	**/
	public function statiscard() {
		$post = I('post');
		if($post['submit'] == "statiscard"){
			$where = "1=1";
			$falv  = filter_data('falv');	//列表查询
			//分公司
			if($falv['bid'] != '') {
				$where .= " and BRANCH_MAP_ID = '".$falv['bid']."'";
				$post['bid'] = $falv['bid'];
			}
			//合作伙伴公司
			if($falv['pid'] != '') {
				$pids = get_plv_childs($falv['pid'],1);
				$where .= " and PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $falv['pid'];
			}
			//卡套餐
			if($post['CARD_P_MAP_ID'] != '') {
				$where .= " and CARD_P_MAP_ID = '".$post['CARD_P_MAP_ID']."'";
			}
			//分页
			$fiRow = ($_REQUEST[C('VAR_PAGE')] ? $_REQUEST[C('VAR_PAGE')]-1 : 0) * C('PAGE_COUNT');
			$liRow = C('PAGE_COUNT');
			$list  = D($this->GVipcard)->groupVipcardlist($where, 'BRANCH_MAP_ID,PARTNER_MAP_ID', $fiRow.','.$liRow, 'PARTNER_MAP_ID');
			foreach($list as $key=>$val){
				$pids  = get_plv_childs($val['PARTNER_MAP_ID'],1);
				$total = D($this->GVipcard)->groupVipcardlist("PARTNER_MAP_ID in(".$pids.")", 'count(*) as CNT,CARD_STATUS', '', 'CARD_STATUS');				
				$news  = array();
				foreach($total as $key2=>$val2){
					$news[$val2['CARD_STATUS']] = $val2['CNT'];
				}
				$list[$key]['zhengcang']  = $news[0] ? $news[0] : '0';	//正常
				$list[$key]['kucun'] 	  = $news[1] ? $news[1] : '0';	//库存
				$list[$key]['dongjie']    = $news[2] ? $news[2] : '0';	//冻结
				$list[$key]['yihuanka']   = $news[3] ? $news[3] : '0';	//已换卡
				$list[$key]['zhikazhong'] = $news[8] ? $news[8] : '0';	//制卡中
				$list[$key]['xiaohu']     = $news[9] ? $news[9] : '0';	//销户
			}
			
			//分页参数
			$this->assign ( 'totalCount', 	C('PAGE_COUNT')==count($list) ? 1 : 0 );
	       	$this->assign ( 'numPerPage', 	'' );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
						
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
			
			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		$this->assign ('cardstatus',C('CARD_STATUS'));	//卡状态
		$this->assign ('cproduct',C('CARD_P_MAP_ID'));	//卡套餐
		\Cookie::set ('_currentUrl_', 		__SELF__);	
		$this->display();
	}
}