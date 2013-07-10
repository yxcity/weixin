<?php

namespace Admin;

use Zend\ModuleManager\ModuleManager;


class Module {
	public function init(ModuleManager $moduleManager)
	{
		$sharedEvents=$moduleManager->getEventManager()->getSharedManager();
		$sharedEvents->attach(__NAMESPACE__,'dispatch',function ($e){
			$controller=$e->getTarget();
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
				'factories' => array () 
		);
	}
	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}
}