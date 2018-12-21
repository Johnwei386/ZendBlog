<?php
namespace ZF2AuthAcl\Form\Filter;

use Zend\InputFilter\InputFilter;

class FindpassFilter extends InputFilter
{

    public function __construct()
    {                
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