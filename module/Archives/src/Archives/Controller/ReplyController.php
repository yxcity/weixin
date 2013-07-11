<?php
namespace Archives\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use module\Application\src\Model\Tool;
class KeywordController extends AbstractActionController{
	private $user;
	private $db;
	function __construct(){
		$this->user=Tool::getSession('auth', 'user');
	}
	
	
	
	
}