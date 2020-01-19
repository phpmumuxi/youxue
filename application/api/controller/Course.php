<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-07-19
 */

//  品牌课程

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\Course as CourseModel;

class Course extends BaseApi
{

    protected $tokenFlag = 0;
    //1.我关注的品牌
    public function courseIndex()
    {
        $data = [];
        if(input('?post.token')){
            $uid = $this->tokenCheck();
            $course = new CourseModel();
            $data = $course->courseIndexDate($uid);
        }
        $this->apiSuccess($data);
    }

    //2.点击品牌页面
    public function brandOne()
    {
        if(input('?post.token')){
            $arr['userId'] = $this->tokenCheck();
        }
        $arr['brandId'] =input('post.brandId/d');
        if(!$arr['brandId'])$this->apiError('paramError');
        $course = new CourseModel();
        $data = $course->brandOneDate($arr);
        $this->apiSuccess($data);
    }

    //3.品牌课程页面
    public function brandClass()
    {
        $post = $this->requestData();
        $post['brandId'] = input('post.brandId/d');
        if(!$post['brandId'])$this->apiError('paramError');
        $course = new CourseModel();
        $data = $course->brandClassDate($post);
        $this->apiSuccess($data);
    }

    //4.关键字搜索
    public function courseKey()
    {
        $post = $this->requestData();
        $post['name'] = input('post.name');
        if(!$post['name'])$this->apiError('paramError');
        $course = new CourseModel();
        $data = $course->courseKeyDate($post);
        $this->apiSuccess($data);
    }

    //5.条件搜索
    public function courseCondition()
    {
        $post = $this->requestData();
        $post['distance'] = input('post.distance',0);
        $post['brandId'] = input('post.brandId',0);
        $post['schoolId'] = input('post.schoolId',0);
        $post['startAge'] = input('post.startAge',0);
        $post['endAge'] = input('post.endAge',0);
        $post['classType'] = input('post.classType',0);
        $post['name'] = input('post.name');
        $course = new CourseModel();
        $data = $course->courseConditionDate($post);
        $this->apiSuccess($data);
    }

    //6.品牌校区
    public function branSchool()
    {
        $cityCode = input('post.cityCode',320100);
        $course = new CourseModel();
        $data = $course->branSchoolDate($cityCode);
        $this->apiSuccess($data);
    }

    //7.课程类型
    public function courseType()
    {
        $course = new CourseModel();
        $data = $course->courseTypeDate();
        $this->apiSuccess($data);
    }

    //8.课程详情
    public function courseOne()
    {
        $arr['classId'] = input('post.classId/d');
        if(!$arr['classId'])$this->apiError('paramError');
        $arr['userId'] = 0;
        if(input('post.token'))$arr['userId'] = $this->tokenCheck();
        $course = new CourseModel();
        $data = $course->courseOneDate($arr);
        $this->apiSuccess($data);
    }

    //9.品牌下课程类型
    public function brandCourseType()
    {
        $brandId = input('post.brandId/d');
        if(!$brandId)$this->apiError('paramError');
        $cityCode = input('post.cityCode',320100);
        $course = new CourseModel();
        $data = $course->brandCourseTypeDate($brandId,$cityCode);
        $this->apiSuccess($data);
    }

    //10.品牌下校区
    public function branOneSchool()
    {
        $arr = [
            'brandId' => input('post.brandId/d'),
            'cityCode' => input('post.cityCode',320100)
        ];
        if(!$arr['brandId'])$this->apiError('paramError');
        $course = new CourseModel();
        $data = $course->branOneSchoolDate($arr);
        $this->apiSuccess($data);
    }

    //11.vip体验课领取
    public function getVipCourse()
    {
        $uid = $this->tokenCheck();
        $userInfo = $this->userInfo();
        if($userInfo['memberLevel']<1){
            $this->apiError('memberLevel');
        }
        if($userInfo['memberEndTime']<time()){
            $a = new \app\common\model\UserMember();
            $r = $a->userMemberInfo($uid);
            if(!$r)$this->apiError('memberLevel');
        }
        $classId = input('post.classId/d');
        if(!$classId)$this->apiError('paramError');
        $arr = [
            'uid' =>$uid,
            'classId' => $classId
        ];
        $course = new CourseModel();
        $data = $course->getVipCourseDate($arr);
        if($data===4)$this->apiError('classMiss');
        if($data===3)$this->apiError('classReceive');
        if($data===2)$this->apiError('failure');
        $this->apiSuccess($data);
    }

