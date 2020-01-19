<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-07-24
 */

namespace app\api\model;

use think\Db;
use think\Model;
use app\common\model\UserMoney as UserMoney;
use app\api\model\Back;

class GroupBuying extends Model
{
    //列表
    public function buyListDate($arr)
    {
        $data = db('buy')
            ->field('id as buyId,name,bigImg,money,term,brandNum,isCmbc,explains,type')
            ->where('status = 1')
            ->select();
        return $data;
    }

    //团购品牌
    public function buyBrandDate($arr)
    {
        $data = db('buy_brand')
            ->field('brandId')
            ->where(['isDelete' => 0, 'buyId' => $arr['buyId']])
            ->select();
        $brandIds = array_column($data, 'brandId');
        $data = $this->cityShop(['brandIds' => $brandIds, 'cityCode' => $arr['cityCode']]);
        if (!$data) return $data;
        $brandIds = array_column($data, 'brandId');
        $data = $this->buyBrandInfo($brandIds);
        if (isset($arr['userId'])) {
            $uBrand = $this->userBuyBrand($arr['userId']);
            if ($uBrand) {
                foreach ($data as $k => $v) {
                    $data[$k]['userBuyBrand'] = 0;
                    foreach ($uBrand as $j => $val) {
                        if ($val['brandId'] == $v['brandId']) {
                            $data[$k]['userBuyBrand'] = 1;
                        }
                    }
                }
            }
        }
        return $data;
    }

    //订单
    public function userBuyOrderDate($arr)
    {
        $data = $this->buyInfo($arr);
        if ($data === 3 || $data === 2) return $data;
        $userBrand = $this->userBrand($arr);
        if ($userBrand) return 4;
        $brand = $this->buyBrandInfo($arr['brandIds']);
        $data['brandName'] = array_column($brand, 'name');
        $data['brandIds'] = $arr['brandIds'];
		$data['schoolIds'] = $arr['schoolIds'];
        return $data;
    }

    //确认领取
    public function userBuyGetDate($arr)
    {
        $data = $this->buyInfo($arr);
        if ($data === 3 || $data === 2) return $data;
        if ($data['type'] != 2) return false;
        $userBrand = $this->userBrand($arr);
        if ($userBrand) return 4;
        $schoolIdsArray = $this->getSchoolBrandIdShopId($arr['schoolIds']);
        $publicOrderNo = orderId('BC');
        $array = [];
        for ($i = 0; $i < count($arr['brandIds']); $i++) {
            $array[$i] = [
                'orderNo' => orderId('B'),
                'buyId' => $arr['buyId'],
                'name' => $data['name'],
                'img' => $data['smallImg'],
                'createTime' => time(),
                'brandId' => $arr['brandIds'][$i],
                'shopId' => $schoolIdsArray[$i]['shopId'],
                'schoolId' => $schoolIdsArray[$i]['schoolId'],
                'money' => $data['money'],
                'term' => $data['term'] * 24 * 3600 + time(),
                'userId' => $arr['userId'],
                'status' => 1,
                'isDispose' => 0,
                'publicOrderNo' => $publicOrderNo,
                'type' => 2,
                'termDay' => $data['term']
            ];
        }
        $a = db('order_buy')->insertAll($array);
        if (!$a) return false;
        $data['publicOrderNo'] = $publicOrderNo;
        $data['termDay'] = $data['term'];
        $brand = $this->buyBrandInfo($arr['brandIds']);
        $data['brandName'] = array_column($brand, 'name');
        return $data;
    }

    //确认订单
    public function getBuyOrderDate($arr)
    {
        $data = $this->buyInfo($arr);
        if ($data === 3 || $data === 2) return $data;
        $userBrand = $this->userBrand($arr);
        if ($userBrand) return 4;
        $schoolIdsArray = $this->getSchoolBrandIdShopId($arr['schoolIds']);
        $publicOrderNo = orderId('BC');
        $array = [];
        for ($i = 0; $i < count($arr['brandIds']); $i++) {
            $array[$i] = [
                'orderNo' => orderId('B'),
                'buyId' => $arr['buyId'],
                'name' => $data['name'],
                'img' => $data['smallImg'],
                'createTime' => time(),
                'brandId' => $arr['brandIds'][$i],
                'shopId' => $schoolIdsArray[$i]['shopId'],
                'schoolId' => $schoolIdsArray[$i]['schoolId'],
                'money' => $data['money'],
                'term' => $data['term'] * 24 * 3600 + time(),
                'userId' => $arr['userId'],
                'status' => 0,
                'isDispose' => 0,
                'publicOrderNo' => $publicOrderNo,
                'type' => 2,
                'termDay' => $data['term']
            ];
        }
        $a = db('order_buy')->insertAll($array);
        return $a ? ['money' => $data['money'], 'publicOrderNo' => $publicOrderNo] : 0;
    }

