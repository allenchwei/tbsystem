<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>大金融业务管理系统</title>
	<link rel="shortcut icon" href="/Public/logo/link.png" type=image/x-icon>
	<!--<?php if($_SESSION['HOME']['CHANNEL_MAP_ID']== 0): ?>-->
		<!--<title><?php echo (C("seo_title")); ?></title>-->
		<!--&lt;!&ndash;<link rel="shortcut icon" href="/Public/logo/big_finance.png" type=image/x-icon>&ndash;&gt;-->
	<!--<?php else: ?>-->
		<!--<title><?php echo ($channel['CHANNEL_NAME']); ?>后台管理系统</title>-->
	<!-- <link rel="shortcut icon" href="/Public/logo/link.png" type=image/x-icon> -->
	<!--<?php endif; ?>-->
	<link href="/Public/dwz/themes/default/style.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="/Public/dwz/themes/css/core.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="/Public/dwz/themes/css/print.css" rel="stylesheet" type="text/css" media="print"/>
	<link href="/Public/dwz/uploadify/css/uploadify.css" rel="stylesheet" type="text/css" media="screen"/>
	<link href="/Public/home/css/base.css" rel="stylesheet" type="text/css" media="screen"/>
	<!--[if IE]>
	<link href="/Public/dwz/themes/css/ieHack.css" rel="stylesheet" type="text/css" media="screen"/>
	<![endif]-->
	<!--[if lte IE 9]>
	<script src="/Public/dwz/js/speedup.js" type="text/javascript"></script>
	<![endif]-->
	<script src="/Public/dwz/js/jquery-1.7.2.min.js" type="text/javascript"></script>
	<script src="/Public/dwz/js/jquery.cookie.js" type="text/javascript"></script>
	<script src="/Public/dwz/js/jquery.validate.js" type="text/javascript"></script>
	<script src="/Public/dwz/js/jquery.bgiframe.js" type="text/javascript"></script>
	<script src="/Public/dwz/xheditor/xheditor-1.2.1.min.js" type="text/javascript"></script>
	<script src="/Public/dwz/xheditor/xheditor_lang/zh-cn.js" type="text/javascript"></script>
	<script src="/Public/dwz/uploadify/scripts/jquery.uploadify.js" type="text/javascript"></script>
	<script src="/Public/dwz/bin/dwz.min.js" type="text/javascript"></script>
	<script src="/Public/dwz/js/dwz.regional.zh.js" type="text/javascript"></script>
	<script type="text/javascript">
		var SETTING = {'USER_ID':'<?php echo $_SESSION['HOME']['USER_ID'];?>','KEYWORD':'<?php echo md5(md5(substr($_SESSION['HOME']['USER_ID'],-1,1).'0000'));?>'};
		$(function(){
			DWZ.init("/Public/dwz/dwz.frag.xml", {
				loginUrl:"/index.php/Home/Public/logout.html", //跳到登录页面
				statusCode:{ok:200, error:300, timeout:301}, //可选】
				pageInfo:{pageNum:"pageNum", numPerPage:"numPerPage", orderField:"orderField", orderDirection:"orderDirection"}, //【可选】
				debug:false, //调试模式 【true|false】
				callback:function(){
					initEnv();
					$("#themeList").theme({themeBase:"themes"});
				}
			});
		});
		$.ajaxSettings.global = true;	//开启框架loading	怕有些地方关闭了
	</script>
