<?php
namespace app\school\model;

use \think\Model;
use \think\Db;

class GoodsFreeGet extends Model
{
	/**
     * 获取免费领商品列表数据
     * return []
     */
	public function getGoodsFreeGetDatas($schoolId,$phone,$status){

        $where1 = $phone ? ['u.phone' => $phone] : '';
          if($status==1){
            $where2 =  'g.status = 1';
          }elseif($status==2){
            $where2 = 'g.status = 0';
          }else{
            $where2 = '';
          }		 
	     $res   =  Db::name('goods_free_get')
                 -> alias('g')
                 -> field('g.id,g.useTime,g.status,u.name as uName,u.phone as uPhone,f.name as goodsName,s.specif as goodsSpecif')
                 -> where('g.schoolId',$schoolId)
                 -> where($where2)
                 -> join('t_user u','u.id = g.uid','LEFT')
                 -> where($where1)
                 -> join('t_goods_free f','f.id = g.goodsId','LEFT')
                 -> join('t_goods_free_stock s','s.id = g.ruleId','LEFT')
                 -> paginate(10, false, ['query' => [
                'phone' => $phone,'status' => $status]]);
          return $res;
	}
}