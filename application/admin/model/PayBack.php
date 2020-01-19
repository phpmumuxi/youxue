<?php
/**
 * User: LiuTong
 * Date: 2017-08-29
 * Time: 16:58
 */

namespace app\admin\model;

use think\Db;
use think\Exception;
use think\Model;

class PayBack extends Model
{
    /**
     * 退款列表
     * Date: 2017-08-30
     * @param string $orderNo
     * @return mixed
     */
    public function getPayBackList($orderNo = '')
    {
        if ($orderNo) {
            $where['b.orderNo'] = $orderNo;
        }
        $where['b.isDelete'] = 0;
        $data = db('back b')
            ->field('b.id,b.money,b.actualMoney,b.reason,a.name as adminName,b.orderNo,b.isReview,b.createTime,ab.name as reviewName,b.reviewTime')
            ->join('admin a', 'a.id=b.adminId', 'LEFT')
            ->join('admin ab', 'ab.id=b.reviewId', 'LEFT')
            ->where($where)
            ->order('b.isReview ASC,b.createTime DESC')
            ->paginate(10, false);
        return $data;
    }

    /**
     * 审核
     * Date: 2017-08-30
     * fix Date: 2017-11-06
     * fix Content: 退款增加万人砍课程
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function updatePayBack($id, $adminId)
    {
        $data = db('back b')
            ->field('b.actualMoney,b.orderNo,u.balance,u.id as userId,b.orderType')
            ->join('user u', 'u.id=b.userId', 'LEFT')
            ->where(['b.id' => $id])
            ->find();
        if (!$data) {
            return false;
        }

        $orderNo = $data['orderNo'];
        $orderId = 0;
        if ($data['orderType'] == 1) {
            //优选课程
            $orderId = db('order_class')->where(['orderNo' => $orderNo])->value('id');
        } elseif ($data['orderType'] == 2) {
            //万人砍
            $orderId = db('activity_wrk_order')->where(['orderNo' => $orderNo])->value('id');
        }
        if (!$orderId) {
            return false;
        }

        Db::startTrans();

        $insertData['userId'] = $data['userId'];
        $insertData['nowMoney'] = $data['balance'];
        $insertData['changeMoney'] = $data['balance'] + $data['actualMoney'];
        $insertData['money'] = $data['actualMoney'];
        $insertData['createTime'] = time();
        $insertData['type'] = 13;// type = 13 退款
        $insertData['orderId'] = $orderId;
        $insertData['orderNo'] = $orderNo;
        $ret = db('record_money')->insert($insertData);
        if (!$ret) {
            Db::rollback();
            return false;
        }

        if ($data['orderType'] == 1) {
            $retOrder = db('order_class')->where(['id' => $orderId])->update(['status' => 3]);
        } elseif ($data['orderType'] == 2) {
            $retOrder = db('activity_wrk_order')->where(['id' => $orderId])->update(['status' => 3]);
        }
        if (!$retOrder) {
            Db::rollback();
            return false;
        }

        $userId = $data['userId'];
        $retUser = db('user')->where(['id' => $userId])->setInc('balance', $data['actualMoney']);
        if (!$retUser) {
            Db::rollback();
            return false;
        }

        $updateData['isReview'] = 1;
        $updateData['reviewTime'] = time();
        $updateData['reviewId'] = $adminId;
        $retUpdate = db('back')->where(['id' => $id])->update($updateData);
        if (!$retUpdate) {
            Db::rollback();
            return false;
        }

        Db::commit();
        return true;
    }

    /**
     * Date: 2017-11-06
     * 退款申请拒绝
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function cancelPayBack($id, $adminId)
    {
        $data = db('back b')
            ->field('b.actualMoney,b.orderNo,u.balance,u.id as userId,b.orderType')
            ->join('user u', 'u.id=b.userId', 'LEFT')
            ->where(['b.id' => $id])
            ->find();
        if (!$data) {
            return false;
        }
        $orderNo = $data['orderNo'];
        $orderId = 0;
        if ($data['orderType'] == 1) {
            //优选课程
            $orderId = db('order_class')->where(['orderNo' => $orderNo])->value('id');
        } elseif ($data['orderType'] == 2) {
            //万人砍
            $orderId = db('activity_wrk_order')->where(['orderNo' => $orderNo])->value('id');
        }
        if (!$orderId) {
            return false;
        }

        Db::startTrans();
        try {
            if ($data['orderType'] == 1) {
                db('order_class')->where(['id' => $orderId])->update(['status' => 4]);
            } elseif ($data['orderType'] == 2) {
                db('activity_wrk_order')->where(['id' => $orderId])->update(['status' => 4]);
            }
            db('back')->where(['id' => $id])->update(['reviewId' => $adminId, 'reviewTime' => time(), 'isReview' => 2]);
            operateLog('退款申请拒绝', 'back', $id, $adminId);
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }
}