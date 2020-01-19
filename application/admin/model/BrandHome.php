<?php
namespace app\admin\model;

use \think\Model;
use \think\Db;

class BrandHome extends Model
{
	//获取所有品牌
  public function getPreferredBrandsDatas(){    
  		$lists  =  Db::name('brand')
                     -> field('id,name')
                     -> where(['isDelete' => 0])
                     -> select();
        return $lists;
  }

  //获取所有优选品牌数据
  public function getPreferredBrandAllDatas($brandsName){
    $where = $brandsName ? ['name' => ['like', "%$brandsName%"]] : '';
      $lists  =  Db::name('brand_home')->where($where)->order('sort DESC')->paginate(10, false, ['query' => ['brandsName' => $brandsName]]);
        return $lists;
  }

   //判断该品牌下有没有上架的商户
  public function hasPreferredBrandShop($brandId){
  		$res  =  Db::name('shop')
                     -> field('id')
                     -> where(['status' => 1,'brandId' => $brandId])
                     -> find();
        return $res;
  }

  //取出对应品牌数据
  public function getPreferredBrandOne($brandId){
  		$res  =  Db::name('brand')
                     -> field('name,homeImg,explain')
                     -> where(['isDelete' => 0])
                     -> find($brandId);
        $sums  = $this-> getPreferredBrandSums($brandId); 
        $post=[];        
        $post['name']  =  $res['name'];         
        $post['homeImg']  =  $res['homeImg'];         
        $post['explain']  =  $res['explain'];         
        $post['likeNum']  =  $sums;         
        return $post;
  }

  //查找该品牌是否存在
  public function hasPreferredBrandOne($brandId){
      $res  =  Db::name('brand_home')
               ->field('id')  
               -> where(['brandId' => $brandId])
               -> find();
      return $res;
  }

  //对应品牌关注的人数
  public function getPreferredBrandSums($brandId){
  		$sum  =  Db::name('brand_collect')  
	             -> where(['isDelete' => 0,'brandId' => $brandId])
	             -> count();
        return $sum;
  }

}