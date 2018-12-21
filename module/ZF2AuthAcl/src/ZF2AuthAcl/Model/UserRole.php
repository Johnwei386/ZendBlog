<?php
namespace ZF2AuthAcl\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class UserRole extends AbstractTableGateway
{

    public $table = 'user_role';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        $this->initialize();
    }
    
    public function getUserRoles($where = array(), $columns = array(), $orderBy = '')
    {
        try {
            $sql = new Sql($this->getAdapter());
            $select = $sql->select()->from(array(
                'sa' => $this->table
            ));
            
            if (count($where) > 0) {
                $select->where($where);
            }
                      
            if (count($columns) > 0) {
                $select->columns($columns);
            }
            
            if (! empty($orderBy)) {
                $select->order($orderBy);
            }
                        
         $statement = $sql->prepareStatementForSqlObject($select);
                
         $clients = $this->resultSetPrototype->initialize($statement->execute())
                  ->toArray();
                
         return $clients;
                
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }
    
    public function saveUserRole($data)
    {
        try{
            $sql = new sql($this->getAdapter());
            $insert = $sql->insert('user_role');
            
            $insert->columns(array('user_id','role_id'));
            $insert->values(array(
                'user_id' => $data['user_id'],
                'role_id' => $data['role_id']
            ));
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
        }catch (\Exception $e){
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }
    
    public function delUserRole($where = array())
    {
        try{
            $sql = new sql($this->getAdapter());
            $delete = $sql->delete('user_role');
            $delete->where($where);
            
            $statement = $sql->prepareStatementForSqlObject($delete);
            $result = $statement->execute();
            if($result->getAffectedRows() > 0){
                return true;
            }            
        }catch (\Exception $e){
            throw new \Exception($e->getPrevious()->getMessage());
        }
        return false;
    }
    
}
