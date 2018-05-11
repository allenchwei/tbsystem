<?php
header("Content-type: text/html; charset=utf-8");

//获取加密
function getmd5Keyword() {
	$keyword = md5(md5(date('d').date('m').date('Y')));
	return $keyword;
}

//左侧补零函数
function setStrzero($str, $num, $var='0', $type='l') {
	$type = ($type == 'l') ? STR_PAD_LEFT : STR_PAD_RIGHT;
	return str_pad($str, $num, $var, $type);
}

//处理金额	setMoney('1.5', '6') = 1500000分【入库】		setMoney('1500000', '6', '2') = 1.5万【读取】
function setMoney($money, $length='2', $type='1') {
	if($money){
		$length = setStrzero('1', $length+1, '0', 'r');
		switch($type){
			case 1:	//乘
				$res = $money * $length;
				break;
			case 2:	//除
				$res = $money / $length;
				break;
		}
	}else{
		$res = '0';
	}	
	return sprintf("%.2f", $res);
}

//处理卡号
function setCard_no($card_no) {
	if($card_no == '-') {
		return $card_no;
	}
	if($card_no) {
		$len = strlen($card_no);
		$t1  = substr($card_no, 0, 6);
		$t2  = substr($card_no,-4, 4);
		$t3  = '';
		for($i=0; $i<$len-10; $i++){
			$t3 .= '*';
		}
		return $t1.$t3.$t2;
	}
}
	
//处理日志	1：登录 2添加 3修改 4删除5审核/复核 6手工 7退出
function setLog($type, $desc) {
	$home = session('HOME');
	if($home['USER_ID']) {
		$resdata = array(
			'LOG_DATE'	=>	date('Ymd'),
			'LOG_TIME'	=>	date('His'),
			'USER_ID'	=>	$home['USER_ID'],
			'USER_NAME'	=>	$home['USER_NAME'],
			'LOG_IP'	=>	get_client_ip(),
			'LOG_TYPE'	=>	$type,
			'LOG_DESC'	=>	$desc
		);
		D('MLog')->addLog($resdata);
	}
}

//处理发送短信
function setSmsmodel($type, $smsdata=array()) {
	$list = D('MSmsmodel')->getSmsmodellist("SMS_MODEL_TYPE = '".$type."' and SMS_MODLE_STATUS = 0");
	if(empty($list)){
		return array();
	}	
	$acct = array(); $bcct = array();	
	foreach($list as $val){
		if($val['SMS_MODEL_CITY'] != '000000'){
			$acct = $val;
		}else{
			$bcct = $val;
		}
	}
	$str = '';
	$finddata = !empty($acct) ? $acct : $bcct;
	switch($type){
		case '2':
			$str = $finddata['SMS_MODEL'];
			break;
		case '6':
			$str = str_replace('{dk_bankacct_no_tail}', $smsdata['dk_bankacct_no_tail'], $finddata['SMS_MODEL']);
			$str = str_replace('{datetime}', $smsdata['datetime'], $str);
			$str = str_replace('{dk_yl_amt}', $smsdata['dk_yl_amt'], $str);
			break;
	}
	return array('mid'=>$finddata['SMS_MODLE_ID'], 'str'=>$str);
}



//获取操作方法
function getaction_select($conname, $actname){	
	$home = session('HOME');
	$user_id = $home['USER_ID'];
	$role_id = $home['ROLE_ID'];
	$ret = '';
	
	//如果是超级管理员，查看所有
	if(C('SPECIAL_USER') == $user_id) {
		$pdata = D('MMenu')->getMenulist("MENU_LEVEL=2 and MENU_STATUS=1 and MENU_NAME='".$conname."'", 'MENU_ID,MENU_NAME,MENU_TITLE');
		if(!empty($pdata[0])) {
			$cdata = D('MMenu')->getMenulist("MENU_PID='".$pdata[0]['MENU_ID']."' and MENU_LEVEL=3 and MENU_STATUS=1 and MENU_NAME='".$actname."'", 'MENU_ID,MENU_NAME,MENU_TITLE');
			if(!empty($cdata[0])) {
				$list = D('MMenu')->getMenulist("MENU_PID='".$cdata[0]['MENU_ID']."' and MENU_LEVEL=3 and MENU_STATUS=1 and MENU_DISPLAY=0", 'MENU_DATA');
				if(!empty($list)){
					foreach($list as $val){
						$ret .= $val['MENU_DATA'];
					}
				}
			}
		}
	}else{
		$pdata = D('MMenu')->getMenulinelist("a.ROLE_ID='".$role_id."' and m.MENU_LEVEL=2 and m.MENU_STATUS=1 and m.MENU_NAME='".$conname."'", 'm.MENU_ID,m.MENU_NAME,m.MENU_TITLE');
		if(!empty($pdata[0])) {
			$cdata = D('MMenu')->getMenulinelist("a.ROLE_ID='".$role_id."' and m.MENU_PID='".$pdata[0]['MENU_ID']."' and m.MENU_LEVEL=3 and m.MENU_STATUS=1 and m.MENU_NAME='".$actname."'", 'm.MENU_ID,m.MENU_NAME,m.MENU_TITLE');
			if(!empty($cdata[0])) {
				$list = D('MMenu')->getMenulinelist("a.ROLE_ID='".$role_id."' and m.MENU_PID='".$cdata[0]['MENU_ID']."' and m.MENU_LEVEL=3 and m.MENU_STATUS=1 and m.MENU_DISPLAY=0", 'm.MENU_DATA');
				if(!empty($list)){
					foreach($list as $val){
						$ret .= $val['MENU_DATA'];
					}
				}
			}
		}
	}
	/* $pdata = D('MMenu')->findMenu("MENU_LEVEL=2 and MENU_STATUS=1 and MENU_NAME='".$conname."'", 'MENU_ID');
	if(!empty($pdata)) {
		$cdata = D('MMenu')->findMenu("MENU_PID='".$pdata['MENU_ID']."' and MENU_LEVEL=3 and MENU_STATUS=1 and MENU_NAME='".$actname."'", 'MENU_ID');
		if(!empty($cdata)) {
			$list = D('MMenu')->getMenulist("MENU_PID='".$cdata['MENU_ID']."' and MENU_LEVEL=3 and MENU_STATUS=1 and MENU_DISPLAY=0", 'MENU_DATA');
			if(!empty($list)){
				foreach($list as $val){
					$ret .= $val['MENU_DATA'];
				}
			}
		}
	} */
	return $ret;
}


//获取地区列表	供模板调用
function getcity_select($code = '', $area='AREA_CODE', $city='CITY_CODE', $province='PROVINCE_CODE'){
	$MCity = 'MCity';
	if($code) {
		$res = D($MCity)->getCity_morelist($area, $city, $province, $code);
	}else{
		$res = D($MCity)->getCity_plist($area, $city, $province);
	}
	return $res;
}

