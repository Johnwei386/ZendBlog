<?php
namespace Blog\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\Regex as RegexValidator;
use Zend\Validator\ValidatorInterface;

class Post implements InputFilterAwareInterface
{
    public $id;
    public $title;
    public $classify;
    public $ctime;
    public $slide;
    public $image;
    public $content;
    protected $inputFilter;
    protected $validator;
    
    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id']))?$data['id']:null;
        $this->title = (!empty($data['title']))?$data['title']:null;
        $this->classify = (!empty($data['classify']))?$data['classify']:null;
        $this->ctime = (!empty(($data['ctime'])))?$data['ctime']:null;
        $this->slide = (!empty(($data['slide'])))?$data['slide']:null;
        $this->image= (!empty(($data['image'])))?$data['image']:null;
        $this->content = (!empty($data['content']))?$data['content']:null;
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("未使用");
    }
    
    public function getInputFilter()
    {   
        if(!$this->inputFilter){
            $inputFilter = new inputFilter;
            
            $inputFilter->add(array(
                'name' => 'id',
                'required' => true,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'title',
                'required' => true,
                'filters' => array(
                    array('name'=>'StripTags'),
                    array('name'=>'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'=>'NotEmpty',
                        'options'=>array(
                            'messages'=>array(
                                //\Zend\Validator\NotEmpty::IS_EMPTY => '请输入标题！',
                            ),
                        ),
                    ),
                    array(
                        'name'=>'StringLength',
                        'options'=>array(
                            'encoding'=>'UTF-8',
                            'min'=>3,
                            'max'=>30,
                            /* 'messages' => array(
                                'stringLengthTooShort' => '标题太短', 
                                'stringLengthTooLong' => '标题太长',
                            ), */
                        ),
                    ),
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'ctime',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Regex',
                        'options' => array(
                            'pattern' => '/^[0-9]{4}[-](0?[1-9]|1[012])[-](0?[1-9]|[12][0-9]|3[01])'.
                                         ' (0?[0-9]|1[0-9]|2[0-3]):(0?[0-9]|[1-5][0-9]):(0?[0-9]|[1-5][0-9])$/',
                            'messages' => array(
                                \Zend\Validator\Regex::NOT_MATCH => '不可用的时间格式',
                            ),
                        ),
                    ),
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'content',
                'required' => true,
                'filters' => array(
                    array('name'=>'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'=>'NotEmpty',
                        'options'=>array(
                            'messages'=>array(
                                //\Zend\Validator\NotEmpty::IS_EMPTY => '内容不能为空',
                            ),
                        ),
                    ),
                    array(
                        'name'=>'StringLength',
                        'options'=>array(
                            'encoding'=>'UTF-8',
                            'min'=>0,
                            'max'=>65535,
                        ),
                    ),
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'captcha',
                'required' => true,
                //'error_message' => '验证码错误',
            ));
            
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
    
    /*return ValidatorInterface,正则匹配认证*/
    public function getValidator()
    {
        if(null === $this->validator){
            $this->validator = new RegexValidator('/<script(.*?)<\/script>/');
        }
        return $this->validator;
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}