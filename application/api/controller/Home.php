<?php
/**
 * Created by PhpStorm.
 * User: Liu
 * Date: 2017-07-05
 * Time: 13:27
 * revise 2017-07-18
 * User: xin
 */

//  首页

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\Home as HomeModel;
use app\common\libs\SendMsg as SendMsg;

class Home extends BaseApi
{

    protected $tokenFlag = 0;

    public function index()
    {
        return 'BaseApi';
    }

    //  1.地址列表
    public function address(){
        $home = new HomeModel();
        $address = $home->getAddress();
        $this->apiSuccess($address);
    }

    //2.轮播图
    public function banner()
    {
        $home = new HomeModel();
        $data = $home->getBanner();
        $this->apiSuccess($data);
    }

    //3.功能模块
    public function homeIco()
    {
        $home = new HomeModel();
        $data = $home->getHomeIco();
        $this->apiSuccess($data);
    }

     //4.优选品牌
    public function brand()
    {
        $cityCode = input('post.cityCode',320100);
        $home = new HomeModel();
        $data = $home->getbrand($cityCode);
        $this->apiSuccess($data);
    }

    //5.所有品牌
    public function brandAll()
    {
        $cityCode = input('post.cityCode',320100);
        $home = new HomeModel();
        $data = $home->brandAll($cityCode);
        $this->apiSuccess($data);
    }

    //6.短信验证
    public function sendSMS()
    {
        $home = new HomeModel();
        if(input('post.token')){
            $phone = $home->userData($this->tokenCheck());
        }else{
            $phone = input('post.phone');
            if(!preg_match("/^1[34578]{1}\d{9}$/",$phone)){
                $this->apiError('phone');
            }
        }
        // $home = new HomeModel();
        $data = $home->sendSMSData($phone);

        if(!($data===true)){
            $this->apiError($data);
        }
        $code = mt_rand(100000,999999);
        $sendMsg = new SendMsg();
        $re = $sendMsg->sendMsg($phone,$code);
        if($re){
            $this->apiError('msgApiError');
        }
        $arr = [
            'phone' => $phone,
            'createTime' => time(),
            'status' => 0,
            'code' => $code
        ];
        $data = $home->sendSMSJoinData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //7.模块H5页面
    public function introduction()
    {
        $type = input('post.type/d');
        $home = new HomeModel();
        $data = $home->introductionData($type);
        $this->apiSuccess($data);
    }

    //8.获取城市code
    public function district()
    {

        $post['lng']=input('post.lng',0);
        $post['lat']=input('post.lat',0);
        $a = new \app\common\libs\BaiDu();
        $arr=$a->getDistrict($post['lat'],$post['lng']);
        $home = new HomeModel();
        $arr['state'] = $home->districtData($arr);
        $this->apiSuccess($arr);
    }

    //获取当前安装包版本号
    public function appVersion()
    {
        $type=input('post.type/d',1);
        $home = new HomeModel();
        $data= $home->appVersionData($type);
        $this->apiSuccess($data);
    }
}