<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-07-19
 */

namespace app\api\model;

use think\Db;
use think\Model;
use app\common\libs\DistenceTool as distance;
use app\common\model\UserMoney as UserMoney;
use app\api\model\Back;

class Course extends Model
{
    public function courseIndexDate($uid)
    {
        $data = Db::name('brand')
            ->alias('b')
            ->field('b.id,b.name,b.smallImg')
            ->join('brand_collect c', 'c.brandId = b.id')
            ->where('c.userId', $uid)
            ->where('c.isDelete=1')
            ->order('b.sort desc')
            ->select();
        return $data;
    }

    //品牌课程页
    public function brandOneDate($arr)
    {
        $data = Db::name('brand')
            ->field('bigImg,name')
            ->where('id', $arr['brandId'])
            ->find();
        if (!$data) return $data;
        $re = $this->brandCollect($arr);
        $data['collect'] = $re['sum'];
        $data['isCollect'] = $re['isCollect'] ? 1 : 0;
        return $data;
    }

    //每个品牌关注人数
    private function brandCollect($arr)
    {
        $data['sum'] = Db::name('brand_collect')
            ->where('brandId', $arr['brandId'])
            ->where('isDelete', 1)
            ->count();
        $data['isCollect'] = 0;
        if (isset($arr['userId'])) {
            $arr['isDelete'] = 1;
            $data['isCollect'] = db('brand_collect')->where($arr)->value('id');
        }

        return $data;
    }

    //品牌课程页面
    public function brandClassDate($arr)
    {
        $where = 'c.brandId=' . $arr['brandId'];
        $data = $this->classInfo($arr, $where);
        return $data;
    }

    //关键字搜索
    public function courseKeyDate($arr)
    {
        $where = "c.name like '%" . $arr['name'] . "%'";
        $data = $this->classInfo($arr, $where);
        return $data;
    }

    //条件查询
    public function courseConditionDate($arr)
    {
        $age = $arr['endAge'] && $arr['startAge'] ? ' (c.endAge >=' . $arr['endAge'] . ' and c.startAge <=' . $arr['startAge'] . ') or (c.endAge <=' . $arr['endAge'] . ' and c.startAge >=' . $arr['startAge'] . ')' : '';
        $where = [];
        $whereOr = '';
        if (!$arr['schoolId'] && $arr['lat'] && $arr['lng'] && $arr['distance']) {
            $raidus = $arr['distance'] ? $arr['distance'] : 5000;
            $a = new distance();
            $array = $a->getLocation($arr['lat'], $arr['lng'], $raidus);

            $where['longitude'] = ['between', [$array['min_lng'], $array['max_lng']]];

            $where['latitude'] = ['between', [$array['min_lat'], $array['max_lat']]];
        }
        if ($arr['name']) $where['c.name'] = ['like', "%" . $arr['name'] . "%"];
        if ($arr['brandId']) $where['c.brandId'] = $arr['brandId'];
        if ($arr['schoolId']) $where['c.schoolId'] = $arr['schoolId'];
        if ($arr['classType']) $where['c.typeId'] = $arr['classType'];
        if ($arr['endAge'] && $arr['startAge']) $whereOr = $age;

        $data = $this->classInfo($arr, $where, $whereOr);
        return $data;
    }

    //品牌校区
    public function branSchoolDate($cityCode)
    {
        $data = Db::name('school')
            ->field('id as schoolId,brandId,name as schoolName')
            ->where('cityCode', $cityCode)
            ->where('status', '1')
            ->select();
        $ids = array_column($data, 'brandId');
        $arr = ['id' => ['in', $ids]];
        $brand = $this->branSchoolAll($arr);
        foreach ($brand as $k => $v) {
            foreach ($data as $j => $val) {
                if ($v['brandId'] == $val['brandId']) {
                    $brand[$k]['schoold'][] = $data[$j];
                }
            }
        }
        return $brand;
    }

    //类别
    public function courseTypeDate()
    {
        $data = Db::name('class_type')
            ->field('id,typeId,name')
            ->select();
        $arr = [];
        if ($data) {
            foreach ($data as $key => $value) {
                if ($value['typeId']) {
                    $key = $value['typeId'];
                    unset($value['typeId']);
                    $arr[$key]['type'][] = $value;
                } else {
                    unset($value['typeId']);
                    $arr[$value['id']] = $value;
                }
            }
        }

        $arr = array_values($arr);
        return $arr;
    }

