<?php
namespace app\admin\model;

use \think\Model;
use \think\Db;
use app\shop\model\School as SchoolModel;

class Shop extends Model
{
	protected static function init()
    {
        parent::init();

        //新增商户，品牌表 shopNum+1
        self::event('after_insert', function ($shop) {
            $brand_id = $shop->brandId;
            Db::name('brand')->where('id',$brand_id)->setInc('shopNum');
        });
        
    }

    //获取商户信息列表(账号管理下)
	public function getAccountShops($shopsName,$status){
		$where = $shopsName ? ['name' => ['like', "%$shopsName%"]] : '';
		if($status==1){
	        $where2 =  'status = 1';
	    }elseif($status==2){
	        $where2 = 'status = 2';
	    }elseif($status==5){
	        $where2 = 'status = 0';
	    }else{
	        $where2 = '';
	    }
		$res= Db::name('shop')
			-> field('id,name,bankUserName,bankName,bankCard,bankType,time,status')
			->where($where)
			->where($where2)
			->where('status!=3')
			->order(['id'=>'DESC'])
			->paginate(10, false, ['query' => [
                'shopsName' => $shopsName,'status' => $status]]);
		return $res;
	}

	public function getShopInfoShops($shopName,$type,$status){
		$where = $shopName ? ['name' => ['like', "%$shopName%"]] : '';
		if($type==1){
			$where1 = 'bankType=1';
		}elseif($type==2){
			$where1 = 'bankType=2';
		}else{
			$where1 = '';
		}
		if($status==5){
			$where2 = 'status=0';
		}elseif($status==1){
			$where2 = 'status=1';
		}elseif($status==2){
			$where2 = 'status=2';
		}else{
			$where2 = '';
		}
		$res= Db::name('shop')
			-> field('id,name,bankUserName,bankName,bankCard,bankType,time,status')	
			->where('status!=3')
			->where($where)
			->where($where1)
			->where($where2)
			->order(['id'=>'DESC'])
			->paginate(10, false, ['query' => [
                'shopsName' => $shopName,'type' => $type,'status' => $status]]);
		return $res;
	}

	//获取商户详情(商户信息下)
	public function getShopInfoDatas($id){
		$res= Db::name('shop')
			->alias('s')
			-> field('s.*,b.name as brandName,a.name as createName')
			->where('s.id',$id)
			->join('t_brand b','b.id = s.brandId','LEFT')
			->join('t_admin a','a.id = s.adminId','LEFT')
			->find();
		if($res['status']==0){
			$res['status'] = '待上架';
		}elseif($res['status']==1){
			$res['status'] = '已上架';
		}elseif($res['status']==2){
			$res['status'] = '已下架';
		}else{
			$res['status'] = '已删除';
		}
		$res['bankType'] = $res['bankType']==1 ? '对公账号':'私人账号';
		$res['createTime'] = date('Y-m-d',$res['createTime']);
		return $res;
	}

  //判断商户名是否存在
	public function searchAccountShopName($name){
		$res= Db::name('shop')->where(['name'=>$name])->value('id');
		return $res;
	}

	//获取品牌(商户账号选择)
	public function getAccountBrands(){
		$lists= Db::name('brand')->field('id,name')->where(['isDelete'=>0])->select();
		return  $lists;
	}

	//删除商户
	public function delAccountShop($id){
		set_time_limit(180); // 最大执行时间这里设置180秒
		//1该商户下所有校区
		$schools = Db::name('school')->field('id')->where("status != 3 and shopId =".$id)->select();
		$schoolSums=count($schools);
		$school_model = new SchoolModel();
		
		$nums=0;
		foreach ($schools as $key => $vo) {
			//删除商户下所有校区数据
			$res=$school_model->delSchool($vo['id']);
			if($res){
				$nums++;
			}
		}
	
		if($nums==$schoolSums){			
	       //2该商户状态改变
	       Db::name('shop')->update(['status'=>3,'id'=>$id]);
			//3.品牌表 商户数量-1 
		   $info = Db::name('shop')->find($id);
		   $shopNum = Db::name('brand')->where('id',$info['brandId'])->value('shopNum');
		   if($shopNum !=0 ){
		   		Db::name('brand')->where('id',$info['brandId'])->setDec('shopNum');
		   }	
		   return true;
		}
		return false;
	}

	//商户下架(商户信息)
	public function shopInfoDownShop($id){
		Db::startTrans();
        try {
			//1该商户下所有上架校区(下架)
			Db::name('school')->where("status = 1 and shopId =".$id)->update(["status" =>2]);
			//2该商户下架
			Db::name('shop')->update(['status'=>2,'id'=>$id]);
		    Db::commit();            
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
		return true;
	}

	//商户上架(商户信息)
	public function shopInfoUpShop($id){
		//该商户下有上架校区
		$res = Db::name('school')->field('id')->where("status = 1 and shopId =".$id)->find();
		if($res){			
			Db::startTrans();
	        try {
				//该商户下所有下架校区
				//Db::name('school')->where("status = 2 and shopId =".$id)->update(["status" =>1]);
				//2该商户上架
				Db::name('shop')->update(['status'=>1,'id'=>$id]);
			    Db::commit();
	        } catch (\Exception $e) {
	            Db::rollback();
	            return false;
	        }
			return true;
		}else{
			return 0;
		}
	}

	//商户账单列表
	public function getShopBills($shopId){
		$res= Db::name('shop_money_record')
			->alias('mr')
			-> field('mr.id,mr.status,(mr.recordDate + s.time*86400) as outTime,mr.recordDate as inTime,mr.money,mr.isOff,s.name as shopName,s.bankUserName,s.bankName,s.bankCard')
			->join('t_shop s','s.id = mr.shopId','LEFT')			
			->where('s.status!=3')
			->where('s.id',$shopId)
			->order('mr.recordDate DESC')
			->paginate(10);
		return $res;
	}

	//商户总收入
	public function getShopTotalMoney($shopId){
		$money=  db('shop_money_record')
				->where('isOff=1')
				->where('shopId',$shopId)
				->sum('money');
		return $money?$money:0;
	}

	//该商户账单详情
	public function getBillDetail($id){
		$re=  db('shop_money_record')
			->field('recordDate,shopId')
			->find($id);
		$startTime = strtotime(date('Y-m-d',$re['recordDate']));//签约日期
		$endTime = $startTime + 86400;	//一天24小时

		$res= db('order_class')
			-> field('id,orderNo,name,money,referrerMoney,adviserMoney,userMoney,shopOne,signDate,isAgain,shopTwo')			
			->where('isSign = 1')
			->where('shopId',$re['shopId'])
			->where('signDate','between',[$startTime,$endTime])
			->paginate(10);
			//halt($res);
		return $res;
	}

}