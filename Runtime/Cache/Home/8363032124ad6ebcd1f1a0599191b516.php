<?php if (!defined('THINK_PATH')) exit();?><form id="pagerForm" method="post" action="">
	<input type="hidden" name="pageNum" value="1" />
	<input type="hidden" name="post[submit]" value="daikuan_home"/>
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
	<form method="post" action="<?php echo U('/Home/Daikuan/daikuan_home');?>" onsubmit="return navTabSearch(this);">
		<input type="hidden" name="post[submit]" value="daikuan_home">
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
				<p class="maxcombox">
					<label>用户姓名：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["USER_NAME"]); ?>" name="post[USER_NAME]">
				</p>
				<p class="maxcombox">
					<label>身份证号：</label>
					<input class="input01 textInput" type="text" value="<?php echo ($postdata["USER_ID"]); ?>" name="post[USER_ID]">
				</p>
				<p class="maxcombox">
					<label>性别：</label>
					<select class="combox" name="post[USER_SEX]">
						<option value="">请选择</option>
						<?php if(is_array($user_sex)): $i = 0; $__LIST__ = $user_sex;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p>
				<p>
					<label>用户年龄：</label>
					<input class="input01 textInput f_l ws13" type="text" value="<?php echo ($postdata["USER_AGE_A"]); ?>" name="post[USER_AGE_A]" maxlength="16">
					<code class="code"> ~ </code>
					<input class="input01 textInput f_l ws13" type="text" value="<?php echo ($postdata["USER_AGE_B"]); ?>" name="post[USER_AGE_B]" maxlength="16">
				</p>
				<div class="clear"></div>
				<p class="maxcombox">
					<label>借款金额：</label>
					<select class="combox" name="post[AMOUNT]">
						<option value="">请选择</option>
						<?php if(is_array($money_type)): $i = 0; $__LIST__ = $money_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
				</p>
				<p class="maxcombox">
					<label>步骤：</label>
					<select class="combox" name="post[STEP]">
						<option value="">请选择</option>
						<?php if(is_array($step_type)): $i = 0; $__LIST__ = $step_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
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
				<?php if($home['CHANNEL_MAP_ID'] == 0): ?><p>
	                    <label>渠道：</label>
	                    <select class="combox" name="post[CHANNEL_MAP_ID]">
	                    	<option value="">请选择</option>
	                    	<option value="0">自主</option>
	                        <?php if(is_array($host_result)): $i = 0; $__LIST__ = $host_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if(($postdata['CHANNEL_MAP_ID'] == $key)): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
	                    </select>
	                </p><?php endif; ?>
				<div class="clear"></div>
			</div>
			<div class="hbtn">
				<button class="ch-btn-skin ch-btn-small ch-icon-search">查 询</button>
				<a rel="Daikuan" target="navTab" href="/index.php/Home/Daikuan/daikuan_home" res_title="false"><button class="ch-btn-skin ch-btn-small ch-icon-refresh">重 置</button></a>
			</div>
		</div>
	</form>
</div>
<div class="pageContent">
	<div class="panelBar selbutton">
		<?php echo getaction_select(Daikuan,daikuan_home);?>
	</div>
	<table class="table" width="100%" layoutH="240">
		<thead>
			<tr>
				<th width="4%" align='center'>姓名</th>
				<th width="6%" align='center'>身份证</th>
				<th width="4%" align='center'>出生日期</th>
				<th width="2%" align="center">性别</th>
				<th width="4%" align="center">手机号码</th>
				<th width="3%" align="center">借款金额</th>
				<th width="4%" align="center">供应商</th>
				<th width="4%" align='center'>渠道</th>
				<th width="2%" align='center'>步骤</th>
				<th width="2%" align='center'>省份</th>
				<th width="2%" align='center'>市级</th>
				<th width="3%" align='center'>月收入</th>
				<th width="4%" align='center'>职业</th>
				<th width="4%" align='center'>资产情况</th>
				<th width="4%" align='center'>人寿保险单</th>
				<th width="4%" align='center'>社保公积金</th>
				<th width="2%" align='center'>微粒贷</th>
				<th width="4%" align='center'>IP地址</th>
				<th width="6%" align='center'>创建时间</th>
			</tr>
		</thead>
		<tbody>
			<?php if(is_array($daikuan)): $k = 0; $__LIST__ = $daikuan;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?><tr target="sid_target" rel="<?php echo ($vo["SHOP_MAP_ID"]); ?>" class="<?php if($k%2 == 1): ?>bg<?php endif; ?>">
					<td><?php echo ($vo["USER_NAME"]); ?></td>
					<?php if($home['CHANNEL_MAP_ID'] == 0): ?><td><?php echo ($vo["USER_ID"]); ?></td><?php else: ?><td><?php echo (replacestar($vo["USER_ID"],3,12)); ?></td><?php endif; ?>
					<td><?php echo ($vo["USER_BIRTHDAY"]); ?></td>
					<td><?php echo ($user_sex[$vo['USER_SEX']]); ?></td>
					<?php if($home['CHANNEL_MAP_ID'] == 0): ?><td><?php echo ($vo["MOBILE"]); ?></td><?php else: ?><td><?php echo (replacestar($vo["MOBILE"],3,5)); ?></td><?php endif; ?>
					<td><?php echo ($money_type[$vo[AMOUNT]]); ?></td>
					<td><?php echo ($vo["SUPPLIER"]); ?></td>
					<td><?php echo ($vo["CHANNEL_MAP_ID"]); ?></td>
					<td><?php echo ($step_type[$vo['STEP']]); ?></td>
					<td><?php echo ($vo["PROVINCE"]); ?></td>
					<td><?php echo ($vo["CITY"]); ?></td>
					<td><?php echo ($month_income[$vo['MONTH_INCOME']]); ?></td>
					<td><?php echo ($job_category[$vo[JOB_CATEGORY]]); ?></td>
					<td><?php echo ($house_car_type[$vo['HOUSE_PROPERTY_CAR']]); ?></td>
					<td><?php echo ($policy_type[$vo['POLICY']]); ?></td>
					<td><?php echo ($social_found_type[$vo['SOCIAL_SECURITY_FOUND']]); ?></td>
					<td><?php echo ($loan_type[$vo['PARTICLE_LOAN']]); ?></td>
					<td><?php echo ($vo["IP_ADDRESS"]); ?></td>
					<td><?php echo ($vo["CREATE_TIME"]); ?></td>
					<!-- <td><?php echo ($shop_status[$vo['SHOP_STATUS']]); ?></td> -->
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
				var url 	    = exportdata ? '/index.php/Home/Daikuan/daikuan_home' + exportdata + '&submit=daikuan_home&ajax=loading' : '/index.php/Home/Daikuan/daikuan_home?submit=daikuan_home&ajax=loading';
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