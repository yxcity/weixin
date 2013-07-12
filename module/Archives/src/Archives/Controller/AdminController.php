<?php

namespace Archives\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use module\Application\src\Model\Tool;

class AdminController extends AbstractActionController {
	private $user;
	private $db;
	function __construct() {
		$this->user = Tool::getSession ( 'auth', 'user' );
	}
	
	function indexAction() {
		$viewData = $this->init ();
		$page = $this->params ()->fromQuery ( 'page' );
		$rows = $this->getDB ()->getArchivesList ( $page );
		$viewData ['rows'] = $rows;
		return $viewData;
	}
	
	function createAction() {
		$viewData = $this->init ();
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$data = $request->getPost ();
			unset($data['submit']);
			$file = $request->getFiles ()->toArray ();
			if ($file && is_array ( $file )) {
				$thumb = Tool::uploadfile ( $file );
				if ($thumb ['res']) {
					$data ['thumb'] = $thumb ['file'];
				}
			}
			$data ['title'] = Tool::filter ( $data ['title'], true );
			$data ['content'] = Tool::filter ( $data ['content'] );
			$this->getDB ()->save ( $data );
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
		$id = ( int ) $this->params ()->fromQuery ( 'id' );
		if (! $id)
			$this->redirect ()->toRoute ( 'archivesAdmin' );
		$row = $this->getDB ()->getArchivesID ( $id );
		if ($row ['id'] != $id || ($row ['uid'] != $this->user->id && $this->user->power < 2))
			$this->redirect ()->toRoute ( 'archivesAdmin' );
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$data = $request->getPost ();
			unset($data['submit']);
			$file = $request->getFiles ()->toArray ();
			if ($file && is_array ( $file )) {
				$thumb = Tool::uploadfile ( $file );
				if ($thumb ['res']) {
					$data ['thumb'] = $thumb ['file'];
				}
			}
			$data ['title'] = Tool::filter ( $data ['title'] );
			$data ['content'] = Tool::filter ( $data ['content'] );
			$this->getDB ()->save ( $data, $id );
		}
		$viewData ['row'] = $row;
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
	
	function getDB() {
		if (! $this->db) {
			$this->db = $this->getServiceLocator ()->get ( 'Archives\Model\Archives' );
		}
		return $this->db;
	}
}