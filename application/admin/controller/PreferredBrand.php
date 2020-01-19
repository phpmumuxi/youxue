<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\BrandHome as BrandHomeModel;

/**
 *  优选品牌管理
 * @package app\admin\controller
 */
class PreferredBrand extends AdminBase
{
	protected $preferred_brand_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->preferred_brand_model = new BrandHomeModel(); 
    }
    
    /**
	 * 优选品牌列表
	 * @package app\admin\controller
	 */

    public function index()
    {
       $this->getAcessTypes();
       $brandsName = input('get.brandsName');
       $brandsName ? $this->assign('brandsName', $brandsName) : '';
	   $data = $this->preferred_brand_model-> getPreferredBrandAllDatas($brandsName);
       $page = $data->render();
       $lists = $data->toArray();
	   return $this->fetch('index', ['lists' => $lists,'page' => $page]); 
    }


	/**
	 * 优选品牌添加
	 * @package app\admin\controller
	 */
	public function add()
    {
		 if ($this->request->isPost()) {  	       
            $brandId  = input('post.brandId/d');
            $sort = input('post.sort'); 
            //判断优选品牌表有没有该品牌
            $s=$this->preferred_brand_model-> hasPreferredBrandOne($brandId);
            if($s){
                 return  ['status'=>'has','msg'=>'该品牌已存在！'];
            }
            //判断该品牌下有没有上架的商户
            $res=$this->preferred_brand_model-> hasPreferredBrandShop($brandId);
            if($res){
                //取出对应品牌数据
                $post = $this->preferred_brand_model->getPreferredBrandOne($brandId);
                $post['brandId'] = $brandId;
                $post['sort'] = $sort;
                $post['createTime'] = time(); 
    		   if ($this->preferred_brand_model->allowField(true)->save($post)) {  
                    return  ['status'=>'ok','msg'=>'添加成功！'];
                } else { 
                    return  ['status'=>'err','msg'=>'添加失败！'];
                } 
            }else{
                return  ['status'=>'no','msg'=>'该品牌下无上架的商户或无课程！'];
            }
		 }else{
            $preferredBrands=$this->preferred_brand_model->getPreferredBrandsDatas();
            return $this->fetch('add', ['preferredBrands' => $preferredBrands]);
		 }
		 
    }

    /**
     * 优选品牌修改
     * @package app\admin\controller
     */
    public function edit()
    {
         if ($this->request->isPost()) {          
            $id  = input('post.id');
            $sort = input('post.sort'); 
            if ($this->preferred_brand_model->save(['sort'=>$sort], ['id'=>$id]) !== false) {
                operateLog('编辑优选品牌','preferred_brand',$id,$this->admin_id);
                return  ['status'=>'ok','msg'=>'更新成功！'];
            } else {
                return  ['status'=>'err','msg'=>'更新失败！'];
            } 
           
         }else{
            $id = input('param.id');
            $info=$this->preferred_brand_model->find($id)->toArray();
            return $this->fetch('edit', ['info' => $info]);
         }
         
    }

    /**
     * 删除优选品牌
     * @param $id
     */
    public function del()
    {   	
        $id=input('post.id');
        if ($this->preferred_brand_model->destroy($id)) {
            operateLog('删除优选品牌','preferred_brand',$id,$this->admin_id);
            return  ['status'=>'ok','msg'=>'删除成功！'];
        } else {
            return  ['status'=>'err','msg'=>'删除失败！'];
        }
    }

}
