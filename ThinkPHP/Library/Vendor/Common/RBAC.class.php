<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use Think\Db;
class RBAC {
    // 认证方法
    static public function authenticate($map,$model='') {
        if(empty($model)) $model = C('USER_AUTH_MODEL');
        //使用给定的Map进行认证
        return M($model)->where($map)->find();
    }

    //用于检测用户权限的方法,并保存到Session中
    static function saveAccessList($authId=null) {
        if(null===$authId) $authId = $_SESSION[C('USER_AUTH_KEY')];
        // 如果使用普通权限模式，保存当前用户的访问权限列表
        // 对管理员开发所有权限
        if(C('USER_AUTH_TYPE') !=2 && !$_SESSION[C('ADMIN_AUTH_KEY')] )
            $_SESSION['_ACCESS_LIST'] = RBAC::getAccessList($authId);
        return ;
    }

    // 取得模块的所属记录访问权限列表 返回有权限的记录ID数组
    static function getRecordAccessList($authId=null,$module='') {
        if(null===$authId) $authId = $_SESSION[C('USER_AUTH_KEY')];
        if(empty($module)) $module = CONTROLLER_NAME;
        //获取权限访问列表
        $accessList = RBAC::getModuleAccessList($authId,$module);
        return $accessList;
    }

    //检查当前操作是否需要认证
    static function checkAccess() {
        //如果项目要求认证，并且当前模块需要认证，则进行权限认证
        if( C('USER_AUTH_ON') ){
            $_module    =    array();
            $_action    =    array();
            if("" != C('REQUIRE_AUTH_MODULE')) {
                //需要认证的模块
                $_module['yes'] = explode(',',strtoupper(C('REQUIRE_AUTH_MODULE')));
            }else {
                //无需认证的模块
                $_module['no'] = explode(',',strtoupper(C('NOT_AUTH_MODULE')));
            }
            //检查当前模块是否需要认证
            if((!empty($_module['no']) && !in_array(strtoupper(CONTROLLER_NAME),$_module['no'])) || (!empty($_module['yes']) && in_array(strtoupper(CONTROLLER_NAME),$_module['yes']))) {
                if("" != C('REQUIRE_AUTH_ACTION')) {
					//需要认证的操作
                    $_action['yes'] = explode(',',strtoupper(C('REQUIRE_AUTH_ACTION')));
                }else {
                    //无需认证的操作
                    $_action['no'] = explode(',',strtoupper(C('NOT_AUTH_ACTION')));
                }
                //检查当前操作是否需要认证
                if((!empty($_action['no']) && !in_array(strtoupper(ACTION_NAME),$_action['no'])) || (!empty($_action['yes']) && in_array(strtoupper(ACTION_NAME),$_action['yes']))) {
                    return true;
                }else {
                    return false;
                }
            }else {
                return false;
            }
        }
        return false;
    }

    // 登录检查
    static public function checkLogin() {
        //检查当前操作是否需要认证
        if(RBAC::checkAccess()) {
            //检查认证识别号
            if(!$_SESSION[C('USER_AUTH_KEY')]) {
                if(C('GUEST_AUTH_ON')) {
                    // 开启游客授权访问
                    if(!isset($_SESSION['_ACCESS_LIST']))
                        // 保存游客权限
                        RBAC::saveAccessList(C('GUEST_AUTH_ID'));
                }else{
                    // 禁止游客访问跳转到认证网关
                    redirect(PHP_FILE.C('USER_AUTH_GATEWAY'));
                }
            }
        }
        return true;
    }
	
