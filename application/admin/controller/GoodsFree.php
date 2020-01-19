<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\GoodsFree as GoodsFreeModel;
use app\common\controller\Upload;

/**
 *  福利专区管理
 * @package app\admin\controller
 */
class GoodsFree extends AdminBase
{
	protected $goods_free_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->goods_free_model = new GoodsFreeModel(); 
    }
    
    /**
	 * 免费商品列表
	 * @package app\admin\controller
	 */

    public function index()
    {
      $this->getAcessTypes();
      $goodsName = input('get.goodsName');
      $status = input('get.status');
      $type = input('get.type');
      $goodsName ? $this->assign('goodsName', $goodsName) : '';
      $data=$this->goods_free_model->getGoodsFreeDatas($goodsName,$status,$type);
      $page = $data->render();
      $info = $data->toArray();
      return $this->fetch('index', ['info' => $info,'page' => $page,'status' => $status,'type' => $type]);        
    }


	/**
	 * 免费商品添加
	 * @package app\admin\controller
	 */
	public function add()
    {
		 if ($this->request->isPost()) {  	       
            $post   = input('post.');
            $post['descr']=input('post.descr','','htmlspecialchars');
            if(!empty(input('post.schoolIds/a'))){
                $post['schoolIds']=implode(",",$post['schoolIds']); 
            } 
            $upload_model = new Upload();
            if($_FILES['topImg']['name']){
                $rr = $upload_model->saveIamge('topImg');
                if(is_array($rr)){
                  $this->error($rr['msg']);
                }else{
                  $post['topImg']=$rr;                
                }
            }
            if($_FILES['listImg']['name']){
                $r = $upload_model->saveIamge('listImg');
                if(is_array($r)){
                  $this->error($r['msg']);
                }else{
                  $post['listImg']=$r;                  
                }
            }
            
            $post['createTime'] = time();
		   if ($this->goods_free_model->allowField(true)->save($post)) {
                $this->success('保存成功','admin/GoodsFree/index');
            } else {
                $this->error('保存失败','admin/GoodsFree/index');
            }		   
		 }else{
            $shops= $this->goods_free_model->getGoodsFreeShops();
            $schools= $this->goods_free_model->getGoodsFreeSchools(); 
            return $this->fetch('add',['shops'=>$shops,'schools'=>$schools]);
		 }
		 
    }

    /**
     * 编辑免费商品
     * @param $id
     * 
     */
    public function edit($id)
    {
      if ($this->request->isPost()) {
            $post = $this->request->post();            
            $post['descr']=input('post.descr','','htmlspecialchars');
            if(!empty(input('post.schoolIds/a'))){
                $post['schoolIds']=implode(",",$post['schoolIds']); 
            }
            $upload_model = new Upload();

            if($_FILES['topImg']['name']){
                $rr = $upload_model->saveIamge('topImg');
                if(is_array($rr)){
                  $this->error($rr['msg']);
                }else{
                  $post['topImg']=$rr;                
                }
            }
            if($_FILES['listImg']['name']){                
                $r = $upload_model->saveIamge('listImg');
                if(is_array($r)){
                  $this->error($r['msg']);
                }else{
                  $post['listImg']=$r;                  
                }
            }
            if ($this->goods_free_model->save($post, ['id'=>$id]) !== false) {
                operateLog('编辑免费商品','goods_free',$id,$this->admin_id);
                $this->success('更新成功','admin/GoodsFree/index');
            } else {
                $this->error('更新失败','admin/GoodsFree/index');
            }            
        }else{
	        $info = $this->goods_free_model->find($id)->toArray();
          if($info['status'] ==1 ){
              $this->error('商品不允许修改!','admin/GoodsFree/index');
          }
          $shops= $this->goods_free_model->getGoodsFreeShops();
          $schools= $this->goods_free_model->getGoodsFreeSchools();
	        return $this->fetch('edit', ['info' => $info,'shops' => $shops,'schools' => $schools]);
        }
    }

    /**
     * 免费商品上架
     * @param $id
     */
    public function upGoodsFree($id)
    {    	
         $arr['adminId'] = $this->admin_id;   
         $arr['upTime'] = time();
         $arr['status'] = 1;
        if ($this->goods_free_model->save($arr,['id'=>$id])) {
            operateLog('免费商品上架','goods_free',$id,$this->admin_id);
            $this->success('商品上架成功','admin/GoodsFree/index');
        } else {
            $this->error('商品上架失败','admin/GoodsFree/index');
        }
    }

    /**
     * 免费商品下架
     * @param $id
     */
    public function downGoodsFree($id)
    {     
         $arr['downId'] = $this->admin_id;   
         $arr['downTime'] = time();
         $arr['status'] = 2;
        if ($this->goods_free_model->save($arr,['id'=>$id])) {
            operateLog('免费商品下架','goods_free',$id,$this->admin_id);
            $this->success('商品下架成功','admin/GoodsFree/index');
        } else {
            $this->error('商品下架失败','admin/GoodsFree/index');
        }
    }

    /**
     * 免费商品详情
     * @param $id
     */
    public function info($id)
    {     
        $info = $this->goods_free_model->getGoodsFreeInfo($id);
        return $this->fetch('info', ['info' => $info]);
    }

}
