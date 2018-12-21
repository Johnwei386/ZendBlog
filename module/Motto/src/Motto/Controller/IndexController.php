<?php
namespace Motto\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Motto\Model\Post;
use Motto\Form\MottoForm;
use Zend\Captcha\Image;
use Blog\Validate\ImageValidate;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    protected $postTable;
    
    public function indexAction()
    {
        //grab the paginator from the blogTable
        $paginator = $this->getPostTable()->fetchAll(true);
        //set the current page to what has been passed in query string
        //,or to set 1 if none set
        $paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page',1));
        //set the number of items per page
        $paginator->setItemCountPerPage(10);
        return new ViewModel(array(
            //'posts' => $this->getPostTable()->fetchAll(),
            'posts' => $paginator,
        ));
    }
    
    public function captchaAction()
    {
        $captcha = new Image();
        $image = new ImageValidate($captcha);
        $form = new MottoForm($image->getCaptchaImage());
        $request = $this->getRequest();
          //判断是否ajax请求，javascript--xmlHttpRequest请求
        if ($request->isXmlHttpRequest()) {
            $model = new ViewModel(array(
                'form' => $form,
            ));
            $model->setTerminal(true); //禁用layout布局
            return $model->setTemplate('motto/index/captcha.phtml'); //设置模板
        } else {
               //非ajax的请求一律跳转到格言主页
            return $this->redirect()->toRoute('motto');
        }
    }
    
    public function addAction()
    {
        $captcha = new Image();
        $image = new ImageValidate($captcha);
        $form = new MottoForm($image->getCaptchaImage());
        
        $request = $this->getRequest();
        if(!$request->isXmlHttpRequest()){
            return $this->redirect()->toRoute('motto');
        }
        if($request->isPost()){
            $post = new Post();
            $form->setInputFilter($post->getInputFilter());
            $data = $request->getPost();            
            $form->setData($data);
            
            if($form->isValid()){
                $data = $form->getData();
                $data['mtime'] = date("Y-m-d H:i:s");
                $post->exchangeArray($data);               
                $this->getPostTable()->saveData($post);
                
                return new JsonModel(array(
                    'status' => true,
                    'message' => '成功写入格言！',
                ));
                return $this->redirect()->toRoute('motto');
            }
        }
        return new JsonModel(array(
            'status' => false,
            'message' => $form->getMessages(),
        ));
    }
    
    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id',0);
        if(!$id){
            return $this->redirect()->toRoute('motto');
        }
    
        try{
            $post = $this->getPostTable()->getRow($id);
        }catch(\Exception $e){
            throw new \Exception($e->getPrevious()->getMessage());
        }
    
        $captcha = new Image();
        $image = new ImageValidate($captcha);
        $form = new MottoForm($image->getCaptchaImage());
        $form->get('submit')->setValue('修改');
          //绑定数据
        $form->bind($post);
    
        $request = $this->getRequest();
        if($request->isPost()){            
            $form->setInputFilter($post->getInputFilter());
            $form->setData($request->getPost());
    
            if($form->isValid()){                
                    //因为使用了bind，所以不用显式回传数据给$post，它会自动将数据回传给$post
                    //我们要做的只是将数据写入数据库即可。
                $this->getPostTable()->saveData($post);
    
                return $this->redirect()->toRoute('motto');
            }
        }
    
        return array(
            'id' => $id,
            'form' => $form,
        );
    }
    
    public function deleteAction()
    {   
        $request=$this->getRequest();
        if($request->isPost()){
            $receive = $request->getPost();
            $id = isset($receive['id'])?$receive['id']:0;
            $id = (int)$id;
            $isdel = isset($receive['confirm'])?$receive['confirm']:'No';
    
            if($isdel == 'Yes'){
                $this->getPostTable()->deleteData($id);
                return new JsonModel(array(
                    'status' => true,
                    'message' => '删除格言成功！',
                ));
            }
        }   
        return new JsonModel(array(
                    'status' => false,
                    'message' => '删除格言失败！',
                ));
    }
    
    public function getPostTable()
    {
        if(!$this->postTable){
            $sm = $this->getServiceLocator();
            $this->postTable = $sm->get('Motto\Model\PostTable');
        }
        return $this->postTable;
    }
}
