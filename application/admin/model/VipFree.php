<?php
namespace app\admin\model;

use \think\Model;
use think\Db;

class VipFree extends Model
{

	/**
     * 获取礼品数据
     * 
     */
    public function getVipFreeDatas($freeName)
    {
      $where = $freeName ? ['name' => ['like', "%$freeName%"]] : '';
      $lists  =  Db::name('vip_free')
              -> where('isDelete',0)
              -> where($where)
              -> paginate(10, false, ['query' => [
                'freeName' => $freeName]]); 
      return $lists;
    }
    
}