//获取地区全称
function getcity_name($code, $type=''){
	$MCity = 'MCity'; $str = '';	
	if($code) {
		$info = D($MCity)->findCity("CITY_S_CODE='".$code."'");
		if(!empty($info)){
			switch($type){
				case '1':
					$str = $info['PROVINCE_NAME'];
					break;
				case '2':
					$str = $info['CITY_NAME'];
					break;
				case '3':
					$str = $info['CITY_S_NAME'];
					break;
				default:
					$str = $info['PROVINCE_NAME'].$info['CITY_NAME'].$info['CITY_S_NAME'];
			}
		}
	}
	return $str;
}
//上福行业类目
function getcate_select($THR_NAME='',$CATEID=0){
	$str = '';
	$cateModel = M('cate_sf');
	if($THR_NAME){
		$cateInfo = $cateModel->where(array('CATEID'=>$CATEID))->find();
		$firArr = $cateModel->group('FIR_NAME')->order('id asc')->select();
		$str .= '<select name="fir_name" level="1">';
		$fir_name = '';
		foreach($firArr as $val){
			if($cateInfo['FIR_NAME']==$val['FIR_NAME']){
				$fir_name = $val['FIR_NAME'];
				$str .= '<option value="'.$val['FIR_NAME'].'" selected >'.$val['FIR_NAME'].'</option>';
			}else{
				$str .= '<option value="'.$val['FIR_NAME'].'">'.$val['FIR_NAME'].'</option>';
			}
		}
		$str .= '</select>';
		//二级类目
		$secArr = $cateModel->where(array('FIR_NAME'=>$fir_name))->group('SEC_NAME')->order('id asc')->select();
		$str .= '<select name="sec_name" level="2">';
		$sec_name = '';
		foreach($secArr as $val){
			if($cateInfo['SEC_NAME']==$val['SEC_NAME']){
				$sec_name = $val['SEC_NAME'];
				$str .= '<option value="'.$val['SEC_NAME'].'" selected >'.$val['SEC_NAME'].'</option>';
			}else{
				$str .= '<option value="'.$val['SEC_NAME'].'">'.$val['SEC_NAME'].'</option>';
			}
		}
		$str .= '</select>';
		//三级类目
		$thrArr = $cateModel->where(array('FIR_NAME'=>$fir_name,'SEC_NAME'=>$sec_name))->group('THR_NAME')->order('id asc')->select();
		$str .= '<select name="thr_name" level="3">';
		foreach($thrArr as $val){
			if($CATEID==$val['CATEID']){
				$str .= '<option value="'.$val['THR_NAME'].'" selected >'.$val['THR_NAME'].'</option>';
			}else{
				$str .= '<option value="'.$val['THR_NAME'].'">'.$val['THR_NAME'].'</option>';
			}
		}
		$str .= '</select>';
	}else{
		$firArr = $cateModel->group('FIR_NAME')->order('id asc')->select();
		$str .= '<select name="fir_name" level="1">';
		$fir_name = '';
		foreach($firArr as $val){
			if(empty($fir_name)){
				$fir_name = $val['FIR_NAME'];
			}
			$str .= '<option value="'.$val['FIR_NAME'].'">'.$val['FIR_NAME'].'</option>';
		}
		$str .= '</select>';
		//二级类目
		$secArr = $cateModel->where(array('FIR_NAME'=>$fir_name))->group('SEC_NAME')->order('id asc')->select();
		$str .= '<select name="sec_name" level="2">';
		$sec_name = '';
		foreach($secArr as $val){
			if(empty($sec_name)){
				$sec_name = $val['SEC_NAME'];
			}
			$str .= '<option value="'.$val['SEC_NAME'].'">'.$val['SEC_NAME'].'</option>';
		}
		$str .= '</select>';
		//三级类目
		$thrArr = $cateModel->where(array('FIR_NAME'=>$fir_name,'SEC_NAME'=>$sec_name))->order('id asc')->select();
		$str .= '<select name="thr_name" level="3">';
		foreach($thrArr as $val){
			$str .= '<option value="'.$val['THR_NAME'].'">'.$val['THR_NAME'].'</option>';
		}
		$str .= '</select>';
	}
	return $str;
}
function getkind_select($kind=''){
	$kindArr = array(
		'1' => '医疗',
		'2' => '餐饮',
		'3' => '生活服务',
		'4' => '购物',
		'5' => '旅游出行',
		'6' => '休闲娱乐',
		'7' => '汽车服务',
		'8' => '其他',
	);
	foreach($kindArr as $key=>$val){
		if($kind==$key){
			$mt .= '<option value="'.$key.'" selected >'.$val.'</option>';
		}else{
			$mt .= '<option value="'.$key.'">'.$val.'</option>';
		}
	}
	$res = '<select class="combox" name="shop[SHOP_KIND]">
					  '.$mt.'
				</select>';
	return $res;
}
function getkind_name($kind=''){
	$kindArr = array(
		'1' => '医疗',
		'2' => '餐饮',
		'3' => '生活服务',
		'4' => '购物',
		'5' => '旅游出行',
		'6' => '休闲娱乐',
		'7' => '汽车服务',
		'8' => '其他',
	);
	return $kindArr[$kind];
}
//获得MCC下拉联动
function getmcc_select($type='',$code='',$mcc_type='MCC_TYPE', $mcc_code='MCC_CODE'){
	return D('MMcctype')->getMcctypesel($type,$code,$mcc_type, $mcc_code);
}
function getmcc_select_2($type='',$code='',$mcc_type='MCC_TYPE', $mcc_code='MCC_CODE'){
	return D('MMcctype')->getMcctypesel_2($type,$code,$mcc_type, $mcc_code);
}
//获得MCC_CODE名称
function getmcc_name($code){
	$data = D('MMcc')->findMcc("MCC_CODE='".$code."'", 'MCC_NAME,MCC_TYPE');
	return C('MCC_TYPE')[$data['MCC_TYPE']].' - '.$data['MCC_NAME'];
}

//获得设备型号联动
function getmodel_select($code='',$model_id='MODEL_ID', $factory_id='FACTORY_MAP_ID'){
	return D('MModel')->getModelsel($code,$model_id, $factory_id);
}

//获得设备型号, 厂商名称
function getmodel_name($code){
	$data = D('MModel')->findModel("MODEL_MAP_ID='".$code."'", 'MODEL_NAME, FACTORY_NAME');
	return $data['FACTORY_NAME'].' '.$data['MODEL_NAME'];
}

//根据账户号，获取账户名称	商户 20201	会员 20101	分公司 20204	合作伙伴 20203	超扣 60102	收单分润 60101	通道 10202
//$flag=0 分账户	$flag=0 总账户
function getacct_name($acct_no, $type){
	$name = '-';
	if($type == '20101'){
		$resdata = D('GLap')->findLap("ACCT_NO='".$acct_no."'", 'ACCT_NAME');
		$name 	 = $resdata['ACCT_NAME'];
	}else{
		$resdata = D('GLae')->findLae("ACCT_NO='".$acct_no."'", 'ACCT_NAME');
		$name 	 = $resdata['ACCT_NAME'];
	}
	return $name;
}

//获取会员名称
function getvip_name($vip_id){
	$name = '-';
	if($vip_id){
		$resdata = D('GVip')->findVip("VIP_ID='".$vip_id."'", 'VIP_NAME');
		$name 	 = $resdata['VIP_NAME'];
	}
	return $name;
}
//获取会员手机
function getvip_mobile($vip_id){
	$name = '-';
	if($vip_id){
		$resdata = D('GVip')->findVip("VIP_ID='".$vip_id."'", 'VIP_MOBILE');
		if(!empty($resdata)){
			$name 	 = $resdata['VIP_MOBILE'];
		}
	}
	return $name;
}

//获取会员名称
function getcard_vipname($card_no){
	$name = '-';
	if($card_no){
		$resdata = D('GVip')->findVip("CARD_NO='".$card_no."'", 'VIP_NAME');
		if(!empty($resdata)){
			$name 	 = $resdata['VIP_NAME'];
		}
	}
	return $name;
}


//起始和终止时间戳
function getmonthtime($type){
	switch($type){
		//月
		case 1:
			$firsttime = mktime(0, 0 , 0,date("m"),1,date("Y"));
			$lasttime = mktime(23,59,59,date("m"),date("t"),date("Y"));		
			break;
		//季
		case 2:
			$season = ceil((date('n'))/3);//当月是第几季度
			$firsttime = mktime(0, 0, 0,$season*3-3+1,1,date('Y'));
			$lasttime = mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y'));		
			break;
		//年
		case 3:
			$firsttime = mktime(0,0,0,1,1,date('Y',time()));
			$lasttime = mktime(23,59,59,12,31,date('Y',time()));	
			break;	
		//上月
		case 4:
			$firsttime = mktime(0,0,0,date("m")-1,1,date("Y"));
			$lasttime = mktime(23,59,59,date("m") ,0,date("Y"));	
			break;		
		//天
		default:
			$firsttime = mktime(0,0,0,date('m',time()),date('d',time()),date('Y',time()));
			$lasttime = mktime(23,59,59,date('m',time()),date('d',time()),date('Y',time()));
	}
	return array($firsttime,$lasttime);
}

//记录日志
function Add_LOG($name, $content){
	//检查目录
	$name = $name.'_'.substr(date('Y'), 2).'_'.date('m').'_'.date('d').'.log';
	$path = './Public/file/apilog/';
	mkdir($path, 0700);
	if(empty($content)){
		file_put_contents($path.$name, PHP_EOL, FILE_APPEND);
		$first = '[ '.date('Y-m-d H:i:s').' ]  '.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REMOTE_ADDR'].'  http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '');
		file_put_contents($path.$name, $first.PHP_EOL, FILE_APPEND);
	}else{
		file_put_contents($path.$name, date('Y-m-d H:i:s').'  '.$content.PHP_EOL, FILE_APPEND);		
	}
}

//去掉非法字符
function trimstr($string){
    $qian = array(" ","　","\t","\n","\r");
	$hou = array("","","","","");
    return str_replace($qian,$hou,$string);
}

//处理图片
function get_climg($str, $type='') {
	$arr = explode("/", $str);
	$oldname = $arr[count($arr)-1];
	
	$arr1 = explode("_", $oldname);
	$imgname = count($arr1)==1 ? $arr1[0] : $arr1[1];
	
	switch($type){
		case 'm':	//400
			$newname = 'm_'.$imgname;
			break;
		case 's':	//100
			$newname = 's_'.$imgname;
			break;
		default:	//原图
			$newname = $imgname;
	}
	$img = str_replace($oldname, $newname, $str);
	return $img;
	
	/* if(file_exists('.'.$img)){
		return $img;
	}else{
		return $str;
	} */
}

//检测输入的验证码是否正确，$code为用户输入的验证码字符串
function check_verify($code, $id = ''){
    $verify = new \Think\Verify();
    if($verify->check($code, $id)){
		return 0;
	}else{
		return 1;
	}
}

