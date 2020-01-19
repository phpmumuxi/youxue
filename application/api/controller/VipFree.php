<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-07-20
 */

//  女王特权

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\VipFree as vipFreeModel;

class VipFree extends BaseApi
{
    protected $tokenFlag = 0;

    //1.列表
    public function vipFreeList()
    {
        $arr = [
            'page'=>input('post.page/d',1),
            'pagesize'=>input('post.pagesize/d',20),
        ];
        $vipFree = new vipFreeModel();
        $data = $vipFree->vipFreeListDate($arr);
        $this->apiSuccess($data);
    }

    //2.详情
    public function vipFreeOne()
    {
        $vipFreeId =input('post.vipFreeId/d');
        if(!$vipFreeId)$this->apiError('paramError');
        $vipFree = new vipFreeModel();
        $data = $vipFree->vipFreeOneDate($vipFreeId);
        $this->apiSuccess($data);
    }

    //3.立即领取
    public function getVipFree()
    {
        $uid=$this->tokenCheck();
        $userInfo = $this->userInfo();
        if($userInfo['memberLevel']<1){
            $this->apiError('memberLevel');
        }
        if($userInfo['memberEndTime']<time()){
            $a = new \app\common\model\UserMember();
            $r = $a->userMemberInfo($uid);
            if(!$r)$this->apiError('memberLevel');
        }
        // print_r($userInfo);exit;
        $vipFreeId = input('post.vipFreeId/d');
        if(!$vipFreeId)$this->apiError('paramError');
        $vipFree = new vipFreeModel();
        $a = $vipFree->vipFreeUser($uid,$vipFreeId);
        if($a)$this->apiError('vipFreeGet');
        $info = $vipFree->vipFreeInfo($vipFreeId);
        if($userInfo['memberLevel']<$info['status']){
            $this->apiError('memberLevel');
        }
        $info['vipFreeId'] = $vipFreeId;
        $info['userId'] = $uid;
        $data = $vipFree->getVipFreeDate($info);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //4.立即使用页面
    public function useVipFree()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'userId' => $uid,
            'id' => input('post.freeUseId/d')
        ];
        if(!$arr['id'])$this->apiError('paramError');
         $vipFree = new vipFreeModel();
         $data = $vipFree->useVipFreeDate($arr);
         $this->apiSuccess($data);
    }

    //5.立即使用
    public function getUseVipFree()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'userId' => $uid,
            'id' => input('post.freeUseId/d')
        ];
        if(!$arr['id'])$this->apiError('paramError');
         $vipFree = new vipFreeModel();
         $a = $vipFree->useVipFreeUser($arr);
         if($a['status'])$this->apiError('vipFreeUse');
         $arr['status'] = $a['status'];
         $data = $vipFree->getUseVipFreeDate($arr);
         if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //6.我的特权券
    public function myVipFree()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'page'=>input('post.page/d',1),
            'pagesize'=>input('post.pagesize/d',20),
        ];
        $vipFree = new vipFreeModel();
        $data = $vipFree->myVipFreeDate($uid,$arr);
        $this->apiSuccess($data);
    }

    //7.内容介绍
    public function vipFreeHtml()
    {
        $id = input('post.id');
        $vipFree = new vipFreeModel();
        $data = $vipFree->vipFreeHtmlDate($id);
        $this->apiSuccess($data);
    }
}