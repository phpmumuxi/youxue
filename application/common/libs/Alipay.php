<?php

namespace app\common\libs;


// use think\Model;
/**
 * 支付宝app支付
 */

class Alipay
{
    // 支付宝分配给开发者的应用ID
    private $appId = '2016121504308760';
    // private $app_id = '2016073000122427';
    // 接口名称
    private $method = 'alipay.trade.app.pay';
    //网关
    public $gatewayUrl = "https://openapi.alipay.com/gateway.do";
    //返回数据格式
    public $format = "json";
    // 编码格式
    private $charset = 'utf-8';
    // 签名算法类型
    private $signType='RSA2';
    // 签名串
    private $sign = '';
    // 请求的时间
    private $timestamp = 0;
    // 调用的接口版本(固定值)
    private $version = '1.0';
    // 回调地址
    private $notify_url = '';
    // 业务请求参数的集合
    private $biz_content = '';
    // 描述信息
    private $body = '';
    // 商品的标题
    private $subject = '同城优学';
    // 商户网站唯一订单号
    private $out_trade_no = 0;
    // 订单总金额
    private $total_fee = '0.01';
    // 销售产品码(固定值)
    private $product_code = 'QUICK_MSECURITY_PAY';
    // 公用回传参数
    private $passback_params = '';


    protected $alipaySdkVersion = "alipay-sdk-php-20161101";
    // private $it_b_pay = '30m';
    // private $partner = '2088221413087390';
    // private $payment_type = '1';
    // private $seller_id = 'yhzn@31ysh.com';
    // private $show_url = 'm.alipay.com';


    // 加密私钥
    private $privateKey='MIIEogIBAAKCAQEAujh9qT/ZiB2vsRpzFL7KvZUx1LS77/dBCOwwwS/TRAkn2UAcaZ3qEe8LO4g3Vy7FhbsqSTUREoZdZ/hePyyITcO6Gd5IQ9zY0MpSByTXRBZCtgcTF9RcRIRbjQEsUaTIrP06eC5XMZ8e33wzDtJk6LO9d5cJDFocI3sk1vGDfvwTxrIN/8FqqJoqbWijfkyYNckg2lF6QhA+LtcbQ4oRDyytwS+gLHNurI4qYdI5w/lBOyQ2XG0/35TJAcIgZA0zA8PrpdihkWGmxxCzO42iYGtMdsp7VDOuP4hxoAciNHX7+zkOyTMV3CmscjlgtVF/eLojBcDQXX8+pSAYdoJY3QIDAQABAoIBAH3zU1qz6sdbfMdGYmu3l5yeWjPJgguNqhkE1vzzAetUXfVcnVr6CaTTjz9WpEd9RO9sZe9ZyxBCfM6jd+s2jYI7TZXNeAceSi79iHl4e3h7rec75bgKU9Wrx9gY4QbfczM9mtxKS4MuYk05zbbKqqGRYCaQqvgFwl5lHcebVhr98GioL4IKW7EIIoe+JDQTRvwsxzoo9SZsytQFi2/hoLSjcce0dB+mL8axBee3ceyDpQFVvvK/5vSNLv0z5cZmXatBAq7ow7v4H+WgwLV5/oBfB11aadqTu83Ddmif6sBS0QUe6CtQXDlrLBkBlJpW3QfmnvKLW/1JwJgf8cI75GkCgYEA3Vd9eqWk4RMGHKzVgCu4GQ3GqrUf1wzK7/g0DlLh+IHFOQO8aXJW+RN2RBcFWtQCX+nNWIzkkN1MBmMz2xUgHV9EDkJNOcZgoy4htk9hkXyZL17t94IzJxaaNyB5bBk0tpj2gR+ZNczHR6za9BWCVENeOaj5fJQTwcAqLQj44s8CgYEA12Eq8PhD/CMNpr4537P6sSDNpKqauA+RvcoOsgb0fUkC1bDP6OilwVrCvmcvLn/X6k9k+tizSNzb36fiN1d5DnjDqB36NViyvykSb3Vy+fKtgr/bdPLgwth+wzCRQtQJC60txByTExHTNoJRg0abCzJhhTXcvUa/UvowFw3dJJMCgYAO/tZpcL9y4C8R0kPEozGdwOkst4iosR0ulMoyDjtw8pnB+xbQrgZmaYMhqc2bEbH3fYeD8Eer6NI1vJLOzOeYFdQugx9oQqBZbc6q40Xg7WgTZYMJubBtfYKFKZYQVMTVehd5OALROkZl/Lf4wVxLCSVhEqmUXxPXlIVG98yk0QKBgBkYRTyWcLVsECchevTJr6f7vno1NpnDlqOaa2Pq04nVe/MSCueEU+vXe2Fb/G9ajfnLDmT74sfWk/fXQ9BW7M/22jU4aPLWRt1nF7LM6Hye/gCCAjv6IJ4TQ5HSktpKglNN9ksnWSubpBRx0rWNAHupfxtaT0HugFRWuku1XdLpAoGAbEtKb7KPOtUaQTC6SMMIFbp6489d7ra/a0wwaRdsnLBoQfSjY6VVd2mXVg3lLwt2aqf6U/sPuINtr2ltZ81MIhefbYPySTjg41ALOaEpQFpYuPI/o8HV9o4wcZOxbdHdEu8y1ioaGXQlBHwiJta1VxnbIaL1VmiAG63Jl8ANqWA=';