    //12.vip体验课立即上课页面
    public function vipCoursePage()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'userId' =>$uid,
            'vipClassId' => input('post.vipClassId/d')
        ];
        if(!$arr['vipClassId'])$this->apiError('paramError');
        $course = new CourseModel();
        $data = $course->vipCoursePageDate($arr);
        $this->apiSuccess($data);
    }

    //13.vip立即上课
    public function useVipCourse()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'userId' =>$uid,
            'vipClassId' => input('post.vipClassId/d')
        ];
        if(!$arr['vipClassId'])$this->apiError('paramError');
        $course = new CourseModel();
        $data = $course->useVipCourseDate($arr);
        if($data===3)$this->apiError('vipClassError');
        if($data===2)$this->apiError('useVipClassError');
        if(!$data)
        $this->apiError('failure');
        else
        $this->apiSuccess($data);
    }

    //14.我的体验课
    public function MyVipCourse()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'userId'=>$uid,
            'page'=>input('post.page',1),
            'pagesize'=>input('post.pagesize',20),
        ];
        $course = new CourseModel();
        $data = $course->MyVipCourseDate($arr);
        $this->apiSuccess($data);
    }

    //15.购买课程
    public function buyCourse()
    {
        $arr = $this->cOrser();
        // print_r($data);exit;
        $course = new CourseModel();
        $data = $course->buyCourseDate($arr);
        $this->apiSuccess($data);
    }

    //16.确认订单
    public function courseOrder()
    {
        $arr = $this->cOrser();
        $arr['isAgain'] = input('post.isAgain/d',0);
        $course = new CourseModel();
        $data = $course->courseOrderDate($arr);
        if(!$data)
        $this->apiError('failure');
        else
        $this->apiSuccess($data);
    }

    //17.确认支付
    public function payCourse()
    {
        $uid = $this->tokenCheck();
        $arr = input('post.');
        $validate = new \think\Validate([
                    'orderNo'  => 'require',
                    'payType'   => 'require|in:1,2,3,4',
                    'money'   => 'require|number',
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }
        // $user = $this->userInfo();
        if($arr['payType']==1){
            $arr['password'] = input('post.password');
            $this->payPassword($arr['password']);
        }
        $arr['uid'] = $uid;
        $course = new CourseModel();
        $data = $course->payCourseDate($arr);
        if(!$data)
        $this->apiError('failure');
        else
        $this->apiSuccess($data);
    }

    //17-1-1 线下支付 扫码支付
    public function courseQRCodePay()
    {
        $arr['uid'] = $this->tokenCheck();
        $arr['orderNo'] = input('post.orderNo');

        if(!$arr['orderNo'] ){
            $this->apiError('paramError');
        }
        $course = new CourseModel();
        $data = $course->courseQRCodePayDate($arr);
        if(!$data)
        $this->apiError('failure');
        else
        $this->apiSuccess($data);
    }

    //17-1-2 线下支付 订单绑定支付
    public function coursePosOrderPay()
    {
        $arr['uid'] = $this->tokenCheck();
        $arr['orderNo'] = input('post.orderNo');
        $arr['posOrderNo']=input('post.posOrderNo');
        if(!$arr['orderNo']||!$arr['posOrderNo']){
            $this->apiError('paramError');
        }
        if(strtolower(substr($arr['posOrderNo'], 0, 3 ))!='ums'){
            $this->apiError('posError');
        }
        $course = new CourseModel();
        $data = $course->coursePosOrderPayDate($arr);
        if(!$data)
        $this->apiError('failure');
        else
        is_array($data)?$this->apiSuccess($data):$this->apiError($data);
    }

    //17-2-1 pos 订单绑定支付后获取订单状态
    public function posOrderStatus()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['orderNo'] = input('post.orderNo');
        $arr['posOrderNo']=input('post.posOrderNo');
        $course = new CourseModel();
        $data = $course->posOrderStatusDate($arr);
        $this->apiSuccess($data);
    }

    //17-2 分笔支付成功后返回数据
    public function payCourseSuccess()
    {
        $uid = $this->tokenCheck();
        $arr = input('post.');
        $validate = new \think\Validate([
                    'orderNo'  => 'require',
                    'payType'   => 'require|in:1,2,3,4,5,6',
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }
        $arr['userId'] = $uid;
        $course = new CourseModel();
        $data = $course->payCourseSuccessDate($arr);
        if($data)$data['payType'] = $arr['payType'];
        $this->apiSuccess($data);
    }

    //18 删除订单
    public function delCourseOrder()
    {
        $uid = $this->tokenCheck();
        $arr['orderNo']=input('post.orderNo');
        if(!$arr['orderNo'])$this->apiError('paramError');
        $arr['userId'] = $uid;
        $course = new CourseModel();
        $data = $course->delCourseOrderDate($arr);
        $this->apiSuccess($data);
    }

    //19课程订单
    public function courseOrderInfo()
    {
        $uid = $this->tokenCheck();
        $arr = input('post.');
        $validate = new \think\Validate([
                    'type'   => 'require|in:1,2,3',
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }
        $arr['page']=input('post.page/d',1);
        $arr['pagesize']=input('post.pagesize/d',20);
        $arr['userId'] = $uid;
        $course = new CourseModel();
        $data = $course->courseOrderInfoDate($arr);
        $this->apiSuccess($data);
    }

    //20课程订单详情
    public function courseOrderOneInfo()
    {
        $uid = $this->tokenCheck();
        $arr = input('post.');
        $validate = new \think\Validate([
                    'type'   => 'require|in:1,2,3',
                    'orderId' => 'require|number',
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }
        $arr['userId'] = $uid;
        $course = new CourseModel();
        $data = $course->courseOrderOneInfoDate($arr);
        $this->apiSuccess($data);
    }

    //21.取消订单
    public function cancelCourseOrder()
    {
        $uid = $this->tokenCheck();
        $arr = input('post.');
        $validate = new \think\Validate([
                    'orderId' => 'require|number',
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }
        $arr['userId'] = $uid;
        $course = new CourseModel();
        $data = $course->cancelCourseOrderDate($arr);
        if(!$data)
        $this->apiError('failure');
        else
        $this->apiSuccess($data);
    }

    //22.关注品牌
    public function attentionBrand()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['brandId'] = input('post.brandId/d');
        if(!$arr['brandId']) $this->apiError('paramError');
        $arr['isDelete'] = input('post.isDelete/d',1);
        $course = new CourseModel();
        $data = $course->attentionBrandDate($arr);
        if(!$data)
        $this->apiError('failure');
        else
        $this->apiSuccess($data);
    }

    private function cOrser()
    {
        $uid = $this->tokenCheck();
        $classId = input('post.classId/d');
        if(!$classId)$this->apiError('paramError');
        $arr['uid'] = $uid;
        $arr['classId'] = $classId;
        return $arr;
    }
    private function requestData()
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

    //23.课程介绍
    public function classHtml()
    {
        $id = input('post.id');
        $course = new CourseModel();
        $data = $course->classHtmlDate($id);
        $this->apiSuccess($data);

    }

    //24.校区介绍
    public function schoolHtml()
    {
        $id = input('post.id');
        $course = new CourseModel();
        $data = $course->schoolHtmlDate($id);
        $this->apiSuccess($data);

    }
    //25.品牌介绍
    public function brandHtml()
    {
        $id = input('post.id');
        $course = new CourseModel();
        $data = $course->brandHtmlDate($id);
        $this->apiSuccess($data);

    }

    // public function txt()
    // {
    //     $arr = input('post.');
    //     $course = new CourseModel();
    //     $data = $course->payClassSuccess($arr);
    //     $this->apiSuccess($data);
    // }
}