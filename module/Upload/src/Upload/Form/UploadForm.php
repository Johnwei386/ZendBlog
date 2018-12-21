<?php
namespace Upload\Form;

use Zend\InputFilter;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Validator;

class UploadForm extends Form
{
    public function __construct($name = null,$options = array())
    {
        parent::__construct($name,$options);
        $this->addElements();
        $this->addInputFilter();
        $this->setAttribute('class', 'form-horizontal');
    }
    
    public function addElements()
    {
        //File Input
        $file = new Element\File('files');
        $file->setLabel('上传文件:')
             ->setLabelAttributes(array('class'=>'col-sm-2 control-label'))
             ->setAttribute('id', 'files')
             ->setAttribute('class','btn btn-default btn-file');
             //->setAttribute('multiple', true); //可以上传多个文件
        $this->add($file);
    }
    
    public function addInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
        
        //File Input
        $fileInput = new InputFilter\FileInput('files');
        $fileInput->setRequired(true);
        
          //控制上传文件大小；最大为2M = 2097152
        $fileInput->getValidatorChain()
                  ->attachByName('filesize', array(
                      'max' => 3145728,
                  ));
                  
          //匹配限制扩展名
        $fileInput->getValidatorChain()
                  ->attach(new Validator\File\Extension(array(
                      'png',
                      'jpg',
                      'jpeg',
                      'gif',
                  )));
        
          //匹配mime类型
        $fileInput->getValidatorChain()
                  ->attach(new Validator\File\MimeType(array(
                      'image/gif', 
                      'image/jpg',
                      'image/png',
                      'image/jpeg',
                  )));
          /*
           *  附加一个过滤项到一组过滤链中，这里为文件重命名过滤操作
         * filerenameupload过滤项将对上传文件重命名然后移动到target指定的目录下
           *  并且对文件的重命名格式以target文件名为母版。
           */
        /*$fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                'target' => '/var/www/Myblog/public/images/uploads/img.png',
                'randomize' => true,
            ));*/
        
        $inputFilter->add($fileInput);
        
        $this->setInputFilter($inputFilter);
    }
}