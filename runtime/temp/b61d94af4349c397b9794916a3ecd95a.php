<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:78:"D:\wamp64\web\youxue\public/../application/admin\view\user_manage\userAdd.html";i:1521799117;}*/ ?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="Bookmark" href="/favicon.ico">
    <link rel="Shortcut Icon" href="/favicon.ico"/>
    <!--[if lt IE 9]>
    <script type="text/javascript" src="/static/lib/html5shiv.js"></script>
    <script type="text/javascript" src="/static/lib/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="/static/h-ui/css/H-ui.min.css"/>
    <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/H-ui.admin.css"/>
    <link rel="stylesheet" type="text/css" href="/static/lib/Hui-iconfont/1.0.8/iconfont.css"/>
    <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/skin/default/skin.css" id="skin"/>
    <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/style.css"/>
    <!--[if IE 6]>
    <script type="text/javascript" src="/static/lib/DD_belatedPNG_0.0.8a-min.js"></script>
    <script>DD_belatedPNG.fix('*');</script>
    <![endif]-->
    <title>用户录入列表</title>

    <style>
        .layui-layer-btn0 {
            background-color: red !important;
            border-color: red !important;
            color: #fff;
        }
    </style>
</head>
<body>

<nav class="breadcrumb">
    <i class="Hui-iconfont">&#xe67f;</i> 首页
    <span class="c-gray en">&gt;</span> 用户录入管理
    <span class="c-gray en">&gt;</span> 用户录入列表
    <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px"
       href="javascript:location.href = '/admin/userManage/userAdd';" title="刷新">
        <i class="Hui-iconfont">&#xe68f;</i>
    </a>
</nav>

