<?php
namespace Home\Controller;
import('Vendor.Cookie.Cookie');
// +----------------------------------------------------------------------
// | @ljf  商户管理
// +----------------------------------------------------------------------
class BaoXianController extends HomeController {


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
            $baoxian = $baoxianModel->alias('a_baoxian')->where($where)->select();
            $p     = new \Think\Page($count, C('PAGE_COUNT'));
            //分页参数
            $this->assign ( 'totalCount',   $count );
            $this->assign ( 'numPerPage',   $p->listRows );
            $this->assign ( 'currentPage',  !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);

            $this->assign ( 'postdata', 	$post );
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
        $this->assign('baoxian',	$baoxian);  //数据库查的结果
        \Cookie::set ('_currentUrl_', 		__SELF__);
        $this->display();
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
                array('AMOUNT',         '借款金额'),
                array('SUPPLIER',       '供应商'),
                array('CHANNEL_MAP_ID', '渠道'),      
                array('STEP',           '步骤'),
                array('PROVINCE',       '省份'),
                array('CITY',           '市级'),
                array('MONTH_INCOME',   '月收入'),
                array('JOB_CATEGORY',   '职业'),
                array('HOUSE_PROPERTY_CAR', '资产情况'),
                array('POLICY',         '人寿保险单'),
                array('SOCIAL_SECURITY_FOUND',  '社保公积金'),
                array('PARTICLE_LOAN',  '微粒贷'),
                array('IP_ADDRESS',     'IP地址'),
                array('CREATE_TIME',    '创建时间'),
            );
            // if ($home['CHANNEL_MAP_ID'] == 0) {
                
            // }
            $xlsarray = array();
            foreach($list as $val){
                $xlsarray[] = array(
                    'USER_NAME'     =>  $val['USER_NAME'],
                    'USER_ID'       =>  $val['USER_ID'],
                    'USER_BIRTHDAY' =>  $val['USER_BIRTHDAY'],
                    'USER_SEX'      =>  C('USER_SEX')[$val['USER_SEX']],
                    'MOBILE'        =>  $val['MOBILE'],
                    'AMOUNT'        =>  C('MONEY_TYPE')[$val['AMOUNT']],
                    'SUPPLIER'      =>  $val['SUPPLIER'],
                    'CHANNEL_MAP_ID'=>  $val['CHANNEL_MAP_ID'],
                    'STEP'          =>  C('STEP_TYPE')[$val['STEP']],
                    'PROVINCE'      =>  $val['PROVINCE'],
                    'CITY'          =>  $val['CITY'],
                    'MONTH_INCOME'  =>  C('MONTH_INCOME')[$val['MONTH_INCOME']],
                    'JOB_CATEGORY'  =>  C('JOB_CATEGORY')[$val['JOB_CATEGORY']],
                    'HOUSE_PROPERTY_CAR'=>  C('HOUSE_CAR_TYPE')[$val['HOUSE_PROPERTY_CAR']],
                    'POLICY'        =>  C('POLICY_TYPE')[$val['POLICY']],
                    'SOCIAL_SECURITY_FOUND'=>   C('SOCIAL_FOUND_TYPE')[$val['SOCIAL_SECURITY_FOUND']],
                    'PARTICLE_LOAN' =>  C('LOAN_TYPE')[$val['PARTICLE_LOAN']],
                    'IP_ADDRESS'    =>  $val['IP_ADDRESS'],
                    'CREATE_TIME'   =>  $val['CREATE_TIME'],
                );
            }
            D($this->MExcel)->exportExcel($xlsname, $xlscell, $xlsarray);
            exit;
        }
        
        $this->display('Public/export');
    }
	
}