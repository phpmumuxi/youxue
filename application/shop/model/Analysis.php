<?php
/**
 * User: LiuTong
 * Date: 2017-09-07
 * Time: 16:40
 */

namespace app\shop\model;

use app\common\model\ShopCommon;
use think\Model;

class Analysis extends Model
{
    /**
     * 按天查询
     * Date: 2017-09-08
     * @param $shopId
     * @param $aTime
     * @param $bTime
     * @return mixed
     * @internal param $schoolId
     */
    public function getSignCount($shopId, $aTime, $bTime)
    {
        $where['signDate'] = ['between', [$aTime, $bTime]];
        $where['shopId'] = $shopId;
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
     * @param $shopId
     * @param $aTime
     * @param $bTime
     * @return mixed
     * @internal param $schoolId
     */
    public function getSignCountMonth($shopId, $aTime, $bTime)
    {
        $where['signDate'] = ['between', [$aTime, $bTime]];
        $where['shopId'] = $shopId;
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
     * @param $shopId
     * @param $timeA
     * @param $timeB
     * @return mixed
     * @internal param $schoolId
     */
    public function getOrderCourseCountDayObsolete($shopId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['status'] = ['in', [1, 4]];
        $where['shopId'] = $shopId;
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
     * @param $shopId
     * @param $timeA
     * @param $timeB
     * @return mixed
     * @internal param $schoolId
     */
    public function getOrderCourseCountMonthObsolete($shopId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['status'] = ['in', [1, 4]];
        $where['shopId'] = $shopId;
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
     * @param $shopId
     * @param $timeA
     * @param $timeB
     * @return mixed
     * @internal param $schoolId
     */
    public function getOrderCourseCountDay($shopId, $timeA, $timeB)
    {
        $schoolIds = $this->shopSchools($shopId);
        if ($schoolIds) {
            $where['createTime'] = ['between', [$timeA, $timeB]];
            $where['orderType'] = ['in', [1, 2, 4]];
            $where['schoolId'] = ['in', $schoolIds];
            $data = db('adviser_order')
                ->field('FROM_UNIXTIME(createTime, "%Y-%m-%d") as date,count(id) as num')
                ->where($where)
                ->group('date')
                ->select();
            return $data;
        } else {
            return [];
        }
    }

    /**
     * 每月报名统计
     * Date: 2017-09-11
     * @param $shopId
     * @param $timeA
     * @param $timeB
     * @return mixed
     * @internal param $schoolId
     */
    public function getOrderCourseCountMonth($shopId, $timeA, $timeB)
    {
        $schoolIds = $this->shopSchools($shopId);
        if ($schoolIds) {
            $where['createTime'] = ['between', [$timeA, $timeB]];
            $where['orderType'] = ['in', [1, 2, 4]];
            $where['schoolId'] = ['in', $schoolIds];
            $data = db('adviser_order')
                ->field('FROM_UNIXTIME(createTime, "%Y-%m") as date,count(id) as num')
                ->where($where)
                ->group('date')
                ->select();
            return $data;
        } else {
            return [];
        }
    }

    /**
     * Date: 2017-11-03
     * 商户校区
     * @param $shopId
     * @return string
     */
    public function shopSchools($shopId)
    {
        $shopCommon = new ShopCommon();
        $data = $shopCommon->schoolListFromShopId($shopId);
        $schoolIdsArray = [];
        if ($data) {
            foreach ($data as $k => $v) {
                array_push($schoolIdsArray, $v['id']);
            }
        }
        return $schoolIdsArray;
    }

    /**
     * 每日金额
     * Date: 2017-09-12
     * @param $shopId
     * @param $timeA
     * @param $timeB
     * @return mixed
     * @internal param $schoolId
     */
    public function getSchoolMoneyDay($shopId, $timeA, $timeB)
    {
        $where['recordDate'] = ['between', [$timeA, $timeB]];
        $where['shopId'] = $shopId;
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
     * @param $shopId
     * @param $timeA
     * @param $timeB
     * @return mixed
     * @internal param $schoolId
     */
    public function getSchoolMoneyMonth($shopId, $timeA, $timeB)
    {
        $where['recordDate'] = ['between', [$timeA, $timeB]];
        $where['$shopId'] = shopId;
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
     * @param $shopId
     * @param $timeA
     * @param $timeB
     * @return mixed
     * @internal param $schoolId
     */
    public function getCourseBuyDayObsolete($shopId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['shopId'] = $shopId;
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
     * @param $shopId
     * @param $timeA
     * @param $timeB
     * @return mixed
     * @internal param $schoolId
     */
    public function getCourseBuyMonthObsolete($shopId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['shopId'] = $shopId;
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
     * @param $shopId
     * @param $timeA
     * @param $timeB
     * @return mixed
     * @internal param $schoolId
     */
    public function getCourseBuyDay($shopId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['shopId'] = $shopId;
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
     * @param $shopId
     * @param $timeA
     * @param $timeB
     * @return mixed
     * @internal param $schoolId
     */
    public function getCourseBuyMonth($shopId, $timeA, $timeB)
    {
        $where['createTime'] = ['between', [$timeA, $timeB]];
        $where['shopId'] = $shopId;
        $where['status'] = 1;
        $data = db('order_class')
            ->field('count(id) as num,name')
            ->where($where)
            ->group('classSchoolId')
            ->select();

        return $data;
    }
}