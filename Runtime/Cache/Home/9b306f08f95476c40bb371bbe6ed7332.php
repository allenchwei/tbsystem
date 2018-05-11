<?php if (!defined('THINK_PATH')) exit();?><div class="pageHeader"></div>
<div class="pageContent">
	<div class="panelBar selbutton">
		<a href="<?php echo U('/Home/System/menu_add/navTabId/System');?>" width="<?php echo (C("tk_max_w")); ?>" height="<?php echo (C("tk_max_h")); ?>" target="dialog" mask="true"><button class="ch-btn-skin ch-btn-small ch-icon-plus">添 加</button></a>
		<a href="javascript:;" onclick="$('#menu_sort').submit();"><button class="ch-btn-skin ch-btn-small ch-icon-wrench">排 序</button></a>
	</div>
	<form id="menu_sort" action="<?php echo U('/Home/System/menu_sort/navTabId/System');?>" method="post" name="form" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone)">
		<table class="table" width="100%" layoutH="81">
			<thead>
				<tr>
					<th width="8%" align='center'></th>
					<th width="25%" >菜单名称</th>
					<th width="9%" >节点</th>
					<th width="25%" >动作</th>
					<th width="5%" align='center'>状态</th>
					<th width="5%" align='center'>类型</th>
					<th width="5%" align='center'>显示</th>
					<th width="18%" align='center'>操作</th>
				</tr>
			</thead>
			<tbody><?php echo ($html_tree); ?></tbody>
		</table>
	</form>
</div>