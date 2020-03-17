<?php
ini_set('memory_limit', '1024M');

class images{
    public $tmp = __DIR__."/tmp/";
    public $dst_im = null;
    public $o_w = 0;
    public $o_h = 0;
    public $all_w = 0;
    public $all_h = 0;
    public $ignore = array(
        '.',
        '..',
        '.DS_Store',
    );

    public function __construct(){

    }

    public function run($rootPath="./"){
        $dirs = scandir($rootPath);
        asort($dirs, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
        if(!empty($dirs)){
            $i = $j = 0;
            $tmp = array();
            foreach($dirs as $key => $value){
                if(in_array($value,$this->ignore)){
                    unset($dirs[$key]);
                    continue;
                }

                if(is_dir($rootPath."/".$value)){
                    $this->run($rootPath."/".$value);
                }else{
                    $num = count($dirs);
                    $filename_pre = $rootPath."t";
                    if($i > 10 || $key == ($num-1)){
                        $i = 0;
                        $j++;
                        $tmp[] = $rootPath."/".$value;
                        !is_dir($filename_pre) && mkdir($filename_pre,777,true);
                        $filename = $filename_pre."/".$j.".jpg";
                        $this->tiny($tmp,$filename);
                        $tmp = array();
                    }else{
                        $tmp[] = $rootPath."/".$value;
                    }
                    $i++;
                }
            }
        }else{
            exit("空目录".PHP_EOL);
        }
    }

    public function tiny($arr,$new_filename){
        $width = $height = 0;
        foreach($arr as $key=>$value){
            list($width, $height) = getimagesize($value);
            $this->all_h += $height;
        }
        foreach($arr as $key=>$value){
            if($key == 0){
                list($this->all_w, $src_h) = getimagesize($value);
                list($this->dst_im,$this->o_w,$this->o_h) = $this->scale($value,$this->all_w,$this->all_h);
            }else{
                list($r,$width,$height) = $this->scale($value,$this->all_w);
                $this->dst_im = $this->mongage($this->dst_im,$r,$this->o_w,$this->o_h,$width,$height);
                $this->o_w = $width;
                $this->o_h += $height;
            }
        }
        $this->jpeg($this->dst_im,$new_filename);
        imagedestroy($this->dst_im);
        $this->dst_im = null;
        $this->o_w = 0;
        $this->o_h = 0;
        $this->all_w = 0;
        $this->all_h = 0;
    }

    //等比缩放
    public function scale($filename,$width,$h=0){
        $fileInfo = getimagesize($filename);//获取照片的信息
        list($src_w, $src_h) = $fileInfo;//将获取到的宽高赋值给变量

        $dst_w = $width;//设置缩放的宽
        $dst_h = $width/$src_w*$src_h;//等比设置缩放的高

        if($fileInfo['mime'] == "image/jpeg"){
            $src_img = imagecreatefromjpeg($filename);
        }elseif($fileInfo['mime'] == "image/png"){
            $src_img = imagecreatefrompng($filename);
        }elseif($fileInfo['mime'] == "image/gif"){
            $src_img = imagecreatefromgif($filename);
        }elseif($fileInfo['mime'] == "image/bmp"){
            $src_img = imagecreatefrombmp($filename);
        }else{
            echo $filename."非图片格式".PHP_EOL;
            $dst_w = 0;
            $dst_h = 0;
        }

        $dst_img = imagecreatetruecolor($dst_w, $dst_h+$h);//创建缩放图片的画布
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);

        return array($dst_img,$dst_w,$dst_h);
    }

    //上下拼接
    public function mongage($o,$source,$o_w,$o_h,$width,$height){
        $source = is_resource($source) ? $source : Imagecreatefromjpeg($source);
        imagecopy($o, $source, 0, $o_h, 0, 0, $width, $height);
        imagedestroy($source);

        return $o;
    }

    //输出合并后水印图片
    public function jpeg($img,$filename){
        imagejpeg($img,$filename);
    }

    public function __destruct(){
        $this->dst_im && imagedestroy($this->dst_im);
    }
}

$images = new images();
$rpath = array(
    "./images/8763",
    "./images/8207",
    "./images/8681",
    "./images/9189",
    "./images/8775",
);
foreach($rpath as $value){
    $images->run($value);
}
exit("执行结束".PHP_EOL);