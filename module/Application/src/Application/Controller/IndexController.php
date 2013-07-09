<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use module\Application\src\Model\Tool;
use Admin\Model\Shop;
use module\Application\src\Model\Alipay;
use Admin\Model\User;
use Admin\Model\Commodity;
use Admin\Model\Type;
class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $domian=Tool::domain();
        $openid = $this->openid();
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $user = new User($adapter);
        $row=$user->clickDomain(sha1($domian));
        if (!$row) $this->redirect()->toRoute('user',array('action'=>'error'));
        $co = new Commodity($adapter);
        $rows=$co->proList(9, 1, sha1($domian));
        
        $db = new Type($adapter);
        $type = $db->typeAll(sha1($domian));
        if ($type)
        {
        	$tmpRows=array();
        	foreach ($type as $key=>$val) {
        		if ($val['pid']){
        			$tmpRows[$val['pid']]['next'][]=$val;
        		}else
        		{
        			$tmpRows[$val['id']]=$val;
        		}
        	}
        }
        unset($type);        

        $dbshop = new Shop($adapter);
        $shops = $dbshop->shopAll(sha1($domian));
        
        $view = new ViewModel(array('openid'=>$openid,'rows'=>$rows,'row'=>$row,'type'=>$tmpRows,'shops'=>$shops));
        $view->setTerminal(200);
        return $view;
    }
    

    /**
     * @todo  支付测试页
     */
    function alipayAction()
    {
        echo '<meta charset="utf-8">';
        $domain = Tool::domain();
        $action = $this->params()->fromQuery('action');
        if ($action=='ok')
        {
        	$success=$this->params()->fromQuery('is_success');
        	$emali=$this->params()->fromQuery('seller_email');
        	$user = new User($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        	$row  = $user->clickDomain(sha1($domain));
        	if ($row['alipayEmail']==$emali)
        	{
        	    $user->editUser($row['id'], array('alipay'=>1));
        	    echo '绑定成功';
        	    exit();
        	}
        }
        echo '测试支付';
        exit();
    }
    
    public function uploadifyAction()
    {
        
        $request=$this->getRequest();    
        $file=$request->getFiles ()->toArray();
    		if ($file && is_array($file))
    		{
    		    $thumb = Tool::uploadfile($file);
    		    if ($thumb['res'])
    		    {
    		        echo $thumb['file'];
    		    }
    		}
        exit();
    }
    

    
    
    function testAction()
    {
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    	$db = new Shop($adapter);
    	$rows = $db->keyList(null,'yalong');
    	var_dump($rows);
    	exit();
    }
    
    function openid()
    {
    	return $this->params()->fromQuery('openid');;
    }

}
