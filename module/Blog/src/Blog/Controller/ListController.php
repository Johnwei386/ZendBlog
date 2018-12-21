<?php
namespace Blog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Blog\Model\Post;
use Blog\Form\PostForm;
use Zend\Captcha\Image;
use Blog\Validate\ImageValidate;

class ListController extends AbstractActionController
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
    
    public function addAction()
    {
        $captcha = new Image();
        $image = new ImageValidate($captcha);
        $form = new PostForm($image->getCaptchaImage());
        $form->get('send')->setValue('添加');
        
        $request = $this->getRequest();
        if($request->isPost()){
            $post = new Post();
            $form->setInputFilter($post->getInputFilter());
            $form->setData($request->getPost());
            //\Zend\Debug\Debug::dump($form->getMessages());die();
            
            if($form->isValid()){
                $data = $form->getData();
                $data['image'] = $this->imageFilter($data['content']);
                $post->exchangeArray($data);
                $post->content=preg_replace('{<script(.*?)>(.*?)</script>}', '$2', $post->content);
                $this->getPostTable()->saveData($post);
                
                return $this->redirect()->toRoute('blog');
            }
        }
        return array(
            'form' => $form,
        );
    }
    
    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id',0);
        if(!$id){
            return $this->redirect()->toRoute('blog',array(
                'action' => 'add',
            ));
        }
    
        try{
            $post = $this->getPostTable()->getRow($id);
        }catch(\Exception $e){
            throw new \Exception($e->getPrevious()->getMessage());
        }
    
        $captcha = new Image();
        $image = new ImageValidate($captcha);
        $form = new PostForm($image->getCaptchaImage());
        $form->get('send')->setValue('修改');
        //绑定数据
        $form->bind($post);
    
        $request = $this->getRequest();
        if($request->isPost()){            
            $form->setInputFilter($post->getInputFilter());
            $form->setData($request->getPost());
    
            if($form->isValid()){
                    //过滤script标签       
                $post->content=preg_replace('{<script(.*?)>(.*?)</script>}', '$2', $post->content);
                $post->image = $this->imageFilter($post->content);             
                
                    //因为使用了bind，所以不用显式回传数据给$post，它会自动将数据回传给$post
                    //我们要做的只是将数据写入数据库即可。
                $this->getPostTable()->saveData($post);
    
                return $this->redirect()->toRoute('blog');
            }
        }
    
        return array(
            'id' => $id,
            'form' => $form,
        );
    }
    
    public function deleteAction()
    {
        $id=(int)$this->params()->fromRoute('id',0);
        if(!$id){
            return $this->redirect()->toRoute('blog');
        }
    
        $request=$this->getRequest();
        if($request->isPost()){
            $del=$request->getPost('del','No');
    
            if($del == 'Yes'){
                $id=(int)$request->getPost('id');
                $this->getPostTable()->deleteData($id);
            }
            return $this->redirect()->toRoute('blog');
        }
        return array(
            'id' => $id,
            'post' => $this->getPostTable()->getRow($id),
        );
    }
    
    public function getPostTable()
    {
        if(!$this->postTable){
            $sm = $this->getServiceLocator();
            $this->postTable = $sm->get('Blog\Model\PostTable');
        }
        return $this->postTable;
    }
    
    public function getFiles($basepath)
    {
        $iterator = new \DirectoryIterator($basepath);
        $files = array();
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getFilename();
            }
        }
        return $files;
    }
    
    protected function imageFilter($content)
    {
        preg_match('/<img.*?src=[\'\"](.*?)[\'\"]/', $content,$matches);
        $image = isset($matches[1])?$matches[1]:null;
        if(empty($image)){
            $image = '/images/default.png';
        }
        
          //过滤操作
        $filter = new \Zend\Filter\StripTags();
        $image = $filter->filter($image);//过滤html标签
        $image = preg_replace('/[ ]+/', '', $image);//过滤空格
    
          //提取文件名
        $files=$this->getFiles(ROOT_PATH."/public/images/uploads");
        $pitch = preg_replace('/\/(.*)\//', '', $image);
        
        //检测image是否是上传文件
        if(!in_array($pitch, $files)){
            $image = '/images/default.png';
        }
        return $image;
    }
}