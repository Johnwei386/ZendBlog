<?php
namespace Motto\Form;

use Zend\Form\Form;
use Zend\Captcha\AdapterInterface as CaptchaAdapter;
use Zend\Form\Element;

class MottoForm extends Form
{ 
    public function __construct(CaptchaAdapter $captcha)
    {
        parent::__construct('motto');       
        $this->setAttribute('class', 'form-horizontal');
        
        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'author',
            'type' => 'Text',
            'options' => array(
                'label' => '作者:',
                'label_attributes' => array(
                    'class'  => 'col-sm-1 control-label'
                ),
            ),
            'attributes' => array(
                'class' => 'form-control',
            ),
        ));
        $this->add(array(
            'name' => 'motto',
            'type' => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => '格言:',
                'label_attributes' => array(
                    'class'  => 'col-sm-1 control-label'
                ),
            ),
            'attributes' => array(
                'class' => 'form-control',
            ),
        ));       
        $this->add(array(
            'name' => 'captcha',
            'type' => 'Zend\Form\Element\Captcha',
            'options' => array(
                'captcha' => $captcha,
                'label' => '验证码:',
            ),
            'attributes'  => array(
                'placeholder' => '请输入验证码',
                'class' => 'form-control',
            ),
        ));   
        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'Csrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 3600
                )
            )
        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Submit',
                'id'  => 'send',
                'class' => 'btn btn-primary form-control'
            ),
        ));
    }
}
