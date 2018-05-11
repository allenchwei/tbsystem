<?php
namespace Home\Model;
use Think\Model;
// +----------------------------------------------------------------------
// | @ljf	MMcctype	商户大类管理
// +----------------------------------------------------------------------
class MMcctypeModel extends Model{
	
	function __construct(){
		$this->mcctype   = "mcctype";
		$this->MMcc   	 = "MMcc";
	}
	
	/*
	* 获取列表
	* @post:
	**/
	public function getMcctypelist($where, $field='*', $limit, $order='MCC_TYPE asc') {
		return M($this->mcctype)->where($where)->field($field)->limit($limit)->order($order)->select();
	}
	
	/*
	* 获取MCC下拉联动
	* @post:
	**/
	public function getMcctypesel_2($type='',$code='',$mcc_type='MCC_TYPE', $mcc_code='MCC_CODE') {
		$mc = '';$mt = '';$time = getmicrotime();
		if (!empty($type)) {
			$result['MCC_TYPE'] = $type;
		}else{
			$result = D($this->MMcc)->findMcc('MCC_CODE = '.$code, 'MCC_TYPE');
		}
		//获取已选中的MCC码, 下拉列表
		//if (!empty($code)) {
			$mc = D($this->MMcc)->getMcc_codelist($result['MCC_TYPE']);
			if ($mc) {
				foreach($mc as $val){
					//设置MCC码选中项
					if ($code == $val['0']) {
						$mc .= '<option value="'.$val['0'].'" selected >'.$val['1'].'</option>';
					}else{
						$mc .= '<option value="'.$val['0'].'">'.$val['1'].'</option>';
					}
				}
			}else{
				$mc = '<option value="">请选择</option>';
			}
		//}
		//获取已选中的MCC类, 下拉列表
		$mcclist = C('MCC_TYPE');
		foreach($mcclist as $key => $val){
			//设置MCC类选中项
			if ($result['MCC_TYPE'] == $key) {
				$mt .= '<option value="'.$key.'" selected >'.$val.'</option>';
			}else{
				$mt .= '<option value="'.$key.'">'.$val.'</option>';
			}
		}
		$res = '<select class="combox" name="'.$mcc_type.'" ref="combox_mcctype_'.$time.'" refUrl="'.__MODULE__.'/Public/ajaxgetmcccode/mcctype/{value}">
					  <option value="">请选择</option>'.$mt.'
				</select>
				<select class="combox" name="'.$mcc_code.'" id="combox_mcctype_'.$time.'">
					  '.$mc.'
				</select>';		
		return $res;
	}

	/*
	* 获取MCC下拉联动
	* @post:
	**/
	public function getMcctypesel($type='',$code='',$mcc_type='MCC_TYPE', $mcc_code='MCC_CODE') {
		$mc = '';$mt = '';$time = getmicrotime();$ajax_url = __MODULE__."/Public/ajaxgetmcccode/mcctype/";
		if (!empty($type)) {
			$result['MCC_TYPE'] = $type;
		}else{
			$result = D($this->MMcc)->findMcc('MCC_CODE = '.$code, 'MCC_TYPE');
		}
		//获取已选中的MCC码, 下拉列表
		//if (!empty($code)) {
			$mc = D($this->MMcc)->getMcc_codelist($result['MCC_TYPE']);
			if ($mc) {
				foreach($mc as $val){
					$mcode = $val['0'] ? ' ['.$val['0'].']':'';
					//设置MCC码选中项
					if ($code == $val['0']) {
						$mc .= '<option value="'.$val['0'].'" selected >'.$val['1'].$mcode.'</option>';
					}else{
						$mc .= '<option value="'.$val['0'].'">'.$val['1'].$mcode.'</option>';
					}
				}
			}else{
				$mc = '<option value="">请选择</option>';
			}
		//}
		//获取已选中的MCC类, 下拉列表
		$mcclist = C('MCC_TYPE');
		foreach($mcclist as $key => $val){
			//设置MCC类选中项
			if ($result['MCC_TYPE'] == $key) {
				$mt .= '<option value="'.$key.'" selected >'.$val.'</option>';
			}else{
				$mt .= '<option value="'.$key.'">'.$val.'</option>';
			}
		}
		$res = '<select class="combox" name="'.$mcc_type.'" id="combox_mcctype_1_'.$time.'">
					  <option value="">请选择</option>'.$mt.'
				</select>
				<select name="'.$mcc_code.'" id="combox_mcctype_2_'.$time.'">
					  '.$mc.'
				</select>';
		
$selhtml = <<<selscript
				$.ajax({
				   type: "POST",
				   url : ajax_url,
				   data: "mcctype="+ mcctype,
				   async: false,
				   success: function(data){
				   		var sel_str = '';
				   		if(data){
							//sel_str = '<select id="'+ sel2_id +'" name="'+ name +'">';
							var mcode = '';
							$.each(data, function(i){
								if (data[i] && data[i].length > 1){
									mcode = data[i][0] ? ' [' + data[i][0] + ']':'';
									sel_str += '<option value="'+data[i][0]+'">'+data[i][1] + mcode +'</option>';
								}
							});
						}else{
							sel_str += '<option value="">请选择</option>';
						}
				   			
							//sel_str +='</select>';
				   		
				   		$('select[name="'+name+'"]').html(sel_str);
				   		//删除当前级别后所有级别, 添加新的级别菜单
				   		var param = this_obj.closest('div.combox');
		   				param.nextAll('div.tinyselect').remove();
		   				$('div.dropdown').remove();
						$("#"+sel2_id).tinyselect({"boxId": sel2_id+'_2'});

						//定位下拉菜单
						var offset = $("#"+sel2_id+'_2').offset();
						$("div.dropdown").css({ "top": offset.top+24, "left": offset.left});
					}
				});	
selscript;
$script_str = '<script>
				$("#combox_mcctype_1_'.$time.'").change(function(){
					var this_obj = $(this);
					var ajax_url = "'.$ajax_url.'";
					var mcctype = this_obj.val();
					var sel2_id = "combox_mcctype_2_'.$time.'";
					var name = "'.$mcc_code.'";'.$selhtml.'
				})
				//下拉列表初始化
				$("div.dropdown").remove();
				$("#combox_mcctype_2_'.$time.'").tinyselect({"boxId": "combox_mcctype_2_'.$time.'_2"});

				//定位下拉菜单
				var offset = $("#combox_mcctype_2_'.$time.'_2").offset();
				var h = $("#combox_mcctype_2_'.$time.'_2").height();
				$("div.dropdown").css({ "top": offset.top+h, "left": offset.left+60 });
			</script>';
		return $res.$script_str;
	}
}
