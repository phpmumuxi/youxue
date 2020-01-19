<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-07-24
 */

//  团购

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\GroupBuying as GroupBuyingModel;

class GroupBuying extends BaseApi
{
    protected $tokenFlag = 0;

    //1.团购列表
    public function buyList()
    {
        $arr = [
            'page' => input('post.page/d', 1),
            'pagesize' => input('post.pagesize/d', 20),
        ];
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->buyListDate($arr);
        $this->apiSuccess($data);
    }

    //2.团购对应可选品牌
    public function buyBrand()
    {
        $arr = [
            'buyId' => input('post.buyId/d'),
            'cityCode' => input('post.cityCode', 320100)
        ];
        if (input('?post.token')) {
            $arr['userId'] = $this->tokenCheck();
        }
        if (!$arr['buyId']) $this->apiError('paramError');
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->buyBrandDate($arr);
        $this->apiSuccess($data);
    }

    //3.下单
    public function userBuyOrder()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'buyId' => input('post.buyId/d'),
            'brandIds' => input('post.brandIds/a'),
            'schoolIds' => input('post.schoolIds/a'),
            'userId' => $uid
        ];
        if (!$arr['buyId'] || !$arr['brandIds'] || !$arr['schoolIds']) $this->apiError('paramError');
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->userBuyOrderDate($arr);
        $data = $this->buyDataError($data);
        $this->apiSuccess($data);
    }

    //3-1免费券确认领取
    public function userBuyGet()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'buyId' => input('post.buyId/d'),
            'brandIds' => input('post.brandIds/a'),
            'schoolIds' => input('post.schoolIds/a'),
            'userId' => $uid
        ];
        if (!$arr['buyId'] || !$arr['brandIds'] || !$arr['schoolIds']) $this->apiError('paramError');
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->userBuyGetDate($arr);
        $data = $this->buyDataError($data);
        $this->apiSuccess($data);
    }

    //4.确认订单
    public function getBuyOrder()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'buyId' => input('post.buyId/d'),
            'brandIds' => input('post.brandIds/a'),
            'schoolIds' => input('post.schoolIds/a'),
            'userId' => $uid
        ];
        if (!$arr['buyId'] || !$arr['brandIds'] || !$arr['schoolIds']) $this->apiError('paramError');
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->getBuyOrderDate($arr);
        $data = $this->buyDataError($data);
        if ($data) {
            $user = $this->userInfo();
            $data['balance'] = $user['balance'];
            $this->apiSuccess($data);
        } else {
            $this->apiError('failure');
        }
    }

    //5.取消订单
    public function cancelBuyOrder()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'publicOrderNo' => input('post.publicOrderNo/s'),
            'userId' => $uid
        ];
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->cancelBuyOrderDate($arr);
        $data = $this->buyDataError($data);
        $this->apiSuccess($data);
    }

    //6.支付
    public function payBuy()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'publicOrderNo' => input('post.publicOrderNo'),
            'payType' => input('post.payType/d'),
            'userId' => $uid,
            'password' => input('post.password'),
        ];
        if ($arr['payType'] == 1) {
            $this->payPassword($arr['password']);
        }
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->payBuyDate($arr);
        $data = $this->buyDataError($data);
        if ($data) {
            $this->apiSuccess($data);
        } else {
            $this->apiError('failure');
        }
    }

    //7.我的团购
    public function myBuy()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'page' => input('post.page/d', 1),
            'pagesize' => input('post.pagesize/d', 20),
            'userId' => $uid,
            'lng' => input('post.lng', 0),
            'lat' => input('post.lat', 0),
            'cityCode' => input('post.cityCode', 320100),
        ];
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->myBuyDate($arr);
        $this->apiSuccess($data);
    }

    //8.选择校区
    public function buyBrandSchool()
    {
        $this->tokenCheck();
        $arr = [
            'lng' => input('post.lng', 0),
            'lat' => input('post.lat', 0),
            'cityCode' => input('post.cityCode', 320100),
            'brandId' => input('post.brandId/d'),
        ];
        if (!$arr['brandId']) $this->apiError('paramError');
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->buyBrandSchoolDate($arr);
        $this->apiSuccess($data);
    }

    //9.确认校区
    public function getBuySchool()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'orderBuyId' => input('post.orderBuyId/d'),
            'schoolId' => input('post.schoolId/d'),
            'userId' => $uid
        ];
        if (!$arr['orderBuyId'] || !$arr['schoolId']) $this->apiError('paramError');
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->getBuySchoolDate($arr);
        if (!$data) {
            $this->apiError('failure');
        } else {
            $this->apiSuccess($data);
        }
    }

    //10.立即使用页面
    public function useBuyPage()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'orderBuyId' => input('post.orderBuyId/d'),
            'userId' => $uid
        ];
        if (!$arr['orderBuyId']) $this->apiError('paramError');
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->useBuyPageDate($arr);
        if (!$data) {
            $this->apiError('failure');
        } else {
            $this->apiSuccess($data);
        }
    }

    //11.立即使用
    public function useBuy()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'orderBuyId' => input('post.orderBuyId/d'),
            'userId' => $uid
        ];
        if (!$arr['orderBuyId']) $this->apiError('paramError');
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->useBuyDate($arr);
        if (!$data) $this->apiError('failure');
        if ($data === 6) $this->apiError('buyStatusUse');
        $this->apiSuccess($data);
    }

    //12.支付成功后数据
    public function successBuy()
    {
        $uid = $this->tokenCheck();
        $arr = [
            'publicOrderNo' => input('post.publicOrderNo'),
            'userId' => $uid,
        ];
        if (!$arr['publicOrderNo']) $this->apiError('paramError');

        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->successDataBuy($arr);
        if (!$data) {
            $this->apiError('failure');
        } else {
            $this->apiSuccess($data);
        }
    }

    private function buyDataError($data)
    {
        if ($data === true) $data = 1;
        if ($data === false) $data = 6;
        switch ($data) {
            case 3:
                $this->apiError('buyOrderMiss');
                break;
            case 2:
                $this->apiError('buyStatus');
                break;
            case 4:
                $this->apiError('buyBrand');
                break;
            case 5:
                $this->apiError('typeError');
                break;
            case 6:
                $this->apiError('failure');
                break;
            default:
                return $data;
                break;
        }
    }

    //13.团购各个券介绍
    public function buyHtml()
    {
        $id = input('post.id');
        $groupBuying = new GroupBuyingModel();
        $data = $groupBuying->buyHtmlDate($id);
        $this->apiSuccess($data);
    }
}

