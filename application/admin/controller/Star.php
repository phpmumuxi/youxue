<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\controller\Upload;
use app\admin\model\Star as StarModel;

/**
 *  星星灯管理
 * @package app\Star\controller
 */
class Star extends AdminBase
{
    // 星星灯列表
    public function index()
    {

       $name = input('get.name');
       $name ? $this->assign('name', $name) : '';
       $starModel = new StarModel();
	     $data = $starModel-> getStarLists($name);
       $page = $data->render();
       $lists = $data->toArray();
	   return $this->fetch('index', ['lists' => $lists,'page' => $page]); 
    }


	// 星星灯添加
	public function add()
    {
		 if ($this->request->isPost()) {
            $post = input('post.');

            $upload_model = new Upload();
            if($_FILES['img']['name']){
                $rr = $upload_model->saveIamge('img');
                if(is_array($rr)){
                  $this->error($rr['msg']);
                }else{
                  $post['img']=$rr;                
                }
            }

            $post['createTime'] = time(); 
            $post['isDelete'] = 0;
            
		   if (db('star_rule')->insert($post)) {
                $this->success('添加成功！','/admin/Star/index');
            } else { 
                $this->error('添加失败！','/admin/Star/index');
            } 
		 }else{
            return $this->fetch();
		 }
		 
    }

    // 星星灯修改
    public function edit($id)
    {
         if ($this->request->isPost()) {          
            $post = input('post.');

            $upload_model = new Upload();
            if($_FILES['img']['name']){
                $rr = $upload_model->saveIamge('img');
                if(is_array($rr)){
                  $this->error($rr['msg']);
                }else{
                  $post['img']=$rr;                
                }
            } 

            if (db('star_rule')->where('id',$id)->update($post) !== false) {
              operateLog('编辑星星灯','star_rule',$id,$this->admin_id);
                $this->success('更新成功！','/admin/Star/index');
            } else {
                $this->error('更新失败！','/admin/Star/index');
            } 
           
         }else{           
            $info=db('star_rule')->where(['id'=>$id])->find();
            return $this->fetch('edit', ['info' => $info]);
         }
         
    }

    // 删除星星灯
    public function del()
    {   	
        $id=input('post.id');
        if (db('star_rule')->where('id',$id)->update(['isDelete'=>1])) {
          operateLog('删除星星灯','star_rule',$id,$this->admin_id);
            return  ['status'=>'ok','msg'=>'删除成功！'];
        } else {
            return  ['status'=>'err','msg'=>'删除失败！'];
        }
    }

    //免费券订单
    public function firstFree()
    {
        
       $status = input('get.status','-1');
       $schoolId = input('get.schoolId','0');
       $phone = input('get.phone','');        
       $orderStatus = [0 => '未使用',1 => '已使用']; 
       $starModel = new StarModel();
       $schools = $starModel-> getSchools();
       $data = $starModel-> getfirstFreeOrders($status,$phone,$schoolId);
       $page = $data->render();
       $lists = $data->toArray();
       return $this->fetch('firstFree', ['orderStatus' => $orderStatus,'schools' => $schools,'schoolId' => $schoolId,'status' => $status,'phone' => $phone,'lists' => $lists,'page' => $page]);
    }

    //星星兑换课程订单
    public function starCourse()
    {
       $status = input('get.status','-1');
       $schoolId = input('get.schoolId','0');
       $phone = input('get.phone','');        
       $orderStatus = [0 => '未上课',1 => '已上课']; 
       $starModel = new StarModel();
       $schools = $starModel-> getSchools();
       $data = $starModel-> getStarCourseOrders($status,$phone,$schoolId);
       $page = $data->render();
       $lists = $data->toArray();
       return $this->fetch('starCourse', ['orderStatus' => $orderStatus,'schools' => $schools,'schoolId' => $schoolId,'status' => $status,'phone' => $phone,'lists' => $lists,'page' => $page]);
    }

    //灯券兑换课程订单
     public function starVoucher()
    {
       $status = input('get.status','-1');
       $schoolId = input('get.schoolId','0');
       $phone = input('get.phone','');        
       $orderStatus = [0 => '未使用',1 => '已使用']; 
       $starModel = new StarModel();
       $schools = $starModel-> getSchools();
       $data = $starModel-> getStarVoucherOrders($status,$phone,$schoolId);
       $page = $data->render();
       $lists = $data->toArray();
       return $this->fetch('starVoucher', ['orderStatus' => $orderStatus,'schools' => $schools,'schoolId' => $schoolId,'status' => $status,'phone' => $phone,'lists' => $lists,'page' => $page]);
    }

    //用户活动管理
     public function activity()
    {
        $phone = input('get.phone','');
        $starModel = new StarModel();
        $data = $starModel->getUserActivity($phone);
        $page = $data->render();
        $lists = $data->toArray();
        return $this->fetch('activity', ['phone' => $phone,'lists' => $lists,'page' => $page]);
    }

    //上轮活动
     public function lastActivity()
    {
        $userId=input('post.userId');
        $round=input('post.round/d')-1;
        $starModel = new StarModel();
        $data = $starModel->getUserLastActivity($userId,$round);
        if($data){
            return  ['status'=>'ok','data'=>$data];
        }
        return  ['status'=>'err','data'=>'错误！'];
    }

    //用户活动详情
     public function info($userId)
    {
        $starModel = new StarModel();
        $data = $starModel->getUserActivityInfo($userId);
        $page = $data->render();
        $lists = $data->toArray();
        return $this->fetch('info', ['lists' => $lists,'page' => $page]);
    }

}
