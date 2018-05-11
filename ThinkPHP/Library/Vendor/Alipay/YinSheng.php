<?php
header("Content-type: text/html; charset=utf-8");
class YinSheng{

	var $pconfig;
	function __construct($pconfig){
		$this->pconfig = $pconfig;
	}
    function YinSheng($pconfig) {
    	$this->__construct($pconfig);
    }
	
	//签名加密
	function yx_encrypt($data){
		$data   = iconv("UTF-8", "GBK//IGNORE", $data);		
		$return = array('success'=>0, 'msg'=>'', 'check'=>'');			
		$pkcs12 = file_get_contents($this->pconfig['pfxpath']); //私钥
		if(openssl_pkcs12_read($pkcs12, $certs, $this->pconfig['pfxpassword'])) {
			$privateKey = $certs['pkey']; 
			$publicKey  = $certs['cert'];			
			$signedMsg  = ""; 
			if(openssl_sign($data, $signedMsg, $privateKey, OPENSSL_ALGO_MD5)) { 
				$return['success'] = 1;
				$return['check']   = sprintf('%-256s',base64_encode($signedMsg));
				$return['msg']     = base64_encode($data);
			}
		}
		return $return;
	}
	
	//单笔代扣
	function yx_pay($MsgCode, $OrderId, $sign){
		$post = array(
			'src'		=> 	$this->pconfig['UserCode'],
			'msgCode' 	=> 	$MsgCode,
			'msgId'   	=> 	sprintf('%014d', $OrderId),
			'check'   	=> 	$sign['check'],
			'msg'    	=> 	$sign['msg'],
		);
		$res = httpPostForm($this->pconfig['xmlbackmsg_url'], $post);
		$res = explode("       ",$res);
		$str = $res[count($res)-1];
		$arr = get_xml(base64_decode($str));
		$arr = $arr['body'];
		if(empty($arr['Result'])){			
			return array('state'=>1, 'msg'=>'代扣失败！'.$arr['Note']);
		}
		if($arr['Result']['BusiState'] == '50'){
			return array('state'=>2, 'msg'=>'等待中！'.$arr['Result']['Note']);
		}
		if($arr['Result']['BusiState'] != '00'){
			return array('state'=>1, 'msg'=>'代扣失败！'.$arr['Result']['Note']);
		}
		return array('state'=>0, 'msg'=>'代扣成功！', 'data'=>$arr);
	}
	
	//代扣模板
	function yx_xmlS1031($param, $xml=null){
		$xml = '<?xml version="1.0" encoding="GBK"?>
				<yspay>
					<head>
						<Ver>'.$param['Ver'].'</Ver>
						<Src>'.$this->pconfig['UserCode'].'</Src>
						<MsgCode>'.$param['MsgCode'].'</MsgCode>
						<Time>'.$param['Time'].'</Time>
					</head>
					<body>
						<Order>
							<OrderId>'.$param['OrderId'].'</OrderId>
							<BusiCode>'.$param['BusiCode'].'</BusiCode>
							<ShopDate>'.$param['ShopDate'].'</ShopDate>
							<Cur>'.$param['Cur'].'</Cur>
							<Amount>'.$param['Amount'].'</Amount>
							<Note>'.$param["Note"].'</Note>
						</Order>
						<Payee>
							<UserCode>'.$this->pconfig['UserCode'].'</UserCode>
							<Name>'.$this->pconfig['Name'].'</Name>
						</Payee>
						<Payer>
							<BankAccountType>'.$param['BankAccountType'].'</BankAccountType>
							<BankName>'.$param['BankName'].'</BankName>
							<AccountNo>'.$param['AccountNo'].'</AccountNo>
							<AccountName>'.$param['AccountName'].'</AccountName>
							<Province>'.$param['Province'].'</Province>
							<City>'.$param['City'].'</City>
							<BankCode>'.$param['BankCode'].'</BankCode>
							<ExtraData>'.$param['ExtraData'].'</ExtraData>
						</Payer>
					</body>
				</yspay>';
		return $xml;
	}
		
	
	//------------------------------------------------------------------------------
	
		
	//签名加密
	function setencrypt($data){
		$data   = iconv("UTF-8", "GBK//IGNORE", $data);		
		$return = array('success'=>0, 'msg'=>'', 'check'=>'');			
		$pkcs12 = file_get_contents($this->pconfig['pfxpath']); //私钥
		if(openssl_pkcs12_read($pkcs12, $certs, $this->pconfig['pfxpassword'])) {
			$privateKey = $certs['pkey']; 
			$publicKey  = $certs['cert'];			
			$signedMsg  = ""; 
			if(openssl_sign($data, $signedMsg, $privateKey, OPENSSL_ALGO_MD5)) { 
				$return['success'] = 1;
				$return['check']   = sprintf('%-256s',base64_encode($signedMsg));
				$return['msg']     = base64_encode($data);
			}
		}
		return $return;
	}
	
