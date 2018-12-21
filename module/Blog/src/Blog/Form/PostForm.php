<?php
namespace Blog\Form;

use Zend\Form\Form;
use Zend\Captcha\AdapterInterface as CaptchaAdapter;
use Zend\Form\Element;
use Zend\Config\Reader\Xml;

class PostForm extends Form
{ 
    public function __construct(CaptchaAdapter $captcha)
    {
        parent::__construct('blog');
        $reader = new Xml();
        $data = $reader->fromFile(CONFIG_PATH.DS.'config.xml');
        
        $this->setAttribute('class', 'form-horizontal');
        
        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
            'attributes' => array(
                'class' => 'form-control',
            ),
        ));
        $this->add(array(
            'name' => 'title',
            'type' => 'Text',
            'options' => array(
                'label' => '标题：',
                'label_attributes' => array(
                    'class'  => 'col-sm-1 control-label'
                ),
            ),
            'attributes' => array(
                'class' => 'form-control',
            ),
        ));
        $this->add(array(
            'name' => 'classify',
            'type' => '\Zend\Form\Element\Select',
            'options' => array(
                'label' => '分类：',
                'label_attributes' => array(
                    'class'  => 'col-sm-1 control-label'
                ),
                'value_options' => $data['category'],
            ),
            'attributes' => array(
                'class' => 'form-control',
            ),
        ));
        $this->add(array(
            'name' => 'ctime',
            'type' => 'Text',
            'options' => array(
                'label' => '时间：',
                'label_attributes' => array(
                    'class'  => 'col-sm-1 control-label'
                ),
            ),
            'attributes' => array(
                'value' => date("Y-m-d H:i:s"),
                'class' => 'form-control',
            ),
        ));
        $this->add(array(
            'name' => 'slide',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'label' => '幻灯片：',
                'label_attributes' => array(
                    'class'  => 'slide control-label'
                ),
                'value_options' => array(
                   'Y' => '是',
                   'N' => '否',
                ),
            ),
            'attributes' => array(
                'value' => 'N',
            ),
        ));
        $this->add(array(
            'name' => 'image',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'content',
            'type' => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => '内容：',
                'label_attributes' => array(
                    'class'  => 'col-sm-1 control-label'
                ),
            ),
            'attributes' => array(
                'id' => 'myedit',
                'class' => 'form-control',
            ),
        ));
        $this->add(array(
            'name' => 'captcha',
            'type' => 'Zend\Form\Element\Captcha',
            'options' => array(
                'captcha' => $captcha,
            ),
            'attributes'  => array(
                'placeholder' => '请输入验证码',
                'class' => 'form-control',
            ),
        ));
        //$this->add(new Element\Csrf('security'));
        $this->add(array(
            'name' => 'send',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Submit',
                'id'  => 'send',
                'class' => 'btn btn-primary form-control'
            ),
        ));
    }
}