<?php

// Setup vars
$controllerNamespace = 'Controllers\\';

// Application's available routes
$routes = [
    'GET' => [
        '~^\/$~' => 'TaskController@index',
        '~^\/\?~' => 'TaskController@index',
        '~^/([\d]+?){1}/show$~' => 'TaskController@show',
        '~^/task/new$~' => 'TaskController@new',
        '~^/logout$~' => 'AuthController@logout',
        '~^/login$~' => 'AuthController@index',
    ],
    'POST' => [
        '~^\/$~' => 'TaskController@index',
        '~^\/\?~' => 'TaskController@index',
        '~^/([\d]+?){1}/show$~' => 'TaskController@store',
        '~^/task/new$~' => 'TaskController@store',
        '~^/login$~' => 'AuthController@login',
    ]
];

$GLOBALS['dboptions'] = [
    'driver' => 'mysql',
    'dbname' => 'task_db',
    'host' => '127.0.0.1',
    'user' => 'task_db_user',
    'password' => '1234',
];
