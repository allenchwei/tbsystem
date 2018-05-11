<?php
/**
 * 用法
 * 	vertion:V2.5
 *  vendor('Alipay.ChinaPay');
 *	$pconfig  = C('ChinaPay');
 *	$Pay  = new \ChinaPay($pconfig);
 *	$res  = $Pay->cp_pay($info);
 */
include_once(DOC_ROOT."/Public/chinapay/netpayclient.php");
//include_once(DOC_ROOT."/Application/Common/Common/function.php");
header('Content-type:text/html;charset=utf-8');

class ChinaPay{
	var $pconfig;
	function __construct($pconfig){
		$this->pconfig = $pconfig;
	}
    function ChinaPay($pconfig) {
    	
    	$this->__construct($pconfig);
    	
		
    }
	
/**
 * [cp_pay 单笔代扣 (代扣交易处理)]
 * @param  [array] $info [数据]
 * @return [type]       [description]
 */
	function cp_pay($info){
		//处理证件类型 DK_IDNO_TYPE  0身份证、1护照、2军人证3、回乡证   9为未知
		switch($info['DK_IDNO_TYPE']){
			case 0:
				$idno_type = '01';//身份证01； // 户口簿04 ；
				break;
			case 1:
				$idno_type = '03';// 护照03 ；
				break;
			case 2:
				$idno_type = '02';// 军官证02 ；
				break;
			case 3:
				$idno_type = '05';// 回乡证05 ；
				break;
			default:
				$idno_type = '06';	// 其他06；			
		}	
		$merid = buildKey($this->pconfig['pri_key']);
		if(!$merid) {
			$this->wrong("导入私钥文件出错！");
		}
		//组装数据
		$order_id = substr(getmicrotime(),2);
		$resdata  = array(
			'merId'			=>	$merid,										//商户号
			'transDate'		=>	date('Ymd'),								//商户日期
			'orderNo'		=>	$order_id,									//订单号
			'transType'		=>	'0003',										//交易类型
			'openBankId'	=>	'0'.substr($info['BANK_BID'],0,3),			//开户行号
			'cardType'		=>	$info['SHOP_ACCT_FLAG'] ? $info['SHOP_ACCT_FLAG'] : 0,	//卡折标志
			'cardNo'		=>	$info['BANKACCT_NO'],						//卡号/折号
			'usrName'		=>	unicode_encode($info['BANKACCT_NAME']),		//持卡人姓名
			'certType'		=>	$idno_type,									//证件类型
			'certId'		=>	$info['DK_IDNO'],							//证件号
			'curyId'		=>	'156',										//币种
			'transAmt'		=>	$info['DK_AMT'],							//金额
			'purpose'		=>	'',											//用途
			'priv1'			=>	unicode_encode('私有域'),					//私有域
			'version'		=>	'20151207',									//版本号
			'gateId'		=>	'7008',										//网关号
			'termType'		=>	'07',										//渠道类型
			'payMode'		=>	'1',										//交易模式
		);
		//计算签名值
		$plain 	  = $resdata['merId'].$resdata['transDate'].$resdata['orderNo'].$resdata['transType'].$resdata['openBankId'].$resdata['cardType'].$resdata['cardNo'].$resdata['usrName'].$resdata['certType'].$resdata['certId'].$resdata['curyId'].$resdata['transAmt'].$resdata['priv1'].$resdata['version'].$resdata['gateId'].$resdata['termType'].$resdata['payMode'];
		$chkvalue = sign(base64_encode($plain));
		if(!$chkvalue) {
			return false;
		}
		$resdata['chkValue'] = $chkvalue;
		$res = httpPostForm($this->pconfig['url_pay'], $resdata);
		$res = str_replace('=', '":"', $res);
		$res = str_replace('&', '","', $res);
		$res = '{"'.$res.'"}';
		$res = json_decode($res, true);
		return $res;
	}

/**
 * [cp_query description]
 * @param  [type] $info [description]
 * @return [type]       [description]
 */
	function cp_query($info){

	}
	
	
	
}
?>