<?php
namespace ZF2AuthAcl\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZF2AuthAcl\Form\LoginForm;
use ZF2AuthAcl\Form\Filter\LoginFilter;
use ZF2AuthAcl\Utility\UserPassword;
use Zend\Session\Container;
use Zend\Captcha\Image;
use Blog\Validate\ImageValidate;

class IndexController extends AbstractActionController
{    
    public function indexAction()
    {   
        $this->_initForceSSL();
        
        $request = $this->getRequest();        
        $view = new ViewModel();
        
        $captcha = new Image();
        $image = new ImageValidate($captcha);
        $loginForm = new LoginForm('loginForm',$image->getCaptchaImage());
        $loginForm->get('submit')->setValue('登录');
        $loginForm->setInputFilter(new LoginFilter());
        
        if ($request->isPost()) {
            $data = $request->getPost();
            $loginForm->setData($data);
            
            if ($loginForm->isValid()) {
                $data = $loginForm->getData();
                
                $userPassword = new UserPassword();
                $encyptPass = $userPassword->create($data['password']);
                
                $authService = $this->getServiceLocator()->get('AuthService');
                
                $authService->getAdapter()
                    ->setIdentity($data['account'])
                    ->setCredential($encyptPass);
                
                $result = $authService->authenticate();
                
                if ($result->isValid()) {
                    
                    $userDetails = $this->_getUserDetails(array(
                        'account' => $data['account']
                    ), array(
                        'id'
                    ));
                    
                    $session = new Container('User');
                    $session->offsetSet('account', $data['account']);
                    $session->offsetSet('userId', $userDetails[0]['id']);
                    $session->offsetSet('roleId', $userDetails[0]['role_id']);
                    $session->offsetSet('roleName', $userDetails[0]['role_name']);
                    
                    $this->flashMessenger()->addMessage(array(
                        'success' => '登录成功！'
                    ));
                    // Redirect to page after successful login
                    $this->redirect()->toRoute('home');
                } else {
                    $this->flashMessenger()->addMessage(array(
                        'error' => '认证失败！'
                    ));
                    // Redirect to page after login failure
                }
                return $this->redirect()->tourl('/login');
                // Logic for login authentication
            } else {
                $errors = $loginForm->getMessages();
                // prx($errors);
            }
        }
        
        $view->setVariable('loginForm', $loginForm);
        return $view;
    }

    public function logoutAction()
    {
        $authService = $this->getServiceLocator()->get('AuthService');
        
        $session = new Container('User');
        $session->getManager()->destroy();
        
        $authService->clearIdentity();
        return $this->redirect()->toUrl('/login');
    }
    
    private function _getUserDetails($where = array(), $columns = array())
    {
        $userTable = $this->getServiceLocator()->get("UserTable");
        $users = $userTable->getUsers($where, $columns);
        return $users;
    }
    
    protected function _initForceSSL() {
        $request = $this->getRequest();
         
        if ('https' === $request->getUri()->getScheme()) {
            return;
        }
    
        $url    = $request->getUri();
        $url->setScheme('https');
        return $this->redirect()->toUrl($url);
    }
}