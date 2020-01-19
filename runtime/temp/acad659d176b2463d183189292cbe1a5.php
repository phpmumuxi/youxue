<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:83:"D:\wamp64\web\youxue\public/../application/admin\view\arrival_management\index.html";i:1521799115;s:65:"D:\wamp64\web\youxue\public/../application/admin\view\layout.html";i:1521799117;}*/ ?>
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

<title>到账管理</title>
</head>
<body>
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 财务管理 <span class="c-gray en">&gt;</span> 到账管理 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace((location.href.split('?'))[0]);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<div class="text-c">
        <input type="text" name="" id="ArrivalManagementTime" style="width:200px" class="input-text" <?php if(($data['type']==2)): ?>disabled<?php endif; ?>
               value="<?php if((isset($data['start_time']) && $data['type']==1)): ?><?php echo date('Y-m-d',$data['start_time']); endif; ?>">
        <button name="" id="ArrivalButtonToday" class="btn btn-primary-outline radius" type="button">今日未处理账单
        </button>&nbsp;&nbsp;<button name="" id="ArrivalButtonHistory" class="btn btn-success-outline radius" type="button">历史未处理账单
        </button>
    </div>
	<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l" style="margin-top:5px;color:red">备注：默认导出是当天账单</span><span class="r"><a class="btn btn-primary radius" href="/admin/ArrivalManagement/export?type=<?php echo $data['type']; ?>" onclick="return confirm('确认要导出账单数据吗？')" >导出</a>&nbsp;&nbsp;<a class="btn btn-success radius" href="<?php echo url('admin/ArrivalManagement/confirm',['type'=>$data['type']]); ?>" onclick="return confirm('确认账单数据成功吗？')">确认成功</a></span></div>
   <div class="mt-20">
	<table class="table table-border table-bordered table-bg table-admin">
		<thead>
			<tr>
				<th colspan="7"><span class="l"><h4>对公账户</h4></span>
				<span class="r" style="margin-top:20px">共有数据：<strong><?php echo $publicData['total']; ?></strong> 条</span></th>
			</tr>
			<tr class="text-c">
				<th width="100">入账时间</th>
				<th width="100">出账时间</th>
				<th >商户名称</th>
				<th width="100">出账金额</th>
				<th width="140">户名</th>
				<th width="100">付账否</th>
				<th width="80">处理状态</th>
			</tr>
		</thead>
		<tbody>
   		<?php if(($publicData['data'])): if(is_array($publicData['data']) || $publicData['data'] instanceof \think\Collection || $publicData['data'] instanceof \think\Paginator): $i = 0; $__LIST__ = $publicData['data'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
		<tr class="text-c">
   			<td><?php echo $vo['inDate']; ?></td>
			<td><?php echo $vo['outDate']; ?></td>
			<td><?php echo $vo['name']; ?></td>
			<td><?php echo $vo['money']; ?></td>
			<td><?php echo $vo['bankName']; ?></td>
			<td><?php if($vo['isOff'] == '1'): ?>是<?php else: ?>否<?php endif; ?></td>
			<td><?php if($vo['status']==0): ?>未处理<?php elseif($vo['status']==1): ?>待处理<?php elseif($vo['status']==2): ?>已处理<?php else: endif; ?></td>
		</tr>
	   <?php endforeach; endif; else: echo "" ;endif; else: ?>
        <tr class="text-c">
            <td colspan="7">暂无数据</td>
        </tr>
        <?php endif; ?>
        <tr class="text-c">
            <td colspan="7" style="text-align: center"><?php echo $publicPage; ?></td>
        </tr>
		</tbody>
	</table>

	<table class="table table-border table-bordered table-bg table-admin mt-40">
		<thead>
			<tr>
				<th colspan="7"><span class="l"><h4>私人账户</h4></span>
				<span class="r" style="margin-top:20px">共有数据：<strong><?php echo $privateData['total']; ?></strong> 条</span></th>
			</tr>
			<tr class="text-c">
				<th width="100">入账时间</th>
				<th width="100">出账时间</th>
				<th >商户名称</th>
				<th width="100">出账金额</th>
				<th width="140">户名</th>
				<th width="100">付账否</th>
				<th width="80">处理状态</th>
			</tr>
		</thead>
		<tbody>
   		<?php if(($privateData['data'])): if(is_array($privateData['data']) || $privateData['data'] instanceof \think\Collection || $privateData['data'] instanceof \think\Paginator): $i = 0; $__LIST__ = $privateData['data'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
		<tr class="text-c">
   			<td><?php echo $vo['inDate']; ?></td>
			<td><?php echo $vo['outDate']; ?></td>
			<td><?php echo $vo['name']; ?></td>
			<td><?php echo $vo['money']; ?></td>
			<td><?php echo $vo['bankName']; ?></td>
			<td><?php if($vo['isOff'] == '0'): ?>否<?php else: ?>是<?php endif; ?></td>
			<td><?php if($vo['status']==0): ?>未处理<?php elseif($vo['status']==1): ?>待处理<?php else: ?>已处理<?php endif; ?></td>
		</tr>
	   <?php endforeach; endif; else: echo "" ;endif; else: ?>
        <tr class="text-c">
            <td colspan="7">暂无数据</td>
        </tr>
        <?php endif; ?>
        <tr class="text-c">
            <td colspan="7" style="text-align: center"><?php echo $privatePage; ?></td>
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

<script src="/static/lib/laydate/laydate.js"></script>
<script type="text/javascript">
	laydate.render({
	    elem: '#ArrivalManagementTime'
	    ,max: 0
	});

	$('#ArrivalButtonToday').click(function () {
        window.location.href = "/admin/ArrivalManagement/index?type=1&start_time=" + $('#ArrivalManagementTime').val();
    })
    $('#ArrivalButtonHistory').click(function(){
		window.location.href= "/admin/ArrivalManagement/index?type=2";
	})
</script>

</body>
</html>