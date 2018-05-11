<?php
namespace Home\Controller;
// +----------------------------------------------------------------------
// | @gzy  首页
// +----------------------------------------------------------------------
class IndexController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->MMenu	= 'MMenu';
		$this->MAccess	= 'MAccess';
		$this->MUser	= 'MUser';
		$this->MShop	= 'MShop';
		$this->MPartner	= 'MPartner';
		$this->MSposreq	= 'MSposreq';
		$this->MSrisk	= 'MSrisk';
		$this->TTbbill  = 'TTbbill';
		$this->MNotice  = 'MNotice';

		$this->TAcqbill = 'TAcqbill';
		$this->TBbill   = 'TBbill';
		$this->TSckbill = 'TSckbill';
		$this->TBbill   = 'TBbill';
		$this->TPbill   = 'TPbill';
	}
	
	/*
	* 首页
	**/
	public function index() {
		$home = session('HOME');
		$user_id = $home['USER_ID'];
		$role_id = $home['ROLE_ID'];
		
		//如果是超级管理员，查看所有
		if(C('SPECIAL_USER') == $user_id){
			$menu = D($this->MMenu)->getMenulist("MENU_STATUS=1 and MENU_DISPLAY=1 and MENU_LEVEL!=1", 'MENU_ID,MENU_TITLE');
		}else{
			$menu = D($this->MMenu)->getMenulinelist("a.ROLE_ID='".$role_id."' and m.MENU_STATUS=1 and m.MENU_DISPLAY=1 and (m.MENU_LEVEL=0 or m.MENU_LEVEL=2)", 'm.MENU_ID,m.MENU_TITLE');
		}
		foreach($menu as $val){
			$html .= '<div class="accordionHeader"><h2><span></span>'.$val['MENU_TITLE'].'</h2></div><div class="accordionContent"><ul class="tree treeFolder">';
			$datas = $this->getchild_menu( $val['MENU_ID'] );
			foreach($datas as $val2) {
				if(empty($val2['MENU_DATA'])){
					$html .= '<li><a href="javascript:void(0);" rel="'.$val2['MENU_NAME'].'">'.$val2['MENU_TITLE'].'</a><ul>';
				}else{
					$html .= '<li><a href="'.$val2['MENU_DATA'].'" target="navTab" rel="'.$val2['MENU_NAME'].'">'.$val2['MENU_TITLE'].'</a><ul>';					
				}			
				$_datas = $this->getchild_menu( $val2['MENU_ID'] );
				if(is_array($_datas)){
					foreach ($_datas as $val3) {
						//去掉系统菜单 但在本地可以访问
						//if($val3['MENU_ID'] != '116' || CON_ENVIRONMENT != 'online'){
							$href  = empty($val3['MENU_DATA']) ? 'javascript:void(0);' : $val3['MENU_DATA'];
							$html .= '<li><a href="'.$href.'" target="navTab" rel="'. $val2['MENU_NAME'].'">'.$val3['MENU_TITLE'].'</a></li>';
						//}
					}
				}
				$html .=  '</ul></li>'; 
			}
			$html .= '</ul></div>';
		}
		
		//我的首页
		$page1 = '';
		$page2 = '';
		$page3 = '';
		$page4 = '';
		$page5 = '';
		$menu_pid = 436;
		//如果是超级管理员，查看所有
		if(C('SPECIAL_USER') == $user_id){
			$pdata = D('MMenu')->getMenulist("MENU_LEVEL=2 and MENU_STATUS=1 and MENU_PID='".$menu_pid."'", 'MENU_ID,MENU_NAME,MENU_TITLE');
			if(!empty($pdata[0])) {
				$cdata = D('MMenu')->getMenulist("MENU_PID='".$pdata[0]['MENU_ID']."' and MENU_LEVEL=3 and MENU_STATUS=1", 'MENU_ID,MENU_NAME,MENU_TITLE');
				if(!empty($cdata)) {
					foreach($cdata as $val){
						$list = D('MMenu')->getMenulist("MENU_PID='".$val['MENU_ID']."' and MENU_LEVEL=3 and MENU_STATUS=1 and MENU_DISPLAY=0", 'MENU_ID,MENU_NAME,MENU_TITLE');
						if(!empty($list)){
							switch($val['MENU_ID']){
								case 438:	//进件变更
									$page1 = array('title'=>$val['MENU_TITLE'], 'list'=>$list);
									break;
								case 444:	//库管
									$page2 = array('title'=>$val['MENU_TITLE'], 'list'=>$list);
									break;
								case 446:	//清算
									$page3 = array('title'=>$val['MENU_TITLE'], 'list'=>$list);
									break;
								case 447:	//风控
									$page4 = array('title'=>$val['MENU_TITLE'], 'list'=>$list);
									break;
								case 448:	//开票提醒
									$page5 = array('title'=>$val['MENU_TITLE'], 'list'=>$list);
									break;
							}
						}						
					}
				}
			}
		}else{
			$pdata = D('MMenu')->getMenulinelist("a.ROLE_ID='".$role_id."' and m.MENU_LEVEL=2 and m.MENU_STATUS=1 and m.MENU_PID='".$menu_pid."'", 'm.MENU_ID,m.MENU_NAME,m.MENU_TITLE');
			if(!empty($pdata[0])) {
				$cdata = D('MMenu')->getMenulinelist("a.ROLE_ID='".$role_id."' and m.MENU_PID='".$pdata[0]['MENU_ID']."' and m.MENU_LEVEL=3 and m.MENU_STATUS=1", 'm.MENU_ID,m.MENU_NAME,m.MENU_TITLE');
				if(!empty($cdata)) {
					foreach($cdata as $val){
						$list = D('MMenu')->getMenulinelist("a.ROLE_ID='".$role_id."' and m.MENU_PID='".$val['MENU_ID']."' and m.MENU_LEVEL=3 and m.MENU_STATUS=1 and m.MENU_DISPLAY=0", 'm.MENU_ID,m.MENU_NAME,m.MENU_TITLE');
						if(!empty($list)){
							switch($val['MENU_ID']){
								case 438:	//进件变更
									$page1 = array('title'=>$val['MENU_TITLE'], 'list'=>$list);
									break;
								case 444:	//库管
									$page2 = array('title'=>$val['MENU_TITLE'], 'list'=>$list);
									break;
								case 446:	//清算
									$page3 = array('title'=>$val['MENU_TITLE'], 'list'=>$list);
									break;
								case 447:	//风控
									$page4 = array('title'=>$val['MENU_TITLE'], 'list'=>$list);
									break;
								case 448:	//开票提醒
									$page5 = array('title'=>$val['MENU_TITLE'], 'list'=>$list);
									break;
							}
						}
					}
				}
			}
		}		
		//进件变更
		$jinjian = $this->getaddnum_count();
		$total1  = array('439'=>$jinjian['p_check_total'],'440'=>$jinjian['p_rcheck_total'],'441'=>$jinjian['s_check_total'],'442'=>$jinjian['s_rcheck_total']);
		//库管
		$kuguan = $this->getstock_count();
		$total2  = array('445'=>$kuguan['sposreq_total']);
		//清算
		$qingsuan = $this->getclear_count();
		$total3  = array('449'=>$qingsuan['THbill_total'],'450'=>$qingsuan['TAcqbill_total'],'451'=>$qingsuan['TBbill_total'],'452'=>$qingsuan['TPbill_total'],'453'=>$qingsuan['TSckbill_total']);	
		//风控
		$fengkong= $this->getriskrun_count();
		$total4  = array('454'=>$fengkong['srisk_total']);		
		//开票提醒
		$kaipiao = $this->getinvoice();
		$total5  = array('455'=>$kaipiao['sposreq_total']);
		
		//查询后台LOGO
		$home = session('HOME');
		// $this->wrong(json_encode($home));
		$channelModel = M('channel');
		$channel = $channelModel->field('LOGO_URL,CHANNEL_NAME,CHANNEL_MAP_ID')->where(array('CHANNEL_STATUS'=>0,'CHANNEL_MAP_ID'=>$home['CHANNEL_MAP_ID']))->find();
		$this->assign('channel',	$channel);
		$this->assign('home',		$home);
		$this->assign('menu_html',	$html);
		$this->assign('page1',		$page1);
		$this->assign('page2',		$page2);
		$this->assign('page3',		$page3);
		$this->assign('page4',		$page4);
		$this->assign('page5',		$page5);
		$this->assign('total1',		$total1);
		$this->assign('total2',		$total2);
		$this->assign('total3',		$total3);
		$this->assign('total4',		$total4);
		$this->assign('total5',		$total5);
		$this->assign('notice_data',$this->getnotice());	//公告

		$this->display();
	}
	
	/**
     * 统计进件变更数量[合作伙伴+商户]
    */
    private function getaddnum_count() {
    	$where_p_check 	= "a.PARTNER_STATUS = 6";
    	$where_p_rcheck = "a.PARTNER_STATUS = 4";
    	$where_s_check 	= "s.SHOP_STATUS = 6";
    	$where_s_rcheck = "s.SHOP_STATUS = 4";
    	$countPartner_c   = D($this->MPartner)->countPartner($where_p_check);	//待初审[合作伙伴]
    	$countPartner_r	  = D($this->MPartner)->countPartner($where_p_rcheck);	//待复审[合作伙伴]
    	$countShop_c	  = D($this->MShop)->countShop($where_s_check);			//待初审[商户]
    	$countShop_r	  = D($this->MShop)->countShop($where_s_rcheck);		//待复审[商户]
    	$res = array(
    		'p_check_total' 	=> $countPartner_c, 
    		'p_rcheck_total' 	=> $countPartner_r,
    		's_check_total' 	=> $countShop_c,
    		's_rcheck_total' 	=> $countShop_r
    	);
    	return $res;								
    }
    /**
     * 统计库存数量[装机申请]
    */
    private function getstock_count() {
    	$where_sposreq 	= "sp.INSTALL_FLAG = 1";
    	$countSposreq   = D($this->MSposreq)->countSposreq($where_sposreq);	//待初审[合作伙伴]
    	$res = array(
    		'sposreq_total' => $countSposreq
    	);
    	return $res;								
    }
    /**
     * 统计清算数量[清算+投保]
    */
    private function getclear_count() {
    	$this->TAcqbill = 'TAcqbill';
		$this->THbill   = 'THbill';
		$this->TBbill   = 'TBbill';
		$this->TSckbill = 'TSckbill';
		$this->TPbill   = 'TPbill';
		$where_check    = "CHECK_FLAG != 2";
    	//$countSposreq   = D($this->MSposreq)->countSposreq($where_sposreq);	//待初审[合作伙伴]
    	$countTAcqbill = D($this->TAcqbill)->countAcqbill($where_check);		//待处理[清算]
    	$countTHbill   = D($this->THbill)->countHbill($where_check);			//待处理[清算]
    	$countTSckbill = D($this->TSckbill)->countSckbill($where_check);		//待处理[清算]
    	$countTBbill   = D($this->TBbill)->countBbill($where_check);			//待处理[清算]
    	$countTPbill   = D($this->TPbill)->countPbill($where_check);			//待初审[投保]

    	$res = array(
    		'TAcqbill_total' => $countTAcqbill,
    		'THbill_total'	 => $countTHbill,
    		'TSckbill_total' => $countTSckbill,
    		'TBbill_total'	 => $countTBbill,
    		'TPbill_total'	 => $countTPbill
    	);
    	return $res;								
    }
    /**
     * 统计风控数量[风险控制]
    */
    private function getriskrun_count() {
    	$where_srisk = "sr.SHOP_GRADE = 4 or sr.SHOP_GRADE = 5";
    	$countSrisk  = D($this->MSrisk)->countSrisk($where_srisk);			//[风险控制]
    	$res = array(
    		'srisk_total' => $countSrisk
    	);
    	return $res;								
    }
    /**
     * 获取公告信息
    */
    private function getnotice($num = 3) {
    	$home  = session('HOME');
		$where = "BRANCH_MAP_ID = ".$home['BRANCH_MAP_ID']." and PARTNER_MAP_ID = ".$home['PARTNER_MAP_ID']." and NOTICE_TIME <= ".date('Ymd');
		$list  = D($this->MNotice)->getNoticelist($where, 'NOTICE_ID,NOTICE_TITLE', $num);	
		if(empty($list)){
			$list  = D($this->MNotice)->getNoticelist("NOTICE_TIME <= ".date('Ymd'), 'NOTICE_ID,NOTICE_TITLE', $num);	
		}
		return $list;			
    }

    /**
     * 获取开票提醒
    */
    private function getinvoice() {
		$home = session('HOME');
		$tax_FLAG = 0;
		//获取发票限制金额
		$leveldata = D($this->MPartner)->findPartner("PARTNER_MAP_ID = '".$home['PARTNER_MAP_ID']."'", 'l.REMINDER_AMT');
		if(empty($leveldata)){
			return $tax_FLAG;
		}
		//累计未开票	发票提醒 0是1否
		$tick 	   = D($this->TPbill)->findPbill("PARTNER_MAP_ID='".$home['PARTNER_MAP_ID']."' and TAX_TICKET_FLAG=1 and SETTLE_DATE<=".date('Ymd'), 'sum(SETTLE_AMT) as AMT');	
		if($tick['AMT'] >= $leveldata['REMINDER_AMT']){
			$tax_FLAG = 1;
		}
		return $tax_FLAG;
    }
    
	/**
     * 按父ID查找菜单子项
     * pid   		父菜单ID
     * with_self  	是否包括他自己
    */
    private function getchild_menu($pid, $with_self = 0) {
		$home = session('HOME');
		$user_id = $home['USER_ID'];
		$role_id = $home['ROLE_ID'];		
		
		$pid = intval($pid);
		//如果是无视权限限制的用户，则获取所有主菜单
		if(C('SPECIAL_USER') == $user_id){
			$result = D($this->MMenu)->getMenulist("MENU_STATUS=1 and MENU_DISPLAY=2 and MENU_LEVEL!=1 and MENU_PID='".$pid."'", 'MENU_ID,MENU_TITLE,MENU_NAME,MENU_DATA');
		}else{
			$result = D($this->MMenu)->getMenulinelist("a.ROLE_ID='".$role_id."' and m.MENU_STATUS=1 and m.MENU_DISPLAY=2 and m.MENU_LEVEL!=1 and m.MENU_PID='".$pid."'", 'm.MENU_ID,m.MENU_TITLE,m.MENU_NAME,m.MENU_DATA');
		}
		if($with_self) {
			$with[] = D($this->MMenu)->findMenu("MENU_ID='".$pid."'", 'MENU_ID,MENU_TITLE,MENU_NAME,MENU_DATA');
			$result = array_merge($with, $result);			
		}
        return $result;
    }
	
	/*
	* 改密码
	**/	
	public function user_uppwd() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "user_uppwd") {
			//验证
			if(empty($post['OLD_PASSWD']) || empty($post['USER_PASSWD']) || empty($post['NEW_PASSWD'])){
				$this->wrong("缺少必填项数据！");
			}
			if($post['USER_PASSWD'] != $post['NEW_PASSWD']){
				$this->wrong("两次新密码输入不一致！");
			}
			$old_pwd = strtoupper(md5(strtoupper(md5($post['OLD_PASSWD']))));
			$info	 = D($this->MUser)->findUser("USER_ID = '".$home['USER_ID']."'", 'USER_PASSWD');
			if($info['USER_PASSWD'] != $old_pwd){
				$this->wrong("原始密码错误！");
			}
			$resdata = array(
				'USER_PASSWD'	=>	strtoupper(md5(strtoupper(md5($post['NEW_PASSWD'])))),
				'UPDATE_TIME'	=>	date('YmdHis'),
			);
			$res = D($this->MUser)->updateUser("USER_ID = '".$home['USER_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right('修改成功！', 'closeCurrent');
		}
		$this->display();
	}
}