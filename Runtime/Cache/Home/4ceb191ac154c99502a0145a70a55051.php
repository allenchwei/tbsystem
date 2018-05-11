<?php if (!defined('THINK_PATH')) exit();?><form id="pagerForm" method="post" action="">
	<input type="hidden" name="pageNum" value="1" />
	<input type="hidden" name="post[submit]" value="trace"/>
	<input type="hidden" name="post[SYSTEM_DATE_A]" value="<?php echo ($postdata["SYSTEM_DATE_A"]); ?>"/>
	<input type="hidden" name="post[SYSTEM_DATE_B]" value="<?php echo ($postdata["SYSTEM_DATE_B"]); ?>"/>
	<input type="hidden" name="post[BRANCH_MAP_ID]" value="<?php echo ($postdata["BRANCH_MAP_ID"]); ?>"/>
	<input type="hidden" name="post[PARTNER_MAP_ID]" value="<?php echo ($postdata["PARTNER_MAP_ID"]); ?>"/>
	<input type="hidden" name="post[TRANS_SUBID]" value="<?php echo ($postdata["TRANS_SUBID"]); ?>"/>
	<input type="hidden" name="post[TRACE_STATUS]" value="<?php echo ($postdata["TRACE_STATUS"]); ?>"/>
	<input type="hidden" name="post[TRANS_AMT_A]" value="<?php echo ($postdata["TRANS_AMT_A"]); ?>"/>
	<input type="hidden" name="post[TRANS_AMT_B]" value="<?php echo ($postdata["TRANS_AMT_B"]); ?>"/>
	<input type="hidden" name="post[HOST_MAP_ID]" value="<?php echo ($postdata["HOST_MAP_ID"]); ?>"/>
	<input type="hidden" name="post[SHOP_NAMEAB]" value="<?php echo ($postdata["SHOP_NAMEAB"]); ?>"/>
	<input type="hidden" name="post[MOBILE]" value="<?php echo ($postdata["MOBILE"]); ?>"/>
	<input type="hidden" name="post[POS_NO]" value="<?php echo ($postdata["POS_NO"]); ?>"/>
	<input type="hidden" name="post[POS_TRACE]" value="<?php echo ($postdata["POS_TRACE"]); ?>"/>
	<input type="hidden" name="post[IS_SHARE_FEE]" value="<?php echo ($postdata["IS_SHARE_FEE"]); ?>"/>
	<input type="hidden" name="post[CHANNEL_MAP_ID]" value="<?php echo ($postdata["CHANNEL_MAP_ID"]); ?>"/>
	<input type="hidden" name="post[VIP_CARDNO]" value="<?php echo ($postdata["VIP_CARDNO"]); ?>"/>
	<input type="hidden" name="post[SOURCE]" value="<?php echo ($postdata["SOURCE"]); ?>"/>
	<input type="hidden" id="exportdata" value="<?php echo ($exportdata); ?>"/><!-- Excel导出 -->
