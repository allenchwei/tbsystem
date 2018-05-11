<?php if (!defined('THINK_PATH')) exit();?><form id="pagerForm" method="post" action="">
	<input type="hidden" name="pageNum" value="1" />
	<input type="hidden" name="post[submit]" value="scert"/>
	<input type="hidden" name="post[bid]" value="<?php echo ($postdata["bid"]); ?>"/>
	<input type="hidden" name="post[pid]" value="<?php echo ($postdata["pid"]); ?>"/>
	<input type="hidden" name="post[SHOP_STATUS]" value="<?php echo ($postdata["SHOP_STATUS"]); ?>"/>
	<input type="hidden" name="post[MCC_TYPE]" value="<?php echo ($postdata["MCC_TYPE"]); ?>"/>
	<input type="hidden" name="post[MCC_CODE]" value="<?php echo ($postdata["MCC_CODE"]); ?>"/>
	<input type="hidden" name="post[POS_STATUS]" value="<?php echo ($postdata["POS_STATUS"]); ?>"/>
	<input type="hidden" name="post[SHOP_NO]" value="<?php echo ($postdata["SHOP_NO"]); ?>"/>
	<input type="hidden" name="post[SHOP_NAME]" value="<?php echo ($postdata["SHOP_NAME"]); ?>"/>
	<input type="hidden" id="exportdata" value="<?php echo ($exportdata); ?>"/><!-- Excel导出 -->
</form>
<div class="pageHeader">
	<form method="post" action="<?php echo U('/Home/Shop/scert');?>" onsubmit="return navTabSearch(this);">
		<input type="hidden" name="post[submit]" value="scert">
		<div class="searchBar">
			<div class="header">
				<p class="maxcombox">
					<label>商户名称：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["SHOP_NAME"]); ?>" name="post[SHOP_NAME]">
				</p>
				<p class="maxcombox">
					<label>&nbsp;商户号：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["SHOP_NO"]); ?>" name="post[SHOP_NO]">
				</p>
				<p class="maxcombox3">
					<label>商户状态：</label>
					<select class="combox" name="post[SHOP_STATUS]">
						<option value="">请选择</option>
						<?php if(is_array($shop_status_check)): $i = 0; $__LIST__ = $shop_status_check;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if(($postdata["SHOP_STATUS"] == $key) and ($postdata["SHOP_STATUS"] != '')): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p>
				<div class="clear"></div>
				<p class="maxcombox">
					<label style="width: 60px;">MCC类：</label>
					<?php echo getmcc_select($postdata['MCC_TYPE'],$postdata['MCC_CODE'],'post[MCC_TYPE]','post[MCC_CODE]');?>
				</p>
				<div class="clear"></div>
				<p class="maxcombox">
					<label style="width: 60px;">归属：</label>
					<?php echo get_level_sel($postdata['bid'],'-1','soplv[]',$postdata['pid'],2);?>
				</p>
				<div class="clear"></div>
			</div>
			<div class="hbtn">
				<button class="ch-btn-skin ch-btn-small ch-icon-search">查 询</button>
				<a rel="Shop" target="navTab" href="/index.php/Home/Shop/scert" res_title="false"><button class="ch-btn-skin ch-btn-small ch-icon-refresh">重 置</button></a>
			</div>
		</div>
	</form>
</div>
<div class="pageContent">
	<div class="panelBar selbutton">
		<?php echo getaction_select(Shop,scert);?>
	</div>
	<table class="table" width="100%" layoutH="209">
		<thead>
			<tr>
				<th width="12%" align="center">商户号</th>
				<th width="16%">商户名称</th>
				<th width="16%" >所在城市</th>
				<th width="12%" align='center'>变更日期</th>
				<th width="12%" align="center">状态</th>
				<th width="16%">归属</th>
				<th width="16%" >归属集团商户</th>
			</tr>
		</thead>
		<tbody>
			<?php if(is_array($list)): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?><tr target="sid_target" rel="<?php echo ($vo["SHOP_MAP_ID"]); ?>_<?php if(($vo["TMP_ID"]) > "0"): ?>1<?php else: ?>0<?php endif; ?>" class="<?php if($k%2 == 1): ?>bg<?php endif; ?>">
					<td><?php echo ($vo["SHOP_NO"]); ?></td>
					<td><?php echo ($vo["SHOP_NAME"]); ?></td>
					<td><?php echo (getcity_name($vo["CITY_NO"])); ?></td>
					<td><?php echo ($vo["CREATE_TIME"]); ?></td>
					<td><?php if(($vo["TMP_ID"]) > "0"): ?>有更新【<?php echo ($shop_status_check[$vo['TMP_STATUS']]); ?>】<?php else: ?> <?php echo ($shop_status[$vo['SHOP_STATUS']]); endif; ?></td>
					<td><?php echo ((isset($vo["PARTNER_NAME"]) && ($vo["PARTNER_NAME"] !== ""))?($vo["PARTNER_NAME"]):"暂无"); ?></td>
					<td><?php echo (get_shopp_name($vo["SHOP_MAP_ID_P"])); ?></td>
				</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		</tbody>
	</table>
	<div class="panelBar">
		<div class="pages"><span>共 <?php echo ((isset($totalCount) && ($totalCount !== ""))?($totalCount):"0"); ?> 条</span></div>
		<div class="pagination" targetType="navTab" totalCount="<?php echo ($totalCount); ?>" numPerPage="<?php echo ($numPerPage); ?>" pageNumShown="10" currentPage="<?php echo ($currentPage); ?>"></div>
	</div>
</div>