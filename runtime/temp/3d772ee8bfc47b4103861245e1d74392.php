<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:70:"D:\wamp64\web\youxue\public/../application/admin\view\index\index.html";i:1521799116;}*/ ?>
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
    <script type="text/javascript" src="lib/html5shiv.js"></script>
    <script type="text/javascript" src="lib/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="/static/h-ui/css/H-ui.min.css"/>
    <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/H-ui.admin.css"/>
    <link rel="stylesheet" type="text/css" href="/static/lib/Hui-iconfont/1.0.8/iconfont.css"/>
    <link rel="stylesheet" type="text/css" href="/static/h-ui.admin/skin/default/skin.css" id="skin"/>

    <!--CSS引用-->


    <!--[if IE 6]>
    <script type="text/javascript" src="lib/DD_belatedPNG_0.0.8a-min.js"></script>
    <script>DD_belatedPNG.fix('*');</script>
    <![endif]-->
    <title>同城优学后台管理</title>
    <style type="text/css">
        .childMenu {
            display: none;
        }

        .childMenus span {
            margin-left: 25px;
            color: #666;
        }

        .childMenus i {
            padding-left: 40px;
            color: #b6b7b8;
            cursor: pointer;
        }
        .childIconSpan:hover{
            color: #148cf1;
        }
    </style>
</head>
<body>

<!--头部-->
<header class="navbar-wrapper">
    <div class="navbar navbar-fixed-top">
        <div class="container-fluid cl"><a class="logo navbar-logo f-l mr-10 hidden-xs" href="/">同城优学后台管理</a>
            <a aria-hidden="false" class="nav-toggle Hui-iconfont visible-xs" href="javascript:;">&#xe667;</a>
            <nav id="Hui-userbar" class="nav navbar-nav navbar-userbar hidden-xs">
                <ul class="cl">
                    <li>管理员</li>
                    <li class="dropDown dropDown_hover">
                        <a href="#" class="dropDown_A"><?php echo (\think\Session::get('admin_name') ?: "管理员"); ?> <i
                                class="Hui-iconfont">&#xe6d5;</i></a>
                        <ul class="dropDown-menu menu radius box-shadow">
                            <li><a href="javascript:;"
                                   onclick="changePassword('密码修改','<?php echo url('admin/login/passEdit'); ?>')">密码修改</a></li>
                            <li><a href="<?php echo url('/admin/login/logout'); ?>">退出</a></li>
                        </ul>
                    </li>
                    <li id="Hui-skin" class="dropDown right dropDown_hover"><a href="javascript:;" class="dropDown_A" title="换肤"><i class="Hui-iconfont" style="font-size:18px">&#xe62a;</i></a>
                        <ul class="dropDown-menu menu radius box-shadow">
                            <li><a href="javascript:;" data-val="default" title="默认（黑色）">默认（黑色）</a></li>
                            <li><a href="javascript:;" data-val="blue" title="蓝色">蓝色</a></li>
                            <li><a href="javascript:;" data-val="green" title="绿色">绿色</a></li>
                            <li><a href="javascript:;" data-val="red" title="红色">红色</a></li>
                            <li><a href="javascript:;" data-val="yellow" title="黄色">黄色</a></li>
                            <li><a href="javascript:;" data-val="orange" title="橙色">橙色</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<!--侧边栏-->
