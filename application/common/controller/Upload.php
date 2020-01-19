<?php
/**
 * User: LiuTong
 * Date: 2017-07-17
 * Time: 16:01
 */

namespace app\common\controller;
use app\common\controller\ImgCompress;

class Upload
{

    /**
     * 保存图片 form表单file形式提交
     * @param $fileName
     * @param int $size
     * @param string $ext
     * @return mixed|string
     */
    public function saveIamge($fileName, $size = 2, $ext = 'jpg,png,gif,jpeg,bmp')
    {
        $file = request()->file($fileName, $size = 2, $ext);
        $dir='/home/data/images';
        $img_size = $file->getInfo()['size'];
        $size = $size * 1048576; // 1024 * 1024 = 1048576 = 1MB
        $info = $file->validate(['ext' => $ext])->move($dir);
        
        if ($info) {
            $path = $info->getSaveName();
            if($img_size > $size){             
                (new ImgCompress($dir. DS .$path))->compressImg();
            }
            return $path;                
        } else {
            return ['msg'=>$file->getError()];
        }
            
    }
}