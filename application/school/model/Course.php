<?php
/**
 * User: LiuTong
 * Date: 2017-08-15
 * Time: 14:12
 */

namespace app\school\model;

use think\Db;
use think\Exception;
use think\Model;

class Course extends Model
{

    /**
     * 课程订单列表
     * Date: 2017-08-15
     * @param $schoolId
     * fix Date: 2017-09-01
     * fix Content: 订单搜索
     * @param $orderNo
     * fix Date: 2017-09-12
     * fix Content: 手机搜索
     * fix Date: 2017-09-18
     * fix Content: 顾问使用顾问表id取代原本的
     * fix Date: 2017-09-29
     * fix Content: 增加分享人
     * @param $status
     * @param $phone
     * @return \think\Paginator
     */
    public function courseList($schoolId, $orderNo = '', $phone = '', $status = -1)
    {
        if ($orderNo) {
            $where['o.orderNo'] = $orderNo;
        }

        if ($phone) {
            $where['u.phone'] = $phone;
        }
        $where['o.schoolId'] = $schoolId;
        $where['o.isDelete'] = 0;

        if ($status != -1) {
            $where['o.status'] = $status;
        } else {
//            $where['o.status'] = ['exp', '<>0'];
            $where['o.status'] = ['exp', 'in(1,4)'];
        }

        $where['o.isDispose'] = 1;
        $data = db('order_class')
            ->alias('o')
            ->field('o.id,o.orderNo,o.money,o.adviserId,o.isDispose,an.name as adviserName,o.createTime,o.name,o.status,o.isSign,u.name as uName,u.phone,o.isAgain,o.signDate,o.benefitId,ub.name as benefitPersonName,ru.name as shareName,o.isOldCustom')
            ->join('user u', 'u.id=o.userID', 'LEFT')
            ->join('adviser_name an', 'an.id=o.adviserId', 'LEFT')
            ->join('user_benefit ub', 'ub.id=o.benefitId', 'LEFT')
            ->join('user ru', 'ru.id=o.shareId', 'LEFT')
            ->order('o.isSign asc')
            ->where($where)
            ->paginate(10, false);
        return $data;
    }

