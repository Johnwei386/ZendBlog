<?php
namespace Blog\Validate;

use Zend\Captcha\Image;

class ImageValidate
{
    protected $image;
    public function __construct(Image $image)
    {
        $this->image = $image;
    }
    
    public function getCaptchaImage()
    {
        $fontpath = ROOT_PATH."/public/fonts/italic_bold.ttf";
        $this->image->setFont($fontpath);
        $this->image->setExpiration(1440); //24分钟
        $this->image->setFontSize(24);
        $this->image->setGcFreq(10); //每10个文件删除一个
        $this->image->setDotNoiseLevel(50);
        $this->image->setLineNoiseLevel(3);
        
        return $this->image;
    }
}