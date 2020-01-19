<?php
namespace app\admin\model;

use \think\Model;
use think\Db;

class GoodsFreeGet extends Model
{
  public function getGoodsFreeGetDatas($phone,$status){
      $where = $phone ? ['u.phone' => $phone] : '';
       if($status==1){
          $where1 = 'g.status=1';
        }elseif($status==2){
          $where1 = 'g.status=0';        
        }else{
          $where1 = '';
        }   
      $res   =  Db::name('goods_free_get')
                   -> alias('g')
                   -> field('g.*,u.name as uName,u.phone as uPhone,f.name as goodsName,f.price as goodsPrice,s.specif as goodsSpecif')
                   -> where($where)
                   -> where($where1)
                   -> join('t_user u','u.id = g.uid','LEFT')
                   -> join('t_goods_free f','f.id = g.goodsId','LEFT')
                   -> join('t_goods_free_stock s','s.id = g.ruleId','LEFT')
                    -> paginate(10, false, ['query' => [
                'phone' => $phone,'status' => $status]]);
      return $res;
  }

  public function getGoodsFreeGetInfos($id){    
      $res   =  Db::name('goods_free_get')
                   -> alias('g')
                   -> field('g.*,u.name as uName,u.phone as uPhone,u.address as uAddress,f.name as goodsName,f.price as goodsPrice,s.specif as goodsSpecif,s.img as specifImg,ts.name as schoolName')
                   -> where('g.id',$id)
                   -> join('t_user u','u.id = g.uid','LEFT')
                   -> join('t_goods_free f','f.id = g.goodsId','LEFT')
                   -> join('t_goods_free_stock s','s.id = g.ruleId','LEFT')
                   -> join('t_school ts','ts.id = g.schoolId','LEFT')
                   -> find();
      return $res;
  }
}