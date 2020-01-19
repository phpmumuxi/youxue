<?php
/**
 * User: LiuTong
 * Date: 2017-09-14
 * Time: 15:17
 */

namespace app\api\model;

use think\Model;
use think\Db;

class Welfare extends Model
{
    /**
     * 福利专区列表
     * Date: 2017-09-14
     * @param $arr
     * @return mixed
     */
    public function getWelfareList($arr)
    {
        $where = [];
        if ($arr['type'] == 1 || $arr['type'] == 2 || $arr['type'] == 3) {
            $where['type'] = $arr['type'];
        } else {
            $where['type'] = 1;
        }
        $where['status'] = 1;
        $data = db('goods_free')
            ->field('id,startTime,endTime,name,price,doudou,vipdoudou,listImg,num,getNum,remark,eventStatus')
            ->where($where)
            ->page($arr['page'], $arr['pageSize'])
            ->order('eventStatus ASC,startTime DESC')
            ->select();

        if ($data) {
            $data = $this->dataHandle($data);
        }

        return $data;
    }

    /**
     * 数据处理
     * Date: 2017-09-14
     * @param $data
     * @return mixed
     */
    private function dataHandle($data)
    {
        $currentTime = date('Y-m-d H:i:s', time());
        foreach ($data as $k => $v) {
            $data[$k]['goodsStatus'] = 0;// 进行中
            if ($v['startTime'] > $currentTime) {
                $data[$k]['goodsStatus'] = 1;// 未开始
            } elseif ($v['endTime'] < $currentTime) {
                $data[$k]['goodsStatus'] = 2;// 已结束
            } elseif ($v['num'] == $v['getNum']) {
                $data[$k]['goodsStatus'] = 3;// 已领完
            }

            $data[$k]['startDate'] = substr($v['startTime'], 0, 10);
            $data[$k]['surplusNum'] = $v['num'] - $v['getNum'];
            $data[$k]['listImg'] = config('imgUrl') . $v['listImg'];

            unset($data[$k]['startTime']);
            unset($data[$k]['endTime']);
            unset($data[$k]['num']);
            unset($data[$k]['getNum']);
        }
        return $data;
    }

    /**
     * 商品详情
     * Date: 2017-09-15
     * @param int $id
     * @param int $uid
     * @return array
     */
    public function getWelfareGoodsDetail($id, $uid)
    {
        $where['id'] = $id;
        $data = db('goods_free')
            ->field('id,startTime,endTime,name,price,doudou,vipdoudou,topImg,intr,descr,num,getNum,remark,shopId,schoolIds,eventStatus')
            ->where($where)
            ->find();
        $data['surplusNum'] = $data['num'] - $data['getNum'];

        $currentTime = date('Y-m-d H:i:s', time());
        $data['goodsStatus'] = 0;// 进行中
        if ($data['startTime'] > $currentTime) {
            $data['goodsStatus'] = 1;// 未开始
        } elseif ($data['endTime'] < $currentTime) {
            $data['goodsStatus'] = 2;// 已结束

            // 已结束
            if ($data['eventStatus'] != 2) {
                $this->updateGFStatus($id, 2);
            }
        } elseif ($data['num'] == $data['getNum']) {
            $data['goodsStatus'] = 3;// 已领完
        }

        // 已开始
        if ($data['goodsStatus'] == 0 && $data['eventStatus'] != 0) {
            $this->updateGFStatus($id, 0);
        }

        unset($data['num']);
        unset($data['getNum']);

        $data['topImg'] = config('imgUrl') . $data['topImg'];

        $data['userGotStatus'] = 0;// 用户领取状态 未领取或未登录、不是会员
        $data['schoolInfo'] = [];
        if ($uid) {
            $goodsId = $id;
            $ret = $this->userGot($data['shopId'], $uid, $goodsId);
            if ($ret == 2) {
                $data['schoolInfo'][] = $this->getUserGotGoodsSchoolInfo($uid, $goodsId);
            }
            if ($ret) {
                $data['userGotStatus'] = $ret;
            }
        }

        $data['stock'] = $this->goodsStock($id);
        return $data;
    }

    /**
     * Date: 2017-10-16
     * 更新活动状态
     * @param $id
     * @param $status
     */
    private function updateGFStatus($id, $status)
    {
        db('goods_free')->where(['id' => $id])->update(['eventStatus' => $status]);
    }

    /**
     * Date: 2017-09-20
     * 用户已领取该商品的校区信息
     * @param $uid
     * @param $goodsId
     * @return mixed
     */
    private function getUserGotGoodsSchoolInfo($uid, $goodsId)
    {
        $schoolId = db('goods_free_get')->where(['uid' => $uid, 'goodsId' => $goodsId])->value('schoolId');
        $data = db('school')->field('id,name,address,phone,latitude,longitude')->where(['id' => $schoolId])->find();
        return $data;
    }


