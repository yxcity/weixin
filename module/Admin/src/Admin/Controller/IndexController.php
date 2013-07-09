<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Model\User;
use module\Application\src\Model\Tool;
class IndexController extends AbstractActionController
{
	public function indexAction()
	{
	    echo $this->params('domain');
	    $view =  new ViewModel();
	    $view->setTerminal(200);
	    return $view;
	}
	
	public function authAction()
	{
	   $viewData=array();
	   $domain=Tool::domain();
	   if (empty($domain)) $this->redirect()->toUrl('/error');
	   $adapter=$this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
       $user = new User($adapter);
       $row = $user->clickDomain(sha1($domain));
       if (!$row || !$row['power']) $this->redirect()->toUrl('/error');
	   $request=$this->getRequest();
       if ($request->isPost())
       {
           $message = '用户或密码错误';
           $data=$request->getPost();
           if (empty($data['username']))
           {
               $message = '请输入用户名';
           }
           if(empty($data['password']))
           {
               $message = '请输入密码';
           }
           if(empty($data['username']) && empty($data['password'])){
               $message = '请输入用户名和密码';
           } 
           if($data['username'] && $data['password'])
           {
               $res=$user->auth(array('username'=>$data['username'],'password'=>sha1($data['password'])));
               if ($res && $res->power >=1 && $res->domain==sha1($domain))
               {
                   Tool::setSession('auth',array('user'=>$res));
                   Tool::setCookie('auth', array('title'=>$res->realname,'message'=>'欢迎登陆系统'),time()+5);
                   $this->redirect()->toRoute('admin');
               }else 
               {
               	$message ='用户名或密码错误！';
               }
           }
           $viewData['hint']=array('title'=>'登陆失败','message'=>$message);
       }
	   $view =  new ViewModel($viewData);
	   $view->setTerminal(200);
	   return $view;
	}
	
	public function logoutAction()
	{
        unset($_SESSION['auth']);
        $this->redirect()->toUrl(BASE_URL."/login");
	    //exit();
	}
}
