<?php
namespace ZF2AuthAcl\Form\Filter;

use Zend\InputFilter\InputFilter;

class LoginUpFilter extends InputFilter
{

    public function __construct()
    {
        $this->add(array(
            'name' => 'account',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            //\Zend\Validator\NotEmpty::IS_EMPTY => '请输入账号！',
                        )
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            //\Zend\Validator\NotEmpty::IS_EMPTY => '请输入密码！',
                        ),
                    ),
                ),
                array(
                    'name'=>'StringLength',
                    'options'=>array(
                        'encoding'=>'UTF-8',
                        'min'=>6,
                    ),
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'new_pass',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        ));
        
        $this->add(array(
            'name' => 'email',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array('name' => 'NotEmpty'),
                array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\EmailAddress::INVALID_FORMAT => '不可用的邮箱格式',
                        ),
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'captcha',
            'required' => true,
        ));
    }
}