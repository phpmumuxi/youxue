<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-09-22
 */

//  星星点灯模块

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\Star as starModel;

class Star extends BaseApi
{

    protected $tokenFlag = 0;
    //1.星星点灯首页
    public function starHome()
    {
        $uid = 0;
        if(input('post.token')){
            $uid = $this->tokenCheck();
        }
       $star = new starModel();
       $data = $star->starHomeData($uid);
       $this->apiSuccess($data);
    }
     //1-2.星星点灯首页2
    public function starHomeTwo()
    {
        $uid = $this->tokenCheck();
        $star = new starModel();
        $data = $star->starHomeDataTwo($uid);
        $this->apiSuccess($data);
    }

    //2.添加分享人
    public function insertShare()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['phone'] = input('post.phone');
        $arr['phone'] = input('post.phone');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$arr['phone'])){
            $this->apiError('phone');
        }
        $star = new starModel();
        $data = $star->insertShareData($arr);
        if($data){
            $data===true?$this->apiSuccess($data):$this->apiError($data);
        }else{
            $this->apiError('failure');
        }
    }

    //3.分享人分信息
    public function shareInfo()
    {
        $uid = $this->tokenCheck();
        $star = new starModel();
        $data = $star->shareInfoData($uid);
        $this->apiSuccess($data);
    }

    //4. 我的星星券
    public function myStarticket()
    {
        $uid = $this->tokenCheck();
        $star = new starModel();
        $data = $star->myStarticketData($uid);
        $this->apiSuccess($data);
    }

    //5.星星收集详情
    public function myStarInfo()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['type'] = input('post.type/d',1);
        $arr['page'] = input('post.page', 1);
        $arr['pagesize'] = input('post.pagesize', 10);
        $star = new starModel();
        $data = $star->myStarInfoData($arr);
        $this->apiSuccess($data);
    }

    //6.兑换课程
    public function starConvertPage()
    {
        $uid = $this->tokenCheck();
        $star = new starModel();
        $data = $star->starConvertPageData($uid);
        $this->apiSuccess($data);
    }
    //7.可选品牌
    public function starBrand()
    {
        $arr['userId']=$this->tokenCheck();
        $arr['cityCode'] = input('post.cityCode',320100);
        $arr['type'] = input('post.type/d',0);
        $star = new starModel();
        $data = $star->starBrandData($arr);
        $this->apiSuccess($data);
    }

    //7-1.品牌校区
    public function starBranSchool()
    {
        $this->tokenCheck();
        $cityCode = input('post.cityCode',320100);
        $star = new starModel();
        $data = $star->starBranSchoolDate($cityCode);
        $this->apiSuccess($data);
    }

    //7-2.课程类型
    public function starCourseType()
    {
        $this->tokenCheck();
        $star = new starModel();
        $data = $star->starCourseTypeDate();
        $this->apiSuccess($data);
    }

    //7-3条件筛选课程
    public function starClass()
    {
        $this->tokenCheck();
        $post = $this->requestDataStar();
        $post['distance'] = input('post.distance',0);
        $post['brandId'] = input('post.brandId',0);
        $post['schoolId'] = input('post.schoolId',0);
        $post['startAge'] = input('post.startAge',0);
        $post['endAge'] = input('post.endAge',0);
        $post['classType'] = input('post.classType',0);
        $star = new starModel();
        $data = $star->starClassData($post);
        $this->apiSuccess($data);
    }

    //8-1.兑换课程详情页
    public function convertClassPage()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['classId'] = input('post.classId/d',0);
        $arr['type'] = input('post.type/d',1);
        if(!$arr['classId'])$this->apiError('paramError');
        if($arr['type']==1){
            $arr['ticketId'] = input('post.ticketId/d',0);
            if(!$arr['ticketId'])$this->apiError('paramError');
        }
        $star = new starModel();
        $data = $star->convertClassPageData($arr);
        $this->apiSuccess($data);
    }

    //8-2.兑换课程
    public function convertClass()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['classId'] = input('post.classId/d',0);
        $arr['classNum'] = input('post.classNum/d',1);
        $arr['type'] = input('post.type/d',1);
        if(!$arr['classId'])$this->apiError('paramError');
        if($arr['type']==1){
            $arr['ticketId'] = input('post.ticketId/d',0);
            if(!$arr['ticketId'])$this->apiError('paramError');
        }
        $star = new starModel();
        $data = $star->convertClassData($arr);
        if(!$data)
        $this->apiError('failure');
        else
        $this->apiSuccess($data);
    }

    //9-1.免费券兑换课程的校区
    public function starFreeSchool()
    {
        $this->tokenCheck();
        $arr['brandId'] = input('post.brandId/d',0);
        $arr['cityCode'] = input('post.cityCode',320100);
        $arr['lat'] = input('post.lat',0);
        $arr['lng'] = input('post.lng',0);
        $star = new starModel();
        $data = $star->starFreeSchoolData($arr);
        $this->apiSuccess($data);
    }

    //9-2. 免费券兑换课程
    public function starFreeClass()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['schoolId'] = input('post.schoolId/d',0);
        $arr['freeId'] = input('post.freeId/d');
        $star = new starModel();
        $data = $star->starFreeClassData($arr);
        if(!$data)
        $this->apiError('failure');
        else
            is_array($data)?$this->apiSuccess($data):$this->apiError($data);
    }

    //10.点灯领取
    public function getStarTicket()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['starId'] = input('post.starId/d',0);
        if(!$arr['starId'])$this->apiError('paramError');
        $star = new starModel();
        $data = $star->getStarTicketData($arr);
        if(!$data)
        $this->apiError('failure');
        else
        $this->apiSuccess($data);
    }

    //11.是否是分享人
    public function isShareUser()
    {
        $uid = $this->tokenCheck();
        $star = new starModel();
        $data = $star->isShareUserData($uid);
        $this->apiSuccess($data);
    }

    private function requestDataStar()
    {
        $post = [
            'cityCode'=>input('post.cityCode',320100),
            'lng'=>input('post.lng',0),
            'lat'=>input('post.lat',0),
            'page'=>input('post.page',1),
            'pagesize'=>input('post.pagesize',20),
        ];
        return $post;
    }

    //12.开始下一轮活动
    public function startStar()
    {
        $uid = $this->tokenCheck();
        $star = new starModel();
        $data = $star->startStarData($uid);
        if(!$data)
        $this->apiError('failure');
        else
        $data===true?$this->apiSuccess($data):$this->apiError($data);
    }
}