/**
* 归属下拉菜单
* $bid 		分公司ID(BRANCH_MAP_ID)
* $flag 	下拉级别标志(用于输出几个select)
* $lvname 	select名字(数组)
* $pid 		合作伙伴ID(PARTNER_MAP_ID)
* $maxlv 	ajax时的最大级别输出显示
* $ajax_url	
*/
function get_level_sel($bid, $flag, $lvname, $pid, $maxlv='5', $is_root='0', $ajax_url='/index.php/Home/Public/ajaxgetsellv'){
	$selhtml = D('MPartner')->getlevelsel($bid, $flag, $lvname, $pid, $maxlv, $is_root);
$time = getmicrotime();
$unique_name = substr(md5($lvname),8,8); // 8位MD5加密 
$selhtml.="
	<script type=\"text/javascript\">
	function sellv_".$time."(thisobj){
		var pid 	 = thisobj.val(),
			flag	 = parseInt(thisobj.attr('flag'))+parseInt(1),
			maxlv	 = thisobj.attr('maxlv'),
			levelsel = parseInt(thisobj.attr('levelsel'))+parseInt(1),
			name  	 = '".$lvname."',
			ajax_url = '".$ajax_url."',
			unique_name = '".$unique_name."',
			param = thisobj.closest('form');
			var bid = param.find('select[name=\"".$lvname."\"]:first').val();
			";

$selhtml.=<<<selscript
			if (flag > maxlv) { return;};
			$.ajax({
			   type: "POST",
			   url : ajax_url,
			   data: "bid="+ bid +"&pid="+ pid +"&flag="+ flag,
			   success: function(data){
			   		var sel_str = '';
			   		if(data){
						sel_str = '<select flag="'+ flag +'" levelsel="'+ levelsel +'" class="unique_name" name="'+ name +'" maxlv="'+ maxlv +'">';
						sel_str += '<option value="">请选择</option>';
			   			$.each(data, function(i){
							if (data[i] && data[i].length > 1){
								sel_str += '<option value="'+data[i][0]+'">' + data[i][1] + '</option>';
							}
						});
						sel_str +='</select>';
			   		};
			   		//删除当前级别后所有级别, 添加新的级别菜单
		   			var param = thisobj.closest('div.combox');
		   			param.nextAll('div.combox').remove();
		   			param.after(sel_str);
		   			param.next('select.unique_name').trigger("change").combox();
					param.next().find('select.unique_name').bind("change", function(){
selscript;
$selhtml.="					sellv_".$time."($(this));
					});
			   }
			})
	}
	$('select.".$unique_name."').on('change',function(){
		sellv_".$time."($(this));
	})
	</script>";
	return $selhtml;
}
//获取归属数据
function get_level_data($pid,$bid,$flag=0,$name_arr){
	/*if ($pid==='') {
		return '';
	}*/
	if (empty($pid)) {
		$b_data = D('MBranch')->findBranch('BRANCH_MAP_ID = '.$bid,'BRANCH_MAP_ID,BRANCH_NAME,BRANCH_LEVEL,BRANCH_MAP_ID_P');
		$name_arr[] = array('name' => $b_data['BRANCH_NAME'], 'id' => $b_data['BRANCH_MAP_ID'], 'level' => $b_data['BRANCH_LEVEL'],'ppid' => $b_data['BRANCH_MAP_ID_P'] );
		return $name_arr;
	}
	$where = 'a.PARTNER_MAP_ID = '.$pid;
	$p_data = D('MPartner')->findPartner($where,'a.BRANCH_MAP_ID,a.PARTNER_MAP_ID,a.PARTNER_NAME,a.PARTNER_LEVEL,a.PARTNER_MAP_ID_P');
	if ($p_data['PARTNER_MAP_ID_P'] == 0) {
		$name_arr[] = array('name' => $p_data['PARTNER_NAME'], 'id' => $p_data['PARTNER_MAP_ID'], 'level' => $p_data['PARTNER_LEVEL'],'ppid' => $p_data['PARTNER_MAP_ID_P']);
		$b_data = D('MBranch')->findBranch('BRANCH_MAP_ID = '.$p_data['BRANCH_MAP_ID'],'BRANCH_MAP_ID,BRANCH_NAME,BRANCH_LEVEL,BRANCH_MAP_ID_P');
		$name_arr[] = array('name' => $b_data['BRANCH_NAME'], 'id' => $b_data['BRANCH_MAP_ID'], 'level' => $b_data['BRANCH_LEVEL'],'ppid' => $b_data['BRANCH_MAP_ID_P'] );
		//去掉当前级别
		if ($flag) {
			unset($name_arr[0]);
		}
		//unset($name_arr[0]);
		krsort($name_arr);
		$name_arr = array_values($name_arr);
		return $name_arr;
	}else{
		$name_arr[] =  array('name' => $p_data['PARTNER_NAME'], 'id' => $p_data['PARTNER_MAP_ID'], 'level' => $p_data['PARTNER_LEVEL'],'ppid' => $p_data['PARTNER_MAP_ID_P']);
		return get_level_data($p_data['PARTNER_MAP_ID_P'],$p_data['BRANCH_MAP_ID'],$flag,$name_arr);
	}
}

//获取归属下所有子集
function get_plv_childs($pid,$flag=0,$lv='',$pids=''){
	if ($lv >= 3) {
		return trim($pids,',');
	}
	if (empty($lv) && empty($pids)) {
		$p_lv = D('MPartner')->findPartner('a.PARTNER_MAP_ID ='.$pid,'a.PARTNER_LEVEL');
		$lv = $p_lv['PARTNER_LEVEL'];
		//是否包含本身级别id
		if ($flag) {
			$pids = $pid;
		}
	}
	$p_data = D('MPartner')->getPartnerlist('a.PARTNER_MAP_ID_P in('.$pid.') and a.PARTNER_LEVEL >'.$lv,'a.PARTNER_MAP_ID');
	$pid_arr = i_array_column($p_data,'PARTNER_MAP_ID');
	$pid = implode(',', $pid_arr);
	$pids .= ','.implode(',', $pid_arr);
	if ($p_data) {
		return get_plv_childs($pid,$flag,$lv+1,$pids);
	}
	return trim($pids,',');
}

//根据归属名称 partname，获取当前子集
function get_partname_childs($partname){
	$pids = '';
	$list = D('MPartner')->getPartnerlist('a.PARTNER_NAME like "%'.$partname.'%"','a.PARTNER_MAP_ID');
	if(empty($list)){
		return $pids;
	}
	$pid_arr = i_array_column($list,'PARTNER_MAP_ID');
	$pids = implode(',', $pid_arr);
	return trim($pids,',');
}

//根据branch_map_id，获取当前子集
function get_partbran_childs($branch_map_id){
	$pids = '';
	$list = D('MPartner')->getPartnerlist('a.BRANCH_MAP_ID = "'.$branch_map_id.'"','a.PARTNER_MAP_ID');
	if(empty($list)){
		return $pids;
	}
	$pid_arr = i_array_column($list,'PARTNER_MAP_ID');
	$pids = implode(',', $pid_arr);
	return trim($pids,',');
}

//根据公司名称，获取当前子集
function get_branchname_childs($branchname){
	$pids = '';
	$list = D('MBranch')->getBranchlist('BRANCH_NAME like "%'.$branchname.'%"','BRANCH_MAP_ID');
	if(empty($list)){
		return $pids;
	}
	$pid_arr = i_array_column($list,'BRANCH_MAP_ID');
	$pids = implode(',', $pid_arr);
	return trim($pids,',');
}

//根据合作方pid,获取归属select
function get_level_select($bid,$flag,$pid,$lvname){
	return D('MPartner')->getlevelsel($bid,$flag,$lvname,$pid);
	if (empty($pid)) {
		return D('MPartner')->getB1sel($where,$lvname,$selid);
	}
	$res = get_level_data($pid);
	krsort($res);
	$res = array_values($res);
	$p_strsel = '';
	for ($i=0; $i < count($res); $i++) {
		switch ($i) {
			case '0':
				$p_strsel .= D('MPartner')->getB1sel($where,$lvname,$res[$i]['id']);
				break;
			case '1':
				$p_strsel .= D('MPartner')->getP1sel($where,$lvname,$res[$i]['id']);
				break;
			case '2':
				$p_strsel .= D('MPartner')->getP2sel($where,$lvname,$res[$i]['id']);
				break;
			case '3':
				$p_strsel .= D('MPartner')->getP3sel($where,$lvname,$res[$i]['id']);
				break;
			default:
				return '';
				break;
		}
	}
	return $p_strsel;
}
//获取归属集团总公司
/*function get_company_sel($lvname,$pid,$maxlv){
	$p_strsel = D('MPartner')->getB1sel($where,$lvname,$pid,$maxlv);
	return $p_strsel;
}*/

function get_company_sel($pid,$post_name){
	//$where = "PARTNER_G_FLAG = 2 and PARTNER_G_FLAG = '".$pid.'"';
	$p_strsel = D('MPartner')->getCompanypsel($pid,$post_name);
	return $p_strsel;
}

//获取归属集团商户名称
function get_shopp_name($shopid){
	$where = "SHOP_LEVEL = 2 and SHOP_MAP_ID = ".$shopid; 
	$data = D('MShop')->findShop($where, 'SHOP_NAME');
	if (!$data) {
		return '暂无';
	}
	return $data['SHOP_NAME'];
}

//获取归属集团商户
function get_shopp_level($shopid,$post_name){
	$selhtml = D('MShop')->getShoppsel($shopid,$post_name);
	return $selhtml;
}

//获取归属名称
function get_level_name($pid,$bid,$flag=0){
	if (empty($pid)) {
		$b_data = D('MBranch')->findBranch('BRANCH_MAP_ID = '.$bid,'BRANCH_NAME');
		return $b_data['BRANCH_NAME'];
	}
	$res = get_level_data($pid,$bid,$flag);
	for ($i=0; $i < count($res); $i++) {
		$p_namestr .= $res[$i]['name'].' - ';
	}
	return trim($p_namestr,' - ');
}
//获取归属下拉列表值
function get_level_val($selname,$selnum='0'){
	$lv = I($selname);
	if (empty($lv)) {
		return array('bid' => '', 'pid' => '' );
	}
	//获取自定义级别值
	if ($selnum !='' && $selnum > 0) {
		return array('bid' => $lv[0], 'pid' => $lv[$selnum]);
	}
	//合作伙伴id
	if ($lv[4]) {
		$pid = $lv[4];
	}elseif ($lv[3]) {
		$pid = $lv[3];
	}elseif ($lv[2]) {
		$pid = $lv[2];
	}elseif ($lv[1]) {
		$pid = $lv[1];
	}
	$selarr = array('bid' => $lv[0], 'pid' => $pid );
	return $selarr;
}

