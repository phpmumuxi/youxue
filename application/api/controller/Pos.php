<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-10-11
 */

//  星星点灯模块

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\Pos as PosPayModel;

class Pos extends BaseApi
{
    protected $tokenFlag = 0;

    //1.接收pos机消息

    public function receive()
    {
        // pay_no 银行交易号
        // order_no 订单编号
        // state 支付成功状态0支付失败1支付成功
        // pos_id POS机ID号
        // pay_record 支付凭证
        if(request()->isPost()){
            $__post = file_get_contents("php://input");
            // print_r($__post);exit;
            $this->writeLog('pos机扫码>>'.var_export($__post,true));
            $data=json_decode($__post,true);
            if($data['state']==1){
                $posPay = new PosPayModel();
                $re=$posPay->receiveData($data);
                // print_r($re);exit;
                if($re){
                    $this->posJphsh($re);
                    $this->apiError('posSuccess');
                }else{
                    $this->apiError('posOrder');
                }
            }
        }else{
            $this->errorMsg('isPost');
        }

    }


    //订单号绑定支付回调
    public function orderQuery()
    {
        $__post = file_get_contents("php://input");
        $this->writeLog('pos机订单绑定>>'.var_export($__post,true));
        // $__post = '0d4f8584cb136135b7efe6275e185fa5bbff091771f5542308f02a7a343c44fefaf67924e6d499678f8ef9d7a775cdb9e7c4814ff7cb0b0c23b12cfae52456e8c4d4594cf548da961d42d48f5cbed06db3352cc5d766baec94a74638b6c2f1a8337207aa1c268fdf91cec4a28725f27f099c76cbe05155b70529b91e284552b1594a914948c90ed99e67c667a1cb6b0f63fe2cf79741c6235b4e974ac2b6551e8397b924bc227f18e306bd5644d45b45ecc77a15965156ff0380c64675371f4f7bc9d1d0c92f31de45615a1df0eaeda460550083f4ee94b6d539ac4f360767ccce56e9b3f6e86efa9aef354fcb1c57cad6f2dab60d297363528ab5ed47540ca715cbfee73cf667ff857660024fa5049505f83b17241620da1ae35df2e2064f851ccc58014d41589bfbf703bb3736eacd65ec9cb54102699054598de95ce92854172d2fa8dd7f7ab1e2a779b0fdd24fe452997c97108de3e38a14873f90aebd6001b94fee393ca1ec318e57871d489a6c9d07d131e000e5ee2441536759e2048469a16142c2f62ee5dde6d1d5cfbc0ff5aa7bd51e4cf286c1e257e106c303c4c8c9310eff10e983360653637107348c20f0d9751e5ea0f6a6b83935ef10f6fa345dcad9fce91b3f0f467beafac71f9d9ef2d0e2ca44bb671413d5510f9077bdbb4399669f4df22f9a4fa489f9856e63d83afd6271b0a75547c0a5c096d851c5d02abbc87d0c3120b4d9a2a8e3e464c7983436daef0d54120b54da4e5bd8a6507658ce66f99b17fd28ba7451bb49b9598eb705cad53112a5b2168012e5e7dbfa0fda9402585825acf1a9ef14001e1bff988e5ee3438ee490c89b4fd7c266fc7390dfe3fd62b17ff7b6e6568832b3875e07dafb03fd3ce3552de4b1ca5fd18801d8';
        $pos = new \app\common\libs\Ptspay();
        $re = $pos->decodePos($__post);
        // print_r($re);exit;
        if(!$re)$this->apiError('failure');
        $posPay = new PosPayModel();
        $data = $posPay->orderQueryData($re);
        $this->apiSuccess($data);
    }

    //极光推送
    public function posJphsh($arr)
    {
        $array=[
            'info'=>'支付成功',
            'data'=>$arr['info']
            ];
        $phone=$arr['phone'];
        $receive=array(
                'alias'=>[$phone]
                );
        $content='支付成功';
        $this->childpush($array,$receive,$content,'3');
    }

    public function childpush($arr,$receive,$content,$type)
    {
        $push = new \app\common\controller\Jpush();
        $m_time = '86400';//离线保留时间(1天)
        $message="";//存储推送状态
        $result = $push->push($receive,$content,$arr,$type,$m_time);
        // $this->success();
    }





    public function writeLog($text) {
        file_put_contents ('/home/data'.DIRECTORY_SEPARATOR."log.txt", date ( "Y-m-d H:i:s" ) . "  " . $text . "\r\n", FILE_APPEND );
    }
}