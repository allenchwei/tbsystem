<?php if (!defined('THINK_PATH')) exit();?><div class="pageContent">
	<form action="/index.php/Home/Index/user_uppwd/navTabId/Index" method="post" name="form" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone)">
		<input type="hidden" name="post[submit]" value="user_uppwd">
		<div class="pageFormContent" layoutH="<?php echo (C("tk_max_add_lay")); ?>">
			<div class="content100">
				<p>
					<label>原始密码：</label>
					<input type="password" value="" class="ws18 required" name="post[OLD_PASSWD]"/>
				</p>
				<p>
					<label>新密码：</label>
					<input type="password" value="" class="ws18 required" name="post[USER_PASSWD]"/>
				</p>
				<p>
					<label>确认密码：</label>
					<input type="password" value="" class="ws18 required" name="post[NEW_PASSWD]"/>
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