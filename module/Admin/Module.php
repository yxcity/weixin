<?php

namespace Admin;

use Zend\Db\ResultSet\ResultSet;
use Admin\Form\ArchivesVerify;
use Zend\Db\TableGateway\TableGateway;
use Admin\Model\Archives;
use Zend\ModuleManager\ModuleManager;
use Admin\Model\Collect;
use Admin\Form\CollectVerify;

class Module {
	public function init(ModuleManager $moduleManager)
	{
		$sharedEvents=$moduleManager->getEventManager()->getSharedManager();
		$sharedEvents->attach(__NAMESPACE__,'dispatch',function ($e){
			$controller=$e->getTarget();
			//$controller->layout('layout/admin');
		},100);
	}
	
	public function getAutoloaderConfig() {
		return array (
				'Zend\Loader\ClassMapAutoloader' => array (
						__DIR__ . '/autoload_classmap.php' 
				),
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => array (
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__ 
						) 
				) 
		);
	}
	public function getServiceConfig() {
		return array (
				'factories' => array (
						'ArchivesTableGateway' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							$resultSet = new ResultSet ();
							$resultSet->setArrayObjectPrototype ( new ArchivesVerify () );
							return new TableGateway ( 'archives', $dbAdapter );
						},
						'Admin\Model\Archives' => function ($sm) {
							$tableGateway = $sm->get ( 'ArchivesTableGateway' );
							$table = new Archives ( $tableGateway );
							return $table;
						},
						'CollectTableGateway'=>function ($sm)
						{
							$dbAdapter=$sm->get('Zend\Db\Adapter\Adapter');
							$resultSet = new ResultSet();
							$resultSet->setArrayObjectPrototype(new CollectVerify());
							return new TableGateway('collect',$dbAdapter);
						},
						'Admin\Model\Collect'=>function ($sm)
						{
							$tableGateway = $sm->get('CollectTableGateway');
							$table = new Collect($tableGateway);
							return $table;
						}
				) 
		);
	}
	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}
}