    // 解密公钥
    private $publicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmekpQBocD8gZKLMR0ZWNYiouFW1yfICHbOkHAtWGgcQA5fiM9qyN/fkFs19A4EadsNuN4IrXcvFnO6O7AJ1Dw1cDK8CYLBCEUojaZOGxrRcghuvCQKC8CAk00A6epnl2X8sGeLC/eyYoE9/lCjRcAXHUzffmdU7jNsAmO2LGMyURkU/KCf10z16nV2mSK9u7LPI+bsUEFhsMOJS1Dr/8fJtECA7ooPzv5JJn7n742mhIeGUoKxPLZ6FSyTZ+LgcG53K8lwST42m75/AoYDiw7VlLSjjoEVJtI2Vqzj/9nX6zW361BohQbd40yL6Iu3nnotxsboYgnuW1l2Cfe1/9RwIDAQAB';

    // $arr['name'] 商品描述
    // $arr['money'] 商品价格
    // $arr['back_url'] 当前回调链接
    // $arr['order_no'] 订单号
    public function sdkExecute($arr)
    {
        //$this->setupCharsets($request);

        $this->notify_url = $arr['back_url'];
        $query['total_amount'] = 0.1;
        $query['body'] = $arr['name'];
        // $query['total_amount'] = $arr['money'];
        $query['out_trade_no'] = $arr['orderNo'];
        $query['subject'] = $this->subject;
        $query['product_code'] = $this->product_code;
        $query['timeout_express'] = "30m";
        $biz_content = json_encode($query);

        $params['app_id'] = $this->appId;
        $params['method'] = $this->method;
        $params['format'] = $this->format;
        $params['sign_type'] = $this->signType;
        $params['timestamp'] = date("Y-m-d H:i:s");
        $params['alipay_sdk'] = $this->alipaySdkVersion;
        $params['charset'] = $this->charset;
        $params['version'] = $this->version;
        $params['notify_url'] = $this->notify_url;
        $params['biz_content'] = $biz_content;
        ksort($params);
        $params['sign'] = $this->generateSign($params, $this->signType);
        foreach ($params as &$value) {
            $value = $this->characet($value, $params['charset']);
        }
        return http_build_query($params);
    }


    // 验证过程
    public function sign($data, $signType = "RSA")
    {
        $priKey=$this->privateKey;
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
       ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

       if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }

        $sign = base64_encode($sign);
        return $sign;
    }


    /*
     * 查询参数排序 a-z
     * */
    protected function getSignContent($params)
    {
        //将要 参数 排序
        ksort( $params );

        //重新组装参数
        $data = '';
        $i = 0;
        foreach($params as $k => $v){

           if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

                // 转换成目标字符集
                $v = $this->characet($v, $this->charset);

                if ($i == 0) {
                    $data .= "$k" . "=" . "$v";
                } else {
                    $data .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset ($k, $v);
        return $data;
    }


    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    protected function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }
        /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = $this->charset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }


        return $data;
    }

   private function setupCharsets($request)
   {
        if ($this->checkEmpty($this->charset)) {
            $this->charset = 'UTF-8';
        }
        $str = preg_match('/[\x80-\xff]/', $this->appId) ? $this->appId : print_r($request, true);
        $this->charset = mb_detect_encoding($str, "UTF-8, GBK") == 'UTF-8' ? 'UTF-8' : 'GBK';
    }

    public function generateSign($params, $signType = "RSA") {
        return $this->sign($this->getSignContent($params), $signType);
    }


    //回调验签
    public function checkSign($arr)
    {
        $result = $this->rsaCheckV1($arr, $this->publicKey, $this->signType);
        return $result;
    }
    public function rsaCheckV1($params,$publicKey,$signType='RSA')
    {
        $sign = $params['sign'];
        $params['sign_type'] = null;
        $params['sign'] = null;
        return $this->verify($this->getSignContent($params), $sign,$publicKey, $signType);
    }

    function verify($data, $sign,$publicKey, $signType = 'RSA')
    {
        $res = "-----BEGIN PUBLIC KEY-----\n" .
             wordwrap($publicKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";

        ($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');

        //调用openssl内置方法验签，返回bool值
        if ("RSA2" == $signType) {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        } else {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        }

        return $result;
    }

}