    /**
     * Date: 2017-11-07
     * 会获取校区的商户与品牌对应关系
     * @param $schoolIds
     * @return mixed
     */
    private function getSchoolBrandIdShopId($schoolIds)
    {
        $where['id'] = ['in', $schoolIds];
        $data = db('school')->field('id,shopId,brandId')->where($where)->select();
        $pData = [];
        if ($data) {
            foreach ($data as $k => $v) {
                array_push($pData, [
                    'shopId' => $v['shopId'],
                    'schoolId' => $v['id']
                ]);
            }
        }
        return $pData;
    }

    //取消订单
    public function cancelBuyOrderDate($arr)
    {
        // print_r($arr);exit;
        $a = 0;
        if ($arr['publicOrderNo']) {
            $data = $this->orderStatus($arr);
            if (!$data) return 3;
            if ($data['status']) return 2;
            $a = db('order_buy')->where(['publicOrderNo' => $arr['publicOrderNo'], 'userId' => $arr['userId']])->delete();
        } else {
            $data = db('order_buy')->where(['userId' => $arr['userId'], 'status' => 0])->value('id');
            if (!$data) return false;
            $a = db('order_buy')->where([
                'userId' => $arr['userId'],
                'status' => 0
            ])->delete();
        }
        return $a ? 1 : 0;
    }

    //支付
    public function payBuyDate($arr)
    {
        $data = $this->orderStatus($arr);

        if (!$data) return 3;
        if ($data['status']) return 2;
        switch ($arr['payType']) {
            case '1'://余额
                $arr['money'] = $data['money'];
                $arr['id'] = $data['id'];
                $res = $this->buyBalance($arr, $data);
                break;
            case '2'://支付宝
                $res = $this->buyAlipay($data);
                // $res = $data;
                break;
            case '3'://银行卡
                $res = $this->buyBank($data);
                break;
            case '4'://微信
                $res = $this->buyWeixin($data);
                break;
            default:
                $res = 5;
                break;
        }
        return $res;
    }

    //余额购买
    public function buyBalance($arr, $data)
    {
        $m = new UserMoney();
        $a = $m->reduceUserMoney($arr['money'], 1, $arr['id'], $arr['publicOrderNo'], $arr['userId']);
        if (!($a === true)) return $a;
        $res = $this->payBuySuccess($arr);
        if (!$res) return false;
        $info = $this->returnDate($arr, $data);
        return $info;

    }

    //支付宝支付
    public function buyAlipay($arr)
    {

        $data = [
            'name' => $arr['name'],
            'money' => $arr['money'],
            // 'money' => 0.1,
            'orderNo' => $arr['publicOrderNo']
        ];
        $a = new Back();
        $res = $a->alipayData($data);
        return $res;
    }

    //微信支付
    public function buyWeixin($arr)
    {
        $data = [
            'name' => $arr['name'],
            'money' => $arr['money'],
            // 'money' => 0.1,
            'order_no' => $arr['publicOrderNo']
        ];
        $a = new Back();
        $res = $a->weixinData($data);
        return $res;
    }

    //银行卡支付
    public function buyBank($arr)
    {
        $data = [
            'name' => $arr['name'],
            'money' => $arr['money'],
            // 'money' => 0.1,
            'order_no' => $arr['publicOrderNo']
        ];
        $a = new Back();
        $res = $a->bankData($data, $arr['userId']);
        return $res;
    }

