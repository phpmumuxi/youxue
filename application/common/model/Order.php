<?php
/**
 * User: LiuTong
 * Date: 2017-08-29
 * Time: 10:09
 */

namespace app\common\model;

use think\Model;

class Order extends Model
{
    /**
     * 获取退款金额 计算公式
     * Date: 2017-08-29
     * @param $orderId
     * @return array
     */
    public function getOrderBackMoney($orderId)
    {
        $backMoney = 0;
        $data = db('order_class')->where(['id' => $orderId])->find();
//        $memberLevel = $data['level'];
//         ps: 普通会员首次购买返现
        $userBackMoney = $data['userMoney'];
        if ($data['isAgain'] == 1) {
            $backMoney = $data['money'] - $userBackMoney - ($data['shopOne'] - $data['shopTwo']);
            $cal = $data['money'] . ' - ' . $userBackMoney . ' - ' . ($data['shopOne'] . ' - ' . $data['shopTwo']);
        } else {
            $backMoney = $data['money'] - $userBackMoney;
            $cal = $data['money'] . ' - ' . $userBackMoney;
        }

        return ['cal' => $cal, 'backMoney' => $backMoney];
    }

    /**
     * 退款申请记录
     * Date: 2017-08-29
     * @param $orderId
     * @param $realBackMoney
     * @param $backInfo
     * @param $adminId
     * @return bool
     */
    public function payBackMoney($orderId, $realBackMoney, $backInfo, $adminId)
    {
        $orderData = db('order_class')->where(['id' => $orderId])->find();
        if (empty($orderData)) {
            return false;
        }

        $backMoney = $this->getOrderBackMoney($orderId);
        $data['money'] = $backMoney['backMoney'];
        $data['actualMoney'] = $realBackMoney;
        $data['reason'] = $backInfo;
        $data['adminId'] = $adminId;
        $data['orderNo'] = $orderData['orderNo'];
        $data['userId'] = $orderData['userId'];
        $data['isReview'] = 0;
        $data['createTime'] = time();
        $data['isDelete'] = 0;

        $ret = db('back')->insert($data);
        if (!$ret) {
            return false;
        }

        // status = 2 退款中
        $update = db('order_class')->where(['id' => $orderId])->update(['status' => 2]);
        if (!$update) {
            return false;
        }

        return true;
    }
}