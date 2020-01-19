<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-09-14
 */

//  顾问入口

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\Adviser as adviserModel;

class Adviser extends BaseApi
{
    //1.顾问消息
    public function adviserNews()
    {
        $arr = $this->adviserData();
        $adviser = new adviserModel();
        $data = $adviser -> adviserNewsData($arr);
        $this->apiSuccess($data);
    }

    //2.客户信息
    public function adviserUser()
    {
        $arr = $this->adviserData();
        $adviser = new adviserModel();
        $data = $adviser -> adviserUserData($arr);
        $this->apiSuccess($data);
    }

    //3.客户详情
    public function adviserUserInfo()
    {
        $arr = [
            'adviserId' => $this->uid,
        ];
        if(input('post.userId/d')){
            $arr['userId'] = input('post.userId');
        }else{
            $this->apiError('paramError');
        }
        $adviser = new adviserModel();
        $data = $adviser -> adviserUserInfoData($arr);
        $this->apiSuccess($data);
    }

    //4.新增备注
    public function adviserRemark()
    {
        $arr = [
            'adviserId' => $this->uid,
            'userId' => input('post.userId/d'),
            'text' => input('post.text'),
        ];
        if(!$arr['userId']||!$arr['text']){
            $this->apiError('paramError');
        }
        if(strlen($arr['text'])>300)$this->apiError('paramError');
        $adviser = new adviserModel();
        $data = $adviser -> adviserRemarkData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }
    //5.修改备注
    public function adviserAlterRemark()
    {
        $arr = [
            'adviserId' => $this->uid,
            'remarkId' => input('post.remarkId/d'),
            'text' => input('post.text'),
        ];
        if(!$arr['remarkId']||!$arr['text']){
            $this->apiError('paramError');
        }
        if(strlen($arr['text'])>300)$this->apiError('paramError');
        $adviser = new adviserModel();
        $data = $adviser -> adviserAlterRemarkData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //6.删除备注
    public function adviserDelRemark()
    {
        $arr = [
            'adviserId' => $this->uid,
            'remarkId' => input('post.remarkId/d')
        ];
        if(!$arr['remarkId']){
            $this->apiError('paramError');
        }
        $adviser = new adviserModel();
        $data = $adviser -> adviserDelRemarkData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    private function adviserData()
    {
        $data = $this->userInfo();
        if($data['isAdviser']!=1){
            $this->apiError('isAdviser');
        }
        $arr = [
            'adviserId' => $this->uid,
            'page' => input('post.page/d',1),
            'pagesize' => input('post.pagesize/d',20),
        ];
        return $arr;
    }
}