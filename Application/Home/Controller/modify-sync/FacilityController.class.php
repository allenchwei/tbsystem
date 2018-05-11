<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @ljf  设备管理
// +----------------------------------------------------------------------
class FacilityController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->MFactory	= 'MFactory';
		$this->MModel	= 'MModel';
		$this->MSposreq	= 'MSposreq';
		$this->MDevice	= 'MDevice';
		$this->MCity	= 'MCity';
		$this->MShop	= 'MShop';
		$this->MPos		= 'MPos';
		$this->MPosback = 'MPosback';
		$this->MKey		= 'MKey';
		$this->MChannel = 'MChannel';
		$this->MExcel 	= 'MExcel';
	}
	
	/*
	* 设备厂商管理
	**/
	public function factory() {
		$post = I('post');
		if($post['submit'] == "factory"){
			$where = "1=1";
			//厂商名称
			if($post['FACTORY_NAME']) {
				$where .= " and FACTORY_NAME like '%".$post['FACTORY_NAME']."%'";
			}
			//分页
			$count = D($this->MFactory)->countFactory($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MFactory)->getFactorylist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
		$factorystatus = array('正常','锁定','注销');		//公司状态
		$this->assign('factorystatus',$factorystatus);
		\Cookie::set ('_currentUrl_', 	__SELF__);
		$this->display();
	}
	/*
	* 设备厂商管理 详情
	**/
	public function factory_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MFactory)->findFactory("FACTORY_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}	
		$this->assign ('info', $info);
		$this->display();
	}
	/*
	* 设备厂商管理 添加
	**/
	public function factory_add() {
		$post = I('post');
		if($post['submit'] == "factory_add") {
			//验证
			if(empty($post['FACTORY_NAME']) || empty($post['AREA_CODE']) || empty($post['ADDRESS']) || empty($post['MANAGER']) || empty($post['ZIP']) || empty($post['TEL'])){
				$this->wrong("请填写*号必填项！");
			}
			$city_no = $post['AREA_CODE'];
			$city_name = D($this->MCity)->findCity("CITY_S_CODE='".$city_no."'");
			//组装数据
			$factorydata = array(
				'FACTORY_NAME'	=>	$post['FACTORY_NAME'],
				'FACTORY_STATUS'=>	0,
				'CITY_NO'		=>	$city_no,
				'CITY_NAME'		=>	$city_name['CITY_S_NAME'],
				'ADDRESS'		=>	$post['ADDRESS'],
				'ZIP'			=>	$post['ZIP'],
				'MANAGER'		=>	$post['MANAGER'],
				'TEL'			=>	$post['TEL'],
				'WEB'			=>	$post['WEB']
			);
			$res = D($this->MFactory)->addFactory($factorydata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign ('factory_status', 	C('FACTORY_STATUS'));	//地区
		$this->display();
	}	
	/*
	* 设备厂商管理 修改
	**/
	public function factory_edit() {
		$post = I('post');
		if($post['submit'] == "factory_edit") {
			//验证
			if(empty($post['FACTORY_NAME']) || empty($post['AREA_CODE']) || empty($post['ADDRESS']) || empty($post['MANAGER']) || empty($post['ZIP']) || empty($post['TEL'])){
				$this->wrong("请填写*号必填项！");
			}
			$city_no = $post['AREA_CODE'];
			$city_name = D($this->MCity)->findCity("CITY_S_CODE='".$city_no."'");
			//组装数据
			$factorydata = array(
				'FACTORY_NAME'	=>	$post['FACTORY_NAME'],
				'FACTORY_STATUS'=>	$post['FACTORY_STATUS'],
				'CITY_NO'		=>	$city_no,
				'CITY_NAME'		=>	$city_name,
				'ADDRESS'		=>	$post['ADDRESS'],
				'ZIP'			=>	$post['ZIP'],
				'MANAGER'		=>	$post['MANAGER'],
				'TEL'			=>	$post['TEL'],
				'WEB'			=>	$post['WEB']
			);
			$res = D($this->MFactory)->updateFactory("FACTORY_MAP_ID='".$post['FACTORY_MAP_ID']."'", $factorydata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}	
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MFactory)->findFactory("FACTORY_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		
		$this->assign ('factory_status', 	C('FACTORY_STATUS'));	//地区
		$this->assign ('info', 		$info);	
		$this->display('factory_add');
	}
	
	
	
	/*
	* 设备型号管理
	**/
	public function model() {
		$post = I('post');
		if($post['submit'] == "model"){
			$where = "1=1";
			//类型
			if($post['MODEL_TYPE']!='') {
				$where .= " and m.MODEL_TYPE = '".$post['MODEL_TYPE']."'";
			}
			//通讯
			if($post['MODEL_COMM']!='') {
				$where .= " and m.MODEL_COMM = '".$post['MODEL_COMM']."'";
			}
			//结构
			if($post['MODEL_PINPAD']!='') {
				$where .= " and m.MODEL_PINPAD = '".$post['MODEL_PINPAD']."'";
			}
			//打印
			if($post['MODEL_PRINTER']!='') {
				$where .= " and m.MODEL_PRINTER = '".$post['MODEL_PRINTER']."'";
			}
			//厂商
			if($post['FACTORY_MAP_ID']) {
				$where .= " and f.FACTORY_MAP_ID = '".$post['FACTORY_MAP_ID']."'";
			}
			//型号名称
			if($post['MODEL_NAME']) {
				$where .= " and m.MODEL_NAME like '%".$post['MODEL_NAME']."%'";
			}
			//分页
			$count = D($this->MModel)->countModel($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MModel)->getModellist($where, 'm.*,f.FACTORY_MAP_ID,f.FACTORY_NAME', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
		
		\Cookie::set ('_currentUrl_', 	__SELF__);
		//设备配置信息
		$this->assign ('model_type', C('MODEL_TYPE_ARR'));
		//厂商下拉列表数据
		$factorysel = D($this->MFactory)->getFactorylist('','FACTORY_MAP_ID,FACTORY_NAME');
		$this->assign ('factorysel', $factorysel);
		$this->display();
	}	
	/*
	* 设备型号管理 详情
	**/
	public function model_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MModel)->findModel("MODEL_MAP_ID='".$id."'", 'm.*, f.FACTORY_NAME');
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//设备配置信息
		$modeltype = C('MODEL_TYPE_ARR');
		$this->assign ('model_type', $modeltype);
		$this->assign ('info', $info);
		$this->display();
	}
	/*
	* 设备型号管理 添加
	**/
	public function model_add() {
		$post = I('post');
		if($post['submit'] == "model_add") {
			//验证
			if(empty($post['MODEL_NAME'])){
				$this->wrong("缺少设备型号！");
			}
			//组装数据
			$model_data = array(
				'FACTORY_MAP_ID'	=>	$post['FACTORY_MAP_ID'],
				'MODEL_NAME'		=>	$post['MODEL_NAME'],
				'MODEL_STATUS'		=>	$post['MODEL_STATUS'] ? $post['MODEL_STATUS'] : 0,
				'MODEL_TYPE'		=>	$post['MODEL_TYPE'],
				'MODEL_COMM'		=>	$post['MODEL_COMM'],
				'MODEL_PINPAD'		=>	$post['MODEL_PINPAD'] ? $post['MODEL_PINPAD'] : 0,
				'MODEL_PRINTER'		=>	$post['MODEL_PRINTER'] ? $post['MODEL_PRINTER'] : 0,
				'MODEL_PRICE'		=>	$post['MODEL_PRICE'] ? setMoney($post['MODEL_PRICE'], '2') : 0,
				'MODEL_REMARK'		=>	$post['MODEL_REMARK']
			);
			$res = D($this->MModel)->addModel($model_data);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		//厂商下拉列表数据
		$factorysel = D($this->MFactory)->getFactorylist('','FACTORY_MAP_ID,FACTORY_NAME');
		$this->assign ('factorysel', $factorysel);
		//设备配置信息
		$modeltype = C('MODEL_TYPE_ARR');	
		$this->assign ('model_type', $modeltype);
		$this->display();
	}	
	/*
	* 设备型号管理 修改
	**/
	public function model_edit() {
		$post = I('post');
		if($post['submit'] == "model_edit") {
			//验证
			if(empty($post['MODEL_NAME'])){
				$this->wrong("缺少设备型号！");
			}
			//组装数据
			$model_data = array(
				'FACTORY_MAP_ID'	=>	$post['FACTORY_MAP_ID'],
				'MODEL_NAME'		=>	$post['MODEL_NAME'],
				'MODEL_STATUS'		=>	$post['MODEL_STATUS'],
				'MODEL_TYPE'		=>	$post['MODEL_TYPE'],
				'MODEL_COMM'		=>	$post['MODEL_COMM'],
				'MODEL_PINPAD'		=>	$post['MODEL_PINPAD'],
				'MODEL_PRINTER'		=>	$post['MODEL_PRINTER'],
				'MODEL_PRICE'		=>	$post['MODEL_PRICE'] ? setMoney($post['MODEL_PRICE'], '2') : 0,
				'MODEL_REMARK'		=>	$post['MODEL_REMARK']
			);
			$res = D($this->MModel)->updateModel("MODEL_MAP_ID='".$post['MODEL_MAP_ID']."'", $model_data);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MModel)->findModel("MODEL_MAP_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//厂商下拉列表数据
		$factorysel = D($this->MFactory)->getFactorylist('','FACTORY_MAP_ID,FACTORY_NAME');
		$this->assign ('factorysel', $factorysel);
		//设备配置信息
		$modeltype = C('MODEL_TYPE_ARR');	
		$this->assign ('model_type', $modeltype);
		$this->assign ('info', $info);	
		
		$this->display('model_add');
	}
	
	
	/*
	* 设备管理
	**/
	public function device() {
		$post = I('post');
		if($post['submit'] == "device"){
			$where = "1=1";
			$soplv = filter_data('soplv');	//列表查询
			//分公司
			if($soplv['bid'] != '') {
				$where .= " and d.BRANCH_MAP_ID = '".$soplv['bid']."'";
				$post['bid'] = $soplv['bid'];
			}
			//合作伙伴
			if($soplv['pid'] != '') {
				$pids = get_plv_childs($soplv['pid'],1);
				$where .= " and d.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $soplv['pid'];
			}
			//厂商
			if($post['FACTORY_MAP_ID']) {
				$where .= " and d.FACTORY_MAP_ID = '".$post['FACTORY_MAP_ID']."'";
			}
			//型号
			if($post['MODEL_MAP_ID']) {
				$where .= " and d.MODEL_MAP_ID = '".$post['MODEL_MAP_ID']."'";
			}
			//隶属
			if($post['DEVICE_ATTACH'] !='') {
				$where .= " and d.DEVICE_ATTACH = '".$post['DEVICE_ATTACH']."'";
			}
			//状态
			if($post['DEVICE_STATUS'] !='') {
				$where .= " and d.DEVICE_STATUS = '".$post['DEVICE_STATUS']."'";
			}
			//商户号
			if($post['SHOP_NO']) {
				$where .= " and d.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//终端号
			if($post['DEVICE_SN']) {
				$where .= " and d.DEVICE_SN = '".$post['DEVICE_SN']."'";
			}
			//开始时间
			if ($post['SYSTEM_DATE_A']) {
				$where .= " and d.UPDATE_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			}
			//结束时间
			if ($post['SYSTEM_DATE_B']) {
				$where .= " and d.UPDATE_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			}
			//分页
			$count = D($this->MDevice)->countDevice($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MDevice)->getDevicelist($where, 'd.*, m.MODEL_NAME', $p->firstRow.','.$p->listRows,'DEVICE_ID DESC');
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
	
		\Cookie::set ('_currentUrl_', 	__SELF__);
		//厂商下拉列表数据
		$factorysel = D($this->MFactory)->getFactorylist('','FACTORY_MAP_ID,FACTORY_NAME');
		$this->assign ('factorysel', $factorysel);
		//型号下拉列表数据
	/*	if ($post['FACTORY_MAP_ID']) {
			$where_m = 'FACTORY_MAP_ID = '.$post['FACTORY_MAP_ID'];
		}*/
		$modelsel = D($this->MModel)->getModellist('f.FACTORY_MAP_ID = '.$post['FACTORY_MAP_ID'],'m.MODEL_MAP_ID,m.MODEL_NAME');
		$this->assign ('modelsel', $modelsel);
		//设备配置信息
		$device_type = C('DEVICE_TYPE_ARR');
		$this->assign ('device_type', $device_type);
		//设备类型数组
		$model_type = C('MODEL_TYPE_ARR');
		$this->assign ('model_type', $model_type);
		$this->display();
	}
	/*
	* 设备管理 详情
	**/
	public function device_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MDevice)->findDevice("d.DEVICE_ID='".$id."'", 'd.*, f.FACTORY_NAME, m.MODEL_NAME');
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if (!empty($info['SHOP_NO'])) {
			$shop_data = get_shop_data($info['SHOP_NO']);
			$info['SHOP_NAMEABCN'] = $shop_data['SHOP_NAMEABCN'];
		}
		$this->assign ('info', $info);
		$this->display();
	}
	/*
	* 设备管理 添加
	**/
	public function device_add() {
		$post = I('post');
		if($post['submit'] == "device_add") {
			//验证
			if(empty($post['DEVICE_SN']) || empty($post['FACTORY_MAP_ID']) || empty($post['MODEL_MAP_ID'])){
				$this->wrong("缺少设备序列号, 厂商, 设备型号！");
			}
			$finddata = D($this->MDevice)->findDevice("DEVICE_SN='".$post['DEVICE_SN']."'");
			if(!empty($finddata)){
				$this->wrong("该卡设备序列号已经存在！");
			}
			/*$len = 0;
			$strlen = strlen($post['DEVICE_SN']);
			if($strlen < 20){
				$post['DEVICE_SN'] = setStrzero($post['DEVICE_SN'], 20, 'F','r');
			}*/
			$home = session('HOME');
			//组装数据
			$devicedata = array(
				'FACTORY_MAP_ID'	=>	$post['FACTORY_MAP_ID'],
				'MODEL_MAP_ID'		=>	$post['MODEL_MAP_ID'],
				'DEVICE_SN' 		=>	$post['DEVICE_SN'],
				'DEVICE_STATUS'		=>	2,
				'DEVICE_ATTACH'		=>	$post['DEVICE_ATTACH'] ? $post['DEVICE_ATTACH'] : '0',
				'BRANCH_MAP_ID'		=>  '100000',
				'PARTNER_MAP_ID'	=>	'0',
				'SHOP_NO'			=>	'-',
				'POS_NO'			=>	'-',
				'POS_INDEX'			=>	$post['DEVICE_SN'],
				'DEVICE_TOKEN'		=>	setStrzero('', 32, 'F'),
				'DEVICE_ADDRESS'	=>	'-',
				'INSTALL_DATE'		=>	'-',
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'CREATE_TIME'		=>	date("YmdHis"),
				//'UPDATE_USERID'	=>	$home['USER_ID'],
				//'UPDATE_USERNAME'	=>	$home['USER_NAME'],
				//'UPDATE_TIME'		=>	date("YmdHis"),
				'REMARK' 			=>	$post['REMARK']
			);
			$res = D($this->MDevice)->addDevice($devicedata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//厂商下拉列表数据
		$factorysel = D($this->MFactory)->getFactorylist('','FACTORY_MAP_ID,FACTORY_NAME');
		$this->assign ('factorysel', $factorysel);
		//设备配置信息
		$this->assign ('device_type', C('DEVICE_TYPE_ARR'));
		$this->display();
	}

	/*
	* 设备管理 修改
	**/
	public function device_edit() {
		$post = I('post');
		if($post['submit'] == "device_edit") {
			//验证
			if(empty($post['DEVICE_SN']) || empty($post['FACTORY_MAP_ID']) || empty($post['MODEL_MAP_ID'])){
				$this->wrong("缺少设备序列号, 厂商, 设备型号！");
			}
			$finddata = D($this->MDevice)->findDevice("DEVICE_ID != '".$post['DEVICE_ID']."' and DEVICE_SN = '".$post['DEVICE_SN']."'");
			if(!empty($finddata)){
				$this->wrong("该卡设备序列号已经存在！");
			}
			$home = session('HOME');
			//组装数据
			$devicedata = array(
				'FACTORY_MAP_ID'	=>	$post['FACTORY_MAP_ID'],
				'MODEL_MAP_ID'		=>	$post['MODEL_MAP_ID'],
				'DEVICE_SN' 		=>	$post['DEVICE_SN'],
				'POS_INDEX'			=>	$post['DEVICE_SN'],
				'CREATE_USERID'		=>	$home['USER_ID'],
				'CREATE_USERNAME'	=>	$home['USER_NAME'],
				'REMARK' 			=>	$post['REMARK']
			);
			$res = D($this->MDevice)->updateDevice("DEVICE_ID='".$post['DEVICE_ID']."'", $devicedata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MDevice)->findDevice("d.DEVICE_ID='".$id."'",'d.*');
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if ($info['DEVICE_STATUS'] != 2) {
			$this->wrong("当前设备已被安装无法编辑！");
		}
		//厂商下拉列表数据
		$factorysel = D($this->MFactory)->getFactorylist('','FACTORY_MAP_ID,FACTORY_NAME');
		$this->assign ('factorysel', $factorysel);
		//型号下拉列表数据
		$modelsel = D($this->MModel)->getModellist('f.FACTORY_MAP_ID = '.$info['FACTORY_MAP_ID'],'m.MODEL_MAP_ID,m.MODEL_NAME');
		$this->assign ('modelsel', $modelsel);
		//设备配置信息
		$this->assign ('device_type', C('DEVICE_TYPE_ARR'));
		$this->assign ('info', $info);
		$this->display('device_add');
	}

	/*
	* 设备管理 修改
	**/
	public function device_edit_1() {
		$post = I('post');
		if($post['submit'] == "device_edit") {
			//验证
			if(empty($post['DEVICE_SN']) || empty($post['FACTORY_MAP_ID']) || empty($post['MODEL_MAP_ID'])){
				$this->wrong("缺少设备序列号, 厂商, 设备型号！");
			}
			/*$finddata = D($this->MDevice)->findDevice("DEVICE_SN='".$post['DEVICE_SN']."'");
			if(!empty($finddata)){
				$this->wrong("该卡设备序列号已经存在！");
			}*/
			$devplv = get_level_val('devplv');
			if ($devplv['bid'] == '' || $devplv['pid'] == '') {
				$this->wrong("请选择归属！");
			}
			//组装数据
			$devicedata = array(
				'FACTORY_MAP_ID'	=>	$post['FACTORY_MAP_ID'],
				'MODEL_MAP_ID'		=>	$post['MODEL_MAP_ID'],
				'DEVICE_SN' 		=>	$post['DEVICE_SN'],
				'BRANCH_MAP_ID'		=>	$devplv['bid'],
				'PARTNER_MAP_ID'	=>	$devplv['pid'],
				//'SHOP_NO'			=>	$post['SHOP_NO'],
				//'POS_NO'			=>	$post['POS_NO'],
				//'POS_INDEX'		=>	$post['POS_INDEX'],
				'DEVICE_ADDRESS'	=>	$post['DEVICE_ADDRESS'],
				'INSTALL_DATE'		=>	date("YmdHis"),
				
			);
			$res = D($this->MDevice)->updateDevice("DEVICE_ID='".$post['DEVICE_ID']."'", $devicedata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MDevice)->findDevice("d.DEVICE_ID='".$id."'",'d.*');
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		if (empty($info['SHOP_NO']) || $info['SHOP_NO']=='-') {
			$this->wrong("当前设备还未安装无法编辑！");
		}
		//厂商下拉列表数据
		$factorysel = D($this->MFactory)->getFactorylist('','FACTORY_MAP_ID,FACTORY_NAME');
		$this->assign ('factorysel', $factorysel);
		//型号下拉列表数据
		$modelsel = D($this->MModel)->getModellist('f.FACTORY_MAP_ID = '.$info['FACTORY_MAP_ID'],'m.MODEL_MAP_ID,m.MODEL_NAME');
		$this->assign ('modelsel', $modelsel);
		//设备配置信息
		$devicetype = C('DEVICE_TYPE_ARR');
		$this->assign ('device_type', $devicetype);
		$this->assign ('info', $info);
		$this->display('device_edit');
	}
	/*
	* 设备管理 安装开通(初始化)
	**/
	/*public function device_install() {
		//当设备状态为
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('DEVICE_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('DEVICE_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$devstatus = M('device')->where($where)->field('DEVICE_STATUS')->select();
		foreach ($devstatus as $key => $value) {
			if (!in_array($value['DEVICE_STATUS'], array('0'))) {
				$this->wrong('此操作仅限设备为使用状态时执行');
			}
		}
		$res = D($this->MDevice)->updateDevice($where, array('DEVICE_STATUS'=> 1));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}*/
	/*
	* 设备管理 冻结
	**/
	public function device_close() {
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('DEVICE_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('DEVICE_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$devstatus = M('device')->where($where)->field('DEVICE_STATUS')->select();
		foreach ($devstatus as $key => $value) {
			if (!in_array($value['DEVICE_STATUS'], array('0'))) {
				$this->wrong('此操作仅限设备为使用状态执行');
			}
		}
		$res = D($this->MDevice)->updateDevice($where, array('DEVICE_STATUS'=> 8));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 设备管理恢复开通
	**/
	public function device_open() {
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('DEVICE_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('DEVICE_ID'=> array('eq', $ids));
		}
		$devstatus = M('device')->where($where)->field('DEVICE_STATUS')->select();
		foreach ($devstatus as $key => $value) {
			if (!in_array($value['DEVICE_STATUS'], array('8'))) {
				$this->wrong('此操作仅限设备为冻结状态');
			}
		}
		$res = D($this->MDevice)->updateDevice($where, array('DEVICE_STATUS'=> 0));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 设备管理 报修
	**/
	public function device_refund() {
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('DEVICE_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('DEVICE_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$devstatus = M('device')->where($where)->field('DEVICE_STATUS')->select();
		foreach ($devstatus as $key => $value) {
			if (!in_array($value['DEVICE_STATUS'], array('0','1','2'))) {
				$this->wrong('此操作仅限设备为使用,待开通,待分配状态时执行');
			}
		}
		$res = D($this->MDevice)->updateDevice($where, array('DEVICE_STATUS'=> 4));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 设备管理 报废
	**/
	public function device_scrap() {
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('DEVICE_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('DEVICE_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$devstatus = M('device')->where($where)->field('DEVICE_STATUS')->select();
		foreach ($devstatus as $key => $value) {
			if (!in_array($value['DEVICE_STATUS'], array('4'))) {
				$this->wrong('此操作仅限设备为待维修状态时执行');
			}
		}
		$res = D($this->MDevice)->updateDevice($where, array('DEVICE_STATUS'=> 5));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 设备管理 注销
	**/
	public function device_cancel() {
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('DEVICE_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('DEVICE_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$devstatus = M('device')->where($where)->field('DEVICE_STATUS')->select();
		foreach ($devstatus as $key => $value) {
			if (!in_array($value['DEVICE_STATUS'], array('0'))) {
				$this->wrong('此操作仅限当设备为使用状态时执行');
			}
		}
		$res = D($this->MDevice)->updateDevice($where, array('DEVICE_STATUS'=> 9));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 设备管理 (安装开通)初始化
	**/
	public function device_int() {
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('DEVICE_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('DEVICE_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$devstatus = M('device')->where($where)->field('DEVICE_STATUS')->select();
		foreach ($devstatus as $key => $value) {
			if (!in_array($value['DEVICE_STATUS'], array('0'))) {
				$this->wrong('此操作仅限当设备为使用状态时执行');
			}
		}
		$res = D($this->MDevice)->updateDevice($where, array('DEVICE_STATUS' => 1, 'DEVICE_TOKEN' => 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF'));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 设备管理 回库
	**/
	public function device_back() {
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('DEVICE_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('DEVICE_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$devstatus = M('device')->where($where)->field('DEVICE_STATUS,DEVICE_SN')->select();
		foreach ($devstatus as $key => $value) {
			if (!in_array($value['DEVICE_STATUS'], array('4'))) {
				$this->wrong('此操作仅限当设备为待维修状态时执行');
			}
		}
		//组装数据
		$devicedata = array(
			'DEVICE_STATUS'		=>	2,
			'BRANCH_MAP_ID'		=>  '100000',
			'PARTNER_MAP_ID'	=>	'0',
			'SHOP_NO'			=>	'-',
			'POS_NO'			=>	'-',
			'DEVICE_TOKEN'		=>	setStrzero('', 32, 'F'),
			'DEVICE_ADDRESS'	=>	'-',
			'INSTALL_DATE'		=>	'-',
			'CREATE_TIME'		=>	date("YmdHis")
		);
		$res = D($this->MDevice)->updateDevice($where, $devicedata);
		if($res['state'] != 0){
			$this->wrong('操作失败');
		}
		//删除商户POS数据
		$dev_sn_arr = i_array_column($devstatus,'DEVICE_SN');
		$map['DEVICE_SN']  = array('in',$dev_sn_arr);
		D($this->MPos)->delPos($map);
		$this->right('操作成功', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 设备管理 Excel导出
	**/
	public function device_export() {
		$post  = array(
			'BRANCH_MAP_ID'		=>	I('bid'),
			'PARTNER_MAP_ID'	=>	I('pid'),
			'SYSTEM_DATE_A'		=>	I('SYSTEM_DATE_A') ? I('SYSTEM_DATE_A') : '',
			'SYSTEM_DATE_B'		=>	I('SYSTEM_DATE_B') ? I('SYSTEM_DATE_B') : '',
			'FACTORY_MAP_ID'	=>	I('FACTORY_MAP_ID'),
			'MODEL_MAP_ID'		=>	I('MODEL_MAP_ID'),
			'DEVICE_STATUS'		=>	I('DEVICE_STATUS'),
			'SHOP_NO'			=>	I('SHOP_NO'),
			'POS_NO'			=>	I('POS_NO')
		);
		$where = "1=1";
		//分支
		if($post['BRANCH_MAP_ID'] != '') {
			$where .= " and d.BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
		}
		//合作伙伴
		if($post['PARTNER_MAP_ID'] != '') {
			$pids = get_plv_childs($post['PARTNER_MAP_ID'],1);
			$where .= " and d.PARTNER_MAP_ID in(".$pids.")";
		}
		//厂商
		if($post['FACTORY_MAP_ID']) {
			$where .= " and d.FACTORY_MAP_ID = '".$post['FACTORY_MAP_ID']."'";
		}
		//型号
		if($post['MODEL_MAP_ID']) {
			$where .= " and d.MODEL_MAP_ID = '".$post['MODEL_MAP_ID']."'";
		}
		//状态
		if($post['DEVICE_STATUS'] !='') {
			$where .= " and d.DEVICE_STATUS = '".$post['DEVICE_STATUS']."'";
		}
		//商户号
		if($post['SHOP_NO']) {
			$where .= " and d.SHOP_NO = '".$post['SHOP_NO']."'";
		}
		//终端号
		if($post['POS_NO']) {
			$where .= " and d.POS_NO = '".$post['POS_NO']."'";
		}
		//开始时间
		if ($post['SYSTEM_DATE_A']) {
			$where .= " and d.UPDATE_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
		}
		//结束时间
		if ($post['SYSTEM_DATE_B']) {
			$where .= " and d.UPDATE_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
		}

		//计算
		$count = D($this->MDevice)->countDevice($where);
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
			$list  = D($this->MDevice)->getDevicelist($where, 'd.*, m.MODEL_NAME', $bRow.','.$eRow,'DEVICE_ID DESC');
			//导出操作
			$xlsname = '设备管理列表数据';
			$xlscell = array(
				array('MODEL_NAME',		'型号名称'),
				array('DEVICE_SN',		'序列号'),
				array('DEVICE_STATUS',	'设备状态'),
				array('GUISHU',			'归属'),
				array('SHOP_NAME',		'商户名称'),
				array('SHOP_NAMEABCN',	'商户简称'),
				array('SHOP_NO',		'安装商户号'),
				array('POS_NO',			'安装终端号'),
				array('INSTALL_DATE',	'安装时间')
			);
			$xlsarray = array();
			foreach($list as $val){
				//获取商户名称
				$shopdata  = D($this->MShop)->findShop('SHOP_NO = "'.$val['SHOP_NO'].'"','SHOP_NAME,SHOP_NAMEABCN');
				$xlsarray[] = array(
					'MODEL_NAME'	=>	$val['MODEL_NAME']."\t",
					'DEVICE_SN'		=>	$val['DEVICE_SN']."\t",
					'DEVICE_STATUS'	=>	C('DEVICE_TYPE_ARR.DEVICE_STATUS')[$val['DEVICE_STATUS']],
					'GUISHU'		=>	get_level_name($val['PARTNER_MAP_ID'], $val['BRANCH_MAP_ID']),
					'SHOP_NAME'		=>	$shopdata['SHOP_NAME'],
					'SHOP_NAMEABCN'	=>	$shopdata['SHOP_NAMEABCN'],
					'SHOP_NO'		=>	$val['SHOP_NO']."\t",
					'POS_NO'		=>	$val['POS_NO']."\t",
					'INSTALL_DATE'	=>	$val['INSTALL_DATE']
				);	
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		$this->display('Public/export');
	}
	
	/*
	* 设备装机管理
	**/
	public function diy() {
		$post = I('post');
		if($post['submit'] == "diy"){
			$sellv = filter_data('soplv');	//列表查询
			$where = 'sp.INSTALL_FLAG != "0"';
			//归属
			if($sellv['bid'] !='') {
				$where .= " and sp.BRANCH_MAP_ID = '".$sellv['bid']."'";
				$post['bid'] = $sellv['bid'];
			}
			if($sellv['pid'] !='') {
				$pids = get_plv_childs($sellv['pid'],1);
				$where .= " and sp.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $sellv['pid'];
			}
			//商户号
			if($post['SHOP_NO']) {
				$where .= " and s.SHOP_NO = '".$post['SHOP_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and s.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			//开始时间
			if ($post['SYSTEM_DATE_A']) {
				$where .= " and sp.CREATE_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			}
			//结束时间
			if ($post['SYSTEM_DATE_B']) {
				$where .= " and sp.CREATE_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			}
			//分页
			$count = D($this->MSposreq)->countSposreq($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MSposreq)->getSposreqlist($where, 'sp.SMODEL_ID,sp.APPLY_DATE, sp.NUM,sp.INSTALL_FLAG, s.SHOP_MAP_ID,s.SHOP_NO, s.SHOP_NAME, s.CITY_NO, a.PARTNER_NAME,m.MODEL_NAME', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			$this->assign ( 'install_status', array('0' => '已装机','1' => '申请受理中','2' => '已拒绝'));	//装机状态 0:完成  1:申请受理中
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set ('_currentUrl_', 	__SELF__);
		//厂商下拉列表数据
		$factorysel = D($this->MFactory)->getFactorylist('','FACTORY_MAP_ID,FACTORY_NAME');
		$this->assign ('factorysel', $factorysel);
		//型号下拉列表数据
		$modelsel = D($this->MModel)->getModellist('','MODEL_MAP_ID,MODEL_NAME');
		$this->assign ('modelsel', $modelsel);
		//设备配置信息
		$device_type = C('DEVICE_TYPE_ARR');
		$this->assign ('device_type', $device_type);
		//设备类型数组
		$model_type = C('MODEL_TYPE_ARR');
		$this->assign ('model_type', $model_type);
		$this->display();
	}
	/*
	* 设备装机管理
	**/
	public function diy_add() {
		$post = I('post');
		$dev  = I('dev');
		if($post['submit'] == "diy_add") {
			//验证
			if(empty($dev)){
				$this->wrong("缺少设备序列号！");
			}
			/*$len = 0;
			$strlen = strlen($post['DEVICE_SN']);
			if($strlen < 20){
				$post['DEVICE_SN'] = setStrzero($post['DEVICE_SN'], 20, 'F','r');
			}*/
			//判断设备序列号是否重复
			$dev_arr = array_column($dev,'DEVICE_SN');
			$new_dev_arr = array_unique($dev_arr);
			if (count($dev_arr) != count($new_dev_arr)) {
				$this->wrong("设备序列号有重复,请重新填写, 受理失败！");
			}
			foreach ($dev as $key => $value) {
				$finddata = D($this->MDevice)->findDevice("DEVICE_SN='".$value['DEVICE_SN']."' and POS_NO = '-' and DEVICE_STATUS = 2");
				if(empty($finddata)){
					$this->wrong("第".($key+1)."个设备序列号不存在或该设备已经被使用, 受理失败！");
				}
				if ($finddata['FACTORY_MAP_ID'] != $post['FACTORY_MAP_ID'] || $finddata['MODEL_MAP_ID'] != $post['MODEL_MAP_ID']) {
					$this->wrong("当前设备与商户申请设备不匹配, 受理失败！");
				}
			}
			$home = session('HOME');
			$m = M();
			$m->startTrans();//开启事务
			//批量修改
			foreach ($dev as $key => $val) {
				$dev_no = $val['DEVICE_SN'];
				$pos_no = $val['POS_NO'];
				$devno_res = D($this->MPos)->findPos('DEVICE_SN = "'.$dev_no.'"','POS_ID');
				if ($devno_res['POS_ID']){
					$this->wrong('该设备序列号已经存在，请换一个再试！');
				}
				//组装数据
				$devicedata = array(
					//'FACTORY_MAP_ID'	=>	$post['FACTORY_MAP_ID'],
					//'MODEL_MAP_ID'		=>	$post['MODEL_MAP_ID'],
					'DEVICE_STATUS'		=>	1,
					'DEVICE_ATTACH'		=>	0,
					'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],
					'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],
					'SHOP_NO'			=>	$post['SHOP_NO'],
					'POS_NO'			=>	$pos_no,
					'DEVICE_ADDRESS'	=>	$post['DEVICE_ADDRESS'],
					'INSTALL_DATE'		=>	date("YmdHis"),
					'CREATE_USERID'		=>	$home['USER_ID'],
					'CREATE_USERNAME'	=>	$home['USER_NAME'],
					'CREATE_TIME'		=>	date("YmdHis"),
					'UPDATE_USERID'		=>	$home['USER_ID'],
					'UPDATE_USERNAME'	=>	$home['USER_NAME'],
					'UPDATE_TIME'		=>	date("YmdHis"),
					'REMARK' 			=>	'装机受理'
				);
				$dev_res = D($this->MDevice)->updateDevice("DEVICE_SN='".$dev_no."'", $devicedata);
				if($dev_res['state'] != 0){
					$m->rollback();//不成功，则回滚
					$this->wrong('设备基本信息更新失败');
				}
				//插入POS表
				$pos_data = array(
					'BRANCH_MAP_ID'		=>	$post['BRANCH_MAP_ID'],				//分支编号
					'PARTNER_MAP_ID'	=>	$post['PARTNER_MAP_ID'],			//代理编号
					'SHOP_MAP_ID'		=>	$post['SHOP_MAP_ID'],				//商户ID
					'SHOP_NO'			=>	$post['SHOP_NO'],					//商户编号
					'POS_NO'			=>	$pos_no,							//终端号
					'POS_INDEX'			=>	$post['SHOP_NO'].$pos_no,			//备用索引
					'DEVICE_SN'			=>	$dev_no,							//设备序列号
					'POS_STATUS'		=>	0,									//状态
					'POS_PBOCKEYFLAG'	=>	0,									//IC公钥更新标志
					'POS_PBOCPARAFLAG'	=>	0,									//IC参数更新标志
					'POS_PARAFLAG'		=>	0,									//参数更新标志
					'POS_PROGFLAG'		=>	0,									//程序更新标志
					'POS_CURPROGVER'	=>	0,									//终端程序当前版本号
					'POS_NEWPROGVER'	=>	0,									//终端程序最新版本号
					'POS_HMDFLAG'		=>	0,									//IC黑名单更新标志
					'POS_BATCH'			=>	00001,								//批次号
					'POS_TRACE'			=>	00001,								//流水号
					'POS_TIMEOUT'		=>	60,									//交易超时时间
					'POS_MAXAMT'		=>	10000000,							//单笔交易金额上限
					'POS_MAXCNT'		=>	500,								//累计笔数
					'POS_CARDFLAG'		=>	11,									//刷卡标志
					'POS_PINFLAG'		=>	000,								//密码标志
					'POS_COMM_RETRY'	=>	3,									//通讯重试次数
					'POS_CONFIRM_MODE'	=>	11,									//预授权完成方式
					'POS_TRANS_DEFAULT'	=>	1,									//默认交易支持
					'POS_TIP'			=>	0,									//小费支持
					'POS_TIP_PER'		=>	0,									//小费百分比
					'POS_MAN_MODE'		=>	0,									//手工输入卡号
					'POS_TRANS_RETRY'	=>	3,									//交易重发次数
					'POS_MAXREFUNDAMT'	=>	99999999,							//退货交易金额上限
					'POS_ECHOTIME'		=>	3600,								//回响周期
					'POS_LOGOUT'		=>	1,									//允许自动签退
					'POS_TICKETNUMS'	=>	2,									//打印票据单数
					'COM_INDEX'			=>	$post['COM_INDEX'] ? $post['COM_INDEX']:'' ,	//通讯参数索引
					'KEY_INDEX'			=>	$post['SHOP_NO'].$pos_no,			//对称密钥索引
				);
				$pos_res = D($this->MPos)->addPos($pos_data);
				if ($pos_res['state'] != 0){
					$m->rollback();//不成功，则回滚
					$this->wrong('商户POS数据添加失败');
				}
				//添加key数据
				$key_data = array(
					'HOST_MAP_ID'		=>	0,											//主机编号
					'HOST_NAME'			=>	'平台',										//中文名称
					'KEY_INDEX'			=>	$post['SHOP_NO'].$pos_no,					//对称密钥索引
					'KEY_MKINDEX'		=>	0,											//保护密钥在加密机索引
					'KEY_KEKINDEX'		=>	0,											//主密钥在加密机索引
					'KEY_KEK'			=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',			
					'KEY_PINKEY'		=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',			
					'KEY_PINVALUE'		=>	'FFFFFFFF',									
					'KEY_MACKEY'		=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',			
					'KEY_MACVALUE'		=>	'FFFFFFFF',									
					'KEY_TRACKKEY'		=>	'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',			
					'KEY_TRACKVALUE'	=>	'FFFFFFFF'									
				);
				$key_res = D($this->MKey)->findKey('KEY_INDEX = "'.$key_data['KEY_INDEX'].'"');
				if (empty($key_res)) {
					$k_res = D($this->MKey)->addKey($key_data);
					if ($k_res['state'] != 0){
						$m->rollback();//不成功，则回滚
						$this->wrong('商户KEY数据添加失败');
					}
				}
				
				//修改商户POS需求信息 状态为正常
				$upspos = D($this->MSposreq)->updateSposreq('SMODEL_ID = '.$post['SMODEL_ID'],array('INSTALL_FLAG' => 0));
				if ($upspos['state'] != 0){
					$m->rollback();//不成功，则回滚
					$this->wrong('商户POS申请变更失败');
				}
			}
			$m->commit();//成功则提交
			$this->right($pos_res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$sposreq_info = D($this->MSposreq)->findSposreq('SMODEL_ID='.$id,'SMODEL_ID,SHOP_MAP_ID,FACTORY_MAP_ID,MODEL_MAP_ID,NUM');
		//获取商户基本信息
		$shop_info = D($this->MShop)->findmoreShop("s.SHOP_MAP_ID='".$sposreq_info['SHOP_MAP_ID']."'",'s.*, a.PARTNER_NAME,b.BRANCH_NAME');
		
		//获取当前最新的POS_NO
		$pos_res = D($this->MPos)->findPos('','POS_NO','CAST(`POS_NO` AS DECIMAL) DESC');
		$this->assign('shop_info',$shop_info);
		$this->assign('sposreq_info',$sposreq_info);
		$this->assign('pos_no',$pos_res['POS_NO']);
		$this->display();
	}
	
	/*
	* 设备装机管理 拒绝
	**/
	public function diy_nopass() {
		$ids = $_REQUEST['id'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('SMODEL_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('SMODEL_ID'=> array('eq', $ids));
		}
		//判断操作项状态
		$upspos = D($this->MSposreq)->updateSposreq($where,array('INSTALL_FLAG' => 2));
		if($upspos['state'] != 0){
			$this->wrong($upspos['msg']);
		}
	//	$this->diy();
		$this->right('操作成功', '_currentUrl_', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);

	//	$this->right('操作成功', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	
	/*
	* 设备统计
	**/
	public function statistics() {
		$post = I('post');
		if($post['submit'] == "statistics"){
			$where = "1=1";
			//厂商
			if($post['FACTORY_MAP_ID']) {
				$where .= " and d.FACTORY_MAP_ID = '".$post['FACTORY_MAP_ID']."'";
			}
			//型号
			if($post['MODEL_MAP_ID']) {
				$where .= " and d.MODEL_MAP_ID = '".$post['MODEL_MAP_ID']."'";
			}
			//状态
			if($post['DEVICE_STATUS'] !='') {
				$where .= " and d.DEVICE_STATUS = '".$post['DEVICE_STATUS']."'";
			}
			
			$field = 'count(d.MODEL_MAP_ID) as sumnum,d.*, m.MODEL_NAME, m.MODEL_TYPE, f.FACTORY_NAME';
			//分页
			$count = D($this->MDevice)->countDevicelist($where, $field, '', 'm.MODEL_MAP_ID', 'sumnum desc');
			$count = count($count);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MDevice)->countDevicelist($where, $field, $p->firstRow.','.$p->listRows, 'm.MODEL_MAP_ID', 'sumnum desc');
			
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
		
		//厂商下拉列表数据
		$factorysel = D($this->MFactory)->getFactorylist('','FACTORY_MAP_ID,FACTORY_NAME');
		//型号下拉列表数据
		$modelsel 	= D($this->MModel)->getModellist('','MODEL_MAP_ID,MODEL_NAME');
		
		$this->assign('factorysel', 		$factorysel);
		$this->assign('modelsel', 			$modelsel);
		$this->assign('dev_status', 		C('DEVICE_TYPE_ARR.DEVICE_STATUS'));	//设备配置信息
		\Cookie::set('_currentUrl_', 		__SELF__);							
		$this->display();	
	}

	/*
	* 本地装机管理
	**/
	public function channelpos() {
		$post = I('post');
		if($post['submit'] == "channelpos"){
			$sellv = filter_data('soplv');	//列表查询
			$where = '1=1';
			//归属渠道
			if($post['CHANNEL_MAP_ID'] !='') {
				$where .= " and pb.CHANNEL_MAP_ID = '".$post['CHANNEL_MAP_ID']."'";
			}
			//归属
			if($sellv['bid'] !='') {
				$where .= " and s.BRANCH_MAP_ID = '".$sellv['bid']."'";
				$post['bid'] = $sellv['bid'];
			}
			if($sellv['pid'] !='') {
				$pids = get_plv_childs($sellv['pid'],1);
				$where .= " and s.PARTNER_MAP_ID in(".$pids.")";
				$post['pid'] = $sellv['pid'];
			}
			//商户号
			if($post['SHOP_NO']) {
				$where .= " and pb.SHOP_NO1 = '".$post['SHOP_NO']."'";
			}
			//终端号
			if($post['POS_NO']) {
				$where .= " and pb.POS_NO = '".$post['POS_NO']."'";
			}
			//商户名称
			if($post['SHOP_NAME']) {
				$where .= " and s.SHOP_NAME like '%".$post['SHOP_NAME']."%'";
			}
			/*//开始时间
			if ($post['SYSTEM_DATE_A']) {
				$where .= " and sp.CREATE_TIME >= '".date('YmdHis',strtotime($post['SYSTEM_DATE_A'].' 00:00:00'))."'";
			}
			//结束时间
			if ($post['SYSTEM_DATE_B']) {
				$where .= " and sp.CREATE_TIME <= '".date('YmdHis',strtotime($post['SYSTEM_DATE_B'].' 23:59:59'))."'";
			}*/
			//分页
			$count = D($this->MPosback)->countPosback($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MPosback)->getPosbacklist($where, '', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post);
			$this->assign ( 'list', 		$list );
		}
	
		\Cookie::set ('_currentUrl_', 	__SELF__);
		$this->display();
	}

	/*
	* 设备装机管理
	**/
	public function channelpos_add() {
		$post = I('post');
		if($post['submit'] == "channelpos_add") {
			//验证
			if(empty($post['CHANNEL_MAP_ID']) || empty($post['POS_NO']) || empty($post['SHOP_NO1'])|| empty($post['SHOP_NO2'])){
				$this->wrong("缺少参数！");
			}
			if(strlen($post['SHOP_NO1']) !=15 || strlen($post['SHOP_NO2']) !=15 ){
				$this->wrong("商户号长度必须15位！");
			}
			$shop_res = D($this->MShop)->findShop('SHOP_NO = "'.$post['SHOP_NO1'].'" and SHOP_STATUS = 0');
			if (empty($shop_res['SHOP_MAP_ID'])){
				$this->wrong('平台商户号不存在');
			}
			$where = 'POS_NO = "'.$post['POS_NO'].'" and SHOP_NO1 = "'.$post['SHOP_NO1'].'"';
			/*$pos_res = D($this->MPos)->findPos($where);
			if (!empty($pos_res)){
				$this->wrong('商户POS中的终端号与平台商户号已经存在，不可重复添加');
			}*/
			
			$posback_info = D($this->MPosback)->findPosback($where);
			if (!empty($posback_info)){
				$this->wrong('商户POS中的终端号与平台商户号已经存在，不可重复添加');
			}
			/*//插入POS表
			$pos_data = array(
				'BRANCH_MAP_ID'		=>	$shop_res['BRANCH_MAP_ID'],				//分支编号
				'PARTNER_MAP_ID'	=>	$shop_res['PARTNER_MAP_ID'],			//代理编号
				'SHOP_MAP_ID'		=>	$shop_res['SHOP_MAP_ID'],				//商户ID
				'SHOP_NO'			=>	$post['SHOP_NO1'],						//商户编号
				'POS_NO'			=>	$post['POS_NO'],						//终端号
				'POS_INDEX'			=>	$post['SHOP_NO1'].$post['POS_NO'],		//备用索引
				'DEVICE_SN'			=>	substr($post['SHOP_NO1'].$post['POS_NO'], -20),		//设备序列号
				'POS_STATUS'		=>	0,										//状态
				'POS_PBOCKEYFLAG'	=>	0,										//IC公钥更新标志
				'POS_PBOCPARAFLAG'	=>	0,										//IC参数更新标志
				'POS_PARAFLAG'		=>	0,										//参数更新标志
				'POS_PROGFLAG'		=>	0,										//程序更新标志
				'POS_CURPROGVER'	=>	0,										//终端程序当前版本号
				'POS_NEWPROGVER'	=>	0,										//终端程序最新版本号
				'POS_HMDFLAG'		=>	0,										//IC黑名单更新标志
				'POS_BATCH'			=>	00001,									//批次号
				'POS_TRACE'			=>	00001,									//流水号
				'POS_TIMEOUT'		=>	60,										//交易超时时间
				'POS_MAXAMT'		=>	10000000,								//单笔交易金额上限
				'POS_MAXCNT'		=>	500,									//累计笔数
				'POS_CARDFLAG'		=>	11,										//刷卡标志
				'POS_PINFLAG'		=>	000,									//密码标志
				'POS_COMM_RETRY'	=>	3,										//通讯重试次数
				'POS_CONFIRM_MODE'	=>	11,										//预授权完成方式
				'POS_TRANS_DEFAULT'	=>	1,										//默认交易支持
				'POS_TIP'			=>	0,										//小费支持
				'POS_TIP_PER'		=>	0,										//小费百分比
				'POS_MAN_MODE'		=>	0,										//手工输入卡号
				'POS_TRANS_RETRY'	=>	3,										//交易重发次数
				'POS_MAXREFUNDAMT'	=>	99999999,								//退货交易金额上限
				'POS_ECHOTIME'		=>	3600,									//回响周期
				'POS_LOGOUT'		=>	1,										//允许自动签退
				'POS_TICKETNUMS'	=>	2,										//打印票据单数
				'COM_INDEX'			=>	'' ,									//通讯参数索引
				'KEY_INDEX'			=>	$post['SHOP_NO1'].$post['POS_NO'],		//对称密钥索引
			);*/
			$m = M();
			$m->startTrans();	//启用事务
			/*$pos_res = D($this->MPos)->addPos($pos_data);
			if ($pos_res['state'] != 0){
				$this->wrong('商户POS数据添加失败');
			}*/
			$posback_info = array(
				//'POS_ID' 			=> $pos_res['POS_ID'], 
				'POS_NO' 			=> $post['POS_NO'], 
				'POS_TYPE' 			=> '', 
				'SHOP_NO1' 			=> $post['SHOP_NO1'], 
				'SHOP_NO2' 			=> $post['SHOP_NO2'], 
				'CHANNEL_MAP_ID'	=> $post['CHANNEL_MAP_ID'],				//POS渠道id
				'PRICE' 			=> $post['PRICE'] ? setMoney($post['PRICE']) : '0', 
				'DEPOSIT' 			=> $post['DEPOSIT'] ? setMoney($post['DEPOSIT']) : '0', 
				'RENTAL' 			=> $post['RENTAL'] ? setMoney($post['RENTAL']) : '0',
				'CREATE_TIME' 		=> date('YmdHis'),
				'UPDATE_TIME' 		=> date('YmdHis')
			);
			$posback_res = D($this->MPosback)->addPosback($posback_info);
			if ($posback_res['state'] != 0){
				$m->rollback();//不成功，则回滚
				$this->wrong('商户POS信息数据添加失败');
			}
			//修改商户扣率
			$jfl = set_jifenlv($post['SHOP_NO1'], '1');
			$smdr = I('smdr');
			$smdr_data = array(
				'JFB_PER_FEE'		=>	$smdr['JFB_PER_FEE'] ? setMoney($smdr['JFB_PER_FEE'],'2') : '0',	//积分宝比例扣率线(万分比)
				'JFB_FIX_FEE'		=>	$smdr['JFB_FIX_FEE'] ? setMoney($smdr['JFB_FIX_FEE'], '2') : '0',	//积分宝封顶扣率线(单位分)
				'PER_FEE'			=>	$smdr['PER_FEE'] ? setMoney($smdr['PER_FEE'],'2') : '0',			//商户比例扣线(万分比)
				'FIX_FEE'			=>	$smdr['FIX_FEE'] ? setMoney($smdr['FIX_FEE'],'2') : '0',			//商户封顶扣率线(单位分)
			);
			//这个函数 是专门用来对比高精度数字的,第一个参数大于第二个返回1,相等返回0,第二个参数大于第一个参数返回-1,第三个参数代表小数位数
			$new_per = $smdr['JFB_PER_FEE']+$smdr['PER_FEE'];
			$old_per = $jfl*100;
			if(bccomp(floatval($new_per), floatval($old_per), 4) !=0) {
				$m->rollback();//不成功，则回滚
				$this->wrong('银行卡扣率必须等于积分宝收单扣率(商户收单扣率+积分宝扣率 必须等于 '.($jfl*100).')');
			}
			$smdr_update = M('smdr')
				->data($smdr_data)
				->where("SHOP_MAP_ID = '".$shop_res['SHOP_MAP_ID']."' and PAY_TYPE = 0")
				->save();
			if($smdr_update === false) {
				$m->rollback();//不成功，则回滚
				$this->wrong('商户扣率信息数据添加失败');
			}
			//查询商户扣率
			$smdr_data = M('smdr')
				->field('SHOP_MAP_ID, PAY_TYPE, JFB_PER_FEE, JFB_FIX_FEE, PER_FEE, FIX_FEE')
				->where("SHOP_MAP_ID = '".$shop_res['SHOP_MAP_ID']."' and PAY_TYPE in(5,0)")
				->order("locate(PAY_TYPE,'5,0')")
				->select();
			$jfb_smdr = $smdr_data[0];
			$yl_smdr  = $smdr_data[1];
			//如果银行卡扣率存在，那么按银行卡的扣率，否则走现金的积分宝扣率
			if ($yl_smdr['PER_FEE']>0 || $yl_smdr['JFB_PER_FEE']>0) {
				$per_fee 	  = $yl_smdr['PER_FEE']/10000;		//商户扣率【银行卡】
				$fix_fee  	  = $yl_smdr['FIX_FEE']/100;		//商户封顶【银行卡】
				$jfb_per_fee  = $yl_smdr['JFB_PER_FEE']/10000;	//积分宝扣率【银行卡】
				$jfb_fix_per  = $yl_smdr['JFB_FIX_FEE']/100;	//积分宝封顶【银行卡】
			}else{
				$per_fee 	  = $jfb_smdr['PER_FEE']/10000;		//商户扣率【积分宝】
				$fix_fee  	  = $jfb_smdr['FIX_FEE']/100;		//商户封顶【积分宝】
				$jfb_per_fee  = $jfb_smdr['JFB_PER_FEE']/10000;	//积分宝扣率【积分宝】
				$jfb_fix_per  = $jfb_smdr['JFB_FIX_FEE']/100;	//积分宝封顶【积分宝】
			}

			//查询商户权限
			$sauth_data = D('MSauth')->findSauth("SHOP_MAP_ID='".$shop_res['SHOP_MAP_ID']."'",'CASH_MAXAMT,AUTH_TRANS_MAP');

			$auth_trans = str_split($sauth_data['AUTH_TRANS_MAP']);
			//同步数据
			$syc_arr = array(
				'operateType' 	=>	1,										//(操作类型)
				'oId'		 	=>	$shop_res['SHOP_MAP_ID'],				//(商户ID)
				'oName'		 	=>	$shop_res['SHOP_NAME'],					//(组织名)
				'oSimpleName' 	=>	$shop_res['SHOP_NAMEABCN'],				//(组织简称)
				'pointRatio'  	=>	$jfb_per_fee,							//(积分比率)
				'pointHighest'  =>	$jfb_fix_per,							//(积分封顶金额，单位元)
				'umsRation' 	=>	$per_fee,								//(银联手续费率)
				'umsLowest' 	=>	0,										//(银联保底手续费，单位元)
				'umsHighest' 	=>	$fix_fee,								//(银联封顶手续费，单位元)
				'cashPermit' 	=>	$auth_trans[3] ? 0 : 1,					//(权限-现金消费)【权限开通】现金消费。（积分宝规则，0：开放；1：关闭）
				'cashLimit' 	=>	$sauth_data['CASH_MAXAMT']/100,			//(单笔现金限额，单位元)
				'shopId' 		=>	$post['SHOP_NO2'],						//(商户号)
				'termId' 		=>	$post['POS_NO'],						//(商户终端号)
				'token' 		=>	strtoupper(md5(strtoupper(md5($shop_res['SHOP_MAP_ID'].'1'))))	//(签名）oId+ operateType 双次MD5
			);

			//同步
			$url = 'http://jiesuanif.jfb315.cn:8056/PointRepositoryOpenLibrary/ShopInfroSynchService';
			Add_LOG(CONTROLLER_NAME, json_encode($syc_arr));
/*
			$resjson = httpPostForm($url, $syc_arr);
			Add_LOG(CONTROLLER_NAME, $resjson);
			$result = json_decode($resjson);
			if ($result->status != '0') {
				$m->rollback();	//回滚
				$this->wrong('商户第三方POS数据同步修改失败');
			}
*/
			$m->commit();	//成功，则提交
			$this->right($posback_res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//渠道列表
		$channel_list = D($this->MChannel)->getChannellist("CHANNEL_STATUS = 0 and CHANNEL_MAP_ID > 0", 'CHANNEL_MAP_ID,CHANNEL_NAME');
		$this->assign ('channel_list', 		$channel_list );			//渠道列表
		$this->display();
	}

	/*
	* 设备管理 修改
	**/
	public function channelpos_edit() {
		$post = I('post');
		if($post['submit'] == "channelpos_edit") {
			//验证
			if(empty($post['CHANNEL_MAP_ID']) || empty($post['POS_NO']) || empty($post['SHOP_NO1'])|| empty($post['SHOP_NO2'])){
				$this->wrong("缺少参数！");
			}
			if(strlen($post['SHOP_NO1']) !=15){
				$this->wrong("平台商户号长度必须15位！");
			}
			$shop_res = D($this->MShop)->findShop('SHOP_NO = "'.$post['SHOP_NO1'].'"');
			if (empty($shop_res)) {
				$this->wrong('平台商户号不存在');
			}
			/*$pos_data = array(
				'BRANCH_MAP_ID'		=>	$shop_res['BRANCH_MAP_ID'],				//分支编号
				'PARTNER_MAP_ID'	=>	$shop_res['PARTNER_MAP_ID'],			//代理编号
				'SHOP_MAP_ID'		=>	$shop_res['SHOP_MAP_ID'],				//商户ID
				'SHOP_NO'			=>	$post['SHOP_NO1'],						//商户编号
				'POS_NO'			=>	$post['POS_NO'],						//终端号
				'POS_INDEX'			=>	$post['SHOP_NO1'].$post['POS_NO'],		//备用索引
				'DEVICE_SN'			=>	substr($post['SHOP_NO1'].$post['POS_NO'], -20),		//设备序列号
				'KEY_INDEX'			=>	$post['SHOP_NO1'].$post['POS_NO'],		//对称密钥索引
			);*/
			$m = M('');
			$m->startTrans();	//启用事务
			$where = 'POS_NO = "'.$post['POS_NO'].'" and SHOP_NO1 != "'.$post['SHOP_NO1'].'"';
			$findposback = D($this->MPosback)->findPosback($where);
			if (!empty($findposback)){
				$this->wrong('商户POS中的终端号已经存在，不可重复添加');
			}
			/*$pos_res = D($this->MPosback)->updatePosback('POS_ID = "'.$post['POS_ID'].'"', $pos_data);
			if ($pos_res['state'] != 0){
				//$m->rollback();//不成功，则回滚
				$this->wrong('商户POS数据修改失败');
			}*/
			$posback_info = array(
				'POS_NO' 			=> $post['POS_NO'], 
				'SHOP_NO1' 			=> $post['SHOP_NO1'], 
				'SHOP_NO2' 			=> $post['SHOP_NO2'], 
				'CHANNEL_MAP_ID' 	=> $post['CHANNEL_MAP_ID'], 
				'PRICE' 			=> $post['PRICE'] ? setMoney($post['PRICE']) : '0', 
				'DEPOSIT' 			=> $post['DEPOSIT'] ? setMoney($post['DEPOSIT']) : '0', 
				'RENTAL' 			=> $post['RENTAL'] ? setMoney($post['RENTAL']) : '0',
				'UPDATE_TIME' 		=> date('YmdHis')

			);
			$pos_res = D($this->MPosback)->updatePosback('POS_BACK_ID = "'.$post['POS_BACK_ID'].'"',$posback_info);
			if ($pos_res['state'] != 0){
				$m->rollback();//不成功，则回滚
				$this->wrong('商户POS信息数据修改失败');
			}
			
			//修改商户扣率
			$jfl = set_jifenlv($post['SHOP_NO1'], '1');
			
			//修改商户扣率
			$smdr = I('smdr');
			$smdr_data = array(
				'JFB_PER_FEE'		=>	$smdr['JFB_PER_FEE'] ? setMoney($smdr['JFB_PER_FEE'],'2') : '0',	//积分宝比例扣率线(万分比)
				'JFB_FIX_FEE'		=>	$smdr['JFB_FIX_FEE'] ? setMoney($smdr['JFB_FIX_FEE'], '2') : '0',	//积分宝封顶扣率线(单位分)
				'PER_FEE'			=>	$smdr['PER_FEE'] ? setMoney($smdr['PER_FEE'],'2') : '0',			//商户比例扣线(万分比)
				'FIX_FEE'			=>	$smdr['FIX_FEE'] ? setMoney($smdr['FIX_FEE'],'2') : '0',			//商户封顶扣率线(单位分)
			);
			if(($smdr['JFB_PER_FEE']+$smdr['PER_FEE']) != ($jfl*100)) {
				$m->rollback();//不成功，则回滚
				$this->wrong('银行卡扣率必须等于积分宝收单扣率(商户收单扣率+积分宝扣率 必须等于 '.($jfl*100).')');
			}
			$smdr_update = M('smdr')
				->data($smdr_data)
				->where("SHOP_MAP_ID = '".$shop_res['SHOP_MAP_ID']."' and PAY_TYPE = 0")
				->save();
			if($smdr_update === false) {
				$m->rollback();//不成功，则回滚
				$this->wrong('商户扣率信息数据修改失败');
			}
			
			$m->commit();
			$this->right($pos_res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MPosback)->findPosbackmore("pb.POS_BACK_ID='".$id."'","pb.*,s.SHOP_MAP_ID");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//查询商户扣率
		$smdr_data = M('smdr')
			->field('SHOP_MAP_ID, PAY_TYPE, JFB_PER_FEE, JFB_FIX_FEE, PER_FEE, FIX_FEE')
			->where("SHOP_MAP_ID = '".$info['SHOP_MAP_ID']."' and PAY_TYPE = 0")
			->find();
		$this->assign ('smdr_info', $smdr_data);
		$this->assign ('info', $info);
		//渠道列表
		$channel_list = D($this->MChannel)->getChannellist("CHANNEL_STATUS = 0 and CHANNEL_MAP_ID > 0", 'CHANNEL_MAP_ID,CHANNEL_NAME');
		$this->assign ('channel_list', 		$channel_list );			//渠道列表
		$this->display('channelpos_add');
	}

	/*
	* 自定义POS删除
	**/
	public function channelpos_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$where = array('POS_BACK_ID'=> array('eq', $id));
		$posback_info = D($this->MPosback)->findPosback($where);
		if (empty($posback_info)) {
			$this->wrong('未找到相关数据');
		}
		$shop_res = D($this->MShop)->findShop('SHOP_NO = "'.$posback_info['SHOP_NO1'].'"');
		if (empty($shop_res)) {
			$this->wrong('平台商户号不存在');
		}
		$m = M();
		$m->startTrans();	//启用事务
		
		$posback_res = D($this->MPosback)->delPosback($where);	//删除POS其他信息表
		if($posback_res['state'] != 0){
			$this->wrong($posback_res['msg']);
		}

	/*	$pos_res = D($this->MPos)->delPos('POS_ID = "'.$posback_info['POS_ID'].'"');//删除商户POS
		if($pos_res['state'] != 0){
			$m->rollback();
			$this->wrong($pos_res['msg']);
		}*/

		//同步数据
		$syc_arr = array(
			'operateType' 	=>	6,										//(操作类型)
			'oId'		 	=>	$shop_res['SHOP_MAP_ID'],				//(商户ID)
			'shopId' 		=>	$posback_info['SHOP_NO2'],				//(商户号)
			'termId' 		=>	$posback_info['POS_NO'],				//(商户终端号)
			'token' 		=>	strtoupper(md5(strtoupper(md5($shop_res['SHOP_MAP_ID'].'6'))))	//(签名）oId+ operateType 双次MD5
		);

		//同步
		$url = 'http://jiesuanif.jfb315.cn:8056/PointRepositoryOpenLibrary/ShopInfroSynchService';
		Add_LOG(CONTROLLER_NAME);
		Add_LOG(CONTROLLER_NAME, json_encode($syc_arr));
/*
		$resjson = httpPostForm($url, $syc_arr);
		Add_LOG(CONTROLLER_NAME, $resjson);
		$result = json_decode($resjson);
		if ($result->status != '0') {
			$m->rollback();	//回滚
			$this->wrong('商户第三方POS数据同步删除失败');
		}
*/
		$m->commit();	//成功，则提交
		$this->right('操作成功', 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}

	/*
	* 自定义POS删除
	**/
	public function poscharno_del() {
		$count = D($this->MPosback)->countPosback($where);
		$list  = D($this->MPosback)->getPosbacklist($where);
		$n = 0;
		foreach ($list as $key => $value) {
			$pos_res = D($this->MPos)->delPos('POS_ID = "'.$value['POS_ID'].'"');//删除商户POS
			if($pos_res['state'] != 0){
				$n++;
			}
		}
		echo '共计：'.$count.'删除失败：'.$n;
	}
}