/*
 * 获取审核记录
 * $check_no 
 * 		1 + HOST_MAP_ID
 *		2 + PARTNER_MAP_ID
 *		3 + SHOP_MAP_ID
 * return array()
*/
//获取审核所有信息
function get_check_note($check_no, $state=''){
	if ($state == '') {
		$where = 'CHECK_NO = "'.$check_no.'" and CHECK_POINT != "0"';
	}else{
		$where = 'CHECK_NO = "'.$check_no.'" and CHECK_POINT = "'.$state.'"';
	}
	$check_info = D('MCheck')->findCheck($where);
	if ($check_info) {
		return $check_info;
	}else{
		return array('CHECK_DESC','暂无审核记录');
	}
}

//获取单条审核信息
function getCheck_desc($host_map_id, $flag, $state=''){
	$CHECK_NO = $flag.setStrzero($host_map_id, 15);
	$where = "CHECK_NO='".$CHECK_NO."'";
	if($state){
		$where .= " and CHECK_POINT='".$state."'";
	}
	$checkdata = D('MCheck')->findCheck($where, 'CHECK_DESC');
	return $checkdata['CHECK_DESC'] ? $checkdata['CHECK_DESC'] : '无'; 
}

/*
 * 开户行联动
 * $bact_info		银行账户数据
 * $bankbname 		支行名称(bact[BANK_NAME])
 * $bidname 		联行号名称(bact[BANKACCT_BID)
  * $bact_type 		帐户类型(1对公,2对私)
 * return str
*/
function get_bank_sel($bact_info='',$bankbname='bact[BANK_NAME]',$bidname='bact[BANKACCT_BID]', $bact_type=''){
	//$time = getmicrotime();
	$unique_name = substr(md5($bankbname),8,8); // 8位MD5加密 
	$area_name='bact[CITY_NO_'.$unique_name.']';
	$bankname='bact[ISSUE_CODE_'.$unique_name.']';
	//获取银行联行号数据
	if($bact_info){
		switch ($bact_type) {
			case '1':
				$biddata = D('MBid')->findBid('BANK_BID = '.$bact_info['BANKACCT_BID1'],'ISSUE_CODE,CITY_S_CODE,BANK_BNAME');
				$bidsel = D('MBid')->getBidlist('ISSUE_CODE = '.$biddata['ISSUE_CODE'].' and CITY_S_CODE = '.$biddata['CITY_S_CODE'],'BANK_BID,BANK_BNAME');
				break;
			case '2':
				$biddata = D('MBid')->findBid('BANK_BID = '.$bact_info['BANKACCT_BID2'],'ISSUE_CODE,CITY_S_CODE,BANK_BNAME');
				$bidsel = D('MBid')->getBidlist('ISSUE_CODE = '.$biddata['ISSUE_CODE'].' and CITY_S_CODE = '.$biddata['CITY_S_CODE'],'BANK_BID,BANK_BNAME');
				break;
			default :
				$biddata = D('MBid')->findBid('BANK_BID = '.$bact_info['BANKACCT_BID'],'ISSUE_CODE,CITY_S_CODE,BANK_BNAME');
				$bidsel = D('MBid')->getBidlist('ISSUE_CODE = '.$biddata['ISSUE_CODE'].' and CITY_S_CODE = '.$biddata['CITY_S_CODE'],'BANK_BID,BANK_BNAME');
				break;
		}
	}
	//银行数据
	$banklist = D('MBank')->getBanklist('','ISSUE_CODE,BANK_NAME');
	//银行下拉列表
	$bank_sel_select = '
	<p class="maxcombox">
		<label>开户行：</label>
		<select class="combox" name="'.$bankname.'" onchange="getbid_'.$unique_name.'()">';
			foreach ($banklist as $key => $value) {
				if ($biddata['ISSUE_CODE'] == $value['ISSUE_CODE'] && $biddata['ISSUE_CODE'] != '') {
					 $bank_sel_select .= '<option value="'.$value['ISSUE_CODE'].'" selected>'.$value['BANK_NAME'].'</option>';
				}else{
					$bank_sel_select .= '<option value="'.$value['ISSUE_CODE'].'">'.$value['BANK_NAME'].'</option>';
				}
			}
		$bank_sel_select .= '</select>';
		//地区下拉列表
		$bank_sel_select .= getcity_select($biddata['CITY_S_CODE'],$area_name);
		//分行下接列表
		$bank_sel_select .= '
		<select class="combox" name="bact[BANK_NAME_SEL_'.$unique_name.']" node-type="getbidno_'.$unique_name.'">';
		foreach ($bidsel as $key => $val) {
			if ($bact_info['BANKACCT_BID'.$bact_type] == $val['BANK_BID']) {
				$bank_sel_select .= '<option value="'.$val['BANK_BID'].'" selected>'.$val['BANK_BNAME'].'</option>';
			}else{
				$bank_sel_select .= '<option value="'.$val['BANK_BID'].'">'.$val['BANK_BNAME'].'</option>';
			}
		}
	$bank_sel_select .= '</select>';
	//$bank_sel_select .= '<input type="hidden" name="'.$bankbname.'" value="'.$bact_info['BANK_NAME'.$bact_type].'">';
	$bank_sel_select .= '<input type="text" class="ws23" name="'.$bankbname.'" value="'.($biddata['BANK_BNAME'] ? $biddata['BANK_BNAME'] : $bact_info['BANK_NAME'.$bact_type]).'"><span class="error" for="'.$bankbname.'" style="left: 761px; display: none;">必填字段</span>';
	$bank_sel_select .= '</p>';
	$bank_sel_select .= '<p>';
	$bank_sel_select .= '<label>开户行联行号：</label>';
	$bank_sel_select .= '<input type="text" class="required digits" name="'.$bidname.'" value="'.$bact_info['BANKACCT_BID'.$bact_type].'">';
	$bank_sel_select .= '</p>';

	//js代码
	$bank_sel_select .= "
	<script>
		/*根据银行和地区获取开户行*/
		$('select[name=\"".$area_name."\"]').on('change', function() {
			getbid_".$unique_name."();
		});";
	$bank_sel_select .= "
		function getbid_".$unique_name."(){
			var city_code = '', issue_code = '';
				city_code 	= $('select[name=\"".$area_name."\"]').val();
				issue_code 	= $('select[name=\"".$bankname."\"]').val();
				if (city_code == '' || issue_code == '') {
					return false;
				}
			$.ajax({
			   type: 'POST',
			   url:  '/index.php/Home/Public/getbid',
			   data: 'city_code='+ city_code +'&issue_code=' + issue_code,
			   success: function(data){
			   		if(!data) return;
			   		if(data.state == 0){
			   			var banksel = '';
			   			for (var i = 0;i < data.data.length; i++) {
			   				banksel += '<option value=\"'+data.data[i].BANK_BID+'\">'+ data.data[i].BANK_BNAME +'</option>';
			   			}
						var ref = $('select[name=\"bact[BANK_NAME_SEL_".$unique_name."]\"]');
						var refCombox = ref.parents('div.combox:first');
						ref.html(banksel).insertAfter(refCombox);
						refCombox.remove();
			   			$('select[name=\"bact[BANK_NAME_SEL_".$unique_name."]\"]').trigger('change').combox();
			   		}
			   }
			});
		}
		/*获取联行号*/
		$('select[node-type=\"getbidno_".$unique_name."\"]').on('change',function(){
			var bid_val	  = $(this).val(),
				bid_name  = $(this).find('option:selected').text();
				$('input[name=\"".$bidname."\"]').val(bid_val);
				$('input[name=\"".$bankbname."\"]').val(bid_name);
		});
	</script>";
	return $bank_sel_select;
}
//获取代扣公司列表
function get_sdkb_sel($selname,$dkid,$flag=0){
	$list = D('MDkco')->getDkcolist('DKCO_STATUS = 0', 'DKCO_MAP_ID,DKCO_NAME');
	$sdkb_sel  = '<select class="combox" name="'.$selname.'">';
	$sdkb_sel .= $flag == 1 ? '<option value="">请选择</option>' : '';
		foreach ($list as $key => $val) {
			if ($dkid == $val['DKCO_MAP_ID']) {
				$sdkb_sel .= '<option value="'.$val['DKCO_MAP_ID'].'" selected>'.$val['DKCO_NAME'].'</option>';
			}else{
				$sdkb_sel .= '<option value="'.$val['DKCO_MAP_ID'].'">'.$val['DKCO_NAME'].'</option>';
			}
		}
	$sdkb_sel .= '</select>';
	return $sdkb_sel;
}
//获取代扣公司名称
function get_dkco_name($dkid){
	$where = "DKCO_MAP_ID = ".$dkid; 
	$data = D('MDkco')->findDkco($where, 'DKCO_NAME');
	if (!$data) {
		return '暂无';
	}
	return $data['DKCO_NAME'];
}
//获取保险公司名称
function get_security_name($sid){
	$data = D('MSecurity')->findSecurity('SECURITY_MAP_ID = '.$sid,'SECURITY_NAME');
	if (!$data) {
		return '暂无';
	}
	return $data['SECURITY_NAME'];
}

//获取商户名称
function get_shop_name($shopid){
	$data = D('MShop')->findShop("SHOP_MAP_ID = '".$shopid."'", 'SHOP_NAME');
	return $data['SHOP_NAME'];
}
//根据获取商户号获取商户基本信息
function get_shop_data($shop_no){
	$shopdata = D('MShop')->findShop('SHOP_NO = "'.$shop_no.'"','SHOP_NAME,SHOP_NAMEABCN');
	return $shopdata;
}

