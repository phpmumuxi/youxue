<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\VipOrder as VipOrderModel;

/**
 *  女王权杖礼品订单管理
 * @package app\admin\controller
 */
class VipOrder extends AdminBase
{

    /**
	 * 礼品订单列表
	 * @package app\admin\controller
	 */

    public function index()
    { 
      $phone = input('get.phone');
      $status = input('get.status');
      $phone ? $this->assign('phone', $phone) : '';
      $vip_oeder_model = new VipOrderModel();
      $data= $vip_oeder_model->getVipOrderDatas($phone,$status);
      $page = $data->render();
      $info = $data->toArray();
      return $this->fetch('index', ['info' => $info,'page' => $page,'status' => $status]);        
    }
    
}
