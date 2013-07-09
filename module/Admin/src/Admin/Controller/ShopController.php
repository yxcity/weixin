<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use module\Application\src\Model\Tool;
use Admin\Model\Shop;
use Admin\Model\User;


/**
 * @todo 门店管理类
 * 
 * @author
 * @version 
 */
class ShopController extends AbstractActionController
{
	private $user;
	private $viewData=array();
    
    public function __construct()
    {
    	$this->user = Tool::getSession('auth','user');
    	$this->viewData['user']=$this->user;
    }
    /**
     * @todo 门店列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
        
        if ($this->user->power!=2) $this->redirect()->toRoute('admin');
        
        $adapter=$this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $request = $this->getRequest();
        
        //编辑门店管理员
        $action= $this->params()->fromQuery('action',null);
        if ($action=='m')
        {
        	$this->viewData['action'] = $action;
        	$id = $this->params()->fromQuery('id');
        	$this->viewData['id']=$id;
        	$user = new User($adapter);
        	$row=$user->getUser($id);
        	if ($row['uid'] != $this->user->id) $this->redirect()->toRoute('users');
        	$userShop=array();
        	if ($row['shop'])
        	{
        		$userShop=json_decode($row['shop']);
        	}
        	$this->viewData['userShop']=$userShop;
        }
        
        $request = $this->getRequest();
        if ($request->isPost())
        {
        	$data=$request->getPost();
        	$shop = $data['id']?json_encode($data['id']):null;
        	$user->editUser($id, array('shop'=>$shop));
        	Tool::setCookie('success', array('title'=>'操作成功','message'=>"编辑门店管理员成功"),time()+2);
        	$this->redirect()->toUrl("/shop?action=m&id={$id}");
        }
        
        $success = Tool::getCookie('success');
        if ($success)
        {
        	$this->viewData['success']=json_decode($success);
        }
        $page=$this->params('page',1);
        $db = new Shop($adapter);
        $rows = $db->shopList($page,$this->user);
        $this->viewData['rows']=$rows;
        $userDB=new User($adapter);
        $this->viewData['userData']=$userDB->getUser($this->user->id);
        $this->viewData['count'] = $db->shopCount($this->user->domain);
        return $this->viewData;
    }
   /**
    * @todo 创建门店
    */ 
    public function createAction()
    {
    	if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
    	if ($this->user->power!=2) $this->redirect()->toRoute('admin');
    	//判断一下是否可以
    	$db = new Shop($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        $request=$this->getRequest();
    	if ($request->isPost())
    	{
    		$postData = $request->getPost();
    		$data=array();
    		$file=$request->getFiles ()->toArray();
    		if ($file && is_array($file))
    		{
    		    $thumb = Tool::uploadfile($file);
    		    if ($thumb['res'])
    		    {
    		        $data['thumb']=$thumb['file'];
    		    }
    		}
    		$images=isset($postData['images'])?$postData['images']:null;
    		if ($images)
    		{
    		    $data['images']=serialize($images);
    		}
    		$data['uid']=$this->user->domain;
    		$data['shopname']=Tool::filter($postData['shopname']);
    		$data['address']=Tool::filter($postData['address']);
    		$data['tel'] = Tool::filter($postData['tel'],true);
    		$data['content']=Tool::filter($postData['content']);
			$data['province']=Tool::filter($postData['province']);
			$data['city']=Tool::filter($postData['city']);
			$data['locationX']=Tool::filter($postData['locationX']);
			$data['locationY']=Tool::filter($postData['locationY']);
    		$data['addtime']=time();
    		if ($db->addShop($data))
    		{
    		    Tool::setCookie('success', array('title'=>'添加成功','message'=>"成功添加门店"),time()+5);
    		    $this->redirect()->toRoute('shop');
    		}
    	}
    	$this->viewData['asset']=array('css'=>array('/lib/uploadify/uploadify.css'),'js'=>array('/lib/uploadify/jquery.uploadify.min.js','/lib/shop.js','/ueditor/ueditor.all.min.js','/ueditor/ueditor.config.js'));
    	return $this->viewData;
    }
    /**
     * @todo 编辑门店
     * @return multitype:Ambigous <\Admin\Model\Ambigous, boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown>
     */
    public function editAction()
    {
        if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
        if ($this->user->power!=2) $this->redirect()->toRoute('admin');
        $id=(int)$this->params()->fromQuery('id');
        if (empty($id)) $this->redirect()->toRoute('shop');
        $db = new Shop($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        $request = $this->getRequest();
        if ($request->isPost())
        {
            $postData = $request->getPost();
            $data=array();
            $file=$request->getFiles ()->toArray();
            if ($file && is_array($file))
            {
            	$thumb = Tool::uploadfile($file);
            	if ($thumb['res'])
            	{
            		$data['thumb']=$thumb['file'];
            	}
            	$images=isset($postData['images'])?$postData['images']:null;
            	if ($images)
            	{
            		$data['images']=serialize($images);
            	}
            	$data['shopname']=Tool::filter($postData['shopname']);
            	$data['address']=Tool::filter($postData['address']);
            	$data['tel'] = Tool::filter($postData['tel'],true);
            	$data['content']=Tool::filter($postData['content']);
				$data['province']=Tool::filter($postData['province']);
			    $data['city']=Tool::filter($postData['city']);
			    $data['locationX']=Tool::filter($postData['locationX']);
			    $data['locationY']=Tool::filter($postData['locationY']);
			   
            	if ($db->editShop($id, $data))
            	{
            	    Tool::setCookie('success', array('title'=>'编辑成功','message'=>"编辑门店成功"),time()+5);
            	    //$this->redirect()->toRoute('shop');
            	}
            	Tool::setCookie('error', array('title'=>'编辑失败','message'=>'编辑失败，写入数据失败'),time()+5);
            }
        }
        $this->viewData['row']=$db->getShop($id);
        $this->viewData['asset']=array('css'=>array('/lib/uploadify/uploadify.css'),'js'=>array('/lib/uploadify/jquery.uploadify.min.js','/lib/shop.js','/ueditor/ueditor.all.min.js','/ueditor/ueditor.config.js'));
        return $this->viewData;
    }
   
    /**
     * @todo
     */
    public function deleteAction()
    {
        if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
        $id = (int)$this->params()->fromQuery('id');
        if ($id)
        {
            $db = new Shop($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
            $row = $db->getShop($id);
            if ($row['uid']==$this->user->domain && $this->user->power >= 2)
            {
                $db->deleteShop($id);
                echo '{"isok":true}';
                exit();
            }
        }
        echo '{"isok":false}';
        exit();
    }
}