    /**
     * 商品库存、规格信息
     * Date: 2017-09-15
     * @param int $goodsId
     * @return array
     */
    private function goodsStock($goodsId)
    {
        $where['goodsId'] = $goodsId;
        $where['isDelete'] = 0;
        $data = db('goods_free_stock')
            ->field('id,specif,num,img')
            ->where($where)
            ->select();
        foreach ($data as $k => $v) {
            $data[$k]['img'] = config('imgUrl') . $v['img'];
        }
        return $data;
    }

    /**
     * 同一商户 用户 在半年内 不同商品 限领三次
     * @param int $shopId
     * @param int $uid
     * @param int $goodsId
     * @return int
     */
    public function userGot($shopId, $uid, $goodsId)
    {
        $where['uid'] = $uid;
        $where['shopId'] = $shopId;
        $data = db('goods_free_get')->where($where)->select();
        $num = count($data);
        if ($num >= 3) {
            // 31536000/2=15768000 86400*(365/2)=15768000
            // 按默认排序选择第一次领取时间
            $createTime = strtotime($data[0]['createTime']) + 15768000;
            if ($createTime < time()) {
                return 1;// 半年内 同一商户 已领三次
            }
        } else {
            foreach ($data as $k => $v) {
                if ($v['goodsId'] == $goodsId) {
                    return 2;// 已领取过该商品
                }
            }
        }
        return 0;// 半年内领次数未超过三次且未领取该商品
    }

    /**
     * 获取校区地址
     * Date: 2017-09-15
     * @param int $goodsId
     * @param int Or string $schoolIds
     * @param float $longitude
     * @param float $latitude
     * @return array
     */
    public function getWelfareGoodsSchoolAddress($goodsId, $schoolIds, $longitude, $latitude)
    {
        $goodsId = $goodsId;
        $where['id'] = ['exp', "in($schoolIds)"];
        $data = db('school')
            ->field('id,name,address,longitude,latitude,phone')
            ->where($where)
            ->select();

        foreach ($data as $k => $v) {
            $distance = 0;
            $distance = ROUND(
                6378.138 * 2 * ASIN(
                    SQRT(
                        POW(
                            SIN(($latitude * PI() / 180 - $v['latitude'] * PI() / 180) / 2), 2) +
                        COS($latitude * PI() / 180) *
                        COS($v['latitude'] * PI() / 180) *
                        POW(SIN(($longitude * PI() / 180 - $v['longitude'] * PI() / 180) / 2), 2)
                    )
                ) * 1000
            );
            if ($distance < 1000) {
                $distance .= 'm';
            } else {
                $distance = sprintf('%.2f', ($distance / 1000)) . 'km';
            }
            $data[$k]['distance'] = $distance;
        }

        return $data;
    }

    /**
     * 领取商品
     * Date: 2017-09-15
     * @param int $uid
     * @param int $goodsId
     * @param int $schoolId
     * @param int $stockId
     * @param int $type
     * @return string Or int
     */
    public function takeWelfareGoods($uid, $goodsId, $schoolId, $stockId, $type)
    {
        $goodsData = db('goods_free')->field('shopId,num,getNum,startTime,endTime,status,doudou,vipdoudou')->where(['id' => $goodsId])->find();
        if ($goodsData['status'] == 2) {
            return 'welfareGoodsOff';// 商品下架 防止突发下架
        }

        if ($goodsData['num'] == $goodsData['getNum']) {
            $ret = 'welfareGoodsTookOff';// 商品已领完
            if ($type == 3) {
                $ret = 'welfareGoodsExchangeOff';
            }
            return $ret;// 商品已兑换完
        }

        $shopId = $goodsData['shopId'];
        if ($type == 1 || $type == 2) {
            $ret = $this->userGot($shopId, $uid, $goodsId);
            if ($ret == 1) {
                return 'WelfareTookAll';// 半年内已免费领完
            } elseif ($ret == 2) {
                return 'WelfareTook';// 已领过
            } else {
                return $this->doFreeTake($uid, $goodsId, $shopId, $schoolId, $stockId, $type);
            }
        } elseif ($type == 3) {
            // 豆豆兑换
            return $this->doDoudouTake($uid, $goodsId, $shopId, $schoolId, $stockId, $goodsData['vipdoudou'], $goodsData['doudou']);
        }
    }

