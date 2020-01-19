<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:87:"D:\wamp64\web\youxue\public/../application/admin\view\finance\adminMoneyDetailBill.html";i:1521799115;s:65:"D:\wamp64\web\youxue\public/../application/admin\view\layout.html";i:1521799117;}*/ ?>
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

<title>账单明细</title>
</head>
<body>
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页
	<span class="c-gray en">&gt;</span>
    财务管理
	<span class="c-gray en">&gt;</span>
	<a href="<?php echo url('admin/Finance/adminMoney'); ?>">平台收入统计管理</a>
	<span class="c-gray en">&gt;</span>
	账单明细
	<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a>
</nav>
<div class="page-container">
	<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l"><a href="<?php echo url('admin/Finance/adminMoneyBill',['id'=>$datas['shopId'],'sTime'=>$datas['sTime'],'eTime'=>$datas['eTime']]); ?>" class="btn btn-success radius" type="button"> 返回</a> </span><span class="r"></span></div>
	<div class="mt-20">
		<table class="table table-border table-bordered table-bg table-sort">
			<thead>
			<tr>
				<th colspan="9"><span class="l"><h5>优选课程</h5></span>
				<span class="r" style="margin-top:10px">共有数据：<strong><?php echo $lists['total']; ?></strong> 条</span></th>
			</tr>
				<tr class="text-c">
					<th width="40">序号</th>
					<th width="100">用户</th>
					<th width="100">手机号</th>
					<th width="130">订单号</th>
					<th width="130">课程名称</th>
					<th width="80">订单金额</th>
					<th width="80">商户结算金额</th>
					<th width="100">
						<select id="userStatus" style='border:0;background:#f5fafe none repeat scroll 0 0;'>
			                    <?php foreach($userStatus as $k=>$v): ?>
			                    <option value="<?php echo $k; ?>" <?php if($k==$status): ?>selected='selected'<?php endif; ?>><?php echo $v; ?></option>
			                    <?php endforeach; ?>
			         </select>
					</th>
					<th width="110">签约时间</th>
				</tr>
			</thead>
			<tbody>
			<?php if(($lists['data'])): if(is_array($lists['data']) || $lists['data'] instanceof \think\Collection || $lists['data'] instanceof \think\Paginator): $k = 0; $__LIST__ = $lists['data'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?>
				<tr class="text-c">
					<td><?php echo $k; ?></td>
					<td><?php echo $vo['userName']; ?></td>
					<td><?php echo $vo['userPhone']; ?></td>
					<td><?php echo $vo['orderNo']; ?></td>
					<td><?php echo $vo['name']; ?></td>
					<td><?php echo $vo['money']; ?></td>
					<td><?php echo $vo['shopMoney']; ?></td>
					<td><?php if($vo['isOldCustom']==1): ?>老用户<?php else: ?>新用户<?php endif; ?></td>
					<td><?php echo date("Y-m-d H:i",$vo['signDate']); ?></td>
				</tr>
				<?php endforeach; endif; else: echo "" ;endif; else: ?>
                <tr class="text-c">
                    <td colspan="9">暂无数据</td>
                </tr>
                <?php endif; ?>
				<tr class="text-c">
					<td colspan="9" style="text-align: center;">
					<?php echo $page; ?>
					</td>
				</tr>
			</tbody>
		</table>

		<table class="table table-border table-bordered table-bg table-admin mt-40">
		<thead>
			<tr>
				<th colspan="8"><span class="l"><h5>万人砍课程</h5></span>
				<span class="r" style="margin-top:10px">共有数据：<strong><?php echo $lists1['total']; ?></strong> 条</span></th>
			</tr>
			<tr class="text-c">
				<th width="40">序号</th>
				<th width="100">用户</th>
				<th width="100">手机号</th>
				<th width="120">课程包名称</th>
				<th width="120">活动名称</th>
				<th width="140">订单号</th>
				<th width="100">订单金额</th>
				<th width="100">签约时间</th>
			</tr>
		</thead>
		<tbody>
   		<?php if(($lists1['data'])): if(is_array($lists1['data']) || $lists1['data'] instanceof \think\Collection || $lists1['data'] instanceof \think\Paginator): $k = 0; $__LIST__ = $lists1['data'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?>
		<tr class="text-c">
			<td><?php echo $k; ?></td>
   			<td><?php echo $vo['userName']; ?></td>
			<td><?php echo $vo['userPhone']; ?></td>
			<td><?php echo $vo['className']; ?></td>
			<td><?php echo $vo['activityName']; ?></td>
			<td><?php echo $vo['orderNo']; ?></td>
			<td><?php echo $vo['money']; ?></td>
			<td><?php echo date("Y-m-d H:i",$vo['signTime']); ?></td>
		</tr>
	   <?php endforeach; endif; else: echo "" ;endif; else: ?>
        <tr class="text-c">
            <td colspan="8">暂无数据</td>
        </tr>
        <?php endif; ?>
        <tr class="text-c">
            <td colspan="8" style="text-align: center"><?php echo $page1; ?></td>
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
    $('#userStatus').on('change',function(){
    	var _status = $(this).val();
        window.location.href =(location.href.split('?'))[0]+"?status=" + _status;
    })
</script>

</body>
</html>