</form>
<div class="pageHeader">
	<form method="post" action="/index.php/Home/Trace/trace/navTabId/Trace" onsubmit="return navTabSearch(this);">
		<input type="hidden" name="post[submit]" value="trace">
		<div class="searchBar">
			<div class="header">				
				<p>
					<span>
						<label>交易日期：</label>
						<input class="input01 textInput date f_l readonly" type="text" value="<?php echo ($postdata["SYSTEM_DATE_A"]); ?>" name="post[SYSTEM_DATE_A]" readonly="true">
						<a class="inputDateButton f_l" href="#">选择</a>
					</span>
					<code class="code"> ~ </code>
					<span>
						<input class="input01 textInput date f_l readonly" type="text" value="<?php echo ($postdata["SYSTEM_DATE_B"]); ?>" name="post[SYSTEM_DATE_B]" readonly="true">
						<a class="inputDateButton f_l" href="#">选择</a>
					</span>
					<span class="sta_day">
						<a href="javascript:;" note-type="seltime" begin="<?php echo ($timedata["jintian_b"]); ?>" end="<?php echo ($timedata["jintian_n"]); ?>">当日</a>
						<a href="javascript:;" note-type="seltime" begin="<?php echo ($timedata["zuotian_b"]); ?>" end="<?php echo ($timedata["zuotian_n"]); ?>">昨日</a>
						<a href="javascript:;" note-type="seltime" begin="<?php echo ($timedata["benyue_b"]); ?>" end="<?php echo ($timedata["benyue_n"]); ?>">本月</a>
						<a href="javascript:;" style="border:none;" note-type="seltime" begin="<?php echo ($timedata["shangyue_b"]); ?>" end="<?php echo ($timedata["shangyue_n"]); ?>">上月</a>
					</span>
				</p>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><p class="maxcombox5">
					<label>第三方渠道：</label>
					<select class="combox" name="post[CHANNEL_MAP_ID]">
						<option value="">请选择</option>
						<?php if(is_array($channel_list)): $i = 0; $__LIST__ = $channel_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["CHANNEL_MAP_ID"]); ?>" <?php if(($postdata['CHANNEL_MAP_ID']) == $vo["CHANNEL_MAP_ID"]): ?>selected<?php endif; ?>><?php echo ($vo["CHANNEL_NAME"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p><?php endif; ?>
				<div class="clear"></div>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><p>
					<label>交易类型：</label>
					<select class="combox" name="post[TRANS_SUBID]">
						<option value="">请选择</option>
						<?php if(is_array($trans_list)): $i = 0; $__LIST__ = $trans_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["TRANS_SUBID"]); ?>" <?php if(($postdata['TRANS_SUBID']) == $vo["TRANS_SUBID"]): ?>selected<?php endif; ?>><?php echo ($vo["TRANS_NAME"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p><?php endif; ?>
				<?php if($home['CHANNEL_MAP_ID'] == 0 OR $home['ROLE_NAME'] == OEM): ?><p class="maxcombox5">
					<label>处理结果：</label>
					<select class="combox" name="post[TRACE_STATUS]">
						<option value="">请选择</option>
						<?php if(is_array($trace_status)): $i = 0; $__LIST__ = $trace_status;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if(($postdata['TRACE_STATUS'] == $key) and ($postdata['TRACE_STATUS'] != '')): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p><?php endif; ?>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><p class="maxcombox5">
					<label>支付通道：</label>
					<select class="combox" name="post[HOST_MAP_ID]">
						<option value="">请选择</option>
						<?php if(is_array($host_list)): $i = 0; $__LIST__ = $host_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["HOST_MAP_ID"]); ?>" <?php if(($postdata['HOST_MAP_ID']) == $vo["HOST_MAP_ID"]): ?>selected<?php endif; ?>><?php echo ($vo["HOST_NAME"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p>
				<p class="maxcombox5">
					<label>是否分润：</label>
					<select class="combox" name="post[IS_SHARE_FEE]">
						<option value="1" <?php if(($postdata['IS_SHARE_FEE']) == "1"): ?>selected<?php endif; ?>>是</option>
						<option value="0" <?php if(($postdata['IS_SHARE_FEE']) == "0"): ?>selected<?php endif; ?>>否</option>
					</select>
				</p><?php else: ?><input type="hidden" name="post[IS_SHARE_FEE]" value="0"/><?php endif; ?>
				<div class="clear"></div>
				<!--<p>
					<label>会员卡号：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["VIP_CARDNO"]); ?>" name="post[VIP_CARDNO]" maxlength="20">
				</p>-->
				<?php if($home['CHANNEL_MAP_ID'] == 0 OR $home['ROLE_NAME'] == OEM): ?><p>
					<label>交易金额：</label>
					<input class="input01 textInput f_l ws13" type="text" value="<?php echo ($postdata["TRANS_AMT_A"]); ?>" name="post[TRANS_AMT_A]" maxlength="16">
					<code class="code"> ~ </code>
					<input class="input01 textInput f_l ws13" type="text" value="<?php echo ($postdata["TRANS_AMT_B"]); ?>" name="post[TRANS_AMT_B]" maxlength="16">
				</p><?php endif; ?>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><!-- <p>
					<label>　终端号：</label>
					<input class="input01 textInput ws13" type="text" value="<?php echo ($postdata["POS_NO"]); ?>" name="post[POS_NO]" maxlength="8">
				</p> -->
				<p>
					<label>POS流水号：</label>
					<input class="input01 textInput ws13" type="text" value="<?php echo ($postdata["POS_TRACE"]); ?>" name="post[POS_TRACE]" maxlength="6">
				</p>
				<!-- <div class="clear"></div> -->
				<p>
					<label>商户简称：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["SHOP_NAMEAB"]); ?>" name="post[SHOP_NAMEAB]" maxlength="12">
				</p>
				<?php else: ?>
				<div class="clear"></div><?php endif; ?>
				<p>
					<label>手机号：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["MOBILE"]); ?>" name="post[MOBILE]" maxlength="11">
				</p>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><p class="maxcombox5">
					<label>流水归属：</label>
					<?php echo get_level_sel($postdata['BRANCH_MAP_ID'],'-1','plv[]',$postdata['PARTNER_MAP_ID'],5);?>
				</p>
				<p class="maxcombox5">
					<label>来源：</label>
					<select class="combox" name="post[SOURCE]">
						<option value="">请选择</option>
						<option value="2" <?php if(($postdata['SOURCE']) == "2"): ?>selected<?php endif; ?>>网关快捷</option>
						<option value="1" <?php if(($postdata['SOURCE']) == "1"): ?>selected<?php endif; ?>>线上</option>
						<option value="0" <?php if(($postdata['SOURCE']) == "0"): ?>selected<?php endif; ?>>线下</option>
					</select>
				</p><?php else: ?><input type="hidden" name="post[SOURCE]" value=""/><?php endif; ?>
				<?php if($home['CHANNEL_MAP_ID'] == 0 OR $home['ROLE_NAME'] == OEM): ?><p>
					<label>交易流水号：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["ORDER_NO"]); ?>" name="post[ORDER_NO]" maxlength="32">
				</p><?php endif; ?>
			</div>
			<div class="hbtn">
				<button class="ch-btn-skin ch-btn-small ch-icon-search">查 询</button>
				<a rel="Trace" target="navTab" href="/index.php/Home/Trace/trace" res_title="false"><button class="ch-btn-skin ch-btn-small ch-icon-refresh">重 置</button></a>
			</div>
		</div>
	</form>
