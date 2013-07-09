<?php
namespace Admin\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Model\User;
use module\Application\src\Model\Tool;
use module\Application\src\Model\Alipay;

class UsersController extends AbstractActionController{
	private $viewData=array();
	function __construct(){
		$this->user=Tool::getSession('auth','user');
		$this->viewData['user']=$this->user;
	}
	/**
	 * @todo 账号列表
	 * (non-PHPdoc)
	 * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
	 */
	function indexAction()
	{
	    if (!$this->user || $this->user->power < 2) $this->redirect()->toUrl('/login');
		$success = Tool::getCookie('success');
		if ($success)
		{
		    $this->viewData['success']=json_decode($success);
		}
		
		$page=$this->params('page',1);
		$adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
		$db=new User($adapter);
		$this->viewData['rows']=$db->userList($page,$this->user);
		return $this->viewData;
	}
	/**
	 * @todo 编辑账号
	 * @return multitype:mixed Ambigous <\Admin\Model\Ambigous, boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown>
	 */
	function editAction()
	{
	    $success = Tool::getCookie('success');
	    if ($success)
	    {
	    	$this->viewData['success']=json_decode($success);
	    }
	    $error=Tool::getCookie('error');
	    if ($error)
	    {
	    	$this->viewData['error']=json_decode($error);
	    }
	    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
	    $db=new User($adapter);
	    $id = (int)$this->params()->fromQuery('id');
	    if (empty($id)) $this->redirect()->toUrl('/admin');
	    $request=$this->getRequest();
	    $row=$db->getUser($id);
	    if ($row['domain'] != $this->user->domain && $this->user->power<3) $this->redirect()->toRoute('admin');
	    if ($request->isPost())
	    {
	    	$postData=$request->getPost();
	    	$data=array();
	    	if ($row['id']>1)
	    	{
	    	    $data['power']=$postData['power'];
	    	}
	    	if ($this->user->power==3)
	    	{
	    	    if ($this->user->power==3)
	    	    {
	    	    	$data['shopCount']=(int)$postData['shopCount'];
	    	    }
	    	    if(!$row['domain'])
	    	    {
	    	        $data['domain'] = sha1($row['username']);
	    	        $data['token'] = Tool::random(20);
	    	    }
	    	}
	    	$password = trim($postData['password']);
	    	if ($password)
	    	{
	    	    $data['password']=sha1($password);
	    	}
	    	
	    	$realname=Tool::filter($postData['realname']);
	    	if ($realname)
	    	{
	    	    $data['realname']=$realname;
	    	}
	    	$data['email']=Tool::filter($postData['email']);
	    	$data['tel']=Tool::filter($postData['tel']);
	    	
	    	if ($this->user->power>=2)
	    	{
	    		$username = Tool::filter($postData['username'],true);
	    		if ($username )
	    		{
	    			$data['username']=$username;
	    		}
	    		$data['validity']=strtotime($postData['validity']);
	    	}
	    	
	    	
	    	
	    	if($db->editUser($id, $data))
	    	{
	    	    Tool::setCookie('success', array('title'=>'编辑成功','message'=>"编辑账号成功"),time()+5);
	    	    $this->redirect()->toUrl("/users/edit?id={$id}");
	    	}
	    	Tool::setCookie('error', array('title'=>'编辑失败','message'=>'编辑失败，写入数据失败'),time()+5);
	    }
	    
	    if ($row['city'])
	    {
	    	$this->viewData['city']=$db->areas($row['city']);
	    }
	    $this->viewData['areas']=$db->areas();
	    $this->viewData['row']=$row;
		$this->viewData['asset']=array('js'=>array('/lib/users.js'));
	    return $this->viewData;
	}
	/**
	 * @todo 创建账号
	 */
	function createAction()
	{
	    if ($this->user->power < 2) $this->redirect()->toUrl('/admin');
	    $success = Tool::getCookie('success');
	    if ($success)
	    {
	    	$this->viewData['success']=json_decode($success);
	    }
	    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
	    $db=new User($adapter);
	    $request=$this->getRequest();
		if ($request->isPost())
		{
		    $postData=$request->getPost();
			$data=array();
			if ($this->user->power==3)
			{
			    $data['domain']=strtolower(Tool::filter($postData['domain'],true));
			    $data['token'] = Tool::random(20);
			    $data['shopCount']=(int)$postData['shopCount'];
			}
			if ($this->user->power==2){
			    $data['domain']=$this->user->domain;
			    $data['uid'] = $this->user->id;
			}
			$data['username']=Tool::filter($postData['username'],true);
			$data['realname']=Tool::filter($postData['realname']);
			$data['password']=sha1($postData['password']);
			$data['email']=Tool::filter($postData['email']);
			$data['tel']=Tool::filter($postData['tel']);
			$data['validity']=strtotime($postData['validity']);
			$data['addtime']=time();
			$data['power'] = $this->user->power == 3 ? 2 : 1;
			$tid=$db->addUser($data);
			if($tid)
			{
			    Tool::setCookie('success', array('title'=>'添加成功','message'=>"成功添加账号"),time()+5);
			    $this->redirect()->toRoute('users');
			}
		}
		$this->viewData['asset']=array('js'=>array('/lib/users.js'));
		$this->viewData['areas']=$db->areas();
		return $this->viewData;
	}
	
