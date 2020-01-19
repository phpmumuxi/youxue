<?php
/**
 * User: LiuTong
 * Date: 2017-09-07
 * Time: 16:40
 */

namespace app\school\model;

use think\Model;

class Analysis extends Model
{
    /**
     * 按天查询
     * Date: 2017-09-08
     * @param $schoolId
     * @param $aTime
     * @param $bTime
     * @return mixed
     */
    public function getSignCount($schoolId, $aTime, $bTime)
    {
        $where['signDate'] = ['between', [$aTime, $bTime]];
        $where['schoolId'] = $schoolId;
        $where['isSign'] = 1;
        $data = db('order_class')
            ->field('FROM_UNIXTIME(signDate, "%Y-%m-%d") as date,count(id) as num')
            ->where($where)
            ->group('date')
            ->select();

        return $data;
    }

    /**
     * 按月查询
     * Date: 2017-09-08
     * fix Date: 2017-11-02
     * fix Content: 作废
     * @param $schoolId
     * @param $aTime
     * @param $bTime
     * @return mixed
     */
    public function getSignCountMonth($schoolId, $aTime, $bTime)
    {
        $where['signDate'] = ['between', [$aTime, $bTime]];
        $where['schoolId'] = $schoolId;
        $where['isSign'] = 1;
        $data = db('order_class')
            ->field('FROM_UNIXTIME(signDate, "%Y-%m") as date,count(id) as num')
            ->where($where)
            ->group('date')
            ->select();

        return $data;
    }

    /**
     * 每日报名统计
     * Date: 2017-09-11
     * fix Date: 2017-11-02
     * fix Content: 作废
     * @param $schoolId
     * @param $timeA
     * @param $timeB
     * @return mixed
     */
    public function getOrderCourseCountDayObsolete($schoolId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['status'] = ['in', [1, 4]];
        $where['schoolId'] = $schoolId;
        $data = db('order_class')
            ->field('FROM_UNIXTIME(createTime, "%Y-%m-%d") as date,count(id) as num')
            ->where($where)
            ->group('date')
            ->select();
        return $data;
    }

    /**
     * 每月报名统计
     * Date: 2017-09-11
     * fix Date: 2017-11-02
     * fix Content: 作废
     * @param $schoolId
     * @param $timeA
     * @param $timeB
     * @return mixed
     */
    public function getOrderCourseCountMonthObsolete($schoolId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['status'] = ['in', [1, 4]];
        $where['schoolId'] = $schoolId;
        $data = db('order_class')
            ->field('FROM_UNIXTIME(createTime, "%Y-%m") as date,count(id) as num')
            ->where($where)
            ->group('date')
            ->select();

        return $data;
    }

    /**
     * 每日报名统计
     * Date: 2017-09-11
     * @param $schoolId
     * @param $timeA
     * @param $timeB
     * @return mixed
     */
    public function getOrderCourseCountDay($schoolId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['orderType'] = ['in', [1, 2, 4]];
        $where['schoolId'] = $schoolId;
        $data = db('adviser_order')
            ->field('FROM_UNIXTIME(createTime, "%Y-%m-%d") as date,count(id) as num')
            ->where($where)
            ->group('date')
            ->select();
        return $data;
    }

    /**
     * 每月报名统计
     * Date: 2017-09-11
     * @param $schoolId
     * @param $timeA
     * @param $timeB
     * @return mixed
     */
    public function getOrderCourseCountMonth($schoolId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['orderType'] = ['in', [1, 2, 4]];
        $where['schoolId'] = $schoolId;
        $data = db('adviser_order')
            ->field('FROM_UNIXTIME(createTime, "%Y-%m") as date,count(id) as num')
            ->where($where)
            ->group('date')
            ->select();

        return $data;
    }

    /**
     * 每日金额
     * Date: 2017-09-12
     * @param $schoolId
     * @param $timeA
     * @param $timeB
     * @return mixed
     */
    public function getSchoolMoneyDay($schoolId, $timeA, $timeB)
    {
        $where['recordDate'] = ['between', [$timeA, $timeB]];
        $where['schoolId'] = $schoolId;
        $data = db('shop_money_record')
            //->field('FROM_UNIXTIME(recordDate, "%Y-%m-%d") as date,sum(money) as num')
            ->field('FROM_UNIXTIME(recordDate, "%Y-%m-%d") as date,sum(price) as num')
            ->where($where)
            ->group('date')
            ->select();

        return $data;
    }

    /**
     * 每月金额
     * Date: 2017-09-12
     * @param $schoolId
     * @param $timeA
     * @param $timeB
     * @return mixed
     */
    public function getSchoolMoneyMonth($schoolId, $timeA, $timeB)
    {
        $where['recordDate'] = ['between', [$timeA, $timeB]];
        $where['schoolId'] = $schoolId;
        $data = db('shop_money_record')
            //->field('FROM_UNIXTIME(recordDate, "%Y-%m") as date,sum(money) as num')
            ->field('FROM_UNIXTIME(recordDate, "%Y-%m") as date,sum(price) as num')
            ->where($where)
            ->group('date')
            ->select();

        return $data;
    }

    /**
     * 课程购买按天
     * Date: 2017-09-12
     * @param $schoolId
     * @param $timeA
     * @param $timeB
     * @return mixed
     */
    public function getCourseBuyDayObsolete($schoolId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['schoolId'] = $schoolId;
        $where['status'] = 1;
        $data = db('order_class')
            ->field('FROM_UNIXTIME(createTime, "%Y-%m-%d") as date,count(id) as num')
            ->where($where)
            ->group('date')
            ->select();

        return $data;
    }

    /**
     * 课程购买按月
     * Date: 2017-09-12
     * @param $schoolId
     * @param $timeA
     * @param $timeB
     * @return mixed
     */
    public function getCourseBuyMonthObsolete($schoolId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['schoolId'] = $schoolId;
        $where['status'] = 1;
        $data = db('order_class')
            ->field('FROM_UNIXTIME(createTime, "%Y-%m") as date,count(id) as num')
            ->where($where)
            ->group('date')
            ->select();

        return $data;
    }

    /**
     * 课程购买按天
     * Date: 2017-11-02
     * @param $schoolId
     * @param $timeA
     * @param $timeB
     * @return mixed
     */
    public function getCourseBuyDay($schoolId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['schoolId'] = $schoolId;
        $where['status'] = 1;
        $data = db('order_class')
            ->field('count(id) as num,name')
            ->where($where)
            ->group('classSchoolId')
            ->select();

        return $data;
    }

    /**
     * 课程购买按月
     * Date: 2017-11-02
     * @param $schoolId
     * @param $timeA
     * @param $timeB
     * @return mixed
     */
    public function getCourseBuyMonth($schoolId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['schoolId'] = $schoolId;
        $where['status'] = 1;
        $data = db('order_class')
            ->field('count(id) as num,name')
            ->where($where)
            ->group('classSchoolId')
            ->select();

        return $data;
    }
}