    //课程详情
    public function courseOneDate($arr)
    {
        $data = Db::name('class_school')
            ->alias('c')
            ->field('c.id as classId,c.name,topImg,c.money,startAge,endAge,s.address,phone,longitude,latitude,0 as firstMoney,0 as vipMoney,c.brandId')
            ->join('school s', 's.id = c.schoolId', 'left')
            // ->join('class_rule r', 'c.id = r.classSchoolId', 'left')
            ->where('c.status = 2 and c.isDelete = 0 and c.id=' . $arr['classId'])
            // ->where('r.isDelete=0')
            ->find();
        $data['userVip'] = 0;
        if ($arr['userId']) {
            $re = Db::name('user_class')
                ->where([
                    'brandId' => $data['brandId'],
                    'userId' => $arr['userId']
                ])->value('id');
            if ($re) $data['userVip'] = 1;
        }

        return $data;
    }

    //品牌下课程类型
    public function brandCourseTypeDate($brandId, $cityCode)
    {
        $type = $this->brandClassType($brandId, $cityCode);
        if (!$type) return [];
        $ids = array_column($type, 'typeId');
        $data = Db::name('class_type')
            ->field('id as typeId,name')
            ->where('id', 'in', $ids)
            ->select();
        return $data;
    }

    //品牌下校区
    public function branOneSchoolDate($arr)
    {
        $data = Db::name('school')
            ->field('id as schoolId,name')
            ->where('status = 1 and brandId=' . $arr['brandId'] . ' and cityCode=' . $arr['cityCode'])
            ->select();
        return $data;
    }

    //vip体验课领取
    public function getVipCourseDate($arr)
    {
        $res = Db::name('class_school')
            ->field('id,brandId,shopId,schoolId')
            ->where('id', $arr['classId'])
            ->find();
        if (!$res) return 4;
        $re = Db::name('user_class')
            ->where([
                'brandId' => $res['brandId'],
                'userId' => $arr['uid']
            ])->value('id');
        if ($re) return 3;
        $adviser = $this->myAdviserInfo($arr['uid'], $res['schoolId']);
        $data = [
            'userId' => $arr['uid'],
            'brandId' => $res['brandId'],
            'classSchoolId' => $res['id'],
            'shopId' => $res['shopId'],
            'schoolId' => $res['schoolId'],
            'adviserId' => $adviser ? $adviser['adviserId'] : 0,
            'status' => 0,
            'createTime' => time(),
            'isDispose' => 0
        ];
        $a = db('user_class')->insertGetId($data);
        if (!$a) return 2;
        $data = $this->vipClassInfo(['vipClassId' => $a, 'userId' => $arr['uid']]);
        return [
            'vipClassId' => $data['vipClassId'],
            'status' => $data['status'],
            'brandName' => $data['brandName'],
            'className' => $data['className'],
            'address' => $data['address'],
            'phone' => $data['phone']
        ];
    }

