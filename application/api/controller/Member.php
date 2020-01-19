<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-09-18
 */

//  会员

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\Member as memberModel;

class Member extends BaseApi
{
    protected $tokenFlag = 0;

    //1.会员信息
    public function memberInfo()
    {
        $member = new memberModel();
        $data = $member->memberInfoData();
        $this->apiSuccess($data);
    }

    //2.特权信息
    public function memberPreInfo()
    {
        $member = new memberModel();
        $data = $member->memberPreInfoData();
        $this->apiSuccess($data);

    }

    //3.确认订单
    public function memberOrder()
    {
        $arr = input('post.');
        $arr['userId'] = $this->tokenCheck();
        $validate = new \think\Validate([
                    'level'  => 'require|in:1,2,3,4',
                    'isCmbc'   => 'require|in:0,1',
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }
        $member = new memberModel();
        $data = $member->memberOrderData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //4.购买vip
    public function buyMember()
    {
        $arr = input('post.');
        $arr['userId'] = $this->tokenCheck();
        $validate = new \think\Validate([
                    'orderMemberId'  => 'require|number',
                    'payType'   => 'require|in:1,2,3,4'
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }
        if($arr['payType']==1){
            $arr['password']=input('post.password');
            $this->payPassword($arr['password']);
        }
        $member = new memberModel();
        $data = $member->buyMemberData($arr);
        $this->apiSuccess($data);
    }

    //5.购买成功之后数据返回
    public function buyMemberSuccess()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['orderMemberId']=input('post.orderMemberId/d');
        if(!$arr['orderMemberId'])$this->apiError('paramError');
        $member = new memberModel();
        $data = $member->buyMemberSuccessData($arr);
        $this->apiSuccess($data);
    }


    public function txt()
    {
        $uid = $this->tokenCheck();

        $a = new \app\common\model\UserMember();
        $data = $a->userMemberInfo($uid);
        $this->apiSuccess($data);
    }

}