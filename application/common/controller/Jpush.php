<?php
/**
 * User: LiuTong
 * Date: 2017-09-18
 * Time: 13:06
 */

namespace app\common\controller;

class Jpush
{
    private $app_key = 'cee4f88299e6532729b4950a';
    private $master_secret = '87cef42daa72f319ad60a4fb';
    private $url = 'https://api.jpush.cn/v3/push';

    public function __construct($app_key = null, $master_secret = null, $url = null)
    {
        if ($app_key) {
            $this->app_key = $app_key;
        }
        if ($master_secret) {
            $this->master_secret = $master_secret;
        }
        if ($url) {
            $this->url = $url;
        }
    }

    public function push($receiver = 'all', $content = '', $arr, $type, $m_time = '86400')
    {
        $base64 = base64_encode("$this->app_key:$this->master_secret");
        $header = array("Authorization:Basic $base64", "Content-Type:application/json");
        $data = array();
        //目标用户终端手机的平台类型android,ios,winphone
        $data['platform'] = 'all';
        //目标用户
        $data['audience'] = $receiver;
        //“通知”对象，是一条推送的实体内容对象之一（另一个是“消息”），是会作为“通知”推送到客户端的。
        $data['notification'] = array(
            //统一的模式--标准模式
            "alert" => $content,
            //安卓自定义
            "android" => array(
                "alert" => $content,
                "title" => "",
                "builder_id" => 1,
                // "extras"=>array("type"=>$m_type, "txt"=>$m_txt)
                "extras" => array('txt_content' => $arr, 'type' => $type)
            ),
            //ios的自定义
            "ios" => array(
                "alert" => $content,
                "badge" => "1",
                "sound" => "default",
                // "extras"=>array("type"=>$m_type, "txt"=>$m_txt)
                "extras" => array('txt_content' => $arr, 'type' => $type)
            )
        );

        //苹果自定义---为了弹出值方便调测
        //应用内消息。或者称作：自定义消息，透传消息
        //此部分内容不会展示到通知栏上，JPush SDK 收到消息内容后透传给 App。需要 App 自行处理
        $data['message'] = array(
            "msg_content" => $content,
            // "extras"=>array("type"=>$m_type, "txt"=>$m_txt)
            "extras" => array('txt_content' => $arr, 'type' => $type)
        );

        /**
         * sendno 纯粹用来作为 API 调用标识，API 返回时被原样返回
         * time_to_live 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到。
         * apns_production 布尔类型   指定 APNS 通知发送环境：0开发环境，1生产环境。或者传递false和true JPush 官方 API LIbrary (SDK) 默认设置为推送 “开发环境”。
         */
        //附加选项
        $data['options'] = array(
            "sendno" => time(),
            "time_to_live" => $m_time,
            "apns_production" => false,
        );
//        return $data;
        $param = json_encode($data);
        $res = $this->push_curl($param, $header);

        if ($res) {
            //得到返回值--成功已否后面判断
            return $res;
        } else {
            //未得到返回值--返回失败
            return false;
        }
    }

    public function push_curl($param = "", $header = "")
    {
        if (empty($param)) {
            return false;
        }
        $postUrl = $this->url;
        $curlPost = $param;
        $ch = curl_init();                                                  //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl);                    //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);                //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);                  //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);              // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);                                             //运行curl
        curl_close($ch);
        return $data;
    }
}