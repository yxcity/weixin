<?php

namespace Archives;

use Zend\ModuleManager\ModuleManager;
//use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Archives\Model\Archives;
use Archives\Model\Auto;

class Module {
	public function init(ModuleManager $moduleManager) {
		$sharedEvents = $moduleManager->getEventManager ()->getSharedManager ();
		$sharedEvents->attach ( __NAMESPACE__, 'dispatch', function ($e) {
			$controller = $e->getTarget ();
		}, 100 );
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
							//$resultSet = new ResultSet();
							//$resultSet->setArrayObjectPrototype ( new ArchivesVerify () );
							return new TableGateway ( 'archives', $dbAdapter );
						},
						'Archives\Model\Archives' => function ($sm) {
							$tableGateway = $sm->get ( 'ArchivesTableGateway' );
							$table = new Archives($tableGateway);
							return $table;
						},
						'AutoTableGateway' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							//$resultSet = new ResultSet();
							//$resultSet->setArrayObjectPrototype ( new ArchivesVerify () );
							return new TableGateway ( 'archives', $dbAdapter );
						},
						'Archives\Model\Auto' => function ($sm) {
							$tableGateway = $sm->get ( 'AutoTableGateway' );
							$table = new Auto($tableGateway);
							return $table;
						} 
				) 
		);
	}
	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}
}
