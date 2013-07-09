<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use module\Application\src\Model\Tool;
use Admin\Model\Commodity;
use Admin\Model\Shop;
use Admin\Model\Type;
use Admin\Model\User;

/**
 * @todo 商品管理类
 * @author
 * @version
 */
class CommodityController extends AbstractActionController
{

    private $user;

    function __construct ()
    {
        $this->user = Tool::getSession('auth','user');
    }

    function indexAction ()
    {
        if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
        $viewData = array();
        $success = Tool::getCookie('success');
        if ($success)
        {
        	$viewData['success']=json_decode($success);
        }
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $db =  new Commodity($adapter);
        $request = $this->getRequest();
        if($request->isPost())
        {
        	$postData = $request->getPost();
        	if ($postData['id'])
        	{
        		foreach ($postData['id'] as $val) {
        			$db->editCommodity($val, array('welcome'=>1));
        		}
        		Tool::setCookie('success', array('title'=>'操作成功','message'=>'设置欢迎商品成功'),time()+5);
        		$this->redirect()->toUrl('/commodity/welcome/');
        	}
        }
        $viewData['user']=$this->user;
        $page=$this->params('page');
        $viewData['rows']=$db->commodityList($page, $this->user);
        //取出门店
        $shopRows = new Shop($adapter);
        $shopRows= $shopRows->userShop($this->user->domain,$num=1000);
        $shop=array();
        if ($shopRows)
        {
        	foreach ($shopRows as $val) {
        		$shop[$val['id']]=$val['shopname'];
        	}
        }
        $viewData['shop']=$shop;
        
        //取得所有分类
        $typeRows=new Type($adapter);
        $typeRows = $typeRows->typeAll($this->user->domain);
        $type=array();
        if ($typeRows)
        {
        	foreach ($typeRows as $val) {
        		$type[$val['id']]=$val['name'];
        	}
        }
        $viewData['type']=$type;
        $viewData['action']=$this->params()->fromQuery('action');
        return $viewData;
    }

    function createAction ()
    {
        if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
        $viewData=array();
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $userShop = array();
        if ($this->user->power==1)
        {
            $user = new User($adapter);
            $row= $user->getUser($this->user->id);
            
            if ($row['shop'])
            {
                $userShop = json_decode($row['shop']);
            }
        }
        $viewData['userShop'] = $userShop;
        $viewData['user']=$this->user;
        $request = $this->getRequest();
        $db  = new Commodity($adapter);
        if ($request->isPost()) {
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
            $data['shop'] = $postData['shop'];
            $data['uid'] = $this->user->domain;
            $data['cateID'] = $postData['cateID'];
            $data['name'] = Tool::filter($postData['wares'],true);
            $data['images'] = isset($postData['images'])?serialize($postData['images']):null;
            $data['price'] = Tool::filter($postData['price'],true);
            $data['rebate'] = Tool::filter($postData['rebate'],true);
            $data['order'] = $postData['order'] ? Tool::filter($postData['order'], true) : null;
            $data['repertory'] = $postData['repertory'] ? Tool::filter($postData['repertory'], true) : null;
            $data['sold'] = $postData['sold'] ? Tool::filter($postData['sold'], true) : null;
            $data['added'] = $postData['added'];
            $data['commend'] = $postData['commend'];
            $data['weixin'] = Tool::filter($postData['weixin']);
            $data['content'] = Tool::filter($postData['content']);
            if ($db ->addCommodity($data)){
            	Tool::setCookie('success', array('title'=>'添加成功','message'=>'成功添加商品'),time()+5);
            	$this->redirect()->toRoute('commodity');
            }
        }
        $shopDB=new Shop($adapter);
        $viewData['shop']=$shopDB->shopAll($this->user->domain);
        $typeDB=new Type($adapter);
        $viewData['type']=$typeDB->typeAll($this->user->domain);
        $viewData['alipay']=isset($this->user->alipay)?1:0;
        $viewData['asset']=array('css'=>array('/lib/uploadify/uploadify.css'),'js'=>array('/lib/uploadify/jquery.uploadify.min.js','/lib/commodity.js','/ueditor/ueditor.all.min.js','/ueditor/ueditor.config.js'));
        return $viewData;
    }

