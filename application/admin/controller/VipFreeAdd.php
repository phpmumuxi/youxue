<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\VipFreeAdd as VipFreeAddModel;


/**
 *  礼品地址管理
 * @package app\admin\controller
 */
class VipFreeAdd extends AdminBase
{
	  protected $vip_add_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->vip_add_model = new VipFreeAddModel(); 
    }
    
  /**
	 * 礼品地址列表
	 * @package app\admin\controller
	 */

    public function index($id)
    {
      $adds= $this->vip_add_model->getVipFreeAddDatas($id);
      return $this->fetch('index', ['adds' => $adds,'id' => $id]);        
    }


	/**
	 * 添加礼品地址
	 * @package app\admin\controller
	 */
	public function add()
    {
     if ($this->request->isPost()) { 
        $post = input('post.');    
       if ($this->vip_add_model->allowField(true)->save($post)) {
              return  ['status'=>'ok','msg'=>'保存成功'];         
        } else {
              return  ['status'=>'err','msg'=>'保存失败'];
        }       
     }else{ 
          $vipFreeId=input('param.vipFreeId');
          return $this->fetch('add', ['vipFreeId' => $vipFreeId]);
		 }
		 
    }

    /**
     * 编辑礼品地址
     * @param $id
     * 
     */
    public function edit()
    {
      if ($this->request->isPost()) {
            $post = $this->request->post();
            $id=input('post.id');
            if ($this->vip_add_model->save($post, ['id'=>$id]) !== false) {
              operateLog('编辑礼品地址','vip_add',$id,$this->admin_id);
                return  ['status'=>'ok','msg'=>'更新成功'];
            } else {
                return  ['status'=>'err','msg'=>'更新失败'];
            }            
        }else{ 
          $id=input('param.id');  
          $info = $this->vip_add_model->find($id)->toArray();
	        return $this->fetch('edit', ['info' => $info]);
        }
    }

    /**
     * 删除礼品地址
     * @param $id
     */
    public function del()
    {
        $id=input('post.id');
        if ($this->vip_add_model->destroy($id)) {
          operateLog('删除礼品地址','vip_add',$id,$this->admin_id);
          return  ['status'=>'ok','msg'=>'删除成功'];
        } else {
          return  ['status'=>'err','msg'=>'删除失败'];
        }
    }
   
}
