<?php
namespace app\school\controller;

use app\common\controller\AdminBase;
use app\school\model\GoodsFreeGet as GoodsFreeGetModel;
use app\common\model\ShopCommon as ShopCommonModel;

/**
 *  福利专区管理（校区）
 * @package app\admin\controller
 */
class GoodsFree extends AdminBase
{
	protected $goods_get_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->goods_get_model = new GoodsFreeGetModel(); 
    }
    
    /**
	 * 免费商品列表
	 * @package app\admin\controller
	 */

    public function index()
    {
      $phone = input('get.phone');
      $status = input('get.status');
      $phone ? $this->assign('phone', $phone) : '';
      $school_model = new ShopCommonModel();
      $schoolId=$school_model->getSchoolIdFromAdminId($this->admin_id);

      $data= $this->goods_get_model->getGoodsFreeGetDatas($schoolId,$phone,$status);
      $page = $data->render();
      $info = $data->toArray();
      return $this->fetch('index', ['page' => $page,'info' => $info,'status' => $status]);        
    }


}
