<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-07-20
 */

namespace app\api\model;

use think\Db;
use think\Model;

class VipFree extends Model 
{

    //列表
    public function vipFreeListDate($arr)
    {
        $arr['page'] = ($arr['page'] == 1) ? 0 : ($arr['page'] - 1) * $arr['pagesize'];
        $data = Db::name('vip_free')
            ->field('id as vipFreeId,name,listImg,explains')
            ->where('isDelete=0')
            ->limit($arr['page'],$arr['pagesize'])
            ->select();
        return $data;
    }

    //详情
    public function vipFreeOneDate($id)
    {
        $data = Db::name('vip_free')
            ->field('id as vipFreeId,name,explains,status,vipPromesse')
            ->where('isDelete=0 and id='.$id)
            ->find();
        if($data){
            $data['vipAdd'] = $this->vipFreeAdd($id);
        }
        return $data;
    }

    //立即领取
    public function getVipFreeDate($arr)
    {
        $time = $arr['validity'];
        $arr['createTime'] = time();
        $arr['status'] = 0;
        $arr['validity'] = $arr['validity']*24*3600+time();
        $arr['freeUseId'] = db('vip_use')->insertGetId($arr);
        $arr['validity'] = $time;
        return $arr['freeUseId']?$arr:[];
    }

    //查询商品信息
    public function vipFreeInfo($id)
    {
        $data = Db::name('vip_free')
            ->field('name,status,validity,explains')
            ->where('isDelete=0 and id='.$id)
            ->find();
        return $data;
    }

    //查询用户领取情况
    public function vipFreeUser($uid,$vipFreeId)
    {
        $data = Db::name('vip_use')
            ->where(['userId'=>$uid,'vipFreeId'=>$vipFreeId])
            ->value('id');
        return $data;
    }

    //立即使用页面
    public function useVipFreeDate($arr)
    {
        $data = Db::name('vip_use')
            ->field('id as freeUseId,name,status,validity,vipFreeId')
            ->where($arr)
            ->find();
        $data['vipAdd'] = $this->vipFreeAdd($data['vipFreeId']);
        return $data;
    }

    //查询使用状态
    public function useVipFreeUser($arr)
    {
        $data = Db::name('vip_use')
            ->field('id,status')
            ->where($arr)
            ->find();
        return $data;
    }

    //立即使用
    public function getUseVipFreeDate($arr)
    {
        $a = db('vip_use')->where($arr)->update(['status' => '1','useTime'=>time()]);
        return $a;
    }

    //我的特权券
    public function myVipFreeDate($uid,$arr)
    {
        $arr['page'] = ($arr['page'] == 1) ? 0 : ($arr['page'] - 1) * $arr['pagesize'];
        $data = Db::name('vip_use')
            ->alias('u')
            ->field('u.id as freeUseId,u.status,u.name,u.validity,u.explains,v.listImg')
            ->join('vip_free v','v.id=u.vipFreeId','left')
            ->where('u.userId',$uid)
            ->order('u.status')
            ->limit($arr['page'],$arr['pagesize'])
            ->select();
        return $data;
    }

    private function vipFreeAdd($id)
    {
        $data = Db::name('vip_free_add')
                ->field('vipAdd,vipPhone')
                ->where('vipFreeId',$id)
                ->select();
            return $data;
    }

    public function vipFreeHtmlDate($id)
    {
        $data = db('vip_free')->field('content')->find($id);
        if($data){
            $data['content']=htmlspecialchars_decode($data['content']);
        }
        return $data;
    }
}