<?php
namespace ZF2AuthAcl\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZF2AuthAcl\Form\LoginUpForm;
use ZF2AuthAcl\Form\FindpassForm;
use ZF2AuthAcl\Form\ResetpassForm;
use ZF2AuthAcl\Form\Filter\ResetpassFilter;
use ZF2AuthAcl\Form\Filter\LoginUpFilter;
use ZF2AuthAcl\Form\Filter\FindpassFilter;
use ZF2AuthAcl\Utility\UserPassword;
use Zend\Captcha\Image;
use Blog\Validate\ImageValidate;
use Zend\View\Model\JsonModel;
use ZF2AuthAcl\Utility\Smtp;
use Zend\Uri\Http;

class UserManagerController extends AbstractActionController
{
    public function indexAction()
    {     
        $this->_initForceSSL();
        $users = $this->getServiceLocator()->get('UserManager');
        return new ViewModel(array(
            'data' => $users->getUserInfo(),
        ));
    }
    
    public function addAction()
    {
        $this->_initForceSSL();
        
        //初始化UserManager
        $users = $this->getServiceLocator()->get('UserManager');
        
        $request = $this->getRequest();
       
        //设置上传表单
        $captcha = new Image();
        $image = new ImageValidate($captcha);
        $loginForm = new LoginUpForm('loginUp',$image->getCaptchaImage());
        $loginForm->get('submit')->setValue('注册');
        $loginForm->setInputFilter(new LoginUpFilter());
        
        if ($request->isPost()) {
            $data = $request->getPost();
            $loginForm->setData($data);
        
            if ($loginForm->isValid()) {
                $data = $loginForm->getData();
                if($data['password'] !== $data['new_pass']){
                    $this->flashMessenger()->addMessage(array(
                        'error' => '密码不一致，请重新输入！'
                    ));
                return $this->redirect()->tourl($_SERVER['REQUEST_URI']);
                }
        
                $userPassword = new UserPassword();
                $encyptPass = $userPassword->create($data['password']);
                $setData = array(
                    'username' => $data['account'],
                    'email' => $data['email'],
                    'password' => $encyptPass,
                    
                );
                $users->setUser($setData);
                
                return $this->redirect()->tourl('/admin');
            } else {
                $errors = $loginForm->getMessages();
            }
        }
        
        return new ViewModel(array(
            'loginForm' => $loginForm,
        ));
    }
    
    public function deluserAction()
    {
        $this->_initForceSSL();
        
        $receive = $this->getRequest()->getPost();
        if($receive['confirm'] == 'yes'){
            $users = $this->getServiceLocator()->get('UserManager');
            $arr = array(
                'id'=>(int)$receive['id'],
            );
            if($users->delUser($arr)){
                return new JsonModel(array(
                    'status' => true,
                    'message' => '删除用户成功！',
                ));
            }
        }
        return new JsonModel(array(
            'status' => false,
            'message' => '删除用户失败！',
        ));
    }
    
    public function findpassAction()
    {
        $this->_initForceSSL();
        $request = $this->getRequest();
         
        //设置上传表单
        $captcha = new Image();
        $image = new ImageValidate($captcha);
        $form = new FindpassForm('findpass',$image->getCaptchaImage());
        $form->get('submit')->setValue('提交');
        $form->setInputFilter(new FindpassFilter());
        
        if($request->isPost()){
            $data = $request->getPost();
            $form->setData($data);
            
            if($form->isValid()){
                $data = $form->getData();
                $users = $this->getServiceLocator()->get('UserManager');
                $result = $users->getUserInfo(array(
                    'email'=>$data['email'],
                ));
                if(count($result) == 0){
                    $this->flashMessenger()->addMessage(array(
                        'error' => '用户邮箱不存在！'
                    ));
                    return $this->redirect()->toUrl($_SERVER['REQUEST_URI']);
                }
                
                $email = $result['0']['email'];
                $account = $result['0']['account'];
                $user_ip = $_SERVER['REMOTE_ADDR'];
                $hashalgo = new UserPassword();
                $token = $hashalgo->create($account.$email.$user_ip);
                $token_time = time();
                $token_id = md5(time().mt_rand(0,2000));
                $cache = $this->serviceLocator->get('Cache');
                $cache->setItem($token_id,$token_time);
                $url = 'http://'.$_SERVER['HTTP_HOST'].'/admin/resetpass?email='.$email.'&token_id='.
                            $token_id.'&token='.$token;
                if($this->sendmail(date('Y-m-d H:i',$token_time), $email, $url)){
                    $this->flashmessenger()->addMessage(array(
                        'success' => '系统已向您的邮箱发送了一封邮件<br/>请登录到您的邮箱及时重置您的密码！'
                    ));
                    return $this->redirect()->toUrl($_SERVER['REQUEST_URI']);
                }
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
        ));
    }
    
