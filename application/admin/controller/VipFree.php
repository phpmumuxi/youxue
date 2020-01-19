<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\VipFree as VipFreeModel;
use app\common\controller\Upload;

/**
 *  女王权杖管理
 * @package app\admin\controller
 */
class VipFree extends AdminBase
{
	protected $vip_free_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->vip_free_model = new VipFreeModel(); 
    }
    
    /**
	 * 礼品列表
	 * @package app\admin\controller
	 */

    public function index()
    {
      $this->getAcessTypes();
      $freeName = input('get.freeName');
      $freeName ? $this->assign('freeName', $freeName) : '';
      $data= $this->vip_free_model->getVipFreeDatas($freeName);
      $page = $data->render();
      $info = $data->toArray();
      return $this->fetch('index', ['info' => $info,'page' => $page]);        
    }


	/**
	 * 礼品商品添加
	 * @package app\admin\controller
	 */
	public function add()
  {
  		 if ($this->request->isPost()) {//数据提交并处理  	       
            $post   = input('post.');
            $post['content']=input('post.content','','htmlspecialchars');
            $upload_model = new Upload();
            if($_FILES['listImg']['name']){
                $rr = $upload_model->saveIamge('listImg');
                if(is_array($rr)){
                  $this->error($rr['msg']);
                }else{
                  $post['listImg']=$rr;                
                }
            }
            
            $post['createTime'] = time();
		        if ($this->vip_free_model->allowField(true)->save($post)) {
                $this->success('保存成功','admin/VipFree/index');
            } else {
                $this->error('保存失败');
            }  
  		 }else{			//添加页面展示
            return $this->fetch();
  		 }
		 
  }

    /**
     * 编辑礼品
     * @param $id
     * 
     */
    public function edit($id)
    {
      if ($this->request->isPost()) {
            $post = $this->request->post(); 
            $post['content']=input('post.content','','htmlspecialchars');
            $upload_model = new Upload();
            if($_FILES['listImg']['name']){
                $rr = $upload_model->saveIamge('listImg');
                if(is_array($rr)){
                  $this->error($rr['msg']);
                }else{
                  $post['listImg']=$rr;                
                }
            }
            
            if ($this->vip_free_model->save($post, ['id'=>$id]) !== false) {
                operateLog('编辑礼品','vip_free',$id,$this->admin_id);
                $this->success('更新成功','admin/VipFree/index');
            } else {
                $this->error('更新失败','admin/VipFree/index');
            }            
        }else{
	        $info = $this->vip_free_model->find($id)->toArray();
	        return $this->fetch('edit', ['info' => $info]);
        }
    }

    /**
     * 礼品删除
     * @param $id
     */
    public function del($id)
    {    	
        if ($this->vip_free_model->save(['isDelete'=>1],['id'=>$id])) {
            operateLog('删除礼品','vip_free',$id,$this->admin_id);
            $this->success('删除成功','admin/VipFree/index');
        } else {
            $this->error('删除失败');
        }
    }

    
}