<aside class="Hui-aside">
    <div class="menu_dropdown bk_2">
        <?php if(!(empty($menus) || (($menus instanceof \think\Collection || $menus instanceof \think\Paginator ) && $menus->isEmpty()))): if(is_array($menus) || $menus instanceof \think\Collection || $menus instanceof \think\Paginator): $i = 0; $__LIST__ = $menus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
        <dl class="menu-article">
            <dt>
                <i class="Hui-iconfont"><?php echo htmlspecialchars_decode($vo['icon']); ?></i> 
                <span class="menuBig"><?php echo $vo['name']; ?></span>
                <span class="label label-danger radius" style="display: none;vertical-align:super;">0</span>
                <i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i>
            </dt>
            <dd>
                <ul>
                    <?php if(!(empty($vo['childs']) || (($vo['childs'] instanceof \think\Collection || $vo['childs'] instanceof \think\Paginator ) && $vo['childs']->isEmpty()))): if(is_array($vo['childs']) || $vo['childs'] instanceof \think\Collection || $vo['childs'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['childs'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;if(empty($val['childs']) || (($val['childs'] instanceof \think\Collection || $val['childs'] instanceof \think\Paginator ) && $val['childs']->isEmpty())): ?>
                    <li>
                        <a data-href="<?php echo url($val['url']); ?>" data-title="<?php echo $val['name']; ?>" href="javascript:void(0)">
                            <?php echo $val['name']; ?>
                            <span some-va="<?php echo $val['url']; ?>" class="someVa label label-danger radius" style="display: none;vertical-align: super;">0</span>
                        </a>
                    </li>
                    <?php else: ?>
                    <li class='childMenus'>
                        <span class="childicon childIconSpan" style='cursor:pointer;'><?php echo $val['name']; ?></span>
                        <i class="Hui-iconfont childicon" id='menuIcon'>&#xe6d5;</i>
                        <ul class='childMenu'>
                            <?php if(is_array($val['childs']) || $val['childs'] instanceof \think\Collection || $val['childs'] instanceof \think\Paginator): $i = 0; $__LIST__ = $val['childs'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                            <li>
                                <a data-href="<?php echo url($v['url']); ?>" data-title="<?php echo $v['name']; ?>" href="javascript:void(0)"><?php echo $v['name']; ?></a>
                            </li>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                    </li>
                    <?php endif; endforeach; endif; else: echo "" ;endif; endif; ?>
                </ul>
            </dd>
        </dl>
        <?php endforeach; endif; else: echo "" ;endif; endif; ?>
        
    </div>
    <div class="mt-30">
    </div>
</aside>

<div class="dislpayArrow hidden-xs"><a class="pngfix" href="javascript:void(0);" onClick="displaynavbar(this)"></a>
</div>

<!--主体-->


<section class="Hui-article-box">
    <div id="Hui-tabNav" class="Hui-tabNav hidden-xs">
        <div class="Hui-tabNav-wp">
            <ul id="min_title_list" class="acrossTab cl">
                <li class="active">
                    <span title="网站概览" data-href="<?php echo url('admin/Index/index'); ?>">网站概览</span>
                    <em></em></li>
            </ul>
        </div>
        <div class="Hui-tabNav-more btn-group"><a id="js-tabNav-prev" class="btn radius btn-default size-S" href="javascript:;"><i class="Hui-iconfont">&#xe6d4;</i></a><a id="js-tabNav-next" class="btn radius btn-default size-S" href="javascript:;"><i class="Hui-iconfont">&#xe6d7;</i></a>
        </div>
    </div>
    <div id="iframe_box" class="Hui-article">
        <div class="show_iframe">
            <div style="display:none" class="loading"></div>
            <iframe scrolling="yes" frameborder="0" src="<?php echo url('/admin/Index/welcome'); ?>"></iframe>
        </div>

    </div>
</section>

<div class="contextMenu" id="Huiadminmenu">
    <ul>
        <li id="closethis">关闭当前</li>
        <li id="closeall">关闭全部</li>
    </ul>
</div>

<!--底部JS引用-->

<script type="text/javascript" src="/static/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="/static/lib/layer/2.4/layer.js"></script>
<script type="text/javascript" src="/static/h-ui/js/H-ui.min.js"></script>
<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.js"></script>

<!--请在下方写此页面业务相关的脚本-->

<script type="text/javascript" src="/static/lib/jquery.contextmenu/jquery.contextmenu.r2.js"></script>
<script type="text/javascript">
    $(function () {
        var flag = true;
        $('.childicon').each(function () {
            $(this).click(function () {
                if (flag) {
                    $(this).parents('li').find('i').html("&#xe6d6;");
                    $(this).parents('li').find('.childMenu').show();
                    flag = false;
                } else {
                    $(this).parents('li').find('i').html("&#xe6d5;");
                    $(this).parents('li').find('.childMenu').hide();
                    flag = true;
                }
            })
        })
    })

    /*密码修改*/
    function changePassword(title, url) {
        layer_show(title, url,'780','400');
    }

    $('.menuBig').each(function (k, v) {
        var t = $(this).text();
        var tt = $(this);
        if (t == '消息提醒') {
            $.post('/school/OrderHandle/orderHandleAjax',{},function (res) {
//                console.log(res);
                tt.next().text(res.all);

                $('.someVa').each(function (k,v) {
                    var arr = [
                        'school/OrderHandle/orderHandleCourse',
                        'school/OrderHandle/orderHandleGroup',
                        'school/OrderHandle/orderHandleFirstGive',
                        'school/OrderHandle/orderHandleStarCourse',
                        'school/OrderHandle/orderHandleStarExchange',
                        'school/OrderHandle/orderHandleFreeCourse',
                        'school/OrderHandle/orderHandleWrkCourse'
                    ];
                    var svt = $(this);
                    var sv = $(this).attr('some-va');
                    if(sv == arr[0]){
                        svt.text(res.numA);
                        svt.show();
                    }else if(sv == arr[1]){
                        svt.text(res.numB);
                        svt.show();
                    }else if(sv == arr[2]){
                        svt.text(res.numE);
                        svt.show();
                    }else if(sv == arr[3]){
                        svt.text(res.numD);
                        svt.show();
                    }else if(sv == arr[4]){
                        svt.text(res.numF);
                        svt.show();
                    }else if(sv == arr[5]){
                        svt.text(res.numC);
                        svt.show();
                    }else if(sv == arr[6]){
                        svt.text(res.numG);
                        svt.show();
                    }
                });
            },'json');
            $(this).next().show();
        }
    });

    setTimeout(function () {
        $('.menuBig').each(function (k, v) {
            var t = $(this).text();
            var tt = $(this);
            if (t == '消息提醒') {
                $.post('/school/OrderHandle/orderHandleAjax',{},function (res) {
//                console.log(res);
                    tt.next().text(res.all);

                    $('.someVa').each(function (k,v) {
                        var arr = [
                            'school/OrderHandle/orderHandleCourse',
                            'school/OrderHandle/orderHandleGroup',
                            'school/OrderHandle/orderHandleFirstGive',
                            'school/OrderHandle/orderHandleStarCourse',
                            'school/OrderHandle/orderHandleStarExchange',
                            'school/OrderHandle/orderHandleFreeCourse',
                            'school/OrderHandle/orderHandleWrkCourse'
                        ];
                        var svt = $(this);
                        var sv = $(this).attr('some-va');
                        if(sv == arr[0]){
                            svt.text(res.numA);
                            svt.show();
                        }else if(sv == arr[1]){
                            svt.text(res.numB);
                            svt.show();
                        }else if(sv == arr[2]){
                            svt.text(res.numE);
                            svt.show();
                        }else if(sv == arr[3]){
                            svt.text(res.numD);
                            svt.show();
                        }else if(sv == arr[4]){
                            svt.text(res.numF);
                            svt.show();
                        }else if(sv == arr[5]){
                            svt.text(res.numC);
                            svt.show();
                        }else if(sv == arr[6]){
                            svt.text(res.numG);
                            svt.show();
                        }
                    });
                },'json');
                $(this).next().show();
            }
        });
    },60000 * 30);
</script>
</body>
</html>