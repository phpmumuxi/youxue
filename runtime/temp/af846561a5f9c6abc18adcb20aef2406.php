<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:74:"D:\wamp64\web\youxue\public/../application/admin\view\finance\adviser.html";i:1521799115;s:65:"D:\wamp64\web\youxue\public/../application/admin\view\layout.html";i:1521799117;}*/ ?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="renderer" content="webkit|ie-comp|ie-stand">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta http-equiv="Cache-Control" content="no-siteapp" />

<link rel="stylesheet" type="text/css" href="/static/h-ui/css/H-ui.min.css" />
<link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/H-ui.admin.css" />
<link rel="stylesheet" type="text/css" href="/static/lib/Hui-iconfont/1.0.8/iconfont.css" />
<link rel="stylesheet" type="text/css" href="/static/h-ui.admin/skin/default/skin.css" id="skin" />
<!--CSS引用-->

<style type="text/css"> 
.pagination{
    list-style-type: none;
    display: inline-block;
    overflow: auto;
}
.pagination li{
    float: left;
    /*padding: 6px 12px;*/
    border:1px solid #e1e2e3;
    margin: 3px;
}
.pagination li a,.pagination li span{
    display: inline-block;
    padding: 6px 12px;
}
.pagination li:hover{
    border:1px solid #38f;
    background: #f2f8ff;
}
.pagination .active{
    border:1px solid #38f;
}
</style>


<!--主体-->

<title>顾问财务管理</title>
</head>
<body>
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页
	<span class="c-gray en">&gt;</span>
    财务管理
	<span class="c-gray en">&gt;</span>
	财务概况
	<span class="c-gray en">&gt;</span>
	顾问财务管理
	<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace((location.href.split('?'))[0]);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a>
</nav>
<div class="page-container">
	
	<div class="mt-20">
		<table class="table table-border table-bordered table-bg table-sort">
			<thead>
				<tr >
					<th colspan="7" class="text-l">
						<input type="text" name="" id="adviserPhone" placeholder="手机号" style="width:200px" class="input-text" value="<?php if((isset($data['phone']))): ?><?php echo $data['phone']; endif; ?>">
					    <button name="" id="adviserButton" class="btn btn-success" type="button"><i class="Hui-iconfont">&#xe665;</i>搜索
					    </button>
					   <span class="r">
					   		共有数据：<strong><?php echo $lists['total']; ?></strong> 条
					   </span>
				   </th>
				</tr>
				<tr class="text-c">
					<th width="60">序号</th>
					<th width="100">顾问姓名</th>
					<th width="120">手机号</th>
					<th width="100">
						<select id="shopId" style='border:0;background:#f5fafe none repeat scroll 0 0;'>
			                    <option value="0">所属商户</option>
			                    <?php foreach($shops as $v): ?>
			                    <option value="<?php echo $v['id']; ?>" <?php if($v['id']==$data['shopId']): ?>selected='selected'<?php endif; ?>><?php echo $v['name']; ?></option>
			                    <?php endforeach; ?>
			            </select>
					</th>
					<th width="100">
						<select id="schoolId" style='border:0;background:#f5fafe none repeat scroll 0 0;'>
			                    <option value="0">所属校区</option>
			                    <?php foreach($schools as $v): ?>
			                    <option value="<?php echo $v['id']; ?>" <?php if($v['id']==$data['schoolId']): ?>selected='selected'<?php endif; ?>><?php echo $v['name']; ?></option>
			                    <?php endforeach; ?>
			            </select>
					</th>
					<th width="80">总收入</th>
					<!-- <th width="80">修改时间</th> -->
					<th width="80">实际收入</th>
				</tr>
			</thead>
			<tbody>
			<?php if(($lists['data'])): if(is_array($lists['data']) || $lists['data'] instanceof \think\Collection || $lists['data'] instanceof \think\Paginator): $k = 0; $__LIST__ = $lists['data'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?>
				<tr class="text-c">
					<td><?php echo $k; ?></td>
					<td><?php echo $vo['name']; ?></td>
					<td><?php echo $vo['phone']; ?></td>
					<td><?php echo $vo['shopName']; ?></td>
					<td><?php echo $vo['schoolName']; ?></td>
					<td><?php echo $vo['allMoney']; ?></td>		
					<!-- <td><?php if($vo['updateTime']>1000): ?><?php echo date('Y-m-d H:i:s',$vo['updateTime']); else: ?>/<?php endif; ?></td> -->		
					<td><?php if($vo['updateTime']>1000): ?><i class="Hui-iconfont" style="cursor:pointer" onclick="adviserNewMoney('<?php echo $vo['userId']; ?>','<?php echo $vo['updateTime']; ?>')">&#xe715;</i><?php else: ?>/<?php endif; ?></td>		
				</tr>
				<?php endforeach; endif; else: echo "" ;endif; else: ?>
                <tr class="text-c">
                    <td colspan="7">暂无数据</td>
                </tr>
                <?php endif; ?>
				<tr class="text-c">
					<td colspan="7" style="text-align: center;">
					<?php echo $page; ?>
					</td>
				</tr>
			</tbody>
		</table>	
	</div>
</div>


<!--底部--JS引用-->

<script type="text/javascript" src="/static/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="/static/lib/layer/2.4/layer.js"></script>
<script type="text/javascript" src="/static/h-ui/js/H-ui.min.js"></script>
<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.js"></script>

<!--请在下方写此页面业务相关的脚本-->

<script type="text/javascript">
    $('#adviserButton').click(function () {
        var phone = $("#adviserPhone").val();
		var mobile = /^1[345789]{1}\d{9}$/;
        if (phone == '') {
	     	layer.msg('手机号必填!',{icon:2,time:2000});
            return false;
        }        
	  if(phone.length!=11 || !mobile.test(phone)){	  	
	     layer.msg('请填写正确手机号!',{icon:2,time:2000});
	     return false;
	  }
        urlShow();
    });
    $('#shopId,#schoolId').on('change',function(){
        urlShow();
    })
    function urlShow(){
       window.location.href = "/admin/Finance/adviser?phone=" + $('#adviserPhone').val()+'&shopId='+$('#shopId').val()+'&schoolId='+$('#schoolId').val();
    }
    function adviserNewMoney(userId,updateTime) {
    	$.post('/admin/Finance/adviserNewMoney',{userId:userId,updateTime:updateTime},function(res){
    			layer.alert(res.time+' 成为顾问<br/>'+'实际收入为：'+res.money+'元');
    	}) 	
    }
</script>

</body>
</html>