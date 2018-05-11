<?php if (!defined('THINK_PATH')) exit();?><div class="pageContent">
	<form action="/index.php/Home/System/role_access/navTabId/System" method="post" name="form" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone)">
		<input type="hidden" name="post[submit]" value="role_access">
		<input type="hidden" name="post[role_id]" value="<?php echo ($role_id); ?>" />
		<table class="table" width="100%" layoutH="62">
			<tbody><?php echo ($html_tree); ?></tbody>
		</table>
		<div class="formBar">
			<div class="f_r">
				<button class="ch-btn-skin ch-btn-small ch-icon-ok">确 定</button>
				<button class="ch-btn-skin ch-btn-small ch-icon-remove close">取 消</button>
			</div>
		</div>
	</form>
</div>
<script type="text/javascript">
	/*
	$(document).ready(function() {
		//树配置
		$("#tree").treeTable({
			expandable: true,
		});
	});*/
	function checkmenu(obj){
		var chk = $("input[type='checkbox']");
		var count = chk.length;
		var num = chk.index(obj);
		var level_top = level_bottom =  chk.eq(num).attr('level')
		for (var i=num; i>=0; i--){
			var le = chk.eq(i).attr('level');
			if(eval(le) < eval(level_top)){
				chk.eq(i).attr("checked",'checked');
				var level_top = level_top-1;
			}
		}
		for (var j=num+1; j<count; j++){
			var le = chk.eq(j).attr('level');
			if(chk.eq(num).attr("checked")=='checked'){
				if(eval(le) > eval(level_bottom)) chk.eq(j).attr("checked",'checked');
				else if(eval(le) == eval(level_bottom)) break;
			}else{
				if(eval(le) > eval(level_bottom)) chk.eq(j).attr("checked",false);
				else if(eval(le) == eval(level_bottom)) break;
			}
		}
	}
</script>