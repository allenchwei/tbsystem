<?php if (!defined('THINK_PATH')) exit();?><div class="pageContent">
	<form action="/index.php/Home/Daikuan/luck_record_add/navTabId/Daikuan" method="post" name="form" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone)">
		<input type="hidden" name="post[submit]" value="luck_record_add">
		<input type="hidden" name="post[ID]" value="<?php echo ($info["ID"]); ?>">
		<div class="pageFormContent" layoutH="<?php echo (C("tk_max_add_lay")); ?>">
			<div class="content30">
				<p style="width: 50%">
					<label>奖项名称：</label>
					<input type="text" class="required ws20" name="post[USER_NAME]" value="<?php echo ($info["USER_NAME"]); ?>">
				</p>
				<p style="width: 50%">
					<label>奖项描述：</label>
					<input type="text" class="" name="post[PRIZE_NAME]" value="<?php echo ($info["PRIZE_NAME"]); ?>">
				</p>
				<p style="width: 50%">
					<label>出价：</label>
					<input type="text" class="required ws20" name="post[OFFER]" value="<?php echo ($info["OFFER"]); ?>"/> 元
				</p>
				<p style="width: 50%">
					<label>权重：</label>
					<input type="text" class="required" name="post[WEIGHT]" value="<?php echo ($info["WEIGHT"]); ?>"/> 
				</p>
				<p style="width: 50%">
                    <label>渠道：</label>
                    <select class="combox" name="post[CHANNEL_MAP_ID]">
                    	<option value="">请选择</option>
                        <?php if(is_array($channel)): $i = 0; $__LIST__ = $channel;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if(($info['CHANNEL_MAP_ID'] == $key)): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </p>
				<div class="clear"></div>
				<!-- <p style="width: 100%">
					<label>图片地址：</label>
					<input type="text" style="width: 70%" class="required ws20" name="post[IMAGE_URL]" value="<?php echo ($info["IMAGE_URL"]); ?>"/>
				</p> -->
				<div class="clear"></div>
				<p style="width: 100%">
					<label>访问地址：</label>
					<input type="text" style="width: 70%" class="required ws20" name="post[LINK_URL]" value="<?php echo ($info["LINK_URL"]); ?>"/>
				</p>
			</div>
			<div class="content50">
				<div class="p_img_2" style="width: 60%">
					<label>图片地址：</label>
					<input type="hidden" name="post[IMAGE_URL]" value="<?php echo ($info["IMAGE_URL"]); ?>">
					<div class="upimgbtndiv"  style="float: left">
						<a href="javascript:;" id='IMAGE_URL' class="anniu ch-btn-skin ch-btn-small ch-icon-arrow-up">上 传</a>
					</div>
					<div class="tp_gb">
						<img style="float: left" src="<?php echo ((isset($info["IMAGE_URL"]) && ($info["IMAGE_URL"] !== ""))?($info["IMAGE_URL"]):'../../../Public/home/images/gszp.png'); ?>" width="85" height="50" node-type="pic" onerror="javascript:this.src='../../../Public/home/images/gszp.png'">
						<a href="javascript:;" <?php if(empty($post[IMAGE_URL])): ?>style="display:block;"<?php endif; ?>><img src="/Public/home/images/tp_gb.png" width="18" height="18" node-type="delpic" target_name="post[IMAGE_URL]"></a>
					</div>
				</div>
				<p>
					<label>状态：</label>
					<input type="radio" name="post[STATUS]" value="1" <?php if(empty($post[STATUS])): ?>checked="checked"<?php endif; ?> <?php if(($info["STATUS"] == 1)): ?>checked="checked"<?php endif; ?>/>正常
					<input type="radio" name="post[STATUS]" value="2" <?php if(($info["STATUS"] == 2)): ?>checked="checked"<?php endif; ?>/>暂停
				</p>
			</div>
		</div>
		<div class="formBar">
			<div class="f_r">
				<button class="ch-btn-skin ch-btn-small ch-icon-ok">确 定</button>
				<button class="ch-btn-skin ch-btn-small ch-icon-remove close">取 消</button>
			</div>
		</div>
	</form>	