	//银盛发送数据
	function httpysdata($MsgCode, $OrderId, $sign){
		$post = array(
			'src'		=> 	$this->pconfig['UserCode'],
			'msgCode' 	=> 	$MsgCode,
			'msgId'   	=> 	sprintf('%014d', $OrderId),
			'check'   	=> 	$sign['check'],
			'msg'    	=> 	$sign['msg'],
		);
		$res = httpPostForm($this->pconfig['xmlbackmsg_url'], $post);
		$res = explode("       ",$res);
		$str = $res[count($res)-1];
		$arr = get_xml(base64_decode($str));
		$arr = $arr['body'];
		return $arr;
	}
	
	//单笔查询模板	5001
	function sel_one_tmp($param, $xml=null){
		$xml = '<?xml version="1.0" encoding="GBK"?>
				<yspay>
					<head>
						<Ver>'.$param['Ver'].'</Ver>
						<Src>'.$this->pconfig['UserCode'].'</Src>
						<MsgCode>'.$param['MsgCode'].'</MsgCode>
						<Time>'.$param['Time'].'</Time>
					</head>
					<body>
						<OrderId>'.$param['OrderId'].'</OrderId>
						<TradeSN>'.$param['TradeSN'].'</TradeSN>
					</body>
				</yspay>';
		return $xml;
	}
	
	//单笔代扣模板	1031
	function dk_one_tmp($param, $xml=null){
		$xml = '<?xml version="1.0" encoding="GBK"?>
				<yspay>
					<head>
						<Ver>'.$param['Ver'].'</Ver>
						<Src>'.$this->pconfig['UserCode'].'</Src>
						<MsgCode>'.$param['MsgCode'].'</MsgCode>
						<Time>'.$param['Time'].'</Time>
					</head>
					<body>
						<Order>
							<OrderId>'.$param['OrderId'].'</OrderId>
							<BusiCode>'.$param['BusiCode'].'</BusiCode>
							<ShopDate>'.$param['ShopDate'].'</ShopDate>
							<Cur>'.$param['Cur'].'</Cur>
							<Amount>'.$param['Amount'].'</Amount>
							<Note>'.$param["Note"].'</Note>
						</Order>
						<Payee>
							<UserCode>'.$this->pconfig['UserCode'].'</UserCode>
							<Name>'.$this->pconfig['Name'].'</Name>
						</Payee>
						<Payer>
							<BankAccountType>'.$param['BankAccountType'].'</BankAccountType>
							<BankName>'.$param['BankName'].'</BankName>
							<AccountNo>'.$param['AccountNo'].'</AccountNo>
							<AccountName>'.$param['AccountName'].'</AccountName>
							<Province>'.$param['Province'].'</Province>
							<City>'.$param['City'].'</City>
							<BankCode>'.$param['BankCode'].'</BankCode>
							<ExtraData>'.$param['ExtraData'].'</ExtraData>
						</Payer>
					</body>
				</yspay>';
		return $xml;
	}
}
?>