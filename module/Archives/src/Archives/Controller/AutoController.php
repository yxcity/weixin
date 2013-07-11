<?php

namespace Archives\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use module\Application\src\Model\Tool;

class AutoController extends AbstractActionController {
	private $user;
	private $db;
	function __construct() {
		$this->user = Tool::getSession('auth', 'user');
	}
	
	function indexAction() {
	}
	/**
	 * @todo 添加车型
	 * @return multitype:NULL
	 */
	function createAction() {
		$viewData=$this->init();
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$post = $request->getPost();
			$data=array();
			$data['title']=$post['title'];
			$data['content'] = $post['content'];
			$this->getDB()->save($data);
		}
		return $viewData;
	}
	/**
	 * @todo 编辑车型
	 * @return Ambigous <\Archives\Model\mixed, mixed>
	 */
	function editAction() {
		$viewData = $this->init();
		$id = $this->params()->fromQuery('id');
		$row = $this->getDB()->getAutoID($id);
		if (!$row || ($this->user->power < 2 && $this->user->id != $row['uid'])) $this->redirect()->toRoute(array('auto'));
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$post = $request->getPost();
			$data=array();
			$data['title']=$post['title'];
			$data['content']=$post['content'];
			$this->getDB()->save($data,$id);
		}
		$viewData['row']=$row;
		return $viewData;
	}
	/**
	 * @todo 车型详情
	 * @return multitype:Ambigous <\Archives\Model\mixed, mixed>
	 */
	function detailsAction() {
		$id = $this->params()->fromQuery('id');
		$row = $this->getDB()->getAutoID($id);
		$viewData=array();
		$viewData['row']=$row;
		return $viewData;
	}
	
	function init()
	{
		if (!$this->user) $this->redirect()->toRoute('auto');
		$viewData=array();
		$viewData['user']=$this->user;
		return $viewData;
	}
	/**
	 * @todo 链接数据库
	 * @return Ambigous <object, multitype:, \Archives\Model\Auto>
	 */
	function getDB()
	{
		if (!$this->db){
			$this->db = $this->getServiceLocator()->get('Archives\Model\Auto');
		}
		return $this->db;
	}
}