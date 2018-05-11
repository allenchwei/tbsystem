<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
import('Vendor.Common.Tree');
// +----------------------------------------------------------------------
// | @gzy  系统设置
// +----------------------------------------------------------------------
class SystemController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->MUser	= 'MUser';	
		$this->MRole	= 'MRole';	
		$this->MBranch	= 'MBranch';
		$this->MPartner	= 'MPartner';
		$this->MMenu	= 'MMenu';
		$this->MAccess	= 'MAccess';
		$this->MLog		= 'MLog';
		$this->PUser	= 'PUser';
	}
	
	/*
	* 操作员管理
	**/
	public function user() {
		$home = session('HOME');
		$post = I('post');
		if (($home['PARTNER_MAP_ID'].'001' != $home['USER_NO']) && $home['BRANCH_MAP_ID'] != C('SPECIAL_USER')) {
			$this->wrong("您当前没有该功能的操作权限，请联系管理员！");
			exit;
		}
		if($post['submit'] == "user"){
			$where = "u.ROLE_ID != 1 and u.USER_STATUS != 2 and u.USER_LEVEL >= '".$home['USER_LEVEL']."'";
			//用户级别
			if($post['USER_LEVEL'] != '') {
				$where .= " and u.USER_LEVEL = '".$post['USER_LEVEL']."'";
			}
			//用户手机
			if($post['USER_MOBILE']) {
				$where .= " and u.USER_MOBILE = '".$post['USER_MOBILE']."'";
			}
			//用户工号
			if($post['USER_NO']) {
				$where .= " and u.USER_NO like '".$post['USER_NO']."%'";
			}
			//归属
			$getlevel = filter_data('plv');	//列表查询
			$post['BRANCH_MAP_ID']  = $getlevel['bid'];
			$post['PARTNER_MAP_ID'] = $getlevel['pid'];
			if($post['BRANCH_MAP_ID']){
				$where .= " and u.BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
			}
			if($post['PARTNER_MAP_ID']){
				if(C('SPECIAL_USER') == $home['USER_ID']){
					$pids = get_plv_childs($post['PARTNER_MAP_ID'], 1);
					$where .= " and u.PARTNER_MAP_ID in (".$pids.")";
				}else{
					$where .= " and u.PARTNER_MAP_ID = '".$post['PARTNER_MAP_ID']."'";
				}		
				
			}
					
			//分页
			$count = D($this->MUser)->countUser($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MUser)->getUserlist($where, 'u.*, a.PARTNER_NAME, b.BRANCH_NAME', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('home',			$home);	//用户级别
		$this->assign('user_level',		C('USER_LEVEL'));	//用户级别
		$this->assign('user_status',	C('USER_STATUS'));	//用户状态		
		\Cookie::set ('_currentUrl_', 	__SELF__);
		$this->display();
	}
	/*
	* 操作员管理 添加
	**/
	public function user_add() {
		$home = session('HOME');
		$post = I('post');
		$plv  = I('plv');
		if($post['submit'] == "user_add") {
			//验证
			if($post['USER_LEVEL'] == ''){
				$this->wrong("请选择用户级别！");
			}
			//处理归属
			if($post['USER_LEVEL'] == 0){
				$post['BRANCH_MAP_ID'] 	= 100000;
				$post['PARTNER_MAP_ID'] = 0;				
			}else{
				if (empty($post['PARTNER_MAP_ID']) && empty($post['BRANCH_MAP_ID']) ) {
					switch($post['USER_LEVEL']){
						case '1':
							if($plv['0'] == ''){
								$this->wrong("请选择用户归属! ");
							}
							$post['BRANCH_MAP_ID'] 	= $plv['0'];
							$post['PARTNER_MAP_ID'] = 0;
							break;
						case '2':
							if(empty($plv['1'])){
								$this->wrong("请选择地市用户归属! ");
							}
							$post['BRANCH_MAP_ID'] 	= $plv['0'];
							$post['PARTNER_MAP_ID'] = $plv['1'];
							break;
						case '3':
							if(empty($plv['2'])){
								$this->wrong("请选择区县级用户归属! ");
							}
							$post['BRANCH_MAP_ID'] 	= $plv['0'];
							$post['PARTNER_MAP_ID'] = $plv['2'];
							break;
						case '4':
							if(empty($plv['3'])){
								$this->wrong("请选择创业合伙人用户归属! ");
							}
							$post['BRANCH_MAP_ID'] 	= $plv['0'];
							$post['PARTNER_MAP_ID'] = $plv['3'];
							break;
						default:
							if(empty($plv['3'])){
								$this->wrong("请选择用户级别! ");
							}
							break;
					}
				}
			}
			//角色	选择的级别必须 和 角色一致
			if(empty($post['ROLE_ID'])){
				$this->wrong("请选择角色！");
			}
			$role_data = D($this->MRole)->findRole("ROLE_ID='".$post['ROLE_ID']."'", 'ROLE_NAME,ROLE_LEVEL');
			if($post['USER_LEVEL'] != $role_data['ROLE_LEVEL']){
				$this->wrong("请将用户级别与角色名称设置相同！");
			}
			
			if(empty($post['USER_NO']) || empty($post['USER_NAME']) || empty($post['USER_MOBILE'])){
				$this->wrong("缺少必填项数据！");
			}
			//用户编号
			$post['USER_NO'] = $post['PARTNER_MAP_ID'].setStrzero(substr($post['USER_NO'], 0, 3), 3);
			$findno = D($this->MUser)->findUser("USER_NO='".$post['USER_NO']."'");
			if(!empty($findno)){
				$this->wrong("该用户编号已经存在！");
			}
			//用户手机
			/*$user_mobile = D($this->MUser)->findUser("USER_MOBILE='".$post['USER_MOBILE']."' and USER_STATUS != 2");
			if(!empty($user_mobile)){
				$this->wrong("该用户手机号已经存在！");
			}	*/	
			//设置初始密码（手机号的后6位）
			$newpwd = substr($post['USER_MOBILE'],-6);	
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'	=>	$post['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'=>	$post['PARTNER_MAP_ID'],
				'USER_NO'		=>	$post['USER_NO'],
				'USER_MOBILE'	=>	$post['USER_MOBILE'],
				'USER_NAME'		=>	$post['USER_NAME'],
				'USER_FLAG'		=>	0,
				'USER_PASSWD'	=>	strtoupper(md5(strtoupper(md5($newpwd)))),
				'USER_LEVEL'	=>	$post['USER_LEVEL'],
				'USER_STATUS'	=>	$post['USER_STATUS'],
				'ROLE_ID'		=>	$post['ROLE_ID'],
				'ROLE_NAME'		=>	$role_data['ROLE_NAME'],
				'EMAIL'			=>	$post['EMAIL'],
				'PINERR_NUM'	=>	0,
				'LOGIN_IP'		=>	get_client_ip(),
				'CREATE_TIME'	=>	date("YmdHis"),
				'ACTIVE_TIME'	=>	date("YmdHis"),
				'UPDATE_TIME'	=>	date("YmdHis")
			);
			$res = D($this->MUser)->addUser($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//角色列表
		//如果是超级管理员，查看所有
		if(C('SPECIAL_USER') == $home['USER_ID']){
			$where = "ROLE_ID != 1 and ROLE_STATUS = 1";
		}else{
			//$where = "ROLE_ID != 1 and ROLE_STATUS = 1 and ROLE_LEVEL>='".$home['USER_LEVEL']."'";
			$where = "ROLE_ID != 1 and ROLE_STATUS = 1 and ROLE_LEVEL='".$home['USER_LEVEL']."'";
		}		
		$role_list = D($this->MRole)->getRolelist($where, 'ROLE_ID,ROLE_NAME');	//除超管外		
		$this->assign('role_list', 			$role_list);
		
		$this->assign('home',			$home);				//session
		$this->assign('user_level',		C('USER_LEVEL'));	//用户级别
		$this->assign('user_status',	C('USER_STATUS'));	//用户状态
		
		if (C('SPECIAL_USER') == $home['USER_ID']) {
			$this->display();
		}else{
			$this->display('user_add2');
		}
	}
	/*
	* 操作员管理 查看
	**/
	public function user_show() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MUser)->findmoreUser("u.USER_ID='".$id."'", 'u.*, a.PARTNER_NAME, b.BRANCH_NAME');
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('user_level',			C('USER_LEVEL'));	//用户级别
		$this->assign('user_status',		C('USER_STATUS'));	//用户状态
		$this->assign('info', 				$info);
		$this->display();
	}
	/*
	* 操作员管理 修改
	**/
	public function user_edit() {
		$home = session('HOME');
		$post = I('post');
		$plv  = I('plv');
		if($post['submit'] == "user_edit") {
			//验证
			if($post['USER_LEVEL'] == ''){
				$this->wrong("请选择用户级别！");
			}
			//处理归属
			if($post['USER_LEVEL'] == 0){
				$post['BRANCH_MAP_ID'] 	= 100000;
				$post['PARTNER_MAP_ID'] = 0;				
			}else{
				if (empty($post['PARTNER_MAP_ID']) && empty($post['BRANCH_MAP_ID']) ) {
					switch($post['USER_LEVEL']){
						case '1':
							if($plv['0'] == ''){
								$this->wrong("请选择用户归属! ");
							}
							$post['BRANCH_MAP_ID'] 	= $plv['0'];
							$post['PARTNER_MAP_ID'] = 0;
							break;
						case '2':
							if(empty($plv['1'])){
								$this->wrong("请选择地市用户归属! ");
							}
							$post['BRANCH_MAP_ID'] 	= $plv['0'];
							$post['PARTNER_MAP_ID'] = $plv['1'];
							break;
						case '3':
							if(empty($plv['2'])){
								$this->wrong("请选择区县级用户归属! ");
							}
							$post['BRANCH_MAP_ID'] 	= $plv['0'];
							$post['PARTNER_MAP_ID'] = $plv['2'];
							break;
						case '4':
							if(empty($plv['3'])){
								$this->wrong("请选择创业合伙人用户归属! ");
							}
							$post['BRANCH_MAP_ID'] 	= $plv['0'];
							$post['PARTNER_MAP_ID'] = $plv['3'];
							break;
						default:
							if(empty($plv['3'])){
								$this->wrong("请选择用户级别! ");
							}
							break;
					}
				}
			}
			//角色	选择的级别必须 和 角色一致
			if(empty($post['ROLE_ID'])){
				$this->wrong("请选择角色！");
			}
			$role_data = D($this->MRole)->findRole("ROLE_ID='".$post['ROLE_ID']."'", 'ROLE_NAME,ROLE_LEVEL');
			if($post['USER_LEVEL'] != $role_data['ROLE_LEVEL']){
				$this->wrong("请将用户级别与角色名称设置相同！");
			}
			
			if(empty($post['USER_NO']) || empty($post['USER_NAME']) || empty($post['USER_MOBILE'])){
				$this->wrong("缺少必填项数据！");
			}
			//用户编号
			$post['USER_NO'] = $post['PARTNER_MAP_ID'].setStrzero(substr($post['USER_NO'], 0, 3), 3);
			$findno = D($this->MUser)->findUser("USER_ID != '".$post['USER_ID']."' and USER_NO='".$post['USER_NO']."'");
			if(!empty($findno)){
				$this->wrong("该用户编号已经存在！");
			}
			//用户手机
			/*$user_mobile = D($this->MUser)->findUser("USER_ID != '".$post['USER_ID']."' and USER_MOBILE='".$post['USER_MOBILE']."' and USER_STATUS != 2");
			if(!empty($user_mobile)){
				$this->wrong("该用户手机号已经存在！");
			}*/
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'	=>	$post['BRANCH_MAP_ID'],
				'PARTNER_MAP_ID'=>	$post['PARTNER_MAP_ID'],
				'USER_NO'		=>	$post['USER_NO'],
				'USER_MOBILE'	=>	$post['USER_MOBILE'],
				'USER_NAME'		=>	$post['USER_NAME'],
				'USER_LEVEL'	=>	$post['USER_LEVEL'],
				'USER_STATUS'	=>	$post['USER_STATUS'],
				'ROLE_ID'		=>	$post['ROLE_ID'],
				'ROLE_NAME'		=>	$role_data['ROLE_NAME'],
				'EMAIL'			=>	$post['EMAIL'],
				'LOGIN_IP'		=>	get_client_ip(),
				'ACTIVE_TIME'	=>	date("YmdHis"),
				'UPDATE_TIME'	=>	date("YmdHis")
			);
			$res = D($this->MUser)->updateUser("USER_ID='".$post['USER_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MUser)->findUser("USER_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//编辑时处理一下编号
		$info['USER_NO'] = substr($info['USER_NO'], -3, 3);
		
		//角色列表
		//如果是超级管理员，查看所有
		if(C('SPECIAL_USER') == $home['USER_ID']){
			$where = "ROLE_ID != 1 and ROLE_STATUS = 1";
		}else{
			//$where = "ROLE_ID != 1 and ROLE_STATUS = 1 and ROLE_LEVEL>='".$home['USER_LEVEL']."'";
			$where = "ROLE_ID != 1 and ROLE_STATUS = 1 and ROLE_LEVEL='".$home['USER_LEVEL']."'";
		}			
		$role_list = D($this->MRole)->getRolelist($where, 'ROLE_ID,ROLE_NAME');	//除超管外		
		$this->assign('role_list', 			$role_list);
		
		$this->assign('home',			$home);				//用户级别
		$this->assign('user_level',		C('USER_LEVEL'));	//用户级别
		$this->assign('user_status',	C('USER_STATUS'));	//用户状态
		$this->assign('info', 			$info);
		
		if (C('SPECIAL_USER') == $home['USER_ID']) {
			$this->display('user_add');
		}else{
			$this->display('user_add2');
		}
	}
	/*
	* 操作员管理 重置密码
	**/
	public function user_resetpwd() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$user_info = D($this->MUser)->findUser("USER_ID = '".$id."'");
		if(empty($user_info)){
			$this->wrong("该用户不存在！");
		}
		$newpwd = substr($user_info['USER_MOBILE'],-6);

		$res = D($this->MUser)->updateUser("USER_ID='".$id."'", array('USER_PASSWD'=> strtoupper(md5(strtoupper(md5($newpwd))))));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('重置成功！密码为 '.$newpwd, 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 操作员管理 注销
	**/
	public function user_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$res = D($this->MUser)->updateUser("USER_ID='".$id."'", array('USER_STATUS'=> 2));
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right('注销成功！');
	}
	
	
	
	/*
	* 角色权限维护
	**/
	public function role() {
		$post = I('post');
		if($post['submit'] == "role"){
			$where = "1=1";	//除root外						
			//角色名称
			if($post['ROLE_NAME']) {
				$where .= " and ROLE_NAME like '%".$post['ROLE_NAME']."%'";
			}
			//角色级别
			if($post['ROLE_LEVEL'] != '') {
				$where .= " and ROLE_LEVEL = '".$post['ROLE_LEVEL']."'";
			}
			//分页
			$count = D($this->MRole)->countRole($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MRole)->getRolelist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('role_level',			C('USER_LEVEL'));	//用户级别
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 角色权限维护 添加
	**/
	public function role_add() {
		$post = I('post');
		if($post['submit'] == "role_add") {
			//验证
			if(empty($post['ROLE_NAME'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'ROLE_PID'		=>	1,
				'ROLE_LEVEL'	=>	$post['ROLE_LEVEL'],
				'ROLE_NAME'		=>	$post['ROLE_NAME'],
				'ROLE_STATUS'	=>	$post['ROLE_STATUS'],
				'ROLE_REMARK'	=>	$post['ROLE_REMARK']
			);
			$res = D($this->MRole)->addRole($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign('role_level',			C('USER_LEVEL'));	//用户级别
		$this->display();
	}
	/*
	* 角色权限维护 修改
	**/
	public function role_edit() {
		$post = I('post');
		if($post['submit'] == "role_edit") {
			//验证
			if(empty($post['ROLE_NAME'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'ROLE_LEVEL'	=>	$post['ROLE_LEVEL'],
				'ROLE_NAME'		=>	$post['ROLE_NAME'],
				'ROLE_STATUS'	=>	$post['ROLE_STATUS'],
				'ROLE_REMARK'	=>	$post['ROLE_REMARK']
			);
			$res = D($this->MRole)->updateRole("ROLE_ID='".$post['ROLE_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			//修改user表角色名称
			D($this->MUser)->updateUser("ROLE_ID='".$post['ROLE_ID']."'", array('ROLE_NAME'=> $post['ROLE_NAME']));			
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MRole)->findRole("ROLE_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$this->assign('info',				$info );
		$this->assign('role_level',			C('USER_LEVEL'));	//用户级别
		$this->display('role_add');
	}
	/*
	* 角色权限维护 删除
	**/
	public function role_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		if($id == 1){
			$this->wrong('参数出错！');
		}
		$info = D($this->MRole)->findRole("ROLE_ID='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$finddata = D($this->MUser)->findUser("ROLE_ID = '".$id."'");
		if(!empty($finddata)){
			$this->wrong("当前角色，存在使用用户，不可删除！");
		}
		
		//固定省、市、县、创业合伙人 不能删除
		$isarr = array(C('JFB_SHENG'),C('JFB_SHI'),C('JFB_XIAN'),C('JFB_CHUANG'));
		if(in_array($id, $isarr)) {
			$this->wrong("固定省、市、县、创业合伙人 不能删除！");
			echo $id;print_r($isarr);exit;
		}
		
		$res = D($this->MRole)->delRole("ROLE_ID='".$id."'");
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right ($res['msg']);
	}
	/*
	* 角色权限维护 权限设置
	**/
	public function role_access() {
		$post = I('post');
		if($post['submit'] == "role_access") {
			//验证
			if(empty($post['role_id'])){
				$this->wrong("缺少参数！");
			}
			//提交有数据，则修改原权限配置
			if(is_array($post['menu_id']) && count($post['menu_id'])>0){
				//先删除原用户组的权限配置
				D($this->MAccess)->delAccess("ROLE_ID = '".$post['role_id']."'");
				$menu = D($this->MMenu)->getMenulist("MENU_ID != ''");
				foreach($menu as $key=>$val){
					$menu[$val['MENU_ID']] = $val;
				}
				foreach($post['menu_id'] as $key=>$val){
					$data[$key] = D($this->MAccess)->get_menuinfo($val, $menu);
					$data[$key]['ROLE_ID'] = $post['role_id'];
				}
				//重新创建角色的权限配置
				D($this->MAccess)->addAccessAll($data);
			}
			//提交无数据，则删除权限配置
			else{
				D($this->MAccess)->delAccess("ROLE_ID='".$post['role_id']."'");
			}
			$this->right('设置成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$role_id = $_REQUEST['id'];
		if(empty($role_id)){
			$this->wrong('缺少参数！');
		}
		$menu 	= D($this->MMenu)->getMenulist("MENU_ID != ''");
		$access = D($this->MAccess)->getAccesslist('ROLE_ID,MENU_ID,MENU_PID,MENU_LEVEL');
		foreach($menu as $val) {
			$rt = array(
				'id'		=>	$val['MENU_ID'],
				'pid'		=>	$val['MENU_PID'],
				'title'		=>	$val['MENU_TITLE'],
				'checked'	=>	(D($this->MAccess)->is_checked($val, $role_id, $access)) ? ' checked' : '',
				'depth'		=>	D($this->MAccess)->get_level($val['MENU_ID'], $menu),
				'pid_menu'	=>	$val['MENU_PID'] ? ' class="tr lt child-of-menu-'.$val['MENU_PID'].'"' : ''
			);
			$newlist[] = $rt;
		}
        $str  = "<tr id='menu-\$id' \$pid_menu>
                    <td style='padding-left:30px;'>\$spacer <input type='checkbox' name='post[menu_id][]' value='\$id' class='radio' level='\$depth' \$checked onclick='javascript:checkmenu(this);' > \$title</td>
                </tr>";
  		$Tree = new \Tree();
		$Tree->init($newlist);
		$html_tree = $Tree->get_tree(0, $str);
		$this->assign('html_tree',		$html_tree );
		$this->assign('role_id',		$role_id );
		$this->display();
	}
	
	
	
	/*
	* 操作日志维护
	**/
	public function log() {
		$post = I('post');
		if($post['submit'] == "log"){
			$where = "1=1";
			//开始日期
			if($post['LOG_DATE_A']) {
				$where .= " and LOG_DATE >= '".date("Ymd",strtotime($post['LOG_DATE_A']))."'";
			}
			//开始日期
			if($post['LOG_DATE_B']) {
				$where .= " and LOG_DATE <= '".date("Ymd",strtotime($post['LOG_DATE_B']))."'";
			}
			//开始日期
			if($post['LOG_TIME_A']) {
				$where .= " and LOG_TIME >= '".$post['LOG_TIME_A']."'";
			}
			//开始日期
			if($post['LOG_TIME_B']) {
				$where .= " and LOG_TIME <= '".$post['LOG_TIME_B']."'";
			}
			//操作人员
			if($post['USER_NAME']) {
				$where .= " and USER_NAME like '%".$post['USER_NAME']."%'";
			}
			//关键字
			if($post['LOG_DESC']) {
				$where .= " and LOG_DESC like '%".$post['LOG_DESC']."%'";
			}
			//分页
			$count = D($this->MLog)->countlog($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->MLog)->getLoglist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		$this->assign('log_types',			C('LOG_TYPES'));		//log类型
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 操作日志维护 删除
	**/
	public function log_del() {
		$ids = $_REQUEST['LOG_ID'];
		if(empty($ids)){
			$this->wrong('缺少参数');
		}
		if(is_array($ids)){
			$where = array('LOG_ID'=> array('in', implode(',', $ids)));
		}else{
			$where = array('LOG_ID'=> array('eq', $ids));
		}
		$res = D($this->MLog)->delLog($where);
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg']);
	}
	
	
	
	/*
	* 监控用户管理
	**/
	public function monlog() {
		$post = I('post');
		if($post['submit'] == "monlog"){
			$where = "1=1";
			//分公司
			if($post['BRANCH_MAP_ID']) {
				$where .= " and BRANCH_MAP_ID = '".$post['BRANCH_MAP_ID']."'";
			}
			//登录用户账号
			if($post['USER_NO']) {
				$where .= " and USER_NO = '".$post['USER_NO']."'";
			}
			//分页
			$count = D($this->PUser)->countUser($where);
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$list  = D($this->PUser)->getUserlist($where, '*', $p->firstRow.','.$p->listRows);
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	
			
			$this->assign ( 'postdata', 	$post );
			$this->assign ( 'list', 		$list );
		}
		//所属分支
		$branch_list = D($this->MBranch)->getBranchlist('','BRANCH_MAP_ID,BRANCH_NAME');
		$this->assign('branch_list', 		$branch_list);
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	/*
	* 监控用户管理 详情
	**/
	public function monlog_add() {
		$post = I('post');
		if($post['submit'] == "monlog_add"){
			//验证
			if(empty($post['USER_NO']) || empty($post['USER_NAME']) || empty($post['USER_PASSWD'])){
				$this->wrong("缺少必填项数据！");
			}
			$finddata = D($this->PUser)->findUser("USER_NO='".$post['USER_NO']."'");
			if(!empty($finddata)){
				$this->wrong("该用户账号已经存在！");
			}
			$branch_data = D($this->MBranch)->findBranch("BRANCH_MAP_ID='".$post['BRANCH_MAP_ID']."'",'BRANCH_NAME');
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'=>	$post['BRANCH_MAP_ID'],
				'BRANCH_NAMEAB'	=>	$branch_data['BRANCH_NAME'],
				'USER_NO'		=>	$post['USER_NO'],
				'USER_NAME'		=>	$post['USER_NAME'],
				'USER_PASSWD'	=>	strtoupper(md5(strtoupper(md5($post['USER_PASSWD']))))
			);
			$res = D($this->PUser)->addUser($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		//所属分支
		$branch_list = D($this->MBranch)->getBranchlist('','BRANCH_MAP_ID,BRANCH_NAME');
		$this->assign('branch_list', 		$branch_list);
		$this->display();
	}
	/*
	* 监控用户管理 详情
	**/
	public function monlog_show() {
		$this->display();
	}
	/*
	* 监控用户管理 修改
	**/
	public function monlog_edit() {
		$post = I('post');
		if($post['submit'] == "monlog_edit"){
			//验证
			if(empty($post['USER_NAME'])){
				$this->wrong("缺少必填项数据！");
			}
			$branch_data = D($this->MBranch)->findBranch("BRANCH_MAP_ID='".$post['BRANCH_MAP_ID']."'",'BRANCH_NAME');
			//组装数据
			$resdata = array(
				'BRANCH_MAP_ID'	=>	$post['BRANCH_MAP_ID'],
				'BRANCH_NAMEAB'	=>	$branch_data['BRANCH_NAME'],
				'USER_NAME'		=>	$post['USER_NAME']
			);
			if($post['USER_PASSWD']){
				$resdata['USER_PASSWD'] = strtoupper(md5(strtoupper(md5($post['USER_PASSWD']))));
			}
			$res = D($this->PUser)->updateUser("USER_NO='".$post['USER_NO']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->PUser)->findUser("USER_NO='".$id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		//所属分支
		$branch_list = D($this->MBranch)->getBranchlist('','BRANCH_MAP_ID,BRANCH_NAME');
		$this->assign('branch_list', 		$branch_list);
		$this->assign('info', 				$info);
		$this->display('monlog_add');
	}
	/*
	* 监控用户管理 删除
	**/
	public function monlog_del() {
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$res = D($this->PUser)->delUser("USER_NO='".$id."'");
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right($res['msg']);
	}
	
	
	
	/*
	* 菜单管理
	**/
	public function menu() {
		$list = D($this->MMenu)->getMenulist("MENU_ID != ''");
		//组装数据
		foreach($list as $val) {
			$rt = array(
				'sort'		=>	$val['MENU_SORT'],
				'id'		=>	$val['MENU_ID'],
				'pid'		=>	$val['MENU_PID'],
				'title'		=>	$val['MENU_TITLE'],
				'name'		=>	$val['MENU_NAME'],
				'data'		=>	($val['MENU_LEVEL']==3 && $val['MENU_DISPLAY']==0) ? '' : $val['MENU_DATA'],
				'status'  	=> 	$val['MENU_STATUS']==1 ? '<font color="red" class="ju">√</font>' : '<font color="blue" class="ju">×</font>',		
				'submenu' 	=> 	($val['MENU_LEVEL']==3 && $val['MENU_DISPLAY']==0) ? '<font color="#cccccc">添加子菜单</font>' : "<a href='".U('/Home/System/menu_add/menu_pid/'.$val['MENU_ID'])."' width='900' height='550' target='dialog' mask='true'>添加子菜单</a>",
				'edit'    	=> 	$val['MENU_LEVEL']==1 ? '<font color="#cccccc">修改</font>' : "<a href='".U('/Home/System/menu_edit/menu_id/'.$val['MENU_ID'].'/menu_pid/'.$val['MENU_PID'])."' width='900' height='550' target='dialog' mask='true'>修改</a>",
				'del'		=> 	$val['MENU_LEVEL']==1 ? '<font color="#cccccc">删除</font>' : "<a href='".U('/Home/System/menu_del/menu_id/'.$val['MENU_ID'])."' target='ajaxTodo' calback='navTabAjaxDone' title='您确定要删除吗？'>删除</a>",
			);
			switch($val['MENU_DISPLAY']) {
				case 0:
					$rt['display'] = '<font color="#cccccc">不显示</font>';
					break;
				case 1:
					$rt['display'] = '主菜单';
					break;
				case 2:
					$rt['display'] = '子菜单';
					break;
			}
			switch($val['MENU_LEVEL']){
				case 0:
					$rt['level'] = '非节点';
					break;
				case 1:
					$rt['level'] = '应用';
					break;
				case 2:
					$rt['level'] = '模块';
					break;
				case 3:
					$rt['level'] = $val['MENU_DISPLAY']==2 ? '方法' : '<font color="#cccccc">方法</font>';
					break;
			}
			$newlist[] = $rt;
		}
		$str = "<tr class='tr'>
				    <td align='center'><input type='text' value='\$sort' class='wid45 sortinput' name='sort[\$id]' maxlength='3'></td>
				    <td >\$spacer \$title</td> 
				    <td >\$name</td> 
				    <td >\$data</td> 
				    <td align='center'>\$status</td> 
				    <td align='center'>\$level</td>
				    <td align='center'>\$display</td> 
					<td align='center'>\$submenu&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;\$edit&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;\$del</td>
				</tr>";
  		$Tree = new \Tree();
		$Tree->init($newlist);
		$html_tree = $Tree->get_tree(0, $str);
		$this->assign('html_tree',		$html_tree );
		\Cookie::set ('_currentUrl_', 	__SELF__ );
		$this->display();
	}
	/*
	* 菜单管理 添加
	**/
	public function menu_add() {
		$post = I('post');
		if($post['submit'] == "menu_add") {
			//验证
			if(empty($post['MENU_PID']) || empty($post['MENU_TITLE'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'MENU_PID'		=>	$post['MENU_PID'],
				'MENU_TITLE'	=>	$post['MENU_TITLE'],
				'MENU_DISPLAY'	=>	$post['MENU_DISPLAY'],
				'MENU_LEVEL'	=>	$post['MENU_LEVEL'],
				'MENU_NAME'		=>	$post['MENU_NAME'],
				'MENU_DATA'		=>	$_REQUEST['post']['MENU_DATA'],
				'MENU_STATUS'	=>	$post['MENU_STATUS'],
				'MENU_REMARK'	=>	$post['MENU_REMARK']
			);
			$res = D($this->MMenu)->addMenu($resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$menu_pid = $_REQUEST['menu_pid'] ? $_REQUEST['menu_pid'] : 0;
		$list 	  = D($this->MMenu)->getMenulist("MENU_ID != ''");
		//组装数据
		foreach($list as $val) {
			$rt = array(
				'id'		=>	$val['MENU_ID'],
				'pid'		=>	$val['MENU_PID'],
				'title'		=>	$val['MENU_TITLE'],
				'disabled'  =>  ($val['MENU_LEVEL']==3 && $val['MENU_DISPLAY']==0) ? 'disabled' : '',
			);
			$newlist[$val['MENU_ID']] = $rt;
		}
		$str  = "<option value='\$id' \$selected \$disabled >\$spacer \$title</option>";
		$Tree = new \Tree();
		$Tree->init($newlist);
		$menu_select = $Tree->get_tree(0, $str, $menu_pid);
		$this->assign('menu_select',	$menu_select);
		$this->display();
	}
	/*
	* 菜单管理 修改
	**/
	public function menu_edit() {
		$post = I('post');
		if($post['submit'] == "menu_edit") {
			//验证
			if(empty($post['MENU_PID']) || empty($post['MENU_TITLE'])){
				$this->wrong("缺少必填项数据！");
			}
			//组装数据
			$resdata = array(
				'MENU_PID'		=>	$post['MENU_PID'],
				'MENU_TITLE'	=>	$post['MENU_TITLE'],
				'MENU_DISPLAY'	=>	$post['MENU_DISPLAY'],
				'MENU_LEVEL'	=>	$post['MENU_LEVEL'],
				'MENU_NAME'		=>	$post['MENU_NAME'],
				'MENU_DATA'		=>	$_REQUEST['post']['MENU_DATA'],
				'MENU_STATUS'	=>	$post['MENU_STATUS'],
				'MENU_REMARK'	=>	$post['MENU_REMARK']
			);
			$res = D($this->MMenu)->updateMenu("MENU_ID='".$post['MENU_ID']."'", $resdata);
			if($res['state'] != 0){
				$this->wrong($res['msg']);
			}
			$this->right($res['msg'], 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		
		$menu_id  = $_REQUEST['menu_id'];
		$menu_pid = $_REQUEST['menu_pid'];
		if(empty($menu_id) || empty($menu_pid)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MMenu)->findMenu("MENU_ID = '".$menu_id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$list = D($this->MMenu)->getMenulist("MENU_ID != ''");
		//组装数据
		foreach($list as $val) {
			$rt = array(
				'id'		=>	$val['MENU_ID'],
				'pid'		=>	$val['MENU_PID'],
				'title'		=>	$val['MENU_TITLE'],
				'disabled'  =>  ($val['MENU_LEVEL']==3 && $val['MENU_DISPLAY']==0) ? 'disabled' : '',
			);
			$newlist[$val['MENU_ID']] = $rt;
		}
		$str  = "<option value='\$id' \$selected \$disabled >\$spacer \$title</option>";
		$Tree = new \Tree();
		$Tree->init($newlist);
		$menu_select = $Tree->get_tree(0, $str, $menu_pid);
		$this->assign('menu_select',		$menu_select);
		$this->assign('info',				$info);	
		$this->display('menu_add');
	}
	/*
	* 菜单管理 删除
	**/
	public function menu_del() {
		$menu_id  = $_REQUEST['menu_id'];
		if(empty($menu_id)){
			$this->wrong('缺少参数！');
		}
		$info = D($this->MMenu)->findMenu("MENU_ID = '".$menu_id."'");
		if(empty($info)){
			$this->wrong("参数数据出错！");
		}
		$childlist = D($this->MMenu)->findMenu("MENU_PID = '".$menu_id."'");
		if(!empty($childlist)){
			$this->wrong("存在子菜单，请先删除子菜单！");
		}
		$res = D($this->MMenu)->delMenu("MENU_ID = '".$menu_id."'");
		if($res['state'] != 0){
			$this->wrong($res['msg']);
		}
		$this->right ($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
	/*
	* 菜单管理 排序
	**/
	public function menu_sort() {
		$sorts = I('sort');
		if(!is_array($sorts)){
			$this->wrong('缺少参数！');
		}
		foreach ($sorts as $key=>$val){
			D($this->MMenu)->updateMenu("MENU_ID='".$key."'", array('MENU_SORT'=> $val));
		}
		$this->right($res['msg'], 'forward', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
	}
}