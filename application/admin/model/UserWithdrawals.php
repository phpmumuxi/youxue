<?php
namespace app\admin\model;

use \think\Model;
use think\Db;
use app\common\model\UserMoney as UserMoneyModel;

class UserWithdrawals extends Model
{

	   //用户提现列表数据
    public function getUserWithdrawalsOrders($ordertId,$phone,$exportId)
    {
        if ($ordertId != -1) {
            $where['mb.status'] = $ordertId;
        } else {
            $where['mb.status'] = ['exp', '>=0'];
        }

        if ($exportId != -1) {
            $where['mb.exportStatus'] = $exportId;
        } else {
            $where['mb.exportStatus'] = ['exp', '>=0'];
        }

        $phone?($where['u.phone']=$phone):'';

        $where['mb.money']= ['>', '0'];
        $where['bu.isDelete']= 0;

        $lists  =  db('money_bank')
                -> alias('mb')
                -> field("mb.id,mb.money,mb.status,mb.disposeTime,mb.exportStatus,mb.remarks,u.name as userName,u.phone as userPhone,bu.bankCard,a.name as adminName")
                -> where($where)
                -> join('t_user u','u.id=mb.userId','LEFT')
                -> join('t_bank_user bu','bu.id=mb.bankId','LEFT')
                -> join('t_admin a','a.id=mb.adminId','LEFT')
                -> order('mb.createTime DESC')
                ->paginate(10, false, ['query' => [
                'ordertId' => $ordertId,'phone' => $phone,'exportId' => $exportId]]);
        return $lists;
    }

    //查询出账详细
    public function exportMoneys()
    {
        $time=strtotime(date('Ymd',time()));//当天之前的数据

        $where['mb.exportStatus']= ['in', '0,1'];
        $where['mb.createTime']= ['<',$time ];
        $where['mb.status']= 0;
        $where['bu.isDelete']= 0;

        $lists  =  db('money_bank')
                  -> alias('mb')
                  -> field("mb.id,mb.money,mb.userId,bu.bankName,bu.bankCard,bu.bankPhone,bu.bankUserName")
                  -> where($where)
                  -> join('t_bank_user bu','bu.id=mb.bankId','LEFT')
                  -> select();
        return $lists;
    }

    //根据状态获取出账详细 1.付款中 3确认成功
    public function getExportDatas($type,$adminId='')
    {
        $time=strtotime(date('Ymd',time()));//当天之前的数据

        $where['exportStatus']= $type;
        $where['createTime']= ['<',$time ];
        $where['status']= 0;

        $re  =  db('money_bank')
              ->field('id')
              ->where($where)
              ->select();
        if(!$re) return 'no';
        if($type==3){
            $datas=[
                'status'=>1,
                'exportStatus'=>2,
                'adminId'=>$adminId,
                'disposeTime'=>time()
            ];
        }else{
            $datas=[
                'exportStatus'=>3,
            ];
        }
        $ids= implode(',',array_column($re, 'id'));
        $res=db('money_bank')->where('id in ('.$ids.')')->update($datas);
        if($res){
            return 'ok';
        }else{
            return 'err';
        }
    }

    //提现失败处理
    public function LoseMoneyDatas($id,$adminId,$remarks)
    {
        $info=db('money_bank')->where('id',$id)->find();
        if(!$info) return 'no';
        $userMoney=new UserMoneyModel();
        $re=$userMoney->reduceUserMoney($info['money'],12,$id,$info['orderNo'],$info['userId']);
        $datas=[
                'status'=>2,
                'exportStatus'=>2,
                'adminId'=>$adminId,
                'disposeTime'=>time(),
                'remarks'=>$remarks,
            ];
        if($re){
            //更新状态
            $res=db('money_bank')->where('id',$id)->update($datas);
            if($res){
                return 'ok';
            }else{
                return 'err';
            }
        }

        return 'err';
    }

}