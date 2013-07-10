<?php

namespace Archives\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use module\Application\src\Model\Tool;

class AdminController extends AbstractActionController {
	private $user;
	function __construct() {
		$this->user = Tool::getSession ( 'auth', 'user' );
	}
	function indexAction() {
		$viewData = $this->init ();
		return $viewData;
	}
	function createAction() {
		$viewData = $this->init ();
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$post = $request->getPost();
			$data=array();
			$data['title']=Tool::filter($post['title'],true);
			$data['content']=Tool::filter($post['content']);
			
		}
		$viewData ['asset'] = array (
				'js' => array (
						'/ueditor/ueditor.all.min.js',
						'/ueditor/ueditor.config.js' 
				) 
		);
		return $viewData;
	}
	function editAction() {
		$viewData = $this->init ();
		$viewData ['asset'] = array (
				'js' => array (
						'/ueditor/ueditor.all.min.js',
						'/ueditor/ueditor.config.js' 
				) 
		);
		return $viewData;
	}
	function init() {
		if (! $this->user)
			$this->redirect ()->toRoute ( 'login' );
		$viewData = array ();
		$viewData ['user'] = $this->user;
		return $viewData;
	}
}