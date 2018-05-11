<?php
//**********************
//current Settings development, online
//**********************
if($_SERVER['HTTP_HOST'] == 'demo.shoudan.com'){
	$HOST = 'development';
}else if($_SERVER['HTTP_HOST'] == 'shoudan.xingdata.com'){
	$HOST = 'zhexing';
}else{
	$HOST = 'online';
}
//define('CON_ENVIRONMENT', $HOST);
define('CON_ENVIRONMENT', "development");

switch (CON_ENVIRONMENT){
	case "development":	
		//本地测试mysql://root:root179@172.18.0.21:3306/tra_db_jfb#utf8
		define('DB_DSN', 		'mysql://root:root179@172.18.0.21:3306/pai_db_jfb#utf8');
		define('DB_PREFIX',		'a_');
        define('C_URL', 		'http://172.18.0.21');//lfq java项目
		define('DB_DSN_GLA', 	'mysql://root:root179@172.18.0.21:3306/gla_db_jfb#utf8');
		define('DB_PREFIX_GLA',	'k_');	
		define('DB_DSN_PAM', 	'mysql://root:root179@172.18.0.21:3306/pam_db_jfb#utf8');
		define('DB_PREFIX_PAM',	'm_');		
		define('DB_DSN_TRA', 	'mysql://root:root179@172.18.0.21:3306/tra_db_jfb#utf8');
		define('DB_PREFIX_TRA',	't_');
		define('DB_DSN_GLA_T', 	'mysql://root:root179@172.18.0.21:3306/gla_db_jfb_t#utf8');
		define('DB_PREFIX_GLA_T',	'k_');	
		define('DB_DSN_JFB', 	'mysql://root:root179@172.18.0.21:3306/jfb#utf8');
		define('DB_PREFIX_JFB',	'jfb_');
		//会员数据同步测试地址
        define('VIP_PUSH_URL', 'http://172.18.0.21:90/');
        // define('VIP_PUSH_URL', 'http://f163w31039.imwork.net/');
        define("MACKEY", "1111111111111111");
        define("SITE_BASE_URL", "http://172.18.0.21/");
        // define("PHP_API_URL", "http://172.18.0.21:8081/");
        define("PHP_API_URL", "http://172.18.0.22:89/");
        define("DB_PREFIX_PAI", "a_");
        define("DB_DSN_PAI", "mysql://root:ruiyit179@172.18.0.21:3306/pai_db_jfb#utf8");
	break;
	case "zhexing":
		//浙星测试
		define('DB_DSN', 		'mysql://root:root179@172.18.0.21:3306/pai_db_jfb#utf8');
		define('DB_PREFIX',		'a_');
		define('C_URL', 		'http://shoudan.xingdata.com');
		
		define('DB_DSN_GLA', 	'mysql://root:root179@172.18.0.21:3306/gla_db_jfb#utf8');
		define('DB_PREFIX_GLA',	'k_');
		
		define('DB_DSN_PAM', 	'mysql://root:root179@172.18.0.21:3306/pam_db_jfb#utf8');
		define('DB_PREFIX_PAM',	'm_');
		
		define('DB_DSN_TRA', 	'mysql://root:root179@172.18.0.21:3306/tra_db_jfb#utf8');
		define('DB_PREFIX_TRA',	't_');
		
		define('DB_DSN_GLA_T', 	'mysql://root:root179@172.18.0.21:3306/gla_db_jfb_t#utf8');
		define('DB_PREFIX_GLA_T',	'k_');	

		//会员数据同步测试地址
		define('VIP_PUSH_URL', 'http://114.215.243.204/');
	break;		
	case "online":
		//线上
		define('DB_DSN', 		'mysql://root:root179@172.18.0.21:3306/pai_db_jfb#utf8');		//192.168.30.152,192.168.30.153
		define('DB_PREFIX',		'a_');
		define('C_URL', 		'http://114.215.243.204');//lfq java项目	
		define('DB_DSN_GLA', 	'mysql://root:root179@172.18.0.21:3306/gla_db_jfb#utf8');		//192.168.30.150,192.168.30.151
		define('DB_PREFIX_GLA',	'k_');	
		define('DB_DSN_PAM', 	'mysql://root:root179@172.18.0.21:3306/pam_db_jfb#utf8');		//192.168.30.141,192.168.30.142
		define('DB_PREFIX_PAM',	'm_');	
		define('DB_DSN_TRA', 	'mysql://root:root179@172.18.0.21:3306/tra_db_jfb#utf8');		//192.168.30.154,192.168.30.155
		define('DB_PREFIX_TRA',	't_');
		define('DB_DSN_GLA_T', 	'mysql://root:root179@172.18.0.21:3306/gla_db_jfb_t#utf8');
		define('DB_PREFIX_GLA_T',	'k_');	
		//会员数据同步测试地址
		define('VIP_PUSH_URL', 'http://114.215.243.204/');
		define("MACKEY", "1111111111111111");
		define("PHP_API_URL", "http://172.16.0.25/");
	break;		
}
?>