    //支付成功
    public function payBuySuccess($arr)
    {
        Db::startTrans();
        try {
            db('order_buy')->where([
                'publicOrderNo' => $arr['publicOrderNo'],
                'userId' => $arr['userId'],
                'status' => 0
            ])
                ->update([
                    'status' => 1,
                    'payType' => $arr['payType'],
                    'payTime' => time(),
                ]);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }

    //第三方支付回调处理
    public function buySuccess($arr)
    {
        $data = db('order_buy')->where([
            'publicOrderNo' => $arr['orderNo'],
            'status' => 0
        ])->find();
        // print_r($data);exit;
        if (!$data) return false;
        Db::startTrans();
        try {
            db('order_buy')->where([
                'publicOrderNo' => $arr['orderNo'],
                'status' => 0
            ])
                ->update([
                    'status' => 1,
                    'payType' => $arr['payType'],
                    'payTime' => time(),
                    'payRecord' => $arr['payRecord'],
                ]);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }

    //成功之后返回的数据
    public function successDataBuy($arr)
    {
        $info = $this->buySuccessData($arr);

        $data = db('order_buy')->field('money,publicOrderNo,termDay,payType')
            ->where([
                'publicOrderNo' => $arr['publicOrderNo'],
                'userId' => $arr['userId'],
                'status' => 1
            ])->find();
        if (!$data) return false;
        $data['orderMoney'] = $data['money'];
        $data['brandName'] = $info;
        return $data;
    }


    private function buySuccessData($arr)
    {
        $data = db('order_buy')->field('brandId')
            ->where([
                'publicOrderNo' => $arr['publicOrderNo'],
                'userId' => $arr['userId']
            ])
            ->select();
        $ids = array_column($data, 'brandId');
        $data = $this->buyBrandInfo($ids);
        $brandName = array_column($data, 'name');
        return $brandName;
    }

    private function returnDate($arr, $data)
    {
        $info = $this->buySuccessData($arr);
        $array = ['brandName' => $info];
        $array['orderMoney'] = $data['money'];
        $array['money'] = $data['money'];
        $array['publicOrderNo'] = $arr['publicOrderNo'];
        $array['termDay'] = $data['termDay'];
        $array['payType'] = 1;
        return $array;
    }

    //判断订单状态
    private function orderStatus($arr)
    {
        $data = db('order_buy')
            ->field('id,status,publicOrderNo,name,money,termDay,userId')
            ->where(['publicOrderNo' => $arr['publicOrderNo'], 'userId' => $arr['userId']])
            ->find();
        return $data;
    }

    //判断团购
    private function buyInfo($arr)
    {
        $data = db('buy')
            ->field('id as buyId,name,smallImg,money,term,type')
            ->where(['id' => $arr['buyId'], 'status' => 1, 'brandNum' => count($arr['brandIds'])])
            ->find();
        if (!$data) return 3;
        $a = db('buy_brand')
            ->where([
                'brandId' => ['in', $arr['brandIds']],
                'buyId' => $arr['buyId'],
                'isDelete' => 0
            ])->select();
        return $a ? $data : 2;
    }

    //查询该用户已买过的品牌
    private function userBuyBrand($uid)
    {
        $data = db('order_buy')
            ->field('brandId')
            ->where('userId', $uid)
            ->select();
        return $data;
    }

    //查询该用户是否买过的品牌
    private function userBrand($arr)
    {
        $data = db('order_buy')
            ->field('brandId')
            ->where(['userId' => $arr['userId'], 'brandId' => ['in', $arr['brandIds']], 'status' => ['in', [1, 2]]])
            ->select();
        return $data;
    }

    //判断城市
    private function cityShop($arr)
    {
        $data = db('shop')
            ->field('brandId')
            ->where(['brandId' => ['in', $arr['brandIds']], 'cityCode' => $arr['cityCode'], 'status' => ['NEQ', 0]])
            ->group('brandId')
            ->select();
        return $data;
    }

    //获取品牌信息
    private function buyBrandInfo($brandIds)
    {
        $data = db('brand')->field('id as brandId,name,smallImg,explain')
            ->where('id', 'in', $brandIds)
            ->select();
        return $data;
    }

    //我的团购
    public function myBuyDate($arr)
    {
        $data = db('order_buy')
            ->alias('o')
            ->field('o.id as orderBuyId,o.name,o.term,o.img,o.status,b.name as brandName,o.schoolId,o.brandId,0 as schoolName,0 as phone,0 as distance,"" as address')
            ->join('brand b', 'b.id=o.brandId', 'left')
            ->where(['o.userId' => $arr['userId'], 'o.status' => ['in', [1, 2]]])
            ->order('o.status')
            ->page($arr['page'], $arr['pagesize'])
            ->select();
        if (!$data) return $data;
        $schoolIds = array_column($data, 'schoolId');
        if ($schoolIds) {
            $arr['schoolId'] = $schoolIds;
            $schoolInfo = $this->buySchoolInfo($arr);
            foreach ($data as $k => $v) {
                foreach ($schoolInfo as $j => $val) {
                    if ($val['schoolId'] == $data[$k]['schoolId']) {
                        $data[$k]['schoolName'] = $val['schoolName'];
                        $data[$k]['phone'] = $val['phone'];
                        $data[$k]['distance'] = $val['distance'];
                        $data[$k]['address'] = $val['address'];
                    }
                }
            }
        }
        return $data;
    }

    //获取校区信息
    private function buySchoolInfo($arr)
    {
        $field = 'ROUND(6378.138*2*ASIN(SQRT(POW(SIN((' . $arr['lat'] . '*PI()/180-latitude*PI()/180)/2),2)+COS(' . $arr['lat'] . '*PI()/180)*COS(latitude*PI()/180)*POW(SIN((' . $arr['lng'] . '*PI()/180-longitude*PI()/180)/2),2)))*1000)';
        $data = db('school')
            ->field('id as schoolId,name as schoolName,phone,longitude,latitude,address,' . $field . ' AS distance')
            ->where('id', 'in', $arr['schoolId'])
            ->select();
        return $data;
    }

    //根据品牌获取校区
    public function buyBrandSchoolDate($arr)
    {
        $field = 'ROUND(6378.138*2*ASIN(SQRT(POW(SIN((' . $arr['lat'] . '*PI()/180-latitude*PI()/180)/2),2)+COS(' . $arr['lat'] . '*PI()/180)*COS(latitude*PI()/180)*POW(SIN((' . $arr['lng'] . '*PI()/180-longitude*PI()/180)/2),2)))*1000)';
        $data = db('school')
            ->field('id as schoolId,name as schoolName,phone,longitude,latitude,address,' . $field . ' AS distance')
            ->where(['brandId' => $arr['brandId'], 'cityCode' => $arr['cityCode'], 'status' => 1])
            ->select();
        return $data;
    }

    //确认校区
    public function getBuySchoolDate($arr)
    {
        $data = db('order_buy')->field('id,brandId')
            ->where(['id' => $arr['orderBuyId'], 'status' => 1, 'userId' => $arr['userId']])
            ->find();
        if (!$data) return false;
        $school = db('school')->field('id,shopId')
            ->where(['brandId' => $data['brandId'], 'id' => $arr['schoolId']])
            ->find();
        if (!$school) return false;
        $adviser = $this->myAdviserInfo($arr['userId'], $arr['schoolId']);
        $adviserId = $adviser ? $adviser['adviserId'] : 0;
        $a = db('order_buy')->where('id', $data['id'])->update([
            'shopId' => $school['shopId'],
            'schoolId' => $school['id'],
            'adviserId' => $adviserId
        ]);
        return $a ? true : false;
    }

    //立即使用页面
    public function useBuyPageDate($arr)
    {
        $data = db('order_buy')
            ->alias('o')
            ->field('o.id as orderBuyId,o.status,o.term,b.name as brandName,s.name as schoolName,s.phone,s.img,address,longitude,latitude')
            ->join('brand b', 'b.id=o.brandId', 'left')
            ->join('school s', 's.id=o.schoolId', 'left')
            ->where(['o.userId' => $arr['userId'], 'o.status' => ['in', [1, 2]], 'o.id' => $arr['orderBuyId']])
            // ->fetchSql()
            ->find();
        return $data;
    }

    //立即使用
    public function useBuyDate($arr)
    {
        $data = db('order_buy')->field('id,status,adviserId')
            ->where([
                'userId' => $arr['userId'],
                'status' => ['in', [1, 2]],
                'id' => $arr['orderBuyId']
            ])
            ->find();
        if (!$data) return false;
        if ($data['status'] == 2) return 6;

        Db::startTrans();
        try {
            db('order_buy')->where('id', $data['id'])->update([
                'status' => 2,
                'useTime' => time()
            ]);
            if ($data['adviserId']) {
                db('adviser_order')->where(['orderType' => 2, 'orderId' => $data['id']])->update(['status' => 1]);
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }

    //查询该用户在该校区是否有顾问
    public function myAdviserInfo($uid, $schoolId)
    {
        $data = db('adviser_user_school')->where([
            'userId' => $uid,
            'schoolId' => $schoolId,
            'isDelete'=>0
        ])->find();
        return $data;
    }

    public function buyHtmlDate($id)
    {
        $data = db('buy')->field('introduct')->find($id);
        if ($data) {
            $data['introduct'] = htmlspecialchars_decode($data['introduct']);
        }
        return $data;
    }
}