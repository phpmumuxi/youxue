<?php

/**
 * 用户模版
 */


namespace app\common\libs;

use core;

class BaiDu
{

    //API控制台申请得到的ak（此处ak值仅供验证参考使用）
    public $ak = 'hayLMndRxAB79dKa17tqG5B5';

    //应用类型为for server, 请求校验方式为sn校验方式时，系统会自动生成sk，可以在应用配置-设置中选择Security Key显示进行查看（此处sk值仅供验证参考使用）
    public $sk = 'SYw22A8cdIGOsAi947qECExwqaT6RhHh';

    //以Geocoding服务为例，地理编码的请求url，参数待填
    public $url = "http://api.map.baidu.com";


    // 坐标转换api(基本用不到)
    public function geoconv()
    {
        //get请求uri前缀
        $uri = '/geoconv/v1/';


        $arr = [
            'coords' => '114.21892734521,29.575429778924',
            'ak' => $this->ak
        ];

        $re = $this->getResult($uri, $arr);

        //输出完整请求的url（仅供参考验证，故不能正常访问服务）

    }

    /**
     * [getLocation 根据地址查询ip]
     * @param  [type] $address [地址名称]
     * @return [array]          [ip地址]
     */
    public function getLocation($address)
    {
        $uri = '/geocoder/v2/';

        $arr = [
            'output' => 'json',
            'ak' => $this->ak,
            'address'=>$address,
        ];

        $re = $this->getResult($uri, $arr);

        if($re['status'] != 0){
            return false;
        }
        // lat<纬度>,lng<经度>
        return $re['result']['location'];
    }


    /**
     * [getAddress 根据定位获取地址信息]
     * @param  [type] $lat [纬度]
     * @param  [type] $lng [经度]
     * @return [type]      [地址信息]
     */
    public function getAddress($lat, $lng)
    {
        $uri = '/geocoder/v2/';
        $arr = [
            'output' => 'json',
            'ak' => $this->ak,
            'location'=>$lat.','.$lng,
        ];
        $re = $this->getResult($uri, $arr);

        if($re['status'] != 0){
            return false;
        }
        return $re['result']['formatted_address'];
    }

    /**
     * [getAddress 根据定位获取城市code]
     * @param  [type] $lat [纬度]
     * @param  [type] $lng [经度]
     * @return [type]      [城市code]
     */
    public function getDistrict($lat, $lng)
    {
        $uri = '/geocoder/v2/';
        $arr = [
            'output' => 'json',
            'ak' => $this->ak,
            'location'=>$lat.','.$lng,
        ];
        $re = $this->getResult($uri, $arr);

        if($re['status'] != 0){
            return false;
        }
        $city['district'] = substr($re['result']['addressComponent']['adcode'],0,4).'00';
        $city['city'] = $re['result']['addressComponent']['city'];
        return $city;
    }

    // 获取api接口
    public function getResult($uri, $arr)
    {
        $url = $this->url . $uri;
        $arr = $this->getParamet($uri, $arr);
        $re = $this->baiduCurl($url, $arr);

        if(!$re){
           return false;
        }

        $re = json_decode($re, true);

        return $re;
    }

    public function getParamet($uri, $arr, $method = 'GET')
    {
        $sk = $this->sk;

        if ($method === 'POST'){
            ksort($arr);
        }
        $querystring = http_build_query($arr);

        //调用sn计算函数，默认get请求
        $arr['sn'] = md5(urlencode($uri.'?'.$querystring.$sk));

        // $url .= '?'.http_build_query($arr);
        return $arr;
    }

    public function baiduCurl($url, $arr)
    {
        $curl = curl_init();
        $str=http_build_query($arr);
        $url=$str?$url.'?'.$str:$url;
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