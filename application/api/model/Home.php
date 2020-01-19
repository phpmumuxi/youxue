<?php
/**
 * Created by PhpStorm.
 * User: Liu
 * Date: 2017-07-05
 * Time: 15:13
 * revise 2017-07-18
 * User: xin
 */

namespace app\api\model;

use think\Db;
use think\Model;

class Home extends Model
{

    public function getAddress()
    {
        $data = Db::name('shop')
            ->alias('s')
            ->field('cityCode,cityName')
            ->where('s.classNum != 0')
            ->where('status = 1')
            ->group('s.cityCode')
            ->order('s.cityCode')
            // ->fetchSql()
            ->select();
        return $data;
    }

    //banner
    public function getBanner()
    {
        $data = Db::name('advertise')
            ->field('img,type,url')
            ->where('isDelete = 0')
            ->order('sort desc')
            ->select();
        return $data;
    }

    //homeIco
    public function getHomeIco()
    {
        $data = Db::name('home_ico')
            ->field('img,name,type')
            ->where('status = 1')
            ->order('sort desc')
            ->select();
        return $data;
    }

    //优选品牌
    public function getbrand($cityCode)
    {
        $ids = $this->shopBrand($cityCode);
        if (!$ids) return $ids;
        $data = Db::name('brand_home')
            ->field('brandId,homeImg,likeNum,explain,name')
            ->where('brandId', 'in', $ids)
            ->order('sort desc')
            // ->fetchSql()
            ->select();
        return $data;
    }

    //所有品牌
    public function brandAll($cityCode)
    {
        $ids = $this->shopBrand($cityCode);
        if (!$ids) return $ids;
        $data = Db::name('brand')
            ->field('id,name,smallImg')
            ->where('id', 'in', $ids)
            ->where('classNum','gt',0)
            ->order('sort desc')
            // ->fetchSql()
            ->select();
        return $data;
    }

    private function shopBrand($cityCode)
    {
        $data = Db::name('shop')
            ->where([
                'cityCode' => $cityCode,
                'status' => 1
            ])->column('brandId');
        return $data;
    }

    //短信验证
    public function sendSMSData($phone)
    {
        $data = db('phone_code')->where([
            'phone' => $phone,
            'status' => 0
        ])->find();
        if (!$data) return true;
        $time = $data['createTime'] + 60;
        if ($time > time()) {
            return 'msgError';
        }
        db('phone_code')->update([
            'id' => $data['id'],
            'status' => 2
        ]);
        return true;
    }

    public function sendSMSJoinData($arr)
    {
        $a = db('phone_code')->insert($arr);
        return $a ? true : false;
    }

    public function userData($uid)
    {
        $data = db('user')->where('id', $uid)->value('phone');
        return $data;
    }

    public function introductionData($type)
    {
        $data = db('informat')->field('id,name,content')->where(['type' => $type, 'isDelete' => 0])->find();
        if ($data) {
            $data['content'] = htmlspecialchars_decode($data['content']);
        }
        return $data;
    }

    public function districtData($arr)
    {
        $add = $this->getAddress();
        $district = array_column($add, 'cityCode');
        return (in_array($arr['district'], $district)) ? 1 : 0;
    }

    public function appVersionData($type)
    {
        if ($type == 1) {
//            $data = db('app_android')->order('id desc')->value('version');
            $data = db('app_android')->field('version,url')->order('id desc')->find();
        } else {
//            $data = db('app_ios')->order('id desc')->value('version');
            $data = db('app_ios')->field('version')->order('id desc')->find();
        }
        return $data;
    }

}