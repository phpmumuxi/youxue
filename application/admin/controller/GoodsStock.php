<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\GoodsFreeStock as GoodsFreeStockModel;
use app\common\controller\Upload;

/**
 *  库存管理
 * @package app\admin\controller
 */
class GoodsStock extends AdminBase
{
	protected $goods_stock_model;

    protected function _initialize()
    {
        parent::_initialize();        
        $this->goods_stock_model = new GoodsFreeStockModel(); 
    }
    
    /**
	 * 库存列表
	 * @package app\admin\controller
	 */

    public function index($id)
    {
      $stocks= $this->goods_stock_model->getGoodsFreeStockDatas($id);
      $goods= $this->goods_stock_model->getGoodsFreeOne($id);
      return $this->fetch('index', ['stocks' => $stocks,'goods' => $goods]);        
    }


	/**
	 * 库存添加
	 * @package app\admin\controller
	 */
	public function add($goodsId)
    {
		 if ($this->request->isPost()) {  	       
            $post   = input('post.');
            $upload_model = new Upload();
            if($_FILES['img']['name']){
                $rr = $upload_model->saveIamge('img');
                if(is_array($rr)){
                  $this->error($rr['msg']);
                }else{
                  $post['img']=$rr;                
                }
            }
            $post['goodsId'] = $goodsId;
            $post['createTime'] = time();
		   if ($this->goods_stock_model->allowField(true)->save($post)) {
            $this->success('保存成功','admin/GoodsStock/index?id='.$goodsId);
        } else {
            $this->error('保存失败','admin/GoodsStock/index?id='.$goodsId);
        }		   
		 }else{
            return $this->fetch();
		 }
		 
    }

    /**
     * 编辑库存
     * @param $id
     * 
     */
    public function edit($id)
    {
      $info = $this->goods_stock_model->find($id)->toArray();
      if ($this->request->isPost()) {
            $post = $this->request->post();
            $upload_model = new Upload();
            if($_FILES['img']['name']){
                $rr = $upload_model->saveIamge('img');
                if(is_array($rr)){
                  $this->error($rr['msg']);
                }else{
                  $post['img']=$rr;                
                }
            }
            if ($this->goods_stock_model->updateGoodsFreeStock($post,$id,$info['goodsId'])) {
              operateLog('编辑库存','goods_free_stock',$id,$this->admin_id);
                $this->success('更新成功','admin/GoodsStock/index?id='.$info['goodsId']);
            } else {
                $this->error('更新失败','admin/GoodsStock/index?id='.$info['goodsId']);
            }            
        }else{   
	        return $this->fetch('edit', ['info' => $info]);
        }
    }

    /**
     * 商品上架
     * @param $id
     */
    public function upGoodsFree($id)
    {    	
         $arr['adminId'] = $this->admin_id;   
         $arr['upTime'] = time();
         $arr['status'] = 1;
        if ($this->goods_stock_model->save($arr,['id'=>$id])) {
            $this->success('商品上架成功','admin/GoodsFree/index');
        } else {
            $this->error('商品上架失败','admin/GoodsFree/index');
        }
    }

    /**
     * 删除库存
     * @param $id
     */
    public function delStock($goodsId,$id,$num,$type)
    {      
        if($type){ //删除商品库存并下架
            $arr['status']=2;
            $arr['downId']=$this->admin_id;
            $arr['downTime']=time();
            $s=$this->goods_stock_model->delGoodsStock($goodsId,$id,$num,$arr);
        }else{
            $s=$this->goods_stock_model->delGoodsStock($goodsId,$id,$num);
        }
        $url=url('admin/GoodsStock/index',['id'=>$goodsId]);
        if($s){
          operateLog('删除库存','goods_free_stock',$id,$this->admin_id);
            return ['msg'=>'删除成功','code'=>1,'url'=>$url];
        }else{
            return ['msg'=>'删除失败','code'=>0,'url'=>$url];
        }
    }

    /**
     * 查询库存
     * @param $id
     */
    public function selGoodsFree($goodsId)
    {     
         $nums=db('goods_free_stock')-> where(['goodsId'=>$goodsId,'isDelete'=>0])->sum('num');
         echo $nums;
    }
   
}
