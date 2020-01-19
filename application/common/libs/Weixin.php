<?php

namespace app\common\libs;


// use think\Model;
/**
 * 微信app支付
 */

class Weixin
{
    // md5加密字符串
    public $key = '8adce1eba0b20057ff383949be56e59a';
    // 应用ID
    public $appid='wxab796e37a9d19c06';

    public $appsecret='5376b3618b0e94cdb6f05622c5f797b6';
    // 商户号
    public $mch_id='1426188202';
    // 交易类型
    public $trade_type='APP';
    //微信支付接口
    public $wxapi='https://api.mch.weixin.qq.com/pay/unifiedorder';
    // public $ticket='https://api.weixin.qq.com/cgi-bin/ticket/getticket';


    // $arr['name'] 商品描述
    // $arr['money'] 商品价格
    // $arr['back_url'] 当前回调链接
    // $arr['order_no'] 订单号
    public function sign($arr)
    {

        $query['body'] = $arr['name'];
        $query['total_fee'] = 0.1*100;
        // $query['total_fee'] = $arr['money']*100;
        // $query['total_fee'] = 1;
        $query['notify_url'] = $arr['back_url'];
        $query['out_trade_no'] = $arr['order_no'];

        $query['nonce_str']=$this->rand16();
        $query['mch_id']=$this->mch_id;
        $query['appid']=$this->appid;
        $query['trade_type']=$this->trade_type;
        $query['spbill_create_ip']='58.213.76.98';


        // 获取加密码
        $data=$this->buildQuery($query);
        $query['sign']=$data;
        // 数组转xml
        $str=$this->linkxml($query);

        // 发送支付请求
        $file=$this->postxml($str);

        // 解析请求之后的数据
        $arr=$this->getxml($file);
        // print_r($arr);exit;
        // print_r($str);
        // print_r($query);
        if($arr['return_code'] == 'SUCCESS'){

            $sign = $arr['sign'];

            $data=$this->buildQuery($arr);

            if($sign == $data){
                $array['appid']=$this->appid;
                $array['partnerid'] = $this->mch_id;
                $array['prepayid'] = $arr['prepay_id'];
                $array['package'] = "Sign=WXPay";
                $array['noncestr'] = $this->rand16();
                $time = time();
                $array['timestamp'] = "$time";
                // $array['signType'] = 'MD5';
                $array['sign'] = $this->buildQuery($array);
                return $array;
            }
        }else{
            $this->errorMsg('weiError');
        }
    }


    public function checkDate($data)
    {
        if(!$data){
            echo 'no data';
            exit;
        }
                // Log::write($__post);
        $arr = $this->getxml($data);
        $sign = $arr['sign'];
        $data = $this->buildQuery($arr);

        if($sign != $data){
            echo 'sign error';
            exit;
        }
        return $arr;
    }

    /**
     * [getxml xml转换成数组]
     * @param  [xml]   $fileContent [准备转换的xml]
     * @return [array]              [转换成功的array]
     */
    public function getxml($fileContent){

        // print_r($fileContent);
        $arr=array();
        //转换为simplexml对象
        $xmlResult = simplexml_load_string($fileContent, null, LIBXML_NOCDATA);
        //foreach循环遍历
        $xmlResult=$this->objectToArray($xmlResult);

        return $xmlResult;
    }

    /**
     * [objectToArray 对象转数组]
     * @param  [type] $e [对象]
     * @return [type]    [数组]
     */
    public function objectToArray($e){
        $e=(array)$e;
        foreach($e as $k=>$v){
            if( gettype($v)=='resource' ) return;
            if( gettype($v)=='object' || gettype($v)=='array' )
                $e[$k]=(array)objectToArray($v);
        }
        return $e;
    }

    /**
     * [postxml 发送微信支付请求]
     * @param  [string] $str [请求的xml]
     * @return [string]      [响应数据]
     */
    public function postxml($str){

        if (!extension_loaded("curl")) {
            $this->error('curl error',2);
        }
        //构造xml
        $xmldata=$str;
        //初始一个curl会话
        $curl = curl_init();
        //设置url
        curl_setopt($curl, CURLOPT_URL,$this->wxapi);
        //设置发送方式：
        curl_setopt($curl, CURLOPT_POST, true);
        //https 不验证ssl证书
        curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置发送数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmldata);
        //抓取URL并把它不传递给浏览器
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //存取数据
        $file_contents = curl_exec($curl);
        //关闭cURL资源，并且释放系统资源
        if(curl_error ( $curl ))
        { echo  'Curl error: '  .  curl_error ( $curl );}
        curl_close($curl);

        return $file_contents;

    }

    /**
     * [linkxml 数组转xml]
     * @param  [array] $data [要转换的数组]
     * @return [string]       [转换成功的xml]
     */
    public function linkxml($data)
    {
        $str='';
        $str.="<xml>\r\n\t";
        $arr=array();
        foreach ($data as $key => $value) {
            $arr[]="<$key>$value</$key>";
        }
        $str.=implode("\r\n\t", $arr);
        $str.="\r\n</xml>";
        return $str;
    }
    /**
     * [rand16 生成随机数]
     * @return [type] [随机数]
     */
    public function rand16(){
        $str=rand('0','999999');
        $start=rand('0','16');
        return substr(md5($str),$start,16);
    }
    /**
     * [buildQuery 加密数据]
     * @param  [array] $query [加密的数组]
     * @return [string]       [加密后字符串]
     */
    public function buildQuery($query)
    {

        if (!$query) {
            return null;
        }

        //将要 参数 排序
        ksort( $query );

        //重新组装参数
        $params = array();
        foreach ($query as $key => $value) {
            if ($value == '') {
                continue;
            }
            if ($key == 'sign') {
                continue;
            }

            $params[] = $key .'='. $value;
        }

        $params[] = 'key=' . $this->key;

        $data = implode('&', $params);

        $sign = strtoupper(md5($data));


        // $out = $data .'&sign='.$sign;
        // print_r($data);

        return $sign;

    }

}