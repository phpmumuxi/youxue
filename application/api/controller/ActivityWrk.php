<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-10-27
 */

//  万人砍模块

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\ActivityWrk as ActivityWrkModel;

class ActivityWrk extends BaseApi
{
    protected $tokenFlag = 0;

    //1.活动列表
    public function activityList()
    {
        $arr = [
            'page'=>input('post.page/d',1),
            'pagesize'=>input('post.pagesize/d',10),
        ];
        $activity = new ActivityWrkModel();
        $data = $activity->activityListData($arr);
        $this->apiSuccess($data);
    }

    //2.活动详情
    public function activityInfo()
    {
        $wrkId = input('post.wrkId/d');
        $activity = new ActivityWrkModel();
        $data = $activity->activityInfoData($wrkId);
        $this->apiSuccess($data);
    }

    //3.开抢
    public function activityOrder()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['wrkId'] = input('post.wrkId/d');
        if(!$arr['wrkId']){
            $this->apiError('paramError');
        }
        $activity = new ActivityWrkModel();
        $data = $activity->activityOrderData($arr);
        $this->apiSuccess($data);
    }

    //4.确认订单
    public function getActivityOrder()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['wrkId'] = input('post.wrkId/d');
        if(!$arr['wrkId']){
            $this->apiError('paramError');
        }
        $activity = new ActivityWrkModel();
        $data = $activity->getActivityOrderData($arr);
        $this->apiSuccess($data);
    }

    //5.支付
    public function payActivityOrder()
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

        if($arr['payType']==1){
            $arr['password'] = input('post.password');
            $this->payPassword($arr['password']);
        }
        $arr['userId'] = $uid;
        $activity = new ActivityWrkModel();
        $data = $activity->payActivityOrderData($arr);
        if(!$data){
            $this->apiError('failure');
        }else{
            $this->apiSuccess($data);
        }
    }

    //5-1 线下支付 扫码支付
    public function wrkQRCodePay()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['orderNo'] = input('post.orderNo');

        if(!$arr['orderNo'] ){
            $this->apiError('paramError');
        }
        $activity = new ActivityWrkModel();
        $data = $activity->wrkQRCodePayData($arr);
        if(!$data){
            $this->apiError('failure');
        }else{
            $this->apiSuccess($data);
        }
    }

    //6 分笔支付成功后返回数据
    public function payWrkSuccess()
    {
        $uid = $this->tokenCheck();
        $arr = input('post.');
        $validate = new \think\Validate([
                    'orderNo'  => 'require',
                    'payType'   => 'require|in:1,2,3,4,5',
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }
        $arr['userId'] = $uid;
        $activity = new ActivityWrkModel();
        $data = $activity->payWrkSuccessDate($arr);
        if($data)$data['payType'] = $arr['payType'];
        $this->apiSuccess($data);
    }

    //7.万人砍订单
    public function wrkOrderInfo()
    {
        $uid = $this->tokenCheck();
        $arr = input('post.');
        $validate = new \think\Validate([
                    'type'   => 'require|in:1,2,3',
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }
        $arr['userId'] = $uid;
        $arr['page']=input('post.page/d',1);
        $arr['pagesize']=input('post.pagesize/d',10);
        $activity = new ActivityWrkModel();
        $data = $activity->wrkOrderInfoData($arr);
        $this->apiSuccess($data);
    }

    //8.万人砍订单详情
    public function wrkOrderOneInfo()
    {
        $uid = $this->tokenCheck();
        $arr = input('post.');
        $validate = new \think\Validate([
                    'type'   => 'require|in:1,2,3',
                    'orderWrkId' => 'require|number',
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }
        $arr['userId'] = $uid;
        $activity = new ActivityWrkModel();
        $data = $activity->wrkOrderOneInfoDate($arr);
        $this->apiSuccess($data);
    }

    //9 删除子订单
    public function delWrkOrder()
    {
        $uid = $this->tokenCheck();
        $arr['orderNo']=input('post.orderNo');
        if(!$arr['orderNo'])$this->apiError('paramError');
        $arr['userId'] = $uid;
        $activity = new ActivityWrkModel();
        $data = $activity->delWrkOrderDate($arr);
        $this->apiSuccess($data);
    }
    //10.取消订单
    public function cancelWrkOrder()
    {
        $uid = $this->tokenCheck();
        $arr = input('post.');
        $validate = new \think\Validate([
                    'orderWrkId' => 'require|number',
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }
        $arr['userId'] = $uid;
        $activity = new ActivityWrkModel();
        $data = $activity->cancelWrkOrderDate($arr);
        if(!$data){
            $this->apiError('failure');
        }else{
            $this->apiSuccess($data);
        }
    }
}