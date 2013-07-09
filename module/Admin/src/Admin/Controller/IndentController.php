<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\Indent;
use module\Application\src\Model\Tool;
use Admin\Model\User;


/**
 * @todo 订单管理类
 * 
 * @author
 * @version 
 */
class IndentController extends AbstractActionController
{
	private $user;
	
    function __construct()
	{
	   $this->user=Tool::getSession('auth','user');	
	  
	}
	/**
	 * The default action - show the home page
	 */
    public function indexAction()
    {
        if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
        $viewData=array();
        $success = Tool::getCookie('success');
        if ($success)
        {
        	$viewData['success']=json_decode($success);
        }
        $page = $this->params('page',1);
        $s = $this->params()->fromQuery('s',2);
        $viewData['s']=$s;
        if ($s==1)
        {
        	$s = "'1','4'";
        }
        $viewData['user']=$this->user;
        $adapter=$this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $user = new User($adapter);
        $row = $user->getUser($this->user->id);
        $userShop=false;
        if ($row['shop'])
        {
        	$userShop = "'".implode("','",json_decode($row['shop']))."'";
        }
        $db = new Indent($adapter);
        $viewData['rows']= $db->indentList($page, $this->user,$userShop,$s);
        $viewData['status']=$db->indentStatus();
        return $viewData;
    }
    /**
     * @todo 编辑订单
     * @return Ambigous <\module\Application\src\Model\Ambigous, boolean, unknown>
     */
    public function editAction()
    {
        if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
        $id = (int)$this->params()->fromQuery('id');
        if (empty($id)) $this->redirect()->toRoute('indent');
        $adapter=$this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $db = new Indent($adapter);
        $row=$db->getIndent($id);
        if ($row['uid']!=$this->user->domain) $this->redirect()->toUrl('/indent');
        $request = $this->getRequest();
        if ($request->isPost())
        {
        	$postData = $request->getPost();
        	$data['express']=$postData['express'];
        	$data['waybill']=$postData['waybill'];
        	$data['status']=$postData['status'];
        	$data['content']=Tool::filter($postData['content'],true);
        	$res=$db->update($data,$id);
        	if ($res)
        	{
        	    Tool::setCookie('success', array('title'=>'操作成功','message'=>'修改订单成功'),time()+3);
        	    $this->redirect()->toRoute('indent');
        	}
        }
        
        $viewData['row']=$row;
        $viewData['status']=$db->indentStatus();
        $viewData['express']=$db->express();
        $viewData['user']=$this->user;
        return $viewData;
    }
    
    public function deleteAction()
    {
    	$domain = Tool::domain();
        $id = (int)$this->params()->fromQuery('id');
    	$indent = new Indent($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
    	$row=$indent->getIndent($id);
    	if ($id && $this->user->power ==2 && $row['uid']==sha1($domain))
    	{
    	    $indent->update(array('display'=>'0'),$id);
    	    exit('{"isok":true}');
    	}
        exit('{"isok":false}');
    }
}