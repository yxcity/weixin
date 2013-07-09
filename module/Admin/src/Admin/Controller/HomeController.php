<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use module\Application\src\Model\Tool;

class HomeController extends AbstractActionController {
    
    public $user;
    
    function __construct(){
    	$this->user=Tool::getSession('auth','user');
    }
	public function indexAction() {
	    if (!isset($this->user->domain) || $this->user->power < 1) $this->redirect()->toUrl('/login');
	    $viewData=array();
	    $viewData['user']=$this->user;
	    $auth=Tool::getCookie('auth');
	    if ($auth)
	    {
	    	$viewData['auth']=json_decode($auth);
	    }
		return $viewData;
	}
}