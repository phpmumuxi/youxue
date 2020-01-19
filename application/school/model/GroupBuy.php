<?php
/**
 * User: LiuTong
 * Date: 2017-08-18
 * Time: 14:10
 */

namespace app\school\model;

use think\Model;
use think\Db;

class GroupBuy extends Model
{
    /**
     * 团购订单
     * Date: 2017-08-18
     * fix Date: 2017-09-01
     * fixContent: 订单搜索
     * @param string $orderNo
     * fix Date: 2017-09-12
     * fix Content: 手机号搜索
     * @param int $phone
     * fix Date: 2017-09-13
     * fix Content: 订单状态筛选
     * fix Date: 2017-09-18
     * fix Content: 使用顾问id取代原本的userId
     * @param int $status
     * @return \think\Paginator
     */
    public function getGroupBuyOrderList($orderNo = '', $phone = 0, $status = -1)
    {
        $where = [];
        if ($orderNo) {
            $where['ob.orderNo'] = $orderNo;
        }

        if ($orderNo) {
            $where['u.phone'] = $phone;
        }

        if ($status != -1) {
            $where['ob.status'] = $status;
        } else {
            $where['ob.status'] = ['exp', '<>0'];
        }

        $where['ob.isDispose'] = 1;
        $data = db('order_buy')
            ->alias('ob')
            ->field('ob.id,ob.orderNo,ob.adviserId,an.name as adviserName,ob.money,ob.createTime,ob.name,ob.status,u.name as uName,u.phone,ob.isDispose,ob.termDay,ob.payTime,ob.term,ob.type')
            ->join('user u', 'u.id=ob.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=ob.adviserId', 'LEFT')
            ->order('ob.payTime asc,ob.id')
            ->where($where)
            ->paginate(10, false);
        return $data;
    }

    /**
     * 订单处理
     * Date: 2017-08-18
     * @param $orderId
     * @return int|string
     */
    public function handleOrder($orderId)
    {
        $data['isDispose'] = 1;
        $data['status'] = 2;
        $data['useTime'] = time();
        $ret = db('order_buy')->where(['id' => $orderId])->update($data);
        return $ret;
    }

    /**
     * 校区顾问列表
     * Date: 2017-08-21
     * @param $schoolId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getSchoolAdviserList($schoolId)
    {
        $data = db('adviser_name')
            ->field('id,userId,name')
            ->where(['schoolId' => $schoolId])
            ->select();
        return $data;
    }

    /**
     * 分配顾问
     * Date: 2017-08-21
     * @param $orderId
     * @param $adviserId
     * @return int|string
     */
    public function assignGroupBuyAdviser($orderId, $adviserId)
    {
        Db::startTrans();

        $ret = db('order_buy')->where(['id' => $orderId])->update(['adviserId' => $adviserId]);
        if (!$ret) {
            Db::rollback();
            return false;
        }

        $ret = $this->adviserOrderHandle($orderId, $adviserId);
        if (!$ret) {
            Db::rollback();
            return false;
        }

        Db::commit();
        return true;
    }

    /**
     * 未处理数
     * Date: 2107-08-22
     * @return int|string
     */
    public function countNum()
    {
        $ret = db('order_buy')->where(['isDispose' => 0])->count('id');
        return $ret;
    }

    /**
     * 生成用户和顾问管理信息记录
     * Date: 2017-09-18
     * @param $orderId
     * @param $adviserId
     * @return bool
     */
    public function adviserOrderHandle($orderId, $adviserId)
    {
        // orderType 单类别1vip体验课2团购3优选课程4点灯券
        // status 订单状态0未体验1已体验2未签约3已签约（0,1体验课用/3,4优选课程用）
        $courseData = db('order_buy')->where(['id' => $orderId])->find();
        $status = $courseData['status'];
        if ($status == 1) {
            $data['status'] = 0;
        } elseif ($status == 2) {
            $data['status'] = 1;
        }
        $data['adviserId'] = $adviserId;
        $data['userId'] = $courseData['userId'];
        $data['name'] = $courseData['name'];
        $data['orderId'] = $courseData['id'];
        $data['orderType'] = 2;
        $data['createTime'] = time();

        $where['orderId'] = $orderId;
        $where['orderType'] = 2;

        $ret = 0;
        // 查询订单是否已生成关联记录
        $ret = db('adviser_order')->field('id')->where($where)->find();
        if ($ret) {
            $ret = db('adviser_order')->where(['id' => $ret['id']])->update($data);
        } else {
            $ret = db('adviser_order')->insert($data);
        }

        if ($ret) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取顾问手机号
     * Date: 2017-09-18
     * @param $adviserId
     * @return mixed
     */
    public function getAdviserPhone($adviserId)
    {
        $where['id'] = $adviserId;
        $ret = db('adviser_name')->where($where)->value('phone');
        return $ret;
    }
}