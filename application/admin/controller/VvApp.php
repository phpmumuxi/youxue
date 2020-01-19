<?php
/**
 * User: Xin
 * Date: 2017-09-28
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\VvApp as VvAppModel;

class VvApp extends AdminBase
{

    public function androidIndex()
    {
        $VvApp = new VvAppModel();
        $data = $VvApp->appInfoData(1);
        $url=explode('/',$data['url']);
        $data['url']=array_pop($url);
        return view('index',['type'=>1,'data'=>$data]);
    }

    public function iosIndex()
    {
        $VvApp = new VvAppModel();
        $data = $VvApp->appInfoData(2);
        return view('index',['type'=>2,'data'=>$data]);
    }

    //安卓更新
    public function androidUpdate()
    {
        $VvApp = new VvAppModel();
        $data = $VvApp->appInfoData(1);
        $url=explode('/',$data['url']);
        $url=array_pop($url);
        $post=input('post.');
        $post['url'] = $data['url'];
        $post['name'] = $data['name'];
        // print_r($post);exit;
        $b = mb_strlen($post['explain'],'utf8');
        if($b<10||$b>300){
            $this->error('说明文字范围为10~300。');
        }
        if($post['version']<=$data['version']){
            $this->error('版本好不能小于当前的版本');
        }
        if(!$_FILES||$_FILES['url']['name']!=$url){
            $this->error('安装包不存在或者包名称不同');
        }
        if ($_FILES["url"]["error"] > 0){
            $this->error("错误:"  . $_FILES["url"]["error"]);
        }
        if(!is_dir('/home/data/android/')){
            mkdir ('/home/data/android/',0777,true);
        }else{
            if(file_exists('/home/data/android/'.$url)){
                if(!unlink ('/home/data/android/'.$url)){
                    $this->error('上传失败，请找开发人员');
                }
            }
        }
        if(!move_uploaded_file($_FILES['url']['tmp_name'], '/home/data/android/' . $_FILES['url']['name'])){
            if(!copy($_FILES['url']['tmp_name'], '/home/data/android/' . $_FILES['url']['name'])){
                $this->error('上传文件失败');
            }
        }
        $data = $VvApp->androidUpdate($post);
        if($data){
            $this->success('操作成功');
        }else{
            $this->error('操作失败，请找开发人员');
        }
    }

    //安卓更新
    public function iosUpdate()
    {
        $VvApp = new VvAppModel();
        $data = $VvApp->appInfoData(2);
        $post=input('post.');
        // print_r($post);exit;
        if($post['version']<=$data['version']){
            $this->error('版本好不能小于当前的版本');
        }
        $a = mb_strlen($post['explain'],'utf8');
        if($a<10||$a>300){
            $this->error('说明文字范围为10~300。');
        }

        $data = $VvApp->iosUpdateDate($post);
        if($data){
            $this->success('操作成功');
        }else{
            $this->error('操作失败，请找开发人员');
        }
    }


}