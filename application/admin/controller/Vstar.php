<?php
/**
 * User: Xin
 * Date: 2017-09-28
 */

namespace app\admin\controller;
use think\Controller;
use app\admin\model\Vstar as VstarModel;

class Vstar extends Controller
{

    public function index()
    {
        $type = input('get.type/d');
        return view('index',['type'=>$type]);
    }

    public function shareUser()
    {
        $arr = input('post.');
        $vstar = new VstarModel();
        $data = $vstar->shareUserData($arr);
        echo json_encode($data);
    }


}