//获取通道名称
function get_host_name($hostid){
	$data = D('MHost')->findHost("HOST_MAP_ID = '".$hostid."'", 'HOST_NAME');
	return $data['HOST_NAME'];
}

//获取渠道名称
function get_channel_name($channelid){
	if ($channelid ==0) {
		return '自主';
	}
	$data = D('MChannel')->findChannel("CHANNEL_MAP_ID = '".$channelid."'", 'CHANNEL_NAME');
	return $data['CHANNEL_NAME'];
}

//代理商名称
function get_agent_name($agentid){
	$data = D('MAgent')->findAgent("AGENT_MAP_ID = '".$agentid."'", 'a.AGENT_NAME');
	return $data['AGENT_NAME'];
}

//获取通道映射规则
function getHost_pppflag($host_map_id){
	$host_ppp_flag = C('HOST_PPP_FLAG');
	$hauth_data = D('MHauth')->findHauth("HOST_MAP_ID = '".$host_map_id."'", 'HOST_PPP_FLAG');
	return $host_ppp_flag[$hauth_data['HOST_PPP_FLAG']] ? $host_ppp_flag[$hauth_data['HOST_PPP_FLAG']] : ''; 
}
//生成并保存唯一商户编号
function create_shopno($shopid){
	$sdata = D('MShop')->findshop("SHOP_MAP_ID = '".$shopid."'", 'CITY_NO,MCC_CODE,SHOP_MAP_ID');
	if ($sdata) {
		if (empty($sdata['CITY_NO'])) {
			return false;
		}
		if (empty($sdata['MCC_CODE'])) {
			return false;
		}
		$shop_no = C('SHOP_NO_FT');
		$shop_no .= substr($sdata['CITY_NO'],0,4);
		$shop_no .= $sdata['MCC_CODE'];
		//$shop_no .= setStrzero($sdata['SHOP_MAP_ID'], 4);
		//查寻当前序号最大值
		$where = "SHOP_NO >= '".$shop_no.'0000'."' and SHOP_NO <='".$shop_no.'9999'."'";
		$data = M('shop')->where($where)->max('SHOP_NO');
		if ($data >= $shop_no.'9999') {
			return false;
		}
		if (empty($data)) {
			return $shop_no.'0000';
		}else{
			return $data+1;
		}
	}else{
		return false;
	}
}
//获取分公司名称
function get_branch_name($bid,$pid = 0){
	if (!empty($pid)) {
		return get_partner_name($pid);
	}
	$b_data = D('MBranch')->findBranch('BRANCH_MAP_ID = '.$bid,'BRANCH_NAME');
	return $b_data['BRANCH_NAME'];
}
//获取合作方名称
function get_partner_name($pid){
	$p_data = D('MPartner')->findPartner('PARTNER_MAP_ID = '.$pid,'PARTNER_NAME');
	return $p_data['PARTNER_NAME'];
}
//数组转对象
function arrayToObject($e){
    if( gettype($e)!='array' ) return;
    foreach($e as $k=>$v){
        if( gettype($v)=='array' || getType($v)=='object' )
            $e[$k]=(object)arrayToObject($v);
    }
    return (object)$e;
}
//对象转数组
function objectToArray($e){
    $e=(array)$e;
    foreach($e as $k=>$v){
        if( gettype($v)=='resource' ) return;
        if( gettype($v)=='object' || gettype($v)=='array' )
            $e[$k]=(array)objectToArray($v);
    }
    return $e;
}
//获得权限字串(将权限,交易等json字串转为正常的权限字串用于入库)
function get_authstr($jsonstr){
	$obj = json_decode(htmlspecialchars_decode($jsonstr));
	$arr = objectToArray($obj);
	$old_auth = setStrzero('',128,'1');
	foreach ($arr as $key => $val) {
		if ($val['is_check']) {
			$old_auth = substr_replace($old_auth,'1',$val['trans_id']-1,1);
		}else{
			$old_auth = substr_replace($old_auth,'0',$val['trans_id']-1,1);
		}
	}
	return $old_auth;
}

//处理交易流水  积分率
function set_jifenlv($shop_no, $flag = '0'){
	$fee = '-';
	if(empty($shop_no)) {
		return $fee;
	}
	$shopdata = D('MSmdr')->findmoreSmdr("sh.SHOP_NO = '".$shop_no."' and sm.PAY_TYPE = 5", 'sm.JFB_PER_FEE');
	if(empty($shopdata)) {
		return $fee;
	}
	$fee = $shopdata['JFB_PER_FEE'];
	if ($flag !=0) {
		return $fee/10000;
	}
	return ($fee/100).'%';
}



//卡=后面的校验数据计算方法
function getCardCheck($cId){
	$key = "jfb.www.jfb315.com";
	$v1  = md5($cId.$key);
	$v2  = "";
	$sb  = "";
	$isOk = false;
	$j = 0;
	for($i=0; $i<10000;$i++){
		$v2 = $v1;
		for($j=0; $j<strlen($v1); $j++){
			if(ord($v1[$j])>=48 && ord($v1[$j])<=57){
				$sb .= $v1[$j];
				if(strlen($sb)>16){
					$isOk = true;
					break;
				}
			}
		}
		$v1 = md5($v2+$v1);
		if($isOk){
			break;
		}
	}
	return $sb;     
}

//生成随机的6位数
function getrand_code($length = 6 ,$type = 1) {
    // 密码字符集，可任意添加你需要的字符
    switch ($type) {
    	case '1':
    		$chars = '1234567890';
    		break;
    	case '2':
    		$chars = 'abcdefghijklmnopqrstuvwxyz';
    		break;
    	case '3':
    		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXY';
    		break;
    	default:
    		 $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    		break;
    }
    $password = '';
    for ( $i = 0; $i < $length; $i++ ) 
    {
        // 这里提供两种字符获取方式
        // 第一种是使用 substr 截取$chars中的任意一位字符；
        // 第二种是取字符数组 $chars 的任意元素
        // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
    }
    return $password;
}

//过滤权限
function filter_auth($id,$lv,$type=1){
	$res = false;
	switch ($type) {
		case '1':		//id过滤
			if (in_array($id, C('AUTH_PASS_ID'))) {
				$res = true;
			}
			break;
		case '2':		//级别过滤
			if (in_array($lv, C('AUTH_PASS_LV'))) {
				$res = true;
			}
			break;
		case '3':		//同时
			if (in_array($id, C('AUTH_PASS_ID')) or in_array($lv, C('AUTH_PASS_LV')) ) {
				$res = true;
			}
			break;
	}
	return $res;
}
//过滤数据
function filter_data($selname){
	//POST数据优先
	$data = I('post');
	if($data['bid']!='' || $data['pid']!=''){
		return array('bid' => $data['bid'], 'pid' => $data['pid']);
	}
	if($data['BRANCH_MAP_ID']!='' || $data['PARTNER_MAP_ID']!=''){
		return array('bid' => $data['BRANCH_MAP_ID'], 'pid' => $data['PARTNER_MAP_ID']);
	}
	if($data['SBRANCH_MAP_ID']!='' || $data['SPARTNER_MAP_ID']!=''){
		return array('bid' => $data['SBRANCH_MAP_ID'], 'pid' => $data['SPARTNER_MAP_ID']);
	}
	if($data['VBRANCH_MAP_ID']!='' || $data['VPARTNER_MAP_ID']!=''){
		return array('bid' => $data['VBRANCH_MAP_ID'], 'pid' => $data['VPARTNER_MAP_ID']);
	}
	//$lv = I($selname);
	$lv = get_level_val($selname);
	if (empty($lv['bid']) && empty($lv['pid'])) {
		$home = session('HOME');
		//如是没有过滤ID(总部) 使用SESSION数据
		if (!filter_auth($home['BRANCH_MAP_ID'])) {
			return array('bid' => $home['BRANCH_MAP_ID'], 'pid' => $home['PARTNER_MAP_ID'] ? $home['PARTNER_MAP_ID'] : '' );
		}
		return array('bid' => '', 'pid' => '');
	}else{
		return $lv;
	}
}
//发送XML数据
function send_xml($xmldata,$url,$chartype='utf-8'){
	//首先检测是否支持curl
	if (!extension_loaded("curl")) {
		trigger_error("对不起，请开启curl功能模块！", E_USER_ERROR);
	}
	//组装请求头
	$header[]="Content-Type: text/xml; charset=".$chartype;
	$header[]="User-Agent: Apache/1.3.26 (Unix)";
	$header[]="Host: {$_SERVER['HTTP_HOST']}";
	$header[]="Accept: text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2";
	$header[]="Connection: keep-alive";
	$header[]="Content-Length: ".strlen($xmldata);
	//$url = "http://{$_SERVER['HTTP_HOST']}".dirname($_SERVER['PHP_SELF']).'/dealxml.php';
	//初始一个curl会话
	$ch = curl_init();
	//设置url
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	//设置发送方式：post
	curl_setopt($ch, CURLOPT_POST, 1);
	//设置发送数据
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	//抓取URL并把它传递给浏览器
	$res = curl_exec($ch);
	//关闭cURL资源，并且释放系统资源
	curl_close($ch);
	header('Content-Type:text/xml; charset='.$chartype);
	//是否转码
	//$res = str_replace("encoding='GBK'", "encoding='UTF-8'", $res);
	return $res;
}
//接受XML数据
function get_xml($xmldata){
	//转换为simplexml对象
	$xmlResult = simplexml_load_string($xmldata);
	//转为数组
	$data = json_decode(json_encode($xmlResult),TRUE);
	return $data;
}

