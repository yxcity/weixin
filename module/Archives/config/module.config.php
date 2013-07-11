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
						'Archives\Controller\Index' => 'Archives\Controller\IndexController',
						'Archives\Controller\Admin' => 'Archives\Controller\AdminController',
						'Archives\Controller\Auto' => 'Archives\Controller\AutoController',
				) 
		),
		
		'router' => array (
				'routes' => array (
						'archivesIndex' => array (
								'type' => 'segment',
								'options' => array (
										'route' => '[/:lang]/archives[/:action][/:page]',
										'constraints' => array (
												'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'page' => '[0-9]+' 
										),
										'defaults' => array (
												'controller' => 'Archives\Controller\Index',
												'action' => 'index' 
										) 
								) 
						),
						'archivesAdmin' => array (
								'type' => 'segment',
								'options' => array (
										'route' => '[/:lang]/admin/archives[/:action][/:page][/]',
										'constraints' => array (
												'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'page' => '[0-9]+'
										),
										'defaults' => array (
												'controller' => 'Archives\Controller\Admin',
												'action' => 'index'
										)
								)
						),
						'auto' => array (
								'type' => 'segment',
								'options' => array (
										'route' => '[/:lang]/auto[/:action][/:page][/]',
										'constraints' => array (
												'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'page' => '[0-9]+'
										),
										'defaults' => array (
												'controller' => 'Archives\Controller\Auto',
												'action' => 'index'
										)
								)
						),
				)
				 
		) 
);