</head>
<body scroll="no">
	<div id="layout">
		<div id="header">
			<div class="headerNav">
				<?php if($_SESSION['HOME']['CHANNEL_MAP_ID']== 0): ?><!--<a class="logo" href="/">标志</a>-->
				<?php else: ?>
					<a class="" href="/"><img src="<?php echo ($channel['LOGO_URL']); ?>" style="width: 50px;height: 50px"><h1 style="margin-top: -40px;margin-left: 52px;font-size: 23px;color:white;"><?php echo ($channel['CHANNEL_NAME']); ?>后台管理系统</h1></a>
					<input type="hidden" value="<?php echo ($channel['CHANNEL_MAP_ID']); ?>">
					<input type="hidden" value="<?php echo ($home['USER_NAME']); ?>"><?php endif; ?>
				<ul class="nav">
					<li style="background:none;"><a href="javascript:;"><?php echo ($_SESSION['HOME']['ROLE_NAME']); ?> - <?php echo ($_SESSION['HOME']['USER_NAME']); ?></a></li>
					<li><a note-type="uppwd" mask="true" target="dialog" height="380" width="640" href="/index.php/Home/Index/user_uppwd/navTabId/Index.html">改密码</a></li>
					<li><a href="<?php echo U('/Home/Public/logout');?>">退出</a></li>					
					<!-- <li><a href="<?php echo U('/Home/Public/logout');?>" target='ajaxTodo' calback='navTabAjaxDone' title='您确定要退出吗？'>退出</a></li> -->
				</ul>
			</div>		
		</div>
		<div id="leftside">
			<div id="sidebar_s">
				<div class="collapse">
					<div class="toggleCollapse"><div></div></div>
				</div>
			</div>
			<div id="sidebar">
				<div class="toggleCollapse"><h2>菜单导航</h2></div>
				<div class="accordion" fillSpace="sidebar">
					<?php echo ($menu_html); ?>
				</div>
			</div>
		</div>
		<div id="container">
			<div id="navTab" class="tabsPage">
				<div class="tabsPageHeader">
					<div class="tabsPageHeaderContent"><!-- 显示左右控制时添加 class="tabsPageHeaderMargin" -->
						<ul class="navTab-tab">
							<li tabid="main" class="main"><a href="javascript:;"><span><span class="home_icon">我的主页</span></span></a></li>
						</ul>
					</div>
					<div class="tabsLeft">left</div><!-- 禁用只需要添加一个样式 class="tabsLeft tabsLeftDisabled" -->
					<div class="tabsRight">right</div><!-- 禁用只需要添加一个样式 class="tabsRight tabsRightDisabled" -->
					<div class="tabsMore">more</div>
				</div>
				<ul class="tabsMoreList">
					<li><a href="javascript:;">我的主页</a></li>
				</ul>
				<div class="navTab-panel tabsPageContent layoutBox">
					<div class="page unitBox">
						<div class="index_top">
							<div class="gg notice_list">
								<ul> 
									<?php if(is_array($notice_data)): $i = 0; $__LIST__ = $notice_data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li><a href="/index.php/Home/Common/notice_show/id/<?php echo ($vo["NOTICE_ID"]); ?>" width="640" height="380" target="dialog" mask="true"><?php echo ($vo["NOTICE_TITLE"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
								</ul>
							</div>
							<div class="gg_h">
								<i></i>系统公告：
							</div>
						</div>
						<div class="index_cont">
							<?php if(($page1 == '') and ($page2 == '') and ($page3 == '') and ($page4 == '') and ($page5 == '')): ?><div class="cont_gd">欢迎登录大金融业务管理平台</div>
							<?php else: ?>							
								<div class="cont_gd"><i></i>我的工单：</div>
								<div class="img">
									<?php if(!empty($page1)): ?><div class="gd">
											<div class="sword"><?php echo ($page1["title"]); ?></div><!-- 进件变更 -->
											<div class="gd_bg bg1">
												<div class="gdc">
													<?php if(is_array($page1["list"])): $i = 0; $__LIST__ = $page1["list"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><p>(<font color="red" rel="<?php echo ($vo["MENU_ID"]); ?>"><?php echo ((isset($total1[$vo['MENU_ID']]) && ($total1[$vo['MENU_ID']] !== ""))?($total1[$vo['MENU_ID']]):"0"); ?></font>) 个<?php echo ($vo["MENU_TITLE"]); ?></p><?php endforeach; endif; else: echo "" ;endif; ?>
												</div>
											</div>
										</div><?php endif; ?>
									<?php if(!empty($page2)): ?><div class="gd">
											<div class="sword"><?php echo ($page2["title"]); ?></div><!-- 库管 -->
											<div class="gd_bg bg2">
												<div class="gdc">
													<?php if(is_array($page2["list"])): $i = 0; $__LIST__ = $page2["list"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><p>(<font color="red" rel="<?php echo ($vo["MENU_ID"]); ?>"><?php echo ((isset($total2[$vo['MENU_ID']]) && ($total2[$vo['MENU_ID']] !== ""))?($total2[$vo['MENU_ID']]):"0"); ?></font>) 个<?php echo ($vo["MENU_TITLE"]); ?></p><?php endforeach; endif; else: echo "" ;endif; ?>
												</div>
											</div>
										</div><?php endif; ?>
									<?php if(!empty($page3)): ?><div class="gd">
											<div class="sword"><?php echo ($page3["title"]); ?></div><!-- 清算 -->
											<div class="gd_bg bg3">
												<div class="gdc">
													<?php if(is_array($page3["list"])): $i = 0; $__LIST__ = $page3["list"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><p>(<font color="red" rel="<?php echo ($vo["MENU_ID"]); ?>"><?php echo ((isset($total3[$vo['MENU_ID']]) && ($total3[$vo['MENU_ID']] !== ""))?($total3[$vo['MENU_ID']]):"0"); ?></font>) 个<?php echo ($vo["MENU_TITLE"]); ?></p><?php endforeach; endif; else: echo "" ;endif; ?>
												</div>
											</div>
										</div><?php endif; ?>
									<?php if(!empty($page4)): ?><div class="gd">
											<div class="sword"><?php echo ($page4["title"]); ?></div><!-- 风控 -->
											<div class="gd_bg bg4">
												<div class="gdc">
													<?php if(is_array($page4["list"])): $i = 0; $__LIST__ = $page4["list"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><p>(<font color="red" rel="<?php echo ($vo["MENU_ID"]); ?>"><?php echo ((isset($total4[$vo['MENU_ID']]) && ($total4[$vo['MENU_ID']] !== ""))?($total4[$vo['MENU_ID']]):"0"); ?></font>) 个<?php echo ($vo["MENU_TITLE"]); ?></p><?php endforeach; endif; else: echo "" ;endif; ?>
												</div>
											</div>
										</div><?php endif; ?>
									<?php if(!empty($page5)): ?><div class="gd">
											<div class="sword"><?php echo ($page5["title"]); ?></div><!-- 开票提醒 -->
											<div class="gd_bg bg1">
												<div class="gdc">
													<?php if(is_array($page5["list"])): $i = 0; $__LIST__ = $page5["list"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><p>(<font color="red" rel="<?php echo ($vo["MENU_ID"]); ?>"><?php echo ((isset($total5[$vo['MENU_ID']]) && ($total5[$vo['MENU_ID']] !== ""))?($total5[$vo['MENU_ID']]):"0"); ?></font>) 个<?php echo ($vo["MENU_TITLE"]); ?></p><?php endforeach; endif; else: echo "" ;endif; ?>
												</div>
											</div>
										</div><?php endif; ?>
									<div class="clear"></div>
								</div><?php endif; ?>
						</div>
						<div class="index_bottom">
							<div class="index_bot">
								<div class="gg">
									<p>1、所有页面打开时，要先点击【查询】，即可显示该页面的内容。</p>
									<p>2、点击【重置】按钮，所有的筛选条件会被清空。</p>
									<p>3、上传图片要求：图片需小于2M，jpg格式。</p>
								</div>
								<div class="gg_h">
									<i></i>操作小贴士：
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			var countnotice = '<?php echo count($notice_data);?>';
			if(countnotice > 0){		
				$(function(){
					//处理滚动
					var $this = $(".notice_list");
					var scrollTimer;
					$this.hover(function(){
						clearInterval(scrollTimer);
					},function(){
						scrollTimer = setInterval(function(){
							scrollNews( $this );
						}, 2000 );
					}).trigger("mouseout");			
				});		
				//滚动
				function scrollNews(obj){
					var $self = obj.find("ul:first");
					var lineHeight = $self.find("li:first").height();
					$self.animate({ "margin-top" : -lineHeight +"px" },600 , function(){
						$self.css({"margin-top":"0px"}).find("li:first").appendTo($self);
					})
				}
			}
		</script>
	</div>
	<div id="footer"></div>
</body>
</html>