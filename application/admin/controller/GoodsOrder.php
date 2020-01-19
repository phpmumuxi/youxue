<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\GoodsFreeGet as GoodsFreeGetModel;

/**
 *  福利专区订单管理（后台）
 * @package app\admin\controller
 */
class GoodsOrder extends AdminBase
{
	  protected $goods_get_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->goods_get_model = new GoodsFreeGetModel(); 
    }
    
    /**
	 * 免费商品订单列表
	 * @package app\admin\controller
	 */

    public function index()
    {
      $phone=input('get.phone');
      $status=input('get.status');
      $infos= $this->goods_get_model->getGoodsFreeGetDatas($phone,$status);
      $page = $infos->render();
      $info = $infos->toArray();
      return $this->fetch('index', ['info' => $info,'page' => $page,'phone' => $phone,'status' => $status]);        
    }

    //免费领商品详情 
    public function info($id)
    {
      $info= $this->goods_get_model->getGoodsFreeGetInfos($id);
      return $this->fetch('info', ['info' => $info]);        
    }

}
