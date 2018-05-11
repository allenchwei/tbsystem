<?php if (!defined('THINK_PATH')) exit();?><div class="pageContent">
	<form action="/index.php/Home/System/user_edit/navTabId/System" method="post" name="form" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone)">
		<input type="hidden" name="post[submit]" value="user_edit">
		<input type="hidden" name="post[USER_LEVEL]" value="<?php echo ($home['USER_LEVEL']); ?>">
		<?php if(($info["USER_ID"]) > "0"): ?><input type="hidden" name="post[USER_ID]" value="<?php echo ($info["USER_ID"]); ?>"><?php endif; ?>
		<div class="pageFormContent" layoutH="<?php echo (C("tk_max_add_lay")); ?>">
			<div class="content100">
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><p>
					<label>用户级别：</label>
					<span class="show"><?php echo C('USER_LEVEL')[$home['USER_LEVEL']];?></span>
					<input type="hidden" name="post[USER_LEVEL]" value="<?php echo ($home['USER_LEVEL']); ?>">
				</p>
				<p class="maxcombox5">
					<label>用户归属：</label>
					<span class="show"><?php echo (get_level_name($home["PARTNER_MAP_ID"],$home['BRANCH_MAP_ID'])); ?></span>
					<input type="hidden" name="post[BRANCH_MAP_ID]"  value="<?php echo ($home['BRANCH_MAP_ID']); ?>">
					<input type="hidden" name="post[PARTNER_MAP_ID]" value="<?php echo ($home['PARTNER_MAP_ID']); ?>">
				</p><?php endif; ?>
				<p>
					<label>角色渠道：</label>
					<select class="combox" name="post[CHANNEL_MAP_ID]">
						<option value="0">请选择</option>
						<?php if(is_array($channel_list)): $i = 0; $__LIST__ = $channel_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["CHANNEL_MAP_ID"]); ?>" <?php if($info['CHANNEL_MAP_ID'] == $vo['CHANNEL_MAP_ID']): ?>selected<?php endif; ?>><?php echo ($vo["CHANNEL_NAME"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p>
				<p>
					<label>角色名称：</label><!-- 
					<span class="show"><?php echo ($home['ROLE_NAME']); ?></span>
					<input type="hidden" name="post[ROLE_ID]" value="<?php echo ($home['ROLE_ID']); ?>">
 -->
					<select class="combox" name="post[ROLE_ID]">
						<option value="">请选择</option>
						<?php if(is_array($role_list)): $i = 0; $__LIST__ = $role_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["ROLE_ID"]); ?>" <?php if($info['ROLE_ID'] == $vo['ROLE_ID']): ?>selected<?php endif; ?>><?php echo ($vo["ROLE_NAME"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p>
				<?php if(((user_edit == user_edit) OR (user_edit == user_add)) && $home['CHANNEL_MAP_ID'] != 0 && $home['ROLE_NAME'] == OEM): ?><p>
					<label>用户工号：</label>
					<input type="text" readonly="true" value="<?php echo ($info["USER_NO"]); ?>" class="digits required" name="post[USER_NO]" maxlength="3"/>
				</p>
				<?php else: ?>
				<p>
					<label>用户工号：</label>
					<input type="text" value="<?php echo ($info["USER_NO"]); ?>" class="digits required" name="post[USER_NO]" maxlength="3"/>
					<span class="message">（3位数，登录工号为：<font color="red">用户归属ID号</font> + <font color="red">输入的三位数字</font>）</span>
				</p><?php endif; ?>
				<p>
					<label>用户名称：</label>
					<input type="text" value="<?php echo ($info["USER_NAME"]); ?>" class="required" name="post[USER_NAME]" maxlength="8"/>
					<?php if(((user_edit == user_edit) OR (user_edit == user_add)) && $home['CHANNEL_MAP_ID'] != 0 && $home['ROLE_NAME'] == OEM): ?><span class="message"><font color="red"></font></span><?php endif; ?>
				</p>
				<p>
					<label>用户状态：</label>
					<select class="combox" name="post[USER_STATUS]">
						<option value="">请选择</option>
						<?php if(is_array($user_status)): $i = 0; $__LIST__ = $user_status;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if($info['USER_STATUS'] == $key): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p>
				<p>
					<label>用户手机：</label>
					<input type="text" value="<?php echo ($info["USER_MOBILE"]); ?>" class="required digits" name="post[USER_MOBILE]" title="请规范输入用户手机" maxlength="11"/>
				</p>
				<p>
					<label>用户邮箱：</label>
					<input type="text" value="<?php echo ($info["EMAIL"]); ?>" name="post[EMAIL]" class="email_auto ws23"/>
				</p>
				<p>
					<label>　</label>
					<font color="red">注：初始密码为 手机号的后6位</font>
				</p>
				<div class="clear"></div>
			</div>
		</div>
		<div class="formBar">
			<div class="f_r">
				<button class="ch-btn-skin ch-btn-small ch-icon-ok">确 定</button>
				<button class="ch-btn-skin ch-btn-small ch-icon-remove close">取 消</button>
			</div>
		</div>
	</form>
	<script type="text/javascript">
		//通过级别返回下拉级别
		$('select[node-type="partnerlv"]').on('change',function(){
			var val = $(this).val();
			if(val == 0){
				$('span[note-type="guishu"]').hide();
			}else{
				$('span[note-type="guishu"]').show();
				$('select[name="plv[]"]').attr('maxlv', val-1);
				$('select.sellv').trigger("change");
			}
		});
		$(".email_auto").mailAutoComplete({
			boxClass: "out_box", //外部box样式
			listClass: "list_box", //默认的列表样式
			focusClass: "focus_box", //列表选样式中
			markCalss: "mark_box", //高亮样式
			autoClass: false,
			textHint: true //提示文字自动隐藏
		});
	</script>
</div>