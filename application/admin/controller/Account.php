<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Shop as ShopModel;
use app\admin\model\ExistCity as ExistCityModel;

/**
 *  商户账号管理
 * @package app\admin\controller
 */
class Account extends AdminBase
{
	protected $shop_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->shop_model = new ShopModel(); 
    }
    
    /**
	 * 商户账号列表
	 * @package 
	 */
    public function index()
    {
       $this->getAcessTypes();
       $shopsName = input('get.shopsName');
       $shopStatus = input('get.status');       
       $shopsName ? $this->assign('shopsName', $shopsName) : '';
	   $data = $this->shop_model-> getAccountShops($shopsName,$shopStatus); 
       $page = $data->render();  // 获取分页显示
       $lists = $data->toArray();
	   return $this->fetch('index', ['lists' => $lists,'page' => $page,'status' => $shopStatus]);
    }

	/**
	 * 商户账号添加
	 * @package app\admin\controller
	 */
	public function add()
    {
		 if ($this->request->isPost()) {//数据提交并处理  	       
            $post       = input('post.');
            $post['name']    = input('post.name');
            $res=$this->shop_model->searchAccountShopName($post['name']);
            if($res){
                $this->error('商户名已存在','admin/Account/index');
            }
            $post['createTime'] = time();      
            $post['adminId'] = $this->admin_id;	   
		   if ($this->shop_model->allowField(true)->save($post)) {
                $this->success('保存成功','admin/Account/index');
            } else {
                $this->error('保存失败','admin/Account/add');
            }		   
		 }else{
            //获取品牌
            $brands=$this->shop_model -> getAccountBrands(); 
            //获取城市
            $exist_city_model = new ExistCityModel();
            $citys=$exist_city_model -> getOpenCitys(1);
            return $this->fetch('add',['brands'=>$brands,'citys'=>$citys]);
		 }
		 
    }

    /**
     * 编辑商户账号
     * @param $id
     * 
     */
    public function edit($id)
    {
    	if ($this->request->isPost()) {
            $post = $this->request->post();	
            if ($this->shop_model->save($post, ['id'=>$id]) !== false) {
                operateLog('编辑商户账号','shop',$id,$this->admin_id);
                $this->success('更新成功','admin/Account/index');
            } else {
                $this->error('更新失败','admin/Account/index');
            }            
        }else{        	
	        $info = $this->shop_model->find($id)->toArray();
	        return $this->fetch('edit', ['info' => $info]);
        }
    }

    /**
     * 删除商户账号
     * @param $id
     */
    public function del($id)
    {    	
        $res = $this->shop_model->delAccountShop($id);
        if ($res) {
            operateLog('删除商户账号','shop',$id,$this->admin_id);
            $this->success('删除成功','admin/Account/index');
        } else {
            $this->error('删除失败','admin/Account/index');
        }
    }

    //商户账单列表  
    public function bill($id)
    {
       // 获取商户总收入
       $totalMoney = $this->shop_model->getShopTotalMoney($id);
       $this->assign('totalMoney', $totalMoney);

       $datas = $this->shop_model-> getShopBills($id); 
       $page = $datas->render();  
       $lists = $datas->toArray();
       return $this->fetch('bill', ['lists' => $lists,'page' => $page]);
    }

    //商户账单详情  
    public function billDetail($id)
    {
       $datas = $this->shop_model->getBillDetail($id); 
       $page = $datas->render();  
       $lists = $datas->toArray();
       return $this->fetch('bill_detail', ['lists' => $lists,'page' => $page]);
    }

}
