<?php
return array(
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view'
        ),
        'template_map' => array(
            'pagination/paginator' => __DIR__ . '/../view/pagination/paginator.phtml'
        )
    ),
    
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Home' => 'Admin\Controller\HomeController',
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Archives' => 'Admin\Controller\ArchivesController',
            'Admin\Controller\Collect' => 'Admin\Controller\CollectController',
            'Admin\Controller\Users' => 'Admin\Controller\UsersController',
            'Admin\Controller\Commodity' => 'Admin\Controller\CommodityController',
            'Admin\Controller\Indent' => 'Admin\Controller\IndentController',
            'Admin\Controller\Shop' => 'Admin\Controller\ShopController',
            'Admin\Controller\Tenant' => 'Admin\Controller\TenantController',
            'Admin\Controller\Type' => 'Admin\Controller\TypeController',
            'Admin\Controller\Keyword' => 'Admin\Controller\KeywordController',
            'Admin\Controller\Answers' => 'Admin\Controller\AnswersController',
            'Admin\Controller\Msg' => 'Admin\Controller\MsgController'
        )
    ),
    
    'router' => array(
        'routes' => array(
            'msg' => array(
            		'type' => 'segment',
            		'options' => array(
            				'route' => '[/:lang]/msg[/:action][/:page]',
            				'constraints' => array(
            						'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
            						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
            						'page' => '[0-9]+'
            				),
            				'defaults' => array(
            						'controller' => 'Admin\Controller\Msg',
            						'action' => 'index'
            				)
            		)
            ),
            'admin' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:lang]/home[/:action][/]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Home',
                        'action' => 'index'
                    )
                )
            ),
            'login' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:lang]/login[/]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'auth'
                    )
                )
            ),
            'logout' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:lang][/:domain]/logout[/]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'logout'
                    )
                )
            ),
            'collect' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:lang]/collect[/:action][/:page]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Collect',
                        'action' => 'index'
                    )
                )
            ),
            'users' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:lang]/users[/:action][/:page][/]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Users',
                        'action' => 'index'
                    )
                )
            ),
            'commodity' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:lang]/commodity[/:action][/:page][/]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Commodity',
                        'action' => 'index'
                    )
                )
            ),
            'indent' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:lang]/indent[/:action][/:page][/]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Indent',
                        'action' => 'index'
                    )
                )
            ),
            'shop' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:lang]/shop[/:action][/:page][/]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Shop',
                        'action' => 'index'
                    )
                )
            ),
            'tenant' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:lang]/tenant[/:action][/:page]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Tenant',
                        'action' => 'index'
                    )
                )
            ),
            'type' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:lang]/t[/:action][/:page][/]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Type',
                        'action' => 'index'
                    )
                )
            ),
            'keyword' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:lang]/keyword[/:action][/:page][/]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Keyword',
                        'action' => 'index'
                    )
                )
            ),
            'answers' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:lang]/answers[/:action][/:page][/]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}(-[A-Z]{2}){0,1}',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Answers',
                        'action' => 'index'
                    )
                )
            )
        )
    )
);



