<?php
namespace app\shop\controller;

use app\common\controller\AdminBase;
use app\common\model\ShopCommon as ShopCommonModel;

/**
 *  商户简介管理
 * Class Admin
 * @package app\admin\controller
 */
class Manager extends AdminBase
{
	  //商户简介   
    public function introduce()
    {
       $shopId=$this->getAdminShopId();
       if ($this->request->isPost()) {           
            $content = input('post.content','','htmlspecialchars');               
            if (db('shop')->update(['content' => $content,'id'=>$shopId])!==false) {
                $this->success('保存成功','shop/Manager/introduce');
            } else {
                $this->error('保存失败','shop/Manager/introduce');
            }      
       }else{
            $content =db('shop')->where('id',$shopId)->value('content');
            return $this->fetch('introduce',['content'=>$content]);
       }
    }    

     // 获取商户Id     
    public function getAdminShopId()
    {
        $shop_common_model=new ShopCommonModel();
        $shopId=$shop_common_model->getShopIdFromAdminId($this->admin_id);
        if (!$shopId) {
            $this->error('该管理员没有指定商户!');
        }
        return $shopId;
    }
}
