<?php
/**
 * User: LiuTong
 * Date: 2017/12/28
 * Time: 13:55
 */

namespace app\common\controller;


class SMSAliyun
{
    // 请参阅 https://ak-console.aliyun.com/ 取得AK信息
    private $accessKeyId = 'LTAIOaKoDdzn9DpK';
    private $accessKeySecret = 'ESgDXR7nyqDsfNoCx2iPk5FD9vP6gM';
    private $domain = "dysmsapi.aliyuncs.com";

    /**
     * 生成签名并发起请求
     *
     * @param $accessKeyId string AccessKeyId (https://ak-console.aliyun.com/)
     * @param $accessKeySecret string AccessKeySecret
     * @param $domain string API接口所在域名
     * @param $params array API具体参数
     * @param $security boolean 使用https
     * @return bool|\stdClass 返回API接口调用结果，当发生错误时返回false
     */
    private function request($accessKeyId, $accessKeySecret, $domain, $params, $security = false)
    {
        $apiParams = array_merge(array(
            "SignatureMethod" => "HMAC-SHA1",
            "SignatureNonce" => uniqid(mt_rand(0, 0xffff), true),
            "SignatureVersion" => "1.0",
            "AccessKeyId" => $accessKeyId,
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "Format" => "JSON",
        ), $params);
        ksort($apiParams);

        $sortedQueryStringTmp = "";
        foreach ($apiParams as $key => $value) {
            $sortedQueryStringTmp .= "&" . $this->encode($key) . "=" . $this->encode($value);
        }

        $stringToSign = "GET&%2F&" . $this->encode(substr($sortedQueryStringTmp, 1));

        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $accessKeySecret . "&", true));

        $signature = $this->encode($sign);

        $url = ($security ? 'https' : 'http') . "://{$domain}/?Signature={$signature}{$sortedQueryStringTmp}";

        try {
            $content = $this->fetchContent($url);
            return json_decode($content);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $str
     * @return null|string|string[]
     */
    private function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

    /**
     * @param $url
     * @return mixed
     */
    private function fetchContent($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-sdk-client" => "php/2.0.0"
        ));

        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $rtn = curl_exec($ch);

        if ($rtn === false) {
            trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
        }
        curl_close($ch);

        return $rtn;
    }

    /**
     * 发送短信
     * @param string $phone
     * @param string $signName
     * @param string $templateCode
     * @param array $templateParam
     * @param string $outId
     * @param string $smsUpExtendCode
     * @return bool|\stdClass
     */
    public function sendSms(string $phone, string $signName, string $templateCode, array $templateParam, string $outId = '', string $smsUpExtendCode = "1234567")
    {
        $params = [];

        // *** 需用户填写部分 ***

        // 必填: 短信接收号码
        $params["PhoneNumbers"] = $phone;

        // 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = $signName;

        // 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $templateCode;

        // 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = $templateParam;

        // 可选: 设置发送短信流水号
        if (!$outId) {
            $outId = 'sms' . time();
        }
        $params['OutId'] = $outId;

        // 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = $smsUpExtendCode;


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 此处可能会抛出异常，注意catch
        $content = $this->request(
            $this->accessKeyId,
            $this->accessKeySecret,
            $this->domain,
            array_merge($params, [
                    "RegionId" => "cn-hangzhou",
                    "Action" => "SendSms",
                    "Version" => "2017-05-25",
                ]
            )
        );

        return $content;
    }

    /**
     * 短信发送记录查询
     * @param string $phone
     * @param string $sendDate
     * @param int $pageSize
     * @param int $currentPage
     * @param string $bizId
     * @return bool|\stdClass
     */
    public function querySendDetails(string $phone, string $sendDate, int $pageSize = 10, $currentPage = 1, $bizId = '')
    {

        $params = [];

        // *** 需用户填写部分 ***

        // 必填: 短信接收号码
        $params["PhoneNumber"] = $phone;

        // 必填: 短信发送日期，格式Ymd，支持近30天记录查询
        $params["SendDate"] = $sendDate;

        // 必填: 分页大小
        $params["PageSize"] = $pageSize;

        // 必填: 当前页码
        $params["CurrentPage"] = $currentPage;

        // 可选: 设置发送短信流水号
        $params["BizId"] = $bizId;

        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***

        // 此处可能会抛出异常，注意catch
        $content = $this->request(
            $this->accessKeyId,
            $this->accessKeySecret,
            $this->domain,
            array_merge($params, [
                    "RegionId" => "cn-hangzhou",
                    "Action" => "QuerySendDetails",
                    "Version" => "2017-05-25",
                ]
            )
        );

        return $content;
    }
}