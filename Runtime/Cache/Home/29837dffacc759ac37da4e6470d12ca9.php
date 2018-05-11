<?php if (!defined('THINK_PATH')) exit();?><form id="pagerForm" method="post" action="">
	<input type="hidden" name="pageNum" value="1" />
	<input type="hidden" name="post[submit]" value="shop"/>
	<input type="hidden" name="post[bid]" value="<?php echo ($postdata["bid"]); ?>"/>
	<input type="hidden" name="post[pid]" value="<?php echo ($postdata["pid"]); ?>"/>
	<input type="hidden" name="post[SHOP_STATUS]" value="<?php echo ($postdata["SHOP_STATUS"]); ?>"/>
	<input type="hidden" name="post[MCC_TYPE]" value="<?php echo ($postdata["MCC_TYPE"]); ?>"/>
	<input type="hidden" name="post[MCC_CODE]" value="<?php echo ($postdata["MCC_CODE"]); ?>"/>
	<input type="hidden" name="post[POS_STATUS]" value="<?php echo ($postdata["POS_STATUS"]); ?>"/>
	<input type="hidden" name="post[SHOP_NO]" value="<?php echo ($postdata["SHOP_NO"]); ?>"/>
	<input type="hidden" name="post[SHOP_NAME]" value="<?php echo ($postdata["SHOP_NAME"]); ?>"/>
	<input type="hidden" name="post[SHOP_MAP_ID]" value="<?php echo ($postdata["SHOP_MAP_ID"]); ?>"/>
	<input type="hidden" id="exportdata" value="<?php echo ($exportdata); ?>"/><!-- Excel导出 -->
</form>
<div class="pageHeader">
	<form method="post" action="<?php echo U('/Home/Shop/shop');?>" onsubmit="return navTabSearch(this);">
		<input type="hidden" name="post[submit]" value="shop">
		<div class="searchBar">
			<div class="header">
				<p class="maxcombox">
					<label>商户名称：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["SHOP_NAME"]); ?>" name="post[SHOP_NAME]">
				</p>
				<!--<p class="maxcombox">
					<label>商户简称：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["SHOP_NAMEABCN"]); ?>" name="post[SHOP_NAMEABCN]">
				</p>-->
				<p class="maxcombox">
					<label>&nbsp;商户号：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["SHOP_NO"]); ?>" name="post[SHOP_NO]">
				</p>
				<p class="maxcombox">
					<label>商户身份证号：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["LP_ID"]); ?>" name="post[LP_ID]">
				</p>
				<div class="clear"></div>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><p class="maxcombox">
					<label style="width: 60px;">MCC类：</label>
					<?php echo getmcc_select($postdata['MCC_TYPE'],$postdata['MCC_CODE'],'post[MCC_TYPE]','post[MCC_CODE]');?>
				</p><?php endif; ?>
				<p class="maxcombox3">
					<label>商户状态：</label>
					<select class="combox" name="post[SHOP_STATUS]">
						<option value="">请选择</option>
						<?php if(is_array($shop_status)): $i = 0; $__LIST__ = $shop_status;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if(($postdata["SHOP_STATUS"] == $key) and ($postdata["SHOP_STATUS"] != '')): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p>
				<p class="maxcombox">
					<label>&nbsp;商户ID：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["SHOP_MAP_ID"]); ?>" name="post[SHOP_MAP_ID]">
				</p>
				<div class="clear"></div>
				<p class="maxcombox">
					<label>商户手机：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["MOBILE"]); ?>" name="post[MOBILE]">
				</p>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><p class="maxcombox">
						<?php if($home['USER_LEVEL'] == 4): ?><label style="width: 60px;">备注：</label>
							以下列表为当前合作伙伴推荐商户
							<?php else: ?>
							<label style="width: 60px;">归属：</label>
							<?php echo get_level_sel($postdata['bid'],'-1','soplv[]',$postdata['pid'],2); endif; ?>
					</p>
					<p>
	                    <label>渠道：</label>
	                    <select class="combox" name="post[CHANNEL_MAP_ID]">
	                    	<option value="">请选择</option>
	                    	<option value="0">自主</option>
	                        <?php if(is_array($host_result)): $i = 0; $__LIST__ = $host_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if(($postdata['CHANNEL_MAP_ID'] == $key)): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
	                    </select>
	                </p>
	                <p>
	                    <label>门店码：</label>
	                    <select class="combox" name="post[SHOP_MAP_ID_CODE]">
	                    	<option value="">请选择</option>
	                    	<option value="is not null">门店码</option>
	                    </select>
	                </p><?php endif; ?>
				<div class="clear"></div>
			</div>
			<div class="hbtn">
				<button class="ch-btn-skin ch-btn-small ch-icon-search">查 询</button>
				<a rel="Shop" target="navTab" href="/index.php/Home/Shop/shop" res_title="false"><button class="ch-btn-skin ch-btn-small ch-icon-refresh">重 置</button></a>
			</div>
		</div>
	</form>
