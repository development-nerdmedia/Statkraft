<?php
//Methods should always be on UPPERCASE
$routes = [
    'admin' => [
        '/' => [
            'GET' => [
                'controller' => 'admin',
                'action' => 'login'
            ],
            'POST' => [
                'controller' => 'admin',
                'action' => 'verify'
            ],
        ],
        "/logout" => [
            'GET' => [
                'controller' => 'admin',
                'action' => 'logout'
            ]
        ],
        "/dashboard" => [
            'GET' => [
                'controller' => 'admin',
                'action' => 'dashboard'
            ]
        ],
        "/recyclepoints/list" => [
            'GET' => [
                'controller' => 'recyclepoints',
                'action' => 'list'
            ]
        ],
        "/recyclepoints/list/*" => [
            'GET' => [
                'controller' => 'recyclepoints',
                'action' => 'list'
            ]
        ],
        "/recyclepoints/add" => [
            'GET' => [
                'controller' => 'recyclepoints',
                'action' => 'add'
            ],
            'POST' => [
                'controller' => 'recyclepoints',
                'action' => 'save'
            ]
        ],
        "/recyclepoints/edit/*" => [
            'GET' => [
                'controller' => 'recyclepoints',
                'action' => 'edit'
            ],
            'POST' => [
                'controller' => 'recyclepoints',
                'action' => 'update'
            ]
        ],
        "/recyclepoints/delete/*" => [
            'GET' => [
                'controller' => 'recyclepoints',
                'action' => 'delete'
            ]
        ],
        "/events/list" => [
            'GET' => [
                'controller' => 'events',
                'action' => 'list'
            ]
        ],
        "/events/list/*" => [
            'GET' => [
                'controller' => 'events',
                'action' => 'list'
            ]
        ],
        "/events/add" => [
            'GET' => [
                'controller' => 'events',
                'action' => 'add'
            ],
            'POST' => [
                'controller' => 'events',
                'action' => 'save'
            ]
        ],
        "/events/edit/*" => [
            'GET' => [
                'controller' => 'events',
                'action' => 'edit'
            ],
            'POST' => [
                'controller' => 'events',
                'action' => 'update'
            ]
        ],
        "/events/delete/*" => [
            'GET' => [
                'controller' => 'events',
                'action' => 'delete'
            ]
        ]
    ],
    'site' => [
    	'/' => [
			'GET' => [ 
                'controller' => 'site', 
                'action' => 'index'
            ]
        ],
        '/iframe' => [
            'GET' => [
                'controller' => 'site',
                'action' => 'iframetmp'
            ]
        ]
    ],
    'api' => [
        '/v1/recyclepoints/get/list/*' => [
            'GET' => [
                'controller' => 'api',
                'action' => 'getRecyclePointList'
            ]
        ],
        '/v1/recyclepoints/get/byid/*' => [
            'GET' => [
                'controller' => 'api',
                'action' => 'getRecyclePointById'
            ]
        ],        
        '/v1/events/get/list/*' => [
            'GET' => [
                'controller' => 'api',
                'action' => 'getEventList'
            ]
        ],
        '/v1/newsletter/save' => [
            'POST' => [
                'controller' => 'api',
                'action' => 'saveNewsletterEmail'
            ]
        ],
        '/v1/ubigeo/getbyparentid/*' => [
            'GET' => [
                'controller' => 'api',
                'action' => 'GetUbigeoByParentId'
            ]
        ]
    ]
];