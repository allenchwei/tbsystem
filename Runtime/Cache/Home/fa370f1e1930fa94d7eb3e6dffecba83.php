<?php if (!defined('THINK_PATH')) exit();?><form id="pagerForm" method="post" action="">
	<input type="hidden" name="pageNum" value="1" />
	<input type="hidden" name="post[submit]" value="channel"/>
	<input type="hidden" name="post[SHOP_MAP_ID]" value="<?php echo ($postdata["SHOP_MAP_ID"]); ?>"/>
</form>
<div class="pageHeader">
	<form method="post" action="<?php echo U('/Home/Daikuan/channel');?>" onsubmit="return navTabSearch(this);">
		<input type="hidden" name="post[submit]" value="channel">
		<div class="searchBar">
			<div class="header">
				<!-- <?php if($home['CHANNEL_MAP_ID'] == 0): ?>-->
					<p>
	                    <label>渠道：</label>
	                    <select class="combox" name="post[CHANNEL_MAP_ID]">
	                    	<option value="">请选择</option>
	                        <?php if(is_array($channel)): $i = 0; $__LIST__ = $channel;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if(($postdata['CHANNEL_MAP_ID'] == $key)): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
	                    </select>
	                </p>
				<!--<?php endif; ?> -->
				<p style="width: 50%">
					<label>渠道名称：</label>
					<input type="text" name="post[CHANNEL_NAME]" value="<?php echo ($info["CHANNEL_NAME"]); ?>"/>
				</p>
				<div class="clear"></div>
			</div>
			<div class="hbtn">
				<button class="ch-btn-skin ch-btn-small ch-icon-search">查 询</button>
				<a rel="Daikuan" target="navTab" href="/index.php/Home/Daikuan/channel" res_title="false"><button class="ch-btn-skin ch-btn-small ch-icon-refresh">重 置</button></a>
			</div>
		</div>
	</form>
</div>
<div class="pageContent">
	<div class="panelBar selbutton">
		<?php echo getaction_select(Daikuan,channel);?>
	</div>
	<table class="table" width="100%" layoutH="140">
		<thead>
			<tr>
				<th width="2%" align='center'>渠道号</th>
				<th width="2%" align='center'>加密号</th>
				<th width="10%" align='center'>渠道名称</th>
				<th width="10%" align='center'>背景图片</th>
				<th width="10%" align='center'>转盘次数</th>
				<th width="10%" align='center'>状态</th>
			</tr>
		</thead>
		<tbody>
			<?php if(is_array($list)): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?><tr target="sid_target" rel="<?php echo ($vo["CHANNEL_MAP_ID"]); ?>">
					<td><?php echo ($vo["CHANNEL_MAP_ID"]); ?></td>
					<td><?php echo ($vo["CHANNEL_KEY"]); ?></td>
					<td><?php echo ($vo["CHANNEL_NAME"]); ?></td>
					<td><img src="<?php echo ($vo["IMAGE_URL"]); ?>" width="20%"/></td>
					<td><?php echo ($vo["TIME"]); ?></td>
					<td><?php echo ($status[$vo[CHANNEL_STATUS]]); ?></td>
				</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		</tbody>
	</table>
	<div class="panelBar">
		<div class="pages"><span>共 <?php echo ((isset($totalCount) && ($totalCount !== ""))?($totalCount):"0"); ?> 条</span></div>
		<div class="pagination" targetType="navTab" totalCount="<?php echo ($totalCount); ?>" numPerPage="<?php echo ($numPerPage); ?>" pageNumShown="10" currentPage="<?php echo ($currentPage); ?>"></div>
	</div>
</div>