</div>
<div class="pageContent">
	<div class="panelBar selbutton">
		<?php echo getaction_select(Shop,shop);?>
	</div>
	<table class="table" width="100%" layoutH="209">
		<thead>
			<tr>
				<th width="4%" align='center'>ID</th>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><th width="4%" align='center'>渠道</th><?php endif; ?>
				<th width="9%" align='center'>商户号</th>
				<th width="13%" align="center">商户名称</th>
				<!--<th width="13%">商户简称</th>-->
				<th width="13%" align="center">商户手机号</th>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><th width="10%">所在城市</th><?php endif; ?>
				<!--<th width="16%">归属</th>
				<th width="5%">归属集团商户</th>-->
				<th width="5%" align="center">商户状态</th>
				<!--<th width="5%" align="center">装机状态</th>-->
				<th width="12%" align='center'>开通日期</th>
			</tr>
		</thead>
		<tbody>
			<?php if(is_array($list)): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?><tr target="sid_target" rel="<?php echo ($vo["SHOP_MAP_ID"]); ?>" class="<?php if($k%2 == 1): ?>bg<?php endif; ?>">
					<td><?php echo ($vo["SHOP_MAP_ID"]); ?></td>
					<?php if($home['CHANNEL_MAP_ID'] == 0): ?><td><?php echo (get_channel_name($vo["CHANNEL_MAP_ID"])); ?></td><?php endif; ?>
					<td><?php echo ($vo["SHOP_NO"]); ?></td>
					<td><?php echo ($vo["SHOP_NAME"]); ?></td>
					<!--<td><?php echo ($vo["SHOP_NAMEABCN"]); ?></td>-->
					<td><?php echo ($vo["MOBILE"]); ?></td>
					<?php if($home['CHANNEL_MAP_ID'] == 0): ?><td><?php echo (getcity_name($vo["CITY_NO"])); ?></td><?php endif; ?>
					<!--<td><?php echo ((isset($vo["PARTNER_NAME"]) && ($vo["PARTNER_NAME"] !== ""))?($vo["PARTNER_NAME"]):"暂无"); ?></td>
					<td><?php if(($vo["SHOP_LEVEL"]) != "3"): ?>暂无<?php else: echo (get_shopp_name($vo["SHOP_MAP_ID_P"])); endif; ?></td>-->
					<td><?php echo ($shop_status[$vo['SHOP_STATUS']]); ?></td>
					<!--<td><?php echo ((isset($install_status[$vo['INSTALL_FLAG']]) && ($install_status[$vo['INSTALL_FLAG']] !== ""))?($install_status[$vo['INSTALL_FLAG']]):"未装机"); ?></td>-->
					<td><?php echo ($vo["CREATE_TIME"]); ?></td>
				</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		</tbody>
	</table>
	<div class="panelBar">
		<div class="pages"><span>共 <?php echo ((isset($totalCount) && ($totalCount !== ""))?($totalCount):"0"); ?> 条</span></div>
		<div class="pagination" targetType="navTab" totalCount="<?php echo ($totalCount); ?>" numPerPage="<?php echo ($numPerPage); ?>" pageNumShown="10" currentPage="<?php echo ($currentPage); ?>"></div>
	</div>
	<script type="text/javascript">		
		//页面加载执行
		$(document).ready(function(){
			var	navsnum     = $('.tabsPageHeader .navTab-tab .selected').index();
			var tabscontent = $('.tabsPageContent .unitBox:eq('+navsnum+')');
			//点击查看统计
			tabscontent.find('a[note-type="getstatis"]').on('click',  function(){
				tabscontent.find('.pages').html('<span><img class="loading" src="/Public/home/images/loading.gif">统计中...</span>');
				
				var	exportdata  = tabscontent.find('#exportdata').val();
				var url 	    = exportdata ? '/index.php/Home/Shop/shop' + exportdata + '&submit=shop&ajax=loading' : '/index.php/Home/Shop/shop?submit=shop&ajax=loading';
				$.ajaxSettings.global = false;		//关闭框架loading	
				$.getJSON(url, {}, function(_data) {
					tabscontent.find('.pages').html('<span>共 '+ _data.count +' 条</span>');
					$.ajaxSettings.global = true;	//开启框架loading	
				});
			});
		});
	</script>
</div>