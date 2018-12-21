<?php
namespace Blog\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class PostTable
{
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }
    
    public function fetchAll($paginated=false,$where=array(),$columns=array())
    {
        if($paginated){
            //create a new Select object for the table blog
            $select = new Select('blog');
            
            if (count($columns) > 0) {
                $select->columns($columns);
            }
            if (count($where) > 0) {
                $select->where($where);
            }
            
            $select->order("ctime DESC");
            //create a new result set based on the blog entity
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Post());
            //create a new pagination adapter object
            $paginatorAdapter = new DbSelect(
                //our configured select object
                $select, 
                //the adapter to run it against
                $this->tableGateway->getAdapter(),
                //the result set to hydrate
                $resultSetPrototype
                );
            $paginator = new Paginator($paginatorAdapter);
            return $paginator;
        }
        $resultSet = $this->tableGateway->select(function(Select $select)use ($where){
            $select->where($where);
            $select->order('ctime DESC');
        });
        return $resultSet;
    }
    
    public function getRow($id)
    {
        try{
            $id = (int)$id;
            $rowset = $this->tableGateway->select(array('id'=>$id));
            $row = $rowset->current();
            if(!$row){
                return false;
            }
            return $row;
        } catch (\Exception $e){
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }
    
    public function saveData(Post $post)
    {
        $data = array(
            'title' => $post->title,
            'classify' => $post->classify,
            'ctime' => $post->ctime,
            'slide' => $post->slide,
            'image' => $post->image,
            'content' => $post->content,
        );
        
        $id = (int)$post->id;
        if($id == 0){
            $this->tableGateway->insert($data);
        }else{
            if($this->getRow($id)){
                $this->tableGateway->update($data,array('id'=>$id));
            }else{
                throw new \Exception('数据记录不存在');
            }
        }
    }
    
    public function deleteData($id)
    {
        $this->tableGateway->delete(array('id' => (int)$id));
    }
}