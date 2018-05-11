<?php if (!defined('THINK_PATH')) exit();?><form id="pagerForm" method="post" action="">
	<input type="hidden" name="pageNum" value="1" />
	<input type="hidden" name="post[submit]" value="zp_count"/>
	<input type="hidden" name="post[CREATE_TIME_A]" value="<?php echo ($postdata["CREATE_TIME_A"]); ?>"/>
	<input type="hidden" name="post[CREATE_TIME_B]" value="<?php echo ($postdata["CREATE_TIME_B"]); ?>"/>
	<input type="hidden" name="post[SHOP_MAP_ID]" value="<?php echo ($postdata["SHOP_MAP_ID"]); ?>"/>
</form>
<div class="pageHeader">
	<form method="post" action="<?php echo U('/Home/Daikuan/zp_count');?>" onsubmit="return navTabSearch(this);">
		<input type="hidden" name="post[submit]" value="zp_count">
		<div class="searchBar">
			<div class="header">
				<p>
					<span>
						<label>注册日期：</label>
						<input class="input01 textInput date f_l readonly" type="text" value="<?php echo ($postdata["CREATE_TIME_A"]); ?>" name="post[CREATE_TIME_A]" readonly="true">
						<a class="inputDateButton f_l" href="#">选择</a>
					</span>
					<code class="code"> ~ </code>
					<span>
						<input class="input01 textInput date f_l readonly" type="text" value="<?php echo ($postdata["CREATE_TIME_B"]); ?>" name="post[CREATE_TIME_B]" readonly="true">
						<a class="inputDateButton f_l" href="#">选择</a>
					</span>
					<span class="sta_day">
						<a href="javascript:;" note-type="seltime" begin="<?php echo ($timedata["jintian_b"]); ?>" end="<?php echo ($timedata["jintian_n"]); ?>">当日</a>
						<a href="javascript:;" note-type="seltime" begin="<?php echo ($timedata["zuotian_b"]); ?>" end="<?php echo ($timedata["zuotian_n"]); ?>">昨日</a>
						<a href="javascript:;" note-type="seltime" begin="<?php echo ($timedata["benyue_b"]); ?>" end="<?php echo ($timedata["benyue_n"]); ?>">本月</a>
						<a href="javascript:;" style="border:none;" note-type="seltime" begin="<?php echo ($timedata["shangyue_b"]); ?>" end="<?php echo ($timedata["shangyue_n"]); ?>">上月</a>
					</span>
				</p>
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><p>
	                    <label>渠道：</label>
	                    <select class="combox" name="post[CHANNEL_MAP_ID]">
	                    	<option value="">请选择</option>
	                        <?php if(is_array($channel)): $i = 0; $__LIST__ = $channel;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if(($postdata['CHANNEL_MAP_ID'] == $key)): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
	                    </select>
	                </p><?php endif; ?>
				<div class="clear"></div>
			</div>
			<div class="hbtn">
				<button class="ch-btn-skin ch-btn-small ch-icon-search">查 询</button>
				<a rel="Daikuan" target="navTab" href="/index.php/Home/Daikuan/zp_count" res_title="false"><button class="ch-btn-skin ch-btn-small ch-icon-refresh">重 置</button></a>
			</div>
		</div>
	</form>
</div>
<div class="pageContent">
	<div class="panelBar selbutton">
		<?php echo getaction_select(Daikuan,zp_count);?>
	</div>
	<table class="table" width="100%" layoutH="140">
		<thead>
			<tr>
				<th width="10%" align='center'>渠道名称</th>
				<th width="8%" align='center'>PV</th>
				<th width="8%" align='center'>UV</th>
				<th width="8%" align='center'>累抽次数</th>
				<th width="8%" align='center'>单位PV点击次数</th>
				<th width="8%" align='center'>单位UV点击次数</th>
				<th width="10%" align='center'>点击日期</th>
			</tr>
		</thead>
		<tbody>
			<?php if(is_array($list)): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?><tr target="sid_target" rel="<?php echo ($vo["TRACE_ID"]); ?>">
					<td><?php echo ($vo["CHANNEL_NAME"]); ?></td>
					<td><?php echo ($vo["TRACE_PV"]); ?></td>
					<td><?php echo ($vo["TRACE_UV"]); ?></td>
					<td><?php echo ($vo["ZP_CLICK"]); ?></td>
					<td><?php echo (sprintf("%.2f",$vo["PV_COUNT"])); ?></td>
					<td><?php echo (sprintf("%.2f",$vo["UV_COUNT"])); ?></td>
					<td><?php echo ($vo["DATE_TIME"]); ?></td>
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
				var url 	    = exportdata ? '/index.php/Home/Daikuan/zp_count' + exportdata + '&submit=zp_count&ajax=loading' : '/index.php/Home/Daikuan/zp_count?submit=zp_count&ajax=loading';
				$.ajaxSettings.global = false;		//关闭框架loading	
				$.getJSON(url, {}, function(_data) {
					tabscontent.find('.pages').html('<span>共 '+ _data.count +' 条</span>');
					$.ajaxSettings.global = true;	//开启框架loading	
				});
			});
			//日期选择
			tabscontent.find('a[note-type="seltime"]').on('click',  function(){
				var begin = $(this).attr('begin'),end = $(this).attr('end');
				tabscontent.find('input[name="post[CREATE_TIME_A]"]').val(begin);
				tabscontent.find('input[name="post[CREATE_TIME_B]"]').val(end);
			});
		});
	</script>
</div>