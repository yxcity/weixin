<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use module\Application\src\Model\Tool;
use Admin\Model\Type;


/**
 * @todo 类型管理
 * 
 * @author
 * @version 
 */
class TypeController extends AbstractActionController
{
	private $user;
	private $adapter;
    public function __construct()
	{
		$this->user = Tool::getSession('auth','user');
	}
    
	/**
	 * The default action - show the home page
	 */
    public function indexAction()
    {
        if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
        $viewData=array();
        $viewData['user']=$this->user;
        $success = Tool::getCookie('success');
        if ($success)
        {
        	$viewData['success']=json_decode($success);
        }
        $page = $this->params('page',1);
        $adapter=$this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $db = new Type($adapter);
        $rows = $db->typeAll($this->user->domain);
        if ($rows)
        {
        	$tmpRows=array();
            foreach ($rows as $key=>$val) {
        		if ($val['pid']){
        			$tmpRows[$val['pid']]['next'][]=$val;
        		}else 
        		{
        		    $tmpRows[$val['id']]=$val;
        		}
        	}
        }
        unset($rows);
        $viewData['rows']=$tmpRows;
        $viewData['user']=$this->user;
        return $viewData;
    }
    /**
     * @todo 创建分类
     */
    public function createAction()
    {
    	if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
    	$viewData=array();
    	$viewData['user']=$this->user;
    	$adapter=$this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    	$request=$this->getRequest();
    	if ($request->isPost())
    	{
    		$postData = $request->getPost();
    		$data=array();
    		$data['pid']=$postData['pid'];
    		$data['name']=Tool::filter($postData['name'],true);
    		$data['domain']=$this->user->domain;
    		$data['display']=$postData['display'];
    		$db = new Type($adapter);
    		if ($db->addType($data))
    		{
    			Tool::setCookie('success', array('title'=>'添加成功','message'=>'已经成功添加分类'),time()+5);
    		    $this->redirect()->toRoute('type');
    		}
    	}
    	$t=new Type($adapter);
    	$rows=$t->typeAll($this->user->domain,'0');
    	$viewData['rows']=$rows;
    	$viewData['asset']=array('js'=>array('/lib/type.js'));
    	return $viewData;
    }
   /**
    * @todo 编辑分类
    * @return multitype:Ambigous <\Admin\Model\Ambigous, boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown>
    */
    public function editAction()
    {
        if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
        $viewData=array();
        $viewData['user']=$this->user;
        $id = $this->params()->fromQuery('id');
        if (empty($id)) $this->redirect()->toRoute('type');
        $adapter=$this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $db = new Type($adapter);
        $request = $this->getRequest();
        if ($request->isPost())
        {
        	$postData=$request->getPost();
        	$data=array();
        	$data['pid']=$postData['pid'];
        	$data['name']=Tool::filter($postData['name'],true);
        	$data['display']=$postData['display'];
        	if ($db->editType($id, $data))
        	{
        	    $db->editPid($id, array('pid'=>$postData['pid']));
        	    Tool::setCookie('success', array('title'=>'编辑成功','message'=>'已经成功编辑分类'),time()+5);
        	    $this->redirect()->toRoute('type');
        	}
        }
        $row=$db->getType($id);
        if ($row['domain'] != $this->user->domain) $this->redirect()->toRoute('type');
        $viewData['row']=$row;
        $rows=$db->typeAll($this->user->domain,'0');
        $viewData['rows']=$rows;
        $viewData['asset']=array('js'=>array('/lib/type.js'));
        return $viewData;
    }
    /**
     * @todo 删除分类
     */
    public function deleteAction()
    {
        if (!$this->user) $this->redirect()->toUrl('/login');
        $id = (int)$this->params()->fromQuery('id');
        if ($id)
        {
            $adapter=$this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $db = new Type($adapter);
            $row = $db->getType($id);
            if ($row['domain']!=$this->user->domain) $this->redirect()->toRoute('type');
        	if ($this->user->power >= 2)
        	{
        		$db->delete($id);
        		echo '{"isok":true}';
        		exit();
        	}
        }
        echo '{"isok":false}';
        exit();
    }
}