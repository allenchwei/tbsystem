<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @ljf  商户管理
// +----------------------------------------------------------------------
class DaikuanController extends HomeController {
	
	public function _initialize() {
		parent::_initialize();	//权限及登录验证
		$this->MShop	= 'MShop';
		$this->MScert	= 'MScert';
		$this->MSauth	= 'MSauth';
		$this->MSmdr	= 'MSmdr';
		$this->MSbact	= 'MSbact';
		$this->MSrisk	= 'MSrisk';
		$this->MSdkb	= 'MSdkb';
		$this->MScfg	= 'MScfg';
		$this->MSposreq	= 'MSposreq';
		$this->MPos		= 'MPos';
		$this->MBranch	= 'MBranch';
		$this->MPartner	= 'MPartner';
		$this->MCity	= 'MCity';
		$this->MCheck	= 'MCheck';
		$this->MHost 	= 'MHost';
		$this->MShopppp	= 'MShopppp';
		$this->MPosppp	= 'MPosppp';
		$this->MChannel	= 'MChannel';
		$this->GLae		= 'GLae';
		$this->TDkls	= 'TDkls';
		$this->MExcel 	= 'MExcel';
		$this->MSdfb 	= 'MSdfb';
		$this->MBank	= 'MBank';
		$this->MSbank	= 'MSbank';
		$this->MGrade	= 'MGrade';
		$this->MGradefee= 'MGradefee';
	}

	public function daikuan_home(){
		$home = session('HOME');
		$post = I('post');
		$ajax = I('ajax');
		if($ajax == 'loading'){
			$post = array(
				'submit'			=>	I('submit'),
				'CREATE_TIME_A'		=>	I('CREATE_TIME_A'),
				'CREATE_TIME_B'		=>	I('CREATE_TIME_B'),
			);
		}
		$post['CREATE_TIME_A'] = $post['CREATE_TIME_A'] ? $post['CREATE_TIME_A'] : date('Y-m-d');
		$post['CREATE_TIME_B'] = $post['CREATE_TIME_B'] ? $post['CREATE_TIME_B'] : date('Y-m-d');
		if($post['submit'] == "daikuan_home"){
			$where = '1=1';
			$flag = 'a_daikuan.';
			//注册时间	开始
			if($post['CREATE_TIME_A']) {
				$where .= " and ".$flag."CREATE_TIME >= '".date('Y-m-d',strtotime($post['CREATE_TIME_A']))." 00:00:00'";
			}
			//注册时间	结束
			if($post['CREATE_TIME_B']) {
				$where .= " and ".$flag."CREATE_TIME <= '".date('Y-m-d',strtotime($post['CREATE_TIME_B']))." 59:59:59'";
			}
			if($post['USER_NAME'] != '') {
				$where .= " and a_daikuan.USER_NAME = '".$post['USER_NAME']."'";
			}
			if($post['USER_ID'] != '') {
				$where .= " and a_daikuan.USER_ID = '".$post['USER_ID']."'";
			}
			if($post['USER_SEX'] != '') {
				$where .= " and a_daikuan.USER_SEX = '".$post['USER_SEX']."'";
			}
			//金额  开始
			if($post['USER_AGE_A']) {
				$where .= " and ".$flag."USER_AGE >= '".$post['USER_AGE_A']."'";
			}
			//金额	结束
			if($post['USER_AGE_B']) {
				$where .= " and ".$flag."USER_AGE <= '".$post['USER_AGE_B']."'";
			}
			//流
			if($post['MOBILE'] != '') {
				$where .= " and a_daikuan.MOBILE = '".$post['MOBILE']."'";
			}
			if($post['STEP'] != '') {
				$where .= " and a_daikuan.STEP = '".$post['STEP']."'";
			}
			if($post['AMOUNT'] != '') {
				// if ($post['AMOUNT'] == '1') {
				// 	$where .= " and a_daikuan.AMOUNT < 5000";
				// }
				// if ($post['AMOUNT'] == '2') {
				// 	$where .= " and 5000 <= a_daikuan.AMOUNT <= 10000";
				// }
				// if ($post['AMOUNT'] == '3') {
				// 	$where .= " and a_daikuan.AMOUNT > 10000";
				// }
				$where .= " and a_daikuan.AMOUNT = '".$post['AMOUNT']."'";
			}
			$daikuanModel = M('daikuan',DB_PREFIX,DB_DAIKUAN);
			$count = $daikuanModel->alias('a_daikuan')->where($where)->count();
			$p 	   = new \Think\Page($count, C('PAGE_COUNT'));
			$daikuan = $daikuanModel->alias('a_daikuan')->where($where)->limit($p->firstRow.','.$p->listRows)->order('ID desc')->select();
			//分页参数
			$this->assign ( 'totalCount', 	$count );
	       	$this->assign ( 'numPerPage', 	$p->listRows );
	       	$this->assign ( 'currentPage', 	!empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);	

			$this->assign ( 'postdata', 	$post );

			//Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
		}
		$this->assign ( 'postdata', 	$post );
		$timedata = array(
			'jintian_b'		=>	date('Y-m-d'),
			'jintian_n'		=>	date('Y-m-d'),
			'zuotian_b'		=>	date('Y-m-d', strtotime('-1 day')),
			'zuotian_n'		=>	date('Y-m-d', strtotime('-1 day')),
			'benyue_b'		=>	date('Y-m-d', getmonthtime(1)[0]),
			'benyue_n'		=>	date('Y-m-d'),
			'shangyue_b'	=>	date('Y-m-d', getmonthtime(4)[0]),
			'shangyue_n'	=>	date('Y-m-d', getmonthtime(4)[1]),
		);
		$money_type = array('1'=>'3万以下','2'=>'3~5万','3'=>'5~10万','4'=>'10~20万','5'=>'20万以上');//借款情况
		$house_car_type = array('1'=>'有车有房','2'=>'有车无房','3'=>'无车有房','4'=>'无车无房');//房产情况
		$policy_type = array('1'=>'无','2'=>'年缴2400元以上','3'=>'年缴2400元以下');//人寿保险单情况
		$social_found_type = array('1'=>'有社保','2'=>'有公积金','3'=>'两者都有','4'=>'两者都无');//社保公积金情况
        $month_income=array('1'=>'5千以下','2'=>'5千~1万','3'=>'1万以上');
        $job_category=array('1'=>'公司职员','2'=>'私营业主','3'=>'公务员/事业单位');
		$loan_type = array('1'=>'有','2'=>'无');//微粒贷情况
		$user_sex = array("1"=>"男","2"=>"女");//性别
		$step_type = array("1"=>"一","2"=>"二");
		$this->assign('timedata',	$timedata);
		$this->assign('user_sex',	$user_sex);
		$this->assign('money_type', $money_type);
		$this->assign('step_type',  $step_type);
		$this->assign('house_car_type',  $house_car_type);
		$this->assign('policy_type',  $policy_type);
		$this->assign('social_found_type',  $social_found_type);
		$this->assign('job_category',  $job_category);
		$this->assign('month_income',  $month_income);
		$this->assign('loan_type',  $loan_type);
		$this->assign('daikuan',	$daikuan);  //数据库查的结果
		$this->assign('home',	$home);
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}


