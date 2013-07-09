<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Model\User;
use Admin\Model\Type;
use module\Application\src\Model\Tool;

class TypeController extends AbstractActionController
{
    
    function indexAction ()
    {
        $openid = $this->openid();
        $domain = Tool::domain();
        if (!$openid) $this->redirect()->toRoute('user',array('action'=>'error'));
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $db = new Type($adapter);
        $viewData['openid']=$openid;
        $viewData['rows'] = $db->typeAll(sha1($domain));
        $viewModel = new ViewModel($viewData);
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    function areasAction()
    {
    	$pid=$this->params()->fromQuery('pid');
    	$db = new User($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
    	$rows=$db->areas($pid);
    	if ($rows)
    	{
    		$str='';
    	    foreach ($rows as $val) {
    			$str.= "<option value=\"{$val['areaid']}\">{$val['name']}</option>".PHP_EOL;
    		}
    		echo $str;
    	}
    	exit();
    }
    
    function openid()
    {
    	return $this->params()->fromQuery('openid');;
    }

}