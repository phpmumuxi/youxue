<?php
namespace app\common\libs;


// use think\Model;

/**
 * 聚合短信
 */
class SendMsg
{

    // 短信验证
    public function sendMsg($phone,$code)
    {
        $url='http://v.juhe.cn/sms/send';
        $arr['mobile']=$phone;
        $arr['tpl_id']='27019';
        $arr['tpl_value']='%23code%23%3d'.$code;
        $arr['key']='521a966f62289e8797ad592bf5f1c249';

        $re  = $this->getCurl($url,$arr);
        $re = json_decode($re,true);
        return $re['error_code'];
    }

    private function getCurl($url,$arr)
    {
        $str=http_build_query($arr);
        $url=$str?$url.'?'.$str:$url;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$url);
        //定义是否显示状态头 1：显示 ； 0：不显示
        curl_setopt($curl, CURLOPT_HEADER,0);
         // 获取数据返回
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //强制协议为1.0
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        //强制使用IPV4协议解析域名
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        //存取数据
        $file_contents = curl_exec($curl);
        //关闭cURL资源，并且释放系统资源
        curl_close($curl);

        return $file_contents;
    }
}