	/*
	* 贷款信息查询	导出
	**/
	public function daikuan_export() {
		//临时加大PHP占用内存
		// ini_set('memory_limit', '256M'); //内存限制
		set_time_limit(0);
		$where = '1=1';
		$flag = 'a_daikuan.';
		$post = I('post');
		//注册时间	开始
		if($post['CREATE_TIME_A']) {
			$where .= " and ".$flag."CREATE_TIME >= '".date('Y-m-d',strtotime($post['CREATE_TIME_A']))." 00:00:00'";
		}
		//注册时间	结束
		if($post['CREATE_TIME_B']) {
			$where .= " and ".$flag."CREATE_TIME <= '".date('Y-m-d',strtotime($post['CREATE_TIME_B']))." 59:59:59'";
		}
		if($post['USER_NAME'] != '') {
			$where .= " and a_daikuan.USER_NAME = '".$post['USER_NAME']."'";
		}
		if($post['USER_ID'] != '') {
			$where .= " and a_daikuan.USER_ID = '".$post['USER_ID']."'";
		}
		if($post['USER_SEX'] != '') {
			$where .= " and a_daikuan.USER_SEX = '".$post['USER_SEX']."'";
		}
		//金额  开始
		if($post['USER_AGE_A']) {
			$where .= " and ".$flag."USER_AGE >= '".$post['USER_AGE']."'";
		}
		//金额	结束
		if($post['USER_AGE_B']) {
			$where .= " and ".$flag."USER_AGE <= '".$post['USER_AGE']."'";
		}
		//流
		if($post['MOBILE'] != '') {
			$where .= " and a_daikuan.MOBILE = '".$post['MOBILE']."'";
		}
		if($post['STEP'] != '') {
			$where .= " and a_daikuan.STEP = '".$post['STEP']."'";
		}
		if($post['AMOUNT'] != '') {
			$where .= " and a_daikuan.AMOUNT = '".$post['AMOUNT']."'";
		}
		$home = session('HOME');
		//查看权限是否为总部
		// if ($home['CHANNEL_MAP_ID'] != 0) {
		// 	$where .= " and ".$flag."CHANNEL_MAP_ID = '".$home['CHANNEL_MAP_ID']."'";
		// }
		$daikuanModel = M('daikuan',DB_PREFIX,DB_DAIKUAN);
		$count = $daikuanModel->alias('a_daikuan')->where($where)->count();

		//计算
		$numPort = floor($count/C('PAGE_COUNT_EXPORT'));
		$urlPort = __ACTION__.'?submit=ajax&'.http_build_query($post);
		$strPort = '';
		if($count > 0){
			for($i=0; $i<=$numPort; $i++){
				$strPort .= '<p><a href="'.$urlPort.'&p='.($i).'"><button class="ch-btn-skin ch-btn-small ch-icon-copy">贷款文档('.($i+1).')</button></a></p>';
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
			//是否参与分润
			$list = $daikuanModel->alias('a_daikuan')->field('*')->where($where)->order('ID desc')->limit($bRow.','.$eRow)->select();
				
			//导出操作
			$xlsname = '贷款文档('.($p+1).')';
			$xlscell = array(
				array('USER_NAME',		'姓名'),
				array('USER_ID',		'身份证'),
				array('USER_BIRTHDAY',	'出生日期'),
				array('USER_SEX',		'性别'),
				array('MOBILE',			'手机号码'),
				array('AMOUNT',			'借款金额'),
				array('SUPPLIER',		'供应商'),
				array('CHANNEL_MAP_ID',	'渠道'),		
				array('STEP',			'步骤'),
				array('PROVINCE',		'省份'),
				array('CITY',			'市级'),
				array('MONTH_INCOME',	'月收入'),
				array('JOB_CATEGORY',	'职业'),
				array('HOUSE_PROPERTY_CAR',	'资产情况'),
				array('POLICY',			'人寿保险单'),
				array('SOCIAL_SECURITY_FOUND',	'社保公积金'),
				array('PARTICLE_LOAN',	'微粒贷'),
				array('IP_ADDRESS',		'IP地址'),
				array('CREATE_TIME',	'创建时间'),
			);
			// if ($home['CHANNEL_MAP_ID'] == 0) {
				
			// }
			$xlsarray = array();
			foreach($list as $val){
				$xlsarray[] = array(
					'USER_NAME'		=>	$val['USER_NAME'],
					'USER_ID'		=>	$val['USER_ID'],
					'USER_BIRTHDAY'	=>	$val['USER_BIRTHDAY'],
					'USER_SEX'		=>	C('USER_SEX')[$val['USER_SEX']],
					'MOBILE'		=>	$val['MOBILE'],
					'AMOUNT'		=>	C('MONEY_TYPE')[$val['AMOUNT']],
					'SUPPLIER'		=>	$val['SUPPLIER'],
					'CHANNEL_MAP_ID'=>	$val['CHANNEL_MAP_ID'],
					'STEP'			=>	C('STEP_TYPE')[$val['STEP']],
					'PROVINCE'		=>	$val['PROVINCE'],
					'CITY'			=>	$val['CITY'],
					'MONTH_INCOME'	=>	C('MONTH_INCOME')[$val['MONTH_INCOME']],
					'JOB_CATEGORY'	=>	C('JOB_CATEGORY')[$val['JOB_CATEGORY']],
					'HOUSE_PROPERTY_CAR'=>	C('HOUSE_CAR_TYPE')[$val['HOUSE_PROPERTY_CAR']],
					'POLICY'		=>	C('POLICY_TYPE')[$val['POLICY']],
					'SOCIAL_SECURITY_FOUND'=>	C('SOCIAL_FOUND_TYPE')[$val['SOCIAL_SECURITY_FOUND']],
					'PARTICLE_LOAN'	=>	C('LOAN_TYPE')[$val['PARTICLE_LOAN']],
					'IP_ADDRESS'	=>	$val['IP_ADDRESS'],
					'CREATE_TIME'	=>	$val['CREATE_TIME'],
				);
			}
			D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
			exit;
		}
		
		$this->display('Public/export');
	}


	public function baoxian_home(){
        $home = session('HOME');
        $post = I('post');
        $ajax = I('ajax');
        if($ajax == 'loading'){
            $post = array(
                'submit'			=>	I('submit'),
                'CREATE_TIME_A'		=>	I('CREATE_TIME_A'),
                'CREATE_TIME_B'		=>	I('CREATE_TIME_B'),
            );
        }
        $post['CREATE_TIME_A'] = $post['CREATE_TIME_A'] ? $post['CREATE_TIME_A'] : date('Y-m-d');
        $post['CREATE_TIME_B'] = $post['CREATE_TIME_B'] ? $post['CREATE_TIME_B'] : date('Y-m-d');
        if($post['submit'] == "baoxian_home"){
            $where = '1=1';
            $flag = 'a_baoxian.';
            //注册时间	开始
            if($post['CREATE_TIME_A']) {
                $where .= " and ".$flag."CREATE_TIME >= '".date('Y-m-d',strtotime($post['CREATE_TIME_A']))." 00:00:00'";
            }
            //注册时间	结束
            if($post['CREATE_TIME_B']) {
                $where .= " and ".$flag."CREATE_TIME <= '".date('Y-m-d',strtotime($post['CREATE_TIME_B']))." 59:59:59'";
            }
            if($post['USER_NAME'] != '') {
                $where .= " and a_baoxian.USER_NAME = '".$post['USER_NAME']."'";
            }
            if($post['USER_ID'] != '') {
                $where .= " and a_baoxian.USER_ID = '".$post['USER_ID']."'";
            }
            if($post['USER_SEX'] != '') {
                $where .= " and a_baoxian.USER_SEX = '".$post['USER_SEX']."'";
            }
            //金额  开始
            if($post['USER_AGE_A']) {
                $where .= " and ".$flag."USER_AGE >= '".$post['USER_AGE_A']."'";
            }
            //金额	结束
            if($post['USER_AGE_B']) {
                $where .= " and ".$flag."USER_AGE <= '".$post['USER_AGE_B']."'";
            }
            //流
            if($post['MOBILE'] != '') {
                $where .= " and a_baoxian.MOBILE = '".$post['MOBILE']."'";
            }
            if($post['STEP'] != '') {
                $where .= " and a_baoxian.STEP = '".$post['STEP']."'";
            }
            if ($post['CHANNEL_MAP_ID'] != "") {
            	$where .= " and a_baoxian.CHANNEL_MAP_ID = '".$post['CHANNEL_MAP_ID']."'";
            }
            if ($post['KIND_STATUS'] != "") {
            	$where .= " and a_baoxian.KIND_STATUS = '".$post['KIND_STATUS']."'";
            }
            if($post['AMOUNT'] != '') {
                // if ($post['AMOUNT'] == '1') {
                //     $where .= " and a_baoxian.AMOUNT < 5000";
                // }
                // if ($post['AMOUNT'] == '2') {
                //     $where .= " and 5000 <= a_baoxian.AMOUNT <= 10000";
                // }
                // if ($post['AMOUNT'] == '3') {
                //     $where .= " and a_baoxian.AMOUNT > 10000";
                // }
                $where .= " and a_baoxian.AMOUNT = '".$post['AMOUNT']."'";
            }
            $baoxianModel = M('baoxian',DB_PREFIX,DB_DAIKUAN);
            $count = $baoxianModel->alias('a_baoxian')->where($where)->count();
            $p     = new \Think\Page($count, C('PAGE_COUNT'));
            $baoxian = $baoxianModel->alias('a_baoxian')
            						->join("a_channel channel on channel.CHANNEL_MAP_ID = a_baoxian.CHANNEL_MAP_ID")
            						->where($where)
            						->limit($p->firstRow.','.$p->listRows)
            						->select();
            //分页参数
            $this->assign ( 'totalCount',   $count );
            $this->assign ( 'numPerPage',   $p->listRows );
            $this->assign ( 'currentPage',  !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);

            $this->assign ( 'postdata', 	$post );

            //Excel导出参数
			unset($post['submit']);
			$this->assign ( 'exportdata', 	'?'.http_build_query($post) );
        }
        $this->assign ( 'postdata', 	$post );
        //Excel导出参数
		unset($post['submit']);
		$this->assign ( 'exportdata', 	'?'.http_build_query($post) );

        $timedata = array(
            'jintian_b'		=>	date('Y-m-d'),
            'jintian_n'		=>	date('Y-m-d'),
            'zuotian_b'		=>	date('Y-m-d', strtotime('-1 day')),
            'zuotian_n'		=>	date('Y-m-d', strtotime('-1 day')),
            'benyue_b'		=>	date('Y-m-d', getmonthtime(1)[0]),
            'benyue_n'		=>	date('Y-m-d'),
            'shangyue_b'	=>	date('Y-m-d', getmonthtime(4)[0]),
            'shangyue_n'	=>	date('Y-m-d', getmonthtime(4)[1]),
        );
        $channel = $this->get_array("channel","CHANNEL_NAME","CHANNEL_MAP_ID");
        
        $money_type = array('1'=>'3万以下','2'=>'3~5万','3'=>'5~10万','4'=>'10~20万','5'=>'20万以上');//借款情况
        $house_car_type = array('1'=>'有车有房','2'=>'有车无房','3'=>'无车有房','4'=>'无车无房');//房产情况
        $policy_type = array('1'=>'无','2'=>'年缴2400元以上','3'=>'年缴2400元以下');//人寿保险单情况
        $social_found_type = array('1'=>'有社保','2'=>'有公积金','3'=>'两者都有','4'=>'两者都无');//社保公积金情况
        $month_income=array('1'=>'5千以下','2'=>'5千~1万','3'=>'1万以上');
        $job_category=array('1'=>'公司职员','2'=>'私营业主','3'=>'公务员/事业单位');
        $loan_type = array('1'=>'有','2'=>'无');//微粒贷情况
        $user_sex = array("1"=>"男","2"=>"女");//性别
        $step_type = array("1"=>"一","2"=>"二");
        $money_type1 = array("1"=>"10万","2"=>"20万","3"=>"30万","4"=>"50万");
        $kind_status = array("1"=>"成功","2"=>"失败","3"=>"待投保");

        $this->assign("host_result",$channel);
        $this->assign('timedata',	$timedata);
        $this->assign('user_sex',	$user_sex);
        $this->assign('money_type', $money_type);
        $this->assign('step_type',  $step_type);
        $this->assign('house_car_type',  $house_car_type);
        $this->assign('policy_type',  $policy_type);
        $this->assign('social_found_type',  $social_found_type);
        $this->assign('job_category',  $job_category);
        $this->assign('month_income',  $month_income);
        $this->assign('loan_type',  $loan_type);
        $this->assign('money_type1',  $money_type1);
        $this->assign('kind_status',  $kind_status);
        $this->assign('baoxian',	$baoxian);  //数据库查的结果
        \Cookie::set ('_currentUrl_', 		__SELF__);
        $this->display('Daikuan/baoxian_home');
    }


    /*
    * 保险信息查询    导出
    **/
    public function baoxian_export() {
        //临时加大PHP占用内存
        // ini_set('memory_limit', '256M'); //内存限制
        set_time_limit(0);
        $where = '1=1';
        $flag = 'a_baoxian.';
        $post = array(
        	"CREATE_TIME_A"=>I("CREATE_TIME_A"),
        	"CREATE_TIME_B"=>I("CREATE_TIME_B"),
        	"USER_NAME"=>I("USER_NAME"),
        	"USER_ID"=>I("USER_ID"),
        	"USER_SEX"=>I("USER_SEX"),
        	"USER_AGE_A"=>I("USER_AGE_A"),
        	"USER_AGE_B"=>I("USER_AGE_B"),
        	"MOBILE"=>I("MOBILE"),
        	"STEP"=>I("STEP"),
        	"AMOUNT"=>I("AMOUNT"),
        	"KIND_STATUS"=>I("KIND_STATUS"),
        );
        //注册时间  开始
        if($post['CREATE_TIME_A']) {
            $where .= " and ".$flag."CREATE_TIME >= '".date('Y-m-d',strtotime($post['CREATE_TIME_A']))." 00:00:00'";
        }
        //注册时间  结束
        if($post['CREATE_TIME_B']) {
            $where .= " and ".$flag."CREATE_TIME <= '".date('Y-m-d',strtotime($post['CREATE_TIME_B']))." 59:59:59'";
        }
        if($post['USER_NAME'] != '') {
            $where .= " and a_baoxian.USER_NAME = '".$post['USER_NAME']."'";
        }
        if($post['USER_ID'] != '') {
            $where .= " and a_baoxian.USER_ID = '".$post['USER_ID']."'";
        }
        if($post['USER_SEX'] != '') {
            $where .= " and a_baoxian.USER_SEX = '".$post['USER_SEX']."'";
        }
        //金额  开始
        if($post['USER_AGE_A']) {
            $where .= " and ".$flag."USER_AGE >= '".$post['USER_AGE_A']."'";
        }
        //金额    结束
        if($post['USER_AGE_B']) {
            $where .= " and ".$flag."USER_AGE <= '".$post['USER_AGE_B']."'";
        }
        //流
        if($post['MOBILE'] != '') {
            $where .= " and a_baoxian.MOBILE = '".$post['MOBILE']."'";
        }
        if($post['STEP'] != '') {
            $where .= " and a_baoxian.STEP = '".$post['STEP']."'";
        }
        if($post['AMOUNT'] != '') {
            $where .= " and a_baoxian.AMOUNT = '".$post['AMOUNT']."'";
        }
        if ($post['KIND_STATUS'] != "") {
        	$where .= " and a_baoxian.KIND_STATUS = '".$post['KIND_STATUS']."'";
        }
        $home = session('HOME');
        //查看权限是否为总部
        // if ($home['CHANNEL_MAP_ID'] != 0) {
        //  $where .= " and ".$flag."CHANNEL_MAP_ID = '".$home['CHANNEL_MAP_ID']."'";
        // }
        $baoxianModel = M('baoxian',DB_PREFIX,DB_DAIKUAN);
        $count = $baoxianModel->alias('a_baoxian')->where($where)->count();

        //计算
        $numPort = floor($count/C('PAGE_COUNT_EXPORT'));
        $urlPort = __ACTION__.'?submit=ajax&'.http_build_query($post);
        $strPort = '';
        if($count > 0){
            for($i=0; $i<=$numPort; $i++){
                $strPort .= '<p><a href="'.$urlPort.'&p='.($i).'"><button class="ch-btn-skin ch-btn-small ch-icon-copy">保险文档('.($i+1).')</button></a></p>';
            }
        }else{
            $strPort .= '<p>暂无数据可下载~</p>';
        }
        $this->assign ( 'strPort',  $strPort );
        
        //导出
        $submit = I('submit');
        $p      = I('p');
        if($submit == 'ajax'){
            $bRow = $p * C('PAGE_COUNT_EXPORT');
            $eRow = C('PAGE_COUNT_EXPORT');
            //是否参与分润
            $list = $baoxianModel->alias('a_baoxian')->field('*')->where($where)->order('ID desc')->limit($bRow.','.$eRow)->select();
                
            //导出操作
            $xlsname = '保险文档('.($p+1).')';
            $xlscell = array(
                array('USER_NAME',      '姓名'),
                array('USER_ID',        '身份证'),
                array('USER_BIRTHDAY',  '出生日期'),
                array('USER_SEX',       '性别'),
                array('MOBILE',         '手机号码'),
                // array('AMOUNT',         '借款金额'),
                // array('SUPPLIER',       '供应商'),
                array('CHANNEL_MAP_ID', '渠道'),      
                array('STEP',           '步骤'),
                array('PROVINCE',       '省份'),
                array('CITY',           '市级'),
                array('MONTH_INCOME',   '月收入'),
                // array('JOB_CATEGORY',   '职业'),
                array('HOUSE_PROPERTY_CAR', '资产情况'),
                // array('POLICY',         '人寿保险单'),
                // array('SOCIAL_SECURITY_FOUND',  '社保公积金'),
                // array('PARTICLE_LOAN',  '微粒贷'),
                array('POLICY_NUM',  	'保单号'),
                array('KIND_STATUS',  	'投保状态'),
                array('KIND_ACCIDENT',   '投保险种'),
                array('IP_ADDRESS',     'IP地址'),
                array('CREATE_TIME',    '创建时间'),
            );
            $kind_status = array("1"=>"成功","2"=>"失败");

            $xlsarray = array();
            foreach($list as $val){
                $xlsarray[] = array(
                    'USER_NAME'     =>  $val['USER_NAME'],
                    'USER_ID'       =>  $val['USER_ID'],
                    'USER_BIRTHDAY' =>  $val['USER_BIRTHDAY'],
                    'USER_SEX'      =>  C('USER_SEX')[$val['USER_SEX']],
                    'MOBILE'        =>  $val['MOBILE'],
                    // 'AMOUNT'        =>  C('MONEY_TYPE')[$val['AMOUNT']],
                    // 'SUPPLIER'      =>  $val['SUPPLIER'],
                    'CHANNEL_MAP_ID'=>  $val['CHANNEL_MAP_ID'],
                    'STEP'          =>  C('STEP_TYPE')[$val['STEP']],
                    'PROVINCE'      =>  $val['PROVINCE'],
                    'CITY'          =>  $val['CITY'],
                    'MONTH_INCOME'  =>  C('MONTH_INCOME')[$val['MONTH_INCOME']],
                    // 'JOB_CATEGORY'  =>  C('JOB_CATEGORY')[$val['JOB_CATEGORY']],
                    'HOUSE_PROPERTY_CAR'=>  C('HOUSE_CAR_TYPE')[$val['HOUSE_PROPERTY_CAR']],
                    // 'POLICY'        =>  C('POLICY_TYPE')[$val['POLICY']],
                    // 'SOCIAL_SECURITY_FOUND'=>   C('SOCIAL_FOUND_TYPE')[$val['SOCIAL_SECURITY_FOUND']],
                    // 'PARTICLE_LOAN' =>  C('LOAN_TYPE')[$val['PARTICLE_LOAN']],
                    'POLICY_NUM'    =>  $val['POLICY_NUM'],
                    'KIND_STATUS'    =>  $kind_status[$val['KIND_STATUS']],
                    'KIND_ACCIDENT'  =>  $val['KIND_ACCIDENT'],
                    'IP_ADDRESS'    =>  $val['IP_ADDRESS'],
                    'CREATE_TIME'   =>  $val['CREATE_TIME'],
                );
            }
            D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
            exit;
        }
        
        $this->display('Public/export');
    }
	

	/*抽奖统计管理显示*/
	public function luck_record(){
		$post = I('post');
		$post['CREATE_TIME_A'] = $post['CREATE_TIME_A'] ? $post['CREATE_TIME_A'] : date('Y-m-d');
		$post['CREATE_TIME_B'] = $post['CREATE_TIME_B'] ? $post['CREATE_TIME_B'] : date('Y-m-d');
		if ($post['submit'] == 'luck_record') {
			$where = '1=1';
			$flag = 't.';
			//日期	开始
			if($post['CREATE_TIME_A']) {
				// $where .= " and ".$flag."DATE_TIME >= '".date('Y-m-d',strtotime($post['CREATE_TIME_A']))." 00:00:00'";
			}
			//日期	结束
			if($post['CREATE_TIME_B']) {
				// $where .= " and ".$flag."DATE_TIME <= '".date('Y-m-d',strtotime($post['CREATE_TIME_B']))." 23:59:59'";
			}
			if ($post['CHANNEL_MAP_ID']) {
				$where .= " and r.CHANNEL_MAP_ID = '".$post['CHANNEL_MAP_ID']."'";
			}
			$luck_record_Model = M('luck_record',DB_PREFIX,DB_DAIKUAN);
		 	$count = $luck_record_Model->where($where)->count();
            $p     = new \Think\Page($count, C('PAGE_COUNT'));
			$luck_record = $luck_record_Model->field('*,r.IMAGE_URL as IMAGE_URL')
											->alias('r')
											->join('a_channel c on c.CHANNEL_MAP_ID = r.CHANNEL_MAP_ID')
											->where($where)
											->limit($p->firstRow.','.$p->listRows)
											// ->group('r.ID')
											->order('ID DESC')->select();
            //分页参数
            $this->assign ( 'totalCount',   $count );
            $this->assign ( 'numPerPage',   $p->listRows );
            $this->assign ( 'currentPage',  !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);

			$this->assign('list',$luck_record);
		}
		$this->assign ( 'postdata', 	$post );
		//时间选择
		$timedata = array(
			'jintian_b'		=>	date('Y-m-d'),
			'jintian_n'		=>	date('Y-m-d'),
			'zuotian_b'		=>	date('Y-m-d', strtotime('-1 day')),
			'zuotian_n'		=>	date('Y-m-d', strtotime('-1 day')),
			'benyue_b'		=>	date('Y-m-d', getmonthtime(1)[0]),
			'benyue_n'		=>	date('Y-m-d'),
			'shangyue_b'	=>	date('Y-m-d', getmonthtime(4)[0]),
			'shangyue_n'	=>	date('Y-m-d', getmonthtime(4)[1]),
		);
		$channel = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID',array('CHANNEL_STATUS'=>1));
		$this->assign('channel',$channel);
		$this->assign('timedata', 			$timedata);
		$this->assign('status',C('LUCK_SHAD_STATUS'));
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}

	/*抽奖统计管理添加*/
	public function luck_record_add(){
		$post = I('post');
		if ($post['submit'] == 'luck_record_add') {
			if (empty($post['USER_NAME']) || empty($post['IMAGE_URL']) || empty($post['OFFER']) || $post['WEIGHT'] == '' || empty($post['CHANNEL_MAP_ID'])) {
				$this->wrong('必填参数异常');
			}
			if ($post['IMAGE_URL']) {
				$post['IMAGE_URL'] = str_replace('s_','',$post['IMAGE_URL']);
			}
			// $this->wrong($post['STATUS']);
			$data = array(
					"USER_NAME"=>$post['USER_NAME'],
					"IMAGE_URL"=>$post['IMAGE_URL'],
					"OFFER"=>$post['OFFER'],
					"WEIGHT"=>$post['WEIGHT'],
					"CHANNEL_MAP_ID"=>$post['CHANNEL_MAP_ID'],
					"LINK_URL"=>$post['LINK_URL'],
					"STATUS"=>$post['STATUS'],
					"PRIZE_NAME"=>$post['PRIZE_NAME'],
				);
			$luck_record_Model = M('luck_record',DB_PREFIX,DB_DAIKUAN);
			$luck_record = $luck_record_Model->add($data);
			Add_LOG('Daikuan', __FUNCTION__ . ' ' . __LINE__ . ' luck_record ' . $luck_record_Model->getlastsql());
			if (!$luck_record) {
				$this->wrong('数据添加失败');
			}
			$this->right('添加成功', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$channel = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID',array('CHANNEL_STATUS'=>1));
		$this->assign('channel',$channel);
		$this->display();
	}

	/*抽奖统计管理添加*/
	public function luck_record_edit(){
		$post = I('post');
		$luck_record_Model = M('luck_record',DB_PREFIX,DB_DAIKUAN);
		if($post['submit'] == "luck_record_edit") {
			if (empty($post['CHANNEL_MAP_ID'])) {
				$this->wrong('参数异常');
			}
			if ($post['IMAGE_URL']) {
				$post['IMAGE_URL'] = str_replace('s_','',$post['IMAGE_URL']);
			}
			$data = array(
					"USER_NAME"=>$post['USER_NAME'],
					"IMAGE_URL"=>$post['IMAGE_URL'],
					"LINK_URL"=>$post['LINK_URL'],
					"OFFER"=>$post['OFFER'],
					"WEIGHT"=>$post['WEIGHT'],
					"CHANNEL_MAP_ID"=>$post['CHANNEL_MAP_ID'],
					"STATUS"=>$post['STATUS'],
					"PRIZE_NAME"=>$post['PRIZE_NAME'],
				);
			Add_LOG('Daikuan', __FUNCTION__ . ' ' . __LINE__ . ' data ' . json_encode($data));
			// $this->wrong(json_encode($data));
			$luck_record = $luck_record_Model->where(array('ID'=>$post['ID']))->save($data);
			Add_LOG('Daikuan', __FUNCTION__ . ' ' . __LINE__ . ' luck_record ' . $luck_record_Model->getlastsql());
			if (!$luck_record) {
				$this->wrong('修改失败');
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$luck_record = $luck_record_Model->where(array('ID'=>$id))->find();
		$channel = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID',array('CHANNEL_STATUS'=>1));
		$this->assign('channel',$channel);
		$this->assign('info',$luck_record);
		$this->display('luck_record_add');
	}

	/*抽奖图片管理*/
	public function luck_photo(){
		$post = I('post');
		if ($post['submit'] == 'luck_photo') {
			$where = '1=1 and c.CHANNEL_STATUS = 1';
			if ($post['CHANNEL_MAP_ID']) {
				$where .= ' and s.CHANNEL_MAP_ID = "'.$post['CHANNEL_MAP_ID'].'"';
			}
			$luck_shad_Model = M('luck_shad',DB_PREFIX,DB_DAIKUAN);
		 	$count = $luck_shad_Model->where($where)->count();
            $p     = new \Think\Page($count, C('PAGE_COUNT'));
			$luck_shad = $luck_shad_Model->field('*,s.IMAGE_URL as IMAGE_URL')
										->alias('s')
										->join('a_channel c on c.CHANNEL_MAP_ID = s.CHANNEL_MAP_ID')
										->where($where)
										->limit($p->firstRow.','.$p->listRows)->order('ID DESC')->select();
            //分页参数
            $this->assign ( 'totalCount',   $count );
            $this->assign ( 'numPerPage',   $p->listRows );
            $this->assign ( 'currentPage',  !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);

			$this->assign('list',$luck_shad);
		}
		$channel = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID',array('CHANNEL_STATUS'=>1));
		$this->assign('channel',$channel);
		$this->assign('type',C('LUCK_SHAD_TYPE'));
		$this->assign('status',C('LUCK_SHAD_STATUS'));
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}
	
	/*
		@param 	$mysqldb    数据表名称
				$fieldname  已知字段
				$selectname 查询字段
		@return $host_result 
 	*/
	private function get_array($mysqldb,$fieldname,$selectname,$where='1=1'){
		$adModel = M($mysqldb,DB_PREFIX,DB_DAIKUAN);
		$find_host = $adModel->where($where)->field($fieldname)->select();
		$host_result = array();
		foreach ($find_host as $key => $value) {
			foreach ($value as $k => $val) {
				$find_host = $adModel->where($fieldname."='$val'")->field($selectname)->find();
				$host_id = $find_host[$selectname];
				$host_result[$host_id] = $val;
			}
		}
		return $host_result;
	}

	/*抽奖图片添加*/
	public function luck_photo_add(){
		$post = I('post');
		if ($post['submit'] == 'luck_photo_add') {
			if (empty($post['CHANNEL_MAP_ID']) || empty($post['IMAGE_URL']) || empty($post['TYPE'])) {
				$this->wrong('必填参数异常');
			}
			$post['IMAGE_URL'] = str_replace('s_','',$post['IMAGE_URL']);
			$data = array(
					"IMAGE_URL"=>$post['IMAGE_URL'],
					"LINK_URL"=>$post['LINK_URL'],
					"TYPE"=>$post['TYPE'],
					"CHANNEL_MAP_ID"=>$post['CHANNEL_MAP_ID'],
					"STATUS"=>$post['STATUS']
				);
			$luck_photo_Model = M('luck_shad',DB_PREFIX,DB_DAIKUAN);
			$luck_photo = $luck_photo_Model->add($data);
			if (!$luck_photo) {
				$this->wrong('图片添加失败');
			}
			$this->right('图片添加成功！');
		}
		$channel = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID',array('CHANNEL_STATUS'=>1));
		$this->assign('channel',$channel);
		$this->assign('type',C('LUCK_SHAD_TYPE'));
		$this->display();
	}

	/*抽奖图片修改*/
	public function luck_photo_edit(){
		$post = I('post');
		$luck_photo_Model = M('luck_shad',DB_PREFIX,DB_DAIKUAN);
		if ($post['submit'] == 'luck_photo_edit') {
			if (empty($post['CHANNEL_MAP_ID']) || empty($post['IMAGE_URL']) || empty($post['TYPE'])) {
				$this->wrong('必填参数异常');
			}
			$post['IMAGE_URL'] = str_replace('s_','',$post['IMAGE_URL']);
			$data = array(
					"IMAGE_URL"=>$post['IMAGE_URL'],
					"LINK_URL"=>$post['LINK_URL'],
					"TYPE"=>$post['TYPE'],
					"CHANNEL_MAP_ID"=>$post['CHANNEL_MAP_ID'],
					"STATUS"=>$post['STATUS'],
				);
			$luck_photo = $luck_photo_Model->where(array('ID'=>$post['ID']))->save($data);
			if (!$luck_photo) {
				$this->wrong('修改失败');
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$luck_record = $luck_photo_Model->where(array('ID'=>$id))->find();
		$channel = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID',array('CHANNEL_STATUS'=>1));
		$this->assign('channel',$channel);
		$this->assign('type',C('LUCK_SHAD_TYPE'));
		$this->assign('info',$luck_record);
		$this->display('luck_photo_add');
	}

	/*转盘点击统计*/
	public function click_total(){
		$post = I('post');
		$post['CREATE_TIME_A'] = $post['CREATE_TIME_A'] ? $post['CREATE_TIME_A'] : date('Y-m-d');
		$post['CREATE_TIME_B'] = $post['CREATE_TIME_B'] ? $post['CREATE_TIME_B'] : date('Y-m-d');
		if ($post['submit'] == 'click_total') {
			$where = '1=1';
			$flag = 't.';
			//日期	开始
			if($post['CREATE_TIME_A']) {
				$where .= " and ".$flag."DATE_TIME >= '".date('Y-m-d',strtotime($post['CREATE_TIME_A']))." 00:00:00'";
			}
			//日期	结束
			if($post['CREATE_TIME_B']) {
				$where .= " and ".$flag."DATE_TIME <= '".date('Y-m-d',strtotime($post['CREATE_TIME_B']))." 23:59:59'";
			}
			if ($post['CHANNEL_MAP_ID']) {
				$where .= " and t.CHANNEL_MAP_ID = '".$post['CHANNEL_MAP_ID']."'";
			}
			$trace_Model = M('trace',DB_PREFIX,DB_DAIKUAN);
		 	$count = $trace_Model->alias('t')
								 ->join('a_channel c on c.CHANNEL_MAP_ID = t.CHANNEL_MAP_ID')
								 ->join('a_luck_record r on r.ID = t.RECORD_ID')
								 ->where($where)
								 ->group('RECORD_ID')->count();
            $p     = new \Think\Page($count, C('PAGE_COUNT'));

			// $TRACE_PUSH = $trace_Model->alias('t')
			// 					 ->field("RECORD_ID,USER_NAME,CHANNEL_NAME,DATE_TIME,ifnull((SELECT CASE WHEN TRACE_PV = NULL THEN 0 ELSE (select count(TRACE_PV) from a_trace where TRACE_PV = 1) END),0) AS TRACE_PV,
			// 					 	ifnull((SELECT CASE WHEN ZP_CLICK = NULL THEN 0 ELSE (select count(ZP_CLICK) from a_trace where ZP_CLICK = 1) END),0) AS ZP_CLICK,count(TRACE_PUSH) as TRACE_PUSH
			// 					 	")
			// 					 ->join('a_channel c on c.CHANNEL_MAP_ID = t.CHANNEL_MAP_ID')
			// 					 ->join('a_luck_record r on r.ID = t.RECORD_ID')
			// 					 // ->join('a_luck_shad s on s.ID = t.PHOTO_ID')
			// 					 ->where('RECORD_ID is not null and TRACE_PUSH = 1 and '.$where)
			// 					 ->limit($p->firstRow.','.$p->listRows)
			// 					 ->order('TRACE_ID DESC')
			// 					 ->group('RECORD_ID')
			// 					 ->select();
			// $TRACE_CLICK = $trace_Model->alias('t')
			// 					 ->field("RECORD_ID,ifnull(count(TRACE_CLICK),0) as TRACE_CLICK")
			// 					 ->join('a_channel c on c.CHANNEL_MAP_ID = t.CHANNEL_MAP_ID')
			// 					 ->join('a_luck_record r on r.ID = t.RECORD_ID')
			// 					 ->where('RECORD_ID is not null and TRACE_CLICK = 1 and '.$where)
			// 					 ->limit($p->firstRow.','.$p->listRows)
			// 					 ->order('TRACE_ID DESC')
			// 					 ->group('RECORD_ID')
			// 					 ->select();
			// foreach ($TRACE_PUSH as $key => $value) {
			// 	foreach ($TRACE_CLICK as $k => $val) {
			// 		if ($value['RECORD_ID'] == $val['RECORD_ID']) {
			// 			// unset($val['RECORD_ID']);
			// 			$trace[] = $value + $val;
			// 		}
			// 	}
			// }
			// $trace = array_merge($TRACE_PUSH,$trace);
			// // var_dump($trace);
			// foreach ($trace as $key => $value) {
			// 	if (isset($value['TRACE_CLICK'])) {
			// 		$record_id[] = $value['RECORD_ID'];
			// 	}
			// }
			// foreach ($trace as $key => $value) {
			// 	for ($i=0; $i < count($record_id); $i++) { 
			// 		if ($record_id[$i] == $value['RECORD_ID'] && !array_key_exists("TRACE_CLICK",$value)) {
			// 			unset($trace[$key]);
			// 		}
			// 	}
			// }
			// ifnull((SELECT CASE WHEN ZP_CLICK = 0 THEN (select sum(ZP_CLICK) from a_trace t inner join a_channel c on t.CHANNEL_MAP_ID = c.CHANNEL_MAP_ID where $where) ELSE 0 END),0) AS ZP_CLICK
			$time = date('Y-m-d',time());
			$record_Model = M('luck_record',DB_PREFIX,DB_DAIKUAN);
			$trace = $record_Model->alias('r')
							->field("r.USER_NAME,c.CHANNEL_NAME,t.TRACE_PV,t.TRACE_PUSH,t.TRACE_CLICK,t.DATE_TIME,
								ifnull((SELECT CASE WHEN TRACE_PV = 0 THEN (select sum(TRACE_PV) from a_trace t inner join a_channel c on t.CHANNEL_MAP_ID = c.CHANNEL_MAP_ID where $where) ELSE 0 END),0) AS TRACE_PV,
								ifnull((SELECT CASE WHEN ZP_CLICK = 0 THEN (select sum(ZP_CLICK) from a_trace t inner join a_channel c on t.CHANNEL_MAP_ID = c.CHANNEL_MAP_ID where $where) ELSE 0 END),0) AS ZP_CLICK,
								r.OFFER * t.TRACE_CLICK as AMOUNT,
								FORMAT((t.TRACE_CLICK / t.TRACE_PUSH) * 100, 0) as RATE,r.OFFER,r.PRIZE_NAME")
							->join('a_channel c on c.CHANNEL_MAP_ID = r.CHANNEL_MAP_ID')
							->join('a_trace t on r.ID = t.RECORD_ID','LEFT')
							->where($where)->group('r.ID,c.CHANNEL_MAP_ID')->select();
			// var_dump($record_Model->getlastsql());
            //分页参数
            $this->assign ( 'totalCount',   $count );
            $this->assign ( 'numPerPage',   $p->listRows );
            $this->assign ( 'currentPage',  !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);

			$this->assign('list',$trace);
		}
		$this->assign ( 'postdata', 	$post );
		//时间选择
		$timedata = array(
			'jintian_b'		=>	date('Y-m-d'),
			'jintian_n'		=>	date('Y-m-d'),
			'zuotian_b'		=>	date('Y-m-d', strtotime('-1 day')),
			'zuotian_n'		=>	date('Y-m-d', strtotime('-1 day')),
			'benyue_b'		=>	date('Y-m-d', getmonthtime(1)[0]),
			'benyue_n'		=>	date('Y-m-d'),
			'shangyue_b'	=>	date('Y-m-d', getmonthtime(4)[0]),
			'shangyue_n'	=>	date('Y-m-d', getmonthtime(4)[1]),
		);
		$channel = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID',array('CHANNEL_STATUS'=>1));
		$this->assign('channel',$channel);
		$this->assign('type',C('LUCK_SHAD_TYPE'));
		$this->assign('timedata', 			$timedata);
		$this->assign('status',C('LUCK_SHAD_STATUS'));
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}

	public function test(){
		$trace_Model = M('trace',DB_PREFIX,DB_DAIKUAN);
		$record_Model = M('luck_record',DB_PREFIX,DB_DAIKUAN);
		$record = $record_Model->select();
		// var_dump($record);
		$trace = $trace_Model->alias('t')
							->field('count(TRACE_PUSH) as TRACE_PUSH')
							->join('a_channel c on c.CHANNEL_MAP_ID = t.CHANNEL_MAP_ID')
							->where('1=1')->select();
		var_dump($trace);
	}
	
	/*渠道管理*/
	public function channel(){
		$post = I('post');
		if ($post['submit'] == 'channel') {
			$where = '1=1';
			if ($post['CHANNEL_MAP_ID']) {
				$where .= ' and CHANNEL_MAP_ID = "'.$post['CHANNEL_MAP_ID'].'"';
			}
			if ($post['CHANNEL_NAME']) {
				$where .= ' and CHANNEL_NAME like "%'.$post['CHANNEL_NAME'].'%"';
			}
			$channel_Model = M('channel',DB_PREFIX,DB_DAIKUAN);
		 	$count = $channel_Model->where($where)->count();
            $p     = new \Think\Page($count, C('PAGE_COUNT'));
			$list = $channel_Model->where($where)->limit($p->firstRow.','.$p->listRows)->order('CHANNEL_MAP_ID DESC')->select();
			// echo $channel_Model->getlastsql();
            //分页参数
            $this->assign ( 'totalCount',   $count );
            $this->assign ( 'numPerPage',   $p->listRows );
            $this->assign ( 'currentPage',  !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);

			$this->assign('list',$list);
		}
		$channel = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID');
		$this->assign('channel',$channel);
		$this->assign('type',C('LUCK_SHAD_TYPE'));
		$this->assign('status',C('LUCK_SHAD_STATUS'));
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}

	/*渠道管理添加*/
	public function channel_add(){
		$post = I('post');
		if ($post['submit'] == 'channel_add') {
			if (empty($post['CHANNEL_NAME']) || empty($post['IMAGE_URL']) || empty($post['TIME'])) {
				$this->wrong('必填参数异常');
			}
			if ($post['IMAGE_URL']) {
				$post['IMAGE_URL'] = str_replace('s_','',$post['IMAGE_URL']);
			}
			$data = array(
					"CHANNEL_NAME"=>$post['CHANNEL_NAME'],
					"CHANNEL_STATUS"=>$post['CHANNEL_STATUS'],
					"IMAGE_URL"=>$post['IMAGE_URL'],
					"TIME"=>$post['TIME'],
				);
			$channel_Model = M('channel',DB_PREFIX,DB_DAIKUAN);
			$list = $channel_Model->add($data);
			if (!$list) {
				$this->wrong('添加失败');
			}
			$channel_key = substr(md5($list), 8, 16);
			$update = $channel_Model->where(array('CHANNEL_MAP_ID'=>$list))->save(array('CHANNEL_KEY'=>$channel_key));
			if (!$update) {
				$this->wrong('添加KEY失败');
			}
			$this->right('添加成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$this->assign('type',C('LUCK_SHAD_TYPE'));
		$this->display();
	}

	/*渠道管理修改*/
	public function channel_edit(){
		$post = I('post');
		$channel_Model = M('channel',DB_PREFIX,DB_DAIKUAN);
		if ($post['submit'] == 'channel_edit') {
			if (empty($post['CHANNEL_NAME']) || empty($post['IMAGE_URL']) || empty($post['TIME'])) {
				$this->wrong('必填参数异常');
			}
			if ($post['IMAGE_URL']) {
				$post['IMAGE_URL'] = str_replace('s_','',$post['IMAGE_URL']);
			}
			$data = array(
					"CHANNEL_NAME"=>$post['CHANNEL_NAME'],
					"CHANNEL_STATUS"=>$post['CHANNEL_STATUS'],
					"IMAGE_URL"=>$post['IMAGE_URL'],
					"TIME"=>$post['TIME'],
				);
			$channel = $channel_Model->where(array('CHANNEL_MAP_ID'=>$post['CHANNEL_MAP_ID']))->save($data);
			if (!$channel) {
				$this->wrong('修改失败');
			}
			$channel = $channel_Model->field('CHANNEL_MAP_ID,CHANNEL_KEY')->where(array('CHANNEL_MAP_ID'=>$post['CHANNEL_MAP_ID']))->find();
			if (empty($channel['CHANNEL_KEY'])) {
				$channel_key = substr(md5($post['CHANNEL_MAP_ID']), 8, 16);
				$update = $channel_Model->where(array('CHANNEL_MAP_ID'=>$post['CHANNEL_MAP_ID']))->save(array('CHANNEL_KEY'=>$channel_key));
				if (!$update) {
					$this->wrong('修改KEY失败');
				}
			}
			$this->right('修改成功！', 'closeCurrent', \Cookie::get ( '_currentUrl_' ), $_REQUEST['navTabId']);
		}
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数！');
		}
		$luck_record = $channel_Model->where(array('CHANNEL_MAP_ID'=>$id))->find();
		$this->assign('type',C('LUCK_SHAD_TYPE'));
		$this->assign('info',$luck_record);
		$this->display('channel_add');
	}

	/*第三方点击统计*/
	public function other_click(){
		$post = I('post');
		$post['CREATE_TIME_A'] = $post['CREATE_TIME_A'] ? $post['CREATE_TIME_A'] : date('Y-m-d');
		$post['CREATE_TIME_B'] = $post['CREATE_TIME_B'] ? $post['CREATE_TIME_B'] : date('Y-m-d');
		if ($post['submit'] == 'other_click') {
			$where = '1=1';
			$flag = 't.';
			//日期	开始
			if($post['CREATE_TIME_A']) {
				$where .= " and ".$flag."DATE_TIME >= '".date('Y-m-d',strtotime($post['CREATE_TIME_A']))." 00:00:00'";
			}
			//日期	结束
			if($post['CREATE_TIME_B']) {
				$where .= " and ".$flag."DATE_TIME <= '".date('Y-m-d',strtotime($post['CREATE_TIME_B']))." 23:59:59'";
			}
			if ($post['CHANNEL_MAP_ID']) {
				$where .= " and t.CHANNEL_MAP_ID = '".$post['CHANNEL_MAP_ID']."'";
			}
			$trace_Model = M('trace',DB_PREFIX,DB_DAIKUAN);
		 	$count = $trace_Model->alias('t')
								 ->join('a_channel c on c.CHANNEL_MAP_ID = t.CHANNEL_MAP_ID')
								 ->join('a_luck_record r on r.ID = t.PHOTO_ID')
								 ->where($where)
								 ->group('PHOTO_ID')->count();
            $p     = new \Think\Page($count, C('PAGE_COUNT'));
            // ifnull((SELECT CASE WHEN TRACE_PUSH = NULL THEN 0 ELSE (select count(TRACE_PUSH) from a_trace where TRACE_PUSH = 1 and PHOTO_ID is not NULL) END),0) AS TRACE_PUSH,
			// $trace = $trace_Model->alias('t')
			// 					 ->field("*,COUNT(TRACE_CLICK) as TRACE_CLICK,
			// 					 	COUNT(DISTINCT TRACE_PUSH) as TRACE_PUSH")
			// 					 ->join('a_channel c on c.CHANNEL_MAP_ID = t.CHANNEL_MAP_ID')
			// 					 ->join('a_luck_shad s on s.ID = t.PHOTO_ID')
			// 					 ->where($where)
			// 					 ->limit($p->firstRow.','.$p->listRows)
			// 					 ->order('TRACE_ID DESC')
			// 					 ->group('PHOTO_ID')
			// 					 ->select();


			// $TRACE_CLICK = $trace_Model->alias('t')->field('CHANNEL_NAME,TYPE,DATE_TIME,PHOTO_ID,count(TRACE_CLICK) as TRACE_CLICK')
			// 					 ->join('a_channel c on c.CHANNEL_MAP_ID = t.CHANNEL_MAP_ID')
			// 					 ->join('a_luck_shad s on s.ID = t.PHOTO_ID')
			// 					 ->where('PHOTO_ID is not null and TRACE_CLICK = 1 and '.$where)
			// 					 ->group('PHOTO_ID')->select();

			// $TRACE_PUSH = $trace_Model->alias('t')->field('PHOTO_ID,count(TRACE_PUSH) as TRACE_PUSH')
			// 					 ->join('a_channel c on c.CHANNEL_MAP_ID = t.CHANNEL_MAP_ID')
			// 					 ->join('a_luck_shad s on s.ID = t.PHOTO_ID')
			// 					 ->where('PHOTO_ID is not null and TRACE_PUSH = 1 and '.$where)
			// 					 ->group('PHOTO_ID')->select();
			// foreach ($TRACE_CLICK as $key => $value) {
			// 	foreach ($TRACE_PUSH as $k => $val) {
			// 		if ($value['PHOTO_ID'] == $val['PHOTO_ID']) {
			// 			unset($val['PHOTO_ID']);
			// 			$trace[] = $value + $val;
			// 		}
			// 	}
			// }

            $time = date('Y-m-d',time());
			$record_Model = M('luck_shad',DB_PREFIX,DB_DAIKUAN);
			$trace = $record_Model->alias('r')
							->field("r.TYPE,c.CHANNEL_NAME,t.TRACE_PUSH,t.TRACE_CLICK,t.DATE_TIME,FORMAT((t.TRACE_CLICK / t.TRACE_PUSH) * 100, 0) as CLICK_RATE")
							->join('a_channel c on c.CHANNEL_MAP_ID = r.CHANNEL_MAP_ID')
							->join('a_trace t on r.ID = t.PHOTO_ID','LEFT')
							->where($where)->group('r.ID,c.CHANNEL_MAP_ID')->select();
			// var_dump($trace);
			// echo $trace_Model->getlastsql();
            //分页参数
            $this->assign ( 'totalCount',   $count );
            $this->assign ( 'numPerPage',   $p->listRows );
            $this->assign ( 'currentPage',  !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);

			$this->assign('list',$trace);
		}
		$this->assign ( 'postdata', 	$post );
		//时间选择
		$timedata = array(
			'jintian_b'		=>	date('Y-m-d'),
			'jintian_n'		=>	date('Y-m-d'),
			'zuotian_b'		=>	date('Y-m-d', strtotime('-1 day')),
			'zuotian_n'		=>	date('Y-m-d', strtotime('-1 day')),
			'benyue_b'		=>	date('Y-m-d', getmonthtime(1)[0]),
			'benyue_n'		=>	date('Y-m-d'),
			'shangyue_b'	=>	date('Y-m-d', getmonthtime(4)[0]),
			'shangyue_n'	=>	date('Y-m-d', getmonthtime(4)[1]),
		);
		$channel = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID',array('CHANNEL_STATUS'=>1));
		$this->assign('channel',$channel);
		$this->assign('type',C('LUCK_SHAD_TYPE'));
		$this->assign('timedata', 			$timedata);
		$this->assign('status',C('LUCK_SHAD_STATUS'));
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}

	/*转盘统计管理*/
	public function zp_count(){
		$post = I('post');
		$post['CREATE_TIME_A'] = $post['CREATE_TIME_A'] ? $post['CREATE_TIME_A'] : date('Y-m-d');
		$post['CREATE_TIME_B'] = $post['CREATE_TIME_B'] ? $post['CREATE_TIME_B'] : date('Y-m-d');
		if ($post['submit'] == 'zp_count') {
			$where = '1=1 and RECORD_ID is NULL and PHOTO_ID is NULL ';
			$flag = 't.';
			//日期	开始
			if($post['CREATE_TIME_A']) {
				$where .= " and ".$flag."DATE_TIME >= '".date('Y-m-d',strtotime($post['CREATE_TIME_A']))." 00:00:00'";
			}
			//日期	结束
			if($post['CREATE_TIME_B']) {
				$where .= " and ".$flag."DATE_TIME <= '".date('Y-m-d',strtotime($post['CREATE_TIME_B']))." 23:59:59'";
			}
			if ($post['CHANNEL_MAP_ID']) {
				$where .= " and t.CHANNEL_MAP_ID = '".$post['CHANNEL_MAP_ID']."'";
			}
			$trace_Model = M('trace',DB_PREFIX,DB_DAIKUAN);
		 	$count = $trace_Model->alias('t')
								 ->join('a_channel c on c.CHANNEL_MAP_ID = t.CHANNEL_MAP_ID')
								 ->join('a_luck_record r on r.ID = t.PHOTO_ID')
								 ->where($where)
								 ->group('PHOTO_ID')->count();
            $p     = new \Think\Page($count, C('PAGE_COUNT'));
            $time = date('Y-m-d',time());
			$record_Model = M('trace',DB_PREFIX,DB_DAIKUAN);
			$trace = $record_Model->alias('t')
							->field("c.CHANNEL_NAME,t.TRACE_PV,t.TRACE_UV,t.DATE_TIME,t.ZP_CLICK,t.ZP_CLICK/t.TRACE_PV as PV_COUNT,t.ZP_CLICK/t.TRACE_UV as UV_COUNT")
							->join('a_channel c on c.CHANNEL_MAP_ID = t.CHANNEL_MAP_ID')
							->where($where)
							->group('t.CHANNEL_MAP_ID')->select();
			// var_dump($trace);
			// echo $trace_Model->getlastsql();
            //分页参数
            $this->assign ( 'totalCount',   $count );
            $this->assign ( 'numPerPage',   $p->listRows );
            $this->assign ( 'currentPage',  !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);

			$this->assign('list',$trace);
		}
		$this->assign ( 'postdata', 	$post );
		//时间选择
		$timedata = array(
			'jintian_b'		=>	date('Y-m-d'),
			'jintian_n'		=>	date('Y-m-d'),
			'zuotian_b'		=>	date('Y-m-d', strtotime('-1 day')),
			'zuotian_n'		=>	date('Y-m-d', strtotime('-1 day')),
			'benyue_b'		=>	date('Y-m-d', getmonthtime(1)[0]),
			'benyue_n'		=>	date('Y-m-d'),
			'shangyue_b'	=>	date('Y-m-d', getmonthtime(4)[0]),
			'shangyue_n'	=>	date('Y-m-d', getmonthtime(4)[1]),
		);
		$channel = $this->get_array('channel','CHANNEL_NAME','CHANNEL_MAP_ID',array('CHANNEL_STATUS'=>1));
		$this->assign('channel',$channel);
		$this->assign('type',C('LUCK_SHAD_TYPE'));
		$this->assign('timedata', 			$timedata);
		$this->assign('status',C('LUCK_SHAD_STATUS'));
		\Cookie::set ('_currentUrl_', 		__SELF__);
		$this->display();
	}

	public function channel_safe(){
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$baoxianModel = M('baoxian',DB_PREFIX,DB_DAIKUAN);
		$data = $baoxianModel->where(array('ID'=>$id))->find();
		// var_dump($data);
		if ($data['MONTH_INCOME'] == '1') {
            $month_income = "500000";
        }elseif ($data['MONTH_INCOME'] == '2') {
            $month_income = "1000000";
        }elseif ($data['MONTH_INCOME'] == '3') {
            $month_income = "2000000";
        }else{
            $month_income = "500000";
        }

        if ($data['HOUSE_PROPERTY_CAR'] == '1') {
            $house_property_car = "有车有房";
        }elseif ($data['HOUSE_PROPERTY_CAR'] == '2') {
            $house_property_car = "有车无房";
        }elseif ($data['HOUSE_PROPERTY_CAR'] == '3') {
            $house_property_car = "无车有房";
        }elseif ($data['HOUSE_PROPERTY_CAR'] == '4') {
            $house_property_car = "无车无房";
        }

        if ($data['CHILDREN'] == '1') {
            $children = "一个";
        }elseif ($data['CHILDREN'] == '2') {
            $children = "两个或以上";
        }elseif ($data['CHILDREN'] == '3') {
            $children = "没有";
        }

        if ($data['TOURISM'] == '1') {
            $tourism = '自驾';
        }elseif ($data['TOURISM'] == '2') {
            $tourism = "火车或公交";
        }elseif ($data['TOURISM'] == '3') {
            $tourism = "飞机";
        }

        if ($data['GUARANTEE'] == '1') {
            $guarantee = '意外保障';
        }elseif ($data['GUARANTEE'] == '2') {
            $guarantee = '重疾保障';
        }elseif ($data['GUARANTEE'] == '3') {
            $guarantee = '医疗保障';
        }

        if ($data['MONEY'] == '1') {
            $sumInsured = '10000000';
        }elseif ($data['MONEY'] == '2') {
            $sumInsured = '20000000';
        }elseif ($data['MONEY'] == '3') {
            $sumInsured = '30000000';
        }elseif ($data['MONEY'] == '4') {
            $sumInsured = '50000000';
        }

        if ($data['POLICY_ONLINE'] == '1') {
            $policy_online = '有';
        }elseif ($data['POLICY_ONLINE'] == '2') {
            $policy_online = '无';
        }

        if ($data['PAY'] == '1') {
            $pay = '年缴';
        }

        $url = 'http://xbbapi.data88.cn/insurance/doInsure';
        $key = '3be20a0334e99695e123b54df785d2f3';
        $para = array();
        $para['adCode'] = '601459d8';//投放编码
        $para['policyHolderName'] = $data['USER_NAME'];//投保人姓名
        $para['mobile'] = $data['MOBILE'];//手机号码
        $para['policyHolderIdCard'] = $data['USER_ID'];//投保人身份证号
        $para['activityConfigNum'] = 0;//活动配置号
        $para['premiumInfo'] = array("paymentType"=>"ANNUAL","sumInsured"=>$sumInsured);//测保字段
        $para['questionnaire'] = array(
            array('question'=>'您的月收入是多少？','answers'=>array($month_income)),
            array("question"=>"您的资产状况？","answers"=>array($house_property_car)),
            array("question"=>"请问您是否已有子女？","answers"=>array($children)),
            array("question"=>"请问您平时与家人出游多以什么方式？","answers"=>array($tourism)),
            array("question"=>"如果让您选择您更倾向于哪种商业保障？","answers"=>array($guarantee)),
            array("question"=>"您的期望保障金额是？","answers"=>array($sumInsured)),
            array("question"=>"您目前是否已有在效保单？","answers"=>array($policy_online)),
            // array("question"=>"缴费方式","answers"=>array($pay))
        );
        $para['fromIp'] = $data['IP_ADDRESS'] ? $data['IP_ADDRESS'] : $_SERVER["REMOTE_ADDR"];//客户ip
        $para['sign'] = md5($para['adCode'].$key.$para['mobile']);
        $para = json_encode($para,JSON_UNESCAPED_UNICODE);
        // var_dump($para);exit;
        Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' para ' . $para);
        $charge = getCurlDataByjson($url,$para);
        $return = json_encode($charge);
        Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' charge' . json_encode($charge,JSON_UNESCAPED_UNICODE));
        $decode = json_decode($return,true);
        $detailMessage = $decode['detailMessage'];
        $productCode = '';
        $companyName = '';
        $time = '';
        foreach ($detailMessage as $value) {
            if ($value['status'] == 'SUCCEEDED') {
                $productCode = $value['productCode'];//险种缩写
                $companyName = $value['companyName'];//险种名称
                break;
            }else{
                if ($value['msg'] == '每日可用余量超量') {
                    $time = 'yes';
                    $companyName = $value['companyName'].'('.$value['msg'].')';
                    break;
                }
            }
        }
        if ($decode['status'] == "FAILED") {
            if ($time == 'yes') {
                $kind_status = 3;//投保未成功
                $toubao_status = '待投保';
            }else{
                $companyName = $decode['message'];
                $kind_status = 2;//投保状态  失败
                $toubao_status = '失败';
            }
        }elseif ($decode['status'] == "SUCCEEDED") {
            $kind_status = 1;//成功
            $toubao_status = '成功';
        }
        Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' productCode=' . $productCode);
        Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' companyName=' . $companyName);
        Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' kind_status=' . $kind_status);

        printf("状态：".$toubao_status);
        echo "<br>";
        printf("投保信息：".$companyName);

        $daikuan = $baoxianModel->where(array('ID' => $id))->save(array('KIND_ACCIDENT'=>$companyName,'POLICY_NUM'=>$decode['policyNo'],'KIND_STATUS'=>$kind_status));
        Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' daikuan=' . $daikuan);
        Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' baoxianModel=' . $baoxianModel->getlastsql());
	}

	public function channel_safe2(){
		$id = $_REQUEST['id'];
		if(empty($id)){
			$this->wrong('缺少参数');
		}
		$baoxianModel = M('baoxian',DB_PREFIX,DB_DAIKUAN);
		$data = $baoxianModel->where(array('ID'=>$id))->find();

		$url = 'http://www.ylxqgo.com/yx/ygrsData.aspx';

        if ($data['USER_SEX'] == '1') {
            $sex = '男';
        }elseif ($data['USER_SEX'] == '2') {
            $sex = '女';
        }

        if ($data['HOUSE_PROPERTY_CAR'] == '1') {
            $hasCar = '有';
            $hasHouse = '有';
        }elseif ($data['HOUSE_PROPERTY_CAR'] == '2') {
            $hasCar = '有';
            $hasHouse = '无';
        }elseif ($data['HOUSE_PROPERTY_CAR'] == '3') {
            $hasCar = '无';
            $hasHouse = '有';
        }elseif ($data['HOUSE_PROPERTY_CAR'] == '4') {
            $hasCar = '无';
            $hasHouse = '无';
        }

        if ($data['POLICY_ONLINE'] == '1') {
            $isCommercialInsurance = '有';
        }elseif ($data['POLICY_ONLINE'] == '2') {
            $isCommercialInsurance = '无';
        }

        if ($data['HOUSE_PROPERTY_CAR'] == '1') {
            $house_property_car = "有车有房";
        }elseif ($data['HOUSE_PROPERTY_CAR'] == '2') {
            $house_property_car = "有车无房";
        }elseif ($data['HOUSE_PROPERTY_CAR'] == '3') {
            $house_property_car = "无车有房";
        }elseif ($data['HOUSE_PROPERTY_CAR'] == '4') {
            $house_property_car = "无车无房";
        }

        if ($data['GUARANTEE'] == '1') {
            $guarantee = '意外保障';
        }elseif ($data['GUARANTEE'] == '2') {
            $guarantee = '重疾保障';
        }elseif ($data['GUARANTEE'] == '3') {
            $guarantee = '医疗保障';
        }

        if ($data['MONEY'] == '1') {
            $sumInsured = '10万';
        }elseif ($data['MONEY'] == '2') {
            $sumInsured = '20万';
        }elseif ($data['MONEY'] == '3') {
            $sumInsured = '30万';
        }else{
            $sumInsured = '30万';
        }

        $para = array();
        $para['access_token'] = 'ylxqbjzswebbest';//识别代码
        $para['name'] = $data['USER_NAME'];//姓名
        $para['mobile'] = $data['MOBILE'];//手机号
        $para['city'] = $data['CITY'];//城市（必填）
        $para['sex'] = $sex;//性别（必填）
        $para['birth'] = $data['USER_BIRTHDAY'];//生日（必填）
        $para['insuranceName'] = '阳光人寿出行无忧意外伤害保险';//保险名称
        $para['socialSecurity'] = '信息流';//是否有社保 有/无
        $para['hasCar'] = $hasCar;//是否有车
        $para['hasHouse'] = $hasHouse;//是否有房
        $para['income'] = '信息流';//收入/薪资 数字，如：100000 若为区间则传最大值
        $para['loanAmount'] = '信息流';//贷款额度
        $para['workIdentity'] = '信息流';//职业身份
        $para['propertyType'] = '信息流';//房产类型
        $para['isCommercialInsurance'] = $isCommercialInsurance;//是否有保单
        $para['localProvidentFund'] = '信息流';//是否有公积金
        $para['needLoan'] = '信息流';//是否有贷款需求
        $para['hasCreditCard'] = '信息流';//是否有信用卡
        $para['channelProperty'] = '信息流';//渠道属性   
        $para['carModel'] = '信息流';//汽车品牌及型号
        $para['channelsource'] = 'ylxqbjzswebbest';//渠道编码     
        $para['answer1'] = $house_property_car;//问卷1
        $para['answer2'] = $guarantee;//问卷2  
        $para['answer3'] = $sumInsured;//问卷3
        Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' para' . json_encode($para,JSON_UNESCAPED_UNICODE));
        // var_dump($para);exit;
        $charge = json_decode(doPost($url,$para),true);
        Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' charge' . json_encode($charge,JSON_UNESCAPED_UNICODE));
        // var_dump($charge);
        if ($charge['state'] == 'true') {
            $daikuan = $baoxianModel->where(array('ID' => $id))->save(array('KIND_ACCIDENT'=>$charge['companyNam'],'POLICY_NUM'=>'','KIND_STATUS'=>1));
            $toubao_status = '成功';
            $message = $charge['companyNam'];
            Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' daikuan=' . $daikuan);
            Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' baoxianModel=' . $baoxianModel->getlastsql());
            $this->assign('type','yangguang');
        }else{
            $daikuan = $baoxianModel->where(array('ID' => $id))->save(array('KIND_ACCIDENT'=>$charge['Msg'],'POLICY_NUM'=>'','KIND_STATUS'=>2));
            $toubao_status = '失败';
            $message = $charge['Msg'];
            Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' daikuan=' . $daikuan);
            Add_LOG('index', __FUNCTION__ . ' ' . __LINE__ . ' baoxianModel=' . $baoxianModel->getlastsql());
        }
        printf("状态：".$toubao_status);
        echo "<br>";
        printf("投保信息：".$message);
	}
}