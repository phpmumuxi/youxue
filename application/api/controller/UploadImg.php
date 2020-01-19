<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-09-11
 */

//  图片上传

namespace app\api\controller;
use app\common\controller\BaseApi;

use app\common\controller\Upload as Upload;
use app\common\libs\Imgbase as Imgbase;

class UploadImg extends BaseApi
{
    protected $tokenFlag = 0;
    //图片上传
    public function uploadAmage()
    {

        $img = new Upload();
        $re = $img->saveIamge('img');
        $this->apiSuccess($re);
    }

    //图片上传base64
    public function uploadAmagebase64()
    {
        $img = input('post.img');
        if(!$img)return false;
        // $img = new Imgbase();
        $re = Imgbase::strToImg($img);
        if($re)$this->apiSuccess($re);
        else
            $this->apiError('failure');
    }
}
