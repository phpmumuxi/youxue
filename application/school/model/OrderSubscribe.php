<?php
/**
 * User: LiuTong
 * Date: 2017/11/16
 * Time: 10:16
 */

namespace app\school\model;


use think\Db;
use think\Exception;
use think\Model;

class OrderSubscribe extends Model
{
    /**
     * Date: 2017-11-16
     * 预订订单列表
     * @param $schoolId
     * @return \think\Paginator
     */
    public function getOrderSubscribeList($schoolId)
    {
        $where['os.schoolId'] = $schoolId;
        $where['os.isDelete'] = 0;
        $where['os.status'] = 1;// 去掉已退款的
        $data = db('order_subscribe os')
            ->field('os.*,u.name as uName,u.phone,an.name as adviserName')
            ->join('user u', 'u.id=os.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=os.adviserId', 'LEFT')
            ->where($where)
            ->paginate(10, false);
        return $data;
    }

    /**
     * Date: 2017-11-16
     * 保存下线订单记录
     * @param $data
     * @param $pid
     * @return bool
     */
    public function orderSubscribeSave($data, $pid)
    {
        Db::startTrans();
        try {
            $data['createTime'] = time();
            $data['isDone'] = 0;
            $data['isDelete'] = 0;
            $data['classId'] = 0;
            $data['status'] = 1;
            $data['isOldCustom'] = 2;
            if ($pid) {
                $data['pid'] = $pid;
            }
            $lastId = db('order_subscribe')->insertGetId($data);
            operateLog('增加线下订单记录', 'order_subscribe', $lastId, $data['adminId']);
        } catch (Exception $e) {
            Db::rollback();
            die($e->getMessage());
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-11-16
     * 修改线下订单记录 删除旧的 增加一条新的
     * fix Date: 2017-11-28
     * fix Content: 修改订金时更新尾款记录pid
     * @param $data
     * @param $id
     * @return bool
     */
    public function orderSubscribeUpdate($data, $id)
    {
        $osInfo = db('order_subscribe')->where(['id' => $id])->find();
        Db::startTrans();
        try {
            $data['createTime'] = time();
            $data['isDone'] = 0;
            $data['isDelete'] = 0;
            $data['status'] = 1;
            $data['isOldCustom'] = 2;
            $data['pid'] = $osInfo['pid'];
            db('order_subscribe')->where(['id' => $id])->update(['isDelete' => 1]);
            $lastId = db('order_subscribe')->insertGetId($data);
            if ($osInfo['orderType'] == 1) {
                // 当修改订金记录时 更新尾款记录pid
                db('order_subscribe')->where(['pid' => $id])->update(['pid' => $lastId]);
            }
            operateLog('修改线下订单记录', 'order_subscribe', $id, $data['adminId']);
        } catch (Exception $e) {
            Db::rollback();
            die($e->getMessage());
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-11-16
     * 删除线下订单记录
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function orderSubscribeDelete($id, $adminId)
    {
        Db::startTrans();
        try {
            db('order_subscribe')->where(['id' => $id])->update(['isDelete' => 1]);
            operateLog('删除线下订单记录', 'order_subscribe', $id, $adminId);
        } catch (Exception $e) {
            Db::rollback();
            die($e->getMessage());
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-11-16
     * 查询已填信息
     * @param $id
     * @return mixed
     */
    public function getOrderSubscribeInfo($id)
    {
        $data = db('order_subscribe os')
            ->field('os.*,u.name as uName,u.phone,an.name as adviserName')
            ->join('user u', 'u.id=os.userId', 'LEFT')
            ->join('adviser_name an', 'an.id=os.adviserId', 'LEFT')
            ->where(['os.id' => $id])
            ->find();
        return $data;
    }

    /**
     * Date: 2017-11-16
     * 根据手机号查询用户id和名称
     * @param $phone
     * @return mixed
     */
    public function userInfoGet($phone)
    {
        $data = db('user')->where(['phone' => $phone])->field('id,name')->find();
        return $data;
    }

    /**
     * Date: 2017-11-16
     * 用户是否已经交过订金
     * @param $userId
     * @param $schoolId
     * @return bool
     */
    public function userOrderMoney($userId, $schoolId)
    {
        $ret = db('order_subscribe')->where(['userId' => $userId, 'schoolId' => $schoolId, 'isDone' => 0, 'isDelete' => 0, 'status' => 1])->find();
        return $ret ? true : false;
    }

    /**
     * Date: 2017-11-16
     * 校区顾问列表
     * @param $schoolId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function schoolAdviser($schoolId)
    {
        $data = db('adviser_name')->where(['schoolId' => $schoolId, 'isDelete' => 0])->select();
        return $data;
    }

    /**
     * Date: 2017-11-16
     * 校区课程列表
     * @param $schoolId
     * @return mixed
     */
    public function schoolCourseList($schoolId)
    {
        $where['cs.schoolId'] = $schoolId;
        $where['cs.status'] = 2;
        $where['cs.isDelete'] = 0;
        $where['cr.isDelete'] = 0;
        $where['cr.isOldCustom'] = 1;
        $data = db('class_school cs')
            ->field('cs.id,cs.name,cr.money,cr.againMoney')
            ->join('class_rule cr', 'cr.classSchoolId=cs.id', 'LEFT')
            ->where($where)
            ->select();
        return $data;
    }

    /**
     * Date: 2017-11-16
     * 生成订单
     * fix Date: 2017-11-28
     * fix Content: 增加续费课程判断 续费课程实际生成订单使用课程审核规则2的续费金额
     * @param $id
     * @param $courseId
     * @param $isOld
     * @param $adminId
     * @return bool
     */
    public function orderSubscribeDone($id, $courseId, $isOld, $adminId)
    {
        $courseInfo = db('class_school')->where(['id' => $courseId])->find();
        $courseMoneyInfo = db('class_rule')->where(['classSchoolId' => $courseId, 'isOldCustom' => 1, 'isDelete' => 0])->find();
        //续费课程
        $isAgain = 0;
        if ($isOld == 1) {
            $courseInfo['money'] = $courseMoneyInfo['againMoney'];
            $isAgain = 1;
        }
        $osInfo = db('order_subscribe')->where(['id' => $id])->find();
        $userInfo = db('user')->where(['id' => $osInfo['userId']])->find();
        $courseRuleInfo = db('class_rule')->where(['classSchoolId' => $courseId, 'isDelete' => 0, 'isOldCustom' => 2])->find();
        if (empty($courseInfo) || empty($osInfo) || empty($userInfo) || empty($courseRuleInfo)) {
            return false;
        }

        $shopDayMoneyInfo = db('shop_money_record')
            ->field('FROM_UNIXTIME(recordDate,"%Y-%m-%d") as rd,id,money,price')
            ->where(['schoolId' => $courseInfo['schoolId'], 'shopId' => $courseInfo['shopId']])
            ->having('rd = "' . date('Y-m-d', time()) . '"')
            ->find();

        $shopInfo = db('shop')->where(['id' => $courseInfo['shopId']])->find();
        $recordAdviser = db('adviser_user_school')->where(['userId' => $osInfo['userId'], 'schoolId' => $osInfo['schoolId']])->find();
        $recordSchoolCustom = db('school_custom')->where(['userId' => $osInfo['userId'], 'schoolId' => $osInfo['schoolId']])->find();
        Db::startTrans();
        try {
            // 1.生成用户订单
            $courseOrder['orderNo'] = orderId('C');// 订单编号
            $courseOrder['userId'] = $osInfo['userId'];// 用户id
            $courseOrder['classSchoolId'] = $courseInfo['id'];// 校区课程id
            $courseOrder['money'] = $courseInfo['money'];// 课程金额
            $courseOrder['createTime'] = time();
            $courseOrder['name'] = $courseInfo['name'];// 课程名称
            $courseOrder['img'] = $courseInfo['listImg'];// 列表图片
            $courseOrder['status'] = 1;// 已付款
            $courseOrder['isSign'] = 1;// 已签约
            $courseOrder['isAgain'] = $isAgain;// 不是续费
            $courseOrder['payType'] = 4;// 下线预约支付
            $courseOrder['payDate'] = date('Y-m-d H:i:s', time());
            $courseOrder['referrerId'] = $userInfo['referrerId'];// 用户推荐人
            $courseOrder['adviserId'] = $osInfo['adviserId'];// 顾问
            $courseOrder['benefitId'] = 0;// 非续费不分配受益人
            $courseOrder['brandId'] = $courseInfo['brandId'];// 品牌id
            $courseOrder['shopId'] = $courseInfo['shopId'];// 商户id
            $courseOrder['schoolId'] = $courseInfo['schoolId'];// 校区id
            if ($userInfo['memberLevel'] > 0 && $userInfo['memberEndTime'] > time()) {
                $courseOrder['level'] = $userInfo['memberLevel'];// 用户当前等级
            } else {
                $courseOrder['level'] = 0;// 会员过期或等级为0 用户等级为0
            }
            $courseOrder['userMoney'] = $courseRuleInfo['lZero'];// 用户返现
            $courseOrder['referrerMoney'] = $courseRuleInfo['referrer'];// 推荐人返现
            $courseOrder['adviserMoney'] = $courseRuleInfo['adviser'];// 顾问返现 现在为0 当前时间 2017-11-16
            $courseOrder['shopMoney'] = $courseRuleInfo['shopMoney'];// 商户结算金额
            $courseOrder['benefitMoney'] = $courseRuleInfo['benefitMoney'];// 受益人金额 现在为0 当前时间 2017-11-16
            $courseOrder['isDispose'] = 1;// 跳过校区消息处理 设置为1
            $courseOrder['signDate'] = time();// 签约时间
            $courseOrder['isDelete'] = 0;
            $courseOrder['payMoney'] = $courseInfo['money'];// 支付金额等于课程金额
            $courseOrder['shareId'] = $userInfo['shareId'];// 用户分享人id
            $courseOrder['starNum'] = $courseInfo['starNum'];// 课程星星数
            $courseOrder['isOldCustom'] = $isOld;
            $orderClassId = db('order_class')->insertGetId($courseOrder);

            // 2.生成顾问相关信息
            if (empty($recordAdviser)) {
                db('adviser_user_school')
                    ->insert(
                        [
                            'userId' => $osInfo['userId'],
                            'adviserId' => $osInfo['adviserId'],
                            'schoolId' => $osInfo['schoolId'],
                            'createTime' => time(),
                            'isDelete' => 0,
                        ]
                    );
            }
            db('adviser_order')
                ->insert(
                    [
                        'adviserId' => $osInfo['adviserId'],
                        'userId' => $osInfo['userId'],
                        'name' => $courseInfo['name'],
                        'orderId' => $orderClassId,
                        'orderType' => 3,
                        'status' => 3,
                        'schoolId' => $osInfo['schoolId'],
                        'createTime' => time(),
                        'isShift' => 0,
                        'money' => $courseInfo['money'],
                        'adviserInitId' => $osInfo['adviserId'],
                        'isOldCustom' => 2,
                    ]
                );

            // 3.生成校区相关信息
            if (empty($recordSchoolCustom)) {
                db('school_custom')
                    ->insert(
                        [
                            'createTime' => time(),
                            'userId' => $osInfo['userId'],
                            'schoolId' => $osInfo['schoolId'],
                            'shopId' => $osInfo['shopId']
                        ]
                    );
            }

            // 4.商户结算 推荐人、收益人不返奖励金额 星星灯不记录
            db('record_shop')
                ->insert(
                    [
                        'shopId' => $courseInfo['shopId'],
                        'nowMoney' => $shopInfo['money'],
                        'changeMoney' => $shopInfo['money'] + $courseRuleInfo['shopMoney'],
                        'money' => $courseRuleInfo['shopMoney'],
                        'createTime' => $orderClassId,
                        'typeId' => time(),
                        'type' => 1
                    ]
                );

            // 增加商户余额
            db('shop')->where(['id' => $courseInfo['shopId']])->setInc('money', $courseRuleInfo['shopMoney']);

            // 商户每日金额记录
            if (empty($shopDayMoneyInfo)) {
                $insertShopData['schoolId'] = $courseInfo['schoolId'];
                $insertShopData['shopId'] = $courseInfo['shopId'];
                $insertShopData['recordDate'] = time();
                $insertShopData['money'] = $courseRuleInfo['shopMoney'];// 商户金额
                $insertShopData['price'] = $courseInfo['money'];// 课程价格
                $insertShopData['isOff'] = 0;
                $insertShopData['createTime'] = time();
                $insertShopData['status'] = 0;
                db('shop_money_record')->insert($insertShopData);
            } else {
                db('shop_money_record')->where(['id' => $shopDayMoneyInfo['id']])->setInc('price', $courseInfo['money']);
                db('shop_money_record')->where(['id' => $shopDayMoneyInfo['id']])->setInc('money', $courseRuleInfo['shopMoney']);
            }

            // 更新线下订单状态
            db('order_subscribe')->where(['id' => $osInfo['pid'], 'isDelete' => 0])->update(['classId' => $courseId, 'isDone' => 1, 'isOldCustom' => $isOld]);
            db('order_subscribe')->where(['pid' => $osInfo['pid'], 'isDelete' => 0])->update(['classId' => $courseId, 'isDone' => 1, 'isOldCustom' => $isOld]);
            operateLog('生成用户订单', 'order_subscribe', $id, $adminId);
        } catch (Exception $e) {
            Db::rollback();
            die($e->getMessage());
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-11-17
     * 检测订单金额是否与课程金额相等
     * @param $id
     * @param $isOld
     * @param $courseId
     * @return bool
     */
    public function checkOrderMoney($id, $courseId, $isOld)
    {
//        $courseInfo = db('class_school')->where(['id' => $courseId])->find();
        $courseInfo = db('class_rule')->where(['classSchoolId' => $courseId, 'isOldCustom' => 1, 'isDelete' => 0])->find();
        $osInfo = db('order_subscribe')->where(['id' => $id, 'isDelete' => 0])->find();
        $moneyOne = db('order_subscribe')->where(['id' => $osInfo['pid'], 'isDelete' => 0, 'isDone' => 0])->value('money');
        $moneyTwo = db('order_subscribe')->where(['pid' => $osInfo['pid'], 'isDelete' => 0, 'isDone' => 0, 'status' => 1, 'orderType' => 2])->sum('money');

        $money = 0;
        if ($isOld == 1) {
            $money = $courseInfo['againMoney'];
        } elseif ($isOld == 2) {
            $money = $courseInfo['money'];
        }
//        echo $courseId . '---' . $money . '--' . $moneyOne . '--' . $moneyTwo;die;
        if ($money != ($moneyOne + $moneyTwo)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Date: 2017-11-17
     * 检测是否有尾款
     * @param $id
     * @return bool
     */
    public function checkOtherMoney($id)
    {
        $osInfo = db('order_subscribe')->where(['id' => $id])->find();
        if ($osInfo['orderType'] == 1) {
            $where['pid'] = $id;
            $where['isDelete'] = 0;
            $where['orderType'] = 2;
            $where['status'] = 1;
            $where['isDone'] = 0;
            $ret = db('order_subscribe')->where($where)->find();
            return $ret ? true : false;
        } else {
            return false;
        }
    }

}