	/**
     +----------------------------------------------------------
     * 权限认证的过滤器方法
     +----------------------------------------------------------
    */
    static public function AccessDecision($appName=MODULE_NAME) {
        //检查是否需要认证
        if(RBAC::checkAccess()) {
            //存在认证识别号，则进行进一步的访问决策
            $accessGuid = md5($appName.CONTROLLER_NAME.ACTION_NAME);
            if(empty($_SESSION[C('ADMIN_AUTH_KEY')])) {
                if(C('USER_AUTH_TYPE') == 2) {
                    //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                    //通过数据库进行访问检查
                    $accessList = RBAC::getAccessList($_SESSION[C('USER_AUTH_KEY')]);
                }else {
                    // 如果是管理员或者当前操作已经认证过，无需再次认证
                    if( $_SESSION[$accessGuid]) {
                        return true;
                    }
                    //登录验证模式，比较登录后保存的权限访问列表
                    $accessList = $_SESSION['_ACCESS_LIST'];
                }
                //判断是否为组件化模式，如果是，验证其全模块名
                $module = defined('P_CONTROLLER_NAME') ? P_CONTROLLER_NAME : CONTROLLER_NAME;				
				if(!isset($accessList[strtoupper($appName)][strtoupper($module)][strtoupper(ACTION_NAME)])) {
                    $_SESSION[$accessGuid] = false;
                    return false;
                }
                else {
                    $_SESSION[$accessGuid] = true;
                }
            }else{
                //管理员无需认证
                return true;
            }
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * 取得当前认证号的所有权限列表
     +----------------------------------------------------------
     * @param integer $authId 用户ID
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
    */
    static public function getAccessList($authId) {
        // Db方式权限数据
        $db     =   Db::getInstance(C('RBAC_DB_DSN'));
        $table  = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'),'menu'=>C('RBAC_NODE_TABLE'));
        $sql    =   "select menu.MENU_ID,menu.MENU_NAME from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['menu']." as menu ".
                    "where user.USER_ID='{$authId}' and user.ROLE_ID=role.ROLE_ID and ( access.ROLE_ID=role.ROLE_ID or (access.ROLE_ID=role.ROLE_PID and role.ROLE_PID!=0 ) ) and role.ROLE_STATUS=1 and access.MENU_ID=menu.MENU_ID and menu.MENU_LEVEL=1 and menu.MENU_STATUS=1";
        $apps =   $db->query($sql);
        $access =  array();
        foreach($apps as $key=>$app) {
            $appId   = $app['MENU_ID'];
            $appName = $app['MENU_NAME'];
            // 读取项目的模块权限
            $access[strtoupper($appName)]   =  array();
            $sql    =   "select menu.MENU_ID,menu.MENU_NAME from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['menu']." as menu ".
                    // 注释SQL是原版的语句，但由于我在系统中加入了非节点类型菜单(level=0)，
                    // 所以 and menu.MENU_PID={$appId} 这句会导致上层有非节点类型菜单的模块无法查询到
                    //"where user.USER_ID='{$authId}' and user.ROLE_ID=role.ROLE_ID and ( access.ROLE_ID=role.ROLE_ID  or (access.ROLE_ID=role.ROLE_PID and role.ROLE_PID!=0 ) ) and role.ROLE_STATUS=1 and access.MENU_ID=menu.MENU_ID and menu.MENU_LEVEL=2 and menu.MENU_PID={$appId} and menu.MENU_STATUS=1";
                    "where user.USER_ID='{$authId}' and user.ROLE_ID=role.ROLE_ID and ( access.ROLE_ID=role.ROLE_ID or (access.ROLE_ID=role.ROLE_PID and role.ROLE_PID!=0 ) ) and role.ROLE_STATUS=1 and access.MENU_ID=menu.MENU_ID and menu.MENU_LEVEL=2 and menu.MENU_STATUS=1";
            $modules =   $db->query($sql);
            // 判断是否存在公共模块的权限
            $publicAction  = array();
            foreach($modules as $key=>$module) {
                $moduleId   = $module['MENU_ID'];
                $moduleName = $module['MENU_NAME'];
                if('PUBLIC'== strtoupper($moduleName)) {
					$sql    =   "select menu.MENU_ID,menu.MENU_NAME from ".
						$table['role']." as role,".
						$table['user']." as user,".
						$table['access']." as access ,".
						$table['menu']." as menu ".
						"where user.USER_ID='{$authId}' and user.ROLE_ID=role.ROLE_ID and ( access.ROLE_ID=role.ROLE_ID  or (access.ROLE_ID=role.ROLE_PID and role.ROLE_PID!=0 ) ) and role.ROLE_STATUS=1 and access.MENU_ID=menu.MENU_ID and menu.MENU_LEVEL=3 and menu.MENU_PID={$moduleId} and menu.MENU_STATUS=1";
						$rs =   $db->query($sql);
						foreach ($rs as $a){
							$publicAction[$a['MENU_NAME']] = $a['MENU_ID'];
						}
						unset($modules[$key]);
                    break;
                }
            }
            // 依次读取模块的操作权限
            foreach($modules as $key=>$module) {
                $moduleId   = $module['MENU_ID'];
                $moduleName = $module['MENU_NAME'];
                $sql    =   "select menu.MENU_ID,menu.MENU_NAME from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['menu']." as menu ".
                    "where user.USER_ID='{$authId}' and user.ROLE_ID=role.ROLE_ID and ( access.ROLE_ID=role.ROLE_ID  or (access.ROLE_ID=role.ROLE_PID and role.ROLE_PID!=0 ) ) and role.ROLE_STATUS=1 and access.MENU_ID=menu.MENU_ID and menu.MENU_LEVEL=3 and menu.MENU_PID={$moduleId} and menu.MENU_STATUS=1";
                $rs =   $db->query($sql);
                $action = array();
                foreach ($rs as $a){
					$action[$a['MENU_NAME']] = $a['MENU_ID'];
					
					//gzy 20160221
					$moduleId2   = $a['MENU_ID'];
					$moduleName2 = $a['MENU_NAME'];
					$sql2    =   "select menu.MENU_ID,menu.MENU_NAME from ".
						$table['role']." as role,".
						$table['user']." as user,".
						$table['access']." as access ,".
						$table['menu']." as menu ".
						"where user.USER_ID='{$authId}' and user.ROLE_ID=role.ROLE_ID and ( access.ROLE_ID=role.ROLE_ID  or (access.ROLE_ID=role.ROLE_PID and role.ROLE_PID!=0 ) ) and role.ROLE_STATUS=1 and access.MENU_ID=menu.MENU_ID and menu.MENU_LEVEL=3 and menu.MENU_DISPLAY=0 and menu.MENU_PID={$moduleId2} and menu.MENU_STATUS=1";
					$rs2 =   $db->query($sql2);
					$action2 = array();
					foreach ($rs2 as $a2){
						$action2[$a2['MENU_NAME']] = $a2['MENU_ID'];
					}
					$action = array_merge($action,$action2);
                }
                // 和公共模块的操作权限合并
                $action += $publicAction;
                $access[strtoupper($appName)][strtoupper($moduleName)]   =  array_change_key_case($action,CASE_UPPER);
            }
        }
        return $access;
    }

    // 读取模块所属的记录访问权限
    static public function getModuleAccessList($authId,$module) {
        // Db方式
        $db     =   Db::getInstance(C('RBAC_DB_DSN'));
        $table = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'));
        $sql    =   "select access.MENU_ID from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ".
                    "where user.USER_ID='{$authId}' and user.ROLE_ID=role.ROLE_ID and ( access.ROLE_ID=role.ROLE_ID  or (access.ROLE_ID=role.ROLE_PID and role.ROLE_PID!=0 ) ) and role.ROLE_STATUS=1 and  access.module='{$module}' and access.status=1";
        $rs =   $db->query($sql);
        $access    =    array();
        foreach ($rs as $menu){
            $access[]    =    $menu['MENU_ID'];
        }
        return $access;
    }
}