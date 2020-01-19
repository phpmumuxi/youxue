<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Activity as ActivityModel;
use app\common\controller\Upload;
/**
 *  万人砍管理
 * Class Activity
 * @package app\admin\controller
 */
class Activity extends AdminBase
{

    //万人砍   活动课程列表管理 
    public function classIndex()
    {
      $className = input('get.className','');      
      $className ? $this->assign('className', $className) : '';
      $schoolId = input('get.schoolId','0');
      $status = input('get.status','-1');
      $classStatus = [0 => '未审核',1 => '审核中',2 => '审核通过',3 => '拒绝'];
      $activityModel= new ActivityModel();
      $data = $activityModel->activityClassLists($className,$schoolId,$status);
      $page = $data->render();
      $lists = $data->toArray();
      $schools = $activityModel->getActivitySchools();  
      return $this->fetch('classIndex', ['lists' => $lists,'schools' => $schools,'schoolId' => $schoolId,'page' => $page,'status' => $status,'classStatus' => $classStatus]);
    }

	// 活动课程添加
	public function classAdd(){
     $activityModel= new ActivityModel();
  	 if ($this->request->isPost()) {   	     
          $post=input('post.');
          $post['content']=input('post.content','','htmlspecialchars');
          $post['adminId'] = $this->admin_id;
          $post['createTime']=time();
          $post['status']=0;
          $post['isStar']=0;
          $post['starNum']=0;
          
  	      if (db('activity_wrk_class')->insert($post)) {              
              $this->success('添加成功','admin/Activity/classIndex');
          } else {
              $this->error('保存失败','admin/Activity/classIndex');
          }		   
  	  }else{ 
         $brands  = $activityModel->getActivityBrands();             
         return $this->fetch('classAdd', ['brands' => $brands]); 
      }         
  }

    //活动课程编辑
    public function classEdit($id){
       if ($this->request->isPost()) {         
            $post=input('post.');
            $post['content']=input('post.content','','htmlspecialchars');
            $post['status']=0;
            if (db('activity_wrk_class')->where(['id'=>$id])->update($post) !== false) {   
            operateLog('修改万人砍活动课程', 'activity_wrk_class', $id, $this->admin_id);           
                $this->success('修改成功','admin/Activity/classIndex');
            } else {
                $this->error('修改失败','admin/Activity/classIndex');
            }      
        }else{
          $activityModel= new ActivityModel();
          $brands  = $activityModel->getActivityBrands();
          $shops = $activityModel->getActivityShops();
          $schools = $activityModel->getActivitySchools();
          $info=db('activity_wrk_class')->where(['id'=>$id])->find();
          return $this->fetch('classEdit', ['info' => $info,'brands' => $brands,'shops' => $shops,'schools' => $schools]);
        }
    }

    // 删除活动课程
    public function classDel($id)
    {
        if (db('activity_wrk_class')->where('id',$id)->update(['isDelete'=>1])) {
          operateLog('删除活动课程', 'activity_wrk_class', $id, $this->admin_id);
            return  ['status'=>'ok','msg'=>'删除成功！'];
        } else {
            return  ['status'=>'err','msg'=>'删除失败！'];
        }
    }

    // 判断课程是否参加万人砍活动以及状态
    public function isActivityAndStatus()
    {
      $id=input('post.id');
      $time=time();
      $where['classId']=$id;
      $where['startTime']=['exp','<='.$time];
      $re = db('activity_class')->where($where)->find();
      if($re){
        return  ['status'=>'has'];
      }else{        
        return  ['status'=>'no'];
      }
    }

    // Ajax数据
    public function getAjaxDatas()
    {
      $id=input('post.id/d');
      $type=input('post.type');
      $activityModel= new ActivityModel();
      if($type=='1'){// 获取商户数据        
        $lists  = $activityModel->getActivityShops($id);            
        $str = "<option value=''>--请选择商户--</option>";        
      }else{// 获取校区数据        
        $lists  = $activityModel->getActivitySchools($id);             
        $str = "<option value=''>--请选择校区--</option>";
      }
      foreach ($lists as $val) {
          $str .= "<option value='".$val['id']."'>".$val['name']."</option>";        
      }
      echo $str;
    }

	 //万人砍   活动列表管理 
    public function activityIndex()
    {
      $activityName = input('get.activityName','');      
      $status = input('get.status','-1');
      $activityName ? $this->assign('activityName', $activityName) : '';
      $activityStatus = [0 => '活动中',1 => '未开始',2 => '活动结束',3 => '活动下架'];
      $activityModel= new ActivityModel();
      $data = $activityModel->activityDataLists($activityName,$status);
      $page = $data->render();
      $lists = $data->toArray();  
      return $this->fetch('activityIndex', ['lists' => $lists,'activityStatus' => $activityStatus,'status' => $status,'page' => $page,'time' => time()]);
    }