    public function resetpassAction()
    {
        $this->_initForceSSL();
        //得到并验证email
        $email = $this->params()->fromQuery('email',null);
        if(empty($email)){
            return array(
                'status' => true,
                'message' => '邮箱地址为空！',
            );
        }
        $email = trim($email);
        $validator = new \Zend\Validator\EmailAddress();
        if(!$validator->isValid($email)){
            $message = $validator->getMessages();
            return array(
                'status' => true,
                'message' => $message,
            );
        }
        $email = preg_replace("/(\\'|".'\")+/', '', $email); //删除所有引号
        
        //检测token是否过期
        $token_id = $this->params()->fromQuery('token_id','abc');
        $cache = $this->serviceLocator->get('Cache');
        $token_time = $cache->getItem($token_id);
        if(empty($token_time)){
            return array(
                'status' => true,
                'message' => '重置密码链接已过期，请重新获取邮件',
            );
        }
        
        //比较token，获知是否为本人操作
        $token = $this->params()->fromQuery('token',null);
        $users = $this->serviceLocator->get('UserManager');
        $result = $users->getUserInfo(array(
            'email'=>$email
        ));
        @$email = isset($result['0']['email'])?$result['0']['email']:'';
        @$account = isset($result['0']['account'])?$result['0']['account']:'';
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $hashalgo = new UserPassword();
        $token_new = $hashalgo->create($account.$email.$user_ip);
        if($token !== $token_new){
            return array(
                'status' => true,
                'message' => '用户验证不通过，请确认是否为本人操作',
            );
        }
        
        //设置重命名表单
        $request = $this->getRequest();
        $captcha = new Image();
        $image = new ImageValidate($captcha);
        $form = new ResetpassForm('findpass',$image->getCaptchaImage());
        $form->get('submit')->setValue('提交');
        $form->setInputFilter(new ResetpassFilter());
        
        if($request->isPost()){
            $data = $request->getPost();
            $form->setData($data);
            
            if($form->isValid()){
                $data = $form->getData();
                
                    //比对输入密码是否一致
                if($data['password'] !== $data['new_pass']){
                    $this->flashMessenger()->addMessage(array(
                        'error' => '密码不一致，请重新输入！'
                    ));
                    return $this->redirect()->toUrl($_SERVER['REQUEST_URI']);
                }
                
                $encyptPass = $hashalgo->create($data['password']);
                $where = array(
                    'email' => $email
                );
                if($users->updatePass($encyptPass,$where)){
                    $this->flashMessenger()->addMessage(array(
                        'success' => '重置密码成功'
                    ));
                    return $this->redirect()->toRoute('admin');
                } else {
                    $this->flashMessenger()->addMessage(array(
                        'error' => '重置密码失败，请返回再试！'
                    ));
                    return $this->redirect()->toUrl($_SERVER['REQUEST_URI']);
                }
            }
        }
        return array(
            'form' => $form
        );
    }
    
