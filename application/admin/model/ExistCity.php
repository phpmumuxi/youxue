<?php
namespace app\admin\model;

use think\Model;
use think\Db;

class ExistCity extends Model
{
  //判断开通城市是否存在
  public function getSearchOpenCity($post){
      $res=Db::name('exist_city')->field('id')->where($post)->find();
      return  $res ;
  }

  //停止开通城市判断
  public function downOpenCity($id){
      $cityCode=Db::name('exist_city')->where('id',$id)->value('cityCode');
      $data['cityCode']=$cityCode;
      $data['status']=1;
      $res = Db::name('shop')->field('id')->where($data)->find();
      return  $res;
  }

  //获取开通的城市
  public function getOpenCitys($status){
      $res = db('exist_city')
            ->where('status',$status)
            ->select();
      return  $res;
  }

}