<script>
		//删除图片
		$('img[node-type="delpic"]').on('click',function(){
			var param1 = $(this).closest('div.p_img_2'),
				param2 = $(this).closest('div.tp_gb'),
				target_name = $(this).attr('target_name');
			param1.find('input[name="'+target_name+'"]').val('');
			param2.find('img[node-type="pic"]').attr('src','../../../Public/home/images/gszp.png');
			$(this).closest('a').hide();
		})
		//删除其他图片
		$('img[node-type="del_other_pic"]').on('click',function(){
			var param1 = $(this).closest('div.p_img_2'),
				param2 = $(this).closest('div.tp_gb'),
				target_name = $(this).attr('target_name');
				param2.find('img[node-type="pic"]').attr('src','../../../Public/home/images/gszp.png');
				$(this).closest('a').hide();
			//param1.find('input[name="'+target_name+'"]').val('');
		 	var img_arr = param1.find('img[node-type="pic"]');
			//拼装图片路径字串
 			var img_url = '';
 			$.each(img_arr, function(i) {
			     var flag = $(this).attr('src');       //这里的this指向每次遍历中Object的当前属性值
			     if(flag == '../../../Public/home/images/gszp.png'){
			     	img_url+=',';
			     }else{
			     	img_url+=','+flag;
			     }
			});
			console.log(img_url);
			var img_path = img_url.substring(1);
			param1.find('input[name="scert[OTHER_PHOTOS]"]').val(img_path);
		})
		var uploadify_onSelectError = function(file, errorCode, errorMsg) {
	        var msgText = "上传失败\n";
	        switch (errorCode) {
	            case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
	                //this.queueData.errorMsg = "每次最多上传 " + this.settings.queueSizeLimit + "个文件";
	                msgText += "每次最多上传 " + this.settings.queueSizeLimit + "个文件";
	                break;
	            case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
	                msgText += "文件大小超过限制( " + this.settings.fileSizeLimit + " )";
	                break;
	            case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
	                msgText += "文件大小为0";
	                break;
	            case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
	                msgText += "文件格式不正确，仅限 " + this.settings.fileTypeExts;
	                break;
	            default:
	                msgText += "错误代码：" + errorCode + "\n" + errorMsg;
	        }
	        alert(msgText);
	    };
		 
		var uploadify_onUploadError = function(file, errorCode, errorMsg, errorString) {
	        // 手工取消不弹出提示
	        if (errorCode == SWFUpload.UPLOAD_ERROR.FILE_CANCELLED
	                || errorCode == SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED) {
	            return;
	        }
	        var msgText = "上传失败\n";
	        switch (errorCode) {
	            case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
	                msgText += "HTTP 错误\n" + errorMsg;
	                break;
	            case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
	                msgText += "上传文件丢失，请重新上传";
	                break;
	            case SWFUpload.UPLOAD_ERROR.IO_ERROR:
	                msgText += "IO错误";
	                break;
	            case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
	                msgText += "安全性错误\n" + errorMsg;
	                break;
	            case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
	                msgText += "每次最多上传 " + this.settings.uploadLimit + "个";
	                break;
	            case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
	                msgText += errorMsg;
	                break;
	            case SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
	                msgText += "找不到指定文件，请重新操作";
	                break;
	            case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
	                msgText += "参数错误";
	                break;
	            default:
	                msgText += "文件:" + file.name + "\n错误码:" + errorCode + "\n"
	                        + errorMsg + "\n" + errorString;
	        }
	        alert(msgText);
	    }
		//图片上传
		$('#REGID').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'auto'	   		: true,
            'multi'       	: false,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'onInit': function () {                        //隐藏进度条
               $("#REGID-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#REGID').closest('div.p_img_2');
		 			param.find('div.tp_gb img[node-type="pic"]').attr('src',resjson.result);
		 			param.find('div.tp_gb img[node-type="pic"]').attr('flag',"1");
		 			param.find('div.tp_gb a').show();
		 			param.find('input[name="scert[REGID_PHOTO]"]').val(resjson.result);
	        }
        });
		$('#ORGID').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'auto'	   		: true,
            'multi'       	: false,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'onInit': function () {                        //隐藏进度条
               $("#ORGID-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#ORGID').closest('div.p_img_2');
		 			param.find('div.tp_gb img[node-type="pic"]').attr('src',resjson.result);
		 			param.find('div.tp_gb a').show();
		 			param.find('input[name="scert[ORGID_PHOTO]"]').val(resjson.result);
	        }
        });
        $('#TAXID').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'auto'	   		: true,
            'multi'       	: false,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'onInit': function () {                        //隐藏进度条
               $("#TAXID-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#TAXID').closest('div.p_img_2');
		 			param.find('div.tp_gb img[node-type="pic"]').attr('src',resjson.result);
		 			param.find('div.tp_gb a').show();
		 			param.find('input[name="scert[TAXID_PHOTO]"]').val(resjson.result);
	        }
        });
        $('#LP_ID').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'hideButton' 	: true,
            'auto'	   		: true,
            'multi'       	: true,
            'queueSizeLimit': 2,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'overrideEvents' : [ 'onDialogClose', 'onUploadError', 'onSelectError' ],
            'onInit': function () {                        //隐藏进度条
               $("#LP_ID-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#LP_ID').closest('div.p_img_2');
		 		var img_arr = param.find('img[node-type="pic"]'),
		 			file_index = 0;
				$.each(img_arr, function(i) {
				     var flag = $(this).attr('src');       //这里的this指向每次遍历中Object的当前属性值
				     if(flag == '../../../Public/home/images/gszp.png'){
				     	file_index = i;
				     	return false; 					//跳出循环
				     }
				});
		 		//file_index = file.index%2;
		 		switch (file_index) {
		 			case 0:
			 			param.find('div[node-type="LP_1"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="LP_1"] a').show();
		 				param.find('input[name="scert[LP_D_PHOTO]"]').val(resjson.result);
		 			break;
		 			case 1:
			 			param.find('div[node-type="LP_2"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="LP_2"] a').show();
			 			param.find('input[name="scert[LP_R_PHOTO]"]').val(resjson.result);
		 			break;
		 		};
	        },
	        'onUploadError' : uploadify_onUploadError,
	        'onSelectError' : uploadify_onSelectError
        });
        $('#BANK_PHOTO').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'auto'	   		: true,
            'multi'       	: false,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'onInit': function () {                        //隐藏进度条
               $("#BANK_PHOTO-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#BANK_PHOTO').closest('div.p_img_2');
		 			param.find('div.tp_gb img[node-type="pic"]').attr('src',resjson.result);
		 			param.find('div.tp_gb a').show();
		 			param.find('input[name="scert[BANK_PHOTO]"]').val(resjson.result);
	        }
        });
		//经营许可证照片
		$('#IMAGE_URL').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'auto'	   		: true,
            'multi'       	: false,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'onInit': function () {                        //隐藏进度条
               $("#IMAGE_URL-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			str = resjson.result.replace('172.19.201.8','zs.yomibank.com'),
		 			// str1 = resjson.result.replace('http://172.19.201.8',''),
		 			param = $('#IMAGE_URL').closest('div.p_img_2');
		 			param.find('div.tp_gb img[node-type="pic"]').attr('src',str);
		 			param.find('div.tp_gb a').show();
		 			param.find('input[name="post[IMAGE_URL]"]').val(str);
	        }
        });


		/* 房屋合同租赁照片 */
  		$('#REGADDR_PHOTO').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'hideButton' 	: true,
            'auto'	   		: true,
            'multi'       	: true,
            'queueSizeLimit': 2,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'overrideEvents' : [ 'onDialogClose', 'onUploadError', 'onSelectError' ],
            'onInit': function () {                        //隐藏进度条
               $("#REGADDR_PHOTO-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#REGADDR_PHOTO').closest('div.p_img_2');
		 			//file_index = file.index%2;
		 		var img_arr = param.find('img[node-type="pic"]'),
		 			file_index = 0;
				$.each(img_arr, function(i) {
				     var flag = $(this).attr('src');       //这里的this指向每次遍历中Object的当前属性值
				     if(flag == '../../../Public/home/images/gszp.png'){
				     	file_index = i;
				     	return false; 					//跳出循环
				     }
				});
		 		switch (file_index) {
		 			case 0:
		 				param.find('div[node-type="REGADDR_1"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="REGADDR_1"] a').show();
		 				param.find('input[name="scert[REGADDR_PHOTO1]"]').val(resjson.result);
		 			break;
		 			case 1:
		 				param.find('div[node-type="REGADDR_2"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="REGADDR_2"] a').show();
			 			param.find('input[name="scert[REGADDR_PHOTO2]"]').val(resjson.result);
		 			break;
		 		};
	        },
	        'onUploadError' : uploadify_onUploadError,
	        'onSelectError' : uploadify_onSelectError
        });
		/* 商户协议照片 */
  		$('#AGREEMENTID_PHOTO').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'hideButton' 	: true,
            'auto'	   		: true,
            'multi'       	: true,
            'queueSizeLimit': 2,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'overrideEvents' : [ 'onDialogClose', 'onUploadError', 'onSelectError' ],
            'onInit': function () {                        //隐藏进度条
               $("#AGREEMENTID_PHOTO-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#AGREEMENTID_PHOTO').closest('div.p_img_2');
		 			//file_index = file.index%2;
		 		var img_arr = param.find('img[node-type="pic"]'),
		 			file_index = 0;
				$.each(img_arr, function(i) {
				     var flag = $(this).attr('src');       //这里的this指向每次遍历中Object的当前属性值
				     if(flag == '../../../Public/home/images/gszp.png'){
				     	file_index = i;
				     	return false; 					//跳出循环
				     }
				});
		 		switch (file_index) {
		 			case 0:
		 				param.find('div[node-type="AGREEMENTID_1"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="AGREEMENTID_1"] a').show();
		 				param.find('input[name="scert[AGREEMENTID_PHOTO1]"]').val(resjson.result);
		 			break;
		 			case 1:
		 				param.find('div[node-type="AGREEMENTID_2"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="AGREEMENTID_2"] a').show();
			 			param.find('input[name="scert[AGREEMENTID_PHOTO2]"]').val(resjson.result);
		 			break;
		 		};
	        },
	        'onUploadError' : uploadify_onUploadError,
	        'onSelectError' : uploadify_onSelectError
        });
		/* 银行卡照片 */
  		$('#BANK_ID').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'hideButton' 	: true,
            'auto'	   		: true,
            'multi'       	: true,
            'queueSizeLimit': 2,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'overrideEvents' : [ 'onDialogClose', 'onUploadError', 'onSelectError' ],
            'onInit': function () {                        //隐藏进度条
               $("#BANK_ID-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#BANK_ID').closest('div.p_img_2');
		 			//file_index = file.index%2;
		 		var img_arr = param.find('img[node-type="pic"]'),
		 			file_index = 0;
				$.each(img_arr, function(i) {
				     var flag = $(this).attr('src');       //这里的this指向每次遍历中Object的当前属性值
				     if(flag == '../../../Public/home/images/gszp.png'){
				     	file_index = i;
				     	return false; 					//跳出循环
				     }
				});
		 		switch (file_index) {
		 			case 0:
		 				param.find('div[node-type="BANK_1"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="BANK_1"] a').show();
		 				param.find('input[name="scert[BANK_D_PHOTO]"]').val(resjson.result);
		 			break;
		 			case 1:
		 				param.find('div[node-type="BANK_2"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="BANK_2"] a').show();
			 			param.find('input[name="scert[BANK_R_PHOTO]"]').val(resjson.result);
		 			break;
		 		};
	        },
	        'onUploadError' : uploadify_onUploadError,
	        'onSelectError' : uploadify_onSelectError
        });
		/* 入账人身份证照片 */
  		$('#BANK_LP_ID').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'hideButton' 	: true,
            'auto'	   		: true,
            'multi'       	: true,
            'queueSizeLimit': 2,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'overrideEvents' : [ 'onDialogClose', 'onUploadError', 'onSelectError' ],
            'onInit': function () {                        //隐藏进度条
               $("#BANK_LP_ID-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#BANK_LP_ID').closest('div.p_img_2');
		 			//file_index = file.index%2;
		 		var img_arr = param.find('img[node-type="pic"]'),
		 			file_index = 0;
				$.each(img_arr, function(i) {
				     var flag = $(this).attr('src');       //这里的this指向每次遍历中Object的当前属性值
				     if(flag == '../../../Public/home/images/gszp.png'){
				     	file_index = i;
				     	return false; 					//跳出循环
				     }
				});
		 		switch (file_index) {
		 			case 0:
		 				param.find('div[node-type="BANK_LP_1"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="BANK_LP_1"] a').show();
		 				param.find('input[name="scert[BANK_LP_D_PHOTO]"]').val(resjson.result);
		 			break;
		 			case 1:
		 				param.find('div[node-type="BANK_LP_2"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="BANK_LP_2"] a').show();
			 			param.find('input[name="scert[BANK_LP_R_PHOTO]"]').val(resjson.result);
		 			break;
		 		};
	        },
	        'onUploadError' : uploadify_onUploadError,
	        'onSelectError' : uploadify_onSelectError
        });
		/* 授权函照片 */
  		$('#GRANTID_PHOTO').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'hideButton' 	: true,
            'auto'	   		: true,
            'multi'       	: true,
            'queueSizeLimit': 2,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'overrideEvents' : [ 'onDialogClose', 'onUploadError', 'onSelectError' ],
            'onInit': function () {                        //隐藏进度条
               $("#GRANTID_PHOTO-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#GRANTID_PHOTO').closest('div.p_img_2');
		 			//file_index = file.index%2;
		 		var img_arr = param.find('img[node-type="pic"]'),
		 			file_index = 0;
				$.each(img_arr, function(i) {
				     var flag = $(this).attr('src');       //这里的this指向每次遍历中Object的当前属性值
				     if(flag == '../../../Public/home/images/gszp.png'){
				     	file_index = i;
				     	return false; 					//跳出循环
				     }
				});
		 		switch (file_index) {
		 			case 0:
		 				param.find('div[node-type="GRANTID_1"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="GRANTID_1"] a').show();
		 				param.find('input[name="scert[GRANTID_PHOTO1]"]').val(resjson.result);
		 			break;
		 			case 1:
		 				param.find('div[node-type="GRANTID_2"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="GRANTID_2"] a').show();
			 			param.find('input[name="scert[GRANTID_PHOTO2]"]').val(resjson.result);
		 			break;
		 		};
	        },
	        'onUploadError' : uploadify_onUploadError,
	        'onSelectError' : uploadify_onSelectError
        });
		/* 法人委托入账授权说明照片 */
 		$('#LP_GRANTID_PHOTO').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'auto'	   		: true,
            'multi'       	: false,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'onInit': function () {                        //隐藏进度条
               $("#LP_GRANTID_PHOTO-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#LP_GRANTID_PHOTO').closest('div.p_img_2');
		 			param.find('div.tp_gb img[node-type="pic"]').attr('src',resjson.result);
		 			param.find('div.tp_gb a').show();
		 			param.find('input[name="scert[LP_GRANTID_PHOTO]"]').val(resjson.result);
	        }
        });
		/* 场所门头照片 */
 		$('#OFFICE_PHOTO1').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'auto'	   		: true,
            'multi'       	: false,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'onInit': function () {                        //隐藏进度条
               $("#OFFICE_PHOTO1-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#OFFICE_PHOTO1').closest('div.p_img_2');
		 			param.find('div.tp_gb img[node-type="pic"]').attr('src',resjson.result);
		 			param.find('div.tp_gb a').show();
		 			param.find('input[name="scert[OFFICE_PHOTO1]"]').val(resjson.result);
	        }
        });

		/* 场所场景照片 */
 		/* 房屋合同租赁照片 */
  		$('#OFFICE_PHOTO').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'hideButton' 	: true,
            'auto'	   		: true,
            'multi'       	: true,
            'queueSizeLimit': 3,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'overrideEvents' : [ 'onDialogClose', 'onUploadError', 'onSelectError' ],
            'onInit': function () {                        //隐藏进度条
               $("#OFFICE_PHOTO-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#OFFICE_PHOTO').closest('div.p_img_2');
		 			param.find('div.tp_gb img[node-type="pic"]').attr('src',resjson.result);
		 			param.find('div.tp_gb a').show();
			 		param.find('input[name="scert[OFFICE_PHOTO2]"]').val(resjson.result);
		 		/*	file_index = file.index%3;
		 		switch (file_index) {
		 			case 0:
			 			param.find('img[id="OFFICE_PHOTO2"]').attr('src',resjson.result);
			 			param.find('input[name="scert[OFFICE_PHOTO2]"]').val(resjson.result);
		 			break;
		 			case 1:
			 			param.find('img[id="OFFICE_PHOTO3"]').attr('src',resjson.result);
			 			param.find('input[name="scert[OFFICE_PHOTO3]"]').val(resjson.result);
		 			break;
		 			case 2:
		 				param.find('img[id="OFFICE_PHOTO4"]').attr('src',resjson.result);
		 				param.find('input[name="scert[OFFICE_PHOTO4]"]').val(resjson.result);
		 			break;
		 		};*/
	        },
	        'onUploadError' : uploadify_onUploadError,
	        'onSelectError' : uploadify_onSelectError
        });
		//收银台照片
		$('#OFFICE_PHOTO3').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'auto'	   		: true,
            'multi'       	: false,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'onInit': function () {                        //隐藏进度条
               $("#OFFICE_PHOTO3-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#OFFICE_PHOTO3').closest('div.p_img_2');
		 			param.find('div.tp_gb img[node-type="pic"]').attr('src',resjson.result);
		 			param.find('div.tp_gb a').show();
		 			param.find('input[name="scert[OFFICE_PHOTO3]"]').val(resjson.result);
	        }
        });
		//室内照片
		$('#OFFICE_PHOTO4').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'auto'	   		: true,
            'multi'       	: false,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'onInit': function () {                        //隐藏进度条
               $("#OFFICE_PHOTO4-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#OFFICE_PHOTO4').closest('div.p_img_2');
		 			param.find('div.tp_gb img[node-type="pic"]').attr('src',resjson.result);
		 			param.find('div.tp_gb a').show();
		 			param.find('input[name="scert[OFFICE_PHOTO4]"]').val(resjson.result);
	        }
        });
		//室内照片
		$('#OFFICE_PHOTO5').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'auto'	   		: true,
            'multi'       	: false,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'onInit': function () {                        //隐藏进度条
               $("#OFFICE_PHOTO5-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#OFFICE_PHOTO5').closest('div.p_img_2');
		 			param.find('div.tp_gb img[node-type="pic"]').attr('src',resjson.result);
		 			param.find('div.tp_gb a').show();
		 			param.find('input[name="scert[OFFICE_PHOTO5]"]').val(resjson.result);
	        }
        });
		//其他
  		$('#OTHER').uploadify({
			'height' 	    : '26px',
            'width'         : '100px', 
        	'formData'      : {
        		'userid'    : SETTING.USER_ID,
				'keyword'   : SETTING.KEYWORD
			},
            'swf'      		: '/Public/dwz/uploadify/scripts/uploadify.swf',    //指定上传控件的主体文件
            'uploader' 		: '/index.php/Home/Public/uploadimg/',    				   //指定服务器端上传处理文件
            //其他配置项
            'buttonText' 	: '上传图片',
            'buttonClass' 	: 'anniu ch-btn-skin ch-btn-small ch-icon-arrow-up uploadimage',
            'hideButton' 	: true,
            'auto'	   		: true,
            'multi'       	: true,
            'queueSizeLimit': 5,
            'fileTypeExts'  : '*.jpg;*.jpeg;*.gif;*.png',
            'fileSizeLimit' : '2MB',
            'overrideEvents' : [ 'onDialogClose', 'onUploadError', 'onSelectError' ],
            'onInit': function () {                        //隐藏进度条
               $("#OTHER-queue").hide();
            },
			'onUploadSuccess' : function(file, data, response) {
		 		var resjson = $.parseJSON(data),
		 			param = $('#OTHER').closest('div.p_img_2');
		 		var img_arr = param.find('img[node-type="pic"]'),
		 			file_index = 0;
				$.each(img_arr, function(i) {
				     var flag = $(this).attr('src');       //这里的this指向每次遍历中Object的当前属性值
				     if(flag == '../../../Public/home/images/gszp.png'){
				     	file_index = i;
				     	return false; 					//跳出循环
				     }
				});
		 		//file_index = file.index%2;
		 		switch (file_index) {
		 			case 0:
			 			param.find('div[node-type="OTHER_1"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="OTHER_1"] a').show();
		 			break;
		 			case 1:
		 				param.find('div[node-type="OTHER_2"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="OTHER_2"] a').show();
		 			break;
		 			case 2:
			 			param.find('div[node-type="OTHER_3"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="OTHER_3"] a').show();
		 			break;
		 			case 3:
			 			param.find('div[node-type="OTHER_4"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="OTHER_4"] a').show();
		 			break;
		 			case 4:
			 			param.find('div[node-type="OTHER_5"] img[node-type="pic"]').attr('src',resjson.result);
			 			param.find('div[node-type="OTHER_5"] a').show();
		 				
		 			break;
		 		};
		 		//拼装图片路径字串
	 			var img_url = '';
	 			$.each(img_arr, function(i) {
				     var flag = $(this).attr('src');       //这里的this指向每次遍历中Object的当前属性值
				     if(flag == '../../../Public/home/images/gszp.png'){
				     	img_url+=',';
				     }else{
				     	img_url+=','+flag;
				     }
				});
				var img_path = img_url.substring(1);
				param.find('input[name="scert[OTHER_PHOTOS]"]').val(img_path);
	        },
	        'onUploadError' : uploadify_onUploadError,
	        'onSelectError' : uploadify_onSelectError
        });
	</script>
</div>