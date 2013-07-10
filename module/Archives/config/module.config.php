<?php
return array (
		'view_manager' => array (
				'template_path_stack' => array (
						'Admin' => __DIR__ . '/../view' 
				),
				'template_map' => array (
						'pagination/paginator' => __DIR__ . '/../view/pagination/paginator.phtml' 
				) 
		),
		
		'controllers' => array (
				'invokables' => array (
						'Admin\Controller\Home' => 'Admin\Controller\HomeController',
				) 
		),
		
		'router' => array (
				'routes' => array (
						'msg' => array (
								'type' => 'segment',
								'options' => array (
										'route' => '[/:lang]/msg[/:action][/:page]',
										'constraints' => array (
												'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'page' => '[0-9]+' 
										),
										'defaults' => array (
												'controller' => 'Admin\Controller\Msg',
												'action' => 'index' 
										) 
								) 
						) 
				)
				 
		) 
);