</div>
<div class="pageContent">
	<div class="panelBar selbutton">
		<?php echo getaction_select(Trace,trace);?>
		<div class="f_r">
			<span class="remark">备注：该功能用于查询涉及到登录者区域内商户的交易流水明细。</span>
		</div>
		<div class="clear"></div>
	</div>
	<table class="table" width="100%" layoutH="242">
		<thead>
			<tr>
				<th width="4%" align='center'>来源</th>
				<th width="6%" align='center'>交易类型</th>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><th width="10%">归属</th><?php endif; ?>
				<th width="16%" >商户名称</th>
				<th width="12%" >商户简称</th>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><th width="6%" align='center'>银行卡号</th><?php endif; ?>
				<th width="6%" align='right'>交易金额</th>
				<th width="6%" align='center'>手续费</th>
				<th width="4%" align='center'>结果</th>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><th width="4%" align='center'>积分率</th><?php endif; ?>
				<th width="11%" align='center'>系统交易时间</th>
				<th width="11%" align='center'>完成交易时间</th>
			</tr>
		</thead>
		<tbody>
			<?php if(is_array($list)): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?><tr target="sid_target" rel="<?php echo ($vo["SYSTEM_REF"]); ?>" class="<?php if($k%2 == 1): ?>bg<?php endif; ?>">
					<td <?php if($vo["SOURCE"] == 1): ?>style="color:red;"<?php endif; ?>><?php if($vo["SOURCE"] == 1): ?>线上<?php elseif($vo["SOURCE"] == ''): ?>网关快捷<?php else: ?>线下<?php endif; ?></td>
					<td><?php echo ($vo["TRANS_NAME"]); ?></td>
					<?php if($home['CHANNEL_MAP_ID'] == 0): ?><td><?php echo (get_branch_name($vo["SBRANCH_MAP_ID"],$vo['SPARTNER_MAP_ID'])); ?></td><?php endif; ?>
					<td><?php echo get_shop_data($vo['SHOP_NO'])[SHOP_NAME];?></td>
					<td><?php echo ($vo["SHOP_NAMEAB"]); ?></td>
					<?php if($home['CHANNEL_MAP_ID'] == 0): ?><td><?php echo (setcard_no($vo["CARD_NO"])); ?></td><?php endif; ?>
					<td><?php echo (setmoney($vo["TRANS_AMT"],2,2)); ?></td>
					<td><?php echo (setmoney($vo["SHOP_MDR"],2,2)); ?></td>
					<td><?php echo ($trace_status[$vo[TRACE_STATUS]]); ?></td>
					<?php if($home['CHANNEL_MAP_ID'] == 0): ?><td><?php echo (setmoney($vo["PER_FEE"],2,2)); ?>%</td><?php endif; ?>
					<td><?php echo ($vo["SYSTEM_DATE"]); ?> <?php echo ($vo["SYSTEM_TIME"]); ?></td>
					<td><?php echo ($vo["POS_DATE"]); ?> <?php echo ($vo["POS_TIME"]); ?></td>
				</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		</tbody>
	</table>
	<div class="panelBar">
		<div class="pages"><span><a class="statis" href="javascript:;" note-type="getstatis">点击查看统计</a></span></div>
		<div class="pagination" checkPage="1" targetType="navTab" totalCount="<?php echo ($totalCount); ?>" numPerPage="<?php echo ($numPerPage); ?>" pageNumShown="10" currentPage="<?php echo ($currentPage); ?>"></div>
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
				var url 	    = exportdata ? '/index.php/Home/Trace/trace' + exportdata + '&submit=trace&ajax=loading' : '/index.php/Home/Trace/trace?submit=trace&ajax=loading';
				$.ajaxSettings.global = false;		//关闭框架loading	
				$.getJSON(url, {}, function(_data) {
					tabscontent.find('.pages').html('<span>共 '+ _data.count +' 条</span><span>　交易总额 '+ _data.amt +' 元</span>');
					$.ajaxSettings.global = true;	//开启框架loading	
				});
			});
			//日期选择
			tabscontent.find('a[note-type="seltime"]').on('click',  function(){
				var begin = $(this).attr('begin'),end = $(this).attr('end');
				tabscontent.find('input[name="post[SYSTEM_DATE_A]"]').val(begin);
				tabscontent.find('input[name="post[SYSTEM_DATE_B]"]').val(end);
			});
		});
	</script>
</div>