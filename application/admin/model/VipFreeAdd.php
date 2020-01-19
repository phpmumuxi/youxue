<?php
namespace app\admin\model;

use \think\Model;
use think\Db;

class VipFreeAdd extends Model
{

	 /**
     * 获取礼品地址列表数据
     * 
     */
    public function getVipFreeAddDatas($id)
    {
      $lists  =  Db::name('vip_free_add')
              -> alias('a')
              -> field('a.*,f.name as FreeName')
              -> where('VipFreeId',$id)
              -> join('t_vip_free f','f.id = a.vipFreeId','LEFT')
              -> select(); 
      return $lists;
    }
    
}