<?php
namespace ZF2AuthAcl\Utility;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;



class UserManager implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    } 
   
    public function getUserInfo($where = array(), $columns = array())
    {
        $users = $this->getServiceLocator()->get('UserTable');
        $userInfo = $users->getUsers($where, $columns);        
        return $userInfo;
    }
    
    public function setUser($setData)
    {
        $users = $this->getServiceLocator()->get('UserTable');
          
          //保存用户值
        $users->saveUser($setData);
          
          //得到user_id
        $where = array('account' => $setData['username']);
        $columns = array('account','id');
        $userInfo = $users->getUsers($where,$columns);
        $user_id = $userInfo[0]['id'];
        
          //得到role_id
        $role = $this->getServiceLocator()->get('RoleTable');
        $where = array('role_name' => 'admin');
        $roleInfo = $role->getUserRoles($where);
        $role_id = $roleInfo[0]['rid'];
        
          //写如user-role关系对应表中
        $data = array(
            'user_id' => $user_id,
            'role_id' => $role_id,
        );
        $user_role = $this->getServiceLocator()->get('UserRoleTable');
        $user_role->saveUserRole($data);
    }
    
    public function delUser($where = array())
    {
        if(0 == $where['id']){
            return false;
        }
        $users = $this->getServiceLocator()->get('UserTable');
        $user_role = $this->getServiceLocator()->get('UserRoleTable');
        $arr = array(
            'user_id' => (int)$where['id'],
        );
        if($users->delUser($where) && $user_role->delUserRole($arr)){
            return true;
        }
        return false;
    }
    
    public function updatePass($password,$where=array())
    {
        $users = $this->getServiceLocator()->get('UserTable');
        if($users->updatePassword($password,$where)){
            return true;
        }
        return false;
    }
}