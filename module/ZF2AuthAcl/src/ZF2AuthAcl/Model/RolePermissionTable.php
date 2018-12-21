<?php
namespace ZF2AuthAcl\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class RolePermissionTable extends AbstractTableGateway
{

    public $table = 'role_permission';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        $this->initialize();
    }

    public function getRolePermissions($where=array(),$columns=array())
    {
        $sql = new Sql($this->getAdapter());
        
        $select = $sql->select($this->table);
        if(count($where) > 0){
            $select->where($where);
        }
        if(count($columns) > 0){
            $select->columns($columns);
        }
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $this->resultSetPrototype->initialize($statement->execute())
                       ->toArray();
        return $result;
    }
    
    public function saveRolePermissions($data)
    {
        try{
            $sql = new Sql($this->getAdapter());
            $insert = $sql->insert($this->table);
            $insert->columns(array(
                'role_name',
                'resource_name',
                'permission_name',
            ));
            $insert->values(array(
                'role_name' => $data['role_name'],
                'resource_name' => $data['resource_name'],
                'permission_name' => $data['permission_name'],            
            ));
            $statement=$sql->prepareStatementForSqlObject($insert);
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
