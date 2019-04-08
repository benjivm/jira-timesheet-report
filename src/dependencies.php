<?php

// Fetch DI Container
$container = $app->getContainer();

// Register flash provider
$container['flash'] = function () {
    return new Slim\Flash\Messages();
};

// Register PHP View helper
$container['view'] = function ($container) {
    $settings = $container->get('settings')['templates'];

    return new Slim\Views\PhpRenderer($settings['path']);
};

// Register HTTPClient for JIRA API requests
$container['httpClient'] = function () {
    $httpClient = Symfony\Component\HttpClient\HttpClient::create([
        'base_uri'   => getenv('JIRA_URL') . '/rest/api/latest/',
        'auth_basic' => [
            getenv('JIRA_USERNAME'),
            getenv('JIRA_PASSWORD'),
        ],
        'headers' => [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ],
    ]);

    return $httpClient;
};
