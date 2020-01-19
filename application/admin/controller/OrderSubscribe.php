<?php
/**
 * User: LiuTong
 * Date: 2017/11/17
 * Time: 13:56
 */

namespace app\admin\controller;

use app\admin\model\OrderSubscribe as OrderSubscribeModel;
use app\common\controller\AdminBase;

class OrderSubscribe extends AdminBase
{

    public function index()
    {
        return false;
    }

    /**
     * Date: 2017-11-17
     * 线下订单列表
     * @return \think\response\View
     */
    public function orderSubscribeList()
    {
        $orderSubscribe = new OrderSubscribeModel();
        $data = $orderSubscribe->getOrderSubscribeList();
        $pages = $data->render();
        $data = $data->toArray();
        return view('orderSubscribeList',
            [
                'pages' => $pages,
                'data' => $data
            ]
        );
    }

    /**
     * Date: 2017-11-20
     * 退款确认
     */
    public function orderPayBack()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $orderSubscribe = new OrderSubscribeModel();
            $retStatus = $orderSubscribe->checkOtherMoney($id);
            if ($retStatus) {
                $res['status'] = 'errMoney';
                $res['msg'] = 'Paid OtherMoney';// 已支付尾款或尾款分笔
            } else {
                $ret = $orderSubscribe->payBackOrder($id, $this->admin_id);
                if ($ret) {
                    $res['status'] = 'ok';
                } else {
                    $res['msg'] = 'PayBack Failure';
                }
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }
}