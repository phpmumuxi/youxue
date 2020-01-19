<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Brand as BrandModel;
use app\common\controller\Upload;

/**
 *  品牌管理
 * @package app\admin\controller
 */
class Brand extends AdminBase
{
	protected $brand_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->brand_model = new BrandModel(); 
    }
    
    /**
	 * 品牌列表
	 * @package app\admin\controller
	 */

    public function index()
    {
      $this->getAcessTypes();
      $brandsName = input('get.brandsName');
      $brandsName ? $this->assign('brandsName', $brandsName) : '';
	  $data = $this->brand_model-> getBrands($brandsName);
      // 获取分页显示
      $page = $data->render();
      $lists = $data->toArray();
	  return $this->fetch('index', ['lists' => $lists,'page' => $page]); 
    }


	/**
	 * 品牌添加
	 * @package app\admin\controller
	 */
	public function add()
    {
		 if ($this->request->isPost()) {//数据提交并处理  	       
            $post       = input('post.');
            $post['intr'] = input('post.intr','','htmlspecialchars');
            $upload_model = new Upload();
            if($_FILES['homeImg']['name']){
                $rr1 = $upload_model->saveIamge('homeImg');
                if(is_array($rr1)){
                  $this->error($rr1['msg']);
                }else{
                  $post['homeImg']=$rr1;                
                }
            }
            if($_FILES['bigImg']['name']){
                $rr2 = $upload_model->saveIamge('bigImg');
                if(is_array($rr2)){
                  $this->error($rr2['msg']);
                }else{
                  $post['bigImg']=$rr2;                
                }
            }
            if($_FILES['smallImg']['name']){
                $rr3 = $upload_model->saveIamge('smallImg');
                if(is_array($rr3)){
                  $this->error($rr3['msg']);
                }else{
                  $post['smallImg']=$rr3;                
                }
            }
         	
            $post['createTime'] = time();	   
		   if ($this->brand_model->allowField(true)->save($post)) {
                $this->success('保存成功','admin/Brand/index');
            } else {
                $this->error('保存失败');
            }		   
		 }else{
            return $this->fetch();
		 }
		 
    }

    /**
     * 编辑品牌
     * @param $id
     * 
     */
    public function edit($id)
    {
    	if ($this->request->isPost()) {
            $post = $this->request->post();
            $post['intr'] = input('post.intr','','htmlspecialchars'); 
            $upload_model = new Upload();
            if($_FILES['homeImg']['name']){
                $rr1 = $upload_model->saveIamge('homeImg');
                if(is_array($rr1)){
                  $this->error($rr1['msg']);
                }else{
                  $post['homeImg']=$rr1;                
                }
            }
            if($_FILES['bigImg']['name']){
                $rr2 = $upload_model->saveIamge('bigImg');
                if(is_array($rr2)){
                  $this->error($rr2['msg']);
                }else{
                  $post['bigImg']=$rr2;                
                }
            }
            if($_FILES['smallImg']['name']){
                $rr3 = $upload_model->saveIamge('smallImg');
                if(is_array($rr3)){
                  $this->error($rr3['msg']);
                }else{
                  $post['smallImg']=$rr3;                
                }
            }
            	
            if ($this->brand_model->save($post, ['id'=>$id]) !== false) {
                operateLog('编辑品牌','brand',$id,$this->admin_id);
                $this->success('更新成功','admin/Brand/index');
            } else {
                $this->error('更新失败');
            }            
        }else{        	
	        $info = $this->brand_model->find($id)->toArray();
	        return $this->fetch('edit', ['info' => $info]);
        }
    }

    /**
     * 删除品牌
     * @param $id
     */
    public function del($id)
    {   
        //判断该品牌下有没有上架的商户
        $res =  $this->brand_model->selBrandShop($id);
        if($res){
            $this->error('删除失败,该品牌下有上架的商户');
        }
        if ($this->brand_model->update(['isDelete'=>1,'id'=>$id])) {
            operateLog('删除品牌','brand',$id,$this->admin_id);
            $this->success('删除成功','admin/Brand/index');
        } else {
            $this->error('删除失败');
        }
    }

}
