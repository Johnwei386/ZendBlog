<?php
namespace Blog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    protected $postTable;
    
    public function indexAction()
    {
        $blogTable = $this->serviceLocator->get('Blog\Model\PostTable');
        $blogs = $blogTable->fetchAll(false,array('slide'=>'Y'));
        $mottoTable = $this->serviceLocator->get('Motto\Model\PostTable');
        $mottos = $mottoTable->fetchAll();
        $this->layout('blog/layout/main.phtml');
        $mottos = $mottos->toArray();
        return new ViewModel(array(
            'blogs' => $blogTable->fetchAll(false,array('slide'=>'Y')),
            'mottos' => $mottos,
            'blgcount' => count($blogs->toArray()),
        ));
    }
    
    public function classlistAction()
    {
        $category = $this->params()->fromRoute('category','');
        //grab the paginator from the blogTable
        $paginator = $this->getPostTable()->fetchAll(true,array(
            'classify' => $category
            
        ));
        //set the current page to what has been passed in query string
        //,or to set 1 if none set
        $paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page',1));
        //set the number of items per page
        $paginator->setItemCountPerPage(2);
        
          //返回格言
        $mottoTable = $this->serviceLocator->get('Motto\Model\PostTable');
        $mottos = $mottoTable->fetchAll();
        $mottos = $mottos->toArray();
        $index = mt_rand()%(count($mottos));
        return new ViewModel(array(
            'category' => $category,
            'posts' => $paginator,
            'motto' => $mottos[$index],
        ));
    }
    
    public function viewblogAction()
    {
        $id = (int)$this->params()->fromRoute('id',0);
        $blog = $this->getPostTable()->getRow($id);
          
          //返回格言
        $mottoTable = $this->serviceLocator->get('Motto\Model\PostTable');
        $mottos = $mottoTable->fetchAll();
        $mottos = $mottos->toArray();
        $index = mt_rand()%(count($mottos));
        return array(
            'blog' => $blog,
            'motto' => $mottos[$index],
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
}