<div class="page-container">

    <div class="cl pd-5 bg-1 bk-gray">
        <span class="l">
            <a href="javascript:;" class="btn btn-primary radius" id="newAdd">
                <i class="Hui-iconfont">&#xe600;</i> 录入用户
            </a>
        </span>
    </div>

    <div class="mt-20">
        <table class="table table-border table-bordered table-bg table-admin">
            <thead>
            <tr>
                <th scope="col" colspan="15">录入用户列表</th>
            </tr>
            <tr class="text-c">
                <th width="5">序号</th>
                <th width="20">姓名</th>
                <th width="10">电话</th>
                <th width="20">宝宝姓名或昵称</th>
                <th width="10">宝宝出生日期</th>
                <th width="10">宝宝性别</th>
                <th width="10">创建时间</th>
                <th width="20">商户</th>
                <th width="20">校区</th>
                <th width="10">状态</th>
                <th width="20">操作</th>
            </tr>
            </thead>

            <tbody>
            <?php if(($data['data'])): if(is_array($data['data']) || $data['data'] instanceof \think\Collection || $data['data'] instanceof \think\Paginator): $k = 0; $__LIST__ = $data['data'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?>
            <tr class="text-c">
                <td><?php echo $k; ?></td>
                <td><?php echo $vo['name']; ?></td>
                <td><?php echo $vo['phone']; ?></td>
                <td>
                    <?php if(($vo['babyNickName'])): ?>
                    <?php echo $vo['babyNickName']; else: ?>
                    无
                    <?php endif; ?>
                </td>

                <td>
                    <?php if(($vo['babyBirth'])): ?>
                    <?php echo $vo['babyBirth']; else: ?>
                    无
                    <?php endif; ?>
                </td>

                <td>
                    <?php if(($vo['babySex'] == 1)): ?>
                    男
                    <?php else: ?>
                    女
                    <?php endif; ?>
                </td>

                <td><?php echo date('Y-m-d',$vo['createTime']); ?></td>

                <td>
                    <?php if(($vo['shopName'])): ?>
                    <?php echo $vo['shopName']; else: ?>
                    无
                    <?php endif; ?>
                </td>

                <td>
                    <?php if(($vo['schoolName'])): ?>
                    <?php echo $vo['schoolName']; else: ?>
                    无
                    <?php endif; ?>
                </td>

                <td>
                    <?php if(($vo['isDone'] == 1)): ?>
                    已导入到用户
                    <?php else: ?>
                    未导入
                    <?php endif; ?>
                </td>

                <td>
                    <?php if(($vo['isDone'] == 0)): ?>
                    <a href="javascript:;" title="编辑" class="someEdit" va="<?php echo $vo['id']; ?>" vb="<?php echo $vo['name']; ?>"
                       vc="<?php echo $vo['phone']; ?>">
                        <i class="Hui-iconfont Hui-iconfont-edit f-16"></i>
                    </a>
                    <span>&nbsp;&nbsp;</span>
                    <a href="javascript:;" title="删除" class="someDel" va="<?php echo $vo['id']; ?>">
                        <i class="Hui-iconfont Hui-iconfont-del3 f-16"></i>
                    </a>
                    <span>&nbsp;&nbsp;</span>
                    <a href="javascript:;" title="保存" class="someSave" va="<?php echo $vo['id']; ?>">
                        <i class="Hui-iconfont Hui-iconfont-daoru f-16"></i>
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endif; else: echo "" ;endif; if($pages): ?>
            <tr class="text-c">
                <td colspan="15" style="text-align: center;">
                    <?php echo $pages; ?>
                </td>
            </tr>
            <?php endif; else: ?>
            <tr class="text-c">
                <td colspan="15" style="text-align: center;">
                    暂无数据
                </td>
            </tr>
            <?php endif; ?>
            </tbody>

        </table>
    </div>

</div>

<script type="text/javascript" src="/static/js/jquery-3.2.1.js"></script>
<script type="text/javascript" src="/static/js/layer/layer.js"></script>
<script type="text/javascript" src="/static/lib/laydate/laydate.js"></script>
<script type="text/javascript">

    // 删除录入的信息
    $('.someDel').click(function () {
        var id = $(this).attr('va');
        if (!id) {
            return false;
        }
        var t = $(this);
        layer.confirm('确认删除该条信息?',
            {
                title: '提示',
                btn: ['确认','取消']
            },function () {
                $.post('/admin/userManage/userDelete', {id: id}, function (res) {
                    if (res.status == 'ok') {
                        layer.msg('操作成功!');
                        t.parent().parent().hide();
                    } else {
                        layer.msg('操作失败!');
                    }
                }, 'json');
            }
        );
    });

    // 修改录入的信息
    $('.someEdit').click(function () {
        var id = $(this).attr('va');
        if(!id){
            return false;
        }

        $.post('/admin/UserManage/getUserInfo',{id: id},function (res) {

            var _html = '<div style="margin: 15px;">'
                + '<label for="newName" style="font-weight: bold;"><span style="color: #ff0000;">*</span>姓名:</label>'
                + '<input id="newName" class="input-text radius size-M" type="text" name="name" value="'+ (res.data.name ? res.data.name : '') +'" placeholder="请填写姓名">'
                + '<br><br><label for="newPhone" style="font-weight: bold;"><span style="color: #ff0000;">*</span>手机号:</label>'
                + '<input id="newPhone" class="input-text radius size-M" type="text" name="phone" value="'+ (res.data.phone ? res.data.phone : '') +'" placeholder="请填写手机号">'
                + '<br><br><label for="newBabyName" style="font-weight: bold;">宝宝名称:</label>'
                + '<input id="newBabyName" class="input-text radius size-M" type="text" name="phone" value="'+ (res.data.babyNickName ? res.data.babyNickName : '') +'" placeholder="请填写宝宝姓名或昵称">'
                + '<br><br><label for="newBabyBirth" style="font-weight: bold;">宝宝出生日期: <span style="color: #b3b3b3;">(例: 2017-11-01)</span></label>'
                + '<input id="newBabyBirth" class="input-text radius size-M" type="text" name="phone" value="'+ (res.data.babyBirth ? res.data.babyBirth : '') +'" placeholder="请填写宝宝出生日期">'
                + '<br><br><label for="newBabySex" style="font-weight: bold;">宝宝性别:</label>'
                + '&nbsp;&nbsp;<input type="radio" id="babySex1" name="babySex" value="1" '+ (res.data.babySex == 1 ? 'checked' : '') +'><label for="babySex1">男</label>'
                + '&nbsp;&nbsp;&nbsp;<input type="radio" id="babySex2" name="babySex" value="2" '+ (res.data.babySex == 2 ? 'checked' : '') +'><label for="babySex2">女</label>'
                + '<input id="newSave" class="btn radius btn-warning" type="button" value="确认" style="position: absolute;right: 15px;bottom: 10px;width: 5em;">'
                + '</div>';

            layer.open({
                type: 1,
                title: '修改录入的用户信息',
                skin: 'layui-layer-rim', //加上边框
                area: ['420px', '450px'], //宽高
                content: _html
            });

            laydate.render({
                elem: '#newBabyBirth'
            });

            $('#newSave').click(function () {
                var name = $('#newName').val();
                if (!name) {
                    layer.msg('请填写用户姓名!');
                    return false;
                }
                var phone = $('#newPhone').val();
                if (!phone) {
                    layer.msg('请填写用户手机号!');
                    return false;
                }
                if (!/^1(3|4|5|6|7|8|9)\d{9}$/.test(phone)) {
                    layer.msg('请填写正确的用户手机号!');
                    return false;
                }

                var babyName = $('#newBabyName').val();
                var babyBirth = $('#newBabyBirth').val();
                var babySex = $('input[name="babySex"]:checked').val();

                $.post('/admin/userManage/userUpdate', {
                    id: id,
                    name: name,
                    phone: phone,
                    babyName: babyName,
                    babyBirth: babyBirth,
                    babySex: babySex
                }, function (res) {
                    if (res.status == 'ok') {
                        layer.closeAll();
                        layer.msg('操作成功!');
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    }else if (res.status == 'errHad') {
                        layer.msg('当前手机号已经是App用户!');
                        return false;
                    } else {
                        layer.msg('操作失败!');
                    }
                }, 'json');
            });
        },'json');
    });

    // 新增录入信息
    $('#newAdd').click(function () {
        var _html = '<div style="margin: 15px;">'
            + '<label for="newName" style="font-weight: bold;"><span style="color: #ff0000;">*</span>姓名:</label>'
            + '<input id="newName" class="input-text radius size-M" type="text" name="name" value="" placeholder="请填写姓名">'
            + '<br><br><label for="newPhone" style="font-weight: bold;"><span style="color: #ff0000;">*</span>手机号:</label>'
            + '<input id="newPhone" class="input-text radius size-M" type="text" name="phone" value="" placeholder="请填写手机号">'
            + '<br><br><label for="newBabyName" style="font-weight: bold;">宝宝名称:</label>'
            + '<input id="newBabyName" class="input-text radius size-M" type="text" name="phone" value="" placeholder="请填写宝宝姓名或昵称">'
            + '<br><br><label for="newBabyBirth" style="font-weight: bold;">宝宝出生日期: <span style="color: #b3b3b3;">(例: 2017-11-01)</span></label>'
            + '<input id="newBabyBirth" class="input-text radius size-M" type="text" name="phone" value="" placeholder="请填写宝宝出生日期">'
            + '<br><br><label for="newBabySex" style="font-weight: bold;">宝宝性别:</label>'
            + '&nbsp;&nbsp;<input type="radio" id="babySex1" name="babySex" value="1" checked><label for="babySex1">男</label>'
            + '&nbsp;&nbsp;&nbsp;<input type="radio" id="babySex2" name="babySex" value="2"><label for="babySex2">女</label>'
            + '<input id="newSave" class="btn radius btn-warning" type="button" value="确认" style="position: absolute;right: 15px;bottom: 10px;width: 5em;">'
            + '</div>';

        layer.open({
            type: 1,
            title: '补录入用户',
            skin: 'layui-layer-rim', //加上边框
            area: ['420px', '450px'], //宽高
            content: _html
        });

        laydate.render({
            elem: '#newBabyBirth'
        });

        $('#newSave').click(function () {
            var name = $('#newName').val();
            if (!name) {
                layer.msg('请填写用户姓名!');
                return false;
            }
            var phone = $('#newPhone').val();
            if (!phone) {
                layer.msg('请填写用户手机号!');
                return false;
            }
            if (!/^1(3|4|5|6|7|8|9)\d{9}$/.test(phone)) {
                layer.msg('请填写正确的用户手机号!');
                return false;
            }

            var babyName = $('#newBabyName').val();
            var babyBirth = $('#newBabyBirth').val();
            var babySex = $('input[name="babySex"]:checked').val();

            $.post('/admin/userManage/userSave', {name: name, phone: phone,babyName: babyName,babyBirth: babyBirth,babySex: babySex}, function (res) {
                if (res.status == 'ok') {
                    layer.closeAll();
                    layer.msg('操作成功!');
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                }else if (res.status == 'errHad') {
                    layer.msg('当前手机号已经是App用户!');
                    return false;
                } else {
                    layer.msg('操作失败!');
                }
            }, 'json');
        });
    });

    // 保存到用户
    $('.someSave').on('click',function () {
        var id = $(this).attr('va');
        if(!id){
            return false;
        }
        layer.confirm('确认导入用户?',
            {
                title: '提示',
                btn: ['确认','取消']
            },function () {
                $.post('/admin/UserManage/doToUser',{id: id},function (res) {
                    if(res.status == 'ok'){
                        layer.closeAll();
                        layer.msg('操作成功!');
                        setTimeout(function () {
                            window.location.reload();
                        },500);
                    }else{
                        layer.msg('操作失败!');
                    }
                },'json');
            },function () {
                
            }
        );
    });
</script>

</body>
</html>