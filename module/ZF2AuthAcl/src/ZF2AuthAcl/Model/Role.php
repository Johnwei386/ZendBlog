<?php
namespace ZF2AuthAcl\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class Role extends AbstractTableGateway
{

    public $table = 'role';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        $this->initialize();
    }
    
    public function getUserRoles($where = array(), $columns = array())
    {
        try {
            $sql = new Sql($this->getAdapter());
            $select = $sql->select()->from(array(
                'role' => $this->table
            ));
            $select->columns(array(
                'rid',
                'role_name',
                'status',
            ));
            $select->where('status = "Active"');
            if (count($where) > 0) {
                $select->where($where);
            }
            
            if (count($columns) > 0) {
                $select->columns($columns);
            }
            $statement = $sql->prepareStatementForSqlObject($select);
            $roles = $this->resultSetPrototype->initialize($statement->execute())
                ->toArray();
            return $roles;
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }
    
    public function saveRole($role_name)
    {
        try{
            $sql = new Sql($this->getAdapter());
            $insert = $sql->insert($this->table);
            
            $insert->columns(array('role_name'));
            $insert->values(array('role_name' => $role_name));
            $statement = $sql->prepareStatementForSqlObject($insert);
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
