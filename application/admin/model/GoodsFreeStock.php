<?php
namespace app\admin\model;

use \think\Model;
use think\Db;

class GoodsFreeStock extends Model
{

  protected static function init()
    {
        parent::init();

        //新增库存，goods_free表 num+
        self::event('after_insert', function ($goods) {
            $goodsId = $goods->goodsId;
            $num = $goods->num;
            $where['num']=['exp','num+' . $num];
            Db::name('goods_free')->where('id',$goodsId)->update($where);
        });
    }

    /**
     * 获取商品库存详情数据
     * 
     */
    public function getGoodsFreeStockDatas($id)
    {
      $res  =  Db::name('goods_free_stock')
            -> alias('g')           
            -> field('g.*,f.name as goodsName')
            -> where(['isDelete'=>0,'goodsId'=>$id])
            -> join('t_goods_free f','f.id = g.goodsId','LEFT')
            -> select(); 
      
      return $res;
    }

  /**
     * 获取单个商品详情数据
     * 
     */
    public function getGoodsFreeOne($id)
    {
      $res  =  Db::name('goods_free')              
              -> field('id,startTime')
              -> find($id); 
      
      return $res;
    }

	/**
     * 修改商品库存
     * 
     */
    public function updateGoodsFreeStock($post,$id,$goodsId)
    {
      Db::startTrans();
      try {
          Db::name('goods_free_stock')->where('id',$id)->update($post);
          $nums=Db::name('goods_free_stock')-> where(['goodsId'=>$goodsId,'isDelete'=>0])->sum('num');
          Db::name('goods_free')-> where('id',$goodsId)->update(['num'=>$nums]);
          Db::commit();     
      } catch (\Exception $e) {
          Db::rollback();
          return false;
      }
	  return true;
    }

    /**
     * 删除商品库存
     * 
     */
    public function delGoodsStock($goodsId,$id,$num,$arr=[])
    {
      Db::startTrans();
      try {
          Db::name('goods_free_stock')->where('id',$id)->update(['isDelete'=>1]);
          $arr['num']=['exp','num-' . $num];
          Db::name('goods_free')-> where('id',$goodsId)->update($arr);
          Db::commit();  
      } catch (\Exception $e) {
          Db::rollback();
          return false;
      }
	  return true;
    }

}