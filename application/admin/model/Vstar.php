<?php
namespace app\admin\model;

use \think\Model;

class Vstar extends Model
{
    public function shareUserData($arr)
    {
       $data = db('user')->where('id',$arr['type'])->value('id');
       if(!$data)return false;
       $array = db('user')->field('id,memberLevel,shareId')->where('phone',$arr['phone'])->find();

       if(!$array){
            $a = [
                'phone'=>$arr['phone'],
                'createTime'=>time(),
                'token'=>0,
                'memberLevel'=>0,
                'status'=>0,
                'isCmbc'=>0,
                'shareId'=>$data
            ];
        $id = db('user')->insertGetId($a);
        $array = [
            'id'=>$id,
            'memberLevel'=>0
        ];
       }else{
            if(!$array['shareId']){
                db('user')->update([
                    'id'=>$array['id'],
                    'shareId'=>$data
                ]);
            unset($array['shareId']);
            }else{
                return '已参与活动，请登录“同城优学”APP查询活动详情';
            }
       }
        $s = new \app\api\model\AddShare();
        $re = $s->addShareDate($array,$data);
        return $re;
    }
}