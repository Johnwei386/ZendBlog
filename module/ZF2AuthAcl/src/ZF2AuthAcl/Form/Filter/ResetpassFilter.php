<?php
namespace ZF2AuthAcl\Form\Filter;

use Zend\InputFilter\InputFilter;

class ResetpassFilter extends InputFilter
{

    public function __construct()
    {
        $this->add(array(
            'name' => 'password',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array('name' => 'NotEmpty'),
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
            'name' => 'captcha',
            'required' => true,
        ));
    }
}
