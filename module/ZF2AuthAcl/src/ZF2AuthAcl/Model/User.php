<?php
namespace ZF2AuthAcl\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class User extends AbstractTableGateway
{

    public $table = 'users';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet(ResultSet::TYPE_ARRAY);
        $this->initialize();
    }
    
    public function getUsers($where = array(), $columns = array())
    {
        try {
            $sql = new Sql($this->getAdapter());
            $select = $sql->select()->from(array(
                'user' => $this->table
            ));
            
            if (count($where) > 0) {
                $select->where($where);
            }
            
            if (count($columns) > 0) {
                $select->columns($columns);
            }
            
            //join()实现多个表查询，将多个表中的内容提取所需要素组合在一起输出
            $select->join(array('userRole' => 'user_role'), 'userRole.user_id = user.id', array('role_id'), 'LEFT');
            $select->join(array('role' => 'role'), 'role.rid = userRole.role_id', array('role_name'), 'LEFT');
            
            $statement = $sql->prepareStatementForSqlObject($select);
            $users = $this->resultSetPrototype->initialize($statement->execute())
                          ->toArray();
            
            return $users;
        } catch (\Exception $e) {
            throw new \Exception($e->getPrevious()->getMessage());
        }
    }
    
    public function saveUser($setData)
    {
        try{
            $sql = new Sql($this->getAdapter());
            $insert = $sql->insert('users');
            $insert->columns(array('account','email','password','created_on'));
            $date=date('Y-m-d H:i:s');
            
            $insert->values(array(
                'account' => $setData['username'],
                'email' => $setData['email'],
                'password' => $setData['password'],
                'created_on' => $date,
                
            ));
            
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
        }catch (\Exception $e){
            throw new \Exception($e->getPrevious()->getMessage());
        }       
    }
    
    public function delUser($where = array())
    {
        try{
            $sql = new Sql($this->getAdapter());
            $delete = $sql->delete('users');
            if(count($where) > 0){
                $delete->where($where);
            }
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
    
    public function updatePassword($password,$where = array())
    {
        try{
            $sql = new Sql($this->getAdapter());
            $update = $sql->update('users');
            
            if(count($where) > 0){
                $update->where($where);
            }
            $update->set(array(
                'password' => $password,
            ));
            $statement = $sql->prepareStatementForSqlObject($update);
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