    /**
     * 免费领取
     * Date: 2017-09-15
     * @param int $uid
     * @param int $goodsId
     * @param int $shopId
     * @param int $schoolId
     * @param int $stockId
     * @param int $type
     * @return string Or int
     */
    private function doFreeTake($uid, $goodsId, $shopId, $schoolId, $stockId, $type)
    {
        $userInfo = db('user')->field('memberLevel,memberEndTime')->where(['id' => $uid])->find();

        $uLevel = $userInfo['memberLevel'];
        if ($type == 2) {
            if ($userInfo['memberEndTime'] > time()) {
                return 'welfareMemberLevelTimeOff';// 会员过期
            }
            $userLevelIsFree = db('user_member')->where(['level' => $uLevel, 'isDelete' => 0, 'isUse' => 1])->value('isFree');
            if (!$userLevelIsFree) {
                return 'welfareLevelNoPermission';// 当前vip等级无免费领权限
            }
        }

        Db::startTrans();

        $whereGFS['id'] = $stockId;
        $gfs = db('goods_free_stock')->field('id,num')->where($whereGFS)->lock(true)->find();
        if ($gfs['num'] == 0) {
            Db::rollback();
            return 'welfareGoodsTookOff';// 商品已领完
        }

        $whereGF['id'] = $goodsId;
        $gf = db('goods_free')->field('id,num,getNum,days,endTime')->where($whereGF)->lock(true)->find();
        if ($gf['num'] == $gf['getNum']) {
            Db::rollback();
            return 'welfareGoodsTookOff';// 商品已领完
        }

        $data['createTime'] = time();
        $data['uid'] = $uid;
        $data['goodsId'] = $goodsId;
        $data['ruleId'] = $stockId;
        $data['doudou'] = 0;
        $data['shopId'] = $shopId;
        $data['schoolId'] = $schoolId;
        $data['status'] = 0;
        $data['availableTime'] = $gf['days'] * 86400 + strtotime($gf['endTime']);
        $ret = db('goods_free_get')->insert($data);
        if (!$ret) {
            Db::rollback();
            return 'failure';// 保存领取记录失败
        }

        $ret = db('goods_free')->where($whereGF)->setInc('getNum');
        if (!$ret) {
            Db::rollback();
            return 'failure';// 更新领取记录失败
        }

        $ret = db('goods_free_stock')->where($whereGFS)->setDec('num');
        if (!$ret) {
            Db::rollback();
            return 'failure';// 更新库存失败
        }

        Db::commit();
        return 0;
    }

    /**
     * 豆豆兑换
     * Date: 2017-09-15
     * @param int $uid
     * @param int $goodsId
     * @param int $shopId
     * @param int $schoolId
     * @param int $stockId
     * @param int $vipdoudou
     * @param int $doudou
     * @return string Or int
     */
    public function doDoudouTake($uid, $goodsId, $shopId, $schoolId, $stockId, $vipdoudou, $doudou)
    {
        Db::startTrans();

        $userInfo = db('user')->field('doudou,memberLevel,memberEndTime')->where(['id' => $uid])->lock(true)->find();
        if ($userInfo['memberEndTime'] > time()) {
            $doudou = $vipdoudou;
        } else {
            $doudou = $doudou;
        }

        if ($userInfo['doudou'] < $doudou) {
            Db::rollback();
            return 'welfareDoudouOff';// 豆豆不足
        }

        $whereGFS['id'] = $stockId;
        $gfs = db('goods_free_stock')->field('id,num')->where($whereGFS)->lock(true)->find();
        if ($gfs['num'] == 0) {
            Db::rollback();
            return 'welfareGoodsExchangeOff';// 商品已兑换完
        }

        $whereGF['id'] = $goodsId;
        $gf = db('goods_free')->field('id,num,getNum,days,endTime')->where($whereGF)->lock(true)->find();
        if ($gf['num'] == $gf['getNum']) {
            Db::rollback();
            return 'welfareGoodsExchangeOff';// 商品已兑换完
        }


        $data['userId'] = $uid;
        $data['nowDoudou'] = $userInfo['doudou'];
        $data['changeDoudou'] = $userInfo['doudou'] - $doudou;
        $data['doudou'] = $doudou;
        $data['createTime'] = time();
        $data['type'] = 9;// 豆豆兑换
        $data['typeId'] = $goodsId;
        $data['availableTime'] = $gf['days'] * 86400 + $gf['endTime'];
        $ret = db('record_doudou')->insert($data);
        if (!$ret) {
            Db::rollback();
            return 'failure';// 保存豆豆变动失败
        }
        unset($data);

        $ret = db('user')->where(['id' => $uid])->setDec('doudou', $doudou);
        if (!$ret) {
            Db::rollback();
            return 'failure';// 更新用户豆豆
        }

        $data['createTime'] = time();
        $data['uid'] = $uid;
        $data['goodsId'] = $goodsId;
        $data['ruleId'] = $stockId;
        $data['doudou'] = $doudou;
        $data['shopId'] = $shopId;
        $data['schoolId'] = $schoolId;
        $data['status'] = 0;
        $ret = db('goods_free_get')->insert($data);
        if (!$ret) {
            Db::rollback();
            return 'failure';// 保存兑换记录失败
        }

        $ret = db('goods_free')->where($whereGF)->setInc('getNum');
        if (!$ret) {
            Db::rollback();
            return 'failure';// 更新兑换记录数失败
        }

        $ret = db('goods_free_stock')->where($whereGFS)->setDec('num');
        if (!$ret) {
            Db::rollback();
            return 'failure';// 更新库存失败
        }

        Db::commit();
        return 0;

    }

