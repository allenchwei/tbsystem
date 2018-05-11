<?php if (!defined('THINK_PATH')) exit();?><div class="pageContent">
	<form action="/index.php/Home/System/menu_edit/navTabId/System" method="post" name="form" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone)">
	<input type="hidden" name="post[submit]" value="menu_edit">
	<?php if(($info["MENU_ID"]) > "0"): ?><input type="hidden" name="post[MENU_ID]" value="<?php echo ($info["MENU_ID"]); ?>"><?php endif; ?>
		<div class="pageFormContent" layoutH="<?php echo (C("tk_max_add_lay")); ?>">
			<div class="content100">
				<p>						
					<label>上级菜单：</label>				
					<select name="post[MENU_PID]">
						<?php echo ($menu_select); ?>
					</select>
				</p>
				<p>
					<label>菜单名称：</label>
					<input type="text" value="<?php echo ($info["MENU_TITLE"]); ?>" class="required ws45" name="post[MENU_TITLE]" maxlength="20"/>
				</p>					
				<p>
					<label>节点类型：</label>				
					<select name="post[MENU_LEVEL]">
						<option value="2" <?php if(($info["MENU_LEVEL"]) == "2"): ?>selected=""<?php endif; ?> >模块</option>
						<option value="3" <?php if(($info["MENU_LEVEL"]) == "3"): ?>selected=""<?php endif; ?> >方法</option>
						<option value="0" <?php if(($info["MENU_LEVEL"]) == "0"): ?>selected=""<?php endif; ?> >非节点</option>
					</select>
				</p>
				<p>
					<label>菜单类型：</label>										
					<select name="post[MENU_DISPLAY]">
						<option value="1" <?php if(($info["MENU_DISPLAY"]) == "1"): ?>selected=""<?php endif; ?> >主菜单</option>
						<option value="2" <?php if(($info["MENU_DISPLAY"]) == "2"): ?>selected=""<?php endif; ?> >子菜单</option>
						<option value="0" <?php if(($info["MENU_DISPLAY"]) == "0"): ?>selected=""<?php endif; ?> >不显示</option>
					</select>
				</p>
				<p>
					<label>节点名称：</label>				
					<input type="text" value="<?php echo ($info["MENU_NAME"]); ?>" name="post[MENU_NAME]" maxlength="20">
				</p>					
				<p>
					<label>链接参数：</label>					
					<textarea class="ws60" name="post[MENU_DATA]" style="height:165px;" ><?php echo ($info["MENU_DATA"]); ?></textarea>
				</p>						
				<p>
					<label>节点状态：</label>
					<input type="radio" class="radio" value="1" name="post[MENU_STATUS]" <?php if(($info['MENU_STATUS'] == 1) OR ($info['MENU_STATUS'] == '') ): ?>checked<?php endif; ?>>
					启用
					<input type="radio" class="radio" value="0" name="post[MENU_STATUS]" <?php if(($info["MENU_STATUS"]) == "0"): ?>checked<?php endif; ?>>
					关闭
				</p>
				<p>
					<label>备注说明：</label>
					<input type="text" value="<?php echo ($info["MENU_REMARK"]); ?>" class="ws60" name="post[MENU_REMARK]"/>
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