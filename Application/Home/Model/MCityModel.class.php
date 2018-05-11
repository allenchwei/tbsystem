<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	城市
// +----------------------------------------------------------------------
class MCityModel extends Model{
	
	function __construct(){
		$this->city	= "city";
	}

	/*
	* 获取地区所有
	* @post:
	**/
	public function getCity_morelist($area, $city, $province, $code) {
		$p = ''; $c = ''; $a = '';$time = getmicrotime();
		$data 	  = $this->findCity("CITY_S_CODE='".$code."'");
		if (empty($data)) {
			return $this->getCity_plist($area, $city, $province);
		}
		$plist 	  = M($this->city)->group('PROVINCE_CODE')->select();
		foreach($plist as $val){
			$selected  = $val['PROVINCE_CODE']==$data['PROVINCE_CODE'] ? 'selected' : '';
			$p		  .= '<option value="'.$val['PROVINCE_CODE'].'0000" '.$selected.'>'.$val['PROVINCE_NAME'].'</option>';
		}
		
		$clist = M($this->city)->where("CITY_CODE > '".$data['PROVINCE_CODE']."00' and CITY_CODE <= '".$data['PROVINCE_CODE']."99'")->group('CITY_CODE')->select();
		foreach($clist as $val){
			$selected  = $val['CITY_CODE']==$data['CITY_CODE'] ? 'selected' : '';
			$c 	      .= '<option value="'.$val['CITY_CODE'].'00" '.$selected.'>'.$val['CITY_NAME'].'</option>';
		}
		
		$alist = M($this->city)->where("CITY_S_CODE > '".$data['CITY_CODE']."00' and CITY_S_CODE <= '".$data['CITY_CODE']."99'")->select();
		foreach($alist as $val){
			$selected  = $val['CITY_S_CODE']==$code ? 'selected' : '';
			$a 	      .= '<option value="'.$val['CITY_S_CODE'].'" '.$selected.'>'.$val['CITY_S_NAME'].'</option>';
		}	
		$res = '<select class="combox" name="'.$province.'" name="" ref="combox_city_'.$time.'" refUrl="/index.php/Home/Public/ajaxgetcity?flag=2&code={value}">
					  '.$p.'
				</select>
				<select class="combox" name="'.$city.'" id="combox_city_'.$time.'" ref="combox_area_'.$time.'" refUrl="/index.php/Home/Public/ajaxgetcity?flag=3&code={value}">
					  '.$c.'
				</select>
				<select class="combox" name="'.$area.'" id="combox_area_'.$time.'">
					  '.$a.'
				</select>';		
		return $res;
	}
	
	/*
	* 获取省 select
	* @post:
	**/
	public function getCity_plist($area, $city, $province) {
		$p = '';	$time = getmicrotime();
		$plist 	  = M($this->city)->group('PROVINCE_CODE')->select();
		foreach($plist as $val){
			$p .= '<option value="'.$val['PROVINCE_CODE'].'0000">'.$val['PROVINCE_NAME'].'</option>';
		}
		$res = '<select class="combox" name="'.$province.'" ref="combox_city_'.$time.'" refUrl="/index.php/Home/Public/ajaxgetcity?flag=2&code={value}">
					  <option value="">请选择</option>'.$p.'
				</select>
				<select class="combox" name="'.$city.'" id="combox_city_'.$time.'" ref="combox_area_'.$time.'" refUrl="/index.php/Home/Public/ajaxgetcity?flag=3&code={value}">
					  <option value="">请选择</option>
				</select>
				<select class="combox" name="'.$area.'" id="combox_area_'.$time.'">
					  <option value="">请选择</option>
				</select>';		
		return $res;
	}
	
	/*
	* 获取市 json
	* @post:
	**/
	public function getCity_clist($code) {
		if( empty($code) ) {
			$res[] = array('', '请选择');
		} else {
			$sub   = substr($code, 0, 2);
			$clist = M($this->city)->where("CITY_CODE > '".$sub."00' and CITY_CODE <= '".$sub."99'")->group('CITY_CODE')->select();
			if(!empty($clist)){
				foreach($clist as $val){
					$res[] = array($val['CITY_CODE'].'00', $val['CITY_NAME']);
				}
			}else{
				$res[] = array('', '请选择');
			}			
		}
		return $res;
	}
	
	/*
	* 获取县 json
	* @post:
	**/
	public function getCity_alist($code) {
		if( empty($code) ) {
			$res[] = array('', '请选择');
		} else {			
			$sub   = substr($code, 0, 4);
			$alist = M($this->city)->where("CITY_S_CODE > '".$sub."00' and CITY_S_CODE <= '".$sub."99'")->select();
			if(!empty($alist)){
				foreach($alist as $val){
					$res[] = array($val['CITY_S_CODE'], $val['CITY_S_NAME']);
				}
			} else {
				$res[] = array('', '请选择');				
			}			
		}
		return $res;
	}
		
	
	/*
	* 获取省列表
	* @post:
	**/
	public function getCityslist($field) {
		return M($this->city)->group('PROVINCE_CODE')->field($field)->select();
	}
	
	/*
	* 获取市列表
	* @post:	省id   "PROVINCE_CODE = '".substr($city_no,0,-4)."'" 		RPAD(PROVINCE_CODE,6,"0") as aId
	**/
	public function getCityclist($where, $field) {
		return M($this->city)->where($where)->group('CITY_CODE')->field($field)->select();
	}
	
	/*
	* 获取县列表
	* @post:	市id   "CITY_CODE = '".substr($city_no,0,-2)."'"
	**/
	public function getCityxlist($where, $field) {
		return M($this->city)->where($where)->field($field)->select();
	}
	
	
	/*
	* 获取单条信息
	* @post:
	**/
	public function findCity($where, $field='*') {
		return M($this->city)->where($where)->field($field)->find();
	}
}
