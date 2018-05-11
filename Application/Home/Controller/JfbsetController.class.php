<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  积分宝设置
// +----------------------------------------------------------------------
class JfbsetController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->MPlevel	= 'MPlevel';
		$this->MFeecfg		= 'MFeecfg';
		$this->MWithhold 	= 'MWithhold';
		$this->MBank 		= 'MBank';
		$this->MCproduct 	= 'MCproduct';
		$this->MCardlevel 	= 'MCardlevel';
		$this->MCardtype 	= 'MCardtype';
		$this->MVipmarket 	= 'MVipmarket';
	}
	
	/*
	* 合作伙伴等级维护 列表
	**/
	public function plevel() {
		$post = I('post');
		if($post['submit'] == "plevel"){
			$where = "1=1";
			//等级
			if($post['PLEVEL_NAME']) {
				$where .= " and PLEVEL_NAME = '".$post['PLEVEL_NAME']."'";
			}
			//分页
			$count = D($this->MPlevel)->countPlevel($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MPlevel)->getPlevellist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('plevel_name',		C('PLEVEL_NAME'));	//合作伙伴等级
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 合作伙伴等级维护 详情
	**/
	public function plevel_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MPlevel)->findPlevel("PLEVEL_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('plevel_name',		C('PLEVEL_NAME'));	//合作伙伴等级
		$this->assign ('info', $info);
		$this->display();
	}
	/*
	* 合作伙伴等级维护 修改
	**/
	public function plevel_edit() {
		$post = I('post');
		if($post['submit'] == "plevel_edit") {
			//验证
			if(empty($post['JOIN_FEE']) || empty($post['JOINFEE_BEGIN']) || empty($post['JOINFEE_END']) || empty($post['FUND_AMT']) || empty($post['FUND_BEGIN']) || empty($post['FUND_END'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'JOIN_FEE'		=>	setMoney($post['JOIN_FEE'], '6'),
				'REMINDER_AMT'	=>	setMoney($post['REMINDER_AMT'], '6'),
				'JOINFEE_BEGIN'	=>	setMoney($post['JOINFEE_BEGIN'], '6'),
				'JOINFEE_END'	=>	setMoney($post['JOINFEE_END'], '6'),
				'FUND_AMT'		=>	setMoney($post['FUND_AMT'], '6'),
				'FUND_BEGIN'	=>	setMoney($post['FUND_BEGIN'], '6'),
				'FUND_END'		=>	setMoney($post['FUND_END'], '6')
			);
			$res = D($this->MPlevel)->updatePlevel("PLEVEL_MAP_ID='".$post['PLEVEL_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MPlevel)->findPlevel("PLEVEL_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('plevel_name',		C('PLEVEL_NAME'));	//合作伙伴等级
		$this->assign('info', 				$info);	
		$this->display('plevel_add');
	}
	
	
	
	/*
	* 积分宝分润办法设置
	**/
	public function jfbfeecfg() {
		$post = I('post');
		if($post['submit'] == "jfbfeecfg"){
			$where = "1=1";
			//分润名目
			if($post['CFG_FLAG']) {
				$where .= " and CFG_FLAG = '".$post['CFG_FLAG']."'";
			}
			//分页
			$count = D($this->MFeecfg)->countFeecfg($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MFeecfg)->getFeecfglist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('cfg_flag',			C('CFG_FLAG'));		//分润名目
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 积分宝分润办法设置 详情
	**/
	public function jfbfeecfg_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MFeecfg)->findFeecfg("CFG_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('cfg_flag',			C('CFG_FLAG'));		//分润名目
		$this->assign('info', 				$info);
		$this->display('jfbfeecfg_show'.($info['CFG_FLAG']==1 ? 1 : 2));
	}
	/*
	* 积分宝分润办法设置 修改
	**/
	public function jfbfeecfg_edit() {
		$post = I('post');
		if($post['submit'] == "jfbfeecfg_edit") {
			if($post['CFG_FLAG'] == 1){
				//验证
				$num = $post['BRANCH_FEE1'] + $post['NTAX_FEE1'] + $post['CADF_FEE1'] + $post['EATF_FEE1'] + $post['ISS_BRANCH_FEE2'] + $post['ACQ_BRANCH_FEE2'] + $post['ISS_PARTNER_FEE1'] + $post['ACQ_PARTNER_FEE1'] + $post['ISS_LCF_FEE1'] + $post['ISS_PARTNER_FEE2'] + $post['ACQ_PARTNER_FEE2'] + $post['ISS_PARTNER_FEE3'] + $post['PARTNER_FEE3A'] + $post['PARTNER_FEE3B'];
				if($post['CON_PER'] != $num){
					$this->wrong("会员收益比例应等于其下方的所有收益比例之和！");
				}
				//组装数据
				$resdata = array(
					'CFG_FLAG'			=>	$post['CFG_FLAG'],
					'CFG_NAME'			=>	C('CFG_FLAG')[$post['CFG_FLAG']],
					'CARD_OPENFEE'		=>	0,
					'DIV_PER'			=>	0,
					'TRAFICC_FEE'		=>	0,
					'CON_PER'			=>	setMoney($post['CON_PER'], '2'),
					'BRANCH_FEE1'		=>	setMoney($post['BRANCH_FEE1'], '2'),
					'CADF_FEE1'			=>	setMoney($post['CADF_FEE1'], '2'),
					'NTAX_FEE1'			=>	setMoney($post['NTAX_FEE1'], '2'),
					'EATF_FEE1'			=>	setMoney($post['EATF_FEE1'], '2'),
					'ISS_BRANCH_FEE2'	=>	setMoney($post['ISS_BRANCH_FEE2'], '2'),
					'ISS_PARTNER_FEE1'	=>	setMoney($post['ISS_PARTNER_FEE1'], '2'),
					'ISS_LCF_FEE1'		=>	setMoney($post['ISS_LCF_FEE1'], '2'),
					'ISS_LCA_FEE1'		=>	0,
					'ISS_PARTNER_FEE2'	=>	setMoney($post['ISS_PARTNER_FEE2'], '2'),
					'ISS_PARTNER_FEE3'	=>	setMoney($post['ISS_PARTNER_FEE3'], '2'),
					'VIP_PARTNER_FEE1'	=>	0,
					'VIP_PARTNER_FEE2'	=>	0,
					'ACQ_BRANCH_FEE2'	=>	setMoney($post['ACQ_BRANCH_FEE2'], '2'),
					'ACQ_PARTNER_FEE1'	=>	setMoney($post['ACQ_PARTNER_FEE1'], '2'),
					'ACQ_PARTNER_FEE2'	=>	setMoney($post['ACQ_PARTNER_FEE2'], '2'),
					'PARTNER_FEE3A'		=>	setMoney($post['PARTNER_FEE3A'], '2'),
					'PARTNER_FEE3B'		=>	setMoney($post['PARTNER_FEE3B'], '2')
				);
			}else{
				//验证
				$num = $post['BRANCH_FEE1'] + $post['ISS_BRANCH_FEE2'] + $post['TRAFICC_FEE'] + $post['ISS_PARTNER_FEE1'] + $post['ISS_PARTNER_FEE2'] + $post['ISS_PARTNER_FEE3'];
				if($post['CARD_OPENFEE'] != $num){
					$this->wrong("会员卡费金额应等于其下方的所有收益金额之和！");
				}
				//组装数据
				$resdata = array(
					'CFG_FLAG'			=>	$post['CFG_FLAG'],
					'CFG_NAME'			=>	C('CFG_FLAG')[$post['CFG_FLAG']],
					'CARD_OPENFEE'		=>	setMoney($post['CARD_OPENFEE'], '2'),
					'DIV_PER'			=>	$post['DIV_PER'] ? setMoney($post['DIV_PER'], '2') : 0,		//等于3出现
					'TRAFICC_FEE'		=>	setMoney($post['TRAFICC_FEE'], '2'),
					'CON_PER'			=>	0,
					'BRANCH_FEE1'		=>	setMoney($post['BRANCH_FEE1'], '2'),
					'CADF_FEE1'			=>	0,
					'NTAX_FEE1'			=>	0,
					'EATF_FEE1'			=>	0,
					'ISS_BRANCH_FEE2'	=>	setMoney($post['ISS_BRANCH_FEE2'], '2'),
					'ISS_PARTNER_FEE1'	=>	setMoney($post['ISS_PARTNER_FEE1'], '2'),
					'ISS_LCF_FEE1'		=>	0,
					'ISS_LCA_FEE1'		=>	0,
					'ISS_PARTNER_FEE2'	=>	setMoney($post['ISS_PARTNER_FEE2'], '2'),
					'ISS_PARTNER_FEE3'	=>	setMoney($post['ISS_PARTNER_FEE3'], '2'),
					'VIP_PARTNER_FEE1'	=>	0,
					'VIP_PARTNER_FEE2'	=>	0,
					'ACQ_BRANCH_FEE2'	=>	0,
					'ACQ_PARTNER_FEE1'	=>	0,
					'ACQ_PARTNER_FEE2'	=>	0,
					'PARTNER_FEE3A'		=>	0,
					'PARTNER_FEE3B'		=>	0
				);
			}
			$res = D($this->MFeecfg)->updateFeecfg("CFG_ID='".$post['CFG_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MFeecfg)->findFeecfg("CFG_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('cfg_flag',			C('CFG_FLAG'));		//分润名目
		$this->assign('info', 				$info);
		$this->display('jfbfeecfg_add'.($info['CFG_FLAG']==1 ? 1 : 2));
	}
	
	
	
	/*
	* 积分宝卡套餐设置
	**/
	public function jfbsetting() {
		$post = I('post');
		if($post['submit'] == "jfbsetting"){
			$where = "1=1";
			//等级
			if($post['CARD_NAME']) {
				$where .= " and c.CARD_NAME like '%".$post['CARD_NAME']."%'";
			}
			//分页
			$count = D($this->MCproduct)->countCproduct($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MCproduct)->getCproductlist($where, 'c.*,l.CLEVEL_NAME,t.CTYPE_NAME', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 积分宝卡套餐设置 详情
	**/
	public function jfbsetting_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MCproduct)->findCproduct("CARD_P_MAP_ID='".$id."'", 'c.*,l.CLEVEL_NAME,t.CTYPE_NAME');
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 积分宝卡套餐设置 修改
	**/
	public function jfbsetting_edit() {
		$post = I('post');
		if($post['submit'] == "jfbsetting_edit"){
			//验证
			if($post['CARD_P_MAP_ID']=='' || empty($post['CARD_NAME']) || empty($post['CARD_BALANCE']) || empty($post['CARD_EXP_NUM']) || $post['USER_OPENFEE']=='' || $post['USER_DEPFEE']==''){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'CARD_BIN'		=>	$post['CARD_BIN'],
				'CARD_NAME'		=>	$post['CARD_NAME'],
				'CARD_DESC'		=>	$post['CARD_DESC'],
				'CARD_TYPE'		=>	$post['CARD_TYPE'],
				'CARD_LEVEL'	=>	$post['CARD_LEVEL'],
				'CARD_BALANCE'	=>	setMoney($post['CARD_BALANCE'], '2'),
				'CARD_EXP_NUM'	=>	$post['CARD_EXP_NUM'],
				'CARD_NUM'		=>	0,
				'USER_OPENFEE'	=>	setMoney($post['USER_OPENFEE'], '2'),
				'USER_DEPFEE'	=>	setMoney($post['USER_DEPFEE'], '2'),
			);
			$res = D($this->MCproduct)->updateCproduct("CARD_P_MAP_ID='".$post['CARD_P_MAP_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MCproduct)->findCproduct("CARD_P_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//卡级别
		$card_level = D($this->MCardlevel)->getCardlevellist("CLEVEL_ID != ''", 'CLEVEL_ID,CLEVEL_NAME');
		//卡种类
		$card_type  = D($this->MCardtype)->getCardtypelist("CTYPE_ID != ''", 'CTYPE_ID,CTYPE_NAME');
		$this->assign('card_level', 		$card_level);
		$this->assign('card_type', 			$card_type);
		$this->assign('info', 				$info);
		$this->display('jfbsetting_add');
	}
	
	
	
	/*
	* 积分宝卡推广办法设置
	**/
	public function jfbmarket() {
		$post = I('post');
		if($post['submit'] == "jfbmarket"){
			$where = "1=1";
			//等级
			if($post['CARD_NAME']) {
				$where .= " and c.CARD_NAME like '%".$post['CARD_NAME']."%'";
			}
			//分页
			$count = D($this->MVipmarket)->countVipmarket($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MVipmarket)->getVipmarketlist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('market_flag',		C('MARKET_FLAG'));	//产品推广标志
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 积分宝卡推广办法设置 添加
	**/
	public function jfbmarket_add() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "jfbmarket_add"){
			//验证
			if(empty($post['MARKET_NAME']) || empty($post['USER_RBLAMT']) || empty($post['USER_RBLPER']) || empty($post['BEGIN_DATE']) || empty($post['BEGIN_END']) || $post['CARD_P_MAP']==''){
				$this->wrong("缺少必填项数据！");
			}
			$post['BEGIN_DATE'] = date("Ymd", strtotime($post['BEGIN_DATE'])).'000000';
			$post['BEGIN_END']  = date("Ymd", strtotime($post['BEGIN_END'])).'235959';
			if($post['BEGIN_DATE'] > $post['BEGIN_END']){
				$this->wrong("请规范选择失效日期！");
			}
			//组装数据
			$resdata = array(
				'CARD_P_MAP'		=>	$post['CARD_P_MAP'],
				'MARKET_NAME'		=>	$post['MARKET_NAME'],
				'MARKET_DESC'		=>	$post['MARKET_DESC'],
				'MARKET_FLAG'		=>	$post['MARKET_FLAG'],
				'BEGIN_DATE'		=>	$post['BEGIN_DATE'],
				'BEGIN_END'			=>	$post['BEGIN_END'],
				'USER_LOADPER'		=>	0,
				'USER_RBLAMT'		=>	setMoney($post['USER_RBLAMT'], '2'),
				'USER_RBLPER'		=>	$post['USER_RBLPER'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'CREATE_TIME'		=>	date('YmdHis'),
			);
			$res = D($this->MVipmarket)->addVipmarket($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//卡套餐列表
		$cprod_list = D($this->MCproduct)->getCproductlist('', 'CARD_P_MAP_ID,CARD_NAME');
		$this->assign('cprod_list', 		$cprod_list);
		$this->assign('market_flag',		C('MARKET_FLAG'));	//产品推广标志
		$this->display();
	}
	/*
	* 积分宝卡推广办法设置 详情
	**/
	public function jfbmarket_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MVipmarket)->findVipmarket("MARKET_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['CARD_P_MAP'] = str_split($info['CARD_P_MAP']);
		//卡套餐列表
		$cprod_list = D($this->MCproduct)->getCproductlist('', 'CARD_P_MAP_ID,CARD_NAME');
		$this->assign('cprod_list', 		$cprod_list);
		$this->assign('market_flag',		C('MARKET_FLAG'));	//产品推广标志
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 积分宝卡推广办法设置 修改
	**/
	public function jfbmarket_edit() {
		$home = session('HOME');
		$post = I('post');
		if($post['submit'] == "jfbmarket_edit"){
			//验证
			if(empty($post['MARKET_NAME']) || empty($post['USER_RBLAMT']) || empty($post['USER_RBLPER']) || empty($post['BEGIN_DATE']) || empty($post['BEGIN_END']) || $post['CARD_P_MAP']==''){
				$this->wrong("缺少必填项数据！");
			}
			$post['BEGIN_DATE'] = date("Ymd", strtotime($post['BEGIN_DATE'])).'000000';
			$post['BEGIN_END']  = date("Ymd", strtotime($post['BEGIN_END'])).'235959';
			if($post['BEGIN_DATE'] > $post['BEGIN_END']){
				$this->wrong("请规范选择失效日期！");
			}
			//组装数据
			$resdata = array(
				'CARD_P_MAP'		=>	$post['CARD_P_MAP'],
				'MARKET_NAME'		=>	$post['MARKET_NAME'],
				'MARKET_DESC'		=>	$post['MARKET_DESC'],
				'MARKET_FLAG'		=>	$post['MARKET_FLAG'],
				'BEGIN_DATE'		=>	$post['BEGIN_DATE'],
				'BEGIN_END'			=>	$post['BEGIN_END'],
				'USER_RBLAMT'		=>	setMoney($post['USER_RBLAMT'], '2'),
				'USER_RBLPER'		=>	$post['USER_RBLPER'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
			);
			$res = D($this->MVipmarket)->updateVipmarket("MARKET_ID='".$post['MARKET_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MVipmarket)->findVipmarket("MARKET_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$info['CARD_P_MAP'] = str_split($info['CARD_P_MAP']);
		//卡套餐列表
		$cprod_list = D($this->MCproduct)->getCproductlist('', 'CARD_P_MAP_ID,CARD_NAME');
		$this->assign('cprod_list', 		$cprod_list);
		$this->assign('market_flag',		C('MARKET_FLAG'));	//产品推广标志
		$this->assign('info', 				$info);
		$this->display('jfbmarket_add');
	}
	/*
	* 积分宝卡推广办法设置 删除
	**/
	public function jfbmarket_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$res = D($this->MVipmarket)->delVipmarket("MARKET_ID='".$id."'");
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg']);
	}
}