    // 万人砍活动添加
    public function activityAdd(){
       $activityModel= new ActivityModel();
       if ($this->request->isPost()) {
            $post=input('post.');

            $classIds=input('post.classIds/a');
            $post['classIds']=implode(",",$classIds);           
            $infos  = $activityModel->getSelectedActivityClass($post['classIds']);
            if($infos){
                $post['price'] = $infos['sumPrice'];
                $post['money'] = $infos['sumMoney'];
            }

            $upload_model = new Upload();
            if($_FILES['topImg']['name']){
                $rr = $upload_model->saveIamge('topImg');
                if(is_array($rr)){
                  $this->error($rr['msg']);
                }else{
                  $post['topImg']=$rr;                
                }
            }
            if($_FILES['listImg']['name']){                
                $r = $upload_model->saveIamge('listImg');
                if(is_array($r)){
                  $this->error($r['msg']);
                }else{
                  $post['listImg']=$r;                  
                }
            }
            
            $post['createTime'] = time();
            $post['startTime'] = strtotime(input('post.startTime'));
            $post['endTime'] = strtotime(input('post.endTime'));

            $post['adminId'] = $this->admin_id;
            $post['status'] = 1;
            $post['stock'] = input('post.stock/d');
            $post['surplus'] = $post['stock'];
            $post['sellNum'] = 0;
            $post['content']=input('post.content','','htmlspecialchars');
            $res=$activityModel->addActivityAndClass($post,$classIds,$post['startTime']);
            if ($res) {                          
                $this->success('添加成功','admin/Activity/activityIndex');
            } else {
                $this->error('保存失败','admin/Activity/activityIndex');
            }      
        }else{             
           $brands  = $activityModel->getActivityBrands();             
           return $this->fetch('activityAdd', ['brands' => $brands]); 
        }         
    }

    //万人砍活动编辑
    public function activityEdit($id){
        $activityModel= new ActivityModel();
        $info=db('activity_wrk')->where(['id'=>$id])->find();
        $classinfos=db('activity_wrk_class')->field('id,name,price,money')-> where('id','in',$info['classIds'])->select();
       if ($this->request->isPost()) {
            $post=input('post.');

            $classIds=input('post.classIds/a');
            $post['classIds']=implode(",",$classIds);
            if($info['classIds'] != $post['classIds']){
                $infos  = $activityModel->getSelectedActivityClass($post['classIds']);
                if($infos){
                    $post['price'] = $infos['sumPrice'];
                    $post['money'] = $infos['sumMoney'];
                }
            }          

            $post['startTime'] = strtotime(input('post.startTime'));
            $post['endTime'] = strtotime(input('post.endTime'));

            $upload_model = new Upload();
            if($_FILES['topImg']['name']){
                $rr = $upload_model->saveIamge('topImg');                
                if(is_array($rr)){
                  $this->error($rr['msg']);
                }else{
                  $post['topImg']=$rr;                
                }
            }
            if($_FILES['listImg']['name']){                
                $r = $upload_model->saveIamge('listImg');
                if(is_array($r)){
                  $this->error($r['msg']);
                }else{
                  $post['listImg']=$r;                  
                }
            }
            
            $post['content']=input('post.content','','htmlspecialchars');

            $res=$activityModel->updateActivityAndClass($post,$classIds,$post['startTime'],$id,$this->admin_id);
            
            if ($res) {              
                $this->success('修改成功','admin/Activity/activityIndex');
            } else {
                $this->error('修改失败','admin/Activity/activityIndex');
            }      
        }else{          
          $brands  = $activityModel->getActivityBrands();
          return $this->fetch('activityEdit', ['info' => $info,'brands' => $brands,'classinfos' => $classinfos]);
        }
    }

    // 删除万人砍活动
    public function activityDel($id)
    {
        if (db('activity_wrk')->where('id',$id)->update(['status'=>3])) {
           operateLog('删除万人砍活动', 'activity_wrk', $id, $this->admin_id);
            return  ['status'=>'ok','msg'=>'删除下架成功！'];
        } else {
            return  ['status'=>'err','msg'=>'删除下架失败！'];
        }
    }

    // 修改万人砍活动剩余库存量
    public function activitySurplusEdit($id,$num)
    {     
        if (db('activity_wrk')->where('id',$id)->update(['surplus'=>$num])) {
            return  ['status'=>'ok','msg'=>'修改剩余库存量成功！'];
        } else {
            return  ['status'=>'err','msg'=>'修改剩余库存量失败！'];
        }
    }

    // Ajax活动课程数据
    public function getAjaxActivityClassDatas()
    {
      $brandId=input('post.brandId');
      $shopId=input('post.shopId');
      $schoolId=input('post.schoolId');

      $activityModel= new ActivityModel();
      $lists  = $activityModel->getActivityClassDatas($brandId,$shopId,$schoolId);
      
      if(!$lists){
        echo  '<font class="ml-30 c-red f-20">暂无对应的活动课程！</font>';die;
      }

      $str = "";
      foreach ($lists as $val) {        
        $str .= "<div><i></i>".$val['name']."&nbsp;&nbsp;(原价：".$val['price']."&nbsp;&nbsp;活动价：".$val['money'].")&nbsp;&nbsp;";
        $str .="<em><span onclick='add_tr_ele($(this).parent().parent(),".$val['id'].")' style='cursor:pointer;color:red;font-size:16px'>&nbsp;&nbsp;[ + ]</span></em></div>";
      }      
      echo $str;
    }

}