//发送post数据，含返回值
function httpPostForm($url, $data, $type='POST'){
	$postdata = http_build_query($data);
	$opts = array(
		'http' =>array(
			'method'	=>	$type,
			'header'	=>	'Content-type: application/x-www-form-urlencoded; charset=utf-8',
			'content'	=>	$postdata
		)
	);
	$context = stream_context_create($opts);
	$result  = file_get_contents($url, false, $context);
	return $result;
}


/**
 * 投保生成密钥		PHP des加密
 * @param $data 待加密明文
 * @param $key DES私钥
 * @param $use3des 是否启用3DES加密，默认不启用
 * 案例 des_encrypt('00000000123456789', '00000000');
 */
function des_encrypt($data='', $key='', $use3des = False){
	$list = des_inci($data, 8);
	$str  = '';
	foreach($list as $val){
		$str .= des_encr($val, $key, $use3des);
	}
	return $str;
}
function des_inci($str, $num, $resdata = array()){
	$length = strlen($str);
	if($length < $num){
		return !empty($resdata) ? array_merge($resdata, array($str)) : array($str);
	}else{
		$t1 = substr($str, 0, $num); 	//前$num位
		$t2 = substr($str, $num);		//剩下的
		$t3 = !empty($resdata) ? array_merge($resdata, array($t1)) : array($t1);
		return des_inci($t2, $num, $t3);
	}
}
function des_encr($data='', $key='', $use3des = False){
	if(empty($data) || empty($key)){
		return False;
	}
	$cipher = $use3des ? MCRYPT_TRIPLEDES : MCRYPT_DES;
	$modes = MCRYPT_MODE_ECB;
	# Add PKCS7 padding.
	$block = mcrypt_get_block_size($cipher, $modes);
	$pad   = $block - (strlen($data) % $block);
	$data .= str_repeat(chr($pad), $pad);

	$iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher, $modes), MCRYPT_RAND);
	$encrypted = @mcrypt_encrypt($cipher, $key, $data, $modes, $iv);
	
	$hex = '';
	for($i=0;$i<strlen($data);$i++)
		$hex .= dechex(ord($encrypted[$i]));
	//$hex = strtoupper($hex);
	return $hex;
}
function hex2asc ($str) 
{  
    $str = join('',explode('\x',$str));  
    $len = strlen($str);  
    for ($i=0;$i<$len;$i+=2) 
    $data.=chr(hexdec(substr($str,$i,2)));  
    return $data;  
}
//投保des加密
function encrypt($str, $key)      
{      
    $block = @mcrypt_get_block_size('des', 'ecb');      
    $pad = $block - (strlen($str) % $block);      
    $str .= str_repeat(chr($pad), $pad);      
      
    return @mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);      
}      
//投保des解密 
function decrypt($str, $key)
{        
    $str = @mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);      
      
    $block = @mcrypt_get_block_size('des', 'ecb');      
    $pad = ord($str[($len = strlen($str)) - 1]);      
    return substr($str, 0, strlen($str) - $pad);      
}

//自定义array_column()函数兼容低版本
function i_array_column($input, $columnKey, $indexKey=null){
    if(!function_exists('array_column')){ 
        $columnKeyIsNumber  = (is_numeric($columnKey))?true:false; 
        $indexKeyIsNull     = (is_null($indexKey))?true :false; 
        $indexKeyIsNumber   = (is_numeric($indexKey))?true:false; 
        $result 			= array(); 
        foreach((array)$input as $key=>$row){ 
            if($columnKeyIsNumber){ 
                $tmp= array_slice($row, $columnKey, 1); 
                $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null; 
            }else{ 
                $tmp= isset($row[$columnKey])?$row[$columnKey]:null; 
            } 
            if(!$indexKeyIsNull){ 
                if($indexKeyIsNumber){ 
                  $key = array_slice($row, $indexKey, 1); 
                  $key = (is_array($key) && !empty($key))?current($key):null; 
                  $key = is_null($key)?0:$key; 
                }else{ 
                  $key = isset($row[$indexKey])?$row[$indexKey]:0; 
                } 
            } 
            $result[$key] = $tmp; 
        } 
        return $result; 
    }else{
        return array_column($input, $columnKey, $indexKey);
    }
}

//文件上传
function uploadfile(){
	$post = array(
		'userid'	=>	I('userid'),
		'keyword'	=>	I('keyword')
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
   // $upload->rootPath =   $path; 			// 设置附件上传根目录
    $upload->savePath  	=   $path; 			// 设置附件上传（子）目录
    // 上传文件 
  	$info   =   $upload->upload();
    if(!$info) {
        return false;
    }else{
       	//取得成功上传的文件信息
        $uploadList = $upload->getUploadFileInfo();
		$file_url = substr( $uploadList[0]['savepath'].$uploadList[0]['savename'], 1);
		return $file_url;
    }
}



/* 
* 数组排序
* $arr
* $keys 按什么字段排序
*/
function arr_sort($arr,$keys,$orderby){
	$keysvalue = $new_array = array();
    foreach ($arr as $k=>$v){
        $keysvalue[$k] = $v[$keys];
    }
	if($orderby== 'asc'){
        asort($keysvalue);
    }else{
		arsort($keysvalue);
		
    }
    reset($keysvalue);
    foreach ($keysvalue as $k=>$v){
        $new_array[] = $arr[$k];
    }
    return $new_array; 
}

//unicode 中午加密
function unicode_encode($name){
    $name = iconv('UTF-8', 'UCS-2', $name);
    $len = strlen($name);
    $str = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2)
    {
        $c = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0)
        {   //两个字节的文字
            $str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
        }
        else
        {
            $str .= $c2;
        }
    }
    return $str;
}
//unicode 中午解码
function unicode_decode($name){
    //转换编码，将Unicode编码转换成可以浏览的utf-8编码
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches))
    {
        $name = '';
        for ($j = 0; $j < count($matches[0]); $j++)
        {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0)
            {
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code).chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $c);
                $name .= $c;
            }
            else
            {
                $name .= $str;
            }
        }
    }
    return $name;
}

//商户同步数据
function shop_sync_data($shop_id,$operateType = 1){
	if (empty($shop_id)) {
		return false;
	}
	// $shop_data = D('MShop')->findShop('SHOP_MAP_ID = "'.$shop_id.'" and SHOP_STATUS = 0');
	$shop_data = D('MShop')->findShop('SHOP_MAP_ID = "'.$shop_id.'"');
	if (empty($shop_data['SHOP_MAP_ID'])) {
		return false;
	}
	$city_data = D('MCity')->findCity("CITY_S_CODE='".$shop_data['CITY_NO']."'",'RPAD(PROVINCE_CODE,6,"0") as PROVINCE_CODE,RPAD(CITY_CODE,6,"0") as CITY_CODE,CITY_S_CODE');
	$res = get_level_data($shop_data['PARTNER_MAP_ID']);
	$oParentRelation = '[100000,0]';
	foreach ($res as $key => $value) {
		if ($key != 3) {
			$oParentRelation .='['.$value['id'].','.($key+1).']';
		}
	}
	$oParentRelation .= '['.$shop_id.',4]';
	//查询日均限额
	//$sauth_info = D('MSauth')->findSauth("SHOP_MAP_ID='".$shop_id."'");
	
	//查询封顶积分
	$topFee = M('smdr')->where('SHOP_MAP_ID = "'.$shop_id.'" and PAY_TYPE = 5')->field('JFB_FIX_FEE')->find();
	$sync_data = array(
		'token' 	 		=>  strtoupper(md5(strtoupper(md5($shop_id.$operateType)))),		//(签名验证)
		'id' 				=>	$shop_id,														//(商户ID)
		'shopCode'			=>	$shop_data['SHOP_NO'],											//(商户号)
		'shopPin'        	=>  $shop_data['SHOP_PIN'], //(商户密码)																													
		'shopKind'        	=>  $shop_data['SHOP_KIND'], //(商户分类)															
		'companyName' 		=>	$shop_data['SHOP_NAME'],										//(商户名称)
		'name' 				=>	$shop_data['SHOP_NAMEABCN'],									//(商户简称)
		'provinceCode' 		=>	$city_data['PROVINCE_CODE'],									//(省份)
		'cityCode' 			=>	$city_data['CITY_CODE'],										//(城市)
		'areaCode' 			=>	$city_data['CITY_S_CODE'],										//(区县)
		'address' 			=>	getcity_name($city_data['CITY_S_CODE']).$shop_data['ADDRESS'],	//(详细地址)
		'mobile' 			=>	$shop_data['MOBILE'],											//(联系手机)
		'telephone' 		=>	$shop_data['TEL'],												//(联系电话)
		'ciEmail' 			=>	$shop_data['EMAIL'],											//(邮箱地址)
		'consumePtype'		=>	$shop_data['MCC_TYPE'],											//(商户分类-大分类)
		'consumeType' 		=>	$shop_data['MCC_CODE'],											//(商户分类-小分类)
		'ration' 			=>	set_jifenlv($shop_data['SHOP_NO'],1),							//(积分率)
		'topRation' 		=>	setMoney($topFee['JFB_FIX_FEE'],2,2),							//(封顶)
		'oParentRelation'	=>	$oParentRelation,												//(商户归属关系)
		'operateType'		=>	$operateType,
		//'dailyLimit'		=>	$sauth_info['DAY_MAXAMT'] ? setMoney($sauth_info['DAY_MAXAMT'],2,2) : '30000' //(日均限额)
	);
	//银行账户信息
	$sbact = D('MSbact')->findSbact('SHOP_MAP_ID = "'.$shop_id.'"');
	//银行帐户组装
	switch ($sbact['SHOP_BANK_FLAG']) {
	 	case '0':
	 		$sync_data['bankActType'] = $sbact['SHOP_BANK_FLAG'];								//(银行账户-结算标志【0对公，1对私】)
	 		$sync_data['bankActName'] = $sbact['BANKACCT_NAME1'];								//(银行账户-户名)
	 		$sync_data['bankAccount'] = $sbact['BANKACCT_NO1'];									//(银行账户-账户)
	 		$sync_data['bankNo']	  = $sbact['BANKACCT_BID1'];								//(银行账户-开户行联行号)
	 		$sync_data['bankName']	  = $sbact['BANK_NAME1'];									//(银行账户-开户行)
	 		break;
	 	case '1':
	 		$sync_data['bankActType'] = $sbact['SHOP_BANK_FLAG'];
	 		$sync_data['bankActName'] = $sbact['BANKACCT_NAME2'];
	 		$sync_data['bankAccount'] = $sbact['BANKACCT_NO2'];
	 		$sync_data['bankNo']	  = $sbact['BANKACCT_BID2'];
	 		$sync_data['bankName']	  = $sbact['BANK_NAME2'];
	 		break;
	}
	//商户门头照
	$scert = D('MScert')->findScert('SHOP_MAP_ID = "'.$shop_id.'"');
	$sync_data['logoImg'] =  $scert['OFFICE_PHOTO1'];//(商户门头)	
	switch ($operateType) {
		case '1':
			$url = VIP_PUSH_URL.'api/open/synchronize/shop/review';
			break;
		case '2':
			$url = VIP_PUSH_URL.'api/open/synchronize/shop/modify';
			break;
		case '3':
			$url = VIP_PUSH_URL.'api/open/synchronize/shop/logout';
			break;
		default:
			return false;
			break;
	}
	Add_LOG(CONTROLLER_NAME, json_encode($sync_data));
	$resjson = httpPostForm($url,$sync_data);
	Add_LOG(CONTROLLER_NAME, $resjson);
	$result = json_decode($resjson);
	if ($result->code != '0') {
		return false;
	}
	return true;
}

