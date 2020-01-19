<?php
namespace app\admin\model;

use \think\Model;

class VipOrder extends Model
{

	/**
     * 获取礼品订单数据
     * 
     */
  
    public function getVipOrderDatas($phone,$status)
    {
      $where1 = $phone ? ['u.phone' => $phone] : '';
      if($status==1){
        $where2 =  'vu.status = 1';
      }elseif($status==2){
        $where2 = 'vu.status = 0';
      }else{
        $where2 = '';
      }
      $lists  =  db('vip_use')
              -> alias('vu')
              -> field('vu.id,vu.name as vipFreeName,vu.status,vu.validity,vu.createTime,vu.useTime,u.name as userName,u.phone')
              ->where($where1)
              ->where($where2)
              -> join('t_user u','u.id=vu.userId','LEFT')
              -> paginate(10, false, ['query' => [
                'phone' => $phone,'status' => $status]]);
      return $lists;
    }
}