    function editAction ()
    {
        if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
        $viewData=array();
        $viewData['user']=$this->user;
        $id = $this->params()->fromQuery('id');
        if (empty($id)) $this->redirect()->toRoute('commodity');
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $db  = new Commodity($adapter);
        $row=$db->getCommodity($id);
        if ($this->user->domain != $row['uid'] && $this->user->power<3) $this->redirect()->toRoute('commodity');
        //门店管理员
        $userShop = array();
        if ($this->user->power==1)
        {
        	$user = new User($adapter);
        	if ($user['shop'])
        	{
        		$userShop = json_decode($user['shop']);
        	}
        }
        $viewData['userShop'] = $userShop;
        
        $request = $this->getRequest();
        if ($request->isPost()) {
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
            $data['shop'] = $postData['shop'];
            //$data['uid'] = $this->user->id;
            $data['cateID'] = $postData['cateID'];
            $data['name'] = Tool::filter($postData['wares'],true);
            $data['price'] = Tool::filter($postData['price'],true);
            $data['rebate'] = Tool::filter($postData['rebate'],true);
            $data['order'] = $postData['order'] ? Tool::filter($postData['order'], true) : null;
            $data['repertory'] = $postData['repertory'] ? Tool::filter($postData['repertory'], true) : null;
            $data['sold'] = $postData['sold'] ? Tool::filter($postData['sold'], true) : null;
            $data['images'] = isset($postData['images'])?serialize($postData['images']):null;
            $data['added'] = $postData['added'];
            $data['commend'] = $postData['commend'];
            $data['weixin'] = Tool::filter($postData['weixin']);
            $data['content'] = Tool::filter($postData['content']);
        	if ($db ->editCommodity($id,$data)){
        		Tool::setCookie('success', array('title'=>'编辑成功','message'=>'成功编辑商品'),time()+5);
        		$this->redirect()->toRoute('commodity');
        	}
        }
       
       $viewData['row']=$row;
       $shopDB=new Shop($adapter);
       $viewData['shop']=$shopDB->shopAll($this->user->domain);
       $typeDB=new Type($adapter);
       $viewData['type']=$typeDB->typeAll($this->user->domain);
       $viewData['asset']=array('css'=>array('/lib/uploadify/uploadify.css'),'js'=>array('/lib/uploadify/jquery.uploadify.min.js','/lib/commodity.js','/ueditor/ueditor.all.min.js','/ueditor/ueditor.config.js'));
       return $viewData; 
    }
    /**
     * @ todo 欢迎商品
     */
    public function welcomeAction()
    {
        if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
        $viewData=array();
        $success = Tool::getCookie('success');
        if ($success)
        {
        	$viewData['success']=json_decode($success);
        }
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $db = new Commodity($adapter);
    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$postData = $request->getPost();
    		if ($postData['id'])
    		{
    			foreach ($postData['id'] as $val) {
    				$db->editCommodity($val, array('welcome'=>0));
    			}
    			Tool::setCookie('success', array('title'=>'操作成功','message'=>'取消欢迎商品成功'),time()+5);
    			$this->redirect()->toRoute('commodity',array('action'=>'welcome'));
    		}
    	}
    	
    	$viewData['user']=$this->user;
    	
    	$viewData['rows'] = $db->welcome($this->user->domain);
    	//取出门店
    	$shopRows = new Shop($adapter);
    	$shopRows= $shopRows->userShop($this->user->domain);
    	$shop=array();
    	if ($shopRows)
    	{
    		foreach ($shopRows as $val) {
    			$shop[$val['id']]=$val['shopname'];
    		}
    	}
    	$viewData['shop']=$shop;
    	
    	//取得所有分类
    	$typeRows=new Type($adapter);
    	$typeRows = $typeRows->typeAll($this->user->domain);
    	$type=array();
    	if ($typeRows)
    	{
    		foreach ($typeRows as $val) {
    			$type[$val['id']]=$val['name'];
    		}
    	}
    	$viewData['type']=$type;
    	return $viewData;
    }
    
    /**
     * @todo 删除商品
     */
    public function deleteAction()
    {
    	if (!isset($this->user->domain)) $this->redirect()->toUrl('/login');
    	$id = (int)$this->params()->fromQuery('id');
    	if ($id)
    	{
    		$db = new Commodity($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
    		$row = $db->getCommodity($id);
    		if ($row['uid']==$this->user->domain && $this->user->power >= 2)
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