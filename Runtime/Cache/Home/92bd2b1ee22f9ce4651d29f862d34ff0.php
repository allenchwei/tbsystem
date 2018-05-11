<?php if (!defined('THINK_PATH')) exit();?><form id="pagerForm" method="post" action="">
	<input type="hidden" name="pageNum" value="1" />
	<input type="hidden" name="post[submit]" value="role"/>
	<input type="hidden" name="post[ROLE_LEVEL]" value="<?php echo ($postdata["ROLE_LEVEL"]); ?>"/>
</form>
<div class="pageHeader">
	<form method="post" action="/index.php/Home/System/role/navTabId/System" onsubmit="return navTabSearch(this);">
		<input type="hidden" name="post[submit]" value="role">
		<div class="searchBar">
			<div class="header">
				<p>
					<label>角色名称：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["ROLE_NAME"]); ?>" name="post[ROLE_NAME]" maxlength="20">
				</p>
				<p>
					<label>角色级别：</label>
					<select class="combox" name="post[ROLE_LEVEL]">
						<option value="">请选择</option>
						<?php if(is_array($role_level)): $i = 0; $__LIST__ = $role_level;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if(($postdata['ROLE_LEVEL'] == $key) and ($postdata['ROLE_LEVEL'] != '')): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p>
				<div class="clear"></div>
			</div>
			<div class="hbtn">
				<button class="ch-btn-skin ch-btn-small ch-icon-search">查 询</button>
				<a rel="System" target="navTab" href="/index.php/Home/System/role" res_title="false"><button class="ch-btn-skin ch-btn-small ch-icon-refresh">重 置</button></a>
			</div>
		</div>
	</form>
</div>
<div class="pageContent">
	<div class="panelBar selbutton">
		<?php echo getaction_select(System,role);?>
	</div>
	<table class="table" width="100%" layoutH="141">
		<thead>
			<tr>
				<th width="8%" align='center'>角色ID</th>
				<th width="32%">角色名称</th>
				<th width="14%" align='left'>角色级别</th>
				<th width="10%" align='center'>角色状态</th>
				<th width="36%">描述</th>
			</tr>
		</thead>
		<tbody>
			<?php if(is_array($list)): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?><tr target="sid_target" rel="<?php echo ($vo["ROLE_ID"]); ?>" class="<?php if($k%2 == 1): ?>bg<?php endif; ?>">
					<td><?php echo ($vo["ROLE_ID"]); ?></td>
					<td><?php echo ($vo["ROLE_NAME"]); ?></td>
					<td><?php echo ($role_level[$vo[ROLE_LEVEL]]); ?></td>
					<td><?php if(($vo["ROLE_STATUS"]) == "1"): ?><font color="red" class="ju">√</font><?php else: ?><font color="blue" class="ju">×</font><?php endif; ?></td>
					<td><?php echo ($vo["ROLE_REMARK"]); ?></td>
				</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		</tbody>
	</table>
	<div class="panelBar">
		<div class="pages"><span>共 <?php echo ((isset($totalCount) && ($totalCount !== ""))?($totalCount):"0"); ?> 条</span></div>
		<div class="pagination" targetType="navTab" totalCount="<?php echo ($totalCount); ?>" numPerPage="<?php echo ($numPerPage); ?>" pageNumShown="10" currentPage="<?php echo ($currentPage); ?>"></div>
	</div>
</div>