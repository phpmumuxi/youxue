<?php  
namespace app\common\controller; 
/** 
 * 图片压缩类：通过缩放来压缩。 
* 如果要保持源图比例，把参数$percent保持为1即可。 
* 即使原比例压缩，也可大幅度缩小。如果缩小比例，则体积会更小。  
 */  
class ImgCompress{  
  
       private $src;  
       private $image;  
       private $imageinfo;  
       private $percent = 0.8;  
  
       /** 
        * 图片压缩 
        * @param $src 源图 
        * @param float $percent  压缩比例 
        */  
       public function __construct($src, $percent=1)  
       {  
              $this->src = $src;  
              $this->percent = $percent;  
       }  
  
  
       // 高清压缩图片   
       public function compressImg()  
       {  
			     if(!file_exists($this->src)) return false;
           $this->_openImage();			
			     $this->_saveImage($this->src);              
       }  
  
       /** 
        * 内部：打开图片 
        */  
       private function _openImage()  
       {  
              list($width, $height, $type, $attr) = getimagesize($this->src);  
              $this->imageinfo = array(  
                     'width'=>$width,  
                     'height'=>$height,  
                     'type'=>image_type_to_extension($type,false),  
                     'attr'=>$attr  
              );  
              $fun = "imagecreatefrom".$this->imageinfo['type'];  
              $this->image = $fun($this->src);  
              $this->_thumpImage();  
       }  
       /** 
        * 内部：操作图片 
        */  
       private function _thumpImage()  
       {  
              $new_width = $this->imageinfo['width'] * $this->percent;  
              $new_height = $this->imageinfo['height'] * $this->percent;  
              $image_thump = imagecreatetruecolor($new_width,$new_height);  
              //将原图复制带图片载体上面，并且按照一定比例压缩,极大的保持了清晰度  
              imagecopyresampled($image_thump,$this->image,0,0,0,0,$new_width,$new_height,$this->imageinfo['width'],$this->imageinfo['height']);  
              imagedestroy($this->image);  
              $this->image = $image_thump; 
			  
       }  
       /** 
        * 输出图片:保存图片则用saveImage() 
        */  
       private function _showImage()  
       {  
              header('Content-Type: image/'.$this->imageinfo['type']);  
              $funcs = "image".$this->imageinfo['type'];  
              $funcs($this->image);  
       }  
       /** 
        * 保存图片到硬盘：  
        */  
       private function _saveImage($dstImgName)  
       {  
              $funcs = "image".$this->imageinfo['type'];  
              $funcs($this->image,$dstImgName);  
       }  
  
       /** 
        * 销毁图片 
        */  
       public function __destruct(){  
              imagedestroy($this->image);			  
       }  
  
}