    /**
     * 校区顾问列表
     * Date: 2017-08-15
     * fix Date: 2017-09-18
     * fix Content: 更改userId为顾问id
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
     * Date: 2017-08-16
     * @param $courseOrderId
     * @param $adviserId
     * fix Date: 2017-09-18
     * fix Content: 插入记录到顾问关联表
     * @return bool
     */
    public function assignCourseAdviser($courseOrderId, $adviserId)
    {
        Db::startTrans();

        $ret = db('order_class')->where(['id' => $courseOrderId])->update(['adviserId' => $adviserId]);
        if (!$ret) {
            Db::rollback();
            return false;
        }

        $ret = $this->adviserOrderHandle($courseOrderId, $adviserId);
        if (!$ret) {
            Db::rollback();
            return false;
        }

        Db::commit();
        return true;
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

    /**
     * 课程处理
     * Date: 2017-08-16
     * @param $courseOrderId
     * @return int|string
     */
    public function handleCourseOrder($courseOrderId)
    {
        $data['isDispose'] = 1;
        $ret = db('order_class')->where(['id' => $courseOrderId])->update($data);
        return $ret;
    }

    /**
     * 课程签约确认
     * Date: 2017-08-16
     * fix Date: 2017-09-01
     * fix Content: 返现
     * fix Date: 2017-09-22
     * fix Content: 签约非续费返顾问 续费返受益人
     * fix Date: 2017-09-28
     * fix Content: 星星活动相关操作 增加操作记录
     * @param $courseOrderId
     * @param $adminId
     * @return int|string
     */
    public function signCourseOrder($courseOrderId, $adminId)
    {
        if (!$courseOrderId) {
            die('1');
            return false;
        }
        $orderInfo = db('order_class')->where(['id' => $courseOrderId])->find();
        if ($orderInfo['status'] != 1) {
            die('2');
            return false;
        }
        if (!$orderInfo['adviserId']) {
            die('3');
            return false;
        }
        $orderId = $courseOrderId;
        $orderNo = $orderInfo['orderNo'];

        $userId = $orderInfo['userId'];

        $shareId = $orderInfo['shareId'];
        $starNum = $orderInfo['starNum'];

        $isAgain = $orderInfo['isAgain'];
        if ($isAgain == 2) {
            if ($orderInfo['benefitId'] == 0) {
                die('4');
                return false;
            }
            $benefitId = $orderInfo['benefitId'];
            $benefitMoney = $orderInfo['benefitMoney'];
            $benefitUserId = db('user_benefit')->where(['id' => $benefitId])->value('userId');
        } else {
            $adviserId = $orderInfo['adviserId'];
            $adviserMoney = $orderInfo['adviserMoney'];
            $adviserUserId = db('adviser_name')->where(['id' => $adviserId])->value('userId');
        }

        $referrerId = $orderInfo['referrerId'];
        $referrerMoney = $orderInfo['referrerMoney'];

        if ($isAgain == 2) {
            $backShopMoney = $orderInfo['shopTwo'];
        } else {
            $backShopMoney = $orderInfo['shopOne'];
        }
        // 课程原价
        $coursePrice = $orderInfo['money'];

        $shopId = $orderInfo['shopId'];
        $schoolId = $orderInfo['schoolId'];

        // 查询是否是同校区第二次购买
        $buyNumbers = db('order_class')->where(['isSign' => 1, 'schoolId' => $schoolId, 'userId' => $userId])->count('id');

        Db::startTrans();

        if ($shareId > 0 && $starNum > 0) {
            $ret = $this->handleStar($shareId, $starNum, $courseOrderId, $adminId);
            if (!$ret) {
                die('5');
                Db::rollback();
                return false;
            }
        }

        // 推荐人返现
        if ($referrerId) {
            // 不查询下单之后绑定的推荐人
            // 生成推荐人金额变动记录
            if ($referrerMoney > 0) {
                $retReferrerRecord = $this->insertUserRecord($referrerId, $orderId, $orderNo, 8, $referrerMoney);
                if (!$retReferrerRecord) {
                    die('6');
                    Db::rollback();
                    return false;
                }

                // 增加推荐人余额
                $retReferrer = db('user')->where(['id' => $referrerId])->setInc('balance', $referrerMoney);
                if (!$retReferrer) {
                    die('7');
                    Db::rollback();
                    return false;
                }
            }
        }

        if ($isAgain == 2) {
            // 受益人返现
            // 生成受益人金额变动记录
            if ($benefitMoney > 0) {
                $retBenefitRecord = $this->insertUserRecord($benefitUserId, $orderId, $orderNo, 14, $benefitMoney);
                if (!$retBenefitRecord) {
                    die('8');
                    Db::rollback();
                    return false;
                }
                // 增加受益人余额
                $retBenefit = db('user')->where(['id' => $benefitId])->setInc('balance', $benefitMoney);
                if (!$retBenefit) {
                    die('9');
                    Db::rollback();
                    return false;
                }
            }
        } else {
            // 客户首次购买顾问返现
            if ($buyNumbers < 1) {
                // 顾问返现
                // 生成顾问金额变动记录
                if ($adviserMoney > 0) {
                    $retAdviserRecord = $this->insertUserRecord($adviserUserId, $orderId, $orderNo, 9, $adviserMoney);
                    if (!$retAdviserRecord) {
                        die('10');
                        Db::rollback();
                        return false;
                    }
                    // 增加顾问余额

                    $retAdviser = db('user')->where(['id' => $adviserUserId])->setInc('balance', $adviserMoney);
                    if (!$retAdviser) {
                        die('11');
                        Db::rollback();
                        return false;
                    }
                }
            }
        }

        // 商户返现
        // 生成商户金额变动记录和每日结算记录
        $retShopRecord = $this->insertShopRecord($shopId, $schoolId, $orderId, $backShopMoney, $coursePrice);
        if (!$retShopRecord) {
            die('12');
            Db::rollback();
            return false;
        }
        //增加商户余额
        $retShop = db('shop')->where(['id' => $shopId])->setInc('money', $backShopMoney);
        if (!$retShop) {
            die('13');
            Db::rollback();
            return false;
        }

        $data['isSign'] = 1;
        $data['signDate'] = time();
        $ret = db('order_class')->where(['id' => $courseOrderId])->update($data);
        if (!$ret) {
            die('14');
            Db::rollback();
            return false;
        }
        $ret = db('adviser_order')->where(['orderId' => $courseOrderId, 'orderType' => 3])->update(['status' => 3]);
        if (!$ret) {
            die('15');
            Db::rollback();
            return false;
        }
        operateLog('课程签约', 'order_class', $courseOrderId, $adminId);

        Db::commit();
        return true;
    }

    /**
     * Date: 2017-11-10/2017-11-13
     * 课程签约新规格
     * @param $orderId
     * @param $adminId
     * @return bool
     * @internal param $courseOrderId
     */
    public function signCourseOrderNew($orderId, $adminId)
    {
        $orderInfo = db('order_class')->where(['id' => $orderId])->find();
        // 支付时间
        $payTime = strtotime($orderInfo['payDate']);
        if ($orderInfo['isSign'] != 0) {
            return false;
        }

        // 商户结算金额
        $shopMoney = $orderInfo['shopMoney'];
        $shopId = $orderInfo['shopId'];
        $shopInfo = db('shop')->where(['id' => $shopId])->find();

        // 用户返现金额
        $userMoney = $orderInfo['userMoney'];
        $userId = $orderInfo['userId'];
        $userInfo = db('user')->where(['id' => $userId])->find();
        $schoolCustomInfo = [];
        $schoolCustomInfo = db('school_custom')->where(['userId' => $userId, 'schoolId' => $orderInfo['schoolId']])->find();

        // 续费
        $isOldCustom = $orderInfo['isOldCustom']; //  == 1 老用户订单

        // 受益人返现金额
        $benefitMoney = $orderInfo['benefitMoney'];
        $benefitId = $orderInfo['benefitId'];
        $benefitUserInfo = [];
        if ($benefitId > 0 && $benefitMoney > 0 && $isOldCustom == 1) {
            $benefitUserInfo = db('user')->where(['id' => $benefitId])->find();
        }

        // 推荐人返现金额
        $referrerMoney = $orderInfo['referrerMoney'];
        $referrerId = $orderInfo['referrerId'];
        $referrerUserInfo = [];
        if ($referrerId && $referrerMoney > 0) {
            $referrerUserInfo = db('user')->where(['id' => $referrerId])->find();
        }

        // 星星活动
        $shareId = $orderInfo['shareId'];
        $starNum = $orderInfo['starNum'];
        $starUserInfo = [];
        $starUserRecordInfo = [];
        $starUserRuleInfo = [];
        if ($shareId > 0 && $starNum > 0) {
            // 分享人是否有记录
            $starUserInfo = db('star_user')->where(['userId' => $shareId])->find();

            $starUserRecordInfo = db('star_user_record')
                ->where(['userId' => $shareId, 'status' => 0])
                ->find();

            $starUserRuleInfo = db('star_rule_user')
                ->where(['userId' => $shareId, 'isEnd' => 0, 'status' => 0])
                ->limit(2)
                ->select();
        }

        $shopDayMoneyInfo = db('shop_money_record')
            ->field('FROM_UNIXTIME(recordDate,"%Y-%m-%d") as rd,id,money,price')
            ->where(['schoolId' => $orderInfo['schoolId'], 'shopId' => $orderInfo['shopId']])
            ->having('rd = "' . date('Y-m-d', time()) . '"')
            ->find();

        Db::startTrans();
        try {
            // 插入校区用户记录
            if (empty($schoolCustomInfo)) {
                db('school_custom')->insert(
                    [
                        'createTime' => time(),
                        'userId' => $userId,
                        'schoolId' => $orderInfo['schoolId'],
                        'shopId' => $orderInfo['shopId'],
                    ]
                );
            }

            // 商户金额变动记录
            db('record_shop')->insert(
                [
                    'shopId' => $orderInfo['shopId'],
                    'nowMoney' => $shopInfo['money'],
                    'changeMoney' => $shopInfo['money'] + $shopMoney,
                    'money' => $shopMoney,
                    'createTime' => time(),
                    'typeId' => $orderId,
                    'type' => 1,
                ]
            );
            // 增加商户余额
            db('shop')->where(['id' => $shopId])->setInc('money', $shopMoney);

            // 商户每日金额记录
            if (empty($shopDayMoneyInfo)) {
                $insertShopData['schoolId'] = $orderInfo['schoolId'];
                $insertShopData['shopId'] = $orderInfo['shopId'];
                $insertShopData['recordDate'] = time();
                $insertShopData['money'] = $shopMoney;
                $insertShopData['price'] = $orderInfo['money'];
                $insertShopData['isOff'] = 0;
                $insertShopData['createTime'] = time();
                $insertShopData['status'] = 0;
                db('shop_money_record')->insert($insertShopData);
            } else {
                db('shop_money_record')->where(['id' => $shopDayMoneyInfo['id']])->setInc('price', $orderInfo['money']);
                db('shop_money_record')->where(['id' => $shopDayMoneyInfo['id']])->setInc('money', $shopMoney);
            }

            // 受益人返现
            if ($benefitUserInfo) {
                db('record_money')->insert(
                    [
                        'userId' => $benefitId,
                        'nowMoney' => $benefitUserInfo['balance'],
                        'changeMoney' => $benefitUserInfo['balance'] + $benefitMoney,
                        'money' => $benefitMoney,
                        'createTime' => time(),
                        'type' => 14,// 受益人
                        'orderId' => $orderId,
                        'orderNo' => $orderInfo['orderNo']
                    ]
                );
            }

            // 推荐人返现
            if ($referrerUserInfo) {
                db('record_money')->insert(
                    [
                        'userId' => $referrerId,
                        'nowMoney' => $referrerUserInfo['balance'],
                        'changeMoney' => $referrerUserInfo['balance'] + $referrerMoney,
                        'money' => $referrerMoney,
                        'createTime' => time(),
                        'type' => 8,// 推荐人
                        'orderId' => $orderId,
                        'orderNo' => $orderInfo['orderNo']
                    ]
                );
            }

            // 课程签约
            db('order_class')->where(['id' => $orderId])->update(
                [
                    'isSign' => 1,
                    'signDate' => time()
                ]
            );

            // 修改顾问订单状态
            db('adviser_order')->where(['orderId' => $orderId, 'orderType' => 3])->update(['status' => 3]);
            operateLog('优选课程签约', 'order_class', $orderId, $adminId);

            // 星星
            if (empty($starUserRuleInfo) && !empty($starUserInfo)) {
                db('star_change')->insert(
                    [
                        'userId' => $shareId,
                        'nowStarNum' => $starUserInfo['starNum'],
                        'changeStar' => $starUserInfo['starNum'] + $starNum,
                        'starNum' => $starNum,
                        'type' => 1,
                        'orderId' => $orderId,
                        'createTime' => time()
                    ]
                );

                // 当前次活动增加星星
                if ($starUserRecordInfo) {
                    db('star_user_record')
                        ->where(['id' => $starUserRecordInfo['id']])
                        ->setInc('starNum', $starNum);
                }
                // 增加个人总星星
                if ($starUserInfo) {
                    db('star_user')
                        ->where(['id' => $starUserInfo['id']])
                        ->setInc('starNum', $starNum);
                }
            } elseif (!empty($starUserRecordInfo)) {
                // 只处理点亮状态！！！
                $insertDataA = [];
                $insertDataB = [];
                // 此次总星星数
                $thisTimeNum = $starUserRecordInfo['starNum'] + $starNum;
                // time() 此处时间应为当前订单实际支付时间 现使用当前时间
                if ($starUserRuleInfo[0]['endTime'] > $payTime) {
                    // 结束时间之前 大于当前星星数 点亮
                    if ($starUserRuleInfo[0]['starNum'] <= $thisTimeNum) {
                        $insertDataA = ['status' => 1, 'id' => $starUserRuleInfo[0]['id']];
                        // 点亮下一级
                        if ($starUserRuleInfo[1]['starNum'] <= $thisTimeNum) {
                            $insertDataB = ['status' => 1, 'id' => $starUserRuleInfo[1]['id']];
                        }
                    }
                } elseif ($starUserRuleInfo[1]['endTime'] > time()) {
                    // 上一级超时未点亮 判断下一级 只判断两级！！！
                    // 结束时间之前 大于当前星星数 点亮
                    if ($starUserRuleInfo[1]['starNum'] <= $thisTimeNum) {
                        $insertDataB = ['status' => 1, 'id' => $starUserRuleInfo[1]['id']];
                    }
                }

                if ($insertDataA) {
                    db('star_rule_user')
                        ->where(['id' => $insertDataA['id']])
                        ->update(['status' => $insertDataA['status']]);
                    pushToUser($userId, $adminId, '星星灯活动提醒', '您有一盏灯点亮了!', 5, true);
                    operateLog('点亮当前等级灯', 'stat_rule_user', $insertDataA['id'], $adminId);
                }
                if ($insertDataB) {
                    db('star_rule_user')
                        ->where(['id' => $insertDataB['id']])
                        ->update(['status' => $insertDataB['status']]);
                    pushToUser($userId, $adminId, '星星灯活动提醒', '您有一盏灯点亮了!', 5, true);
                    operateLog('点亮当前等级灯', 'stat_rule_user', $insertDataA['id'], $adminId);
                }

                // 星星变动表
                db('star_change')
                    ->insert(
                        [
                            'userId' => $shareId,
                            'nowStar' => $starUserInfo['starNum'],
                            'changeStar' => $starUserInfo['starNum'] + $starNum,
                            'starNum' => $starNum,
                            'type' => 1,
                            'orderId' => $orderId,
                            'createTime' => time()
                        ]
                    );
                // 当前次活动增加星星
                if ($starUserRecordInfo) {
                    db('star_user_record')
                        ->where(['id' => $starUserRecordInfo['id']])
                        ->setInc('starNum', $starNum);
                }
                // 增加个人总星星
                if ($starUserInfo) {
                    db('star_user')
                        ->where(['id' => $starUserInfo['id']])
                        ->setInc('starNum', $starNum);
                }
            }

        } catch (Exception $e) {
            Db::rollback();
            dump($e->getMessage());
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * 用户金额变动记录
     * Date: 2017-09-01
     * @param $userId
     * @param $orderId
     * @param $orderNo
     * @param $type
     * @param int $money
     * @return bool
     * fix Date: 2017-09-22
     * fix Content: 增加课程续费收益人奖励 优化:简化形参
     */
    public function insertUserRecord($userId, $orderId, $orderNo, $type, $money = 0)
    {
        $data = db('user')->where(['id' => $userId])->find();
        $insertData['userId'] = $userId;
        $insertData['nowMoney'] = $data['balance'];

        $insertData['type'] = $type;// type = 8推荐人收入 9顾问奖励 14课程续费受益人奖励
        $insertData['changeMoney'] = $data['balance'] + $money;
        $insertData['money'] = $money;

        $insertData['createTime'] = time();
        $insertData['orderId'] = $orderId;
        $insertData['orderNo'] = $orderNo;
        $ret = db('record_money')->insert($insertData);
        if (!$ret) {
            return false;
        }

        return true;
    }

    /**
     * 插入或更新商户金额变动记录以及每日结算记录
     * Date: 2017-09-01
     * @param $shopId
     * @param $schoolId
     * @param $orderId
     * @param $money
     * @param $coursePrice
     * @return bool
     */
    public function insertShopRecord($shopId, $schoolId, $orderId, $money, $coursePrice)
    {
        $shopInfo = db('shop')->where(['id' => $shopId])->find();
        $shopMoney = $shopInfo['money'];

        $insertData['shopId'] = $shopId;
        $insertData['nowMoney'] = $shopMoney;
        $insertData['changeMoney'] = $shopMoney + $money;
        $insertData['money'] = $money;
        $insertData['createTime'] = time();
        $insertData['typeId'] = $orderId;// 课程订单id
        $insertData['type'] = 1;

        Db::startTrans();
        $ret = db('record_shop')->insert($insertData);
        if (!$ret) {
            Db::rollback();
            return false;
        }

        $data = db('shop_money_record')
            ->field('FROM_UNIXTIME(recordDate,"%Y-%m-%d") as rd,id,money,price')
            ->where(['schoolId' => $schoolId, 'shopId' => $shopId])
            ->having('rd = "' . date('Y-m-d', time()) . '"')
            ->find();
        $insertShopData['schoolId'] = $schoolId;
        $insertShopData['shopId'] = $shopId;
        $insertShopData['recordDate'] = time();
        $insertShopData['money'] = $money;
        $insertShopData['price'] = $coursePrice;
        if (!$data) {
            $insertShopData['isOff'] = 0;
            $insertShopData['createTime'] = time();
            $insertShopData['status'] = 0;
            $ret = db('shop_money_record')->insert($insertShopData);
            if (!$ret) {
                Db::rollback();
                return false;
            }
        } else {
            $id = $data['id'];
            $insertShopData['money'] = $data['money'] + $money;
            $insertShopData['price'] = $data['price'] + $coursePrice;
            $ret = db('shop_money_record')->where(['id' => $id])->update($insertShopData);
            if (!$ret) {
                Db::rollback();
                return false;
            }
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
        $ret = db('order_class')->where(['isDispose' => 0])->count('id');
        return $ret;
    }

    /**
     * 续费处理
     * Date: 2017-09-01
     * @param $orderId
     * @param $isAgain
     * @return int|string
     */
    public function handleOrderAgain($orderId, $isAgain)
    {
        $data['isAgain'] = $isAgain;
        $ret = db('order_class')->where(['id' => $orderId])->update($data);
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
        $courseData = db('order_class')->where(['id' => $orderId])->find();
        $isSign = $courseData['isSign'];
        if ($isSign == 1) {
            $data['status'] = 3;
        } else {
            $data['status'] = 2;
        }
        $data['adviserId'] = $adviserId;
        $data['userId'] = $courseData['userId'];
        $data['name'] = $courseData['name'];
        $data['orderId'] = $courseData['id'];
        $data['orderType'] = 3;
        $data['createTime'] = time();

        $where['orderId'] = $orderId;
        $where['orderType'] = 3;

        $ret = 0;
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
     * Date: 2017-09-21
     * 校区受益人列表
     * @param $schoolId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getSchoolBenefitPersonList($schoolId)
    {
        $where['isDelete'] = 0;
        $where['schoolId'] = $schoolId;
        $data = db('user_benefit')->field('id,userId,name,phone')->where($where)->select();
        return $data;
    }

    /**
     * Date: 2017-09-22
     * 分配订单受益人
     * @param $orderId
     * @param $bpId
     * @return bool
     */
    public function assignCourseBenefitPerson($orderId, $bpId)
    {
        $where['id'] = $orderId;
        $data['benefitId'] = $bpId;
        $ret = db('order_class')->where($where)->update($data);
        if ($ret) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date: 2017-09-28
     * 处理星星
     * @param $shareId
     * @param $starNum
     * @param $orderId
     * @param $adminId
     * @return bool
     */
    private function handleStar($shareId, $starNum, $orderId, $adminId)
    {
        $userInfo = db('star_user')
            ->field('id,userId,shareTime,starNum')
            ->where(['userId' => $shareId])
            ->find();
        if (!$userInfo) {
            return false;
        }
        $userInfoId = $userInfo['id'];
        $userId = $userInfo['userId'];
        // $shareTime = $userInfo['shareTime'];
        $nowStarNum = $userInfo['starNum'];

        $recordInfo = db('star_user_record')
            ->field('id,userId,starNum,starRound')
            ->where(['userId' => $shareId, 'status' => 0])
            ->find();
        if (!$recordInfo) {
            return false;
        }
        $recordId = $recordInfo['id'];
        $recordStarNum = $recordInfo['starNum'];

        $ruleInfo = db('star_rule_user')
            ->where(['userId' => $shareId, 'isEnd' => 0, 'status' => 0])
            ->order('level ASC')
            ->limit(2)
            ->select();

        // 未查询到可点灯 视为全部点亮 只累加到当前轮星星数 2017-10-18 调整
        if (!$ruleInfo) {
            try {
                // 金额变动记录
                db('star_change')
                    ->insert(
                        [
                            'userId' => $shareId,
                            'nowStar' => $nowStarNum,
                            'changeStar' => ($nowStarNum + $starNum),
                            'starNum' => $starNum,
                            'type' => 1,
                            'orderId' => $orderId,
                            'createTime' => time()
                        ]
                    );
                // 当前次活动增加星星
                db('star_user_record')->where(['id' => $recordId])->setInc('starNum', $starNum);
                // 增加个人总星星
                db('star_user')->where(['id' => $userInfoId])->setInc('starNum', $starNum);
            } catch (Exception $e) {
                // dump($e->getMessage());
                return false;
            }
            return true;
        }

        // 只处理点亮状态！！！
        $insertDataA = [];
        $insertDataB = [];
        // 此次总星星数
        $thisTimeNum = $recordStarNum + $starNum;
        // time() 此处时间应为当前订单实际支付时间 现使用当前时间
        if ($ruleInfo[0]['endTime'] > time()) {
            // 结束时间之前 大于当前星星数 点亮
            if ($ruleInfo[0]['starNum'] <= $thisTimeNum) {
                $insertDataA = ['status' => 1, 'id' => $ruleInfo[0]['id']];
                // 点亮下一级
                if ($ruleInfo[1]['starNum'] <= $thisTimeNum) {
                    $insertDataB = ['status' => 1, 'id' => $ruleInfo[1]['id']];
                }
            }
        } elseif ($ruleInfo[1]['endTime'] > time()) {
            // 上一级超时未点亮 判断下一级 只判断两级！！！
            // 结束时间之前 大于当前星星数 点亮
            if ($ruleInfo[1]['starNum'] <= $thisTimeNum) {
                $insertDataB = ['status' => 1, 'id' => $ruleInfo[1]['id']];
            }
        }

//        Db::startTrans();
        try {
            if ($insertDataA) {
                db('star_rule_user')
                    ->where(['id' => $insertDataA['id']])
                    ->update(['status' => $insertDataA['status']]);
                pushToUser($userId, $adminId, '星星灯活动提醒', '您有一盏灯点亮了!', 5, true);
                operateLog('点亮当前等级灯', 'stat_rule_user', $insertDataA['id'], $adminId);
            }
            if ($insertDataB) {
                db('star_rule_user')
                    ->where(['id' => $insertDataB['id']])
                    ->update(['status' => $insertDataB['status']]);
                pushToUser($userId, $adminId, '星星灯活动提醒', '您有一盏灯点亮了!', 5, true);
                operateLog('点亮当前等级灯', 'stat_rule_user', $insertDataA['id'], $adminId);
            }
            // 金额变动记录
            db('star_change')
                ->insert(
                    [
                        'userId' => $shareId,
                        'nowStar' => $nowStarNum,
                        'changeStar' => ($nowStarNum + $starNum),
                        'starNum' => $starNum,
                        'type' => 1,
                        'orderId' => $orderId,
                        'createTime' => time()
                    ]
                );
            // 当前次活动增加星星
            db('star_user_record')->where(['id' => $recordId])->setInc('starNum', $starNum);
            // 增加个人总星星
            db('star_user')->where(['id' => $userInfoId])->setInc('starNum', $starNum);
        } catch (Exception $e) {
            // dump($e->getMessage());
//            Db::rollback();
            return false;
        }
//        Db::commit();
        return true;
    }

    /**
     * Date: 2017-10-27
     * 获取万人砍课程列表
     * @param $schoolId
     * @param $orderNo
     * @param $phone
     * @return mixed
     */
    public function getCourseWrk($schoolId, $orderNo, $phone)
    {
        if ($orderNo) {
            $where['awo.orderNo'] = $orderNo;
        }
        if ($phone) {
            $where['u.phone'] = $phone;
        }
        $where['awi.schoolId'] = $schoolId;
        $where['awi.isDispose'] = 1;
        $data = db('activity_wrk_info awi')
            ->field('awi.id,awo.name as wrkName,awc.name as courseName,awo.orderNo,awi.price,awi.money,an.name as adviserName,awi.isSign,awi.signTime,u.name as uName,u.phone,awo.status as orderStatus,awi.adviserId')
            ->join('activity_wrk_order awo', 'awo.id=awi.wrkOrderId', 'LEFT')
            ->join('activity_wrk_class awc', 'awc.id=awi.wrkClassId', 'LEFT')
            ->join('adviser_name an', 'an.id=awi.adviserId', 'LEFT')
            ->join('user u', 'u.id=awi.userId', 'LEFT')
            ->where($where)
            ->order('awi.isSign asc')
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-08-16
     * 万人砍课程签约确认
     * @param $courseOrderId
     * @param $adminId
     * @return int|string
     */
    public function signCourseOrderWrk($courseOrderId, $adminId)
    {
        if (!$courseOrderId) {
            return false;
        }
        $orderInfo = db('activity_wrk_info awi')
            ->field('awi.*,awo.status,awo.orderNo')
            ->join('activity_wrk_order awo', 'awo.id=awi.wrkOrderId', 'LEFT')
            ->where(['awi.id' => $courseOrderId])
            ->find();

        if ($orderInfo['isDispose'] != 1) {
            return false;
        }

        if ($orderInfo['status'] != 1) {
            return false;
        }
        // 没顾问 报错
        if (!$orderInfo['adviserId']) {
            return false;
        }
        $orderId = $courseOrderId;
        $orderNo = $orderInfo['orderNo'];

//        $userId = $orderInfo['userId'];

        //分享人 星星数
        $shareId = $orderInfo['shareId'];
        $starNum = $orderInfo['starNum'];

        //推荐人 返现
        $referrerId = $orderInfo['referrerId'];
        $referrerMoney = $orderInfo['referrerMoney'];

        //商户金额
        $backShopMoney = $orderInfo['shopMoney'];
        // 课程原价
        $coursePrice = $orderInfo['price'];

        $shopId = $orderInfo['shopId'];
        $schoolId = $orderInfo['schoolId'];

        Db::startTrans();

        if ($shareId > 0 && $starNum > 0) {
            $ret = $this->handleStar($shareId, $starNum, $courseOrderId, $adminId);
            if (!$ret) {
                Db::rollback();
                return false;
            }
        }

        // 推荐人返现
        if ($referrerId) {
            // 不查询下单之后绑定的推荐人
            // 生成推荐人金额变动记录
            if ($referrerMoney > 0) {
                $retReferrerRecord = $this->insertUserRecord($referrerId, $orderId, $orderNo, 8, $referrerMoney);
                if (!$retReferrerRecord) {
                    Db::rollback();
                    return false;
                }
                // 增加推荐人余额
                $retReferrer = db('user')->where(['id' => $referrerId])->setInc('balance', $referrerMoney);
                if (!$retReferrer) {
                    Db::rollback();
                    return false;
                }
            }
        }

        $adviserId = $orderInfo['adviserId'];
        $adviserMoney = $orderInfo['adviserMoney'];
        $adviserUserId = db('adviser_name')->where(['id' => $adviserId])->value('userId');
        // 顾问返现
        // 生成顾问金额变动记录
        if ($adviserMoney > 0) {
            $retAdviserRecord = $this->insertUserRecord($adviserUserId, $orderId, $orderNo, 9, $adviserMoney);
            if (!$retAdviserRecord) {
                Db::rollback();
                return false;
            }
            // 增加顾问余额
            $retAdviser = db('user')->where(['id' => $adviserUserId])->setInc('balance', $adviserMoney);
            if (!$retAdviser) {
                Db::rollback();
                return false;
            }
        }

        // 商户金额
        // 生成商户金额变动记录和每日结算记录
        $retShopRecord = $this->insertShopRecord($shopId, $schoolId, $orderId, $backShopMoney, $coursePrice);
        if (!$retShopRecord) {
            Db::rollback();
            return false;
        }
        //增加商户余额
        $retShop = db('shop')->where(['id' => $shopId])->setInc('money', $backShopMoney);
        if (!$retShop) {
            Db::rollback();
            return false;
        }

        $data['isSign'] = 1;
        $data['signTime'] = time();
        $ret = db('activity_wrk_info')->where(['id' => $courseOrderId])->update($data);
        if (!$ret) {
            Db::rollback();
            return false;
        }
        $ret = db('adviser_order')->where(['orderId' => $courseOrderId, 'orderType' => 7])->update(['status' => 3]);
        if (!$ret) {
            Db::rollback();
            return false;
        }
        operateLog('万人砍课程签约', 'activity_wrk_info', $courseOrderId, $adminId);

        Db::commit();
        return true;
    }

    /**
     * Date: 2017-11-02
     * 免费课管理
     * @param $schoolId
     * @param $phone
     * @return mixed
     */
    public function getFreeCourse($schoolId, $phone)
    {
        if ($phone) {
            $where['uc.phone'] = $phone;
        }
        $where['uc.isDispose'] = 1;
        $where['uc.schoolId'] = $schoolId;
        $data = db('user_class uc')
            ->field('uc.*,cs.name as courseName,u.name as uName,u.phone,an.name as adviserName')
            ->join('class_school cs', 'cs.id=uc.classSchoolId', 'LEFT')
            ->join('adviser_name an', 'an.id=uc.adviserId', 'LEFT')
            ->join('user u', 'u.id=uc.userId', 'LEFT')
            ->order('uc.status asc')
            ->where($where)
            ->paginate(10, false);
        return $data;
    }

    /**
     * Date: 2017-11-02
     * 免费体验课 确认体验
     * @param $orderId
     * @param $adminId
     * @return bool
     */
    public function sureFreeCourse($orderId, $adminId)
    {
        $ret = db('adviser_order')->where(['orderId' => $orderId, 'orderType' => 1])->value('id');
        try {
            db('user_class')->where(['id' => $orderId])->update(['status' => 1, 'useTime' => time()]);
            operateLog('免费体验课确认已体验', 'user_class', $orderId, $adminId);
            db('adviser_order')->where(['id' => $ret])->update(['status' => 1]);
            operateLog('顾问关联订单表更新用户免费体验课已体验', 'adviser_order', $ret, $adminId);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

}