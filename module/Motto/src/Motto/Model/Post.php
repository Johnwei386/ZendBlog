<?php
namespace Motto\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\Regex as RegexValidator;
use Zend\Validator\ValidatorInterface;

class Post implements InputFilterAwareInterface
{
    public $id;
    public $author;
    public $mtime;
    public $motto;   
    protected $inputFilter;
    protected $validator;
    
    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id']))?$data['id']:null;
        $this->author = (!empty($data['author']))?$data['author']:null;
        $this->mtime = (!empty(($data['mtime'])))?$data['mtime']:null;
        $this->motto = (!empty($data['motto']))?$data['motto']:null;
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
                'name' => 'author',
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
                                \Zend\Validator\NotEmpty::IS_EMPTY => '作者不能为空！',
                            ),
                        ),
                    ),
                    array(
                        'name'=>'StringLength',
                        'options'=>array(
                            'encoding'=>'UTF-8',
                            'max'=>100,
                            'messages' => array(
                                'stringLengthTooLong' => '作者名称太长！',
                            ),
                        ),
                    ),
                ),
            ));           
            
            $inputFilter->add(array(
                'name' => 'motto',
                'required' => true,
                'filters' => array(
                    array('name'=>'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'=>'NotEmpty',
                        'options'=>array(
                            'messages'=>array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => '格言不能为空！',
                            ),
                        ),
                    ),
                    array(
                        'name'=>'StringLength',
                        'options'=>array(
                            'encoding'=>'UTF-8',
                            'max'=>255,//最大255个字符
                            'messages' => array(
                                'stringLengthTooLong' => '格言过长！',
                            ),
                        ),
                    ),
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'captcha',
                'required' => true,
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