    /**
     * 我的福利
     * Date: 2017-09-15
     * @param array $arr
     * @return array
     * fix Date: 2017-09-20
     * fix Content: 增加实物领取的限定时间 availableTime
     */
    public function getWelfareUserList($arr)
    {
        $where['gfg.uid'] = $arr['uid'];
        $data = db('goods_free_get gfg')
            ->field('gfg.id,gf.name,gfg.status,gf.endTime,gfg.doudou,gf.price,gf.listImg,gf.remark,gfg.availableTime')
            ->join('goods_free gf', 'gf.id=gfg.goodsId', 'LEFT')
            ->where($where)
            ->page($arr['page'], $arr['pageSize'])
            ->order('gfg.createTime DESC')
            ->select();

        $currentTime = date('Y-m-d H:i:s', time());
        foreach ($data as $k => $v) {
            $data[$k]['useStatus'] = 0;// 未兑换
            if ($v['availableTime'] < $currentTime) {
                $data[$k]['useStatus'] = 1;// 已过期
            }

            if ($v['status'] == 1) {
                $data[$k]['useStatus'] = 2;// 已兑换
            }

            $data[$k]['listImg'] = config('imgUrl') . $v['listImg'];
            unset($data[$k]['endTime']);
            unset($data[$k]['status']);
        }
        return $data;
    }

    /**
     * 实物领取页面
     * Date: 2017-09-18
     * @param $arr
     * @return mixed
     * fix Date: 2017-09-20
     * fix Content: 增加过期状态 经纬度信息
     * fix Date: 2017-09-27
     * fix Content: 未领取的情况下判断过期状态
     */
    public function getWelfareUserGoodsDetail($arr)
    {
        $where['gfg.uid'] = $arr['uid'];
        $where['gfg.id'] = $arr['id'];
        $data = db('goods_free_get gfg')
            ->field('gfg.id,gfg.status,s.name as schoolName,s.phone as schoolPhone,s.address,s.img,gf.name as goodsName,s.longitude,s.latitude')
            ->join('school s', 's.id=gfg.schoolId', 'LEFT')
            ->join('goods_free gf', 'gf.id=gfg.goodsId', 'LEFT')
            ->where($where)
            ->find();
        $data['img'] = config('imgUrl') . $data['img'];
        if ($data['status'] == 0) {
            $flag = $this->welfareTakeTime($arr['id'], $arr['uid']);
            if (!$flag) {
                $data['status'] = 2;// 超过设置的实物领取时间
            }
        }
        return $data;
    }

    /**
     * 用户实物领取
     * Date: 2017-09-18
     * @param $arr
     * @return int|string
     */
    public function welfareUserRealTake($arr)
    {
        $where['uid'] = $arr['uid'];
        $where['id'] = $arr['id'];
        $where['status'] = 0;

        $data['status'] = 1;
        $data['useTime'] = time();
        $ret = db('goods_free_get')->where($where)->update($data);
        return $ret;
    }

    /**
     * Date: 2017-09-20
     * 是否超过领取时间
     * @param $id
     * @param $uid
     * @return bool
     */
    public function welfareTakeTime($id, $uid)
    {
        $availableTime = db('goods_free_get')->where(['id' => $id, 'uid' => $uid])->value('availableTime');
        if ($availableTime < time()) {
            return false;
        }

        return true;

    }

    /**
     * Date: 2017-09-20
     * 领取成功页面信息
     * @param $goodsId
     * @param $uid
     * @return mixed
     */
    public function getWelfareGoodsEndDaysSchoolInfo($goodsId, $uid)
    {
        $data = db('goods_free')
            ->field('name,remark,days')
            ->where(['id' => $goodsId])
            ->find();

        $address = db('goods_free_get gfg')
            ->join('school s', 's.id=gfg.schoolId', 'LEFT')
            ->where(['goodsId' => $goodsId, 'uid' => $uid])
            ->value('s.address');

        $data['address'] = $address;
        return $data;
    }

    //商品详情
    public function goodsHtmlDate($id)
    {
        $data = db('goods_free')->field('descr')->find($id);
        if ($data) {
            $data['descr'] = htmlspecialchars_decode($data['descr']);
        }
        return $data;
    }

}