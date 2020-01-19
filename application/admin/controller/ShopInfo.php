<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Shop as ShopModel;
//use app\admin\model\ExistCity as ExistCityModel;

/**
 *  商户信息管理
 * @package app\admin\controller
 */
class ShopInfo extends AdminBase
{
	protected $shop_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->shop_model = new ShopModel(); 
    }
    
    /**
	 * 商户信息列表
	 * @package 
	 */

    public function index()
    {
       $this->getAcessTypes();
       $shopName = input('get.shopName');
       $type = input('get.type');
       $status = input('get.status');
	   $data = $this->shop_model-> getShopInfoShops($shopName,$type,$status);
       $page = $data->render();  // 获取分页显示
       $lists = $data->toArray(); 
	   return $this->fetch('index', ['lists' => $lists,'page' => $page,'shopName' => $shopName,'type' => $type,'status' => $status]);
    }


	/**
	 * 商户详情
	 * @package 
	 */
	public function info($id)
    { 
        $info=$this->shop_model->getShopInfoDatas($id);   
        return $this->fetch('info',['info'=>$info]);	 
    }

    /**
     * 商户上架
     * @param $id
     * 
     */
    public function upShop($id)
    {
    	$res=$this->shop_model->shopInfoUpShop($id);
        if($res==true){
            operateLog('商户上架','shop',$id,$this->admin_id);
            $this->success('上架成功','admin/ShopInfo/index');
        }elseif($res==0){
            $this->error('该商户下没有上架的校区','admin/ShopInfo/index');
        }else{
            $this->error('上架失败','admin/ShopInfo/index');
        }
    }

    /**
     * 商户下架
     * @param $id
     */
    public function downShop($id)
    {    	
        $res=$this->shop_model->shopInfoDownShop($id);
        if($res){
            operateLog('商户下架','shop',$id,$this->admin_id);
            $this->success('下架成功','admin/ShopInfo/index');
        }else{
            $this->error('下架失败','admin/ShopInfo/index');
        }
    }

}
