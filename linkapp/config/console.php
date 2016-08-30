<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
      'log',
      'app\base\settings',
    ],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
          'enablePrettyUrl' => true,
          'showScriptName' => false,
          'baseUrl' => 'http://thisdomain.com',
          'rules' => [
            'settings' => 'settings/update',
            'links' => 'links/index',
            '<alias:login|logout>' => 'site/<alias>',
            '<short_url>' => 'links/view',
          ],
        ],
      ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];


return $config;
