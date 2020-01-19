<?php
namespace app\admin\model;

use \think\Model;
use \think\Db;
use app\admin\model\Shop as ShopModel;

class Brand extends Model
{
  //获取品牌列表数据
	public function getBrands($brandsName){
		$where = $brandsName ? ['name' => ['like', "%$brandsName%"]] : '';
		$lists= Db::name('brand')->where($where)->where(['isDelete'=>0])->order('sort DESC')->paginate(10, false, ['query' => [
                'brandsName' => $brandsName]]);
		return $lists;
	}

	//判断该品牌下有没有上架的商户
	public function selBrandShop($id){
		$shop=Db::name('shop')->field('id')->where("status !=3 and brandId =".$id)->find();
		return $shop;
	}
}