    //vip体验课立即上课页面
    public function vipCoursePageDate($arr)
    {
        $data = $this->vipClassInfo($arr);
        return !$data ? [] : [
            'vipClassId' => $data['vipClassId'],
            'status' => $data['status'],
            'brandName' => $data['brandName'],
            'className' => $data['className'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'longitude' => $data['longitude'],
            'latitude' => $data['latitude'],
            'img' => $data['img']
        ];
    }

    //vip 立即上课
    public function useVipCourseDate($arr)
    {
        $status = db('user_class')
            ->field('status,adviserId')
            ->where(['id' => $arr['vipClassId'], 'userId' => $arr['userId']])
            ->find();
        if (!$status) return 3;
        if ($status['status'] == 1) return 2;
        Db::startTrans();
        try {
            db('user_class')->where(['id' => $arr['vipClassId'], 'userId' => $arr['userId']])->update(['status' => 1]);
            if ($status['adviserId']) {
                db('adviser_order')->where(['orderType' => 1, 'orderId' => $arr['vipClassId']])->update(['status' => 1]);
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

    //我的体验课
    public function MyVipCourseDate($arr)
    {
        $data = db('user_class')
            ->alias('u')
            ->field('u.id as vipClassId,u.status,b.name as brandName,c.name as className,c.listImg,address')
            ->join('brand b', 'b.id=u.brandId', 'left')
            ->join('class_school c', 'c.id=u.classSchoolId', 'left')
            ->join('school s', 's.id=u.schoolId', 'left')
            ->where(['u.userId' => $arr['userId']])
            ->page($arr['page'], $arr['pagesize'])
            // ->fetchsql()
            ->select();
        return $data;

    }

    //查询该用户下团购课是否有顾问
    private function buyUserAdviser($arr)
    {
        $data = db('order_buy')->where([
            'userId' => $arr['uid'],
            'schoolId' => $arr['schoolId']
        ])->value('adviserId');
        return $data;
    }

    //查询体验课信息
    private function vipClassInfo($arr)
    {
        $data = db('user_class')
            ->alias('u')
            ->field('u.id as vipClassId,u.status,u.createTime,u.useTime,b.name as brandName,c.name as className,address,s.phone,longitude,latitude,s.img')
            ->join('brand b', 'b.id=u.brandId', 'left')
            ->join('class_school c', 'c.id=u.classSchoolId', 'left')
            ->join('school s', 's.id=u.schoolId', 'left')
            ->where(['u.id' => $arr['vipClassId'], 'userId' => $arr['userId']])
            ->find();
        return $data;
    }

    //课程类别
    private function brandClassType($brandId, $cityCode)
    {
        $data = Db::name('class_school')
            ->alias('c')
            ->field('typeId')
            ->join('school s', 's.id= c.schoolId', 'left')
            ->where('c.brandId', $brandId)
            ->where('c.status=2 and c.isDelete = 0 and s.cityCode=' . $cityCode)
            ->select();
        return $data;
    }

    //品牌
    private function branSchoolAll($arr)
    {
        $data = Db::name('brand')
            ->field('id as brandId,name as brandName')
            ->where('classNum','gt',0)
            ->where($arr)
            ->select();
        return $data;
    }

    //查询课程信息
    private function classInfo($arr, $where, $whereOr = '')
    {
        // $arr['page'] = ($arr['page'] == 1) ? 0 : ($arr['page'] - 1) * $arr['pagesize'];
        $field = -1;
        if ($arr['lat'] && $arr['lng']) {
            $field = 'ROUND(6378.138*2*ASIN(SQRT(POW(SIN((' . $arr['lat'] . '*PI()/180-latitude*PI()/180)/2),2)+COS(' . $arr['lat'] . '*PI()/180)*COS(latitude*PI()/180)*POW(SIN((' . $arr['lng'] . '*PI()/180-longitude*PI()/180)/2),2)))*1000)';
        }

        $data = Db::name('class_school')
            ->alias('c')
            ->join('school s', 's.id=c.schoolId', 'left')
            ->join('brand b', 'b.id=c.brandId', 'left')
            ->field('c.id as classId,c.name as className,c.listImg,c.money,c.brandId,startAge,endAge,s.name as schoolName,s.address,longitude,latitude,b.name as brandName,' . $field . ' AS distance')
            ->where('c.status=2 and c.isDelete=0 ')
            ->where('s.cityCode=' . $arr['cityCode'])
            // ->where('b.classNum','gt',0)
            // ->where('s.status=1')
            ->where($where)
            ->where($whereOr)
            ->page($arr['page'], $arr['pagesize'])
            ->order('distance')
            // ->fetchsql(1)
            ->select();
        return $data;
    }


    //购买课程
    public function buyCourseDate($arr)
    {
        $data = db('class_school')
            ->alias('cs')
            ->field('cs.id as classId,cs.name,cs.money,b.name as brankName,s.address,cs.schoolId,isOldCustom')
            ->join('brand b', 'b.id=cs.brandId', 'left')
            ->join('school s', 's.id=cs.schoolId', 'left')
            ->where('cs.id', $arr['classId'])
            ->find();
        if (!$data) return $data;
        $data['isAgain'] = 0;
        $data['againMoney'] = $data['money'];
        $r = $this->isCustom(['userId'=>$arr['uid'],'schoolId'=>$data['schoolId']]);
        $res = $this->userMember($arr['uid']);
        $res['classId'] = $arr['classId'];
        $r?$data['isAgain'] = 1:$data['isAgain'] = 0;
        $res['isOldCustom']=$data['isAgain'] == 1?1:2;
        if($data['isOldCustom']==1){
            $res['isOldCustom']=1;
        }
        $re = $this->classRule($res);
        if ($data['isAgain'] == 1) {
            $data['againMoney'] = $re['againMoney'];
        }
        // if(!$data['isAgain']&&$data['isOldCustom']==1){
        //     $data['isAgain'] = 1;
        // }
        //$data['userMoney'] = $re['userMoney'];
        $data['type'] = 0;
        if ($re['type'] == 1) {
            //$data['vipOne'] = $re['vipOne'];
            $data['type'] = 1;
        }
// print_r($re);die;
        return $data;
    }

    //确认订单
    public function courseOrderDate($arr)
    {
        $data = db('class_school')
            ->field('id,name,money,listImg,brandId,shopId,schoolId,starNum,isOldCustom')
            ->where('id', $arr['classId'])
            ->find();
        
        if (!$data) return false;
        $r = $this->isCustom(['userId'=>$arr['uid'],'schoolId'=>$data['schoolId']]);
        $arr['isOldCustom']=1;
        if (!$r) {
            $arr['isAgain'] = 0;
            $arr['isOldCustom']=2;
        }
        $isAgain = $arr['isAgain'];
        if($data['isOldCustom']==1){
            $isAgain = 1;
            $arr['isOldCustom']=1;
        }
        $res = $this->userMember($arr['uid']);
        $res['classId'] = $arr['classId'];
        $res['isOldCustom']=$isAgain  == 1?1:2;
        $re = $this->classRule($res);
        $adviser = $this->myAdviserInfo($arr['uid'], $data['schoolId']);
        $array = [
            'orderNo' => orderId('C'),
            'userId' => $arr['uid'],
            'classSchoolId' => $data['id'],
            'money' =>$arr['isAgain']?$re['againMoney']:$data['money'],
            'name' => $data['name'],
            'img' => $data['listImg'],
            'status' => 0,
            'isSign' => 0,
            'isAgain' => $arr['isAgain'],
            'referrerId' => $res['referrerId'],
            'adviserId' => $adviser ? $adviser['adviserId'] : 0,
            'shopId' => $data['shopId'],
            'schoolId' => $data['schoolId'],
            'brandId' => $data['brandId'],
            'level' => ($res['memberLevel'] == 0 || $res['memberEndTime'] < time()) ? 0 : $res['memberLevel'],
            'userMoney' => $re['userMoney']?$re['userMoney']:0,
            'level' => $re['type'] ? ($re['type'] - 1) : 0,
            'referrerMoney' => $re['referrerMoney']?$re['referrerMoney']:0,
            'adviserMoney' => $re['adviserMoney']?$re['adviserMoney']:0,
            'benefitMoney' => $re['benefitMoney']?$re['benefitMoney']:0,
            'shopMoney' => $re['shopMoney'],
            'isDispose' => 0,
            'isDelete' => 0,
            'createTime' => time(),
            'starNum' => $data['starNum'],
            'shareId' => $res['shareId'],
            'isOldCustom'=>$arr['isOldCustom']
        ];

        $a = db('order_class')->insert($array);

        return $a ? ['orderNo' => $array['orderNo'], 'money' => $array['money']] : false;
    }

    //确认支付
    public function payCourseDate($arr)
    {
        $data = $this->courseOrderNo($arr);
        if (!$data) return false;

        if (!($data['status'] == 0 || $data['status'] == 5)) {
            return false;
        }
        $money = $data['money'] - $data['payMoney'];
        if ($money > 100) {
            if ($arr['money'] < 100 || $arr['money'] > $money) {
                return false;
            }
        } else {
            if ($arr['money'] < $money || $arr['money'] > $money) {
                return false;
            }
        }
        // print_r($data);die;
        switch ($arr['payType']) {
            case 1://余额
                $res = $this->courseBalancepay($arr, $data);
                break;
            case 2://支付宝
                $res = $this->courseAlipay($arr, $data);
                break;
            case 3://银行卡
                $res = $this->courseBankpay($arr, $data);
                break;
            case 4://微信
                $res = $this->courseWerixinpay($arr, $data);
                break;
            default:
                $res = 0;
                break;
        }
        return $res;
    }

    //余额
    private function courseBalancepay($arr, $data)
    {
        $m = new UserMoney();
        $a = $m->reduceUserMoney($arr['money'], 2, $data['id'], $data['orderNo'], $data['userId']);
        if (!($a === true)) return false;
        $data1 = [
            'orderClassId' => $data['id'],
            'orderNo' => orderId('CA'),
            'payType' => 1,
            'userId' => $data['userId'],
            'createTime' => time(),
            'money' => $arr['money'],
            'name' => $data['name'],
            'status' => 1,
            'isDelete' => 0,
            'payTime' => time()
        ];
        $res = $this->payCourseSuccessBalance($data, $data1);
        if (!$res) return false;
        $re = $this->payCourseSuccessDate(['orderNo' => $data['orderNo'], 'userId' => $data['userId']]);
        $re['payType'] = 1;
        return $re;


    }

    //支付宝
    private function courseAlipay($arr, $data)
    {
        $data['money'] = $arr['money'];
        $re = $this->payCourseOrder($data);
        if (!$re) return false;
        $data = [
            'name' => $data['name'],
            'money' => $data['money'],
            'orderNo' => $re['orderNo']
        ];
        $a = new Back();
        $res = $a->alipayData($data);
        return $res;
    }

    //银行卡
    private function courseBankpay($arr, $data)
    {
        $data['money'] = $arr['money'];
        $re = $this->payCourseOrder($data);
        if (!$re) return false;
        $data = [
            'name' => $data['name'],
            'money' => $data['money'],
            'order_no' => $re['orderNo']
        ];
        $a = new Back();
        $res = $a->bankData($data, $arr['uid']);
        return $res;

    }

    //微信
    private function courseWerixinpay($arr, $data)
    {
        $data['money'] = $arr['money'];
        $re = $this->payCourseOrder($data);
        if (!$re) return false;
        $data = [
            'name' => $data['name'],
            'money' => $data['money'],
            'order_no' => $re['orderNo']
        ];
        $a = new Back();
        $res = $a->weixinData($data);
        return $res;
    }

    //pos机扫码
    public function courseQRCodePayDate($arr)
    {
        $res = $this->courseOrderNo($arr);
        if (!$res || !($res['status']==0||$res['status']==5)) return false;
        $data = [];
        $random = '';
        for ($i = 1; $i <= 6; $i++) {
            $random .= chr(rand(97, 122));
        }
        $data['order_no'] = $arr['orderNo'];
        $data['money'] = $res['money']-$res['payMoney'];
        $data['random'] = $random;
        $data['account'] = '125907775810801';
        $encryption = "asf12uq17ad!!3s8!aa;a;kj%d#"; //加密码
        $data['sign'] = md5($data['order_no'] . $data['random'] . $encryption);
        return $data;
    }

    //pos机订单
    public function coursePosOrderPayDate($arr)
    {
        $re = db('pos_order')->where(['posOrderId' => $arr['posOrderNo'], 'isDelete' => 1])->value('id');
        if ($re) return "posDel";
        $re = db('order_class_postwo')->where([
            'extOrderId' => $arr['posOrderNo'],
            'orderState' => ['in', [1, 4]]
        ])->find();

        $r = db('order_class_postwo')->where([
            'extOrderId' => $arr['posOrderNo'],
            'orderId' => $arr['orderNo']
        ])->find();

        if ($re || $r) return 'posOrderError';
        $res = $this->courseOrderNo($arr);
        if (!$res || !($res['status']==0||$res['status']==5)) return 'orderClassMiss';
        $data = [
            'orderClassId' => $res['id'],
            'extOrderId' => $arr['posOrderNo'],
            'status' => 9,
            'money' => $res['money']-$res['payMoney'],
            'orderId' => $arr['orderNo'],
            'createTime' => time(),
            'userId' => $arr['uid'],
            'orderState' => 0
        ];
        $id = db('order_class_postwo')->insertGetId($data);
        $pos = new \app\common\libs\Ptspay();
        $ret = $pos->sign(['order_no' => $arr['posOrderNo']]);
        $ret = json_decode($ret, true);
        if (isset($ret['stateDesc'])) {
            db('order_class_postwo')->update([
                'id' => $id,
                'status' => 10,
                'orderState' => 4
            ]);
        }
        return $ret;
    }

    //余额分笔支付
    private function payCourseSuccessBalance($data, $data1)
    {
        $money = $data['payMoney'] + $data1['money'];
        $array = [
            'payMoney' => $money,
            'id' => $data['id'],
        ];
        if (!$data['payType']) {
            $array['payType'] = 1;
        }
        if (!$data['status']) {
            $array['status'] = 5;
        }
        $data3 = [];
        if ($money == $data['money']) {
            $array['status'] = 1;
            $array['payDate'] = date('Y-m-d H:i:s');

            $arr = $this->userMember($data['userId']);
            $arr['classId'] = $data['classSchoolId'];
            // $arr['isOldCustom']=$data['isAgain']==1?1:2;
            // $re = $this->classRule($arr);
            if ($arr['memberLevel'] != $data['level']) {
                $array['level'] = $arr['memberLevel'];
            }
            //$data['userMoney'] = $re['userMoney'];
            //$array['userMoney'] = $data['userMoney'];
            if (!$arr['isGive']) {
                $data3['isGive'] = 1;
                if ($arr['memberLevel'] == 0) {
                    $data3['memberLevel'] = 1;
                    $num = $this->vipInfo(1);
                    $data3['memberEndTime'] = (time() + $num * 2635200);
                    $array['level'] = 1;
                    $vip = [
                        'orderNo'=>orderId('J'),
                        'memberId'=>0,
                        'name'=>'钻石vip会员',
                        'money'=>0,
                        'createTime'=>time(),
                        'userId'=>$data['userId'],
                        'level'=>1,
                        'month'=>$num,
                        'status'=>1,
                        'type'=>2
                    ];
                }
            }
            //if($data['userMoney']){
                // $m = new UserMoney();
                 // $m->reduceUserMoney($data['userMoney'],5,$data['id'],$data['orderNo'],$data['userId']);
                //$m->reduceUserMoney($data['userMoney'], 7, $data['id'], $data['orderNo'], $data['userId']);
            //}
        }
        // print_r($data);die;
        Db::startTrans();
        try {
            db('order_class_pay')->insert($data1);
            db('order_class')->update($array);
            if ($data3) {
                $data3['id'] = $data['userId'];
                db('user')->update($data3);
                if(isset($vip)){
                    db('order_member')->insert($vip);
                }
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // echo $e->getMessage();exit;
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }

    //查询会员
    public function vipInfo($num)
    {
        $data = db('user_member')->where('isUse=1 and level=' . $num)->value('month');
        return $data;
    }

    //支付成功
    public function payClassSuccess($arr)
    {
        $data = db('order_class_pay')->where('orderNo="' . $arr['orderNo'] . '" and status=0 and isDelete=0')->find();
        if (!$data) return false;
        $res = db('order_class')->field('id,orderNo,userId,money,status,payType,userMoney,payMoney,level,classSchoolId,isAgain')->where('id=' . $data['orderClassId'] . ' and isDelete=0')->find();

        $arr['id'] = $data['id'];
        $arr['status'] = 1;
        $arr['payTime'] = time();

        // $a = $m->reduceUserMoney($data['money'],2,$res['id'],$res['orderNo'],$res['userId']);
        // if(!($a===true))return false;
        $money = $data['money'] + $res['payMoney'];
        $array = [
            'id' => $res['id'],
            'payMoney' => $money
        ];
        $data3 = [];
        if ($money == $res['money']) {
            $array['status'] = 1;
            $array['payDate'] = date('Y-m-d H:i:s');
            $a = $this->userMember($res['userId']);
            $a['classId'] = $res['classSchoolId'];
            // $a['isOldCustom']=$res['isAgain']==1?1:2;
            // $re = $this->classRule($a);
            if ($a['memberLevel'] != $res['level']) {
                $array['level'] = $a['memberLevel'];
            }
            // $res['userMoney'] = $re['userMoney'];
            // $array['userMoney'] = $re['userMoney'];
            if (!$a['isGive']) {
                $data3['isGive'] = 1;
                if ($a['memberLevel'] == 0) {
                    $data3['memberLevel'] = 1;
                    $num = $this->vipInfo(1);
                    $data3['memberEndTime'] = time() + $num * 2635200;
                    $array['level'] = 1;
                    $vip = [
                        'orderNo'=>orderId('J'),
                        'memberId'=>0,
                        'name'=>'钻石vip会员',
                        'money'=>0,
                        'createTime'=>time(),
                        'userId'=>$res['userId'],
                        'level'=>1,
                        'month'=>$num,
                        'status'=>1,
                        'type'=>2
                    ];
                }
            }
//             $m = new UserMoney();
// //            $m->reduceUserMoney($res['userMoney'],5,$res['id'],$res['orderNo'],$res['userId']);
//             $m->reduceUserMoney($res['userMoney'], 7, $res['id'], $res['orderNo'], $res['userId']);
        } else {
            $array['status'] = $res['status'] ? $res['status'] : 5;
        }
        $array['payType'] = $res['payType'] ? $res['payType'] : 1;

        Db::startTrans();
        try {
            db('order_class_pay')->update($arr);
            db('order_class')->update($array);
            if ($data3) {
                $data3['id'] = $data['userId'];
                db('user')->update($data3);
                if(isset($vip)){
                    db('order_member')->insert($vip);
                }

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

    //第三方分笔支付生成订单
    private function payCourseOrder($data)
    {
        $arr = [
            'orderClassId' => $data['id'],
            'orderNo' => orderId('CA'),
            'userId' => $data['userId'],
            'createTime' => time(),
            'money' => $data['money'],
            'name' => $data['name'],
            'status' => 0,
            'isDelete' => 0
        ];
        $re = db('order_class_pay')->insert($arr);
        return $re ? ['orderNo' => $arr['orderNo']] : false;
    }

    //分笔支付后返回数据
    public function payCourseSuccessDate($arr)
    {
        $data = db('order_class oc')
            ->field('oc.name,oc.orderNo,oc.money,oc.payMoney,oc.userMoney,b.name as brandName,s.address')
            ->join('brand b', 'b.id=oc.brandId', 'left')
            ->join('school s', 's.id = oc.schoolId', 'left')
            ->where('orderNo="' . $arr['orderNo'] . '" and userId=' . $arr['userId'])
            ->find();
        return $data;
    }

    //查询订单数据
    private function courseOrderNo($arr)
    {
        $data = db('order_class')->field('id,orderNo,userId,money,name,status,level,payMoney,payType,userMoney,level,classSchoolId,isAgain')->where(['orderNo' => $arr['orderNo'], 'userId' => $arr['uid'], 'isDelete' => 0])->find();
        return $data;
    }

    //获取顾问id
    private function adviserIdValue($arr)
    {
        $res = db('order_class')->where(['userId' => $arr['uid'], 'schoolId' => $arr['schoolId'], 'isDelete' => 0])->order('id desc')->value('adviserId');
        if ($res) return $res;
        $res = db('user_class')->where(['userId' => $arr['uid'], 'schoolId' => $arr['schoolId']])->value('adviserId');
        return $res ? $res : 0;
    }

    //查询课程规则数据
    private function classRule($arr)
    {
        $type = 0;
        if ($arr['memberLevel'] == 0 || $arr['memberEndTime'] < time()) {
            // $re = $this->firstCourseBuy($arr['uid']);
            $type = $arr['isGive'] ? 0 : 1;
        } else {
            $type = $arr['memberLevel'] + 1;
        }
        $res = db('class_rule')->field('money,againMoney,lZero,lOne,lTwo,lThree,lFour,adviser,referrer,shopMoney,benefitMoney')
            ->where(['classSchoolId' => $arr['classId'], 'isDelete' => 0])
            ->where('isOldCustom='.$arr['isOldCustom'])
            ->find();
        // print_r($arr);
        $data = [
            'money' => $res['money'],
            'againMoney' => $res['againMoney'],
            'adviserMoney' => $res['adviser'],
            'referrerMoney' => $res['referrer'],
            'benefitMoney' => $res['benefitMoney'],
            'shopMoney' => $res['shopMoney'],
        ];
        switch ($type) {
            case 1:
                $data['userMoney'] = $res['lZero'];
                $data['vipOne'] = $res['lOne'];
                break;
            case 2:
                $data['userMoney'] = $res['lOne'];
                break;
            case 3:
                $data['userMoney'] = $res['lTwo'];
                break;
            case 4:
                $data['userMoney'] = $res['lThree'];
                break;
            case 5:
                $data['userMoney'] = $res['lFour'];
                break;
            default:
                $data['userMoney'] = 0;
                break;
        }
        $data['type'] = $type;
        return $data;

    }

    //判断是否是首次购买
    private function firstCourseBuy($uid)
    {
        $data = db('order_class')->where(['userId' => $uid, 'isDelete' => 0, 'status' => ['neq', '0']])->count();
        return $data;
    }

    //获取用户信息
    public function userMember($uid)
    {
        $data = Db::name('user')
            ->field('isAdviser,isReferrer,memberLevel,memberEndTime,balance,referrerId,isGive,referrerId,shareId')
            ->where('id', $uid)
            ->find();
        if ($data['memberLevel'] && $data['memberEndTime'] < time()) {
            $a = new \app\common\model\UserMember();
            $r = $a->userMemberInfo($uid);
            if ($r) {
                $data['memberLevel'] = $r['memberLevel'];
                $data['memberEndTime'] = $r['memberEndTime'];
            }
        }
        return $data;
    }

    //删除子订单
    public function delCourseOrderDate($arr)
    {
        $data = db('order_class')->where('orderNo="' . $arr['orderNo'] . '" and userId=' . $arr['userId'] . ' and isDelete=0')->value('id');
        if (!$data) return false;
        $a = db('order_class_pay')->where('orderClassId=' . $data . ' and status=0 and isDelete=0')->update(['isDelete'=>1]);
        return $a ? true : false;
    }

    //课程订单
    public function courseOrderInfoDate($arr)
    {
        $where = '';
        switch ($arr['type']) {
            case 1:
                $where['o.status'] = 0;
                break;
            case 2:
                $where['o.status'] = 5;
                break;
            case 3:
                $where['o.status'] = ['in', [1, 4]];
                break;
            default:
                return false;
                break;
        }
        $where['userId'] = $arr['userId'];
        $where['o.isDelete'] = 0;
        $data = db('order_class o')
            ->field('o.id as orderId,o.name,o.money,o.payMoney,b.name as brandName,s.address,o.status,o.img')
            ->join('brand b', 'b.id=o.brandId', 'left')
            ->join('school s', 's.id=o.schoolId', 'left')
            ->where($where)
            ->page($arr['page'], $arr['pagesize'])
            ->order('o.id desc')
            ->select();
        return $data;
    }

    //订单详情
    public function courseOrderOneInfoDate($arr)
    {
        $data = db('order_class o')
            ->field('o.id as orderId,orderNo,o.name,b.name as brandName,s.address,money,payMoney,userMoney,o.payType,o.payDate')
            ->join('brand b', 'b.id=o.brandId', 'left')
            ->join('school s', 's.id=o.schoolId', 'left')
            ->where(['o.id' => $arr['orderId'], 'o.userId' => $arr['userId'], 'o.isDelete' => 0])
            ->find();
        if (!$data) return $data;
        if ($arr['type'] == 2 || $arr['type'] == 3) {
            $data['payInfo'] = db('order_class_pay')->field('payType,paytime,money')->where(['orderClassId' => $arr['orderId'], 'userId' => $arr['userId'], 'status' => 1])->select();
        }
        return $data;
    }

    //删除订单
    public function cancelCourseOrderDate($arr)
    {
        $data = db('order_class')->field('id,status')->where(['id' => $arr['orderId'], 'userId' => $arr['userId'], 'isDelete' => 0])->find();
        if (!$data) return false;
        if ($data['status']) return false;
        Db::startTrans();
        try{
            db('order_class')->update(['isDelete' => 1, 'id' => $data['id']]);
            db('order_class_pay')->where('orderClassId='.$data['id'])->delete();
            // 提交事务
            Db::commit();
        }catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }

    //关注品牌
    public function attentionBrandDate($arr)
    {
        $data = db('brand_collect')->where(['brandId' => $arr['brandId'], 'userId' => $arr['userId']])->find();
        $num = db('brand_home')->where('brandId', $arr['brandId'])->value('likeNum');
        // print_r($data);die;
        $likeNum = $num;
        if ($arr['isDelete'] === 1) {
            if ($data) {
                if ($data['isDelete'] == 1) {
                    return false;
                }
                db('brand_collect')->update(['id' => $data['id'], 'isDelete' => 1]);
            } else {
                $arr['createTime'] = time();
                db('brand_collect')->insert($arr);
            }
            $likeNum = $num + 1;
        } else {
            if ($data) {
                if ($data['isDelete'] != 1) {
                    return false;
                }
                db('brand_collect')->update(['id' => $data['id'], 'isDelete' => 0]);
            } else {
                return false;
            }
            $likeNum = $num - 1;
        }
        db('brand_home')->where('brandId', $arr['brandId'])->update(['likeNum' => $likeNum]);

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

    //课程介绍
    public function classHtmlDate($id)
    {
        $data = db('class_school')->field('content')->find($id);
        if ($data) {
            $data['content'] = htmlspecialchars_decode($data['content']);
        }
        return $data;
    }

    //校区介绍
    public function schoolHtmlDate($id)
    {
        $data = db('school')->field('intr')->find($id);
        if ($data) {
            $data['intr'] = htmlspecialchars_decode($data['intr']);
        }
        return $data;
    }

    //品牌介绍
    public function brandHtmlDate($id)
    {
        /*
        $data = db('school')->field('intr')->find($id);
        if($data){
            $data['intr']=htmlspecialchars_decode($data['intr']);
        }
        return $data;
        */
        $data = db('brand')->field('intr')->find($id);
        if ($data) {
            $data['intr'] = htmlspecialchars_decode($data['intr']);
        }
        return $data;
    }

    //pos机订单绑定状态
    public function posOrderStatusDate($arr)
    {
        $data = db('order_class_postwo')
            ->field('status,orderState')
            ->where([
                'orderId' => $arr['orderNo'],
                'extOrderId' => $arr['posOrderNo'],
                'userId' => $arr['userId']
            ])->find();
        return $data;
    }

    //判断是否是该校区的老用户
    private function isCustom($arr)
    {
        $data = db('school_custom')->where([
                'userId'=>$arr['userId'],
                'schoolId'=>$arr['schoolId']
            ])->value('id');
        return $data;
    }


}