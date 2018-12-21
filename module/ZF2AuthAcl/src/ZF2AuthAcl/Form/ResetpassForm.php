<?php
namespace ZF2AuthAcl\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Captcha\AdapterInterface as CaptchaAdapter;

class ResetpassForm extends Form
{

    public function __construct($name,CaptchaAdapter $captcha)
    {
        parent::__construct($name);
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');

        $this->add(array(
            'name' => 'password',
            'type' => 'password',
            'attributes' => array(
                'id' => 'password',
                'class' => 'form-control',
            ),
            'options' => array(
                'label' => '设置密码',
                'label_attributes' => array(
                    'class'  => 'col-sm-1 control-label'
                ),
            )
        ));

        $this->add(array(
            'name' => 'new_pass',
            'type' => 'password',
            'attributes' => array(
                'id' => 'new_pass',
                'class' => 'form-control',
            ),
            'options' => array(
                'label' => '确认密码',
                'label_attributes' => array(
                    'class'  => 'col-sm-1 control-label'
                ),
            )
        ));

        $this->add(array(
            'name' => 'captcha',
            'type' => 'Zend\Form\Element\Captcha',
            'options' => array(
                'label' => '验证码',
                'label_attributes' => array(
                    'class'  => 'col-sm-1 control-label'
                ),
                'captcha' => $captcha,
            ),
            'attributes' => array(
                'class' => 'form-control',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'loginCsrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 3600
                )
            )
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Submit',
                'class' => 'form-control btn btn-primary'
            )
        ));
    }
}