//partner 根据当前partner_id 找到所有上级， 以[100,1],[101,2],[103,3]返回
function getParentRelation($pid){
	$res = get_level_data($pid);
	$str = '[100000,0]';
	foreach ($res as $key => $value) {
		$str .='['.$value['id'].','.($key+1).']';
	}
	return $str;
}

//aes加密
function aes_encrypt($plainText, $keyStr = '1234567890123456'){
	import("Org.Util.Myaes");
	$aes = new \Myaes();
	$aes->set_key($keyStr);
	$aes->require_pkcs5();
	return $aes->encrypt($plainText);
}
//aes解密
function aes_decrypt($encStr, $keyStr = '1234567890123456'){
	import("Org.Util.Myaes");
	$aes = new \Myaes();
	$aes->set_key($keyStr);
	$aes->require_pkcs5();
	return $decString = $aes->decrypt($encStr);	//aes解密
}

//根据身份证获取年龄
function getAgeByID($id){
    if(empty($id)) return '';
    $birthyear=substr($id,6,4);
    return (date('Y') - $birthyear);
/*
	//过了这年的生日才算多了1周岁
    if(empty($id)) return '';
    $date=strtotime(substr($id,6,8));
	//获得出生年月日的时间戳
    $today=strtotime('today');
	//获得今日的时间戳
    $diff=floor(($today-$date)/86400/365);
	//得到两个日期相差的大体年数
   
	//strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
    $age=strtotime(substr($id,6,8).' +'.$diff.'years')>$today?($diff+1):$diff;

    return $age;*/
} 
//记录会员id变更内容
function updatevip_log($result,$old_id,$new_id,$tablename){
   if($result === false) {
   		$content = '【'.$tablename.'】由原'.$old_id.' 变更为'.$new_id.'失败';
   		Add_LOG($tablename.'_log', $content);
	}else if($result == 0){
		$content = '【'.$tablename.'】由原'.$old_id.' 变更为'.$new_id.'未修改';
   		Add_LOG($tablename.'_log', $content);
	}else{
		$content = '【'.$tablename.'】由原'.$old_id.' 变更为'.$new_id.'成功';
   		Add_LOG($tablename.'_log', $content);
	}
}

//组装积分宝规则的归属
function jfbguishu($pid,$oid){
	//获取会员归属
	$res = get_level_data($pid);
	$oParentRelation = '[100000,0]';
	foreach ($res as $key => $value) {
		$oParentRelation .='['.$value['id'].','.($key+1).']';
	}
	if ($oid) {
		$oParentRelation .= '['.$oid.','.(count($res)+1).']';
	}
	return $oParentRelation;
}
/*
 * 功能     获取post rawdata 数据方法
 * @author  罗森
 * @date    2016.11.04
 * @para    $key      string   要获取的字段名
 * @para    $default  string 如果值为空时给的默认取给的默认参数
 */
function getPara($key, $default = "")
{
    $data = json_decode(file_get_contents('php://input'), true);
    return ($data[$key] === "" || $data[$key] === null) ? $default : $data[$key];
}
/*
 * 功能     利用CURL 获取的接口信息
 * @desc    post方式 以参数拼接后的字符形式：utf-8&f=8&rsv_bp=1&rsv_idx=1&tn=baidu&wd=querystr 传递
 * @author  罗森
 * @date    2016.11.04
 * @para    $url   string 是要访问的接口地址
 * @para    $data  array  接口要求的参数数组
 */
function getCurlData($url, $data)
{
    $querystr = "";
    foreach ($data as $k => $v) {
        $querystr .= $k . "=" . $v . "&";
    }
    $querystr = trim($querystr, "&");
    // writeSql($url . $querystr);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); //访问的接口地址
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //不输出在页面上
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $querystr); //post参数值
    $contents = curl_exec($ch);
    curl_close($ch);
    return json_decode($contents);
}
/*
 * 功能： 所有接口的签名方法
 * 规则： $str = $key.$funcName.$key.$signTime
 *        strtoupper(sha1(strtoupper(md5($str))));
 *      sign=E806005F12AE438241AE18FE3EB77069CECF82BF
 */
function rySign($data)
{
    $key      = "04BF6939558F476E8B2DB1F7E60C4769";
    $signTime = $data["signTime"];
    $funcName = $data["funcName"];
    $str      = $funcName . $key . $signTime;
    $sign     = strtoupper(sha1(strtoupper(md5($str))));
    //echo $sign;exit;
    if ($data["sign"] != $sign) {
        echo json_encode(array("status" => 290, "message" => "签名错误!"));
        exit();
    } else {
        return true;
    }

}
function szFileSign($data, $secretKey)
{
    foreach ($data as $k => $v) {
        if (($v === "") || ($v === null) || $k === "sign") {
            unset($data[$k]);
        }
    }
    //dump($data);
    //参数按键值排序
    ksort($data);
    $str = "";
    //拼接uri  query字符串 ‘k1=v1&k2=v2&k3=v3’
    foreach ($data as $key => $val) {
        $str .= $key . "=" . $val . "&";
    }
    $str = trim($str, "&") . "&key=" . $secretKey;
    return md5($str);
}
/**
 * 功能：将SQL语句写入文件函数
 * @param  string $sql      SQL语句
 * file_put_contents("你的目录加文件名路径", "你要写入的内容", FILE_APPEND);
 * //追加写入  FILE_APPEND 参数为追加内容
 * file_put_contents("./data/arr.txt",$a,FILE_APPEND);
 **/
function writeSql($sql)
{
    //$path = dirname(THINK_PATH).'/Public/file/'.date('Ym');
    //$filename = dirname(THINK_PATH).'/Public/file/'.date('Ym').'/'.date('YmdHis').rand(10000,99999).'.sql';
    $filename = dirname(THINK_PATH) . '/Public/file/' . date('YmdHis') . rand(100000, 999999) . 'test.log';
    $fileok   = rtrim($filename, ".sql") . ".ok";
    //echo $fileok;
    //exit;
    //if (is_dir($path)){
    chmod($filename, 777);
    file_put_contents($filename, $sql, FILE_APPEND);
    //file_put_contents($fileok,"",FILE_APPEND);
    //}else{
    //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
    //$res = mkdir(iconv("UTF-8", "GBK", $path),0777,true);
    //if ($res){
    //chmod($filename, 777);
    //file_put_contents($filename,$sql,FILE_APPEND);
    //         file_put_contents($fileok,"",FILE_APPEND);
    //     }else{
    //         echo "create path err";
    //     }
    // }
}
/*  功能：四舍六入五成双
 *  规则：
 *  1. 被修约的数字小于5时，该数字舍去；
 *  2. 被修约的数字大于5时，则进位；
 *  3. 被修约的数字等于5时，
 *          要看5前面的数字，
 *          若是奇数则进位，
 *          若是偶数则将5舍掉，
 *          即修约后末尾数字都成为偶数；
 *          若5的后面还有不为“0”的任何数，则此时无论5的前面是奇数还是偶数，均应进位。
 * @para   $num  结算数字
 * @para   $precision  保留位数
 */