    public function rpnexusAction()
    {
        $rpRelationArr = $this->getRPrelations();
        $roles = $this->getServiceLocator()
                      ->get('RoleTable')
                      ->getUserRoles();
        $rpRelationTab = $this->getServiceLocator()->get('RolePermissionTable');
        
        //检测是否存在admin账户，存在即返回，不存在则创建admin角色
        $is_exist = false;
        $adrole = '';
        foreach($roles as $role){
            if(in_array('admin', $role)){
                $is_exist = true;
                $adrole = $role['role_name'];
                break;
            }
        }
        if(!$is_exist){
            $role=$this->getServiceLocator()->get('RoleTable');
            $role->saveRole('admin');
            $adrole = 'admin';
        }
        
        //设置匹配表
        $result = $rpRelationTab->getRolePermissions();
        $compareArr = array();
        foreach($result as $array){
            array_shift($array);
            $val = array_values($array);
            $val = implode('-', $val);
            $compareArr[] = $val;
        }
        
        //写入所有资源-权限关系表到admin角色下
        foreach($rpRelationArr as $key => $value){
            foreach($value as $val){
                $tmpArray = array(
                    'role_name' => $adrole,
                    'resource_name' => $key,
                    'permission_name' => $val,
                );
                if(!in_array($adrole.'-'.$key.'-'.$val, $compareArr)){
                    $rpRelationTab->saveRolePermissions($tmpArray);
                }               
            }
        }        
        
        //接下来如果要实现个性化的角色权限设定，需要先配置好xml配置文件，然后从配置文件写入设定
        
        return array(
            'result' => $rpRelationTab->getRolePermissions()
        );
    }
    
    public function sendmail($time,$email,$url){
        $smtpserver = "smtp.163.com"; //SMTP服务器
        $smtpserverport = 25; //SMTP服务器端口
        $smtpusermail = "18829281448@163.com"; //SMTP服务器的用户邮箱,如xxx@163.com
        $smtpuser = "18829281448@163.com"; //SMTP服务器的用户帐号xxx@163.com
        $smtppass = "wj450324."; //SMTP服务器的用户密码
        $smtp = new Smtp($smtpserver, $smtpserverport, true, $smtpuser, $smtppass); //这里面的一个true是表示使用身份验证,否则不使用身份验证.
        
        $emailtype = "HTML"; //信件类型，文本:text；网页：HTML
        $smtpemailto = $email;
        $smtpemailfrom = $smtpusermail;
        $emailsubject = "Myblog.web - 找回密码"; //邮件标题
        $emailbody = "亲爱的".$email."：<br/>您在".$time."提交了找回密码请求。请点击下面的链接重置密码（按钮1小时内有效）。<br/><a href='".$url."' target='_blank'>".$url."</a><br/>如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问。<br/>如果您没有提交找回密码请求，请忽略此邮件。";
        $rs = $smtp->sendmail($smtpemailto, $smtpemailfrom, $emailsubject, $emailbody, $emailtype);
        return $rs;
    }
    
    public function getRPrelations()
    {
        $manager = $this->getServiceLocator()->get('ModuleManager');
        $modules = $manager->getLoadedModules();
        $loadedModules      = array_keys($modules);
        $skipActionsList    = array('notFoundAction', 'getMethodFromAction');
    
        $relationArr = array();
        foreach ($loadedModules as $loadedModule) {
            $moduleClass = '\\' .$loadedModule . '\Module';
            $moduleObject = new $moduleClass;
            $config = $moduleObject->getConfig();
    
            $controllers = $config['controllers']['invokables'];
            foreach ($controllers as $key => $moduleClass) {
                $tmpArray = get_class_methods($moduleClass);
                $controllerActions = array();
                foreach ($tmpArray as $action) {
                    if (substr($action, strlen($action)-6) === 'Action' && !in_array($action, $skipActionsList)) {
                        $action = preg_replace('/(Action)$/', '', $action);
                        $controllerActions[] = $action;
                    }
                }
                $relationArr[$moduleClass] = $controllerActions;
            }
        }
        return $relationArr;
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