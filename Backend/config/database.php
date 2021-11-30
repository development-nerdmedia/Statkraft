<?php

Configure::write('DB', [
    'default' => [
        'driver' => 'pdo=>mysql',
        'persistent' => false,
    	'host' => '192.168.1.5',
    	'login' => 'statkraftuser',
        'password' => 'st4tkr4ft@',
        'database' => 'STATKRAFTDB',
        'encoding' => 'utf8',
        'prefix' => 'TWEB_'
    ]
]);


