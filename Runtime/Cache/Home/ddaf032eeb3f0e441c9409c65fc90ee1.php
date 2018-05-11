<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!--<title><?php if(empty($title)): echo (C("seo_title")); else: echo ($title); endif; ?></title>-->
	<title>大金融业务系统</title>
	<link href="/Public/home/css/common.css" rel="stylesheet" />
	<link rel="shortcut icon" href="/Public/logo/link.png" type=image/x-icon>
	<!-- 默认js基础库 -->
	<script type="text/javascript" src="/Public/home/js/jquery-1.7.2.min.js"></script>
	<script type="text/javascript" src="/Public/home/js/c.js"></script>
	<script type="text/javascript" src="/Public/lhgdialog/lhgcore.lhgdialog.min.js?skin=iblue"></script>	
	<?php if(empty($keywords)): ?><meta name="keywords" content="<?php echo (C("seo_keywords")); ?>">
	<?php else: ?>
		<meta name="keywords" content="<?php echo ($keywords); ?>"><?php endif; ?>
	<?php if(empty($description)): ?><meta name="description" content="<?php echo (C("seo_description")); ?>">
	<?php else: ?>
		<meta name="description" content="<?php echo ($description); ?>"><?php endif; ?>
</head>
<body>
		<link href="/Public/home/css/ym_login_style.css" rel="stylesheet" type="text/css" media="all" />
		<link href="/Public/home/css/login1.css" rel="stylesheet"/>
		<div class="login">
			<center><img src="/Public/home/images/ym_logo.png" style="width: 150px; margin-bottom: 10px;" /></center>
			<div class="login-top">
					<input type="text"  data-type="mobile" placeholder="输入用户名">
					<input type="password" placeholder="输入密码"  data-type="password">
					<!-- <table>
						<tr>
							<td style="float: left"><input type="text" style="width: 50%;" placeholder="输入验证码"  data-type="verify">
							</td>
							<td style="float: left;vertical-align: middle;margin-left: -17%;margin-top: 1%">
								<span class="yzm">
									<img src="<?php echo U('Home/Public/verify');?>" class="yzm_img" data-action-type="Repeatverify" alt="验证码" title="点击刷新验证码">
								</span>
							</td>
						</tr>
					</table> -->
					<div class="nct" style="width: 80%;">
						<input type="text" placeholder="输入验证码" data-type="verify" autocomplete="off" maxlength="4" class="input ninput" style="width: 40%;float: left">
						<span style="margin-left: 8%;float: left;margin-top: 1%"><img src="<?php echo U('Home/Public/verify');?>" style="vertical-align: middle;" class="yzm_img" data-action-type="Repeatverify" alt="验证码" title="点击刷新验证码"></span>
					</div>
					<div class="forgot">
						<center><input type="button" data-action-type="Logining" value="登　录" style="width: 60%;height: 40px;margin-top: 5%"></center>
					</div>
			</div>
			<!--<div class="login-bottom">
				<h3>  &nbsp;<a href="#"></a>&nbsp </h3>
			</div>-->
			<script type="text/javascript" src="/Public/home/js/md5.js"></script>
			<script type="text/javascript" src="/Public/home/js/user_login.js"></script>
		</div>