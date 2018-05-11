<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @gzy  
// +----------------------------------------------------------------------
class PublicController extends HomeController {	
	
	public function _initialize() {
		$this->MUser		= 'MUser';
		$this->MTrans		= 'MTrans';
		$this->MPaytype		= 'MPaytype';
		$this->TDkls		= 'TDkls';
		$this->TTrace		= 'TTrace';
		$this->TSmsls		= 'TSmsls';
		$this->MBid			= 'MBid';
		$this->MPartner		= 'MPartner';
		$this->MSmsmodel	= 'MSmsmodel';
		$this->GVip			= 'GVip';
		$this->MReg			= 'MReg';
		$this->GVipcard		= 'GVipcard';
		$this->MFeecfg		= 'MFeecfg';
		$this->GLap			= 'GLap';
		$this->MDevice		= 'MDevice';
		$this->MBank		= 'MBank';
		$this->MCity		= 'MCity';
		$this->MExcel 		= 'MExcel';
		$this->MDkco 		= 'MDkco';
		$this->TTrabak1	 	= 'TTrabak1';
		$this->MSdkb	 	= 'MSdkb';
		$this->MShop	 	= 'MShop';
	}
	
	/*
	* 生成验证码
	**/
    public function verify() {
		$vercfg =    array(
			'imageW'	=>	0,				//设置为0自动计算
			'imageH'	=>	0,				//设置为0自动计算
			'fontSize'  =>	16,				//验证码字体大小
			'codeSet' 	=>	'023456789',
			'length'    =>	4,				//验证码位数
			'useCurve'  =>	false,			//是否使用混淆曲线
			'useNoise'  =>	false,			//是否添加杂点
			'bg'		=>	array(249, 249, 249)
		);
		$Verify =  new \Think\Verify($vercfg);
		$Verify->fontttf = '4.ttf'; 
		$Verify->entry();
	}	
	
	//退出
	public function logout(){
		//日志
		setLog(7, '用户退出成功！');		
		session(null);
		redirect(__APP__."/Home/Public/login.html");
	}
	
	/*
	* 登录
	**/
	public function login(){
		$home = session('HOME');
		if($home['USER_ID']) {
			redirect(__APP__);
		}
		//验证 cookie
		$jfb_mo = base64_decode(cookie('jfb_mo'));
		$this->assign('mobile',	$jfb_mo);
		$this->display();
	}
	
	/*
	* 登录校验
	* * 改动此处，也要改动 Public checklogin 方法
	* @post:
	* 	mobile			手机号码
	*	password		用户密码
	**/
    public function checklogin() {
		$post = array(
			'user_no'	=> 	I('mobile'),
			'password'	=>	I('password'),	//已经MD5一次
			'verify'	=>	I('verify'),
			'rememb'	=>	I('rememb'),
		);
		if(empty($post['user_no']) || empty($post['password']) || empty($post['verify'])){
			$this->ajaxResponse(1, '缺少用户工号、用户密码、验证码！');
		}
		if(check_verify($post['verify']) != 0){
			$this->ajaxResponse(1, '验证码错误！');
		}
		$post['password'] = strtoupper(md5(strtoupper($post['password'])));
		
		$response = D($this->MUser)->Login($post);
		if($response['state'] != 0){
			$this->ajaxResponse(1, $response['msg']);
		}
		
		//RBAC
		import('Vendor.Common.RBAC');
		session(C('USER_AUTH_KEY'), $response['userinfo']['USER_ID']);
		if(C('SPECIAL_USER') == $response['userinfo']['USER_ID']){
			session(C('ADMIN_AUTH_KEY'), true);
		}else{
			session(C('ADMIN_AUTH_KEY'), false);
		}
		session('_session_time', time());
		
		//session
		$sedata = array(
			'BRANCH_MAP_ID'		=>	$response['userinfo']['BRANCH_MAP_ID'],
			'BRANCH_NAME'		=>	$response['userinfo']['BRANCH_NAME'],
			'PARTNER_MAP_ID'	=>	$response['userinfo']['PARTNER_MAP_ID'],
			'USER_ID'			=>	$response['userinfo']['USER_ID'],
			'USER_NO'			=>	$response['userinfo']['USER_NO'],
			'USER_NAME'			=>	$response['userinfo']['USER_NAME'],
			'USER_MOBILE'		=>	$response['userinfo']['USER_MOBILE'],
			'USER_LEVEL'		=>	$response['userinfo']['USER_LEVEL'],
			'ROLE_ID'			=>	$response['userinfo']['ROLE_ID'],
			'ROLE_NAME'			=>	$response['userinfo']['ROLE_NAME'],
			'USER_PASSWD'		=>	$response['userinfo']['USER_PASSWD'],
		);
		session('HOME', $sedata);
		
		//cookie
		cookie('jfb_mo', base64_encode($response['userinfo']['USER_NO']), 30*24*3600);
		if($post['rememb'] == 1){
			cookie('jfb_ch', base64_encode(md5(md5($response['userinfo']['USER_ID']."@#jf"))), 7*24*3600);
		}
		
		//RBAC缓存访问权限
		\RBAC::saveAccessList();
		
		//日志
		setLog(1, '用户登录成功！');
		
		$this->ajaxResponse(0, '恭喜您，登录成功！');
    }

	
	//忘记密码
	public function forget(){
		$this->display();
	}
	
	/*
	* Excel导入
	**/
	/* public function insert($begin, $num, $j){
		set_time_limit(0);
		for($i=1000; $i<1100; $i++){
			$num   	= 10000;
			$begin 	= ($i-1) * $num;
			$num	= $i * $num;
			$this->insert($begin, $num, 1);
		}
		
		
		$str = '';
		for($i=$begin; $i<$num; $i++){
			$mid  = $i%10000;
			$str .= "('".$mid."', 'AA_".$i."'),";
		}
		$str = substr($str, 0, -1).';';
		$sql = "insert  into `a`(`MM_ID`,`NAME`) values ".$str;
		file_put_contents("sql".$j.".sql", $sql.PHP_EOL, FILE_APPEND);		
	} */
    public function ceshi() {
		/* set_time_limit(0);	
		vendor("PHPExcel180.PHPExcel");
		$file_name 	   = 'shuilv.xls';	//cardbin.xls city.xls
		$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($file_name, $encode='utf-8');
		$sheet 		   = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); // 取得总行数
		$highestColumn = $sheet->getHighestColumn(); // 取得总列数
		
		
		$Model = M('rates');
		for($i=3; $i<=$highestRow; $i++){	
			$A = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue(); 
			$B = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue(); 
			$C = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue(); 
			$D = $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue(); 
			$E = $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue(); 
			$F = $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
			$G = $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
			$H = $objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
			$I = $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
			$J = $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
			$K = $objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
			$M = $objPHPExcel->getActiveSheet()->getCell("M".$i)->getValue();
			
			$level = '';
			if(empty($C) && empty($D) && empty($E) && empty($F) && empty($G) && empty($H) && empty($I)){
				$level = 1;
			}else if(!empty($C) && empty($D) && empty($E) && empty($F) && empty($G) && empty($H) && empty($I)){
				$level = 2;
			}else if(!empty($C) && !empty($D) && empty($E) && empty($F) && empty($G) && empty($H) && empty($I)){
				$level = 3;
			}else if(!empty($C) && !empty($D) && !empty($E) && empty($F) && empty($G) && empty($H) && empty($I)){
				$level = 4;
			}else if(!empty($C) && !empty($D) && !empty($E) && !empty($F) && empty($G) && empty($H) && empty($I)){
				$level = 5;
			}else if(!empty($C) && !empty($D) && !empty($E) && !empty($F) && !empty($G) && empty($H) && empty($I)){
				$level = 6;
			}else if(!empty($C) && !empty($D) && !empty($E) && !empty($F) && !empty($G) && !empty($H) && empty($I)){
				$level = 7;
			}else if(!empty($C) && !empty($D) && !empty($E) && !empty($F) && !empty($G) && !empty($H) && !empty($I)){
				$level = 8;
			}
			
			if($M > 0 && $M < 1){
				$M = $M * 100 .'%';
			}
			if(strpos($M, '，') !== false) {
				$M = str_replace('，', ",", $M);
			}
			if(strpos($M, '、') !== false) {
				$M = str_replace('、', ",", $M);
			}
			if(strpos($M, '%%') !== false) {
				$M = str_replace('%%', "%", $M);
			}
			
			
			
			
			//组装数据
			$resdata = array(
				'RATES_CODE'	=>	$J,
				'RATES_LEVEL'	=>	$level,
				'RATES_NAME'	=>	$K,
				'RATES_PERC'	=>	$M,
			);
			$Model->data($resdata)->add();
		}
		exit; */
		
		
		
    	/* set_time_limit(0);	
		vendor("PHPExcel180.PHPExcel");
		$file_name 	   = 'citys.xls';	//cardbin.xls city.xls
		$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($file_name, $encode='utf-8');
		$sheet 		   = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); // 取得总行数
		$highestColumn = $sheet->getHighestColumn(); // 取得总列数 */
		
		/*$Model = M('bank');
		for($i=2; $i<=$highestRow; $i++){
			//$BANK_NAME	= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
			$ISSUE_CODE = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
			$BANK_BNAME	= $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
			if($ISSUE_CODE){
				$count = $Model->where("BANK_BID='".$BANK_BID."'")->count();
				if($count == 0){
					//组装数据
					$resdata = array(
						'ISSUE_CODE'	=>	'0'.$ISSUE_CODE.'0000',
						'BANK_TYPE'		=>	1,
						'BANK_NAME'		=>	$BANK_BNAME,
					);
					$Model->data($resdata)->add();
				}
			}
		}*/
		
		/*$Model = M('bid');
		for($i=3; $i<=$highestRow; $i++){
			//$BANK_NAME	= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
			$BANK_BID 	= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
			$BANK_BNAME	= $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
			$CITY_S_CODE= $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
			if($BANK_BID){
				$count = $Model->where("BANK_BID='".$BANK_BID."'")->count();
				if($count == 0){
					$issue_code = '0'.substr($BANK_BID,0,3).'0000';
					$city_code = substr($CITY_S_CODE,0,2);
					//银行数据
					$bankdata = D('MBank')->findBank('ISSUE_CODE = "'.$issue_code.'"','ISSUE_CODE,BANK_NAME');
					$citydata = D('MCity')->findCity("PROVINCE_CODE='".$city_code."'",'PROVINCE_NAME');
					//组装数据
					$resdata = array(
						'BANK_TYPE'		=>	'9',
						'ISSUE_CODE'	=>	'0'.substr($BANK_BID,0,3).'0000',
						'BANK_NAME'		=>	$bankdata['BANK_NAME'] ? $bankdata['BANK_NAME'] : '',
						'CITY_S_CODE'	=>	$CITY_S_CODE ? $CITY_S_CODE : '',
						'CITY_S_NAME'	=>	$citydata['PROVINCE_NAME'] ? $citydata['PROVINCE_NAME'] : '',
						'BANK_BID'		=>	$BANK_BID,
						'BANK_BNAME'	=>	$BANK_BNAME ? $BANK_BNAME : ''
					);
					//dump($resdata);exit;
					$Model->data($resdata)->add();
				}
			}
		}*/
		
		
		/* $Model = M('mcc');
		for($i=3; $i<=$highestRow; $i++){
			$MCC_TYPE	= $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
			$MCC_NAME 	= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
			$MCC_CODE	= $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
			$MCC_DESC 	= $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
			if($MCC_CODE){
				$count = $Model->where("MCC_CODE='".$MCC_CODE."'")->count();
				if($count == 0){
					//组装数据
					$resdata = array(
						'MCC_TYPE'	=>	$MCC_TYPE ? $MCC_TYPE : '',
						'MCC_CODE'	=>	$MCC_CODE ? $MCC_CODE : '',
						'MCC_NAME'	=>	$MCC_DESC ? $MCC_DESC : $MCC_NAME,
						'MCC_DESC'	=>	$MCC_NAME ? $MCC_NAME : $MCC_DESC,
					);
					$Model->data($resdata)->add();
				}
			}
		} */
		
		/* $Model = M('cardbin');
		for($i=6; $i<=$highestRow; $i++){
			$ISSUE_CODE = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
			$ISSUE_NAME = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
			$CARD_NAME	= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
			$PAN_OFF 	= $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
			$PAN_LEN 	= $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
			$CARD_BIN 	= $objPHPExcel->getActiveSheet()->getCell("O".$i)->getValue();
			$CARD_TRACK = $objPHPExcel->getActiveSheet()->getCell("P".$i)->getValue();
			$CARD_TYPE 	= $objPHPExcel->getActiveSheet()->getCell("Q".$i)->getValue();			
			$count = $Model->where("CARD_BIN='".$CARD_BIN."'")->count();
			if($count == 0){
				$length = strlen($ISSUE_CODE);
				//组装数据
				$resdata = array(
					'ISSUE_CODE'=>	$length==7 ? '0'.$ISSUE_CODE : $ISSUE_CODE,
					'ISSUE_NAME'=>	$ISSUE_NAME,
					'CARD_BIN'=>	$CARD_BIN,
					'CARD_NAME'=>	$CARD_NAME,
					'CARD_TYPE'=>	$CARD_TYPE=='贷记卡' ? 2 : 1,
					'CARD_TRACK'=>	$CARD_TRACK,
					'PAN_OFF'=>		$PAN_OFF,
					'PAN_LEN'=>		$PAN_LEN,
					'BIN_FLAG'=>	0
				);
				$Model->data($resdata)->add();
			}
		} */
		
		/* $Model = M('city');
		for($i=3; $i<=$highestRow; $i++){
			$code   = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
			$parent = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue(); 
			$name   = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue(); 
			$str    = $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue(); 
			$level  = $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue(); 
			$tel  	= $objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
			$zip  	= $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
			
			if($level == 3){
				$arr = array();
				$arr = explode(",", $str);
				//组装数据
				$resdata = array(
					'PROVINCE_CODE'	=>	substr($parent, 0, 2),
					'PROVINCE_NAME'	=>	$arr[0] ? $arr[0] : '',
					'CITY_CODE'		=>	substr($parent, 0, 4),
					'CITY_NAME'		=>	$arr[1] ? $arr[1] : '',
					'CITY_S_CODE'	=>	$code,
					'CITY_S_NAME'	=>	$arr[2] ? $arr[2] : '',
					'CITY_ZIP_CODE'	=>	$zip ? $zip : '',
					'CITY_TEL_CODE'	=>	$tel ? $tel : '',
				);
				$Model->data($resdata)->add();
			}
		} */
		
		$this->display();
	}
	
	//---------------------------------------- 以下ajax --------------------------------------------
	
	/*
	* 省市县处理 模板发送ajax
	**/	
    public function ajaxgetcity() {
		$post = array(
			'flag'=>		$_REQUEST['flag'],	//2市3县
			'code'=>		$_REQUEST['code']
		);
		if($post['flag'] == 2){
			$res = D('MCity')->getCity_clist($post['code']);
		}else if($post['flag'] == 3){			
			$res = D('MCity')->getCity_alist($post['code']);
		}		
		$this->ajaxReturn($res);
	}	

	/*
	* 邮编处理 模板发送ajax
	**/	
    public function ajaxgetcode() {
    	$city_no = $_REQUEST['city_no'];
		if (empty($_REQUEST['city_no'])) {
			$this->ajaxResponse(1,'缺少银行代码和城市代码');
		}
		$res = D('MCity')->findCity('CITY_S_CODE = "'.$city_no.'"','CITY_ZIP_CODE');
		if (empty($res)) {
			$res = array('CITY_ZIP_CODE' => '');
			$this->ajaxReturn($res);
		}
		$this->ajaxReturn($res);
	}
	/*
	* 归属级别处理() 模板发送ajax
	**/	
   /* public function ajaxgetplv() {
		$post = array(
			'p_id'	=>	$_REQUEST['p_id'],
			'b_id'	=>	$_REQUEST['b_id'],
			'flag'	=>	$_REQUEST['flag']
		);
		if (!empty($post['b_id'])) {
			$b_where = ' and BRANCH_MAP_ID = '.$post['b_id'];
		}else{
			$p_where = ' and PARTNER_MAP_ID_P = "'.$post['p_id'].'"';
		}
		$where = 'PARTNER_LEVEL = '.$post['flag'].$b_where.$p_where;
		//$res = D('MPartner')->getPlvsel($where,'PARTNER_MAP_ID_P,PARTNER_LEVEL');
		$res = D('MPartner')->getPartner_select($where);
		if (empty($res)) {
			$res[] = array('', '请选择');
		}
		$this->ajaxReturn($res);
	}*/

