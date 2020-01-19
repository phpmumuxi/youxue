<?php
//配置文件
return [
    // 应用Trace
    'app_trace'              => false,
	// 是否自动转换URL中的控制器和操作名
    'url_convert'            => false,
	'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
       'prefix'         => 'think',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
       'auto_start'     => true,
    ],
];