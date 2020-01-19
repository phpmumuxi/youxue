<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:75:"D:\wamp64\web\youxue\public/../application/admin\view\statistics\money.html";i:1521799117;s:65:"D:\wamp64\web\youxue\public/../application/admin\view\layout.html";i:1521799117;}*/ ?>
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


<!--主体-->

<title>销售额统计</title>
</head>
<body>
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页
	<span class="c-gray en">&gt;</span>
    统计概况	
	<span class="c-gray en">&gt;</span>
	销售额统计
	<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace((location.href.split('?'))[0]);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a>
</nav>
<div class="page-container">
	<div class="text-c">
        <span id="timeDay">
        开始时间: <input type="text" id="sTime" placeholder="开始时间" style="width:120px" class="input-text"
                     value="<?php if((isset($data['sTime']))): ?><?php echo $data['sTime']; endif; ?>">
        &nbsp;
        结束时间: <input type="text" id="eTime" placeholder="结束时间" style="width:120px" class="input-text"
                     value="<?php if((isset($data['eTime']))): ?><?php echo $data['eTime']; endif; ?>">
        </span>

        <span id="timeMonth" style='display:none'>
        开始时间: <input type="text" id="ssTime" placeholder="开始时间" style="width:120px" class="input-text"
                     value="<?php if((isset($data['ssTime']))): ?><?php echo $data['ssTime']; endif; ?>">
        &nbsp;
        结束时间: <input type="text" id="eeTime" placeholder="结束时间" style="width:120px" class="input-text"
                     value="<?php if((isset($data['eeTime']))): ?><?php echo $data['eeTime']; endif; ?>">
        </span>&nbsp;

        <span>
            <select id="shopId" style='border:0;background:white none repeat scroll 0 0;width:140px'>
                    <option value="0">选择商户</option>
                    <?php foreach($shops as $v): ?>
                    <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                    <?php endforeach; ?>
            </select>
        </span>
        <span>
            <select id="schoolId" style='border:0;background:white none repeat scroll 0 0;width:140px'>
                    <option value="0">选择校区</option>                    
            </select>
        </span>
        &nbsp;
        <input id="btnSearchOne" type="radio" name="type" value="1" checked> 按天&nbsp;
        <input id="btnSearchTwo" type="radio" name="type" value="2"> 按月
        <button name="" id="btnSearch" class="btn btn-success" type="button">查询</button>
    </div>

    <div class="mt-20">
        <div id="mainContent" style="height: 400px;"></div>
    </div>
</div>


<!--底部--JS引用-->

<script type="text/javascript" src="/static/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="/static/lib/layer/2.4/layer.js"></script>
<script type="text/javascript" src="/static/h-ui/js/H-ui.min.js"></script>
<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.js"></script>

<!--请在下方写此页面业务相关的脚本-->

<script src="/static/lib/laydate/laydate.js"></script>
<script type="text/javascript" src="/static/js/echarts.min.js"></script>
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('mainContent'));
    // 指定图表的配置项和数据
    var option = {
        title: {
            text: '销售额统计'
        },
        color : [ "#ff6262"],
        tooltip: {
            trigger: 'axis',
        },
        // legend: {
        //     data:['总收入']
        // },
        xAxis: {
            name:'日期',
            type : 'category'      
        },
        yAxis: {
            name:'金额',
            type : 'value',
            axisLabel : {
                formatter: '{value}k'
            }
        },
        series: [{
            name: '销售额',
            type: 'line'
        }]
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
</script>
<script type="text/javascript">
$(function(){

    //按天查询
    laydate.render({
        elem: '#sTime'
        ,type: 'date'
        ,format: 'yyyy-MM-dd'
        ,max: '<?php echo $data['eTime']; ?>'              
    });
    laydate.render({
        elem: '#eTime'
        ,type: 'date'
        ,format: 'yyyy-MM-dd'
        ,max: '<?php echo $data['eTime']; ?>' 
    });

    //按月查询
    laydate.render({
        elem: '#ssTime'
        ,type: 'month'
        ,format: 'yyyy-MM'           
    });
    laydate.render({
        elem: '#eeTime'
        ,type: 'month'
        ,format: 'yyyy-MM'        
    });
    
    $('#shopId').on('change',function(){
        var _shopId=parseInt($(this).val());
        $('#schoolId').empty();
        if(_shopId>0){
            $.post("/admin/Statistics/getSchoolAjaxDatas",{id:_shopId},function(res){                
                $('#schoolId').html(res);
            });
        }else{            
            $('#schoolId').html('<option value="0">选择校区</option>');
        }
    })

    //类型改变（天或月）
    $('input[name="type"]').on('change',function(){
        var viewType = $(this).val();    
        if(viewType == 1){
            $("#timeDay").show();
            $("#timeMonth").hide();          
        }else{
            $("#timeDay").hide();
            $("#timeMonth").show();
        }
    })

    //全局首次调用
    loadData(1,"<?php echo $data['sTime']; ?>","<?php echo $data['eTime']; ?>");
})

$('#btnSearch').click(function () {
        var type = $('input[name="type"]:checked').val();

        if (type == 1) {

            var aTime = $('#sTime').val();
            var bTime = $('#eTime').val();

            if (aTime > bTime) {
                layer.msg('开始时间不能大于结束时间!',{icon:2,time:2000});
                return false;
            }

            loadData(1,aTime,bTime);
        } else if (type == 2) {

            var aTime = $('#ssTime').val();
            var bTime = $('#eeTime').val();

            if (aTime > bTime) {
                layer.msg('开始时间不能大于结束时间!',{icon:2,time:2000});
                return false;
            }

            loadData(2,aTime,bTime);
        }
});

function loadData(type,aTime,bTime){
    var shopId = $('#shopId').val();
    var schoolId = $('#schoolId').val();

    myChart.showLoading('default',{text:'加载中'})
    $.ajax({
        url:'/admin/Statistics/dataAjax',
        data:{type:type,aTime:aTime,bTime:bTime,shopId:shopId,schoolId:schoolId,action:'money'},
        dataType:'JSON',
        success:function(res){
           renderData(res);
            myChart.hideLoading();
        }
    })
}

/*显示数据*/
function renderData(data){
    
    myChart.setOption({
        xAxis: {
            data:data['key']
        },
        series:[
            {data:data['all'],itemStyle: {normal:{label:{show: true,formatter: '{c}k'}}}}
        ]
    })
}

</script>

</body>
</html>