	/*
	* 归属级别处理() 模板发送ajax
	**/	
    public function ajaxgetsellv() {
		$post = array(
			'bid'	=>	$_REQUEST['bid'],
			'pid'	=>	$_REQUEST['pid'],
			'flag'	=>	$_REQUEST['flag'],
			'lv'	=>	$_REQUEST['levelsel']
		);
		$res = '';
		switch ($post['flag']) {
			case '1':
				$where = 'BRANCH_MAP_ID = '.$post['pid'].' and PARTNER_MAP_ID_P = 0 and PARTNER_STATUS = 0 and PARTNER_LEVEL = 1';
				$res = D('MPartner')->getPartner_select($where);
				break;
			case '2':
				$where = 'BRANCH_MAP_ID = '.$post['bid'].' and PARTNER_MAP_ID_P = "'.$post['pid'].'" and PARTNER_LEVEL = 2 and PARTNER_STATUS = 0';
				$res = D('MPartner')->getPartner_select($where);
				break;
			case '3':
				$where = 'BRANCH_MAP_ID = '.$post['bid'].' and PARTNER_MAP_ID_P = "'.$post['pid'].'" and PARTNER_LEVEL = 3 and PARTNER_STATUS = 0';
				$res = D('MPartner')->getPartner_select($where);
				break;
			case '4':
				$where = 'BRANCH_MAP_ID = '.$post['bid'].' and PARTNER_MAP_ID_P = "'.$post['pid'].'" and PARTNER_LEVEL = 4 and PARTNER_STATUS = 0';
				$res = D('MPartner')->getPartner_select($where);
				break;
		}
		$this->ajaxReturn($res);
	}

	/*
	* 集团子公司处理 模板发送ajax
	**/	
    public function ajaxgetcompany() {
    	$post = array(
			'pid'	=>	$_REQUEST['pid'],
			'flag'	=>	$_REQUEST['flag']
		);
		$where = 'BRANCH_MAP_ID = '.$post['pid'].' and PARTNER_G_FLAG = 2';
		//$res = D('MPartner')->getPlvsel($where,'PARTNER_MAP_ID_P,PARTNER_LEVEL');
		$res = D('MPartner')->getPartner_select($where);
		if (empty($res) && empty($post['flag'])) {
			$res[] = array('','暂无数据');
		}
		$this->ajaxReturn($res);
    }

    /*
	* 集团主商户处理 模板发送ajax
	**/	
    public function ajaxgetshop_p() {
    	$post = array(
			'pid'	=>	$_REQUEST['pid'],
			'flag'	=>	$_REQUEST['flag']
		);
		$where = 'BRANCH_MAP_ID = '.$post['pid'].' and SHOP_LEVEL = 2';
		$res = D('MShop')->getShop_select($where);
		if (empty($res) && empty($post['flag'])) {
			$res[] = array('','暂无数据');
		}
		$this->ajaxReturn($res);
    }

 	/*
	* 获取商户名称处理 模板发送ajax
	**/	
    public function ajaxgetshopname() {
    	$post = array(
			'shop_no'	=>	$_REQUEST['shop_no']
		);
		$where = 'SHOP_NO = "'.$post['shop_no'].'"';
		//$res = D('MPartner')->getPlvsel($where,'PARTNER_MAP_ID_P,PARTNER_LEVEL');
		$result = D('MShop')->findShop($where,'SHOP_NAME');
		$this->ajaxReturn($result);
    }

    /*
	* 获取通道名称处理 模板发送ajax
	**/	
    public function ajaxgethshopname() {
    	$post = array(
			'hshop_no'	=>	$_REQUEST['hshop_no']
		);
		$where = 'HSHOP_NO = "'.$post['hshop_no'].'"';
		//$res = D('MPartner')->getPlvsel($where,'PARTNER_MAP_ID_P,PARTNER_LEVEL');
		$result = D('MHshop')->findHshop($where,'HSHOP_NAME');
		$this->ajaxReturn($result);
    }

	/*
	* MCC分类处理 模板发送ajax
	**/	
    public function ajaxgetmcccode() {
		$post = array(
			'mcctype' => $_REQUEST['mcctype']
		);
		if (!empty($post['mcctype'])) {
			$res = D('MMcc')->getMcc_codelist($post['mcctype']);
		}else{
			$res[] = array('','请选择');
		}
		$this->ajaxReturn($res);
	}

	/*
	* 设备型号处理 模板发送ajax
	**/	
    public function ajaxgetmodel() {
		$post = array(
			'f_id'	=>	$_REQUEST['f_id']
		);
		$res = D('MModel')->getModellist('f.FACTORY_MAP_ID = '.$post['f_id'],'m.MODEL_MAP_ID,m.MODEL_NAME');
		$sel[] = array('','请选择');
		foreach ($res as $key => $value) {
			$sel[] = array($value['MODEL_MAP_ID'],$value['MODEL_NAME']);
		}
		$this->ajaxReturn($sel);
	}

	/*
	* 设备型号处理 select发送ajax
	**/	
    public function ajaxgetmodelsel() {
    	$post = array(
			'm_id'	=>	$_REQUEST['m_id'],
			'f_id'	=>	$_REQUEST['f_id']
		);
		echo D('MModel')->getModelsel('',$post['m_id'], $post['f_id']);
	}

	/*
	* 根据用户ID获得用户名称
	**/	
    public function getusername() {
    	$home = session('HOME');
		$post = array(
			'USER_NO'	=>	$_REQUEST['user_no']
		);
		$where = 'USER_NO = '.$post['USER_NO'].' and USER_STATUS = 0 and ROLE_ID = 6';
		$result = D('MUser')->findUser($where,'USER_NAME');
		if(!$result){
			$res = array(
				'state' => 1,
				'msg' 	=> '参数获取失败',
				'data' 	=> $result
			);
		}else{
			$res = array(
				'state' => 0,
				'msg' 	=> '参数获取成功',
				'data' 	=> $result
			);
		}
		$this->ajaxReturn($res);
	}	
	/*
	* 获取当前归属级别
	**/	
	public function getlevelsel(){
		$post = array(
			'bid'	=>	$_REQUEST['bid'],
			'pid'	=>	$_REQUEST['pid'],
			'flag'	=>	$_REQUEST['flag'] ? $_REQUEST['flag'] : 6	//1,合作方(地级子公司);2,合作方(区县服务中心); 3推广中心 4创业合伙人
		);
		return get_level_select($bid,$flag,$pid,$lvname);
	}
	/*
	* 代理商逐级归属(合作伙伴)
	**/	
	public function getpartner_sel(){
		$post = array(
			'BRANCH_MAP_ID'	=>	$_REQUEST['bid']
		);
		$result = D('MPartner')->getPartner_select($post['BRANCH_MAP_ID'],'PARTNER_MAP_ID,PARTNER_NAME');
		$this->ajaxReturn($result);
	}
	/*
	* 代理商逐级归属(商户)
	**/	
	public function getshop_sel(){
		$post = array(
			'PARTNER_MAP_ID'	=>	$_REQUEST['aid']
		);
		$result = D('MShop')->getShop_select($post['PARTNER_MAP_ID'],'SHOP_MAP_ID,SHOP_NAME');
		$this->ajaxReturn($result);
	}

	/*
	* 根据地区,银行编号或取联行号
	**/	
    public function getbid() {
		$post = array(
			'city_code'		=>	$_REQUEST['city_code'],	
			'issue_code'	=>	$_REQUEST['issue_code']
		);
		if (empty($post['city_code']) || empty($post['issue_code'])) {
			$this->ajaxResponse(1,'缺少银行代码和城市代码');
		}
		$where = 'CITY_S_CODE = "'.$post['city_code'].'" and ISSUE_CODE = "'.$post['issue_code'].'"';
		$data = D('MBid')->getBidlist($where,'BANK_BID,BANK_BNAME');
		if (!empty($data)) {
			$res = array(
				'state'  => 0,
				'msg' 	 => 'success',
				'data' 	 => $data
			);
		}
		$this->ajaxReturn($res);
	}	
	//处理web上传[投保]
    public function upload_file() {
    	$post = array(
			'userid'	=>	I('userid'),
			'keyword'	=>	I('keyword'),
			'type'		=>	I('type')
		);
		if(empty($post['userid']) || empty($post['keyword'])) {
			$this->ajaxResponse(1, '缺少上传秘钥');
		}
		$kwd = md5(md5( substr($post['userid'], -1, 1).'0000' ));
		if($kwd != $post['keyword']) {
			$this->ajaxResponse(1, '上传秘钥错误');
		}
		if(empty($_FILES)) {
            $this->ajaxResponse(1, '请上传文件');
        }
    	$path = './Public/file/upload/'.$post['userid'].'/'.date('Ymd').'/';
    	// 文件上传
		import("Org.Util.UploadFile");
        //导入上传类
        $upload = new \UploadFile();			// 实例化上传类
        $upload->maxSize   	=   3145728 ;		// 设置附件上传大小
        $upload->exts      	=   array('xls');	// 设置附件上传类型
       // $upload->rootPath  	=   $path; 		// 设置附件上传根目录
        $upload->savePath  	=   $path; 			// 设置附件上传（子）目录
        // 上传文件 
      	$info   =   $upload->upload();
        if(!$info) {
            $file_url = false;
        }else{
           	//取得成功上传的文件信息
            $uploadList = $upload->getUploadFileInfo();
			$file_url 	= $uploadList[0]['savepath'].$uploadList[0]['savename'];
        }

     	$in_res = $this->fcarddetail_include($file_url);
	   if (!$in_res) {
	      	 $res = array(
				'state'	=>	1,
				'msg'	=>	'文件上传失败'
			);
       }else{
       		//取得成功上传的文件信息
            $uploadList = $upload->getUploadFileInfo();
			$file_url = substr( $uploadList[0]['savepath'].$uploadList[0]['savename'], 1);
			$res = array(
				'state'	=>	0,
				'msg'	=>	'文件上传成功'
			);
       }
       $this->ajaxReturn($res);
    }
	//预免卡意外险投保导入
	public function fcarddetail_include($file_res,$type=1){
		if(!$file_res){
			return array('state'=>1, 'msg'=>'文件上传失败！');
		}
		//导入Excel
		set_time_limit(0);	
		vendor("PHPExcel180.PHPExcel");
		$file_name 	   = $file_res;
		$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($file_name, $encode='utf-8');
		$sheet 		   = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); 		// 取得总行数
		$highestColumn = $sheet->getHighestColumn(); 	// 取得总列数

