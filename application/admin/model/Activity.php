<?php
namespace app\admin\model;

use \think\Model;
use \think\Db;
class Activity extends Model
{

  //获取活动课程列表数据
  public function activityClassLists($className,$schoolId,$status){
    $className?($where['awc.name']=['like', "%$className%"]):'';
    $schoolId?($where['awc.schoolId']=$schoolId):'';
    if ($status != -1) {
        $where['awc.status'] = $status;
    } else {
        $where['awc.status'] = ['exp', '>=0'];
    }

    $where['awc.isDelete']=0;
  	$info =  db('activity_wrk_class awc')
           -> field('awc.id,awc.name,awc.money,awc.price,a.name as adminName,awc.isStar,awc.starNum,sc.name as schoolName,awc.status')
           ->where($where)             
           -> join('t_admin a','a.id = awc.adminId','LEFT')
           -> join('t_school sc','sc.id = awc.schoolId','LEFT')
           -> paginate(10, false, ['query' => [
              'className' => $className,'schoolId' => $schoolId,'status' => $status]]);
	
    return $info;
  }

  //获取品牌列表
  public function getActivityBrands(){
      $brands  =  db('brand')
              -> field('id,name')
              -> where('isDelete=0')
              -> select();
      return $brands;
  }

  //获取商户列表
  public function getActivityShops($brandId=''){
      $where=$brandId?(['brandId'=>$brandId]):'';
      $shops  =  db('shop')
              -> field('id,name')
              -> where($where)
              -> where('status!=3')
              -> select();
      return $shops;
  }

  //获取校区列表
  public function getActivitySchools($shopId=''){
      $where=$shopId?(['shopId'=>$shopId]):'';
      $schools  =  db('school')
                -> field('id,name')
                -> where($where)
                -> where('status!=3')
                -> select();
      return $schools;
  }

  //获取 万人砍活动 列表数据
  public function activityDataLists($activityName,$status){
    $activityName?($where['aw.name']=['like', "%$activityName%"]):'';
    if ($status != -1) {
        $where['aw.status'] = $status;
    } else {
        $where['aw.status'] = ['exp', '>=0'];
    }

    $info =  db('activity_wrk aw')
           -> field('aw.id,aw.name,aw.startTime,aw.endTime,a.name as adminName,aw.money,aw.price,aw.status,aw.surplus,aw.sellNum,aw.stock')
           ->where($where)
           -> join('t_admin a','a.id = aw.adminId','LEFT')
           -> paginate(10, false, ['query' => [
              'activityName' => $activityName,'status' => $status]]);
  
    return $info;
  }

  //获取对应所属的活动课程数据
  public function getActivityClassDatas($brandId,$shopId,$schoolId){
        $lists  =  db('activity_wrk_class')
                -> field('id,name,price,money')
                -> where(['isDelete'=>0,'status'=>2,'brandId'=>$brandId,'shopId'=>$shopId,'schoolId'=>$schoolId])
                -> select();
        return $lists;
  }

  //获取万人砍活动  被选中的课程数据
  public function getSelectedActivityClass($ids){
      $lists  =  db('activity_wrk_class')
              -> field('id,price,money')
              -> where('id','in',$ids)
              -> select();

      $arr['sumPrice']=0;
      $arr['sumMoney']=0;
      foreach ($lists as $key => $va) {
        $arr['sumPrice'] += $va['price'];
        $arr['sumMoney'] += $va['money'];
      }

      return $arr;
  }

  //添加万人砍活动和课程中间表
  public function addActivityAndClass($post,$classIds,$startTime){
      Db::startTrans();
      try {
        db('activity_wrk')->insert($post);
        $id = db('activity_wrk')->getLastInsID();
        $data=[];
        foreach ($classIds as $k => $v) {
              $data[$k]['wrkId']=$id;
              $data[$k]['classId']=$v;
              $data[$k]['startTime']=$startTime;
        }
        db('activity_class')->insertAll($data);
        Db::commit();            
      } catch (\Exception $e) {
          Db::rollback();
          return false;
      }
      return true;
  }

  //修改万人砍活动和课程中间表
  public function updateActivityAndClass($post,$classIds,$startTime,$id,$adminId){
      Db::startTrans();
      try {
        $re=db('activity_wrk')->where(['id'=>$id])->update($post);

        if($re!==0){          
            $data=[];
            foreach ($classIds as $k => $v) {
                  $data[$k]['wrkId']=$id;
                  $data[$k]['classId']=$v;
                  $data[$k]['startTime']=$startTime;
            }
            db('activity_class')->where('wrkId',$id)->delete();
            db('activity_class')->insertAll($data);
            operateLog('修改万人砍活动', 'activity_wrk', $id, $adminId);
        }
        Db::commit();            
      } catch (\Exception $e) {
          Db::rollback();
          return false;
      }
      return true;
  }

}