function round465($num, $precision = 2)
{
    $pow = pow(10, $precision);
    if ((floor($num * $pow * 10) % 5 == 0) && (floor($num * $pow * 10) == $num * $pow * 10) && (floor($num * $pow) % 2 == 0)) {
//舍去位为5 && 舍去位后无数字 && 舍去位前一位是偶数    =》 不进一
        return floor($num * $pow) / $pow;
    } else {
//四舍五入
    }
        return round($num, $precision);
}
/*
 * 功能     胜智接口签名方法
 * @author  罗森
 * @date    2016.11.04
 * @para    $data  array  需要签名的字段数组
 *
 */
function szSign($data, $signKey)
{
    foreach ($data as $k => $v) {
        if (($v === "") || ($v === null) || $k === "sign") {
            unset($data[$k]);
        }
    }
    //参数按键值排序
    ksort($data);
    $str = "";
    //拼接uri  query字符串 ‘k1=v1&k2=v2&k3=v3’
    foreach ($data as $key => $val) {
        $str .= $key . "=" . $val . "&";
    }
    //$str = trim($str, "&")."&key=".C("SZ_SIGN_KEY");
    $str = trim($str, "&") . "&key=" . $signKey;
    $str = md5($str);
    return strtoupper($str);
}
/**
 * 验证手机号是否正确
 * @author sea
 * @param INT $mobile
 */
function isMobile($mobile) {
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}
function httpGet($url){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $contents = curl_exec($ch);
    curl_close($ch);
    return json_decode($contents);

}


/*
 * 功能     利用CURL 获取的接口信息
 * @desc    post方式 json传输 
 * @date    2016.12.11
 * @para    $url   string 是要访问的接口地址
 * @para    $data  array  接口要求的参数数组
 */
function getCurlDataByjson($url,$jsonStr){
    $ch = curl_init($url); //请求的URL地址
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");//post方式发送
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);//$data JSON类型字符串
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//不输出在页面上
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($jsonStr)));
    $contents = curl_exec($ch);//这里的data打印出来是空的
    curl_close($ch);
    return json_decode($contents);
}
/*
 * 访问后台接口的签名方法
 */
function rySignStr($data){
    $key      = "04BF6939558F476E8B2DB1F7E60C4769";//签名秘钥
    $signTime = $data["signTime"];
    $funcName = $data["funcName"];
    $str      = $funcName.$key.$signTime;
    $sign     = strtoupper(sha1(strtoupper(md5($str))));
    return $sign;
}
/* 2017 04 20 CRYSTAL 金额左补0 */
function getLD($str){
	return str_pad($str,18,'0',STR_PAD_LEFT);
}

function doPostArr($url, $post_data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    return  curl_exec($ch);
}
/* 
 * @params
 *      $url    string
 *      $post_data array
 * curl 通过json格式请求
 * @author  sea
 * @email zouhai@ruiyit.com
 * @time 2016-10-20
 *  */
function doPost($url, $post_data){
    $jsonData = json_encode($post_data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($ch);
	curl_close($ch);
    return $result;
}

/*
 * 功能     利用CURL 获取的接口信息
 * @desc    post方式 以参数拼接后的字符形式：utf-8&f=8&rsv_bp=1&rsv_idx=1&tn=baidu&wd=querystr 传递
 * @author  罗森
 * @date    2017.11.04
 * @para    $url   string 是要访问的接口地址
 * @para    $data  array  接口要求的参数数组
 */
function getDownData($url, $data)
{
    $querystr = "";
    foreach ($data as $k => $v) {
        $querystr .= $k . "=" . $v . "&";
    }
    $querystr = trim($querystr, "&");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); //访问的接口地址
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //不输出在页面上
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $querystr); //post参数值
    $contents = curl_exec($ch);
    curl_close($ch);
    return $contents;
}

//记录日志
function Add_DWON_LOG($name, $content){
	//检查目录
	$path = './Public/file/apilog/';
	mkdir($path, 0700);
	if(empty($content)){
		file_put_contents($path.$name, PHP_EOL, FILE_APPEND);
		$first = '[ '.date('Y-m-d H:i:s').' ]  '.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REMOTE_ADDR'].'  http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '');
		file_put_contents($path.$name, $first.PHP_EOL, FILE_APPEND);
	}else{
		file_put_contents($path.$name, $content.PHP_EOL, FILE_APPEND);		
	}
}

 //初始化银行卡实名认证签名参数
function initBankParams($rdata){
    $version = C('VERSION');//版本号
    $accCode = C('ACCCODE');//调用方注册的账户ID
    $accessKeyId = C('ACCESSKEYID');//用户申请的密钥的索引
    $merchantId = C('MERCHANTID');//商户号
    $cdata = array(
       "serviceCode"=>null,
       "name"=>null,
       "idNumber"=>null,
       "merchantId"=>$merchantId,
       "bankCard"=>null,
       "mobile"=>null,
       "requestId"=>generateNo(),
       "accCode"=>$accCode,
       "accessKeyId"=>$accessKeyId,
       "version"=>$version,
       "timestamp"=>time()."000",
    );
    foreach($cdata as $key=>$value){
        if(array_key_exists($key, $rdata)){
            $cdata[$key] = $rdata[$key];
            // echo $rdata[$key];
        }  
    }
    return $cdata; 
}

function generateNo(){
    return time()."000".rand(10000,99999);
}
//实名认证数据传输
function reqApi($rdata, $blpost = false){
    $testurl = C('SONGSHUN_URL');
    $version = C('VERSION');//版本号
    $accCode = C('ACCCODE');//调用方注册的账户ID
    $accessKeyId = C('ACCESSKEYID');//用户申请的密钥的索引
    $merchantId = C('MERCHANTID');//商户号
    $signatur = songshun_sign($rdata);
    $aUrl = $testurl."/".$version."/".$accCode."/".$accessKeyId."/".$signatur."/".$rdata["timestamp"];
    if($blpost){
        $rt = doPostArr($aUrl,$rdata);
    }
    else{
        $enstr = "";
        foreach($rdata as $mk => $mv)
        { 
          if($mv != null && $mv != ""){
             $enstr =  $enstr.$mk."=".urlencode($mv)."&";
          }
        }
        $enstr = rtrim($enstr,"&");
        $aUrl = $aUrl."?".$enstr;
        $rt = requestGet($aUrl);
    }
    Add_DWON_LOG('auth_photo',$rt);
    return $rt;
}

function songshun_sign($rdata){
       $PRIVATEKEY = C('PRIVATEKEY');
       ksort($rdata);
       $enstr = "";
       foreach($rdata as $mk => $mv)
       { 
         if($mv != null && $mv != ""){
            $enstr =  $enstr.$mk."=".$mv."&";
         }
       }
       $enstr = rtrim($enstr,"&");
       $signatur = md5($enstr.$PRIVATEKEY);
       return $signatur;
   }

function requestGet($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $data = curl_exec($ch);
    return $data;
}

//json传值为中文
function decodeUnicode($str)
	{
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
        create_function(
            '$matches',
            'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
        ),
        $str);
	}

/**
　　* 下载图片到服务器
　　* @param string $image_file 网站图片地址
　　* @return $save_image_file 服务器图片绝对地址
　　*/
function DownLoadPic($image_file){
	$filename = date('His').floor(microtime()*1000);
	$fileupname = 'Public/person_photo/'.date('Ymd');
	if (!file_exists($fileupname)) {
		mkdir($fileupname);
	}
    $save_image_file = $fileupname.'/'.$filename.'.jpg';
	// $file = file_put_contents($save_image_file, file_get_contents($image_file),FILE_APPEND);
	$file = file_put_contents($save_image_file, $image_file,FILE_APPEND);
	return $save_image_file;
}

/*利用PHP将部分内容用星号替换*/
function replaceStar($str, $start, $length = 0)
	{
	  $i = 0;
	  $star = '';
	  if($start >= 0) {
	   if($length > 0) {
	    $str_len = strlen($str);
	    $count = $length;
	    if($start >= $str_len) {//当开始的下标大于字符串长度的时候，就不做替换了
	     $count = 0;
	    }
	   }elseif($length < 0){
	    $str_len = strlen($str);
	    $count = abs($length);
	    if($start >= $str_len) {//当开始的下标大于字符串长度的时候，由于是反向的，就从最后那个字符的下标开始
	     $start = $str_len - 1;
	    }
	    $offset = $start - $count + 1;//起点下标减去数量，计算偏移量
	    $count = $offset >= 0 ? abs($length) : ($start + 1);//偏移量大于等于0说明没有超过最左边，小于0了说明超过了最左边，就用起点到最左边的长度
	    $start = $offset >= 0 ? $offset : 0;//从最左边或左边的某个位置开始
	   }else {
	    $str_len = strlen($str);
	    $count = $str_len - $start;//计算要替换的数量
	   }
	  }else {
	   if($length > 0) {
	    $offset = abs($start);
	    $count = $offset >= $length ? $length : $offset;//大于等于长度的时候 没有超出最右边
	   }elseif($length < 0){
	    $str_len = strlen($str);
	    $end = $str_len + $start;//计算偏移的结尾值
	    $offset = abs($start + $length) - 1;//计算偏移量，由于都是负数就加起来
	    $start = $str_len - $offset;//计算起点值
	    $start = $start >= 0 ? $start : 0;
	    $count = $end - $start + 1;
	   }else {
	    $str_len = strlen($str);
	    $count = $str_len + $start + 1;//计算需要偏移的长度
	    $start = 0;
	   }
	  }
	 
	  while ($i < $count) {
	   $star .= '*';
	   $i++;
	  }
	 
	  return substr_replace($str, $star, $start, $count);
	}
?>