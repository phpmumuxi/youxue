<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:70:"D:\wamp64\web\youxue\public/../application/admin\view\login\index.html";i:1521799116;}*/ ?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="renderer" content="webkit|ie-comp|ie-stand">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta http-equiv="Cache-Control" content="no-siteapp" />

<link href="/static/h-ui/css/H-ui.min.css" rel="stylesheet" type="text/css" />
<link href="/static/h-ui.admin/css/H-ui.login.css" rel="stylesheet" type="text/css" />
<link href="/static/lib/Hui-iconfont/1.0.8/iconfont.css" rel="stylesheet" type="text/css" />

<title>后台登录 - 同城优学</title>

</head>
<body>
<input type="hidden" id="TenantId" name="TenantId" value="" />
<div class="header"></div>
<div class="loginWraper">
  <div id="loginform" class="loginBox">
    <form class="form form-horizontal" action="<?php echo url('admin/login/login'); ?>" method="post" onsubmit='return checks()'>
     <div class="row cl">
        <div class="formControls col-xs-7 col-xs-offset-5">
          <span style="font-size:20px;">管理后台登录</span>
        </div>
      </div>
      <div class="row cl">
        <label class="form-label col-xs-3"><i class="Hui-iconfont">&#xe60d;</i></label>
        <div class="formControls col-xs-8">
          <input name="name" type="text" placeholder="账户" class="input-text size-L">
        </div>
      </div>
      <div class="row cl">
        <label class="form-label col-xs-3"><i class="Hui-iconfont">&#xe60e;</i></label>
        <div class="formControls col-xs-8">
          <input name="password" type="password" placeholder="密码" class="input-text size-L">
        </div>
      </div>
      <div class="row cl">
        <div class="formControls col-xs-8 col-xs-offset-3">
         <button type="submit" class="btn btn-success radius size-L"><i class="icon-ok"></i> 确定</button>
         <button type="reset" class="btn btn-default radius size-L"> 取消</button>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="footer">Copyright © 2017 江苏人之初教育科技有限公司</div>
<script type="text/javascript" src="/static/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="/static/h-ui/js/H-ui.min.js"></script>
<script type="text/javascript" src="/static/lib/layer/2.4/layer.js"></script>
<script type="text/javascript">
function checks(){
  var name = $.trim($("input[type=text]").val());
  var password = $.trim($("input[type=password]").val());
  if(name==''){
     layer.msg('用户名必填！',{icon:2,time:2000});
     return false;
  }
  if(password==''){
     layer.msg('密码必填！',{icon:2,time:2000});
     return false;
  }
  return true;
}
</script>
</body>
</html>