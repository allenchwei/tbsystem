<?php if (!defined('THINK_PATH')) exit();?><form id="pagerForm" method="post" action="">
	<input type="hidden" name="pageNum" value="1" />
	<input type="hidden" name="post[submit]" value="user"/>
	<input type="hidden" name="post[USER_LEVEL]" value="<?php echo ($postdata["USER_LEVEL"]); ?>"/>
	<input type="hidden" name="post[USER_MOBILE]" value="<?php echo ($postdata["USER_MOBILE"]); ?>"/>
	<input type="hidden" name="post[USER_NO]" value="<?php echo ($postdata["USER_NO"]); ?>"/>
	<input type="hidden" name="post[BRANCH_MAP_ID]" value="<?php echo ($postdata["BRANCH_MAP_ID"]); ?>"/>
	<input type="hidden" name="post[PARTNER_MAP_ID]" value="<?php echo ($postdata["PARTNER_MAP_ID"]); ?>"/>
</form>
<div class="pageHeader">
	<form method="post" action="/index.php/Home/System/user/navTabId/System" onsubmit="return navTabSearch(this);">
		<input type="hidden" name="post[submit]" value="user">
		<div class="searchBar">
			<div class="header">
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><p>
					<label>用户级别：</label>
					<select class="combox" name="post[USER_LEVEL]" node-type="partnerlv">
						<option value="">请选择</option>
						<?php if(is_array($user_level)): $i = 0; $__LIST__ = $user_level;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if(($key) >= $_SESSION['HOME']['USER_LEVEL']): ?><option value="<?php echo ($key); ?>" <?php if(($postdata['USER_LEVEL'] == $key) and ($postdata['USER_LEVEL'] != '')): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p><?php endif; ?>
				<p>
					<label>用户手机：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["USER_MOBILE"]); ?>" name="post[USER_MOBILE]" maxlength="11" onkeyup="value=value.replace(/[^\d]/g,'')">
				</p>
				<p>
					<label>用户工号：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["USER_NO"]); ?>" name="post[USER_NO]" maxlength="11" onkeyup="value=value.replace(/[^\d]/g,'')">
				</p>
				<div class="clear"></div>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><p class="maxcombox4">
					<label>用户归属：</label>
					<span class="f_l" note-type="guishu" <?php if(($postdata['USER_LEVEL'] == 0) and ($postdata['USER_LEVEL'] != '')): ?>style="display:none;"<?php else: ?>style="display:block;"<?php endif; ?>>

					<?php if(($home['USER_LEVEL'] == 0) and ($home['USER_LEVEL'] != '')): echo get_level_sel($postdata['BRANCH_MAP_ID'],'-1','plv[]',$postdata['PARTNER_MAP_ID'],5,1);?>
						<?php else: ?>
						<?php echo get_level_sel($postdata['BRANCH_MAP_ID'],'-1','plv[]',$postdata['PARTNER_MAP_ID'],$home['USER_LEVEL']-1); endif; ?>
					
					
					</span>
				</p><?php endif; ?>
				<div class="clear"></div>
				<script type="text/javascript">
					$('select[node-type="partnerlv"]').on('change',function(){
						var val = $(this).val();
						if(val==0 && val!=''){
							$('span[note-type="guishu"]').hide();
						}else{
							$('span[note-type="guishu"]').show();
							var param = $('select[name="plv[]"]').closest('div.combox');
							param.nextAll('div.combox').remove();
							$('select[name="plv[]"]').attr('maxlv', val-1);
							$('select.sellv').trigger("change");
						}
					});
				</script>
			</div>
			<div class="hbtn">
				<button class="ch-btn-skin ch-btn-small ch-icon-search">查 询</button>
				<a rel="System" target="navTab" href="/index.php/Home/System/user" res_title="false"><button class="ch-btn-skin ch-btn-small ch-icon-refresh">重 置</button></a>
			</div>
		</div>
	</form>
</div>
<div class="pageContent">
	<div class="panelBar selbutton">
		<?php echo getaction_select(System,user);?>
	</div>
	<table class="table" width="100%" layoutH="175">
		<thead>
			<tr>
				<th width="15%" >姓名</th>
				<th width="10%" >用户工号</th>
				<th width="10%" align='center'>手机号</th>
				<th width="35%" >用户归属</th>
				<th width="10%" >角色</th>
				<th width="6%" align='center'>状态</th>
				<th width="14%" align='center'>创建日期</th>
			</tr>
		</thead>
		<tbody>
			<?php if(is_array($list)): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?><tr target="sid_target" rel="<?php echo ($vo["USER_ID"]); ?>" class="<?php if($k%2 == 1): ?>bg<?php endif; ?>">
					<td><?php echo ($vo["USER_NAME"]); ?></td>
					<td><?php echo ($vo["USER_NO"]); ?></td>
					<td><?php echo ($vo["USER_MOBILE"]); ?></td>
					<td><?php echo (get_branch_name($vo["BRANCH_MAP_ID"],$vo['PARTNER_MAP_ID'])); ?></td>
					<td><?php echo ($vo["ROLE_NAME"]); ?></td>
					<td><?php echo ($user_status[$vo[USER_STATUS]]); ?></td>
					<td><?php echo ($vo["CREATE_TIME"]); ?></td>
				</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		</tbody>
	</table>
	<div class="panelBar">
		<div class="pages"><span>共 <?php echo ((isset($totalCount) && ($totalCount !== ""))?($totalCount):"0"); ?> 条</span></div>
		<div class="pagination" targetType="navTab" totalCount="<?php echo ($totalCount); ?>" numPerPage="<?php echo ($numPerPage); ?>" pageNumShown="10" currentPage="<?php echo ($currentPage); ?>"></div>
	</div>
</div>