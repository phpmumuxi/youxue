<?php
namespace app\admin\model;

use \think\Model;

class VvApp extends Model
{
    public function appInfoData($type)
    {
        if($type==1){
            $data = db('app_android')
                ->field('url,version,name')
                ->order('id desc')
                // ->fetchsql(1)
                ->find();
        }else{
            $data = db('app_ios')
                ->field('version')
                ->order('id desc')
                ->find();
        }
        return $data;
    }

    public function androidUpdate($arr)
    {
        $arr['createTime']=time();
        $a = db('app_android')->insert($arr);
        return $a;
    }

    public function iosUpdateDate($arr)
    {
        $arr['createTime']=time();
        $a = db('app_ios')->insert($arr);
        return $a;
    }
}