	function configAction()
	{
	    if (!isset($this->user->id)) $this->redirect()->toUrl('/login');
	    $success = Tool::getCookie('success');
	    if ($success)
	    {
	    	$this->viewData['success']=json_decode($success);
	    }
	    $adapter=$this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
	    $user=new User($adapter);
	    $request = $this->getRequest();
	    if ($request->isPost())
	    {
	        $postData = $request->getPost();
            $data = array();
            $data['sitename'] = Tool::filter($postData['sitename']);
            $data['realname'] = Tool::filter($postData['realname']);
            $data['email'] = $postData['email'];
            $data['tel'] = $postData['tel'];
            $data['city'] = $postData['city'];
            $data['area'] = $postData['area'];
            $data['address'] = Tool::filter($postData['address']);
            $data['wc'] = $postData['wc'];
            $data['welcome'] = Tool::filter($postData['welcome']);
            $data['nodata'] = Tool::filter($postData['nodata']);
            $data['PID'] = $postData['PID'];
            $data['KEY'] = $postData['KEY'];
            $data['alipayEmail'] = $postData['alipayEmail'];
            $user->editUser($this->user->id, $data);
            Tool::setCookie('success', array('title'=>'编辑成功','message'=>"编辑信息成功"),time()+5);
            $this->redirect()->toUrl('/users/config');
	    }
	    
	    $row = $user->getUser($this->user->id);
	    $row['welcome']=htmlspecialchars_decode(stripcslashes($row['welcome']));
	    if ($row['city'])
	    {
	    	$this->viewData['city']=$user->areas($row['city']);
	    }
	    $this->viewData['areas']=$user->areas();
	    $this->viewData['row']=$row;
	    return $this->viewData;
	}
	
	
	/**
	 * @todo 删除账号
	 */
	function deleteAction()
	{
	    $id = (int)$this->params()->fromQuery('id');
	    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
	    $db = new User($adapter);
	    $row = $db->getUser($id);
		if ($id && ($this->user->power==3 || $row['domain']==$this->user->domain))
		{
		    $db->delUser($id);
		    echo '{"isok":true}';
		}else 
		{
		    echo '{"isok":false}';
		}
		exit();
	}
	/**
	 * @todo Ajax 验证域名是否使用
	 */
	function clickAction(){
	    $request = $this->getRequest();
	    $postData = $request->getPost();
	    $domain = strtolower(Tool::filter($postData['domain'],true));
	    $db = new User($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
	    $row = $db ->clickDomain(sha1($domain));
	    if ($row)
	    {
	    	echo '{"isok":true}';
	    }else 
	    {
	    	echo '{"isok":false}';
	    }
	    exit();
	}
	/**
	 * @todo 验证用户用户名是否存在
	 */
	function clickuserAction()
	{
	    $request=$this->getRequest();
		if ($request->isPost())
		{
		    $data=$request->getPost();
		    $username =$data['username']; 
		    $db = new User($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		    $row=$db->getUser(null,$username);
		    if ($row)
		    {
		    	exit('{"isok":"true"}');
		    }
		    exit('{"isok":"false"}');
		}
	    exit('{"isok":"true"}');
	}
	
	/**
	 * @todo 注册账户
	 * @return \Zend\View\Model\ViewModel
	 */
	function signupAction()
	{
        $domain  = Tool::domain();
        if ($domain!='weixin')
        {
        	$d = Tool::getDomain();
        	$this->redirect()->toUrl("http://weixin.{$d}/users/signup");
        }
	    $request=$this->getRequest();
	    $viewData=array();
	    $success = Tool::getCookie('success');
	    if ($success)
	    {
	    	$viewData['success']=json_decode($success);
	    }
	    $error = Tool::getCookie('error');
	    if ($error)
	    {
	    	$viewData['error']=json_decode($error);
	    }
		if ($request->isPost())
		{
		    $db = new User($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		    $postData=$request->getPost();
		    $username=Tool::filter($postData['username'],true);
		    $row=false;
		    if ($username)
		    {
		    	$row=$db->getUser(null,$username);
		    	if ($row)
		    	{
		    	    Tool::setCookie('error', array('title'=>'登记失败','message'=>"登记账号失败,该用户已经存在，请换个再试"),time()+5);
		    	    $this->redirect()->toUrl("http://weixin.{$d}/users/signup");
		    	}
		    }
			$data['username']=$username;
			$data['realname']=Tool::filter($postData['realname'],true);
			$data['email']=Tool::filter($postData['email'],true);
			$data['tel']=Tool::filter($postData['tel'],true);
			$data['password']=sha1($data['password']);
			$data['addtime']=time();
			if (!$row)
			{
			    $res = $db->addUser($data);
			    if ($res)
			    {
			    	Tool::setCookie('success', array('title'=>'登记成功','message'=>"登记账号成功,稍后人工处理"),time()+5);
			    	$this->redirect()->toUrl("http://weixin.{$d}/users/signup");
			    }
			}
		}
	    $view=new ViewModel($viewData);
		$view->setTerminal(200);
		return $view;
	}
	/**
	 * @todo 支付测试
	 */
	function alipayAction()
	{
	    $domain = Tool::domain();
	    $user = new User($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		$uRow = $user->clickDomain(sha1($domain));
		$testCode = Tool::random(20);
	    $payData = array(
                'serialnumber' => $testCode,
                'title' => '测试支付',
                'sum' => 0.01,
                'body' => '测试支付',
                'PID' => $uRow['PID'],
                'KEY' => $uRow['KEY'],
                'alipayEmail' => $uRow['alipayEmail'],
		        'notify_url'=>BASE_URL."/index/alipay?action=ok&code={$testCode}", ////服务器异步通知页面路径
		        'return_url'=>BASE_URL."/index/alipay?action=ok&code={$testCode}?",///页面跳转同步通知页面路径(支付成功跳转)
		        'show_url'=>BASE_URL."/index/alipay",
            );
		$alipay = new Alipay();
		$alipay->pay($payData);
	}
}