<?php
/**
 * 外部调用回调接口
 */
namespace app\api\controller;

use app\common\controller\BaseApi;
use app\common\libs\Alipay;
use app\common\libs\Weixin;
use app\common\libs\Bankpay;

class back extends BaseApi
{
	protected $tokenFlag = 0;
	//支付宝回调地址
	public function aliClientPayBack()
	{
		$__post = file_get_contents("php://input");
		$this->writeLog('支付宝>>'.var_export($__post,true));
		//echo 'success';
	}
	//支付宝回调
	public function alipayCallbackData()
	{
		// 1.获取数据
		$arr = $_POST;
		$this->writeLog('支付宝>>'.var_export($_POST,true));
		$a = new Alipay();
		$result = $a->checkSign($arr);
		if($result) {//验证成功
			//商户订单号
			$out_trade_no = $_POST['out_trade_no'];
			//支付宝交易号
			$trade_no = $_POST['trade_no'];
			//交易状态
			$trade_status = $_POST['trade_status'];

		    if($trade_status == 'TRADE_FINISHED') {
		    	$this->paySuccess($out_trade_no,2,$trade_no);
		    }else if ($trade_status == 'TRADE_SUCCESS') {
				$this->paySuccess($out_trade_no,2,$trade_no);
			}
				echo "success";		//请不要修改或删除	
			}else {
			//     //验证失败
			    echo "fail";	//请不要修改或删除
			}
	}

	public function banksign()
	{
		$__post = file_get_contents("php://input");
		$this->writeLog('银行卡>>'.var_export($__post,true));
	}
	//银行卡
	public function bankCallbackData()
	{
		// $__post = file_get_contents("php://input");
		$this->writeLog('银行卡>>'.var_export($_POST,true));
		// $this->writeLog($__post);

		$post = $_POST['jsonRequestData'];
		$post = str_replace('\\','',$post);
		$arr = json_decode($post,true);

		$a = new Bankpay();
		$a->solutionSign($arr);

		$this->paySuccess($arr['noticeData']['merchantPara'],3,$arr['noticeData']['bankSerialNo']);
	}

	//回调函数
	public function weixinCallbackData()
	{

		$__post = file_get_contents("php://input");
		$this->writeLog('微信>>'.var_export($__post,true));
		$a = new Weixin();
		$arr=$a->checkDate($__post);

		if($arr['result_code'] == 'SUCCESS'){
			$this->paySuccess($arr['out_trade_no'],4,$arr['transaction_id']);
		}

		header('Content-Type:text/xml');
		echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
	}

	public function paySuccess($orderNo,$payType,$payRecord)
	{
		$arr = [
			'orderNo' => $orderNo,
			'payType' => $payType,
			'payRecord' => $payRecord
		];
		$type = $this -> orderType($orderNo);
		switch ($type) {
			case '1':
			$this->payCourseSuccess($arr);
			break;
			case '2':
			$this->payWrkSuccess($arr);
			break;
			case '3':
			$this->payBuySuccess($arr);
			break;
			case '4':
			$this->payVipSuccess($arr);
			break;
			default:
			$this->errorMsg('error');
			break;
		}
	}

	public function payCourseSuccess($arr)
	{
		$a = new \app\api\model\Course();
		$a -> payClassSuccess($arr);
	}
	public function payWrkSuccess($arr)
	{
		$a = new \app\api\model\ActivityWrk();
		$a -> payWrkOrderSuccess($arr);
	}
	public function payBuySuccess($arr)
	{
		$a = new \app\api\model\GroupBuying();
		$a -> buySuccess($arr);
	}
	public function payVipSuccess($arr)
	{
		$a = new \app\api\model\Member();
		$a -> payBackSuccess($arr);
	}


	//判断类别
	private function orderType($order)
	{
		$a = substr($order , 0 , 1);
		$type = 0;
		switch ($a) {
			case 'C'://课程
				$type = 1;
				break;
			case 'W'://万人砍
				$type = 2;
				break;
			case 'B'://团购
				$type = 3;
				break;
			case 'M'://vip
				$type = 4;
				break;
		}
		return $type;
	}

	//请确保项目文件有可写权限，不然打印不了日志。
	function writeLog($text) {
		file_put_contents ('/home/data'.DIRECTORY_SEPARATOR."log.txt", date ( "Y-m-d H:i:s" ) . "  " . $text . "\r\n", FILE_APPEND );
	}

	function txt(){
		$a = input('post.a');
		echo $this->orderType($a);
	}
}