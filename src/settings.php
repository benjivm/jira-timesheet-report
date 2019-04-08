<?php

// Environment variables
$dotenv = Dotenv\Dotenv::create(__DIR__ . '/../');
$dotenv->load();

return [
    'settings' => [
        'displayErrorDetails'    => getenv('DEBUG'),
        'addContentLengthHeader' => true,
        'templates'              => [
            'path' => __DIR__ . '/../views/',
        ],
        'logger' => [
            'name'  => 'jira-timesheet-report',
            'path'  => __DIR__ . '/../logs/app.log',
            'level' => Monolog\Logger::DEBUG,
        ],
    ],
];
