<?php
namespace app\admin\model;

use \think\Model;
use think\Db;

class GoodsFree extends Model
{

    /**
     * 获取免费商品
     * 
     */
    public function getGoodsFreeDatas($goodsName,$status,$type)
    {
      $where = $goodsName ? ['name' => ['like', "%$goodsName%"]] : '';
      if($type==1){
        $where1 = 'type=1';
      }elseif($type==2){
        $where1 = 'type=2';
      }elseif($type==3){
        $where1 = 'type=3';
      }else{
        $where1 = '';
      }
      if($status==1){
        $where2 = 'status=1';
      }elseif($status==2){
        $where2 = 'status=2';
      }else{
        $where2 = '';
      }
      $lists  =  Db::name('goods_free')
              -> where($where)
              -> where($where1)
              -> where($where2)
              -> paginate(10, false, ['query' => [
                'goodsName' => $goodsName,'status' => $status,'type' => $type]]); 
      return $lists;
    }

  /**
     * 获取免费商品详情数据
     * 
     */
    public function getGoodsFreeInfo($id)
    {
      $lists  =  Db::name('goods_free')
              -> alias('g')
              -> field('g.*,s.name as shopName')
              -> where('g.id',$id)
              -> join('t_shop s','s.id = g.shopId','LEFT')
              -> find(); 
      $where['id']=['in',$lists['schoolIds']];
      $school =  Db::name('school')
              -> field('name')
              -> where($where)
              -> select();
      if(count($school)>1){
        $school_name='';
        foreach ($school as $v){  
            $school_name .= $v['name'].','; 
         } 
        $school_name =rtrim($school_name,',');
      }else{
         $school_name=$school['0']['name'];
      }
      $lists['schoolName']=$school_name;
      $lists['createTime']=date('Y-m-d H-m',$lists['createTime']);
      if($lists['type']==1){
          $lists['type']='全员免费';
      }elseif($lists['type']==2){
          $lists['type']='vip免费';
      }else{
          $lists['type']='豆豆福利';
      }
      return $lists;
    }

	/**
     * 获取商户数据
     * 
     */
    public function getGoodsFreeShops()
    {
      $lists  =  Db::name('shop')
              -> field('id,name')
              -> where('status',1)
              -> select(); 
      return $lists;
    }

    /**
     * 获取校区数据
     * 
     */
    public function getGoodsFreeSchools()
    {
      $lists  =  Db::name('school')
              -> field('id,shopId,name')
              -> where('status',1)
              -> select(); 
      return $lists;
    }
}