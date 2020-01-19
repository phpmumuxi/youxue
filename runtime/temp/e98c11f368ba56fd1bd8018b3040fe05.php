<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:81:"D:\wamp64\web\youxue\public/../application/admin\view\finance\adminMoneyBill.html";i:1521799115;s:65:"D:\wamp64\web\youxue\public/../application/admin\view\layout.html";i:1521799117;}*/ ?>
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

<title>校区收入详情</title>
</head>
<body>
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页
	<span class="c-gray en">&gt;</span>
    财务管理
	<span class="c-gray en">&gt;</span>
	<a href="<?php echo url('admin/Finance/adminMoney'); ?>">平台收入统计管理</a>
	<span class="c-gray en">&gt;</span>
	校区收入详情
	<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a>
</nav>
<div class="page-container">
	<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l"><a href="<?php echo url('admin/Finance/adminMoney'); ?>" class="btn btn-success radius"> 返回</a> </span><span class="r">共有数据：<strong><?php echo $lists['total']; ?></strong> 条</span></div>
	<div class="mt-20">
		<table class="table table-border table-bordered table-bg table-sort">
			<thead>
				<tr class="text-c">
					<th width="60">序号</th>
					<th width="200">
						<select id="schoolId" style='border:0;background:#f5fafe none repeat scroll 0 0;'>
			                    <option value="0">分校</option>
			                    <?php foreach($schools as $v): ?>
			                    <option value="<?php echo $v['id']; ?>" <?php if($v['id']==$schoolId): ?>selected='selected'<?php endif; ?>><?php echo $v['name']; ?></option>
			                    <?php endforeach; ?>
			         </select>
					</th>
					<th width="100">总收入</th>
					<th width="100">已出账</th>
					<th width="100">未出账</th>
					<th width="80">详情</th>
				</tr>
			</thead>
			<tbody>
			<?php if(($lists['data'])): if(is_array($lists['data']) || $lists['data'] instanceof \think\Collection || $lists['data'] instanceof \think\Paginator): $k = 0; $__LIST__ = $lists['data'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?>
				<tr class="text-c">
					<td><?php echo $k; ?></td>
					<td><?php echo $vo['name']; ?></td>
					<td><?php echo $vo['onMoney']+$vo['offMoney']; ?></td>
					<td><?php echo $vo['onMoney']; ?></td>
					<td><?php echo $vo['offMoney']; ?></td>
					<td><a href="<?php echo url('admin/Finance/adminMoneyDetailBill',['id'=>$vo['id'],'shopId'=>$datas['id'],'sTime'=>$datas['sTime'],'eTime'=>$datas['eTime']]); ?>" title="账单详情"><i class="Hui-iconfont">&#xe63a;</i></a></td>
				</tr>
				<?php endforeach; endif; else: echo "" ;endif; else: ?>
                <tr class="text-c">
                    <td colspan="6">暂无数据</td>
                </tr>
                <?php endif; ?>
				<tr class="text-c">
					<td colspan="6" style="text-align: center;">
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
$('#schoolId').on('change',function(){
     window.location.href = (location.href.split('?'))[0]+"?schoolId="+$('#schoolId').val();
})
</script>

</body>
</html>