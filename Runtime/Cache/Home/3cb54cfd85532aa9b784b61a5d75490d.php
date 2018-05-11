<?php if (!defined('THINK_PATH')) exit();?><div class="pageContent">
	<form action="/index.php/Home/System/role_edit/navTabId/System" method="post" name="form" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone)">
		<input type="hidden" name="post[submit]" value="role_edit">
		<?php if(($info["ROLE_ID"]) > "0"): ?><input type="hidden" name="post[ROLE_ID]" value="<?php echo ($info["ROLE_ID"]); ?>"><?php endif; ?>
		<div class="pageFormContent" layoutH="<?php echo (C("tk_max_add_lay")); ?>">
			<div class="content100">
				<p>
					<label>角色名称：</label>
					<input type="text" value="<?php echo ($info["ROLE_NAME"]); ?>" class="required ws26" name="post[ROLE_NAME]" maxlength="20"/>
				</p>
				<p>
					<label>角色级别：</label>
					<select class="combox" name="post[ROLE_LEVEL]">
						<?php if(is_array($role_level)): $i = 0; $__LIST__ = $role_level;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if(($info['ROLE_LEVEL'] == $key) and ($info['ROLE_LEVEL'] != '')): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p>
				<p>
					<label>节点状态：</label>				
					<input type="radio" class="radio" value="1" name="post[ROLE_STATUS]" <?php if(($info['ROLE_STATUS'] == 1) OR ($info['ROLE_STATUS'] == '') ): ?>checked<?php endif; ?>>
					启用
					<input type="radio" class="radio" value="0" name="post[ROLE_STATUS]" <?php if(($info["ROLE_STATUS"]) == "0"): ?>checked<?php endif; ?>>
					关闭
				</p>
				<p>
					<label>描述：</label>				
					<textarea style="width:480px;height:100px;" name="post[ROLE_REMARK]" maxlength="50"><?php echo ($info["ROLE_REMARK"]); ?></textarea>
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
</div>