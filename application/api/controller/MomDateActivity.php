<?php
/**
 * Created by PhpStorm.
 * User: Xin
 * Date: 2017-08-30
 * Time: 13:33
 */

//  妈妈约活动

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\MomDateActivity as MomDateActivityModel;

class MomDateActivity extends BaseApi
{
    protected $tokenFlag = 0;

    //1.妈妈约活动列表
    public function momDateList()
    {
        $post = [
            'minAge' =>input('post.minAge',0),
            'maxAge' =>input('post.maxAge',0),
            'minTime' =>input('post.minTime',0),
            'maxTime' =>input('post.maxTime',0),
            'status' => input('post.status',0),
            'page'=>input('post.page/d',1),
            'pagesize'=>input('post.pagesize/d',20),
        ];
        if(input('post.token')){
            $post['userId'] = $this->tokenCheck();
        }
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->momDateListData($post);
        $this->apiSuccess($data);
    }

    //2.发起活动
    public function organizedMomDate()
    {
        $uid = $this->tokenCheck();
        $res = $this->userInfo();
        if(!($res['isAdviser']||$res['memberLevel']||$res['memberEndTime']>time()||$res['isFictitious'])){
            $this->apiError('userNoJuri');
        }
        $arr = [
                'name' => input('post.name'),
                'dateTime' => input('post.dateTime'),
                'minAge' => input('post.minAge/d',0),
                'maxAge' => input('post.maxAge/d',0),
                'peopleNum' => input('post.peopleNum/d',20),
                'contact' => input('post.contact'),
                'address' => input('post.address'),
                'introduct' => input('post.introduct'),
                'img' => input('post.img'),
                'userId' => $uid
        ];
        $validate = new \think\Validate([
                    'name'  => 'require',
                    'dateTime'   => 'require',
                    'minAge'   => 'number',
                    'maxAge'   => 'number',
                    'peopleNum'=>'number',
                    'contact' => 'require',
                    'address' =>'require'
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }

        if($arr['minAge']>$arr['maxAge']){
            $this->apiError('paramError');
        }
        if($arr['peopleNum']>20){$arr['peopleNum'] = 20;}
        // print_r($arr);exit;
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->organizedMomDateData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //3.活动详情
    public function momDateInfo()
    {
        $arr['momId'] = input('post.momId/d');
        if(!$arr['momId'])$this->apiError('paramError');
        $arr['userId'] = 0;
        if(input('post.token')){
            $arr['userId'] = $this->tokenCheck();
        }
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->momDateInfoData($arr);
        $this->apiSuccess($data);
    }

    //3-1 发起者获取用户报名信息
    public function sponsorMonDate()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['momId'] = input('post.momId/d');
        if(!$arr['momId'])$this->apiError('paramError');
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->sponsorMonDateData($arr);
        $this->apiSuccess($data);
    }

    //4.活动报名
    public function momDateApply()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['momId'] = input('post.momId/d');
        $arr['babyNum'] = input('post.babyNum/d');
        $arr['parentNum'] = input('post.parentNum/d');
        $arr['isPhone'] = input('post.isPhone/d');
        $arr['remark'] = input('post.remark');
        if(!$arr['momId'])$this->apiError('paramError');
        if(!($arr['babyNum']&&$arr['parentNum']))
            $this->apiError('paramError');
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->momDateApplyData($arr);
        // echo $data;exit;
        if($data){
            if(is_array($data)){
                $this->apiSuccess($data);
            }else{
                $this->apiError($data);
            }
        }else{
            $this->apiError('failure');
        }
    }

    //5.取消报名
    public function momDateCancelApply()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['momId'] = input('post.momId/d');
        if(!$arr['momId'])$this->apiError('paramError');
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->momDateCancelApplyData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }


    //6.活动审核
    public function momDateAudit()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['enrolId'] = input('post.enrolId/d');
        $arr['status'] = input('post.status/d');
        $arr['momId'] = input('post.momId/d');
        if(!$arr['enrolId']||!$arr['momId'])$this->apiError('paramError');
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->momDateAuditData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }

    }

    //7.取消活动
    public function cancelMomDate()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['momId'] = input('post.momId/d');
        if(!$arr['momId'])$this->apiError('paramError');
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->cancelMomDateData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //8.分享活动
    public function shareMomDate()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['momId'] = input('post.momId/d');
        if(!$arr['momId'])$this->apiError('paramError');
        $arr['shareId'] = input('post.shareId/d');
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->shareMomDateTest($arr);
        if($data)$this->apiError($data);
        $arr['text'] = input('post.text');
        if(!$arr['text'])$this->apiError('paramError');
        $arr['momImg'] = input('post.momImg/a');
        if(count($arr['momImg'])>3)$this->apiError('paramError');
        $array = [];
        if($arr['momImg']){
            foreach ($arr['momImg'] as $k => $v) {
                $n = explode('/', $v);
                $nEnd = end($n);
                $name = $n[0].'/'.'small'.$nEnd;
                $path='/home/data/' . DS . 'images/';
                $image = \think\Image::open($path.$v);
                $image->thumb(160, 160)->save($path.$name);
                $array[]=[
                    'userId' => $arr['userId'],
                    'momId' =>  $arr['momId'],
                    'img' =>  $v,
                    'smallImg' => $name,
                    'isDelete' => 0,
                    'createTime' => time()
                ];
            }
        }
        unset($arr['momImg']);
        if($arr['shareId']){
            $data = $MomDateActivity->shareMomDateUpdate($arr,$array);
        }else{
            unset($arr['shareId']);
            $data = $MomDateActivity->shareMomDateData($arr,$array);
        }
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }
    //8-1.编辑分享活动
    public function compileMomDate()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['momId'] = input('post.momId/d');
        if(!$arr['momId'])$this->apiError('paramError');
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->compileMomDateData($arr);
        $this->apiSuccess($data);
    }

    //9.删除分享
    public function delShareMom()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['shareId'] = input('post.shareId/d');
        if(!$arr['shareId'])$this->apiError('paramError');
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->delShareMomData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //10.删除分享图片
    public function delShareMomImg()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['imgId'] = input('post.imgId/d');
        if(!$arr['imgId'])$this->apiError('paramError');
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->delShareMomImgData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //11.我发起的活动
    public function myMomDate()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['page'] = input('post.page/d',1);
        $arr['pagesize'] = input('post.pagesize/d',20);
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->myMomDateData($arr);
        $this->apiSuccess($data);
    }

    //12.我参与的活动
    public function myJoinMomDate()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['page'] = input('post.page/d',1);
        $arr['pagesize'] = input('post.pagesize/d',20);
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->myJoinMomDateData($arr);
        $this->apiSuccess($data);
    }

    //13.活动结束接口
    public function momDateEnd()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['momId'] = input('post.momId/d');
        if(!$arr['momId'])$this->apiError('paramError');
        $MomDateActivity = new MomDateActivityModel();
        $data = $MomDateActivity->momDateEndData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }
}