		$Model = M('tbls', DB_PREFIX_TRA, DB_DSN_TRA);
		$success_num = 0;
		$total = $highestRow - 1;
		for($i=2; $i<=$highestRow; $i++){
			$TB_ID		 	= $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();	//保单号
			$TB_FLAG	 	= $objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();	//投保结果
			$TB_NO			= $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();	//保单号
			$ONLINE_FLAG	= $objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();	//投保方式
			if (empty($TB_ID) || empty($TB_FLAG)) {
				continue;
			}
			$TB_ID_1 	   	= trim($TB_ID);
			$where = 'TB_ID = '.$TB_ID_1.' and ONLINE_FLAG = 1';
			if($TB_FLAG == '成功' && $ONLINE_FLAG == '非在线'){
				$home = session('HOME');
				$updata = array(
					'TB_FLAG' => 0, 
					'TB_NO'	  => $TB_NO, 
					'TB_TIME' => date('YmdHis'), 
					'TB_DESC' => '线下导入投保成功'
				);
				$result = $Model->where($where)->save($updata);
				if($result === false) {
					continue;
				}
				$success_num++;
			}
		}
		return array('state'=>0, 'msg'=>'总计：'.$total.'条，失败：'.($total - $success_num));
	}
	//设备导入
	public function device_include($file_res){
		if(!$file_res){
			return array('state'=>1, 'msg'=>'文件上传失败！');
		}
		//导入Excel
		set_time_limit(0);	
		vendor("PHPExcel180.PHPExcel");
		$file_name 	   = $file_res;
		$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($file_name, $encode='utf-8');
		$sheet 		   = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); 		// 取得总行数
		$highestColumn = $sheet->getHighestColumn(); 	// 取得总列数
		
		$resdata = array();
		$Model   = M('device');
		$error_num = '0';
		$error_item = '';
		for($i=2; $i<=$highestRow; $i++){
			$FACTORY_MAP_ID	= $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
			$MODEL_MAP_ID 	= $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
			$DEVICE_SN		= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
			//验证
			if(empty($DEVICE_SN) || empty($FACTORY_MAP_ID) || empty($MODEL_MAP_ID)){
				continue;
			}
			$finddata = D($this->MDevice)->findDevice("d.DEVICE_SN='".$DEVICE_SN."'");
			if(!empty($finddata)){
				continue;
			}
			//调试
			$res_arr = array(
				'FACTORY_MAP_ID'	=>	$FACTORY_MAP_ID,
				'MODEL_MAP_ID'		=>	$MODEL_MAP_ID,
				'DEVICE_SN' 		=>	$DEVICE_SN,
				'DEVICE_STATUS'		=>	'2',
				'DEVICE_ATTACH'		=>	'2',
				'BRANCH_MAP_ID'		=>  '100000',
				'PARTNER_MAP_ID'	=>	'0',
				'SHOP_NO'			=>	'-',
				'POS_NO'			=>	'-',
				'POS_INDEX'			=>	$DEVICE_SN,
				'DEVICE_TOKEN'		=>	setStrzero('', 32, 'F'),
				'DEVICE_ADDRESS'	=>	'-',
				'INSTALL_DATE'		=>	'-',
				'CREATE_USERID'		=>	'',
				'CREATE_USERNAME'	=>	'',
				'CREATE_TIME'		=>	date("YmdHis")
			);
			$res = $Model->data($res_arr)->add();
			if (!$res) {
				$error_num++;
				$error_item .= $res_arr['DEVICE_SN'].'、';
			}
		}
		if ($error_num !=0) {
			return array('state'=>1, 'msg'=>'文件导入完成，失败'.$error_num.'个('.$error_item.')');
		}
		return array('state'=>0, 'msg'=>'文件导入成功');
	}

	//批量变更会员id
	public function update_vipid($file_res){
		if(!$file_res){
			return array('state'=>1, 'msg'=>'文件上传失败！');
		}
		//导入Excel
		set_time_limit(0);	
		vendor("PHPExcel180.PHPExcel");
		$file_name 	   = $file_res;
		$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($file_name, $encode='utf-8');
		$sheet 		   = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); 		// 取得总行数
		$highestColumn = $sheet->getHighestColumn(); 	// 取得总列数
		
		$resdata = array();
		$error_num = '0';
		$error_item = '';
		for($i=2; $i<=$highestRow; $i++){
			$OLD_ID	= $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
			$NEW_ID = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
			//1.APP申请加盟表[A_JOIN]
			$up_res = M('join')->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'A_JOIN');

			//2.交易流水表[T_TRACE/T_TRACE_HIS]
			$up_res = M('trace', DB_PREFIX_TRA, DB_DSN_TRA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_TRACE');
			$up_res = M('trace_his', DB_PREFIX_TRA, DB_DSN_TRA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_TRACE_HIS');

			//3.积分宝运营流水表[T_JFBLS/HIS]
			$up_res = M('jfbls', DB_PREFIX_TRA, DB_DSN_TRA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_JFBLS');
			$up_res = M('jfbls_his', DB_PREFIX_TRA, DB_DSN_TRA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_JFBLS_HIS');

			//4.积分宝卡费流水表[T_KFLS/HIS]
			$up_res = M('kfls', DB_PREFIX_TRA, DB_DSN_TRA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_KFLS');
			$up_res = M('kfls_his', DB_PREFIX_TRA, DB_DSN_TRA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_KFLS_HIS');
			
			//5.差错流水[T_ETRACE/HIS]
			/*$up_res = M('etrace', DB_PREFIX_TRA, DB_DSN_TRA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_ETRACE');
			$up_res = M('etrace_his', DB_PREFIX_TRA, DB_DSN_TRA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_ETRACE_HIS');*/

			//6.短信流水表[T_SMSLS/ HIS]
			$up_res = M('smsls', DB_PREFIX_TRA, DB_DSN_TRA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_SMSLS');
			$up_res = M('smsls_his', DB_PREFIX_TRA, DB_DSN_TRA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_SMSLS_HIS');

			//7.投保明细表[T_TBLS/HIS]
			$up_res = M('tbls', DB_PREFIX_TRA, DB_DSN_TRA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_TBLS');
			$up_res = M('tbls_his', DB_PREFIX_TRA, DB_DSN_TRA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_TBLS_HIS');

			//8.明细账表[T_DAP/HIS]
			$up_res = M('dap', DB_PREFIX_TRA, DB_DSN_TRA)->where('ACCT_NO = "'.$OLD_ID.'"')->save(array('ACCT_NO' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_DAP');
			$up_res = M('dap_his', DB_PREFIX_TRA, DB_DSN_TRA)->where('ACCT_NO = "'.$OLD_ID.'"')->save(array('ACCT_NO' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'T_DAP_HIS');

			//9.实体卡号信息表[K_VIPCARD/HIS]
			$up_res = M('vipcard', DB_PREFIX_GLA, DB_DSN_GLA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'K_VIPCARD');
			$up_res = M('vipcard_his', DB_PREFIX_GLA, DB_DSN_GLA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'K_VIPCARD_HIS');

			//10.会员信息表[K_VIP/HIS]
			$up_res = M('vip', DB_PREFIX_GLA, DB_DSN_GLA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'K_VIP');
			$up_res = M('vip_his', DB_PREFIX_GLA, DB_DSN_GLA)->where('VIP_ID = "'.$OLD_ID.'"')->save(array('VIP_ID' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'K_VIP_HIS');

			//11.会员分户账表[K_LAP]
			$up_res = M('lap', DB_PREFIX_GLA, DB_DSN_GLA)->where('ACCT_NO = "'.$OLD_ID.'"')->save(array('ACCT_NO' => $NEW_ID));
			updatevip_log($up_res,$OLD_ID,$NEW_ID,'K_LAP');
		}

		return array('state'=>0, 'msg'=>'修改，成功');
	}
	
	//批量修改梅露手动添卡时的校验码
	//批量变更会员id
	public function change_cardno($file_res){//change_cardno
		if(!$file_res){
			return array('state'=>1, 'msg'=>'文件上传失败！');
		}
		//导入Excel
		set_time_limit(0);	
		vendor("PHPExcel180.PHPExcel");
		$file_name 	   = $file_res;
		$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($file_name, $encode='utf-8');
		$sheet 		   = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); 		// 取得总行数
		$highestColumn = $sheet->getHighestColumn(); 	// 取得总列数
		
		$resdata = array();
		$error_num = '0';
		$error_item = '';
		for($i=2; $i<=$highestRow; $i++){
			$CARD_NO= $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
			$CARD_CHECK = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
			//批量修改会员卡的校验码
			$up_res = M('vipcard', DB_PREFIX_GLA, DB_DSN_GLA)->where('CARD_NO = "'.$CARD_NO.'" AND CARD_STATUS = 1')->save(array('CARD_CHECK' => $CARD_CHECK));
			updatevip_log($up_res,$CARD_NO,$CARD_CHECK,'K_VIPCARD_CHECKNO');
		}
		return array('state'=>0, 'msg'=>'修改，成功');
	}

	//批量注销会员
	public function clear_vip($file_res){	//clear_vip
		if(!$file_res){
			return array('state'=>1, 'msg'=>'文件上传失败！');
		}
		//导入Excel
		ini_set('memory_limit', '256M'); //内存限制
		set_time_limit(0);	
		vendor("PHPExcel180.PHPExcel");
		$file_name 	   = $file_res;
		$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($file_name, $encode='utf-8');
		$sheet 		   = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); 		// 取得总行数
		$highestColumn = $sheet->getHighestColumn(); 	// 取得总列数
		
		$m = M('', DB_PREFIX_GLA, DB_DSN_GLA);
		for($i=1; $i<=$highestRow; $i++){
			$name = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
			//$mobile = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
			$name_c = trim($name,"'");
			//$mobile_c = trim($mobile,"'");
			$info = D($this->GVip)->findNewsVip("CARD_NO = '".$name_c."'");
			if (empty($name_c) || empty($info['VIP_ID'])) {
				continue;
			}

			//改 vip 表
			$m->startTrans();	//启用事务
			$upvipdata = array(
				'VIP_STATUS' => '9', 
				'VIP_MOBILE' => '9'.$info['VIP_ID'], 
				'CARD_NO' 	 => '9'.$info['VIP_ID'], 
				'VIP_IDNO' 	 => '9'.$info['VIP_ID']
			);
			//注销会员
			$up_res = M('vip', DB_PREFIX_GLA, DB_DSN_GLA)->where("CARD_NO = '".$info['CARD_NO']."'")->save($upvipdata);
			updatevip_log($up_res,$info['VIP_ID'],$name_c.'= 9','K_VIP_CLEAR');

			//注销卡
			if($info['CARD_NO']){
				$up_res = M('vipcard', DB_PREFIX_GLA, DB_DSN_GLA)->where("CARD_NO = '".$info['CARD_NO']."'")->save(array('CARD_STATUS'=>9,'UPDATE_TIME' => date('YmdHis')));
				updatevip_log($up_res,$info['VIP_ID'],$name_c.'= 9','K_VIPCARD_CLEAR');
			}

			//同步修改会员数据
			$url = VIP_PUSH_URL.'api/open/synchronize/member/logout';
			$data = array(
				'token' 	 	 => strtoupper(md5(strtoupper(md5($id.'3')))),	//(签名验证)
				'mId' 	 		 => $id,							//(会员ID)
				'operateType'	 => '3'								//(操作类型)
			);
			Add_LOG(CONTROLLER_NAME);
			Add_LOG(CONTROLLER_NAME, json_encode($data));
			$resjson = httpPostForm($url,$data);
			Add_LOG(CONTROLLER_NAME, $resjson);
			$result = json_decode($resjson);
			if ($result->code != '0') {
				$m->rollback();	//回滚
			}
			$m->commit();	//成功
		}
		return array('state'=>0, 'msg'=>'修改，成功');
	}
	//批量修改代扣
	public function change_sdkb($file_res){//change_sdkb
		if(!$file_res){
			return array('state'=>1, 'msg'=>'文件上传失败！');
		}
		//导入Excel
		set_time_limit(0);	
		vendor("PHPExcel180.PHPExcel");
		$file_name 	   = $file_res;
		$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($file_name, $encode='utf-8');
		$sheet 		   = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); 		// 取得总行数
		$highestColumn = $sheet->getHighestColumn(); 	// 取得总列数
		
		$resdata = array();
		$error_num1 = '0';	//银盛
		$error_num2 = '0';	//银联
		$msdkb = M('sdkb');
		$mshop = D($this->MShop);
		$mtdkls= D($this->TDkls);
		$Model = M('sdkb'); 							// 实例化一个model对象 没有对应任何数据表
		$mdkls = M('dkls', DB_PREFIX_TRA, DB_DSN_TRA); // 实例化一个model对象 没有对应任何数据表
		for($i=1; $i<=$highestRow; $i++){
			$SHOP_ID = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
			$DKCO_MAP_ID = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
			
			//变更商户代扣
			$sql = "update a_sdkb set DKCO_MAP_ID = '".$DKCO_MAP_ID."' where SHOP_MAP_ID = '".$SHOP_ID."' limit 1";
			$sdkb_res1 = $Model->execute($sql);

			//查询商户信息
			$sql3 = "select * from a_shop where SHOP_MAP_ID = '".$SHOP_ID."'";
			$sdkb_res = $Model->query($sql3);
			//变更代扣流水
			$sql2 = "update t_dkls set DKCO_MAP_ID = '".$DKCO_MAP_ID."' where SHOP_NO = '".$sdkb_res['0']['SHOP_NO']."'";
			$dkls_res = $mdkls->execute($sql2);
			
			if ($DKCO_MAP_ID == 1) {
				$error_num1++;
			}else{
				$error_num2++;
			}
		}
		$content = '已经成功修改银盛'.$error_num1.'个商户';
   		Add_LOG('update_sdkb_log', $content);
   		$content2 = '已经成功修改银联'.$error_num2.'个商户';
   		Add_LOG('update_sdkb_log', $content2);
		return array('state'=>0, 'msg'=>'修改，成功'.($error_num1+$error_num2));
	}
	

	//批量修改卡归属0802
	public function change_guishu($file_res){//change_guishu
		if(!$file_res){
			return array('state'=>1, 'msg'=>'文件上传失败！');
		}
		//导入Excel
		ini_set('memory_limit', '256M'); //内存限制
		set_time_limit(0);	
		vendor("PHPExcel180.PHPExcel");
		$file_name 	   = $file_res;
		$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($file_name, $encode='utf-8');
		$sheet 		   = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); 		// 取得总行数
		$highestColumn = $sheet->getHighestColumn(); 	// 取得总列数
		
		$resdata = array();
		$error_num = '0';
		$error_item = '';
		for($i=2; $i<=$highestRow; $i++){
			$CARD_NO1= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
			//$PID1 = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
			
			$CARD_NO = trim($CARD_NO1,"'");
			//$PID = trim($PID1,"'");
			//$BID = M('partner')->where('PARTNER_MAP_ID = "'.$PID.'"')->find();
			$PID = '59728';
			$BID['BRANCH_MAP_ID'] = '4572';
			if (empty($CARD_NO) || empty($PID) || empty($BID['BRANCH_MAP_ID'])) {
				continue;
			}
			//批量修改会员卡的校验码
			$data = array(
				'BRANCH_MAP_ID' => $BID['BRANCH_MAP_ID'],
				'BRANCH_MAP_ID1' => $BID['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID' => $PID,
				'PARTNER_MAP_ID1' => $PID,
			);
			$up_res = M('vipcard', DB_PREFIX_GLA, DB_DSN_GLA)->where('CARD_NO = "'.$CARD_NO.'"')->save($data);
			updatevip_log($up_res,$CARD_NO,$BID['BRANCH_MAP_ID'].'='.$PID,'K_VIPCARD_GUISHU');
			$data2 = array(
				'BRANCH_MAP_ID' => $BID['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID' => $PID,
			);
			$up_res = M('vip', DB_PREFIX_GLA, DB_DSN_GLA)->where('CARD_NO = "'.$CARD_NO.'"')->save($data2);
			updatevip_log($up_res,$CARD_NO,$BID['BRANCH_MAP_ID'].'='.$PID,'K_VIP_GUISHU');
		}
		return array('state'=>0, 'msg'=>'修改，成功');
	}

	//批量商户同步
	public function sync_shop_more($file_res){//sync_shop_more
		if(!$file_res){
			return array('state'=>1, 'msg'=>'文件上传失败！');
		}
		//导入Excel
		ini_set('memory_limit', '256M'); //内存限制
		set_time_limit(0);	
		vendor("PHPExcel180.PHPExcel");
		$file_name 	   = $file_res;
		$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($file_name, $encode='utf-8');
		$sheet 		   = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); 		// 取得总行数
		$highestColumn = $sheet->getHighestColumn(); 	// 取得总列数
	

		$n_ok = 0;
		$n_no = 0;
		//return array('state'=>0, 'msg'=>'共计'.$highestRow);
		echo '<h1>共计 '.($highestRow-1).' 个商户！</h1>';
		for($i=2; $i<=$highestRow; $i++){
			$SHOP_MAP_ID = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
			$sync_res = shop_sync_data($SHOP_MAP_ID,1);
			if (!$sync_res) {
				echo '<h1>商户进件数据同步失败</h1>';
				$n_no++;
			}else{
				echo '<h1>商户进件数据同步成功</h1>';
				$n_ok++;
			}
		}
		echo '<h1>共计 '.($highestRow-1).' 个商户！成功：'.$n_ok.'个，失败：'.$n_no.'个</h1>';
	}

	/*
	* 文件导入
	**/
	public function uploadComeFile(){
		$post = array(
			'userid'	=>	I('userid'),
			'keyword'	=>	I('keyword'),
			'type'		=>	I('type'),
			'action'	=>	I('action'),
		);
		if(empty($post['userid']) || empty($post['keyword'])) {
			$this->ajaxResponse(1, '缺少上传秘钥！');
		}
		$kwd = md5(md5( substr($post['userid'], -1, 1).'0000' ));
		if($kwd != $post['keyword']) {
			$this->ajaxResponse(1, '上传秘钥错误！');
		}
		if(empty($_FILES)) {
            $this->ajaxResponse(1, '请上传文件！');
        }
    	$path = './Public/file/upload/'.$post['userid'].'/'.date('Ymd').'/';
		Mk_Folder($path);
		
    	// 文件上传
		import("Org.Util.UploadFile");
        $upload = new \UploadFile();			// 实例化上传类
        $upload->maxSize  = 3145728 ;			// 设置附件上传大小
        $upload->exts     = array('xls');		// 设置附件上传类型
        $upload->savePath = $path; 				// 设置附件上传（子）目录
		if(!$upload->upload()){
			$this->ajaxResponse(1, $upload->getErrorMsg());
		}
		//取得成功上传的文件信息
		$uploadList = $upload->getUploadFileInfo();
		$file_url 	= $uploadList[0]['savepath'].$uploadList[0]['savename'];
				
		$res = $this->$post['action']($file_url);
		if($res['state'] != 0){
			$this->ajaxResponse(1, $res['msg']);
		}
		$this->ajaxReturn($res);
	}
	//黑名单卡导入
	public function upload_crisk($file_res){
		if(!$file_res){
			return array('state'=>1, 'msg'=>'文件上传失败！');
		}
		//导入Excel
		set_time_limit(0);	
		vendor("PHPExcel180.PHPExcel");
		$file_name 	   = $file_res;
		$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($file_name, $encode='utf-8');
		$sheet 		   = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); 		// 取得总行数
		$highestColumn = $sheet->getHighestColumn(); 	// 取得总列数
		
		$resdata = array();
		$Model   = M('crisk');
		for($i=2; $i<=$highestRow; $i++){
			$CARD_NO		= $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
			$CARD_EXP 		= $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
			$ISSUE_CODE		= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
			$ISSUE_NAME 	= $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
			$CREATE_DATE 	= $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
			$CARDBLACK_FLAG	= $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
			$CARDBLACK_DESC	= $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();				
			$count = $Model->where("CARD_NO='".$CARD_NO."'")->count();
			if($count == 0){
				//处理数据
				$resdata[] = array(
					'CARD_NO'			=>	$CARD_NO,
					'ISSUE_CODE'		=>	$ISSUE_CODE,
					'ISSUE_NAME'		=>	$ISSUE_NAME,
					'CARD_EXP'			=>	$CARD_EXP,
					'CARDBLACK_FLAG'	=>	$CARDBLACK_FLAG,
					'CREATE_DATE'		=>	$CREATE_DATE,
					'CARDBLACK_DESC'	=>	$CARDBLACK_DESC,
				);
			}
		}
		if(!empty($resdata)){
			$result = $Model->addAll($resdata);
			if($result === false) {
				return array('state'=>1, 'msg'=>'文件入库失败！');
			}
		}
		return array('state'=>0, 'msg'=>!empty($resdata) ? '文件导入成功！' : '无数据可导入!');
	}
	//代扣流水 钱宝数据导入
	public function upload_qbls($file_res){
		if(!$file_res){
			return array('state'=>1, 'msg'=>'文件上传失败！');
		}
		//导入Excel
		set_time_limit(0);	
		vendor("PHPExcel180.PHPExcel");
		$file_name 	   = $file_res;
		$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($file_name, $encode='utf-8');
		$sheet 		   = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); 		// 取得总行数
		$highestColumn = $sheet->getHighestColumn(); 	// 取得总列数
		
		$resdata = array();
		$Model   = M('qbls', DB_PREFIX_TRA, DB_DSN_TRA);
		for($i=3; $i<=$highestRow; $i++){
			$CARD_NO		= $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
			$TRADE_ID 		= $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
			$TRADE_NAME		= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
			$TRADE_STATUS 	= $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
			$TRADE_AMT 		= $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
			$HAND_AMT		= $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
			$ADDIT_AMT		= $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
			$SHOP_NAME		= $objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
			$POS_NO 		= $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
			$TRADE_TIME		= $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
			$TRADE_REF 		= $objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
			$JFB_REF 		= $objPHPExcel->getActiveSheet()->getCell("L".$i)->getValue();
			$ACCOUNT_STATUS	= $objPHPExcel->getActiveSheet()->getCell("M".$i)->getValue();
			$ACCOUNT_TIME	= $objPHPExcel->getActiveSheet()->getCell("N".$i)->getValue();	
			$ACCOUNT_BATCH	= $objPHPExcel->getActiveSheet()->getCell("O".$i)->getValue();				
			$count = $Model->where("TRADE_ID='".$TRADE_ID."'")->count();
			if($count == 0){
				//处理数据
				$resdata[] = array(
					'CARD_NO'		=>	$CARD_NO,
					'TRADE_ID'		=>	$TRADE_ID,
					'TRADE_NAME'	=>	$TRADE_NAME,
					'TRADE_STATUS'	=>	$TRADE_STATUS,
					'TRADE_AMT'		=>	$TRADE_AMT,
					'HAND_AMT'		=>	$HAND_AMT,
					'ADDIT_AMT'		=>	$ADDIT_AMT,
					'SHOP_NAME'		=>	$SHOP_NAME,
					'POS_NO'		=>	$POS_NO,
					'TRADE_TIME'	=>	$TRADE_TIME 	? date('YmdHis',strtotime($TRADE_TIME)) 	: '',
					'TRADE_REF'		=>	$TRADE_REF,
					'JFB_REF'		=>	$JFB_REF,
					'ACCOUNT_STATUS'=>	$ACCOUNT_STATUS,
					'ACCOUNT_TIME'	=>	$ACCOUNT_TIME 	? date('YmdHis',strtotime($ACCOUNT_TIME))	: '',
					'ACCOUNT_BATCH'=>	$ACCOUNT_BATCH,
				);
			}
		}
		if(!empty($resdata)){
			$result = $Model->addAll($resdata);
			if($result === false) {
				return array('state'=>1, 'msg'=>'文件入库失败！');
			}
		}
		return array('state'=>0, 'msg'=>!empty($resdata) ? '文件导入成功！' : '无数据可导入!');
	}
	
	//处理web上传
    public function uploadimgs() {
		$post = array(
			'userid'=>		$_REQUEST['userid'],
			'keyword'=>		$_REQUEST['keyword']
		);
		if(empty($post['userid']) || empty($post['keyword'])) {
			$this->ajaxResponse(1, '缺少上传秘钥');
		}
		$kwd = md5(md5( substr($post['userid'], -1, 1).'0000' ));
		if($kwd != $post['keyword']) {
			$this->ajaxResponse(1, '上传秘钥错误');
		}
		if(empty($_FILES)) {
            $this->ajaxResponse(1, '请上传图片');
        }
		/* if($_FILES['filedata']['size'] > 1024) {
            $this->ajaxResponse(1, '图片太大啦！最大支持 1MB');
        } */
		
		//检查目录
		$path = './Public/file/upload/'.$post['userid'].'/'.date('Ymd').'/';
		Mk_Folder($path);
		
		//获取图片类型
		$name = uniqid();
		$arra = explode(".", $_FILES['filedata']['name']);
		$type = $arra[count($arra)-1];		
		
		$filetemp = $_FILES['filedata']['tmp_name']; 					//文件临时目录
		$filepath = $path.$name.'.'.$type;
		if(move_uploaded_file($filetemp, $filepath)){					//将文件从临时目录移动到保存目录
			$res = array(
				'state'=>	0,
				'msg'=>		'图片上传成功',
				'name'=>	$name,
				'type'=>	$type,
				'image'=>	$filepath,
				'result'=>	C('_URL').substr($filepath, 1),
				'size'=>	$_FILES['filedata']['size'],
				'data'=>	$_FILES
			);
			$this->ajaxReturn($res);
		}else{
			$this->ajaxResponse(1, '图片上传失败');
		}
	}
	//处理web上传
    public function uploadimgcut() {
		$post = array(
			'userid'=>		I('userid'),
			'keyword'=>		I('keyword'),
			'image'=>		I('image'),
			'name'=>		I('name'),
			'type'=>		I('type'),
			'x'=>			I('x'),
			'y'=>			I('y'),
			'w'=>			I('w'),
			'h'=>			I('h')
		);
		if(empty($post['userid']) || empty($post['keyword'])) {
			$this->ajaxResponse(1, '缺少上传秘钥');
		}
		$kwd = md5(md5( substr($post['userid'], -1, 1).'0000' ));
		if($kwd != $post['keyword']) {
			$this->ajaxResponse(1, '上传秘钥错误');
		}
		if(empty($post['image'])) {
            $this->ajaxResponse(1, '请上传图片');
        }
		if($post['w'] <100 || $post['h'] <100){
			$this->ajaxResponse(1, '图片太小啦！尺寸不小于 100*100');
		}
		
		//检查目录
		$path = './Public/file/upload/'.$post['userid'].'/'.date('Ymd').'/';
		Mk_Folder($path);
		
		//创建图片	
		$image = new \Think\Image();
		$image->open($post['image']);
		$image->crop($post['w'], $post['h'], $post['x'], $post['y']);
		$m_img = $path.'m_'.$post['name'].'.'.$post['type'];		//400
		$s_img = $path.'s_'.$post['name'].'.'.$post['type'];		//100
		$image->thumb(400, 400)->save($m_img);
		$image->thumb(100, 100)->save($s_img);
		
		$res = array(
			'state'		=>	0,
			'msg'		=>	'图片上传成功',
			'result'	=>	C('_URL').substr($s_img, 1)
		);
		$this->ajaxReturn($res);
	}
	
	/*
	* 上传图片 - 公共接口
	* @post:
	*	userid		用户id
	*	keyword		秘钥
	**/
    public function uploadimg() {
		$post = array(
			'userid'=>		I('userid'),
			'keyword'=>		I('keyword'),
			'type'=>		I('type'),			//类型
		);
		if(empty($post['userid']) || empty($post['keyword'])) {
			$this->ajaxResponse(1, '缺少上传秘钥');
		}
		$kwd = md5(md5( substr($post['userid'], -1, 1).'0000' ));
		if($kwd != $post['keyword']) {
			$this->ajaxResponse(1, '上传秘钥错误');
		}
		if(empty($_FILES)) {
            $this->ajaxResponse(1, '请上传文件');
        }
		
		// 文件上传
		import("Org.Util.UploadFile");
		import("Org.Util.Image"); 	//生成略缩图时需要
        //导入上传类
        $upload = new \UploadFile();
        //设置上传文件大小
        $upload->maxSize            = 3292200;
        //设置上传文件类型
        $upload->allowExts          = explode(',', 'jpg,gif,png,jpeg');
        //设置附件上传目录
        $upload->savePath           = "./Public/file/upload/".$post['userid']."/";
        //设置需要生成缩略图，仅对图像文件有效
        $upload->thumb              = true;
        // 设置引用图片类库包路径
        $upload->imageClassPath     = '@.ORG.Image';
        //设置需要生成缩略图的文件后缀
        $upload->thumbPrefix        = 'm_,s_';  //生产2张缩略图
        //设置缩略图最大宽度
        $upload->thumbMaxWidth      = '400,100';
        //设置缩略图最大高度
        $upload->thumbMaxHeight     = '400,100';
        //设置上传文件规则
		$upload->autoSub = true;
		$upload->subType = 'date';
		$upload->dateFormat = 'Ymd';		
        //删除原图
        $upload->thumbRemoveOrigin  = false;
        if (!$upload->upload()) {
            //捕获上传异常
			$res = array(
				'state'=>1,
				'msg'=>$upload->getErrorMsg()
			);
        } else {
            //取得成功上传的文件信息
            $uploadList = $upload->getUploadFileInfo();
            //给m_缩略图添加水印, Image::water('原文件名','水印图片地址')
            //\Image::water($uploadList[0]['savepath'] . 'm_' . $uploadList[0]['savename'], './Public/home/images/logo.png');
			$url = substr( $uploadList[0]['savepath'].$uploadList[0]['savename'], 1);
			//默认s100x100  m400x400  o原图
			switch($post['type']){
				case 'm':
					$imgurl = C('_URL').get_climg($url,'m');
					break;
				case 'o':
					$imgurl = C('_URL').get_climg($url);
					break;
				default:
					$imgurl = C('_URL').get_climg($url,'s');
			}			
			$res = array(
				'state'=>	0,
				'msg'=>		'图片上传成功',
				'result'=>	$imgurl
			);
        }
		$this->ajaxReturn($res);
	}

	/*
	* 图片弹框现实模板 - 公共接口
	* @post:
	*	userid		用户id
	*	keyword		秘钥
	**/
    public function image_show() {
    	$img_url = I('img_url');
    	$this->assign('img_url',get_climg($img_url));
    	$this->display();
    }
	
	
		
	/*
	* 获取通道商户名称
	* @post:
	*	host_id		通道id
	*	hshop_no	通道商户编号
	**/
	public function gethshop_data() {
    	$host_id  = I('host_id');
    	$hshop_no = I('hshop_no');
		if(empty($host_id) || empty($hshop_no)){
			$this->ajaxResponse(1, '缺少参数！');
		}		
		$finddata = D('MHshop')->findHshop("HOST_MAP_ID='".$host_id."' and HSHOP_NO='".$hshop_no."'", 'HSHOP_NAME');
		if(empty($finddata)){
			$this->ajaxResponse(1, '该通道下的商户编号不存在！');
		}
		$this->ajaxResponse(0, '获取成功！', $finddata);
    }
	
	
	/*
	* 根据卡号获取卡套餐类型和卡费
	* @post:
	*	card_no		卡号码
	**/
	public function getvipcard_data() {
    	$card_no  = I('card_no');
		if(empty($card_no)){
			$this->ajaxResponse(1, '缺少参数！');
		}
		$vipcard = D('GVipcard')->findVipcard("CARD_NO = '".$card_no."' and CARD_STATUS=1", 'CARD_P_MAP_ID,BRANCH_MAP_ID,PARTNER_MAP_ID');
		if(empty($vipcard)){
			$this->ajaxResponse(1, '该卡号不存在或已绑定！');
		}
		$cproduct = D('MCproduct')->findCproduct_one("CARD_P_MAP_ID = '".$vipcard['CARD_P_MAP_ID']."'", 'CARD_NAME');
		if(empty($cproduct)){
			$this->ajaxResponse(1, '该卡号的卡套餐不存在，请先分配卡套餐！');			
		}
		$feecfg = D('MFeecfg')->findFeecfg("CFG_FLAG = '".$vipcard['CARD_P_MAP_ID']."'", 'CARD_OPENFEE');
		if(empty($feecfg)){
			$this->ajaxResponse(1, '该卡套餐收费分润不存在！');			
		}
		$res = array(
			'CARD_P_MAP_ID'	=>	$vipcard['CARD_P_MAP_ID'],
			'BRANCH_MAP_ID'	=>	$vipcard['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'=>	$vipcard['PARTNER_MAP_ID'],
			'CARD_NAME'		=>	$cproduct['CARD_NAME'],
			'CARD_OPENFEE'	=>	$feecfg['CARD_OPENFEE']>0 ? setMoney($feecfg['CARD_OPENFEE'], '2', '2') : 0,
		);
		$this->ajaxResponse(0, '获取成功！', $res);
    }
	
	
	/*
	* 根据卡号获取 母卡卡id
	* @post:
	*	card_no		卡号码
	**/
	public function getvipcard_mdata() {
		$card_no  = I('card_no');
		if(empty($card_no)){
			$this->ajaxResponse(1, '缺少参数！');
		}
		$vipcard = D('GVipcard')->findVipcard("CARD_NO = '".$card_no."' and CARD_STATUS=0", 'VIP_ID');
		if(empty($vipcard)){
			$this->ajaxResponse(1, '该卡号不存在或已绑定！');
		}
		$this->ajaxResponse(0, '获取成功！', $vipcard);		
	}
	
	
	/*
	* 银盛代扣接口
	* 供张华夜间调用
	**/
	public function ysmorepay() {
		if(CON_ENVIRONMENT != 'online'){
			echo '非生产环境不能提交代扣！';
			exit;
		}
		set_time_limit(0);			
		include_once(C('ChinaPay')['url_netpay']);
		vendor('Alipay.YinSheng');
		
		//查询
		$sellist = D($this->TDkls)->getDklsgrouplist("DK_FLAG != 0 and DK_ORDER_ID > 0", 'DK_ORDER_ID', '', 'DK_ORDER_ID', 'DK_ORDER_ID desc');
		if(!empty($sellist)) {
			for($i = 0; $i<count($sellist); $i++){
				$group = array();
				$field = 'DKCO_MAP_ID,DK_ORDER_ID,DK_DATE,sum(DK_AMT) as DK_AMT';
				$group = D($this->TDkls)->findNewsDkls("DK_ORDER_ID = '".$sellist[$i]['DK_ORDER_ID']."'", $field);
				
				$res   = array();
				$state = '';
				//ChinaPay
				if($group['DKCO_MAP_ID'] == 2){
					$merid = buildKey(C('ChinaPay')['pri_key']);
					if(!$merid) {
						continue;
					}
					//组装数据
					$resdata = array(
						'merId'			=>	$merid,										//商户号
						'transType'		=>	'0003',										//交易类型
						'orderNo'		=>	$group['DK_ORDER_ID'],						//订单号 	16位
						'transDate'		=>	$group['DK_DATE'],							//商户日期
						'version'		=>	'20100831',									//版本号
						'priv1'			=>	'\u5929',									//私有域
					);
					//计算签名值
					$plain 	  = $resdata['merId'].$resdata['transType'].$resdata['orderNo'].$resdata['transDate'].$resdata['version'];
					$chkvalue = sign(base64_encode($plain));
					if(!$chkvalue) {
						continue;
					}
					$resdata['chkValue'] = $chkvalue;
					$res   = httpPostForm(C('ChinaPay')['url_sel_pay'], $resdata);
					$res   = str_replace('=', '":"', $res);
					$res   = str_replace('&', '","', $res);
					$res   = '{"'.$res.'"}';
					$res   = json_decode($res, true);
					$state = $res['responseCode'];				
					if($state != '00') {
						continue;
					}
				}
				//银盛
				elseif($group['DKCO_MAP_ID'] == 1){
					//组装数据			
					$resdata = array(
						'Ver'			=>	'1.0',											//版本号
						'MsgCode'		=>	'S5001',										//报文编号
						'Time'			=>	date('YmdHis'),									//发送时间
						'OrderId'		=>	$group['DK_ORDER_ID'],							//订单号
						'TradeSN'		=>	'',												//交易流水
					);
					$Pay   = new \YinSheng(C('YinshengPay'));
					$xml   = $Pay->sel_one_tmp($resdata);		//组报文
					$sign  = $Pay->setencrypt($xml);			//签名加密
					$res   = $Pay->httpysdata($resdata['MsgCode'], $resdata['OrderId'], $sign);
					$state = $res['Order']['State'];				
					if($state != '00') {
						continue;
					}
				}
				
				//查询，返回成功，说明确实存在已扣款，未修改状态
				if($state == '00') {
					//获取手续费
					$dk_sxf	   = 0;
					$dkcodata  = D($this->MDkco)->findDkco("DKCO_MAP_ID = '".$group['DKCO_MAP_ID']."'", 'DKCO_NAME,DKCO_FEE_FLAG,DKCO_FEE_FIX,DKCO_FEE_PER');
					switch($dkcodata['DKCO_FEE_FLAG']){
						case '0':	//每笔固定金额
							$dk_sxf = $dkcodata['DKCO_FEE_FIX'];
						break;
						case '1':	//每笔百分比
							$dk_sxf = $group['DK_AMT'] * $dkcodata['DKCO_FEE_PER'] / 100;
						break;
					}
					
					//分摊列表
					$fentanlist	= D($this->TDkls)->getNewsDklslist("DK_ORDER_ID = '".$group['DK_ORDER_ID']."'", 'DK_ID');
					
					//分摊手续费
					$fen_cnt	= count($fentanlist);
					$fen_qiuyu 	= $dk_sxf % $fen_cnt;
					$fen_one  	= floor($dk_sxf / $fen_cnt);
					
					//写log
					Add_LOG('Dk');
					Add_LOG('Dk', '查询成功：确实存在已扣款，未修改状态。代扣订单：'.$group['DK_ORDER_ID']);
					Add_LOG('Dk', '查询结果：银行返回数据：'.json_encode($res));
					Add_LOG('Dk', '代扣银行：'.$dkcodata['DKCO_NAME']);
					
					//开始修改状态
					foreach($fentanlist as $key=>$val){
						$dk_fee = $fen_one;
						if($key == ($fen_cnt - 1)) {
							$dk_fee = $fen_one + $fen_qiuyu;
						}
						$updata = array(
							'DK_FEE'	=>	$dk_fee,
							'RES'		=>	'批量代扣成功！【查询修改】',
							'DK_FLAG'	=>	0,
						);
						D($this->TDkls)->updateDkls("DK_ID = '".$val['DK_ID']."'", $updata);				
					}
					
					Add_LOG('Dk', '状态、手续费修改成功！');
				}
			}
		}
		
		Add_LOG('Dk');
		Add_LOG('Dk', '开始批量代扣...');
				 
		//代扣
		$dklist = D($this->TDkls)->getDklsgrouplist("DK_FLAG != 0", 'BANKACCT_NO', '', 'BANKACCT_NO');
		if(empty($dklist)) {
			echo '无代扣数据！';
			exit;
		}
		
		for($j = 0; $j<count($dklist); $j++){
			$group = array();
			$field = 'BANKACCT_NO,BANKACCT_NAME,sum(DK_AMT) as DK_AMT,SHOP_NO,BANK_BID,DKCO_MAP_ID,DK_IDNO_TYPE,SHOP_ACCT_FLAG,DK_IDNO,BANK_NAME,MOBILE,BRANCH_MAP_ID,PARTNER_MAP_ID,SHOP_NAME';
			$group = D($this->TDkls)->findNewsDkls("DK_FLAG != 0 and BANKACCT_NO = '".$dklist[$j]['BANKACCT_NO']."'", $field);
			
			//检查必须项
			if(empty($group['BANKACCT_NO']) || empty($group['BANKACCT_NAME']) || empty($group['DK_AMT'])){
				$updata = array(
					'DK_DATE'		=>	date('Ymd'),
					'DK_TIME'		=>	date('His'),
					'RES'			=>	'代扣数据不完整！',
				);
				D($this->TDkls)->updateDkls("DK_FLAG != 0 and BANKACCT_NO = '".$group['BANKACCT_NO']."'", $updata);
				continue;
			}
			
			//检查bid
			if(empty($group['BANK_BID'])){
				$updata = array(
					'DK_DATE'		=>	date('Ymd'),
					'DK_TIME'		=>	date('His'),
					'RES'			=>	'缺少银行BID！',
				);
				D($this->TDkls)->updateDkls("DK_FLAG != 0 and BANKACCT_NO = '".$group['BANKACCT_NO']."'", $updata);
				continue;
			}
			
			//订单号
			$order_id = substr(getmicrotime(), 2);	//16位
			
			$res     = array();
			$state   = '';
			$traceNo = '';
			//ChinaPay
			if($group['DKCO_MAP_ID'] == 2){
				//检查证件号
				if(empty($group['DK_IDNO'])){
					$updata = array(
						'DK_DATE'		=>	date('Ymd'),
						'DK_TIME'		=>	date('His'),
						'RES'			=>	'缺少证件号码！',
					);
					D($this->TDkls)->updateDkls("DK_FLAG != 0 and BANKACCT_NO = '".$group['BANKACCT_NO']."'", $updata);
					continue;
				}
				
				$merid = buildKey(C('ChinaPay')['pri_key']);
				if(!$merid) {
					continue;
				}
				//处理证件类型
				switch($group['DK_IDNO_TYPE']){
					case '0':
						$idno_type = '01';
						break;
					case '1':
						$idno_type = '03';
						break;
					case '2':
						$idno_type = '02';
						break;
					case '3':
						$idno_type = '05';
						break;
					default:
						$idno_type = '06';
				}
				$bankacct_name = json_encode($group['BANKACCT_NAME']);
				$bankacct_name = substr($bankacct_name, 1);
				$bankacct_name = substr($bankacct_name, 0, -1); 
				//组装数据
				$resdata = array(
					'merId'			=>	$merid,										//商户号
					'transDate'		=>	date('Ymd'),								//商户日期
					'orderNo'		=>	$order_id,									//订单号 	16位
					'transType'		=>	'0003',										//交易类型
					'openBankId'	=>	'0'.substr($group['BANK_BID'],0,3),			//开户行号
					'cardType'		=>	$group['SHOP_ACCT_FLAG'] ? $group['SHOP_ACCT_FLAG'] : 0,	//卡折标志
					'cardNo'		=>	$group['BANKACCT_NO'],						//卡号/折号
					'usrName'		=>	$bankacct_name,								//持卡人姓名
					'certType'		=>	$idno_type,									//证件类型
					'certId'		=>	$group['DK_IDNO'],							//证件号
					'curyId'		=>	'156',										//币种
					'transAmt'		=>	$group['DK_AMT'],							//金额
					'purpose'		=>	'',											//用途
					'priv1'			=>	'\u5929',									//私有域
					'version'		=>	'20151207',									//版本号
					'gateId'		=>	'7008',										//网关号
					'termType'		=>	'07',										//渠道类型
					'payMode'		=>	'1',										//交易模式
				);
				//计算签名值
				$plain 	  = $resdata['merId'].$resdata['transDate'].$resdata['orderNo'].$resdata['transType'].$resdata['openBankId'].$resdata['cardType'].$resdata['cardNo'].$resdata['usrName'].$resdata['certType'].$resdata['certId'].$resdata['curyId'].$resdata['transAmt'].$resdata['priv1'].$resdata['version'].$resdata['gateId'].$resdata['termType'].$resdata['payMode'];
				$chkvalue = sign(base64_encode($plain));
				if(!$chkvalue) {
					continue;
				}
				$resdata['chkValue'] = $chkvalue;
				$res   = httpPostForm(C('ChinaPay')['url_pay'], $resdata);
				$res   = str_replace('=', '":"', $res);
				$res   = str_replace('&', '","', $res);
				$res   = '{"'.$res.'"}';
				$res   = json_decode($res, true);
				$state = $res['responseCode'];				
				if($state != '00') {
					Add_LOG('Dk');
					Add_LOG('Dk', 'ChinaPay - 代扣失败，订单id：'.$order_id.'，原因：'.$res['message']);
					Add_LOG('Dk', '代扣发送数据：'.json_encode($resdata));
					Add_LOG('Dk', '银行返回数据：'.json_encode($res));
					
					$updata = array(
						'DK_DATE'		=>	date('Ymd'),
						'DK_TIME'		=>	date('His'),
						'RES'			=>	$res['message'],
						'DK_FLAG'		=> 	2,
						'DK_ORDER_ID'	=>	$order_id
					);
					D($this->TDkls)->updateDkls("DK_FLAG != 0 and BANKACCT_NO = '".$group['BANKACCT_NO']."'", $updata);
					continue;
				}
			}
			//银盛
			elseif($group['DKCO_MAP_ID'] == 1){
				//检查证件号
				if(empty($group['BANK_NAME'])){
					$updata = array(
						'DK_DATE'		=>	date('Ymd'),
						'DK_TIME'		=>	date('His'),
						'RES'			=>	'缺少银行名称！',
					);
					D($this->TDkls)->updateDkls("DK_FLAG != 0 and BANKACCT_NO = '".$group['BANKACCT_NO']."'", $updata);
					continue;
				}
				
				//组装数据			
				$resdata = array(
					'Ver'				=>	'1.0',											//版本号
					'MsgCode'			=>	'S1031',										//报文编号
					'Time'				=>	date('YmdHis'),									//发送时间
					'OrderId'			=>	$order_id,										//订单号
					'BusiCode'			=>	'1010004',										//业务代码	 max(10)	1010004
					'ShopDate'			=>	date('Ymd'),									//商户日期
					'Cur'				=>	'CNY',											//币种
					'Amount'			=>	$group['DK_AMT'],								//金额
					'Note'				=>	'积分宝佣金代扣',								//订单说明
					'BankAccountType'	=>	'11',											//账户类型	11借记卡
					'BankName'			=>	$group['BANK_NAME'],							//银行
					'AccountNo'			=>	$group['BANKACCT_NO'],							//账户号	6217993300031483990	
					'AccountName'		=>	$group['BANKACCT_NAME'],						//账户名
					'Province'			=>	'',												//开户行所在省
					'City'				=>	'',												//开户行所在市
					'BankCode'			=>	setStrzero($group['BANK_BID'], 12, '0', 'r'),	//银行行号
					'ExtraData'			=>	'',												//银行卡附加数据
				);
				$Pay   = new \YinSheng(C('YinshengPay'));
				$xml   = $Pay->dk_one_tmp($resdata);		//组报文
				$sign  = $Pay->setencrypt($xml);			//加密
				$res   = $Pay->httpysdata($resdata['MsgCode'], $resdata['OrderId'], $sign);
				$state = $res['Result']['BusiState'];				
				if($state != '00') {
					Add_LOG('Dk');
					Add_LOG('Dk', '银盛 - 代扣失败，订单id：'.$order_id.'，原因：'.$res['Result']['Note']);
					Add_LOG('Dk', '代扣发送数据：'.json_encode($resdata));
					Add_LOG('Dk', '银行返回数据：'.json_encode($res));
					
					$updata = array(
						'DK_DATE'		=>	date('Ymd'),
						'DK_TIME'		=>	date('His'),
						'RES'			=>	$res['Result']['Note'],
						'DK_FLAG'		=> 	2,
						'DK_ORDER_ID'	=>	$order_id
					);
					D($this->TDkls)->updateDkls("DK_FLAG != 0 and BANKACCT_NO = '".$group['BANKACCT_NO']."'", $updata);					
					continue;
				}
				$traceNo = $res['Result']['TradeSN'];
			}
			
			//代扣成功
			if($state == '00') {
				//获取手续费
				$dk_sxf	   = 0;
				$dkcodata  = D($this->MDkco)->findDkco("DKCO_MAP_ID = '".$group['DKCO_MAP_ID']."'", 'DKCO_FEE_FLAG,DKCO_FEE_FIX,DKCO_FEE_PER');
				switch($dkcodata['DKCO_FEE_FLAG']){
					case '0':	//每笔固定金额
						$dk_sxf = $dkcodata['DKCO_FEE_FIX'];
					break;
					case '1':	//每笔百分比
						$dk_sxf = $group['DK_AMT'] * $dkcodata['DKCO_FEE_PER'] / 100;
					break;
				}
				
				//分摊列表
				$fentanlist	= D($this->TDkls)->getNewsDklslist("DK_FLAG != 0 and BANKACCT_NO = '".$group['BANKACCT_NO']."'", 'DK_ID');
				
				//分摊手续费
				$fen_cnt	= count($fentanlist);
				$fen_qiuyu 	= $dk_sxf % $fen_cnt;
				$fen_one  	= floor($dk_sxf / $fen_cnt);
				
				foreach($fentanlist as $key=>$val){
					$dk_fee = $fen_one;
					if($key == ($fen_cnt - 1)) {
						$dk_fee = $fen_one + $fen_qiuyu;
					}
					$updata = array(
						'DK_FEE'		=>	$dk_fee,
						'DK_DATE'		=>	date('Ymd'),
						'DK_TIME'		=>	date('His'),
						'RES'			=>	'批量代扣成功！',
						'DK_FLAG'		=>	0,
						'DK_ORDER_ID'	=>	$order_id,
						'DK_RET_TRACE'	=>	$traceNo,
					);
					D($this->TDkls)->updateDkls("DK_ID = '".$val['DK_ID']."'", $updata);				
				}
				
				//发送短信
				if($group['MOBILE']){
					$smsdata = array(
						'dk_bankacct_no_tail'	=>	substr($group['BANKACCT_NO'], -4, 4),
						'datetime'				=>	date('Y-m-d H:i:s'),
						'dk_yl_amt'				=>	setMoney($group['DK_AMT'], 2, 2),
					);			
					$arr = setSmsmodel(6, $smsdata);
					if(!empty($arr)){
						$sls = array(
							'BRANCH_MAP_ID'		=>	$group['BRANCH_MAP_ID'],
							'PARTNER_MAP_ID'	=>	$group['PARTNER_MAP_ID'],
							'SMS_MODEL_TYPE'	=>	6,
							'VIP_FLAG'			=>	0,
							'VIP_ID'			=>	0,
							'VIP_CARDNO'		=>	'-',
							'SMS_RECV_MOB'		=>	$group['MOBILE'],
							'SMS_RECV_NAME'		=>	$group['SHOP_NAME'],
							'SMS_TEXT'			=>	$arr['str'],
							'SMS_STATUS'		=>	2,
							'SMS_DATE'			=>	date('Ymd'),
							'SMS_TIME'			=>	date('His'),
							'SMS_MODEL_ID'		=>	$arr['mid'],
							'SMS_MUL_BATCH'		=>	0,
							'SMS_RESP_ID'		=>	0,
						);
						D($this->TSmsls)->addSmsls($sls);
					}
				}
			}
		}
		
		echo '查询、批量代扣执行完成！';
		exit;	
	}

	
	//检查会员开户	
	public function checkVipadd() {
		$post['VIP_ADDRESS'] 	= I('VIP_ADDRESS');
		$post['VIP_BIRTHDAY'] 	= I('VIP_BIRTHDAY');
		$post['VIP_CITY'] 		= I('VIP_CITY');
		$post['VIP_EMAIL'] 		= I('VIP_EMAIL');
		$post['VIP_IDNO'] 		= I('VIP_IDNO');
		$post['VIP_IDNOTYPE'] 	= I('VIP_IDNOTYPE');
		$post['VIP_MOBILE'] 	= I('VIP_MOBILE');
		$post['VIP_NAME'] 		= I('VIP_NAME');
		$post['VIP_SEX'] 		= I('VIP_SEX');	
		//验证
		if(empty($post['VIP_NAME']) || empty($post['VIP_IDNO']) || empty($post['VIP_BIRTHDAY']) || empty($post['VIP_MOBILE']) || empty($post['VIP_CITY'])){
			$this->ajaxResponse(1, '缺少必填项数据！');
		}
		
		//检测手机号
		//检验手机号是否与积分宝原系统会员的手机号重复
		$url = VIP_PUSH_URL.'api/open/member/check_mobile_identity.json';
		$mobile_data   = array('mobile' => $post['VIP_MOBILE'],'identity' => '');
		$mobile_valid  = json_decode(httpPostForm($url, $mobile_data));
		if ($mobile_valid->code == '0') {
			$this->ajaxResponse(1, '该手机号已在积分宝原系统中注册！');
		}
		//检验证件号是否与积分宝原系统会员的证件号重复
		$identity_data = array('mobile' => '','identity' => $post['VIP_IDNO']);
		$identity_valid  = json_decode(httpPostForm($url, $identity_data));
		if ($identity_valid->code == '0') {
			$this->ajaxResponse(1, '该证件号已在积分宝原系统中注册！');
		}
		//检验新系统中的手机号与证件号码
		$findvip = D('GVip')->findVip("VIP_MOBILE = '".$post['VIP_MOBILE']."'");
		if(!empty($findvip)){
			$this->ajaxResponse(1, '该手机号已注册！');
		}
		//检测证件号
		$findvip = D('GVip')->findVip("VIP_IDNOTYPE = '".$post['VIP_IDNOTYPE']."' and VIP_IDNO = '".$post['VIP_IDNO']."'");
		if(!empty($findvip)){
			$this->ajaxResponse(1, '该证件号已注册！');
		}
		//检查性别是否合法
		if($post['VIP_IDNOTYPE']==0 && $post['VIP_IDNOTYPE']!=''){
			$sex = substr($post['VIP_IDNO'], -2, 1);
			if($sex%2 != $post['VIP_SEX']){
				$this->ajaxResponse(1, '请规范选择会员性别！');
			}
		}
		$this->ajaxResponse(0, '通过！');		
	}
	
	//银行bid 查找带回
	public function getbankbid() {
		$post = I('post');
		if($post['submit'] == "getbankbid"){
			$where = "1=1";
			//所在省
			if($post['CITY_S_CODE']) {
				$where .= " and CITY_S_CODE = '".$post['CITY_S_CODE']."'";
			}
			//开户银行
			if($post['ISSUE_CODE']) {
				$where .= " and ISSUE_CODE = '".$post['ISSUE_CODE']."'";
			}
			//银行名称
			if($post['BANK_BNAME']) {
				$where .= " and BANK_BNAME like '%".$post['BANK_BNAME']."%'";
			}
			//银行行号
			if($post['BANK_BID']) {
				$where .= " and BANK_BID = '".$post['BANK_BID']."'";
			}
			//分页
			$count = D($this->MBid)->countBid($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MBid)->getBidlist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		//省列表
		$citylist = D($this->MCity)->getCityslist('RPAD(PROVINCE_CODE,6,"0") as PROVINCE_CODE,PROVINCE_NAME');
		//银行列表
		$banklist = D($this->MBank)->getBanklist('','ISSUE_CODE,BANK_NAME');
		$this->assign('citylist',			$citylist);
		$this->assign('banklist',			$banklist);
		$this->display();	
	}
	
	
	/*
	* 会员二维码开户
	* 
	* http://demo.shoudan.com/index.php/Home/Public/vipcode.html?BRANCH_MAP_ID=100003&PARTNER_MAP_ID=4
	**/
	public function vipcode() {
		echo "此功能已经停用，请使用积分宝APP注册";
		exit;
		$post = array(
			'BRANCH_MAP_ID'	 =>	I('BRANCH_MAP_ID'),
			'PARTNER_MAP_ID' =>	I('PARTNER_MAP_ID'),
		);
		//验证
		if(empty($post['BRANCH_MAP_ID']) || empty($post['PARTNER_MAP_ID'])){
			echo '缺少参数！';
			exit;
		}
		$info = D($this->MPartner)->findPartner("a.BRANCH_MAP_ID='".$post['BRANCH_MAP_ID']."' and a.PARTNER_MAP_ID='".$post['PARTNER_MAP_ID']."'", 'a.BRANCH_MAP_ID,b.BRANCH_NAME,a.PARTNER_MAP_ID,a.PARTNER_NAME');
		if(empty($info)){
			echo '数据匹配错误！';
			exit;
		}
		//特许处理
		$checkpart = 0;
		if(in_array($post['PARTNER_MAP_ID'], array('100014','100032','100046','100047','100057','100058','100064','100065','100066'))){
			$checkpart = 1;
		}
		$this->assign('info', 		$info);
		$this->assign('checkpart', 	$checkpart);
		$this->display();	
	}
	//发送验证码
	public function sendsms() {
		$post = array(
			'mobile'	     =>	I('mobile'),
			'BRANCH_MAP_ID'	 =>	I('BRANCH_MAP_ID'),
			'PARTNER_MAP_ID' =>	I('PARTNER_MAP_ID'),
			'keyword'        =>	I('keyword'),
		);
		//验证
		if(empty($post['mobile']) || empty($post['BRANCH_MAP_ID']) || empty($post['PARTNER_MAP_ID']) || empty($post['keyword'])){
			$this->ajaxResponse(1, '缺少参数！');
		}
		$key = md5(md5(date('Ymd').'jfb'));
		if($key != $post['keyword']){
			$this->ajaxResponse(1, '秘钥不匹配！');
		}
		//检测手机号
		$mobile = D($this->GVip)->findVip("VIP_MOBILE = '".$post['mobile']."'");
		if(!empty($mobile)){
			$this->ajaxResponse(1, '该手机号已经存在！');
		}
		//检测短信
		$ctime  = time() - 180;
		$lsdata = D($this->TSmsls)->findSmsls("SMS_RECV_MOB='".$post['mobile']."'", 'SMS_DATE,SMS_TIME', 'SMS_DATE desc,SMS_TIME desc');
		if(!empty($lsdata)){
			$dtime  = strtotime($lsdata['SMS_DATE'].' '.$lsdata['SMS_TIME']);
			if($ctime < $dtime){
				$this->ajaxResponse(1, '请 '.($dtime - $ctime).' 秒后再重新发送！');
			}
		}
		
		//短信模板
		$code  = rand(100000, 999999);
		$model = D($this->MSmsmodel)->findSmsmodel("SMS_MODEL_TYPE = '9'");
		$text  = str_replace('{check_code}', $code, $model['SMS_MODEL']);
		
		//短信流水
		$smsls = array(
			'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],
			'SMS_MODEL_TYPE'	=>	'9',
			'VIP_FLAG'			=>	'0',
			'VIP_ID'			=>	'0',
			'VIP_CARDNO'		=>	'-',
			'SMS_RECV_MOB'		=>	$post['mobile'],
			'SMS_RECV_NAME'		=>	$post['mobile'],
			'SMS_TEXT'			=>	$text,
			'SMS_STATUS'		=>	'2',
			'SMS_DATE'			=>	date('Ymd'),
			'SMS_TIME'			=>	date('His'),
			'SMS_MODEL_ID'		=>	$model['SMS_MODLE_ID'],
			'SMS_MUL_BATCH'		=>	'0',
			'SMS_RESP_ID'		=>	'0',
		);
		$res = D($this->TSmsls)->addSmsls($smsls);
		if($res['state'] != 0){
			$this->ajaxResponse(1, '短信发送失败！');
		}
		$this->ajaxResponse(0, '短信发送成功！', array('SMS_ID'=> $res['result']));		
	}
	//激活
	public function addvipcode() {
		echo "此功能已经停用，请使用积分宝APP注册";
		exit;
		$post = array(
			'BRANCH_MAP_ID'	 =>	I('BRANCH_MAP_ID'),
			'PARTNER_MAP_ID' =>	I('PARTNER_MAP_ID'),
			'sms_id'	 	=>	I('sms_id'),
			'card_flag'  	=>	I('card_flag'),
			'VIP_MOBILE' 	=>	I('VIP_MOBILE'),
			'SMS_CODE'   	=>	I('SMS_CODE'),
			'VIP_NAME'   	=>	I('VIP_NAME'),
			'VIP_IDNO'   	=>	I('VIP_IDNO'),		//非必填
			'CARD_NO'    	=>	I('CARD_NO'),		//非必填
			'CARD_CHECK' 	=>	I('CARD_CHECK'),	//非必填
		);
		//验证
		if(empty($post['BRANCH_MAP_ID']) || empty($post['PARTNER_MAP_ID']) || empty($post['VIP_MOBILE']) || empty($post['SMS_CODE'])){
			$this->ajaxResponse(1, '缺少参数！');
		}
		//检测短信
		$ctime  = time() - 300;
		$lsdata = D($this->TSmsls)->findSmsls("SMS_ID='".$post['sms_id']."'", 'SMS_TEXT', 'SMS_DATE desc,SMS_TIME desc');
		if(empty($lsdata)){
			$this->ajaxResponse(1, '短信验证码错误！');
		}
		$dtime  = strtotime($lsdata['SMS_DATE'].' '.$lsdata['SMS_TIME']);		
		if($ctime > $dtime){
			$this->ajaxResponse(1, '该验证码已失效！');
		}
		//检测证件号
		if(!empty($post['VIP_IDNO'])){
			$card = D($this->GVip)->findVip("VIP_IDNOTYPE = '0' and VIP_IDNO = '".$post['VIP_IDNO']."'");
			if(!empty($card)){
				$this->ajaxResponse(1, '该证件号已注册！');
			}
			$city_no = substr($post['VIP_IDNO'], 0, 6);
			$Y		 = substr($post['VIP_IDNO'], 6, 4);
			$m		 = substr($post['VIP_IDNO'], 10, 2);
			$d		 = substr($post['VIP_IDNO'], 12, 2);
			$sex	 = substr($post['VIP_IDNO'], 16, 1)%2 == 0 ? '0' : '1';			
			$post['VIP_CITY']	 	=	$city_no;
			$post['VIP_IDNOTYPE']	=	'0';
			$post['VIP_BIRTHDAY'] 	=	$Y.$m.$d;
			$post['VIP_SEX']	  	=	$sex;
		}else{
			$post['VIP_CITY']	 	=	'';		
			$post['VIP_IDNOTYPE']	=	'9';
			$post['VIP_IDNO']		=	'9'.$post['VIP_MOBILE'];
			$post['VIP_BIRTHDAY'] 	=	'';	
			$post['VIP_SEX']	  	=	'';
		}
		//检测卡号、验证码
		if(!empty($post['CARD_NO']) && !empty($post['CARD_CHECK'])){
			$vipcard = D($this->GVipcard)->findVipcard("CARD_NO = '".$post['CARD_NO']."' and CARD_STATUS=1 and CARD_CHECK = '".$post['CARD_CHECK']."'");
			if(empty($vipcard)){
				$this->ajaxResponse(1, '卡号、校验码不通过！');
			}
		}
		//vip表
		$vip = array(
			'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],
			'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],
			'VIP_SOURCE'		=>	'4',					//二维码
			'CARD_NO'			=>	$post['CARD_NO'] ? $post['CARD_NO'] : $post['VIP_MOBILE'],
			'VIP_NAME'			=>	$post['VIP_NAME'] ? $post['VIP_NAME'] : $post['VIP_MOBILE'],
			'VIP_STATUS'		=>	'0',
			'VIP_CARD_FLAG'		=>	$post['card_flag'] ? $post['card_flag'] : '1',
			'VIP_AUTH_FLAG'		=>	$post['card_flag'] ? '1' : '0',
			'VIP_PARTNER_FLAG'	=>	'0',
			'VIP_IDNOTYPE'		=>	$post['VIP_IDNOTYPE'],	//会员证件类型
			'VIP_IDNO'			=>	$post['VIP_IDNO'],		//证件号码
			'VIP_MOBILE'		=>	$post['VIP_MOBILE'],
			'VIP_CITY'			=>	$post['VIP_CITY'],		//所在城市
			'VIP_ADDRESS'		=>	'',						//户口地址
			'VIP_SEX'			=>	$post['VIP_SEX'],		//性别
			'VIP_EMAIL'			=>	'sshmjfb@163.com',		//持卡人电子邮件
			'VIP_BIRTHDAY'		=>	$post['VIP_BIRTHDAY'],	//持卡人生日
			'VIP_DONATE'		=>	0,
			'VIP_DONATE_PER'	=>	'',
			'VIP_ID_M'			=>	'',
			'VIP_PIN'			=>	strtoupper(md5(strtoupper(md5('888888')))),
			'VIP_PINTIME'		=>	0,
			'VIP_PINLIMIT'		=>	5,
			'CREATE_TIME'		=>	date('YmdHis'),
			'ACTIVE_TIME'		=>	date('YmdHis'),
			'UPDATE_TIME'		=>	date('YmdHis'),
			'RES'				=>	'',
			'ID_PHOTO'			=>	'',
		);
		$M = M();
		$M->startTrans();	//启用事务
		
		$res = D($this->GVip)->addVip($vip);
		if($res['state'] != 0){
			$this->ajaxResponse(1, '会员添加失败！');
		}
		//没传卡号和校验码，则为新注册
		if(!empty($post['CARD_NO']) && !empty($post['CARD_CHECK'])){
			//将会员归属覆盖卡归属
			$vcarddata = array(
				'BRANCH_MAP_ID1'	=>	$vipcard['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID1'	=>	$vipcard['PARTNER_MAP_ID'],
				'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],				
				'VIP_ID'			=>	$res['VIP_ID'],	//vip表vip_id 覆盖vipcard表 vip_id
				'CARD_STATUS'		=>	0
			);
			$res_vip = D($this->GVipcard)->updateVipcard("CARD_NO = '".$post['CARD_NO']."'", $vcarddata);
			if($res_vip['state'] != 0){
				$M->rollback();	//回滚
				$this->ajaxResponse(1, '卡产品修改失败！');
			}
			
			//如果是预免卡3，只插入lap表，如果是收费卡2，插入lap表,还要插入reg表
			$fee = D($this->MFeecfg)->findFeecfg('CFG_FLAG = 3', 'CARD_OPENFEE');
			$lap = array(
				'SUBJECT_CODE'	=>	'20101',
				'ACCT_NO'		=>	setStrzero($res['VIP_ID'], 9),
				'ACCT_NAME'		=>	$post['VIP_NAME'],
				'ACCT_TYPE'		=>	'V',
				'ACCT_VALBAL'	=>	'0',
				'ACCT_YLTBAL'	=>	'0',
				'ACCT_YLVBAL'	=>	'0',
				'ACCT_YWTBAL'	=>	'0',
				'ACCT_YWVBAL'	=>	'0',
				'ACCT_DIVBAL'	=>	$post['card_flag']==3 ? $fee['CARD_OPENFEE'] : '0',
				'ACCT_CAMT'		=>	'0',
				'ACCT_DAMT'		=>	'0',
				'ACCT_DATE'		=>	date('Ymd'),
				'SYSTEM_TIME'	=>	date('YmdHis'),
				'MAC'			=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',
				'YLT_AMT'		=>	'0',
				'YWT_AMT'		=>	'0',
			);
			$res_lap = D($this->GLap)->addLap($lap);
			if($res_lap['state'] != 0){
				$M->rollback();	//回滚
				$this->ajaxResponse(1, '会员分账户表导入失败！');
			}		
			if($post['card_flag'] == 2){
				//reg
				$fee = D($this->MFeecfg)->findFeecfg('CFG_FLAG = 2', 'TRAFICC_FEE');
				$reg = array(
					'REG_TYPE'	=>	'101',
					'REG_INDEX'	=>	setStrzero($res['VIP_ID'], 9),
					'REG_DESC'	=>	$post['VIP_NAME'],
					'REG_AMT'	=>	$fee['TRAFICC_FEE'],
					'MARK_FLAG'	=>	1,
					'MARK_DATE'	=>	date('Ymd'),
					'YL_BAL'	=>	'0',
					'YW_BAL'	=>	'0',
					'DIV_BAL'	=>	'0',
					'AMT1'		=>	'0',
					'AMT2'		=>	'0',
				);
				D($this->MReg)->addReg($reg);			
			}	
		}

		//同步新增会员数据
		/*$url = VIP_PUSH_URL.'api/open/synchronize/member/register';
		$data = array(			
			'token' 	 	 => strtoupper(md5(strtoupper(md5($res['VIP_ID'].'1')))),	//(签名验证)
			'mId' 	 		 => $res['VIP_ID'],						//(会员ID)
			'mCId' 	 		 => $vip['CARD_NO'],					//(卡号)
			'mName' 		 => $vip['VIP_NAME'],					//(会员姓名)
			'mIdentityType'	 => $vip['VIP_IDNOTYPE'],				//(证件类型)
			'mIdentityId' 	 => $vip['VIP_IDNO'],					//(证件号)
			'mBirthday'		 => $vip['VIP_BIRTHDAY'],				//(会员生日)
			'mMobile' 		 => $vip['VIP_MOBILE'],					//(手机号码)
			'gender' 		 => $vip['VIP_SEX'],					//(会员性别)
			'mCurrentCity'	 => getcity_name($vip['VIP_CITY']),		//(所在城市)
			'mNativeAddress' => $vip['VIP_ADDRESS'],				//(户口地址)
			'mEmail' 		 => $vip['VIP_EMAIL'],					//(会员邮箱)
			'operateType'	 => '1',								//(操作类型)
			'mRemark'	 	 => 'glzxNew'
		);
		Add_LOG(CONTROLLER_NAME, json_encode($data));
		$resjson = httpPostForm($url,$data);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->code != '0') {
			$M->rollback();	//回滚
			$this->wrong('会员数据同步失败');
		}*/
		$M->commit();	//成功

		//发短信
		if($vip['VIP_MOBILE'] && ($post['card_flag']==3 or $post['card_flag']==2)){
			//短信模板
			$model_arr = setSmsmodel(2);
			//短信流水
			$smsls = array(
				'BRANCH_MAP_ID'		=>	$vip['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'	=>	$vip['PARTNER_MAP_ID'],
				'SMS_MODEL_TYPE'	=>	'2',
				'VIP_FLAG'			=>	$vip['VIP_CARD_FLAG'],
				'VIP_ID'			=>	$res['VIP_ID'],
				'VIP_CARDNO'		=>	$vip['CARD_NO'],
				'SMS_RECV_MOB'		=>	$vip['VIP_MOBILE'],
				'SMS_RECV_NAME'		=>	$vip['VIP_NAME'],
				'SMS_TEXT'			=>	$model_arr['str'],
				'SMS_STATUS'		=>	'2',
				'SMS_DATE'			=>	date('Ymd'),
				'SMS_TIME'			=>	date('His'),
				'SMS_MODEL_ID'		=>	$model_arr['mid'],
				'SMS_MUL_BATCH'		=>	'0',
				'SMS_RESP_ID'		=>	'0',
			);
			D($this->TSmsls)->addSmsls($smsls);
		}
		$this->ajaxResponse(0, '激活成功！');
	}

	//商户扣率
	public function getsmdr() {
		//获取商户结算信息
		$list = M('smdr')->alias('sm')
				->join(DB_PREFIX.'shop sh on sh.SHOP_MAP_ID = sm.SHOP_MAP_ID')
				->where("sh.SHOP_STATUS = 0 and sm.PAY_TYPE in (5,0)")
				->field('sm.SHOP_MAP_ID, sm.PAY_TYPE, sm.JFB_PER_FEE, sm.JFB_FIX_FEE, sm.PER_FEE, sm.FIX_FEE, sm.SHOP_STATUS,sh.SHOP_NAME')
				->order("sm.SHOP_MAP_ID desc,locate(sm.PAY_TYPE,'5,0')")
				->select();
			//导出操作
			$xlsname = '积分宝商户扣率数据';
			$xlscell = array(
				array('SHOP_MAP_ID',	'商户ID'),
				array('SHOP_NAME',		'商户名称'),
				array('PAY_TYPE',		'交易支付方式'),
				array('PER_FEE',		'商户收单扣率'),
				array('FIX_FEE',		'商户收单封顶'),
				array('JFB_PER_FEE',	'积分宝扣率'),
				array('JFB_FIX_FEE',	'积分宝封顶')
			);
			$pay_arr = array(
				'5' => '积分宝扣率',
				'0' => '银行卡扣率',
				'4' => '储值卡'
			);
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'SHOP_MAP_ID'		=>	$val['SHOP_MAP_ID'],
					'SHOP_NAME'			=>	$val['SHOP_NAME'],
					'PAY_TYPE'			=>	$pay_arr[$val['PAY_TYPE']],
					'PER_FEE'			=>	$val['PER_FEE']/10000,
					'FIX_FEE'			=>	$val['FIX_FEE']/100,
					'JFB_PER_FEE'		=>	$val['JFB_PER_FEE']/10000,
					'JFB_FIX_FEE'		=>	$val['JFB_FIX_FEE']/100
				);	
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
	}


	/**
	 * [import_ttrabak1 导入通道/渠道临时对账流水表]
	 * @param  integer $channel [通道入口：5，兴业]
	 * @return [type]           [description]
	 * 文件头:DZ（对账）
	 * 代理商编号：3101080001x
	 * 日期格式：yyyymmdd(例如：20160616)
	 * 后缀：.xls
	 * 规则：DZ+代理商编号+日期格式+.xls 例如：2016-06-15 t1历史交易报表 (芮易).xls 重命名为:DZ3101080001x20160616.xls
	 */
	function import_ttrabak1($channel=2){ 
		$data = [];
		if($channel==2) $channelno = '3101080001x';
		else $channelno = '';
     	$path = C('Uploads').'ttrabak1/'.date('Y').'/DZ'.$channelno.date(Ymd).'.xls';
        $result = D($this->MExcel)->importExecl($path);
        if($result["error"] == 1){     
          $execl_data = $result["data"][0]["Content"];
          //$rows = $result["data"][0]["Rows"];
          if($channel==2){ //兴业通道
          	foreach($execl_data as $k=>$v){
	              if($k==1 || empty($v[0]) || $v[0] == '合计' || $v[0] == '序号') continue;
	              $data[] = [ 'HOST_MAP_ID'=>$channel, //通道的host_map_id，必填
	              				'CHANNEL_MAP_ID'=>0, //通道的ID，必填
	              				'HOST_TRACE'=>substr($v[6], 0,12), //主机流水号,必填
	              				//'ORDER_NO'=>'', //订单号
	              				'POS_DATE'=> implode('', explode('-', $v[1])),
	              				'POS_TIME'=> implode('',explode(':', $v[2])),
	              				'HSHOP_NO'=>$v[3],
	              				'CARD_NO'=>$v[5],
	              				'TRANS_AMT'=>$v[7]*100,
	              				'SETTLE_AMT'=>$v[9]*100,
	              				];
	          }
          }else{ //卡友通道
	          foreach($execl_data as $k=>$v){
	              if($k==1 || empty($v[0])) continue;
	              $date = explode(' ', $v[1]);
	              $data[] = [ 'HOST_MAP_ID'=>$channel, //通道的host_map_id，必填
	              				'CHANNEL_MAP_ID'=>0, //通道的ID，必填
	              				'HOST_TRACE'=>substr($v[6], 0,12), //主机流水号,必填
	              				//'ORDER_NO'=>'', //订单号
	              				'HOST_TRACE'=>substr($v[6], 0,12), //参考号
	              				'POS_DATE'=> implode('', explode('-', $date[0])),
	              				'POS_TIME'=> implode('',explode(':', $date[1])),
	              				//'TRACE_RETCODE'=>$v[2],
	              				'CARD_NO'=>$v[5],
	              				'TRANS_AMT'=>$v[7]*100,
	              				'SETTLE_AMT'=>$v[13]*100,
	              				];
	          }
          } 
          $res = D($this->TTrabak1)->addAllTrace($data); 
			if($res['state'] == 1){ //失败
				echo '-1';
				setLog(2, 'import_ttrabak1'.$path.'插入失败！');
			}elseif($res['state'] == 0){ //成功
				echo '0';
				setLog(2, 'import_ttrabak1'.$path.'插入成功！');
			}
        }elseif($result["error"] == 0){
        	echo '-1';
        	setLog(2, 'import_ttrabak1'.$result["message"].'path:'.$path);
        }
 	}


	//商户数据导出
	public function out_shopdata(){
		//状态
		$where = "s.SHOP_STATUS = '0'";
		//分页
		$count = D('MShop')->countShop($where);
		//$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
		$list  = D('MShop')->getShoplist($where, 's.*,p.PARTNER_NAME,b.BRANCH_NAME ','','s.CREATE_TIME DESC');
		$log_filename = "./Public/file/upload/out_shopdata.txt";
		file_put_contents($log_filename, "共计".$count."条商户记录"."\r\n", FILE_APPEND);
		$shop_data_str = '';
		$oParentRelation = '[100000,0]';
		foreach ($list as $key => $value) {
			$city_data = D('MCity')->findCity("CITY_S_CODE='".$value['CITY_NO']."'",'RPAD(PROVINCE_CODE,6,"0") as PROVINCE_CODE,RPAD(CITY_CODE,6,"0") as CITY_CODE,CITY_S_CODE');
			$res = get_level_data($value['PARTNER_MAP_ID']);
			//$oParentRelation = '[100000,0]';
			foreach ($res as $kk => $val) {
				if ($kk != 3) {
					$oParentRelation .='['.$val['id'].','.($kk+1).']';
				}
			}
			$oParentRelation .= '['.$value['SHOP_MAP_ID'].',4]';
			$shop_data_str = $value['SHOP_MAP_ID'].','
						   .$value['SHOP_NO'].','												//(商户号)
						   .$value['SHOP_NAME'].','												//(商户名称)
						   .$value['SHOP_NAMEABCN'].','											//(商户简称)
						   .$city_data['PROVINCE_CODE'].','										//(省份)
						   .$city_data['CITY_CODE'].','											//(城市)
						   .$city_data['CITY_S_CODE'].','										//(区县)
						   .getcity_name($city_data['CITY_S_CODE']).$value['ADDRESS'].','		//(详细地址)
						   .$value['MOBILE'].','												//(联系手机)
					 	   .$value['TEL'].','													//(联系电话)
						   .$value['EMAIL'].','													//(邮箱地址)
						   .$value['MCC_TYPE'].','												//(商户分类-大分类)
		 				   .$value['MCC_CODE'].','												//(商户分类-小分类)
						   .set_jifenlv($value['SHOP_NO'],1).','								//(积分率)
						   .$oParentRelation.','												//(商户归属关系)
						   .$value['CREATE_TIME'];												//(商户进件时间)
      		file_put_contents($log_filename, $shop_data_str."\r\n", FILE_APPEND);
      		$shop_data_str = '';
      		$oParentRelation = '[100000,0]';
		}
	}

	//商户数据导出
	public function out_shopdata2(){
		ini_set('memory_limit', '250M'); //内存限制
		set_time_limit(0); //
		//状态
		$where = "s.SHOP_STATUS = '0'";
		//分页
		$count = D('MShop')->countShop($where);
		$list  = D('MShop')->getShoplist($where, 's.SHOP_MAP_ID,s.SHOP_NO,s.SHOP_NAME,s.SHOP_NAMEABCN,s.CREATE_TIME,s.PARTNER_MAP_ID','','s.CREATE_TIME DESC');
		$log_filename = "./Public/file/upload/out_shopdata.txt";
		file_put_contents($log_filename, "共计".$count."条商户记录"."\r\n", FILE_APPEND);
		$shop_data_str = '';
		$oParentRelation = '[100000,0]';
		foreach ($list as $key => $value) {
			$res = get_level_data($value['PARTNER_MAP_ID']);
			foreach ($res as $kk => $val) {
				if ($kk != 3) {
					$oParentRelation .='['.$val['id'].','.($kk+1).']';
				}
			}
			$oParentRelation .= '['.$value['SHOP_MAP_ID'].',4]';
			$shop_data_str = $value['SHOP_MAP_ID'].','
						   .$value['SHOP_NO'].','												//(商户号)
						   .$value['SHOP_NAME'].','												//(商户名称)
						   .$value['SHOP_NAMEABCN'].','											//(商户简称)
						   .$oParentRelation.','												//(商户归属关系)
						   .$value['CREATE_TIME'];												//(商户进件时间)
      		file_put_contents($log_filename, $shop_data_str."\r\n", FILE_APPEND);
      		$shop_data_str = '';
      		$oParentRelation = '[100000,0]';
		}
	}
	//会员数据同步
	public function syn_vipdata(){
		$where = "VIP_MOBILE = '18110861338'";
		//分页
		$count = D($this->GVip)->countNewsVip($where);
		$post = array(
			'pCountIndex' => I('pCountIndex'), 
			'pageSize' 	  => I('pageSize')
		);
		//$limit = ($post['pCountIndex'] - 1)*$post['pageSize'].','.$post['pCountIndex']*$post['pageSize'];
		
		$list  = D($this->GVip)->getNewsViplist($where,'*');
		$reg_url = VIP_PUSH_URL.'api/open/synchronize/member/register';
		//同步修改会员数据
		$update_url = VIP_PUSH_URL.'api/open/synchronize/member/modify';
		//结果记录
		$add_data = array('error_num'=>0,'success_num'=>0,'log_filename'=>'./Public/file/upload/syn_addvip_log.txt');
		$update_data = array('error_num'=>0,'success_num'=>0,'log_filename'=>'./Public/file/upload/syn_updatevip_log.txt');
		$total = count($list);
		//同步新增会员数据
		foreach ($list as $key => $value) {
			$total++;
			$regdata = array(
				'token' 	 	 => strtoupper(md5(strtoupper(md5($value['VIP_ID'].'1')))),				//(签名验证)
				'mId' 	 		 => $value['VIP_ID'],						//(会员ID)
				'mCId' 	 		 => $value['CARD_NO'] ? $value['CARD_NO'] : $value['VIP_MOBILE'],		//(卡号)
				'mName' 		 => $value['VIP_NAME'],						//(会员姓名)
				'mIdentityType'	 => $value['VIP_IDNOTYPE'],					//(证件类型)
				'mIdentityId' 	 => $value['VIP_IDNO'],						//(证件号)
				'mBirthday'		 => $value['VIP_BIRTHDAY'] ? date('Ymd',$value['VIP_BIRTHDAY']) : '',	//(会员生日)
				'mMobile' 		 => $value['VIP_MOBILE'],					//(手机号码)
				'gender' 		 => $value['VIP_SEX'],						//(会员性别)
				'mCurrentCity'	 => getcity_name($value['VIP_CITY']),		//(所在城市)
				'mNativeAddress' => $value['VIP_ADDRESS'],					//(户口地址)
				'mEmail' 		 => $value['VIP_EMAIL'],					//(会员邮箱)
				'operateType'	 => '1',									//(操作类型)
				'migrateType'	 => '2',									//(迁移区分)
				'mRemark'	 	 => 'glzxNew'
			);
			Add_LOG(CONTROLLER_NAME, json_encode($regdata));
			$resjson = httpPostForm($reg_url,$regdata);
			$result = json_decode($resjson);
			if ($result->code != 0) {
				$add_data['error_num']++;
      			file_put_contents($add_data['log_filename'], json_encode($regdata)."\r\n失败原因：\r\n".json_encode($resjson)."\r\n", FILE_APPEND);
			}else{
				$add_data['success_num']++;
			}
			Add_LOG(CONTROLLER_NAME, $resjson);
			$result = json_decode($resjson);
		}
		file_put_contents($add_data['log_filename'], "共成功".$add_data['success_num']."个会员, 失败".$add_data['error_num']."个会员"."\r\n", FILE_APPEND);
		//同步更新会员数据
		foreach ($list as $key => $value) {
			$updatedata = array(
				'token' 	 	 => strtoupper(md5(strtoupper(md5($value['VIP_ID'].'2')))),				//(签名验证)
				'mId' 	 		 => $value['VIP_ID'],						//(会员ID)
				'mCId' 	 		 => $value['CARD_NO'] ? $value['CARD_NO'] : $value['VIP_MOBILE'],		//(卡号)
				'mName' 		 => $value['VIP_NAME'],						//(会员姓名)
				'mIdentityType'	 => $value['VIP_IDNOTYPE'],					//(证件类型)
				'mIdentityId' 	 => $value['VIP_IDNO'],						//(证件号)
				'mBirthday'		 => $value['VIP_BIRTHDAY'] ? date('Ymd',$value['VIP_BIRTHDAY']) : '',	//(会员生日)
				'mMobile' 		 => $value['VIP_MOBILE'],					//(手机号码)
				'gender' 		 => $value['VIP_SEX'],						//(会员性别)
				'mCurrentCity'	 => getcity_name($value['VIP_CITY']),		//(所在城市)
				'mNativeAddress' => $value['VIP_ADDRESS'],					//(户口地址)
				'mEmail' 		 => $value['VIP_EMAIL'],					//(会员邮箱)
				'operateType'	 => '2'										//(操作类型)
			);
			Add_LOG(CONTROLLER_NAME, json_encode($updatedata));
			$resjson1 = httpPostForm($update_url,$updatedata);
			$result1 = json_decode($resjson1);
			if ($result1->code != 0) {
				$update_data['error_num']++;
				file_put_contents($update_data['log_filename'], json_encode($updatedata)."\r\n失败原因：\r\n".json_encode($resjson1)."\r\n", FILE_APPEND);
			}else{
				$update_data['success_num']++;
			}
			Add_LOG(CONTROLLER_NAME, $resjson1);
			$result = json_decode($resjson1);
		}
		file_put_contents($update_data['log_filename'], "共成功".$update_data['success_num']."个会员, 失败".$update_data['error_num']."个会员"."\r\n", FILE_APPEND);
		echo '完成，共计'.count($list).'个会员<br />';
		echo '添加，成功'.$add_data['success_num'].'个会员<br />';
		echo '修改，成功'.$update_data['success_num'].'个会员<br />';
	}
	//修改会员身份认证信息
	public function change_authvip(){
		$list  = D($this->GVip)->getNewsViplist('');
		
		foreach ($list as $key => $value) {
			if (substr($value['VIP_IDNO'], 0, 1) == '9') {
				$vip_auth_flag = 0;
			}else{
				$vip_auth_flag = 1;
			}
			$where = 'VIP_ID = "'.$value['VIP_ID'].'"';
			$data = array('VIP_AUTH_FLAG' => $vip_auth_flag);
			D($this->GVip)->updateVip($where, $data);
		}
		echo "完成";
	}

	//同步商户数据【添加】
	public function sync_shop(){
		$shop_id = I('SHOP_MAP_ID');
		$sync_res = shop_sync_data($shop_id,1);
		if (!$sync_res) {
			echo '<h1>商户进件数据同步失败</h1>';
		}else{
			echo '<h1>商户进件数据同步成功</h1>';
		}
	}

	//同步商户数据【更新】
	public function sync_shop_update(){
		$shop_id = I('SHOP_MAP_ID');
		$sync_res = shop_sync_data($shop_id,2);
		if (!$sync_res) {
			echo '<h1>商户更新数据同步失败</h1>';
		}else{
			echo '<h1>商户更新数据同步成功</h1>';
		}
	}

	//修改商户bid	临时
	public function upshopbid(){		
		set_time_limit(0);		
		$sbidlist = D('sbids')->order('SHOP_ID asc')->select();	
		$a = 0;$b = 0;
		for($i = 0; $i<count($sbidlist); $i++){
			$BANK_BID  = substr($sbidlist[$i]['BANK_BID'], 2).'000000000';
			$sdkbdata  = D($this->MSdkb)->findSdkb("SHOP_MAP_ID = '".$sbidlist[$i]['SHOP_ID']."'");
			if(empty($sdkbdata)) {
				continue;
			}
			$resdata = array();
			//对公
			/* if($sbidlist[$i]['SHOP_BANK_FLAG'] == '0'){
				//if(empty($sdkbdata['BANKACCT_NO1']) || empty($sdkbdata['BANKACCT_BID1'])){
					$resdata = array(
						'SHOP_BANK_FLAG'	=>	'0',
						'SHOP_ACCT_FLAG'	=>	'0',
						'BANKACCT_NAME1'	=>	$sbidlist[$i]['BANKACCT_NAME'],
						'BANKACCT_NO1'		=>	$sbidlist[$i]['BANK_ACCTNO'],
						'BANKACCT_BID1'		=>	$BANK_BID,
						'BANK_NAME1'		=>	$sbidlist[$i]['BANK_NAME'],
					);
					D($this->MSdkb)->updateSdkb("SHOP_MAP_ID = '".$sbidlist[$i]['SHOP_ID']."'", $resdata);
				//}
			} */
			//对私
		 	if($sdkbdata['SHOP_BANK_FLAG'] == '1'){
				if(empty($sdkbdata['BANKACCT_NO2']) || empty($sdkbdata['BANKACCT_BID2'])){
					$resdata = array(
						'SHOP_BANK_FLAG'	=>	'1',
						'SHOP_ACCT_FLAG'	=>	'0',
						'BANKACCT_NAME2'	=>	$sbidlist[$i]['BANKACCT_NAME'],
						'BANKACCT_NO2'		=>	$sbidlist[$i]['BANK_ACCTNO'],
						'BANKACCT_BID2'		=>	$BANK_BID,
						'BANK_NAME2'		=>	$sbidlist[$i]['BANK_NAME'],
					);
					D($this->MSdkb)->updateSdkb("SHOP_MAP_ID = '".$sbidlist[$i]['SHOP_ID']."'", $resdata);
					$b = $b + 1;
				}
			}
		}
		echo $a;exit;
	}

	//检查代扣流水 bid
	public function checkdklsbid(){
		set_time_limit(0);
		$dklslist = D($this->TDkls)->getDklsgrouplist("DK_FLAG = 0", 'SHOP_NO', '', 'SHOP_NO');
		for($i = 0; $i<count($dklslist); $i++){
			$shopdata = D($this->MShop)->findShop("SHOP_NO = '".$dklslist[$i]['SHOP_NO']."'", 'SHOP_MAP_ID');
			//查询商户	
			if(empty($shopdata)) {
				continue;
			}
			$sdkbdata = D($this->MSdkb)->findSdkb("SHOP_MAP_ID = '".$shopdata['SHOP_MAP_ID']."'");
			
			$resdata = array();
			//对公
			if($sdkbdata['SHOP_BANK_FLAG'] == '0'){
				$resdata = array(
					'BANK_NAME'	=>	$sdkbdata['BANK_NAME1'],
				);				
				D($this->TDkls)->updateDkls("SHOP_NO = '".$dklslist[$i]['SHOP_NO']."'", $resdata);
			}
			//对私
			else if($sdkbdata['SHOP_BANK_FLAG'] == '1'){
				$resdata = array(
					'BANK_NAME'	=>	$sdkbdata['BANK_NAME2'],
				);				
				D($this->TDkls)->updateDkls("SHOP_NO = '".$dklslist[$i]['SHOP_NO']."'", $resdata);
			}
		}
	}
		
	//缺陷商户bid导出	临时
	public function exportshopbid(){
		set_time_limit(0);
		
		$p 	   = I('p');
		$size  = I('size');
		$flag  = I('flag');
		
		$bRow  = $p * $size;
		$eRow  = $size;
		
	//	if($flag == 0){
			$where1 = "SHOP_BANK_FLAG = 0 and BANKACCT_NO1 > 0 and BANKACCT_BID1 <= 0";	//对公
			$field1 = 'SHOP_MAP_ID,BANKACCT_NO1 as BANKACCT_NO,BANKACCT_BID1 as BANKACCT_BID';
			$list1  = D($this->MSdkb)->getNewsSdkblist($where1, $field1, $bRow.','.$eRow);
			
	//	}else{
			$where2 = "SHOP_BANK_FLAG = 1 and BANKACCT_NO2 > 0 and BANKACCT_BID2 <= 0";	//对私
			$field2 = 'SHOP_MAP_ID,BANKACCT_NO2 as BANKACCT_NO,BANKACCT_BID2 as BANKACCT_BID';
			$list2  = D($this->MSdkb)->getNewsSdkblist($where2, $field2, $bRow.','.$eRow);
	//	}
		$xlsname = '有银行卡号，没有BID的商户';
		$xlscell = array(
			array('SHOP_MAP_ID',	'商户id'),
			array('SHOP_NAME',		'商户名称'),
			array('SHOP_NO',		'商户编号'),
			array('BANKACCT_NO',	'银行卡号'),
			array('BANKACCT_BID',	'BID'),
		);		
		$xlsarray = array();
		$list = array_merge($list1, $list2);
		foreach($list as $val){
			$shopdata = D($this->MShop)->findShop("SHOP_MAP_ID = '".$val['SHOP_MAP_ID']."'", 'SHOP_NAME,SHOP_NO');
			$xlsarray[] = array(
				'SHOP_MAP_ID'		=>	$val['SHOP_MAP_ID'],
				'SHOP_NAME'			=>	$shopdata['SHOP_NAME'],
				'SHOP_NO'			=>	$shopdata['SHOP_NO'],
				'BANKACCT_NO'		=>	$val['BANKACCT_NO'],
				'BANKACCT_BID'		=>	$val['BANKACCT_BID'],
			);	
		}
		D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
	}

	//更新现有的，投保数据（将所有投保成功的数据，改为过期状态）
	public function uptbstatus(){
		$post = array(
			'vflag' => I('vflag'), 	//2：付费卡，意外险；3：预免卡，意外险；
			'stype' => I('stype') ? I('stype') : 1,	//1：意外险；2：养老险
		);
		
		if ($post['stype'] == 2) {
			//养老险
			$where = 'SECURITY_TYPE = '.$post['stype'].' and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
		}else{
			if (empty($post['vflag'])) {
				echo '参数不能为空';exit;
			}
			//预免卡，意外险
			$where = 'VIP_FLAG = '.$post['vflag'].' and SECURITY_TYPE = '.$post['stype'].' and TB_DEL_FLAG = 0 and ONLINE_FLAG = 0';
		}
		
		$mtbls = M('tbls', DB_PREFIX_TRA, DB_DSN_TRA); // 实例化一个model对象 没有对应任何数据表
		$sql = "select * from (select * from  t_tbls order by TB_ID desc) a where ".$where." group by VIP_ID order by TB_ID desc";
		$list = $mtbls->query($sql);

		$n = 0;
		foreach ($list as $key => $value) {
			$uptb = $mtbls->where($where.' and TB_FLAG = 1 and VIP_ID = '.$value['VIP_ID'].' and TB_ID < '.$value['TB_ID'])->save(array('TB_FLAG' => 6));
			if ($uptb) {
				$n++;
			}
		}
		echo '共成功'.$n;
		
	}

	//更新VIP会员账户数据（用于对已经投过意外险的，不再生成流水）
	public function change_viplap(){
		$list = M('noinsure', DB_PREFIX_GLA, DB_DSN_GLA)->limit()->select();
		$m_vip = D('GVip');
		$m_lap = M('lap', DB_PREFIX_GLA, DB_DSN_GLA);
		$m_tb = D('TTbls');
		$n = 0;
		$m = 0;
		foreach ($list as $key => $value) {
			$vipdata = $m_vip->findNewsVip('VIP_ID = '.$value['VIP_ID'],'VIP_ID,VIP_MOBILE');
			if (!empty($vipdata['VIP_ID'])) {
				//投保数据
				/*$tb_data = $m_tb->findTbls('VIP_MOBILE = '.$vipdata['VIP_MOBILE'], 'TB_ID,SECURITY_TYPE,VIP_FLAG');
				if (!empty($tb_data['TB_ID'])) {
					echo '手机号为:'.$vipdata['VIP_MOBILE'].'【'.C('SECURITY_TYPE')[$tb_data['SECURITY_TYPE']].'】【'.C('VIP_FLAG')[$tb_data['VIP_FLAG']].'】, 存在重复投保<br />';
					$n++;
				}*/

				//账户数据
				$find_res = $m_lap->where("ACCT_NO = '".setStrzero($value['VIP_ID'], 9)."'")->field('ACCT_NO,ACCT_YWTBAL,ACCT_YWVBAL')->find();
				if (!empty($find_res['ACCT_NO'])) {
					echo '手机号为:'.$vipdata['VIP_MOBILE'].', 当前意外已投总额：'.$find_res['ACCT_YWTBAL'].', 当前意外可投金额：'.$find_res['ACCT_YWVBAL'].'<br />';
					$n++;
				}
				
				$up_res = $m_lap->where("ACCT_NO = '".setStrzero($value['VIP_ID'], 9)."'")->save(array('ACCT_YWTBAL' => 800,'ACCT_YWVBAL' => 0 ));
				if ($up_res) {
					$m++;
				}
			}
		}
		echo '总计匹配：'.$n;
		echo '<br />';
		echo '总计修改：'.$m;
	}

	//匹配未迁入数据是什么原因重复
	public function check_nopassvip(){
		$list = M('nopass', DB_PREFIX_GLA, DB_DSN_GLA)->select();
		$m_vip = D('GVip');
		//$m_lap = M('lap', DB_PREFIX_GLA, DB_DSN_GLA);
		//$m_tb = D('TTbls');
		$n = 0;
		$vip_str = '';
		foreach ($list as $key => $value) {
			$vipdata = $m_vip->findNewsVip('VIP_ID = '.$value['VIP_ID'],'VIP_ID');
			if (!empty($vipdata['VIP_ID'])) {
				$vip_str .= '会员ID('.$value['VIP_ID'].')重复,';
			}

			$vipdata1 = $m_vip->findNewsVip('VIP_MOBILE = "'.$value['VIP_MOBILE'].'"','VIP_ID');
			if (!empty($vipdata1['VIP_ID'])) {
				$vip_str .= '手机号('.$value['VIP_MOBILE'].')重复,';
			}

			$vipdata2 = $m_vip->findNewsVip('CARD_NO = "'.$value['CARD_NO'].'"','VIP_ID');
			if (!empty($vipdata2['VIP_ID'])) {
				$vip_str .= '卡号('.$value['CARD_NO'].')重复,';
			}

			$vipdata3 = $m_vip->findNewsVip('VIP_IDNO = "'.$value['IDNO'].'"','VIP_ID');
			if (!empty($vipdata3['VIP_ID'])) {
				$vip_str .= '会员身份证('.$value['IDNO'].')重复,';
			}

			if (!empty($vip_str)) {
				echo '手机号为:【'.$value['VIP_MOBILE'].'】'.$vip_str.'<br />';
				$n++;
			}
			$vip_str = '';
		}
		echo '总计匹配：'.$n;
	}

	//匹配会员，将其改为付费卡
	public function upcard_type(){
		$post = array(
			'pCountIndex' => I('pagenum'), 
			'pageSize'    => 10
		);
		$limit = ($post['pCountIndex'] - 1)*$post['pageSize'].','.$post['pageSize'];
		$list = M('sbid', DB_PREFIX_GLA, DB_DSN_GLA)->limit($limit)->select();
		$m_vip = D('GVip');
		$m_lap = D('Lap');
		$m_reg = D('MReg');
		$m_feecfg = D('MFeecfg');
		$m_vipcard = D('GVipcard');
		$lap_num = 0;
		$reg_num = 0;
		$feecreg = $m_feecfg->findFeecfg('CFG_FLAG = 2', 'TRAFICC_FEE');
		foreach ($list as $key => $value) {
			//查看匹配多少会员
			$vipdata = $m_vip->findNewsVip('VIP_ID = '.$value['VIP_ID']);
			if (!empty($vipdata['VIP_ID'])) {
				//查看匹配会员中有多少是已经消费的
				$find_res = $m_lap->where("ACCT_NO = '".setStrzero($value['VIP_ID'], 9)."'")->field('ACCT_NO,ACCT_YWTBAL,ACCT_YWVBAL')->find();
				if (empty($find_res['ACCT_NO'])) {
					$lapdata = array(
						'SUBJECT_CODE'	=>	'20101',
						'ACCT_NO'		=>	setStrzero($res['VIP_ID'], 9),
						'ACCT_NAME'		=>	$resdata['VIP_NAME'],
						'ACCT_TYPE'		=>	'V',
						'ACCT_VALBAL'	=>	'0',
						'ACCT_YLTBAL'	=>	'0',
						'ACCT_YLVBAL'	=>	'0',
						'ACCT_YWTBAL'	=>	'0',
						'ACCT_YWVBAL'	=>	'0',
						'ACCT_DIVBAL'	=>	'0',
						'ACCT_CAMT'		=>	'0',
						'ACCT_DAMT'		=>	'0',
						'ACCT_DATE'		=>	date('Ymd'),
						'SYSTEM_TIME'	=>	date('YmdHis'),
						'MAC'			=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',
						'YLT_AMT'		=>	'0',
						'YWT_AMT'		=>	'0',
					);
					$m_lap->addLap($lapdata);
					Add_LOG(CONTROLLER_NAME);
					Add_LOG(CONTROLLER_NAME, 'VIP_ID为:'.$vipdata['VIP_ID'].', 添加：lap表');
					$lap_num++;
				}
				//reg
				$regdata = array(
					'REG_TYPE'	=>	'101',
					'REG_INDEX'	=>	setStrzero($vipdata['VIP_ID'], 9),
					'REG_DESC'	=>	$vipdata['VIP_NAME'],
					'REG_AMT'	=>	$feecreg['TRAFICC_FEE'],
					'MARK_FLAG'	=>	1,
					'MARK_DATE'	=>	date('Ymd'),
				);
				$inreg = $m_reg->addReg($regdata);
				if (!$inreg) {
					Add_LOG(CONTROLLER_NAME);
					Add_LOG(CONTROLLER_NAME, json_encode($regdata));
					Add_LOG(CONTROLLER_NAME, '出错啦');
				}else{
					$reg_num++;
				}
			}
		}
		echo '添加了lap表：'.$lap_num.'个，添加了reg表：'.$reg_num.'个';
	}

	//商户银行数据导出
	public function out_shopdata3(){
		ini_set('memory_limit', '250M'); //内存限制
		set_time_limit(0); //
		//状态
		$where = "sh.SHOP_STATUS = '0'";
		//分页
		$count = D('MSbact')->countNotmpSbact($where);
		$list  = D('MSbact')->getNotmpSbactlist($where, 'sh.SHOP_NO,sh.SHOP_NAME,sb.*','','sh.CREATE_TIME DESC');
		$log_filename = "./Public/file/upload/out_shopdata.txt";
		file_put_contents($log_filename, "共计".$count."条商户记录"."\r\n", FILE_APPEND);
		$shop_data_str = '';
		foreach ($list as $key => $value) {
			//银行帐户组装
			switch ($value['SHOP_BANK_FLAG']) {
			 	case '0':
			 		$bankActType  = $value['SHOP_BANK_FLAG'];								//(银行账户-结算标志【0对公，1对私】)
			 		$bankActName  = $value['BANKACCT_NAME1'];								//(银行账户-户名)
			 		$bankAccount  = $value['BANKACCT_NO1'];									//(银行账户-账户)
			 		$bankNo	  	  = $value['BANKACCT_BID1'];								//(银行账户-开户行联行号)
			 		$bankName	  = $value['BANK_NAME1'];									//(银行账户-开户行)
			 		break;
			 	case '1':
			 		$bankActType  = $value['SHOP_BANK_FLAG'];
			 		$bankActName  = $value['BANKACCT_NAME2'];
			 		$bankAccount  = $value['BANKACCT_NO2'];
			 		$bankNo	  	  = $value['BANKACCT_BID2'];
			 		$bankName	  = $value['BANK_NAME2'];
			 		break;
			}

			$shop_data_str = $value['SHOP_MAP_ID'].','
						   .$value['SHOP_NO'].','										//(商户号)
						   .$value['SHOP_NAME'].','										//(商户名称)
						   .$bankActType.','											//(商户简称)
						   .$bankActName.','											//(商户简称)
						   .$bankAccount.','											//(商户简称)
						   .$bankNo.','													//(商户简称)
						   .$bankName.','												//(商户归属关系)
						   .$value['CREATE_TIME'];										//(商户进件时间)
      		file_put_contents($log_filename, $shop_data_str."\r\n", FILE_APPEND);
      		$shop_data_str = '';
		}
		echo '<h style="text-align:center">结束</h1>';
	}
}